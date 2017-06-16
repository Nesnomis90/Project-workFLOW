<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// Cron does 1 run per minute (fastest)

// Update completed bookings 
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	$pdo = connect_to_db();
	$sql = "UPDATE 	`booking`
			SET		`actualEndDateTime` = `endDateTime`,
					`cancellationCode` = NULL
			WHERE 	CURRENT_TIMESTAMP > `endDateTime`
			AND 	`actualEndDateTime` IS NULL
			AND 	`dateTimeCancelled` IS NULL
			AND		`bookingID` <> 0";
	$pdo->exec($sql);
	
	//Close the connection
	$pdo = null;
}
catch(PDOException $e)
{
	$error = 'Error updating booking: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

/////////////////////////////////////////////////////////////////////////////////////////////
// START Check if a meeting is about to start and alert the user by sending an email START //
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	$pdo = connect_to_db();
	// Get all upcoming meetings that are TIME_LEFT_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL minutes away from starting.
	// That we haven't already alerted/sent email to
	// Only try to alert a user up to 1 minute until meeting starts (in theory they should instantly get alerted)
	// Only try to alert a user if the booking was made longer than MINIMUM_TIME_PASSED_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL minutes ago
	$sql = "SELECT 	m.`name`					AS MeetingRoomName,
					c.`name`					AS CompanyName,
					u.`email`					AS UserEmail,
					b.`bookingID`				AS TheBookingID,
					b.`dateTimeCreated`			AS DateCreated,
					b.`startDateTime`			AS StartDate,
					b.`endDateTime`				AS EndDate,
					b.`displayName`				AS DisplayName,
					b.`description`				AS BookingDescription,
					b.`cancellationCode`		AS CancelCode
			FROM	`booking` b
			JOIN 	`meetingroom` m
			ON 		b.`meetingRoomID` = m.`meetingRoomID`
			JOIN	`company` c
			ON 		c.`companyID` = b.`companyID`
			JOIN	`user` u
			ON		u.`userID` = b.`userID`
			WHERE 	DATE_SUB(b.`startDateTime`, INTERVAL :bufferMinutes MINUTE) < CURRENT_TIMESTAMP
			AND		DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
			AND 	b.`dateTimeCancelled` IS NULL
			AND 	b.`actualEndDateTime` IS NULL
			AND		b.`cancellationCode` IS NOT NULL
			AND 	DATE_ADD(b.`dateTimeCreated`, INTERVAL :waitMinutes MINUTE) < CURRENT_TIMESTAMP
			AND		b.`emailSent` = 0
			AND		b.`bookingID` <> 0";		
	$s = $pdo->preare($sql);
	$s->bindValue(':bufferMinutes', TIME_LEFT_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL)
	$s->bindValue(':waitMinutes', MINIMUM_TIME_PASSED_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL)
	$s->execute();
	
	$result = $s->fetchAll();
	$rowNum = sizeOf($result);
	
	//Close the connection
	$pdo = null;
	
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
		
		foreach(upcomingMeetingsNotAlerted AS $row){
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
			
			if($mailResult){
				// Update booking that we've "sent" an email to the user 
				try
				{
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
					
					$pdo = connect_to_db();
					$sql = "UPDATE 	`booking`
							SET		`emailSent` = 1
							WHERE	`bookingID` = :bookingID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':bookingID', $row['TheBookingID']);
					$s->execute();
					
					//Close the connection
					$pdo = null;
				}
				catch(PDOException $e)
				{
					$error = 'Error updating booking: ' . $e->getMessage();
					include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
					$pdo = null;
					exit();
				}
			}
		}
	}
}
catch(PDOException $e)
{
	$error = 'Error checking if meeting time is starting soon: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// END Check if a meeting is about to start and alert the user by sending an email END //
/////////////////////////////////////////////////////////////////////////////////////////
?>