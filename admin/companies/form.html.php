<!-- This is the HTML form used for EDITING or ADDING COMPANY information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php htmlout($pageTitle); ?></title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 180px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend><?php htmlout($pageTitle); ?></legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddCompanyError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddCompanyError']); ?></b></span>
					<?php unset($_SESSION['AddCompanyError']); ?>
				<?php endif; ?>
			</div>
			
			<form method="post">
				<?php if(isSet($originalCompanyName)) : ?>
					<div>
						<label for="originalCompanyName">Original Company Name:</label>
						<span><b><?php htmlout($originalCompanyName); ?></b></span>	
					</div>
				<?php endif; ?>
				<div>
					<label for="CompanyName">Set a new Company Name: </label>
					<input type="text" name="CompanyName" placeholder="Enter A Company Name" value="<?php htmlout($CompanyName); ?>">
				</div>
				<?php if ($ShowDateToRemove) :?>
					<div>
						<label for="originalDateToRemove">Original Date to Remove:</label>
						<?php if(isSet($originalDateToDisplay) AND $originalDateToDisplay != "") : ?>
							<span><b><?php htmlout($originalDateToDisplay); ?></b></span>
						<?php else : ?>
							<span><b>No date has been Set</b></span>
						<?php endif; ?>
					</div>
					<div>
						<label for="DateToRemove">Set a new Date to Remove: </label>
						<input type="text" name="DateToRemove" value="<?php htmlout($DateToRemove); ?>">
					</div>
				<?php endif; ?>
				<div class="left">
					<input type="hidden" name="CompanyID" value="<?php htmlout($CompanyID); ?>">
					<input type="submit" name="action" value="<?php htmlout($button); ?>">
					<?php if($button == 'Edit Company') : ?>
						<input type="submit" name="edit" value="Reset">
						<input type="submit" name="edit" value="Cancel">
					<?php elseif($button == 'Add Company') : ?>
						<input type="submit" name="add" value="Cancel">					
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>