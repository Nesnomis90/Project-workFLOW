<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once a day (more often in case SQL connection fails?)
// TO-DO: add sleep between queries?

// If, for some reason, a company does not have a subscription set. We set it to default.
// TO-DO: Not extensively tested and probably super broken/bad
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
	
}
catch(PDOException $e)
{
	$error = 'Error checking/giving company a default subscription: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Update the billing date periods for the company when the last one has ended
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
}
catch(PDOException $e)
{
	$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Make a company inactive when the current date is past the date set by admin
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
}
catch(PDOException $e)
{
	$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Make any user turn into a normal user (access level) when the current date is past the date set by admin
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
}
catch(PDOException $e)
{
	$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}
?>