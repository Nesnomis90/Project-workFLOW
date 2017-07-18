<!-- This is the HTML form used for EDITING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">	
		<title>Edit Booking</title>
		<style>
			label {
				width: 200px;
			}
		</style>
	</head>
	<body>
		<h1>Edit Booking</h1>
		
		<div>
			<?php if(isSet($_SESSION['EditBookingError'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['EditBookingError']); ?></b></span>
				<?php unset($_SESSION['EditBookingError']); ?>
			<?php endif; ?>
		</div>
		
		<form action="" method="post">
			<div>
				<label for="originalMeetingRoomName">Booked Meeting Room: </label>
				<span><b><?php htmlout($originalMeetingRoomName); ?></b></span>
			</div>
			<div>
				<label for="meetingRoomID">Set New Meeting Room: </label>
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
				<label for="originalStartDateTime">Booked Start Time: </label>
				<span><b><?php htmlout($originalStartDateTime); ?></b></span>
			</div>
			<div>
				<label for="startDateTime">Set New Start Time: </label>				
				<input type="text" name="startDateTime" id="startDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($startDateTime); ?>">
				<input type="submit" name="edit" value="Increase Start By Minimum">
			</div>
			<div>	
				<label for="originalEndDateTime">Booked End Time: </label>
				<span><b><?php htmlout($originalEndDateTime); ?></b></span>
			</div>
			<div>
				<label for="endDateTime">Set New End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				placeholder="date hh:mm:ss" 
				value="<?php htmlout($endDateTime); ?>">
				<input type="submit" name="edit" value="Increase End By Minimum">
			</div>
			<div>
				<label for="originalSelectedUser">Booked For User: </label>
				<span><b><?php htmlout($originalUserInformation); ?></b></span>
			</div>
			<div>
				<label for="SelectedUser">Set New User: </label>
				<?php if(isSet($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']) :?>
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
						<input type="submit" name="edit" value="Select This User">
					<?php else : ?>
						<span><b>The search found 0 users.</b></span>
					<?php endif; ?>
					</div>
					<div>
						<label for="usersearchstring">Search for User:</label>
						<input type="text" name="usersearchstring" 
						value="<?php htmlout($usersearchstring); ?>">
						<input type="submit" name="edit" value="Search">
					</div>
				<?php elseif(isSet($SelectedUserID) AND $SelectedUserID != NULL AND $SelectedUserID != "") : ?>
						<span><b><?php htmlout($userInformation); ?> </b></span>
						<input type="submit" name="edit" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="<?php htmlout($SelectedUserID);?>">
					</div>
				<?php else : ?>
						<span><b>N/A - Deleted</b></span>
						<input type="submit" name="edit" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="">			
					</div>
				<?php endif; ?>
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
				<?php if(	isSet($_SESSION['EditBookingDisplayCompanySelect']) AND 
							$_SESSION['EditBookingDisplayCompanySelect']) : ?>
					<?php if(isSet($_SESSION['EditBookingSelectACompany'])) : ?>
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
					<?php endif; ?>
				<?php else : ?>
					<?php if(isSet($company)) : ?>
						<span><b>This user is only connected to one company: <?php htmlout($companyName); ?></b></span>
					<?php else : ?>
						<span><b>This user is not connected to a company.</b></span>
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
				<label for="displayName">Set New Display Name: </label>
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
				<input type="submit" name="edit" value="Cancel">
				<?php if(	(isSet($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']) OR 
							($SelectedUserID == "" OR $SelectedUserID == NULL OR !isSet($SelectedUserID))) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<span><b>You need to select the user you want before you can finish editing.</b></span>
				<?php elseif(isSet($_SESSION['EditBookingSelectACompany'])) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<span><b>You need to select the company you want before you can finish editing.</b></span>			
				<?php else : ?>
					<input type="submit" name="edit" value="Finish Edit">
				<?php endif; ?>
			</div>
		</form>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>