<!-- This is the HTML form used for adding an EMPLOYEE for the company owner-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Add Employee</title>
		<style>
			label {
				width: 130px;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<fieldset class="left"><legend>Add An Employee</legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddEmployeeAsOwnerError'])) :?>
						<span><b class="feedback"><?php htmlout($_SESSION['AddEmployeeAsOwnerError']); ?></b></span>
					<?php unset($_SESSION['AddEmployeeAsOwnerError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
			<fieldset class="left"><legend>Company Connection</legend>
				<div>
					<label for="CompanyID">Company Name:</label>
					<span><b><?php htmlout($companyName); ?></b></span>
				</div>
				<div>
					<label for="PositionID">Select Role:</label>
					<select name="PositionID">
						<?php foreach($companyposition as $row): ?>
							<?php if (isSet($selectedPositionID) AND $selectedPositionID == $row['PositionID']) : ?>
								<option selected="selected" value="<?php htmlout($row['PositionID']); ?>"><?php htmlout($row['CompanyPositionName']);?></option>
							<?php elseif(!isSet($selectedPositionID) AND $row['CompanyPositionName'] == "Employee") : ?>
								<option selected="selected" value="<?php htmlout($row['PositionID']); ?>"><?php htmlout($row['CompanyPositionName']);?></option>
							<?php else : ?>
								<option value="<?php htmlout($row['PositionID']); ?>"><?php htmlout($row['CompanyPositionName']);?></option>
							<?php endif; ?>
						<?php endforeach; ?>
					</select>
				</div>
			</fieldset>

			<fieldset class="left"><legend>Create And Add A New User</legend>
				<div>
					<label>New User Email:</label>
					<input type="text" name="registerThenAddUserFromEmail" placeholder="Insert New User's Email"
					value="">
				</div>
			</fieldset>	

			<fieldset class="left"><legend>Add Existing User</legend>
				<div class="left">
					<?php if(isSet($_SESSION['AddEmployeeAsOwnerSearchResult'])) :?>
							<span><b class="feedback"><?php htmlout($_SESSION['AddEmployeeAsOwnerSearchResult']); ?></b></span>
						<?php unset($_SESSION['AddEmployeeAsOwnerSearchResult']); ?>
					<?php endif; ?>
				</div>

				<div>
					<label for="UserID">User:</label>
					<select name="UserID">
						<option value="">Select a User</option>
						<?php foreach($users as $row): ?>
							<?php if(isSet($selectedUserID) AND $selectedUserID == $row['UserID']) : ?>
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
			</fieldset>

			<div class="left">
				<input type="submit" name="action" value="Search">
				<input type="submit" name="action" value="Confirm Employee">
				<input type="submit" name="action" value="Cancel">
			</div>
			</form>
		</fieldset>
	</body>
</html>