<?php
/* ************************************************************************** */
/*     File: src\api\user\validate_session.php                                */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/02/03 17:08:58                                           */
/*     Updated: 2026/02/03 17:11:01                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */


header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';

// 1. Resume Session
session_set_cookie_params([
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// 2. Check Login Status
if (isset($_SESSION['user_id']) && isset($_SESSION['role']))
{
    
    // --------------------------------------------------------------------
    // SELF-HEALING MECHANISM
    // If entity_id is missing, we go get it immediately.
    // --------------------------------------------------------------------
    if (!isset($_SESSION['entity_id']) || $_SESSION['entity_id'] === null)
	{
        
        // Connect to DB only if we need to fix the session
        $database = new DbConnection();
        $db = $database->connect();
        
        $uid = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        $found_id = null;

        // Determine table based on role
        $query = "";
        if ($role === 'customer')
		{
            $query = "SELECT id FROM customer WHERE user_id = :uid LIMIT 1";
        }
		elseif ($role === 'food_place' || $role === 'merchant')
		{
            $query = "SELECT id FROM food_place WHERE user_id = :uid LIMIT 1";
        }
		elseif ($role === 'rider')
		{
            $query = "SELECT id FROM delivery_drivers WHERE user_id = :uid LIMIT 1";
        }

        // Execute Lookup
        if ($query !== "")
		{
            try {
                $stmt = $db->prepare($query);
                $stmt->bindParam(':uid', $uid);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row) {
                    $found_id = $row['id'];
                    // FIX THE SESSION PERMANENTLY
                    $_SESSION['entity_id'] = $found_id; 
                }
            } catch (Exception $e) {
                // Silently fail logging, we will return null later
            }
        }
    }
    // --------------------------------------------------------------------

    echo json_encode(array(
        "is_logged_in" => true,
        "user_id"      => $_SESSION['user_id'],
        "role"         => $_SESSION['role'],
        // Return the existing ID OR the one we just found
        "entity_id"    => isset($_SESSION['entity_id']) ? $_SESSION['entity_id'] : null, 
        "csrf_token"   => isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : null
    ));

} else {
    // Not logged in
    echo json_encode(array("is_logged_in" => false));
}
?>