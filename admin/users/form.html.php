<!-- This is the HTML form used for EDITING or ADDING USER information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#bookingdescription {
				vertical-align: top;
			}
		</style>		
		<title><?php htmlout($pageTitle); ?></title>
	</head>
	<body>
		<h1><?php htmlout($pageTitle); ?></h1>
		<?php if (isset($_SESSION['AddNewUserError'])) :?>
			<p><b><?php htmlout($_SESSION['AddNewUserError']);?></b></p> 
			<?php unset($_SESSION['AddNewUserError']); ?>
		<?php endif; ?>
		<form action="?<?php htmlout($action); ?>" method="post">
			<div>
				<?php if($action == 'editform') : ?>
				<label for="originalFirstName">Original First Name: </label>
				<b><?php htmlout($originalFirstName); ?></b>
				</div>
				<div>
				<?php endif; ?>
				<label for="firstname">Set New First Name: </label>
				<input type="text" name="firstname" id="firstname" 
				placeholder="Enter First Name"
				value="<?php htmlout($firstname); ?>">
			</div>
			<div>
				<?php if($action == 'editform') : ?>
				<label for="originalLastName">Original Last Name: </label>
				<b><?php htmlout($originalLastName); ?></b>
				</div>
				<div>
				<?php endif; ?>			
				<label for="lastname">Set New Last Name: </label>
				<input type="text" name="lastname" id="lastname" 
				placeholder="Enter Last Name"
				value="<?php htmlout($lastname); ?>">
			</div>
			<div>
				<?php if($action == 'editform') : ?>
				<label for="originalEmail">Original Email: </label>
				<b><?php htmlout($originalEmail); ?></b>
				</div>
				<div>				
				<?php endif; ?>			
				<label for="email">Set New Email: </label>
				<input type="text" name="email" id="email" 
				placeholder="Enter Email"
				value="<?php htmlout($email); ?>">
			</div>
			<?php if($action == 'addform') : ?>
				<div>
					<label for="password">Generated Password:</label>
					<b><?php htmlout($generatedPassword); ?></b>
				</div>
			<?php elseif($action == 'editform') : ?>
				<div>
					<label for="password">Set New Password:</label>
					<input type="password" name="password" id="password"
					placeholder="Enter New Password"
					value="<?php htmlout($password);?>">
				</div>
				<div>
					<label for="confirmpassword">Repeat Password:</label>
					<input type="password" name="confirmpassword" id="confirmpassword"
					placeholder="Enter New Password"
					value="<?php htmlout($confirmpassword);?>">
				</div>
			<?php endif; ?>
			<div>
				<?php if($action == 'editform') : ?>
				<label for="originalAccessName">Original Access: </label>
				<b><?php htmlout($originalAccessName); ?></b>
				</div>
				<div>				
				<?php endif; ?>			
				<label for="accessID">Set New Access: 
					<select name="accessID" id="accessID">
						<?php foreach($access as $row): ?> 
							<?php if($row['accessID']==$accessID):?>
								<option selected="selected" 
										value=<?php htmlout($row['accessID']); ?>>
										<?php htmlout($row['accessname']);?>
								</option>
							<?php else : ?>
								<option value=<?php htmlout($row['accessID']); ?>>
										<?php htmlout($row['accessname']);?>
								</option>
							<?php endif;?>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<?php if($action == 'editform') : ?>
			<div>
				<label for="originalDisplayName">Original Display Name: </label>
				<?php if($originalDisplayName != "") : ?>
					<b><?php htmlout($originalDisplayName); ?></b>
				<?php else: ?>
					<b>This User Has No Display Name.</b>
				<?php endif; ?>
			</div>
			<div>				
				<label for="displayname">Set New Display Name: </label>
				<input type="text" name="displayname" id="displayname"
				placeholder="Enter A Display Name"					
				value="<?php htmlout($displayname); ?>">
			</div>	
			<div>
				<label for="originalBookingDescription">Original Booking Description: </label>
				<?php if($originalBookingDescription != "") : ?>
					<b><?php htmlout($originalBookingDescription); ?></b>
				<?php else: ?>
					<b>This User Has No Booking Description.</b>
				<?php endif; ?>	
			</div>
			<div>
				<label for="bookingdescription">Set New Booking Description: </label>
				<textarea rows="4" cols="50" name="bookingdescription" id="bookingdescription"
				placeholder="Enter A Booking Description"><?php htmlout($bookingdescription); ?></textarea>
			</div>
			<div>
				<label for="originalDateToRemove">Original Date to Reduce Access:</label>
				<?php if(isset($originalDateToDisplay) AND $originalDateToDisplay != "") : ?>
					<b><?php htmlout($originalDateToDisplay); ?></b>	
				<?php else : ?>
					<b>No date has been Set</b>
				<?php endif; ?>
			</div>
			<div>
				<label for="ReduceAccessAtDate">Set New Date to Reduce Access: </label>
				<input type="text" name="ReduceAccessAtDate" id="ReduceAccessAtDate"
				value="<?php htmlout($reduceAccessAtDate); ?>">
			</div>
			<?php endif; ?>
			<div>
				<?php if($action == 'addform') :?>
					<input type="submit" name="add" value="Reset">
					<input type="submit" name="add" value="Cancel">
				<?php elseif($action == 'editform') : ?>
					<input type="submit" name="edit" value="Reset">
					<input type="submit" name="edit" value="Cancel">				
				<?php endif; ?>
			</div>
			<div>
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" name="action" value="<?php htmlout($button); ?>">
			</div>			
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>