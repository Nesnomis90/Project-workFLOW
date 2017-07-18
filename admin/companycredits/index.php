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
	unset($_SESSION['EditCompanyCreditsChangeCredits']);
	unset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']);
	unset($_SESSION['EditCompanyCreditsCreditsArray']);
	unset($_SESSION['EditCompanyCreditsOriginalInfo']);
	unset($_SESSION['EditCompanyCreditsSelectedCreditsID']);
	unset($_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID']);
	unset($_SESSION['EditCompanyCreditsNewAlternativeAmount']);
}

// if admin wants to change credits info for the selected company
// we load a new html form
if (	isSet($_POST['action']) AND $_POST['action'] == 'Edit' OR
		isSet($_SESSION['refreshEditCompanyCredits']) AND $_SESSION['refreshEditCompanyCredits'])
{
	if(isSet($_SESSION['refreshEditCompanyCredits']) AND $_SESSION['refreshEditCompanyCredits']){
		// Acknowledge that we have refreshEditCompanyCredits
		unset($_SESSION['refreshEditCompanyCredits']);
		
		$selectedCreditsID = $_SESSION['EditCompanyCreditsSelectedCreditsID'];
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
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditCompanyCreditsOriginalInfo'] = $row;
				
			// Set the correct information
			$_SESSION['EditCompanyCreditsSelectedCreditsID'] = $row['CreditsID'];
			$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = $row['CreditsAlternativeAmount'];
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching company credits details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
		
		// Get information from database again of available credits
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$sql = 'SELECT 	`CreditsID`				AS CreditsID,
							`name`					AS CreditsName,
							`minuteAmount`			AS CreditsGivenInMinutes,
							`monthlyPrice`			AS CreditsMonthlyPrice,
							`overCreditHourPrice`	AS CreditsHourPrice,
							`overCreditMinutePrice`	AS CreditsMinutePrice
					FROM 	`credits`';
			$result = $pdo->query($sql);
				
			//Close connection
			$pdo = null;
			
			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){

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
				$creditsGiven = convertMinutesToHoursAndMinutes($row['CreditsGivenInMinutes']);
				$CreditsInformation = "Name: " . $row['CreditsName'] . ". Monthly Price: " . $creditsMonthlyPrice . ", Credits Given: " . $creditsGiven . ". Over Credits Fee:  " . $creditsOverCreditsFee . ".";
				
				$credits[] = array(
									'CreditsID' => $row['CreditsID'],
									'CreditsName' => $row['CreditsName'],
									'CreditsGivenInMinutes' => $creditsGiven,
									'CreditsInformation' => $CreditsInformation
									);
			}		
			
			$_SESSION['EditCompanyCreditsCreditsArray'] = $credits;
			
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching credits details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}	
	}
	
	// Set original/correct values
	$original = $_SESSION['EditCompanyCreditsOriginalInfo'];	
	$CompanyID = $original['TheCompanyID'];
	$CompanyName = $original['CompanyName'];
	$credits = $_SESSION['EditCompanyCreditsCreditsArray'];

	$BillingStart = $original['CompanyBillingMonthStart'];
	$BillingEnd =  $original['CompanyBillingMonthEnd'];
	$displayBillingStart = convertDatetimeToFormat($BillingStart , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$displayBillingEnd = convertDatetimeToFormat($BillingEnd , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$BillingPeriod = $displayBillingStart . " to " . $displayBillingEnd . ".";	
	
	if($_SESSION['EditCompanyCreditsNewAlternativeAmount'] == NULL){
		$creditsAlternativeAmount = 0;
	} else {
		$creditsAlternativeAmount = $_SESSION['EditCompanyCreditsNewAlternativeAmount'];
	}
	$originalCreditsName = $original['CreditsName']; 
	$originalCreditsAlternativeCreditsAmount = convertMinutesToHoursAndMinutes($original['CreditsAlternativeAmount']);
	$CreditsAlternativeCreditsAmount = $creditsAlternativeAmount;
		
	$selectedCreditsID = $_SESSION['EditCompanyCreditsSelectedCreditsID'];
		
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual form we want to use
	include 'editcompanycredits.html.php';
	exit();
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Set Original Amount'){
	
	$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = $_SESSION['EditCompanyCreditsOriginalInfo']['CreditsAlternativeAmount'];
	
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}	
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Select Amount'){
	$invalidInput = FALSE;
	
	$newAlternativeCreditsAmount = trimAllWhitespace($_POST['CreditsAlternativeCreditsAmount']);
	$validAlternativeCreditAmountFormat = validateIntegerNumber($newAlternativeCreditsAmount);
	if(!$validAlternativeCreditAmountFormat AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCompanyCreditsError'] = "The alt. credit given amount you submitted has illegal characters in it.";		
	}
	$invalidAlternativeCreditAmountSize = isNumberInvalidCreditsAmount($newAlternativeCreditsAmount);
	if($invalidAlternativeCreditAmountSize AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCompanyCreditsError'] = "The alt. credit given amount you submitted is too big.";
	}
	
	if(!$invalidInput){
		unset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']);
		$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = $newAlternativeCreditsAmount;
	}
	
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}		
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Change Amount'){
	$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount'] = TRUE;
	
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}		
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Select Credits'){
	
	// Make admin have to confirm alternative amount when changing credits
	if(	$_POST['CreditsID'] != $_SESSION['EditCompanyCreditsOriginalInfo']['CreditsID'] AND
		$_POST['CreditsID'] != $_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID']){
		$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount'] = TRUE;
		$_SESSION['EditCompanyCreditsNewAlternativeAmount']	= 0;
	}
	
	$_SESSION['EditCompanyCreditsSelectedCreditsID'] = $_POST['CreditsID'];
	
	unset($_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID']);
	unset($_SESSION['EditCompanyCreditsChangeCredits']);
	
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}		
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Change Credits'){
	
	$_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID'] = $_SESSION['EditCompanyCreditsSelectedCreditsID'];
	
	$_SESSION['EditCompanyCreditsChangeCredits'] = TRUE;
	unset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']);
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}		
	header('Location: .');
	exit();	
}

if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){
	
	unset($_SESSION['EditCompanyCreditsChangeCredits']);
	unset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']);
	unset($_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID']);
	
	$original = $_SESSION['EditCompanyCreditsOriginalInfo'];
	
	$_SESSION['EditCompanyCreditsSelectedCreditsID'] = $original['CreditsID'];
	$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = $original['CreditsAlternativeAmount'];
	
	$_SESSION['refreshEditCompanyCredits'] = TRUE;
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}	
	header('Location: .');
	exit();	
}

// If the user clicks any cancel buttons he'll be directed back to the employees page again
if (isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['CompanyCreditsUserFeedback'] = "You cancelled your company credits editing.";
}

// Perform the actual database update of the edited information
if (isSet($_POST['edit']) AND $_POST['edit'] == 'Finish Edit')
{
	// Check if there were any changes made
	$NumberOfChanges = 0;
	$newCredits = FALSE;
	$newAltCredits = FALSE;
	$original = $_SESSION['EditCompanyCreditsOriginalInfo'];
	
	if($_SESSION['EditCompanyCreditsSelectedCreditsID'] != $original['CreditsID']){
		$NumberOfChanges++;
		$newCredits = TRUE;
	}
	if($_SESSION['EditCompanyCreditsNewAlternativeAmount'] == 0){
		$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = NULL;
	}
	if($_SESSION['EditCompanyCreditsNewAlternativeAmount'] != $original['CreditsAlternativeAmount']){
		$NumberOfChanges++;
		$newAltCredits = TRUE;
	}

	if($NumberOfChanges > 0){
		// Update selected company credits connection with a new credits and/or alternative credits amount
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`companycredits` 
					SET		`CreditsID` = :CreditsID,
							`altMinuteAmount` = :CreditsGivenInMinutes,
							`lastModified` = CURRENT_TIMESTAMP
					WHERE 	`CompanyID` = :CompanyID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $original['TheCompanyID']);
			$s->bindValue(':CreditsID', $_SESSION['EditCompanyCreditsSelectedCreditsID']);
			$s->bindValue(':CreditsGivenInMinutes', $_SESSION['EditCompanyCreditsNewAlternativeAmount']);
			$s->execute(); 
					
			//close connection
			$pdo = null;	
		}
		catch (PDOException $e)
		{
			$error = 'Error changing credits information for company: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
		
		$_SESSION['CompanyCreditsUserFeedback'] = "Successfully updated the credits info for the company: " . $original['CompanyName'];
		
		// Add a log event that a company credit was changed
		try
		{
			// Save a description with information about the meeting room that was removed
			if($_SESSION['EditCompanyCreditsNewAlternativeAmount'] == NULL){
				$_SESSION['EditCompanyCreditsNewAlternativeAmount'] = "None";
			}
			
			if($newCredits){
				$credits = $_SESSION['EditCompanyCreditsCreditsArray'];
				foreach($credits AS $row){
					if($row['CreditsID'] == $_SESSION['EditCompanyCreditsSelectedCreditsID']){
						$creditsName = $row['CreditsName'];
						break;
					}
				}
				$description = "The company: " . $original['CompanyName'] . " went from having the Credit: " . $original['CreditsName'] .
								" and the alternative credits given: " . convertMinutesToHoursAndMinutes($original['CreditsAlternativeAmount']) . ".\nTo having the Credit: " .
								$creditsName . " and the alternative credits given: " . convertMinutesToHoursAndMinutes($_SESSION['EditCompanyCreditsNewAlternativeAmount']) .
								".\nThis change was done by: " . $_SESSION['LoggedInUserName'];									
			} elseif(!$newCredits AND $newAltCredits){
				$description = "The company: " . $original['CompanyName'] . " went from having the alternative credits given: " . 
								convertMinutesToHoursAndMinutes($original['CreditsAlternativeAmount']) . ".\nTo having the alternative credits given: " . 
								convertMinutesToHoursAndMinutes($_SESSION['EditCompanyCreditsNewAlternativeAmount']) . ".\nNormally the company would get " . 
								convertMinutesToHoursAndMinutes($original['CreditsGivenInMinutes']) . " from the assigned Credits.\nThis change was done by: " . 
								$_SESSION['LoggedInUserName'];					
			}
	
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Company Credits Changed'
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
	} else {
		$_SESSION['CompanyCreditsUserFeedback'] = "No changes were made to the credits info for the company: " . $original['CompanyName'];
	}

	clearEditCompanyCreditsSessions();
	
	if(isSet($_GET['Company'])){	
		// Refresh CompanyCredits for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}	
		
	// Do a normal page reload
	header('Location: .');
	exit();	
}

if(isSet($refreshCompanyCredits) AND $refreshCompanyCredits){
	// TO-DO: Add code that should occur on a refresh
	unset($refreshCompanyCredits);
}

// Remove any unused variables from memory 
clearEditCompanyCreditsSessions();

// Get only information from the specific company
if(isSet($_GET['Company'])){	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = 'SELECT 			c.`CompanyID`									AS TheCompanyID,
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
				FROM 			`company` c
				INNER JOIN 		`companycredits` cc
				ON 				c.`CompanyID` = cc.`CompanyID`
				INNER JOIN 		`credits` cr
				ON 				cr.`CreditsID` = cc.`CreditsID`
				WHERE 			c.`CompanyID` = :CompanyID
				AND				c.`isActive` > 0
				LIMIT 			1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
		$s->execute();

		$result = $s->fetchAll(PDO::FETCH_ASSOC);
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
if(!isSet($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 			c.`CompanyID`									AS TheCompanyID,
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
				FROM 			`company` c
				INNER JOIN 		`companycredits` cc
				ON 				c.`CompanyID` = cc.`CompanyID`
				INNER JOIN 		`credits` cr
				ON 				cr.`CreditsID` = cc.`CreditsID`
				WHERE 			c.`isActive` > 0
				ORDER BY		UNIX_TIMESTAMP(cc.`datetimeAdded`)
				DESC";
				
		$return = $pdo->query($sql);
		$result = $return->fetchAll(PDO::FETCH_ASSOC);
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