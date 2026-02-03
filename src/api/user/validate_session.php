<?php
/* ************************************************************************** */
/*     File: src\api\user\validate_session.php                                */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/03 17:08:58                                           */
/*     Updated: 2026/02/03 17:11:01                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


header('Access-Control-Allow-Origin: https://localhost'); 
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Credentials: true'); // Still needed for Cookies!

include_once '../../config/session_auth.php';

start_secure_session();

if (isset($_SESSION['user_id']))
{
	http_response_code(200);
	echo json_encode(array(
		"is_logged_in" => true,
		"user_id" => $_SESSION['user_id'],
		"role" => $_SESSION['role'],
		"csrf_token" => $_SESSION['csrf_token']
	));
}
else
{
	http_response_code(200); // 200 OK, but false payload
	echo json_encode(array("is_logged_in" => false));
}
?>
