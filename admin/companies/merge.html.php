<!-- This is the HTML form used for MERGING COMPANIES-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
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
					<span><b class="feedback"><?php htmlout($_SESSION['MergeCompanyError']); ?></b></span>
					<?php unset($_SESSION['MergeCompanyError']); ?>
				<?php endif; ?>
			</div>

		<form action="" method="post">
			<div class="left">
				<label for="mergingCompanyName">Selected Company Name:</label>
				<span><b><?php htmlout($mergingCompanyName); ?></b></span>
			</div>

			<?php if(isSet($companies)) : ?>
				<div class="left">
					<label for="CompanyName">Select Company To Merge With: </label>
					<select name="mergingCompanyID">
						<?php foreach($companies AS $company) : ?>
							<option value="<?php htmlout($company['CompanyID']); ?>"><?php htmlout($company['CompanyName']); ?></option>
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
				<input type="password" name="password" value="">
			</div>
			<div class="left">
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" name="action" value="Confirm Merge">
				<input type="submit" name="merge" value="Cancel">					
			</div>
		</form>
		</fieldset>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>