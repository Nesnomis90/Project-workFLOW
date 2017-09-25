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
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<h1>Manage Orders</h1>

		<div class="left">
			<?php if(isSet($_SESSION['OrderStaffFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderStaffFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderStaffFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Active Orders</caption>
			<tr>
				<th colspan="4">Order</th>
				<th colspan="2">Date</th>
				<th colspan="1">Alter Order</th>
			</tr>
			<tr>
				<th>Status</th>
				<th>Content</th>
				<th>Description</th>
				<th>Feedback</th>
				<th>Start</th>
				<th>End</th>
				<th>Edit</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($order as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['OrderStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderDescription']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderFeedback']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="11"><b>There are no active orders.</b></td></tr>
			<?php endif; ?>
		</table>
	</body>
</html>
