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
				border: 1px solid #ddd;
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
			<div>
				<?php if(isset($_SESSION['equipmentEnableDelete']) AND $_SESSION['equipmentEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		<?php if($rowNum>0) :?>
			<input type="submit" name="action" value="Add Equipment">
			<table id="equipmenttable">
				<caption>Available Equipment</caption>
				<tr>
					<th colspan="3">Equipment</th>
					<th>Date</th>
					<th colspan="2">Alter Equipment</th>
				</tr>
				<tr>
					<th>Name</th>
					<th>Description</th>
					<th>Used In Room</th>
					<th>Added</th>
					<th>Edit</th>
					<th>Delete</th>
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
							<td>
								<?php if(isset($_SESSION['equipmentEnableDelete']) AND $_SESSION['equipmentEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
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
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
