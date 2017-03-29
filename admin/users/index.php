<?php 
// This is the index file for the USERS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

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
		$sql = 'DELETE FROM `user` WHERE `userID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting user to delete.';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// Refresh webpage
	header('Location: .');
	exit();	
}
 
// If admin wants to add a user to the database
// we load a new html form
if (isset($_GET['add']))
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';	
	try
	{
		// Get name and IDs for access level
		$pdo = connect_to_db();
		$sql = 'SELECT `accessID` ,`accessname` FROM `accesslevel`';
		$result = $pdo->query($sql);
		
		$accessnames = '';
		
		//TO-DO SE GJENNOM OM TING ER RIKTIG HER
		// SKAL LAGE EN DROPDOWN LIST MED SELECT TIL HTML UT FRA INNHENTET ACCESSLEVEL INFO FRA DATABASE
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
		$error = 'Error getting access level info from database.';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	$pageTitle = 'New User';
	$action = 'addform';
	$firstname = '';
	$lastname = '';
	$email = '';
	$id = '';
	$displayname = '';
	$bookingdescription = '';
	$button = 'Add user';
	
	// We want a reset all fields button while adding a new user
	$reset = 'reset';
	// We don't need to see display name and booking description when adding a new user
	$displaynametype = 'hidden';
	$bookingdescriptiontype = 'hidden';
	include 'form.html.php';
	exit();
}

// if admin wants to edit user information
// we load a new html form
// TO-DO: ACTUALLY DO THIS!
if (isset($_GET['edit']))
{
	$pageTitle = 'Edit User';
	// Get values from database
	$action = 'editform';
	$firstname = '';
	$lastname = '';
	$email = '';
	$id = '';
	$button = 'Edit user';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	// Want to see display name and booking description while editing
	$displaynametype = 'text';
	$bookingdescriptiontype = 'text';
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the user
if (isset($_GET['addform']))
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	// Add the user to the database
	// TO-DO: Generate password, send password to email, salt/hash password
	// bind hashedpassword to :password
	// TO-DO: Generate activation code, check if code already exists (has to be unique)
	// bind activationcode to :activationcode
	try
	{
		//Generate activation code
		$activationcode = generateActivationCode();
		echo 'activation code we generated on addform: ' . $activationcode . '<br />';
		
		//Generate password for user
		//TO-DO: ADD THE ACTUAL PASSWORD GENERATOR. JUST USING ACTIVATION CODE TO GET A 64 CHAR
		$hashedPassword = generateActivationCode();
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `user` SET
		`firstname` = :firstname,
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
		
		// close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted user: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Refresh webpage
	header('Location: .');
	exit();
}



// Display users list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 	u.`userID`, 
					u.`firstname`, 
					u.`lastname`, 
					u.`email`,
					a.`AccessName`,
					u.`displayname`,
					u.`bookingdescription`,
                    GROUP_CONCAT(CONCAT_WS(' for ', cp.`name`, c.`name`) separator ', ') AS WorksFor,
					DATE_FORMAT(u.`create_time`, '%d %b %Y %T') AS DateCreated,
					u.`isActive`,
					DATE_FORMAT(u.`lastActivity`, '%d %b %Y %T') AS LastActive
					FROM `user` u 
					LEFT JOIN `employee` e 
					ON e.UserID = u.userID 
					LEFT JOIN `company` c 
					ON e.CompanyID = c.CompanyID 
					LEFT JOIN `companyposition` cp 
					ON cp.PositionID = e.PositionID
					LEFT JOIN `accesslevel` a
					ON u.AccessID = a.AccessID
					GROUP BY u.`userID`
                    ORDER BY u.`AccessID`
                    ASC"
					;
	$result = $pdo->query($sql);

	//Close connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching users from the database!';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}
// Define the users variable to avoid errors if it's empty
foreach ($result as $row)
{
	$users[] = array('id' => $row['userID'], 
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname'],
					'email' => $row['email'],
					'accessname' => $row['AccessName'],
					'displayname' => $row['displayname'],
					'bookingdescription' => $row['bookingdescription'],
					'worksfor' => $row['WorksFor'],
					'datecreated' => $row['DateCreated'],
					'isActive' => $row['isActive'],					
					'lastactive' => $row['LastActive'],
					);
}

// Create the registered users list in HTML
include_once 'users.html.php';
?>