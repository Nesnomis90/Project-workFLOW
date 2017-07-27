<?php 
// This is the index file for the BOOKINGS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
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
	if(isSet($_POST['UserID']) AND $_POST['UserID'] != $_SESSION['LoggedInUserID']){
		if(isSet($_POST['sendEmail']) AND $_POST['sendEmail'] == 1){
			$emailSubject = "Your meeting has been cancelled!";

			$emailMessage = 
			"A booked meeting has been cancelled by an Admin!\n" .
			"The meeting was booked for the room " . $_POST['MeetingInfo'];
			
			$email = $_POST['Email'];
			
			$mailResult = sendEmail($email, $emailSubject, $emailMessage);
			
			if(!$mailResult){
				$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
			}
		
			$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing
		} elseif(isSet($_POST['sendEmail']) AND $_POST['sendEmail'] == 0) {
			$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent Email.";
		}
	} else {
		$_SESSION['BookingUserFeedback'] .= "\nDid not send an email because you cancelled your own meeting."; // TO-DO: Remove?
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

// If admin wants to be able to delete bookings it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['bookingsEnableDelete'] = TRUE;
	$refreshBookings = TRUE;
}

// If admin wants to be disable booking deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['bookingsEnableDelete']);
	$refreshBookings = TRUE;
}

// If admin wants to remove a booked meeting from the database
if (isSet($_POST['action']) and $_POST['action'] == 'Delete')
{
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
			$logEventDescription = 'The booking made for ' . $_POST['UserInfo'] . ' for the meeting room ' .
			$_POST['MeetingInfo'] . ' was deleted by: ' . $_SESSION['LoggedInUserName'];
		} else {
			$logEventDescription = 'A booking was deleted by: ' . $_SESSION['LoggedInUserName'];
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Removed'
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
	if(isSet($_POST['BookingStatus']) AND $_POST['BookingStatus'] == 'Active'){
		// Send email to user that meeting has been cancelled
		emailUserOnCancelledBooking();
	}
	
	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to cancel a scheduled booked meeting (instead of deleting)
if (isSet($_POST['action']) and $_POST['action'] == 'Cancel')
{
	// Only cancel if booking is currently active
	if(	isSet($_POST['BookingStatus']) AND  
		($_POST['BookingStatus'] == 'Active' OR $_POST['BookingStatus'] == 'Active Today')){	
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
			$s->bindValue(':id', $_POST['id']);
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

		$_SESSION['BookingUserFeedback'] .= "Successfully cancelled the booking";

			// Add a log event that a booking was cancelled
		try
		{
			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($_POST['UserInfo']) AND isSet($_POST['MeetingInfo'])){
				$logEventDescription = 'The booking made for ' . $_POST['UserInfo'] . ' for the meeting room ' .
				$_POST['MeetingInfo'] . ' was cancelled by: ' . $_SESSION['LoggedInUserName'];
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

		emailUserOnCancelledBooking();
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['BookingUserFeedback'] = "Meeting has already been completed. Did not cancel it.";
	}
	
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

			// Get credits given
			if(!empty($row["CreditSubscriptionMinuteAmount"])){
				$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
			} else {
				$companyMinuteCredits = 0;
			}

				// Calculate Company Credits Remaining
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

			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName'],
								'creditsRemaining' => $displayCompanyCreditsRemaining 
								);
		}
			
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isSet($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['EditBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				unset($_SESSION['EditBookingSelectACompany']);
				unset($_SESSION['EditBookingDisplayCompanySelect']);
				$_SESSION['EditBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
				$_SESSION['EditBookingInfoArray']['CreditsRemaining'] = $company[0]['creditsRemaining'];
				
			}
		} else{
			// User is NOT in a company
			
			unset($_SESSION['EditBookingDisplayCompanySelect']);
			$_SESSION['EditBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditBookingInfoArray']['BookedForCompany'] = "";
			$_SESSION['EditBookingInfoArray']['CreditsRemaining'] = "N/A";
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
				break;
			}
		}			
	}
	
	$_SESSION['EditBookingInfoArray'] = $row;	
	
		// Edited inputs
	$bookingID = $row['TheBookingID'];
	$companyName = $row['BookedForCompany'];
	$creditsRemaining = $row['CreditsRemaining'];
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
			$pdo = connect_to_db();
			/* Old SQL with all users
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`
					FROM 	`user`
					WHERE 	`isActive` > 0";
			*/
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
if(isSet($_POST['edit']) AND $_POST['edit'] == "Finish Edit")
{
	$originalValue = $_SESSION['EditBookingOriginalInfoArray'];
	
	if($originalValue['BookingCompleted'] == 1){
		$bookingCompleted = TRUE;
	} else {
		$bookingCompleted = FALSE;
	}

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
	
	// Check if any values actually changed. If not, we don't need to bother the database
	$numberOfChanges = 0;
	$checkIfTimeslotIsAvailable = FALSE;
	$newMeetingRoom = FALSE;
	$newStartTime = FALSE;
	$newEndTime = FALSE;
	$newUser = FALSE;
	
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

	if($_POST['companyID'] != $originalValue['TheCompanyID']){
		$numberOfChanges++;
	}
	if($dspname != $originalValue['BookedBy']){
		$numberOfChanges++;
	}	
	if($bknDscrptn != $originalValue['BookingDescription']){
		$numberOfChanges++;
	}	
	if($_POST['meetingRoomID'] != $originalValue['TheMeetingRoomID']){
		$numberOfChanges++;
		$newMeetingRoom = TRUE;
	}
	if(isSet($_POST['userID']) AND $_POST['userID'] != $originalValue['TheUserID']){
		$numberOfChanges++;
		$newUser = TRUE;
	}	
	if($validatedAdminNote == ""){
		$validatedAdminNote = NULL;
	}
	if($validatedAdminNote != $originalValue['AdminNote']){
		$numberOfChanges++;
		$newUser = TRUE;
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
					
				$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
				$s->bindValue(':StartTime', $startDateTime);
				$s->bindValue(':EndTime', $endDateTime);
				$s->bindValue(':BookingID', $_POST['bookingID']);
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

	// Set correct companyID
	if(	isSet($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
		$_POST['companyID'] != ''){
		$companyID = $_POST['companyID'];
	} else {
		$companyID = NULL;
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
		
		$s->bindValue(':BookingID', $_POST['bookingID']);
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_POST['userID']);
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
		
	$_SESSION['BookingUserFeedback'] .= "Successfully updated the booking information!";
	
	// Send email to the user (if altered by someone else) that their booking has been changed
		// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email
	if(!$bookingCompleted){
		if($_SESSION['EditBookingInfoArray']['sendEmail'] == 1 OR $originalValue['sendEmail'] == 1){
			
			// date display formatting
			$NewStartDate = convertDatetimeToFormat($startDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$NewEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$OldStartDate = convertDatetimeToFormat($oldStartTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$OldEndDate = convertDatetimeToFormat($oldEndTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			
			// Meeting room name(s)
			$oldMeetingRoomName = $originalValue['BookedRoomName'];		
			if($newMeetingRoom){
				// Get meeting room name
				foreach ($_SESSION['EditBookingMeetingRoomsArray'] AS $room){
					if($room['meetingRoomID'] == $_POST['meetingRoomID']){
						$newMeetingRoomName = $room['meetingRoomName'];
						break;
					}
				}
			} else {
				$newMeetingRoomName = $oldMeetingRoomName;
			}

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
					"End Time: " . $NewEndDate . ".\n\n" .	
					"If you wish to cancel this meeting, or just end it early, you can easily do so by clicking the link given below.\n" .
					"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
					"/booking/?cancellationcode=" . $cancellationCode;
					
					$email = $_SESSION['EditBookingInfoArray']['UserEmail'];
					
					$mailResult = sendEmail($email, $emailSubject, $emailMessage);
					
					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
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
					"Is no longer active";
					
					$email = $originalValue['UserEmail'];
					
					$mailResult = sendEmail($email, $emailSubject, $emailMessage);
					
					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
					}
					
					$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing				
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
					"If you wish to cancel your meeting, or just end it early, you can easily do so by clicking the link given below.\n" .
					"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
					"/booking/?cancellationcode=" . $cancellationCode;

					$email = $originalValue['UserEmail'];

					$mailResult = sendEmail($email, $emailSubject, $emailMessage);
	
					if(!$mailResult){
						$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
					}

					$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing	
				} else {	
					$_SESSION['BookingUserFeedback'] .= "\nUser does not want to be sent an Email.";
				}
			}
		}
	}
	clearEditBookingSessions();

	// Load booking history list webpage with the updated booking information
	header('Location: .');
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
				/* old SQL without company requirement restriction
				$sql = 'SELECT	`bookingdescription`, 
								`displayname`,
								`firstName`,
								`lastName`,
								`email`
						FROM 	`user`
						WHERE 	`userID` = :userID
						LIMIT 	1'; */
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

				// Get credits given
				if(!empty($row["CreditSubscriptionMinuteAmount"])){
					$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
				} else {
					$companyMinuteCredits = 0;
				}

					// Calculate Company Credits Remaining
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

				$company[] = array(
									'companyID' => $row['companyID'],
									'companyName' => $row['companyName'],
									'creditsRemaining' => $displayCompanyCreditsRemaining 
									);
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
				}
				$_SESSION['AddBookingCompanyArray'] = $company;
			} else{
				// User is NOT in a company
				
				$_SESSION['AddBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddBookingDisplayCompanySelect']);
				unset($_SESSION['AddBookingCompanyArray']);
				$_SESSION['AddBookingInfoArray']['TheCompanyID'] = "";
				$_SESSION['AddBookingInfoArray']['BookedForCompany'] = "";
				$_SESSION['AddBookingInfoArray']['CreditsRemaining'] = "N/A";
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
if (isSet($_POST['add']) AND $_POST['add'] == "Add booking")
{
	// Validate user inputs
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
		
		$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
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
	
	// Add the booking to the database
	try
	{	
		if(	isSet($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
			$_POST['companyID'] != ''){
			$companyID = $_POST['companyID'];
		} else {
			$companyID = NULL;
		}
	
		if($validatedAdminNote == ''){
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
		
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_POST['userID']);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $dspname);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':description', $bknDscrptn);
		$s->bindValue(':adminNote', $validatedAdminNote);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();

		unset($_SESSION['lastBookingID']);
		$_SESSION['lastBookingID'] = $pdo->lastInsertId();

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
	
	// Add a log event that a booking has been created
	try
	{
		// Get meeting room name
		$MeetingRoomName = 'N/A';
		foreach ($_SESSION['AddBookingMeetingRoomsArray'] AS $room){
			if($room['meetingRoomID'] == $_POST['meetingRoomID']){
				$MeetingRoomName = $room['meetingRoomName'];
				break;
			}
		}
		unset($_SESSION['AddBookingMeetingRoomsArray']);
		
		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$displayValidatedStartDate . ' to ' . $displayValidatedEndDate;
		
		// Get user information
		$userinfo = 'N/A';
		$info = $_SESSION['AddBookingInfoArray']; 
		if(isSet($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}
		
		// Get company name
		$companyName = 'N/A';
		if(isSet($companyID)){
			foreach($_SESSION['AddBookingCompanyArray'] AS $company){
				if($companyID == $company['companyID']){
					$companyName = $company['companyName'];
					break;
				}
			}
		}		
		
		// Save a description with information about the booking that was created
		$logEventDescription =  "Meeting room: " . $MeetingRoomName . 
								".\nTime Slot: " . $displayValidatedStartDate . " to " . $displayValidatedEndDate .
								".\nFor the user: " . $userinfo . " and company: " . $companyName . 
								".\nBooking was made by: " . $_SESSION['LoggedInUserName'] . ".";
		
		if(isSet($_SESSION['lastBookingID'])){
			$lastBookingID = $_SESSION['lastBookingID'];
			unset($_SESSION['lastBookingID']);				
		}

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
		$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':UserID', $_POST['userID']);
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
	if($_SESSION['AddBookingInfoArray']['sendEmail'] == 1){
		if($_POST['userID'] == $_SESSION['LoggedInUserID']){
			// Admin booked a meeting for him/herself
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
		} else {
			// Admin booked a meeting for someone else
			$emailSubject = "You have been assigned a booked meeting!";

			$emailMessage = 
			"A booked meeting has been assigned to you by an Admin!\n" .
			"The meeting has been set to: \n" .
			"Meeting Room: " . $MeetingRoomName . ".\n" . 
			"Start Time: " . $displayValidatedStartDate . ".\n" .
			"End Time: " . $displayValidatedEndDate . ".\n\n" .
			"If you wish to cancel this meeting, or just end it early, you can easily do so by clicking the link given below.\n" .
			"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
			"/booking/?cancellationcode=" . $cancellationCode;		
		}

		$email = $_SESSION['AddBookingInfoArray']['UserEmail'];
		
		$mailResult = sendEmail($email, $emailSubject, $emailMessage);
		
		if(!$mailResult){
			$_SESSION['BookingUserFeedback'] .= "\n\n[WARNING] System failed to send Email to user.";
		}
		
		$_SESSION['BookingUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email: $email."; // TO-DO: Remove after testing
	} elseif($_SESSION['AddBookingInfoArray']['sendEmail'] == 0){
		$_SESSION['BookingUserFeedback'] .= " \nUser does not want to get sent Emails.";
	}
	
	// Booking a new meeting is done. Reset all connected sessions.
	clearAddBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
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
			/* Old SQL with all users
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`
					FROM 	`user`
					WHERE 	`isActive` > 0";
			*/
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
								IF(b.`userID` IS NULL, NULL,
									(
										SELECT 		GROUP_CONCAT(c.`name` separator ",\n")
										FROM 		`company` c
										INNER JOIN 	`employee` e
										ON 			e.`CompanyID` = c.`CompanyID`
										WHERE  		e.`userID` = b.`userID`
										AND			c.`isActive` = 1
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription,
							b.`adminNote`									AS AdminNote,
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn 
				FROM 		`booking` b
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				DESC';
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
} elseif(isSet($_GET['Meetingroom']) AND $_GET['Meetingroom'] != NULL AND $_GET['Meetingroom'] != "") {
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
										AND			c.`isActive` = 1
										GROUP BY 	e.`userID`
									)
								)
							)												AS WorksForCompany,		 
							b.`description`									AS BookingDescription,
							b.`adminNote`									AS AdminNote,							
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn 
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
				$completedDateTime >= $cancelledDateTime ){
		$status = 'Ended Early';
		// Valid status?
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
				$endDateTime < $cancelledDateTime AND 
				$startDateTime > $cancelledDateTime){
		$status = 'Ended Early';
		// Valid status?
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
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,	
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo
									);
	}	elseif($status == "Completed Today") {
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
									'BookingWasCompletedOn' => $displayCompletedDateTime,
									'BookingWasCancelledOn' => $displayCancelledDateTime,	
									'UserInfo' => $userinfo,
									'MeetingInfo' => $meetinginfo
								);
	}	elseif($status == "Completed"){				
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
									'BookingWasCompletedOn' => $displayCompletedDateTime,
									'BookingWasCancelledOn' => $displayCancelledDateTime,	
									'UserInfo' => $userinfo,
									'MeetingInfo' => $meetinginfo
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