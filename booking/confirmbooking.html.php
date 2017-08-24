<!-- This is the HTML form used for all users in BOOKING to confirm that they want to create a meeting if it makes the company go over their free booking time -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Booking</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Booking Information:</legend>
			<div class="left">
				<span style="white-space: pre-wrap;" class="warning">TO-DO: This booking will put your company at xhxm above credits this period and will result in a cost of xxkr/h </span>
				<label>Meeting Room: </label>
				<span></span>
				<label>Start Date: </label>
				<span></span>
				<label>End Date: </label>
				<span></span>
				<label>Booked For Company: </label>
				<span></span>
				<form action="" method="post">
					<input type="submit" name="action" value="Confirm Booking">
					<input type="submit" name="add" value="Cancel">
				</form>
			</div>
		</fieldset>
	</body>
</html>