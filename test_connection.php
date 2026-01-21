<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: test_connection.php                                                */
/*   Created: 2026/01/20 19:28:48 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:28:50                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// we will test the connections
// 1. Include the class file
require_once 'api/DbConnection.php';
require_once 'models/MenuItem.php';

// 2. Instantiate the class (Create the object)
$dbObj = new DbConnection();
// 3. Call the method to connect
$conn = $dbObj->connect();
// 4. Check if it worked
if ($conn)
	{
	    echo "<h1>✅ Success! PHP is connected to 'food_delivery'.</h1>";
}
else
{
	    echo "<h1>❌ Error. Could not connect.</h1>";
}

// Test values
$testValues = [
    'food_place_id' => 2,
    'item_name' => 'Bogus Item',
    'item_description' => 'This is a test item with bogus values.',
    'price' => 9.99
];

// Instantiate the MenuItem class
try {
    $menuItem = new MenuItem($conn);

    // Set properties
    $menuItem->food_place_id = $testValues['food_place_id'];
    $menuItem->item_name = $testValues['item_name'];
    $menuItem->item_description = $testValues['item_description'];
    $menuItem->price = $testValues['price'];

    // Print the object to verify it's instantiated correctly
    echo "<pre>";
    var_dump($menuItem);
    echo "</pre>";

    // Try to create the menu item
    if ($menuItem->create())
	{
        echo "Success! The menu item was created.";
    }
	else
	{
        echo "Failed to create the menu item.";
    }
}
catch (Exception $e)
{
    echo "Error: " . $e->getMessage();
}
?>

