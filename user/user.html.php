<!-- This is the HTML form used to display user information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>	
		<style>
			label {
				width: 210px;
			}
		</style>
		<?php if(isset($_SESSION['loggedIn'])) : ?>
			<title>Your User Information</title>
		<?php else : ?>
			<title>User Information</title>
		<?php endif; ?>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<?php if(isset($_SESSION['loggedIn'])) : ?>
			<h1>Your User Information</h1>
		<?php else : ?>
			<h1>User Information</h1>
		<?php endif; ?>
		
		<div class="left">
			<?php if(isset($_SESSION['normalUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['normalUserFeedback']); ?></b></span>
				<?php unset($_SESSION['normalUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<?php if(isset($_SESSION['loggedIn']) AND isset($_SESSION['LoggedInUserID'])) : ?>
			<div class="left">
				<fieldset><legend>Your User Information</legend>
					<form action="" method="post">
						<div>
							<label>First Name: </label>
							<span><?php htmlout($originalFirstName); ?></span>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Set New First Name: </label>
								<input type="text" name="firstName" value="<?php htmlout($firstName); ?>">
							</div>
						<?php endif; ?>

						<div>
							<label>Last Name: </label>
							<span><?php htmlout($originalLastName); ?></span>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Set New Last Name: </label>
								<input type="text" name="lastName" value="<?php htmlout($lastName); ?>">
							</div>
						<?php endif; ?>

						<div>
							<label>Email: </label>
							<span><?php htmlout($originalEmail); ?></span>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Set New Email: </label>
								<input type="text" name="email" value="<?php htmlout($email); ?>">
							</div>
						<?php endif; ?>

						<div>
							<label>Default Display Name: </label>
							<span style="white-space: pre-wrap;"><?php htmlout($originalDisplayName); ?></span>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Set New Display Name: </label>
								<input type="text" name="displayName" value="<?php htmlout($displayName); ?>">
							</div>
						<?php endif; ?>

						<div>
							<label>Default Booking Description: </label>
							<span style="white-space: pre-wrap;"><?php htmlout($originalBookingDescription); ?></span>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Set New Booking Description: </label>
								<textarea rows="4" cols="50" name="bookingDescription" style="white-space: pre-wrap;"><?php htmlout($bookingDescription); ?></textarea>
							</div>
						<?php endif; ?>
						
						<div>
							<label>Email Alert Status: </label>
							<?php if($originalSendEmail == 1) : ?>
								<span><b>Send Me Email Alerts</b></span>
							<?php elseif($originalSendEmail == 0) : ?>
								<span><b>Don't Send Me Email Alerts</b></span>
							<?php endif; ?>
						</div>
						<?php if(isset($editMode)) : ?>
							<div>
								<label>Change Email Alert Status: </label>
								<select name="sendEmail">
									<?php if($sendEmail == 1) : ?>
										<option selected="selected" value="1"><b>Send Me Email Alerts</b></option>
										<option value="0"><b>Don't Send Me Email Alerts</b></option>
									<?php elseif($sendEmail == 0) : ?>
										<option value="1"><b>Send Me Email Alerts</b></option>
										<option selected="selected" value="0"><b>Don't Send Me Email Alerts</b></option>										
									<?php endif; ?>
								</select>
							</div>
						<?php endif; ?>						

						<?php if(isset($userCanHaveABookingCode)) : ?>
							<div>
								<label>Booking Code: </label>
								<span><?php htmlout($bookingCodeStatus); ?></span>
								<?php if(isset($userHasABookingCode) AND !isset($showBookingCode)) : ?>
									<label>Reveal Code: </label><input type="submit" name="action" value="Show Code">
								<?php elseif(isset($userHasABookingCode) AND isset($showBookingCode) AND $showBookingCode) : ?>
									<label>Reveal Code: </label><span><b><?php htmlout($showBookingCode); ?></b></span>
								<?php elseif(isset($userHasABookingCode) AND isset($showBookingCode) AND $showBookingCode == FALSE) : ?>
									<label>Reveal Code: </label><span><b>Could not retrieve code.</b></span>
								<?php endif; ?>
							</div>
							
							<?php if(isset($editMode)) : ?>
								<div>
									<?php if(!isset($userHasABookingCode)) : ?>
										<label>Set Your Booking Code: </label>
									<?php else : ?>
										<label>Set A New Booking Code: </label>
									<?php endif; ?>
									<?php if(isset($canSetNewCode)) : ?>
										<input type="number" name="bookingCode" min="1" max="<?php htmlout((10 ** BOOKING_CODE_LENGTH)-1); ?>"
										placeholder="<?php htmlout(BOOKING_CODE_LENGTH . " digits"); ?>" value="">
									<?php else : ?>
										<span><b>You can not set a new booking code before <?php htmlout($displayNextBookingCodeChange); ?></b></span>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						
						<div class="left">
							<?php if(isset($editMode)) : ?>
								<label>Set New Password: </label><input type="password" name="password1" value="">
								<label>Repeat New Password: </label><input type="password" name="password2" value="">
								<label>Confirm With Your Password: </label><input type="password" name="confirmPassword" value=""><span style="color: red;">* Required for any change</span>
								<div class="left">
									<input type="submit" name="action" value="Confirm Change">
									<input type="submit" name="action" value="Reset">
									<input type="submit" name="action" value="Cancel">
								</div>
							<?php else : ?>
								<input type="submit" name="action" value="Change Information">
							<?php endif; ?>
						</div>
					</form>
				</fieldset>
			</div>
		<?php endif; ?>
	</body>
</html>