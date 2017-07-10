<?php include_once $_SERVER['DOCUMENT_ROOT'] .
'/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Log In</title>
	</head>
	<body>
		<h1>Log In</h1>
		<div class="left">
			<b>Please log in to view the page that you requested</b>
		</div>
		
		<div class="left">
		<?php if (isset($_SESSION['loginError'])): ?>
			<span class="warning"><b><?php htmlout($_SESSION['loginError']); ?></b></span>
			<?php unset($_SESSION['loginError']); ?>
		<?php endif; ?>
		</div>
		
		<div>
			<form action="" method="post">
				<div>
					<?php if(!isset($_SESSION['loginEmailSubmitted'])){
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
			<span><a href="/admin/">Return to CMS home</a></span>
		</div>
	</body>
</html>
