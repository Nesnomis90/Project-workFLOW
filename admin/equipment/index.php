<?php 
// This is the index file for the EQUIPMENT folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// If admin wants to remove unavailable equipment
if(isset($_POST['action']) AND $_POST['action'] == 'Remove'){
	// Remove equipment from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = 'DELETE FROM `equipment` 
				WHERE 		`EquipmentID` = :EquipmentID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->execute();
		
		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error removing equipment: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
	
	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add equipment to the database
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Add Equipment')
{
	// Set form variables to be ready for adding values
	$pageTitle = 'New Equipment';
	$EquipmentName = '';
	$EquipmentDescription = '';
	$EquipmentID = '';
	$button = 'Confirm Equipment';
	
	// We want a reset all fields button while adding a new meeting room
	$reset = 'reset';
	
	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the equipment
if (isset($_POST['action']) AND $_POST['action'] == 'Confirm Equipment')
{
	// Add the equipment to the database
	try
	{		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `equipment` 
				SET			`name` = :EquipmentName,
							`description` = :EquipmentDescription';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentName', $_POST['EquipmentName']);
		$s->bindValue(':EquipmentDescription', $_POST['EquipmentDescription']);		
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted equipment to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Load equipment list webpage with new equipment
	header('Location: .');
	exit();
}

// if admin wants to edit equipment information
// we load a new html form
if (isset($_POST['action']) AND $_POST['action'] == 'Edit')
{
	// Get information from database again on the selected meeting room
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 		`EquipmentID`					AS TheEquipmentID,
							`name`							AS EquipmentName,
							`description`					AS EquipmentDescription
				FROM 		`equipment`
				WHERE		`EquipmentID` = :EquipmentID";
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentID', $_POST['EquipmentID']);
		$s->execute();
		
		// Create an array with the row information we retrieved
		$row = $s->fetch();

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error fetching meeting room details.';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Set the correct information
	$pageTitle = 'Edit User';
	$EquipmentID = $row['TheEquipmentID'];
	$EquipmentName = $row['EquipmentName'];
	$EquipmentDescription = $row['EquipmentDescription'];
	$button = 'Edit Equipment';
	
	// Don't want a reset button to blank all fields while editing
	$reset = 'hidden';
	
	// Change to the template we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if (isset($_POST['action']) AND $_POST['action'] == 'Edit Equipment')
{
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'UPDATE `equipment`
				SET		`name` = :EquipmentName,
						description = :EquipmentDescription
				WHERE 	EquipmentID = :id';
		$s = $pdo->prepare($sql);
		$s->bindValue(':id', $_POST['EquipmentID']);
		$s->bindValue(':EquipmentName', $_POST['EquipmentName']);
		$s->bindValue(':EquipmentDescription', $_POST['EquipmentDescription']);
		$s->execute();
		
		// Close the connection
		$pdo = Null;
	}
	catch (PDOException $e)
	{
		$error = 'Error updating submitted equipment: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Load user list webpage with updated database
	header('Location: .');
	exit();
}

// If the user clicks any cancel buttons he'll be directed back to the equipment page again
if (isset($_POST['action']) AND $_POST['action'] == 'Cancel'){
	// Doesn't actually need any code to work, since it happends automatically when a submit
	// occurs. *it* being doing the normal startup code.
	// Might be useful for something later?
	echo "<b>Cancel button clicked. Taking you back to /admin/equipment/!</b><br />";
}


// Display equipment list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	
	$sql = "SELECT 		e.`EquipmentID`									AS TheEquipmentID,
						e.`name`										AS EquipmentName,
						e.`description`									AS EquipmentDescription,
						DATE_FORMAT(e.`datetimeAdded`,'%d %b %Y %T') 	AS DateTimeAdded,
						UNIX_TIMESTAMP(e.`datetimeAdded`)				AS OrderByDate,
						GROUP_CONCAT(m.`name` separator ', ')			AS EquipmentIsInTheseRooms
			FROM 		`equipment` e
			LEFT JOIN 	`roomequipment` re
			ON 			e.`EquipmentID` = re.`EquipmentID`
			LEFT JOIN 	`meetingroom` m
			ON 			m.`meetingRoomID` = re.`meetingRoomID`
			GROUP BY 	e.`EquipmentID`
			ORDER BY	OrderByDate
			DESC";
			
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();
	
	//close connection
	$pdo = null;
		
}
catch (PDOException $e)
{
	$error = 'Error getting equipment information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}	

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$equipment[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'DateTimeAdded' => $row['DateTimeAdded'],
							'EquipmentIsInTheseRooms' => $row['EquipmentIsInTheseRooms']							
						);
}

// Create the equipment list in HTML
include_once 'equipment.html.php';
?>