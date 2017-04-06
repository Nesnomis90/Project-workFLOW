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
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}
// If admin wants to see list of employees in the specific company from the database
if (isset($_POST['action']) AND  $_POST['action'] == 'Employees')
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	
	$sql = "SELECT 	u.`userID`					AS UsrID,
					c.`companyID`				AS TheCompanyID,
					c.`name`					AS CompanyName,
					u.`firstName`, 
					u.`lastName`,
					u.`email`,
					cp.`name`					AS PositionName, 
					DATE_FORMAT(e.`startDateTime`,'%d %b %Y %T') 	AS StartDateTime,
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
			WHERE 	c.`companyID` = :id";
			
	$s = $pdo->prepare($sql);
	$s->bindValue(':id', $_POST['id']);
	$s->execute();
	
	$result = $s->fetchAll();
	$rowNum = sizeOf($result);
	
	//close connection
	$pdo = null;
	
	
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
							'StartDateTime' => $row['StartDateTime']
							);
	}
	//TO-DO: MAKE THIS WORK WITH A SESSION LATER? URL ISN'T CHANGED. WE'RE ONLY INCLUDING A TEMPLATE
	// 		 WE'RE STILL IN "COMPANIES"
	//$location = "http://$_SERVER[HTTP_HOST]/admin/employees";
	//echo "location is: $location";
	//header("Location: $location");
	include_once $_SERVER['DOCUMENT_ROOT'] . '/admin/employees/employees.html.php';
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