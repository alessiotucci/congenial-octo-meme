<?php
/* ************************************************************************** */
/*     File: api\customer\read.php                                            */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/30 23:13:11                                           */
/*     Updated: 2026/01/30 23:13:12                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// api/customer/read.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Customer.php';

$database = new DbConnection();
$db = $database->connect();
$customer = new Customer($db);

$result = $customer->read();
$num = $result->rowCount();

if($num > 0) {
    $cust_arr = []; // Short array syntax
    $cust_arr['data'] = [];

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $cust_item = [
            'id' => $id,
            'full_name' => $first_name . ' ' . $last_name,
            'nick_name' => $nick_name,
            'email' => $email, // From the JOIN
            'phone' => $phone_number_original
        ];
        array_push($cust_arr['data'], $cust_item);
    }
    echo json_encode($cust_arr);
} else {
    echo json_encode("No customers found.");
}
?>