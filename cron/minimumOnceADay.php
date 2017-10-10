<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once a day (more often in case SQL connection fails?)

function alertStaffAboutOrderStatusOfTheDay(){
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
				INNER JOIN 	`meetingroom` m
				ON			m.`meetingRoomID` = b.`meetingRoomID`
				INNER JOIN 	`company` c
				ON 			c.`companyID` = b.`companyID`
				LEFT JOIN	(
										`extraorders` eo
							INNER JOIN 	`extra` ex
							ON 			eo.`extraID` = ex.`extraID`
				)
				ON 			eo.`orderID` = o.`orderID`
				WHERE		DATE(b.`startDateTime`) = CURRENT_DATE
				AND			o.`emailCheckSent` = 0
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
				$orderCounter = 1;
				$emailMessage = "This is a summary of the orders set for today.";

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
					if($numberOfExtrasOrdered == 0){
						$extrasOrderedStatus = "This order currently has nothing ordered.";
					} elseif($numberOfExtrasApproved == $numberOfExtrasOrdered AND $numberOfExtrasPurchased == $numberOfExtrasOrdered){
						$extrasOrderedStatus = "All $numberOfExtrasOrdered extras have been set as approved and purchased";
					} elseif($numberOfExtrasApproved == $numberOfExtrasOrdered AND $numberOfExtrasPurchased < $numberOfExtrasOrdered){
						$extrasOrderedStatus = "All $numberOfExtrasOrdered extras have been set as approved. $numberOfExtrasPurchased have been set purchased";
					} elseif($numberOfExtrasApproved < $numberOfExtrasOrdered AND $numberOfExtrasPurchased == $numberOfExtrasOrdered){
						$extrasOrderedStatus = "$numberOfExtrasApproved out of $numberOfExtrasOrdered extras has been set as approved. All of them have been set as purchased though";
					} elseif($numberOfExtrasApproved < $numberOfExtrasOrdered AND $numberOfExtrasPurchased < $numberOfExtrasOrdered){
						$extrasOrderedStatus = "$numberOfExtrasApproved out of $numberOfExtrasOrdered extras has been set as approved. $numberOfExtrasPurchased has been set as purchased";
					}

					$displayStartDate = convertDatetimeToFormat($row['StartDate'] , 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);
					$displayEndDate = convertDatetimeToFormat($row['EndDate'], 'Y-m-d H:i:s', DATETIME_DEFAULT_FORMAT_TO_DISPLAY);

					$emailMessage .= 
					"\n\nOrder #$orderCounter scheduled for today:\n" . 
					"The booked Meeting Room: " . $row['MeetingRoomName'] . ".\n" . 
					"The booked Start Time: " . $displayStartDate . ".\n" .
					"The booked End Time: " . $displayEndDate . ".\n" .
					"The booked Company: " . $row['CompanyName'] . ".\n\n" .
					"The order approval status: " . $approvedStatus . ".\n" .
					"The order Content: " . $row['OrderContent'] . ".\n" .
					"The extras ordered status: " . $extrasOrderedStatus . ".";

					// Update booking that we've "sent" an email to the user 
					$sql = "UPDATE 	`orders`
							SET		`emailCheckSent` = 1
							WHERE	`orderID` = :orderID";
					$s = $pdo->prepare($sql);
					$s->bindValue(':orderID', $row['TheOrderID']);
					$s->execute();

					$orderCounter++;
				}

				$emailSubject = "Today's scheduled orders!";

				$email = $staffAndAdminEmails;

				// Instead of sending the email here, we store them in the database to send them later instead.
				// That way, we can limit the amount of email being sent out easier.
				// Store email to be sent out later
				$sql = 'INSERT INTO	`email`
						SET			`subject` = :subject,
									`message` = :message,
									`receivers` = :receivers,
									`dateTimeRemove` = DATE_ADD(CURRENT_TIMESTAMP INTERVAL 23 HOUR)';
				$s = $pdo->prepare($sql);
				$s->bindValue(':subject', $emailSubject);
				$s->bindValue(':message', $emailMessage);
				$s->bindValue(':receivers', $email);
				$s->execute();

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

// If, for some reason, a company does not have a subscription set. We set it to default.
function setDefaultSubscriptionIfCompanyHasNone(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();

		// Check if there is any information to change
		$sql = "SELECT 		COUNT(*)
				FROM 		`company`
				WHERE		`CompanyID` 
				NOT IN		(
								SELECT 	`CompanyID`
								FROM 	`companycredits`
							)";
		$return = $pdo->query($sql);
		$rowCount = $return->fetchColumn();

		if($rowCount > 0) {
			$pdo = connect_to_db();
			$sql = "SELECT 		`CompanyID`						AS CompanyID,
								(
									SELECT 	`CreditsID`
									FROM	`credits`
									WHERE	`name` = 'Default'
								)								AS CreditsID
					FROM 		`company`
					WHERE		`CompanyID` 
					NOT IN		(
									SELECT 	`CompanyID`
									FROM 	`companycredits`
								)";
			$return = $pdo->query($sql);
			$result = $return->fetchAll(PDO::FETCH_ASSOC);

			$pdo->beginTransaction();
			foreach($result AS $insert){
				$creditsID = $insert['CreditsID'];
				$companyID = $insert['CompanyID'];

				$pdo->exec("INSERT INTO `companycredits`
							SET			`CompanyID` = " . $companyID . ",
										`CreditsID` = " . $creditsID);
			}

			$success = $pdo->commit();
			if(!$success){ // If commit failed we have to retry
				$pdo = null;
				return FALSE;
			}
		}
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// Checks if there are any billing periods that have ended for any company
// If there are any then we:
//		Update the company credits history table with the current values
//		Update the billing date periods
// 		Check if company went over booking credits and alert admin including links to the exact booking history
function updateBillingDatesForCompanies(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		// Check if there is any information to change
		$sql = "SELECT 		COUNT(*)
				FROM 		`company` c
				INNER JOIN 	`companycredits` cc
				ON 			cc.`CompanyID` = c.`CompanyID`
				INNER JOIN 	`credits` cr
				ON			cr.`CreditsID` = cc.`CreditsID`
				WHERE		CURDATE() >= c.`endDate`";
		$return = $pdo->query($sql);
		$rowCount = $return->fetchColumn();

		if($rowCount > 0) {
			$minimumSecondsPerBooking = MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS * 60; // e.g. 15min = 900s
			$aboveThisManySecondsToCount = BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS * 60; // E.g. 5min = 300s
			// There is information to update. Get needed values
			// 			Change this?
			$sql = "SELECT 		c.`CompanyID`				AS TheCompanyID,
								c.`dateTimeCreated`			AS dateTimeCreated,
								c.`startDate`				AS StartDate,
								c.`endDate`					AS EndDate,
								cr.`minuteAmount`			AS CreditsGivenInMinutes,
								cr.`monthlyPrice`			AS MonthlyPrice,
								cr.`overCreditHourPrice`	AS HourPrice,
								cc.`altMinuteAmount`		AS AlternativeAmount,
								(
									SELECT (BIG_SEC_TO_TIME(SUM(
															IF(
																(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																) > :aboveThisManySecondsToCount,
																IF(
																	(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																) > :minimumSecondsPerBooking, 
																	(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																), 
																	:minimumSecondsPerBooking
																),
																0
															)
									)))	AS BookingTimeUsed
									FROM 		`booking` b  
									INNER JOIN 	`company` c 
									ON 			b.`CompanyID` = c.`CompanyID` 
									WHERE 		b.`CompanyID` = TheCompanyID
									AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
									AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
									AND			b.`mergeNumber` = 0
								)							AS BookingTimeThisPeriodFromCompany,
								(
									SELECT (BIG_SEC_TO_TIME(SUM(
															IF(
																(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																) > :aboveThisManySecondsToCount,
																IF(
																	(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																) > :minimumSecondsPerBooking, 
																	(
																	(
																		DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
																		)*86400 
																	+ 
																	(
																		TIME_TO_SEC(b.`actualEndDateTime`) 
																		- 
																		TIME_TO_SEC(b.`startDateTime`)
																		) 
																), 
																	:minimumSecondsPerBooking
																),
																0
															)
									)))	AS BookingTimeUsed
									FROM 		`booking` b  
									INNER JOIN 	`company` c 
									ON 			b.`CompanyID` = c.`CompanyID` 
									WHERE 		b.`CompanyID` = TheCompanyID
									AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
									AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
									AND			b.`mergeNumber` <> 0
								)							AS BookingTimeThisPeriodFromTransfers
					FROM 		`company` c
					INNER JOIN 	`companycredits` cc
					ON 			cc.`CompanyID` = c.`CompanyID`
					INNER JOIN 	`credits` cr
					ON			cr.`CreditsID` = cc.`CreditsID`
					WHERE		CURDATE() >= c.`endDate`";
			$s = $pdo->prepare($sql);
			$s->bindValue(':minimumSecondsPerBooking', $minimumSecondsPerBooking);
			$s->bindValue(':aboveThisManySecondsToCount', $aboveThisManySecondsToCount);
			$s->execute();
			$result = $s->fetchAll(PDO::FETCH_ASSOC);
			$dateTimeNow = getDatetimeNow();
			$displayDateTimeNow = convertDatetimeToFormat($dateTimeNow , 'Y-m-d H:i:s', DATE_DEFAULT_FORMAT_TO_DISPLAY);

			$pdo->beginTransaction();
			foreach($result AS $insert){
				if($insert['AlternativeAmount'] == NULL){
					$creditsGivenInMinutes = $insert['CreditsGivenInMinutes'];
				} else {
					$creditsGivenInMinutes = $insert['AlternativeAmount'];
				}
				$companyID = $insert['TheCompanyID'];
				$companyCreationDate = $insert['dateTimeCreated'];
				$startDate = $insert['StartDate'];
				$endDate = $insert['EndDate'];
				$monthlyPrice = $insert['MonthlyPrice'];
				$hourPrice = $insert['HourPrice'];
				$bookingTimeUsedThisPeriodFromCompany = $insert['BookingTimeThisPeriodFromCompany'];
				$bookingTimeUsedThisPeriodFromCompanyInMinutes = convertTimeToMinutes($bookingTimeUsedThisPeriodFromCompany);
				$bookingTimeUsedThisPeriodFromTransfers = $insert['BookingTimeThisPeriodFromTransfers'];
				$bookingTimeUsedThisPeriodFromTransfersInMinutes = convertTimeToMinutes($bookingTimeUsedThisPeriodFromTransfers);
				$totalBookingTimeUsedThisPeriodInMinutes = $bookingTimeUsedThisPeriodFromCompanyInMinutes + $bookingTimeUsedThisPeriodFromTransfersInMinutes;
				$displayBookingTimeUsedThisPeriodFromCompany = convertMinutesToHoursAndMinutes($bookingTimeUsedThisPeriodFromCompanyInMinutes);
				$displayBookingTimeUsedThisPeriodFromTransfers = convertMinutesToHoursAndMinutes($bookingTimeUsedThisPeriodFromTransfersInMinutes);
				$displayTotalBookingTimeThisPeriod = convertMinutesToHoursAndMinutes($totalBookingTimeUsedThisPeriodInMinutes);
				$displayCompanyCredits = convertMinutesToHoursAndMinutes($creditsGivenInMinutes);

				$setAsBilled = FALSE;
				$setAsOverCreditDueToTransfer = FALSE;

				if($totalBookingTimeUsedThisPeriodInMinutes > $creditsGivenInMinutes){
					// Company went over credit this period
					$companiesOverCredit[] = array(
													'CompanyID' => $companyID,
													'StartDate' => $startDate,
													'EndDate' 	=> $endDate
													);
					if($bookingTimeUsedThisPeriodFromCompanyInMinutes < $creditsGivenInMinutes){
						$setAsOverCreditDueToTransfer = TRUE;
					}
				} else {
					if($monthlyPrice == 0 OR $monthlyPrice == NULL){
						// Company had no fees to pay this month
						$setAsBilled = TRUE;
					}
				}

				$sql = "INSERT INTO `companycreditshistory`
						SET			`CompanyID` = " . $companyID . ",
									`startDate` = '" . $startDate . "',
									`endDate` = '" . $endDate . "',
									`minuteAmount` = " . $creditsGivenInMinutes . ",
									`monthlyPrice` = " . $monthlyPrice . ",
									`overCreditHourPrice` = " . $hourPrice;
				if($setAsBilled){
					$billingDescriptionInformation = 	"This period was Set As Billed automatically at the end of the period due to there being no fees.\n" .
														"At that time the company had produced a total booking time of: " . $displayTotalBookingTimeThisPeriod .
														", with a credit given of: " . $displayCompanyCredits . " and a monthly fee of " . convertToCurrency(0) . ".";							
					$sql .= ", 	`hasBeenBilled` = 1,
								`billingDescription` = '" . $billingDescriptionInformation . "'";
				}

				if($setAsOverCreditDueToTransfer){
					$billingDescriptionInformation = 	"This period is only marked as 'over credits' if you include bookings transferred from another company in this period.\n" .
														"In this period the company had produced a total booking time of: " . $displayTotalBookingTimeThisPeriod .
														", with a credit given of: " . $displayCompanyCredits . ".\n" .
														"The booking time made by the company: " . $displayBookingTimeUsedThisPeriodFromCompany . 
														"\nThe booking time from transfers: " . $displayBookingTimeUsedThisPeriodFromTransfers . 
														"\nThe transferred bookings will have their own period listed with the exact details, and may have already been billed." .
														"\nIt is therefore important to double check that the company isn't being unfairly billed twice, due to a merge.";							
					$sql .= ", 	`billingDescription` = '" . $billingDescriptionInformation . "'";
				}

				$pdo->exec($sql);

				// We want the system to always have a period from x day to x day of next month. 
				// This doesn't work for all dates, due to February, so we manually set an appropriate date
				// And return to the correct pattern again in March
				date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
				$newDate = DateTime::createFromFormat("Y-m-d H:i:s", $companyCreationDate);
				$dayNumberToKeep = $newDate->format("d");

				$newEndDate = addOneMonthToPeriodDate($dayNumberToKeep, $endDate);
				$sql = "UPDATE 	`company`
						SET		`prevStartDate` = `startDate`,
								`startDate` = `endDate`,
								`endDate` = :newEndDate
						WHERE	`companyID` = :companyID";
				$s = $pdo->prepare($sql);
				$s->bindValue(':companyID', $companyID);
				$s->bindValue(':newEndDate', $newEndDate);
				$s->execute();
			}

			$success = $pdo->commit();
			if($success){
				// Check if any of the companies went over credits and send an email to Admin that they did
				if(isSet($companiesOverCredit) AND sizeOf($companiesOverCredit) > 0){
					// There were companies that went over credit
					if(sizeOf($companiesOverCredit) == 1){
						// One company went over
						$emailSubject = "A company went over credit!";
						$companyID = $companiesOverCredit[0]['CompanyID'];
						$startDate = $companiesOverCredit[0]['StartDate'];
						$endDate = $companiesOverCredit[0]['EndDate'];

						//Link example: http://localhost/admin/companies/?companyID=2&BillingStart=2017-05-15&BillingEnd=2017-06-15
						$link = "http://$_SERVER[HTTP_HOST]/admin/companies/?companyID=" . $companyID . 
								"&BillingStart=" . $startDate . "&BillingEnd=" . $endDate;

						$emailMessage = 
						"A company has gone over credit the previous period.\nClick the link below to see the details!\n
						Link: " . $link;
					} else {
						// More than one company went over
						$emailSubject = "Companies went over credit!";

						$emailMessage =
						"More than one company has gone over credit the previous period.\nClick the links below to see the details!\n";

						foreach($companiesOverCredit AS $url){
							$companyID = $url['CompanyID'];
							$startDate = $url['StartDate'];
							$endDate = $url['EndDate'];

							$link = "http://$_SERVER[HTTP_HOST]/admin/companies/?companyID=" . $companyID . 
									"&BillingStart=" . $startDate . "&BillingEnd=" . $endDate;

							$emailMessage .= "Link: " . $link . "\n";
						}
					}

					echo "Email Message being sent out: \n" . $emailMessage;	// TO-DO: Remove before uploading
					echo "<br />";

					// Get admin email(s)
					$sql = "SELECT 		u.`email`		AS Email
							FROM 		`user` u
							INNER JOIN 	`accesslevel` a
							ON			a.`AccessID` = u.`AccessID`
							WHERE		a.`AccessName` = 'Admin'
							AND			u.`sendAdminEmail` = 1";
					$return = $pdo->query($sql);
					$result = $return->fetchAll(PDO::FETCH_ASSOC);

					if(isSet($result)){
						foreach($result AS $Email){
							$email[] = $Email['Email'];
						}
						echo "Will be sent to these email(s): " . implode(", ", $email); // TO-DO: Remove before uploading
						echo "<br />";
					} else {
						echo "No Admins want to receive an Email"; // TO-DO: Remove before uploading
						echo "<br />";
					}

					// Only try to send out email if there are any admins that have set they want them
					if(isSet($email)){
						$mailResult = sendEmail($email, $emailSubject, $emailMessage);

						$email = implode(", ", $email);

						if(!$mailResult){
							// Email failed to be prepared. Store it in database to try again later

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
					}
				}
			} else {
				// If commit failed we have to retry
				$pdo = null;
				return FALSE;
			}
		}

		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo->rollback();
		$pdo = null;
		echo "PDO Exception: " . $e->getMessage(); // TO-DO: Remove before uploading
		echo "<br />";
		return FALSE;
	}
}

// Make a company inactive when the current date is past the date set by admin
	// Note: This doesn't really do anything since inactive companies are not treated any different.
function setCompanyAsInactiveOnSetDate(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "UPDATE 	`company`
				SET 	`isActive` = 0
				WHERE 	DATE(CURRENT_TIMESTAMP) >= `removeAtDate`
				AND 	`isActive` = 1
				AND		`companyID` <> 0";
		$pdo->exec($sql);

		//Close the connection
		$pdo = null;
		return TRUE;
	}
	catch(PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}
}

// Make any user turn into a normal user (access level) when the current date is past the date set by admin
function setUserAccessToNormalOnSetDate(){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		$sql = "UPDATE 	`user`
				SET 	`AccessID` = ( 
										SELECT 	`AccessID`
										FROM 	`accesslevel`
										WHERE 	`AccessName` = 'Normal User'
										LIMIT 	1
									),
						`bookingCode` = NULL,
						`reduceAccessAtDate` = NULL
				WHERE 	DATE(CURRENT_TIMESTAMP) >= `reduceAccessAtDate`
				AND 	`isActive` = 1
				AND		`userID` <> 0";
		$pdo->exec($sql);

		//Close the connection
		$pdo = null;
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
$updatedDefaultSubscription = setDefaultSubscriptionIfCompanyHasNone();
$updatedBillingDates = updateBillingDatesForCompanies();
$updatedCompanyActivity = setCompanyAsInactiveOnSetDate();
$updatedUserAccess = setUserAccessToNormalOnSetDate();
$alertedStaffAboutOrderStatusOfTheDay = alertStaffAboutOrderStatusOfTheDay();

$repetition = 1; // repeats it once, i.e. tries twice.
$sleepTime = 1; // Second(s)

// If we get a FALSE back, the function failed to do its purpose
// Let's wait and try again x times.

if(!$updatedDefaultSubscription){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setDefaultSubscriptionIfCompanyHasNone();
		if($success){
			echo "Successfully Updated Default Subscription";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Update Default Subscription";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Updated Default Subscription";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$updatedBillingDates){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = updateBillingDatesForCompanies();
		if($success){
			echo "Successfully Updated Billing Dates For Companies";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Update Billing Dates For Companies";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Updated Billing Dates For Companies";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$updatedCompanyActivity){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setCompanyAsInactiveOnSetDate();
		if($success){
			echo "Successfully Set Company As Inactive";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Set Company As Inactive";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Set Company As Inactive";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$updatedUserAccess){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = setUserAccessToNormalOnSetDate();
		if($success){
			echo "Successfully Set User Access Level To Normal";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Set User Access Level To Normal";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Set User Access Level To Normal";	// TO-DO: Remove before uploading.
	echo "<br />";
}

if(!$alertedStaffAboutOrderStatusOfTheDay){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = alertStaffAboutOrderStatusOfTheDay();
		if($success){
			echo "Successfully Alerted Staff About Today's Orders";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Alert Staff About Today's Orders";	// TO-DO: Remove before uploading.
	echo "<br />";
} else {
	echo "Successfully Alerted Staff About Today's Orders";	// TO-DO: Remove before uploading.
	echo "<br />";
}

// Close database connection
$pdo = null;

// The actual actions taken // END //
?>