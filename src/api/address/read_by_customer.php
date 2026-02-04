<?php
/* ************************************************************************** */
/*     File: src\api\address\read_by_customer.php                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/04 14:49:05                                           */
/*     Updated: 2026/02/04 14:52:50                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

// 1. Validation
if(!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing Customer ID."]);
    exit();
}

// 2. Set ID on the Model
// This ID will be used in the JOIN clause inside the model
$address->customer_id = $_GET['id'];

// 3. Call the Model Method
$result = $address->read_by_customer();
$num = $result->rowCount();

$address_arr = [];
$address_arr['data'] = [];

if($num > 0) {
    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        // Populate the array with the specific columns we selected
        $address_item = [
            'id' => $id,
            'address_line1' => $address_line1,
            'address_line2' => $address_line2,
            'city' => $city,
            'postal_code' => $postal_code
        ];

        array_push($address_arr['data'], $address_item);
    }
}

// Always return 200 with data array
http_response_code(200);
echo json_encode($address_arr);
?>