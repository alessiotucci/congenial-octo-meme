<?php
/* ************************************************************************** */
/*     File: src\api\user\login.php                                           */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/03 17:05:52                                           */
/*     Updated: 2026/02/03 17:07:23                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


// If you are accessing via HTTPS on standard port (443)
header('Access-Control-Allow-Origin: https://localhost'); 
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Credentials: true'); // Still needed for Cookies!

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../config/session_auth.php'; // Load our security rules

$database = new DbConnection();
$db = $database->connect();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password))
{
	
	// 1. FIND USER BY EMAIL
	$query = "SELECT id, password, role FROM users WHERE email = :email LIMIT 1";
	$stmt = $db->prepare($query);
	$stmt->bindParam(':email', $data->email);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	// 2. VERIFY PASSWORD
	if ($user && password_verify($data->password, $user['password']))
	{
		
		$entity_id = null;
        switch($user['role'])
		{
            case 'food_place':
                $e_query = "SELECT id FROM food_place WHERE user_id = :uid LIMIT 1";
                break;
            case 'customer':
                $e_query = "SELECT id FROM customer WHERE user_id = :uid LIMIT 1";
                break;
            case 'delivery_driver':
                $e_query = "SELECT id FROM delivery_driver WHERE user_id = :uid LIMIT 1";
                break;
            default:
                $e_query = null;
        }
		if ($e_query)
		{
            $stmt_e = $db->prepare($e_query);
            $stmt_e->bindParam(':uid', $user['id']);
            $stmt_e->execute();
            $result = $stmt_e->fetch(PDO::FETCH_ASSOC); 
            if ($result)
			{
                $entity_id = $result['id'];
            }
        }

		// 3. START SECURE SESSION
		start_secure_session();
		// 4. REGENERATE ID (Critical for security)
		session_regenerate_id(true);
		// 5. STORE DATA ON SERVER (Not in the browser!)
		$_SESSION['user_id'] = $user['id'];
		$_SESSION['role'] = $user['role'];
		// 6. GENERATE CSRF TOKEN
		// We give this to JS to prove it's really the app making requests
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
		http_response_code(200);
		echo json_encode(array(
			"message" => "Login successful",
			"role"	=> $user['role'],
			"entity_id" => $entity_id, // look up
			"user_id" => $user['id'],
			"csrf_token" => $_SESSION['csrf_token'] // JS saves this in memory
		));
	}
	else
	{
		http_response_code(401);
		echo json_encode(array("message" => "Invalid credentials"));
	}
}
else
{
	http_response_code(400);
	echo json_encode(array("message" => "Missing email or password"));
}
?>
