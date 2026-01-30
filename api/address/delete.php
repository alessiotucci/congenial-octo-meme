<?php
/* ************************************************************************** */
/*     File: api\address\delete.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:38:10                                           */
/*     Updated: 2026/01/30 16:31:58                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/address/delete.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id))
{
	$address->id = $data->id;
	if($address->delete())
	{
		http_response_code(200);
		echo json_encode("Address Deleted. NICE!");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Address could not be deleted.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Missing ID.");
}
?>
