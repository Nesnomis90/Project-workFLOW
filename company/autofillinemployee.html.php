<!-- This is the HTML form used for adding an EMPLOYEE for the company owner-->
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

		<fieldset class="left"><legend>Add Employee As Requested</legend>
			<div class="left">
				<?php if(isSet($_SESSION['AddEmployeeAsRequestedError'])) :?>
						<span><b class="feedback"><?php htmlout($_SESSION['AddEmployeeAsRequestedError']); ?></b></span>
					<?php unset($_SESSION['AddEmployeeAsRequestedError']); ?>
				<?php endif; ?>
			</div>

			<form action="" method="post">
			<fieldset class="left"><legend>Company Connection</legend>
				<div>
					<label>Company Name:</label>
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

			<fieldset class="left"><legend>User Information</legend>
				<div>
					<label>Name: </label>
					<span><b><?php htmlout($userName); ?></b></span>
					<label>Email: </label>
					<span><b><?php htmlout($userEmail); ?></b></span>
					<input type="hidden" name="UserName" value="<?php htmlout($userName); ?>">
					<input type="hidden" name="UserID" value="<?php htmlout($userID); ?>">
					<input type="hidden" name="Email" value="<?php htmlout($userEmail); ?>">
				</div>
			</fieldset>

			<div class="left">
				<input type="submit" name="action" value="Confirm Employee">
				<input type="submit" name="action" value="Cancel">
			</div>
			</form>
		</fieldset>
	</body>
</html>