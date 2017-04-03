<?php 
// This is the index file for the COMPANIES folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// If admin wants to remove a company from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	// Delete selected company from database
	try
	{
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
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}


// Display companies list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		c.companyID 										AS CompID,
						c.`name` 											AS CompanyName, 
						COUNT(c.`name`) 									AS NumberOfEmployees,
						(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
						FROM `booking` b 
						INNER JOIN `employee` e 
						ON b.`UserID` = e.`UserID` 
						INNER JOIN `company` c 
						ON e.`CompanyID` = c.`CompanyID` 
						WHERE b.`CompanyID` = CompID
						AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND MONTH(b.`actualEndDateTime`) = MONTH(NOW()))   	AS MonthlyCompanyWideBookingTimeUsed,
						(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
						FROM `booking` b 
						INNER JOIN `employee` e 
						ON b.`UserID` = e.`UserID` 
						INNER JOIN `company` c 
						ON e.`CompanyID` = c.`CompanyID` 
						WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed,
			DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')						AS DeletionDate,
			DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')					AS DatetimeCreated
			FROM 		`company` c 
			JOIN 		`employee` e 
			ON 			c.CompanyID = e.CompanyID 
			GROUP BY 	c.`name`";
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
// Define the users variable to avoid errors if it's empty
foreach ($result as $row)
{
	if($row['MonthlyCompanyWideBookingTimeUsed'] == null){
		$MonthlyTimeUsed = '00:00:00';
	} else {
		$MonthlyTimeUsed = $row['MonthlyCompanyWideBookingTimeUsed'];
	}
	
	if($row['TotalCompanyWideBookingTimeUsed'] == null){
		$TotalTimeUsed = '00:00:00';
	} else {
		$TotalTimeUsed = $row['TotalCompanyWideBookingTimeUsed'];
	}
	
	$companies[] = array('id' => $row['CompID'], 
					'CompanyName' => $row['CompanyName'],
					'NumberOfEmployees' => $row['NumberOfEmployees'],
					'MonthlyCompanyWideBookingTimeUsed' => $MonthlyTimeUsed,
					'TotalCompanyWideBookingTimeUsed' => $TotalTimeUsed,
					'DeletionDate' => $row['DeletionDate'],
					'DatetimeCreated' => $row['DatetimeCreated']
					);
}

// Create the companies list in HTML
include_once 'companies.html.php';
?>