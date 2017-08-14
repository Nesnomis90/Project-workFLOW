<!-- This is the HTML form used for EDITING BOOKING information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Change Meeting Room</title>
		<style>
			label{
				width: 220px;
			}
		</style>		
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Change Meeting Room</legend>
			<div class="left">
				<?php if(isSet($_SESSION['BookingRoomChangeError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['BookingRoomChangeError']); ?></b></span>
					<?php unset($_SESSION['BookingRoomChangeError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
				<div>
					<label>Current Booked Meeting Room: </label>
					<span><b><?php htmlout($originalMeetingRoomName); ?></b></span>
				</div>

				<div>
					<label>Swap To An Available Room:</label>
					<?php if(isSet($availableRooms)) : ?>
						<select name="availableRooms">
							<option value="">Select A Room</option>
							<?php foreach($availableRooms AS $room) : ?>
								<option value="<?php htmlout($room['MeetingRoomID']); ?>"><?php htmlout($room['MeetingRoomName']); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<span><b>There are no available rooms.</b></span>
					<?php endif; ?>
				</div>

				<div>
					<label>Swap With An Occupied Room:</label>
					<?php if(isSet($occupiedRooms)) : ?>
						<select name="occupiedRooms">
							<option value="">Select A Room</option>
							<?php foreach($occupiedRooms AS $room) : ?>
								<option value="<?php htmlout($room['MeetingRoomID'] . "|" . $room['BookingID']); ?>"><?php htmlout($room['MeetingRoomName']); ?></option>
							<?php endforeach; ?>
						</select><span>*</span>
						<span><b>* This requires confirmation from the owner of the booked meeting room, or someone with the appropriate access rights.</b></span>
					<?php else : ?>
						<span><b>There are no occupied rooms.</b></span>
					<?php endif; ?>	
				</div>

				<div class="left">
					<input type="submit" name="changeroom" value="Go Back">
					<input type="submit" name="changeroom" value="Confirm Change">
				</div>
			</form>
		</fieldset>
	</body>
</html>