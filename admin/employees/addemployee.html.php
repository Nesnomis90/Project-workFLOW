<!-- This is the HTML form used for adding an EMPLOYEE-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/adminnavcheck.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Add Employee</title>
		<style>
			label {
				width: 150px;
			}
		</style>		
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset class="left"><legend>Add Employee</legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddEmployeeError'])) :?>
						<span><b class="feedback"><?php htmlout($_SESSION['AddEmployeeError']); ?></b></span>
					<?php unset($_SESSION['AddEmployeeError']); ?>
				<?php endif; ?>
			</div>
			
			<div class="left">
				<?php if(isSet($_SESSION['AddEmployeeSearchResult'])) :?>
						<span><b class="feedback"><?php htmlout($_SESSION['AddEmployeeSearchResult']); ?></b></span>
					<?php unset($_SESSION['AddEmployeeSearchResult']); ?>
				<?php endif; ?>
			</div>
			
			<form action="" method="post">
				<div>
					<label for="CompanyID">Company name:</label>
					<?php if(!isSet($_GET['Company'])) : ?>
						<select name="CompanyID" id="CompanyID">
							<option value="">Select a Company</option>
							<?php foreach($companies as $row): ?> 
								<?php if (isSet($selectedCompanyID) AND $selectedCompanyID == $row['CompanyID']) : ?>
									<option selected="selected" value="<?php htmlout($row['CompanyID']); ?>">
											<?php htmlout($row['CompanyName']);?>
									</option>
								<?php else : ?>
									<option value="<?php htmlout($row['CompanyID']); ?>">
											<?php htmlout($row['CompanyName']);?>
									</option>
								<?php endif; ?>					
							<?php endforeach; ?>
						</select>
					<?php else :?>
						<span><b><?php htmlout($companies['CompanyName']); ?></b></span>
					<?php endif; ?>
				</div>
				<?php if(!isSet($_GET['Company'])) :?>
					<div>
						<label for="companysearchstring">Search for Company:</label>
						<input type="text" name="companysearchstring" 
						value="<?php htmlout($companysearchstring); ?>">
					</div>
				<?php endif; ?>
				<div>
					<label for="UserID">User:</label>
					<select name="UserID" id="UserID">
						<option value="">Select a User</option>
						<?php foreach($users as $row): ?>
							<?php if (isSet($selectedUserID) AND $selectedUserID == $row['UserID']) : ?>
								<option selected="selected" value="<?php htmlout($row['UserID']); ?>"><?php htmlout($row['UserIdentifier']);?></option>
							<?php else : ?>
								<option value="<?php htmlout($row['UserID']); ?>"><?php htmlout($row['UserIdentifier']);?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="usersearchstring">Search for User:</label>
					<input type="text" name="usersearchstring" 
					value="<?php htmlout($usersearchstring); ?>">
				</div>
				<div>
					<label for="PositionID">Select Role:</label>
					<select name="PositionID" id="PositionID">
						<?php foreach($companyposition as $row): ?>
							<?php if (isSet($selectedPositionID) AND $selectedPositionID == $row['PositionID']) : ?>
								<option selected="selected" value="<?php htmlout($row['PositionID']); ?>"><?php htmlout($row['CompanyPositionName']);?></option>
							<?php else : ?>
								<option value="<?php htmlout($row['PositionID']); ?>"><?php htmlout($row['CompanyPositionName']);?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="left">
					<input type="submit" name="action" value="Search">
					<input type="submit" name="action" value="Confirm Employee">
					<input type="submit" name="action" value="Cancel">
				</div>
			</form>
		</fieldset>
		
	<div class="left"><a href="/admin/">Return to CMS home</a></div>
	</body>
</html>