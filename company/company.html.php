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
			<?php if(isSet($selectedCompanyToDisplayID)) : ?>
				label {
					width: 230px;
				}
			<?php else : ?>
				label {
					width: 130px;
				}				
			<?php endif; ?>
		</style>		
		<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 1) : ?>
			<title>Manage Companies</title>
		<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 1) : ?>
			<title>Manage Company</title>
		<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 0) : ?>
			<title>Set Up A Company Connection</title>
		<?php endif; ?>
	</head>
	<body onload="startTime()">
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>
	
	<?php if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND isSet($_SESSION['LoggedInUserID']) AND !empty($_SESSION['LoggedInUserID']) AND !isSet($noAccess)) : ?>
	
		<fieldset class="left">
			<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 1) : ?>
				<legend>Manage Companies</legend>
			<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 1) : ?>
				<legend>Manage Company</legend>
			<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 0) : ?>
				<legend>Set Up A Company Connection</legend>
			<?php endif; ?>

			<form action="" method="post">
				<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 1) : ?>
					<fieldset class="left"><legend>Select A Company To Display</legend>
						<div class="left">
							<label>Currently Selected: </label>
							<?php if(isSet($companyInformation) AND !empty($companyInformation['CompanyName'])) : ?>
								<span><b><?php htmlout($companyInformation['CompanyName']); ?></b></span>
							<?php else : ?>
								<span><b>No Company Has Been Selected.</b></span>
							<?php endif; ?>
							<label>Choose: </label>
							<select name="selectedCompanyToDisplay">
								<?php foreach($companiesUserWorksFor AS $company) : ?>
									<?php if($company['CompanyID'] == $selectedCompanyToDisplayID) : ?>
										<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
									<?php else : ?>
										<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>
							<input type="submit" name="action" value="Select Company">
						</div>
					</fieldset>
				<?php endif; ?>
			
				<?php if(isSet($companyInformation) AND !empty($companyInformation)) : ?>
					<fieldset class="left"><legend>Company Details:</legend>
						<div class="left">
							<label>Displaying Company: </label>
							<span><b><?php htmlout($companyInformation['CompanyName']); ?></b></span>
						</div>
						
						<div class="left">
							<label>Employees: </label>
							<span><a href="?employees"><?php htmlout($companyInformation['NumberOfEmployees']); ?></a></span>
						</div>
					</fieldset>

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
			
				<fieldset class="left">
				<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 0) : ?>
					<legend>Request To Join Another Company</legend>
				<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 0) : ?>
					<legend>Request To Join A Company</legend>
				<?php endif; ?>
					<div class="left">
						<label>Choose: </label>
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
					<div class="left">
						<input type="submit" name="action" value="Request To Join">
					</div>
				</fieldset>

				<div class="left">
					<input type="submit" name="action" value="Create A Company">
				</div>
			</form>
		</fieldset>
	<?php elseif(isSet($noAccess)) : ?>
		<h2>You do not have the rights to view this information.</h2>
	<?php else : ?>
		<h2>This page requires you to be logged in to view.</h2>
	<?php endif; ?>
	</body>
</html>