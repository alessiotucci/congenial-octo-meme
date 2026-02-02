<?php
/* ************************************************************************** */
/*     File: api\customer\update.php                                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 23:13:33                                           */
/*     Updated: 2026/01/30 23:13:35                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */
// api/customer/update.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Customer.php';

$database = new DbConnection();
$db = $database->connect();
$customer = new Customer($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    $customer->id = $data->id;
    $customer->first_name = $data->first_name;
    $customer->last_name = $data->last_name;
    $customer->nick_name = $data->nick_name;
    $customer->phone_number_original = $data->phone_number_original ?? '';

    if($customer->update()) {
        echo json_encode("Customer updated.");
    } else {
        http_response_code(503);
        echo json_encode("Customer could not be updated.");
    }
} else {
    http_response_code(400);
    echo json_encode("Missing Customer ID.");
}
?>

