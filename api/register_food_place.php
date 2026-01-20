/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: register_food_place.php                                            */
/*   Created: 2026/01/20 19:31:17 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:34:52                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

<?php
// Include the database and FoodPlace class

include_once '../config/db_params.php';
include_once '../api/DbConnection.php';
include_once '../models/food_place.php';


// Get database connection
$database = new DbConnection();
$db = $database->connect();

// Instantiate FoodPlace object
$foodPlace = new FoodPlace($db);

// Get data from the request
$data = json_decode(file_get_contents("php://input"));

// Set properties from the request
$foodPlace->name = $data->name;
$foodPlace->address_id = $data->address_id;
$foodPlace->average_rating = $data->average_rating;
$foodPlace->total_reviews = $data->total_reviews;
$foodPlace->food_type = $data->food_type;
$foodPlace->description = $data->description;
$foodPlace->opening_hours = $data->opening_hours;

// Create the food place
if ($foodPlace->create())
{
     echo json_encode("Food place was created.");
}
else
{
     echo json_encode("Unable to create food place.");
}
?>

