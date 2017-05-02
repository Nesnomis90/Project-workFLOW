<!-- This is the HTML form used for DISPLAYING a list of COMPANIES-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#companiestable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#companiestable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}
			
			#companiestable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#companiestable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#companiestable tr:hover{background-color:#ddd;}
			
			#companiestable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#companiestable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Companies</title>
	</head>
	<body>
		<h1>Manage Companies</h1>
		<?php if(isset($_SESSION['CompanyUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['CompanyUserFeedback']); ?></b></p>
			<?php unset($_SESSION['CompanyUserFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<?php if(isset($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<input type="submit" name="action" value="Create Company">
			</form>
			<table id="companiestable">
				<caption>Registered Companies</caption>
				<tr>
					<th>Employee List</th>
					<th>Company Name (click for employee list)</th>
					<th># of employees</th>
					<th>Booking time used (this month)</th>
					<th>Booking time used (all time)</th>
					<th>Date to be removed</th>
					<th>Created at</th>
					<th></th>
					<th></th>
				</tr>
				<?php foreach ($companies as $company): ?>
					<tr>
						<?php $goto = "http://$_SERVER[HTTP_HOST]/admin/employees/?Company=" . $company['id'];?>
						<form action="<?php htmlout($goto) ;?>" method="post">
							<td>
								<input type="submit" value="Employees">
								<input type="hidden" name="Company" value="<?php htmlout($company['id']); ?>">
							</td>
						</form>
						<form action="" method="post">
							<td>
								<?php htmlout($company['CompanyName']); ?>
								<input type="hidden" id="CompanyName" name="CompanyName" 
								value="<?php htmlout($company['CompanyName']); ?>"> 
							</td>
							<td><?php htmlout($company['NumberOfEmployees']); ?></td>
							<td><?php htmlout($company['MonthlyCompanyWideBookingTimeUsed']); ?></td>
							<td><?php htmlout($company['TotalCompanyWideBookingTimeUsed']); ?></td>
							<?php if($company['DeletionDate'] == null) :?>
									<td>
										<p>No Date Set</p>
									</td>
							<?php elseif($company['DeletionDate'] != null) : ?>
									<td>
										<p><?php htmlout($company['DeletionDate']); ?></p>
										<input type="submit" name="action" value="Cancel Date">
									</td>
							<?php endif; ?>
							<td><?php htmlout($company['DatetimeCreated']); ?></td>							
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isset($_SESSION['companiesEnableDelete']) AND $_SESSION['companiesEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="id" value="<?php htmlout($company['id']); ?>">
						</form>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no companies registered in the database.</b></tr>
			<tr>
				<form action="" method="post">
					<input type="submit" name="action" value="Create Company">
				</form>
			</tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>
