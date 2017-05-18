<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once an hour

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
?>