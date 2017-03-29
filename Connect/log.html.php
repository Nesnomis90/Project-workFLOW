<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#logevent {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}

			#logevent tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#logevent th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}

			#logevent tr:hover{background-color:#ddd;}
			
			#logevent tr:nth-child(even) {background-color: #f2f2f2;}
			
			#logevent caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>System Log</title>
	</head>
	<body>
		<p><a href="?addlog">Test adding a log description</a></p>
		<p>Here are all the logs created by the system:</p>
		<table id = "logevent">
			<caption>Log Events</caption>
			<tr>
				<th>Date</th>
				<th>Action</th>
				<th>Action Description</th>
				<th>Log Description</th>
				<th>Delete Log Entry</th>
			</tr>
			<?php foreach ($log as $row): ?>
				<form action="?deletelog" method="post">
					<tr>
						<td><?php htmlout($row['date'])?></td>
						<td><?php htmlout($row['actionName'])?></td>
						<td><?php htmlout($row['actionDescription'])?></td>
						<td><?php htmlout($row['logDescription'])?></td>
						
						<td><input type="submit" value="Delete"></td>
						<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
					</tr>
				</form>
			<?php endforeach; ?>
		</table>
	</body>
</html>