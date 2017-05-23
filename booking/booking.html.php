<!-- This is the HTML form used to display booking information to normal users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Booking Information</title>
		<style>
			#bookingstable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}

			#bookingstable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#bookingstable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}

			#bookingstable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}			
			
			#bookingstable tr:hover{background-color:#ddd;}
			
			#bookingstable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#bookingstable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>		
	</head>
	<body>
	<?php if(isset($_GET['meetingroom']) AND $_GET['meetingroom']!=NULL AND $_GET['meetingroom']!="") : ?>
		<h1>Set Booking Information</h1>
		<?php if(isset($_SESSION['normalBookingFeedback'])) : ?>
			<p><b><?php htmlout($_SESSION['normalBookingFeedback']); ?></b></p>
			<?php unset($_SESSION['normalBookingFeedback']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<label for="meetingRoomID">Meeting Room: </label>
				<?php if(isset($_GET['meetingroom'])) : ?>
					<?php foreach($meetingroom as $row): ?> 
						<?php if($row['meetingRoomID']==$_GET['meetingroom']):?>
							<div><b><?php htmlout($row['meetingRoomName']);?></b></div>
						<?php endif;?>
					<?php endforeach; ?>					
				<?php else : ?>
					<select name="meetingRoomID" id="meetingRoomID">
						<?php foreach($meetingroom as $row): ?> 
							<?php if($row['meetingRoomID']==$selectedMeetingRoomID):?>
								<option selected="selected" 
										value="<?php htmlout($row['meetingRoomID']); ?>">
										<?php htmlout($row['meetingRoomName']);?>
								</option>
							<?php else : ?>
								<option value="<?php htmlout($row['meetingRoomID']); ?>">
										<?php htmlout($row['meetingRoomName']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				<?php endif; ?>
			</div>
			<div>
				<label for="startDateTime">Start Time: </label>
				<input type="text" name="startDateTime" id="startDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($startDateTime); ?>">
			</div>
			<div>
				<label for="endDateTime">End Time: </label>
				<input type="text" name="endDateTime" id="endDateTime" 
				placeholder="date hh:mm:ss"
				value="<?php htmlout($endDateTime); ?>">
			</div>
			<div>
				<label for="companyID">Company: </label>
				<?php if(	isset($_SESSION['CreateBookingDisplayCompanySelect']) AND 
							$_SESSION['CreateBookingDisplayCompanySelect']) : ?>
					<?php if(!isset($_SESSION['CreateBookingSelectedACompany'])) : ?>
						<select name="companyID" id="companyID">
							<?php foreach($company as $row): ?> 
								<?php if($row['companyID']==$selectedCompanyID):?>
									<option selected="selected" 
											value="<?php htmlout($row['companyID']); ?>">
											<?php htmlout($row['companyName']);?>
									</option>
								<?php else : ?>
									<option value="<?php htmlout($row['companyID']); ?>">
											<?php htmlout($row['companyName']);?>
									</option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
						<input type="submit" name="action" value="Select This Company">
					<?php else : ?>
						<b><?php htmlout($companyName); ?></b>
						<input type="hidden" name="companyID" id="companyID" 
						value="<?php htmlout($companyID); ?>">
						<input type="submit" name="action" value="Change Company">
					<?php endif; ?>
				<?php else : ?>
					<?php if(isset($company)) : ?>
						<b>Your booking will automatically be connected with company: <?php htmlout($companyName); ?></b>
					<?php else : ?>
						<b>Your booking will not be connected with a company.</b>
					<?php endif; ?>
					<input type="hidden" name="companyID" id="companyID" 
					value="<?php htmlout($companyID); ?>">
				<?php endif; ?>
			</div>
			<div>
				<label for="displayName">Display Name: </label>
				<input type="text" name="displayName" id="displayName" 
				value="<?php htmlout($displayName); ?>">
				<input type="submit" name="action" value="Get Default Display Name">
			</div>
			<div>
				<label for="description">Booking Description: </label>
				<textarea rows="4" cols="50" name="description" id="description"><?php htmlout($description); ?></textarea>
				<input type="submit" name="action" value="Get Default Booking Description"> 
			</div>
			<div>
				<input type="submit" name="action" value="Reset">
				<?php if(!isset($_SESSION['CreateBookingSelectedACompany'])) : ?>
					<input type="submit" name="disabled" value="Create Meeting" disabled>
					<b>You need to select the company you want before you can add the booking.</b>
				<?php else : ?>
					<input type="submit" name="action" value="Create Meeting">
				<?php endif; ?>				
			</div>
		</form>
	<?php elseif(isset($_GET['cancellationcode'])) : ?>
		<h1>Cancel Your Booking!</h1>
	<?php elseif(isset($_SESSION['loggedIn'])) : ?>
		<form action="" method="post">		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div>
					<input type="submit" name="action" value="Create Booking">
				</div>		
			</form>
			<table id="bookingstable">
				<caption>All booking history</caption>
				<tr>
					<th colspan="8">Booking information</th>
					<th colspan="2">Alter Booking</th>
				</tr>				
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Display Name</th>
					<th>For Company</th>
					<th>Description</th>
					<th>Created At</th>
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if(	$booking['BookingStatus'] == "Active" OR 
								$booking['BookingStatus'] == "Completed Today"): ?>					
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td><?php htmlout($booking['EndTime']); ?></td>
							<td><?php htmlout($booking['BookedBy']); ?></td>
							<td><?php htmlout($booking['BookedForCompany']); ?></td>
							<td><?php htmlout($booking['BookingDescription']); ?></td>
							<td><?php htmlout($booking['BookingWasCreatedOn']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
							<input type="hidden" name="UserInfo" id="UserInfo"
							value="<?php htmlout($booking['UserInfo']); ?>">
							<input type="hidden" name="MeetingInfo" id="MeetingInfo"
							value="<?php htmlout($booking['MeetingInfo']); ?>">
							<input type="hidden" name="BookingStatus" id="BookingStatus"
							value="<?php htmlout($booking['BookingStatus']); ?>">
							<input type="hidden" name="Email" id="Email"
							value="<?php htmlout($booking['email']); ?>">
						</tr>
					<?php endif; ?>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no booked meetings registered in the database.</b></tr>
			<form action="" method="post">
				<tr><input type="submit" name="action" value="Create Booking"></tr>
			</form>
		<?php endif; ?>
		</form>
	<?php elseif(!isset($_SESSION['loggedIn'])) : ?>
		<form action="" method="post">		
		<?php if($rowNum>0) :?>
			<form action="" method="post">
				<div>
					<input type="submit" name="action" value="Create Booking">
				</div>		
			</form>
			<table id="bookingstable">
				<caption>All booking history</caption>
				<tr>
					<th colspan="4">Booking information</th>
					<th colspan="2">Alter Booking</th>
				</tr>				
				<tr>
					<th>Status</th>
					<th>Room Name</th>
					<th>Start Time</th>
					<th>End Time</th>
					<th>Edit</th>			
					<th>Cancel</th>
				</tr>
				<?php foreach ($bookings as $booking): ?>
					<form action="" method="post">
					<?php if(	$booking['BookingStatus'] == "Active" OR 
								$booking['BookingStatus'] == "Completed Today"): ?>
						<tr>
							<td><?php htmlout($booking['BookingStatus']);?></td>
							<td><?php htmlout($booking['BookedRoomName']); ?></td>
							<td><?php htmlout($booking['StartTime']); ?></td>
							<td><?php htmlout($booking['EndTime']); ?></td>
							<td><input type="submit" name="action" value="Edit"></td>							
							<td><input type="submit" name="action" value="Cancel"></td>
							<input type="hidden" name="id" value="<?php htmlout($booking['id']); ?>">
							<input type="hidden" name="MeetingInfo" id="MeetingInfo"
							value="<?php htmlout($booking['MeetingInfo']); ?>">
							<input type="hidden" name="BookingStatus" id="BookingStatus"
							value="<?php htmlout($booking['BookingStatus']); ?>">
						</tr>
					<?php endif; ?>
					</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no booked meetings registered in the database.</b></tr>
			<form action="" method="post">
				<tr><input type="submit" name="action" value="Create Booking"></tr>
			</form>
		<?php endif; ?>
		</form>		
	<?php endif; ?>
	<?php //TO-DO: Fix -> include '../logout.inc.html.php'; ?>
	</body>
</html>