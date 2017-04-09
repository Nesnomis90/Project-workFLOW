<!--This is the HTML form for DISPLAYING a list of BOOKINGS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#bookingstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}

			#bookingstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#bookingstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}

			#bookingstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}			
			
			#bookingstable tr:hover{background-color:#ddd;}
			
			#bookingstable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#bookingstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Booked Meetings</title>
	</head>
	<body>
		<h1>Manage Booked Meetings</h1>
		<?php if($rowNum>0) :?>
			<p><a href="?add">Create a new booking?</a></p>
			<table id= "bookingstable">
				<caption>All booking history</caption>
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Display Name</th>
					<th>Booked For Company</th>
					<th>Booking Description</th>
					<th>Booking Created At</th>
					<th>Time If Finished</th>
					<th>Time If Cancelled</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Works for</th>
					<th>Cancel Booking</th>
					<th>Edit Booking (Not functional)</th>
					<th>Delete Booking</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td><?php htmlout($booking['EndTime']); ?></td>
							<td><?php htmlout($booking['BookedBy']); ?></td>
							<td><?php htmlout($booking['BookedForCompany']); ?></td>
							<td><?php htmlout($booking['BookingDescription']); ?></td>
							<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
							<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
							<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
							<td><?php htmlout($booking['firstName']); ?></td>
							<td><?php htmlout($booking['lastName']); ?></td>
							<td><?php htmlout($booking['email']); ?></td>
							<td><?php htmlout($booking['WorksForCompany']); ?></td>
							<td><input type="submit" name="action" value="Cancel"></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no booked meetings registered in the database.</b></tr>
			<tr><a href="?add">Book a meeting?</a></tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>