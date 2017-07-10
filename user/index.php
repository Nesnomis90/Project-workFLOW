<?php 
// This is the index file for the user folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

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
	} else {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a first name.";
		$invalidInput = TRUE;
	}	
		//Lastname
	if(isset($_POST['lastname'])){
		$lastname = $_POST['lastname'];
		$lastname = trim($lastname);
	} else {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting a last name.";
		$invalidInput = TRUE;
	}		
		//Email
	if(isset($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} else {
		$_SESSION[$FeedbackSessionToUse] = "An account cannot be created without submitting an email.";
		$invalidInput = TRUE;
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
	list($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName) = validateUserInputs('registerUserFeedback');	
	
	if($invalidInput){
		$_SESSION['registerUserFirstName'] = $validatedFirstname;
		$_SESSION['registerUserLastName'] = $validatedLastname;
		$_SESSION['registerUserEmail'] = $email;
		$_SESSION['refreshRegisterUser'] = TRUE;
		header("Location: .");
		exit();
	}
	
	$_SESSION['registerUserFeedback'] = "Your account has been successfully created.\nA confirmation link has been sent to your email.";
	
	$_SESSION['refreshRegisterUser'] = TRUE;
}

// Code to execute when a user wants to register an account 
if(isset($_GET['register']) OR (isset($_SESSION['refreshRegisterUser']) AND $_SESSION['refreshRegisterUser'])){

	
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

// Load the html template
include_once 'user.html.php';
?>