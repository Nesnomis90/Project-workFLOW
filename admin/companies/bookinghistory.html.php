<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#bookinghistorytable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#bookinghistorytable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}
			
			#bookinghistorytable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#bookinghistorytable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#bookinghistorytable tr:hover{background-color:#ddd;}
			
			#bookinghistorytable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#bookinghistorytable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Booking History</title>
	</head>
	<body>
		<h1>Booking History</h1>
		<form action="" method="post">
			<div>
				<?php if() : ?>
					<input type="submit" name="action" value="Previous Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Previous Period">
				<?php endif; ?>
				<?php if() : ?>
					<input type="submit" name="action" value="Next Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Next Period">
				<?php endif; ?>
			</div>
		</form>
		<table id="bookinghistorytable">
			<caption>From The Period Of <?php htmlout($BillingPeriod); ?></caption>
			<?php if(isset($bookingHistory)) : ?>
				<?php foreach($bookingHistory AS $row) : ?>
					<tr>
						<td>User: <?php htmlout($row['UserInformation']); ?></td>
						<td>Booked the meeting room: <?php htmlout($row['MeetingRoomName']); ?></td>
						<td>For the period of: <?php htmlout($row['BookingPeriod']); ?></td>
						<td>Using a total time of: <?php htmlout($row['BookingPeriod']); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<b>Company Had No Completed Bookings This Period</b>
			<?php endif; ?>
		</table>
		
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>		