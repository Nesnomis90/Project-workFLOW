<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Meeting Room</title>
	</head>
	<body>
		<h1>Meeting Room</h1>
		<?php if(isset($_SESSION['MeetingRoomAllUsersFeedback'])) : ?>
			<div><b><?php htmlout($_SESSION['MeetingRoomAllUsersFeedback']); ?></b></div>
			<?php unset($_SESSION['MeetingRoomAllUsersFeedback']); ?>
		<?php endif; ?>
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
			<?php foreach ($meetingrooms as $room): ?>
				<?php if(!isset($i)){$i = 0;}; ?>
				<?php if($i < $maxRoomsToShow) : ?>
					<form action="" method="post">
						<div>
							<div>
								<label for="MeetingRoomName">Room Name: </label>
								<?php htmlout($room['MeetingRoomName']); ?>
								<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
								value="<?php htmlout($room['MeetingRoomName']); ?>">
							</div>
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
							<div><input type="submit" name="action" value="Create Meeting"></div>
							<input type="hidden" name="MeetingRoomID" value="<?php echo $room['MeetingRoomID']; ?>">
						</div>
					</form>
				<?php $i++; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php else : ?>
			<div><b>There are no meeting rooms.</b></div>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>