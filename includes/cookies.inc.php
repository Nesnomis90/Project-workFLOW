<?php

// Get variables
require_once 'variables.inc.php';

// Cookie setup
function setNewMeetingRoomCookies($meetingRoomName, $idCode){
	$hashedIdCode = hashMeetingRoomIDCode($idCode);
	// Set to 'never expire' i.e. last until 19th January 2038
	setcookie(MEETINGROOM_NAME, $meetingRoomName, 2147483647, '/');
	setcookie(MEETINGROOM_IDCODE, $hashedIdCode, 2147483647, '/');
}

// Cookie removal
function deleteMeetingRoomCookies(){
	// To delete a cookie you have to make it expire by setting a date in the past
	setcookie(MEETINGROOM_NAME, "", time() - 3600, '/');
	setcookie(MEETINGROOM_IDCODE, "", time() - 3600, '/');	
	// Just in case?
	unset($_COOKIE[MEETINGROOM_NAME]);
	unset($_COOKIE[MEETINGROOM_IDCODE]);
}

// Function used in meetingroom and booking pages for all users to check if we're on a local device
function checkIfLocalDevice(){
	if(isSet($_COOKIE[MEETINGROOM_NAME]) AND isSet($_COOKIE[MEETINGROOM_IDCODE]))
	{
		// There are local meeting room identifiers set in cookies. Check if they are valid
		$meetingRoomName = $_COOKIE[MEETINGROOM_NAME];
		$meetingRoomIDCode = $_COOKIE[MEETINGROOM_IDCODE];

		if(!isSet($_SESSION['OriginalCookieMeetingRoomName']) AND !isSet($_SESSION['OriginalCookieMeetingRoomIDCode'])){
			$validMeetingRoom = databaseContainsMeetingRoomWithIDCode($meetingRoomName, $meetingRoomIDCode);
			if($validMeetingRoom === TRUE){
				// Cookies are correctly identifying a meeting room
				// Hopefully this means it's a local device we set up and not someone malicious
				$_SESSION['OriginalCookieMeetingRoomName'] = $meetingRoomName;
				$_SESSION['OriginalCookieMeetingRoomIDCode'] = $meetingRoomIDCode;

				if(!isSet($_SESSION['DefaultMeetingRoomInfo'])){
					try
					{
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
						
						$pdo = connect_to_db();
						$sql = "SELECT 	`meetingRoomID`							AS TheMeetingRoomID, 
										`name` 									AS TheMeetingRoomName,
										`capacity`								AS TheMeetingRoomCapacity,
										`description`							AS TheMeetingRoomDescription,
										`location`								AS TheMeetingRoomLocation
								FROM	`meetingroom`
								WHERE 	`name` = :meetingRoomName
								LIMIT 	1";
						$s = $pdo->prepare($sql);
						$s->bindValue(':meetingRoomName', $meetingRoomName);
						$s->execute();
						$row = $s->fetch(PDO::FETCH_ASSOC);

						$_SESSION['DefaultMeetingRoomInfo'] = $row;

						//Close the connection
						$pdo = null;
					}
					catch(PDOException $e)
					{
						$error = 'Error getting meeting room info: ' . $e->getMessage();
						include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
						$pdo = null;
						exit();
					}
				}
			} elseif($validMeetingRoom === FALSE){
				// The cookies set does not match a meeting room i.e. someone manually changed a cookie
				resetLocalDevice();
			}
		}
		if(	$_COOKIE[MEETINGROOM_NAME] != $_SESSION['OriginalCookieMeetingRoomName'] OR 
			$_COOKIE[MEETINGROOM_IDCODE] != $_SESSION['OriginalCookieMeetingRoomIDCode']){
				// Cookies have changed
				unset($_SESSION['OriginalCookieMeetingRoomName']);
				unset($_SESSION['OriginalCookieMeetingRoomIDCode']);
				unset($_SESSION['DefaultMeetingRoomInfo']);
			}
	} else {
		unset($_SESSION['OriginalCookieMeetingRoomName']);
		unset($_SESSION['OriginalCookieMeetingRoomIDCode']);
		unset($_SESSION['DefaultMeetingRoomInfo']);
	}
}

// Function to remove locally set device information
// This occurs when cookies do not match session info or if someone logs in
function resetLocalDevice(){
	deleteMeetingRoomCookies();
	unset($_SESSION['DefaultMeetingRoomInfo']);
	unset($_SESSION['OriginalCookieMeetingRoomName']);
	unset($_SESSION['OriginalCookieMeetingRoomIDCode']);
	// TO-DO: Do anything more here to punish cookie manipulation?
}
?>