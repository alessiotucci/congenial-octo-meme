<?php
/* ************************************************************************** */
/*     File: api\customer\create.php                                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:32:32                                           */
/*     Updated: 2026/01/23 17:32:32                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */


header('Access-Control-Allow-Origin: *'); // Or your specific localhost URL
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Customer.php'; 

$database = new DbConnection();
$db = $database->connect();

$customer = new Customer($db);
$data = json_decode(file_get_contents("php://input"));

// 1. VALIDATION
if (empty($data->first_name) || empty($data->last_name) || empty($data->phone_number_original) || empty($data->user_id))
{
    http_response_code(400);
    echo json_encode(array("message" => "Missing required data (Name, Phone, or User ID)."));
    exit();
}

// 2. ASSIGN DATA
$customer->first_name = $data->first_name;
$customer->last_name = $data->last_name;
$customer->user_id = $data->user_id; // FK
$customer->phone_number_original = $data->phone_number_original;

// Optional fields with defaults
$customer->nick_name = $data->nick_name ?? ''; 
$customer->phone_number_normalized = $data->phone_number_normalized ?? '';

// 3. CREATE
// Capture the returned ID into a variable
$new_customer_id = $customer->create();
if ($new_customer_id)
{
    http_response_code(201);
    // Return the Captured ID
    echo json_encode(array(
        "message" => "Success! Created a new customer.",
        "id" => $new_customer_id,
        "user_id" => $customer->user_id
    ));
}
else
{
    http_response_code(503);
    echo json_encode(array("message" => "Unable to create customer."));
}
?>