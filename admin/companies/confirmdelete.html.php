<!-- This is the HTML form used by ADMIN in COMPANIES to confirm that they want to DELETE A COMPANY -->
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
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Warning!</legend>
			<div class="left">
				<span style="white-space: pre-wrap;"><?php htmlout("You are about to delete the company $companyName." . 
				"\nThis will permanently remove all information about the company and remove all its active bookings." .
				"\n\nAre you sure you want to delete this company?"); ?></span>	
			</div>
			<?php if(isSet($wrongPassword)) : ?>
			<div class="left">
				<span class="warning"><?php htmlout($wrongPassword); ?></span>
			</div>
			<?php endif; ?>
			<div class="left">
				<form method="post">
					<label>Confirm with Password: </label>
					<?php if(isSet($wrongPassword)) : ?>
						<input type="password" name="password" class="fillOut">
					<?php else : ?>
						<input type="password" name="password">
					<?php endif; ?>
					<input type="submit" name="confirmdelete" value="Yes, Delete The Company">
					<input type="submit" name="confirmdelete" value="No, Cancel The Delete">
					<input type="hidden" name="CompanyName" value="<?php htmlout($companyName); ?>">
					<input type="hidden" name="CompanyID" value="<?php htmlout($companyID); ?>">
				</form>
			</div>
		</fieldset>
	</body>
</html>