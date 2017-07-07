<!-- This is the HTML form used for adding ROOMEQUIPMENT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Add Room Equipment</title>
	</head>
	<body>
		<h1>Add Room Equipment</h1>
		<?php if(isset($AddRoomEquipmentError)) :?>
			<div>
				<p><b><?php htmlout($AddRoomEquipmentError); ?> </b></p>
			</div>
		<?php endif; ?>
		<?php if(isset($_SESSION['AddRoomEquipmentSearchResult'])) :?>
				<p><b><?php htmlout($_SESSION['AddRoomEquipmentSearchResult']); ?></b></p>
			<?php unset($_SESSION['AddRoomEquipmentSearchResult']); ?>
		<?php endif; ?>			
		<form action="" method="post">
			<div>
				<label for="MeetingRoomID">Meeting Room Name:</label>
				<?php if(!isset($_GET['Meetingroom'])) : ?>
					<select name="MeetingRoomID" id="MeetingRoomID">
						<option value="">Select a Meeting Room</option>
						<?php foreach($meetingrooms as $row): ?> 
							<?php if (isset($selectedMeetingRoomID) AND $selectedMeetingRoomID == $row['MeetingRoomID']) : ?>
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
					<b><?php htmlout($meetingrooms['MeetingRoomName']); ?></b>
				<?php endif; ?>					
			</div>
			<?php if(!isset($_GET['Meetingroom'])) :?>			
				<div>
					<label for="meetingroomsearchstring">Search for Meeting Room:</label>
					<input type="text" name="meetingroomsearchstring" 
					value="<?php htmlout($meetingroomsearchstring); ?>">
				</div>
			<?php endif; ?>	
			<div>
				<label for="EquipmentID">Equipment:</label>
				<select name="EquipmentID" id="EquipmentID">
					<option value="">Select Equipment</option>
					<?php foreach($equipment as $row): ?> 
						<?php if (isset($selectedEquipmentID) AND $selectedEquipmentID == $row['EquipmentID']) : ?>
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
			</div>
			<div>
				<label for="EquipmentAmount">Select an Amount:</label>
				<input type="number" name="EquipmentAmount" min="1" max="255"
				value="<?php htmlout($EquipmentAmount); ?>">
			</div>
			<div>
				<input type="submit" name="action" value="Search">
				<input type="submit" name="action" value="Confirm Room Equipment">
			</div>
			<div>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>