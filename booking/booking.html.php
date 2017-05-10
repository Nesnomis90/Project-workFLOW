<!-- This is the HTML form used to display booking information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Booking Information</title>
	</head>
	<body>
		<h1>Booking Information</h1>
		<?php if(isset($_SESSION['normalBookingFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['normalBookingFeedback']); ?></b></p>
			<?php unset($_SESSION['normalBookingFeedback']); ?>
		<?php endif; ?>

		<?php //TO-DO: Fix -> include '../logout.inc.html.php'; ?>
	</body>
</html>