<?php 
// This is the index file for the meeting room folder (all users)

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdmin();
unsetSessionsFromCompanyManagement();
unsetSessionsFromUserManagement();
unsetSessionsFromBookingManagement();

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	userIsLoggedIn();
}

/*
	TO-DO:
		Search meeting room status by datetime?
*/

// ADMIN INTERACTIONS // START //

if(isSet($_GET['cancelSetDefaultRoom'])){
	unset($_SESSION['SetDefaultRoom']);
	header("Location: .");
	exit();
}

// If Admin wants to set a meeting room as the default room on a local device
if(	(isSet($_POST['action']) AND $_POST['action'] == "Set Default Room") OR 
	(isSet($_POST['action']) AND $_POST['action'] == "Change Default Room") OR
	(isSet($_SESSION['SetDefaultRoom']) AND $_SESSION['SetDefaultRoom'])){
		
	$_SESSION['SetDefaultRoom'] = TRUE;
		// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
	if (!isUserAdmin()){
		exit();
	}
	unset($_SESSION['SetDefaultRoom']);
	// User logged in as Admin and can set the default meeting room on this local device
	// Display meeting room list to choose from.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT  	`meetingRoomID`	AS TheMeetingRoomID, 
							`name`			AS MeetingRoomName, 
							`capacity`		AS MeetingRoomCapacity, 
							`description`	AS MeetingRoomDescription,
							`idCode`		AS MeetingRoomIDCode
				FROM 		`meetingroom`';
		$return = $pdo->query($sql);
		$result = $return->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
		
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

	foreach ($result as $row){
		$meetingrooms[] = array('MeetingRoomID' => $row['TheMeetingRoomID'], 
								'MeetingRoomName' => $row['MeetingRoomName'],
								'MeetingRoomCapacity' => $row['MeetingRoomCapacity'],
								'MeetingRoomDescription' => $row['MeetingRoomDescription'],
								'MeetingRoomIDCode' => $row['MeetingRoomIDCode']
								);
	}
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	include_once 'adminroomselect.html.php';
	exit();
}

// If Admin has chosen a default meeting room from the available meeting rooms
if(isSet($_POST['action']) AND $_POST['action'] == "Set As Default"){
		// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
	if (!isUserAdmin()){
		exit();
	}
	
		// Set the proper cookies for the meeting room and logout the Admin.
	if(isSet($_POST['MeetingRoomName']) AND isSet($_POST['MeetingRoomIDCode'])){
		$meetingRoomName = $_POST['MeetingRoomName'];
		
		setNewMeetingRoomCookies($meetingRoomName, $_POST['MeetingRoomIDCode']);
		destroySession();
		$defaultMeetingRoomFeedback = "Set $meetingRoomName as the default meeting room for this device. Also logged you off as Admin.";
		header("Location: .");
		exit();
	} else {
		$_SESSION['MeetingRoomAllUsersFeedback'] = "Couldn't set default meeting room for local device.";
	}
}

// Update meeting room info for local device
checkIfLocalDevice();

// ADMIN INTERACTIONS // END //


// NON-ADMIN INTERACTIONS // START //

// Updates/Sets the default page when user wants it
if(isSet($_POST['action']) AND $_POST['action'] == "Show Default Room Only"){

	$TheMeetingRoomID = $_SESSION["DefaultMeetingRoomInfo"]["TheMeetingRoomID"];
	$location = "http://$_SERVER[HTTP_HOST]/meetingroom/?meetingroom=" . $TheMeetingRoomID;
	header("Location: $location");
	exit();
}

// Shows all meeting rooms again when already looking at single room
if(isSet($_POST['action']) AND $_POST['action'] == "Show All Rooms"){

	$location = "http://$_SERVER[HTTP_HOST]/meetingroom/";
	header("Location: $location");
	exit();
}

// If user wants to refresh the page to get the most up-to-date information
if (isSet($_POST['action']) and $_POST['action'] == 'Refresh'){
	
	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/meetingroom/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// Redirect to booking when a room has been selected
if(isSet($_POST['action']) AND $_POST['action'] == "Booking Information"){

	$TheMeetingRoomID = $_POST['MeetingRoomID'];
	$name = $_POST['MeetingRoomName'];
	$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID . "&name=" . $name;
	header("Location: $location");
	exit();
}

if(isSet($_GET['meetingroom']) AND !empty($_GET['meetingroom'])){
	// Display selected meeting room
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
							m.`name`			AS MeetingRoomName, 
							m.`capacity`		AS MeetingRoomCapacity, 
							m.`description`		AS MeetingRoomDescription, 
							m.`location`		AS MeetingRoomLocation,
							(
								SELECT 	COUNT(*)
								FROM 	`roomequipment` re
								WHERE 	re.`MeetingRoomID` = TheMeetingRoomID
							)					AS MeetingRoomEquipmentAmount,
							(
								SELECT	b.`endDateTime`
								FROM	`booking` b
								WHERE	b.`meetingRoomID` = TheMeetingRoomID
								AND		b.`actualEndDateTime` IS NULL
								AND		b.`dateTimeCancelled` IS NULL
								AND		CURRENT_TIMESTAMP 
								BETWEEN	b.`startDateTime` 
								AND 	b.`endDateTime`
							)					AS CurrentMeetingEnd,
							(
								SELECT		b.`startDateTime`
								FROM		`booking` b
								WHERE		b.`meetingRoomID` = TheMeetingRoomID
								AND			b.`actualEndDateTime` IS NULL
								AND			b.`dateTimeCancelled` IS NULL
								AND			b.`startDateTime` > CURRENT_TIMESTAMP
								AND			CURRENT_DATE = DATE(b.`startDateTime`)
								ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
								ASC
								LIMIT 1
							)					AS NextMeetingStart
				FROM 		`meetingroom` m
				WHERE		m.`meetingRoomID` = :meetingRoomID
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':meetingRoomID', $_GET['meetingroom']);
		$s->execute();
		$result = $s->fetchAll();

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
} else {
	// Display meeting rooms
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
							m.`name`			AS MeetingRoomName, 
							m.`capacity`		AS MeetingRoomCapacity, 
							m.`description`		AS MeetingRoomDescription, 
							m.`location`		AS MeetingRoomLocation,
							(
								SELECT 	COUNT(*)
								FROM 	`roomequipment` re
								WHERE 	re.`MeetingRoomID` = TheMeetingRoomID
							)					AS MeetingRoomEquipmentAmount,
							(
								SELECT	b.`endDateTime`
								FROM	`booking` b
								WHERE	b.`meetingRoomID` = TheMeetingRoomID
								AND		b.`actualEndDateTime` IS NULL
								AND		b.`dateTimeCancelled` IS NULL
								AND		CURRENT_TIMESTAMP 
								BETWEEN	b.`startDateTime` 
								AND 	b.`endDateTime`
							)					AS CurrentMeetingEnd,
							(
								SELECT		b.`startDateTime`
								FROM		`booking` b
								WHERE		b.`meetingRoomID` = TheMeetingRoomID
								AND			b.`actualEndDateTime` IS NULL
								AND			b.`dateTimeCancelled` IS NULL
								AND			b.`startDateTime` > CURRENT_TIMESTAMP
								AND			CURRENT_DATE = DATE(b.`startDateTime`)
								ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
								ASC
								LIMIT 1
							)					AS NextMeetingStart 
				FROM 		`meetingroom` m';
		$result = $pdo->query($sql);

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
}

foreach($result as $row){

	if($row['CurrentMeetingEnd'] != NULL AND $row['NextMeetingStart'] == NULL){
		$currentMeetingEnd = convertDatetimeToFormat($row['CurrentMeetingEnd'], 'Y-m-d H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);
		$status = "Occupied until " . $currentMeetingEnd . " and then available all day";
	} elseif($row['CurrentMeetingEnd'] != NULL AND $row['NextMeetingStart'] != NULL){
		$currentMeetingEnd = convertDatetimeToFormat($row['CurrentMeetingEnd'], 'Y-m-d H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);
		$nextMeetingStart = convertDatetimeToFormat($row['NextMeetingStart'], 'Y-m-d H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);
		$status = "Occupied until " . $currentMeetingEnd . " and again starting at " . $nextMeetingStart;
	} elseif($row['NextMeetingStart'] != NULL){
		$nextMeetingStart = convertDatetimeToFormat($row['NextMeetingStart'], 'Y-m-d H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);
		$status = "Available until " . $nextMeetingStart;
	} else {
		$status = "Available all day";
	}

	$meetingrooms[] = array('MeetingRoomID' => $row['TheMeetingRoomID'], 
							'MeetingRoomName' => $row['MeetingRoomName'],
							'MeetingRoomCapacity' => $row['MeetingRoomCapacity'],
							'MeetingRoomDescription' => $row['MeetingRoomDescription'],
							'MeetingRoomLocation' => $row['MeetingRoomLocation'],
							'MeetingRoomEquipmentAmount' => $row['MeetingRoomEquipmentAmount'],
							'MeetingRoomStatus' => $status
					);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Load the html template
include_once 'meetingroomforallusers.html.php';
?>