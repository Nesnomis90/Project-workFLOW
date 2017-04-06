<!-- This is the HTML form used for DISPLAYING a list of ROOMEQUIPMENT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#roomequipmenttable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#roomequipmenttable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}
			
			#roomequipmenttable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#roomequipmenttable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#roomequipmenttable tr:hover{background-color:#ddd;}
			
			#roomequipmenttable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#roomequipmenttable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Equipment in Meeting Rooms</title>
	</head>
	<body>
		<h1>Manage Equipment in Meeting Rooms</h1>
		<form action="" method="post">
		<?php if($rowNum>0) :?>
			<input type="submit" name="action" value="Add Room Equipment">
			<table id= "roomequipmenttable">
				<caption>Meeting Room Equipment</caption>
				<tr>
					<th>Equipment Name</th>
					<th>Equipment Description</th>
					<th>Amount</th>
					<th>Meeting Room Name</th>
					<th>Added At</th>
					<th></th>
					<th></th>
				</tr>
				<?php foreach ($roomequipment as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['EquipmentName']); ?></td>
							<td><?php htmlout($row['EquipmentDescription']); ?></td>
							<td><?php htmlout($row['EquipmentAmount']); ?></td>
							<td><?php htmlout($row['MeetingRoomName']); ?></td>	
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><input type="submit" name="action" value="Change Amount"></td>
							<td><input type="submit" name="action" value="Remove"></td>
							<input type="hidden" name="EquipmentID" value="<?php echo $row['TheEquipmentID']; ?>">
							<input type="hidden" name="MeetingRoomID" value="<?php echo $row['MeetingRoomID']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no equipment for any meeting rooms registered in the database.</b></tr>
			<tr><input type="submit" name="action" value="Add Room Equipment"></tr>
		<?php endif; ?>
		</form>
		<p><a href="..">Return to CMS home</a></p>
	</body>
</html>
