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
		<title>Manage Meeting Rooms</title>
	</head>
	<body>
		<h1>Manage Meeting Rooms</h1>
		<?php if(isset($_SESSION['MeetingRoomUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['MeetingRoomUserFeedback']); ?></b></p>
			<?php unset($_SESSION['MeetingRoomUserFeedback']); ?>
		<?php endif; ?>	
		<form action="" method="post">
			<div>
				<?php if(isset($_SESSION['meetingroomsEnableDelete']) AND $_SESSION['meetingroomsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>		
		<?php if($rowNum>0) :?>
			<form action="?add" method="post">
				<div>
					<input type="submit" name="action" value="Create Meeting Room">
				</div>
			</form>
			<table id= "meetingroomstable">
				<caption>Current Meeting Rooms</caption>
				<tr>
					<th colspan="2">Equipment In Room</th>
					<th colspan="4">Meeting Room</th>
					<th colspan="2">Alter Room</th>
				</tr>
				<tr>
					<th>List</th>
					<th>Amount</th>
					<th>Name</th>
					<th>Capacity</th>
					<th>Description</th>
					<th>Location</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php foreach ($meetingrooms as $room): ?>
					<tr>
						<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/roomequipment/?Meetingroom=" . $room['MeetingRoomID'];?>
						<form action="<?php htmlout($goto) ;?>" method="post">
							<td>
								<input type="submit" value="Equipment">
								<?php // test if needed <input type="hidden" name="Meetingroom" value="<?php htmlout($room['MeetingRoomID']); //"> after php end?>				
							</td>
							<td><?php htmlout($room['MeetingRoomEquipmentAmount']); ?></td>
						</form>
						<form action="" method="post">
							<td>
								<?php htmlout($room['MeetingRoomName']); ?>
								<input type="hidden" name="MeetingRoomName" id="MeetingRoomName"
								value="<?php htmlout($room['MeetingRoomName']); ?>">
							</td>
							<td><?php htmlout($room['MeetingRoomCapacity']); ?></td>
							<td><?php htmlout($room['MeetingRoomDescription']); ?></td>
							<td><?php htmlout($room['MeetingRoomLocation']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isset($_SESSION['meetingroomsEnableDelete']) AND $_SESSION['meetingroomsEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="MeetingRoomID" value="<?php echo $room['MeetingRoomID']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no meeting rooms registered in the database.</b></tr>
			<tr>			
				<form action="?add" method="post">
					<div>
						<input type="submit" name="action" value="Create Meeting Room">
					</div>
				</form>
			</tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>