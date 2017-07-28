<!-- This is the HTML form used for EDITING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Edit Booking</title>
		<style>
			label{
				width: 200px;
			}
		</style>		
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Edit Booking</legend>
			<div class="left">
				<?php if(isSet($_SESSION['EditCreateBookingError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['EditCreateBookingError']); ?></b></span>
					<?php unset($_SESSION['EditCreateBookingError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
				<div>
					<label for="userInformation">Welcome </label>
					<span><b><?php htmlout($_SESSION['EditCreateBookingLoggedInUserInformation']); ?></b></span>
				</div>

				<div>
					<label for="originalMeetingRoomName">Booked Meeting Room: </label>
					<span><b><?php htmlout($originalMeetingRoomName); ?></b></span>
				</div>

				<div>
					<label for="originalStartDateTime">Booked Start Time: </label>
					<span><b><?php htmlout($originalStartDateTime); ?></b></span>
					<input type="hidden" name="startDateTime" value="<?php htmlout($originalStartDateTime); ?>">
				</div>

				<div>	
					<label for="originalEndDateTime">Booked End Time: </label>
					<span><b><?php htmlout($originalEndDateTime); ?></b></span>
					<input type="hidden" name="endDateTime" value="<?php htmlout($originalEndDateTime); ?>">
				</div>

				<div>
					<label for="originalSelectedUser">Booked For User: </label>
					<span><b><?php htmlout($originalUserInformation); ?></b></span>
				</div>

				<div>
					<label for="originalCompanyInBooking">Booked for Company: </label>
					<?php if(isSet($originalCompanyName)) :?>
						<span><b><?php htmlout($originalCompanyName); ?></b></span>
					<?php else : ?>
						<span><b>This booking had no company assigned.</b></span>
					<?php endif; ?>
				</div>

				<div>
					<label for="companyID">Set New Company: </label>
					<?php if(	isSet($_SESSION['EditCreateBookingDisplayCompanySelect']) AND 
								$_SESSION['EditCreateBookingDisplayCompanySelect']) : ?>
						<?php if(isSet($_SESSION['EditCreateBookingSelectACompany'])) : ?>
							<select name="companyID" id="companyID">
								<?php foreach($company as $row): ?> 
									<?php if($row['companyID']==$selectedCompanyID):?>
										<option selected="selected" value="<?php htmlout($row['companyID']); ?>"><?php htmlout($row['companyName']);?></option>
									<?php else : ?>
										<option value="<?php htmlout($row['companyID']); ?>"><?php htmlout($row['companyName']);?></option>
									<?php endif;?>
								<?php endforeach; ?>
							</select>
							<input type="submit" name="edit" value="Select This Company">
						<?php else : ?>
							<span><b><?php htmlout($companyName); ?></b></span>
							<input type="hidden" name="companyID" id="companyID" 
							value="<?php htmlout($companyID); ?>">
							<input type="submit" name="edit" value="Change Company">							
							<label>Credits Remaining: </label>
							<?php if(substr($creditsRemaining,0,1) === "-") : ?>
								<span style="color:red"><?php htmlout($creditsRemaining); ?></span><span>¹</span>
							<?php else : ?>
								<span style="color:green"><?php htmlout($creditsRemaining); ?></span><span>¹</span>
							<?php endif; ?>	
							<label>Credits Booked: </label>
							<span><?php htmlout($potentialExtraCreditsUsed); ?></span><span>²</span>
							<label>Potential Remaining: </label>
							<?php if(substr($potentialCreditsRemaining,0,1) === "-") : ?>
								<span style="color:red"><?php htmlout($potentialCreditsRemaining); ?></span><span>³</span>
							<?php else : ?>
								<span style="color:green"><?php htmlout($potentialCreditsRemaining); ?></span><span>³</span>
							<?php endif; ?>
							<label>Next Period At:</label>
							<span><b><?php htmlout($companyPeriodEndDate); ?></b></span>
						<?php endif; ?>
					<?php else : ?>
						<?php if(isSet($company)) : ?>
							<span><b>You are only connected to one company: <?php htmlout($companyName); ?></b></span>
							<label>Credits Remaining: </label>
							<?php if(substr($creditsRemaining,0,1) === "-") : ?>
								<span style="color:red"><?php htmlout($creditsRemaining); ?></span><span>¹</span>
							<?php else : ?>
								<span style="color:green"><?php htmlout($creditsRemaining); ?></span><span>¹</span>
							<?php endif; ?>	
							<label>Credits Booked: </label>
							<span><?php htmlout($potentialExtraCreditsUsed); ?></span><span>²</span>
							<label>Potential Remaining: </label>
							<?php if(substr($potentialCreditsRemaining,0,1) === "-") : ?>
								<span style="color:red"><?php htmlout($potentialCreditsRemaining); ?></span><span>³</span>
							<?php else : ?>
								<span style="color:green"><?php htmlout($potentialCreditsRemaining); ?></span><span>³</span>
							<?php endif; ?>
							<label>Next Period At:</label>
							<span><b><?php htmlout($companyPeriodEndDate); ?></b></span>
						<?php else : ?>
							<span><b>You are not connected with a company.</b></span>
						<?php endif; ?>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
					<?php endif; ?>
				</div>

				<div>
					<label for="originalDisplayName">Booked Display Name: </label>
					<span><b>
						<?php if($originalDisplayName == "") : ?>
							This booking has no Display Name set.
						<?php else : ?>
							<?php htmlout($originalDisplayName); ?>
						<?php endif; ?>
					</b></span>
				</div>

				<div>
					<label for="displayName">Change Display Name: </label>
					<input type="text" name="displayName" id="displayName" 
					value="<?php htmlout($displayName); ?>">
					<input type="submit" name="edit" value="Get Default Display Name">
				</div>

				<div>
					<label for="originalBookingDescription">Booked Description: </label>
					<span><b>
						<?php if($originalBookingDescription == "") : ?>
							This booking has no Booking Description set.
						<?php else : ?>
							<?php htmlout($originalBookingDescription); ?>
						<?php endif; ?>
					</b></span>
				</div>

				<div>
					<label class="description" for="description">Set New Booking Description: </label>
					<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
					<input type="submit" name="edit" value="Get Default Booking Description">
				</div>

				<div class="left">
					<input type="hidden" name="bookingID" id="bookingID" 
					value="<?php htmlout($bookingID); ?>">
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Go Back">
					<?php if(isSet($_SESSION['EditCreateBookingSelectACompany'])) : ?>
						<input type="submit" name="disabled" value="Finish Edit" disabled>
						<span><b>You need to select the company you want before you can finish editing.</b></span>			
					<?php else : ?>
						<input type="submit" name="edit" value="Finish Edit">
					<?php endif; ?>
					<?php if(isSet($_SESSION['EditCreateBookingSelectACompany'])) : ?>
						<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("¹ The given credit minus the sum of completed bookings this period (up to $companyPeriodEndDate).\n  This does not take into account non-completed bookings."); ?></b></span>
						<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("² The sum of future bookings this period that have not been completed yet.\n  This is the maximum extra credits that have a potential of being used if the booking(s) complete."); ?></b></span>
						<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("³ The potential minimum credits remaining if all booked meetings complete.\n  The actual remaining credits will be higher if the booking(s) cancel or complete early."); ?></b></span>
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>