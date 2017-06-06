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
							cr.`minuteAmount`								AS CreditsMinutesGiven,
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
		$creditsGivenInMinutes = $row['CreditsAlternativeAmount'];
		$alternativeCredits = "Yes";
	} else {
		$creditsGivenInMinutes = $row['CreditsGivenInMinutes'];
		$alternativeCredits = "No";
	}
	
	if($creditsGivenInMinutes > 59){
		$creditsGivenInHours = floor($creditsGivenInMinutes/60);
		$creditsGivenInMinutes -= $creditsGivenInHours*60;
		$creditsGiven = $creditsGivenInHours . 'h' . $creditsGivenInMinutes . 'm';
	} elseif($creditsGivenInMinutes > 0) {
		$creditsGiven = '0h' . $creditsGivenInMinutes . 'm';
	} else {
		$creditsGiven = 'None';
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