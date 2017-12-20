<?php
// Update completed bookings
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
			$sql = 'SELECT 		b.`bookingID`										AS BookingID,
								b.`meetingRoomID`									AS TheMeetingRoomID, 
								(
									SELECT	`name`
									FROM	`meetingroom`
									WHERE	`meetingRoomID` = TheMeetingRoomID 
								)													AS TheMeetingRoomName,
								b.`startDateTime`									AS StartDateTime,
								b.`endDateTime`										AS EndDateTime,
								b.`orderID` 										AS OrderID,
								SUM(eo.`amount`*ex.`price`)							AS TotalOrderCost,
								GROUP_CONCAT(
												CONCAT(eo.`amount`, " × ", ex.`name`) 
												SEPARATOR "\n"
											)										AS TotalOrder
					FROM 		`booking` b
					LEFT JOIN 	(
												`orders` o
									INNER JOIN 	`extraorders` eo
									ON 			eo.`orderID` = o.`orderID`
									INNER JOIN 	`extra` ex
									ON			ex.`extraID` = eo.`extraID`
								)
					ON 			o.`orderID` = b.`orderID`
					WHERE 		CURRENT_TIMESTAMP >= b.`endDateTime`
					AND 		b.`actualEndDateTime` IS NULL
					AND 		b.`dateTimeCancelled` IS NULL
					AND			b.`bookingID` >= :minBookingID
					GROUP BY 	b.`bookingID`';
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

				// Add log events that booking/order was set as completed
				$meetingRoomName = $booking['TheMeetingRoomName'];
				$startDateTimeString = $booking['StartDateTime'];
				$endDateTimeString = $booking['EndDateTime'];
				$startDateTime = correctDatetimeFormat($startDateTimeString);
				$endDateTime = correctDatetimeFormat($endDateTimeString);

				$displayValidatedStartDate = convertDatetimeToFormat($startDateTime , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
				$displayValidatedEndDate = convertDatetimeToFormat($endDateTime, 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

				$logEventDescription = 	"The booking for the meeting room: " . $meetingRoomName . 
										"\nStarting at: " . $displayValidatedStartDate . 
										"\nEnding at: " . $displayValidatedEndDate . 
										"\nWas set as completed due to the scheduled time being over.";

				$sql = "INSERT INTO `logevent`
						SET			`actionID` = 	(
														SELECT 	`actionID` 
														FROM 	`logaction`
														WHERE 	`name` = 'Booking Completed'
													),
									`description` = :description";
				$s = $pdo->prepare($sql);
				$s->bindValue(':description', $logEventDescription);
				$s->execute();

				if(!empty($orderID)){
					$orderTotalOrder = $booking['TotalOrder'];
					$orderTotalCost = $booking['TotalOrderCost'];
					$logEventDescription = 	"Order was completed automatically due to the following: " .
											"\nThe booking for the meeting room: " . $meetingRoomName . 
											"\nStarting at: " . $displayValidatedStartDate . 
											"\nEnding at: " . $displayValidatedEndDate . 
											"\nWas set as completed." .
											"\nItem(s) Ordered: \n" . $orderTotalOrder .
											"\nTotal Order Costt: " . convertToCurrency($orderTotalCost);;

					$sql = "INSERT INTO `logevent`
							SET			`actionID` = 	(
															SELECT 	`actionID` 
															FROM 	`logaction`
															WHERE 	`name` = 'Order Completed'
														),
										`description` = :description";
					$s = $pdo->prepare($sql);
					$s->bindValue(':description', $logEventDescription);
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
?>