<!--This is the HTML form for DISPLAYING a list of BOOKINGS for a company-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Company Booking History</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<?php if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND isSet($_SESSION['LoggedInUserID']) AND !empty($_SESSION['LoggedInUserID']) AND !isSet($noAccess)) : ?>
			<h1>Company Booking History</h1>

			<div class="left">
				<?php if(isSet($_SESSION['normalCompanyBookingFeedback'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['normalCompanyBookingFeedback']); ?></b></span>
					<?php unset($_SESSION['normalCompanyBookingFeedback']); ?>
				<?php endif; ?>
			</div>

			<?php if(isSet($bookingsActiveToday) OR isSet($_GET['activeBooking'])) : ?>
				<table>
					<caption>Active Bookings Today</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Connected user information</th>	
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<th>Cancel Booking</th>
						<?php endif; ?>
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
						<th>Company Role</th>
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<th>Cancel</th>
						<?php endif; ?>
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
									<td><?php htmlout($booking['firstName']); ?></td>
									<td><?php htmlout($booking['lastName']); ?></td>
									<td><?php htmlout($booking['email']); ?></td>
									<td><?php htmlout($booking['CompanyRole']); ?></td>
									<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
										<td><input type="submit" name="booking" value="Cancel"></td>
									<?php endif; ?>
									<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
									<input type="hidden" name="MeetingInfo" id="MeetingInfo"
									value="<?php htmlout($booking['MeetingInfo']); ?>">
									<input type="hidden" name="BookingStatus" id="BookingStatus"
									value="<?php htmlout($booking['BookingStatus']); ?>">
									<input type="hidden" name="Email" id="Email"
									value="<?php htmlout($booking['email']); ?>">
									<input type="hidden" name="UserInfo" id="UserInfo"
									value="<?php htmlout($booking['UserInfo']); ?>">
									<input type="hidden" name="sendEmail" id="sendEmail"
									value="<?php htmlout($booking['sendEmail']); ?>">
								</tr>
							</form>
						<?php endforeach; ?>
					<?php else : ?>
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<tr><td colspan="13"><b>There are no more active bookings today.</b></td></tr>
						<?php else : ?>
							<tr><td colspan="12"><b>There are no more active bookings today.</b></td></tr>
						<?php endif; ?>
					<?php endif; ?>
				</table>
			<?php endif; ?>

			<?php if(isSet($bookingsCompletedToday)) : ?>
				<table>
					<caption>Completed Bookings Today</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Connected user information</th>	
						<th colspan="5">Completion Info</th>
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
						<th>Company Role</th>
						<th>Finished At</th>
						<th>Actual Duration</th>
						<th>Price Duration</th>
						<th>Ended Early Message</th>
						<th>Ended Early By</th>
					</tr>
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
								<td><?php htmlout($booking['firstName']); ?></td>
								<td><?php htmlout($booking['lastName']); ?></td>
								<td><?php htmlout($booking['email']); ?></td>
								<td><?php htmlout($booking['CompanyRole']); ?></td>
								<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
								<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
								<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<td><?php htmlout($booking['CancelledByUserName']); ?></td>								
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
			
			<?php if(isSet($bookingsFuture) OR isSet($_GET['activeBooking'])) : ?>
				<table>
					<caption>Future Bookings</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Connected user information</th>
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<th>Cancel Booking</th>
						<?php endif; ?>
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
						<th>Company Role</th>
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<th>Cancel</th>
						<?php endif; ?>
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
									<td><?php htmlout($booking['firstName']); ?></td>
									<td><?php htmlout($booking['lastName']); ?></td>
									<td><?php htmlout($booking['email']); ?></td>
									<td><?php htmlout($booking['CompanyRole']); ?></td>
									<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
										<td><input type="submit" name="booking" value="Cancel"></td>
									<?php endif; ?>
									<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
									<input type="hidden" name="MeetingInfo" id="MeetingInfo"
									value="<?php htmlout($booking['MeetingInfo']); ?>">
									<input type="hidden" name="BookingStatus" id="BookingStatus"
									value="<?php htmlout($booking['BookingStatus']); ?>">
									<input type="hidden" name="Email" id="Email"
									value="<?php htmlout($booking['email']); ?>">
									<input type="hidden" name="UserInfo" id="UserInfo"
									value="<?php htmlout($booking['UserInfo']); ?>">
									<input type="hidden" name="sendEmail" id="sendEmail"
									value="<?php htmlout($booking['sendEmail']); ?>">
								</tr>
							</form>
						<?php endforeach; ?>
					<?php else : ?>
						<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
							<tr><td colspan="13"><b>There are no more future bookings.</b></td></tr>
						<?php else : ?>
							<tr><td colspan="12"><b>There are no more future bookings.</b></td></tr>
						<?php endif; ?>
					<?php endif; ?>
				</table>
			<?php endif; ?>

			<?php if(isSet($bookingsCompleted)) : ?>
				<table>
					<caption>Completed Bookings</caption>
					<tr>
						<th colspan="8">Booking information</th>
						<th colspan="4">Connected user information</th>	
						<th colspan="5">Completion Info</th>
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
						<th>Company Role</th>
						<th>Finished At</th>
						<th>Actual Duration</th>
						<th>Price Duration</th>
						<th>Ended Early Message</th>
						<th>Ended Early By</th>
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
								<td><?php htmlout($booking['firstName']); ?></td>
								<td><?php htmlout($booking['lastName']); ?></td>
								<td><?php htmlout($booking['email']); ?></td>
								<td><?php htmlout($booking['CompanyRole']); ?></td>
								<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
								<td><?php htmlout($booking['CompletedMeetingDuration']); ?></td>
								<td><?php htmlout($booking['CompletedMeetingDurationForPrice']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<td><?php htmlout($booking['CancelledByUserName']); ?></td>
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
						<th colspan="4">Connected user information</th>	
						<th colspan="3">Cancel information</th>
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
						<th>Company Role</th>
						<th>Cancelled At</th>
						<th>Cancel Message</th>
						<th>Cancelled By</th>
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
								<td><?php htmlout($booking['firstName']); ?></td>
								<td><?php htmlout($booking['lastName']); ?></td>
								<td><?php htmlout($booking['email']); ?></td>
								<td><?php htmlout($booking['CompanyRole']); ?></td>
								<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($booking['CancelMessage']); ?></td>
								<td><?php htmlout($booking['CancelledByUserName']); ?></td>
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
						<th colspan="4">Connected user information</th>						
						<th colspan="2">Completion Info</th>
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
						<th>Company Role</th>
						<th>Finished</th>
						<th>Cancelled</th>
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
								<td><?php htmlout($booking['firstName']); ?></td>
								<td><?php htmlout($booking['lastName']); ?></td>
								<td><?php htmlout($booking['email']); ?></td>
								<td><?php htmlout($booking['CompanyRole']); ?></td>
								<td><?php htmlout($booking['BookingWasCompletedOn']); ?></td>
								<td><?php htmlout($booking['BookingWasCancelledOn']); ?></td>
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