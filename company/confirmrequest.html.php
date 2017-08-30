<!-- This is the HTML form used for all users in COMPANY to confirm that they want to create request to join a company -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Request</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset><legend>Your Request:</legend>
			<div class="left">
			<span style="white-space: pre-wrap;"><?php htmlout(
			"You have chosen to send a request to join the company $companyName." . 
			"\nWith the message below:\n$requestMessage." .
			"\nAre you sure you want to send the request?"); ?></span>

			</div>
			<div class="left">
				<form action="" method="post">
					<input type="hidden" name="companyID" value="<?php htmlout($selectedCompanyToJoinID); ?>">
					<input type="hidden" name="requestMessage" value="<?php htmlout($requestMessage); ?>">
					<input type="hidden" name="companyName" value="<?php htmlout($companyName); ?>">
					<input type="submit" name="confirm" value="Yes, Send The Request">
					<input type="submit" name="confirm" value="No, Cancel The Request">
				</form>
			</div>
		</fieldset>
	</body>
</html>