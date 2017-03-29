<?php
// This is the Index file for the Connect folder

// Get database connection functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

// Check if user wants to submit something (this time it's a test for submitting a log description)
// This is done through isset which checks if the query string contains a variable named addlog
if (isset($_GET['addlog']))
{
	include_once 'insertLog.html.php';
	exit();
}

// To insert the values the user has submitted, we have to first get the text he has tried to submit
if (isset($_POST['LogDescription'])){
	
	try{
		// Use connect to Database function from db.inc.php
		$pdo = connect_to_db();
		
		// The SQL command we're going to use
		$logDescription = $_POST['LogDescription'];
		$sql = 'INSERT INTO `logevent` SET `actionID` = 10, `description` = :logDescription';
		
		// Using PREPARED STATEMENT to guard against dangerous characters
		// Avoids SQL injection since the database knows it's getting a value
		$s = $pdo->prepare($sql);
		$s->bindValue(':logDescription', $logDescription);
		$s->execute();
		
		//Close connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error adding log event: ' . $e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// If successfully added data, let's load the SAME webpage again so the new data shows up
	header('Location: .');
	exit();
}

// To delete the log event selected by the user
if (isset($_GET['deletelog'])){
	try
	{
		// Use connect to Database function from db.inc.php
		$pdo = connect_to_db();
		
		$logEventIDToDelete = $_POST['id'];
		$sql = 'DELETE FROM `logevent` WHERE `logID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $logEventIDToDelete);
		$s->execute();
		
		//Close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error deleting log: ' . $e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// refresh page
	header('Location: .');
	exit();
}

// Get log data we need to display it in our html template
try
{	
	// Get the wanted amount of Log Events the user wants displayed
	//TO-DO: Make this an admin choice
	$logLimit = 100;

	// Use connect to Database function from db.inc.php
	$pdo = connect_to_db();
	
	//Retrieve log data from database
	//$sql = 'SELECT `logID`,`logDateTime` FROM `logevent`';
	$sql = 'SELECT 	l.logID, 
					DATE_FORMAT(l.logDateTime, "%d %b %Y %T") AS LogDate, 
					la.`name` AS ActionName, la.description AS ActionDescription, 
					l.description AS LogDescription 
					FROM `logevent` l 
					JOIN `logaction` la 
					ON la.actionID = l.actionID
					ORDER BY UNIX_TIMESTAMP(l.logDateTime) 
					DESC
					LIMIT ' . $logLimit;
	$result = $pdo->query($sql);
	
	//Close connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching logevent: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	 
	$pdo = null;
	exit();
}

// Create the array we will go through to display information in HTML
foreach ($result as $row)
{
	//$log[] = $row['logDateTime'];
	$log[] = array(
		'id' => $row['logID'], 
		'date' => $row['LogDate'], 
		'actionName' => $row['ActionName'], 
		'actionDescription' => $row['ActionDescription'], 
		'logDescription' => $row['LogDescription']
		);
}
// Create the Log Event table in HTML
include_once 'log.html.php';
?>