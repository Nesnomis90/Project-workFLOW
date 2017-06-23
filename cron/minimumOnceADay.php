<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once a day (more often in case SQL connection fails?)

// If, for some reason, a company does not have a subscription set. We set it to default.
// TO-DO: Not extensively tested and probably super broken/bad
function setDefaultSubscriptionIfCompanyHasNone(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		// Check if there is any information to change
		$sql = "SELECT 		COUNT(*)
				FROM 		`company`
				WHERE		`CompanyID` 
				NOT IN		(
								SELECT 	`CompanyID`
								FROM 	`companycredits`
							)"
		$return = $pdo->query($sql);
		$rowCount = $return->fetchColumn();
		
		if($rowCount > 0) {		
			$pdo = connect_to_db();
			$sql = "SELECT 		`CompanyID`						AS CompanyID,
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
								)";
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);
			
			$creditsID = $insert['CreditsID'];

			$pdo->beginTransaction();
			foreach($result AS $insert){
				$companyID = $insert['CompanyID'];

				$pdo->exec("INSERT INTO `companycredits`
							SET			`CompanyID` = " . $companyID . ",
										`CreditsID` = " . $creditsID);
			}
			
			$success = $pdo->commit();
			if(!$success){ // If commit failed we have to retry
				$pdo = null;
				return FALSE;
			}				
		}
		$pdo = null;
		return TRUE;	
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}	
}

// Checks if there are any billing periods that have ended for any company
// If there are any then we:
//		Update the company credits history table with the current values
//		Update the billing date periods
// 		Check if company went over booking credits and alert admin including links to the exact booking history
function updateBillingDatesForCompanies(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		// Check if there is any information to change
		$sql = "SELECT 		COUNT(*)
				FROM 		`company` c
				INNER JOIN 	`companycredits` cc
				ON 			cc.`CompanyID` = c.`CompanyID`
				INNER JOIN 	`credits` cr
				ON			cr.`CreditsID` = cc.`CreditsID`
				WHERE 		c.`isActive` = 1
				AND			CURDATE() >= c.`endDate`"
		$return = $pdo->query($sql);
		$rowCount = $return->fetchColumn();
		
		if($rowCount > 0) {
			// There is information to update. Get needed values
			$sql = "SELECT 		c.`CompanyID`				AS TheCompanyID,
								c.`startDate`				AS StartDate,
								c.`endDate`					AS EndDate,
								cr.`minuteAmount`			AS CreditsGivenInMinutes,
								cr.`monthlyPrice`			AS MonthlyPrice,
								cr.`overCreditMinutePrice`	AS MinutePrice,
								cr.`overCreditHourPrice`	AS HourPrice,
								cc.`altMinuteAmount`		AS AlternativeAmount
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
									WHERE 		b.`CompanyID` = TheCompanyID
									AND 		b.`actualEndDateTime`
									BETWEEN		c.`startDate`
									AND			c.`endDate`
								)							AS BookingTimeThisPeriod								
					FROM 		`company` c
					INNER JOIN 	`companycredits` cc
					ON 			cc.`CompanyID` = c.`CompanyID`
					INNER JOIN 	`credits` cr
					ON			cr.`CreditsID` = cc.`CreditsID`
					WHERE 		c.`isActive` = 1
					AND			CURDATE() >= c.`endDate`";
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);
		
			$pdo->beginTransaction();
			foreach($result AS $insert){
				if($insert['AlternativeAmount'] == NULL){
					$creditsGivenInMinutes = $insert['CreditsGivenInMinutes'];
				} else {
					$creditsGivenInMinutes = $insert['AlternativeAmount'];
				}
				$companyID = $insert['TheCompanyID'];
				$startDate = $insert['StartDate'];
				$endDate = $insert['EndDate'];
				$monthlyPrice = $insert['MonthlyPrice'];
				$minutePrice = $insert['MinutePrice'];
				$hourPrice = $insert['HourPrice'];
				$bookingTimeUsedThisMonth = $insert['BookingTimeThisPeriod'];
				$bookingTimeUsedThisMonthInMinutes = convertTimeToMinutes($bookingTimeUsedThisMonth);
				
				if($bookingTimeUsedThisMonthInMinutes > $creditsGivenInMinutes){
					// Company went over credit this period
					$companiesOverCredit[] = array(
													'CompanyID' => $companyID,
													'StartDate' => $startDate,
													'EndDate' 	=> $endDate
													);
				}
				
				$pdo->exec("INSERT INTO `companycreditshistory`
							SET			`CompanyID` = " . $companyID . ",
										`startDate` = '" . $startDate . "',
										`endDate` = '" . $endDate . "',
										`minuteAmount` = " . $creditsGivenInMinutes . ",
										`monthlyPrice` = " . $monthlyPrice . ",
										`overCreditMinutePrice` = " . $minutePrice . ",
										`overCreditHourPrice` = " . $hourPrice);
			}	
		
			$sql = "UPDATE 	`company`
					SET		`prevStartDate` = `startDate`,
							`startDate` = `endDate`,
							`endDate` = (`startDate` + INTERVAL 1 MONTH)
					WHERE	`companyID` <> 0
					AND		CURDATE() >= `endDate`";		
			$pdo->exec($sql);
			$success = $pdo->commit();
			if($success){
				// Check if any of the companies went over credits and send an email to Admin that they did
				if(isset($companiesOverCredit) AND sizeOf($companiesOverCredit) > 0){
					// There were companies that went over credit
					if(sizeOf($companiesOverCredit) == 1){
						// One company went over
						$emailSubject = "A company went over credit!";
						$companyID = $companiesOverCredit[0]['CompanyID'];
						$startDate = $companiesOverCredit[0]['StartDate'];
						$endDate = $companiesOverCredit[0]['EndDate'];					

						//Link example: http://localhost/admin/companies/?companyID=2&BillingStart=2017-05-15&BillingEnd=2017-06-15
						$link = "http://$_SERVER[HTTP_HOST]/admin/companies/?CompanyID=" . $companyID . 
								"&BillingStart=" . $startDate . "&BillingEnd=" . $endDate;
							
						$emailMessage = 
						"Click the link below to see the details\n
						Link: " . $link;		
					} else {
						// More than one company went over
						$emailSubject = "Companies went over credit!";

						$emailMessage = 
						"Click the links below to see the details\n";
						
						foreach($companiesOverCredit AS Url){
							$companyID = url['CompanyID'];
							$startDate = url['StartDate'];
							$endDate = url['EndDate'];
							
							$link = "http://$_SERVER[HTTP_HOST]/admin/companies/?CompanyID=" . $companyID . 
									"&BillingStart=" . $startDate . "&BillingEnd=" . $endDate;

							$emailMessage .= "Link: " . $link . "\n";
						}
					}
					
					// Get admin(s) emails
					$sql = "SELECT 		u.`email`		AS Email
							FROM 		`user` u
							INNER JOIN 	`accesslevel` a
							WHERE		a.`AccessID` = u.`AccessID`
							AND			a.`AccessName` = 'Admin'"
					$return = $pdo->query($sql);
					$result = $return->fetchAll(PDO::FETCH_ASSOC);
					
					if(isset($result)){
						foreach($result AS $Email){
							$email[] = $Email['Email'];
						}
					}
					
					$mailResult = sendEmail($email, $emailSubject, $emailMessage);
					
					if(!$mailResult){
						// TO-DO: What to do if the mail doesn't want to send?
						// Store it somewhere and have another cron try to send emails?
					}						
				}
			} else {
				// If commit failed we have to retry
				$pdo = null;
				return FALSE;
			}			
		}
		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo->rollback();
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

$repetition = 3;
$sleepTime = 1; // Second(s)

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

// The actual actions taken // END //
?>