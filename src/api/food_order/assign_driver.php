<?php
/* ************************************************************************** */
/*     File: api\food_order\assign_driver.php                                 */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/01 01:18:41                                           */
/*     Updated: 2026/02/01 01:18:43                                           */
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

if(!empty($data->id) && !empty($data->driver_id))
{
    
    $order->id = $data->id;
    $order->assigned_driver_id = $data->driver_id;

    if($order->assignDriver())
	{
        http_response_code(200);
        echo json_encode(["message" => "Job Accepted! Drive safe."]);
    } else {
        // This happens if the job was already taken by someone else
        http_response_code(409); // Conflict
        echo json_encode(["message" => "Job unavailable. It may have been taken."]);
    }
} else {
    http_response_code(400);
    echo json_encode(["message" => "Incomplete Data."]);
}
?>