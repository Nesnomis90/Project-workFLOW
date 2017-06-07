<?php 
// This is the index file for the COMPANYCREDITS folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add more/remove if it ruins multiple tabs open

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the 'edit'/'change amount' company credits form
function clearEditCompanyCreditsSessions(){
	unset($_SESSION['EditCompanyCreditsOriginalAlternativeCreditsAmount']);
}

// if admin wants to change credits info for the selected company
// we load a new html form
if (	isset($_POST['action']) AND $_POST['action'] == 'Edit' OR
		isset($_SESSION['refreshEditCompanyCredits']) AND $_SESSION['refreshEditCompanyCredits'])
{
	if(isset($_SESSION['refreshEditCompanyCredits']) AND $_SESSION['refreshEditCompanyCredits']){
		// Acknowledge that we have refreshEditCompanyCredits
		unset($_SESSION['refreshEditCompanyCredits']);
		
		$selectedCreditsID = $_POST['CreditsID'];
	} else {
		// Make sure we don't have any relevant values in memory
		clearEditCompanyCreditsSessions();
		// Get information from database again on the selected company credits
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			
			$sql = "SELECT 		c.`CompanyID`									AS TheCompanyID,
								c.`name`										AS CompanyName,
								c.`startDate`									AS CompanyBillingMonthStart,
								c.`endDate`										AS CompanyBillingMonthEnd,
								cr.`CreditsID`									AS CreditsID,
								cr.`name`										AS CreditsName,
								cr.`description`								AS CreditsDescription,
								cr.`minuteAmount`								AS CreditsGivenInMinutes,
								cr.`monthlyPrice`								AS CreditsMonthlyPrice,
								cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
								cr.`overCreditHourPrice`						AS CreditsHourPrice,
								cc.`altMinuteAmount`							AS CreditsAlternativeAmount
					FROM 		`company` c
					JOIN 		`companycredits` cc
					ON 			c.`CompanyID` = cc.`CompanyID`
					JOIN 		`credits` cr
					ON 			cr.`CreditsID` = cc.`CreditsID`
					WHERE 		c.`isActive` > 0
					AND			c.`CompanyID` = :CompanyID
					AND			cr.`CreditsID` = :CreditsID
					LIMIT 		1";
			
			$s = $pdo->prepare($sql);
			$s->bindValue(':CreditsID', $_POST['CreditsID']);
			$s->bindValue(':CompanyID', $_POST['CompanyID']);
			$s->execute();
			
			// Create an array with the row information we retrieved
			$row = $s->fetch();
			$_SESSION['EditCompanyCreditsOriginalInfo'] = $row;
				
			// Set the correct information
			$selectedCreditsID = $row['CreditsID'];
			$CreditsAlternativeAmount = $row['CreditsAlternativeAmount'];
			
			$_SESSION['EditCompanyCreditsOriginalAlternativeCreditsAmount'] = $CreditsAlternativeAmount;			
			
			//Close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching company credits details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
	}
	
	// Set original/correct values
	$original = $_SESSION['EditCompanyCreditsOriginalInfo'];	
	$CompanyID = $original['TheCompanyID'];
	$CompanyName = $original['CompanyName'];
	
	$BillingStart = $original['CompanyBillingMonthStart'];
	$BillingEnd =  $original['CompanyBillingMonthEnd'];
	$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$BillingPeriod = $displayBillingStart . " to " . $displayBillingEnd . ".";	
	
	$originalCreditsName = $original['CreditsName']; 
	$originalCreditsAlternativeCreditsAmount = convertMinutesToHoursAndMinutes($original['CreditsAlternativeAmount']);
	
	// TO-DO: Set original variables. Originally selected credits. Make a dropdown list of possible credits.
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual form we want to use
	include 'editcompanycredits.html.php';
	exit();
}

// Get only information from the specific company
if(isset($_GET['Company'])){	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 		c.`CompanyID`									AS TheCompanyID,
							c.`name`										AS CompanyName,
							c.`startDate`									AS CompanyBillingMonthStart,
							c.`endDate`										AS CompanyBillingMonthEnd,
							cr.`CreditsID`									AS CreditsID,
							cr.`name`										AS CreditsName,
							cr.`description`								AS CreditsDescription,
							cr.`minuteAmount`								AS CreditsGivenInMinutes,
							cr.`monthlyPrice`								AS CreditsMonthlyPrice,
							cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
							cr.`overCreditHourPrice`						AS CreditsHourPrice,
							cc.`altMinuteAmount`							AS CreditsAlternativeAmount,
							cc.`datetimeAdded` 								AS DateTimeAdded,
							cc.`lastModified`								AS DateTimeLastModified
				FROM 		`company` c
				JOIN 		`companycredits` cc
				ON 			c.`CompanyID` = cc.`CompanyID`
				JOIN 		`credits` cr
				ON 			cr.`CreditsID` = cc.`CreditsID`
				WHERE 		c.`isActive` > 0
				AND			c.`CompanyID` = :CompanyID
				LIMIT 		1";
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
		$s->execute();
		
		$result = $s->fetchAll();
		$rowNum = sizeOf($result);
		
		//close connection
		$pdo = null;
			
	}
	catch (PDOException $e)
	{
		$error = 'Error getting company credits information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Get all companies and their credits
if(!isset($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 		c.`CompanyID`									AS TheCompanyID,
							c.`name`										AS CompanyName,
							c.`startDate`									AS CompanyBillingMonthStart,
							c.`endDate`										AS CompanyBillingMonthEnd,
							cr.`CreditsID`									AS CreditsID,
							cr.`name`										AS CreditsName,
							cr.`description`								AS CreditsDescription,
							cr.`minuteAmount`								AS CreditsGivenInMinutes,
							cr.`monthlyPrice`								AS CreditsMonthlyPrice,
							cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
							cr.`overCreditHourPrice`						AS CreditsHourPrice,
							cc.`altMinuteAmount`							AS CreditsAlternativeAmount,
							cc.`datetimeAdded` 								AS DateTimeAdded,
							cc.`lastModified`								AS DateTimeLastModified
				FROM 		`company` c
				JOIN 		`companycredits` cc
				ON 			c.`CompanyID` = cc.`CompanyID`
				JOIN 		`credits` cr
				ON 			cr.`CreditsID` = cc.`CreditsID`
				WHERE 		c.`isActive` > 0
				ORDER BY	UNIX_TIMESTAMP(cc.`datetimeAdded`)
				DESC";
				
		$result = $pdo->query($sql);
		$rowNum = $result->rowCount();
		
		//close connection
		$pdo = null;
			
	}
	catch (PDOException $e)
	{
		$error = 'Error getting company credit information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}	

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){

	$addedDateTime = $row['DateTimeAdded'];
	$displayAddedDateTime = convertDatetimeToFormat($addedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$lastModifiedDateTime = $row['DateTimeLastModified'];
	$displaylastModifiedDateTime = convertDatetimeToFormat($lastModifiedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$companyBillingMonthStart = $row['CompanyBillingMonthStart'];
	$displayCompanyBillingMonthStart = convertDatetimeToFormat($companyBillingMonthStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$companyBillingMonthEnd = $row['CompanyBillingMonthEnd'];
	$displayCompanyBillingMonthEnd = convertDatetimeToFormat($companyBillingMonthEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	// Format Credits (From minutes to hours and minutes)
	if($row['CreditsAlternativeAmount'] != NULL){
		$creditsGiven = convertMinutesToHoursAndMinutes($row['CreditsAlternativeAmount']);
		$alternativeCredits = "Yes";
	} else {
		$creditsGiven = convertMinutesToHoursAndMinutes($row['CreditsGivenInMinutes']);
		$alternativeCredits = "No";
	}
		
	// Format what over fee rate we're using (hourly or minute by minute)
	$creditsMinutePrice = $row['CreditsMinutePrice'];
	$creditsHourPrice = $row['CreditsHourPrice'];
	if($creditsMinutePrice != NULL){
		$creditsOverCreditsFee = convertToCurrency($creditsMinutePrice) . '/min';
	} elseif($creditsHourPrice != NULL) {
		$creditsOverCreditsFee = convertToCurrency($creditsHourPrice) . '/hour';
	} else {
		$creditsOverCreditsFee = "Error, not set.";
	}
	
	$creditsMonthlyPrice = convertToCurrency($row['CreditsMonthlyPrice']);
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$companycredits[] = array(
							'TheCompanyID' => $row['TheCompanyID'],
							'CompanyName' => $row['CompanyName'],
							'CompanyBillingMonthStart' => $displayCompanyBillingMonthStart,
							'CompanyBillingMonthEnd' => $displayCompanyBillingMonthEnd,						
							'CreditsID' => $row['CreditsID'],
							'CreditsName' => $row['CreditsName'],
							'CreditsDescription' => $row['CreditsDescription'],
							'CreditsGiven' => $creditsGiven,
							'CreditsMonthlyPrice' => $creditsMonthlyPrice,
							'CreditsOverCreditsFee' => $creditsOverCreditsFee,
							'CompanyUsingAlternativeCreditsGiven' => $alternativeCredits,
							'DateTimeAdded' => $displayAddedDateTime,
							'DateTimeLastModified' => $displaylastModifiedDateTime						
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the company credits list in HTML
include_once 'companycredits.html.php';
?>