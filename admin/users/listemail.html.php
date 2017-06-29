<!-- This is the HTML form used for DISPLAYING a list of USER email-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<title>Manage Users</title>
		<style>
			#emailList {
				vertical-align: top;
			}
		</style>			
	</head>
	<body>
		<h1>Email List</h1>
		<?php if(isset($_SESSION['UserEmailListError'])) : ?>
			<h2><?php htmlout($_SESSION['UserEmailListError']); ?></h2>
			<?php unset($_SESSION['UserEmailListError']); ?>
		<?php endif; ?>
		<div>
			<form action="" method="post">
				<?php if(	isset($_SESSION['UserEmailListSeparatorSelected']) AND
							$_SESSION['UserEmailListSeparatorSelected']) : ?>
					<label for="separatorchar">The character selected to separate emails with: </label>
					<input type="text" name="separatorchardisabled" id="separatorchardisabled"
					disabled
					value="<?php htmlout($separatorChar); ?>">						
					<input type="submit" name="action" value="Change Separator Char">
				<?php else : ?>
					<label for="separatorchar">Set the character you want to separate emails with: </label>
					<input type="text" name="separatorchar" id="separatorchar"
					placeholder="e.g. , or ;"
					value="<?php htmlout($separatorChar); ?>">
					<input type="submit" name="action" value="Select Separator Char">		
				<?php endif; ?>
			</form>
		</div>
		<div>
			<label for="emailList">Copy Into Your Email Program:</label>
			<textarea rows="4" cols="50" name="emailList" id="emailList"
			disabled
			placeholder="List of User Email"><?php htmlout($emailList); ?></textarea>
		</div>
		<div>
			<label>Tip: On WINDOWS - Click inside box once. Press Ctrl+A then CTRL+C to copy everything. Paste into your Email program with Ctrl+V.</label>
		</div>
		<div>
			<label>Tip: On MAC - Click inside box once. Press Command-A then Command-C to copy everything. Paste into your Email program with Command-V.</label>
		</div>
		<div>
			<form action="" method="post">
				<input type="submit" name="action" value="Return To Users">
			</form>
		</div>
		<p><a href="..">Return to CMS home</a></p>
	</body>
</html>