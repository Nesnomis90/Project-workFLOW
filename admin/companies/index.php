<?php 
// This is the index file for the COMPANIES folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add sessions from other places too. Remove if it breaks multiple tabs

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember values when displaying booking history
function clearBookingHistorySessions(){
	unset($_SESSION['BookingHistoryIntervalNumber']);
	unset($_SESSION['BookingHistoryCompanyInfo']);
	unset($_SESSION['BookingHistoryFirstPeriodIntervalNumber']);
}

// Function to clear sessions used to remember user inputs on refreshing the add company form
function clearAddCompanySessions(){
	unset($_SESSION['AddCompanyCompanyName']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit company form
function clearEditCompanySessions(){	
	unset($_SESSION['EditCompanyOriginalName']);
	unset($_SESSION['EditCompanyOriginalRemoveDate']);
	
	unset($_SESSION['EditCompanyChangedName']);
	unset($_SESSION['EditCompanyChangedRemoveDate']);
	
	unset($_SESSION['EditCompanyCompanyID']);
}

// Function to calculate booking time used and the cost of that period for a company
function calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow){
	
	if($rightNow === TRUE){
		$sql = "SELECT 		IFNULL(
									cc.`altMinuteAmount`,
									cr.`minuteAmount`)		AS CreditSubscriptionMinuteAmount,
							cr.`monthlyPrice`				AS CreditSubscriptionMonthlyPrice,
							cr.`overCreditMinutePrice`		AS CreditSubscriptionMinutePrice,
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
		$sql = "SELECT 		`minuteAmount`				AS CreditSubscriptionMinuteAmount,
							`monthlyPrice`				AS CreditSubscriptionMonthlyPrice,
							`overCreditMinutePrice`		AS CreditSubscriptionMinutePrice,
							`overCreditHourPrice`		AS CreditSubscriptionHourPrice,
							`hasBeenBilled`				AS PeriodHasBeenBilled,
							`billingDescription`		AS BillingDescription
				FROM 		`companycreditshistory`
				WHERE 		`companyID` = :CompanyID
				AND 		`startDate` = :startDate
				AND			`endDate` = :endDate
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':startDate', $BillingStart);
		$s->bindValue(':endDate', $BillingEnd);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		
		$periodHasBeenBilled = $row['PeriodHasBeenBilled'];
		$billingDescription = $row['BillingDescription'];
		if($billingDescription == NULL){
			$billingDescription = "";
		}
	}
	// Get the credit information for the selected period (if we have it saved in companycreditshistory
	
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
	$minPrice = $row["CreditSubscriptionMinutePrice"];
	if($minPrice == NULL OR $minPrice == ""){
		$minPrice = 0;
	}	
	
	if(	($minPrice == 0 AND $hourPrice == 0) OR 
		($minPrice != 0 AND $hourPrice != 0 )){
		$overCreditsFee = "Not set";
	} elseif($minPrice != 0 AND $hourPrice == 0) {
		$overCreditsFee = convertToCurrency($minPrice) . "/m";
	} elseif($minPrice == 0 AND $hourPrice != 0) {
		$overCreditsFee = convertToCurrency($hourPrice) . "/h";
	}

	//Get completed booking history from the current billing period
	$sql = "SELECT 		b.`startDateTime`		AS BookingStartedDatetime,
						b.`actualEndDateTime`	AS BookingCompletedDatetime,
						u.`firstName`			AS UserFirstname,
						u.`lastName`			AS UserLastname,
						u.`email`				AS UserEmail,
						m.`name`				AS MeetingRoomName
			FROM 		`booking` b
			INNER JOIN  `company` c
			ON 			c.`CompanyID` = b.`companyID`
			LEFT JOIN	`user` u
			ON 			u.`userID` = b.`userID`
			LEFT JOIN 	`meetingroom` m
			ON			m.`meetingRoomID` = b.`meetingRoomID`
			WHERE   	b.`CompanyID` = :CompanyID
			AND 		b.`actualEndDateTime` IS NOT NULL
			AND     	b.`dateTimeCancelled` IS NULL
			AND         b.`actualEndDateTime`
			BETWEEN	    :startDate
			AND			:endDate";

	$s = $pdo->prepare($sql);
	$s->bindValue(':CompanyID', $companyID);
	$s->bindValue(':startDate', $BillingStart);
	$s->bindValue(':endDate', $BillingEnd);
	$s->execute();
	$result = $s->fetchAll(PDO::FETCH_ASSOC);
		
	//Close the connection
	$pdo = null;	
	
	$totalBookingTimeThisPeriod = 0;
	foreach($result as $row){
		
		// Format dates to display
		$startDateTime = convertDatetimeToFormat($row['BookingStartedDatetime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$endDateTime = convertDatetimeToFormat($row['BookingCompletedDatetime'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		
		$bookingPeriod = $startDateTime . " to " . $endDateTime;
		
		// Calculate time used
		$bookingTimeUsed =  convertTwoDateTimesToTimeDifferenceInMinutes($row['BookingStartedDatetime'], $row['BookingCompletedDatetime']);
		$displayBookingTimeUsed = convertMinutesToHoursAndMinutes($bookingTimeUsed);
	
		// Check if the meeting was long enough to even be counted
		if($bookingTimeUsed < BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS){
			$bookingTimeUsed = 0;
		}
		
		// Check if time used is higher than minimum time for a booked meeting
		if(	$bookingTimeUsed < MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS AND
			$bookingTimeUsed != 0){
			$bookingTimeUsed = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS;
			$displayBookingTimeUsedInPriceCalculations = convertMinutesToHoursAndMinutes($bookingTimeUsed);
		} else {
			$displayBookingTimeUsedInPriceCalculations = $displayBookingTimeUsed;
		}			

		$totalBookingTimeThisPeriod += $bookingTimeUsed;

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
		
		$bookingHistory[] = array(
									'BookingPeriod' => $bookingPeriod,
									'UserInformation' => $userInformation,
									'MeetingRoomName' => $meetingRoomName,
									'BookingTimeUsed' => $displayBookingTimeUsed,
									'BookingTimeCharged' => $displayBookingTimeUsedInPriceCalculations
									);
	}
	
		// Calculate monthly cost (subscription + over credit charges)
	if($totalBookingTimeThisPeriod > 0){
		if($totalBookingTimeThisPeriod > $companyMinuteCredits){
			// Company has used more booking time than credited. Let's calculate how far over they went
			$actualTimeOverCreditsInMinutes = $totalBookingTimeThisPeriod - $companyMinuteCredits;
		
			// Let's calculate cost
			if($hourPrice == 0 AND $minPrice == 0){
				// The subscription has no valid overtime price set, should not occur
				$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . 
										$actualTimeOverCreditsInMinutes . "m * cost (not set)";
				$totalBookingCostThisMonth = "N/A";
			} elseif($hourPrice != 0 AND $minPrice != 0){
				// The subscription has two valid overtime price set, should not occur
				$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . 
										$actualTimeOverCreditsInMinutes . "m * cost (not set)";
				$totalBookingCostThisMonth = "N/A";
			} elseif($hourPrice == 0 AND $minPrice != 0){
				// The subscription charges by the minute, if over credits
				$overFeeCostThisMonth = $minPrice * $actualTimeOverCreditsInMinutes;
				$totalCost = $monthPrice+$overFeeCostThisMonth;
				$displayOverFeeCostThisMonth = convertToCurrency($overFeeCostThisMonth);
				$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency($overFeeCostThisMonth);
				$totalBookingCostThisMonth = convertToCurrency($totalCost);
			} elseif($hourPrice != 0 AND $minPrice == 0){
				// The subsription charges by the hour, if over credits
				// Round up to closest hour/SPLIT_PRICE_PER_HOUR_INTO_THIS_MANY_PIECES.
				if(SPLIT_PRICE_PER_HOUR_INTO_THIS_MANY_PIECES > 0){
					$hourAmountUsedInCalculation = floor($actualTimeOverCreditsInMinutes/60);
					$minutesRemaining = $actualTimeOverCreditsInMinutes - $hourAmountUsedInCalculation*60;
					$split = SPLIT_PRICE_PER_HOUR_INTO_THIS_MANY_PIECES;
					$slicedHourPrice = $hourPrice/$split;
					$minuteSlice = 60/$split;
					$nextMinuteAmount = ceil($minutesRemaining/$minuteSlice)*$minuteSlice;
					if($nextMinuteAmount == 60){
						$hourAmountUsedInCalculation += 1;
						$minuteAmountUsedInCalculation = 0;
						$overFeeCostMinute = 0;
					} else {
						$minuteAmountUsedInCalculation = $nextMinuteAmount;
						$overFeeCostMinute = $slicedHourPrice * ($minuteAmountUsedInCalculation/$minuteSlice);
					}
					$displayHourAmountUsedInCalculation = $hourAmountUsedInCalculation . "h" . $minuteAmountUsedInCalculation . "m";
					$overFeeCostHour = $hourPrice * $hourAmountUsedInCalculation;
					$overFeeCostThisMonth = $overFeeCostHour + $overFeeCostMinute;
				} else {
					$hourAmountUsedInCalculation = ceil($actualTimeOverCreditsInMinutes/60);
					$displayHourAmountUsedInCalculation = $hourAmountUsedInCalculation . "h0m";
					$overFeeCostThisMonth = $hourPrice * $hourAmountUsedInCalculation;
				}
				
				$displayOverFeeCostThisMonth = convertToCurrency($overFeeCostThisMonth);
				$totalCost = $monthPrice+$overFeeCostThisMonth;
				$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency($overFeeCostThisMonth);
				$totalBookingCostThisMonth = convertToCurrency($totalCost);
			}
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $totalBookingTimeThisPeriod;
			$overCreditsTimeUsed = $totalBookingTimeThisPeriod - $companyMinuteCredits;
			$displayOverCreditsTimeUsed = convertMinutesToHoursAndMinutes($overCreditsTimeUsed);
		} else {
			$bookingCostThisMonth = convertToCurrency($monthPrice) . " + " . convertToCurrency(0);
			$totalBookingCostThisMonth = convertToCurrency($monthPrice);				
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $totalBookingTimeThisPeriod;
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
	
	if(!isset($actualTimeOverCreditsInMinutes)){
		$actualTimeOverCreditsInMinutes = "";
	}		
	if(!isset($hourAmountUsedInCalculation)){
		$hourAmountUsedInCalculation = "";
	}			
	if(!isset($displayOverCreditsTimeUsed)){
		$displayOverCreditsTimeUsed = "";
	}		
	if(!isset($displayOverFeeCostThisMonth)){
		$displayOverFeeCostThisMonth = "";
	}	
	if(!isset($displayHourAmountUsedInCalculation)){
		$displayHourAmountUsedInCalculation = "";
	}
	if(!isset($bookingHistory)){
		$bookingHistory = array();
	}
	if(!isset($periodHasBeenBilled)){
		$periodHasBeenBilled = 0;
	}
	
	return array(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
					$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
					$hourAmountUsedInCalculation, $bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining,
					$displayHourAmountUsedInCalculation, $actualTimeOverCreditsInMinutes, $periodHasBeenBilled, $billingDescription);
}

// Function to check if user inputs for companies are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isset($_POST['CompanyName'])){
		$companyName = trim($_POST['CompanyName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddCompanyError'] = "Company cannot be created without a name!";
	}
	if(isset($_POST['DateToRemove'])){
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

		if (isset($correctFormatIfValid) AND $correctFormatIfValid === FALSE AND !$invalidInput){
			$_SESSION['AddCompanyError'] = "The date you submitted did not have a correct format. Please try again.";
			$invalidInput = TRUE;
		}
		if(isset($correctFormatIfValid) AND $correctFormatIfValid !== FALSE){
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
	if(isset($_SESSION['EditCompanyOriginalName'])){
		$originalCompanyName = strtolower($_SESSION['EditCompanyOriginalName']);
		$newCompanyName = strtolower($validatedCompanyName);

		if(isset($_SESSION['EditCompanyOriginalName']) AND $originalCompanyName == $newCompanyName){
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
						LIMIT 1';
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

// If admin wants to set a period as billed
if (isset($_POST['history']) AND $_POST['history'] == "Set As Billed"){
	
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];
	
	// Remember information
	$NextPeriod = $_POST['nextPeriod'];
	$PreviousPeriod = $_POST['PreviousPeriod'];
	$BillingStart = $_POST['billingStart'];
	$BillingEnd = $_POST['billingEnd'];
	
	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	
		
		// Format billing dates
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " To " . $displayBillingEnd . ".";	
		
		// rightNow decides if we use the companycreditshistory or the credits/companycredits information
		if($NextPeriod){
			$rightNow = FALSE;
		} else {
			$rightNow = TRUE;
		}

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$hourAmountUsedInCalculation, $bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining,
				$displayHourAmountUsedInCalculation, $actualTimeOverCreditsInMinutes, $periodHasBeenBilled, $billingDescription) 
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow);
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];
	
	var_dump($_SESSION); // TO-DO: Remove after testing is over.
	
	include_once 'bookinghistory.html.php';
	exit();	
}

// If admin wants to see the booking history of the period after the currently shown one
if (isset($_POST['history']) AND $_POST['history'] == "Next Period"){
	
	if(isset($_SESSION['BookingHistoryIntervalNumber'])){
		$intervalNumber = $_SESSION['BookingHistoryIntervalNumber'] - 1;
	} else {
		$intervalNumber = -1;
	}
	$_SESSION['BookingHistoryIntervalNumber'] = $intervalNumber;

	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];
	
	
	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		// Check if there is a next period for this company, if not we disable the next period button
		$sql = "SELECT IF(
							DATE(`endDate`) = DATE_SUB(`startDate`,INTERVAL :intervalNumber - 1 MONTH), 
							NULL, 
							1
						) AS ValidBillingDate,
						DATE_SUB(`startDate`,INTERVAL :intervalNumber MONTH) AS CompanyBillingDateStart,
						DATE_SUB(`startDate`,INTERVAL :intervalNumber - 1 MONTH) AS CompanyBillingDateEnd
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':intervalNumber', $intervalNumber);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
			
		if($row['ValidBillingDate'] == NULL){
			$NextPeriod = FALSE;
		} else {
			$NextPeriod = TRUE;
		}
		$PreviousPeriod = TRUE;
		
		// Format billing dates
		$BillingStart = $row['CompanyBillingDateStart'];
		$BillingEnd =  $row['CompanyBillingDateEnd'];
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " To " . $displayBillingEnd . ".";	
		
		// rightNow decides if we use the companycreditshistory or the credits/companycredits information
		if($NextPeriod){
			$rightNow = FALSE;
		} else {
			$rightNow = TRUE;
		}

		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$hourAmountUsedInCalculation, $bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining,
				$displayHourAmountUsedInCalculation, $actualTimeOverCreditsInMinutes, $periodHasBeenBilled, $billingDescription) 
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow);
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];
	
	var_dump($_SESSION); // TO-DO: Remove after testing is over.
	
	include_once 'bookinghistory.html.php';
	exit();	
}

// If admin wants to see the booking history of the period before the currently shown one
if (	(isset($_POST['history']) AND $_POST['history'] == "Previous Period") OR 
		(isset($_POST['history']) AND $_POST['history'] == "First Period")){
	//TO-DO: Do the same here as in "next period" for calculating cost
	// Set correct period based on what user clicked.
	if(isset($_POST['history']) AND $_POST['history'] == "Previous Period"){
		if(isset($_SESSION['BookingHistoryIntervalNumber'])){
			$intervalNumber = $_SESSION['BookingHistoryIntervalNumber'] + 1;
		} else {
			$intervalNumber = 1;
		}
		$_SESSION['BookingHistoryIntervalNumber'] = $intervalNumber;		
	} elseif(isset($_POST['history']) AND $_POST['history'] == "First Period"){
		$_SESSION['BookingHistoryIntervalNumber'] = $_SESSION['BookingHistoryFirstPeriodIntervalNumber'];
		$intervalNumber = $_SESSION['BookingHistoryIntervalNumber'];
	}
	
	$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	$CompanyName = $_SESSION['BookingHistoryCompanyInfo']['CompanyName'];
	
	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();	

		$sql = "SELECT IF(
							DATE(`dateTimeCreated`) = DATE_SUB(`startDate`, INTERVAL :intervalNumber MONTH), 
							NULL, 
							1
						) 															AS ValidBillingDate,
						DATE_SUB(`startDate`, INTERVAL :intervalNumber MONTH)		AS CompanyBillingDateStart,
						DATE_SUB(`startDate`, INTERVAL :intervalNumber -1 MONTH) 	AS CompanyBillingDateEnd
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':intervalNumber', $intervalNumber);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
			
		if($row['ValidBillingDate'] == NULL){
			$PreviousPeriod = FALSE;
		} else {
			$PreviousPeriod = TRUE;
		}
		$NextPeriod = TRUE;
		
		// Format billing dates
		$BillingStart = $row['CompanyBillingDateStart'];
		$BillingEnd =  $row['CompanyBillingDateEnd'];
		$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
		$BillingPeriod = $displayBillingStart . " To " . $displayBillingEnd . ".";			
		
		$rightNow = FALSE;
		
		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$hourAmountUsedInCalculation, $bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining,
				$displayHourAmountUsedInCalculation, $actualTimeOverCreditsInMinutes, $periodHasBeenBilled, $billingDescription) 
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow);
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	$displayDateTimeCreated = $_SESSION['BookingHistoryCompanyInfo']['CompanyDateTimeCreated'];
	
	var_dump($_SESSION); // TO-DO: Remove after testing is over.
	
	include_once 'bookinghistory.html.php';
	exit();	
}

// If admin wants to see the booking history of the selected company
if ((isset($_POST['action']) AND $_POST['action'] == "Booking History") OR 
	((isset($_POST['history']) AND $_POST['history'] == "Last Period"))){
		//TO-DO: Do the same here as in "next period" for calculating cost
	if(isset($_SESSION['BookingHistoryCompanyInfo'])){
		unset($_SESSION['BookingHistoryIntervalNumber']);
		$companyID = $_SESSION['BookingHistoryCompanyInfo']['CompanyID'];
	} else {
		$companyID = $_POST['id'];
	}
	
	// Get booking history for the selected company
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// Get relevant company information
		$sql = "SELECT 	`companyID`			AS CompanyID, 
						`name`				AS CompanyName,
						`dateTimeCreated`	AS CompanyDateTimeCreated,
						`prevStartDate`		AS CompanyBillingDatePreviousStart,
						`startDate`			AS CompanyBillingDateStart,
						`endDate`			AS CompanyBillingDateEnd
				FROM 	`company`
				WHERE 	`companyID` = :CompanyID
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$_SESSION['BookingHistoryCompanyInfo'] = $row;
		
		$dateTimeCreated = $row['CompanyDateTimeCreated'];
		$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated,'Y-m-d H:i:s',DATE_DEFAULT_FORMAT_TO_DISPLAY);
		
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
		$BillingPeriod = $displayBillingStart . " To " . $displayBillingEnd . ".";			
		
		// Get first period as intervalNumber
		$firstPeriodIntervalNumber = convertTwoDateTimesToTimeDifferenceInMonths($dateTimeCreated,$BillingEnd);
		$_SESSION['BookingHistoryFirstPeriodIntervalNumber'] = $firstPeriodIntervalNumber;
		
		$rightNow = TRUE;
		
		list(	$bookingHistory, $displayCompanyCredits, $displayCompanyCreditsRemaining, $displayOverCreditsTimeUsed, 
				$displayMonthPrice, $displayTotalBookingTimeThisPeriod, $displayOverFeeCostThisMonth, $overCreditsFee,
				$hourAmountUsedInCalculation, $bookingCostThisMonth, $totalBookingCostThisMonth, $companyMinuteCreditsRemaining,
				$displayHourAmountUsedInCalculation, $actualTimeOverCreditsInMinutes, $periodHasBeenBilled, $billingDescription) 
		= calculatePeriodInformation($pdo, $companyID, $BillingStart, $BillingEnd, $rightNow);	
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	var_dump($_SESSION); // TO-DO: Remove after testing is over.
	
	include_once 'bookinghistory.html.php';
	exit();
}

// If admin wants to be able to delete companies it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['companiesEnableDelete'] = TRUE;
	$refreshcompanies = TRUE;
}

// If admin wants to be disable company deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['companiesEnableDelete']);
	$refreshcompanies = TRUE;
}

// If admin wants to activate a registered company
if (isset($_POST['action']) AND $_POST['action'] == 'Activate') {	
	// Update selected company in database to be active
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`company`
				SET		`isActive` = 1
				WHERE 	`companyID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
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

// If admin wants to remove a company from the database
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
	
	$_SESSION['CompanyUserFeedback'] = "Successfully removed the company!";
	
	// Add a log event that a company was removed
	try
	{
		// Save a description with information about the meeting room that was removed
		$description = "The company: " . $_POST['CompanyName'] . " was removed by: " . $_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
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
if ((isset($_POST['action']) AND $_POST['action'] == 'Create Company') OR 
	(isset($_SESSION['refreshAddCompany']) AND $_SESSION['refreshAddCompany']))
{	
	// Check if it was a user input or a forced refresh
	if(isset($_SESSION['refreshAddCompany']) AND $_SESSION['refreshAddCompany']){
		//	Ackowledge that we have refreshed
		unset($_SESSION['refreshAddCompany']);
	}

	// Set initial values
	$CompanyName = '';

	// Set always correct values
	$pageTitle = 'New Company';
	$button = 'Add Company';	
	$id = '';
	
	if(isset($_SESSION['AddCompanyCompanyName'])){
		$CompanyName = $_SESSION['AddCompanyCompanyName'];
		unset($_SESSION['AddCompanyCompanyName']);
	}
	
	// We want a reset all fields button while adding a new company
	$reset = 'reset';
	
	// We don't need to see date to remove when adding a new company
	$ShowDateToRemove = FALSE;
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual html form template
	include 'form.html.php';
	exit();
}

// if admin wants to edit company information
// we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR 
	(isset($_SESSION['refreshEditCompany']) AND $_SESSION['refreshEditCompany']))
{
	// Check if it was a user input or a forced refresh
	if(isset($_SESSION['refreshEditCompany']) AND $_SESSION['refreshEditCompany']){
		//	Acknowledge that we have refreshed
		unset($_SESSION['refreshEditCompany']);
		
		// Get values we had before the refresh
		if(isset($_SESSION['EditCompanyChangedName'])){
			$CompanyName = $_SESSION['EditCompanyChangedName'];
		} else {
			$CompanyName = '';
		}
			
		if(isset($_SESSION['EditCompanyCompanyID'])){
			$id = $_SESSION['EditCompanyCompanyID'];
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
		$row = $s->fetch(PDO::FETCH_ASSOC);
		$CompanyName = $row['name'];
		$DateToRemove = $row['removeAtDate'];
		$id = $row['companyID'];
		
		if(!isset($DateToRemove) OR $DateToRemove == NULL){
			$DateToRemove = '';
		}
		$_SESSION['EditCompanyOriginalName'] = $CompanyName;
		$_SESSION['EditCompanyOriginalRemoveDate'] = $DateToRemove;
		$_SESSION['EditCompanyCompanyID'] = $id;
	}
	// Display original values
	$originalCompanyName = $_SESSION['EditCompanyOriginalName'];
	$originalDateToDisplay = convertDatetimeToFormat($_SESSION['EditCompanyOriginalRemoveDate'] , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	if(isset($_SESSION['EditCompanyChangedRemoveDate'])){
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
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual form we want to use
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the company
if (isset($_POST['action']) AND $_POST['action'] == 'Add Company')
{
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
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `company` 
				SET			`name` = :CompanyName,
							`startDate` = CURDATE(),
							`endDate` = (CURDATE() + INTERVAL 1 MONTH)';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyName', $validatedCompanyName);
		$s->execute();
		
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
	
	$_SESSION['CompanyUserFeedback'] = "Successfully added the company: " . $validatedCompanyName . ".";
	
		// Give the company the default subscription
	try
	{	
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
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
		
		//Close the connection
		$pdo = null;
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
		if(isset($_SESSION['LastCompanyID'])){
			$LastCompanyID = $_SESSION['LastCompanyID'];
			unset($_SESSION['LastCompanyID']);
		}
		// Save a description with information about the meeting room that was added
		$logEventdescription = "The company: " . $validatedCompanyName . " was added by: " . $_SESSION['LoggedInUserName'];
		
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
	
	clearAddCompanySessions();
	
	// Load companies list webpage with new company
	header('Location: .');
	exit();
}

// Perform the actual database update of the edited information
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit Company'))
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
	
	if(	isset($_SESSION['EditCompanyOriginalName']) AND 
		$_SESSION['EditCompanyOriginalName'] != $validatedCompanyName){
		$NumberOfChanges++;
	}
	
	if(	isset($_SESSION['EditCompanyOriginalRemoveDate']) AND 
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
					WHERE 	`companyID` = :id';
			
			if ($validatedCompanyDateToRemove == ''){
				$validatedCompanyDateToRemove = null;
			}
				
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
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
	
	$_SESSION['CompanyUserFeedback'] = "Successfully removed the cancel date from the company: " . $_POST['CompanyName'] . ".";
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();
}

if(isset($_POST['edit']) AND $_POST['edit'] == 'Reset'){	
	$_SESSION['EditCompanyChangedName'] = $_SESSION['EditCompanyOriginalName'];
	$_SESSION['EditCompanyChangedRemoveDate'] = $_SESSION['EditCompanyOriginalRemoveDate'];
	
	$_SESSION['refreshEditCompany'] = TRUE;
	header('Location: .');
	exit();	
}

if(isset($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$refreshcompanies = TRUE;
	$_SESSION['CompanyUserFeedback'] = "You cancelled your company editing.";
}

if(isset($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$refreshcompanies = TRUE;
	$_SESSION['CompanyUserFeedback'] = "You cancelled your company creation.";
}

if(isset($_POST['history']) AND $_POST['history'] == 'Return To Companies'){
	$refreshcompanies = TRUE;
	clearBookingHistorySessions();
}

if(isset($refreshcompanies) AND $refreshcompanies) {
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
	// Made it so the user doesn't have to be an employee anymore for the hours to count
	// Only takes into account time spent and company the booking was booked for.
	$sql = "SELECT 		c.companyID 										AS CompID,
						c.`name` 											AS CompanyName,
						c.`dateTimeCreated`									AS DatetimeCreated,
						c.`removeAtDate`									AS DeletionDate,
						c.`isActive`										AS CompanyActivated,
						(
							SELECT 	COUNT(c.`name`) 
							FROM 	`company` c 
							JOIN 	`employee` e 
							ON 		c.CompanyID = e.CompanyID 
							WHERE 	e.companyID = CompID
						)													AS NumberOfEmployees, 
						(
							SELECT (
									BIG_SEC_TO_TIME(
													SUM(
														DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
														)*86400 
													+ 
													SUM(
														TIME_TO_SEC(b.`actualEndDateTime`) 
														- 
														TIME_TO_SEC(b.`startDateTime`)
														) 
													) 
									) 
							FROM 		`booking` b  
							INNER JOIN 	`company` c 
							ON 			b.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
						)   												AS PreviousMonthCompanyWideBookingTimeUsed,           
						(
							SELECT (
									BIG_SEC_TO_TIME(
													SUM(
														DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
														)*86400 
													+ 
													SUM(
														TIME_TO_SEC(b.`actualEndDateTime`) 
														- 
														TIME_TO_SEC(b.`startDateTime`)
														) 
													) 
									) 
							FROM 		`booking` b  
							INNER JOIN 	`company` c 
							ON 			b.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
						)   												AS MonthlyCompanyWideBookingTimeUsed,
						(
							SELECT (
									BIG_SEC_TO_TIME(
													SUM(
														DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
														)*86400 
													+ 
													SUM(
														TIME_TO_SEC(b.`actualEndDateTime`) 
														- 
														TIME_TO_SEC(b.`startDateTime`)
														) 
													) 
									)
							FROM 		`booking` b 
							INNER JOIN 	`company` c 
							ON 			b.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
						)   												AS TotalCompanyWideBookingTimeUsed,
						cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
						cc.`lastModified`									AS CompanyCreditsLastModified,
						cr.`name`											AS CreditSubscriptionName,
						cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
						cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
						cr.`overCreditMinutePrice`							AS CreditSubscriptionMinutePrice,
						cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice
			FROM 		`company` c
			LEFT JOIN	`companycredits` cc
			ON			c.`CompanyID` = cc.`CompanyID`
			LEFT JOIN	`credits` cr
			ON			cr.`CreditsID` = cc.`CreditsID`
			GROUP BY 	c.`name`";

	$return = $pdo->query($sql);
	$result = $return->fetchAll(PDO::FETCH_ASSOC);
	if(isset($result)){
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
	$minPrice = $row["CreditSubscriptionMinutePrice"];
	if($minPrice == NULL OR $minPrice == ""){
		$minPrice = 0;
	}	
	
	if(	($minPrice == 0 AND $hourPrice == 0) OR 
		($minPrice != 0 AND $hourPrice != 0 )){
		$overCreditsFee = "Not set";
	} elseif($minPrice != 0 AND $hourPrice == 0) {
		$overCreditsFee = $minPrice . "/m";
	} elseif($minPrice == 0 AND $hourPrice != 0) {
		$overCreditsFee = $hourPrice . "/h";
	}
		// Calculate monthly cost (subscription + over credit charges)
	if($MonthlyTimeUsed != "N/A"){
		$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
		$monthlyTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);
		$actualTimeUsedInMinutesThisMonth = $monthlyTimeHour*60 + $monthlyTimeMinute;
		if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
			// Company has used more booking time than credited. Let's calculate how far over they went
			$actualTimeOverCreditsInMinutes = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
		
			// Let's calculate cost
			if($hourPrice == 0 AND $minPrice == 0){
				// The subscription has no valid overtime price set, should not occur
				$bookingCostThisMonth = $monthPrice . "+" . $actualTimeOverCreditsInMinutes . "m * cost (not set)";
			} elseif($hourPrice != 0 AND $minPrice != 0){
				// The subscription has two valid overtime price set, should not occur
				$bookingCostThisMonth = $monthPrice . "+" . $actualTimeOverCreditsInMinutes . "m * cost (not set)";
			} elseif($hourPrice == 0 AND $minPrice != 0){
				// The subscription charges by the minute, if over credits
				$bookingCostThisMonth = $minPrice * $actualTimeOverCreditsInMinutes;
				$bookingCostThisMonth = $monthPrice . "+" . $bookingCostThisMonth;
			} elseif($hourPrice != 0 AND $minPrice == 0){
				// The subsription charges by the hour, if over credits
				// TO-DO: Round up/down? Break down into minutes? Currently rounding up.
				$bookingCostThisMonth = $hourPrice * ceil($actualTimeOverCreditsInMinutes/60);
				$bookingCostThisMonth = $monthPrice . "+" . $bookingCostThisMonth;
			}
			$companyMinuteCreditsRemaining = 0;
			
		} else {
			$bookingCostThisMonth = $monthPrice . "+0";
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
		}		
	} elseif($monthPrice != 0) {
		$bookingCostThisMonth = $monthPrice . "+0";
		$companyMinuteCreditsRemaining = $companyMinuteCredits;
	} else {
		$bookingCostThisMonth = "N/A";
		$companyMinuteCreditsRemaining = $companyMinuteCredits;
	}
		// Calculate cost for previous month (subscription + over credit charges)
	// TO-DO: Change/fix calculations? This will be wrong if credits/hour rate etc changes from previous month
	if($PrevMonthTimeUsed != "N/A"){
		$prevMonthTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
		$prevMonthTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);		
		$actualTimeUsedInMinutesPrevMonth = $prevMonthTimeHour*60 + $prevMonthTimeMinute;
		if($actualTimeUsedInMinutesPrevMonth > $companyMinuteCredits){
			// Company has used more booking time than credited. Let's calculate how far over they went
			$actualTimeOverCreditsInMinutes = $actualTimeUsedInMinutesPrevMonth - $companyMinuteCredits;
		
			// Let's calculate cost
			if($hourPrice == 0 AND $minPrice == 0){
				// The subscription has no valid overtime price set, should not occur
				$bookingCostPrevMonth = $monthPrice . "+" . $actualTimeOverCreditsInMinutes . "m * cost (not set)";
			} elseif($hourPrice != 0 AND $minPrice != 0){
				// The subscription has two valid overtime price set, should not occur
				$bookingCostPrevMonth = $monthPrice . "+" . $actualTimeOverCreditsInMinutes . "m * cost (not set)";
			} elseif($hourPrice == 0 AND $minPrice != 0){
				// The subscription charges by the minute, if over credits
				$bookingCostPrevMonth = $minPrice * $actualTimeOverCreditsInMinutes;
				$bookingCostPrevMonth = $monthPrice . "+" . $bookingCostPrevMonth;
			} elseif($hourPrice != 0 AND $minPrice == 0){
				// The subsription charges by the hour, if over credits
				// TO-DO: Round up/down? Break down into minutes? Currently rounding up.
				$bookingCostPrevMonth = $hourPrice * ceil($actualTimeOverCreditsInMinutes/60);
				$bookingCostPrevMonth = $monthPrice . "+" . $bookingCostPrevMonth;
			}	
		} else {
			$bookingCostPrevMonth = $monthPrice . "+0";
		}		
	} elseif($monthPrice != 0) {
		$bookingCostPrevMonth = $monthPrice . "+0";
	} else {
		$bookingCostPrevMonth = "N/A";
	}	
	
		// Format company credits remaining to be displayed
	$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);	
	
	// Display dates
	$dateCreated = $row['DatetimeCreated'];	
	$dateToRemove = $row['DeletionDate'];
	$isActive = ($row['CompanyActivated'] == 1);
	$dateTimeCreatedToDisplay = convertDatetimeToFormat($dateCreated, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateToRemoveToDisplay = convertDatetimeToFormat($dateToRemove, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);	
	
	if($isActive){
		$companies[] = array(
								'id' => $row['CompID'], 
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
								'CreditSubscriptionMonthlyPrice' => $monthPrice,
								'BookingCostPrevMonth' => $bookingCostPrevMonth,
								'BookingCostThisMonth' => $bookingCostThisMonth,
								'OverCreditsFee' => $overCreditsFee
							);
	} elseif(!$isActive AND ($dateToRemove == "" OR $dateToRemove == NULL)) {
		$unactivedcompanies[] = array(
										'id' => $row['CompID'], 
										'CompanyName' => $row['CompanyName'],
										'DatetimeCreated' => $dateTimeCreatedToDisplay
									);		
	} elseif(!$isActive AND $dateToRemove != "" AND $dateToRemove != NULL){
		$inactivecompanies[] = array(
										'id' => $row['CompID'], 
										'CompanyName' => $row['CompanyName'],
										'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
										'DeletionDate' => $dateToRemoveToDisplay,
										'DatetimeCreated' => $dateTimeCreatedToDisplay
									);		
	}
}
var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the companies list in HTML
include_once 'companies.html.php';
?>