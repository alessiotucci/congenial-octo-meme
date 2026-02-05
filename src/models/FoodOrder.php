<?php
/* ************************************************************************** */
/*     File: models\FoodOrder.php                                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:47:08                                           */
/*     Updated: 2026/01/31 18:14:15                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

class FoodOrder
{
	private $conn;
	private $table = 'food_order';
	private $table_items = 'order_items';

	public $id;
	public $customer_id;
	public $food_place_id;
	public $customer_address_id;
	public $delivery_fee;
	public $requested_delivery_time;
	
	// Calculated Properties (Not set by user input directly)
	//TODO
	public $total_amount;
	public $order_status_id;
	public $order_datetime;

	// Public property to hold items when reading a single order
    public $items_list = []; 
    public $restaurant_name;
    public $status_name;

	public $assigned_driver_id;

	// NEW Properties for the Extended View
    public $customer_name;
    public $delivery_address;


	public function __construct($db)
	{
		$this->conn = $db;
	}

	// $items is an array of objects: [{ "menu_item_id": 1, "quantity": 2 }]
	public function create($items)
	{
		try
		{
			$this->conn->beginTransaction();
			$calculated_total = 0;
			$validated_items = []; // We store the trusted data here
			foreach ($items as $item)
			{
				// Look up the REAL price from the database
				// We also check if the item belongs to the correct restaurant
				$queryPrice = "SELECT price, food_place_id FROM menu_item WHERE id = ? LIMIT 1";
				$stmtPrice = $this->conn->prepare($queryPrice);
				$stmtPrice->bindParam(1, $item->menu_item_id);
				$stmtPrice->execute();
				
				$row = $stmtPrice->fetch(PDO::FETCH_ASSOC);
				if (!$row)
				{
					throw new Exception("Invalid Menu Item ID: " . $item->menu_item_id);
				}
				// Anti-Hack: Ensure item belongs to the restaurant we are ordering from
				if ($row['food_place_id'] != $this->food_place_id)
				{
					throw new Exception("Security Alert: Item " . $item->menu_item_id . " does not belong to this restaurant.");
				}
				$real_price = $row['price'];
				$qty = $item->quantity;
				$calculated_total += ($real_price * $qty);
				$validated_items[] = [
					'id' => $item->menu_item_id,
					'qty' => $qty,
					'price' => $real_price
				];
			}
			// Add delivery fee to final total
			$this->total_amount = $calculated_total + $this->delivery_fee;
			// ---------------------------------------------------------
			// STEP B: INSERT ORDER HEADER
			// ---------------------------------------------------------
			$queryOrder = 'INSERT INTO ' . $this->table . ' 
						   SET 
							 customer_id = :cust_id,
							 food_place_id = :place_id,
							 customer_address_id = :addr_id,
							 delivery_fee = :fee,
							 total_amount = :total,
							 requested_delivery_time = :req_time,
							 order_status_id = 1,  -- 1 = Pending
							 assigned_driver_id = NULL,
							 cust_driver_rating = NULL,
							 cust_food_place_rating = NULL,
							 order_datetime = NOW()';
			$stmtOrder = $this->conn->prepare($queryOrder);

			// Bind Params
			$this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
			$this->food_place_id = htmlspecialchars(strip_tags($this->food_place_id));
			$this->customer_address_id = htmlspecialchars(strip_tags($this->customer_address_id));
			$this->delivery_fee = htmlspecialchars(strip_tags($this->delivery_fee));
			$this->requested_delivery_time = htmlspecialchars(strip_tags($this->requested_delivery_time));
			// ... (assume other sanitization happens in controller or here)

			$stmtOrder->bindParam(':cust_id', $this->customer_id);
			$stmtOrder->bindParam(':place_id', $this->food_place_id);
			$stmtOrder->bindParam(':addr_id', $this->customer_address_id);
			$stmtOrder->bindParam(':fee', $this->delivery_fee);
			$stmtOrder->bindParam(':total', $this->total_amount); // We use the SAFE calculated total
			$stmtOrder->bindParam(':req_time', $this->requested_delivery_time);

			if (!$stmtOrder->execute())
			{
				throw new Exception("Failed to create Order Header.");
			}
			// Get the new Order ID
			$new_order_id = $this->conn->lastInsertId();
			// ---------------------------------------------------------
			// STEP C: INSERT ORDER ITEMS
			// ---------------------------------------------------------
			$queryItem = 'INSERT INTO ' . $this->table_items . ' 
						  (order_id, menu_item_id, quantity, price) 
						  VALUES (:oid, :mid, :qty, :price)';
			$stmtItem = $this->conn->prepare($queryItem);
			foreach ($validated_items as $v_item)
			{
				$stmtItem->bindParam(':oid', $new_order_id);
				$stmtItem->bindParam(':mid', $v_item['id']);
				$stmtItem->bindParam(':qty', $v_item['qty']);
				$stmtItem->bindParam(':price', $v_item['price']); // Snapshot price
				
				if (!$stmtItem->execute())
				{
					throw new Exception("Failed to add item to order.");
				}
			}
			// 2. COMMIT TRANSACTION
			$this->conn->commit();
			$this->id = $new_order_id;
			return true;
		}
		catch (Exception $e)
		{
			$this->conn->rollBack();
			printf("Error: %s\n", $e->getMessage()); // Useful for debug
			return false;
		}
	}
	// ------------------------------------------------------------------
    // 2. READ HISTORY (List of Orders for a Customer)
    // ------------------------------------------------------------------
    public function read_by_customer()
    {
        // We JOIN 'food_place' to show the Restaurant Name
        // We JOIN 'order_status' to show "Delivered" instead of "4"
        $query = 'SELECT 
                    o.id, 
                    o.total_amount, 
                    o.order_datetime, 
                    o.order_status_id,
                    fp.name as restaurant_name,
                    os.status_value
                  FROM ' . $this->table . ' o
                  JOIN food_place fp ON o.food_place_id = fp.id
                  JOIN order_status os ON o.order_status_id = os.id
                  WHERE o.customer_id = :id
                  ORDER BY o.order_datetime DESC';

        $stmt = $this->conn->prepare($query);
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $stmt->bindParam(':id', $this->customer_id);
        $stmt->execute();

        return $stmt;
    }

    // ------------------------------------------------------------------
    // 3. READ SINGLE (Detailed View with Items)
    // ------------------------------------------------------------------
	public function read_single()
    {
        $query = 'SELECT 
                    o.id, 
                    o.total_amount, 
                    o.delivery_fee,
                    o.order_datetime, 
                    o.order_status_id,
                    o.assigned_driver_id,
                    -- Restaurant Info
                    fp.name as restaurant_name,
                    -- Status Info
                    os.status_value,
                    -- Customer Info
                    c.first_name, 
                    c.last_name,
                    -- Address Info
                    a.address_line1, 
                    a.city, 
                    a.postal_code
                  FROM ' . $this->table . ' o
                  JOIN food_place fp ON o.food_place_id = fp.id
                  JOIN order_status os ON o.order_status_id = os.id
                  JOIN customer c ON o.customer_id = c.id
                  JOIN customer_address ca ON o.customer_address_id = ca.id
                  JOIN address a ON ca.address_id = a.id
                  WHERE o.id = ?
                  LIMIT 0,1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Map DB columns to Object Properties
            $this->id = $row['id'];
            $this->total_amount = $row['total_amount'];
            $this->delivery_fee = $row['delivery_fee'];
            $this->order_datetime = $row['order_datetime'];
            $this->order_status_id = $row['order_status_id'];
            $this->status_name = $row['status_value'];
            $this->assigned_driver_id = $row['assigned_driver_id'];
            
            $this->restaurant_name = $row['restaurant_name'];
            
            // Format Composite Fields
            $this->customer_name = $row['first_name'] . ' ' . $row['last_name'];
            $this->delivery_address = $row['address_line1'] . ', ' . $row['city'] . ' ' . $row['postal_code'];

            // Fetch Items Helper
            $this->get_order_items();
            
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------
    // PRIVATE HELPER: FETCH ITEMS
    // ------------------------------------------------------------------
    private function get_order_items()
    {
        $query = 'SELECT 
                    oi.quantity, 
                    oi.price as price_at_order, 
                    mi.item_name 
                  FROM ' . $this->table_items . ' oi
                  JOIN menu_item mi ON oi.menu_item_id = mi.id
                  WHERE oi.order_id = :oid';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':oid', $this->id);
        $stmt->execute();

        $this->items_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

	// Inside models/FoodOrder.php

    // ------------------------------------------------------------------
    // READ INCOMING ORDERS (For the Kitchen Dashboard)
    // ------------------------------------------------------------------
    public function read_incoming_orders()
    {
        // 1. Get the Order Headers (Status < 4 means not delivered yet)
        $query = "SELECT 
                    o.id, 
                    o.total_amount, 
                    o.order_datetime, 
                    o.order_status_id,
                    c.first_name, 
                    c.last_name,
                    os.status_value
                  FROM " . $this->table . " o
                  JOIN customer c ON o.customer_id = c.id
                  JOIN order_status os ON o.order_status_id = os.id
                  WHERE o.food_place_id = :fid 
                  AND o.order_status_id < 4
                  ORDER BY o.order_datetime ASC";

        $stmt = $this->conn->prepare($query);
        $this->food_place_id = htmlspecialchars(strip_tags($this->food_place_id));
        $stmt->bindParam(':fid', $this->food_place_id);
        $stmt->execute();

        $orders = [];

        // 2. Loop through orders and attach Items
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['items'] = $this->get_items_for_order($row['id']);
            $orders[] = $row;
        }

        return $orders;
    }

    // Private helper to avoid code duplication
    private function get_items_for_order($order_id)
    {
        $qItems = "SELECT quantity, mi.item_name 
                   FROM " . $this->table_items . " oi
                   JOIN menu_item mi ON oi.menu_item_id = mi.id
                   WHERE oi.order_id = :oid";
        
        $stmtI = $this->conn->prepare($qItems);
        $stmtI->bindParam(':oid', $order_id);
        $stmtI->execute();
        
        return $stmtI->fetchAll(PDO::FETCH_ASSOC);
    }

	

    // ------------------------------------------------------------------
    // STATE MACHINE: Update Status (e.g., Picked Up, Delivered)
    // ------------------------------------------------------------------
    public function updateStatus()
    {
        $query = 'UPDATE ' . $this->table . ' 
                  SET order_status_id = :status 
                  WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        $this->order_status_id = htmlspecialchars(strip_tags($this->order_status_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':status', $this->order_status_id);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------
    // STATE MACHINE: Cancel Order (Soft Cancel)
    // ------------------------------------------------------------------
    public function cancelOrder()
    {
        // We force status to 5 (Cancelled)
        $query = 'UPDATE ' . $this->table . ' 
                  SET order_status_id = 5, assigned_driver_id = NULL 
                  WHERE id = :id';

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute())
		{
            return true;
        }
        return false;
    }


	// ------------------------------------------------------------------
    // DRIVER: READ AVAILABLE & ACTIVE JOBS (Pool + My Mission)
    // ------------------------------------------------------------------
    public function read_for_driver($driver_id)
    {
        // We need 4 JOINS to get: Restaurant Name, Customer Name, and Physical Address
        $query = "SELECT 
                    o.id, 
                    o.delivery_fee, 
                    o.assigned_driver_id,
                    o.total_amount,
                    fp.name as restaurant_name,
                    c.first_name, c.last_name,
                    a.address_line1, a.city,
                    -- Subquery to count items without grouping/breaking rows
                    (SELECT COUNT(*) FROM " . $this->table_items . " WHERE order_id = o.id) as item_count
                  FROM " . $this->table . " o
                  JOIN food_place fp ON o.food_place_id = fp.id
                  JOIN customer c ON o.customer_id = c.id
                  -- JOIN to get the address ID
                  JOIN customer_address ca ON o.customer_address_id = ca.id
                  -- JOIN to get the actual text
                  JOIN address a ON ca.address_id = a.id
                  WHERE 
                    -- Scenario A: Ready (3) AND Unassigned (Pool)
                    (o.order_status_id = 3 AND o.assigned_driver_id IS NULL)
                    OR 
                    -- Scenario B: Ready (3) AND Assigned to ME (Active Job)
                    (o.order_status_id = 3 AND o.assigned_driver_id = :did)
                  ORDER BY o.order_datetime ASC";

        $stmt = $this->conn->prepare($query);
        $driver_id = htmlspecialchars(strip_tags($driver_id));
        $stmt->bindParam(':did', $driver_id);
        $stmt->execute();

        return $stmt;
    }

    // ------------------------------------------------------------------
    // DRIVER: READ HISTORY (Delivered Orders)
    // ------------------------------------------------------------------
    public function read_driver_history($driver_id)
    {
        $query = "SELECT 
                    o.id, 
                    o.delivery_fee, 
                    o.order_datetime,
                    fp.name as restaurant_name,
                    c.first_name, c.last_name
                  FROM " . $this->table . " o
                  JOIN food_place fp ON o.food_place_id = fp.id
                  JOIN customer c ON o.customer_id = c.id
                  WHERE 
                    o.assigned_driver_id = :did 
                    AND o.order_status_id = 4 -- Delivered
                  ORDER BY o.order_datetime DESC";

        $stmt = $this->conn->prepare($query);
        $driver_id = htmlspecialchars(strip_tags($driver_id));
        $stmt->bindParam(':did', $driver_id);
        $stmt->execute();

        return $stmt;
    }

    // ------------------------------------------------------------------
    // DRIVER: ASSIGN SELF (Accept Job)
    // ------------------------------------------------------------------
    public function assignDriver()
    {
        // We add "AND assigned_driver_id IS NULL" to prevent Race Conditions.
        // If two drivers click "Accept" at the same time, the second one will fail.
        $query = 'UPDATE ' . $this->table . ' 
                  SET assigned_driver_id = :did 
                  WHERE id = :id AND assigned_driver_id IS NULL';

        $stmt = $this->conn->prepare($query);
        
        $this->assigned_driver_id = htmlspecialchars(strip_tags($this->assigned_driver_id));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':did', $this->assigned_driver_id);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            // Check if any row was actually updated
            if($stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }
}
?>
