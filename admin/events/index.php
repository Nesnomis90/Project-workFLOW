<?php
// This is the Index file for the EVENTS folder
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add booking form
function clearAddEventSessions(){
	unset($_SESSION['AddEventWeeksSelected']);
	unset($_SESSION['AddEventDaysSelected']);
	unset($_SESSION['AddEventRoomChoiceSelected']);
	unset($_SESSION['AddEventRoomsSelected']);
	unset($_SESSION['AddEventInfoArray']);
	unset($_SESSION['AddEventOriginalInfoArray']);
	unset($_SESSION['AddEventMeetingRoomsArray']);
	unset($_SESSION['AddEventDaysConfirmed']);
}

// Function to remember the user inputs in Add Event
function rememberAddEventInputs(){
	if(isset($_SESSION['AddEventInfoArray'])){
		$newValues = $_SESSION['AddEventInfoArray'];
		
		$newValues['StartTime'] =  trimExcessWhitespace($_POST['startTime']);
		$newValues['EndTime'] =  trimExcessWhitespace($_POST['endTime']);
		$newValues['StartDate'] =  trimExcessWhitespace($_POST['startDate']);
		$newValues['EndDate'] =  trimExcessWhitespace($_POST['endDate']);
		
		$newValues['EventName'] = trimExcessWhitespace($_POST['eventName']);
		$newValues['EventDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['eventDescription']);
		
		$_SESSION['AddEventDaysSelected'] = $_POST['daysSelected'];
		
		$_SESSION['AddEventInfoArray'] = $newValues;
	}
}

// If admin wants to create a new event
if(	(isset($_POST['action']) AND $_POST['action'] == "Create Event") OR
	(isset($_SESSION['refreshAddEvent']) AND $_SESSION['refreshAddEvent'])
	){
	
	if(isset($_SESSION['refreshAddEvent'])){
		// Acknowledge that we hav refreshed the page
		unset($_SESSION['refreshAddEvent']); 
	}
	
	if(!isset($_SESSION['AddEventInfoArray'])){
		// Create an array with the row information we want to use
		$_SESSION['AddEventInfoArray'] = array(
													'TheEventID' => '',
													'StartTime' => '',
													'EndTime' => '',
													'EventName' => '',
													'EventDescription' => '',
													'BookedForCompany' => '',
													'startDate' => '',
													'lastDate' => ''
												);
		$_SESSION['AddEventOriginalInfoArray'] = $_SESSION['AddEventInfoArray'];
	}
	
	if(!isset($_SESSION['AddEventMeetingRoomsArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			// Get name and IDs for meeting rooms
			$sql = 'SELECT 	`meetingRoomID`,
							`name` 
					FROM 	`meetingroom`';
			$result = $pdo->query($sql);
				
			//Close connection
			$pdo = null;
			
			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$meetingroom[] = array(
									'MeetingRoomID' => $row['meetingRoomID'],
									'MeetingRoomName' => $row['name']
									);
			}		
			
			$_SESSION['AddEventMeetingRoomsArray'] = $meetingroom;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching meeting room details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}	
	}
	
	// Array for the days of the week
	$daysOfTheWeek = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	
	// Set correct output
	$row = $_SESSION['AddEventInfoArray'];
	if(isset($row['StartTime'])){
		$startTime = $row['StartTime'];
	} else {
		$startTime = "";
	}
	if(isset($row['EndTime'])){
		$endTime = $row['EndTime'];
	} else {
		$endTime = "";
	}
	if(isset($row['EventName'])){
		$eventName = $row['EventName'];
	} else {
		$eventName = "";
	}
	if(isset($row['EventDescription'])){
		$eventDescription = $row['EventDescription'];
	} else {
		$eventDescription = "";
	}
	if(isset($_SESSION['AddEventDaysSelected'])){
		$daysSelected = $_SESSION['AddEventDaysSelected'];
	} else {
		$daysSelected = array();
	}
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	include_once 'addevent.html.php';
	exit();
}

// If admin wants to submit the created event
if(isset($_POST['add']) AND $_POST['add'] == "Create Event"){
	
	$invalidInput = TRUE; // test
	// Validate user inputs
		// TO-DO: Validate user inputs.	
	if($invalidInput){
		rememberAddEventInputs();
		$_SESSION['refreshAddEvent'] = TRUE;
		header('Location: .');
		exit();
	}
	
	// Check if the timeslot(s) is taken for the selected meeting room(s)
		// TO-DO: Get datetimes, also this requires a lot more work
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql =	" 	SELECT 	COUNT(*)	AS HitCount
					FROM 	(
								SELECT 		1
								FROM 		`booking` b
								LEFT JOIN	`roomevent` rev
								ON 			rev.`MeetingRoomID` = b.`meetingRoomID`
								WHERE 		b.`meetingRoomID` = :MeetingRoomID
								AND			b.`dateTimeCancelled` IS NULL
								AND			b.`actualEndDateTime` IS NULL
								AND		
								(		
										(
											b.`startDateTime` >= :StartTime AND 
											b.`startDateTime` < :EndTime
										) 
								OR 		(
											b.`endDateTime` > :StartTime AND 
											b.`endDateTime` <= :EndTime
										)
								OR 		(
											:EndTime > b.`startDateTime` AND 
											:EndTime < b.`endDateTime`
										)
								OR 		(
											:StartTime > b.`startDateTime` AND 
											:StartTime < b.`endDateTime`
										)
								)
								LIMIT 1
							) AS BookingsFound";
		$s = $pdo->prepare($sql);
		
		$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':StartTime', $startDateTime);
		$s->bindValue(':EndTime', $endDateTime);
		$s->execute();
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error checking if event time is available: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Check if we got any hits, if so the timeslot is already taken
	$row = $s->fetch(PDO::FETCH_ASSOC);		
	if ($row['HitCount'] > 0){

		// Timeslot was taken
		rememberAddEventInputs();
		// TO-DO: add information on what was taken and which meeting room.
		$_SESSION['AddEventError'] = "The event couldn't be made. The timeslot is already taken for this meeting room.";
		$_SESSION['refreshAddEvent'] = TRUE;	
		header('Location: .');
		exit();				
	}
	
	// TO-DO: Add Event to database
	// TO-DO: Create log event?
	
	header("Location: .");
	exit();
}

if(isset($_POST['add']) AND $_POST['add'] == "Confirm Day(s)"){
	
	$_SESSION['AddEventDaysConfirmed'] = TRUE;
	// TO-DO: disable checkboxes for days
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Change Day(s)"){
	
	unset($_SESSION['AddEventDaysConfirmed']);
	// TO-DO: disable checkboxes for days
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}




// If admin wants to leave the page and be directed back to the events page again
if (isset($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['EventsUserFeedback'] = "You cancelled your new event.";
}

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddEventSessions();
//clearEditEventSessions();


// EVENTS OVERVIEW CODE SNIPPET START //

if(isset($refreshEvents) AND $refreshEvents) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshEvents);
}

// Get Event Data
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	// Use connect to Database function from db.inc.php
	$pdo = connect_to_db();
	$sql = 'SELECT	`EventID`			AS TheEventID,
					`startTime`			AS StartTime,
					`endTime`			AS EndTime,
					`name`				AS EventName,
					`description`		AS EventDescription,
					`dateTimeCreated`	AS DateTimeCreated,
					`startDate`			AS StartDate,
					`lastDate`			AS LastDate,
					WEEK(`startDate`,3)	AS WeekStart,
					WEEK(`lastDate`,3)	AS WeekEnd,
					`daysSelected`		AS DaysSelected,
					(
						SELECT 		GROUP_CONCAT(m.`name` separator ",\n")
						FROM		`roomevent` rev
						INNER JOIN 	`meetingroom` m
						ON			rev.`meetingRoomID` = m.`meetingRoomID`
						WHERE		rev.`EventID` = TheEventID
					)					AS UsedMeetingRooms
			FROM 	`event`';
	$return = $pdo->query($sql);
	$result = $return->fetchAll(PDO::FETCH_ASSOC);
	if(isset($result)){
		$rowNum = sizeOf($result);
	} else {
		$rowNum = 0;
	}
	
	//Close connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching events: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php'; 
	$pdo = null;
	exit();
}

// Create the array we will go through to display information in HTML
foreach ($result as $row)
{
	// Check if event is over or still active
	$startDate = $row['StartDate'];
	$lastDate = $row['LastDate'];
	$dateNow = getDateNow();
	$timeNow = getTimeNow();
	$startTime = $row['StartTime'];
	$endTime = $row['EndTime'];
	$weekStart = $row['WeekStart'];
	$weekEnd = $row['WeekEnd'];
	
	if($weekStart == $weekEnd){
		// single event
		if($dateNow > $lastDate AND $timeNow > $endTime){
			$status = "Completed\n(Single Event)";
		} else {
			$status = "Active\n(Single Event)";
		}
	} elseif($weekEnd > $weekStart) {
		// repeated event
		if($dateNow > $lastDate AND $timeNow > $endTime){
			$status = "Completed\n(Repeated Event)";
		} else {
			$status = "Active\n(Repeated Event)";
		}		
	}
	
	// Turn the datetime retrieved into a more displayable format
	$dateCreated = $row['DateTimeCreated'];
	$displayableDateCreated = convertDatetimeToFormat($dateCreated, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayableStartDate = convertDatetimeToFormat($startDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$startDateWithWeekNumber = $displayableStartDate . "\nWeek #" . $weekStart;
	$displayableEndDate = convertDatetimeToFormat($lastDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$endDateWithWeekNumber = $displayableEndDate . "\nWeek #" . $weekEnd;
	$displayableStartTime = convertDatetimeToFormat($startTime, 'H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayableEndTime = convertDatetimeToFormat($endTime, 'H:i:s', TIME_DEFAULT_FORMAT_TO_DISPLAY);

	if(substr($status,0,6) == "Active"){
		$activeEvents[] = array(
							'EventStatus' => $status,
							'EventID' => $row['TheEventID'], 
							'DateTimeCreated' => $displayableDateCreated, 
							'EventName' => $row['EventName'], 
							'EventDescription' => $row['EventDescription'], 
							'UsedMeetingRooms' => $row['UsedMeetingRooms'],
							'DaysSelected' => $row['DaysSelected'],
							'StartTime' => $displayableStartTime,
							'EndTime' => $displayableEndTime,
							'StartDate' => $startDateWithWeekNumber,
							'LastDate' => $endDateWithWeekNumber
						);
	} else {
		$completedEvents[] = array(
							'EventStatus' => $status,
							'EventID' => $row['TheEventID'], 
							'DateTimeCreated' => $displayableDateCreated, 
							'EventName' => $row['EventName'], 
							'EventDescription' => $row['EventDescription'], 
							'UsedMeetingRooms' => $row['UsedMeetingRooms'],
							'DaysSelected' => $row['DaysSelected'],
							'StartTime' => $displayableStartTime,
							'EndTime' => $displayableEndTime,
							'StartDate' => $startDateWithWeekNumber,
							'LastDate' => $endDateWithWeekNumber
						);		
	}
}	

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Events table in HTML
include_once 'events.html.php';
?>