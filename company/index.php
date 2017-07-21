<?php 
// This is the index file for the company folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	$gotoPage = ".";
	userIsLoggedIn();
}

unsetSessionsFromAdminUsers(); // TO-DO: Add more or remove

/*
//variables to implement
$numberOfCompanies; //int
$companiesUserWorksFor; //array
$selectedCompanyToDisplayID; //int
$selectedCompanyName; //string
$selectedCompanyToJoinID;//int

// values to retrieve
$_POST['selectedCompanyToJoin'];
*/

// get companies user works for


if(isSet($_POST['selectedCompanyToDisplay']) AND !empty($_POST['selectedCompanyToDisplay'])){
	$selectedCompanyToDisplayID = $_POST['selectedCompanyToDisplay'];
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
							cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice,
							COUNT(DISTINCT cch.`startDate`)						AS CompanyCreditsHistoryPeriods,
							SUM(cch.`hasBeenBilled`)							AS CompanyCreditsHistoryPeriodsSetAsBilled
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
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
		
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

	// Create an array with the actual key/value pairs we want to use in our HTML
	foreach ($result as $row)
	{	
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
		
			// Get Period Status information
		if(isSet($row['CompanyCreditsHistoryPeriods']) AND $row['CompanyCreditsHistoryPeriods'] != NULL){
			$totalPeriods = $row['CompanyCreditsHistoryPeriods'];
		} else {
			$totalPeriods = 0;
		}
		if(isSet($row['CompanyCreditsHistoryPeriodsSetAsBilled']) AND $row['CompanyCreditsHistoryPeriodsSetAsBilled'] != NULL){
			$billedPeriods = $row['CompanyCreditsHistoryPeriodsSetAsBilled'];
		} else {
			$billedPeriods = 0;
		}
		$notBilledPeriods = $totalPeriods - $billedPeriods;
		
		$companies[] = array(
								'CompanyID' => $row['CompanyID'], 
								'CompanyName' => $row['CompanyName'],
								'NumberOfEmployees' => $row['NumberOfEmployees'],
								'PreviousMonthCompanyWideBookingTimeUsed' => $PrevMonthTimeUsed,
								'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
								'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
								'DeletionDate' => $dateToRemoveToDisplay,
								'DatetimeCreated' => $dateTimeCreatedToDisplay,
								'CreditSubscriptionName' => $row["CreditSubscriptionName"],
								'CompanyCredits' => $displayCompanyCredits,
								'CompanyCreditsRemaining' => $displayCompanyCreditsRemaining,
								'CreditSubscriptionMonthlyPrice' => convertToCurrency($monthPrice),
								'OverCreditsFee' => $overCreditsFee,
								'TotalPeriods' => $totalPeriods,
								'BilledPeriods' => $billedPeriods,
								'NotBilledPeriods' => $notBilledPeriods
							);
	}
	var_dump($_SESSION); // TO-DO: remove after testing is done	
}

include_once 'company.html.php';
?>