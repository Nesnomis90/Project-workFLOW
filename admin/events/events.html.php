<!--This is the HTML form for DISPLAYING a list of EVENTS -->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#eventstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}

			#eventstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#eventstable tr:nth-of-type(even) {background-color: #f2f2f2;}
			#eventstable tr:nth-of-type(odd) {background-color: white;}			
			#eventstable tr:hover{background-color:#DBEAE8;}
			
			#eventstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}

			#eventstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}			
			
			#eventstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Scheduled Events</title>
	</head>
	<body>
		<h1>Scheduled Events</h1>
		
		<?php if(isset($_SESSION['EventsUserFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['EventsUserFeedback']); ?></b></p>
			<?php unset($_SESSION['EventsUserFeedback']); ?>
		<?php endif; ?>	
		
		<form action="" method="post">				
			<div>
				<input type="submit" name="action" value="Create Event">
			</div>
			<div>
			<?php if(isset($_SESSION['eventsEnableDelete']) AND $_SESSION['eventsEnableDelete']) : ?>
				<input type="submit" name="action" value="Disable Delete">
			<?php else : ?>
				<input type="submit" name="action" value="Enable Delete">
			<?php endif; ?>
			</div>			
		</form>
		
		<table id="eventstable">
			<caption>Active Events</caption>
			<tr>
				<th colspan="10">Event information</th>
				<th colspan="3">Alter Event</th>
			</tr>				
			<tr>
				<th>Status</th>
				<th>Event Name</th>
				<th>Description</th>
				<th>Active in Room(s)</th>
				<th>Repeat Info</th>				
				<th>Start Time</th>
				<th>End Time</th>
				<th>First Date</th>
				<th>Last Date</th>
				<th>Created At</th>
				<th>Edit</th>
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($activeEvents)) : ?>						
			<?php foreach ($activeEvents AS $event) : ?>
				<form action="" method="post">				
					<tr>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventStatus']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventName']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventDescription']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['UsedMeetingRooms']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['RepeatInfo']); ?></td>
						<td><?php htmlout($event['StartTime']); ?></td>
						<td><?php htmlout($event['EndTime']); ?></td>						
						<td><?php htmlout($event['StartDate']); ?></td>
						<td><?php htmlout($event['LastDate']); ?></td>
						<td><?php htmlout($event['DateTimeCreated']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isset($_SESSION['eventsEnableDelete']) AND $_SESSION['eventsEnableDelete']) : ?>
								<input type="submit" name="action" value="Delete">
							<?php else : ?>
								<input type="submit" name="disabled" value="Delete" disabled>
							<?php endif; ?>
						</td>
						<input type="hidden" name="EventID" value="<?php htmlout($event['EventID']); ?>">
						<input type="hidden" name="EventInfo" id="EventInfo"
						value="<?php htmlout($event['EventInfo']); ?>">
						<input type="hidden" name="EventStatus" id="EventStatus"
						value="<?php htmlout($event['EventStatus']); ?>">
					</tr>
				</form>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="13"><b>There are no active events.</b></td>
			</tr>
		<?php endif; ?>
		</table>
		
		<table id="eventstable">
			<caption>Completed Events</caption>
			<tr>
				<th colspan="10">Event information</th>
				<th colspan="3">Alter Event</th>
			</tr>				
			<tr>
				<th>Status</th>
				<th>Event Name</th>
				<th>Description</th>
				<th>Active in Room(s)</th>
				<th>Repeat Info</th>				
				<th>Start Time</th>
				<th>End Time</th>
				<th>First Date</th>
				<th>Last Date</th>
				<th>Created At</th>
				<th>Edit</th>
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($completedEvents)) : ?>						
			<?php foreach ($completedEvents AS $event) : ?>
				<form action="" method="post">				
					<tr>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventStatus']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventName']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventDescription']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['UsedMeetingRooms']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['RepeatInfo']); ?></td>
						<td><?php htmlout($event['StartTime']); ?></td>
						<td><?php htmlout($event['EndTime']); ?></td>						
						<td><?php htmlout($event['StartDate']); ?></td>
						<td><?php htmlout($event['LastDate']); ?></td>
						<td><?php htmlout($event['DateTimeCreated']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isset($_SESSION['eventsEnableDelete']) AND $_SESSION['eventsEnableDelete']) : ?>
								<input type="submit" name="action" value="Delete">
							<?php else : ?>
								<input type="submit" name="disabled" value="Delete" disabled>
							<?php endif; ?>
						</td>
						<input type="hidden" name="EventID" value="<?php htmlout($event['EventID']); ?>">
						<input type="hidden" name="EventInfo" id="EventInfo"
						value="<?php htmlout($event['EventInfo']); ?>">
						<input type="hidden" name="EventStatus" id="EventStatus"
						value="<?php htmlout($event['EventStatus']); ?>">
					</tr>
				</form>
			<?php endforeach; ?>
		<?php else : ?>
			<tr>
				<td colspan="13"><b>There are no completed events.</b></td>
			</tr>
		<?php endif; ?>
		</table>
		
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>	