<?php
/* ************************************************************************** */
/*     File: src\api\food_order\read_for_driver.php                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/04 18:02:18                                           */
/*     Updated: 2026/02/04 18:02:19                                           */
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

if(!isset($_GET['id'])) { // Driver ID
    http_response_code(400);
    echo json_encode(["message" => "Missing Driver ID"]);
    exit();
}

$result = $order->read_for_driver($_GET['id']);
$num = $result->rowCount();

$arr = ['data' => []];

if($num > 0) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $item = [
            'id' => $id,
            'delivery_fee' => $delivery_fee,
            'assigned_driver_id' => $assigned_driver_id,
            'restaurant_name' => $restaurant_name,
            'customer_name' => $first_name . ' ' . $last_name,
            'address_line1' => $address_line1,
            'city' => $city,
            'item_count' => $item_count
        ];
        array_push($arr['data'], $item);
    }
}

echo json_encode($arr);
?>