<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: read.php                                                           */
/*   Created: 2026/01/21 12:22:12 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:22:46                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/food_place/read.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';

$database = new DbConnection();
$db = $database->connect();
$place = new FoodPlace($db);

$result = $place->read();
$num = $result->rowCount();

if($num > 0)
{
	$places_arr = array();
	$places_arr['data'] = array();

	while($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$place_item = array(
			'id' => $id,
			'name' => $name,
			'type' => $food_type,
			'rating' => $average_rating,
			'location (city + address1)' => $city . ', ' . $address_line1
			//TODO: Combined for easy display
		);
		array_push($places_arr['data'], $place_item);
	}
	http_response_code(200);
	echo json_encode($places_arr);
}
else
{
	http_response_code(404);
	echo json_encode('No Food Places found.');
}
?>
