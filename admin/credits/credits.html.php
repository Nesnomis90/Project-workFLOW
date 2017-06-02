<!-- This is the HTML form used for DISPLAYING a list of CREDITS-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#creditstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#creditstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}
			
			#creditstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#creditstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#creditstable tr:hover{background-color:#ddd;}
			
			#creditstable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#creditstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Company Booking Credits</title>
	</head>
	<body>
		<h1>Manage Company Booking Credits</h1>
		<?php if(isset($_SESSION['CreditsUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['CreditsUserFeedback']); ?></b></p>
			<?php unset($_SESSION['CreditsUserFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
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
			<table id="creditstable">
				<caption>Available Credits</caption>
				<tr>
					<th colspan="5">Credits</th>
					<th colspan="2">Dates</th>
					<th colspan="2">Alter Credits</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Monthly Free Booking Time</th>
					<th>Over Credits Fee</th>
					<th>Active for # Companies</th>
					<th>Added</th>
					<th>Last Modified</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php foreach ($credits as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['CreditsName']); ?></td>
							<td><?php htmlout($row['CreditsDescription']); ?></td>
							<td><?php htmlout($row['CreditsGiven']); ?></td>
							<td><?php htmlout($row['CreditsOverCreditsFee']); ?></td>
							<td><?php htmlout($row['CreditsIsUsedByThisManyCompanies']); ?></td>
							<td><?php htmlout($row['CreditsLastModified']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
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
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
