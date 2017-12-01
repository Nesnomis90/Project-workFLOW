<!--This is the HTML form for DISPLAYING MEETING ROOMS for all users-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Meeting Room</title>
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<style>
			label {
				width: 85px;
			}
			table td {
				min-width: 50px;
			}
		</style>
	</head>
	<body onload="startTime(); refreshPageTimer(<?php htmlout(SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE); ?>);">

		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/topnav.html.php'; ?>

		<div class="left">
			<span><b>Last Refresh: <?php htmlout(getDatetimeNowInDisplayFormat()); ?></b></span>
			<form action="" method="post">
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
				<form action="" method="post">
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
				<form action="" method="post">
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
				<?php if(isSet($default) AND $_GET['meetingroom'] == $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Default Room For Device</h2></div>
				<?php elseif(isSet($default) AND $_GET['meetingroom'] != $default['TheMeetingRoomID']) : ?>
					<div class="left"><h2>Viewing Non-Default Room For Device</h2></div>
				<?php elseif(!isSet($default)) : ?>
					<div class="left"><h2>Viewing Selected Meeting Room</h2></div>
				<?php endif; ?>
				<table><tr>
					<?php foreach($meetingrooms AS $meetingRoomID => $bookings): ?>
						<td><form action="" method="post">
							<table>
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
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php elseif($currentStartTimeInMinutes >= $nextStartTimeInMinutes AND $timeNowInMinutes > $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($displayTimeNow); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php else : ?>
												<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < $nextStartTimeInMinutes;) : ?>
													<?php if($currentStartTimeInMinutes+$bookingMinuteChunks > $nextStartTimeInMinutes) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $nextStartTimeInMinutes, $bookingMinuteChunks); ?>
													<?php else : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
													<?php endif; ?>
													<tr>
														<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
														<td style="background-color: #33ff33;"></td>
													</tr>
													<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
												<?php endfor; ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php endif; ?>
											<?php $currentStartTimeInMinutes = $nextEndTimeInMinutes; ?>
											<input type="hidden" name="bookingStartTime" value="">
											<input type="hidden" name="BookingID" value="<?php htmlout($bookingInfo['BookingID']); ?>">
											<input type="hidden" name="MeetingRoomName" value="<?php htmlout($bookingInfo['MeetingRoomName']); ?>">
											<input type="hidden" name="MeetingRoomID" value="<?php htmlout($meetingRoomID); ?>">
										<?php endif; ?>
									<?php endforeach; ?>

									<?php if($currentStartTimeInMinutes < 1440) : ?>
										<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
										<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
											<tr>
												<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
												<td style="background-color: #33ff33;"></td>
											</tr>
											<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
											<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
										<?php endfor; ?>
									<?php endif; ?>
								<?php else : ?>
									<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
									<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
										<tr>
											<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
											<td style="background-color: #33ff33;"></td>
										</tr>
										<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
										<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
									<?php endfor; ?>
								<?php endif; ?>
							</table>
						</form></td>
					<?php endforeach; ?>
				</tr></table>
			<?php else : ?>
				<div class="left"><h2>This isn't a valid meeting room.</h2></div>
			<?php endif; ?>
		<?php else : ?>
			<?php if(!empty($meetingrooms)) :?>
				<div class="left"><h2>Active Meeting Rooms:</h2></div>
				<table><tr>
					<?php foreach($meetingrooms AS $meetingRoomID => $bookings): ?>
						<td><form action="" method="post">
							<table>
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
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php elseif($currentStartTimeInMinutes >= $nextStartTimeInMinutes AND $timeNowInMinutes > $nextStartTimeInMinutes) : ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($displayTimeNow); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php else : ?>
												<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < $nextStartTimeInMinutes;) : ?>
													<?php if($currentStartTimeInMinutes+$bookingMinuteChunks > $nextStartTimeInMinutes) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $nextStartTimeInMinutes, $bookingMinuteChunks); ?>
													<?php else : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
													<?php endif; ?>
													<tr>
														<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
														<td style="background-color: #33ff33;"></td>
													</tr>
													<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
												<?php endfor; ?>
												<?php if($nextEndTimeInMinutes > $nextStartTimeInMinutes+$bookingMinuteChunks) : ?>
													<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes<$nextEndTimeInMinutes;) : ?>
														<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
														<tr>
															<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
															<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
														</tr>
														<?php $currentStartTimeInMinutes = $endTimeInMinutes; ?>
													<?php endfor; ?>
												<?php else : ?>
													<tr>
														<td><?php htmlout($bookingInfo['MeetingStartTime']); ?> - <?php htmlout($bookingInfo['MeetingEndTime']); ?></td>
														<td style="background-color: #ff3333;"><?php htmlout($bookingInfo['BookingDisplayName']); ?></td>
													</tr>
												<?php endif; ?>
											<?php endif; ?>
											<?php $currentStartTimeInMinutes = $nextEndTimeInMinutes; ?>
											<input type="hidden" name="bookingStartTime" value="">
											<input type="hidden" name="BookingID" value="<?php htmlout($bookingInfo['BookingID']); ?>">
											<input type="hidden" name="MeetingRoomName" value="<?php htmlout($bookingInfo['MeetingRoomName']); ?>">
											<input type="hidden" name="MeetingRoomID" value="<?php htmlout($meetingRoomID); ?>">
										<?php endif; ?>
									<?php endforeach; ?>

									<?php if($currentStartTimeInMinutes < 1440) : ?>
										<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
										<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
											<tr>
												<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
												<td style="background-color: #33ff33;"></td>
											</tr>
											<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
											<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
										<?php endfor; ?>
									<?php endif; ?>
								<?php else : ?>
									<?php $endTimeInMinutes = getNextBookingEndTime($currentStartTimeInMinutes, $currentStartTimeInMinutes+$bookingMinuteChunks, $bookingMinuteChunks); ?>
									<?php for($currentStartTimeInMinutes; $currentStartTimeInMinutes < 1440;) : ?>
										<tr>
											<td><?php echo convertMinutesToTime($currentStartTimeInMinutes); ?> - <?php echo convertMinutesToTime($endTimeInMinutes); ?></td>
											<td style="background-color: #33ff33;"></td>
										</tr>
										<?php $currentStartTimeInMinutes=$endTimeInMinutes; ?>
										<?php $endTimeInMinutes+=$bookingMinuteChunks; ?>
									<?php endfor; ?>
								<?php endif; ?>
							</table>
						</form></td>
					<?php endforeach; ?>
				</tr></table>
			<?php else : ?>
				<div class="left"><h2>There are no meeting rooms.</h2></div>
			<?php endif; ?>
		<?php endif; ?>
	</body>
</html>