USE test;
SET NAMES utf8;
USE meetingflow;

INSERT INTO `booking` SET
							`meetingRoomID` = 1,
							`userID` = 1,
							`companyID` = 1,
							`displayName` = '',
							`startDateTime` = DATE_FORMAT(STR_TO_DATE('31-03-2017 17:50:00', '%d-%m-%Y %T'),'%Y-%m-%d %T'),
							`endDateTime` = DATE_FORMAT(STR_TO_DATE('31-03-2017 18:00:00','%d-%m-%Y %T'),'%Y-%m-%d %T'),
							`description` = '',
							`cancellationCode` = '09916c78a8b4d26b77e7129641a953371d87dffefab21d4df19c3a7e5c50c6e0';


SELECT 		b.`bookingID`,
			b.`companyID`,
			m.`name` 										AS BookedRoomName, 
			DATE_FORMAT(b.startDateTime, '%d %b %Y %T') 	AS StartTime, 
			DATE_FORMAT(b.endDateTime, '%d %b %Y %T') 		AS EndTime, 
			b.displayName 									AS BookedBy,
			(	SELECT `name` 
				FROM `company` 
				WHERE `companyID` = b.`companyID`
			)												AS BookedForCompany,
			u.firstName, 
			u.lastName, 
			u.email, 
			GROUP_CONCAT(c.`name` separator ', ') 			AS WorksForCompany, 
			b.description AS BookingDescription, 
			DATE_FORMAT(b.dateTimeCreated, '%d %b %Y %T') 	AS BookingWasCreatedOn, 
			DATE_FORMAT(b.actualEndDateTime, '%d %b %Y %T') AS BookingWasCompletedOn, 
			DATE_FORMAT(b.dateTimeCancelled, '%d %b %Y %T') AS BookingWasCancelledOn 
FROM 		`booking` b 
LEFT JOIN 	`meetingroom` m 
ON 			b.meetingRoomID = m.meetingRoomID 
LEFT JOIN 	`user` u 
ON 			u.userID = b.userID 
LEFT JOIN 	`employee` e 
ON 			e.UserID = u.userID 
LEFT JOIN 	`company` c 
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	b.bookingID
ORDER BY 	b.bookingID
DESC;

SELECT 1 FROM `booking` WHERE `cancellationCode` = 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae';

SELECT 	u.`userID`,
			u.`bookingdescription`, 
			u.`displayname`,
			c.`companyID`,
			c.`name` 					AS companyName
	FROM 	`user` u
	JOIN 	`employee` e
	ON 		e.userID = u.userID
	JOIN	`company` c
	ON 		c.companyID = e.companyID
	WHERE 	u.`userID` = 1;
	
UPDATE `user` SET `bookingDescription` = '', `displayName` = '' WHERE userID <> 0 AND `bookingDescription` IS NULL AND `displayName` IS NULL; 


SELECT 	b.`bookingID`,
		m.`name` AS BookedRoomName, 
		DATE_FORMAT(b.startDateTime, '%d %b %Y %T') AS StartTime, 
		DATE_FORMAT(b.endDateTime, '%d %b %Y %T') AS EndTime, 
		b.displayName AS BookedBy, 
		u.firstName, 
		u.lastName, 
		u.email, 
		GROUP_CONCAT(c.`name` separator ', ') AS WorksForCompany, 
		b.description AS BookingDescription, 
		DATE_FORMAT(b.dateTimeCreated, '%d %b %Y %T') AS BookingWasCreatedOn, 
		DATE_FORMAT(b.actualEndDateTime, '%d %b %Y %T') AS BookingWasCompletedOn, 
		DATE_FORMAT(b.dateTimeCancelled, '%d %b %Y %T') AS BookingWasCancelledOn 
		FROM `booking` b 
		LEFT JOIN `meetingroom` m 
		ON b.meetingRoomID = m.meetingRoomID 
		LEFT JOIN `user` u 
		ON u.userID = b.userID 
		LEFT JOIN `employee` e 
		ON e.UserID = u.userID 
		LEFT JOIN `company` c 
		ON c.CompanyID = e.CompanyID 
		GROUP BY b.bookingID
		ORDER BY b.bookingID
		DESC;

SELECT  `meetingRoomID`, 
		`name`, 
		`capacity`, 
		`description`, 
		`location`
FROM `meetingroom`;

SELECT 	u.`userID`, 
			u.`firstname`, 
			u.`lastname`, 
			u.`email`,
			a.`AccessName`,
			u.`displayname`,
			u.`bookingdescription`
			FROM `user` u
			JOIN `accesslevel` a
			ON a.accessID = u.accessID
			WHERE u.`userID` = 3;

SELECT `accessID` ,`accessname` FROM `accesslevel`;

SELECT 	u.`userID`, 
		u.`firstname`, 
		u.`lastname`, 
		u.`email`,
		a.`AccessName`,
		u.`displayname`,
		u.`bookingdescription`,
		GROUP_CONCAT(CONCAT_WS(' for ', cp.`name`, c.`name`) separator ', ') AS WorksFor,
		DATE_FORMAT(u.`create_time`, "%d %b %Y %T") AS DateCreated,
		u.`isActive`,
		DATE_FORMAT(u.`lastActivity`, "%d %b %Y %T") AS LastActive
		FROM `user` u 
		LEFT JOIN `employee` e 
		ON e.UserID = u.userID 
		LEFT JOIN `company` c 
		ON e.CompanyID = c.CompanyID 
		LEFT JOIN `companyposition` cp 
		ON cp.PositionID = e.PositionID
		LEFT JOIN `accesslevel` a
		ON u.AccessID = a.AccessID
		GROUP BY u.`userID`
		ORDER BY u.`AccessID`
		ASC;

SELECT 		c.companyID 										AS CompID,
			c.`name` 											AS CompanyName, 
			COUNT(c.`name`) 									AS NumberOfEmployees,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `employee` e 
			ON b.`UserID` = e.`UserID` 
			INNER JOIN `company` c 
			ON e.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID
			AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
			AND MONTH(b.`actualEndDateTime`) = MONTH(NOW()))   	AS MonthlyCompanyWideBookingTimeUsed,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `employee` e 
			ON b.`UserID` = e.`UserID` 
			INNER JOIN `company` c 
			ON e.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed,
			DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
			DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate 
FROM 		`company` c 
LEFT JOIN 	`employee` e 
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	c.`name`;


SELECT 		c.companyID 										AS CompID,
			c.`name` 											AS CompanyName, 
			(SELECT 	COUNT(c.`name`) 
            FROM 		`company` c 
			JOIN 		`employee` e 
			ON 			c.CompanyID = e.CompanyID 
			WHERE 		e.companyID = CompID)								AS NumberOfEmployees,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `employee` e 
			ON b.`UserID` = e.`UserID` 
			INNER JOIN `company` c 
			ON e.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID
			AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
			AND MONTH(b.`actualEndDateTime`) = MONTH(NOW()))   	AS MonthlyCompanyWideBookingTimeUsed,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `employee` e 
			ON b.`UserID` = e.`UserID` 
			INNER JOIN `company` c 
			ON e.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed,
			DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
			DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate 
FROM 		`company` c 
GROUP BY 	c.`name`;

INSERT INTO `company` SET `name` = 'testytessfasad';


SELECT 		c.companyID 										AS CompID,
						c.`name` 											AS CompanyName,
						DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
						DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate,							
						(
							SELECT 	COUNT(c.`name`) 
							FROM 	`company` c 
							JOIN 	`employee` e 
							ON 		c.CompanyID = e.CompanyID 
							WHERE 	e.companyID = CompID
						)													AS NumberOfEmployees,
						(
							SELECT 		(SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`)))) 
							FROM 		`booking` b 
							INNER JOIN 	`employee` e 
							ON 			b.`UserID` = e.`UserID` 
							INNER JOIN 	`company` c 
							ON 			e.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
							AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
							AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)   												AS MonthlyCompanyWideBookingTimeUsed,
						(
							SELECT 		(SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`)))) 
							FROM 		`booking` b 
							INNER JOIN 	`employee` e 
							ON 			b.`UserID` = e.`UserID` 
							INNER JOIN 	`company` c 
							ON 			e.`CompanyID` = c.`CompanyID` 
							WHERE 		b.`CompanyID` = CompID
						)   												AS TotalCompanyWideBookingTimeUsed
			FROM 		`company` c 
			GROUP BY 	c.`name`;


SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS CompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						cp.`name`					AS PositionName, 
						e.`startDateTime`,
						(
							SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`))) 
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = 5
							AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
							AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						) 							AS MonthlyBookingTimeUsed,
						(
							SELECT 		SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - 
										TIME_TO_SEC(b.`startDateTime`))) 
							FROM 		`booking` b
							INNER JOIN `employee` e
							ON 			b.`userID` = e.`userID`
							INNER JOIN `company` c
							ON 			c.`companyID` = e.`companyID`
							INNER JOIN 	`user` u 
							ON 			e.`UserID` = u.`UserID` 
							WHERE 		b.`userID` = UsrID
							AND 		b.`companyID` = 5
						) 							AS TotalBookingTimeUsed							
				FROM 	`company` c 
				JOIN 	`employee` e
				ON 		e.CompanyID = c.CompanyID 
				JOIN 	`companyposition` cp 
				ON 		cp.PositionID = e.PositionID
				JOIN 	`user` u 
				ON 		u.userID = e.UserID 
				WHERE 	c.`companyID` = 5;


SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `isActive` = 1;

SELECT l.logID, DATE_FORMAT(l.logDateTime, "%d %b %Y %T") AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription FROM `logevent` l JOIN `logaction` la ON la.actionID = l.actionID ORDER BY UNIX_TIMESTAMP(l.logDateTime) DESC;

SELECT l.logID, l.logDateTime, la.`name`, la.description, l.description FROM `logevent` l JOIN `logaction` la ON la.actionID = l.actionID;

CREATE DATABASE IF NOT EXISTS test;

SHOW DATABASES LIKE 'test';

SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'sys';

INSERT INTO employee(`CompanyID`, `UserID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test5'),(SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'));

SELECT `userID` FROM `user` WHERE `email` = 'test@test.com';

SELECT `CompanyID` FROM `company` WHERE `name` = 'test5';

SELECT * FROM `user`;

SELECT * FROM `company`;

SELECT * FROM `booking`;

SELECT * FROM `employee`;

SELECT * FROM `meetingroom`;

SELECT * FROM `roomequipment`;

SELECT * FROM `equipment`;

SELECT * FROM `logaction`;

SELECT * FROM `companyposition`;

SELECT * FROM `accesslevel`;

SELECT * FROM `logevent`;

INSERT INTO `logevent`(`actionID`, `description`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'Database Created'), 'Database was created automatically by the PHP script.');

INSERT INTO `logevent`(`actionID`, `sessionID`, `description`, `userID`, `companyID`, `bookingID`, `meetingRoomID`, `equipmentID`) VALUES (7, NULL, 'This is a more in-depth description over the details connected to this log event', 1, NULL, NULL, NULL, NULL);

INSERT INTO `logevent`(`actionID`,`description`) VALUES (10, 'test');

SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, c.`name` AS CompanyName, c.dateTimeCreated AS CreationDate, w.ip AS BookedFromIPAddress FROM `logevent` l LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` LIKE 'Company%';

UPDATE `logevent` SET `meetingRoomID` = 2 WHERE `logID` = 18;

SELECT * FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `booking` b ON b.bookingID = l.bookingID LEFT JOIN `equipment` eq ON eq.EquipmentID = l.equipmentID LEFT JOIN `meetingroom` m ON m.meetingRoomID = l.meetingRoomID LEFT JOIN `websession` w ON w.sessionID = l.sessionID;

INSERT INTO `logevent`(`actionID`, `description`, `userID`) VALUES (3, 'This is a more in-depth description over the details connected to this log event', NULL);

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND eq.`name` = 'wifi';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND eq.`name` = 'Wifi';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND m.`name` = 'Blåmann';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND m.`name` = 'Blåmann';

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`CompanyID` = 5;
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`CompanyID` = 1;
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`userID` = 1;

INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`email` = 'test@test.com' AND c.`name` = 'test1'), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), 2, (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`userID` = 2), 'Display Name', '2017-03-25 16:00:00', '2017-03-25 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), 3, (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`userID` = 3), 'Display Name', '2017-03-26 16:00:00', '2017-03-26 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

SELECT * FROM `booking` JOIN `user` ON `user`.`userID` = `booking`.`userID`;

UPDATE `booking` SET `companyID` = (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON c.CompanyID = e.CompanyID JOIN `user` u ON u.userID = e.UserID WHERE e.UserID = 1) WHERE `userID`= 8 AND `bookingID` <> 0;
UPDATE `booking` SET `companyID` = 1 WHERE `bookingID` = 4;

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` JOIN `employee` e ON u.`userID` = e.`UserID` JOIN `company` c ON c.`CompanyID` = e.`CompanyID` WHERE b.actualEndDateTime BETWEEN '2017-01-01' AND '2017-06-06' AND b.`userID` = 1 AND b.`companyID` = 5;
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` JOIN `employee` e ON u.`userID` = e.`UserID` JOIN `company` c ON c.`CompanyID` = e.`CompanyID` WHERE b.actualEndDateTime BETWEEN '2017-01-01' AND '2017-06-06' AND b.`userID` = 1 AND b.`companyID` = 1;
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` JOIN `employee` e ON u.`userID` = e.`UserID` JOIN `company` c ON c.`CompanyID` = e.`CompanyID` WHERE b.actualEndDateTime BETWEEN '2017-01-01' AND '2017-06-06' AND b.`userID` = 1; #AND b.`companyID` = 5;

INSERT INTO `accesslevel`(`accessname`, `description`) VALUES ('test','test');

DELETE FROM `accesslevel` WHERE `AccessID` = 7;

INSERT INTO `companyposition`(`name`, `description`) VALUES ('test','test');

DELETE FROM `companyposition` WHERE `PositionID` = 3;

DELETE FROM `logaction` WHERE `actionID` = 1;

INSERT INTO `logevent`(`actionID`) VALUES (1);

UPDATE `logevent` SET `logDateTime` = `logDateTime` - INTERVAL 40 DAY WHERE `logID` < 6;

DELETE FROM `logevent` WHERE (`logDateTime` < CURDATE() - INTERVAL 30 DAY) AND `logID` <> 0;

DELETE FROM `equipment` WHERE `EquipmentID` = 3;

SELECT re.amount, e.`name`, e.`description` FROM `equipment` e JOIN `roomequipment` re JOIN `meetingroom` m WHERE m.meetingroomid = re.meetingroomid AND re.EquipmentID = e.EquipmentID AND m.`name` = 'Blåmann';

DELETE FROM `roomequipment` WHERE `MeetingRoomID` = 1 AND `equipmentID` = 3;

INSERT INTO `roomequipment`(`equipmentID`, `meetingRoomID`, `amount`) VALUES (3,1,3);

UPDATE `meetingroom` SET `location` = NULL WHERE `meetingRoomID` = 2;

UPDATE `meetingroom` SET `location` = 'New location URL/location description' WHERE `meetingRoomID` = 2;

DELETE FROM `meetingroom` WHERE `meetingRoomID` = 3;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, GROUP_CONCAT(c.`name` separator ', ') AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID GROUP BY b.bookingID;

DELETE FROM `company` WHERE `companyID` = 3;

DELETE FROM `user` WHERE `userID` = 9;

INSERT INTO `employee`(`companyID`, `userID`, `positionID`) VALUES (1,1,2);

DELETE FROM `employee` WHERE `UserID` = 10 AND `companyID` = 1;

SELECT * FROM `booking` ORDER BY `startDateTime` ASC;

DELETE FROM `booking` WHERE `bookingID` <> 0 AND ((`actualEndDateTime` < CURDATE() - INTERVAL 30 DAY) OR  (`dateTimeCancelled` < CURDATE() - INTERVAL 30 DAY));

DELETE FROM `booking` WHERE `bookingID` = 8;

UPDATE `company` SET `removeAtDate` = DATE(CURRENT_TIMESTAMP) WHERE `CompanyID` = 9;

DELETE FROM `company` WHERE `removeAtDate` IS NOT NULL AND `removeAtDate` < CURRENT_TIMESTAMP AND `CompanyID` <> 0;

DELETE FROM `company` WHERE `CompanyID` = 8;

DELETE FROM `user` WHERE userID = 15;

UPDATE `accesslevel` SET `Description` = 'New description of the permission for the access level' WHERE `AccessID` = 6;

UPDATE `accesslevel` SET `AccessName` = 'New name for the access level' WHERE `AccessID` = 6;

UPDATE `logaction` SET `name` = 'New log action name' WHERE `actionID` = 1;

UPDATE `companyposition` SET `name` = 'Employee' WHERE `PositionID` = 2;

UPDATE `equipment` SET `description` = 'New description for equipment' WHERE `EquipmentID` = 3;

UPDATE `equipment` SET `name` = 'New name for equipment' WHERE `EquipmentID` = 3;

UPDATE `roomequipment` re JOIN `equipment` e ON e.EquipmentID = re.EquipmentID JOIN `meetingroom` m ON m.meetingRoomID = re.MeetingRoomID SET re.`amount` = 2 WHERE re.EquipmentID = 2 AND re.meetingRoomID = 1;

UPDATE `meetingroom` SET `location` = 'New location URL/location description' WHERE `meetingRoomID` = 3;

INSERT INTO `meetingroom`(`name`, `capacity`, `description`, `location`) VALUES ('A fake meeting room', 0, 'Cannot fit anyone.', 'Random image url');

UPDATE `meetingroom` SET `description` = 'New Description of the meeting room' WHERE `meetingRoomID` = 3;

UPDATE `meetingroom` SET `capacity` = 4 WHERE `meetingRoomID` = 2;

UPDATE `employee` e JOIN `user` u ON u.userID = e.UserID JOIN `company` c ON c.CompanyID = e.CompanyID SET e.`PositionID` = 1 WHERE c.CompanyID = 4 AND u.userID = 5;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN '2017-03-22 14:20:00' AND '2017-03-30 14:30:00';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN '2017-03-22 14:20:00' AND '2017-03-30 14:30:00';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND CURRENT_TIMESTAMP < b.endDateTime AND c.CompanyID = 4;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS YourDisplayedName, b.description AS YourBookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.userID = 1;

INSERT INTO meetingroom(`name`, `capacity`, `description`) VALUES ('Toillpeis', 3, 'You must be a real toillpeis to have booked to room!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'This booking is just to look at the COOL VIEW!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-16 12:00:00', '2017-03-16 13:30:00', 'I could not find a better room');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-16 13:30:00', '2017-03-16 14:00:00', 'Someone told me this has a cool view');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-21 12:00:00', '2017-03-21 13:30:00', 'I could not find a better room', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test11@test.com'), 'NEED IT!', '2017-01-22 14:30:00', '2017-01-22 15:30:00', '...?', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

UPDATE `booking` SET actualEndDateTime = endDateTime WHERE actualEndDateTime IS NULL AND dateTimeCancelled IS NULL AND endDateTime < CURRENT_TIMESTAMP AND bookingID <> 0;

UPDATE `booking` SET dateTimeCancelled = CURRENT_TIMESTAMP WHERE bookingID = 6;

UPDATE `booking` SET `displayName` = 'new Display Name' WHERE bookingID = 6;

UPDATE `booking` SET `description` = 'new Booking Description' WHERE bookingID = 6;

UPDATE `booking` SET `actualEndDateTime` = CURRENT_TIMESTAMP WHERE bookingID = 5 AND CURRENT_TIMESTAMP BETWEEN `startDateTime` AND `endDateTime`;

SELECT * FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID;

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE b.actualEndDateTime BETWEEN '2017-03-15' AND '2017-03-17' AND u.`userID` = 1;

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`actualEndDateTime` BETWEEN '2017-03-15' AND '2017-03-17' AND c.`CompanyID` = 5;

DELETE FROM `booking` WHERE `bookingID` = 1;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT COUNT(*) AS CompanyBookings FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT `booking`.`bookingID`, `booking`.`startDateTime`, `booking`.`endDateTime` FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`name` = 'New Company Name';

SELECT c.`name`, SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` ORDER BY c.`name`;

INSERT INTO `companyposition`(`name`, `description`) VALUES ('Owner', 'This person has access to all company information and management.');

INSERT INTO `companyposition`(`name`, `description`) VALUES ('Employee', 'This person has access to browse company information.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Full website access.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Company Owner', 'Full company information and management.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Meeting Room', 'These are special accounts used to handle booking code login.');

UPDATE `employee` SET `PositionID` = 1 WHERE `PositionID` = 2;

INSERT INTO `equipment`(`name`, `description`) VALUES('HDTV','This TV has an HD signal. HDMI input. etc.');

INSERT INTO `equipment`(`name`, `description`) VALUES('WiFi','This room has a WiFi connection.');

INSERT INTO `equipment`(`name`, `description`) VALUES('ETHERNET','This room supports wired Ethernet connections.');

UPDATE `equipment` SET `description` = '2.4 and 5 Ghz.' WHERE `equipmentID` = 2;

UPDATE `equipment` SET `description` = 'CAT-6 10Gb/s.' WHERE `equipmentID` = 3;

SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'WiFi';

INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES((SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'WiFi'), (SELECT `MeetingRoomID` FROM `meetingroom` WHERE `name`= 'Blåmann'), 1);

INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES((SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'ETHERNET'), (SELECT `MeetingRoomID` FROM `meetingroom` WHERE `name`= 'Blåmann'), 4);

SELECT * FROM `roomequipment` re JOIN `equipment` e JOIN `meetingroom` m WHERE re.EquipmentID = e.EquipmentID AND re.MeetingRoomID = m.meetingRoomID;

SELECT re.`amount`, e.`name`, e.`description` FROM `roomequipment` re JOIN `equipment` e JOIN `meetingroom` m WHERE re.EquipmentID = e.EquipmentID AND re.MeetingRoomID = m.meetingRoomID;

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`firstName` = 'testy' AND u.`lastName` = 'mctester';

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`email` = 'test@test.com';

SELECT u.firstName, u.lastName, cp.`name` FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID AND c.`name` = 'test5';

SELECT * FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID;

SELECT c.`name` FROM `company` c JOIN `employee` e WHERE c.CompanyID = e.CompanyID;

SELECT c.`name`, COUNT(c.`name`) AS NumberOfEmployees FROM `company` c JOIN `employee` e WHERE c.CompanyID = e.CompanyID GROUP BY c.`name`;

INSERT INTO `employee`(`CompanyID`, `UserID`, `PositionID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test1'),(SELECT `userID` FROM `user` WHERE `email` = 'test10@test.com'), (SELECT `PositionID` FROM `companyposition` WHERE `name` = 'Employee'));

INSERT INTO `company`(`name`) VALUES ('test6');

INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`,`accessID`, `activationcode`) VALUES ('test2@test.com', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae', 'Test2', 'McTest2', 4, 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

SELECT u.`firstname`, u.`lastname`, u.`email`, c.`name` AS CompanyName, cp.`name` AS CompanyRole FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY c.`name` ;

SELECT `firstname`, `lastname`, `email`, (SELECT c.`name` FROM `company` c JOIN `employee` e JOIN `companyposition`cp JOIN `user` u WHERE c.CompanyID = e.CompanyID AND u.userID = e.UserID) AS CompanyName, (SELECT cp.`name` FROM `companyposition` cp JOIN `company` c JOIN `employee` e JOIN `user` u WHERE c.companyID = e.companyID AND u.userid = e.userid AND cp.positionID = e.positionID) AS CompanyRole FROM `user`;

SELECT DISTINCT u.userID FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY u.userid ASC;

SELECT c.`name` FROM `company` c JOIN `employee` e JOIN `companyposition`cp JOIN `user` u WHERE c.CompanyID = e.CompanyID AND u.userID = e.UserID;

SELECT cp.`name` FROM `companyposition` cp JOIN `company` c JOIN `employee` e JOIN `user` u WHERE c.companyID = e.companyID AND u.userid = e.userid AND cp.positionID = e.positionID;

SELECT * FROM `equipment` e JOIN `roomequipment` re JOIN `meetingroom` m WHERE m.meetingroomid = re.meetingroomid AND re.EquipmentID = e.EquipmentID;

INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`, `accessID`, `activationcode`) VALUES ('test15@test.com', '123test', 'testy15', 'mctester15', 4, 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
