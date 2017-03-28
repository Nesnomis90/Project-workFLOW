<?php
// Code to deal with MAGICQUOTES, a feature added to protect against dangerous characters.
// But it's not wise to use with SQL statement
if (get_magic_quotes_gpc())
{
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while (list($key, $val) = each($process))
	{
		foreach ($val as $k => $v)
		{
			unset($process[$key][$k]);
			if (is_array($v))
			{
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			}
			else
			{
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

// Index file for Connect folder
require_once 'phpDBconnect.php';

// Check if user wants to submit something (this time it's a test for submitting a log description)
// This is done through isset which checks if the query string contains a variable named addlog
if (isset($_GET['addlog']))
{
	include 'insertLog.html.php';
	exit();
}

// To insert the values the user has submitted, we have to first get the text he has tried to submit
if (isset($_POST['LogDescription'])){
	
	try{
		// Use connect to Database function from phpDBconnect.php
		$pdo = connect_to_db();
		
		// The SQL command we're going to use
		$logDescription = $_POST['LogDescription'];
		$sql = 'INSERT INTO `logevent` SET `actionID` = 10, `description` = :logDescription';
		
		// Using PREPARED STATEMENT to guard against dangerous characters
		// Avoids SQL injection since the database knows it's getting a value
		$s = $pdo->prepare($sql);
		$s->bindValue(':logDescription', $logDescription);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$error = 'Error adding log event: ' . $e->getMessage() . '<br />';
		include 'error.html.php';
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
		// Use connect to Database function from phpDBconnect.php
		$pdo = connect_to_db();
		
		$logEventIDToDelete = $_POST['id'];
		$sql = 'DELETE FROM `logevent` WHERE `logID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $logEventIDToDelete);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error deleting log: ' . $e->getMessage() . '<br />';
		include 'error.html.php';
		exit();
	}
	
	// refresh page
	header('Location: .');
	exit();
}



// Get log data we need to display it in our html template
try
{	
	// Use connect to Database function from phpDBconnect.php
	$pdo = connect_to_db();
	
	//Retrieve log data from database
	//$sql = 'SELECT `logID`,`logDateTime` FROM `logevent`';
	$sql = 'SELECT l.logID, l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription FROM `logevent` l JOIN `logaction` la ON la.actionID = l.actionID';
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
	//$log[] = $row['logDateTime'];
	$log[] = array(
		'id' => $row['logID'], 
		'date' => $row['LogDate'], 
		'actionName' => $row['ActionName'], 
		'actionDescription' => $row['ActionDescription'], 
		'logDescription' => $row['LogDescription']
		);
}
include 'log.html.php';
?>