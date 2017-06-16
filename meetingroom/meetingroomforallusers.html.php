<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Meeting Room</title>
		<script src="/scripts/myFunctions.js"></script>		
	</head>
	<body onload="startTime()">
	<div id="ClockPlacement">
		<b id="Clock"></b>
	</div>
		<?php if(isset($_SESSION['DefaultMeetingRoomInfo']) AND !isset($defaultMeetingRoomFeedback)) : ?>
			<div>
			<form action="" method="post">
				<label for="defaultMeetingRoomName">The Default Meeting Room For This Device: </label>
				<b><?php htmlout($_SESSION['DefaultMeetingRoomInfo']['TheMeetingRoomName']); ?></b>			
				<input type="submit" name="action" 
				value="Change Default Room"><span style="color:red">* Requires Admin Access</span>	
			</form>
			</div>
		<?php else : ?>
			<form action="" method="post">
				<input type="submit" name="action" 
				value="Set Default Room"><span style="color:red">* Requires Admin Access</span>
			</form>
		<?php endif; ?>
		<h1>Meeting Room</h1>
		<form action="" method="post">
			<?php if(isset($_SESSION['DefaultMeetingRoomInfo'])) : ?>
			<?php $default = $_SESSION['DefaultMeetingRoomInfo']; ?>
				<?php if((!isset($_GET['meetingroom'])) OR
						(isset($_GET['meetingroom']) AND $_GET['meetingroom'] == $default['TheMeetingRoomID'])) : ?>
					<input type="submit" name="action" value="Select Default Room">
				<?php else : ?>
					<input type="submit" name="action" value="Show All Rooms">
				<?php endif; ?>			
			<?php endif; ?>
			<input type="submit" name="action" value="Refresh">
			<b>Last Refresh: <?php htmlout(getDatetimeNowInDisplayFormat()); ?></b>
		</form>
		<?php if(isset($_SESSION['MeetingRoomAllUsersFeedback'])) : ?>
			<div><b><?php htmlout($_SESSION['MeetingRoomAllUsersFeedback']); ?></b></div>
			<?php unset($_SESSION['MeetingRoomAllUsersFeedback']); ?>
		<?php endif; ?>
		<?php if(isset($defaultMeetingRoomFeedback)) : ?>
			<div><b><?php htmlout($defaultMeetingRoomFeedback); ?></b></div>
		<?php endif; ?>
		<?php if(isset($_GET['meetingroom']) AND $_GET['meetingroom'] != NULL AND $_GET['meetingroom'] != "") : ?>
			<?php if(isset($meetingrooms)) : ?>
				<?php if(isset($default) AND $_GET['meetingroom'] == $default['TheMeetingRoomID']) : ?>
					<h2>Viewing Default Room For Device</h2>
				<?php elseif(isset($default) AND $_GET['meetingroom'] != $default['TheMeetingRoomID']) : ?>
					<h2>Viewing Non-Default Room For Device</h2>
				<?php elseif(!isset($default)) : ?>
					<h2>Viewing Selected Meeting Room</h2>
				<?php endif; ?>			
				<?php foreach ($meetingrooms as $room): ?>
					<form action="" method="post">
						<fieldset>
							<legend><?php htmlout($room['MeetingRoomName']); ?></legend>
							<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
							value="<?php htmlout($room['MeetingRoomName']); ?>">
							<div>
								<label for="MeetingRoomCapacity">Capacity: </label>
								<?php htmlout($room['MeetingRoomCapacity']); ?>
							</div>
							<div>
								<label for="MeetingRoomDescription">Description: </label>
								<?php htmlout($room['MeetingRoomDescription']); ?>
							</div>
							<div>
								<label for="MeetingRoomLocation">Location: </label>
								<?php htmlout($room['MeetingRoomLocation']); ?>
							</div>
							<div><input type="submit" name="action" value="Booking Information"></div>
							<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
						</fieldset>
					</form>
				<?php endforeach; ?>	
			<?php else : ?>
				<h2>This isn't a valid meeting room.</h2>
			<?php endif; ?>				
		<?php elseif(!isset($_GET['meetingroom'])) : ?>
			<div>
				<form action="" method="post">
					<label for="maxRoomsToDisplay">Max Rooms Displayed: </label>
					<input type="number" name="logsToShow" min="1" max="<?php htmlout($totalMeetingRooms); ?>"
					value="<?php htmlout($roomDisplayLimit); ?>">
					<b>/<?php htmlout($totalMeetingRooms); ?></b>
					<input type="submit" name="action" value="Set New Max">
					<input type="hidden" name="oldDisplayLimit" value="<?php htmlout($maxRoomsToShow); ?>">
				</form>
			</div>		
			<?php if(isset($meetingrooms)) :?>
				<h2>Available Meeting Rooms:</h2>
				<?php foreach ($meetingrooms as $room): ?>
					<?php if(!isset($i)){$i = 0;}; ?>
					<?php if($i < $maxRoomsToShow) : ?>
						<form action="" method="post">
							<fieldset>
								<legend><?php htmlout($room['MeetingRoomName']); ?></legend>
								<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
								value="<?php htmlout($room['MeetingRoomName']); ?>">
								<div>
									<label for="MeetingRoomCapacity">Capacity: </label>
									<?php htmlout($room['MeetingRoomCapacity']); ?>
								</div>
								<div>
									<label for="MeetingRoomDescription">Description: </label>
									<?php htmlout($room['MeetingRoomDescription']); ?>
								</div>
								<div>
									<label for="MeetingRoomLocation">Location: </label>
									<?php htmlout($room['MeetingRoomLocation']); ?>
								</div>
								<div><input type="submit" name="action" value="Book This Room"></div>
								<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
							</fieldset>
						</form>
					<?php $i++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<h2>There are no meeting rooms.</h2>
			<?php endif; ?>
		<?php endif; ?>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>