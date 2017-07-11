<!-- This is the HTML form used for EDITING or ADDING EQUIPMENT information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title><?php htmlout($pageTitle); ?></title>
		<style>
			label {
				width: 220px;
			}
		</style>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		
		<div>
			<?php if(isset($_SESSION['AddEquipmentError'])) :?>
				<span><b class="feedback"><?php htmlout($_SESSION['AddEquipmentError']); ?></b></span>
				<?php unset($_SESSION['AddEquipmentError']); ?>
			<?php endif; ?>
		</div>
		
		<form action="" method="post">
			<?php if($button == 'Edit Equipment') : ?>
				<div>
					<label for="OriginalEquipmentName">Original Equipment Name: </label>
					<span><b><?php htmlout($originalEquipmentName); ?></b></span>
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
					<span><b><?php htmlout($originalEquipmentDescription); ?></b></span>
				</div>
			<?php endif; ?>
			
			<div>
				<label class="description" for="EquipmentDescription">Set New Equipment Description: </label>
					<textarea rows="4" cols="50" name="EquipmentDescription" id="EquipmentDescription"
					placeholder="Enter Equipment Description"><?php htmlout($EquipmentDescription); ?></textarea>
			</div>			
			<div class="left">
				<input type="hidden" name="EquipmentID" value="<?php htmlout($EquipmentID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>
			<div class="left">
				<?php if($button == 'Confirm Equipment') : ?>
					<input type="submit" name="add" value="Reset">
					<input type="submit" name="add" value="Cancel">
				<?php elseif($button == 'Edit Equipment') : ?>
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Cancel">				
				<?php endif; ?>
			</div>
		</form>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>