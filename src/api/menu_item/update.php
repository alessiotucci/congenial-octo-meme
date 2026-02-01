<?php
/* ************************************************************************** */
/*     File: api\menu_item\update.php                                         */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 00:02:25                                           */
/*     Updated: 2026/01/31 00:02:26                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/menu_item/update.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: PUT');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->id)) {
    $item->id = $data->id;
    $item->item_name = $data->name;
    $item->item_description = $data->description;
    $item->price = $data->price;
    $item->category = $data->category;
    $item->is_available = $data->is_available; // Boolean

    if($item->update()) {
        echo json_encode("Menu Item Updated.");
    } else {
        http_response_code(503);
        echo json_encode("Update failed.");
    }
} else {
    echo json_encode("Missing ID.");
}
?>
