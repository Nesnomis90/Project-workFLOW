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
				<?php if(isset($_SESSION['logEventsEnableDelete']) AND $_SESSION['logEventsEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
			<div>
				<label for="logsToShow">Maximum log events shown: </label>
				<input type="number" name="logsToShow" min="10" max="1000"
				value="<?php htmlout($logLimit); ?>">
				<input type="submit" name="action" value="Set New Maximum">
			</div>
			<div>
				<label for="currentLogsDisplayed">Logs currently being displayed: </label>
				<b><?php htmlout($rowNum); ?></b>
			</div>
			<div>
				<label for="checkboxSearch">Select what logs to display: </label>
			</div>
			<div>
				<input type="checkbox" name="search" value="All" >All<br />
				<input type="checkbox" name="search" value="AllAccount">All Account
				<input type="checkbox" name="search" value="AccountActivated">Account Activated
				<input type="checkbox" name="search" value="AccountCreated">Account Created
				<input type="checkbox" name="search" value="AccountRemoved">Account Removed<br />
				<input type="checkbox" name="search" value="AllBooking">All Booking
				<input type="checkbox" name="search" value="BookingCancelled">Booking Cancelled
				<input type="checkbox" name="search" value="BookingCompleted">Booking Completed
				<input type="checkbox" name="search" value="BookingCreated">Booking Created
				<input type="checkbox" name="search" value="BookingRemoved">Booking Removed<br />
				<input type="checkbox" name="search" value="CompanyCreated">Company Created
				<input type="checkbox" name="search" value="CompanyRemoved">Company Removed<br />
				<input type="checkbox" name="search" value="DatabaseCreated">Database Created
				<input type="checkbox" name="search" value="TableCreated">Database Table Created<br />
				<input type="checkbox" name="search" value="EmployeeAdded">Employee Added
				<input type="checkbox" name="search" value="EmployeeRemoved">Employee Removed<br />
				<input type="checkbox" name="search" value="EquipmentAdded">Equipment Added
				<input type="checkbox" name="search" value="EquipmentRemoved">Equipment Removed<br />
				<input type="checkbox" name="search" value="MeetingRoomAdded">Meeting Room Added
				<input type="checkbox" name="search" value="MeetingRoomRemoved">Meeting Room Removed<br />
				<input type="checkbox" name="search" value="RoomEquipmentAdded">Room Equipment Added
				<input type="checkbox" name="search" value="RoomEquipmentRemoved">Room Equipment Removed<br />
				<input type="submit" name="action" value="Refresh Logs">
			</div>
		</form>
		<table id = "logevent">
			<caption>Log Events</caption>
			<tr>
				<th>Date</th>
				<th>Action</th>
				<th>Action Description</th>
				<th>Log Description</th>
				<th>Delete Log Entry</th>
			</tr>
			<?php if($rowNum>0) :?>
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
			<?php else : ?>
				<tr><b>There are no log events that match your search.</b></tr>
			<?php endif; ?>
		</table>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>