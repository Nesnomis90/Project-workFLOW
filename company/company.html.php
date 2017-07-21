<!-- This is the HTML form used for DISPLAYING COMPANY information to connected users-->
<?php include_once $_SERVER['DOCUMENT_ROOT'] .
 '/includes/helpers.inc.php'; ?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" type="text/css" href="/CSS/myCSS.css">
		<?php if($numberOfCompanies > 1) : ?>
			<title>Manage Companies</title>
		<?php elseif($numberOfCompanies == 1) : ?>
			<title>Manage Company</title>
		<?php elseif($numberOfCompanies == 0) : ?>
			<title>Join A Company</title>
		<?php endif; ?>
	</head>
	<body>
		<fieldset>
			<?php if($numberOfCompanies > 1) : ?>
				<legend>Manage Companies</legend>
			<?php elseif($numberOfCompanies == 1) : ?>
				<legend>>Manage Company</legend>
			<?php elseif($numberOfCompanies == 0) : ?>
				<legend>Join A Company</legend>
			<?php endif; ?>
			
			
		</fieldset>
	</body>
</html>