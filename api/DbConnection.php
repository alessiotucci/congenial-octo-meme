<?php
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
