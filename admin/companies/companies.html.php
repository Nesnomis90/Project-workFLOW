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
		<?php if($rowNum>0) :?>
			<p><a href="?add">Add new company</a></p>
			<table id= "companiestable">
				<caption>Registered Companies</caption>
				<tr>
					<th>Company Name (click for employee list)</th>
					<th># of employees</th>
					<th>Booking time used (this month)</th>
					<th>Booking time used (all time)</th>
					<th>Date to be removed</th>
					<th>Created at</th>
					<th>Employee List</th>
					<th></th>
					<th></th>
				</tr>
				<?php foreach ($companies as $company): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($company['CompanyName']); ?></td>
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
							<td><input type="submit" name="action" value="Employees"></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="id" value="<?php echo $company['id']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no companies registered in the database.</b></tr>
			<tr><a href="?add">Add a company?</a></tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	</body>
</html>
