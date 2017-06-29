<!--- This form is for displaying log events --->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>System Log</title>
	</head>
	<body>

		<form action="" method="post">
			<fieldset><legend>Manage Log Events</legend>
			<?php if(isset($_SESSION['LogEventUserFeedback'])) : ?>
				<b id="warning"><?php htmlout($_SESSION['LogEventUserFeedback']); ?></b>
				<?php unset($_SESSION['LogEventUserFeedback']); ?>
			<?php endif; ?>			
			<div>
				<fieldset><legend><b>Limit logs by amount: </b></legend>
					<label for="logsToShow">Maximum log events to display: </label>
					<input type="number" name="logsToShow" min="10" max="1000"
					value="<?php htmlout($logLimit); ?>">
					<input type="submit" name="action" value="Set New Maximum"><br />
					<label for="currentLogsDisplayed">Logs currently being displayed: </label>
					<?php if (isset($rowNum)) : ?>
						<b><?php htmlout($rowNum); ?></b>
					<?php else : ?>
						<b>N/A</b>
					<?php endif; ?>
				</fieldset>
			</div>
			<div>
				<fieldset><legend><b>Limit logs by category: </b></legend>
					<input type="checkbox" name="searchAll" value="All" <?php htmlout($checkAll); ?>>All<br />
					<?php foreach($checkboxes AS $checkbox) : ?>
						<?php //checkbox[0] is the log action name ?>
						<?php //checkbox[1] is the text displayed ?>
						<?php //checkbox[2] is if it should have a linefeed ?>
						<?php //checkbox[3] is if it should be checked ?>
						<?php if($checkbox[2]) : ?>
							<?php if($checkbox[3]) : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($checkbox[0]); ?>" checked="checked"><?php htmlout($checkbox[1]); ?><br />
							<?php else : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($checkbox[0]); ?>"><?php htmlout($checkbox[1]); ?><br />
							<?php endif; ?>
						<?php else : ?>
							<?php if($checkbox[3]) : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($checkbox[0]); ?>" checked="checked"><?php htmlout($checkbox[1]); ?>
							<?php else : ?>
								<input type="checkbox" name="search[]" 
								value="<?php htmlout($checkbox[0]); ?>"><?php htmlout($checkbox[1]); ?>
							<?php endif; ?>						
						<?php endif; ?>
					<?php endforeach; ?>
				</fieldset>
			<div>
				<fieldset><legend><b>Limit logs displayed by date: </b></legend>
					<?php if(isset($displayValidatedStartDate) AND isset($displayValidatedEndDate)) : ?>
						<b>Currently displaying logs from <?php htmlout($displayValidatedStartDate); ?> to <?php htmlout($displayValidatedEndDate); ?>.</b>
					<?php elseif(isset($displayValidatedStartDate) AND !isset($displayValidatedEndDate)) : ?>
						<b>Currently displaying logs from <?php htmlout($displayValidatedStartDate); ?> to today.</b>
					<?php elseif(!isset($displayValidatedStartDate) AND isset($displayValidatedEndDate)) : ?>
						<b>Currently displaying logs from the beginning up to <?php htmlout($displayValidatedEndDate); ?>.</b>
					<?php else : ?>
						<?php if($invalidInput AND !$noCheckedCheckboxes) : ?>
							<b>Currently not displaying any logs due to an incorrect date being submitted.</b>
						<?php else : ?>
							<b>Currently displaying logs from the beginning up to today.</b>
						<?php endif; ?>
					<?php endif; ?> <br />
					<label for="filterStartDate">Earliest date to display logs from: </label>
					<input type="text" name="filterStartDate" 
					value="<?php htmlout($validatedStartDate); ?>"><br />
					<label for="filterEndDate">Latest date to display logs from: </label>
					<input type="text" name="filterEndDate"
					value="<?php htmlout($validatedEndDate); ?>">
				</fieldset>
			</div>
			
			<div>
				<input type="submit" name="action" value="Refresh Logs">
			</div>
			
			<div style="position: absolute; right: 10px; bottom: 10px;">
				<?php if(isset($_SESSION['logEventsEnableDelete']) AND $_SESSION['logEventsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
			</fieldset>
		</form>
		
		<table>
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
			<?php if(isset($search) OR isset($searchAll)) : ?>
				<?php if(isset($rowNum) AND $rowNum>0) :?>
					<?php foreach ($log as $row): ?>
						<form action="" method="post">
							<tr>
								<td><?php htmlout($row['date']); ?></td>
								<td><?php htmlout($row['actionName']); ?></td>
								<td><?php htmlout($row['actionDescription']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['logDescription']); ?></td>
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
				<?php elseif($invalidInput) : ?>
					<tr><td colspan="5"><b>No logs could be found due to an incorrect date being submitted.</b></td></tr>
				<?php endif; ?>
			<?php elseif(isset($noCheckedCheckboxes) AND $noCheckedCheckboxes) : ?>
				<tr><td colspan="5"><b>No log event categories has been selected.</b></td></tr>
			<?php elseif(!isset($noCheckedCheckboxes) AND $invalidInput) : ?>
				<tr><td colspan="5"><b>No logs could be found due to no categories being selected and an incorrect date being submitted.</b></td></tr>
			<?php endif; ?>
		</table>
		
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>