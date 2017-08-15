<!-- This is the HTML form used for ADMINS to add a feedback message to users when cancelling their bookings -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>			
		<title>Cancel Message</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Add a reason for cancelling:</legend>
			<div class="left">
				<?php if(isSet($_SESSION['confirmReasonError'])) : ?>
					<span style="white-space: pre-wrap;" class="warning"><?php htmlout($_SESSION['confirmReasonError']); ?></span>
					<?php unset($_SESSION['confirmReasonError']); ?>
				<?php endif; ?>
			</div>

			<div class="left">
				<form action="" method="post">
					<span>This will be added to the email sent out to the user the meeting was registered to.</span>
					<label for="bookingCode">Added message: </label>
					<?php if(isSet($cancelMessage)) : ?>
						<textarea rows="4" cols="50" name="cancelMessage" placeholder="Default: No reason given."><?php htmlout($cancelMessage); ?></textarea>
					<?php else : ?>
						<textarea rows="4" cols="50" name="cancelMessage" placeholder="Default: No reason given."></textarea>
					<?php endif; ?>
					<input type="submit" name="action" value="Confirm Reason">
				</form>
			</div>
		</fieldset>
	</body>
</html>