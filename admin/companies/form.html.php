<!-- This is the HTML form used for EDITING or ADDING COMPANY information-->
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
				<label for="CompanyName">Company Name: 
					<input type="text" name="CompanyName" id="CompanyName" 
					required placeholder="Enter A Company Name" 
					oninvalid="this.setCustomValidity('Enter A Company Name Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($CompanyName); ?>">
				</label>
			</div>
			<div style="display:<?php htmlout($CompanyPositionStyle); ?>">
				<label for="CompanyPositionID">Company Position: 
					<select name="CompanyPositionID" id="CompanyPositionID">
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
			<div style="display:<?php htmlout($DateToRemoveStyle); ?>">
				<label for="DateToRemove">Date to Remove: 
					<input type="text" name="DateToRemove" id="DateToRemove" 
					value="<?php htmlout($DateToRemove); ?>">
				</label>
			</div>
			<div>
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" value="<?php htmlout($button); ?>">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>