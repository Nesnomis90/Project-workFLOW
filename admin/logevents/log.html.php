<!--- This form is for displaying log events --->
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
				border: 1px solid #ddd;
			}

			#logevent td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
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
		<h1>Manage Log Events</h1>
		<?php if(isset($_SESSION['LogEventUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['LogEventUserFeedback']); ?></b></p>
			<?php unset($_SESSION['LogEventUserFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="logsToShow">Maximum log events to display: </label>
				<input type="number" name="logsToShow" min="10" max="1000"
				value="<?php htmlout($logLimit); ?>">
				<input type="submit" name="action" value="Set New Maximum">
			</div>
			<div>
				<label for="currentLogsDisplayed">Logs currently being displayed: </label>
				<?php if (isset($rowNum)) : ?>
					<b><?php htmlout($rowNum); ?></b>
				<?php else : ?>
					<b>N/A</b>
				<?php endif; ?>
			</div>
			<div>
				<label for="checkboxSearch">Select what logs to display: </label>
			</div>
			<div>
				<b>Limit logs displayed by category: </b><br />
				<input type="checkbox" name="searchAll" value="All" <?php htmlout($checkAll); ?>>All<br />
				<?php foreach($checkboxes AS $checkbox) : ?>
					<?php foreach($checkbox AS $info) : ?>
						<?php //info[0] is the log action name ?>
						<?php //info[1] is the text displayed ?>
						<?php //info[2] is if it should have a linefeed ?>
						<?php //info[3] is if it should be checked ?>
						<?php if($info[2]) : ?>
							<?php if($info[3]) : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($info[0]); ?>" checked="checked"><?php htmlout($info[1]); ?></br />
							<?php else : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($info[0]); ?>"><?php htmlout($info[1]); ?></br />
							<?php endif; ?>
						<?php else : ?>
							<?php if($info[3]) : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($info[0]); ?>" checked="checked"><?php htmlout($info[1]); ?>
							<?php else : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($info[0]); ?>"><?php htmlout($info[1]); ?>
							<?php endif; ?>						
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endforeach; ?>
			<div>
				<b>Limit logs displayed by date: </b><br />
				<?php if(isset($displayValidatedStartDate) AND isset($displayValidatedEndDate)) : ?>
					<b>Currently displaying logs from <?php htmlout($displayValidatedStartDate); ?> to <?php htmlout($displayValidatedEndDate); ?>.</b>
				<?php elseif(isset($displayValidatedStartDate) AND !isset($displayValidatedEndDate)) : ?>
					<b>Currently displaying logs from <?php htmlout($displayValidatedStartDate); ?> to today.</b>
				<?php elseif(!isset($displayValidatedStartDate) AND isset($displayValidatedEndDate)) : ?>
					<b>Currently displaying logs from the beginning up to <?php htmlout($displayValidatedStartDate); ?>.</b>
				<?php else : ?>
					<b>Currently displaying logs from the beginning up to today.</b>
				<?php endif; ?> <br />
				<label for="filterStartDate">Earliest date to display logs from: </label>
				<input type="text" name="filterStartDate" 
				value="<?php htmlout($filterStartDate); ?>"><br />
				<label for="filterEndDate">Latest date to display logs from: </label>
				<input type="text" name="filterEndDate"
				value="<?php htmlout($filterEndDate); ?>">
			</div>
			</div>
				<input type="submit" name="action" value="Refresh Logs">
			</div>		
			<div>
				<?php if(isset($_SESSION['logEventsEnableDelete']) AND $_SESSION['logEventsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>
		<table id = "logevent">
			<caption>Log Events</caption>
			<tr>
				<th>Date</th>
				<th colspan="2">Log Action</th>
				<th>Log Information</th>
				<th>Alter Log</th>
			</tr>
			<tr>
				<th>Created</th>
				<th>Name</th>
				<th>Description</th>
				<th>Description</th>
				<th>Delete</th>
			</tr>
			<?php if(isset($rowNum) AND $rowNum>0) :?>
				<?php foreach ($log as $row): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($row['date'])?></td>
							<td><?php htmlout($row['actionName'])?></td>
							<td><?php htmlout($row['actionDescription'])?></td>
							<td><?php htmlout($row['logDescription'])?></td>
							<td>
								<?php if(isset($_SESSION['logEventsEnableDelete']) AND $_SESSION['logEventsEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="id" value="<?php echo $row['id']; ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			<?php elseif(isset($rowNum) AND $rowNum < 1) : ?>
				<tr><td colspan="5"><b>There are no log events that match your search.</b></td></tr>
			<?php elseif(!isset($rowNum)) : ?>
				<tr><td colspan="5"><b>No log event categories has been selected.</b></td></tr>
			<?php endif; ?>
		</table>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>