<?php
/* ************************************************************************** */
/*     File: api\address\create.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:37:02                                           */
/*     Updated: 2026/01/30 16:37:49                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/address/create.php
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

// Validation: Check only critical fields (City and Address Line 1 are usually mandatory)
if(!empty($data->address_line1) && !empty($data->city) && !empty($data->country_id))
{
	
	$address->unit_number = $data->unit_number;
	$address->street_number = $data->street_number;
	$address->address_line1 = $data->address_line1;
	$address->address_line2 = $data->address_line2;
	$address->city = $data->city;
	$address->region = $data->region;
	$address->postal_code = $data->postal_code;
	$address->country_id = $data->country_id;

	$new_id = $address->create();

	if($new_id)
	{
		http_response_code(201);
		echo json_encode("Address Created Successfully $new_id");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Unable to create address. Database error.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Unable to create address. Data is incomplete.");
}
?>

