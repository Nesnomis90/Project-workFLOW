<!-- This is the HTML form used for EDITING or ADDING CREDITS information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title><?php htmlout($pageTitle); ?></title>
		<style>
			label {
				width: 250px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset class="left"><legend><?php htmlout($pageTitle); ?></legend>
			<div class="left">
				<?php if(isSet($_SESSION['EditCreditsError'])) :?>
					<span><b class="feedback"><?php htmlout($_SESSION['EditCreditsError']); ?></b></span>
					<?php unset($_SESSION['EditCreditsError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
				<?php if($button == 'Edit Credits') : ?>
					<div>
						<label for="OriginalCreditsName">Original Credits Name: </label>
						<span><b><?php htmlout($originalCreditsName); ?></b></span>
					</div>
				<?php endif; ?>
				<div>
					<label for="CreditsName">Set New Credits Name: </label>
					<?php if(	isSet($_SESSION['EditCreditsOriginalInfo']) AND 
								$_SESSION['EditCreditsOriginalInfo']['CreditsName'] == 'Default') : ?>
						<input type="hidden" name="CreditsName" id="CreditsName"
						value="<?php htmlout($CreditsName); ?>">
						<input type="text" name="DisabledCreditsName" id="DisabledCreditsName"
						disabled value="Can't change.">
					<?php else : ?>
						<input type="text" name="CreditsName" id="CreditsName" 
						placeholder="Enter Credits Name"
						value="<?php htmlout($CreditsName); ?>">
					<?php endif; ?>
				</div>

				<?php if($button == 'Edit Credits') : ?>
					<div>
						<label for="OriginalCreditsDescription">Original Credits Description: </label>
						<span><b><?php htmlout($originalCreditsDescription); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label class="description" for="CreditsDescription">Set New Credits Description: </label>
					<?php if(	isSet($_SESSION['EditCreditsOriginalInfo']) AND 
								$_SESSION['EditCreditsOriginalInfo']['CreditsName'] == 'Default') : ?>
						<input type="hidden" name="CreditsDescription" id="CreditsDescription"
						value="<?php htmlout($CreditsDescription); ?>">
						<input type="text" name="DisabledCreditsDescription" id="DisabledCreditsDescription"
						disabled value="Can't change.">	
					<?php else : ?>
						<textarea rows="4" cols="50" name="CreditsDescription" id="CreditsDescription"
						placeholder="Enter Credits Description"><?php htmlout($CreditsDescription); ?></textarea>
					<?php endif; ?>
				</div>

				<?php if($button == 'Edit Credits') : ?>
					<div>
						<label for="OriginalCreditsAmount">Original Credits Amount: </label>
						<span><b><?php htmlout($originalCreditsAmount); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="CreditsAmount">Set New Credits Amount: </label>
					<input type="number" name="CreditsAmount" id="CreditsAmount" 
					min="0" max="65535"
					placeholder="e.g. 90"
					value="<?php htmlout($CreditsAmount); ?>"><span style="color: red;">*given in minutes</span>
				</div>

				<?php if($button == 'Edit Credits') : ?>
					<div>
						<label for="OriginalCreditsMonthlyPrice">Original Monthly Subscription Price: </label>
						<span><b><?php htmlout($originalCreditsMonthlyPrice); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="CreditsMonthlyPrice">Set New Monthly Subscription Price: </label>
					<input type="number" name="CreditsMonthlyPrice" id="CreditsMonthlyPrice" 
					min="0" max="<?php htmlout(MAXIMUM_FLOAT_NUMBER); ?>" step="<?php htmlout(SET_CURRENCY_STEP_PRECISION); ?>" placeholder="e.g. 2150.50"
					value="<?php htmlout($CreditsMonthlyPrice); ?>">
				</div>

				<?php if($button == 'Edit Credits') : ?>
					<div>
						<label for="OriginalCreditsHourPrice">Original Over Credits Fee (per hour): </label>
						<span><b><?php htmlout($originalCreditsHourPrice); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="CreditsHourPrice">Set New Over Credits Fee (per hour): </label>
					<input type="number" name="CreditsHourPrice" id="CreditsHourPrice" 
					min="0" max="<?php htmlout(MAXIMUM_FLOAT_NUMBER); ?>" step="<?php htmlout(SET_CURRENCY_STEP_PRECISION); ?>" placeholder="e.g. 150.50"
					value="<?php htmlout($CreditsHourPrice); ?>">
				</div>

				<div class="left">
					<input type="hidden" name="CreditsID" value="<?php htmlout($CreditsID); ?>">
					<input type="submit" name="action" value="<?php htmlout($button); ?>">
					<?php if($button == 'Confirm Credits') : ?>
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">
					<?php elseif($button == 'Edit Credits') : ?>
						<input type="submit" name="edit" value="Reset">
						<input type="submit" name="edit" value="Cancel">
					<?php endif; ?>
				</div>
			</form>
		</fieldset>

		<div class="left"><a href="..">Return to CMS home</a></div>
	</body>
</html>