<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#billingDescriptionDisabled {
				vertical-align: top;
			}
			#billingDescription {
				vertical-align: top;
			}
		</style>	
		<title>Booking History</title>
	</head>
	<body>
		<div>
			<form action="" method="post">
				<input type="submit" name="history" value="Return To Companies">
			</form>
		</div>
		<h1>Booking History</h1>
		<h2>For the company: <?php htmlout($CompanyName); ?> (Active Since: <?php htmlout($displayDateTimeCreated); ?>)</h2>
		<?php if(isset($periodsSummmedUp)) : ?>
			<h3>The company has <span style="color:red">NOT BILLED PERIODS</span>.</h3>
			<fieldset><legend><b>Not Billed Periods</b></legend>
			<?php $totalCostForAllPeriodsSummedUp = 0; ?>
			<?php foreach($periodsSummmedUp AS $period) : ?>
				<fieldset><legend><b><?php htmlout($period['StartDate'] . " - " . $period['EndDate']); ?></b></legend>
					Credits Given: <b><?php htmlout($period['CreditsGiven']); ?></b><br />
					Booking Time Charged: <b><?php htmlout($period['BookingTimeCharged']); ?></b><br />
					Excess Booking Time: <b><?php htmlout($period['OverCreditsTimeExact']); ?></b><br />
					Excess Time Charged: <b><?php htmlout($period['OverCreditsTimeCharged']); ?></b><br />
					Cost (Subscription + Excess Booking Time): <b><?php htmlout($period['TotalBookingCostThisMonthAsParts']); ?></b><br />
					Cost (Total): <b><span style="color:red"><?php htmlout($period['TotalBookingCostThisMonth']); ?></span></b><br />
					<?php $totalCostForAllPeriodsSummedUp += $period['TotalBookingCostThisMonthJustNumber']; ?>
				</fieldset>
			<?php endforeach; ?>
				Total Cost All Periods: <span style="color:red"><b><?php htmlout(convertToCurrency($totalCostForAllPeriodsSummedUp)); ?></span></b><br />
			</fieldset>
		<?php endif; ?>
		
		<?php if($rightNow) : ?>
			<h2>Billing Status: Period still in progress.</h2>
		<?php elseif(!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0) : ?>
			<h2>Billing Status: This booking has <span style="color:red">NOT BEEN BILLED</span>.</h2>
		<?php elseif($periodHasBeenBilled == 1) : ?>
			<h2>Billing Status: This booking has <span style="color:green">BEEN BILLED</span>.</h2><br />
		<?php endif; ?>

		<form action="" method="post">
			<div id="ChooseEarlierPeriod">
				<?php if(isset($PreviousPeriod) AND $PreviousPeriod) : ?>
					<input type="submit" name="history" value="Previous Period">
					<br />
					<input type="submit" name="history" value="First Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Previous Period" disabled>
					<br />
					<input type="submit" name="disabled" value="First Period" disabled>
				<?php endif; ?>
			</div>
			<div id="ChooseLaterPeriod">
				<?php if(isset($NextPeriod) AND $NextPeriod) : ?>
					<input type="submit" name="history" value="Next Period">
					<br />
					<input type="submit" name="history" value="Last Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Next Period" disabled>
					<br />
					<input type="submit" name="disabled" value="Last Period" disabled>
				<?php endif; ?>			
			</div>
		</form>		
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
							User: <b><?php htmlout($row['UserInformation']); ?></b><br />
							Booked the meeting room: <b><?php htmlout($row['MeetingRoomName']); ?></b><br />
							For the period of: <b><?php htmlout($row['BookingPeriod']); ?></b><br />
							Using a total time of: <b><?php htmlout($row['BookingTimeUsed']); ?></b><br />
							Time used in price calculation: <b><?php htmlout($row['BookingTimeCharged']); ?></b><br />
					</fieldset>
					<?php $bookingNumberThisPeriod += 1; ?>
				<?php endforeach; ?>
		<?php elseif($rightNow) : ?>
			<b>There are no bookings completed so far this period.</b><br />
		<?php else : ?>
			<b>There were no bookings completed this period.</b><br />
		<?php endif; ?>

		<?php if($rightNow) : ?>
			<h2>Billing Status: Period still in progress.</h2>
			Producing a total of actual booking time used so far this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b><br />
			The total booking time charged with after including minimum booking length: <b><?php htmlout($displayTotalBookingTimeUsedInPriceCalculationsThisPeriod); ?></b><br />
		<?php else : ?>
			<?php if(!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0) : ?>
				<h2>Billing Status: This booking has <span style="color:red">NOT BEEN BILLED</span>.</h2>
			<?php elseif($periodHasBeenBilled == 1) : ?>
				<h2>Billing Status: This booking has <span style="color:green">BEEN BILLED</span>.</h2><br />
			<?php endif; ?>
			Producing a total booking time used this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b><br />
			The total booking time charged with after including minimum booking length: <b><?php htmlout($displayTotalBookingTimeUsedInPriceCalculationsThisPeriod); ?></b><br />
		<?php endif; ?>
	
		<?php if($companyMinuteCreditsRemaining < 0) : ?>
			This is <span style="color:<?php htmlout($color); ?>"><b>MORE</b></span> than the credit given this period: <b><?php htmlout($displayCompanyCredits); ?></b><br />
			The extra time used this period: <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($displayOverCreditsTimeUsed); ?></b></span><br />
			<?php if($hourAmountUsedInCalculation!="") : ?>
				Time used for calculating price: <b><?php htmlout($displayHourAmountUsedInCalculation); ?></b><br />
			<?php else : ?>
				Time used for calculating price: <b><?php htmlout($actualTimeOverCreditsInMinutes."m"); ?></b><br />
			<?php endif; ?>	
			The company has an "over credits"-fee of: <b><?php htmlout($overCreditsFee); ?></b><br />
			<?php if($hourAmountUsedInCalculation!="") : ?>
				Giving an "over credits"-cost of: <b><?php htmlout($displayHourAmountUsedInCalculation); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($displayOverFeeCostThisMonth); ?></b></span><br />
			<?php else : ?>
				Giving an "over credits"-cost of: <b><?php htmlout($actualTimeOverCreditsInMinutes."m"); ?></b>*<b><?php htmlout($overCreditsFee); ?></b> = <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($displayOverFeeCostThisMonth); ?></b></span><br />
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
		
		This company has a monthly set subscription cost of: <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($displayMonthPrice); ?></b></span><br />
		<?php if($rightNow) : ?>
			Resulting in the total cost so far this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <span style="color:red"><b><?php htmlout($totalBookingCostThisMonth); ?></b></span><br />
		<?php else : ?>
			Resulting in the total cost this period of: <b><?php htmlout($bookingCostThisMonth); ?></b> = <span style="color:<?php htmlout($color); ?>"><b><?php htmlout($totalBookingCostThisMonth); ?></b></span><br />
		<?php endif; ?>
		<br />
		<?php if(!$rightNow AND (!isset($periodHasBeenBilled) OR $periodHasBeenBilled == 0)) : ?>
			<form action="" method="post">
				<label for="billingDescription">Billing Description: </label>
				<textarea name="billingDescription" id="billingDescription" rows="4" cols="50"
				placeholder="Type in any additional information you'd like to see when viewing this period later."></textarea>
				<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
				<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
				<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
				<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>"><br />
				<input type="submit" name="history" value="Set As Billed">
			</form>
		<?php elseif(!$rightNow AND $periodHasBeenBilled == 1) : ?>
			<label for="billingDescriptionDisabled">Billing Description: </label>
			<textarea name="billingDescriptionDisabled" id="billingDescriptionDisabled" 
			rows="4" cols="50" disabled><?php htmlout($billingDescription); ?></textarea>
		<?php endif; ?>
		</fieldset>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>		