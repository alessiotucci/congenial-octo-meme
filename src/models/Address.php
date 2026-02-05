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

	public $customer_id; // Passed from frontend, used for filtering via the junction table

	// constructor
	public function __construct($db)
	{
		$this->conn = $db;
	}

// ------------------------------------------------------------------
    // 1. CREATE (Transactional: Address + Optional Customer Link)
    // ------------------------------------------------------------------
    public function create()
    {
        // 1. Log the inputs to see what we received
        error_log("[DEBUG] Address::create called");
        error_log("[DEBUG] Data: Unit=" . $this->unit_number . ", Street=" . $this->street_number . ", City=" . $this->city);
        error_log("[DEBUG] CustomerID is: " . ($this->customer_id ?? 'NULL'));

        // Check if we are already inside a transaction (e.g., called by FoodPlace)
        $isNestedTransaction = $this->conn->inTransaction();

        try {
            // A. START TRANSACTION (Only if not already in one)
            if (!$isNestedTransaction) {
                $this->conn->beginTransaction();
            }

            // B. INSERT INTO ADDRESS TABLE
            $query = 'INSERT INTO ' . $this->table . ' 
                      (unit_number, street_number, address_line1, address_line2, 
                       city, region, postal_code, country_id)
                      VALUES 
                      (:unit_number, :street_number, :address_line1, :address_line2, 
                       :city, :region, :postal_code, :country_id)';

            $stmt = $this->conn->prepare($query);

            // Sanitize
            
			//$this->unit_number = htmlspecialchars(strip_tags($this->unit_number));
            $this->street_number = htmlspecialchars(strip_tags($this->street_number));
            $this->address_line1 = htmlspecialchars(strip_tags($this->address_line1));
            
			//$this->address_line2 = htmlspecialchars(strip_tags($this->address_line2));
            
			$this->city = htmlspecialchars(strip_tags($this->city));
            $this->region = htmlspecialchars(strip_tags($this->region));
            $this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
            $this->country_id = htmlspecialchars(strip_tags($this->country_id));

            // Bind
            $stmt->bindParam(':unit_number', $this->unit_number);
            $stmt->bindParam(':street_number', $this->street_number);
            $stmt->bindParam(':address_line1', $this->address_line1);
            $stmt->bindParam(':address_line2', $this->address_line2);
            $stmt->bindParam(':city', $this->city);
            $stmt->bindParam(':region', $this->region);
            $stmt->bindParam(':postal_code', $this->postal_code);
            $stmt->bindParam(':country_id', $this->country_id);

            if (!$stmt->execute()) {
                // Log specific SQL errors
                $errorInfo = $stmt->errorInfo();
                error_log("[ERROR] Address INSERT failed: " . $errorInfo[2]);
                throw new Exception("Failed to insert into Address table: " . $errorInfo[2]);
            }

            // GET THE NEW ID
            $this->id = $this->conn->lastInsertId();
            error_log("[DEBUG] Address created successfully with ID: " . $this->id);

            // C. INSERT INTO CROSS REFERENCE TABLE (customer_address)
            // CRITICAL FIX: Only try to link if we actually have a customer_id!
            // FoodPlace creation does NOT have a customer_id, so this was failing before.
            if (!empty($this->customer_id)) {
                error_log("[DEBUG] Linking Address " . $this->id . " to Customer " . $this->customer_id);
                
                $linkQuery = 'INSERT INTO customer_address (customer_id, address_id) VALUES (:cust_id, :addr_id)';
                $linkStmt = $this->conn->prepare($linkQuery);

                $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
                
                $linkStmt->bindParam(':cust_id', $this->customer_id);
                $linkStmt->bindParam(':addr_id', $this->id);

                if (!$linkStmt->execute()) {
                    $linkError = $linkStmt->errorInfo();
                    error_log("[ERROR] Customer-Address Link failed: " . $linkError[2]);
                    throw new Exception("Failed to link address to customer.");
                }
            } else {
                error_log("[DEBUG] Skipping customer link (customer_id is empty)");
            }

            // D. COMMIT TRANSACTION (Only if we started it)
            if (!$isNestedTransaction) {
                $this->conn->commit();
            }
            return $this->id;

        } catch (Exception $e) {
            // E. ROLLBACK ON FAILURE (Only if we started it)
            // If we are nested, we do NOT rollback here. We let the parent (FoodPlace) decide.
            if (!$isNestedTransaction && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            
            error_log("[ERROR] Address Transaction Failed: " . $e->getMessage());
            return false;
        }
    }

//paste it here
// 2. READ (Get All - rarely used for addresses, but good to have)
public function read()
{			//TODO: remove the select *, it can break things
	try
	{
		$query = 'SELECT * FROM ' . $this->table . ' ORDER BY id DESC';
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

public function read_by_customer()
    {
		$query = 'SELECT 
		ca.id as id, -- <--- THIS IS THE FIX. We grab the Link ID, not the Address ID.
		a.address_line1, 
		a.address_line2, 
		a.city, 
		a.postal_code 
	  FROM ' . $this->table . ' a
	  JOIN customer_address ca ON a.id = ca.address_id
	  WHERE ca.customer_id = :customer_id
	  ORDER BY ca.id DESC'; // Ordered by Link ID

        $stmt = $this->conn->prepare($query);
        $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
        $stmt->bindParam(':customer_id', $this->customer_id);
        $stmt->execute();
        return $stmt;
    }

}
?>
