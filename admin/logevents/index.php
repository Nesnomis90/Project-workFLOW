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
	$refreshLogs = TRUE;
}

// If admin wants to be disable log deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['logEventsEnableDelete']);
	$refreshLogs = TRUE;
}

// Let's define what checkboxes should be displayed
$checkboxes[] = array(
							// Log action name`			//text displayed			// If line feed // if checked
						array('Account Activated', 		'Account Activated', 		FALSE, 			FALSE),
						array('Account Created', 		'Account Created', 			FALSE, 			FALSE),
						array('Account Removed', 		'Account Removed', 			TRUE, 			FALSE),
						array('Booking Cancelled', 		'Booking Cancelled', 		FALSE, 			FALSE),
						array('Booking Completed', 		'Booking Completed', 		FALSE, 			FALSE),
						array('Booking Created', 		'Booking Created', 			FALSE, 			FALSE),
						array('Booking Removed', 		'Booking Removed', 			TRUE, 			FALSE),
						array('Company Created', 		'Company Created', 			FALSE, 			FALSE),
						array('Company Removed', 		'Company Removed', 			TRUE, 			FALSE),
						array('Database Created', 		'Database Created', 		FALSE, 			FALSE),
						array('Table Created', 			'Database Table Created', 	TRUE,			FALSE),
						array('Employee Added', 		'Employee Added', 			FALSE, 			FALSE),
						array('Employee Removed', 		'Employee Removed', 		TRUE, 			FALSE),
						array('Equipment Added', 		'Equipment Added', 			FALSE,			FALSE),
						array('Equipment Removed', 		'Equipment Removed', 		TRUE, 			FALSE),
						array('Meeting Room Added', 	'Meeting Room Added', 		FALSE, 			FALSE),
						array('Meeting Room Removed', 	'Meeting Room Removed', 	TRUE, 			FALSE),
						array('Room Equipment Added', 	'Room Equipment Added',		FALSE, 			FALSE),
						array('Room Equipment Removed', 'Room Equipment Removed', 	TRUE, 			FALSE)
					);		


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
	$refreshLogs = TRUE;
	// Load Log Events list webpage with updated database
	header('Location: .');
	exit();
}

// If admin wants to change what type of logs to display
// or the max amount of logs
if (isset($_POST['action']) AND $_POST['action'] == "Refresh Logs" OR 
	isset($_POST['action']) AND $_POST['action'] == "Set New Maximum" OR 
	isset($refreshLogs) AND $refreshLogs){

	if(isset($_POST['logsToShow'])){
		$newLogLimit = $_POST['logsToShow'];

		if ($newLogLimit < 10 OR $newLogLimit > 1000){
			$newLogLimit = 10;
		}			
	}

	if(isset($_POST['searchAll'])){
		$numberOfCheckboxesActivated = 1;
	} else {
		$numberOfCheckboxesActivated = 0;
		
		if(isset($_POST['search']) AND !empty($_POST['search'])) {
			// The user has checked some checkmarks
			
				// Let's check how many are activated
			foreach($_POST['search'] AS $check){
				if($numberOfCheckboxesActivated == 0){
					$sqlAdd = " WHERE la.`name` = '" . $check . "'";
				} else {
					$sqlAdd .= " OR la.`name` = '" . $check ."'";
				}
				$numberOfCheckboxesActivated++;
				
				// Let's remember what checkboxes have been checked
					// We pass the array by reference so we can edit the values
				foreach($checkboxes AS &$checkbox){
					foreach($checkbox AS &$info){

						if($check == $info[0]){
							// Update the checkmark status from FALSE to TRUE
							$info[3] = TRUE;
							unset($info); 	// <-- This is IMPORTANT. We need to say we're done with that reference
											// Or else the original array gets all messed up.
											
							// No need to look through the array more, we've already updated the value
							break 2;	
						}	
					}
				}
			}
			
		} else {
			// The user has not checked any checkmarks. Let's tell the user
			$_SESSION['LogEventUserFeedback'] = "You need to select at least one category of log events to display with the checkboxes.";
		}		
	}
}


if(!isset($numberOfCheckboxesActivated)){
	// Default 
	$numberOfCheckboxesActivated = 1;
	
}

// Fix the amount of logs to display
if (isset($newLogLimit)){
	$logLimit = $newLogLimit;
} else {
	if(isset($_POST['logsToShow'])){
		$logLimit = $_POST['logsToShow'];
	} else {
		$logLimit = 10;
	}
}

// We handle when the checkbox "All" should be checked.
if (isset($_POST['search']) AND !empty($_POST['search']) AND !isset($_POST['searchAll'])){
	$checkAll = "";
} elseif(!isset($_POST['search']) AND !isset($_POST['searchAll'])){
	$checkAll = "";
} else {
	$checkAll = 'checked="checked"';
}

// We start validating date inputs
$invalidInput = FALSE;

if (!isset($_POST['filterStartDate'])){
	$filterStartDate = '';
} else {
	$filterStartDate = trim($_POST['filterStartDate']);
}

if(!$invalidInput AND $filterStartDate != ""){
	$validatedStartDate = correctDatetimeFormat($filterStartDate);
	$displayValidatedStartDate = convertDatetimeToFormat($validatedStartDate , 'Y-m-d H:i:s', 'F jS Y H:i');
	if($validatedStartDate === FALSE){
		// The user submitted a start date in a format we had not expected
		$_SESSION['LogEventUserFeedback'] = "The start date you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}		
}

if (!isset($_POST['filterEndDate'])){
	$filterEndDate = '';
} else {
	$filterEndDate = trim($_POST['filterEndDate']);	
}

if(!$invalidInput AND $filterEndDate != ""){
	$validatedEndDate = correctDatetimeFormat($filterEndDate);
	$displayValidatedEndDate = convertDatetimeToFormat($validatedEndDate, 'Y-m-d H:i:s', 'F jS Y H:i');
	if($validatedEndDate === FALSE){
		// The user submitted a start date in a format we had not expected
		$_SESSION['LogEventUserFeedback'] = "The end date you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}		
}


// Check if admin has even checked any boxes yet, if not just give a warning
if (!isset($_POST['search']) AND !isset($_POST['searchAll']) AND !$invalidInput){
	$_SESSION['LogEventUserFeedback'] = "You need to select at least one category of log events with the checkboxes.";
	$invalidInput = TRUE;
}

if($invalidInput){
	// We've found some invalid user inputs
	include_once 'log.html.php';
	exit();	
}

if(!isset($sqlAdd)){
	// We've not added any additional SQL code yet
	if($filterStartDate != '' AND $filterEndDate != ''){
		// Both dates are filled out. Use BETWEEN for MySQL
		$sqlAddDates = ' WHERE (l.`logDateTime` BETWEEN :filterStartDate AND :filterEndDate) ';
		$useBothDates = TRUE;
	} elseif($filterStartDate == '' AND $filterEndDate != ''){
		// Only end date is filled out. Use less than
		$sqlAddDates = ' WHERE (l.`logDateTime` < :filterEndDate) ';
		$useEndDate = TRUE;
	} elseif($filterStartDate != '' AND $filterEndDate == ''){
		// Only start date is filled out. Use greater than
		$sqlAddDates = ' WHERE (l.`logDateTime` > :filterStartDate) ';
		$useStartDate = TRUE;
	}
} else {
	// We've already altered the sql code earlier, which means we've already started a "WHERE"-segment
	if($filterStartDate != '' AND $filterEndDate != ''){
		// Both dates are filled out. Use BETWEEN for MySQL
		$sqlAddDates = ' AND (l.`logDateTime` BETWEEN :filterStartDate AND :filterEndDate) ';
		$useBothDates = TRUE;
	} elseif($filterStartDate == '' AND $filterEndDate != ''){
		// Only end date is filled out. Use less than
		$sqlAddDates = ' AND (l.`logDateTime` < :filterEndDate) ';
		$useEndDate = TRUE;
	} elseif($filterStartDate != '' AND $filterEndDate == ''){
		// Only start date is filled out. Use greater than
		$sqlAddDates = ' AND (l.`logDateTime` > :filterStartDate) ';
		$useStartDate = TRUE;
	}	
}

//	Make sure admin has selected a category of log events to show, if not we can't show anything.
if($numberOfCheckboxesActivated > 0){
	
	// Get log data we need to display it in our html template
	try
	{

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Use connect to Database function from db.inc.php
		$pdo = connect_to_db();
		
		//Retrieve log data from database
		if (isset($sqlAddDates)){
			// We want to filter by date, we need to use a prepared statement
			if (isset($sqlAdd)){
			$sql = 'SELECT 		l.logID, 
								DATE_FORMAT(l.logDateTime, "%d %b %Y %T") 	AS LogDate, 
								la.`name` 									AS ActionName, 
								la.description 								AS ActionDescription, 
								l.description 								AS LogDescription 
					FROM 		`logevent` l 
					JOIN 		`logaction` la 
					ON 			la.actionID = l.actionID' . $sqlAdd . $sqlAddDates . ' 
					ORDER BY 	UNIX_TIMESTAMP(l.logDateTime) 
					DESC
					LIMIT ' . $logLimit;				
			} else {
				$sql = 'SELECT 		l.logID, 
									DATE_FORMAT(l.logDateTime, "%d %b %Y %T") 	AS LogDate, 
									la.`name` 									AS ActionName, 
									la.description 								AS ActionDescription, 
									l.description 								AS LogDescription 
						FROM 		`logevent` l 
						JOIN 		`logaction` la 
						ON 			la.actionID = l.actionID' . $sqlAddDates . ' 
						ORDER BY 	UNIX_TIMESTAMP(l.logDateTime) 
						DESC
						LIMIT ' . $logLimit;			
			}
			
			$s = $pdo->prepare($sql);
			if (isset($useBothDates) AND $useBothDates){
				$s->bindValue(':filterStartDate', $validatedStartDate);
				$s->bindValue(':filterEndDate', $validatedEndDate);			
			}
			if (isset($useStartDate) AND $useStartDate){
				$s->bindValue(':filterStartDate', $validatedStartDate);			
			}			
			if (isset($useEndDate) AND $useEndDate){
				$s->bindValue(':filterEndDate', $validatedEndDate);			
			}	
			
			$s->execute();
			
			$result = $s->fetchAll();
			$rowNum = sizeOf($result);
		} else {
			// We don't want to filter by date, we just use a standard query
			if (isset($sqlAdd)){
			$sql = 'SELECT 		l.logID, 
								DATE_FORMAT(l.logDateTime, "%d %b %Y %T") 	AS LogDate, 
								la.`name` 									AS ActionName, 
								la.description 								AS ActionDescription, 
								l.description 								AS LogDescription 
					FROM 		`logevent` l 
					JOIN 		`logaction` la 
					ON 			la.actionID = l.actionID' . $sqlAdd . ' 
					ORDER BY 	UNIX_TIMESTAMP(l.logDateTime) 
					DESC
					LIMIT ' . $logLimit;				
			} else {
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
			}
			
			$result = $pdo->query($sql);
			$rowNum = $result->rowCount();		
		}

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