<?php
require 'phpDBvars.php';

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// PREPARED STATEMENTS are VERY IMPORTANT when inserting any data from external sources (user input)
	// to sanitize and validate the data that is being submitted.
	// Very useful against SQL injections.
	
	
	// prepare SQL and bind parameters
	$stmt = $conn->prepare("INSERT INTO MyGuests (firstname, lastname, email)
	VALUES (:firstname, :lastname, :email)");
	$stmt->bindParam(':firstname', $firstname);
	$stmt->bindParam(':lastname', $lastname);
	$stmt->bindParam(':email', $email);
	
	// insert a row
	$firstname = "John";
	$lastname = "Doe";
	$email = "john@example.com";
	$stmt->execute();
	
	// insert another row
	$firstname = "Mary";
	$lastname = "Moe";
	$email = "mary@example.com";
	$stmt->execute();
	
	// insert another row
	$firstname = "Julie";
	$lastname = "Dooley";
	$email = "julie@example.com";
	$stmt->execute();
	
	echo "New records created successfully";
	} 
catch(PDOException $e)
	{
	echo "Error: " . $e->getMessage();
	}
	
//Close connection
$conn->null;
//$stmt close or null?
?>