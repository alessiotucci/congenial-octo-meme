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

// read a single food_place, method GET
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';

$database = new DbConnection();
$db = $database->connect();
$delivery_driver = new FoodPlace($db);

$result = $food_place->read();
$num = $result->rowCount();

if ($num > 0)
{
	$food_place_arr = array();
	$food_place_arr['data'] = array(); //TODO
	
	while ($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$item = array(
					'id' => $id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'rating' => $rating,
					'phone_number_normalized' => $phone_number_normalized,
					'email' => $email,
					'role' => $role,
					'created_at' => $created_at,
				);
		array_push($food_place_arr['data'], $item);
	}
	http_response_code(200);
	echo json_encode($food_place_arr);
}
else
{
	http_response_code(404);
	echo json_encode("No delivery drivers found.");
}
?>
