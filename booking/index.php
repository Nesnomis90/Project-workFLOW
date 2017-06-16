<?php 
// This is the index file for the booking folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

/*
	TO-DO:
		Cancel with code/login
		Edit booking from code/login
*/

// Function to clear sessions used to remember user inputs on refreshing the add booking form
function clearAddCreateBookingSessions(){
	unset($_SESSION['AddCreateBookingInfoArray']);
	unset($_SESSION['AddCreateBookingChangeUser']);
	unset($_SESSION['AddCreateBookingUsersArray']);
	unset($_SESSION['AddCreateBookingOriginalInfoArray']);
	unset($_SESSION['AddCreateBookingMeetingRoomsArray']);	
	unset($_SESSION['AddCreateBookingUserSearch']);
	unset($_SESSION['AddCreateBookingSelectedNewUser']);
	unset($_SESSION['AddCreateBookingSelectedACompany']);	
	unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
	
	unset($_SESSION['bookingCodeUserID']);
	
	unset($_SESSION['cancelBookingOriginalValues']);	
}

// Function to clear sessions used to remember user inputs on refreshing the edit booking form
function clearEditCreateBookingSessions(){
	unset($_SESSION['EditCreateBookingInfoArray']);
	unset($_SESSION['EditCreateBookingChangeUser']);
	unset($_SESSION['EditCreateBookingUsersArray']);
	unset($_SESSION['EditCreateBookingOriginalInfoArray']);
	unset($_SESSION['EditCreateBookingMeetingRoomsArray']);	
	unset($_SESSION['EditCreateBookingUserSearch']);
	unset($_SESSION['EditCreateBookingSelectedNewUser']);
	unset($_SESSION['EditCreateBookingSelectedACompany']);
	unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
	
	unset($_SESSION['cancelBookingOriginalValues']);
}

// Function to remember the user inputs in Edit Booking
function rememberEditCreateBookingInputs(){
	if(isset($_SESSION['EditCreateBookingInfoArray'])){
		$newValues = $_SESSION['EditCreateBookingInfoArray'];

			// The company selected
		$newValues['TheCompanyID'] = $_POST['companyID'];
			// The user selected
		$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
			// The booking description
		$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);

		$_SESSION['EditCreateBookingInfoArray'] = $newValues;			
	}
}

// Function to remember the user inputs in Add Booking
function rememberAddCreateBookingInputs(){
	if(isset($_SESSION['AddCreateBookingInfoArray'])){
		$newValues = $_SESSION['AddCreateBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isset($_POST['userID'])){
			$newValues['TheUserID'] = $_POST['userID'];
		}
			// The meeting room selected
		if(isset($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
		} else {
			$meetingRoomID = $_POST['meetingRoomID'];
		}				
		$newValues['TheMeetingRoomID'] = $meetingRoomID; 
			// The company selected
		$newValues['TheCompanyID'] = $_POST['companyID'];
			// The user selected
		$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
			// The booking description
		$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);
			// The start time
		$newValues['StartTime'] = trimExcessWhitespace($_POST['startDateTime']);
			// The end time
		$newValues['EndTime'] = trimExcessWhitespace($_POST['endDateTime']);
		
		$_SESSION['AddCreateBookingInfoArray'] = $newValues;			
	}
}

// Function to check if user is logged in or if we're on a local device
function checkIfLocalDeviceOrLoggedIn(){
	if(isset($_SESSION['LoggedInUserID'])){
		$SelectedUserID = $_SESSION['LoggedInUserID'];
	}
	if(isset($_SESSION['bookingCodeUserID'])){
		$SelectedUserID = $_SESSION['bookingCodeUserID'];
	}

	// Make sure user is logged in before going further
		// If local, use booking code
	if(!isset($SelectedUserID)){
		if(isset($_SESSION['DefaultMeetingRoomInfo'])){
			// We're accessing a local device.
			// Confirm with booking code
			// Set default values for bookingcode template
			var_dump($_SESSION); // TO-DO: remove after testing is done
			$bookingCode = "";
			include_once 'bookingcode.html.php';
			exit();
		}
			// If not local, use regular log in
		if(checkIfUserIsLoggedIn() === FALSE){
			makeUserLogIn();
			var_dump($_SESSION); // TO-DO: remove after testing is done
			exit();
		}	
	}
	return $SelectedUserID;
}

// This is used on cancel
function emailUserOnCancelledBooking(){

	$bookingCreatorUserEmail = $_SESSION['cancelBookingOriginalValues']['UserEmail'];
	unset($_SESSION['cancelBookingOriginalValues']['UserEmail']);
	$bookingCreatorMeetingInfo = $_SESSION['cancelBookingOriginalValues']['MeetingInfo'];
	$emailSubject = "Your meeting has been cancelled!";

	$emailMessage = 
	"A booked meeting has been cancelled by an Admin!\n" .
	"The meeting was booked for the room " . $bookingCreatorMeetingInfo;
	
	$email = $bookingCreatorUserEmail;
	
	$mailResult = sendEmail($email, $emailSubject, $emailMessage);

	if(!$mailResult){
		$_SESSION['normalBookingFeedback'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['normalBookingFeedback'] .= " This is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing			
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse){
	// Get user inputs
	$invalidInput = FALSE;
	$usingBookingCode = FALSE;
	
	if(isset($_POST['startDateTime']) AND !$invalidInput){
		$startDateTimeString = $_POST['startDateTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting a start time.";
	}
	if(isset($_POST['endDateTime']) AND !$invalidInput){
		$endDateTimeString = $_POST['endDateTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting an end time.";
	}
	
	if(isset($_POST['displayName'])){
		$displayNameString = $_POST['displayName'];
	} else {
		$displayNameString = '';
	}
	if(isset($_POST['description'])){
		$bookingDescriptionString = $_POST['description'];
	} else {
		$bookingDescriptionString = '';
	}
	
	if($usingBookingCode){
		if(isset($_POST['bookingCode'])){
			$bookingCode = $_POST['bookingCode'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting a booking code.";		
		}
	}
	
	// Remove excess whitespace and prepare strings for validation
	$validatedStartDateTime = trimExcessWhitespace($startDateTimeString);
	$validatedEndDateTime = trimExcessWhitespace($endDateTimeString);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	if($usingBookingCode){
		$validatedBookingCode = trimExcessWhitespace($bookingCode);
	} else {
		$validatedBookingCode = "";
	}
	
	// Do actual input validation
	if(validateDateTimeString($validatedStartDateTime) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted start time has illegal characters in it.";
	}
	if(validateDateTimeString($validatedEndDateTime) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted end time has illegal characters in it.";
	}
	if(validateString($validatedDisplayName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted display name has illegal characters in it.";
	}
	if(validateString($validatedBookingDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted booking description has illegal characters in it.";
	}
	
	if($usingBookingCode){	
		if(validateIntegerNumber($validatedBookingCode) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "Your submitted booking code has illegal characters in it.";		
		}
	}
	
	// Are values actually filled in?
	if($validatedStartDateTime == "" AND $validatedEndDateTime == "" AND !$invalidInput){
		
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start and end time for your booking.";	
		$invalidInput = TRUE;
	} elseif($validatedStartDateTime != "" AND $validatedEndDateTime == "" AND !$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in an end time for your booking.";	
		$invalidInput = TRUE;		
	} elseif($validatedStartDateTime == "" AND $validatedEndDateTime != "" AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start time for your booking.";	
		$invalidInput = TRUE;		
	}
	if($usingBookingCode){
		if(isset($validatedBookingCode) AND $validatedBookingCode == "" AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "You need to fill in a booking code to be able to create the booking.";	
			$invalidInput = TRUE;
		}
	}
	
	// Check if input length is allowed
		// DisplayName
	$invalidDisplayName = isLengthInvalidDisplayName($validatedDisplayName);
	if($invalidDisplayName AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The display name submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// BookingDescription
	$invalidBookingDescription = isLengthInvalidBookingDescription($validatedBookingDescription);
	if($invalidBookingDescription AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	
	// Check if the dateTime inputs we received are actually datetimes
	$startDateTime = correctDatetimeFormat($validatedStartDateTime);
	$endDateTime = correctDatetimeFormat($validatedEndDateTime);

	if (isset($startDateTime) AND $startDateTime === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The start date you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}
	if (isset($endDateTime) AND $endDateTime === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The end date you submitted did not have a correct format. Please try again.";
		$invalidInput = TRUE;
	}	
	
	$timeNow = getDatetimeNow();
	
	if($startDateTime > $endDateTime AND !$invalidInput){
		// End time can't be before the start time
		
		$_SESSION[$FeedbackSessionToUse] = "The start time can't be later than the end time. Please select a new start time or end time.";
		$invalidInput = TRUE;
	}
	
	if($startDateTime < $timeNow AND !$invalidInput){
		// You can't book a meeting starting in the past.
		
		$_SESSION[$FeedbackSessionToUse] = "The start time you selected is already over. Select a new start time.";
		$invalidInput = TRUE;
	}
	
	if($endDateTime < $timeNow AND !$invalidInput){
		// You can't book a meeting ending in the past.
		
		$_SESSION[$FeedbackSessionToUse] = "The end time you selected is already over. Select a new end time.";
		$invalidInput = TRUE;	
	}	
	
	if($endDateTime == $startDateTime AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to select an end time that is different from your start time.";	
		$invalidInput = TRUE;				
	} 

	// Check if booking code submitted is a valid booking code
	if($usingBookingCode){
		if(databaseContainsBookingCode($validatedBookingCode) === FALSE AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "The booking code you submitted is not valid.";	
			$invalidInput = TRUE;
		}
	}
	
	// We want to check if a booking is in the correct minute slice e.g. 15 minute increments.
		// We check both start and end time for online/admin bookings
		// Does not apply to booking with booking code (starts immediately until next/selected chunk
	$invalidStartTime = isBookingDateTimeMinutesInvalid($startDateTime);
	if($invalidStartTime AND !$usingBookingCode AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your start time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
		$invalidInput = TRUE;	
	}
	$invalidEndTime = isBookingDateTimeMinutesInvalid($endDateTime);
	if($invalidEndTime AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your end time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
		$invalidInput = TRUE;	
	}
	
	// We want to check if the booking is the correct minimum length
		// Does not apply to booking with booking code (starts immediately until next/selected chunk
	$invalidBookingLength = isBookingTimeDurationInvalid($startDateTime, $endDateTime);
	if($invalidBookingLength AND !$usingBookingCode AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your start time and end time needs to have at least a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes difference.";
		$invalidInput = TRUE;		
	}

	return array($invalidInput, $startDateTime, $endDateTime, $validatedBookingDescription, $validatedDisplayName, $validatedBookingCode);
}

// Check if we're accessing from a local device
// If so, set that meeting room's info as the default meeting room info
checkIfLocalDevice();

// If user wants to go back to the main page while in the confirm booking page
if (isset($_POST['action']) and $_POST['action'] == 'Go Back'){
	
	if(isset($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to go back to the main page while editing
if (isset($_POST['edit']) and $_POST['edit'] == 'Go Back'){
	
	$_SESSION['normalBookingFeedback'] = "You cancelled your booking editing.";
	
	if(isset($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to refresh the page to get the most up-to-date information
if (isset($_POST['action']) and $_POST['action'] == 'Refresh'){
	
	if(isset($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to cancel a scheduled booked meeting
if (	(isset($_POST['action']) and $_POST['action'] == 'Cancel') OR 
		(isset($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking']))
{
	if(isset($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking']){
		unset($_SESSION['refreshCancelBooking']);
	} else {
		$_SESSION['cancelBookingOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['cancelBookingOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['cancelBookingOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];
	}
	
	$bookingID = $_SESSION['cancelBookingOriginalValues']['BookingID'];
	$bookingStatus = $_SESSION['cancelBookingOriginalValues']['BookingStatus'];
	$bookingMeetingInfo = $_SESSION['cancelBookingOriginalValues']['MeetingInfo'];
	
	$_SESSION['confirmOrigins'] = "Cancel";
	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
	unset($_SESSION['confirmOrigins']);
	
	// Check if selected user ID is creator of booking or an admin
	$continueCancel = FALSE;
	$cancelledByAdmin = FALSE;
		// Check if the user is the creator of the booking	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'SELECT 		COUNT(*)		AS HitCount,
							b.`userID`,
							u.`email`		AS UserEmail,
							u.`firstName`,
							u.`lastName`
				FROM		`booking` b
				INNER JOIN 	`user` u
				ON 			b.`userID` = u.`userID`
				WHERE 		`bookingID` = :id
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $bookingID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$bookingCreatorUserID = $row['userID'];
		$bookingCreatorUserEmail = $row['UserEmail'];
		$bookingCreatorUserInfo = $row['lastName'] . ", " . $row['firstName'] . " - " . $row['UserEmail'];
		if($row['HitCount'] > 0){
			if($bookingCreatorUserID == $SelectedUserID){
				$continueCancel = TRUE;
			}
		} 
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
		// Check if the user is an admin
		// Only needed if the the user isn't the creator of the booking
	if($SelectedUserID != $bookingCreatorUserID) {
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'SELECT 	a.`AccessName`	
					FROM	`user` u
					JOIN	`accesslevel` a
					ON 		u.`AccessID` = a.`AccessID`
					WHERE 	u.`userID` = :userID
					LIMIT	1';
					
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $SelectedUserID);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);
			if($row['AccessName'] == "Admin"){
				$continueCancel = TRUE;
				$cancelledByAdmin = TRUE;
			}
			
			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}		
	}

	if($continueCancel === FALSE){
		$_SESSION['normalBookingFeedback'] = "You cannot cancel this booked meeting.";
		if(isset($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = '.';
		}
		header('Location: ' . $location);
		exit();				
	}
	
	// Only cancel if booking is currently active
	if(	isset($bookingStatus) AND  
		($bookingStatus == 'Active' OR $bookingStatus == 'Active Today')){
		// Update cancellation date for selected booked meeting in database
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`booking` 
					SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL				
					WHERE 	`bookingID` = :id
					AND		`dateTimeCancelled` IS NULL
					AND		`actualEndDateTime` IS NULL';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $bookingID);
			$s->execute();
			
			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
		
		$_SESSION['normalBookingFeedback'] = "Successfully cancelled the booking.";
		
			// Add a log event that a booking was cancelled
		try
		{
			$nameOfUserWhoBooked = "N/A";
			if(isset($_SESSION['LoggedInUserName'])){
				$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
			}
			if(isset($_SESSION["AddCreateBookingInfoArray"])){
				$nameOfUserWhoBooked = $_SESSION["AddCreateBookingInfoArray"]["UserLastname"] . ', ' . $_SESSION["AddCreateBookingInfoArray"]["UserFirstname"];
			}			
			
			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isset($bookingCreatorUserInfo) AND isset($bookingMeetingInfo)){
				$logEventDescription = 'The booking made for ' . $bookingCreatorUserInfo . ' for the meeting room ' .
				$bookingMeetingInfo . ' was cancelled by: ' . $nameOfUserWhoBooked;
			} else {
				$logEventDescription = 'A booking was cancelled by: ' . $nameOfUserWhoBooked;
			}
			
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT `actionID` 
													FROM `logaction`
													WHERE `name` = 'Booking Cancelled'
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
		if($cancelledByAdmin){
			$_SESSION['cancelBookingOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;
			emailUserOnCancelledBooking();
		}
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['normalBookingFeedback'] = "Meeting has already ended. Did not cancel it.";
	}

	unset($_SESSION['cancelBookingOriginalValues']);	
	// Load booked meetings list webpage with updated database
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$location = '.';
	}
	header('Location: ' . $location);
	exit();		
}

// BOOKING OVERVIEW CODE SNIPPETS // END //

// ADD BOOKING CODE SNIPPET // START //

// Handles booking code check
if(isset($_POST['action']) AND $_POST['action'] == "confirmcode"){
	
	$bookingCode = trim($_POST['bookingCode']);
	$validatedBookingCode = trimAllWhitespace($bookingCode);
	if(validateIntegerNumber($validatedBookingCode) !== TRUE){
		$bookingCode = "";
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted had non-numbers in it.";
		var_dump($_SESSION); // TO-DO: remove after testing is done
		include_once 'bookingcode.html.php';
		exit();
	}
	
	list($invalidBookingCode,$bookingCode) = isNumberInvalidBookingCode($validatedBookingCode);
	if($invalidBookingCode === TRUE){
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted (" . $bookingCode .") is an invalid code.";
		$bookingCode = "";
		var_dump($_SESSION); // TO-DO: remove after testing is done
		include_once 'bookingcode.html.php';
		exit();	
	}
	
	$hashedBookingCode = hashBookingCode($validatedBookingCode);
	
	// Code is a valid digit. Check if it matches with a user in our database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get booking information
		$pdo = connect_to_db();
		// Get name and IDs for meeting rooms
		$sql = 'SELECT 	COUNT(*)		AS HitCount,
						`userID`
				FROM 	`user`
				WHERE	`isActive` = 1
				AND		`bookingCode` = :bookingCode
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':bookingCode',$hashedBookingCode);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
			
		//Close connection
		$pdo = null;
		
		if ($row['HitCount'] > 0)
		{
			// Booking code is a valid user
			$_SESSION['bookingCodeUserID'] = $row['userID'];
			// Check if we are confirming a create booking, a cancel or an edit.
			if($_SESSION['confirmOrigins'] == "Create Meeting"){
				$_SESSION['refreshAddCreateBooking'] = TRUE;
				unset($_SESSION['confirmOrigins']);
			}
			if($_SESSION['confirmOrigins'] == "Cancel"){
				$_SESSION['refreshCancelBooking'] = TRUE;
				unset($_SESSION['confirmOrigins']);
			}
			
			if(isset($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$meetingRoomID = $_POST['meetingRoomID'];
				$location = '.';
			}
			header('Location: ' . $location);
			exit();						
		} else {
			$_SESSION['confirmBookingCodeError'] = "The booking code you submitted (" . $bookingCode .") is an incorrect code.";
			$bookingCode = "";
		
			var_dump($_SESSION); // TO-DO: Remove after testing
			
			include_once 'bookingcode.html.php';
			exit();
		}
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching user information from booking code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
}

// Handles booking based on selected meeting room
if(	((isset($_POST['action']) AND $_POST['action'] == 'Create Meeting')) OR
	(isset($_SESSION['refreshAddCreateBooking']) AND $_SESSION['refreshAddCreateBooking']))
{
	// Confirm that we've reset.
	unset($_SESSION['refreshAddCreateBooking']);
	
	$_SESSION['confirmOrigins'] = "Create Meeting";
	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
	unset($_SESSION['confirmOrigins']);
	
	// Get information from database on booking information user can choose between
	if(!isset($_SESSION['AddCreateBookingMeetingRoomsArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			// Get booking information
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
									'meetingRoomID' => $row['meetingRoomID'],
									'meetingRoomName' => $row['name']
									);
			}		
			
			$_SESSION['AddCreateBookingMeetingRoomsArray'] = $meetingroom;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching meeting room details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
	}

	if(!isset($_SESSION['AddCreateBookingInfoArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			// Get the logged in user's default booking information
			$pdo = connect_to_db();
			$sql = 'SELECT	`bookingdescription`, 
							`displayname`,
							`firstName`,
							`lastName`,
							`email`
					FROM 	`user`
					WHERE 	`userID` = :userID
					LIMIT 	1';
				
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $SelectedUserID);
			$s->execute();
			
			// Create an array with the row information we retrieved
			$result = $s->fetch(PDO::FETCH_ASSOC);
				
			// Set default booking display name and booking description
			if($result['displayname']!=NULL){
				$displayName = $result['displayname'];
			} else {
				$displayName = "";
			}

			if($result['bookingdescription']!=NULL){
				$description = $result['bookingdescription'];
			} else {
				$description = "";
			}
			
			if($result['firstName']!=NULL){
				$firstname = $result['firstName'];
			}		
			
			if($result['lastName']!=NULL){
				$lastname = $result['lastName'];
			}	
			
			if($result['email']!=NULL){
				$email = $result['email'];
			}					

			//Close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching default user details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}	
	
		// Create an array with the row information we want to use	
		$_SESSION['AddCreateBookingInfoArray'] = array(
													'TheCompanyID' => '',
													'TheMeetingRoomID' => '',
													'StartTime' => '',
													'EndTime' => '',
													'BookingDescription' => '',
													'BookedBy' => '',
													'BookedForCompany' => '',
													'TheUserID' => '',
													'UserFirstname' => '',
													'UserLastname' => '',
													'UserEmail' => '',
													'UserDefaultDisplayName' => '',
													'UserDefaultBookingDescription' => ''
												);			
		$_SESSION['AddCreateBookingInfoArray']['UserDefaultBookingDescription'] = $description;
		$_SESSION['AddCreateBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
		$_SESSION['AddCreateBookingInfoArray']['UserFirstname'] = $firstname;	
		$_SESSION['AddCreateBookingInfoArray']['UserLastname'] = $lastname;	
		$_SESSION['AddCreateBookingInfoArray']['UserEmail'] = $email;	
		$_SESSION['AddCreateBookingInfoArray']['TheUserID'] = $SelectedUserID;
		if(isset($_GET['meetingroom'])){
			$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = $_GET['meetingroom'];
		}
		$_SESSION['AddCreateBookingOriginalInfoArray'] = $_SESSION['AddCreateBookingInfoArray'];
	}

		// Check if we need a company select for the booking
	try
	{		
		// We want the companies the user works for to decide if we need to
		// have a dropdown select or just a fixed value (with 0 or 1 company)
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT	c.`companyID`,
						c.`name` 					AS companyName
				FROM 	`user` u
				JOIN 	`employee` e
				ON 		e.userID = u.userID
				JOIN	`company` c
				ON 		c.companyID = e.companyID
				WHERE 	u.`userID` = :userID';
			
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $SelectedUserID);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll();
			
		foreach($result as $row){		
			// Get the companies the user works for
			// This will be used to create a dropdown list in HTML
			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName']
								);
		}
	
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isset($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['AddCreateBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
				$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['AddCreateBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
			$_SESSION['AddCreateBookingCompanyArray'] = $company;
		} else{
			// User is NOT in a company
			
			$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
			unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
			unset($_SESSION['AddCreateBookingCompanyArray']);
			$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['AddCreateBookingInfoArray']['BookedForCompany'] = "";
		}		
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching user details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();					
	}
	
	// Set the correct information
	if(isset($_GET['meetingroom'])){
		$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = $_GET['meetingroom'];
	} elseif(isset($_POST['meetingRoomID'])) {
		$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = $_POST['meetingRoomID'];
	} else {
		$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = "";
	}
	$row = $_SESSION['AddCreateBookingInfoArray'];
	$original = $_SESSION['AddCreateBookingOriginalInfoArray'];

		// Altered inputs
	if(isset($row['TheCompanyID'])){
		
			// Changed company?
		if(isset($company)){
			foreach($company AS $cmp){
				if($cmp['companyID'] == $row['TheCompanyID']){
					$row['BookedForCompany'] = $cmp['companyName'];
					break;
				}
			}				
		}
		
		$selectedCompanyID = $row['TheCompanyID'];
		$companyID = $row['TheCompanyID'];
	} else {
		$selectedCompanyID = '';
		$companyID = '';	
	}

	if(isset($row['BookedForCompany'])){
		$companyName = $row['BookedForCompany'];
	} else {
		$companyName = '';
	}	
	
	//	userID has been set earlier
	$meetingroom = $_SESSION['AddCreateBookingMeetingRoomsArray'];
	if(isset($row['TheMeetingRoomID'])){
		$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	} else {
		$selectedMeetingRoomID = '';
	}
	if(isset($_GET['meetingroom'])){
		$selectedMeetingRoomID = $_GET['meetingroom'];
	}
	if(isset($row['StartTime']) AND $row['StartTime'] != ""){
		$startDateTime = $row['StartTime'];
	} else {
		$startDateTime = getDatetimeNow();
		$startDateTime = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	
	if(isset($row['EndTime']) AND $row['EndTime'] != ""){
		$endDateTime = $row['EndTime'];
	} else {
		$endDateTime = getDatetimeNow();
		$endDateTime = convertDatetimeToFormat($endDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	
	if(isset($row['BookedBy'])){
		$displayName = $row['BookedBy'];
	} else {
		$displayName = '';
	}
	
	if(isset($row['BookingDescription'])){
		$description = $row['BookingDescription'];
	} else {
		$description = '';
	}
	
	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];	

	$_SESSION['AddCreateBookingInfoArray'] = $row; // Remember the company/user info we changed based on user choice
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	// Change form
	include 'addbooking.html.php';
	exit();		
}

// When the user has added the needed information and wants to add the booking
if (isset($_POST['add']) AND $_POST['add'] == "Add Booking")
{
	// Validate user inputs
	list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname, $bookingCode) = validateUserInputs('AddCreateBookingError');
					
	// handle feedback process on invalid input values
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	
	if($invalidInput){
		
		rememberAddCreateBookingInputs();
		$_SESSION['refreshAddCreateBooking'] = TRUE;
		
		header('Location: ' . $location);
		exit();			
	}
	
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
	}	
	
	// Check if the timeslot is taken for the selected meeting room
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql =	" 	SELECT 	COUNT(*)	AS HitCount
					FROM 	(
								SELECT 	1
								FROM 	`booking`
								WHERE 	`meetingRoomID` = :MeetingRoomID
								AND		`dateTimeCancelled` IS NULL
								AND		`actualEndDateTime` IS NULL
								AND		
								(		
										(
											`startDateTime` >= :StartTime AND 
											`startDateTime` < :EndTime
										) 
								OR 		(
											`endDateTime` > :StartTime AND 
											`endDateTime` <= :EndTime
										)
								OR 		(
											:EndTime > `startDateTime` AND 
											:EndTime < `endDateTime`
										)
								OR 		(
											:StartTime > `startDateTime` AND 
											:StartTime < `endDateTime`
										)
								)
								LIMIT 1
							) AS BookingsFound";
		$s = $pdo->prepare($sql);
		
		$s->bindValue(':MeetingRoomID', $meetingRoomID);
		$s->bindValue(':StartTime', $startDateTime);
		$s->bindValue(':EndTime', $endDateTime);
		$s->execute();
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error checking if booking time is available: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Check if we got any hits, if so the timeslot is already taken
	$row = $s->fetch(PDO::FETCH_ASSOC);		
	if ($row['HitCount'] > 0){

		// Timeslot was taken
		rememberAddCreateBookingInputs();
		
		$_SESSION['AddCreateBookingError'] = "The booking couldn't be made. The timeslot is already taken for this meeting room.";
		$_SESSION['refreshAddCreateBooking'] = TRUE;	

		if(isset($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$meetingRoomID = $meetingRoomID;
			$location = '.';
		}
		header('Location: ' . $location);
		exit();				
	}
	
	// Add the booking to the database
	try
	{	
		if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
			$_POST['companyID'] != ''){
			$companyID = $_POST['companyID'];
		} else {
			$companyID = NULL;
		}
	
		// Generate cancellation code
		$cancellationCode = generateCancellationCode();
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `booking` 
				SET			`meetingRoomID` = :meetingRoomID,
							`userID` = :userID,
							`companyID` = :companyID,
							`displayName` = :displayName,
							`startDateTime` = :startDateTime,
							`endDateTime` = :endDateTime,
							`description` = :description,
							`cancellationCode` = :cancellationCode';

		$s = $pdo->prepare($sql);
		
		$s->bindValue(':meetingRoomID', $meetingRoomID);
		$s->bindValue(':userID', $_SESSION["AddCreateBookingInfoArray"]["TheUserID"]);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $dspname);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':description', $bknDscrptn);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();

		unset($_SESSION['lastBookingID']);
		$_SESSION['lastBookingID'] = $pdo->lastInsertId();
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted booking to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['normalBookingFeedback'] = "Successfully created the booking.";
	
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	// Add a log event that a booking has been created
	try
	{
		// Get meeting room name
		$MeetingRoomName = 'N/A';
		foreach ($_SESSION['AddCreateBookingMeetingRoomsArray'] AS $room){
			if($room['meetingRoomID'] == $meetingRoomID){
				$MeetingRoomName = $room['meetingRoomName'];
				break;
			}
		}
		unset($_SESSION['AddCreateBookingMeetingRoomsArray']);
		
		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$displayValidatedStartDate . ' to ' . $displayValidatedEndDate;
		
		// Get user information
		$userinfo = 'N/A';
		$info = $_SESSION['AddCreateBookingInfoArray']; 
		if(isset($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}
		
		// Get company name
		$companyName = 'N/A';
		if(isset($companyID)){
			foreach($_SESSION['AddCreateBookingCompanyArray'] AS $company){
				if($companyID == $company['companyID']){
					$companyName = $company['companyName'];
					break;
				}
			}
		}
		
		$nameOfUserWhoBooked = "N/A";
		if(isset($_SESSION['LoggedInUserName'])){
			$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
		}
		if(isset($_SESSION["AddCreateBookingInfoArray"])){
			$nameOfUserWhoBooked = $_SESSION["AddCreateBookingInfoArray"]["UserLastname"] . ', ' . $_SESSION["AddCreateBookingInfoArray"]["UserFirstname"];
		}
	
		// Save a description with information about the booking that was created
		$logEventDescription = 'A booking was created for the meeting room: ' . $meetinginfo . 
		', for the user: ' . $userinfo . ' and company: ' . $companyName . '. Booking was made by: ' . $nameOfUserWhoBooked;
		
		if(isset($_SESSION['lastBookingID'])){
			$lastBookingID = $_SESSION['lastBookingID'];
			unset($_SESSION['lastBookingID']);				
		}

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Created'
											),
							`userID` = :UserID,
							`meetingRoomID` = :MeetingRoomID,
							`bookingID` = :BookingID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->bindValue(':BookingID', $lastBookingID);
		$s->bindValue(':MeetingRoomID', $meetingRoomID);
		$s->bindValue(':UserID', $_SESSION["AddCreateBookingInfoArray"]["TheUserID"]);
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
	
	//Send email with booking information and cancellation code to the user who the booking is for.
		// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email	

	$emailSubject = "New Booking Information!";
	
	$emailMessage = 
	"Your meeting has been successfully booked!\n" . 
	"The meeting has been set to: \n" .
	"Meeting Room: " . $MeetingRoomName . ".\n" . 
	"Start Time: " . $displayValidatedStartDate . ".\n" .
	"End Time: " . $displayValidatedEndDate . ".\n\n" .
	"If you wish to cancel your meeting, or just end it early, you can easily do so by clicking the link given below.\n" .
	"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
	"/booking/?cancellationcode=" . $cancellationCode;		

	$email = $_SESSION['AddCreateBookingInfoArray']['UserEmail'];
	
	$mailResult = sendEmail($email, $emailSubject, $emailMessage);
	
	if(!$mailResult){
		$_SESSION['normalBookingFeedback'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['normalBookingFeedback'] .= " This is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing	
	
	// Booking a new meeting is done. Reset all connected sessions.
	clearAddCreateBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

//	User wants to change the company the booking is for (after having already selected it)
if(isset($_POST['add']) AND $_POST['add'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['AddCreateBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddCreateBookingInputs();
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// User confirms what company he wants the booking to be for.
if(isset($_POST['add']) AND $_POST['add'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberAddCreateBookingInputs();
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to get their default display name
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Display Name"){	  
	$displayName = $_SESSION['AddCreateBookingInfoArray']['UserDefaultDisplayName'];
	if(isset($_SESSION['AddCreateBookingInfoArray'])){
		rememberAddCreateBookingInputs();

		if($displayName != ""){
			if($displayName != $_SESSION['AddCreateBookingInfoArray']['BookedBy']){
				
					// The user selected
				$_SESSION['AddCreateBookingInfoArray']['BookedBy'] = $displayName;
				
			} else {
				// Description was already the default booking description
				$_SESSION['AddCreateBookingError'] = "This is already your default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['AddCreateBookingError'] = "You have no default display name. You can set one in your user information.";
			$_SESSION['AddCreateBookingInfoArray']['BookedBy'] = "";
		}		
	}
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}

// If user wants to get their default booking description
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['AddCreateBookingInfoArray']['UserDefaultBookingDescription'];
	if(isset($_SESSION['AddCreateBookingInfoArray'])){
		
		rememberAddCreateBookingInputs();

		if($bookingDescription != ""){
			if($bookingDescription != $_SESSION['AddCreateBookingInfoArray']['BookingDescription']){
				$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = $bookingDescription;
		
			} else {
				// Description was already the default booking description
				$_SESSION['AddCreateBookingError'] = "This is already your default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['AddCreateBookingError'] = "You have no default booking description. You can set one in your user information.";
			$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = "";
		}
	}
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to increase the start timer by minimum allowed time (e.g. to the closest 15 min chunk)
if(isset($_POST['add']) AND $_POST['add'] == "Increase Start By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddCreateBookingInputs();
	
	$startTime = $_SESSION['AddCreateBookingInfoArray']['StartTime'];
	$correctStartTime = correctDatetimeFormat($startTime);
	$newStartTime = convertDatetimeToFormat(getNextValidBookingEndTime($correctStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$_SESSION['AddCreateBookingInfoArray']['StartTime'] = $newStartTime;
	
	if($_SESSION['AddCreateBookingInfoArray']['StartTime'] >= $_SESSION['AddCreateBookingInfoArray']['EndTime']){
		$newCorrectStartTime = correctDatetimeFormat($newStartTime);
		$newEndTime = convertDatetimeToFormat(getNextValidBookingEndTime($newCorrectStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$_SESSION['AddCreateBookingInfoArray']['EndTime'] = $newEndTime;
	}
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to increase the end timer by minimum allowed time (e.g. 15 min)
if(isset($_POST['add']) AND $_POST['add'] == "Increase End By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddCreateBookingInputs();
	
	$endTime = $_SESSION['AddCreateBookingInfoArray']['EndTime'];
	$correctEndTime = correctDatetimeFormat($endTime);
	$_SESSION['AddCreateBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}

// If user wants to change the values back to the original values
if (isset($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddCreateBookingInfoArray'] = $_SESSION['AddCreateBookingOriginalInfoArray'];
	unset($_SESSION['AddCreateBookingSelectedACompany']);
	unset($_SESSION['AddCreateBookingChangeUser']);
	unset($_SESSION['AddCreateBookingSelectedNewUser']);
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isset($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();		
}

// If user wants to leave the page and be directed back to the booking page again
if (isset($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['normalBookingFeedback'] = "You cancelled your new booking.";
}

// ADD BOOKING CODE SNIPPET // END //


// EDIT BOOKING CODE SNIPPET // START //

// if user wants to edit a booking, we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isset($_SESSION['refreshEditCreateBooking']) AND $_SESSION['refreshEditCreateBooking']))
{
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshEditCreateBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshEditCreateBooking']);	
		
	} else {
		// Get information from database again on the selected booking
		if(!isset($_SESSION['EditCreateBookingMeetingRoomsArray'])){
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				
				// Get booking information
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
										'meetingRoomID' => $row['meetingRoomID'],
										'meetingRoomName' => $row['name']
										);
				}		
				
				$_SESSION['EditCreateBookingMeetingRoomsArray'] = $meetingroom;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching meeting room details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();		
			}
		}
		
		if(!isset($_SESSION['EditCreateBookingInfoArray'])){
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				
				// Get booking information
				$pdo = connect_to_db();
				$sql = "SELECT 		b.`bookingID`									AS TheBookingID,
									b.`companyID`									AS TheCompanyID,
									b.`meetingRoomID`								AS TheMeetingRoomID,
									b.startDateTime 								AS StartTime, 
									b.endDateTime 									AS EndTime, 
									b.description 									AS BookingDescription,
									b.displayName 									AS BookedBy,
									(	
										SELECT `name` 
										FROM `company` 
										WHERE `companyID` = TheCompanyID
									)												AS BookedForCompany,
									b.`cancellationCode`							AS CancellationCode,
									m.`name` 										AS BookedRoomName,									
									u.`userID`										AS TheUserID, 
									u.`firstName`									AS UserFirstname,
									u.`lastName`									AS UserLastname,
									u.`email`										AS UserEmail,
									u.`displayName` 								AS UserDefaultDisplayName,
									u.`bookingDescription`							AS UserDefaultBookingDescription
						FROM 		`booking` b 
						LEFT JOIN 	`meetingroom` m 
						ON 			b.meetingRoomID = m.meetingRoomID 
						LEFT JOIN 	`company` c 
						ON 			b.CompanyID = c.CompanyID
						LEFT JOIN 	`user` u
						ON 			b.`userID` = u.`userID`
						WHERE 		b.`bookingID` = :BookingID
						GROUP BY 	b.`bookingID`";
				$s = $pdo->prepare($sql);
				$s->bindValue(':BookingID', $_POST['id']);
				$s->execute();
								
				//Close connection
				$pdo = null;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching booking details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();		
			}
			
			// Create an array with the row information we retrieved
			$_SESSION['EditCreateBookingInfoArray'] = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditCreateBookingOriginalInfoArray'] = $_SESSION['EditCreateBookingInfoArray'];
		}	
		
	}

	// Set the correct information on form call
	$SelectedUserID = $_SESSION['EditCreateBookingInfoArray']['TheUserID'];	
	
		// Check if we need a company select for the booking
	try
	{		
		// We want the companies the user works for to decide if we need to
		// have a dropdown select or just a fixed value (with 0 or 1 company)
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT	c.`companyID`,
						c.`name` 					AS companyName
				FROM 	`user` u
				JOIN 	`employee` e
				ON 		e.userID = u.userID
				JOIN	`company` c
				ON 		c.companyID = e.companyID
				WHERE 	u.`userID` = :userID
				AND 	c.`isActive` = 1';
			
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $SelectedUserID);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll();
			
		foreach($result as $row){		
			// Get the companies the user works for
			// This will be used to create a dropdown list in HTML
			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName']
								);
		}
			
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isset($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['EditCreateBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
				unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
				$_SESSION['EditCreateBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditCreateBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company
			
			$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
			unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
			$_SESSION['EditCreateBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditCreateBookingInfoArray']['BookedForCompany'] = "";
		}		
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching user details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();					
	}
	
	// Set the correct information
	$row = $_SESSION['EditCreateBookingInfoArray'];
	$original = $_SESSION['EditCreateBookingOriginalInfoArray'];

	// Changed company
	if(isset($company)){
		foreach($company AS $cmp){
			if($cmp['companyID'] == $row['TheCompanyID']){
				$row['BookedForCompany'] = $cmp['companyName'];
				break;
			}
		}			
	}
	
		// Edited inputs
	$bookingID = $row['TheBookingID'];
	$companyName = $row['BookedForCompany'];
	$selectedCompanyID = $row['TheCompanyID'];
	$companyID = $row['TheCompanyID'];
	//	userID has been set earlier
	$meetingroom = $_SESSION['EditCreateBookingMeetingRoomsArray'];
	$selectedMeetingRoomID = $row['TheMeetingRoomID'];	
	$displayName = $row['BookedBy'];
	$description = $row['BookingDescription'];
	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];
		// Original values	
	$originalStartDateTime = $original['StartTime'];
	$originalEndDateTime = $original['EndTime'];
	if(!validateDatetimeWithFormat($originalStartDateTime, DATETIME_DEFAULT_FORMAT_TO_DISPLAY)){
		$originalStartDateTime = convertDatetimeToFormat($originalStartDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	if(!validateDatetimeWithFormat($originalEndDateTime, DATETIME_DEFAULT_FORMAT_TO_DISPLAY)){
		$originalEndDateTime = convertDatetimeToFormat($originalEndDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}	
	if($original['BookedForCompany']!=NULL){
		$originalCompanyName = $original['BookedForCompany'];
	}
	$originalMeetingRoomName = $original['BookedRoomName'];
	if(!isset($originalMeetingRoomName) OR $originalMeetingRoomName == NULL OR $originalMeetingRoomName == ""){
		$originalMeetingRoomName = "N/A - Deleted";	
	}
	$originalDisplayName = $original['BookedBy'];
	$originalBookingDescription = $original['BookingDescription'];
	$originalUserInformation = 	$original['UserLastname'] . ', ' . $original['UserFirstname'] . 
								' - ' . $original['UserEmail'];
	if(!isset($originalUserInformation) OR $originalUserInformation == NULL OR $originalUserInformation == ",  - "){
		$originalUserInformation = "N/A - Deleted";	
	}	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual form we want to use
	include 'editbooking.html.php';
	exit();
}

// If user wants to update the booking information after editing
if(isset($_POST['edit']) AND $_POST['edit'] == "Finish Edit")
{
	// Validate user inputs
	list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname, $bookingCode) = validateUserInputs('EditCreateBookingError');
	
	// TO-DO: get user info from booking code
	
	if($invalidInput){
		
		rememberEditCreateBookingInputs();
		// Refresh.
		$_SESSION['refreshEditCreateBooking'] = TRUE;	

		header('Location: .');
		exit();			
	}
	
	// Check if any values actually changed. If not, we don't need to bother the database
	$numberOfChanges = 0;
	$originalValue = $_SESSION['EditCreateBookingOriginalInfoArray'];
	
	if($_POST['companyID'] != $originalValue['TheCompanyID']){
		$numberOfChanges++;
	}
	if($dspname != $originalValue['BookedBy']){
		$numberOfChanges++;
	}	
	if($bknDscrptn != $originalValue['BookingDescription']){
		$numberOfChanges++;
	}

	if($numberOfChanges == 0){
		// There were no changes made. Go back to booking overview
		$_SESSION['normalBookingFeedback'] = "No changes were made to the booking.";
		header('Location: .');
		exit();	
	}

	// Set correct companyID
	if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
		$_POST['companyID'] != ''){
		$companyID = $_POST['companyID'];
	} else {
		$companyID = NULL;
	}
	
	// Update booking information because values have changed!
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "UPDATE	`booking`
				SET 	`companyID` = :companyID,
						`displayName` = :displayName,
						`description` = :description
				WHERE	`bookingID` = :BookingID";
		$s = $pdo->prepare($sql);
		
		$s->bindValue(':BookingID', $_POST['bookingID']);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $dspname);
		$s->bindValue(':description', $bknDscrptn);
	
		$s->execute();
		
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error updating booking information in the database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
		
	$_SESSION['normalBookingFeedback'] .= "Successfully updated the booking information!";
	
	clearEditCreateBookingSessions();
	
	// Load booking history list webpage with the updated booking information
	header('Location: .');
	exit();	
}

//	User wants to change the company the booking is for (after having already selected it)
if(isset($_POST['edit']) AND $_POST['edit'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['EditCreateBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditCreateBookingInputs();
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// User confirms what company he wants the booking to be for.
if(isset($_POST['edit']) AND $_POST['edit'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditCreateBookingInputs();
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If user wants to get their default display name
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Display Name"){

	$displayName = $_SESSION['EditCreateBookingOriginalInfoArray']['UserDefaultBookingDescription'];
	if(isset($_SESSION['EditCreateBookingInfoArray'])){
		
		rememberEditCreateBookingInputs();

		if($displayName != ""){
			if($displayName != $_SESSION['EditCreateBookingInfoArray']['BookedBy']){
					// The user selected
				$_SESSION['EditCreateBookingInfoArray']['BookedBy'] = $displayName;

				unset($_SESSION['EditCreateBookingDefaultDisplayNameForNewUser']);				
			} else {
				// Description was already the default booking description
				$_SESSION['EditCreateBookingError'] = "This is already the user's default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['EditCreateBookingError'] = "This user has no default display name.";
			$_SESSION['EditCreateBookingInfoArray']['BookedBy'] = "";
		}		
	}
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If user wants to get their default booking description
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['EditCreateBookingOriginalInfoArray']['UserDefaultDisplayName'];
	if(isset($_SESSION['EditCreateBookingInfoArray'])){
		
		rememberEditCreateBookingInputs();

		if($bookingDescription != ""){
			if($bookingDescription != $_SESSION['EditCreateBookingInfoArray']['BookingDescription']){
				
					// Set the default booking description
				$_SESSION['EditCreateBookingInfoArray']['BookingDescription'] = $bookingDescription;
	
				unset($_SESSION['EditCreateBookingDefaultBookingDescriptionForNewUser']);			
			} else {
				// Description was already the default booking description
				$_SESSION['EditCreateBookingError'] = "This is already the user's default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['EditCreateBookingError'] = "This user has no default booking description.";
			$_SESSION['EditCreateBookingInfoArray']['BookingDescription'] = "";
		}
	}
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}

/* TO-DO: Remove comment block if we will allow editing the start/end time for users
// If user wants to increase the start timer by minimum allowed time (e.g. 15 min)
if(isset($_POST['edit']) AND $_POST['edit'] == "Increase Start By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditCreateBookingInputs();
	
	$startTime = $_SESSION['EditCreateBookingInfoArray']['StartTime'];
	$correctStartTime = correctDatetimeFormat($startTime);
	$_SESSION['EditCreateBookingInfoArray']['StartTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	if($_SESSION['EditCreateBookingInfoArray']['StartTime'] == $_SESSION['EditCreateBookingInfoArray']['EndTime']){
		$endTime = $_SESSION['EditCreateBookingInfoArray']['EndTime'];
		$correctEndTime = correctDatetimeFormat($endTime);
		$_SESSION['EditCreateBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If user wants to increase the end timer by minimum allowed time (e.g. 15 min)
if(isset($_POST['edit']) AND $_POST['edit'] == "Increase End By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditCreateBookingInputs();
	
	$endTime = $_SESSION['EditCreateBookingInfoArray']['EndTime'];
	$correctEndTime = correctDatetimeFormat($endTime);
	$_SESSION['EditCreateBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}*/

// If user wants to change the values back to the original values while editing
if (isset($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditCreateBookingInfoArray'] = $_SESSION['EditCreateBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// EDIT BOOKING CODE SNIPPET // END //

// CANCELLATION CODE SNIPPET // START //

// Cancels a booking from a submitted cancellation link
if(isset($_GET['cancellationcode'])){
	
	$cancellationCode = $_GET['cancellationcode'];
		
	// Check if code is correct (64 chars)
	if(strlen($cancellationCode)!=64){
		$_SESSION['normalBookingFeedback'] = "The cancellation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}
		
	//	Check if the submitted code is in the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "SELECT 	`bookingID`,
						`meetingRoomID`									AS TheMeetingRoomID, 
						(
							SELECT	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = TheMeetingRoomID 
						)												AS TheMeetingRoomName,
						`startDateTime`,
						`endDateTime`,
						`actualEndDateTime`
				FROM	`booking`
				WHERE 	`cancellationCode` = :cancellationCode
				AND		`dateTimeCancelled` IS NULL
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error validating cancellation code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Check if the select even found something
	$rowCount = $s->rowCount();
	if($rowCount == 0){
		// No match.
		$_SESSION['normalBookingFeedback'] = "The cancellation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}

	$result = $s->fetch(PDO::FETCH_ASSOC);
	
	$bookingID = $result['bookingID'];
	$TheMeetingRoomName = $result['TheMeetingRoomName'];
	$startDateTimeString = $result['startDateTime'];
	$endDateTimeString = $result['endDateTime'];
	$actualEndDateTimeString = $result['actualEndDateTime'];
	
	$startDateTime = stringToDateTime($startDateTime, 'Y-m-d H:i:s');
	$endDateTime = stringToDateTime($endDateTime, 'Y-m-d H:i:s');
	
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTimeString , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTimeString, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
	
	// Check if the meeting has already ended
	if($actualEndDateTimeString != "" AND $actualEndDateTimeString != NULL){
		// Meeting has not ended already.
		// Check if we're cancelling the booking or simply ending the booking early!
		$timeNow = getDatetimeNow();
		if($timeNow > $startDateTime AND $timeNow < $endDateTime) {
			// The booking is already active, so we're ending it early
			$sql = "UPDATE 	`booking`
					SET		`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`actualEndDateTime` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL
					WHERE 	`bookingID` = :bookingID";
			$bookingFeedback = 	"The booking for " . $TheMeetingRoomName . ". Starting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " has been ended early by using the cancellation link.";
			$logEventDescription = $bookingFeedback;
		} elseif($timeNow < $startDateTime) {
			// The booking hasn't started yet, so we're actually cancelling the meeting
			$sql = "UPDATE 	`booking`
					SET		`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL
					WHERE 	`bookingID` = :bookingID";	
			$bookingFeedback = 	"The booking for " . $TheMeetingRoomName . ". Starting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " has been cancelled by using the cancellation link.";
			$logEventDescription = $bookingFeedback;								
		} elseif($timeNow > $endDateTime) {
			// The booking has (in theory) already ended, so there shouldn't be an active cancellation code for it
			// We just have to assume the booking failed to update itself on completion
			$sql = "UPDATE 	`booking`
					SET		`actualEndDateTime` = `endDateTime`
							`cancellationCode` = NULL
					WHERE 	`bookingID` = :bookingID";
			$bookingFeedback = 		"The booked meeting has already ended.";
			$logEventDescription = 	"The booking for " . $TheMeetingRoomName . ". Starting at: " . $displayValidatedStartDate . 
									" and ending at: " . $displayValidatedEndDate . " was attempted to be cancelled with the " . 
									"cancellation link, but the meeting should have already been completed." .
									" The end date of the booking has been updated to have occured on the scheduled time.";			
		}	
	} else {
		// Meeting has already ended. So there's no reason to cancel it.
		$bookingFeedback = 	"The booked meeting has already ended.";
		$sql = "UPDATE 	`booking`
				SET		`cancellationCode` = NULL
				WHERE 	`bookingID` = :bookingID";
		$bookingFeedback = 		"The booked meeting has already ended.";
		$logEventDescription = 	"The booking for " . $TheMeetingRoomName . ". Starting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " was attempted to be cancelled with the " . 
								"cancellation link, but the meeting had already ended so it had no effect.";
	}
	
	// Update the booked meeting
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$s = $pdo->prepare($sql);
		$s->bindValue(':bookingID', $bookingID);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error updating booking: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	
	$_SESSION['normalBookingFeedback'] = $bookingFeedback;
										
	// Add a log event about the updated booking
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent`
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Cancelled'
											),
							`description` = :description,
							`bookingID` = :bookingID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->bindValue(':bookingID', $bookingID);
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
}

// CANCELLATION CODE SNIPPET // END //

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddCreateBookingSessions();
clearEditCreateBookingSessions();
unset($_SESSION["cancelBookingOriginalValues"]);
unset($_SESSION["confirmOrigins"]);
unset($_SESSION["EditCreateBookingError"]);

if(isset($refreshBookings) AND $refreshBookings) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshBookings);
}

// Display relevant booked meetings
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	if(isset($_GET['meetingroom']) AND $_GET['meetingroom'] != NULL AND $_GET['meetingroom'] != ""){
		$sql = "SELECT 		b.`bookingID`,
							b.`companyID`,
							m.`name` 										AS BookedRoomName, 
							b.startDateTime 								AS StartTime,
							b.endDateTime									AS EndTime, 
							b.displayName 									AS BookedBy,
							(	
								SELECT 	`name` 
								FROM 	`company` 
								WHERE 	`companyID` = b.`companyID`
							)												AS BookedForCompany,
							u.firstName, 
							u.lastName, 
							u.email, 
							GROUP_CONCAT(c.`name` separator ', ') 			AS WorksForCompany, 
							b.description 									AS BookingDescription, 
							b.dateTimeCreated 								AS BookingWasCreatedOn, 
							b.actualEndDateTime								AS BookingWasCompletedOn, 
							b.dateTimeCancelled								AS BookingWasCancelledOn 
				FROM 		`booking` b 
				LEFT JOIN 	`meetingroom` m 
				ON 			b.meetingRoomID = m.meetingRoomID 
				LEFT JOIN 	`user` u 
				ON 			u.userID = b.userID 
				LEFT JOIN 	`employee` e 
				ON 			e.UserID = u.userID 
				LEFT JOIN 	`company` c 
				ON 			c.CompanyID = e.CompanyID
				WHERE		b.`meetingRoomID` = :meetingRoomID
				GROUP BY 	b.bookingID
				ORDER BY 	UNIX_TIMESTAMP(b.startDateTime)
				ASC";
		$s = $pdo->prepare($sql);
		$s->bindValue(':meetingRoomID', $_GET['meetingroom']);
		$s->execute();
		$result = $s->fetchAll();
		$rowNum = sizeOf($result);	
	} elseif(!isset($_GET['meetingroom'])){
		$sql = "SELECT 		b.`bookingID`,
							b.`companyID`,
							m.`name` 										AS BookedRoomName, 
							b.startDateTime 								AS StartTime,
							b.endDateTime									AS EndTime, 
							b.displayName 									AS BookedBy,
							(	
								SELECT 	`name` 
								FROM 	`company` 
								WHERE 	`companyID` = b.`companyID`
							)												AS BookedForCompany,
							u.firstName, 
							u.lastName, 
							u.email, 
							GROUP_CONCAT(c.`name` separator ', ') 			AS WorksForCompany, 
							b.description 									AS BookingDescription, 
							b.dateTimeCreated 								AS BookingWasCreatedOn, 
							b.actualEndDateTime								AS BookingWasCompletedOn, 
							b.dateTimeCancelled								AS BookingWasCancelledOn 
				FROM 		`booking` b 
				LEFT JOIN 	`meetingroom` m 
				ON 			b.meetingRoomID = m.meetingRoomID 
				LEFT JOIN 	`user` u 
				ON 			u.userID = b.userID 
				LEFT JOIN 	`employee` e 
				ON 			e.UserID = u.userID 
				LEFT JOIN 	`company` c 
				ON 			c.CompanyID = e.CompanyID
				GROUP BY 	b.bookingID
				ORDER BY 	UNIX_TIMESTAMP(b.startDateTime)
				ASC";
		$result = $pdo->query($sql);
		$rowNum = $result->rowCount();
	}

	//Close the connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching booking information from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

foreach ($result as $row)
{
	$datetimeNow = getDatetimeNow();
	$startDateTime = $row['StartTime'];	
	$endDateTime = $row['EndTime'];
	$completedDateTime = $row['BookingWasCompletedOn'];
	$dateOnlyNow = convertDatetimeToFormat($datetimeNow, 'Y-m-d H:i:s', 'Y-m-d');
	$dateOnlyCompleted = convertDatetimeToFormat($completedDateTime,'Y-m-d H:i:s','Y-m-d');
	$dateOnlyStart = convertDatetimeToFormat($startDateTime,'Y-m-d H:i:s','Y-m-d');
	$cancelledDateTime = $row['BookingWasCancelledOn'];
	$createdDateTime = $row['BookingWasCreatedOn'];	
	
	// Describe the status of the booking based on what info is stored in the database
	// If not finished and not cancelled = active
	// If meeting time has passed and finished time has updated (and not been cancelled) = completed
	// If cancelled = cancelled
	// If meeting time has passed and finished time has NOT updated (and not been cancelled) = Ended without updating
	// If none of the above = Unknown
	if(			$completedDateTime == null AND $cancelledDateTime == null AND 
				$datetimeNow < $endDateTime AND $dateOnlyNow != $dateOnlyStart) {
		$status = 'Active';
		// Valid status
	} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
				$datetimeNow < $endDateTime AND $dateOnlyNow == $dateOnlyStart){
		$status = 'Active Today';
		// Valid status		
	} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
				$dateOnlyNow != $dateOnlyCompleted){
		$status = 'Completed';
		// Valid status
	} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
				$dateOnlyNow == $dateOnlyCompleted){
		$status = 'Completed Today';
		// Valid status
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
				$startDateTime > $cancelledDateTime){
		$status = 'Cancelled';
		// Valid status
	} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
				$completedDateTime > $cancelledDateTime ){
		$status = 'Ended Early';
		// Valid status
	} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
				$completedDateTime < $cancelledDateTime ){
		$status = 'Cancelled after Completion';
		// This should not be allowed to happen eventually
	} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
				$datetimeNow > $endDateTime){
		$status = 'Ended without updating database';
		// This should never occur
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND 
				$endDateTime < $cancelledDateTime){
		$status = 'Cancelled after meeting should have been Completed';
		// This should not be allowed to happen eventually
	} else {
		$status = 'Unknown';
		// This should never occur
	}
	
	$roomName = $row['BookedRoomName'];
	$firstname = $row['firstName'];
	$lastname = $row['lastName'];
	$email = $row['email'];
	$userinfo = $lastname . ', ' . $firstname . ' - ' . $row['email'];
	$worksForCompany = $row['WorksForCompany'];
	if(!isset($roomName) OR $roomName == NULL OR $roomName == ""){
		$roomName = "N/A - Deleted";
	}
	if(!isset($userinfo) OR $userinfo == NULL OR $userinfo == ",  - "){
		$userinfo = "N/A - Deleted";	
	}
	if(!isset($email) OR $email == NULL OR $email == ""){
		$firstname = "N/A - Deleted";
		$lastname = "N/A - Deleted";
		$email = "N/A - Deleted";		
	}
	if(!isset($worksForCompany) OR $worksForCompany == NULL OR $worksForCompany == ""){
		$worksForCompany = "N/A";
	}
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayCompletedDateTime = convertDatetimeToFormat($completedDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayCancelledDateTime = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
	$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	$meetinginfo = $roomName . ' for the timeslot: ' . $displayValidatedStartDate . 
					' to ' . $displayValidatedEndDate;
	
	if($status == "Active Today"){				
		$bookingsActiveToday[] = array('id' => $row['bookingID'],
							'BookingStatus' => $status,
							'BookedRoomName' => $roomName,
							'StartTime' => $displayValidatedStartDate,
							'EndTime' => $displayValidatedEndDate,
							'BookedBy' => $row['BookedBy'],
							'BookedForCompany' => $row['BookedForCompany'],
							'BookingDescription' => $row['BookingDescription'],
							'firstName' => $firstname,
							'lastName' => $lastname,
							'email' => $email,
							'WorksForCompany' => $worksForCompany,
							'BookingWasCreatedOn' => $displayCreatedDateTime,
							'BookingWasCompletedOn' => $displayCompletedDateTime,
							'BookingWasCancelledOn' => $displayCancelledDateTime,	
							'UserInfo' => $userinfo,
							'MeetingInfo' => $meetinginfo
						);
	}	elseif($status == "Active") {
		$bookingsFuture[] = array('id' => $row['bookingID'],
							'BookingStatus' => $status,
							'BookedRoomName' => $roomName,
							'StartTime' => $displayValidatedStartDate,
							'EndTime' => $displayValidatedEndDate,
							'BookedBy' => $row['BookedBy'],
							'BookedForCompany' => $row['BookedForCompany'],
							'BookingDescription' => $row['BookingDescription'],
							'firstName' => $firstname,
							'lastName' => $lastname,
							'email' => $email,
							'WorksForCompany' => $worksForCompany,
							'BookingWasCreatedOn' => $displayCreatedDateTime,
							'BookingWasCompletedOn' => $displayCompletedDateTime,
							'BookingWasCancelledOn' => $displayCancelledDateTime,	
							'UserInfo' => $userinfo,
							'MeetingInfo' => $meetinginfo
						);		
	}
}
var_dump($_SESSION); // TO-DO: remove after testing is done
// Load the html template
include_once 'booking.html.php';
?>