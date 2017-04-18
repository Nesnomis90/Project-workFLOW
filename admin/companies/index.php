<?php 
// This is the index file for the COMPANIES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}


// If admin wants to remove a company from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	// Delete selected company from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `company` 
				WHERE 		`companyID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting company to delete: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// Add a log event that a company was removed
	try
	{
		session_start();

		// Save a description with information about the meeting room that was removed
		$description = "The company: " . $_POST['CompanyName'] . " was removed by: " . $_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Company Removed'
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
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add a company to the database
// we load a new html form
if (isset($_GET['add']))
{	
	// Set values to be displayed in HTML
	$pageTitle = 'New Company';
	$action = 'addform';
	$CompanyName = '';
	$id = '';
	$button = 'Add company';
	
	// We want a reset all fields button while adding a new company
	$reset = 'reset';
	
	// We don't need to see date to remove when adding a new company
	$ShowDateToRemove = FALSE;
	
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
		$sql = 'SELECT 	`companyID`,
						`name`,
						`removeAtDate`
				FROM 	`company`
				WHERE 	`companyID` = :id';
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
	$pageTitle = 'Edit Company';
	$action = 'editform';
	$CompanyName = $row['name'];
	$DateToRemove = $row['removeAtDate'];
	$id = $row['companyID'];
	$button = 'Edit company';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	// Want to see date to remove while editing
	$ShowDateToRemove = TRUE;
	
	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the company
if (isset($_GET['addform']))
{
	// TO-DO: Check if company already exists.
	
	// Add the company to the database
	try
	{	
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `company` 
				SET			`name` = :CompanyName';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyName', $_POST['CompanyName']);
		$s->execute();
		
		session_start();
		unset($_SESSION['LastCompanyID']);
		$_SESSION['LastCompanyID'] = $pdo->lastInsertId();
		
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
	
		// Add a log event that a company was added
	try
	{
		session_start();
		if(isset($_SESSION['LastCompanyID'])){
			$LastCompanyID = $_SESSION['LastCompanyID'];
			unset($_SESSION['LastCompanyID']);
		}
		// Save a description with information about the meeting room that was added
		$description = "The company: " . $_POST['CompanyName'] . " was added by: " . $_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Company Created'
											),
							`companyID` = :TheCompanyID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
		$s->bindValue(':TheCompanyID', $LastCompanyID);
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
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`company` 
				SET		`removeAtDate` = :removeAtDate,
						`name` = :name
				WHERE 	`companyID` = :id';
		
		if ($_POST['DateToRemove']!=''){
			$CorrectDate = correctDateFormat($_POST['DateToRemove']);
		} else {
			$CorrectDate = null;
		}

						
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->bindValue(':removeAtDate', $CorrectDate);
		$s->bindValue(':name', $_POST['CompanyName']);
		$s->execute();
		
		//close connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error editing company information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

// if admin wants to cancel the date to remove
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel Date')
{
	// Update selected company by making date to remove null	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`company` 
				SET		`removeAtDate` = NULL
				WHERE 	`companyID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error cancelling removal date: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

// Display companies list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		c.companyID 										AS CompID,
						c.`name` 											AS CompanyName,
						DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
						DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate,							
						(
							SELECT 	COUNT(c.`name`) 
							FROM 	`company` c 
							JOIN 	`employee` e 
							ON 		c.CompanyID = e.CompanyID 
							WHERE 	e.companyID = CompID
						)													AS NumberOfEmployees,
						(
							SELECT 		(SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`)))) 
							FROM 		`booking` b 
							INNER JOIN 	`employee` e 
							ON 			b.`UserID` = e.`UserID` 
							INNER JOIN 	`company` c 
							ON 			e.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
							AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
							AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)   												AS MonthlyCompanyWideBookingTimeUsed,
						(
							SELECT 		(SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`)))) 
							FROM 		`booking` b 
							INNER JOIN 	`employee` e 
							ON 			b.`UserID` = e.`UserID` 
							INNER JOIN 	`company` c 
							ON 			e.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
						)   												AS TotalCompanyWideBookingTimeUsed
			FROM 		`company` c 
			GROUP BY 	c.`name`";
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();
	
	//Close the connection
	$pdo = null;	
}
catch (PDOException $e)
{
	$error = 'Error fetching companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach ($result as $row)
{
	if($row['MonthlyCompanyWideBookingTimeUsed'] == null){
		$MonthlyTimeUsed = '00:00:00';
	} else {
		$MonthlyTimeUsed = $row['MonthlyCompanyWideBookingTimeUsed'];
	}
	
	if($row['TotalCompanyWideBookingTimeUsed'] == null){
		$TotalTimeUsed = '00:00:00';
	} else {
		$TotalTimeUsed = $row['TotalCompanyWideBookingTimeUsed'];
	}
	
	$companies[] = array('id' => $row['CompID'], 
					'CompanyName' => $row['CompanyName'],
					'NumberOfEmployees' => $row['NumberOfEmployees'],
					'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
					'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
					'DeletionDate' => $row['DeletionDate'],
					'DatetimeCreated' => $row['DatetimeCreated']
					);
}

// Create the companies list in HTML
include_once 'companies.html.php';
?>