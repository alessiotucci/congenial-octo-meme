<?php
/* ************************************************************************** */
/*     File: models\FoodOrder.php                                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:47:08                                           */
/*     Updated: 2026/01/23 17:47:09                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

class FoodOrder
{
	// properties for the queries
	private $conn;
	private $table = 'food_order';

	// properites
	public $id;
	public $customer_id;
	public $food_place_id;
	public $customer_address_id;
	public $order_status_id;
	public $assigned_driver_id; // Can be NULL?
	public $order_datetime;
	public $delivery_fee;
	public $total_amount;
	public $requested_delivery_time;
	public $cust_delivery_rating;
	public $cust_food_place_rating;

	public function __construct($db)
	{
		$this->conn = $db;
	}

	public function create()
	{
		$query = 'INSERT INTO ' . this->table . ('customer_id, food_place_id,
				customer_address_id, order_status_id, assigned_driver_id,
				order_datetime, delivery_fee, total_amout,
				requested_delivery_time, cust_delivery_rating,
				cust_food_place_rating)
			VALUE  (:customer_id, :food_place_id,
				:customer_address_id, :order_status_id, :assigned_driver_id,
				:order_datetime, :delivery_fee, :total_amout,
				:requested_delivery_time, :cust_delivery_rating,
				:cust_food_place_rating)';

		$stmt = this->conn->prepare($query);

		// clean up the input
		$this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
		$this->food_place_id = htmlspecialchars(strip_tags($this->food_place_id));
		$this->customer_address_id = htmlspecialchars(strip_tags($this->customer_address_id));
		$this->order_status_id = htmlspecialchars(strip_tags($this->order_status_id));
		$this->assigned_driver_id = htmlspecialchars(strip_tags($this->assigned_driver_id));
		$this->order_datetime = htmlspecialchars(strip_tags($this->order_datetime);
		$this->delivery_fee = htmlspecialchars(strip_tags($this->delivery_fee));
		$this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
		$this->requested_delivery_time = htmlspecialchars(strip_tags($this->requested_delivery_time));
		$this->cust_delivery_rating = htmlspecialchars(strip_tags($this->cust_delivery_rating));
		$this->cust_food_place_rating = htmlspecialchars(strip_tags($this->cust_food_place_rating));

		//TODO; handle the nulls?

		// bind the params
		$stmt->bindParam(':customer_id', $this->customer_id);
		$stmt->bindParam(':food_place_id', $this->food_place_id);
		$stmt->bindParam(':customer_address_id', $this->customer_address_id);
		$stmt->bindParam(':order_status_id', $this->order_status_id);
		$stmt->bindParam(':assigned_driver_id', $this->assigned_driver_id);
		$stmt->bindParam(':order_datetime', $this->customer_id);
		$stmt->bindParam(':delivery_fee', $this->customer_id);
		$stmt->bindParam(':total_amount', $this->customer_id);
		$stmt->bindParam(':requested_delivery_time', $this->customer_id);
		$stmt->bindParam(':cust_delivery_rating', $this->customer_id);
		$stmt->bindParam(':cust_food_place_rating', $this->customer_id);

		if ($stmt->execute())
		{
			printf("Success! Created the order!");
			return $this->conn->lastInsertId();
			/*return (true);*/
		}
		else
		{
			printf("Failure! Cannot create the order: %s\n", $stmt->error);
			return (false);
		}
}
?>
