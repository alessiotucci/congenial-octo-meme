<?php
/* ************************************************************************** */
/*     File: api\menu_item\delete.php                                         */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 00:02:28                                           */
/*     Updated: 2026/01/31 00:02:29                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// api/menu_item/delete.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: DELETE');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    $item->id = $data->id;
    
    if($item->delete()) {
        echo json_encode("Item removed (Soft Delete).");
    } else {
        http_response_code(503);
        echo json_encode("Delete failed.");
    }
} else {
    echo json_encode("Missing ID.");
}
?>