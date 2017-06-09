<!-- This is the HTML form used for identifying user with their BOOKING CODE -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">			
		<title>Confirm Booking Code</title>
	</head>
	<body>
		<h1>Confirm your identity with your Booking Code!</h1>
		<?php if(isset($_SESSION['confirmBookingCodeError'])) : ?>
			<?php htmlout($_SESSION['confirmBookingCodeError']); ?>
			<?php unset($_SESSION['confirmBookingCodeError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<label for="bookingCode">Submit your booking code: </label>
			<input type="text" name="bookingCode" id="bookingCode"
			value="<?php htmlout($bookingCode); ?>">
			<input type="hidden" name="action" value="confirmcode">
			<input type="submit" value="Confirm Code">
		</form>
	</body>
</html>