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
		<?php if(isset($_SESSION['AddCompanyError'])) : ?>
			<p><b><?php htmlout($_SESSION['AddCompanyError']); ?></b></p>
			<?php unset($_SESSION['AddCompanyError']); ?>
		<?php endif; ?>		
		<form action="" method="post">
			<?php if(isset($originalCompanyName)) : ?>
				<div>
					<label for="originalCompanyName">Original Company Name:</label>
					<b><?php htmlout($originalCompanyName); ?></b>	
				</div>
			<?php endif; ?>
			<div>
				<label for="CompanyName">Set a new Company Name: </label>
				<input type="text" name="CompanyName" id="CompanyName" 
				placeholder="Enter A Company Name"
				value="<?php htmlout($CompanyName); ?>">
			</div>
			<?php if ($ShowDateToRemove) :?>
				<div>
					<label for="originalDateToRemove">Original Date to Remove:</label>
					<?php if(isset($originalDateToDisplay) AND $originalDateToDisplay != "") : ?>
						<b><?php htmlout($originalDateToDisplay); ?></b>	
					<?php else : ?>
						<b>No date has been Set</b>
					<?php endif; ?>
				</div>
				<div>
					<label for="DateToRemove">Set a new Date to Remove: </label>
					<input type="text" name="DateToRemove" id="DateToRemove"
					value="<?php htmlout($DateToRemove); ?>">
				</div>
			<?php endif; ?>
			<div>
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>
			<div>
				<?php if($button == 'Edit Company') : ?>
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Cancel">
				<?php elseif($button == 'Add Company') : ?>
					<input type="submit" name="add" value="Cancel">					
				<?php endif; ?>
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>