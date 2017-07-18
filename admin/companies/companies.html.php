<!-- This is the HTML form used for DISPLAYING a list of COMPANIES-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Manage Companies</title>
	</head>
	<body>
		<h1>Manage Companies</h1>
		
		<?php if(isSet($_SESSION['CompanyUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['CompanyUserFeedback']); ?></b></p>
			<?php unset($_SESSION['CompanyUserFeedback']); ?>
		<?php endif; ?>
		
		<form action="" method="post">
			<div class="right">
				<?php if(isSet($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>
		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div class="left">
					<input type="submit" name="action" value="Create Company">
				</div>
			</form>
			<table>
				<caption>Registered Companies</caption>
				<tr>
					<th colspan="2">Employees</th>
					<th>Company</th>
					<th colspan="6">Booking Subscription</th>
					<th colspan="4">Booking Time Used</th>
					<th colspan="3">Billing Status (Completed Periods)</th>
					<th colspan="2">Dates</th>
					<th colspan="2">Alter Company</th>
				</tr>
				<tr>
					<th>List</th>
					<th>Amount</th>
					<th>Name</th>
					<th>Details</th>
					<th>Name</th>
					<th>Credits Given</th>
					<th>Credits Remaining</th>
					<th>Monthly Fee</th>
					<th>Over Credits Fee</th>
					<th>Last Month</th>
					<th>This Month</th>
					<th>All Time</th>
					<th>History</th>
					<th>Total Periods</th>
					<th>Set As Billed</th>
					<th>Not Set As Billed</th>
					<th>Make Inactive At</th>
					<th>Created At</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php if (isSet($companies)) : ?>
					<?php foreach ($companies as $company): ?>
						<tr>
							<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $company['id'];?>
							<form action="<?php htmlout($goto) ;?>" method="post">
								<td>
									<input type="submit" value="Employees">
									<input type="hidden" name="Company" value="<?php htmlout($company['id']); ?>">
								</td>
							</form>
								<td><?php htmlout($company['NumberOfEmployees']); ?></td>
								<td>
									<?php htmlout($company['CompanyName']); ?> 
								</td>
							<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companycredits/?Company=" . $company['id'];?>
							<form action="<?php htmlout($goto) ;?>" method="post">
								<td>
									<input type="submit" value="Credits">
									<input type="hidden" name="Company" value="<?php htmlout($company['id']); ?>">
								</td>
							</form>								
								<td><?php htmlout($company['CreditSubscriptionName']); ?></td>
								<td><?php htmlout($company['CompanyCredits']); ?></td>
								<td>
									<?php if(substr($company['CompanyCreditsRemaining'],0,1) === "-") : ?>
										<span style="color:red"><?php htmlout($company['CompanyCreditsRemaining']); ?></span>
									<?php else : ?>
										<span style="color:green"><?php htmlout($company['CompanyCreditsRemaining']); ?></span>
									<?php endif; ?>
								</td>
								<td><?php htmlout($company['CreditSubscriptionMonthlyPrice']); ?></td>
								<td><?php htmlout($company['OverCreditsFee']); ?></td>
								<td><?php htmlout($company['PreviousMonthCompanyWideBookingTimeUsed']); ?></td>
								<td><?php htmlout($company['MonthlyCompanyWideBookingTimeUsed']); ?></td>
								<td><?php htmlout($company['TotalCompanyWideBookingTimeUsed']); ?></td>
							<form action="" method="post">
								<td><input type="submit" name="action" value="Booking History"></td>
								<td><?php htmlout($company['TotalPeriods']); ?></td>
								<td><?php htmlout($company['BilledPeriods']); ?></td>								
								<td>
									<?php if($company['NotBilledPeriods'] > 0) : ?>
										<span style="color:red"><?php htmlout($company['NotBilledPeriods']); ?></span>
									<?php else : ?>
										<span style="color:green"><?php htmlout($company['NotBilledPeriods']); ?></span>
									<?php endif; ?>
								</td>								
								<?php if($company['DeletionDate'] == null) :?>
										<td>
											<p>No Date Set</p>
										</td>
								<?php elseif($company['DeletionDate'] != null) : ?>
										<td>
											<p><?php htmlout($company['DeletionDate']); ?></p>
											<input type="submit" name="action" value="Cancel Date">
										</td>
								<?php endif; ?>
								<td><?php htmlout($company['DatetimeCreated']); ?></td>							
								<td><input type="submit" name="action" value="Edit"></td>
								<td>
									<?php if(isSet($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
										<input type="submit" name="action" value="Delete">
									<?php else : ?>
										<input type="submit" name="disabled" value="Delete" disabled>
									<?php endif; ?>
								</td>
								<input type="hidden" id="CompanyName" name="CompanyName" 
									value="<?php htmlout($company['CompanyName']); ?>">
								<input type="hidden" name="id" value="<?php htmlout($company['id']); ?>">
							</form>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan=7><b>There are no active companies</b></td></tr>
				<?php endif; ?>
			</table>
			
			<?php if(isSet($unactivedcompanies)) : ?>
				<table>
					<caption>Unactivated New Companies</caption>
					<tr>
						<th>Company</th>
						<th>Date</th>
						<th colspan="2">Alter Company</th>
					</tr>				
					<tr>
						<th>Name</th>
						<th>Created</th>
						<th>Activate</th>
						<th>Delete</th>
					</tr>
					<?php foreach ($unactivedcompanies as $company): ?>
						<tr>
							<form action="" method="post">
								<td>
									<?php htmlout($company['CompanyName']); ?>
									<input type="hidden" id="CompanyName" name="CompanyName" 
									value="<?php htmlout($company['CompanyName']); ?>"> 
								</td>
								<td><?php htmlout($company['DatetimeCreated']); ?></td>							
								<td><input type="submit" name="action" value="Activate"></td>
								<td>
									<?php if(isSet($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
										<input type="submit" name="action" value="Delete">
									<?php else : ?>
										<input type="submit" name="disabled" value="Delete" disabled>
									<?php endif; ?>
								</td>
								<input type="hidden" name="id" value="<?php htmlout($company['id']); ?>">
							</form>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
			<?php if(isSet($inactivecompanies)) : ?>
				<table>
					<caption>Inactive Old Companies</caption>
					<tr>
						<th>Company</th>
						<th colspan="2">Booking Time Used</th>
						<th colspan="2">Dates</th>
						<th colspan="2">Alter Company</th>
					</tr>				
					<tr>
						<th>Name</th>
						<th>This Month</th>
						<th>All Time</th>						
						<th>Created</th>
						<th>Made Inactive</th>
						<th>Activate</th>
						<th>Delete</th>
					</tr>
					<?php foreach ($inactivecompanies as $company): ?>
						<tr>
							<form action="" method="post">
								<td>
									<?php htmlout($company['CompanyName']); ?>
									<input type="hidden" id="CompanyName" name="CompanyName" 
									value="<?php htmlout($company['CompanyName']); ?>"> 
								</td>
								<td><?php htmlout($company['MonthlyCompanyWideBookingTimeUsed']); ?></td>
								<td><?php htmlout($company['TotalCompanyWideBookingTimeUsed']); ?></td>								
								<td><?php htmlout($company['DatetimeCreated']); ?></td>
								<td><?php htmlout($company['DeletionDate']); ?></td>
								<td><input type="submit" name="action" value="Activate"></td>
								<td>
									<?php if(isSet($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
										<input type="submit" name="action" value="Delete">
									<?php else : ?>
										<input type="submit" name="disabled" value="Delete" disabled>
									<?php endif; ?>
								</td>
								<input type="hidden" name="id" value="<?php htmlout($company['id']); ?>">
							</form>
						</tr>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
		<?php else : ?>
			<tr><b>There are no companies registered in the database.</b></tr>
			<tr>
				<form action="" method="post">
					<input type="submit" name="action" value="Create Company">
				</form>
			</tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
