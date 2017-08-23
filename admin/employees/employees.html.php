<!-- This is the HTML form used for DISPLAYING a list of EMPLOYEES for admin-->
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
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Company Employees</h1>
		
		<div class="left">
			<?php if(isSet($_SESSION['EmployeeAdminFeedback'])) : ?>
				<span style="white-space: pre-wrap;"><b><?php htmlout($_SESSION['EmployeeAdminFeedback']); ?></b></span>
				<?php unset($_SESSION['EmployeeAdminFeedback']); ?>
			<?php endif; ?>
		</div>

		<?php if(isSet($_GET['Company'])) : ?>
			<div class="left">
				<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/companies/"; ?>
				<form action="<?php htmlout($goto); ?>" method="post">
					<input type="submit" value="Return to Companies">
				</form>
				<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/employees/"; ?>
				<form action="<?php htmlout($goto); ?>" method="post">
					<input type="submit" value="Get All Employees">
				</form>
			</div>
		<?php endif; ?>
	
		<form action="" method="post">
			<div class="left">
				<input type="submit" name="action" value="Add Employee">
			</div>
			<div class="right">
				<?php if(isSet($_SESSION['employeesEnableDelete']) AND $_SESSION['employeesEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Remove">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Remove">
				<?php endif; ?>
			</div>
		</form>

		<table>
			<caption>Company Employees</caption>
			<tr>
				<th colspan="2">Company</th>
				<th colspan="3">User Information</th>
				<th colspan="3">Booking Time Used</th>
				<th>Date</th>
				<th colspan="3">Alter Employee</th>
			</tr>
			<tr>
				<th>Name</th>
				<th>Role</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Email</th>
				<th>Previous Month</th>
				<th>This Month</th>
				<th>All Time</th>
				<th>Added</th>
				<th>Transfer</th>
				<th>Change Role</th>
				<th>Remove</th>
			</tr>
			<?php if($rowNum > 0) : ?>
				<?php if(isSet($_GET['Company']) AND 
						(isSet($deletedEmployees) OR isSet($removedEmployees))) : ?>
				<tr>
					<td colspan="12"><b>The Following Are Currently Employed Users</b></td>
				</tr>
				<?php endif; ?>
				<?php foreach ($employees as $employee) : ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($employee['CompanyName']); ?></td>
							<td><?php htmlout($employee['PositionName']); ?></td>
							<td><?php htmlout($employee['firstName']); ?></td>
							<td><?php htmlout($employee['lastName']); ?></td>
							<td><?php htmlout($employee['email']); ?></td>
							<td><?php htmlout($employee['PreviousMonthBookingTimeUsed']); ?></td>							
							<td><?php htmlout($employee['MonthlyBookingTimeUsed']); ?></td>
							<td><?php htmlout($employee['TotalBookingTimeUsed']); ?></td>
							<td><?php htmlout($employee['StartDateTime']); ?></td>
							<td><input type="submit" name="action" value="Transfer"></td>
							<td><input type="submit" name="action" value="Change Role"></td>
							<td>
								<?php if(isSet($_SESSION['employeesEnableDelete']) AND $_SESSION['employeesEnableDelete']) : ?>
									<input type="submit" name="action" value="Remove">
								<?php else : ?>
									<input type="submit" name="disabled" value="Remove" disabled>
								<?php endif; ?>
							</td>	
							<input type="hidden" name="UserID" value="<?php htmlout($employee['UsrID']); ?>">
							<input type="hidden" name="CompanyID" value="<?php htmlout($employee['CompanyID']); ?>">
							<input type="hidden" name="CompanyName" value="<?php htmlout($employee['CompanyName']); ?>">
							<input type="hidden" name="UserName" value="<?php htmlout($employee['lastName'] . ", " . $employee['firstName']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php else : ?>
				<tr><td colspan="12"><b>There are no employees registered in the database.</b></td></tr>
			<?php endif; ?>	
				<?php if(isSet($removedEmployees)) : ?>
					<tr>
						<td colspan="12"><b>The Following Are Previously Employed Users</b></td>
					</tr>
					<?php foreach($removedEmployees as $employee) : ?>
						<tr>
							<td><?php htmlout($employee['CompanyName']); ?></td>
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
				<?php if(isSet($deletedEmployees)) : ?>
					<tr>
						<td colspan="12"><b>The Following Is A Summation Of Deleted Users</b></td>
					</tr>
					<?php foreach($deletedEmployees as $employee) : ?>
						<tr>
							<td><?php htmlout($employee['CompanyName']); ?></td>
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

	<div class="left"><a href="..">Return to CMS home</a></div>

	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
