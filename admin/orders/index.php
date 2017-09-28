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
	unset($_SESSION['EditOrderExtraOrdered']);
	unset($_SESSION['EditOrderOrderMessages']);
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

// if admin wants to edit Order information
// we load a new html form
if ((isSet($_POST['action']) AND $_POST['action'] == 'Details') OR
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

		$orderID = $_POST['OrderID'];

		// Get information from database again on the selected order
		try
		{
			include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
			$pdo = connect_to_db();

			$sql = 'SELECT 		`orderID`						AS TheOrderID,
								`orderUserNotes`				AS OrderUserNotes,
								`dateTimeCreated`				AS DateTimeCreated,
								`dateTimeUpdated`				AS DateTimeUpdated,
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

			if(!empty($row['DateTimeUpdated'])){
				$dateTimeUpdated = $row['DateTimeUpdated'];
				$displayDateTimeUpdated = convertDatetimeToFormat($dateTimeUpdated , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);				
			} else {
				$displayDateTimeUpdated = "N/A";
			}

			$_SESSION['EditOrderOriginalInfo']['OrderIsApproved'] = $orderIsApproved;
			$_SESSION['EditOrderOriginalInfo']['DateTimeCreated'] = $displayDateTimeCreated;
			$_SESSION['EditOrderOriginalInfo']['DateTimeUpdated'] = $displayDateTimeUpdated;
			$_SESSION['EditOrderOrderID'] = $orderID;

			$sql = 'SELECT 		ex.`extraID`											AS ExtraID,
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

			$_SESSION['EditOrderOriginalInfo']['ExtraOrdered'] = $extraOrdered;
			$_SESSION['EditOrderExtraOrdered'] = $extraOrdered;

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
						SET		`orderCommunicationToUser` = :OrderCommunicationToUser,
								`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = CURRENT_TIMESTAMP,
								`adminNote` = :adminNote
								`orderApprovedByUserID` = :orderApprovedByUserID
						WHERE 	`orderID` = :OrderID';
				$s = $pdo->prepare($sql);
				$s->bindValue(':OrderID', $orderID);
				$s->bindValue(':approvedByAdmin', $approvedByAdmin);
				$s->bindValue(':approvedByStaff', $approvedByStaff);
				$s->bindValue(':adminNote', $validatedAdminNote);
				$s->bindValue(':orderApprovedByUserID', $_SESSION['LoggedInUserID']);
				$s->execute();
			} else {
				$sql = 'UPDATE 	`orders`
						SET		`orderApprovedByAdmin` = :approvedByAdmin,
								`orderApprovedByStaff` = :approvedByStaff,
								`dateTimeUpdated` = CURRENT_TIMESTAMP,
								`dateTimeApproved` = NULL,
								`adminNote` = :adminNote
								`orderApprovedByUserID` = NULL
						WHERE 	`orderID` = :OrderID';
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
						o.`dateTimeCreated`								AS DateTimeCreated,
						o.`dateTimeUpdated`								AS DateTimeUpdated,
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
		$newOrder = FALSE;
	} else {
		$displayDateTimeUpdated = "N/A";
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
						'OrderMessageStatus' => $messageStatus,
						'OrderLastMessageFromUser' => $displayLastMessageFromUser,
						'OrderLastMessageFromStaff' => $displayLastMessageFromStaff,
						'OrderStartTime' => $displayDateTimeStart,
						'OrderEndTime' => $displayDateTimeEnd,
						'DateTimeApproved' => $displayDateTimeApproved,
						'DateTimeCreated' => $displayDateTimeCreated,
						'DateTimeUpdated' => $displayDateTimeUpdated,
						'DateTimeCancelled' => $displayDateTimeCancelled,
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

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the Order list in HTML
include_once 'orders.html.php';
?>