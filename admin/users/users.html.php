<!-- This is the HTML form used for DISPLAYING a list of USER information-->
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
				width: auto;
			}
		</style>
	</head>
	<body>
		<h1>Manage Users</h1>
		
		<div class="left">
			<?php if(isset($_SESSION['UserManagementFeedbackMessage'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['UserManagementFeedbackMessage']); ?></b></span>
				<?php unset($_SESSION['UserManagementFeedbackMessage']); ?>
			<?php endif; ?>
		</div>

		<div class="left">
			<form action="?add" method="post">
				<input type="submit" name="action" value="Create User">
			</form>
		</div>
		<div class="right">
			<form action="" method="post">
				<?php if(isset($_SESSION['usersEnableDelete']) AND $_SESSION['usersEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</form>
		</div>		
		
		<?php if($rowNum>0) :?>
			<table>
				<caption>Activated Users</caption>
				<tr>
					<th colspan="3">User Information</th>
					<th>Website</th>
					<th colspan="2">Default Booking</th>
					<th>Works For</th>
					<th colspan="3">Dates</th>
					<th colspan="2">Alter User</th>
				</tr>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email <a style="color: white" href="?getEmails">(Click to Export)</a></th>
					<th>Access</th>
					<th>Name</th>
					<th>Description</th>
					<th>Company</th>
					<th>Created</th>
					<th>Last Active</th>
					<th>Reduce Access At</th>
					<th>Edit</th>
					<th>Delete</th>
				</tr>
				<?php foreach ($users as $user): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($user['firstname']); ?></td>
							<td><?php htmlout($user['lastname']); ?></td>
							<td><?php htmlout($user['email']); ?></td>
							<td><?php htmlout($user['accessname']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($user['displayname']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($user['bookingdescription']); ?></td>
							<td style="white-space: pre-wrap;"><?php htmlout($user['worksfor']); ?></td>
							<td><?php htmlout($user['datecreated']); ?></td>
							<td><?php htmlout($user['lastactive']); ?></td>
							<?php if($user['reduceaccess'] == null) :?>
								<td><p>No Date Set</p></td>
							<?php elseif($user['reduceaccess'] != null) : ?>
								<td>
									<p><?php htmlout($user['reduceaccess']); ?></p>
									<input type="submit" name="action" value="Cancel Date">
								</td>								
							<?php endif; ?>
							<td><input type="submit" name="action" value="Edit"></td>
							<td>
								<?php if(isset($_SESSION['usersEnableDelete']) AND $_SESSION['usersEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="id" value="<?php htmlout($user['id']); ?>">
							<input type="hidden" name="UserInfo" id="UserInfo"
							value="<?php htmlout($user['UserInfo']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
			
			<?php if(isset($inactiveusers)) : ?>
				<table>
					<caption>Unactivated Users</caption>
					<tr>
						<th colspan="3">User Information</th>
						<th>Website</th>
						<th>Date</th>
						<th>Alter User</th>
					</tr>				
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Access</th>
						<th>Created</th>
						<th>Delete</th>
					</tr>
					<?php foreach ($inactiveusers as $user): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($user['firstname']); ?></td>
							<td><?php htmlout($user['lastname']); ?></td>
							<td><?php htmlout($user['email']); ?></td>
							<td><?php htmlout($user['accessname']); ?></td>
							<td><?php htmlout($user['datecreated']); ?></td>
							<td>
								<?php if(isset($_SESSION['usersEnableDelete']) AND $_SESSION['usersEnableDelete']) : ?>
									<input type="submit" name="action" value="Delete">
								<?php else : ?>
									<input type="submit" name="disabled" value="Delete" disabled>
								<?php endif; ?>
							</td>
							<input type="hidden" name="id" value="<?php htmlout($user['id']); ?>">
						</tr>
					</form>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>
			
		<?php else : ?>
			<tr><b>There are no users registered in the database.</b></tr>
		<?php endif; ?>
		
	<div class="left"><a href="..">Return to CMS home</a></div>
		
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
