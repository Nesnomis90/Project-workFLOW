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
	unset($_SESSION['EditBookingSelectedACompany']);
	unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);	
	unset($_SESSION['EditBookingDisplayCompanySelect']);
}

// Function to remember the user inputs in Edit Booking
function rememberEditBookingInputs(){
	if(isset($_SESSION['EditBookingInfoArray'])){
		$newValues = $_SESSION['EditBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isset($_POST['userID'])){
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
			// The start time
		$newValues['StartTime'] = trimExcessWhitespace($_POST['startDateTime']);
			// The end time
		$newValues['EndTime'] = trimExcessWhitespace($_POST['endDateTime']);
		
		$_SESSION['EditBookingInfoArray'] = $newValues;			
	}
}

// Function to remember the user inputs in Add Booking
function rememberAddBookingInputs(){
	if(isset($_SESSION['AddBookingInfoArray'])){
		$newValues = $_SESSION['AddBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isset($_POST['userID'])){
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
			// The start time
		$newValues['StartTime'] = trimExcessWhitespace($_POST['startDateTime']);
			// The end time
		$newValues['EndTime'] = trimExcessWhitespace($_POST['endDateTime']);
		
		$_SESSION['AddBookingInfoArray'] = $newValues;			
	}
}

// This is used on cancel and delete.
function emailUserOnCancelledBooking(){
	$emailSubject = "Your meeting has been cancelled!";

	$emailMessage = 
	"A booked meeting has been cancelled by an Admin!\n" .
	"The meeting was booked for the room " . $_POST['MeetingInfo'];
	
	$email = $_POST['Email'];
	
	$mailResult = sendEmail($email, $emailSubject, $emailMessage);
	
	if(!$mailResult){
		$_SESSION['BookingUserFeedback'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['BookingUserFeedback'] .= " This is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing			
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse){
	// Get user inputs
	$invalidInput = FALSE;
	
	if(isset($_POST['startDateTime']) AND !$invalidInput){
		$startDateTimeString = $_POST['startDateTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "A booking cannot be finished without submitting a start time.";
	}
	if(isset($_POST['endDateTime']) AND !$invalidInput){
		$endDateTimeString = $_POST['endDateTime'];
	} else {
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "A booking cannot be finished without submitting an end time.";
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
	
	// Remove excess whitespace and prepare strings for validation
	$validatedStartDateTime = trimExcessWhitespace($startDateTimeString);
	$validatedEndDateTime = trimExcessWhitespace($endDateTimeString);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);	
	
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

	// We want to check if a booking is in the correct minute slice e.g. 15 minute increments.
		// We check both start and end time for online/admin bookings
	$invalidStartTime = isBookingDateTimeMinutesInvalid($startDateTime);
	if($invalidStartTime AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your start time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
		$invalidInput = TRUE;	
	}
	$invalidEndTime = isBookingDateTimeMinutesInvalid($endDateTime);
	if($invalidEndTime AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your end time has to be in a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes slice from hh:00.";
		$invalidInput = TRUE;	
	}
	
	// We want to check if the booking is the correct minimum length
	$invalidBookingLength = isBookingTimeDurationInvalid($startDateTime, $endDateTime);
	if($invalidBookingLength AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your start time and end time needs to have at least a " . MINIMUM_BOOKING_TIME_IN_MINUTES . " minutes difference.";
		$invalidInput = TRUE;		
	}	

	return array($invalidInput, $startDateTime, $endDateTime, $validatedBookingDescription, $validatedDisplayName);
}

// If admin wants to be able to delete bookings it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['bookingsEnableDelete'] = TRUE;
	$refreshBookings = TRUE;
}

// If admin wants to be disable booking deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['bookingsEnableDelete']);
	$refreshBookings = TRUE;
}

// If admin wants to remove a booked meeting from the database
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
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
		exit();
	}
	
	$_SESSION['BookingUserFeedback'] .= "Successfully removed the booking";
	
	// Add a log event that a booking was deleted
	try
	{
		// Save a description with information about the booking that was removed
		$logEventDescription = "N/A";
		if(isset($_POST['UserInfo']) AND isset($_POST['MeetingInfo'])){
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
	if(isset($_POST['BookingStatus']) AND $_POST['BookingStatus'] == 'Active'){
		// Send email to user that meeting has been cancelled
		emailUserOnCancelledBooking();
	}
	
	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to cancel a scheduled booked meeting (instead of deleting)
if (isset($_POST['action']) and $_POST['action'] == 'Cancel')
{
	// Only cancel if booking is currently active
	if(	isset($_POST['BookingStatus']) AND  
		($_POST['BookingStatus'] == 'Active' OR $_POST['BookingStatus'] == 'Active Today')){	
		// Update cancellation date for selected booked meeting in database
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`booking` 
					SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
							`cancellationCode` = NULL				
					WHERE 	`bookingID` = :id';
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
			if(isset($_POST['UserInfo']) AND isset($_POST['MeetingInfo'])){
				$logEventDescription = 'The booking made for ' . $_POST['UserInfo'] . ' for the meeting room ' .
				$_POST['MeetingInfo'] . ' was cancelled by: ' . $_SESSION['LoggedInUserName'];
			} else {
				$logEventDescription = 'A booking was cancelled by: ' . $_SESSION['LoggedInUserName'];
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
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isset($_SESSION['refreshEditBooking']) AND $_SESSION['refreshEditBooking']))
{
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshEditBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshEditBooking']);	
		
		// Set the information back to what it was before the refresh
				// The user search string
		if(isset($_SESSION['EditBookingUserSearch'])){
			$usersearchstring = $_SESSION['EditBookingUserSearch'];
			unset($_SESSION['EditBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isset($_SESSION['EditBookingUsersArray'])){
			$users = $_SESSION['EditBookingUsersArray'];
					
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['EditBookingInfoArray']['TheUserID'];	
		
	} else {
		// Get information from database again on the selected booking
		if(!isset($_SESSION['EditBookingMeetingRoomsArray'])){
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
		
		if(!isset($_SESSION['EditBookingInfoArray'])){
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
			$_SESSION['EditBookingInfoArray'] = $s->fetch(PDO::FETCH_ASSOC);
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
				
				$_SESSION['EditBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['EditBookingSelectedACompany'] = TRUE;
				unset($_SESSION['EditBookingDisplayCompanySelect']);
				$_SESSION['EditBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company
			
			$_SESSION['EditBookingSelectedACompany'] = TRUE;
			unset($_SESSION['EditBookingDisplayCompanySelect']);
			$_SESSION['EditBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditBookingInfoArray']['BookedForCompany'] = "";
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
	if(isset($_SESSION['EditBookingSelectedNewUser'])){
		
		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];
				
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
	if(isset($company)){
		foreach($company AS $cmp){
			if($cmp['companyID'] == $row['TheCompanyID']){
				$row['BookedForCompany'] = $cmp['companyName'];
				break;
			}
		}			
	}
	
	$_SESSION['EditBookingInfoArray'] = $row;	
	
		// Edited inputs
	$bookingID = $row['TheBookingID'];
	$companyName = $row['BookedForCompany'];
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

// Admin wants to change the user the booking is reserved for
// We need to get a list of all active users
if((isset($_POST['edit']) AND $_POST['edit'] == "Change User") OR 
	(isset($_SESSION['refreshEditBookingChangeUser'])) AND $_SESSION['refreshEditBookingChangeUser']){
	
	if(isset($_SESSION['refreshEditBookingChangeUser']) AND $_SESSION['refreshEditBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshEditBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['EditBookingUsersArray']);	
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isset($_POST['edit']) AND $_POST['edit'] == "Change User"){
		rememberEditBookingInputs();
	}

	$usersearchstring = "";
	
	if(isset($_SESSION['EditBookingUserSearch'])){
		$usersearchstring = $_SESSION['EditBookingUserSearch'];
	}	
	
	if(!isset($_SESSION['EditBookingUsersArray'])){
		// Get all active users and their default booking information
		try
		{
			$pdo = connect_to_db();
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`
					FROM 	`user`
					WHERE 	`isActive` > 0";
		
			if ($usersearchstring != ''){
				$sqladd = " AND (`firstname` LIKE :search
							OR `lastname` LIKE :search
							OR `email` LIKE :search)";
				$sql = $sql . $sqladd;
				
				$finalusersearchstring = '%' . $usersearchstring . '%';
				
				$s = $pdo->prepare($sql);
				$s->bindValue(":search", $finalusersearchstring);
				$s->execute();
				$result = $s->fetchAll();
				
			} else {
				$result = $pdo->query($sql);
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
									'bookingDescription' => $row['bookingdescription']
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Increase Start By Minimum"){
	
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Increase End By Minimum"){
	
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Select This User"){
	
	// We haven't set the company if we are changing the user.
	unset($_SESSION['EditBookingSelectedACompany']);

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
if(isset($_POST['edit']) AND $_POST['edit'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['EditBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isset($_POST['edit']) AND $_POST['edit'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['EditBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin is editing a booking and wants to limit the users shown by searching
if(isset($_SESSION['EditBookingChangeUser']) AND isset($_POST['edit']) AND $_POST['edit'] == "Search"){
	
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Display Name"){
	  
	$displayName = $_SESSION['EditBookingDefaultDisplayNameForNewUser'];
	if(isset($_SESSION['EditBookingInfoArray'])){
		
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['EditBookingDefaultBookingDescriptionForNewUser'];
	if(isset($_SESSION['EditBookingInfoArray'])){
		
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
if (isset($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditBookingInfoArray'] = $_SESSION['EditBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isset($_POST['edit']) AND $_POST['edit'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your booking editing.";
}


// If admin wants to update the booking information after editing
if(isset($_POST['edit']) AND $_POST['edit'] == "Finish Edit")
{

	// Validate user inputs
	list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname) = validateUserInputs('EditBookingError');
	
	if($invalidInput){
		
		rememberEditBookingInputs();
		// Refresh.
		if(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
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
	$originalValue = $_SESSION['EditBookingOriginalInfoArray'];
	
	if($startDateTime != $originalValue['StartTime']){
		$numberOfChanges++;
		$newStartTime = TRUE;
	}
	if($endDateTime != $originalValue['EndTime']){
		$numberOfChanges++;
		$newEndTime = TRUE;
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
	if(isset($_POST['userID']) AND $_POST['userID'] != $originalValue['TheUserID']){
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
			$sql =	" 	SELECT 	COUNT(*)	AS HitCount
						FROM 	(
									SELECT 	1
									FROM 	`booking`
									WHERE 	`meetingRoomID` = :MeetingRoomID
									AND		
									(		
											(
												`startDateTime` > :StartTime AND 
												`startDateTime` < :EndTime
											) 
									OR 		(
												`endDateTime` > :StartTime AND 
												`endDateTime` < :EndTime
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
			
			if(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
				$_SESSION['refreshEditBookingChangeUser'] = TRUE;				
			} else {
				$_SESSION['refreshEditBooking'] = TRUE;	
			}
			header('Location: .');
			exit();				
		}
	}
	
	// Set correct companyID
	if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
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
	if($_POST['userID'] != $_SESSION['LoggedInUserID']){
		
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
				$_SESSION['BookingUserFeedback'] .= " [WARNING] System failed to send Email to user.";
			}
			
			$_SESSION['BookingUserFeedback'] .= " This is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing
		
			// Send information to old user that their meeting has been cancelled/transferred
			// TO-DO: Make two emails here. One cancelled for old booking and one created for new booking.
			$emailSubject = "Your meeting has been cancelled by an Admin!";
			
			$emailMessage = 
			"Your booked meeting has been removed by an Admin!\n" .
			"The meeting you booking for: \n" .
			"Meeting Room: " . $oldMeetingRoomName . ".\n" . 
			"Start Time: " . $OldStartDate . ".\n" .
			"End Time: " . $OldEndDate . ".\n\n";
			
			$email = $originalValue['UserEmail'];
		} else {
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
		}
		
		$mailResult = sendEmail($email, $emailSubject, $emailMessage);
		
		if(!$mailResult){
			$_SESSION['BookingUserFeedback'] .= " [WARNING] System failed to send Email to user.";
		}
		
		$_SESSION['BookingUserFeedback'] .= " This is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing			
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
if (	(isset($_POST['action']) AND $_POST['action'] == "Create Booking") OR 
		(isset($_SESSION['refreshAddBooking']) AND $_SESSION['refreshAddBooking']))
{
	
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshAddBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddBooking']);	
		
		// Set the information back to what it was before the refresh
				// The user search string
		if(isset($_SESSION['AddBookingUserSearch'])){
			$usersearchstring = $_SESSION['AddBookingUserSearch'];
			unset($_SESSION['AddBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isset($_SESSION['AddBookingUsersArray'])){
			$users = $_SESSION['AddBookingUsersArray'];
					
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];	
	
	} else {
		// Get information from database on booking information user can choose between
		if(!isset($_SESSION['AddBookingMeetingRoomsArray'])){
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
	
		if(!isset($_SESSION['AddBookingInfoArray'])){
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
				$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
				$s->execute();
				
				// Create an array with the row information we retrieved
				$result = $s->fetch(PDO::FETCH_ASSOC);
					
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
														'BookedBy' => '',
														'BookedForCompany' => '',
														'TheUserID' => '',
														'UserFirstname' => '',
														'UserLastname' => '',
														'UserEmail' => '',
														'UserDefaultDisplayName' => '',
														'UserDefaultBookingDescription' => ''
													);			
			$_SESSION['AddBookingInfoArray']['UserDefaultBookingDescription'] = $description;
			$_SESSION['AddBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
			$_SESSION['AddBookingInfoArray']['UserFirstname'] = $firstname;	
			$_SESSION['AddBookingInfoArray']['UserLastname'] = $lastname;	
			$_SESSION['AddBookingInfoArray']['UserEmail'] = $email;	
			$_SESSION['AddBookingInfoArray']['TheUserID'] = $_SESSION['LoggedInUserID'];
			
			$_SESSION['AddBookingOriginalInfoArray'] = $_SESSION['AddBookingInfoArray'];
		}
		
		// Set the correct information on form call
		$usersearchstring = '';
		$users = Null;
		$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];
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
				
				$_SESSION['AddBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['AddBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddBookingDisplayCompanySelect']);
				$_SESSION['AddBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['AddBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company
			
			$_SESSION['AddBookingSelectedACompany'] = TRUE;
			unset($_SESSION['AddBookingDisplayCompanySelect']);
			$_SESSION['AddBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['AddBookingInfoArray']['BookedForCompany'] = "";
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
	$row = $_SESSION['AddBookingInfoArray'];
	$original = $_SESSION['AddBookingOriginalInfoArray'];
		// Changed user
	if(isset($_SESSION['AddBookingSelectedNewUser'])){
		
		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];

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
	$meetingroom = $_SESSION['AddBookingMeetingRoomsArray'];
	if(isset($row['TheMeetingRoomID'])){
		$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	} else {
		$selectedMeetingRoomID = '';
	}
	
	if(isset($row['StartTime']) AND $row['StartTime'] != ""){
		$startDateTime = $row['StartTime'];
	} else {
		$validBookingStartTime = getNextValidBookingStartTime();
		$startDateTime = convertDatetimeToFormat($validBookingStartTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	}
	
	if(isset($row['EndTime']) AND $row['EndTime'] != ""){
		$endDateTime = $row['EndTime'];
	} else {
		$validBookingEndTime = getNextValidBookingEndTime(substr($validBookingStartTime,0,-3));
		$endDateTime = convertDatetimeToFormat($validBookingEndTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
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

	$_SESSION['AddBookingInfoArray'] = $row; // Remember the company/user info we changed based on user choice
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change form
	include 'addbooking.html.php';
	exit();	
}

// Admin wants to increase the start timer by minimum allowed time (e.g. 15 min)
if(isset($_POST['add']) AND $_POST['add'] == "Increase Start By Minimum"){
	
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
if(isset($_POST['add']) AND $_POST['add'] == "Increase End By Minimum"){
	
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
if (isset($_POST['add']) AND $_POST['add'] == "Add booking")
{
	// Validate user inputs
	list($invalidInput, $startDateTime, $endDateTime, $bknDscrptn, $dspname) = validateUserInputs('AddBookingError');
					
	// handle feedback process on invalid input values
	if($invalidInput){
		
		rememberAddBookingInputs();
		// Refresh.
		if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
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
		$sql =	" 	SELECT 	COUNT(*)	AS HitCount
					FROM 	(
								SELECT 	1
								FROM 	`booking`
								WHERE 	`meetingRoomID` = 26
								AND		
								(		
										(
											`startDateTime` > '2017-06-14 17:00:00' AND 
											`startDateTime` < '2017-06-15 18:39:00'
										) 
								OR 		(
											`endDateTime` > '2017-06-14 17:00:00' AND 
											`endDateTime` < '2017-06-15 18:39:00'
										)
								OR 		(
											'2017-06-15 18:39:00' > `startDateTime` AND 
											'2017-06-15 18:39:00' < `endDateTime`
										)
								OR 		(
											'2017-06-14 17:00:00' > `startDateTime` AND 
											'2017-06-14 17:00:00' < `endDateTime`
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
		if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
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
		if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
			$_POST['companyID'] != ''){
			$companyID = $_POST['companyID'];
		} else {
			$companyID = NULL;
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
							`cancellationCode` = :cancellationCode';

		$s = $pdo->prepare($sql);
		
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_POST['userID']);
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
		if(isset($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}
		
		// Save a description with information about the booking that was created
		$logEventDescription = 'A booking was created for the meeting room: ' . $meetinginfo . 
		', for the user: ' . $userinfo . '. Booking was made by: ' . $_SESSION['LoggedInUserName'];
		
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
		$_SESSION['BookingUserFeedback'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['BookingUserFeedback'] .= "this is the email msg we're sending out: $emailMessage. Sent to email: $email."; // TO-DO: Remove after testing	
	
	// Booking a new meeting is done. Reset all connected sessions.
	clearAddBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

// Admin wants to change the user the booking is for
// We need to get a list of all active users
if((isset($_POST['add']) AND $_POST['add'] == "Change User") OR 
	(isset($_SESSION['refreshAddBookingChangeUser'])) AND $_SESSION['refreshAddBookingChangeUser']){
	
	if(isset($_SESSION['refreshAddBookingChangeUser']) AND $_SESSION['refreshAddBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshAddBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['AddBookingUsersArray']);	
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isset($_POST['add']) AND $_POST['add'] == "Change User"){
		rememberAddBookingInputs();
	}

	$usersearchstring = "";
	
	if(isset($_SESSION['AddBookingUserSearch'])){
		$usersearchstring = $_SESSION['AddBookingUserSearch'];
	}	
	
	if(!isset($_SESSION['AddBookingUsersArray'])){
		// Get all active users and their default booking information
		try
		{
			$pdo = connect_to_db();
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`
					FROM 	`user`
					WHERE 	`isActive` > 0";
		
			if ($usersearchstring != ''){
				$sqladd = " AND (`firstname` LIKE :search
							OR `lastname` LIKE :search
							OR `email` LIKE :search)";
				$sql = $sql . $sqladd;
				
				$finalusersearchstring = '%' . $usersearchstring . '%';
				
				$s = $pdo->prepare($sql);
				$s->bindValue(":search", $finalusersearchstring);
				$s->execute();
				$result = $s->fetchAll();
				
			} else {
				$result = $pdo->query($sql);
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
									'bookingDescription' => $row['bookingdescription']
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
if(isset($_POST['add']) AND $_POST['add'] == "Select This User"){
	
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
if(isset($_POST['add']) AND $_POST['add'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['AddBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddBookingInputs();
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isset($_POST['add']) AND $_POST['add'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['AddBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberAddBookingInputs();
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to get the default values for the user's display name
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Display Name"){
	  
	$displayName = $_SESSION['AddBookingDefaultDisplayNameForNewUser'];
	if(isset($_SESSION['AddBookingInfoArray'])){
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
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['AddBookingDefaultBookingDescriptionForNewUser'];
	if(isset($_SESSION['AddBookingInfoArray'])){
		
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
if(isset($_SESSION['AddBookingChangeUser']) AND isset($_POST['add']) AND $_POST['add'] == "Search"){
	
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
if (isset($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddBookingInfoArray'] = $_SESSION['AddBookingOriginalInfoArray'];
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isset($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your new booking.";
}


// ADD CODE SNIPPET END //

// END OF USER INPUT CODE //

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddBookingSessions();
clearEditBookingSessions();


// BOOKING OVERVIEW CODE SNIPPET START //

if(isset($refreshBookings) AND $refreshBookings) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshBookings);
}

// Display booked meetings history list
if(!isset($_GET['Meetingroom'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 		b.`bookingID`,
							b.`companyID`,
							m.`name` 										AS BookedRoomName, 
							b.startDateTime 								AS StartTime, 
							b.endDateTime									AS EndTime, 
							b.displayName 									AS BookedBy,
							c.`name` 										AS BookedForCompany,
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
				WHERE		c.`isActive` = 1
				GROUP BY 	b.bookingID
				ORDER BY 	b.bookingID
				DESC";
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
} else {
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 		b.`bookingID`,
							b.`companyID`,
							m.`name` 										AS BookedRoomName, 
							b.startDateTime 								AS StartTime, 
							b.endDateTime									AS EndTime, 
							b.displayName 									AS BookedBy,
							c.`name`										AS BookedForCompany,
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
				WHERE		c.`isActive` = 1
				AND			b.`meetingRoomID` = :MeetingRoomID 
				GROUP BY 	b.bookingID
				ORDER BY 	b.bookingID
				DESC";	
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
		$s->execute();
		$result = $s->fetchAll();

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
		// This should never occur in practice. Still occurs without manually running update script
	} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND 
				endDateTime < $cancelledDateTime){
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


	// Calculate meeting duration on completion
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($completedDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);
	$timeDifferenceInMinutes = $timeDifference->i;
	$timeDifferenceInHours = $timeDifference->h;
	$timeDifferenceInDays = $timeDifference->d;
	
	if($timeDifferenceInDays > 0){
		$timeDifferenceInHours += $timeDifferenceInDays*24;
	}
	if($timeDifferenceInHours > 0){
		$displayCompletedMeetingDuration = $timeDifferenceInHours . 'h' . $timeDifferenceInMinutes . 'm';
	} else {
		$displayCompletedMeetingDuration = $timeDifferenceInMinutes . 'm';
	}
	
	// TO-DO: Replace code above with this and test if it works $displayCompletedMeetingDuration = convertDateTimesToTimeDifference($startDateTime, $completedDateTime);
					
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
	}	elseif($status == "Completed Today") {
		$bookingsCompletedToday[] = array('id' => $row['bookingID'],
							'BookingStatus' => $status,
							'BookedRoomName' => $roomName,
							'StartTime' => $displayValidatedStartDate,
							'EndTime' => $displayValidatedEndDate,
							'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
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
	}	elseif($status == "Active"){				
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
	}	elseif($status == "Completed"){				
		$bookingsCompleted[] = array('id' => $row['bookingID'],
							'BookingStatus' => $status,
							'BookedRoomName' => $roomName,
							'StartTime' => $displayValidatedStartDate,
							'EndTime' => $displayValidatedEndDate,
							'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
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
	}	elseif($status == "Cancelled"){
		$bookingsCancelled[] = array('id' => $row['bookingID'],
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
	}	else {				
		$bookingsOther[] = array('id' => $row['bookingID'],
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
if(isset($displayRoomNameForTitle) AND ($displayRoomNameForTitle == NULL OR $displayRoomNameForTitle == "N/A - Deleted")){
	unset($displayRoomNameForTitle);
}
// BOOKING OVERVIEW CODE SNIPPET END //
var_dump($_SESSION); // TO-DO: remove after testing is done
// Create the booking information table in HTML
include_once 'bookings.html.php';
?>