<?php
//$dbengine 	= 'mysql';
define('dbhost', 'localhost');
define('dbuser', 'root');
define('dbpassword', '5Bdp32LAHYQ8AemvQM9P');
define('dbname', 'meetingflow');

// Connect to server and create our wanted database
function create_db()
{
	$pdo = null;

	try {
	//	Create connection without an existing database
	$pdo = new PDO("mysql:host=".dbhost, dbuser, dbpassword);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// Creating the SQL query to make the database
	$sql = "CREATE DATABASE IF NOT EXISTS " . dbname;

	//Executing the SQL query
	$pdo->exec($sql);
	$output = 'Created database ' . dbname . "<br>";
	include 'output.html.php';
	
	//Closing the connection
	$pdo = null;
	
	} 
catch(PDOException $e)
	{
	$output = 'Unable to create the database.<br>';
	include 'output.html.php';
	die("DB ERROR: " . $e->getMessage());
	}	
}

//	Connect to an existing database
function connect_to_db()
{
	$pdo = null;
	try {
	//	Create connection with an existing database
	$pdo = new PDO("mysql:host=".dbhost."; dbname=" .dbname, dbuser, dbpassword);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// use the correct database
	//$sql = "USE " . dbname;
	//$pdo->exec($sql);
	
	$output = "Succesfully connected to database: " . dbname . "<br>";
	include 'output.html.php';
	
	return $pdo;

	} 
catch(PDOException $e)
	{
	$output = 'Unable to connect to the database.<br>';
	include 'output.html.php';
	$pdo = null;	// Close connection
	die("DB ERROR: " . $e->getMessage());

	}
}

function create_tables()
{
	try
	{
		//	Connect to the database so we can create tables in it
		$conn = connect_to_db();
		// The SQL queries of the tables we want to create
		$sql = "CREATE ";
	
		$conn->exec($sql);
	}
	catch(PDOException $e)
	{
		$output = 'Unable to create tables in ' . dbname . "<br>";
		die("DB ERROR: " . $e->getMessage());
	}

}
?>