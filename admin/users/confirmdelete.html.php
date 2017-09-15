<!-- This is the HTML form used by ADMIN in USERS to confirm that they want to DELETE A USER -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
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
				<span style="white-space: pre-wrap;"><?php htmlout("You are about to delete the user $userInfo." . 
				"\nThis will permanently remove all information about the user and remove all its active bookings." .
				"\n\nAre you sure you want to delete this user?"); ?></span>	
			</div>
			<?php if(isSet($wrongPassword)) : ?>
			<div class="left">
				<span class="warning"><?php htmlout($wrongPassword); ?></span>
			</div>
			<?php endif; ?>
			<div class="left">
				<form action="" method="post">
					<label>Confirm with Password: </label>
					<?php if(isSet($wrongPassword)) : ?>
						<input type="password" name="password" class="fillOut">
					<?php else : ?>
						<input type="password" name="password">
					<?php endif; ?>
					<input type="submit" name="confirmdelete" value="Yes, Delete The User">
					<input type="submit" name="confirmdelete" value="No, Cancel The Delete">
					<input type="hidden" name="UserInfo" value="<?php htmlout($userInfo); ?>">
					<input type="hidden" name="UserID" value="<?php htmlout($userID); ?>">
				</form>
			</div>
		</fieldset>
	</body>
</html>