<!-- This is the HTML form used for EDITING or ADDING MEETING ROOM information-->
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
				<label for="name">Room Name: 
					<input type="text" name="name" id="name" 
					required placeholder="Enter Room Name" 
					oninvalid="this.setCustomValidity('Enter Room Name Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($name); ?>">
				</label>
			</div>
			<div>
				<label for="capacity">Capacity: 
					<input type="number" name="capacity" id="capacity" 
					required placeholder="Enter Capacity" 
					oninvalid="this.setCustomValidity('Enter Capacity Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($capacity); ?>">
				</label>
			</div>
			<div>
				<label for="description">Room Description: 
					<input type="text" name="description" id="description" 
					required placeholder="Enter Room Description" 
					oninvalid="this.setCustomValidity('Enter Room Description Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($description); ?>">
				</label>
			</div>
			<div>
				<label for="location">Location: 
					<input type="text" name="location" id="location" 
					required placeholder="Enter Location" 
					oninvalid="this.setCustomValidity('Enter Room Location Here')"
					oninput="setCustomValidity('')"
					value="<?php htmlout($location); ?>">
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