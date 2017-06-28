<!-- This is the HTML form used for ADDING BOOKING information-->
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
		<title>Book A New Meeting</title>
	</head>
	<body>
		<h1>Book A New Meeting</h1>
		<?php if(isset($_SESSION['AddBookingError'])) : ?>
			<p><b><?php htmlout($_SESSION['AddBookingError']); ?></b></p>
			<?php unset($_SESSION['AddBookingError']); ?>
		<?php endif; ?>
		<?php if(isset($_SESSION['AddBookingUserCannotBookForSelf'])) : ?>
			<b><span style="color:red">You can not book a meeting for yourself since you are not connected to a company.</span></b>
		<?php endif; ?>
		<form action="" method="post">
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
				<?php if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']) : ?>
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
						<input type="submit" name="add" value="Select This User">
					<?php else : ?>
						<b>The search found 0 users.</b>
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
						<b>You:  <?php htmlout($userInformation); ?> </b>
					<?php else : ?>
						<b><?php htmlout($userInformation); ?> </b>
					<?php endif; ?>
						<input type="submit" name="add" value="Change User">
						<input type="hidden" name="userID" id="userID"
						value="<?php htmlout($SelectedUserID);?>">
					</div>			
				<?php endif; ?>
			<div>
				<label for="companyID">Company: </label>
				<?php if(	isset($_SESSION['AddBookingDisplayCompanySelect']) AND 
							$_SESSION['AddBookingDisplayCompanySelect']) : ?>
					<?php if(!isset($_SESSION['AddBookingSelectedACompany'])) : ?>
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
						<b><?php htmlout($companyName); ?></b>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
						<input type="submit" name="add" value="Change Company">
					<?php endif; ?>
				<?php else : ?>
					<?php if(isset($company)) : ?>
						<b><?php htmlout($companyName); ?></b>
					<?php else : ?>
						<b>This user is not connected to a company.</b>
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
				<label for="description">Booking Description: </label>
				<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
				<input type="submit" name="add" value="Get Default Booking Description"> 
			</div>
			<div>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
				<?php if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<b>You need to select the user you want before you can add the booking.</b>
				<?php elseif(!isset($_SESSION['AddBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<b>You need to select the company you want before you can add the booking.</b>
				<?php else : ?>
					<input type="submit" name="add" value="Add booking">
				<?php endif; ?>				
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>