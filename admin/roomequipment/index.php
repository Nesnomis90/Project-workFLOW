<?php 
// This is the index file for the ROOMEQUIPMENT folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// If admin wants to remove equipment from a room
if(isset($_POST['action']) AND $_POST['action'] == 'Remove'){
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
	
	// Load room equipment list webpage with updated database
	header('Location: .');
	exit();	
}

// Admin clicked the search button, trying to limit the shown equipment and meeting rooms
if(isset($_POST['action']) AND $_POST['action'] == 'Search'){
	// Let's remember what was searched for
	session_start();
	
	// If we are looking at a specific meeting room, let's refresh info about
	// that meeting room again.
	if(isset($_GET['Meetingroom'])){	
		$_SESSION['AddRoomEquipmentEquipmentSearch'] = $_POST['equipmentsearchstring'];
		$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
		$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
		$_SESSION['refreshAddRoomEquipment'] = TRUE;
		
		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	} else {
		$_SESSION['AddRoomEquipmentEquipmentSearch'] = $_POST['equipmentsearchstring'];
		$_SESSION['AddRoomEquipmentMeetingRoomSearch'] = $_POST['meetingroomsearchstring'];
		$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
		$_SESSION['AddRoomEquipmentSelectedMeetingRoom'] = $_POST['MeetingRoomID'];
		$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
		
		// Also we want to refresh AddRoomEquipment with our new values!
		$_SESSION['refreshAddRoomEquipment'] = TRUE;
		header('Location: .');
		exit();
	}	
}


// 	If admin wants to update the database by adding equipment to a meeting room
// 	we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Add Room Equipment') OR 
	(isset($_SESSION['refreshAddRoomEquipment']) AND $_SESSION['refreshAddRoomEquipment']))
{	

	$equipmentsearchstring = '';
	$meetingroomsearchstring = '';
	$EquipmentAmount = 0;

	session_start();
	// Check if the call was a form submit or a forced refresh
	if(isset($_SESSION['refreshAddRoomEquipment']) AND $_SESSION['refreshAddRoomEquipment']){
		// Acknowledge that we have refreshed the page
		unset($_SESSION['refreshAddRoomEquipment']);
		
		// Display the 'error' that made us refresh
		if(isset($_SESSION['AddRoomEquipmentError'])){
			$AddRoomEquipmentError = $_SESSION['AddRoomEquipmentError'];
			unset($_SESSION['AddRoomEquipmentError']);
		}
		
		// Remember the meeting room string that was searched before refreshing
		if(isset($_SESSION['AddRoomEquipmentMeetingRoomSearch'])){
			$meetingroomsearchstring = $_SESSION['AddRoomEquipmentMeetingRoomSearch'];
			unset($_SESSION['AddRoomEquipmentMeetingRoomSearch']);
		}
		
		// Remember the equipment string that was searched before refreshing
		if(isset($_SESSION['AddRoomEquipmentEquipmentSearch'])){
			$equipmentsearchstring = $_SESSION['AddRoomEquipmentEquipmentSearch'];
			unset($_SESSION['AddRoomEquipmentEquipmentSearch']);
		}
		
		// Remember what meeting room was selected before refreshing
		if(isset($_SESSION['AddRoomEquipmentSelectedMeetingRoom'])){
			$selectedMeetingRoomID = $_SESSION['AddRoomEquipmentSelectedMeetingRoom'];
			unset($_SESSION['AddRoomEquipmentSelectedMeetingRoom']);
		}
		
		// Remember what equipment was selected before refreshing
		if(isset($_SESSION['AddEmployeeSelectedUserID'])){
			$selectedEquipmentID = $_SESSION['AddEmployeeSelectedUserID'];
			unset($_SESSION['AddEmployeeSelectedUserID']);
		}	
		
		// Remember the equipment amount that was selected before refreshing
		if(isset($_SESSION['AddRoomEquipmentSelectedEquipmentAmount'])){
			$EquipmentAmount = $_SESSION['AddRoomEquipmentSelectedEquipmentAmount'];
			unset($_SESSION['AddRoomEquipmentSelectedEquipmentAmount']);
		}	
		
	}


	// Get info about equipment and rooms from the database
	try
	{
		// Get all equipment and meeting rooms so the admin can search/choose from them
			//Meeting Rooms
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';		
		$pdo = connect_to_db();		
			
		if(!isset($_GET['Meetingroom'])){
			// If we're NOT looking at a specific meetingroom already
			$sql = 'SELECT 	`meetingRoomID`	AS MeetingRoomID,
							`name` 			AS MeetingRoomName
					FROM 	`meetingroom`';
					
			if ($meetingroomsearchstring != ''){
				$sqladd = " WHERE `name` LIKE :search";
				$sql = $sql . $sqladd;	
				
				$finalmeetingroomsearchstring = '%' . $meetingroomsearchstring . '%';
				
				$s = $pdo->prepare($sql);
				$s->bindValue(':search', $finalmeetingroomsearchstring);
				$s->execute();
				$result = $s->fetchAll();
			} else {
				$result = $pdo->query($sql);
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
			$sql = 'SELECT 	`meetingRoomID`	AS MeetingRoomID,
							`name` 			AS MeetingRoomName
					FROM 	`meetingroom`
					WHERE 	`meetingRoomID` = :MeetingRoomID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
			$s->execute();
			$meetingrooms = $s->fetch();
		}	

		session_start();
		$_SESSION['AddRoomEquipmentMeetingRoomArray'] = $meetingrooms;	
		
			// Equipment
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
			$result = $s->fetchAll();
		} else {
			$result = $pdo->query($sql);
		}
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$equipment[] = array(
									'EquipmentID' => $row['EquipmentID'],
									'EquipmentName' => $row['EquipmentName']
									);
		}
			
		session_start();
		$_SESSION['AddRoomEquipmentEquipmentArray'] = $equipment;		
			
			
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
		
	// Change to the actual html form template
	include 'addroomequipment.html.php';
	exit();
}

// When admin has added the needed information and wants to add equipment in a meeting room
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Room Equipment')
{
	session_start();
	// Make sure we only do this if user filled out all values
	if(!isset($_GET['Meetingroom'])){
		$a = ($_POST['EquipmentID'] == '');
		$b = ($_POST['MeetingRoomID'] == '');
		$c = ($_POST['EquipmentAmount'] < 1);
		if ($a AND $b AND $c){
			$d = "Need to select an equipment, a meeting room and an amount first!";
			if($a AND $b AND !$c){
				$d = "Need to select an equipment and a meeting room first!";
			}
			if($a AND !$b AND $c){
				$d = "Need to select an equipment and the amount first!";
			}
			if(!$a AND $b AND $c){
				$d = "Need to select a meeting room and the equipment amount first!";
			}
			if($a AND !$b AND !$c){
				$d = "Need to select an equipment first!";
			}
			if(!$a AND $b AND !$c){
				$d = "Need to select a meeting room first!";
			}
			if(!$a AND !$b AND $c){
				$d = "Need to select an equipment amount first!";
			}
			/*
			if($_POST['EquipmentID'] == '' AND $_POST['MeetingRoomID'] == '' AND $_POST['EquipmentAmount'] == ''){
				// We didn't have enough values filled in. "go back" to roomequipment fill in
				$_SESSION['refreshAddRoomEquipment'] = TRUE;
				$_SESSION['AddRoomEquipmentError'] = "Need to select an equipment, a meetingroom and an amount first!";
				header('Location: .');
				exit();
			} elseif(($_POST['EquipmentID'] != '' OR $_POST['EquipmentAmount'] != '') AND $_POST['MeetingRoomID'] == '' ){
				$_SESSION['refreshAddRoomEquipment'] = TRUE;
				$_SESSION['AddRoomEquipmentError'] = "Need to select a Meeting Room first!";
				header('Location: .');	
				exit();
			} elseif(($_POST['EquipmentAmount'] != '' OR $_POST['MeetingRoomID'] != '') AND $_POST['EquipmentID'] == ''){
				$_SESSION['refreshAddRoomEquipment'] = TRUE;
				$_SESSION['AddRoomEquipmentError'] = "Need to select an Equipment first!";
				$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
				header('Location: .');			
				exit();
			} elseif(($_POST['EquipmentID'] != '' OR $_POST['MeetingRoomID'] != '') AND $_POST['EquipmentAmount'] == ''){

				$_SESSION['AddRoomEquipmentError'] = "Need to select an Equipment Amount first!";

			}*/
			// We didn't have enough values filled in. "go back" to roomequipment fill in
			$_SESSION['AddRoomEquipmentError'] = $d;
			$_SESSION['refreshAddRoomEquipment'] = TRUE;
			header('Location: .');			
			exit();
		}

	} else {
		// If we have already selected a specific meeting room
		if($_POST['EquipmentID'] == '' OR $_POST['EquipmentAmount'] == 0){
			// We didn't have enough values filled in. "go back" to room equipment fill in
			if($_POST['EquipmentID'] == '' AND $_POST['EquipmentAmount'] == 0){
				$_SESSION['AddRoomEquipmentError'] = "Need to select an equipment and amount first!";
			} elseif($_POST['EquipmentID'] == '' AND $_POST['EquipmentAmount'] > 0){
				$_SESSION['AddRoomEquipmentError'] = "Need to select an equipment first!";
			} elseif($_POST['EquipmentID'] <> '' AND $_POST['EquipmentAmount'] == 0){
				$_SESSION['AddRoomEquipmentError'] = "Need to select an equipment amount first!";
			} else {
				$_SESSION['AddRoomEquipmentError'] = "Unknown refresh.";
			}
			// Refresh RoomEquipment for the specific meeting room again
			$_SESSION['refreshAddRoomEquipment'] = TRUE;
			$TheMeetingRoomID = $_GET['Meetingroom'];
			$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
			header("Location: $location");
			exit();
		}
	}
	
	// If we're looking at a specific meetingroom
	session_start();
	if(isset($_GET['Meetingroom'])){
		$MeetingRoomID = $_SESSION['AddRoomEquipmentMeetingRoomArray']['MeetingRoomID'];
	} else {
		$MeetingRoomID = $_POST['MeetingRoomID'];
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
		
		if ($row[0] > 0)
		{
			// This meeting room and equipment combination already exists in our database
			// This means the equipment is already in the selected meeting room!
			
			session_start();
			
			$_SESSION['AddRoomEquipmentSelectedEquipment'] = $_POST['EquipmentID'];
			$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'] = $_POST['EquipmentAmount'];
			$_SESSION['refreshAddRoomEquipment'] = TRUE;
			$_SESSION['AddRoomEquipmentError'] = "The selected equipment is already in the selected meeting room!";
			
			
			if(isset($_GET['Meetingroom'])){
				
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
		
			// move the variables below...
		$_SESSION['AddRoomEquipmentSelectedEquipment'];
		$_SESSION['AddRoomEquipmentSelectedMeetingRoom'];
		$_SESSION['AddRoomEquipmentSelectedEquipmentAmount'];	
		$_SESSION['AddRoomEquipmentMeetingRoomArray'];
		$_SESSION['AddRoomEquipmentEquipmentArray'];
		
		session_start();
		// Get selected meeting room name
		if(isset($_SESSION['AddRoomEquipmentMeetingRoomArray'])){
			foreach($_SESSION['AddRoomEquipmentMeetingRoomArray'] AS $row){
				if($row['MeetingRoomID'] == $_POST['MeetingRoomID']){
					$meetingroominfo = $row['MeetingRoomName'];
					break;
				}
			}
			unset($_SESSION['AddRoomEquipmentMeetingRoomArray']);
		}
		
		// Get selected equipment name
		if(isset($_SESSION['AddRoomEquipmentEquipmentArray'])){
			foreach($_SESSION['AddRoomEquipmentEquipmentArray'] AS $row){
				if($row['CompanyID'] == $_POST['CompanyID']){
					$equipmentinfo = $row['CompanyName'];
					break;
				}
			}
			unset($_SESSION['AddEmployeeCompaniesArray']);
		}
	
		
		// Save a description with some extra information to be kept after 
		// the user or company has been removed
		$description = 'The equipment: ' . $equipmentinfo . 
		' was added to the meeting room: ' . $meetingroominfo . 
		' with the amount: ' . $_POST['EquipmentAmount'] . ". Added by: " .
		$_SESSION['LoggedInUserName'];
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
											SELECT `actionID` 
											FROM `logaction`
											WHERE `name` = 'Room Equipment Added'
											),
							`meetingRoomID` = :MeetingRoomID,
							`equipmentID` = :EquipmentID,
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $MeetingRoomID);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);	
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
	
	// If we are looking at a specific meeting room, let's refresh info about
	// that meeting room again.
	if(isset($_GET['Meetingroom'])){
		// Refresh RoomEquipment for the specific meeting room again
		$TheMeetingRoomID = $_GET['Meetingroom'];
		$location = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $TheMeetingRoomID;
		header("Location: $location");
		exit();
	}
	
	// Load room equipment list webpage with new room equipment connection
	header('Location: .');
	exit();
}

// if admin wants to change the amount of an equipment in a room
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Change Amount')
{
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
	
	// Change to the actual form we want to use
	include 'changeamount.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Amount')
{
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
		$s->bindValue(':EquipmentAmount', $_POST['EquipmentAmount']);
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
	
	// Load room equipment list webpage with updated database
	header('Location: .');
	exit();
}


// If the user clicks any cancel buttons he'll be directed back to the roomequipment page again
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel'){
	// Doesn't actually need any code to work, since it happends automatically when a submit
	// occurs. *it* being doing the normal startup code.
	// Might be useful for something later?
	echo "<b>Cancel button clicked. Taking you back to /admin/roomequipment/!</b><br />";
}

session_start();
// Get only information from the specific mneetingroom
if(isset($_GET['Meetingroom'])){
	
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
							DATE_FORMAT(re.`datetimeAdded`,'%d %b %Y %T') 	AS DateTimeAdded,
							UNIX_TIMESTAMP(re.`datetimeAdded`)				AS OrderByDate
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				WHERE 		m.`meetingRoomID` = :MeetingRoomID
				ORDER BY	OrderByDate
				DESC";
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':MeetingRoomID', $_GET['Meetingroom']);
		$s->execute();
		
		$result = $s->fetchAll();
		$rowNum = sizeOf($result);
		
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

// Display roomequipment list
if(!isset($_GET['Meetingroom'])){
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
							DATE_FORMAT(re.`datetimeAdded`,'%d %b %Y %T') 	AS DateTimeAdded,
							UNIX_TIMESTAMP(re.`datetimeAdded`)				AS OrderByDate
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				ORDER BY	OrderByDate
				DESC";
				
		$result = $pdo->query($sql);
		$rowNum = $result->rowCount();
		
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
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$roomequipment[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'EquipmentAmount' => $row['EquipmentAmount'],							
							'DateTimeAdded' => $row['DateTimeAdded'],
							'MeetingRoomID' => $row['MeetingRoomID'],
							'MeetingRoomName' => $row['MeetingRoomName']							
						);
}

// Create the equipment list in HTML
include_once 'roomequipment.html.php';