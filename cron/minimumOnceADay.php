<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once a day (more often in case SQL connection fails?)

// If, for some reason, a company does not have a subscription set. We set it to default.
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	$pdo = connect_to_db();
	$sql = "SELECT 		COUNT(*),
						`CompanyID`
			FROM 		`company`
			WHERE		`CompanyID` 
			NOT IN		(
							SELECT 	`CompanyID`
							FROM 	`companycredits`
						)";
	$pdo->exec($sql);
	$result = $pdo->fetchAll();
	
	//Close the connection
	$pdo = null;
	
	if($result[0] > 0){
		// Need to add subscription to some companies
		$
		foreach($result AS $companyID){
			
		}
		
	}
	
	
	
	
	
	"INSERT INTO `companycredits`
			SET			`CompanyID` = (
										SELECT 		`CompanyID`
										FROM 		`company`
										WHERE		`CompanyID` 
										NOT IN		(
														SELECT 	`CompanyID`
														FROM 	`companycredits`
													)
										LIMIT 		1
										),
						`CreditsID` = (
										SELECT 	`CreditsID`
										FROM	`credits`
										WHERE	`name` = 'Default'
										)";		
	$pdo->exec($sql);
	

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