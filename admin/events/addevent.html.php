<!-- This is the HTML form used for ADDING EVENT information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">		
		<title>Schedule A New Event</title>
		<style>
			label {
				width: 140px;
			}
			.week {
				width: 300px;
			}
		</style>
	</head>
	<body>
		<fieldset><legend><b>Schedule A New Event</b></legend>
		
			<div class="warning">
				<?php if(isset($_SESSION['AddEventError'])) : ?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddEventError']); ?></b></span>
					<?php unset($_SESSION['AddEventError']); ?>
				<?php endif; ?>
			</div>
			
			<form action="" method="post">
				<div>
					<fieldset>
						<?php if(!isset($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<legend>Select the room selection type you want to use</legend>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND !isset($_SESSION['AddEventRoomsSelected'])) : ?>
							<legend>Select the meeting room(s) for the event</legend>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND isset($_SESSION['AddEventRoomsSelected'])) : ?>
							<legend><b>Selected meeting room(s) for the event</b></legend>
						<?php endif; ?>	
						
						<?php if(!isset($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<input type="submit" name="add" value="Select A Single Room">
							<input type="submit" name="add" value="Select Multiple Rooms">
							<input type="submit" name="add" value="Select All Rooms">
						<?php else : ?>
							<input type="submit" name="add" value="Change Room Selection">
						<?php endif; ?>
						<?php if(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms") : ?>
							<div>
								<?php if(!isset($_SESSION['AddEventRoomsSelected'])) : ?>
									<?php for($i = 0; $i < sizeOf($meetingroom); $i++) : ?>
										<?php $meetingRoomSelected = FALSE; ?>
										<?php for($j = 0; $j < sizeOf($roomsSelected); $j++) : ?>
											<?php if($roomsSelected[$j] == $meetingroom[$i]['MeetingRoomID']) : ?>
												<label><input type="checkbox" name="roomsSelected[]" checked="checked" value="<?php htmlout($meetingroom[$i]['MeetingRoomID']); ?>"><?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></label>
												<?php if(($i % 4) == 3) : ?><br /><?php endif; ?>
												<?php $meetingRoomSelected = TRUE; break; ?>
											<?php endif; ?>
										<?php endfor; ?>
										<?php if(!$meetingRoomSelected) : ?>
											<label><input type="checkbox" name="roomsSelected[]" value="<?php htmlout($meetingroom[$i]['MeetingRoomID']); ?>"><?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></label>
											<?php if(($i % 4) == 3) : ?><br /><?php endif; ?>
										<?php endif; ?>
									<?php endfor; ?>
									<input type="submit" name="add" value="Confirm Room(s)">
								<?php else : ?>
									<?php for($i = 0; $i < sizeOf($meetingroom); $i++) : ?>
										<?php $meetingRoomSelected = FALSE; ?>
										<?php for($j = 0; $j < sizeOf($roomsSelected); $j++) : ?>
											<?php if($roomsSelected[$j] == $meetingroom[$i]['MeetingRoomID']) : ?>
												<span><b>☑ <?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></b></span>
												<?php if(($i % 4) == 3) : ?><br /><?php endif; ?>
												<?php $meetingRoomSelected = TRUE; break; ?>
											<?php endif; ?>
										<?php endfor; ?>
										<?php if(!$meetingRoomSelected) : ?>
											<span>☐ <?php htmlout($meetingroom[$i]['MeetingRoomName']); ?></span>
											<?php if(($i % 4) == 3) : ?><br /><?php endif; ?>
										<?php endif; ?>
									<?php endfor; ?>								
									<span><b>Event will be scheduled for <?php htmlout($numberOfRoomsSelected); ?> rooms.</b></span>
								<?php endif; ?>
							</div>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room") : ?>
							<?php if(isset($_SESSION['AddEventRoomsSelected'])) : ?>
								<div><b>Event will be scheduled for the room <?php htmlout($roomSelected); ?></b></div>
							<?php else : ?>
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
									<input type="submit" name="add" value="Confirm Room(s)">
								</div>							
							<?php endif; ?>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select All Rooms") : ?>
							<div><b>Event will be scheduled for all rooms (Total of <?php htmlout($numberOfRoomsSelected); ?> rooms).</b></div>
						<?php endif; ?>
					</fieldset>
				</div>
				
				<div>
					<fieldset><legend>Event Details</legend>
						<?php if(!isset($_SESSION['AddEventDetailsConfirmed'])) : ?>
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
							<input type="submit" name="add" value="Confirm Details">
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
							<input type="submit" name="add" value="Change Details">
							<input type="hidden" name="startTime" value="<?php htmlout($startTime); ?>">
							<input type="hidden" name="endTime" value="<?php htmlout($endTime); ?>">
							<input type="hidden" name="eventName" value="<?php htmlout($eventName); ?>">
							<input type="hidden" name="eventDescription" value="<?php htmlout($eventDescription); ?>">
						<?php endif; ?>
					</fieldset>
				</div>

				<div class="container">
					<div class="left">
						<fieldset>
							<?php if(!isset($_SESSION['AddEventDaysConfirmed'])) : ?>
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
								<div class="right">
									<input type="submit" name="add" value="Confirm Day(s)">
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
								<div class="right">
									<input type="submit" name="add" value="Change Day(s)">
								</div>
							<?php endif; ?>
						</fieldset>
					</div>
					<div class="right">
						<fieldset>
						<?php if(!isset($_SESSION['AddEventWeekChoiceSelected'])) : ?>
							<legend>Select the week selection type you want to use</legend>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND !isset($_SESSION['AddEventWeeksSelected'])) : ?>
							<legend>Select the week(s) for the event</legend>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND isset($_SESSION['AddEventWeeksSelected'])) : ?>
							<legend><b>Selected weeks(s) for the event</b></legend>
						<?php endif; ?>
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
										<?php if(!isset($_SESSION['AddEventWeeksSelected'])) : ?>	
											<?php $i = 0; ?>
											<?php foreach($weeksOfTheYear AS $week) : ?>
												<?php $weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
												<?php $weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>						
												<?php $weekSelected = FALSE; ?>
												<?php for($j = 0; $j < sizeOf($weeksSelected); $j++) : ?>
													<?php if($weeksSelected[$j] == $week['WeekNumber']) : ?>
														<label class="week"><input type="checkbox" name="weeksSelected[]" checked="checked" value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></label>
														<?php if($i % 4 == 3) : ?><br /><?php endif; ?>
														<?php $weekSelected = TRUE; break; ?>
													<?php endif; ?>
												<?php endfor; ?>
												<?php if(!$weekSelected) : ?>
													<label class="week"><input type="checkbox" name="weeksSelected[]" value="<?php htmlout($week['WeekNumber']); ?>"><?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></label>
													<?php if($i % 4 == 3) : ?><br /><?php endif; ?>
												<?php endif; ?>
												<?php $i++; ?>
											<?php endforeach; ?>
										<?php else : ?>
											<?php $i = 0; ?>
											<?php foreach($weeksOfTheYear AS $week) : ?>
												<?php $weekStart = convertDatetimeToFormat($week['StartDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>
												<?php $weekEnd = convertDatetimeToFormat($week['EndDate'], 'Y-m-d', DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR); ?>											
												<?php $weekSelected = FALSE; ?>
												<?php for($j = 0; $j < sizeOf($weeksSelected); $j++) : ?>
													<?php if($weeksSelected[$j] == $week['WeekNumber']) : ?>
														<b>☑ <?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?></b>
														<?php if($i % 4 == 3) : ?><br /><?php endif; ?>
														<?php $weekSelected = TRUE; break; ?>
													<?php endif; ?>
												<?php endfor; ?>
												<?php if(!$weekSelected) : ?>
													☐ <?php htmlout($week['WeekNumber'] . ": " . $weekStart . "-" . $weekEnd); ?>
													<?php if($i % 4 == 3) : ?><br /><?php endif; ?>
												<?php endif; ?>
												<?php $i++; ?>
											<?php endforeach; ?>
										<?php endif; ?>									
									<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week") : ?>
										<?php if(isset($_SESSION['AddEventWeeksSelected'])) : ?>
											<div><b>Event will be scheduled for the week <?php htmlout($weekSelected); ?></b></div>
										<?php else : ?>
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
									<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select All Weeks") : ?>
										<div><b>Event will be scheduled for all the remaining weeks this year (Total of <?php htmlout($numberOfWeeksSelected); ?> weeks).</b></div>
									<?php endif; ?>
								</div>
							</div>
							<div class="container">	
								<div class="right">
									<?php if(isset($_SESSION['AddEventWeekChoiceSelected']) AND !isset($_SESSION['AddEventWeeksSelected'])) : ?>
										<input type="submit" name="add" value="Confirm Week(s)">
									<?php endif; ?>
								</div>
							</div>
						</fieldset>
					</div>					
				</div>
				
				<div class="container">
					<div class="left">
						<?php if(!isset($_SESSION['AddEventDaysConfirmed'])) : ?>
							<span><b>You need to select the day(s) you want before you can create the event.</b></span>
						<?php elseif(sizeOf($daysSelected) == 0) : ?>
							<span><b>You need to select at least one day you want before you can create the event.</b></span>
						<?php elseif(!isset($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<span><b>You need to pick the meeting room selection type before you can create the event.</b></span>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room" AND !isset($_SESSION['AddEventRoomsSelected'])) : ?>
							<span><b>You need to select the meeting room before you can create the event.</b></span>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms" AND (!isset($_SESSION['AddEventRoomsSelected']) OR (isset($_SESSION['AddEventRoomsSelected']) AND sizeOf($_SESSION['AddEventRoomsSelected']) == 0))) : ?>
							<span><b>You need to select at least one meeting room before you can create the event.</b></span>
						<?php elseif(!isset($_SESSION['AddEventWeekChoiceSelected'])) : ?>
							<span><b>You need to pick the week selection type before you can create the event.</b></span>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week" AND !isset($_SESSION['AddEventWeeksSelected'])) : ?>
							<span><b>You need to select the week before you can create the event.</b></span>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks" AND (!isset($_SESSION['AddEventWeeksSelected']) OR (isset($_SESSION['AddEventWeeksSelected']) AND sizeOf($_SESSION['AddEventWeeksSelected']) == 0))) : ?>
							<span><b>You need to select at least one week before you can create the event.</b></span>
						<?php elseif(!isset($_SESSION['AddEventDetailsConfirmed'])) : ?>
							<span><b>You need to type in the event details before you can create the event.</b></span>
						<?php endif; ?>
					</div>
				</div>
				
				<div class="container">
					<div class="left">
						<?php if(!isset($_SESSION['AddEventDaysConfirmed'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(sizeOf($daysSelected) == 0) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isset($_SESSION['AddEventRoomChoiceSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select A Single Room" AND !isset($_SESSION['AddEventRoomsSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isset($_SESSION['AddEventRoomChoiceSelected']) AND $_SESSION['AddEventRoomChoiceSelected'] == "Select Multiple Rooms" AND (!isset($_SESSION['AddEventRoomsSelected']) OR (isset($_SESSION['AddEventRoomsSelected']) AND sizeOf($_SESSION['AddEventRoomsSelected']) == 0))) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isset($_SESSION['AddEventWeekChoiceSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select A Single Week" AND !isset($_SESSION['AddEventWeeksSelected'])) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(isset($_SESSION['AddEventWeekChoiceSelected']) AND $_SESSION['AddEventWeekChoiceSelected'] == "Select Multiple Weeks" AND (!isset($_SESSION['AddEventWeeksSelected']) OR (isset($_SESSION['AddEventWeeksSelected']) AND sizeOf($_SESSION['AddEventWeeksSelected']) == 0))) : ?>
							<input type="submit" name="disabled" value="Create Event" disabled>
						<?php elseif(!isset($_SESSION['AddEventDetailsConfirmed'])) : ?>
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
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>