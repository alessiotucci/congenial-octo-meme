<?php
/* ************************************************************************** */
/*     File: api\address\read_single.php                                      */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 15:37:38                                           */
/*     Updated: 2026/01/30 17:22:03                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// api/address/read_single.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php';

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$address->id = isset($_GET['id']) ? $_GET['id'] : die('Did not get an id');

if($address->read_single())
{
	$addr_arr = array(
		'id' => $address->id,
		'unit' => $address->unit_number,
		'line1' => $address->address_line1,
		'city' => $address->city,
		'zip' => $address->postal_code
	);
	http_response_code(200);
	echo json_encode($addr_arr);
}
else
{
	http_response_code(404);
	echo json_encode("Address not found.");
}
?>
