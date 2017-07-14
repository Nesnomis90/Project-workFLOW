<!-- This is the HTML form used for ADDING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Book A New Meeting</title>
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label{
				width: 140px;
			}
		</style>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<h1>Book A New Meeting</h1>
		
		<div class="left">
		<?php if(isset($_SESSION['AddCreateBookingError'])) : ?>
			<span><b><?php htmlout($_SESSION['AddCreateBookingError']); ?></b></span>
			<?php unset($_SESSION['AddCreateBookingError']); ?>
		<?php endif; ?>
		</div>
		
		<form action="" method="post">
			<div class="left">
				<label for="userInformation">Welcome </label>
				<?php $firstName = $_SESSION["AddCreateBookingOriginalInfoArray"]["UserFirstname"]; ?>
				<?php $lastName = $_SESSION["AddCreateBookingOriginalInfoArray"]["UserLastname"]; ?>
				<?php $userInformation = $lastName . ", " . $firstName; ?>
				<span><b><?php htmlout($userInformation); ?></b></span>
			</div>

			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<?php if(isset($_GET['meetingroom'])) : ?>
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomID']==$_GET['meetingroom']):?>
							<span><b><?php htmlout($row['meetingRoomName']);?></b></span>
						<?php endif;?>
					<?php endforeach; ?>					
				<?php else : ?>
					<select name="meetingRoomID" id="meetingRoomID">
						<?php foreach($meetingroom as $row): ?> 
							<?php if($row['meetingRoomID']==$selectedMeetingRoomID):?>
								<option selected="selected" value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
							<?php else : ?>
								<option value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>

			<div>
				<label for="startDateTime">Start Time: </label>
				<?php if(isset($_SESSION['AddCreateBookingStartImmediately']) AND $_SESSION['AddCreateBookingStartImmediately']) : ?>
					<input type="text" name="disabled" id="disabled" 
					placeholder="date hh:mm:ss"
					value="<?php htmlout($startDateTime); ?>"
					disabled>
					<input type="hidden" name="startDateTime" id="startDateTime" 
					placeholder="date hh:mm:ss"
					value="<?php htmlout($startDateTime); ?>">
					<input type="submit" name="add" value="Change Start Time">
				<?php else : ?>
					<input type="text" name="startDateTime" id="startDateTime" 
					placeholder="date hh:mm:ss"
					value="<?php htmlout($startDateTime); ?>">
					<input type="submit" name="add" value="Increase Start By Minimum">
					<input type="submit" name="add" value="Start Booking Immediately">				
				<?php endif; ?>
			</div>

			<div>
				<label for="endDateTime">End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($endDateTime); ?>">
				<input type="submit" name="add" value="Increase End By Minimum">
			</div>

			<div>
				<label for="companyID">Company: </label>
				<?php if(	isset($_SESSION['AddCreateBookingDisplayCompanySelect']) AND 
							$_SESSION['AddCreateBookingDisplayCompanySelect']) : ?>
					<?php if(!isset($_SESSION['AddCreateBookingSelectedACompany'])) : ?>
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
					<?php endif; ?>
				<?php else : ?>
					<?php if(isset($company)) : ?>
						<span><b>Your booking will automatically be connected with company: <?php htmlout($companyName); ?></b></span>
					<?php else : ?>
						<span><b>Your booking will not be connected with a company.</b></span>
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

			<div class="left">
				<label class="description" for="description">Booking Description: </label>
				<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
				<input type="submit" name="add" value="Get Default Booking Description"> 
			</div>

			<div class="left">
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
				<?php if(!isset($_SESSION['AddCreateBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Add Booking" disabled>
					<span><b>You need to select the company you want before you can add the booking.</b></span>
				<?php else : ?>
					<input type="submit" name="add" value="Add Booking">
				<?php endif; ?>				
			</div>
		</form>
	</body>
</html>