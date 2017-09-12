<!-- This is the HTML form used for DISPLAYING a list of EMPLOYEES for users in a company-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Company Employees</title>
	</head>
	<body onload="startTime()">

	<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<div class="left">
			<?php if(isSet($_SESSION['EmployeeUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['EmployeeUserFeedback']); ?></b></span>
				<?php unset($_SESSION['EmployeeUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<table>
			<caption>Company Employees</caption>
			<tr>
				<th>Company</th>
				<th colspan="3">User Information</th>
				<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
					<th colspan="3">Booking Time Used</th>
				<?php endif; ?>
				<th>Date</th>
				<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
					<th colspan="2">Alter Employee</th>
				<?php endif; ?>
			</tr>
			<tr>
				<th>Role</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
					<th>Previous Month</th>
					<th>This Month</th>
					<th>All Time</th>
				<?php endif; ?>
				<th>Added</th>
				<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
					<th>Change Role</th>
					<th>Remove</th>
				<?php endif; ?>
			</tr>
			<?php if($rowNum > 0) :?>
				<?php foreach ($employees as $employee): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($employee['PositionName']); ?></td>
							<td><?php htmlout($employee['firstName']); ?></td>
							<td><?php htmlout($employee['lastName']); ?></td>
							<td><?php htmlout($employee['email']); ?></td>
							<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
								<td><?php htmlout($employee['PreviousMonthBookingTimeUsed']); ?></td>
								<td><?php htmlout($employee['MonthlyBookingTimeUsed']); ?></td>
								<td><?php htmlout($employee['TotalBookingTimeUsed']); ?></td>
							<?php endif; ?>
							<td><?php htmlout($employee['StartDateTime']); ?></td>
							<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
								<?php if($_SESSION['LoggedInUserID'] != $employee['UsrID']) : ?>
									<td><input type="submit" name="action" value="Change Role"></td>
									<td><input type="submit" name="action" value="Remove"></td>
								<?php else : ?>
									<td></td>
									<td></td>
								<?php endif; ?>
							<?php endif; ?>
							<input type="hidden" name="CompanyName" value="<?php htmlout($employee['CompanyName']); ?>">
							<input type="hidden" name="UserID" value="<?php htmlout($employee['UsrID']); ?>">
							<input type="hidden" name="CompanyID" value="<?php htmlout($employee['CompanyID']); ?>">
							<input type="hidden" name="UserName" value="<?php htmlout($employee['lastName'] . ", " . $employee['firstName']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><b>There are no employees connected to this company.</b></tr>
			<?php endif; ?>

			<?php if(isSet($companyRole) AND $companyRole == "Owner") : ?>
				<form action="" method="post">
					<tr>
						<td colspan="11">
							<input type="hidden" name="action" value="Add Employee">
							<input type="submit" style="font-size: 150%" value="+">
						</td>
					</tr>
				</form>
			<?php endif; ?>

			<?php if(isSet($removedEmployees) AND isSet($companyRole) AND $companyRole == "Owner") : ?>
				<tr>
					<td colspan="11"><b>The Following Are Previously Employed Users With Booking Time</b></td>
				</tr>
				<?php foreach($removedEmployees as $employee): ?>
					<tr>
						<td>Removed</td>
						<td><?php htmlout($employee['firstName']); ?></td>
						<td><?php htmlout($employee['lastName']); ?></td>
						<td><?php htmlout($employee['email']); ?></td>
						<td><?php htmlout($employee['PreviousMonthBookingTimeUsed']); ?></td>
						<td><?php htmlout($employee['MonthlyBookingTimeUsed']); ?></td>
						<td><?php htmlout($employee['TotalBookingTimeUsed']); ?></td>
						<td colspan="3">N/A</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if(isSet($deletedEmployees) AND isSet($companyRole) AND $companyRole == "Owner") : ?>
				<tr>
					<td colspan="11"><b>The Following Is A Summation Of Booking Time By Deleted Users</b></td>
				</tr>
				<?php foreach($deletedEmployees as $employee): ?>
					<tr>
						<td>Deleted</td>
						<td colspan="3">Every Deleted User Summed Together</td>
						<td><?php htmlout($employee['PreviousMonthBookingTimeUsed']); ?></td>
						<td><?php htmlout($employee['MonthlyBookingTimeUsed']); ?></td>
						<td><?php htmlout($employee['TotalBookingTimeUsed']); ?></td>
						<td colspan="3">N/A</td>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</table>
	</body>
</html>
