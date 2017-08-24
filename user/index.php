<?php 
// This is the index file for the user folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	$gotoPage = ".";
	userIsLoggedIn();
}

function getLocationWeCameFromInUserBooking(){
	$pathWeCameFrom = $_SERVER['PHP_SELF'];
	$pathWithoutPHPFile = substr($pathWeCameFrom, 0, strrpos($pathWeCameFrom,'/'));	
	$location = "Location: " . $pathWithoutPHPFile;

	if(isSet($_GET['totalBooking'])){
		$location .= "?totalBooking";
	} elseif(isSet($_GET['completedBooking'])){
		$location .= "?completedBooking";
	} elseif(isSet($_GET['cancelledBooking'])){
		$location .= "?cancelledBooking";
	}  elseif(isSet($_GET['activeBooking'])){
		$location .= "?activeBooking";
	}

	return $location;
}

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse){
	$invalidInput = FALSE;
	
	// Get user inputs
		//Firstname
	if(isSet($_POST['firstname'])){
		$firstname = $_POST['firstname'];
		$firstname = trim($firstname);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a first name.";
		$invalidInput = TRUE;
	}	
		//Lastname
	if(isSet($_POST['lastname'])){
		$lastname = $_POST['lastname'];
		$lastname = trim($lastname);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a last name.";
		$invalidInput = TRUE;
	}		
		//Email
	if(isSet($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting an email.";
		$invalidInput = TRUE;
	}

		// Display Name (edit only)
	if(isSet($_POST['displayname'])){
		$displayNameString = $_POST['displayname'];
	} else {
		$displayNameString = '';
	}
		// Booking Description (edit only)
	if(isSet($_POST['bookingdescription'])){
		$bookingDescriptionString = $_POST['bookingdescription'];
	} else {
		$bookingDescriptionString = '';
	}	
	
	// Remove excess whitespace and prepare strings for validation
	$validatedFirstname = trimExcessWhitespace($firstname);
	$validatedLastname = trimExcessWhitespace($lastname);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	
	// Do actual input validation
		// First Name
	if(validateNames($validatedFirstname) === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The first name submitted contains illegal characters.";
		$invalidInput = TRUE;		
	}
	if(strlen($validatedFirstname) < 1 AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to submit a first name.";
		$invalidInput = TRUE;	
	}	
		// Last Name
	if(validateNames($validatedLastname) === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The last name submitted contains illegal characters.";
		$invalidInput = TRUE;			
	}
	if(strlen($validatedLastname) < 1 AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to submit a last name.";
		$invalidInput = TRUE;	
	}	
		// Email
	if(strlen($email) < 1 AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to submit an email.";
		$invalidInput = TRUE;
	}	
	if(!validateUserEmail($email) AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The email submitted is not a valid email.";
		$invalidInput = TRUE;
	}	
	if(strlen($email) < 3 AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "You need to submit an actual email.";
		$invalidInput = TRUE;
	}
	
		// Display Name
	if(validateString($validatedDisplayName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted display name has illegal characters in it.";
	}
	$invalidDisplayName = isLengthInvalidDisplayName($validatedDisplayName);
	if($invalidDisplayName AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The displayName submitted is too long.";	
		$invalidInput = TRUE;		
	}		
		// Booking Description
	if(validateString($validatedBookingDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION[$FeedbackSessionToUse] = "Your submitted booking description has illegal characters in it.";
	}	
	$invalidBookingDescription = isLengthInvalidBookingDescription($validatedBookingDescription);
	if($invalidBookingDescription AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	
	// Check if the submitted email has already been used
	if(isSet($_SESSION['EditNormalUserOriginaEmail'])){
		$originalEmail = $_SESSION['EditNormalUserOriginaEmail'];
		// no need to check if our own email exists in the database
		if($email!=$originalEmail){
			if (databaseContainsEmail($email)){
				// The email has been used before. So we can't create a new user with this info.
				$_SESSION[$FeedbackSessionToUse] = "The new email you've set is already connected to an account.";
				$invalidInput = TRUE;	
			}				
		}
	} else {
		if (databaseContainsEmail($email)){
			// The email has been used before. So we can't create a new user with this info.
			$_SESSION[$FeedbackSessionToUse] = "The submitted email is already connected to an account.";
			$invalidInput = TRUE;	
		}			
	}
return array($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName);	
}

// If user wants to submit the registration details and create the account
if(isSet($_POST['register']) AND $_POST['register'] == "Register Account"){
	// Input validation
	list($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName) = validateUserInputs('registerUserWarning');	

		//Password
	if(isSet($_POST['password1']) AND isSet($_POST['password2']) AND !$invalidInput){
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		
		$minimumPasswordLength = MINIMUM_PASSWORD_LENGTH;
		if($password1 == "" AND $password2 == ""){
			$_SESSION["registerUserWarning"] = "You need to fill in your password.";
			$invalidInput = TRUE;			
		} elseif($password1 == "" OR $password2 == ""){
			$_SESSION["registerUserWarning"] = "You need to fill in your password twice to avoid typing a wrong password.";
			$invalidInput = TRUE;
		} elseif($password1 != $password2) {
			$_SESSION["registerUserWarning"] = "The two passwords you submitted did not match. Try again.";
			$invalidInput = TRUE;			
		} elseif($password1 == $password2 AND (strlen(utf8_decode($password1)) < $minimumPasswordLength)){
			$_SESSION["registerUserWarning"] = "The submitted password is not long enough. You are required to make it at least $minimumPasswordLength characters long.";
			$invalidInput = TRUE;			
		}
		
		$password = $password1;
	}
	
	if($invalidInput){
		$_SESSION['registerUserFirstName'] = $validatedFirstname;
		$_SESSION['registerUserLastName'] = $validatedLastname;
		$_SESSION['registerUserEmail'] = $email;
		$_SESSION['refreshRegisterUser'] = TRUE;
		header("Location: .");
		exit();
	}

	// The email has NOT been used before and all inputs are valid, so we can create the new user!
	try
	{
		// Add the user to the database
		
		//Generate activation code
		$activationcode = generateActivationCode();
		
		// Hash the user generated password
		$hashedPassword = hashPassword($password);
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `user`(`firstname`, `lastname`, `password`, `activationcode`, `email`, `accessID`) 
				SELECT		:firstname,
							:lastname,
							:password,
							:activationcode,
							:email,
							`accessID`
				FROM 		`accesslevel`
				WHERE		`AccessName` = "Normal User"';
		$s = $pdo->prepare($sql);
		$s->bindValue(':firstname', $validatedFirstname);
		$s->bindValue(':lastname', $validatedLastname);
		$s->bindValue(':password', $hashedPassword);
		$s->bindValue(':activationcode', $activationcode);
		$s->bindValue(':email', $email);
		$s->execute();

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error registering account: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Add a log event that a user has been created
	try
	{
		// Save a description with information about the user that was added
		
		$description = "N/A";
		$userinfo = $validatedLastname . ', ' . $validatedFirstname . ' - ' . $email;
		if(isSet($_SESSION['LoggedInUserName'])){
			$description = "An account for the user: " . $userinfo . " was registered by the user: " . $_SESSION['LoggedInUserName'];
		} else {
			$description = "An account was registered for the user: " . $userinfo;
		}

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Account Created'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
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
	
	// Send user an email with the activation code
		// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email
	$emailSubject = "Account Activation Link";
	
	$emailMessage = 
	"Your account has been created.\n" .
	"Before you can log in you need to activate your account.\n" .
	"If the account isn't activated within 8 hours, it is removed.\n" .
	"Click this link to activate your account: " . $_SERVER['HTTP_HOST'] . 
	"/user/?activateaccount=" . $activationcode;
	
	$mailResult = sendEmail($email, $emailSubject, $emailMessage);
	
	if(!$mailResult){
		if(isSet($_SESSION['registerUserFeedback'])){
			$_SESSION['registerUserFeedback'] .= "\n[WARNING] System failed to send Email to user.";
		} else {
			$_SESSION['registerUserFeedback'] = "\n[WARNING] System failed to send Email to user.";
		}
		
	}
	
	$_SESSION['registerUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to: $email."; // TO-DO: Remove before uploading	
	
	// End of register account 
	$_SESSION['registerUserFeedback'] .= "\nYour account has been successfully created.\nA confirmation link has been sent to your email.";
	
	$firstName = "";
	$lastName = "";
	$email = "";
	$password1 = "";
	$password2 = "";
	
	var_dump($_SESSION); // TO-DO: Remove after testing
	
	include_once 'register.html.php';
	exit();
}

// Code to execute when a user wants to register an account 
if(isSet($_GET['register']) OR (isSet($_SESSION['refreshRegisterUser']) AND $_SESSION['refreshRegisterUser'])){

	if(isSet($_SESSION['refreshRegisterUser']) AND $_SESSION['refreshRegisterUser']){
		$refreshedRegister = TRUE;
		unset($_SESSION['refreshRegisterUser']);
	}
	
	if(isSet($_SESSION['registerUserWarning']) AND strpos(strtolower($_SESSION['registerUserWarning']), 'email') !== FALSE){
		$invalidEmail = TRUE;
	}
	// Set correct startvalues
	if(isSet($_SESSION['registerUserFirstName'])){
		$firstName = $_SESSION['registerUserFirstName'];
		unset($_SESSION['registerUserFirstName']);
	} else {
		$firstName = "";
	}
	if(isSet($_SESSION['registerUserLastName'])){
		$lastName = $_SESSION['registerUserLastName'];
		unset($_SESSION['registerUserLastName']);
	} else {
		$lastName = "";
	}
	if(isSet($_SESSION['registerUserEmail'])){
		$email = $_SESSION['registerUserEmail'];
		unset($_SESSION['registerUserEmail']);
	} else {
		$email = "";
	}
	$password1 = "";
	$password2 = "";
	
	var_dump($_SESSION); // TO-DO: Remove after testing
	
	include_once 'register.html.php';
	exit();
}

// Code to execute to activate an account from activation link
if(isSet($_GET['activateaccount']) AND !empty($_GET['activateaccount'])){
	
	$activationCode = $_GET['activateaccount'];
		
	// Check if code is correct (64 chars)
	if(strlen($activationCode) != 64){
		$_SESSION['normalUserFeedback'] = "The activation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}
		
	//	Check if the submitted code is in the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "SELECT 	`userID`,
						`email`,
						`firstname`,
						`lastname`,
						`password`
				FROM	`user`
				WHERE 	`activationCode` = :activationCode
				AND		`isActive` = 0
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':activationCode', $activationCode);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error validating activation code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Check if the select even found something
	$result = $s->fetch(PDO::FETCH_ASSOC);
	if(isSet($result)){
		$rowNum = sizeOf($result);
	} else {
		$rowNum = 0;
	}
	if($rowNum == 0){
		// No match.
		$_SESSION['normalUserFeedback'] = "The activation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}
	
	$userID = $result['userID'];
	$email = $result['email'];
	$firstname = $result['firstname'];
	$lastname = $result['lastname'];
	$hashedPassword = $result['password'];
	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "UPDATE 	`user`
				SET		`isActive` = 1,
						`activationCode` = NULL
				WHERE 	`userID` = :userID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $userID);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error activating user: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	
	$_SESSION['normalUserFeedback'] = 	"The account for " . $lastname . ", " . $firstname . " - " . $email . 
										" has been activated!";
									
	// Add a log event that the account got activated
	try
	{
		// Save a description with information about the user that was activated
		
		$logEventDescription = 	"The account for " . $lastname . ", " . $firstname . " - " . $email . 
								" has been activated by using the activation link!";
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent`
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Account Activated'
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
}

// If admin wants to cancel a scheduled booked meeting (instead of deleting)
if (isSet($_POST['booking']) and $_POST['booking'] == 'Cancel')
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
		
		$_SESSION['normalUserBookingFeedback'] .= "Successfully cancelled the booking";
		
			// Add a log event that a booking was cancelled
		try
		{
			// Save a description with information about the booking that was cancelled
			$logEventDescription = "N/A";
			if(isSet($_POST['MeetingInfo'])){
				$logEventDescription = 'The user: ' . $_SESSION['LoggedInUserName'] . ' cancelled their own booking for the meeting room ' . $_POST['MeetingInfo'];
			} else {
				$logEventDescription = 'The user: ' . $_SESSION['LoggedInUserName'] . ' cancelled their own booking.';
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
		unset($_SESSION['normalUserOriginalInfoArray']);
		unset($_SESSION['normalUserOriginalWorksForArray']);
	} else {
		// Booking was not active, so no need to cancel it.
		$_SESSION['normalUserBookingFeedback'] = "Meeting has already been completed. Did not cancel it.";
	}
	
	// Load booked meetings list webpage with updated database
	
	$location = getLocationWeCameFromInUserBooking();
	
	header($location);
	exit();	
}

// if user wants to see the details of their booking history
if(	(isSet($_SESSION['loggedIn']) AND isSet($_SESSION['LoggedInUserID']) AND 
	(isSet($_GET['totalBooking']) OR isSet($_GET['activeBooking']) OR isSet($_GET['completedBooking']) OR isSet($_GET['cancelledBooking'])))
){
	
	$userID = $_SESSION['LoggedInUserID'];

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
							b.`description`									AS BookingDescription,
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
				WHERE		b.`UserID` = :userID
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				DESC';
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $userID);
		$s->execute();
		
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error getting booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	foreach($result as $row)
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
					$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled == $dateOnlyNow){
			$status = 'Ended Early Today';
			// Valid status?
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime 
					AND $dateOnlyCancelled == $dateOnlyNow){
			$status = 'Ended Early Today';
			// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled < $dateOnlyNow){
			$status = 'Ended Early';
			// Valid status?
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime 
					AND $dateOnlyCancelled < $dateOnlyNow){
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
		if(!isSet($roomName) OR $roomName == NULL OR $roomName == ""){
			$roomName = "N/A - Deleted";
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

		if($status == "Active Today" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))) {				
			$bookingsActiveToday[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'MeetingInfo' => $meetinginfo
										);
		}	elseif(($status == "Completed Today" OR $status == "Ended Early Today")AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){
			if($status == "Completed Today"){
				$cancelledByUserName = "";
				$cancelMessage = "";
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
												'BookingWasCreatedOn' => $displayCreatedDateTime,
												'BookingWasCompletedOn' => $displayCompletedDateTime,
												'BookingWasCancelledOn' => $displayCancelledDateTime,
												'MeetingInfo' => $meetinginfo,
												'CancelMessage' => $cancelMessage,
												'CancelledByUserName' => $cancelledByUserName
											);
		}	elseif($status == "Active" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsFuture[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'MeetingInfo' => $meetinginfo
									);
		}	elseif(($status == "Completed" OR $status == "Ended Early") AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){	
			if($status == "Completed"){
				$cancelledByUserName = "";
				$cancelMessage = "";
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
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'MeetingInfo' => $meetinginfo,
											'CancelMessage' => $cancelMessage,
											'CancelledByUserName' => $cancelledByUserName
										);
		}	elseif($status == "Cancelled" AND (isSet($_GET['cancelledBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsCancelled[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'MeetingInfo' => $meetinginfo,
											'CancelMessage' => $cancelMessage,
											'CancelledByUserName' => $cancelledByUserName
										);
		}	elseif(isSet($_GET['totalBooking'])){
			$bookingsOther[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'MeetingInfo' => $meetinginfo
									);
		}
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Create the booking information table in HTML
	include_once 'bookings.html.php';
	exit();
} else {
	unset($_SESSION['normalUserBookingHistory']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Reset"){
	$_SESSION['normalUserEditInfoArray'] = $_SESSION['normalUserOriginalInfoArray'];
}
if(isSet($_POST['action']) AND $_POST['action'] == "Cancel"){
	unset($_SESSION['normalUserOriginalInfoArray']);
	unset($_SESSION['normalUserOriginalWorksForArray']);
	unset($_SESSION['normalUserEditInfoArray']);
	unset($_SESSION['normalUserEditWorksForArray']);
	unset($_SESSION['normalUserEditMode']);
}

if(isSet($_SESSION['loggedIn']) AND isSet($_SESSION['LoggedInUserID'])){
	// Get User information if user is logged in
	$userID = $_SESSION['LoggedInUserID'];
	if(isSet($_SESSION['normalUserOriginalInfoArray']) AND $_SESSION['normalUserOriginalInfoArray']['UserID'] != $userID){
		unset($_SESSION['normalUserOriginalInfoArray']);
		unset($_SESSION['normalUserOriginalWorksForArray']);
	}
	if(!isSet($_SESSION['normalUserOriginalInfoArray']) OR !isSet($_SESSION['normalUserOriginalWorksForArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'SELECT 		u.`userID`				AS UserID,
								u.`email`				AS Email,
								u.`firstName`			AS FirstName,
								u.`lastName`			AS LastName,
								u.`displayName`			AS DisplayName,
								u.`bookingDescription`	AS BookingDescription,
								u.`bookingCode`			AS BookingCode,
								u.`lastCodeUpdate`		AS LastCodeUpdate,
								DATE_ADD(
											u.`lastCodeUpdate`,
											INTERVAL 30 DAY
										) 				AS NextBookingCodeChange,
								u.`create_time`			AS DateTimeCreated,
								u.`lastActivity`		AS LastActive,
								u.`sendEmail`			AS SendEmail,
								u.`sendOwnerEmail`		AS SendOwnerEmail,
								u.`sendAdminEmail`		AS SendAdminEmail,
								u.`password`			AS HashedPassword,
								a.`AccessName`			AS AccessName,
								a.`Description` 		AS AccessDescription,
								(
									SELECT 	COUNT(*)
									FROM	`booking`
									WHERE	`userID` = :userID
								)						AS TotalBookedMeetings,
								(
									SELECT 	COUNT(*)
									FROM	`booking`
									WHERE	`userID` = :userID
									AND 	`actualEndDateTime` IS NULL
									AND 	`dateTimeCancelled` IS NULL
									AND 	`endDateTime` > CURRENT_TIMESTAMP
								)						AS ActiveBookedMeetings,
								(
									SELECT 	COUNT(*)
									FROM	`booking`
									WHERE	`userID` = :userID
									AND 	(
												`actualEndDateTime` IS NOT NULL
											OR
												(
															`actualEndDateTime` IS NULL
													AND 	`dateTimeCancelled` IS NULL
													AND 	`endDateTime` <= CURRENT_TIMESTAMP
												)
											)
								)						AS CompletedBookedMeetings,
								(
									SELECT 	COUNT(*)
									FROM	`booking`
									WHERE	`userID` = :userID
									AND 	`actualEndDateTime` IS NULL
									AND 	`dateTimeCancelled` IS NOT NULL
								)						AS CancelledBookedMeetings
					FROM		`user` u
					INNER JOIN	`accesslevel` a
					ON			a.`AccessID` = u.`AccessID`
					WHERE 		u.`userID` = :userID
					AND			u.`isActive` = 1
					LIMIT 		1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $userID);
			$s->execute();
			
			$result = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['normalUserOriginalInfoArray'] = $result;
			
			// Get employee information
			$sql = "SELECT 		cp.`name`					AS CompanyPosition,
								c.`name`					AS CompanyName,
								c.`CompanyID`				AS CompanyID,
								e.`sendEmailOnceOrAlways`	AS SendEmailOnceOrAlways
					FROM		`employee` e
					INNER JOIN	`company` c
					ON			c.`CompanyID` = e.`CompanyID`
					INNER JOIN	`companyposition` cp
					ON			e.`PositionID` = cp.`PositionID`
					WHERE		e.`userID` = :userID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $userID);
			$s->execute();
			
			$worksForArray = $s->fetchAll(PDO::FETCH_ASSOC);
			$_SESSION['normalUserOriginalWorksForArray'] = $worksForArray;
			
			//Close the connection
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error getting user information: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$result = $_SESSION['normalUserOriginalInfoArray'];
		$worksForArray = $_SESSION['normalUserOriginalWorksForArray'];
	}

	$lastActive = convertDatetimeToFormat($result['LastActive'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS);
	$dateCreated = convertDatetimeToFormat($result['DateTimeCreated'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS);
	$lastCodeUpdateDate = $result['LastCodeUpdate'];

	if($lastCodeUpdateDate !== NULL){
		$dateNow = getDateNow();
		$nextBookingCodeChange = $result['NextBookingCodeChange'];		
		if($dateNow > $nextBookingCodeChange){
			$canSetNewCode = TRUE;
		} else {
			$displayNextBookingCodeChange = convertDatetimeToFormat($nextBookingCodeChange, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		}
	} else {
		$canSetNewCode = TRUE;
	}

	$originalFirstName = $result['FirstName'];
	$originalLastName = $result['LastName'];
	$originalEmail = $result['Email'];
	$originalDisplayName = $result['DisplayName'];
	$originalBookingDescription = $result['BookingDescription'];
	$originalSendEmail = $result['SendEmail'];
	$originalSendAdminEmail = $result['SendAdminEmail'];
	$originalSendOwnerEmail = $result['SendOwnerEmail'];

	$numberOfTotalBookedMeetings = $result['TotalBookedMeetings'];
	$numberOfActiveBookedMeetings = $result['ActiveBookedMeetings'];
	$numberOfCompletedBookedMeetings = $result['CompletedBookedMeetings'];
	$numberOfCancelledBookedMeetings = $result['CancelledBookedMeetings'];
	
	$accessName = $result['AccessName'];
	$accessDescription = $result['AccessDescription'];
	$originalBookingCode = $result['BookingCode'];
	
	$userIsACompanyOwner = FALSE;
	if(!isSet($worksForArray) OR (isSet($worksForArray) AND empty($worksForArray))){
		$worksFor = "You have no company connection.";
	} else {
		// Check if user is owner in any companies.
		foreach($worksForArray AS $row){
			$position = $row['CompanyPosition'];
			$companyName = $row['CompanyName'];
			if($position == "Owner"){
				$userIsACompanyOwner = TRUE;
			}
			$newWorksForArray[] = $position . " in " . $companyName . ".";
		}
	}
	$worksFor = implode("\n",$newWorksForArray);

	if($accessName != "Normal User"){
		$userCanHaveABookingCode = TRUE;

		if($originalBookingCode !== NULL){
			$userHasABookingCode = TRUE;
			$bookingCodeStatus = "You have an active booking code.";
		} else {
			$bookingCodeStatus = "You have not set a booking code.";
		}
	}
} else {
	unset($_SESSION['normalUserOriginalInfoArray']);
	unset($_SESSION['normalUserOriginalWorksForArray']);
	unset($_SESSION['normalUserEditInfoArray']);
	unset($_SESSION['normalUserEditWorksForArray']);
	unset($_SESSION['normalUserEditMode']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Show Code"){
	$showBookingCode = revealBookingCode($originalBookingCode);

	if(isSet($_SESSION['normalUserEditInfoArray']) AND isSet($_SESSION['normalUserEditMode'])){
		$_SESSION['normalUserEditInfoArray']['FirstName'] = trimExcessWhitespace($_POST['firstName']);
		$_SESSION['normalUserEditInfoArray']['LastName'] = trimExcessWhitespace($_POST['lastName']);
		$_SESSION['normalUserEditInfoArray']['DisplayName'] = trimExcessWhitespace($_POST['displayName']);
		$_SESSION['normalUserEditInfoArray']['BookingDescription'] = trimExcessWhitespaceButLeaveLinefeed($_POST['bookingDescription']);
		$_SESSION['normalUserEditInfoArray']['Email'] = $_POST['email'];
		$_SESSION['normalUserEditInfoArray']['SendEmail'] = $_POST['sendEmail'];
		if(isSet($_POST['sendOwnerEmail'])){
			$_SESSION['normalUserEditInfoArray']['SendOwnerEmail'] = $_POST['sendOwnerEmail'];
		}			
		if(isSet($_POST['sendAdminEmail'])){
			$_SESSION['normalUserEditInfoArray']['SendAdminEmail'] = $_POST['sendAdminEmail'];
		}		
	}
}

if(isSet($_POST['action']) AND $_POST['action'] == "Confirm Change"){
	// Do input validation
	$invalidInput = FALSE;
	
	// Get user inputs
		// Firstname
	if(isSet($_POST['firstName'])){
		$firstname = $_POST['firstName'];
		$firstname = trim($firstname);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have a first name.";
		$invalidInput = TRUE;
	}	
		// Lastname
	if(isSet($_POST['lastName'])){
		$lastname = $_POST['lastName'];
		$lastname = trim($lastname);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have a last name.";
		$invalidInput = TRUE;
	}		
		// Email
	if(isSet($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have an email.";
		$invalidInput = TRUE;
	}
		// Display Name
	if(isSet($_POST['displayName'])){
		$displayNameString = $_POST['displayName'];
	} else {
		$displayNameString = '';
	}
		// Booking Description
	if(isSet($_POST['bookingDescription'])){
		$bookingDescriptionString = $_POST['bookingDescription'];
	} else {
		$bookingDescriptionString = '';
	}
		// Booking Code
	if(isSet($_POST['bookingCode']) AND !empty($_POST['bookingCode'])){
		$bookingCode = $_POST['bookingCode'];
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedFirstname = trimExcessWhitespace($firstname);
	$validatedLastname = trimExcessWhitespace($lastname);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	if(isSet($bookingCode)){
		$validatedBookingCode = trimAllWhitespace($bookingCode);
	}
	
	// Do actual input validation
		// First Name
	if(validateNames($validatedFirstname) === FALSE AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "The first name submitted contains illegal characters.";
		$invalidInput = TRUE;		
	}
	if(strlen($validatedFirstname) < 1 AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to submit a first name.";
		$invalidInput = TRUE;	
	}	
		// Last Name
	if(validateNames($validatedLastname) === FALSE AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "The last name submitted contains illegal characters.";
		$invalidInput = TRUE;			
	}
	if(strlen($validatedLastname) < 1 AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to submit a last name.";
		$invalidInput = TRUE;	
	}	
		// Email
	if(strlen($email) < 1 AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to submit an email.";
		$invalidInput = TRUE;
	}	
	if(!validateUserEmail($email) AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "The email submitted is not a valid email.";
		$invalidInput = TRUE;
	}	
	if(strlen($email) < 3 AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to submit an actual email.";
		$invalidInput = TRUE;
	}
	
		// Display Name
	if(validateString($validatedDisplayName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['normalUserFeedback'] = "Your submitted display name has illegal characters in it.";
	}
	$invalidDisplayName = isLengthInvalidDisplayName($validatedDisplayName);
	if($invalidDisplayName AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "The displayName submitted is too long.";	
		$invalidInput = TRUE;		
	}		
		// Booking Description
	if(validateString($validatedBookingDescription) === FALSE AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "Your submitted booking description has illegal characters in it.";
		$invalidInput = TRUE;
	}	
	$invalidBookingDescription = isLengthInvalidBookingDescription($validatedBookingDescription);
	if($invalidBookingDescription AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	if(isSet($validatedBookingCode)){
			// Booking Code
		if(validateIntegerNumber($validatedBookingCode) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION['normalUserFeedback'] = "Your submitted booking code has illegal characters in it.";		
		}
			// Check if booking code is a legit format (correct amount of digits)
		if(isNumberInvalidBookingCode($validatedBookingCode) === TRUE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION['normalUserFeedback'] = "The booking code you selected is not valid.";		
		}
		
		// Check if booking code submitted already exists
		if(databaseContainsBookingCode($validatedBookingCode) === TRUE AND !$invalidInput){
			$_SESSION['normalUserFeedback'] = "The booking code you selected is not valid.";	
			$invalidInput = TRUE;
		}
	}
	
	// Check if the submitted email has already been used
	$originalEmail = $_SESSION['normalUserOriginalInfoArray']['Email'];
	// no need to check if our own email exists in the database
	if($email != $originalEmail AND !$invalidInput){
		if(databaseContainsEmail($email)){
			// The email has been used before. So we can't create a new user with this info.
			$_SESSION['normalUserFeedback'] = "The new email you've set is already connected to an account.";
			$invalidInput = TRUE;
		}
	}

	$changePassword = FALSE;

	// Check if user is trying to set a new password
	// And if so, check if both fields are filled in and match each other
	if(isSet($_POST['password1'])){
		$password1 = $_POST['password1'];
	}
	if(isSet($_POST['password2'])){
		$password2 = $_POST['password2'];
	}
	$minimumPasswordLength = MINIMUM_PASSWORD_LENGTH;
	if(($password1 != '' OR $password2 != '') AND !$invalidInput){

		if($password1 == $password2){
			// Both passwords match, hopefully that means it's the correct password the user wanted to submit

				if(strlen(utf8_decode($password1)) < $minimumPasswordLength){
					$_SESSION['normalUserFeedback'] = "The submitted password is not long enough. You are required to make it at least $minimumPasswordLength characters long.";
					$invalidInput = TRUE;
				} else {
					// Both passwords were the same. They were not empty and they were longer than the minimum requirement
					$changePassword = TRUE;
				}
		} else {
			$_SESSION['normalUserFeedback'] = "Your new Password and Repeat Password did not match.";
			$invalidInput = TRUE;
		}
	} else {
		// Password was empty. Not a big deal since it's not required
		// Just means we won't change it!
	}	

	if(isSet($_SESSION['normalUserEditInfoArray'])){
		$_SESSION['normalUserEditInfoArray']['FirstName'] = $validatedFirstname;
		$_SESSION['normalUserEditInfoArray']['LastName'] = $validatedLastname;
		$_SESSION['normalUserEditInfoArray']['DisplayName'] = $validatedDisplayName;
		$_SESSION['normalUserEditInfoArray']['BookingDescription'] = $validatedBookingDescription;
		$_SESSION['normalUserEditInfoArray']['Email'] = $email;
		$_SESSION['normalUserEditInfoArray']['SendEmail'] = $_POST['sendEmail'];
		if(isSet($_POST['sendOwnerEmail'])){
			$_SESSION['normalUserEditInfoArray']['SendOwnerEmail'] = $_POST['sendOwnerEmail'];
		}
		if(isSet($_POST['sendAdminEmail'])){
			$_SESSION['normalUserEditInfoArray']['SendAdminEmail'] = $_POST['sendAdminEmail'];
		}
		if(isSet($validatedBookingCode)){
			$_SESSION['normalUserEditInfoArray']['BookingCode'] = hashBookingCode($validatedBookingCode);
			$_SESSION['normalUserEditInfoArray']['LastCodeUpdate'] = getDatetimeNow();
		}
	}
	
	if(isSet($_SESSION['normalUserEditWorksForArray'])){
		// TO-DO: Change SendEmailOnceOrAlways values from POST
		foreach($_SESSION['normalUserEditWorksForArray'] AS $company){
			$post = "sendCompanyID=" . $company['CompanyID'] . "Email";
			if(isSet($_POST[$post])){
				$company['SendEmailOnceOrAlways'] = $_POST[$post];
			}
			$updatedWorksForArray[] = $company;
		}
		$_SESSION['normalUserEditWorksForArray'] = $updatedWorksForArray;
	}

	if(isSet($_POST['confirmPassword']) AND !empty($_POST['confirmPassword']) AND !$invalidInput){
		$password = $_POST['confirmPassword'];
		$hashedPassword = hashPassword($password);
		if($hashedPassword == $result['HashedPassword']){
			if($_SESSION['normalUserEditInfoArray'] != $_SESSION['normalUserOriginalInfoArray']){
				// Save changes to database
				if($changePassword){
					// Change password
					$hashedNewPassword = hashPassword($password1);
				} else {
					$hashedNewPassword = $_SESSION['normalUserOriginalInfoArray']['HashedPassword'];
				}
				$new = $_SESSION['normalUserEditInfoArray'];
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

					$pdo = connect_to_db();

					$pdo->beginTransaction();

					// Update User Information
					$sql = 'UPDATE 	`user` 
							SET		`firstName` = :firstname,
									`lastName` = :lastname,
									`email` = :email,
									`password` = :password,
									`displayName` = :displayname,
									`bookingDescription` = :bookingdescription,
									`sendEmail` = :sendEmail,
									`sendOwnerEmail` = :sendOwnerEmail,
									`sendAdminEmail` = :sendAdminEmail,
									`bookingCode` = :bookingCode,
									`lastCodeUpdate` = :LastCodeUpdate,
									`lastActivity` = CURRENT_TIMESTAMP
							WHERE 	`userID` = :userID';

					$s = $pdo->prepare($sql);
					$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
					$s->bindValue(':firstname', $new['FirstName']);
					$s->bindValue(':lastname', $new['LastName']);
					$s->bindValue(':email', $new['Email']);
					$s->bindValue(':password', $hashedNewPassword);
					$s->bindValue(':displayname', $new['DisplayName']);
					$s->bindValue(':bookingdescription', $new['BookingDescription']);
					$s->bindValue(':sendEmail', $new['SendEmail']);
					$s->bindValue(':sendOwnerEmail', $new['SendOwnerEmail']);
					$s->bindValue(':sendAdminEmail', $new['SendAdminEmail']);
					$s->bindValue(':bookingCode', $new['BookingCode']);
					$s->bindValue(':LastCodeUpdate', $new['LastCodeUpdate']);
					$s->execute();

					// Update Employee Information (company owner emails)
					
					
					$pdo->commit();
					
					// Close the connection
					$pdo = Null;
				}
				catch (PDOException $e)
				{
					$error = 'Error updating user information: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo->rollBack();
					$pdo = null;
					exit();
				}
			} else {
				$_SESSION['normalUserFeedback'] = "No changes were made.";
			}
			unset($_SESSION['normalUserEditMode']);
			unset($_SESSION['normalUserEditInfoArray']);
			unset($_SESSION['normalUserEditWorksForArray']);
			unset($_SESSION['normalUserOriginalInfoArray']);
			unset($_SESSION['normalUserOriginalWorksForArray']);
			
			header("Location: .");
			exit();
		} else {
			$_SESSION['normalUserFeedback'] = "The Password you submitted was incorrect.";
		}
	} elseif(isSet($_POST['confirmPassword']) AND empty($_POST['confirmPassword']) AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to type in your password before you can make any changes.";
	}
}

if(isSet($_POST['action']) AND $_POST['action'] == "Change Information"){
	$_SESSION['normalUserEditMode'] = TRUE;
}

if(isSet($_SESSION['normalUserEditMode'])){
	$editMode = TRUE;
	if(!isSet($_SESSION['normalUserEditInfoArray'])){
		$_SESSION['normalUserEditInfoArray'] = $_SESSION['normalUserOriginalInfoArray'];
	}
	if(!isSet($_SESSION['normalUserEditWorksForArray']) AND isSet($_SESSION['normalUserEditWorksForArray'])){
		$_SESSION['normalUserEditWorksForArray'] = $_SESSION['normalUserOriginalWorksForArray'];
	}
	$edit = $_SESSION['normalUserEditInfoArray'];
	$firstName = $edit['FirstName'];
	$lastName = $edit['LastName'];
	$email = $edit['Email'];
	$displayName = $edit['DisplayName'];
	$bookingDescription = $edit['BookingDescription'];
	$sendEmail = $edit['SendEmail'];
	$sendOwnerEmail = $edit['SendOwnerEmail'];
	$sendAdminEmail = $edit['SendAdminEmail'];

	if(isSet($_SESSION['normalUserEditWorksForArray'])){
		$editWorksFor = $_SESSION['normalUserEditWorksForArray'];
	}
}

var_dump($_SESSION); // TO-DO: Remove after done testing

// Load the html template
include_once 'user.html.php';
?>