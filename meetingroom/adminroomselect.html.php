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
				border: 1px solid #ddd;
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
		<title>Select A Meeting Room</title>
	</head>
	<body>
		<h1>Select A Meeting Room</h1>	
		<?php if($rowNum>0) :?>
			<table id= "meetingroomstable">
				<caption>Current Meeting Rooms</caption>
				<tr>
					<th colspan="3">Meeting Room</th>
					<th>Select A Room</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Capacity</th>
					<th>Description</th>
					<th>Set As Default</th>
				</tr>
				<?php foreach ($meetingrooms as $room): ?>
					<tr>
						<form action="" method="post">
							<td>
								<?php htmlout($room['MeetingRoomName']); ?>
								<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
								value="<?php htmlout($room['MeetingRoomName']); ?>">
							</td>
							<td><?php htmlout($room['MeetingRoomCapacity']); ?></td>
							<td><?php htmlout($room['MeetingRoomDescription']); ?></td>
							<td><input type="submit" name="action" value="Set As Default"></td>
							<input type="hidden" name="MeetingRoomID" value="<?php htmlout($room['MeetingRoomID']); ?>">
							<input type="hidden" name="MeetingRoomIDCode" value="<?php htmlout($room['MeetingRoomIDCode']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no meeting rooms registered in the database.</b></tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>