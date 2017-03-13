<?php
require 'phpDBvars.php';

//DELETE Syntax:
//	DELETE FROM table_name
//	WHERE some_column = some_value
//
//	IMPORTANT -> Everything in the table will be deleted if the WHERE clause is not included

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//	Create the SQL query
	$sql = "DELETE FROM MyGuests WHERE id=3";
	
	// use exec() because no results are returned
	$conn->exec($sql);
	echo "Record deleted successfully";
	} 
catch(PDOException $e)
	{
	echo $sql . "<br>" . $e->getMessage();
	}
	
//Close connection
$conn->null;

?>