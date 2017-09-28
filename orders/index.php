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
	unset($_SESSION['EditStaffOrderIsApproved']);
	unset($_SESSION['EditStaffOrderOrderID']);
	unset($_SESSION['EditStaffOrderExtraOrdered']);
	unset($_SESSION['EditStaffOrderOrderMessages']);
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

	if(isSet($_SESSION['EditStaffOrderExtraOrdered'])){
		if(isSet($_POST['isApprovedForPurchase'])){
			$isApprovedForPurchaseArray = $_POST['isApprovedForPurchase'];
			foreach($_SESSION['EditStaffOrderExtraOrdered'] AS &$extra){
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
			foreach($_SESSION['EditStaffOrderExtraOrdered'] AS &$extra){
				$extra['ExtraBooleanApprovedForPurchase'] = 0;
				unset($extra); // destroy reference.
			}
		}
		if(isSet($_POST['isPurchased'])){
			$isPurchasedArray = $_POST['isPurchased'];
			foreach($_SESSION['EditStaffOrderExtraOrdered'] AS &$extra){
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
			foreach($_SESSION['EditStaffOrderExtraOrdered'] AS &$extra){
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
	$validatedIsApproved = $orderIsApproved;

	// Do actual input validation
	if(validateString($validatedOrderCommunicationToUser) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['OrderStaffDetailsFeedback'] = "Your submitted message to the user has illegal characters in it.";
	}

	// Check if input length is allowed
		// OrderCommunicationToUser
	$invalidOrderCommunicationToUser = isLengthInvalidOrderMessage($validatedOrderCommunicationToUser);
	if($invalidOrderCommunicationToUser AND !$invalidInput){
		$_SESSION['OrderStaffDetailsFeedback'] = "Your submitted message to the user is too long.";
		$invalidInput = TRUE;
	}

	return array($invalidInput, $validatedOrderCommunicationToUser, $validatedIsApproved);
}

// if staff wants to edit Order information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Details') OR
	(isSet($_SESSION['refreshEditStaffOrder']) AND $_SESSION['refreshEditStaffOrder'])
	){

	// Check if we're activated by a user or by a forced refresh
	if(isSet($_SESSION['refreshEditStaffOrder']) AND $_SESSION['refreshEditStaffOrder']){
		// Confirm we've refreshed
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
		if(isSet($_SESSION['EditStaffOrderExtraOrdered'])){
			$extraOrdered = $_SESSION['EditStaffOrderExtraOrdered'];
		}
		if(isSet($_SESSION['EditStaffOrderOrderMessages'])){
			$orderMessages = $_SESSION['EditStaffOrderOrderMessages'];
		}
	} else {
		// Make sure we don't have any remembered values in memory
		clearEditStaffOrderSessions();

		$orderID = $_POST['OrderID'];

		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$sql = 'SELECT 		`orderID`					AS TheOrderID,
								`orderUserNotes`			AS OrderUserNotes,
								`dateTimeCreated`			AS DateTimeCreated,
								`dateTimeUpdated`			AS DateTimeUpdated,
								`orderApprovedByUser`		AS OrderApprovedByUser,
								`orderApprovedByAdmin`		AS OrderApprovedByAdmin,
								`orderApprovedByStaff`		AS OrderApprovedByStaff,
								`orderChangedByUser`		AS OrderChangedByUser,
								`orderChangedByStaff`		AS OrderChangedByStaff,
								`orderNewMessageFromUser`	AS OrderNewMessageFromUser,
								`orderNewMessageFromStaff`	AS OrderNewMessageFromStaff
					FROM 		`orders`
					WHERE		`orderID` = :OrderID
					LIMIT 		1';

			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			// Create an array with the row information we retrieved
			$row = $s->fetch(PDO::FETCH_ASSOC);
			$_SESSION['EditStaffOrderOriginalInfo'] = $row;

			if($row['OrderApprovedByAdmin'] == 1 OR $row['OrderApprovedByStaff'] == 1){
				$orderIsApproved = 1;
			} else {
				$orderIsApproved = 0;
			}

			$dateTimeCreated = $row['DateTimeCreated'];
			$displayDateTimeCreated = convertDatetimeToFormat($dateTimeCreated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

			if(!empty($row['DateTimeUpdated'])){
				$dateTimeUpdated = $row['DateTimeUpdated'];
				$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);				
			} else {
				$displayDateTimeUpdated = "N/A";
			}

			$_SESSION['EditStaffOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditStaffOrderOriginalInfo']['DateTimeCreated'] = $displayDateTimeCreated;
			$_SESSION['EditStaffOrderOriginalInfo']['DateTimeUpdated'] = $displayDateTimeUpdated;
			$_SESSION['EditStaffOrderOrderID'] = $orderID;

			// Get information about the extras ordered
			$sql = 'SELECT 		ex.`extraID` 											AS ExtraID,
								ex.`name`												AS ExtraName,
								eo.`amount`												AS ExtraAmount,
								IFNULL(eo.`alternativePrice`, ex.`price`)				AS ExtraPrice,
								IFNULL(eo.`alternativeDescription`, ex.`description`)	AS ExtraDescription,
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

			$_SESSION['EditStaffOrderOriginalInfo']['ExtraOrdered'] = $extraOrdered;
			$_SESSION['EditStaffOrderExtraOrdered'] = $extraOrdered;

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

			$_SESSION['EditStaffOrderOrderMessages'] = $orderMessages;

			// Update that there are no new messages from user
			$sql = "UPDATE	`orders`
					SET		`orderNewMessageFromUser` = 0
					WHERE	`orderID` = :OrderID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':OrderID', $orderID);
			$s->execute();

			$pdo->commit();

			//Close the connection
			$pdo = null;
		}
		catch (PDOException $e)
		{
			$error = 'Error fetching order details.' . $e->getMessage();
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
			$pdo = null;
			exit();
		}
	}

	// Set original values
	$originalOrderIsApproved = $_SESSION['EditStaffOrderOriginalInfo']['OrderIsApproved'];
	$originalOrderUserNotes = $_SESSION['EditStaffOrderOriginalInfo']['OrderUserNotes'];
	$originalOrderCreated = $_SESSION['EditStaffOrderOriginalInfo']['DateTimeCreated'];
	$originalOrderUpdated = $_SESSION['EditStaffOrderOriginalInfo']['DateTimeUpdated'];

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
	}

	$extraChanged = FALSE;
	if($original['ExtraOrdered'] != $_SESSION['EditStaffOrderExtraOrdered']){
		$numberOfChanges++;
		$extraChanged = TRUE;
	}

	$orderID = $_POST['OrderID'];
	
	if($numberOfChanges > 0){
		// Some changes were made, let's update!
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			if($extraChanged OR $messageAdded){
				$pdo->beginTransaction();
			}

			if($setAsApproved){
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`orderApprovedByUserID` = :orderApprovedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':orderApprovedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`orderApprovedByUserID` = NULL
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
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
				foreach($_SESSION['EditStaffOrderExtraOrdered'] AS $key => $extra){
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
						$orderApprovedByUserID = $_SESSION['LoggedInUserID'];
					} else {
						$orderApprovedByUserID = NULL;
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
											`orderApprovedByUserID` = :orderApprovedByUserID,
											`purchased` = CURRENT_TIMESTAMP,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} elseif($extraBooleanApprovedForPurchase == 1 AND $extraBooleanPurchased == 0){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = CURRENT_TIMESTAMP,
											`orderApprovedByUserID` = :orderApprovedByUserID,
											`purchased` = NULL,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} elseif($extraBooleanApprovedForPurchase == 0 AND $extraBooleanPurchased == 1){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`orderApprovedByUserID` = :orderApprovedByUserID,
											`purchased` = CURRENT_TIMESTAMP,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} else {
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`orderApprovedByUserID` = :orderApprovedByUserID,
											`purchased` = NULL,
											`purchasedByUserID` = :purchasedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						}
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':orderApprovedByUserID', $orderApprovedByUserID);
						$s->bindValue(':purchasedByUserID', $purchasedByUserID);
						$s->execute();
					} elseif($updateApprovedForPurchase AND !$updatePurchased){
						if($extraBooleanApprovedForPurchase == 1){
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = CURRENT_TIMESTAMP,
											`orderApprovedByUserID` = :orderApprovedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						} else {
							$sql = "UPDATE	`extraorders`
									SET		`approvedForPurchase` = NULL,
											`orderApprovedByUserID` = :orderApprovedByUserID
									WHERE	`orderID` = :OrderID
									AND		`extraID` = :ExtraID";
						}
						$s = $pdo->prepare($sql);
						$s->bindValue(':OrderID', $orderID);
						$s->bindValue(':ExtraID', $extraID);
						$s->bindValue(':orderApprovedByUserID', $orderApprovedByUserID);
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

			if($extraChanged OR $messageAdded){
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
						o.`dateTimeCreated`								AS DateTimeCreated,
						o.`dateTimeUpdated`								AS DateTimeUpdated,
						o.`orderApprovedByUser`							AS OrderApprovedByUser,
						o.`orderApprovedByAdmin`						AS OrderApprovedByAdmin,
						o.`orderApprovedByStaff`						AS OrderApprovedByStaff,
						o.`orderChangedByUser`							AS OrderChangedByUser,
						o.`orderChangedByStaff`							AS OrderChangedByStaff,
						o.`orderNewMessageFromUser`						AS OrderNewMessageFromUser,
						o.`orderNewMessageFromStaff`					AS OrderNewMessageFromStaff,
						GROUP_CONCAT(ex.`name`, " (", eo.`amount`, ")"
							SEPARATOR "\n")								AS OrderContent,
						b.`startDateTime`								AS OrderStartDateTime,
						b.`endDateTime`									AS OrderEndDateTime,
						m.`name`										AS OrderRoomName,
						c.`name`										AS OrderBookedFor,
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
						)												AS OrderLastMessageFromUser
			FROM 		`orders` o
			INNER JOIN	`extraorders` eo
			ON 			eo.`orderID` = o.`orderID`
			INNER JOIN 	`extra` ex
			ON 			eo.`extraID` = ex.`extraID`
			INNER JOIN	`booking` b
			ON 			b.`orderID` = o.`orderID`
			INNER JOIN	`meetingroom` m
			ON 			m.`meetingRoomID` = b.`meetingRoomID`
			INNER JOIN 	`company` c
			ON 			c.`companyID` = b.`companyID`
			WHERE		o.`dateTimeCancelled` IS NULL
			AND			b.`dateTimeCancelled` IS NULL
			AND			b.`actualEndDateTime` IS NULL
			AND			b.`orderID` IS NOT NULL
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
		$newOrder = FALSE;
	} else {
		$displayDateTimeUpdated = "N/A";
		$newOrder = TRUE;
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

	$orderMessagesSent = $row['OrderMessagesSent'];
	$orderNewMessageFromUser = $row['OrderNewMessageFromUser'];
	$orderNewMessageFromStaff = $row['OrderNewMessageFromStaff'];
	$orderChangedByUser = $row['OrderChangedByUser'];
	$orderChangedByStaff = $row['OrderChangedByStaff'];
	$extrasOrdered = $row['OrderExtrasOrdered'];
	$extrasApproved = $row['OrderExtrasApproved'];
	$extrasPurchased = $row['OrderExtrasPurchased'];

	if($orderNewMessageFromStaff == 1 AND $orderNewMessageFromUser == 1){
		$messageStatus = "New Message From User!\nMessage Sent To User Not Seen Yet.";
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
		$orderStatus = "New Order!\nPending Staff Approval";
	} elseif($orderIsApprovedByStaff AND $orderIsApprovedByUser){
		$orderStatus = "Order Approved!";
		if($extrasApproved == $extrasOrdered AND $extrasPurchased == $extrasOrdered){
			$orderStatus .= "\nAll Items Approved!\nAll Items Purchased!";
		} elseif($extrasApproved == $extrasOrdered AND $extrasPurchased < $extrasOrdered){
			$orderStatus .= "\nAll Items Approved!\nPending Item Purchases.";
		} elseif($extrasApproved < $extrasOrdered){
			$orderStatus .= "\nPending Item Approval.\nPending Item Purchases.";
		}
	} elseif($orderIsApprovedByStaff AND !$orderIsApprovedByUser) {
		$orderStatus = "Order Not Approved After Change.\nPending User Approval.";
	} elseif(!$orderIsApprovedByStaff AND $orderIsApprovedByUser) {
		$orderStatus = "Order Not Approved.\nPending Staff Approval.";
	} else {
		$orderStatus = "Order Not Approved After Change.\nPending Staff Approval.\nPending User Approval.";
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

	// Create an array with the actual key/value pairs we want to use in our HTML
	$order[] = array(
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
						'OrderContent' => $row['OrderContent'],
						'OrderApprovedByUser' => $displayOrderApprovedByUser,
						'OrderApprovedByStaff' => $displayOrderApprovedByStaff,
						'OrderRoomName' => $row['OrderRoomName'],
						'OrderBookedFor' => $row['OrderBookedFor']
					);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'orders.html.php';
?>