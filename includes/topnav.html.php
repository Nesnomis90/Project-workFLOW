<?php session_start(); ?>
<?php
if(isset($_GET['logoutForNav'])){
	// Same stuff we do on logout in access
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		unset($_SESSION['LoggedInUserID']);
		unset($_SESSION['LoggedInUserName']);
		unset($_SESSION['LoggedInUserIsOwnerInTheseCompanies']);
	// Refresh page without get parameters
	$pathWeCameFrom = $_SERVER['PHP_SELF'];
	$pathWithoutPHPFile = substr($pathWeCameFrom, 0, strrpos($pathWeCameFrom,'/'));	
	$location = "Location: " . $pathWithoutPHPFile;
	header($location);
	exit();
}
?>
<?php if(!isset($_SESSION['DefaultMeetingRoomInfo'])) : ?>
	<div class="topnav">
		<ul>
			<li><a href="#home">Home</a></li>
			<li><a href="/meetingroom">Room Status</a></li>
			<li><a href="/booking">Book Meeting</a></li>
			<?php if(!isset($_SESSION['loggedIn'])) : ?>
				<li style="float:right"><a href="/includes/register.html.php">Register</a></li>
				<li style="float:right"><a href="/includes/login.html.php">Log In</a></li>
			<?php else : ?>
				<li style="float:right"><a href="?logoutForNav">Log Out</a></li>
			<?php endif; ?>
		</ul>
	</div>
<?php endif; ?>