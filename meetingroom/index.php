<?php 
// This is the index file for the meeting room folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

/*
	TO-DO:
		Show meeting room status (booked or not?)
		Search meeting room status by datetime?

*/

// Redirect to booking when a room has been selected
if(isset($_POST['action']) AND $_POST['action'] == "Select Room"){

	$TheMeetingRoomID = $_POST['MeetingRoomID'];
	$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;
	header("Location: $location");
	exit();
}

if(isset($_POST['action']) AND $_POST['action'] == "Set New Max"){
	
	// Validate user input
	$roomDisplayLimitString = trimExcessWhiteSpace($_POST['logsToShow']);
	$isNumber = validateIntegerNumber($roomDisplayLimitString);
	if($isNumber === TRUE){
		$maxRoomsToShow = $roomDisplayLimitString;
		$roomDisplayLimit = $roomDisplayLimitString;
		if($roomDisplayLimitString != $_POST['oldDisplayLimit']){
			$_SESSION['MeetingRoomAllUsersFeedback'] = "Set new maximum rooms to display to: $maxRoomsToShow.";				
		} else {
			$_SESSION['MeetingRoomAllUsersFeedback'] = "No change were made.";
		}
	} else {
		$_SESSION['MeetingRoomAllUsersFeedback'] = "You tried to submit something that wasn't a valid number.";	 
	}
}

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
						COUNT(re.`amount`)	AS MeetingRoomEquipmentAmount
			FROM 		`meetingroom` m
			LEFT JOIN 	`roomequipment` re
			ON 			re.`meetingRoomID` = m.`meetingRoomID`
			GROUP BY 	m.`meetingRoomID`';
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

$totalMeetingRooms = sizeOf($meetingrooms);

// Sets default values
if(!isset($maxRoomsToShow)){
	if($totalMeetingRooms < 10){
		$maxRoomsToShow = $totalMeetingRooms;
	} else {
		$maxRoomsToShow = 10;	
	}
}
if(!isset($roomDisplayLimit)){
	$roomDisplayLimit = $maxRoomsToShow;
}

// Load the html template
include_once 'meetingroomforallusers.html.php';
?>