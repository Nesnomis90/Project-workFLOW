<?php
$pdo = null;
function connect_to_db()
{
	$dbengine 	= 'mysql';
	$dbhost 	= 'localhost';
	$dbuser		= 'root';
	$dbpassword = '5Bdp32LAHYQ8AemvQM9P';
	$dbname		= 'MeetingFLOW';
	
	try {
	print("starting connection process.");
	//	Create connection
	$pdo = new PDO("".$dbengine.":host=$dbhost; dbname=$dbname", $dbuser, $dbpassword);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	print("new PDO created");
	
	return $pdo;
	
	
	//$sql = "CREATE DATABASE $dbname";
	// use exec() because no results are returned
	//$conn->exec($sql);
	//echo "Database created successfully<br>";
	} 
catch(PDOException $e)
	{
	//echo $sql . "<br>" . $e->getMessage();
	$e->getMessage();
	}
}


	
//Close connection
//$conn->null;
?>