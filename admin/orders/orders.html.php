<!-- This is the HTML form used for DISPLAYING a list of Order-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Booking Order</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Booking Order</h1>

		<div class="left">
			<?php if(isSet($_SESSION['OrderUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderUserFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Available Order</caption>
			<tr>
				<th colspan="6">Order</th>
				<th colspan="5">Date</th>
				<th colspan="2">Alter Order</th>
			</tr>
			<tr>
				<th>Status</th>
				<th>Content</th>
				<th>Description</th>
				<th>Feedback</th>
				<th>Admin Note</th>
				<th>Final Price</th>
				<th>Start</th>
				<th>End</th>
				<th>Created</th>
				<th>Last Update</th>
				<th>Cancelled</th>
				<th>Edit</th>
				<th>Cancel</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($order as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['OrderStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderDescription']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderFeedback']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
							<td><?php htmlout($row['OrderPriceCharged']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['DateTimeCancelled']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="13"><b>There are no Order registered in the database.</b></td></tr>
			<?php endif; ?>
		</table>
		<p><a href="/admin/">Return to CMS home</a></p>
	</body>
</html>
