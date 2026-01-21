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
		require __DIR__ . '/../config/db_params.php';
		$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
		try
		{
			$this->connection = new PDO($dsn, $user, $pass);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->connection;
		}
		catch (PDOException $e)
		{
			echo "Connection Failed: " . $e->getMessage();
			return (null);
		}
	}
}
?>
