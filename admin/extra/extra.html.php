<!-- This is the HTML form used for DISPLAYING a list of Extra-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Booking Extra</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Booking Extra</h1>

		<form method="post">
			<div class="right">
				<?php if(isSet($_SESSION['ExtraEnableDelete']) AND $_SESSION['ExtraEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>		
		
		<div class="left">
			<?php if(isSet($_SESSION['ExtraUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['ExtraUserFeedback']); ?></b></span>
				<?php unset($_SESSION['ExtraUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table class="myTable">
			<caption>Available Extra</caption>
			<tr>
				<th colspan="5">Extra</th>
				<th colspan="2">Date</th>
				<th colspan="2">Alter Extra</th>
			</tr>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th>Price</th>
				<th>Active In # Orders</th>
				<th>Type</th>
				<th>Added</th>
				<th>Last Update</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php foreach($extra as $row): ?>
					<form method="post">
						<tr>
							<td><?php htmlout($row['ExtraName']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['ExtraDescription']); ?></td>
							<td><?php htmlout($row['ExtraPrice']); ?></td>
							<td><?php htmlout($row['ExtraIsInThisManyActiveOrders']); ?></td>
							<td><?php htmlout($row['ExtraType']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isSet($_SESSION['ExtraEnableDelete']) AND $_SESSION['ExtraEnableDelete'] AND
										$row['ExtraIsInThisManyActiveOrders'] == 0) : ?>
									<input type="submit" name="action" value="Delete">
								<?php elseif(isSet($_SESSION['ExtraEnableDelete']) AND $_SESSION['ExtraEnableDelete'] AND
										$row['ExtraIsInThisManyActiveOrders'] > 0) : ?>
									<b>Not enabled due to it being reserved in an active order.</b>
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="ExtraName" value="<?php htmlout($row['ExtraName']); ?>">
							<input type="hidden" name="ExtraID" value="<?php htmlout($row['TheExtraID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="9"><b>There are no Extra registered in the database.</b></td></tr>
			<?php endif; ?>

			<form method="post">
				<tr>
					<td colspan="9">
						<input type="hidden" name="action" value="Add Extra">
						<input type="submit" style="font-size: 150%; color: green;" value="+">
					</td>
				</tr>
			</form>

			<tr><th colspan="9"></th></tr>
		</table>
	</body>
</html>
