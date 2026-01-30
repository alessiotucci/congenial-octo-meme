<?php
/* ************************************************************************** */
/*     File: api\customer\create.php                                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:32:32                                           */
/*     Updated: 2026/01/23 17:32:32                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');


include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Customer.php'; 

$database = new DbConnection();
$db = $database->connect();

$customer = new Customer($db);
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->first_name) && !empty($data->last_name)
	&& !empty($data->phone_number_original) && !empty($data->user_id))
{
	$customer->first_name = $data->first_name;
	$customer->last_name = $data->last_name;
	$customer->nick_name = $data->nick_name ?? ''; //optionals
	$customer->phone_number_original = $data->phone_number_original;
	$customer->phone_number_normalized = $data->phone_number_normalized ?? '';
	$customer->user_id = $data->user_id; // this is a FK
	if ($customer->create())
	{
		http_response_code(201);
		echo json_encode("Created Customer!");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Unable to create a Customer. Internal error.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Unable to create a Customer. Data is incomplete.");
}

?>
