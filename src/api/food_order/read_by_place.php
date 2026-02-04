<?php
/* ************************************************************************** */
/*     File: src\api\food_order\read_by_place.php                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/04 16:47:02                                           */
/*     Updated: 2026/02/04 16:47:49                                           */
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

if(!isset($_GET['id']))
{
	http_response_code(400);
	echo json_encode(["message" => "Missing Food Place ID"]);
	exit();
}
$order->food_place_id = $_GET['id'];
$data = $order->read_incoming_orders();
echo json_encode(['data' => $data]);
?>
