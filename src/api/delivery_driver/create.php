<?php
/* ************************************************************************** */
/*     File: api\delivery_drivers\create.php                                  */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 16:31:34                                           */
/*     Updated: 2026/01/27 12:06:37                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/DeliveryDriver.php'; 

$database = new DbConnection();
$db = $database->connect();

$deliveryDriver = new DeliveryDriver($db);
$data = json_decode(file_get_contents("php://input"));

// 1. VALIDATION: Check strictly for required fields
if (
    empty($data->first_name) || 
    empty($data->last_name) || 
    empty($data->phone_number_original) || 
    empty($data->user_id)
) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Incomplete data. First Name, Last Name, Phone, and User ID are required."
    ));
    exit(); // Stop execution here
}

// 2. ASSIGN DATA
$deliveryDriver->first_name = $data->first_name;
$deliveryDriver->last_name = $data->last_name;
$deliveryDriver->phone_number_original = $data->phone_number_original;
$deliveryDriver->user_id = $data->user_id;
$deliveryDriver->rating = $data->rating ?? 0.0;

$new_driver_id = $deliveryDriver->create();

if ($new_driver_id)
{
    http_response_code(201); 
    // Return a clean JSON Object
    echo json_encode(array(
        "message" => "Delivery Driver Profile Created!",
        "id" => $new_driver_id,
        "user_id" => $deliveryDriver->user_id
    ));
}
else
{
    http_response_code(503); 
    echo json_encode(array("message" => "Unable to create Delivery Driver. Internal DB error."));
}
?>