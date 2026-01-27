<?php
/* ************************************************************************** */
/*     File: api\user\read_single.php                                         */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 14:51:54                                           */
/*     Updated: 2026/01/27 17:52:21                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/User.php';

$database = new DbConnection();
$db = $database->connect();

$user = new User($db);

$user->id = isset($_GET['id']) ? $_GET['id'] : die('Didnt get an id'); // TODO
if ($user->read_single())
{
	$user_arr = array(
				'id' => $user->id,
				'email' => $user->email,
				'role' => $user->role,
				'created_at' => $user->created_at
			);
	http_response_code(200);
	echo json_encode($user_arr);
}
else
{
	http_response_code(404);
	echo json_encode("Error: User not found.");
}
?>
