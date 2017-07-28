<!-- This is the HTML form used for ADDING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Book A New Meeting</title>
		<style>
			label {
				width: 140px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<form action="" method="post">
		<fieldset><legend><b>Book A New Meeting</b></legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddBookingError'])) : ?>
					<span><b class="warning"><?php htmlout($_SESSION['AddBookingError']); ?></b></span>
					<?php unset($_SESSION['AddBookingError']); ?>
				<?php endif; ?>	
			</div>

			<div class="left">
				<?php if(isSet($_SESSION['AddBookingUserCannotBookForSelf'])) : ?>
					<span><b class="feedback">You can not book a meeting for yourself since you are not connected to a company.</b></span>
				<?php endif; ?>	
			</div>

			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<select name="meetingRoomID" id="meetingRoomID">
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomID']==$selectedMeetingRoomID):?>
							<option selected="selected" value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
						<?php else : ?>
							<option value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
						<?php endif;?>
					<?php endforeach; ?>
				</select>				
			</div>

			<div>
				<label for="startDateTime">Start Time: </label>
				<input type="text" name="startDateTime" id="startDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($startDateTime); ?>">
				<input type="submit" name="add" value="Increase Start By Minimum">
			</div>

			<div>
				<label for="endDateTime">End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($endDateTime); ?>">
				<input type="submit" name="add" value="Increase End By Minimum">
			</div>

			<div>
				<label for="SelectedUser">User: </label>
				<?php if(isSet($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']) : ?>
					<?php if(isSet($users)) : ?>
						<select name="userID" id="userID">
							<?php foreach($users as $row): ?> 
								<?php if($row['userID'] == $SelectedUserID):?>
									<option style="background-color:grey; color:white;" selected="selected" 
									value="<?php htmlout($row['userID']); ?>">Last Selected: <?php htmlout($row['userInformation']);?></option>
								<?php elseif($row['userID'] == $_SESSION['LoggedInUserID']) : ?>
									<option style="background-color:grey; color:white;" value="<?php htmlout($row['userID']); ?>">You: <?php htmlout($row['userInformation']);?></option>									
								<?php else : ?>
									<option value="<?php htmlout($row['userID']); ?>"><?php htmlout($row['userInformation']);?></option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="add" value="Select This User">
					<?php else : ?>
						<span><b>The search found 0 users.</b></span>
					<?php endif; ?>
					</div>
					<div>
						<label for="usersearchstring">Search for User:</label>
						<input type="text" name="usersearchstring" 
						value="<?php htmlout($usersearchstring); ?>">
						<input type="submit" name="add" value="Search">
					</div>
				<?php else : ?>
					<?php if($_SESSION['LoggedInUserID'] == $SelectedUserID) : ?>
						<span><b>You:  <?php htmlout($userInformation); ?></b></span>
					<?php else : ?>
						<span><b><?php htmlout($userInformation); ?> </b></span>
					<?php endif; ?>
						<input type="submit" name="add" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="<?php htmlout($SelectedUserID);?>">
					</div>			
				<?php endif; ?>

			<div>
				<label for="companyID">Company: </label>
				<?php if(	isSet($_SESSION['AddBookingDisplayCompanySelect']) AND 
							$_SESSION['AddBookingDisplayCompanySelect']) : ?>
					<?php if(!isSet($_SESSION['AddBookingSelectedACompany'])) : ?>
						<select name="companyID" id="companyID">
							<?php foreach($company as $row): ?> 
								<?php if($row['companyID']==$selectedCompanyID):?>
									<option selected="selected" value="<?php htmlout($row['companyID']); ?>"><?php htmlout($row['companyName']);?></option>
								<?php else : ?>
									<option value="<?php htmlout($row['companyID']); ?>"><?php htmlout($row['companyName']);?></option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="add" value="Select This Company">
					<?php else : ?>
						<span><b><?php htmlout($companyName); ?></b></span>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
						<input type="submit" name="add" value="Change Company">
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
					<?php endif; ?>
				<?php else : ?>
					<?php if(isSet($company)) : ?>
						<span><b><?php htmlout($companyName); ?></b></span>
					<?php else : ?>
						<span><b>This user is not connected to a company.</b></span>
					<?php endif; ?>
					<input type="hidden" name="companyID" id="companyID" 
					value="<?php htmlout($companyID); ?>">
				<?php endif; ?>
			</div>

			<div>
				<label for="displayName">Display Name: </label>
				<input type="text" name="displayName" id="displayName" 
				value="<?php htmlout($displayName); ?>">
				<input type="submit" name="add" value="Get Default Display Name">
			</div>

			<div>
				<label class="description" for="description">Booking Description: </label>
				<textarea rows="4" cols="50" name="description"
				placeholder="The description logged in users will see about the meeting."><?php htmlout($description); ?></textarea>
				<input type="submit" name="add" value="Get Default Booking Description"> 
			</div>

			<div>
				<label class="description" for="adminNote">Admin Note: </label>
				<textarea rows="4" cols="50" name="adminNote"
				placeholder="Type in any additional information that only admin can see. This will highlighted during the billing period."><?php htmlout($adminNote); ?></textarea>
			</div>

			<div class="left">
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
				<?php if(isSet($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<span><b>You need to select the user you want before you can add the booking.</b></span>
				<?php elseif(!isSet($_SESSION['AddBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<span><b>You need to select the company you want before you can add the booking.</b></span>
				<?php else : ?>
					<input type="submit" name="add" value="Add booking">
				<?php endif; ?>
				<?php if(isSet($_SESSION['AddBookingSelectedACompany'])) : ?>
					<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("¹ The given credit minus the sum of completed bookings this period.\n  This does not take into account non-completed bookings."); ?></b></span>
					<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("² The sum of future bookings this period that have not been completed yet.\n  This is the maximum extra credits that have a potential of being used if the booking(s) complete."); ?></b></span>
					<span style="clear: both; white-space: pre-wrap;"><b><?php htmlout("³ The potential minimum credits remaining if all booked meetings complete.\n  The actual remaining credits will be higher if the booking(s) cancel or complete early."); ?></b></span>
				<?php endif; ?>
			</div>
		</fieldset>
		</form>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>