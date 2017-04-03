#START OF CREATE TABLE QUERIES
#
#ACCESS LEVEL
CREATE TABLE IF NOT EXISTS `accesslevel` (
  `AccessID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccessName` varchar(255) DEFAULT NULL,
  `Description` text,
  PRIMARY KEY (`AccessID`),
  UNIQUE KEY `AccessName_UNIQUE` (`AccessName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#BOOKING
CREATE TABLE IF NOT EXISTS `booking` (
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;
#COMPANY
CREATE TABLE IF NOT EXISTS `company` (
  `CompanyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `removeAtDate` date DEFAULT NULL,
  `bookingTimeUsedThisMonth` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`CompanyID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#COMPANYPOSITION
CREATE TABLE IF NOT EXISTS `companyposition` (
  `PositionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`PositionID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#EMPLOYEE
CREATE TABLE IF NOT EXISTS `employee` (
  `CompanyID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `startDateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PositionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserID`,`CompanyID`),
  KEY `FK_CompanyID_idx` (`CompanyID`),
  KEY `FK_PositionID_idx` (`PositionID`),
  CONSTRAINT `FK_CompanyID` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#EQUIPMENT
CREATE TABLE IF NOT EXISTS `equipment` (
  `EquipmentID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`EquipmentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#LOGACTION
CREATE TABLE IF NOT EXISTS `logaction` (
  `actionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`actionID`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#LOGEVENT
CREATE TABLE IF NOT EXISTS `logevent` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#MEETINGROOM
CREATE TABLE IF NOT EXISTS `meetingroom` (
  `meetingRoomID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT 'No name set',
  `capacity` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `description` text,
  `location` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`meetingRoomID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
#ROOMEQUIPMENT
CREATE TABLE IF NOT EXISTS `roomequipment` (
  `EquipmentID` int(10) unsigned NOT NULL,
  `MeetingRoomID` int(10) unsigned NOT NULL,
  `amount` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`EquipmentID`,`MeetingRoomID`),
  KEY `FK_MeetingRoomID_idx` (`MeetingRoomID`),
  CONSTRAINT `FK_EquipmentID` FOREIGN KEY (`EquipmentID`) REFERENCES `equipment` (`EquipmentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_MeetingRoomID2` FOREIGN KEY (`MeetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#USER
CREATE TABLE IF NOT EXISTS `user` (
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
  UNIQUE KEY `activationCode_UNIQUE` (`activationCode`),
  UNIQUE KEY `bookingCode_UNIQUE` (`bookingCode`),
  KEY `FK_AccessID_idx` (`AccessID`),
  CONSTRAINT `FK_AccessID` FOREIGN KEY (`AccessID`) REFERENCES `accesslevel` (`AccessID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#WEBSESSION
CREATE TABLE IF NOT EXISTS `websession` (
  `sessionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned DEFAULT NULL,
  `dateTimeStart` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `IP` char(64) NOT NULL,
  PRIMARY KEY (`sessionID`),
  KEY `FK_UserID4_idx` (`userID`),
  CONSTRAINT `FK_UserID4` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
#
#END OF CREATE TABLE QUERIES
#
#START OF INSERT QUERIES
#
#INSERT DATA INTO USER (firstname, lastname, email, hashed password, accessID and activationcode) - TEMPLATE
INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`, `accessID`, `activationcode`) VALUES ('User Email', 'SHA 256 hashed password', 'First Name', 'Last Name', <accessID should be 4>, 'SHA 256 hash length activation code');
#INSERT A NEW COMPANY - TEMPLATE
INSERT INTO `company`(`name`) VALUES ('Company Name');
#INSERT A NEW BOOKING OF A MEETING ROOM BASED ON THE SELECTED MEETING ROOM AND REFERENCES THE USER WHO CREATED IT AND THE COMPANY THAT USER WORKS FOR (IF THEY ONLY WORK FOR ONE COMPANY) - TEMPLATE
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`email` = 'User Email'), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`userID` = <userID>), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
#INSERT A NEW BOOKING OF A MEETING ROOM BASED ON THE SELECTED MEETING ROOM FOR THE SELECTED COMPANY AND THE USER WHO CREATED IT (IF THEY WORK FOR MULTIPLE COMPANIES)- TEMPLATE
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE u.`email` = 'Email' AND c.`name` = 'Company Name'), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), (SELECT e.`companyID` FROM `company` c JOIN `employee` e ON e.`CompanyID` = c.`CompanyID` JOIN `user` u ON u.`userID` = e.`userID` WHERE e.`userID` = <userID> AND e.`companyID` = <companyID>), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `CompanyID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES (<meetingroomID>, <userID>, <companyID>, 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', '64 char SHA256 code');
#INSERT A NEW EMPLOYEE IN A COMPANY, IF THE COMPANY ALREADY EXISTS, BASED ON AN EXISTING USER AND SETTING THEIR COMPANY ROLE - TEMPLATE
INSERT INTO `employee`(`CompanyID`, `UserID`, `PositionID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'Company Name'),(SELECT `userID` FROM `user` WHERE `email` = 'user Email'), (SELECT `PositionID` FROM `companyposition` WHERE `name` = 'Company role name'));
INSERT INTO `employee`(`CompanyID`, `UserID`, `PositionID`) VALUES (<companyID>, <userID>, (SELECT `PositionID` FROM `companyposition` WHERE `name` = 'Company Position'));
#INSERT A NEW MEETING ROOM INTO THE SYSTEM - TEMPLATE
INSERT INTO `meetingroom`(`name`, `capacity`, `description`, `location`) VALUES ('MeetingRoom Name', <capacityNumber>, 'MeetingRoom Description', 'Image url of location');
#INSERT NEW EQUIPMENT INTO A MEETING ROOM WITH THE AMOUNT OF THAT EQUIPMENT - TEMPLATE
INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES((SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'Equipment Name'), (SELECT `MeetingRoomID` FROM `meetingroom` WHERE `name`= 'Meeting Room Name'), 1);
INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES(<equipmentID>, <meetingroomID>, <amountNumber>);
#INSERT NEW EQUIPMENT THAT THE MEETING ROOMS WILL HAVE ACCESS TO - TEMPLATE
INSERT INTO `equipment`(`name`, `description`) VALUES('Equipment Name','Equipment Description');
#INSERT ACCESSLEVEL BACKEND ONLY - TEMPLATE
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Access Name', 'Access description.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('', '');
#INSERT ACCESSLEVEL BACKEND ONLY - QUERIES
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Has full access to all website pages, company information and user information.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Company Owner', 'Full company information and management.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Meeting Room', 'These are special accounts used to handle booking code login.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('AccessName', 'Access Description.');
#INSERT LOGEVENT - TEMPLATE
INSERT INTO `logevent`(`actionID`, `sessionID`, `description`, `userID`, `companyID`, `bookingID`, `meetingRoomID`, `equipmentID`) VALUES ((SELECT `actionID` FROM `logaction` WHERE `name` = 'the action name'), <sessionID>, 'This is a more in-depth description over the details connected to this log event', <userID>, <companyID>, <bookingID>, <meetingRoomID>, <equipmentID>);
INSERT INTO `logevent`(`actionID`, `sessionID`, `description`, `userID`, `companyID`, `bookingID`, `meetingRoomID`, `equipmentID`) VALUES (<actionID>, <sessionID>, 'This is a more in-depth description over the details connected to this log event', <userID>, <companyID>, <bookingID>, <meetingRoomID>, <equipmentID>);
#INSERT LOGACTION BACKEND ONLY - TEMPLATE
INSERT INTO `logaction`(`name`,`description`) VALUES ('An action name','A description of what that action should apply to');
INSERT INTO `logaction`(`name`,`description`) VALUES ('','');
#INSERT LOGACTION BACKEND ONLY - QUERIES
INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Created','The referenced user just registered an account.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Removed','A user account has been removed. See log description for more information.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Created','The referenced user created a new meeting room booking.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Cancelled','The referenced user cancelled a meeting room booking.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Completed','The referenced booking has been completed.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Created','The referenced user just created the referenced company.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Removed','A company has been removed. See log description for more information.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Database Created','The database we are using right now just got created.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Table Created','A table in the database was created.');
#INSERT COMPANYPOSITION BACKEND ONLY - TEMPLATE
INSERT INTO `companyposition`(`name`, `description`) VALUES ('A position name', 'A description of what that role is within the company.');
#INSERT COMPANYPOSITION BACKEND ONLY - QUERIES
INSERT INTO `companyposition`(`name`, `description`) VALUES ('Owner', 'User can manage company information and add/remove users connected to the company.');
INSERT INTO `companyposition`(`name`, `description`) VALUES ('Employee', 'User can view company information and connected users.');

#
#END OF INSERT QUERIES
#
#START OF UPDATE DATA
#
#UPDATE EMAIL OF SELECTED USER - TEMPLATE
UPDATE `user` SET `email` = <newEmail> WHERE `userID` = <UserID>;
#UPDATE PASSWORD OF SELECTED USER - TEMPLATE
UPDATE `user` SET `password` = <newPassword> WHERE `userID` = <userID>;
#UPDATE ACCESSID OF SELECTED USER - TEMPLATE
UPDATE `user` SET `AccessID` = <newAccessID> WHERE `userID` = <userID>;
#UPDATE FIRST AND LASTNAME OF SELECTED USER - TEMPLATE
UPDATE `user` SET `firstname` = 'NewFirstName', `lastname` = 'NewLastName' WHERE `userID` = <userID>;
#UPDATE DISPLAYNAME OF SELECTED USER - TEMPLATE
UPDATE `user` SET `displayName` = 'NewDisplayName' WHERE `userID` = <userID>;
#UPDATE DEFAULT BOOKING DESCRIPTION OF SELECTED USER - TEMPLATE
UPDATE `user` SET `bookingDescription` = 'NewBookingDescription' WHERE `userID` = <userID>;
#UPDATE BOKING CODE OF SELECTED USER - TEMPLATE
UPDATE `user` SET `bookingCode` = <newCodeNumber> WHERE `userID` = <userID>;
#UPDATE THE DATETIME OF THE LAST ACTIVITY OF THE SELECTED USER - TEMPLATE
UPDATE `user` SET `lastActivity` = CURRENT_TIMESTAMP WHERE `userID` = <userID>;
#UPDATE THE TEMPORARY PASSWORD AND THE DATETIME IT WAS ACTIVATED FOR THE SELECTED USER - TEMPLATE
UPDATE `user` SET `tempPassword` = 'newTempPassword', `dateRequested` = CURRENT_TIMESTAMP WHERE `userID` = <userID>;
#UPDATE THE USER ACCOUNT TO BE ACTIVE (ALLOWED TO LOG IN TO THE WEBSITE) - TEMPLATE
UPDATE `user` SET `isActive` = 1 WHERE `userID` = <userID>;
#UPDATE THE COMPANY NAME FOR THE SELECTED COMPANY - TEMPLATE
UPDATE `company` SET `name` = 'New Company Name' WHERE `CompanyID` = <CompanyID>;
#UPDATE THE COMPANY INFORMATION WITH A DATE WHEN THE SELECTED COMPANY SHOULD BE AUTOMATICALLY REMOVED - TEMPLATE
UPDATE `company` SET `removeAtDate` = 'some new date in the format year-month-day' WHERE `companyID` = <CompanyID>;
#UPDATE THE ACTIVE BOOKINGS TO BE SET AS COMPLETED WHEN THE TIME HAS GONE PAST THEIR SCHEDULED ENDING TIME
UPDATE `booking` SET `actualEndDateTime` = `endDateTime` WHERE `actualEndDateTime` IS NULL AND `dateTimeCancelled` IS NULL AND `endDateTime` < CURRENT_TIMESTAMP AND `bookingID` <> 0;
#UPDATE THE BOOKING TO ACKNOWLEDGE THAT IT HAS BEEN CANCELLED - TEMPLATE
UPDATE `booking` SET `dateTimeCancelled` = CURRENT_TIMESTAMP WHERE `bookingID` = <bookingID>;
#UPDATE THE DISPLAY NAME OF THE SELECTED BOOKING - TEMPLATE
UPDATE `booking` SET `displayName` = 'new Display Name' WHERE `bookingID` = <bookingID>;
#UPDATE THE BOOKING DESCRIPTION OF THE SELECTED BOOKING - TEMPLATE
UPDATE `booking` SET `description` = 'new Booking Description' WHERE `bookingID` = <bookingID>;
#UPDATE THE SELECTED USERS EMPLOYEE STATUS WITHIN THE SELECTED COMPANY - TEMPLATE
UPDATE `employee` e JOIN `user` u ON u.userID = e.UserID JOIN `company` c ON c.CompanyID = e.CompanyID SET e.`PositionID` = <positionID> WHERE c.CompanyID = <CompanyID> AND u.userID = <userID>;
#UPDATE THE ROOM NAME OF THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `meetingroom` SET `name` = 'New Name' WHERE `meetingRoomID` = <meetingRoomID>;
#UPDATE THE ROOM CAPACITY OF THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `meetingroom` SET `capacity` = <NewCapacityNumber> WHERE `meetingRoomID` = <meetingRoomID>;
#UPDATE THE ROOM DESCRIPTION OF THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `meetingroom` SET `description` = 'New Description of the meeting room' WHERE `meetingRoomID` = <meetingRoomID>;
#UPDATE THE ROOM LOCATION IMAGE/DESCRIPTION FOR THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `meetingroom` SET `location` = 'New location URL/location description' WHERE `meetingRoomID` = <meetingRoomID>;
#UPDATE THE ROOM TO NOT HAVE A LOCATION IMAGE/DESCRIPTION FOR THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `meetingroom` SET `location` = NULL WHERE `meetingRoomID` = <meetingroomID>;
#UPDATE THE AMOUNT OF THE SELECTED EQUIPMENT IN THE SELECTED MEETING ROOM - TEMPLATE
UPDATE `roomequipment` re JOIN `equipment` e ON e.EquipmentID = re.EquipmentID JOIN `meetingroom` m ON m.meetingRoomID = re.MeetingRoomID SET re.`amount` = <someNewAmountNumber> WHERE re.EquipmentID = <equipmentID> AND re.meetingRoomID = <meetingRoomID>;
#UPDATE THE EQUIPMENT NAME OF THE SELECTED EQUIPMENT - TEMPLATE
UPDATE `equipment` SET `name` = 'New name for equipment' WHERE `EquipmentID` = <EquipmentID>;
#UPDATE THE EQUIPMENT DESCRIPTION OF THE SELECTED EQUIPMENT - TEMPLATE
UPDATE `equipment` SET `description` = 'New description for equipment' WHERE `EquipmentID` = <EquipmentID>;
#UPDATE THE NAME OF THE SELECTED LOG ACTION
UPDATE `logaction` SET `name` = 'New log action name' WHERE `actionID` = <actionID>;
#UPDATE THE DESCRIPTION OF THE SELECTED LOG ACTION
UPDATE `logaction` SET `description` = 'New log action description' WHERE `actionID` = <actionID>;
#UPDATE THE NAME OF THE SELECTED COMPANY POSITION - TEMPLATE
UPDATE `companyposition` SET `name` = 'New position name' WHERE `PositionID` = <positionID>;
#UPDATE THE PERMISSION DESSCRIPTION OF THE SELECTED COMPANY POSITION - TEMPLATE
UPDATE `companyposition` SET `description` = 'New description of the permission this company position gives' WHERE `PositionID` = <positionID>;
#UPDATE THE NAME OF THE SELECTED ACCESS LEVEL
UPDATE `accesslevel` SET `AccessName` = 'New name for the access level' WHERE `AccessID` = <AccessID>;
#UPDATE THE PERMISSION DESCRIPTION OF THE SELECTED ACCESS LEVEL
UPDATE `accesslevel` SET `Description` = 'New description of the permission for the access level' WHERE `AccessID` = <AccessID>;
#
#END OF UPDATE DATA
#
#START OF SELECT QUERIES
#
#SELECT USERS AND THEIR POSITION WITHIN A SPECIFIC COMPANY BASED ON COMPANY NAME
SELECT u.`firstName`, u.`lastName`, cp.`name`, e.`startDateTime` FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID AND c.`name` = 'Company Name';
SELECT u.`firstName`, u.`lastName`, cp.`name`, e.`startDateTime` FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID AND c.`companyID` = <companyID>;
#SELECT BOOKING TIME USED BY THE SELECTED USER
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE b.`userID` = <userID>;
#SELECT BOOKING TIME USED BY A USER BASED ON FIRST/LAST NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`firstName` = 'First Name' AND u.`lastName` = 'Last Name';
#SELECT BOOKING TIME USED BY A USER BASED ON EMAIL
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`email` = 'Email';
#SELECT BOOKING TIME USED BY THE SELECTED USER, REGARDLESS OF COMPANY, BASED ON A TIMESPAN OF TWO DATES
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE b.actualEndDateTime BETWEEN 'Some date in format year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second' AND u.`userID` = <userID>;
#SELECT BOOKING TIME USED BY THE SELECTED USER, FOR A SELECTED COMPANY, BASED ON A TIMESPAN OF TWO DATES
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` JOIN `employee` e ON u.`userID` = e.`UserID` JOIN `company` c ON c.`CompanyID` = e.`CompanyID` WHERE b.actualEndDateTime BETWEEN 'Some date in format year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second' AND b.`userID` = <userID> AND b.`companyID` = <companyID>;
#SELECT BOOKING TIME USED BY ENTIRE COMPANY BASED ON SELECTED COMPANY
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`CompanyID` = <companyID>;
#SELECT BOOKING TIME USED BY THE SELECTED COMPANY BASED ON A TIMESPAN OF TWO DATES
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`actualEndDateTime` BETWEEN 'Some date in formay year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second' AND b.`CompanyID` = <companyID>;
#SELECT BOOKING TIME USED BY ENTIRE COMPANY BASED ON COMPANY NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`name` = 'Company Name';
#SELECT ALL COMPANY NAMES AND THE NUMBER OF EMPLOYEES IT HAS
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
			WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed
FROM 		`company` c 
JOIN 		`employee` e 
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	c.`name`;
#SELECT ALL USERS THAT ARE REGISTERED AS AN EMPLOYEE AND THE COMPANY AND POSITION THEY HOLD
SELECT u.`firstname`, u.`lastname`, u.`email`, c.`name` AS CompanyName, cp.`name` AS CompanyRole FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY c.`name`;
#SELECT ALL USERS THAT ARE REGISTERED AND SHOW THEIR CONNECTED COMPANY AND EMPLOYEE POSITION, IF THEY HAVE ONE.
SELECT u.`firstname`, u.`lastname`, u.`email`, c.`name` AS CompanyName, cp.`name` AS CompanyRole FROM `user` u LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON e.CompanyID = c.CompanyID LEFT JOIN `companyposition` cp ON cp.PositionID = e.PositionID ORDER BY c.`name`;
#SELECT ROOM EQUIPMENT IN A SPECIFIC MEETING ROOM BASED ON MEETING ROOM NAME
SELECT re.amount, e.`name`, e.`description` FROM `equipment` e JOIN `roomequipment` re JOIN `meetingroom` m WHERE m.meetingroomid = re.meetingroomid AND re.EquipmentID = e.EquipmentID AND m.`name` = 'Meeting Room Name';
#SELECT THE USER INFORMATION BASED ON THE SELECTED USER, IF ACCOUNT IS ACTIVE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `userID` = <userID> AND `isActive` = 1;
#SELECT THE USER INFORMATION BASED ON THE SELECTED USER, IF ACCOUNT IS INACTIVE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `userID` = <userID> AND `isActive` = 0;
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT ARE ACTIVE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `isActive` = 1;
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT ARE INACTIVE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `isActive` = 0;
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS A FIRST AND LASTNAME THAT CONTAINS THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `firstName` LIKE '%SomeLetters%' AND `lastName` LIKE '%SomeLetters%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS A FIRST AND LASTNAME THAT MATCHES EXACTLY THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `firstName` = 'SomeLetters' AND `lastName` = 'SomeLetters';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS A FIRST AND LASTNAME THAT BEGINS WITH THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `firstName` LIKE 'SomeLetters%' AND `lastName` LIKE 'SomeLetters%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS A FIRST AND LASTNAME THAT ENDS WITH THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `firstName` LIKE '%SomeLetters' AND `lastName` LIKE '%SomeLetters';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS AN EMAIL THAT CONTAINS THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `email` LIKE '%SomeLetters%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS AN EMAIL THAT MATCHES EXACTLY THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `email` = 'SomeLetters';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS AN EMAIL THAT STARTS WITH THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `email` LIKE 'SomeLetters%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAS AN EMAIL THAT ENDS WITH THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `email` LIKE '%SomeLetters';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT WERE CREATED BEFORE THE SELECTED DATE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `create_time` < 'Some Date in the year-month-day format';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT WERE CREATED AFTER THE SELECTED DATE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `create_time` > 'Some Date in the year-month-day format';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT WERE CREATED ON THE SELECTED DATE
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `create_time` LIKE 'Some Date in the year-month-day format%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAVE A DISPLAY NAME THAT CONTAINS THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `displayName` LIKE '%SomeLetters%';
#SELECT THE USER INFORMATION FOR ALL ACCOUNTS THAT HAVE A DEFAULT BOOKING DESCRIPTION THAT CONTAINS THE SEARCHED STRING
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `bookingDescription` LIKE '%SomeLetters%';
#SELECT THE USER INFORMATION FOR THE ACCOUNT THAT HAS THE EXACT BOOKING NUMBER THAT WAS SEARCHED FOR
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE `bookingCode` = <someBookingNumber/someSha256HashString>;
#SELECT THE USER INFORMATION FOR ALL THE ACCOUNTS THAT HAS THE SELECTED ACCESSID
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE u.`AccessID` = <someAccessID>;
#SELECT THE USER INFORMATION FOR ALL THE ACCOUNTS THAT HAS THE SELECTED ACCESSID
SELECT u.`firstname`, u.`lastname`, u.`email`, a.`AccessName`, u.`displayname`, u.`bookingdescription`, u.`create_time` FROM `user` u JOIN `accesslevel` a ON u.AccessID = a.AccessID WHERE a.AccessName = 'AnAccessName';
#SELECT BOOKING OVERVIEW (FOR ADMIN) FOR ALL USERS WHO HAVE CREATED A BOOKING AND LIST HOW MANY THEY HAVE MADE, HOW MANY HAVE BEEN CANCELLED, HOW MANY HAVE BEEN COMPLETED AND HOW MANY ARE STILL ACTIVE
SELECT u.`firstName`, u.`lastName`, u.`email`, count(b.userID) AS BookingsCreated, count(b.dateTimeCancelled) AS BookingsCancelled, count(b.actualEndDateTime) AS BookingsCompleted, count(b.userID) - count(b.dateTimeCancelled) - count(b.actualEndDateTime) AS ActiveBookings FROM `booking` b JOIN `user` u ON b.userID = u.userID GROUP BY b.userID;
#SELECT ALL BOOKINGS (FOR ADMIN), WHICH SHOWS ROOM NAME, TIME PERIOD IT WAS/IS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION, TIME IT WAS CREATED, CANCELLATION DATE IF CANCELLED AND TIME THE MEETING ENDED IF IT HAS ENDED
SELECT b.bookingID, m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, GROUP_CONCAT(c.`name` separator ', ') AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID GROUP BY b.bookingID;
#SELECT ALL ACTIVE BOOKINGS (FOR ADMIN), WHICH SHOWS ROOM NAME, TIME PERIOD IT IS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION AND TIME IT WAS CREATED
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL;
#SELECT ACTIVE BOOKINGS (FOR ADMIN) BEFORE THE SELECTED TIME
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND (b.endDateTime < 'Some date in format year-month-day hour:minute:second');
#SELECT ACTIVE BOOKINGS (FOR ADMIN) AFTER THE SELECTED TIME
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND (b.endDateTime > 'Some date in format year-month-day hour:minute:second');
#SELECT ACTIVE BOOKINGS (FOR ADMIN) BETWEEN TWO SELECTED TIMES
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND (b.endDateTime BETWEEN 'Some date in format year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second');
#SELECT ALL BOOKINGS (FOR THE SELECTED USER)
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS YourDisplayedName, b.description AS YourBookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.userID = <userID>;
#SELECT ACTIVE BOOKINGS (FOR THE IN-HOUSE USER AND ABOVE), WHICH SHOWS THE TIME PERIOD IT IS BOOKED FOR, DISPLAYNAME OF WHO BOOKED IT, WHAT COMPANY THEY BELONG TOO (IF ANY) AND THEIR SELECTED BOOKING DESCRIPTION
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL;
#SELECT ACTIVE BOOKINGS (FOR NORMAL USER), WHICH SHOWS THE ROOM NAME AND TIME PERIOD IT IS BOOKED FOR
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL;
#SELECT COMPLETED BOOKINGS (FOR ADMIN), WHICH SHOWS ROOM NAME, TIME PERIOD IT WAS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION, TIME IT WAS CREATED AND TIME THE MEETING ENDED
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS MeetingEndedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NOT NULL;
#SELECT COMPLETED BOOKINGS (FOR IN-HOUSE USER AND ABOVE), WHICH SHOWS ROOM NAME, TIME PERIOD IT WAS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION, TIME IT WAS CREATED AND TIME THE MEETING ENDED
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.actualEndDateTime AS MeetingEndedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NOT NULL;
#SELECT CANCELLED BOOKINGS (FOR ADMIN), WHICH SHOWS ROOM NAME, TIME PERIOD IT WAS/IS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION, TIME IT WAS CREATED, CANCELLATION DATE IF CANCELLED AND TIME THE MEETING ENDED IF IT HAS ENDED
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NOT NULL;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED COMPANY (FOR IN-HOUSE USER AND ABOVE), WHICH SHOWS THE TIME PERIOD IT WAS/IS BOOKED FOR, DISPLAYNAME OF WHO BOOKED IT, WHAT COMPANY THEY BELONG TOO (IF ANY) AND THEIR SELECTED BOOKING DESCRIPTION - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND c.CompanyID = <companyID>;
#SELECT ACTIVE BOOKINGS (FOR IN-HOUSE USER AND ABOVE) BEFORE A GIVEN TIME - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime < 'Some Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS (FOR IN-HOUSE USER AND ABOVE) AFTER A GIVEN TIME - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime > 'Some Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS (FOR IN-HOUSE USER AND ABOVE) BETWEEN TWO TIMES - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN 'Some Date in the year-month-day hour:minute:second format' AND 'Another Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS (FOR NORMAL USER) BEFORE A GIVEN TIME - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime < 'Some Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS (FOR NORMAL USER) AFTER A GIVEN TIME - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime > 'Some Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS (FOR NORMAL USER) BETWEEN TWO TIMES - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN 'Some Date in the year-month-day hour:minute:second format' AND 'Another Date in the year-month-day hour:minute:second format';
#SELECT ACTIVE BOOKINGS FOR THE SELECTED MEETING ROOM (FOR NORMAL USER) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND m.`name` = 'Meeting Room Name';
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.meetingRoomID = <meetingRoomID>;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED MEETING ROOM (FOR IN-HOUSE USERS AND ABOVE) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND m.`name` = 'Meeting Room Name';
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.meetingRoomID = <meetingRoomID>;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED USER (FOR ADMIN) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.userID = <userID>;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED COMPANY (FOR ADMIN) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.companyID = <companyID>;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED EQUIPMENT (FOR IN-HOUSE USERS AND ABOVE) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND eq.`name` = 'Equipment Name';
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND re.EquipmentID = <equipmentID>;
#SELECT ACTIVE BOOKINGS FOR THE SELECTED EQUIPMENT (FOR NORMAL USERS) - TEMPLATE
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND eq.`name` = 'Equipment Name';
SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID LEFT JOIN `roomequipment` re ON  re.MeetingRoomID = m.meetingRoomID LEFT JOIN `equipment` eq ON eq.EquipmentID = re.EquipmentID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND re.EquipmentID = <equipmentID>;
#SELECT ALL LOG EVENTS, WHICH DISPLAYS TIME, ACTION AND DESCRIPTIONS OF ACTION AND THE LOG EVENT
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription FROM `logevent` l JOIN `logaction` la ON la.actionID = l.actionID;
#SELECT ALL LOG EVENTS ABOUT USER ACCOUNTS
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, w.ip AS ConnectedIP, u.create_time AS DateCreated FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` LIKE 'Account%';
#SELECT ALL LOG EVENTS ABOUT USER ACOUNTS CREATED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, w.ip AS ConnectedIP, u.create_time AS DateCreated FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` = 'Account Created';
#SELECT ALL LOG EVENTS ABOUT USER ACOUNTS REMOVED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, w.ip AS ConnectedIP FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` = 'Account Removed';
#SELECT ALL LOG EVENTS ABOUT BOOKINGS
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, c.`name` AS BookedForCompanyName, b.startDateTime AS BookingStartTime, b.endDateTime AS BookingEndTime, m.`name` AS MeetingRoom, b.dateTimeCancelled AS DateIfCancelled, b.actualEndDateTime AS DateIfCompleted, w.ip AS BookedFromIPAddress FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `booking` b ON b.bookingID = l.bookingID LEFT JOIN `meetingroom` m ON m.meetingRoomID = l.meetingRoomID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` LIKE 'Booking%';
#SELECT ALL LOG EVENTS ABOUT BOOKINGS CREATED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, c.`name` AS BookedForCompanyName, b.startDateTime AS BookingStartTime, b.endDateTime AS BookingEndTime, m.`name` AS MeetingRoom, w.ip AS BookedFromIPAddress FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `booking` b ON b.bookingID = l.bookingID LEFT JOIN `meetingroom` m ON m.meetingRoomID = l.meetingRoomID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE b.bookingID = l.bookingID AND la.`name` = 'Booking Created';
#SELECT ALL LOG EVENTS ABOUT BOOKINGS CANCELLED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, c.`name` AS BookedForCompanyName, b.startDateTime AS BookingStartTime, b.endDateTime AS BookingEndTime, m.`name` AS MeetingRoom, b.dateTimeCancelled AS DateCancelled, w.ip AS CancelledFromIPAddress FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `booking` b ON b.bookingID = l.bookingID LEFT JOIN `meetingroom` m ON m.meetingRoomID = l.meetingRoomID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE b.bookingID = l.bookingID AND la.`name` = 'Booking Cancelled';
#SELECT ALL LOG EVENTS ABOUT BOOKINGS COMPLETED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, u.firstName, u.lastName, u.email, c.`name` AS BookedForCompanyName, b.startDateTime AS BookingStartTime, b.endDateTime AS BookingEndTime, m.`name` AS MeetingRoom, b.actualEndDateTime AS CompletionDate FROM `logevent` l LEFT JOIN `user` u ON u.userID = l.userID LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `booking` b ON b.bookingID = l.bookingID LEFT JOIN `meetingroom` m ON m.meetingRoomID = l.meetingRoomID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE b.bookingID = l.bookingID AND la.`name` = 'Booking Completed';
#SELECT ALL LOG EVENTS ABOUT COMPANIES
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, c.`name` AS CompanyName, c.dateTimeCreated AS CreationDate, u.firstName, u.lastName, u.email, w.ip AS ConnectedIPAddress FROM `logevent` l LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `employee` e ON e.CompanyID = l.CompanyID LEFT JOIN `user` u ON l.userID = e.UserID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` LIKE 'Company%' GROUP BY l.logID;
#SELECT ALL LOG EVENTS ABOUT COMPANIES CREATED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, c.`name` AS CompanyName, c.dateTimeCreated AS CreationDate, u.firstName, u.lastName, u.email, w.ip AS ConnectedIPAddress FROM `logevent` l LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `employee` e ON e.CompanyID = l.CompanyID LEFT JOIN `user` u ON l.userID = e.UserID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` = 'Company Created' GROUP BY l.logID;
#SELECT ALL LOG EVENTS ABOUT COMPANIES CREATED
SELECT l.logDateTime AS LogDate, la.`name` AS ActionName, la.description AS ActionDescription, l.description AS LogDescription, c.`name` AS CompanyName, c.dateTimeCreated AS CreationDate, u.firstName, u.lastName, u.email, w.ip AS ConnectedIPAddress FROM `logevent` l LEFT JOIN `company` c ON c.CompanyID = l.companyID LEFT JOIN `employee` e ON e.CompanyID = l.CompanyID LEFT JOIN `user` u ON l.userID = e.UserID LEFT JOIN `websession` w ON w.sessionID = l.sessionID LEFT JOIN `logaction` la ON la.actionID = l.actionID WHERE la.`name` = 'Company Removed' GROUP BY l.logID;
#SELECT ADD-ONS AFTER (WHERE) FOR SEARCHING
#FOR THE SELECTED USER
WHERE l.userID = <userID>;
#BETWEEN TWO SELECTED DATES
WHERE l.logDateTime BETWEEN 'year-month-day hour:minute:second' AND 'year-month-day hour:minute:second';
#AFTER THE SELECTED TIME
WHERE l.logDateTime > 'year-month-day hour:minute:second';
#BEFORE THE SELECTED TIME
WHERE l.logDateTime < 'year-month-day hour:minute:second';
#END OF SELECT QUERIES
#
#START OF DELETE QUERIES
#
#DELETE THE SELECTED USER - TEMPLATE
DELETE FROM `user` WHERE userID = <userID>;
#DELETE THE SELECTED COMPANY - TEMPLATE
DELETE FROM `company` WHERE `CompanyID` = <companyID>;
#DELETE THE SELECTED COMPANY IF THEY HAVE BEEN SET TO BE DELETED AT A SPECIFIC DATE, AND THAT DATE HAS BEEN REACHED - TEMPLATE
DELETE FROM `company` WHERE `removeAtDate` IS NOT NULL AND `removeAtDate` < CURRENT_TIMESTAMP AND `CompanyID` <> 0;
#DELETE THE SELECTED BOOKING INFORMATION - TEMPLATE
DELETE FROM `booking` WHERE `bookingID` = <bookingID>;
#DELETE ALL BOOKINGS THAT WERE CANCELLED OR ENDED x DAYS AGO - TEMPLATE
DELETE FROM `booking` WHERE `bookingID` <> 0 AND ((`actualEndDateTime` < CURDATE() - INTERVAL x DAY) OR  (`dateTimeCancelled` < CURDATE() - INTERVAL x DAY));
#DELETE THE EMPLOYEE STATUS OF THE SELECTED USER IN THE SELECTED COMPANY - TEMPLATE
DELETE FROM `employee` WHERE `UserID` = <userID> AND `companyID` = <companyID>;
#DELETE THE SELECTED MEETING ROOM - TEMPLATE
DELETE FROM `meetingroom` WHERE `meetingRoomID` = <meetingRoomID>;
#DELETE THE SELECTED EQUIPMENT FROM THE SELECTED ROOM - TEMPLATE
DELETE FROM `roomequipment` WHERE `MeetingRoomID` = <meetingRoomID> AND `equipmentID` = <equipmentID>;
#DELETE THE SELECTED EQUIPMENT FROM BEING AVAILABLE FOR MEETING ROOMS - TEMPLATE
DELETE FROM `equipment` WHERE `EquipmentID` = <equipmentID>;
#DELETE ALL LOG EVENTS THAT ARE OLDER THAN x DAYS - TEMPLATE
DELETE FROM `logevent` WHERE (`logDateTime` < CURDATE() - INTERVAL x DAY) AND `logID` <> 0;
#DELETE THE SELECTED LOGACTION (SETS EXISTING LOGEVENT ACTONS AS NULL) - TEMPLATE
DELETE FROM `logaction` WHERE `actionID` = <actionID>;
#DELETE THE SELECTED COMPANY POSITION - TEMPLATE
DELETE FROM `companyposition` WHERE `PositionID` = <positionID>;
#DELETE THE SELECTED ACCESS LEVEL - TEMPLATE
DELETE FROM `accesslevel` WHERE `AccessID` = <accessID>;
#
#END OF DELETE QUERIES