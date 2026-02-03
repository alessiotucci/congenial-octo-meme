<?php
/* ************************************************************************** */
/*                                                                            */
/*   create.php                                         :+:      :+:    :+:   */
/*   File: create.php                                                         */
/*   Created: 2026/01/21 12:21:22 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:21:24                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/food_place/create.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$place = new FoodPlace($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->user_id) && !empty($data->name) && !empty($data->city))
{
	
	// --- Food Place Data ---
	$place->user_id = $data->user_id;
	$place->name = $data->name;
	
	// Optional fields: Use defaults if missing
	$place->food_type = $data->food_type ?? '';
	$place->description = $data->description ?? '';
	$place->opening_hours = $data->opening_hours ?? '';
	
	// --- Address Data ---
	$place->address->address_line1 = $data->address_line1;
	$place->address->street_number = $data->street_number;
	$place->address->city = $data->city;
	$place->address->postal_code = $data->postal_code;
	$place->address->country_id = $data->country_id ?? 1; // Default to 1 if missing
	$place->address->address_line2 = $data->address_line2 ?? null;
	$place->address->unit_number = $data->unit_number ?? null;
	$place->address->region = $data->region ?? '';

	// 3. Create (Trigger Transaction)
	if($place->createWithAddress())
	{
		http_response_code(201);
		echo json_encode(array(
			"message" => "Food Place Created successfully",
			"id" => $place->id,
			"address_id" => $place->address->id // Optional, but useful
		));
	}
	else
	{
		http_response_code(503);
		echo json_encode(array("message" => "Unable to create Food Place."));
	}
}
else
{
    http_response_code(400);
    echo json_encode("Incomplete data. Name, User ID, and City are required.");
}
?>