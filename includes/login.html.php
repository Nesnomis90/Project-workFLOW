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
		<fieldset><legend>Log In</legend>
			<div class="left">
				<span><b>Please log in to view the page that you requested</b></span>
			</div>

			<div class="left">
			<?php if(isSet($_SESSION['loginError'])) : ?>
				<span><b class="warning"><?php htmlout($_SESSION['loginError']); ?></b></span>
				<?php unset($_SESSION['loginError']); ?>
			<?php endif; ?>
			</div>

			<div class="left">
				<form action="" method="post">
					<div>
						<?php if(!isSet($_SESSION['loginEmailSubmitted'])){
							$email = "";
						} else {
							$email = $_SESSION['loginEmailSubmitted'];
							unset($_SESSION['loginEmailSubmitted']);
						}?>
						<label for="email">Email: </label> 
						<input type="text" name="email" id="email"
						value="<?php htmlout($email); ?>">
					</div>
					<div>
						<label for="password">Password: </label> 
						<input type="password" name="password" id="password">
					</div>
					<div>
						<input type="hidden" name="action" value="login">
						<input type="submit" value="Log in">
					</div>
				</form>
			</div>

			<div class="left">
				<form action="" method="post">
					<input type="submit" name="action" value="Forgotten Password?">
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
