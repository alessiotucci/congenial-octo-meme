<?php
/* ************************************************************************** */
/*     File: models\Customer.php                                              */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:18:04                                           */
/*     Updated: 2026/01/23 17:18:04                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

class Customer
{
	private $conn;
	private $table = 'customer';

	// Properties
	public $id;
	public $first_name;
	public $last_name;
	public $nick_name;
	public $phone_number_original;
	public $phone_number_normalized;
	public $user_id; // this is a FK

	public function __construct($db)
	{
		$this->conn = $db;
	}

	public function create()
	{
		$query = 'INSERT INTO ' . $this->table . ' (first_name, last_name,
				nick_name, phone_number_original, phone_number_normalized,
				user_id)
				VALUES (:first_name, :last_name,
				:nick_name, :phone_number_original, :phone_number_normalized,
				:user_id)'; // FK!

		$stmt = $this->conn->prepare($query);

		// this will clean up the input
		$this->first_name = htmlspecialchars(strip_tags($this->first_name));
		$this->last_name = htmlspecialchars(strip_tags($this->last_name));
		$this->nick_name = htmlspecialchars(strip_tags($this->nick_name));
		//TODO: we need a function to normalize the phone number
		$this->phone_number_original = htmlspecialchars(strip_tags($this->phone_number_original));
		$this->phone_number_normalized = htmlspecialchars(strip_tags($this->phone_number_normalized));
		$this->user_id = htmlspecialchars(strip_tags($this->user_id));

		// bind the params
		$stmt->bindParam(':first_name', $this->first_name);
		$stmt->bindParam(':last_name', $this->last_name);
		$stmt->bindParam(':nick_name', $this->nick_name);

		$stmt->bindParam(':phone_number_original', $this->phone_number_original);
		$stmt->bindParam(':phone_number_normalized', $this->phone_number_normalized);
		$stmt->bindParam(':user_id', $this->user_id);

		if ($stmt->execute())
		{
			printf("Success! Created a customer!\n");
			return(true);
		}
		else
		{
			printf("Failure: error %s\n", $stmt->error);
			return(false);
		}
	}
}

?>
