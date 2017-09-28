<!-- This is the HTML form used for DISPLAYING a list of Order-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Orders</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<div class="left">
			<?php if(isSet($_SESSION['OrderUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderUserFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Order History</caption>
			<tr>
				<th colspan="13">Order</th>
				<th colspan="3">Messages</th>
				<th colspan="4">Meeting</th>
				<th colspan="1">Cancel Order</th>
			</tr>
			<tr>
				<th>Status</th>
				<th>Approved By User</th>
				<th>Approved By Staff</th>
				<th>Approved By Name</th>
				<th>Content</th>
				<th>User Notes</th>
				<th>Admin Note</th>
				<th>Final Price</th>
				<th>Created At</th>
				<th>Last Update</th>
				<th>Approved At</th>
				<th>Cancelled At</th>
				<th>Details</th>
				<th>Status</th>
				<th>Last Message From Staff</th>
				<th>Last Message From User</th>
				<th>Room Name</th>
				<th>Start</th>
				<th>End</th>
				<th>Booked For</th>
				<th></th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($order as $row): ?>
					<form action="" method="post">
						<tr>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td>
							<td><?php htmlout($row['OrderApprovedByUser']); ?></td>
							<td><?php htmlout($row['OrderApprovedByStaff']); ?></td>
							<td><?php htmlout($row['OrderApprovedByName']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
							<td><?php htmlout($row['OrderFinalPrice']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['DateTimeApproved']); ?></td>
							<td><?php htmlout($row['DateTimeCancelled']); ?></td>
							<td><input type="submit" name="action" value="Details"></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderMessageStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromStaff']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromUser']); ?></td>
							<td><?php htmlout($row['OrderRoomName']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['OrderBookedFor']); ?></td>
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="18"><b>There are no orders registered in the database.</b></td></tr>
			<?php endif; ?>
		</table>
		<div class="left"><a href="/admin/">Return to CMS home</a></div>
	</body>
</html>