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
			label {
				width: 300px;
			}
		</style>
	</head>
	<body>
		<h1>Email List</h1>
		
		<div class="left">
			<?php if(isSet($_SESSION['UserEmailListError'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['UserEmailListError']); ?></b></span>
				<?php unset($_SESSION['UserEmailListError']); ?>
			<?php endif; ?>
		</div>
		
		<div>
			<form action="" method="post">
				<?php if(	isSet($_SESSION['UserEmailListSeparatorSelected']) AND
							$_SESSION['UserEmailListSeparatorSelected']) : ?>
					<label for="separatorchar">The current character to separate emails with: </label>
					<input style="width: 20px; text-align: center;" type="text" name="separatorchardisabled" id="separatorchardisabled"
					disabled
					value="<?php htmlout($separatorChar); ?>">						
					<input type="submit" name="action" value="Change Separator Char">
				<?php else : ?>
					<label for="separatorchar">Set new character to separate emails with: </label>
					<input style="width: 40px;"type="text" name="separatorchar" id="separatorchar"
					placeholder="e.g. ;"
					value="<?php htmlout($separatorChar); ?>">
					<input type="submit" name="action" value="Select Separator Char">		
				<?php endif; ?>
			</form>
		</div>
		
		<div>
			<label class="description" for="emailList">Copy Into Your Email Program:</label>
			<textarea rows="4" cols="50" name="emailList" id="emailList"
			disabled style="white-space: pre-wrap;"
			placeholder="List of User Email"><?php htmlout($emailList); ?></textarea>
		</div>
		
		<div>
			<span>Tip: On WINDOWS - Click inside box once. Press Ctrl+A then CTRL+C to copy everything. Paste into your Email program with Ctrl+V.</span>
		</div>
		
		<div>
			<span>Tip: On MAC - Click inside box once. Press Command-A then Command-C to copy everything. Paste into your Email program with Command-V.</span>
		</div>
		
		<div class="left">
			<form action="" method="post">
				<input type="submit" name="action" value="Return To Users">
			</form>
		</div>
		
		<div class="left"><a href="..">Return to CMS home</a></div>
		
		<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>