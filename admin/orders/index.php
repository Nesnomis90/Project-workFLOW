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
	unset($_SESSION['EditOrderCommunicationToUser']);
	unset($_SESSION['EditOrderAdminNote']);
	unset($_SESSION['EditOrderIsApproved']);
	unset($_SESSION['EditOrderOrderID']);
}

// Function to check if user inputs for Order are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['OrderCommunicationToUser']) AND !$invalidInput){
		$orderCommunicationToUser = trim($_POST['OrderCommunicationToUser']);
	} else {
		// Doesn't need to be set.
		$orderCommunicationToUser = "";
	}
	if(isSet($_POST['AdminNote']) AND !$invalidInput){
		$adminNote = trim($_POST['AdminNote']);
	} else {
		// Doesn't need to be set.
		$adminNote = NULL;
	}
	if(isSet($_POST['isApproved']) AND $_POST['isApproved'] == 1){
		$orderIsApproved = 1;
	} else {
		$orderIsApproved = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedOrderCommunicationToUser = trimExcessWhitespaceButLeaveLinefeed($orderCommunicationToUser);
	$validatedAdminNote = trimExcessWhitespaceButLeaveLinefeed($adminNote);
	$validatedIsApproved = $orderIsApproved;

	// Do actual input validation
	if(validateString($validatedAdminNote) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddOrderError'] = "Your submitted Admin Note has illegal characters in it.";
	}
	if(validateString($validatedOrderCommunicationToUser) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddOrderError'] = "Your submitted message to the user has illegal characters in it.";
	}

	// Check if input length is allowed
		// OrderCommunicationToUser
	$invalidOrderCommunicationToUser = isLengthInvalidEquipmentDescription($validatedOrderCommunicationToUser);
	if($invalidOrderCommunicationToUser AND !$invalidInput){
		$_SESSION['AddOrderError'] = "Your submitted message to the user is too long.";
		$invalidInput = TRUE;
	}
		// AdminNote
	$invalidAdminNote = isLengthInvalidEquipmentDescription($validatedAdminNote);
	if($invalidAdminNote AND !$invalidInput){
		$_SESSION['AddOrderError'] = "The admin note submitted is too long.";
		$invalidInput = TRUE;
	}

	return array($invalidInput, $validatedOrderCommunicationToUser, $validatedAdminNote, $validatedIsApproved);
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
		if(isSet($_SESSION['EditOrderCommunicationToUser'])){
			$orderCommunicationToUser = $_SESSION['EditOrderCommunicationToUser'];
			unset($_SESSION['EditOrderCommunicationToUser']);
		} else {
			$orderCommunicationToUser = "";
		}
		if(isSet($_SESSION['EditOrderAdminNote'])){
			$orderAdminNote = $_SESSION['EditOrderAdminNote'];
			unset($_SESSION['EditOrderAdminNote']);
		} else {
			$orderAdminNote = "";
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
			$sql = 'SELECT 		`orderID`						AS TheOrderID,
								`orderUserNotes`				AS OrderUserNotes,
								`orderCommunicationToUser`		AS OrderCommunicationToUser,
								`orderCommunicationFromUser`	AS OrderCommunicationFromUser,
								`orderApprovedByAdmin`			AS OrderApprovedByAdmin,
								`orderApprovedByStaff` 			AS OrderApprovedByStaff,
								`adminNote`						AS OrderAdminNote
					FROM 		`orders`
					WHERE		`orderID` = :OrderID
					LIMIT 		1';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $_POST['OrderID']);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditOrderOriginalInfo'] = $row;

			// Set the correct information
			$orderID = $row['TheOrderID'];
			$orderAdminNote = $row['OrderAdminNote'];

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}
			$_SESSION['EditOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditOrderOrderID'] = $orderID;

			$sql = 'SELECT 		ex.`name`												AS ExtraName,
								eo.`amount`												AS ExtraAmount,
								IFNULL(eo.`alternativePrice`, ex.`price`)				AS ExtraPrice,
								IFNULL(eo.`alternativeDescription`, ex.`description`)	AS ExtraDescription
					FROM 		`extraorders` eo
					INNER JOIN	`extra` ex
					ON 			ex.`extraID` = eo.`extraID`
					WHERE		eo.`orderID` = :OrderID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $_POST['OrderID']);
			$s->execute();

			$result = $s->fetchAll(PDO::FETCH_ASSOC);
			foreach($result AS $extra){
				$extraName = $extra['ExtraName'];
				$extraAmount = $extra['ExtraAmount'];
				$extraPrice = convertToCurrency($extra['ExtraPrice']);
				$extraDescription = $extra['ExtraDescription'];

				if(!isSet($orderContent)){
					$orderContent = "$extraAmount of $extraName ($extraDescription) at $extraPrice each.";
				} else {
					$orderContent .= "\n$extraAmount of $extraName ($extraDescription) at $extraPrice each.";
				}
			}

			$_SESSION['EditOrderOriginalInfo']['OrderContent'] = $orderContent;
			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching order details: ' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Set original values
	$originalOrderCommunicationToUser = $_SESSION['EditOrderOriginalInfo']['OrderCommunicationToUser'];
	$originalOrderCommunicationFromUser = $_SESSION['EditOrderOriginalInfo']['OrderCommunicationFromUser'];
	$originalOrderAdminNote = $_SESSION['EditOrderOriginalInfo']['OrderAdminNote'];
	$originalOrderIsApproved = $_SESSION['EditOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderUserNotes = $_SESSION['EditOrderOriginalInfo']['OrderUserNotes'];
	$originalOrderContent = $_SESSION['EditOrderOriginalInfo']['OrderContent'];

	if($originalOrderCommunicationToUser == ""){
		$originalOrderCommunicationToUser = "No messages sent to user.";
	}

	if($originalOrderCommunicationFromUser == ""){
		$originalOrderCommunicationFromUser = "No messages received from user.";
	}

	if(!isSet($orderCommunicationToUser)){
		$orderCommunicationToUser = "";
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'editorder.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Order'){
	// Validate user inputs
	list($invalidInput, $validatedOrderCommunicationToUser, $validatedAdminNote, $validatedIsApproved) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditOrderCommunicationToUser'] = $validatedOrderCommunicationToUser;
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

		$messageAdded = FALSE;
		if($validatedOrderCommunicationToUser != ""){
			$numberOfChanges++;
			$messageAdded = TRUE;

			$dateTimeNow = getDatetimeNow();
			$displayDateTimeNow = convertDatetimeToFormat($dateTimeNow, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$fullOrderCommunicationToUser = $original['OrderCommunicationToUser'] . "$displayDateTimeNow:\n" . $validatedOrderCommunicationToUser . "\n\n";
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
			if($setAsApproved AND $messageAdded){
				$sql = 'UPDATE 	`orders`
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`adminNote` = :adminNote
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderCommunicationToUser', $fullOrderCommunicationToUser);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} elseif($setAsApproved AND !$messageAdded){
				$sql = 'UPDATE 	`orders`
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`adminNote` = :adminNote
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} elseif(!$setAsApproved AND $messageAdded){
				$sql = 'UPDATE 	`orders`
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`adminNote` = :adminNote
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderCommunicationToUser', $fullOrderCommunicationToUser);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':approvedByUserID', NULL);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`adminNote` = :adminNote
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':approvedByUserID', NULL);
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
	$sql = 'SELECT 		o.`orderID`										AS TheOrderID,
						o.`orderUserNotes`								AS OrderUserNotes,
						o.`orderCommunicationToUser`					AS OrderCommunicationToUser,
						o.`orderCommunicationFromUser`					AS OrderCommunicationFromUser,
						o.`dateTimeCreated`								AS DateTimeCreated,
						o.`dateTimeUpdated`								AS DateTimeUpdated,
						o.`dateTimeApproved`							AS DateTimeApproved,
						o.`dateTimeCancelled`							AS DateTimeCancelled,
						o.`orderApprovedByUser`							AS OrderApprovedByUser,
						o.`orderApprovedByAdmin`						AS OrderApprovedByAdmin,
						o.`orderApprovedByStaff`						AS OrderApprovedByStaff,
						o.`priceCharged`								AS OrderPriceCharged,
						o.`adminNote`									AS OrderAdminNote,
						(
							SELECT 	CONCAT_WS(", ",`lastname`, `firstname`)
							FROM	`user`
							WHERE	`userID` = o.`approvedByUserID`
							LIMIT 	1
						)												AS OrderApprovedByUserName,
						GROUP_CONCAT(eo.`amount`, " - ", ex.`name` 
							SEPARATOR "\n")								AS OrderContent,
						b.`startDateTime`								AS OrderStartDateTime,
						b.`endDateTime`									AS OrderEndDateTime,
						b.`actualEndDateTime`							AS OrderBookingCompleted,
						b.`dateTimeCancelled`							AS OrderBookingCancelled,
						(
							SELECT 	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = b.`meetingRoomID`
							LIMIT 	1
						)												AS OrderRoomName
			FROM 		`orders` o
			INNER JOIN	`extraorders` eo
			ON 			eo.`orderID` = o.`orderID`
			INNER JOIN 	`extra` ex
			ON 			eo.`extraID` = ex.`extraID`
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
	if(!empty($row['DateTimeUpdated'])){
		$dateTimeUpdated = $row['DateTimeUpdated'];
		$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);		
	} else {
		$displayDateTimeUpdated = "N/A";
	}
	$dateTimeStart = $row['OrderStartDateTime'];
	$displayDateTimeStart = convertDatetimeToFormat($dateTimeStart , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	$dateTimeEnd = $row['OrderEndDateTime'];
	$displayDateTimeEnd = convertDatetimeToFormat($dateTimeEnd , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	if($row['DateTimeCancelled'] != NULL){
		$dateTimeCancelled = $row['DateTimeCancelled'];
		$displayDateTimeCancelled = convertDatetimeToFormat($dateTimeCancelled , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	} elseif($row['OrderBookingCancelled'] != NULL){
		$dateTimeCancelled = $row['OrderBookingCancelled'];
		$displayDateTimeCancelled = convertDatetimeToFormat($dateTimeCancelled , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	} else {
		$displayDateTimeCancelled = "";
	}

	if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
		$orderIsApproved = TRUE;
		$orderIsApprovedByStaff = TRUE;
		$displayOrderApprovedByStaff = "Yes";
		if(!empty($row['OrderApprovedByUserName'])){
			$orderApprovedBy = $row['OrderApprovedByUserName'];
		} else {
			$orderApprovedBy = "N/A - Deleted User";
		}
		$dateTimeApproved = $row['DateTimeApproved'];
		$displayDateTimeApproved = convertDatetimeToFormat($dateTimeApproved , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
	} else {
		$orderIsApproved = FALSE;
		$orderIsApprovedByStaff = FALSE;
		$displayOrderApprovedByStaff = "No";
		$orderApprovedBy = "";
		$displayDateTimeApproved = "";
	}

	if($row['OrderApprovedByUser'] == 1){
		$orderIsApprovedByUser = TRUE;
		$displayOrderApprovedByUser = "Yes";
	} else {
		$orderIsApprovedByUser = FALSE;
		$displayOrderApprovedByUser = "No";
	}	
	
	if(!empty($row['OrderRoomName'])){
		$orderRoomName = $row['OrderRoomName'];
	} else {
		$orderRoomName = "N/A - Deleted Room";
	}

	if($row['OrderPriceCharged'] != NULL){
		$priceCharged = $row['OrderPriceCharged'];
		$displayPriceCharged = convertToCurrency($priceCharged);
	} else {
		$displayPriceCharged = "N/A";
	}

	if($orderIsApprovedByStaff AND $orderIsApprovedByUser){
		$orderStatus = "Approved";
	} elseif($orderIsApprovedByStaff AND !$orderIsApprovedByUser) {
		$orderStatus = "Pending User Approval";
	} elseif(!$orderIsApprovedByStaff AND $orderIsApprovedByUser) {
		$orderStatus = "Pending Staff Approval";
	} else {
		$orderStatus = "Not Approved";
	}	

	if($orderIsApproved){
		if($row['OrderBookingCompleted'] != NULL){
			$orderStatus = "Completed";
		} elseif($row['DateTimeCancelled'] != NULL OR $row['OrderBookingCancelled'] != NULL){
			$orderStatus = "Cancelled";
		}
	} else {
		if($row['OrderBookingCompleted'] != NULL){
			$orderStatus = "Ended without being approved.";
		} elseif($row['DateTimeCancelled'] != NULL OR $row['OrderBookingCancelled'] != NULL){
			$orderStatus = "Cancelled";
		}
	}

	// Create an array with the actual key/value pairs we want to use in our HTML
	$order[] = array(
						'TheOrderID' => $row['TheOrderID'],
						'OrderStatus' => $orderStatus,
						'OrderUserNotes' => $row['OrderUserNotes'],
						'OrderCommunicationToUser' => $row['OrderCommunicationToUser'],
						'OrderCommunicationFromUser' => $row['OrderCommunicationFromUser'],
						'OrderStartTime' => $displayDateTimeStart,
						'OrderEndTime' => $displayDateTimeEnd,
						'DateTimeApproved' => $displayDateTimeApproved,
						'DateTimeCreated' => $displayDateTimeCreated,
						'DateTimeUpdated' => $displayDateTimeUpdated,
						'DateTimeCancelled' => $displayDateTimeCancelled,
						'OrderContent' => $row['OrderContent'],
						'OrderAdminNote' => $row['OrderAdminNote'],
						'OrderPriceCharged' => $displayPriceCharged,
						'OrderApprovedByUser' => $displayOrderApprovedByUser,
						'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
						'OrderApprovedByName' => $orderApprovedBy
					);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'orders.html.php';
?>