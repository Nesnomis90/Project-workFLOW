<!-- This is the HTML form used for EDITING Order information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Order Details</title>
		<style>
			label {
				width: 210px;
			}
			.checkboxlabel{
				display: inline-block;
				float: left;
				clear: none;
				width: auto;
			}
		</style>
		<script>
			function addAlternativeExtra(){
				// Get table we want to manipulate
				var table = document.getElementById("addAlternative");
				var tableRows = table.rows.length;

				// Add new row at the "end" i.e. between the old extra and the button
				var row = table.insertRow(tableRows-1);

				// Insert td element for "Name" column
				var columnName = row.insertCell(0);
				var columnDescription = row.insertCell(1);
				columnDescription.setAttribute("id", "addAlternativeDescriptionSelected");
				var columnPrice = row.insertCell(2);
				columnPrice.setAttribute("id", "addAlternativePriceSelected");
				var columnAmount = row.insertCell(3);

				// Create the select box we want to be able to choose from
				var selectExtraName = document.createElement("select");
				selectExtraName.setAttribute("id", "addAlternativeSelected");
				selectExtraName.onchange = function onChange(){changeAlternativeText();}

				// Create the input number for amount
				var inputExtraAmount = document.createElement("input");
				inputExtraAmount.setAttribute("type", "number");
				inputExtraAmount.setAttribute("value", "1");
				inputExtraAmount.setAttribute("min", "1");

				// Get available extras
				var availableExtrasArray = <?php echo json_encode($availableExtra); ?>;

				// Add the available extra names as options
				for(var i = 0; i < availableExtrasArray.length; i++){
					var option = document.createElement("option");
					option.value = availableExtrasArray[i]['ExtraID'];
					option.text = availableExtrasArray[i]['ExtraName'];
					selectExtraName.appendChild(option);
				}

				// Add items/values to columns
				columnName.appendChild(selectExtraName);
				columnDescription.innerHTML = availableExtrasArray[0]['ExtraDescription'];
				columnPrice.innerHTML = availableExtrasArray[0]['ExtraPrice'];
				columnAmount.appendChild(inputExtraAmount);
			}

			function changeAlternativeText(){
				var selectBox = document.getElementById("addAlternativeSelected");
				var	descriptionText = document.getElementById("addAlternativeDescriptionSelected");
				var priceText = document.getElementById("addAlternativePriceSelected");
				
				// Get available extras
				var availableExtrasArray = <?php echo json_encode($availableExtra); ?>;

				// Add the available extra names as options
				for(var i = 0; i < availableExtrasArray.length; i++){
					if(selectBox.options[selectBox.selectedIndex].value == availableExtrasArray[i]['ExtraID']){
						descriptionText.innerHTML = availableExtrasArray[i]['ExtraDescription'];
						priceText.innerHTML = availableExtrasArray[i]['ExtraPrice'];
						break;
					}
				}
			}
		</script>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<form action="" method="post">
			<fieldset><legend>Order Details</legend>
				<div>
					<?php if(isSet($_SESSION['AddOrderError'])) :?>
						<span><b class="feedback"><?php htmlout($_SESSION['AddOrderError']); ?></b></span>
						<?php unset($_SESSION['AddOrderError']); ?>
					<?php endif; ?>
				</div>

				<div>
					<label>Date Created: </label>
					<span><b><?php htmlout($originalOrderCreated); ?></b></span>
				</div>

				<div>
					<label>Last Update: </label>
					<span><b><?php htmlout($originalOrderUpdated); ?></b></span>
				</div>

				<div>
					<label>User Notes: </label>
					<span style="white-space: pre-wrap;"><b><?php htmlout($originalOrderUserNotes); ?></b></span>
				</div>

				<div>
					<label>Messages: </label>
					<span style="white-space: pre-wrap;"><b><?php htmlout($orderMessages); ?></b></span>
				</div>

				<div>
					<label class="description">Send New Message To User: </label>
					<textarea rows="4" cols="50" name="OrderCommunicationToUser" placeholder="Enter New Message To User"><?php htmlout($orderCommunicationToUser); ?></textarea>
				</div>

				<div>
					<label>Original Admin Note: </label>
					<span style="white-space: pre-wrap;"><b><?php htmlout($originalOrderAdminNote); ?></b></span>
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
					<label>Change Order Approval: </label>
					<?php if($orderIsApproved == 1) : ?>
						<label class="checkboxlabel"><input type="checkbox" name="isApproved" value="1" checked>Set As Approved</label>
					<?php else : ?>
						<label class="checkboxlabel"><input type="checkbox" name="isApproved" value="1">Set As Approved</label>
					<?php endif; ?>
				</div>
			</fieldset>

			<table id="addAlternative">
				<caption>Items Ordered</caption>
				<tr>
					<th colspan="4">Item</th>
					<th colspan="3">Approved For Purchase</th>
					<th colspan="3">Set As Purchased</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Price (1 Amount)</th>
					<th>Amount</th>
					<th>Approved?</th>
					<th>By Staff</th>
					<th>At Date</th>
					<th>Purchased?</th>
					<th>By Staff</th>
					<th>At Date</th>
				</tr>
				<?php if(isSet($extraOrdered)) : ?>
					<?php foreach($extraOrdered as $row): ?>
						<tr>
							<td><?php htmlout($row['ExtraName']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['ExtraDescription']); ?></td>
							<td><?php htmlout($row['ExtraPrice']); ?></td>
							<td><?php htmlout($row['ExtraAmount']); ?></td>
							<td>
								<?php if($row['ExtraBooleanApprovedForPurchase'] == 1) : ?>
									<label style="width: auto;"><input type="checkbox" name="isApprovedForPurchase[]" value="<?php htmlout($row['ExtraID']); ?>" checked>Approved</label>
								<?php else : ?>
									<label style="width: auto;"><input type="checkbox" name="isApprovedForPurchase[]" value="<?php htmlout($row['ExtraID']); ?>">Approved</label>
								<?php endif; ?>
							</td>
							<td><?php htmlout($row['ExtraApprovedForPurchaseByUser']); ?></td>
							<td><?php htmlout($row['ExtraDateTimeApprovedForPurchase']); ?></td>
							<td>
								<?php if($row['ExtraBooleanPurchased'] == 1) : ?>
									<label style="width: auto;"><input type="checkbox" name="isPurchased[]" value="<?php htmlout($row['ExtraID']); ?>" checked>Purchased</label>
								<?php else : ?>
									<label style="width: auto;"><input type="checkbox" name="isPurchased[]" value="<?php htmlout($row['ExtraID']); ?>">Purchased</label>
								<?php endif; ?>
							</td>
							<td><?php htmlout($row['ExtraPurchasedByUser']); ?></td>
							<td><?php htmlout($row['ExtraDateTimePurchased']); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr><td colspan="10"><button type="button" onclick="addAlternativeExtra()">Add Alternative</button></td></tr>
				<?php else : ?>
					<tr><td colspan="10"><b>This order has nothing in it.</b></td></tr>
				<?php endif; ?>
			</table>

			<div class="left">
				<input type="hidden" name="OrderID" value="<?php htmlout($orderID); ?>">
				<input type="submit" name="action" value="Go Back">
				<input type="submit" name="action" value="Reset">
				<input type="submit" name="action" value="Submit Changes">
			</div>
		</form>
	</body>
</html>