<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "myDBPDO";

// 	Data Types for each column in table:
//	Text:
//		CHAR(size) -- Fixed length. letters/numbers/special characters). up to 255 characters
//		VARCHAR(size) -- A CHAR that converts to TEXT if more than 255 characters are applied
//		TINYTEXT	-- String up to 255 characters
//		Text	-- String up to 65,535 characters
//	Number:
//		TINYINT(size) -- -128 to 127 or 0 to 255 (unsigned). size sets max number
//		SMALLINT(size) -- -32768 to 32767 or 0 to 65535 (unsigned). size sets max number
//		FLOAT(size,d) -- A small number with a floating decimal point. d sets max digits after decimal point
//	Date/Time:
//		DATE() -- A date. Format YYYY-MM-DD
//		DATETIME() -- A date and time combination. Format YYYY-MM-DD HH:MI:SS
//		TIMESTAMP() -- Format YYYY-MM-DD HH:MI:SS. Sets itself automatically in an INSERT/UPDATE query
//		TIME() -- A time. Format HH:MI:SS
//		YEAR() -- A year in two-digit or four-digit format. 1901 to 2155 or 70 to 69 (1970 to 2069)
// 	Optional attributes for each column
//		NOT NULL -- Each row MUST CONTAIN a value. Null values are NOT ALLOWED
//		DEFAULT -- Set a default value that is added when no other value is passed
//		UNSIGNED -- For int data types
//		AUTO INCREMENT -- MySQL automatically increases the value of the field by 1 for new records
//		PRIMARY KEY -- Used to uniquely identify the rows in a table.



try {
	//	Create connection
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	//	set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	// sql to create table
	$sql = "CREATE TABLE MyGuests (
	id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	firstname VARCHAR(30) NOT NULL,
	lastname VARCHAR(30) NOT NULL,
	email VARCHAR(50),
	reg_date TIMESTAMP
	)";
	
	// use exec() because no results are returned
	$conn->exec($sql);
	echo "Table MyGuests created successfully";
	} 
catch(PDOException $e)
	{
	echo $sql . "<br>" . $e->getMessage();
	}
		
//Close connection
$conn->null;

?>