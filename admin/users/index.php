<?php 
// This is the index file for the USERS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// If admin wants to remove a user from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
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
		session_start();

		// Save a description with information about the user that was removed
		
		$description = "N/A";
		if(isset($_POST['UserInfo'])){
			$description = 'The User: ' . $_POST['UserInfo'] . 
			' was deleted by: ' . $_SESSION['LoggedInUserName'];
		} else {
			$description = 'An unactivated User was deleted by: ' . $_SESSION['LoggedInUserName'];
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Account Removed'
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
if (isset($_GET['add']) OR (isset($_SESSION['refreshUserAddform']) AND $_SESSION['refreshUserAddform']))
{	
	// Check if the call was /?add/ or a forced refresh
	if(isset($_SESSION['refreshUserAddform']) AND $_SESSION['refreshUserAddform']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshUserAddform']);
	}
	
	// Get name and IDs for access level
	// Admin needs to give a new user a specific access.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 	`accessID`,
						`accessname` 
				FROM 	`accesslevel`';
		$result = $pdo->query($sql);
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$access[] = array(
								'accessID' => $row['accessID'],
								'accessname' => $row['accessname']
								);
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
	
	//Generate password for user
	$generatedPassword = generateUserPassword(6);	
	$hashedPassword = hashPassword($generatedPassword);
	
	// Set values to be displayed in HTML
	$pageTitle = 'New User';
	$action = 'addform';
	$firstname = '';
	$lastname = '';
	$email = '';
	$id = '';
	$displayname = '';
	$bookingdescription = '';
	$button = 'Add user';
	
	// If we refreshed and want to keep the same values
	if(isset($_SESSION['AddNewUserFirstname'])){
		$firstname = $_SESSION['AddNewUserFirstname'];
		unset($_SESSION['AddNewUserFirstname']);		
	}
	if(isset($_SESSION['AddNewUserLastname'])){
		$lastname = $_SESSION['AddNewUserLastname'];
		unset($_SESSION['AddNewUserLastname']);		
	}	
	if(isset($_SESSION['AddNewUserEmail'])){
		$email = $_SESSION['AddNewUserEmail'];
		unset($_SESSION['AddNewUserEmail']);		
	}	
	if(isset($_SESSION['AddNewUserSelectedAccess'])){
		$accessID = $_SESSION['AddNewUserSelectedAccess'];
		unset($_SESSION['AddNewUserSelectedAccess']);		
	}
	
	// We want a reset all fields button while adding a new user
	$reset = 'reset';
	
	// We don't need to see display name and booking description when adding a new user
	// style=display:block to show, style=display:none to hide
	$displaynameStyle = 'none';
	$bookingdescriptionStyle = 'none';
	
	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the user
if (isset($_GET['addform']))
{
	
	// Add the user to the database
	// TO-DO: Send password and activation link to email
	
	// Check if the submitted email has already been used
	$email = $_POST['email'];
	if (databaseContainsEmail($email)){
		// The email has been used before. So we can't create a new user with this info.
		
		$_SESSION['refreshUserAddform'] = TRUE;
		$_SESSION['AddNewUserError'] = "The submitted email already exists in the database.";
		
		// Let's remember the info the admin submitted
		$_SESSION['AddNewUserFirstname'] = $_POST['firstname'];
		$_SESSION['AddNewUserLastname'] = $_POST['lastname'];
		$_SESSION['AddNewUserEmail'] = $_POST['email'];
		$_SESSION['AddNewUserSelectedAccess'] = $_POST['accessID'];		
		
		
	} else {
		// The email has NOT been used before, so we can create the new user!
		try
		{
			//Generate activation code
			$activationcode = generateActivationCode();
			
			// Hash the user generated password
			$hashedPassword = $_POST['hashedPassword'];	
			
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
			$s->bindValue(':firstname', $_POST['firstname']);
			$s->bindValue(':lastname', $_POST['lastname']);		
			$s->bindValue(':accessID', $_POST['accessID']);
			$s->bindValue(':password', $hashedPassword);
			$s->bindValue(':activationcode', $activationcode);
			$s->bindValue(':email', $_POST['email']);
			$s->execute();
			
			session_start();
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
			session_start();

			// Save a description with information about the user that was added
			
			$description = "N/A";
			$userinfo = $_POST['lastname'] . ', ' . $_POST['firstname'] . ' - ' . $_POST['email'];
			if(isset($_SESSION['LoggedInUserName'])){
				$description = "An account for: " . $userinfo . " was created by: " . $_SESSION['LoggedInUserName'];
			} else {
				$description = "An account was created for " . $userinfo;
			}
			$lastUserID = $_SESSION['lastUserID'];
			unset($_SESSION['lastUserID']);
			
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT `actionID` 
													FROM `logaction`
													WHERE `name` = 'Account Created'
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
	}
	
	// Load user list webpage with new user
	header('Location: .');
	exit();
}

// if admin wants to edit user information
// we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR 
(isset($_SESSION['refreshEditform'])) AND $_SESSION['refreshEditform'])
{
		// Check if the call was edit button or a forced refresh
	if(isset($_SESSION['refreshEditform']) AND $_SESSION['refreshEditform']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshEditform']);
	
		// Set the information back to what it was before the refresh
		$firstname = $_SESSION['EditUserChangedFirstname'];
		unset($_SESSION['EditUserChangedFirstname']);
		$lastname = $_SESSION['EditUserChangedLastname'];
		unset($_SESSION['EditUserChangedLastname']);
		$email = $_SESSION['EditUserChangedEmail'];
		unset($_SESSION['EditUserChangedEmail']);
		$accessID = $_SESSION['EditUserChangedAccessID'];
		unset($_SESSION['EditUserChangedAccessID']);
		$id = $_SESSION['TheUserID'];
		unset($_SESSION['TheUserID']);
		$displayname = $_SESSION['EditUserChangedDisplayname'];
		unset($_SESSION['EditUserChangedDisplayname']);
		$bookingdescription = $_SESSION['EditUserChangedBookingDescription'];
		unset($_SESSION['EditUserChangedBookingDescription']);
		$access = $_SESSION['EditUserAccessList'];
		unset($_SESSION['EditUserAccessList']);
		
	} else {
		
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
							u.`displayname`,
							u.`bookingdescription`
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
			$result = $pdo->query($sql);
			
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
		$id = $row['userID'];
		$displayname = $row['displayname'];
		$bookingdescription = $row['bookingdescription'];
	}
	
	// Set the correct information
	$pageTitle = 'Edit User';
	$action = 'editform';
	$button = 'Edit user';
	$password = '';
	$confirmpassword = '';
	
	// Remember the values we retrieved.
	$_SESSION['EditUserOldFirstname'] = $firstname;
	$_SESSION['EditUserOldLastname'] = $lastname;
	$_SESSION['EditUserOldEmail'] = $email;
	$_SESSION['EditUserOldAccessID'] = $accessID;
	$_SESSION['EditUserOldDisplayname'] = $displayname;
	$_SESSION['EditUserOldBookingDescription'] = $bookingdescription;
	$_SESSION['TheUserID'] = $id;
	$_SESSION['EditUserAccessList'] = $access;
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	// Want to see display name and booking description while editing
	// style=display:block to show, style=display:none to hide
	$displaynameStyle = 'block';
	$bookingdescriptionStyle = 'block';
	
	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['editform']))
{
	// Check if any values were actually changed
	$NumberOfChanges = 0;
	
	// Check if user is trying to set a new password
	// And if so, check if both fields are filled in and match each other
	if($_POST['password'] == $_POST['confirmpassword']){
		if($_POST['password'] != ''){
			$NumberOfChanges++;	
		}

	} else {
		$_SESSION['AddNewUserError'] = "Password and Confirm Password did not match.";
		$_SESSION['refreshEditform'] = TRUE;
		
		// Remember if admin made any other changes
		$_SESSION['EditUserChangedFirstname'] = $_POST['firstname'];
		$_SESSION['EditUserChangedLastname'] = $_POST['lastname'];
		$_SESSION['EditUserChangedEmail'] = $_POST['email'];
		$_SESSION['EditUserChangedAccessID'] = $_POST['accessID'];
		$_SESSION['EditUserChangedDisplayname'] = $_POST['displayname'];
		$_SESSION['EditUserChangedBookingDescription'] = $_POST['bookingdescription'];
		
		// Load user list webpage with updated database
		header('Location: .');
		exit();
	}
	
		// Check against the values we retrieved before loading the page
	if ( isset($_SESSION['EditUserOldFirstname']) AND 
	$_POST['firstname'] != $_SESSION['EditUserOldFirstname'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldFirstname']);
	}
	if ( isset($_SESSION['EditUserOldLastname']) AND 
	$_POST['lastname'] != $_SESSION['EditUserOldLastname'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldLastname']);
	}
	if ( isset($_SESSION['EditUserOldEmail']) AND 
	$_POST['email'] != $_SESSION['EditUserOldEmail'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldEmail']);
	}
	if ( isset($_SESSION['EditUserOldAccessID']) AND 
	$_POST['accessID'] != $_SESSION['EditUserOldAccessID'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldAccessID']);
	}
	if ( isset($_SESSION['EditUserOldDisplayname']) AND 
	$_POST['displayname'] != $_SESSION['EditUserOldDisplayname'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldDisplayname']);
	}	
	if ( isset($_SESSION['EditUserOldBookingDescription']) AND 
	$_POST['bookingdescription'] != $_SESSION['EditUserOldBookingDescription'])
	{
		$NumberOfChanges++;
		unset($_SESSION['EditUserOldBookingDescription']);
	}
	

	if ($NumberOfChanges == 0){
		
		$_SESSION['UserManagementFeedbackMessage'] = "No changes were made.";
		
		// Load user list webpage with updated database
		header('Location: .');
		exit();
	}
	
	// Don't need to remember the access list anymore
	unset($_SESSION['EditUserAccessList']);


	// We actually have something to update!	
	try
	{
		if ($_POST['password'] != ''){
			// Update user info (new password)
			$newPassword = $_POST['password'];
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
			$s->bindValue(':firstname', $_POST['firstname']);
			$s->bindValue(':lastname', $_POST['lastname']);
			$s->bindValue(':email', $_POST['email']);
			$s->bindValue(':password', $hashedNewPassword);
			$s->bindValue(':accessID', $_POST['accessID']);
			$s->bindValue(':displayname', $_POST['displayname']);
			$s->bindValue(':bookingdescription', $_POST['bookingdescription']);
			$s->execute();			
		} else {
			// Update user info (no new password)
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'UPDATE `user` SET
							firstname = :firstname,
							lastname = :lastname,
							email = :email,
							accessID = :accessID,
							displayname = :displayname,
							bookingdescription = :bookingdescription
					WHERE 	userID = :id';
					
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->bindValue(':firstname', $_POST['firstname']);
			$s->bindValue(':lastname', $_POST['lastname']);
			$s->bindValue(':email', $_POST['email']);
			$s->bindValue(':accessID', $_POST['accessID']);
			$s->bindValue(':displayname', $_POST['displayname']);
			$s->bindValue(':bookingdescription', $_POST['bookingdescription']);
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
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();
}

// Display users list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		u.`userID`, 
						u.`firstname`, 
						u.`lastname`, 
						u.`email`,
						a.`AccessName`,
						u.`displayname`,
						u.`bookingdescription`,
						GROUP_CONCAT(CONCAT_WS(' in ', cp.`name`, c.`name`) separator ', ') 	AS WorksFor,
						DATE_FORMAT(u.`create_time`, '%d %b %Y %T') 							AS DateCreated,
						u.`isActive`,
						DATE_FORMAT(u.`lastActivity`, '%d %b %Y %T') 							AS LastActive
			FROM 		`user` u 
			LEFT JOIN 	`employee` e 
			ON 			e.UserID = u.userID 
			LEFT JOIN 	`company` c 
			ON 			e.CompanyID = c.CompanyID 
			LEFT JOIN 	`companyposition` cp 
			ON 			cp.PositionID = e.PositionID
			LEFT JOIN 	`accesslevel` a
			ON 			u.AccessID = a.AccessID
			GROUP BY 	u.`userID`
			ORDER BY 	u.`userID`
			DESC";
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();

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
	// If user has activated the account
	if($row['isActive'] == 1){
		$userinfo = $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'];
		$users[] = array('id' => $row['userID'], 
						'firstname' => $row['firstname'],
						'lastname' => $row['lastname'],
						'email' => $row['email'],
						'accessname' => $row['AccessName'],
						'displayname' => $row['displayname'],
						'bookingdescription' => $row['bookingdescription'],
						'worksfor' => $row['WorksFor'],
						'datecreated' => $row['DateCreated'],			
						'lastactive' => $row['LastActive'],
						'UserInfo' => $userinfo
						);
	} elseif ($row['isActive'] == 0) {
		$inactiveusers[] = array('id' => $row['userID'], 
				'firstname' => $row['firstname'],
				'lastname' => $row['lastname'],
				'email' => $row['email'],
				'accessname' => $row['AccessName'],
				'datecreated' => $row['DateCreated']
				);
	}
}

// Create the registered users list in HTML
include_once 'users.html.php';
?>