<?php
// Retrieve credentials from Docker Environment Variables
// We provide defaults (after ?:) just in case, but Docker should provide them.

// CRITICAL: In Docker, the host is the SERVICE NAME ('db'), not 'localhost'!
$host = getenv('MYSQL_HOST') ?: 'db';
$db_name = getenv('MYSQL_DATABASE') ?: 'food_delivery';
$user = getenv('MYSQL_USER') ?: 'food_app_user';
$pass = getenv('MYSQL_PASSWORD') ?: 'secure_user_pass';

echo "<h2>🐳 Docker DB Connection Test</h2>";
echo "Attempting to connect to Host: <b>$host</b>...<br>";

try {
    $dsn = "mysql:host=$host;dbname=$db_name;charset=utf8";
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h3 style='color: green;'>✅ SUCCESS!</h3>";
    echo "PHP is successfully connected to the Docker Database.<br>";
    echo "Now you can work from the 42 PC using Docker!";
}
catch (PDOException $e) {
    echo "<h3 style='color: red;'>❌ ERROR</h3>";
    echo "Connection failed: " . $e->getMessage() . "<br><br>";
    echo "<b>Debug Hint:</b><br>";
    echo "1. Are the variables in docker-compose.yml passed to the app?<br>";
    echo "2. Is the database container running? (docker ps)<br>";
}
?>
