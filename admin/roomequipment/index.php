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

// 	If admin wants to add a company to the database
// 	we load a new html form
if ((isset($_POST['action']) AND $_POST['action'] == 'Add Room Equipment') OR 
	(isset($_POST['action']) AND $_POST['action'] == 'Search'))
{	
	// This is a GOTO label.
	goToAddRoomEquipment:
	// Get info about equipment and rooms from the database
	try
	{
		if (isset($_POST['action']) AND $_POST['action'] == 'Search'){
			$equipmentsearchstring = $_POST['equipmentsearchstring'];
			$meetingroomsearchstring = $_POST['meetingroomsearchstring'];
			echo "Search button clicked <br />";
		} else {
			$equipmentsearchstring = '';
			$meetingroomsearchstring = '';
		}
		
		// Get all equipment and meeting rooms so the admin can search/choose from them
			//Meeting Rooms
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';			
			
		$pdo = connect_to_db();
		$sql = 'SELECT 	`meetingRoomID`	AS MeetingRoomID,
						`name` 			AS MeetingRoomName
				FROM 	`meetingroom`';
				
		echo "meetingroomsearchstring is: $meetingroomsearchstring <br />";
		if ($meetingroomsearchstring != ''){
			$sqladd = " WHERE `name` LIKE :search";
			$sql = $sql . $sqladd;	
			
			$finalmeetingroomsearchstring = '%' . $meetingroomsearchstring . '%';
			
			$s = $pdo->prepare($sql);
			$s->bindValue(':search', $finalmeetingroomsearchstring);
			$s->execute();
			$result = $s->fetchAll();
			echo "Size of result: " . sizeOf($result) . "<br />";
		} else {
			$result = $pdo->query($sql);
			echo "Size of result: " . sizeOf($result) . "<br />";
		}
		echo "Meeting Room SQL is: $sql <br />";
			
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$meetingrooms[] = array(
									'MeetingRoomID' => $row['MeetingRoomID'],
									'MeetingRoomName' => $row['MeetingRoomName']
									);
		}
		
			// Equipment
		$sql = 'SELECT 	`EquipmentID`,
						`name` 			AS EquipmentName
				FROM 	`equipment`';

		echo "equipmentsearchstring is: $equipmentsearchstring <br />";
		if ($equipmentsearchstring != ''){
			$sqladd = " WHERE `name` LIKE :search";
			$sql = $sql . $sqladd;
			
			$finalequipmentsearchstring = '%' . $equipmentsearchstring . '%';
			
			$s = $pdo->prepare($sql);
			$s->bindValue(":search", $finalequipmentsearchstring);
			$s->execute();
			$result = $s->fetchAll();
			echo "Size of result: " . sizeOf($result) . "<br />";
			
		} else {
			$result = $pdo->query($sql);
			echo "Size of result: " . sizeOf($result) . "<br />";
		}
		echo "Equipment SQL is: $sql <br />";
		
		// Get the rows of information from the query
		// This will be used to create a dropdown list in HTML
		foreach($result as $row){
			$equipment[] = array(
									'EquipmentID' => $row['EquipmentID'],
									'EquipmentName' => $row['EquipmentName']
									);
		}
			
		$EquipmentAmount = 0;
			
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
	// Make sure we only do this if user filled out all values
	// TO-DO:   GOTO is a bad practice. Find a solution?
	// 			THIS IS ONLY NEEDED AS LONG AS WE DON'T HAVE ANY
	// 			REQUIRED ATTRIBUTES ON THE SELECT FIELDS
	if($_POST['EquipmentID'] == '' AND $_POST['MeetingRoomID'] == '' AND $_POST['EquipmentAmount'] == ''){
		// We didn't have enough values filled in. "go back" to roomequipment fill in
		echo "<b>Need to select an equipment, a meetingroom and an amount first!</b><br />";
		goto goToAddRoomEquipment;
		exit();
	} elseif(($_POST['EquipmentID'] != '' OR $_POST['EquipmentAmount'] != '') AND $_POST['MeetingRoomID'] == '' ){
		echo "<b>Need to select a Meeting Room first!</b><br />";
		goto goToAddRoomEquipment;
		exit();
	} elseif(($_POST['EquipmentAmount'] != '' OR $_POST['MeetingRoomID'] != '') AND $_POST['EquipmentID'] == ''){
		echo "<b>Need to select an Equipment first!</b><br />";
		goto goToAddRoomEquipment;
		exit();
	} elseif(($_POST['EquipmentID'] != '' OR $_POST['MeetingRoomID'] != '') AND $_POST['EquipmentAmount'] == ''){
		echo "<b>Need to select an Equipment Amount first!</b><br />";
		goto goToAddRoomEquipment;
		exit();
	}
	
	// TO-DO: CHECK THAT EQUIPMENT ISN'T ALREADY IN THE ROOM
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
		$s->bindValue(':MeetingRoomID', $_POST['MeetingRoomID']);
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

// Display roomequipment list
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