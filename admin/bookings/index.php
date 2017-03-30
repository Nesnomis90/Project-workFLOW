<?php 
// This is the index file for the BOOKINGS folder

// Display booking information list
// TO-DO: SORT THE SELECT STATEMENT SO IT HAS THE LATEST BOOKINGS FIRST.. ORDER BY BOOKING ID ETC
// TO-DO: THIS NEEDS THE ADD, ADDFORM, EDIT, EDITFORM AND DELETE CODE SNIPPETS
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();
	$sql = "SELECT 	b.`bookingID`,
					m.`name` AS BookedRoomName, 
					DATE_FORMAT(b.startDateTime, '%d %b %Y %T') AS StartTime, 
					DATE_FORMAT(b.endDateTime, '%d %b %Y %T') AS EndTime, 
					b.displayName AS BookedBy, 
					u.firstName, 
					u.lastName, 
					u.email, 
					GROUP_CONCAT(c.`name` separator ', ') AS WorksForCompany, 
					b.description AS BookingDescription, 
					DATE_FORMAT(b.dateTimeCreated, '%d %b %Y %T') AS BookingWasCreatedOn, 
					DATE_FORMAT(b.actualEndDateTime, '%d %b %Y %T') AS BookingWasCompletedOn, 
					DATE_FORMAT(b.dateTimeCancelled, '%d %b %Y %T') AS BookingWasCancelledOn 
					FROM `booking` b 
					LEFT JOIN `meetingroom` m 
					ON b.meetingRoomID = m.meetingRoomID 
					LEFT JOIN `user` u 
					ON u.userID = b.userID 
					LEFT JOIN `employee` e 
					ON e.UserID = u.userID 
					LEFT JOIN `company` c 
					ON c.CompanyID = e.CompanyID 
					GROUP BY b.bookingID";
	$result = $pdo->query($sql);
	$rowNum = $result->rowCount();

	//Close the connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching booking information from the database: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

foreach ($result as $row)
{
	$bookings[] = array('id' => $row['bookingID'], 
						'BookedRoomName' => $row['BookedRoomName'],
						'StartTime' => $row['StartTime'],
						'EndTime' => $row['EndTime'],
						'BookedBy' => $row['BookedBy'],
						'firstName' => $row['firstName'],
						'lastName' => $row['lastName'],
						'email' => $row['email'],
						'WorksForCompany' => $row['WorksForCompany'],
						'BookingDescription' => $row['BookingDescription'],
						'BookingWasCreatedOn' => $row['BookingWasCreatedOn'],
						'BookingWasCompletedOn' => $row['BookingWasCompletedOn'],
						'BookingWasCancelledOn' => $row['BookingWasCancelledOn'],					
					);
}

// Create the booking information table in HTML
include_once 'bookings.html.php';

?>