<!-- This is the HTML form used for EDITING or ADDING USER information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
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
					<p><b><?php htmlout($generatedPassword); ?></b></p>
					<input type="hidden" name="generatedPassword"
					value="<?php htmlout($generatedPassword); ?>">
					<input type="hidden" name="hashedPassword" id="hashedPassword"
					value="<?php htmlout($hashedPassword); ?>">
				</div>
			<?php elseif($action == 'editform') : ?>
				<div>
					<label for="password">Set new Password:</label>
					<input type="password" name="password" id="password"
					value="<?php htmlout($password);?>">
				</div>
				<div>
					<label for="confirmpassword">Confirm Password:</label>
					<input type="password" name="confirmpassword" id="confirmpassword"
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
					value="<?php htmlout($displayname); ?>">
				</label>
			</div>
			<div style="display:<?php htmlout($bookingdescriptionStyle); ?>">
				<label for="bookingdescription">Default Booking Description: 
					<input type="text" name="bookingdescription" id="bookingdescription" 
					value="<?php htmlout($bookingdescription); ?>">
				</label>
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