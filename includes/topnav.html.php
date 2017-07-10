<div class="topnav">
	<ul>
		<li><a href="#home">Home</a></li>
		<li><a href="/meetingroom">Room Status</a></li>
		<li><a href="/booking">Book Meeting</a></li>
		<?php if(!isset($_SESSION['loggedIn'])) : ?>
			<li style="float:right"><a href="/includes/register.html.php">Register</a></li>
			<li style="float:right"><a href="/includes/login.html.php">Log In</a></li>
		<?php else : ?>
			<li style="float:right"><a href="#logout">Log Out</a></li>
		<?php endif; ?>
	</ul>
</div>