<!-- This is the HTML form used to display user information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>	

		<?php if(isSet($editMode)) : ?>
			<style>
				label {
					width: 260px;
				}
			</style>
		<?php else : ?>
			<style>
				label {
					width: 220px;
				}
			</style>			
		<?php endif; ?>

		<?php if(isSet($_SESSION['loggedIn'])) : ?>
			<title>Your User Information</title>
		<?php else : ?>
			<title>User Information</title>
		<?php endif; ?>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<div class="left">
			<?php if(isSet($_SESSION['normalUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['normalUserFeedback']); ?></b></span>
				<?php unset($_SESSION['normalUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<?php if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND isSet($_SESSION['LoggedInUserID']) AND !empty($_SESSION['LoggedInUserID'])) : ?>
			<div class="left">
				<fieldset>
					<form action="" method="post">
						<fieldset class="left"><legend>User Information:</legend>
							<div>
								<label>First Name: </label>
								<span><?php htmlout($originalFirstName); ?></span>
							</div>
							<?php if(isSet($editMode)) : ?>
								<div>
									<label>Change First Name: </label>
									<input type="text" name="firstName" value="<?php htmlout($firstName); ?>">
								</div>
							<?php endif; ?>

							<div>
								<label>Last Name: </label>
								<span><?php htmlout($originalLastName); ?></span>
							</div>
							<?php if(isSet($editMode)) : ?>
								<div>
									<label>Change Last Name: </label>
									<input type="text" name="lastName" value="<?php htmlout($lastName); ?>">
								</div>
							<?php endif; ?>

							<div>
								<label>Email: </label>
								<span><?php htmlout($originalEmail); ?></span>
							</div>
							<?php if(isSet($editMode)) : ?>
								<div>
									<label>Change Email: </label>
									<input type="text" name="email" value="<?php htmlout($email); ?>">
								</div>
							<?php endif; ?>
							<div>
								<label>Company Connection: </label>
								<span style="white-space: pre-wrap;"><b><?php htmlout($worksFor); ?></b></span>
							</div>
						</fieldset>

						<fieldset class="left"><legend>Booking Information:</legend>
							<?php if($numberOfTotalBookedMeetings > 0 AND !isSet($editMode)) : ?>
								<div>
									<label>Booked Meetings (Total):</label>
									<span><a href="?totalBooking"><?php htmlout($numberOfTotalBookedMeetings); ?></a></span>
								</div>

								<?php if($numberOfActiveBookedMeetings > 0) : ?>
									<div>
										<label>Booked Meetings (Active):</label>
										<span><a href="?activeBooking"><?php htmlout($numberOfActiveBookedMeetings); ?></a></span>
									</div>
								<?php endif; ?>

								<?php if($numberOfCompletedBookedMeetings > 0) : ?>
									<div>
										<label>Booked Meetings (Completed):</label>
										<span><a href="?completedBooking"><?php htmlout($numberOfCompletedBookedMeetings); ?></a></span>
									</div>
								<?php endif; ?>

								<?php if($numberOfCancelledBookedMeetings > 0) : ?>
									<div>
										<label>Booked Meetings (Cancelled):</label>
										<span><a href="?cancelledBooking"><?php htmlout($numberOfCancelledBookedMeetings); ?></a></span>
									</div>
								<?php endif; ?>
							<?php elseif($numberOfTotalBookedMeetings == 0) : ?>
								<span>This will display your meeting statistics if you have any.</span>
							<?php endif; ?>
							
							<?php if($accessName == "Admin") : ?>
								<div>
									<label>Default Display Name: </label>
									<span style="white-space: pre-wrap;"><?php htmlout($originalDisplayName); ?></span>
								</div>

								<?php if(isSet($editMode)) : ?>
									<div>
										<label>Change Display Name: </label>
										<input type="text" name="displayName" value="<?php htmlout($displayName); ?>">
									</div>
								<?php endif; ?>

								<div>
									<label>Default Booking Description: </label>
									<span style="white-space: pre-wrap;"><?php htmlout($originalBookingDescription); ?></span>
								</div>

								<?php if(isSet($editMode)) : ?>
									<div>
										<label>Change Booking Description: </label>
										<textarea rows="4" cols="50" name="bookingDescription" style="white-space: pre-wrap;"><?php htmlout($bookingDescription); ?></textarea>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<?php if(isSet($userCanHaveABookingCode)) : ?>
								<div>
									<label>Booking Code: </label>
									<span><b><?php htmlout($bookingCodeStatus); ?></b></span>
									<?php if(isSet($userHasABookingCode) AND !isSet($showBookingCode)) : ?>
										<label>Reveal Code: </label><input type="submit" name="action" value="Show Code">
									<?php elseif(isSet($userHasABookingCode) AND isSet($showBookingCode) AND $showBookingCode) : ?>
										<label>Reveal Code: </label><span><b><?php htmlout($showBookingCode); ?></b></span>
									<?php elseif(isSet($userHasABookingCode) AND isSet($showBookingCode) AND $showBookingCode == FALSE) : ?>
										<label>Reveal Code: </label><span><b>Could not retrieve code.</b></span>
									<?php endif; ?>
								</div>

								<?php if(isSet($editMode)) : ?>
									<div>
										<?php if(!isSet($userHasABookingCode)) : ?>
											<label>Set Your Booking Code: </label>
										<?php else : ?>
											<label>Set A New Booking Code: </label>
										<?php endif; ?>
										<?php if(isSet($canSetNewCode)) : ?>
											<input type="number" name="bookingCode" min="1" max="<?php htmlout((10 ** BOOKING_CODE_LENGTH)-1); ?>"
											placeholder="<?php htmlout(BOOKING_CODE_LENGTH . " digits"); ?>" value="">
										<?php else : ?>
											<span><b>You can not set a new booking code before <?php htmlout($displayNextBookingCodeChange); ?></b></span>
										<?php endif; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
						</fieldset>

						<fieldset class="left"><legend>Account Information: </legend>
							<div>
								<label>Account Status: </label>
								<span><?php htmlout($accessName); ?></span>
							</div>

							<div>
								<label>Status Description: </label>
								<span style="white-space: pre-wrap;"><?php htmlout($accessDescription); ?></span>
							</div>

							<div>
								<label>Account & Meeting Alert Status: </label>
								<?php if($originalSendEmail == 1) : ?>
									<span><b>Send Me Email Alerts</b></span>
								<?php elseif($originalSendEmail == 0) : ?>
									<span><b>Don't Send Me Email Alerts</b></span>
								<?php endif; ?>
							</div>

							<?php if(isSet($editMode)) : ?>
								<div>
									<label>Change Account & Meeting Alert Status: </label>
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

							<?php if($userIsACompanyOwner) : ?>
								<div>
									<label>Company Owner Alert Status: </label>
									<?php if($originalSendOwnerEmail == 1) : ?>
										<span><b>Send Me Email Alerts</b></span>
									<?php elseif($originalSendOwnerEmail == 0) : ?>
										<span><b>Don't Send Me Email Alerts</b></span>
									<?php endif; ?>
								</div>

								<?php if(isSet($editMode)) : ?>
									<div>
										<label>Change Company Owner Alert Status: </label>
										<select name="sendOwnerEmail">
											<?php if($sendOwnerEmail == 1) : ?>
												<option selected="selected" value="1"><b>Send Me Email Alerts</b></option>
												<option value="0"><b>Don't Send Me Email Alerts</b></option>
											<?php elseif($sendOwnerEmail == 0) : ?>
												<option value="1"><b>Send Me Email Alerts</b></option>
												<option selected="selected" value="0"><b>Don't Send Me Email Alerts</b></option>										
											<?php endif; ?>
										</select>
									</div>
								<?php endif; ?>

								<?php if($originalSendOwnerEmail == 1) : ?>
									<div>
										<?php foreach($worksForArray AS $company) : ?>
											<?php if($company['CompanyPosition'] == "Owner") : ?>
												<label>Company: <b><?php htmlout($company['CompanyName']); ?></b></label>
												<?php if($company['SendEmailOnceOrAlways'] == 1) : ?>
													<span><b>Send Everytime A Booking Goes Over Credit</b></span>
												<?php elseif($company['SendEmailOnceOrAlways'] == 0) : ?>
													<span><b>Only Send First Time A Booking Goes Over Credit</b></span>
												<?php endif; ?>

												<?php if(isSet($editMode)) : ?>
													<label>Change <b><?php htmlout($company['CompanyName']); ?></b></label>
													<select name="sendCompanyID<?php htmlout($company['CompanyID']); ?>Email">
														<?php if($company['SendEmailOnceOrAlways'] == 1) : ?>
															<option selected="selected" value="1"><b>Send Everytime A Booking Goes Over Credit</b></option>
															<option value="0"><b>Only Send First Time A Booking Goes Over Credit</b></option>
														<?php elseif($company['SendEmailOnceOrAlways'] == 0) : ?>
															<option value="1"><b>Send Everytime A Booking Goes Over Credit</b></option>
															<option selected="selected" value="0"><b>Only Send First Time A Booking Goes Over Credit</b></option>
														<?php endif; ?>
													</select>
												<?php endif; ?>
											<?php endif; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>

							<?php endif; ?>

							<?php if($accessName == "Admin") : ?>
								<div>
									<label>Admin Information Alert Status: </label>
									<?php if($originalSendAdminEmail == 1) : ?>
										<span><b>Send Me Email Alerts</b></span>
									<?php elseif($originalSendAdminEmail == 0) : ?>
										<span><b>Don't Send Me Email Alerts</b></span>
									<?php endif; ?>
								</div>

								<?php if(isSet($editMode)) : ?>
									<div>
										<label>Change Admin Alert Status: </label>
										<select name="sendAdminEmail">
											<?php if($sendAdminEmail == 1) : ?>
												<option selected="selected" value="1"><b>Send Me Email Alerts</b></option>
												<option value="0"><b>Don't Send Me Email Alerts</b></option>
											<?php elseif($sendAdminEmail == 0) : ?>
												<option value="1"><b>Send Me Email Alerts</b></option>
												<option selected="selected" value="0"><b>Don't Send Me Email Alerts</b></option>										
											<?php endif; ?>
										</select>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<div class="left">
								<?php if(isSet($editMode)) : ?>
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
						</fieldset>
					</form>
				</fieldset>
			</div>
		<?php elseif(isSet($_GET['activateaccount'])) : ?>
			<h2>Account Activation</h2>
		<?php elseif(isSet($_GET['resetpassword'])) : ?>
			<h2>Set New Password</h2>
		<?php else : ?>
			<h2>This page requires you to be logged in to view.</h2>
		<?php endif; ?>
	</body>
</html>