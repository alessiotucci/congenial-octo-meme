<?php
/* ************************************************************************** */
/*     File: api\food_order\create.php                                        */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 16:25:52                                           */
/*     Updated: 2026/01/23 16:25:55                                           */
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
include_once '../../models/FoodOrder.php'; //TODO: saving up for later

$database = new DbConnection();
$db = $database->connect();

$foodOrder = new FoodOrder($db);

$data = json_decode(file_get_contents("php://input"));

//TODO: finish up the data chcking, I'll do it tomorrow
if (!empty($data->customer_id) && !empty($data->food_place_id)
	&& !empty($data->customer_address_id) && !empty($data->order_status_id)
	&& !empty($data->) && !empty($data->)
	&& !empty($data->) && !empty($data->) && !empty($data->) )
{

}
else
{
	http_response_code(400);
	echo json_encode("Unable to create the Order. Data is incomplete.");
}
?>
