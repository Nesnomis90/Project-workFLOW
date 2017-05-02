<!-- This is the HTML form used to display user information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>User Information</title>
	</head>
	<body>
		<h1>User Information</h1>
		<?php if(isset($_SESSION['normalUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['normalUserFeedback']); ?></b></p>
			<?php unset($_SESSION['normalUserFeedback']); ?>
		<?php endif; ?>

		<?php //TO-DO: Fix -> include '../logout.inc.html.php'; ?>
	</body>
</html>
