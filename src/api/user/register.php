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


//1) headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Content-Type: application/json');


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
			echo json_decode("Email already in use!.");
		}
		else
		{
		    $new_user_id = $user->create();
		    if ($new_user_id)
		    {
		        http_response_code(201);
		        $response_data = array(
		            "message" => "User successfully created!",
		            "id" => $new_user_id
		        );
		        echo json_encode($response_data);
		    }
		    else
		    {
		        http_response_code(503); // 503 Service Unavailable
		        echo json_encode(array("message" => "Unable to create user."));
		    }
		}

}
else
{
	http_response_code(400);
	echo json_encode("Unable to create user. Data is incomplete.");
}
?>
