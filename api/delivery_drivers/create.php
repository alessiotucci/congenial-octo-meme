<?php
/* ************************************************************************** */
/*     File: api\delivery_drivers\create.php                                  */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 16:31:34                                           */
/*     Updated: 2026/01/23 16:31:34                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,
		Content-Type, Access-Control-Allow-Methods, Authorization,
		X-Requested-With');


include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/DeliveryDriver.php'; 

$database = new DbConnection();
$db = $database->connect();

$deliveryDriver = new DeliveryDriver($db);
$data = json_decode(file_get_contents("php://input"));

if ( !empty($data->first_name) && !empty($data->last_name)
		&& !empty($data->phone_number_original) && !empty($data->user_id))
{
		$deliveryDriver->first_name = $data->first_name;
		$deliveryDriver->last_name = $data->last_name;
		$deliveryDriver->phone_number_original = $data->phone_number_original;
		$deliveryDriver->phone_number_normalized = $data->phone_number_normalized;
		$deliveryDriver->rating = $data->rating;
		$deliveryDriver->user_id = $data->user_id ; //FK
		if ($deliveryDriver->create())
		{
			http_response_code(201); 
			echo json_encode("Created Delivery Driver!");
		}
		else
		{
			http_response_code(503); 
			echo json_encode("Unable to create Delivery Driver. Internal error.");

		}
}
else
{
	http_response_code(400); 
	echo json_encode("Unable to create Delivery Driver. Data is incomplete.");
}
?>
