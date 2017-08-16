<?php
session_start();
// Make sure we don't have any admin sessions still around when not browsing admin pages.
unsetSessionsFromAdminUsers(); // TO-DO: Remove if stuff gets broken.

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
		unset($_SESSION['LoggedInUserIsOwnerInTheseCompanies']);
	// Refresh page without get parameters
	$location = getLocationWeCameFrom();
	header($location);
	exit();
}

// Set the correct query links in href
$loginForNav = "?loginForNav";
$logoutForNav = "?logoutForNav";
if(isSet($_GET['meetingroom'])){
	$TheMeetingRoomID = $_GET['meetingroom'];
	$loginForNav .= "&meetingroom=" . $TheMeetingRoomID;
	$logoutForNav .= "&meetingroom=" . $TheMeetingRoomID;
		if(isSet($_GET['name'])){
			$name = $_GET['name'];
			$loginForNav .= "&name=" . $name;
			$logoutForNav .= "&name=" . $name;
		}	
}
?>
<div class="topnav">
	<ul>
		<?php if(!isSet($_SESSION["DefaultMeetingRoomInfo"])) : ?>
			<li><a href="/">Home</a></li>
		<?php else : ?>
			<li><a href="/booking/?meetingroom=<?php htmlout($_SESSION["DefaultMeetingRoomInfo"]["TheMeetingRoomID"]); ?>">Home</a></li>
		<?php endif; ?>
		
		<li><a href="/meetingroom">Meeting Rooms</a></li>
		<li><a href="/booking">Booked Meetings</a></li>
		
		<li><b id="Clock"></b></li>
		
		<?php if(!isSet($_SESSION['loggedIn']) AND !isSet($_SESSION["DefaultMeetingRoomInfo"])) : ?>
			<li style="float:right;"><a href="/user/?register">Register</a></li>
			<li style="float:right;"><a href="<?php htmlout($loginForNav); ?>">Log In</a></li>
		<?php elseif(isSet($_SESSION['loggedIn'])) : ?>
			<li style="float:right;"><a href="<?php htmlout($logoutForNav); ?>">Log Out</a></li>
			<li style="float:right;"><a href="/user">My Account</a></li>
			<li style="float:right;"><a href="/company">My Company</a></li>
		<?php endif; ?>
	</ul>
</div>