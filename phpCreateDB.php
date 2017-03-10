<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDB";

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	
	$sql = "CREATE DATABASE myDBPDO";
	// use exec() because no results are returned
	$conn->exec($sql);
	echo "Database created successfully<br>";
	} 
catch(PDOException $e)
	{
	echo $sql . "<br>" . $e->getMessage();
	}
	
//Close connection
$conn->null;
?>