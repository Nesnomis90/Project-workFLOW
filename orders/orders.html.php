<!-- This is the HTML form used for DISPLAYING a list of Orders for STAFF users-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Orders</title>
	</head>
	<body onload="startTime()">
		<?php if($accessRole == "Admin") : ?>
			<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		<?php else : ?>
			<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/stafftopnav.html.php'; ?>
		<?php endif; ?>

		<div class="left">
			<?php if(isSet($_SESSION['OrderStaffFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderStaffFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderStaffFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Active Orders</caption>
			<tr>
				<th colspan="3">Order</th>
				<th colspan="2">Communication</th>
				<th colspan="4">Date</th>
				<th colspan="2">Approved By</th>
				<th colspan="1">Full Information</th>
			</tr>
			<tr>
				<th>Status</th>
				<th>Content</th>
				<th>User Notes</th>
				<th>To User</th>
				<th>From User</th>
				<th>Meeting Start</th>
				<th>Meeting End</th>
				<th>Created At</th>
				<th>Last Update</th>
				<th>User</th>
				<th>Staff</th>
				<th>Details</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($order as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['OrderStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderCommunicationToUser']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderCommunicationFromUser']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['OrderApprovedByUser']); ?></td>
							<td><?php htmlout($row['OrderApprovedByStaff']); ?></td>
							<td><input type="submit" name="action" value="Details"></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="12"><b>There are no active orders.</b></td></tr>
			<?php endif; ?>
		</table>
	</body>
</html>
