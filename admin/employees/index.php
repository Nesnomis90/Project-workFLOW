<?php 
// This is the index file for the EMPLOYEES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

session_start();
// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
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
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isset($_GET['Company'])){
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	}
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}

if(isset($_POST['action']) AND $_POST['action'] == 'Search'){
	// Admin clicked the search button, trying to limit the shown company and user lists
	// Let's remember what was searched for
	session_start();
	
	// If we are looking at a specific company, let's refresh info about
	// that company again.
	if(isset($_GET['Company'])){	
		$_SESSION['AddEmployeeUserSearch'] = $_POST['usersearchstring'];
		$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
		$_SESSION['refreshAddEmployee'] = TRUE;
		
		$TheCompanyID = $_GET['Company'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
		header("Location: $location");
		exit();
	} else {
		$_SESSION['AddEmployeeCompanySearch'] = $_POST['companysearchstring'];
		$_SESSION['AddEmployeeUserSearch'] = $_POST['usersearchstring'];
		$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
		$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
		
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

	session_start();
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshAddEmployee']) AND $_SESSION['refreshAddEmployee']){
		// Acknowledge that we have refreshed the form
		unset($_SESSION['refreshAddEmployee']);
		
		// Display the 'error' that made us refresh
		if(isset($_SESSION['AddEmployeeError'])){
			$AddEmployeeError = $_SESSION['AddEmployeeError'];
			unset($_SESSION['AddEmployeeError']);
		}
		
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
	}

	// Get info about company position, users and companies from the database
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
		
		session_start();
		$_SESSION['AddEmployeeCompanyPositionArray'] = $companyposition;

		// Get all companies and users so the admin can search/choose from them
			//Companies	
			
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
				
		} else {
			$sql = 'SELECT 	`companyID` AS CompanyID,
							`name`		AS CompanyName
					FROM 	`company`
					WHERE 	`companyID` = :CompanyID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':CompanyID', $_GET['Company']);
			$s->execute();
			$companies = $s->fetch();
		}
		
		session_start();
		$_SESSION['AddEmployeeCompaniesArray'] = $companies;

		
			//	Users - Only active ones?
			// 	TO-DO: Change to allow all users?	
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
		session_start();
		$_SESSION['AddEmployeeUsersArray'] = $users;
			
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching user and company lists: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
		
	// Change to the actual html form template
	include 'addemployee.html.php';
	exit();
}

// When admin has added the needed information and wants to add an employee connection
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Employee')
{
	session_start();
	// Make sure we only do this if user filled out all values
	if(!isset($_GET['Company'])){
		// If we have to select a company
		if($_POST['CompanyID'] == '' AND $_POST['UserID'] == ''){
			// We didn't have enough values filled in. "go back" to employee fill in
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeError'] = "Need to select a user and a company first!";
			header('Location: .');
			exit();
		} elseif($_POST['CompanyID'] != '' AND $_POST['UserID'] == ''){
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeError'] = "Need to select a user first!";
			$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
			header('Location: .');
			exit();
		} elseif($_POST['CompanyID'] == '' AND $_POST['UserID'] != ''){
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeError'] = "Need to select a company first!";
			$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
			header('Location: .');
			exit();
		}		
	} else {
		// If we have already selected a specific company
		if($_POST['UserID'] == ''){
			// We didn't have enough values filled in. "go back" to employee fill in
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeError'] = "Need to select a user first!";
			
			$TheCompanyID = $_GET['Company'];
			$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
			header("Location: $location");
			exit();
		}
	}

	
	// If we're looking at a specific company
	session_start();
	if(isset($_GET['Company'])){
		$CompanyID = $_SESSION['AddEmployeeCompaniesArray']['CompanyID'];
	} else {
		$CompanyID = $_POST['CompanyID'];
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
			
			session_start();
			
			$_SESSION['AddEmployeeSelectedUserID'] = $_POST['UserID'];
			$_SESSION['refreshAddEmployee'] = TRUE;
			$_SESSION['AddEmployeeError'] = "The selected user is already an employee in the selected company!";
			
			if(isset($_GET['Company'])){
				
				$_SESSION['AddEmployeeSelectedCompanyID'] = $_GET['Company'];
				
				$TheCompanyID = $_GET['Company'];
				$location = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $TheCompanyID;
				header("Location: $location");
				exit();
			}


			$_SESSION['AddEmployeeSelectedCompanyID'] = $_POST['CompanyID'];
			header('Location: .');
			exit();			
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
		
		session_start();
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
			foreach($_SESSION['AddEmployeeCompaniesArray'] AS $row){
				if($row['CompanyID'] == $_POST['CompanyID']){
					$companyinfo = $row['CompanyName'];
					break;
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
	
		
		// Save a description with some extra information to be kept after 
		// the user or company has been removed
		$description = 'The user: ' . $userinfo . 
		' was added to the company: ' . $companyinfo . 
		' and was given the position: ' . $positioninfo;
		
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

// if admin wants to set the date to remove for a company
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
	
	// Change to the actual form we want to use
	include 'changerole.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Role')
{
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
		$s->bindValue(':PositionID', $_POST['PositionID']);
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
	// Doesn't actually need any code to work, since it happends automatically when a submit
	// occurs. *it* being doing the normal startup code.
	// Might be useful for something later?
	echo "<b>Cancel button clicked. Taking you back to /admin/employees/!</b><br />";
}

// Get only information from the specific company
if(isset($_GET['Company'])){
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	
	$sql = "SELECT 	u.`userID`					AS UsrID,
					c.`companyID`				AS TheCompanyID,
					c.`name`					AS CompanyName,
					u.`firstName`, 
					u.`lastName`,
					u.`email`,
					cp.`name`					AS PositionName, 
					DATE_FORMAT(e.`startDateTime`,'%d %b %Y %T') 	AS StartDateTime,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = :id
						AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
					) 							AS MonthlyBookingTimeUsed,
					(
						SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
									TIME_TO_SEC(b.`startDateTime`))) 
						FROM 		`booking` b
						INNER JOIN `employee` e
						ON 			b.`userID` = e.`userID`
						INNER JOIN `company` c
						ON 			c.`companyID` = e.`companyID`
						INNER JOIN 	`user` u 
						ON 			e.`UserID` = u.`UserID` 
						WHERE 		b.`userID` = UsrID
						AND 		b.`companyID` = :id
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
	
	//close connection
	$pdo = null;
}

session_start();

if(!isset($_GET['Company'])){
	// Display employee list
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
							DATE_FORMAT(e.`startDateTime`,'%d %b %Y %T') 	AS StartDateTime,
							UNIX_TIMESTAMP(e.`startDateTime`)				AS OrderByDate,
							(
								SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
											TIME_TO_SEC(b.`startDateTime`))) 
								FROM 		`booking` b
								INNER JOIN `employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN `company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
								AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
								AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
							) 												AS MonthlyBookingTimeUsed,
							(
								SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
											TIME_TO_SEC(b.`startDateTime`))) 
								FROM 		`booking` b
								INNER JOIN `employee` e
								ON 			b.`userID` = e.`userID`
								INNER JOIN `company` c
								ON 			c.`companyID` = e.`companyID`
								INNER JOIN 	`user` u 
								ON 			e.`UserID` = u.`UserID` 
								WHERE 		b.`userID` = UsrID
								AND 		b.`companyID` = TheCompanyID
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
	
	if($row['MonthlyBookingTimeUsed'] == null){
		$MonthlyTimeUsed = '00:00:00';
	} else {
		$MonthlyTimeUsed = $row['MonthlyBookingTimeUsed'];
	}

	if($row['TotalBookingTimeUsed'] == null){
		$TotalTimeUsed = '00:00:00';
	} else {
		$TotalTimeUsed = $row['TotalBookingTimeUsed'];
	}
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$employees[] = array('CompanyID' => $row['TheCompanyID'], 
						'UsrID' => $row['UsrID'],
						'CompanyName' => $row['CompanyName'],
						'PositionName' => $row['PositionName'],
						'firstName' => $row['firstName'],
						'lastName' => $row['lastName'],
						'email' => $row['email'],
						'MonthlyBookingTimeUsed' => $MonthlyTimeUsed,
						'TotalBookingTimeUsed' => $TotalTimeUsed,
						'StartDateTime' => $row['StartDateTime']
						);
}
/*
if (isset($_SESSION['EmployeeInfoFromCompany'])){
	// We are sent here from the company tab
	// Let's display the company specific information
	$employees = $_SESSION['EmployeeInfoFromCompany'];
	unset($_SESSION['EmployeeInfoFromCompany']);
	
	$rowNum = sizeOf($employees);
}*/

// Create the employees list in HTML
include_once 'employees.html.php';	

?>