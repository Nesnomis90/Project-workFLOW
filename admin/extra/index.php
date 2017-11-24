<?php 
// This is the index file for the Extra folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the add Extra form
function clearAddExtraSessions(){
	unset($_SESSION['AddExtraDescription']);
	unset($_SESSION['AddExtraName']);
	unset($_SESSION['AddExtraPrice']);
	unset($_SESSION['AddExtraIsAlternative']);
	unset($_SESSION['LastExtraID']);
}

// Function to clear sessions used to remember user inputs on refreshing the edit Extra form
function clearEditExtraSessions(){
	unset($_SESSION['EditExtraOriginalInfo']);
	unset($_SESSION['EditExtraDescription']);
	unset($_SESSION['EditExtraName']);
	unset($_SESSION['EditExtraPrice']);
	unset($_SESSION['EditExtraIsAlternative']);
	unset($_SESSION['EditExtraExtraID']);
}

// Function to check if user inputs for Extra are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['ExtraName']) AND !$invalidInput){
		$extraName = trim($_POST['ExtraName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "An Extra cannot be added without a name!";
	}
	if(isSet($_POST['ExtraDescription']) AND !$invalidInput){
		$extraDescription = trim($_POST['ExtraDescription']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "An Extra cannot be added without a description!";
	}
	if(isSet($_POST['ExtraDescription']) AND !$invalidInput){
		$extraPrice = trim($_POST['ExtraPrice']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "An Extra cannot be added without a price!";
	}
	if(isSet($_POST['isAlternative']) AND $_POST['isAlternative'] == 1 AND !$invalidInput){
		$extraIsAlternative = 1;
	} else {
		$extraIsAlternative = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedExtraName = trimExcessWhitespace($extraName);
	$validatedExtraDescription = trimExcessWhitespaceButLeaveLinefeed($extraDescription);
	$validatedExtraPrice = trimExcessWhitespace($extraPrice);
	$validatedIsAlternative = $extraIsAlternative;

	// Do actual input validation
	if(validateString($validatedExtraName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "Your submitted Extra name has illegal characters in it.";
	}
	if(validateString($validatedExtraDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "Your submitted Extra description has illegal characters in it.";
	}
	if(validateFloatNumber($validatedExtraPrice) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddExtraError'] = "Your submitted Extra price has illegal characters in it.";
	}

	// Are values actually filled in?
	if($validatedExtraName == "" AND !$invalidInput){
		$_SESSION['AddExtraError'] = "You need to fill in a name for your Extra.";	
		$invalidInput = TRUE;
	}
	if($validatedExtraDescription == "" AND !$invalidInput){
		$_SESSION['AddExtraError'] = "You need to fill in a description for your Extra.";
		$invalidInput = TRUE;
	}
	if($validatedExtraPrice == "" AND !$invalidInput){
		$_SESSION['AddExtraError'] = "You need to fill in the set price for your Extra.";
		$invalidInput = TRUE;
	}

	// Check if input length is allowed
		// ExtraName
		// Uses same limit as display name (max 255 chars)
	$invalidExtraName = isLengthInvalidExtraName($validatedExtraName);
	if($invalidExtraName AND !$invalidInput){
		$_SESSION['AddExtraError'] = "The extra name submitted is too long.";	
		$invalidInput = TRUE;
	}
		// ExtraDescription
	$invalidExtraDescription = isLengthInvalidExtraDescription($validatedExtraDescription);
	if($invalidExtraDescription AND !$invalidInput){
		$_SESSION['AddExtraError'] = "The extra description submitted is too long.";
		$invalidInput = TRUE;
	}
	$invalidExtraPrice = isNumberInvalidCreditsMonthlyPrice($validatedExtraPrice);
	if($invalidExtraPrice AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The Extra price is too big.";
		$invalidInput = TRUE;
	}

	// Check if the Extra already exists (based on name).
	$nameChanged = TRUE;
	if(isSet($_SESSION['EditExtraOriginalInfo'])){
		$originalExtraName = strtolower($_SESSION['EditExtraOriginalInfo']['ExtraName']);
		$newExtraName = strtolower($validatedExtraName);

		if($originalExtraName == $newExtraName){
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
					FROM 	`extra`
					WHERE 	`name`= :ExtraName';
			$s = $pdo->prepare($sql);
			$s->bindValue(':ExtraName', $validatedExtraName);
			$s->execute();

			$pdo = null;

			$row = $s->fetch();

			if($row[0] > 0){
				// This name is already being used for an Extra
				$_SESSION['AddExtraError'] = "There is already an Extra with the name: " . $validatedExtraName . "!";
				$invalidInput = TRUE;
			}
			// Extra name hasn't been used before
		}
		catch (PDOException $e)
		{
			$error = 'Error searching through Extra.' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	return array($invalidInput, $validatedExtraDescription, $validatedExtraName, $validatedExtraPrice, $validatedIsAlternative);
}

// If admin wants to be able to delete Extra it needs to enabled first
if(isSet($_POST['action']) AND $_POST['action'] == "Enable Delete"){
	$_SESSION['ExtraEnableDelete'] = TRUE;
	$refreshExtra = TRUE;
}

// If admin wants to be disable Extra deletion
if (isSet($_POST['action']) AND $_POST['action'] == "Disable Delete"){
	unset($_SESSION['ExtraEnableDelete']);
	$refreshExtra = TRUE;
}

// If admin wants to delete no longer wanted Extra
if(isSet($_POST['action']) AND $_POST['action'] == 'Delete'){
	// Delete Extra from database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = 'DELETE FROM `extra` 
				WHERE 		`extraID` = :ExtraID';
		$s = $pdo->prepare($sql);
		$s->bindValue(':ExtraID', $_POST['ExtraID']);
		$s->execute();

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error removing Extra: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	$_SESSION['ExtraUserFeedback'] = "Successfully removed the Extra.";

	// Add a log event that an Extra has been Deleted
	try
	{
		// Save a description with information about the Extra that was Deleted
		$description = "The Extra: " . $_POST['ExtraName'] . " was removed by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Extra Removed'
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

// If admin wants to add Extra to the database
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Add Extra') OR
	(isSet($_SESSION['refreshAddExtra']) AND $_SESSION['refreshAddExtra'])
	){
	// Confirm we've refreshed
	unset($_SESSION['refreshAddExtra']);

	// Set form variables to be ready for adding values
	$pageTitle = 'New Extra';
	$extraName = '';
	$extraDescription = '';
	$extraPrice = 0;
	$extraIsAlternative = 0;
	$extraID = '';
	$button = 'Confirm Extra';

	if(isSet($_SESSION['AddExtraDescription'])){
		$extraDescription = $_SESSION['AddExtraDescription'];
		unset($_SESSION['AddExtraDescription']);
	}

	if(isSet($_SESSION['AddExtraName'])){
		$extraName = $_SESSION['AddExtraName'];
		unset($_SESSION['AddExtraName']);
	}
	
	if(isSet($_SESSION['AddExtraPrice'])){
		$extraPrice = $_SESSION['AddExtraPrice'];
		unset($_SESSION['AddExtraPrice']);
	}

	if(isSet($_SESSION['AddExtraIsAlternative'])){
		$extraIsAlternative = $_SESSION['AddExtraIsAlternative'];
		unset($_SESSION['AddExtraIsAlternative']);
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change form
	include 'form.html.php';
	exit();
}

// When admin has added the needed information and wants to add the Extra
if(isSet($_POST['action']) AND $_POST['action'] == 'Confirm Extra'){
	// Validate user inputs
	list($invalidInput, $validatedExtraDescription, $validatedExtraName, $validatedExtraPrice, $validatedIsAlternative) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['AddExtraDescription'] = $validatedExtraDescription;
		$_SESSION['AddExtraName'] = $validatedExtraName;
		$_SESSION['AddExtraPrice'] = $validatedExtraPrice;
		$_SESSION['AddExtraIsAlternative'] = $validatedIsAlternative;

		$_SESSION['refreshAddExtra'] = TRUE;
		header('Location: .');
		exit();
	}

	// Add the Extra to the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo = connect_to_db();
		$sql = 'INSERT INTO `extra` 
				SET			`name` = :ExtraName,
							`description` = :ExtraDescription,
							`price` = :ExtraPrice,
							`isAlternative` = :ExtraIsAlternative';
		$s = $pdo->prepare($sql);
		$s->bindValue(':ExtraName', $validatedExtraName);
		$s->bindValue(':ExtraDescription', $validatedExtraDescription);
		$s->bindValue(':ExtraPrice', $validatedExtraPrice);
		$s->bindValue(':ExtraIsAlternative', $validatedIsAlternative);
		$s->execute();

		//Close the connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error adding submitted Extra to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['ExtraUserFeedback'] = "Successfully added the Extra: " . $validatedExtraName;

		// Add a log event that an Extra was added
	try
	{
		// Save a description with information about the Extra that was added
		$description = 	"The Extra: $validatedExtraName" . 
						"\nwith the description: $validatedExtraDescription" . 
						"\nand the price: $validatedExtraPrice" .
						"\nwas added by: " . $_SESSION['LoggedInUserName'];

		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Extra Added'
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

	clearAddExtraSessions();

	// Load Extra list webpage with new Extra
	header('Location: .');
	exit();
}

// If admin wants to null values while adding
if(isSet($_POST['add']) AND $_POST['add'] == 'Reset'){

	clearAddExtraSessions();

	$_SESSION['refreshAddExtra'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Extra overview again
if(isSet($_POST['add']) AND $_POST['add'] == 'Cancel'){
	$_SESSION['ExtraUserFeedback'] = "You cancelled your Extra creation.";
	$refreshExtra = TRUE;
}

// if admin wants to edit Extra information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditExtra']) AND $_SESSION['refreshEditExtra'])
	){

	// Check if we're activated by a user or by a forced refresh
	if(isSet($_SESSION['refreshEditExtra']) AND $_SESSION['refreshEditExtra']){
		//Confirm we've refreshed
		unset($_SESSION['refreshEditExtra']);	

		// Get values we had before refresh
		if(isSet($_SESSION['EditExtraDescription'])){
			$extraDescription = $_SESSION['EditExtraDescription'];
			unset($_SESSION['EditExtraDescription']);
		} else {
			$extraDescription = '';
		}
		if(isSet($_SESSION['EditExtraName'])){
			$extraName = $_SESSION['EditExtraName'];
			unset($_SESSION['EditExtraName']);
		} else {
			$extraName = '';
		}
		if(isSet($_SESSION['EditExtraPrice'])){
			$extraPrice = $_SESSION['EditExtraPrice'];
			unset($_SESSION['EditExtraPrice']);
		} else {
			$extraPrice = 0;
		}
		if(isSet($_SESSION['EditExtraIsAlternative'])){
			$extraIsAlternative = $_SESSION['EditExtraIsAlternative'];
			unset($_SESSION['EditExtraIsAlternative']);
		} else {
			$extraIsAlternative = 0;
		}
		if(isSet($_SESSION['EditExtraExtraID'])){
			$extraID = $_SESSION['EditExtraExtraID'];
		}
	} else {
		// Make sure we don't have any remembered values in memory
		clearEditExtraSessions();
		// Get information from database again on the selected meeting room
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = "SELECT 		`extraID`				AS TheExtraID,
								`name`					AS ExtraName,
								`description`			AS ExtraDescription,
								`price`					AS ExtraPrice,
								`isAlternative`			AS ExtraIsAlternative
					FROM 		`extra`
					WHERE		`extraID` = :ExtraID";

			$s = $pdo->prepare($sql);
			$s->bindValue(':ExtraID', $_POST['ExtraID']);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditExtraOriginalInfo'] = $row;

			// Set the correct information
			$extraID = $row['TheExtraID'];
			$extraName = $row['ExtraName'];
			$extraDescription = $row['ExtraDescription'];
			$extraPrice = $row['ExtraPrice'];
			$extraIsAlternative = $row['ExtraIsAlternative'];
			$_SESSION['EditExtraExtraID'] = $extraID;

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
	$pageTitle = 'Edit Extra';
	$button = 'Edit Extra';	

	// Set original values
	$originalExtraName = $_SESSION['EditExtraOriginalInfo']['ExtraName'];
	$originalExtraDescription = $_SESSION['EditExtraOriginalInfo']['ExtraDescription'];
	$originalExtraPrice = $_SESSION['EditExtraOriginalInfo']['ExtraPrice'];
	$originalExtraIsAlternative = $_SESSION['EditExtraOriginalInfo']['ExtraIsAlternative'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'form.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Extra'){
	// Validate user inputs
	list($invalidInput, $validatedExtraDescription, $validatedExtraName, $validatedExtraPrice, $validatedIsAlternative) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditExtraDescription'] = $validatedExtraDescription;
		$_SESSION['EditExtraName'] = $validatedExtraName;
		$_SESSION['EditExtraPrice'] = $validatedExtraPrice;
		$_SESSION['EditExtraIsAlternative'] = $validatedIsAlternative;

		$_SESSION['refreshEditExtra'] = TRUE;
		header('Location: .');
		exit();
	}	

	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isSet($_SESSION['EditExtraOriginalInfo'])){
		$original = $_SESSION['EditExtraOriginalInfo'];
		unset($_SESSION['EditExtraOriginalInfo']);

		if($original['ExtraName'] != $validatedExtraName){
			$numberOfChanges++;
		}
		if($original['ExtraDescription'] != $validatedExtraDescription){
			$numberOfChanges++;
		}
		if($original['ExtraPrice'] != $validatedExtraPrice){
			$numberOfChanges++;
		}
		if($original['ExtraIsAlternative'] != $validatedIsAlternative){
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
			$sql = 'UPDATE 	`extra`
					SET		`name` = :ExtraName,
							`description` = :ExtraDescription,
							`price` = :ExtraPrice,
							`isAlternative` = :ExtraIsAlternative,
							`dateTimeUpdated` = CURRENT_TIMESTAMP
					WHERE 	extraID = :ExtraID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':ExtraID', $_POST['ExtraID']);
			$s->bindValue(':ExtraName', $validatedExtraName);
			$s->bindValue(':ExtraDescription', $validatedExtraDescription);
			$s->bindValue(':ExtraPrice', $validatedExtraPrice);
			$s->bindValue(':ExtraIsAlternative', $validatedIsAlternative);
			$s->execute();

			// Close the connection
			$pdo = Null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating submitted Extra: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		$_SESSION['ExtraUserFeedback'] = "Successfully updated the Extra: " . $validatedExtraName;
	} else {
		$_SESSION['ExtraUserFeedback'] = "No changes were made to the Extra: " . $validatedExtraName;
	}

	clearEditExtraSessions();

	// Load Extra list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){

	$_SESSION['EditExtraName'] = $_SESSION['EditExtraOriginalInfo']['ExtraName'];
	$_SESSION['EditExtraDescription'] = $_SESSION['EditExtraOriginalInfo']['ExtraDescription'];
	$_SESSION['EditExtraPrice'] = $_SESSION['EditExtraOriginalInfo']['ExtraPrice'];
	$_SESSION['EditExtraIsAlternative'] = $_SESSION['EditExtraOriginalInfo']['ExtraIsAlternative'];

	$_SESSION['refreshEditExtra'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Extra overview again
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['ExtraUserFeedback'] = "You cancelled your Extra editing.";
	$refreshExtra = TRUE;
}

if(isSet($refreshExtra) AND $refreshExtra) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshExtra);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearAddExtraSessions();
clearEditExtraSessions();

// Display Extra list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	$sql = 'SELECT 		ex.`extraID`										AS TheExtraID,
						ex.`name`											AS ExtraName,
						ex.`description`									AS ExtraDescription,
						ex.`price`											AS ExtraPrice,
						ex.`isAlternative`									AS ExtraIsAlternative,
						ex.`datetimeAdded`									AS DateTimeAdded,
						ex.`dateTimeUpdated`								AS DateTimeUpdated,
						(
							SELECT 		COUNT(*)
							FROM 		`orders` o
							INNER JOIN 	`booking` b
							ON 			b.`orderID` = o.`orderID`
							INNER JOIN	`extraorders` eo
							ON			eo.`orderID` = o.`orderID`
							WHERE		eo.`extraID` = ex.`extraID`
							AND			b.`dateTimeCancelled` IS NULL
							AND			b.`actualEndDateTime` IS NULL
							AND			b.`endDateTime` > CURRENT_TIMESTAMP
						)													AS ExtraIsInThisManyActiveOrders
			FROM 		`extra` ex
			ORDER BY	ex.`name`';

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
	$error = 'Error getting Extra information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach($result AS $row){

	$dateTimeAdded = $row['DateTimeAdded'];
	$displayDateTimeAdded = convertDatetimeToFormat($dateTimeAdded , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateTimeUpdated = $row['DateTimeUpdated'];
	$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$extraIsAlternative = $row['ExtraIsAlternative'];

	if($extraIsAlternative == 1){
		$displayExtraType = "Alternative";
	} else {
		$displayExtraType = "Normal";
	}

	$price = $row['ExtraPrice'];
	$displayPrice = convertToCurrency($price);

	// Create an array with the actual key/value pairs we want to use in our HTML
	$extra[] = array(
							'TheExtraID' => $row['TheExtraID'],
							'ExtraName' => $row['ExtraName'],
							'ExtraDescription' => $row['ExtraDescription'],
							'ExtraPrice' => $displayPrice,
							'ExtraType' => $displayExtraType,
							'DateTimeAdded' => $displayDateTimeAdded,
							'DateTimeUpdated' => $displayDateTimeUpdated,
							'ExtraIsInThisManyActiveOrders' => $row['ExtraIsInThisManyActiveOrders']
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Extra list in HTML
include_once 'extra.html.php';
?>