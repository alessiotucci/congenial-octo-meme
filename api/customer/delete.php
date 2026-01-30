<?php
/* ************************************************************************** */
/*     File: api\customer\delete.php                                          */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 23:13:50                                           */
/*     Updated: 2026/01/30 23:13:52                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */
// api/customer/delete.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');
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

    $result = $customer->delete();

    if($result === true) {
        echo json_encode("Customer deleted.");
    } elseif ($result === "BUSY") {
        http_response_code(409); // Conflict
        echo json_encode("Cannot delete: Customer has active orders.");
    } else {
        http_response_code(503);
        echo json_encode("Delete failed.");
    }
} else {
    http_response_code(400);
    echo json_encode("Missing ID.");
}
?>

