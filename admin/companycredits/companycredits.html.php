<!-- This is the HTML form used for DISPLAYING a list of COMPANYCREDITS-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#companycreditstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#companycreditstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}
			
			#companycreditstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#companycreditstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#companycreditstable tr:hover{background-color:#ddd;}
			
			#companycreditstable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#companycreditstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Credits for Companies</title>
	</head>
	<body>
		<h1>Manage Credits for Companies</h1>
		<?php if(isset($_SESSION['CompanyCreditsUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['CompanyCreditsUserFeedback']); ?></b></p>
			<?php unset($_SESSION['CompanyCreditsUserFeedback']); ?>
		<?php endif; ?>						
		<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companies/"; ?>
		<?php if(isset($_GET['Company'])) :?>
			<form action="<?php htmlout($goto); ?>" method="post">
				<input type="submit" value="Return to Companies">
			</form>
		<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companycredits/"; ?>
			<form action="<?php htmlout($goto); ?>" method="post">
				<input type="submit" value="Get All Company Credits">
			</form>
		<?php endif; ?>
		<?php if($rowNum>0) :?>
			<table id="companycreditstable">
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
				<?php foreach ($companycredits as $row): ?>
					<form action="" method="post">
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
							<input type="hidden" name="CompanyID" value="<?php echo $row['TheCompanyID']; ?>">
							<input type="hidden" name="CreditsID" value="<?php echo $row['CreditsID']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php elseif(isset($_GET['Company'])) : ?>
			<tr><b>The company ID submitted does not belong to a registered company.</b></tr>
		<?php else : ?>
			<tr><b>There are no credits for any companies registered in the database. This should only occur if there are no companies registered.</b></tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>