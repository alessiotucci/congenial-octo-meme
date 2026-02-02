<?php
/* ************************************************************************** */
/*     File: api\food_order\create.php                                        */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 16:25:52                                           */
/*     Updated: 2026/01/31 17:56:29                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodOrder.php';

$database = new DbConnection();
$db = $database->connect();
$order = new FoodOrder($db);

$data = json_decode(file_get_contents("php://input"));

// 1. Basic Validation
if(
	!empty($data->customer_id) && 
	!empty($data->food_place_id) && 
	!empty($data->customer_address_id) &&
	!empty($data->items) && is_array($data->items) // Ensure items is a list
)
{
	$order->customer_id = $data->customer_id;
	$order->food_place_id = $data->food_place_id;
	$order->customer_address_id = $data->customer_address_id;
	$order->requested_delivery_time = $data->requested_delivery_time;
	$order->delivery_fee = isset($data->delivery_fee) ? $data->delivery_fee : 0.00; // Default free delivery

	// 2. Call Create (Pass the Items Array)
	// We do NOT pass 'total_amount'. The class calculates it.
	if($order->create($data->items))
	{
		http_response_code(201);
		echo json_encode(array(
			"message" => "Order Placed Successfully.",
			"order_id" => $order->id,
			"total_charged" => $order->total_amount // Return the calculated total so user knows
		));
	}
	else
	{
		http_response_code(503);
		echo json_encode("Unable to place order. Transaction failed.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Incomplete Data. Items array required.");
}
?>
