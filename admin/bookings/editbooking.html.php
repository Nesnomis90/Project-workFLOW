<!-- This is the HTML form used for EDITING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#description {
				vertical-align: top;
			}
		</style>		
		<title>Edit Booking</title>
	</head>
	<body>
		<h1>Edit Booking</h1>
		<?php if(isset($_SESSION['EditBookingError'])) : ?>
			<p><b><?php htmlout($_SESSION['EditBookingError']); ?></b></p>
			<?php unset($_SESSION['EditBookingError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="originalMeetingRoomName">Booked Meeting Room: </label>
				<b><?php htmlout($originalMeetingRoomName); ?></b>
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
				<b><?php htmlout($originalStartDateTime); ?></b>
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
				<b><?php htmlout($originalEndDateTime); ?></b>
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
				<b><?php htmlout($originalUserInformation); ?></b>
			</div>
			<div>
				<label for="SelectedUser">Set New User: </label>
				<?php if(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']) :?>
					<?php if(isset($users)) : ?>
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
						<b>The search found 0 users.</b>
					<?php endif; ?>
					</div>
					<div>
						<label for="usersearchstring">Search for User:</label>
						<input type="text" name="usersearchstring" 
						value="<?php htmlout($usersearchstring); ?>">
						<input type="submit" name="edit" value="Search">
					</div>
				<?php elseif(isset($SelectedUserID) AND $SelectedUserID != NULL AND $SelectedUserID != "") : ?>
						<b><?php htmlout($userInformation); ?> </b>
						<input type="submit" name="edit" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="<?php htmlout($SelectedUserID);?>">
					</div>
				<?php else : ?>
						<b>N/A - Deleted</b>
						<input type="submit" name="edit" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="">			
					</div>
				<?php endif; ?>
			<div>
				<label for="originalCompanyInBooking">Booked for Company: </label>
				<?php if(isset($originalCompanyName)) :?>
					<b><?php htmlout($originalCompanyName); ?></b>
				<?php else : ?>
					<b>This booking had no company assigned.</b>
				<?php endif; ?>
			</div>
			<div>
				<label for="companyID">Set New Company: </label>
				<?php if(	isset($_SESSION['EditBookingDisplayCompanySelect']) AND 
							$_SESSION['EditBookingDisplayCompanySelect']) : ?>
					<?php if(isset($_SESSION['EditBookingSelectACompany'])) : ?>
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
						<b><?php htmlout($companyName); ?></b>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
						<input type="submit" name="edit" value="Change Company">
					<?php endif; ?>
				<?php else : ?>
					<?php if(isset($company)) : ?>
						<b>This user is only connected to one company: <?php htmlout($companyName); ?></b>
					<?php else : ?>
						<b>This user is not connected to a company.</b>
					<?php endif; ?>
					<input type="hidden" name="companyID" id="companyID" 
					value="<?php htmlout($companyID); ?>">
				<?php endif; ?>
			</div>
			<div>
				<label for="originalDisplayName">Booked Display Name: </label>
				<b>
					<?php if($originalDisplayName == "") : ?>
						<b>This booking has no Display Name set.</b>
					<?php else : ?>
						<?php htmlout($originalDisplayName); ?>
					<?php endif; ?>
				</b>
			</div>
			<div>
				<label for="displayName">Set New Display Name: </label>
				<input type="text" name="displayName" id="displayName" 
				value="<?php htmlout($displayName); ?>">
				<input type="submit" name="edit" value="Get Default Display Name">
			</div>
			<div>
				<label for="originalBookingDescription">Booked Description: </label>
				<b>
					<?php if($originalBookingDescription == "") : ?>
						<b>This booking has no Booking Description set.</b>
					<?php else : ?>
						<?php htmlout($originalBookingDescription); ?>
					<?php endif; ?>
				</b>
			</div>
			<div>
				<label for="description">Set New Booking Description: </label>
				<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
				<input type="submit" name="edit" value="Get Default Booking Description">
			</div>
			<div>
				<input type="hidden" name="bookingID" id="bookingID" 
				value="<?php htmlout($bookingID); ?>">
				<input type="submit" name="edit" value="Reset">
				<input type="submit" name="edit" value="Cancel">
				<?php if(	(isset($_SESSION['EditBookingChangeUser']) AND $_SESSION['EditBookingChangeUser']) OR 
							($SelectedUserID == "" OR $SelectedUserID == NULL OR !isset($SelectedUserID))) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<b>You need to select the user you want before you can finish editing.</b>
				<?php elseif(isset($_SESSION['EditBookingSelectACompany'])) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<b>You need to select the company you want before you can finish editing.</b>				
				<?php else : ?>
					<input type="submit" name="edit" value="Finish Edit">
				<?php endif; ?>
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>