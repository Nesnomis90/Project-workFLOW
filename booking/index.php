<?php 
// This is the index file for the booking folder (all users)

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdmin();
unsetSessionsFromUserManagement();
unsetSessionsFromCompanyManagement();
unsetSessionsFromMeetingroomManagement();

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	userIsLoggedIn();
}

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
	unset($_SESSION['AddCreateBookingAvailableExtra']);
	unset($_SESSION['AddCreateBookingStepOneCompleted']);
	unset($_SESSION['AddCreateBookingOrderTooSoon']);
	unset($_SESSION['AddCreateBookingOrderUserNotes']);
	unset($_SESSION['AddCreateBookingOrderAddedExtra']);

	unset($_SESSION['refreshAddCreateBookingConfirmed']);

	unset($_SESSION['bookingCodeUserID']);

	unset($_SESSION['normalUserOriginalInfoArray']); // Make sure we get up-to-date user values after doing bookings
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

	unset($_SESSION['bookingCodeUserID']);

	unset($_SESSION['normalUserOriginalInfoArray']); // Make sure we get up-to-date user values after doing bookings
}

// Function to clear sessions used during cancelling and changing rooms.
function clearChangeBookingSessions(){
	unset($_SESSION['changeRoomChangedBy']);
	unset($_SESSION['changeRoomChangedByUser']);
	unset($_SESSION['changeToMeetingRoomID']);
	unset($_SESSION['changeRoomOriginalBookingValues']);
	unset($_SESSION['changeRoomOriginalValues']);
	unset($_SESSION['continueChangeRoom']);
	unset($_SESSION['changeToOccupiedRoomBookingID']);
	unset($_SESSION['cancelBookingOriginalValues']);

	unset($_SESSION['bookingCodeUserID']);

	unset($_SESSION['normalUserOriginalInfoArray']); // Make sure we get up-to-date user values after doing bookings
}

// Function to clear sessions used during creating an order after a booking has been made
function clearAddOrderToBookingSessions(){
	unset($_SESSION['refreshCreateOrder']);
	unset($_SESSION['createOrderOriginalValues']);
	unset($_SESSION['AddCreateOrderForBookingAvailableExtra']);
	unset($_SESSION['AddCreateOrderForBookingOrderUserNotes']);
	unset($_SESSION['AddCreateOrderForBookingOrderAddedExtra']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit Order form
function clearEditBookingOrderSessions(){
	unset($_SESSION['EditBookingOrderOriginalInfo']);
	unset($_SESSION['EditBookingOrderCommunicationToStaff']);
	unset($_SESSION['EditBookingOrderIsApprovedByUser']);
	unset($_SESSION['EditBookingOrderOrderID']);
	unset($_SESSION['EditBookingOrderExtraOrdered']);
	unset($_SESSION['EditBookingOrderOrderMessages']);
	unset($_SESSION['EditBookingOrderAvailableExtra']);
	unset($_SESSION['EditBookingOrderAlternativeExtraAdded']);
	unset($_SESSION['EditBookingOrderAlternativeExtraCreated']);
	unset($_SESSION['EditBookingOrderExtraOrderedOnlyNames']);
	unset($_SESSION['EditBookingOrderTotalPrice']);
	unset($_SESSION['resetEditBookingOrder']);
	unset($_SESSION['refreshEditBookingOrder']);
}

function updateBookingCodeGuesses(){
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
			unset($_SESSION['adminBookingCodeGuesses']);
			return TRUE;
		}
	} else {
		return TRUE;
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

		return FALSE;
	} elseif(isSet($_SESSION['bookingCodeGuesses']) AND (sizeOf($_SESSION['bookingCodeGuesses']) == (MAXIMUM_BOOKING_CODE_GUESSES - 1))){
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an incorrect code.\nYou have 1 attempt remaining before you are timed out.\nLog in and check your account information to see your code, if you have forgotten it.";
		return TRUE;
	} elseif(isSet($_SESSION['bookingCodeGuesses']) AND (sizeOf($_SESSION['bookingCodeGuesses']) > 0)){
		$remainingAttempts = MAXIMUM_BOOKING_CODE_GUESSES - sizeOf($_SESSION['bookingCodeGuesses']);
		$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an incorrect code.\nYou have $remainingAttempts attempts remaining before you are timed out.";
		return TRUE;
	}
}

function updateAdminBookingCodeGuesses(){
/*	// Check if any of the old admin guesses are old enough to remove
	if(isSet($_SESSION['adminBookingCodeGuesses'])){
		$dateTimeNow = getDatetimeNow();
		$newArray = array();
		for($i=0; $i < sizeOf($_SESSION['adminBookingCodeGuesses']); $i++){
			$startDateTime = $_SESSION['adminBookingCodeGuesses'][$i];

			$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);
			if($timeDifference < BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES){
				$newArray[] = $_SESSION['adminBookingCodeGuesses'][$i];
			}
		}
		if(sizeOf($newArray) > 0){
			$_SESSION['adminBookingCodeGuesses'] = $newArray;
		} else {
			unset($_SESSION['adminBookingCodeGuesses']);
			return TRUE;
		}
	} else {
		return TRUE;
	}*/

	// Limit the amount of tries the session has to guess an admin booking code.
	if(isSet($_SESSION['adminBookingCodeGuesses']) AND (sizeOf($_SESSION['adminBookingCodeGuesses']) >= MAXIMUM_ADMIN_BOOKING_CODE_GUESSES)){
		$dateTimeNow = getDatetimeNow();
		$startDateTime = $_SESSION['bookingCodeGuesses'][0];

		$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);
		$timeRemaining = floor(BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES - $timeDifference);
		if($timeRemaining > 0){
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can not be removed.\nYou can try again in $timeRemaining minute(s).";
		} else {
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can not be removed.\nYou can try again in less than a minute.";
		}

		return FALSE;
	} elseif(isSet($_SESSION['adminBookingCodeGuesses']) AND (sizeOf($_SESSION['adminBookingCodeGuesses']) > 0)){
		$dateTimeNow = getDatetimeNow();
		$startDateTime = $_SESSION['bookingCodeGuesses'][0];

		$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);
		$timeRemaining = floor(BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES - $timeDifference);
		$remainingAttempts = MAXIMUM_ADMIN_BOOKING_CODE_GUESSES - sizeOf($_SESSION['adminBookingCodeGuesses']);
		if($timeRemaining > 0){
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can be removed by an Admin ($remainingAttempts attempts left).\nOr you can try again in $timeRemaining minute(s).";
		} else {
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can be removed by an Admin ($remainingAttempts attempts left).\nOr you can try again in less than a minute.";
		}

		return TRUE;
	} elseif(!isSet($_SESSION['adminBookingCodeGuesses']) AND isSet($_SESSION['bookingCodeGuesses'])){
		$dateTimeNow = getDatetimeNow();
		$startDateTime = $_SESSION['bookingCodeGuesses'][0];

		$timeDifference = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $dateTimeNow);
		$timeRemaining = floor(BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES - $timeDifference);
		$remainingAttempts = MAXIMUM_ADMIN_BOOKING_CODE_GUESSES;
		if($timeRemaining > 0){
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can be removed by an Admin ($remainingAttempts attempts left).\nOr you can try again in $timeRemaining minute(s).";
		} else {
			$_SESSION['confirmBookingCodeError'] = "You have inserted an incorrect code too many times.\nThe timeout can be removed by an Admin ($remainingAttempts attempts left).\nOr you can try again in less than a minute.";
		}

		return TRUE;
	} else {
		return TRUE;
	}
}

// Function to remember the user inputs in Edit Booking
function rememberEditCreateBookingInputs(){
	if(isSet($_SESSION['EditCreateBookingInfoArray'])){
		$newValues = $_SESSION['EditCreateBookingInfoArray'];

			// The company selected
		if(isSet($_POST['companyID'])){
			$newValues['TheCompanyID'] = $_POST['companyID'];
		}
			// The user selected
		if(isSet($_POST['displayName'])){
			$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
		}
			// The booking description
		if(isSet($_POST['description'])){
			$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);
		}

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
		if(isSet($_POST['companyID'])){
			$newValues['TheCompanyID'] = $_POST['companyID'];
		}
			// The user selected
		if(isSet($_POST['displayName'])){
			$newValues['BookedBy'] = trimExcessWhitespace($_POST['displayName']);
		}
			// The booking description
		if(isSet($_POST['description'])){
			$newValues['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['description']);
		}
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
			var_dump($_SESSION); // TO-DO: remove after testing is done
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

// This is used when a booking is cancelled by someone else than the booking owner
// e.g. company owner or an admin
function emailUserOnCancelledBooking(){

	if(isSet($_SESSION['cancelBookingOriginalValues'])){
		if($_SESSION['cancelBookingOriginalValues']['SendEmail'] == 1){
			$bookingCreatorUserEmail = $_SESSION['cancelBookingOriginalValues']['UserEmail'];
			$bookingCreatorMeetingInfo = $_SESSION['cancelBookingOriginalValues']['MeetingInfo'];
			$cancelledBy = $_SESSION['cancelBookingOriginalValues']['CancelledBy'];

			if(isSet($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'])){
				$reasonForCancelling = $_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'];
			} else {
				$reasonForCancelling = "No reason given.";
			}

			$emailSubject = "Your meeting has been cancelled!";

			$emailMessage = 
			"A booked meeting has been cancelled by " . $cancelledBy . "!\n" .
			"The meeting was booked for the room " . $bookingCreatorMeetingInfo .
			"\nReason given for cancelling: " . $reasonForCancelling;

			$email = $bookingCreatorUserEmail;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			if(!$mailResult){
				$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

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

				$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing
		}
	} else {
		$_SESSION['BookingUserFeedback'] .= "\nFailed to send an email to the user that the booking got cancelled.";
	}
}

// This is used when an order is cancelled by someone else than the booking owner
// e.g. company owner or an admin
function emailUserOnCancelledOrder(){

	if(isSet($_SESSION['cancelOrderOriginalValues'])){
		if($_SESSION['cancelOrderOriginalValues']['SendEmail'] == 1){
			$bookingCreatorUserEmail = $_SESSION['cancelOrderOriginalValues']['UserEmail'];
			$bookingCreatorMeetingInfo = $_SESSION['cancelOrderOriginalValues']['MeetingInfo'];
			$cancelledBy = $_SESSION['cancelOrderOriginalValues']['CancelledBy'];

			if(isSet($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'])){
				$reasonForCancelling = $_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'];
			} else {
				$reasonForCancelling = "No reason given.";
			}

			$emailSubject = "Your meeting order has been cancelled!";

			$emailMessage = 
			"An order for your booked meeting has been cancelled by " . $cancelledBy . "!\n" .
			"The meeting was booked for the room " . $bookingCreatorMeetingInfo .
			"\nReason given for cancelling the order: " . $reasonForCancelling;

			$email = $bookingCreatorUserEmail;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			if(!$mailResult){
				$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

				// Email failed to be prepared. Store it in database to try again later
				try
				{
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
				}
				catch (PDOException $e)
				{
					$pdo->rollBack();
					$error = 'Error storing email: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing
		}
	} else {
		$_SESSION['BookingUserFeedback'] .= "\nFailed to send an email to the user that the booking got cancelled.";
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

// Function to check if user inputs for Order are correct
function validateOrderUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['OrderCommunicationToStaff']) AND !$invalidInput){
		$orderCommunicationToStaff = trim($_POST['OrderCommunicationToStaff']);
	} else {
		// Doesn't need to be set.
		$orderCommunicationToStaff = "";
	}

	// Update submitted item amounts of originally submitted items
	if(isSet($_SESSION['EditBookingOrderExtraOrdered'])){
		// Update submitted item amounts of originally submitted items
		foreach($_SESSION['EditBookingOrderExtraOrdered'] AS &$extra){
			$extraID = $extra['ExtraID'];
			$postSubmittedAmountName = "extraAmountSelected-" . $extraID;
			if(isSet($_POST[$postSubmittedAmountName]) AND $_POST[$postSubmittedAmountName] > 1){
				$extra['ExtraAmount'] = $_POST[$postSubmittedAmountName];
			}
			unset($extra); // destroy reference.
		}
	}

	if(isSet($_POST['isApprovedByUser']) AND $_POST['isApprovedByUser'] == 1){
		$orderIsApprovedByUser = 1;
	} else {
		$orderIsApprovedByUser = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedOrderCommunicationToStaff = trimExcessWhitespaceButLeaveLinefeed($orderCommunicationToStaff);
	$validatedIsApprovedByUser = $orderIsApprovedByUser;

	// Do actual input validation
	if(validateString($validatedOrderCommunicationToStaff) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditBookingOrderError'] = "Your submitted message to staff has illegal characters in it.";
	}

	// Check if input length is allowed
		// OrderCommunicationToStaff
	$invalidorderCommunicationToStaff = isLengthInvalidOrderMessage($validatedOrderCommunicationToStaff);
	if($invalidorderCommunicationToStaff AND !$invalidInput){
		$_SESSION['EditBookingOrderError'] = "Your submitted message to staff is too long.";
		$invalidInput = TRUE;
	}

	return array($invalidInput, $validatedOrderCommunicationToStaff, $validatedIsApprovedByUser);
}


// Check if we're accessing from a local device
// If so, set that meeting room's info as the default meeting room info
checkIfLocalDevice();

// If user wants to refresh the booking code template when timed out.
if(isSet($_POST['bookingCode']) AND $_POST['bookingCode'] == "Refresh"){
	var_dump($_SESSION);	// TO-DO: Remove before uploading
	include_once 'bookingcode.html.php';
	exit();
}

// If user wants to go back to the main page while in the confirm booking page
if (isSet($_POST['action']) and $_POST['action'] == 'Go Back'){
	unset($_SESSION['confirmOrigins']);
	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
	}

	header("Location: $location");
	exit();
}

// If user wants to go back to the main page while editing
if (isSet($_POST['edit']) and $_POST['edit'] == 'Go Back'){
	unset($_SESSION['confirmOrigins']);
	$_SESSION['normalBookingFeedback'] = "You cancelled your booking editing.";

	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
	}

	header("Location: $location");
	exit();
}

// If user wants to go back to the main page while changing rooms
if (isSet($_POST['changeroom']) and $_POST['changeroom'] == 'Go Back'){
	unset($_SESSION['confirmOrigins']);
	$_SESSION['normalBookingFeedback'] = "You cancelled your meeting room change.";

	clearChangeBookingSessions();

	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $TheMeetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
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

// If user does not want to cancel the meeting/order anyway
if(isSet($_POST['confirmCancel']) AND $_POST['confirmCancel'] == "Abort Cancel"){

	if(isSet($_SESSION['cancelOrderOriginalValues'])){
		$_SESSION['normalBookingFeedback'] = "You did not cancel the order.";
	}

	if(isSet($_SESSION['cancelBookingOriginalValues'])){
		$_SESSION['normalBookingFeedback'] = "You did not cancel the meeting.";
	}

	clearChangeBookingSessions();

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

// If user has confirmed the order/meeting cancel
if(isSet($_POST['confirmCancel']) AND $_POST['confirmCancel'] == "Confirm Cancel"){
	$invalidInput = FALSE;
	// Do input validation
	if(isSet($_POST['cancelMessage']) AND !empty($_POST['cancelMessage'])){
		$cancelMessage = trimExcessWhitespaceButLeaveLinefeed($_POST['cancelMessage']);
	} else {
		$cancelMessage = "";
	}
	if(validateString($cancelMessage) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['confirmReasonError'] = "Your submitted message has illegal characters in it.";
	}

	$invalidCancelMessage = isLengthInvalidBookingDescription($cancelMessage);
	if($invalidCancelMessage AND !$invalidInput){
		$_SESSION['confirmReasonError'] = "Your submitted message is too long.";
		$invalidInput = TRUE;
	}

	if($invalidInput){

		$cancelledBy = $_POST['cancelledBy'];

		var_dump($_SESSION); // TO-DO: Remove when done testing

		include_once 'cancelmessage.html.php';
		exit();
	}

	if(isSet($_SESSION['cancelOrderOriginalValues'])){
		$_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'] = $cancelMessage;
		$_SESSION['refreshCancelOrder'] = TRUE;
	}

	if(isSet($_SESSION['cancelBookingOriginalValues'])){
		$_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'] = $cancelMessage;
		$_SESSION['refreshCancelBooking'] = TRUE;
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
}

// If user wants to cancel a scheduled booked meeting
if ((isSet($_POST['action']) and $_POST['action'] == 'Cancel') OR 
	(isSet($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking'])
	){

	if(isSet($_SESSION['refreshCancelBooking']) AND $_SESSION['refreshCancelBooking']){
		unset($_SESSION['refreshCancelBooking']);
	} else {
		$_SESSION['cancelBookingOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['cancelBookingOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['cancelBookingOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];

		if(isSet($_POST['sendEmail']) AND !empty($_POST['sendEmail'])){
			$_SESSION['cancelBookingOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email']) AND !empty($_POST['Email'])){
			$_SESSION['cancelBookingOriginalValues']['UserEmail'] = $_POST['Email'];
		}
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
		$sql = 'SELECT 		COUNT(*)			AS HitCount,
							b.`userID`			AS UserID,
							u.`email`			AS UserEmail,
							u.`firstName`		AS FirstName,
							u.`lastName`		AS LastName,
							u.`sendEmail`		AS SendEmail
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
			$bookingCreatorUserID = $row['UserID'];
			$bookingCreatorUserEmail = $row['UserEmail'];
			$bookingCreatorUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
			$_SESSION['cancelBookingOriginalValues']['SendEmail'] = $row['SendEmail'];
			$_SESSION['cancelBookingOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;
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
								b.`userID`		AS UserID,
								u.`email`		AS UserEmail,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByUserName = $row['LastName'] . ", " . $row['FirstName'];
				$cancelledByUserEmail = $row['UserEmail'];
				$cancelledByUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
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
			$sql = 'SELECT 		COUNT(*) 		AS HitCount,
								u.`userID`		AS UserID,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByAdminName = $row['LastName'] . ", " . $row['FirstName'];
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
			$location = "http://$_SERVER[HTTP_HOST]/booking/";
		}
		header('Location: ' . $location);
		exit();
	}

	// Only cancel if booking is currently active
	if(isSet($bookingStatus) AND ($bookingStatus == 'Active' OR $bookingStatus == 'Active Today')){

		// Load new template to make user confirm the cancel and an optional way to add a reason for cancelling the meeting
		if(!isSet($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'])){

			if(isSet($cancelledByAdmin)){
				$cancelledBy = "Admin";
			} elseif($cancelledByOwner){
				$cancelledBy = "Owner";
			} else {
				$cancelledBy = "Creator";
			}

			var_dump($_SESSION); // TO-DO: Remove before uploading
			include_once 'cancelmessage.html.php';
			exit();
		}

		if(isSet($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'])){
			$cancelMessage = $_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'];
		} else {
			$cancelMessage = NULL;
		}

		// Update cancellation date for selected booked meeting in database
		try
		{
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
				$s->bindValue(':cancelledByUserID', $SelectedUserID);
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
				$s->bindValue(':cancelledByUserID', $SelectedUserID);
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
			if($cancelledByAdmin){
				$nameOfUserWhoCancelled = $cancelledByAdminName;
				$_SESSION['cancelBookingOriginalValues']['CancelledBy'] = "an Admin: " . $cancelledByAdminName;
			} elseif($cancelledByOwner) {
				$nameOfUserWhoCancelled = $cancelledByUserName;
				$_SESSION['cancelBookingOriginalValues']['CancelledBy'] = "a Company Owner: " . $cancelledByUserName;
			} else {
				$nameOfUserWhoCancelled = $bookingCreatorUserInfo;
				unset($_SESSION['cancelBookingOriginalValues']);
			}

			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($bookingCreatorUserInfo) AND isSet($bookingMeetingInfo)){
				$logEventDescription = 	"A booking with these details was cancelled:" . 
										"\nBooked for User: " . $bookingCreatorUserInfo . 
										"\nMeeting Information: " . $bookingMeetingInfo . 
										"\nIt was cancelled by: " . $nameOfUserWhoCancelled;
			} else {
				$logEventDescription = 	"A booking was cancelled by: " . $nameOfUserWhoCancelled;
			}

			if(isSet($cancelledByAdmin) AND $cancelledByAdmin AND isSet($_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'])){
				$logEventDescription .= "\nReason for cancelling: " . $_SESSION['cancelBookingOriginalValues']['ReasonForCancelling'];
			}

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
		if($cancelledByAdmin OR $cancelledByOwner){
			// only send email to inform the user that the booking was cancelled, if the user didn't cancel their own booking.
			emailUserOnCancelledBooking();
		}
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['normalBookingFeedback'] = "Meeting has already ended. Did not cancel it.";
	}

	$_SESSION['normalBookingFeedback'] = "Successfully cancelled the booking.";

	clearChangeBookingSessions();

	// Load booked meetings list webpage with updated database
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
	}
	header('Location: ' . $location);
	exit();	
}

// If user wants to change the room for the booked meeting
if ((isSet($_POST['action']) and $_POST['action'] == 'Change Room') OR 
	(isSet($_SESSION['refreshChangeBookingRoom']) AND $_SESSION['refreshChangeBookingRoom'])
	){
	if(isSet($_SESSION['refreshChangeBookingRoom']) AND $_SESSION['refreshChangeBookingRoom']){
		unset($_SESSION['refreshChangeBookingRoom']);
	} else {
		$_SESSION['changeRoomOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['changeRoomOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['changeRoomOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];
		if(isSet($_POST['sendEmail'])){
			$_SESSION['changeRoomOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email'])){
			$_SESSION['changeRoomOriginalValues']['UserEmail'] = $_POST['Email'];
		}
	}

	$bookingID = $_SESSION['changeRoomOriginalValues']['BookingID'];
	$bookingStatus = $_SESSION['changeRoomOriginalValues']['BookingStatus'];
	$bookingMeetingInfo = $_SESSION['changeRoomOriginalValues']['MeetingInfo'];

	if(!isSet($_SESSION['changeRoomOriginalBookingValues'])){

		$_SESSION['confirmOrigins'] = "Change Room";
		$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
		unset($_SESSION['confirmOrigins']);

		// Check if selected user ID is creator of booking, owner of the company it's booked for or an admin
		$continueChangeRoom = FALSE;
		$changedByOwner = FALSE;
		$changedByAdmin = FALSE;

		// Get original booking information
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'SELECT 		COUNT(*)			AS HitCount,
								b.`userID`			AS UserID,
								b.`startDateTime` 	AS StartDateTime,
								b.`endDateTime` 	AS EndDateTime,
								b.`meetingRoomID` 	AS MeetingRoomID,
								(
									SELECT 	`name`
									FROM 	`meetingroom`
									WHERE	`meetingRoomID` = b.`meetingRoomID`
								)					AS MeetingRoomName,
								u.`email`			AS UserEmail,
								u.`firstName`,
								u.`lastName`,
								u.`sendEmail`
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
				$bookingCreatorUserID = $row['UserID'];
				$bookingCreatorUserEmail = $row['UserEmail'];
				$bookingCreatorUserInfo = $row['lastName'] . ", " . $row['firstName'] . " - " . $row['UserEmail'];
				$bookingStartDateTime = $row['StartDateTime'];
				$bookingEndDateTime = $row['EndDateTime'];
				$bookingMeetingRoomID = $row['MeetingRoomID'];
				$originalMeetingRoomName = $row['MeetingRoomName'];

				$_SESSION['changeRoomOriginalValues']['SendEmail'] = $row['sendEmail'];
				$_SESSION['changeRoomOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;
				$_SESSION['changeRoomOriginalBookingValues'] = array(
																		'UserID' => $bookingCreatorUserID,
																		'Email' => $bookingCreatorUserEmail,
																		'UserInfo' => $bookingCreatorUserInfo,
																		'StartDateTime' => $bookingStartDateTime,
																		'EndDateTime' => $bookingEndDateTime,
																		'MeetingRoomID' => $bookingMeetingRoomID,
																		'MeetingRoomName' => $originalMeetingRoomName,
																		'ContinueChangeRoom' => FALSE
																	);

				// Check if the user is the creator of the booking	
				if(isSet($bookingCreatorUserID) AND !empty($bookingCreatorUserID) AND $bookingCreatorUserID == $SelectedUserID){
					$continueChangeRoom = TRUE;
					$_SESSION['changeRoomOriginalBookingValues']['ContinueChangeRoom'] = TRUE;
					unset($_SESSION['changeRoomChangedByUser']);
					unset($_SESSION['changeRoomChangedBy']);
				}
			} 
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error changing booked meeting room: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

			// Check if the user is an owner of the company the booking is booked for
		if(!$continueChangeRoom) {
			try
			{
				$sql = 'SELECT 		COUNT(*)		AS HitCount,
									b.`userID`		AS UserID,
									u.`email`		AS UserEmail,
									u.`firstName`	AS FirstName,
									u.`lastName`	AS LastName
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
					$changedByUserID = $row['UserID'];
					$changedByUserName = $row['LastName'] . ", " . $row['FirstName'];
					$changedByUserEmail = $row['UserEmail'];
					$changedByUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
					$continueChangeRoom = TRUE;
					$changedByOwner = TRUE;
					$_SESSION['changeRoomChangedBy'] = "Owner";
					$_SESSION['changeRoomOriginalBookingValues']['ContinueChangeRoom'] = TRUE;
					$_SESSION['changeRoomChangedByUser'] = $changedByUserName;
				}
			}
			catch (PDOException $e)
			{
				$pdo = null;
				$error = 'Error changing booked meeting room: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				exit();
			}
		}

			// Check if the user is an admin
			// Only needed if the the user isn't the creator of the booking or an owner
		if(!$continueChangeRoom) {
			try
			{
				$sql = 'SELECT 		COUNT(*) 		AS HitCount,
									u.`UserID`		AS UserID,
									u.`firstName`	AS FirstName,
									u.`lastName`	AS LastName
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
					$changedByAdminID = $row['UserID'];
					$changedByAdminName = $row['LastName'] . ", " . $row['FirstName'];
					$continueChangeRoom = TRUE;
					$changedByAdmin = TRUE;
					$_SESSION['changeRoomChangedBy'] = "Admin";
					$_SESSION['changeRoomOriginalBookingValues']['ContinueChangeRoom'] = TRUE;
					$_SESSION['changeRoomChangedByUser'] = $changedByAdminName;
				}
			}
			catch (PDOException $e)
			{
				$pdo = null;
				$error = 'Error changing booked meeting room: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				exit();
			}
		}

	} elseif($_SESSION['changeRoomOriginalBookingValues']['ContinueChangeRoom']){
		$bookingStartDateTime = $_SESSION['changeRoomOriginalBookingValues']['StartDateTime'];
		$bookingEndDateTime = $_SESSION['changeRoomOriginalBookingValues']['EndDateTime'];
		$originalMeetingRoomName = $_SESSION['changeRoomOriginalBookingValues']['MeetingRoomName'];
		$continueChangeRoom = $_SESSION['changeRoomOriginalBookingValues']['ContinueChangeRoom'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
	}

	if($continueChangeRoom === FALSE){

		$_SESSION['normalBookingFeedback'] = "You cannot change room for this booked meeting.";

		$pdo = null;
		clearChangeBookingSessions();

		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = "http://$_SERVER[HTTP_HOST]/booking/";
		}

		header('Location: ' . $location);
		exit();
	}

	// Get Available/Occupied rooms in the time period of the original booked meeting.
		// We don't care about room events for occupied rooms, since we can only swap with another booked room.
		// But we also need to make sure that the two bookings can actually swap properly (different booking times)
	try
	{
		$sql = 'SELECT 		COUNT(*)			AS HitCount,
							m.`name`			AS MeetingRoomName,
							m.`meetingRoomID` 	AS MeetingRoomID,
							b.`bookingID`		AS BookingID
				FROM		`meetingroom` m
				INNER JOIN	`booking` b
				ON 			b.`meetingRoomID` = m.`meetingRoomID`
				WHERE 		b.`actualEndDateTime` IS NULL
				AND			b.`dateTimeCancelled` IS NULL
				AND
						(		
								(
									b.`startDateTime` >= :startDateTime AND 
									b.`startDateTime` < :endDateTime
								) 
						OR 		(
									b.`endDateTime` > :startDateTime AND 
									b.`endDateTime` <= :endDateTime
								)
						OR 		(
									:endDateTime > b.`startDateTime` AND 
									:endDateTime < b.`endDateTime`
								)
						OR 		(
									:startDateTime > b.`startDateTime` AND 
									:startDateTime < b.`endDateTime`
								)
						)
				AND			b.`bookingID` <> :bookingID
				GROUP BY	m.`meetingRoomID`';
		$s = $pdo->prepare($sql);
		$s->bindValue(':bookingID', $bookingID);
		$s->bindValue(':startDateTime', $bookingStartDateTime);
		$s->bindValue(':endDateTime', $bookingEndDateTime);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result) AND sizeOf($result) > 0){
			foreach($result AS $row){
				if($row['HitCount'] == 1){
					$occupiedRooms[] = array(
												'MeetingRoomName' => $row['MeetingRoomName'],
												'MeetingRoomID' => $row['MeetingRoomID'],
												'BookingID' => $row['BookingID']
											);
				} else {
					if(isSet($unavailableOccupiedRooms)){
						$unavailableOccupiedRooms .= "\n" . $row['MeetingRoomName'];
					} else {
						$unavailableOccupiedRooms = $row['MeetingRoomName'];
					}
					
				}
			}
		}

			// For available rooms we have to check what rooms are taken by both bookings and events.
			// Here we use the same approach we use for "checking if timeslot is taken" for bookings, to keep it consistent.
		$sql = "SELECT 		m.`meetingRoomID`	AS MeetingRoomID,
							m.`name`			AS MeetingRoomName
				FROM 		`meetingroom` m
				WHERE		m.`meetingRoomID` 
				NOT IN
				(
					SELECT 		b.`meetingRoomID`
					FROM 		`booking` b
					WHERE 		b.`dateTimeCancelled` IS NULL
					AND			b.`actualEndDateTime` IS NULL
					AND
							(		
									(
										b.`startDateTime` >= :startDateTime AND 
										b.`startDateTime` < :endDateTime
									) 
							OR 		(
										b.`endDateTime` > :startDateTime AND 
										b.`endDateTime` <= :endDateTime
									)
							OR 		(
										:endDateTime > b.`startDateTime` AND 
										:endDateTime < b.`endDateTime`
									)
							OR 		(
										:startDateTime > b.`startDateTime` AND 
										:startDateTime < b.`endDateTime`
									)
							)
				)
				AND			m.`meetingRoomID` 
				NOT IN
				(
					SELECT 		rev.`meetingRoomID`
					FROM 		`roomevent` rev
					WHERE 
							(		
									(
										rev.`startDateTime` >= :startDateTime AND 
										rev.`startDateTime` < :endDateTime
									) 
							OR 		(
										rev.`endDateTime` > :startDateTime AND 
										rev.`endDateTime` <= :endDateTime
									)
							OR 		(
										:endDateTime > rev.`startDateTime` AND 
										:endDateTime < rev.`endDateTime`
									)
							OR 		(
										:startDateTime > rev.`startDateTime` AND 
										:startDateTime < rev.`endDateTime`
									)
							)
				)";
		$s = $pdo->prepare($sql);
		$s->bindValue(':startDateTime', $bookingStartDateTime);
		$s->bindValue(':endDateTime', $bookingEndDateTime);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result) AND sizeOf($result) > 0){
			foreach($result AS $row){
				$availableRooms[] = array(
											'MeetingRoomName' => $row['MeetingRoomName'],
											'MeetingRoomID' => $row['MeetingRoomID']
										);
			}
		}
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error getting occupied meeting rooms: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	var_dump($_SESSION); // TO-DO: Remove after done testing

	unset($_SESSION['bookingCodeUserID']);

	include_once 'changeroom.html.php';
	exit();
}

// If user wants to change the room for the booked meeting
if ((isSet($_POST['changeroom']) and $_POST['changeroom'] == 'Confirm Change') OR 
	(isSet($_SESSION['refreshConfirmBookingRoom']) AND $_SESSION['refreshConfirmBookingRoom'])
	){

	if(!isSet($_SESSION['refreshConfirmBookingRoom'])){
		$changeToAvailableRoom = FALSE;
		$changeToOccupiedRoom = FALSE;
		if(isSet($_POST['availableRooms']) AND !empty($_POST['availableRooms'])){
			$changeToAvailableRoom = TRUE;
			$SelectedMeetingRoomID = $_POST['availableRooms'];
		}	
		if(isSet($_POST['occupiedRooms']) AND !empty($_POST['occupiedRooms'])){
			$changeToOccupiedRoom = TRUE;
			$SelectedMeetingRoomIDAndBookingID = $_POST['occupiedRooms'];
			$explode_result = explode("|", $SelectedMeetingRoomIDAndBookingID);
			$SelectedMeetingRoomID = $explode_result[0];
			$bookingID = $explode_result[1];
		}

		if(!$changeToAvailableRoom AND !$changeToOccupiedRoom){
			$_SESSION['BookingRoomChangeError'] = "You have to choose the new room you want your meeting to take place in.";
			$_SESSION['refreshChangeBookingRoom'] = TRUE;
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$location = "http://$_SERVER[HTTP_HOST]/booking/";
			}
			header('Location: ' . $location);
			exit();
		}

		if($changeToAvailableRoom AND $changeToOccupiedRoom){
			$_SESSION['BookingRoomChangeError'] = "You have to choose between selecting an available room or an occupied room. You can not select both.";
			$_SESSION['refreshChangeBookingRoom'] = TRUE;
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$location = "http://$_SERVER[HTTP_HOST]/booking/";
			}
			header('Location: ' . $location);
			exit();
		}
		$_SESSION['changeToMeetingRoomID'] = $SelectedMeetingRoomID;
		$_SESSION['changeToOccupiedRoomBookingID'] = $bookingID;
	} else {
		unset($_SESSION['refreshConfirmBookingRoom']);
		$SelectedMeetingRoomID = $_SESSION['changeToMeetingRoomID'];
		$bookingID = $_SESSION['changeToOccupiedRoomBookingID'];
		$changeToOccupiedRoom = TRUE;
	}

	// Go through process of validating booking code, just like we did on accessing this form, for the other booked meeting room
	if($changeToOccupiedRoom){
		$_SESSION['confirmOrigins'] = "Confirm Change";
		$SelectedUserID = checkIfLocalDeviceOrLoggedIn();
		unset($_SESSION['confirmOrigins']);

		// Check if selected user ID is creator of booking, owner of the company it's booked for or an admin
		$continueChangeRoom = FALSE;
		$changedByOwner = FALSE;
		$changedByAdmin = FALSE;

		// Get original booking information
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'SELECT 		COUNT(*)			AS HitCount,
								b.`userID`			AS UserID,
								b.`startDateTime` 	AS StartDateTime,
								b.`endDateTime` 	AS EndDateTime,
								b.`meetingRoomID` 	AS MeetingRoomID,
								(
									SELECT 	`name`
									FROM 	`meetingroom`
									WHERE	`meetingRoomID` = b.`meetingRoomID`
								)					AS MeetingRoomName,
								u.`email`			AS UserEmail,
								u.`firstName`		AS FirstName,
								u.`lastName`		AS LastName,
								u.`sendEmail`
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
				$bookingCreatorUserID = $row['UserID'];
				$bookingCreatorUserEmail = $row['UserEmail'];
				$bookingCreatorUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
				$bookingCreatorSendEmail = $row['sendEmail'];
				$bookingStartDateTime = $row['StartDateTime'];
				$bookingEndDateTime = $row['EndDateTime'];
				$bookingMeetingRoomID = $row['MeetingRoomID'];
				$occupiedMeetingRoomName = $row['MeetingRoomName'];

				// Check if the user is the creator of the booking	
				if(isSet($bookingCreatorUserID) AND !empty($bookingCreatorUserID) AND $bookingCreatorUserID == $SelectedUserID){
					$continueChangeRoom = TRUE;
				}
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			clearChangeBookingSessions();
			$error = 'Error changing booked meeting room: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

			// Check if the user is an owner of the company the booking is booked for
		if(!$continueChangeRoom) {
			try
			{
				$sql = 'SELECT 		COUNT(*)		AS HitCount,
									b.`userID`		AS UserID,
									u.`email`		AS UserEmail,
									u.`firstName`	AS FirstName,
									u.`lastName`	AS LastName
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
					$changedByUserID = $row['UserID'];
					$changedByUserName = $row['LastName'] . ", " . $row['FirstName'];
					$changedByUserEmail = $row['UserEmail'];
					$changedByUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
					$continueChangeRoom = TRUE;
					$changedByOwner = TRUE;
				}
			}
			catch (PDOException $e)
			{
				$pdo = null;
				clearChangeBookingSessions();
				$error = 'Error changing booked meeting room: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				exit();
			}
		}

			// Check if the user is an admin
			// Only needed if the the user isn't the creator of the booking or an owner
		if(!$continueChangeRoom) {
			try
			{
				$sql = 'SELECT 		COUNT(*) 		AS HitCount,
									u.`userID`		AS UserID,
									u.`firstName`	AS FirstName,
									u.`lastName`	AS LastName
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
					$changedByAdminID = $row['UserID'];
					$changedByAdminName = $row['LastName'] . ", " . $row['FirstName'];
					$continueChangeRoom = TRUE;
					$changedByAdmin = TRUE;
				}
			}
			catch (PDOException $e)
			{
				$pdo = null;
				clearChangeBookingSessions();
				$error = 'Error changing booked meeting room: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				exit();
			}
		}

		if($continueChangeRoom === FALSE){
			$pdo = null;
			$_SESSION['normalBookingFeedback'] = "You cannot change room for this booked meeting.";
			clearChangeBookingSessions();
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$location = "http://$_SERVER[HTTP_HOST]/booking/";
			}
			header('Location: ' . $location);
			exit();
		}

		// Double check that the timeslots are available
		$originalStartDateTime = $_SESSION['changeRoomOriginalBookingValues']['StartDateTime'];
		$originalEndDateTime = $_SESSION['changeRoomOriginalBookingValues']['EndDateTime'];
		$occupiedStartDateTime = $bookingStartDateTime;
		$occupiedEndDateTime = $bookingEndDateTime;
		$originalBookingID = $_SESSION['changeRoomOriginalValues']['BookingID'];
		$occupiedBookingID = $bookingID;
		$originalMeetingRoomID = $_SESSION['changeRoomOriginalBookingValues']['MeetingRoomID'];
		$occupiedMeetingRoomID = $SelectedMeetingRoomID;

		try
		{
			$sql =	" 	SELECT 	SUM(cnt)	AS HitCount
						FROM 
						(
							(
							SELECT 		COUNT(*) AS cnt
							FROM 		`booking` b
							WHERE 		b.`meetingRoomID` = :OldMeetingRoomID
							AND			b.`bookingID` <> :OldBookingID
							AND			b.`dateTimeCancelled` IS NULL
							AND			b.`actualEndDateTime` IS NULL
							AND		
									(		
											(
												b.`startDateTime` >= :NewStartTime AND 
												b.`startDateTime` < :NewEndTime
											) 
									OR 		(
												b.`endDateTime` > :NewStartTime AND 
												b.`endDateTime` <= :NewEndTime
											)
									OR 		(
												:NewEndTime > b.`startDateTime` AND 
												:NewEndTime < b.`endDateTime`
											)
									OR 		(
												:NewStartTime > b.`startDateTime` AND 
												:NewStartTime < b.`endDateTime`
											)
									)
							LIMIT 1
							)
							UNION
							(
							SELECT 		COUNT(*) AS cnt
							FROM 		`roomevent` rev
							WHERE 		rev.`meetingRoomID` = :OldMeetingRoomID
							AND	
									(		
											(
												rev.`startDateTime` >= :NewStartTime AND 
												rev.`startDateTime` < :NewEndTime
											) 
									OR 		(
												rev.`endDateTime` > :NewStartTime AND 
												rev.`endDateTime` <= :NewEndTime
											)
									OR 		(
												:NewEndTime > rev.`startDateTime` AND 
												:NewEndTime < rev.`endDateTime`
											)
									OR 		(
												:NewStartTime > rev.`startDateTime` AND 
												:NewStartTime < rev.`endDateTime`
											)
									)
							LIMIT 1
							)
							UNION
							(
							SELECT 		COUNT(*) AS cnt
							FROM 		`booking` b
							WHERE 		b.`meetingRoomID` = :NewMeetingRoomID
							AND			b.`bookingID` <> :NewBookingID
							AND			b.`dateTimeCancelled` IS NULL
							AND			b.`actualEndDateTime` IS NULL
							AND		
									(		
											(
												b.`startDateTime` >= :OldStartTime AND 
												b.`startDateTime` < :OldEndTime
											) 
									OR 		(
												b.`endDateTime` > :OldStartTime AND 
												b.`endDateTime` <= :OldEndTime
											)
									OR 		(
												:OldEndTime > b.`startDateTime` AND 
												:OldEndTime < b.`endDateTime`
											)
									OR 		(
												:OldStartTime > b.`startDateTime` AND 
												:OldStartTime < b.`endDateTime`
											)
									)
							LIMIT 1
							)
							UNION
							(
							SELECT 		COUNT(*) AS cnt
							FROM 		`roomevent` rev
							WHERE 		rev.`meetingRoomID` = :NewMeetingRoomID
							AND	
									(		
											(
												rev.`startDateTime` >= :OldStartTime AND 
												rev.`startDateTime` < :OldEndTime
											) 
									OR 		(
												rev.`endDateTime` > :OldStartTime AND 
												rev.`endDateTime` <= :OldEndTime
											)
									OR 		(
												:OldEndTime > rev.`startDateTime` AND 
												:OldEndTime < rev.`endDateTime`
											)
									OR 		(
												:OldStartTime > rev.`startDateTime` AND 
												:OldStartTime < rev.`endDateTime`
											)
									)
							LIMIT 1
							)
						) AS TimeSlotTaken";
			$s = $pdo->prepare($sql);

			$s->bindValue(':OldMeetingRoomID', $originalMeetingRoomID);
			$s->bindValue(':NewMeetingRoomID', $occupiedMeetingRoomID);
			$s->bindValue(':OldStartTime', $originalStartDateTime);
			$s->bindValue(':OldEndTime', $originalEndDateTime);
			$s->bindValue(':NewStartTime', $occupiedStartDateTime);
			$s->bindValue(':NewEndTime', $occupiedEndDateTime);
			$s->bindValue(':OldBookingID', $originalBookingID);
			$s->bindValue(':NewBookingID', $occupiedBookingID);
			$s->execute();

			$row = $s->fetch(PDO::FETCH_ASSOC);

			if(isSet($row) AND $row['HitCount'] > 0){
				// We can't change to this room, since it's already taken.
				clearChangeBookingSessions();
				$pdo = null;

				$_SESSION['normalBookingFeedback'] = "Could not swap rooms because the booked time slot of at least one meeting is no longer available in the other room.";

 				if(isSet($_GET['meetingroom'])){
					$meetingRoomID = $_GET['meetingroom'];
					$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
				} else {
					$location = "http://$_SERVER[HTTP_HOST]/booking/";
				}

				header('Location: ' . $location);
				exit();
			}
		}
		catch(PDOException $e)
		{
			$error = 'Error checking if booking time is available: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			clearChangeBookingSessions();
			exit();
		}

		// Update both bookings and swap their meeting room IDs
		try
		{
			$pdo->beginTransaction();
			$sql = 'UPDATE	`booking`
					SET		`meetingRoomID` = :meetingRoomID
					WHERE	`bookingID` = :bookingID';

			$s = $pdo->prepare($sql);
			$s->bindValue(':meetingRoomID', $occupiedMeetingRoomID);
			$s->bindValue(':bookingID', $originalBookingID);
			$s->execute();

			$sql = 'UPDATE	`booking`
					SET		`meetingRoomID` = :meetingRoomID
					WHERE	`bookingID` = :bookingID';

			$s = $pdo->prepare($sql);
			$s->bindValue(':meetingRoomID', $originalMeetingRoomID);
			$s->bindValue(':bookingID', $occupiedBookingID);
			$s->execute();

			$pdo->commit();
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$pdo = null;
			clearChangeBookingSessions();
			$error = 'Error changing booked meeting room: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
		$originalMeetingRoomName = $_SESSION['changeRoomOriginalBookingValues']['MeetingRoomName'];

		$_SESSION['normalBookingFeedback'] = "Successfully swapped the booked meetings for the rooms $originalMeetingRoomName and $occupiedMeetingRoomName.";

		// Send email to the original user if the room is changed by someone else.
		if(isSet($_SESSION['changeRoomChangedBy'])){

			if($_SESSION['changeRoomOriginalValues']['SendEmail'] == 1){

				$startDateTime = $_SESSION['changeRoomOriginalBookingValues']['StartDateTime'];
				$endDateTime = $_SESSION['changeRoomOriginalBookingValues']['EndDateTime'];
				$displayStartDate = convertDatetimeToFormat($startDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
				$displayEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

				$emailSubject = "New Booking Information!";

				$changedByUser = $_SESSION['changeRoomChangedByUser'];
				if(isSet($_SESSION['changeRoomChangedBy']) AND $_SESSION['changeRoomChangedBy'] == "Admin"){
					$emailMessage = "Your meeting has been moved to a new room by the admin: $changedByUser!\n";
				} elseif(isSet($_SESSION['changeRoomChangedBy']) AND $_SESSION['changeRoomChangedBy'] == "Owner"){
					$emailMessage = "Your meeting has been moved to a new room by your company owner: $changedByUser!\n";
				}

				$emailMessage .=
				"The current meeting information has been updated: \n" .
				"Old Meeting Room: " . $originalMeetingRoomName . ".\n" .
				"New Meeting Room: " . $occupiedMeetingRoomName . ".\n" .
				"Start Time: " . $displayStartDate . ".\n" .
				"End Time: " . $displayEndDate . ".";

				$email = $_SESSION['changeRoomOriginalValues']['UserEmail'];

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				if(!$mailResult){
					$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

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

					$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
				}

				$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing	

			} elseif($_SESSION['changeRoomOriginalValues']['SendEmail'] == 0){
				$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails."; // TO-DO: remove when done testing
			}
		}

		// Send email to the original user of the occupied room, if it's changed by someone else.
		if(isSet($changedByAdmin) OR isSet($changedByOwner)){

			if($bookingCreatorSendEmail == 1){

				$displayStartDate = convertDatetimeToFormat($bookingStartDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
				$displayEndDate = convertDatetimeToFormat($bookingEndDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
				$emailSubject = "New Booking Information!";

				if(isSet($changedByAdmin)){
					$emailMessage = "Your meeting has been moved to a new room by the admin: $changedByAdminName!\n";
				} elseif(isSet($changedByOwner)){
					$emailMessage = "Your meeting has been moved to a new room by your company owner: $changedByUserName!\n";
				}

				$emailMessage .=
				"The current meeting information has been updated: \n" .
				"Old Meeting Room: " . $occupiedMeetingRoomName . ".\n" .
				"New Meeting Room: " . $originalMeetingRoomName . ".\n" .
				"Start Time: " . $displayStartDate . ".\n" .
				"End Time: " . $displayEndDate . ".";

				$email = $bookingCreatorUserEmail;

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				if(!$mailResult){
					$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

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

					$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
				}

				$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove after testing

			} elseif($bookingCreatorSendEmail == 0){
				$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails."; // TO-DO: remove when done testing
			}
		}
	} else {
		// Just change booked room to the selected available room
			// Double check that the room is available for that timeslot	
		$originalStartDateTime = $_SESSION['changeRoomOriginalBookingValues']['StartDateTime'];
		$originalEndDateTime = $_SESSION['changeRoomOriginalBookingValues']['EndDateTime'];

		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();	

			$sql =	" 	SELECT 	SUM(cnt)	AS HitCount,
								(
									SELECT 	`name` 
									FROM 	`meetingroom` 
									WHERE 	`meetingRoomID` = :MeetingRoomID
								) 			AS MeetingRoomName
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

			$s->bindValue(':MeetingRoomID', $SelectedMeetingRoomID);
			$s->bindValue(':StartTime', $originalStartDateTime);
			$s->bindValue(':EndTime', $originalEndDateTime);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);
			if(isSet($row) AND $row['HitCount'] > 0){
				// We can't change to this room, since it's already taken.
				clearChangeBookingSessions();
				$pdo = null;

				$_SESSION['normalBookingFeedback'] = "Could not change your meeting to your selected room, since it's already taken.";

				if(isSet($_GET['meetingroom'])){
					$meetingRoomID = $_GET['meetingroom'];
					$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
				} else {
					$location = "http://$_SERVER[HTTP_HOST]/booking/";
				}
				header('Location: ' . $location);
				exit();
			} elseif(isSet($row) AND empty($row['HitCount'])){
				$newMeetingRoomName = $row['MeetingRoomName'];
			}
		}
		catch(PDOException $e)
		{
			$error = 'Error checking if booking time is available: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			clearChangeBookingSessions();
			exit();
		}

		try
		{
			$originalBookingID = $_SESSION['changeRoomOriginalValues']['BookingID'];

			$sql = 'UPDATE	`booking`
					SET		`meetingRoomID` = :meetingRoomID
					WHERE	`bookingID` = :bookingID';

			$s = $pdo->prepare($sql);
			$s->bindValue(':meetingRoomID', $SelectedMeetingRoomID);
			$s->bindValue(':bookingID', $originalBookingID);
			$s->execute();
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error changing booked meeting room: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			clearChangeBookingSessions();
			exit();
		}

		$_SESSION['normalBookingFeedback'] = "Successfully changed the room for your meeting.";

		// Send email to the original user if the room is changed by someone else. (Changing to available room)
		if(isSet($_SESSION['changeRoomChangedBy'])){

			if($_SESSION['changeRoomOriginalValues']['SendEmail'] == 1){

				$originalMeetingRoomName = $_SESSION['changeRoomOriginalBookingValues']['MeetingRoomName'];
				$startDateTime = $_SESSION['changeRoomOriginalBookingValues']['StartDateTime'];
				$endDateTime = $_SESSION['changeRoomOriginalBookingValues']['EndDateTime'];
				$displayStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
				$displayEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

				$emailSubject = "New Booking Information!";

				$changedByUser = $_SESSION['changeRoomChangedByUser'];
				if(isSet($_SESSION['changeRoomChangedBy']) AND $_SESSION['changeRoomChangedBy'] == "Admin"){
					$emailMessage = "Your meeting has been moved to a new room by the admin: $changedByUser!\n";
				} elseif(isSet($_SESSION['changeRoomChangedBy']) AND $_SESSION['changeRoomChangedBy'] == "Owner"){
					$emailMessage = "Your meeting has been moved to a new room by your company owner: $changedByUser!\n";
				}

				$emailMessage .=
				"The current meeting information has been updated: \n" .
				"Old Meeting Room: " . $originalMeetingRoomName . ".\n" .
				"New Meeting Room: " . $newMeetingRoomName . ".\n" .
				"Start Time: " . $displayStartDate . ".\n" .
				"End Time: " . $displayEndDate . ".";

				$email = $_SESSION['changeRoomOriginalValues']['UserEmail'];

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				if(!$mailResult){
					$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

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

					$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
				}

				$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing	

			} elseif($_SESSION['changeRoomOriginalValues']['SendEmail'] == 0){
				$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails."; // TO-DO: remove when done testing
			}
		}
	}

	clearChangeBookingSessions();		

	if(isSet($_GET['meetingroom']) AND !empty($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
	}

	header('Location: ' . $location);
	exit();
}

// BOOKING OVERVIEW CODE SNIPPETS // END //

// ADD BOOKING CODE SNIPPET // START //

// Handles removing booking code timeout on the device if an admin submits their booking code
if(isSet($_POST['action']) AND $_POST['action'] == "Remove Timeout"){

	if(updateAdminBookingCodeGuesses() === FALSE){
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

	// Code is a valid digit. Check if it matches with an admin in our database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		// Get booking information
		$pdo = connect_to_db();
		// Get name and IDs for meeting rooms
		$sql = 'SELECT 		COUNT(*)		AS HitCount
				FROM 		`user` u
				INNER JOIN 	`accesslevel` a
				ON			a.`AccessID` = u.`AccessID`
				WHERE		u.`isActive` = 1
				AND			a.`AccessName` = "Admin"
				AND			u.`bookingCode` = :bookingCode
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':bookingCode',$hashedBookingCode);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		//Close connection
		$pdo = null;

		if($row['HitCount'] > 0){
			unset($_SESSION['bookingCodeGuesses']);
			unset($_SESSION['adminBookingCodeGuesses']);
			$_SESSION['confirmBookingCodeError'] = "Successfully removed timeout.";

			var_dump($_SESSION); // TO-DO: Remove after testing

			include_once 'bookingcode.html.php';
			exit();
		} else {
			// Remember last datetime we guessed wrong
			$_SESSION['adminBookingCodeGuesses'][] = getDatetimeNow();
			$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an incorrect admin code.";

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

// Handles booking code check
if(isSet($_POST['action']) AND $_POST['action'] == "Confirm Code"){

	if(updateBookingCodeGuesses() === FALSE){
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

		if($row['HitCount'] > 0){
			// Booking code is a valid user
			unset($_SESSION['bookingCodeGuesses']);
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
			if($_SESSION['confirmOrigins'] == "Change Room"){
				$_SESSION['refreshChangeBookingRoom'] = TRUE;
				unset($_SESSION['confirmOrigins']);
			}
			if($_SESSION['confirmOrigins'] == "Confirm Change"){
				$_SESSION['refreshConfirmBookingRoom'] = TRUE;
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
			unset($_SESSION['bookingCodeUserID']);
			// Remember last datetime we guessed wrong
			$_SESSION['bookingCodeGuesses'][] = getDatetimeNow();
			if(!isSet($_SESSION['confirmBookingCodeError'])){
				$_SESSION['confirmBookingCodeError'] = "The booking code you submitted is an incorrect code.";
			}

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
	(isSet($_SESSION['refreshAddCreateBooking']) AND $_SESSION['refreshAddCreateBooking'])
	){
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
								u.`bookingdescription`, 
								u.`displayname`,
								u.`firstName`,
								u.`lastName`,
								u.`email`,
								u.`sendEmail`,
								a.`AccessName`
					FROM 		`user` u
					INNER JOIN 	`accesslevel` a
					ON			u.`AccessID` = a.`AccessID`
					WHERE 		u.`userID` = :userID
					LIMIT 		1";
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
			if(isSet($result['displayname'])){
				$displayName = $result['displayname'];
			} else {
				$displayName = "";
			}

			if(isSet($result['bookingdescription'])){
				$description = $result['bookingdescription'];
			} else {
				$description = "";
			}

			if(!empty($result['firstName'])){
				$firstname = $result['firstName'];
			}

			if(!empty($result['lastName'])){
				$lastname = $result['lastName'];
			}	

			if(!empty($result['email'])){
				$email = $result['email'];
			}

			if(!empty($result['sendEmail'])){
				$sendEmail = $result['sendEmail'];
			}

			if(!empty($result['AccessName'])){
				$access = $result['AccessName'];
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
													'sendEmail' => '',
													'Access' => ''
												);

		$_SESSION['AddCreateBookingInfoArray']['UserDefaultBookingDescription'] = $description;
		$_SESSION['AddCreateBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
		$_SESSION['AddCreateBookingInfoArray']['UserFirstname'] = $firstname;
		$_SESSION['AddCreateBookingInfoArray']['UserLastname'] = $lastname;
		$_SESSION['AddCreateBookingInfoArray']['BookedBy'] = $firstname . " " . $lastname;
		$_SESSION['AddCreateBookingInfoArray']['UserEmail'] = $email;
		$_SESSION['AddCreateBookingInfoArray']['TheUserID'] = $SelectedUserID;
		$_SESSION['AddCreateBookingInfoArray']['sendEmail'] = $sendEmail;
		$_SESSION['AddCreateBookingInfoArray']['Access'] = $access;

		if(isSet($_GET['meetingroom']) AND !empty($_GET['meetingroom'])){
			$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = $_GET['meetingroom'];
		}

		$_SESSION['AddCreateBookingOriginalInfoArray'] = $_SESSION['AddCreateBookingInfoArray'];
	}

	if(!isSet($_SESSION['AddCreateBookingAvailableExtra'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			// Get booking information
			$pdo = connect_to_db();

			// Get all available extras that the user can use to add
			// That are not already in the order
			$sql = 'SELECT 	`extraID`		AS ExtraID,
							`name`			AS ExtraName,
							`description`	AS ExtraDescription,
							`price`			AS ExtraPrice
					FROM 	`extra`
					WHERE	`isAlternative` = 0';
			$result = $pdo->query($sql);
			$availableExtra = $result->fetchAll(PDO::FETCH_ASSOC);
			$_SESSION['AddCreateBookingAvailableExtra'] = $availableExtra;

			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching available extras: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$availableExtra = $_SESSION['AddCreateBookingAvailableExtra'];
	}

	$availableExtrasNumber = sizeOf($availableExtra);

		// Get company information for the companies the user works for
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT		c.`companyID`,
							c.`dateTimeCreated`,
							c.`name` 					AS companyName,
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
								AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
									'startDate' => $row['startDate'],
									'dateTimeCreated' => $row['dateTimeCreated'],
									'endDate' => $displayEndDate,
									'creditsGiven' => $companyMinuteCredits,
									'creditsRemaining' => $displayCompanyCreditsRemaining,
									'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
									'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining,
									'HourPriceOverCredit' => $displayOverHourPrice
									);
				$_SESSION['AddCreateBookingCompanyArray'] = $company;
			}

		$pdo = null;

		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isSet($company)){
			if(sizeOf($company) > 1){
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
				$_SESSION['AddCreateBookingInfoArray']['HourPriceOverCredit'] = $company[0]['HourPriceOverCredit'];
				$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = "Booked for " . $company[0]['companyName'];
			}
		} else {
			// User is NOT in a company

			$_SESSION['AddCreateBookingSelectedACompany'] = TRUE;
			unset($_SESSION['AddCreateBookingDisplayCompanySelect']);
			unset($_SESSION['AddCreateBookingCompanyArray']);
			$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['AddCreateBookingInfoArray']['BookedForCompany'] = "";
			$_SESSION['AddCreateBookingInfoArray']['CreditsRemaining'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['PotentialExtraMonthlyTimeUsed'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['PotentialCreditsRemaining'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['HourPriceOverCredit'] = "N/A";
			$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = "";
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

	if(isSet($row['Access'])){
		$access = $row['Access'];
	} else {
		$access = '';
	}

	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];	

	$_SESSION['AddCreateBookingInfoArray'] = $row; // Remember the company/user info we changed based on user choice

	// order information (step 2)
	if(isSet($_SESSION['AddCreateBookingOrderUserNotes'])){
		$userNotes = $_SESSION['AddCreateBookingOrderUserNotes'];
	} else {
		$userNotes = "";
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done
	// Change form
	include 'addbooking.html.php';
	exit();
}

// When the user has added the needed information and wants to add the booking
if ((isSet($_POST['add']) AND $_POST['add'] == "Add Booking") OR 
	(isSet($_SESSION['refreshAddCreateBookingConfirmed']) AND $_SESSION['refreshAddCreateBookingConfirmed'])
	){
	// Validate user inputs
	if(!isSet($_SESSION['refreshAddCreateBookingConfirmed'])){
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

		if(isSet($_POST['companyID']) AND !empty($_POST['companyID'])){
			$companyID = $_POST['companyID'];
		} else {
			// TO-DO: Give error since there's no companyID?
			$companyID = NULL;
		}

		if(!isSet($dspname) OR ($dspname == "" AND !empty($_SESSION['AddCreateBookingInfoArray']['BookedBy']))){
			$dspname = $_SESSION['AddCreateBookingInfoArray']['BookedBy'];
		}

		if(!isSet($bknDscrptn) OR ($bknDscrptn == "" AND !empty($_SESSION["AddCreateBookingInfoArray"]["BookingDescription"]))){
			$bknDscrptn = $_SESSION["AddCreateBookingInfoArray"]["BookingDescription"];
		}

		$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = $companyID;
		$_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'] = $meetingRoomID;
		$_SESSION['AddCreateBookingInfoArray']['StartTime'] = $startDateTime;
		$_SESSION['AddCreateBookingInfoArray']['EndTime'] = $endDateTime;
		$_SESSION['AddCreateBookingInfoArray']['BookedBy'] = $dspname;
		$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = $bknDscrptn;
	} else {
		$meetingRoomID = $_SESSION['AddCreateBookingInfoArray']['TheMeetingRoomID'];
		$companyID = $_SESSION['AddCreateBookingInfoArray']['TheCompanyID'];
		$startDateTime = $_SESSION['AddCreateBookingInfoArray']['StartTime'];
		$endDateTime = $_SESSION['AddCreateBookingInfoArray']['EndTime'];
		$dspname = $_SESSION['AddCreateBookingInfoArray']['BookedBy'];
		$bknDscrptn = $_SESSION['AddCreateBookingInfoArray']['BookingDescription'];
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
	if($row['HitCount'] > 0){

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

	// We're finished validating the inputs and seeing if it's available now.
	// Make the user finish step 1 now and give them the option to go to step 2 (make an order)	if logged in
	if(isSet($_SESSION["loggedIn"]) AND !isSet($_SESSION["DefaultMeetingRoomInfo"]) AND !isSet($_SESSION['AddCreateBookingStepOneCompleted']) AND !isSet($_SESSION['refreshAddCreateBookingConfirmed'])){
		$dateTimeNow = getDatetimeNow();
		$timeDifferenceInDays = convertTwoDateTimesToTimeDifferenceInDays($dateTimeNow, $startDateTime);

		if($timeDifferenceInDays < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
			$_SESSION['AddCreateBookingOrderTooSoon'] = TRUE;
		} else {
			unset($_SESSION['AddCreateBookingOrderTooSoon']);
		}

		$_SESSION['AddCreateBookingStepOneCompleted'] = TRUE;
		$_SESSION['refreshAddCreateBooking'] = TRUE;
		rememberAddCreateBookingInputs();

		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$meetingRoomID = $meetingRoomID;
			$location = '.';
		}
		header('Location: ' . $location);
		exit();
	} elseif(isSet($_SESSION["loggedIn"]) AND !isSet($_SESSION["DefaultMeetingRoomInfo"]) AND isSet($_SESSION['AddCreateBookingStepOneCompleted']) AND !isSet($_SESSION['refreshAddCreateBookingConfirmed']) AND isSet($_POST['LastAlternativeID']) AND $_POST['LastAlternativeID'] != ""){
		// There has been items added to the order
		$lastID = $_POST['LastAlternativeID']+1;
		for($i = 0; $i < $lastID; $i++){
			$postExtraIDName = "extraIDAccepted" . $i;
			$postAmountName = "AmountSelected-" . $i;
			if(isSet($_POST[$postExtraIDName]) AND $_POST[$postExtraIDName] > 0){
				if(!isSet($addedExtra)){
					$addedExtra = array();
				}

				$invalid = FALSE;
				// input validation (check if the amounts submitted are valid)
				$amountSubmitted = $_POST[$postAmountName];

				if(validateIntegerNumber($amountSubmitted) === FALSE){
					$invalid = TRUE;
				}

				if(isNumberInvalidOrderAmount($amountSubmitted) === TRUE){
					$invalid = TRUE;
				}

				if($invalid AND !$invalidInput){
					$invalidInput = TRUE;
					$_SESSION['AddCreateBookingError'] = "At least one amount you submitted was not valid.";
				}

				$addedExtra[] = array(
										"ExtraID" => $_POST[$postExtraIDName],
										"ExtraAmount" => $amountSubmitted,
										"Invalid" => $invalid
									);
			}
		}

		if(isSet($addedExtra) AND sizeOf($addedExtra) > 0){
			// Validate the order notes
			$userNotes = $_POST["UserNotes"];
			$trimmedUserNotes = trimExcessWhitespaceButLeaveLinefeed($userNotes);

			if(validateString($trimmedUserNotes) === FALSE AND !$invalidInput){
				$invalidInput = TRUE;
				$_SESSION['AddCreateBookingError'] = "The Order Notes you submitted has illegal characters in it.";
			}

			$invalidOrderUserNotes = isLengthInvalidOrderUserNotes($trimmedUserNotes);
			if($invalidOrderUserNotes AND !$invalidInput){
				$_SESSION['AddCreateBookingError'] = "The Order Notes submitted is too long.";
				$invalidInput = TRUE;
			}

			$_SESSION['AddCreateBookingOrderUserNotes'] = $trimmedUserNotes;
			$_SESSION['AddCreateBookingOrderAddedExtra'] = $addedExtra;

			if($invalidInput){ // This resets the javascript that has been done, unless we use the addedextra
				$_SESSION['refreshAddCreateBooking'] = TRUE;

				header('Location: ' . $location);
				exit();
			}
		}
	}

	// We know it's available. Let's check if this booking makes the company go over credits.
	// If over credits, ask for a confirmation before creating the booking
	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateOnlyEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', 'Y-m-d');
	$timeBookedInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $endDateTime);

	// Get meeting room name
	$MeetingRoomName = 'N/A';
	foreach ($_SESSION['AddCreateBookingMeetingRoomsArray'] AS $room){
		if($room['meetingRoomID'] == $meetingRoomID){
			$MeetingRoomName = $room['meetingRoomName'];
			break;
		}
	}

	// Get company info
	$companyName = 'N/A';
	if(isSet($companyID)){
		foreach($_SESSION['AddCreateBookingCompanyArray'] AS $company){
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
					AND 		DATE(b.`endDateTime`) < :newEndPeriod
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
	if($bookingWentOverCredits AND !isSet($_SESSION['refreshAddCreateBookingConfirmed'])){
		var_dump($_SESSION); // TO-DO: Remove before uploading
		include_once 'confirmbooking.html.php';
		exit();
	}

	unset($_SESSION['AddCreateBookingStartImmediately']);

	if(!isSet($dspname) OR ($dspname == "" AND !empty($_SESSION['AddCreateBookingInfoArray']['BookedBy']))){
		$dspname = $_SESSION['AddCreateBookingInfoArray']['BookedBy'];
	}

	if(!isSet($bknDscrptn) OR ($bknDscrptn == "" AND !empty($_SESSION["AddCreateBookingInfoArray"]["BookingDescription"]))){
		$bknDscrptn = $_SESSION["AddCreateBookingInfoArray"]["BookingDescription"];
	}

	if(isSet($_SESSION['AddCreateBookingOrderAddedExtra'])){
		$orderAdded = TRUE;
	} else {
		$orderAdded = FALSE;
	}

	// Add the booking to the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		// Generate cancellation code
		$cancellationCode = generateCancellationCode();

		$pdo = connect_to_db();

		$pdo->beginTransaction();

		if($orderAdded){
			// Create the order/extraorders first
			if(!empty($_SESSION['AddCreateBookingOrderUserNotes'])){
				$userNotes = $_SESSION['AddCreateBookingOrderUserNotes'];
			} else {
				$userNotes = "";
			}

			$addedExtra = $_SESSION['AddCreateBookingOrderAddedExtra'];

			$sql = "INSERT INTO	`orders`
					SET			`orderUserNotes` = :orderUserNotes";
			$s = $pdo->prepare($sql);
			$s->bindValue(':orderUserNotes', $userNotes);
			$s->execute();

			$theOrderID = $pdo->lastInsertId();

			foreach($addedExtra AS $extra){
				$extraID = $extra["ExtraID"];
				$extraAmount = $extra["ExtraAmount"];

				$sql = "INSERT INTO	`extraorders`
						SET			`extraID` = :extraID,
									`orderID` = :orderID,
									`amount` = :amount";
				$s = $pdo->prepare($sql);
				$s->bindValue(':extraID', $extraID);
				$s->bindValue(':orderID', $theOrderID);
				$s->bindValue(':amount', $extraAmount);
				$s->execute();
			}

			$sql = 'INSERT INTO `booking` 
					SET			`meetingRoomID` = :meetingRoomID,
								`userID` = :userID,
								`companyID` = :companyID,
								`displayName` = :displayName,
								`startDateTime` = :startDateTime,
								`endDateTime` = :endDateTime,
								`description` = :description,
								`cancellationCode` = :cancellationCode,
								`orderID` = :orderID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':meetingRoomID', $meetingRoomID);
			$s->bindValue(':userID', $_SESSION["AddCreateBookingInfoArray"]["TheUserID"]);
			$s->bindValue(':companyID', $companyID);
			$s->bindValue(':orderID', $theOrderID);
			$s->bindValue(':displayName', $dspname);
			$s->bindValue(':startDateTime', $startDateTime);
			$s->bindValue(':endDateTime', $endDateTime);
			$s->bindValue(':description', $bknDscrptn);
			$s->bindValue(':cancellationCode', $cancellationCode);
			$s->execute();
		} else {
			// no order added
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
		}
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error adding submitted order and booking to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['normalBookingFeedback'] = "Successfully created the booking.";

	// Add a log event that a booking (and order) has been created
	try
	{
		unset($_SESSION['AddCreateBookingMeetingRoomsArray']);

		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$displayValidatedStartDate . ' to ' . $displayValidatedEndDate;

		// Get user information
		$userinfo = 'N/A';
		$info = $_SESSION['AddCreateBookingInfoArray']; 
		if(isSet($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}

		$nameOfUserWhoBooked = "N/A";
		if(isSet($_SESSION['LoggedInUserName'])){
			$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
		}
		if(isSet($info["UserLastname"])){
			$nameOfUserWhoBooked = $info["UserLastname"] . ', ' . $info["UserFirstname"];
		}

		// Save a description with information about the booking that was created
		$logEventDescription = 	"A booking was created with these details:" .
								"\nMeeting Room: " . $MeetingRoomName . 
								"\nStart Time: " . $displayValidatedStartDate .
								"\nEnd Time: " . $displayValidatedEndDate .
								"\nBooked for User: " . $userinfo . 
								"\nBooked for Company: " . $companyName . 
								"\nIt was created by: " . $nameOfUserWhoBooked;
		if($addExtraLogEventDescription){
			$logEventDescription .= "\nThis booking, if completed, will put the company at $timeOverCredits over the Credits given that period.";
		}

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

		if($orderAdded){
			// Save a description with information about the booking that was created
			$logEventDescription = 	"An order was created for the meeting:" .
									"\nMeeting Room: " . $MeetingRoomName . 
									"\nStart Time: " . $displayValidatedStartDate .
									"\nEnd Time: " . $displayValidatedEndDate .
									"\nBooked for User: " . $userinfo . 
									"\nBooked for Company: " . $companyName . 
									"\nIt was created by: " . $nameOfUserWhoBooked;

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Order Created'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventDescription);
			$s->execute();
		}
	}
	catch(PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error adding log events to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	//Send email with booking information and cancellation code to the user who the booking is for.	
	if($info['sendEmail'] == 1){
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

		if($bookingWentOverCredits){
			// Add time over credits and the price per hour the company subscription has.
			$emailMessage .= "\n\nWarning: If this booking is completed the company you booked for will be $timeOverCredits over the given free booking time.\nThis will result in a cost of $companyHourPriceOverCredits";
		}

		$email = $_SESSION['AddCreateBookingInfoArray']['UserEmail'];

		$mailResult = sendEmail($email, $emailSubject, $emailMessage);

		if(!$mailResult){
			$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

			// Email failed to be prepared. Store it in database to try again later
			try
			{
				$sql = 'INSERT INTO	`email`
						SET			`subject` = :subject,
									`message` = :message,
									`receivers` = :receivers,
									`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)';
				$s = $pdo->prepare($sql);
				$s->bindValue(':subject', $emailSubject);
				$s->bindValue(':message', $emailMessage);
				$s->bindValue(':receivers', $email);
				$s->execute();
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();
				$error = 'Error storing email: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

			$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
		}

		$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove before uploading
	} elseif($info['sendEmail'] == 0){
		$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails.";
	}

	// Send email to alert company owner(s) that a booking was made that is over credits.
		// Check if any owners want to receive an email
	if($bookingWentOverCredits){
		try
		{
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
			$s->bindValue(':UserEmail', $_SESSION['AddCreateBookingInfoArray']['UserEmail']);
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
			"The employee: " . $nameOfUserWhoBooked . "\n" .
			"In your company: " . $companyName . "\n" .
			"Has booked a meeting that will put your company above the treshold of free booking time this period.\n" .
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
				$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user(s).";

				// Email failed to be prepared. Store it in database to try again later
				try
				{
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->execute();
				}
				catch (PDOException $e)
				{
					$pdo->rollBack();
					$error = 'Error storing email: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading			
		} else {
			$_SESSION['normalBookingFeedback'] .= "\n\nNo Company Owners were sent an email about the booking going over booking."; // TO-DO: Remove before uploading.
		}
	}

	try
	{
		$pdo->commit();
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		unset($_SESSION['normalBookingFeedback']);
		$error = 'Error committing booking and order queries: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Booking a new meeting is done. Reset all connected sessions.
	clearAddCreateBookingSessions();

	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

if(isSet($_POST['confirm']) AND $_POST['confirm'] == "Yes, Create The Booking"){
	$_SESSION['refreshAddCreateBookingConfirmed'] = TRUE;
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

if(isSet($_POST['confirm']) AND $_POST['confirm'] == "No, Cancel The Booking"){
	$_SESSION['normalBookingFeedback'] = "You cancelled your new booking.";
	unset($_SESSION['refreshAddCreateBookingConfirmed']);
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

//	User wants to change the company the booking is for (after having already selected it)
if(isSet($_POST['add']) AND $_POST['add'] == "Change Company"){

	// We want to select a company again
	unset($_SESSION['AddCreateBookingSelectedACompany']);

	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddCreateBookingInputs();
	$_SESSION['AddCreateBookingInfoArray']['BookingDescription'] = "";
	$_SESSION['AddCreateBookingInfoArray']['TheCompanyID'] = "";

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
	unset($_SESSION['AddCreateBookingStepOneCompleted']);

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
							u.`lastName`,
							a.`AccessName`
				FROM		`booking` b
				INNER JOIN 	`user` u
				ON 			b.`userID` = u.`userID`
				INNER JOIN	`accesslevel` a
				ON			a.`AccessID` = u.`AccessID`
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
		$bookingCreatorUserAccess = $row['AccessName'];

		if(isSet($row) AND $row['HitCount'] > 0){
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
							c.`dateTimeCreated`,
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
								AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
				$overHourPrice = $row['CreditHourPriceOverCredits'];
				$displayOverHourPrice = convertToCurrency($overHourPrice) . "/h";
				
				$company[] = array(
									'companyID' => $row['companyID'],
									'companyName' => $row['companyName'],
									'dateTimeCreated' => $row['dateTimeCreated'],
									'endDate' => $displayEndDate,
									'creditsRemaining' => $displayCompanyCreditsRemaining,
									'PotentialExtraMonthlyTimeUsed' => $displayPotentialExtraMonthlyTimeUsed,
									'PotentialCreditsRemaining' => $displayPotentialCompanyCreditsRemaining,
									'HourPriceOverCredit' => $displayOverHourPrice
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
				$_SESSION['EditCreateBookingInfoArray']['HourPriceOverCredit'] = $company[0]['HourPriceOverCredit'];
				$_SESSION['EditCreateBookingInfoArray']['BookingDescription'] = "Booked for " . $company[0]['companyName'];				
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
			$_SESSION['EditCreateBookingInfoArray']['HourPriceOverCredit'] = "N/A";
			$_SESSION['EditCreateBookingInfoArray']['BookingDescription'] = "";
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
	
	// Save changes
	$_SESSION['EditCreateBookingInfoArray'] = $row;
	
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

	if(empty($dspname) AND !empty($_SESSION["EditCreateBookingInfoArray"]["BookedBy"])){
		$dspname = $_SESSION["EditCreateBookingInfoArray"]["BookedBy"];
	}
	if(empty($bknDscrptn) AND !empty($_SESSION["EditCreateBookingInfoArray"]["BookingDescription"])){
		$bknDscrptn = $_SESSION["EditCreateBookingInfoArray"]["BookingDescription"];
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
	$_SESSION['EditCreateBookingInfoArray']['BookingDescription'] = "";
	$_SESSION['EditCreateBookingInfoArray']['TheCompanyID'] = "";
	
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
		if(strlen($_GET['cancellationcode']) != 64){
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
		$sql = "SELECT 	COUNT(*)											AS HitCount,
						(
							IF(b.`userID` IS NULL, NULL, (SELECT u.`email` FROM `user` u WHERE u.`userID` = b.`userID`))
						) 													AS Email,
						(
							IF(b.`userID` IS NULL, NULL, (SELECT u.`sendEmail` FROM `user` u WHERE u.`userID` = b.`userID`))
						) 													AS SendEmail,
						b.`bookingID`										AS BookingID,
						b.`meetingRoomID`									AS TheMeetingRoomID, 
						(
							SELECT	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = TheMeetingRoomID 
						)													AS TheMeetingRoomName,
						b.`startDateTime`									AS StartDateTime,
						b.`endDateTime`										AS EndDateTime,
						b.`actualEndDateTime`								AS ActualEndDateTime,
						b.`orderID`											AS OrderID
				FROM	`booking` b
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

	$bookingID = $result['BookingID'];
	$TheMeetingRoomName = $result['TheMeetingRoomName'];
	$startDateTimeString = $result['StartDateTime'];
	$endDateTimeString = $result['EndDateTime'];
	$actualEndDateTimeString = $result['ActualEndDateTime'];
	$startDateTime = correctDatetimeFormat($startDateTimeString);
	$endDateTime = correctDatetimeFormat($endDateTimeString);

	$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$email = $result['Email'];
	$sendEmail = $result['SendEmail'];
	$meetinginfo = $TheMeetingRoomName . ' for the timeslot: ' . $displayValidatedStartDate . 
					' to ' . $displayValidatedEndDate;

	$meetingCancelled = FALSE;

	if($result['OrderID'] != NULL){
		$orderID = $result['OrderID'];
	}

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
			$meetingCancelled = TRUE;
			$endedEarly = TRUE;
		} elseif($timeNow < $startDateTime) {
			// The booking hasn't started yet, so we're actually cancelling the meeting
			$sql = "UPDATE 	`booking`
					SET		`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL
					WHERE 	`bookingID` = :bookingID";
			$bookingFeedback = 	"The booking for " . $TheMeetingRoomName . ".\nStarting at: " . $displayValidatedStartDate . 
								" and ending at: " . $displayValidatedEndDate . " has been cancelled by using the cancellation link.";
			$logEventDescription = $bookingFeedback;
			$meetingCancelled = TRUE;
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
	
	if(isSet($logEventDescription) AND isSet($bookingID)){
		// Update the booked meeting
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$pdo->beginTransaction();

			$s = $pdo->prepare($sql);
			$s->bindValue(':bookingID', $bookingID);
			$s->execute();
		}
		catch(PDOException $e)
		{
			$error = 'Error updating booking: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		if(isSet($orderID) AND isSet($endedEarly) AND $endedEarly){
		// Add a log event about the updated booking
			try
			{
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
			catch(PDOException $e)
			{
				$pdo->rollBack();
				$error = 'Error updating meeting order: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}
		}

		// Add a log event about the updated booking
		try
		{
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
	}

	$_SESSION['normalBookingFeedback'] = $bookingFeedback;

	// Send mail to the user the booking was registered too about the meeting being cancelled/ended early.
	if(isSet($meetingCancelled) AND $meetingCancelled){		
		$_SESSION['cancelBookingOriginalValues']['SendEmail'] = $sendEmail;
		$_SESSION['cancelBookingOriginalValues']['UserEmail'] = $email ;
		$_SESSION['cancelBookingOriginalValues']['MeetingInfo'] = $meetinginfo;
		$_SESSION['cancelBookingOriginalValues']['CancelledBy'] = "someone using the cancellation link";
		emailUserOnCancelledBooking();
	}
}

// CANCELLATION CODE SNIPPET // END //

// ORDER CODE SNIPPET // START //

	// ADD ORDER CODE SNIPPET // START //

// If user wants to create an order after the booking has already been made / order has been cancelled before
if	(isSet($_SESSION['loggedIn']) AND 
	((isSet($_POST['order']) AND $_POST['order'] == 'Create')) OR 
	(isSet($_SESSION['refreshCreateOrder']) AND $_SESSION['refreshCreateOrder'])
	){

	if(isSet($_SESSION['refreshCreateOrder']) AND $_SESSION['refreshCreateOrder']){
		unset($_SESSION['refreshCreateOrder']);
	} else {
		$_SESSION['createOrderOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['createOrderOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];

		if(isSet($_POST['sendEmail']) AND !empty($_POST['sendEmail'])){
			$_SESSION['createOrderOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email']) AND !empty($_POST['Email'])){
			$_SESSION['createOrderOriginalValues']['UserEmail'] = $_POST['Email'];
		}
	}

	$bookingID = $_SESSION['createOrderOriginalValues']['BookingID'];
	$bookingStatus = $_SESSION['createOrderOriginalValues']['BookingStatus'];

	// Only do this if the booking is still active (and not today)
	if(!isSet($bookingStatus) OR (isSet($bookingStatus) AND $bookingStatus != 'Active')){
		$_SESSION['normalBookingFeedback'] = "This meeting can not submit an order.";

		clearAddOrderToBookingSessions();

		// Load booked meetings list webpage with updated database
		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = "http://$_SERVER[HTTP_HOST]/booking/";
		}
		header('Location: ' . $location);
		exit();
	}

	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();

	// Check if selected user ID is creator of booking, owner of the company it's booked for or an admin
	$continueOrderCreation = FALSE;
	$createdByOwner = FALSE;
	$createdByAdmin = FALSE;

		// Check if the user is the creator of the booking	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 		COUNT(*)			AS HitCount,
							b.`userID`			AS UserID,
							b.`startDateTime`	AS StartDateTime,
							u.`email`			AS UserEmail,
							u.`firstName`		AS FirstName,
							u.`lastName`		AS LastName,
							u.`sendEmail`		AS SendEmail
				FROM		`booking` b
				INNER JOIN 	`user` u
				ON 			b.`userID` = u.`userID`
				WHERE 		b.`bookingID` = :BookingID
				AND			b.`orderID` IS NOT NULL
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':BookingID', $bookingID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		if(isSet($row) AND $row['HitCount'] > 0){

			$orderStartDateTime = $row['StartDateTime'];
			$dateTimeNow = getDatetimeNow();
			$daysUntilMeetingStarts = convertTwoDateTimesToTimeDifferenceInDays($dateTimeNow, $orderStartDateTime);

			if($daysUntilMeetingStarts < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
				$_SESSION['normalBookingFeedback'] = "The booked meeting is within the time period where you can no longer create an order.";
				clearAddOrderToBookingSessions();

				// Load booked meetings list webpage with updated database
				if(isSet($_GET['meetingroom'])){
					$meetingRoomID = $_GET['meetingroom'];
					$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
				} else {
					$location = "http://$_SERVER[HTTP_HOST]/booking/";
				}
				header('Location: ' . $location);
				exit();
			}

			$bookingCreatorUserID = $row['UserID'];
			$bookingCreatorUserEmail = $row['UserEmail'];
			$bookingCreatorUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
			$_SESSION['createOrderOriginalValues']['SendEmail'] = $row['SendEmail'];
			$_SESSION['createOrderOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;
			if(isSet($bookingCreatorUserID) AND !empty($bookingCreatorUserID) AND $bookingCreatorUserID == $SelectedUserID){
				$continueOrderCreation = TRUE;
			}
		}
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error retrieving original booking information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

		// Check if the user is an owner of the company the booking is booked for
	if(!$continueOrderCreation) {
		try
		{
			$sql = 'SELECT 		COUNT(*)		AS HitCount,
								b.`userID`		AS UserID,
								u.`email`		AS UserEmail,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByUserName = $row['LastName'] . ", " . $row['FirstName'];
				$cancelledByUserEmail = $row['UserEmail'];
				$cancelledByUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
				$continueOrderCreation = TRUE;
				$createdByOwner = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error validating cancelling access: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
	}

		// Check if the user is an admin
		// Only needed if the the user isn't the creator of the booking or an owner
	if(!$continueOrderCreation) {
		try
		{
			$sql = 'SELECT 		COUNT(*) 		AS HitCount,
								u.`userID`		AS UserID,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByAdminName = $row['LastName'] . ", " . $row['FirstName'];
				$continueOrderCreation = TRUE;
				$createdByAdmin = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error validating cancelling access: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
	}

	if($continueOrderCreation === FALSE){
		$pdo = null;
		$_SESSION['normalBookingFeedback'] = "You do not have the access to create an order for this booked meeting.";
		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = "http://$_SERVER[HTTP_HOST]/booking/";
		}
		header('Location: ' . $location);
		exit();
	}

	// Get needed information from database
	if(!isSet($_SESSION['AddCreateOrderForBookingAvailableExtra'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			// Get booking information
			$pdo = connect_to_db();

			// Get all available extras that the user can use to add
			// That are not already in the order
			$sql = 'SELECT 	`extraID`		AS ExtraID,
							`name`			AS ExtraName,
							`description`	AS ExtraDescription,
							`price`			AS ExtraPrice
					FROM 	`extra`
					WHERE	`isAlternative` = 0';
			$result = $pdo->query($sql);
			$availableExtra = $result->fetchAll(PDO::FETCH_ASSOC);
			$_SESSION['AddCreateOrderForBookingAvailableExtra'] = $availableExtra;

			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching available extras: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$availableExtra = $_SESSION['AddCreateOrderForBookingAvailableExtra'];
	}

	$availableExtrasNumber = sizeOf($availableExtra);

	if(isSet($_SESSION['AddCreateOrderForBookingOrderUserNotes'])){
		$userNotes = $_SESSION['AddCreateOrderForBookingOrderUserNotes'];
	} else {
		$userNotes = "";
	}

	var_dump($_SESSION);	// TO-DO: remove before uploading
	include_once 'addorder.html.php';
	exit();
}
	// ADD ORDER CODE SNIPPET // END //

	// SUBMIT ORDER CODE SNIPPET // START //

if(isSet($_POST['add']) AND $_POST['add'] == "Add Order"){

	$bookingID = $_SESSION['createOrderOriginalValues']['BookingID'];

	$invalidInput = FALSE;

	// Get the items added to the order
	$lastID = $_POST['LastAlternativeID']+1;
	for($i = 0; $i < $lastID; $i++){
		$postExtraIDName = "extraIDAccepted" . $i;
		$postAmountName = "AmountSelected-" . $i;
		if(isSet($_POST[$postExtraIDName]) AND $_POST[$postExtraIDName] > 0){
			if(!isSet($addedExtra)){
				$addedExtra = array();
			}

			$invalid = FALSE;
			// input validation (check if the amounts submitted are valid)
			$amountSubmitted = $_POST[$postAmountName];

			if(validateIntegerNumber($amountSubmitted) === FALSE){
				$invalid = TRUE;
			}

			if(isNumberInvalidOrderAmount($amountSubmitted) === TRUE){
				$invalid = TRUE;
			}

			if($invalid AND !$invalidInput){
				$invalidInput = TRUE;
				$_SESSION['AddCreateBookingOrderError'] = "At least one amount you submitted was not valid.";
			}

			$addedExtra[] = array(
									"ExtraID" => $_POST[$postExtraIDName],
									"ExtraAmount" => $amountSubmitted,
									"Invalid" => $invalid
								);
		}
	}

	if(isSet($addedExtra) AND sizeOf($addedExtra) > 0){
		// Validate the order notes
		$userNotes = $_POST["UserNotes"];
		$trimmedUserNotes = trimExcessWhitespaceButLeaveLinefeed($userNotes);

		if(validateString($trimmedUserNotes) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION['AddCreateBookingOrderError'] = "The Order Notes you submitted has illegal characters in it.";
		}

		$invalidOrderUserNotes = isLengthInvalidOrderUserNotes($trimmedUserNotes);
		if($invalidOrderUserNotes AND !$invalidInput){
			$_SESSION['AddCreateBookingOrderError'] = "The Order Notes submitted is too long.";
			$invalidInput = TRUE;
		}

		$_SESSION['AddCreateOrderForBookingOrderUserNotes'] = $trimmedUserNotes;
		$_SESSION['AddCreateOrderForBookingOrderAddedExtra'] = $addedExtra;

		if($invalidInput){ // This resets the javascript that has been done, unless we use the addedextra
			$_SESSION['refreshCreateOrder'] = TRUE;
			
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$location = "http://$_SERVER[HTTP_HOST]/booking/";
			}
			header('Location: ' . $location);
			exit();
		}
	}

	// Get relevant booking information
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		$sql = "SELECT 		c.`name`				AS CompanyName,
							c.`companyID` 			AS CompanyID,
							b.`cancellationCode` 	AS CancellationCode,
							b.`startDateTime`		AS BookingStartTime,
							b.`endDateTime`			AS BookingEndTime,
							b.`orderID`				AS OriginalOrderID,
							u.`lastname`			AS UserLastName,
							u.`firstName`			AS UserFirstName,
							u.`email`				AS UserEmail,
							m.`name`				AS MeetingRoomName
				FROM 		`booking` b
				INNER JOIN 	`company` c
				ON			c.`companyID` = b.`companyID`
				INNER JOIN 	`meetingroom` m
				ON 			m.`meetingRoomID` = b.`meetingRoomID`
				INNER JOIN  `user` u
				ON 			u.`userID` = b.`userID`
				WHERE		b.`bookingID` = :bookingID
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':bookingID', $bookingID);
		$s->execute();

		$row = $s->fetch(PDO::FETCH_ASSOC);

		$companyName = $row['CompanyName'];
		$companyID = $row['CompanyID'];
		$cancellationCode = $row['CancellationCode'];
		$meetingRoomName = $row['MeetingRoomName'];
		$userinfo = $row['UserLastName'] . ", " . $row['UserFirstName'] . " - " . $row['UserEmail'];
		$bookingStartTime = $row['BookingStartTime'];
		$bookingEndTime = $row['BookingEndTime'];
		$displayBookingStartTime = convertDatetimeToFormat($bookingStartTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBookingEndTime = convertDatetimeToFormat($bookingEndTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$originalOrderID = $row['OriginalOrderID'];
	}
	catch (PDOException $e)
	{
		$error = 'Error getting orginal booking information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Add the order to the database
	try
	{
		$pdo->beginTransaction();

		// Create the order/extraorders first
		if(!empty($_SESSION['AddCreateOrderForBookingOrderUserNotes'])){
			$userNotes = $_SESSION['AddCreateOrderForBookingOrderUserNotes'];
		} else {
			$userNotes = "";
		}

		$addedExtra = $_SESSION['AddCreateOrderForBookingOrderAddedExtra'];

		$sql = "INSERT INTO	`orders`
				SET			`orderUserNotes` = :orderUserNotes";
		$s = $pdo->prepare($sql);
		$s->bindValue(':orderUserNotes', $userNotes);
		$s->execute();

		$theOrderID = $pdo->lastInsertId();

		foreach($addedExtra AS $extra){
			$extraID = $extra["ExtraID"];
			$extraAmount = $extra["ExtraAmount"];

			$sql = "INSERT INTO	`extraorders`
					SET			`extraID` = :extraID,
								`orderID` = :orderID,
								`amount` = :amount";
			$s = $pdo->prepare($sql);
			$s->bindValue(':extraID', $extraID);
			$s->bindValue(':orderID', $theOrderID);
			$s->bindValue(':amount', $extraAmount);
			$s->execute();
		}

		if(isSet($originalOrderID) AND $originalOrderID > 0){
			$sql = "UPDATE	`orders`
					SET		`removedFromBookingID` = :removedFromBookingID
					WHERE	`orderID` = :orginalOrderID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':orginalOrderID', $originalOrderID);
			$s->bindValue(':removedFromBookingID', $bookingID);
			$s->execute();
		}

		$sql = 'UPDATE 	`booking` 
				SET		`orderID` = :orderID
				WHERE	`bookingID` = :bookingID';

		$s = $pdo->prepare($sql);
		$s->bindValue(':orderID', $theOrderID);
		$s->bindValue(':bookingID', $bookingID);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error adding submitted order and updating booking in database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Add a log event that an order has been created
	try
	{
		if(isSet($_SESSION['LoggedInUserName'])){
			$nameOfUserWhoBooked = $_SESSION['LoggedInUserName'];
		} else {
			$nameOfUserWhoBooked = "N/A";
		}

		// Save a description with information about the booking that was created
		$logEventDescription = 	"An order was created for the meeting:" .
								"\nMeeting Room: " . $MeetingRoomName . 
								"\nStart Time: " . $displayBookingStartTime .
								"\nEnd Time: " . $displayBookingEndTime .
								"\nBooked for User: " . $userinfo . 
								"\nBooked for Company: " . $companyName . 
								"\nIt was created by: " . $nameOfUserWhoBooked;

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Order Created'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error adding log events to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$bookingCreatorUserEmail = $_SESSION['createOrderOriginalValues']['UserEmail']; 
	$sendEmail = $_SESSION['createOrderOriginalValues']['SendEmail'];

	//Send email with order information to the user who the booking is for.	
	if($sendEmail == 1){
		$emailSubject = "New Order Information!";

		$cancelDayAmount = MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER;

		$emailMessage = 
		"Your order has been successfully booked!\n" . 
		"The order is connected to the meeting with the following details: \n" .
		"\nMeeting Room: " . $MeetingRoomName . 
		"\nStart Time: " . $displayBookingStartTime .
		"\nEnd Time: " . $displayBookingEndTime .
		"\nBooked for User: " . $userinfo . 
		"\nBooked for Company: " . $companyName . 
		"\nOrder added by: " . $nameOfUserWhoBooked .
		"\n\nIf you wish to cancel the order, or alter its contents, you have to do it before it's $cancelDayAmount days left until the meeting starts.\n" .
		"If if it's within $cancelDayAmount days left you have to contact an Admin directly to see what can be done.\n\n" . 
		"If you wish to cancel the booked meeting, including this submitted order, you can easily do so by using the link given below.\n" .
		"Click this link to cancel the booked meeting: " . $_SERVER['HTTP_HOST'] . 
		"/booking/?cancellationcode=" . $cancellationCode;

		$email = $bookingCreatorUserEmail;

		$mailResult = sendEmail($email, $emailSubject, $emailMessage);

		if(!$mailResult){
			$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";

			// Email failed to be prepared. Store it in database to try again later
			try
			{
				$sql = 'INSERT INTO	`email`
						SET			`subject` = :subject,
									`message` = :message,
									`receivers` = :receivers,
									`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)';
				$s = $pdo->prepare($sql);
				$s->bindValue(':subject', $emailSubject);
				$s->bindValue(':message', $emailMessage);
				$s->bindValue(':receivers', $email);
				$s->execute();
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();
				$error = 'Error storing email: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

			$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
		}

		$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove before uploading
	} elseif($sendEmail == 0){
		$_SESSION['normalBookingFeedback'] .= "\nUser did not want to get sent Emails.";
	}

	// Send email to alert company owner(s) that an order was made.
	// Check if any owners want to receive an email
	try
	{
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
		$s->bindValue(':UserEmail', $email);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);

		if(isSet($result) AND !empty($result)){
			foreach($result AS $row){
				// Check if user wants to receive owner emails
				if($row['SendOwnerEmail'] == 1){ 
					$companyOwnerEmails[] = $row['Email'];
				}
			}
		}
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error sending email to company owner(s): ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

		// We found company owners to send email too
	if(isSet($companyOwnerEmails) AND !empty($companyOwnerEmails)){
		$emailSubject = "Order Added To Booked Meeting!";

		$emailMessage = 
		"The employee: " . $nameOfUserWhoBooked . "\n" .
		"In your company: " . $companyName . "\n" .
		"Has submitted an order for a booked meeting with the following details:\n" .
		"\nMeeting Room: " . $MeetingRoomName . 
		"\nStart Time: " . $displayBookingStartTime .
		"\nEnd Time: " . $displayBookingEndTime .
		"\nBooked for User: " . $userinfo . 
		"\n\nIf you wish to cancel the booked meeting, including this submitted order, you can easily do so by using the link given below.\n" .
		"Click this link to cancel the booked meeting: " . $_SERVER['HTTP_HOST'] . 
		"/booking/?cancellationcode=" . $cancellationCode . "\n\n" . 
		"If you do not wish to receive these emails, you can disable them in 'My Account' under 'Company Owner Alert Status'.";

		$email = $companyOwnerEmails;

		$mailResult = sendEmail($email, $emailSubject, $emailMessage);

		$email = implode(", ", $email);

		if(!$mailResult){
			$_SESSION['normalBookingFeedback'] .= "\n\n[WARNING] System failed to send Email to user(s).";

			// Email failed to be prepared. Store it in database to try again later
			try
			{
				$sql = 'INSERT INTO	`email`
						SET			`subject` = :subject,
									`message` = :message,
									`receivers` = :receivers,
									`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY)';
				$s = $pdo->prepare($sql);
				$s->bindValue(':subject', $emailSubject);
				$s->bindValue(':message', $emailMessage);
				$s->bindValue(':receivers', $email);
				$s->execute();
			}
			catch (PDOException $e)
			{
				$pdo->rollBack();
				$error = 'Error storing email: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

			$_SESSION['normalBookingFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
		}

		$_SESSION['normalBookingFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading			
	} else {
		$_SESSION['normalBookingFeedback'] .= "\n\nNo Company Owners were sent an email about the booking going over booking."; // TO-DO: Remove before uploading.
	}

	// TO-DO: Add emails to admin if order is close?

	try
	{
		$commitResult = $pdo->commit();

		if($commitResult){
			$_SESSION['normalBookingFeedback'] = "Successfully created the order." . $_SESSION['normalBookingFeedback'];
		} else {
			$_SESSION['normalBookingFeedback'] = "Order may not have been successfully created." . $_SESSION['normalBookingFeedback'];
		}

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		unset($_SESSION['normalBookingFeedback']);
		$error = 'Error committing order queries: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Booking a new meeting is done. Reset all connected sessions.
	clearAddOrderToBookingSessions();

	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

	// SUBMIT ORDER CODE SNIPPET // END //

	// EDIT ORDER CODE SNIPPET // START //

// If user wants to edit their order (while they still can)
if	(isSet($_SESSION['loggedIn']) AND 
	((isSet($_POST['order']) AND $_POST['order'] == "Edit") OR 
	(isSet($_SESSION['refreshEditBookingOrder']) AND $_SESSION['refreshEditBookingOrder']) OR
	(isSet($_SESSION['resetEditBookingOrder'])))
	){
	
	if(isSet($_SESSION['refreshEditBookingOrder']) AND $_SESSION['refreshEditBookingOrder']){
		unset($_SESSION['refreshEditBookingOrder']);
		
		// Get values we had before refresh
		if(isSet($_SESSION['EditBookingOrderCommunicationToStaff'])){
			$orderCommunicationToStaff = $_SESSION['EditBookingOrderCommunicationToStaff'];
			unset($_SESSION['EditBookingOrderCommunicationToStaff']);
		} else {
			$orderCommunicationToStaff = "";
		}
		if(isSet($_SESSION['EditBookingOrderIsApprovedByUser'])){
			$orderIsApprovedByUser = $_SESSION['EditBookingOrderIsApprovedByUser'];
			unset($_SESSION['EditBookingOrderIsApprovedByUser']);
		} else {
			$orderIsApprovedByUser = 0;
		}
		if(isSet($_SESSION['EditBookingOrderOrderID'])){
			$orderID = $_SESSION['EditBookingOrderOrderID'];
		}
		if(isSet($_SESSION['EditBookingOrderAvailableExtra'])){
			$availableExtra = $_SESSION['EditBookingOrderAvailableExtra'];
		}
		if(isSet($_SESSION['EditBookingOrderOrderMessages'])){
			$orderMessages = $_SESSION['EditBookingOrderOrderMessages'];
		}
		if(isSet($_SESSION['EditBookingOrderExtraOrdered'])){
			$extraOrdered = $_SESSION['EditBookingOrderExtraOrdered'];
		}
		if(isSet($_SESSION['EditBookingOrderAlternativeExtraAdded'])){
			$addedExtra = $_SESSION['EditBookingOrderAlternativeExtraAdded'];
		}
		if(isSet($_SESSION['EditBookingOrderExtraOrderedOnlyNames'])){
			$extraOrderedOnlyNames = $_SESSION['EditBookingOrderExtraOrderedOnlyNames'];
		}
	} else {
		if(isSet($_SESSION['resetEditBookingOrder'])){
			$orderID = $_SESSION['resetEditBookingOrder'];
		} else {
			$orderID = $_POST['OrderID'];
		}

		// Make sure we don't have any remembered values in memory
		clearEditBookingOrderSessions();

		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$sql = 'SELECT 		o.`orderID`						AS TheOrderID,
								o.`orderUserNotes`				AS OrderUserNotes,
								o.`dateTimeCreated`				AS DateTimeCreated,
								o.`dateTimeUpdatedByStaff`		AS DateTimeUpdatedByStaff,
								o.`dateTimeUpdatedByUser`		AS DateTimeUpdatedByUser,
								o.`orderApprovedByUser`			AS OrderApprovedByUser,
								o.`orderApprovedByAdmin`		AS OrderApprovedByAdmin,
								o.`orderApprovedByStaff` 		AS OrderApprovedByStaff,
								o.`orderChangedByUser`			AS OrderChangedByUser,
								o.`orderChangedByStaff`			AS OrderChangedByStaff,
								o.`orderNewMessageFromUser`		AS OrderNewMessageFromUser,
								o.`orderNewMessageFromStaff`	AS OrderNewMessageFromStaff,
								b.`startDateTime`				AS DateTimeStart
					FROM 		`orders` o
					INNER JOIN 	`booking` b
					ON 			b.`orderID` = o.`orderID`
					WHERE		o.`orderID` = :OrderID
					LIMIT 		1';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditBookingOrderOriginalInfo'] = $row;

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}

			$dateTimeCreated = $row['DateTimeCreated'];
			$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$dateTimeStart = $row['DateTimeStart'];
			$displayDateTimeStart = convertDatetimeToFormat($dateTimeStart , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

			if(!empty($row['DateTimeUpdatedByStaff'])){
				$dateTimeUpdatedByStaff = $row['DateTimeUpdatedByStaff'];
				$displayDateTimeUpdatedByStaff = convertDatetimeToFormat($dateTimeUpdatedByStaff , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			} else {
				$displayDateTimeUpdatedByStaff = "";
			}

			if(!empty($row['DateTimeUpdatedByUser'])){
				$dateTimeUpdatedByUser = $row['DateTimeUpdatedByUser'];
				$displayDateTimeUpdatedByUser = convertDatetimeToFormat($dateTimeUpdatedByUser , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			} else {
				$displayDateTimeUpdatedByUser = "";
			}

			$datetimeNow = getDatetimeNow();
			$timeDifferenceInDaysUntilStart = convertTwoDateTimesToTimeDifferenceInDays($datetimeNow, $dateTimeStart);
			$timeDifferenceInDaysUntilEditOrCancelGetsBlocked = $timeDifferenceInDaysUntilStart - MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER;

			$_SESSION['EditBookingOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditBookingOrderOriginalInfo']['DateTimeCreated'] = $displayDateTimeCreated;
			$_SESSION['EditBookingOrderOriginalInfo']['DateTimeUpdatedByStaff'] = $displayDateTimeUpdatedByStaff;
			$_SESSION['EditBookingOrderOriginalInfo']['DateTimeUpdatedByUser'] = $displayDateTimeUpdatedByUser;
			$_SESSION['EditBookingOrderOriginalInfo']['DateTimeStart'] = $displayDateTimeStart;
			$_SESSION['EditBookingOrderOriginalInfo']['DaysLeftToEditOrCancel'] = $timeDifferenceInDaysUntilEditOrCancelGetsBlocked;

			$_SESSION['EditBookingOrderOrderID'] = $orderID;

			$sql = 'SELECT 		ex.`extraID`											AS ExtraID,
								ex.`name`												AS ExtraName,
								eo.`amount`												AS ExtraAmount,
								IFNULL(eo.`alternativePrice`, ex.`price`)				AS ExtraPrice,
								ex.`description`										AS ExtraDescription,
								eo.`purchased`											AS ExtraDateTimePurchased,
								eo.`approvedForPurchase`								AS ExtraDateTimeApprovedForPurchase
					FROM 		`extraorders` eo
					INNER JOIN	`extra` ex
					ON 			ex.`extraID` = eo.`extraID`
					WHERE		eo.`orderID` = :OrderID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$extraOrderedNumber = 0;
			$extrasApproved = 0;
			$totalPrice = 0;

			$result = $s->fetchAll(PDO::FETCH_ASSOC);
			foreach($result AS $extra){
				$extraOrderedNumber++;
				$extraName = $extra['ExtraName'];
				$extraAmount = $extra['ExtraAmount'];
				$extraPrice = $extra['ExtraPrice'];
				$finalPrice = $extraAmount * $extraPrice;
				$totalPrice += $finalPrice;
				//$extraPrice = convertToCurrency($extra['ExtraPrice']);
				$extraDescription = $extra['ExtraDescription'];
				$extraID = $extra['ExtraID'];

				if($extra['ExtraDateTimePurchased'] != NULL){
					$extrasApproved++;
					$booleanPurchased = 1;
					$dateTimePurchased = $extra['ExtraDateTimePurchased'];
					$displayDateTimePurchased = convertDatetimeToFormat($dateTimePurchased , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					if($extra['ExtraPurchasedByUser'] != NULL){
						$displayPurchasedByUser = $extra['ExtraPurchasedByUser'];
					} else {
						$displayPurchasedByUser = "N/A - Deleted User";
					}
				} else {
					$displayDateTimePurchased = "";
					$displayPurchasedByUser = "";
					$booleanPurchased = 0;
				}

				if($extra['ExtraDateTimeApprovedForPurchase'] != NULL){
					$booleanApprovedForPurchase = 1;
					$dateTimeApprovedForPurchase = $extra['ExtraDateTimeApprovedForPurchase'];
				} else {
					$booleanApprovedForPurchase = 0;
					$displayDateTimeApprovedForPurchase = "";
				}

				$extraOrderedOnlyNames[] = $extraName;

				$extraOrdered[] = array(
											'ExtraID' => $extraID,
											'ExtraName' => $extraName,
											'ExtraAmount' => $extraAmount,
											'ExtraPrice' => $extraPrice,
											'ExtraDescription' => $extraDescription,
											'ExtraDateTimePurchased' => $displayDateTimePurchased,
											'ExtraPurchasedByUser' => $displayPurchasedByUser,
											'ExtraDateTimeApprovedForPurchase' => $displayDateTimeApprovedForPurchase,
											'ExtraBooleanApprovedForPurchase' => $booleanApprovedForPurchase,
											'ExtraBooleanPurchased' => $booleanPurchased
										);
			}

			if(!isSet($extraOrdered)){
				$extraOrdered = array();
			}
			if(!isSet($extraOrderedOnlyNames)){
				$extraOrderedOnlyNames = array();
			}

			$_SESSION['EditBookingOrderTotalPrice'] = $totalPrice;
			$_SESSION['EditBookingOrderOriginalInfo']['OrderExtrasOrdered'] = $extraOrderedNumber;
			$_SESSION['EditBookingOrderOriginalInfo']['OrderExtrasApproved'] = $extrasApproved;
			$_SESSION['EditBookingOrderOriginalInfo']['ExtraOrdered'] = $extraOrdered;
			$_SESSION['EditBookingOrderExtraOrdered'] = $extraOrdered;
			$_SESSION['EditBookingOrderExtraOrderedOnlyNames'] = $extraOrderedOnlyNames;

			// Get information about messages sent to/from staff
			$sql = 'SELECT	`messageID`		AS OrderMessageID,
							`message`		AS OrderMessage,
							`sentByStaff`	AS OrderMessageSentByStaff,
							`sentByUser`	AS OrderMessageSentByUser,
							`messageSeen`	AS OrderMessageSeen,
							`dateTimeAdded`	AS OrderMessageDateTimeAdded
					FROM	`ordermessages`
					WHERE	`orderID` = :OrderID';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$result = $s->fetchAll(PDO::FETCH_ASSOC);

			$pdo->beginTransaction();

			$orderMessages = "";
			foreach($result AS $message){

				$messageID = $message['OrderMessageID'];
				$messageOnly = $message['OrderMessage'];
				$sentByStaff = $message['OrderMessageSentByStaff'];
				$sentByUser = $message['OrderMessageSentByUser'];
				if($sentByStaff == 1){
					$messageAddFrom = "(Staff)";
				} elseif($sentByUser == 1){
					$messageAddFrom = "(User)";
				} else {
					$messageAddFrom = "(Unknown)";
				}

				$messageSeen = $message['OrderMessageSeen'];
				if($messageSeen == 0 AND $sentByStaff == 1){
					$messageAddSeen = "NEW MESSAGE ";
				} elseif($messageSeen == 0 AND $sentByUser == 1){
					$messageAddSeen = "NOT READ BY STAFF ";
				} else {
					$messageAddSeen = "";
				}

				$messageDateTimeAdded = $message['OrderMessageDateTimeAdded'];
				$displayMessageDateTimeAdded = convertDatetimeToFormat($messageDateTimeAdded , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

				$finalMessage = $displayMessageDateTimeAdded . " " . $messageAddSeen . $messageAddFrom . ": " . $messageOnly . "\n";

				$orderMessages .= $finalMessage;

				if($sentByUser == 1 AND $messageSeen == 0){
					// Update that the new messages (from user) has been seen.
					$sql = "UPDATE	`ordermessages`
							SET		`messageSeen` = 1
							WHERE	`orderID` = :OrderID
							AND		`messageID` = :MessageID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':MessageID', $messageID);
					$s->execute();
				}
			}

			$_SESSION['EditBookingOrderOrderMessages'] = $orderMessages;

			// Update that there are no new messages from staff
			// Also, we've seen any of the changes if there were any
			$sql = "UPDATE	`orders`
					SET		`orderNewMessageFromStaff` = 0,
							`orderChangedByStaff` = 0
					WHERE	`orderID` = :OrderID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$pdo->commit();

			// Get all available extras if user wants to add an alternative
			// That are not already in the order
			$sql = 'SELECT 	`extraID`		AS ExtraID,
							`name`			AS ExtraName,
							`description`	AS ExtraDescription,
							`price`			AS ExtraPrice
					FROM 	`extra`
					WHERE	`extraID`
					NOT IN 	(
								SELECT 	`extraID`
								FROM 	`extraorders`
								WHERE	`orderID` = :OrderID
							)
					AND		`isAlternative` = 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();
			$availableExtra = $s->fetchAll(PDO::FETCH_ASSOC);
			$_SESSION['EditBookingOrderAvailableExtra'] = $availableExtra;

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$error = 'Error fetching order details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Set original values
	$originalOrderIsApproved = $_SESSION['EditBookingOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderUserNotes = $_SESSION['EditBookingOrderOriginalInfo']['OrderUserNotes'];
	$originalOrderCreated = $_SESSION['EditBookingOrderOriginalInfo']['DateTimeCreated'];
	$originalOrderUpdatedByUser = $_SESSION['EditBookingOrderOriginalInfo']['DateTimeUpdatedByUser'];
	$originalOrderUpdatedByStaff = $_SESSION['EditBookingOrderOriginalInfo']['DateTimeUpdatedByStaff'];
	$originalMeetingStartDate = $_SESSION['EditBookingOrderOriginalInfo']['DateTimeStart'];
	$daysLeftToEditOrCancel = $_SESSION['EditBookingOrderOriginalInfo']['DaysLeftToEditOrCancel'];
	$originalTotalPrice = $_SESSION['EditBookingOrderTotalPrice'];
	$extraOrdered = $_SESSION['EditBookingOrderOriginalInfo']['ExtraOrdered'];

	// Adjust days left message to user
	if($daysLeftToEditOrCancel > 1){
		$displayDaysLeftMessage = "$daysLeftToEditOrCancel Days Left";
	} elseif($daysLeftToEditOrCancel == 1){
		$displayDaysLeftMessage = "1 Day Left";
	} elseif($daysLeftToEditOrCancel == 0){
		$startTime = convertDatetimeToFormat($originalMeetingStartDate , DATETIME_DEFAULT_FORMAT_TO_DISPLAY, TIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayDaysLeftMessage = "Last Day (Ends at $startTime)";
	} elseif($daysLeftToEditOrCancel < 0){
		$displayDaysLeftMessage = "No Longer Eligible";
	}

	// Calculate order status
	$extraOrderedNumber = $_SESSION['EditBookingOrderOriginalInfo']['OrderExtrasOrdered'];
	$extrasApproved = $_SESSION['EditBookingOrderOriginalInfo']['OrderExtrasApproved'];
	$orderIsApprovedByUser = $_SESSION['EditBookingOrderOriginalInfo']['OrderApprovedByUser'];

	if($originalOrderIsApproved == 1 AND $orderIsApprovedByUser == 1){
		$orderStatus = "Order Approved!";
		if($extrasApproved == $extraOrderedNumber){
			$orderStatus .= "\n\nAll Items Approved!";
		} elseif($extrasApproved < $extraOrderedNumber){
			$orderStatus .= "\n\nPending Item Approval.";
		}
	} elseif($originalOrderIsApproved == 1 AND $orderIsApprovedByUser == 0){
		$orderStatus = "Order Not Yet Approved After Change.\n\nPending Your Approval.";
	} elseif($originalOrderIsApproved == 0 AND $orderIsApprovedByUser == 1){
		$orderStatus = "Order Not Yet Approved.\n\nPending Staff Approval.";
	} elseif($originalOrderIsApproved == 0 AND $orderIsApprovedByUser == 0){
		$orderStatus = "Order Not Yet Approved After Change.\n\nPending Staff Approval.\n\nPending Your Approval.";
	}

	$availableExtrasNumber = sizeOf($availableExtra);

	if(!isSet($orderCommunicationToStaff)){
		$orderCommunicationToStaff = "";
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading
	include_once 'editorder.html.php';
	exit();
}

	// EDIT ORDER CODE SNIPPET // END //

	// FINISH EDIT ORDER CODE SNIPPET // START //
// Perform the actual database update of the edited information
if(isSet($_POST['order']) AND $_POST['order'] == 'Submit Changes'){
	// Validate user inputs
	list($invalidInput, $validatedOrderCommunicationToStaff, $validatedIsApprovedByUser) = validateOrderUserInputs();

	// JavaScript alternatives submitted data
	if(isSet($_POST['LastAlternativeID']) AND $_POST['LastAlternativeID'] != ""){
		// There has been an alternative added
		$lastID = $_POST['LastAlternativeID'] + 1;
		for($i = 0; $i < $lastID; $i++){
			$postExtraIDName = "addAlternativeSelected-" . $i;
			$postExtraIDNameWithJavascript = "extraIDAccepted" . $i;
			$postAmountName = "AmountSelected-" . $i;

			// POST name changes depending if the user has used javascript or not to confirm the amount.
			// If not confirmed we take the raw value
			if(isSet($_POST[$postExtraIDName]) AND $_POST[$postExtraIDName] > 0){
				$extraID = $_POST[$postExtraIDName];
			} elseif(isSet($_POST[$postExtraIDNameWithJavascript]) AND $_POST[$postExtraIDNameWithJavascript] > 0){
				$extraID = $_POST[$postExtraIDNameWithJavascript];
			}

			if(isSet($extraID)){
				// These are existing alternatives added
				if(!isSet($addedExtra)){
					$addedExtra = array();
				}

				$addedExtra[] = array(
										"ExtraID" => $extraID,
										"ExtraAmount" => $_POST[$postAmountName]
									);
			}
		}
	}

	// Refresh form on invalid
	if($invalidInput){
		// Refresh.
		if(isSet($addedExtra)){
			$_SESSION['EditBookingOrderAlternativeExtraAdded'] = $addedExtra;
		}

		$_SESSION['EditBookingOrderCommunicationToStaff'] = $validatedOrderCommunicationToStaff;
		$_SESSION['EditBookingOrderIsApprovedByUser'] = $validatedIsApprovedByUser;

		$_SESSION['refreshEditOrder'] = TRUE;
		header('Location: .');
		exit();
	}

	// Check if any extras were removed
	if(isSet($_POST['ItemsRemovedFromOrder']) AND $_POST['ItemsRemovedFromOrder'] > 0){
		foreach($_SESSION['EditBookingOrderExtraOrdered'] AS $extra){
			$extraID = $extra['ExtraID'];
			$postSubmittedAmountName = "extraAmountSelected-" . $extraID;
			if(!isSet($_POST[$postSubmittedAmountName])){
				// If there's no selected amount, then this item is no longer in the order
				$removedExtraID[] = $extraID;
			}
		}
	}

	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isSet($_SESSION['EditBookingOrderOriginalInfo'])){
		$original = $_SESSION['EditBookingOrderOriginalInfo'];
		unset($_SESSION['EditBookingOrderOriginalInfo']);

		$messageAdded = FALSE;
		if($validatedOrderCommunicationToStaff != ""){
			$numberOfChanges++;
			$messageAdded = TRUE;
		}

		if($original['OrderApprovedByUser'] != $validatedIsApprovedByUser){
			$numberOfChanges++;
			// Approved changed.
			$approvedByUser = $validatedIsApprovedByUser;
		} else {
			// Approve didn't change
			$approvedByUser = $original['OrderApprovedByUser'];
		}

		$extraChanged = FALSE;
		if($original['ExtraOrdered'] != $_SESSION['EditBookingOrderExtraOrdered']){
			$numberOfChanges++;
			$extraChanged = TRUE;
		}

		$extraAdded = FALSE;
		if(isSet($addedExtra)){
			$numberOfChanges++;
			$extraAdded = TRUE;
		}

		$extraRemoved = FALSE;
		if(isSet($removedExtraID)){
			$numberOfChanges++;
			$extraRemoved = TRUE;
		}
	}

	$orderID = $_POST['OrderID'];

	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$pdo->beginTransaction();

			if($messageAdded AND ($extraAdded OR $extraRemoved)){
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByUser` = :approvedByUser,
								`dateTimeUpdatedByUser` = CURRENT_TIMESTAMP,
								`orderChangedByUser` = 1,
								`orderApprovedByUser` = :approvedByUser,
								`orderNewMessageFromUser` = 1
						WHERE 	`orderID` = :OrderID';
			} elseif(!$messageAdded AND ($extraAdded OR $extraRemoved)){
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByUser` = :approvedByUser,
								`dateTimeUpdatedByUser` = CURRENT_TIMESTAMP,
								`orderChangedByUser` = 1
						WHERE 	`orderID` = :OrderID';
			} elseif($messageAdded AND !$extraAdded AND !$extraRemoved){
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByUser` = :approvedByUser,
								`dateTimeUpdatedByUser` = CURRENT_TIMESTAMP,
								`orderNewMessageFromUser` = 1
						WHERE 	`orderID` = :OrderID';
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByUser` = :approvedByUser,
								`dateTimeUpdatedByUser` = CURRENT_TIMESTAMP,
						WHERE 	`orderID` = :OrderID';
			}
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->bindValue(':approvedByUser', $approvedByUser);
			$s->execute();

			if($messageAdded){
				$sql = 'INSERT INTO `ordermessages`
						SET			`message` = :message,
									`sentByUser` = 1,
									`dateTimeAdded` = CURRENT_TIMESTAMP,
									`messageFromUserID` = :messageFromUserID,
									`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':message', $validatedOrderCommunicationToStaff);
				$s->bindValue(':messageFromUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			}

			if($extraChanged){
				// Update extraorders table
				foreach($_SESSION['EditBookingOrderExtraOrdered'] AS $key => $extra){
					$extraID = $extra['ExtraID'];
					$extraAmount = $extra['ExtraAmount'];
					$updateAmount = FALSE;

					// update if the values actually changed
					if($original['ExtraOrdered'][$key]['ExtraAmount'] != $extraAmount){
						$updateAmount = TRUE;
					}

					if($updateAmount){
						$sql = "UPDATE	`extraorders`
								SET		`amount` = :amount
								WHERE	`orderID` = :OrderID
								AND		`extraID` = :ExtraID";
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':amount', $extraAmount);
						$s->execute();
					}
				}
			}

			if($extraAdded){
				foreach($addedExtra AS $extra){
					$extraID = $extra['ExtraID'];
					$extraAmount = $extra['ExtraAmount'];

					$sql = "INSERT INTO 	`extraorders`
							SET				`extraID` = :ExtraID,
											`orderID` = :OrderID,
											`amount` = :ExtraAmount";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':ExtraID', $extraID);
					$s->bindValue(':ExtraAmount', $extraAmount);
					$s->execute();
				}
			}

			if($extraRemoved){
				foreach($removedExtraID AS $extraID){
					$sql = "DELETE FROM `extraorders`
							WHERE		`extraID` = :ExtraID
							AND			`orderID` = :OrderID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':ExtraID', $extraID);
					$s->execute();
				}
			}

			$pdo->commit();

			// Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$error = 'Error updating submitted Order: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		$_SESSION['normalBookingFeedback'] = "Successfully updated the Order.";
	} else {
		$_SESSION['normalBookingFeedback'] = "No changes were made to the Order.";
	}

	clearEditBookingOrderSessions();

	// Load Order list webpage
	header('Location: .');
	exit();
}

// If user wants to get original values while editing
if(isSet($_POST['action']) AND $_POST['action'] == 'Reset'){

	clearEditBookingOrderSessions();

	$_SESSION['resetEditBookingOrder'] = $_POST['OrderID'];
	header('Location: .');
	exit();
}

// If the user wants to leave the page and go back to the booking overview again
if(isSet($_POST['action']) AND $_POST['action'] == 'Go Back'){
	$_SESSION['normalBookingFeedback'] = "You left the order without making any changes.";
	$refreshBookings = TRUE;
}

	// FINISH EDIT ORDER CODE SNIPPET // END //

	// CANCEL ORDER CODE SNIPPET // START //

// If user wants to cancel am scheduled order (only accesed by being online)
if	(isSet($_SESSION['loggedIn']) AND 
	((isSet($_POST['order']) AND $_POST['order'] == 'Cancel') OR 
	(isSet($_SESSION['refreshCancelOrder']) AND $_SESSION['refreshCancelOrder']))
	){

	if(isSet($_SESSION['refreshCancelOrder']) AND $_SESSION['refreshCancelOrder']){
		unset($_SESSION['refreshCancelOrder']);
	} else {
		$_SESSION['cancelOrderOriginalValues']['BookingID'] = $_POST['id'];
		$_SESSION['cancelOrderOriginalValues']['BookingStatus'] = $_POST['BookingStatus'];
		$_SESSION['cancelOrderOriginalValues']['MeetingInfo'] = $_POST['MeetingInfo'];

		if(isSet($_POST['sendEmail']) AND !empty($_POST['sendEmail'])){
			$_SESSION['cancelOrderOriginalValues']['SendEmail'] = $_POST['sendEmail'];
		}
		if(isSet($_POST['Email']) AND !empty($_POST['Email'])){
			$_SESSION['cancelOrderOriginalValues']['UserEmail'] = $_POST['Email'];
		}
	}

	$bookingID = $_SESSION['cancelOrderOriginalValues']['BookingID'];
	$bookingStatus = $_SESSION['cancelOrderOriginalValues']['BookingStatus'];
	$bookingMeetingInfo = $_SESSION['cancelOrderOriginalValues']['MeetingInfo'];

	$SelectedUserID = checkIfLocalDeviceOrLoggedIn();

	// Check if selected user ID is creator of booking, owner of the company it's booked for or an admin
	$continueCancel = FALSE;
	$cancelledByOwner = FALSE;
	$cancelledByAdmin = FALSE;

		// Check if the user is the creator of the booking	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 		COUNT(*)			AS HitCount,
							o.`orderID`			AS OrderID,
							b.`userID`			AS UserID,
							b.`startDateTime`	AS StartDateTime,
							u.`email`			AS UserEmail,
							u.`firstName`		AS FirstName,
							u.`lastName`		AS LastName,
							u.`sendEmail`		AS SendEmail
				FROM		`booking` b
				INNER JOIN 	`user` u
				ON 			b.`userID` = u.`userID`
				INNER JOIN 	`orders` o
				ON 			o.`orderID` = b.`orderID`
				WHERE 		b.`bookingID` = :BookingID
				AND			b.`orderID` IS NOT NULL
				AND			o.`dateTimeCancelled` IS NULL
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':BookingID', $bookingID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		$cancelTheCancel = FALSE;

		if(isSet($row) AND $row['HitCount'] > 0){

			$orderStartDateTime = $row['StartDateTime'];
			$dateTimeNow = getDatetimeNow();
			$daysUntilMeetingStarts = convertTwoDateTimesToTimeDifferenceInDays($dateTimeNow, $orderStartDateTime);

			if($daysUntilMeetingStarts < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
				$cancelTheCancel = TRUE;
				$_SESSION['normalBookingFeedback'] = "The booked meeting is within the time period where you can no longer cancel the order.";
			}

			$bookingCreatorUserID = $row['UserID'];
			$bookingCreatorUserEmail = $row['UserEmail'];
			$bookingCreatorUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
			$bookingOrderID = $row['OrderID'];

			$_SESSION['cancelOrderOriginalValues']['SendEmail'] = $row['SendEmail'];
			$_SESSION['cancelOrderOriginalValues']['UserEmail'] = $bookingCreatorUserEmail;

			if(isSet($bookingCreatorUserID) AND !empty($bookingCreatorUserID) AND $bookingCreatorUserID == $SelectedUserID){
				$continueCancel = TRUE;
			}
		} else {
			$cancelTheCancel = TRUE;
			$_SESSION['normalBookingFeedback'] = "This meeting has no order connected to it, or it has already been cancelled.";
		}

		if($cancelTheCancel){
			clearChangeBookingSessions();

			// Load booked meetings list webpage with updated database
			if(isSet($_GET['meetingroom'])){
				$meetingRoomID = $_GET['meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
			} else {
				$location = "http://$_SERVER[HTTP_HOST]/booking/";
			}
			header('Location: ' . $location);
			exit();
		}
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error retrieving original booking and order information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

		// Check if the user is an owner of the company the booking is booked for
	if(!$continueCancel) {
		try
		{
			$sql = 'SELECT 		COUNT(*)		AS HitCount,
								b.`userID`		AS UserID,
								u.`email`		AS UserEmail,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByUserName = $row['LastName'] . ", " . $row['FirstName'];
				$cancelledByUserEmail = $row['UserEmail'];
				$cancelledByUserInfo = $row['LastName'] . ", " . $row['FirstName'] . " - " . $row['UserEmail'];
				$continueCancel = TRUE;
				$cancelledByOwner = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error validating cancelling access: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
	}

		// Check if the user is an admin
		// Only needed if the the user isn't the creator of the booking or an owner
	if(!$continueCancel) {
		try
		{
			$sql = 'SELECT 		COUNT(*) 		AS HitCount,
								u.`userID`		AS UserID,
								u.`firstName`	AS FirstName,
								u.`lastName`	AS LastName
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
				$cancelledByAdminName = $row['LastName'] . ", " . $row['FirstName'];
				$continueCancel = TRUE;
				$cancelledByAdmin = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error validating cancelling access: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
	}

	if($continueCancel === FALSE){
		$pdo = null;
		$_SESSION['normalBookingFeedback'] = "You do not have the access to cancel the order for this booked meeting.";
		if(isSet($_GET['meetingroom'])){
			$meetingRoomID = $_GET['meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
		} else {
			$location = "http://$_SERVER[HTTP_HOST]/booking/";
		}
		header('Location: ' . $location);
		exit();
	}

	// Only cancel the order if booking is currently active
	if(isSet($bookingStatus) AND $bookingStatus == 'Active'){

		// Load new template to make user confirm the cancel and an optional way to add a reason for cancelling the order
		if(!isSet($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'])){

			if(isSet($cancelledByAdmin)){
				$cancelledBy = "Admin";
			} elseif($cancelledByOwner){
				$cancelledBy = "Owner";
			} else {
				$cancelledBy = "Creator";
			}

			var_dump($_SESSION); // TO-DO: Remove before uploading
			include_once 'cancelmessage.html.php';
			exit();
		}

		if(isSet($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling']) AND !empty($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'])){
			$cancelMessage = $_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'];
		} else {
			$cancelMessage = NULL;
		}

		// Update cancellation date for selected order in database
		try
		{
			$pdo->beginTransaction();

			$sql = 'UPDATE 	`orders` 
					SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancelMessage` = :cancelMessage,
							`cancelledByUserID` = :cancelledByUserID
					WHERE 	`orderID` = :orderID
					AND		`dateTimeCancelled` IS NULL';
			$s = $pdo->prepare($sql);
			$s->bindValue(':orderID', $bookingOrderID);
			$s->bindValue(':cancelMessage', $cancelMessage);
			$s->bindValue(':cancelledByUserID', $SelectedUserID);
			$s->execute();
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
			$pdo = null;
			$error = 'Error updating selected booked order to be cancelled: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

			// Add a log event that a order was cancelled
		try
		{
			if($cancelledByAdmin){
				$nameOfUserWhoCancelled = $cancelledByAdminName;
				$_SESSION['cancelOrderOriginalValues']['CancelledBy'] = "an Admin: " . $cancelledByAdminName;
			} elseif($cancelledByOwner) {
				$nameOfUserWhoCancelled = $cancelledByUserName;
				$_SESSION['cancelOrderOriginalValues']['CancelledBy'] = "a Company Owner: " . $cancelledByUserName;
			} else {
				$nameOfUserWhoCancelled = $bookingCreatorUserInfo;
				unset($_SESSION['cancelOrderOriginalValues']);
			}

			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($bookingCreatorUserInfo) AND isSet($bookingMeetingInfo)){
				$logEventDescription = 	"An order for the booking with these details was cancelled:" . 
										"\nBooked for User: " . $bookingCreatorUserInfo . 
										"\nMeeting Information: The booked room " . $bookingMeetingInfo . 
										"\nIt was cancelled by: " . $nameOfUserWhoCancelled;
			} else {
				$logEventDescription = 	"A booking was cancelled by: " . $nameOfUserWhoCancelled;
			}

			if(isSet($cancelledByAdmin) AND $cancelledByAdmin AND isSet($_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'])){
				$logEventDescription .= "\nReason for cancelling: " . $_SESSION['cancelOrderOriginalValues']['ReasonForCancelling'];
			}

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Order Cancelled'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventDescription);
			$s->execute();
		}
		catch(PDOException $e)
		{
			$pdo->rollBack();
			$error = 'Error adding log event to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		if($cancelledByAdmin OR $cancelledByOwner){
			// only send email to inform the user that the order was cancelled, if the user didn't cancel their own order.
			emailUserOnCancelledOrder();
		} else {
			try
			{
				// Inform admin(s) that the order was cancelled by the booking creator
				// TO-DO: Remove staff if not needed/wanted
				$sql = "SELECT 		u.`email`		AS Email
						FROM 		`user` u
						INNER JOIN 	`accesslevel` a
						ON			a.`AccessID` = u.`AccessID`
						WHERE		(
													a.`AccessName` = 'Admin'
										AND			u.`sendAdminEmail` = 1
									)
						OR 			a.`AccessName` = 'Staff'";
				$return = $pdo->query($sql);
				$result = $return->fetchAll(PDO::FETCH_ASSOC);

				if(isSet($result)){
					foreach($result AS $Email){
						$email[] = $Email['Email'];
					}
				}

				$emailSubject = "An order was cancelled!";

				$emailMessage = $logEventDescription;

				// Only try to send out email if there are any admins and staff that have set they want them
				if(isSet($email)){
					$mailResult = sendEmail($email, $emailSubject, $emailMessage);

					if(!$mailResult){
						// Email failed to be prepared. Store it in database to try again later

						$email = implode(", ", $email);

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
					}
				}
			}
			catch(PDOException $e)
			{
				$pdo->rollBack();
				$error = 'Error sending email: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}
		}

		try
		{
			$pdo->commit();
			//Close the connection
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$pdo->rollBack();
			$error = 'Error completing cancellation process: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		$_SESSION['normalBookingFeedback'] = "Successfully cancelled the order.";
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['normalBookingFeedback'] = "Meeting has already ended or is too close to starting. Did not cancel the order.";
	}

	clearChangeBookingSessions();

	// Load booked meetings list webpage with updated database
	if(isSet($_GET['meetingroom'])){
		$meetingRoomID = $_GET['meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/booking/?meetingroom=" . $meetingRoomID;
	} else {
		$location = "http://$_SERVER[HTTP_HOST]/booking/";
	}
	header('Location: ' . $location);
	exit();
}

	// CANCEL ORDER CODE SNIPPET // START //

// ORDER CODE SNIPPET // END //

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
updateAdminBookingCodeGuesses();
updateBookingCodeGuesses();
clearAddCreateBookingSessions();
clearEditCreateBookingSessions();
clearChangeBookingSessions();
unset($_SESSION["cancelOrderOriginalValues"]);
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
							b.`orderID`,
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
							b.`orderID`,
							o.`dateTimeCancelled`							AS OrderDateTimeCancelled,
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
							b.`dateTimeCreated`								AS BookingWasCreatedOn
				FROM 		`booking` b
				LEFT JOIN 	`orders` o
				ON 			o.`orderID` = b.`orderID`
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

foreach($result as $row){
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

	$timeDifferenceInDays = convertTwoDateTimesToTimeDifferenceInDays($datetimeNow, $startDateTime);

	// Check if meeting has an order connected and what the user is allowed to do with it
	if(isSet($row['orderID']) AND $row['orderID'] > 0){
		if(empty($row['OrderDateTimeCancelled'])){
			// Order has not been cancelled yet
			if($timeDifferenceInDays < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
				// Order is outside the timeframe where user can add/alter/cancel the order
				$orderCanBeCreated = FALSE;
				$orderCanBeEdited = FALSE;
				$orderCanBeCancelled = FALSE;
				$orderDetailsOnly = TRUE;
			} else {
				$orderCanBeCreated = FALSE; // Order is already created
				$orderCanBeCancelled = TRUE;
				$orderCanBeEdited = TRUE;
				$orderDetailsOnly = FALSE;
			}
		} else {
			// Order has been cancelled
			if($timeDifferenceInDays < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
				$orderCanBeCreated = FALSE;
			} else {
				$orderCanBeCreated = TRUE; // Can create a new order then
			}
			$orderCanBeEdited = FALSE;
			$orderCanBeCancelled = FALSE;
			$orderDetailsOnly = FALSE;
		}
	} else {
		// Meeting has no order yet
		if($timeDifferenceInDays < MINIMUM_DAYS_UNTIL_MEETING_STARTS_WHERE_YOU_CAN_STILL_PLACE_AN_ORDER){
			$orderCanBeCreated = FALSE;
		} else {
			$orderCanBeCreated = TRUE;
		}
		$orderCanBeEdited = FALSE;
		$orderCanBeCancelled = FALSE;
		$orderDetailsOnly = FALSE;
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
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'WorksForCompany' => $worksForCompany,
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookedUserID' => $row['BookedUserID'],
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo,
										'sendEmail' => $row['sendEmail'],
										'OrderID' => $row['orderID'],
										'OrderCanBeCreated' => $orderCanBeCreated,
										'OrderCanBeEdited' => $orderCanBeEdited,
										'OrderCanBeCancelled' => $orderCanBeCancelled,
										'OrderCanBeDetailsOnly' => $orderDetailsOnly
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
									'sendEmail' => $row['sendEmail'],
									'OrderID' => $row['orderID'],
									'OrderCanBeCreated' => $orderCanBeCreated,
									'OrderCanBeEdited' => $orderCanBeEdited,
									'OrderCanBeCancelled' => $orderCanBeCancelled,
									'OrderCanBeDetailsOnly' => $orderDetailsOnly
								);
	}
}
var_dump($_SESSION); // TO-DO: remove after testing is done
// Load the html template
include_once 'booking.html.php';
?>