<!-- This is the HTML form used for EDITING or ADDING Extra information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title><?php htmlout($pageTitle); ?></title>
		<style>
			label {
				width: 220px;
			}
			.checkboxlabel{
				float: none;
				clear: none;
				width: 200px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend><?php htmlout($pageTitle); ?></legend>
			<div>
				<?php if(isSet($_SESSION['AddExtraError'])) :?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddExtraError']); ?></b></span>
					<?php unset($_SESSION['AddExtraError']); ?>
				<?php endif; ?>
			</div>
			
			<form action="" method="post">
				<?php if($button == 'Edit Extra') : ?>
					<div>
						<label>Original Extra Name: </label>
						<span><b><?php htmlout($originalExtraName); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label>Set New Extra Name: </label>
					<input type="text" name="ExtraName" placeholder="Enter Extra Name" value="<?php htmlout($extraName); ?>">
				</div>

				<?php if($button == 'Edit Extra') : ?>
					<div>
						<label>Original Extra Price: </label>
						<span><b><?php htmlout($originalExtraPrice); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label>Set New Extra Price: </label>
					<input type="number" name="ExtraPrice" min="0" placeholder="Enter Extra Price" value="<?php htmlout($extraPrice); ?>">
				</div>

				<?php if($button == 'Edit Extra') : ?>
					<div>
						<label>Original Extra Description: </label>
						<span><b><?php htmlout($originalExtraDescription); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label class="description" for="ExtraDescription">Set New Extra Description: </label>
						<textarea rows="4" cols="50" name="ExtraDescription" placeholder="Enter Extra Description"><?php htmlout($extraDescription); ?></textarea>
				</div>

				<?php if($button == 'Edit Extra') : ?>
					<div>
						<label>Original Type Selected: </label>
						<?php if($originalExtraIsAlternative == 1) : ?>
							<span><b><?php htmlout("Alternative Type"); ?></b></span>
						<?php else : ?>
							<span><b><?php htmlout("Normal Type"); ?></b></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div>
					<?php if($extraIsAlternative == 1) : ?>
						<label><input type="checkbox" name="isAlternative" value="1" checked>Alternative Type</label>
					<?php else : ?>
						<label><input type="checkbox" name="isAlternative" value="1">Alternative Type</label>
					<?php endif; ?>
				</div>

				<div class="left">
					<input type="hidden" name="ExtraID" value="<?php htmlout($extraID); ?>">
					<input type="submit" name="action" value="<?php htmlout($button); ?>">
				</div>

				<div class="left">
					<?php if($button == 'Confirm Extra') : ?>
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">
					<?php elseif($button == 'Edit Extra') : ?>
						<input type="submit" name="edit" value="Reset">
						<input type="submit" name="edit" value="Cancel">				
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>