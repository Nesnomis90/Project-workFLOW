<!--This is the HTML form for DISPLAYING a list of MEETING ROOMS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Select A Meeting Room</title>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<?php if($rowNum>0) :?>
			<table>
				<caption>Active Meeting Rooms</caption>
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
	</body>
</html>