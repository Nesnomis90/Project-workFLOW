<!-- This is the HTML form used for adding an EMPLOYEE-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Add Employee</title>
	</head>
	<body>
		<h1>Add Employee</h1>
		<?php if(isset($AddEmployeeError)) :?>
			<div>
				<p><b><?php htmlout($AddEmployeeError); ?> </b></p>
			</div>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="CompanyID">Company name:</label>
				<?php if(!isset($_GET['Company'])) : ?>
					<select name="CompanyID" id="CompanyID">
						<option value="">Select a Company</option>
						<?php foreach($companies as $row): ?> 
							<?php if (isset($selectedCompanyID) AND $selectedCompanyID == $row['CompanyID']) : ?>
								<option selected="selected" value=<?php htmlout($row['CompanyID']); ?>>
										<?php htmlout($row['CompanyName']);?>
								</option>
							<?php else : ?>
								<option value=<?php htmlout($row['CompanyID']); ?>>
										<?php htmlout($row['CompanyName']);?>
								</option>
							<?php endif; ?>					
						<?php endforeach; ?>
					</select>
				<?php else :?>
					<b><?php htmlout($companies['CompanyName']); ?></b>
				<?php endif; ?>
			</div>
			<?php if(!isset($_GET['Company'])) :?>
				<div>
					<label for="companysearchstring">Search for Company:</label>
					<input type="text" name="companysearchstring" 
					value=<?php htmlout($companysearchstring); ?>>
				</div>
			<?php endif; ?>
			<div>
				<label for="UserID">User:</label>
				<select name="UserID" id="UserID">
					<option value="">Select a User</option>
					<?php foreach($users as $row): ?>
						<?php if (isset($selectedUserID) AND $selectedUserID == $row['UserID']) : ?>
							<option selected="selected" value=<?php htmlout($row['UserID']); ?>>
									<?php htmlout($row['UserIdentifier']);?>>
							</option>
						<?php else : ?>
							<option value=<?php htmlout($row['UserID']); ?>>
									<?php htmlout($row['UserIdentifier']);?>
							</option>
						<?php endif; ?>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<label for="usersearchstring">Search for User:</label>
				<input type="text" name="usersearchstring" 
				value=<?php htmlout($usersearchstring); ?>>
			</div>
			<div>
				<label for="PositionID">Select Role:</label>
				<select name="PositionID" id="PositionID">
					<?php foreach($companyposition as $row): ?> 
						<option value=<?php htmlout($row['PositionID']); ?>>
								<?php htmlout($row['CompanyPositionName']);?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
			<div>
				<input type="submit" name="action" value="Search">
				<input type="submit" name="action" value="Confirm Employee">
				<input type="submit" name="action" value="Cancel">
			</div>
			<div>
				<input type="reset">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>