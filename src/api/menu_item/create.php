<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: create.php                                                         */
/*   Created: 2026/01/21 15:24:30 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 15:33:32                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/menu_item/create.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

$data = json_decode(file_get_contents("php://input"));

if(!empty($data->food_place_id) && !empty($data->name) && !empty($data->price)) {
    
    $item->food_place_id = $data->food_place_id;
    $item->item_name = $data->name;
    $item->item_description = $data->description;
    $item->price = $data->price;
    // Default to 'Main' if category is missing
    $item->category = !empty($data->category) ? $data->category : 'Main';
    // Default to true (1) if missing
    $item->is_available = isset($data->is_available) ? $data->is_available : 1;

    if($item->create()) {
        http_response_code(201);
        echo json_encode("Menu Item Created.");
    } else {
        http_response_code(503);
        echo json_encode("Creation failed.");
    }
} else {
    http_response_code(400);
    echo json_encode("Incomplete Data.");
}
?>