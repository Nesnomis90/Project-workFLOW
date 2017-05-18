<!-- This is the HTML form used to display booking information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Confirm Booking</title>
	</head>
	<body>
	<?php if(isset($_SESSION['DefaultMeetingRoomInfo'])) : ?>
		<h1>Enter your booking code</h1>
	<?php elseif(isset($_SESSION['LoggedInUserID'])) : ?>
		<h1>Confirm your booking details</h1>
	<?php else : ?>
		<h1>You need to be logged in.</h1>
	<?php endif; ?>
	
	
	<?php //TO-DO: Fix -> include '../logout.inc.html.php'; ?>
	</body>
</html>