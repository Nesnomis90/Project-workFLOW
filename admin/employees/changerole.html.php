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
		<form action="?changerole" method="post">
			<div>
				<label for="CompanyName">Company Name:
					<b><?php htmlout($CompanyName); ?></b>
				</label>
			</div>
			<div>				
				<label for="UserIdentifier">User:
					<b><?php htmlout($UserIdentifier); ?></b>
				</label>
			</div>
			<div>
				<label for=PositionID>Role: 
					<select name=PositionID id=PositionID>
						<?php foreach($companyposition as $row): ?> 
							<?php if($row['CompanyPositionName']==$companypositionname):?>
								<option selected="selected" 
										value=<?php htmlout($row['PositionID']); ?>>
										<?php htmlout($row['CompanyPositionName']);?>
								</option>
							<?php else : ?>
								<option value=<?php htmlout($row['PositionID']); ?>>
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
				<input type="submit" value="Confirm Role">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>