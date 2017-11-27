<!-- This is the HTML form used for DISPLAYING a list of EQUIPMENT-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Meeting Room Equipment</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Meeting Room Equipment</h1>

		<form action="" method="post">
			<div class="right">
				<?php if(isSet($_SESSION['equipmentEnableDelete']) AND $_SESSION['equipmentEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>		
		
		<div class="left">
			<?php if(isSet($_SESSION['EquipmentUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['EquipmentUserFeedback']); ?></b></span>
				<?php unset($_SESSION['EquipmentUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table class="myTable">
			<caption>Available Equipment</caption>
			<tr>
				<th colspan="3">Equipment</th>
				<th>Date</th>
				<th colspan="2">Alter Equipment</th>
			</tr>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Used In Room</th>
				<th>Added</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach ($equipment as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['EquipmentName']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['EquipmentDescription']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['EquipmentIsInTheseRooms']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isSet($_SESSION['equipmentEnableDelete']) AND $_SESSION['equipmentEnableDelete'] AND
										$row['EquipmentIsInTheseRooms'] == "") : ?>
									<input type="submit" name="action" value="Delete">
								<?php elseif(isSet($_SESSION['equipmentEnableDelete']) AND $_SESSION['equipmentEnableDelete'] AND
										$row['EquipmentIsInTheseRooms'] != "") : ?>
									<b>Not enabled due to it being used in a room.</b>								
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" id="EquipmentName" name="EquipmentName"
							value="<?php htmlout($row['EquipmentName']); ?>">
							<input type="hidden" name="EquipmentID" value="<?php htmlout($row['TheEquipmentID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><b>There are no equipment registered in the database.</b></tr>
			<?php endif; ?>

			<form action="" method="post">
				<tr>
					<td colspan="6">
						<input type="hidden" name="action" value="Add Equipment">
						<input type="submit" style="font-size: 150%; color: green;" value="+">
					</td>
				</tr>
			</form>

			<tr><th colspan="7"></th></tr>
		</table>
	</body>
</html>
