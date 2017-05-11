<!-- This is the HTML form used for CHANGING an EMPLOYEE ROLE-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Change Employee Role</title>
	</head>
	<body>
		<h1>Change Employee Role</h1>
		<form action="" method="post">
			<div>
				<label for="CompanyName">Company Name:</label>
				<b id="CompanyName"><?php htmlout($CompanyName); ?></b>	
			</div>
			<div>				
				<label for="UserIdentifier">User:</label>
				<b id="UserIdentifier"><?php htmlout($UserIdentifier); ?></b>
			</div>
			<div>
				<label for="CurrentCompanyPositionName">Current Role:</label>
				<b id="CurrentCompanyPositionName"><?php htmlout($CurrentCompanyPositionName); ?></b>
			</div>
			<div>
				<label for="PositionID">Set New Role:</label>
				<select name="PositionID" id="PositionID">
					<?php foreach($companyposition as $row): ?> 
						<?php if($row['CompanyPositionName']==$CurrentCompanyPositionName):?>
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
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>