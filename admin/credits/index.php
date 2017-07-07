<?php 
// This is the index file for the CREDITS folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add more/remove if it ruins multiple tabs open

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add Credits form
function clearAddCreditsSessions(){
	unset($_SESSION['AddCreditsDescription']);
	unset($_SESSION['AddCreditsName']);
	unset($_SESSION['LastCreditsID']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit Credits form
function clearEditCreditsSessions(){
	unset($_SESSION['EditCreditsOriginalInfo']);
	
	unset($_SESSION['EditCreditsName']);
	unset($_SESSION['EditCreditsDescription']);
	unset($_SESSION['EditCreditsAmount']);
	unset($_SESSION['EditCreditsMonthlyPrice']);
	unset($_SESSION['EditCreditsMinutePrice']);
	unset($_SESSION['EditCreditsHourPrice']);
	
	unset($_SESSION['EditCreditsCreditsID']);
}

// Function to check if user inputs for credits are correct
function validateUserInputs(){
	$invalidInput = FALSE;
	
	// Get user inputs
	if(isset($_POST['CreditsName']) AND !$invalidInput){
		$creditsName = trim($_POST['CreditsName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a name!";
	}
	if(isset($_POST['CreditsDescription']) AND !$invalidInput){
		$creditsDescription = trim($_POST['CreditsDescription']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a description!";
	}
	if(isset($_POST['CreditsAmount']) AND !$invalidInput){
		$creditsAmount = trim($_POST['CreditsAmount']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a monthly given amount!";
	}
	if(isset($_POST['CreditsMonthlyPrice']) AND !$invalidInput){
		$creditsMonthlyPrice = trim($_POST['CreditsMonthlyPrice']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a monthly subscription price!";
	}
	if(isset($_POST['CreditsHourPrice']) AND !$invalidInput){
		$creditsHourPrice = trim($_POST['CreditsHourPrice']);
	} else {
		$creditsHourPrice = ""; 
		// TO-DO: Change if needed
		// Can be either hour price or minute price, so can be not set
		/*$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a hourly over credits fee!";*/
	}
	if(isset($_POST['CreditsMinutePrice']) AND !$invalidInput){
		$creditsMinutePrice = trim($_POST['CreditsMinutePrice']);
	} else {
		$creditsMinutePrice = ""; 
		// TO-DO: Change if needed
		// Can be either hour price or minute price, so can be not set
		/*$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a minute by minute over credits fee!";*/
	}
	
	// Remove excess whitespace and prepare strings for validation
	$validatedCreditsName = trimExcessWhitespace($creditsName);
	$validatedCreditsDescription = trimExcessWhitespaceButLeaveLinefeed($creditsDescription);
	$validatedCreditsAmount = trimAllWhitespace($creditsAmount);
	$validatedCreditsHourPrice = trimAllWhitespace($creditsHourPrice);
	$validatedCreditsMinutePrice = trimAllWhitespace($creditsMinutePrice);
	$validatedCreditsMonthlyPrice = trimAllWhitespace($creditsMonthlyPrice);
	
	// Are values actually filled in?
	if($validatedCreditsName == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a name for your credits.";	
		$invalidInput = TRUE;		
	}
	if($validatedCreditsDescription == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a description for your credits.";	
		$invalidInput = TRUE;		
	}
	if($validatedCreditsAmount == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a monthly given amount for your credits.";	
		$invalidInput = TRUE;
	}
	if($validatedCreditsMonthlyPrice == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a monthly subscription price for your credits.";	
		$invalidInput = TRUE;
	}	
	if($validatedCreditsHourPrice == "" AND $validatedCreditsMinutePrice == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in an hourly or minute by minute over credits fee for your credits.";	
		$invalidInput = TRUE;		
	}
	if($validatedCreditsHourPrice != "" AND $validatedCreditsMinutePrice != "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You cannot fill in both an hourly and a minute by minute over credits fee for your credits.";	
		$invalidInput = TRUE;		
	}		
	
	// Do actual input validation
	if(validateString($validatedCreditsName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits name has illegal characters in it.";
	}
	if(validateString($validatedCreditsDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits description has illegal characters in it.";
	}
	if(validateIntegerNumber($validatedCreditsAmount) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits amount has illegal characters in it.";
	}
	if($validatedCreditsHourPrice != ""){
		// TO-DO: Make hour rate a float, just in case?
		if(validateIntegerNumber($validatedCreditsHourPrice) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION['EditCreditsError'] = "Your submitted hourly over credits fee has illegal characters in it.";
		}		
	}
	if($validatedCreditsMinutePrice != ""){
		if(validateFloatNumber($validatedCreditsMinutePrice) === FALSE AND !$invalidInput){
			$invalidInput = TRUE;
			$_SESSION['EditCreditsError'] = "Your submitted minute by minute over credits fee has illegal characters in it.";
		}
	}
	// TO-DO: Make float?
	if(validateIntegerNumber($validatedCreditsMonthlyPrice) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted monthly subscription price has illegal characters in it.";
	}	

	// Check if input length is allowed
		// Credits Name
		// Uses same limit as display name (max 255 chars)
	$invalidCreditsName = isLengthInvalidDisplayName($validatedCreditsName);
	if($invalidCreditsName AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits name submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// Credits Description // Just use same check as with equipment description
	$invalidCreditsDescription = isLengthInvalidEquipmentDescription($validatedCreditsDescription);
	if($invalidCreditsDescription AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits description submitted is too long.";	
		$invalidInput = TRUE;		
	}
		// Credits Amount
		// TO-DO: We check the amount in minutes. Does admin also submit in minutes or just hours?
	$invalidCreditsAmount = isNumberInvalidCreditsAmount($validatedCreditsAmount);
	if($invalidCreditsAmount AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits amount submitted is too big.";	
		$invalidInput = TRUE;
	}
		// Credits Hourly Price
	$invalidCreditsHourPrice = isNumberInvalidCreditsHourPrice($validatedCreditsHourPrice);
	if($invalidCreditsHourPrice AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The hourly over credits fee submitted is too big.";	
		$invalidInput = TRUE;
	}
		// Credits Minute Price
	$invalidCreditsMinutePrice = isNumberInvalidCreditsMinutePrice($validatedCreditsMinutePrice);
	if($invalidCreditsMinutePrice AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The minute by minute over credits fee submitted is too big.";	
		$invalidInput = TRUE;
	}
		// Credits Monthly Price
	$invalidCreditsMonthlyPrice = isNumberInvalidCreditsMonthlyPrice($validatedCreditsMonthlyPrice);
	if($invalidCreditsMonthlyPrice AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The monthly subscription price is too big.";	
		$invalidInput = TRUE;
	}
	
	// Check if the credits already exists (based on name).
	$nameChanged = TRUE;
	if(isset($_SESSION['EditCreditsOriginalInfo'])){
		$originalCreditsName = strtolower($_SESSION['EditCreditsOriginalInfo']['CreditsName']);
		$newCreditsName = strtolower($validatedCreditsName);		

		if($originalCreditsName == $newCreditsName){
			$nameChanged = FALSE;
		} 
	}
	if($nameChanged AND !$invalidInput) {
		// Check if new name is taken
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT 	COUNT(*) 
					FROM 	`credits`
					WHERE 	`name`= :creditsName';
			$s = $pdo->prepare($sql);
			$s->bindValue(':creditsName', $validatedCreditsName);		
			$s->execute();
			
			$pdo = null;
			
			$row = $s->fetch();
			
			if ($row[0] > 0)
			{
				// This name is already being used for a credits
				$_SESSION['EditCreditsError'] = "There is already a credits with the name: " . $validatedCreditsName . "!";
				$invalidInput = TRUE;	
			}
			// Credits name hasn't been used before	
		}
		catch (PDOException $e)
		{
			$error = 'Error searching through credits.' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

return array($invalidInput, $validatedCreditsDescription, $validatedCreditsName, $validatedCreditsAmount, $validatedCreditsHourPrice, $validatedCreditsMinutePrice, $validatedCreditsMonthlyPrice);
}

// If admin wants to be able to delete credits it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['creditsEnableDelete'] = TRUE;
	$refreshCredits = TRUE;
}

// If admin wants to be able to delete credits that is currently being used in a room it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Delete Used Credits"){
	$_SESSION['creditsEnableDeleteUsedCredits'] = TRUE;
	$refreshCredits = TRUE;
}

// If admin wants to be disable used credits deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete Used Credits"){
	unset($_SESSION['creditsEnableDeleteUsedCredits']);
	$refreshCredits = TRUE;
}

// If admin wants to be disable credits deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['creditsEnableDelete']);
	unset($_SESSION['creditsEnableDeleteUsedCredits']);
	$refreshCredits = TRUE;
}

// If admin wants to delete no longer wanted Credits
if(isset($_POST['action']) AND $_POST['action'] == 'Delete'){
	// We have one Credits that's should always be in the table and never deleted
	// This one is called 'Default'
	
	if(isset($_POST['CreditsName']) AND $_POST['CreditsName'] == 'Default'){
		// We can't delete this one.
		$_SESSION['CreditsUserFeedback'] = "This Credits cannot be deleted. It is the default given Credits to all new companies.";
	} else {
		// Delete credits from database
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "DELETE FROM `credits` 
					WHERE 		`CreditsID` = :CreditsID
					AND			`name` != 'Default'";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CreditsID', $_POST['CreditsID']);
			$s->execute();
			
			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error removing Credits: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}
		
		$_SESSION['CreditsUserFeedback'] = "Successfully removed the Credits.";
		
		// Add a log event that the Credits has been Deleted
		try
		{
			// Save a description with information about the Credits that was Deleted
			$description = "The Credits: " . $_POST['CreditsName'] . " was removed by: " . $_SESSION['LoggedInUserName'];
			
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Credits Removed'
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
	}
	
	// Load company list again
	header('Location: .');
	exit();	
}

// If admin wants to add Credits to the database
// we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Add Credits') OR
	(isset($_SESSION['refreshAddCredits']) AND $_SESSION['refreshAddCredits']))
{
	// Confirm we've refreshed
	unset($_SESSION['refreshAddCredits']);
	
	// Set form variables to be ready for adding values
	$pageTitle = 'New Credits';
	$CreditsName = '';
	$CreditsDescription = '';
	$CreditsAmount = 0;
	$CreditsHourPrice = '';
	$CreditsMinutePrice = '';
	$CreditsMonthlyPrice = 0;
	$CreditsID = '';
	$button = 'Confirm Credits';
	
	if(isset($_SESSION['AddCreditsDescription'])){
		$CreditsDescription = $_SESSION['AddCreditsDescription'];
		unset($_SESSION['AddCreditsDescription']);
	}
	if(isset($_SESSION['AddCreditsName'])){
		$CreditsName = $_SESSION['AddCreditsName'];
		unset($_SESSION['AddCreditsName']);
	}
	if(isset($_SESSION['AddCreditsAmount'])){
		$CreditsAmount = $_SESSION['AddCreditsAmount'];
		unset($_SESSION['AddCreditsAmount']);
	}	
	if(isset($_SESSION['AddCreditsHourPrice'])){
		$CreditsHourPrice = $_SESSION['AddCreditsHourPrice'];
		unset($_SESSION['AddCreditsHourPrice']);
	}
	if(isset($_SESSION['AddCreditsMonthlyPrice'])){
		$CreditsMonthlyPrice = $_SESSION['AddCreditsMonthlyPrice'];
		unset($_SESSION['AddCreditsMonthlyPrice']);
	}		
	if(isset($_SESSION['AddCreditsMinutePrice'])){
		$CreditsMinutePrice = $_SESSION['AddCreditsMinutePrice'];
		unset($_SESSION['AddCreditsMinutePrice']);
	}
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the Credits
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Credits')
{
	// Validate user inputs
	list($invalidInput, $validatedCreditsDescription, $validatedCreditsName, $validatedCreditsAmount, $validatedCreditsHourPrice, $validatedCreditsMinutePrice, $validatedCreditsMonthlyPrice) = validateUserInputs();
	
	// Refresh form on invalid
	if($invalidInput){
		
		// Refresh.
		$_SESSION['AddCreditsDescription'] = $validatedCreditsDescription;
		$_SESSION['AddCreditsName'] = $validatedCreditsName;
		$_SESSION['AddCreditsAmount'] = $validatedCreditsAmount;
		$_SESSION['AddCreditsMonthlyPrice'] = $validatedCreditsMonthlyPrice;
		$_SESSION['AddCreditsHourPrice'] = $validatedCreditsHourPrice;
		$_SESSION['AddCreditsMinutePrice'] = $validatedCreditsMinutePrice;
		
		$_SESSION['refreshAddCredits'] = TRUE;
		header('Location: .');
		exit();			
	}
	if($validatedCreditsMinutePrice == ""){
		$validatedCreditsMinutePrice = NULL;
	}
	if($validatedCreditsHourPrice == ""){
		$validatedCreditsHourPrice = NULL;
	}	
	// Add the Credits to the database
	try
	{		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `credits` 
				SET			`name` = :CreditsName,
							`description` = :CreditsDescription,
							`minuteAmount` = :CreditsAmount,
							`monthlyPrice` = :CreditsMonthlyPrice,
							`overCreditMinutePrice` = :CreditsMinutePrice,
							`overCreditHourPrice` = :CreditsHourPrice';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CreditsName', $validatedCreditsName);
		$s->bindValue(':CreditsDescription', $validatedCreditsDescription);
		$s->bindValue(':CreditsAmount', $validatedCreditsAmount);
		$s->bindValue(':CreditsMonthlyPrice', $validatedCreditsMonthlyPrice);
		$s->bindValue(':CreditsMinutePrice', $validatedCreditsMinutePrice);
		$s->bindValue(':CreditsHourPrice', $validatedCreditsHourPrice);
		$s->execute();
	
		unset($_SESSION['LastCreditsID']);
		$_SESSION['LastCreditsID'] = $pdo->lastInsertId();	
	
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted Credits to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['CreditsUserFeedback'] = "Successfully added the Credits: " . $validatedCreditsName;
	
		// Add a log event that we added a Credits
	try
	{
		// Format Credits (From minutes to hours and minutes)
		$creditsGivenInMinutes = $validatedCreditsAmount;
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
		if($validatedCreditsMinutePrice != NULL){
			$creditsOverCreditsFee = convertToCurrency($validatedCreditsMinutePrice) . '/min';
		} elseif($validatedCreditsHourPrice != NULL) {
			$creditsOverCreditsFee = convertToCurrency($validatedCreditsHourPrice) . '/hour';
		} else {
			$creditsOverCreditsFee = "Error, not set.";
		}
		
		$creditsMonthlyPrice = convertToCurrency($validatedCreditsMonthlyPrice);
		
	// Save a description with information about the Credits that was added
		$description = "New Credits: " . $validatedCreditsName . "\nwith description: " . 
		$validatedCreditsDescription . ",\nand monthly credits given: " . $creditsGiven . 
		",\nand monthly subscription price: " . $creditsMonthlyPrice . 
		",\nand over credits fee: " . $creditsOverCreditsFee .
		"\nwas added by: " . $_SESSION['LoggedInUserName'];
		
		if(isset($_SESSION['LastCreditsID'])){
			$lastCreditsID = $_SESSION['LastCreditsID'];
			unset($_SESSION['LastCreditsID']);
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		// TO-DO: Add credit amount etc.
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Credits Added'
											),
							`CreditsID` = :TheCreditsID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
		$s->bindValue(':TheCreditsID', $lastCreditsID);
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
	
	clearAddCreditsSessions();
	
	// Load Credits list webpage with new Credits
	header('Location: .');
	exit();
}

// If admin wants to null values while adding
if(isset($_POST['add']) AND $_POST['add'] == 'Reset'){
	
	$_SESSION['AddCreditsDescription'] = "";
	$_SESSION['AddCreditsName'] = "";

	$_SESSION['refreshAddCredits'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Credits overview again
if (isset($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$_SESSION['CreditsUserFeedback'] = "You cancelled your Credits creation.";
	$refreshCredits = TRUE;
}

// if admin wants to edit Credits information
// we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isset($_SESSION['refreshEditCredits']) AND $_SESSION['refreshEditCredits']))
{
	
	// Check if we're activated by a user or by a forced refresh
	if(isset($_SESSION['refreshEditCredits']) AND $_SESSION['refreshEditCredits']){
		//Confirm we've refreshed
		unset($_SESSION['refreshEditCredits']);	
		
		// Get values we had before refresh
		if(isset($_SESSION['EditCreditsDescription'])){
			$CreditsDescription = $_SESSION['EditCreditsDescription'];
			unset($_SESSION['EditCreditsDescription']);
		} else {
			$CreditsDescription = '';
		}		
		if(isset($_SESSION['EditCreditsName'])){
			$CreditsName = $_SESSION['EditCreditsName'];
			unset($_SESSION['EditCreditsName']);
		} else {
			$CreditsName = '';
		}
		if(isset($_SESSION['EditCreditsAmount'])){
			$CreditsAmount = $_SESSION['EditCreditsAmount'];
			unset($_SESSION['EditCreditsAmount']);
		} else {
			$CreditsAmount = 0;
		}
		if(isset($_SESSION['EditCreditsMonthlyPrice'])){
			$CreditsMonthlyPrice = $_SESSION['EditCreditsMonthlyPrice'];
			unset($_SESSION['EditCreditsMonthlyPrice']);
		} else {
			$CreditsMonthlyPrice = 0;
		}	
		if(isset($_SESSION['EditCreditsMinutePrice'])){
			$CreditsMinutePrice = $_SESSION['EditCreditsMinutePrice'];
			unset($_SESSION['EditCreditsMinutePrice']);
		} else {
			$CreditsMinutePrice = '';
		}
		if(isset($_SESSION['EditCreditsHourPrice'])){
			$CreditsHourPrice = $_SESSION['EditCreditsHourPrice'];
			unset($_SESSION['EditCreditsHourPrice']);
		} else {
			$CreditsHourPrice = '';
		}			
		if(isset($_SESSION['EditCreditsCreditsID'])){
			$CreditsID = $_SESSION['EditCreditsCreditsID'];
		}
	} else {
		// Make sure we don't have any relevant values in memory
		clearAddCreditsSessions();
		// Get information from database again on the selected credits
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = "SELECT 		`CreditsID`						AS TheCreditsID,
								`name`							AS CreditsName,
								`description`					AS CreditsDescription,
								`minuteAmount`					AS CreditsGivenInMinutes,
								`monthlyPrice`					AS CreditsMonthlyPrice,
								`overCreditMinutePrice`			AS CreditsMinutePrice,
								`overCreditHourPrice`			AS CreditsHourPrice
					FROM 		`credits`
					WHERE		`CreditsID` = :CreditsID";
					
			$s = $pdo->prepare($sql);
			$s->bindValue(':CreditsID', $_POST['CreditsID']);
			$s->execute();
			
			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditCreditsOriginalInfo'] = $row;
			
			// Set the correct information
			$CreditsID = $row['TheCreditsID'];
			$CreditsName = $row['CreditsName'];
			$CreditsDescription = $row['CreditsDescription'];
			$CreditsAmount = $row['CreditsGivenInMinutes'];
			$CreditsMonthlyPrice = $row['CreditsMonthlyPrice'];
			$CreditsMinutePrice = $row['CreditsMinutePrice'];
			$CreditsHourPrice = $row['CreditsHourPrice'];
			
			$_SESSION['EditCreditsCreditsID'] = $CreditsID;

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching meeting room details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}		
	}

	// Set always correct information
	$pageTitle = 'Edit User';	
	$button = 'Edit Credits';	
	
	// Set original values
	$original = $_SESSION['EditCreditsOriginalInfo'];
	$originalCreditsName = $original['CreditsName'];
	$originalCreditsDescription = $original['CreditsDescription'];
	$originalCreditsAmount = $original['CreditsGivenInMinutes'];
	$originalCreditsMonthlyPrice = $original['CreditsMonthlyPrice'];
	$originalCreditsMinutePrice = $original['CreditsMinutePrice'];
	$originalCreditsHourPrice = $original['CreditsHourPrice'];
	
	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the template we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_POST['action']) AND $_POST['action'] == 'Edit Credits')
{
	// Validate user inputs
	list($invalidInput, $validatedCreditsDescription, $validatedCreditsName, $validatedCreditsAmount, $validatedCreditsHourPrice, $validatedCreditsMinutePrice, $validatedCreditsMonthlyPrice) = validateUserInputs();

	// Make sure we don't try to change the name of the Credits named 'Default'
	// Or try to change the description
	if(isset($_SESSION['EditCreditsOriginalInfo']) AND !$invalidInput){
		if(	$_SESSION['EditCreditsOriginalInfo']['CreditsName'] == 'Default' AND
			$validatedCreditsName != 'Default'){
			$invalidInput = TRUE;
			$_SESSION['EditCreditsError'] = "You can not alter the name of this Credits.";
			$validatedCreditsName = $_SESSION['EditCreditsOriginalInfo']['CreditsName'];
		}
		if(	$_SESSION['EditCreditsOriginalInfo']['CreditsName'] == 'Default' AND
			$validatedCreditsDescription != $_SESSION['EditCreditsOriginalInfo']['CreditsDescription']){
			$invalidInput = TRUE;
			$_SESSION['EditCreditsError'] = "You can not alter the description of this Credits.";
			$validatedCreditsDescription = $_SESSION['EditCreditsOriginalInfo']['CreditsDescription'];
		}		
	}

	// Refresh form on invalid
	if($invalidInput){
		
		// Refresh.
		$_SESSION['EditCreditsDescription'] = $validatedCreditsDescription;
		$_SESSION['EditCreditsName'] = $validatedCreditsName;
		$_SESSION['EditCreditsAmount'] = $validatedCreditsAmount;
		$_SESSION['EditCreditsMonthlyPrice'] = $validatedCreditsMonthlyPrice;
		$_SESSION['EditCreditsMinutePrice'] = $validatedCreditsMinutePrice;
		$_SESSION['EditCreditsHourPrice'] = $validatedCreditsHourPrice;
		
		$_SESSION['refreshEditCredits'] = TRUE;
		header('Location: .');
		exit();			
	}	
	
	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isset($_SESSION['EditCreditsOriginalInfo'])){
		$original = $_SESSION['EditCreditsOriginalInfo'];
		unset($_SESSION['EditCreditsOriginalInfo']);
		
		if($original['CreditsName'] != $validatedCreditsName){
			$numberOfChanges++;
		}
		if($original['CreditsDescription'] != $validatedCreditsDescription){
			$numberOfChanges++;
		}
		if($original['CreditsGivenInMinutes'] != $validatedCreditsAmount){
			$numberOfChanges++;
		}
		if($original['CreditsMonthlyPrice'] != $validatedCreditsMonthlyPrice){
			$numberOfChanges++;
		}
		if($original['CreditsMinutePrice'] != $validatedCreditsMinutePrice){
			$numberOfChanges++;
		}
		if($original['CreditsHourPrice'] != $validatedCreditsHourPrice){
			$numberOfChanges++;
		}
		unset($original);
	}
	
	if($validatedCreditsHourPrice == 0){
		$validatedCreditsHourPrice = NULL;
	}
	if($validatedCreditsMinutePrice == 0){
		$validatedCreditsMinutePrice = NULL;
	}
	
	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`credits`
					SET		`name` = :CreditsName,
							`description` = :CreditsDescription,
							`minuteAmount` = :CreditsGivenInMinutes,
							`monthlyPrice` = :CreditsMonthlyPrice,
							`overCreditMinutePrice`	= :CreditsMinutePrice,
							`overCreditHourPrice` = :CreditsHourPrice,
							`lastModified` = CURRENT_TIMESTAMP
					WHERE 	CreditsID = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['CreditsID']);
			$s->bindValue(':CreditsName', $validatedCreditsName);
			$s->bindValue(':CreditsDescription', $validatedCreditsDescription);
			$s->bindValue(':CreditsGivenInMinutes', $validatedCreditsAmount);
			$s->bindValue(':CreditsMonthlyPrice', $validatedCreditsMonthlyPrice);
			$s->bindValue(':CreditsMinutePrice', $validatedCreditsMinutePrice);
			$s->bindValue(':CreditsHourPrice', $validatedCreditsHourPrice);
			$s->execute();
															
			// Close the connection
			$pdo = Null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating submitted Credits: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		
		$_SESSION['CreditsUserFeedback'] = "Successfully updated the Credits: " . $validatedCreditsName;		
	} else {
		$_SESSION['CreditsUserFeedback'] = "No changes were made to the Credits: " . $validatedCreditsName;
	}

	clearEditCreditsSessions();

	// Load Credits list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isset($_POST['edit']) AND $_POST['edit'] == 'Reset'){
	
	$original = $_SESSION['EditCreditsOriginalInfo'];
	$_SESSION['EditCreditsName'] = $original['CreditsName'];
	$_SESSION['EditCreditsDescription'] = $original['CreditsDescription'];
	$_SESSION['EditCreditsAmount'] = $original['CreditsGivenInMinutes'];
	$_SESSION['EditCreditsMonthlyPrice'] = $original['CreditsMonthlyPrice'];
	$_SESSION['EditCreditsMinutePrice'] = $original['CreditsMinutePrice'];
	$_SESSION['EditCreditsHourPrice'] = $original['CreditsHourPrice'];
	unset($original);
	
	$_SESSION['refreshEditCredits'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Credits overview again
if (isset($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['CreditsUserFeedback'] = "You cancelled your Credits editing.";
	$refreshCredits = TRUE;
}

if(isset($refreshCredits) AND $refreshCredits) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshCredits);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearAddCreditsSessions();
clearEditCreditsSessions();

// Display Credits list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	
	$sql = "SELECT 		cr.`CreditsID`									AS TheCreditsID,
						cr.`name`										AS CreditsName,
						cr.`description`								AS CreditsDescription,
						cr.`minuteAmount`								AS CreditsGivenInMinutes,
						cr.`monthlyPrice`								AS CreditsMonthlyPrice,
						cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
						cr.`overCreditHourPrice`						AS CreditsHourPrice,
						cr.`lastModified`								AS CreditsLastModified,
						cr.`datetimeAdded`								AS DateTimeAdded,
						UNIX_TIMESTAMP(cr.`datetimeAdded`)				AS OrderByDate,
						(
							SELECT 	COUNT(cc.`CreditsID`)
							FROM 	`companycredits` cc
							WHERE 	cc.`CreditsID` = TheCreditsID
						)												AS CreditsIsUsedByThisManyCompanies
			FROM 		`credits` cr
			ORDER BY	OrderByDate
			DESC";
			
	$return = $pdo->query($sql);
	$result = $return->fetchAll(PDO::FETCH_ASSOC);
	if(isset($result)){
		$rowNum = sizeOf($result);
	} else {
		$rowNum = 0;
	}
	//close connection
	$pdo = null;
		
}
catch (PDOException $e)
{
	$error = 'Error getting Credits information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}	

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){
	
	// Format datetimes
	$addedDateTime = $row['DateTimeAdded'];
	$modifiedDateTime = $row['CreditsLastModified'];
	$displayAddedDateTime = convertDatetimeToFormat($addedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$displayModifiedDateTime = convertDatetimeToFormat($modifiedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	
	// Format Credits (From minutes to hours and minutes)
	$creditsGivenInMinutes = $row['CreditsGivenInMinutes'];
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
	$credits[] = array(
							'TheCreditsID' => $row['TheCreditsID'],
							'CreditsName' => $row['CreditsName'],
							'CreditsDescription' => $row['CreditsDescription'],
							'CreditsGiven' => $creditsGiven,
							'CreditsMonthlyPrice' => $creditsMonthlyPrice,
							'CreditsOverCreditsFee' => $creditsOverCreditsFee,
							'DateTimeAdded' => $displayAddedDateTime,
							'CreditsLastModified' => $displayModifiedDateTime,
							'CreditsIsUsedByThisManyCompanies' => $row['CreditsIsUsedByThisManyCompanies']							
						);
}
var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Credits list in HTML
include_once 'credits.html.php';
?>