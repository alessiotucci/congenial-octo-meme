/* ************************************************************************** */
/*                                                                            */
/*   Host: DESKTOP-TQURMND                                                    */
/*   File: food_place.php                                                     */
/*   Created: 2026/01/20 19:28:10 | By: marvin <marvin@42.fr>                 */
/*   Updated: 2026/01/20 19:36:25                                             */
/*   OS: WindowsNT 2 x86 | CPU: c:\programdata\chocolatey\lib\unx             */
/*                                                                            */
/* ************************************************************************** */

<?php
// class for the FoodPlace
class FoodPlace
{
    private $conn;
    private $table = 'food_place'; // The MySQL table name

    // Properties that match the table columns
    public $id;
    public $name;
    public $address_id;
    public $average_rating;
    public $total_reviews;
    public $food_type;
    public $description;
    public $opening_hours;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Method to create a new food place
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
