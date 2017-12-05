<!-- This is the HTML form used for EDITING BOOKING information-->
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

			<form method="post">
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
						</select><span> ¹</span>
						<?php if(isSet($unavailableOccupiedRooms)) : ?>
							<label>Excluded Occupied Rooms:</label>
							<span style="white-space: pre-wrap;"><b><?php htmlout($unavailableOccupiedRooms); ?></b>²</span>
							<span><b>¹ This requires confirmation from the owner of the booked meeting, or someone with the appropriate access rights.</b></span>
							<span><b>² These rooms are occupied in the same time slot as your booked meeting, but they overlap with more than one meeting.</b></span>
						<?php else : ?>
							<span><b>¹ This requires confirmation from the owner of the booked meeting, or someone with the appropriate access rights.</b></span>
						<?php endif; ?>
					<?php else : ?>
						<span><b>There are no occupied rooms.</b></span>
						<?php if(isSet($unavailableOccupiedRooms)) : ?>
							<label>Excluded Occupied Rooms:</label>
							<span><?php htmlout($unavailableOccupiedRooms); ?>¹</span>
							<span><b>¹ These rooms are occupied in the same time slot as your booked meeting, but they overlap with more than one meeting.</b></span>
						<?php endif; ?>
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