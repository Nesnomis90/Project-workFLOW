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

		<fieldset><legend>Warning!</legend>
			<div class="left">
				<span style="white-space: pre-wrap;"><?php htmlout("This booking, if completed, will put the company $companyName at a total of $timeOverCredits above credits this period." .
					"\nThe 'over credits'-fee is $companyHourPriceOverCredits" .
					"\nDo you still want to create this booking?"); ?></span>
			</div>
			<div class="left">
				<form action="" method="post">
					<input type="submit" name="confirm" value="Yes, Create The Booking">
					<input type="submit" name="confirm" value="No, Cancel The Booking">
				</form>
			</div>
		</fieldset>
	</body>
</html>