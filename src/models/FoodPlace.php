<?php
/* ************************************************************************** */
/*     File: models\FoodPlace.php                                             */
/*     Author: atucci <atucci@student.42.fr>                                  */
/*     Created: 2026/01/27 10:43:35                                           */
/*     Updated: 2026/01/27 18:59:28                                           */
/*     System: unknown [SurfaceLaptopmy]                                      */
/*     Hardware: unknown | RAM: Unknown                                       */
/* ************************************************************************** */

//TODO: refactor this class to have a small instance of address inside of it!

// class for the FoodPlace
include_once 'Address.php'; // Ensure we can see the smaller class
class FoodPlace
{
    private $conn;
    private $table = 'food_place'; // The MySQL table name

    // Properties that match the table columns
    public $id;
	public $user_id; // this is a FK
    public $name;
    public $address_id; // this is a FK
    public $average_rating;
    public $total_reviews;
    public $food_type;
    public $description;
    public $opening_hours;

	// Address Properties (We need these here to receive data from the API)
    /*public $unit_number;
    public $street_number;
    public $address_line1;
    public $address_line2;
    public $region;
    public $postal_code;
    public $country_id;*/

	//TODO:or we can just create an istance of the class Address inside of it!
	public $address;

    public function __construct($db)
    {
        $this->conn = $db;
		$this->address = new Address($db); //SETTING IT UP IN THE CONSTRUCTOR
    }

	public function createWithAddress()
	{
        try
		{
            $this->conn->beginTransaction();
            $address_id = $this->address->create(); 
            if (!$address_id)
			{
                throw new Exception("Failed to create address.");
            }
            // 4. Create the FoodPlace using that ID
            $query = 'INSERT INTO ' . $this->table . ' 
                      SET
						user_id = :user_id,
                        name = :name,
                        address_id = :addr_id,  /* <--- The Critical Link */
                        food_type = :food_type,
                        description = :desc,
                        opening_hours = :hours';

            $stmt = $this->conn->prepare($query);

            // Sanitize FoodPlace data
			$this->user_id = htmlspecialchars(strip_tags($this->user_id));
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->food_type = htmlspecialchars(strip_tags($this->food_type));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->opening_hours = htmlspecialchars(strip_tags($this->opening_hours));

            // Bind Params
			$stmt->bindParam(':user_id', $this->user_id);
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':addr_id', $address_id); // Use the variable! not $this->address_id
            $stmt->bindParam(':food_type', $this->food_type);
            $stmt->bindParam(':desc', $this->description);
            $stmt->bindParam(':hours', $this->opening_hours);

            if (!$stmt->execute())
			{
                throw new Exception("Failed to create Food Place.");
            }
            // 5. Commit (Save everything)
			//TODO: Wait, bu why?
			$this->id = $this->conn->lastInsertId();
            $this->conn->commit();
            return true;

        }
		catch (Exception $e)
		{
			//TODO: this is a quick fix, let's double check for failure in address creaton
			if ($this->conn->inTransaction())
			{
                $this->conn->rollBack();
            }
			error_log("FoodPlace Creation Failed: " . $e->getMessage());
            return false;
        }
    }

// ------------------------------------------------------------------
    // 2. READ ALL (Join Address info)
    // ------------------------------------------------------------------
    public function read()
    {
		try
		{
		  $query = 'SELECT 
              p.id, p.user_id, p.name, p.food_type, p.average_rating, p.description,
              a.address_line1, a.city, a.country_id
            FROM ' . $this->table . ' p
            LEFT JOIN address a ON p.address_id = a.id
            ORDER BY p.id DESC';

        	$stmt = $this->conn->prepare($query);
        	if (!$stmt)
				throw new Exception('Query preparation failed!');
			if (!$stmt->execute())
				throw new Exception('Query execution failed!');
        	return $stmt;	
		}
		catch (Exception $e)
		{
			return false;
		}
        // Selects Restaurant info AND City/Street from Address table

    }

	//TODO: READ_SINGLE
	// ------------------------------------------------------------------
    // 3. READ SINGLE (Fully Hydrate both Objects)
    // ------------------------------------------------------------------
    public function read_single()
    {
        $query = 'SELECT 
                    p.*,
                    a.unit_number, a.street_number, a.address_line1, a.address_line2, 
                    a.city, a.region, a.postal_code, a.country_id
                  FROM ' . $this->table . ' p
                  LEFT JOIN address a ON p.address_id = a.id
                  WHERE p.id = ? LIMIT 0,1';

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row)
		{
            // Fill FoodPlace properties
            $this->user_id = $row['user_id'];
            $this->name = $row['name'];
            $this->address_id = $row['address_id'];
            $this->food_type = $row['food_type'];
            $this->description = $row['description'];
            $this->opening_hours = $row['opening_hours'];
            $this->average_rating = $row['average_rating'];
            $this->total_reviews = $row['total_reviews'];

            // Fill Address Object properties
            $this->address->id = $row['address_id'];
            $this->address->unit_number = $row['unit_number'];
            $this->address->street_number = $row['street_number'];
            $this->address->address_line1 = $row['address_line1'];
            $this->address->address_line2 = $row['address_line2'];
            $this->address->city = $row['city'];
            $this->address->region = $row['region'];
            $this->address->postal_code = $row['postal_code'];
            $this->address->country_id = $row['country_id'];
            return true;
        }
        return false;
    }

// ------------------------------------------------------------------
    // 4. UPDATE (Update Place + Delegate Address Update)
    // ------------------------------------------------------------------
    public function update()
    {
        try {
            $this->conn->beginTransaction();

            // A. Update FoodPlace Fields
            $query = 'UPDATE ' . $this->table . '
                      SET 
                        name = :name,
                        food_type = :food_type,
                        description = :desc,
                        opening_hours = :hours
                      WHERE id = :id';

            $stmt = $this->conn->prepare($query);

            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->food_type = htmlspecialchars(strip_tags($this->food_type));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->opening_hours = htmlspecialchars(strip_tags($this->opening_hours));
            $this->id = htmlspecialchars(strip_tags($this->id));

            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':food_type', $this->food_type);
            $stmt->bindParam(':desc', $this->description);
            $stmt->bindParam(':hours', $this->opening_hours);
            $stmt->bindParam(':id', $this->id);

            if (!$stmt->execute())
				throw new Exception("Restaurant update failed.");

            // B. Find the associated Address ID
            // We do this to be safe, ensuring we update the correct address row
            $sqlAddress = "SELECT address_id FROM " . $this->table . " WHERE id = :id";
            $stmtAddr = $this->conn->prepare($sqlAddress);
            $stmtAddr->bindParam(':id', $this->id);
            $stmtAddr->execute();
            $row = $stmtAddr->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['address_id'])
			{
                // Set the ID on the child object and tell it to update itself
                $this->address->id = $row['address_id'];
                // The address properties (city, zip, etc.) should already be set 
                // on $this->address by the controller before calling this method.
                if (!$this->address->update())
				{
                    throw new Exception("Address update failed.");
                }
            }
            $this->conn->commit();
            return true;

        }
		catch (Exception $e)
		{
            $this->conn->rollBack();
            return false;
        }
    }

    // ------------------------------------------------------------------
    // 5. DELETE (Strict Mode) TODO: double check this function
    // ------------------------------------------------------------------
    public function delete()
    {
        // A. Check for Active Orders (Status < 4)
        $checkQuery = "SELECT id FROM food_order WHERE food_place_id = ? AND order_status_id < 4 LIMIT 1";
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(1, $this->id);
        $checkStmt->execute();

        if ($checkStmt->rowCount() > 0)
		{
            return "BUSY"; // Custom error code
        }

        try
		{
            $this->conn->beginTransaction();

            // B. Get Address ID (to delete it later)
            $sqlGetAddr = "SELECT address_id FROM " . $this->table . " WHERE id = ?";
            $stmtGet = $this->conn->prepare($sqlGetAddr);
            $stmtGet->bindParam(1, $this->id);
            $stmtGet->execute();
            $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
            $addr_id_to_delete = $row['address_id'];

            // C. Delete FoodPlace
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $this->id = htmlspecialchars(strip_tags($this->id));
            $stmt->bindParam(':id', $this->id);

            if (!$stmt->execute()) throw new Exception("Delete failed.");

            // D. Delete Orphan Address
            if ($addr_id_to_delete)
			{
                $this->address->id = $addr_id_to_delete;
                if (!$this->address->delete())
					throw new Exception("Address delete failed.");
            }

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
