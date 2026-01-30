<?php
/* ************************************************************************** */
/*     File: api\customer\read_single.php                                     */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 23:13:21                                           */
/*     Updated: 2026/01/30 23:13:23                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/customer/read_single.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Customer.php';

$database = new DbConnection();
$db = $database->connect();
$customer = new Customer($db);

$customer->id = isset($_GET['id']) ? $_GET['id'] : die();

if($customer->read_single()) {
    $cust_arr = [
        'id' => $customer->id,
        'first_name' => $customer->first_name,
        'last_name' => $customer->last_name,
        'nick_name' => $customer->nick_name,
        'phone' => $customer->phone_number_original,
        'email' => $customer->email
    ];
    echo json_encode($cust_arr);
} else {
    http_response_code(404);
    echo json_encode("Customer not found.");
}
?>

