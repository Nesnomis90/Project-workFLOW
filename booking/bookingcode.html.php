<!-- This is the HTML form used for identifying user with their BOOKING CODE -->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<link rel="stylesheet" type="text/css" href="/CSS/jquery.keypad.css">
		<script src="/scripts/myFunctions.js"></script>	
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="/scripts/jquery.plugin.min.js"></script>
		<script src="/scripts/jquery.keypad.js"></script>
		<script>
			$(function () {
				$('#bookingCode').keypad();
			});
		</script>
		<title>Confirm Booking Code</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<?php $activateRemoveTimeout = !updateBookingCodeGuesses(); ?>
		<?php if($activateRemoveTimeout){ updateAdminBookingCodeGuesses(); } ?>
		<fieldset><legend>Confirm your identity with your Booking Code:</legend>
			<div class="left">
				<?php if(isSet($_SESSION['confirmBookingCodeError'])) : ?>
					<span style="white-space: pre-wrap;" class="warning"><?php htmlout($_SESSION['confirmBookingCodeError']); ?></span>
					<?php unset($_SESSION['confirmBookingCodeError']); ?>
				<?php endif; ?>
			</div>

			<div class="left">
				<form action="" method="post">
					<?php if(!$activateRemoveTimeout OR ($activateRemoveTimeout AND !isSet($_SESSION['adminBookingCodeGuesses'])) OR ($activateRemoveTimeout AND isSet($_SESSION['adminBookingCodeGuesses']) AND sizeOf($_SESSION['adminBookingCodeGuesses']) != MAXIMUM_ADMIN_BOOKING_CODE_GUESSES)) : ?>
						<label for="bookingCode">Submit your booking code: </label>
						<input type="password" id="bookingCode" name="bookingCode" maxlength="<?php htmlout(BOOKING_CODE_LENGTH); ?>"
						placeholder="<?php htmlout(BOOKING_CODE_LENGTH); ?> digits"
						value="">
					<?php endif; ?>
					<?php if(!$activateRemoveTimeout) : ?>
						<input type="submit" name="action" value="Confirm Code">
					<?php endif; ?>
					<?php if(($activateRemoveTimeout AND !isSet($_SESSION['adminBookingCodeGuesses'])) OR ($activateRemoveTimeout AND isSet($_SESSION['adminBookingCodeGuesses']) AND sizeOf($_SESSION['adminBookingCodeGuesses']) != MAXIMUM_ADMIN_BOOKING_CODE_GUESSES)) : ?>
						<input type="submit" name="action" value="Remove Timeout"><span>* Requires Admin Access</span>
					<?php endif; ?>
				</form>
			</div>
			<div class="left">
				<form action="" method="Post">
					<input type="submit" name="action" value="Go Back">
					<?php if(isSet($_SESSION['bookingCodeGuesses'])) : ?>
						<input type="submit" name="bookingCode" value="Refresh">
					<?php endif; ?>
				</form>
			</div>
		</fieldset>
	</body>
</html>