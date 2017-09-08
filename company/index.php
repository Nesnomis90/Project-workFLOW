<?php 
// This is the index file for the company folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Function to clear sessions used to remember user inputs on refreshing the add employee form
function clearAddEmployeeAsOwnerSessions(){
	unset($_SESSION['AddEmployeeAsOwnerUserSearch']);

	unset($_SESSION['AddEmployeeAsOwnerSelectedUserID']);
	unset($_SESSION['AddEmployeeAsOwnerSelectedPositionID']);

	unset($_SESSION['AddEmployeeAsOwnerCompanyPositionArray']);
	unset($_SESSION['AddEmployeeAsOwnerUsersArray']);
	
	unset($_SESSION['AddEmployeeAsOwnerAutoFillInEmail']);
}
// Function to clear sessions used to remember user inputs on refreshing the edit employee form
function clearEditEmployeeAsOwnerSessions(){
	unset($_SESSION['EditEmployeeAsOwnerOriginalPositionID']);
}

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND isSet($_SESSION['LoggedInUserID']) AND !empty($_SESSION['LoggedInUserID'])){
	$gotoPage = ".";
	userIsLoggedIn();
} else {
	var_dump($_SESSION); // TO-DO: remove after testing is done	

	include_once 'company.html.php';
	exit();
}

// If admin wants to be able to delete companies it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Remove"){
	$_SESSION['normalEmployeesEnableDelete'] = TRUE;
}

// If admin wants to be disable company deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Remove"){
	unset($_SESSION['normalEmployeesEnableDelete']);
}

unsetSessionsFromUserManagement();

if(isSet($_SESSION['normalCompanyCreateACompany']) AND $_SESSION['normalCompanyCreateACompany'] == "Invalid"){
	$_SESSION['normalCompanyCreateACompany'] = TRUE;
}

if(!isSet($_GET['ID']) AND !isSet($_GET['employees']) AND !isSet($_SESSION['normalUserSettingCompanyID'])){
	unset($_SESSION['normalUserCompanyIDSelected']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Create A Company"){
	$_SESSION['normalCompanyCreateACompany'] = TRUE;
	unset($_SESSION['normalCompanyJoinACompany']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Join A Company"){
	$_SESSION['normalCompanyJoinACompany'] = TRUE;
	unset($_SESSION['normalCompanyCreateACompany']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Confirm"){
	// Validate text input
	$invalidInput = FALSE;

	if(isSet($_POST['createACompanyName'])){
		$companyName = trim($_POST['createACompanyName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['normalCompanyFeedback'] = "Company cannot be created without a name!";
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedCompanyName = trimExcessWhitespace($companyName);

	// Do actual input validation
	if(validateString($validatedCompanyName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['normalCompanyFeedback'] = "Your submitted company name has illegal characters in it.";
	}

	// Are values actually filled in?
	if($validatedCompanyName == "" AND !$invalidInput){
		$_SESSION['normalCompanyFeedback'] = "You need to fill in a name for the company.";	
		$invalidInput = TRUE;		
	}

	// Check if input length is allowed
		// CompanyName
		// Uses same limit as display name (max 255 chars)
	$invalidCompanyName = isLengthInvalidDisplayName($validatedCompanyName);
	if($invalidCompanyName AND !$invalidInput){
		$_SESSION['normalCompanyFeedback'] = "The company name submitted is too long.";	
		$invalidInput = TRUE;
	}

	// Check if name is already taken
	if(!$invalidInput){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			// Check for company names
			$pdo = connect_to_db();
			$sql = 'SELECT 	COUNT(*)
					FROM 	`company`
					WHERE 	`name` = :CompanyName
					LIMIT 	1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyName', $validatedCompanyName);
			$s->execute();

			$row = $s->fetch();

			if ($row[0] > 0)
			{
				// This name is already being used for a company	
				$_SESSION['normalCompanyFeedback'] = "There is already a company with the name: " . $validatedCompanyName . "!";
				$invalidInput = TRUE;
				$pdo = null;
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


	if(!$invalidInput){
		// Create Company
		try
		{
			$sql = 'INSERT INTO `company` 
					SET			`name` = :CompanyName,
								`startDate` = CURDATE(),
								`endDate` = (CURDATE() + INTERVAL 1 MONTH)';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyName', $validatedCompanyName);
			$s->execute();
			
			$lastCompanyID = $pdo->lastInsertId();
		}
		catch (PDOException $e)
		{
			$error = 'Error adding submitted company to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		
		$_SESSION['normalCompanyFeedback'] = "Successfully added the company: " . $validatedCompanyName . ".";

			// Give the company the default subscription
		try
		{
			$sql = "INSERT INTO `companycredits` 
					SET			`CompanyID` = :CompanyID,
								`CreditsID` = (
												SELECT 	`CreditsID`
												FROM	`credits`
												WHERE	`name` = 'Default'
												)";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $lastCompanyID);
			$s->execute();
		}
		catch (PDOException $e)
		{
			$error = 'Error giving company a booking subscription: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// Make user owner of company
		try
		{
			$sql = "INSERT INTO `employee` 
					SET			`CompanyID` = :CompanyID,
								`UserID`	= :UserID,
								`PositionID` = (
												SELECT 	`PositionID`
												FROM	`companyposition`
												WHERE	`name` = 'Owner'
												)";
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $lastCompanyID);
			$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
			$s->execute();
		}
		catch (PDOException $e)
		{
			$error = 'Error giving company a booking subscription: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// Add a log event that a company was added
		try
		{
			// Save a description with information about the meeting room that was added
			$userinfo = $_SESSION['LoggedInUserName'] . " - " . $_SESSION['email'];
			$logEventdescription = "The company: " . $validatedCompanyName . "\nWas created by the user: " . $userinfo;

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Company Created'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventdescription);
			$s->execute();
		}
		catch(PDOException $e)
		{
			$error = 'Error adding log event to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// Add a log event that an employee was added (owner)
		try
		{
			// Save a description with information about the employee that was added
			// to the company.
			$userinfo = $_SESSION['LoggedInUserName'] . " - " . $_SESSION['email'];
			$logEventDescription = 'The user: ' . $userinfo . "\nWas automatically given the role: Owner\nIn the company: " . $validatedCompanyName . " on creation.";

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Employee Added'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventDescription);
			$s->execute();
		}
		catch(PDOException $e)
		{
			$error = 'Error adding log event to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		// Send email to admin(s) that a company has been created
		try
		{
			// Get admin(s) emails
			$sql = "SELECT 		u.`email`		AS Email
					FROM 		`user` u
					INNER JOIN 	`accesslevel` a
					ON			a.`AccessID` = u.`AccessID`
					WHERE		a.`AccessName` = 'Admin'
					AND			u.`sendAdminEmail` = 1";
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);

			if(isSet($result)){
				foreach($result AS $Email){
					$email[] = $Email['Email'];
				}
			}

			// Only try to send out email if there are any admins that have set they want them
			if(isSet($email)){
				$emailSubject = "New Company Created";

				$emailMessage = "The user: " . $_SESSION['LoggedInUserName'] .
								"\ncreated a new company: " . $validatedCompanyName;

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				if(!$mailResult){
					$_SESSION['normalCompanyFeedback'] .= "\n\n[WARNING] System failed to send Email to Admin.";
					// TO-DO: FIX-ME: What to do if the mail doesn't want to send?
					// Store it somewhere and have a cron try to send emails?
				}
				$email = implode($email,", ");
				$_SESSION['normalCompanyFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to email(s): $email."; // TO-DO: Remove after testing				
			}
			// close connection
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error sending mail to Admin: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		unset($_SESSION['normalCompanyCreateACompany']);
		unset($_SESSION['normalUserOriginalInfoArray']);
	} else {
		$_SESSION['normalCompanyCreateACompany'] = "Invalid";
	}
}

if(isSet($_POST['action']) AND $_POST['action'] == "Cancel"){
	unset($_SESSION['normalCompanyCreateACompany']);
	unset($_SESSION['normalCompanyJoinACompany']);
	unset($_SESSION['AddEmployeeAsOwnerAutoFillInEmail']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Request To Join"){
	unset($_SESSION['normalCompanyCreateACompany']);

	$continueRequest = FALSE;

	if(isSet($_POST['selectedCompanyToJoin']) AND !empty($_POST['selectedCompanyToJoin'])){
		$selectedCompanyToJoinID = $_POST['selectedCompanyToJoin'];
		$continueRequest = TRUE;
	} else {
		$_SESSION['normalCompanyFeedback'] = "Failed to identify the company you wanted to join, so the process was aborted.";
	}

	if($continueRequest){
		if(isSet($_POST['requestToJoinMessage']) AND !empty($_POST['requestToJoinMessage'])){
			$requestMessage = trimExcessWhitespaceButLeaveLinefeed($_POST['requestToJoinMessage']);
		} else {
			$requestMessage = "N/A";
		}

		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();

			$sql = "SELECT 	`name`	AS CompanyName
					FROM	`company`
					WHERE	`companyID` = :companyID
					LIMIT 	1";
			$s = $pdo->prepare($sql);
			$s->bindValue(":companyID", $selectedCompanyToJoinID);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);
			
			if(isSet($row['CompanyName']) AND !empty($row['CompanyName'])){
				$companyName = $row['CompanyName'];
			}
		}
		catch(PDOException $e)
		{
			$error = 'Error getting company name: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		var_dump($_SESSION);	// TO-DO: Remove before uploading
		include_once 'confirmrequest.html.php';
		exit();
	}
}

if(isSet($_POST['confirm']) AND $_POST['confirm'] == "Yes, Send The Request"){
	
	$continueRequest = FALSE;

	if(isSet($_POST['companyID']) AND !empty($_POST['companyID'])){
		$selectedCompanyToJoinID = $_POST['companyID'];
		$continueRequest = TRUE;
	} else {
		$_SESSION['normalCompanyFeedback'] = "Failed to identify the company you wanted to join, so the process was aborted.";
	}
	
	if($continueRequest = TRUE){
		if(isSet($_POST['requestMessage']) AND !empty($_POST['requestMessage'])){
			$requestMessage	= $_POST['requestMessage'];
		} else {
			$requestMessage = "N/A";
		}
		if(isSet($_POST['companyName']) AND !empty($_POST['companyName'])){
			$companyName = $_POST['companyName'];
		} else {
			$companyName = "N/A";
		}

		// Get company owner(s) in company and check if they want to receive email.
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			// TO-DO: Limit the companies to the ones that have owners that want to receive email?
			$sql = 'SELECT		u.`email`					AS Email,
								u.`sendOwnerEmail`			AS SendOwnerEmail,
								e.`sendEmailOnceOrAlways`	AS SendEmailOnceOrAlways
					FROM 		`user` u
					INNER JOIN	`employee` e
					ON 			e.`UserID` = u.`UserID`
					INNER JOIN	`company` c
					ON 			c.`CompanyID` = e.`CompanyID`
					INNER JOIN	`companyposition` cp
					ON			e.`PositionID` = cp.`PositionID`
					WHERE 		c.`CompanyID` = :CompanyID
					AND			cp.`name` = "Owner"';

			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $selectedCompanyToJoinID);
			$s->execute();
			$result = $s->fetchAll(PDO::FETCH_ASSOC);

			if(isSet($result) AND !empty($result)){
				foreach($result AS $row){
					// Check if user wants to receive owner emails?
					// TO-DO: Send regardless?
					if($row['SendOwnerEmail'] == 1){
						$companyOwnerEmails[] = $row['Email'];
					}
				}
			}

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error getting company owner contact information: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

			// We found company owners to send email too
		if(isSet($companyOwnerEmails) AND !empty($companyOwnerEmails)){
			$emailSubject = "A user wants to join your company!";

			// Send direct link to auto-fill in user info for confirming employee status.
			$url = $_SERVER['HTTP_HOST'] . "/company/?ID=" . $selectedCompanyToJoinID . "&employees&add=" . $_SESSION['email'];

			$emailMessage = 
			"The user: " . $_SESSION['LoggedInUserName'] .
			"\nWho can be contacted at: " . $_SESSION['email'] . 
			"\nHas requested to join your company: " . $companyName .
			"\n\nIf you want to add this user, log in to the url below and add the user" .
			"\nLink: " . $url;

			$email = $companyOwnerEmails;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			if(!$mailResult){
				$_SESSION['normalCompanyFeedback'] .= "\n\n[WARNING] System failed to send Email to user(s).";
			}

			$email = implode(", ", $email);

			$_SESSION['normalCompanyFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading			
		} else {
			if(isSet($_SESSION['normalCompanyFeedback'])){
				$_SESSION['normalCompanyFeedback'] .= "\n\nThe request couldn't be made, since there were no company owner(s) that wanted to be contacted."; // TO-DO: Remove before uploading.
			} else {
				$_SESSION['normalCompanyFeedback'] = "The request couldn't be made, since there were no company owner(s) that wanted to be contacted."; // TO-DO: Remove before uploading.
			}
		}
	}
	unset($_SESSION['normalCompanyJoinACompany']);
}

if(isSet($_POST['confirm']) AND $_POST['confirm'] == "No, Cancel The Request"){
	$_SESSION['normalCompanyFeedback'] = "Cancelled your request.";
	unset($_SESSION['normalCompanyJoinACompany']);
}

if(isSet($_POST['action']) AND $_POST['action'] == "Select Company"){
	unset($_SESSION['normalCompanyCreateACompany']);
	unset($_SESSION['normalCompanyJoinACompany']);
	if(isSet($_POST['selectedCompanyToDisplay']) AND !empty($_POST['selectedCompanyToDisplay'])){
		$selectedCompanyToDisplayID = $_POST['selectedCompanyToDisplay'];
		$_SESSION['normalUserCompanyIDSelected'] = $selectedCompanyToDisplayID;
	} else {
		unset($_SESSION['normalUserCompanyIDSelected']);
	}
} elseif(isSet($_GET['ID']) AND !empty($_GET['ID'])) {
	$selectedCompanyToDisplayID = $_GET['ID'];
	$_SESSION['normalUserCompanyIDSelected'] = $selectedCompanyToDisplayID;
}

// TO-DO: FIX-ME: Needs testing
if(isSet($_GET['add']) AND $_GET['add'] != ""){
	$_SESSION['AddEmployeeAsOwnerAutoFillInEmail'] = $_GET['add'];
	header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected'] . "&employees");
	exit();
}

if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn']){
	if(isSet($_SESSION['normalUserCompanyIDSelected']) AND !isSet($_GET['ID']) AND !isSet($_GET['employees'])){
		header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected']);
	} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND isSet($_GET['ID']) AND $_GET['ID'] != $_SESSION['normalUserCompanyIDSelected'] AND !isSet($_GET['employees'])) {
		header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected']);
	} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND !isSet($_GET['ID']) AND isSet($_GET['employees'])){
		header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected'] . "&employees");
	} elseif(isSet($_SESSION['normalUserCompanyIDSelected']) AND isSet($_GET['ID']) AND $_GET['ID'] != $_SESSION['normalUserCompanyIDSelected'] AND isSet($_GET['employees'])) {
		header("Location: .?ID=" . $_SESSION['normalUserCompanyIDSelected'] . "&employees");
	}
} else {
	clearAddEmployeeAsOwnerSessions();
	clearEditEmployeeAsOwnerSessions();

	header("Location: /company/");
}

// if admin wants to change the company role for a user
// we load a new html form
if (isSet($_POST['action']) AND $_POST['action'] == 'Change Role')
{
	// Get information from database again on the selected employee
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		// Get name and IDs for company position
		$pdo = connect_to_db();
		$sql = 'SELECT 	`PositionID`,
						`name` 			AS CompanyPositionName,
						`description`	AS CompanyPositionDescription
				FROM 	`companyposition`';
		$result = $pdo->query($sql);

		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$companyposition[] = array(
									'PositionID' => $row['PositionID'],
									'CompanyPositionName' => $row['CompanyPositionName'],
									'CompanyPositionDescription' => $row['CompanyPositionDescription']
									);
		}

		// Get employee information
		$sql = 'SELECT 		u.`userID`					AS UsrID,
							c.`companyID`				AS TheCompanyID,
							c.`name`					AS CompanyName,
							u.`firstName`, 
							u.`lastName`,
							cp.`PositionID`,
							cp.`name`					AS PositionName							
				FROM 		`company` c 
				INNER JOIN 	`employee` e
				ON 			e.CompanyID = c.CompanyID 
				INNER JOIN 	`companyposition` cp 
				ON 			cp.PositionID = e.PositionID
				INNER JOIN 	`user` u 
				ON 			u.userID = e.UserID
				WHERE		e.userID = :UserID
				AND 		e.companyID = :CompanyID
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->execute();

		//Close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching employee details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();		
	}

	// Create an array with the row information we retrieved
	$row = $s->fetch(PDO::FETCH_ASSOC);

	// Set the correct information
	$CompanyName = $row['CompanyName'];
	$UserIdentifier = $row['firstName'] . ' ' . $row['lastName'];
	$CurrentCompanyPositionName = $row['PositionName'];
	$CompanyID = $row['TheCompanyID'];
	$UserID = $row['UsrID'];
	$_SESSION['EditEmployeeAsOwnerOriginalPositionID'] = $row['PositionID'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the actual form we want to use
	include 'changerole.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isSet($_POST['action']) AND $_POST['action'] == 'Confirm Role')
{
	// Check if anything actually changed
	$theSelectedPositionID = $_POST['PositionID'];
	$NumberOfChanges = 0;

	if(	isSet($_SESSION['EditEmployeeAsOwnerOriginalPositionID']) AND 
		$_SESSION['EditEmployeeAsOwnerOriginalPositionID'] != $theSelectedPositionID){
		$NumberOfChanges++;
	}

	if($_SESSION['LoggedInUserID'] != $_POST['UserID']){

		if($NumberOfChanges > 0){
			// Update selected employee connection with a new company position
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

				$pdo = connect_to_db();
				$sql = 'UPDATE 	`employee` 
						SET		`PositionID` = :PositionID
						WHERE 	`companyID` = :CompanyID
						AND 	`userID` = :UserID
						AND		`userID` <> :LoggedInUserID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':CompanyID', $_POST['CompanyID']);
				$s->bindValue(':UserID', $_POST['UserID']);
				$s->bindValue(':LoggedInUserID', $_SESSION['LoggedInUserID']);
				$s->bindValue(':PositionID', $theSelectedPositionID);
				$s->execute(); 

				//close connection
				$pdo = null;
			}
			catch (PDOException $e)
			{
				$error = 'Error changing company position in employee information: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}

			$_SESSION['EmployeeUserFeedback'] = "Successfully updated the employee.";
		} else {
			$_SESSION['EmployeeUserFeedback'] = "No changes were made to the employee.";
		}
	} else {
		$_SESSION['EmployeeUserFeedback'] = "No changes were made since you tried to alter your own role.";
	}
 
	clearEditEmployeeAsOwnerSessions();

	$TheCompanyID = $_SESSION['normalUserCompanyIDSelected'];
	$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
	header("Location: $location");
	exit();
}

// If company owner wants to remove an employee from their company
if(isSet($_POST['action']) AND $_POST['action'] == 'Remove'){
	// Remove employee connection in database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'DELETE FROM `employee` 
				WHERE 		`companyID` = :CompanyID
				AND 		`userID` = :UserID
				AND 		`userID` <> :LoggedInUserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':LoggedInUserID', $_SESSION['LoggedInUserID']);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error deleting employee connection: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	$_SESSION['EmployeeUserFeedback'] = "Successfully removed the employee.";

	// Add a log event that an employee was removed from a company
	try
	{
		// Save a description with information about the employee that was removed
		// from the company.
		$userName = $_POST['UserName'];
		if(empty($userName) OR $userName == ", "){
			$userName = "N/A";
		}
		$companyName = $_POST['CompanyName'];
		if(empty($companyName)){
			$companyName = "N/A";
		}

		$logEventDescription = 'The user: ' . $userName . 
		' was removed from the company: ' . $companyName . 
		".\nRemoved by: " . $_SESSION['LoggedInUserName'];

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Employee Removed'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);	
		$s->bindValue(':description', $logEventDescription);
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

	$TheCompanyID = $_GET['ID'];
	$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
	header("Location: $location");
	exit();
}

// Company owner clicked the search button, trying to limit the shown user lists
if(isSet($_POST['action']) AND $_POST['action'] == 'Search'){
	// Forget the old array values we have saved
	unset($_SESSION['AddEmployeeAsOwnerUsersArray']);

	$_SESSION['AddEmployeeAsOwnerShowSearchResults'] = TRUE;
	// Let's remember what was selected and searched for

	$userSearchString = $_POST['usersearchstring'];
	$_SESSION['AddEmployeeAsOwnerUserSearch'] = trimExcessWhitespace($userSearchString);
	$_SESSION['AddEmployeeAsOwnerSelectedUserID'] = $_POST['UserID'];
	$_SESSION['AddEmployeeAsOwnerSelectedPositionID'] = $_POST['PositionID'];

	$_SESSION['refreshAddEmployeeAsOwner'] = TRUE;
	$TheCompanyID = $_GET['ID'];
	$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
	header("Location: $location");
	exit();
}

// 	If company owner wants to add an employee to a company in the database
// 	we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Add Employee') OR 
	(isSet($_SESSION['refreshAddEmployeeAsOwner']) AND $_SESSION['refreshAddEmployeeAsOwner']))
{
	$usersearchstring = '';

	if(isSet($_SESSION['normalUserCompanyIDSelected'], $_SESSION['normalUserCompanyNameSelected'])){
		$companyID = $_SESSION['normalUserCompanyIDSelected'];
		$companyName = $_SESSION['normalUserCompanyNameSelected'];
	} else {
		clearAddEmployeeAsOwnerSessions();

		$_SESSION['normalCompanyFeedback'] = "Failed to add employee. Try again.";

		$location = "http://$_SERVER[HTTP_HOST]/company/";
		header("Location: $location");
		exit();
	}

	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshAddEmployeeAsOwner']) AND $_SESSION['refreshAddEmployeeAsOwner']){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddEmployeeAsOwner']);

		// Remember the user string that was searched before refreshing
		if(isSet($_SESSION['AddEmployeeAsOwnerUserSearch'])){
			$usersearchstring = $_SESSION['AddEmployeeAsOwnerUserSearch'];
			unset($_SESSION['AddEmployeeAsOwnerUserSearch']);
		}

		// Remember what user was selected before refreshing
		if(isSet($_SESSION['AddEmployeeAsOwnerSelectedUserID'])){
			$selectedUserID = $_SESSION['AddEmployeeAsOwnerSelectedUserID'];
			unset($_SESSION['AddEmployeeAsOwnerSelectedUserID']);
		}

		// Remember what company position was selected before refreshing
		if(isSet($_SESSION['AddEmployeeAsOwnerSelectedPositionID'])){
			$selectedPositionID = $_SESSION['AddEmployeeAsOwnerSelectedPositionID'];
			unset($_SESSION['AddEmployeeAsOwnerSelectedPositionID']);
		}
	}

	// Get info about company position, users and companies from the database
	// if we don't already have them saved in a session array
	if(!isSet($_SESSION['AddEmployeeAsOwnerUsersArray']) OR !isSet($_SESSION['AddEmployeeAsOwnerCompanyPositionArray'])){	

		try
		{
			// Get all users and companypositions so owner can search/choose from them
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

				// Company Positions
			//Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddEmployeeAsOwnerCompanyPositionArray'])){
				// We don't have info about company position saved. Let's get it

				$sql = 'SELECT 	`PositionID`,
								`name` 			AS CompanyPositionName,
								`description`	AS CompanyPositionDescription
						FROM 	`companyposition`';
				$result = $pdo->query($sql);

				// Get the rows of information from the query
				// This will be used to create a dropdown list in HTML
				foreach($result as $row){
					$companyposition[] = array(
											'PositionID' => $row['PositionID'],
											'CompanyPositionName' => $row['CompanyPositionName'],
											'CompanyPositionDescription' => $row['CompanyPositionDescription']
											);
				}

				$_SESSION['AddEmployeeAsOwnerCompanyPositionArray'] = $companyposition;
			} else {
				$companyposition = $_SESSION['AddEmployeeAsOwnerCompanyPositionArray'];
			}

				//	Users - Only active ones
			// Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddEmployeeAsOwnerUsersArray'])){
				// We don't have info about users saved. Let's get it

				$sql = "SELECT 	`userID` 	AS UserID,
								`firstname`,
								`lastname`,
								`email`
						FROM 	`user`
						WHERE	`isActive` > 0
						AND		(`UserID` NOT IN 
								(
									SELECT 	`UserID`
									FROM	`employee`
									WHERE	`CompanyID` = :CompanyID
								))";

				if ($usersearchstring != ''){
					$sqladd = " AND (`firstname` LIKE :search
								OR `lastname` LIKE :search
								OR `email` LIKE :search)";
					$sql = $sql . $sqladd;

					$finalusersearchstring = '%' . $usersearchstring . '%';

					$s = $pdo->prepare($sql);
					$s->bindValue(":search", $finalusersearchstring);
					$s->bindValue(':CompanyID', $companyID);
					$s->execute();
					$result = $s->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$s = $pdo->prepare($sql);
					$s->bindValue(':CompanyID', $companyID);
					$s->execute();
					$result = $s->fetchAll(PDO::FETCH_ASSOC);
				}

				// Get the rows of information from the query
				// This will be used to create a dropdown list in HTML
				foreach($result as $row){
					$users[] = array(
										'UserID' => $row['UserID'],
										'UserIdentifier' => $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email']
									);
				}
				if(isSet($users)){
					$_SESSION['AddEmployeeAsOwnerUsersArray'] = $users;
					$usersFound = sizeOf($users);
				} else {
					$_SESSION['AddEmployeeAsOwnerUsersArray'] = array();
					$usersFound = 0;
				}
				if(isSet($_SESSION['AddEmployeeAsOwnerShowSearchResults']) AND $_SESSION['AddEmployeeAsOwnerShowSearchResults']){
					$_SESSION['AddEmployeeAsOwnerSearchResult'] = "The search result found $usersFound users";
				}

			} else {
				$users = $_SESSION['AddEmployeeAsOwnerUsersArray'];
			}

			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching user, company and company position lists: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

	} else {
		$companyposition = $_SESSION['AddEmployeeAsOwnerCompanyPositionArray'];
		$users = $_SESSION['AddEmployeeAsOwnerUsersArray'];
	}

	if(isSet($_SESSION['AddEmployeeAsOwnerSearchResult'])){
		$_SESSION['AddEmployeeAsOwnerSearchResult'] .= ".";
	}
	unset($_SESSION['AddEmployeeAsOwnerShowSearchResults']);

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the actual html form template
	include 'addemployee.html.php';
	exit();
}

// When admin has added the needed information and wants to add an employee connection
if (isSet($_POST['action']) AND $_POST['action'] == 'Confirm Employee')
{
	
	// Get company info
	$companyID = $_SESSION['normalUserCompanyIDSelected'];
	$companyName = $_SESSION['normalUserCompanyNameSelected'];

	$invalidInput = FALSE;
	$createUser = FALSE;

	if(!isSet($_SESSION['AddEmployeeAsOwnerAutoFillInEmail'])){

		if(isSet($_POST['UserID']) AND !empty($_POST['UserID'])){
			$userID = $_POST['UserID'];
		} elseif(isset($_POST['registerThenAddUserFromEmail']) AND !empty($_POST['registerThenAddUserFromEmail'])){
			$email = $_POST['registerThenAddUserFromEmail'];
			if(validateUserEmail($email) === FALSE){
				$_SESSION['AddEmployeeAsOwnerError'] = "The email you submitted is not a valid email";
				$invalidInput = TRUE;
			}
			if(strlen($email) < 3 AND !$invalidInput){
				$_SESSION[AddEmployeeAsOwnerError] = "You need to submit an actual email.";
				$invalidInput = TRUE;
			}
			if(!databaseContainsEmail($email) AND !$invalidInput){
				$createUser = TRUE;
			} else {
				$invalidInput = TRUE;
				$_SESSION['AddEmployeeAsOwnerError'] = "The email you submitted belongs to an already existing user.";
			}
		} else {
			$invalidInput = TRUE;
			$_SESSION['AddEmployeeAsOwnerError'] = "You have not selected an existing user or inserted an email to create a new one.";
		}

		// Make sure we only do this if user filled out all values
		if($invalidInput){
			$_SESSION['refreshAddEmployeeAsOwner'] = TRUE;
			$userSearchString = $_POST['usersearchstring'];
			$_SESSION['AddEmployeeAsOwnerUserSearch'] = trimExcessWhitespace($userSearchString);

			$TheCompanyID = $_SESSION['normalUserCompanyIDSelected'];
			$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
			header("Location: $location");
			exit();
		}
	} else {
		if(isSet($_POST['UserID']) AND !empty($_POST['UserID'])){
			$userID = $_POST['UserID'];
		} else {
			$invalidInput = TRUE;
		}
		if($_POST['email'] AND $_POST['email'] != ""){
			$email = $_POST['email'];
		} else {
			$invalidInput = TRUE;
		}

		if($invalidInput){
			clearAddEmployeeAsOwnerSessions();

			$_SESSION['normalCompanyFeedback'] = "Failed to add employee.";

			$location = "http://$_SERVER[HTTP_HOST]/company/";
			header("Location: $location");
			exit();
		}
	}

	// Check if the employee connection already exists for the user and company.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// If we're creating a new user, we give it the same access level as the company owner has. (Normal or in-house).
		if($createUser){

			//Generate activation code
			$activationcode = generateActivationCode();

			// Hash the user generated password
			$generatedPassword = generateUserPassword(6);
			$hashedPassword = hashPassword($generatedPassword);

			$firstName = "";
			$lastName = "";

			$sql = 'INSERT INTO `user`(`firstname`, `lastname`, `password`, `activationcode`, `email`, `accessID`)
					SELECT		:firstname,
								:lastname,
								:password,
								:activationcode,
								:email,
								IF(
									(a.`AccessName` = "Normal User"),
									(a.`AccessID`),
									(
										SELECT 	`AccessID`
										FROM	`accesslevel`
										WHERE	`AccessName` = "In-House User"
									)
								) AS AccessID
					FROM 		`accesslevel` a
					INNER JOIN	`user` u
					ON			u.`AccessID` = a.`AccessID`
					WHERE		u.`UserID` = :loggedInUser';
			$s = $pdo->prepare($sql);
			$s->bindValue(':firstname', $firstName);
			$s->bindValue(':lastname', $lastName);
			$s->bindValue(':password', $hashedPassword);
			$s->bindValue(':activationcode', $activationcode);
			$s->bindValue(':email', $email);
			$s->bindValue(':loggedInUser', $_SESSION['LoggedInUserID']);
			$s->execute();

			$userID = $pdo->lastInsertId();

			// Send user an email with the activation code and password
				// TO-DO: This is UNTESTED since we don't have php.ini set up to actually send email
			$emailSubject = "Account Activation Link - FLOW";

			$emailMessage = 
			"An account has been registered for you at " . $_SERVER['HTTP_HOST'] . ", by the user: " . $_SESSION['LoggedInUserName'] . 
			"\nYour generated password is: " . $generatedPassword . " (This can and should be changed after you log in)" .
			"\nBefore you can log in you need to activate your account.\n" .
			"If the account isn't activated within 8 hours, it is removed.\n" .
			"Click this link to activate your account: " . $_SERVER['HTTP_HOST'] . 
			"/user/?activateaccount=" . $activationcode;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			if(!$mailResult){
				$_SESSION['EmployeeUserFeedback'] .= "\n[WARNING] System failed to send Email to user.";
			}

			$_SESSION['EmployeeUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage.\nSent to: $email."; // TO-DO: Remove after testing	
		} else {
			$sql = 'SELECT 	COUNT(*) 
					FROM 	`employee`
					WHERE 	`CompanyID`= :CompanyID
					AND 	`UserID` = :UserID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $companyID);
			$s->bindValue(':UserID', $userID);
			$s->execute();

			$row = $s->fetch();

			if ($row[0] > 0)
			{
				if(!isSet($_SESSION['AddEmployeeAsOwnerAutoFillInEmail'])){
					// This user and company combination already exists in our database
					// This means the user is already an employee in the company!
					$_SESSION['AddEmployeeAsOwnerError'] = "The selected user is already an employee in your company!";
					$_SESSION['AddEmployeeAsOwnerSelectedUserID'] = $userID;
					$_SESSION['AddEmployeeAsOwnerSelectedPositionID'] = $_POST['PositionID'];
					$_SESSION['refreshAddEmployeeAsOwner'] = TRUE;
					$_SESSION['AddEmployeeAsOwnerUserSearch'] = trimExcessWhitespace($_POST['usersearchstring']);

					$TheCompanyID = $_SESSION['normalUserCompanyIDSelected'];
					$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
					header("Location: $location");
					exit();
				} else {
					clearAddEmployeeAsOwnerSessions();

					$_SESSION['normalCompanyFeedback'] = "The user is already an employee in your company.";

					$location = "http://$_SERVER[HTTP_HOST]/company/";
					header("Location: $location");
					exit();
				}
			}
		}
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for employee connection.' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Add the new employee connection to the database
	try
	{
		$sql = 'INSERT INTO `employee` 
				SET			`companyID` = :CompanyID,
							`userID` = :UserID,
							`PositionID` = :PositionID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $companyID);
		$s->bindValue(':UserID', $userID);
		$s->bindValue(':PositionID', $_POST['PositionID']);		
		$s->execute();
	}
	catch (PDOException $e)
	{
		$error = 'Error creating employee connection in database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	if($createUser){
		$_SESSION['EmployeeUserFeedback'] .= "\nSuccessfully created the user and added it as an employee.";
	} else {
		$_SESSION['EmployeeUserFeedback'] .= "\nSuccessfully added the employee.";
	}

	if($createUser){
		// Add a log event that a user was created by the company owner
		try
		{
			// Save a description with information about the employee that was added
			// to the company.
			$logEventDescription = 'An account was created to add as an employee. The user created is based on the email: ' . $email . 
			".\nThe company owner who added this account is: " . $_SESSION['LoggedInUserName'];

			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Account Created'
												),
								`description` = :description";
			$s = $pdo->prepare($sql);
			$s->bindValue(':description', $logEventDescription);
			$s->execute();

		}
		catch(PDOException $e)
		{
			$error = 'Error adding log event to database: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Add a log event that a user was added as an employee in a company
	try
	{
		$userinfo = 'N/A';
		$positioninfo = 'N/A';

		// Get selected user info
		if($createUser){
			$userinfo = "N/A - " . $email;
		} else {
			if(isSet($_SESSION['AddEmployeeAsOwnerUsersArray'])){
				foreach($_SESSION['AddEmployeeAsOwnerUsersArray'] AS $row){
					if($row['UserID'] == $userID){
						$userinfo = $row['UserIdentifier'];
						break;
					}
				}
				unset($_SESSION['AddEmployeeAsOwnerUsersArray']);
			}
		}

		// Get selected position name
		if(isSet($_SESSION['AddEmployeeAsOwnerCompanyPositionArray'])){
			foreach($_SESSION['AddEmployeeAsOwnerCompanyPositionArray'] AS $row){
				if($row['PositionID'] == $_POST['PositionID']){
					$positioninfo = $row['CompanyPositionName'];
					break;
				}
			}
			unset($_SESSION['AddEmployeeAsOwnerCompanyPositionArray']);
		}

		// Save a description with information about the employee that was added
		// to the company.
		$logEventDescription = 'The user: ' . $userinfo . 
		' was added to the company: ' . $companyName . 
		' and was given the position: ' . $positioninfo . ".\nAdded by : " .
		$_SESSION['LoggedInUserName'];

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Employee Added'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
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

	clearAddEmployeeAsOwnerSessions();

	// Load employee list webpage with new employee connection
	$TheCompanyID = $_SESSION['normalUserCompanyIDSelected'];
	$location = "http://$_SERVER[HTTP_HOST]/company/?ID=" . $TheCompanyID . "&employees";
	header("Location: $location");
	exit();
}

// If a company owner has used an "add employee"-link sent to their email, after a user requested it.
if(isSet($_SESSION['AddEmployeeAsOwnerAutoFillInEmail'])){
	if(empty($_SESSION['AddEmployeeAsOwnerAutoFillInEmail'])){
		clearAddEmployeeAsOwnerSessions();

		$_SESSION['normalCompanyFeedback'] = "Failed to add employee due to a blank email being submitted.";

		$location = "http://$_SERVER[HTTP_HOST]/company/";
		header("Location: $location");
		exit();
	}

	$userEmail = $_SESSION['AddEmployeeAsOwnerAutoFillInEmail'];
	$companyID = $_SESSION['normalUserCompanyIDSelected'];

	try
	{
		// Get all users and companypositions so owner can search/choose from them
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

			// Company Positions
		//Only get info if we haven't gotten it before
		if(!isSet($_SESSION['AddEmployeeAsOwnerCompanyPositionArray'])){
			// We don't have info about company position saved. Let's get it

			$sql = 'SELECT 	`PositionID`,
							`name` 			AS CompanyPositionName,
							`description`	AS CompanyPositionDescription
					FROM 	`companyposition`';
			$result = $pdo->query($sql);

			// Get the rows of information from the query
			// This will be used to create a dropdown list in HTML
			foreach($result as $row){
				$companyposition[] = array(
										'PositionID' => $row['PositionID'],
										'CompanyPositionName' => $row['CompanyPositionName'],
										'CompanyPositionDescription' => $row['CompanyPositionDescription']
										);
			}

			$_SESSION['AddEmployeeAsOwnerCompanyPositionArray'] = $companyposition;
		} else {
			$companyposition = $_SESSION['AddEmployeeAsOwnerCompanyPositionArray'];
		}

		if(!isSet($_SESSION['AddEmployeeAsOwnerUsersArray'])){
			// We don't have info about users saved. Let's get it

			$sql = "SELECT 	`userID` 	AS UserID,
							`firstname`,
							`lastname`,
							`email`,
							(
								SELECT	`name`
								FROM	`company`
								WHERE	`CompanyID` = :CompanyID
							)			AS CompanyName
					FROM 	`user`
					WHERE	`isActive` > 0
					AND		`email` = :email
					LIMIT 	1";
			$s = $pdo->prepare($sql);
			$s->bindValue(':email', $userEmail);
			$s->bindValue(':CompanyID', $companyID);
			$s->execute();
			$row = $s->fetch(PDO::FETCH_ASSOC);

			$users = array(
								'UserID' => $row['UserID'],
								'UserIdentifier' => $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'],
								'UserName' => $row['lastname'] . ', ' . $row['firstname'],
								'UserEmail' => $row['email'],
								'CompanyName' => $row['CompanyName']
							);

			if(isSet($users)){
				$_SESSION['AddEmployeeAsOwnerUsersArray'] = $users;
			} else {
				clearAddEmployeeAsOwnerSessions();

				$_SESSION['normalCompanyFeedback'] = "Failed to add employee due to the user not being found.";

				$location = "http://$_SERVER[HTTP_HOST]/company/";
				header("Location: $location");
				exit();
			}
		} else {
			$users = $_SESSION['AddEmployeeAsOwnerUsersArray'];
		}

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching user, company and company position information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$userID = $users['UserID'];
	$userName = $users['UserName'];
	$userEmail = $users['UserEmail'];
	$companyName = $users['CompanyName'];
	if(!isSet($_SESSION['normalUserCompanyNameSelected'])){
		$_SESSION['normalUserCompanyNameSelected'] = $companyName;
	}

	var_dump($_SESSION); // TO-DO: Remove before uploading

	include_once 'autofillinemployee.html.php';
	exit();
}

clearAddEmployeeAsOwnerSessions();

// Get employee information for the selected company when user wants it
if(isSet($_GET['employees']) AND isSet($_SESSION['normalUserCompanyIDSelected']) AND !empty($_SESSION['normalUserCompanyIDSelected'])){

	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		// First check if the user making the call is actually in the company. If not, we won't display anything.
		// Also doubles as the company role check to decide what should be displayed.
		$sql = "SELECT 		COUNT(*) 	AS HitCount,
							cp.`name` 	AS CompanyPosition,
							c.`name`	AS CompanyName
				FROM 		`employee` e
				INNER JOIN 	`companyposition` cp
				ON 			cp.`PositionID` = e.`PositionID`
				INNER JOIN 	`company` c
				ON			c.`CompanyID` = e.`CompanyID`
				WHERE		e.`CompanyID` = :CompanyID
				AND 		e.`UserID` = :UserID
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(":CompanyID", $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(":UserID", $_SESSION['LoggedInUserID']);
		$s->execute();

		$userResult = $s->fetch(PDO::FETCH_ASSOC);

		if(isSet($userResult) AND $userResult['HitCount'] > 0){
			$companyRole = $userResult['CompanyPosition'];
			$_SESSION['normalUserCompanyNameSelected'] = $userResult['CompanyName'];
		} else {
			$noAccess = TRUE;

			var_dump($_SESSION); // TO-DO: remove after testing is done	

			include_once 'company.html.php';
			exit();
		}

		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						cp.`name`					AS PositionName, 
						e.`startDateTime`			AS StartDateTime,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
							AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
						) 							AS PreviousMonthBookingTimeUsed,						
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
							AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
						) 							AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							)))	AS BookingTimeUsed
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
						) 							AS TotalBookingTimeUsed							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID 
				WHERE 	c.`companyID` = :id";
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s				
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();

		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}

		// Start a second SQL query to collect the booked time by removed users
		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
							AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
						)														AS PreviousMonthBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
							AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
						)														AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByRemovedEmployees
							FROM 		`booking` b
							INNER JOIN 	`employee` e
							ON 			e.`companyID` = b.`companyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
							AND 		b.`userID` = UsrID
						)														AS TotalBookingTimeUsed
				FROM 		`company` c
				JOIN 		`booking` b
				ON 			c.`companyID` = b.`companyID`
				JOIN 		`user` u 
				ON 			u.userID = b.UserID 
				WHERE 		c.`companyID` = :id
				AND 		b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :id)
				GROUP BY 	UsrID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();

		$removedEmployeesResult = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($removedEmployeesResult)){
			$removedEmployeesResultRowNum = sizeOf($removedEmployeesResult);
		} else {
			$removedEmployeesResultRowNum = 0;
		}

		// SQL Query to get booked time for deleted users
		$sql = "SELECT 	`companyID`				AS TheCompanyID,
						`name`					AS CompanyName,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
							FROM 		`booking` b
							INNER JOIN 	`company` c
							ON 			b.`CompanyID` = c.`CompanyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NULL
							AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
						)														AS PreviousMonthBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
							FROM 		`booking` b
							INNER JOIN 	`company` c
							ON 			b.`CompanyID` = c.`CompanyID`
							WHERE 		b.`companyID` = :id
							AND 		b.`userID` IS NULL
							AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
							AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
						)														AS MonthlyBookingTimeUsed,
						(
							SELECT (BIG_SEC_TO_TIME(SUM(
													IF(
														(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :aboveThisManySecondsToCount,
														IF(
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														) > :minimumSecondsPerBooking, 
															(
															(
																DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																)*86400 
															+ 
															(
																TIME_TO_SEC(b.`actualEndDateTime`) 
																- 
																TIME_TO_SEC(b.`startDateTime`)
																) 
														), 
															:minimumSecondsPerBooking
														),
														0
													)
							))) AS TotalBookingTimeByDeletedUsers
						FROM 	`booking` b
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NULL
						)														AS TotalBookingTimeUsed
				FROM 	`company`
				WHERE	`companyID` = :id";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_SESSION['normalUserCompanyIDSelected']);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();

		$deletedUsersResult = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($deletedUsersResult)){
			$deletedUsersResultRowNum = sizeOf($deletedUsersResult);
		} else {
			$deletedUsersResultRowNum = 0;
		}

		//close connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error getting employee information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	// If we're looking at a specific company and they have removed employees with booking time
	if($removedEmployeesResultRowNum > 0){
		foreach($removedEmployeesResult AS $row){	

			// Calculate and display company booking time details
			if($row['PreviousMonthBookingTimeUsed'] == null){
				$PrevMonthTimeUsed = 'N/A';
			} else {
				$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
				$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
				$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
				$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
			}	

			if($row['MonthlyBookingTimeUsed'] == null){
				$MonthlyTimeUsed = 'N/A';
			} else {
				$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
				$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
				$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
				$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';	
			
			}

			if($row['TotalBookingTimeUsed'] == null){
				$TotalTimeUsed = 'N/A';
			} else {
				$TotalTimeUsed = $row['TotalBookingTimeUsed'];
				$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
				$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
				$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
			}

			$removedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'firstName' => $row['firstName'],
										'lastName' => $row['lastName'],
										'email' => $row['email'],
										'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,											
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}

		if($removedEmployees[0]['TotalBookingTimeUsed'] == "N/A"){
			// The company has no used booking time by removed users
			unset($removedEmployees);
		}
	}

	// If we're looking at a specific company and they have old booking time used by now deleted users
	if($deletedUsersResultRowNum > 0){
		foreach($deletedUsersResult AS $row){	

			// Calculate and display company booking time details
			if($row['PreviousMonthBookingTimeUsed'] == null){
				$PrevMonthTimeUsed = 'N/A';
			} else {
				$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
				$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
				$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
				$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
			}

			if($row['MonthlyBookingTimeUsed'] == null){
				$MonthlyTimeUsed = 'N/A';
			} else {
				$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
				$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
				$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
				$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';
			}

			if($row['TotalBookingTimeUsed'] == null){
				$TotalTimeUsed = 'N/A';
			} else {
				$TotalTimeUsed = $row['TotalBookingTimeUsed'];
				$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
				$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
				$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
			}

			$deletedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}

		if($deletedEmployees[0]['TotalBookingTimeUsed'] == "N/A"){
			// The company has no used booking time by deleted users
			unset($deletedEmployees);
		}
	}

	// Create an array with the actual key/value pairs we want to use in our HTML	
	foreach($result AS $row){

		// Calculate and display company booking time details
		if($row['PreviousMonthBookingTimeUsed'] == null){
			$PrevMonthTimeUsed = 'N/A';
		} else {
			$PrevMonthTimeUsed = $row['PreviousMonthBookingTimeUsed'];
			$prevMonthTimeHour = substr($PrevMonthTimeUsed,0,strpos($PrevMonthTimeUsed,":"));
			$prevMonthTimeMinute = substr($PrevMonthTimeUsed,strpos($PrevMonthTimeUsed,":")+1, 2);
			$PrevMonthTimeUsed = $prevMonthTimeHour . 'h' . $prevMonthTimeMinute . 'm';
		}

		if($row['MonthlyBookingTimeUsed'] == null){
			$MonthlyTimeUsed = 'N/A';
		} else {
			$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
			$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,":"));
			$monthylTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,":")+1, 2);
			$MonthlyTimeUsed = $monthlyTimeHour . 'h' . $monthylTimeMinute . 'm';
		}

		if($row['TotalBookingTimeUsed'] == null){
			$TotalTimeUsed = 'N/A';
		} else {
			$TotalTimeUsed = $row['TotalBookingTimeUsed'];
			$totalTimeHour = substr($TotalTimeUsed,0,strpos($TotalTimeUsed,":"));
			$totalTimeMinute = substr($TotalTimeUsed,strpos($TotalTimeUsed,":")+1, 2);
			$TotalTimeUsed = $totalTimeHour . 'h' . $totalTimeMinute . 'm';			
		}

		$startDateTime = $row['StartDateTime'];
		$displayStartDateTime = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		// Create an array with the actual key/value pairs we want to use in our HTML
		$employees[] = array(
							'CompanyID' => $row['TheCompanyID'], 
							'UsrID' => $row['UsrID'],
							'CompanyName' => $row['CompanyName'],
							'PositionName' => $row['PositionName'],
							'firstName' => $row['firstName'],
							'lastName' => $row['lastName'],
							'email' => $row['email'],
							'PreviousMonthBookingTimeUsed' => $PrevMonthTimeUsed,
							'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
							'TotalBookingTimeUsed' => $TotalTimeUsed,
							'StartDateTime' => $displayStartDateTime
							);
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	include_once 'employees.html.php';
	exit();
}

// Get list of companies the user works for
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		c.`CompanyID`	AS CompanyID,
						c.`name`		AS CompanyName
			FROM		`company` c
			INNER JOIN 	`employee` e
			ON 			e.`CompanyID` = c.`CompanyID`
			INNER JOIN	`user` u
			ON			u.`UserID` = e.`UserID`
			AND			u.`UserID` = :UserID";
	$s = $pdo->prepare($sql);
	$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
	$s->execute();
	$companiesUserWorksFor = $s->fetchAll(PDO::FETCH_ASSOC);
	if(isSet($companiesUserWorksFor)){
		$numberOfCompanies = sizeOf($companiesUserWorksFor);
	} else {
		$numberOfCompanies = 0;
	}
}
catch (PDOException $e)
{
	$error = 'Error fetching list of companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

if(!isset($_SESSION['normalUserCompanyIDSelected']) AND isSet($numberOfCompanies) AND $numberOfCompanies == 1){
	$_SESSION['normalUserCompanyIDSelected'] = $companiesUserWorksFor[0]['CompanyID'];
	$_SESSION['normalUserSettingCompanyID'] = TRUE;
	header("Location: .");
	exit();
} else {
	unset($_SESSION['normalUserSettingCompanyID']);
}

// First check if the company selected is one of the companies the user actually works for
if(isSet($selectedCompanyToDisplayID) OR (isSet($selectedCompanyToDisplayID) AND empty($selectedCompanyToDisplayID))){

	$companyHit = FALSE;
	foreach($companiesUserWorksFor AS $cmp){
		if($selectedCompanyToDisplayID == $cmp['CompanyID']){
			$companyHit = TRUE;
			break;
		}
	}

	if($companyHit === FALSE){
		$noAccess = TRUE;
		$pdo = null;
		var_dump($_SESSION); // TO-DO: remove after testing is done	

		include_once 'company.html.php';
		exit();
	}
}

// Get a list of all companies that the user does not already work in
try
{
	$sql = "SELECT 	c.`CompanyID`	AS CompanyID,
					c.`name`		AS CompanyName
			FROM	`company` c
			WHERE	`companyID`
			NOT IN	(
						SELECT 	`companyID`
						FROM	`employee`
						WHERE	`userID` = :userID
					)";
	$s = $pdo->prepare($sql);
	$s->bindValue(":userID", $_SESSION['LoggedInUserID']);
	$s->execute();

	$companies = $s->fetchAll(PDO::FETCH_ASSOC);
}
catch (PDOException $e)
{
	$error = 'Error fetching list of companies from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// if user wants to see the details of the company booking history
if(isSet($_GET['totalBooking']) OR isSet($_GET['activeBooking']) OR isSet($_GET['completedBooking']) OR isSet($_GET['cancelledBooking'])){

	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 		b.`userID`										AS BookedUserID,
							b.`bookingID`,
							(
								IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
							)        										AS BookedRoomName,
							b.`startDateTime`								AS StartTime,
							b.`endDateTime`									AS EndTime, 
							b.`displayName` 								AS BookedBy,
							(
								IF(b.`companyID` IS NULL, NULL, (SELECT `name` FROM `company` WHERE `companyID` = b.`companyID`))
							)        										AS BookedForCompany,	 
							b.`description`									AS BookingDescription,
							b.`dateTimeCreated`								AS BookingWasCreatedOn, 
							b.`actualEndDateTime`							AS BookingWasCompletedOn, 
							b.`dateTimeCancelled`							AS BookingWasCancelledOn,
							b.`cancelMessage`								AS BookingCancelMessage,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserFirstName,
							(
								IF(b.`cancelledByUserID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`cancelledByUserID`))
							)        										AS CancelledByUserLastName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS firstName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS lastName,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS email,
							(
								IF(b.`userID` IS NULL, NULL, (SELECT `sendEmail` FROM `user` WHERE `userID` = b.`userID`))
							) 												AS sendEmail,
							(
								IF(b.`userID` IS NULL, NULL, 
									(
										SELECT 		cp.`name` 
										FROM 		`companyposition` cp
										INNER JOIN 	`employee` e
										ON			cp.`PositionID` = e.`PositionID`
										WHERE 		e.`userID` = b.`userID`
										AND			e.`CompanyID`= :CompanyID
									)
								)
							) 												AS CompanyRole
				FROM 		`booking` b
				WHERE		b.`CompanyID` = :CompanyID
				ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
				DESC';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $selectedCompanyToDisplayID);
		$s->execute();

		$result = $s->fetchAll(PDO::FETCH_ASSOC);

		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error getting booking history: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	foreach($result as $row)
	{
		$datetimeNow = getDatetimeNow();
		$startDateTime = $row['StartTime'];	
		$endDateTime = $row['EndTime'];
		$completedDateTime = $row['BookingWasCompletedOn'];
		$dateOnlyNow = convertDatetimeToFormat($datetimeNow, 'Y-m-d H:i:s', 'Y-m-d');
		$dateOnlyCompleted = convertDatetimeToFormat($completedDateTime,'Y-m-d H:i:s','Y-m-d');
		$dateOnlyStart = convertDatetimeToFormat($startDateTime,'Y-m-d H:i:s','Y-m-d');
		$cancelledDateTime = $row['BookingWasCancelledOn'];
		$dateOnlyCancelled = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', 'Y-m-d');
		$createdDateTime = $row['BookingWasCreatedOn'];	

		// Describe the status of the booking based on what info is stored in the database
		// If not finished and not cancelled = active
		// If meeting time has passed and finished time has updated (and not been cancelled) = completed
		// If cancelled = cancelled
		// If meeting time has passed and finished time has NOT updated (and not been cancelled) = Ended without updating
		// If none of the above = Unknown
		if(			$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow < $endDateTime AND $dateOnlyNow != $dateOnlyStart) {
			$status = 'Active';
			// Valid status
		} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow < $endDateTime AND $dateOnlyNow == $dateOnlyStart){
			$status = 'Active Today';
			// Valid status		
		} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
					$dateOnlyNow != $dateOnlyCompleted){
			$status = 'Completed';
			// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime == null AND 
					$dateOnlyNow == $dateOnlyCompleted){
			$status = 'Completed Today';
			// Valid status
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$startDateTime > $cancelledDateTime){
			$status = 'Cancelled';
				// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled == $dateOnlyNow){
			$status = 'Ended Early Today';
			// Valid status?
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime AND 
					$dateOnlyCancelled == $dateOnlyNow){
			$status = 'Ended Early Today';
			// Valid status
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime >= $cancelledDateTime AND $dateOnlyCancelled < $dateOnlyNow){
			$status = 'Ended Early';
			// Valid status?
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND
					$endDateTime > $cancelledDateTime AND $startDateTime < $cancelledDateTime AND 
					$dateOnlyCancelled < $dateOnlyNow){
			$status = 'Ended Early';
			// Valid status?
		} elseif(	$completedDateTime != null AND $cancelledDateTime != null AND
					$completedDateTime < $cancelledDateTime ){
			$status = 'Cancelled after Completion';
			// This should not be allowed to happen
		} elseif(	$completedDateTime == null AND $cancelledDateTime == null AND 
					$datetimeNow > $endDateTime){
			$status = 'Ended without updating database';
			// This should only occur when the cron does not check and update every minute
		} elseif(	$completedDateTime == null AND $cancelledDateTime != null AND 
					$endDateTime < $cancelledDateTime){
			$status = 'Cancelled after meeting should have been Completed';
			// This should not be allowed to happen
		} else {
			$status = 'Unknown';
			// This should never occur
		}

		$roomName = $row['BookedRoomName'];
		$displayRoomNameForTitle = $roomName;
		$firstname = $row['firstName'];
		$lastname = $row['lastName'];
		$email = $row['email'];
		$userinfo = $lastname . ', ' . $firstname . ' - ' . $row['email'];
		$companyRole = $row['CompanyRole'];

		if(!isSet($roomName) OR empty($roomName)){
			$roomName = "N/A - Deleted";
		}
		if(!isSet($userinfo) OR $userinfo == NULL OR $userinfo == ",  - "){
			$userinfo = "N/A - Deleted";
		}
		if(!isSet($email) OR empty($email)){
			$firstname = "N/A - Deleted";
			$lastname = "N/A - Deleted";
			$email = "N/A - Deleted";
		}
		if(!isSet($companyRole) OR empty($companyRole)){
			$companyRole = "Removed";
		}

		$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayCompletedDateTime = convertDatetimeToFormat($completedDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayCancelledDateTime = convertDatetimeToFormat($cancelledDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);	
		$displayCreatedDateTime = convertDatetimeToFormat($createdDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		$meetinginfo = $roomName . ' for the timeslot: ' . $displayValidatedStartDate . 
						' to ' . $displayValidatedEndDate;

		$completedMeetingDurationInMinutes = convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $completedDateTime);
		$displayCompletedMeetingDuration = convertMinutesToHoursAndMinutes($completedMeetingDurationInMinutes);
		if($completedMeetingDurationInMinutes < BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS){
			$completedMeetingDurationForPrice = 0;
		} elseif($completedMeetingDurationInMinutes < MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS){
			$completedMeetingDurationForPrice = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS;
		} else {
			$completedMeetingDurationForPrice = $completedMeetingDurationInMinutes;
		}
		$displayCompletedMeetingDurationForPrice = convertMinutesToHoursAndMinutes($completedMeetingDurationForPrice);

		$cancelMessage = $row['BookingCancelMessage'];
		if($cancelMessage == NULL){
			$cancelMessage = "";
		}
		if($row['CancelledByUserLastName'] == NULL AND $row['CancelledByUserFirstName'] == NULL){
			$cancelledByUserName = "N/A - Deleted";
		} else {
			$cancelledByUserName = $row['CancelledByUserLastName'] . ", " . $row['CancelledByUserFirstName'];
		}
		if($status == "Active Today" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))) {
			$bookingsActiveToday[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo,
											'sendEmail' => $row['sendEmail']
										);
		}	elseif(($status == "Completed Today" OR $status == "Ended Early Today") AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){
			if($status == "Completed Today"){
				$cancelledByUserName = "";
				$cancelMessage = "";
			}
			$bookingsCompletedToday[] = array(	'id' => $row['bookingID'],
												'BookingStatus' => $status,
												'BookedRoomName' => $roomName,
												'StartTime' => $displayValidatedStartDate,
												'EndTime' => $displayValidatedEndDate,
												'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
												'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
												'BookedBy' => $row['BookedBy'],
												'BookedForCompany' => $row['BookedForCompany'],
												'BookingDescription' => $row['BookingDescription'],
												'BookingWasCreatedOn' => $displayCreatedDateTime,
												'BookingWasCompletedOn' => $displayCompletedDateTime,
												'BookingWasCancelledOn' => $displayCancelledDateTime,
												'CancelMessage' => $cancelMessage,
												'CancelledByUserName' => $cancelledByUserName,
												'firstName' => $firstname,
												'lastName' => $lastname,
												'email' => $email,
												'CompanyRole' => $companyRole,
												'UserInfo' => $userinfo,
												'MeetingInfo' => $meetinginfo
											);
		}	elseif($status == "Active" AND (isSet($_GET['activeBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsFuture[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'CompanyRole' => $companyRole,
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo,
										'sendEmail' => $row['sendEmail']
									);
		}	elseif(($status == "Completed" OR $status == "Ended Early") AND (isSet($_GET['completedBooking']) OR isSet($_GET['totalBooking']))){
			if($status == "Completed"){
				$cancelledByUserName = "";
				$cancelMessage = "";
			}
			$bookingsCompleted[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'CompletedMeetingDuration' => $displayCompletedMeetingDuration,
											'CompletedMeetingDurationForPrice' => $displayCompletedMeetingDurationForPrice,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'CancelMessage' => $cancelMessage,
											'CancelledByUserName' => $cancelledByUserName,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo
										);
		}	elseif($status == "Cancelled" AND (isSet($_GET['cancelledBooking']) OR isSet($_GET['totalBooking']))){
			$bookingsCancelled[] = array(	'id' => $row['bookingID'],
											'BookingStatus' => $status,
											'BookedRoomName' => $roomName,
											'StartTime' => $displayValidatedStartDate,
											'EndTime' => $displayValidatedEndDate,
											'BookedBy' => $row['BookedBy'],
											'BookedForCompany' => $row['BookedForCompany'],
											'BookingDescription' => $row['BookingDescription'],
											'BookingWasCreatedOn' => $displayCreatedDateTime,
											'BookingWasCompletedOn' => $displayCompletedDateTime,
											'BookingWasCancelledOn' => $displayCancelledDateTime,
											'CancelMessage' => $cancelMessage,
											'CancelledByUserName' => $cancelledByUserName,
											'firstName' => $firstname,
											'lastName' => $lastname,
											'email' => $email,
											'CompanyRole' => $companyRole,
											'UserInfo' => $userinfo,
											'MeetingInfo' => $meetinginfo
										);		
		}	elseif(isSet($_GET['totalBooking'])){				
			$bookingsOther[] = array(	'id' => $row['bookingID'],
										'BookingStatus' => $status,
										'BookedRoomName' => $roomName,
										'StartTime' => $displayValidatedStartDate,
										'EndTime' => $displayValidatedEndDate,
										'BookedBy' => $row['BookedBy'],
										'BookedForCompany' => $row['BookedForCompany'],
										'BookingDescription' => $row['BookingDescription'],
										'BookingWasCreatedOn' => $displayCreatedDateTime,
										'BookingWasCompletedOn' => $displayCompletedDateTime,
										'BookingWasCancelledOn' => $displayCancelledDateTime,
										'CancelMessage' => $cancelMessage,
										'CancelledByUserName' => $cancelledByUserName,
										'firstName' => $firstname,
										'lastName' => $lastname,
										'email' => $email,
										'CompanyRole' => $companyRole,
										'UserInfo' => $userinfo,
										'MeetingInfo' => $meetinginfo
									);
		}
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Create the booking information table in HTML
	include_once 'bookings.html.php';
	exit();
} else {
	unset($_SESSION['normalCompanyBookingHistory']);
}

if(isSet($selectedCompanyToDisplayID) AND !empty($selectedCompanyToDisplayID)){

	// Get company information
	try
	{
		// Calculate booking time used for a company
		// Only takes into account time spent and company the booking was booked for.
			// Booking time is rounded for each booking, instead of summed up and then rounded.
			// We therefore get the minimum time per booking for our equations
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s

		$sql = "SELECT 		c.`companyID` 										AS CompanyID,
							c.`name` 											AS CompanyName,
							c.`dateTimeCreated`									AS DatetimeCreated,
							c.`removeAtDate`									AS DeletionDate,
							c.`isActive`										AS CompanyActivated,
							c.`startDate`										AS PeriodStartDate,
							c.`endDate`											AS PeriodEndDate,
							(
								SELECT 	COUNT(e.`CompanyID`)
								FROM 	`employee` e
								WHERE 	e.`companyID` = :CompanyID
							)													AS NumberOfEmployees,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = :CompanyID
								AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
								AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
							)   												AS PreviousMonthCompanyWideBookingTimeUsed,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b  
								INNER JOIN 	`company` c 
								ON 			b.`CompanyID` = c.`CompanyID` 
								WHERE 		b.`CompanyID` = :CompanyID
								AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
								AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
							)													AS MonthlyCompanyWideBookingTimeUsed,
							(
								SELECT (BIG_SEC_TO_TIME(SUM(
														IF(
															(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :aboveThisManySecondsToCount,
															IF(
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															) > :minimumSecondsPerBooking, 
																(
																(
																	DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																	)*86400 
																+ 
																(
																	TIME_TO_SEC(b.`actualEndDateTime`) 
																	- 
																	TIME_TO_SEC(b.`startDateTime`)
																	) 
															), 
																:minimumSecondsPerBooking
															),
															0
														)
								)))	AS BookingTimeUsed
								FROM 		`booking` b
								WHERE 		b.`CompanyID` = :CompanyID
							)													AS TotalCompanyWideBookingTimeUsed,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
							)													AS TotalBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NULL
								AND 	`endDateTime` > CURRENT_TIMESTAMP
							)													AS ActiveBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	(
											`actualEndDateTime` IS NOT NULL
										OR
											(
														`actualEndDateTime` IS NULL
												AND 	`dateTimeCancelled` IS NULL
												AND 	`endDateTime` <= CURRENT_TIMESTAMP
											)
										)
							)													AS CompletedBookedMeetings,
							(
								SELECT 	COUNT(*)
								FROM	`booking`
								WHERE	`companyID` = :CompanyID
								AND 	`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NOT NULL
							)													AS CancelledBookedMeetings,
							cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
							cc.`lastModified`									AS CompanyCreditsLastModified,
							cr.`name`											AS CreditSubscriptionName,
							cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
							cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
							cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice,
							(
								SELECT		cp.`name`
								FROM		`companyposition` cp
								INNER JOIN	`employee` e
								ON			e.`PositionID` = cp.`PositionID`
								WHERE		e.`CompanyID` = :CompanyID
								AND			e.`UserID` = :UserID
								LIMIT 		1
							)													AS CompanyRole
				FROM 		`company` c
				LEFT JOIN	`companycredits` cc
				ON			c.`CompanyID` = cc.`CompanyID`
				LEFT JOIN	`credits` cr
				ON			cr.`CreditsID` = cc.`CreditsID`
				WHERE		c.`CompanyID` = :CompanyID
				GROUP BY 	c.`CompanyID`
				LIMIT 		1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->bindValue(':CompanyID', $selectedCompanyToDisplayID);
		$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
		$s->execute();
		$row = $s->fetch(PDO::FETCH_ASSOC);

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching company information from the database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Calculate and display company booking time details
	if($row['PreviousMonthCompanyWideBookingTimeUsed'] == null){
		$PrevMonthTimeUsed = 'N/A';
	} else {
		$PrevMonthTimeUsed = convertTimeToHoursAndMinutes($row['PreviousMonthCompanyWideBookingTimeUsed']);
	}

	if($row['MonthlyCompanyWideBookingTimeUsed'] == null){
		$MonthlyTimeUsed = 'N/A';
	} else {
		$MonthlyTimeUsed = convertTimeToHoursAndMinutes($row['MonthlyCompanyWideBookingTimeUsed']);
	}

	if($row['TotalCompanyWideBookingTimeUsed'] == null){
		$TotalTimeUsed = 'N/A';
	} else {
		$TotalTimeUsed = convertTimeToHoursAndMinutes($row['TotalCompanyWideBookingTimeUsed']);	
	}

	// Calculate and display company booking subscription details
	if($row["CompanyAlternativeMinuteAmount"] != NULL AND $row["CompanyAlternativeMinuteAmount"] != ""){
		$companyMinuteCredits = $row["CompanyAlternativeMinuteAmount"];
	} elseif($row["CreditSubscriptionMinuteAmount"] != NULL AND $row["CreditSubscriptionMinuteAmount"] != "") {
		$companyMinuteCredits = $row["CreditSubscriptionMinuteAmount"];
	} else {
		$companyMinuteCredits = 0;
	}
		// Format company credits to be displayed
	$displayCompanyCredits = convertMinutesToHoursAndMinutes($companyMinuteCredits);

	$monthPrice = $row["CreditSubscriptionMonthlyPrice"];
	if($monthPrice == NULL OR $monthPrice == ""){
		$monthPrice = 0;
	}
	$hourPrice = $row["CreditSubscriptionHourPrice"];
	if($hourPrice == NULL OR $hourPrice == ""){
		$hourPrice = 0;
	}
	$overCreditsFee = convertToCurrency($hourPrice) . "/h";

		// Calculate Company Credits Remaining
	if($MonthlyTimeUsed != "N/A"){
		$monthlyTimeHour = substr($MonthlyTimeUsed,0,strpos($MonthlyTimeUsed,"h"));
		$monthlyTimeMinute = substr($MonthlyTimeUsed,strpos($MonthlyTimeUsed,"h")+1,-1);
		$actualTimeUsedInMinutesThisMonth = $monthlyTimeHour*60 + $monthlyTimeMinute;
		if($actualTimeUsedInMinutesThisMonth > $companyMinuteCredits){
			$minusCompanyMinuteCreditsRemaining = $actualTimeUsedInMinutesThisMonth - $companyMinuteCredits;
			$displayCompanyCreditsRemaining = "-" . convertMinutesToHoursAndMinutes($minusCompanyMinuteCreditsRemaining);
		} else {
			$companyMinuteCreditsRemaining = $companyMinuteCredits - $actualTimeUsedInMinutesThisMonth;
			$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
		}
	} else {
		$companyMinuteCreditsRemaining = $companyMinuteCredits;
		$displayCompanyCreditsRemaining = convertMinutesToHoursAndMinutes($companyMinuteCreditsRemaining);
	}

		// Display dates
	$dateCreated = $row['DatetimeCreated'];	
	$dateToRemove = $row['DeletionDate'];
	$periodStartDate = $row['PeriodStartDate'];
	$periodEndDate = $row['PeriodEndDate'];
	$isActive = ($row['CompanyActivated'] == 1);
	$dateTimeCreatedToDisplay = convertDatetimeToFormat($dateCreated, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateToRemoveToDisplay = convertDatetimeToFormat($dateToRemove, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$periodStartDateDisplay = convertDatetimeToFormat($periodStartDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$periodEndDateDisplay = convertDatetimeToFormat($periodEndDate, 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY);
	$periodInfo = $periodStartDateDisplay . " - " . $periodEndDateDisplay;

	$numberOfTotalBookedMeetings = $row['TotalBookedMeetings'];
	$numberOfActiveBookedMeetings = $row['ActiveBookedMeetings'];
	$numberOfCompletedBookedMeetings = $row['CompletedBookedMeetings'];
	$numberOfCancelledBookedMeetings = $row['CancelledBookedMeetings'];

	$companyInformation = array(
							'CompanyID' => $row['CompanyID'], 
							'CompanyName' => $row['CompanyName'],
							'NumberOfEmployees' => $row['NumberOfEmployees'],
							'PreviousMonthCompanyWideBookingTimeUsed' => $PrevMonthTimeUsed,
							'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
							'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
							'DeletionDate' => $dateToRemoveToDisplay,
							'DatetimeCreated' => $dateTimeCreatedToDisplay,
							'CompanyCredits' => $displayCompanyCredits,
							'CompanyCreditsRemaining' => $displayCompanyCreditsRemaining,
							'CreditSubscriptionMonthlyPrice' => convertToCurrency($monthPrice),
							'OverCreditsFee' => $overCreditsFee,
							'CompanyRole' => $row['CompanyRole'],
							'PeriodInfo' => $periodInfo
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done	

include_once 'company.html.php';
?>