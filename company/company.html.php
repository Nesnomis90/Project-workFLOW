<!-- This is the HTML form used for DISPLAYING COMPANY information to connected users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>	
		<style>
			label {
				width: 250px;
			}
		</style>		
		<?php if($numberOfCompanies > 1) : ?>
			<title>Manage Companies</title>
		<?php elseif($numberOfCompanies == 1) : ?>
			<title>Manage Company</title>
		<?php elseif($numberOfCompanies == 0) : ?>
			<title>Join A Company</title>
		<?php endif; ?>
	</head>
	<body onload="startTime()">
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
	
		<fieldset class="left">
			<form action="" method="post">
				<?php if($numberOfCompanies > 1) : ?>
					<legend>Manage Companies</legend>

					<div class="left">
						<label>Select The Company To Look At:</label>
						<select name="selectedCompanyToDisplay">
							<?php foreach($companiesUserWorksFor AS $company) : ?>
								<?php if($company['CompanyID'] == $selectedCompanyToDisplayID) : ?>
									<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php else : ?>
									<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
				<?php elseif($numberOfCompanies == 1) : ?>
					<legend>Manage Company</legend>
				<?php elseif($numberOfCompanies == 0) : ?>
					<legend>Join A Company</legend>
				<?php endif; ?>
			
				<?php if(isSet($companyInformation) AND !empty($companyInformation)) : ?>
					<div class="left">
						<label>Displaying Company: </label>
						<span><b><?php htmlout($companyInformation['CompanyName']); ?></b></span>
					</div>
					
					<div class="left">
						<label>Employees: </label>
						<span><a href="?employees"><?php htmlout($companyInformation['NumberOfEmployees']); ?></a></span>
					</div>

					<fieldset class="left"><legend>Booking Details:</legend>
						<div class="left">
							<label>Monthly Credits Given: </label>
							<span><b><?php htmlout($companyInformation['CompanyCredits']); ?></b></span>
						</div>

						<div class="left">
							<label>Monthly Credits Remaining: </label>
							<span><b><?php htmlout($companyInformation['CompanyCreditsRemaining']); ?></b></span>
						</div>
						
						<div class="left">
							<label>Monthly Fee: </label>
							<span><b><?php htmlout($companyInformation['CreditSubscriptionMonthlyPrice']); ?></b></span>
						</div>

						<div class="left">
							<label>Over Credits Fee: </label>
							<span><b><?php htmlout($companyInformation['OverCreditsFee']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (This Month): </label>
							<span><b><?php htmlout($companyInformation['MonthlyCompanyWideBookingTimeUsed']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (Last Month): </label>
							<span><b><?php htmlout($companyInformation['PreviousMonthCompanyWideBookingTimeUsed']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (Total): </label>
							<span><b><?php htmlout($companyInformation['TotalCompanyWideBookingTimeUsed']); ?></b></span>
						</div>					
					</fieldset>
				<?php endif; ?>
			
				<fieldset class="left"><legend>Request To Join Another Company<legend>
					<div class="left">
						<label>Select The Company To Look At: </label>
						<select name="selectedCompanyToJoin">
							<?php foreach($companies AS $company) : ?>
								<?php if($company['CompanyID'] == $selectedCompanyToJoinID) : ?>
									<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php else : ?>
									<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
				</fieldset>

				<div class="left">
					<input type="submit" name="action" value="Create A Company">
				</div>
			</form>
		</fieldset>
	</body>
</html>