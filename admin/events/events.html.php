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
		<title>Manage Events</title>
	</head>
	<body>
		<h1>Manage Events</h1>
		<form action="" method="post">				
			<div>
				<input type="submit" name="action" value="Create Booking">
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
			<caption>Scheduled Events</caption>
			<tr>
				<th colspan="7">Event information</th>
				<th colspan="3">Alter Event</th>
			</tr>				
			<tr>
				<th>Status</th>
				<th>Room Name</th>
				<th>Start Time</th>
				<th>End Time</th>
				<th>Event Name</th>
				<th>Description</th>
				<th>Created At</th>
				<th>Edit</th>					
				<th>Cancel</th>
				<th>Delete</th>
			</tr>
		<?php if(isset($events)) : ?>						
			<?php foreach ($events AS $event) : ?>
				<form action="" method="post">				
					<tr>
						<td><?php htmlout($event['EventStatus']);?></td>
						<td><?php htmlout($event['EventRoomName']); ?></td>
						<td><?php htmlout($event['StartTime']); ?></td>
						<td><?php htmlout($event['EndTime']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventName']); ?></td>
						<td style="white-space: pre-wrap;"><?php htmlout($event['EventDescription']); ?></td>
						<td><?php htmlout($event['EventWasCreatedOn']); ?></td>
						<td><input type="submit" name="action" value="Edit"></td>							
						<td><input type="submit" name="action" value="Cancel"></td>
						<td>
							<?php if(isset($_SESSION['eventsEnableDelete']) AND $_SESSION['eventsEnableDelete']) : ?>
								<input type="submit" name="action" value="Delete">
							<?php else : ?>
								<input type="submit" name="disabled" value="Delete" disabled>
							<?php endif; ?>
						</td>
						<input type="hidden" name="id" value="<?php htmlout($event['EventID']); ?>">
						<input type="hidden" name="EventInfo" id="EventInfo"
						value="<?php htmlout($event['EventInfo']); ?>">
						<input type="hidden" name="EventStatus" id="EventStatus"
						value="<?php htmlout($event['EventStatus']); ?>">
					</tr>
				</form>
			<?php endforeach; ?>
		<?php endif; ?>
		</table>		
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>	