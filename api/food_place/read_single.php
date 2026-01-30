<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: read_single.php                                                    */
/*   Created: 2026/01/21 12:23:08 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 12:23:26                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// api/food_place/read_single.php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/FoodPlace.php';

$database = new DbConnection();
$db = $database->connect();
$place = new FoodPlace($db);

$place->id = isset($_GET['id']) ? $_GET['id'] : die();

if($place->read_single())
{
    
    // Create a structured response
    $place_arr = array(
        'id' => $place->id,
        'name' => $place->name,
        'description' => $place->description,
        'info' => array(
            'type' => $place->food_type,
            'hours' => $place->opening_hours,
            'rating' => $place->average_rating
        ),
        'address' => array(
            'street' => $place->address->street_number . ' ' . $place->address->address_line1,
            'unit' => $place->address->unit_number,
            'city' => $place->address->city,
            'zip' => $place->address->postal_code
        )
    );
    echo json_encode($place_arr);
}
else
{
    http_response_code(404);
    echo json_encode("Food Place not found.");
}
?>
