<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

function getLocationWeCameFrom(){
	$pathWeCameFrom = $_SERVER['PHP_SELF'];
	$pathWithoutPHPFile = substr($pathWeCameFrom, 0, strrpos($pathWeCameFrom,'/'));
	$location = "Location: " . $pathWithoutPHPFile;

	if(isSet($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location .= "?meetingroom=" . $TheMeetingRoomID;
		if(isSet($_GET['name'])){
			$name = $_GET['name'];
			$location .= "&name=" . $name;
		}
	}

	if(isSet($_GET['ID'])){
		$CompanyID = $_GET['ID'];
		$location .= "?ID=" . $CompanyID;
		if(isSet($_GET['employees'])){
			$location .= "&employees";
		}
	}

	return $location;
}

if(isSet($_GET['loginForNav'])){
	$loggedIn = makeUserLogIn();

	// Refresh page without get parameters
	$location = getLocationWeCameFrom();
	header($location);
	exit();
}

if(isSet($_GET['logoutForNav'])){
	// Same stuff we do on logout in access
	unset($_SESSION['loggedIn']);
	unset($_SESSION['email']);
	unset($_SESSION['password']);
	unset($_SESSION['LoggedInUserID']);
	unset($_SESSION['LoggedInUserName']);
	// Refresh page without get parameters
	$location = getLocationWeCameFrom();

	header($location);
	exit();
}
?>