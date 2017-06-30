<!-- This is the HTML form used for ADDING EVENT information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">		
		<title>Schedule A New Event</title>
	</head>
	<body>
		<h1>Schedule A New Event</h1>
		
		<div class="warning">
		<?php if(isset($_SESSION['AddEventError'])) : ?>
			<b><?php htmlout($_SESSION['AddEventError']); ?></b>
			<?php unset($_SESSION['AddEventError']); ?>
		<?php endif; ?>
		</div>
<!--- TO-DO: Fix this. This is a copypaste mess right now --->
		<form action="" method="post">
			<div>
				<label for="checkboxMeetingroom">Select the meeting room(s) the event should appear in: </label>
			</div>
			
			<div>
				<input type="checkbox" name="meetingroomAll" value="All" <?php htmlout($checkAll); ?>>All<br />
				<?php foreach($checkboxes AS $checkbox) : ?>
					<?php //checkbox[0] is the meeting room ID ?>
					<?php //checkbox[1] is the meeting room name ?>
					<?php //checkbox[2] is if it should have a linefeed ?>
					<?php //checkbox[3] is if it should be checked ?>
					<?php if($checkbox[3]) : ?>
						<input type="checkbox" name="meetingroom[]" 
						value="<?php htmlout($checkbox[0]); ?>" checked="checked"><?php htmlout($checkbox[1]); ?>
					<?php else : ?>
						<input type="checkbox" name="meetingroom[]" 
						value="<?php htmlout($checkbox[0]); ?>"><?php htmlout($checkbox[1]); ?>
					<?php endif; ?>
					<?php if($checkbox[2]): ?><br /><?php endif; ?>
				<?php endforeach; ?>
			<div>
			
			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<select name="meetingRoomID" id="meetingRoomID">
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomID']==$selectedMeetingRoomID):?>
							<option selected="selected" value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
						<?php else : ?>
							<option value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
						<?php endif;?>
					<?php endforeach; ?>
				</select>				
			</div>
			
			<div>
				<label for="startTime">Start Time: </label>
				<input type="text" name="startTime" id="startTime" 
				placeholder="hh:mm:ss"
				value="<?php htmlout($startTime); ?>">
			</div>
			
			<div>
				<label for="endTime">End Time: </label>
				<input type="text" name="endTime" id="endTime" 
				placeholder="hh:mm:ss"
				value="<?php htmlout($endTime); ?>">
			</div>

			<div>
				<label for="eventName">Event Name: </label>
				<input type="text" name="eventName" id="eventName" 
				value="<?php htmlout($eventName); ?>">
			</div>
			
			<div>
				<label class="description" for="eventDescription">Event Description: </label>
				<textarea rows="4" cols="50" name="eventDescription" id="eventDescription"><?php htmlout($eventDescription); ?></textarea>
			</div>
			
			<div>
				<fieldset><legend>Select the day(s) for the event</legend>
					<input type="checkbox" name="daysSelected[]" value="Monday">Monday<br />
					<input type="checkbox" name="daysSelected[]" value="Tuesday">Tuesday<br />
					<input type="checkbox" name="daysSelected[]" value="Wednesday">Wednesday<br />
					<input type="checkbox" name="daysSelected[]" value="Thursday">Thursday<br />
					<input type="checkbox" name="daysSelected[]" value="Friday">Friday<br />
					<input type="checkbox" name="daysSelected[]" value="Saturday">Saturday<br />
					<input type="checkbox" name="daysSelected[]" value="Sunday">Sunday
				</fieldset>
			</div>
			
			<div>
				<label for="">Select if event should repeat: </label>
				<input type="submit" name="add" value="Repeat">
			</div>
			
			<div>
				<input type="submit" name="add" value="Reset">
				<input type="submit" name="add" value="Cancel">
				<?php if(isset($_SESSION['AddBookingChangeUser']) AND $_SESSION['AddBookingChangeUser']) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<b>You need to select the user you want before you can add the booking.</b>
				<?php elseif(!isset($_SESSION['AddBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Add booking" disabled>
					<b>You need to select the company you want before you can add the booking.</b>
				<?php else : ?>
					<input type="submit" name="add" value="Add booking">
				<?php endif; ?>				
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>