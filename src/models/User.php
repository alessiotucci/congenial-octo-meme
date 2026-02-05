<?php
/* ************************************************************************** */
/*     File: models\User.php                                                  */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/26 13:05:12                                           */
/*     Updated: 2026/02/05 11:40:30                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

// class for the User
class User
{
	private $conn;
	private $table = 'users';

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

		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':password', $this->password);
		$stmt->bindParam(':role', $this->role);

		if ($stmt->execute())
		{
				//printf("Success! Created the user!\n");
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
			//TODO: SELECT * MIGHT BREAK
		$query = 'SELECT id, email, role, password  FROM ' . $this->table . ' WHERE email = ? LIMIT 0,1';
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
		try
		{
			$query = 'SELECT id, email, role, created_at
					  FROM ' . $this->table . ' ORDER BY created_at DESC ';
			$stmt = $this->conn->prepare($query);
			if (!$stmt)
				throw new Exception('Query preparation failed!');
			if (!$stmt->execute())
					throw new Exception('Query execution failed!');
			return ($stmt); //returning the statement object
		}
		catch (Exception $e)
		{
			throw $e;
		}
	}
	// 2)
	public function read_single()
	{
		$query = 'SELECT id, email, role, created_at
				  FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1 ';
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $this->id);
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
	public function update()
	{
		$query = 'UPDATE ' . $this->table . ' SET email = :email, role = :role 
											  WHERE id = :id';
		$this->email = htmlspecialchars(strip_tags($this->email));
		$this->role = htmlspecialchars(strip_tags($this->role));
		$this->id = htmlspecialchars(strip_tags($this->id));
		$stmt = $this->conn->prepare($query);

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
	}
	// 4
	public function delete()
	{
		$query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
		$stmt = $this->conn->prepare($query);

		$this->id = htmlspecialchars(strip_tags($this->id));
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
	}

	/*Work around to keep the logic works when user restart a sessions*/
	public function findEntityId($user_id, $role)
	{
		$table = '';
		$id_found = null;

		// Determine Table based on Role
		switch ($role)
		{
			case 'customer':
				$table = 'customer';
				break;
			case 'food_place':
				$table = 'food_place';
				break;
			case 'rider':
				$table = 'delivery_drivers';
				break;
			default:
				return [
					'status' => 'error',
					'message' => "Unknown role: $role"
				];
		}

		// Query the Database
		try {
			$query = "SELECT id FROM $table WHERE user_id = :uid LIMIT 1";
			$stmt = $this->conn->prepare($query);
			$stmt->bindParam(':uid', $user_id);
			$stmt->execute();

			if ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$id_found = $row['id'];
				return [
					'status' => 'success',
					'user_id' => $user_id,
					'role' => $role,
					'entity_id' => $id_found
				];
			}
			else
			{
				return [
					'status' => 'pending_setup',
					'message' => "Profile not found for this user"
				];
			}
		}
		catch (Exception $e)
		{
			return [
				'status' => 'error',
				'message' => "Database error: " . $e->getMessage()
			];
		}
	}
}
?>
