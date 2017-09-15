<!-- This is the HTML form used for COMPANY OWNERS to confirm that they want to ADD AN EMPLOYEE -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/navcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Confirm Add</title>
		<style>
			label {
				width: 170px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/topnav.html.php'; ?>

		<fieldset><legend>Employee You Want To Add:</legend>
			<div class="left">
				<?php if(isSet($wrongPassword)) : ?>
					<div>
						<span class="warning"><?php htmlout($wrongPassword); ?></span>
					</div>
				<?php endif; ?>
				<form action="" method="post">
				<?php if($createUser) : ?>
					<div>
						<span style="white-space: pre-wrap;"><?php htmlout(
						"You have selected to add a new user to the company $companyName." . 
						"\nThe user will be created based on the email you submitted ($email)." .
						"\nIf this email is not correct, or the user does not activate the account within 8 hours, the account will be removed." .
						"\nAre you sure the details you submitted are correct and that you want add this new user to your company as an employee?"); ?></span>
					</div>
				<?php else : ?>
					<div>
						<label>Name: </label>
						<span><b><?php htmlout($userName); ?></b></span>
					</div>
					<div>
						<label>Into The Company: </label>
						<span><b><?php htmlout($companyName); ?></b></span>
					</div>
				<?php endif; ?>
					<div>
						<label>Confirm With Password: </label>
						<?php if(isSet($wrongPassword)) : ?>
							<input type="password" name="password" class="fillOut">
						<?php else : ?>
							<input type="password" name="password">
						<?php endif; ?>
					</div>
					<div class="left">
						<input type="hidden" name="CompanyID" value="<?php htmlout($companyID); ?>">
						<input type="hidden" name="UserID" value="<?php htmlout($userID); ?>">
						<input type="hidden" name="CompanyName" value="<?php htmlout($companyName); ?>">
						<?php if($createUser) : ?>
							<input type="hidden" name="Email" value="<?php htmlout($email); ?>">
						<?php else : ?>
							<input type="hidden" name="UserName" value="<?php htmlout($userName); ?>">
						<?php endif; ?>
						<input type="hidden" name="PositionID" value="<?php htmlout($positionID); ?>">
						<input type="hidden" name="CreateUser" value="<?php htmlout($createUser); ?>">
						<input type="submit" name="confirmadd" value="Add Employee">
						<input type="submit" name="confirmadd" value="Cancel">
					</div>
				</form>
		</fieldset>
	</body>
</html>