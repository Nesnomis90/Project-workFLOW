<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// Cron does 1 run per minute (fastest)

// Update completed bookings
function updateCompletedBookings(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		if(!isSet($pdo)){
			$pdo = connect_to_db();
		}

		$sql = "UPDATE 	`booking`
				SET		`actualEndDateTime` = `endDateTime`,
						`cancellationCode` = NULL,
						`emailSent` = 1
				WHERE 	CURRENT_TIMESTAMP > `endDateTime`
				AND 	`actualEndDateTime` IS NULL
				AND 	`dateTimeCancelled` IS NULL
				AND		`bookingID` <> 0";
		$pdo->exec($sql);

		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// Check if a meeting is about to start and alert the user by sending an email
// FIX-ME: PHP script only runs for 30 sec before cancelling. What if we have to send a lot of emails?
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
							b.`dateTimeCreated`			AS DateCreated,
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
				AND			u.`sendEmail` = 1
				AND			b.`bookingID` <> 0";
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
														'DateCreated' => $row['DateCreated'],
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

				$mailResult = sendEmail($email, $emailSubject, $emailMessage);

				echo "Email being sent out about a meeting starting soon: $emailMessage";	// TO-DO: Remove before uploading
				echo "<br />";
				echo "Email is being sent to: $email";
				echo "<br />";

				if($mailResult){
					// Update booking that we've "sent" an email to the user 
					try
					{
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
						
						if(!isSet($pdo)){
							$pdo = connect_to_db();
						}
						$sql = "UPDATE 	`booking`
								SET		`emailSent` = 1
								WHERE	`bookingID` = :bookingID";
						$s = $pdo->prepare($sql);
						$s->bindValue(':bookingID', $row['TheBookingID']);
						$s->execute();
					}
					catch(PDOException $e)
					{
						$pdo = null;
						return FALSE;
					}
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

$repetition = 3;
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
			echo "Successfully Sent Emails To User(s) That Meeting Is Starting Soon";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Send Emails To User(s) That Meeting Is Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Sent Emails To User(s) That Meeting Is Starting Soon";	// TO-DO: Remove before uploading.
	echo "<br />";
}

// Close database connection
$pdo = null;

// The actual actions taken // END //
?>