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

//create endpoint for the menu item
// api/menu_item/create.php

// 1. HEADERS (Standard REST API Headers)
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

// 2. INCLUDES
// We go up two levels (../../) because we are in api/menu_item/
include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/MenuItem.php';

// 3. DATABASE CONNECTION
$database = new DbConnection();
$db = $database->connect();

// 4. INSTANTIATE THE OBJECT
$menuItem = new MenuItem($db);


// 5. GET THE DATA
$data = json_decode(file_get_contents("php://input"));

// 6. VALIDATE THE DATA
if (!empty($data->food_place_id) && !empty($data->item_name)
	&& !empty($data->price))
{
	$menuItem->item_name = $data->item_name;
	$menuItem->price = $data->price;
	$menuItem->item_description = $data->item_description ?? ''; // empty
	$menuItem->food_place_id = $data->food_place_id;

	// 7. CREATE
	if ($menuItem->create())
	{
		http_response_code(201);
		echo json_encode("Menu item created!");
	}
	else
	{
		http_response_code(503);
		echo json_encode("Unable to create the item.");
	}
}
else
{
	http_response_code(400);
	echo json_encode("Incomplete data!\n");
}
