<?php
// This is the index file for the MEETING ROOMS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
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
		$s->bindValue(':id', $_POST['id']);
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
		$description = "The meeting room: " . $_POST['MeetingRoomName'] . " was removed by: " . $_SESSION['LoggedInUserName'];
		
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
		$s->bindValue(':description', $description);
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
if (isset($_GET['add']))
{
	// Set form variables to be ready for adding values
	$pageTitle = 'New Meeting Room';
	$action = 'addform';
	$name = '';
	$capacity = '';
	$description = '';
	$location = '';
	$id = '';
	$button = 'Add room';
	
	if(isset($_SESSION['AddMeetingRoomCapacity'])){
		$capacity = $_SESSION['AddMeetingRoomCapacity'];
		unset($_SESSION['AddMeetingRoomCapacity']);
	}
	if(isset($_SESSION['AddMeetingRoomDescription'])){
		$description = $_SESSION['AddMeetingRoomDescription'];
		unset($_SESSION['AddMeetingRoomDescription']);
	}	
	if(isset($_SESSION['AddMeetingRoomLocation'])){
		$location = $_SESSION['AddMeetingRoomLocation'];
		unset($_SESSION['AddMeetingRoomLocation']);
	}		
	
	// We want a reset all fields button while adding a new meeting room
	$reset = 'reset';
	
	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the meeting room
if (isset($_GET['addform']))
{
	// Check if the meeting room already exists (based on name).
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`meetingroom`
				WHERE 	`name`= :MeetingRoomName';
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomName', $_POST['name']);		
		$s->execute();
		
		$pdo = null;
		
		$row = $s->fetch();
		
		if ($row[0] > 0)
		{
			// This name is already being used for a meeting room
			
			session_start();
			
			$_SESSION['AddMeetingRoomCapacity'] = $_POST['capacity'];
			$_SESSION['AddMeetingRoomDescription'] = $_POST['description'];
			$_SESSION['AddMeetingRoomLocation'] = $_POST['location'];
			$_SESSION['AddMeetingRoomError'] = "The name: " . $_POST['name'] . " is already used for a meeting room!";
			
			// Refresh meeting rooms add form
			$location = "http://$_SERVER[HTTP_HOST]/admin/meetingrooms/?add";
			header("Location: $location");
			exit();		
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
		$s->bindValue(':name', $_POST['name']);
		$s->bindValue(':capacity', $_POST['capacity']);		
		$s->bindValue(':description', $_POST['description']);
		$s->bindValue(':location', $_POST['location']);
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
	
	$_SESSION['MeetingRoomUserFeedback'] = "Successfully added the meeting room: " . $_POST['name'];
	
		// Add a log event that a meeting room was added
	try
	{
		session_start();

		// Save a description with information about the meeting room that was added
		$description = "The meeting room: " . $_POST['name'] . ", with capacity: " . 
		$_POST['capacity'] . " and description: " . $_POST['description'] . 
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
		$s->bindValue(':description', $description);
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
if (isset($_POST['action']) AND $_POST['action'] == 'Edit')
{
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
		$s->bindValue(':id', $_POST['id']);
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
	
	// Set the correct information
	$pageTitle = 'Edit User';
	$action = 'editform';
	$name = $row['name'];
	$capacity = $row['capacity'];
	$id = $row['meetingRoomID'];
	$description = $row['description'];
	$location = $row['location'];
	$button = 'Edit room';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['editform']))
{
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
		$s->bindValue(':id', $_POST['id']);
		$s->bindValue(':name', $_POST['name']);
		$s->bindValue(':capacity', $_POST['capacity']);
		$s->bindValue(':description', $_POST['description']);
		$s->bindValue(':location', $_POST['location']);
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
	
	$_SESSION['MeetingRoomUserFeedback'] = "Successfully updated the meeting room: " . $_POST['name'];
	
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
	$meetingrooms[] = array('id' => $row['TheMeetingRoomID'], 
							'name' => $row['MeetingRoomName'],
							'capacity' => $row['MeetingRoomCapacity'],
							'description' => $row['MeetingRoomDescription'],
							'location' => $row['MeetingRoomLocation'],
							'MeetingRoomEquipmentAmount' => $row['MeetingRoomEquipmentAmount']
					);
}

// Create the Meeting Rooms table in HTML
include_once 'meetingrooms.html.php';
?>