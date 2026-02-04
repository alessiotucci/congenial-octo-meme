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

// 1. VALIDATION: Don't just die()
if (!isset($_GET['id']))
{
    http_response_code(400);
    echo json_encode(["message" => "Missing Food Place ID."]);
    exit();
}

$item->food_place_id = $_GET['id'];
$result = $item->read_by_food_place();
$num = $result->rowCount();

// 2. CONSISTENT RESPONSE
// We always return a 'data' array. If empty, it's just []
$menu_arr = [];
$menu_arr['data'] = [];

if($num > 0)
{
    while($row = $result->fetch(PDO::FETCH_ASSOC))
	{
        extract($row);
        $menu_item = [
            'id' => $id,
            'name' => $item_name,
            'description' => $item_description, // Matched JS property name
            'price' => $price,
            'category' => $category,
            'is_available' => (bool)$is_available // Matched JS property name
        ];
        array_push($menu_arr['data'], $menu_item);
    }
}

// Always return JSON with 200 OK (Empty list is not an error)
http_response_code(200);
echo json_encode($menu_arr);
?>