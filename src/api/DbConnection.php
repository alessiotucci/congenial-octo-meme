<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: DbConnection.php                                                   */
/*   Created: 2026/01/20 19:29:03 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:29:05                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// The first class
class DbConnection
{
    private $connection;

    public function connect()
    {
        $this->connection = null;

        // 1. Import the variables from the config file
        require __DIR__ . '/../config/db_params.php';

        // DEBUG: Uncomment this if it fails again to see what PHP sees
        error_log("Attempting connect to Host: $host | DB: $db_name | User: $username");

        try {
            // 2. Use the CORRECT variable names from db_params.php
            // $db_name, not $db
            // $username, not $user
            // $password, not $pass

            $dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $this->connection;
        }
        catch (PDOException $e)
        {
            // Log error to Docker logs (check with: docker logs food_app)
            error_log("Database Connection Error: " . $e->getMessage());
			exit();
            return null;
        }
    }
}
?>
