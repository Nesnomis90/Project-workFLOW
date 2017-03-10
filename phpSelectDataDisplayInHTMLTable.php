<!DOCTYPE html>
<html>
<body>

<?php
require 'phpDBvars.php';

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
//	SELECT <command/column> FROM table_name
//
//	* command selects all data
//	column name retrieves only data from that column
//	multiple columns can be selected at once (SELECT column1, column2)


// Try to connect and retrieve data from DB
try {
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
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
	echo "Error: " . $e->getMessage();
}
$conn = null;
echo "</table>";

?>

</body>
</html>