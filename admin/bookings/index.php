<?php 
// This is the index file for the BOOKINGS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Display booking information list
// TO-DO: THIS NEEDS THE ADD, ADDFORM, EDIT, EDITFORM CODE SNIPPETS

// If admin wants to remove a booked meeting from the database
// TO-DO: ADD A CONFIRMATION BEFORE ACTUALLY DOING THE DELETION!
// MAYBE BY TYPING ADMIN PASSWORD AGAIN?
if (isset($_POST['action']) and $_POST['action'] == 'Delete')
{
	// Delete selected booked meeting from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `booking` 
				WHERE 		`bookingID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error getting booked meeting to delete: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add a booked meeting to the database
// we load a new html form
// TO-DO: NEED TO KNOW WHAT USER IS LOGGED IN TO GET THEIR USERID
// TO-DO: ADMIN STRICTLY SPEAKING DOESN'T NEED THIS ADD FUNCTION
// IT'S JUST THAT WE HAVE TO MAKE THAT FUNCTION FOR THE ACTUAL USERS
// LATER ANYWAY
if (isset($_GET['add']))
{
	try
	{
		// Retrieve the user's default displayname and bookingdescription
		// if they have any.
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	u.`userID`,
						u.`bookingdescription`, 
						u.`displayname`,
						c.`companyID`,
						c.`name` 					AS companyName
				FROM 	`user` u
				JOIN 	`employee` e
				ON 		e.userID = u.userID
				JOIN	`company` c
				ON 		c.companyID = e.companyID
				WHERE 	u.`userID` = :userID'; // <--- FIX THIS?
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $_POST['userID']; // <--- FIX THIS?
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll();
		
		foreach($result as $row){
			// Get the companies the user works for
			// This will be used to create a dropdown list in HTML
			$company = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName']
								);
								
			// Set default booking display name and booking description
			$displayname = $row['displayname'];
			$bookingdescription = $row['bookingdescription'];
			$userID = $row['userID'];
		}

		// Get name and IDs for access level
		$sql = 'SELECT 	`meetingRoomID`,
						`name` 
				FROM 	`meetingroom`';
		$result = $pdo->query($sql);
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$meetingroom[] = array(
								'meetingRoomID' => $row['meetingRoomID'],
								'meetingRoomName' => $row['name']
								);
		}
			
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if (sizeOf($company)>1){
			$displayCompanySelect = TRUE;
		} else {
			$displayCompanySelect = FALSE;
			$companyID = $company['companyID'];
		}
		
		//Close the connection
		$pdo = null;
		
		// Set form variables to be ready for adding values
		$pageTitle = 'New Meeting Booking';
		$action = 'addform';
		$meetingroomname = '';
		$startDateTime = '';
		$endDateTime = '';
		$id = '';
		$button = 'Add booking';
		
		// We want a reset all fields button while adding a new meeting room
		$reset = 'reset';
		
		// Change form
		include 'form.html.php';
		exit();
		
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching user information from the database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// When admin has added the needed information and wants to add the booking
if (isset($_GET['addform']))
{
	// Add the booking to the database
	try
	{
		//Generate cancellation code
		$cancellationCode = generateCancellationCode();
		//TO-DO: Remove echo statement when testing is over
		echo 'cancellation code we generated on addform: ' . $cancellationCode . '<br />';
		
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `booking` SET
							`meetingRoomID` = :meetingRoomID,
							`userID` = :userID,
							`companyID` = :companyID,
							`displayName` = :displayName,
							`startDateTime` = :startDateTime,
							`endDateTime` = :endDateTime,
							`description` = :description,
							`cancellationCode` = :cancellationCode';

		$s = $pdo->prepare($sql);
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_POST['userID']);	// <-- NEED TO GET THIS FROM SOMEWHERE
		$s->bindValue(':companyID', $_POST['companyID']);
		$s->bindValue(':displayName', $_POST['displayName']);
		$s->bindValue(':startDateTime', $_POST['startDateTime']);
		$s->bindValue(':endDateTime', $_POST['endDateTime']);
		$s->bindValue(':description', $_POST['description']);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted booking to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

// Display booked meetings history list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 		b.`bookingID`,
						b.`companyID`,
						m.`name` 										AS BookedRoomName, 
						DATE_FORMAT(b.startDateTime, '%d %b %Y %T') 	AS StartTime, 
						DATE_FORMAT(b.endDateTime, '%d %b %Y %T') 		AS EndTime, 
						b.displayName 									AS BookedBy,
						(	SELECT `name` 
							FROM `company` 
							WHERE `companyID` = b.`companyID`
						)												AS BookedForCompany,
						u.firstName, 
						u.lastName, 
						u.email, 
						GROUP_CONCAT(c.`name` separator ', ') 			AS WorksForCompany, 
						b.description 									AS BookingDescription, 
						DATE_FORMAT(b.dateTimeCreated, '%d %b %Y %T') 	AS BookingWasCreatedOn, 
						DATE_FORMAT(b.actualEndDateTime, '%d %b %Y %T') AS BookingWasCompletedOn, 
						DATE_FORMAT(b.dateTimeCancelled, '%d %b %Y %T') AS BookingWasCancelledOn 
			FROM 		`booking` b 
			LEFT JOIN 	`meetingroom` m 
			ON 			b.meetingRoomID = m.meetingRoomID 
			LEFT JOIN 	`user` u 
			ON 			u.userID = b.userID 
			LEFT JOIN 	`employee` e 
			ON 			e.UserID = u.userID 
			LEFT JOIN 	`company` c 
			ON 			c.CompanyID = e.CompanyID 
			GROUP BY 	b.bookingID
			ORDER BY 	b.bookingID
			DESC";
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();

	//Close the connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching booking information from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

foreach ($result as $row)
{
	$bookings[] = array('id' => $row['bookingID'], 
						'BookedRoomName' => $row['BookedRoomName'],
						'StartTime' => $row['StartTime'],
						'EndTime' => $row['EndTime'],
						'BookedBy' => $row['BookedBy'],
						'BookedForCompany' => $row['BookedForCompany'],
						'BookingDescription' => $row['BookingDescription'],
						'firstName' => $row['firstName'],
						'lastName' => $row['lastName'],
						'email' => $row['email'],
						'WorksForCompany' => $row['WorksForCompany'],
						'BookingWasCreatedOn' => $row['BookingWasCreatedOn'],
						'BookingWasCompletedOn' => $row['BookingWasCompletedOn'],
						'BookingWasCancelledOn' => $row['BookingWasCancelledOn'],					
					);
}

// Create the booking information table in HTML
include_once 'bookings.html.php';

?>