<!-- This is the HTML form used for EDITING or ADDING USER information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>		
		<title><?php htmlout($pageTitle); ?></title>
		<style>
			<?php if($action == 'editform') : ?>
				label {
					width: 220px;
				}
			<?php else : ?>
				label {
					width: 150px;
				}
			<?php endif; ?>
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<fieldset><legend><?php htmlout($pageTitle); ?></legend>
			<div class="left">
				<?php if (isSet($_SESSION['AddNewUserError'])) :?>
					<span><b class="feedback"><?php htmlout($_SESSION['AddNewUserError']);?></b></span> 
					<?php unset($_SESSION['AddNewUserError']); ?>
				<?php endif; ?>
			</div>

			<form action="?<?php htmlout($action); ?>" method="post">
				<?php if($action == 'editform') : ?>
					<div>
						<label for="originalFirstName">Original First Name: </label>
						<span><b><?php htmlout($originalFirstName); ?></b></span>
					</div>
				<?php endif; ?>
				<div>
					<label for="firstname">Set New First Name: </label>
					<input type="text" name="firstname" placeholder="Enter First Name" value="<?php htmlout($firstname); ?>">
				</div>

				<?php if($action == 'editform') : ?>
					<div>
						<label for="originalLastName">Original Last Name: </label>
						<span><b><?php htmlout($originalLastName); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="lastname">Set New Last Name: </label>
					<input type="text" name="lastname" placeholder="Enter Last Name" value="<?php htmlout($lastname); ?>">
				</div>

				<?php if($action == 'editform') : ?>
					<div>
						<label for="originalEmail">Original Email: </label>
						<span><b><?php htmlout($originalEmail); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="email">Set New Email: </label>
					<input type="text" name="email" placeholder="Enter Email" value="<?php htmlout($email); ?>">
				</div>

				<?php if($action == 'addform') : ?>
					<div>
						<label for="password">Generated Password:</label>
						<span><b><?php htmlout($generatedPassword); ?></b></span>
					</div>
				<?php elseif($action == 'editform') : ?>
					<div>
						<label for="password">Password:</label>
						<input type="password" name="password" placeholder="Set New Password" value="<?php htmlout($password);?>">
					</div>

					<div>
						<label for="confirmpassword">Password:</label>
						<input type="password" name="confirmpassword" placeholder="Repeat New Password" value="<?php htmlout($confirmpassword);?>">
					</div>
				<?php endif; ?>

				<?php if($action == 'editform') : ?>
					<div>
						<label for="originalAccessName">Original Access: </label>
						<span><b><?php htmlout($originalAccessName); ?></b></span>
					</div>
				<?php endif; ?>

				<div>
					<label for="accessID">Set New Access: </label>
						<select name="accessID">
							<?php foreach($access as $row): ?> 
								<?php if($row['accessID']==$accessID):?>
									<option selected="selected" value=<?php htmlout($row['accessID']); ?>><?php htmlout($row['accessname']);?></option>
								<?php else : ?>
									<option value=<?php htmlout($row['accessID']); ?>><?php htmlout($row['accessname']);?></option>
								<?php endif;?>
							<?php endforeach; ?>
						</select>
				</div>

				<?php if($action == 'editform') : ?>
					<div>
						<label for="originalDisplayName">Original Display Name: </label>
						<?php if($originalDisplayName != "") : ?>
							<span><b><?php htmlout($originalDisplayName); ?></b></span>
						<?php else: ?>
							<span><b>This User Has No Display Name.</b></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="displayname">Set New Display Name: </label>
						<input type="text" name="displayname" placeholder="Enter A Display Name" value="<?php htmlout($displayname); ?>">
					</div>

					<div>
						<label for="originalBookingDescription">Original Booking Description: </label>
						<?php if($originalBookingDescription != "") : ?>
							<span><b style="white-space: pre-wrap;"><?php htmlout($originalBookingDescription); ?></b></span>
						<?php else: ?>
							<span><b>This User Has No Booking Description.</b></span>
						<?php endif; ?>	
					</div>

					<div>
						<label class="description" for="bookingdescription">Set New Booking Description: </label>
						<textarea rows="4" cols="50" name="bookingdescription" placeholder="Enter A Booking Description" style="white-space: pre-wrap;"><?php htmlout($bookingdescription); ?></textarea>
					</div>

					<div>
						<label for="originalDateToRemove">Original Date to Reduce Access:</label>
						<?php if(isSet($originalDateToDisplay) AND $originalDateToDisplay != "") : ?>
							<span><b><?php htmlout($originalDateToDisplay); ?></b></span>
						<?php else : ?>
							<span><b>No date has been Set</b></span>
						<?php endif; ?>
					</div>

					<div>
						<label for="ReduceAccessAtDate">Set New Date to Reduce Access: </label>
						<input type="text" name="ReduceAccessAtDate" value="<?php htmlout($reduceAccessAtDate); ?>">
					</div>
				<?php endif; ?>

				<div class="left">
					<input type="hidden" name="UserID" value="<?php htmlout($userID); ?>">
					<input type="submit" name="action" value="<?php htmlout($button); ?>">
					<?php if($action == 'addform') :?>
						<input type="submit" name="add" value="Reset">
						<input type="submit" name="add" value="Cancel">
					<?php elseif($action == 'editform') : ?>
						<input type="submit" name="edit" value="Reset">
						<input type="submit" name="edit" value="Cancel">
					<?php endif; ?>
				</div>
			</form>
		</fieldset>
	</body>
</html>