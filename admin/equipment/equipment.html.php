<!-- This is the HTML form used for DISPLAYING a list of EQUIPMENT-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#equipmenttable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#equipmenttable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}
			
			#equipmenttable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#equipmenttable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#equipmenttable tr:hover{background-color:#ddd;}
			
			#equipmenttable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#equipmenttable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Meeting Room Equipment</title>
	</head>
	<body>
		<h1>Manage Meeting Room Equipment</h1>
		<?php if(isset($_SESSION['EquipmentUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['EquipmentUserFeedback']); ?></b></p>
			<?php unset($_SESSION['EquipmentUserFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
		<?php if($rowNum>0) :?>
			<input type="submit" name="action" value="Add Equipment">
			<table id= "equipmenttable">
				<caption>Available Equipment</caption>
				<tr>
					<th>Equipment Name</th>
					<th>Equipment Description</th>
					<th>Rooms Currently Using This Equipment</th>
					<th>Added at</th>
					<th></th>
					<th></th>
				</tr>
				<?php foreach ($equipment as $row): ?>
					<form action="" method="post">
						<tr>
							<td>
								<?php htmlout($row['EquipmentName']); ?>
								<input type="hidden" id="EquipmentName" name="EquipmentName"
								value="<?php htmlout($row['EquipmentName']); ?>">
							</td>
							<td><?php htmlout($row['EquipmentDescription']); ?></td>
							<td><?php htmlout($row['EquipmentIsInTheseRooms']); ?></td>
							<td><?php htmlout($row['DateTimeAdded']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>
							<td><input type="submit" name="action" value="Remove"></td>
							<input type="hidden" name="EquipmentID" value="<?php echo $row['TheEquipmentID']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no employees in this company registered in the database.</b></tr>
			<tr><input type="submit" name="action" value="Add Equipment"></tr>
		<?php endif; ?>
		</form>
		<p><a href="..">Return to CMS home</a></p>
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>
