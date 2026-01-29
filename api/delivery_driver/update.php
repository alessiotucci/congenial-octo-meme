<?php
/* ************************************************************************** */
/*     File: api\delivery_driver\update.php                                   */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/28 17:11:56                                           */
/*     Updated: 2026/01/28 17:12:09                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/DeliveryDriver.php';

$database = new DbConnection();
$db = $database->connect();
$driver = new DeliveryDriver($db);

$data = json_decode(file_get_contents("php://input"));

// We need ID to update, plus at least one field
if(!empty($data->id) && (!empty($data->first_name) && !empty($data->last_name))) 
{
    $driver->id = $data->id;
    $driver->first_name = $data->first_name;
    $driver->last_name = $data->last_name;
    $driver->phone_number_original = $data->phone_number_original;

    if($driver->update()) {
        http_response_code(200);
        echo json_encode("Driver Profile updated.");
    } else {
        http_response_code(503);
        echo json_encode("Unable to update driver.");
    }
} else {
    http_response_code(400);
    echo json_encode("Incomplete data. Need ID, Name, Phone.");
}

?>
