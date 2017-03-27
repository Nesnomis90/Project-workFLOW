<?php
// Database information we use in the code to connect to it
//$dbengine 	= 'mysql';
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '5Bdp32LAHYQ8AemvQM9P');
define('DB_NAME', 'meetingflow');

// A global array to keep track of log events that occur before
// the log event table has been created.
global $logEventArray;
$logEventArray = array();

// Function to connect to server and create our wanted database
function create_db()
{
	$pdo = null;
	global $logEventArray;

	try {
	//	Create connection without an existing database
	$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASSWORD);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	if(!dbExists($pdo,DB_NAME)){
		// Creating the SQL query to make the database
		$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;

		//Executing the SQL query
		$pdo->exec($sql);
		$output = 'Created database: ' . DB_NAME . '<br />';
		
		//	Add the creation to log event
		$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Database Created'), 'Database " . DB_NAME . " was created automatically by the PHP script. This should only occur once, at the very start of the log event.')";
		$logEventArray[] = $sqlLog;

	} else {
		$output = 'Database: ' . DB_NAME . ' already exists.<br />';
	}
	
	include 'output.html.php';
	
	//Closing the connection
	$pdo = null;
	
	} 
catch(PDOException $e)
	{
	$error = 'Unable to create the database.<br />';
	include 'error.html.php';
	$pdo = null;
	die("DB ERROR: " . $e->getMessage());
	}	
}

// Function to connect to an existing database
function connect_to_db()
{
	$pdo = null;
	
	try {
	//	Create connection with an existing database
	$pdo = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
	//	set the PDO error mode to exception
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
	$output = "Succesfully connected to database: " . DB_NAME . "<br />";
	include 'output.html.php';
	
	return $pdo; //Return the active connection

	} 
catch(PDOException $e)
	{
	$error = 'Unable to connect to the database.<br />';
	include 'error.html.php';
	$pdo = null;	// Close connection
	die("DB ERROR: " . $e->getMessage());

	}
}

// Function to see if database exists
function dbExists($pdo, $databaseName){
	try{
		// Run a SHOW DATABASES query on the selected database
		$result = $pdo->query("SHOW DATABASES LIKE '$databaseName'");
		// The result will either be an empty set, if it doesn't exist. Or a single row, if it does exist.
		$row = $result->rowCount();
		if ($row > 0){
			return TRUE;
		} else {
			return FALSE;
		}
	} 
	catch (Exception $e)
	{
		return FALSE;
	}
}
// Function to fill in default values for the Access Level table
function fillAccessLevel($pdo){
	try
	{
		//Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Has full access to all website pages, company information and user information.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Company Owner', 'Full company information and management.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Meeting Room', 'These are special accounts used to handle booking code login.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('test', 'test')");
		
		// Commit the transaction
		$pdo->commit();
	} 
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		echo "Error: " . $e->getMessage() . "<br />";
	}
}

// Function to fill in default values for the Company Position table
function fillCompanyPosition($pdo){
	try
	{
		// Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `companyposition`(`name`, `description`) VALUES ('Owner', 'User can manage company information and add/remove users connected to the company.')");
		$pdo->exec("INSERT INTO `companyposition`(`name`, `description`) VALUES ('Employee', 'User can view company information and connected users.')");
		
		// Commit the transaction
		$pdo->commit();
		
	}
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		echo "Error: " . $e->getMessage() . "<br />";
	}
}

// Function to fill in default values for the Log Action table
function fillLogAction($pdo){
	try
	{
		// Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Created','The referenced user just registered an account.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Removed','A user account has been removed. See log description for more information.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Created','The referenced user created a new meeting room booking.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Cancelled','The referenced user cancelled a meeting room booking.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Completed','The referenced booking has been completed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Created','The referenced user just created the referenced company.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Removed','A company has been removed. See log description for more information.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Database Created','The database we are using right now just got created.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Table Created','A table in the database was created.')");
		
		// Commit the transaction
		$pdo->commit();
		
	}
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		echo "Error: " . $e->getMessage() . "<br />";
	}
}

// Function to see if database table exists
function tableExists($pdo, $table) {
	try {
		// Run a SELECT query on the selected table
		$result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
		// The result will either be FALSE (no table found) or a PDOSTATEMENT Object (table found)
		// !== returns TRUE if $result is not equal to FALSE, or if they are not the same type 
		return $result !== FALSE;
	} 
	catch (Exception $e) 
	{
		// If there's an exception, then the table isn't found.
		return FALSE;
	}
}

// Function to create the tables for our database if they don't already exist
function create_tables()
{
	try
	{
		// Log event array
		global $logEventArray;
		// Timers to check how long it takes to execute these actions
		$time = 0;
		$prevtime = 0;
		$totaltime = 0;
		
		//	Connect to the database so we can create tables in it
		$conn = connect_to_db();
		
		// The SQL queries of the tables we need to create
			//Access Level
		$table = 'accesslevel';
		
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
			  `AccessID` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `AccessName` varchar(255) DEFAULT NULL,
			  `Description` text,
			  PRIMARY KEY (`AccessID`),
			  UNIQUE KEY `AccessName_UNIQUE` (`AccessName`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8");
			
			// Fill default values
			fillAccessLevel($conn);
			
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;
			
			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating and filling table ' . $table. ':</b> ' . $time . 's<br />';	
		
		} else {
			//If the table exists, but for some reason has no values in it, then fill it
			$result = $conn->query("SELECT `AccessName` FROM `accesslevel`");
			$row = $result->rowCount();
			if($row==0){
				// No values in the table. Insert the needed values.
				fillAccessLevel($conn);
				
				echo "<b>Inserted default values into $table.</b> <br />";
		
				$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
				$time = $totaltime - $prevtime;
				$prevtime = $totaltime;
				echo '<b>Execution time for filling table ' . $table. ':</b> ' . $time . 's<br />';	
			} else {
				// Table already has (some) values in it
				echo "<b>Table $table already had values in it.</b> <br />";
			}
			
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			//User accounts
		$table = 'user';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `email` varchar(255) NOT NULL,
						  `password` char(64) NOT NULL,
						  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `firstName` varchar(255) DEFAULT NULL,
						  `lastName` varchar(255) DEFAULT NULL,
						  `displayName` varchar(255) DEFAULT NULL,
						  `bookingDescription` text,
						  `bookingCode` varchar(10) DEFAULT NULL,
						  `tempPassword` char(64) DEFAULT NULL,
						  `dateRequested` timestamp NULL DEFAULT NULL,
						  `AccessID` int(10) unsigned NOT NULL,
						  `lastActivity` timestamp NULL DEFAULT NULL,
						  `isActive` tinyint(1) NOT NULL DEFAULT '0',
						  `activationCode` char(64) NOT NULL,
						  PRIMARY KEY (`userID`),
						  UNIQUE KEY `email_UNIQUE` (`email`),
						  UNIQUE KEY `bookingCode_UNIQUE` (`bookingCode`),
						  KEY `FK_AccessID_idx` (`AccessID`),
						  CONSTRAINT `FK_AccessID` FOREIGN KEY (`AccessID`) REFERENCES `accesslevel` (`AccessID`) ON DELETE NO ACTION ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						
								
			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}
		
			//Meeting Room
		$table = 'meetingroom';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{		
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `meetingRoomID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL DEFAULT 'No name set',
						  `capacity` tinyint(3) unsigned NOT NULL DEFAULT '1',
						  `description` text,
						  `location` varchar(255) DEFAULT NULL,
						  PRIMARY KEY (`meetingRoomID`)
						) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						
						
			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			//Company
		$table = 'company';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `removeAtDate` date DEFAULT NULL,
						  `bookingTimeUsedThisMonth` smallint(5) unsigned NOT NULL DEFAULT '0',
						  PRIMARY KEY (`CompanyID`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
		
			//Booking
		$table = 'booking';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `bookingID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `meetingRoomID` int(10) unsigned NOT NULL,
						  `userID` int(10) unsigned NOT NULL,
						  `companyID` int(10) unsigned DEFAULT NULL,
						  `displayName` varchar(255) DEFAULT NULL,
						  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `dateTimeCancelled` timestamp NULL DEFAULT NULL,
						  `startDateTime` datetime NOT NULL,
						  `endDateTime` datetime NOT NULL,
						  `actualEndDateTime` datetime DEFAULT NULL,
						  `description` text,
						  `cancellationCode` char(64) NOT NULL,
						  PRIMARY KEY (`bookingID`),
						  KEY `FK_MeetingRoomID_idx` (`meetingRoomID`),
						  KEY `FK_UserID_idx` (`userID`),
						  KEY `FK_UserID2_idx` (`userID`),
						  KEY `FK_CompanyID3_idx` (`companyID`),
						  CONSTRAINT `FK_CompanyID3` FOREIGN KEY (`companyID`) REFERENCES `company` (`CompanyID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID` FOREIGN KEY (`meetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE NO ACTION ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE NO ACTION ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
				
			//Company Position
		$table = 'companyposition';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `PositionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `description` text NOT NULL,
						  PRIMARY KEY (`PositionID`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
			
			//Insert default values for the Company Position table
			fillCompanyPosition($conn);
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating and filling table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			//If the table exists, but for some reason has no values in it, then fill it
			$result = $conn->query("SELECT `name` FROM `companyposition`");
			$row = $result->rowCount();
			if($row == 0){
				
				fillCompanyPosition($conn);
				
				echo "<b>Inserted default values into $table.</b> <br />";
		
				$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
				$time = $totaltime - $prevtime;
				$prevtime = $totaltime;
				echo '<b>Execution time for filling table ' . $table. ':</b> ' . $time . 's<br />';	
			} else {
				// Table already has (some) values in it
				echo "<b>Table $table already had values in it.</b> <br />";
			}
		
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
		
			//Employee
		$table = 'employee';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL,
						  `UserID` int(10) unsigned NOT NULL,
						  `startDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `PositionID` int(10) unsigned NOT NULL,
						  PRIMARY KEY (`UserID`,`CompanyID`),
						  KEY `FK_CompanyID_idx` (`CompanyID`),
						  KEY `FK_PositionID_idx` (`PositionID`),
						  CONSTRAINT `FK_CompanyID` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';			
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
		
			//Equipment for meeting rooms
		$table = 'equipment';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `EquipmentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `description` text NOT NULL,
						  PRIMARY KEY (`EquipmentID`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';		
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
		
			//Log Action
		$table = 'logaction';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `actionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `description` varchar(255) NOT NULL,
						  PRIMARY KEY (`actionID`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
			
			//Fill in default values for table Log Action
			fillLogAction($conn);
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';		
		} else { 
			//If the table exists, but for some reason has no values in it, then fill it
			$result = $conn->query("SELECT `name` FROM `logaction`");
			$row = $result->rowCount();
			if($row==0){
				// No values in the table. Insert the needed values.
				fillLogAction($conn);
				
				echo "<b>Inserted default values into $table.</b> <br />";
		
				$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
				$time = $totaltime - $prevtime;
				$prevtime = $totaltime;
				echo '<b>Execution time for filling table ' . $table. ':</b> ' . $time . 's<br />';	
			} else {
				// Table already has (some) values in it
				echo "<b>Table $table already had values in it.</b> <br />";
			}		
		
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}
		
			//Equipment in meeting rooms
		$table = 'roomequipment';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `EquipmentID` int(10) unsigned NOT NULL,
						  `MeetingRoomID` int(10) unsigned NOT NULL,
						  `amount` tinyint(3) unsigned NOT NULL DEFAULT '1',
						  PRIMARY KEY (`EquipmentID`,`MeetingRoomID`),
						  KEY `FK_MeetingRoomID_idx` (`MeetingRoomID`),
						  CONSTRAINT `FK_EquipmentID` FOREIGN KEY (`EquipmentID`) REFERENCES `equipment` (`EquipmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID2` FOREIGN KEY (`MeetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';		
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}
		
			//Web Session information
		$table = 'websession';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `sessionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `userID` int(10) unsigned DEFAULT NULL,
						  `dateTimeStart` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `IP` char(64) NOT NULL,
						  PRIMARY KEY (`sessionID`),
						  KEY `FK_UserID4_idx` (`userID`),
						  CONSTRAINT `FK_UserID4` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
						
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}		
				
			//Log Event
		$table = 'logevent';
		//Check if table already exists
		if (!tableExists($conn, $table))
		{
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `logID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `actionID` int(10) unsigned DEFAULT NULL,
						  `userID` int(10) unsigned DEFAULT NULL,
						  `companyID` int(10) unsigned DEFAULT NULL,
						  `bookingID` int(10) unsigned DEFAULT NULL,
						  `meetingRoomID` int(10) unsigned DEFAULT NULL,
						  `equipmentID` int(10) unsigned DEFAULT NULL,
						  `sessionID` int(10) unsigned DEFAULT NULL,
						  `description` text,
						  `logDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`logID`),
						  KEY `FK_UserID3_idx` (`userID`),
						  KEY `FK_ActionID_idx` (`actionID`),
						  KEY `FK_CompanyID2_idx` (`companyID`),
						  KEY `FK_BookingID_idx` (`bookingID`),
						  KEY `FK_MeetingRoomID3_idx` (`meetingRoomID`),
						  KEY `FK_EquipmentID2_idx` (`equipmentID`),
						  KEY `FK_SessionID_idx` (`sessionID`),
						  CONSTRAINT `FK_ActionID` FOREIGN KEY (`actionID`) REFERENCES `logaction` (`actionID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_BookingID` FOREIGN KEY (`bookingID`) REFERENCES `booking` (`bookingID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_CompanyID2` FOREIGN KEY (`companyID`) REFERENCES `company` (`CompanyID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_EquipmentID2` FOREIGN KEY (`equipmentID`) REFERENCES `equipment` (`EquipmentID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID3` FOREIGN KEY (`meetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_SessionID` FOREIGN KEY (`sessionID`) REFERENCES `websession` (`sessionID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID3` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");
												
			//	Add the creation to log event
			$sqlLog = "INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Table Created'), 'The table $table was created automatically by the PHP script. This should only occur once, at the very start of the log events.')";
			$logEventArray[] = $sqlLog;						

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';	
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}	
		
		// Store the saved up log events in the now created database
		if(tableExists($conn,$table)){
			if(count($logEventArray)>0){
				foreach($logEventArray as $sqlStatement){
					$conn->exec($sqlStatement);
					//echo $sqlStatement . " <br />";
				}
				$logEventArray = array(); //reinitialize it i.e. make it empty	
				
				$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
				$time = $totaltime - $prevtime;
				$prevtime = $totaltime;
				echo '<b>Execution time for filling in Log Event with database/table creation:</b> ' . $time . 's<br />';	
			}else{
				echo "<b>There was nothing to add into Log Event</b><br />";
			}
		}else{
			echo "<b>$table does not exist so couldn't start log event procedure</b><br />";
		}
		
		//Calculating total time spent checking if tables exist and/or creating them.
		echo '<b>Total Execution Time for creating all tables:</b> ' . $totaltime . 's.<br />';
		
		//Close connection
		$conn = null;
	}
	catch(PDOException $e)
	{
		$error = 'Failed to create tables for ' . DB_NAME . "<br />";
		include 'error.html.php';
		
		$conn = null;
		die("DB ERROR: " . $e->getMessage());
	}
}
?>