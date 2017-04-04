<?php 
// This is the index file for the EMPLOYEES folder

//TO-DO: This needs fixes to ADD and EDIT!

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// If admin wants to remove an employee from the selected company
if(isset($_POST['action']) AND $_POST['action'] == 'Remove'){
	// Remove employee connection in database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `employee` 
				WHERE 		`companyID` = :CompanyID
				AND 		`userID` = :UserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error deleting employee connection: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}


// If admin wants to add a company to the database
// we load a new html form
//	TO-DO: NEED A WAY TO BE ABLE TO SELECT THE USER AND THE COMPANY WE WANT TO MATCH
if (isset($_GET['add']))
{	
	// Update company position for the employee connection in database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get name and IDs for company position
		$pdo = connect_to_db();
		$sql = 'SELECT 	`positionID`,
						`name` 			AS CompanyPositionName,
						`description`	AS CompanyPositionDescription
				FROM 	`companyposition`';
		$result = $pdo->query($sql);
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$companyposition[] = array(
									'positionID' => $row['positionID'],
									'CompanyPositionName' => $row['CompanyPositionName'],
									'CompanyPositionDescription' => $row['CompanyPositionDescription']
									);
		}

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company position: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Set values to be displayed in HTML
	$pageTitle = 'New Employee';
	$action = 'addform';
	$CompanyName = '';
	$CompanyID = '';
	$UserID = '';
	$companypositionname = '';
	$UserIdentifier = '';
	$button = 'Add employee';
	
	// We want a reset all fields button while adding a new company
	$reset = 'reset';
	
	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// if admin wants to set the date to remove for a company
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Edit')
{
	// Get information from database again on the selected company	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get company information
		$pdo = connect_to_db();
		$sql = ''; // TO-DO: FIX.
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
						
		//Close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	// Create an array with the row information we retrieved
	$row = $s->fetch();
		
	// Set the correct information
	$pageTitle = 'Edit Employee';
	$action = 'editform';
	$CompanyName = $row['CompanyName'];
	$id = $row['CompanyID'];
	$button = 'Edit employee';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	
	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the company
if (isset($_GET['addform']))
{
	// Add the company to the database
	try
	{	
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `employee` 
				SET			`companyID` = :CompanyID,
							`userID` = :UserID,
							`PositionID` = :PositionID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':PositionID', $_POST['PositionID']);		
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted company to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Load companies list webpage with new company
	header('Location: .');
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['editform']))
{
	// Update selected company by inserted the date to remove	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$sql = 'UPDATE 	`employee` 
				SET		`PositionID` = :PositionID
				WHERE 	`companyID` = :CompanyID
				AND 	`userID` = :UserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->execute(); 
				
		//close connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error changing company position in employee information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	// Load employee list webpage with updated database
	header('Location: .');
	exit();
}

// Display employee list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	
	// This SQL is for an employee list for the specific company	
	
/*	$sql = 'SELECT 	u.`userID`					AS UsrID,
					c.`companyID`				AS CompanyID,
					c.`name`					AS CompanyName,
					u.`firstName`, 
					u.`lastName`,
					u.`email`,
					cp.`name`					AS PositionName, 
					e.`startDateTime`,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = :id
						AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
					) 							AS MonthlyBookingTimeUsed,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = :id
					) 							AS TotalBookingTimeUsed							
			FROM 	`company` c 
			JOIN 	`employee` e
			ON 		e.CompanyID = c.CompanyID 
			JOIN 	`companyposition` cp 
			ON 		cp.PositionID = e.PositionID
			JOIN 	`user` u 
			ON 		u.userID = e.UserID 
			WHERE 	c.`companyID` = :id'; */

	$sql = 'SELECT 	u.`userID`					AS UsrID,
					c.`companyID`				AS TheCompanyID,
					c.`name`					AS CompanyName,
					u.`firstName`, 
					u.`lastName`,
					u.`email`,
					cp.`name`					AS PositionName, 
					e.`startDateTime`,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = TheCompanyID
						AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
					) 							AS MonthlyBookingTimeUsed,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = TheCompanyID
					) 							AS TotalBookingTimeUsed							
			FROM 	`company` c 
			JOIN 	`employee` e
			ON 		e.CompanyID = c.CompanyID 
			JOIN 	`companyposition` cp 
			ON 		cp.PositionID = e.PositionID
			JOIN 	`user` u 
			ON 		u.userID = e.UserID';
			
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();
	
	//close connection
	$pdo = null;
		
}
catch (PDOException $e)
{
	$error = 'Error getting employee information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}	

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){
	
	if($row['MonthlyBookingTimeUsed'] == null){
		$MonthlyTimeUsed = '00:00:00';
	} else {
		$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
	}

	if($row['TotalBookingTimeUsed'] == null){
		$TotalTimeUsed = '00:00:00';
	} else {
		$TotalTimeUsed = $row['TotalBookingTimeUsed'];
	}
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$employees[] = array('CompanyID' => $row['TheCompanyID'], 
						'UsrID' => $row['UsrID'],
						'CompanyName' => $row['CompanyName'],
						'PositionName' => $row['PositionName'],
						'firstName' => $row['firstName'],
						'lastName' => $row['lastName'],
						'email' => $row['email'],
						'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
						'TotalBookingTimeUsed' => $TotalTimeUsed,
						'startDateTime' => $row['startDateTime']
						);
}

// Create the employees list in HTML
include_once 'employees.html.php';
?>