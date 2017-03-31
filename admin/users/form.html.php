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
		<form action="?<?php htmlout($action); ?>" method="post">
			<div>
				<label for="firstname">First Name: 
					<input type="text" name="firstname" id="firstname" 
					required placeholder="Enter First Name" 
					oninvalid="this.setCustomValidity('Enter First Name Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($firstname); ?>">
				</label>
			</div>
			<div>
				<label for="lastname">Last Name: 
					<input type="text" name="lastname" id="lastname" 
					required placeholder="Enter Last Name" 
					oninvalid="this.setCustomValidity('Enter Last Name Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($lastname); ?>">
				</label>
			</div>
			<div>
				<label for="email">Email: 
					<input type="email" name="email" id="email" 
					required placeholder="Enter Email" 
					oninvalid="this.setCustomValidity('Enter Email Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($email); ?>">
				</label>
			</div>
			<div>
				<label for="accessID">Access: 
					<select name="accessID" id="accessID">
						<?php foreach($access as $row): ?> 
							<?php if($row['accessname']==$accessname):?>
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
	</body>
</html>