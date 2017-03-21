#START OF CREATE TABLE QUERIES
#
#

#
#END OF CREATE TABLE QUERIES
#
#START OF INSERT QUERIES
#
#INSERT DATA INTO USER (firstname, lastname, email, hashed password, accessID and activationcode)
INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`, `accessID`, `activationcode`) VALUES ('User Email', 'SHA 256 hashed password', 'First Name', 'Last Name', <accessID should be 4>, 'SHA 256 hash length activation code');
#INSERT A NEW COMPANY
INSERT INTO `company`(`name`) VALUES ('Company Name');
#INSERT A NEW BOOKING OF A MEETING ROOM BASED ON THE SELECTED MEETING ROOM AND THE USER WHO CREATED IT
INSERT INTO `booking`(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', 'ecd71870d1963316a97e3ac3408c9835ad8cf0f3c1bc703527c30265534f75ae');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`, `cancellationCode`) VALUES (<meetingroomID>, <userID>, 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description', '64 char SHA256 code');
#INSERT A NEW EMPLOYEE IN A COMPANY, IF THE COMPANY ALREADY EXISTS, BASED ON AN EXISTING USER AND SETTING THEIR COMPANY ROLE
INSERT INTO `employee`(`CompanyID`, `UserID`, `PositionID`) VALUES ((SELECT `CompanyID` FROM `company` WHERE `name` = 'test1'),(SELECT `userID` FROM `user` WHERE `email` = 'test10@test.com'), (SELECT `PositionID` FROM `companyposition` WHERE `name` = 'Employee'));
INSERT INTO `employee`(`CompanyID`, `UserID`, `PositionID`) VALUES (<companyID>, <userID>, (SELECT `PositionID` FROM `companyposition` WHERE `name` = 'Company Position'));
#INSERT A NEW MEETING ROOM INTO THE SYSTEM
INSERT INTO `meetingroom`(`name`, `capacity`, `description`, `location`) VALUES ('MeetingRoom Name', <capacityNumber>, 'MeetingRoom Description', 'Image url of location');
#INSERT NEW EQUIPMENT INTO A MEETING ROOM WITH THE AMOUNT OF THAT EQUIPMENT
INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES((SELECT `EquipmentID` FROM `equipment` WHERE `name` = 'Equipment Name'), (SELECT `MeetingRoomID` FROM `meetingroom` WHERE `name`= 'Meeting Room Name'), 1);
INSERT INTO `roomequipment`(`EquipmentID`, `MeetingRoomID`, `amount`) VALUES(<equipmentID>, <meetingroomID>, <amountNumber>);
#INSERT NEW EQUIPMENT TO CHOOSE FROM
INSERT INTO `equipment`(`name`, `description`) VALUES('Equipment Name','Equipment Description');
#INSERT ACCESSLEVEL BACKEND ONLY
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Admin', 'Full website access.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Company Owner', 'Full company information and management.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('In-House User', 'Can book meeting rooms with a booking code.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Normal User', 'Can browse meeting room schedules, with limited information, and request a booking.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('Meeting Room', 'These are special accounts used to handle booking code login.');
INSERT INTO `accesslevel`(`AccessName`, `Description`) VALUES ('AccessName', 'Access Description.');
#INSERT LOGACTION BACKEND ONLY
INSERT INTO `logaction`(`name`,`description`) VALUES ('An action name','A description of what that action should apply to');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Created','The referenced user created a new meeting room booking.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Booking Cancelled','The referenced user cancelled a meeting room booking.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Created','The referenced user just registered an account.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Account Removed','');
INSERT INTO `logaction`(`name`,`description`) VALUES ('An action name','A description of what that action should apply to');
INSERT INTO `logaction`(`name`,`description`) VALUES ('An action name','A description of what that action should apply to');
#
#END OF INSERT QUERIES
#
#START OF UPDATE DATA
#
#UPDATE EMAIL OF SELECTED USER
UPDATE `user` SET `email` = <newEmail> WHERE `userID` = <UserID>;
#UPDATE PASSWORD OF SELECTED USER
UPDATE `user` SET `password` = <newPassword> WHERE `userID` = <userID>;
#UPDATE ACCESSID OF SELECTED USER
UPDATE `user` SET `AccessID` = <newAccessID> WHERE `userID` = <userID>;
#UPDATE FIRST AND LASTNAME OF SELECTED USER
UPDATE `user` SET `firstname` = 'NewFirstName', `lastname` = 'NewLastName' WHERE `userID` = <userID>;
#UPDATE DISPLAYNAME OF SELECTED USER
UPDATE `user` SET `displayName` = 'NewDisplayName' WHERE `userID` = <userID>;
#UPDATE DEFAULT BOOKING DESCRIPTION OF SELECTED USER
UPDATE `user` SET `bookingDescription` = 'NewBookingDescription' WHERE `userID` = <userID>;
#UPDATE BOKING CODE OF SELECTED USER
UPDATE `user` SET `bookingCode` = <newCodeNumber> WHERE `userID` = <userID>;
#UPDATE THE DATETIME OF THE LAST ACTIVITY OF THE SELECTED USER
UPDATE `user` SET `lastActivity` = CURRENT_TIMESTAMP WHERE `userID` = <userID>;
#UPDATE THE TEMPORARY PASSWORD AND THE DATETIME IT WAS ACTIVATED FOR THE SELECTED USER
UPDATE `user` SET `tempPassword` = 'newTempPassword', `dateRequested` = CURRENT_TIMESTAMP WHERE `userID` = <userID>;
#UPDATE THE USER ACCOUNT TO BE ACTIVE (ALLOWED TO LOG IN TO THE WEBSITE)
UPDATE `user` SET `isActive` = 1 WHERE `userID` = <userID>;
#UPDATE THE COMPANY NAME FOR THE SELECTED COMPANY
UPDATE `company` SET `name` = 'New Company Name' WHERE `CompanyID` = <CompanyID>;
#UPDATE THE COMPANY INFORMATION WITH A DATE WHEN THE SELECTED COMPANY SHOULD BE AUTOMATICALLY REMOVED
UPDATE `company` SET `removeAtDate` = 'some new date in the format year-month-day' WHERE `companyID` = <CompanyID>;
#UPDATE THE ACTIVE BOOKINGS TO BE SET AS COMPLETED WHEN THE TIME HAS GONE PAST THEIR SCHEDULED ENDING TIME
UPDATE `booking` SET actualEndDateTime = endDateTime WHERE actualEndDateTime IS NULL AND dateTimeCancelled IS NULL AND endDateTime < CURRENT_TIMESTAMP AND bookingID <> 0;
#UPDATE THE BOOKING TO ACKNOWLEDGE THAT IT HAS BEEN CANCELLED BY THE SELECTED USER
UPDATE `booking` SET dateTimeCancelled = CURRENT_TIMESTAMP WHERE bookingID = <bookingID>;
#UPDATE THE DISPLAY NAME OF THE SELECTED BOOKING
UPDATE `booking` SET `displayName` = 'new Display Name' WHERE bookingID = <bookingID>;
#UPDATE THE BOOKING DESCRIPTION OF THE SELECTED BOOKING
UPDATE `booking` SET `description` = 'new Booking Description' WHERE bookingID = <bookingID>;
#
#END OF UPDATE DATA
#
#START OF SELECT QUERIES
#
#SELECT USERS AND THEIR POSITION WITHIN A SPECIFIC COMPANY BASED ON COMPANY NAME
SELECT u.`firstName`, u.`lastName`, cp.`name`, e.`startDateTime` FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID AND c.`name` = 'Company Name';
#SELECT BOOKING TIME USED BY THE SELECTED USER
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`userID` = <userID>;
#SELECT BOOKING TIME USED BY A USER BASED ON FIRST/LAST NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`firstName` = 'First Name' AND u.`lastName` = 'Last Name';
#SELECT BOOKING TIME USED BY A USER BASED ON EMAIL
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`email` = 'Email';
#SELECT BOOKING TIME USED BY THE SELECTED USER BASED ON A TIMESPAN OF TWO DATES
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE b.actualEndDateTime BETWEEN 'Some date in formay year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second' AND u.`userID` = <userID>;
#SELECT BOOKING TIME USED BY ENTIRE COMPANY BASED ON SELECTED COMPANY
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`CompanyID` = <companyID>;
#SELECT BOOKING TIME USED BY THE SELECTED COMPANY BASED ON A TIMESPAN OF TWO DATES
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE b.`actualEndDateTime` BETWEEN 'Some date in formay year-month-day hour:minute:second' AND 'Another date in format year-month-day hour:minute:second' AND c.`CompanyID` = <companyID>;
#SELECT BOOKING TIME USED BY ENTIRE COMPANY BASED ON COMPANY NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`name` = 'Company Name';
#SELECT ALL COMPANY NAMES AND THE NUMBER OF EMPLOYEES IT HAS
SELECT c.`name`, COUNT(c.`name`) AS NumberOfEmployees FROM `company` c JOIN `employee` e ON c.CompanyID = e.CompanyID GROUP BY c.`name`;
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
#SELECT THE OVERVIEW OVER ALL USERS WHO HAVE CREATED A BOOKING AND LIST HOW MANY THEY HAVE MADE, HOW MANY HAVE BEEN CANCELLED, HOW MANY HAVE BEEN COMPLETED AND HOW MANY ARE STILL ACTIVE
SELECT u.firstName, u.lastName, u.email, count(b.userID) AS BookingsCreated, count(b.dateTimeCancelled) AS BookingsCancelled, count(b.actualEndDateTime) AS BookingsCompleted, count(b.userID) - count(b.dateTimeCancelled) - count(b.actualEndDateTime) AS ActiveBookings FROM `booking` b JOIN `user` u ON b.userID = u.userID GROUP BY b.userID;
#SELECT THE ADMIN OVERVIEW OVER ALL BOOKINGS, WHICH SHOWS TIME PERIOD IT WAS/IS BOOKED FOR, DISPLAYNAME, CONNECTED COMPANY, BOOKING DESCRIPION, DATE IT WAS CREATED AND CANCELLATION DATE IF CANCELLED
SELECT b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, u.firstName, u.lastName, u.email, c.`name` AS WorksForCompany, b.description AS BookingDescription, b.dateTimeCreated AS BookingWasCreatedOn, b.actualEndDateTime AS BookingWasCompletedOn, b.dateTimeCancelled AS BookingWasCancelledOn FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID;
#SELECT THE OVERVIEW OVER ALL ACTIVE BOOKINGS, WHICH SHOWS THE TIME PERIOD IT WAS/IS BOOKED FOR, DISPLAYNAME OF WHO BOOKED IT, WHAT COMPANY THEY BELONG TOO (IF ANY) AND THEIR SELECTED BOOKING DESCRIPTION
SELECT b.startDateTime AS StartTime, b.endDateTime AS EndTime, b.displayName AS BookedBy, c.`name` AS Company, b.description AS BookingDescription FROM `booking` b LEFT JOIN `meetingroom` m ON b.meetingRoomID = m.meetingRoomID LEFT JOIN `user` u ON u.userID = b.userID LEFT JOIN `employee` e ON e.UserID = u.userID LEFT JOIN `company` c ON c.CompanyID = e.CompanyID WHERE b.dateTimeCancelled IS NULL AND b.actualEndDateTime IS NULL;
#
#END OF SELECT QUERIES
#