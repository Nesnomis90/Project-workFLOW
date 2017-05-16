<!-- This is the HTML form used to display booking information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Booking Information</title>
	</head>
	<body>
		<h1>Booking Information</h1>
		<?php if(isset($_SESSION['normalBookingFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['normalBookingFeedback']); ?></b></p>
			<?php unset($_SESSION['normalBookingFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<?php if(isset($_GET['meetingroom'])) : ?>
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomID']==$_GET['meetingroom']):?>
							<div><b><?php htmlout($row['meetingRoomName']);?></b></div>
						<?php endif;?>
					<?php endforeach; ?>					
				<?php else : ?>
					<select name="meetingRoomID" id="meetingRoomID">
						<?php foreach($meetingroom as $row): ?> 
							<?php if($row['meetingRoomID']==$selectedMeetingRoomID):?>
								<option selected="selected" 
										value="<?php htmlout($row['meetingRoomID']); ?>">
										<?php htmlout($row['meetingRoomName']);?>
								</option>
							<?php else : ?>
								<option value="<?php htmlout($row['meetingRoomID']); ?>">
										<?php htmlout($row['meetingRoomName']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
			<div>
				<label for="startDateTime">Start Time: </label>
				<input type="text" name="startDateTime" id="startDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($startDateTime); ?>">
			</div>
			<div>
				<label for="endDateTime">End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($endDateTime); ?>">
			</div>
			<div>
				<label for="companyID">Company: </label>
				<?php if(	isset($_SESSION['CreateBookingDisplayCompanySelect']) AND 
							$_SESSION['CreateBookingDisplayCompanySelect']) : ?>
					<?php if(!isset($_SESSION['CreateBookingSelectedACompany'])) : ?>
						<select name="companyID" id="companyID">
							<?php foreach($company as $row): ?> 
								<?php if($row['companyID']==$selectedCompanyID):?>
									<option selected="selected" 
											value="<?php htmlout($row['companyID']); ?>">
											<?php htmlout($row['companyName']);?>
									</option>
								<?php else : ?>
									<option value="<?php htmlout($row['companyID']); ?>">
											<?php htmlout($row['companyName']);?>
									</option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="action" value="Select This Company">
					<?php else : ?>
						<b><?php htmlout($companyName); ?></b>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
						<input type="submit" name="action" value="Change Company">
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
				<label for="displayName">Display Name: </label>
				<input type="text" name="displayName" id="displayName" 
				value="<?php htmlout($displayName); ?>">
				<input type="submit" name="action" value="Get Default Display Name">
			</div>
			<div>
				<label for="description">Booking Description: </label>
				<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
				<input type="submit" name="action" value="Get Default Booking Description"> 
			</div>
			<div>
				<input type="submit" name="action" value="Reset">
				<?php if(!isset($_SESSION['CreateBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Create Meeting" disabled>
					<b>You need to select the company you want before you can add the booking.</b>
				<?php else : ?>
					<input type="submit" name="action" value="Create Meeting">
				<?php endif; ?>				
			</div>
		</form>
		<?php //TO-DO: Fix -> include '../logout.inc.html.php'; ?>
	</body>
</html>