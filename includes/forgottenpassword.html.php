<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Log In</title>
	</head>
	<body>
		<fieldset><legend>Request New Password</legend>
			<div class="left">
				<span><b>Submit the email of the account you want to request a new password for.</b></span>
			</div>

			<div class="left">
				<?php if(isSet($_SESSION['forgottenPasswordError'])): ?>
					<span><b class="warning"><?php htmlout($_SESSION['forgottenPasswordError']); ?></b></span>
					<?php unset($_SESSION['forgottenPasswordError']); ?>
				<?php endif; ?>
			</div>

			<div class="left">
				<form method="post">
					<div>
						<?php if(!isSet($_SESSION['forgottenPasswordEmailSubmitted'])){
							$email = "";
						} else {
							$email = $_SESSION['forgottenPasswordEmailSubmitted'];
							unset($_SESSION['forgottenPasswordEmailSubmitted']);
						}?>
						<label for="email">Email: </label> 
						<input type="text" name="email" id="email" value="<?php htmlout($email); ?>">
					</div>
					<div>
						<input type="hidden" name="action" value="requestPassword">
						<input type="submit" value="Request New Password">
					</div>
				</form>
			</div>

			<div class="left">
				<?php if(isSet($_SESSION['SetDefaultRoom'])) : ?>
					<span><a href="/meetingroom/?cancelSetDefaultRoom">Return to Meetingroom</a></span>
				<?php else : ?>
					<span><a href="/">Return to Home</a></span>
				<?php endif; ?>
			</div>
		</fieldset>
	</body>
</html>
