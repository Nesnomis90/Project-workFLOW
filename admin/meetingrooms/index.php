<?php
// This is the index file for the MEETING ROOMS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to check if user inputs for meeting room are correct
function validateUserInputs(){
	$invalidInput = FALSE;
	
	// Get user inputs
	if(isset($_POST['MeetingRoomName']) AND !$invalidInput){
		$meetingRoomName = trim($_POST['MeetingRoomName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "A meeting room cannot be added without a name!";
	}
	if(isset($_POST['MeetingRoomDescription']) AND !$invalidInput){
		$meetingRoomDescription = trim($_POST['MeetingRoomDescription']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "A meeting room cannot be added without a description!";
	}
	if(isset($_POST['MeetingRoomCapacity']) AND !$invalidInput){
		$meetingRoomCapacity = trim($_POST['MeetingRoomCapacity']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "A meeting room cannot be added without setting a room capacity!";
	}
	if(isset($_POST['MeetingRoomLocation']) AND !$invalidInput){
		$meetingRoomLocation = trim($_POST['MeetingRoomLocation']);
	} else {
		//$invalidInput = TRUE;
		//$_SESSION['AddMeetingRoomError'] = "A meeting room cannot be added without adding a room location!"; // To-DO: Change back to invalidInput if we implement this
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedMeetingRoomName = trimExcessWhitespace($meetingRoomName);
	$validatedMeetingRoomDescription = trimExcessWhitespaceButLeaveLinefeed($meetingRoomDescription);
	$validatedMeetingRoomCapacity = trimAllWhitespace($meetingRoomCapacity);
	$validatedMeetingRoomLocation = trimExcessWhitespace($meetingRoomLocation);
	
	// Do actual input validation
	if(validateString($validatedMeetingRoomName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "Your submitted meeting room name has illegal characters in it.";
	}
	if(validateString($validatedMeetingRoomDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "Your submitted meeting room description has illegal characters in it.";
	}
	if(validateIntegerNumber($validatedMeetingRoomCapacity) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "Your submitted meeting room capacity has illegal characters in it.";
	}
	if(validateString($validatedMeetingRoomLocation) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddMeetingRoomError'] = "Your submitted meeting room location has illegal characters in it.";
	}
	
	// Are values actually filled in?
	if($validatedMeetingRoomName == "" AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "You need to fill in a name for your meeting room.";	
		$invalidInput = TRUE;		
	}
	if($validatedMeetingRoomDescription == "" AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "You need to fill in a description for your meeting room.";	
		$invalidInput = TRUE;		
	}
	if($validatedMeetingRoomCapacity == "" AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "You need to fill in a maximum capacity for your meeting room.";	
		$invalidInput = TRUE;		
	}	
	if($validatedMeetingRoomLocation == "" AND !$invalidInput){
		// TO-DO: Add back in when we implement it
		//$_SESSION['AddMeetingRoomError'] = "You need to fill in a location for your meeting room.";	
		//$invalidInput = TRUE;		
	}
	
	// Check if input is allowed
		// MeetingRoomName
		// Uses same limit as display name (max 255 chars)
	$invalidMeetingRoomName = isLengthInvalidDisplayName($validatedMeetingRoomName);
	if($invalidMeetingRoomName AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "The meeting room name submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// MeetingRoomDescription
	$invalidMeetingRoomDescription = isLengthInvalidMeetingRoomDescription($validatedMeetingRoomDescription);
	if($invalidMeetingRoomDescription AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "The meeting room description submitted is too long.";	
		$invalidInput = TRUE;		
	}
		// MeetingRoomLocation
	$invalidMeetingRoomLocation = isLengthInvalidMeetingRoomLocation($validatedMeetingRoomLocation);
	if($invalidMeetingRoomLocation AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "The meeting room location submitted is too long.";	//TO-DO: Not sure if needed if we implement some kind of picture system for location
		$invalidInput = TRUE;		
	}
		// MeetingRoomCapacity
	$invalidMeetingRoomCapacity = isNumberInvalidMeetingRoomCapacity($validatedMeetingRoomCapacity);
	if($invalidMeetingRoomCapacity AND !$invalidInput){
		$_SESSION['AddMeetingRoomError'] = "The meeting room capacity submitted is not an acceptable number.";
		$invalidInput = TRUE;		
	}
	
	// Check if the meeting room already exists (based on name).
		// only if have changed the name (edit only)
	if(isset($_SESSION['EditMeetingRoomOriginalInfo']) AND $_SESSION['EditMeetingRoomOriginalInfo']['MeetingRoomName'] == $validatedMeetingRoomName){
		// Do nothing, since we haven't changed the name we're editing
	} else {
		// It's a new name, let's check if it has been used before!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT 	COUNT(*) 
					FROM 	`meetingroom`
					WHERE 	`name`= :MeetingRoomName';
			$s = $pdo->prepare($sql);
			$s->bindValue(':MeetingRoomName', $validatedMeetingRoomName);		
			$s->execute();
			
			$pdo = null;
			
			$row = $s->fetch();
			
			if ($row[0] > 0)
			{
				// This name is already being used for a meeting room

				$_SESSION['AddMeetingRoomError'] = "The name: " . $validatedMeetingRoomName . " is already used for a meeting room!";
				$invalidInput = TRUE;	
			}	
			// Meeting room name hasn't been used before
		}
		catch (PDOException $e)
		{
			$error = 'Error searching through meeting rooms.' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}	
	}
return array($invalidInput, $validatedMeetingRoomDescription, $validatedMeetingRoomName, $validatedMeetingRoomCapacity, $validatedMeetingRoomLocation);
}

// If admin wants to be able to delete bookings it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['meetingroomsEnableDelete'] = TRUE;
	$refreshMeetingRooms = TRUE;
}

// If admin wants to be disable booking deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['meetingroomsEnableDelete']);
	$refreshMeetingRooms = TRUE;
}

// If admin wants to remove a meeting room from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	// Delete selected meeting room from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `meetingroom` 
				WHERE 		`meetingRoomID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['MeetingRoomID']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting meeting room to delete: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	$_SESSION['MeetingRoomUserFeedback'] = "Successfully removed the meeting room.";
	
	// Add a log event that a meeting room was removed
	try
	{
		session_start();

		// Save a description with information about the meeting room that was removed
		$logEventDescription = "The meeting room: " . $validatedMeetingRoomName . " was removed by: " . $_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Meeting Room Removed'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->execute();
		
		//Close the connection
		$pdo = null;		
	}
	catch(PDOException $e)
	{
		$error = 'Error adding log event to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add a meeting room to the database
// we load a new html form
if (isset($_GET['add']) OR (isset($_SESSION['refreshMeetingRoomAddform']) AND $_SESSION['refreshMeetingRoomAddform']))
{
	// Check if the call was /?add/ or a forced refresh
	if(isset($_SESSION['refreshMeetingRoomAddform']) AND $_SESSION['refreshMeetingRoomAddform']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshMeetingRoomAddform']);
	}
	
	// Set form variables to be ready for adding values
	$meetingRoomName = '';
	$meetingRoomCapacity = '';
	$meetingRoomDescription = '';
	$meetingRoomLocation = '';

	// If we refreshed and want to keep the same values
	if(isset($_SESSION['AddMeetingRoomName'])){
		$meetingRoomName = $_SESSION['AddMeetingRoomName'];
		unset($_SESSION['AddMeetingRoomName']);
	}
	if(isset($_SESSION['AddMeetingRoomCapacity'])){
		$meetingRoomCapacity = $_SESSION['AddMeetingRoomCapacity'];
		unset($_SESSION['AddMeetingRoomCapacity']);
	}
	if(isset($_SESSION['AddMeetingRoomDescription'])){
		$meetingRoomDescription = $_SESSION['AddMeetingRoomDescription'];
		unset($_SESSION['AddMeetingRoomDescription']);
	}	
	if(isset($_SESSION['AddMeetingRoomLocation'])){
		$meetingRoomLocation = $_SESSION['AddMeetingRoomLocation'];
		unset($_SESSION['AddMeetingRoomLocation']);
	}		
	
	// Set always correct info
	$pageTitle = 'New Meeting Room';
	$action = 'addform';
	$button = 'Add room';
	$meetingRoomID = '';	
	
	// We want a reset all fields button while adding a new meeting room
	$reset = 'reset';
	
	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the meeting room
if (isset($_GET['addform']))
{
	// Validate user inputs
	list($invalidInput, $validatedMeetingRoomDescription, $validatedMeetingRoomName, $validatedMeetingRoomCapacity, $validatedMeetingRoomLocation) = validateUserInputs();
	
	// Refresh form on invalid
	if($invalidInput){
		
		// Save user inputs
		$_SESSION['AddMeetingRoomDescription'] = $validatedMeetingRoomDescription;
		$_SESSION['AddMeetingRoomName'] = $validatedMeetingRoomName;
		$_SESSION['AddMeetingRoomCapacity'] = $validatedMeetingRoomCapacity;
		$_SESSION['AddMeetingRoomLocation'] = $validatedMeetingRoomLocation;

		$_SESSION['refreshAddMeetingRoom'] = TRUE;
		header('Location: .');
		exit();	
	}		
	
	// Add the meeting room to the database
	try
	{		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `meetingroom` SET
							`name` = :name,
							`capacity` = :capacity,
							`description` = :description,
							`location` = :location';
		$s = $pdo->prepare($sql);
		$s->bindValue(':name', $validatedMeetingRoomName);
		$s->bindValue(':capacity', $_POST['MeetingRoomCapacity']);		
		$s->bindValue(':description', $validatedMeetingRoomDescription);
		$s->bindValue(':location', $_POST['MeetingRoomLocation']);
		$s->execute();
		
		session_start();
		unset($_SESSION['LastMeetingRoomID']);
		$_SESSION['LastMeetingRoomID'] = $pdo->lastInsertId();	
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted meeting room to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['MeetingRoomUserFeedback'] = "Successfully added the meeting room: " . $validatedMeetingRoomName;
	
		// Add a log event that a meeting room was added
	try
	{
		session_start();

		// Save a description with information about the meeting room that was added
		$logEventDescription = "The meeting room: " . $validatedMeetingRoomName . ", with capacity: " . 
		$_POST['MeetingRoomCapacity'] . " and description: " . $validatedMeetingRoomDescription . 
		" was added by: " . $_SESSION['LoggedInUserName'];
		
		if(isset($_SESSION['LastMeetingRoomID'])){
			$lastMeetingRoomID = $_SESSION['LastMeetingRoomID'];
			unset($_SESSION['LastMeetingRoomID']);
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Meeting Room Added'
											),
							`meetingRoomID` = :TheMeetingRoomID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->bindValue(':TheMeetingRoomID', $lastMeetingRoomID);
		$s->execute();
		
		//Close the connection
		$pdo = null;		
	}
	catch(PDOException $e)
	{
		$error = 'Error adding log event to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	
	
	// Load meeting room list webpage with new meeting room
	header('Location: .');
	exit();
}

// if admin wants to edit meeting room information
// we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR 
	(isset($_SESSION['refreshEditMeetingRoom']) AND $_SESSION['refreshEditMeetingRoom']))
{
	
	// TO-DO: Check if refresh or user input
	if(isset($_SESSION['refreshEditMeetingRoom']) AND $_SESSION['refreshEditMeetingRoom']){
		// Acknowledge that we have refreshed
		unset($_SESSION['refreshEditMeetingRoom']);
		
		//TO-DO: Set values to what they were before refresh
		
		
	} else {
		// Get information from database again on the selected meeting room
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT  `meetingRoomID`, 
							`name`, 
							`capacity`, 
							`description`, 
							`location`
					FROM 	`meetingroom`
					WHERE 	`meetingRoomID` = :id';
					
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['MeetingRoomID']);
			$s->execute();
			
			// Create an array with the row information we retrieved
			$row = $s->fetch();

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching meeting room details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		// Set correct information
		$meetingRoomName = $row['name'];
		$meetingRoomCapacity = $row['MeetingRoomCapacity'];
		$meetingRoomID = $row['meetingRoomID'];
		$meetingRoomDescription = $row['MeetingRoomDescription'];
		$meetingRoomLocation = $row['MeetingRoomLocation']; 		
	}
	


	
	// Set the always correct information
	$pageTitle = 'Edit User';
	$action = 'editform';
	$button = 'Edit room';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['editform']))
{
	// Validate user inputs
	list($invalidInput, $validatedMeetingRoomDescription, $validatedMeetingRoomName, $validatedMeetingRoomCapacity, $validatedMeetingRoomLocation) = validateUserInputs();
	
	// Refresh form on invalid
	if($invalidInput){
		
		// Refresh.
		$_SESSION['EditMeetingRoomDescription'] = $validatedMeetingRoomDescription;
		$_SESSION['EditMeetingRoomName'] = $validatedMeetingRoomName;
		$_SESSION['EditMeetingRoomCapacity'] = $validatedMeetingRoomCapacity;
		$_SESSION['EditMeetingRoomLocation'] = $validatedMeetingRoomLocation;
		
		$_SESSION['refreshEditMeetingRoom'] = TRUE;
		header('Location: .');
		exit();			
	}	
	
	// Update the meeeting room in the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'UPDATE `meetingroom` SET
						`name` = :name,
						capacity = :capacity,
						description = :description,
						location = :location
				WHERE 	meetingRoomID = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['MeetingRoomID']); // TO-DO: Change to a session variable if the ID isn't saved
		$s->bindValue(':name', $validatedMeetingRoomName);
		$s->bindValue(':capacity', $validatedMeetingRoomCapacity);
		$s->bindValue(':description', $validatedMeetingRoomDescription);
		$s->bindValue(':location', $validatedMeetingRoomLocation);
		$s->execute();
		
		// Close the connection
		$pdo = Null;
	}
	catch (PDOException $e)
	{
		$error = 'Error updating submitted meeting room: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['MeetingRoomUserFeedback'] = "Successfully updated the meeting room: " . $validatedMeetingRoomName;
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();
}

// If the user clicks any cancel buttons he'll be directed back to the meeting room page again
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel'){
	$_SESSION['MeetingRoomUserFeedback'] = "Cancel button clicked. Taking you back to /admin/meetingrooms/!";
	$refreshMeetingRooms = TRUE;
}


/* if ($refreshMeetingRooms) {
	TO-DO: Add code that should occur on a refresh
}*/



// Display meeting room list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = 'SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
						m.`name`			AS MeetingRoomName, 
						m.`capacity`		AS MeetingRoomCapacity, 
						m.`description`		AS MeetingRoomDescription, 
						m.`location`		AS MeetingRoomLocation,
						COUNT(re.`amount`)	AS MeetingRoomEquipmentAmount
			FROM 		`meetingroom` m
			LEFT JOIN 	`roomequipment` re
			ON 			re.`meetingRoomID` = m.`meetingRoomID`
			GROUP BY 	m.`meetingRoomID`';
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();

	//Close the connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching meeting rooms from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}


foreach ($result as $row)
{
	$meetingrooms[] = array('MeetingRoomID' => $row['TheMeetingRoomID'], 
							'MeetingRoomName' => $row['MeetingRoomName'],
							'MeetingRoomCapacity' => $row['MeetingRoomCapacity'],
							'MeetingRoomDescription' => $row['MeetingRoomDescription'],
							'MeetingRoomLocation' => $row['MeetingRoomLocation'],
							'MeetingRoomEquipmentAmount' => $row['MeetingRoomEquipmentAmount']
					);
}

// Create the Meeting Rooms table in HTML
include_once 'meetingrooms.html.php';
?>