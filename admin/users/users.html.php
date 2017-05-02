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
		<?php if($rowNum>0) :?>
			<form action="?add" method="post">
				<div>
					<input type="submit" name="action" value="Create User">
				</div>
			</form>
			<table id= "usertable">
				<caption>Activated Users</caption>
				<tr>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Email</th>
					<th>Access</th>
					<th>Default Booking Name</th>
					<th>Default Booking Description</th>
					<th>Works for</th>
					<th>Date Created</th>
					<th>Last Active Date</th>
					<th>Edit User</th>
					<th>Delete User</th>
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
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Access</th>
						<th>Date Created</th>
						<th>Delete User</th>
					</tr>
					<?php foreach ($inactiveusers as $user): ?>
					<form action="" method="post">
						<tr>
							<td><?php htmlout($user['firstname']); ?></td>
							<td><?php htmlout($user['lastname']); ?></td>
							<td><?php htmlout($user['email']); ?></td>
							<td><?php htmlout($user['accessname']); ?></td>
							<td><?php htmlout($user['datecreated']); ?></td>
							<td><input type="submit" name="action" value="Delete"></td>
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
		<?php include '../logout.inc.html.php'; ?>
	</body>
</html>
