<?php 
// This is the index file for the EMPLOYEES folder

// TO-DO: This needs fixes to ADD and EDIT!

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


// 	If admin wants to add a company to the database
// 	we load a new html form
//	TO-DO: NEED A WAY TO BE ABLE TO SELECT THE USER AND THE COMPANY WE WANT TO MATCH
if ((isset($_POST['action']) AND $_POST['action'] == 'Add Employee') OR (isset($_POST['action']) AND $_POST['action'] == 'Search'))
{	
	// Update company position for the employee connection in database
	try
	{
		if (isset($_POST['action']) AND $_POST['action'] == 'Search'){
			$usersearchstring = $_POST['usersearchstring'];
			$companysearchstring = $_POST['companysearchstring'];
			echo "Search button clicked <br />";
		} else {
			$usersearchstring = '';
			$companysearchstring = '';
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get name and IDs for company position
		$pdo = connect_to_db();
		$sql = 'SELECT 	`PositionID`,
						`name` 			AS CompanyPositionName,
						`description`	AS CompanyPositionDescription
				FROM 	`companyposition`';
		$result = $pdo->query($sql);
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$companyposition[] = array(
									'PositionID' => $row['PositionID'],
									'CompanyPositionName' => $row['CompanyPositionName'],
									'CompanyPositionDescription' => $row['CompanyPositionDescription']
									);
		}

		// Get all companies and users so the admin can search/choose from them
			//Companies
		$sql = 'SELECT 	`companyID` AS CompanyID,
						`name`		AS CompanyName
				FROM 	`company`';
				
		echo "companysearchstring is: $companysearchstring <br />";
		if ($companysearchstring != ''){
			$sqladd = " WHERE `name` LIKE :search";
			$sql = $sql . $sqladd;	
			
			$finalcompanysearchstring = '%' . $companysearchstring . '%';
			
			$s = $pdo->prepare($sql);
			$s->bindValue(':search', $finalcompanysearchstring);
			$s->execute();
			$result = $s->fetchAll();
			echo "Size of result: " . sizeOf($result) . "<br />";
		} else {
			$result = $pdo->query($sql);
			echo "Size of result: " . sizeOf($result) . "<br />";
		}
		echo "Company SQL is: $sql <br />";
			
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$companies[] = array(
									'CompanyID' => $row['CompanyID'],
									'CompanyName' => $row['CompanyName']
									);
		}
		
			//	Users - Only active ones?
			// 	TO-DO: Change to allow all users?	
		$sql = "SELECT 	`userID` 	AS UserID,
						`firstname`,
						`lastname`,
						`email`
				FROM 	`user`
				WHERE	`isActive` > 0";

		echo "usersearchstring is: $usersearchstring <br />";
		if ($usersearchstring != ''){
			$sqladd = " AND (`firstname` LIKE :search
						OR `lastname` LIKE :search
						OR `email` LIKE :search)";
			$sql = $sql . $sqladd;
			
			$finalusersearchstring = '%' . $usersearchstring . '%';
			
			$s = $pdo->prepare($sql);
			$s->bindValue(":search", $finalusersearchstring);
			$s->execute();
			$result = $s->fetchAll();
			echo "Size of result: " . sizeOf($result) . "<br />";
			
		} else {
			$result = $pdo->query($sql);
			echo "Size of result: " . sizeOf($result) . "<br />";
		}
		echo "User SQL is: $sql <br />";
		

		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$users[] = array(
									'UserID' => $row['UserID'],
									'UserIdentifier' => $row['lastname'] . ', ' .
									$row['firstname'] . ' - ' . $row['email']
									);
		}
				
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching searched user and company list: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
		
	// Change to the actual html form template
	include 'addemployee.html.php';
	exit();
}

// if admin wants to set the date to remove for a company
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Change Role')
{
	// Get information from database again on the selected company	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		// Get name and IDs for company position
		$pdo = connect_to_db();
		$sql = 'SELECT 	`PositionID`,
						`name` 			AS CompanyPositionName,
						`description`	AS CompanyPositionDescription
				FROM 	`companyposition`';
		$result = $pdo->query($sql);
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$companyposition[] = array(
									'PositionID' => $row['PositionID'],
									'CompanyPositionName' => $row['CompanyPositionName'],
									'CompanyPositionDescription' => $row['CompanyPositionDescription']
									);
		}
		
		// Get employee information
		$sql = 'SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						cp.`name`					AS PositionName							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID
				WHERE	e.userID = :UserID
				AND 	e.companyID = :CompanyID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
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
	$CompanyName = $row['CompanyName'];
	$UserIdentifier = $row['firstName'] . ' ' . $row['lastName'];
	$companypositionname = $row['PositionName'];
	$CompanyID = $row['TheCompanyID'];
	$UserID = $row['UsrID'];
	
	// Change to the actual form we want to use
	include 'changerole.html.php';
	exit();
}

// When admin has added the needed information and wants to add an employee connection
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Employee')
{
	// TO-DO: CHECK THAT USER ISN'T ALREADY IN THE COMPANY
	// Add the new employee connection to the database
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
		$error = 'Error creating employee connection in database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Load employee list webpage with new employee connection
	header('Location: .');
	exit();
}

// Perform the actual database update of the edited information
if (isset($_GET['changerole']))
{
	// Update selected employee connection with a new company position
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`employee` 
				SET		`PositionID` = :PositionID
				WHERE 	`companyID` = :CompanyID
				AND 	`userID` = :UserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':PositionID', $_POST['PositionID']);
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