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
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$companycredit[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'EquipmentAmount' => $row['EquipmentAmount'],							
							'DateTimeAdded' => $displayAddedDateTime,
							'MeetingRoomID' => $row['MeetingRoomID'],
							'MeetingRoomName' => $row['MeetingRoomName']							
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the company credits list in HTML
include_once 'companycredits.html.php';
?>