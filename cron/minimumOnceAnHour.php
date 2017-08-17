<?php
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';
// PHP code that we will set to be run at a certain interval, with CRON, to interact with our database
// This file is set to run minimum once an hour

// Delete users that have not been activated within 8 hours of being created
function deleteNotActivatedUsersIfTakingTooLong(){
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
$deletedNotActivatedUsers = deleteNotActivatedUsersIfTakingTooLong();

$repetition = 3;
$sleepTime = 1; // Second(s)

// If we get a FALSE back, the function failed to do its purpose
// Let's wait and try again x times.

if(!$deletedNotActivatedUsers){
	for($i = 0; $i < $repetition; $i++){
		sleep($sleepTime);
		$success = deleteNotActivatedUsersIfTakingTooLong();
		if($success){
			echo "Successfully Deleted Non-Activated Users";	// TO-DO: Remove before uploading.
			echo "<br />";
			break;
		}
	}
	unset($success);
	echo "Failed To Delete Non-Activated Users";	// TO-DO: Remove before uploading.
	echo "<br />";	
} else {
	echo "Successfully Deleted Non-Activated Users";	// TO-DO: Remove before uploading.
	echo "<br />";
}

// Close database connection
$pdo = null;
// The actual actions taken // END //
?>