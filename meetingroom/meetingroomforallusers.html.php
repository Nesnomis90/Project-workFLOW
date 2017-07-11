<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" HTTP-EQUIV="refresh" CONTENT="<?php htmlout(SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE); ?>"> <!-- Refreshes every 30 sec -->
		<title>Meeting Room</title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 85px;
			}
		</style>
	</head>
	<body onload="startTime()">
	
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
	
		<?php if(isset($_SESSION['DefaultMeetingRoomInfo']) AND !isset($defaultMeetingRoomFeedback)) : ?>
			<div class="left">
				<form action="" method="post">
					<label style="width: 295px;" for="defaultMeetingRoomName">The Default Meeting Room For This Device: </label>
					<span><b><?php htmlout($_SESSION['DefaultMeetingRoomInfo']['TheMeetingRoomName']); ?></b></span>
					<div class="left">
						<input type="submit" name="action" value="Change Default Room"><span style="color:red">* Requires Admin Access</span>
					</div>
				</form>
			</div>
		<?php else : ?>
			<div class="left">
				<form action="" method="post">
					<input type="submit" name="action" 
					value="Set Default Room"><span style="color:red">* Requires Admin Access</span>
				</form>
			<div>
		<?php endif; ?>
		
		<div class="left"><h1>Meeting Room</h1></div>
		
		<div class="left">
			<form action="" method="post">
				<?php if(isset($_SESSION['DefaultMeetingRoomInfo'])) : ?>
				<?php $default = $_SESSION['DefaultMeetingRoomInfo']; ?>
					<?php if((!isset($_GET['meetingroom'])) OR
							(isset($_GET['meetingroom']) AND $_GET['meetingroom'] != $default['TheMeetingRoomID'])) : ?>
						<input type="submit" name="action" value="Select Default Room">
					<?php else : ?>
						<input type="submit" name="action" value="Show All Rooms">
					<?php endif; ?>			
				<?php endif; ?>
				<input type="submit" name="action" value="Refresh">
				<span><b>Last Refresh: <?php htmlout(getDatetimeNowInDisplayFormat()); ?></b></span>
			</form>
		</div>
		
		<?php if(isset($_SESSION['MeetingRoomAllUsersFeedback'])) : ?>
			<div class="left"><b><?php htmlout($_SESSION['MeetingRoomAllUsersFeedback']); ?></b></div>
			<?php unset($_SESSION['MeetingRoomAllUsersFeedback']); ?>
		<?php endif; ?>
		
		<?php if(isset($defaultMeetingRoomFeedback)) : ?>
			<div class="left"><b><?php htmlout($defaultMeetingRoomFeedback); ?></b></div>
		<?php endif; ?>
		
		<?php if(isset($_GET['meetingroom']) AND $_GET['meetingroom'] != NULL AND $_GET['meetingroom'] != "") : ?>
			<?php if(isset($meetingrooms)) : ?>
				<?php if(isset($default) AND $_GET['meetingroom'] == $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Default Room For Device</h2></div>
				<?php elseif(isset($default) AND $_GET['meetingroom'] != $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Non-Default Room For Device</h2></div>
				<?php elseif(!isset($default)) : ?>
					<div class="left"><h2>Viewing Selected Meeting Room</h2></div>
				<?php endif; ?>			
				<?php foreach ($meetingrooms as $room): ?>
					<form action="" method="post">
						<fieldset>
							<legend><b><?php htmlout($room['MeetingRoomName']); ?></b></legend>
							<div class="left">
								<label for="MeetingRoomCapacity">Capacity: </label>
								<span><?php htmlout($room['MeetingRoomCapacity']); ?></span>
							</div>
							<div class="left">
								<label for="MeetingRoomDescription">Description: </label>
								<span><?php htmlout($room['MeetingRoomDescription']); ?></span>
							</div>
							<div class="left">
								<label for="MeetingRoomLocation">Location: </label>
								<span><?php htmlout($room['MeetingRoomLocation']); ?></span>
							</div>
							<div class="left"><input type="submit" name="action" value="Booking Information"></div>
							<input type="hidden" name="MeetingRoomName" id="MeetingRoomName" value="<?php htmlout($room['MeetingRoomName']); ?>">
							<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
						</fieldset>
					</form>
				<?php endforeach; ?>	
			<?php else : ?>
				<div class="left"><h2>This isn't a valid meeting room.</h2></div>
			<?php endif; ?>				
		<?php elseif(!isset($_GET['meetingroom'])) : ?>
			<div class="left">
				<form action="" method="post">
					<label style="width: 160px;" for="maxRoomsToDisplay">Max Rooms Displayed: </label>
					<input type="number" name="logsToShow" min="1" max="<?php htmlout($totalMeetingRooms); ?>"
					value="<?php htmlout($roomDisplayLimit); ?>">
					<b>/<?php htmlout($totalMeetingRooms); ?></b>
					<div class="left">
						<input type="submit" name="action" value="Set New Max">
						<input type="hidden" name="oldDisplayLimit" value="<?php htmlout($maxRoomsToShow); ?>">
					</div>
				</form>
			</div>		
			<?php if(isset($meetingrooms)) :?>
				<div class="left"><h2>Available Meeting Rooms:</h2></div>
				<?php foreach ($meetingrooms as $room): ?>
					<?php if(!isset($i)){$i = 0;}; ?>
					<?php if($i < $maxRoomsToShow) : ?>
						<div class="left">
							<form action="" method="post">
								<fieldset>
									<legend><b><?php htmlout($room['MeetingRoomName']); ?></b></legend>
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
						</div>
					<?php $i++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="left"><h2>There are no meeting rooms.</h2></div>
			<?php endif; ?>
		<?php endif; ?>

		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>