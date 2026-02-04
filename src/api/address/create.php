<?php
/* ************************************************************************** */
/*     File: api\address\create.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:37:02                                           */
/*     Updated: 2026/01/30 16:37:49                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$data = json_decode(file_get_contents("php://input"));

// Validation: We MUST have a customer_id to link the address to!
if(
    !empty($data->address_line1) && 
    !empty($data->city) && 
    !empty($data->country_id) && 
    !empty($data->customer_id) // <--- CRITICAL NEW REQUIREMENT
)
{
    // 1. Set Address Data
    $address->unit_number = isset($data->unit_number) ? $data->unit_number : '';
    $address->street_number = isset($data->street_number) ? $data->street_number : '';
    $address->address_line1 = $data->address_line1;
    $address->address_line2 = isset($data->address_line2) ? $data->address_line2 : '';
    $address->city = $data->city;
    $address->region = isset($data->region) ? $data->region : '';
    $address->postal_code = $data->postal_code;
    $address->country_id = $data->country_id;

    // 2. Set Link Data (For the Cross Ref Table)
    $address->customer_id = $data->customer_id;

    // 3. Call the Model
    $new_id = $address->create();

    if($new_id)
    {
        http_response_code(201);
        echo json_encode(array(
            "message" => "Address Created Successfully",
            "id" => $new_id
        ));
    }
    else
    {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create address. Database transaction failed."));
    }
}
else
{
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create address. Data is incomplete (Missing Customer ID or Address fields)."));
}
?>