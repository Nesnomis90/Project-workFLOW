<?php 
// This is the index file for the company folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND isSet($_SESSION['LoggedInUserID']) AND !empty($_SESSION['LoggedInUserID'])){
	$gotoPage = ".";
	userIsLoggedIn();
} else {
	var_dump($_SESSION); // TO-DO: remove after testing is done	

	include_once 'company.html.php';
	exit();
}

// If admin wants to be able to delete companies it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Remove"){
	$_SESSION['normalEmployeesEnableDelete'] = TRUE;
}

// If admin wants to be disable company deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Remove"){
	unset($_SESSION['normalEmployeesEnableDelete']);
}

unsetSessionsFromAdminUsers(); // TO-DO: Add more or remove
unsetSessionsFromUserManagement();

function unsetSessionsFromCompanyManagement(){
	unset($_SESSION['normalUserCompanyIDSelected']);
	unset($_SESSION['normalCompanyCreateACompany']);
}

if(!isSet($_GET['ID']) AND !isSet($_GET['employees'])){
	unset($_SESSION['normalUserCompanyIDSelected']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Create A Company"){
	$_SESSION['normalCompanyCreateACompany'] = TRUE;
}

if(isSet($_POST['action']) AND $_POST['action'] == "Confirm"){
	// Validate text input
	$invalidInput = FALSE;

	if(isSet($_POST['createACompanyName'])){
		$companyName = trim($_POST['createACompanyName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['normalCompanyFeedback'] = "Company cannot be created without a name!";
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedCompanyName = trimExcessWhitespace($companyName);

	// Do actual input validation
	if(validateString($validatedCompanyName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['normalCompanyFeedback'] = "Your submitted company name has illegal characters in it.";
	}

	// Are values actually filled in?
	if($validatedCompanyName == "" AND !$invalidInput){
		$_SESSION['normalCompanyFeedback'] = "You need to fill in a name for the company.";	
		$invalidInput = TRUE;		
	}

	// Check if input length is allowed
		// CompanyName
		// Uses same limit as display name (max 255 chars)
	$invalidCompanyName = isLengthInvalidDisplayName($validatedCompanyName);
	if($invalidCompanyName AND !$invalidInput){
		$_SESSION['normalCompanyFeedback'] = "The company name submitted is too long.";	
		$invalidInput = TRUE;
	}

	// Check if name is already taken
	if(!$invalidInput){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			// Check for company names
			$pdo = connect_to_db();
			$sql = 'SELECT 	COUNT(*)
					FROM 	`company`
					WHERE 	`name` = :CompanyName
					LIMIT 	1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyName', $validatedCompanyName);
			$s->execute();

			//Close connection
			$pdo = null;

			$row = $s->fetch();

			if ($row[0] > 0)
			{
				// This name is already being used for a company	
				$_SESSION['normalCompanyFeedback'] = "There is already a company with the name: " . $validatedCompanyName . "!";
				$invalidInput = TRUE;
			}
			// Company name hasn't been used before
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching company details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();	
		}
	}


	if(!$invalidInput){
		// Create Company
		try
		{
			$sql = 'INSERT INTO `company` 
					SET			`name` = :CompanyName,
								`startDate` = CURDATE(),
								`endDate` = (CURDATE() + INTERVAL 1 MONTH)';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyName', $validatedCompanyName);
			$s->execute();
			
			unset($_SESSION['LastCompanyID']);
			$_SESSION['LastCompanyID'] = $pdo->lastInsertId();

		}
		catch (PDOException $e)
		{
			$error = 'Error adding submitted company to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		
		$_SESSION['normalCompanyFeedback'] = "Successfully added the company: " . $validatedCompanyName . ".";
		
			// Give the company the default subscription
		try
		{
			$sql = "INSERT INTO `companycredits` 
					SET			`CompanyID` = :CompanyID,
								`CreditsID` = (
												SELECT 	`CreditsID`
												FROM	`credits`
												WHERE	`name` = 'Default'
												)";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['LastCompanyID']);
			$s->execute();
		}
		catch (PDOException $e)
		{
			$error = 'Error giving company a booking subscription: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}	

			// Make user owner of company
		try
		{
			$sql = "INSERT INTO `employee` 
					SET			`CompanyID` = :CompanyID,
								`PositionID` = (
												SELECT 	`PositionID`
												FROM	`companyposition`
												WHERE	`name` = 'Owner'
												)";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['LastCompanyID']);
			$s->execute();
		}
		catch (PDOException $e)
		{
			$error = 'Error giving company a booking subscription: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// Add a log event that a company was added
		try
		{
			if(isSet($_SESSION['LastCompanyID'])){
				$LastCompanyID = $_SESSION['LastCompanyID'];
				unset($_SESSION['LastCompanyID']);
			}
			// Save a description with information about the meeting room that was added
			$logEventdescription = "The company: " . $validatedCompanyName . " was created by: " . $_SESSION['LoggedInUserName'];

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Company Created'
												),
								`companyID` = :TheCompanyID,
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventdescription);
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

		// Send email to admin(s) that a company has been created
		unset($_SESSION['normalCompanyCreateACompany']);		
	}
}

if(isSet($_POST['action']) AND $_POST['action'] == "Request To Join"){
	unset($_SESSION['normalCompanyCreateACompany']);
	// TO-DO:
}

if(isSet($_POST['action']) AND $_POST['action'] == "Select Company"){
	unset($_SESSION['normalCompanyCreateACompany']);
	if(isSet($_POST['selectedCompanyToDisplay']) AND !empty($_POST['selectedCompanyToDisplay'])){
		$selectedCompanyToDisplayID = $_POST['selectedCompanyToDisplay'];
		$_SESSION['normalUserCompanyIDSelected'] = $selectedCompanyToDisplayID;
	} else {
		unset($_SESSION['normalUserCompanyIDSelected']);
	}
} elseif(isSet($_GET['ID']) AND !empty($_GET['ID'])) {
	$selectedCompanyToDisplayID = $_GET['ID'];
	$_SESSION['normalUserCompanyIDSelected'] = $selectedCompanyToDisplayID;
}

if(isSet($_SESSION['normalUserCompanyIDSelected']) AND !isSet($_GET['ID']) AND !isSet($_GET['employees'])){
	header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected']);
} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND isSet($_GET['ID']) AND $_GET['ID'] != $_SESSION['normalUserCompanyIDSelected'] AND !isSet($_GET['employees'])) {
	header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected']);
} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND !isSet($_GET['ID']) AND isSet($_GET['employees'])){
	header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected'] . "&employees");
} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND isSet($_GET['ID']) AND $_GET['ID'] != $_SESSION['normalUserCompanyIDSelected'] AND isSet($_GET['employees'])) {
	header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected'] . "&employees");
}
/*
//variables to implement
$selectedCompanyToJoinID;//int

// values to retrieve
$_POST['selectedCompanyToJoin'];
*/

// Get employee information for the selected company when user wants it
if(isSet($_GET['employees']) AND isSet($_SESSION['normalUserCompanyIDSelected']) AND !empty($_SESSION['normalUserCompanyIDSelected'])){

	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		// First check if the user making the call is actually in the company. If not, we won't display anything.
		// Also doubles as the company role check to decide what should be displayed.
		$sql = "SELECT 		COUNT(*) 	AS HitCount,
							cp.`name` 	AS CompanyPosition
				FROM 		`employee` e
				INNER JOIN `companyposition` cp
				ON 			cp.`PositionID` = e.`PositionID`
				WHERE		`CompanyID` = :CompanyID
				AND 		`UserID` = :UserID
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(":CompanyID", $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(":UserID", $_SESSION['LoggedInUserID']);
		$s->execute();

		$userResult = $s->fetch(PDO::FETCH_ASSOC);

		if(isSet($userResult) AND $userResult['HitCount'] > 0){
			$companyRole = $userResult['CompanyPosition'];
		} else {
			$noAccess = TRUE;

			var_dump($_SESSION); // TO-DO: remove after testing is done	

			include_once 'company.html.php';		
			exit();
		}
		
		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						cp.`name`					AS PositionName, 
						e.`startDateTime`			AS StartDateTime,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
						) 							AS PreviousMonthBookingTimeUsed,						
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
						) 							AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
						) 							AS TotalBookingTimeUsed							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID 
				WHERE 	c.`companyID` = :id";
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s				
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();
		
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
		
		// Start a second SQL query to collect the booked time by removed users
		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
						)														AS PreviousMonthBookingTimeUsed,						
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
						)														AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
						)														AS TotalBookingTimeUsed
				FROM 		`company` c
				JOIN 		`booking` b
				ON 			c.`companyID` = b.`companyID`
				JOIN 		`user` u 
				ON 			u.userID = b.UserID 
				WHERE 		c.`companyID` = :id
				AND 		b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
				GROUP BY 	UsrID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();
		
		$removedEmployeesResult = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($removedEmployeesResult)){
			$removedEmployeesResultRowNum = sizeOf($removedEmployeesResult);
		} else {
			$removedEmployeesResultRowNum = 0;
		}
		
		// SQL Query to get booked time for deleted users
		$sql = "SELECT 	`companyID`				AS TheCompanyID,
						`name`					AS CompanyName,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
							FROM 		`booking` b
							INNER JOIN 	`company` c
							ON 			b.`CompanyID` = c.`CompanyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NULL
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
						)														AS PreviousMonthBookingTimeUsed,						
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
							FROM 		`booking` b
							INNER JOIN 	`company` c
							ON 			b.`CompanyID` = c.`CompanyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NULL
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
						)														AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
						FROM 	`booking` b
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NULL
						)														AS TotalBookingTimeUsed
				FROM 	`company`
				WHERE	`companyID` = :id";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();

		$deletedUsersResult = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($deletedUsersResult)){
			$deletedUsersResultRowNum = sizeOf($deletedUsersResult);
		} else {
			$deletedUsersResultRowNum = 0;
		}
		
		//close connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error getting employee information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// If we're looking at a specific company and they have removed employees with booking time
	if($removedEmployeesResultRowNum > 0){
		foreach($removedEmployeesResult AS $row){	
			
			// Calculate and display company booking time details
			if($row['PreviousMonthBookingTimeUsed'] == null){
				$PrevMonthTimeUsed = 'N/A';
			} else {
				$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
				$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
				$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
				$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
			}	
			
			if($row['MonthlyBookingTimeUsed'] == null){
				$MonthlyTimeUsed = 'N/A';
			} else {
				$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
				$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
				$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
				$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';	
			
			}

			if($row['TotalBookingTimeUsed'] == null){
				$TotalTimeUsed = 'N/A';
			} else {
				$TotalTimeUsed = $row['TotalBookingTimeUsed'];
				$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
				$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
				$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
			}
			
			$removedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'firstName' => $row['firstName'],
										'lastName' => $row['lastName'],
										'email' => $row['email'],
										'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,											
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}

		if($removedEmployees[0]['TotalBookingTimeUsed'] == "N/A"){
			// The company has no used booking time by removed users
			unset($removedEmployees);
		}
	}
	
	// If we're looking at a specific company and they have old booking time used by now deleted users
	if($deletedUsersResultRowNum > 0){
		foreach($deletedUsersResult AS $row){	

			// Calculate and display company booking time details
			if($row['PreviousMonthBookingTimeUsed'] == null){
				$PrevMonthTimeUsed = 'N/A';
			} else {
				$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
				$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
				$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
				$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
			}	
			
			if($row['MonthlyBookingTimeUsed'] == null){
				$MonthlyTimeUsed = 'N/A';
			} else {
				$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
				$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
				$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
				$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';	
			
			}

			if($row['TotalBookingTimeUsed'] == null){
				$TotalTimeUsed = 'N/A';
			} else {
				$TotalTimeUsed = $row['TotalBookingTimeUsed'];
				$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
				$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
				$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
			}		
			
			$deletedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}
		
		if($deletedEmployees[0]['TotalBookingTimeUsed'] == "N/A"){
			// The company has no used booking time by deleted users
			unset($deletedEmployees);
		}
	}

	// Create an array with the actual key/value pairs we want to use in our HTML	
	foreach($result AS $row){
		
		// Calculate and display company booking time details
		if($row['PreviousMonthBookingTimeUsed'] == null){
			$PrevMonthTimeUsed = 'N/A';
		} else {
			$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
			$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
			$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
			$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
		}	
		
		if($row['MonthlyBookingTimeUsed'] == null){
			$MonthlyTimeUsed = 'N/A';
		} else {
			$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
			$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
			$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
			$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';
		}

		if($row['TotalBookingTimeUsed'] == null){
			$TotalTimeUsed = 'N/A';
		} else {
			$TotalTimeUsed = $row['TotalBookingTimeUsed'];
			$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
			$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
			$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
		}
		
		$startDateTime = $row['StartDateTime'];
		$displayStartDateTime = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		
		// Create an array with the actual key/value pairs we want to use in our HTML
		$employees[] = array(
							'CompanyID' => $row['TheCompanyID'], 
							'UsrID' => $row['UsrID'],
							'CompanyName' => $row['CompanyName'],
							'PositionName' => $row['PositionName'],
							'firstName' => $row['firstName'],
							'lastName' => $row['lastName'],
							'email' => $row['email'],
							'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,
							'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
							'TotalBookingTimeUsed' => $TotalTimeUsed,
							'StartDateTime' => $displayStartDateTime
							);
	}
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	include_once 'employees.html.php';
	exit();
}

// Get list of companies the user works for
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		c.`CompanyID`	AS CompanyID,
						c.`name`		AS CompanyName
			FROM		`company` c
			INNER JOIN 	`employee` e
			ON 			e.`CompanyID` = c.`CompanyID`
			INNER JOIN	`user` u
			ON			u.`UserID` = e.`UserID`
			WHERE		c.`isActive` = 1
			AND			u.`UserID` = :UserID";
	$s = $pdo->prepare($sql);
	$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
	$s->execute();
	$companiesUserWorksFor = $s->fetchAll(PDO::FETCH_ASSOC);
	if(isSet($companiesUserWorksFor)){
		$numberOfCompanies = sizeOf($companiesUserWorksFor);
	} else {
		$numberOfCompanies = 0;
	}
}
catch (PDOException $e)
{
	$error = 'Error fetching list of companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// First check if the company selected is one of the companies the user actually works for
if(isSet($selectedCompanyToDisplayID) OR (isSet($selectedCompanyToDisplayID) AND empty($selectedCompanyToDisplayID))){

	$companyHit = FALSE;
	foreach($companiesUserWorksFor AS $cmp){
		if($selectedCompanyToDisplayID == $cmp['CompanyID']){
			$companyHit = TRUE;
			break;
		}
	}
	
	if($companyHit === FALSE){
		$noAccess = TRUE;
		$pdo = null;
		var_dump($_SESSION); // TO-DO: remove after testing is done	

		include_once 'company.html.php';		
		exit();
	}
}

// get a list of all companies
try
{
	$sql = "SELECT 	`CompanyID`	AS CompanyID,
					`name`		AS CompanyName
			FROM	`company`
			WHERE	`isActive` = 1";
	$return = $pdo->query($sql);
	$companies = $return->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e)
{
	$error = 'Error fetching list of companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// if user wants to see the details of the company booking history
if(isSet($_GET['totalBooking']) OR isSet($_GET['activeBooking']) OR isSet($_GET['completedBooking']) OR isSet($_GET['cancelledBooking'])){

	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 		b.`userID`										AS BookedUserID,
							b.`bookingID`,
							(
								IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
							)        										AS BookedRoomName,
							b.`startDateTime`								AS StartTime,
							b.`endDateTime`									AS EndTime, 
							b.`displayName` 								AS BookedBy,
							(
								IF(b.`companyID` IS NULL, NULL, (SELECT `name` FROM `company` WHERE `companyID` = b.`companyID`))
							)        										AS BookedForCompany,	 
							b.`description`									AS BookingDescription,
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn,										
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS firstName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS lastName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS email,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `sendEmail` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS sendEmail,
							(
								IF(b.`userID` IS NULL, NULL, 
									(
										SELECT 		cp.`name` 
										FROM 		`companyposition` cp
										INNER JOIN 	`employee` e
										ON			cp.`PositionID` = e.`PositionID`
										WHERE 		e.`userID` = b.`userID`
										AND			e.`CompanyID`= :CompanyID
									)
								)
							) 												AS CompanyRole
				FROM 		`booking` b
				WHERE		b.`CompanyID` = :CompanyID
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				ASC';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $selectedCompanyToDisplayID);
		$s->execute();

		$result = $s->fetchAll(PDO::FETCH_ASSOC);

		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error getting booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	foreach($result as $row)
	{
		$datetimeNow = getDatetimeNow();
		$startDateTime = $row['StartTime'];	
		$endDateTime = $row['EndTime'];
		$completedDateTime = $row['BookingWasCompletedOn'];
		$dateOnlyNow = convertDatetimeToFormat($datetimeNow, 'Y-m-d H:i:s', 'Y-m-d');
		$dateOnlyCompleted = convertDatetimeToFormat($completedDateTime,'Y-m-d H:i:s','Y-m-d');
		$dateOnlyStart = convertDatetimeToFormat($startDateTime,'Y-m-d H:i:s','Y-m-d');
		$cancelledDateTime = $row['BookingWasCancelledOn'];
		$createdDateTime = $row['BookingWasCreatedOn'];	
		
		// Describe the status of the booking based on what info is stored in the database
		// If not finished and not cancelled = active
		// If meeting time has passed and finished time has updated (and not been cancelled) = completed
		// If cancelled = cancelled
		// If meeting time has passed and finished time has NOT updated (and not been cancelled) = Ended without updating
		// If none of the above = Unknown
		if(			$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow < $endDateTime AND $dateOnlyNow != $dateOnlyStart) {
			$status = 'Active';
			// Valid status
		} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow < $endDateTime AND $dateOnlyNow == $dateOnlyStart){
			$status = 'Active Today';
			// Valid status		
		} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
					$dateOnlyNow != $dateOnlyCompleted){
			$status = 'Completed';
			// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
					$dateOnlyNow == $dateOnlyCompleted){
			$status = 'Completed Today';
			// Valid status
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$startDateTime > $cancelledDateTime){
			$status = 'Cancelled';
			// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime >= $cancelledDateTime ){
			$status = 'Ended Early';
			// Valid status?
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$endDateTime < $cancelledDateTime AND 
					$startDateTime > $cancelledDateTime){
			$status = 'Ended Early';
			// Valid status?
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime < $cancelledDateTime ){
			$status = 'Cancelled after Completion';
			// This should not be allowed to happen eventually
		} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow > $endDateTime){
			$status = 'Ended without updating database';
			// This should never occur
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND 
					$endDateTime < $cancelledDateTime){
			$status = 'Cancelled after meeting should have been Completed';
			// This should not be allowed to happen eventually
		} else {
			$status = 'Unknown';
			// This should never occur
		}

		$roomName = $row['BookedRoomName'];
		$displayRoomNameForTitle = $roomName;
		$firstname = $row['firstName'];
		$lastname = $row['lastName'];
		$email = $row['email'];
		$userinfo = $lastname . ', ' . $firstname . ' - ' . $row['email'];
		$companyRole = $row['CompanyRole'];

		if(!isSet($roomName) OR empty($roomName)){
			$roomName = "N/A - Deleted";
		}
		if(!isSet($userinfo) OR $userinfo == NULL OR $userinfo == ",  - "){
			$userinfo = "N/A - Deleted";	
		}
		if(!isSet($email) OR empty($email)){
			$firstname = "N/A - Deleted";
			$lastname = "N/A - Deleted";
			$email = "N/A - Deleted";		
		}
		if(!isSet($companyRole) OR empty($companyRole)){
			$companyRole = "Removed";
		}

		$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayCompletedDateTime = convertDatetimeToFormat($completedDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayCancelledDateTime = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
		$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		$meetinginfo = $roomName . ' for the timeslot: ' . $displayValidatedStartDate . 
						' to ' . $displayValidatedEndDate;

		$completedMeetingDurationInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $completedDateTime);
		$displayCompletedMeetingDuration = convertMinutesToHoursAndMinutes($completedMeetingDurationInMinutes);
		if($completedMeetingDurationInMinutes < BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS){
			$completedMeetingDurationForPrice = 0;
		} elseif($completedMeetingDurationInMinutes < MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS){
			$completedMeetingDurationForPrice = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS;
		} else {
			$completedMeetingDurationForPrice = $completedMeetingDurationInMinutes;
		}
		$displayCompletedMeetingDurationForPrice = convertMinutesToHoursAndMinutes($completedMeetingDurationForPrice);
		
		if($status == "Active Today" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))) {				
			$bookingsActiveToday[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo,
											'sendEmail' => $row['sendEmail']
										);
		}	elseif($status == "Completed Today" AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsCompletedToday[] = array(	'id' => $row['bookingID'],
												'BookingStatus' => $status,
												'BookedRoomName' => $roomName,
												'StartTime' => $displayValidatedStartDate,
												'EndTime' => $displayValidatedEndDate,
												'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
												'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
												'BookedBy' => $row['BookedBy'],
												'BookedForCompany' => $row['BookedForCompany'],
												'BookingDescription' => $row['BookingDescription'],
												'BookingWasCreatedOn' => $displayCreatedDateTime,
												'BookingWasCompletedOn' => $displayCompletedDateTime,
												'BookingWasCancelledOn' => $displayCancelledDateTime,
												'firstName' => $firstname,
												'lastName' => $lastname,
												'email' => $email,
												'CompanyRole' => $companyRole,
												'UserInfo' => $userinfo,
												'MeetingInfo' => $meetinginfo
											);
		}	elseif($status == "Active" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsFuture[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'CompanyRole' => $companyRole,
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo,
										'sendEmail' => $row['sendEmail']
									);
		}	elseif(($status == "Completed" OR $status == "Ended Early") AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){				
			$bookingsCompleted[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
											'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo
										);
		}	elseif($status == "Cancelled" AND (isSet($_GET['cancelledBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsCancelled[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo
										);		
		}	elseif(isSet($_GET['totalBooking'])){				
			$bookingsOther[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'CompanyRole' => $companyRole,
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo
									);
		}
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Create the booking information table in HTML
	include_once 'bookings.html.php';
	exit();
} else {
	unset($_SESSION['normalCompanyBookingHistory']);
}

if(isSet($selectedCompanyToDisplayID) AND !empty($selectedCompanyToDisplayID)){

	// Get company information
	try
	{
		// Calculate booking time used for a company
		// Only takes into account time spent and company the booking was booked for.
			// Booking time is rounded for each booking, instead of summed up and then rounded.
			// We therefore get the minimum time per booking for our equations
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s
		
		$sql = "SELECT 		c.`companyID` 										AS CompanyID,
							c.`name` 											AS CompanyName,
							c.`dateTimeCreated`									AS DatetimeCreated,
							c.`removeAtDate`									AS DeletionDate,
							c.`isActive`										AS CompanyActivated,
							(
								SELECT 	COUNT(e.`CompanyID`)
								FROM 	`employee` e
								WHERE 	e.`companyID` = :CompanyID
							)													AS NumberOfEmployees,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = :CompanyID
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`prevStartDate`
								AND			c.`startDate`
							)   												AS PreviousMonthCompanyWideBookingTimeUsed,           
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = :CompanyID
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							)													AS MonthlyCompanyWideBookingTimeUsed,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b
								WHERE 		b.`CompanyID` = :CompanyID
							)													AS TotalCompanyWideBookingTimeUsed,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
							)													AS TotalBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NULL
								AND 	`endDateTime` > CURRENT_TIMESTAMP
							)													AS ActiveBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	(
											`actualEndDateTime` IS NOT NULL
										OR
											(
														`actualEndDateTime` IS NULL
												AND 	`dateTimeCancelled` IS NULL
												AND 	`endDateTime` <= CURRENT_TIMESTAMP
											)
										)
							)													AS CompletedBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NOT NULL
							)													AS CancelledBookedMeetings,
							cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
							cc.`lastModified`									AS CompanyCreditsLastModified,
							cr.`name`											AS CreditSubscriptionName,
							cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
							cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
							cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice
				FROM 		`company` c
				LEFT JOIN	`companycredits` cc
				ON			c.`CompanyID` = cc.`CompanyID`
				LEFT JOIN	`credits` cr
				ON			cr.`CreditsID` = cc.`CreditsID`
				LEFT JOIN 	`companycreditshistory` cch
				ON 			cch.`CompanyID` = c.`CompanyID`
				WHERE		c.`CompanyID` = :CompanyID
				GROUP BY 	c.`CompanyID`
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->bindValue(':CompanyID', $selectedCompanyToDisplayID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		//Close the connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company information from the database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Calculate and display company booking time details
	if($row['PreviousMonthCompanyWideBookingTimeUsed'] == null){
		$PrevMonthTimeUsed = 'N/A';
	} else {
		$PrevMonthTimeUsed = convertTimeToHoursAndMinutes($row['PreviousMonthCompanyWideBookingTimeUsed']);
	}	

	if($row['MonthlyCompanyWideBookingTimeUsed'] == null){
		$MonthlyTimeUsed = 'N/A';
	} else {
		$MonthlyTimeUsed = convertTimeToHoursAndMinutes($row['MonthlyCompanyWideBookingTimeUsed']);
	}

	if($row['TotalCompanyWideBookingTimeUsed'] == null){
		$TotalTimeUsed = 'N/A';
	} else {
		$TotalTimeUsed = convertTimeToHoursAndMinutes($row['TotalCompanyWideBookingTimeUsed']);	
	}

	// Calculate and display company booking subscription details
	if($row["CompanyAlternativeMinuteAmount"] != NULL AND $row["CompanyAlternativeMinuteAmount"] != ""){
		$companyMinuteCredits = $row["CompanyAlternativeMinuteAmount"];
	} elseif($row["CreditSubscriptionMinuteAmount"] != NULL AND $row["CreditSubscriptionMinuteAmount"] != "") {
		$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
	} else {
		$companyMinuteCredits = 0;
	}
		// Format company credits to be displayed
	$displayCompanyCredits = convertMinutesToHoursAndMinutes($companyMinuteCredits);

	$monthPrice = $row["CreditSubscriptionMonthlyPrice"];
	if($monthPrice == NULL OR $monthPrice == ""){
		$monthPrice = 0;
	}
	$hourPrice = $row["CreditSubscriptionHourPrice"];
	if($hourPrice == NULL OR $hourPrice == ""){
		$hourPrice = 0;
	}
	$overCreditsFee = convertToCurrency($hourPrice) . "/h";

		// Calculate Company Credits Remaining
	if($MonthlyTimeUsed != "N/A"){
		$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
		$monthlyTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);
		$actualTimeUsedInMinutesThisMonth = $monthlyTimeHour*60 + $monthlyTimeMinute;
		if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
			$minusCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
			$displayCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusCompanyMinuteCreditsRemaining);
		} else {
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
			$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
		}
	} else {
		$companyMinuteCreditsRemaining = $companyMinuteCredits;
		$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
	}

		// Display dates
	$dateCreated = $row['DatetimeCreated'];	
	$dateToRemove = $row['DeletionDate'];
	$isActive = ($row['CompanyActivated'] == 1);
	$dateTimeCreatedToDisplay = convertDatetimeToFormat($dateCreated, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateToRemoveToDisplay = convertDatetimeToFormat($dateToRemove, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	$numberOfTotalBookedMeetings = $row['TotalBookedMeetings'];
	$numberOfActiveBookedMeetings = $row['ActiveBookedMeetings'];
	$numberOfCompletedBookedMeetings = $row['CompletedBookedMeetings'];
	$numberOfCancelledBookedMeetings = $row['CancelledBookedMeetings'];
	
	$companyInformation = array(
							'CompanyID' => $row['CompanyID'], 
							'CompanyName' => $row['CompanyName'],
							'NumberOfEmployees' => $row['NumberOfEmployees'],
							'PreviousMonthCompanyWideBookingTimeUsed' => $PrevMonthTimeUsed,
							'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
							'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
							'DeletionDate' => $dateToRemoveToDisplay,
							'DatetimeCreated' => $dateTimeCreatedToDisplay,
							'CompanyCredits' => $displayCompanyCredits,
							'CompanyCreditsRemaining' => $displayCompanyCreditsRemaining,
							'CreditSubscriptionMonthlyPrice' => convertToCurrency($monthPrice),
							'OverCreditsFee' => $overCreditsFee
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done	

include_once 'company.html.php';
?>