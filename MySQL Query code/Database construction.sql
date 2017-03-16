CREATE TABLE `user` (
  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `create_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `firstName` varchar(45) DEFAULT NULL,
  `lastName` varchar(45) DEFAULT NULL,
  `displayName` varchar(45) DEFAULT NULL,
  `bookingDescription` text,
  `numberCode` varchar(10) DEFAULT NULL,
  `tempPassword` varchar(50) DEFAULT NULL,
  `DateRequested` datetime DEFAULT NULL,
  `AccessID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`),
  KEY `FK_AccessID_idx` (`AccessID`),
  CONSTRAINT `FK_AccessID` FOREIGN KEY (`AccessID`) REFERENCES `accesslevel` (`AccessID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


CREATE TABLE `meetingroom` (
  `meetingRoomID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `capacity` int(10) unsigned DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`meetingRoomID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;


CREATE TABLE `employee` (
  `CompanyID` int(10) unsigned NOT NULL,
  `UserID` int(10) unsigned NOT NULL,
  `startDateTime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `PositionID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`UserID`,`CompanyID`),
  KEY `FK_CompanyID_idx` (`CompanyID`),
  KEY `FK_PositionID_idx` (`PositionID`),
  CONSTRAINT `FK_CompanyID` FOREIGN KEY (`CompanyID`) REFERENCES `company` (`CompanyID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_UserID` FOREIGN KEY (`UserID`) REFERENCES `user` (`userID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SELECT * FROM test.employee;

CREATE TABLE `companyposition` (
  `PositionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`PositionID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE `company` (
  `CompanyID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `dateTimeCreated` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `bookingTimeUsedThisMonth` int(10) unsigned DEFAULT NULL,
  `removeAtDate` datetime DEFAULT NULL,
  PRIMARY KEY (`CompanyID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;


CREATE TABLE `booking` (
  `bookingID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meetingRoomID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned DEFAULT NULL,
  `displayName` varchar(45) DEFAULT NULL,
  `dateTimeCreated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dateTimeCancelled` timestamp NULL DEFAULT NULL,
  `startDateTime` datetime NOT NULL,
  `endDateTime` datetime NOT NULL,
  `description` text,
  PRIMARY KEY (`bookingID`),
  KEY `FK_MeetingRoomID_idx` (`meetingRoomID`),
  KEY `FK_UserID_idx` (`userID`),
  KEY `FK_UserID2_idx` (`userID`),
  CONSTRAINT `FK_MeetingRoomID` FOREIGN KEY (`meetingRoomID`) REFERENCES `meetingroom` (`meetingRoomID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `FK_UserID2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE `accesslevel` (
  `AccessID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AccessName` varchar(45) DEFAULT NULL,
  `Description` text,
  PRIMARY KEY (`AccessID`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

