<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: register.php                                                       */
/*   Created: 2026/01/20 19:29:10 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:29:13                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// 1. Headers
header('Access-Control-Allow-Origin: *'); // NOTE: For production, specify the exact domain
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Content-Type: application/json');
// header('Access-Control-Allow-Credentials: true'); // <--- UNCOMMENT THIS if your frontend sends cookies

include_once '../../config/db_params.php';
include_once '../../api/DbConnection.php';
include_once '../../models/User.php';

$database = new DbConnection();
$db = $database->connect();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password) && !empty($data->role))
{
    $user->email = $data->email;
    $user->password = password_hash($data->password, PASSWORD_DEFAULT);
    $user->role = $data->role;

    if ($user->emailExists())
    {
        http_response_code(400);
        // FIX: You had json_decode here, which is wrong. Changed to json_encode.
        echo json_encode(array("message" => "Email already in use!")); 
    }
    else
    {
        $new_user_id = $user->create();
        
        if ($new_user_id)
        {
            // -------------------------------------------------------------
            // ✅ START SECURITY UPGRADE: SERVER-SIDE AUTO-LOGIN
            // -------------------------------------------------------------
            
            // 1. Configure Session Security
            session_set_cookie_params([
                'secure' => true,      // HTTPS only
                'httponly' => true,    // JS cannot access cookies (Prevents XSS)
                'samesite' => 'Strict' // Prevents CSRF
            ]);
            
            // 2. Start the Session
            session_start();

            // 3. Generate a CSRF Token
            $csrf_token = bin2hex(random_bytes(32));

            // 4. Store User Data in Session
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['email'] = $user->email;
            $_SESSION['role'] = $user->role;
            $_SESSION['csrf_token'] = $csrf_token;
            // Note: $_SESSION['entity_id'] remains NULL until Step 2 is completed

            // -------------------------------------------------------------
            // END SECURITY UPGRADE
            // -------------------------------------------------------------

            http_response_code(201);
            
            $response_data = array(
                "message" => "User successfully created and logged in!",
                "id" => $new_user_id,
                "csrf_token" => $csrf_token // Send this to frontend for state
            );
            
            echo json_encode($response_data);
        }
        else
        {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to create user."));
        }
    }
}
else
{
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
?>