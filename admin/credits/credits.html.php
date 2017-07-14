<!-- This is the HTML form used for DISPLAYING a list of CREDITS-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Manage Company Booking Credits</title>
	</head>
	<body>
		<h1>Manage Company Booking Credits</h1>
		
		<div class="left">
			<?php if(isset($_SESSION['CreditsUserFeedback'])) : ?>
				<span class="feedback"><b><?php htmlout($_SESSION['CreditsUserFeedback']); ?></b></span>
				<?php unset($_SESSION['CreditsUserFeedback']); ?>
			<?php endif; ?>
		</div>
		
		<form action="" method="post">
			<div class="right">
				<?php if(isset($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
					<?php if(isset($_SESSION['creditsEnableDeleteUsedCredits']) AND $_SESSION['creditsEnableDeleteUsedCredits']) : ?>
						<input type="submit" name="action" value="Disable Delete Used Credits">
					<?php else : ?>
						<input type="submit" name="action" value="Enable Delete Used Credits">
					<?php endif; ?>
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		<?php if($rowNum>0) :?>
			<input type="submit" name="action" value="Add Credits">
			<table>
				<caption>Available Credits</caption>
				<tr>
					<th colspan="6">Credits</th>
					<th colspan="2">Dates</th>
					<th colspan="2">Alter Credits</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Monthly Given Booking Time</th>
					<th>Monthly Subscription Cost</th>
					<th>Over Credits Fee</th>
					<th>Active for # Companies</th>
					<th>Last Modified</th>
					<th>Added</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php foreach ($credits as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['CreditsName']); ?></td>
							<td><?php htmlout($row['CreditsDescription']); ?></td>
							<td><?php htmlout($row['CreditsGiven']); ?></td>
							<td><?php htmlout($row['CreditsMonthlyPrice']); ?></td>
							<td><?php htmlout($row['CreditsOverCreditsFee']); ?></td>
							<td class="alignmid"><?php htmlout($row['CreditsIsUsedByThisManyCompanies']); ?></td>
							<td><?php htmlout($row['CreditsLastModified']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td class="alignmid"><input type="submit" name="action" value="Edit"></td>
							<td class="alignmid">
								<?php if($row['CreditsName'] == 'Default') : ?>
									<b>N/A</b>
								<?php else : ?>
									<?php if(isset($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete'] AND
											$row['CreditsIsUsedByThisManyCompanies'] == 0) : ?>
										<input type="submit" name="action" value="Delete">
									<?php elseif(isset($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete'] AND
											$row['CreditsIsUsedByThisManyCompanies'] != 0) : ?>
										<?php if(isset($_SESSION['creditsEnableDeleteUsedCredits']) AND $_SESSION['creditsEnableDeleteUsedCredits']) : ?>
											<input type="submit" name="action" value="Delete">
										<?php else : ?>
											<b>Not Enabled</b>
											<input type="submit" name="disabled" value="Delete" disabled>
										<?php endif; ?>									
									<?php else : ?>
										<input type="submit" name="disabled" value="Delete" disabled>
									<?php endif; ?>
								<?php endif; ?>
							</td>
							<input type="hidden" id="CreditsName" name="CreditsName"
							value="<?php htmlout($row['CreditsName']); ?>">
							<input type="hidden" name="CreditsID" value="<?php htmlout($row['TheCreditsID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no Credits registered in the database. This should never occur as the system needs a Credits named Default to function properly.</b></tr>
			<tr><input type="submit" name="action" value="Add Credits"></tr>
		<?php endif; ?>
		</form>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
	
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
