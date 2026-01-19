<?php
// we will test the connections
// 1. Include the class file
require_once 'api/DbConnection.php';
// 2. Instantiate the class (Create the object)
$dbObj = new DbConnection();
// 3. Call the method to connect
$conn = $dbObj->connect();
// 4. Check if it worked
if ($conn) {
	    echo "<h1>✅ Success! PHP is connected to 'food_delivery'.</h1>";
} else {
	    echo "<h1>❌ Error. Could not connect.</h1>";
}
?>
