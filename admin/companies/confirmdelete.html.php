<!-- This is the HTML form used by ADMIN in COMPANIES to confirm that they want to DELETE A COMPANY -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Delete</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Warning!</legend>
			<div class="left">
				<span style="white-space: pre-wrap;"><?php htmlout("You are about to delete the company $companyName." . 
				"\nThis will permanently remove all information about the company and remove all its active bookings." .
				"\n\nAre you sure you want to delete this company?"); ?></span>	
			</div>
			<div class="left">
				<form action="" method="post">
					<input type="submit" name="confirmdelete" value="Yes, Delete The Company">
					<input type="submit" name="confirmdelete" value="No, Cancel The Delete">
				</form>
			</div>
		</fieldset>
	</body>
</html>