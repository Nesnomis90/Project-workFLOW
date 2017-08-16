<!--This is the HTML form for DISPLAYING a list of BOOKINGS for individual users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Booked Meetings</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<?php if(isSet($_SESSION['loggedIn']) AND isSet($_SESSION['LoggedInUserID'])) : ?>
			<h1>Manage Your Booked Meetings</h1>

			<div class="left">
				<?php if(isSet($_SESSION['normalUserBookingFeedback'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['normalUserBookingFeedback']); ?></b></span>
					<?php unset($_SESSION['normalUserBookingFeedback']); ?>
				<?php endif; ?>
			</div>

			<?php if(isSet($bookingsActiveToday) OR isSet($_GET['activeBooking'])) : ?>
				<table>
					<caption>Active Bookings Today</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th>Cancel Booking</th>
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
						<th>Cancel</th>
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
									<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>						
									<td><input type="submit" name="booking" value="Cancel"></td>
									<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
									<input type="hidden" name="MeetingInfo" id="MeetingInfo"
									value="<?php htmlout($booking['MeetingInfo']); ?>">
									<input type="hidden" name="BookingStatus" id="BookingStatus"
									value="<?php htmlout($booking['BookingStatus']); ?>">
								</tr>
							</form>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="10"><b>There are no more active bookings today.</b></td></tr>
					<?php endif; ?>
				</table>
			<?php endif; ?>

			<?php if(isSet($bookingsCompletedToday)) : ?>
				<table>
					<caption>Completed Bookings Today</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Completion Info</th>
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
						<th>Ended Early Message</th>
					</tr>
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
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
								<input type="hidden" name="UserID" value="<?php htmlout($booking['BookedUserID']); ?>">
								<input type="hidden" name="MeetingInfo" id="MeetingInfo"
								value="<?php htmlout($booking['MeetingInfo']); ?>">
								<input type="hidden" name="BookingStatus" id="BookingStatus"
								value="<?php htmlout($booking['BookingStatus']); ?>">
							</tr>
						</form>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
			<?php if(isSet($bookingsFuture) OR isSet($_GET['activeBooking'])) : ?>
				<table>
					<caption>Future Bookings</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th>Cancel Booking</th>
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
						<th>Cancel</th>
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
									<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
									<td><input type="submit" name="booking" value="Cancel"></td>
									<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
									<input type="hidden" name="MeetingInfo" id="MeetingInfo"
									value="<?php htmlout($booking['MeetingInfo']); ?>">
									<input type="hidden" name="BookingStatus" id="BookingStatus"
									value="<?php htmlout($booking['BookingStatus']); ?>">
									<input type="hidden" name="Email" id="Email"
									value="<?php htmlout($booking['email']); ?>">
								</tr>
							</form>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="10"><b>There are no more future bookings.</b></td></tr>
					<?php endif; ?>
				</table>
			<?php endif; ?>

			<?php if(isSet($bookingsCompleted)) : ?>
				<table>
					<caption>Completed Bookings</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Completion Info</th>
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
						<th>Ended Early Message</th>
					</tr>
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
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
								<input type="hidden" name="UserID" value="<?php htmlout($booking['BookedUserID']); ?>">
								<input type="hidden" name="MeetingInfo" id="MeetingInfo"
								value="<?php htmlout($booking['MeetingInfo']); ?>">
								<input type="hidden" name="BookingStatus" id="BookingStatus"
								value="<?php htmlout($booking['BookingStatus']); ?>">
							</tr>
						</form>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
			<?php if(isSet($bookingsCancelled)) : ?>
				<table>
					<caption>Bookings Cancelled</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="2">Cancel information</th>
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
						<th>Cancelled At</th>
						<th>Cancel Message</th>
					</tr>
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
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
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

			<?php if(isSet($bookingsOther)) : ?>
				<table>
					<caption>Other Bookings</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="3">Completion Info</th>
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
						<th>Cancel Message</th>
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
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
								<input type="hidden" name="MeetingInfo" id="MeetingInfo"
								value="<?php htmlout($booking['MeetingInfo']); ?>">
								<input type="hidden" name="BookingStatus" id="BookingStatus"
								value="<?php htmlout($booking['BookingStatus']); ?>">
							</tr>
						</form>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
		<?php else : ?>
			<h1>This information can only be accessed if logged in.</h1>
		<?php endif; ?>

	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>