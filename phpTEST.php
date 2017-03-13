<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTDE/xhtml1-strict.dtd">
<hmtl xlmsn="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<?php
	require('phpDBvars.php');
	// set the default timezone to use.
	date_default_timezone_set('CET');
	
	//Retrieve date and time
		//Date formatting source http://php.net/manual/en/function.date.php
	$date = date('l, F dS Y.');	//Output format: month(text), day(digit) number suffix(st,nd,rd,th) Year(digit).
								//e.g.: Monday, March 13th 2017.
	$time = date('H:i:s');		//Output format: Hours:minutes:seconds
								//e.g.: 10:56:42
	?>

	<head>
		<title>Today&rsquo;s Date</title>
		<meta http-equiv="content-type"
		content="text/html; charset=utf-8"/>
	</head>
	<body>
		<p>This is test to see if our Apache 2.4 and PHP script is communicating.</p>
		<p>Today&rsquo;s date (according to this web server) is <?php echo $date ?></p>
		<p>The time is currently <?php echo $time ?>.</p>
		<p>The database name is <?php echo $dbname ?></p>
	</body>
</html>
