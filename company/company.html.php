<!-- This is the HTML form used for DISPLAYING COMPANY information to connected users-->
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
					width: 150px;
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

	<?php if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND !empty($_SESSION['LoggedInUserID']) AND !isSet($noAccess)) : ?>
		<h2>Company Management</h2>
	<?php elseif(isSet($noAccess)) : ?>
		<h2>You do not have the rights to view this information.</h2>
	<?php else : ?>
		<h2>This page requires you to be logged in to view.</h2>
	<?php endif; ?>

	<?php if(isSet($_SESSION['loggedIn']) AND $_SESSION['loggedIn'] AND !empty($_SESSION['LoggedInUserID']) AND !isSet($noAccess)) : ?>

		<fieldset class="left">
			<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 1) : ?>
				<legend>Manage Companies</legend>
			<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 1) : ?>
				<legend>Manage Company</legend>
			<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 0) : ?>
				<legend>Set Up A Company Connection</legend>
			<?php endif; ?>

			<div class="left fieldsetIndentReplication">
				<?php if(isSet($_SESSION['normalCompanyFeedback'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['normalCompanyFeedback']); ?></b></span>
					<?php unset($_SESSION['normalCompanyFeedback']); ?>
				<?php endif; ?>
			</div>

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
							<label>Your Role: </label>
							<span><b><?php htmlout($companyInformation['CompanyRole']); ?></b></span>
						</div>

						<div class="left">
							<label>Employees: </label>
							<span><a href="?employees"><?php htmlout($companyInformation['NumberOfEmployees']); ?></a></span>
						</div>
					</fieldset>

					<fieldset class="left"><legend>Booking Details:</legend>
						<div class="left">
							<label>Current Period: </label>
							<span><b><?php htmlout($companyInformation['PeriodInfo']); ?></b></span>
						</div>

						<div class="left">
							<label>Credits Given (This Period): </label>
							<span><b><?php htmlout($companyInformation['CompanyCredits']); ?></b></span>
						</div>

						<div class="left">
							<label>Credits Remaining (This Period): </label>
							<?php if(substr($companyInformation['CompanyCreditsRemaining'],0,1) == "-"){$color="red";}else{$color="green";} ?>
							<span><b style="color: <?php htmlout($color); ?>;"><?php htmlout($companyInformation['CompanyCreditsRemaining']); ?></b></span>
						</div>

						<div class="left">
							<label>Monthly Fee (This Period): </label>
							<span><b><?php htmlout($companyInformation['CreditSubscriptionMonthlyPrice']); ?></b></span>
						</div>

						<div class="left">
							<label>Over Credits Fee (This Period): </label>
							<span><b><?php htmlout($companyInformation['OverCreditsFee']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (This Period): </label>
							<span><b><?php htmlout($companyInformation['MonthlyCompanyWideBookingTimeUsed']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (Last Period): </label>
							<span><b><?php htmlout($companyInformation['PreviousMonthCompanyWideBookingTimeUsed']); ?></b></span>
						</div>

						<div class="left">
							<label>Booking Time Used (Total): </label>
							<span><b><?php htmlout($companyInformation['TotalCompanyWideBookingTimeUsed']); ?></b></span>
						</div>

						<?php if($numberOfTotalBookedMeetings > 0) : ?>
							<div>
								<label>Booked Meetings (Total):</label>
								<span><a href="?ID=<?php htmlout($_GET['ID']); ?>&totalBooking"><?php htmlout($numberOfTotalBookedMeetings); ?></a></span>
							</div>

							<?php if($numberOfActiveBookedMeetings > 0) : ?>
								<div>
									<label>Booked Meetings (Active):</label>
									<span><a href="?ID=<?php htmlout($_GET['ID']); ?>&activeBooking"><?php htmlout($numberOfActiveBookedMeetings); ?></a></span>
								</div>
							<?php endif; ?>

							<?php if($numberOfCompletedBookedMeetings > 0) : ?>
								<div>
									<label>Booked Meetings (Completed):</label>
									<span><a href="?ID=<?php htmlout($_GET['ID']); ?>&completedBooking"><?php htmlout($numberOfCompletedBookedMeetings); ?></a></span>
								</div>
							<?php endif; ?>

							<?php if($numberOfCancelledBookedMeetings > 0) : ?>
								<div>
									<label>Booked Meetings (Cancelled):</label>
									<span><a href="?ID=<?php htmlout($_GET['ID']); ?>&cancelledBooking"><?php htmlout($numberOfCancelledBookedMeetings); ?></a></span>
								</div>
							<?php endif; ?>
						<?php endif; ?>	
					</fieldset>
				<?php endif; ?>

				<div class="left">
					<?php if(isSet($_SESSION['normalCompanyJoinACompany'])) : ?>
						<fieldset class="left">
						<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 0) : ?>
							<legend>Request To Join Another Company</legend>
						<?php elseif(isSet($numberOfCompanies) AND $numberOfCompanies == 0) : ?>
							<legend>Request To Join A Company</legend>
						<?php endif; ?>
							<?php if(!empty($companies)) : ?>
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
									<label>Request Message: </label>
									<textarea rows="4" cols="50" name="requestToJoinMessage"
									placeholder="Enter any information you would like to send to the company."></textarea>
								</div>
								<div class="left">
									<input type="submit" name="action" value="Request To Join">
									<input type="submit" name="action" value="Cancel">
								</div>
							<?php else : ?>
								<div class="left">
									<span><b>There are no companies to join.</b></span>
									<input type="submit" name="action" value="Cancel">
								</div>
							<?php endif; ?>
						</fieldset>
					<?php endif; ?>
				</div>

				<div class="left">
					<?php if(isSet($_SESSION['normalCompanyCreateACompany']) AND $_SESSION['normalCompanyCreateACompany'] === "Invalid") : ?>
						<fieldset><legend>Create A Company</legend>
							<label>Set Company Name: </label>
							<input class="fillOut" type="text" name="createACompanyName" value="">
							<div class="left">
								<input type="submit" name="action" value="Confirm">
								<input type="submit" name="action" value="Cancel">
							</div>
						</fieldset>
					<?php elseif(isSet($_SESSION['normalCompanyCreateACompany']) AND $_SESSION['normalCompanyCreateACompany']) : ?>
						<fieldset><legend>Create A Company</legend>
							<label>Set Company Name: </label>
							<input type="text" name="createACompanyName" value="">
							<div class="left">
								<input type="submit" name="action" value="Confirm">
								<input type="submit" name="action" value="Cancel">
							</div>
						</fieldset>
					<?php endif; ?>
				</div>

				<?php if(!isSet($_SESSION['normalCompanyCreateACompany']) OR !isSet($_SESSION['normalCompanyJoinACompany'])) : ?>
					<div class="left">
						<fieldset>
							<?php if(isSet($numberOfCompanies) AND $numberOfCompanies > 1) : ?>
								<legend>Other Choices</legend>
							<?php else : ?>
								<legend>Your Choices</legend>
							<?php endif; ?>
							<?php if(!isSet($_SESSION['normalCompanyJoinACompany'])) : ?>
								<input type="submit" name="action" value="Join A Company">
							<?php endif; ?>
							<?php if(!isSet($_SESSION['normalCompanyCreateACompany'])) : ?>
								<input type="submit" name="action" value="Create A Company">
							<?php endif; ?>
						</fieldset>
					</div>
				<?php endif; ?>
			</form>
		</fieldset>
	<?php endif; ?>
	</body>
</html>