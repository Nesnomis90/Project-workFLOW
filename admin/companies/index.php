<?php 
// This is the index file for the COMPANIES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember values when displaying booking history
function clearBookingHistorySessions(){
	unset($_SESSION['BookingHistoryStartDate']);
	unset($_SESSION['BookingHistoryEndDate']);
	unset($_SESSION['BookingHistoryCompanyInfo']);
	unset($_SESSION['BookingHistoryCompanyMergeNumber']);
	unset($_SESSION['BookingHistoryDisplayWithMerged']);
}

// Function to clear sessions used to remember user inputs on refreshing the add company form
function clearAddCompanySessions(){
	unset($_SESSION['AddCompanyCompanyName']);
}

// Function to clear sessions used to remember user inputs during the company merging
function clearMergeCompanySessions(){
	unset($_SESSION['MergeCompanySelectedCompanyID']);
	unset($_SESSION['MergeCompanySelectedCompanyID2']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit company form
function clearEditCompanySessions(){	
	unset($_SESSION['EditCompanyOriginalName']);
	unset($_SESSION['EditCompanyOriginalRemoveDate']);

	unset($_SESSION['EditCompanyChangedName']);
	unset($_SESSION['EditCompanyChangedRemoveDate']);

	unset($_SESSION['EditCompanyCompanyID']);
}
// Function to check if the company has unbilled periods and then sums them up and displays the total 
function sumUpUnbilledPeriods($pdo, $companyID){

	$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
	$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // e.g. 5min = 300s
	$roundDownToTheClosestMinuteNumberInSeconds = ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT * 60; // e.g. 15min = 900s
	if(isSet($roundDownToTheClosestMinuteNumberInSeconds) AND $roundDownToTheClosestMinuteNumberInSeconds != 0){
			// Rounds down to the closest 15 minutes (on finished summation per period)
		$sql = "SELECT		StartDate, 
							EndDate,
							CompanyMergeNumber,
							CreditSubscriptionMonthlyPrice,
							CreditSubscriptionHourPrice,
							CreditsGivenInSeconds/60							AS CreditSubscriptionMinuteAmount,
							BIG_SEC_TO_TIME(CreditsGivenInSeconds) 				AS CreditsGiven,
							BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 		AS BookingTimeCharged,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeExact,
							BIG_SEC_TO_TIME(
											FLOOR(
												(
													IF(
														(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
														BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
														0
													)
												)/:roundDownToTheClosestMinuteNumberInSeconds
											)*:roundDownToTheClosestMinuteNumberInSeconds
							)													AS OverCreditsTimeCharged
				FROM (
						SELECT 	cch.`startDate`									AS StartDate,
								cch.`endDate`									AS EndDate,
								cch.`minuteAmount`*60							AS CreditsGivenInSeconds,
								cch.`monthlyPrice`								AS CreditSubscriptionMonthlyPrice,
								cch.`overCreditHourPrice`						AS CreditSubscriptionHourPrice,
								cch.`mergeNumber`								AS CompanyMergeNumber,
								(
									SELECT (IFNULL(SUM(
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
										0)
									),0))
									FROM 		`booking` b
									WHERE 		b.`CompanyID` = :CompanyID
									AND			b.`mergeNumber` = cch.`mergeNumber`
									AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
									AND			DATE(b.`actualEndDateTime`) < cch.`endDate`
								)										AS BookingTimeChargedInSeconds
							FROM 		`companycreditshistory` cch
							INNER JOIN	`companycredits` cc
							ON 			cc.`CompanyID` = cch.`CompanyID`
							INNER JOIN 	`credits` cr
							ON 			cr.`CreditsID` = cc.`CreditsID`
							WHERE 		cch.`CompanyID` = :CompanyID
							AND 		cch.`hasBeenBilled` = 0
				)													AS PeriodInformation";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->bindValue(':roundDownToTheClosestMinuteNumberInSeconds', $roundDownToTheClosestMinuteNumberInSeconds);
	} else {
		$sql = "SELECT		StartDate, 
							EndDate,
							CompanyMergeNumber,
							CreditSubscriptionMonthlyPrice,
							CreditSubscriptionHourPrice,
							CreditsGivenInSeconds/60							AS CreditSubscriptionMinuteAmount,
							BIG_SEC_TO_TIME(CreditsGivenInSeconds) 				AS CreditsGiven,
							BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 		AS BookingTimeCharged,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeExact,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeCharged
				FROM (
						SELECT 	cch.`startDate`									AS StartDate,
								cch.`endDate`									AS EndDate,
								cch.`minuteAmount`*60							AS CreditsGivenInSeconds,
								cch.`monthlyPrice`								AS CreditSubscriptionMonthlyPrice,
								cch.`overCreditHourPrice`						AS CreditSubscriptionHourPrice,
								cch.`mergeNumber`								AS CompanyMergeNumber,
								(
									SELECT (IFNULL(SUM(
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
										0)
									),0))
									FROM 		`booking` b
									WHERE 		b.`CompanyID` = :CompanyID
									AND			b.`mergeNumber` = cch.`mergeNumber`
									AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
									AND 		DATE(b.`actualEndDateTime`) < cch.`endDate`
								)										AS BookingTimeChargedInSeconds
							FROM 		`companycreditshistory` cch
							INNER JOIN	`companycredits` cc
							ON 			cc.`CompanyID` = cch.`CompanyID`
							INNER JOIN 	`credits` cr
							ON 			cr.`CreditsID` = cc.`CreditsID`
							WHERE 		cch.`CompanyID` = :CompanyID
							AND 		cch.`hasBeenBilled` = 0
				)													AS PeriodInformation";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);		
	}

	$s->execute();
	$result = $s->fetchAll(PDO::FETCH_ASSOC);

	$numberOfMergedPeriods = 0;

	foreach($result AS $row){
				// Get credits values
		$companyMinuteCredits = $row['CreditSubscriptionMinuteAmount'];
		if($companyMinuteCredits == NULL OR $companyMinuteCredits == ""){
			$companyMinuteCredits = 0;
		}
		$monthPrice = $row["CreditSubscriptionMonthlyPrice"];
		if($monthPrice == NULL OR $monthPrice == ""){
			$monthPrice = 0;
		}
		$hourPrice = $row["CreditSubscriptionHourPrice"];
		if($hourPrice == NULL OR $hourPrice == ""){
			$hourPrice = 0;
		}

		$mergeNumber = $row['CompanyMergeNumber'];
		if($mergeNumber == 0){
			$mergeStatus = "Period From This Company";
		} else {
			$mergeStatus = "Transferred From Another Company (ID=$mergeNumber)";
			$numberOfMergedPeriods += 1;
		}

		// Calculate price
		$bookingTimeChargedInMinutes = convertTimeToMinutes($row['OverCreditsTimeCharged']);
		if(isSet($roundDownToTheClosestMinuteNumberInSeconds) AND $roundDownToTheClosestMinuteNumberInSeconds != 0){
			// Adapt hourprice into correct piece of our ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT (e.g. 15 min)
			$splitPricePerHourIntoThisManyPieces = 60 / ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT;
				// Rounds down
			$numberOfMinuteSlices = floor($bookingTimeChargedInMinutes / ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT);
			$slicedHourPrice = $hourPrice/$splitPricePerHourIntoThisManyPieces;
			$overFeeCostThisMonth = $numberOfMinuteSlices * $slicedHourPrice;			
		} else {
			// Get price for the exact minute amount used
			$minutePrice = $hourPrice/60;
			$overFeeCostThisMonth = $bookingTimeChargedInMinutes * $minutePrice;
		}

		$totalCost = $monthPrice+$overFeeCostThisMonth;
		$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency($overFeeCostThisMonth);
		$totalBookingCostThisMonth = convertToCurrency($totalCost);		

		$startDate = $row['StartDate'];
		$endDate = $row['EndDate'];
		$displayStartDate = convertDatetimeToFormat($startDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayEndDate = convertDatetimeToFormat($endDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

		$periodsSummmedUp[] = array(
										'MergeStatus' => $mergeStatus,
										'MergeNumber' => $mergeNumber,
										'DisplayStartDate' => $displayStartDate,
										'DisplayEndDate' => $displayEndDate,
										'StartDate' => $startDate,
										'EndDate' => $endDate,
										'CreditsGiven' => convertTimeToHoursAndMinutes($row['CreditsGiven']),
										'BookingTimeCharged' => convertTimeToHoursAndMinutes($row['BookingTimeCharged']),
										'OverCreditsTimeExact' => convertTimeToHoursAndMinutes($row['OverCreditsTimeExact']),
										'OverCreditsTimeCharged' => convertTimeToHoursAndMinutes($row['OverCreditsTimeCharged']),
										'TotalBookingCostThisMonthAsParts' => $bookingCostThisMonth,
										'TotalBookingCostThisMonth' => $totalBookingCostThisMonth,
										'TotalBookingCostThisMonthJustNumber' => $totalCost
									);
	}

	if($numberOfMergedPeriods > 0){
		$displayMergeStatus = TRUE;
	} else {
		$displayMergeStatus = FALSE;
	}

	if(isSet($periodsSummmedUp)){
		return array($periodsSummmedUp, $displayMergeStatus);
	} else {
		return FALSE;
	}
}

// Function to check if the company has unbilled periods and then sums them up and displays the total 
function displayAllPeriodsFromMergedNumber($pdo, $companyID, $mergeNumber){

	$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
	$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // e.g. 5min = 300s
	$roundDownToTheClosestMinuteNumberInSeconds = ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT * 60; // e.g. 15min = 900s
	if(isSet($roundDownToTheClosestMinuteNumberInSeconds) AND $roundDownToTheClosestMinuteNumberInSeconds != 0){
			// Rounds down to the closest 15 minutes (on finished summation per period)
		$sql = "SELECT		StartDate, 
							EndDate,
							CompanyMergeNumber,
							BillingStatus,
							CreditSubscriptionMonthlyPrice,
							CreditSubscriptionHourPrice,
							CreditsGivenInSeconds/60							AS CreditSubscriptionMinuteAmount,
							BIG_SEC_TO_TIME(CreditsGivenInSeconds) 				AS CreditsGiven,
							BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 		AS BookingTimeCharged,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeExact,
							BIG_SEC_TO_TIME(
											FLOOR(
												(
													IF(
														(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
														BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
														0
													)
												)/:roundDownToTheClosestMinuteNumberInSeconds
											)*:roundDownToTheClosestMinuteNumberInSeconds
							)													AS OverCreditsTimeCharged
				FROM (
						SELECT 	cch.`startDate`									AS StartDate,
								cch.`endDate`									AS EndDate,
								cch.`minuteAmount`*60							AS CreditsGivenInSeconds,
								cch.`monthlyPrice`								AS CreditSubscriptionMonthlyPrice,
								cch.`overCreditHourPrice`						AS CreditSubscriptionHourPrice,
								cch.`mergeNumber`								AS CompanyMergeNumber,
								cch.`hasBeenBilled`								AS BillingStatus,
								(
									SELECT (IFNULL(SUM(
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
										0)
									),0))
									FROM 		`booking` b
									WHERE 		b.`CompanyID` = :CompanyID
									AND			b.`mergeNumber` = cch.`mergeNumber`
									AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
									AND			DATE(b.`actualEndDateTime`) < cch.`endDate`
								)										AS BookingTimeChargedInSeconds
							FROM 		`companycreditshistory` cch
							INNER JOIN	`companycredits` cc
							ON 			cc.`CompanyID` = cch.`CompanyID`
							INNER JOIN 	`credits` cr
							ON 			cr.`CreditsID` = cc.`CreditsID`
							WHERE 		cch.`CompanyID` = :CompanyID
							AND 		cch.`mergeNumber` = :mergeNumber
				)													AS PeriodInformation";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':mergeNumber', $mergeNumber);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->bindValue(':roundDownToTheClosestMinuteNumberInSeconds', $roundDownToTheClosestMinuteNumberInSeconds);
	} else {
		$sql = "SELECT		StartDate, 
							EndDate,
							CompanyMergeNumber,
							BillingStatus,
							CreditSubscriptionMonthlyPrice,
							CreditSubscriptionHourPrice,
							CreditsGivenInSeconds/60							AS CreditSubscriptionMinuteAmount,
							BIG_SEC_TO_TIME(CreditsGivenInSeconds) 				AS CreditsGiven,
							BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 		AS BookingTimeCharged,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeExact,
							BIG_SEC_TO_TIME(
											IF(
												(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
												BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
												0
											)
							)													AS OverCreditsTimeCharged
				FROM (
						SELECT 	cch.`startDate`									AS StartDate,
								cch.`endDate`									AS EndDate,
								cch.`minuteAmount`*60							AS CreditsGivenInSeconds,
								cch.`monthlyPrice`								AS CreditSubscriptionMonthlyPrice,
								cch.`overCreditHourPrice`						AS CreditSubscriptionHourPrice,
								cch.`mergeNumber`								AS CompanyMergeNumber,
								cch.`hasBeenBilled`								AS BillingStatus,
								(
									SELECT (IFNULL(SUM(
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
										0)
									),0))
									FROM 		`booking` b
									WHERE 		b.`CompanyID` = :CompanyID
									AND			b.`mergeNumber` = cch.`mergeNumber`
									AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
									AND 		DATE(b.`actualEndDateTime`) < cch.`endDate`
								)										AS BookingTimeChargedInSeconds
							FROM 		`companycreditshistory` cch
							INNER JOIN	`companycredits` cc
							ON 			cc.`CompanyID` = cch.`CompanyID`
							INNER JOIN 	`credits` cr
							ON 			cr.`CreditsID` = cc.`CreditsID`
							WHERE 		cch.`CompanyID` = :CompanyID
							AND 		cch.`mergeNumber` = :mergeNumber
				)													AS PeriodInformation";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':mergeNumber', $mergeNumber);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);		
	}

	$s->execute();
	$result = $s->fetchAll(PDO::FETCH_ASSOC);

	foreach($result AS $row){
		// Get credits values
		$companyMinuteCredits = $row['CreditSubscriptionMinuteAmount'];
		if($companyMinuteCredits == NULL OR $companyMinuteCredits == ""){
			$companyMinuteCredits = 0;
		}
		$monthPrice = $row["CreditSubscriptionMonthlyPrice"];
		if($monthPrice == NULL OR $monthPrice == ""){
			$monthPrice = 0;
		}
		$hourPrice = $row["CreditSubscriptionHourPrice"];
		if($hourPrice == NULL OR $hourPrice == ""){
			$hourPrice = 0;
		}

		$mergeStatus = "Transferred From Another Company (ID=$mergeNumber)";

		// Calculate price
		$bookingTimeChargedInMinutes = convertTimeToMinutes($row['OverCreditsTimeCharged']);
		if(isSet($roundDownToTheClosestMinuteNumberInSeconds) AND $roundDownToTheClosestMinuteNumberInSeconds != 0){
			// Adapt hourprice into correct piece of our ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT (e.g. 15 min)
			$splitPricePerHourIntoThisManyPieces = 60 / ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT;
				// Rounds down
			$numberOfMinuteSlices = floor($bookingTimeChargedInMinutes / ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT);
			$slicedHourPrice = $hourPrice/$splitPricePerHourIntoThisManyPieces;
			$overFeeCostThisMonth = $numberOfMinuteSlices * $slicedHourPrice;			
		} else {
			// Get price for the exact minute amount used
			$minutePrice = $hourPrice/60;
			$overFeeCostThisMonth = $bookingTimeChargedInMinutes * $minutePrice;
		}

		$totalCost = $monthPrice+$overFeeCostThisMonth;
		$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency($overFeeCostThisMonth);
		$totalBookingCostThisMonth = convertToCurrency($totalCost);		

		$startDate = $row['StartDate'];
		$endDate = $row['EndDate'];
		$displayStartDate = convertDatetimeToFormat($startDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayEndDate = convertDatetimeToFormat($endDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

		$allPeriodsFromMergedNumber[] = array(
										'MergeStatus' => $mergeStatus,
										'MergeNumber' => $mergeNumber,
										'BillingStatus' => $row['BillingStatus'],
										'DisplayStartDate' => $displayStartDate,
										'DisplayEndDate' => $displayEndDate,
										'StartDate' => $startDate,
										'EndDate' => $endDate,
										'CreditsGiven' => convertTimeToHoursAndMinutes($row['CreditsGiven']),
										'BookingTimeCharged' => convertTimeToHoursAndMinutes($row['BookingTimeCharged']),
										'OverCreditsTimeExact' => convertTimeToHoursAndMinutes($row['OverCreditsTimeExact']),
										'OverCreditsTimeCharged' => convertTimeToHoursAndMinutes($row['OverCreditsTimeCharged']),
										'TotalBookingCostThisMonthAsParts' => $bookingCostThisMonth,
										'TotalBookingCostThisMonth' => $totalBookingCostThisMonth,
										'TotalBookingCostThisMonthJustNumber' => $totalCost
									);
	}
	$displayMergeStatus = TRUE;

	if(isSet($allPeriodsFromMergedNumber)){
		return array($allPeriodsFromMergedNumber, $displayMergeStatus);
	} else {
		return FALSE;
	}
}

// Function to calculate booking time used and the cost of that period for a company
function calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber){

	if($rightNow === TRUE){
		// Get the current credit information
		$sql = "SELECT 		IFNULL(
									cc.`altMinuteAmount`,
									cr.`minuteAmount`)		AS CreditSubscriptionMinuteAmount,
							cr.`monthlyPrice`				AS CreditSubscriptionMonthlyPrice,
							cr.`overCreditHourPrice`		AS CreditSubscriptionHourPrice
				FROM 		`companycredits` cc
				INNER JOIN 	`credits` cr
				ON 			cr.`CreditsID` = cc.`CreditsID`
				WHERE 		`companyID` = :CompanyID
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
	} else {
		// Get the credit information for the selected period (if we have it saved in companycreditshistory
		$sql = "SELECT 		`minuteAmount`				AS CreditSubscriptionMinuteAmount,
							`monthlyPrice`				AS CreditSubscriptionMonthlyPrice,
							`overCreditHourPrice`		AS CreditSubscriptionHourPrice,
							`hasBeenBilled`				AS PeriodHasBeenBilled,
							`billingDescription`		AS BillingDescription
				FROM 		`companycreditshistory`
				WHERE 		`companyID` = :CompanyID
				AND 		`startDate` = :startDate
				AND			`endDate` = :endDate
				AND			`mergeNumber` = :mergeNumber
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':startDate', $BillingStart);
		$s->bindValue(':endDate', $BillingEnd);
		$s->bindValue(':mergeNumber', $mergeNumber);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		$periodHasBeenBilled = $row['PeriodHasBeenBilled'];
		$billingDescription = $row['BillingDescription'];
	}

	// Get credits values
	$companyMinuteCredits = $row['CreditSubscriptionMinuteAmount'];
	if($companyMinuteCredits == NULL OR $companyMinuteCredits == ""){
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

	//Get completed booking history from the selected billing period
	$sql = "SELECT 		(
							BIG_SEC_TO_TIME(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
								)
							)
						)						AS BookingTimeUsed,
						(
							BIG_SEC_TO_TIME(
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
							)
						)						AS BookingTimeCharged,
						b.`startDateTime`		AS BookingStartedDatetime,
						b.`actualEndDateTime`	AS BookingCompletedDatetime,
						b.`adminNote`			AS AdminNote,
						b.`cancelMessage`		AS CancelMessage,
						b.`mergeNumber`			AS MergeNumber,
						(
							IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
						)						AS UserFirstname,
						(
							IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
						)						AS UserLastname,
						(
							IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
						)						AS UserEmail,
						(
							IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
						) 						AS MeetingRoomName
			FROM 		`booking` b
			WHERE   	b.`CompanyID` = :CompanyID
			AND 		b.`actualEndDateTime` IS NOT NULL
			AND         DATE(b.`actualEndDateTime`) >= :startDate
			AND         DATE(b.`actualEndDateTime`) < :endDate";

	if(!isSet($_SESSION['BookingHistoryDisplayWithMerged'])){
		$sql .= " AND	b.`mergeNumber` = :mergeNumber";
		$includeMerged = FALSE;
	} else {
		$includeMerged = TRUE;
	}

	$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
	$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // e.g. 1min = 60s
	$s = $pdo->prepare($sql);
	$s->bindValue(':CompanyID', $companyID);
	$s->bindValue(':startDate', $BillingStart);
	$s->bindValue(':endDate', $BillingEnd);
	if(!$includeMerged){
		$s->bindValue(':mergeNumber', $mergeNumber);
	}
	$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
	$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
	$s->execute();
	$result = $s->fetchAll(PDO::FETCH_ASSOC);

	$totalBookingTimeThisPeriod = 0;
	$totalBookingTimeUsedInPriceCalculations = 0;
	//$totalBookingTimeThisPeriodIncludingMerged = 0;
	//$totalBookingTimeUsedInPriceCalculationsIncludingMerged = 0;
	foreach($result AS $row){

		$mergeNumber = $row['MergeNumber'];

		// Format dates to display
		$startDateTime = convertDatetimeToFormat($row['BookingStartedDatetime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$endDateTime = convertDatetimeToFormat($row['BookingCompletedDatetime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		$bookingPeriod = $startDateTime . " up to " . $endDateTime;

		// Calculate time used
		$bookingTimeUsed = convertTimeToMinutes($row['BookingTimeUsed']);
		$displayBookingTimeUsed = convertTimeToHoursAndMinutes($row['BookingTimeUsed']);
		$bookingTimeUsedInPriceCalculations = convertTimeToMinutes($row['BookingTimeCharged']);
		$displayBookingTimeUsedInPriceCalculations = convertTimeToHoursAndMinutes($row['BookingTimeCharged']);

		//$totalBookingTimeThisPeriodIncludingMerged += $bookingTimeUsed;
		//$totalBookingTimeUsedInPriceCalculationsIncludingMerged += $bookingTimeUsedInPriceCalculations;
		
		if($includeMerged AND $mergeNumber == 0){
			// If we're including merged bookings, still only track time used by the non-merged
			$totalBookingTimeThisPeriod += $bookingTimeUsed;
			$totalBookingTimeUsedInPriceCalculations += $bookingTimeUsedInPriceCalculations;			
		} elseif(!$includeMerged){
			// if we're just calculating a specific period
			$totalBookingTimeThisPeriod += $bookingTimeUsed;
			$totalBookingTimeUsedInPriceCalculations += $bookingTimeUsedInPriceCalculations;			
		}

		if($row['UserLastname'] == NULL){
			$userInformation = "<deleted user>";
		} else {
			$userInformation = $row['UserLastname'] . ", " . $row['UserFirstname'] . " - " . $row['UserEmail'];
		}
		if($row['MeetingRoomName'] == NULL){
			$meetingRoomName = "<deleted room>";
		} else {
			$meetingRoomName = $row['MeetingRoomName'];
		}
		if($row['AdminNote'] == NULL){
			$adminNote = "";
		} else {
			$adminNote = $row['AdminNote'];
		}
		if($row['CancelMessage'] == NULL){
			$cancelMessage = "";
		} else {
			$cancelMessage = $row['CancelMessage'];
		}

		$bookingHistory[] = array(
									'BookingPeriod' => $bookingPeriod,
									'UserInformation' => $userInformation,
									'MeetingRoomName' => $meetingRoomName,
									'BookingTimeUsed' => $displayBookingTimeUsed,
									'BookingTimeCharged' => $displayBookingTimeUsedInPriceCalculations,
									'AdminNote' => $adminNote,
									'CancelMessage' => $cancelMessage
									);
	}
		// Calculate monthly cost (subscription + over credit charges)
	if($totalBookingTimeUsedInPriceCalculations > 0){
		if($totalBookingTimeUsedInPriceCalculations > $companyMinuteCredits){
			// Company has used more booking time than credited. Let's calculate how far over they went
			$actualTimeOverCreditsInMinutes = $totalBookingTimeUsedInPriceCalculations - $companyMinuteCredits;

			// Let's calculate cost
				// Check if user has set that price should be rounded down to x minutes (e.g. down to closest 15 minute)
			$selectedMinuteAmount = ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT;
			if(isSet($selectedMinuteAmount) AND $selectedMinuteAmount != 0){
				// Adapt hourprice into correct piece of our ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT (e.g. 15 min)
				$splitPricePerHourIntoThisManyPieces = 60 / $selectedMinuteAmount;
					// Rounds down
				$numberOfMinuteSlices = floor($actualTimeOverCreditsInMinutes / $selectedMinuteAmount);
				$slicedHourPrice = $hourPrice/$splitPricePerHourIntoThisManyPieces;
				$overFeeCostThisMonth = $numberOfMinuteSlices * $slicedHourPrice;
				$roundedDownTimeOverCreditsInMinutes = $numberOfMinuteSlices * $selectedMinuteAmount;
			} else {
				// Get price for the exact minute amount used
				$minutePrice = $hourPrice/60;
				$overFeeCostThisMonth = $actualTimeOverCreditsInMinutes * $minutePrice;
				$roundedDownTimeOverCreditsInMinutes = $actualTimeOverCreditsInMinutes;
			}
			$displayOverFeeCostThisMonth = convertToCurrency($overFeeCostThisMonth);
			$totalCost = $monthPrice+$overFeeCostThisMonth;
			$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency($overFeeCostThisMonth);
			$totalBookingCostThisMonth = convertToCurrency($totalCost);

			$companyMinuteCreditsRemaining = $companyMinuteCredits - $totalBookingTimeUsedInPriceCalculations;
			$overCreditsTimeUsed = $totalBookingTimeUsedInPriceCalculations - $companyMinuteCredits;
			$displayOverCreditsTimeUsed = convertMinutesToHoursAndMinutes($overCreditsTimeUsed);
		} else {
			$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency(0);
			$totalBookingCostThisMonth = convertToCurrency($monthPrice);
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $totalBookingTimeUsedInPriceCalculations;
		}
	} else {
		$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency(0);
		$displayOverFeeCostThisMonth = convertToCurrency(0);
		$totalBookingCostThisMonth = convertToCurrency($monthPrice);
		$companyMinuteCreditsRemaining = $companyMinuteCredits;
	}

	$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
	$displayMonthPrice = convertToCurrency($monthPrice);
	$displayTotalBookingTimeThisPeriod = convertMinutesToHoursAndMinutes($totalBookingTimeThisPeriod);
	$displayTotalBookingTimeUsedInPriceCalculationsThisPeriod = convertMinutesToHoursAndMinutes($totalBookingTimeUsedInPriceCalculations);
	if(isSet($roundedDownTimeOverCreditsInMinutes)){
		$displayTotalBookingTimeChargedWithAfterCredits = convertMinutesToHoursAndMinutes($roundedDownTimeOverCreditsInMinutes);
	}

	if(!isSet($actualTimeOverCreditsInMinutes)){
		$actualTimeOverCreditsInMinutes = "";
	}
	if(!isSet($displayOverCreditsTimeUsed)){
		$displayOverCreditsTimeUsed = "None";
	}
	if(!isSet($displayOverFeeCostThisMonth)){
		$displayOverFeeCostThisMonth = "";
	}
	if(!isSet($displayTotalBookingTimeChargedWithAfterCredits)){
		$displayTotalBookingTimeChargedWithAfterCredits = "N/A";
	}
	if(!isSet($displayTotalBookingTimeChargedWithAfterCredits)){
		$displayTotalBookingTimeChargedWithAfterCredits = "N/A";
	}
	if(!isSet($bookingHistory)){
		$bookingHistory = array();
	}
	if(!isSet($periodHasBeenBilled)){
		$periodHasBeenBilled = 0;
	}
	if(!isSet($billingDescription) OR $billingDescription == NULL){
		$billingDescription = "";
	}

	return array(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
					$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
					$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
					$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
					$displayTotalBookingTimeChargedWithAfterCredits);
}

// Function to check if user inputs for companies are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['CompanyName'])){
		$companyName = trim($_POST['CompanyName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddCompanyError'] = "Company cannot be created without a name!";
	}
	if(isSet($_POST['DateToRemove'])){
		$dateToRemove = trim($_POST['DateToRemove']);
	} else {
		$dateToRemove = ""; //This doesn't have to be set
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedCompanyName = trimExcessWhitespace($companyName);
	$validatedCompanyDateToRemove = trimExcessWhitespace($dateToRemove);

	// Do actual input validation
	if(validateString($validatedCompanyName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddCompanyError'] = "Your submitted company name has illegal characters in it.";
	}
	if(validateDateTimeString($validatedCompanyDateToRemove) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddCompanyError'] = "Your submitted date has illegal characters in it.";
	}

	// Are values actually filled in?
	if($validatedCompanyName == "" AND !$invalidInput){
		$_SESSION['AddCompanyError'] = "You need to fill in a name for the company.";	
		$invalidInput = TRUE;
	}

	// Check if input length is allowed
		// CompanyName
		// Uses same limit as display name (max 255 chars)
	$invalidCompanyName = isLengthInvalidDisplayName($validatedCompanyName);
	if($invalidCompanyName AND !$invalidInput){
		$_SESSION['AddCompanyError'] = "The company name submitted is too long.";	
		$invalidInput = TRUE;
	}

	// Check if the dateTime input we received are actually datetime
	// if the user submitted one
	if($validatedCompanyDateToRemove != ""){

		$correctFormatIfValid = correctDatetimeFormat($validatedCompanyDateToRemove);

		if (isSet($correctFormatIfValid) AND $correctFormatIfValid === FALSE AND !$invalidInput){
			$_SESSION['AddCompanyError'] = "The date you submitted did not have a correct format. Please try again.";
			$invalidInput = TRUE;
		}
		if(isSet($correctFormatIfValid) AND $correctFormatIfValid !== FALSE){
			$correctFormatIfValid = convertDatetimeToFormat($correctFormatIfValid,'Y-m-d H:i:s', 'Y-m-d');

			// Check if the (now valid) datetime we received is a future date or not
			$dateNow = getDateNow();
			if(!($correctFormatIfValid > $dateNow)){
				$_SESSION['AddCompanyError'] = "The date you submitted has already occured. Please choose a future date.";
				$invalidInput = TRUE;
			} else {
				$validatedCompanyDateToRemove = $correctFormatIfValid;
			}
		}
	}

	// Check if the company already exists (based on name).
		// only if have changed the name (edit only)
	if(isSet($_SESSION['EditCompanyOriginalName'])){
		$originalCompanyName = strtolower($_SESSION['EditCompanyOriginalName']);
		$newCompanyName = strtolower($validatedCompanyName);

		if(isSet($_SESSION['EditCompanyOriginalName']) AND $originalCompanyName == $newCompanyName){
			// Do nothing, since we haven't changed the name we're editing
		} elseif(!$invalidInput) {
			// Check if new name is taken
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
					$_SESSION['AddCompanyError'] = "There is already a company with the name: " . $validatedCompanyName . "!";
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
	}
return array($invalidInput, $validatedCompanyName, $validatedCompanyDateToRemove);
}

function refreshBookingHistory(){
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];

	// Remember information
	$NextPeriod = $_POST['nextPeriod'];
	$PreviousPeriod = $_POST['previousPeriod'];
	$BillingStart = $_POST['billingStart'];
	$BillingEnd = $_POST['billingEnd'];

	$mergedCompanies = FALSE;

	if(isSet($_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers']) AND $_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers'] != 0){
		$mergedCompanies = TRUE;
	}

	if(isSet($_SESSION['BookingHistoryCompanyMergeNumber']) AND $_SESSION['BookingHistoryCompanyMergeNumber'] != ""){
		$mergeNumber = $_SESSION['BookingHistoryCompanyMergeNumber'];
	} else {
		unset($_SESSION['BookingHistoryCompanyMergeNumber']);
		$mergeNumber = 0;
	}

	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		// Format billing dates
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";	

		// rightNow decides if we use the companycreditshistory or the credits/companycredits information
		if($NextPeriod){
			$rightNow = FALSE;
		} else {
			$rightNow = TRUE;
		}

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		// Sum up periods that are not set as billed
		list($periodsSummmedUp, $displayMergeStatus) = sumUpUnbilledPeriods($pdo, $companyID);
		if($periodsSummmedUp === FALSE){
			// No periods not set as billed
			unset($periodsSummmedUp);
		}

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error refreshing booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();
}

if(isSet($_POST['history']) AND $_POST['history'] == "Include Transferred Bookings"){
	$_SESSION['BookingHistoryDisplayWithMerged'] = TRUE;
	unset($_SESSION['BookingHistoryCompanyMergeNumber']);

	refreshBookingHistory();
}

if(isSet($_POST['history']) AND $_POST['history'] == "Exclude Transferred Bookings"){
	unset($_SESSION['BookingHistoryDisplayWithMerged']);
	unset($_SESSION['BookingHistoryCompanyMergeNumber']);

	refreshBookingHistory();
}

if(isSet($_POST['changeToMergeNumber']) AND $_POST['changeToMergeNumber'] != ""){

	$mergeNumber = $_POST['changeToMergeNumber'];
	$_SESSION['BookingHistoryCompanyMergeNumber'] = $mergeNumber;
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];

	// Get all periods (billed and not billed) from the selected merged company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		list($displayAllPeriodsFromMergedNumber, $displayMergeStatus) = displayAllPeriodsFromMergedNumber($pdo, $companyID, $mergeNumber);
		if($displayAllPeriodsFromMergedNumber === FALSE){
			// No periods found, for some reason
			unset($displayAllPeriodsFromMergedNumber);
		}
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistoryperiodsonly.html.php';
	exit();
}

if(isSet($_POST['history']) AND $_POST['history'] == "Go To This Period"){
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];

	// Get period specific information
	$BillingStart = $_POST['startDate'];
	$BillingEnd = $_POST['endDate'];
	$mergeNumber = $_POST['mergeNumber'];

	$_SESSION['BookingHistoryCompanyMergeNumber'] = $mergeNumber;

	// Get booking history for the selected company in the selected period
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// Get relevant company information
		$sql = "SELECT 	`companyID`				AS CompanyID, 
						`name`					AS CompanyName,
						DATE(`dateTimeCreated`)	AS CompanyDateCreated,
						`dateTimeCreated`		AS CompanyDateTimeCreated,
						`prevStartDate`			AS CompanyBillingDatePreviousStart,
						`startDate`				AS CompanyBillingDateStart,
						`endDate`				AS CompanyBillingDateEnd
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$_SESSION['BookingHistoryCompanyInfo'] = $row;

		$dateTimeCreated = $row['CompanyDateTimeCreated'];
		$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated,'Y-m-d H:i:s', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$lastBillingDate = $row['CompanyBillingDateEnd'];

		$_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'] = $displayDateTimeCreated;

		$CompanyName = $row['CompanyName'];

		// Format billing dates
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";			

		// An unbilled period cannot be right now.
		$rightNow = FALSE;

		// Disable "next/prev period" as this is merged and dates are weird.
		$NextPeriod = FALSE;
		$PreviousPeriod = FALSE;
		$mergedCompanies = FALSE;
		$lookingAtASpecificMergedPeriod = TRUE;

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();
}

// If admin wants to set a period as billed
if(isSet($_POST['history']) AND $_POST['history'] == "Set As Billed"){

	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];

	// Remember information
	$NextPeriod = $_POST['nextPeriod'];
	$PreviousPeriod = $_POST['previousPeriod'];
	$BillingStart = $_POST['billingStart'];
	$BillingEnd = $_POST['billingEnd'];

	if(isSet($_SESSION['BookingHistoryCompanyMergeNumber']) AND $_SESSION['BookingHistoryCompanyMergeNumber'] != ""){
		$mergeNumber = $_SESSION['BookingHistoryCompanyMergeNumber'];
		$mergedCompanies = FALSE;
		$lookingAtASpecificMergedPeriod = TRUE;
	} else {
		unset($_SESSION['BookingHistoryCompanyMergeNumber']);
		$mergeNumber = 0;
	}

	if(isSet($_POST['billingDescription'])){
		$billingDescriptionAdminAddition = trimExcessWhitespaceButLeaveLinefeed($_POST['billingDescription']);
	}

	if(!isSet($billingDescriptionAdminAddition) OR $billingDescriptionAdminAddition == ""){
		$billingDescriptionAdminAddition = "No additional information submitted";
	}

	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		// Format billing dates
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";	

		$rightNow = FALSE;

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		// Update period as billed relevant information and admin inputs

		$dateTimeNow = getDatetimeNow();
		$displayDateTimeNow = convertDatetimeToFormat($dateTimeNow , 'Y-m-d H:i:s', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$billingDescriptionInformation = 	"This period was Set As Billed on " . $displayDateTimeNow .
											" by the user " . $_SESSION['LoggedInUserName'] .
											".\nAt that time the company had produced a total booking time of: " . $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod .
											", with a credit given of: " . $displayCompanyCredits . " resulting in excess use of: " . $displayOverCreditsTimeUsed . 
											" (billed as " . $displayTotalBookingTimeChargedWithAfterCredits . ").\nThe montly fee was set as " . $displayMonthPrice . 
											".\nResulting in a total billing cost that period of " . $bookingCostThisMonth . " = " . $totalBookingCostThisMonth . 
											".\nAdditional information submitted by Admin:\n" . $billingDescriptionAdminAddition;
		if(substr($billingDescriptionInformation,-1) != "."){
			$billingDescriptionInformation . ".";
		}

		$sql = "UPDATE 	`companycreditshistory`
				SET		`hasBeenBilled` = 1,
						`billingDescription` = :billingDescription
				WHERE   `CompanyID` = :CompanyID
				AND	    `startDate` = :startDate
				AND		`endDate` = :endDate
				AND		`mergeNumber` = :mergeNumber";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':startDate', $BillingStart);
		$s->bindValue(':endDate', $BillingEnd);
		$s->bindValue(':mergeNumber', $mergeNumber);
		$s->bindValue(':billingDescription', $billingDescriptionInformation);
		$s->execute();

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error setting period as Billed: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// We've clearly billed it now, but the retrieved info hasn't seen that yet, so we update it.
	$periodHasBeenBilled = 1;
	$billingDescription = $billingDescriptionInformation;
	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();	
}

// If admin wants to see the booking history of the period after the currently shown one
if(isSet($_POST['history']) AND $_POST['history'] == "Next Period"){

	// Get company information
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];
	$companyCreationDate = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateCreated'];
	$companyBillingDateEnd = $_SESSION['BookingHistoryCompanyInfo']['CompanyBillingDateEnd'];
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$newDate = DateTime::createFromFormat("Y-m-d", $companyCreationDate);
	$dayNumberToKeep = $newDate->format("d");

	$oldStartDate = $_SESSION['BookingHistoryStartDate'];
	$oldEndDate = $_SESSION['BookingHistoryEndDate'];
	$startDate = $oldEndDate;
	$endDate = addOneMonthToPeriodDate($dayNumberToKeep, $oldEndDate);

	// Save our newly set start/end dates
	$_SESSION['BookingHistoryStartDate'] = $startDate;
	$_SESSION['BookingHistoryEndDate'] = $endDate;

	$mergedCompanies = FALSE;

	if(isSet($_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers']) AND $_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers'] != 0){
		$mergedCompanies = TRUE;
	}

	if(isSet($_SESSION['BookingHistoryCompanyMergeNumber']) AND $_SESSION['BookingHistoryCompanyMergeNumber'] != ""){
		$mergeNumber = $_SESSION['BookingHistoryCompanyMergeNumber'];
	} else {
		unset($_SESSION['BookingHistoryCompanyMergeNumber']);
		$mergeNumber = 0;
	}

	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		if($endDate == $companyBillingDateEnd){
			$NextPeriod = FALSE;
		} else {
			$NextPeriod = TRUE;
		}
		$PreviousPeriod = TRUE;

		// Format billing dates
		$BillingStart = $startDate;
		$BillingEnd = $endDate;
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";	

		// rightNow decides if we use the companycreditshistory or the credits/companycredits information
		if($NextPeriod){
			$rightNow = FALSE;
		} else {
			$rightNow = TRUE;
		}

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		// Sum up periods that are not set as billed
		list($periodsSummmedUp, $displayMergeStatus) = sumUpUnbilledPeriods($pdo, $companyID);
		if($periodsSummmedUp === FALSE){
			// No periods not set as billed
			unset($periodsSummmedUp);
		}

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();
}

// If admin wants to see the booking history of the period before the currently shown one
if(	(isSet($_POST['history']) AND $_POST['history'] == "Previous Period") OR 
		(isSet($_POST['history']) AND $_POST['history'] == "First Period")){

	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);	
	// Get company information
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];		
	$companyCreationDate = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateCreated'];
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$newDate = DateTime::createFromFormat("Y-m-d", $companyCreationDate);
	$dayNumberToKeep = $newDate->format("d");

	// Set correct period based on what user clicked.
	if(isSet($_POST['history']) AND $_POST['history'] == "Previous Period"){
		$oldStartDate = $_SESSION['BookingHistoryStartDate'];
		$oldEndDate = $_SESSION['BookingHistoryEndDate'];
		$startDate = removeOneMonthFromPeriodDate($dayNumberToKeep, $oldStartDate);
		$endDate = $oldStartDate;
	} elseif(isSet($_POST['history']) AND $_POST['history'] == "First Period"){
		$startDate = $companyCreationDate;
		$endDate = addOneMonthToPeriodDate($dayNumberToKeep, $companyCreationDate);
	}
	
	// Save our newly set start/end dates
	$_SESSION['BookingHistoryStartDate'] = $startDate;
	$_SESSION['BookingHistoryEndDate'] = $endDate;

	$mergedCompanies = FALSE;

	if(isSet($_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers']) AND $_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers'] != 0){
		$mergedCompanies = TRUE;
	}

	if(isSet($_SESSION['BookingHistoryCompanyMergeNumber']) AND $_SESSION['BookingHistoryCompanyMergeNumber'] != ""){
		$mergeNumber = $_SESSION['BookingHistoryCompanyMergeNumber'];
	} else {
		unset($_SESSION['BookingHistoryCompanyMergeNumber']);
		$mergeNumber = 0;
	}

	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		if($startDate == $companyCreationDate){
			$PreviousPeriod = FALSE;
		} else {
			$PreviousPeriod = TRUE;
		}
		$NextPeriod = TRUE;

		// Format billing dates
		$BillingStart = $startDate;
		$BillingEnd =  $endDate;
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";			

		$rightNow = FALSE;

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		// Sum up periods that are not set as billed
		list($periodsSummmedUp, $displayMergeStatus) = sumUpUnbilledPeriods($pdo, $companyID);
		if($periodsSummmedUp === FALSE){
			// No periods not set as billed
			unset($periodsSummmedUp);
		}

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];

	var_dump($_SESSION); // TO-DO: Remove before uploading.

	include_once 'bookinghistory.html.php';
	exit();
}

// Redirect to the proper period and company when given a link
if (	(isSet($_GET['companyID']) AND isSet($_GET['BillingStart']) AND isSet($_GET['BillingEnd'])) OR
		isSet($_SESSION['refreshBookingHistoryFromLink'])
	){

	// Save GET parameters then load a clean URL
		// Link example IN: http://localhost/admin/companies/?companyID=2&BillingStart=2017-05-15&BillingEnd=2017-06-15
		// Link out: http://localhost/admin/companies/
	if(isSet($_SESSION['refreshBookingHistoryFromLink'])){
		list($companyID, $BillingStart, $BillingEnd) = $_SESSION['refreshBookingHistoryFromLink'];		
		unset($_SESSION['refreshBookingHistoryFromLink']);
	} else {
		$companyID = $_GET['companyID'];
		$BillingStart = $_GET['BillingStart'];
		$BillingEnd =  $_GET['BillingEnd'];
		$_SESSION['refreshBookingHistoryFromLink'] = array($companyID, $BillingStart, $BillingEnd);
		header("Location: .");
		exit();
	}

	$abortLink = FALSE;

	if(isSet($companyID, $BillingStart, $BillingEnd) AND $companyID != "" AND $BillingStart != "" AND $BillingEnd != ""){
		// First check if the dates in the get parameters are valid period dates for the company
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$sql = "SELECT 	COUNT(*)
					FROM	`companycreditshistory`
					WHERE	`companyID` = :companyID
					AND		`startDate` = :startDate
					AND		`endDate` = :endDate
					AND		`mergeNumber` = 0
					LIMIT 	1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':companyID', $companyID);
			$s->bindValue(':startDate', $BillingStart);
			$s->bindValue(':endDate', $BillingEnd);
			$s->execute();
			$row = $s->fetch();

			if($row[0] == 0){
				// The dates submitted are not valid. At least not stored in the database.
				$abortLink = TRUE;
			}
		}
		catch (PDOException $e)
		{
			$error = 'Error checking if link parameters are a valid period: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$abortLink = TRUE;
	}

	if($abortLink){
		$_SESSION['CompanyUserFeedback'] = 	"The link you used did not correspond to a correct period and/or company." .
											"\nCould therefore not retrieve any booking information.";

		header('Location: .');
		exit();
	}

	// Get booking history for the selected company in the selected period
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// Get relevant company information
		$sql = "SELECT 	`companyID`				AS CompanyID, 
						`name`					AS CompanyName,
						DATE(`dateTimeCreated`)	AS CompanyDateCreated,
						`dateTimeCreated`		AS CompanyDateTimeCreated,
						`prevStartDate`			AS CompanyBillingDatePreviousStart,
						`startDate`				AS CompanyBillingDateStart,
						`endDate`				AS CompanyBillingDateEnd
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$_SESSION['BookingHistoryCompanyInfo'] = $row;

		$dateTimeCreated = $row['CompanyDateTimeCreated'];
		$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated,'Y-m-d H:i:s', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$lastBillingDate = $row['CompanyBillingDateEnd'];

		$_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'] = $displayDateTimeCreated;

		$CompanyName = $row['CompanyName'];

		// Format billing dates
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";			

		// Save our newly set start/end dates
		$_SESSION['BookingHistoryStartDate'] = $BillingStart;
		$_SESSION['BookingHistoryEndDate'] = $BillingEnd;

		// Check if date submitted is current period
		if($BillingEnd != $lastBillingDate){
			$rightNow = FALSE;
		} else {
			$rightNow = TRUE;
		}

		// Check if there are any periods before/after this
		if($BillingEnd >= $lastBillingDate){
			$NextPeriod = FALSE;
		} else {
			$NextPeriod = TRUE;
		}
		if($BillingStart <= $dateTimeCreated){
			$PreviousPeriod = FALSE;
		} else {
			$PreviousPeriod = TRUE;
		}

		$mergeNumber = 0;

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();
}

// If admin wants to see the booking history of the selected company
if ((isSet($_POST['action']) AND $_POST['action'] == "Booking History") OR 
	(isSet($_POST['history']) AND $_POST['history'] == "Last Period") OR
	(isSet($_POST['history']) AND $_POST['history'] == "Display Default")
	){

	if(isSet($_SESSION['BookingHistoryCompanyInfo'])){
		$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	} else {
		$companyID = $_POST['CompanyID'];
	}

	if(	(isSet($_POST['action']) AND $_POST['action'] == "Booking History") OR 
		(isSet($_POST['history']) AND $_POST['history'] == "Display Default")
		){
		unset($_SESSION['BookingHistoryCompanyMergeNumber']);
	}

	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// Get relevant company information
		$sql = "SELECT 	`companyID`				AS CompanyID, 
						`name`					AS CompanyName,
						DATE(`dateTimeCreated`)	AS CompanyDateCreated,
						`dateTimeCreated`		AS CompanyDateTimeCreated,
						`prevStartDate`			AS CompanyBillingDatePreviousStart,
						`startDate`				AS CompanyBillingDateStart,
						`endDate`				AS CompanyBillingDateEnd,
						(
							SELECT 	COUNT(DISTINCT `mergeNumber`)
							FROM	`companycreditshistory`
							WHERE	`companyID` = :CompanyID
							AND		`mergeNumber` <> 0
							LIMIT 	1
						)						AS CompanyMergeNumbers
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$_SESSION['BookingHistoryCompanyInfo'] = $row;

		$mergedCompanies = FALSE;

		if($row['CompanyMergeNumbers'] > 0){
			$sql = "SELECT 	DISTINCT `mergeNumber`
					FROM	`companycreditshistory`
					WHERE	`companyID` = :CompanyID
					AND		`mergeNumber` <> 0";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $companyID);
			$s->execute();
			$mergeNumbers = $s->fetchAll();
			$_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers'] = $mergeNumbers;

			$mergedCompanies = TRUE;
		}

		$dateTimeCreated = $row['CompanyDateTimeCreated'];
		$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated,'Y-m-d H:i:s', DATE_DEFAULT_FORMAT_TO_DISPLAY);

		$_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'] = $displayDateTimeCreated;

		$CompanyName = $row['CompanyName'];

		if($row['CompanyBillingDatePreviousStart'] == NULL){
			$PreviousPeriod = FALSE;
		} else {
			$PreviousPeriod = TRUE;
		}
		$NextPeriod = FALSE;

		// Format billing dates
		$BillingStart = $row['CompanyBillingDateStart'];
		$BillingEnd =  $row['CompanyBillingDateEnd'];
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " up to " . $displayBillingEnd . ".";

		// Save booking period dates
		$_SESSION['BookingHistoryStartDate'] = $BillingStart;
		$_SESSION['BookingHistoryEndDate'] = $BillingEnd;

		$rightNow = TRUE;

		if(isSet($_SESSION['BookingHistoryCompanyMergeNumber']) AND $_SESSION['BookingHistoryCompanyMergeNumber'] != ""){
			$mergeNumber = $_SESSION['BookingHistoryCompanyMergeNumber'];
		} else {
			unset($_SESSION['BookingHistoryCompanyMergeNumber']);
			$mergeNumber = 0;
		}

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining, $actualTimeOverCreditsInMinutes, 
				$periodHasBeenBilled, $billingDescription, $displayTotalBookingTimeUsedInPriceCalculationsThisPeriod, 
				$displayTotalBookingTimeChargedWithAfterCredits)
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow, $mergeNumber);

			// Sum up periods that are not set as billed
		list($periodsSummmedUp, $displayMergeStatus) = sumUpUnbilledPeriods($pdo, $companyID);
		if($periodsSummmedUp === FALSE){
			// No periods not set as billed
			unset($periodsSummmedUp);
		}

		$pdo = NULL;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'bookinghistory.html.php';
	exit();
}

// If admin wants to activate a registered company
if (isSet($_POST['action']) AND $_POST['action'] == 'Activate') {
	// Update selected company in database to be active
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'UPDATE 	`company`
				SET		`isActive` = 1,
						`removeAtDate` = NULL
				WHERE 	`companyID` = :CompanyID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->execute();

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error activating company: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// If admin wants to merge two companies
if ((isSet($_POST['action']) and $_POST['action'] == 'Merge') OR 
	(isSet($_SESSION['refreshMergeCompany']) AND $_SESSION['refreshMergeCompany'])
	){

	unset($_SESSION['refreshMergeCompany']);

	if((isSet($_POST['CompanyID']) AND !empty($_POST['CompanyID'])) OR isSet($_SESSION['MergeCompanySelectedCompanyID'])){

		if(!isSet($_SESSION['MergeCompanySelectedCompanyID'])){
			$_SESSION['MergeCompanySelectedCompanyID'] = $_POST['CompanyID'];
		}

		$companyID = $_SESSION['MergeCompanySelectedCompanyID'];

		if(isSet($_POST['CompanyName']) AND $_POST['CompanyName'] != ""){
			$mergingCompanyName = $_POST['CompanyName'];
		} else {
			$mergingCompanyName = "N/A";
		}

		if(isSet($_SESSION['MergeCompanySelectedCompanyID2'])){
			$selectedCompanyIDToMergeWith = $_SESSION['MergeCompanySelectedCompanyID2'];
		}

		// Get companies
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT	`CompanyID`		AS CompanyID,
							`name`			AS CompanyName
					FROM	`company`
					WHERE 	`companyID` != :CompanyID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $companyID);
			$s->execute();
			$companies = $s->fetchAll(PDO::FETCH_ASSOC);

			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error getting list of companies: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

		var_dump($_SESSION); // TO-DO: Remove before uploading

		include_once 'merge.html.php';
		exit();
	} else {
		$_SESSION['CompanyUserFeedback'] = "Could not retrieve information to merge this company.";
	}
}

// If admin wants to confirm what two companies to merge
if (isSet($_POST['action']) and $_POST['action'] == 'Confirm Merge'){

	// Check that we have a secondary company to merge into submitted
	if(!isSet($_POST['mergingCompanyID']) OR (isSet($_POST['mergingCompanyID']) AND empty($_POST['mergingCompanyID']))){
		$_SESSION['MergeCompanyError'] = "You cannot merge two companies without choosing a secondary company.";
		$_SESSION['refreshMergeCompany'] = TRUE;
		header("Location: .");
		exit();
	}

	$_SESSION['MergeCompanySelectedCompanyID2'] = $_POST['mergingCompanyID'];

	// Check that we have a password submitted
	if(!isSet($_POST['password']) OR (isSet($_POST['password']) AND empty($_POST['password']))){
		$_SESSION['MergeCompanyError'] = "You cannot merge two companies without submitting your password.";
		$_SESSION['refreshMergeCompany'] = TRUE;
		header("Location: .");
		exit();
	}

	$password = $_POST['password'];
	$hashedPassword = hashPassword($password);

	// Check if password is correct
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 	`password`	AS RealPassword
				FROM 	`user`
				WHERE	`userID` = :UserID
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
		$s->execute();

		$row = $s->fetch(PDO::FETCH_ASSOC);
		$realPassword = $row['RealPassword'];
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error confirming password: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	if($hashedPassword != $realPassword){
		$pdo = null;
		$_SESSION['MergeCompanyError'] = "The password you submitted is incorrect.";
		$_SESSION['refreshMergeCompany'] = TRUE;
		header("Location: .");
		exit();
	}

	// Password is correct. Let's transfer all employees and booking history to the new company
	if(	isSet($_SESSION['MergeCompanySelectedCompanyID']) AND !empty($_SESSION['MergeCompanySelectedCompanyID']) AND
		isSet($_SESSION['MergeCompanySelectedCompanyID2']) AND !empty($_SESSION['MergeCompanySelectedCompanyID2']) AND
		$_SESSION['MergeCompanySelectedCompanyID'] != $_SESSION['MergeCompanySelectedCompanyID2'])
	{
		// We have two company IDs and can start the merging process
		try
		{
			// Get all the info from the company we're merging and deleting, to keep track of the correct values.
			$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
			$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 5min = 300s
			$sql = "SELECT 		c.`CompanyID`				AS TheCompanyID,
								c.`name`					AS OldCompanyName,
								(
									SELECT 	`name`
									FROM	`company`
									WHERE 	`CompanyID` = :newCompanyID
								)							AS MergeIntoCompanyName,
								c.`startDate`				AS StartDate,
								c.`endDate`					AS EndDate,
								cr.`minuteAmount`			AS CreditsGivenInMinutes,
								cr.`monthlyPrice`			AS MonthlyPrice,
								cr.`overCreditHourPrice`	AS HourPrice,
								cc.`altMinuteAmount`		AS AlternativeAmount,
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
									WHERE 		b.`CompanyID` = :oldCompanyID
									AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
									AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
									AND			b.`mergeNumber` = 0
								)							AS BookingTimeThisPeriod
					FROM 		`company` c
					INNER JOIN 	`companycredits` cc
					ON 			cc.`CompanyID` = c.`CompanyID`
					INNER JOIN 	`credits` cr
					ON			cr.`CreditsID` = cc.`CreditsID`
					WHERE		c.`CompanyID` = :oldCompanyID
					LIMIT 		1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
			$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
			$s->bindValue(':oldCompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':newCompanyID', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$dateTimeNow = getDatetimeNow();

			if($row['AlternativeAmount'] == NULL){
				$creditsGivenInMinutes = $row['CreditsGivenInMinutes'];
			} else {
				$creditsGivenInMinutes = $row['AlternativeAmount'];
			}

			$companyID = $row['TheCompanyID'];
			$startDate = $row['StartDate'];
			$endDate = $row['EndDate'];
			$monthlyPrice = $row['MonthlyPrice'];
			$hourPrice = $row['HourPrice'];
			$bookingTimeUsedThisMonth = $row['BookingTimeThisPeriod'];
			$bookingTimeUsedThisMonthInMinutes = convertTimeToMinutes($bookingTimeUsedThisMonth);
			$displayTotalBookingTimeThisPeriod = convertMinutesToHoursAndMinutes($bookingTimeUsedThisMonthInMinutes);
			$displayCompanyCredits = convertMinutesToHoursAndMinutes($creditsGivenInMinutes);

			$setAsBilled = FALSE;

			if($bookingTimeUsedThisMonthInMinutes < $creditsGivenInMinutes){
				if($monthlyPrice == 0 OR $monthlyPrice == NULL){
					// Company had no fees to pay this month
					$setAsBilled = TRUE;
				}
			}

			$oldCompanyName = $row['OldCompanyName'];
			$mergeIntoCompanyName = $row['MergeIntoCompanyName'];

			// Begin all the SQL queries we need to go through to merge the two companies.
			$pdo->beginTransaction();

			// These updates would cause an duplicate key error on an update, but we ignore that
			// and just update the users that can be updated (not already employed in the new company)
			$sql = 'UPDATE IGNORE	`employee`
					SET				`CompanyID` = :CompanyID2
					WHERE			`CompanyID` = :CompanyID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();

			$currentDate = getDateNow();
			$mergeMessage = "This booking originally belonged to the company: " . $oldCompanyName .
							"\nIt was merged into the company: " . $mergeIntoCompanyName . 
							" at " . convertDatetimeToFormat($currentDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

			// Update bookings to transfer them to the new company. Also keep a record that it was merged.
			$sql = 'UPDATE 	`booking`
					SET		`CompanyID` = :CompanyID2,
							`adminNote` = CONCAT_WS("\n\n",`adminNote`, :mergeMessage),
							`mergeNumber` = :CompanyID
					WHERE	`CompanyID` = :CompanyID
					AND		`mergeNumber` = 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':mergeMessage', $mergeMessage);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();

			// Update previously merged bookings also
			$sql = 'UPDATE 	`booking`
					SET		`CompanyID` = :CompanyID2,
							`adminNote` = CONCAT_WS("\n\n",`adminNote`, :mergeMessage)
					WHERE	`CompanyID` = :CompanyID
					AND		`mergeNumber` <> 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':mergeMessage', $mergeMessage);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();

			// Add the current credits as companycreditshistory for the old company, before transferring it to the new company
			$sql = "INSERT INTO `companycreditshistory`
					SET			`CompanyID` = " . $companyID . ",
								`startDate` = '" . $startDate . "',
								`endDate` = '" . $endDate . "',
								`minuteAmount` = " . $creditsGivenInMinutes . ",
								`monthlyPrice` = " . $monthlyPrice . ",
								`overCreditHourPrice` = " . $hourPrice;

			if($setAsBilled){
				$billingDescriptionInformation = 	"This period was Set As Billed automatically during a company merge due to there being no fees.\n" .
													"At that time the company had produced a total booking time of: " . $displayTotalBookingTimeThisPeriod .
													", with a credit given of: " . $displayCompanyCredits . " and a monthly fee of " . convertToCurrency(0) . ".";							
				$sql .= ", 	`hasBeenBilled` = 1,
							`billingDescription` = '" . $billingDescriptionInformation . "'";
			}
			$pdo->exec($sql);

			// Update companycreditshistory to be a part of the new company, but mark it as a merged history.
			$sql = 'UPDATE 	`companycreditshistory`
					SET		`CompanyID` = :CompanyID2,
							`mergeNumber` = :CompanyID
					WHERE	`CompanyID` = :CompanyID
					AND		`mergeNumber` = 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();

			// Update previously merged companycreditshistory also
			$sql = 'UPDATE 	`companycreditshistory`
					SET		`CompanyID` = :CompanyID2
					WHERE	`CompanyID` = :CompanyID
					AND		`mergeNumber` <> 0';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['MergeCompanySelectedCompanyID2']);
			$s->execute();
			
			// Deleting company will cascade to companycredits(, companycreditshistory) and employees.
			$sql = 'DELETE FROM `company`
					WHERE		`CompanyID` = :CompanyID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['MergeCompanySelectedCompanyID']);
			$s->execute();

			$pdo->commit();
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo->rollback();
			$pdo = null;
			$error = 'Error merging companies: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

		$_SESSION['CompanyUserFeedback'] = 	"Successfully merged the company: " . $oldCompanyName . 
											"\nInto the company: " . $mergeIntoCompanyName;

		// Add a log event that the companies merged
		try
		{
			// Save a description with information about the meeting room that was removed
			$description = 	"The company: " . $oldCompanyName . 
							"\nHas been merged into the company: " . $mergeIntoCompanyName .
							"\nThis transferred all employees and the company's booking history." .
							"\nIt was merged by: " . $_SESSION['LoggedInUserName'];

			$pdo = connect_to_db();
			$pdo->beginTransaction();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Company Merged'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $description);
			$s->execute();

			$description = 	"The company: " . $oldCompanyName . 
							" no longer exists due to being merged." .
							"\nIt was removed by: " . $_SESSION['LoggedInUserName'];

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Company Removed'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $description);
			$s->execute();

			$description = 	"The employees in the company: " . $oldCompanyName . 
							" no longer exists due to being merged." .
							"\nIt was merged by: " . $_SESSION['LoggedInUserName'];

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Employee Removed'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $description);
			$s->execute();

			$description = 	"The employees in the company: " . $oldCompanyName . 
							" were added into the company: " . $mergeIntoCompanyName .
							" due to the companies being merged." .
							"\nIt was merged by: " . $_SESSION['LoggedInUserName'];

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Employee Added'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $description);
			$s->execute();
			
			$pdo->commit();

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
	} else {
		$_SESSION['CompanyUserFeedback'] = "Failed to merge the two selected companies.";
	}

	$pdo = null;
	clearMergeCompanySessions();
	header("Location: .");
	exit();
}

// If admin wants to remove a company from the database
if(isSet($_POST['action']) and $_POST['action'] == 'Delete'){

	$companyID = $_POST['CompanyID'];
	$companyName = $_POST['CompanyName'];

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'confirmdelete.html.php';
	exit();
}

if(isSet($_POST['confirmdelete']) AND $_POST['confirmdelete'] == "Yes, Delete The Company"){

	if(	(!isSet($_POST['CompanyID'], $_POST['CompanyName'])) OR 
		(isSet($_POST['CompanyID'], $_POST['CompanyName']) AND ($_POST['CompanyID'] == "" OR $_POST['CompanyName'] == ""))){
		$_SESSION['CompanyUserFeedback'] = "Could not delete the company due to a missing identifier.";

		// Load company list webpage with updated database
		header('Location: .');
		exit();
	}

	$companyID = $_POST['CompanyID'];
	$companyName = $_POST['CompanyName'];

	// Check if password is submitted
	if(!isSet($_POST['password']) OR (isSet($_POST['password']) AND $_POST['password'] == "")){

		$wrongPassword = "You need to fill in your password.";

		var_dump($_SESSION); // TO-DO: Remove before uploading

		include_once 'confirmdelete.html.php';
		exit();
	}

	$submittedPassword = $_POST['password'];
	$hashedPassword = hashPassword($submittedPassword);

	// Check if password is correct
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT	`password`
				FROM	`user`
				WHERE	`userID` = :userID
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
		$s->execute();

		$correctPassword = $s->fetchColumn();

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error confirming password: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	if($hashedPassword != $correctPassword){
		$wrongPassword = "The password you submitted is incorrect.";

		var_dump($_SESSION); // TO-DO: Remove before uploading

		include_once 'confirmdelete.html.php';
		exit();
	}

	// Remove the company's active/future bookings
	// The rest will be set companyID to null from FK being deleted
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$pdo->beginTransaction();
		$sql = 'DELETE FROM `booking` 
				WHERE 		`companyID` = :CompanyID
				AND			`dateTimeCancelled` IS NULL
				AND			`actualEndDateTime` IS NULL
				AND			`endDateTime` > CURRENT_TIMESTAMP';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$pdo->rollback();
		$error = 'Error deleting active bookings: ' . $e->getMessage();
		$pdo = null;
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	// Delete selected company from database
	try
	{
		$sql = 'DELETE FROM `company` 
				WHERE 		`companyID` = :CompanyID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$pdo->rollback();
		$error = 'Error deleting company: ' . $e->getMessage();
		$pdo = null;
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	// Add a log event that a company was removed
	try
	{
		// Save a description with information about the meeting room that was removed
		$description = 	"The company: $companyName no longer exists." .
						"\nIt was removed by: " . $_SESSION['LoggedInUserName'];

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Company Removed'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
		$s->execute();

		$pdo->commit();

		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$pdo->rollback();
		$error = 'Error adding log event to database: ' . $e->getMessage();
		$pdo = null;
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	$_SESSION['CompanyUserFeedback'] = "Successfully removed the company $companyName!";

	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

if(isSet($_POST['confirmdelete']) AND $_POST['confirmdelete'] == "No, Cancel The Delete"){

	$_SESSION['CompanyUserFeedback'] = "Cancelled the deletion process.";

	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add a company to the database
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Create Company') OR 
	(isSet($_SESSION['refreshAddCompany']) AND $_SESSION['refreshAddCompany'])
	){

	// Check if it was a user input or a forced refresh
	if(isSet($_SESSION['refreshAddCompany']) AND $_SESSION['refreshAddCompany']){
		//	Ackowledge that we have refreshed
		unset($_SESSION['refreshAddCompany']);
	}

	// Set initial values
	$CompanyName = '';

	// Set always correct values
	$pageTitle = 'New Company';
	$button = 'Add Company';
	$CompanyID = '';

	if(isSet($_SESSION['AddCompanyCompanyName'])){
		$CompanyName = $_SESSION['AddCompanyCompanyName'];
		unset($_SESSION['AddCompanyCompanyName']);
	}

	// We want a reset all fields button while adding a new company
	$reset = 'reset';

	// We don't need to see date to remove when adding a new company
	$ShowDateToRemove = FALSE;

	var_dump($_SESSION); // TO-DO: Remove before uploading

	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// if admin wants to edit company information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR 
	(isSet($_SESSION['refreshEditCompany']) AND $_SESSION['refreshEditCompany'])
	){

	// Check if it was a user input or a forced refresh
	if(isSet($_SESSION['refreshEditCompany']) AND $_SESSION['refreshEditCompany']){
		//	Acknowledge that we have refreshed
		unset($_SESSION['refreshEditCompany']);

		// Get values we had before the refresh
		if(isSet($_SESSION['EditCompanyChangedName'])){
			$CompanyName = $_SESSION['EditCompanyChangedName'];
		} else {
			$CompanyName = '';
		}

		if(isSet($_SESSION['EditCompanyCompanyID'])){
			$CompanyID = $_SESSION['EditCompanyCompanyID'];
		}

	} else {
		// Make sure we don't have old values in memory
		clearEditCompanySessions();
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
					WHERE 	`companyID` = :CompanyID';
			$s = $pdo->prepare($sql);
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
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$CompanyName = $row['name'];
		$DateToRemove = $row['removeAtDate'];
		$CompanyID = $row['companyID'];

		if(!isSet($DateToRemove) OR $DateToRemove == NULL){
			$DateToRemove = '';
		}

		$_SESSION['EditCompanyOriginalName'] = $CompanyName;
		$_SESSION['EditCompanyOriginalRemoveDate'] = $DateToRemove;
		$_SESSION['EditCompanyCompanyID'] = $CompanyID;
	}

	// Display original values
	$originalCompanyName = $_SESSION['EditCompanyOriginalName'];
	$originalDateToDisplay = convertDatetimeToFormat($_SESSION['EditCompanyOriginalRemoveDate'] , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	if(isSet($_SESSION['EditCompanyChangedRemoveDate'])){
		$DateToRemove = $_SESSION['EditCompanyChangedRemoveDate'];
	} else {
		$DateToRemove = $originalDateToDisplay;
	}

	// Set always correct values
	$pageTitle = 'Edit Company';
	$button = 'Edit Company';

	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	// Want to see date to remove while editing
	$ShowDateToRemove = TRUE;

	var_dump($_SESSION); // TO-DO: Remove before uploading

	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the company
if(isSet($_POST['action']) AND $_POST['action'] == 'Add Company'){

	list($invalidInput, $validatedCompanyName, $validatedCompanyDateToRemove) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){
		$_SESSION['AddCompanyCompanyName'] = $validatedCompanyName;

		$_SESSION['refreshAddCompany'] = TRUE;
		header('Location: .');
		exit();	
	}

	// Add the company to the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$startPeriodDate = getDateNow();
		date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
		$newDate = DateTime::createFromFormat("Y-m-d", $startPeriodDate);
		$dayNumberToKeep = $newDate->format("d");
		$endPeriodDate = addOneMonthToPeriodDate($dayNumberToKeep, $startPeriodDate);

		$pdo = connect_to_db();
		$sql = 'INSERT INTO `company` 
				SET			`name` = :CompanyName,
							`startDate` = :startPeriodDate,
							`endDate` = :endPeriodDate';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyName', $validatedCompanyName);
		$s->bindValue(':startPeriodDate', $startPeriodDate);
		$s->bindValue(':endPeriodDate', $endPeriodDate);
		$s->execute();

		$companyID = $pdo->lastInsertId();
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted company to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['CompanyUserFeedback'] = "Successfully added the company: " . $validatedCompanyName . ".";

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
		$s->bindValue(':CompanyID', $companyID);
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
		// Save a description with information about the meeting room that was added
		$logEventdescription = "The company: " . $validatedCompanyName . " was added by: " . $_SESSION['LoggedInUserName'];

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Company Created'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventdescription);
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

	clearAddCompanySessions();

	// Load companies list webpage with new company
	header('Location: .');
	exit();
}

// Perform the actual database update of the edited information
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit Company'))
{
	list($invalidInput, $validatedCompanyName, $validatedCompanyDateToRemove) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){
		$_SESSION['EditCompanyChangedName'] = $validatedCompanyName;
		$_SESSION['EditCompanyChangedRemoveDate'] = $validatedCompanyDateToRemove;

		$_SESSION['refreshEditCompany'] = TRUE;
		header('Location: .');
		exit();	
	}

	// Check if there has been any changes
	$NumberOfChanges = 0;

	if(	isSet($_SESSION['EditCompanyOriginalName']) AND 
		$_SESSION['EditCompanyOriginalName'] != $validatedCompanyName){
		$NumberOfChanges++;
	}

	if(	isSet($_SESSION['EditCompanyOriginalRemoveDate']) AND 
		$_SESSION['EditCompanyOriginalRemoveDate'] != $validatedCompanyDateToRemove){
		$NumberOfChanges++;
	}

	// Give feedback on to user based on what we do
	// No change or update
	if($NumberOfChanges > 0){
		// Update selected company with the new information
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'UPDATE 	`company` 
					SET		`removeAtDate` = :removeAtDate,
							`name` = :name
					WHERE 	`companyID` = :CompanyID';

			if ($validatedCompanyDateToRemove == ''){
				$validatedCompanyDateToRemove = null;
			}

			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_POST['CompanyID']);
			$s->bindValue(':removeAtDate', $validatedCompanyDateToRemove);
			$s->bindValue(':name', $validatedCompanyName);
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

		$_SESSION['CompanyUserFeedback'] = "Successfully updated the company: " . $validatedCompanyName . ".";		
	} else {
		$_SESSION['CompanyUserFeedback'] = "No changes were made to the company: " . $validatedCompanyName . ".";
	}

	clearEditCompanySessions();

	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

// if admin wants to cancel the date to remove
if (isSet($_POST['action']) AND $_POST['action'] == 'Cancel Date')
{
	// Update selected company by making date to remove null	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'UPDATE 	`company` 
				SET		`removeAtDate` = NULL
				WHERE 	`companyID` = :CompanyID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
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

	$_SESSION['CompanyUserFeedback'] = "Successfully removed the cancel date from the company: " . $_POST['CompanyName'] . ".";

	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){	
	$_SESSION['EditCompanyChangedName'] = $_SESSION['EditCompanyOriginalName'];
	$_SESSION['EditCompanyChangedRemoveDate'] = $_SESSION['EditCompanyOriginalRemoveDate'];

	$_SESSION['refreshEditCompany'] = TRUE;
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$refreshcompanies = TRUE;
	$_SESSION['CompanyUserFeedback'] = "You cancelled your company editing.";
}

if(isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$refreshcompanies = TRUE;
	$_SESSION['CompanyUserFeedback'] = "You cancelled your company creation.";
}

if(isSet($_POST['merge']) AND $_POST['merge'] == 'Cancel'){
	$refreshcompanies = TRUE;
	$_SESSION['CompanyUserFeedback'] = "You cancelled your company merging.";
}

if(isSet($refreshcompanies) AND $refreshcompanies) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshcompanies);
}

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddCompanySessions();
clearEditCompanySessions();
clearBookingHistorySessions();

// Display companies list
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

	$sql = "SELECT 		c.`companyID` 										AS CompID,
						c.`name` 											AS CompanyName,
						c.`dateTimeCreated`									AS DatetimeCreated,
						c.`removeAtDate`									AS DeletionDate,
						c.`isActive`										AS CompanyActivated,
						(
							SELECT 	COUNT(e.`CompanyID`)
							FROM 	`employee` e
							WHERE 	e.`companyID` = CompID
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
							WHERE 		b.`CompanyID` = CompID
							AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
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
							WHERE 		b.`CompanyID` = CompID
							AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
							WHERE 		b.`CompanyID` = CompID
						)													AS TotalCompanyWideBookingTimeUsed,
						cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
						cc.`lastModified`									AS CompanyCreditsLastModified,
						cr.`name`											AS CreditSubscriptionName,
						cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
						cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
						cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice,
						COUNT(cch.`CompanyID`)								AS CompanyCreditsHistoryPeriods,
						SUM(cch.`hasBeenBilled`)							AS CompanyCreditsHistoryPeriodsSetAsBilled
			FROM 		`company` c
			LEFT JOIN	`companycredits` cc
			ON			c.`CompanyID` = cc.`CompanyID`
			LEFT JOIN	`credits` cr
			ON			cr.`CreditsID` = cc.`CreditsID`
			LEFT JOIN 	`companycreditshistory` cch
			ON 			cch.`CompanyID` = c.`CompanyID`
			GROUP BY 	c.`CompanyID`
			ORDER BY 	c.`name`";
	$s = $pdo->prepare($sql);
	$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
	$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
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
	$error = 'Error fetching companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach($result as $row){
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
	if(!empty($row["CompanyAlternativeMinuteAmount"])){
		$companyMinuteCredits = $row["CompanyAlternativeMinuteAmount"];
	} elseif(!empty($row["CreditSubscriptionMinuteAmount"])) {
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
	if(isSet($row['CompanyCreditsHistoryPeriods']) AND $row['CompanyCreditsHistoryPeriods'] != ""){
		$totalPeriods = $row['CompanyCreditsHistoryPeriods'];
	} else {
		$totalPeriods = 0;
	}
	if(isSet($row['CompanyCreditsHistoryPeriodsSetAsBilled']) AND $row['CompanyCreditsHistoryPeriodsSetAsBilled'] != ""){
		$billedPeriods = $row['CompanyCreditsHistoryPeriodsSetAsBilled'];
	} else {
		$billedPeriods = 0;
	}
	$notBilledPeriods = $totalPeriods - $billedPeriods;

	if($isActive){
		$companies[] = array(
								'CompanyID' => $row['CompID'], 
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
	} elseif(!$isActive AND ($dateToRemove == "" OR $dateToRemove == NULL)) {
		$unactivedcompanies[] = array(
										'CompanyID' => $row['CompID'], 
										'CompanyName' => $row['CompanyName'],
										'DatetimeCreated' => $dateTimeCreatedToDisplay
									);
	} elseif(!$isActive AND $dateToRemove != "" AND $dateToRemove != NULL){
		$inactivecompanies[] = array(
										'CompanyID' => $row['CompID'], 
										'CompanyName' => $row['CompanyName'],
										'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
										'DeletionDate' => $dateToRemoveToDisplay,
										'DatetimeCreated' => $dateTimeCreatedToDisplay
									);
	}
}

unset($_SESSION['MergeCompanySelectedCompanyID']);
unset($_SESSION['MergeCompanySelectedCompanyID2']);

var_dump($_SESSION); // TO-DO: Remove before uploading

// Create the companies list in HTML
include_once 'companies.html.php';
?>