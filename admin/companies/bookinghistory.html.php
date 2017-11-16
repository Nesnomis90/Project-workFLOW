<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Booking History</title>
		<style>
			label {
				width: 140px;
			}
			.period {
				width: 210px;
			}
			.notBilled {
				width: 300px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<div class="left">
			<h1>Booking History</h1>

			<h2>For the company: <?php htmlout($CompanyName); ?> (Active Since: <?php htmlout($displayDateTimeCreated); ?>)</h2>
		</div>

		<?php if(isSet($mergedCompanies) AND $mergedCompanies) : ?>
		<div class="left">
			<fieldset><legend><b>Merged Companies</b></legend>
				<div class="left">
					<span>This company has bookings transferred from other companies due to a company merge.</span>
					<?php if($mergeNumber == 0) : ?>
						<span style="clear:both;">Currently only displaying bookings made for the selected company. "Not Billed Periods" always includes transferred bookings.</span>
					<?php else : ?>
						<span style="clear:both;">Currently only displaying bookings transferred from another company (ID=<?php htmlout($mergeNumber); ?>). "Not Billed Periods" always includes transferred bookings.</span>
						<form action="" method="post">
							<input type="hidden" name="changeToMergeNumber" value="0">
							<input type="submit" value="Change Back To Default">
						</form>
					<?php endif; ?>
				</div>

				<div class="left">
					<?php foreach($_SESSION['BookingHistoryCompanyInfo']['CompanyMergeNumbers'] AS $availableMergeNumbers) : ?>
						<form action="" method="post">
							<input type="hidden" name="changeToMergeNumber" value="<?php htmlout($availableMergeNumbers[0]); ?>">
							<input type="submit" value="Look at periods from the merged company (ID=<?php htmlout($availableMergeNumbers[0]); ?>)">
						</form>
					<?php endforeach; ?>
				</div>
				<?php if(isSet($_SESSION['BookingHistoryDisplayWithMerged'])) : ?>
					<div class="left">
						<form action="" method="post">
							<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
							<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
							<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
							<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>">
							<input type="submit" name="history" value="Exclude Transferred Bookings">
						</form>
					</div>
				<?php else : ?>
					<div class="left">
						<form action="" method="post">
							<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
							<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
							<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
							<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>">
							<input type="submit" name="history" value="Include Transferred Bookings">
						</form>
					</div>
				<?php endif; ?>
			</fieldset>
		</div>
		<?php endif; ?>

		<?php if(isSet($lookingAtASpecificMergedPeriod) AND $lookingAtASpecificMergedPeriod) : ?>
			<div class="left">
				<form action="" method="post">
					<input type="submit" name="history" value="Display Default">
				</form>
			</div>
		<?php endif; ?>

		<?php if(isSet($periodsSummmedUp)) : ?>

			<div class="left">
				<h3>The company has <b style="color:red">NOT BILLED PERIODS</b>.</h3>
			</div>

			<div class="left">
				<fieldset><legend><b>Not Billed Periods</b></legend>
				<?php $totalCostForAllPeriodsSummedUp = 0; ?>
				<?php foreach($periodsSummmedUp AS $period) : ?>
					<fieldset><legend><b><?php htmlout($period['DisplayStartDate'] . " up to " . $period['DisplayEndDate']); ?></b></legend>
						<form action="" method="post">
							<div class="left">
								<?php if($displayMergeStatus) : ?>
									<label class="notBilled">Period Info:</label><span><b><?php htmlout($period['MergeStatus']); ?></b></span>
								<?php endif; ?>
								<label class="notBilled">Credits Given:</label><span><b><?php htmlout($period['CreditsGiven']); ?></b></span>
								<label class="notBilled">Booking Time Charged:</label><span><b><?php htmlout($period['BookingTimeCharged']); ?></b></span>
								<label class="notBilled">Excess Booking Time:</label><span><b><?php htmlout($period['OverCreditsTimeExact']); ?></b></span>
								<label class="notBilled">Excess Time Charged:</label><span><b><?php htmlout($period['OverCreditsTimeCharged']); ?></b></span>
								<label class="notBilled">Cost (Subscription + Excess Time + Orders):</label><span><b><?php htmlout($period['TotalBookingCostThisMonthAsParts']); ?></b></span>
								<label class="notBilled">Cost (Total):</label><span><b style="color:red"><?php htmlout($period['TotalBookingCostThisMonth']); ?></b></span>
								<?php $totalCostForAllPeriodsSummedUp += $period['TotalBookingCostThisMonthJustNumber']; ?>
							</div>

							<div class="left">
								<input type="hidden" name="startDate" value="<?php htmlout($period['StartDate']); ?>">
								<input type="hidden" name="endDate" value="<?php htmlout($period['EndDate']); ?>">
								<input type="hidden" name="mergeNumber" value="<?php htmlout($period['MergeNumber']); ?>">
								<input type="submit" name="history" value="Go To This Period">
							</div>
						</form>
					</fieldset>
				<?php endforeach; ?>
					<div class="fieldsetIndentReplication">
						<label class="notBilled">Total Cost All Periods:</label><span><b style="color:red"><?php htmlout(convertToCurrency($totalCostForAllPeriodsSummedUp)); ?></b></span>
					</div>
				</fieldset>
			</div>

		<?php endif; ?>

		<div class="left">
			<?php if($rightNow) : ?>
				<h2>Billing Status: Period still in progress.</h2>
			<?php elseif(isSet($bookingHistory) AND sizeOf($bookingHistory) > 1 AND (!isSet($periodHasBeenBilled) OR $periodHasBeenBilled == 0)) : ?>
				<h2>Billing Status: This period has <b style="color:red">NOT BEEN BILLED</b>.</h2>
			<?php elseif(isSet($bookingHistory) AND sizeOf($bookingHistory) > 1 AND isSet($periodHasBeenBilled) AND $periodHasBeenBilled == 1) : ?>
				<h2>Billing Status: This period has <b style="color:green">BEEN BILLED</b>.</h2><br />
			<?php endif; ?>
		</div>

		<form action="" method="post">
			<div class="left">
				<?php if(isSet($PreviousPeriod) AND $PreviousPeriod) : ?>
					<input type="submit" name="history" value="First Period">
					<input type="submit" name="history" value="Previous Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="First Period" disabled>
					<input type="submit" name="disabled" value="Previous Period" disabled>
				<?php endif; ?>
			</div>
			<div class="right">
				<?php if(isSet($NextPeriod) AND $NextPeriod) : ?>
					<input type="submit" name="history" value="Next Period">
					<input type="submit" name="history" value="Last Period">
				<?php else : ?>
					<input type="submit" name="disabled" value="Next Period" disabled>
					<input type="submit" name="disabled" value="Last Period" disabled>
				<?php endif; ?>
			</div>
		</form>

		<div class="left">
			<?php if(empty($periodHasBeenBilled)){
				$color='red';
			} elseif($periodHasBeenBilled == 1) {
				$color='green';
			} ?>

			<?php $bookingNumberThisPeriod = 1; ?>
			<?php if(!empty($bookingHistory)) : ?>
				<fieldset><legend>Completed Bookings during <b><?php htmlout($BillingPeriod); ?></b></legend>
					<?php foreach($bookingHistory AS $row) : ?>
						<fieldset><legend><b>Booking #<?php htmlout($bookingNumberThisPeriod); ?></b></legend>
								<label class="period">User:</label><span><b><?php htmlout($row['UserInformation']); ?></b></span>
								<label class="period">Booked the meeting room:</label><span><b><?php htmlout($row['MeetingRoomName']); ?></b></span>
								<label class="period">For the period of:</label><span><b><?php htmlout($row['BookingPeriod']); ?></b></span>
								<label class="period">Using a total time of:</label><span><b><?php htmlout($row['BookingTimeUsed']); ?></b></span>
								<label class="period">Time used in price calculation:</label><span><b><?php htmlout($row['BookingTimeCharged']); ?></b></span>
								<?php if(!empty($row['CancelMessage'])) : ?>
									<label class="period">Ended Early Message:</label><span><b><?php htmlout($row['CancelMessage']); ?></b></span>
								<?php endif; ?>
								<?php if(!empty($row['AdminNote'])) : ?>
									<label class="period" style="color: red;">Admin Note (Booking):</label><span style="white-space: pre-wrap; color:red;"><b><?php htmlout($row['AdminNote']); ?></b></span>
								<?php endif; ?>
								<?php if(!empty($row['TotalOrderCost'])) : ?>
									<label class="period" style="color: red;">Order Cost:</label><span style="color: red;"><b><?php htmlout($row['TotalOrderCost']); ?></b></span>
								<?php endif; ?>
								<?php if(!empty($row['OrderAdminNote'])) : ?>
									<label class="period" style="color: red;">Admin Note (Order):</label><span style="white-space: pre-wrap; color:red;"><b><?php htmlout($row['OrderAdminNote']); ?></b></span>
								<?php endif; ?>
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
				<?php if(empty($periodHasBeenBilled)) : ?>
					<h2>Billing Status: This period has <b style="color:red">NOT BEEN BILLED</b>.</h2>
				<?php elseif($periodHasBeenBilled == 1) : ?>
					<h2>Billing Status: This period has <b style="color:green">BEEN BILLED</b>.</h2><br />
				<?php endif; ?>
				<span>Producing a total booking time used this period: <b><?php htmlout($displayTotalBookingTimeThisPeriod); ?></b></span><br />
				<span>The total booking time charged with after including minimum booking length: <b><?php htmlout($displayTotalBookingTimeUsedInPriceCalculationsThisPeriod); ?></b></span><br />
			<?php endif; ?>

			<?php if($companyCreditsHistoryPeriodExists) : ?>
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
				<span>The total cost of all orders this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span><br />
				<?php if($rightNow) : ?>
					<span>Resulting in the total cost, including orders, so far this period of: <b><?php htmlout($periodCost); ?></b> = <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalPeriodCost); ?></b></span>
				<?php else : ?>
					<span>Resulting in the total cost, including orders, this period of: <b><?php htmlout($periodCost); ?></b> = <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalPeriodCost); ?></b></span>
				<?php endif; ?>
			<?php else : ?>
					<span>There is no credits information saved for this company from this period.</span><br />
					<span>This means we don't know the free booking time given, the subscription cost nor the cost when going over the given booking time.</span><br />
					<span>Therefore the following numbers are not complete and will require some manual research and calculations.</span><br />
					<?php if(!empty($bookingHistory) AND $companyMinuteCreditsRemaining < 0 AND $totalOrderCostThisPeriod > 0) : ?>
						<span>The extra time used this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayOverCreditsTimeUsed); ?></b></span><br />
						<span>Time used for calculating price: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b></span><br />
						<span>The company has an "over credits"-fee of: <b>N/A</b></span><br />
						<span>Giving an "over credits"-cost of: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b>*<b>N/A</b></span><br />
						<span>This company has a monthly set subscription cost of: <b style="color:<?php htmlout($color); ?>">N/A</b></span><br />
						<span>The total cost of all orders this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span><br />	
						<span>Resulting in the total cost, including orders, this period of: <b>(<?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?>*Over Credits Fee) + Monthly Subscription + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b> = <b style="color:<?php htmlout($color); ?>">N/A + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span>
					<?php elseif(!empty($bookingHistory) AND $companyMinuteCreditsRemaining < 0 AND $totalOrderCostThisPeriod == 0) : ?>
						<span>The extra time used this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayOverCreditsTimeUsed); ?></b></span><br />
						<span>Time used for calculating price: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b></span><br />
						<span>The company has an "over credits"-fee of: <b>N/A</b></span><br />
						<span>Giving an "over credits"-cost of: <b><?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?></b>*<b>N/A</b></span><br />
						<span>This company has a monthly set subscription cost of: <b style="color:<?php htmlout($color); ?>">N/A</b></span><br />
						<span>The total cost of all orders this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span><br />	
						<span>Resulting in the total cost this period of: <b>(<?php htmlout($displayTotalBookingTimeChargedWithAfterCredits); ?>*Over Credits Fee) + Monthly Subscription + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b> = <b style="color:<?php htmlout($color); ?>">N/A</b></span>
					<?php elseif(!empty($bookingHistory) AND $companyMinuteCreditsRemaining == 0 AND $totalOrderCostThisPeriod > 0) : ?>
						<span>This company has a monthly set subscription cost of: <b style="color:<?php htmlout($color); ?>">N/A</b></span><br />
						<span>The total cost of all orders this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span><br />	
						<span>Resulting in the total cost this period of: <b>Monthly Subscription + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b> = <b style="color:<?php htmlout($color); ?>">N/A + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span>
					<?php elseif(empty($bookingHistory) OR (!empty($bookingHistory) AND $companyMinuteCreditsRemaining == 0 AND $totalOrderCostThisPeriod == 0)) : ?>
						<span>This company has a monthly set subscription cost of: <b style="color:<?php htmlout($color); ?>">N/A</b></span><br />
						<span>The total cost of all orders this period: <b style="color:<?php htmlout($color); ?>"><?php htmlout($displayTotalOrderCostThisPeriod); ?></b></span><br />	
						<span>Resulting in the total cost this period of: <b>Monthly Subscription + <?php htmlout($displayTotalOrderCostThisPeriod); ?></b> = <b style="color:<?php htmlout($color); ?>">N/A</b></span>
					<?php endif; ?>
			<?php endif; ?>

			<div class="left">
				<?php if(!$rightNow AND empty($periodHasBeenBilled)) : ?>
					<form action="" method="post">
						<label class="description" for="billingDescription">Billing Description: </label>
						<textarea name="billingDescription" rows="4" cols="50" placeholder="Type in any additional information you'd like to see when viewing this period later."></textarea>
						<input type="hidden" name="nextPeriod" value="<?php htmlout($NextPeriod); ?>">
						<input type="hidden" name="previousPeriod" value="<?php htmlout($PreviousPeriod); ?>">
						<input type="hidden" name="billingStart" value="<?php htmlout($BillingStart); ?>">
						<input type="hidden" name="billingEnd" value="<?php htmlout($BillingEnd); ?>"><br />
						<input type="submit" name="history" value="Set As Billed">
					</form>
				<?php elseif(!$rightNow AND $periodHasBeenBilled == 1) : ?>
					<label class="description" for="billingDescriptionDisabled">Billing Description: </label>
					<textarea name="billingDescriptionDisabled" rows="8" cols="100" disabled><?php htmlout($billingDescription); ?></textarea>
				<?php endif; ?>
			</div>
			</fieldset>
		</div>
	</body>
</html>		