<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>System Log</title>
	</head>
	<body>
		<p>Here are all the logs created by the system:</p>
		<?php foreach ($log as $row): ?>
			<blockquote>
				<p><?php echo htmlspecialchars($row, ENT_QUOTES, 'UTF-8');
					?>
				</p>
			</blockquote>
		<?php endforeach; ?>
	</body>
</html>