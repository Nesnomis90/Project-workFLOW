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
	
	// begin the transaction
	$conn->beginTransaction();
	// our SQL statements
		// use exec() because no results are returned
	$conn->exec("INSERT INTO MyGuests (firstname, lastname, email)
	VALUES ('John', 'Doe', 'john@example.com')");
	$conn->exec("INSERT INTO MyGuests (firstname, lastname, email)
	VALUES ('Mary', 'Moe', 'mary@example.com')");
	$conn->exec("INSERT INTO MyGuests (firstname, lastname, email)
	VALUES ('Julie', 'Dooley', 'julie@example.com')");	
	
	// commit the transactio
	$conn->commit();
	echo "New records created successfully";
	} 
catch(PDOException $e)
	{
	// roll back the transaction if something failed
	$conn->rollback();
	echo "Error: " . $e->getMessage();
	}
	
//Close connection
$conn->null;

?>