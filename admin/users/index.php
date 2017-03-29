<?php 
// This is the index file for the USERS folder

// If admin wants to remove a user from the database
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

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
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';	
	try
	{
		// Get name and IDs for access level
		$pdo = connect_to_db();
		$sql = 'SELECT `accessID` ,`accessname` FROM `accesslevel`';
		$result = $pdo->query($sql);
		
		$accessname = '';
		
		//TO-DO SE GJENNOM OM TING ER RIKTIG HER
		// SKAL LAGE EN DROPDOWN LIST MED SELECT TIL HTML UT FRA INNHENTET ACCESSLEVEL INFO FRA DATABASE
		foreach($result as $row){
			$access[] = array(
								'accessID' => row['accessID'],
								'accessname' => row['accessname']
								);
			$accessname = $accessname . "<option> value=" . row['accessID'] . ">". row['accessname'] ."</option><br />";
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
	$accessname = 'Normal User';
	$accessID = 4;
	$id = '';
	$button = 'Add user';
	$reset = 'hidden';
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
	$accessname = '';
	$accessID = '';
	$id = '';
	$button = 'Edit user';
	$reset = 'reset';
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the user
if (isset($_GET['addform']))
{
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	// Add the user to the database
	// TO-DO: Generate password, send password to email, salt/hash password
	// bind hashedpassword to :password
	// TO-DO: Generate activation code, check if code already exists (has to be unique)
	// bind activationcode to :activationcode
	try
	{
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
		$s->bindValue(':email', $_POST['email']);
		$s->bindValue(':accessID', $_POST['accessID']);
		$s->bindValue(':password', $hashedPassword);
		$s->bindValue(':activationcode', $activationcode);
		$s->execute();
		
		// close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted user.';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Refresh webpage
	header('Location: .');
	exit();
}



// Display users list
include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
try
{
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
$users[] = array();
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