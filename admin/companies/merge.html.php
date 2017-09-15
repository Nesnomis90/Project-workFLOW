<!-- This is the HTML form used for MERGING COMPANIES-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Merge Companies</title>
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

		<fieldset class="left"><legend>Merge Companies</legend>
			<div class="left">
				<?php if(isSet($_SESSION['MergeCompanyError'])) : ?>
					<span><b class="warning"><?php htmlout($_SESSION['MergeCompanyError']); ?></b></span>
					<?php $fillOut = TRUE; ?>
					<?php unset($_SESSION['MergeCompanyError']); ?>
				<?php endif; ?>
			</div>

		<form action="" method="post">
			<div class="left">
				<span style="white-space: pre-wrap;"><b><?php htmlout("The company you have selected will be removed. All its employees and booking history will be transferred into the new company.\nEmployees who work in both companies will not have their employee information transferred."); ?></b></span>
			</div>
			<div class="left">
				<label for="mergingCompanyName">Selected Company To Remove: </label>
				<span><b><?php htmlout($mergingCompanyName); ?></b></span>
			</div>

			<?php if(isSet($companies)) : ?>
				<div class="left">
					<label for="CompanyName">Select Company To Merge Into: </label>
					<select name="mergingCompanyID">
						<?php foreach($companies AS $company) : ?>
							<?php if(isSet($selectedCompanyIDToMergeWith) AND $selectedCompanyIDToMergeWith == $company['CompanyID']) : ?>
								<option selected="selected" value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
							<?php else : ?>
								<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			<?php else : ?>
				<div class="left">
					<span><b>There are no companies to merge with.</b></span>
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
				<input type="submit" name="action" value="Confirm Merge">
				<input type="submit" name="merge" value="Cancel">					
			</div>
		</form>
		</fieldset>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>