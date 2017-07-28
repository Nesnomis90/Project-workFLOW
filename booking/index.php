<?php 
// This is the index file for the booking folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	$gotoPage = ".";
	userIsLoggedIn();
}

/*
	TO-DO:
		Make log in work properly
		Make Edit booking work (Fullført?) (needs testing)
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
	unset($_SESSION['AddCreateBookingCompanyArray']);
	unset($_SESSION['AddCreateBookingStartImmediately']);
	
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
	unset($_SESSION['EditCreateBookingSelectACompany']);
	unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
	unset($_SESSION['EditCreateBookingLoggedInUserInformation']);
	
	unset($_SESSION["EditCreateBookingOriginalBookingID"]);

	unset($_SESSION['cancelBookingOriginalValues']);
}

// Function to remember the user inputs in Edit Booking
function rememberEditCreateBookingInputs(){
	if(isSet($_SESSION['EditCreateBookingInfoArray'])){
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
	if(isSet($_SESSION['AddCreateBookingInfoArray'])){
		$newValues = $_SESSION['AddCreateBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isSet($_POST['userID'])){
			$newValues['TheUserID'] = $_POST['userID'];
		}
			// The meeting room selected
		if(isSet($_GET['meetingroom'])){
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
	if(isSet($_SESSION['LoggedInUserID'])){
		$SelectedUserID = $_SESSION['LoggedInUserID'];
	}
	if(isSet($_SESSION['bookingCodeUserID'])){
		$SelectedUserID = $_SESSION['bookingCodeUserID'];
	}

	// Make sure user is logged in before going further
		// If local, use booking code
	if(!isSet($SelectedUserID)){
		if(isSet($_SESSION['DefaultMeetingRoomInfo'])){
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

	if(isSet($_POST['UserID']) AND $_POST['UserID'] != $_SESSION['LoggedInUserID']){
		if(isSet($_POST['sendEmail']) AND $_POST['sendEmail'] == 1){
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
				$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
			}
			
			$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing
		} elseif(isSet($_POST['sendEmail']) AND $_POST['sendEmail'] == 0) {
			$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent Email.";
		}
	} else {
		$_SESSION['normalBookingFeedback'] .= "\nDid not send an email because you cancelled your own meeting.";
	}
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse, $editing){
	// Get user inputs
	$invalidInput = FALSE;
	$usingBookingCode = FALSE;
	if(!$editing){
		if(isSet($_POST['startDateTime']) AND !$invalidInput){
			$startDateTimeString = $_POST['startDateTime'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting a start time.";
		}
		if(isSet($_POST['endDateTime']) AND !$invalidInput){
			$endDateTimeString = $_POST['endDateTime'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting an end time.";
		}
	}
	
	if(isSet($_POST['displayName'])){
		$displayNameString = $_POST['displayName'];
	} else {
		$displayNameString = '';
	}
	if(isSet($_POST['description'])){
		$bookingDescriptionString = $_POST['description'];
	} else {
		$bookingDescriptionString = '';
	}
	
	if($usingBookingCode){
		if(isSet($_POST['bookingCode'])){
			$bookingCode = $_POST['bookingCode'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be created without submitting a booking code.";		
		}
	}
	
	// Remove excess whitespace and prepare strings for validation
	if(!$editing){
		$validatedStartDateTime = trimExcessWhitespace($startDateTimeString);
		$validatedEndDateTime = trimExcessWhitespace($endDateTimeString);
	}
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	if($usingBookingCode){
		$validatedBookingCode = trimAllWhitespace($bookingCode);
	} else {
		$validatedBookingCode = "";
	}
	
	// Do actual input validation
	if(!$editing){
		if(validateDateTimeString($validatedStartDateTime) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "Your submitted start time has illegal characters in it.";
		}
		if(validateDateTimeString($validatedEndDateTime) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "Your submitted end time has illegal characters in it.";
		}
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
	if(!$editing){
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
	}
	if($usingBookingCode){
		if(isSet($validatedBookingCode) AND $validatedBookingCode == "" AND !$invalidInput){
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
	if(!$editing){
		$startDateTime = correctDatetimeFormat($validatedStartDateTime);
		$endDateTime = correctDatetimeFormat($validatedEndDateTime);

		if (isSet($startDateTime) AND $startDateTime === FALSE AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "The start date you submitted did not have a correct format. Please try again.";
			$invalidInput = TRUE;
		}
		if (isSet($endDateTime) AND $endDateTime === FALSE AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "The end date you submitted did not have a correct format. Please try again.";
			$invalidInput = TRUE;
		}	
		
		$timeNow = getDatetimeNow();
	
		if($startDateTime > $endDateTime AND !$invalidInput){
			// End time can't be before the start time
			
			$_SESSION[$FeedbackSessionToUse] = "The start time can't be later than the end time. Please select a new start time or end time.";
			$invalidInput = TRUE;
		}
	
		if($startDateTime < $timeNow AND !$invalidInput AND !isSet($_SESSION['AddCreateBookingStartImmediately'])){
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
		if(!isSet($_SESSION['AddCreateBookingStartImmediately']) AND !$invalidInput){
			$invalidStartTime = isBookingDateTimeMinutesInvalid($startDateTime);
			if($invalidStartTime AND !$usingBookingCode){
				$_SESSION[$FeedbackSessionToUse] = "Your start time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
				$invalidInput = TRUE;	
			}			
		}
		if(!$invalidInput){
			$invalidEndTime = isBookingDateTimeMinutesInvalid($endDateTime);
			if($invalidEndTime){
				$_SESSION[$FeedbackSessionToUse] = "Your end time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
				$invalidInput = TRUE;	
			}			
		}
		
		// We want to check if the booking is the correct minimum length
			// Does not apply to booking with booking code (starts immediately until next/selected chunk
		if(!isSet($_SESSION['AddCreateBookingStartImmediately']) AND !$invalidInput){
			$invalidBookingLength = isBookingTimeDurationInvalid($startDateTime, $endDateTime);
			if($invalidBookingLength AND !$usingBookingCode){
				$_SESSION[$FeedbackSessionToUse] = "Your start time and end time needs to have at least a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes difference.";
				$invalidInput = TRUE;		
			}		
		}
		
		return array($invalidInput, $startDateTime, $endDateTime, $validatedBookingDescription, $validatedDisplayName, $validatedBookingCode);
	} else {
		return array($invalidInput, $validatedBookingDescription, $validatedDisplayName, $validatedBookingCode);
	}
}

// Check if we're accessing from a local device
// If so, set that meeting room's info as the default meeting room info
checkIfLocalDevice();

// If user wants to go back to the main page while in the confirm booking page
if (isSet($_POST['action']) and $_POST['action'] == 'Go Back'){
	
	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to go back to the main page while editing
if (isSet($_POST['edit']) and $_POST['edit'] == 'Go Back'){
	
	$_SESSION['normalBookingFeedback'] = "You cancelled your booking editing.";
	
	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to refresh the page to get the most up-to-date information
if (isSet($_POST['action']) and $_POST['action'] == 'Refresh'){
	
	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;
		if(isSet($_GET['name'])){
			$name = $_GET['name'];
			$location .= "&name=" . $name;	
		}		
	} else {
		$location = ".";
	}

	header("Location: $location");
	exit();
}

// If user wants to cancel a scheduled booked meeting
if (	(isSet($_POST['action']) and $_POST['action'] == 'Cancel') OR 
		(isSet($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking']))
{
	if(isSet($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking']){
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
	
	// Check if selected user ID is creator of booking, owner of the company it's booked for or an admin
	$continueCancel = FALSE;
	$cancelledByOwner = FALSE;
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
				WHERE 		b.`bookingID` = :BookingID
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':BookingID', $bookingID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		if(isSet($row) AND $row['HitCount'] > 0){
			$bookingCreatorUserID = $row['userID'];
			$bookingCreatorUserEmail = $row['UserEmail'];
			$bookingCreatorUserInfo = $row['lastName'] . ", " . $row['firstName'] . " - " . $row['UserEmail'];
			if(isSet($bookingCreatorUserID) AND !empty($bookingCreatorUserID) AND $bookingCreatorUserID == $SelectedUserID){
				$continueCancel = TRUE;
			}
		} 
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

		// Check if the user is an owner of the company the booking is booked for
	if(!$continueCancel) {	
		try
		{
			$sql = 'SELECT 		COUNT(*)		AS HitCount,
								b.`userID`,
								u.`email`		AS UserEmail,
								u.`firstName`,
								u.`lastName`
					FROM		`booking` b
					INNER JOIN	`employee` e
					ON			e.`CompanyID` = b.`CompanyID`
					INNER JOIN 	`user` u
					ON 			e.`userID` = u.`userID`
					INNER JOIN	`companyposition` cp
					ON			cp.`PositionID` = e.`PositionID`
					WHERE 		b.`bookingID` = :BookingID
					AND			e.`UserID` = :UserID
					AND			cp.`name` = "Owner"
					LIMIT 		1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':BookingID', $bookingID);
			$s->bindValue(':UserID', $SelectedUserID);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);

			if(isSet($row) AND $row['HitCount'] > 0){
				$cancelledByUserID = $row['userID'];
				$cancelledByUserEmail = $row['UserEmail'];
				$cancelledByUserInfo = $row['lastName'] . ", " . $row['firstName'] . " - " . $row['UserEmail'];
				$continueCancel = TRUE;
				$cancelledByOwner = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
	}

		// Check if the user is an admin
		// Only needed if the the user isn't the creator of the booking or an owner
	if(!$continueCancel) {
		try
		{
			$sql = 'SELECT 		COUNT(*) 	AS HitCount	
					FROM		`user` u
					INNER JOIN	`accesslevel` a
					ON 			u.`AccessID` = a.`AccessID`
					WHERE 		u.`userID` = :userID
					AND			a.`AccessName` = "Admin"
					LIMIT		1';
					
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $SelectedUserID);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);
			if(isSet($row) AND $row['HitCount'] > 0){
				$continueCancel = TRUE;
				$cancelledByAdmin = TRUE;
			}

		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}		
	}

	if($continueCancel === FALSE){
		$pdo = null;
		$_SESSION['normalBookingFeedback'] = "You cannot cancel this booked meeting.";
		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = '.';
		}
		header('Location: ' . $location);
		exit();				
	}
	
	// Only cancel if booking is currently active
	if(	isSet($bookingStatus) AND  
		($bookingStatus == 'Active' OR $bookingStatus == 'Active Today')){
		// Update cancellation date for selected booked meeting in database
		try
		{
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
			if(isSet($_SESSION['LoggedInUserName'])){
				$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
			}
			if(isSet($_SESSION["AddCreateBookingInfoArray"])){
				$nameOfUserWhoBooked = $_SESSION["AddCreateBookingInfoArray"]["UserLastname"] . ', ' . $_SESSION["AddCreateBookingInfoArray"]["UserFirstname"];
			}			
			
			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($bookingCreatorUserInfo) AND isSet($bookingMeetingInfo)){
				$logEventDescription = 'The booking made for ' . $bookingCreatorUserInfo . ' for the meeting room ' .
				$bookingMeetingInfo . ' was cancelled by: ' . $nameOfUserWhoBooked;
			} else {
				$logEventDescription = 'A booking was cancelled by: ' . $nameOfUserWhoBooked;
			}

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
		if($cancelledByAdmin OR $cancelledByOwner){
			$_SESSION['cancelBookingOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;
			emailUserOnCancelledBooking();
		}
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['normalBookingFeedback'] = "Meeting has already ended. Did not cancel it.";
	}

	unset($_SESSION['cancelBookingOriginalValues']);	
	// Load booked meetings list webpage with updated database
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['action']) AND $_POST['action'] == "confirmcode"){

	// Check if any of the old guesses are old enough to remove
	if(isSet($_SESSION['bookingCodeGuesses'])){
		$dateTimeNow = getDatetimeNow();
		$newArray = array();
		for($i=0; $i < sizeOf($_SESSION['bookingCodeGuesses']); $i++){
			$startDateTime = $_SESSION['bookingCodeGuesses'][$i];
			
			$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);		
			if($timeDifference < BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES){
				$newArray[] = $_SESSION['bookingCodeGuesses'][$i];
			}
		}
		if(sizeOf($newArray) > 0){
			$_SESSION['bookingCodeGuesses'] = $newArray;
		} else {
			unset($_SESSION['bookingCodeGuesses']);
		}
	}
	// Limit the amount of tries the session has to guess a booking code.
	if(isSet($_SESSION['bookingCodeGuesses']) AND (sizeOf($_SESSION['bookingCodeGuesses']) >= MAXIMUM_BOOKING_CODE_GUESSES)){
		
		$startDateTime = $_SESSION['bookingCodeGuesses'][0];
		
		$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);
		$timeRemaining = floor(BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES - $timeDifference);
		if($timeRemaining > 0){
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect booking code too many times.\nYou can try again in $timeRemaining minute(s).";
		} else {
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect booking code too many times.\nYou can try again in less than a minute.";
		}
		
		var_dump($_SESSION); // TO-DO: remove after testing is done
		include_once 'bookingcode.html.php';
		exit();		
	}
	
	$bookingCode = trim($_POST['bookingCode']);
	$validatedBookingCode = trimAllWhitespace($bookingCode);
	if(validateIntegerNumber($validatedBookingCode) !== TRUE){
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted had non-numbers in it.";
		var_dump($_SESSION); // TO-DO: remove after testing is done
		include_once 'bookingcode.html.php';
		exit();
	}
	
	$invalidBookingCode = isNumberInvalidBookingCode($validatedBookingCode);
	if($invalidBookingCode === TRUE){
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an invalid code.";
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
			if($_SESSION['confirmOrigins'] == "Edit Meeting"){
				$_SESSION['refreshEditCreateBooking'] = TRUE;
				unset($_SESSION['confirmOrigins']);
			}
			
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$meetingRoomID = $_POST['meetingRoomID'];
				$location = '.';
			}
			header('Location: ' . $location);
			exit();						
		} else {
			// Remember last datetime we guessed wrong
			$_SESSION['bookingCodeGuesses'][] = getDatetimeNow();
			$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an incorrect code.";
		
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
if(	((isSet($_POST['action']) AND $_POST['action'] == 'Create Meeting')) OR
	(isSet($_SESSION['refreshAddCreateBooking']) AND $_SESSION['refreshAddCreateBooking']))
{
	// Confirm that we've reset.
	unset($_SESSION['refreshAddCreateBooking']);
	
	$_SESSION['confirmOrigins'] = "Create Meeting";
	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
	unset($_SESSION['confirmOrigins']);

	if(!isSet($_SESSION['AddCreateBookingInfoArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			// Get the logged in user's default booking information
			$pdo = connect_to_db();

			// New SQL where we require a company connection
			$sql = "SELECT	(
								SELECT COUNT(*)
								FROM	`employee`
								WHERE 	`userID` = :userID
							) AS HitCount,
							`bookingdescription`, 
							`displayname`,
							`firstName`,
							`lastName`,
							`email`,
							`sendEmail`
					FROM 	`user`
					WHERE 	`userID` = :userID
					LIMIT 	1";	
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $SelectedUserID);
			$s->execute();
			
			// Create an array with the row information we retrieved
			$result = $s->fetch(PDO::FETCH_ASSOC);
			
			if($result['HitCount'] == 0){
				// User is not working in a company. We can't let them book
				$_SESSION['normalBookingFeedback'] = "Only users connected to a company can book a meeting.";
				header("Location: .");
				exit();
			}
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
			
			if($result['sendEmail']!=NULL){
				$sendEmail = $result['sendEmail'];
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

	// Get information from database on booking information user can choose between
	if(!isSet($_SESSION['AddCreateBookingMeetingRoomsArray'])){
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
		
		// Create an array with the row information we want to use	
		$_SESSION['AddCreateBookingInfoArray'] = array(
													'TheCompanyID' => '',
													'TheMeetingRoomID' => '',
													'StartTime' => '',
													'EndTime' => '',
													'BookingDescription' => '',
													'BookedBy' => '',
													'BookedForCompany' => '',
													'CreditsRemaining' => 'N/A',
													'PotentialExtraMonthlyTimeUsed' => 'N/A',
													'PotentialCreditsRemaining' => 'N/A',
													'EndDate' => 'N/A',
													'TheUserID' => '',
													'UserFirstname' => '',
													'UserLastname' => '',
													'UserEmail' => '',
													'UserDefaultDisplayName' => '',
													'UserDefaultBookingDescription' => '',
													'sendEmail' => ''
												);			
		$_SESSION['AddCreateBookingInfoArray']['UserDefaultBookingDescription'] = $description;
		$_SESSION['AddCreateBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
		$_SESSION['AddCreateBookingInfoArray']['UserFirstname'] = $firstname;	
		$_SESSION['AddCreateBookingInfoArray']['UserLastname'] = $lastname;	
		$_SESSION['AddCreateBookingInfoArray']['UserEmail'] = $email;	
		$_SESSION['AddCreateBookingInfoArray']['TheUserID'] = $SelectedUserID;
		$_SESSION['AddCreateBookingInfoArray']['sendEmail'] = $sendEmail;
		if(isSet($_GET['meetingroom'])){
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
		$sql = 'SELECT		c.`companyID`,
							c.`name` 					AS companyName,
							c.`endDate`,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = e.`CompanyID`
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							)													AS MonthlyCompanyWideBookingTimeUsed,
															(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = e.`CompanyID`
								AND 		b.`actualEndDateTime` IS NULL
								AND			b.`dateTimeCancelled` IS NULL
								AND			b.`endDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							)													AS PotentialExtraMonthlyCompanyWideBookingTimeUsed,
							(
								SELECT 	IFNULL(cc.`altMinuteAmount`, cr.`minuteAmount`)
								FROM 		`company` c
								INNER JOIN	`companycredits` cc
								ON			c.`CompanyID` = cc.`CompanyID`
								INNER JOIN	`credits` cr
								ON			cr.`CreditsID` = cc.`CreditsID`
								WHERE		c.`CompanyID` = e.`CompanyID`
							) 													AS CreditSubscriptionMinuteAmount
				FROM 		`user` u
				INNER JOIN 	`employee` e
				ON 			e.`userID` = u.`userID`
				INNER JOIN	`company` c
				ON 			c.`companyID` = e.`companyID`
				WHERE 		u.`userID` = :userID
				AND			c.`isActive` = 1';
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s	

		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $SelectedUserID);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($result as $row){
				// Get the companies the user works for
				// This will be used to create a dropdown list in HTML

				// Get booking time used this month
				if(empty($row['MonthlyCompanyWideBookingTimeUsed'])){
					$MonthlyTimeUsed = 'N/A';
				} else {
					$MonthlyTimeUsed = convertTimeToHoursAndMinutes($row['MonthlyCompanyWideBookingTimeUsed']);
				}

				// Get potential booking time used this month
				if(empty($row['PotentialExtraMonthlyCompanyWideBookingTimeUsed'])){
					$potentialExtraMonthlyTimeUsed = 'N/A';
				} else {
					$potentialExtraMonthlyTimeUsed = convertTimeToHoursAndMinutes($row['PotentialExtraMonthlyCompanyWideBookingTimeUsed']);
				}

				// Get credits given
				if(!empty($row["CreditSubscriptionMinuteAmount"])){
					$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
				} else {
					$companyMinuteCredits = 0;
				}

					// Calculate Company Credits Remaining (only includes completed bookings)
				if($MonthlyTimeUsed != "N/A"){
					$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);
					$actualTimeUsedInMinutesThisMonth = $monthlyTimeHour*60 + $monthlyTimeMinute;
					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusCompanyMinuteCreditsRemaining);
					} else {
						$companyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
					}
				} else {
					$companyMinuteCreditsRemaining = $companyMinuteCredits;
					$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
				}

					// Calculate Potential Company Credits Remaining (includes future bookings)
				if($potentialExtraMonthlyTimeUsed == "N/A"){
					$displayPotentialCompanyCreditsRemaining = $displayCompanyCreditsRemaining;
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes(0);
				} elseif($MonthlyTimeUsed != "N/A"){
					$monthlyTimeHour = substr($potentialExtraMonthlyTimeUsed,0,strpos($potentialExtraMonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($potentialExtraMonthlyTimeUsed,strpos($potentialExtraMonthlyTimeUsed,"h")+1,-1);
					$potentialExtraMonthlyTimeUsedInMinutes = $monthlyTimeHour*60 + $monthlyTimeMinute;
					$actualTimeUsedInMinutesThisMonth += $potentialExtraMonthlyTimeUsedInMinutes;

					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusPotentialCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayPotentialCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusPotentialCompanyMinuteCreditsRemaining);
					} else {
						$potentialCompanyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayPotentialCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($potentialCompanyMinuteCreditsRemaining);
					}
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes($potentialExtraMonthlyTimeUsedInMinutes);
				} else {
					$monthlyTimeHour = substr($potentialExtraMonthlyTimeUsed,0,strpos($potentialExtraMonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($potentialExtraMonthlyTimeUsed,strpos($potentialExtraMonthlyTimeUsed,"h")+1,-1);
					$potentialExtraMonthlyTimeUsedInMinutes = $monthlyTimeHour*60 + $monthlyTimeMinute;
					$actualTimeUsedInMinutesThisMonth = $potentialExtraMonthlyTimeUsedInMinutes;

					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusPotentialCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayPotentialCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusPotentialCompanyMinuteCreditsRemaining);
					} else {
						$potentialCompanyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayPotentialCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($potentialCompanyMinuteCreditsRemaining);
					}
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes($potentialExtraMonthlyTimeUsedInMinutes);				
				}

				$displayEndDate =  convertDatetimeToFormat($row['endDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
				
				$company[] = array(
									'companyID' => $row['companyID'],
									'companyName' => $row['companyName'],
									'endDate' => $displayEndDate,
									'creditsRemaining' => $displayCompanyCreditsRemaining,
									'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
									'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining
									);
			}
	
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isSet($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['AddCreateBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
				$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['AddCreateBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
				$_SESSION['AddCreateBookingInfoArray']['CreditsRemaining'] = $company[0]['creditsRemaining'];
				$_SESSION['AddCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = $company[0]['PotentialExtraMonthlyTimeUsed'];
				$_SESSION['AddCreateBookingInfoArray']['PotentialCreditsRemaining'] = $company[0]['PotentialCreditsRemaining'];				
			}
			$_SESSION['AddCreateBookingCompanyArray'] = $company;
		} else{
			// User is NOT in a company
			
			$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
			unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
			unset($_SESSION['AddCreateBookingCompanyArray']);
			$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['AddCreateBookingInfoArray']['BookedForCompany'] = "";
			$_SESSION['AddCreateBookingInfoArray']['CreditsRemaining'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
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
	$row = $_SESSION['AddCreateBookingInfoArray'];
	$original = $_SESSION['AddCreateBookingOriginalInfoArray'];

		// Altered inputs
	if(isSet($row['TheCompanyID'])){
		
			// Changed company?
		if(isSet($company)){
			foreach($company AS $cmp){
				if($cmp['companyID'] == $row['TheCompanyID']){
					$row['BookedForCompany'] = $cmp['companyName'];
					$row['CreditsRemaining'] = $cmp['creditsRemaining'];
					$row['PotentialExtraMonthlyTimeUsed'] = $cmp['PotentialExtraMonthlyTimeUsed'];
					$row['PotentialCreditsRemaining'] = $cmp['PotentialCreditsRemaining'];
					$row['EndDate'] = $cmp['endDate'];					
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

	if(isSet($row['BookedForCompany'])){
		$companyName = $row['BookedForCompany'];
	} else {
		$companyName = '';
	}

	if(isSet($row['CreditsRemaining'])){
		$creditsRemaining = $row['CreditsRemaining'];
	} else {
		$creditsRemaining = 'N/A';
	}

	if(isSet($row['PotentialExtraMonthlyTimeUsed'])){
		$potentialExtraCreditsUsed = $row['PotentialExtraMonthlyTimeUsed'];
	} else {
		$potentialExtraCreditsUsed = 'N/A';
	}

	if(isSet($row['PotentialCreditsRemaining'])){
		$potentialCreditsRemaining = $row['PotentialCreditsRemaining'];
	} else {
		$potentialCreditsRemaining = 'N/A';
	}

	if(isSet($row['EndDate'])){
		$companyPeriodEndDate = $row['EndDate'];
	} else {
		$companyPeriodEndDate = 'N/A';
	}
	
	//	userID has been set earlier
	$meetingroom = $_SESSION['AddCreateBookingMeetingRoomsArray'];
	if(isSet($row['TheMeetingRoomID'])){
		$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	} else {
		$selectedMeetingRoomID = '';
	}
	if(isSet($_GET['meetingroom'])){
		$selectedMeetingRoomID = $_GET['meetingroom'];
	}
	
	if(isSet($row['StartTime']) AND $row['StartTime'] != ""){
		$startDateTime = $row['StartTime'];
	} else {
		$validBookingStartTime = getNextValidBookingStartTime();
		$startDateTime = convertDatetimeToFormat($validBookingStartTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	if(isSet($row['EndTime']) AND $row['EndTime'] != ""){
		$endDateTime = $row['EndTime'];
	} else {
		$validBookingEndTime = getNextValidBookingEndTime(substr($validBookingStartTime,0,-3));
		$endDateTime = convertDatetimeToFormat($validBookingEndTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}	

	if(isSet($row['BookedBy'])){
		$displayName = $row['BookedBy'];
	} else {
		$displayName = '';
	}
	
	if(isSet($row['BookingDescription'])){
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
if (isSet($_POST['add']) AND $_POST['add'] == "Add Booking")
{
	// Validate user inputs
	list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname, $bookingCode) = validateUserInputs('AddCreateBookingError', FALSE);
					
	// handle feedback process on invalid input values
	if(isSet($_GET['meetingroom'])){
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
	
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
	}	
	
	if(isSet($_SESSION['AddCreateBookingStartImmediately']) AND $_SESSION['AddCreateBookingStartImmediately']){
		$startDateTime = getDatetimeNow();
	}
	
	// Check if the timeslot is taken for the selected meeting room
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

		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$meetingRoomID = $meetingRoomID;
			$location = '.';
		}
		header('Location: ' . $location);
		exit();				
	}
	
	unset($_SESSION['AddCreateBookingStartImmediately']);
	
	// Add the booking to the database
	try
	{	
		if(	isSet($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
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
		if(isSet($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}
		
		// Get company name
		$companyName = 'N/A';
		if(isSet($companyID)){
			foreach($_SESSION['AddCreateBookingCompanyArray'] AS $company){
				if($companyID == $company['companyID']){
					$companyName = $company['companyName'];
					// TO-DO: Use this for something?
					$companyCreditsRemaining = $company['creditsRemaining'];
					$companyCreditsBooked = $company['PotentialExtraMonthlyTimeUsed'];
					$companyCreditsPotentialMinimumRemaining = $company['PotentialCreditsRemaining'];
					$companyPeriodEndDate = $company['endDate'];
					break;
				}
			}
		}
		
		$nameOfUserWhoBooked = "N/A";
		if(isSet($_SESSION['LoggedInUserName'])){
			$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
		}
		if(isSet($info["UserLastname"])){
			$nameOfUserWhoBooked = $info["UserLastname"] . ', ' . $info["UserFirstname"];
		}
	
		// Save a description with information about the booking that was created
		$logEventDescription = 'A booking was created for the meeting room: ' . $meetinginfo . 
		', for the user: ' . $userinfo . ' and company: ' . $companyName . '. Booking was made by: ' . $nameOfUserWhoBooked;
		
		if(isSet($_SESSION['lastBookingID'])){
			$lastBookingID = $_SESSION['lastBookingID'];
			unset($_SESSION['lastBookingID']);				
		}

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Booking Created'
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
		
		/*
			// TO-DO: Use this for something?
			$companyCreditsRemaining;
			$companyCreditsBooked;
			$companyCreditsPotentialMinimumRemaining;
			$companyPeriodEndDate;
		*/

	// TO-DO: add to e-mail being sent if the booking created goes over credits.	
	if($info['sendEmail'] == 1){
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
			$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
		}
		
		$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing	
	} elseif($info['sendEmail'] == 0){
		$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails.";
	}
	// Booking a new meeting is done. Reset all connected sessions.
	clearAddCreateBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

//	User wants to change the company the booking is for (after having already selected it)
if(isSet($_POST['add']) AND $_POST['add'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['AddCreateBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddCreateBookingInputs();
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['add']) AND $_POST['add'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberAddCreateBookingInputs();
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['add']) AND $_POST['add'] == "Get Default Display Name"){	  
	$displayName = $_SESSION['AddCreateBookingInfoArray']['UserDefaultDisplayName'];
	if(isSet($_SESSION['AddCreateBookingInfoArray'])){
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
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['add']) AND $_POST['add'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['AddCreateBookingInfoArray']['UserDefaultBookingDescription'];
	if(isSet($_SESSION['AddCreateBookingInfoArray'])){
		
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
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to book the meeting to start immediately.
if(isSet($_POST['add']) AND $_POST['add'] == "Start Booking Immediately"){

	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddCreateBookingInputs();

	$correctStartTime = getDatetimeNow();
	$newStartTime = convertDatetimeToFormat($correctStartTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$_SESSION['AddCreateBookingInfoArray']['StartTime'] = $newStartTime;

	$_SESSION['AddCreateBookingStartImmediately'] = TRUE;

	$newCorrectStartTime = correctDatetimeFormat($newStartTime);
	$newEndTime = convertDatetimeToFormat(getNextValidBookingEndTime($newCorrectStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$_SESSION['AddCreateBookingInfoArray']['EndTime'] = $newEndTime;

	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to change the start time of the booking, after having set it to start immediately.
if(isSet($_POST['add']) AND $_POST['add'] == "Change Start Time"){

	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddCreateBookingInputs();

	$startTime = $_SESSION['AddCreateBookingInfoArray']['StartTime'];
	$correctStartTime = correctDatetimeFormat($startTime);
	$newStartTime = convertDatetimeToFormat(getNextValidBookingEndTime($correctStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$_SESSION['AddCreateBookingInfoArray']['StartTime'] = $newStartTime;

	unset($_SESSION['AddCreateBookingStartImmediately']);

	if($_SESSION['AddCreateBookingInfoArray']['StartTime'] >= $_SESSION['AddCreateBookingInfoArray']['EndTime']){
		$newCorrectStartTime = correctDatetimeFormat($newStartTime);
		$newEndTime = convertDatetimeToFormat(getNextValidBookingEndTime($newCorrectStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$_SESSION['AddCreateBookingInfoArray']['EndTime'] = $newEndTime;
	}

	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['add']) AND $_POST['add'] == "Increase Start By Minimum"){

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
	if(isSet($_GET['meetingroom'])){
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
if(isSet($_POST['add']) AND $_POST['add'] == "Increase End By Minimum"){

	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddCreateBookingInputs();

	$endTime = $_SESSION['AddCreateBookingInfoArray']['EndTime'];
	$correctEndTime = correctDatetimeFormat($endTime);
	$_SESSION['AddCreateBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
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
if (isSet($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddCreateBookingInfoArray'] = $_SESSION['AddCreateBookingOriginalInfoArray'];
	unset($_SESSION['AddCreateBookingSelectedACompany']);
	unset($_SESSION['AddCreateBookingChangeUser']);
	unset($_SESSION['AddCreateBookingSelectedNewUser']);
	
	$_SESSION['refreshAddCreateBooking'] = TRUE;
	if(isSet($_GET['meetingroom'])){
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
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['normalBookingFeedback'] = "You cancelled your new booking.";
}

// ADD BOOKING CODE SNIPPET // END //


// EDIT BOOKING CODE SNIPPET // START //

// if user wants to edit a booking, we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditCreateBooking']) AND $_SESSION['refreshEditCreateBooking']))
{
	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshEditCreateBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshEditCreateBooking']);
	} else {
		$_SESSION['EditCreateBookingOriginalBookingID'] = $_POST['id'];
	}
	// Get information from database again on the selected booking
	/* 	We don't allow regulars users to change the meeting room of the booking.
		They can cancel their current and book another meeting room if they want to.
	if(!isSet($_SESSION['EditCreateBookingMeetingRoomsArray'])){
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
	}*/
	$_SESSION['confirmOrigins'] = "Edit Meeting";
	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
	unset($_SESSION['confirmOrigins']);		
	
	$bookingID = $_SESSION['EditCreateBookingOriginalBookingID'];
	
	// Check if selected user ID is creator of booking or an admin
	$continueEdit = FALSE;
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
		$bookingCreatorUserFirstName = $row['firstName'];
		$bookingCreatorUserLastName = $row['lastName'];
		
		if($row['HitCount'] > 0){
			if($bookingCreatorUserID == $SelectedUserID){
				$continueEdit = TRUE;
			}
		} 
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error checking if user is booking creator: ' . $e->getMessage();
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
			$sql = 'SELECT 	a.`AccessName`,
							u.`firstName`,
							u.`lastName`
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
				$continueEdit = TRUE;
			}
			$userInformation = $row['lastName'] . ", " . $row['firstName'];
			$_SESSION['EditCreateBookingLoggedInUserInformation'] = $userInformation;
			
			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error checking if user is admin: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}		
	} else {
		$userInformation = $bookingCreatorUserLastName . ", " . $bookingCreatorUserFirstName;
		$_SESSION['EditCreateBookingLoggedInUserInformation'] = $userInformation;
	}

	if($continueEdit === FALSE){
		$_SESSION['normalBookingFeedback'] = "You cannot edit this booked meeting.";
		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = '.';
		}
		header('Location: ' . $location);
		exit();				
	}
	
	if(!isSet($_SESSION['EditCreateBookingInfoArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			// Get booking information
			$pdo = connect_to_db();
			$sql = "SELECT 		b.`bookingID`									AS TheBookingID,
								b.`companyID`									AS TheCompanyID,
								b.`meetingRoomID`								AS TheMeetingRoomID,
								b.`startDateTime` 								AS StartTime, 
								b.`endDateTime` 								AS EndTime, 
								b.`description` 								AS BookingDescription,
								b.`displayName` 								AS BookedBy,
								b.`userID`										AS TheUserID,
								b.`cancellationCode`							AS CancellationCode,
								IF(b.`companyID` IS NULL, NULL, 
									(	
										SELECT `name` 
										FROM `company` 
										WHERE `companyID` = TheCompanyID
									)
								)												AS BookedForCompany,
								IF(b.`meetingRoomID` IS NULL, NULL,
									(
										SELECT 	`name`
										FROM 	`meetingroom`
										WHERE 	`meetingRoomID` = TheMeetingRoomID
									)
								) 												AS BookedRoomName,
								IF(b.`userID` IS NULL, NULL, 
									(
										SELECT 	`firstName`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS UserFirstname,
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 	`lastName`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS UserLastname,
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 	`email`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS UserEmail,
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 	`sendEmail`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS sendEmail,
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 	`displayName`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS UserDefaultDisplayName,
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 	`bookingDescription`
										FROM 	`user`
										WHERE 	`userID` = TheUserID
									)
								)												AS UserDefaultBookingDescription
					FROM 		`booking` b
					WHERE		b.`bookingID` = :BookingID
					LIMIT		1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':BookingID', $bookingID);
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
		$_SESSION['EditCreateBookingInfoArray']['CreditsRemaining'] = "N/A";
		$_SESSION['EditCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
		$_SESSION['EditCreateBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
		$_SESSION['EditCreateBookingInfoArray']['EndDate'] = "N/A";
		$_SESSION['EditCreateBookingOriginalInfoArray'] = $_SESSION['EditCreateBookingInfoArray'];
	}

	// Set the correct information on form call
	$UserIDInBooking = $_SESSION['EditCreateBookingInfoArray']['TheUserID'];	
	
		// Check if we need a company select for the booking
	try
	{		
		// We want the companies the user works for to decide if we need to
		// have a dropdown select or just a fixed value (with 0 or 1 company)
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT		c.`companyID`,
							c.`name` 					AS companyName,
							c.`endDate`,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = e.`CompanyID`
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							)													AS MonthlyCompanyWideBookingTimeUsed,
															(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`endDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`endDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = e.`CompanyID`
								AND 		b.`actualEndDateTime` IS NULL
								AND			b.`dateTimeCancelled` IS NULL
								AND			b.`endDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							)													AS PotentialExtraMonthlyCompanyWideBookingTimeUsed,
							(
								SELECT 	IFNULL(cc.`altMinuteAmount`, cr.`minuteAmount`)
								FROM 		`company` c
								INNER JOIN	`companycredits` cc
								ON			c.`CompanyID` = cc.`CompanyID`
								INNER JOIN	`credits` cr
								ON			cr.`CreditsID` = cc.`CreditsID`
								WHERE		c.`CompanyID` = e.`CompanyID`
							) 													AS CreditSubscriptionMinuteAmount
				FROM 		`user` u
				INNER JOIN 	`employee` e
				ON 			e.`userID` = u.`userID`
				INNER JOIN	`company` c
				ON 			c.`companyID` = e.`companyID`
				WHERE 		u.`userID` = :userID
				AND			c.`isActive` = 1';
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s	

		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $UserIDInBooking);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
			
			foreach($result as $row){
				// Get the companies the user works for
				// This will be used to create a dropdown list in HTML

				// Get booking time used this month
				if(empty($row['MonthlyCompanyWideBookingTimeUsed'])){
					$MonthlyTimeUsed = 'N/A';
				} else {
					$MonthlyTimeUsed = convertTimeToHoursAndMinutes($row['MonthlyCompanyWideBookingTimeUsed']);
				}

				// Get potential booking time used this month
				if(empty($row['PotentialExtraMonthlyCompanyWideBookingTimeUsed'])){
					$potentialExtraMonthlyTimeUsed = 'N/A';
				} else {
					$potentialExtraMonthlyTimeUsed = convertTimeToHoursAndMinutes($row['PotentialExtraMonthlyCompanyWideBookingTimeUsed']);
				}

				// Get credits given
				if(!empty($row["CreditSubscriptionMinuteAmount"])){
					$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
				} else {
					$companyMinuteCredits = 0;
				}

					// Calculate Company Credits Remaining (only includes completed bookings)
				if($MonthlyTimeUsed != "N/A"){
					$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);
					$actualTimeUsedInMinutesThisMonth = $monthlyTimeHour*60 + $monthlyTimeMinute;
					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusCompanyMinuteCreditsRemaining);
					} else {
						$companyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
					}
				} else {
					$companyMinuteCreditsRemaining = $companyMinuteCredits;
					$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
				}

					// Calculate Potential Company Credits Remaining (includes future bookings)
				if($potentialExtraMonthlyTimeUsed == "N/A"){
					$displayPotentialCompanyCreditsRemaining = $displayCompanyCreditsRemaining;
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes(0);
				} elseif($MonthlyTimeUsed != "N/A"){
					$monthlyTimeHour = substr($potentialExtraMonthlyTimeUsed,0,strpos($potentialExtraMonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($potentialExtraMonthlyTimeUsed,strpos($potentialExtraMonthlyTimeUsed,"h")+1,-1);
					$potentialExtraMonthlyTimeUsedInMinutes = $monthlyTimeHour*60 + $monthlyTimeMinute;
					$actualTimeUsedInMinutesThisMonth += $potentialExtraMonthlyTimeUsedInMinutes;

					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusPotentialCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayPotentialCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusPotentialCompanyMinuteCreditsRemaining);
					} else {
						$potentialCompanyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayPotentialCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($potentialCompanyMinuteCreditsRemaining);
					}
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes($potentialExtraMonthlyTimeUsedInMinutes);
				} else {
					$monthlyTimeHour = substr($potentialExtraMonthlyTimeUsed,0,strpos($potentialExtraMonthlyTimeUsed,"h"));
					$monthlyTimeMinute = substr($potentialExtraMonthlyTimeUsed,strpos($potentialExtraMonthlyTimeUsed,"h")+1,-1);
					$potentialExtraMonthlyTimeUsedInMinutes = $monthlyTimeHour*60 + $monthlyTimeMinute;
					$actualTimeUsedInMinutesThisMonth = $potentialExtraMonthlyTimeUsedInMinutes;

					if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
						$minusPotentialCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
						$displayPotentialCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusPotentialCompanyMinuteCreditsRemaining);
					} else {
						$potentialCompanyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
						$displayPotentialCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($potentialCompanyMinuteCreditsRemaining);
					}
					$displayPotentialExtraMonthlyTimeUsed = convertMinutesToHoursAndMinutes($potentialExtraMonthlyTimeUsedInMinutes);				
				}

				$displayEndDate =  convertDatetimeToFormat($row['endDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
				
				$company[] = array(
									'companyID' => $row['companyID'],
									'companyName' => $row['companyName'],
									'endDate' => $displayEndDate,
									'creditsRemaining' => $displayCompanyCreditsRemaining,
									'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
									'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining
									);
			}
	
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isSet($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['EditCreateBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				//$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
				unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
				$_SESSION['EditCreateBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditCreateBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
				$_SESSION['EditCreateBookingInfoArray']['CreditsRemaining'] = $company[0]['creditsRemaining'];
				$_SESSION['EditCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = $company[0]['PotentialExtraMonthlyTimeUsed'];
				$_SESSION['EditCreateBookingInfoArray']['PotentialCreditsRemaining'] = $company[0]['PotentialCreditsRemaining'];				
			}
		} else {
			// User is NOT in a company
			
			//$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
			unset($_SESSION['EditCreateBookingDisplayCompanySelect']);
			$_SESSION['EditCreateBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditCreateBookingInfoArray']['BookedForCompany'] = "";
			$_SESSION['EditCreateBookingInfoArray']['CreditsRemaining'] = "N/A";
			$_SESSION['EditCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
			$_SESSION['EditCreateBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
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
	if(isSet($company)){
		foreach($company AS $cmp){
			if($cmp['companyID'] == $row['TheCompanyID']){
				$row['BookedForCompany'] = $cmp['companyName'];
				$row['CreditsRemaining'] = $cmp['creditsRemaining'];
				$row['PotentialExtraMonthlyTimeUsed'] = $cmp['PotentialExtraMonthlyTimeUsed'];
				$row['PotentialCreditsRemaining'] = $cmp['PotentialCreditsRemaining'];
				$row['EndDate'] = $cmp['endDate'];				
				break;
			}
		}			
	}

		// Edited inputs
	$bookingID = $row['TheBookingID'];
	$companyName = $row['BookedForCompany'];
	$creditsRemaining = $row['CreditsRemaining'];
	$potentialExtraCreditsUsed = $row['PotentialExtraMonthlyTimeUsed'];
	$potentialCreditsRemaining = $row['PotentialCreditsRemaining'];
	$companyPeriodEndDate = $row['EndDate'];	
	$selectedCompanyID = $row['TheCompanyID'];
	$companyID = $row['TheCompanyID'];
	//	userID has been set earlier
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
	if(!isSet($originalMeetingRoomName) OR $originalMeetingRoomName == NULL OR $originalMeetingRoomName == ""){
		$originalMeetingRoomName = "N/A - Deleted";	
	}
	$originalDisplayName = $original['BookedBy'];
	$originalBookingDescription = $original['BookingDescription'];
	$originalUserInformation = 	$original['UserLastname'] . ', ' . $original['UserFirstname'] . 
								' - ' . $original['UserEmail'];
	if(!isSet($originalUserInformation) OR $originalUserInformation == NULL OR $originalUserInformation == ",  - "){
		$originalUserInformation = "N/A - Deleted";	
	}	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual form we want to use
	include 'editbooking.html.php';
	exit();
}

// If user wants to update the booking information after editing
if(isSet($_POST['edit']) AND $_POST['edit'] == "Finish Edit")
{
	// Validate user inputs
	list($invalidInput, $bknDscrptn, $dspname, $bookingCode) = validateUserInputs('EditCreateBookingError', TRUE);
	
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
	if(	isSet($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
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
if(isSet($_POST['edit']) AND $_POST['edit'] == "Change Company"){
	
	// We want to select a company again
	//unset($_SESSION['EditCreateBookingSelectedACompany']);
	$_SESSION['EditCreateBookingSelectACompany'] = TRUE;
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditCreateBookingInputs();
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// User confirms what company he wants the booking to be for.
if(isSet($_POST['edit']) AND $_POST['edit'] == "Select This Company"){

	// Remember that we've selected a new company
	//$_SESSION['EditCreateBookingSelectedACompany'] = TRUE;
	unset($_SESSION['EditCreateBookingSelectACompany']);
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditCreateBookingInputs();
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If user wants to get their default display name
if(isSet($_POST['edit']) AND $_POST['edit'] == "Get Default Display Name"){

	$displayName = $_SESSION['EditCreateBookingOriginalInfoArray']['UserDefaultBookingDescription'];
	if(isSet($_SESSION['EditCreateBookingInfoArray'])){
		
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
if(isSet($_POST['edit']) AND $_POST['edit'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['EditCreateBookingOriginalInfoArray']['UserDefaultDisplayName'];
	if(isSet($_SESSION['EditCreateBookingInfoArray'])){
		
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
if(isSet($_POST['edit']) AND $_POST['edit'] == "Increase Start By Minimum"){
	
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
if(isSet($_POST['edit']) AND $_POST['edit'] == "Increase End By Minimum"){
	
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
if (isSet($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditCreateBookingInfoArray'] = $_SESSION['EditCreateBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditCreateBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// EDIT BOOKING CODE SNIPPET // END //

// CANCELLATION CODE SNIPPET // START //

// Cancels a booking from a submitted cancellation link
if(isSet($_GET['cancellationcode']) OR isSet($_SESSION['refreshWithCancellationCode'])){
	if(isSet($_GET['cancellationcode'])){
		// Check if code is correct (64 chars)
		if(strlen($_GET['cancellationcode'])!=64){
			$_SESSION['normalBookingFeedback'] = "The cancellation code that was submitted is not a valid code.";
			header("Location: .");
			exit();
		}
		$_SESSION['refreshWithCancellationCode'] = $_GET['cancellationcode'];
		header("Location: .");
		exit();		
	}
	
	$cancellationCode = $_SESSION['refreshWithCancellationCode'];
	unset($_SESSION['refreshWithCancellationCode']);

	//	Check if the submitted code is in the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "SELECT 	COUNT(*)										AS HitCount,
						`bookingID`,
						`meetingRoomID`									AS TheMeetingRoomID, 
						(
							SELECT	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = TheMeetingRoomID 
						)												AS TheMeetingRoomName,
						`startDateTime`									AS StartDateTime,
						`endDateTime`									AS EndDateTime,
						`actualEndDateTime`								AS ActualEndDateTime
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

	$result = $s->fetch(PDO::FETCH_ASSOC);
	if(isSet($result)){
		$rowNum = $result['HitCount'];
	} else {
		$rowNum = 0;
	}
	// Check if the select even found something
	if($rowNum == 0){
		// No match.
		$_SESSION['normalBookingFeedback'] = "The cancellation code that was submitted did not match an active meeting.";
		header("Location: .");
		exit();
	}
	
	$bookingID = $result['bookingID'];
	$TheMeetingRoomName = $result['TheMeetingRoomName'];
	$startDateTimeString = $result['StartDateTime'];
	$endDateTimeString = $result['EndDateTime'];
	$actualEndDateTimeString = $result['ActualEndDateTime'];
	
	$startDateTime = correctDatetimeFormat($startDateTimeString);
	$endDateTime = correctDatetimeFormat($endDateTimeString);
	
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
	
	// Check if the meeting has already ended
	if($actualEndDateTimeString == "" OR $actualEndDateTimeString == NULL){
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
			$bookingFeedback = 	"The booking for " . $TheMeetingRoomName . ".\nStarting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " has been ended early by using the cancellation link.";
			$logEventDescription = $bookingFeedback;
		} elseif($timeNow < $startDateTime) {
			// The booking hasn't started yet, so we're actually cancelling the meeting
			$sql = "UPDATE 	`booking`
					SET		`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL
					WHERE 	`bookingID` = :bookingID";	
			$bookingFeedback = 	"The booking for " . $TheMeetingRoomName . ".\nStarting at: " . $displayValidatedStartDate . 
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
			$logEventDescription = 	"The booking for " . $TheMeetingRoomName . ".\nStarting at: " . $displayValidatedStartDate . 
									" and ending at: " . $displayValidatedEndDate . " was attempted to be cancelled with the " . 
									"cancellation link, but the meeting should have already been completed." .
									" The end date of the booking has been updated to have occured on the scheduled time.";			
		}	
	} elseif(isSet($actualEndDateTimeString) AND $actualEndDateTimeString != "" AND $actualEndDateTimeString != NULL) {
		// Meeting has already ended. So there's no reason to cancel it.
		$bookingFeedback = 	"The booked meeting has already ended.";
		$sql = "UPDATE 	`booking`
				SET		`cancellationCode` = NULL
				WHERE 	`bookingID` = :bookingID";
		$bookingFeedback = 		"The booked meeting has already ended.";
		$logEventDescription = 	"The booking for " . $TheMeetingRoomName . ".\nStarting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " was attempted to be cancelled with the " . 
								"cancellation link, but the meeting had already ended so it had no effect.";
	} else {
		$bookingFeedback = 		"Could not cancel the meeting.";
	}
	
	if(isSet($logEventDescription) AND isSet($bookingID) AND $bookingID != NULL){
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
	$_SESSION['normalBookingFeedback'] = $bookingFeedback;
}

// CANCELLATION CODE SNIPPET // END //

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddCreateBookingSessions();
clearEditCreateBookingSessions();
unset($_SESSION["cancelBookingOriginalValues"]);
unset($_SESSION["confirmOrigins"]);
unset($_SESSION["EditCreateBookingError"]);

if(isSet($refreshBookings) AND $refreshBookings) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshBookings);
}

// Display relevant booked meetings
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	if(isSet($_GET['meetingroom']) AND $_GET['meetingroom'] != NULL AND $_GET['meetingroom'] != ""){
		$sql = 'SELECT 		b.`userID`										AS BookedUserID,
							b.`bookingID`,
							(
								IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
							)        										AS BookedRoomName,
							b.`startDateTime`								AS StartTime,
							b.`endDateTime`									AS EndTime, 
							b.`displayName` 								AS BookedBy,
							(
								IF(b.`companyID` IS NULL, NULL, (SELECT `name` FROM `company` WHERE `companyID` = b.`companyID`))
							)        										AS BookedForCompany,										
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS firstName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS lastName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS email,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `sendEmail` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS sendEmail,
							(
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 		GROUP_CONCAT(c.`name` separator ",\n")
										FROM 		`company` c
										INNER JOIN `employee` e
										ON 			e.`CompanyID` = c.`CompanyID`
										WHERE  		e.`userID` = b.`userID`
										AND			c.`isActive` = 1
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription, 
							b.`dateTimeCreated`								AS BookingWasCreatedOn
				FROM 		`booking` b
				WHERE		b.`meetingRoomID` = :meetingRoomID
				AND			b.`dateTimeCancelled` IS NULL
				AND 		b.`actualEndDateTime` IS NULL
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				ASC';
		$s = $pdo->prepare($sql);
		$s->bindValue(':meetingRoomID', $_GET['meetingroom']);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
	} elseif(!isSet($_GET['meetingroom'])){
		$sql = 'SELECT 		b.`userID`										AS BookedUserID,
							b.`bookingID`,
							(
								IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
							)        										AS BookedRoomName,
							b.`startDateTime`								AS StartTime,
							b.`endDateTime`									AS EndTime, 
							b.`displayName` 								AS BookedBy,
							(
								IF(b.`companyID` IS NULL, NULL, (SELECT `name` FROM `company` WHERE `companyID` = b.`companyID`))
							)        										AS BookedForCompany,										
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS firstName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS lastName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS email,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `sendEmail` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS sendEmail,
							(
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 		GROUP_CONCAT(c.`name` separator ",\n")
										FROM 		`company` c
										INNER JOIN `employee` e
										ON 			e.`CompanyID` = c.`CompanyID`
										WHERE  		e.`userID` = b.`userID`
										AND			c.`isActive` = 1
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription, 
							b.`dateTimeCreated`								AS BookingWasCreatedOn
				FROM 		`booking` b
				WHERE		b.`dateTimeCancelled` IS NULL
				AND 		b.`actualEndDateTime` IS NULL
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				ASC';
		$return = $pdo->query($sql);
		$result = $return->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
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
	$dateOnlyNow = convertDatetimeToFormat($datetimeNow, 'Y-m-d H:i:s', 'Y-m-d');
	$dateOnlyStart = convertDatetimeToFormat($startDateTime,'Y-m-d H:i:s','Y-m-d');
	$createdDateTime = $row['BookingWasCreatedOn'];	
	
	// Check if booking is for today or for the future
	if($datetimeNow < $endDateTime AND $dateOnlyNow != $dateOnlyStart) {
		$status = 'Active';
	} elseif($datetimeNow < $endDateTime AND $dateOnlyNow == $dateOnlyStart){
		$status = 'Active Today';
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
	if(!isSet($roomName) OR $roomName == NULL OR $roomName == ""){
		$roomName = "N/A - Deleted";
	}
	if(!isSet($userinfo) OR $userinfo == NULL OR $userinfo == ",  - "){
		$userinfo = "N/A - Deleted";	
	}
	if(!isSet($email) OR $email == NULL OR $email == ""){
		$firstname = "N/A - Deleted";
		$lastname = "N/A - Deleted";
		$email = "N/A - Deleted";		
	}
	if(!isSet($worksForCompany) OR $worksForCompany == NULL OR $worksForCompany == ""){
		$worksForCompany = "N/A";
	}
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
	$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	$meetinginfo = $roomName . ' for the timeslot: ' . $displayValidatedStartDate . 
					' to ' . $displayValidatedEndDate;
	
	if($status == "Active Today"){				
		$bookingsActiveToday[] = array(	'id' => $row['bookingID'],
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
										'BookedUserID' => $row['BookedUserID'],
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo,
										'sendEmail' => $row['sendEmail']
									);
	}	elseif($status == "Active") {
		$bookingsFuture[] = array(	'id' => $row['bookingID'],
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
									'BookedUserID' => $row['BookedUserID'],									
									'UserInfo' => $userinfo,
									'MeetingInfo' => $meetinginfo,
									'sendEmail' => $row['sendEmail']
								);
	}
}
var_dump($_SESSION); // TO-DO: remove after testing is done
// Load the html template
include_once 'booking.html.php';
?>