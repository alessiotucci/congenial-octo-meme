<?php
/* ************************************************************************** */
/*     File: src\api\user\logout.php                                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/03 17:27:54                                           */
/*     Updated: 2026/02/03 17:29:04                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// 1. HEADERS (Allow CORS so JS can call it)
header('Access-Control-Allow-Origin: https://localhost');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Credentials: true');

include_once '../../config/session_auth.php';

start_secure_session();
$_SESSION = array();

if (ini_get("session.use_cookies"))
{
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
	);
}
session_destroy();
http_response_code(200);
echo json_encode(array("message" => "Logged out successfully"));
?>
