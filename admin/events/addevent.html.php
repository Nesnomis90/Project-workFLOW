<!-- This is the HTML form used for ADDING EVENT information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title>Schedule A New Event</title>
		<style>
			label {
				width: 140px;
			}
			.week {
				width: 270px;
			}
			.room {
				width: 200px;
			}
			.checkboxlabel{
				float: none;
				clear: none;
			}			
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend><b>Schedule A New Event</b></legend>

			<?php if(isSet($_SESSION['AddEventError'])) : ?>
				<div class="left">
					<span><b class="feedback"><?php htmlout($_SESSION['AddEventError']); ?></b></span>
					<?php unset($_SESSION['AddEventError']); ?>
				</div>
			<?php endif; ?>
			
			<form method="post">
				<div>
					<fieldset>
						<?php if(!isSet($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<legend>Select the room selection type you want to use</legend>
						<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND !isSet($_SESSION['AddEventRoomsSelected'])) : ?>
							<legend>Select the meeting room(s) for the event</legend>
						<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND isSet($_SESSION['AddEventRoomsSelected'])) : ?>
							<legend><b>Selected meeting room(s) for the event</b></legend>
						<?php endif; ?>	
						
						<?php if(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms") : ?>
							<div class="left">
								<?php if(!isSet($_SESSION['AddEventRoomsSelected'])) : ?>
									<div class="left">
										<?php for($i = 0; $i < sizeOf($meetingroom); $i++) : ?>
											<?php $meetingRoomSelected = FALSE; ?>
											<?php for($j = 0; $j < sizeOf($roomsSelected); $j++) : ?>
												<?php if($roomsSelected[$j] == $meetingroom[$i]['MeetingRoomID']) : ?>
													<label class="checkboxlabel room"><input type="checkbox" name="roomsSelected[]" checked="checked" value="<?php htmlout($meetingroom[$i]['MeetingRoomID']); ?>"><?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></label>
													<?php $meetingRoomSelected = TRUE; break; ?>
												<?php endif; ?>
											<?php endfor; ?>
											<?php if(!$meetingRoomSelected) : ?>
												<label class="checkboxlabel room"><input type="checkbox" name="roomsSelected[]" value="<?php htmlout($meetingroom[$i]['MeetingRoomID']); ?>"><?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></label>
											<?php endif; ?>
										<?php endfor; ?>
									</div>
								<?php else : ?>
									<div class="left">
										<?php for($i = 0; $i < sizeOf($meetingroom); $i++) : ?>
											<?php $meetingRoomSelected = FALSE; ?>
											<?php for($j = 0; $j < sizeOf($roomsSelected); $j++) : ?>
												<?php if($roomsSelected[$j] == $meetingroom[$i]['MeetingRoomID']) : ?>
													<label class="checkboxlabel room"><b>☑ <?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></b></label>
													<?php $meetingRoomSelected = TRUE; break; ?>
												<?php endif; ?>
											<?php endfor; ?>
											<?php if(!$meetingRoomSelected) : ?>
												<label class="checkboxlabel room">☐ <?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></label>
											<?php endif; ?>
										<?php endfor; ?>
									</div>
								<?php endif; ?>
							</div>
						<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room") : ?>
							<?php if(!isSet($_SESSION['AddEventRoomsSelected'])) : ?>
								<div>
									<label for="meetingRoomID">Meeting Room: </label>
									<select name="meetingRoomID" id="meetingRoomID">
										<?php foreach($meetingroom as $row): ?> 
											<?php if($row['MeetingRoomID'] == $selectedMeetingRoomID):?>
												<option selected="selected" value="<?php htmlout($row['MeetingRoomID']); ?>"><?php htmlout($row['MeetingRoomName']);?></option>
											<?php else : ?>
												<option value="<?php htmlout($row['MeetingRoomID']); ?>"><?php htmlout($row['MeetingRoomName']);?></option>
											<?php endif;?>
										<?php endforeach; ?>
									</select>
								</div>							
							<?php endif; ?>
						<?php endif; ?>

						<div class="left">
							<?php if(!isSet($_SESSION['AddEventRoomChoiceSelected'])) : ?>
								<input type="submit" name="add" value="Select A Single Room">
								<input type="submit" name="add" value="Select Multiple Rooms">
								<input type="submit" name="add" value="Select All Rooms">
							<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND !isSet($_SESSION['AddEventRoomsSelected'])) : ?>
								<input type="submit" name="add" value="Confirm Room(s)">
							<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND isSet($_SESSION['AddEventRoomsSelected'])) : ?>
								<span><b><?php htmlout($roomsSelectedFeedback); ?></b></span>
							<?php endif; ?>
						</div>	

						<div class="right">
							<?php if(isSet($_SESSION['AddEventRoomChoiceSelected'])) : ?>
								<input type="submit" name="add" value="Change Room Selection">
							<?php endif; ?>
						</div>

					</fieldset>
				</div>
				
				<div>
					<fieldset><legend>Event Details</legend>
						<?php if(!isSet($_SESSION['AddEventDetailsConfirmed'])) : ?>
							<div>
								<label for="startTime">Start Time: </label>
								<input type="text" name="startTime" id="startTime" 
								placeholder="hh:mm (e.g. 13:30)"
								value="<?php htmlout($startTime); ?>">
							</div>
							
							<div>
								<label for="endTime">End Time: </label>
								<input type="text" name="endTime" id="endTime" 
								placeholder="hh:mm (e.g. 14:30)"
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
							<div class="left">
								<input type="submit" name="add" value="Confirm Details">
							</div>
						<?php else : ?>
							<div>
								<label for="startTime">Start Time: </label>
								<input type="text" name="disabled" placeholder="hh:mm:ss" disabled
								value="<?php htmlout($startTime); ?>">
							</div>
							
							<div>
								<label for="endTime">End Time: </label>
								<input type="text" name="disabled" placeholder="hh:mm:ss" disabled
								value="<?php htmlout($endTime); ?>">
							</div>

							<div>
								<label for="eventName">Event Name: </label>
								<input type="text" name="disabled" disabled
								value="<?php htmlout($eventName); ?>">
							</div>
							
							<div>
								<label class="description" for="eventDescription">Event Description: </label>
								<textarea rows="4" cols="50" name="disabled" disabled><?php htmlout($eventDescription); ?></textarea>
							</div>
							<div class="right">
								<input type="submit" name="add" value="Change Details">
							</div>
							<input type="hidden" name="startTime" value="<?php htmlout($startTime); ?>">
							<input type="hidden" name="endTime" value="<?php htmlout($endTime); ?>">
							<input type="hidden" name="eventName" value="<?php htmlout($eventName); ?>">
							<input type="hidden" name="eventDescription" value="<?php htmlout($eventDescription); ?>">
						<?php endif; ?>
					</fieldset>
				</div>


				<div class="left">
					<fieldset>
						<?php if(!isSet($_SESSION['AddEventDaysConfirmed'])) : ?>
							<legend>Select the day(s) for the event</legend>
							<div class="left">
							<?php for($i = 0; $i < sizeOf($daysOfTheWeek); $i++) : ?>
								<?php $daySelected = FALSE; ?>
								<?php for($j = 0; $j < sizeOf($daysSelected); $j++) : ?>
									<?php if($daysSelected[$j] == $daysOfTheWeek[$i]) : ?>
										<label><input type="checkbox" name="daysSelected[]" checked="checked" value="<?php htmlout($daysOfTheWeek[$i]); ?>"><?php htmlout($daysOfTheWeek[$i]); ?></label>
										<?php if($daysOfTheWeek[$i] != "Sunday") : ?><br /><?php endif; ?>
										<?php $daySelected = TRUE; break; ?>
									<?php endif; ?>
								<?php endfor; ?>
								<?php if(!$daySelected) : ?>
									<label><input type="checkbox" name="daysSelected[]" value="<?php htmlout($daysOfTheWeek[$i]); ?>"><?php htmlout($daysOfTheWeek[$i]); ?></label>
									<?php if($daysOfTheWeek[$i] != "Sunday") : ?><br /><?php endif; ?>
								<?php endif; ?>
							<?php endfor; ?>
							</div>
							<div>
								<div class="bottomright">
									<input type="submit" name="add" value="Confirm Day(s)">
								</div>
							</div>
						<?php else : ?>
							<legend><b>Day(s) selected for the event</b></legend>
							<div class="left">
							<?php for($i = 0; $i < sizeOf($daysOfTheWeek); $i++) : ?>
								<?php $daySelected = FALSE; ?>
								<?php for($j = 0; $j < sizeOf($daysSelected); $j++) : ?>
									<?php if($daysSelected[$j] == $daysOfTheWeek[$i]) : ?>
										<b>☑ <?php htmlout($daysOfTheWeek[$i]); ?></b>
										<?php if($daysOfTheWeek[$i] != "Sunday") : ?><br /><?php endif; ?>
										<?php $daySelected = TRUE; break; ?>
									<?php endif; ?>
								<?php endfor; ?>
								<?php if(!$daySelected) : ?>
									☐ <?php htmlout($daysOfTheWeek[$i]); ?>
									<?php if($daysOfTheWeek[$i] != "Sunday") : ?><br /><?php endif; ?>
								<?php endif; ?>
							<?php endfor; ?>
							</div>
							<div class="bottomright">
								<input type="submit" name="add" value="Change Day(s)">
							</div>
						<?php endif; ?>
					</fieldset>
				</div>
				
				<div class="left">
					<fieldset>
						<?php if(!isSet($_SESSION['AddEventWeekChoiceSelected'])) : ?>
							<legend>Select the week selection type you want to use</legend>
						<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND !isSet($_SESSION['AddEventWeeksSelected'])) : ?>
							<legend>Select the week(s) for the event</legend>
						<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND isSet($_SESSION['AddEventWeeksSelected'])) : ?>
							<legend><b>Selected weeks(s) for the event</b></legend>
						<?php endif; ?>
						
						<div class="left">	
							<?php if(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks") : ?>
								<?php if(!isSet($_SESSION['AddEventWeeksSelected'])) : ?>	
									<div class="left">
										<?php $i = 0; ?>
										<?php foreach($weeksOfTheYear AS $week) : ?>
											<?php $weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
											<?php $weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>						
											<?php $weekSelected = FALSE; ?>
											<?php for($j = 0; $j < sizeOf($weeksSelected); $j++) : ?>
												<?php if($weeksSelected[$j] == $week['WeekNumber']) : ?>
													<label class="checkboxlabel week"><input type="checkbox" name="weeksSelected[]" checked="checked" value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></label>
													<?php $weekSelected = TRUE; break; ?>
												<?php endif; ?>
											<?php endfor; ?>
											<?php if(!$weekSelected) : ?>
												<label class="checkboxlabel week"><input type="checkbox" name="weeksSelected[]" value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></label>
											<?php endif; ?>
											<?php $i++; ?>
										<?php endforeach; ?>
									</div>
								<?php else : ?>
									<div class="left">
										<?php $i = 0; ?>
										<?php foreach($weeksOfTheYear AS $week) : ?>
											<?php $weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
											<?php $weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>											
											<?php $weekSelected = FALSE; ?>
											<?php for($j = 0; $j < sizeOf($weeksSelected); $j++) : ?>
												<?php if($weeksSelected[$j] == $week['WeekNumber']) : ?>
													<label class="checkboxlabel week"><b>☑ <?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></b></label>
													<?php $weekSelected = TRUE; break; ?>
												<?php endif; ?>
											<?php endfor; ?>
											<?php if(!$weekSelected) : ?>
												<label class="checkboxlabel week">☐ <?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></label>
											<?php endif; ?>
											<?php $i++; ?>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>									
							<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week") : ?>
								<?php if(!isSet($_SESSION['AddEventWeeksSelected'])) : ?>
									<div>
										<label for="weekNumber">Select the one week: </label>
										<select name="weekNumber" id="weekNumber">
											<?php foreach($weeksOfTheYear as $week): ?>
												<?php $weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
												<?php $weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
												<?php if($week['WeekNumber'] == $selectedWeekNumber):?>
													<option selected="selected" value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></option>
												<?php else : ?>
													<option value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></option>
												<?php endif;?>
											<?php endforeach; ?>						
										</select>
									</div>
								<?php endif; ?>
							<?php endif; ?>

							<div class="left">
								<?php if(!isSet($_SESSION['AddEventWeekChoiceSelected'])) : ?>
									<input type="submit" name="add" value="Select A Single Week">
									<input type="submit" name="add" value="Select Multiple Weeks">
									<input type="submit" name="add" value="Select All Weeks">
								<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND !isSet($_SESSION['AddEventWeeksSelected'])) : ?>
									<input type="submit" name="add" value="Confirm Week(s)">
								<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND isSet($_SESSION['AddEventWeeksSelected'])) : ?>
									<span><b><?php htmlout($weeksSelectedFeedback); ?></b></span>
								<?php endif; ?>
							</div>
							
							<div class="right">
								<?php if(isSet($_SESSION['AddEventWeekChoiceSelected'])) : ?>
									<input type="submit" name="add" value="Change Week Selection">
								<?php endif; ?>
							</div>
						</div>
					</fieldset>
				</div>
				
				<div class="left">
					<?php if(!isSet($_SESSION['AddEventDaysConfirmed'])) : ?>
						<span><b>You need to select the day(s) you want before you can create the event.</b></span>
					<?php elseif(sizeOf($daysSelected) == 0) : ?>
						<span><b>You need to select at least one day you want before you can create the event.</b></span>
					<?php elseif(!isSet($_SESSION['AddEventRoomChoiceSelected'])) : ?>
						<span><b>You need to pick the meeting room selection type before you can create the event.</b></span>
					<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room" AND !isSet($_SESSION['AddEventRoomsSelected'])) : ?>
						<span><b>You need to select the meeting room before you can create the event.</b></span>
					<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms" AND (!isSet($_SESSION['AddEventRoomsSelected']) OR (isSet($_SESSION['AddEventRoomsSelected']) AND sizeOf($_SESSION['AddEventRoomsSelected']) == 0))) : ?>
						<span><b>You need to select at least one meeting room before you can create the event.</b></span>
					<?php elseif(!isSet($_SESSION['AddEventWeekChoiceSelected'])) : ?>
						<span><b>You need to pick the week selection type before you can create the event.</b></span>
					<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week" AND !isSet($_SESSION['AddEventWeeksSelected'])) : ?>
						<span><b>You need to select the week before you can create the event.</b></span>
					<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks" AND (!isSet($_SESSION['AddEventWeeksSelected']) OR (isSet($_SESSION['AddEventWeeksSelected']) AND sizeOf($_SESSION['AddEventWeeksSelected']) == 0))) : ?>
						<span><b>You need to select at least one week before you can create the event.</b></span>
					<?php elseif(!isSet($_SESSION['AddEventDetailsConfirmed'])) : ?>
						<span><b>You need to type in the event details before you can create the event.</b></span>
					<?php endif; ?>
				</div>
				
				<div class="container">
					<div class="left">
						<?php if(!isSet($_SESSION['AddEventDaysConfirmed'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(sizeOf($daysSelected) == 0) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isSet($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room" AND !isSet($_SESSION['AddEventRoomsSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isSet($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms" AND (!isSet($_SESSION['AddEventRoomsSelected']) OR (isSet($_SESSION['AddEventRoomsSelected']) AND sizeOf($_SESSION['AddEventRoomsSelected']) == 0))) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isSet($_SESSION['AddEventWeekChoiceSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week" AND !isSet($_SESSION['AddEventWeeksSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isSet($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks" AND (!isSet($_SESSION['AddEventWeeksSelected']) OR (isSet($_SESSION['AddEventWeeksSelected']) AND sizeOf($_SESSION['AddEventWeeksSelected']) == 0))) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isSet($_SESSION['AddEventDetailsConfirmed'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php else : ?>
							<input type="submit" name="add" value="Create Event">
						<?php endif; ?>
					</div>
					<div class="right">
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">
					</div>
				</div>
			</form>
		</fieldset>
	<p><a href="..">Return to CMS home</a></p>
	</body>
</html>