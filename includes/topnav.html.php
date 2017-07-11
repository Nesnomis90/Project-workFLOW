<?php
session_start();
function getLocationWeCameFrom(){
	
	$pathWeCameFrom = $_SERVER['PHP_SELF'];
	$pathWithoutPHPFile = substr($pathWeCameFrom, 0, strrpos($pathWeCameFrom,'/'));	
	$location = "Location: " . $pathWithoutPHPFile;
	
	if(isset($_GET['meetingroom'])){
		$TheMeetingRoomID = $_GET['meetingroom'];
		$location .= "?meetingroom=" . $TheMeetingRoomID;
		if(isset($_GET['name'])){
			$name = $_GET['name'];
			$location .= "&name=" . $name;	
		}		
	}

	return $location;
}

if(isset($_GET['loginForNav'])){
	
	$loggedIn = makeUserLogIn();
	
	// Refresh page without get parameters
	$location = getLocationWeCameFrom();
	header($location);
	exit();	
}

if(isset($_GET['logoutForNav'])){
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
if(isset($_GET['meetingroom'])){
	$TheMeetingRoomID = $_GET['meetingroom'];
	$loginForNav .= "&meetingroom=" . $TheMeetingRoomID;
	$logoutForNav .= "&meetingroom=" . $TheMeetingRoomID;
		if(isset($_GET['name'])){
			$name = $_GET['name'];
			$loginForNav .= "&name=" . $name;
			$logoutForNav .= "&name=" . $name;
		}	
}
?>
<?php if(!isset($_SESSION['DefaultMeetingRoomInfo'])) : ?>
	<div class="topnav">
		<ul>
			<li><a href="#home">Home</a></li>
			<li><a href="/meetingroom">Room Status</a></li>
			<li><a href="/booking">Book Meeting</a></li>
			<?php if(!isset($_SESSION['loggedIn']) AND !isset($_SESSION["DefaultMeetingRoomInfo"])) : ?>
				<li style="float:right"><a href="/user/?register">Register</a></li>
				<li style="float:right"><a href="<?php htmlout($loginForNav); ?>">Log In</a></li>
			<?php else : ?>
				<li style="float:right"><a href="<?php htmlout($logoutForNav); ?>">Log Out</a></li>
			<?php endif; ?>
		</ul>
	</div>
<?php endif; ?>