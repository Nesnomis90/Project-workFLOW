<?php 
// This is the index file for the BOOKINGS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add booking form
function clearAddBookingSessions(){
	unset($_SESSION['AddBookingInfoArray']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingUsersArray']);
	unset($_SESSION['AddBookingOriginalInfoArray']);
	unset($_SESSION['AddBookingMeetingRoomsArray']);	
	unset($_SESSION['AddBookingUserSearch']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['AddBookingDefaultBookingDescriptionForNewUser']);	
	unset($_SESSION['AddBookingDisplayCompanySelect']);	
	unset($_SESSION['AddBookingCompanyArray']);
	unset($_SESSION['AddBookingUserCannotBookForSelf']);
	
	unset($_SESSION['refreshAddBookingConfirmed']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit booking form
function clearEditBookingSessions(){
	unset($_SESSION['EditBookingInfoArray']);
	unset($_SESSION['EditBookingChangeUser']);
	unset($_SESSION['EditBookingUsersArray']);
	unset($_SESSION['EditBookingOriginalInfoArray']);
	unset($_SESSION['EditBookingMeetingRoomsArray']);	
	unset($_SESSION['EditBookingUserSearch']);
	unset($_SESSION['EditBookingSelectedNewUser']);
	unset($_SESSION['EditBookingSelectACompany']);
	unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);	
	unset($_SESSION['EditBookingDisplayCompanySelect']);
	unset($_SESSION['EditBookingCompanyArray']);

	unset($_SESSION['refreshEditBookingConfirmed']);
}

// Function to clear sessions used to remember information during the cancel process.
function clearCancelSessions(){
	unset($_SESSION['cancelAdminBookingOriginalValues']);
}

// Function to remember the user inputs in Edit Booking
function rememberEditBookingInputs(){
	if(isSet($_SESSION['EditBookingInfoArray'])){
		$newValues = $_SESSION['EditBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isSet($_POST['userID'])){
			$newValues['TheUserID'] = $_POST['userID'];
		}
			// The meeting room selected
		$newValues['TheMeetingRoomID'] = $_POST['meetingRoomID']; 
			// The company selected
		$newValues['TheCompanyID'] = $_POST['companyID'];
			// The user selected
		$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
			// The booking description
		$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);
			// The admin note
		$newValues['AdminNote'] = trimExcessWhitespaceButLeaveLinefeed($_POST['adminNote']);
			// The start time
		if(isSet($_POST['startDateTime'])){
			$newValues['StartTime'] = trimExcessWhitespace($_POST['startDateTime']);
		}
			// The end time
		if(isSet($_POST['endDateTime'])){
			$newValues['EndTime'] = trimExcessWhitespace($_POST['endDateTime']);
		}
		
		$_SESSION['EditBookingInfoArray'] = $newValues;			
	}
}

// Function to remember the user inputs in Add Booking
function rememberAddBookingInputs(){
	if(isSet($_SESSION['AddBookingInfoArray'])){
		$newValues = $_SESSION['AddBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isSet($_POST['userID'])){
			$newValues['TheUserID'] = $_POST['userID'];
		}
			// The meeting room selected
		$newValues['TheMeetingRoomID'] = $_POST['meetingRoomID']; 
			// The company selected
		$newValues['TheCompanyID'] = $_POST['companyID'];
			// The user selected
		$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
			// The booking description
		$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);
			// The admin note
		$newValues['AdminNote'] = trimExcessWhitespaceButLeaveLinefeed($_POST['adminNote']);
			// The start time
		$newValues['StartTime'] = trimExcessWhitespace($_POST['startDateTime']);
			// The end time
		$newValues['EndTime'] = trimExcessWhitespace($_POST['endDateTime']);
		
		$_SESSION['AddBookingInfoArray'] = $newValues;
	}
}

// This is used on cancel and delete.
function emailUserOnCancelledBooking(){
	if(isSet($_SESSION['cancelAdminBookingOriginalValues']['UserID']) AND $_SESSION['cancelAdminBookingOriginalValues']['UserID'] != $_SESSION['LoggedInUserID']){
		if(isSet($_SESSION['cancelAdminBookingOriginalValues']['SendEmail']) AND $_SESSION['cancelAdminBookingOriginalValues']['SendEmail'] == 1){

			$bookingCreatorUserEmail = $_SESSION['cancelAdminBookingOriginalValues']['UserEmail'];
			$bookingCreatorMeetingInfo = $_SESSION['cancelAdminBookingOriginalValues']['MeetingInfo'];

			if(isSet($_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'])){
				$reasonForCancelling = $_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'];
			} else {
				$reasonForCancelling = "No reason given.";
			}

			$emailSubject = "Your meeting has been cancelled!";

			$emailMessage = 
			"A booked meeting has been cancelled by an Admin!\n" .
			"The meeting was booked for the room " . $bookingCreatorMeetingInfo .
			"\nReason given for cancelling: " . $reasonForCancelling;

			$email = $bookingCreatorUserEmail;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			if(!$mailResult){
				$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

				// Email failed to be prepared. Store it in database to try again later
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

					$pdo = connect_to_db();
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->execute();

					//close connection
					$pdo = null;
				}
				catch (PDOException $e)
				{
					$error = 'Error storing email: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading
		} elseif(isSet($_SESSION['cancelAdminBookingOriginalValues']['SendEmail']) AND $_SESSION['cancelAdminBookingOriginalValues']['SendEmail'] == 0) {
			$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent Email.";
		}
	} elseif(isSet($_SESSION['cancelAdminBookingOriginalValues']['UserID']) AND $_SESSION['cancelAdminBookingOriginalValues']['UserID'] == $_SESSION['LoggedInUserID']){
		$_SESSION['BookingUserFeedback'] .= "\nDid not send an email because you cancelled your own meeting.";
	} else {
		$_SESSION['BookingUserFeedback'] .= "\nFailed to send an email to the user that the booking got cancelled.";
	}
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse, $editing, $bookingCompleted){
	// Get user inputs
	$invalidInput = FALSE;

	if(!$bookingCompleted){
		if(isSet($_POST['startDateTime']) AND !$invalidInput){
			$startDateTimeString = $_POST['startDateTime'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be finished without submitting a start time.";
		}
		if(isSet($_POST['endDateTime']) AND !$invalidInput){
			$endDateTimeString = $_POST['endDateTime'];
		} else {
			$invalidInput = TRUE;
			$_SESSION[$FeedbackSessionToUse] = "A booking cannot be finished without submitting an end time.";
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
	if(isSet($_POST['adminNote'])){
		$adminNoteString = $_POST['adminNote'];
	} else {
		$adminNoteString = '';
	}	
	
	// Remove excess whitespace and prepare strings for validation
	if(!$bookingCompleted){	
		$validatedStartDateTime = trimExcessWhitespace($startDateTimeString);
		$validatedEndDateTime = trimExcessWhitespace($endDateTimeString);
	}
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	$validatedAdminNote = trimExcessWhitespaceButLeaveLinefeed($adminNoteString);
	
	// Do actual input validation
	if(!$bookingCompleted){
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
	if(validateString($validatedAdminNote) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted admin note has illegal characters in it.";
	}
	
	// Are values actually filled in?
	if(!$bookingCompleted){
		if($validatedStartDateTime == "" AND $validatedEndDateTime == "" AND !$invalidInput){

			$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start and an end time for your booking.";	
			$invalidInput = TRUE;
		} elseif($validatedStartDateTime != "" AND $validatedEndDateTime == "" AND !$invalidInput) {
			$_SESSION[$FeedbackSessionToUse] = "You need to fill in an end time for your booking.";	
			$invalidInput = TRUE;
		} elseif($validatedStartDateTime == "" AND $validatedEndDateTime != "" AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "You need to fill in a start time for your booking.";	
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
		// AdminNote
	$invalidAdminNote = isLengthInvalidBookingDescription($validatedAdminNote);
	if($invalidAdminNote AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The admin note submitted is too long.";	
		$invalidInput = TRUE;
	}

	// Check if the dateTime inputs we received are actually datetimes
	if(!$bookingCompleted){
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

		// We want to check if a booking is in the correct minute slice e.g. 15 minute increments.
			// We check both start and end time for online/admin bookings
		if(!$editing){
			if(!$invalidInput){
				$invalidStartTime = isBookingDateTimeMinutesInvalid($startDateTime);
				if($invalidStartTime){
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
			if(!$invalidInput){
				$invalidBookingLength = isBookingTimeDurationInvalid($startDateTime, $endDateTime);
				if($invalidBookingLength){
					$_SESSION[$FeedbackSessionToUse] = "Your start time and end time needs to have at least a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes difference.";
					$invalidInput = TRUE;
				}
			}
		}
	}

	if($bookingCompleted){
		$startDateTime = NULL;
		$endDateTime = NULL;
	}
	return array($invalidInput, $startDateTime, $endDateTime, $validatedBookingDescription, $validatedDisplayName, $validatedAdminNote);
}


// If admin wants to remove a booked meeting from the database
/*
if (isSet($_POST['action']) and $_POST['action'] == 'Delete'){
	// Delete selected booked meeting from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `booking` 
				WHERE 		`bookingID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting booked meeting to delete: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['BookingUserFeedback'] .= "Successfully removed the booking";

	// Add a log event that a booking was deleted
	try
	{
		// Save a description with information about the booking that was removed
		$logEventDescription = "N/A";
		if(isSet($_POST['UserInfo']) AND isSet($_POST['MeetingInfo'])){
			$logEventDescription = 	"A booking with these details was removed:" .
									"\nMeeting Information: " . $_POST['MeetingInfo'] .
									"\nUser Information: " . $_POST['UserInfo'] .
									"\nIt was removed by: " . $_SESSION['LoggedInUserName'];
		} else {
			$logEventDescription = 'A booking was removed by: ' . $_SESSION['LoggedInUserName'];
		}

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Booking Removed'
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

	// Check if the meeting that was removed still was active, if so
	if(isSet($_POST['BookingStatus']) AND ($_POST['BookingStatus'] == 'Active' OR $_POST['BookingStatus'] == 'Active Today')){
		// Send email to user that meeting has been cancelled
		$_SESSION['cancelAdminBookingOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['cancelAdminBookingOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['cancelAdminBookingOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];
		$_SESSION['cancelAdminBookingOriginalValues']['UserInfo'] = $_POST['UserInfo'];

		if(isSet($_POST['UserID']) AND !empty($_POST['UserID'])){
			$_SESSION['cancelAdminBookingOriginalValues']['UserID'] = $_POST['UserID'];
		}
		if(isSet($_POST['sendEmail']) AND !empty($_POST['sendEmail'])){
			$_SESSION['cancelAdminBookingOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email']) AND !empty($_POST['Email'])){
			$_SESSION['cancelAdminBookingOriginalValues']['UserEmail'] = $_POST['Email'];
		}

		emailUserOnCancelledBooking();
		clearCancelSessions();
	}

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}
*/

// If admin does not want to cancel the meeting anyway.
if(isSet($_POST['action']) AND $_POST['action'] == "Abort Cancel"){

	clearCancelSessions();

	$_SESSION['BookingUserFeedback'] = "You did not cancel the meeting.";

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();
}

// If admin wants to cancel a scheduled booked meeting (instead of deleting)
if (	(isSet($_POST['action']) and $_POST['action'] == 'Cancel') OR 
		(isSet($_SESSION['refreshCancelAdminBooking']) AND $_SESSION['refreshCancelAdminBooking'])
		){
	
	if(isSet($_SESSION['refreshCancelAdminBooking']) AND $_SESSION['refreshCancelAdminBooking']){
		unset($_SESSION['refreshCancelAdminBooking']);
	} else {
		$_SESSION['cancelAdminBookingOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['cancelAdminBookingOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['cancelAdminBookingOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];
		$_SESSION['cancelAdminBookingOriginalValues']['UserInfo'] = $_POST['UserInfo'];

		if(isSet($_POST['UserID']) AND !empty($_POST['UserID'])){
			$_SESSION['cancelAdminBookingOriginalValues']['UserID'] = $_POST['UserID'];
		}
		if(isSet($_POST['sendEmail']) AND !empty($_POST['sendEmail'])){
			$_SESSION['cancelAdminBookingOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email']) AND !empty($_POST['Email'])){
			$_SESSION['cancelAdminBookingOriginalValues']['UserEmail'] = $_POST['Email'];
		}
	}

	$bookingID = $_SESSION['cancelAdminBookingOriginalValues']['BookingID'];
	$bookingStatus = $_SESSION['cancelAdminBookingOriginalValues']['BookingStatus'];
	$bookingMeetingInfo = $_SESSION['cancelAdminBookingOriginalValues']['MeetingInfo'];
	$userInfo = $_SESSION['cancelAdminBookingOriginalValues']['UserInfo'];

	// Only cancel if booking is currently active
	if(isSet($bookingStatus) AND ($bookingStatus == 'Active' OR $bookingStatus == 'Active Today')){

		// Load new template to let admin add a reason for cancelling the meeting
		if(!isSet($_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'])){
			var_dump($_SESSION); // TO-DO: Remove before uploading
			include_once 'cancelmessage.html.php';
			exit();
		}

		if(isSet($_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'])){
			$cancelMessage = $_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'];
		} else {
			$cancelMessage = NULL;
		}

		// Update cancellation date for selected booked meeting in database
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			// Get info if the booking is ending early and if it had an order connected
			$sql = "SELECT 	COUNT(*)	AS HitCount,
							`orderID`	AS OrderID
					FROM	`booking`
					WHERE	`bookingID` = :bookingID
					AND		`actualEndDateTime` IS NULL
					AND		`dateTimeCancelled` IS NULL
					AND		CURRENT_TIMESTAMP
					BETWEEN	`startDateTime`
					AND		`endDateTime`
					LIMIT 	1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':bookingID', $bookingID);
			$s->execute();

			$row = $s->fetchAll(PDO::FETCH_ASSOC);
			if($row['HitCount'] > 0){
				$endedEarly = TRUE;
				if($row['OrderID'] != NULL){
					$orderID = $row['OrderID'];
				}
			} else {
				$endedEarly = FALSE;
			}

			$pdo->beginTransaction();

			if($endedEarly){
				// Meeting got cancelled after the meeting started.
				$sql = 'UPDATE 	`booking` 
						SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
								`actualEndDateTime` = CURRENT_TIMESTAMP,
								`cancellationCode` = NULL,
								`cancelMessage` = :cancelMessage,
								`cancelledByUserID` = :cancelledByUserID
						WHERE 	`bookingID` = :bookingID
						AND		`dateTimeCancelled` IS NULL
						AND		`actualEndDateTime` IS NULL';
				$s = $pdo->prepare($sql);
				$s->bindValue(':bookingID', $bookingID);
				$s->bindValue(':cancelMessage', $cancelMessage);
				$s->bindValue(':cancelledByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();

				if(isSet($orderID)){
					$sql = "UPDATE	`orders`
							SET		`orderFinalPrice` = (
															SELECT		SUM(IFNULL(eo.`alternativePrice`, ex.`price`) * eo.`amount`) AS FullPrice
															FROM		`extra` ex
															INNER JOIN 	`extraorders` eo
															ON 			ex.`extraID` = eo.`extraID`
															WHERE		eo.`orderID` = :OrderID
														)
							WHERE	`orderID` = :OrderID
							AND		`orderFinalPrice` IS NULL";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->execute();
				}

			} else {
				// Meeting got cancelled before the meeting started.
				$sql = 'UPDATE 	`booking` 
						SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
								`cancellationCode` = NULL,
								`cancelMessage` = :cancelMessage,
								`cancelledByUserID` = :cancelledByUserID
						WHERE 	`bookingID` = :bookingID
						AND		`dateTimeCancelled` IS NULL
						AND		`actualEndDateTime` IS NULL';
				$s = $pdo->prepare($sql);
				$s->bindValue(':bookingID', $bookingID);
				$s->bindValue(':cancelMessage', $cancelMessage);
				$s->bindValue(':cancelledByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			}
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$pdo = null;
			$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

			// Add a log event that a booking was cancelled
		try
		{
			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($bookingMeetingInfo) AND isSet($userInfo)){
				$logEventDescription = 	"A booking with these details was cancelled:" .
										"\nMeeting Information: " . $bookingMeetingInfo .
										"\nBooked For User: " . $userInfo .
										"\nIt was cancelled by: " . $_SESSION['LoggedInUserName'];
			} else {
				$logEventDescription = 'A booking was cancelled by: ' . $_SESSION['LoggedInUserName'];
			}

			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Booking Cancelled'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventDescription);
			$s->execute();

			$pdo->commit();

			//Close the connection
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$pdo->rollBack();
			$error = 'Error adding log event to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		$_SESSION['BookingUserFeedback'] .= "Successfully cancelled the booking";

		emailUserOnCancelledBooking();
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['BookingUserFeedback'] = "Meeting has already been completed. Did not cancel it.";
	}

	clearCancelSessions();

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin has finished adding a reason for cancelling a meeting.
if(isSet($_POST['action']) AND $_POST['action'] == "Confirm Reason"){
	$invalidInput = FALSE;
	// Do input validation
	if(isSet($_POST['cancelMessage']) AND !empty($_POST['cancelMessage'])){
		$cancelMessage = trimExcessWhitespaceButLeaveLinefeed($_POST['cancelMessage']);
	} else {
		$cancelMessage = "";
	}
	if(validateString($cancelMessage) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['confirmAdminReasonError'] = "Your submitted message has illegal characters in it.";
	}

	$invalidCancelMessage = isLengthInvalidBookingDescription($cancelMessage);
	if($invalidCancelMessage AND !$invalidInput){
		$_SESSION['confirmAdminReasonError'] = "Your submitted message is too long.";	
		$invalidInput = TRUE;
	}

	if($invalidInput){
		
		var_dump($_SESSION); // TO-DO: Remove when done testing

		include_once 'cancelmessage.html.php';
		exit();
	}

	$_SESSION['cancelAdminBookingOriginalValues']['ReasonForCancelling'] = $cancelMessage;
	$_SESSION['refreshCancelAdminBooking'] = TRUE;

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// EDIT CODE SNIPPET START//


// if admin wants to edit a booking, we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditBooking']) AND $_SESSION['refreshEditBooking']))
{
	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshEditBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshEditBooking']);	
		
		// Set the information back to what it was before the refresh
				// The user search string
		if(isSet($_SESSION['EditBookingUserSearch'])){
			$usersearchstring = $_SESSION['EditBookingUserSearch'];
			unset($_SESSION['EditBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isSet($_SESSION['EditBookingUsersArray'])){
			$users = $_SESSION['EditBookingUsersArray'];
					
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['EditBookingInfoArray']['TheUserID'];	
		
	} else {
		// Get information from database again on the selected booking
		if(!isSet($_SESSION['EditBookingMeetingRoomsArray'])){
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
				
				$_SESSION['EditBookingMeetingRoomsArray'] = $meetingroom;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching meeting room details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();		
			}
		}
		
		if(!isSet($_SESSION['EditBookingInfoArray'])){
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
									b.`adminNote`									AS AdminNote,
									(
										(b.`dateTimeCancelled` IS NOT NULL) 
										OR
										(b.`actualEndDateTime` IS NOT NULL)
									)												AS BookingCompleted,
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
						LIMIT 		1";
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
			$_SESSION['EditBookingInfoArray'] = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditBookingInfoArray']['CreditsRemaining'] = "N/A";
			$_SESSION['EditBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
			$_SESSION['EditBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
			$_SESSION['EditBookingInfoArray']['EndDate'] = "N/A";
			$_SESSION['EditBookingOriginalInfoArray'] = $_SESSION['EditBookingInfoArray'];
		}	
		
		// Set the correct information on form call
		$usersearchstring = '';
		$users = Null;
		$SelectedUserID = $_SESSION['EditBookingInfoArray']['TheUserID'];
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
							c.`dateTimeCreated`,
							c.`startDate`,
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
								AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
								AND			DATE(b.`actualEndDateTime`) < c.`endDate`
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
								AND			DATE(b.`endDateTime`) >= c.`startDate`
								AND			DATE(b.`endDateTime`) < c.`endDate`
							)													AS PotentialExtraMonthlyCompanyWideBookingTimeUsed,
							(
								SELECT 	IFNULL(cc.`altMinuteAmount`, cr.`minuteAmount`)
								FROM 		`company` c
								INNER JOIN	`companycredits` cc
								ON			c.`CompanyID` = cc.`CompanyID`
								INNER JOIN	`credits` cr
								ON			cr.`CreditsID` = cc.`CreditsID`
								WHERE		c.`CompanyID` = e.`CompanyID`
							) 													AS CreditSubscriptionMinuteAmount,
							(
								SELECT 		cr.`overCreditHourPrice`
								FROM		`credits` cr
								INNER JOIN 	`companycredits` cc
								ON			cr.`CreditsID` = cc.`CreditsID`
								WHERE		cc.`CompanyID` = e.`companyID`
								Limit		1
							)													AS CreditHourPriceOverCredits
				FROM 		`user` u
				INNER JOIN 	`employee` e
				ON 			e.`userID` = u.`userID`
				INNER JOIN	`company` c
				ON 			c.`companyID` = e.`companyID`
				WHERE 		u.`userID` = :userID';
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
			$overHourPrice = $row['CreditHourPriceOverCredits'];
			$displayOverHourPrice = convertToCurrency($overHourPrice) . "/h";

			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName'],
								'dateTimeCreated' => $row['dateTimeCreated'],
								'startDate' => $row['startDate'],
								'endDate' => $displayEndDate,
								'creditsGiven' => $companyMinuteCredits,
								'creditsRemaining' => $displayCompanyCreditsRemaining,
								'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
								'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining,
								'HourPriceOverCredit' => $displayOverHourPrice
								);
			$_SESSION['EditBookingCompanyArray'] = $company;
		}

		$pdo = null;

		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isSet($company)){
			if(sizeOf($company)>1){
				// User is in multiple companies

				$_SESSION['EditBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				unset($_SESSION['EditBookingSelectACompany']);
				unset($_SESSION['EditBookingDisplayCompanySelect']);
				$_SESSION['EditBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
				$_SESSION['EditBookingInfoArray']['CreditsRemaining'] = $company[0]['creditsRemaining'];
				$_SESSION['EditBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = $company[0]['PotentialExtraMonthlyTimeUsed'];
				$_SESSION['EditBookingInfoArray']['PotentialCreditsRemaining'] = $company[0]['PotentialCreditsRemaining'];
				$_SESSION['EditBookingInfoArray']['EndDate'] = $company[0]['endDate'];
				$_SESSION['EditBookingInfoArray']['HourPriceOverCredit'] = $company[0]['HourPriceOverCredit'];
				$_SESSION['EditBookingInfoArray']['BookingDescription'] = "Booked for " . $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company

			unset($_SESSION['EditBookingDisplayCompanySelect']);
			unset($_SESSION['EditBookingCompanyArray']);
			$_SESSION['EditBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditBookingInfoArray']['BookedForCompany'] = "";
			$_SESSION['EditBookingInfoArray']['CreditsRemaining'] = "N/A";
			$_SESSION['EditBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
			$_SESSION['EditBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
			$_SESSION['EditBookingInfoArray']['EndDate'] = "N/A";
			$_SESSION['EditBookingInfoArray']['HourPriceOverCredit'] = "N/A";
			$_SESSION['EditBookingInfoArray']['BookingDescription'] = "";
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
	$row = $_SESSION['EditBookingInfoArray'];
	$original = $_SESSION['EditBookingOriginalInfoArray'];
		// Changed user
	if(isSet($_SESSION['EditBookingSelectedNewUser'])){
		
		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];
				$row['sendEmail'] = $user['sendEmail'];
				$row['BookedBy'] = $user['firstName'] . " " . $user['lastName'];

				$_SESSION['EditBookingDefaultDisplayNameForNewUser'] = $user['displayName'];
				$_SESSION['EditBookingDefaultBookingDescriptionForNewUser'] = $user['bookingDescription'];
				break;
			}
		}
	} else {
		$_SESSION['EditBookingDefaultDisplayNameForNewUser'] = $original['UserDefaultDisplayName'];
		$_SESSION['EditBookingDefaultBookingDescriptionForNewUser'] = $original['UserDefaultBookingDescription'];
	}

	// Changed company
	if(isSet($company)){
		foreach($company AS $cmp){
			if($cmp['companyID'] == $row['TheCompanyID']){
				$row['BookedForCompany'] = $cmp['companyName'];
				$row['CreditsRemaining'] = $cmp['creditsRemaining'];
				$row['PotentialExtraMonthlyTimeUsed'] = $cmp['PotentialExtraMonthlyTimeUsed'];
				$row['PotentialCreditsRemaining'] = $cmp['PotentialCreditsRemaining'];
				$row['EndDate'] = $cmp['endDate'];
				$row['HourPriceOverCredit'] = $cmp['HourPriceOverCredit'];
				$row['BookingDescription'] = "Booked for " . $cmp['companyName'];
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
	$meetingroom = $_SESSION['EditBookingMeetingRoomsArray'];
	$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	$startDateTime = $row['StartTime'];
	$endDateTime = $row['EndTime'];
	if(!validateDatetimeWithFormat($startDateTime, DATETIME_DEFAULT_FORMAT_TO_DISPLAY)){
		$startDateTime = convertDatetimeToFormat($startDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	if(!validateDatetimeWithFormat($endDateTime, DATETIME_DEFAULT_FORMAT_TO_DISPLAY)){
		$endDateTime = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	$displayName = $row['BookedBy'];
	$description = $row['BookingDescription'];
	if($row['AdminNote'] === NULL){
		$adminNote = "";
	} else {
		$adminNote = $row['AdminNote'];
	}
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
	if($original['BookedForCompany'] != NULL){
		$originalCompanyName = $original['BookedForCompany'];
	}
	$originalMeetingRoomName = $original['BookedRoomName'];
	if(!isSet($originalMeetingRoomName) OR $originalMeetingRoomName == NULL OR $originalMeetingRoomName == ""){
		$originalMeetingRoomName = "N/A - Deleted";	
	}
	$originalDisplayName = $original['BookedBy'];
	$originalBookingDescription = $original['BookingDescription'];
	if($original['AdminNote'] === NULL){
		$originalAdminNote = "";
	} else {
		$originalAdminNote = $original['AdminNote'];
	}
	$originalUserInformation = 	$original['UserLastname'] . ', ' . $original['UserFirstname'] . 
								' - ' . $original['UserEmail'];
	if(!isSet($originalUserInformation) OR $originalUserInformation == NULL OR $originalUserInformation == ",  - "){
		$originalUserInformation = "N/A - Deleted";	
	}	

	if($original['BookingCompleted'] == 1){
		$bookingHasBeenCompleted = TRUE;
	}

	// Save changes
	$_SESSION['EditBookingInfoArray'] = $row;

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the actual form we want to use
	include 'editbooking.html.php';
	exit();
}

// Admin wants to change the user the booking is reserved for
// We need to get a list of all active users
if((isSet($_POST['edit']) AND $_POST['edit'] == "Change User") OR 
	(isSet($_SESSION['refreshEditBookingChangeUser'])) AND $_SESSION['refreshEditBookingChangeUser']){
	
	if(isSet($_SESSION['refreshEditBookingChangeUser']) AND $_SESSION['refreshEditBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshEditBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['EditBookingUsersArray']);
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isSet($_POST['edit']) AND $_POST['edit'] == "Change User"){
		rememberEditBookingInputs();
	}

	$usersearchstring = "";
	
	if(isSet($_SESSION['EditBookingUserSearch'])){
		$usersearchstring = $_SESSION['EditBookingUserSearch'];
	}	
	
	if(!isSet($_SESSION['EditBookingUsersArray'])){
		// Get all active users and their default booking information
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();

			// New SQL that only gets users that are registered in a company (an employee)
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`,
							`sendEmail`
					FROM 	`user`
					WHERE 	`isActive` > 0
					AND		`userID`
					IN	(
							SELECT 	DISTINCT `userID`
							FROM 	`employee`
						)";
			if ($usersearchstring != ''){
				$sqladd = " AND (`firstname` LIKE :search
							OR `lastname` LIKE :search
							OR `email` LIKE :search)";
				$sql = $sql . $sqladd;
				
				$finalusersearchstring = '%' . $usersearchstring . '%';
				
				$s = $pdo->prepare($sql);
				$s->bindValue(":search", $finalusersearchstring);
				$s->execute();
				$result = $s->fetchAll(PDO::FETCH_ASSOC);
				
			} else {
				$return = $pdo->query($sql);
				$result = $return->fetchAll(PDO::FETCH_ASSOC);
			}	
			
			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$users[] = array(
									'userID' => $row['userID'],
									'lastName' => $row['lastname'],
									'firstName' => $row['firstname'],
									'email' => $row['email'],
									'userInformation' => $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'],
									'displayName' => $row['displayname'],
									'bookingDescription' => $row['bookingdescription'],
									'sendEmail' => $row['sendEmail']
									);
			}
			$_SESSION['EditBookingUsersArray'] = $users;
			
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error fetching user details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
	} else {
		$users = $_SESSION['EditBookingUsersArray'];
	}

	$_SESSION['refreshEditBooking'] = TRUE;
	$_SESSION['EditBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// Admin wants to increase the start timer by minimum allowed time (e.g. 15 min)
if(isSet($_POST['edit']) AND $_POST['edit'] == "Increase Start By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditBookingInputs();
	
	$startTime = $_SESSION['EditBookingInfoArray']['StartTime'];
	$correctStartTime = correctDatetimeFormat($startTime);
	$_SESSION['EditBookingInfoArray']['StartTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	if($_SESSION['EditBookingInfoArray']['StartTime'] == $_SESSION['EditBookingInfoArray']['EndTime']){
		$endTime = $_SESSION['EditBookingInfoArray']['EndTime'];
		$correctEndTime = correctDatetimeFormat($endTime);
		$_SESSION['EditBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// Admin wants to increase the end timer by minimum allowed time (e.g. 15 min)
if(isSet($_POST['edit']) AND $_POST['edit'] == "Increase End By Minimum"){
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditBookingInputs();
	
	$endTime = $_SESSION['EditBookingInfoArray']['EndTime'];
	$correctEndTime = correctDatetimeFormat($endTime);
	$_SESSION['EditBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// Admin confirms what user he wants the booking to be for.
if(isSet($_POST['edit']) AND $_POST['edit'] == "Select This User"){
	
	// We haven't set the company if we are changing the user.
	$_SESSION['EditBookingSelectACompany'] = TRUE;

	// We no longer need to be able to change the user
	unset($_SESSION['EditBookingChangeUser']);
	
	// Remember that we've selected a new user
	$_SESSION['EditBookingSelectedNewUser'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

//	Admin wants to change the company the booking is for (after having already selected it)
if(isSet($_POST['edit']) AND $_POST['edit'] == "Change Company"){
	
	// We want to select a company again
	$_SESSION['EditBookingSelectACompany'] = TRUE;
	
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isSet($_POST['edit']) AND $_POST['edit'] == "Select This Company"){

	// Remember that we've selected a new company
	unset($_SESSION['EditBookingSelectACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin is editing a booking and wants to limit the users shown by searching
if(isSet($_SESSION['EditBookingChangeUser']) AND isSet($_POST['edit']) AND $_POST['edit'] == "Search"){
	
	// Let's remember what was selected and searched for
		// The user search string
	$_SESSION['EditBookingUserSearch'] = $_POST['usersearchstring'];

	rememberEditBookingInputs();
	
	// Get the new users
	$_SESSION['refreshEditBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// If admin wants to get the default values for the user's display name
if(isSet($_POST['edit']) AND $_POST['edit'] == "Get Default Display Name"){
	  
	$displayName = $_SESSION['EditBookingDefaultDisplayNameForNewUser'];
	if(isSet($_SESSION['EditBookingInfoArray'])){

		rememberEditBookingInputs();

		if($displayName != ""){
			if($displayName != $_SESSION['EditBookingInfoArray']['BookedBy']){
					// The user selected
				$_SESSION['EditBookingInfoArray']['BookedBy'] = $displayName;

				unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);				
			} else {
				// Description was already the default booking description
				$_SESSION['EditBookingError'] = "This is already the user's default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['EditBookingError'] = "This user has no default display name.";
			$_SESSION['EditBookingInfoArray']['BookedBy'] = "";
		}		
	}

	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to get the default values for the user's booking description
if(isSet($_POST['edit']) AND $_POST['edit'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['EditBookingDefaultBookingDescriptionForNewUser'];
	if(isSet($_SESSION['EditBookingInfoArray'])){
		
		rememberEditBookingInputs();

		if($bookingDescription != ""){
			if($bookingDescription != $_SESSION['EditBookingInfoArray']['BookingDescription']){
				
					// Set the default booking description
				$_SESSION['EditBookingInfoArray']['BookingDescription'] = $bookingDescription;
	
				unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);			
			} else {
				// Description was already the default booking description
				$_SESSION['EditBookingError'] = "This is already the user's default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['EditBookingError'] = "This user has no default booking description.";
			$_SESSION['EditBookingInfoArray']['BookingDescription'] = "";
		}
	}
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the values back to the original values while editing
if (isSet($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditBookingInfoArray'] = $_SESSION['EditBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your booking editing.";
}


// If admin wants to update the booking information after editing
if( (isSet($_POST['edit']) AND $_POST['edit'] == "Finish Edit") OR 
	((isSet($_SESSION['refreshEditBookingConfirmed']) AND $_SESSION['refreshEditBookingConfirmed'])))
{
	$originalValue = $_SESSION['EditBookingOriginalInfoArray'];
	
	if($originalValue['BookingCompleted'] == 1){
		$bookingCompleted = TRUE;
	} else {
		$bookingCompleted = FALSE;
	}

	if(!isSet($_SESSION['refreshEditBookingConfirmed'])){
		// Validate user inputs
		list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname, $validatedAdminNote) = validateUserInputs('EditBookingError', TRUE, $bookingCompleted);

		// We want to check if a booking is in the correct minute slice e.g. 15 minute increments.
			// We check both start and end time for online/admin bookings
			// We do this for editing only if the times have changed from their original values.
		if(!$bookingCompleted){
			$compareStartDate = convertDatetimeToFormat($startDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$compareEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$originalStartDateTime = $originalValue['StartTime'];
			$compareOriginalStartDate = convertDatetimeToFormat($originalStartDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$originalEndDateTime = $originalValue['EndTime'];
			$compareOriginalEndDate = convertDatetimeToFormat($originalEndDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			if($compareStartDate != $compareOriginalStartDate AND !$invalidInput){
				$invalidStartTime = isBookingDateTimeMinutesInvalid($startDateTime);
				if($invalidStartTime AND !$invalidInput){
					$_SESSION['EditBookingError'] = "Your start time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
					$invalidInput = TRUE;	
				}
			}
			if($compareEndDate != $compareOriginalEndDate AND !$invalidInput){	
				$invalidEndTime = isBookingDateTimeMinutesInvalid($endDateTime);
				if($invalidEndTime AND !$invalidInput){
					$_SESSION['EditBookingError'] = "Your end time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
					$invalidInput = TRUE;	
				}
			}
			if( (($compareStartDate != $compareOriginalStartDate) OR
				($compareEndDate != $compareOriginalEndDate)) AND
				!$invalidInput){
				// We want to check if the booking is the correct minimum length
				$invalidBookingLength = isBookingTimeDurationInvalid($startDateTime, $endDateTime);
				if($invalidBookingLength AND !$invalidInput){
					$_SESSION['EditBookingError'] = "Your start time and end time needs to have at least a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes difference.";
					$invalidInput = TRUE;		
				}
			}
		}
		
		if($invalidInput){
			
			rememberEditBookingInputs();
			// Refresh.
			if(isSet($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
				$_SESSION['refreshEditBookingChangeUser'] = TRUE;				
			} else {
				$_SESSION['refreshEditBooking'] = TRUE;	
			}
			header('Location: .');
			exit();
		}

		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
		} else {
			$meetingRoomID = $_POST['meetingRoomID'];
		}

		// Set correct companyID
		if(isSet($_POST['companyID']) AND !empty($_POST['companyID'])){
			$companyID = $_POST['companyID'];
		} else {
			// TO-DO: Give error since there's no companyID?
			$companyID = NULL;
		}
		
		if(isSet($_POST['userID']) AND !empty($_POST['userID'])){
			$userID = $_POST['userID'];
		}

		$_SESSION['EditBookingInfoArray']['TheCompanyID'] = $companyID;
		$_SESSION['EditBookingInfoArray']['TheUserID'] = $userID;
		$_SESSION['EditBookingInfoArray']['TheMeetingRoomID'] = $meetingRoomID;
		$_SESSION['EditBookingInfoArray']['StartTime'] = $startDateTime;
		$_SESSION['EditBookingInfoArray']['EndTime'] = $endDateTime;
		$_SESSION['EditBookingInfoArray']['BookedBy'] = $dspname;
		$_SESSION['EditBookingInfoArray']['BookingDescription'] = $bknDscrptn;
		$_SESSION['EditBookingInfoArray']['AdminNote'] = $validatedAdminNote;
	} else {
		$companyID = $_SESSION['EditBookingInfoArray']['TheCompanyID'];
		$userID = $_SESSION['EditBookingInfoArray']['TheUserID'];
		$meetingRoomID = $_SESSION['EditBookingInfoArray']['TheMeetingRoomID'];
		$startDateTime = $_SESSION['EditBookingInfoArray']['StartTime'];
		$endDateTime = $_SESSION['EditBookingInfoArray']['EndTime'];
		$dspname = $_SESSION['EditBookingInfoArray']['BookedBy'];
		$bknDscrptn = $_SESSION['EditBookingInfoArray']['BookingDescription'];
		$validatedAdminNote = $_SESSION['EditBookingInfoArray']['AdminNote'];
	}

	$bookingID = $_SESSION['EditBookingInfoArray']['TheBookingID'];

	// Check if any values actually changed. If not, we don't need to bother the database
	$numberOfChanges = 0;
	$checkIfTimeslotIsAvailable = FALSE;
	$newMeetingRoom = FALSE;
	$newStartTime = FALSE;
	$newEndTime = FALSE;
	$newUser = FALSE;
	$newCompany = FALSE;

	if(!$bookingCompleted){
		if($compareStartDate != $compareOriginalStartDate){
			$numberOfChanges++;
			$newStartTime = TRUE;
		}
		if($compareEndDate != $compareOriginalEndDate){
			$numberOfChanges++;
			$newEndTime = TRUE;
		}
	}

	if($companyID != $originalValue['TheCompanyID']){
		$numberOfChanges++;
		$newCompany = TRUE;
	}
	if($dspname != $originalValue['BookedBy']){
		$numberOfChanges++;
	}
	if($bknDscrptn != $originalValue['BookingDescription']){
		$numberOfChanges++;
	}
	if($meetingRoomID != $originalValue['TheMeetingRoomID']){
		$numberOfChanges++;
		$newMeetingRoom = TRUE;
	}
	if($userID != $originalValue['TheUserID']){
		$numberOfChanges++;
		$newUser = TRUE;
	}
	if(empty($validatedAdminNote)){
		$validatedAdminNote = NULL;
	}
	if($validatedAdminNote != $originalValue['AdminNote']){
		$numberOfChanges++;
	}

	if($numberOfChanges == 0){
		// There were no changes made. Go back to booking overview
		$_SESSION['BookingUserFeedback'] = "No changes were made to the booking.";
		header('Location: .');
		exit();	
	}

	// Check if we need to check the timeslot or if we can just update the booking
		// If we changed the meeting room
	if($newMeetingRoom){
		$checkIfTimeslotIsAvailable = TRUE;
	}

	$oldStartTime = $originalValue['StartTime'];
	$oldEndTime = $originalValue['EndTime'];

	if(!$bookingCompleted){
			// If we set the start time earlier than before or
			// If we set the start time later than the previous end time
		if( ($newStartTime AND $startDateTime < $oldStartTime) OR 
			($newStartTime AND $startDateTime >= $oldEndTime)){
			$checkIfTimeslotIsAvailable = TRUE;
		}
			// If we set the end time later than before or
			// If we set the end time earlier than the previous start time
		if( ($newEndTime AND $endDateTime > $oldEndTime) OR 
			($newEndTime AND $endDateTime <= $oldStartTime)){
			$checkIfTimeslotIsAvailable = TRUE;
		}

		// Check if the timeslot is taken for the selected meeting room
		// and ignore our own booking since it's the one we're editing
		if($checkIfTimeslotIsAvailable){
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
								AND			b.`bookingID` != :BookingID
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
				$s->bindValue(':BookingID', $bookingID);
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
				rememberEditBookingInputs();

				$_SESSION['EditBookingError'] = "The booking couldn't be made. The timeslot is already taken for this meeting room.";

				if(isSet($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
					$_SESSION['refreshEditBookingChangeUser'] = TRUE;
				} else {
					$_SESSION['refreshEditBooking'] = TRUE;	
				}
				header('Location: .');
				exit();	
			}
		}
	} else {
		$startDateTime = $oldStartTime;
		$endDateTime = $oldEndTime;
	}

	if(!$bookingCompleted){
		// We know it's available. Let's check if this booking makes the company go over credits.
		// If over credits, ask for a confirmation before creating the booking
		$NewStartDate = convertDatetimeToFormat($startDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$NewEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$OldStartDate = convertDatetimeToFormat($oldStartTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$OldEndDate = convertDatetimeToFormat($oldEndTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		$dateOnlyEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', 'Y-m-d');
		$timeBookedInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime,$endDateTime);
		$oldTimeBookedInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($oldStartTime,$oldEndTime);	

		// Meeting room name(s)
		$oldMeetingRoomName = $originalValue['BookedRoomName'];
		if($newMeetingRoom){
			// Get meeting room name
			foreach ($_SESSION['EditBookingMeetingRoomsArray'] AS $room){
				if($room['meetingRoomID'] == $meetingRoomID){
					$newMeetingRoomName = $room['meetingRoomName'];
					break;
				}
			}
		} else {
			$newMeetingRoomName = $oldMeetingRoomName;
		}

		// Get company information
		$companyName = 'N/A';
		if(isSet($companyID)){
			foreach($_SESSION['EditBookingCompanyArray'] AS $company){
				if($companyID == $company['companyID']){
					$companyName = $company['companyName'];
					$companyCreationDate = $company['dateTimeCreated'];
					$companyCreditsRemaining = $company['creditsRemaining'];
					$companyCreditsBooked = $company['PotentialExtraMonthlyTimeUsed'];
					$companyCreditsPotentialMinimumRemaining = $company['PotentialCreditsRemaining'];
					$companyCreditsPotentialMinimumRemainingInMinutes = convertHoursAndMinutesToMinutes($companyCreditsPotentialMinimumRemaining);
					$displayCompanyPeriodEndDate = $company['endDate']; //Display format
					$companyPeriodEndDate = convertDatetimeToFormat($displayCompanyPeriodEndDate, DATE_DEFAULT_FORMAT_TO_DISPLAY, 'Y-m-d');
					$companyPeriodStartDate = $company['startDate'];
					$companyHourPriceOverCredits = $company['HourPriceOverCredit'];
					$companyMinuteCredits = $company['creditsGiven'];
					break;
				}
			}
		}	

		$bookingWentOverCredits = FALSE;
		$firstTimeOverCredit = FALSE;
		$addExtraLogEventDescription = FALSE;
		$newPeriod = FALSE;

		// Only go through the process of checking credits etc if we've changed anything about the booking time or company
		if($newCompany OR $newStartTime OR $newEndTime){
			// Check if the booking that was made was for the current period.
			if($dateOnlyEndDate <= $companyPeriodEndDate){

				// Credits remaining calculation depends what company we selected
				if(!$newCompany){
					$companyCreditsPotentialMinimumRemainingInMinutes += $oldTimeBookedInMinutes;
				}

				if($companyCreditsPotentialMinimumRemainingInMinutes < 0){
					// Company was already over given credits
					$bookingWentOverCredits = TRUE;
					$minutesOverCredits = -($companyCreditsPotentialMinimumRemainingInMinutes) + $timeBookedInMinutes;
					$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
					$addExtraLogEventDescription = TRUE;
				} elseif($timeBookedInMinutes > $companyCreditsPotentialMinimumRemainingInMinutes){
					// This booking, if completed, will put the company over their given credits
					$bookingWentOverCredits = TRUE;
					$firstTimeOverCredit = TRUE;
					$minutesOverCredits = $timeBookedInMinutes - $companyCreditsPotentialMinimumRemainingInMinutes;
					$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
					$addExtraLogEventDescription = TRUE;
				}
			} else {
				$newPeriod = TRUE;
				// Get exact period the user is booking for
				date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
				$newDate = DateTime::createFromFormat("Y-m-d H:i:s", $companyCreationDate);
				$dayNumberToKeep = $newDate->format("d");

				list($newCompanyPeriodStart, $newCompanyPeriodEnd) = getPeriodDatesForCompanyFromDateSubmitted($dayNumberToKeep, $dateOnlyEndDate, $companyPeriodStartDate, $companyPeriodEndDate);

				// For displaying the new period dates
				$periodStartDate = convertDatetimeToFormat($newCompanyPeriodStart, "Y-m-d", DATE_DEFAULT_FORMAT_TO_DISPLAY);
				$periodEndDate = convertDatetimeToFormat($newCompanyPeriodEnd, "Y-m-d", DATE_DEFAULT_FORMAT_TO_DISPLAY);

				// Get booking time used so far for the future period
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

					$pdo = connect_to_db();
					$sql = 'SELECT (BIG_SEC_TO_TIME(SUM(
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
							)))	AS PotentialBookingTimeUsed
							FROM 		`booking` b 
							WHERE 		b.`CompanyID` = :companyID
							AND 		DATE(b.`endDateTime`) >= :newStartPeriod
							AND			DATE(b.`endDateTime`) < :newEndPeriod
							AND 		b.`actualEndDateTime` IS NULL
							AND			b.`dateTimeCancelled` IS NULL
							AND			b.`bookingID` <> :bookingID';
					$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
					$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s	

					$s = $pdo->prepare($sql);
					$s->bindValue(':companyID', $companyID);
					$s->bindValue(':bookingID', $bookingID);
					$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
					$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
					$s->bindValue(':newStartPeriod', $newCompanyPeriodStart);
					$s->bindValue(':newEndPeriod', $newCompanyPeriodEnd);
					$s->execute();
					$row = $s->fetch(PDO::FETCH_ASSOC);

					if(isSet($row['PotentialBookingTimeUsed']) AND !empty($row['PotentialBookingTimeUsed'])){
						$timeBookedSoFarThisPeriod = convertTimeToMinutes($row['PotentialBookingTimeUsed']);
					} else {
						$timeBookedSoFarThisPeriod = 0;
					}

					// Add the time in minutes for the selected booking to the period time
					$totalTimeBookedSoFarThisPeriod = $timeBookedSoFarThisPeriod + $timeBookedInMinutes;
					$totalTimeBookedInTime = convertMinutesToHoursAndMinutes($totalTimeBookedSoFarThisPeriod);
		
					// Previous booking time depends what company we selected
					if(!$newCompany){
						$previousTotalTimeBookedSoFarThisPeriod = $timeBookedSoFarThisPeriod + $oldTimeBookedInMinutes;
					} else {
						$previousTotalTimeBookedSoFarThisPeriod = $timeBookedSoFarThisPeriod;
					}

					if($totalTimeBookedSoFarThisPeriod > $companyMinuteCredits){
						$bookingWentOverCredits = TRUE;
						if($previousTotalTimeBookedSoFarThisPeriod <= $companyMinuteCredits){
							$firstTimeOverCredit = TRUE;
						}
						$minutesOverCredits = $totalTimeBookedSoFarThisPeriod - $companyMinuteCredits;
						$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
						$addExtraLogEventDescription = TRUE;
					}

					$pdo = null;
				}
				catch(PDOException $e)
				{
					$error = 'Error fetching future booking details: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}
			}
		}
	}
	

	// Send user to the confirmation template if needed
	if($bookingWentOverCredits AND !isSet($_SESSION['refreshEditBookingConfirmed'])){
		var_dump($_SESSION); // TO-DO: Remove before uploading
		include_once 'confirmbooking.html.php';
		exit();
	}

	// Generate new cancellation code if we change users
	if($newUser){
		$cancellationCode = generateCancellationCode();
	} else {
		$cancellationCode = $originalValue['CancellationCode'];
	}

	// Update booking information because values have changed!
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "UPDATE	`booking`
				SET 	`meetingRoomID` = :meetingRoomID,
						`userID` = :userID,
						`companyID` = :companyID,
						`startDateTime` = :startDateTime,
						`endDateTime` = :endDateTime,
						`displayName` = :displayName,
						`description` = :description,
						`adminNote`	= :adminNote,
						`cancellationCode` = :cancellationCode
				WHERE	`bookingID` = :BookingID";
		$s = $pdo->prepare($sql);

		$s->bindValue(':BookingID', $bookingID);
		$s->bindValue(':meetingRoomID', $meetingRoomID);
		$s->bindValue(':userID', $userID);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':displayName', $dspname);
		$s->bindValue(':description', $bknDscrptn);
		$s->bindValue(':adminNote', $validatedAdminNote);
		$s->bindValue(':cancellationCode', $cancellationCode);

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

	if(isSet($_SESSION['BookingUserFeedback'])){
		$_SESSION['BookingUserFeedback'] .= "Successfully updated the booking information!";
	} else {
		$_SESSION['BookingUserFeedback'] = "Successfully updated the booking information!";
	}

	// Send email to users affected (if changed) or company owners if over credit.
		// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email
	if(!$bookingCompleted){
		// Check if we're sending email to the user(s) the meetings are/were booked for.
		if($_SESSION['EditBookingInfoArray']['sendEmail'] == 1 OR $originalValue['sendEmail'] == 1){

			// Email information
			if($newUser){

				if($_SESSION['EditBookingInfoArray']['sendEmail'] == 1){
					// Send information to new user about meeting
					$emailSubject = "You have been assigned a booked meeting!";

					$emailMessage = 
					"A booked meeting has been assigned to you by an Admin!\n" .
					"The meeting has been set to: \n" .
					"Meeting Room: " . $newMeetingRoomName . ".\n" . 
					"Start Time: " . $NewStartDate . ".\n" .
					"End Time: " . $NewEndDate . ".\n" .
					"For the Company: " . $companyName . "\n\n" .
					"If you wish to cancel this meeting, or just end it early, you can easily do so by using the link given below.\n" .
					"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
					"/booking/?cancellationcode=" . $cancellationCode;

					if($bookingWentOverCredits){
						// Add time over credits and the price per hour the company subscription has.
						$emailMessage .= "\n\nWarning: If this booking is completed the company it is booked for will be $timeOverCredits over the given free booking time.\nThis will result in a cost of $companyHourPriceOverCredits";
					}

					$email = $_SESSION['EditBookingInfoArray']['UserEmail'];

					$mailResult = sendEmail($email, $emailSubject, $emailMessage);

					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

						// Email failed to be prepared. Store it in database to try again later
						try
						{
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

							$pdo = connect_to_db();
							$sql = 'INSERT INTO	`email`
									SET			`subject` = :subject,
												`message` = :message,
												`receivers` = :receivers,
												`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
							$s = $pdo->prepare($sql);
							$s->bindValue(':subject', $emailSubject);
							$s->bindValue(':message', $emailMessage);
							$s->bindValue(':receivers', $email);
							$s->execute();

							//close connection
							$pdo = null;
						}
						catch (PDOException $e)
						{
							$error = 'Error storing email: ' . $e->getMessage();
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
							$pdo = null;
							exit();
						}

						$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
					}

					$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing				
				}

				if($originalValue['sendEmail'] == 1){
					// Send information to old user that their meeting has been cancelled/transferred
					$emailSubject = "Your meeting has been cancelled by an Admin!";

					$emailMessage = 
					"Your booked meeting has been cancelled by an Admin!\n" .
					"The meeting you had booked for: \n" .
					"Meeting Room: " . $oldMeetingRoomName . ".\n" . 
					"Start Time: " . $OldStartDate . ".\n" .
					"End Time: " . $OldEndDate . ".\n" .
					"Is no longer active.";

					$email = $originalValue['UserEmail'];

					$mailResult = sendEmail($email, $emailSubject, $emailMessage);

					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

						// Email failed to be prepared. Store it in database to try again later
						try
						{
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

							$pdo = connect_to_db();
							$sql = 'INSERT INTO	`email`
									SET			`subject` = :subject,
												`message` = :message,
												`receivers` = :receivers,
												`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
							$s = $pdo->prepare($sql);
							$s->bindValue(':subject', $emailSubject);
							$s->bindValue(':message', $emailMessage);
							$s->bindValue(':receivers', $email);
							$s->execute();

							//close connection
							$pdo = null;
						}
						catch (PDOException $e)
						{
							$error = 'Error storing email: ' . $e->getMessage();
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
							$pdo = null;
							exit();
						}

						$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
					}

					$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing				
				} else {
					$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent an Email.";
				}
			} elseif($originalValue['TheUserID'] == $_SESSION['LoggedInUserID']){
				$_SESSION['BookingUserFeedback'] .= "\nDid not send an email with the updated information, since you changed your own booking."; // TO-DO: Remove?
			} else {
				if($originalValue['sendEmail'] == 1){
					$emailSubject = "Booking Information Has Changed!";

					$emailMessage = 
					"Your booked meeting has been altered by an Admin!\n" .
					"Your new booking has been set to: \n" .
					"Meeting Room: " . $newMeetingRoomName . ".\n" . 
					"Start Time: " . $NewStartDate . ".\n" .
					"End Time: " . $NewEndDate . ".\n\n" .
					"Your original booking was for: \n" .
					"Meeting Room: " . $oldMeetingRoomName . ".\n" . 
					"Start Time: " . $OldStartDate . ".\n" .
					"End Time: " . $OldEndDate . ".\n\n" .
					"If you wish to cancel your meeting, or just end it early, you can easily do so by using the link given below.\n" .
					"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] .
					"/booking/?cancellationcode=" . $cancellationCode;
					
					if($bookingWentOverCredits){
						// Add time over credits and the price per hour the company subscription has.
						$emailMessage .= "\n\nWarning: If this booking is completed the company it is booked for will be $timeOverCredits over the given free booking time.\nThis will result in a cost of $companyHourPriceOverCredits";
					}

					$email = $originalValue['UserEmail'];

					$mailResult = sendEmail($email, $emailSubject, $emailMessage);

					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

						// Email failed to be prepared. Store it in database to try again later
						try
						{
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

							$pdo = connect_to_db();
							$sql = 'INSERT INTO	`email`
									SET			`subject` = :subject,
												`message` = :message,
												`receivers` = :receivers,
												`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
							$s = $pdo->prepare($sql);
							$s->bindValue(':subject', $emailSubject);
							$s->bindValue(':message', $emailMessage);
							$s->bindValue(':receivers', $email);
							$s->execute();

							//close connection
							$pdo = null;
						}
						catch (PDOException $e)
						{
							$error = 'Error storing email: ' . $e->getMessage();
							include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
							$pdo = null;
							exit();
						}

						$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
					}

					$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing	
				} else {
					$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent an Email.";
				}
			}
		}

		// Send email to alert company owner(s) that a booking was made that is over credits.
			// Check if any owners want to receive an email
		if($bookingWentOverCredits){
			try
			{
				if($newUser){
					$userEmail = $_SESSION['EditBookingInfoArray']['UserEmail'];
					$bookedForUserName = $_SESSION['EditBookingInfoArray']['UserLastname'] . ", " . $_SESSION['EditBookingInfoArray']['UserFirstname'];
				} else {
					$userEmail = $originalValue['UserEmail'];
					$bookedForUserName = $originalValue['UserLastname'] . ", " . $originalValue['UserFirstname'];
				}

				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

				$pdo = connect_to_db();

				$sql = 'SELECT		u.`email`					AS Email,
									e.`sendEmail`				AS SendOwnerEmail,
									e.`sendEmailOnceOrAlways`	AS SendEmailOnceOrAlways
						FROM 		`user` u
						INNER JOIN	`employee` e
						ON 			e.`UserID` = u.`UserID`
						INNER JOIN	`company` c
						ON 			c.`CompanyID` = e.`CompanyID`
						INNER JOIN	`companyposition` cp
						ON			e.`PositionID` = cp.`PositionID`
						WHERE 		c.`CompanyID` = :CompanyID
						AND			cp.`name` = "Owner"
						AND			u.`email` <> :UserEmail';

				$s = $pdo->prepare($sql);
				$s->bindValue(':CompanyID', $companyID);
				$s->bindValue(':UserEmail', $userEmail);
				$s->execute();
				$result = $s->fetchAll(PDO::FETCH_ASSOC);

				if(isSet($result) AND !empty($result)){
					foreach($result AS $row){
						// Check if user wants to receive owner emails
						if($row['SendOwnerEmail'] == 1){ 
							// Check if user wants to receive all emails or just the first time booking goes over credit per period
								// sendEmailOnceOrAlways: 1 = always, sendEmailOnceOrAlways: 0 = once.
							if($row['SendEmailOnceOrAlways'] == 1 OR ($row['SendEmailOnceOrAlways'] == 0 AND $firstTimeOverCredit)){ 
								$companyOwnerEmails[] = $row['Email'];
							}
						}
					}
				}

				//Close the connection
				$pdo = null;
			}
			catch (PDOException $e)
			{
				$error = 'Error sending email to company owner(s): ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

				// We found company owners to send email too
			if(isSet($companyOwnerEmails) AND !empty($companyOwnerEmails)){
				// TO-DO: This might need some change since it's an edit and not a fresh booking.
				$emailSubject = "Booked Meeting Above Credits!";

				$emailMessage = 
				"The employee: " . $bookedForUserName . "\n" .
				"In your company: " . $companyName . "\n" .
				"Has been assigned a meeting, by an admin, that will put your company above the treshold of free booking time this period.\n" .
				"If this booking is completed your company will be $timeOverCredits over the treshold.\nThis will result in a cost of $companyHourPriceOverCredits\n\n" .
				"The meeting has been set to: \n" .
				"Meeting Room: " . $newMeetingRoomName . ".\n" . 
				"Start Time: " . $NewStartDate . ".\n" .
				"End Time: " . $NewEndDate . ".\n\n" .
				"If you wish to cancel this meeting, or just end it early, you can easily do so by using the link given below.\n" .
				"Click this link to cancel the booked meeting: " . $_SERVER['HTTP_HOST'] . 
				"/booking/?cancellationcode=" . $cancellationCode . "\n\n" . 
				"If you do not wish to receive these emails, you can disable them in 'My Account' under 'Company Owner Alert Status'.";

				$email = $companyOwnerEmails;

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				$email = implode(", ", $email);

				if(!$mailResult){
					$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user(s).";

					// Email failed to be prepared. Store it in database to try again later
					try
					{
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

						$pdo = connect_to_db();
						$sql = 'INSERT INTO	`email`
								SET			`subject` = :subject,
											`message` = :message,
											`receivers` = :receivers,
											`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
						$s = $pdo->prepare($sql);
						$s->bindValue(':subject', $emailSubject);
						$s->bindValue(':message', $emailMessage);
						$s->bindValue(':receivers', $email);
						$s->execute();

						//close connection
						$pdo = null;
					}
					catch (PDOException $e)
					{
						$error = 'Error storing email: ' . $e->getMessage();
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
						$pdo = null;
						exit();
					}

					$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
				}

				$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading			
			} else {
				$_SESSION['BookingUserFeedback'] .= "\n\nNo Company Owners were sent an email about the booking going over booking."; // TO-DO: Remove before uploading.
			}
		}
	}
	
	// Add log event that meeting was created/removed? Changing user/meetingroom etc.

	clearEditBookingSessions();

	// Load booking history list webpage with the updated booking information
	header('Location: .');
	exit();	
}

if(isSet($_POST['Edit']) AND $_POST['Edit'] == "Yes, Edit The Booking"){
	$_SESSION['refreshEditBookingConfirmed'] = TRUE;
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/bookings/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}

if(isSet($_POST['Edit']) AND $_POST['Edit'] == "No, Cancel The Edit"){
	$_SESSION['BookingUserFeedback'] = "You cancelled your booking editing.";
	unset($_SESSION['refreshEditBookingConfirmed']);
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/bookings/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}
// EDIT CODE SNIPPET END //




// ADD CODE SNIPPET START //

// If admin wants to add a booked meeting to the database
// we load a new html form
if (	(isSet($_POST['action']) AND $_POST['action'] == "Create Booking") OR 
		(isSet($_SESSION['refreshAddBooking']) AND $_SESSION['refreshAddBooking'])
		){

	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshAddBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddBooking']);

		// Set the information back to what it was before the refresh
				// The user search string
		if(isSet($_SESSION['AddBookingUserSearch'])){
			$usersearchstring = $_SESSION['AddBookingUserSearch'];
			unset($_SESSION['AddBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isSet($_SESSION['AddBookingUsersArray'])){
			$users = $_SESSION['AddBookingUsersArray'];
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];

	} else {
		// Get information from database on booking information user can choose between
		if(!isSet($_SESSION['AddBookingInfoArray'])){
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
				$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
				$s->execute();

				// Create an array with the row information we retrieved
				$result = $s->fetch(PDO::FETCH_ASSOC);
				$notInACompany = FALSE;
				if($result['HitCount'] == 0){
					// User is not working in a company. We can't let them book for themself.
					$notInACompany = TRUE;
					$_SESSION['AddBookingUserCannotBookForSelf'] = TRUE;
				} else {
					// Set default booking display name and booking description
					if($result['displayname']!=NULL){
						$displayName = $result['displayname'];
					}

					if($result['bookingdescription']!=NULL){
						$description = $result['bookingdescription'];
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
			$_SESSION['AddBookingInfoArray'] = array(
														'TheCompanyID' => '',
														'TheMeetingRoomID' => '',
														'StartTime' => '',
														'EndTime' => '',
														'BookingDescription' => '',
														'AdminNote' => '',
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
			if(!$notInACompany){
				$_SESSION['AddBookingInfoArray']['UserDefaultBookingDescription'] = $description;
				$_SESSION['AddBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
				$_SESSION['AddBookingInfoArray']['UserFirstname'] = $firstname;
				$_SESSION['AddBookingInfoArray']['UserLastname'] = $lastname;
				$_SESSION['AddBookingInfoArray']['UserEmail'] = $email;
				$_SESSION['AddBookingInfoArray']['sendEmail'] = $sendEmail;
				$_SESSION['AddBookingInfoArray']['BookedBy'] = $firstname . " " . $lastname;
				$_SESSION['AddBookingInfoArray']['TheUserID'] = $_SESSION['LoggedInUserID'];
			}
			$_SESSION['AddBookingOriginalInfoArray'] = $_SESSION['AddBookingInfoArray'];
		}

		// Set the correct information on form call
		$usersearchstring = '';
		$users = Null;
		$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];

		if(!isSet($_SESSION['AddBookingMeetingRoomsArray'])){
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

				// Get booking information
				$pdo = connect_to_db();
				// Get name and IDs for meeting rooms
				$sql = 'SELECT 		`meetingRoomID`,
									`name` 
						FROM 		`meetingroom`
						ORDER BY	`name`';
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

				$_SESSION['AddBookingMeetingRoomsArray'] = $meetingroom;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching meeting room details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}
		}
	}

		// Check if we need a company select for the booking
	if(isSet($SelectedUserID) AND !empty($SelectedUserID)){
		try
		{
			// We want the companies the user works for to decide if we need to
			// have a dropdown select or just a fixed value (with 0 or 1 company)
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT		c.`companyID`,
								c.`name` 					AS companyName,
								c.`dateTimeCreated`,
								c.`startDate`,
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
									AND			DATE(b.`actualEndDateTime`) >= c.`startDate`
									AND			DATE(b.`actualEndDateTime`) < c.`endDate`
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
									AND			DATE(b.`endDateTime`) >= c.`startDate`
									AND			DATE(b.`endDateTime`) < c.`endDate`
								)													AS PotentialExtraMonthlyCompanyWideBookingTimeUsed,
								(
									SELECT 		IFNULL(cc.`altMinuteAmount`, cr.`minuteAmount`)
									FROM 		`company` c
									INNER JOIN	`companycredits` cc
									ON			c.`CompanyID` = cc.`CompanyID`
									INNER JOIN	`credits` cr
									ON			cr.`CreditsID` = cc.`CreditsID`
									WHERE		c.`CompanyID` = e.`CompanyID`
								) 													AS CreditSubscriptionMinuteAmount,
								(
									SELECT 		cr.`overCreditHourPrice`
									FROM		`credits` cr
									INNER JOIN 	`companycredits` cc
									ON			cr.`CreditsID` = cc.`CreditsID`
									WHERE		cc.`CompanyID` = e.`companyID`
									Limit		1
								)													AS CreditHourPriceOverCredits
					FROM 		`user` u
					INNER JOIN 	`employee` e
					ON 			e.`userID` = u.`userID`
					INNER JOIN	`company` c
					ON 			c.`companyID` = e.`companyID`
					WHERE 		u.`userID` = :userID
					ORDER BY	c.`name`';
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
				$overHourPrice = $row['CreditHourPriceOverCredits'];
				$displayOverHourPrice = convertToCurrency($overHourPrice) . "/h";

				$company[] = array(
									'companyID' => $row['companyID'],
									'companyName' => $row['companyName'],
									'dateTimeCreated' => $row['dateTimeCreated'],
									'startDate' => $row['startDate'],
									'endDate' => $displayEndDate,
									'creditsGiven' => $companyMinuteCredits,
									'creditsRemaining' => $displayCompanyCreditsRemaining,
									'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
									'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining,
									'HourPriceOverCredit' => $displayOverHourPrice
									);
				$_SESSION['AddBookingCompanyArray'] = $company;
			}

			$pdo = null;

			// We only need to allow the user a company dropdown selector if they
			// are connected to more than 1 company.
			// If not we just store the companyID in a hidden form field
			if(isSet($company)){
				if (sizeOf($company)>1){
					// User is in multiple companies

					$_SESSION['AddBookingDisplayCompanySelect'] = TRUE;
				} elseif(sizeOf($company) == 1) {
					// User is in ONE company

					$_SESSION['AddBookingSelectedACompany'] = TRUE;
					unset($_SESSION['AddBookingDisplayCompanySelect']);
					$_SESSION['AddBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
					$_SESSION['AddBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
					$_SESSION['AddBookingInfoArray']['CreditsRemaining'] = $company[0]['creditsRemaining'];
					$_SESSION['AddBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = $company[0]['PotentialExtraMonthlyTimeUsed'];
					$_SESSION['AddBookingInfoArray']['HourPriceOverCredit'] = $company[0]['HourPriceOverCredit'];
					$_SESSION['AddBookingInfoArray']['PotentialCreditsRemaining'] = $company[0]['PotentialCreditsRemaining'];
					$_SESSION['AddBookingInfoArray']['BookingDescription'] = "Booked for " . $company[0]['companyName'];
				}
			} else{
				// User is NOT in a company

				$_SESSION['AddBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddBookingDisplayCompanySelect']);
				unset($_SESSION['AddBookingCompanyArray']);
				$_SESSION['AddBookingInfoArray']['TheCompanyID'] = "";
				$_SESSION['AddBookingInfoArray']['BookedForCompany'] = "";
				$_SESSION['AddBookingInfoArray']['CreditsRemaining'] = "N/A";
				$_SESSION['AddBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
				$_SESSION['AddBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
				$_SESSION['AddBookingInfoArray']['HourPriceOverCredit'] = "N/A";
				$_SESSION['AddBookingInfoArray']['BookingDescription'] = "";
			}
		}
		catch(PDOException $e)
		{
			$error = 'Error fetching user details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		// We haven't selected a user yet.
		$_SESSION['AddBookingInfoArray']['TheCompanyID'] = "";
		$_SESSION['AddBookingInfoArray']['BookedForCompany'] = "";
		$_SESSION['AddBookingInfoArray']['CreditsRemaining'] = "N/A";
		$_SESSION['AddBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
		$_SESSION['AddBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
		unset($_SESSION['AddBookingDisplayCompanySelect']);
		unset($_SESSION['AddBookingCompanyArray']);
	}

	// Set the correct information
	$row = $_SESSION['AddBookingInfoArray'];
	$original = $_SESSION['AddBookingOriginalInfoArray'];
		// Changed user
	if(isSet($_SESSION['AddBookingSelectedNewUser'])){

		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];
				$row['sendEmail'] = $user['sendEmail'];
				$row['BookedBy'] = $user['firstName'] . " " . $user['lastName'];
 
				$_SESSION['AddBookingDefaultDisplayNameForNewUser'] = $user['displayName'];
				$_SESSION['AddBookingDefaultBookingDescriptionForNewUser'] = $user['bookingDescription'];
				break;
			}
		}
	} else {
		$_SESSION['AddBookingDefaultDisplayNameForNewUser'] = $original['UserDefaultDisplayName'];
		$_SESSION['AddBookingDefaultBookingDescriptionForNewUser'] = $original['UserDefaultBookingDescription'];
	}

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
					$row['HourPriceOverCredit'] = $cmp['HourPriceOverCredit'];
					$row['BookingDescription'] = "Booked for " . $cmp['companyName'];
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
	$meetingroom = $_SESSION['AddBookingMeetingRoomsArray'];
	if(isSet($row['TheMeetingRoomID'])){
		$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	} else {
		$selectedMeetingRoomID = '';
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
	if(isSet($row['AdminNote'])){
		$adminNote = $row['AdminNote'];
	} else {
		$adminNote = '';
	}

	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];	

	$_SESSION['AddBookingInfoArray'] = $row; // Remember the company/user info we changed based on user choice

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change form
	if(isSet($notInACompany) AND $notInACompany){
		$_SESSION['refreshAddBookingChangeUser'] = TRUE;
		header("Location: .");
		exit();
	}

	include 'addbooking.html.php';
	exit();
}

// Admin wants to increase the start timer by minimum allowed time (e.g. 15 min)
if(isSet($_POST['add']) AND $_POST['add'] == "Increase Start By Minimum"){

	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddBookingInputs();

	$startTime = $_SESSION['AddBookingInfoArray']['StartTime'];
	$correctStartTime = correctDatetimeFormat($startTime);
	$_SESSION['AddBookingInfoArray']['StartTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctStartTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	if($_SESSION['AddBookingInfoArray']['StartTime'] == $_SESSION['AddBookingInfoArray']['EndTime']){
		$endTime = $_SESSION['AddBookingInfoArray']['EndTime'];
		$correctEndTime = correctDatetimeFormat($endTime);
		$_SESSION['AddBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();
}

// Admin wants to increase the end timer by minimum allowed time (e.g. 15 min)
if(isSet($_POST['add']) AND $_POST['add'] == "Increase End By Minimum"){

	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddBookingInputs();

	$endTime = $_SESSION['AddBookingInfoArray']['EndTime'];
	$correctEndTime = correctDatetimeFormat($endTime);
	$_SESSION['AddBookingInfoArray']['EndTime'] = convertDatetimeToFormat(getNextValidBookingEndTime($correctEndTime), 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// When admin has added the needed information and wants to add the booking
if ((isSet($_POST['add']) AND $_POST['add'] == "Add Booking") OR 
	(isSet($_SESSION['refreshAddBookingConfirmed']) AND $_SESSION['refreshAddBookingConfirmed'])
	){
	// Validate user inputs
	if(!isSet($_SESSION['refreshAddBookingConfirmed'])){
		list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname, $validatedAdminNote) = validateUserInputs('AddBookingError', FALSE, FALSE);

		// handle feedback process on invalid input values
		if($invalidInput){

			rememberAddBookingInputs();
			// Refresh.
			if(isSet($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
				$_SESSION['refreshAddBookingChangeUser'] = TRUE;
			} else {
				$_SESSION['refreshAddBooking'] = TRUE;
			}
			header('Location: .');
			exit();
		}

		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
		} else {
			$meetingRoomID = $_POST['meetingRoomID'];
		}

		if(isSet($_POST['companyID']) AND !empty($_POST['companyID'])){
			$companyID = $_POST['companyID'];
		} else {
			// TO-DO: Give error since there's no companyID?
			$companyID = NULL;
		}

		$_SESSION['AddBookingInfoArray']['TheCompanyID'] = $companyID;
		$_SESSION['AddBookingInfoArray']['TheMeetingRoomID'] = $meetingRoomID;
		$_SESSION['AddBookingInfoArray']['StartTime'] = $startDateTime;
		$_SESSION['AddBookingInfoArray']['EndTime'] = $endDateTime;
		$_SESSION['AddBookingInfoArray']['BookedBy'] = $dspname;
		$_SESSION['AddBookingInfoArray']['BookingDescription'] = $bknDscrptn;
		$_SESSION['AddBookingInfoArray']['AdminNote'] = $validatedAdminNote;
	} else {
		$companyID = $_SESSION['AddBookingInfoArray']['TheCompanyID'];
		$meetingRoomID = $_SESSION['AddBookingInfoArray']['TheMeetingRoomID'];
		$startDateTime = $_SESSION['AddBookingInfoArray']['StartTime'];
		$endDateTime = $_SESSION['AddBookingInfoArray']['EndTime'];
		$dspname = $_SESSION['AddBookingInfoArray']['BookedBy'];
		$bknDscrptn = $_SESSION['AddBookingInfoArray']['BookingDescription'];
		$validatedAdminNote = $_SESSION['AddBookingInfoArray']['AdminNote'];
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
		rememberAddBookingInputs();

		$_SESSION['AddBookingError'] = "The booking couldn't be made. The timeslot is already taken for this meeting room.";
		if(isSet($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
			$_SESSION['refreshAddBookingChangeUser'] = TRUE;
		} else {
			$_SESSION['refreshAddBooking'] = TRUE;	
		}
		header('Location: .');
		exit();
	}

	// We know it's available. Let's check if this booking makes the company go over credits.
	// If over credits, ask for a confirmation before creating the booking
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateOnlyEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', 'Y-m-d');
	$timeBookedInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $endDateTime);	

	// Get meeting room name
	$MeetingRoomName = 'N/A';
	foreach ($_SESSION['AddBookingMeetingRoomsArray'] AS $room){
		if($room['meetingRoomID'] == $meetingRoomID){
			$MeetingRoomName = $room['meetingRoomName'];
			break;
		}
	}

	// Get company info
	$companyName = 'N/A';
	if(isSet($companyID)){
		foreach($_SESSION['AddBookingCompanyArray'] AS $company){
			if($companyID == $company['companyID']){
				$companyName = $company['companyName'];
				$companyCreationDate = $company['dateTimeCreated'];
				$companyCreditsRemaining = $company['creditsRemaining'];
				$companyCreditsBooked = $company['PotentialExtraMonthlyTimeUsed'];
				$companyCreditsPotentialMinimumRemaining = $company['PotentialCreditsRemaining'];
				$companyCreditsPotentialMinimumRemainingInMinutes = convertHoursAndMinutesToMinutes($companyCreditsPotentialMinimumRemaining);
				$displayCompanyPeriodEndDate = $company['endDate']; //Display format
				$companyPeriodEndDate = convertDatetimeToFormat($displayCompanyPeriodEndDate, DATE_DEFAULT_FORMAT_TO_DISPLAY, 'Y-m-d');
				$companyPeriodStartDate = $company['startDate'];
				$companyHourPriceOverCredits = $company['HourPriceOverCredit'];
				$companyMinuteCredits = $company['creditsGiven'];
				break;
			}
		}
	}

	// Check if the booking that was made was for the current period.
	$bookingWentOverCredits = FALSE;
	$firstTimeOverCredit = FALSE;
	$addExtraLogEventDescription = FALSE;
	$newPeriod = FALSE;
	if($dateOnlyEndDate <= $companyPeriodEndDate){
		if($companyCreditsPotentialMinimumRemainingInMinutes < 0){
			// Company was already over given credits
			$bookingWentOverCredits = TRUE;
			$minutesOverCredits = -($companyCreditsPotentialMinimumRemainingInMinutes) + $timeBookedInMinutes;
			$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
			$addExtraLogEventDescription = TRUE;
		} elseif($timeBookedInMinutes > $companyCreditsPotentialMinimumRemainingInMinutes){
			// This booking, if completed, will put the company over their given credits
			$bookingWentOverCredits = TRUE;
			$firstTimeOverCredit = TRUE;
			$minutesOverCredits = $timeBookedInMinutes - $companyCreditsPotentialMinimumRemainingInMinutes;
			$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
			$addExtraLogEventDescription = TRUE;
		}
	} else {
		$newPeriod = TRUE;
		// Get exact period the user is booking for
		date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
		$newDate = DateTime::createFromFormat("Y-m-d H:i:s", $companyCreationDate);
		$dayNumberToKeep = $newDate->format("d");

		list($newCompanyPeriodStart, $newCompanyPeriodEnd) = getPeriodDatesForCompanyFromDateSubmitted($dayNumberToKeep, $dateOnlyEndDate, $companyPeriodStartDate, $companyPeriodEndDate);

		// For displaying the new period dates
		$periodStartDate = convertDatetimeToFormat($newCompanyPeriodStart, "Y-m-d", DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$periodEndDate = convertDatetimeToFormat($newCompanyPeriodEnd, "Y-m-d", DATE_DEFAULT_FORMAT_TO_DISPLAY);

		// Get booking time used so far for the future period
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'SELECT (BIG_SEC_TO_TIME(SUM(
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
					)))	AS PotentialBookingTimeUsed
					FROM 		`booking` b 
					WHERE 		b.`CompanyID` = :companyID
					AND 		DATE(b.`endDateTime`) >= :newStartPeriod
					AND			DATE(b.`endDateTime`) < :newEndPeriod
					AND 		b.`actualEndDateTime` IS NULL
					AND			b.`dateTimeCancelled` IS NULL';
			$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
			$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s	

			$s = $pdo->prepare($sql);
			$s->bindValue(':companyID', $companyID);
			$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
			$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
			$s->bindValue(':newStartPeriod', $newCompanyPeriodStart);
			$s->bindValue(':newEndPeriod', $newCompanyPeriodEnd);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);

			if(isSet($row['PotentialBookingTimeUsed']) AND !empty($row['PotentialBookingTimeUsed'])){
				$timeBookedSoFarThisPeriod = convertTimeToMinutes($row['PotentialBookingTimeUsed']);
			} else {
				$timeBookedSoFarThisPeriod = 0;
			}

			// Add the time in minutes for the selected booking to the period time
			$totalTimeBookedSoFarThisPeriod = $timeBookedSoFarThisPeriod + $timeBookedInMinutes;
			$totalTimeBookedInTime = convertMinutesToHoursAndMinutes($totalTimeBookedSoFarThisPeriod);

			if($totalTimeBookedSoFarThisPeriod > $companyMinuteCredits){
				$bookingWentOverCredits = TRUE;
				if($timeBookedSoFarThisPeriod <= $companyMinuteCredits){
					$firstTimeOverCredit = TRUE;
				}
				$minutesOverCredits = $totalTimeBookedSoFarThisPeriod - $companyMinuteCredits;
				$timeOverCredits = convertMinutesToHoursAndMinutes($minutesOverCredits);
				$addExtraLogEventDescription = TRUE;
			}

			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error fetching future booking details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Send user to the confirmation template if needed
	if($bookingWentOverCredits AND !isSet($_SESSION['refreshAddBookingConfirmed'])){
		var_dump($_SESSION); // TO-DO: Remove before uploading
		include_once 'confirmbooking.html.php';
		exit();
	}

	if(!isSet($dspname) OR (empty($dspname) AND !empty($_SESSION["AddCreateBookingInfoArray"]["BookedBy"]))){
		$dspname = $_SESSION["AddBookingInfoArray"]["BookedBy"];
	}
	if(!isSet($bknDscrptn) OR (empty($bknDscrptn) AND !empty($_SESSION["AddCreateBookingInfoArray"]["BookingDescription"]))){
		$bknDscrptn = $_SESSION["AddBookingInfoArray"]["BookingDescription"];
	}

	// Add the booking to the database
	try
	{
		if(empty($validatedAdminNote)){
			$validatedAdminNote = NULL;
		}

		//Generate cancellation code
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
							`adminNote` = :adminNote,
							`cancellationCode` = :cancellationCode';

		$s = $pdo->prepare($sql);

		$s->bindValue(':meetingRoomID', $meetingRoomID);
		$s->bindValue(':userID', $_SESSION["AddBookingInfoArray"]["TheUserID"]);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $dspname);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':description', $bknDscrptn);
		$s->bindValue(':adminNote', $validatedAdminNote);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted booking to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['BookingUserFeedback'] .= "Successfully created the booking.";
	
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateOnlyEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', 'Y-m-d');
	$timeBookedInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime,$endDateTime);	
	
	// Add a log event that a booking has been created
	try
	{
		unset($_SESSION['AddBookingMeetingRoomsArray']);

		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$displayValidatedStartDate . ' to ' . $displayValidatedEndDate;

		// Get user information
		$userinfo = 'N/A';
		$info = $_SESSION['AddBookingInfoArray']; 
		if(isSet($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}

		// Save a description with information about the booking that was created
		$logEventDescription = 	"A booking with these details was created: " .
								"\nMeeting room: " . $MeetingRoomName . 
								"\nStart Time: " . $displayValidatedStartDate . 
								"\nEnd Time: ". $displayValidatedEndDate .
								"\nBooker for User: " . $userinfo . 
								"\nBooked for Company: " . $companyName . 
								"\nIt was created by: " . $_SESSION['LoggedInUserName'] . ".";
		if($addExtraLogEventDescription){
			$logEventDescription .= "\nThis booking, if completed, will put the company at $timeOverCredits over the Credits given that period.";
		}

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes.db.inc.php';
		
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Booking Created'
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
	
	//Send email with booking information and cancellation code to the user who the booking is for.
		// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email
	if($info['sendEmail'] == 1){
		if($info["TheUserID"] == $_SESSION['LoggedInUserID']){
			// Admin booked a meeting for him/herself
			$emailSubject = "New Booking Information!";
			
			$emailMessage = 
			"Your meeting has been successfully booked!\n" . 
			"The meeting has been set to: \n" .
			"Meeting Room: " . $MeetingRoomName . ".\n" . 
			"Start Time: " . $displayValidatedStartDate . ".\n" .
			"End Time: " . $displayValidatedEndDate . ".\n\n" .
			"If you wish to cancel your meeting, or just end it early, you can easily do so by using the link given below.\n" .
			"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
			"/booking/?cancellationcode=" . $cancellationCode;		
		} else {
			// Admin booked a meeting for someone else
			$emailSubject = "You have been assigned a booked meeting!";

			$emailMessage = 
			"A booked meeting has been assigned to you by an Admin!\n" .
			"The meeting has been set to: \n" .
			"Meeting Room: " . $MeetingRoomName . ".\n" . 
			"Start Time: " . $displayValidatedStartDate . ".\n" .
			"End Time: " . $displayValidatedEndDate . ".\n\n" .
			"If you wish to cancel this meeting, or just end it early, you can easily do so by using the link given below.\n" .
			"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
			"/booking/?cancellationcode=" . $cancellationCode;		
		}

		if($bookingWentOverCredits){
			// Add time over credits and the price per hour the company subscription has.
			$emailMessage .= "\n\nWarning: If this booking is completed the company you booked for will be $timeOverCredits over the given free booking time.\nThis will result in a cost of $companyHourPriceOverCredits";
		}

		$email = $info['UserEmail'];

		$mailResult = sendEmail($email, $emailSubject, $emailMessage);

		if(!$mailResult){
			$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

			// Email failed to be prepared. Store it in database to try again later
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

				$pdo = connect_to_db();
				$sql = 'INSERT INTO	`email`
						SET			`subject` = :subject,
									`message` = :message,
									`receivers` = :receivers,
									`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
				$s = $pdo->prepare($sql);
				$s->bindValue(':subject', $emailSubject);
				$s->bindValue(':message', $emailMessage);
				$s->bindValue(':receivers', $email);
				$s->execute();

				//close connection
				$pdo = null;
			}
			catch (PDOException $e)
			{
				$error = 'Error storing email: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

			$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
		}

		$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing
	} elseif($info['sendEmail'] == 0){
		$_SESSION['BookingUserFeedback'] .= " \nUser does not want to get sent Emails.";
	}

	// Send email to alert company owner(s) that a booking was made that is over credits.
		// Check if any owners want to receive an email
	if($bookingWentOverCredits){
		try
		{
			$userEmail = $info['UserEmail'];
			$bookedForUserName = $info['UserLastname'] . ", " . $info['UserFirstname'];

			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();

			$sql = 'SELECT		u.`email`					AS Email,
								e.`sendEmail`				AS SendOwnerEmail,
								e.`sendEmailOnceOrAlways`	AS SendEmailOnceOrAlways
					FROM 		`user` u
					INNER JOIN	`employee` e
					ON 			e.`UserID` = u.`UserID`
					INNER JOIN	`company` c
					ON 			c.`CompanyID` = e.`CompanyID`
					INNER JOIN	`companyposition` cp
					ON			e.`PositionID` = cp.`PositionID`
					WHERE 		c.`CompanyID` = :CompanyID
					AND			cp.`name` = "Owner"
					AND			u.`email` <> :UserEmail';

			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $companyID);
			$s->bindValue(':UserEmail', $userEmail);
			$s->execute();
			$result = $s->fetchAll(PDO::FETCH_ASSOC);

			if(isSet($result) AND !empty($result)){
				foreach($result AS $row){
					// Check if user wants to receive owner emails
					if($row['SendOwnerEmail'] == 1){ 
						// Check if user wants to receive all emails or just the first time booking goes over credit per period
							// sendEmailOnceOrAlways: 1 = always, sendEmailOnceOrAlways: 0 = once.
						if($row['SendEmailOnceOrAlways'] == 1 OR ($row['SendEmailOnceOrAlways'] == 0 AND $firstTimeOverCredit)){ 
							$companyOwnerEmails[] = $row['Email'];
						}
					}
				}
			}

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error sending email to company owner(s): ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// We found company owners to send email too
		if(isSet($companyOwnerEmails) AND !empty($companyOwnerEmails)){
			$emailSubject = "Booked Meeting Above Credits!";

			$emailMessage = 
			"The employee: " . $bookedForUserName . "\n" .
			"In your company: " . $companyName . "\n" .
			"Has been assigned a meeting, by an admin, that will put your company above the treshold of free booking time this period.\n" .
			"If this booking is completed your company will be $timeOverCredits over the treshold.\nThis will result in a cost of $companyHourPriceOverCredits\n\n" .
			"The meeting has been set to: \n" .
			"Meeting Room: " . $MeetingRoomName . ".\n" . 
			"Start Time: " . $displayValidatedStartDate . ".\n" .
			"End Time: " . $displayValidatedEndDate . ".\n\n" .
			"If you wish to cancel this meeting, or just end it early, you can easily do so by using the link given below.\n" .
			"Click this link to cancel the booked meeting: " . $_SERVER['HTTP_HOST'] . 
			"/booking/?cancellationcode=" . $cancellationCode . "\n\n" . 
			"If you do not wish to receive these emails, you can disable them in 'My Account' under 'Company Owner Alert Status'.";

			$email = $companyOwnerEmails;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			$email = implode(", ", $email);

			if(!$mailResult){
				$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user(s).";

				// Email failed to be prepared. Store it in database to try again later
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

					$pdo = connect_to_db();
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->execute();

					//close connection
					$pdo = null;
				}
				catch (PDOException $e)
				{
					$error = 'Error storing email: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$_SESSION['BookingUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading			
		} else {
			$_SESSION['BookingUserFeedback'] .= "\n\nNo Company Owners were sent an email about the booking going over credits."; // TO-DO: Remove before uploading.
		}
	}	
	
	// Booking a new meeting is done. Reset all connected sessions.
	clearAddBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

if(isSet($_POST['Add']) AND $_POST['Add'] == "Yes, Create The Booking"){
	$_SESSION['refreshAddBookingConfirmed'] = TRUE;
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/bookings/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}

if(isSet($_POST['Add']) AND $_POST['Add'] == "No, Cancel The Booking"){
	$_SESSION['normalBookingFeedback'] = "You cancelled your new booking.";
	unset($_SESSION['refreshAddBookingConfirmed']);
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/bookings/?meetingroom=" . $meetingRoomID;
	} else {
		$meetingRoomID = $_POST['meetingRoomID'];
		$location = '.';
	}
	header('Location: ' . $location);
	exit();
}

// Admin wants to change the user the booking is for
// We need to get a list of all active users
if((isSet($_POST['add']) AND $_POST['add'] == "Change User") OR 
	(isSet($_SESSION['refreshAddBookingChangeUser'])) AND $_SESSION['refreshAddBookingChangeUser']){

	if(isSet($_SESSION['refreshAddBookingChangeUser']) AND $_SESSION['refreshAddBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshAddBookingChangeUser']);
	}

	// Forget the old search result for users if we had one saved
	unset($_SESSION['AddBookingUsersArray']);
	unset($_SESSION['AddBookingCompanyArray']);

	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isSet($_POST['add']) AND $_POST['add'] == "Change User"){
		rememberAddBookingInputs();
	}

	$usersearchstring = "";
	
	if(isSet($_SESSION['AddBookingUserSearch'])){
		$usersearchstring = $_SESSION['AddBookingUserSearch'];
	}

	if(!isSet($_SESSION['AddBookingUsersArray'])){
		// Get all active users and their default booking information
		try
		{
			$pdo = connect_to_db();
			// New SQL that only gets users that are registered in a company (an employee)
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`,
							`sendEmail`
					FROM 	`user`
					WHERE 	`isActive` > 0
					AND		`userID`
					IN	(
							SELECT 	DISTINCT `userID`
							FROM 	`employee`
						)";

			if ($usersearchstring != ''){
				$sqladd = " AND (`firstname` LIKE :search
							OR `lastname` LIKE :search
							OR `email` LIKE :search)";
				$sql .= $sqladd;

				$sql .= " ORDER BY `lastname`";

				$finalusersearchstring = '%' . $usersearchstring . '%';

				$s = $pdo->prepare($sql);
				$s->bindValue(":search", $finalusersearchstring);
				$s->execute();
				$result = $s->fetchAll(PDO::FETCH_ASSOC);

			} else {
				
				$sql .= " ORDER BY `lastname`";
				
				$return = $pdo->query($sql);
				$result = $return->fetchAll(PDO::FETCH_ASSOC);
			}

			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$users[] = array(
									'userID' => $row['userID'],
									'lastName' => $row['lastname'],
									'firstName' => $row['firstname'],
									'email' => $row['email'],
									'userInformation' => $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'],
									'displayName' => $row['displayname'],
									'bookingDescription' => $row['bookingdescription'],
									'sendEmail' => $row['sendEmail']
									);
			}
			$_SESSION['AddBookingUsersArray'] = $users;

			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error fetching user details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$users = $_SESSION['AddBookingUsersArray'];
	}

	$_SESSION['refreshAddBooking'] = TRUE;
	$_SESSION['AddBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// Admin confirms what user he wants the booking to be for.
if(isSet($_POST['add']) AND $_POST['add'] == "Select This User"){
	
	// We haven't set the company if we are changing the user.
	unset($_SESSION['AddBookingSelectedACompany']);

	// We no longer need to be able to change the user
	unset($_SESSION['AddBookingChangeUser']);
	
	// Remember that we've selected a new user
	$_SESSION['AddBookingSelectedNewUser'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddBookingInputs();

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

//	Admin wants to change the company the booking is for (after having already selected it)
if(isSet($_POST['add']) AND $_POST['add'] == "Change Company"){

	// We want to select a company again
	unset($_SESSION['AddBookingSelectedACompany']);

	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddBookingInputs();

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();
}

// Admin confirms what company he wants the booking to be for.
if(isSet($_POST['add']) AND $_POST['add'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['AddBookingSelectedACompany'] = TRUE;

	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberAddBookingInputs();

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to get the default values for the user's display name
if(isSet($_POST['add']) AND $_POST['add'] == "Get Default Display Name"){

	$displayName = $_SESSION['AddBookingDefaultDisplayNameForNewUser'];
	if(isSet($_SESSION['AddBookingInfoArray'])){
		rememberAddBookingInputs();

		if($displayName != ""){
			if($displayName != $_SESSION['AddBookingInfoArray']['BookedBy']){

					// The user selected
				$_SESSION['AddBookingInfoArray']['BookedBy'] = $displayName;

				unset($_SESSION['AddBookingDefaultDisplayNameForNewUser']);				
			} else {
				// Description was already the default booking description
				$_SESSION['AddBookingError'] = "This is already the user's default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['AddBookingError'] = "This user has no default display name.";
			$_SESSION['AddBookingInfoArray']['BookedBy'] = "";
		}
	}

	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();
}

// If admin wants to get the default values for the user's booking description
if(isSet($_POST['add']) AND $_POST['add'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['AddBookingDefaultBookingDescriptionForNewUser'];
	if(isSet($_SESSION['AddBookingInfoArray'])){
		
		rememberAddBookingInputs();

		if($bookingDescription != ""){
			if($bookingDescription != $_SESSION['AddBookingInfoArray']['BookingDescription']){
				$_SESSION['AddBookingInfoArray']['BookingDescription'] = $bookingDescription;

				unset($_SESSION['AddBookingDefaultBookingDescriptionForNewUser']);			
			} else {
				// Description was already the default booking description
				$_SESSION['AddBookingError'] = "This is already the user's default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['AddBookingError'] = "This user has no default booking description.";
			$_SESSION['AddBookingInfoArray']['BookingDescription'] = "";
		}
	}
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin is adding a booking and wants to limit the users shown by searching
if(isSet($_SESSION['AddBookingChangeUser']) AND isSet($_POST['add']) AND $_POST['add'] == "Search"){
	
	// Let's remember what was selected and searched for
		// The user search string
	$_SESSION['AddBookingUserSearch'] = $_POST['usersearchstring'];

	rememberAddBookingInputs();
	
	// Get the new users
	$_SESSION['refreshAddBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// If admin wants to change the values back to the original values
if (isSet($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddBookingInfoArray'] = $_SESSION['AddBookingOriginalInfoArray'];
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your new booking.";
}


// ADD CODE SNIPPET END //

// END OF USER INPUT CODE //

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddBookingSessions();
clearEditBookingSessions();
clearCancelSessions();


// BOOKING OVERVIEW CODE SNIPPET START //

if(isSet($refreshBookings) AND $refreshBookings) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshBookings);
}

// Display booked meetings history list
if(!isSet($_GET['Meetingroom'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
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
										INNER JOIN 	`employee` e
										ON 			e.`CompanyID` = c.`CompanyID`
										WHERE  		e.`userID` = b.`userID`
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription,
							b.`adminNote`									AS AdminNote,
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn,
							b.`cancelMessage`								AS BookingCancelMessage,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserFirstName,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserLastName
				FROM 		`booking` b
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				ASC';
		$result = $pdo->query($sql);

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
} elseif(isSet($_GET['Meetingroom']) AND !empty($_GET['Meetingroom'])) {
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
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
										INNER JOIN 	`employee` e
										ON 			e.`CompanyID` = c.`CompanyID`
										WHERE  		e.`userID` = b.`userID`
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription,
							b.`adminNote`									AS AdminNote,							
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn,
							b.`cancelMessage`								AS BookingCancelMessage,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserFirstName,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserLastName							
				FROM 		`booking` b
				WHERE		b.`meetingRoomID` = :MeetingRoomID
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				ASC';
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);

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
}

foreach($result AS $row){

	$datetimeNow = getDatetimeNow();
	$startDateTime = $row['StartTime'];	
	$endDateTime = $row['EndTime'];
	$completedDateTime = $row['BookingWasCompletedOn'];
	$dateOnlyNow = convertDatetimeToFormat($datetimeNow, 'Y-m-d H:i:s', 'Y-m-d');
	$dateOnlyCompleted = convertDatetimeToFormat($completedDateTime,'Y-m-d H:i:s','Y-m-d');
	$dateOnlyStart = convertDatetimeToFormat($startDateTime,'Y-m-d H:i:s','Y-m-d');
	$cancelledDateTime = $row['BookingWasCancelledOn'];
	$dateOnlyCancelled = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', 'Y-m-d');
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
				$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled == $dateOnlyNow){
		$status = 'Ended Early Today';
		// Valid status?
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
				$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime AND 
				$dateOnlyCancelled == $dateOnlyNow){
		$status = 'Ended Early Today';
		// Valid status
	} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
				$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled < $dateOnlyNow){
		$status = 'Ended Early';
		// Valid status?
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
				$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime AND 
				$dateOnlyCancelled < $dateOnlyNow){
		$status = 'Ended Early';
		// Valid status?
	} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
				$completedDateTime < $cancelledDateTime ){
		$status = 'Cancelled after Completion';
		// This should not be allowed to happen
	} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
				$datetimeNow > $endDateTime){
		$status = 'Ended without updating database';
		// This should only occur when the cron does not check and update every minute
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND 
				$endDateTime < $cancelledDateTime){
		$status = 'Cancelled after meeting should have been Completed';
		// This should not be allowed to happen
	} else {
		$status = 'Unknown';
		// This should never occur
	}
	
	$roomName = $row['BookedRoomName'];
	$displayRoomNameForTitle = $roomName;
	$firstname = $row['firstName'];
	$lastname = $row['lastName'];
	$email = $row['email'];
	$userinfo = $lastname . ', ' . $firstname . ' - ' . $email;
	$worksForCompany = $row['WorksForCompany'];
	$adminNote = $row['AdminNote'];
	if(!isSet($adminNote) OR $adminNote == NULL){
		$adminNote = "";
	}
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
	$displayCompletedDateTime = convertDatetimeToFormat($completedDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayCancelledDateTime = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
	$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	$meetinginfo = $roomName . ' for the timeslot: ' . $displayValidatedStartDate . 
					' to ' . $displayValidatedEndDate;

	$completedMeetingDurationInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $completedDateTime);
	$displayCompletedMeetingDuration = convertMinutesToHoursAndMinutes($completedMeetingDurationInMinutes);
	if($completedMeetingDurationInMinutes < BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS){
		$completedMeetingDurationForPrice = 0;
	} elseif($completedMeetingDurationInMinutes < MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS){
		$completedMeetingDurationForPrice = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS;
	} else {
		$completedMeetingDurationForPrice = $completedMeetingDurationInMinutes;
	}
	$displayCompletedMeetingDurationForPrice = convertMinutesToHoursAndMinutes($completedMeetingDurationForPrice);
	
	$cancelMessage = $row['BookingCancelMessage'];
	if($cancelMessage == NULL){
		$cancelMessage = "";
	}
	if($row['CancelledByUserLastName'] == NULL AND $row['CancelledByUserFirstName'] == NULL){
		$cancelledByUserName = "N/A - Deleted";
	} else {
		$cancelledByUserName = $row['CancelledByUserLastName'] . ", " . $row['CancelledByUserFirstName'];
	}

	if($status == "Active Today"){				
		$bookingsActiveToday[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'AdminNote' => $adminNote,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'WorksForCompany' => $worksForCompany,
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookedUserID' => $row['BookedUserID'],
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,	
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo,
										'sendEmail' => $row['sendEmail']
									);
	}	elseif($status == "Completed Today" OR $status == "Ended Early Today") {
		if($status == "Completed Today"){
			$cancelMessage = "";
			$cancelledByUserName = "";
		}
		$bookingsCompletedToday[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
											'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'AdminNote' => $adminNote,
											'CancelMessage' => $cancelMessage,
											'CancelledByUserName' => $cancelledByUserName,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'WorksForCompany' => $worksForCompany,
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookedUserID' => $row['BookedUserID'],
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,	
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo
										);		
	}	elseif($status == "Active"){				
		$bookingsFuture[] = array(	'id' => $row['bookingID'],
									'BookingStatus' => $status,
									'BookedRoomName' => $roomName,
									'StartTime' => $displayValidatedStartDate,
									'EndTime' => $displayValidatedEndDate,
									'BookedBy' => $row['BookedBy'],
									'BookedForCompany' => $row['BookedForCompany'],
									'BookingDescription' => $row['BookingDescription'],
									'AdminNote' => $adminNote,
									'firstName' => $firstname,
									'lastName' => $lastname,
									'email' => $email,
									'WorksForCompany' => $worksForCompany,
									'BookingWasCreatedOn' => $displayCreatedDateTime,
									'BookedUserID' => $row['BookedUserID'],
									'BookingWasCompletedOn' => $displayCompletedDateTime,
									'BookingWasCancelledOn' => $displayCancelledDateTime,	
									'UserInfo' => $userinfo,
									'MeetingInfo' => $meetinginfo,
									'sendEmail' => $row['sendEmail']
								);
	}	elseif($status == "Completed" OR $status == "Ended Early"){	
		if($status == "Completed"){
			$cancelMessage = "";
			$cancelledByUserName = "";
		}
		$bookingsCompleted[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
										'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'AdminNote' => $adminNote,
										'CancelMessage' => $cancelMessage,
										'CancelledByUserName' => $cancelledByUserName,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'WorksForCompany' => $worksForCompany,
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookedUserID' => $row['BookedUserID'],
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,	
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo
									);
	}	elseif($status == "Cancelled"){
		$bookingsCancelled[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'AdminNote' => $adminNote,
										'CancelMessage' => $cancelMessage,
										'CancelledByUserName' => $cancelledByUserName,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'WorksForCompany' => $worksForCompany,
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookedUserID' => $row['BookedUserID'],
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,	
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo
									);		
	}	else {				
		$bookingsOther[] = array(	'id' => $row['bookingID'],
									'BookingStatus' => $status,
									'BookedRoomName' => $roomName,
									'StartTime' => $displayValidatedStartDate,
									'EndTime' => $displayValidatedEndDate,
									'BookedBy' => $row['BookedBy'],
									'BookedForCompany' => $row['BookedForCompany'],
									'BookingDescription' => $row['BookingDescription'],
									'AdminNote' => $adminNote,
									'firstName' => $firstname,
									'lastName' => $lastname,
									'email' => $email,
									'WorksForCompany' => $worksForCompany,
									'BookingWasCreatedOn' => $displayCreatedDateTime,
									'BookedUserID' => $row['BookedUserID'],
									'BookingWasCompletedOn' => $displayCompletedDateTime,
									'BookingWasCancelledOn' => $displayCancelledDateTime,	
									'UserInfo' => $userinfo,
									'MeetingInfo' => $meetinginfo,
									'sendEmail' => $row['sendEmail']
								);
	}
}
if(isSet($displayRoomNameForTitle) AND ($displayRoomNameForTitle == NULL OR $displayRoomNameForTitle == "N/A - Deleted")){
	unset($displayRoomNameForTitle);
}
// BOOKING OVERVIEW CODE SNIPPET END //
var_dump($_SESSION); // TO-DO: remove after testing is done
// Create the booking information table in HTML
include_once 'bookings.html.php';
?>