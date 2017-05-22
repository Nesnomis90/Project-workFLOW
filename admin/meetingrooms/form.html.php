<!-- This is the HTML form used for EDITING or ADDING MEETING ROOM information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#MeetingRoomDescription {
				vertical-align: top;
			}
		</style>
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<?php if(isset($_SESSION['AddMeetingRoomError'])) : ?>
			<p><b><?php htmlout($_SESSION['AddMeetingRoomError']); ?></b></p>
			<?php unset($_SESSION['AddMeetingRoomError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<?php if($button == 'Edit Room') : ?>
			<div>
				<label for="OriginalMeetingRoomName">Original Meeting Room Name: </label>
				<b><?php htmlout($originalMeetingRoomName); ?></b>
			</div>
			<?php endif; ?>
			<div>
				<label for="MeetingRoomName">Set New Room Name: </label>
				<input type="text" name="MeetingRoomName" id="MeetingRoomName"
				placeholder="Enter Room Name" 
				value="<?php htmlout($meetingRoomName); ?>">
			</div>
			<?php if($button == 'Edit Room') : ?>
			<div>
				<label for="OriginalMeetingRoomCapacity">Original Meeting Room Capacity: </label>
				<b><?php htmlout($originalMeetingRoomCapacity); ?></b>
			</div>
			<?php endif; ?>			
			<div>
				<label for="MeetingRoomCapacity">Set New Capacity: </label>
				<input type="number" name="MeetingRoomCapacity" id="MeetingRoomCapacity"
				min="1" max="255"				
				value="<?php htmlout($meetingRoomCapacity); ?>">
			</div>
			<?php if($button == 'Edit Room') : ?>
			<div>
				<label for="OriginalMeetingRoomDescription">Original Meeting Room Description: </label>
				<b><?php htmlout($originalMeetingRoomDescription); ?></b>
			</div>
			<?php endif; ?>			
			<div>
				<label for="MeetingRoomDescription">Set New Room Description: </label>
				<textarea rows="4" cols="50" name="MeetingRoomDescription" id="MeetingRoomDescription"
				placeholder="Enter Room Description"><?php htmlout($meetingRoomDescription); ?></textarea>
			</div>	
			<?php if($button == 'Edit Room') : ?>
			<div>
				<label for="OriginalMeetingRoomLocation">Original Meeting Room Location: </label>
				<b><?php htmlout($originalMeetingRoomLocation); ?></b>
			</div>
			<?php endif; ?>			
			<div>
				<label for="MeetingRoomLocation">Set New Location: </label> 
				<input type="text" name="MeetingRoomLocation" id="MeetingRoomLocation" 
				placeholder="Enter Location" 
				value="<?php htmlout($meetingRoomLocation); ?>">
			</div>
			<div>
				<input type="hidden" name="MeetingRoomID" value="<?php htmlout($meetingRoomID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>
			<div>
			<?php if($button == 'Edit Room') : ?>
				<input type="submit" name="edit" value="Reset">
				<input type="submit" name="edit" value="Cancel">
			<?php elseif($button == 'Add Room') : ?>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">			
			<?php endif; ?>
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>