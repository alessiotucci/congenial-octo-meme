<?php
/* ************************************************************************** */
/*     File: api\address\read.php                                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:37:23                                           */
/*     Updated: 2026/01/30 16:26:49                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/address/read.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$result = $address->read();
$num = $result->rowCount();

if($num > 0)
{
	$addr_arr = array();
	$addr_arr['data'] = array();

	//TODO: add more data from the result
	// address_line2
	// region
	// and so on
	while($row = $result->fetch(PDO::FETCH_ASSOC))
	{
		extract($row);
		$addr_item = array(
			'id' => $id,
			'street_number' => $street_number,
			'address_line1' => $address_line1,
			'city' => $city,
			'country_id' => $country_id
		);
		array_push($addr_arr['data'], $addr_item);
	}
	http_response_code(200);
	echo json_encode($addr_arr);
}
else
{
	http_response_code(404);
	echo json_encode("no addresses found.");
}
?>
