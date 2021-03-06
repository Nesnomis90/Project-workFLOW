<!-- This is the HTML form used for DISPLAYING a list of COMPANYCREDITS-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Credits for Companies</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Credits for Companies</h1>

		<?php if(isSet($_SESSION['CompanyCreditsUserFeedback'])) : ?>
			<span><b class="feedback"><?php htmlout($_SESSION['CompanyCreditsUserFeedback']); ?></b></span>
			<?php unset($_SESSION['CompanyCreditsUserFeedback']); ?>
		<?php endif; ?>

		<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companies/"; ?>
		<?php if(isSet($_GET['Company'])) :?>
			<form action="<?php htmlout($goto); ?>" method="post">
				<input type="submit" value="Return to Companies">
			</form>
			<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companycredits/"; ?>
			<form action="<?php htmlout($goto); ?>" method="post">
				<input type="submit" value="Get All Company Credits">
			</form>
		<?php endif; ?>

		<table class="myTable">
			<caption>Company Credits</caption>
			<tr>
				<th colspan="3">Company</th>
				<th colspan="6">Credits</th>
				<th colspan="2">Date</th>
				<th>Alter Company Credits</th>
			</tr>
			<tr>
				<th>Name</th>
				<th>Billing Month Start</th>
				<th>Billing Month End</th>
				<th>Name</th>
				<th>Description</th>
				<th>Monthly Free Booking Time</th>
				<th>Given Alternative Credits</th>
				<th>Monthly Subscription Cost</th>
				<th>Over Credits Fee</th>
				<th>Last Modified</th>
				<th>Added</th>
				<th>Edit</th>
			</tr>
		<?php if($rowNum>0) :?>
				<?php foreach ($companycredits as $row): ?>
					<form method="post">
						<tr>
							<td>
								<?php htmlout($row['CompanyName']); ?>
								<input type="hidden" name="CompanyName" value="<?php htmlout($row['CompanyName']); ?>">
							</td>
							<td><?php htmlout($row['CompanyBillingMonthStart']); ?></td>
							<td><?php htmlout($row['CompanyBillingMonthEnd']); ?></td>
							<td>
								<?php htmlout($row['CreditsName']); ?>
								<input type="hidden" name="CreditsName" value="<?php htmlout($row['CreditsName']); ?>">
							</td>
							<td><?php htmlout($row['CreditsDescription']); ?></td>
							<td><?php htmlout($row['CreditsGiven']); ?></td>
							<td><?php htmlout($row['CompanyUsingAlternativeCreditsGiven']); ?></td>
							<td><?php htmlout($row['CreditsMonthlyPrice']); ?></td>
							<td><?php htmlout($row['CreditsOverCreditsFee']); ?></td>
							<td><?php htmlout($row['DateTimeLastModified']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<input type="hidden" name="CompanyID" value="<?php htmlout($row['TheCompanyID']); ?>">
							<input type="hidden" name="CreditsID" value="<?php htmlout($row['CreditsID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
		<?php elseif(isSet($_GET['Company'])) : ?>
			<tr><td colspan="12"><b>The company submitted does not exist or isn't linked to a credits.</b></td></tr>
		<?php else : ?>
			<tr><td colspan="12"><b>There are no credits for any companies registered in the database. This should only occur if there are no companies registered.</b></td></tr>
		<?php endif; ?>
		</table>
	</body>
</html>
