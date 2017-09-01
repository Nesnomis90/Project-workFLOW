<!-- This is the HTML form used for EDITING or ADDING MEETING ROOM information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title><?php htmlout($pageTitle); ?></title>
		<style>
			label {
				width: 190px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend><?php htmlout($pageTitle); ?></legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddMeetingRoomError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddMeetingRoomError']); ?></b></span>
					<?php unset($_SESSION['AddMeetingRoomError']); ?>
				<?php endif; ?>
			</div>
			
			<form action="" method="post">
				<?php if($button == 'Edit Room') : ?>
					<div>
						<label for="OriginalMeetingRoomName">Original Room Name: </label>
						<span><b><?php htmlout($originalMeetingRoomName); ?></b></span>
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
						<label for="OriginalMeetingRoomCapacity">Original Room Capacity: </label>
						<span><b><?php htmlout($originalMeetingRoomCapacity); ?></b></span>
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
						<label for="OriginalMeetingRoomDescription">Original Room Description: </label>
						<span><b style="white-space: pre-wrap;"><?php htmlout($originalMeetingRoomDescription); ?></b></span>
					</div>
				<?php endif; ?>
				
				<div>
					<label class="description" for="MeetingRoomDescription">Set New Room Description: </label>
					<textarea rows="4" cols="50" name="MeetingRoomDescription" id="MeetingRoomDescription"
					placeholder="Enter Room Description" style="white-space: pre-wrap;"><?php htmlout($meetingRoomDescription); ?></textarea>
				</div>
				
				<?php if($button == 'Edit Room') : ?>
					<div>
						<label for="OriginalMeetingRoomLocation">Original Room Location: </label>
						<?php if($originalMeetingRoomLocation == "") : ?>
							<span><b>This room has no location set.</b></span>
						<?php else : ?>
							<span><b><?php htmlout($originalMeetingRoomLocation); ?></b></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				
				<div>
					<label for="MeetingRoomLocation">Set New Location: </label> 
					<input type="text" name="MeetingRoomLocation" id="MeetingRoomLocation" 
					placeholder="Enter Location" 
					value="<?php htmlout($meetingRoomLocation); ?>">
				</div>
				
				<div class="left">
					<input type="hidden" name="MeetingRoomID" value="<?php htmlout($meetingRoomID); ?>">
					<input type="submit" name="action" value="<?php htmlout($button); ?>">
					<?php if($button == 'Edit Room') : ?>
						<input type="submit" name="edit" value="Reset">
						<input type="submit" name="edit" value="Cancel">
					<?php elseif($button == 'Add Room') : ?>
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">			
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>