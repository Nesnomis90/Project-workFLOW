<?php 

// Define some cookies we use to identify if the website is accessed locally by a meeting room panel
// Cookie name
	//TO-DO: Change cookie names after uploading
define('MEETINGROOM_NAME', 'This is a temporary cookie name used to hold the meeting room name'); 
define('MEETINGROOM_ID', 'This is a temporary cookie name used to hold the meeting room IDCode'); 

// Cookie setup
function setNewMeetingRoomCookies($meetingRoomName, $idCode){
	// Set to 'never expire' i.e. last until 19th January 2038
	setcookie(MEETINGROOM_NAME, $meetingRoomName, 2147483647, '/');
	setcookie(MEETINGROOM_ID, $idCode, 2147483647, '/');
}
?>