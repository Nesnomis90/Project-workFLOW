<!-- This is the HTML form used for EDITING or ADDING EQUIPMENT information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<form action="" method="post">
			<div>
				<label for="EquipmentName">Equipment Name: 
					<input type="text" name="EquipmentName" id="EquipmentName" 
					required placeholder="Enter Equipment Name" 
					oninvalid="this.setCustomValidity('Enter Equipment Name Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($EquipmentName); ?>">
				</label>
			</div>
			<div>
				<label for="EquipmentDescription">Equipment Description: 
					<input type="text" name="EquipmentDescription" id="EquipmentDescription" 
					required placeholder="Enter Equipment Description" 
					oninvalid="this.setCustomValidity('Enter Equipment Description Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($EquipmentDescription); ?>">
				</label>
			</div>
			<div>
				<input type="hidden" name="EquipmentID" value="<?php htmlout($EquipmentID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
				<input type="submit" name="action" value="Cancel">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>