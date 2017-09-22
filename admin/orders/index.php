<?php 
// This is the index file for the Orders folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the edit Order form
function clearEditOrderSessions(){
	unset($_SESSION['EditOrderOriginalInfo']);
	unset($_SESSION['EditOrderFeedback']);
	unset($_SESSION['EditOrderAdminNote']);
	unset($_SESSION['EditOrderIsApproved']);
	unset($_SESSION['EditOrderOrderID']);
}

// Function to check if user inputs for Order are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['OrderFeedback']) AND !$invalidInput){
		$orderFeedback = trim($_POST['OrderFeedback']);
	} else {
		// Doesn't need to be set.
		$orderFeedback = NULL;
	}
	if(isSet($_POST['AdminNote']) AND !$invalidInput){
		$adminNote = trim($_POST['AdminNote']);
	} else {
		// Doesn't need to be set.
		$adminNote = NULL;
	}
	if(isSet($_POST['isApproved']) AND $_POST['isApproved'] == 1 AND !$invalidInput){
		$orderIsApproved = 1;
	} else {
		$orderIsApproved = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedOrderFeedback = trimExcessWhitespaceButLeaveLinefeed($orderFeedback);
	$validatedAdminNote = trimExcessWhitespaceButLeaveLinefeed($adminNote);
	$validatedIsApproved = $orderIsApproved;

	// Do actual input validation
	if(validateString($validatedAdminNote) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddOrderError'] = "Your submitted Admin Note has illegal characters in it.";
	}
	if(validateString($validatedOrderFeedback) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddOrderError'] = "Your submitted Order feedback has illegal characters in it.";
	}

	// Check if input length is allowed
		// OrderFeedback
	$invalidOrderFeedback = isLengthInvalidEquipmentDescription($validatedOrderFeedback);
	if($invalidOrderFeedback AND !$invalidInput){
		$_SESSION['AddOrderError'] = "The order feedback submitted is too long.";
		$invalidInput = TRUE;
	}
		// AdminNote
	$invalidAdminNote = isLengthInvalidEquipmentDescription($validatedAdminNote);
	if($invalidAdminNote AND !$invalidInput){
		$_SESSION['AddOrderError'] = "The admin note submitted is too long.";
		$invalidInput = TRUE;
	}

	return array($invalidInput, $validatedOrderFeedback, $validatedAdminNote, $validatedIsApproved);
}

// if admin wants to edit Order information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditOrder']) AND $_SESSION['refreshEditOrder'])
	){

	// Check if we're activated by a user or by a forced refresh
	if(isSet($_SESSION['refreshEditOrder']) AND $_SESSION['refreshEditOrder']){
		//Confirm we've refreshed
		unset($_SESSION['refreshEditOrder']);	

		// Get values we had before refresh
		if(isSet($_SESSION['EditOrderFeedback'])){
			$orderFeedback = $_SESSION['EditOrderFeedback'];
			unset($_SESSION['EditOrderFeedback']);
		} else {
			$orderFeedback = '';
		}
		if(isSet($_SESSION['EditOrderAdminNote'])){
			$orderAdminNote = $_SESSION['EditOrderAdminNote'];
			unset($_SESSION['EditOrderAdminNote']);
		} else {
			$orderAdminNote = '';
		}
		if(isSet($_SESSION['EditOrderIsApproved'])){
			$orderIsApproved = $_SESSION['EditOrderIsApproved'];
			unset($_SESSION['EditOrderIsApproved']);
		} else {
			$orderIsApproved = 0;
		}
		if(isSet($_SESSION['EditOrderOrderID'])){
			$orderID = $_SESSION['EditOrderOrderID'];
		}
	} else {
		// Make sure we don't have any remembered values in memory
		clearEditOrderSessions();

		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = "SELECT 		`orderID`				AS TheOrderID,
								`orderFeedback`			AS OrderFeedback,
								`orderApprovedByAdmin`	AS OrderApprovedByAdmin,
								`orderApprovedByStaff` 	AS OrderApprovedByStaff,
								`adminNote`				AS OrderAdminNote
					FROM 		`order`
					WHERE		`orderID` = :OrderID
					LIMIT 		1";

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $_POST['OrderID']);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditOrderOriginalInfo'] = $row;

			// Set the correct information
			$orderID = $row['TheOrderID'];
			$orderFeedback = $row['OrderFeedback'];
			$orderAdminNote = $row['OrderAdminNote'];

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}
			$_SESSION['EditOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditOrderOrderID'] = $orderID;

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
	$pageTitle = 'Edit Order';
	$button = 'Edit Order';	

	// Set original values
	$originalOrderName = $_SESSION['EditOrderOriginalInfo']['OrderName'];
	$originalOrderFeedback = $_SESSION['EditOrderOriginalInfo']['OrderFeedback'];
	$originalOrderAdminNote = $_SESSION['EditOrderOriginalInfo']['OrderAdminNote'];
	$originalOrderIsApproved = $_SESSION['EditOrderOriginalInfo']['OrderIsApproved'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'editorder.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Order'){
	// Validate user inputs
	list($invalidInput, $validatedOrderFeedback, $validatedAdminNote, $validatedIsApproved) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditOrderFeedback'] = $validatedOrderFeedback;
		$_SESSION['EditOrderAdminNote'] = $validatedAdminNote;
		$_SESSION['EditOrderIsApproved'] = $validatedIsApproved;

		$_SESSION['refreshEditOrder'] = TRUE;
		header('Location: .');
		exit();
	}	

	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isSet($_SESSION['EditOrderOriginalInfo'])){
		$original = $_SESSION['EditOrderOriginalInfo'];
		unset($_SESSION['EditOrderOriginalInfo']);

		if($original['OrderFeedback'] != $validatedOrderFeedback){
			$numberOfChanges++;
		}
		if($original['OrderAdminNote'] != $validatedAdminNote){
			$numberOfChanges++;
		}
		$setAsApproved = FALSE;
		if($original['OrderIsApproved'] != $validatedIsApproved){
			$numberOfChanges++;
			// Approved changed.
			if($validatedIsApproved == 1){
				// Only admin can edit this, so it's approved by admin
				$setAsApproved = TRUE;
				$approvedByAdmin = 1;
				$approvedByStaff = 0;
			} else {
				$approvedByAdmin = 0;
				$approvedByStaff = 0;
			}
		} else {
			// Approve didn't change
			$approvedByAdmin = $original['OrderApprovedByAdmin'];
			$approvedByStaff = $original['OrderApprovedByStaff'];
		}
		unset($original);
	}

	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			if($setAsApproved){
				$sql = 'UPDATE 	`orders`
						SET		`orderFeedback` = :OrderFeedback,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`approvedByUserID` = :approvedByUserID,
								`adminNote` = :adminNote
						WHERE 	orderID = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderFeedback', $validatedOrderFeedback);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderFeedback` = :OrderFeedback,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`adminNote` = :adminNote
						WHERE 	orderID = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderFeedback', $validatedOrderFeedback);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->execute();
			}

			// Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error updating submitted Order: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}

		$_SESSION['OrderUserFeedback'] = "Successfully updated the Order.";
	} else {
		$_SESSION['OrderUserFeedback'] = "No changes were made to the Order.";
	}

	clearEditOrderSessions();

	// Load Order list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){

	clearEditOrderSessions();

	$_SESSION['refreshEditOrder'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Order overview again
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['OrderUserFeedback'] = "You cancelled your Order editing.";
	$refreshOrder = TRUE;
}

if(isSet($refreshOrder) AND $refreshOrder) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshOrder);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearEditOrderSessions();

// Display Order list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	$sql = 'SELECT 		o.`orderID`					AS TheOrderID,
						o.`orderDescription`		AS OrderDescription,
						o.`orderFeedback`			AS OrderFeedback,
						o.`dateTimeCreated`			AS DateTimeCreated,
						o.`dateTimeUpdated`			AS DateTimeUpdated,
						o.`dateTimeApproved`		AS DateTimeApproved,
						o.`orderApprovedByUser`		AS OrderApprovedByUser,
						o.`orderApprovedByAdmin`	AS OrderApprovedByAdmin,
						o.`orderApprovedByStaff`	AS OrderApprovedByStaff,
						o.`priceCharged`			AS OrderPriceCharged,
						o.`adminNote`				AS OrderAdminNote,
						(
							SELECT 	CONCAT_WS(", ",`lastname`, `firstname`)
							FROM	`user`
							WHERE	`userID` = o.`approvedByUserID`
							LIMIT 	1
						)							AS OrderApprovedByUserName,
						COUNT(eo.`extraID`)			AS OrderAmount,
						b.`startDateTime`			AS OrderStartDateTime,
						b.`endDateTime`				AS OrderEndDateTime,
						b.`actualEndDateTime`		AS OrderBookingCompleted,
						b.`dateTimeCancelled`		AS OrderBookingCancelled
			FROM 		`orders` o
			INNER JOIN	`extraorders` eo
			ON 			eo.`orderID` = o.`orderID`
			INNER JOIN	`booking` b
			ON 			b.`orderID` = o.`orderID`
			GROUP BY	o.`orderID`';

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
	$error = 'Error getting Order information: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	exit();
}

// Create an array with the actual key/value pairs we want to use in our HTML
foreach($result AS $row){

	$dateTimeCreated = $row['DateTimeCreated'];
	$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateTimeUpdated = $row['DateTimeUpdated'];
	$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

	$orderIsApproved = FALSE;
	$orderApprovedBy = "Not Approved";

	if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
		$orderIsApproved = TRUE;
		if(!empty($row['OrderApprovedByUserName']) AND $row['OrderApprovedByUserName'] != ""){
			$orderApprovedBy = $row['OrderApprovedByUserName'];
		} else {
			$orderApprovedBy = "N/A - Deleted User";
		}
	}
	
	

	// Create an array with the actual key/value pairs we want to use in our HTML
	$order[] = array(
							'TheOrderID' => $row['TheOrderID'],
							'OrderName' => $row['OrderName'],
							'OrderFeedback' => $row['OrderFeedback'],
							'OrderPrice' => $displayPrice,
							'OrderType' => $displayOrderType,
							'DateTimeAdded' => $displayDateTimeAdded,
							'DateTimeUpdated' => $displayDateTimeUpdated,
							'OrderIsInThisManyActiveOrders' => $row['OrderIsInThisManyActiveOrders']
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'order.html.php';
?>