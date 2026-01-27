<?php
/* ************************************************************************** */
/*     File: DeliveryDriver.php                                               */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 15:56:32                                           */
/*     Updated: 2026/01/27 12:07:15                                           */
/*     System: WindowsNT [DESKTOP-TQURMND]                                    */
/*     Hardware: c:\programdata\chocolatey\lib\unxutils\tools\unxutils...     */
/* ************************************************************************** */

class DeliveryDriver
{
	private $conn;
	private $table = 'delivery_drivers';

	//Properties
	public $id;
	public $first_name;
	public $last_name;
	public $phone_number_original;
	public $phone_number_normalized;
	public $rating;
	public $user_id; // this is a FK

	public function __construct($db)
	{
		$this->conn = $db;
	}
	public function create()
	{
		$query = 'INSERT INTO ' . $this->table . ' (first_name, last_name,
				phone_number_original, phone_number_normalized, rating, user_id)
				VALUES (:first_name, :last_name, :phone_number_original,
						:phone_number_normalized, :rating, :user_id)'; //FK!

		$stmt = $this->conn->prepare($query);

		// this will clean the input
		$this->first_name = htmlspecialchars(strip_tags($this->first_name));
		$this->last_name = htmlspecialchars(strip_tags($this->last_name));
		$this->phone_number_original = htmlspecialchars(strip_tags($this->phone_number_original));
		//TODO: we need a function to normalize the phone number, otherwise this
		// field is pointless
		$this->phone_number_normalized = htmlspecialchars(strip_tags($this->phone_number_normalized));
		$this->rating = htmlspecialchars(strip_tags($this->rating));
		$this->user_id = htmlspecialchars(strip_tags($this->user_id));

		// bind the params
		$stmt->bindParam(':first_name', $this->first_name);
		$stmt->bindParam(':last_name', $this->last_name);
		$stmt->bindParam(':phone_number_original', $this->phone_number_original);

		$stmt->bindParam(':phone_number_normalized',
				$this->phone_number_normalized);
		$stmt->bindParam(':rating', $this->rating);
		$stmt->bindParam(':user_id', $this->user_id);

		if ($stmt->execute())
		{
			printf("Success! Created the food delivery driver!\n");
			return (true);
		}
		else
		{
			printf("Error: Failed: %s\n", $stmt->error);
			return (false);
		}
	}

	// FUNCTION TO READ ALL
	public function read()
	{
		$query = 'SELECT dd.id, dd.first_name, dd.last_name, dd.rating,
				  dd.phone_number_normalized, u.email, u.role, u.created_at
				  FROM ' . $this->table . '  dd 
				  LEFT JOIN users u ON dd.user_id = u.id;';
		$stmt = $this->conn->prepare($query);
		$stmt->execute();
		return ($stmt);
	}

	// FUNCTION TO READ BY ID
	public function read_single()
	{

	}

	// FUNCTION TO UPDATE VALUES
	public function update()
	{

	}
	// FUNCTION TO DELETE A DELIVERY DRIVER
	public function delete()
	{

	}
}
?>d
