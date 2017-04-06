<!-- This is the HTML form used for CHANGING ROOMEQUIPMENT AMOUNT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Change Meeting Room Equipment Amount</title>
	</head>
	<body>
		<h1>Change Meeting Room Equipment Amount</h1>
		<form action="" method="post">
			<div>
				<label for="EquipmentName">Equipment Name:</label>
				<b id="EquipmentName"><?php htmlout($EquipmentName); ?></b>	
			</div>
			<div>				
				<label for="MeetingRoomName">Meeting Room Name:</label>
				<b id="MeetingRoomName"><?php htmlout($MeetingRoomName); ?></b>
			</div>
			<div>
				<label for="CurrentEquipmentAmount">Current Amount:</label>
				<b id="CurrentEquipmentAmount"><?php htmlout($CurrentEquipmentAmount); ?></b>
			</div>
			<div>
				<label for="EquipmentAmount">Set Amount:</label>
				<input type="number" name="EquipmentAmount" 
				min="1" max="255" value="<?php htmlout($EquipmentAmount); ?>">
			</div>
			<div>
				<input type="hidden" name="EquipmentID" value="<?php htmlout($EquipmentID); ?>">
				<input type="hidden" name="MeetingRoomID" value="<?php htmlout($MeetingRoomID); ?>">
				<input type="submit" name="action" value="Confirm Amount">
				<input type="submit" name="action" value="Cancel">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>