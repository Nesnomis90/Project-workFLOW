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
				border: 1px solid #ddd;
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
		<?php if(isset($_SESSION['BookingUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['BookingUserFeedback']); ?></b></p>
			<?php unset($_SESSION['BookingUserFeedback']); ?>
		<?php endif; ?>	
		<form action="" method="post">		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div>
					<input type="submit" name="action" value="Create Booking">
				</div>
				<div>
				<?php if(isset($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
				</div>			
			</form>
			<table id= "bookingstable">
				<caption>All booking history</caption>
				<tr>
					<th colspan="8">Booking information</th>
					<th colspan="2">Completion Dates</th>
					<th colspan="4">Connected user information</th>
					<th colspan="3">Alter Booking</th>
				</tr>				
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Display Name</th>
					<th>For Company</th>
					<th>Description</th>
					<th>Created At</th>
					<th>Finished</th>
					<th>Cancelled</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Works For Company</th>
					<th>Edit</th>					
					<th>Cancel</th>
					<th>Delete</th>
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
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<td>
								<?php if(isset($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
							<input type="hidden" name="UserInfo" id="UserInfo"
							value="<?php htmlout($booking['UserInfo']); ?>">
							<input type="hidden" name="MeetingInfo" id="MeetingInfo"
							value="<?php htmlout($booking['MeetingInfo']); ?>">
							<input type="hidden" name="BookingStatus" id="BookingStatus"
							value="<?php htmlout($booking['BookingStatus']); ?>">
							<input type="hidden" name="Email" id="Email"
							value="<?php htmlout($booking['email']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no booked meetings registered in the database.</b></tr>
			<form action="" method="post">
				<tr><input type="submit" name="action" value="Create Booking"></tr>
			</form>
		<?php endif; ?>
		</form>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>