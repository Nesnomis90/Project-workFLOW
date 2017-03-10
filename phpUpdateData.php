<?php
require 'phpDBvars.php';

//UPDATE Syntax:
//	UPDATE FROM table_name
//	SET column1=value, column2=value2,...
//	WHERE some_column = some_value
//
//	IMPORTANT -> Everything in the table will be updated if the WHERE clause is not included

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	//	Create the SQL query
	$sql = "UPDATE MyGuests
	SET lastname='Doe'
	WHERE id=2";
	
	//	Prepare statement
	$stmt = $conn->prepare($sql);
	
	//	Execute the query
	$stmt->execute();
	
	//	Echo a message to say the update succeeded
	echo $stmt->rowCount() . " records UPDATED successfully";
	} 
catch(PDOException $e)
	{
	echo $sql . "<br>" . $e->getMessage();
	}
	
//Close connection
$conn->null;

?>