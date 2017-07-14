<!-- This is the HTML form used for identifying user with their BOOKING CODE -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>			
		<title>Confirm Booking Code</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body>
		<h1>Confirm your identity with your Booking Code!</h1>
		
		<div class="left">
			<?php if(isset($_SESSION['confirmBookingCodeError'])) : ?>
				<?php htmlout($_SESSION['confirmBookingCodeError']); ?>
				<?php unset($_SESSION['confirmBookingCodeError']); ?>
			<?php endif; ?>
		</div>
		
		<div class="left">
			<form action="" method="post">
				<label for="bookingCode">Submit your booking code: </label>
				<input type="number" name="bookingCode" min="1" max="<?php htmlout(pow(10,BOOKING_CODE_LENGTH)-1); ?>"
				placeholder="<?php htmlout(BOOKING_CODE_LENGTH); ?> digits" value="<?php htmlout($bookingCode); ?>">
				<input type="hidden" name="action" value="confirmcode">
				<input type="submit" value="Confirm Code">
			</form>
		</div>
		<div class="left">
			<form action="" method="Post">
				<input type="submit" name="action" value="Go Back">
			</form>
		</div>
	</body>
</html>