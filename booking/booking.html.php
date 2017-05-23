<!-- This is the HTML form used to display booking information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Booking Information</title>
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
	</head>
	<body>
	<h1>Booking Information Overview</h1>
	<?php if(isset($_SESSION['normalBookingFeedback'])) : ?>
		<p><b><?php htmlout($_SESSION['normalBookingFeedback']); ?></b></p>
		<?php unset($_SESSION['normalBookingFeedback']); ?>
	<?php endif; ?>
	<?php if(isset($_GET['cancellationcode'])) : ?>
		<h1>Cancel Your Booking!</h1>
	<?php elseif(isset($_SESSION['loggedIn'])) : ?>
		<form action="" method="post">		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div>
					<input type="submit" name="action" value="Create Booking">
				</div>		
			</form>
			<table id="bookingstable">
				<caption>Bookings Today</caption>
				<tr>
					<th colspan="8">Booking information</th>
					<th colspan="2">Alter Booking</th>
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
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if(	$booking['BookingStatus'] == "Active Today" OR 
								$booking['BookingStatus'] == "Completed Today"): ?>					
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td>
								<?php if($booking['BookingStatus'] == "Completed Today") : ?>
									<?php htmlout($booking['BookingWasCompletedOn']); ?>
								<?php else : ?>
									<?php htmlout($booking['EndTime']); ?>
								<?php endif; ?>
							</td>
							<td><?php htmlout($booking['BookedBy']); ?></td>
							<td><?php htmlout($booking['BookedForCompany']); ?></td>
							<td><?php htmlout($booking['BookingDescription']); ?></td>
							<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
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
					<?php endif; ?>
					</form>
				<?php endforeach; ?>
			</table>
			<table id="bookingstable">
				<caption>Future Bookings</caption>
				<tr>
					<th colspan="8">Booking information</th>
					<th colspan="2">Alter Booking</th>
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
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if($booking['BookingStatus'] == "Active"): ?>					
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td><?php htmlout($booking['EndTime']); ?></td>
							<td><?php htmlout($booking['BookedBy']); ?></td>
							<td><?php htmlout($booking['BookedForCompany']); ?></td>
							<td><?php htmlout($booking['BookingDescription']); ?></td>
							<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
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
					<?php endif; ?>
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
	<?php elseif(!isset($_SESSION['loggedIn'])) : ?>
		<form action="" method="post">		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div>
					<input type="submit" name="action" value="Create Booking">
				</div>		
			</form>
			<table id="bookingstable">
				<caption>All booking history</caption>
				<tr>
					<th colspan="4">Booking information</th>
					<th colspan="2">Alter Booking</th>
				</tr>				
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if(	$booking['BookingStatus'] == "Active Today" OR 
								$booking['BookingStatus'] == "Completed Today"): ?>
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td>
								<?php if($booking['BookingStatus'] == "Completed Today") : ?>
									<?php htmlout($booking['BookingWasCompletedOn']); ?>
								<?php else : ?>
									<?php htmlout($booking['EndTime']); ?>
								<?php endif; ?>
							</td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
							<input type="hidden" name="MeetingInfo" id="MeetingInfo"
							value="<?php htmlout($booking['MeetingInfo']); ?>">
							<input type="hidden" name="BookingStatus" id="BookingStatus"
							value="<?php htmlout($booking['BookingStatus']); ?>">
						</tr>
					<?php endif; ?>
					</form>
				<?php endforeach; ?>
			</table>
			<table id="bookingstable">
				<caption>Future Bookings</caption>
				<tr>
					<th colspan="4">Booking information</th>
					<th colspan="2">Alter Booking</th>
				</tr>				
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if($booking['BookingStatus'] == "Active"): ?>					
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td><?php htmlout($booking['EndTime']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
							<input type="hidden" name="MeetingInfo" id="MeetingInfo"
							value="<?php htmlout($booking['MeetingInfo']); ?>">
							<input type="hidden" name="BookingStatus" id="BookingStatus"
							value="<?php htmlout($booking['BookingStatus']); ?>">
						</tr>
					<?php endif; ?>
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
	<?php endif; ?>
	<?php if(isset($_SESSION['loggedIn'])) : ?>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	<?php endif; ?>
	</body>
</html>