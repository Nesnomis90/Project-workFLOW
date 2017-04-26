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
	
	unset($_SESSION['AddBookingInfoArray']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingUsersArray']);
	unset($_SESSION['AddBookingOriginalInfoArray']);
	unset($_SESSION['AddBookingMeetingRoomsArray']);	
	unset($_SESSION['AddBookingUserSearch']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['AddBookingDefaultBookingDescriptionForNewUser']);	
	
}

// Function to remember the user inputs in Edit Booking
function rememberEditBookingInputs(){
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

// Function to remember the user inputs in Add Booking
function rememberAddBookingInputs(){
	if(isset($_SESSION['AddBookingInfoArray'])){
		$newValues = $_SESSION['AddBookingInfoArray'];

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
		
		$_SESSION['AddBookingInfoArray'] = $newValues;			
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
		$sql = 'UPDATE 	`booking` 
				SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP 
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



// EDIT CODE SNIPPET START//


// if admin wants to edit a booking, we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isset($_SESSION['refreshEditBooking']) AND $_SESSION['refreshEditBooking']))
{
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

// Admin wants to change the user the booking is reserved for
// We need to get a list of all active users
if((isset($_POST['edit']) AND $_POST['edit'] == "Change User") OR 
	(isset($_SESSION['refreshEditBookingChangeUser'])) AND $_SESSION['refreshEditBookingChangeUser']){
	
	if(isset($_SESSION['refreshEditBookingChangeUser']) AND $_SESSION['refreshEditBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshEditBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['EditBookingUsersArray']);	
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isset($_POST['edit']) AND $_POST['edit'] == "Change User"){
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Select This User"){
	
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['EditBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isset($_POST['edit']) AND $_POST['edit'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['EditBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberEditBookingInputs();
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin is editing a booking and wants to limit the users shown by searching
if(isset($_SESSION['EditBookingChangeUser']) AND isset($_POST['edit']) AND $_POST['edit'] == "Search"){
	
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Display Name"){
	  
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
if(isset($_POST['edit']) AND $_POST['edit'] == "Get Default Booking Description"){
	
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
if (isset($_POST['edit']) AND $_POST['edit'] == "Reset"){

	$_SESSION['EditBookingInfoArray'] = $_SESSION['EditBookingOriginalInfoArray'];
	
	$_SESSION['refreshEditBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isset($_POST['edit']) AND $_POST['edit'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your booking editing.";
}


// If admin wants to update the booking information after editing
if(isset($_POST['edit']) AND $_POST['edit'] == "Finish Edit")
{

	// Start validating user inputs
	$invalidInput = FALSE;
	
		// Are values actually filled in?
	if($_POST['startDateTime'] == "" AND $_POST['endDateTime'] == ""){
		
		$_SESSION['EditBookingError'] = "You need to fill in a start and end time for your booking.";	
		$invalidInput = TRUE;
	} elseif($_POST['startDateTime'] != "" AND $_POST['endDateTime'] == "") {
		$_SESSION['EditBookingError'] = "You need to fill in an end time for your booking.";	
		$invalidInput = TRUE;		
	} elseif($_POST['startDateTime'] == "" AND $_POST['endDateTime'] != ""){
		$_SESSION['EditBookingError'] = "You need to fill in a start time for your booking.";	
		$invalidInput = TRUE;		
	}
		// DateTime formats
	// TO-DO: Check if stuff is valid when the proper datetime user input submit has been decided
		
		// DisplayName
			// Has to be less than 255 chars (MySQL - VARCHAR 255)
	$dspname = $_POST['displayName'];
	$dspnameLength = strlen(utf8_decode($dspname));
	$dspnameMaxLength = 255; // TO-DO: Adjust if needed.
	if($dspnameLength > $dspnameMaxLength AND !$invalidInput){
		
		$_SESSION['EditBookingError'] = "The displayName submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// BookingDescription
			// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)
	$bknDscrptn = $_POST['description'];
	$bknDscrptnLength = strlen(utf8_decode($bknDscrptn));
	$bknDscrptnMaxLength = 500; // TO-DO: Adjust if needed.
	if($bknDscrptnLength > $bknDscrptnMaxLength AND !$invalidInput){
		
		$_SESSION['EditBookingError'] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	
	
	$startDateTime = correctDatetimeFormat($_POST['startDateTime']);
	$endDateTime = correctDatetimeFormat($_POST['endDateTime']);

	$timeNow = getDatetimeNow();
	
	if($startDateTime > $endDateTime){
		// End time can't be before the start time
		
		$_SESSION['EditBookingError'] = "The start time can't be later than the end time. Please select a new start time or end time.";
		$invalidInput = TRUE;
	
	}
	
	if($startDateTime < $timeNow AND !$invalidInput){
		// You can't book stuff in the past.
		
		$_SESSION['EditBookingError'] = "The start time you selected is already over. Select a new start time.";
		$invalidInput = TRUE;
	}
	
	if($endDateTime < $timeNow AND !$invalidInput){
		// You can't book stuff in the past.
		
		$_SESSION['EditBookingError'] = "The end time you selected is already over. Select a new end time.";
		$invalidInput = TRUE;	
	}	
	
	if($invalidInput){
		
		rememberEditBookingInputs();
		// Refresh.
		if(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']){
			$_SESSION['refreshEditBookingChangeUser'] = TRUE;				
		} else {
			$_SESSION['refreshEditBooking'] = TRUE;	
		}
		header('Location: .');
		exit();			
	}		
		
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
	
		$startTime = correctDatetimeFormat($_POST['startDateTime']);
		$endTime = correctDatetimeFormat($_POST['endDateTime']);
		$oldStartTime = $originalValue['StartTime'];
		$oldEndTime = $originalValue['EndTime'];
	
		// If we set the start time earlier than before or
		// If we set the start time later than the previous end time
	if( ($newStartTime AND $startTime < $oldStartTime) OR 
		($newStartTime AND $startTime >= $oldEndTime)){
		$checkIfTimeslotIsAvailable = TRUE;
	}
		// If we set the end time later than before or
		// If we set the end time earlier than the previous start time
	if( ($newEndTime AND $endTime > $oldEndTime) OR 
		($newEndTime AND $endTime <= $oldStartTime)){
		$checkIfTimeslotIsAvailable = TRUE;
	}
	
	// Check if the timeslot is taken for the selected meeting room
	// and ignore our own booking since it's the one we're editing
	if($checkIfTimeslotIsAvailable){
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql =	" 	SELECT 	COUNT(*)
						FROM 	`booking`
						WHERE 	`meetingRoomID` = :MeetingRoomID
						AND		
						(		
								(
									`startDateTime` 
									BETWEEN :StartTime
									AND :EndTime
								) 
						OR 		(
									`endDateTime`
									BETWEEN :StartTime
									AND :EndTime
								)
						)
						AND		`bookingID` != :BookingID";
			$s = $pdo->prepare($sql);
			
			$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
			$s->bindValue(':StartTime', $startTime);
			$s->bindValue(':EndTime', $endTime);
			$s->bindValue(':BookingID', $_POST['bookingID']);
			$s->execute();
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
			rememberEditBookingInputs();
			
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
	
	if(	isset($_POST['companyID']) AND $_POST['companyID'] != NULL AND 
		$_POST['companyID'] != ''){
		$companyID = $_POST['companyID'];
	} else {
		$companyID = NULL;
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


// EDIT CODE SNIPPET END //







// ADD CODE SNIPPET START //

// If admin wants to add a booked meeting to the database
// we load a new html form
if (	(isset($_POST['action']) AND $_POST['action'] == "Create Booking") OR 
		(isset($_SESSION['refreshAddBooking']) AND $_SESSION['refreshAddBooking']))
{
	
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshAddBooking'])){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddBooking']);	
		
		// Set the information back to what it was before the refresh
				// The user search string
		if(isset($_SESSION['AddBookingUserSearch'])){
			$usersearchstring = $_SESSION['AddBookingUserSearch'];
			unset($_SESSION['AddBookingUserSearch']);
		} else {
			$usersearchstring = "";
		}
				// The user dropdown select options
		if(isset($_SESSION['AddBookingUsersArray'])){
			$users = $_SESSION['AddBookingUsersArray'];
					
		}
			// The selected user in the dropdown select	
		$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];	
	
		} else {
			// Get information from database on booking information user can choose between
			if(!isset($_SESSION['AddBookingMeetingRoomsArray'])){
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
					
					$_SESSION['AddBookingMeetingRoomsArray'] = $meetingroom;
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching meeting room details: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();		
				}
			}
		
			if(!isset($_SESSION['AddBookingInfoArray'])){
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
					
					// Get the logged in user's default booking information
					$pdo = connect_to_db();
					$sql = 'SELECT	`bookingdescription`, 
									`displayname`,
									`firstName`,
									`lastName`,
									`email`
							FROM 	`user`
							WHERE 	`userID` = :userID
							LIMIT 	1';
						
					$s = $pdo->prepare($sql);
					$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
					$s->execute();
					
					// Create an array with the row information we retrieved
					$result = $s->fetch();
						
					// Set default booking display name and booking description
					if($result['displayname']!=NULL){
						$displayName = $result['displayname'];
					}

					if($result['bookingdescription']!=NULL){
						$description = $result['bookingdescription'];
					}
					
					if($result['firstName']!=NULL){
						$firstname = $result['firstName'];
					}		
					
					if($result['lastName']!=NULL){
						$lastname = $result['lastName'];
					}	
					
					if($result['email']!=NULL){
						$email = $result['email'];
					}					
		
					//Close connection
					$pdo = null;
				}
				catch (PDOException $e)
				{
					$error = 'Error fetching default user details: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();		
				}	
			
				// Create an array with the row information we want to use	
				$_SESSION['AddBookingInfoArray'][] = array(
															'TheCompanyID' => '',
															'TheMeetingRoomID' => '',
															'StartTime' => '',
															'EndTime' => '',
															'BookingDescription' => '',
															'BookedBy' => '',
															'BookedForCompany' => '',
															'TheUserID' => '',
															'UserFirstname' => '',
															'UserLastname' => '',
															'UserEmail' => '',
															'UserDefaultDisplayName' => '',
															'UserDefaultBookingDescription' => ''
														);			
				$_SESSION['AddBookingInfoArray']['UserDefaultBookingDescription'] = $description;
				$_SESSION['AddBookingInfoArray']['UserDefaultDisplayName'] = $displayName;
				$_SESSION['AddBookingInfoArray']['UserFirstname'] = $firstname;	
				$_SESSION['AddBookingInfoArray']['UserLastname'] = $lastname;	
				$_SESSION['AddBookingInfoArray']['UserEmail'] = $email;	
				$_SESSION['AddBookingInfoArray']['TheUserID'] = $_SESSION['LoggedInUserID'];
				
				$_SESSION['AddBookingOriginalInfoArray'] = $_SESSION['AddBookingInfoArray'];
			}
			
			// Set the correct information on form call
			$usersearchstring = '';
			$users = Null;
			$SelectedUserID = $_SESSION['AddBookingInfoArray']['TheUserID'];
	}

		// Check if we need a company select for the booking
	try
	{		
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
				
				$_SESSION['AddBookingDisplayCompanySelect'] = TRUE;
			} elseif(sizeOf($company) == 1) {
				// User is in ONE company
				
				$_SESSION['AddBookingSelectedACompany'] = TRUE;
				unset($_SESSION['AddBookingDisplayCompanySelect']);
				$_SESSION['AddBookingInfoArray']['TheCompanyID'] = $company[0]['companyID'];
				$_SESSION['AddBookingInfoArray']['BookedForCompany'] = $company[0]['companyName'];
			}
		} else{
			// User is NOT in a company
			
			$_SESSION['AddBookingSelectedACompany'] = TRUE;
			unset($_SESSION['AddBookingDisplayCompanySelect']);
			$_SESSION['AddBookingInfoArray']['TheCompanyID'] = "";
			$_SESSION['AddBookingInfoArray']['BookedForCompany'] = "";
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
	$row = $_SESSION['AddBookingInfoArray'];
	$original = $_SESSION['AddBookingOriginalInfoArray'];
		// Changed user
	if(isset($_SESSION['AddBookingSelectedNewUser'])){
		
		foreach($users AS $user){
			if($user['userID'] == $row['TheUserID']){
				$row['UserLastname'] = $user['lastName'];
				$row['UserFirstname'] = $user['firstName'];
				$row['UserEmail'] = $user['email'];

				$_SESSION['AddBookingDefaultDisplayNameForNewUser'] = $user['displayName'];
				$_SESSION['AddBookingDefaultBookingDescriptionForNewUser'] = $user['bookingDescription'];
				break;
			}
		}
	} else {
		$_SESSION['AddBookingDefaultDisplayNameForNewUser'] = $original['UserDefaultDisplayName'];
		$_SESSION['AddBookingDefaultBookingDescriptionForNewUser'] = $original['UserDefaultBookingDescription'];
	}

		// Altered inputs

	if(isset($row['TheCompanyID'])){
		
			// Changed company?
		foreach($company AS $cmp){
			if($cmp['companyID'] == $row['TheCompanyID']){
				$row['BookedForCompany'] = $cmp['companyName'];
				break;
			}
		}	
		
		$selectedCompanyID = $row['TheCompanyID'];
		$companyID = $row['TheCompanyID'];
	} else {
		$selectedCompanyID = '';
		$companyID = '';	
	}

	if(isset($row['BookedForCompany'])){
		$companyName = $row['BookedForCompany'];
	} else {
		$companyName = '';
	}	
	
	//	userID has been set earlier
	$meetingroom = $_SESSION['AddBookingMeetingRoomsArray'];
	if(isset($row['TheMeetingRoomID'])){
		$selectedMeetingRoomID = $row['TheMeetingRoomID'];
	} else {
		$selectedMeetingRoomID = '';
	}
	if(isset($row['StartTime'])){
		$startDateTime = $row['StartTime'];
	} else {
		$startDateTime = getDatetimeNow();
	}
	
	if(isset($row['EndTime'])){
		$endDateTime = $row['EndTime'];
	} else {
		$endDateTime = getDatetimeNow();
	}
	
	if(isset($row['BookedBy'])){
		$displayName = $row['BookedBy'];
	} else {
		$displayName = '';
	}
	
	if(isset($row['BookingDescription'])){
		$description = $row['BookingDescription'];
	} else {
		$description = '';
	}
	
	$userInformation = $row['UserLastname'] . ', ' . $row['UserFirstname'] . ' - ' . $row['UserEmail'];	

	// Change form
	include 'addbooking.html.php';
	exit();	
}

// When admin has added the needed information and wants to add the booking
if (isset($_POST['add']) AND $_POST['add'] == "Add booking")
{
	// Start validating user inputs
	$invalidInput = FALSE;
	
		// Are values actually filled in?
	if($_POST['startDateTime'] == "" AND $_POST['endDateTime'] == ""){
		
		$_SESSION['AddBookingError'] = "You need to fill in a start and end time for your booking.";	
		$invalidInput = TRUE;
	} elseif($_POST['startDateTime'] != "" AND $_POST['endDateTime'] == "") {
		$_SESSION['AddBookingError'] = "You need to fill in an end time for your booking.";	
		$invalidInput = TRUE;		
	} elseif($_POST['startDateTime'] == "" AND $_POST['endDateTime'] != ""){
		$_SESSION['AddBookingError'] = "You need to fill in a start time for your booking.";	
		$invalidInput = TRUE;		
	}
		// DateTime formats
	// TO-DO: Check if stuff is valid when the proper datetime user input submit has been decided
		
		// DisplayName
			// Has to be less than 255 chars (MySQL - VARCHAR 255)
	$dspname = $_POST['displayName'];
	$dspnameLength = strlen(utf8_decode($dspname));
	$dspnameMaxLength = 255; // TO-DO: Adjust if needed.
	if($dspnameLength > $dspnameMaxLength AND !$invalidInput){
		
		$_SESSION['AddBookingError'] = "The displayName submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// BookingDescription
			// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)
	$bknDscrptn = $_POST['description'];
	$bknDscrptnLength = strlen(utf8_decode($bknDscrptn));
	$bknDscrptnMaxLength = 500; // TO-DO: Adjust if needed.
	if($bknDscrptnLength > $bknDscrptnMaxLength AND !$invalidInput){
		
		$_SESSION['AddBookingError'] = "The booking description submitted is too long.";	
		$invalidInput = TRUE;		
	}
	
	$startDateTime = correctDatetimeFormat($_POST['startDateTime']);
	$endDateTime = correctDatetimeFormat($_POST['endDateTime']);

	$timeNow = getDatetimeNow();
	
	if($startDateTime > $endDateTime AND !$invalidInput){
		// End time can't be before the start time
		
		$_SESSION['AddBookingError'] = "The start time can't be later than the end time. Please select a new start time or end time.";
		$invalidInput = TRUE;
	
	}
	
	if($startDateTime < $timeNow AND !$invalidInput){
		// You can't book a meeting starting in the past.
		
		$_SESSION['AddBookingError'] = "The start time you selected is already over. Select a new start time.";
		$invalidInput = TRUE;
	}
	
	if($endDateTime < $timeNow AND !$invalidInput){
		// You can't book a meeting ending in the past.
		
		$_SESSION['AddBookingError'] = "The end time you selected is already over. Select a new end time.";
		$invalidInput = TRUE;	
	}	
	
	if($invalidInput){
		
		rememberAddBookingInputs();
		// Refresh.
		if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
			$_SESSION['refreshAddBookingChangeUser'] = TRUE;				
		} else {
			$_SESSION['refreshAddBooking'] = TRUE;	
		}
		header('Location: .');
		exit();			
	}
	
	// Check if the timeslot is taken for the selected meeting room
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql =	" 	SELECT 	COUNT(*)
					FROM 	`booking`
					WHERE 	`meetingRoomID` = :MeetingRoomID
					AND		
					(		
							(
								`startDateTime` 
								BETWEEN :StartTime
								AND :EndTime
							) 
					OR 		(
								`endDateTime`
								BETWEEN :StartTime
								AND :EndTime
							)
					)";
		$s = $pdo->prepare($sql);
		
		$s->bindValue(':MeetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':StartTime', $startDateTime);
		$s->bindValue(':EndTime', $endDateTime);
		$s->execute();
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
		rememberAddBookingInputs();
		
		$_SESSION['AddBookingError'] = "The booking couldn't be made. The timeslot is already taken for this meeting room.";
		if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']){
			$_SESSION['refreshAddBookingChangeUser'] = TRUE;				
		} else {
			$_SESSION['refreshAddBooking'] = TRUE;	
		}
		header('Location: .');
		exit();				
	}
	
	// Add the booking to the database
	try
	{	
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
		
		$s->bindValue(':meetingRoomID', $_POST['meetingRoomID']);
		$s->bindValue(':userID', $_SESSION['LoggedInUserID']);
		$s->bindValue(':companyID', $companyID);
		$s->bindValue(':displayName', $_POST['displayName']);
		$s->bindValue(':startDateTime', $startDateTime);
		$s->bindValue(':endDateTime', $endDateTime);
		$s->bindValue(':description', $_POST['description']);
		$s->bindValue(':cancellationCode', $cancellationCode);
		$s->execute();

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
	
	// Add a log event that a booking has been created
	try
	{
		// Get meeting room name
		$MeetingRoomName = 'N/A';
		foreach ($_SESSION['AddBookingMeetingRoomsArray'] AS $room){
			if($room['meetingRoomID'] == $_POST['meetingRoomID']){
				$MeetingRoomName = $room['meetingRoomName'];
				break;
			}
		}
		unset($_SESSION['AddBookingMeetingRoomsArray']);
		
		$meetinginfo = $MeetingRoomName . ' for the timeslot: ' . 
		$_POST['startDateTime'] . ' to ' . $_POST['endDateTime'];
		
		// Get user information
		$userinfo = 'N/A';
		$info = $_SESSION['AddBookingInfoArray'];
		if(isset($info['UserLastname'])){
			$userinfo = $info['UserLastname'] . ', ' . $info['UserFirstname'] . ' - ' . $info['UserEmail'];
		}
		
		// Save a description with information about the booking that was created
		$description = 'A booking was created for the meeting room: ' . $meetinginfo . 
		', for the user: ' . $userinfo . '. Booking was made by: ' . $_SESSION['LoggedInUserName'];
		
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
		$s->bindValue(':UserID', $_POST['userID']);
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
	
	clearBookingSessions();
	
	// Load booking history list webpage with new booking
	header('Location: .');
	exit();
}

// Admin wants to change the user the booking is for
// We need to get a list of all active users
if((isset($_POST['add']) AND $_POST['add'] == "Change User") OR 
	(isset($_SESSION['refreshAddBookingChangeUser'])) AND $_SESSION['refreshAddBookingChangeUser']){
	
	if(isset($_SESSION['refreshAddBookingChangeUser']) AND $_SESSION['refreshAddBookingChangeUser']){
		// Confirm that we have refreshed
		unset($_SESSION['refreshAddBookingChangeUser']);
	}	
	
	// Forget the old search result for users if we had one saved
	unset($_SESSION['AddBookingUsersArray']);	
	
	// Let's remember what was selected if we do any changes before clicking "change user"
	if(isset($_POST['add']) AND $_POST['add'] == "Change User"){
		rememberAddBookingInputs();
	}

	$usersearchstring = "";
	
	if(isset($_SESSION['AddBookingUserSearch'])){
		$usersearchstring = $_SESSION['AddBookingUserSearch'];
	}	
	
	if(!isset($_SESSION['AddBookingUsersArray'])){
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
			$_SESSION['AddBookingUsersArray'] = $users;
			
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
		$users = $_SESSION['AddBookingUsersArray'];
	}

	$_SESSION['refreshAddBooking'] = TRUE;
	$_SESSION['AddBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// Admin confirms what user he wants the booking to be for.
if(isset($_POST['add']) AND $_POST['add'] == "Select This User"){
	
	// We haven't set the company if we are changing the user.
	unset($_SESSION['AddBookingSelectedACompany']);

	// We no longer need to be able to change the user
	unset($_SESSION['AddBookingChangeUser']);
	
	// Remember that we've selected a new user
	$_SESSION['AddBookingSelectedNewUser'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This User"
	rememberAddBookingInputs();
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

//	Admin wants to change the company the booking is for (after having already selected it)
if(isset($_POST['add']) AND $_POST['add'] == "Change Company"){
	
	// We want to select a company again
	unset($_SESSION['AddBookingSelectedACompany']);
	
	// Let's remember what was selected if we do any changes before clicking "Change Company"
	rememberAddBookingInputs();
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// Admin confirms what company he wants the booking to be for.
if(isset($_POST['add']) AND $_POST['add'] == "Select This Company"){

	// Remember that we've selected a new company
	$_SESSION['AddBookingSelectedACompany'] = TRUE;
	
	// Let's remember what was selected if we do any changes before clicking "Select This Company"
	rememberAddBookingInputs();
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}


// If admin wants to get the default values for the user's display name
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Display Name"){
	  
	$displayName = $_SESSION['AddBookingDefaultDisplayNameForNewUser'];
	if(isset($_SESSION['AddBookingInfoArray'])){
		$newValues = $_SESSION['AddBookingInfoArray'];

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
				
				$_SESSION['AddBookingInfoArray'] = $newValues;	

				unset($_SESSION['AddBookingDefaultDisplayNameForNewUser']);				
			} else {
				// Description was already the default booking description
				$_SESSION['AddBookingError'] = "This is already the user's default display name.";
			}
		} else {
			// The user has no default display name
			$_SESSION['AddBookingError'] = "This user has no default display name.";
		}		
	}
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin wants to get the default values for the user's booking description
if(isset($_POST['add']) AND $_POST['add'] == "Get Default Booking Description"){
	
	$bookingDescription = $_SESSION['AddBookingDefaultBookingDescriptionForNewUser'];
	if(isset($_SESSION['AddBookingInfoArray'])){
		$newValues = $_SESSION['AddBookingInfoArray'];

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
				
				$_SESSION['AddBookingInfoArray'] = $newValues;	

				unset($_SESSION['AddBookingDefaultBookingDescriptionForNewUser']);			
			} else {
				// Description was already the default booking description
				$_SESSION['AddBookingError'] = "This is already the user's default booking description.";
			}
		} else {
			// The user has no default booking description
			$_SESSION['AddBookingError'] = "This user has no default booking description.";
		}
	}
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();	
}

// If admin is adding a booking and wants to limit the users shown by searching
if(isset($_SESSION['AddBookingChangeUser']) AND isset($_POST['add']) AND $_POST['add'] == "Search"){
	
	// Let's remember what was selected and searched for
		// The user search string
	$_SESSION['AddBookingUserSearch'] = $_POST['usersearchstring'];

	rememberAddBookingInputs();
	
	// Get the new users
	$_SESSION['refreshAddBookingChangeUser'] = TRUE;
	header('Location: .');
	exit();
}

// If admin wants to change the values back to the original values
if (isset($_POST['add']) AND $_POST['add'] == "Reset"){

	$_SESSION['AddBookingInfoArray'] = $_SESSION['AddBookingOriginalInfoArray'];
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	
	$_SESSION['refreshAddBooking'] = TRUE;
	header('Location: .');
	exit();		
}

// If admin wants to leave the page and be directed back to the booking page again
if (isset($_POST['add']) AND $_POST['add'] == 'Cancel'){

	$_SESSION['BookingUserFeedback'] = "You cancelled your new booking.";
}


// ADD CODE SNIPPET END //

// END OF USER INPUT CODE //

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