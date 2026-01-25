<?php
//TODO: replace this stuff with the .ENV
$host = '127.0.0.1'; // OR your remote host address
$port = 3306;
$user = 'root';      // OR your remote user
$pass = 'root';      // OR your remote pass

try
{
    $conn = new PDO("mysql:host=$host;port=$port", $user, $pass);
    echo "✅ PHP is working and connected to MySQL!\n
		Now I can work from 42 pc using docker!";
}
catch (PDOException $e)
{
    echo "❌ PHP works, but DB connection failed: " . $e->getMessage();
}
?>
