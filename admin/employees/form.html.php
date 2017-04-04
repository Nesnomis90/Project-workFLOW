<!-- This is the HTML form used for EDITING or ADDING EMPLOYEE information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<form action="?<?php htmlout($action); ?>" method="post">
			<div>
				<label for="">Company Name:
					<input type="text" name="CompanyName" id="CompanyName"
					value="<?php htmlout($CompanyName); ?>">
					<p>some way to choose a company</p>
				</label>
			</div>
			<div>				
				<label for="">User:
					<input type="text" name="UserIdentifier" id="UserIdentifier"
					value="<?php htmlout($UserIdentifier); ?>">
					<p>some way to choose a user</p>
				</label>
			</div>
			<div>
				<label for="positionID">Role: 
					<select name="positionID" id="positionID">
						<?php foreach($companyposition as $row): ?> 
							<?php if($row['CompanyPositionName']==$companypositionname):?>
								<option selected="selected" 
										value=<?php htmlout($row['positionID']); ?>>
										<?php htmlout($row['CompanyPositionName']);?>
								</option>
							<?php else : ?>
								<option value=<?php htmlout($row['positionID']); ?>>
										<?php htmlout($row['CompanyPositionName']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<div>
				<input type="hidden" name="CompanyID" value="<?php htmlout($CompanyID); ?>">
				<input type="hidden" name="UserID" value="<?php htmlout($UserID); ?>">
				<input type="submit" value="<?php htmlout($button); ?>">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>