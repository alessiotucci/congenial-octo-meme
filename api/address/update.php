<?php
/* ************************************************************************** */
/*     File: api\address\update.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:37:54                                           */
/*     Updated: 2026/01/30 16:34:21                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// api/address/update.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$data = json_decode(file_get_contents("php://input"));

//TODO: double check this part
if(!empty($data->id))
{
	$address->id = $data->id;
	$address->unit_number = $data->unit_number;
	$address->street_number = $data->street_number;
	$address->address_line1 = $data->address_line1;
	$address->address_line2 = $data->address_line2;
	$address->city = $data->city;
	$address->region = $data->region;
	$address->postal_code = $data->postal_code;
	$address->country_id = $data->country_id;

	if($address->update())
	{
		http_response_code(200);
		echo json_encode("Address Updated Successfully.");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Address could not be updated.");
	}
}
else
{
		http_response_code(400);
	echo json_encode("Missing  ID.");
}
?>
