<?php
require 'phpDBvars.php';

// Syntax rules:
//	The SQL query must be quoted inPHP
//	String values inside the SQL query must be quoted
//	Numeric values must not be quoted
//	The word NULL must not be quoted
//	Auto-Increment and TIMESTAMP is handled automatically by MySQL

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	
	$sql = "INSERT INTO MyGuests (firstname, lastname, email)
	VALUES ('John', 'Doe', 'john@example.com')";
	// use exec() because no results are returned
	$conn->exec($sql);
	$last_id = $conn->lastInsertId();
	echo "New record created successfully. Last inserted ID is : " . $last_id;
	} 
catch(PDOException $e)
	{
	echo $sql . "<br>" . $e->getMessage();
	}
	
//Close connection
$conn->null;

?>