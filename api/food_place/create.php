/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: create.php                                                         */
/*   Created: 2026/01/21 12:21:22 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:21:24                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

//create endpoint for the food place
<?php
// api/food_place/create.php

// 1. HEADERS (Standard REST API Headers)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// 2. INCLUDES
// We go up two levels (../../) because we are in api/food_place/
include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';

// 3. DATABASE CONNECTION
$database = new DbConnection();
$db = $database->connect();

// 4. INSTANTIATE THE OBJECT
$foodPlace = new FoodPlace($db);

// 5. GET RAW JSON DATA
$data = json_decode(file_get_contents("php://input"));

// 6. VALIDATE DATA
// We check if the mandatory fields are present
if(!empty($data->name) && !empty($data->address_line1)
	&& !empty($data->country_id) && !empty($data->food_type))
{
    // 7. ASSIGN DATA TO THE OBJECT
    // FoodPlace properties
    $foodPlace->name = $data->name;
    $foodPlace->food_type = $data->food_type;
    $foodPlace->description = $data->description ?? ''; // Optional
    $foodPlace->opening_hours = $data->opening_hours ?? ''; // Optional

    // Address properties (These get passed down to the Address class inside createWithAddress)
    $foodPlace->unit_number = $data->unit_number ?? '';
    $foodPlace->street_number = $data->street_number ?? '';
    $foodPlace->address_line1 = $data->address_line1;
    $foodPlace->address_line2 = $data->address_line2 ?? '';
    $foodPlace->region = $data->region ?? '';
    $foodPlace->postal_code = $data->postal_code ?? '';
    $foodPlace->country_id = $data->country_id;

    // 8. EXECUTE THE CREATION LOGIC
    if($foodPlace->createWithAddress())
	{
        // Success
        http_response_code(201); // 201 = Created
        echo json_encode("Food Place and Address created successfully.");
    }
	else
	{
        // Server Error
        http_response_code(503); // 503 = Service Unavailable
        echo json_encode("Unable to create Food Place.");
    }
}
else
{
    // User Error (Missing Data)
    http_response_code(400); // 400 = Bad Request
    echo json_encode("Unable to create Food Place. Data is incomplete.");
}
?>
