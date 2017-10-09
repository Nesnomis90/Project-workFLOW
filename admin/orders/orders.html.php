<!-- This is the HTML form used for DISPLAYING a list of Order-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script>
			$(document).ready(function blinkText(){ 
				$(".blink_me").fadeOut("normal").fadeIn("normal", blinkText);
			});
		</script>
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Orders</title>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<div class="left">
			<?php if(isSet($_SESSION['OrderUserFeedback'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['OrderUserFeedback']); ?></b></span>
				<?php unset($_SESSION['OrderUserFeedback']); ?>
			<?php endif; ?>
		</div>

		<?php if($sortBy == "Day") : ?>
			<table>
				<caption>Active Orders - Listed by Day</caption>
				<?php if($rowNum > 0) : ?>
					<?php foreach($orderByDay AS $dayNumber => $days) : ?>
						<tr><td colspan="15"><?php htmlout("Day Number: " . $dayNumber); ?></td></tr>
						<tr><td>
							<table>
								<tr>
									<th colspan="12">Order</th>
									<th colspan="3">Messages</th>
									<th colspan="4">Meeting</th>
									<th colspan="1">Cancel Order</th>
								</tr>
								<tr>
									<th>Status</th>
									<th>Approved By User</th>
									<th>Approved By Staff</th>
									<th>Approved By Name</th>
									<th>Content</th>
									<th>User Notes</th>
									<th>Admin Note</th>
									<th>Final Price</th>
									<th>Created At</th>
									<th>Last Update</th>
									<th>Approved At</th>
									<th>Details</th>
									<th>Status</th>
									<th>Last Message From Staff</th>
									<th>Last Message From User</th>
									<th>Room Name</th>
									<th>Start</th>
									<th>End</th>
									<th>Booked For Company</th>
									<th></th>
								</tr>
								<?php foreach($days AS $order) : ?>
									<form action="" method="post">
										<tr>
											<?php if($order['OrderStatus'] == "New Order!") : ?>
												<td style="white-space: pre-wrap; color: green;"><span class="blink_me"><?php htmlout($order['OrderStatus']); ?></span></td>
											<?php else : ?>
												<td style="white-space: pre-wrap;"><?php htmlout($order['OrderStatus']); ?></td>
											<?php endif; ?>
											<td><?php htmlout($order['OrderApprovedByUser']); ?></td>
											<td><?php htmlout($order['OrderApprovedByStaff']); ?></td>
											<td><?php htmlout($order['OrderApprovedByName']); ?></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderContent']); ?></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderUserNotes']); ?></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderAdminNote']); ?></td>
											<td><?php htmlout($order['OrderFinalPrice']); ?></td>
											<td><?php htmlout($order['DateTimeCreated']); ?></td>
											<td><?php htmlout($order['DateTimeUpdated']); ?></td>
											<td><?php htmlout($order['DateTimeApproved']); ?></td>
											<td><input type="submit" name="action" value="Details"></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderMessageStatus']); ?></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderLastMessageFromStaff']); ?></td>
											<td style="white-space: pre-wrap;"><?php htmlout($order['OrderLastMessageFromUser']); ?></td>
											<td><?php htmlout($order['OrderRoomName']); ?></td>
											<td><?php htmlout($order['OrderStartTime']); ?></td>
											<td><?php htmlout($order['OrderEndTime']); ?></td>
											<td><?php htmlout($order['OrderBookedFor']); ?></td>
											<td><input type="submit" name="action" value="Cancel"></td>
											<input type="hidden" name="OrderID" value="<?php htmlout($order['TheOrderID']); ?>">
										</tr>
									</form>
								<?php endforeach; ?>
							</table>
						</td></tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="3"><b>There are no active orders.</b></td></tr>
				<?php endif; ?>
			</table>
		<?php elseif($sortBy == "Week") : ?>
			<table>
				<caption>Active Orders - Listed by Week</caption>
				<?php if($rowNum > 0) : ?>
					<?php foreach($orderByWeek as $weekNumber => $weeks): ?>
						<tr>
							<td><?php htmlout("Week #" . $weekNumber); ?></td>
							<?php foreach($weeks AS $dayName => $days) : ?>
								<td><?php htmlout($dayName); ?></td>
								<td>
									<table>
										<tr>
											<th colspan="12">Order</th>
											<th colspan="3">Messages</th>
											<th colspan="4">Meeting</th>
											<th colspan="1">Cancel Order</th>
										</tr>
										<tr>
											<th>Status</th>
											<th>Approved By User</th>
											<th>Approved By Staff</th>
											<th>Approved By Name</th>
											<th>Content</th>
											<th>User Notes</th>
											<th>Admin Note</th>
											<th>Final Price</th>
											<th>Created At</th>
											<th>Last Update</th>
											<th>Approved At</th>
											<th>Details</th>
											<th>Status</th>
											<th>Last Message From Staff</th>
											<th>Last Message From User</th>
											<th>Room Name</th>
											<th>Start</th>
											<th>End</th>
											<th>Booked For Company</th>
											<th></th>
										</tr>
											<?php foreach($days as $order): ?>
												<form action="" method="post">
													<tr>
														<?php if($order['OrderStatus'] == "New Order!") : ?>
															<td style="white-space: pre-wrap; color: green;"><span class="blink_me"><?php htmlout($order['OrderStatus']); ?></span></td>
														<?php else : ?>
															<td style="white-space: pre-wrap;"><?php htmlout($order['OrderStatus']); ?></td>
														<?php endif; ?>
														<td><?php htmlout($order['OrderApprovedByUser']); ?></td>
														<td><?php htmlout($order['OrderApprovedByStaff']); ?></td>
														<td><?php htmlout($order['OrderApprovedByName']); ?></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderContent']); ?></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderUserNotes']); ?></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderAdminNote']); ?></td>
														<td><?php htmlout($order['OrderFinalPrice']); ?></td>
														<td><?php htmlout($order['DateTimeCreated']); ?></td>
														<td><?php htmlout($order['DateTimeUpdated']); ?></td>
														<td><?php htmlout($order['DateTimeApproved']); ?></td>
														<td><input type="submit" name="action" value="Details"></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderMessageStatus']); ?></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderLastMessageFromStaff']); ?></td>
														<td style="white-space: pre-wrap;"><?php htmlout($order['OrderLastMessageFromUser']); ?></td>
														<td><?php htmlout($order['OrderRoomName']); ?></td>
														<td><?php htmlout($order['OrderStartTime']); ?></td>
														<td><?php htmlout($order['OrderEndTime']); ?></td>
														<td><?php htmlout($order['OrderBookedFor']); ?></td>
														<td><input type="submit" name="action" value="Cancel"></td>
														<input type="hidden" name="OrderID" value="<?php htmlout($order['TheOrderID']); ?>">
													</tr>
												</form>
											<?php endforeach; ?>
									</table>
								</td>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="3"><b>There are no active orders.</b></td></tr>
				<?php endif; ?>
			</table>
		<?php else : ?>
			<table>
				<caption>Active Orders - Listed by starting time</caption>
				<tr>
					<th colspan="12">Order</th>
					<th colspan="3">Messages</th>
					<th colspan="4">Meeting</th>
					<th colspan="1">Cancel Order</th>
				</tr>
				<tr>
					<th>Status</th>
					<th>Approved By User</th>
					<th>Approved By Staff</th>
					<th>Approved By Name</th>
					<th>Content</th>
					<th>User Notes</th>
					<th>Admin Note</th>
					<th>Final Price</th>
					<th>Created At</th>
					<th>Last Update</th>
					<th>Approved At</th>
					<th>Details</th>
					<th>Status</th>
					<th>Last Message From Staff</th>
					<th>Last Message From User</th>
					<th>Room Name</th>
					<th>Start</th>
					<th>End</th>
					<th>Booked For Company</th>
					<th></th>
				</tr>
				<?php if($rowNum > 0) : ?>
					<?php foreach($order AS $row): ?>
						<form action="" method="post">
							<tr>
								<?php if($row['OrderStatus'] == "New Order!") : ?>
									<td style="white-space: pre-wrap; color: green;"><span class="blink_me"><?php htmlout($row['OrderStatus']); ?></span></td>
								<?php else : ?>
									<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td>
								<?php endif; ?>
								<td><?php htmlout($row['OrderApprovedByUser']); ?></td>
								<td><?php htmlout($row['OrderApprovedByStaff']); ?></td>
								<td><?php htmlout($row['OrderApprovedByName']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
								<td><?php htmlout($row['OrderFinalPrice']); ?></td>
								<td><?php htmlout($row['DateTimeCreated']); ?></td>
								<td><?php htmlout($row['DateTimeUpdated']); ?></td>
								<td><?php htmlout($row['DateTimeApproved']); ?></td>
								<td><input type="submit" name="action" value="Details"></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderMessageStatus']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromStaff']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromUser']); ?></td>
								<td><?php htmlout($row['OrderRoomName']); ?></td>
								<td><?php htmlout($row['OrderStartTime']); ?></td>
								<td><?php htmlout($row['OrderEndTime']); ?></td>
								<td><?php htmlout($row['OrderBookedFor']); ?></td>
								<td><input type="submit" name="action" value="Cancel"></td>
								<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
							</tr>
						</form>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="20"><b>There are no active orders.</b></td></tr>
				<?php endif; ?>
			</table>
		<?php endif; ?>

		<?php if(isSet($ordersCompleted)) : ?>
			<table><caption>Completed Orders</caption>
				<tr>
					<th colspan="10">Order</th>
					<th colspan="3">Messages</th>
					<th colspan="4">Meeting</th>
				</tr>
				<tr>
					<th>Status</th>
					<th>Approved By Name</th>
					<th>Content</th>
					<th>User Notes</th>
					<th>Admin Note</th>
					<th>Final Price</th>
					<th>Last Update</th>
					<th>Approved At</th>
					<th>Completed At</th>
					<th>Details</th>
					<th>Status</th>
					<th>Last Message From Staff</th>
					<th>Last Message From User</th>
					<th>Booked For Company</th>
				</tr>
					<?php foreach($ordersCompleted AS $row): ?>
						<form action="" method="post">
							<tr>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td>
								<td><?php htmlout($row['OrderApprovedByName']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
								<td><?php htmlout($row['OrderFinalPrice']); ?></td>
								<td><?php htmlout($row['DateTimeUpdated']); ?></td>
								<td><?php htmlout($row['DateTimeApproved']); ?></td>
								<td><?php htmlout($row['DateTimeCompleted']); ?></td>
								<td><input type="submit" name="action" value="Details"></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderMessageStatus']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromStaff']); ?></td>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromUser']); ?></td>
								<td><?php htmlout($row['OrderBookedFor']); ?></td>
								<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
							</tr>
						</form>
					<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<?php if(isSet($ordersCancelled)) : ?>
			<table><caption>Cancelled Orders</caption>
				<tr>
					<th colspan="8">Order</th>
					<th colspan="3">Messages</th>
					<th colspan="4">Meeting</th>
				</tr>
				<tr>
					<th>Status</th>
					<th>Content</th>
					<th>User Notes</th>
					<th>Admin Note</th>
					<th>Created At</th>
					<th>Last Update</th>
					<th>Cancelled At</th>
					<th>Details</th>
					<th>Status</th>
					<th>Last Message From Staff</th>
					<th>Last Message From User</th>
					<th>Room Name</th>
					<th>Start</th>
					<th>End</th>
					<th>Booked For Company</th>
				</tr>
				<?php foreach($ordersCancelled AS $row): ?>
					<form action="" method="post">
						<tr>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['DateTimeCancelled']); ?></td>
							<td><input type="submit" name="action" value="Details"></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderMessageStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromStaff']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromUser']); ?></td>
							<td><?php htmlout($row['OrderRoomName']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['OrderBookedFor']); ?></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>

		<?php if(isSet($ordersOther)) : ?>
			<table><caption>Other Orders</caption>
				<tr>
					<th colspan="12">Order</th>
					<th colspan="3">Messages</th>
					<th colspan="4">Meeting</th>
					<th colspan="1">Cancel Order</th>
				</tr>
				<tr>
					<th>Status</th>
					<th>Approved By User</th>
					<th>Approved By Staff</th>
					<th>Approved By Name</th>
					<th>Content</th>
					<th>User Notes</th>
					<th>Admin Note</th>
					<th>Final Price</th>
					<th>Created At</th>
					<th>Last Update</th>
					<th>Approved At</th>
					<th>Details</th>
					<th>Status</th>
					<th>Last Message From Staff</th>
					<th>Last Message From User</th>
					<th>Room Name</th>
					<th>Start</th>
					<th>End</th>
					<th>Booked For Company</th>
					<th></th>
				</tr>
				<?php foreach($ordersOther AS $row) : ?>
					<form action="" method="post">
						<tr>
							<?php if($row['OrderStatus'] == "New Order!") : ?>
								<td style="white-space: pre-wrap; color: green;"><span class="blink_me"><?php htmlout($row['OrderStatus']); ?></span></td>
							<?php else : ?>
								<td style="white-space: pre-wrap;"><?php htmlout($row['OrderStatus']); ?></td>
							<?php endif; ?>
							<td><?php htmlout($row['OrderApprovedByUser']); ?></td>
							<td><?php htmlout($row['OrderApprovedByStaff']); ?></td>
							<td><?php htmlout($row['OrderApprovedByName']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderContent']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderUserNotes']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderAdminNote']); ?></td>
							<td><?php htmlout($row['OrderFinalPrice']); ?></td>
							<td><?php htmlout($row['DateTimeCreated']); ?></td>
							<td><?php htmlout($row['DateTimeUpdated']); ?></td>
							<td><?php htmlout($row['DateTimeApproved']); ?></td>
							<td><input type="submit" name="action" value="Details"></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderMessageStatus']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromStaff']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($row['OrderLastMessageFromUser']); ?></td>
							<td><?php htmlout($row['OrderRoomName']); ?></td>
							<td><?php htmlout($row['OrderStartTime']); ?></td>
							<td><?php htmlout($row['OrderEndTime']); ?></td>
							<td><?php htmlout($row['OrderBookedFor']); ?></td>
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="OrderID" value="<?php htmlout($row['TheOrderID']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
		<?php endif; ?>
	</body>
</html>