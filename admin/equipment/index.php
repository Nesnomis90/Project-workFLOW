<?php 
// This is the index file for the EQUIPMENT folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add equipment form
function clearAddEquipmentSessions(){
	unset($_SESSION['AddEquipmentDescription']);
	unset($_SESSION['AddEquipmentName']);
	unset($_SESSION['LastEquipmentID']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit equipment form
function clearEditEquipmentSessions(){
	unset($_SESSION['EditEquipmentOriginalInfo']);
	unset($_SESSION['EditEquipmentDescription']);
	unset($_SESSION['EditEquipmentName']);
	unset($_SESSION['EditEquipmentEquipmentID']);
}

// Function to check if user inputs for equipment are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['EquipmentName']) AND !$invalidInput){
		$equipmentName = trim($_POST['EquipmentName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddEquipmentError'] = "An equipment cannot be added without a name!";
	}
	if(isSet($_POST['EquipmentDescription']) AND !$invalidInput){
		$equipmentDescription = trim($_POST['EquipmentDescription']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddEquipmentError'] = "An equipment cannot be added without a description!";
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedEquipmentName = trimExcessWhitespace($equipmentName);
	$validatedEquipmentDescription = trimExcessWhitespaceButLeaveLinefeed($equipmentDescription);

	// Do actual input validation
	if(validateString($validatedEquipmentName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddEquipmentError'] = "Your submitted equipment name has illegal characters in it.";
	}
	if(validateString($validatedEquipmentDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddEquipmentError'] = "Your submitted equipment description has illegal characters in it.";
	}

	// Are values actually filled in?
	if($validatedEquipmentName == "" AND !$invalidInput){
		$_SESSION['AddEquipmentError'] = "You need to fill in a name for your equipment.";	
		$invalidInput = TRUE;
	}
	if($validatedEquipmentDescription == "" AND !$invalidInput){
		$_SESSION['AddEquipmentError'] = "You need to fill in a description for your equipment.";
		$invalidInput = TRUE;
	}

	// Check if input length is allowed
		// EquipmentName
		// Uses same limit as display name (max 255 chars)
	$invalidEquipmentName = isLengthInvalidDisplayName($validatedEquipmentName);
	if($invalidEquipmentName AND !$invalidInput){
		$_SESSION['AddEquipmentError'] = "The equipment name submitted is too long.";	
		$invalidInput = TRUE;
	}
		// EquipmentDescription
	$invalidEquipmentDescription = isLengthInvalidEquipmentDescription($validatedEquipmentDescription);
	if($invalidEquipmentDescription AND !$invalidInput){
		$_SESSION['AddEquipmentError'] = "The equipment description submitted is too long.";
		$invalidInput = TRUE;
	}

	// Check if the equipment already exists (based on name).
	$nameChanged = TRUE;
	if(isSet($_SESSION['EditEquipmentOriginalInfo'])){
		$originalEquipmentName = strtolower($_SESSION['EditEquipmentOriginalInfo']['EquipmentName']);
		$newEquipmentName = strtolower($validatedEquipmentName);

		if($originalEquipmentName == $newEquipmentName){
			$nameChanged = FALSE;
		}
	}
	if($nameChanged AND !$invalidInput) {
		// Check if new name is taken
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT 	COUNT(*) 
					FROM 	`equipment`
					WHERE 	`name`= :EquipmentName';
			$s = $pdo->prepare($sql);
			$s->bindValue(':EquipmentName', $validatedEquipmentName);
			$s->execute();

			$pdo = null;

			$row = $s->fetch();

			if ($row[0] > 0)
			{
				// This name is already being used for an equipment
				$_SESSION['AddEquipmentError'] = "There is already an equipment with the name: " . $validatedEquipmentName . "!";
				$invalidInput = TRUE;
			}
			// Equipment name hasn't been used before
		}
		catch (PDOException $e)
		{
			$error = 'Error searching through equipment.' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}
	
return array($invalidInput, $validatedEquipmentDescription, $validatedEquipmentName);
}

// If admin wants to be able to delete equipment it needs to enabled first
if (isSet($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['equipmentEnableDelete'] = TRUE;
	$refreshEquipment = TRUE;
}

// If admin wants to be disable equipment deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['equipmentEnableDelete']);
	$refreshEquipment = TRUE;
}

// If admin wants to delete no longer wanted equipment
if(isSet($_POST['action']) AND $_POST['action'] == 'Delete'){
	// Delete equipment from database
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

	$_SESSION['EquipmentUserFeedback'] = "Successfully removed the equipment.";

	// Add a log event that an equipment has been Deleted
	try
	{
		// Save a description with information about the equipment that was Deleted
		$description = "The equipment: " . $_POST['EquipmentName'] . " was removed by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Equipment Removed'
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

	// Load company list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin wants to add equipment to the database
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Add Equipment') OR
	(isSet($_SESSION['refreshAddEquipment']) AND $_SESSION['refreshAddEquipment'])
	){
	// Confirm we've refreshed
	unset($_SESSION['refreshAddEquipment']);

	// Set form variables to be ready for adding values
	$pageTitle = 'New Equipment';
	$EquipmentName = '';
	$EquipmentDescription = '';
	$EquipmentID = '';
	$button = 'Confirm Equipment';

	if(isSet($_SESSION['AddEquipmentDescription'])){
		$EquipmentDescription = $_SESSION['AddEquipmentDescription'];
		unset($_SESSION['AddEquipmentDescription']);
	}

	if(isSet($_SESSION['AddEquipmentName'])){
		$EquipmentName = $_SESSION['AddEquipmentName'];
		unset($_SESSION['AddEquipmentName']);
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the equipment
if(isSet($_POST['action']) AND $_POST['action'] == 'Confirm Equipment'){
	// Validate user inputs
	list($invalidInput, $validatedEquipmentDescription, $validatedEquipmentName) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['AddEquipmentDescription'] = $validatedEquipmentDescription;
		$_SESSION['AddEquipmentName'] = $validatedEquipmentName;

		$_SESSION['refreshAddEquipment'] = TRUE;
		header('Location: .');
		exit();
	}	

	// Add the equipment to the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `equipment` 
				SET			`name` = :EquipmentName,
							`description` = :EquipmentDescription';
		$s = $pdo->prepare($sql);
		$s->bindValue(':EquipmentName', $validatedEquipmentName);
		$s->bindValue(':EquipmentDescription', $validatedEquipmentDescription);
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

	$_SESSION['EquipmentUserFeedback'] = "Successfully added the equipment: " . $validatedEquipmentName;

		// Add a log event that an equipment was added
	try
	{
		// Save a description with information about the equipment that was added
		$description = "The equipment: " . $validatedEquipmentName . ", with description: " . 
		$validatedEquipmentDescription . " was added by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Equipment Added'
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

	clearAddEquipmentSessions();

	// Load equipment list webpage with new equipment
	header('Location: .');
	exit();
}

// If admin wants to null values while adding
if(isSet($_POST['add']) AND $_POST['add'] == 'Reset'){

	$_SESSION['AddEquipmentDescription'] = "";
	$_SESSION['AddEquipmentName'] = "";

	$_SESSION['refreshAddEquipment'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the equipment overview again
if (isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$_SESSION['EquipmentUserFeedback'] = "You cancelled your equipment creation.";
	$refreshEquipment = TRUE;
}

// if admin wants to edit equipment information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditEquipment']) AND $_SESSION['refreshEditEquipment']))
{

	// Check if we're activated by a user or by a forced refresh
	if(isSet($_SESSION['refreshEditEquipment']) AND $_SESSION['refreshEditEquipment']){
		//Confirm we've refreshed
		unset($_SESSION['refreshEditEquipment']);	

		// Get values we had before refresh
		if(isSet($_SESSION['EditEquipmentDescription'])){
			$EquipmentDescription = $_SESSION['EditEquipmentDescription'];
			unset($_SESSION['EditEquipmentDescription']);
		} else {
			$EquipmentDescription = '';
		}
		if(isSet($_SESSION['EditEquipmentName'])){
			$EquipmentName = $_SESSION['EditEquipmentName'];
			unset($_SESSION['EditEquipmentName']);
		} else {
			$EquipmentName = '';
		}
		if(isSet($_SESSION['EditEquipmentEquipmentID'])){
			$EquipmentID = $_SESSION['EditEquipmentEquipmentID'];
		}
	} else {
		// Make sure we don't have any remembered values in memory
		clearEditEquipmentSessions();
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
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditEquipmentOriginalInfo'] = $row;

			// Set the correct information
			$EquipmentID = $row['TheEquipmentID'];
			$EquipmentName = $row['EquipmentName'];
			$EquipmentDescription = $row['EquipmentDescription'];
			$_SESSION['EditEquipmentEquipmentID'] = $EquipmentID;

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
	}

	// Set always correct information
	$pageTitle = 'Edit Equipment';
	$button = 'Edit Equipment';	

	// Set original values
	$originalEquipmentName = $_SESSION['EditEquipmentOriginalInfo']['EquipmentName'];
	$originalEquipmentDescription = $_SESSION['EditEquipmentOriginalInfo']['EquipmentDescription'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Equipment'){
	// Validate user inputs
	list($invalidInput, $validatedEquipmentDescription, $validatedEquipmentName) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditEquipmentDescription'] = $validatedEquipmentDescription;
		$_SESSION['EditEquipmentName'] = $validatedEquipmentName;

		$_SESSION['refreshEditEquipment'] = TRUE;
		header('Location: .');
		exit();
	}	

	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isSet($_SESSION['EditEquipmentOriginalInfo'])){
		$original = $_SESSION['EditEquipmentOriginalInfo'];
		unset($_SESSION['EditEquipmentOriginalInfo']);

		if($original['EquipmentName'] != $validatedEquipmentName){
			$numberOfChanges++;
		}
		if($original['EquipmentDescription'] != $validatedEquipmentDescription){
			$numberOfChanges++;
		}
		unset($original);
	}

	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'UPDATE 	`equipment`
					SET		`name` = :EquipmentName,
							`description` = :EquipmentDescription
					WHERE 	EquipmentID = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['EquipmentID']);
			$s->bindValue(':EquipmentName', $validatedEquipmentName);
			$s->bindValue(':EquipmentDescription', $validatedEquipmentDescription);
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

		$_SESSION['EquipmentUserFeedback'] = "Successfully updated the equipment: " . $validatedEquipmentName;
	} else {
		$_SESSION['EquipmentUserFeedback'] = "No changes were made to the equipment: " . $validatedEquipmentName;
	}

	clearEditEquipmentSessions();

	// Load equipment list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){

	$_SESSION['EditEquipmentName'] = $_SESSION['EditEquipmentOriginalInfo']['EquipmentName'];
	$_SESSION['EditEquipmentDescription'] = $_SESSION['EditEquipmentOriginalInfo']['EquipmentDescription'];

	$_SESSION['refreshEditEquipment'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the equipment overview again
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['EquipmentUserFeedback'] = "You cancelled your equipment editing.";
	$refreshEquipment = TRUE;
}

if(isSet($refreshEquipment) AND $refreshEquipment) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshEquipment);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearAddEquipmentSessions();
clearEditEquipmentSessions();

// Display equipment list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	$sql = 'SELECT 		e.`EquipmentID`									AS TheEquipmentID,
						e.`name`										AS EquipmentName,
						e.`description`									AS EquipmentDescription,
						e.`datetimeAdded`								AS DateTimeAdded,
						(
							SELECT 		GROUP_CONCAT(m.`name` separator ",\n")
							FROM 		`meetingroom` m
							INNER JOIN 	`roomequipment` re
							ON 			m.`meetingRoomID` = re.`meetingRoomID`
							WHERE		re.`equipmentID` = TheEquipmentID
							GROUP BY	re.`equipmentID`
						)												AS EquipmentIsInTheseRooms
			FROM 		`equipment` e
			ORDER BY	e.`name`';

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
	$error = 'Error getting equipment information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach($result AS $row){

	$addedDateTime = $row['DateTimeAdded'];
	$displayAddedDateTime = convertDatetimeToFormat($addedDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	// Create an array with the actual key/value pairs we want to use in our HTML
	$equipment[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'DateTimeAdded' => $displayAddedDateTime,
							'EquipmentIsInTheseRooms' => $row['EquipmentIsInTheseRooms']
						);
}
var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the equipment list in HTML
include_once 'equipment.html.php';
?>