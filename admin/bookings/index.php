<?php 
// This is the index file for the BOOKINGS folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear out session information
function clearBookingSessions(){
	
	unset($_SESSION['EditBookingInfoArray']);
	unset($_SESSION['EditBookingChangeUser']);
	unset($_SESSION['EditBookingUsersArray']);
	unset($_SESSION['EditBookingOriginalInfoArray']);
	unset($_SESSION['EditBookingMeetingRoomsArray']);	
	unset($_SESSION['EditBookingUserSearch']);
	unset($_SESSION['EditBookingSelectedNewUser']);
	unset($_SESSION['EditBookingSelectedACompany']);
	unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);
}

// Function to remember the user inputs in Edit Booking
function rememberEditBookingInputs(){
	session_start();
	if(isset($_SESSION['EditBookingInfoArray'])){
		$newValues = $_SESSION['EditBookingInfoArray'];

			// The user selected, if the booking is for another user
		if(isset($_POST['userID'])){
			$newValues['TheUserID'] = $_POST['userID'];
		}
			// The meeting room selected
		$newValues['TheMeetingRoomID'] = $_POST['meetingRoomID']; 
			// The company selected
		$newValues['TheCompanyID'] = $_POST['companyID'];
			// The user selected
		$newValues['BookedBy'] = $_POST['displayName'];
			// The booking description
		$newValues['BookingDescription'] = $_POST['description'];
			// The start time
		$newValues['StartTime'] = $_POST['startDateTime'];
			// The end time
		$newValues['EndTime'] = $_POST['endDateTime'];
		
		$_SESSION['EditBookingInfoArray'] = $newValues;			
	}
}

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
	
	$_SESSION['BookingUserFeedback'] = "Successfully removed the booking";
	
	// Add a log event that a booking was deleted
	try
	{
		session_start();

		// Save a description with information about the booking that was removed
		$description = "N/A";
		if(isset($_POST['UserInfo']) AND isset($_POST['MeetingInfo'])){
			$description = 'The booking made by ' . $_POST['UserInfo'] . ' for the meeting room ' .
			$_POST['MeetingInfo'] . ' was deleted by: ' . $_SESSION['LoggedInUserName'];
		} else {
			$description = 'A booking was deleted by: ' . $_SESSION['LoggedInUserName'];
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Removed'
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
	
	
	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to cancel a scheduled booked meeting (instead of deleting)
if (isset($_POST['action']) and $_POST['action'] == 'Cancel')
{
	// Update cancellation date for selected booked meeting in database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'UPDATE 	`booking` SET 
						`dateTimeCancelled` = CURRENT_TIMESTAMP 
				WHERE 	`bookingID` = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['id']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error updating selected booked meeting to be cancelled: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	$_SESSION['BookingUserFeedback'] = "Successfully cancelled the booking";
	
		// Add a log event that a booking was cancelled
	try
	{
		session_start();

		// Save a description with information about the booking that was cancelled
		$description = "N/A";
		if(isset($_POST['UserInfo']) AND isset($_POST['MeetingInfo'])){
			$description = 'The booking made by ' . $_POST['UserInfo'] . ' for the meeting room ' .
			$_POST['MeetingInfo'] . ' was cancelled by: ' . $_SESSION['LoggedInUserName'];
		} else {
			$description = 'A booking was cancelled by: ' . $_SESSION['LoggedInUserName'];
		}
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Cancelled'
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
	
	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add a booked meeting to the database
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == "Create Booking")
{
	try
	{
		session_start();
		// Retrieve the user's default displayname and bookingdescription
		// if they have any.
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT	u.`bookingdescription`, 
						u.`displayname`,
						c.`companyID`,
						c.`name` 					AS companyName
				FROM 	`user` u
				JOIN 	`employee` e
				ON 		e.userID = u.userID
				JOIN	`company` c
				ON 		c.companyID = e.companyID
				WHERE 	u.`userID` = :userID';
			
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
		$s->execute();
		
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll();
		
		$displayName = '';
		$description = '';
		
		foreach($result as $row){		
			// Get the companies the user works for
			// This will be used to create a dropdown list in HTML
			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName']
								);
				
				
			// Set default booking display name and booking description
			if($row['displayname']!=NULL){
				$displayName = $row['displayname'];
			}

			if($row['bookingdescription']!=NULL){
				$description = $row['bookingdescription'];
			}

		}		
	
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isset($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$displayCompanySelect = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$displayCompanySelect = FALSE;
				$companyID = $company[0]['companyID'];
			}
		} else{
			// User is NOT in a company
			$displayCompanySelect = FALSE;
			$companyID = NULL;
		}
		
		// Get name and IDs for meeting rooms
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
		
		// Remember the meeting room info for creating a log event later
		session_start();
		unset($_SESSION['AddBookingMeetingRooms']);
		$_SESSION['AddBookingMeetingRooms'] = $meetingroom;		
		
		//Close the connection
		$pdo = null;
		
		// Set form variables to be ready for adding values
		$meetingroomname = '';
		$startDateTime = '';
		$endDateTime = '';
		$id = '';
		
		// Change form
		include 'addbooking.html.php';
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
if (isset($_POST['action']) AND $_POST['action'] == "Add booking")
{
	// Add the booking to the database
	try
	{	
		session_start();
	
		if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
			$_POST['companyID'] != ''){
			$companyID = $_POST['companyID'];
		} else {
			$companyID = NULL;
		}
	
		//Generate cancellation code
		$cancellationCode = generateCancellationCode();
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `booking` 
				SET			`meetingRoomID` = :meetingRoomID,
							`userID` = :userID,
							`companyID` = :companyID,
							`displayName` = :displayName,
							`startDateTime` = :startDateTime,
							`endDateTime` = :endDateTime,
							`description` = :description,
							`cancellationCode` = :cancellationCode';

		$s = $pdo->prepare($sql);
		
		$startDateTime = correctDatetimeFormat($_POST['startDateTime']);
		$endDateTime = correctDatetimeFormat($_POST['endDateTime']);
		
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $_POST['displayName']);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':description', $_POST['description']);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();

		session_start();
		unset($_SESSION['lastBookingID']);
		$_SESSION['lastBookingID'] = $pdo->lastInsertId();
		
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
	
	$_SESSION['BookingUserFeedback'] = "Successfully created the booking.";
	
	// Add a log event that a user has been created
	try
	{
		session_start();

		// Get meeting room name
		$MeetingRoomName = 'N/A';
		foreach ($_SESSION['AddBookingMeetingRooms'] AS $room){
			if($room['meetingRoomID'] == $_POST['meetingRoomID']){
				$MeetingRoomName = $room['meetingRoomName'];
				break;
			}
		}
		unset($_SESSION['AddBookingMeetingRooms']);
		
		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$_POST['startDateTime'] . ' to ' . $_POST['endDateTime'];
		
		// Save a description with information about the booking that was created
		$description = 'A booking was created for the meeting room ' . $meetinginfo . 
		' by: ' . $_SESSION['LoggedInUserName'];
		
		if(isset($_SESSION['lastBookingID'])){
			$lastBookingID = $_SESSION['lastBookingID'];
			unset($_SESSION['lastBookingID']);				
		}

		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Booking Created'
											),
							`userID` = :UserID,
							`meetingRoomID` = :MeetingRoomID,
							`bookingID` = :BookingID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $description);
		$s->bindValue(':BookingID', $lastBookingID);
		$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':UserID', $_SESSION['LoggedInUserID']);
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
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

// if admin wants to edit a booking, we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isset($_SESSION['refreshEditBooking']) AND $_SESSION['refreshEditBooking']))
{
	// TO-DO: Remember changed values on "Change User"-button press	
	
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshEditBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshEditBooking']);	
		
		// Set the information back to what it was before the refresh
				// The user search string
		if(isset($_SESSION['EditBookingUserSearch'])){
			$usersearchstring = $_SESSION['EditBookingUserSearch'];
			unset($_SESSION['EditBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isset($_SESSION['EditBookingUsersArray'])){
			$users = $_SESSION['EditBookingUsersArray'];
					
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['EditBookingInfoArray']['TheUserID'];	
		
	} else {
		// Get information from database again on the selected booking
		if(!isset($_SESSION['EditBookingMeetingRoomsArray'])){
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				
				// Get booking information
				$pdo = connect_to_db();
				// Get name and IDs for meeting rooms
				$sql = 'SELECT 	`meetingRoomID`,
								`name` 
						FROM 	`meetingroom`';
				$result = $pdo->query($sql);
					
				//Close connection
				$pdo = null;
				
				// Get the rows of information from the query
				// This will be used to create a dropdown list in HTML
				foreach($result as $row){
					$meetingroom[] = array(
										'meetingRoomID' => $row['meetingRoomID'],
										'meetingRoomName' => $row['name']
										);
				}		
				
				$_SESSION['EditBookingMeetingRoomsArray'] = $meetingroom;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching meeting room details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();		
			}
		}
		
		if(!isset($_SESSION['EditBookingInfoArray'])){
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				
				// Get booking information
				$pdo = connect_to_db();
				$sql = "SELECT 		b.`bookingID`									AS TheBookingID,
									b.`companyID`									AS TheCompanyID,
									b.`meetingRoomID`								AS TheMeetingRoomID,
									m.`name` 										AS BookedRoomName, 
									b.startDateTime 								AS StartTime, 
									b.endDateTime 									AS EndTime, 
									b.description 									AS BookingDescription,
									b.displayName 									AS BookedBy,
									(	
										SELECT `name` 
										FROM `company` 
										WHERE `companyID` = TheCompanyID
									)												AS BookedForCompany,
									u.`userID`										AS TheUserID, 
									u.`firstName`									AS UserFirstname,
									u.`lastName`									AS UserLastname,
									u.`email`										AS UserEmail,
									u.`displayName` 								AS UserDefaultDisplayName,
									u.`bookingDescription`							AS UserDefaultBookingDescription
						FROM 		`booking` b 
						LEFT JOIN 	`meetingroom` m 
						ON 			b.meetingRoomID = m.meetingRoomID 
						LEFT JOIN 	`company` c 
						ON 			b.CompanyID = c.CompanyID
						LEFT JOIN 	`user` u
						ON 			b.`userID` = u.`userID`
						WHERE 		b.`bookingID` = :BookingID
						GROUP BY 	b.`bookingID`";
				$s = $pdo->prepare($sql);
				$s->bindValue(':BookingID', $_POST['id']);
				$s->execute();
								
				//Close connection
				$pdo = null;
			}
			catch (PDOException $e)
			{
				$error = 'Error fetching booking details: ' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();		
			}
			
			// Create an array with the row information we retrieved		
			$_SESSION['EditBookingInfoArray'] = $s->fetch();
			$_SESSION['EditBookingOriginalInfoArray'] = $_SESSION['EditBookingInfoArray'];
		}	
		
		// Set the correct information on form call
		$usersearchstring = '';
		$users = Null;
		$SelectedUserID = $_SESSION['EditBookingInfoArray']['TheUserID'];
	}

		// Check if we need a company select for the booking
	try
	{		
		session_start();
		// We want the companies the user works for to decide if we need to
		// have a dropdown select or just a fixed value (with 0 or 1 company)
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT	c.`companyID`,
						c.`name` 					AS companyName
				FROM 	`user` u
				JOIN 	`employee` e
				ON 		e.userID = u.userID
				JOIN	`company` c
				ON 		c.companyID = e.companyID
				WHERE 	u.`userID` = :userID';
			
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $SelectedUserID);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$result = $s->fetchAll();
			
		foreach($result as $row){		
			// Get the companies the user works for
			// This will be used to create a dropdown list in HTML
			$company[] = array(
								'companyID' => $row['companyID'],
								'companyName' => $row['companyName']
								);
		}
			
		$pdo = null;
				
		// We only need to allow the user a company dropdown selector if they
		// are connected to more than 1 company.
		// If not we just store the companyID in a hidden form field
		if(isset($company)){
			if (sizeOf($company)>1){
				// User is in multiple companies
				
				$_SESSION['EditBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['EditBookingSelectedACompany'] = TRUE;
				unset($_SESSION['EditBookingDisplayCompanySelect']);
				$_SESSION['EditBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['EditBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company
			
			$_SESSION['EditBookingSelectedACompany'] = TRUE;
			unset($_SESSION['EditBookingDisplayCompanySelect']);
			$_SESSION['EditBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['EditBookingInfoArray']['BookedForCompany'] = "";
		}		
	}
	catch(PDOException $e)
	{
		$error = 'Error fetching user details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();					
	}
	
	// Set the correct information
	$row = $_SESSION['EditBookingInfoArray'];
	$original = $_SESSION['EditBookingOriginalInfoArray'];
		// Changed user
	if(isset($_SESSION['EditBookingSelectedNewUser'])){
		
		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];
				
				$_SESSION['EditBookingDefaultDisplayNameForNewUser'] = $user['displayName'];
				$_SESSION['EditBookingDefaultBookingDescriptionForNewUser'] = $user['bookingDescription'];
				break;
			}
		}
	} else {
		$_SESSION['EditBookingDefaultDisplayNameForNewUser'] = $original['UserDefaultDisplayName'];
		$_SESSION['EditBookingDefaultBookingDescriptionForNewUser'] = $original['UserDefaultBookingDescription'];
	}
		// Changed company
	foreach($company AS $cmp){
		if($cmp['companyID'] == $row['TheCompanyID']){
			$row['BookedForCompany'] = $cmp['companyName'];
			break;
		}
	}
		// Edited inputs
	$bookingID = $row['TheBookingID'];
	$companyName = $row['BookedForCompany'];
	$selectedCompanyID = $row['TheCompanyID'];
	$companyID = $row['TheCompanyID'];
	//	userID has been set earlier
	$meetingroom = $_SESSION['EditBookingMeetingRoomsArray'];
	$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	$startDateTime = $row['StartTime'];
	$endDateTime = $row['EndTime'];
	$displayName = $row['BookedBy'];
	$description = $row['BookingDescription'];
	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];
		// Original values	
	$originalStartDateTime = $original['StartTime'];
	$originalEndDateTime = $original['EndTime'];
	if($original['BookedForCompany']!=NULL){
		$originalCompanyName = $original['BookedForCompany'];
	}
	$originalMeetingRoomName = $original['BookedRoomName'];
	$originalDisplayName = $original['BookedBy'];
	$originalBookingDescription = $original['BookingDescription'];
	$originalUserInformation = 	$original['UserLastname'] . ', ' . $original['UserFirstname'] . 
								' - ' . $original['UserEmail'];
	
	// Change to the actual form we want to use
	include 'editbooking.html.php';
	exit();
}

session_start();
// Admin wants to change the user the booking is reserved for
// We need to get a list of all active users
if((isset($_POST['action']) AND $_POST['action'] == "Change User") OR 
	(isset($_SESSION['refreshEditBookingChangeUser'])) AND $_SESSION['refreshEditBookingChangeUser']){
	
	if(isset($_SESSION['refreshEditBookingChangeUser']) AND $_SESSION['refreshEditBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshEditBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['EditBookingUsersArray']);	
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isset($_POST['action']) AND $_POST['action'] == "Change User"){
		rememberEditBookingInputs();
	}

	$usersearchstring = "";
	
	if(isset($_SESSION['EditBookingUserSearch'])){
		$usersearchstring = $_SESSION['EditBookingUserSearch'];
	}	
	
	if(!isset($_SESSION['EditBookingUsersArray'])){
		// Get all active users and their default booking information
		try
		{
			$pdo = connect_to_db();
			$sql = "SELECT 	`userID`, 
							`firstname`, 
							`lastname`, 
							`email`,
							`displayname`,
							`bookingdescription`
					FROM 	`user`
					WHERE 	`isActive` > 0";
		
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
									'userID' => $row['userID'],
									'lastName' => $row['lastname'],
									'firstName' => $row['firstname'],
									'email' => $row['email'],
									'userInformation' => $row['lastname'] . ', ' . $row['firstname'] . ' - ' . $row['email'],
									'displayName' => $row['displayname'],
									'bookingDescription' => $row['bookingdescription']
									);
			}
			session_start();
			$_SESSION['EditBookingUsersArray'] = $users;
			
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error fetching user details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();		
		}
	} else {
		$users = $_SESSION['EditBookingUsersArray'];
	}

	$_SESSION['refreshEditBooking'] = TRUE;
	$_SESSION['EditBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// Admin confirms what user he wants the booking to be for.
if(isset($_POST['action']) AND $_POST['action'] == "Select This User"){
	
	// We haven't set the company if we are changing the user.
	unset($_SESSION['EditBookingSelectedACompany']);

	// We no longer need to be able to change the user
	unset($_SESSION['EditBookingChangeUser']);
	
	// Remember that we've selected a new user
	$_SESSION['EditBookingSelectedNewUser'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

//	Admin wants to change the company the booking is for (after having already selected it)
if(isset($_POST['action']) AND $_POST['action'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['EditBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isset($_POST['action']) AND $_POST['action'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['EditBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

//TO-DO: Add a "Select This Company" aswell!

session_start();
// If admin is editing a booking and wants to limit the users shown by searching
if(isset($_SESSION['EditBookingChangeUser']) AND isset($_POST['action']) AND $_POST['action'] == "Search"){
	
	// Let's remember what was selected and searched for
		// The user search string
	$_SESSION['EditBookingUserSearch'] = $_POST['usersearchstring'];

	rememberEditBookingInputs();
	
	// Get the new users
	$_SESSION['refreshEditBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// If admin wants to get the default values for the user's display name
if(isset($_POST['action']) AND $_POST['action'] == "Get Default Display Name"){
	  
	session_start();
	
	$displayName = $_SESSION['EditBookingDefaultDisplayNameForNewUser'];
	if(isset($_SESSION['EditBookingInfoArray'])){
		$newValues = $_SESSION['EditBookingInfoArray'];

		if($displayName != ""){
			if($displayName != $newValues['BookedBy']){
				
				if(isset($_POST['userID'])){
					$newValues['TheUserID'] = $_POST['userID'];
				}
					// The meeting room selected
				$newValues['TheMeetingRoomID'] = $_POST['meetingRoomID']; 
					// The company selected
				$newValues['TheCompanyID'] = $_POST['companyID'];
					// The user selected
				$newValues['BookedBy'] = $displayName;
					// The booking description
				$newValues['BookingDescription'] = $_POST['description'];
					// The start time
				$newValues['StartTime'] = $_POST['startDateTime'];
					// The end time
				$newValues['EndTime'] = $_POST['endDateTime'];
				
				$_SESSION['EditBookingInfoArray'] = $newValues;	

				unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);				
			} else {
				// Description was already the default booking description
				$_SESSION['EditBookingError'] = "This is already the user's default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['EditBookingError'] = "This user has no default display name.";
		}		
	}
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to get the default values for the user's booking description
if(isset($_POST['action']) AND $_POST['action'] == "Get Default Booking Description"){
	
	session_start();
	
	$bookingDescription = $_SESSION['EditBookingDefaultBookingDescriptionForNewUser'];
	if(isset($_SESSION['EditBookingInfoArray'])){
		$newValues = $_SESSION['EditBookingInfoArray'];

		if($bookingDescription != ""){
			if($bookingDescription != $newValues['BookingDescription']){
				
				if(isset($_POST['userID'])){
					$newValues['TheUserID'] = $_POST['userID'];
				}
					// The meeting room selected
				$newValues['TheMeetingRoomID'] = $_POST['meetingRoomID']; 
					// The company selected
				$newValues['TheCompanyID'] = $_POST['companyID'];
					// The user selected
				$newValues['BookedBy'] = $_POST['displayName'];
					// The booking description
				$newValues['BookingDescription'] = $bookingDescription;
					// The start time
				$newValues['StartTime'] = $_POST['startDateTime'];
					// The end time
				$newValues['EndTime'] = $_POST['endDateTime'];
				
				$_SESSION['EditBookingInfoArray'] = $newValues;	

				unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);			
			} else {
				// Description was already the default booking description
				$_SESSION['EditBookingError'] = "This is already the user's default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['EditBookingError'] = "This user has no default booking description.";
		}
	}
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to change the values back to the original values
if (isset($_POST['action']) AND $_POST['action'] == "Reset"){

	$_SESSION['EditBookingInfoArray'] = $_SESSION['EditBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel Edit'){
	// Doesn't actually need any code to work, since it happends automatically when a submit
	// occurs. *it* being doing the normal startup code.
	// Might be useful for something later?
	$_SESSION['BookingUserFeedback'] = "You cancelled your booking editing.";
}


// If admin wants to update the booking information after editing
if(isset($_POST['action']) AND $_POST['action'] == "Finish Edit")
{

	// Check if all values are valid before doing anything
		// TO-DO:
		// Are all inputs filled out?
		
		// Are the filled out values actually the correct format?

		
	// Check if any values actually changed. If not, we don't need to bother the database
	$numberOfChanges = 0;
	$checkIfTimeslotIsAvailable = FALSE;
	$newMeetingRoom = FALSE;
	$newStartTime = FALSE;
	$newEndTime = FALSE;
	$originalValue = $_SESSION['EditBookingOriginalInfoArray'];
	
	if($_POST['startDateTime'] != $originalValue['StartTime']){
		$numberOfChanges++;
		$newStartTime = TRUE;
	}
	if($_POST['endDateTime'] != $originalValue['EndTime']){
		$numberOfChanges++;
		$newEndTime = TRUE;
	}	
	if($_POST['companyID'] != $originalValue['TheCompanyID']){
		$numberOfChanges++;
	}
	if($_POST['displayName'] != $originalValue['BookedBy']){
		$numberOfChanges++;
	}	
	if($_POST['description'] != $originalValue['BookingDescription']){
		$numberOfChanges++;
	}	
	if($_POST['meetingRoomID'] != $originalValue['TheMeetingRoomID']){
		$numberOfChanges++;
		$newMeetingRoom = TRUE;
	}
	if(isset($_POST['userID']) AND $_POST['userID'] != $originalValue['TheUserID']){
		$numberOfChanges++;
	}	

	if($numberOfChanges == 0){
		// There were no changes made. Go back to booking overview
		$_SESSION['BookingUserFeedback'] = "No changes were made to the booking.";
		header('Location: .');
		exit();	
	}
	
	// Check if we need to check the timeslot or if we can just update the booking
		// If we changed the meeting room
	if($newMeetingRoom){
		$checkIfTimeslotIsAvailable = TRUE;
	}
		// If we set the start time earlier than before or
		// If we set the start time later than the previous end time
	if( ($newStartTime AND $_POST['startDateTime'] < $originalValue['StartTime']) OR 
		($newStartTime AND $_POST['startDateTime'] >= $originalValue['EndTime'])){
		$checkIfTimeslotIsAvailable = TRUE;
	}
		// If we set the end time later than before or
		// If we set the end time earlier than the previous start time
	if( ($newEndTime AND $_POST['endDateTime'] > $originalValue['EndTime']) OR 
		($newEndTime AND $_POST['endDateTime'] <= $originalValue['StartTime'])){
		$checkIfTimeslotIsAvailable = TRUE;
	}
	
	// Check if the timeslot is taken for the selected meeting room
	// TO-DO: UNTESTED
	if($checkIfTimeslotIsAvailable){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql =	" 	SELECT 	COUNT(*)
						FROM 	`booking`
						WHERE 	`meetingRoomID` = :MeetingRoomID
						AND		`startDateTime` = :StartTime
						AND 	`endDateTime` = :EndTime
						LIMIT 	1";
			$s = $pdo->prepare($sql);
			
			$startTime = correctDatetimeFormat($_POST['startDateTime']);
			$endTime = correctDatetimeFormat($_POST['endDateTime']);
			
			$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
			$s->bindValue(':StartTime', $startTime);
			$s->bindValue(':EndTime', $endTime);			
			$pdo = null;
		}
		catch(PDOException $e)
		{
			$error = 'Error checking if booking time is available: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		// Check if we got any hits, if so the timeslot is already taken
		$row = $s->fetch();
		if ($row[0] > 0){
			// Timeslot was taken
			$_SESSION['EditBookingError'] = "The booking couldn't be made. The timeslot is already taken for this meeting room.";
			if(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
				$_SESSION['refreshEditBookingChangeUser'] = TRUE;				
			} else {
				$_SESSION['refreshEditBooking'] = TRUE;	
			}
			header('Location: .');
			exit();				
		}
	}
	
	if($_POST['companyID'] == ""){
		$TheCompanyID = NULL;
	} else {
		$TheCompanyID = $_POST['companyID'];
	}
	
	// Update booking information because values have changed!
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "UPDATE	`booking`
				SET 	`meetingRoomID` = :meetingRoomID,
						`userID` = :userID,
						`companyID` = :companyID,
						`startDateTime` = :startDateTime,
						`endDateTime` = :endDateTime,
						`displayName` = :displayName,
						`description` = :description
				WHERE	`bookingID` = :BookingID";
		$s = $pdo->prepare($sql);
		
		$startDateTime = correctDatetimeFormat($_POST['startDateTime']);
		$endDateTime = correctDatetimeFormat($_POST['endDateTime']);
		
		$s->bindValue(':BookingID', $_POST['bookingID']);
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_POST['userID']);
		$s->bindValue(':companyID', $TheCompanyID);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':displayName', $_POST['displayName']);
		$s->bindValue(':description', $_POST['description']);
		
		$s->execute();
		
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error updating booking information in the database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	$_SESSION['BookingUserFeedback'] = "Successfully updated the booking information!";
	clearBookingSessions();
	
	// Load booking history list webpage with the updated booking information
	header('Location: .');
	exit();	
}

// We're not doing any adding or editing anymore, clear all remembered values
clearBookingSessions();

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
						(	
							SELECT `name` 
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
	// Make datetime correct formats for comparing them
	$datetimeNow = getDatetimeNow();
	$datetimeEndWrongFormat = $row['EndTime'];
	$datetimeEnd = correctDatetimeFormatForBooking($datetimeEndWrongFormat);
	
	// Describe the status of the booking based on what info is stored in the database
	// If not finished and not cancelled = active
	// If meeting time has passed and finished time has updated (and not been cancelled) = completed
	// If cancelled = cancelled
	// If meeting time has passed and finished time has NOT updated (and not been cancelled) = Ended without updating
	// If none of the above = Unknown
	// TO-DO: CHECK IF THIS MAKES SENSE!
	if(			$row['BookingWasCompletedOn'] == null AND $row['BookingWasCancelledOn'] == null AND 
				$datetimeNow < $datetimeEnd ) {
		$status = 'Active';
	} elseif(	$row['BookingWasCompletedOn'] != null AND $row['BookingWasCancelledOn'] == null){
		$status = 'Completed';
	} elseif(	$row['BookingWasCompletedOn'] == null AND $row['BookingWasCancelledOn'] != null AND
				$row['StartTime'] > $row['BookingWasCancelledOn']){
		$status = 'Cancelled';
	} elseif(	$row['BookingWasCompletedOn'] != null AND $row['BookingWasCancelledOn'] != null AND
				$row['BookingWasCompletedOn'] > $row['BookingWasCancelledOn'] ){
		$status = 'Ended Early';
	} elseif(	$row['BookingWasCompletedOn'] != null AND $row['BookingWasCancelledOn'] != null AND
				$row['BookingWasCompletedOn'] < $row['BookingWasCancelledOn'] ){
		$status = 'Cancelled after Completion';
	} elseif(	$row['BookingWasCompletedOn'] == null AND $row['BookingWasCancelledOn'] == null AND 
				$datetimeNow > $datetimeEnd){
		$status = 'Ended without updating database';
	} elseif(	$row['BookingWasCompletedOn'] == null AND $row['BookingWasCancelledOn'] != null AND 
				$row['EndTime'] < $row['BookingWasCancelledOn']){
		$status = 'Cancelled after meeting should have been Completed';
	} else {
		$status = 'Unknown';
	}
	
	$userinfo = $row['lastName'] . ', ' . $row['firstName'] . ' - ' . $row['email'];
	$meetinginfo = 	$row['BookedRoomName'] . ' for the timeslot: ' . $row['StartTime'] . 
					' to ' . $row['EndTime'];
	
	
	$bookings[] = array('id' => $row['bookingID'],
						'BookingStatus' => $status,
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
						'UserInfo' => $userinfo,
						'MeetingInfo' => $meetinginfo
					);
}

// Create the booking information table in HTML
include_once 'bookings.html.php';

?>