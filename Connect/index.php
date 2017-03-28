<?php
require_once 'phpDBconnect.php';

//Make sure our database and tables exist
create_db();
create_tables();

// Get log data we need to display it in our html template
try
{
	//Use connect to Database function from phpDBconnect.php
	$pdo = connect_to_db();
	
	//Retrieve log data from database
	$sql = 'SELECT `logDateTime` FROM `logevent`';
	$result = $pdo->query($sql);
}
catch (PDOException $e)
{
	 $error = 'Error fetching logevent: ' . $e->getMessage();
	 include 'error.html.php';
	 
	 $pdo = null;
	 exit();
}
foreach ($result as $row)
{
	$log[] = $row['logDateTime'];
}
include 'log.html.php';
?>