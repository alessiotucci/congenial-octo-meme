<?php
/* ************************************************************************** */
/*     File: api\menu_item\read_single.php                                    */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/31 00:02:06                                           */
/*     Updated: 2026/01/31 00:02:09                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// api/menu_item/read_single.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

$database = new DbConnection();
$db = $database->connect();
$item = new MenuItem($db);

// Get ID from URL: ?id=5
$item->id = isset($_GET['id']) ? $_GET['id'] : die();

if($item->read_single()) {
    
    // Create array with full details
    $item_arr = [
        'id' => $item->id,
        'food_place_id' => $item->food_place_id,
        'name' => $item->item_name,
        'description' => $item->item_description,
        'category' => $item->category,
        'price' => $item->price,
        'is_available' => (bool)$item->is_available,
        'is_deleted' => (bool)$item->is_deleted
    ];

    // Check if it was soft-deleted (Optional: You might want to return 404 if deleted)
    if ($item->is_deleted) {
        http_response_code(404);
        echo json_encode("Item not found (Deleted).");
    } else {
        echo json_encode($item_arr);
    }

} else {
    http_response_code(404);
    echo json_encode("Item not found.");
}
?>

