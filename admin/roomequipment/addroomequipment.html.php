<!-- This is the HTML form used for adding ROOMEQUIPMENT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Add Room Equipment</title>
		<style>
			label {
				width: 180px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Add Room Equipment</legend>
			<div class="left">
				<?php if(isSet($AddRoomEquipmentError)) : ?>
					<span><b class="feedback"><?php htmlout($AddRoomEquipmentError); ?> </b></span>
				<?php endif; ?>
			</div>

			<div class="left">
				<?php if(isSet($_SESSION['AddRoomEquipmentSearchResult'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddRoomEquipmentSearchResult']); ?></b></span>
					<?php unset($_SESSION['AddRoomEquipmentSearchResult']); ?>
				<?php endif; ?>	
			</div>

			<form action="" method="post">
				<div>
					<label for="MeetingRoomID">Meeting Room Name:</label>
					<?php if(!isSet($_GET['Meetingroom'])) : ?>
						<select name="MeetingRoomID" id="MeetingRoomID">
							<option value="">Select a Meeting Room</option>
							<?php foreach($meetingrooms as $row): ?> 
								<?php if (isSet($selectedMeetingRoomID) AND $selectedMeetingRoomID == $row['MeetingRoomID']) : ?>
									<option selected="selected" value="<?php htmlout($row['MeetingRoomID']); ?>">
											<?php htmlout($row['MeetingRoomName']);?>
									</option>
								<?php else : ?>
									<option value="<?php htmlout($row['MeetingRoomID']); ?>">
											<?php htmlout($row['MeetingRoomName']);?>
									</option>
								<?php endif; ?>	
							<?php endforeach; ?>
						</select>
					<?php else :?>
						<span><b><?php htmlout($meetingrooms['MeetingRoomName']); ?></b></span>
					<?php endif; ?>
				</div>

				<?php if(!isSet($_GET['Meetingroom'])) :?>
					<div>
						<label for="meetingroomsearchstring">Search for Meeting Room:</label>
						<input type="text" name="meetingroomsearchstring" 
						value="<?php htmlout($meetingroomsearchstring); ?>">
						<input type="submit" name="action" value="Search">
					</div>
				<?php endif; ?>	

				<div>
					<label for="EquipmentID">Equipment:</label>
					<select name="EquipmentID" id="EquipmentID">
						<option value="">Select Equipment</option>
						<?php foreach($equipment as $row): ?> 
							<?php if (isSet($selectedEquipmentID) AND $selectedEquipmentID == $row['EquipmentID']) : ?>
								<option selected="selected" value="<?php htmlout($row['EquipmentID']); ?>">
										<?php htmlout($row['EquipmentName']);?>
								</option>
							<?php else : ?>
								<option value="<?php htmlout($row['EquipmentID']); ?>">
										<?php htmlout($row['EquipmentName']);?>
								</option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>

				<div>
					<label for="equipmentsearchstring">Search for Equipment:</label>
					<input type="text" name="equipmentsearchstring" 
					value="<?php htmlout($equipmentsearchstring); ?>">
					<input type="submit" name="action" value="Search">
				</div>

				<div>
					<label for="EquipmentAmount">Select an Amount:</label>
					<input type="number" name="EquipmentAmount" min="1" max="255"
					value="<?php htmlout($EquipmentAmount); ?>">
				</div>

				<div class="left">
					<input type="submit" name="action" value="Confirm Room Equipment">
					<input type="submit" name="add" value="Reset">
					<input type="submit" name="add" value="Cancel">
				</div>
			</form>
		</fieldset>
	</body>
</html>