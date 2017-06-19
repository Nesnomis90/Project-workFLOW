<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once a day (more often in case SQL connection fails?)
// TO-DO: add sleep between queries?
// TO-DO: Make a sleep then repeat function on catch?

// If, for some reason, a company does not have a subscription set. We set it to default.
// TO-DO: Not extensively tested and probably super broken/bad
function setDefaultSubscriptionIfCompanyHasNone(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "SELECT 		COUNT(`CompanyID`),
							`CompanyID`,
							(
								SELECT 	`CreditsID`
								FROM	`credits`
								WHERE	`name` = 'Default'
							)								AS CreditsID
				FROM 		`company`
				WHERE		`CompanyID` 
				NOT IN		(
								SELECT 	`CompanyID`
								FROM 	`companycredits`
							)
				GROUP BY	`CompanyID`";
		$return = $pdo->query($sql);
		$result = $return->fetchAll();
		
		$sql = "INSERT INTO `companycredits`(`CompanyID`, `CreditsID`) 
				VALUES ";
				
		if($result[0] != NULL AND $result[0] > 0){
			// Need to add subscription to some companies
			$CreditsID = $result[0]['CreditsID'];
			foreach($result AS $companyRow){
				$CompanyID = $companyRow['CompanyID'];
				$sql .= "(" . $CompanyID . "," . $CreditsID ."),";
			}
			// Remove last ,
			$sql = substr($sql,0, -1);
			
			$pdo->exec($sql);
		}
		$pdo = null;
		unset($sql);
		return TRUE;
		
	}
	catch(PDOException $e)
	{
		//$error = 'Error checking/giving company a default subscription: ' . $e->getMessage();
		//include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		return FALSE;
	}	
}


// Update the billing date periods for the company when the last one has ended
function updateBillingDatesForCompanies(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "UPDATE 	`company`
				SET		`prevStartDate` = `startDate`,
						`startDate` = `endDate`,
						`endDate` = (`startDate` + INTERVAL 1 MONTH)
				WHERE	`companyID` <> 0
				AND		CURDATE() > `endDate`";		
		$pdo->exec($sql);
		
		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		//$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
		//include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		return FALSE;
	}	
}

// Make a company inactive when the current date is past the date set by admin
function setCompanyAsInactiveOnSetDate(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "UPDATE 	`company`
				SET 	`isActive` = 0
				WHERE 	DATE(CURRENT_TIMESTAMP) >= `removeAtDate`
				AND 	`isActive` = 1
				AND		`companyID` <> 0";		
		$pdo->exec($sql);
		
		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		//$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
		//include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		return FALSE;
	}
}

// Make any user turn into a normal user (access level) when the current date is past the date set by admin
function setUserAccessToNormalOnSetDate(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "UPDATE 	`user`
				SET 	`AccessID` = ( 
										SELECT 	`AccessID`
										FROM 	`accesslevel`
										WHERE 	`AccessName` = 'Normal User'
										LIMIT 	1
									),
						`bookingCode` = NULL,
						`reduceAccessAtDate` = NULL
				WHERE 	DATE(CURRENT_TIMESTAMP) >= `reduceAccessAtDate`
				AND 	`isActive` = 1
				AND		`userID` <> 0";		
		$pdo->exec($sql);
		
		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		//$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
		//include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		return FALSE;
	}	
}

// Get current credit information and insert into companycreditshistory on the billing month's end
function updateCompanyCreditsHistory(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "";		
		$pdo->exec($sql);
		
		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}	
}

// The actual actions taken // START //
	// Run our SQL functions
$updatedDefaultSubscription = setDefaultSubscriptionIfCompanyHasNone();
$updatedBillingDates = updateBillingDatesForCompanies();
$updatedCompanyActivity = setCompanyAsInactiveOnSetDate();
$updatedUserAccess = setUserAccessToNormalOnSetDate();
$updatedCompanyCreditsHistory = updateCompanyCreditsHistory();

$repetition = 3;
$sleepTime = 1;

// If we get a FALSE back, the function failed to do its purpose
// Let's wait and try again x times.

if(!$updatedDefaultSubscription){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setDefaultSubscriptionIfCompanyHasNone();
		if($success){
			break;
		}
	}
	unset($success);
}

if(!$updatedBillingDates){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = updateBillingDatesForCompanies();
		if($success){
			break;
		}
	}
	unset($success);
}

if(!$updatedCompanyActivity){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setCompanyAsInactiveOnSetDate();
		if($success){
			break;
		}
	}
	unset($success);
}

if(!$updatedUserAccess){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setUserAccessToNormalOnSetDate();
		if($success){
			break;
		}
	}
	unset($success);
}

if(!$updatedCompanyCreditsHistory){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = updateCompanyCreditsHistory();
		if($success){
			break;
		}
	}
	unset($success);
}
// The actual actions taken // END //
?>