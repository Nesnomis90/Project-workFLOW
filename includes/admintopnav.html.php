<?php
require_once 'adminnavcheck.inc.php';

/*	<script src="/scripts/myFunctions.js"></script>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>
*/
?>

<div class="topnav">
	<ul>
		<li><a href="/">Home</a></li>
		<li><a href="/admin/bookings">Bookings</a></li>
		<li><a href="/admin/companies">Companies</a></li>
		<li><a href="/admin/companycredits">Company Credits</a></li>
		<li><a href="/admin/credits">Credits</a></li>
		<li><a href="/admin/employees">Employees</a></li>
		<li><a href="/admin/equipment">Equipment</a></li>
		<li><a href="/admin/events">Events</a></li>
		<li><a href="/admin/logevents">Log Events</a></li>
		<li><a href="/admin/meetingrooms">Meeting Rooms</a></li>
		<li><a href="/admin/roomequipment">Room Equipment</a></li>
		<li><a href="/admin/users">Users</a></li>

		<li><b id="Clock"></b></li>
		<li style="float:right;"><a href="?logoutForNav">Log Out</a></li>
	</ul>
</div>