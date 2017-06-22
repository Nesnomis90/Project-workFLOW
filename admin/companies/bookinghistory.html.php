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
			<?php if(isset($bookingHistory) AND !empty($bookingHistory)) : ?>
				<?php foreach($bookingHistory AS $row) : ?>
				<fieldset>
						User: <b><?php htmlout($row['UserInformation']); ?></b><br />
						Booked the meeting room: <b><?php htmlout($row['MeetingRoomName']); ?></b><br />
						For the period of: <b><?php htmlout($row['BookingPeriod']); ?></b><br />
						Using a total time of: <b><?php htmlout($row['BookingTimeUsed']); ?></b><br />
						Time used in price calculation: <b><?php htmlout($row['BookingTimeCharged']); ?></b><br />
				</fieldset>
				<?php endforeach; ?>
			<?php elseif($rightNow) : ?>
				<b>There are no bookings completed so far this period.</b><br />
			<?php else : ?>
				<b>There were no bookings completed this period.</b><br />
			<?php endif; ?>
			
			<?php if($rightNow) : ?>
				Producing a total booking time used so far this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b><br />
			<?php else : ?>
				Producing a total booking time used this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b><br />
			<?php endif; ?>
			
			<?php if($companyMinuteCreditsRemaining < 0) : ?>
				<?php if(!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0){
					$color='red';
				} elseif($periodHasBeenBilled == 1) {
					$color='green';
				} ?>
				This is <span style="color:<?php htmlout($color); ?>"><b>MORE</b></span> than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
				The extra time used this period: <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($displayOverCreditsTimeUsed); ?></b></span><br />
				<?php if($hourAmountUsedInCalculation!="") : ?>
					Time used for calculating price: <b><?php htmlout($displayHourAmountUsedInCalculation); ?></b><br />
				<?php else : ?>
					Time used for calculating price: <b><?php htmlout($actualTimeOverCreditsInMinutes."m"); ?></b><br />
				<?php endif; ?>	
				The company has an "over credits"-fee of: <b><?php htmlout($overCreditsFee); ?></b><br />
				<?php if($hourAmountUsedInCalculation!="") : ?>
					Giving an "over credits"-cost of: <b><?php htmlout($displayHourAmountUsedInCalculation); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <b><?php htmlout($displayOverFeeCostThisMonth); ?></b><br />
				<?php else : ?>
					Giving an "over credits"-cost of: <b><?php htmlout($actualTimeOverCreditsInMinutes."m"); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <b><?php htmlout($displayOverFeeCostThisMonth); ?></b><br />
				<?php endif; ?>
			<?php elseif($companyMinuteCreditsRemaining == 0) : ?>
				This is <span style="color:green"><b>EXACTLY</b></span> the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
			<?php else : ?>
				This is <span style="color:green"><b>LESS</b></span> than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
				<?php if($rightNow) : ?>
					Credits remaining this period: <b><?php htmlout($displayCompanyCreditsRemaining); ?></b><br />
				<?php else : ?>
					Credits remaining at the end of the period: <b><?php htmlout($displayCompanyCreditsRemaining); ?></b><br />
				<?php endif; ?>
			<?php endif; ?>
			This company has a monthly set subscription cost of: <b><?php htmlout($displayMonthPrice); ?></b><br />
			<?php if($rightNow) : ?>
				Resulting in the total cost so far this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <span style="color:red"><b><?php htmlout($totalBookingCostThisMonth); ?></b></span><br />
			<?php else : ?>
				Resulting in the total cost this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($totalBookingCostThisMonth); ?></b></span><br />
			<?php endif; ?>
			<br /><h2>Billing Status:</h2>
			<?php if(!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0) : ?>
				<form action="" method="post">
					This booking has <span style="color:red">NOT BEEN BILLED</span>.
					<input type="submit" name="action" value="Set As Billed"><br />
					<textarea name="billingDescription" rows="4" cols="50"
					placeholder="Type in any additional information you'd like to see when viewing this period later."></textarea>
					<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
					<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
					<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
					<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>">
				</form>
			<?php elseif($periodHasBeenBilled == 1) : ?>
				This booking has <span style="color:green">BEEN BILLED</span>.<br />
				<label for="billingDescription">Billing Description: </label>
				<textarea rows="4" cols="50" disabled><?php htmlout($billingDescription); ?></textarea>
			<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>		