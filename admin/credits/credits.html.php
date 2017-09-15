<!-- This is the HTML form used for DISPLAYING a list of CREDITS-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Company Booking Credits</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Company Booking Credits</h1>

		<form action="" method="post">
			<div class="right">
				<?php if(isSet($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
					<?php if(isSet($_SESSION['creditsEnableDeleteUsedCredits']) AND $_SESSION['creditsEnableDeleteUsedCredits']) : ?>
						<input type="submit" name="action" value="Disable Delete Used Credits">
					<?php else : ?>
						<input type="submit" name="action" value="Enable Delete Used Credits">
					<?php endif; ?>
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>

		<div class="left">
			<?php if(isSet($_SESSION['CreditsUserFeedback'])) : ?>
				<span class="feedback"><b><?php htmlout($_SESSION['CreditsUserFeedback']); ?></b></span>
				<?php unset($_SESSION['CreditsUserFeedback']); ?>
			<?php endif; ?>
		</div>

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
				<th>Active for # of Companies</th>
				<th>Last Modified</th>
				<th>Added</th>
				<th>Edit</th>
				<th>Delete</th>
			</tr>
			<?php if($rowNum>0) :?>
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
									<?php if(isSet($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete'] AND
											$row['CreditsIsUsedByThisManyCompanies'] == 0) : ?>
										<input type="submit" name="action" value="Delete">
									<?php elseif(isSet($_SESSION['creditsEnableDelete']) AND $_SESSION['creditsEnableDelete'] AND
											$row['CreditsIsUsedByThisManyCompanies'] != 0) : ?>
										<?php if(isSet($_SESSION['creditsEnableDeleteUsedCredits']) AND $_SESSION['creditsEnableDeleteUsedCredits']) : ?>
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
			<?php else : ?>
				<tr><b>There are no Credits registered in the database. This should never occur as the system needs a Credits named Default to function properly.</b></tr>
			<?php endif; ?>

			<form action="" method="post">
				<tr>
					<td colspan="10">
						<input type="hidden" name="action" value="Add Credits">
						<input type="submit" style="font-size: 150%; color: green;" value="+">
					</td>
				</tr>
			</form>	

		</table>

	<div class="left"><a href="..">Return to CMS home</a></div>
	</body>
</html>
