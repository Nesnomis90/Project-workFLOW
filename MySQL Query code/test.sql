INSERT INTO employee(`CompanyID`, `UserID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test5'),(SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'));

SELECT `userID` FROM `user` WHERE `email` = 'test@test.com';

SELECT `CompanyID` FROM `company` WHERE `name` = 'test5';

SELECT * FROM `employee`;

SELECT * FROM `user`;

SELECT * FROM `company`;

SELECT * FROM `booking`;

SELECT * FROM `meetingroom`;

INSERT INTO meetingroom(`name`, `capacity`, `description`) VALUES ('Toillpeis', 3, 'You must be a real toillpeis to have booked to room!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Blåmann'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'This booking is just to look at the COOL VIEW!');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test2@test.com'), 'A real toillpeis', '2017-03-16 12:00:00', '2017-03-16 13:30:00', 'I could not find a better room');

INSERT INTO booking(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Toillpeis'), (SELECT `userID` FROM `user` WHERE `email` = 'test@test.com'), 'CoolViewGuy', '2017-03-16 13:30:00', '2017-03-16 14:00:00', 'Someone told me this has a cool view');

USE test;

DELETE FROM `booking` WHERE `bookingID` = 1;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT COUNT(*) AS CompanyBookings FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID`;

SELECT * FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT `booking`.`bookingID`, `booking`.`startDateTime`, `booking`.`endDateTime` FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT TIMEDIFF( `booking`.`endDateTime`, `booking`.`startDateTime`) FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT SUM(TIMEDIFF( `booking`.`endDateTime`, `booking`.`startDateTime`)) FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `name` = 'test5';

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(`booking`.`endDateTime`) - TIME_TO_SEC(`booking`.`startDateTime`)))  AS BookingTimeUsed FROM `booking` INNER JOIN `employee` ON `Booking`.`UserID` = `employee`.`UserID` INNER JOIN `company` ON `employee`.`CompanyID` = `company`.`CompanyID` WHERE `company`.`name` = 'test5';

INSERT INTO `companyposition`(`name`, `description`) VALUES ('Owner', 'This person has access to all company information and management.');

INSERT INTO `companyposition`(`name`, `description`) VALUES ('Employee', 'This person has access to browse company information.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Full website access.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Company Owner', 'Full company information and management.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.');

INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Meeting Room', 'These are special accounts used to handle booking code login.');