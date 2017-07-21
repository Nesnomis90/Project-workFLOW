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
				width: 210px;
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
		<fieldset>
			<form action="" method="post">
				<?php if($numberOfCompanies > 1) : ?>
					<legend>Manage Companies</legend>

					<div class="left">
						<label>Select The Company To Look At:</label>
						<select name="companySelect">
							<?php foreach($companies AS $company) : ?>
								<?php if($company['CompanyID'] == $selectedCompanyID) : ?>
									<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php else : ?>
									<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="left">
						<label>Currently Displaying Company: </label>
						<span><b><?php htmlout($selectedCompanyName); ?></b></span>
					</div>
				<?php elseif($numberOfCompanies == 1) : ?>
					<legend>Manage Company</legend>

					<div class="left">
						<label>Displaying Company: </label>
						<span><b><?php htmlout($selectedCompanyName); ?></b></span>
					</div>
				<?php elseif($numberOfCompanies == 0) : ?>
					<legend>Join A Company</legend>
				<?php endif; ?>
			</form>
		</fieldset>
	</body>
</html>