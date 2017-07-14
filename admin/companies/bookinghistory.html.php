<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">	
		<title>Booking History</title>
		<style>
			label {
				width: 140px;
			}
		</style>
	</head>
	<body>
		<div class="left">
			<form action="" method="post">
				<input type="submit" name="history" value="Return To Companies">
			</form>
		</div>
		
		<h1>Booking History</h1>
		
		<h2>For the company: <?php htmlout($CompanyName); ?> (Active Since: <?php htmlout($displayDateTimeCreated); ?>)</h2>
		
		<?php if(isset($periodsSummmedUp)) : ?>
		
			<h3>The company has <b style="color:red">NOT BILLED PERIODS</b>.</h3>
			
			<fieldset><legend><b>Not Billed Periods</b></legend>
			<?php $totalCostForAllPeriodsSummedUp = 0; ?>
			<?php foreach($periodsSummmedUp AS $period) : ?>
				<fieldset><legend><b><?php htmlout($period['StartDate'] . " - " . $period['EndDate']); ?></b></legend>
					<span>Credits Given: <b><?php htmlout($period['CreditsGiven']); ?></b></span><br />
					<span>Booking Time Charged: <b><?php htmlout($period['BookingTimeCharged']); ?></b></span><br />
					<span>Excess Booking Time: <b><?php htmlout($period['OverCreditsTimeExact']); ?></b></span><br />
					<span>Excess Time Charged: <b><?php htmlout($period['OverCreditsTimeCharged']); ?></b></span><br />
					<span>Cost (Subscription + Excess Booking Time): <b><?php htmlout($period['TotalBookingCostThisMonthAsParts']); ?></b></span><br />
					<span>Cost (Total): <b style="color:red"><?php htmlout($period['TotalBookingCostThisMonth']); ?></b></span>
					<?php $totalCostForAllPeriodsSummedUp += $period['TotalBookingCostThisMonthJustNumber']; ?>
				</fieldset>
			<?php endforeach; ?>
				<div>
					<span>Total Cost All Periods: <b style="color:red"><?php htmlout(convertToCurrency($totalCostForAllPeriodsSummedUp)); ?></b></span>
				</div>
			</fieldset>
		<?php endif; ?>
		
		<?php if($rightNow) : ?>
			<h2>Billing Status: Period still in progress.</h2>
		<?php elseif(isset($bookingHistory) AND sizeOf($bookingHistory) > 1 AND (!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0)) : ?>
			<h2>Billing Status: This booking has <b style="color:red">NOT BEEN BILLED</b>.</h2>
		<?php elseif(isset($bookingHistory) AND sizeOf($bookingHistory) > 1 AND isset($periodHasBeenBilled) AND $periodHasBeenBilled == 1) : ?>
			<h2>Billing Status: This booking has <b style="color:green">BEEN BILLED</b>.</h2><br />
		<?php endif; ?>
		
		<form action="" method="post">
			<div class="left">
				<?php if(isset($PreviousPeriod) AND $PreviousPeriod) : ?>
					<input type="submit" name="history" value="First Period">
					<input type="submit" name="history" value="Previous Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="First Period" disabled>				
					<input type="submit" name="disabled" value="Previous Period" disabled>
				<?php endif; ?>
			</div>
			<div class="right">
				<?php if(isset($NextPeriod) AND $NextPeriod) : ?>
					<input type="submit" name="history" value="Next Period">
					<input type="submit" name="history" value="Last Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Next Period" disabled>
					<input type="submit" name="disabled" value="Last Period" disabled>
				<?php endif; ?>				
			</div>
		</form>

		<div class="left">
			<?php if(!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0){
				$color='red';
			} elseif($periodHasBeenBilled == 1) {
				$color='green';
			} ?>
			
			<?php $bookingNumberThisPeriod = 1; ?>
			<?php if(isset($bookingHistory) AND !empty($bookingHistory)) : ?>
				<fieldset><legend>Completed Bookings during <b><?php htmlout($BillingPeriod); ?></b></legend>
					<?php foreach($bookingHistory AS $row) : ?>
						<fieldset><legend><b>Booking #<?php htmlout($bookingNumberThisPeriod); ?></b></legend>
								<label>User:</label><span><b><?php htmlout($row['UserInformation']); ?></b></span>
								<label>Booked the meeting room:</label><span><b><?php htmlout($row['MeetingRoomName']); ?></b></span>
								<label>For the period of:</label><span><b><?php htmlout($row['BookingPeriod']); ?></b></span>
								<label>Using a total time of:</label><span><b><?php htmlout($row['BookingTimeUsed']); ?></b></span>
								<label>Time used in price calculation:</label><span><b><?php htmlout($row['BookingTimeCharged']); ?></b></span>
						</fieldset>
						<?php $bookingNumberThisPeriod += 1; ?>
					<?php endforeach; ?>
			<?php elseif($rightNow) : ?>
				<span><b>There are no bookings completed so far this period.</b></span>
			<?php else : ?>
				<span><b>There were no bookings completed this period (<?php htmlout($BillingPeriod); ?>).</b></span>
			<?php endif; ?>

			<?php if($rightNow) : ?>
				<h2>Billing Status: Period still in progress.</h2>
				
				<span>Producing a total of actual booking time used so far this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b></span><br />
				<span>The total booking time charged with after including minimum booking length: <b><?php htmlout($displayTotalBookingTimeUsedInPriceCalculationsThisPeriod); ?></b></span><br />
			<?php else : ?>
				<?php if(!isset($periodHasBeenBilled) OR (isset($periodHasBeenBilled) AND $periodHasBeenBilled == 0)) : ?>
					<h2>Billing Status: This booking has <b style="color:red">NOT BEEN BILLED</b>.</h2>
				<?php elseif(isset($periodHasBeenBilled) AND $periodHasBeenBilled == 1) : ?>
					<h2>Billing Status: This booking has <b style="color:green">BEEN BILLED</b>.</h2><br />
				<?php endif; ?>
				<span>Producing a total booking time used this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b></span><br />
				<span>The total booking time charged with after including minimum booking length: <b><?php htmlout($displayTotalBookingTimeUsedInPriceCalculationsThisPeriod); ?></b></span><br />
			<?php endif; ?>
		
			<?php if($companyMinuteCreditsRemaining < 0) : ?>
				<span>This is <b style="color:<?php htmlout($color); ?>">MORE</b> than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b></span><br />
				<span>The extra time used this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayOverCreditsTimeUsed); ?></b></span><br />
				<span>Time used for calculating price: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b></span><br />
				<span>The company has an "over credits"-fee of: <b><?php htmlout($overCreditsFee); ?></b></span><br />
				<span>Giving an "over credits"-cost of: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayOverFeeCostThisMonth); ?></b></span><br />
			<?php elseif($companyMinuteCreditsRemaining == 0) : ?>
				<span>This is <b style="color:green">EXACTLY</b> the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b></span><br />
			<?php else : ?>
				<span>This is <b style="color:green">LESS</b> than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b></span><br />
				<?php if($rightNow) : ?>
					<span>Credits remaining this period: <b><?php htmlout($displayCompanyCreditsRemaining); ?></b></span><br />
				<?php else : ?>
					<span>Credits remaining at the end of the period: <b><?php htmlout($displayCompanyCreditsRemaining); ?></b></span><br />
				<?php endif; ?>
			<?php endif; ?>
			
			<span>This company has a monthly set subscription cost of: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayMonthPrice); ?></b></span><br />
			<?php if($rightNow) : ?>
				<span>Resulting in the total cost so far this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <b style="color:<?php htmlout($color); ?>"><?php htmlout($totalBookingCostThisMonth); ?></b></span>
			<?php else : ?>
				<span>Resulting in the total cost this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <b style="color:<?php htmlout($color); ?>"><?php htmlout($totalBookingCostThisMonth); ?></b></span>
			<?php endif; ?>
		</div>
		
		<?php if(!$rightNow AND (!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0)) : ?>
			<form action="" method="post">
				<label class="description" for="billingDescription">Billing Description: </label>
				<textarea name="billingDescription" id="billingDescription" rows="4" cols="50"
				placeholder="Type in any additional information you'd like to see when viewing this period later."></textarea>
				<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
				<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
				<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
				<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>"><br />
				<input type="submit" name="history" value="Set As Billed">
			</form>
		<?php elseif(!$rightNow AND $periodHasBeenBilled == 1) : ?>
			<label class="description" for="billingDescriptionDisabled">Billing Description: </label>
			<textarea name="billingDescriptionDisabled" id="billingDescriptionDisabled" 
			rows="4" cols="50" disabled><?php htmlout($billingDescription); ?></textarea>
		<?php endif; ?>
		</fieldset>

	<div class="left"><a href="..">Return to CMS home</a></div>
		
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>		