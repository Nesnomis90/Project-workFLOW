<!-- This is the HTML form used for ADDING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Book A New Meeting</title>
	</head>
	<body>
		<h1>Book A New Meeting</h1>
		<?php if(isset($_SESSION['AddBookingError'])) : ?>
			<p><b><?php htmlout($_SESSION['AddBookingError']); ?></b></p>
			<?php unset($_SESSION['AddBookingError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<select name="meetingRoomID" id="meetingRoomID">
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomName']==$meetingroomname):?>
							<option selected="selected" 
									value=<?php htmlout($row['meetingRoomID']); ?>>
									<?php htmlout($row['meetingRoomName']);?>
							</option>
						<?php else : ?>
							<option value=<?php htmlout($row['meetingRoomID']); ?>>
									<?php htmlout($row['meetingRoomName']);?>
							</option>
						<?php endif;?>
					<?php endforeach; ?>
				</select>				
			</div>
			<div>
				<label for="startDateTime">Start Time: </label>
				<input type="text" name="startDateTime" id="startDateTime" 
				required placeholder="dd-mm-yyyy hh:mm:ss" 
				oninvalid="this.setCustomValidity('Enter Your Starting Date And Time Here')"
				oninput="setCustomValidity('')"
				value="<?php htmlout($startDateTime); ?>">
			</div>
			<div>
				<label for="endDateTime">End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				required placeholder="dd-mm-yyyy hh:mm:ss" 
				oninvalid="this.setCustomValidity('Enter Your Ending Date And Time Here')"
				oninput="setCustomValidity('')"
				value="<?php htmlout($endDateTime); ?>">
			</div>
			<div>
				<?php if($displayCompanySelect == TRUE) : ?>
					<label for="companyID">Company: </label>
					<select name="companyID" id="companyID">
						<?php foreach($company as $row): ?> 
							<?php if($row['companyName']==$companyname):?>
								<option selected="selected" 
										value=<?php htmlout($row['companyID']); ?>>
										<?php htmlout($row['companyName']);?>
								</option>
							<?php else : ?>
								<option value=<?php htmlout($row['companyID']); ?>>
										<?php htmlout($row['companyName']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				<?php else : ?>
					<input type="hidden" name="companyID" id="companyID" 
					value="<?php htmlout($companyID); ?>">
				<?php endif; ?>
			</div>
			<div>
				<label for="displayName">Display Name: </label>
				<input type="text" name="displayName" id="displayName" 
				value="<?php htmlout($displayName); ?>">
			</div>
			<div>
				<label for="description">Booking Description: </label>
				<input type="text" name="description" id="description" 
				value="<?php htmlout($description); ?>">
			</div>
			<div>
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" value="Add booking">
			</div>
			<div>
				<input type="reset">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>