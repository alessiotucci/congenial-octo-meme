<?php
/* ************************************************************************** */
/*     File: api\delivery_driver\read_single.php                              */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/27 17:38:06                                           */
/*     Updated: 2026/01/27 18:51:48                                           */
/*     System: unknown [DESKTOP-12TRQA8]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/DeliveryDriver.php';

$database = new DbConnection();
$db = $database->connect();

$delivery_driver = new DeliveryDriver($db);

$delivery_driver->id = isset($_GET['id']) ? $_GET['id'] : die('Didnt get an id!'); // TODO
if ($delivery_driver->read_single())
{
	$delivery_driver_arr = array(
				'id' => $delivery_driver->id,
				'first_name' => $delivery_driver->first_name,
				'last_name' => $delivery_driver->last_name,
				'rating' => $delivery_driver->rating,
				'phone_number_normalized' => $delivery_driver->phone_number_normalized,
				'email' => $delivery_driver->email,
				'role' => $delivery_driver->role,
				'created_at' => $delivery_driver->created_at,
			);
	http_response_code(200);
	echo json_encode($delivery_driver_arr);
}
else
{
	http_response_code(404);
	echo json_encode("Error: Delivery Driver not found.");
}
?>
