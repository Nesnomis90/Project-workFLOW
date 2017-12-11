<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Meeting Room</title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 85px;
			}
		</style>
	</head>
	<body onload="startTime(); refreshPageTimer(<?php htmlout(SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE); ?>);">

		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<div class="left">
			<span><b>Last Refresh: <?php htmlout(getDatetimeNowInDisplayFormat()); ?></b></span>
			<form method="post">
				<?php if(isSet($_SESSION['DefaultMeetingRoomInfo'])) : ?>
				<?php $default = $_SESSION['DefaultMeetingRoomInfo']; ?>
					<?php if((!isSet($_GET['meetingroom'])) OR
							(isSet($_GET['meetingroom']) AND $_GET['meetingroom'] != $default['TheMeetingRoomID'])) : ?>
						<input type="submit" name="action" value="Show Default Room Only">
					<?php else : ?>
						<input type="submit" name="action" value="Show All Rooms">
					<?php endif; ?>
				<?php endif; ?>
			</form>
		</div>

		<?php if(isSet($_SESSION['DefaultMeetingRoomInfo']) AND !isSet($defaultMeetingRoomFeedback)) : ?>
			<div class="left">
				<form method="post">
					<label style="width: 295px;" for="defaultMeetingRoomName">The Default Meeting Room For This Device: </label>
					<span><b><?php htmlout($_SESSION['DefaultMeetingRoomInfo']['TheMeetingRoomName']); ?></b></span>
					<?php if($adminLoggedIn) : ?>
						<div class="left">
							<input type="submit" name="action" value="Change Default Room">
						</div>
					<?php endif; ?>
				</form>
			</div>
		<?php elseif($adminLoggedIn) : ?>
			<div class="left">
				<form method="post">
					<input type="submit" name="action" value="Set Default Room">
				</form>
			<div>
		<?php endif; ?>

		<?php if(isSet($_SESSION['MeetingRoomAllUsersFeedback'])) : ?>
			<div class="left"><b class="feedback"><?php htmlout($_SESSION['MeetingRoomAllUsersFeedback']); ?></b></div>
			<?php unset($_SESSION['MeetingRoomAllUsersFeedback']); ?>
		<?php endif; ?>

		<?php if(isSet($defaultMeetingRoomFeedback)) : ?>
			<div class="left"><b class="feedback"><?php htmlout($defaultMeetingRoomFeedback); ?></b></div>
		<?php endif; ?>

		<?php if(!empty($_GET['meetingroom'])) : ?>
			<?php if(isSet($meetingrooms)) : ?>
				<?php if(isSet($default) AND $_GET['meetingroom'] == $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Default Room For Device</h2></div>
				<?php elseif(isSet($default) AND $_GET['meetingroom'] != $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Non-Default Room For Device</h2></div>
				<?php elseif(!isSet($default)) : ?>
					<div class="left"><h2>Viewing Selected Meeting Room</h2></div>
				<?php endif; ?>
				<?php foreach($meetingrooms AS $room): ?>
					<div class="left">
						<form method="post">
							<?php if(substr($room['MeetingRoomStatus'],0,8) == "Occupied") : ?>
								<?php $color = "#ff3333"; // Light Red?>
							<?php elseif(substr($room['MeetingRoomStatus'],0,9) == "Available") : ?>
								<?php $color = "#33ff33"; // Light Green?>
							<?php endif; ?>
							<fieldset style="border-style: solid; border-color: <?php htmlout($color); ?>"><legend><b><?php htmlout($room['MeetingRoomName']); ?></b></legend>
								<div class="left">
									<label>Status: </label>
									<span><?php htmlout($room['MeetingRoomStatus']); ?></span>
								</div>
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
								<input type="hidden" name="MeetingRoomName" value="<?php htmlout($room['MeetingRoomName']); ?>">
								<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
							</fieldset>
						</form>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="left"><h2>This isn't a valid meeting room.</h2></div>
			<?php endif; ?>
		<?php else : ?>
			<?php if(isSet($meetingrooms)) :?>
				<div class="left"><h2>Active Meeting Rooms:</h2></div>
				<?php foreach ($meetingrooms AS $room): ?>
					<div class="left">
						<form method="post">
						<?php if(substr($room['MeetingRoomStatus'],0,8) == "Occupied") : ?>
							<?php $color = "#ff3333"; // Light Red?>
						<?php elseif(substr($room['MeetingRoomStatus'],0,9) == "Available") : ?>
							<?php $color = "#33ff33"; // Light Green?>
						<?php endif; ?>
							<fieldset style="border-style: solid; border-color: <?php htmlout($color); ?>"><legend><b><?php htmlout($room['MeetingRoomName']); ?></b></legend>
								<div class="left">
									<label>Status: </label>
									<span><?php htmlout($room['MeetingRoomStatus']); ?></span>
								</div>
								<div class="left">
									<label for="MeetingRoomCapacity">Capacity: </label>
									<?php htmlout($room['MeetingRoomCapacity']); ?>
								</div>
								<div class="left">
									<label for="MeetingRoomDescription">Description: </label>
									<?php htmlout($room['MeetingRoomDescription']); ?>
								</div>
								<div class="left">
									<label for="MeetingRoomLocation">Location: </label>
									<?php htmlout($room['MeetingRoomLocation']); ?>
								</div>
								<div class="left"><input type="submit" name="action" value="Booking Information"></div>
								<input type="hidden" name="MeetingRoomName" value="<?php htmlout($room['MeetingRoomName']); ?>">
								<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
							</fieldset>
						</form>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<div class="left"><h2>There are no meeting rooms.</h2></div>
			<?php endif; ?>
		<?php endif; ?>
	</body>
</html>