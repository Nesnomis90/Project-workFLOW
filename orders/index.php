<?php 
// This is the index file for the Orders folder

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navcheck.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!

if(!userIsLoggedIn()){
	// Not logged in. Send user a login prompt.
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/login.html.php';
	exit();
}

if(userHasAccess('Staff')){
	$accessRole = "Staff";
} elseif(userHasAccess('Admin')) {
	$accessRole = "Admin";
} else {
	$error = 'You do not have the access level to view this page.';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/accessdenied.html.php';
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the edit Order form
function clearEditStaffOrderSessions(){
	unset($_SESSION['EditStaffOrderOriginalInfo']);
	unset($_SESSION['EditStaffOrderCommunicationToUser']);
	unset($_SESSION['EditStaffOrderAdminNote']);
	unset($_SESSION['EditStaffOrderIsApproved']);
	unset($_SESSION['EditStaffOrderOrderID']);
}

// Function to check if user inputs for Order are correct
function validateUserInputs(){
	$invalidInput = FALSE;

	// Get user inputs
	if(isSet($_POST['OrderCommunicationToUser']) AND !$invalidInput){
		$orderCommunicationToUser = trim($_POST['OrderCommunicationToUser']);
	} else {
		// Doesn't need to be set.
		$orderCommunicationToUser = NULL;
	}

	if(isSet($_POST['isApproved']) AND $_POST['isApproved'] == 1 AND !$invalidInput){
		$orderIsApproved = 1;
	} else {
		$orderIsApproved = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedOrderCommunicationToUser = trimExcessWhitespaceButLeaveLinefeed($orderCommunicationToUser);
	$validatedIsApproved = $orderIsApproved;

	// Do actual input validation
	if(validateString($validatedOrderCommunicationToUser) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['AddOrderError'] = "Your submitted Order feedback has illegal characters in it.";
	}

	// Check if input length is allowed
		// OrderCommunicationToUser
	$invalidOrderCommunicationToUser = isLengthInvalidEquipmentDescription($validatedOrderCommunicationToUser);
	if($invalidOrderCommunicationToUser AND !$invalidInput){
		$_SESSION['AddOrderError'] = "The order feedback submitted is too long.";
		$invalidInput = TRUE;
	}

	return array($invalidInput, $validatedOrderCommunicationToUser, $validatedIsApproved);
}

// if admin wants to edit Order information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Edit') OR
	(isSet($_SESSION['refreshEditStaffOrder']) AND $_SESSION['refreshEditStaffOrder'])
	){

	// Check if we're activated by a user or by a forced refresh
	if(isSet($_SESSION['refreshEditStaffOrder']) AND $_SESSION['refreshEditStaffOrder']){
		//Confirm we've refreshed
		unset($_SESSION['refreshEditStaffOrder']);	

		// Get values we had before refresh
		if(isSet($_SESSION['EditStaffOrderCommunicationToUser'])){
			$orderCommunicationToUser = $_SESSION['EditStaffOrderCommunicationToUser'];
			unset($_SESSION['EditStaffOrderCommunicationToUser']);
		} else {
			$orderCommunicationToUser = '';
		}
		if(isSet($_SESSION['EditStaffOrderIsApproved'])){
			$orderIsApproved = $_SESSION['EditStaffOrderIsApproved'];
			unset($_SESSION['EditStaffOrderIsApproved']);
		} else {
			$orderIsApproved = 0;
		}
		if(isSet($_SESSION['EditStaffOrderOrderID'])){
			$orderID = $_SESSION['EditStaffOrderOrderID'];
		}
	} else {
		// Make sure we don't have any remembered values in memory
		clearEditStaffOrderSessions();

		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();
			$sql = 'SELECT 		`orderID`				AS TheOrderID,
								`orderUserNotes`		AS OrderUserNotes,
								`orderCommunicationToUser`			AS OrderCommunicationToUser,
								`orderApprovedByAdmin`	AS OrderApprovedByAdmin,
								`orderApprovedByStaff` 	AS OrderApprovedByStaff
					FROM 		`order`
					WHERE		`orderID` = :OrderID
					LIMIT 		1';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $_POST['OrderID']);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditStaffOrderOriginalInfo'] = $row;

			// Set the correct information
			$orderID = $row['TheOrderID'];
			$orderCommunicationToUser = $row['OrderCommunicationToUser'];

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}
			$_SESSION['EditStaffOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditStaffOrderOrderID'] = $orderID;

			$sql = 'SELECT 		ex.`name`												AS ExtraName,
								eo.`amount`												AS ExtraAmount,
								IFNULL(eo.`alternativePrice`, ex.`price`)				AS ExtraPrice,
								IFNULL(eo.`alternativeDescription`, ex.`description`)	AS ExtraDescription,
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

			$_SESSION['EditStaffOrderOriginalInfo']['OrderContent'] = $orderContent;
			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching order details.';
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Set original values
	$originalOrderCommunicationToUser = $_SESSION['EditStaffOrderOriginalInfo']['OrderCommunicationToUser'];
	$originalOrderIsApproved = $_SESSION['EditStaffOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderUserNotes = $_SESSION['EditStaffOrderOriginalInfo']['OrderUserNotes'];
	$originalOrderContent = $_SESSION['EditStaffOrderOriginalInfo']['OrderContent'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'editorder.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Order'){
	// Validate user inputs
	list($invalidInput, $validatedOrderCommunicationToUser, $validatedIsApproved) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditStaffOrderCommunicationToUser'] = $validatedOrderCommunicationToUser;
		$_SESSION['EditStaffOrderIsApproved'] = $validatedIsApproved;

		$_SESSION['refreshEditStaffOrder'] = TRUE;
		header('Location: .');
		exit();
	}

	// Check if values have actually changed
	$numberOfChanges = 0;
	if(isSet($_SESSION['EditStaffOrderOriginalInfo'])){
		$original = $_SESSION['EditStaffOrderOriginalInfo'];
		unset($_SESSION['EditStaffOrderOriginalInfo']);

		$messageAdded = FALSE;
		if($validatedOrderCommunicationToUser != ""){
			$numberOfChanges++;
			$messageAdded = TRUE;

			$dateTimeNow = getDatetimeNow();
			$displayDateTimeNow = convertDatetimeToFormat($dateTimeNow, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			$fullOrderCommunicationToUser = $original['OrderCommunicationToUser'] . "\n\n$displayDateTimeNow " . $validatedOrderCommunicationToUser;
		}

		$setAsApproved = FALSE;
		if($original['OrderIsApproved'] != $validatedIsApproved){
			$numberOfChanges++;
			// Approved changed.
			if($validatedIsApproved == 1 AND $accessRole == "Admin"){
				$setAsApproved = TRUE;
				$approvedByAdmin = 1;
				$approvedByStaff = 0;
			} elseif($validatedIsApproved == 1 AND $accessRole == "Staff") {
				$setAsApproved = TRUE;
				$approvedByAdmin = 0;
				$approvedByStaff = 1;
			} elseif($validatedIsApproved == 0) {
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
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderCommunicationToUser', $fullOrderCommunicationToUser);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} elseif($setAsApproved AND !$messageAdded){
				$sql = 'UPDATE 	`orders`
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} elseif(!$setAsApproved AND $messageAdded){
				$sql = 'UPDATE 	`orders`
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderCommunicationToUser', $fullOrderCommunicationToUser);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':approvedByUserID', NULL);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
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

		$_SESSION['OrderStaffFeedback'] = "Successfully updated the Order.";
	} else {
		$_SESSION['OrderStaffFeedback'] = "No changes were made to the Order.";
	}

	clearEditStaffOrderSessions();

	// Load Order list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Reset'){

	clearEditStaffOrderSessions();

	$_SESSION['refreshEditStaffOrder'] = TRUE;
	header('Location: .');
	exit();	
}

// If the admin wants to leave the page and go back to the Order overview again
if(isSet($_POST['edit']) AND $_POST['edit'] == 'Cancel'){
	$_SESSION['OrderStaffFeedback'] = "You cancelled your Order editing.";
	$refreshOrder = TRUE;
}

if(isSet($refreshOrder) AND $refreshOrder) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshOrder);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearEditStaffOrderSessions();

// Display Order list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	// Only retrieves info of orders that are not cancelled, completed and have a meeting room attached.
	$pdo = connect_to_db();
	$sql = 'SELECT 		o.`orderID`										AS TheOrderID,
						o.`orderUserNotes`								AS OrderUserNotes,
						o.`orderCommunicationToUser`					AS OrderCommunicationToUser,
						o.`orderCommunicationFromUser`					AS OrderCommunicationFromUser,
						o.`dateTimeCreated`								AS DateTimeCreated,
						o.`dateTimeUpdated`								AS DateTimeUpdated,
						o.`orderApprovedByUser`							AS OrderApprovedByUser,
						o.`orderApprovedByAdmin`						AS OrderApprovedByAdmin,
						o.`orderApprovedByStaff`						AS OrderApprovedByStaff,
						GROUP_CONCAT(CONCAT_WS("\n", ex.`name`))		AS OrderContent,
						b.`startDateTime`								AS OrderStartDateTime,
						b.`endDateTime`									AS OrderEndDateTime,
						m.`name`										AS OrderRoomName
			FROM 		`orders` o
			INNER JOIN	`extraorders` eo
			ON 			eo.`orderID` = o.`orderID`
			INNER JOIN 	`extra` ex
			ON 			eo.`extraID` = ex.`extraID`
			INNER JOIN	`booking` b
			ON 			b.`orderID` = o.`orderID`
			INNER JOIN	`meetingroom` m
			ON 			m.`meetingRoomID` = b.`meetingRoomID`
			WHERE		o.`dateTimeCancelled` IS NULL
			AND			b.`dateTimeCancelled` IS NULL
			AND			b.`actualEndDateTime` IS NULL
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

	if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
		$orderIsApprovedByStaff = TRUE;
		$displayOrderApprovedByStaff = "Yes";
	} else {
		$orderIsApprovedByStaff = FALSE;
		$displayOrderApprovedByStaff = "No";
	}

	if($row['OrderApprovedByUser'] == 1){
		$orderIsApprovedByUser = TRUE;
		$displayOrderApprovedByUser = "Yes";
	} else {
		$orderIsApprovedByUser = FALSE;
		$displayOrderApprovedByUser = "No";
	}

	$orderRoomName = $row['OrderRoomName'];

	$orderStatus = "N/A";

	if($orderIsApprovedByStaff AND $orderIsApprovedByUser){
		$orderStatus = "Approved";
	} elseif($orderIsApprovedByStaff AND !$orderIsApprovedByUser) {
		$orderStatus = "Pending User Approval";
	} elseif(!$orderIsApprovedByStaff AND $orderIsApprovedByUser) {
		$orderStatus = "Pending Staff Approval";
	} else {
		$orderStatus = "Not Approved";
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
						'DateTimeCreated' => $displayDateTimeCreated,
						'DateTimeUpdated' => $displayDateTimeUpdated,
						'OrderContent' => $row['OrderContent'],
						'OrderApprovedByUser' => $displayOrderApprovedByUser,
						'OrderApprovedByStaff' => $displayOrderApprovedByStaff
					);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'orders.html.php';
?>