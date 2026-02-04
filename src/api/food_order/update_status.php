<?php
/* ************************************************************************** */
/*     File: api\food_order\update_status.php                                 */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/01 01:19:01                                           */
/*     Updated: 2026/02/01 01:19:03                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodOrder.php';

$database = new DbConnection();
$db = $database->connect();
$order = new FoodOrder($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id) && !empty($data->status_id)) {
    $order->id = $data->id;
    $order->order_status_id = $data->status_id;

    if($order->updateStatus())
	{
        echo json_encode("Order status updated.");
    }
	else
	{
        http_response_code(503);
        echo json_encode("Could not update status.");
    }
} else {
    http_response_code(400);
    echo json_encode("Missing Order ID or Status ID.");
}
?>

