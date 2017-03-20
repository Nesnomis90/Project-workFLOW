#START OF INSERT QUERIES
#
#INSERT DATA INTO USER (firstname, lastname, email, hashed password, accessID and activationcode)
INSERT INTO `user`(`email`, `password`, `firstname`, `lastname`, `accessID`, `activationcode`) VALUES ('User Email', 'SHA 256 hashed password', 'First Name', 'Last Name', <accessID should be 4>, 'SHA 256 hash length activation code');
#INSERT A NEW COMPANY
INSERT INTO `company`(`name`) VALUES ('Company Name');
#INSERT A NEW BOOKING OF A MEETING ROOM BASED ON THE SELECTED MEETING ROOM AND THE USER WHO CREATED IT
INSERT INTO `booking`(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES ((SELECT `meetingRoomID` FROM `meetingroom` WHERE `name` = 'Meeting Room Name'), (SELECT `userID` FROM `user` WHERE `email` = 'Email'), 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description');
INSERT INTO `booking`(`meetingRoomID`, `userID`, `displayName`, `startDateTime`, `endDateTime`, `description`) VALUES (<meetingroomID>, <userID>, 'Display Name', '2017-03-15 16:00:00', '2017-03-15 17:30:00', 'Booking Description');
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
#
#END OF UPDATE DATA
#
#START OF SELECT QUERIES
#
#SELECT USERS AND THEIR POSITION WITHIN A SPECIFIC COMPANY BASED ON COMPANY NAME
SELECT u.`firstName`, u.`lastName`, cp.`name`, e.`startDateTime` FROM `company` c JOIN `companyposition` cp JOIN `employee` e JOIN `user` u WHERE u.userID = e.UserID AND e.CompanyID = c.CompanyID AND cp.PositionID = e.PositionID AND c.`name` = 'Company Name';
#SELECT BOOKING TIME USED BY A USER BASED ON FIRST/LAST NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`firstName` = 'First Name' AND u.`lastName` = 'Last Name';
#SELECT BOOKING TIME USED BY A USER BASED ON USER EMAIL
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS BookingTimeUsed FROM `booking` b INNER JOIN `user` u ON b.`UserID` = u.`UserID` WHERE u.`email` = 'Email';
#SELECT BOOKING TIME USED BY ENTIRE COMPANY BASED ON COMPANY NAME
SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`endDateTime`) - TIME_TO_SEC(b.`startDateTime`)))  AS CompanyWideBookingTimeUsed FROM `booking` b INNER JOIN `employee` e ON b.`UserID` = e.`UserID` INNER JOIN `company` c ON e.`CompanyID` = c.`CompanyID` WHERE c.`name` = 'Company Name';
#SELECT ALL COMPANY NAMES AND THE NUMBER OF EMPLOYEES IT HAS
SELECT c.`name`, COUNT(c.`name`) AS NumberOfEmployees FROM `company` c JOIN `employee` e WHERE c.CompanyID = e.CompanyID GROUP BY c.`name`;
#SELECT ALL USERS THAT ARE REGISTERED AS AN EMPLOYEE AND THE COMPANY AND POSITION THEY HOLD
SELECT u.`firstname`, u.`lastname`, u.`email`, c.`name` AS CompanyName, cp.`name` AS CompanyRole FROM `user` u JOIN `company` c JOIN `employee` e JOIN `companyposition` cp WHERE e.CompanyID = c.CompanyID AND e.UserID = u.userID AND cp.PositionID = e.PositionID ORDER BY c.`name`;
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
#
#END OF SELECT QUERIES
#