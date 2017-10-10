<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// Cron does 1 run per minute (fastest)

// Update completed bookings
// Untested with new order update functions.
function updateCompletedBookings(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		$sql = "SELECT 	MIN(`bookingID`)
				FROM 	`booking`
				WHERE 	CURRENT_TIMESTAMP >= `endDateTime`
				AND 	`actualEndDateTime` IS NULL
				AND 	`dateTimeCancelled` IS NULL
				LIMIT 	1";
		$return = $pdo->query($sql);
		$minBookingID = $return->fetchColumn();

		if(!empty($minBookingID) AND $minBookingID > 0){
			// There are completed bookings that needs to be updated
			// Minimize query time by using index search provided by the lowest bookingID found earlier.
			$sql = "SELECT 	`bookingID`	AS BookingID,
							`orderID` 	AS OrderID
					FROM 	`booking`
					WHERE 	CURRENT_TIMESTAMP >= `endDateTime`
					AND 	`actualEndDateTime` IS NULL
					AND 	`dateTimeCancelled` IS NULL
					AND		`bookingID` >= :minBookingID";
			$s = $pdo->prepare($sql);
			$s->bindValue(':minBookingID', $minBookingID);
			$s->execute();
			$result = $s->fetchAll(PDO::FETCH_ASSOC);	

			$pdo->beginTransaction();

			foreach($result AS $booking){
				$bookingID = $booking['BookingID'];
				$orderID = $booking['OrderID'];

				$sql = "UPDATE 	`booking`
						SET		`actualEndDateTime` = `endDateTime`,
								`cancellationCode` = NULL,
								`emailSent` = 1
						WHERE 	CURRENT_TIMESTAMP > `endDateTime`
						AND 	`actualEndDateTime` IS NULL
						AND 	`dateTimeCancelled` IS NULL
						AND		`bookingID` = :BookingID";
				$s = $pdo->prepare($sql);
				$s->bindValue(':BookingID', $bookingID);
				$s->execute();

				if(!empty($orderID)){
					// should only update once, when the booking hasn't been marked as completed yet
					// but just in case, make sure the value of finalprice hasn't been set yet
					$sql = "UPDATE	`orders`
							SET		`orderFinalPrice` = (
															SELECT		SUM(IFNULL(eo.`alternativePrice`, ex.`price`) * eo.`amount`) AS FullPrice
															FROM		`extra` ex
															INNER JOIN 	`extraorders` eo
															ON 			ex.`extraID` = eo.`extraID`
															WHERE		eo.`orderID` = :OrderID
														)
							WHERE	`orderID` = :OrderID
							AND		`orderFinalPrice` IS NULL";
					$s = $pdo->prepare($sql);
					$s->bindValue(':OrderID', $orderID);
					$s->execute();
				}
			}

			$pdo->commit();
		}

		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo->rollBack();
		$pdo = null;
		return FALSE;
	}
}

function alertStaffThatMeetingWithOrderIsAboutToStart(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		// Get all upcoming meetings that are TIME_LEFT_IN_MINUTES_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL minutes away from starting.
		// That have an active order attached that we haven't already alerted/sent email to staff about
		// Only try to alert up to 1 minute before it starts (should occur way before)
		// Only gets orders that are connected to a meeting that still has a meeting room and a company assigned whilst still being active itself.
		// Only get orders that actually have ordered items attached to it.
		$sql = 'SELECT 		m.`name`										AS MeetingRoomName,
							c.`name`										AS CompanyName,
							b.`startDateTime`								AS StartDate,
							b.`endDateTime`									AS EndDate,
							o.`orderID`										AS TheOrderID,
							o.`orderApprovedByUser`							AS OrderApprovedByUser,
							o.`orderApprovedByAdmin`						AS OrderApprovedByAdmin,
							o.`orderApprovedByStaff`						AS OrderApprovedByStaff,
							GROUP_CONCAT(ex.`name`, " (", eo.`amount`, ")"
								SEPARATOR "\n")								AS OrderContent,
							COUNT(eo.`extraID`)								AS OrderExtrasOrdered,
							COUNT(eo.`approvedForPurchase`)					AS OrderExtrasApproved,
							COUNT(eo.`purchased`)							AS OrderExtrasPurchased
				FROM		`booking` b
				INNER JOIN 	`orders` o
				ON 			o.`orderID` = b.`orderID`
				INNER JOIN	(
										`extraorders` eo
							INNER JOIN 	`extra` ex
							ON 			eo.`extraID` = ex.`extraID`
				)
				ON 			eo.`orderID` = o.`orderID`
				INNER JOIN 	`meetingroom` m
				ON			m.`meetingRoomID` = b.`meetingRoomID`
				INNER JOIN 	`company` c
				ON 			c.`companyID` = b.`companyID`
				WHERE 		DATE_SUB(b.`startDateTime`, INTERVAL :bufferMinutes MINUTE) < CURRENT_TIMESTAMP
				AND			DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
				AND 		b.`dateTimeCancelled` IS NULL
				AND			o.`dateTimeCancelled` IS NULL
				AND 		b.`actualEndDateTime` IS NULL
				AND			b.`orderID` IS NOT NULL
				AND			o.`emailSoonSent` = 0
				GROUP BY 	o.`orderID`';
		$s = $pdo->prepare($sql);
		$s->bindValue(':bufferMinutes', TIME_LEFT_IN_MINUTES_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL);
		$s->execute();

		$upcomingMeetingsNotAlerted = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($upcomingMeetingsNotAlerted)){
			$rowNum = sizeOf($upcomingMeetingsNotAlerted);
		} else {
			$rowNum  = 0;
		}

		if($rowNum > 0){
			// Get staff/admin emails
			$sql = "SELECT 		u.`email`		AS Email
					FROM 		`user` u
					INNER JOIN 	`accesslevel` a
					ON			a.`AccessID` = u.`AccessID`
					WHERE		(
												a.`AccessName` = 'Admin'
									AND			u.`sendAdminEmail` = 1
								)
					OR 			a.`AccessName` = 'Staff'";
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);

			if(isSet($result)){
				foreach($result AS $Email){
					$email[] = $Email['Email'];
				}
				$staffAndAdminEmails = implode(", ", $email);
				echo "Will be sent to these email(s): " . $staffAndAdminEmails; // TO-DO: Remove before uploading
				echo "<br />";
			} else {
				echo "Found no Admin/Staff that want to receive an Email"; // TO-DO: Remove before uploading
				echo "<br />";
			}

			echo "Number of orders to Alert about: $rowNum";	// TO-DO: Remove before uploading.
			echo "<br />";

			try
			{
				$pdo->beginTransaction();

				foreach($upcomingMeetingsNotAlerted AS $row){
					$orderApprovedByUser = ($row['OrderApprovedByUser'] == 1) ? TRUE : FALSE;
					$orderApprovedByAdmin = ($row['OrderApprovedByAdmin'] == 1) ? TRUE : FALSE;
					$orderApprovedByStaff = ($row['OrderApprovedByStaff'] == 1) ? TRUE : FALSE;

					// Check if the order itself is approved or not (by both parties)
					if($orderApprovedByUser AND ($orderApprovedByAdmin OR $orderApprovedByStaff)){
						$approvedStatus = "Order approved by both staff and user.";
					} elseif(!$orderApprovedByUser AND ($orderApprovedByAdmin OR $orderApprovedByStaff)){
						$approvedStatus = "Order not yet approved by user.";
					} elseif($orderApprovedByUser AND !$orderApprovedByAdmin AND !$orderApprovedByStaff){
						$approvedStatus = "Order not yet approved by staff.";
					} elseif(!$orderApprovedByUser AND !$orderApprovedByAdmin AND !$orderApprovedByStaff){
						$approvedStatus = "Order not yet approved by staff and user.";
					}

					// Check if the extras ordered are approved, and purchased, or not.
					$numberOfExtrasOrdered = $row['OrderExtrasOrdered'];
					$numberOfExtrasApproved = $row['OrderExtrasApproved'];
					$numberOfExtrasPurchased = $row['OrderExtrasPurchased'];
					if($numberOfExtrasApproved == $numberOfExtrasOrdered AND $numberOfExtrasPurchased == $numberOfExtrasOrdered){
						$extrasOrderedStatus = "All $numberOfExtrasOrdered extras have been set as approved and purchased";
					} elseif($numberOfExtrasApproved == $numberOfExtrasOrdered AND $numberOfExtrasPurchased < $numberOfExtrasOrdered){
						$extrasOrderedStatus = "All $numberOfExtrasOrdered extras have been set as approved. $numberOfExtrasPurchased have been set purchased";
					} elseif($numberOfExtrasApproved < $numberOfExtrasOrdered AND $numberOfExtrasPurchased == $numberOfExtrasOrdered){
						$extrasOrderedStatus = "$numberOfExtrasApproved out of $numberOfExtrasOrdered extras has been set as approved. All of them have been set as purchased though";
					} elseif($numberOfExtrasApproved < $numberOfExtrasOrdered AND $numberOfExtrasPurchased < $numberOfExtrasOrdered){
						$extrasOrderedStatus = "$numberOfExtrasApproved out of $numberOfExtrasOrdered extras has been set as approved. $numberOfExtrasPurchased has been set as purchased";
					}

					$emailSubject = "Upcoming Order Info!";

					$displayStartDate = convertDatetimeToFormat($row['StartDate'] , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					$displayEndDate = convertDatetimeToFormat($row['EndDate'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

					$emailMessage = 
					"A booked meeting with an active order is starting soon!\n" . 
					"The booked Meeting Room: " . $row['MeetingRoomName'] . ".\n" . 
					"The booked Start Time: " . $displayStartDate . ".\n" .
					"The booked End Time: " . $displayEndDate . ".\n" .
					"The booked Company: " . $row['CompanyName'] . ".\n\n" .
					"The order approval status: " . $approvedStatus . ".\n" .
					"The order Content: " . $row['OrderContent'] . ".\n" .
					"The extras ordered status: " . $extrasOrderedStatus . ".";

					$email = $staffAndAdminEmails;

					// Instead of sending the email here, we store them in the database to send them later instead.
					// That way, we can limit the amount of email being sent out easier.
					// Store email to be sent out later
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = :orderStartDateTime';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->bindValue(':orderStartDateTime', $row['StartDate']);
					$s->execute();

					// Update booking that we've "sent" an email to the user 
					$sql = "UPDATE 	`orders`
							SET		`emailSoonSent` = 1
							WHERE	`orderID` = :orderID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':orderID', $row['TheOrderID']);
					$s->execute();
				}

				$pdo->commit();
			}
			catch(PDOException $e)
			{
				$pdo->rollBack();
				$pdo = null;
				return FALSE;
			}
		}
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// Check if a meeting is about to start and alert the user by "sending an email" e.g. adding it to the email queue.
function alertUserThatMeetingIsAboutToStart(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		// Get all upcoming meetings that are TIME_LEFT_IN_MINUTES_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL minutes away from starting.
		// That we haven't already alerted/sent email to
		// And only for the users who want to receive emails
		// Only try to alert a user up to 1 minute until meeting starts (in theory they should instantly get alerted)
		// Only try to alert a user if the booking was made longer than MINIMUM_TIME_PASSED_IN_MINUTES_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL minutes ago
		$sql = "SELECT 		(
								SELECT 	`name`
								FROM 	`meetingroom`
								WHERE 	`meetingRoomID` = b.`meetingRoomID`
							)							AS MeetingRoomName,
							(
								SELECT 	`name`
								FROM 	`company`
								WHERE 	`companyID` = b.`companyID`
							)							AS CompanyName,
							u.`email`					AS UserEmail,
							b.`bookingID`				AS TheBookingID,
							b.`startDateTime`			AS StartDate,
							b.`endDateTime`				AS EndDate,
							b.`displayName`				AS DisplayName,
							b.`description`				AS BookingDescription,
							b.`cancellationCode`		AS CancelCode
				FROM		`booking` b
				INNER JOIN 	`user` u
				ON			u.`userID` = b.`userID`
				WHERE 		DATE_SUB(b.`startDateTime`, INTERVAL :bufferMinutes MINUTE) < CURRENT_TIMESTAMP
				AND			DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
				AND 		b.`dateTimeCancelled` IS NULL
				AND 		b.`actualEndDateTime` IS NULL
				AND			b.`cancellationCode` IS NOT NULL
				AND 		DATE_ADD(b.`dateTimeCreated`, INTERVAL :waitMinutes MINUTE) < CURRENT_TIMESTAMP
				AND			b.`emailSent` = 0
				AND			u.`sendEmail` = 1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':bufferMinutes', TIME_LEFT_IN_MINUTES_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL);
		$s->bindValue(':waitMinutes', MINIMUM_TIME_PASSED_IN_MINUTES_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL);
		$s->execute();

		$result = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($result)){
			$rowNum = sizeOf($result);
		} else {
			$rowNum  = 0;
		}

		if($rowNum > 0){
			foreach($result AS $row){
				$upcomingMeetingsNotAlerted[] = array(
														'MeetingRoomName' => $row['MeetingRoomName'],
														'CompanyName' => $row['CompanyName'],
														'UserEmail' => $row['UserEmail'],
														'TheBookingID' => $row['TheBookingID'],
														'StartDate' => $row['StartDate'],
														'EndDate' => $row['EndDate'],
														'DisplayName' => $row['DisplayName'],
														'BookingDescription' => $row['BookingDescription'],
														'CancelCode' => $row['CancelCode']
													);
			}

			$numberOfUsersToAlert = sizeOf($upcomingMeetingsNotAlerted);
			echo "Number of users to Alert: $numberOfUsersToAlert";	// TO-DO: Remove before uploading.
			echo "<br />";

			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

				if(!isSet($pdo)){
					$pdo = connect_to_db();
				}

				$pdo->beginTransaction();

				foreach($upcomingMeetingsNotAlerted AS $row){
					$emailSubject = "Upcoming Meeting Info!";

					$displayStartDate = convertDatetimeToFormat($row['StartDate'] , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					$displayEndDate = convertDatetimeToFormat($row['EndDate'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

					$emailMessage = 
					"You have a booked meeting starting soon!\n" . 
					"Your booked Meeting Room: " . $row['MeetingRoomName'] . ".\n" . 
					"Your booked Start Time: " . $displayStartDate . ".\n" .
					"Your booked End Time: " . $displayEndDate . ".\n\n" .
					"If you wish to cancel your meeting, or just end it early, you can easily do so by clicking the link given below.\n" .
					"Click this link to cancel your booked meeting: " . $_SERVER['HTTP_HOST'] . 
					"/booking/?cancellationcode=" . $row['CancelCode'];

					$email = $row['UserEmail'];

					// Instead of sending the email here, we store them in the database to send them later instead.
					// That way, we can limit the amount of email being sent out easier.
					// Store email to be sent out later
					$sql = 'INSERT INTO	`email`
							SET			`subject` = :subject,
										`message` = :message,
										`receivers` = :receivers,
										`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 1 HOUR);';
					$s = $pdo->prepare($sql);
					$s->bindValue(':subject', $emailSubject);
					$s->bindValue(':message', $emailMessage);
					$s->bindValue(':receivers', $email);
					$s->execute();

					// Update booking that we've "sent" an email to the user 
					$sql = "UPDATE 	`booking`
							SET		`emailSent` = 1
							WHERE	`bookingID` = :bookingID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':bookingID', $row['TheBookingID']);
					$s->execute();
				}

				$pdo->commit();
			}
			catch(PDOException $e)
			{
				$pdo->rollBack();
				$pdo = null;
				return FALSE;
			}
		}
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// Remove emails that have been stored further than the dateTimeRemove set when it went into the queue.
// In theory this should never occur, since every email should be deleted from queue on being sent.
function removeOldEmailsFromQueue(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		$sql = "DELETE FROM	`email`
				WHERE		`dateTimeRemove` < CURRENT_TIMESTAMP
				AND			`emailID` <> 0";
		$pdo->query($sql);

		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}	
}

// Check our saved emails and attempt to send as many as we have limited ourselves to
// Always get the freshest stored emails first.
// TO-DO: Change from DESC to ASC (or nothing) if this doesn't work well
function checkEmailQueue(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		$sql = "SELECT		`emailID`	AS TheEmailID,
							`subject`	AS EmailSubject,
							`message`	AS EmailMessage,
							`receivers`	AS EmailsToSendTo
				FROM		`email`
				ORDER BY	UNIX_TIMESTAMP(`dateTimeAdded`) DESC
				LIMIT		:limitEmailsToSendAtOnce";
		$s = $pdo->prepare($sql);
		$s->bindValue(':limitEmailsToSendAtOnce', MAX_NUMBER_OF_EMAILS_TO_SEND_AT_ONCE);
		$s->execute();

		$emailsToSendOut = $s->fetchAll(PDO::FETCH_ASSOC);
		if(isSet($emailsToSendOut)){
			$numberOfEmailsInQueue = sizeOf($emailsToSendOut);
		} else {
			$numberOfEmailsInQueue  = 0;
		}

		echo "Number of emails to send out: $numberOfEmailsInQueue";	// TO-DO: Remove before uploading.
		echo "<br />";

		if($numberOfEmailsInQueue > 0){

			foreach($emailsToSendOut AS $queue){
				$emailSubject = $queue['EmailSubject'];

				$emailMessage = $queue['EmailMessage'];

				$emailAsText = $queue['EmailsToSendTo'];
				$email = explode(", ", $emailAsText); //sendEmail takes array as input. We store it as text

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				if($mailResult){
					
					echo "Succesfully sent email to $emailAsText.\nEmail message sent out was: $emailMessage"; // TO-DO: Remove before uploading
					echo "<br />";

					// Email has been succesfully prepared, so we don't need to have it in the queue anymore.
					$sql = "DELETE FROM `email`
							WHERE		`emailID` = :emailID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':emailID', $queue['TheEmailID']);
					$s->execute();
				}
			}
		}

		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// The actual actions taken // START //
	// Run our SQL functions
$updatedCompletedBookings = updateCompletedBookings();
$alertedUserOnMeetingStart = alertUserThatMeetingIsAboutToStart();
$checkedEmailQueue = checkEmailQueue();
$removedOldEmailsFromQueue = removeOldEmailsFromQueue();
$alertedStaffOnOrderStart = alertStaffThatMeetingWithOrderIsAboutToStart();

$repetition = 0; // No repetition since it checks once a minute
$sleepTime = 1; // Second(s)

// If we get a FALSE back, the function failed to do its purpose
// Let's wait and try again x times.
if(!$updatedCompletedBookings){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = updateCompletedBookings();
		if($success){
			echo "Successfully Updated Completed Bookings";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Update Completed Bookings";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Updated Completed Bookings";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$alertedUserOnMeetingStart){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = alertUserThatMeetingIsAboutToStart();
		if($success){
			echo "Successfully Added Emails To Queue About Meetings Starting Soon";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Add Emails To Queue About Meetings Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Added Emails To Queue About Meetings Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$checkedEmailQueue){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = checkEmailQueue();
		if($success){
			echo "Successfully Checked Email Queue And Sent Emails If There Were Any";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Check Email Queue And Send Emails";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Checked Email Queue And Sent Emails If There Were Any";	// TO-DO: Remove before uploading.
	echo "<br />";
}

// No need to try to repeat this.
if(!$removedOldEmailsFromQueue){
	echo "Failed To Check Email Queue And Remove Old Emails";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Checked Email Queue And Removed Old Emails";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$alertedStaffOnOrderStart){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = alertStaffThatMeetingWithOrderIsAboutToStart();
		if($success){
			echo "Successfully Added Emails To Queue About Orders Starting Soon";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Add Emails To Queue About Orders Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Added Emails To Queue About Orders Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
}
// Close database connection
$pdo = null;

// The actual actions taken // END //
?>