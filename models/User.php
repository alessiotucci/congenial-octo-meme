<?php
/* ************************************************************************** */
/*     File: models\User.php                                                  */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 13:05:12                                           */
/*     Updated: 2026/01/26 13:05:14                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

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
			return ($this->conn->lastInsertId()); //FK (?)
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
	// 1)
	public function read()
	{
		$query = 'SELECT id, email, role, created_at
				  FROM ' . $this->table . 'ORDER BY created_at DESC';
		$stmt-> = $this->conn->prepare($query);
		$stmt->execute();
		return ($stmt); //returning the statement object
	}
	// 2)
	public function read_single()
	{
		$query = 'SELECT id, email, role, created_at
				  FROM ' . $this->table . 'WHERE id = ? LIMIT 0,1';
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $this->id); //TODO: check api logic
		$stmt->execute();

		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		if ($row)
		{
			$this->email = $row['email'];
			$this->role = $row['role'];
			$this->created_at = $row['created_at'];
			return (true);
		}
		else
		{
			return (false);
		}
	}
	//3
	public function  update()
	{
		$query = 'UPDATE ' . $this->table . ' SET email = :email, role = :role
											  WHERE id = :id';
		$this->email = htmlspecialchars(strip_tags($this->email));
		$this->role = htmlspecialchars(strip_tags($this->role));
		$this->id = htmlspecialchars(strip_tags($this->id));

		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':role', $this->role);
		$stmt->bindParam(':id', $this->id);

		if ($stmt->execute())
		{
			return (true);
		}
		else
		{
			printf("Error: %s.\n", $stmt->error);
			return (false);
		}
	// 4
	public function delete()
	{
		$query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
		$stmt = $this->conn->prepare($query);

		$this->id = htmlspecialchars(strip_tags($this->id));
		if ($stmt->execute())
		{
			return (true);
		}
		else
		{
			printf("Error: %s.\n", $stmt->error);
			return (false);
		}
	}
}
?>
