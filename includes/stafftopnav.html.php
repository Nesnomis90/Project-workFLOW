<?php
require_once 'navcheck.inc.php';
// Make sure we don't have any admin sessions still around when not browsing admin pages.

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
			<li style="float:right;"><a href="/orders">My Company</a></li>
		<?php endif; ?>
	</ul>
</div>