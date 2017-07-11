<!-- This is the HTML form used to display user information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>			
		<title>User Information</title>
	</head>
	<body>
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
		
		<h1>User Information</h1>
		
		<?php if(isset($_SESSION['normalUserFeedback'])) : ?>
			<span><b class="feedback"><?php htmlout($_SESSION['normalUserFeedback']); ?></b></span>
			<?php unset($_SESSION['normalUserFeedback']); ?>
		<?php endif; ?>

		<?php if(isset($_SESSION['loggedIn']) : ?>
			
		<?php endif; ?>
		
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>