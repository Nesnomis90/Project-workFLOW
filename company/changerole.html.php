<!-- This is the HTML form used for CHANGING an EMPLOYEE ROLE for the company owner-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Change Employee Role</title>
		<style>
			label {
				width: 120px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset class="left"><legend>Change Employee Role</legend>
			<form action="" method="post">
				<div>
					<label for="CompanyName">Company Name:</label>
					<span><b id="CompanyName"><?php htmlout($CompanyName); ?></b></span>
				</div>
				<div>				
					<label for="UserIdentifier">User:</label>
					<span><b id="UserIdentifier"><?php htmlout($UserIdentifier); ?></b></span>
				</div>
				<div>
					<label for="CurrentCompanyPositionName">Current Role:</label>
					<span><b id="CurrentCompanyPositionName"><?php htmlout($CurrentCompanyPositionName); ?></b></span>
				</div>
				<div>
					<label for="PositionID">Set New Role:</label>
					<select name="PositionID" id="PositionID">
						<?php foreach($companyposition as $row): ?> 
							<?php if($row['CompanyPositionName'] == $CurrentCompanyPositionName):?>
								<option selected="selected" 
										value="<?php htmlout($row['PositionID']); ?>">
										<?php htmlout($row['CompanyPositionName']);?>
								</option>
							<?php else : ?>
								<option value="<?php htmlout($row['PositionID']); ?>">
										<?php htmlout($row['CompanyPositionName']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<input type="hidden" name="CompanyID" value="<?php htmlout($CompanyID); ?>">
					<input type="hidden" name="UserID" value="<?php htmlout($UserID); ?>">
					<input type="submit" name="action" value="Confirm Role">
					<input type="submit" name="action" value="Cancel">
				</div>
			</form>
		</fieldset>
	</body>
</html>