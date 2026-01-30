<?php
/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: Address.php                                                        */
/*   Created: 2026/01/21 12:26:05 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 13:10:23                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// the class blueprint for an address
//TODO: I create first the address because almost every other identity depends
// on it (customer, food_place, etc). It thas no dependencies other than 
// country (I have seeded country with SQL queries


class Address
{
	// properties for the queries
	private $conn;
	private $table = 'address'; //TODO: an exact match

	// properties that mirrors the db
	public $id;
	public $unit_number;
	public $street_number;
	public $address_line1;
	public $address_line2;
	public $city;
	public $region;
	public $postal_code;
	public $country_id;

	// constructor
	public function __construct($db)
	{
		$this->conn = $db;
	}
	//functions
	public function create()
	{
		$query = 'INSERT INTO ' . $this->table . ' (unit_number, street_number,
		  address_line1, address_line2, city,  region, postal_code, country_id)
		 VALUES (:unit_number, :street_number,
		  :address_line1, :address_line2, :city, :region, :postal_code, :country_id)';

		$stmt = $this->conn->prepare($query);

		// this will clean the input
		$this->unit_number = htmlspecialchars(strip_tags($this->unit_number));
		$this->street_number = htmlspecialchars(strip_tags($this->street_number));
		$this->address_line1 = htmlspecialchars(strip_tags($this->address_line1));
		$this->address_line2 = htmlspecialchars(strip_tags($this->address_line2));
		$this->city = htmlspecialchars(strip_tags($this->city));
		$this->region = htmlspecialchars(strip_tags($this->region));
		$this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
		$this->country_id = htmlspecialchars(strip_tags($this->country_id));

		// Binding the param, telling the PDO use the real value
		$stmt->bindParam(':unit_number', $this->unit_number);
		$stmt->bindParam(':street_number', $this->street_number);
		$stmt->bindParam(':address_line1', $this->address_line1);
		$stmt->bindParam(':address_line2', $this->address_line2);
		$stmt->bindParam(':city', $this->city);
		$stmt->bindParam(':region', $this->region);
		$stmt->bindParam(':postal_code', $this->postal_code);
		$stmt->bindParam(':country_id', $this->country_id);

		if ($stmt->execute())
		{
			printf("Success! Created the address!\n");
			return ($this->conn->lastInsertId()); // this is a FK!
			//return (true);
		}
		else
		{
			printf("Error! Failed: %s\n", $stmt->error);
			return (false);
		}
	}

//paste it here
// 2. READ (Get All - rarely used for addresses, but good to have)
public function read()
{			//TODO: remove the select *, it can break things
	$query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC';
	$stmt = $this->conn->prepare($query);
	$stmt->execute();
	return $stmt;
}

// 3. READ SINGLE
public function read_single()
{			//TODO: remove the select *, it can break things
	$query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';
	$stmt = $this->conn->prepare($query);
	$stmt->bindParam(1, $this->id);
	$stmt->execute();

	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if($row) {
		$this->unit_number = $row['unit_number'];
		$this->street_number = $row['street_number'];
		$this->address_line1 = $row['address_line1'];
		$this->address_line2 = $row['address_line2'];
		$this->city = $row['city'];
		$this->region = $row['region'];
		$this->postal_code = $row['postal_code'];
		$this->country_id = $row['country_id'];
		return true;
	}
	return false;
}

// 4. UPDATE (In-Place Update)
public function update()
{
	$query = 'UPDATE ' . $this->table . '
			  SET 
				unit_number = :unit,
				street_number = :street,
				address_line1 = :addr1,
				address_line2 = :addr2,
				city = :city,
				region = :region,
				postal_code = :zip,
				country_id = :country
			  WHERE id = :id';

	$stmt = $this->conn->prepare($query);

	// Sanitize
	$this->unit_number   = htmlspecialchars(strip_tags($this->unit_number));
	$this->street_number = htmlspecialchars(strip_tags($this->street_number));
	$this->address_line1 = htmlspecialchars(strip_tags($this->address_line1));
	$this->address_line2 = htmlspecialchars(strip_tags($this->address_line2));
	$this->city          = htmlspecialchars(strip_tags($this->city));
	$this->region        = htmlspecialchars(strip_tags($this->region));
	$this->postal_code   = htmlspecialchars(strip_tags($this->postal_code));
	$this->country_id    = htmlspecialchars(strip_tags($this->country_id));
	$this->id            = htmlspecialchars(strip_tags($this->id));

	// Bind
	$stmt->bindParam(':unit', $this->unit_number);
	$stmt->bindParam(':street', $this->street_number);
	$stmt->bindParam(':addr1', $this->address_line1);
	$stmt->bindParam(':addr2', $this->address_line2);
	$stmt->bindParam(':city', $this->city);
	$stmt->bindParam(':region', $this->region);
	$stmt->bindParam(':zip', $this->postal_code);
	$stmt->bindParam(':country', $this->country_id);
	$stmt->bindParam(':id', $this->id);

	if($stmt->execute()) {
		return true;
	}
	return false;
}

// 5. DELETE
public function delete()
{
	$query = 'DELETE FROM ' . $this->table . ' WHERE id = :id';
	$stmt = $this->conn->prepare($query);
	$this->id = htmlspecialchars(strip_tags($this->id));
	$stmt->bindParam(':id', $this->id);

	if($stmt->execute()) {
		return true;
	}
	return false;
}
}
?>
