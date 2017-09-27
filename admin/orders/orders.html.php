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

		<h1>Manage Orders</h1>

		<div class="left">
			<?php if(isSet($_SESSION['OrderUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderUserFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Order History</caption>
			<tr>
				<th colspan="5">Order</th>
				<th colspan="2">Communication</th>
				<th colspan="6">Date</th>
				<th colspan="3">Approved By</th>
				<th colspan="2">Alter Order</th>
			</tr>
			<tr>
				<th>Status</th>
				<th>Content</th>
				<th>User Notes</th>
				<th>Admin Note</th>
				<th>Final Price</th>
				<th>To User</th>
				<th>From User</th>
				<th>Meeting Start</th>
				<th>Meeting End</th>
				<th>Created At</th>
				<th>Approved At</th>
				<th>Last Update</th>
				<th>Cancelled At</th>
				<th>User</th>
				<th>Staff</th>
				<th>Staff Name</th>
				<th>Edit</th>
				<th>Cancel</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($order as $row): ?>
					<form action="" method="post">
						<tr>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td><?php htmlout($row['OrderStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
							<td><?php htmlout($row['OrderPriceCharged']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderCommunicationToUser']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderCommunicationFromUser']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeApproved']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['DateTimeCancelled']); ?></td>
							<td><?php htmlout($row['OrderApprovedByUser']); ?></td>
							<td><?php htmlout($row['OrderApprovedByStaff']); ?></td>
							<td><?php htmlout($row['OrderApprovedByName']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
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