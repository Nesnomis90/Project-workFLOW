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

// Set default sorting option
if(isSet($_GET['sortBy'])){
	if($_GET['sortBy'] == "Day"){
		$sortBy = "Day";
	} elseif($_GET['sortBy'] == "Week"){
		$sortBy = "Week";
	} elseif($_GET['sortBy'] == "Starting Time"){
		$sortBy = "Starting Time";
	}
} else {
	header("Location: ?sortBy=Starting+Time");
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the edit Order form
function clearEditOrderSessions(){
	unset($_SESSION['EditOrderOriginalInfo']);
	unset($_SESSION['EditOrderCommunicationToUser']);
	unset($_SESSION['EditOrderAdminNote']);
	unset($_SESSION['EditOrderIsApproved']);
	unset($_SESSION['EditOrderOrderID']);
	unset($_SESSION['EditOrderExtraOrdered']);
	unset($_SESSION['EditOrderOrderMessages']);
	unset($_SESSION['EditOrderAvailableExtra']);
	unset($_SESSION['EditOrderAlternativeExtraAdded']);
	unset($_SESSION['EditOrderAlternativeExtraCreated']);
	unset($_SESSION['EditOrderExtraOrderedOnlyNames']);
	unset($_SESSION['resetEditOrder']);
	unset($_SESSION['refreshEditOrder']);
}

function clearEditOrderSessionsOutsideReset(){
	unset($_SESSION['EditOrderDisableEdit']);
	unset($_SESSION['EditOrderOrderStatus']);
}

// Function to clear sessions used to remember information during the cancel process.
function clearCancelSessions(){
	unset($_SESSION['cancelAdminOrder']);
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

	if(isSet($_SESSION['EditOrderExtraOrdered'])){
		if(isSet($_POST['isApprovedForPurchase'])){
			$isApprovedForPurchaseArray = $_POST['isApprovedForPurchase'];
			foreach($_SESSION['EditOrderExtraOrdered'] AS &$extra){
				$isApprovedForPurchaseUpdated = FALSE;
				for($i=0; $i<sizeOf($isApprovedForPurchaseArray); $i++){
					if($extra['ExtraID'] == $isApprovedForPurchaseArray[$i]){
						$extra['ExtraBooleanApprovedForPurchase'] = 1;
						$isApprovedForPurchaseUpdated = TRUE;
						break;
					}
				}
				if(!$isApprovedForPurchaseUpdated){
					$extra['ExtraBooleanApprovedForPurchase'] = 0;
				}
				unset($extra); // destroy reference.
			}
		} else {
			foreach($_SESSION['EditOrderExtraOrdered'] AS &$extra){
				$extra['ExtraBooleanApprovedForPurchase'] = 0;
				unset($extra); // destroy reference.
			}
		}
		if(isSet($_POST['isPurchased'])){
			$isPurchasedArray = $_POST['isPurchased'];
			foreach($_SESSION['EditOrderExtraOrdered'] AS &$extra){
				$isPurchasedUpdated = FALSE;
				for($i=0; $i<sizeOf($isPurchasedArray); $i++){
					if($extra['ExtraID'] == $isPurchasedArray[$i]){
						$extra['ExtraBooleanPurchased'] = 1;
						$isPurchasedUpdated = TRUE;
						break;
					}
				}
				if(!$isPurchasedUpdated){
					$extra['ExtraBooleanPurchased'] = 0;
				}
				unset($extra); // destroy reference.
			}
		} else {
			foreach($_SESSION['EditOrderExtraOrdered'] AS &$extra){
				$extra['ExtraBooleanPurchased'] = 0;
				unset($extra); // destroy reference.
			}
		}
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
	$invalidOrderCommunicationToUser = isLengthInvalidOrderMessage($validatedOrderCommunicationToUser);
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

// If admin does not want to cancel the morder anyway.
if(isSet($_POST['action']) AND $_POST['action'] == "Abort Cancel"){
	clearCancelSessions();

	$_SESSION['OrderUserFeedback'] = "You did not cancel the order.";

	header('Location: .');
	exit();
}

// If admin wants to cancel an order
if (	(isSet($_POST['action']) and $_POST['action'] == 'Cancel') OR 
		(isSet($_SESSION['refreshCancelAdminOrder']) AND $_SESSION['refreshCancelAdminOrder'])
	){

	if(isSet($_SESSION['refreshCancelAdminOrder']) AND $_SESSION['refreshCancelAdminOrder']){
		unset($_SESSION['refreshCancelAdminOrder']);
	} else {
		$_SESSION['cancelAdminOrder']['OrderID'] = $_POST['OrderID'];
	}

	$orderID = $_SESSION['cancelAdminOrder']['OrderID'];

	// Load new template to let admin add a reason for cancelling the meeting
	if(!isSet($_SESSION['cancelAdminOrder']['ReasonForCancelling'])){
		var_dump($_SESSION); // TO-DO: Remove before uploading
		include_once 'cancelmessage.html.php';
		exit();
	}

	if(isSet($_SESSION['cancelAdminOrder']['ReasonForCancelling']) AND !empty($_SESSION['cancelAdminOrder']['ReasonForCancelling'])){
		$cancelMessage = $_SESSION['cancelAdminOrder']['ReasonForCancelling'];
	} else {
		$cancelMessage = NULL;
	}

	// Get relevant information from the booking and order.
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		$sql = 'SELECT 		b.`startDateTime`			AS BookingStartDateTime,
							b.`endDateTime`				AS BookingEndDateTime,
							u.`firstname`				AS UserFirstName,
							u.`lastname`				AS UserLastName,
							u.`email`					AS UserEmail,
							u.`sendEmail`				AS UserSendEmail,
							u.`userID`					AS UserID,
							c.`name`					AS CompanyName,
							m.`name`					AS RoomName,
							GROUP_CONCAT(
											ex.`name`, 
											" (", 
											eo.`amount`, 
											")"
											SEPARATOR ", "
										)				AS OrderContent
				FROM		`booking` b
				INNER JOIN 	`orders` o
				ON			o.`orderID` = b.`orderID`
				INNER JOIN 	`meetingroom` m
				ON 			m.`meetingRoomID` = b.`meetingRoomID`
				INNER JOIN	`user` u
				ON 			u.`userID` = b.`userID`
				INNER JOIN 	`company` c
				ON 			c.`companyID` = b.`companyID`
				LEFT JOIN	(
										`extraorders` eo
							INNER JOIN 	`extra` ex
							ON 			eo.`extraID` = ex.`extraID`
				)
				ON 			eo.`orderID` = o.`orderID`
				WHERE		o.`orderID` = :orderID
				AND			b.`bookingID` IS NOT NULL
				AND			b.`actualEndDateTime` IS NULL
				AND			b.`dateTimeCancelled` IS NULL
				AND			o.`dateTimeCancelled` IS NULL
				AND			CURRENT_TIMESTAMP < b.`startDateTime`
				LIMIT 		1';
		$s = $pdo->prepare($sql);
		$s->bindValue(':orderID', $orderID);
		$s->execute();

		$row = $s->fetch(PDO::FETCH_ASSOC);

		$dateTimeStart = $row['BookingStartDateTime'];
		$dateTimEnd = $row['BookingEndDateTime'];
		$displayBookingStartDateTime = convertDatetimeToFormat($dateTimeStart , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$displayBookingEndDateTime = convertDatetimeToFormat($dateTimEnd , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

		$userEmail = $row['UserEmail'];
		$sendEmail = $row['UserSendEmail'];
		$userID = $row['UserID'];

		$companyName = $row['CompanyName'];
		$bookingMeetingInfo = "Room Name: " . $row['RoomName'] . " Time Slot: $displayBookingStartDateTime to $displayBookingEndDateTime.";
		$userInfo = $row['UserLastName'] . ", " . $row['UserFirstName'] . " - " . $userEmail;
		$orderContent = $row['OrderContent'];
	}
	catch (PDOException $e)
	{
		$pdo = null;
		$error = 'Error getting selected order information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

	if(empty($dateTimeStart)){
		// Did not match an order, meaning it was already finished/cancelled
		$pdo = null;

		$_SESSION['OrderUserFeedback'] = "Order has already been completed or cancelled!";

		clearCancelSessions();

		// Load booked meetings list webpage with updated database
		header('Location: .');
		exit();	
	}
	
	try
	{
		$pdo->beginTransaction();

		// Meeting got cancelled before the meeting started.
		$sql = 'UPDATE 	`orders` 
				SET 	`dateTimeCancelled` = CURRENT_TIMESTAMP,
						`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
						`orderChangedByStaff` = 1,
						`cancelMessage` = :cancelMessage,
						`cancelledByUserID` = :cancelledByUserID
				WHERE 	`orderID` = :orderID
				AND		`dateTimeCancelled` IS NULL';
		$s = $pdo->prepare($sql);
		$s->bindValue(':orderID', $orderID);
		$s->bindValue(':cancelMessage', $cancelMessage);
		$s->bindValue(':cancelledByUserID', $_SESSION['LoggedInUserID']);
		$s->execute();
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$pdo = null;
		$error = 'Error updating selected order to be cancelled: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}

		// Add a log event that a booking was cancelled
	try
	{
		// Save a description with information about the order that was cancelled
		$logEventDescription = 	"An order with these details was cancelled:" .
								"\nMeeting Information: " . $bookingMeetingInfo .
								"\nExtras Ordered: " . $orderContent .
								"\nBooked For Company: " . $companyName .
								"\nBooked By User: " . $userInfo .
								"\nIt was cancelled by: " . $_SESSION['LoggedInUserName'];

		$sql = "INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT 	`actionID` 
												FROM 	`logaction`
												WHERE 	`name` = 'Order Cancelled'
											),
							`description` = :description";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->execute();
	}
	catch(PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error adding log event to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	if(isSet($userID) AND $userID != $_SESSION['LoggedInUserID']){
		if(isSet($sendEmail) AND $sendEmail == 1){

			if(!empty($_SESSION['cancelAdminOrder']['ReasonForCancelling'])){
				$reasonForCancelling = $_SESSION['cancelAdminOrder']['ReasonForCancelling'];
			} else {
				$reasonForCancelling = "No reason given.";
			}

			$emailSubject = "Your meeting order has been cancelled!";

			$emailMessage = 
			"An order for your meeting has been cancelled by an Admin!" .
			"\nMeeting Information: " . $bookingMeetingInfo .
			"\nExtras Ordered: " . $orderContent .
			"\nBooked For Company: " . $companyName .
			"\nReason given for cancelling: " . $reasonForCancelling .
			"\n\nThe meeting itself is still scheduled as normal and active.";

			$email = $userEmail;

			$mailResult = sendEmail($email, $emailSubject, $emailMessage);

			$_SESSION['OrderUserFeedback'] = "";
			if(!$mailResult){
				$_SESSION['OrderUserFeedback'] = "[WARNING] System failed to send Email to user.";

				// Email failed to be prepared. Store it in database to try again later
				try
				{
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 7 DAY);';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->execute();
				}
				catch (PDOException $e)
				{
					$pdo->rollBack();
					$error = 'Error storing email: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$_SESSION['OrderUserFeedback'] .= "\nEmail to be sent has been stored and will be attempted to be sent again later.";
			}

			$_SESSION['OrderUserFeedback'] .= "\nThis is the email msg we're sending out:\n$emailMessage\nSent to email: $email."; // TO-DO: Remove before uploading
		} elseif(isSet($sendEmail) AND $sendEmail == 0) {
			$_SESSION['OrderUserFeedback'] = "User does not want to be sent Email.";
		}
	} elseif(isSet($userID) AND $userID == $_SESSION['LoggedInUserID']){
		$_SESSION['OrderUserFeedback'] = "Did not send an email because you cancelled your own meeting.";
	} else {
		$_SESSION['OrderUserFeedback'] = "Failed to send an email to the user that the booking got cancelled.";
	}

	try
	{
		$pdo->commit();

		//close connection
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$pdo->rollBack();
		$error = 'Error commiting transaction: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}

	$_SESSION['OrderUserFeedback'] .= "\nSuccessfully cancelled the order!";

	clearCancelSessions();

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// If admin has finished adding a reason for cancelling an order.
if(isSet($_POST['action']) AND $_POST['action'] == "Confirm Reason"){
	$invalidInput = FALSE;
	// Do input validation
	if(isSet($_POST['cancelMessage']) AND !empty($_POST['cancelMessage'])){
		$cancelMessage = trimExcessWhitespaceButLeaveLinefeed($_POST['cancelMessage']);
	} else {
		$cancelMessage = "";
	}
	if(validateString($cancelMessage) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['confirmAdminReasonError'] = "Your submitted message has illegal characters in it.";
	}

	$invalidCancelMessage = isLengthInvalidBookingDescription($cancelMessage);
	if($invalidCancelMessage AND !$invalidInput){
		$_SESSION['confirmAdminReasonError'] = "Your submitted message is too long.";
		$invalidInput = TRUE;
	}

	if($invalidInput){
		var_dump($_SESSION); // TO-DO: Remove when done testing

		include_once 'cancelmessage.html.php';
		exit();
	}

	$_SESSION['cancelAdminOrder']['ReasonForCancelling'] = $cancelMessage;
	$_SESSION['refreshCancelAdminOrder'] = TRUE;

	// Load booked meetings list webpage with updated database
	header('Location: .');
	exit();	
}

// if admin wants to edit Order information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Details') OR
	(isSet($_SESSION['refreshEditOrder']) AND $_SESSION['refreshEditOrder']) OR
	(isSet($_SESSION['resetEditOrder']))
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
		if(isSet($_SESSION['EditOrderAvailableExtra'])){
			$availableExtra = $_SESSION['EditOrderAvailableExtra'];
		}
		if(isSet($_SESSION['EditOrderOrderMessages'])){
			$orderMessages = $_SESSION['EditOrderOrderMessages'];
		}
		if(isSet($_SESSION['EditOrderExtraOrdered'])){
			$extraOrdered = $_SESSION['EditOrderExtraOrdered'];
		}
		if(isSet($_SESSION['EditOrderAlternativeExtraAdded'])){
			$addedExtra = $_SESSION['EditOrderAlternativeExtraAdded'];
		}
		if(isSet($_SESSION['EditOrderAlternativeExtraCreated'])){
			$createdExtra = $_SESSION['EditOrderAlternativeExtraCreated'];
		}
		if(isSet($_SESSION['EditOrderExtraOrderedOnlyNames'])){
			$extraOrderedOnlyNames = $_SESSION['EditOrderExtraOrderedOnlyNames'];
		}
	} else {

		if(isSet($_SESSION['resetEditOrder'])){
			$orderID = $_SESSION['resetEditOrder'];
		} else {
			$orderID = $_POST['OrderID'];
		}

		// Make sure we don't have any remembered values in memory
		clearEditOrderSessions();

		if(!isSet($_SESSION['EditOrderDisableEdit'])){
			$_SESSION['EditOrderDisableEdit'] = $_POST['disableEdit'];
		}
		if(!isSet($_SESSION['EditOrderOrderStatus'])){
			$_SESSION['EditOrderOrderStatus'] = $_POST['OrderStatus'];
		}
		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$sql = 'SELECT 		`orderID`						AS TheOrderID,
								`orderUserNotes`				AS OrderUserNotes,
								`dateTimeCreated`				AS DateTimeCreated,
								`dateTimeCancelled`				AS DateTimeCancelled,
								`dateTimeUpdatedByStaff`		AS DateTimeUpdatedByStaff,
								`dateTimeUpdatedByUser`			AS DateTimeUpdatedByUser,
								`cancelMessage`					AS OrderCancelMessage,
								`orderApprovedByUser`			AS OrderApprovedByUser,
								`orderApprovedByAdmin`			AS OrderApprovedByAdmin,
								`orderApprovedByStaff` 			AS OrderApprovedByStaff,
								`orderChangedByUser`			AS OrderChangedByUser,
								`orderChangedByStaff`			AS OrderChangedByStaff,
								`orderNewMessageFromUser`		AS OrderNewMessageFromUser,
								`orderNewMessageFromStaff`		AS OrderNewMessageFromStaff,
								`adminNote`						AS OrderAdminNote
					FROM 		`orders`
					WHERE		`orderID` = :OrderID
					LIMIT 		1';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditOrderOriginalInfo'] = $row;

			// Set the correct information
			$orderAdminNote = $row['OrderAdminNote'];

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}

			$dateTimeCreated = $row['DateTimeCreated'];
			$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

			if(!empty($row['DateTimeUpdatedByStaff'])){
				$dateTimeUpdatedByStaff = $row['DateTimeUpdatedByStaff'];
				$displayDateTimeUpdatedByStaff = convertDatetimeToFormat($dateTimeUpdatedByStaff , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			} else {
				$displayDateTimeUpdatedByStaff = "";
			}

			if(!empty($row['DateTimeUpdatedByUser'])){
				$dateTimeUpdatedByUser = $row['DateTimeUpdatedByUser'];
				$displayDateTimeUpdatedByUser = convertDatetimeToFormat($dateTimeUpdatedByUser , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			} else {
				$displayDateTimeUpdatedByUser = "";
			}

			if(!empty($row['DateTimeCancelled'])){
				$dateTimeCancelled = $row['DateTimeCancelled'];
				$displayDateTimeCancelled = convertDatetimeToFormat($dateTimeCancelled, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
			} else {
				$displayDateTimeCancelled = NULL;
			}

			$_SESSION['EditOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditOrderOriginalInfo']['DateTimeCreated'] = $displayDateTimeCreated;
			$_SESSION['EditOrderOriginalInfo']['DateTimeCancelled'] = $displayDateTimeCancelled;
			$_SESSION['EditOrderOriginalInfo']['DateTimeUpdatedByStaff'] = $displayDateTimeUpdatedByStaff;
			$_SESSION['EditOrderOriginalInfo']['DateTimeUpdatedByUser'] = $displayDateTimeUpdatedByUser;

			$_SESSION['EditOrderOrderID'] = $orderID;

			$sql = 'SELECT 		ex.`extraID`											AS ExtraID,
								ex.`name`												AS ExtraName,
								eo.`amount`												AS ExtraAmount,
								IFNULL(eo.`alternativePrice`, ex.`price`)				AS ExtraPrice,
								ex.`description`										AS ExtraDescription,
								eo.`purchased`											AS ExtraDateTimePurchased,
								(
									SELECT 	CONCAT_WS(", ", u.`lastname`, u.`firstname`)
									FROM	`user` u
									WHERE	u.`userID` = eo.`purchasedByUserID`
								)														AS ExtraPurchasedByUser,
								eo.`approvedForPurchase`								AS ExtraDateTimeApprovedForPurchase,
								(
									SELECT 	CONCAT_WS(", ", u.`lastname`, u.`firstname`)
									FROM	`user` u
									WHERE	u.`userID` = eo.`approvedByUserID`
								)														AS ExtraApprovedForPurchaseByUser
					FROM 		`extraorders` eo
					INNER JOIN	`extra` ex
					ON 			ex.`extraID` = eo.`extraID`
					WHERE		eo.`orderID` = :OrderID';
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$result = $s->fetchAll(PDO::FETCH_ASSOC);
			foreach($result AS $extra){
				$extraName = $extra['ExtraName'];
				$extraAmount = $extra['ExtraAmount'];
				$extraPrice = convertToCurrency($extra['ExtraPrice']);
				$extraDescription = $extra['ExtraDescription'];
				$extraID = $extra['ExtraID'];

				if($extra['ExtraDateTimePurchased'] != NULL){
					$booleanPurchased = 1;
					$dateTimePurchased = $extra['ExtraDateTimePurchased'];
					$displayDateTimePurchased = convertDatetimeToFormat($dateTimePurchased , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					if($extra['ExtraPurchasedByUser'] != NULL){
						$displayPurchasedByUser = $extra['ExtraPurchasedByUser'];
					} else {
						$displayPurchasedByUser = "N/A - Deleted User";
					}
				} else {
					$displayDateTimePurchased = "";
					$displayPurchasedByUser = "";
					$booleanPurchased = 0;
				}

				if($extra['ExtraDateTimeApprovedForPurchase'] != NULL){
					$booleanApprovedForPurchase = 1;
					$dateTimeApprovedForPurchase = $extra['ExtraDateTimeApprovedForPurchase'];
					$displayDateTimeApprovedForPurchase = convertDatetimeToFormat($dateTimeApprovedForPurchase , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					if($extra['ExtraApprovedForPurchaseByUser'] != NULL){
						$displayApprovedForPurchaseByUser = $extra['ExtraApprovedForPurchaseByUser'];
					} else {
						$displayApprovedForPurchaseByUser = "N/A - Deleted User";
					}
				} else {
					$booleanApprovedForPurchase = 0;
					$displayDateTimeApprovedForPurchase = "";
					$displayApprovedForPurchaseByUser = "";
				}

				$extraOrderedOnlyNames[] = $extraName;

				$extraOrdered[] = array(
											'ExtraID' => $extraID,
											'ExtraName' => $extraName,
											'ExtraAmount' => $extraAmount,
											'ExtraPrice' => $extraPrice,
											'ExtraDescription' => $extraDescription,
											'ExtraDateTimePurchased' => $displayDateTimePurchased,
											'ExtraPurchasedByUser' => $displayPurchasedByUser,
											'ExtraDateTimeApprovedForPurchase' => $displayDateTimeApprovedForPurchase,
											'ExtraApprovedForPurchaseByUser' => $displayApprovedForPurchaseByUser,
											'ExtraBooleanApprovedForPurchase' => $booleanApprovedForPurchase,
											'ExtraBooleanPurchased' => $booleanPurchased
										);
			}

			if(!isSet($extraOrdered)){
				$extraOrdered = array();
			}
			if(!isSet($extraOrderedOnlyNames)){
				$extraOrderedOnlyNames = array();
			}

			$_SESSION['EditOrderOriginalInfo']['ExtraOrdered'] = $extraOrdered;
			$_SESSION['EditOrderExtraOrdered'] = $extraOrdered;
			$_SESSION['EditOrderExtraOrderedOnlyNames'] = $extraOrderedOnlyNames;

			// Get information about messages sent to/from user
			$sql = 'SELECT	`messageID`		AS OrderMessageID,
							`message`		AS OrderMessage,
							`sentByStaff`	AS OrderMessageSentByStaff,
							`sentByUser`	AS OrderMessageSentByUser,
							`messageSeen`	AS OrderMessageSeen,
							`dateTimeAdded`	AS OrderMessageDateTimeAdded
					FROM	`ordermessages`
					WHERE	`orderID` = :OrderID';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$result = $s->fetchAll(PDO::FETCH_ASSOC);

			$pdo->beginTransaction();

			$orderMessages = "";
			foreach($result AS $message){

				$messageID = $message['OrderMessageID'];
				$messageOnly = $message['OrderMessage'];
				$sentByStaff = $message['OrderMessageSentByStaff'];
				$sentByUser = $message['OrderMessageSentByUser'];
				if($sentByStaff == 1){
					$messageAddFrom = "(Staff)";
				} elseif($sentByUser == 1){
					$messageAddFrom = "(User)";
				} else {
					$messageAddFrom = "(Unknown)";
				}

				$messageSeen = $message['OrderMessageSeen'];
				if($messageSeen == 0 AND $sentByUser == 1){
					$messageAddSeen = "NEW MESSAGE ";
				} elseif($messageSeen == 0 AND $sentByStaff == 1){
					$messageAddSeen = "NOT READ BY USER ";
				} else {
					$messageAddSeen = "";
				}

				$messageDateTimeAdded = $message['OrderMessageDateTimeAdded'];
				$displayMessageDateTimeAdded = convertDatetimeToFormat($messageDateTimeAdded , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

				$finalMessage = $displayMessageDateTimeAdded . " " . $messageAddSeen . $messageAddFrom . ": " . $messageOnly . "\n";

				$orderMessages .= $finalMessage;

				if($sentByUser == 1 AND $messageSeen == 0){
					// Update that the new messages (from user) has been seen.
					$sql = "UPDATE	`ordermessages`
							SET		`messageSeen` = 1
							WHERE	`orderID` = :OrderID
							AND		`messageID` = :MessageID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':MessageID', $messageID);
					$s->execute();
				}
			}

			$_SESSION['EditOrderOrderMessages'] = $orderMessages;

			// Update that there are no new messages from user
			// Also, we've seen any of the changes if there were any
			$sql = "UPDATE	`orders`
					SET		`orderNewMessageFromUser` = 0,
							`orderChangedByUser` = 0
					WHERE	`orderID` = :OrderID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$pdo->commit();

			// Get all available extras if admin wants to add an alternative
			// That are not already in the order
			$sql = 'SELECT 	`extraID`		AS ExtraID,
							`name`			AS ExtraName,
							`description`	AS ExtraDescription,
							`price`			AS ExtraPrice
					FROM 	`extra`
					WHERE	`extraID` 
					NOT IN 	(
								SELECT 	`extraID`
								FROM 	`extraorders`
								WHERE	`orderID` = :OrderID
							)';
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();
			$availableExtra = $s->fetchAll(PDO::FETCH_ASSOC);
			$_SESSION['EditOrderAvailableExtra'] = $availableExtra;

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
	$originalOrderAdminNote = $_SESSION['EditOrderOriginalInfo']['OrderAdminNote'];
	$originalOrderIsApproved = $_SESSION['EditOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderUserNotes = $_SESSION['EditOrderOriginalInfo']['OrderUserNotes'];
	$originalOrderCreated = $_SESSION['EditOrderOriginalInfo']['DateTimeCreated'];
	$originalOrderUpdatedByStaff = $_SESSION['EditOrderOriginalInfo']['DateTimeUpdatedByStaff'];
	$originalOrderUpdatedByUser = $_SESSION['EditOrderOriginalInfo']['DateTimeUpdatedByUser'];

	$disableEdit = $_SESSION['EditOrderDisableEdit'];
	$orderStatus = $_SESSION['EditOrderOrderStatus'];

	if(!empty($_SESSION['EditOrderOriginalInfo']['DateTimeCancelled'])){
		$originalCancelMessage = $_SESSION['EditOrderOriginalInfo']['OrderCancelMessage'];
		$originalDateTimeCancelled = $_SESSION['EditOrderOriginalInfo']['DateTimeCancelled'];
	}

	$availableExtrasNumber = sizeOf($availableExtra);

	if(!isSet($orderCommunicationToUser)){
		$orderCommunicationToUser = "";
	}

	var_dump($_SESSION); // TO-DO: remove after testing is done

	// Change to the template we want to use
	include 'details.html.php';
	exit();
}

// Perform the actual database update of the edited information
if(isSet($_POST['action']) AND $_POST['action'] == 'Submit Changes'){
	// Validate user inputs
	list($invalidInput, $validatedOrderCommunicationToUser, $validatedAdminNote, $validatedIsApproved) = validateUserInputs();

	// JavaScript alternatives submitted data
	if(isSet($_POST['LastAlternativeID']) AND $_POST['LastAlternativeID'] != ""){
		// There has been an alternative added
		$lastID = $_POST['LastAlternativeID']+1;
		for($i=0; $i < $lastID; $i++){
			$postExtraIDName = "addAlternativeSelected" . $i;
			$postExtraNameName = "AlternativeName" . $i;
			$postAmountName = "AmountSelected" . $i;
			$postAlternativeDescriptionName = "AlternativeDescription" . $i;
			$postAlternativePriceName = "AlternativePrice" . $i;
			if(isSet($_POST[$postExtraIDName]) AND $_POST[$postExtraIDName] > 0){
				// These are existing alternatives added
				if(!isSet($addedExtra)){
					$addedExtra = array();
				}
				$addedExtra[] = array(
										"ExtraID" => $_POST[$postExtraIDName],
										"ExtraAmount" => $_POST[$postAmountName]
									);
			} elseif(isSet($_POST[$postExtraNameName])) {
				// These are newly created alternatives
				if(!isSet($createdExtra)){
					$createdExtra = array();
				}

				$invalid = FALSE;
				// input validation
				$newAlternativeExtraName = $_POST[$postExtraNameName];
				$newAlternativeExtraDescription = $_POST[$postAlternativeDescriptionName];

				$trimmedNewAlternativeExtraName = trimExcessWhitespace($newAlternativeExtraName);
				$trimmedNewAlternativeExtraDescription = trimExcessWhitespaceButLeaveLinefeed($newAlternativeExtraDescription);

				// Do actual input validation
				if(validateString($trimmedNewAlternativeExtraName) === FALSE AND !$invalidInput){
					$invalidInput = TRUE;
					$invalid = TRUE;
					$_SESSION['AddOrderError'] = "Your submitted Extra name has illegal characters in it.";
				}
				if(validateString($trimmedNewAlternativeExtraDescription) === FALSE AND !$invalidInput){
					$invalidInput = TRUE;
					$invalid = TRUE;
					$_SESSION['AddOrderError'] = "Your submitted Extra description has illegal characters in it.";
				}

				// Are values actually filled in?
				if($trimmedNewAlternativeExtraName == "" AND !$invalidInput){
					$_SESSION['AddOrderError'] = "You need to fill in a name for your Extra.";	
					$invalidInput = TRUE;
					$invalid = TRUE;
				}
				if($trimmedNewAlternativeExtraDescription == "" AND !$invalidInput){
					$_SESSION['AddOrderError'] = "You need to fill in a description for your Extra.";
					$invalidInput = TRUE;
					$invalid = TRUE;
				}

				// Are character lengths fine?
				$invalidExtraName = isLengthInvalidExtraName($trimmedNewAlternativeExtraName);
				if($invalidExtraName AND !$invalidInput){
					$_SESSION['AddOrderError'] = "The extra name submitted is too long.";	
					$invalidInput = TRUE;
					$invalid = TRUE;
				}
				$invalidExtraDescription = isLengthInvalidExtraDescription($trimmedNewAlternativeExtraDescription);
				if($invalidExtraDescription AND !$invalidInput){
					$_SESSION['AddOrderError'] = "The extra description submitted is too long.";
					$invalidInput = TRUE;
					$invalid = TRUE;
				}

				// Check if new name is taken
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
					$pdo = connect_to_db();
					$sql = 'SELECT 	COUNT(*) 
							FROM 	`extra`
							WHERE 	`name`= :ExtraName';
					$s = $pdo->prepare($sql);
					$s->bindValue(':ExtraName', $trimmedNewAlternativeExtraName);
					$s->execute();

					$pdo = null;

					$row = $s->fetch();

					if($row[0] > 0){
						// This name is already being used for an Extra
						$_SESSION['AddOrderError'] = "There is already an Extra with the name: " . $trimmedNewAlternativeExtraName . "!";
						$invalidInput = TRUE;
						$invalid = TRUE;
					}
				}
				catch (PDOException $e)
				{
					$error = 'Error searching through Extra.' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}

				$createdExtra[] = array(
										"ExtraName" => $trimmedNewAlternativeExtraName,
										"ExtraAmount" => $_POST[$postAmountName],
										"ExtraDescription" => $trimmedNewAlternativeExtraDescription,
										"ExtraPrice" => $_POST[$postAlternativePriceName],
										"Invalid" => $invalid
										);
			}
		}
	}

	// Refresh form on invalid
	if($invalidInput){
		// Refresh.
		if(isSet($addedExtra)){
			$_SESSION['EditOrderAlternativeExtraAdded'] = $addedExtra;
		}
		if(isSet($createdExtra)){
			$_SESSION['EditOrderAlternativeExtraCreated'] = $createdExtra;
		}
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
	}

	$extraChanged = FALSE;
	if($original['ExtraOrdered'] != $_SESSION['EditOrderExtraOrdered']){
		$numberOfChanges++;
		$extraChanged = TRUE;
	}

	$extraAdded = FALSE;
	if(isSet($addedExtra)){
		$numberOfChanges++;
		$extraAdded = TRUE;
	}

	$extraCreated = FALSE;
	if(isSet($createdExtra)){
		$numberOfChanges++;
		$extraCreated = TRUE;
	}

	$orderID = $_POST['OrderID'];

	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			if($extraChanged OR $messageAdded OR $extraAdded OR $extraCreated){
				$pdo->beginTransaction();
			}

			if($setAsApproved){
				if($messageAdded AND ($extraAdded OR $extraCreated)){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = CURRENT_TIMESTAMP,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = :orderApprovedByUserID,
									`orderChangedByStaff` = 1,
									`orderApprovedByUser` = 0,
									`orderNewMessageFromStaff` = 1
							WHERE 	`orderID` = :OrderID';
				} elseif(!$messageAdded AND ($extraAdded OR $extraCreated)){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = CURRENT_TIMESTAMP,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = :orderApprovedByUserID,
									`orderChangedByStaff` = 1,
									`orderApprovedByUser` = 0
							WHERE 	`orderID` = :OrderID';
				} elseif($messageAdded AND !$extraAdded AND !$extraCreated){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = CURRENT_TIMESTAMP,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = :orderApprovedByUserID,
									`orderNewMessageFromStaff` = 1
							WHERE 	`orderID` = :OrderID';
				} else {
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = CURRENT_TIMESTAMP,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = :orderApprovedByUserID
							WHERE 	`orderID` = :OrderID';
				}
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':orderApprovedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} else {
				if($messageAdded AND ($extraAdded OR $extraCreated)){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = NULL,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = NULL,
									`orderChangedByStaff` = 1,
									`orderApprovedByUser` = 0,
									`orderNewMessageFromStaff` = 1
							WHERE 	`orderID` = :OrderID';
				} elseif(!$messageAdded AND ($extraAdded OR $extraCreated)){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = NULL,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = NULL,
									`orderChangedByStaff` = 1,
									`orderApprovedByUser` = 0
							WHERE 	`orderID` = :OrderID';
				} elseif($messageAdded AND !$extraAdded AND !$extraCreated){
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = NULL,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = NULL,
									`orderNewMessageFromStaff` = 1
							WHERE 	`orderID` = :OrderID';
				} else {
					$sql = 'UPDATE 	`orders`
							SET		`orderApprovedByAdmin` = :approvedByAdmin,
									`orderApprovedByStaff` = :approvedByStaff,
									`dateTimeUpdatedByStaff` = CURRENT_TIMESTAMP,
									`dateTimeApproved` = NULL,
									`adminNote` = :adminNote,
									`orderApprovedByUserID` = NULL
							WHERE 	`orderID` = :OrderID';
				}
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->execute();
			}

			if($messageAdded){
				$sql = 'INSERT INTO `ordermessages`
						SET			`message` = :message,
									`sentByStaff` = 1,
									`dateTimeAdded` = CURRENT_TIMESTAMP,
									`messageFromUserID` = :messageFromUserID,
									`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':message', $validatedOrderCommunicationToUser);
				$s->bindValue(':messageFromUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			}

			if($extraChanged){
				// Update extraorders table
				foreach($_SESSION['EditOrderExtraOrdered'] AS $key => $extra){
					$extraID = $extra['ExtraID'];
					$extraBooleanApprovedForPurchase = $extra['ExtraBooleanApprovedForPurchase'];
					$extraBooleanPurchased = $extra['ExtraBooleanPurchased'];
					$updateApprovedForPurchase = FALSE;
					$updatePurchased = FALSE;
					// update if the values actually changed
					if($original['ExtraOrdered'][$key]['ExtraBooleanApprovedForPurchase'] != $extraBooleanApprovedForPurchase){
						$updateApprovedForPurchase = TRUE;
					}
					if($original['ExtraOrdered'][$key]['ExtraBooleanPurchased'] != $extraBooleanPurchased){
						$updatePurchased = TRUE;
					}

					if($extraBooleanApprovedForPurchase == 1){
						$extraApprovedByUserID = $_SESSION['LoggedInUserID'];
					} else {
						$extraApprovedByUserID = NULL;
					}

					if($extraBooleanPurchased == 1){
						$purchasedByUserID = $_SESSION['LoggedInUserID'];
					} else {
						$purchasedByUserID = NULL;
					}

					if($updateApprovedForPurchase AND $updatePurchased){
						if($extraBooleanApprovedForPurchase == 1 AND $extraBooleanPurchased == 1){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = CURRENT_TIMESTAMP,
											`approvedByUserID` = :extraApprovedByUserID,
											`purchased` = CURRENT_TIMESTAMP,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} elseif($extraBooleanApprovedForPurchase == 1 AND $extraBooleanPurchased == 0){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = CURRENT_TIMESTAMP,
											`approvedByUserID` = :extraApprovedByUserID,
											`purchased` = NULL,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} elseif($extraBooleanApprovedForPurchase == 0 AND $extraBooleanPurchased == 1){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`approvedByUserID` = :extraApprovedByUserID,
											`purchased` = CURRENT_TIMESTAMP,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} else {
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`approvedByUserID` = :extraApprovedByUserID,
											`purchased` = NULL,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						}
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':extraApprovedByUserID', $extraApprovedByUserID);
						$s->bindValue(':purchasedByUserID', $purchasedByUserID);
						$s->execute();
					} elseif($updateApprovedForPurchase AND !$updatePurchased){
						if($extraBooleanApprovedForPurchase == 1){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = CURRENT_TIMESTAMP,
											`approvedByUserID` = :extraApprovedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} else {
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`approvedByUserID` = :extraApprovedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						}
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':extraApprovedByUserID', $extraApprovedByUserID);
						$s->execute();
					} elseif(!$updateApprovedForPurchase AND $updatePurchased){
						if($extraBooleanPurchased == 1){
							$sql = "UPDATE	`extraorders`
									SET		`purchased` = CURRENT_TIMESTAMP,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} else {
							$sql = "UPDATE	`extraorders`
									SET		`purchased` = NULL,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						}
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':purchasedByUserID', $purchasedByUserID);
						$s->execute();
					}
				}
			}

			if($extraAdded){
				foreach($addedExtra AS $extra){
					$extraID = $extra['ExtraID'];
					$extraAmount = $extra['ExtraAmount'];

					$sql = "INSERT INTO 	`extraorders`
							SET				`extraID` = :ExtraID,
											`orderID` = :OrderID,
											`amount` = :ExtraAmount";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':ExtraID', $extraID);
					$s->bindValue(':ExtraAmount', $extraAmount);
					$s->execute();
				}
			}

			if($extraCreated){
				foreach($createdExtra AS $extra){
					$extraName = $extra['ExtraName'];
					$extraDescription = $extra['ExtraDescription'];
					$extraAmount = $extra['ExtraAmount'];
					$extraPrice = $extra['ExtraPrice'];

					$sql = "INSERT INTO 	`extra`
							SET				`name` = :ExtraName,
											`description` = :ExtraDescription,
											`price` = :ExtraPrice,
											`isAlternative` = 1";
					$s = $pdo->prepare($sql);
					$s->bindValue(':ExtraName', $extraName);
					$s->bindValue(':ExtraDescription', $extraDescription);
					$s->bindValue(':ExtraPrice', $extraPrice);
					$s->execute();

					$extraID = $pdo->lastInsertId();

					// Save a description with information about the Extra that was added
					$description = 	"The Extra: $extraName" . 
									"\nwith the description: $extraDescription" . 
									"\nand the price: $extraPrice" .
									"\nwas added by: " . $_SESSION['LoggedInUserName'] .
									"\nas an alternative only extra";

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

					$sql = "INSERT INTO 	`extraorders`
							SET				`extraID` = :ExtraID,
											`orderID` = :OrderID,
											`amount` = :ExtraAmount";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->bindValue(':ExtraID', $extraID);
					$s->bindValue(':ExtraAmount', $extraAmount);
					$s->execute();
				}
			}

			if($extraChanged OR $messageAdded OR $extraAdded OR $extraCreated){
				$pdo->commit();
			}

			// Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$pdo->rollBack();
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
	clearEditOrderSessionsOutsideReset();

	// Load Order list webpage
	header('Location: .');
	exit();
}

// If admin wants to get original values while editing
if(isSet($_POST['action']) AND $_POST['action'] == 'Reset'){

	clearEditOrderSessions();

	$_SESSION['resetEditOrder'] = $_POST['OrderID'];
	header('Location: .');
	exit();
}

// If the admin wants to leave the page and go back to the Order overview again
if(isSet($_POST['action']) AND $_POST['action'] == 'Go Back'){
	$_SESSION['OrderUserFeedback'] = "You left the order without making any changes.";
	$refreshOrder = TRUE;
}

if(isSet($refreshOrder) AND $refreshOrder) {
	// TO-DO: Add code that should occur on a refresh
	unset($refreshOrder);
}

// Remove any unused variables from memory // TO-DO: Change if this ruins having multiple tabs open etc.
clearEditOrderSessions();
clearEditOrderSessionsOutsideReset();
clearCancelSessions();

// Display Order list
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	$pdo = connect_to_db();
	$sql = 'SELECT 		o.`orderID`										AS TheOrderID,
						o.`orderUserNotes`								AS OrderUserNotes,
						o.`dateTimeCreated`								AS DateTimeCreated,
						o.`dateTimeUpdatedByStaff`						AS DateTimeUpdatedByStaff,
						o.`dateTimeUpdatedByUser`						AS DateTimeUpdatedByUser,
						o.`dateTimeApproved`							AS DateTimeApproved,
						o.`dateTimeCancelled`							AS DateTimeCancelled,
						o.`orderApprovedByUser`							AS OrderApprovedByUser,
						o.`orderApprovedByAdmin`						AS OrderApprovedByAdmin,
						o.`orderApprovedByStaff`						AS OrderApprovedByStaff,
						o.`orderChangedByUser`							AS OrderChangedByUser,
						o.`orderChangedByStaff`							AS OrderChangedByStaff,
						o.`orderNewMessageFromUser`						AS OrderNewMessageFromUser,
						o.`orderNewMessageFromStaff`					AS OrderNewMessageFromStaff,
						o.`orderFinalPrice`								AS OrderFinalPrice,
						o.`adminNote`									AS OrderAdminNote,
						(
							SELECT 	CONCAT_WS(", ",`lastname`, `firstname`)
							FROM	`user`
							WHERE	`userID` = o.`orderApprovedByUserID`
							LIMIT 	1
						)												AS OrderApprovedByUserName,
						GROUP_CONCAT(ex.`name`, " (", eo.`amount`, ")"
							SEPARATOR "\n")								AS OrderContent,
						COUNT(eo.`extraID`)								AS OrderExtrasOrdered,
						COUNT(eo.`approvedForPurchase`)					AS OrderExtrasApproved,
						COUNT(eo.`purchased`)							AS OrderExtrasPurchased,
						(
							SELECT	COUNT(om.`messageID`)
							FROM	`ordermessages` om
							WHERE	om.`orderID` = o.`orderID`
							LIMIT 	1
						)												AS OrderMessagesSent,
						(
							SELECT		om.`message`
							FROM		`ordermessages` om
							WHERE		om.`orderID` = o.`orderID`
							AND			om.`sentByStaff` = 1
							ORDER BY	om.`dateTimeAdded` DESC
							LIMIT 	1
						)												AS OrderLastMessageFromStaff,
						(
							SELECT		om.`message`
							FROM		`ordermessages` om
							WHERE		om.`orderID` = o.`orderID`
							AND			om.`sentByUser` = 1
							ORDER BY	om.`dateTimeAdded` DESC
							LIMIT 	1
						)												AS OrderLastMessageFromUser,
						b.`startDateTime`								AS OrderStartDateTime,
						b.`endDateTime`									AS OrderEndDateTime,
						b.`actualEndDateTime`							AS OrderBookingCompleted,
						b.`dateTimeCancelled`							AS OrderBookingCancelled,
						(
							SELECT 	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = b.`meetingRoomID`
							LIMIT 	1
						)												AS OrderRoomName,
						(
							SELECT 	`name`
							FROM	`company`
							WHERE	`companyID` = b.`companyID`
							LIMIT 	1
						)												AS OrderBookedFor
			FROM 		`orders` o
			INNER JOIN	`booking` b
			ON 			b.`orderID` = o.`orderID`
			LEFT JOIN	(
									`extraorders` eo
						INNER JOIN 	`extra` ex
						ON 			eo.`extraID` = ex.`extraID`
			)
			ON 			eo.`orderID` = o.`orderID`
			WHERE		b.`orderID` IS NOT NULL
			GROUP BY	o.`orderID`
			ORDER BY	b.`startDateTime`';

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
	if(!empty($row['DateTimeUpdatedByStaff']) AND !empty($row['DateTimeUpdatedByUser'])){
		$dateTimeUpdatedByStaff = $row['DateTimeUpdatedByStaff'];
		$dateTimeUpdatedByUser = $row['DateTimeUpdatedByUser'];
		if($dateTimeUpdatedByStaff > $dateTimeUpdatedByUser){
			$dateTimeUpdated = $dateTimeUpdatedByStaff;
		} else {
			$dateTimeUpdated = $dateTimeUpdatedByUser;
		}
		$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$newOrder = FALSE;
	} elseif(!empty($row['DateTimeUpdatedByStaff']) AND empty($row['DateTimeUpdatedByUser'])){
		$dateTimeUpdated = $row['DateTimeUpdatedByStaff'];
		$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$newOrder = FALSE;
	} elseif(empty($row['DateTimeUpdatedByStaff']) AND !empty($row['DateTimeUpdatedByUser'])){
		$dateTimeUpdated = $row['DateTimeUpdatedByUser'];
		$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		$newOrder = FALSE;
	} else {
		$displayDateTimeUpdated = "";
		$newOrder = TRUE;
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

	if(!empty($row['OrderBookedFor'])){
		$orderBookedFor = $row['OrderBookedFor'];
	} else {
		$orderBookedFor = "N/A - Deleted Company";
	}

	if($row['OrderFinalPrice'] != NULL){
		$orderFinalPrice = $row['OrderFinalPrice'];
		$displayOrderFinalPrice = convertToCurrency($orderFinalPrice);
	} else {
		$displayOrderFinalPrice = "N/A";
	}

	$orderMessagesSent = $row['OrderMessagesSent'];
	$orderNewMessageFromUser = $row['OrderNewMessageFromUser'];
	$orderNewMessageFromStaff = $row['OrderNewMessageFromStaff'];
	$orderChangedByUser = $row['OrderChangedByUser'];
	$orderChangedByStaff = $row['OrderChangedByStaff'];
	$extrasOrdered = $row['OrderExtrasOrdered'];
	$extrasApproved = $row['OrderExtrasApproved'];
	$extrasPurchased = $row['OrderExtrasPurchased'];

	if($orderNewMessageFromStaff == 1 AND $orderNewMessageFromUser == 1){
		$messageStatus = "New Message From User!\n\nMessage Sent To User Not Seen Yet.";
	} elseif($orderNewMessageFromStaff == 1 AND $orderNewMessageFromUser == 0){
		$messageStatus = "Message Sent To User Not Seen Yet.";
	} elseif($orderNewMessageFromStaff == 0 AND $orderNewMessageFromUser == 1) {
		$messageStatus = "New Message From User!";
	} elseif($orderMessagesSent > 0) {
		$messageStatus = "All Messages Seen.";
	} else {
		$messageStatus = "No Messages Sent.";
	}

	if($newOrder){
		$orderStatus = "New Order!";
	} elseif($orderIsApprovedByStaff AND $orderIsApprovedByUser){
		$orderStatus = "Order Approved!";
		if($extrasApproved == $extrasOrdered AND $extrasPurchased == $extrasOrdered){
			$orderStatus .= "\n\nAll Items Approved!\n\nAll Items Purchased!";
		} elseif($extrasApproved == $extrasOrdered AND $extrasPurchased < $extrasOrdered){
			$orderStatus .= "\n\nAll Items Approved!\n\nPending Item Purchases.";
		} elseif($extrasApproved < $extrasOrdered){
			$orderStatus .= "\n\nPending Item Approval.\n\nPending Item Purchases.";
		}
	} elseif($orderIsApprovedByStaff AND !$orderIsApprovedByUser) {
		$orderStatus = "Order Not Approved After Change.\n\nPending User Approval.";
	} elseif(!$orderIsApprovedByStaff AND $orderIsApprovedByUser) {
		$orderStatus = "Order Not Approved.\n\nPending Staff Approval.";
	} else {
		$orderStatus = "Order Not Approved After Change.\n\nPending Staff Approval.\n\nPending User Approval.";
	}

	if(!empty($row['OrderLastMessageFromUser'])){
		$displayLastMessageFromUser = $row['OrderLastMessageFromUser'];
	} else {
		$displayLastMessageFromUser = "";
	}

	if(!empty($row['OrderLastMessageFromStaff'])){
		$displayLastMessageFromStaff = $row['OrderLastMessageFromStaff'];
	} else {
		$displayLastMessageFromStaff = "";
	}

	if($orderIsApproved){
		if($row['OrderBookingCompleted'] != NULL){
			$orderStatus = "Completed";
			$status = "Completed";

			$dateTimeCompleted = $row['OrderBookingCompleted'];
			$displayDateTimeCompleted = convertDatetimeToFormat($dateTimeCompleted , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		} elseif($row['DateTimeCancelled'] != NULL OR $row['OrderBookingCancelled'] != NULL){
			$orderStatus = "Cancelled";
			$status = "Cancelled";
		} else {
			$status = "Active";
		}
	} else {
		if($row['OrderBookingCompleted'] != NULL){
			$orderStatus = "Ended without being approved";
			$status = "Ended without being approved";

			$dateTimeCompleted = $row['OrderBookingCompleted'];
			$displayDateTimeCompleted = convertDatetimeToFormat($dateTimeCompleted , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
		} elseif($row['DateTimeCancelled'] != NULL OR $row['OrderBookingCancelled'] != NULL){
			$orderStatus = "Cancelled";
			$status = "Cancelled";
		} else {
			$status = "Active";
		}
	}

	// sort orders by their date and put them into different arrays representing orders today, this week and
	if($status == "Active"){
		if($sortBy == "Day"){
			date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
			$newDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeStart);
			$dayNumberAndYear = $newDateTime->format("z-Y");

			$orderByDay[$dayNumberAndYear][] = array(
												'TheOrderID' => $row['TheOrderID'],
												'OrderStatus' => $orderStatus,
												'OrderUserNotes' => $row['OrderUserNotes'],
												'OrderMessageStatus' => $messageStatus,
												'OrderLastMessageFromUser' => $displayLastMessageFromUser,
												'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
												'OrderStartTime' => $displayDateTimeStart,
												'OrderEndTime' => $displayDateTimeEnd,
												'DateTimeApproved' => $displayDateTimeApproved,
												'DateTimeCreated' => $displayDateTimeCreated,
												'DateTimeUpdated' => $displayDateTimeUpdated,
												'OrderContent' => $row['OrderContent'],
												'OrderAdminNote' => $row['OrderAdminNote'],
												'OrderFinalPrice' => $displayOrderFinalPrice,
												'OrderApprovedByUser' => $displayOrderApprovedByUser,
												'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
												'OrderApprovedByName' => $orderApprovedBy,
												'OrderRoomName' => $orderRoomName,
												'OrderBookedFor' => $orderBookedFor
											);
		} elseif($sortBy == "Week"){
			date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
			$newDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $dateTimeStart);
			$dayName = $newDateTime->format("l");
			$weekNumberAndYear = $newDateTime->format("W-Y");

			$orderByWeek[$weekNumberAndYear][$dayName][] = array(
																	'TheOrderID' => $row['TheOrderID'],
																	'OrderStatus' => $orderStatus,
																	'OrderUserNotes' => $row['OrderUserNotes'],
																	'OrderMessageStatus' => $messageStatus,
																	'OrderLastMessageFromUser' => $displayLastMessageFromUser,
																	'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
																	'OrderStartTime' => $displayDateTimeStart,
																	'OrderEndTime' => $displayDateTimeEnd,
																	'DateTimeApproved' => $displayDateTimeApproved,
																	'DateTimeCreated' => $displayDateTimeCreated,
																	'DateTimeUpdated' => $displayDateTimeUpdated,
																	'OrderContent' => $row['OrderContent'],
																	'OrderAdminNote' => $row['OrderAdminNote'],
																	'OrderFinalPrice' => $displayOrderFinalPrice,
																	'OrderApprovedByUser' => $displayOrderApprovedByUser,
																	'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
																	'OrderApprovedByName' => $orderApprovedBy,
																	'OrderRoomName' => $orderRoomName,
																	'OrderBookedFor' => $orderBookedFor
																);
		} else {
			$order[] = array(
								'TheOrderID' => $row['TheOrderID'],
								'OrderStatus' => $orderStatus,
								'OrderUserNotes' => $row['OrderUserNotes'],
								'OrderMessageStatus' => $messageStatus,
								'OrderLastMessageFromUser' => $displayLastMessageFromUser,
								'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
								'OrderStartTime' => $displayDateTimeStart,
								'OrderEndTime' => $displayDateTimeEnd,
								'DateTimeApproved' => $displayDateTimeApproved,
								'DateTimeCreated' => $displayDateTimeCreated,
								'DateTimeUpdated' => $displayDateTimeUpdated,
								'OrderContent' => $row['OrderContent'],
								'OrderAdminNote' => $row['OrderAdminNote'],
								'OrderFinalPrice' => $displayOrderFinalPrice,
								'OrderApprovedByUser' => $displayOrderApprovedByUser,
								'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
								'OrderApprovedByName' => $orderApprovedBy,
								'OrderRoomName' => $orderRoomName,
								'OrderBookedFor' => $orderBookedFor
							);
		}
	} elseif($status == "Completed"){
		$ordersCompleted[] = array(
								'TheOrderID' => $row['TheOrderID'],
								'OrderStatus' => $orderStatus,
								'OrderUserNotes' => $row['OrderUserNotes'],
								'OrderMessageStatus' => $messageStatus,
								'OrderLastMessageFromUser' => $displayLastMessageFromUser,
								'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
								'DateTimeApproved' => $displayDateTimeApproved,
								'DateTimeUpdated' => $displayDateTimeUpdated,
								'DateTimeCompleted' => $displayDateTimeCompleted,
								'OrderContent' => $row['OrderContent'],
								'OrderAdminNote' => $row['OrderAdminNote'],
								'OrderFinalPrice' => $displayOrderFinalPrice,
								'OrderApprovedByName' => $orderApprovedBy,
								'OrderBookedFor' => $orderBookedFor
							);
	} elseif($status == "Cancelled"){
		$ordersCancelled[] = array(
								'TheOrderID' => $row['TheOrderID'],
								'OrderStatus' => $orderStatus,
								'OrderUserNotes' => $row['OrderUserNotes'],
								'OrderMessageStatus' => $messageStatus,
								'OrderLastMessageFromUser' => $displayLastMessageFromUser,
								'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
								'OrderStartTime' => $displayDateTimeStart,
								'OrderEndTime' => $displayDateTimeEnd,
								'DateTimeCreated' => $displayDateTimeCreated,
								'DateTimeUpdated' => $displayDateTimeUpdated,
								'DateTimeCancelled' => $displayDateTimeCancelled,
								'OrderContent' => $row['OrderContent'],
								'OrderAdminNote' => $row['OrderAdminNote'],
								'OrderRoomName' => $orderRoomName,
								'OrderBookedFor' => $orderBookedFor
							);
	} else {
		if(!isSet($displayDateTimeCancelled)){
			$displayDateTimeCancelled = "";
		}
		if(!isSet($displayDateTimeCompleted)){
			$displayDateTimeCompleted = "";
		}
		$ordersOther[] = array(
								'TheOrderID' => $row['TheOrderID'],
								'OrderStatus' => $orderStatus,
								'OrderUserNotes' => $row['OrderUserNotes'],
								'OrderMessageStatus' => $messageStatus,
								'OrderLastMessageFromUser' => $displayLastMessageFromUser,
								'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
								'OrderStartTime' => $displayDateTimeStart,
								'OrderEndTime' => $displayDateTimeEnd,
								'DateTimeApproved' => $displayDateTimeApproved,
								'DateTimeCreated' => $displayDateTimeCreated,
								'DateTimeUpdated' => $displayDateTimeUpdated,
								'DateTimeCancelled' => $displayDateTimeCancelled,
								'DateTimeCompleted' => $displayDateTimeCompleted,
								'OrderContent' => $row['OrderContent'],
								'OrderAdminNote' => $row['OrderAdminNote'],
								'OrderFinalPrice' => $displayOrderFinalPrice,
								'OrderApprovedByUser' => $displayOrderApprovedByUser,
								'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
								'OrderApprovedByName' => $orderApprovedBy,
								'OrderRoomName' => $orderRoomName,
								'OrderBookedFor' => $orderBookedFor
							);
	}
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'orders.html.php';
?>