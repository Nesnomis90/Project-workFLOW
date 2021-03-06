<!--This is the HTML form for DISPLAYING a list of MEETING ROOMS -->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Meeting Rooms</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Meeting Rooms</h1>

		<form method="post">
			<div class="right">
				<?php if(isSet($_SESSION['meetingroomsEnableDelete']) AND $_SESSION['meetingroomsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
					<?php if(isSet($_SESSION['meetingroomsEnableDeleteUsedMeetingRoom']) AND $_SESSION['meetingroomsEnableDeleteUsedMeetingRoom']) : ?>
						<input type="submit" name="action" value="Disable Delete Used Meeting Room">
					<?php else : ?>
						<input type="submit" name="action" value="Enable Delete Used Meeting Room">
					<?php endif; ?>
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>

		<?php if(isSet($_SESSION['MeetingRoomUserFeedback'])) : ?>
			<div class="left">
				<span><b class="feedback"><?php htmlout($_SESSION['MeetingRoomUserFeedback']); ?></b></span>
				<?php unset($_SESSION['MeetingRoomUserFeedback']); ?>
			</div>
		<?php endif; ?>

		<table class="myTable">
			<caption>Current Meeting Rooms</caption>
			<tr>
				<th colspan="2">Equipment In Room</th>
				<th colspan="4">Booking Information</th>
				<th colspan="4">Meeting Room</th>
				<th colspan="2">Alter Room</th>
			</tr>
			<tr>
				<th>List</th>
				<th>Amount</th>
				<th>List</th>
				<th>Booked</th>
				<th>Completed</th>
				<th>Cancelled</th>
				<th>Name</th>
				<th>Capacity</th>
				<th>Description</th>
				<th>Location</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
			<?php if($rowNum>0) :?>
				<?php foreach ($meetingrooms as $room): ?>
					<tr>
						<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $room['MeetingRoomID'];?>
						<form action="<?php htmlout($goto) ;?>" method="post">
							<td><input type="submit" value="Equipment"></td>
						</form>
							<td><?php htmlout($room['MeetingRoomEquipmentAmount']); ?></td>
						<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/bookings/?Meetingroom=" . $room['MeetingRoomID'];?>
						<form action="<?php htmlout($goto) ;?>" method="post">
							<td><input type="submit" value="Bookings"></td>
						</form>	
							<td><?php htmlout($room['MeetingRoomActiveBookings']); ?></td>
							<td><?php htmlout($room['MeetingRoomCompletedBookings']); ?></td>
							<td><?php htmlout($room['MeetingRoomCancelledBookings']); ?></td>
							<td><?php htmlout($room['MeetingRoomName']); ?></td>
							<td><?php htmlout($room['MeetingRoomCapacity']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($room['MeetingRoomDescription']); ?></td>
							<td><?php htmlout($room['MeetingRoomLocation']); ?></td>
						<form method="post">
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isSet($_SESSION['meetingroomsEnableDelete']) AND $_SESSION['meetingroomsEnableDelete'] AND
										$room['MeetingRoomActiveBookings'] == 0) : ?>
									<input type="submit" name="action" value="Delete">
								<?php elseif(isSet($_SESSION['meetingroomsEnableDelete']) AND $_SESSION['meetingroomsEnableDelete'] AND
										$room['MeetingRoomActiveBookings'] > 0) : ?>
									<?php if(isSet($_SESSION['meetingroomsEnableDeleteUsedMeetingRoom']) AND $_SESSION['meetingroomsEnableDeleteUsedMeetingRoom']) : ?>
										<input type="submit" name="action" value="Delete">
									<?php else : ?>
										<b>Not Enabled</b>
										<input type="submit" name="disabled" value="Delete" disabled>
									<?php endif; ?>
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
							value="<?php htmlout($room['MeetingRoomName']); ?>">
							<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
						</form>
					</tr>
				<?php endforeach; ?>
		<?php else : ?>
			<tr><b>There are no meeting rooms registered in the database.</b></tr>
		<?php endif; ?>

			<form method="post">
				<tr>
					<td colspan="12">
						<input type="hidden" name="action" value="Create Meeting Room">
						<input type="submit" style="font-size: 150%; color: green;" value="+">
					</td>
				</tr>
			</form>

			<tr><th colspan="12"></th></tr>
		</table>
	</body>
</html>