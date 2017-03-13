<?php
$pdo = null;

//$dbengine 	= 'mysql';
$dbhost 	= 'localhost';
$dbuser		= 'root';
$dbpassword = '5Bdp32LAHYQ8AemvQM9P';
$dbname		= 'meetingflow';

//	Connect to an existing database
function connect_to_db()
{
	try {
	//	Create connection with an existing database
	$pdo = new PDO("mysql:host=$dbhost; dbname=$dbname", $dbuser, $dbpassword);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
	return $pdo;

	} 
catch(PDOException $e)
	{
	die("DB ERROR: " . $e->getMessage());
	}
}

// Connect to server and create our wanted database
function create_db()
{
	try {
	//	Create connection without an existing database
	$pdo = new PDO("mysql:host=$dbhost", $dbuser, $dbpassword);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// Creating the SQL query to make the database
	$sql = "CREATE DATABASE " . $dbname;

	//Executing the SQL query
	$pdo->exec($sql) or die (print_r($conn->errorInfo(), true));
	//Closing the connection
	$pdo = null;
	

	} 
catch(PDOException $e)
	{
	die("DB ERROR: " . $e->getMessage());
	}	
}


// ATTEMPT TO CREATE THE DATABASE!!!
create_db();

?>