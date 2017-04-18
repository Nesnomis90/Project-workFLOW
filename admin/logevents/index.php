<?php
// This is the Index file for the LOG EVENTS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// To delete the log event selected by the user
if (isset($_GET['deletelog'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
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
	
	$_SESSION['LogEventUserFeedback'] = "Successfully deleted the log event.";
	
	// Load Log Events list webpage with updated database
	header('Location: .');
	exit();
}

// Get log data we need to display it in our html template
try
{
	// Get the wanted amount of Log Events the user wants displayed
	//TO-DO: Make this an admin choice
	$logLimit = 100;

	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	// Use connect to Database function from db.inc.php
	$pdo = connect_to_db();
	
	//Retrieve log data from database
	//$sql = 'SELECT `logID`,`logDateTime` FROM `logevent`';
	$sql = 'SELECT 		l.logID, 
						DATE_FORMAT(l.logDateTime, "%d %b %Y %T") AS LogDate, 
						la.`name` AS ActionName, 
						la.description AS ActionDescription, 
						l.description AS LogDescription 
			FROM 		`logevent` l 
			JOIN 		`logaction` la 
			ON 			la.actionID = l.actionID
			ORDER BY 	UNIX_TIMESTAMP(l.logDateTime) 
			DESC
			LIMIT ' . $logLimit;
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();
	
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