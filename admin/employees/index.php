<?php 
// This is the index file for the EMPLOYEES folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add employee form
function clearAddEmployeeSessions(){
	unset($_SESSION['AddEmployeeCompanySearch']);
	unset($_SESSION['AddEmployeeUserSearch']);
	
	unset($_SESSION['AddEmployeeSelectedCompanyID']);
	unset($_SESSION['AddEmployeeSelectedUserID']);
	unset($_SESSION['AddEmployeeSelectedPositionID']);
	
	unset($_SESSION['AddEmployeeCompaniesArray']);
	unset($_SESSION['AddEmployeeCompanyPositionArray']);
	unset($_SESSION['AddEmployeeUsersArray']);	
}

// Function to clear sessions used to remember user inputs on refreshing the edit employee form
function clearEditEmployeeSessions(){
	unset($_SESSION['EditEmployeeOriginalPositionID']);
}

function clearTransferEmployeeSessions(){
	unset($_SESSION['TransferEmployeeSelectedCompanyID']);
	unset($_SESSION['TransferEmployeeSelectedCompanyName']);
	unset($_SESSION['TransferEmployeeSelectedCompanyID2']);
	unset($_SESSION['TransferEmployeeSelectedUserID']);
	unset($_SESSION['TransferEmployeeSelectedUserName']);
}

// If admin wants to be able to delete companies it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Remove"){
	$_SESSION['employeesEnableDelete'] = TRUE;
	$refreshEmployees = TRUE;
}

// If admin wants to be disable company deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Remove"){
	unset($_SESSION['employeesEnableDelete']);
	$refreshEmployees = TRUE;
}

// If admin wants to remove an employee from the selected company
if(isSet($_POST['action']) AND $_POST['action'] == 'Remove'){
	// Remove employee connection in database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `employee` 
				WHERE 		`companyID` = :CompanyID
				AND 		`userID` = :UserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error deleting employee connection: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	$_SESSION['EmployeeAdminFeedback'] = "Successfully removed the employee.";
	
	// Add a log event that an employee was removed from a company
	try
	{
		// Save a description with information about the employee that was removed
		// from the company.
		$logEventDescription = 'The user: ' . $_POST['UserName'] . 
		' was removed from the company: ' . $_POST['CompanyName'] . 
		".\nRemoved by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
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
	
	//	Go to the employee main page with the appropriate values
	if(isSet($_GET['Company'])){
		// Refresh employee for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	} else {
		// Do a normal page reload
		header('Location: .');
		exit();
	}
}
// Admin clicked the search button, trying to limit the shown company and user lists
if(isSet($_POST['action']) AND $_POST['action'] == 'Search'){
	// Forget the old array values we have saved
	unset($_SESSION['AddEmployeeCompaniesArray']);
	unset($_SESSION['AddEmployeeUsersArray']);
	
	$_SESSION['AddEmployeeShowSearchResults'] = TRUE;
	// Let's remember what was selected and searched for
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isSet($_GET['Company'])){	
		$_SESSION['AddEmployeeUserSearch'] = trimExcessWhitespace($_POST['usersearchstring']);
		$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
		$_SESSION['AddEmployeeSelectedPositionID'] = $_POST['PositionID'];
		$_SESSION['refreshAddEmployee'] = TRUE;
		
		// Refresh AddEmployee for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	} else {
		$companySearchString = $_POST['companysearchstring'];
		$userSearchString = $_POST['usersearchstring'];
		$_SESSION['AddEmployeeCompanySearch'] = trimExcessWhitespace($companySearchString);
		$_SESSION['AddEmployeeUserSearch'] = trimExcessWhitespace($userSearchString);
		$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
		$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
		$_SESSION['AddEmployeeSelectedPositionID'] = $_POST['PositionID'];
		
		// Also we want to refresh AddEmployee with our new values!
		$_SESSION['refreshAddEmployee'] = TRUE;
		header('Location: .');
		exit();
	}
}

// 	If admin wants to add an employee to a company in the database
// 	we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Add Employee') OR 
	(isSet($_SESSION['refreshAddEmployee']) AND $_SESSION['refreshAddEmployee']))
{	

	$companysearchstring = '';
	$usersearchstring = '';

	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshAddEmployee']) AND $_SESSION['refreshAddEmployee']){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddEmployee']);

		// Remember the company string that was searched before refreshing
		if(isSet($_SESSION['AddEmployeeCompanySearch'])){
			$companysearchstring = $_SESSION['AddEmployeeCompanySearch'];
			unset($_SESSION['AddEmployeeCompanySearch']);
		}

		// Remember the user string that was searched before refreshing
		if(isSet($_SESSION['AddEmployeeUserSearch'])){
			$usersearchstring = $_SESSION['AddEmployeeUserSearch'];
			unset($_SESSION['AddEmployeeUserSearch']);
		}

		// Remember what company was selected before refreshing
		if(isSet($_SESSION['AddEmployeeSelectedCompanyID'])){
			$selectedCompanyID = $_SESSION['AddEmployeeSelectedCompanyID'];
			unset($_SESSION['AddEmployeeSelectedCompanyID']);
		}

		// Remember what user was selected before refreshing
		if(isSet($_SESSION['AddEmployeeSelectedUserID'])){
			$selectedUserID = $_SESSION['AddEmployeeSelectedUserID'];
			unset($_SESSION['AddEmployeeSelectedUserID']);
		}

		// Remember what company position was selected before refreshing
		if(isSet($_SESSION['AddEmployeeSelectedPositionID'])){
			$selectedPositionID = $_SESSION['AddEmployeeSelectedPositionID'];
			unset($_SESSION['AddEmployeeSelectedPositionID']);
		}
	}

	// Get info about company position, users and companies from the database
	// if we don't already have them saved in a session array
	if(	!isSet($_SESSION['AddEmployeeCompaniesArray']) OR 
		!isSet($_SESSION['AddEmployeeUsersArray']) OR
		!isSet($_SESSION['AddEmployeeCompanyPositionArray'])){	
	
		try
		{
			// Get all users, companies and companypositions so admin can search/choose from them
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

				//Companies	
			// Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddEmployeeCompaniesArray'])){
				// We don't have info about companies saved. Let's get it

				if(!isSet($_GET['Company'])){
					// If we're NOT looking at a specific company already
					$sql = 'SELECT 	`companyID` AS CompanyID,
									`name`		AS CompanyName
							FROM 	`company`
							WHERE	`companyID` <> 0';

					if ($companysearchstring != ''){
						$sqladd = " AND `name` LIKE :search";
						$sql = $sql . $sqladd;	

						$finalcompanysearchstring = '%' . $companysearchstring . '%';

						$s = $pdo->prepare($sql);
						$s->bindValue(':search', $finalcompanysearchstring);
						$s->execute();
						$result = $s->fetchAll(PDO::FETCH_ASSOC);
					} else {
						$return = $pdo->query($sql);
						$result = $return->fetchAll(PDO::FETCH_ASSOC);
					}

					// Get the rows of information from the query
					// This will be used to create a dropdown list in HTML
					foreach($result as $row){
						$companies[] = array(
												'CompanyID' => $row['CompanyID'],
												'CompanyName' => $row['CompanyName']
												);
					}

					if(isSet($companies)){
						$_SESSION['AddEmployeeCompaniesArray'] = $companies;
						$companiesFound = sizeOf($companies);
					} else {
						$_SESSION['AddEmployeeCompaniesArray'] = array();
						$companiesFound = 0;
					}
					if(isSet($_SESSION['AddEmployeeShowSearchResults']) AND $_SESSION['AddEmployeeShowSearchResults']){
						$_SESSION['AddEmployeeSearchResult'] = "The search result found $companiesFound companies.";
					}

				} else {
					$sql = 'SELECT 	`companyID` AS CompanyID,
									`name`		AS CompanyName
							FROM 	`company`
							WHERE 	`companyID` = :CompanyID
							AND		`isActive` = 1
							LIMIT 	1';
					$s = $pdo->prepare($sql);
					$s->bindValue(':CompanyID', $_GET['Company']);
					$s->execute();
					$companies = $s->fetch(PDO::FETCH_ASSOC);
				}
			} else {
				$companies = $_SESSION['AddEmployeeCompaniesArray'];
			}

				// Company Positions
			//Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddEmployeeCompanyPositionArray'])){
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

				$_SESSION['AddEmployeeCompanyPositionArray'] = $companyposition;
			} else {
				$companyposition = $_SESSION['AddEmployeeCompanyPositionArray'];
			}

				//	Users - Only active ones
			// Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddEmployeeUsersArray'])){
				// We don't have info about users saved. Let's get it

				$sql = "SELECT 	`userID` 	AS UserID,
								`firstname`,
								`lastname`,
								`email`
						FROM 	`user`
						WHERE	`isActive` > 0";

				if ($usersearchstring != ''){
					$sqladd = " AND (`firstname` LIKE :search
								OR `lastname` LIKE :search
								OR `email` LIKE :search)";
					$sql = $sql . $sqladd;

					$finalusersearchstring = '%' . $usersearchstring . '%';

					$s = $pdo->prepare($sql);
					$s->bindValue(":search", $finalusersearchstring);
					$s->execute();
					$result = $s->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$return = $pdo->query($sql);
					$result = $return->fetchAll(PDO::FETCH_ASSOC);
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
					$_SESSION['AddEmployeeUsersArray'] = $users;
					$usersFound = sizeOf($users);
				} else {
					$_SESSION['AddEmployeeUsersArray'] = array();
					$usersFound = 0;
				}
				if(isSet($_SESSION['AddEmployeeShowSearchResults']) AND $_SESSION['AddEmployeeShowSearchResults']){
					if(isSet($_SESSION['AddEmployeeSearchResult'])){
					$_SESSION['AddEmployeeSearchResult'] .= " and $usersFound users";
					} else {
						$_SESSION['AddEmployeeSearchResult'] = "The search result found $usersFound users";
					}
				}

			} else {
				$users = $_SESSION['AddEmployeeUsersArray'];
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
		$companies = $_SESSION['AddEmployeeCompaniesArray'];
		$companyposition = $_SESSION['AddEmployeeCompanyPositionArray'];
		$users = $_SESSION['AddEmployeeUsersArray'];
	}


	if(isSet($_SESSION['AddEmployeeSearchResult'])){
		$_SESSION['AddEmployeeSearchResult'] .= ".";
	}
	unset($_SESSION['AddEmployeeShowSearchResults']);

	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual html form template
	include 'addemployee.html.php';
	exit();
}

// When admin has added the needed information and wants to add an employee connection
if (isSet($_POST['action']) AND $_POST['action'] == 'Confirm Employee')
{
	// If we're looking at a specific company
	if(isSet($_GET['Company'])){
		$CompanyID = $_GET['Company'];
	} else {
		$CompanyID = $_POST['CompanyID'];
	}	

	// Make sure we only do this if user filled out all values
	$a = ($_POST['UserID'] == '');
	$b = ($CompanyID == '');	

	if($a OR $b){
		// Some value wasn't filled out.
		// Set appropriate feedback message to admin
		if($a AND $b){
			$c = "Need to select a user and a company first!";
		}
		if($a AND !$b){
			$c = "Need to select a user!";
			$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
		}
		if(!$a AND $b){
			$c = "Need to select a company first!";
			$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
		}
		
		// We didn't have enough values filled in. "go back" to add employee
		$_SESSION['refreshAddEmployee'] = TRUE;
		$_SESSION['AddEmployeeError'] = $c;
		//TO-DO: Remove/Change the search variables if we don't want it to show up after a search
		$companySearchString = $_POST['companysearchstring'];
		$userSearchString = $_POST['usersearchstring'];
		$_SESSION['AddEmployeeCompanySearch'] = trimExcessWhitespace($companySearchString);
		$_SESSION['AddEmployeeUserSearch'] = trimExcessWhitespace($userSearchString);	
		
		if(isSet($_GET['Company'])){	
			// We were looking at a specific company. Let's go back to info about that company
			$TheCompanyID = $_GET['Company'];
			$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
			header("Location: $location");
			exit();
		} else {
			// We were not looking at a specific meeting room. Let's do a normal refresh.
			header('Location: .');			
			exit();
		}			
	}
			
	// Check if the employee connection already exists for the user and company.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`employee`
				WHERE 	`CompanyID`= :CompanyID
				AND 	`UserID` = :UserID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $CompanyID);
		$s->bindValue(':UserID', $_POST['UserID']);		
		$s->execute();
		
		$pdo = null;
		
		$row = $s->fetch();
		
		if ($row[0] > 0)
		{
			// This user and company combination already exists in our database
			// This means the user is already an employee in the company!
			$_SESSION['AddEmployeeError'] = "The selected user is already an employee in the selected company!";
			$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
			$_SESSION['AddEmployeeSelectedPositionID'] = $_POST['PositionID'];
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeUserSearch'] = trimExcessWhitespace($_POST['usersearchstring']);
			
			if(isSet($_GET['Company'])){	
				// Refresh AddEmployee for the specific company again
				$TheCompanyID = $_GET['Company'];
				$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
				header("Location: $location");
				exit();
			} else {
				$_SESSION['AddEmployeeCompanySearch'] = trimExcessWhitespace($_POST['companysearchstring']);
				$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
				
				// Also we want to refresh AddEmployee with our new values!
				$_SESSION['refreshAddEmployee'] = TRUE;
				header('Location: .');
				exit();
			}		
		}	
		// No employee connection found. Now we can create it.
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
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `employee` 
				SET			`companyID` = :CompanyID,
							`userID` = :UserID,
							`PositionID` = :PositionID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $CompanyID);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':PositionID', $_POST['PositionID']);		
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error creating employee connection in database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
		$_SESSION['EmployeeAdminFeedback'] = "Successfully added the employee.";
	
	// Add a log event that a user was added as an employee in a company
	try
	{
		/* The following code is to get the text information that the user selected.
		This can not be retrieved with a $_POST statement since the value is the IDs
		and not the text. So we go through the same arrays we used earlier, now saved
		in a session variable, to get the text again by matching it with the selected IDs.
		
		This could be done a lot simpler, with way less code, and no need to use session.
		This would require javascript to get the actual text from the selected element. Then
		we could save it as a hidden input and get the values with $_POST directly.
		PROS: Way less code, better overview and ... faster?
		CONS: Info isn't retrieved if javascript is disabled.
		
		Could also be done by doing a new SELECT QUERY for the information instead, since we 
		already have the IDs for all the info. This would look cleaner, but would be an
		unnecessary query since we already have access to all the information.
		*/
		$userinfo = 'N/A';
		$companyinfo = 'N/A';
		$positioninfo = 'N/A';
		
		// Get selected user info
		if(isSet($_SESSION['AddEmployeeUsersArray'])){
			foreach($_SESSION['AddEmployeeUsersArray'] AS $row){
				if($row['UserID'] == $_POST['UserID']){
					$userinfo = $row['UserIdentifier'];
					break;
				}
			}
			unset($_SESSION['AddEmployeeUsersArray']);
		}
		
		// Get selected company name
		if(isSet($_SESSION['AddEmployeeCompaniesArray'])){
			if($CompanyID == $_SESSION['AddEmployeeCompaniesArray'][0]){
				$companyinfo = $_SESSION['AddEmployeeCompaniesArray']['CompanyName'];
			} else {
				foreach($_SESSION['AddEmployeeCompaniesArray'] AS $row){
					if($row['CompanyID'] == $CompanyID){
						$companyinfo = $row['CompanyName'];
						break;
					}
				} 				
			}
			unset($_SESSION['AddEmployeeCompaniesArray']);
		}
		
		// Get selected position name
		if(isSet($_SESSION['AddEmployeeCompanyPositionArray'])){
			foreach($_SESSION['AddEmployeeCompanyPositionArray'] AS $row){
				if($row['PositionID'] == $_POST['PositionID']){
					$positioninfo = $row['CompanyPositionName'];
					break;
				}
			}
			unset($_SESSION['AddEmployeeCompanyPositionArray']);
		}		
	
		
		// Save a description with information about the employee that was added
		// to the company.
		$logEventDescription = 'The user: ' . $userinfo . 
		' was added to the company: ' . $companyinfo . 
		' and was given the position: ' . $positioninfo . ".\nAdded by : " .
		$_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
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
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isSet($_GET['Company'])){
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}
	
	// Load employee list webpage with new employee connection
	header('Location: .');
	exit();
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
		$sql = 'SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						cp.`PositionID`,
						cp.`name`					AS PositionName							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID
				WHERE	e.userID = :UserID
				AND 	e.companyID = :CompanyID
				LIMIT 	1';
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
	$_SESSION['EditEmployeeOriginalPositionID'] = $row['PositionID'];
	
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
	
	if(	isSet($_SESSION['EditEmployeeOriginalPositionID']) AND 
		$_SESSION['EditEmployeeOriginalPositionID'] != $theSelectedPositionID){
		$NumberOfChanges++;
	}
	
	if($NumberOfChanges > 0){
		// Update selected employee connection with a new company position
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`employee` 
					SET		`PositionID` = :PositionID
					WHERE 	`companyID` = :CompanyID
					AND 	`userID` = :UserID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_POST['CompanyID']);
			$s->bindValue(':UserID', $_POST['UserID']);
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
		
		$_SESSION['EmployeeAdminFeedback'] = "Successfully updated the employee.";		
	} else {
		$_SESSION['EmployeeAdminFeedback'] = "No changes were made to the employee.";
	}
	
	clearEditEmployeeSessions();
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isSet($_GET['Company'])){	
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}
	
	// Load employee list webpage with updated database
	header('Location: .');
	exit();
}

// If admin wants to transfer an employee
if ((isSet($_POST['action']) and $_POST['action'] == 'Transfer') OR 
	(isSet($_SESSION['refreshTransferEmployee']) AND $_SESSION['refreshTransferEmployee'])
	){

	unset($_SESSION['refreshTransferEmployee']);

	if((isSet($_POST['CompanyID']) AND !empty($_POST['CompanyID'])) OR isSet($_SESSION['TransferEmployeeSelectedCompanyID'])){

		if(!isSet($_SESSION['TransferEmployeeSelectedCompanyID'])){
			$_SESSION['TransferEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
		}

		$companyID = $_SESSION['TransferEmployeeSelectedCompanyID'];

		if(!isSet($_SESSION['TransferEmployeeSelectedCompanyName'])){
			$_SESSION['TransferEmployeeSelectedCompanyName'] = $_POST['CompanyName'];
		}

		$transferCompanyName = $_SESSION['TransferEmployeeSelectedCompanyName'];

		if(!isSet($_SESSION['TransferEmployeeSelectedUserID'])){
			$_SESSION['TransferEmployeeSelectedUserID'] = $_POST['UserID'];
		}
		
		$userID = $_SESSION['TransferEmployeeSelectedUserID'];

		if(!isSet($_SESSION['TransferEmployeeSelectedUserName'])){
			$_SESSION['TransferEmployeeSelectedUserName'] = $_POST['UserName'];
		}

		$transferEmployeeName = $_SESSION['TransferEmployeeSelectedUserName'];
		
		if(isSet($_SESSION['TransferEmployeeSelectedCompanyID2'])){
			$selectedCompanyIDToTransferTo = $_SESSION['TransferEmployeeSelectedCompanyID2'];
		}

		// Get companies
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT	`CompanyID`		AS CompanyID,
							`name`			AS CompanyName
					FROM	`company`
					WHERE 	`companyID` != :CompanyID
					AND 	`isActive` = 1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $companyID);
			$s->execute();
			$companies = $s->fetchAll(PDO::FETCH_ASSOC);

			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error getting list of companies: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

		var_dump($_SESSION);	// TO-DO: Remove after done testing

		include_once 'transfer.html.php';
		exit();
	} else {
		$_SESSION['CompanyUserFeedback'] = "Could not retrieve information to merge this company.";
	}
}

// If admin wants to confirm the employee transfer
if (isSet($_POST['action']) and $_POST['action'] == 'Confirm Transfer'){

	// Check that we have a secondary company to merge into submitted
	if(!isSet($_POST['transferCompanyID']) OR (isSet($_POST['transferCompanyID']) AND empty($_POST['transferCompanyID']))){
		$_SESSION['TransferEmployeeError'] = "You cannot transfer an employee without choosing a target company.";
		$_SESSION['refreshTransferEmployee'] = TRUE;
		header("Location: .");
		exit();
	}

	$_SESSION['TransferEmployeeSelectedCompanyID2'] = $_POST['transferCompanyID'];

	// Check that we have a password submitted
	if(!isSet($_POST['password']) OR (isSet($_POST['password']) AND empty($_POST['password']))){
		$_SESSION['TransferEmployeeError'] = "You cannot transfer an employee without submitting your password.";
		$_SESSION['refreshTransferEmployee'] = TRUE;
		header("Location: .");
		exit();
	}

	$password = $_POST['password'];
	$hashedPassword = hashPassword($password);

	// Check if password is correct
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'SELECT 	`password`	AS RealPassword
				FROM 	`user`
				WHERE	`userID` = :UserID
				LIMIT 	1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
		$s->execute();

		$row = $s->fetch(PDO::FETCH_ASSOC);
		$realPassword = $row['RealPassword'];
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error confirming password: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	if($hashedPassword != $realPassword){
		$pdo = null;
		$_SESSION['TransferEmployeeError'] = "The password you submitted is incorrect.";
		$_SESSION['refreshTransferEmployee'] = TRUE;
		header("Location: .");
		exit();
	}

	// Password is correct. Let's transfer the employee and its booking history to the new company
	if(	isSet($_SESSION['TransferEmployeeSelectedCompanyID']) AND !empty($_SESSION['TransferEmployeeSelectedCompanyID']) AND
		isSet($_SESSION['TransferEmployeeSelectedCompanyID2']) AND !empty($_SESSION['TransferEmployeeSelectedCompanyID2']) AND
		isSet($_SESSION['TransferEmployeeSelectedUserID']) AND !empty($_SESSION['TransferEmployeeSelectedUserID']) AND
		$_SESSION['TransferEmployeeSelectedCompanyID'] != $_SESSION['TransferEmployeeSelectedCompanyID2'])
	{
		try
		{
			$pdo = connect_to_db();
			$sql = 'SELECT 	`name`	AS NewCompanyName
					FROM 	`company`
					WHERE	`companyID` = :companyID
					LIMIT 	1';
			$s = $pdo->prepare($sql);
			$s->bindValue(':companyID', $_SESSION['TransferEmployeeSelectedCompanyID2']);
			$s->execute();

			$row = $s->fetch(PDO::FETCH_ASSOC);
			$oldCompanyName = $_SESSION['TransferEmployeeSelectedCompanyName'];
			$newCompanyName = $row['NewCompanyName'];
			
			$pdo->beginTransaction();
			$sql = 'UPDATE	`booking`
					SET 	`CompanyID` = :CompanyID2
					WHERE	`CompanyID`	= :CompanyID
					AND		`UserID` = :UserID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['TransferEmployeeSelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['TransferEmployeeSelectedCompanyID2']);
			$s->bindValue(':UserID', $_SESSION['TransferEmployeeSelectedUserID']);
			$s->execute();

			$sql = 'UPDATE IGNORE	`employee`
					SET				`CompanyID` = :CompanyID2
					WHERE			`CompanyID` = :CompanyID
					AND				`UserID` = :UserID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['TransferEmployeeSelectedCompanyID']);
			$s->bindValue(':CompanyID2', $_SESSION['TransferEmployeeSelectedCompanyID2']);
			$s->bindValue(':UserID', $_SESSION['TransferEmployeeSelectedUserID']);
			$s->execute();

			// Make sure the employee is removed if it failed to update due to a duplicate
			$sql = 'DELETE FROM `employee`
					WHERE		`CompanyID` = :CompanyID
					AND			`UserID` = :UserID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_SESSION['TransferEmployeeSelectedCompanyID']);
			$s->bindValue(':UserID', $_SESSION['TransferEmployeeSelectedUserID']);
			$s->execute();

			$pdo->commit();
		}
		catch (PDOException $e)
		{
			$pdo = null;
			$error = 'Error transfering employee: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			exit();
		}

		$employeeName = $_SESSION['TransferEmployeeSelectedUserName'];
		$_SESSION['EmployeeAdminFeedback'] = 	"Successfully transferred the employee: " . $employeeName . 
												"\nFrom the company: " . $oldCompanyName .
												"\nTo the company: " . $newCompanyName;

		// Add log event that an employee was transferred
		try
		{
			// Save a description with information about the employee that was removed
			// from the company.
			$logEventDescription = "The user: " . $employeeName . 
			" was transferred as an employee from the company: " . $oldCompanyName . 
			"\nto the company: " . $newCompanyName . 
			".\nThis also transferred all the user's booking history, for that company, to the new company." . 
			".\nTransferred by: " . $_SESSION['LoggedInUserName'];

			$pdo = connect_to_db();
			$sql = "INSERT INTO `logevent` 
					SET			`actionID` = 	(
													SELECT 	`actionID` 
													FROM 	`logaction`
													WHERE 	`name` = 'Employee Transferred'
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
	} else {
		$_SESSION['EmployeeAdminFeedback'] = "Failed to transfer the employee.";
	}

	clearTransferEmployeeSessions();

	//	Go to the employee main page with the appropriate values
	if(isSet($_GET['Company'])){
		// Refresh employee for the specific company again
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	} else {
		// Do a normal page reload
		header('Location: .');
		exit();
	}
}

// If the user clicks any cancel buttons he'll be directed back to the employees page again
if (isSet($_POST['action']) AND $_POST['action'] == 'Cancel'){
	$_SESSION['EmployeeAdminFeedback'] = "Cancel button clicked. Taking you back to /admin/employees/!";
}

if(isSet($refreshEmployees) AND $refreshEmployees){
	// TO-DO: Add code that should occur on a refresh
	unset($refreshEmployees);
}

// Remove any unused variables from memory
clearAddEmployeeSessions();
clearEditEmployeeSessions();
clearTransferEmployeeSessions();

// Get only information from the specific company
if(isSet($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
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
							AND 		b.`companyID` = :CompanyID
							AND 		c.`CompanyID` = b.`companyID`
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
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
							AND 		b.`companyID` = :CompanyID
							AND 		c.`CompanyID` = b.`companyID`
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
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
							AND 		b.`companyID` = :CompanyID
							AND 		c.`CompanyID` = b.`companyID`
						) 							AS TotalBookingTimeUsed							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID 
				WHERE 	c.`companyID` = :CompanyID";
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s				
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
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
							WHERE 		b.`companyID` = :CompanyID
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :CompanyID)
							AND 		b.`userID` = UsrID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
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
							WHERE 		b.`companyID` = :CompanyID
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :CompanyID)
							AND 		b.`userID` = UsrID
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
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
							WHERE 		b.`companyID` = :CompanyID
							AND 		b.`userID` IS NOT NULL
							AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :CompanyID)
							AND 		b.`userID` = UsrID
						)														AS TotalBookingTimeUsed
				FROM 		`company` c
				JOIN 		`booking` b
				ON 			c.`companyID` = b.`companyID`
				JOIN 		`user` u 
				ON 			u.userID = b.UserID 
				WHERE 		c.`companyID` = :CompanyID
				AND 		b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = :CompanyID)
				GROUP BY 	UsrID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
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
							WHERE 		b.`companyID` = :CompanyID
							AND 		b.`userID` IS NULL
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`prevStartDate`
							AND			c.`startDate`
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
							WHERE 		b.`companyID` = :CompanyID
							AND 		b.`userID` IS NULL
							AND 		b.`actualEndDateTime`
							BETWEEN		c.`startDate`
							AND			c.`endDate`
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
						WHERE 	b.`companyID` = :CompanyID
						AND 	b.`userID` IS NULL
						)														AS TotalBookingTimeUsed
				FROM 	`company`
				WHERE	`companyID` = :CompanyID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
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
}

// Get information from all companies
if(!isSet($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 		u.`userID`										AS UsrID,
							c.`companyID`									AS TheCompanyID,
							c.`name`										AS CompanyName,
							u.`firstName`, 
							u.`lastName`,
							u.`email`,
							cp.`name`										AS PositionName, 
							e.`startDateTime`								AS StartDateTime,
							UNIX_TIMESTAMP(e.`startDateTime`)				AS OrderByDate,
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
								INNER JOIN 	`employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN 	`company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
								AND 		c.`CompanyID` = b.`companyID`
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`prevStartDate`
								AND			c.`startDate`
							) 												AS PreviousMonthBookingTimeUsed,							
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
								))) 	AS BookingTimeUsed
								FROM 		`booking` b
								INNER JOIN 	`employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN 	`company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
								AND 		c.`CompanyID` = b.`companyID`
								AND 		b.`actualEndDateTime`
								BETWEEN		c.`startDate`
								AND			c.`endDate`
							) 												AS MonthlyBookingTimeUsed,
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
								INNER JOIN 	`employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN 	`company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
								AND 		c.`CompanyID` = b.`companyID`
							) 												AS TotalBookingTimeUsed							
				FROM 		`company` c 
				JOIN 		`employee` e
				ON 			e.CompanyID = c.CompanyID 
				JOIN 		`companyposition` cp 
				ON 			cp.PositionID = e.PositionID
				JOIN 		`user` u 
				ON 			u.userID = e.UserID
				ORDER BY 	CompanyName DESC,
							OrderByDate DESC";
		$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
		$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 1min = 60s				
		$s = $pdo->prepare($sql);
		$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
		$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
		$s->execute();
		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum = 0;
		}
		//close connection
		$pdo = null;	
	}
	catch (PDOException $e)
	{
		$error = 'Error getting employee information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
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

// Create the employees list in HTML
include_once 'employees.html.php';
?>