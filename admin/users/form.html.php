<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
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
				<label for="firstname">First Name: <input type="text" name="firstname"
				id="firstname" value="<?php htmlout($firstname); ?>"></label>
			</div>
			<div>
				<label for="lastname">Last Name: <input type="text" name="lastname"
				id="lastname" value="<?php htmlout($lastname); ?>"></label>
			</div>
			<div>
				<label for="email">Email: <input type="text" name="email"
				id="email" value="<?php htmlout($email); ?>"></label>
			</div>
			<div>
				<label for="accessname">Access: 
					<select> <?php htmlout($accessnames); ?></select>
				</label>
			</div>
			<div>
				<input type="hidden" name="id" value="<?php
				htmlout($id); ?>">
				<input type="submit" value="<?php htmlout($button); ?>">
			</div>
			<div>
				<input type="<?php htmlout($reset); ?>">
			</div>
		</form>
	</body>
</html>