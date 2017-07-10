<!-- This is the HTML form used for users to register an account-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Register Account</title>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<?php if(!isset($_SESSION['loggedIn'])) : ?>
			<h1>Register Account</h1>
			<div>
				<form action="" method="post">
					<fieldset><legend>Enter your login information</legend>
						<div>
							<?php if(!isset($_SESSION['registerUserFeedback']) AND !isset($_SESSION['registerUserWarning'])) : ?>
								<span><b>All fields have to be filled in</b></span>
							<?php endif; ?>
						</div>
						<div>
							<?php if(isset($_SESSION['registerUserFeedback'])) : ?>
								<span class="feedback"><b><?php htmlout($_SESSION['registerUserFeedback']); ?></b></span>
								<?php unset($_SESSION['registerUserFeedback']); ?>
							<?php endif; ?>
						</div>
						<div>
							<?php if(isset($_SESSION['registerUserWarning'])) : ?>
								<span class="warning"><b><?php htmlout($_SESSION['registerUserWarning']); ?></b></span>
								<?php unset($_SESSION['registerUserWarning']); ?>
							<?php endif; ?>
						</div>						
						<div>
							<label for="firstname">First Name: </label>
							<?php if(isset($refreshedRegister) AND $firstName == "") : ?>
								<input class="fillOut" type="text" name="firstname" placeholder="Enter your first/given name"
								value="<?php htmlout($firstName); ?>">
							<?php else : ?>
								<input type="text" name="firstname" placeholder="Enter your first/given name"
								value="<?php htmlout($firstName); ?>">
							<?php endif; ?>
						</div>
						<div>
							<label for="lastname">Last Name: </label>
							<?php if(isset($refreshedRegister) AND $lastName == "") : ?>
								<input class="fillOut" type="text" name="lastname" placeholder="Enter your last/family name"
								value="<?php htmlout($lastName); ?>">
							<?php else : ?>
								<input type="text" name="lastname" placeholder="Enter your last/family name"
								value="<?php htmlout($lastName); ?>">
							<?php endif; ?>
						</div>
						<div>
							<label for="email">Email: </label>
							<?php if(isset($refreshedRegister) AND ($email == "" OR isset($invalidEmail))) : ?>
								<input class="fillOut" type="text" name="email" placeholder="Enter your email"
								value="<?php htmlout($email); ?>"><span style="color: red">*</span>
							<?php else : ?>
								<input type="text" name="email" placeholder="Enter your email"
								value="<?php htmlout($email); ?>"><span style="color: red">*</span>
							<?php endif; ?>
						</div>
						<div>
							<label for="password1">Password: </label>
							<?php if($firstName != "" AND $lastName != "" AND $email != "" AND !isset($invalidEmail)) : ?>
								<input class="fillOut" type="password" name="password1" placeholder="Set your password"
								value="<?php htmlout($password1); ?>">
							<?php else : ?>
								<input type="password" name="password1" placeholder="Set your password"
								value="<?php htmlout($password1); ?>">							
							<?php endif; ?>
						</div>
						<div class="right">
							<input type="hidden" name="register" value="Register Account">
							<input type="submit" value="Register Account">
						</div>						
						<div>
							<label for="password2">Password: </label>
							<?php if($firstName != "" AND $lastName != "" AND $email != "" AND !isset($invalidEmail)) : ?>
								<input class="fillOut" type="password" name="password2" placeholder="Set your password"
								value="<?php htmlout($password2); ?>">
							<?php else : ?>
								<input type="password" name="password2" placeholder="Set your password"
								value="<?php htmlout($password2); ?>">							
							<?php endif; ?>
						</div>						
						<div>
							<span style="color: red">*</span>You will be sent a confirmation link that has to be accessed to activate the account.
						</div>
					</fieldset>
				</form>
			</div>
		<?php else : ?>
			<h1>You're already logged into a registered account.</h1>
		<?php endif; ?>
	</body>
</html>