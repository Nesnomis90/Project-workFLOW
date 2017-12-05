<!-- This is the HTML form used for DISPLAYING an overview of a COMPANY's BOOKING HISTORY in detail with a focus on PERIODS ONLY-->
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
			.notBilled {
				width: 300px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<div class="left">
			<h1>Booking History</h1>
		</div>

		<div class="left">
			<form method="post">
				<input type="submit" name="history" value="Display Default">
			</form>
		</div>

		<?php if(isSet($displayAllPeriodsFromMergedNumber)) : ?>
		<div class="left">
			<fieldset><legend><b>All Periods</b></legend>
			<?php $totalCostForAllPeriodsSummedUp = 0; ?>
			<?php foreach($displayAllPeriodsFromMergedNumber AS $period) : ?>
				<fieldset><legend><b><?php htmlout($period['DisplayStartDate'] . " up to " . $period['DisplayEndDate']); ?></b></legend>
					<form method="post">
						<div class="left">
							<?php if($period['BillingStatus'] == 0) : ?>
								<?php $color = "red"; ?>
								<label class="notBilled">Billing Status: </label><span style="color: red;">This period has NOT BEEN SET AS BILLED.</span>
								<?php $totalCostForAllPeriodsSummedUp += $period['TotalBookingCostThisMonthJustNumber']; ?>
							<?php else : ?>
								<?php $color = "green"; ?>
								<label class="notBilled">Billing Status: </label><span style="color: green;">This period has BEEN SET AS BILLED.</span>
							<?php endif; ?>
							<label class="notBilled">Period Info:</label><span><b><?php htmlout($period['MergeStatus']); ?></b></span>
							<label class="notBilled">Credits Given:</label><span><b><?php htmlout($period['CreditsGiven']); ?></b></span>
							<label class="notBilled">Booking Time Charged:</label><span><b><?php htmlout($period['BookingTimeCharged']); ?></b></span>
							<label class="notBilled">Excess Booking Time:</label><span><b><?php htmlout($period['OverCreditsTimeExact']); ?></b></span>
							<label class="notBilled">Excess Time Charged:</label><span><b><?php htmlout($period['OverCreditsTimeCharged']); ?></b></span>
							<label class="notBilled">Cost (Subscription + Excess Time + Orders):</label><span><b><?php htmlout($period['TotalBookingCostThisMonthAsParts']); ?></b></span>
							<label class="notBilled">Cost (Total):</label><span><b style="color:<?php htmlout($color); ?>"><?php htmlout($period['TotalBookingCostThisMonth']); ?></b></span>
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
	</body>
</html>		