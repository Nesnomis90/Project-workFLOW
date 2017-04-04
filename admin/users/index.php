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
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();	
}
 
// If admin wants to add a user to the database
// we load a new html form
if (isset($_GET['add']))
{	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		// Get name and IDs for access level
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

// if admin wants to edit user information
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Edit')
{
	// Get information from database again on the selected user
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'SELECT 	u.`userID`, 
						u.`firstname`, 
						u.`lastname`, 
						u.`email`,
						a.`AccessName`,
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
	$pageTitle = 'Edit User';
	$action = 'editform';
	$firstname = $row['firstname'];
	$lastname = $row['lastname'];
	$email = $row['email'];
	$accessname = $row['AccessName'];
	$id = $row['userID'];
	$displayname = $row['displayname'];
	$bookingdescription = $row['bookingdescription'];
	$button = 'Edit user';
	
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

// When admin has added the needed information and wants to add the user
if (isset($_GET['addform']))
{
	// Add the user to the database
	// TO-DO: Generate password, send password to email, salt/hash password
	// bind hashedpassword to :password
	try
	{
		//Generate activation code
		$activationcode = generateActivationCode();
		//TO-DO: Remove echo statement when testing is over
		echo 'activation code we generated on addform: ' . $activationcode . '<br />';
		
		//Generate password for user
		//TO-DO: ADD THE ACTUAL PASSWORD GENERATOR. JUST USING ACTIVATION CODE TO GET A 64 CHAR
		$hashedPassword = generateActivationCode();
		
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
	
	// Load user list webpage with new user
	header('Location: .');
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['editform']))
{
	try
	{
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
						GROUP_CONCAT(CONCAT_WS(' for ', cp.`name`, c.`name`) separator ', ') 	AS WorksFor,
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
			ORDER BY 	u.`AccessID`
			ASC";
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
					'lastactive' => $row['LastActive']
					);
}

// Create the registered users list in HTML
include_once 'users.html.php';
?>