<!-- This is the HTML form used for TRANSFERING EMPLOYEEs-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Transfer Employee</title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 220px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset class="left"><legend>Transfer Employee</legend>
			<div class="left">
				<?php if(isSet($_SESSION['TransferEmployeeError'])) : ?>
					<span><b class="warning"><?php htmlout($_SESSION['TransferEmployeeError']); ?></b></span>
					<?php $fillOut = TRUE; ?>
					<?php unset($_SESSION['TransferEmployeeError']); ?>
				<?php endif; ?>
			</div>

		<form action="" method="post">
			<div class="left">
				<span style="white-space: pre-wrap;"><b><?php htmlout("The employee you have selected will have all its booking history, from this company, transferred to the new company.\nIf you don't want this to happen, you should instead remove the employee and add it to the new company manually."); ?></b></span>
			</div>
			<div class="left">
				<label>Selected Employee To Transfer: </label>
				<span><b><?php htmlout($transferEmployeeName); ?></b></span>
			</div>

			<div class="left">
				<label>Company Employee Is In: </label>
				<span><b><?php htmlout($transferCompanyName); ?></b></span>
			</div>
			
			<?php if(isSet($companies)) : ?>
				<div class="left">
					<label>Select Company To Transfer To: </label>
					<select name="transferCompanyID">
						<?php foreach($companies AS $company) : ?>
							<?php if(isSet($selectedCompanyIDToTransferTo) AND $selectedCompanyIDToTransferTo == $company['CompanyID']) : ?>
								<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
							<?php else : ?>
								<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			<?php else : ?>
				<div class="left">
					<span><b>There are no companies to transfer to.</b></span>
				</div>
			<?php endif; ?>

			<div class="left">
				<label>Confirm With Password: </label>
				<?php if(isSet($fillOut)) : ?>
					<input class="fillOut" type="password" name="password" value="">
				<?php else : ?>
					<input type="password" name="password" value="">
				<?php endif; ?>
			</div>
			<div class="left">
				<input type="hidden" name="CompanyID" value="<?php htmlout($companyID); ?>">
				<input type="submit" name="action" value="Confirm Transfer">
				<input type="submit" name="merge" value="Cancel">					
			</div>
		</form>
		</fieldset>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>