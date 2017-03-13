<?php
require 'phpDBvars.php';

try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	
	//$sql =
	// use exec() because no results are returned
	//$conn->exec($sql);
	echo "Connected successfully";
	//echo "SQL query successful";
	} 
catch(PDOException $e)
	{
	echo "Connection failed: " . $e->getMessage();
	//echo $sql . "<br>" . $e->getMessage();
	}
	
//Close connection
$conn->null;
?>