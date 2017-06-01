<!-- This is the HTML form used for DISPLAYING a list of USER information-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<style>
			#usertable {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#usertable th {
				padding: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
				border: 1px solid #ddd;
			}
			
			#usertable tr {
				padding: 8px;
				text-align: left;
				border-bottom: 1px solid #ddd;
			}
			
			#usertable td {
				padding: 8px;
				text-align: left;
				border: 1px solid #ddd;
			}

			#usertable tr:hover{background-color:#ddd;}
			
			#usertable tr:nth-child(even) {background-color: #f2f2f2;}
			
			#usertable caption {
				padding: 8px;
				font-size: 300%;
			}
		</style>
		<title>Manage Users</title>
	</head>
	<body>
		<h1>Manage Users</h1>
		<?php if(isset($_SESSION['UserManagementFeedbackMessage'])) : ?>
			<p><b><?php htmlout($_SESSION['UserManagementFeedbackMessage']); ?></b></p>
			<?php unset($_SESSION['UserManagementFeedbackMessage']); ?>
		<?php endif; ?>
		<form action="" method="post">
			<div>
				<?php if(isset($_SESSION['usersEnableDelete']) AND $_SESSION['usersEnableDelete']) : ?>
					<input type="submit" name="action" value="Disable Delete">
				<?php else : ?>
					<input type="submit" name="action" value="Enable Delete">
				<?php endif; ?>
			</div>
		</form>
		<?php if(isset($_SESSION['UserEmailsToBeDisplayed'])) : ?>
			<form action="" method="post">
				<div>
					<label>Get All Visible Emails (<?php htmlout(sizeOf($_SESSION['UserEmailsToBeDisplayed'])); ?>) In List Form To Copy: </label>
					<input type="submit" name="action" value="Get Emails">
				</div>
			</form>
		<?php endif; ?>
		<?php if($rowNum>0) :?>
			<form action="?add" method="post">
				<div>
					<input type="submit" name="action" value="Create User">
				</div>
			</form>
			<table id="usertable">
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
					<th>Email</th>
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
							<td><?php htmlout($user['displayname']); ?></td>
							<td><?php htmlout($user['bookingdescription']); ?></td>
							<td><?php htmlout($user['worksfor']); ?></td>
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
							<input type="hidden" name="id" value="<?php echo $user['id']; ?>">
							<input type="hidden" name="UserInfo" id="UserInfo"
							value="<?php htmlout($user['UserInfo']); ?>">
						</tr>
					</form>
				<?php endforeach; ?>
			</table>
			<table id="usertable">
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
						<input type="hidden" name="id" value="<?php echo $user['id']; ?>">
					</tr>
				</form>
				<?php endforeach; ?>
			</table>
		<?php else : ?>
			<tr><b>There are no users registered in the database.</b></tr>
			<tr>
				<form action="?add" method="post">
					<div>
						<input type="submit" name="action" value="Create User">
					</div>
				</form>
			</tr>
		<?php endif; ?>
		<p><a href="..">Return to CMS home</a></p>
	<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/logout.inc.html.php'; ?>
	</body>
</html>
