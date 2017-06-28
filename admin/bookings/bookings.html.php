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
			
			#bookingstable tr:nth-of-type(even) {background-color: #f2f2f2;}
			#bookingstable tr:nth-of-type(odd) {background-color: white;}			
			#bookingstable tr:hover{background-color:#DBEAE8;}
			
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
			
			#bookingstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Booked Meetings</title>
	</head>
	<body>
		<?php if(isset($_GET['Meetingroom']) AND isset($displayRoomNameForTitle)) : ?>
			<h1>Manage Booked Meetings(Room: <?php htmlout($displayRoomNameForTitle); ?>)</h1>
		<?php elseif(isset($_GET['Meetingroom']) AND !isset($displayRoomNameForTitle)) : ?>
			<h1>This Meeting Room Has No Registered Meetings</h1>
		<?php else : ?>
			<h1>Manage All Booked Meetings</h1>
		<?php endif; ?>
		<?php if(isset($_SESSION['BookingUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['BookingUserFeedback']); ?></b></p>
			<?php unset($_SESSION['BookingUserFeedback']); ?>
		<?php endif; ?>			
		<?php if(isset($_GET['Meetingroom'])) : ?>
			<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/meetingrooms"; ?>	
			<div>
				<form action="<?php htmlout($goto); ?>" method="post">
					<input type="submit" value="Return To Meeting Rooms">
				</form>
			</div>		
			<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/bookings"; ?>	
			<div>
				<form action="<?php htmlout($goto); ?>" method="post">
					<input type="submit" value="Get Bookings For All Rooms">
				</form>
			</div>		
		<?php endif; ?>
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
		<table id="bookingstable">
			<caption>Active Bookings Today</caption>
			<tr>
				<th colspan="8">Booking information</th>
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
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($bookingsActiveToday)) : ?>						
			<?php foreach ($bookingsActiveToday AS $booking) : ?>
				<form action="" method="post">				
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>
		</table>
		<table id="bookingstable">
			<caption>Completed Bookings Today</caption>
			<tr>
				<th colspan="8">Booking information</th>
				<th colspan="3">Completion Info</th>
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
				<th>Finished At</th>
				<th>Actual Duration</th>
				<th>Price Duration</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($bookingsCompletedToday)) : ?>						
			<?php foreach ($bookingsCompletedToday AS $booking) : ?>
				<form action="" method="post">				
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>
		</table>		
		<table id="bookingstable">
			<caption>Future Bookings</caption>
			<tr>
				<th colspan="8">Booking information</th>
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
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>	
		<?php if(isset($bookingsFuture)) : ?>						
			<?php foreach ($bookingsFuture AS $booking) : ?>
				<form action="" method="post">				
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>
		</table>
		<table id="bookingstable">
			<caption>Completed Bookings</caption>
			<tr>
				<th colspan="8">Booking information</th>
				<th colspan="3">Completion Info</th>
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
				<th>Finished At</th>
				<th>Actual Duration</th>
				<th>Price Duration</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>	
		<?php if(isset($bookingsCompleted)) : ?>						
			<?php foreach ($bookingsCompleted AS $booking) : ?>
				<form action="" method="post">				
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>
		</table>		
		<table id="bookingstable">
			<caption>Bookings Cancelled</caption>
			<tr>
				<th colspan="8">Booking information</th>
				<th>Completion Date</th>
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
				<th>Cancelled</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($bookingsCancelled)) : ?>				
			<?php foreach ($bookingsCancelled AS $booking) : ?>
				<form action="" method="post">			
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>		
		</table>
	<?php if(isset($bookingsOther)) : ?>		
		<table id="bookingstable">
			<caption>Other Bookings</caption>
			<tr>
				<th colspan="8">Booking information</th>
				<th colspan="2">Completion Info</th>
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
			<?php foreach ($bookingsOther AS $booking) : ?>
				<form action="" method="post">			
					<tr>
						<td><?php htmlout($booking['BookingStatus']);?></td>
						<td><?php htmlout($booking['BookedRoomName']); ?></td>
						<td><?php htmlout($booking['StartTime']); ?></td>
						<td><?php htmlout($booking['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookedBy']); ?></td>
						<td><?php htmlout($booking['BookedForCompany']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['BookingDescription']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
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
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>