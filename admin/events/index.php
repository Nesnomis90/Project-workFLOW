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
	unset($_SESSION['AddEventMeetingRoomsArray']);
	unset($_SESSION['AddEventDaysConfirmed']);
	unset($_SESSION['AddEventDetailsConfirmed']);
	unset($_SESSION['AddEventWeekChoiceSelected']);
	unset($_SESSION['AddEventRoomSelectedButNotConfirmed']);
	unset($_SESSION['AddEventWeekSelectedButNotConfirmed']);
} 

// Function to remember the user inputs in Add Event
function rememberAddEventInputs(){
	if(isset($_SESSION['AddEventInfoArray'])){
		$newValues = $_SESSION['AddEventInfoArray'];
		
		$newValues['StartTime'] =  trimExcessWhitespace($_POST['startTime']);
		$newValues['EndTime'] =  trimExcessWhitespace($_POST['endTime']);
		/*
		$newValues['StartDate'] =  trimExcessWhitespace($_POST['startDate']);
		$newValues['EndDate'] =  trimExcessWhitespace($_POST['endDate']); 
		
		Not implemented 
		*/
		
		$newValues['EventName'] = trimExcessWhitespace($_POST['eventName']);
		$newValues['EventDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['eventDescription']);
		
		if(isset($_POST['daysSelected'])){
			$_SESSION['AddEventDaysSelected'] = $_POST['daysSelected'];
		}
		if(isset($_POST['roomsSelected'])){
			$_SESSION['AddEventRoomsSelected'] = $_POST['roomsSelected'];
		}
		if(isset($_POST['meetingRoomID'])){
			$_SESSION['AddEventRoomSelectedButNotConfirmed'] = $_POST['meetingRoomID'];
		}		
		if(isset($_POST['weeksSelected'])){
			$_SESSION['AddEventWeeksSelected'] = $_POST['weeksSelected'];
		}
		if(isset($_POST['weekNumber'])){
			$_SESSION['AddEventWeekSelectedButNotConfirmed'] = $_POST['weekNumber'];
		}

		$_SESSION['AddEventInfoArray'] = $newValues;
	}
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse, $editing){
	// Get user inputs
	$invalidInput = FALSE;
	
	if(isset($_POST['startTime']) AND !$invalidInput){
		$startTimeString = $_POST['startTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting a start time.";
	}
	if(isset($_POST['endTime']) AND !$invalidInput){
		$endTimeString = $_POST['endTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting an end time.";
	}
	if(isset($_POST['eventName']) AND !$invalidInput){
		$eventNameString = $_POST['eventName'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting a name.";
	}
	if(isset($_POST['eventDescription'])){ // Description can be null
		$eventDescriptionString = $_POST['eventDescription'];
	} else {
		$eventDescriptionString = "";
	}
	
	// Remove excess whitespace and prepare strings for validation
	$validatedStartTime = trimExcessWhitespace($startTimeString);
	$validatedEndTime = trimExcessWhitespace($endTimeString);
	$validatedEventName = trimExcessWhitespaceButLeaveLinefeed($eventNameString);
	$validatedEventDescription = trimExcessWhitespaceButLeaveLinefeed($eventDescriptionString);
	
	// Do actual input validation
	if(validateDateTimeString($validatedStartTime) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted start time has illegal characters in it.";
	}
	if(validateDateTimeString($validatedEndTime) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted end time has illegal characters in it.";
	}
	if(validateString($validatedEventName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted event name has illegal characters in it.";
	}
	if(validateString($validatedEventDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted event description has illegal characters in it.";
	}
	
	// Are values actually filled in?
	if($validatedStartTime == "" AND $validatedEndTime == "" AND !$invalidInput){
		
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start and an end time for your event.";	
		$invalidInput = TRUE;
	} elseif($validatedStartTime != "" AND $validatedEndTime == "" AND !$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in an end time for your event.";	
		$invalidInput = TRUE;		
	} elseif($validatedStartTime == "" AND $validatedEndTime != "" AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start time for your event.";	
		$invalidInput = TRUE;		
	}
	if($validatedEventName == "" AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a name for your event.";	
		$invalidInput = TRUE;
	}
	/*	Not implemented
	if($validatedEventDescription == "" AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a description for your event.";	
		$invalidInput = TRUE;
	}
	*/
	
	// Check if input length is allowed
		// EventName
	$invalidEventName = isLengthInvalidDisplayName($validatedEventName);
	if($invalidEventName AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The event name submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// EventDescription
	$invalidEventDescription = isLengthInvalidBookingDescription($validatedEventDescription);
	if($invalidEventDescription AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The event description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	
	// Check if the time inputs we received are actually time
	$startTime = correctTimeFormat($validatedStartTime);
	$endTime = correctTimeFormat($validatedEndTime);
	
	if (isset($startTime) AND $startTime === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The start time you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}
	if (isset($endTime) AND $endTime === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The end time you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}
	
	if($startTime > $endTime AND !$invalidInput){
		// End time can't be before the start time
		$_SESSION[$FeedbackSessionToUse] = "The start time can't be later than the end time. Please select a new start time or end time.";
		$invalidInput = TRUE;
	}
	if($endTime == $startTime AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to select an end time that is different from your start time.";	
		$invalidInput = TRUE;				
	}
	
	return array($invalidInput, $startTime, $endTime, $validatedEventName, $validatedEventDescription);
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
	
	// Array for the remaining weeks this year
		// TO-DO: test this and fix template for week part
	$dateNow = getDateNow();
	$lastDate = '2017-12-28';
	$weeksOfTheYear = getWeekInfoBetweenTwoDateTimes($dateNow, $lastDate); //Returns week number, start date and end date
	
	
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
	if(isset($_SESSION['AddEventWeeksSelected'])){
		$weeksSelected = $_SESSION['AddEventWeeksSelected'];
	} else {
		$weeksSelected = array();
	}	
	if(isset($_SESSION['AddEventRoomsSelected'])){
		$roomsSelected = $_SESSION['AddEventRoomsSelected'];
	} else {
		$roomsSelected = array();
	}	
	
	if(isset($_SESSION['AddEventMeetingRoomsArray'])){
		$meetingroom = $_SESSION['AddEventMeetingRoomsArray'];
	} else {
		$meetingroom = array();
	}
	
	if(isset($_SESSION['AddEventRoomSelectedButNotConfirmed'])){
		$selectedMeetingRoomID = $_SESSION['AddEventRoomSelectedButNotConfirmed'];
	} else {
		$selectedMeetingRoomID = "";
	}
	
	if(isset($_SESSION['AddEventWeekSelectedButNotConfirmed'])){
		$selectedWeekNumber = $_SESSION['AddEventWeekSelectedButNotConfirmed'];
	} else {
		$selectedWeekNumber = "";
	}
	
	// Give admin feedback on the roomname (if one) or the amount of rooms selected.
	if(isset($_SESSION['AddEventRoomsSelected'])){
		if($_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room"){
			foreach($meetingroom AS $room){
				if($room['MeetingRoomID'] == $_SESSION['AddEventRoomsSelected']){
					$roomSelected = $room['MeetingRoomName'];
					break;
				}
			}
		} elseif($_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms"){
			$numberOfRoomsSelected = sizeOf($_SESSION['AddEventRoomsSelected']);
		} elseif($_SESSION['AddEventRoomChoiceSelected'] == "Select All Rooms"){
			$numberOfRoomsSelected = sizeOf($meetingroom);
		}
	}
	
	// Give admin feedback on the week info (if one) or the amount of weeks selected.
	if(isset($_SESSION['AddEventWeeksSelected'])){
		if($_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week"){
			foreach($weeksOfTheYear AS $week){
				if($week['WeekNumber'] == $_SESSION['AddEventWeeksSelected']){
					$weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR);
					$weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR);
					$weekSelected = $week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd;
					break;
				}
			}
		} elseif($_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks"){
			$numberOfWeeksSelected = sizeOf($_SESSION['AddEventWeeksSelected']);
		} elseif($_SESSION['AddEventWeekChoiceSelected'] == "Select All Weeks"){
			$numberOfWeeksSelected = sizeOf($weeksOfTheYear);
		}
	}	
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	include_once 'addevent.html.php';
	exit();
}

// If admin wants to submit the created event
if(isset($_POST['add']) AND $_POST['add'] == "Create Event"){
	
	list($invalidInput, $startTime, $endTime, $eventName, $eventDescription) = validateUserInputs('AddEventError', FALSE);
	// Validate user inputs
		// TO-DO: Validate user inputs.	
	if($invalidInput){
		rememberAddEventInputs();
		$_SESSION['refreshAddEvent'] = TRUE;
		header('Location: .');
		exit();
	}
	echo "<br />";
	echo $startTime;
	echo "<br />";
	echo $endTime;
	echo "<br />";
	echo $eventName;
	echo "<br />";
	echo $eventDescription;
	echo "<br />";
	
	// Remove after done testing.
	echo "no invalidInputs";
	exit();
	// Remove after done testing 
	
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

// If admin wants to decide the amount of weeks to select
	// A single week (dropdown list)
if(isset($_POST['add']) AND $_POST['add'] == "Select A Single Week"){
	
	$_SESSION['AddEventWeekChoiceSelected'] = "Select A Single Week";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// Multiple meeting rooms (checkboxes)
if(isset($_POST['add']) AND $_POST['add'] == "Select Multiple Weeks"){
	
	$_SESSION['AddEventWeekChoiceSelected'] = "Select Multiple Weeks";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// All meeting rooms
if(isset($_POST['add']) AND $_POST['add'] == "Select All Weeks"){
	
	$_SESSION['AddEventWeeksSelected'] = TRUE;
	$_SESSION['AddEventWeekChoiceSelected'] = "Select All Weeks";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Confirm Week(s)"){
	
	rememberAddEventInputs();

	if(isset($_POST['weeksSelected'])){
		if(sizeOf($_POST['weeksSelected']) > 0){
			$_SESSION['AddEventWeeksSelected'] = $_POST['weeksSelected'];
		} else {
			$_SESSION['AddEventError'] = "You need to select at least one week.";
		}
	}

	if(isset($_POST['weekNumber'])){
		$_SESSION['AddEventWeeksSelected'] = $_POST['weekNumber'];
	}
	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the week(s) selected decision
if(isset($_POST['add']) AND $_POST['add'] == "Change Week Selection"){
	
	unset($_SESSION['AddEventWeeksSelected']);
	unset($_SESSION['AddEventWeekChoiceSelected']);
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Confirm Day(s)"){
	
	rememberAddEventInputs();
	
	if(isset($_POST['daysSelected']) AND sizeOf($_POST['daysSelected']) > 0){
		$_SESSION['AddEventDaysConfirmed'] = TRUE;
	} else {
		$_SESSION['AddEventError'] = "You need to select at least one day.";
	}
	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Change Day(s)"){
	
	unset($_SESSION['AddEventDaysConfirmed']);
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Confirm Details"){
	
	list($invalidInput, $startTime, $endTime, $eventName, $eventDescription) = validateUserInputs('AddEventError', FALSE);
	
	if(!$invalidInput){
		$_SESSION['AddEventDetailsConfirmed'] = TRUE;
	}
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == "Change Details"){
	
	unset($_SESSION['AddEventDetailsConfirmed']);
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}


// If admin wants to decide the amount of meeting rooms to select
	// A single meeting room (dropdown list)
if(isset($_POST['add']) AND $_POST['add'] == "Select A Single Room"){
	
	$_SESSION['AddEventRoomChoiceSelected'] = "Select A Single Room";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// Multiple meeting rooms (checkboxes)
if(isset($_POST['add']) AND $_POST['add'] == "Select Multiple Rooms"){
	
	$_SESSION['AddEventRoomChoiceSelected'] = "Select Multiple Rooms";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// All meeting rooms
if(isset($_POST['add']) AND $_POST['add'] == "Select All Rooms"){
	
	$_SESSION['AddEventRoomsSelected'] = TRUE;
	$_SESSION['AddEventRoomChoiceSelected'] = "Select All Rooms";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// To confirm room selection (a room/multiple rooms)
if(isset($_POST['add']) AND $_POST['add'] == "Confirm Room(s)"){
	
	if(isset($_POST['meetingroom'])){
		if(sizeOf($_POST['meetingroom']) > 0){
			$_SESSION['AddEventRoomsSelected'] = $_POST['meetingroom'];
		} else {
			$_SESSION['AddEventError'] = "You need to select at least one meeting room.";
		}
	}
	if(isset($_POST['meetingRoomID'])){
		$_SESSION['AddEventRoomsSelected'] = $_POST['meetingRoomID'];
	}
	
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the meeting room(s) selected decision
if(isset($_POST['add']) AND $_POST['add'] == "Change Room Selection"){
	
	unset($_SESSION['AddEventRoomsSelected']);
	unset($_SESSION['AddEventRoomChoiceSelected']);
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['add']) AND $_POST['add'] == 'Reset'){
	clearAddEventSessions();
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