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
	exit();
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
	unset($_SESSION['EditStaffOrderFeedback']);
	unset($_SESSION['EditStaffOrderAdminNote']);
	unset($_SESSION['EditStaffOrderIsApproved']);
	unset($_SESSION['EditStaffOrderOrderID']);
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

	if(isSet($_POST['isApproved']) AND $_POST['isApproved'] == 1 AND !$invalidInput){
		$orderIsApproved = 1;
	} else {
		$orderIsApproved = 0;
	}

	// Remove excess whitespace and prepare strings for validation
	$validatedOrderFeedback = trimExcessWhitespaceButLeaveLinefeed($orderFeedback);
	$validatedIsApproved = $orderIsApproved;

	// Do actual input validation
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

	return array($invalidInput, $validatedOrderFeedback, $validatedIsApproved);
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
		if(isSet($_SESSION['EditStaffOrderFeedback'])){
			$orderFeedback = $_SESSION['EditStaffOrderFeedback'];
			unset($_SESSION['EditStaffOrderFeedback']);
		} else {
			$orderFeedback = '';
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
								`orderDescription`		AS OrderDescription,
								`orderFeedback`			AS OrderFeedback,
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
			$orderFeedback = $row['OrderFeedback'];

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
	$originalOrderFeedback = $_SESSION['EditStaffOrderOriginalInfo']['OrderFeedback'];
	$originalOrderIsApproved = $_SESSION['EditStaffOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderDescription = $_SESSION['EditStaffOrderOriginalInfo']['OrderDescription'];
	$originalOrderContent = $_SESSION['EditStaffOrderOriginalInfo']['OrderContent'];

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'editorder.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Edit Order'){
	// Validate user inputs
	list($invalidInput, $validatedOrderFeedback, $validatedIsApproved) = validateUserInputs();

	// Refresh form on invalid
	if($invalidInput){

		// Refresh.
		$_SESSION['EditStaffOrderFeedback'] = $validatedOrderFeedback;
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

		if($original['OrderFeedback'] != $validatedOrderFeedback){
			$numberOfChanges++;
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
			if($setAsApproved){
				$sql = 'UPDATE 	`orders`
						SET		`orderFeedback` = :OrderFeedback,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`approvedByUserID` = :approvedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderFeedback', $validatedOrderFeedback);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':approvedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderFeedback` = :OrderFeedback,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $_POST['OrderID']);
				$s->bindValue(':OrderFeedback', $validatedOrderFeedback);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
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
						o.`orderDescription`							AS OrderDescription,
						o.`orderFeedback`								AS OrderFeedback,
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
						'OrderDescription' => $row['OrderDescription'],
						'OrderFeedback' => $row['OrderFeedback'],
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