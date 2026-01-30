<?php
/* ************************************************************************** */
/*     File: api\menu_item\read.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 00:02:02                                           */
/*     Updated: 2026/01/31 00:02:04                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// api/menu_item/read.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

// Call the NEW global read method
$result = $item->read();
$num = $result->rowCount();

if($num > 0) {
    $menu_arr = [];
    $menu_arr['data'] = [];

    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $menu_item = [
            'id' => $id,
            'food_place_id' => $food_place_id, // Important to know who owns this item
            'name' => $item_name,
            'category' => $category,
            'price' => $price,
            'is_available' => (bool)$is_available
        ];
        array_push($menu_arr['data'], $menu_item);
    }
    echo json_encode($menu_arr);
} else {
    echo json_encode("No menu items found anywhere.");
}
?>