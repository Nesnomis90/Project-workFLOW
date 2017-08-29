<!-- This is the HTML form used by ADMIN in BOOKINGS to confirm that they want to create a meeting if it makes the company go over their free booking time -->
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
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Warning!</legend>
			<div class="left">

				<?php if($newPeriod) : ?>
					<span style="white-space: pre-wrap;"><?php htmlout("This booking, if completed, will put the company $companyName at a total of $totalTimeBookedInTime booked for the period starting at $periodStartDate and ending at $periodEndDate." .
						"\nThis puts that company $timeOverCredits* above credits for that period." .
						"\nWith an 'over credits'-fee of $companyHourPriceOverCredits*" .
						"\nDo you still want to create this booking?" . 
						"\n\n*This is assuming the company keeps the same credits given and 'over fee'-cost as their current period." . 
						"\nTherefore these details may not accurately reflect the correct amount they will be charged."); ?></span>				
				<?php else : ?>
					<span style="white-space: pre-wrap;"><?php htmlout("This booking, if completed, will put the company $companyName at a total of $timeOverCredits above credits for the current period." .
						"\nThe 'over credits'-fee is $companyHourPriceOverCredits" .
						"\nDo you still want to create this booking?"); ?></span>
				<?php endif; ?>
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