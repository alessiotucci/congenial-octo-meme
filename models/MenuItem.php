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

	//constructor
	public function __construct($db)
	{
		$this->conn = $db;
	}

	//functions
	public function create()
	{
		$query = 'INSERT INTO ' . $this->table . ' (food_place_id, item_name,
		item_description, price)
		VALUES (:food_place_id, :item_name, :item_description, :price)';

		$stmt = $this->conn->prepare($query);
		
		// clean up the input
		$this->item_name = htmlspecialchars(strip_tags($this->item_name));
		$this->item_description = htmlspecialchars(strip_tags($this->item_description));
		$this->price = htmlspecialchars(strip_tags($this->price));
		$this->food_place_id = htmlspecialchars(strip_tags($this->food_place_id));

		//bind the param
		$stmt->bindParam(':item_name', $this->item_name);
		$stmt->bindParam(':item_description', $this->item_description);
		$stmt->bindParam(':price', $this->price);
		$stmt->bindParam(':food_place_id', $this->food_place_id);

		if ($stmt->execute())
		{
			printf("Success! Created the food item!\n");
			return(true);
		}
		else
		{
			printf("Failure! Cannot create the food item: %s\n", $stmt->error);
			return(false);
		}
	}
}
?>
