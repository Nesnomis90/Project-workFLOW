<!-- This is the HTML form used for EDITING COMPANYCREDITS information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<title>Edit Company Credits</title>
	</head>
	<body>
		<h1>Edit Company Credits</h1>
		<?php if(isset($_SESSION['EditCompanyCreditsError'])) : ?>
			<p><b><?php htmlout($_SESSION['EditCompanyCreditsError']); ?></b></p>
			<?php unset($_SESSION['EditCompanyCreditsError']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="selectedCompanyName">Company Selected: </label>
				<b><?php htmlout($CompanyName); ?></b>
			</div>
			<div>
				<label for="currentBillingPeriod">Current Billing Period: </label>
				<b><?php htmlout($BillingPeriod); ?></b>
			</div>
			<div>
				<label for="originalCreditsName">Active Credits: </label>
				<b><?php htmlout($originalCreditsName); ?></b>
			</div>
			<div>			
			<?php if(isset($_SESSION['EditCompanyCreditsChangeCredits']) AND $_SESSION['EditCompanyCreditsChangeCredits']) : ?>
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
								<b><?php htmlout($row['CreditsName']);?></b>
								<?php $creditsGivenInHTML = $row['CreditsGivenInMinutes'];?>
						<?php endif;?>
					<?php endforeach; ?>
					<input type="submit" name="edit" value="Change Credits">
				</div>
				<div>
				<label for="CreditsGiven">Credits Given: </label>	
				<b><?php htmlout($creditsGivenInHTML);?></b>
			<?php endif; ?>
			</div>
			<div>
				<label for="originalCreditsAmount">Original Alternative Credits Given: </label>
				<b><?php htmlout($originalCreditsAlternativeCreditsAmount); ?></b>		
			</div>
			<div>
			<?php if(	isset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) AND 
						$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) : ?>
				<label for="CreditsAlternativeCreditsAmount">Set New Alt. Credits Given(minutes): </label>
				<input type="number" name="CreditsAlternativeCreditsAmount" min="0" max="65535"
				value="<?php htmlout($CreditsAlternativeCreditsAmount); ?>">
				<input type="submit" name="edit" value="Set Original Amount">
				<input type="submit" name="edit" value="Select Amount">
			<?php else : ?>
				<label for="CreditsAlternativeCreditsAmount">Selected Alt. Credits Given(minutes): </label>
				<b><?php htmlout($CreditsAlternativeCreditsAmount); ?>m</b>
				<input type="submit" name="edit" value="Change Amount">
			<?php endif; ?>
			</div>
			<div>
				<input type="hidden" name="CompanyID" id="CompanyID" 
				value="<?php htmlout($CompanyID); ?>">
				<input type="submit" name="edit" value="Reset">
				<input type="submit" name="edit" value="Cancel">
				<?php if(isset($_SESSION['EditCompanyCreditsChangeCredits']) AND $_SESSION['EditCompanyCreditsChangeCredits']) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<b>You need to select the credits you want before you can finish editing.</b>
				<?php elseif(	isset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) AND 
								$_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']) : ?>
					<input type="submit" name="disabled" value="Finish Edit" disabled>
					<b>You need to select the alternative Credits given before you can finish editing.</b>			
				<?php else : ?>
					<input type="submit" name="edit" value="Finish Edit">
				<?php endif; ?>
			</div>
		</form>			
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>		