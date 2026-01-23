/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: User.php                                                           */
/*   Created: 2026/01/20 19:27:29 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:27:41                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */
<?php
// class for the User
class User
{
	private $conn;
	private $table = 'users'; //TODO: match the MySQL table!

	// other properties
	public $id;
	public $email;
	public $password; //TODO: only store the hashed string
	public $role;
	public $created_at;

	public function __construct($db)
	{
	$this->conn = $db;
	}
	public function create()
	{
	$query = ' INSERT INTO ' . $this->table . ' SET email = :email, password = :password, role = :role ';
	// ALTERNATIVE (Standard SQL)
	//$query = 'INSERT INTO ' . $this->table . ' (email, password, role) 
    //      VALUES (:email, :password, :role)';

	$stmt = $this->conn->prepare($query);
	$this->email = htmlspecialchars(strip_tags($this->email));
	$this->password = htmlspecialchars(strip_tags($this->password));
	$this->role = htmlspecialchars(strip_tags($this->role));

	//TODO
	// 4. BIND PARAMETERS (You were missing this part!)
        // This tells PDO: "Put the value of $this->email where you see :email"
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);

	if ($stmt->execute())
	{
			printf("Success! Created the user!\n");
			return ($this->conn->lasInsertId()); //FK (?)
			//return (true);
	}
	else
	{
	printf("Error: %s.\n", $stmt->error);
	return (false);
	}
	}

	public function emailExists()
	{
	$query = 'SELECT *  FROM ' . $this->table . ' WHERE email = ? LIMIT 0,1';
	$stmt = $this->conn->prepare($query);
	$this->email = htmlspecialchars(strip_tags($this->email));
	$stmt->bindParam(1, $this->email);
	$stmt->execute();
			if ($stmt->rowCount() > 0)
			{
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			$this->id = $row['id'];
			$this->password = $row['password'];
			$this->role = $row['role'];
				return (true);
			}
			else
			{
				return (false);
			}
	}
}
?>
