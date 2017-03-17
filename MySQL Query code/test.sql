USE test;

INSERT INTO employee(`CompanyID`, `UserID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test5'),(SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'));

SELECT `userID` FROM `user` WHERE `email` = 'test@test.com';

SELECT `CompanyID` FROM `company` WHERE `name` = 'test5';

SELECT * FROM `employee`;

SELECT * FROM `user`;

SELECT * FROM `company`;

SELECT * FROM `booking`;

SELECT * FROM `meetingroom`;

SELECT * FROM `roomequipment`;

SELECT * FROM `equipment`;

SELECT * FROM `accesslevel`;

INSERT INTO meetingroom(`name`, `capacity`, `description`) VALUES ('Toillpeis', 3, 'You must be a real toillpeis to have booked to room!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'This booking is just to look at the COOL VIEW!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-16 12:00:00', '2017-03-16 13:30:00', 'I could not find a better room');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-16 13:30:00', '2017-03-16 14:00:00', 'Someone told me this has a cool view');

DELETE FROM `booking` WHERE `bookingID` = 1;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT COUNT(*) AS CompanyBookings FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT `booking`.`bookingID`, `booking`.`startDateTime`, `booking`.`endDateTime` FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT TIMEDIFF( `booking`.`endDateTime`, `booking`.`startDateTime`) FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT SUM(TIMEDIFF( `booking`.`endDateTime`, `booking`.`startDateTime`)) FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`name` = 'test5';

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