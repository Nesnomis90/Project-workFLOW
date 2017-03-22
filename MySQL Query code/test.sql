USE test;

INSERT INTO employee(`CompanyID`, `UserID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test5'),(SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'));

SELECT `userID` FROM `user` WHERE `email` = 'test@test.com';

SELECT `CompanyID` FROM `company` WHERE `name` = 'test5';

SELECT * FROM `employee`;

SELECT * FROM `user`;

SELECT * FROM `company`;

SELECT * FROM `companyposition`;

SELECT * FROM `booking`;

SELECT * FROM `meetingroom`;

SELECT * FROM `roomequipment`;

SELECT * FROM `equipment`;

SELECT * FROM `accesslevel`;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN '2017-03-22 14:20:00' AND '2017-03-30 14:30:00';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND b.endDateTime BETWEEN '2017-03-22 14:20:00' AND '2017-03-30 14:30:00';

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL AND CURRENT_TIMESTAMP < b.endDateTime AND c.CompanyID = 4;

SELECT m.`name` AS BookedRoomName, b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS YourDisplayedName, b.description AS YourBookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.userID = 1;

INSERT INTO meetingroom(`name`, `capacity`, `description`) VALUES ('Toillpeis', 3, 'You must be a real toillpeis to have booked to room!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'This booking is just to look at the COOL VIEW!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-16 12:00:00', '2017-03-16 13:30:00', 'I could not find a better room');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-16 13:30:00', '2017-03-16 14:00:00', 'Someone told me this has a cool view');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-21 12:00:00', '2017-03-21 13:30:00', 'I could not find a better room', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test11@test.com'), 'NEED IT!', '2017-03-22 14:30:00', '2017-03-22 15:30:00', '...?', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

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

UPDATE `meetingroom` SET `EquipmentID` = ((SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'HDTV')) WHERE `EquipmentID` = 0;

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

INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`,`accessID`, `activationcode`) VALUES ('test15@test.com', '123test', 'testy15', 'mctester15', 4, 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');

SELECT u.`firstname`, u.`lastname`, u.`email`, c.`name` AS CompanyName, cp.`name` AS CompanyRole FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY c.`name` ;

SELECT `firstname`, `lastname`, `email`, (SELECT c.`name` FROM `company` c JOIN `employee` e JOIN `companyposition`cp JOIN `user` u WHERE c.CompanyID = e.CompanyID AND u.userID = e.UserID) AS CompanyName, (SELECT cp.`name` FROM `companyposition` cp JOIN `company` c JOIN `employee` e JOIN `user` u WHERE c.companyID = e.companyID AND u.userid = e.userid AND cp.positionID = e.positionID) AS CompanyRole FROM `user`;

SELECT DISTINCT u.userID FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY u.userid ASC;

SELECT c.`name` FROM `company` c JOIN `employee` e JOIN `companyposition`cp JOIN `user` u WHERE c.CompanyID = e.CompanyID AND u.userID = e.UserID;

SELECT cp.`name` FROM `companyposition` cp JOIN `company` c JOIN `employee` e JOIN `user` u WHERE c.companyID = e.companyID AND u.userid = e.userid AND cp.positionID = e.positionID;

SELECT re.amount, e.`name`, e.`description` FROM `equipment` e JOIN `roomequipment` re JOIN `meetingroom` m WHERE m.meetingroomid = re.meetingroomid AND re.EquipmentID = e.EquipmentID AND m.`name` = 'Blåmann';

SELECT * FROM `equipment` e JOIN `roomequipment` re JOIN `meetingroom` m WHERE m.meetingroomid = re.meetingroomid AND re.EquipmentID = e.EquipmentID;

INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`, `accessID`, `activationcode`) VALUES ('test15@test.com', '123test', 'testy15', 'mctester15', 4, 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
