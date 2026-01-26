/* ************************************************************************** */
/*     File: api\user\delete.php                                              */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 15:11:08                                           */
/*     Updated: 2026/01/26 15:34:44                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With'); //TODO:

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
	
	if ($user->delete())
	{
		http_response_code(200);
		echo json_encode("User deleted, nice");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Failed: User not deleted");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Error: missing ID!");
}
?>
