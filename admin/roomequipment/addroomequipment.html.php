<!-- This is the HTML form used for adding ROOMEQUIPMENT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Add Room Equipment</title>
	</head>
	<body>
		<h1>Add Room Equipment</h1>
		<form action="" method="post">
			<div>
				<label for="MeetingRoomID">Meeting Room Name:</label>
				<select name="MeetingRoomID" id="MeetingRoomID">
					<option value="">Select a Meeting Room</option>
					<?php foreach($meetingrooms as $row): ?> 
						<option value=<?php htmlout($row['MeetingRoomID']); ?>>
								<?php htmlout($row['MeetingRoomName']);?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label for="meetingroomsearchstring">Search for Meeting Room:</label>
				<input type="text" name="meetingroomsearchstring" 
				value=<?php htmlout($meetingroomsearchstring); ?>>
			</div>
			<div>
				<label for="EquipmentID">Equipment:</label>
				<select name="EquipmentID" id="EquipmentID">
					<option value="">Select Equipment:</option>
					<?php foreach($equipment as $row): ?> 
						<option value=<?php htmlout($row['EquipmentID']); ?>>
								<?php htmlout($row['EquipmentName']);?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label for="equipmentsearchstring">Search for Equipment:</label>
				<input type="text" name="equipmentsearchstring" 
				value=<?php htmlout($equipmentsearchstring); ?>>
			</div>
			<div>
				<label for="EquipmentAmount">Select an Amount:</label>
				<input type="number" name="EquipmentAmount" min="1" max="255"
				value="<?php htmlout($EquipmentAmount); ?>">
			</div>
			<div>
				<input type="submit" name="action" value="Search">
				<input type="submit" name="action" value="Confirm Room Equipment">
				<input type="submit" name="action" value="Cancel">
				<input type="hidden" name="meetingroomsearch" id="meetingroomsearch"
				value="<?php htmlout($meetingroomsearchstring) ;?>">
				<input type="hidden" name="equipmentsearch" id="equipmentsearch"
				value="<?php htmlout($equipmentsearchstring) ;?>">
			</div>
			<div>
				<input type="reset">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>