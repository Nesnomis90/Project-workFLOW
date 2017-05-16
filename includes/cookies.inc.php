<?php 

// Define some cookies we use to identify if the website is accessed locally by a meeting room panel
// Cookie name
	//TO-DO: Change cookie names after uploading
define('MEETINGROOM_NAME', 'This is a temporary cookie name used to hold the meeting room name'); 
define('MEETINGROOM_ID', 'This is a temporary cookie name used to hold the meeting room IDCode'); 

// Cookie setup
function setNewMeetingRoomCookies($meetingRoomName, $idCode){
	$hashedIdCode = hashCookies($idCode);
	// Set to 'never expire' i.e. last until 19th January 2038
	setcookie(MEETINGROOM_NAME, $meetingRoomName, 2147483647, '/');
	setcookie(MEETINGROOM_ID, $hashedIdCode, 2147483647, '/');
}

function deleteMeetingRoomCookies(){
	// To delete a cookie you have to make it expire by setting a date in the past
	// To-DO: Add path if not working?
	setcookie(MEETINGROOM_NAME, '', time() - 3600);
	setcookie(MEETINGROOM_ID, '', time() - 3600;	
	// Just in case?
	unset($_COOKIE[MEETINGROOM_NAME]);
	unset($_COOKIE[MEETINGROOM_ID]);
}
?>