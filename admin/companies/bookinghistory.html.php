<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Booking History</title>
	</head>
	<body>
		<h1>Booking History</h1>
		<div>
			<form action="" method="post">
				<input type="submit" name="history" value="Return To Companies">
			</form>
		</div>
		<form action="" method="post">
			<div>
				<?php if(isset($PreviousPeriod) AND $PreviousPeriod) : ?>
					<input type="submit" name="history" value="Previous Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Previous Period" disabled>
				<?php endif; ?>
				<?php if(isset($NextPeriod) AND $NextPeriod) : ?>
					<input type="submit" name="history" value="Next Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Next Period" disabled>
				<?php endif; ?>
			</div>
			<div>
				<?php if(isset($PreviousPeriod) AND $PreviousPeriod) : ?>
					<input type="submit" name="history" value="First Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="First Period" disabled>
				<?php endif; ?>
				<?php if(isset($NextPeriod) AND $NextPeriod) : ?>
					<input type="submit" name="history" value="Last Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Last Period" disabled>
				<?php endif; ?>				
			</div>
		</form>
			<h2>For the company: <?php htmlout($CompanyName); ?></h2>
			<h3>First period starts at: <?php htmlout($displayDateTimeCreated); ?><h3>
			<h3>Currently viewing the period: <?php htmlout($BillingPeriod); ?></h3>
			<?php if(isset($bookingHistory)) : ?>
				<?php foreach($bookingHistory AS $row) : ?>
				<fieldset>
						User: <b><?php htmlout($row['UserInformation']); ?></b><br />
						Booked the meeting room: <b><?php htmlout($row['MeetingRoomName']); ?></b><br />
						For the period of: <b><?php htmlout($row['BookingPeriod']); ?></b><br />
						Using a total time of: <b><?php htmlout($row['BookingTimeUsed']); ?></b><br />
				</fieldset>
				<?php endforeach; ?>
					Producing a total booking time used this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b><br />
					<?php if($companyMinuteCreditsRemaining < 0) : ?>
						This is MORE than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
						The extra time used this period: <b><?php htmlout($displayOverCreditsTimeUsed); ?></b><br />
						Time used for calculating price: <b><?php htmlout($displayHourAmountUsedInCalculation); ?></b><br />
					<?php elseif($companyMinuteCreditsRemaining == 0) : ?>
						This is EXACTLY the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
					<?php else : ?>
						This is LESS than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
						Credits remaining at the end of the period: <b><?php htmlout($displayCompanyCreditsRemaining); ?></b><br />
					<?php endif; ?>
					This company has a monthly set subscription cost of: <b><?php htmlout($displayMonthPrice); ?></b><br />
					And an "over credits"-fee of: <b><?php htmlout($overCreditsFee); ?></b><br />
					Giving an "over credits"-cost of: <b><?php htmlout($hourAmountUsedInCalculation."h"); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <b><?php htmlout($displayOverFeeCostThisMonth); ?></b><br />
					Resulting in the total cost this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <b><?php htmlout($totalBookingCostThisMonth); ?></b><br />
			<?php else : ?>
				<b>There were no bookings completed this period.</b>
			<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>		