<?php
/* ************************************************************************** */
/*     File: api\delivery_driver\delete.php                                   */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/28 17:12:38                                           */
/*     Updated: 2026/01/28 17:12:49                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/DeliveryDriver.php';

$database = new DbConnection();
$db = $database->connect();
$driver = new DeliveryDriver($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id))
{
    $driver->id = $data->id;

    // The delete method in the class handles the logic:
    // It returns FALSE if orders exist (Strict Mode)
    if($driver->delete())
	{
        http_response_code(200);
        echo json_encode("Driver Profile deleted. User account remains active.");
    }
	else
	{
        // We assume failure here means the "Strict Mode" check failed
        http_response_code(400); // 400 Bad Request is appropriate for logic violation
        echo json_encode("Cannot delete driver. They have active orders or ID does not exist.");
    }
}
else
{
    http_response_code(400);
    echo json_encode("Missing Driver ID.");
}
?>
