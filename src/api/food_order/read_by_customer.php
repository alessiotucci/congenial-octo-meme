<?php
/* ************************************************************************** */
/*     File: src\api\food_order\read_by_customer.php                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/04 16:11:52                                           */
/*     Updated: 2026/02/04 16:12:47                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodOrder.php';

$database = new DbConnection();
$db = $database->connect();
$order = new FoodOrder($db);

// Validate ID
if(!isset($_GET['id']))
{
	http_response_code(400);
	echo json_encode(["message" => "Missing Customer ID."]);
	exit();
}

$order->customer_id = $_GET['id'];
$result = $order->read_by_customer();
$num = $result->rowCount();

$arr = ['data' => []];

if($num > 0)
{
	while($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$item = [
			'id' => $id,
			'restaurant' => $restaurant_name,
			'total' => $total_amount,
			'date' => $order_datetime,
			'status' => $status_value,
			'status_id' => $order_status_id
		];
		array_push($arr['data'], $item);
	}
}

echo json_encode($arr);
?>
