<?php 
// This is the index file for the ROOMEQUIPMENT folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add room equipment form
function clearAddRoomEquipmentSessions(){

	unset($_SESSION['AddRoomEquipmentEquipmentArray']);	
	unset($_SESSION['AddRoomEquipmentEquipmentSearch']);

	unset($_SESSION['AddRoomEquipmentSelectedEquipment']);
	unset($_SESSION['AddRoomEquipmentSelectedEquipmentAmount']);
	unset($_SESSION['AddRoomEquipmentSelectedMeetingRoom']);

	unset($_SESSION['AddRoomEquipmentMeetingRoomArray']);
	unset($_SESSION['AddRoomEquipmentMeetingRoomSearch']);
}

// Function to clear sessions used to remember user inputs on refreshing the 'edit'/'change amount' room equipment form
function clearEditRoomEquipmentSessions(){
	unset($_SESSION['EditRoomEquipmentOriginalEquipmentAmount']);
}

// If admin wants to be able to remove equipment from a room it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Remove"){
	$_SESSION['roomequipmentEnableDelete'] = TRUE;
	$refreshRoomEquipment = TRUE;
}

// If admin wants to be disable room equipment deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Remove"){
	unset($_SESSION['roomequipmentEnableDelete']);
	$refreshRoomEquipment = TRUE;
}

// If admin wants to remove equipment from a room
if(isSet($_POST['action']) AND $_POST['action'] == 'Remove'){
	// Remove room equipment connection from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'DELETE FROM `roomequipment` 
				WHERE 		`EquipmentID` = :EquipmentID
				AND			`MeetingRoomID` = :MeetingRoomID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->bindValue(':MeetingRoomID', $_POST['MeetingRoomID']);
		$s->execute();

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error removing equipment from the meeting room: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	$_SESSION['RoomEquipmentUserFeedback'] = "Successfully removed the equipment from the room.";

	// Add a log event that equipment was removed from a meeting room
	try
	{
		// Save a description with information about the equipment that was removed
		// from the meeting room.
		$description = 'The equipment: ' . $_POST['EquipmentName'] . 
		' was removed from the meeting room: ' . $_POST['MeetingRoomName'] . 
		".\nRemoved by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Room Equipment Removed'
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

	//	Go to the room equipment main page with the appropriate values
	if(isSet($_GET['Meetingroom'])){	
		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	} else {
		// Do a normal page reload
		header('Location: .');
		exit();
	}
}

// Admin clicked the search button, trying to limit the shown equipment and meeting rooms
if(isSet($_POST['action']) AND $_POST['action'] == 'Search'){
	// We are doing a new search, so we need to get new meeting room and equipment arrays
	unset($_SESSION['AddRoomEquipmentEquipmentArray']);	
	unset($_SESSION['AddRoomEquipmentMeetingRoomArray']);

	$_SESSION['AddRoomEquipmentShowSearchResults'] = TRUE;

	// Let's remember what was searched for
	$_SESSION['AddRoomEquipmentEquipmentSearch'] = trimExcessWhitespace($_POST['equipmentsearchstring']);
	$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
	$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
	$_SESSION['refreshAddRoomEquipment'] = TRUE;

	// If we are looking at a specific meeting room, let's refresh info about
	// that meeting room again.
	if(isSet($_GET['Meetingroom'])){

		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	} else {
		$_SESSION['AddRoomEquipmentMeetingRoomSearch'] = trimExcessWhitespace($_POST['meetingroomsearchstring']);
		$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];

		// Also we want to refresh AddRoomEquipment with our new values!
		$_SESSION['refreshAddRoomEquipment'] = TRUE;
		header('Location: .');
		exit();
	}
}

// 	If admin wants to update the database by adding equipment to a meeting room
// 	we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Add Room Equipment') OR 
	(isSet($_SESSION['refreshAddRoomEquipment']) AND $_SESSION['refreshAddRoomEquipment'])
	){

	// Set initial values
	$equipmentsearchstring = '';
	$meetingroomsearchstring = '';
	$EquipmentAmount = 1;

	// Check if the call was a form submit or a forced refresh
	if(isSet($_SESSION['refreshAddRoomEquipment']) AND $_SESSION['refreshAddRoomEquipment']){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddRoomEquipment']);

		// Display the 'error' that made us refresh
		if(isSet($_SESSION['AddRoomEquipmentError'])){
			$AddRoomEquipmentError = $_SESSION['AddRoomEquipmentError'];
			unset($_SESSION['AddRoomEquipmentError']);
		}

		// Set the meeting room string that was searched before refreshing
		if(isSet($_SESSION['AddRoomEquipmentMeetingRoomSearch'])){
			$meetingroomsearchstring = $_SESSION['AddRoomEquipmentMeetingRoomSearch'];
			unset($_SESSION['AddRoomEquipmentMeetingRoomSearch']);
		}

		// Set the equipment string that was searched before refreshing
		if(isSet($_SESSION['AddRoomEquipmentEquipmentSearch'])){
			$equipmentsearchstring = $_SESSION['AddRoomEquipmentEquipmentSearch'];
			unset($_SESSION['AddRoomEquipmentEquipmentSearch']);
		}

		// Set what meeting room was selected before refreshing
		if(isSet($_SESSION['AddRoomEquipmentSelectedMeetingRoom'])){
			$selectedMeetingRoomID = $_SESSION['AddRoomEquipmentSelectedMeetingRoom'];
			unset($_SESSION['AddRoomEquipmentSelectedMeetingRoom']);
		}

		// Set what equipment was selected before refreshing
		if(isSet($_SESSION['AddRoomEquipmentSelectedEquipment'])){
			$selectedEquipmentID = $_SESSION['AddRoomEquipmentSelectedEquipment'];
			unset($_SESSION['AddRoomEquipmentSelectedEquipment']);
		}

		// Set the equipment amount that was selected before refreshing
		if(isSet($_SESSION['AddRoomEquipmentSelectedEquipmentAmount'])){
			$EquipmentAmount = $_SESSION['AddRoomEquipmentSelectedEquipmentAmount'];
			unset($_SESSION['AddRoomEquipmentSelectedEquipmentAmount']);
		}
	}

	// Get info about equipment and rooms from the database
	// if we don't already have them saved in a session array
	if(	!isSet($_SESSION['AddRoomEquipmentMeetingRoomArray']) OR 
		!isSet($_SESSION['AddRoomEquipmentEquipmentArray'])){

		try
		{
			// Get all equipment and meeting rooms so the admin can search/choose from them
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();	

				//Meeting Rooms
			// Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddRoomEquipmentMeetingRoomArray'])){
				// We don't have info about meeting rooms saved. Let's get it

				if(!isSet($_GET['Meetingroom'])){
					// If we're NOT looking at a specific meetingroom already
					$sql = 'SELECT 	`meetingRoomID`	AS MeetingRoomID,
									`name` 			AS MeetingRoomName
							FROM 	`meetingroom`';

					if ($meetingroomsearchstring != ''){
						$sqladd = " WHERE `name` LIKE :search";
						$sql .= $sqladd;

						$finalmeetingroomsearchstring = '%' . $meetingroomsearchstring . '%';

						$sql .= " ORDER BY `name`";

						$s = $pdo->prepare($sql);
						$s->bindValue(':search', $finalmeetingroomsearchstring);
						$s->execute();
						$result = $s->fetchAll(PDO::FETCH_ASSOC);
					} else {
						
						$sql .= " ORDER BY `name`";
						
						$return = $pdo->query($sql);
						$result = $return->fetchAll(PDO::FETCH_ASSOC);
					}

					// Get the rows of information from the query
					// This will be used to create a dropdown list in HTML
					foreach($result as $row){
						$meetingrooms[] = array(
												'MeetingRoomID' => $row['MeetingRoomID'],
												'MeetingRoomName' => $row['MeetingRoomName']
												);
					}
				} else {
					// We want info about a specific meeting room
					$sql = 'SELECT 	`meetingRoomID`	AS MeetingRoomID,
									`name` 			AS MeetingRoomName
							FROM 	`meetingroom`
							WHERE 	`meetingRoomID` = :MeetingRoomID
							LIMIT 	1';
					$s = $pdo->prepare($sql);
					$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
					$s->execute();
					$meetingrooms = $s->fetch(PDO::FETCH_ASSOC);
				}
				if(isSet($meetingrooms)){
					$_SESSION['AddRoomEquipmentMeetingRoomArray'] = $meetingrooms;
					$meetingRoomsFound = sizeOf($meetingrooms);
				} else {
					$_SESSION['AddRoomEquipmentMeetingRoomArray'] = array();
					$meetingRoomsFound = 0;
				}

				if(isSet($_SESSION['AddRoomEquipmentShowSearchResults']) AND $_SESSION['AddRoomEquipmentShowSearchResults']){
					$_SESSION['AddRoomEquipmentSearchResult'] = "The search result found $meetingRoomsFound meeting rooms";
				}
			} else {
				$meetingrooms = $_SESSION['AddRoomEquipmentMeetingRoomArray'];
			}
				// Equipment
			// Only get info if we haven't gotten it before
			if(!isSet($_SESSION['AddRoomEquipmentEquipmentArray'])){
				// We don't have info about equipment saved. Let's get it

				$sql = 'SELECT 	`EquipmentID`,
								`name` 			AS EquipmentName
						FROM 	`equipment`';

				if ($equipmentsearchstring != ''){
					$sqladd = " WHERE `name` LIKE :search";
					$sql = $sql . $sqladd;

					$finalequipmentsearchstring = '%' . $equipmentsearchstring . '%';

					$s = $pdo->prepare($sql);
					$s->bindValue(":search", $finalequipmentsearchstring);
					$s->execute();
					$result = $s->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$return = $pdo->query($sql);
					$result = $return->fetchAll(PDO::FETCH_ASSOC);
				}

				// Get the rows of information from the query
				// This will be used to create a dropdown list in HTML
				foreach($result as $row){
					$equipment[] = array(
											'EquipmentID' => $row['EquipmentID'],
											'EquipmentName' => $row['EquipmentName']
											);
				}
				if(isSet($equipment)){
					$_SESSION['AddRoomEquipmentEquipmentArray'] = $equipment;
					$equipmentFound = sizeOf($equipment);
				} else {
					$_SESSION['AddRoomEquipmentEquipmentArray'] = array();
					$equipmentFound = 0;
				}
				if(isSet($_SESSION['AddRoomEquipmentShowSearchResults']) AND $_SESSION['AddRoomEquipmentShowSearchResults']){
					if(isSet($_SESSION['AddRoomEquipmentSearchResult'])){
						$_SESSION['AddRoomEquipmentSearchResult'] .= " and $equipmentFound equipment";
					} else {
						$_SESSION['AddRoomEquipmentSearchResult'] = "The search result found $equipmentFound equipment";
					}
				}
			} else {
				$equipment = $_SESSION['AddRoomEquipmentEquipmentArray'];
			}

			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching equipment and meeting room lists: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	} else {
		$meetingrooms = $_SESSION['AddRoomEquipmentMeetingRoomArray'];
		$equipment = $_SESSION['AddRoomEquipmentEquipmentArray'];
	}

	if(isSet($_SESSION['AddRoomEquipmentSearchResult'])){
		$_SESSION['AddRoomEquipmentSearchResult'] .= ".";
	}
	unset($_SESSION['AddRoomEquipmentShowSearchResults']);

	// Change to the actual html form template
	include 'addroomequipment.html.php';
	exit();
}

// When admin has added the needed information and wants to add equipment in a meeting room
if(isSet($_POST['action']) AND $_POST['action'] == 'Confirm Room Equipment'){

	// If we're looking at a specific meetingroom
	if(isSet($_GET['Meetingroom'])){
		$MeetingRoomID = $_GET['Meetingroom'];
	} else {
		$MeetingRoomID = $_POST['MeetingRoomID'];
	}

	// Make sure we only do this if user filled out all values
	$a = ($_POST['EquipmentID'] == '');
	$b = ($MeetingRoomID == '');
	$c = ($_POST['EquipmentAmount'] < 1);

	if ($a OR $b OR $c){
		// Some value wasn't filled out.
		// Set appropriate feedback message to admin
		if($a AND $b AND !$c){
			$d = "You need to select an equipment and a meeting room first!";
			$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
		}
		if($a AND !$b AND $c){
			$d = "You need to select an equipment and the amount first!";
			$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];
		}
		if(!$a AND $b AND $c){
			$d = "You need to select a meeting room and the equipment amount first!";
			$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
		}
		if($a AND !$b AND !$c){
			$d = "You need to select an equipment first!";
			$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];
			$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
		}
		if(!$a AND $b AND !$c){
			$d = "You need to select a meeting room first!";
			$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
			$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
		}
		if(!$a AND !$b AND $c){
			$d = "You need to select an equipment amount first!";
			$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];
			$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
		}
		if($a AND $b AND $c){
			$d = "You need to select an equipment, a meeting room and an amount first!";
		}

		// We didn't have enough values filled in. "go back" to add roomequipment
		$_SESSION['refreshAddRoomEquipment'] = TRUE;
		$_SESSION['AddRoomEquipmentError'] = $d;

		$_SESSION['AddRoomEquipmentMeetingRoomSearch'] = trimExcessWhitespace($_POST['meetingroomsearchstring']);
		$_SESSION['AddRoomEquipmentEquipmentSearch'] = trimExcessWhitespace($_POST['equipmentsearchstring']);

		if(isSet($_GET['Meetingroom'])){
			// We were looking at a specific meeting room. Let's go back to info about that meetingroom
			$TheMeetingRoomID = $_GET['Meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
			header("Location: $location");
			exit();
		} else {
			// We were not looking at a specific meeting room. Let's do a normal refresh.
			header('Location: .');
			exit();
		}
	}

	// Check if the room equipment connection already exists for the meeting room and equipment.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`roomequipment`
				WHERE 	`MeetingRoomID`= :MeetingRoomID
				AND 	`EquipmentID` = :EquipmentID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $MeetingRoomID);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->execute();

		$pdo = null;

		$row = $s->fetch();

		if($row[0] > 0){
			// This meeting room and equipment combination already exists in our database
			// This means the equipment is already in the selected meeting room!

			$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
			$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
			$_SESSION['AddRoomEquipmentMeetingRoomSearch'] = trimExcessWhitespace($_POST['meetingroomsearchstring']);
			$_SESSION['AddRoomEquipmentEquipmentSearch'] = trimExcessWhitespace($_POST['equipmentsearchstring']);

			$_SESSION['refreshAddRoomEquipment'] = TRUE;
			$_SESSION['AddRoomEquipmentError'] = "The selected equipment is already in the selected meeting room!";

			if(isSet($_GET['Meetingroom'])){

				$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_GET['Meetingroom'];

				// Refresh RoomEquipment for the specific meeting room again
				$TheMeetingRoomID = $_GET['Meetingroom'];
				$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
				header("Location: $location");
				exit();
			}

			// Refresh RoomEquipment 
			$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];
			header('Location: .');
			exit();
		}
		// No roomequipment connection found. Now we can create it.
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for roomequipment connection.' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Add the new roomequipment connection to the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'INSERT INTO `roomequipment` 
				SET			`EquipmentID` = :EquipmentID,
							`MeetingRoomID` = :MeetingRoomID,
							`amount` = :EquipmentAmount';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->bindValue(':MeetingRoomID', $MeetingRoomID);
		$s->bindValue(':EquipmentAmount', $_POST['EquipmentAmount']);
		$s->execute();

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error creating equipment and meeting room connection in database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['RoomEquipmentUserFeedback'] = "Successfully added the equipment to the room.";

	// Add a log event that equipment was added in a meeting room	
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
		$meetingroominfo = 'N/A';
		$equipmentinfo = 'N/A';

		// Get selected meeting room name
		if(isSet($_SESSION['AddRoomEquipmentMeetingRoomArray'])){
			if($_SESSION['AddRoomEquipmentMeetingRoomArray'][0] == $MeetingRoomID){
				$meetingroominfo = $_SESSION['AddRoomEquipmentMeetingRoomArray']['MeetingRoomName'];
			} else {
				foreach($_SESSION['AddRoomEquipmentMeetingRoomArray'] AS $row){
					if($row['MeetingRoomID'] == $MeetingRoomID){
						$meetingroominfo = $row['MeetingRoomName'];
						break;
					}
				}
			}
			unset($_SESSION['AddRoomEquipmentMeetingRoomArray']);
		}

		// Get selected equipment name
		if(isSet($_SESSION['AddRoomEquipmentEquipmentArray'])){
			foreach($_SESSION['AddRoomEquipmentEquipmentArray'] AS $row){
				if($row['EquipmentID'] == $_POST['EquipmentID']){
					$equipmentinfo = $row['EquipmentName'];
					break;
				}
			}
			unset($_SESSION['AddRoomEquipmentEquipmentArray']);
		}

		// Save a description with information about the equipment that was added
		// to the meeting room.	
		$logEventDescription = 'The equipment: ' . $equipmentinfo . 
		' was added to the meeting room: ' . $meetingroominfo . 
		' with the amount: ' . $_POST['EquipmentAmount'] . ".\nAdded by: " .
		$_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Room Equipment Added'
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

	// If we are looking at a specific meeting room, let's refresh info about
	// that meeting room again.
	if(isSet($_GET['Meetingroom'])){
		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	}

	clearAddRoomEquipmentSessions();

	// Load room equipment list webpage with new room equipment connection
	header('Location: .');
	exit();
}

// if admin wants to change the amount of an equipment in a room
// we load a new html form
if(isSet($_POST['action']) AND $_POST['action'] == 'Change Amount'){
	// Get information from database again on the selected room equipment
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();

		// Get roomequipment information
		$sql = "SELECT 		e.`EquipmentID`									AS TheEquipmentID,
							e.`name`										AS EquipmentName,
							e.`description`									AS EquipmentDescription,
							re.`amount`										AS EquipmentAmount,
							m.`meetingRoomID`								AS MeetingRoomID,
							m.`name`										AS MeetingRoomName
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				WHERE 		re.`EquipmentID` = :EquipmentID
				AND			re.`MeetingRoomID` = :MeetingRoomID";

		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->bindValue(':MeetingRoomID', $_POST['MeetingRoomID']);
		$s->execute();

		//Close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching room equipment details: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	// Create an array with the row information we retrieved
	$row = $s->fetch();

	// Set the correct information
	$EquipmentName = $row['EquipmentName'];
	$MeetingRoomName = $row['MeetingRoomName'];
	$CurrentEquipmentAmount = $row['EquipmentAmount'];
	$EquipmentAmount = $row['EquipmentAmount'];
	$EquipmentID = $row['TheEquipmentID'];
	$MeetingRoomID = $row['MeetingRoomID'];

	$_SESSION['EditRoomEquipmentOriginalEquipmentAmount'] = $EquipmentAmount;

	// Change to the actual form we want to use
	include 'changeamount.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Confirm Amount'){
	// Check if there were any changes made
	$NumberOfChanges = 0;

	$selectedRoomEquipmentAmount = $_POST['EquipmentAmount'];

	if(	isSet($_SESSION['EditRoomEquipmentOriginalEquipmentAmount']) AND 
		$_SESSION['EditRoomEquipmentOriginalEquipmentAmount'] != $selectedRoomEquipmentAmount){
		$NumberOfChanges++;
	}

	if($NumberOfChanges > 0){
		// Update selected room equipment connection with a new equipment amount
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

			$pdo = connect_to_db();
			$sql = 'UPDATE 	`roomequipment` 
					SET		`amount` = :EquipmentAmount
					WHERE 	`MeetingRoomID` = :MeetingRoomID
					AND 	`EquipmentID` = :EquipmentID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':MeetingRoomID', $_POST['MeetingRoomID']);
			$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
			$s->bindValue(':EquipmentAmount', $selectedRoomEquipmentAmount);
			$s->execute(); 

			//close connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error changing equipment amount in room equipment information: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
		$_SESSION['RoomEquipmentUserFeedback'] = "Successfully updated the equipment info for the room.";
	} else {
		$_SESSION['RoomEquipmentUserFeedback'] = "No changes were made to the equipment info for the room.";
	}

	clearEditRoomEquipmentSessions();

	if(isSet($_GET['Meetingroom'])){	
		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	}

	// Do a normal page reload
	header('Location: .');
	exit();	
}

// If admin wants to null values while adding
if (isSet($_POST['add']) AND $_POST['add'] == "Reset"){

	clearAddRoomEquipmentSessions();

	$_SESSION['refreshAddRoomEquipment'] = TRUE;
	header('Location: .');
	exit();	
}

// If the user clicks any cancel buttons he'll be directed back to the roomequipment page again
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$_SESSION['RoomEquipmentUserFeedback'] = "You cancelled your meeting room and equipment connection creation.";
	$refreshRoomEquipment = TRUE;
}

// If the user clicks any cancel buttons he'll be directed back to the roomequipment page again
if (isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['RoomEquipmentUserFeedback'] = "You cancelled your meeting room and equipment connection editing.";
	$refreshRoomEquipment = TRUE;
}

if(isSet($refreshRoomEquipment) AND $refreshRoomEquipment){
	unset($refreshRoomEquipment);
}

// Remove any unused variables from memory
clearAddRoomEquipmentSessions();
clearEditRoomEquipmentSessions();

// Get only information from the specific meetingroom
if(isSet($_GET['Meetingroom'])){

	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		$sql = "SELECT 		e.`EquipmentID`									AS TheEquipmentID,
							e.`name`										AS EquipmentName,
							e.`description`									AS EquipmentDescription,
							re.`amount`										AS EquipmentAmount,
							m.`meetingRoomID`								AS MeetingRoomID,
							m.`name`										AS MeetingRoomName,
							re.`datetimeAdded`								AS DateTimeAdded,
							UNIX_TIMESTAMP(re.`datetimeAdded`)				AS OrderByDate
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				WHERE 		m.`meetingRoomID` = :MeetingRoomID
				LIMIT		1";

		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
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
		$error = 'Error getting room equipment information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Get information from all meeting rooms
if(!isSet($_GET['Meetingroom'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		$sql = "SELECT 		e.`EquipmentID`									AS TheEquipmentID,
							e.`name`										AS EquipmentName,
							e.`description`									AS EquipmentDescription,
							re.`amount`										AS EquipmentAmount,
							m.`meetingRoomID`								AS MeetingRoomID,
							m.`name`										AS MeetingRoomName,
							re.`datetimeAdded`								AS DateTimeAdded
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				ORDER BY	m.`name`";

		$return = $pdo->query($sql);
		$result = $return->fetchAll(PDO::FETCH_ASSOC);
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
		$error = 'Error getting room equipment information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){

	$addedDateTime = $row['DateTimeAdded'];
	$displayAddedDateTime = convertDatetimeToFormat($addedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	// Create an array with the actual key/value pairs we want to use in our HTML
	$roomequipment[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'EquipmentAmount' => $row['EquipmentAmount'],
							'DateTimeAdded' => $displayAddedDateTime,
							'MeetingRoomID' => $row['MeetingRoomID'],
							'MeetingRoomName' => $row['MeetingRoomName']
						);
}

// Create the room equipment list in HTML
include_once 'roomequipment.html.php';
?>