<!DOCTYPE html>
<html>
<body>

<?php
require 'phpDBconnect.php';

//	HTML code to create the table to hold the retrieved data
echo "<table style ='border: solid 1px black;'>";
echo "<tr><th>Id</th><th>Firstname</th><th>Lastname</th></tr>";

//RecursiveArrayIterator instead?
class TableRows extends RecursiveIteratorIterator {
	function __construct($it) {
		parent::__construct($it, self::LEAVES_ONLY);
	}
	
	function current() {
		return "<td style='width:150px;border:1px solid black;'>" . parent::current(). "</td>";
	}
	
	function beginChildren() {
		echo "<tr>";
	}
	
	function endChildren() {
		echo "</tr" . "\n";
	}
}

//	SELECT Syntax:
//	SELECT <command/column> 
//	FROM table_name
//
//	* command selects all data
//	column name retrieves only data from that column
//	multiple columns can be selected at once (SELECT column1, column2)
//
//	To limit the number of records to retrieve use the LIMIT command
// 	To change what records to start from use the OFFSET command
//
//	SELECT a,b,c... FROM x LIMIT int_a OFFSET int_b


// Try to connect and retrieve data from DB
try {
	$conn = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_Exception);
	
	// prepare SQL request query
	$stmt = $conn->prepare("SELECT id, firstname, lastname FROM MyGuests");
	$stmt->execute();
	
	// set the resulting array to associative
	$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
	foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
		echo $v;
	}
}
catch(PDOException $e){
	$error = "Error: " . $e->getMessage();
	include 'error.html.php';
}
$conn = null;		//Close connection
echo "</table>";	//End syntax for the created table

?>

</body>
</html>