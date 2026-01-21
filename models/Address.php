/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: Address.php                                                        */
/*   Created: 2026/01/21 12:26:05 | By: Alessio Tucci <email>                 */
/*   Updated: 2026/01/21 13:05:03                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

// the class blueprint for an address
//TODO: I create first the address because almost every other identity depends
// on it (customer, food_place, etc). It thas no dependencies other than 
// country (I have seeded country with SQL queries

<?php

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
		  address_line1, address_line2, region, postal_code, country_id)
		 VALUES (:unit_number, :street_number,
		  :address_line1, :address_line2, :region, :postal_code, :country_id)';

		$stmt = $this->conn->prepare($query);

		// this will clean the input
		$this->unit_number = htmlspecialchars(strip_tags($this->unit_number));
		$this->street_number = htmlspecialchars(strip_tags($this->street_number));
		$this->address_line1 = htmlspecialchars(strip_tags($this->address_line1));
		$this->address_line2 = htmlspecialchars(strip_tags($this->address_line2));
		$this->region = htmlspecialchars(strip_tags($this->region));
		$this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
		$this->country_id = htmlspecialchars(strip_tags($this->country_id));

		// Binding the param, telling the PDO use the real value
		$stmt->bindParam(':unit_number', $this->unit_number);
		$stmt->bindParam(':street_number', $this->street_number);
		$stmt->bindParam(':address_line1', $this->address_line1);
		$stmt->bindParam(':address_line2', $this->address_line2);
		$stmt->bindParam(':region', $this->region);
		$stmt->bindParam(':postal_code', $this->postal_code);
		$stmt->bindParam(':country_id', $this->country_id);

		if ($stmt->execute())
		{
			printf("Success! Created the address!\n");
			return (true);
		}
		else
		{
			printf("Error! Failed: %s\n", $stmt->error);
			return (false);
		}
	}
}
?>
