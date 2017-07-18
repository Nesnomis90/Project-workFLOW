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
	if(isSet($_SESSION['AddEventInfoArray'])){
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
		
		if(isSet($_POST['daysSelected'])){
			$_SESSION['AddEventDaysSelected'] = $_POST['daysSelected'];
		}
		if(isSet($_POST['roomsSelected'])){
			$_SESSION['AddEventRoomsSelected'] = $_POST['roomsSelected'];
		}
		if(isSet($_POST['meetingRoomID'])){
			$_SESSION['AddEventRoomSelectedButNotConfirmed'] = $_POST['meetingRoomID'];
		}		
		if(isSet($_POST['weeksSelected'])){
			$_SESSION['AddEventWeeksSelected'] = $_POST['weeksSelected'];
		}
		if(isSet($_POST['weekNumber'])){
			$_SESSION['AddEventWeekSelectedButNotConfirmed'] = $_POST['weekNumber'];
		}

		$_SESSION['AddEventInfoArray'] = $newValues;
	}
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse, $editing){
	// Get user inputs
	$invalidInput = FALSE;
	
	if(isSet($_POST['startTime']) AND !$invalidInput){
		$startTimeString = $_POST['startTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting a start time.";
	}
	if(isSet($_POST['endTime']) AND !$invalidInput){
		$endTimeString = $_POST['endTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting an end time.";
	}
	if(isSet($_POST['eventName']) AND !$invalidInput){
		$eventNameString = $_POST['eventName'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "An event cannot be created without submitting a name.";
	}
	if(isSet($_POST['eventDescription'])){ // Description can be null
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
	
	if (isSet($startTime) AND $startTime === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The start time you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}
	if (isSet($endTime) AND $endTime === FALSE AND !$invalidInput){
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

// If admin wants to be able to delete events it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['eventsEnableDelete'] = TRUE;
	$refreshEvents = TRUE;
}

// If admin wants to be disable event deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['eventsEnableDelete']);
	$refreshEvents = TRUE;
}

// If admin wants to delete the event (and all the linked schedules in roomevent)
if(isSet($_POST['action']) AND $_POST['action'] == "Delete"){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();

		$sql = 'DELETE FROM `event`
				WHERE 		`eventID` = :EventID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EventID', $_POST['EventID']);
		$s->execute();
			
		//Close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error removing event: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}	
}

// If admin wants to create a new event
if(	(isSet($_POST['action']) AND $_POST['action'] == "Create Event") OR
	(isSet($_SESSION['refreshAddEvent']) AND $_SESSION['refreshAddEvent'])
	){
	
	if(isSet($_SESSION['refreshAddEvent'])){
		// Acknowledge that we hav refreshed the page
		unset($_SESSION['refreshAddEvent']); 
	}
	
	if(!isSet($_SESSION['AddEventInfoArray'])){
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
	
	if(!isSet($_SESSION['AddEventMeetingRoomsArray'])){
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
	if(isSet($row['StartTime'])){
		$startTime = $row['StartTime'];
	} else {
		$startTime = "";
	}
	if(isSet($row['EndTime'])){
		$endTime = $row['EndTime'];
	} else {
		$endTime = "";
	}
	if(isSet($row['EventName'])){
		$eventName = $row['EventName'];
	} else {
		$eventName = "";
	}
	if(isSet($row['EventDescription'])){
		$eventDescription = $row['EventDescription'];
	} else {
		$eventDescription = "";
	}
	if(isSet($_SESSION['AddEventDaysSelected'])){
		$daysSelected = $_SESSION['AddEventDaysSelected'];
	} else {
		$daysSelected = array();
	}
	if(isSet($_SESSION['AddEventWeeksSelected'])){
		$weeksSelected = $_SESSION['AddEventWeeksSelected'];
	} else {
		$weeksSelected = array();
	}	
	if(isSet($_SESSION['AddEventRoomsSelected'])){
		$roomsSelected = $_SESSION['AddEventRoomsSelected'];
	} else {
		$roomsSelected = array();
	}	
	
	if(isSet($_SESSION['AddEventMeetingRoomsArray'])){
		$meetingroom = $_SESSION['AddEventMeetingRoomsArray'];
	} else {
		$meetingroom = array();
	}
	
	if(isSet($_SESSION['AddEventRoomSelectedButNotConfirmed'])){
		$selectedMeetingRoomID = $_SESSION['AddEventRoomSelectedButNotConfirmed'];
	} else {
		$selectedMeetingRoomID = "";
	}
	
	if(isSet($_SESSION['AddEventWeekSelectedButNotConfirmed'])){
		$selectedWeekNumber = $_SESSION['AddEventWeekSelectedButNotConfirmed'];
	} else {
		$selectedWeekNumber = "";
	}
	
	// Give admin feedback on the roomname (if one) or the amount of rooms selected.
	if(isSet($_SESSION['AddEventRoomsSelected'])){
		if($_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room"){
			foreach($meetingroom AS $room){
				if($room['MeetingRoomID'] == $_SESSION['AddEventRoomsSelected']){
					$roomSelected = $room['MeetingRoomName'];
					break;
				}
			}
			$roomsSelectedFeedback = "Event will be scheduled for the room named: $roomSelected.";
		} elseif($_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms"){
			$numberOfRoomsSelected = sizeOf($_SESSION['AddEventRoomsSelected']);
			$roomsSelectedFeedback = "Event will be scheduled for $numberOfRoomsSelected room(s).";
		} elseif($_SESSION['AddEventRoomChoiceSelected'] == "Select All Rooms"){
			$numberOfRoomsSelected = sizeOf($meetingroom);
			$roomsSelectedFeedback = "Event will be scheduled for all rooms (Total of $numberOfRoomsSelected rooms).";
			if($_SESSION['AddEventRoomsSelected'] === TRUE){
				foreach($meetingroom AS $room){
					$roomIDs[] = $room['MeetingRoomID'];
				}
				$_SESSION['AddEventRoomsSelected'] = $roomIDs;
			}
		}
	}
	
	// Give admin feedback on the week info (if one) or the amount of weeks selected.
	if(isSet($_SESSION['AddEventWeeksSelected'])){
		if($_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week"){
			foreach($weeksOfTheYear AS $week){
				if($week['WeekNumber'] == $_SESSION['AddEventWeeksSelected']){
					$weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR);
					$weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR);
					$weekSelected = $week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd;
					break;
				}
			}
			$weeksSelectedFeedback = "Event will be scheduled for the week $weekSelected.";
		} elseif($_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks"){
			$numberOfWeeksSelected = sizeOf($_SESSION['AddEventWeeksSelected']);
			$weeksSelectedFeedback = "Event will be scheduled for $numberOfWeeksSelected weeks.";
		} elseif($_SESSION['AddEventWeekChoiceSelected'] == "Select All Weeks"){
			$numberOfWeeksSelected = sizeOf($weeksOfTheYear);
			$weeksSelectedFeedback = "Event will be scheduled for all the remaining weeks this year (Total of $numberOfWeeksSelected weeks).";
			if($_SESSION['AddEventWeeksSelected'] === TRUE){
				foreach($weeksOfTheYear AS $week){
					$weekNumbers[] = $week['WeekNumber'];
				}
				$_SESSION['AddEventWeeksSelected'] = $weekNumbers;				
			}
		}
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	include_once 'addevent.html.php';
	exit();
}

// If admin wants to submit the created event
if(isSet($_POST['add']) AND $_POST['add'] == "Create Event"){
	
	// Get valid user inputs
	list($invalidInput, $startTime, $endTime, $eventName, $eventDescription) = validateUserInputs('AddEventError', FALSE);
	
	if($invalidInput){
		rememberAddEventInputs();
		$_SESSION['refreshAddEvent'] = TRUE;
		header('Location: .');
		exit();
	}

	// Turn all admin selections into datetimes
	$weeksSelected = $_SESSION['AddEventWeeksSelected'];
	$daysSelected = $_SESSION['AddEventDaysSelected'];
	if(!is_array($weeksSelected)){
		$weeksSelected = array($weeksSelected);
	}
	if(!is_array($daysSelected)){
		$daysSelected = array($daysSelected);
	}
	$yearNow = date("Y"); // TO-DO: Change if we allow different years
	$dateTimeNow = getDatetimeNow();
	for($i=0; $i < sizeOf($weeksSelected); $i++){
		for($j=0; $j < sizeOf($daysSelected); $j++){
			$startDateTime = getDateTimeFromTimeDayNameWeekNumberAndYear($startTime,$daysSelected[$j],$weeksSelected[$i],$yearNow);
			$endDateTime =  getDateTimeFromTimeDayNameWeekNumberAndYear($endTime,$daysSelected[$j],$weeksSelected[$i],$yearNow);
			// Don't check if the datetime is in the past e.g. we selected a monday and it's already tuesday
			if($startDateTime > $dateTimeNow ){
				$dateTimesToCheck[] = array('StartDateTime' => $startDateTime, 'EndDateTime' => $endDateTime);
			}
		}
	}
	
	// Check if the timeslot(s) is taken for the selected meeting room(s)
		// What we want to achieve:
		// 1. Compare all datetimes and meeting room IDs to see if they're "available"
		// 2. Create event there if available
		// 3. Save information on events that couldn't be made due to it not being "available"
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		
		$sql =	" 	SELECT SUM(cnt)	AS HitCount
					FROM 
					(
						(
						SELECT 		COUNT(*) AS cnt
						FROM 		`booking` b
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
						)
						UNION
						(
						SELECT 		COUNT(*) AS cnt
						FROM 		`roomevent` rev
						WHERE 		rev.`meetingRoomID` = :MeetingRoomID
						AND	
								(		
										(
											rev.`startDateTime` >= :StartTime AND 
											rev.`startDateTime` < :EndTime
										) 
								OR 		(
											rev.`endDateTime` > :StartTime AND 
											rev.`endDateTime` <= :EndTime
										)
								OR 		(
											:EndTime > rev.`startDateTime` AND 
											:EndTime < rev.`endDateTime`
										)
								OR 		(
											:StartTime > rev.`startDateTime` AND 
											:StartTime < rev.`endDateTime`
										)
								)
						LIMIT 1
						)
					) AS TimeSlotTaken";
		$s = $pdo->prepare($sql);
		
		// We have to repeat this for every meeting room and every datetime.
		$roomsSelected = $_SESSION['AddEventRoomsSelected'];
		if(!is_array($roomsSelected)){
			$roomsSelected = array($roomsSelected);
		}
			// Meeting rooms selected
		$timeSlotTakenInfo = array();
		$timeSlotAvailableInfo = array();
		for($i=0; $i < sizeOf($roomsSelected); $i++){
			$meetingRoomID = $roomsSelected[$i];
				// Datetimes
			foreach($dateTimesToCheck AS $dateTimes){		
				$startDateTime = $dateTimes['StartDateTime'];
				$endDateTime = $dateTimes['EndDateTime'];
				$s->bindValue(':MeetingRoomID', $meetingRoomID);
				$s->bindValue(':StartTime', $startDateTime);
				$s->bindValue(':EndTime', $endDateTime);
				$s->execute();

				$row = $s->fetch(PDO::FETCH_ASSOC);
				if ($row['HitCount'] > 0){
					// Timeslot taken for that meeting room
					$timeSlotTakenInfo[] = array(
													'StartDateTime' => $startDateTime,
													'EndDateTime' => $endDateTime,
													'MeetingRoomID' => $meetingRoomID
 												);
				} else {
					// Timeslot NOT taken for that meeting room
					$timeSlotAvailableInfo[] = array(
													'StartDateTime' => $startDateTime,
													'EndDateTime' => $endDateTime,
													'MeetingRoomID' => $meetingRoomID
 												);
				}
			}
		}

		
	}
	catch(PDOException $e)
	{
		$error = 'Error checking if event time is available: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	try
	{
		if(isSet($timeSlotAvailableInfo) AND sizeOf($timeSlotAvailableInfo) > 0){
			$firstDate = convertDatetimeToFormat($timeSlotAvailableInfo[0]['StartDateTime'], 'Y-m-d H:i:s', 'Y-m-d');
			$lastDate = convertDatetimeToFormat(end($timeSlotAvailableInfo)['EndDateTime'], 'Y-m-d H:i:s', 'Y-m-d');
			$daysSelected = implode( "\n", $daysSelected);
			// Insert the new event base information
			$sql = "INSERT INTO `event`
					SET			`name` = :EventName,
								`description` = :EventDescription,
								`startTime` = :StartTime,
								`endTime` = :EndTime,
								`startDate`= :FirstDate,
								`lastDate` = :LastDate,
								`daysSelected` = :DaysSelected,
								`dateTimeCreated` = CURRENT_TIMESTAMP";
			$s = $pdo->prepare($sql);
			$s->bindValue(':EventName', $eventName);
			$s->bindValue(':EventDescription', $eventDescription);
			$s->bindValue(':StartTime', $startTime);
			$s->bindValue(':EndTime', $endTime);
			$s->bindValue(':FirstDate', $firstDate);
			$s->bindValue(':LastDate', $lastDate);
			$s->bindValue(':DaysSelected',$daysSelected);	
			$s->execute();
			
			$EventID = $pdo->lastInsertID();
			
			// Insert new events into database
			$sql = "INSERT INTO `roomevent`
					SET			`EventID` = :EventID,
								`meetingRoomID` = :MeetingRoomID,
								`startDateTime` = :StartDateTime,
								`endDateTime` = :EndDateTime";
			$pdo->beginTransaction();
			$s = $pdo->prepare($sql);
			$s->bindValue(':EventID', $EventID);
			foreach($timeSlotAvailableInfo AS $insert){
				$s->bindValue(':MeetingRoomID', $insert['MeetingRoomID']);
				$s->bindValue(':StartDateTime', $insert['StartDateTime']);
				$s->bindValue(':EndDateTime', $insert['EndDateTime']);
				$s->execute();
			}
			$pdo->commit();
		}
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error scheduling new events: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo->rollBack();
		$pdo = null;
		exit();
	}
	
	// Display to admin the events that did not get created due to the timeslot being taken
	if(isSet($timeSlotAvailableInfo) AND sizeOf($timeSlotAvailableInfo) > 0){
		if(isSet($timeSlotTakenInfo) AND sizeOf($timeSlotTakenInfo) > 0 ){
			// Get meeting room names from the IDs
			$lastRoomID = "";
			$roomName = "";
			foreach($timeSlotTakenInfo AS $event){
				$roomID = $event['MeetingRoomID'];
				if($roomID != $lastRoomID){
					foreach($_SESSION['AddEventMeetingRoomsArray'] AS $room){
						if($room['MeetingRoomID'] == $roomID){
							$roomName = $room['MeetingRoomName'];
							break;
						}
					}
				}
				$timeSlotTakenInfoWithRoomNames[] = array(
															'MeetingRoomName' => $roomName,
															'StartDateTime' => $event['StartDateTime'],
															'EndDateTime' => $event['EndDateTime']
														);
				$lastRoomID = $roomID;
			}
			
			$_SESSION['EventsUserFeedback'] = 	"Could not add all the events to the schedule, due to the timeslot being occupied.\n" .
												"In total " . sizeOf($timeSlotTakenInfo) . " events were not added.\n";
												"The following dates and meeting rooms were already taken:";
			
			foreach($timeSlotTakenInfoWithRoomNames AS $event){
				$_SESSION['EventsUserFeedback'] .= "\nMeeting Room: " . $event['MeetingRoomName'] . 
													", Start Date: " . convertDatetimeToFormat($event['StartDateTime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY) . 
													", End Date: " . convertDatetimeToFormat($event['EndDateTime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			}
			$_SESSION['EventsUserFeedback'] .= ".";
		} else {
			$_SESSION['EventsUserFeedback'] = "Successfully added all events to the schedule.";
		}
	} else {
		$_SESSION['EventsUserFeedback'] = "Could not add any events to the schedule, due to the timeslot being occupied for all of them.";
	}
	// TO-DO: Create log event?
	
	header("Location: .");
	exit();
}

// If admin wants to decide the amount of weeks to select
	// A single week (dropdown list)
if(isSet($_POST['add']) AND $_POST['add'] == "Select A Single Week"){
	
	$_SESSION['AddEventWeekChoiceSelected'] = "Select A Single Week";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// Multiple meeting rooms (checkboxes)
if(isSet($_POST['add']) AND $_POST['add'] == "Select Multiple Weeks"){
	
	$_SESSION['AddEventWeekChoiceSelected'] = "Select Multiple Weeks";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// All meeting rooms
if(isSet($_POST['add']) AND $_POST['add'] == "Select All Weeks"){
	
	$_SESSION['AddEventWeeksSelected'] = TRUE;
	$_SESSION['AddEventWeekChoiceSelected'] = "Select All Weeks";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == "Confirm Week(s)"){
	
	rememberAddEventInputs();

	if(isSet($_POST['weeksSelected'])){
		if(sizeOf($_POST['weeksSelected']) > 0){
			$_SESSION['AddEventWeeksSelected'] = $_POST['weeksSelected'];
		} else {
			$_SESSION['AddEventError'] = "You need to select at least one week.";
		}
	}

	if(isSet($_POST['weekNumber'])){
		$_SESSION['AddEventWeeksSelected'] = $_POST['weekNumber'];
		unset($_SESSION['AddEventWeekSelectedButNotConfirmed']);
	}
	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the week(s) selected decision
if(isSet($_POST['add']) AND $_POST['add'] == "Change Week Selection"){

	rememberAddEventInputs();
	
	unset($_SESSION['AddEventWeeksSelected']);
	unset($_SESSION['AddEventWeekChoiceSelected']);
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == "Confirm Day(s)"){
	
	rememberAddEventInputs();
	
	if(isSet($_POST['daysSelected']) AND sizeOf($_POST['daysSelected']) > 0){
		$_SESSION['AddEventDaysConfirmed'] = TRUE;
	} else {
		$_SESSION['AddEventError'] = "You need to select at least one day.";
	}
	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == "Change Day(s)"){
	
	rememberAddEventInputs();
	
	unset($_SESSION['AddEventDaysConfirmed']);	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == "Confirm Details"){
	
	list($invalidInput, $startTime, $endTime, $eventName, $eventDescription) = validateUserInputs('AddEventError', FALSE);
	
	if(!$invalidInput){
		$_SESSION['AddEventDetailsConfirmed'] = TRUE;
	}
	rememberAddEventInputs();
	
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == "Change Details"){

	rememberAddEventInputs();
	
	unset($_SESSION['AddEventDetailsConfirmed']);
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}


// If admin wants to decide the amount of meeting rooms to select
	// A single meeting room (dropdown list)
if(isSet($_POST['add']) AND $_POST['add'] == "Select A Single Room"){
	
	$_SESSION['AddEventRoomChoiceSelected'] = "Select A Single Room";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// Multiple meeting rooms (checkboxes)
if(isSet($_POST['add']) AND $_POST['add'] == "Select Multiple Rooms"){
	
	$_SESSION['AddEventRoomChoiceSelected'] = "Select Multiple Rooms";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// All meeting rooms
if(isSet($_POST['add']) AND $_POST['add'] == "Select All Rooms"){
	
	$_SESSION['AddEventRoomsSelected'] = TRUE;
	$_SESSION['AddEventRoomChoiceSelected'] = "Select All Rooms";
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}
	// To confirm room selection (a room/multiple rooms)
if(isSet($_POST['add']) AND $_POST['add'] == "Confirm Room(s)"){
	
	if(isSet($_POST['meetingroom'])){
		if(sizeOf($_POST['meetingroom']) > 0){
			$_SESSION['AddEventRoomsSelected'] = $_POST['meetingroom'];
		} else {
			$_SESSION['AddEventError'] = "You need to select at least one meeting room.";
		}
	}
	if(isSet($_POST['meetingRoomID'])){
		$_SESSION['AddEventRoomsSelected'] = $_POST['meetingRoomID'];
		unset($_SESSION['AddEventRoomSelectedButNotConfirmed']);
	}
	
	rememberAddEventInputs();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the meeting room(s) selected decision
if(isSet($_POST['add']) AND $_POST['add'] == "Change Room Selection"){

	rememberAddEventInputs();
	
	unset($_SESSION['AddEventRoomsSelected']);
	unset($_SESSION['AddEventRoomChoiceSelected']);
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['add']) AND $_POST['add'] == 'Reset'){
	clearAddEventSessions();
	$_SESSION['refreshAddEvent'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to leave the page and be directed back to the events page again
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['EventsUserFeedback'] = "You cancelled your new event.";
}

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddEventSessions();
//clearEditEventSessions();


// EVENTS OVERVIEW CODE SNIPPET START //

if(isSet($refreshEvents) AND $refreshEvents) {
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
					`daysSelected`		AS DaysSelected,
					(
						SELECT 		GROUP_CONCAT(DISTINCT m.`name` separator ",\n")
						FROM		`roomevent` rev
						INNER JOIN 	`meetingroom` m
						ON			rev.`meetingRoomID` = m.`meetingRoomID`
						WHERE		rev.`EventID` = TheEventID
					)					AS UsedMeetingRooms,
					(
						SELECT 	COUNT(*)
						FROM 	`meetingroom`
					)					AS TotalMeetingRooms,
					(
						SELECT 	`startDateTime`
						FROM 	`roomevent`
						WHERE	`EventID` = TheEventID
						AND 	`startDateTime` > CURRENT_TIMESTAMP
						ORDER BY UNIX_TIMESTAMP(`startDateTime`) ASC
						LIMIT 1
					) 					AS NextStart
			FROM 	`event`';
	$return = $pdo->query($sql);
	$result = $return->fetchAll(PDO::FETCH_ASSOC);
	if(isSet($result)){
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
	$weekStart = date("W",strtotime($startDate));
	$weekEnd = date("W",strtotime($lastDate));
	$nextStart = $row['NextStart'];

	// Check if the event is a single day or multiple days
	$daysSelected = $row['DaysSelected'];
	$daysSelectedArray = explode("\n", $daysSelected);
	$numberOfDaysSelected = sizeOf($daysSelectedArray);

	if($dateNow > $lastDate AND $timeNow > $endTime){
		$completed = TRUE;
	} else {
		$completed = FALSE;
	}

	if($weekStart == $weekEnd){
		// single week
		if($numberOfDaysSelected > 1){
			if($completed){
				$status = "Completed\n(Single Week)";
			} else {
				$status = "Active\n(Single Week)";
			}
		} else {
			if($completed){
				$status = "Completed\n(Single Day)";
			} else {
				$status = "Active\n(Single Day)";
			}
		}
	} elseif($weekEnd > $weekStart) {
		// repeated weeks
		if($completed){
			$status = "Completed\n(Multiple Weeks)";
		} else {
			$status = "Active\n(Multiple Weeks)";
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
	$displayableNextStart = convertDatetimeToFormat($nextStart, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	// Check if we should list individual meeting rooms or just mention that all have been selected
	$totalMeetingRooms = $row['TotalMeetingRooms'];
	$meetingRoomsUsed = $row['UsedMeetingRooms'];
	$usedMeetingRoomsArray = explode(",", $meetingRoomsUsed);
	$numberOfUsedMeetingRooms = sizeOf($usedMeetingRoomsArray);
	if($numberOfUsedMeetingRooms == $totalMeetingRooms){
		$usedMeetingRooms = "All " . $numberOfUsedMeetingRooms . " Rooms";
	} else {
		$usedMeetingRooms = $meetingRoomsUsed;
	}

	if($completed){
		$completedEvents[] = array(
							'EventStatus' => $status,
							'EventID' => $row['TheEventID'], 
							'DateTimeCreated' => $displayableDateCreated, 
							'EventName' => $row['EventName'], 
							'EventDescription' => $row['EventDescription'], 
							'UsedMeetingRooms' => $usedMeetingRooms,
							'DaysSelected' => $daysSelected,
							'StartTime' => $displayableStartTime,
							'EndTime' => $displayableEndTime,
							'StartDate' => $startDateWithWeekNumber,
							'LastDate' => $endDateWithWeekNumber
						);
	} else {
		$activeEvents[] = array(
							'EventStatus' => $status,
							'EventID' => $row['TheEventID'], 
							'DateTimeCreated' => $displayableDateCreated, 
							'EventName' => $row['EventName'], 
							'EventDescription' => $row['EventDescription'],
							'UsedMeetingRooms' => $usedMeetingRooms,
							'DaysSelected' => $daysSelected,
							'StartTime' => $displayableStartTime,
							'EndTime' => $displayableEndTime,
							'StartDate' => $startDateWithWeekNumber,
							'LastDate' => $endDateWithWeekNumber,
							'NextStart' => $displayableNextStart
						);
	}
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Events table in HTML
include_once 'events.html.php';
?>