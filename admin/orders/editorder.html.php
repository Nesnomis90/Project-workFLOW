<!-- This is the HTML form used for EDITING or ADDING Order information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Edit Order</title>
		<style>
			label {
				width: 220px;
			}
			.checkboxlabel{
				float: none;
				clear: none;
				width: 200px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Edit Order</legend>
			<div>
				<?php if(isSet($_SESSION['AddOrderError'])) :?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddOrderError']); ?></b></span>
					<?php unset($_SESSION['AddOrderError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">

				<div>
					<label>Order Content</label>
					<span style="white-space: pre-wrap;"><b><?php htmlout($originalOrderContent); ?></b></span>
				</div>

				<div>
					<label>Order Description: </label>
					<span><b><?php htmlout($originalOrderDescription); ?></b></span>
				</div>

				<div>
					<label>Original Order Feedback: </label>
					<span><b><?php htmlout($originalOrderFeedback); ?></b></span>
				</div>

				<div>
					<label class="description">Set New Order Feedback: </label>
						<textarea rows="4" cols="50" name="OrderFeedback" placeholder="Enter Order Feedback"><?php htmlout($orderFeedback); ?></textarea>
				</div>

				<div>
					<label>Original Admin Note: </label>
					<span><b><?php htmlout($originalOrderAdminNote); ?></b></span>
				</div>

				<div>
					<label class="description">Set New Admin Note: </label>
						<textarea rows="4" cols="50" name="AdminNote" placeholder="Enter Admin Note"><?php htmlout($orderAdminNote); ?></textarea>
				</div>

				<div>
					<label>Original Order Approval: </label>
					<?php if($originalOrderIsApproved == 1) : ?>
						<span><b><?php htmlout("Order Approved"); ?></b></span>
					<?php else : ?>
						<span><b><?php htmlout("Order Not Approved"); ?></b></span>
					<?php endif; ?>
				</div>

				<div>
					<?php if($orderIsApproved == 1) : ?>
						<label><input type="checkbox" name="isApproved" value="1" checked>Set As Approved</label>
					<?php else : ?>
						<label><input type="checkbox" name="isApproved" value="1">Set As Approved</label>
					<?php endif; ?>
				</div>

				<div class="left">
					<input type="hidden" name="OrderID" value="<?php htmlout($orderID); ?>">
					<input type="submit" name="action" value="Edit Order">
				</div>

				<div class="left">
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Cancel">
				</div>
			</form>
		</fieldset>
	</body>
</html>