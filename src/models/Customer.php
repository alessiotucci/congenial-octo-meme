<?php
/* ************************************************************************** */
/*     File: models\Customer.php                                              */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/23 17:18:04                                           */
/*     Updated: 2026/01/30 22:22:11                                           */
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

	// Extra properties for Read (from Joins)
	public $email;

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
			//printf("Success! Created a customer!\n");
			return ($this->conn->lastInsertId());
			//return(true);
		}
		else
		{
			//printf("Failure: error %s\n", $stmt->error);
			return(false);
		}
	}

	// ------------------------------------------------------------------
    // 2. READ ALL (Join with Users to get Email)
    // ------------------------------------------------------------------
    public function read()
    {
        $query = 'SELECT 
                    c.id, c.first_name, c.last_name, c.nick_name, c.phone_number_original, 
                    u.email 
                  FROM ' . $this->table . ' c
                  LEFT JOIN users u ON c.user_id = u.id
                  ORDER BY c.id DESC';

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // ------------------------------------------------------------------
    // 3. READ SINGLE
    // ------------------------------------------------------------------
    public function read_single()
    {
        $query = 'SELECT 
                    c.id, c.first_name, c.last_name, c.nick_name, c.phone_number_original, 
                    u.email 
                  FROM ' . $this->table . ' c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.id = ? 
                  LIMIT 0,1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->nick_name = $row['nick_name'];
            $this->phone_number_original = $row['phone_number_original'];
            $this->email = $row['email'];
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------
    // 4. UPDATE (Profile Info Only)
    // ------------------------------------------------------------------
    public function update()
    {
        $query = 'UPDATE ' . $this->table . '
                  SET 
                    first_name = :first,
                    last_name = :last,
                    nick_name = :nick,
                    phone_number_original = :phone
                  WHERE id = :id';

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->nick_name = htmlspecialchars(strip_tags($this->nick_name));
        $this->phone_number_original = htmlspecialchars(strip_tags($this->phone_number_original));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind
        $stmt->bindParam(':first', $this->first_name);
        $stmt->bindParam(':last', $this->last_name);
        $stmt->bindParam(':nick', $this->nick_name);
        $stmt->bindParam(':phone', $this->phone_number_original);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------
    // 5. DELETE (Strict Mode + Bridge Table Cleanup)
    // ------------------------------------------------------------------
    public function delete()
    {
        // A. Strict Check: Active Orders
        // We check if this customer has any orders that are NOT "Delivered" (Status < 4)
        $checkQuery = "SELECT id FROM food_order WHERE customer_id = ? AND order_status_id < 4 LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $this->id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0)
		{
            return "BUSY"; // Custom error code handled by API
        }

        try
		{
            $this->conn->beginTransaction();

            // B. Delete links in the Bridge Table (customer_address)
            // We must remove these connections before deleting the customer
            $delLinkQuery = "DELETE FROM customer_address WHERE customer_id = :id";
            $delLinkStmt = $this->conn->prepare($delLinkQuery);
            $delLinkStmt->bindParam(':id', $this->id);
            if (!$delLinkStmt->execute())
				throw new Exception("Failed to clean up address links.");

            // C. Delete the Customer Profile
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(':id', $this->id);

            if (!$stmt->execute())
				throw new Exception("Failed to delete customer.");

            $this->conn->commit();
            return true;

        }
		catch (Exception $e)
		{
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
