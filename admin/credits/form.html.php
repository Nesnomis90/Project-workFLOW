<!-- This is the HTML form used for EDITING or ADDING CREDITS information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<style>
			#CreditsDescription {
				vertical-align: top;
			}
		</style>
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<?php if(isset($_SESSION['EditCreditsError'])) :?>
			<p><b><?php htmlout($_SESSION['EditCreditsError']); ?></b></p>
			<?php unset($_SESSION['EditCreditsError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<?php if($button == 'Edit Credits') : ?>
			<div>
				<label for="OriginalCreditsName">Original Credits Name: </label>
				<b><?php htmlout($originalCreditsName); ?></b>
			</div>
			<?php endif; ?>		
			<div>
				<label for="CreditsName">Set New Credits Name: </label>
				<?php if(	isset($_SESSION['EditCreditsOriginalInfo']) AND 
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
				<b><?php htmlout($originalCreditsDescription); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="CreditsDescription">Set New Credits Description: </label>
				<?php if(	isset($_SESSION['EditCreditsOriginalInfo']) AND 
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
				<b><?php htmlout($originalCreditsAmount); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="CreditsAmount">Set New Credits Amount: </label>
				<input type="number" name="CreditsAmount" id="CreditsAmount" 
				min="0" max="65535"
				placeholder="Minutes"
				value="<?php htmlout($CreditsAmount); ?>">
			</div>
			<?php if($button == 'Edit Credits') : ?>
			<div>
				<label for="OriginalCreditsMonthlyPrice">Original Monthly Subscription Price: </label>
				<b><?php htmlout($originalCreditsMonthlyPrice); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="CreditsMonthlyPrice">Set New Monthly Subscription Price: </label>
				<input type="number" name="CreditsMonthlyPrice" id="CreditsMonthlyPrice" 
				min="0" max="65535"
				value="<?php htmlout($CreditsMonthlyPrice); ?>">
			</div>
			<?php if($button == 'Edit Credits') : ?>
			<div>
				<label for="OriginalCreditsHourPrice">Original Over Credits Fee (Charged per hour): </label>
				<b><?php htmlout($originalCreditsHourPrice); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="CreditsHourPrice">Set New Over Credits Fee (Charged per hour): </label>
				<input type="number" name="CreditsHourPrice" id="CreditsHourPrice" 
				min="0" max="65535"
				value="<?php htmlout($CreditsHourPrice); ?>">
			</div>
			<?php if($button == 'Edit Credits') : ?>
			<div>
				<label for="OriginalCreditsMinutePrice">Original Over Credits Fee (Charged per minute): </label>
				<b><?php htmlout($originalCreditsMinutePrice); ?></b>
			</div>
			<?php endif; ?>					
			<div>
				<label for="CreditsMinutePrice">Set New Over Credits Fee (Charged per minute): </label>
				<input type="text" name="CreditsMinutePrice" id="CreditsMinutePrice" 
				placeholder="e.g. 2.50"
				value="<?php htmlout($CreditsMinutePrice); ?>">
			</div>				
			<div>
				<input type="hidden" name="CreditsID" value="<?php htmlout($CreditsID); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>
			<div>
			<?php if($button == 'Confirm Credits') : ?>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
			<?php elseif($button == 'Edit Credits') : ?>
				<input type="submit" name="edit" value="Reset">
				<input type="submit" name="edit" value="Cancel">				
			<?php endif; ?>
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>