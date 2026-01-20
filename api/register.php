<?php
//1) headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');
header('Contet-Type: application/json');


include_once '../config/db_params.php';
include_once '../api/DbConnection.php';
include_once '../models/User.php';


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
			if ($user->create())
			{
			http_response_code(201);
			echo json_decode("User successfully created!"); //TODO now it works
			}
			else
			{
			http_response_code(503);
			echo json_decode("Unable to create user.");
			}
		}

}
else
{
	http_response_code(400);
	echo json_encode(array("message" => "Unable to create user. Data is incomplete."));
}
?>
