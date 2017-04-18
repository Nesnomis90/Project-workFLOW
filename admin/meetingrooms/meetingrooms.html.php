<!--This is the HTML form for DISPLAYING a list of MEETING ROOMS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#meetingroomstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}

			#meetingroomstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#meetingroomstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}

			#meetingroomstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}			
			
			#meetingroomstable tr:hover{background-color:#ddd;}
			
			#meetingroomstable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#meetingroomstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Meeting Rooms</title>
	</head>
	<body>
		<h1>Manage Meeting Rooms</h1>
		<?php if($rowNum>0) :?>
			<p><a href="?add">Add new meeting room</a></p>
			<table id= "meetingroomstable">
				<caption>Current Meeting Rooms</caption>
				<tr>
					<th>Equipment List</th>
					<th># of Equipment</th>
					<th>Room Name</th>
					<th>Capacity</th>
					<th>Room Description</th>
					<th>Location</th>
					<th>Edit Room</th>
					<th>Delete Room</th>
				</tr>
				<?php foreach ($meetingrooms as $room): ?>
					<tr>
						<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $room['id'];?>
						<form action="<?php htmlout($goto) ;?>" method="post">
							<td>
								<input type="submit" value="Equipment">
								<input type="hidden" name="Meetingroom" value="<?php htmlout($room['id']); ?>">							
							</td>
							<td><?php htmlout($room['MeetingRoomEquipmentAmount']); ?></td>
						</form>
						<form action="" method="post">
							<td><?php htmlout($room['name']); ?></td>
							<td><?php htmlout($room['capacity']); ?></td>
							<td><?php htmlout($room['description']); ?></td>
							<td><?php htmlout($room['location']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="id" value="<?php echo $room['id']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no meeting rooms registered in the database.</b></tr>
			<tr><a href="?add">Create a meeting room!</a></tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>