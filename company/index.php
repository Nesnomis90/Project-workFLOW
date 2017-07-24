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

unsetSessionsFromAdminUsers(); // TO-DO: Add more or remove
unsetSessionsFromUserManagement();

function unsetSessionsFromCompanyManagement(){
	unset($_SESSION['normalUserCompanyIDSelected']);
}

if(!isSet($_GET['ID']) AND !isSet($_GET['employees'])){
	unset($_SESSION['normalUserCompanyIDSelected']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Select Company"){
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
			echo "You have the role of $companyRole in this company";
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

// get a list of all companies
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
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

if(isSet($selectedCompanyToDisplayID) AND !empty($selectedCompanyToDisplayID)){
	// Get company information
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
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
				LIMIT 		1;";
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