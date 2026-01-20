/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: test_connection.php                                                */
/*   Created: 2026/01/20 19:28:48 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:28:50                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

<?php
// we will test the connections
// 1. Include the class file
require_once 'api/DbConnection.php';
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
?>
