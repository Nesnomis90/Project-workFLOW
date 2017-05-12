<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// Cron does 1 run per minute (fastest)
/* TO-DO:
	Reduce user access on reduceAtDate
		Make access into Normal User
		Remove Booking Code
	Send Email to users that their meeting is starting in x minutes
*/

// TO-DO: This is all untested

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

// Delete users that have not been activated within 8 hours of being created
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
	$pdo = connect_to_db();
	$sql = "DELETE 	
			FROM 	`user`
			WHERE 	DATE_ADD(`create_time`, INTERVAL 8 HOUR) < CURRENT_TIMESTAMP
			AND 	`isActive` = 0
			AND		`userID` <> 0";		
	$pdo->exec($sql);
	
	//Close the connection
	$pdo = null;
}
catch(PDOException $e)
{
	$error = 'Error deleting unactivated user: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

// Make a company inactive when the current date is past the date set by admin
// TO-DO: only needs to run once per day, if we make another cron for it
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
}
catch(PDOException $e)
{
	$error = 'Error deleting company with a set remove date: ' . $e->getMessage();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}

?>