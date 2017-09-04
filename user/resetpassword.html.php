<!-- This is the HTML form used for users to SET A NEW PASSWORD if they have forgotten it-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Set New Password</title>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<?php if(!isSet($_SESSION['loggedIn'])) : ?>
			<div class="left">
				<form action="" method="post">
					<fieldset><legend>Set Your New Password</legend>
						<div class="left">
							<?php if(isSet($_SESSION['resetPasswordFeedback'])) : ?>
								<span class="warning"><b><?php htmlout($_SESSION['resetPasswordFeedback']); ?></b></span>
								<?php unset($_SESSION['resetPasswordFeedback']); ?>
							<?php endif; ?>
						</div>
						<div class="left">
							<label for="password1">Set New Password: </label>
							<input type="password" name="password1" placeholder="Set your password"
							value="<?php htmlout($password1); ?>">
						</div>
						<div class="left">
							<label for="password2">Set New Password: </label>
								<input type="password" name="password2" placeholder="Repeat your password"
								value="<?php htmlout($password2); ?>">
						</div>
						<div class="left">
							<input type="hidden" name="reset" value="Set New Password">
							<input type="submit" value="Set New Password">
						</div>
					</fieldset>
				</form>
			</div>
		<?php else : ?>
			<h1>You're already logged into a registered account.</h1>
		<?php endif; ?>
	</body>
</html>