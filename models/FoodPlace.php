/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: FoodPlace.php                                                      */
/*   Created: 2026/01/20 19:28:10 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/21 13:14:53                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

//TODO: refactor this class to have a small instance of address inside of it!

<?php
// class for the FoodPlace
include_once 'Address.php'; // Ensure we can see the smaller class
class FoodPlace
{
    private $conn;
    private $table = 'food_place'; // The MySQL table name

    // Properties that match the table columns
    public $id;
    public $name;
    public $address_id; // this is a FK
    public $average_rating;
    public $total_reviews;
    public $food_type;
    public $description;
    public $opening_hours;

	// Address Properties (We need these here to receive data from the API)
	//TODO:or we can just create an istance of the class Address inside of it!
    public $unit_number;
    public $street_number;
    public $address_line1;
    public $address_line2;
    public $region;
    public $postal_code;
    public $country_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method to create a new food place TODO: do not work with the FK
    public function create()
    {
        $query = 'INSERT INTO ' . $this->table . ' SET
            name = :name,
            address_id = :address_id,
            average_rating = :average_rating,
            total_reviews = :total_reviews,
            food_type = :food_type,
            description = :description,
            opening_hours = :opening_hours';

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->address_id = htmlspecialchars(strip_tags($this->address_id));
        $this->average_rating = htmlspecialchars(strip_tags($this->average_rating));
        $this->total_reviews = htmlspecialchars(strip_tags($this->total_reviews));
        $this->food_type = htmlspecialchars(strip_tags($this->food_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->opening_hours = htmlspecialchars(strip_tags($this->opening_hours));

        // Bind parameters
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':address_id', $this->address_id);
        $stmt->bindParam(':average_rating', $this->average_rating);
        $stmt->bindParam(':total_reviews', $this->total_reviews);
        $stmt->bindParam(':food_type', $this->food_type);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':opening_hours', $this->opening_hours);

        if ($stmt->execute())
			{
            printf("Success! Created the food place!\n");
            return true;
        }
		else
		{
            printf("Error: %s.\n", $stmt->error);
            return false;
        }
    }
	public function createWithAddress()
	{
        try
		{
            $this->conn->beginTransaction();
            // 1. COMPOSITION: Create the "Smaller" Object
            // We pass OUR connection to the new Address object
            $address = new Address($this->conn);
            // 2. Pass data to the smaller object
			$address->unit_number = $this->unit_number;
			$address->street_number = $this->street_number;
            $address->address_line1 = $this->address_line1;
			$address->address_line2 = $this->address_line2;
			$address->region = $this->region;
			$address->postal_code = $this->postal_code;
			$address->country_id = $this->country_id;
			// 3. CALL THE METHOD (Get the ID back)
            $address_id = $address->create(); 
            if (!$address_id)
			{
                throw new Exception("Failed to create address.");
            }
            // 4. Create the FoodPlace using that ID
            $query = 'INSERT INTO ' . $this->table . ' 
                      SET 
                        name = :name,
                        address_id = :addr_id,  /* <--- The Critical Link */
                        food_type = :food_type,
                        description = :desc,
                        opening_hours = :hours';

            $stmt = $this->conn->prepare($query);

            // Sanitize FoodPlace data
            $this->name = htmlspecialchars(strip_tags($this->name));
            $this->food_type = htmlspecialchars(strip_tags($this->food_type));
            $this->description = htmlspecialchars(strip_tags($this->description));
            $this->opening_hours = htmlspecialchars(strip_tags($this->opening_hours));

            // Bind Params
            $stmt->bindParam(':name', $this->name);
            $stmt->bindParam(':addr_id', $address_id); // Use the variable, not $this->address_id
            $stmt->bindParam(':food_type', $this->food_type);
            $stmt->bindParam(':desc', $this->description);
            $stmt->bindParam(':hours', $this->opening_hours);

            if (!$stmt->execute())
			{
                throw new Exception("Failed to create Food Place.");
            }
            // 5. Commit (Save everything)
            $this->conn->commit();
            return true;

        }
		catch (Exception $e)
		{
            $this->conn->rollBack();
            return false;
        }
    }

    // Method to fetch a food place by ID
    public function read()
    {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE id = ? LIMIT 0,1';
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->name = $row['name'];
            $this->address_id = $row['address_id'];
            $this->average_rating = $row['average_rating'];
            $this->total_reviews = $row['total_reviews'];
            $this->food_type = $row['food_type'];
            $this->description = $row['description'];
            $this->opening_hours = $row['opening_hours'];
            return true;
        }
		else
		{
            return false;
        }
    }
}
?>
