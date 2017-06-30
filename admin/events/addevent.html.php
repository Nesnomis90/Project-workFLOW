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
		<fieldset><legend><b>Schedule A New Event</b></legend>
			<div class="warning">
			<?php if(isset($_SESSION['AddEventError'])) : ?>
				<b><?php htmlout($_SESSION['AddEventError']); ?></b>
				<?php unset($_SESSION['AddEventError']); ?>
			<?php endif; ?>
			</div>
			
			<form action="" method="post">
				<div>
					<fieldset><legend>Select the meeting room(s) for the event</legend>
						<?php if(!isset($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<input type="submit" name="add" value="Select A Single Room">
							<input type="submit" name="add" value="Select Multiple Rooms">
							<input type="submit" name="add" value="Select All Rooms">
						<?php else : ?>
							<input type="submit" name="add" value="Change Room Selection">
						<?php endif; ?>
						<?php if(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms") : ?>
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
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room") : ?>
							<?php if($_SESSION['AddEventRoomsSelected']) : ?>
								<div>Event will be scheduled for the room <?php htmlout($roomSelected); ?></div>
							<?php else : ?>
								<div>
									<label for="meetingRoomID">Meeting Room: </label>
									<select name="meetingRoomID" id="meetingRoomID">
										<?php foreach($meetingroom as $row): ?> 
											<?php if($row['meetingRoomID'] == $selectedMeetingRoomID):?>
												<option selected="selected" value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
											<?php else : ?>
												<option value="<?php htmlout($row['meetingRoomID']); ?>"><?php htmlout($row['meetingRoomName']);?></option>
											<?php endif;?>
										<?php endforeach; ?>
									</select>				
								</div>							
							<?php endif; ?>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select All Rooms") : ?>
							<div>Event will be scheduled for all rooms.</div>
						<?php endif; ?>
					</fieldset>
				</div>
				
				<div>
					<fieldset><legend>Event Details</legend>
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
					</fieldset>
				</div>
	<div class="container">
		<div class="left"></div>
		<div class="innerWrap">
			<div class="right"></div>
			<div class="middle"></div>
		</div>
	</div>
				<div class="container">
					<div class="left">
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
					<div class="right">
						<fieldset><legend>Select the week(s) it should be active</legend>
							<div class="container">
								<div class="left">
									<?php if(!isset($_SESSION['AddEventWeekChoiceSelected'])) : ?>
										<input type="submit" name="add" value="Select A Single Week">
										<input type="submit" name="add" value="Select Multiple Weeks">
										<input type="submit" name="add" value="Select All Weeks">
									<?php else : ?>
										<input type="submit" name="add" value="Change Week Selection">
									<?php endif; ?>
									
									<?php if(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks") : ?>
									
									<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Weeks") : ?>
										<div>
											<label for="weekNumber">Select the one week: </label>
											<select name="weekNumber" id="weekNumber">
												<?php foreach($weekNumber as $row): ?> 
													<?php if($row['weekNumber'] == $selectedWeekNumber):?>
														<option selected="selected" value="<?php htmlout($row['weekNumber']); ?>"><?php htmlout($row['weekDate']);?></option>
													<?php else : ?>
														<option value="<?php htmlout($row['weekNumber']); ?>"><?php htmlout($row['weekDate']);?></option>
													<?php endif;?>
												<?php endforeach; ?>						
											</select>
										</div>						
									<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select All Weeks") : ?>
									
									<?php endif; ?>
								</div>
							</div>
							<div class="container">	
								<div class="right">
									<?php if(!isset($_SESSION['AddEventWeeksSelected'])) : ?>
										<input type="submit" name="add" value="Confirm Weeks">
									<?php else : ?>
										<input type="submit" name="add" value="Change Weeks">
									<?php endif; ?>
								</div>
							</div>
						</fieldset>
					</div>					
				</div>
				
				<div class="container">
					<div class="left">
						<?php if(!isset($_SESSION['AddEventDaysSelected'])) : ?>
							<b>You need to select the day(s) you want before you can create the event.</b>
						<?php elseif(isset($_SESSION['AddEventDaysSelected']) AND $_SESSION['AddEventDaysSelected'] == 0) : ?>
							<b>You need to select at least one day you want before you can create the event.</b>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="container">
					<?php if(!isset($_SESSION['AddEventDaysSelected'])) : ?>
						<div class="left"><input type="submit" name="disabled" value="Create Event" disabled></div>
					<?php elseif(isset($_SESSION['AddEventDaysSelected']) AND $_SESSION['AddEventDaysSelected'] == 0) : ?>
						<div class="left"><input type="submit" name="disabled" value="Create Event" disabled></div>
					<?php else : ?>
						<div class="left"><input type="submit" name="add" value="Create Event"></div>
					<?php endif; ?>
					<div class="right">
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">
					</div>
				</div>
			</form>
		</fieldset>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>