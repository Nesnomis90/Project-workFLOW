<?php 
// This is the index file for the user folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add more or remove
// TO-DO: Add a "Set new password" after user activates their account with the link?
// or just have them do it after they log in themselves...

// Function to validate user inputs
function validateUserInputs($FeedbackSessionToUse){
	$invalidInput = FALSE;
	
	// Get user inputs
		//Firstname
	if(isset($_POST['firstname'])){
		$firstname = $_POST['firstname'];
		$firstname = trim($firstname);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a first name.";
		$invalidInput = TRUE;
	}	
		//Lastname
	if(isset($_POST['lastname'])){
		$lastname = $_POST['lastname'];
		$lastname = trim($lastname);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a last name.";
		$invalidInput = TRUE;
	}		
		//Email
	if(isset($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting an email.";
		$invalidInput = TRUE;
	}


	elseif(isset($_POST['password'])){
		$password = $_POST['password'];
	}
	
		// Display Name (edit only)
	if(isset($_POST['displayname'])){
		$displayNameString = $_POST['displayname'];
	} else {
		$displayNameString = '';
	}
		// Booking Description (edit only)
	if(isset($_POST['bookingdescription'])){
		$bookingDescriptionString = $_POST['bookingdescription'];
	} else {
		$bookingDescriptionString = '';
	}	
	
	// Remove excess whitespace and prepare strings for validation
	$validatedFirstname = trimExcessWhitespace($firstname);
	$validatedLastname = trimExcessWhitespace($lastname);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	$validatedReduceAccessAtDate = trimExcessWhitespace($reduceAccessAtDate);
	
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
	if(isset($_SESSION['EditNormalUserOriginaEmail'])){
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
return array($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName, $validatedReduceAccessAtDate);	
}

// If user wants to submit the registration details and create the account
if(isset($_POST['register']) AND $_POST['register'] == "Register Account"){
	// Input validation
	list($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName) = validateUserInputs('registerUserWarning');	

		//Password
	if(isset($_POST['password1']) AND isset($_POST['password2']) AND !$invalidInput){
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
		
		unset($_SESSION['lastUserID']);
		$_SESSION['lastUserID'] = $pdo->lastInsertId();
		
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
		if(isset($_SESSION['LoggedInUserName'])){
			$description = "An account for: " . $userinfo . " was registered by: " . $_SESSION['LoggedInUserName'];
		} else {
			$description = "An account was registered for " . $userinfo;
		}
		
		if(isset($_SESSION['lastUserID'])){
			$lastUserID = $_SESSION['lastUserID'];
			unset($_SESSION['lastUserID']);				
		}

		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Account Created'
											),
							`UserID` = :UserID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
		$s->bindValue(':UserID', $lastUserID);
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
		$_SESSION['registerUserFeedback'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['registerUserFeedback'] .= "this is the email msg we're sending out: $emailMessage. Sent to: $email."; // TO-DO: Remove after testing	
	
	// End of register account 
	$_SESSION['registerUserFeedback'] = "Your account has been successfully created.\nA confirmation link has been sent to your email.";
	
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
if(isset($_GET['register']) OR (isset($_SESSION['refreshRegisterUser']) AND $_SESSION['refreshRegisterUser'])){

	if(isset($_SESSION['refreshRegisterUser']) AND $_SESSION['refreshRegisterUser']){
		$refreshedRegister = TRUE;
		unset($_SESSION['refreshRegisterUser']);
	}
	
	if(isset($_SESSION['registerUserWarning']) AND strpos(strtolower($_SESSION['registerUserWarning']), 'email') !== FALSE){
		$invalidEmail = TRUE;
	}
	// Set correct startvalues
	if(isset($_SESSION['registerUserFirstName'])){
		$firstName = $_SESSION['registerUserFirstName'];
		unset($_SESSION['registerUserFirstName']);
	} else {
		$firstName = "";
	}
	if(isset($_SESSION['registerUserLastName'])){
		$lastName = $_SESSION['registerUserLastName'];
		unset($_SESSION['registerUserLastName']);
	} else {
		$lastName = "";
	}
	if(isset($_SESSION['registerUserEmail'])){
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
if(isset($_GET['activateaccount'])){
	
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
	if(isset($result)){
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
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Account Activated'
											),
							`description` = :description,
							`userID` = :userID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->bindValue(':userID', $userID);
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

if(isset($_POST['action']) AND $_POST['action'] == "Reset"){
	$_SESSION['normalUserEditInfoArray'] = $_SESSION['normalUserOriginalInfoArray'];
}

if(isset($_SESSION['loggedIn']) AND isset($_SESSION['LoggedInUserID'])){
	// Get User information if user is logged in
	$userID = $_SESSION['LoggedInUserID'];
	if(!isset($_SESSION['normalUserOriginalInfoArray'])){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "SELECT 		u.`email`				AS Email,
								u.`firstName`			AS FirstName,
								u.`lastName`			AS LastName,
								u.`displayName`			AS DisplayName,
								u.`bookingDescription`	AS BookingDescription,
								u.`bookingCode`			AS BookingCode,
								u.`create_time`			AS DateTimeCreated,
								u.`lastActivity`		AS LastActive,
								u.`sendEmail`			AS SendEmail,
								u.`sendAdminEmail`		AS SendAdminEmail,
								u.`password`			AS HashedPassword,
								a.`AccessName`			AS AccessName,
								a.`Description` 		AS AccessDescription
					FROM		`user` u
					INNER JOIN	`accesslevel` a
					WHERE 		`userID` = :userID
					AND			`isActive` = 1
					LIMIT 		1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $userID);
			$s->execute();
			
			$result = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['normalUserOriginalInfoArray'] = $result;
			
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
	}

	$lastActive = convertDatetimeToFormat($result['LastActive'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS);
	$dateCreated = convertDatetimeToFormat($result['DateTimeCreated'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS);
	
	$originalFirstName = $result['FirstName'];
	$originalLastName = $result['LastName'];
	$originalEmail = $result['Email'];
	$originalDisplayName = $result['DisplayName'];
	$originalBookingDescription = $result['BookingDescription'];
	$originalSendEmail = $result['SendEmail'];
	$originalSendAdminEmail = $result['SendAdminEmail'];
	
	$accessName = $result['AccessName'];
	$accessDescription = $result['AccessDescription'];
	$originalBookingCode = $result['BookingCode'];
	
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
	unset($_SESSION['normalUserEditInfoArray']);
	unset($_SESSION['normalUserEditMode']);
}

if(isset($_POST['action']) AND $_POST['action'] == "Show Code"){
	$showBookingCode = revealBookingCode($originalBookingCode);
}

if(isset($_POST['action']) AND $_POST['action'] == "Confirm Change"){
	// Do input validation
	$invalidInput = FALSE;
	
	// Get user inputs
		// Firstname
	if(isset($_POST['firstName'])){
		$firstname = $_POST['firstName'];
		$firstname = trim($firstname);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have a first name.";
		$invalidInput = TRUE;
	}	
		// Lastname
	if(isset($_POST['lastName'])){
		$lastname = $_POST['lastName'];
		$lastname = trim($lastname);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have a last name.";
		$invalidInput = TRUE;
	}		
		// Email
	if(isset($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} elseif(!$invalidInput) {
		$_SESSION['normalUserFeedback'] = "Your account needs to have an email.";
		$invalidInput = TRUE;
	}
		// Display Name
	if(isset($_POST['displayName'])){
		$displayNameString = $_POST['displayName'];
	} else {
		$displayNameString = '';
	}
		// Booking Description
	if(isset($_POST['bookingDescription'])){
		$bookingDescriptionString = $_POST['bookingDescription'];
	} else {
		$bookingDescriptionString = '';
	}
		// Booking Code
	if(isset($_POST['bookingCode']) AND !empty($_POST['bookingCode'])){
		$bookingCode = $_POST['bookingCode'];
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedFirstname = trimExcessWhitespace($firstname);
	$validatedLastname = trimExcessWhitespace($lastname);
	$validatedDisplayName = trimExcessWhitespaceButLeaveLinefeed($displayNameString);
	$validatedBookingDescription = trimExcessWhitespaceButLeaveLinefeed($bookingDescriptionString);
	if(isset($bookingCode)){
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
	if(isset($validatedBookingCode)){
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
		if (databaseContainsEmail($email)){
			// The email has been used before. So we can't create a new user with this info.
			$_SESSION['normalUserFeedback'] = "The new email you've set is already connected to an account.";
			$invalidInput = TRUE;	
		}				
	}

	$changePassword = FALSE;
	
	// Check if user is trying to set a new password
	// And if so, check if both fields are filled in and match each other
	if(isset($_POST['password1'])){
		$password1 = $_POST['password1'];
	} 
	if(isset($_POST['password2'])){
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

	if(isset($_SESSION['normalUserEditInfoArray'])){
		$_SESSION['normalUserEditInfoArray']['FirstName'] = $validatedFirstname;
		$_SESSION['normalUserEditInfoArray']['LastName'] = $validatedLastname;
		$_SESSION['normalUserEditInfoArray']['DisplayName'] = $validatedDisplayName;
		$_SESSION['normalUserEditInfoArray']['BookingDescription'] = $validatedBookingDescription;
		$_SESSION['normalUserEditInfoArray']['Email'] = $email;
		$_SESSION['normalUserEditInfoArray']['SendEmail'] = $_POST['sendEmail'];
		if(isset($validatedBookingCode)){
			$_SESSION['normalUserEditInfoArray']['BookingCode'] = hashBookingCode($validatedBookingCode);
		}
	}

	if(isset($_POST['confirmPassword']) AND !empty($_POST['confirmPassword']) AND !$invalidInput){
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
					$sql = 'UPDATE 	`user` 
							SET		`firstName` = :firstname,
									`lastName` = :lastname,
									`email` = :email,
									`password` = :password,
									`displayName` = :displayname,
									`bookingDescription` = :bookingdescription,
									`sendEmail` = :sendEmail,
									`bookingCode` = :bookingCode
							WHERE 	userID = :userID';
							
					$s = $pdo->prepare($sql);
					$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
					$s->bindValue(':firstname', $new['FirstName']);
					$s->bindValue(':lastname', $new['LastName']);
					$s->bindValue(':email', $new['Email']);
					$s->bindValue(':password', $hashedNewPassword);
					$s->bindValue(':displayname', $new['DisplayName']);
					$s->bindValue(':bookingdescription', $new['BookingDescription']);
					$s->bindValue(':sendEmail', $new['SendEmail']);
					$s->bindValue(':bookingCode', $new['BookingCode']);
					$s->execute();
						
					// Close the connection
					$pdo = Null;
				}
				catch (PDOException $e)
				{
					$error = 'Error updating submitted user: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}				
			}
			unset($_SESSION['normalUserEditMode']);
			unset($_SESSION['normalUserEditInfoArray']);
			unset($_SESSION['normalUserOriginalInfoArray']);
		} else {
			$_SESSION['normalUserFeedback'] = "The Password you submitted was incorrect.";
		}
	} elseif(isset($_POST['confirmPassword']) AND empty($_POST['confirmPassword']) AND !$invalidInput){
		$_SESSION['normalUserFeedback'] = "You need to type in your password before you can make any changes.";
	}
}

if(isset($_POST['action']) AND $_POST['action'] == "Change Information"){
	$_SESSION['normalUserEditMode'] = TRUE;
}

if(isset($_SESSION['normalUserEditMode'])){
	$editMode = TRUE;
	if(!isset($_SESSION['normalUserEditInfoArray'])){
		$_SESSION['normalUserEditInfoArray'] = $_SESSION['normalUserOriginalInfoArray'];
	}
	$edit = $_SESSION['normalUserEditInfoArray'];
	$firstName = $edit['FirstName'];
	$lastName = $edit['LastName'];
	$email = $edit['Email'];
	$displayName = $edit['DisplayName'];
	$bookingDescription = $edit['BookingDescription'];
	$sendEmail = $edit['SendEmail'];
}

var_dump($_SESSION); // TO-DO: Remove after done testing

// Load the html template
include_once 'user.html.php';
?>