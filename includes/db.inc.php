<?php
// This file holds all variables needed to connect to our database
// It also has functions to
// 						a) Create the database if it does not exist create_db();
//						b) Create the tables we need if they do not exist create_tables();
//						c) Connect to the database to use it for other things connect_to_db();
// a) and b) are run automatically when this file is included
// which means it will always try to make sure the database and its tables exist

//Libraries, functions etc. to include
require_once 'variables.inc.php';
require_once 'access.inc.php';
require_once 'htmlout.inc.php';

// A global array to keep track of log events that occur before
// the log event table has been created.
global $logEventArray;
$logEventArray = array();

// Function to connect to server and create our wanted database
function create_db(){
	$pdo = null;
	global $logEventArray;

	try 
	{
		//	Create connection without an existing database
		$pdo = new PDO("mysql:host=".DB_HOST, DB_USER, DB_PASSWORD);
		//	set the PDO error mode to exception
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->exec('SET NAMES "utf8"');
		
		if(!dbExists($pdo,DB_NAME)){
			// Creating the SQL query to make the database
			$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;

			//Executing the SQL query
			$pdo->exec($sql);
			$output = 'Created database: ' . DB_NAME . '<br />';

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
											SELECT 	`actionID` 
											FROM 	`logaction` 
											WHERE 	`name` = "Database Created"
										), 
									"Database ' . DB_NAME . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log event."
									)';
			$logEventArray[] = $sqlLog;

		} else {
			$output = '<b>Database: ' . DB_NAME . ' already exists.</b><br />';
		}

		include $_SERVER['DOCUMENT_ROOT'] . '/includes/output.html.php';

		//Closing the connection
		$pdo = null;
	} 
	catch(PDOException $e)
	{
		$error = 'Unable to create the database: ' . $e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to connect to an existing database
function connect_to_db(){
	$pdo = null;

	try {
		//	Create connection with an existing database
		$pdo = new PDO("mysql:host=".DB_HOST.";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
		//	set the PDO error mode to exception
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->exec('SET NAMES "utf8"');

		return $pdo; //Return the active connection
	} 
	catch(PDOException $e)
	{
		$error = 'Unable to connect to the database' . $e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;	// Close connection
		exit();
	}
}

// Function to see if database exists
function dbExists($pdo, $databaseName){
	try
	{
		// Check if the database exists by counting schemas with that name
		$return = $pdo->query("	SELECT 	COUNT(*) 
								FROM 	information_schema.SCHEMATA
								WHERE	`SCHEMA_NAME` = '" . $databaseName . "'");
		$rowCount = $return->fetchColumn();
		if ($rowCount > 0){
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

// Function to fill in default values for the company subscription (credits) table
function fillCredits($pdo){
	try
	{
		//Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `credits`
					SET			`name` = 'Default',
								`description` = 'Set by default for all new companies.',
								`minuteAmount` = 0,
								`monthlyPrice` = 0,
								`overCreditHourPrice` = 0");

		// Commit the transaction
		$pdo->commit();
	} 
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		$error = 'Encountered an error while trying to insert default values into table credits: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to fill in default values for the Access Level table
function fillAccessLevel($pdo){
	try
	{
		//Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Full access to all website pages, company information and user information.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.')");
		$pdo->exec("INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.')");

		// Commit the transaction
		$pdo->commit();
	} 
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		$error = 'Encountered an error while trying to insert default values into table accesslevel: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
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
		$pdo = null;
		$error = 'Encountered an error while trying to insert default values into table companyposition: ' .
			$e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Function to fill in default values for the Log Action table
function fillLogAction($pdo){
	try
	{
		// Insert the needed values.
		$pdo->beginTransaction();
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Created','The referenced user just registered an account.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Activated','The referenced user just activated their account.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Removed','The referenced user account has been removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Created','The referenced user created a new meeting room booking.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Cancelled','The referenced user cancelled a meeting room booking.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Completed','The referenced booking has been completed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Removed', 'The referenced booking was removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Created','The referenced user just created the referenced company.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Removed','The referenced company has been removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Merged','The referenced companies has been merged together.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Credits Changed', 'The referenced company had its credits information changed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Credits Added', 'The referenced Credits was added.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Credits Removed', 'The referenced Credits was removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Database Created','The database we are using right now just got created.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Table Created','A table in the database was created.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Employee Added', 'The referenced user was given the referenced position in the referenced company.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Employee Removed', 'The referenced user was removed from the referenced company.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Employee Transferred', 'The referenced user has had its employee status (and booking history) transferred from the old company to the new company.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Equipment Added','The referenced equipment was added.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Equipment Removed','The referenced equipment was removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Event Created', 'The referenced event was created for the referenced week(s).')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Event Removed', 'The referenced event and all its scheduled week(s) were removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Meeting Room Added', 'The referenced meeting room was added.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Meeting Room Removed', 'The referenced meeting room was removed.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Room Equipment Added', 'The referenced equipment was added into the referenced meeting room with the referenced amount.')");
		$pdo->exec("INSERT INTO `logaction`(`name`,`description`) VALUES ('Room Equipment Removed', 'The referenced equipment was removed from the referenced meeting room.')");

		// Commit the transaction
		$pdo->commit();

	}
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$pdo->rollback();
		$pdo = null;
		$error = 'Encountered an error while trying to insert default values into table logaction: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Function to fill in default values for the user table
function fillUser($pdo){
	try
	{
		// Get/set a host email
		$email = "admin@" . $_SERVER['HTTP_HOST'];

		// Create a default password
		$defaultPassword = "admin";
		$password = hashPassword($defaultPassword);

		// Insert the needed values.
		$sql = "INSERT INTO	`user`
				SET			`email` = :email,
							`password` = :password,
							`AccessID` = (
											SELECT 	`AccessID`
											FROM	`accesslevel`
											WHERE	`AccessName` = 'Admin'
											LIMIT 	1
										),
							`isActive` = 1,
							`sendAdminEmail` = 1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->bindValue(':password', $password);
		$s->execute();
	} 
	catch (PDOException $e)
	{
		//	Cancels the transaction from going through if something went wrong.
		$error = 'Encountered an error while trying to insert default values into table credits: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to see if database table exists
function tableExists($pdo, $table){
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
function create_tables(){
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
		if (!tableExists($conn, $table)){
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
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating and filling table ' . $table. ':</b> ' . $time . 's<br />';

		} else {
			//If the table exists, but for some reason has no values in it, then fill it
			$return = $conn->query("SELECT 	COUNT(*) 
									FROM 	`accesslevel`");
			$rowCount = $return->fetchColumn();
			if($rowCount == 0){
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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `AccessID` int(10) unsigned NOT NULL,
						  `email` varchar(255) NOT NULL,
						  `password` char(64) NOT NULL,
						  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `firstName` varchar(255) DEFAULT NULL,
						  `lastName` varchar(255) DEFAULT NULL,
						  `displayName` varchar(255) DEFAULT NULL,
						  `bookingDescription` text,
						  `bookingCode` char(64) DEFAULT NULL,
						  `dateRequested` timestamp NULL DEFAULT NULL,
						  `reduceAccessAtDate` date DEFAULT NULL,
						  `lastCodeUpdate` date DEFAULT NULL,
						  `lastActivity` timestamp NULL DEFAULT NULL,
						  `isActive` tinyint(1) unsigned NOT NULL DEFAULT '0',
						  `loginBlocked` tinyint(1) unsigned NOT NULL DEFAULT '0',
						  `timeoutAmount` tinyint(2) unsigned NOT NULL DEFAULT '0',
						  `activationCode` char(64) DEFAULT NULL,
						  `resetPasswordCode` char(64) DEFAULT NULL,
						  `sendEmail` tinyint(1) unsigned NOT NULL DEFAULT '1',
						  `sendOwnerEmail` tinyint(1) unsigned NOT NULL DEFAULT '1',
						  `sendAdminEmail` tinyint(1) unsigned NOT NULL DEFAULT '0',
						  PRIMARY KEY (`userID`),
						  UNIQUE KEY `email_UNIQUE` (`email`),
						  UNIQUE KEY `activationCode_UNIQUE` (`activationCode`),
						  UNIQUE KEY `bookingCode_UNIQUE` (`bookingCode`),
						  UNIQUE KEY `resetPasswordCode_UNIQUE` (`resetPasswordCode`),
						  KEY `FK_AccessID_idx` (`AccessID`),
						  CONSTRAINT `FK_AccessID` FOREIGN KEY (`AccessID`) REFERENCES `accesslevel` (`AccessID`) ON DELETE NO ACTION ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//Insert default values for the user table (admin account)
			fillUser($conn);

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			//If the table exists, but for some reason has no values in it, then fill it
			$return = $conn->query("SELECT 	COUNT(*) 
									FROM 	`user`");
			$rowCount = $return->fetchColumn();
			if($rowCount == 0){

				fillUser($conn);

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

			//Meeting Room
		$table = 'meetingroom';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `meetingRoomID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL DEFAULT 'No name set',
						  `capacity` tinyint(3) unsigned NOT NULL DEFAULT '1',
						  `description` text,
						  `location` varchar(255) DEFAULT NULL,
						  `idCode` char(64) NOT NULL,
						  PRIMARY KEY (`meetingRoomID`),
						  UNIQUE KEY `idCode_UNIQUE` (`idCode`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `removeAtDate` date DEFAULT NULL,
						  `isActive` tinyint(1) NOT NULL DEFAULT '0',
						  `prevStartDate` date DEFAULT NULL,
						  `startDate` date DEFAULT NULL,
						  `endDate` date DEFAULT NULL,
						  PRIMARY KEY (`CompanyID`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `bookingID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `meetingRoomID` int(10) unsigned DEFAULT NULL,
						  `userID` int(10) unsigned DEFAULT NULL,
						  `companyID` int(10) unsigned DEFAULT NULL,
						  `displayName` varchar(255) DEFAULT NULL,
						  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `dateTimeCancelled` timestamp NULL DEFAULT NULL,
						  `startDateTime` datetime NOT NULL,
						  `endDateTime` datetime NOT NULL,
						  `actualEndDateTime` datetime DEFAULT NULL,
						  `description` text,
						  `adminNote` text,
						  `cancelMessage` text,
						  `cancelledByUserID` int(10) unsigned DEFAULT NULL,
						  `cancellationCode` char(64) DEFAULT NULL,
						  `emailSent` tinyint(1) NOT NULL DEFAULT '0',
						  `mergeNumber` int(10) unsigned NOT NULL DEFAULT '0',
						  PRIMARY KEY (`bookingID`),
						  UNIQUE KEY `cancellationCode_UNIQUE` (`cancellationCode`),
						  KEY `FK_MeetingRoomID_idx` (`meetingRoomID`),
						  KEY `FK_UserID2_idx` (`userID`),
						  KEY `FK_CompanyID3_idx` (`companyID`),
						  KEY `FK_UserID4_idx` (`cancelledByUserID`),
						  CONSTRAINT `FK_CompanyID3` FOREIGN KEY (`companyID`) REFERENCES `company` (`CompanyID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID` FOREIGN KEY (`meetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID4` FOREIGN KEY (`cancelledByUserID`) REFERENCES `user` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
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
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating and filling table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			//If the table exists, but for some reason has no values in it, then fill it
			$return = $conn->query("SELECT 	COUNT(*) 
									FROM 	`companyposition`");
			$rowCount = $return->fetchColumn();
			if($rowCount == 0){

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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL,
						  `UserID` int(10) unsigned NOT NULL,
						  `PositionID` int(10) unsigned NOT NULL,
						  `startDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `sendEmailOnceOrAlways` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT 'Once = 0\nAlways = 1',
						  PRIMARY KEY (`UserID`,`CompanyID`),
						  KEY `FK_CompanyID_idx` (`CompanyID`),
						  KEY `FK_PositionID_idx` (`PositionID`),
						  CONSTRAINT `FK_CompanyID` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE ON UPDATE CASCADE,
						  CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `EquipmentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `description` text NOT NULL,
						  `datetimeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`EquipmentID`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction`
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
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
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			//If the table exists, but for some reason has no values in it, then fill it
			$return = $conn->query("SELECT 	COUNT(*) 
									FROM 	`logaction`");
			$rowCount = $return->fetchColumn();
			if($rowCount == 0){
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
			// ON DELETE RESTRICT is the same as not having an ON DELETE
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `EquipmentID` int(10) unsigned NOT NULL,
						  `MeetingRoomID` int(10) unsigned NOT NULL,
						  `amount` tinyint(3) unsigned NOT NULL DEFAULT '1',
						  `datetimeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`EquipmentID`,`MeetingRoomID`),
						  KEY `FK_MeetingRoomID_idx` (`MeetingRoomID`),
						  CONSTRAINT `FK_EquipmentID` FOREIGN KEY (`EquipmentID`) REFERENCES `equipment` (`EquipmentID`) ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID2` FOREIGN KEY (`MeetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			//Company booking subscriptions (credits)
		$table = 'credits';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CreditsID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `name` varchar(255) NOT NULL,
						  `description` text NOT NULL,
						  `minuteAmount` smallint(5) unsigned NOT NULL DEFAULT '0',
						  `monthlyPrice` float unsigned NOT NULL DEFAULT '0',
						  `overCreditHourPrice` float unsigned NOT NULL,
						  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `datetimeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`CreditsID`),
						  UNIQUE KEY `name_UNIQUE` (`name`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			// Fill default values
			fillCredits($conn);

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else {
			//If the table exists, but for some reason has no values in it, then fill it
			$return = $conn->query("SELECT 	COUNT(*) 
									FROM 	`credits`");
			$rowCount = $return->fetchColumn();
			if($rowCount == 0){
				// No values in the table. Insert the needed values.
				fillCredits($conn);

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

			// Selected credits per company
		$table = 'companycredits';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL,
						  `CreditsID` int(10) unsigned NOT NULL,
						  `altMinuteAmount` smallint(5) unsigned DEFAULT NULL,
						  `lastModified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  `datetimeAdded` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`CompanyID`,`CreditsID`),
						  KEY `FK_CreditsID_idx` (`CreditsID`),
						  CONSTRAINT `FK_CompanyID4` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE ON UPDATE CASCADE,
						  CONSTRAINT `FK_CreditsID` FOREIGN KEY (`CreditsID`) REFERENCES `credits` (`CreditsID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			// Credits history per billing month per company
		$table = 'companycreditshistory';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `CompanyID` int(10) unsigned NOT NULL,
						  `startDate` date NOT NULL,
						  `endDate` date NOT NULL,
						  `mergeNumber` int(10) unsigned NOT NULL DEFAULT '0',
						  `minuteAmount` smallint(5) unsigned NOT NULL,
						  `monthlyPrice` float unsigned NOT NULL DEFAULT '0',
						  `overCreditHourPrice` float unsigned NOT NULL,
						  `hasBeenBilled` tinyint(1) unsigned NOT NULL DEFAULT '0',
						  `billingDescription` text,
						  PRIMARY KEY (`CompanyID`,`startDate`,`endDate`,`mergeNumber`),
						  CONSTRAINT `FK_CompanyID5` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			// Admin created events
		$table = 'event';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `EventID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `startTime` time NOT NULL,
						  `endTime` time NOT NULL,
						  `name` varchar(255) DEFAULT NULL,
						  `description` text,
						  `daysSelected` varchar(255) NOT NULL,
						  `startDate` date NOT NULL,
						  `lastDate` date NOT NULL,
						  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`EventID`)
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
			$logEventArray[] = $sqlLog;

			$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
			$time = $totaltime - $prevtime;
			$prevtime = $totaltime;
			echo '<b>Execution time for creating table ' . $table. ':</b> ' . $time . 's<br />';
		} else { 
			echo '<b>Table ' . $table. ' already exists</b>.<br />';
		}

			// Actual event datetime overview per room
		$table = 'roomevent';
		//Check if table already exists
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `meetingRoomID` int(10) unsigned NOT NULL,
						  `EventID` int(10) unsigned NOT NULL,
						  `startDateTime` datetime NOT NULL,
						  `endDateTime` datetime NOT NULL,
						  PRIMARY KEY (`meetingRoomID`,`EventID`,`startDateTime`,`endDateTime`),
						  KEY `FK_EventID_idx` (`EventID`),
						  CONSTRAINT `FK_EventID` FOREIGN KEY (`EventID`) REFERENCES `event` (`EventID`) ON DELETE CASCADE ON UPDATE CASCADE,
						  CONSTRAINT `FK_MeetingRoomID4` FOREIGN KEY (`meetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE CASCADE ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
		if (!tableExists($conn, $table)){
			$conn->exec("CREATE TABLE IF NOT EXISTS `$table` (
						  `logID` int(10) unsigned NOT NULL AUTO_INCREMENT,
						  `actionID` int(10) unsigned DEFAULT NULL,
						  `description` text,
						  `logDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						  PRIMARY KEY (`logID`),
						  KEY `FK_ActionID_idx` (`actionID`),
						  CONSTRAINT `FK_ActionID` FOREIGN KEY (`actionID`) REFERENCES `logaction` (`actionID`) ON DELETE SET NULL ON UPDATE CASCADE
						) ENGINE=InnoDB DEFAULT CHARSET=utf8");

			//	Add the creation to log event
			$sqlLog = '	INSERT INTO `logevent`(`actionID`, `description`) 
						VALUES 		(
										(
										SELECT 	`actionID` 
										FROM 	`logaction` 
										WHERE 	`name` = "Table Created"
										), 
									"The table ' . $table . ' was created automatically by the PHP script.\nThis should only occur once, at the very start of the log events."
									)';
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
			if(count($logEventArray) > 0){
				foreach($logEventArray AS $sqlStatement){
					$conn->exec($sqlStatement);
				}

				$logEventArray = array(); //reinitialize it i.e. make it empty	
				
				$totaltime = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
				$time = $totaltime - $prevtime;
				$prevtime = $totaltime;
				echo '<b>Execution time for filling in Log Event with database/table creation:</b> ' . $time . 's<br />';
			} else{
				echo "<b>There was nothing to add into Log Event</b><br />";
			}
		} else{
			echo "<b>$table does not exist so couldn't start log event procedure</b><br />";
		}

		//Calculating total time spent checking if tables exist and/or creating them.
		echo '<b>Total Execution Time for creating all tables:</b> ' . $totaltime . 's.<br />';

		//Close connection
		$conn = null;
	}
	catch(PDOException $e)
	{
		$error = 'Failed to create tables for ' . DB_NAME . ": " . $e->getMessage() . '<br />';
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$conn = null;
		exit();
	}
}
?>