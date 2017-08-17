<!--This is the HTML form for DISPLAYING a list of BOOKINGS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Booked Meetings</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<?php if(isSet($_GET['Meetingroom']) AND isSet($displayRoomNameForTitle)) : ?>
			<h1>Manage Booked Meetings(Room: <?php htmlout($displayRoomNameForTitle); ?>)</h1>
		<?php elseif(isSet($_GET['Meetingroom']) AND !isSet($displayRoomNameForTitle)) : ?>
			<h1>This Meeting Room Has No Registered Meetings</h1>
		<?php else : ?>
			<h1>Manage All Booked Meetings</h1>
		<?php endif; ?>
		
		<div>
			<?php if(isSet($_SESSION['BookingUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['BookingUserFeedback']); ?></b></span>
				<?php unset($_SESSION['BookingUserFeedback']); ?>
			<?php endif; ?>
		</div>
		
		<?php if(isSet($_GET['Meetingroom'])) : ?>
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
			<div class="left">
				<input type="submit" name="action" value="Create Booking">
			</div>
			<div class="right">
			<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
				<input type="submit" name="action" value="Disable Delete">
			<?php else : ?>
				<input type="submit" name="action" value="Enable Delete">
			<?php endif; ?>
			</div>		
		</form>
		
		<table>
			<caption>Active Bookings Today</caption>
			<tr>
				<th colspan="9">Booking Information</th>
				<th colspan="4">Booked For User</th>
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
				<th>Admin Note</th>
				<th>Created At</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isSet($bookingsActiveToday)) : ?>						
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
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
						<input type="hidden" name="sendEmail" id="sendEmail"
						value="<?php htmlout($booking['sendEmail']); ?>">
					</tr>
				</form>
			<?php endforeach; ?>
		<?php endif; ?>
		</table>
		
		<table>
			<caption>Completed Bookings Today</caption>
			<tr>
				<th colspan="9">Booking Information</th>
				<th colspan="5">Completion Info</th>
				<th colspan="4">Booked For User</th>
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
				<th>Admin Note</th>
				<th>Created At</th>
				<th>Finished At</th>
				<th>Actual Duration</th>
				<th>Price Duration</th>
				<th>Ended Early Message</th>
				<th>Ended Early By</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isSet($bookingsCompletedToday)) : ?>						
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
						<td><?php htmlout($booking['CancelledByUserName']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
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
		
		<table>
			<caption>Future Bookings</caption>
			<tr>
				<th colspan="9">Booking information</th>
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
				<th>Admin Note</th>
				<th>Created At</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>	
		<?php if(isSet($bookingsFuture)) : ?>						
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
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
		
		<table>
			<caption>Completed Bookings</caption>
			<tr>
				<th colspan="9">Booking information</th>
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
				<th>Admin Note</th>
				<th>Created At</th>
				<th>Finished At</th>
				<th>Actual Duration</th>
				<th>Price Duration</th>
				<th>Ended Early Message</th>
				<th>Ended Early By</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>	
		<?php if(isSet($bookingsCompleted)) : ?>						
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
						<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
						<td><?php htmlout($booking['CancelledByUserName']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
								<input type="submit" name="action" value="Delete">
							<?php else : ?>
								<input type="submit" name="disabled" value="Delete" disabled>
							<?php endif; ?>
						</td>
						<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
						<input type="hidden" name="UserInfo" id="UserInfo"
						value="<?php htmlout($booking['UserInfo']); ?>">
						<input type="hidden" name="UserID" value="<?php htmlout($booking['BookedUserID']); ?>">
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
		
		<table>
			<caption>Bookings Cancelled</caption>
			<tr>
				<th colspan="9">Booking information</th>
				<th colspan="3">Cancel information</th>
				<th colspan="4">Booked for user</th>
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
				<th>Admin Note</th>
				<th>Created At</th>
				<th>Cancelled At</th>
				<th>Cancel Message</th>
				<th>Cancelled By</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Works For Company</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isSet($bookingsCancelled)) : ?>				
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
						<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
						<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
						<td><?php htmlout($booking['CancelledByUserName']); ?></td>
						<td><?php htmlout($booking['firstName']); ?></td>
						<td><?php htmlout($booking['lastName']); ?></td>
						<td><?php htmlout($booking['email']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($booking['WorksForCompany']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
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
		
		<?php if(isSet($bookingsOther)) : ?>		
		<table>
			<caption>Other Bookings</caption>
			<tr>
				<th colspan="9">Booking information</th>
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
				<th>Admin Note</th>
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
						<td style="white-space: pre-wrap;"><?php htmlout($booking['AdminNote']); ?></td>
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
							<?php if(isSet($_SESSION['bookingsEnableDelete']) AND $_SESSION['bookingsEnableDelete']) : ?>
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
		
	<div class="left"><a href="..">Return to CMS home</a></div>

	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>