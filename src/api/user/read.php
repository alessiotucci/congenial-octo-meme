<?php
/* ************************************************************************** */
/*     File: api\user\read.php                                                */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 13:23:44                                           */
/*     Updated: 2026/01/27 10:31:08                                           */
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

$result = $user->read();
$num = $result->rowCount();

if ($num > 0)
{
	$user_arr = array();
	$user_arr['data'] = array(); //TODO
	
	while ($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$item = array(
					'id' => $id,
					'email' => $email,
					'role' => $role,
					'created_at' => $created_at
				);
		array_push($user_arr['data'], $item);
	}
	http_response_code(200);
	echo json_encode($user_arr);
}
else
{
	http_response_code(404);
	echo json_encode("No users found.");
}
?>
