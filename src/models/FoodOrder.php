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
}
?>
