<?php
/* ************************************************************************** */
/*     File: api\food_order\cancel.php                                        */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/01 01:19:22                                           */
/*     Updated: 2026/02/01 01:19:24                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT'); // PUT because we are updating, not deleting

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodOrder.php';

$database = new DbConnection();
$db = $database->connect();
$order = new FoodOrder($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    $order->id = $data->id;

    if($order->cancelOrder()) {
        echo json_encode("Order #".$order->id." has been cancelled.");
    } else {
        http_response_code(503);
        echo json_encode("Could not cancel order.");
    }
} else {
    http_response_code(400);
    echo json_encode("Missing Order ID.");
}
?>

