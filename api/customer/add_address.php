<?php
/* ************************************************************************** */
/*     File: api\customer\add_address.php                                     */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 18:02:28                                           */
/*     Updated: 2026/01/31 18:03:13                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/Address.php'; 

$database = new DbConnection();
$db = $database->connect();
$address = new Address($db);

$data = json_decode(file_get_contents("php://input"));

// We need a Customer ID to link to, plus the Address data
if(!empty($data->customer_id) && !empty($data->city) && !empty($data->address_line1)) {
    
    try {
        $db->beginTransaction();

        // 1. Create the Address (using existing Model)
        $address->unit_number = $data->unit_number;
        $address->street_number = $data->street_number;
        $address->address_line1 = $data->address_line1;
        $address->address_line2 = $data->address_line2;
        $address->city = $data->city;
        $address->region = $data->region;
        $address->postal_code = $data->postal_code;
        $address->country_id = $data->country_id;

        $new_addr_id = $address->create();

        if(!$new_addr_id) {
            throw new Exception("Failed to create address record.");
        }
			//TODO: bad practice!!
        // 2. Create the Link (Bridge Table)
        $queryLink = "INSERT INTO customer_address (customer_id, address_id) VALUES (:cid, :aid)";
        $stmtLink = $db->prepare($queryLink);
        $stmtLink->bindParam(':cid', $data->customer_id);
        $stmtLink->bindParam(':aid', $new_addr_id);

        if(!$stmtLink->execute()) {
             throw new Exception("Failed to link address to customer.");
        }

        // 3. Get the Bridge ID (This is what you need for the Order API!)
        $bridge_id = $db->lastInsertId();

        $db->commit();

        http_response_code(201);
        echo json_encode(array(
            "message" => "Address added to customer profile.",
            "customer_address_id" => $bridge_id, // <--- IMPORTANT: Save this for the Order!
            "address_id" => $new_addr_id
        ));

    } catch (Exception $e) {
        $db->rollBack();
        http_response_code(503);
        echo json_encode("Error: " . $e->getMessage());
    }

} else {
    http_response_code(400);
    echo json_encode("Incomplete data. Customer ID and Address required.");
}
?>
