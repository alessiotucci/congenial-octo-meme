/* ************************************************************************** */
/*     File: api\user\update.php                                              */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 15:04:04                                           */
/*     Updated: 2026/01/26 15:33:13                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

<?php

// always start off with the headers
header('Access-Control-Allow-Origin: *');
header('Content-Type: applicaton/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/User.php';

$database = new DbConnection();
$db = $database->connect();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));
if (!empty($data->id))
{
	$user->id = $data->id;
	$user->email = $data->email;
	$user->role = $data->role;

	if ($user->update())
	{
		http_response_code(200);
		echo json_encode("User updated!");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Failure: user not updated.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Error: missing ID.");
}
?>
