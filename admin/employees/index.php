<?php 
// This is the index file for the EMPLOYEES folder

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

// If admin wants to be able to delete companies it needs to enabled first
if (isset($_POST['action']) AND $_POST['action'] == "Enable Remove"){
	$_SESSION['employeesEnableDelete'] = TRUE;
	$refreshEmployees = TRUE;
}

// If admin wants to be disable company deletion
if (isset($_POST['action']) AND $_POST['action'] == "Disable Remove"){
	unset($_SESSION['employeesEnableDelete']);
	$refreshEmployees = TRUE;
}

// If admin wants to remove an employee from the selected company
if(isset($_POST['action']) AND $_POST['action'] == 'Remove'){
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
	
	$_SESSION['EmployeeUserFeedback'] = "Successfully removed the employee.";
	
	// Add a log event that an employee was removed from a company
	try
	{
		// Save a description with information about the employee that was removed
		// from the company.
		$logEventDescription = 'The user: ' . $_POST['UserName'] . 
		' was removed from the company: ' . $_POST['CompanyName'] . 
		'. Removed by: ' . $_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Employee Removed'
											),
							`companyID` = :CompanyID,
							`userID` = :UserID,
							`positionID` = :PositionID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_POST['CompanyID']);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':PositionID', $_POST['PositionID']);		
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
	if(isset($_GET['Company'])){	
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
if(isset($_POST['action']) AND $_POST['action'] == 'Search'){
	// Forget the old array values we have saved
	unset($_SESSION['AddEmployeeCompaniesArray']);
	unset($_SESSION['AddEmployeeUsersArray']);
	
	$_SESSION['AddEmployeeShowSearchResults'] = TRUE;
	// Let's remember what was selected and searched for
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isset($_GET['Company'])){	
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
if ((isset($_POST['action']) AND $_POST['action'] == 'Add Employee') OR 
	(isset($_SESSION['refreshAddEmployee']) AND $_SESSION['refreshAddEmployee']))
{	

	$companysearchstring = '';			
	$usersearchstring = '';

	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshAddEmployee']) AND $_SESSION['refreshAddEmployee']){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddEmployee']);
		
		// Remember the company string that was searched before refreshing
		if(isset($_SESSION['AddEmployeeCompanySearch'])){
			$companysearchstring = $_SESSION['AddEmployeeCompanySearch'];
			unset($_SESSION['AddEmployeeCompanySearch']);
		}
		
		// Remember the user string that was searched before refreshing
		if(isset($_SESSION['AddEmployeeUserSearch'])){
			$usersearchstring = $_SESSION['AddEmployeeUserSearch'];
			unset($_SESSION['AddEmployeeUserSearch']);
		}
		
		// Remember what company was selected before refreshing
		if(isset($_SESSION['AddEmployeeSelectedCompanyID'])){
			$selectedCompanyID = $_SESSION['AddEmployeeSelectedCompanyID'];
			unset($_SESSION['AddEmployeeSelectedCompanyID']);
		}
		
		// Remember what user was selected before refreshing
		if(isset($_SESSION['AddEmployeeSelectedUserID'])){
			$selectedUserID = $_SESSION['AddEmployeeSelectedUserID'];
			unset($_SESSION['AddEmployeeSelectedUserID']);
		}	

		// Remember what company position was selected before refreshing
		if(isset($_SESSION['AddEmployeeSelectedPositionID'])){
			$selectedPositionID = $_SESSION['AddEmployeeSelectedPositionID'];
			unset($_SESSION['AddEmployeeSelectedPositionID']);
		}		
	}

	// Get info about company position, users and companies from the database
	// if we don't already have them saved in a session array
	if(	!isset($_SESSION['AddEmployeeCompaniesArray']) OR 
		!isset($_SESSION['AddEmployeeUsersArray']) OR
		!isset($_SESSION['AddEmployeeCompanyPositionArray'])){	
	
		try
		{	
			// Get all users, companies and companypositions so admin can search/choose from them
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			
				//Companies	
			// Only get info if we haven't gotten it before
			if(!isset($_SESSION['AddEmployeeCompaniesArray'])){
				// We don't have info about companies saved. Let's get it
				
				if(!isset($_GET['Company'])){
					// If we're NOT looking at a specific company already
					$sql = 'SELECT 	`companyID` AS CompanyID,
									`name`		AS CompanyName
							FROM 	`company`';
							
					if ($companysearchstring != ''){
						$sqladd = " WHERE `name` LIKE :search";
						$sql = $sql . $sqladd;	
						
						$finalcompanysearchstring = '%' . $companysearchstring . '%';
						
						$s = $pdo->prepare($sql);
						$s->bindValue(':search', $finalcompanysearchstring);
						$s->execute();
						$result = $s->fetchAll();
					} else {
						$result = $pdo->query($sql);
					}
					
					// Get the rows of information from the query
					// This will be used to create a dropdown list in HTML
					foreach($result as $row){
						$companies[] = array(
												'CompanyID' => $row['CompanyID'],
												'CompanyName' => $row['CompanyName']
												);
					}
					
					if(isset($companies)){
						$_SESSION['AddEmployeeCompaniesArray'] = $companies;
						$companiesFound = sizeOf($companies);
					} else {
						$_SESSION['AddEmployeeCompaniesArray'] = array();
						$companiesFound = 0;
					}
					if(isset($_SESSION['AddEmployeeShowSearchResults']) AND $_SESSION['AddEmployeeShowSearchResults']){
						$_SESSION['AddEmployeeSearchResult'] = "The search result found $companiesFound companies.";
					}					
						
				} else {
					$sql = 'SELECT 	`companyID` AS CompanyID,
									`name`		AS CompanyName
							FROM 	`company`
							WHERE 	`companyID` = :CompanyID
							LIMIT 	1';
					$s = $pdo->prepare($sql);
					$s->bindValue(':CompanyID', $_GET['Company']);
					$s->execute();
					$companies = $s->fetch();		
				}
			} else {
				$companies = $_SESSION['AddEmployeeCompaniesArray'];
			}
			
				// Company Positions
			//Only get info if we haven't gotten it before
			if(!isset($_SESSION['AddEmployeeCompanyPositionArray'])){
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
			if(!isset($_SESSION['AddEmployeeUsersArray'])){
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
					$result = $s->fetchAll();
					
				} else {
					$result = $pdo->query($sql);
				}
					
				// Get the rows of information from the query
				// This will be used to create a dropdown list in HTML
				foreach($result as $row){
					$users[] = array(
											'UserID' => $row['UserID'],
											'UserIdentifier' => $row['lastname'] . ', ' .
											$row['firstname'] . ' - ' . $row['email']
											);
				}
				if(isset($users)){
					$_SESSION['AddEmployeeUsersArray'] = $users;
					$usersFound = sizeOf($users);
				} else {
					$_SESSION['AddEmployeeUsersArray'] = array();
					$usersFound = 0;
				}
				if(isset($_SESSION['AddEmployeeShowSearchResults']) AND $_SESSION['AddEmployeeShowSearchResults']){
					if(isset($_SESSION['AddEmployeeSearchResult'])){
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


	if(isset($_SESSION['AddEmployeeSearchResult'])){
		$_SESSION['AddEmployeeSearchResult'] .= ".";
	}
	unset($_SESSION['AddEmployeeShowSearchResults']);

	var_dump($_SESSION); // TO-DO: remove after testing is done
	
	// Change to the actual html form template
	include 'addemployee.html.php';
	exit();
}

// When admin has added the needed information and wants to add an employee connection
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Employee')
{
	// If we're looking at a specific company
	if(isset($_GET['Company'])){
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
		
		if(isset($_GET['Company'])){	
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
			
			if(isset($_GET['Company'])){	
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
	
		$_SESSION['EmployeeUserFeedback'] = "Successfully added the employee.";
	
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
		if(isset($_SESSION['AddEmployeeUsersArray'])){
			foreach($_SESSION['AddEmployeeUsersArray'] AS $row){
				if($row['UserID'] == $_POST['UserID']){
					$userinfo = $row['UserIdentifier'];
					break;
				}
			}
			unset($_SESSION['AddEmployeeUsersArray']);
		}
		
		// Get selected company name
		if(isset($_SESSION['AddEmployeeCompaniesArray'])){
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
		if(isset($_SESSION['AddEmployeeCompanyPositionArray'])){
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
		' and was given the position: ' . $positioninfo . ". Added by : " .
		$_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Employee Added'
											),
							`companyID` = :CompanyID,
							`userID` = :UserID,
							`positionID` = :PositionID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $CompanyID);
		$s->bindValue(':UserID', $_POST['UserID']);
		$s->bindValue(':PositionID', $_POST['PositionID']);		
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
	if(isset($_GET['Company'])){
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
if (isset($_POST['action']) AND $_POST['action'] == 'Change Role')
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
				AND 	e.companyID = :CompanyID';
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
	$row = $s->fetch();
		
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
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Role')
{
	// Check if anything actually changed
	$theSelectedPositionID = $_POST['PositionID'];
	$NumberOfChanges = 0;
	
	if(	isset($_SESSION['EditEmployeeOriginalPositionID']) AND 
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
		
		$_SESSION['EmployeeUserFeedback'] = "Successfully updated the employee.";		
	} else {
		$_SESSION['EmployeeUserFeedback'] = "No changes were made to the employee.";
	}
	
	clearEditEmployeeSessions();
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isset($_GET['Company'])){	
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}
	
	// Load employee list webpage with updated database
	header('Location: .');
	exit();
}

// If the user clicks any cancel buttons he'll be directed back to the employees page again
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel'){
	$_SESSION['EmployeeUserFeedback'] = "Cancel button clicked. Taking you back to /admin/employees/!";
}

if(isset($refreshEmployees) AND $refreshEmployees){
	// TO-DO: Add code that should occur on a refresh
	unset($refreshEmployees);
}

// Remove any unused variables from memory 
// TO-DO: Change if this ruins having multiple tabs open etc.
clearAddEmployeeSessions();
clearEditEmployeeSessions();

// Get only information from the specific company
if(isset($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		// TO-DO: Change SQL Query if time calculation is broken
		// Changed it so it should (in theory at least) calculate correctly now
		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						cp.`name`					AS PositionName, 
						e.`startDateTime`			AS StartDateTime,
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
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = :id
							AND 		c.`CompanyID` = b.`companyID`
							AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
							AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						) 							AS MonthlyBookingTimeUsed,
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
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_GET['Company']);
		$s->execute();
		
		$result = $s->fetchAll();
		$rowNum = sizeOf($result);
		
		// Start a second SQL query to collect the booked time by removed users
		// TO-DO: Still needs fixing. Shows employees as removed.
		$sql = "SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						(
						SELECT 	(
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
								) AS TotalBookingTimeByRemovedEmployees
						FROM 	`booking` b
						INNER JOIN `employee` e
						ON 		e.`companyID` = b.`companyID`
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NOT NULL
						AND		e.`userID` != b.`userID`
						AND 	b.`userID` = UsrID
						AND 			YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 			MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)														AS MonthlyBookingTimeUsed,
						(
						SELECT 	(
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
								) AS TotalBookingTimeByRemovedEmployees
						FROM 	`booking` b
						INNER JOIN `employee` e
						ON 		e.`companyID` = b.`companyID`
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NOT NULL
						AND		e.`userID` != b.`userID`
						AND 	b.`userID` = UsrID
						)														AS TotalBookingTimeUsed
				FROM 		`company` c
				JOIN 		`booking` b
				ON 			c.`companyID` = b.`companyID`
				JOIN 		`user` u 
				ON 			u.userID = b.UserID 
				WHERE 		c.`companyID` = :id
				GROUP BY 	UsrID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_GET['Company']);
		$s->execute();
		
		$removedEmployeesResult = $s->fetchAll();
		$removedEmployeesResultRowNum = sizeOf($removedEmployeesResult);
		
		// SQL Query to get booked time for deleted users
		// TO-DO: needs testing
		$sql = "SELECT 	`companyID`				AS TheCompanyID,
						`name`					AS CompanyName,
						(
						SELECT 	(
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
								) AS TotalBookingTimeByDeletedUsers
						FROM 	`booking` b
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NULL
						AND 	YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 	MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)														AS MonthlyBookingTimeUsed,
						(
						SELECT 	(
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
								) AS TotalBookingTimeByDeletedUsers
						FROM 	`booking` b
						WHERE 	b.`companyID` = :id
						AND 	b.`userID` IS NULL
						)														AS TotalBookingTimeUsed
				FROM 	`company`
				WHERE	`companyID` = :id";
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_GET['Company']);
		$s->execute();
		
		$deletedUsersResult = $s->fetchAll();
		$deletedUsersResultRowNum = sizeOf($deletedUsersResult);	
		
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
		
		
		if($row['MonthlyBookingTimeUsed'] == null){
			$MonthlyTimeUsed = 'N/A';
		} else {
			$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
		}

		if($row['TotalBookingTimeUsed'] == null){
			$TotalTimeUsed = 'N/A';
		} else {
			$TotalTimeUsed = $row['TotalBookingTimeUsed'];
		}		
			$removedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'firstName' => $row['firstName'],
										'lastName' => $row['lastName'],
										'email' => $row['email'],	
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}
		if($removedEmployees[0]['TotalBookingTimeUsed'] == ""){
			// The company has no used booking time by removed users
			unset($removedEmployees);
		}
	}
	
	// If we're looking at a specific company and they have old booking time used by now deleted users
	if($deletedUsersResultRowNum > 0){
		foreach($deletedUsersResult AS $row){	
		
		
		if($row['MonthlyBookingTimeUsed'] == null){
			$MonthlyTimeUsed = 'N/A';
		} else {
			$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
		}

		if($row['TotalBookingTimeUsed'] == null){
			$TotalTimeUsed = 'N/A';
		} else {
			$TotalTimeUsed = $row['TotalBookingTimeUsed'];
		}		
			$deletedEmployees[] = array(
										'CompanyID' => $row['TheCompanyID'],
										'CompanyName' => $row['CompanyName'],
										'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
										'TotalBookingTimeUsed' => $TotalTimeUsed
										);
		}
		if($deletedEmployees[0]['TotalBookingTimeUsed'] == ""){
			// The company has no used booking time by deleted users
			unset($deletedEmployees);
		}
	}	
}

// Get information from all companies
if(!isset($_GET['Company'])){
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
								INNER JOIN `employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN `company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
								AND 		c.`CompanyID` = b.`companyID`
								AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
								AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
							) 												AS MonthlyBookingTimeUsed,
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
								INNER JOIN `employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN `company` c
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
				ORDER BY 	OrderByDate DESC";
				
		$result = $pdo->query($sql);
		$rowNum = $result->rowCount();
		
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
	
	// TO-DO: Transform monthly/total booking time into text instead time
	// e.g. 1 day 24 hours, 30 minutes, 23 hours 40 minutes
	
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
						'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
						'TotalBookingTimeUsed' => $TotalTimeUsed,
						'StartDateTime' => $displayStartDateTime
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the employees list in HTML
include_once 'employees.html.php';
?>