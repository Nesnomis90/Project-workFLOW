<!-- This is the HTML form used for DISPLAYING a list of USER email-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Manage Users</title>
	</head>
	<body>
		<h1>Email List</h1>
		<form action="" method="post">
			<?php if(	isset($_SESSION['UserEmailListSeparatorSelected']) AND
						$_SESSION['UserEmailListSeparatorSelected']) : ?>
				<label for="separatorchar">The character selected to separate emails with: </label>
				<input type="text" name="separatorchardisabled" id="separatorchardisabled"
				disabled
				value="<?php htmlout($separatorChar); ?>">						
				<input type="submit" name="action" value="changeseparatorchar">
			<?php else : ?>
				<label for="separatorchar">Set the character you want to separate emails with: </label>
				<input type="text" name="separatorchar" id="separatorchar"
				placeholder="e.g. , or ;"
				value="<?php htmlout($separatorChar); ?>">			
				<input type="hidden" name="action" value="selectseparatorchar">
				<input type="submit" value="Select Separator Char"			
			<?php endif; ?>
		</form>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>