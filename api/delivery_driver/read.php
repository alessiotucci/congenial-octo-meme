<?php
/* ************************************************************************** */
/*     File: read.php                                                         */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/27 11:42:58                                           */
/*     Updated: 2026/01/27 12:12:16                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
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

$result = $delivery_driver->read();
$num = $result->rowCount();

if ($num > 0)
{
	$delivery_driver_arr = array();
	$delivery_driver_arr['data'] = array(); //TODO
	
	while ($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$item = array(
					'id' => $id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'rating' => $rating,
					'phone_number_normalized' => $phone_number_normalized,
					'email' => $email,
					'role' => $role,
					'created_at' => $created_at
				);
		array_push($delivery_driver_arr['data'], $item);
	}
	http_response_code(200);
	echo json_encode($delivery_driver_arr);
}
else
{
	http_response_code(404);
	echo json_encode("No delivery drivers found.");
}
?>
