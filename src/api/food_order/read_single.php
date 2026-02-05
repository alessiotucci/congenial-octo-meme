<?php
/* ************************************************************************** */
/*     File: api\food_order\read_single.php                                   */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/01 01:18:21                                           */
/*     Updated: 2026/02/01 01:18:23                                           */
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

$order->id = isset($_GET['id']) ? $_GET['id'] : die();

if($order->read_single()) {
    
    $order_arr = array(
        'id' => $order->id,
        'status_id' => $order->order_status_id,
        'status_name' => $order->status_name, // Added this useful label
        'total' => $order->total_amount,
        'delivery_fee' => $order->delivery_fee,
        'ordered_at' => $order->order_datetime,
        'driver_id' => $order->assigned_driver_id,
        
        // NEW USEFUL DATA
        'restaurant_name' => $order->restaurant_name,
        'customer_name' => $order->customer_name,
        'delivery_address' => $order->delivery_address,

        'items' => $order->items_list 
    );
    
    echo json_encode($order_arr);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Order not found."]);
}
?>
