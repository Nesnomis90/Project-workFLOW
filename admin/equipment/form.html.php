<!-- This is the HTML form used for EDITING or ADDING EQUIPMENT information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<style>
			#EquipmentDescription {
				vertical-align: top;
			}
		</style>
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<?php if(isset($_SESSION['AddEquipmentError'])) :?>
			<p><b><?php htmlout($_SESSION['AddEquipmentError']); ?></b></p>
			<?php unset($_SESSION['AddEquipmentError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<?php if($button == 'Edit Equipment') : ?>
			<div>
				<label for="OriginalEquipmentName">Original Equipment Name: </label>
				<b><?php htmlout($originalEquipmentName); ?></b>
			</div>
			<?php endif; ?>		
			<div>
				<label for="EquipmentName">Set New Equipment Name: </label>
				<input type="text" name="EquipmentName" id="EquipmentName" 
				placeholder="Enter Equipment Name"
				value="<?php htmlout($EquipmentName); ?>">
			</div>
			<?php if($button == 'Edit Equipment') : ?>
			<div>
				<label for="OriginalEquipmentDescription">Original Equipment Description: </label>
				<b><?php htmlout($originalEquipmentDescription); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="EquipmentDescription">Set New Equipment Description: </label>
					<textarea rows="4" cols="50" name="EquipmentDescription" id="EquipmentDescription"
					placeholder="Enter Equipment Description"><?php htmlout($EquipmentDescription); ?></textarea>
			</div>			
			<div>
				<input type="hidden" name="EquipmentID" value="<?php htmlout($EquipmentID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>
			<div>
			<?php if($button == 'Confirm Equipment') : ?>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
			<?php elseif($button == 'Edit Equipment') : ?>
				<input type="submit" name="edit" value="Reset">
				<input type="submit" name="edit" value="Cancel">				
			<?php endif; ?>
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>