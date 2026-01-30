<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: update.php                                                         */
/*   Created: 2026/01/21 12:23:44 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:23:59                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/food_place/update.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';

$database = new DbConnection();
$db = $database->connect();
$place = new FoodPlace($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id))
{
	$place->id = $data->id;
	
	// Place Data
	$place->name = $data->name;
	$place->food_type = $data->food_type;
	$place->description = $data->description;
	$place->opening_hours = $data->opening_hours;

	// Address Data (For in-place update)
	$place->address->unit_number = $data->unit_number;
	$place->address->street_number = $data->street_number;
	$place->address->address_line1 = $data->address_line1;
	$place->address->address_line2 = $data->address_line2;
	$place->address->city = $data->city;
	$place->address->region = $data->region;
	$place->address->postal_code = $data->postal_code;
	$place->address->country_id = $data->country_id;

	if($place->update())
	{
		echo json_encode("Food Place and Address Updated.");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Update failed.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Missing ID.");
}
?>
