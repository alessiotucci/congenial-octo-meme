<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: MenuItem.php                                                       */
/*   Created: 2026/01/21 14:59:12 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 15:46:43                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// class to store the menu items

class MenuItem
{
	//properties for the queries
	private $conn;
	private $table = 'menu_item'; //TODO: an exact match

	//properties that mirror the db
	public $id;
	public $food_place_id; // FK
	public $item_name;
	public $item_description;
	public $price;

	// new stuff
	public $category;
	public $is_available;
	public $is_deleted;

	//constructor
	public function __construct($db)
	{
		$this->conn = $db;
	}

	// 1. CREATE
	public function create()
	{
		$query = 'INSERT INTO ' . $this->table . ' 
				  SET 
					food_place_id = :fpid,
					item_name = :name,
					item_description = :desc,
					price = :price,
					category = :cat,
					is_available = :avail'; // We can set availability on creation

		$stmt = $this->conn->prepare($query);

		// Sanitize
		$this->food_place_id = htmlspecialchars(strip_tags($this->food_place_id));
		$this->item_name = htmlspecialchars(strip_tags($this->item_name));
		$this->item_description = htmlspecialchars(strip_tags($this->item_description));
		$this->price = htmlspecialchars(strip_tags($this->price));
		$this->category = htmlspecialchars(strip_tags($this->category));
		// Force availability to 1 or 0
		$this->is_available = $this->is_available ? 1 : 0; 

		// Bind
		$stmt->bindParam(':fpid', $this->food_place_id);
		$stmt->bindParam(':name', $this->item_name);
		$stmt->bindParam(':desc', $this->item_description);
		$stmt->bindParam(':price', $this->price);
		$stmt->bindParam(':cat', $this->category);
		$stmt->bindParam(':avail', $this->is_available);

		if ($stmt->execute())
		{
			return true;
		}
		else
		{
			printf("Error: %s.\n", implode(" | ", $stmt->errorInfo()));
			return false;
		}
	}

	// 2. READ BY RESTAURANT (Crucial for the Menu Page)
	// We strictly hide items that are "Soft Deleted"
	public function read_by_food_place()
	{		//TODO: delete the select *
		$query = 'SELECT id, food_place_id, item_name, item_description, price, category, is_available, is_deleted  FROM ' . $this->table . ' 
				  WHERE food_place_id = ? AND is_deleted = 0
				  ORDER BY category ASC, item_name ASC';

		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $this->food_place_id);
		$stmt->execute();
		return $stmt;
	}

	// 3. READ SINGLE
	public function read_single()
	{		//TODO: delete the select *
		$query = 'SELECT food_place_id, item_name, item_description, price, category, is_available, is_deleted ' .
			' FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';
		$stmt = $this->conn->prepare($query);
		$stmt->bindParam(1, $this->id);
		$stmt->execute();
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row)
		{
			$this->food_place_id = $row['food_place_id'];
			$this->item_name = $row['item_name'];
			$this->item_description = $row['item_description'];
			$this->price = $row['price'];
			$this->category = $row['category'];
			$this->is_available = $row['is_available'];
			$this->is_deleted = $row['is_deleted'];
			return true;
		}
		return false;
	}

	// 4. UPDATE (Regular Update + Stock Toggle)
	public function update()
	{
		$query = 'UPDATE ' . $this->table . '
				  SET 
					item_name = :name,
					item_description = :desc,
					price = :price,
					category = :cat,
					is_available = :avail
				  WHERE id = :id';

		$stmt = $this->conn->prepare($query);

		$this->item_name = htmlspecialchars(strip_tags($this->item_name));
		$this->item_description = htmlspecialchars(strip_tags($this->item_description));
		$this->price = htmlspecialchars(strip_tags($this->price));
		$this->category = htmlspecialchars(strip_tags($this->category));
		$this->is_available = $this->is_available ? 1 : 0;
		$this->id = htmlspecialchars(strip_tags($this->id));

		$stmt->bindParam(':name', $this->item_name);
		$stmt->bindParam(':desc', $this->item_description);
		$stmt->bindParam(':price', $this->price);
		$stmt->bindParam(':cat', $this->category);
		$stmt->bindParam(':avail', $this->is_available);
		$stmt->bindParam(':id', $this->id);

		if ($stmt->execute()) {
			return true;
		}
		return false;
	}

	// 5. DELETE (The Soft Delete) just updating a value
	public function delete()
	{
		// Instead of DELETE, we UPDATE the flag
		$query = 'UPDATE ' . $this->table . ' SET is_deleted = 1 WHERE id = :id';
		
		$stmt = $this->conn->prepare($query);
		$this->id = htmlspecialchars(strip_tags($this->id));
		$stmt->bindParam(':id', $this->id);

		if ($stmt->execute())
			return true;
		else
			return false;
	}
	// ------------------------------------------------------------------
	// 6. READ ALL (Global list - useful for Admins)
	// ------------------------------------------------------------------
	public function read()
	{		//TODO: delete the select *!
		$query = 'SELECT * FROM ' . $this->table . ' 
				  WHERE is_deleted = 0
				  ORDER BY food_place_id ASC, category ASC';

		$stmt = $this->conn->prepare($query);
		$stmt->execute();
		return $stmt;
	}
}
?>
