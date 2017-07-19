<?php 
// This is the index file for the USERS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add user form
function clearAddUserSessions(){
	unset($_SESSION['AddNewUserFirstname']);
	unset($_SESSION['AddNewUserLastname']);
	unset($_SESSION['AddNewUserEmail']);
	unset($_SESSION['AddNewUserSelectedAccess']);	
	unset($_SESSION['AddNewUserAccessArray']);
	unset($_SESSION['AddNewUserGeneratedPassword']);
	unset($_SESSION['AddNewUserDefaultAccessID']);
	
	unset($_SESSION['lastUserID']);
	
	unset($_SESSION['UserEmailListSeparatorSelected']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit user form
function clearEditUserSessions(){
	unset($_SESSION['EditUserOriginaEmail']);
	unset($_SESSION['EditUserOriginalFirstName']);
	unset($_SESSION['EditUserOriginalLastName']);
	unset($_SESSION['EditUserOriginaAccessID']);
	unset($_SESSION['EditUserOriginaAccessName']);
	unset($_SESSION['EditUserOriginaDisplayName']);
	unset($_SESSION['EditUserOriginaBookingDescription']);
	unset($_SESSION['EditUserOriginaReduceAccessAtDate']);
	unset($_SESSION['EditUserOriginalUserID']);
	
	unset($_SESSION['EditUserChangedEmail']);	
	unset($_SESSION['EditUserChangedFirstname']);
	unset($_SESSION['EditUserChangedLastname']);
	unset($_SESSION['EditUserChangedAccessID']);
	unset($_SESSION['EditUserChangedDisplayname']);
	unset($_SESSION['EditUserChangedBookingDescription']);
	unset($_SESSION['EditUserChangedReduceAccessAtDate']);
	
	unset($_SESSION['EditUserAccessList']);
	
	unset($_SESSION['UserEmailListSeparatorSelected']);
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
		$_SESSION[$FeedbackSessionToUse] = "A user cannot be created without submitting a first name.";
		$invalidInput = TRUE;
	}	
		//Lastname
	if(isSet($_POST['lastname'])){
		$lastname = $_POST['lastname'];
		$lastname = trim($lastname);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "A user cannot be created without submitting a last name.";
		$invalidInput = TRUE;
	}		
		//Email
	if(isSet($_POST['email'])){
		$email = $_POST['email'];
		$email = trim($email);
	} elseif(!$invalidInput) {
		$_SESSION[$FeedbackSessionToUse] = "A user cannot be created without submitting an email.";
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
		// Reduce Access At Date (edit only)
	if(isSet($_POST['ReduceAccessAtDate'])){
		$reduceAccessAtDate = $_POST['ReduceAccessAtDate'];
	} else {
		$reduceAccessAtDate = '';
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
		$_SESSION[$FeedbackSessionToUse] = "Your submitted booking description has illegal characters in it.";
		$invalidInput = TRUE;
	}	
	$invalidBookingDescription = isLengthInvalidBookingDescription($validatedBookingDescription);
	if($invalidBookingDescription AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// Reduce Access At Date
	if(validateDateTimeString($validatedReduceAccessAtDate) === FALSE AND !$invalidInput){
		$_SESSION[$FeedbackSessionToUse] = "Your submitted date has illegal characters in it.";
		$invalidInput = TRUE;
	}

	// Check if the dateTime input we received are actually datetime
	// if the user submitted one
	if($validatedReduceAccessAtDate != ""){
		
		$correctFormatIfValid = correctDatetimeFormat($validatedReduceAccessAtDate);

		if (isSet($correctFormatIfValid) AND $correctFormatIfValid === FALSE AND !$invalidInput){
			$_SESSION[$FeedbackSessionToUse] = "The date you submitted did not have a correct format. Please try again.";
			$invalidInput = TRUE;
		}
		if(isSet($correctFormatIfValid) AND $correctFormatIfValid !== FALSE){
			$correctFormatIfValid = convertDatetimeToFormat($correctFormatIfValid,'Y-m-d H:i:s', 'Y-m-d');
			
			// Check if the (now valid) datetime we received is a future date or not
			$dateNow = getDateNow();
			if(!($correctFormatIfValid > $dateNow)){
				$_SESSION[$FeedbackSessionToUse] = "The date you submitted has already occured. Please choose a future date.";
				$invalidInput = TRUE;
			} else {
				$validatedReduceAccessAtDate = $correctFormatIfValid;
			}		
		}
	}
	
	// Check if the submitted email has already been used
	if(isSet($_SESSION['EditUserOriginaEmail'])){
		$originalEmail = $_SESSION['EditUserOriginaEmail'];
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

// If admin wants to return to users from the email-list
if (isSet($_POST['action']) AND $_POST['action'] == "Return To Users"){
	unset($_SESSION['UserEmailListSeparatorSelected']);
	header('Location: .');
	exit();
}

// If admin wants to get a list of easily copied emails from the users that is being displayed
if (	(isSet($_GET['getEmails'])) OR 
		isSet($_SESSION['refreshUserEmailList']) AND $_SESSION['refreshUserEmailList']){

		if(isSet($_GET['getEmails'])){
			$_SESSION['refreshUserEmailList'] = TRUE;
			header("Location: .");
			exit();
		}
		
		if(isSet($_SESSION['refreshUserEmailList']) AND !isSet($_GET['getEmails'])){
			unset($_SESSION['refreshUserEmailList']);
		}
		
	$separatorChar = ",";
	$_SESSION['UserEmailListSeparatorSelected'] = TRUE;
	$emailList = implode($separatorChar,$_SESSION['UserEmailsToBeDisplayed']);
	
	var_dump($_SESSION);	// TO-DO: Remove when done testing
	
	include_once 'listemail.html.php';
	exit();
}

// If admin wants to change email separator from the default ","
if (isSet($_POST['action']) AND $_POST['action'] == "Change Separator Char"){
	
	unset($_SESSION['UserEmailListSeparatorSelected']);
	$separatorChar = "";
	$emailList = "";
	
	var_dump($_SESSION);	// TO-DO: Remove when done testing
	
	include_once 'listemail.html.php';
	exit();	
}

// If admin wants to set the newly selected character as the new email separator
if (isSet($_POST['action']) AND $_POST['action'] == "Select Separator Char"){
	
	$separatorChar = trim($_POST['separatorchar']);
	$invalidInput = FALSE;
	if(strlen($separatorChar) == 0){
		$_SESSION['UserEmailListError'] = "You did not submit a character. Set as default character: ','.";
		$invalidInput = TRUE;
	}	
	if(strlen(utf8_decode($separatorChar)) > 1){
		// We only want one char
		// TO-DO: Change/remove if wrong
		$_SESSION['UserEmailListError'] = "You submitted too many characters. Set as default character: ','.";
		$invalidInput = TRUE;
	}
	
	if($invalidInput){
 		$_SESSION['refreshUserEmailList'] = TRUE;
		header("Location: .");
		exit();
	}
	
	$_SESSION['UserEmailListSeparatorSelected'] = TRUE;
	$emailList = implode($separatorChar,$_SESSION['UserEmailsToBeDisplayed']);
	var_dump($_SESSION);	// TO-DO: Remove when done testing
	
	include_once 'listemail.html.php';
	exit();
}



// If admin wants to be able to delete users it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['usersEnableDelete'] = TRUE;
	$refreshUsers = TRUE;
}

// If admin wants to be disable user deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['usersEnableDelete']);
	$refreshUsers = TRUE;
}


// If admin wants to remove a user from the database
if (isSet($_POST['action']) and $_POST['action'] == 'Delete')
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	// Delete selected user from database
	try
	{
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `user` 
				WHERE 		`userID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting user to delete: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	$_SESSION['UserManagementFeedbackMessage'] = "User Successfully Removed.";
	
	// Add a log event that a user account was removed
	try
	{
		// Save a description with information about the user that was removed
		
		$description = "N/A";
		if(isSet($_POST['UserInfo'])){
			$description = 'The User: ' . $_POST['UserInfo'] . 
			' was deleted by: ' . $_SESSION['LoggedInUserName'];
		} else {
			$description = 'An unactivated User was deleted by: ' . $_SESSION['LoggedInUserName'];
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Account Removed'
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
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();	
}
 
// If admin wants to add a user to the database
// we load a new html form
if (isSet($_GET['add']) OR (isSet($_SESSION['refreshAddUser']) AND $_SESSION['refreshAddUser']))
{	
	unset($_SESSION['UserEmailsToBeDisplayed']);

	// Check if the call was /?add/ or a forced refresh
	if(isSet($_SESSION['refreshAddUser']) AND $_SESSION['refreshAddUser']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshAddUser']);
		
		// Set correct values
		$access = $_SESSION['AddNewUserAccessArray'];
		$generatedPassword = $_SESSION['AddNewUserGeneratedPassword'];
	} else {
		// Make sure we don't have any remembered values in memory
		clearAddUserSessions();
		
		// Get name and IDs for access level
		// Admin needs to give a new user a specific access.
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'SELECT 	`accessID`,
							`accessname` 
					FROM 	`accesslevel`';
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);
			
			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$access[] = array(
									'accessID' => $row['accessID'],
									'accessname' => $row['accessname']
									);
				if($row['accessname'] == 'Normal User'){
					$_SESSION['AddNewUserDefaultAccessID'] = $row['accessID'];
				}
			}
			
			//Close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error getting access level info from database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
		
		// Generate password for user
		$generatedPassword = generateUserPassword(MINIMUM_PASSWORD_LENGTH);
		
		// Set correct values
		$_SESSION['AddNewUserAccessArray'] = $access;
		$_SESSION['AddNewUserGeneratedPassword'] = $generatedPassword;
	}
	
	// Set initial values
	$firstname = '';
	$lastname = '';
	$email = '';	
	
	// Set always correct values
	$pageTitle = 'New User';
	$action = 'addform';
	$button = 'Add User';	
	$id = '';
	$displayname = '';
	$bookingdescription = '';	
	
	// If we refreshed and want to keep the same values
	if(isSet($_SESSION['AddNewUserFirstname'])){
		$firstname = $_SESSION['AddNewUserFirstname'];
		unset($_SESSION['AddNewUserFirstname']);		
	}
	if(isSet($_SESSION['AddNewUserLastname'])){
		$lastname = $_SESSION['AddNewUserLastname'];
		unset($_SESSION['AddNewUserLastname']);		
	}	
	if(isSet($_SESSION['AddNewUserEmail'])){
		$email = $_SESSION['AddNewUserEmail'];
		unset($_SESSION['AddNewUserEmail']);		
	}	
	if(isSet($_SESSION['AddNewUserSelectedAccess'])){
		$accessID = $_SESSION['AddNewUserSelectedAccess'];
		unset($_SESSION['AddNewUserSelectedAccess']);		
	} else {
		$accessID = $_SESSION['AddNewUserDefaultAccessID'];
	}
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the user
if (isSet($_GET['addform']) AND isSet($_POST['action']) AND $_POST['action'] == 'Add User')
{
	// Validate user inputs
	list($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName, $validatedReduceAccessAtDate) = validateUserInputs('AddNewUserError');	
	
	if($invalidInput){
		// Let's remember the info the admin submitted
		$_SESSION['AddNewUserFirstname'] = $validatedFirstname;
		$_SESSION['AddNewUserLastname'] = $validatedLastname;
		$_SESSION['AddNewUserEmail'] = $email;
		$_SESSION['AddNewUserSelectedAccess'] = $_POST['accessID'];	
		
		// Let's refresh the add template
		$_SESSION['refreshAddUser'] = TRUE;
		header('Location: .');
		exit();
	}
	
	// The email has NOT been used before and all inputs are valid, so we can create the new user!
	try
	{
		// Add the user to the database
		
		//Generate activation code
		$activationcode = generateActivationCode();
		
		// Hash the user generated password
		$hashedPassword = hashPassword($_SESSION['AddNewUserGeneratedPassword']);
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `user` 
				SET			`firstname` = :firstname,
							`lastname` = :lastname,
							`accessID` = :accessID,
							`password` = :password,
							`activationcode` = :activationcode,
							`email` = :email';
		$s = $pdo->prepare($sql);
		$s->bindValue(':firstname', $validatedFirstname);
		$s->bindValue(':lastname', $validatedLastname);		
		$s->bindValue(':accessID', $_POST['accessID']);
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
		$error = 'Error adding submitted user to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['UserManagementFeedbackMessage'] = 
	"User Successfully Created. It is currently inactive and unable to log in.";
		
	// Add a log event that a user has been created
	try
	{
		// Save a description with information about the user that was added
		
		$description = "N/A";
		$userinfo = $validatedLastname . ', ' . $validatedFirstname . ' - ' . $email;
		if(isSet($_SESSION['LoggedInUserName'])){
			$description = "An account for: " . $userinfo . " was created by: " . $_SESSION['LoggedInUserName'];
		} else {
			$description = "An account was created for " . $userinfo;
		}
		
		if(isSet($_SESSION['lastUserID'])){
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
	$generatedPassword = $_SESSION['AddNewUserGeneratedPassword'];

	$emailSubject = "Account Activation Link";
	
	$emailMessage = 
	"Your account has been created.\n" . 
	"Your generated Password: " . $generatedPassword . ".\n" .
	"For security reasons you should set a new password after you've logged in.\n\n" .
	"Before you can log in you need to activate your account.\n" .
	"If the account isn't activated within 8 hours, it is removed.\n" .
	"Click this link to activate your account: " . $_SERVER['HTTP_HOST'] . 
	"/user/?activateaccount=" . $activationcode;
	
	$mailResult = sendEmail($email, $emailSubject, $emailMessage);
	
	if(!$mailResult){
		$_SESSION['UserManagementFeedbackMessage'] .= " [WARNING] System failed to send Email to user.";
	}
	
	$_SESSION['UserManagementFeedbackMessage'] .= "this is the email msg we're sending out: $emailMessage. Sent to: $email."; // TO-DO: Remove after testing	
	
	// Forget information we don't need anymore
	clearAddUserSessions();

	// Load user list webpage with new user
	header('Location: .');
	exit();
}

// If admin wants to null values while adding
if (isSet($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddNewUserFirstname'] = "";
	$_SESSION['AddNewUserLastname'] = "";
	$_SESSION['AddNewUserEmail'] = "";
	$_SESSION['AddNewUserSelectedAccess'] = $_SESSION['AddNewUserDefaultAccessID'];
	
	$_SESSION['refreshAddUser'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the user page again
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['UserManagementFeedbackMessage'] = "You cancelled your user creation.";
	header("Location: /admin/users");
	exit();
}

// if admin wants to edit user information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR 
(isSet($_SESSION['refreshEditUser'])) AND $_SESSION['refreshEditUser'])
{
	unset($_SESSION['UserEmailsToBeDisplayed']);
	
		// Check if the call was edit button or a forced refresh
	if(isSet($_SESSION['refreshEditUser']) AND $_SESSION['refreshEditUser']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshEditUser']);
	
		// Set the information back to what it was before the refresh
		$firstname = $_SESSION['EditUserChangedFirstname'];
		unset($_SESSION['EditUserChangedFirstname']);
		$lastname = $_SESSION['EditUserChangedLastname'];
		unset($_SESSION['EditUserChangedLastname']);
		$email = $_SESSION['EditUserChangedEmail'];
		unset($_SESSION['EditUserChangedEmail']);
		$accessID = $_SESSION['EditUserChangedAccessID'];
		unset($_SESSION['EditUserChangedAccessID']);
		$id = $_SESSION['EditUserOriginalUserID'];
		$displayname = $_SESSION['EditUserChangedDisplayname'];
		unset($_SESSION['EditUserChangedDisplayname']);
		$bookingdescription = $_SESSION['EditUserChangedBookingDescription'];
		unset($_SESSION['EditUserChangedBookingDescription']);
		
		$access = $_SESSION['EditUserAccessList'];
		
	} else {
		
		// Make sure we don't come in with old info in memory
		clearEditUserSessions();
		// Get information from database again on the selected user
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'SELECT 	u.`userID`, 
							u.`firstname`, 
							u.`lastname`, 
							u.`email`,
							a.`accessID`,
							a.`accessname`,
							u.`displayname`,
							u.`bookingdescription`,
							u.`reduceAccessAtDate`
					FROM 	`user` u
					JOIN 	`accesslevel` a
					ON 		a.accessID = u.accessID
					WHERE 	u.`userID` = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
			
			// Get name and IDs for access level
			$sql = 'SELECT 	`accessID`,
							`accessname` 
					FROM 	`accesslevel`';
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);
			
			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$access[] = array(
									'accessID' => $row['accessID'],
									'accessname' => $row['accessname']
									);
			}
			
			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching user details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		
		// Create an array with the row information we retrieved
		$row = $s->fetch();
		
		// Set the correct information
		$firstname = $row['firstname'];
		$lastname = $row['lastname'];
		$email = $row['email'];
		$accessID = $row['accessID'];
		$accessName = $row['accessname'];
		$id = $row['userID'];
		$displayname = $row['displayname'];
		$bookingdescription = $row['bookingdescription'];
		$reduceAccessAtDate = $row['reduceAccessAtDate'];
	
		if(!isSet($reduceAccessAtDate)){
			$reduceAccessAtDate = '';
		}
	
		// Remember the original values we retrieved.
		$_SESSION['EditUserOriginalFirstName'] = $firstname;
		$_SESSION['EditUserOriginalLastName'] = $lastname;
		$_SESSION['EditUserOriginaEmail'] = $email;
		$_SESSION['EditUserOriginaAccessID'] = $accessID;
		$_SESSION['EditUserOriginaDisplayName'] = $displayname;
		$_SESSION['EditUserOriginaBookingDescription'] = $bookingdescription;
		$_SESSION['EditUserOriginaReduceAccessAtDate'] = $reduceAccessAtDate;
		$_SESSION['EditUserOriginaAccessName'] = $accessName;
		
		$_SESSION['EditUserOriginalUserID'] = $id;
		$_SESSION['EditUserAccessList'] = $access;
	}
	// Display original values
	$originalDateToDisplay = convertDatetimeToFormat($_SESSION['EditUserOriginaReduceAccessAtDate'] , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	if(isSet($_SESSION['EditUserChangedReduceAccessAtDate'])){
		$reduceAccessAtDate = $_SESSION['EditUserChangedReduceAccessAtDate'];
	} else {
		$reduceAccessAtDate = $originalDateToDisplay;
	}
	
	// Set the correct information
	$pageTitle = 'Edit User';
	$action = 'editform';
	$button = 'Edit User';
	$password = '';
	$confirmpassword = '';
	
	$originalFirstName = $_SESSION['EditUserOriginalFirstName'];
	$originalLastName = $_SESSION['EditUserOriginalLastName'];
	$originalEmail = $_SESSION['EditUserOriginaEmail'];
	$originalDisplayName = $_SESSION['EditUserOriginaDisplayName'];
	$originalBookingDescription = $_SESSION['EditUserOriginaBookingDescription'];
	$originalAccessName = $_SESSION['EditUserOriginaAccessName'];
		
	var_dump($_SESSION); // TO-DO: remove after testing is done	
		
	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isSet($_GET['editform'])AND isSet($_POST['action']) AND $_POST['action'] == 'Edit User')
{
		// Validate user inputs
	list($invalidInput, $email, $validatedFirstname, $validatedLastname, $validatedBookingDescription, $validatedDisplayName, $validatedReduceAccessAtDate) = validateUserInputs('AddNewUserError');

	// Check if any values were actually changed
	$NumberOfChanges = 0;
	$changePassword = FALSE;
	$changedReduceAccessAtDate = FALSE;
	
	// Check if user is trying to set a new password
	// And if so, check if both fields are filled in and match each other
	if(isSet($_POST['password'])){
		$password = $_POST['password'];
	} 
	if(isSet($_POST['confirmpassword'])){
		$confirmPassword = $_POST['confirmpassword'];
	}
	$minimumPasswordLength = MINIMUM_PASSWORD_LENGTH;
	if(($password != '' OR $confirmPassword != '') AND !$invalidInput){
			
		if($password == $confirmPassword){
			// Both passwords match, hopefully that means it's the correct password the user wanted to submit

				if(strlen(utf8_decode($password)) < $minimumPasswordLength){
					$_SESSION['AddNewUserError'] = "The submitted password is not long enough. You are required to make it at least $minimumPasswordLength characters long.";
					$invalidInput = TRUE;			
				} else {
					// Both passwords were the same. They were not empty and they were longer than the minimum requirement
					$NumberOfChanges++;
					$changePassword = TRUE;				
				}
		} else {
			$_SESSION['AddNewUserError'] = "Password and Confirm Password did not match.";
			$invalidInput = TRUE;
		}
	} else {
		// Password was empty. Not a big deal since it's not required
		// Just means we won't change it!
	}
	if($invalidInput){
		// Let's remember the info the admin submitted
		$_SESSION['EditUserChangedFirstname'] = $validatedFirstname;
		$_SESSION['EditUserChangedLastname'] = $validatedLastname;
		$_SESSION['EditUserChangedEmail'] = $email;
		$_SESSION['EditUserChangedAccessID'] = $_POST['accessID'];
		$_SESSION['EditUserChangedDisplayname'] = $validatedDisplayName;
		$_SESSION['EditUserChangedBookingDescription'] = $validatedBookingDescription;	
		$_SESSION['EditUserChangedReduceAccessAtDate'] = $validatedReduceAccessAtDate;
		
		// Let's refresh the edit template
		$_SESSION['refreshEditUser'] = TRUE;
		header('Location: .');
		exit();
	}
		
		// Check against the values we retrieved before loading the page
	if ( isSet($_SESSION['EditUserOriginalFirstName']) AND 
	$validatedFirstname != $_SESSION['EditUserOriginalFirstName'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginalFirstName']);
	}
	if ( isSet($_SESSION['EditUserOriginalLastName']) AND 
	$validatedLastname != $_SESSION['EditUserOriginalLastName'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginalLastName']);
	}
	if ( isSet($_SESSION['EditUserOriginaEmail']) AND 
	$email != $_SESSION['EditUserOriginaEmail'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginaEmail']);
	}
	if ( isSet($_SESSION['EditUserOriginaAccessID']) AND 
	$_POST['accessID'] != $_SESSION['EditUserOriginaAccessID'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginaAccessID']);
	}
	if ( isSet($_SESSION['EditUserOriginaDisplayName']) AND 
	$validatedDisplayName != $_SESSION['EditUserOriginaDisplayName'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginaDisplayName']);
	}	
	if ( isSet($_SESSION['EditUserOriginaBookingDescription']) AND 
	$validatedBookingDescription != $_SESSION['EditUserOriginaBookingDescription'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginaBookingDescription']);
	}
	if ( isSet($_SESSION['EditUserOriginaReduceAccessAtDate']) AND 
	$validatedReduceAccessAtDate != $_SESSION['EditUserOriginaReduceAccessAtDate'])
	{
		$changedReduceAccessAtDate = TRUE;
		$NumberOfChanges++;
		unset($_SESSION['EditUserOriginaReduceAccessAtDate']);
	}

	if ($NumberOfChanges > 0){
		// We actually have something to update!	
		try
		{
			if ($changePassword AND $changedReduceAccessAtDate){
				// Update user info (new password and date)
				$newPassword = $password;
				$hashedNewPassword = hashPassword($newPassword);
				
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				$pdo = connect_to_db();
				$sql = 'UPDATE `user` SET
								firstname = :firstname,
								lastname = :lastname,
								email = :email,
								password = :password,
								accessID = :accessID,
								displayname = :displayname,
								bookingdescription = :bookingdescription,
								reduceAccessAtDate = :reduceAccessAtDate
						WHERE 	userID = :id';
						
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->bindValue(':firstname', $validatedFirstname);
				$s->bindValue(':lastname', $validatedLastname);
				$s->bindValue(':email', $email);
				$s->bindValue(':password', $hashedNewPassword);
				$s->bindValue(':accessID', $_POST['accessID']);
				$s->bindValue(':displayname', $validatedDisplayName);
				$s->bindValue(':bookingdescription', $validatedBookingDescription);
				$s->bindValue(':reduceAccessAtDate', $validatedReduceAccessAtDate);
				$s->execute();			
			} elseif($changePassword AND !$changedReduceAccessAtDate) {
				// Update user info (no new date)
				$newPassword = $password;
				$hashedNewPassword = hashPassword($newPassword);
				
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				$pdo = connect_to_db();
				$sql = 'UPDATE `user` SET
								firstname = :firstname,
								lastname = :lastname,
								email = :email,
								password = :password,
								accessID = :accessID,
								displayname = :displayname,
								bookingdescription = :bookingdescription
						WHERE 	userID = :id';
						
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->bindValue(':firstname', $validatedFirstname);
				$s->bindValue(':lastname', $validatedLastname);
				$s->bindValue(':email', $email);
				$s->bindValue(':password', $hashedNewPassword);
				$s->bindValue(':accessID', $_POST['accessID']);
				$s->bindValue(':displayname', $validatedDisplayName);
				$s->bindValue(':bookingdescription', $validatedBookingDescription);
				$s->execute();					
			} elseif(!$changePassword AND $changedReduceAccessAtDate){
				// Update user info (no new password)
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				$pdo = connect_to_db();
				$sql = 'UPDATE `user` 
						SET		firstname = :firstname,
								lastname = :lastname,
								email = :email,
								accessID = :accessID,
								displayname = :displayname,
								bookingdescription = :bookingdescription,
								reduceAccessAtDate = :reduceAccessAtDate
						WHERE 	userID = :id';
						
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->bindValue(':firstname', $validatedFirstname);
				$s->bindValue(':lastname', $validatedLastname);
				$s->bindValue(':email', $email);
				$s->bindValue(':accessID', $_POST['accessID']);
				$s->bindValue(':displayname', $validatedDisplayName);
				$s->bindValue(':bookingdescription', $validatedBookingDescription);
				$s->bindValue(':reduceAccessAtDate', $validatedReduceAccessAtDate);
				$s->execute();					
			} elseif(!$changePassword AND !$changedReduceAccessAtDate){
				// Update user info (no new password and no new date)
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				$pdo = connect_to_db();
				$sql = 'UPDATE `user` 
						SET		firstname = :firstname,
								lastname = :lastname,
								email = :email,
								accessID = :accessID,
								displayname = :displayname,
								bookingdescription = :bookingdescription
						WHERE 	userID = :id';
						
				$s = $pdo->prepare($sql);
				$s->bindValue(':id', $_POST['id']);
				$s->bindValue(':firstname', $validatedFirstname);
				$s->bindValue(':lastname', $validatedLastname);
				$s->bindValue(':email', $email);
				$s->bindValue(':accessID', $_POST['accessID']);
				$s->bindValue(':displayname', $validatedDisplayName);
				$s->bindValue(':bookingdescription', $validatedBookingDescription);
				$s->execute();						
			}
				
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
		
		$_SESSION['UserManagementFeedbackMessage'] = "User Successfully Updated.";		
	} else {		
		$_SESSION['UserManagementFeedbackMessage'] = "No changes were made.";
	}
	
	// No need to remember values anymore
	clearEditUserSessions();
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();
}

// If admin wants to change the values back to the original values while editing
if (isSet($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditUserChangedFirstname'] = $_SESSION['EditUserOriginalFirstName'];
	$_SESSION['EditUserChangedLastname'] = $_SESSION['EditUserOriginalLastName'];
	$_SESSION['EditUserChangedEmail'] = $_SESSION['EditUserOriginaEmail'];
	$_SESSION['EditUserChangedAccessID'] = $_SESSION['EditUserOriginaAccessID'];
	$_SESSION['EditUserChangedDisplayname'] = $_SESSION['EditUserOriginaDisplayName'];
	$_SESSION['EditUserChangedBookingDescription'] = $_SESSION['EditUserOriginaBookingDescription'];
	$_SESSION['EditUserChangedReduceAccessAtDate'] = $_SESSION['EditUserOriginaReduceAccessAtDate'];
	
	$_SESSION['refreshEditUser'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the user page again
if (isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){

	$_SESSION['UserManagementFeedbackMessage'] = "You cancelled your user editing.";
	header("Location: /admin/users");
	exit();
}

// if admin wants to cancel the date to reduce access
if (isSet($_POST['action']) AND $_POST['action'] == 'Cancel Date')
{
	// Update selected user by making date to reduce access null	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`user` 
				SET		`reduceAccessAtDate` = NULL
				WHERE 	userID = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error cancelling reduce access date: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	$_SESSION['UserManagementFeedbackMessage'] = "Successfully removed the reduce access at date from the user: " . $_POST['UserInfo'] . ".";
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();
}

// End of user input code snippets

if (isSet($refreshUsers) AND $refreshUsers){
	// TO-DO: Add code that should occur on a refresh
	unset($refreshUsers);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearAddUserSessions();
clearEditUserSessions();

// Display users list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = 'SELECT 		u.`userID`,
						u.`firstname`,
						u.`lastname`,
						u.`email`,
						a.`AccessName`,
						u.`displayname`,
						u.`bookingdescription`,
						(
							SELECT 		GROUP_CONCAT(CONCAT_WS(" in ", cp.`name`, CONCAT(c.`name`,".")) separator "\n")
							FROM 		`company` c
							INNER JOIN 	`employee` e
							ON 			e.`CompanyID` = c.`CompanyID`
							INNER JOIN 	`companyposition` cp
							ON 			cp.`PositionID` = e.`PositionID`
							WHERE  		e.`userID` = u.`userID`
							AND			c.`isActive` = 1
							GROUP BY 	e.`userID`
						)																					AS WorksFor,
						u.`create_time`								 										AS DateCreated,
						u.`isActive`,
						u.`lastActivity`							 										AS LastActive,
						u.`reduceAccessAtDate`																AS ReduceAccessAtDate
			FROM 		`user` u
			INNER JOIN	`accesslevel` a
			ON 			u.`AccessID` = a.`AccessID`
			ORDER BY 	u.`userID`
			DESC';
	$return = $pdo->query($sql);
	$result = $return->fetchAll(PDO::FETCH_ASSOC);
	if(isSet($result)){
		$rowNum = sizeOf($result);
	} else {
		$rowNum = 0;
	}

	//Close the connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching users from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach ($result as $row)
{
	$createdDateTime = $row['DateCreated'];
	$lastActiveDateTime = $row['LastActive'];
	$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayLastActiveDateTime = convertDatetimeToFormat($lastActiveDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$reduceAccessAtDate = $row['ReduceAccessAtDate'];
	if($reduceAccessAtDate != NULL AND $reduceAccessAtDate != ""){
		$displayReduceAccessAtDate = convertDatetimeToFormat($reduceAccessAtDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	} else {
		$displayReduceAccessAtDate = NULL;
	}
	
	$userinfo = $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'];
	
	// If user has activated the account
	if($row['isActive'] == 1){
		$users[] = array('id' => $row['userID'], 
						'firstname' => $row['firstname'],
						'lastname' => $row['lastname'],
						'email' => $row['email'],
						'accessname' => $row['AccessName'],
						'displayname' => $row['displayname'],
						'bookingdescription' => $row['bookingdescription'],
						'worksfor' => $row['WorksFor'],
						'datecreated' => $displayCreatedDateTime,			
						'lastactive' => $displayLastActiveDateTime,
						'UserInfo' => $userinfo,
						'reduceaccess' => $displayReduceAccessAtDate				
						);
						
		$email[] = $row['email'];				
	} elseif ($row['isActive'] == 0) {
		$inactiveusers[] = array('id' => $row['userID'], 
				'firstname' => $row['firstname'],
				'lastname' => $row['lastname'],
				'email' => $row['email'],
				'accessname' => $row['AccessName'],
				'datecreated' => $displayCreatedDateTime
				);
	}
	
}
if(isSet($email)){
	$_SESSION['UserEmailsToBeDisplayed'] = $email;
}

var_dump($_SESSION); // TO-DO: Remove after done testing
// Create the registered users list in HTML
include_once 'users.html.php';
?>