<!-- This is the HTML form used for EDITING COMPANYCREDITS information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Edit Company Credits</title>
		<style>
			label {
				width: 190px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend>Edit Company Credits</legend>
			<div class="left">
				<?php if(isSet($_SESSION['EditCompanyCreditsError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['EditCompanyCreditsError']); ?></b></span>
					<?php unset($_SESSION['EditCompanyCreditsError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
				<div>
					<label for="selectedCompanyName">Company Selected: </label>
					<span><b><?php htmlout($CompanyName); ?></b></span>
				</div>
				<div>
					<label for="currentBillingPeriod">Current Billing Period: </label>
					<span><b><?php htmlout($BillingPeriod); ?></b></span>
				</div>
				<div>
					<label for="originalCreditsName">Active Credits: </label>
					<span><b><?php htmlout($originalCreditsName); ?></b></span>
				</div>
				<div>
				<?php if(isSet($_SESSION['EditCompanyCreditsChangeCredits']) AND $_SESSION['EditCompanyCreditsChangeCredits']) : ?>
					<label for="CreditsID">Set New Credits: </label>
					<select name="CreditsID" id="CreditsID">
						<?php foreach($credits as $row): ?> 
							<?php if($row['CreditsID']==$selectedCreditsID):?>
								<option selected="selected" 
										value="<?php htmlout($row['CreditsID']); ?>">
										<?php htmlout($row['CreditsInformation']);?>
								</option>
							<?php else : ?>
								<option value="<?php htmlout($row['CreditsID']); ?>">
										<?php htmlout($row['CreditsInformation']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
					<input type="submit" name="edit" value="Select Credits">
				<?php else : ?>
					<label for="CreditsID">Selected Credits: </label>
						<?php foreach($credits as $row): ?> 
							<?php if($row['CreditsID']==$selectedCreditsID):?>
									<span><b><?php htmlout($row['CreditsName']);?></b></span>
									<?php $creditsGivenInHTML = $row['CreditsGivenInMinutes'];?>
							<?php endif;?>
						<?php endforeach; ?>
						<input type="submit" name="edit" value="Change Credits">
					</div>
					<div>
					<label for="CreditsGiven">Credits Given: </label>	
					<span><b><?php htmlout($creditsGivenInHTML);?></b></span>
				<?php endif; ?>
				</div>
				<div>
					<label for="originalCreditsAmount">Original Alt. Credits Given: </label>
					<span><b><?php htmlout($originalCreditsAlternativeCreditsAmount); ?></b></span>	
				</div>
				<div>
				<?php if(	isSet($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) AND 
							$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) : ?>
					<label for="CreditsAlternativeCreditsAmount">Set New Alt. Credits Given: </label>
					<input type="number" name="CreditsAlternativeCreditsAmount" min="0" max="65535"
					value="<?php htmlout($CreditsAlternativeCreditsAmount); ?>"><span style="color: red;">*</span>
					<input type="submit" name="edit" value="Set Original Amount">
					<input type="submit" name="edit" value="Select Amount">
					<span style="color: red;">* specified in minutes.</span> 
				<?php elseif(!isSet($_SESSION['EditCompanyCreditsChangeCredits'])) : ?>
					<?php if($CreditsAlternativeCreditsAmount == 0) : ?>
						<label for="CreditsAlternativeCreditsAmount">Using Default Credits Given: </label>
						<span><b><?php htmlout($creditsGivenInHTML); ?></b></span>
						<input type="submit" name="edit" value="Change Amount">	
					<?php else : ?>
						<label for="CreditsAlternativeCreditsAmount">Selected Alt. Credits Given: </label>
						<span><b><?php htmlout(convertMinutesToHoursAndMinutes($CreditsAlternativeCreditsAmount)); ?></b></span>
						<input type="submit" name="edit" value="Change Amount">
					<?php endif; ?>
				<?php endif; ?>
				</div>
				<div class="left">
					<input type="hidden" name="CompanyID" id="CompanyID" 
					value="<?php htmlout($CompanyID); ?>">
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Cancel">
					<?php if(isSet($_SESSION['EditCompanyCreditsChangeCredits']) AND $_SESSION['EditCompanyCreditsChangeCredits']) : ?>
						<input type="submit" name="disabled" value="Finish Edit" disabled>
						<span><b>You need to select the credits you want before you can finish editing.</b></span>
					<?php elseif(	isSet($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) AND 
									$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) : ?>
						<input type="submit" name="disabled" value="Finish Edit" disabled>
						<span><b>You need to select the alternative Credits given before you can finish editing.</b></span>		
					<?php else : ?>
						<input type="submit" name="edit" value="Finish Edit">
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>		