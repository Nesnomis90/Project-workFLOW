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
				<label for="firstname">First Name: 
					<input type="text" name="firstname" id="firstname" 
					placeholder="Enter First Name"
					value="<?php htmlout($firstname); ?>">
				</label>
			</div>
			<div>
				<label for="lastname">Last Name: 
					<input type="text" name="lastname" id="lastname" 
					placeholder="Enter Last Name"
					value="<?php htmlout($lastname); ?>">
				</label>
			</div>
			<div>
				<label for="email">Email: 
					<input type="text" name="email" id="email" 
					placeholder="Enter Email"
					value="<?php htmlout($email); ?>">
				</label>
			</div>
			<?php if($action == 'addform') : ?>
				<div>
					<label for="password">Generated Password:</label>
					<b><?php htmlout($generatedPassword); ?></b>
				</div>
			<?php elseif($action == 'editform') : ?>
				<div>
					<label for="password">Set new Password:</label>
					<input type="password" name="password" id="password"
					placeholder="Enter New Password"
					value="<?php htmlout($password);?>">
				</div>
				<div>
					<label for="confirmpassword">Confirm Password:</label>
					<input type="password" name="confirmpassword" id="confirmpassword"
					placeholder="Enter New Password"
					value="<?php htmlout($confirmpassword);?>">
				</div>
			<?php endif; ?>
			<div>
				<label for="accessID">Access: 
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
			<div style="display:<?php htmlout($displaynameStyle); ?>">
				<label for="displayname">Default Display Name: 
					<input type="text" name="displayname" id="displayname"
					placeholder="Enter A Display Name"					
					value="<?php htmlout($displayname); ?>">
				</label>
			</div>		
			<div style="display:<?php htmlout($bookingdescriptionStyle); ?>">
				<label for="bookingdescription">Default Booking Description: </label>
				<textarea rows="4" cols="50" name="bookingdescription" id="bookingdescription"
				placeholder="Enter A Booking Description"><?php htmlout($bookingdescription); ?></textarea>
			</div>
			<div>
				<input type="hidden" name="id" value="<?php htmlout($id); ?>">
				<input type="submit" value="<?php htmlout($button); ?>">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	<p><a href="..">Return to CMS home</a></p>
	<?php include '../logout.inc.html.php'; ?>
	</body>
</html>