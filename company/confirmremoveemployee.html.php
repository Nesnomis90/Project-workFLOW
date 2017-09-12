<!-- This is the HTML form used for COMPANY OWNERS to confirm that they want to REMOVE AN EMPLOYEE -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Removal</title>
		<style>
			label {
				width: 170px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/topnav.html.php'; ?>

		<fieldset><legend>Employee You Want To Remove:</legend>
			<div class="left">
				<?php if(isSet($feedback)) : ?>
					<div>
						<span class="warning"><?php htmlout($feedback); ?></span>
					</div>
				<?php endif; ?>
				<form action="" method="post">
					<div>
						<label>Name: </label>
						<span><b><?php htmlout($userName); ?></b></span>
					</div>
					<div>
						<label>From The Company: </label>
						<span><b><?php htmlout($companyName); ?></b></span>
					</div>
					<div>
						<label>Confirm With Password: </label>
						<?php if(isSet($feedback)) : ?>
							<input type="password" name="password" class="fillOut">
						<?php else : ?>
							<input type="password" name="password">
						<?php endif; ?>
					</div>
					<div class="left">
						<input type="hidden" name="CompanyID" value="<?php htmlout($companyID); ?>">
						<input type="hidden" name="UserID" value="<?php htmlout($userID); ?>">
						<input type="hidden" name="CompanyName" value="<?php htmlout($companyName); ?>">
						<input type="hidden" name="UserName" value="<?php htmlout($userName); ?>">
						<input type="submit" name="remove" value="Remove Employee">
						<input type="submit" name="remove" value="Cancel">
					</div>
				</form>
		</fieldset>
	</body>
</html>