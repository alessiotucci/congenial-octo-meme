<?php
/* ************************************************************************** */
/*	 File: api\food_order\create.php										*/
/*	 Author: atucci <atucci@student.42.fr>								  */
/*	 Created: 2026/01/23 16:25:52										   */
/*	 Updated: 2026/01/23 16:25:55										   */
/*	 System: WindowsNT [DESKTOP-TQURMND]									*/
/*	 Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...	 */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,
		Content-Type, Access-Control-Allow-Methods, Authorization,
		X-Requested-With');


include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodOrder.php'; //TODO: saving up for later

$database = new DbConnection();
$db = $database->connect();

$foodOrder = new FoodOrder($db);

$data = json_decode(file_get_contents("php://input"));
// We check only the CRITICAL fields.
// We do NOT check for driver_id or ratings because a new order won't have them yet.
if (!empty($data->customer_id) && !empty($data->food_place_id)
	&& !empty($data->customer_address_id) && !empty($data->total_amount))
{
	// Assign Data
	$foodOrder->customer_id = $data->customer_id;
	$foodOrder->food_place_id = $data->food_place_id;
	$foodOrder->customer_address_id = $data->customer_address_id;
	$foodOrder->total_amount = $data->total_amount;

	// Defaults
	// If status is missing, default to '1' (Pending)
	$foodOrder->order_status_id = $data->order_status_id ?? 1;

	// If fee is missing, default to 0
	$foodOrder->delivery_fee = $data->delivery_fee ?? 0.00;

	// If time is missing, use current time
	$foodOrder->order_datetime = $data->order_datetime ?? date('Y-m-d H:i:s');
	$foodOrder->requested_delivery_time = $data->requested_delivery_time ?? date('Y-m-d H:i:s', strtotime('+1 hour'));

	// Optional Fields (Driver/Ratings)
	$foodOrder->assigned_driver_id = $data->assigned_driver_id ?? null;
	$foodOrder->cust_driver_rating = $data->cust_driver_rating ?? null;
	$foodOrder->cust_restaurant_rating = $data->cust_restaurant_rating ?? null;

	// Create Order
	$new_order_id = $foodOrder->create();

	if ($new_order_id)
	{
		http_response_code(201);
		echo json_encode(array(
			"message" => "Order Created.",
			"id" => $new_order_id // We send this back so the Frontend can start adding items!
		));
	}
	else
	{
		http_response_code(503);
		echo json_encode(array("message" => "Unable to create order."));
	}
}
else
{
	http_response_code(400);
	echo json_encode(array("message" => "Unable to create Order. Data is incomplete."));
}
?>
