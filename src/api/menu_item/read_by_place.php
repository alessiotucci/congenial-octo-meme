<?php
/* ************************************************************************** */
/*     File: api\menu_item\read_by_place.php                                  */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 00:06:10                                           */
/*     Updated: 2026/01/31 00:06:12                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */
// api/menu_item/read_by_place.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

// Get the restaurant ID from URL: ?id=1
$item->food_place_id = isset($_GET['id']) ? $_GET['id'] : die();

$result = $item->read_by_food_place();
$num = $result->rowCount();

if($num > 0) {
    $menu_arr = [];
    $menu_arr['data'] = [];

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $menu_item = [
            'id' => $id,
            'name' => $item_name,
            'desc' => $item_description,
            'price' => $price,
            'category' => $category,
            'in_stock' => (bool)$is_available // Cast to true/false for clean JSON
        ];
        array_push($menu_arr['data'], $menu_item);
    }
    echo json_encode($menu_arr);
} else {
    echo json_encode("No menu items found.");
}
?>

