<!--This is the HTML form for DISPLAYING a list of BOOKINGS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Booked Meetings</title>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<?php if(isSet($_SESSION['loggedIn']) AND isSet($_SESSION['LoggedInUserID'])) : ?>
			<h1>Manage Your Booked Meetings</h1>

			<div>
				<?php if(isSet($_SESSION['normalUserBookingFeedback'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['normalUserBookingFeedback']); ?></b></span>
					<?php unset($_SESSION['normalUserBookingFeedback']); ?>
				<?php endif; ?>
			</div>
			
			<table>
				<caption>Your booking history</caption>
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
			<?php if(isSet($bookingOutput)) : ?>						
				<?php foreach ($bookingOutput AS $booking) : ?>
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
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
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
		<?php else : ?>
			<h1>This information can only be accessed if logged in.</h1>
		<?php endif; ?>

	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>