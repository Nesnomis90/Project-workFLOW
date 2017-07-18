<?php
// Constants used to salt passwords
require_once 'salts.inc.php';
require_once 'cookies.inc.php';

// Functions to salt and hash info
	// Function to salt and hash passwords
function hashPassword($rawPassword){
	$SaltedPassword = $rawPassword . PW_SALT;
	$HashedPassword = hash('sha256', $SaltedPassword);
	return $HashedPassword;
}
	// Function to salt and hash booking codes
function hashBookingCode($rawBookingCode){
	$SaltedBookingCode = $rawBookingCode . BC_SALT;
	$HashedBookingCode = hash('sha256', $SaltedBookingCode);
	return $HashedBookingCode;	
}
	// Function to salt and hash meeting room name into an IDCode
function hashMeetingRoomIDCode($rawCode){
	$saltedCode = $rawCode . CK_SALT;
	$hashedCode = hash('sha256', $saltedCode);
	return $hashedCode;
}

// Functions connected to user activity and access

// Checks if the cookie submitted is a valid meeting room
function databaseContainsMeetingRoomWithIDCode($name, $cookieIdCode){
	
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*),
						`idCode` 
				FROM 	`meetingroom`
				WHERE 	`name` = :name
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':name', $name);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error validating database from cookie.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}
	
	$row = $s->fetch();
	if ($row[0] > 0)
	{
		// The cookie had a valid meeting room name.
		// Check if the idCode is correct
		$hashedIDCode = hashMeetingRoomIDCode($row['idCode']);
		if ($hashedIDCode == $cookieIdCode)
		{
			return TRUE;
		}
		else
		{
			// idCode in cookie is not the valid idCode
			return FALSE;
		}	
	}
	else
	{
		// meeting room name in cookie does not match any rooms
		return FALSE;
	}
}

// Updates the timestamp of when the user was last active
function updateUserActivity()
{
	if(isSet($_SESSION['LoggedInUserID'])){
		// If a user logs in, or does something while logged in, we'll use this to update the database
		// to indicate when they last used the website
		try
		{
			include_once 'db.inc.php';
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`user`
					SET		`lastActivity` = CURRENT_TIMESTAMP()
					WHERE 	`userID` = :userID
					AND		`isActive` > 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':userID', $_SESSION['LoggedInUserID']);

			$s->execute();
			
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating user activity.';
			include_once 'error.html.php';
			$pdo = null;
			exit();
		}			
	}
}

// returns TRUE if user is logged in and updates the database with their last active timestamp
function userIsLoggedIn() 
{
	session_start(); // Do not remove this
	$isLoggedIn = checkIfUserIsLoggedIn();
	if($isLoggedIn === TRUE){
		updateUserActivity();
		return TRUE;
	} elseif($isLoggedIn === FALSE){
		return FALSE;
	}
}

// returns TRUE if user is logged in
function checkIfUserIsLoggedIn()
{
	// If user is trying to log in
	if (isSet($_POST['action']) and $_POST['action'] == 'login')
	{
		if(isSet($_POST['email']) AND $_POST['email'] != ""){
			// Remember email if it's filled in. Retyping an email is the most annoying thing in the world.
			$email = trim($_POST['email']);
			$_SESSION['loginEmailSubmitted'] = $email;
		}
		// Check if user has filled in the necessary information
		if (!isSet($_POST['email']) or $_POST['email'] == '' or
		!isSet($_POST['password']) or $_POST['password'] == '')
		{
			// User didn't fill in enough info
			// Save a custom error message for the user
			$_SESSION['loginError'] = 'Please fill in both fields';
			return FALSE;
		}
		// User has filled in both fields, check if login details are correct
			// Add our custom password salt and compare the finished hash to the database
		$SubmittedPassword = $_POST['password'];
		$password = hashPassword($SubmittedPassword);
		if (databaseContainsUser($email, $password))
		{
			// Correct log in info! Update the session data to know we're logged in
			if(!isSet($_SESSION['loggedIn'])){
				$_SESSION['loggedIn'] = TRUE;
			}
			if(!isSet($_SESSION['email'])){
				$_SESSION['email'] = $email;
			}
			if(!isSet($_SESSION['password'])){
				$_SESSION['password'] = $password;
			}
			if(!isSet($_SESSION['LoggedInUserID'])){
				$_SESSION['LoggedInUserID'] = $_SESSION['DatabaseContainsUserID'];
			}
			if(!isSet($_SESSION['LoggedInUserName'])){
				$_SESSION['LoggedInUserName'] = $_SESSION['DatabaseContainsUserName']; 
			}
			
			// We're not a local device if we can log in
			resetLocalDevice();
			
			unset($_SESSION['DatabaseContainsUserID']);
			unset($_SESSION['DatabaseContainsUserName']);
			unset($_SESSION['loginEmailSubmitted']);
			return TRUE;
		}
		else
		{
			// Wrong log in info.
			// Or user data has changed since last check
			// Meaning the login data isn't correct anymore
			// So we log out a user if previously logged in
			unset($_SESSION['loggedIn']);
			unset($_SESSION['email']);
			unset($_SESSION['password']);
			unset($_SESSION['LoggedInUserID']);
			unset($_SESSION['LoggedInUserName']);
			unset($_SESSION['LoggedInUserIsOwnerInTheseCompanies']);
			
			$_SESSION['loginError'] = 
			'The specified email address or password was incorrect.';
			return FALSE;
		}
	}
	// If user wants to log out
	if (isSet($_POST['action']) and $_POST['action'] == 'logout')
	{
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		unset($_SESSION['LoggedInUserID']);
		unset($_SESSION['LoggedInUserName']);
		unset($_SESSION['LoggedInUserIsOwnerInTheseCompanies']);
		unset($_SESSION['loginEmailSubmitted']);
		header('Location: ' . $_POST['goto']);
		exit();
	}
	// The user is in a session that was previously logged in
	// Let's check if the user STILL EXISTS in the database
	// i.e. if the login info is still correct
	// This causes an extra SQL QUERY every single time a page
	// is loaded again. But is more secure than just checking for the 
	// loggedIn = true session variable in the case that user info
	// has been altered while someone is already logged in with old data
	if (isSet($_SESSION['loggedIn']))
	{
		return databaseContainsUser($_SESSION['email'],
		$_SESSION['password']);
	}
	return FALSE;
}

// Function to check if the submitted user exists in our database
// AND has been activated
function databaseContainsUser($email, $password)
{
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*),
						`userID`,
						`firstname`,
						`lastname`
				FROM 	`user`
				WHERE 	email = :email 
				AND 	password = :password
				AND		`isActive` > 0
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->bindValue(':password', $password);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for user.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}
	
	$row = $s->fetch();
	// If we got a hit, then the user info was correct
	if ($row[0] > 0)
	{
		if(!isSet($_SESSION['LoggedInUserID'])){
			$_SESSION['DatabaseContainsUserID'] = $row['userID'];
		}
		
		if(!isSet($_SESSION['LoggedInUserName'])){
			$_SESSION['DatabaseContainsUserName'] = $row['lastname'] . ", " . $row['firstname'];
		}
		return TRUE;
	}
	else
	{
		unset($_SESSION['DatabaseContainsUserID']);
		unset($_SESSION['DatabaseContainsUserName']);	
		return FALSE;
	}
}

// Check if user has the specific access we're looking for
function userHasAccess($access)
{
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 		COUNT(*) 
				FROM 		`user` u
				INNER JOIN 	accesslevel a
				ON 			u.AccessID = a.AccessID
				WHERE 		u.email = :email 
				AND 		a.AccessName = :AccessName
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $_SESSION['email']);
		$s->bindValue(':AccessName', $access);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for user access.';
		include_once 'error.html.php';
		$pdo = connect_to_db();
		exit();
	}
	
	$row = $s->fetch();
	if ($row[0] > 0)
	{
		// User has the access we were looking for!
		return TRUE;
	}
	else
	{
		// User does NOT have the access needed.
		return FALSE;
	}
}

// Function to check if the email submitted already is being used
function databaseContainsEmail($email)
{
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`user`
				WHERE 	email = :email
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error validating email.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}
	
	$row = $s->fetch();
	// If we got a hit, then the email exists in our database
	if ($row[0] > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

// Function to check if the booking code submitted already is being used
function databaseContainsBookingCode($rawBookingCode)
{
	$hashedBookingCode = hashBookingCode($rawBookingCode);
	
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`user`
				WHERE 	`bookingCode` = :BookingCode
				AND		`isActive` > 0
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':BookingCode', $hashedBookingCode);
		$s->execute();
		
		$pdo = null;		
	}
	catch(PDOException $e)
	{
		$error = 'Error validating booking code.';
		include_once 'error.html.php';
		$pdo = null;
		exit();		
	}
	
	$row = $s->fetch();
	// If we got a hit, then the booking code exists in our database
	if ($row[0] > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}	
	
}

// Function to get user information based on the booking code submitted
// TO-DO: UNTESTED
function getUserInfoFromBookingCode($rawBookingCode)
{
	if(!databaseContainsBookingCode($rawBookingCode))
	{
		// The booking code we received does not exist in the database.
		// Can't retrieve any info then
		return FALSE;
	}
	
	// We know the code exists. Let's get the info of the person it belongs to
	$hashedBookingCode = hashBookingCode($rawBookingCode);
	
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 	`userID`						AS TheUserID,
						`email`							AS TheUserEmail,
						`firstName`						AS TheUserFirstname,
						`lastName`						AS TheUserLastname,
						`displayName`					AS TheUserDisplayName,
						`bookingDescription`			AS TheUserBookingDescription,
						`AccessID`						AS TheUserAccessID,
						(
							SELECT 	`AccessName`
							FROM 	`accesslevel`
							WHERE 	`AccessID` = TheUserAccessID
							LIMIT 	1
						)								AS TheUserAccessName
				FROM 	`user`
				WHERE 	`bookingCode` = :BookingCode
				AND		`isActive` > 0
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':BookingCode', $hashedBookingCode);
		$s->execute();
		
		$pdo = null;		
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching user info based on booking code.';
		include_once 'error.html.php';
		$pdo = null;
		exit();		
	}
	
	$row = $s->fetch();
	return $row;
}

// Function to "return" the raw booking code value to a user who has forgotten their own
// returns FALSE if not found.
function revealBookingCode($bookingCode){
	// Since the booking code has been salted and hashed, we have to repeat the process
	// We can only do this by "brute forcing", but we know the possible values we can have
	// For 7+ digits a for loop will take over 10 seconds to loop through all combinations
	// For 6 digits a for loop will take up to 1.4s loop through all combinations

	$maxNumber = 10 ** BOOKING_CODE_LENGTH;
	
	// Loop through possible combinations and hash them
	for($i=0; $i<$maxNumber; $i++){
		
		$viableHashedBookingCode = hashBookingCode($i);
		
		if($viableHashedBookingCode == $bookingCode){
			// We found a match!
			$actualBookingCode = $i;
			return $actualBookingCode;
		}
	}
	
	// Found no match. 
	return FALSE;
}

// Function to make sure user is Admin
function isUserAdmin(){
		// Check if user is logged in
	if (!userIsLoggedIn())
	{
		// Not logged in. Send user a login prompt.
		include_once 'login.html.php';
		exit();
	}
		// Check if user has Admin access
	if (!userHasAccess('Admin'))
	{
		// User is NOT ADMIN.
		$error = 'Only Admin may access this page.';
		include_once 'accessdenied.html.php';
		return false;
	}
	return true;
}

// Function to make sure only users can access this
function makeUserLogIn(){
		// Check if user is logged in
	if (!userIsLoggedIn())
	{
		// Not logged in. Send user a login prompt.
		include_once 'login.html.php';
		exit();
	}
	return true;
}

// Function to make sure user is In-House User
function isUserInHouseUser(){
	// Check if user is logged in
	if (!userIsLoggedIn())
	{
		// Not logged in. Send user a login prompt.
		include_once 'login.html.php';
		exit();
	}
		// Check if user has In-House User access
	if (!userHasAccess('In-House User'))
	{
		// User is NOT IN-HOUSE USER.
		$error = 'Only In-House Users can access this page.';
		include_once 'accessdenied.html.php';
		return false;
	}
	return true;
}

// Function to make sure user is the owner of the company
// TO-DO: UNTESTED!
function isUserCompanyOwner(){
	session_start();
	
	if(!isSet($_SESSION['LoggedInUserIsOwnerInTheseCompanies'])){
		// Check if user is a company owner
		try
		{
			$UserID = $_SESSION['LoggedInUserID'];
			
			include_once 'db.inc.php';
			$pdo = connect_to_db();
			$sql = "SELECT 		COUNT(*),
								c.`name`		AS CompanyName,
								c.`companyID`   AS CompanyID
					FROM 		`employee` e
					INNER JOIN 	`companyposition` cp
					ON			e.`PositionID` = cp.`PositionID`
					LEFT JOIN	`company` c
					ON			c.`companyID` = e.`companyID`
					WHERE 		e.`UserID` = :UserID 
					AND 		cp.`name` = 'Owner'";
			$s = $pdo->prepare($sql);
			$s->bindValue(':UserID', $UserID);
			$s->execute();
			
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error checking if user is company owner.' . $e->getMessage();
			include_once 'error.html.php';
			$pdo = null;
			exit();
		}
		 
		$result = $s->fetchAll();
		// If we got a hit, then the user is an owner for at least 1 company in our database
		if ($result[0] > 0)
		{
			foreach($result AS $row){
				$OwnerInCompanies[] = array (
												'CompanyName' => $row['CompanyName'],
												'CompanyID' => $row['CompanyID']
											);
			}
			
			$_SESSION['LoggedInUserIsOwnerInTheseCompanies'] = $OwnerInCompanies;
			
			return TRUE;
		}
		else
		{
			unset($_SESSION['LoggedInUserIsOwnerInTheseCompanies']);
			return FALSE;
		}
	} else {
		return TRUE;
	}
}
?>