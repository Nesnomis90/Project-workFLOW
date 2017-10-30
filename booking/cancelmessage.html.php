<!-- This is the HTML form used for users in BOOKING to confirm if they want to cancel their/someones meeting/order -->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Cancel</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Are you sure you want to cancel?</legend>
			<div class="left">
				<?php if(isSet($_SESSION['confirmReasonError'])) : ?>
					<span style="white-space: pre-wrap;" class="warning"><?php htmlout($_SESSION['confirmReasonError']); ?></span>
					<?php unset($_SESSION['confirmReasonError']); ?>
				<?php endif; ?>
			</div>

			<div class="left">
				<form action="" method="post">
					<div class="left">
						<?php if($cancelledBy == "Admin") : ?>
							<span style="white-space: pre-wrap;"><?php htmlout(	"Add a reason in the textfield below if you want to inform the user why it happened" . 
																				"\nThis message will be sent by email alerting them that it was cancelled."
																				"\nThe message will also be accessable later in the history and can be accessed by the user, company owners and admins."); ?></span>
						<?php elseif($cancelledBy == "Owner") : ?>
							<span style="white-space: pre-wrap;"><?php htmlout(	"Add a reason in the textfield below if you want to inform your employee why it happened" . 
																				"\nThis message will be sent by email alerting them that it was cancelled."
																				"\nThe message will also be accessable later in the history and can be accessed by the user, other owners in your company and admins."); ?></span>					
						<?php else : ?>
							<span style="white-space: pre-wrap;"><?php htmlout(	"Please fill in a description in the textbox letting us know why you decided to cancel this." .
																				"\nYou can also leave it blank if there's no reason to add a message."); ?></span>					
						<?php endif; ?>
						<label for="cancelMessage">Added message: </label>
						<?php if(isSet($cancelMessage)) : ?>
							<textarea rows="4" cols="50" name="cancelMessage" placeholder="Default: No reason given."><?php htmlout($cancelMessage); ?></textarea>
						<?php else : ?>
							<textarea rows="4" cols="50" name="cancelMessage" placeholder="Default: No reason given."></textarea>
						<?php endif; ?>
					</div>
					<div class="left">
						<input type="submit" name="confirmCancel" value="Confirm Reason">
						<input type="submit" name="confirmCancel" value="Abort Cancel">
						<input type="hidden" name="cancelledBy" value="<?php htmlout($cancelledBy); ?>">
					</div>
				</form>
			</div>
		</fieldset>
	</body>
</html>