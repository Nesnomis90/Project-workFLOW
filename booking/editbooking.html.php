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
		<?php if(isset($_SESSION['EditCreateBookingError'])) : ?>
			<p><b><?php htmlout($_SESSION['EditCreateBookingError']); ?></b></p>
			<?php unset($_SESSION['EditCreateBookingError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="userInformation">Welcome </label>
				<b><?php htmlout($_SESSION['EditCreateBookingLoggedInUserInformation']); ?></b>
			</div>		
			<div>
				<label for="originalMeetingRoomName">Booked Meeting Room: </label>
				<b><?php htmlout($originalMeetingRoomName); ?></b>
			</div>
			<div>
				<label for="originalStartDateTime">Booked Start Time: </label>
				<b><?php htmlout($originalStartDateTime); ?></b>
				<input type="hidden" name="startDateTime" value="<?php htmlout($originalStartDateTime); ?>">
			</div>
			<div>	
				<label for="originalEndDateTime">Booked End Time: </label>
				<b><?php htmlout($originalEndDateTime); ?></b>
				<input type="hidden" name="endDateTime" value="<?php htmlout($originalEndDateTime); ?>">
			</div>
			<div>
				<label for="originalSelectedUser">Booked For User: </label>
				<b><?php htmlout($originalUserInformation); ?></b>
			</div>
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
				<?php if(	isset($_SESSION['EditCreateBookingDisplayCompanySelect']) AND 
							$_SESSION['EditCreateBookingDisplayCompanySelect']) : ?>
					<?php if(isset($_SESSION['EditCreateBookingSelectACompany'])) : ?>
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
						<b>You are only connected to one company: <?php htmlout($companyName); ?></b>
					<?php else : ?>
						<b>You are not connected with a company.</b>
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
				<input type="submit" name="edit" value="Go Back">
				<?php if(isset($_SESSION['EditCreateBookingSelectACompany'])) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<b>You need to select the company you want before you can finish editing.</b>				
				<?php else : ?>
					<input type="submit" name="edit" value="Finish Edit">
				<?php endif; ?>
			</div>
		</form>
	</body>
</html>