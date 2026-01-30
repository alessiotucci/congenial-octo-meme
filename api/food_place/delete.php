<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: delete.php                                                         */
/*   Created: 2026/01/21 12:24:12 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:24:32                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/food_place/delete.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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
	$result = $place->delete();

	if($result === true)
	{
		//TODO: WHICH STATUS CODE FORE A GOOD DELETE?
		echo json_encode("Food Place Deleted.");
	}
	elseif ($result === "BUSY")
	{
		http_response_code(409); // Conflict
		echo json_encode("Cannot delete: Active orders exist.");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Delete failed.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Missing ID.");
}
?>
