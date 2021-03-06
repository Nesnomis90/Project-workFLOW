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
	if($row[0] > 0){
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
function updateUserActivity(){
	if(!empty($_SESSION['LoggedInUserID'])){
		// If a user logs in, or does something while logged in, we'll use this to update the database
		// to indicate when they last used the website
		// If a user logs in, this also means they didn't forget their password, so we can reset any request for it
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
function userIsLoggedIn(){
	$isLoggedIn = checkIfUserIsLoggedIn();
	if($isLoggedIn === TRUE){
		updateUserActivity();
		return TRUE;
	} else {
		return FALSE;
	}
}

// returns TRUE if user is logged in
function checkIfUserIsLoggedIn(){

	// If user is trying to log in
	if(isSet($_POST['action']) and $_POST['action'] == 'login'){

		if(!empty($_POST['email'])){
			// Remember email if it's filled in. Retyping an email is the most annoying thing in the world.
			$email = trim($_POST['email']);
			$_SESSION['loginEmailSubmitted'] = $email;
		}

		// Check if user has filled in the necessary information
		if(empty($_POST['email']) OR empty($_POST['password'])){
			// User didn't fill in enough info
			// Save a custom error message for the user
			$_SESSION['loginError'] = 'Please fill in both fields';
			return FALSE;
		}

		if(!validateUserEmail($email)){
			$_SESSION['loginError'] = 'Email submitted is not a valid email.';
			return FALSE;
		}

		// Check if the email submitted belongs to an account that has been blocked from being able to log in
		if(isUserBlockedFromLogin($email)){
			$_SESSION['loginError'] = 	"The account you are trying to access has been locked due to too many incorrect login attempts." .
										"\nTo activate it, follow the instructions sent to your email, or contact an admin.";
			return FALSE;
		}

		// Check if this session is allowed to do any login attempts at the moment
		if(isSet($_SESSION['wrongLoginAttempts'], $_SESSION['loginBlocked'])){
			$dateTimeNow = getDatetimeNow();
			$dateTimeBlocked = end($_SESSION['wrongLoginAttempts']);
			$timoutInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($dateTimeBlocked, $dateTimeNow);

			if($timoutInMinutes >= WRONG_LOGIN_GUESS_TIMEOUT_IN_MINUTES){
				unset($_SESSION['wrongLoginAttempts']);
				unset($_SESSION['loginBlocked']);
			} else {
				$timeoutLeft = WRONG_LOGIN_GUESS_TIMEOUT_IN_MINUTES - $timoutInMinutes;
			}
		}

		if(isSet($_SESSION['loginBlocked'])){
			if($timeoutLeft > 0){
				$_SESSION['loginError'] = "You are not allowed to attempt a login for another $timeoutLeft minute(s).";
			} else {
				$_SESSION['loginError'] = "You are not allowed to attempt a login for another minute.";
			}
			return FALSE;
		}

		// User has filled in both fields, check if login details are correct
			// Add our custom password salt and compare the finished hash to the database
		$submittedPassword = $_POST['password'];
		$password = hashPassword($submittedPassword);

		$userInfo = databaseContainsUser($email, $password);
		if($userInfo === TRUE){
			// Correct log in info! Update the session data to know we're logged in
			$_SESSION['loggedIn'] = TRUE;
			$_SESSION['email'] = $email;
			$_SESSION['password'] = $password;
			$_SESSION['LoggedInUserID'] = $_SESSION['DatabaseContainsUserID'];
			$_SESSION['LoggedInUserName'] = $_SESSION['DatabaseContainsUserName'];

			// We're not a local device if we can log in
			resetLocalDevice();

			unset($_SESSION['wrongLoginAttempts']);
			unset($_SESSION['loginBlocked']);
			unset($_SESSION['DatabaseContainsUserID']);
			unset($_SESSION['DatabaseContainsUserName']);
			unset($_SESSION['loginEmailSubmitted']);

			return TRUE;
		} else {
			// Wrong log in info.
			unset($_SESSION['loggedIn']);
			unset($_SESSION['email']);
			unset($_SESSION['password']);
			unset($_SESSION['LoggedInUserID']);
			unset($_SESSION['LoggedInUserName']);

			// Track # of wrong login attempts and limit login if too high.
			$dateTimeNow = getDatetimeNow();
			$_SESSION['wrongLoginAttempts'][] = $dateTimeNow;

			$blocked = FALSE;

			if(sizeOf($_SESSION['wrongLoginAttempts']) >= MAXIMUM_WRONG_LOGIN_GUESSES){
				$_SESSION['loginBlocked'] = TRUE;

				// Add to the user's account (from the email used) that someone tried to login to their account and failed
				increaseLoginTimeouts($email);

				// Check how many timeouts we have now
				$timeoutAmount = trackLoginTimeouts($email);

				// Block account from logging in, if too many timeouts
				// Also sends email to user about how to activate account again
				$maxTimeouts = MAXIMUM_WRONG_LOGIN_TIMEOUTS;
				if($timeoutAmount >= $maxTimeouts){
					$blocked = TRUE;

					// Generate new activation code
					$activationCode = generateActivationCode();

					blockUserLogin($email, $activationCode);
					sendEmailAboutLoginBeingBlocked($email, $activationCode);
				}
			}

			$timeoutDurationInMinutes = WRONG_LOGIN_GUESS_TIMEOUT_IN_MINUTES;

			if($blocked){
				$_SESSION['loginError'] .= "\n\nYou are also unable to attempt any log in for $timeoutDurationInMinutes minutes.";
			} else {
				// Calculate remaining login attempts before receiving a timeout
				$attemptsSoFar = sizeOf($_SESSION['wrongLoginAttempts']);
				$attemptsRemaining = MAXIMUM_WRONG_LOGIN_GUESSES - $attemptsSoFar;

				if($attemptsRemaining == 2){
					$_SESSION['loginError'] = "The specified email address or password was incorrect.\nYou have 2 attempts left to insert the correct login information.";
				} elseif($attemptsRemaining == 1){
					$_SESSION['loginError'] = "The specified email address or password was incorrect.\nYou have 1 attempt left to insert the correct login information.";
				} elseif($attemptsRemaining == 0){
					$_SESSION['loginError'] = "The specified email address or password was incorrect.\nYou are now unable to attempt any log in for $timeoutDurationInMinutes minutes.";
				} else {
					$_SESSION['loginError'] = "The specified email address or password was incorrect.";
				}
			}

			return FALSE;
		}
	}

	// If user has forgotten password
	if(isSet($_POST['action']) AND $_POST['action'] == "Forgotten Password?"){
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/forgottenpassword.html.php';
		exit();
	}

	// If user has set their new password
	if(isSet($_POST['action']) AND $_POST['action'] == "requestPassword"){
		if(isSet($_POST['email']) AND $_POST['email'] != ""){
			// Remember email if it's filled in. Retyping an email is the most annoying thing in the world.
			$email = trim($_POST['email']);
			$_SESSION['forgottenPasswordEmailSubmitted'] = $email;

			if(validateUserEmail($email)){
				if(databaseContainsEmail($email)){
					// Email submitted belongs to a user. Let's genereate a reset password code and update the user
					try
					{
						// Generate reset password code
						$resetPasswordCode = generateResetPasswordCode();

						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

						$pdo = connect_to_db();
						$sql = 'UPDATE	`user`
								SET		`resetPasswordCode` = :resetPasswordCode
								WHERE	`email` = :email';
						$s = $pdo->prepare($sql);
						$s->bindValue(':resetPasswordCode', $resetPasswordCode);
						$s->bindValue(':email', $email);
						$s->execute();

						//Close the connection
						$pdo = null;
					}
					catch (PDOException $e)
					{
						$error = 'Error connecting reset password code to user: ' . $e->getMessage();
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
						$pdo = null;
						exit();
					}

					// Let's send an email to the user with the reset password link

					$emailSubject = "New Password Request!";

					$url = $_SERVER['HTTP_HOST'] . "/user/?resetpassword=" . $resetPasswordCode;

					$emailMessage = 
					"Someone has requested a new password for your account!\n" .
					"If you did not request this, just ignore this email.\n\n" . 
					"To set a new password for your account go to the link below.\n" . 
					"Link: " . $url;

					$mailResult = sendEmail($email, $emailSubject, $emailMessage);

					$_SESSION['forgottenPasswordError'] = "User found and reset link sent to email!";

					if(!$mailResult){
						$_SESSION['forgottenPasswordError'] .= "\n\n[WARNING] System failed to send Email.";

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

						$_SESSION['forgottenPasswordError'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
					}

					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/forgottenpassword.html.php';
					exit();
				} else {
					$_SESSION['forgottenPasswordError'] = "Email submitted does not belong to a user.";
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/forgottenpassword.html.php';
					exit();
				}
			} else {
				$_SESSION['forgottenPasswordError'] = "Email submitted is not a valid email.";
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/forgottenpassword.html.php';
				exit();
			}
		} else {
			$_SESSION['forgottenPasswordError'] = "Please fill in your email.";
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/forgottenpassword.html.php';
			exit();
		}
	}

	// If user wants to log out
	/* See navcheck.inc.php and adminnavcheck.inc.php
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		unset($_SESSION['LoggedInUserID']);
		unset($_SESSION['LoggedInUserName']);
		unset($_SESSION['loginEmailSubmitted']);
	*/

	// The user is in a session that was previously logged in
	// Let's check if the user STILL EXISTS in the database
	// i.e. if the login info is still correct
	// This causes an extra SQL QUERY every single time a page
	// is loaded again. But is more secure than just checking for the 
	// loggedIn = true session variable in the case that user info
	// has been altered while someone is already logged in with old data
	if(isSet($_SESSION['loggedIn'])){
		$userExists = databaseContainsUser($_SESSION['email'], $_SESSION['password']);

		unset($_SESSION['DatabaseContainsUserID']);
		unset($_SESSION['DatabaseContainsUserName']);

		return $userExists;
	}

	return FALSE;
}

// Function to check if the submitted user exists in our database
// AND has been activated
function databaseContainsUser($email, $password){
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
	if($row[0] > 0){

		$_SESSION['DatabaseContainsUserID'] = $row['userID'];
		$_SESSION['DatabaseContainsUserName'] = $row['lastname'] . ", " . $row['firstname'];

		return TRUE;
	} else {
		unset($_SESSION['DatabaseContainsUserID']);
		unset($_SESSION['DatabaseContainsUserName']);

		return FALSE;
	}
}

// Check if user has the specific access we're looking for
function userHasAccess($access){
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
	if($row[0] > 0){
		// User has the access we were looking for!
		return TRUE;
	} else {
		// User does NOT have the access needed.
		return FALSE;
	}
}

// Function to check if the email submitted already is being used
function databaseContainsEmail($email){
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
	if($row[0] > 0){
		return TRUE;
	} else {
		return FALSE;
	}
}

// Function used to check if email belongs to an account that has been blocked from too many incorrect login attempts
function isUserBlockedFromLogin($email){
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`user`
				WHERE 	`email` = :email
				AND		`loginBlocked` = 1
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->execute();

		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error validating account status.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}

	$row = $s->fetch();
	// If we got a hit, then the user is blocked from being able to log in
	if($row[0] > 0){
		return TRUE;
	} else {
		return FALSE;
	}
}

// Function to check if the booking code submitted already is being used
function databaseContainsBookingCode($rawBookingCode){
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
	if($row[0] > 0){
		return TRUE;
	}
	else
	{
		return FALSE;
	}
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
	if(!userIsLoggedIn()){
		// Not logged in. Send user a login prompt.
		include_once 'login.html.php';
		exit();
	}
		// Check if user has Admin access
	if(!userHasAccess('Admin')){
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
	if(!userIsLoggedIn()){
		// Not logged in. Send user a login prompt.
		include_once 'login.html.php';
		exit();
	}
	return true;
}

// Function to keep track of the number of timeouts the account has received and block it from being able to log in if too high
function trackLoginTimeouts($email){
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT	`timeoutAmount`
				FROM	`user`
				WHERE	`email` = :email
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->execute();
		$amount = $s->fetchColumn();

		$pdo = null;

		return $amount;
	}
	catch(PDOException $e)
	{
		$error = 'Error checking user timeout amounts.';
		include_once 'error.html.php';
		$pdo = null;
		exit();	
	}	
}

// Function to increase the amount of times the account has received a login timeout
function increaseLoginTimeouts($email){
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'UPDATE	`user`
				SET		`timeoutAmount` = (`timeoutAmount` + 1)
				WHERE	`email` = :email';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->execute();

		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error updating user timeout amounts.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to make sure the user can't log in if the account has received too many login timeouts
function blockUserLogin($email, $activationCode){
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'UPDATE	`user`
				SET		`loginBlocked` = 1,
						`activationCode` = :activationCode
				WHERE	`email` = :email
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->bindValue(':activationCode', $activationCode);
		$s->execute();

		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error blocking user login.';
		include_once 'error.html.php';
		$pdo = null;
		exit();
	}
}

// The email we send to the user when their account gets blocked from logging in
function sendEmailAboutLoginBeingBlocked($email, $activationCode){

	$emailSubject = "Your Account Has Been Blocked!";

	$url = $_SERVER['HTTP_HOST'] . "/user/?activateaccount=" . $activationCode;

	$emailMessage = 
	"Someone has tried to log into your account and failed too many times!\n" .
	"Therefore your account has been blocked for further login attempts for your account's safety.\n\n" . 
	"You can activate your account again by going to the link below.\n" . 
	"Link: " . $url;

	$mailResult = sendEmail($email, $emailSubject, $emailMessage);

	$_SESSION['loginError'] = "The account you tried to access has been blocked from further login attempts.";
	
	if(!$mailResult){
		$_SESSION['loginError'] .= "\n\n[WARNING] System failed to send Email.";

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

		$_SESSION['loginError'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";		
	}
}
?>