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
				width: 200px;
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
					<label>Booked Meeting Room: </label>
					<span><b><?php htmlout($originalMeetingRoomName); ?></b></span>
				</div>

				<div>
					<label>Select Available Room:</label>
					<select name="availableRooms">
						<option value="">Select A Room</option>
					</select>
				</div>

				<div>
					<label>Swap Occupied Room:</label><span>*</span>
					<select name="occupiedRooms">
						<option value="">Select A Room</option>
					</select>
					<span><b>* This requires confirmation from the owner of the booked meeting room, or someone with the appropriate access rights.</b></span>
				</div>

				<div class="left">
					<input type="hidden" name="bookingID" value="<?php htmlout($bookingID); ?>">
					<input type="submit" name="changeroom" value="Go Back">
					<input type="submit" name="changeroom" value="Confirm Change">
				</div>
			</form>
		</fieldset>
	</body>
</html>