<?php
/* ************************************************************************** */
/*     File: src\api\user\get_entity_id.php                                   */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/05 11:21:22                                           */
/*     Updated: 2026/02/05 11:21:38                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/User.php';

session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// 1. Guard Clause: Must be logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(["message" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// 2. Connect to DB
$database = new DbConnection();
$db = $database->connect();

// 3. Use the new class
$finder = new User($db);
$result = $finder->findEntityId($user_id, $role);

// 4. Handle the result
if ($result['status'] === 'success')
{
    $_SESSION['entity_id'] = $result['entity_id'];
    echo json_encode($result);
}
else
{
    http_response_code($result['status'] === 'error' ? 500 : 400);
    echo json_encode($result);
}
?>

