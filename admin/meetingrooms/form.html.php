<!-- This is the HTML form used for EDITING or ADDING MEETING ROOM information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<?php if(isset($_SESSION['AddMeetingRoomError'])) : ?>
			<p><b><?php htmlout($_SESSION['AddMeetingRoomError']); ?></b></p>
			<?php unset($_SESSION['AddMeetingRoomError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="MeetingRoomName">Room Name: 
					<input type="text" name="MeetingRoomName" id="MeetingRoomName"
					placeholder="Enter Room Name" 
					value="<?php htmlout($meetingRoomName); ?>">
				</label>
			</div>
			<div>
				<label for="MeetingRoomCapacity">Capacity: 
					<input type="number" name="MeetingRoomCapacity" id="MeetingRoomCapacity"
					min="1" max="255"				
					value="<?php htmlout($meetingRoomCapacity); ?>">
				</label>
			</div>
			<div>
				<label for="MeetingRoomDescription">Room Description: 
					<input type="text" name="MeetingRoomDescription" id="MeetingRoomDescription" 
					placeholder="Enter Room Description" 
					value="<?php htmlout($meetingRoomDescription); ?>">
				</label>
			</div>
			<div>
				<label for="MeetingRoomLocation">Location: 
					<input type="text" name="MeetingRoomLocation" id="MeetingRoomLocation" 
					placeholder="Enter Location" 
					value="<?php htmlout($meetingRoomLocation); ?>">
				</label>
			</div>
			<div>
				<input type="hidden" name="MeetingRoomID" value="<?php htmlout($meetingRoomID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
				<input type="submit" name="action" value="Cancel">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>