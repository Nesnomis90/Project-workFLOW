<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Meeting Room</title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 85px;
			}
			table.innerTable th {
				height: 50px; 
				max-width: 100px;
				word-wrap: break-word;
			}
			table.innerTable tr {
				height: 40px;
			}
			table.innerTable td {
				min-width: 50px;
				max-width: 50px;
				word-wrap: break-word;
				height: inherit;
			}
			table.innerTable td.occupied {
				background-color: #ff3333;
				text-align: center;
			}
			table.innerTable td.available {
				background-color: #33ff33;
			}
			div.overflow {
				overflow: hidden;
				height: inherit;
			}
		</style>
		<script>
			var dateSelected = '<?php htmlout($dateSelected); ?>';

			function createBooking(meetingRoomID, timeSelected){
				var inputPlacement = document.getElementById("bookingForm");
				$("#bookingForm").empty();

				var inputMeetingRoomID = document.createElement("input");
				inputMeetingRoomID.setAttribute("type", "hidden");
				inputMeetingRoomID.setAttribute("name", "MeetingRoomID");
				inputMeetingRoomID.setAttribute("id", "MeetingRoomID");
				inputMeetingRoomID.setAttribute("value", meetingRoomID);
				inputPlacement.appendChild(inputMeetingRoomID);

				var dateTime = dateSelected + " " + timeSelected + ":00";

				var inputDateTimeStart = document.createElement("input");
				inputDateTimeStart.setAttribute("type", "hidden");
				inputDateTimeStart.setAttribute("name", "DateTimeStart");
				inputDateTimeStart.setAttribute("id", "DateTimeStart");
				inputDateTimeStart.setAttribute("value", dateTime);
				inputPlacement.appendChild(inputDateTimeStart);

				document.getElementById("bookingForm").submit();
			}

			function onClickBookedMeeting(cellClicked){
				if(cellClicked.id == "clicked"){
					// Remove expand on second click
					cellClicked.removeAttribute("id");
					cellClicked.childNodes[3].removeAttribute("style");						
				} else {
					// Expand table cell on click
					cellClicked.childNodes[3].style.maxWidth = "10em";
					cellClicked.childNodes[3].style.width = "10em";
					cellClicked.childNodes[3].style.height = "10em";
					cellClicked.childNodes[3].style.display = "block";
					cellClicked.id = "clicked";
				}

					// Also add edit/cancel buttons
					// Also add change room button if local
				// Add shrink animation if already big
					// Also remove edit/cancel/change room buttons
			}

			function alterBooking(bookingID){
				var inputPlacement = document.getElementById("bookingForm");
				$("#bookingForm").empty();

				var inputBookingID = document.createElement("input");
				inputBookingID.setAttribute("type", "hidden");
				inputBookingID.setAttribute("name", "BookingID");
				inputBookingID.setAttribute("id", "BookingID");
				inputBookingID.setAttribute("value", bookingID);
				inputPlacement.appendChild(inputBookingID);

				//document.getElementById("bookingForm").submit();
			}
		</script>
	</head>
	<body onload="startTime(); refreshPageTimer(<?php htmlout(SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE); ?>);">

		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<div class="left">
			<span><b>Last Refresh: <?php htmlout(getTimeNowInDisplayFormat()); ?></b></span>
			<form method="post">
				<?php if(isSet($_SESSION['DefaultMeetingRoomInfo'])) : ?>
				<?php $default = $_SESSION['DefaultMeetingRoomInfo']; ?>
					<?php if((!isSet($_GET['meetingroom'])) OR
							(isSet($_GET['meetingroom']) AND $_GET['meetingroom'] != $default['TheMeetingRoomID'])) : ?>
						<input type="submit" name="action" value="Show Default Room Only">
					<?php else : ?>
						<input type="submit" name="action" value="Show All Rooms">
					<?php endif; ?>
				<?php endif; ?>
			</form>
		</div>

		<?php if(isSet($_SESSION['DefaultMeetingRoomInfo']) AND !isSet($defaultMeetingRoomFeedback)) : ?>
			<div class="left">
				<form method="post">
					<label style="width: 295px;" for="defaultMeetingRoomName">The Default Meeting Room For This Device: </label>
					<span><b><?php htmlout($_SESSION['DefaultMeetingRoomInfo']['TheMeetingRoomName']); ?></b></span>
					<?php if($adminLoggedIn) : ?>
						<div class="left">
							<input type="submit" name="action" value="Change Default Room">
						</div>
					<?php endif; ?>
				</form>
			</div>
		<?php elseif($adminLoggedIn) : ?>
			<div class="left">
				<form method="post">
					<input type="submit" name="action" value="Set Default Room">
				</form>
			<div>
		<?php endif; ?>

		<?php if(isSet($_SESSION['MeetingRoomAllUsersFeedback'])) : ?>
			<div class="left"><b class="feedback"><?php htmlout($_SESSION['MeetingRoomAllUsersFeedback']); ?></b></div>
			<?php unset($_SESSION['MeetingRoomAllUsersFeedback']); ?>
		<?php endif; ?>

		<?php if(isSet($defaultMeetingRoomFeedback)) : ?>
			<div class="left"><b class="feedback"><?php htmlout($defaultMeetingRoomFeedback); ?></b></div>
		<?php endif; ?>

		<?php if(!empty($_GET['meetingroom'])) : ?>
			<?php if(!empty($meetingrooms)) : ?>
				<table><tr>
					<?php foreach($meetingrooms AS $meetingRoomID => $bookings): ?>
						<td>
							<table class="innerTable">
								<?php if($displayingToday) : ?>
									<?php $currentStartTimeInMinutes = $timeNowInMinutes; ?> 
								<?php else : ?>
									<?php $currentStartTimeInMinutes = 0; ?>
								<?php endif; ?>
								<?php if(!empty($bookings)) : ?>
									<tr>
										<th colspan="2"><?php htmlout($bookings[0]['MeetingRoomName']); ?></th>
									</tr>
									<?php foreach($bookings AS $bookingInfo) : ?>
										<?php if(!empty($bookingInfo['StartTimeInMinutesSinceMidnight']) AND !empty($bookingInfo['EndTimeInMinutesSinceMidnight'])) : ?>
											<?php $nextStartTimeInMinutes = $bookingInfo['StartTimeInMinutesSinceMidnight']; ?>
											<?php $nextEndTimeInMinutes = $bookingInfo['EndTimeInMinutesSinceMidnight']; ?>
											<?php if($currentStartTimeInMinutes == $nextStartTimeInMinutes AND $timeNowInMinutes <= $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php elseif($currentStartTimeInMinutes >= $nextStartTimeInMinutes AND $timeNowInMinutes > $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($displayTimeNow); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php else : ?>
												<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < $nextStartTimeInMinutes;) : ?>
													<?php if($currentStartTimeInMinutes+$bookingMinuteChunks > $nextStartTimeInMinutes) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $nextStartTimeInMinutes, $bookingMinuteChunks); ?>
													<?php else : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
													<?php endif; ?>
													<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
														<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
														<td class="available"></td>
													</tr>
													<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
												<?php endfor; ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php endif; ?>
											<?php $currentStartTimeInMinutes = $nextEndTimeInMinutes; ?>
										<?php endif; ?>
									<?php endforeach; ?>

									<?php if($currentStartTimeInMinutes < 1440) : ?>
										<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
										<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
											<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
												<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
												<td class="available"></td>
											</tr>
											<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
											<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
										<?php endfor; ?>
									<?php endif; ?>
								<?php else : ?>
									<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
									<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
										<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
											<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
											<td class="available"></td>
										</tr>
										<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
										<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
									<?php endfor; ?>
								<?php endif; ?>
							</table>
						</td>
					<?php endforeach; ?>
					<form id="bookingForm" action="/booking/" method="post"></form>
				</tr></table>
			<?php else : ?>
				<div class="left"><h2>This isn't a valid meeting room.</h2></div>
			<?php endif; ?>
		<?php else : ?>
			<?php if(!empty($meetingrooms)) :?>
				<table><tr>
					<?php foreach($meetingrooms AS $meetingRoomID => $bookings): ?>
						<td>
							<table class="innerTable">
								<?php if($displayingToday) : ?>
									<?php $currentStartTimeInMinutes = $timeNowInMinutes; ?> 
								<?php else : ?>
									<?php $currentStartTimeInMinutes = 0; ?>
								<?php endif; ?>
								<?php if(!empty($bookings)) : ?>
									<tr>
										<th colspan="2"><?php htmlout($bookings[0]['MeetingRoomName']); ?></th>
									</tr>
									<?php foreach($bookings AS $bookingInfo) : ?>
										<?php if(!empty($bookingInfo['StartTimeInMinutesSinceMidnight']) AND !empty($bookingInfo['EndTimeInMinutesSinceMidnight'])) : ?>
											<?php $nextStartTimeInMinutes = $bookingInfo['StartTimeInMinutesSinceMidnight']; ?>
											<?php $nextEndTimeInMinutes = $bookingInfo['EndTimeInMinutesSinceMidnight']; ?>
											<?php if($currentStartTimeInMinutes == $nextStartTimeInMinutes AND $timeNowInMinutes <= $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php elseif($currentStartTimeInMinutes >= $nextStartTimeInMinutes AND $timeNowInMinutes > $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($displayTimeNow); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php else : ?>
												<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < $nextStartTimeInMinutes;) : ?>
													<?php if($currentStartTimeInMinutes+$bookingMinuteChunks > $nextStartTimeInMinutes) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $nextStartTimeInMinutes, $bookingMinuteChunks); ?>
													<?php else : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
													<?php endif; ?>
													<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
														<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
														<td class="available"></td>
													</tr>
													<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
												<?php endfor; ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr onclick="alterBooking(<?php htmlout($bookingInfo['BookingID']); ?>); onClickBookedMeeting(this);">
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td class="occupied"><div class="overflow"><?php htmlout($bookingInfo['BookingDisplayName']); ?></div></td>
													</tr>
												<?php endif; ?>
											<?php endif; ?>
											<?php $currentStartTimeInMinutes = $nextEndTimeInMinutes; ?>
										<?php endif; ?>
									<?php endforeach; ?>

									<?php if($currentStartTimeInMinutes < 1440) : ?>
										<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
										<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
											<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
												<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
												<td class="available"></td>
											</tr>
											<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
											<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
										<?php endfor; ?>
									<?php endif; ?>
								<?php else : ?>
									<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
									<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
										<tr onclick="createBooking('<?php htmlout($meetingRoomID); ?>','<?php echo convertMinutesToTime($currentStartTimeInMinutes); ?>')">
											<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
											<td class="available"></td>
										</tr>
										<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
										<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
									<?php endfor; ?>
								<?php endif; ?>
							</table>
						</td>
					<?php endforeach; ?>
					<form id="bookingForm" action="/booking/" method="post"></form>
				</tr></table>
			<?php else : ?>
				<div class="left"><h2>There are no meeting rooms.</h2></div>
			<?php endif; ?>
		<?php endif; ?>
	</body>
</html>