<!-- This is the HTML form used for DISPLAYING a list of USER information-->
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<script src="/scripts/myFunctions.js"></script>
		<title>Manage Users</title>
		<style>
			label {
				width: auto;
			}
		</style>
	</head>
	<body onload="startTime()">
		<?php include_once $_SERVER['DOCUMENT_ROOT'] .'/includes/admintopnav.html.php'; ?>

		<h1>Manage Users</h1>

		<div class="left">
			<?php if(isSet($_SESSION['UserManagementFeedbackMessage'])) : ?>
				<span><b class="feedback"><?php htmlout($_SESSION['UserManagementFeedbackMessage']); ?></b></span>
				<?php unset($_SESSION['UserManagementFeedbackMessage']); ?>
			<?php endif; ?>
		</div>

		<div class="left">
			<form action="?add" method="post">
				<input type="submit" name="action" value="Create User">
			</form>
		</div>

		<?php if($rowNum>0) :?>
			<table class="myTable">
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
					<form method="post">
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
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="UserID" value="<?php htmlout($user['UserID']); ?>">
							<input type="hidden" name="UserInfo" value="<?php htmlout($user['UserInfo']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>

			<?php if(isSet($blockedUsers)) : ?>
			<table class="myTable">
				<caption>Blocked Users (Cannot log in)</caption>
				<tr>
					<th colspan="3">User Information</th>
					<th>Website</th>
					<th colspan="2">Default Booking</th>
					<th>Works For</th>
					<th colspan="3">Dates</th>
					<th colspan="3">Alter User</th>
				</tr>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Access</th>
					<th>Name</th>
					<th>Description</th>
					<th>Company</th>
					<th>Created</th>
					<th>Last Active</th>
					<th>Reduce Access At</th>
					<th>Edit</th>
					<th>Re-Activate</th>
					<th>Delete</th>
				</tr>
				<?php foreach ($blockedUsers as $user): ?>
					<form method="post">
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
							<td><input type="submit" name="action" value="Re-Activate"></td>
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="UserID" value="<?php htmlout($user['UserID']); ?>">
							<input type="hidden" name="UserInfo" value="<?php htmlout($user['UserInfo']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
			<?php endif; ?>

			<?php if(isSet($inactiveusers)) : ?>
				<table class="myTable">
					<caption>Unactivated Users</caption>
					<tr>
						<th colspan="3">User Information</th>
						<th>Website</th>
						<th>Date</th>
						<th colspan="2">Alter User</th>
					</tr>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Access</th>
						<th>Created</th>
						<th>Activate</th>
						<th>Delete</th>
					</tr>
					<?php foreach ($inactiveusers as $user): ?>
					<form method="post">
						<tr>
							<td><?php htmlout($user['firstname']); ?></td>
							<td><?php htmlout($user['lastname']); ?></td>
							<td><?php htmlout($user['email']); ?></td>
							<td><?php htmlout($user['accessname']); ?></td>
							<td><?php htmlout($user['datecreated']); ?></td>
							<td><input type="submit" name="action" value="Activate"></td>
							<td><input type="submit" name="action" value="Delete"></td>
							<input type="hidden" name="UserID" value="<?php htmlout($user['UserID']); ?>">
							<input type="hidden" name="UserInfo" value="<?php htmlout($user['UserInfo']); ?>">
						</tr>
					</form>
					<?php endforeach; ?>
				</table>
			<?php endif; ?>

		<?php else : ?>
			<tr><b>There are no users registered in the database.</b></tr>
		<?php endif; ?>

	<div class="left"><a href="/admin/">Return to CMS home</a></div>
	</body>
</html>
