<?php
// This is the Index file for the LOG EVENTS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// If admin wants to be able to delete logs it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['logEventsEnableDelete'] = TRUE;
}

// If admin wants to be disable log deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['logEventsEnableDelete']);
}

// If admin wants to change the amount of log events displayed
if (isset($_POST['action']) AND $_POST['action'] == "Set New Maximum"){
	$setNewMaximum = TRUE;
}

// If admin wants to change what type of logs to display
if (isset($_POST['action']) AND $_POST['action'] == "Refresh Logs"){
	// TO-DO:
	if(isset($_POST['searchAll'])){
		$numberOfCheckboxesActivated = 1;
	} else {
		$numberOfCheckboxesActivated = 0;
		
		if(isset($_POST['search']) AND !empty($_POST['search'])) {
			// The user has checked some checkmarks
				// Let's check how many are activated
			foreach($_POST['search'] AS $check){
				$numberOfCheckboxesActivated++;
			}
			
			
		} else {
			// The user has not checked any checkmarks. Let's tell the user
			$_SESSION['LogEventUserFeedback'] = "You need to select at least one category of log events to display with the checkboxes.";
		}		
	}
}

// To delete the log event selected by the user
if (isset($_POST['action']) AND $_POST['action'] == "Delete"){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Use connect to Database function from db.inc.php
		$pdo = connect_to_db();
		
		$logEventIDToDelete = $_POST['id'];
		$sql = 'DELETE FROM `logevent` 
				WHERE 		`logID` = :id';
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

if(!isset($numberOfCheckboxesActivated)){
	$numberOfCheckboxesActivated = 1;
}

//	Make sure admin has selected a category of log events to show, if not we can't show anything.
if($numberOfCheckboxesActivated > 0){
	
	// Get log data we need to display it in our html template
	try
	{
		if(!isset($setNewMaximum)){
			$setNewMaximum = FALSE;
		}
		// Get the wanted amount of Log Events the user wants displayed
		//TO-DO: Make this an admin choice
		if(isset($_POST['logsToShow']) AND $setNewMaximum){
			$logLimit = $_POST['logsToShow'];
			
			if ($logLimit < 0 OR $logLimit > 1000){
				$logLimit = 10;
			}	
			$setNewMaximum = FALSE;
		} else {
			$logLimit = 10;
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Use connect to Database function from db.inc.php
		$pdo = connect_to_db();
		
		//Retrieve log data from database
		$sql = 'SELECT 		l.logID, 
							DATE_FORMAT(l.logDateTime, "%d %b %Y %T") 	AS LogDate, 
							la.`name` 									AS ActionName, 
							la.description 								AS ActionDescription, 
							l.description 								AS LogDescription 
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
} else {
	$rowNum = 0;
}

// Create the Log Event table in HTML
include_once 'log.html.php';
?>