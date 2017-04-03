<?php 
// This is the index file for the COMPANIES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// If admin wants to remove a company from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	// Delete selected company from database
	try
	{
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
	$DateToRemove = '';
	$id = '';
	$button = 'Add company';
	
	// We want a reset all fields button while adding a new company
	$reset = 'reset';
	
	// We don't need to see date to remove when adding a new company
	// style=display:block to show, style=display:none to hide
	$DateToRemoveStyle = 'none';
	$CompanyPositionStyle = 'none';
	
	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// for edit 	
// $companypositionname = $row['CompanyPositionName'];


// When admin has added the needed information and wants to add the company
if (isset($_GET['addform']))
{
	// Add the company to the database
	try
	{	
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `company` SET
							`name` = :CompanyName';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyName', $_POST['CompanyName']);
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

// if admin wants to edit company information
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] = 'Edit')
{
	// Get information from database again on the selected company	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get company information
		$pdo = connect_to_db();
		$sql = 'SELECT 	
				WHERE 	c.`companyID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
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
	$DateToRemove = $row['DateToRemove'];
	$id = $row['companyID'];
	$button = 'Edit company';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	// Want to see company position and date to remove while editing
	// style=display:block to show, style=display:none to hide
	$DateToRemoveStyle = 'block';
	$CompanyPositionStyle = 'block';
	
	// Change to the actual form we want to use
	include 'form.html.php';
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