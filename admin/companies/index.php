<?php 
// This is the index file for the COMPANIES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add company form
function clearAddCompanySessions(){
	unset($_SESSION['AddCompanyCompanyName']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit company form
function clearEditCompanySessions(){
	
	unset($_SESSION['EditCompanyOriginalName']);
	unset($_SESSION['EditCompanyOriginalRemoveDate']);
	
	unset($_SESSION['EditCompanyOldName']);
	unset($_SESSION['EditCompanyOldRemoveDate']);
	
	unset($_SESSION['EditCompanyCompanyID']);
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
	if(isset($_SESSION['EditCompanyOriginalName']) AND $_SESSION['EditCompanyOriginalName'] == $validatedCompanyName){
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
return array($invalidInput, $validatedCompanyName, $validatedCompanyDateToRemove);
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
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
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
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Company Removed'
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
		if(isset($_SESSION['EditCompanyOldName'])){
			$CompanyName = $_SESSION['EditCompanyOldName'];
		} else {
			$CompanyName = '';
		}
			
		if(isset($_SESSION['EditCompanyCompanyID'])){
			$id = $_SESSION['EditCompanyCompanyID'];
		} else {
			// TO-DO: What do we do if we're editing and suddenly no ID?
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
		$row = $s->fetch();
		$CompanyName = $row['name'];
		$DateToRemove = $row['removeAtDate'];
		$id = $row['companyID'];
		
		if(!isset($DateToRemove)){
			$DateToRemove = '';
		}
		$_SESSION['EditCompanyOriginalName'] = $CompanyName;
		$_SESSION['EditCompanyOriginalRemoveDate'] = $DateToRemove;
		$_SESSION['EditCompanyCompanyID'] = $id;
	}
	// Display original values
	$originalCompanyName = $_SESSION['EditCompanyOriginalName'];
	$originalDateToDisplay = convertDatetimeToFormat($_SESSION['EditCompanyOriginalRemoveDate'] , 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);

	if(isset($_SESSION['EditCompanyOldRemoveDate'])){
		$DateToRemove = $_SESSION['EditCompanyOldRemoveDate'];
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
				SET			`name` = :CompanyName';
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
		$_SESSION['EditCompanyOldName'] = $validatedCompanyName;
		$_SESSION['EditCompanyOldRemoveDate'] = $validatedCompanyDateToRemove;

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

if(isset($refreshcompanies) AND $refreshcompanies) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshcompanies);
}

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddCompanySessions();
clearEditCompanySessions();

// Display companies list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	// TO-DO: Fix SQL query if time is broken after change
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
							AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
							AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
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
						)   												AS TotalCompanyWideBookingTimeUsed
			FROM 		`company` c 
			GROUP BY 	c.`name`";
			
			// TO-DO: REVERT BIG_SEC_TO_TIME AND FIND ALTERNATE SOLUTION IF BROKEN
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();
	
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
	// TO-DO: Change booking time used from time to easily readable text instead if needed

	if($row['MonthlyCompanyWideBookingTimeUsed'] == null){
		$MonthlyTimeUsed = 'N/A';
	} else {
		$MonthlyTimeUsed = $row['MonthlyCompanyWideBookingTimeUsed'];
	}
	
	if($row['TotalCompanyWideBookingTimeUsed'] == null){
		$TotalTimeUsed = 'N/A';
	} else {
		$TotalTimeUsed = $row['TotalCompanyWideBookingTimeUsed'];
	}
	
	$dateCreated = $row['DatetimeCreated'];	
	$dateToRemove = $row['DeletionDate'];
	$dateTimeCreatedToDisplay = convertDatetimeToFormat($dateCreated, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateToRemoveToDisplay = convertDatetimeToFormat($dateToRemove, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	
	if($row['CompanyActivated'] == 1){
		$companies[] = array('id' => $row['CompID'], 
						'CompanyName' => $row['CompanyName'],
						'NumberOfEmployees' => $row['NumberOfEmployees'],
						'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
						'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
						'DeletionDate' => $dateToRemoveToDisplay,
						'DatetimeCreated' => $dateTimeCreatedToDisplay
						);
	} elseif($row['CompanyActivated'] == 0) {
		$inactivecompanies[] = array('id' => $row['CompID'], 
						'CompanyName' => $row['CompanyName'],
						'DatetimeCreated' => $dateTimeCreatedToDisplay
						);		
	}	
}


// Create the companies list in HTML
include_once 'companies.html.php';
?>