USE test;
SET NAMES utf8;
USE meetingflow;
SHOW WARNINGS;

SELECT 		b.`bookingID`									AS TheBookingID,
			b.`companyID`									AS TheCompanyID,
			b.`meetingRoomID`								AS TheMeetingRoomID,
			b.`startDateTime` 								AS StartTime, 
			b.`endDateTime` 								AS EndTime, 
			b.`description` 								AS BookingDescription,
			b.`displayName` 								AS BookedBy,
            b.`userID`										AS TheUserID,
			b.`cancellationCode`							AS CancellationCode,
            IF(b.`companyID` IS NULL, NULL, 
				(	
					SELECT `name` 
					FROM `company` 
					WHERE `companyID` = TheCompanyID
				)
            )												AS BookedForCompany,
            IF(b.`meetingRoomID` IS NULL, NULL,
				(
					SELECT 	`name`
					FROM 	`meetingroom`
					WHERE 	`meetingRoomID` = TheMeetingRoomID
				)
            ) 												AS BookedRoomName,
            IF(b.`userID` IS NULL, NULL, 
				(
					SELECT 	`firstName`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserFirstname,
			IF(b.`userID` IS NULL, NULL,
				(
					SELECT 	`lastName`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserLastname,
            IF(b.`userID` IS NULL, NULL,
				(
					SELECT 	`email`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserEmail,
            IF(b.`userID` IS NULL, NULL,
				(
					SELECT 	`email`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserEmail,
            IF(b.`userID` IS NULL, NULL,
				(
					SELECT 	`displayName`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserDefaultDisplayName,
            IF(b.`userID` IS NULL, NULL,
				(
					SELECT 	`bookingDescription`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS UserDefaultBookingDescription
FROM 		`booking` b
GROUP BY 	b.`bookingID`;

SELECT 		b.`bookingID`									AS TheBookingID,
			b.`companyID`									AS TheCompanyID,
			b.`meetingRoomID`								AS TheMeetingRoomID,
			b.`startDateTime` 								AS StartTime, 
			b.`endDateTime` 								AS EndTime, 
			b.`description` 								AS BookingDescription,
			b.`displayName` 								AS BookedBy,
			(	
				SELECT `name` 
				FROM `company` 
				WHERE `companyID` = TheCompanyID
			)												AS BookedForCompany,
			b.`cancellationCode`							AS CancellationCode,
			(
				SELECT 	`name`
                FROM 	`meetingroom`
                WHERE 	`meetingRoomID` = TheMeetingRoomID
            ) 												AS BookedRoomName,									
			u.`userID`										AS TheUserID, 
			u.`firstName`									AS UserFirstname,
			u.`lastName`									AS UserLastname,
			u.`email`										AS UserEmail,
			u.`displayName` 								AS UserDefaultDisplayName,
			u.`bookingDescription`							AS UserDefaultBookingDescription
FROM 		`booking` b
LEFT JOIN 	`user` u
ON 			b.`userID` = u.`userID`
GROUP BY 	b.`bookingID`;

SELECT 		b.`bookingID`									AS TheBookingID,
			b.`companyID`									AS TheCompanyID,
			b.`meetingRoomID`								AS TheMeetingRoomID,
			b.startDateTime 								AS StartTime, 
			b.endDateTime 									AS EndTime, 
			b.description 									AS BookingDescription,
			b.displayName 									AS BookedBy,
			(	
				SELECT `name` 
				FROM `company` 
				WHERE `companyID` = TheCompanyID
			)												AS BookedForCompany,
			b.`cancellationCode`							AS CancellationCode,
			m.`name` 										AS BookedRoomName,									
			u.`userID`										AS TheUserID, 
			u.`firstName`									AS UserFirstname,
			u.`lastName`									AS UserLastname,
			u.`email`										AS UserEmail,
			u.`displayName` 								AS UserDefaultDisplayName,
			u.`bookingDescription`							AS UserDefaultBookingDescription
FROM 		`booking` b 
LEFT JOIN 	`meetingroom` m 
ON 			b.meetingRoomID = m.meetingRoomID 
LEFT JOIN 	`company` c 
ON 			b.CompanyID = c.CompanyID
LEFT JOIN 	`user` u
ON 			b.`userID` = u.`userID`
GROUP BY 	b.`bookingID`;

SELECT 		u.`userID`, 
			u.`firstname`, 
			u.`lastname`, 
			u.`email`,
			a.`AccessName`,
			u.`displayname`,
			u.`bookingdescription`,
			(
				SELECT 		GROUP_CONCAT(CONCAT_WS(" in ", cp.`name`, CONCAT(c.`name`,".")) separator "\n")
				FROM 		`company` c
				INNER JOIN 	`employee` e
				ON 			e.`CompanyID` = c.`CompanyID`
                INNER JOIN 	`companyposition` cp
                ON 			cp.`PositionID` = e.`PositionID`
				WHERE  		e.`userID` = u.`userID`
				AND			c.`isActive` = 1
				GROUP BY 	e.`userID`
            )																					AS WorksFor,
			u.`create_time`								 										AS DateCreated,
			u.`isActive`,
			u.`lastActivity`							 										AS LastActive,
			u.`reduceAccessAtDate`																AS ReduceAccessAtDate
FROM 		`user` u
INNER JOIN	`accesslevel` a
ON 			u.`AccessID` = a.`AccessID`
ORDER BY 	u.`userID`
DESC;

SELECT 		u.`userID`, 
			u.`firstname`, 
			u.`lastname`, 
			u.`email`,
			a.`AccessName`,
			u.`displayname`,
			u.`bookingdescription`,
			GROUP_CONCAT(CONCAT_WS(" in ", cp.`name`, CONCAT(c.`name`,".")) separator "\n") 	AS WorksFor,
			u.`create_time`								 										AS DateCreated,
			u.`isActive`,
			u.`lastActivity`							 										AS LastActive,
			u.`reduceAccessAtDate`																AS ReduceAccessAtDate
FROM 		`user` u 
LEFT JOIN 	`employee` e 
ON 			e.UserID = u.userID 
LEFT JOIN 	`company` c 
ON 			e.CompanyID = c.CompanyID 
LEFT JOIN 	`companyposition` cp 
ON 			cp.PositionID = e.PositionID
LEFT JOIN 	`accesslevel` a
ON 			u.AccessID = a.AccessID
GROUP BY 	u.`userID`
ORDER BY 	u.`userID`
DESC;

SELECT	`EventID`			AS TheEventID,
		`startTime`			AS StartTime,
		`endTime`			AS EndTime,
		`name`				AS EventName,
		`description`		AS EventDescription,
		`dateTimeCreated`	AS DateTimeCreated,
		`startDate`			AS StartDate,
		`lastDate`			AS LastDate,
		`daysSelected`		AS DaysSelected,
		(
			SELECT 		GROUP_CONCAT(DISTINCT m.`name` separator ",\n")
			FROM		`roomevent` rev
			INNER JOIN 	`meetingroom` m
			ON			rev.`meetingRoomID` = m.`meetingRoomID`
			WHERE		rev.`EventID` = TheEventID
		)					AS UsedMeetingRooms,
		(
			SELECT 	COUNT(*)
			FROM 	`meetingroom`
		)					AS TotalMeetingRooms,
        (
			SELECT 	`startDateTime`
            FROM 	`roomevent`
            WHERE	`EventID` = TheEventID
            AND 	`startDateTime` > CURRENT_TIMESTAMP
            ORDER BY UNIX_TIMESTAMP(`startDateTime`) ASC
            LIMIT 1
        ) 					AS NextStart
FROM 	`event`;

SELECT 		c.`companyID` 										AS CompID,
			c.`name` 											AS CompanyName,
			c.`dateTimeCreated`									AS DatetimeCreated,
			c.`removeAtDate`									AS DeletionDate,
			c.`isActive`										AS CompanyActivated,
			(
				SELECT 	COUNT(e.`CompanyID`)
				FROM 	`employee` e
				WHERE 	e.`companyID` = CompID
			)													AS NumberOfEmployees, 
			(
				SELECT (BIG_SEC_TO_TIME(SUM(
										IF(
											(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 60,
											IF(
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 900, 
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											), 
												900
											),
											0
										)
				)))	AS BookingTimeUsed
				FROM 		`booking` b  
				INNER JOIN 	`company` c 
				ON 			b.`CompanyID` = c.`CompanyID` 
				WHERE 		b.`CompanyID` = CompID
				AND 		b.`actualEndDateTime`
				BETWEEN		c.`prevStartDate`
				AND			c.`startDate`
			)   												AS PreviousMonthCompanyWideBookingTimeUsed,           
			(
				SELECT (BIG_SEC_TO_TIME(SUM(
										IF(
											(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 60,
											IF(
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 900, 
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											), 
												900
											),
											0
										)
				)))	AS BookingTimeUsed
				FROM 		`booking` b  
				INNER JOIN 	`company` c 
				ON 			b.`CompanyID` = c.`CompanyID` 
				WHERE 		b.`CompanyID` = CompID
				AND 		b.`actualEndDateTime`
				BETWEEN		c.`startDate`
				AND			c.`endDate`
			)													AS MonthlyCompanyWideBookingTimeUsed,
   						(
				SELECT (BIG_SEC_TO_TIME(SUM(
										IF(
											(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 60,
											IF(
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											) > 900, 
												(
												(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
											), 
												900
											),
											0
										)
				)))	AS BookingTimeUsed
				FROM 		`booking` b
				WHERE 		b.`CompanyID` = CompID
			)													AS TotalCompanyWideBookingTimeUsed,
			cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
			cc.`lastModified`									AS CompanyCreditsLastModified,
			cr.`name`											AS CreditSubscriptionName,
			cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
			cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
			cr.`overCreditMinutePrice`							AS CreditSubscriptionMinutePrice,
			cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice,
			COUNT(DISTINCT cch.`startDate`)						AS CompanyCreditsHistoryPeriods,
			SUM(cch.`hasBeenBilled`)							AS CompanyCreditsHistoryPeriodsSetAsBilled            
FROM 		`company` c
LEFT JOIN	`companycredits` cc
ON			c.`CompanyID` = cc.`CompanyID`
LEFT JOIN	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
LEFT JOIN 	`companycreditshistory` cch
ON 			cch.`CompanyID` = c.`CompanyID`
GROUP BY 	c.`CompanyID`;

SELECT 		(
				BIG_SEC_TO_TIME(
						(
							DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
							)*86400 
						+ 
						(
							TIME_TO_SEC(b.`actualEndDateTime`) 
							- 
							TIME_TO_SEC(b.`startDateTime`)
						)
					)
				)						AS BookingTimeUsed,
			(
				BIG_SEC_TO_TIME(
					IF(
						(
							(
								DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
								)*86400 
							+ 
							(
								TIME_TO_SEC(b.`actualEndDateTime`) 
								- 
								TIME_TO_SEC(b.`startDateTime`)
							) 
						) > 60,
						IF(
							(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
								) 
							) > 900, 
							(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
								) 
							), 
							900
						),
						0
                    )
				)
            )						AS BookingTimeCharged,
			b.`startDateTime`		AS BookingStartedDatetime,
			b.`actualEndDateTime`	AS BookingCompletedDatetime,
			(
				IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
			)						AS UserFirstname,
			(
				IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
			)						AS UserLastname,
			(
				IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
			)						AS UserEmail,
			(
				IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
			) 						AS MeetingRoomName
FROM 		`booking` b
WHERE   	b.`CompanyID` = 2
AND 		b.`actualEndDateTime` IS NOT NULL
AND     	b.`dateTimeCancelled` IS NULL
AND         b.`actualEndDateTime`
BETWEEN	    '2017-03-15'
AND			'2017-06-15';

SELECT 		(
				BIG_SEC_TO_TIME(
						(
							DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
							)*86400 
						+ 
						(
							TIME_TO_SEC(b.`actualEndDateTime`) 
							- 
							TIME_TO_SEC(b.`startDateTime`)
						)
					)
				)						AS BookingTimeUsed,
			(
				BIG_SEC_TO_TIME(
					IF(
						(
							(
								DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
								)*86400 
							+ 
							(
								TIME_TO_SEC(b.`actualEndDateTime`) 
								- 
								TIME_TO_SEC(b.`startDateTime`)
							) 
						) > 60,
						IF(
							(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
								) 
							) > 900, 
							(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
								) 
							), 
							900
						),
						0
                    )
				)
            )						AS BookingTimeCharged,
			b.`startDateTime`		AS BookingStartedDatetime,
			b.`actualEndDateTime`	AS BookingCompletedDatetime,
			u.`firstName`			AS UserFirstname,
			u.`lastName`			AS UserLastname,
			u.`email`				AS UserEmail,
			m.`name`				AS MeetingRoomName
FROM 		`booking` b
INNER JOIN  `company` c
ON 			c.`CompanyID` = b.`companyID`
LEFT JOIN	`user` u
ON 			u.`userID` = b.`userID`
LEFT JOIN 	`meetingroom` m
ON			m.`meetingRoomID` = b.`meetingRoomID`
WHERE   	b.`CompanyID` = 2
AND 		b.`actualEndDateTime` IS NOT NULL
AND     	b.`dateTimeCancelled` IS NULL
AND         b.`actualEndDateTime`
BETWEEN	    '2017-03-15'
AND			'2017-06-15';

SELECT 		b.`userID`										AS BookedUserID,
			b.`bookingID`,
			(
				IF(b.`meetingRoomID` IS NULL, NULL, (SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = b.`meetingRoomID`))
            )        										AS BookedRoomName,
			b.`startDateTime`								AS StartTime,
			b.`endDateTime`									AS EndTime, 
			b.`displayName` 								AS BookedBy,
			(
				IF(b.`companyID` IS NULL, NULL, (SELECT `name` FROM `company` WHERE `companyID` = b.`companyID`))
            )        										AS BookedForCompany,										
            (
				IF(b.`userID` IS NULL, NULL, (SELECT `firstName` FROM `user` WHERE `userID` = b.`userID`))
            ) 												AS firstName,
            (
				IF(b.`userID` IS NULL, NULL, (SELECT `lastName` FROM `user` WHERE `userID` = b.`userID`))
            ) 												AS lastName,
            (
				IF(b.`userID` IS NULL, NULL, (SELECT `email` FROM `user` WHERE `userID` = b.`userID`))
            ) 												AS email,
            (
				IF(b.`userID` IS NULL, NULL,
					(
						SELECT 		GROUP_CONCAT(c.`name` separator ",\n")
						FROM 		`company` c
						INNER JOIN 	`employee` e
						ON 			e.`CompanyID` = c.`CompanyID`
						WHERE  		e.`userID` = b.`userID`
                        AND			c.`isActive` = 1
						GROUP BY 	e.`userID`
					)
				)
            )												AS WorksForCompany,		 
			b.`description`									AS BookingDescription, 
			b.`dateTimeCreated`								AS BookingWasCreatedOn, 
			b.`actualEndDateTime`							AS BookingWasCompletedOn, 
			b.`dateTimeCancelled`							AS BookingWasCancelledOn 
FROM 		`booking` b
ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
ASC;


SELECT SUM(cnt)	AS HitCount
FROM (
	(SELECT 		COUNT(*) AS cnt
	FROM 		`booking` b
	WHERE 		b.`meetingRoomID` = 32
	AND			b.`dateTimeCancelled` IS NULL
	AND			b.`actualEndDateTime` IS NULL
	AND		
	(		
			(
				b.`startDateTime` >= '2017-08-04 12:00:00' AND 
				b.`startDateTime` < '2017-08-04 23:59:59'
			) 
	OR 		(
				b.`endDateTime` > '2017-08-04 12:00:00' AND 
				b.`endDateTime` <= '2017-08-04 23:59:59'
			)
	OR 		(
				'2017-08-04 23:59:59' > b.`startDateTime` AND 
				'2017-08-04 23:59:59' < b.`endDateTime`
			)
	OR 		(
				'2017-08-04 12:00:00' > b.`startDateTime` AND 
				'2017-08-04 12:00:00' < b.`endDateTime`
			)
	)
    LIMIT	1)
	UNION
	(SELECT 	COUNT(*) AS cnt
	FROM 		`roomevent` rev
	WHERE 		rev.`meetingRoomID` = 32
	AND	 	
	(
			(
				rev.`startDateTime` >= '2017-08-04 12:00:00' AND 
				rev.`startDateTime` < '2017-08-04 23:59:59'
			)                    
	OR 		(
				rev.`endDateTime` > '2017-08-04 12:00:00' AND 
				rev.`endDateTime` <= '2017-08-04 23:59:59'
			)                    
	OR 		(
				'2017-08-04 23:59:59' > rev.`startDateTime` AND 
				'2017-08-04 23:59:59' < rev.`endDateTime`
			)                   
	OR 		(
				'2017-08-04 12:00:00' > rev.`startDateTime` AND 
				'2017-08-04 12:00:00' < rev.`endDateTime`
			)
	)
    LIMIT 	1)
) AS TimeSlotTaken;

SELECT 		*
FROM 		`roomevent` rev
WHERE 		rev.`meetingRoomID` = 32
AND	 	
(
		(
			rev.`startDateTime` >= '2017-08-04 12:00:00' AND 
			rev.`startDateTime` < '2017-08-04 23:59:59'
		)                    
OR 		(
			rev.`endDateTime` > '2017-08-04 12:00:00' AND 
			rev.`endDateTime` <= '2017-08-04 23:59:59'
		)                    
OR 		(
			'2017-08-04 23:59:59' > rev.`startDateTime` AND 
			'2017-08-04 23:59:59' < rev.`endDateTime`
		)                   
OR 		(
			'2017-08-04 12:00:00' > rev.`startDateTime` AND 
			'2017-08-04 12:00:00' < rev.`endDateTime`
		)
);

SELECT 	COUNT(*)	AS HitCount
FROM 	(
			SELECT 		1
			FROM 		`booking` b
			LEFT JOIN	`roomevent` rev
			ON 			rev.`MeetingRoomID` = b.`meetingRoomID`
			WHERE 		b.`meetingRoomID` = 32
			AND			b.`dateTimeCancelled` IS NULL
			AND			b.`actualEndDateTime` IS NULL
			AND		
			(		
					(
						b.`startDateTime` >= '2017-08-04 12:00:00' AND 
						b.`startDateTime` < '2017-08-04 23:59:59'
					) 
			OR 		(
						b.`endDateTime` > '2017-08-04 12:00:00' AND 
						b.`endDateTime` <= '2017-08-04 23:59:59'
					)
			OR 		(
						'2017-08-04 23:59:59' > b.`startDateTime` AND 
						'2017-08-04 23:59:59' < b.`endDateTime`
					)
			OR 		(
						'2017-08-04 12:00:00' > b.`startDateTime` AND 
						'2017-08-04 12:00:00' < b.`endDateTime`
					)
			OR 		(
						rev.`startDateTime` >= '2017-08-04 12:00:00' AND 
						rev.`startDateTime` < '2017-08-04 23:59:59'
					)                    
			OR 		(
						rev.`endDateTime` > '2017-08-04 12:00:00' AND 
						rev.`endDateTime` <= '2017-08-04 23:59:59'
					)                    
 			OR 		(
						'2017-08-04 23:59:59' > rev.`startDateTime` AND 
						'2017-08-04 23:59:59' < rev.`endDateTime`
					)                   
			OR 		(
						'2017-08-04 12:00:00' > rev.`startDateTime` AND 
						'2017-08-04 12:00:00' < rev.`endDateTime`
					)
			)
		) AS BookingsFound;

SELECT	`EventID`				AS TheEventID,
		`startTime`				AS StartTime,
		`endTime`				AS EndTime,
		`name`					AS EventName,
		`description`			AS EventDescription,
		`dateTimeCreated`		AS DateTimeCreated,
		`startDate`				AS StartDate,
		`lastDate`				AS LastDate,
		WEEK(`startDate`,3)		AS WeekStart,
		WEEK(`lastDate`,3)		AS WeekEnd,
		`daysSelected`			AS DaysSelected,
		(
			SELECT 		GROUP_CONCAT(DISTINCT m.`name` separator ",\n")
			FROM		`roomevent` rev
			INNER JOIN 	`meetingroom` m
			ON			rev.`meetingRoomID` = m.`meetingRoomID`
			WHERE		rev.`EventID` = TheEventID
		)						AS UsedMeetingRooms
FROM 	`event`;

INSERT INTO `event`
SET			`startTime` = '23:45:01',
			`endTime` = '23:59:59',
            `startDate`= '2017-08-04',
            `lastDate` = '2017-08-04',
            `daysSelected` = 'Friday',
            `dateTimeCreated` = CURRENT_TIMESTAMP;

INSERT INTO `roomevent`
SET			`EventID` = 2,
			`meetingRoomID` = 32,
            `startDateTime` = '2017-08-04 23:45:01',
            `endDateTime` = '2017-08-04 23:59:59';

SELECT	(
			SELECT COUNT(*)
			FROM	`employee`
            WHERE 	`userID` = 16
		) AS HitCount,
		`bookingdescription`, 
		`displayname`,
		`firstName`,
		`lastName`,
		`email`
FROM 	`user`
WHERE 	`userID` = 16
LIMIT 	1;

SELECT	COUNT(e.`userID`),
		`bookingdescription`, 
		`displayname`,
		`firstName`,
		`lastName`,
		`email`
FROM 	`user` u
JOIN 	`employee` e
ON 		e.`userID` = u.`userID`
WHERE 	u.`userID` = 16
LIMIT 	1;

SELECT 	COUNT(*),
		`bookingID`,
		`meetingRoomID`									AS TheMeetingRoomID, 
		(
			SELECT	`name`
			FROM	`meetingroom`
			WHERE	`meetingRoomID` = TheMeetingRoomID 
		)												AS TheMeetingRoomName,
		`startDateTime`,
		`endDateTime`,
		`actualEndDateTime`
FROM	`booking`
WHERE 	`cancellationCode` = 'aecffbf33f25291a7f3cdf3204622e6847514cdd1faa0362771c1863ce34025b'
AND		`dateTimeCancelled` IS NULL
LIMIT 	1;

SELECT 	`userID`, 
		`firstname`, 
		`lastname`, 
		`email`,
		`displayname`,
		`bookingdescription`
FROM 	`user`
WHERE 	`isActive` > 0
AND		`userID`
IN	(
		SELECT 	DISTINCT `userID`
        FROM 	`employee`
	);

SELECT 		u.`userID`, 
			u.`firstname`, 
			u.`lastname`, 
			u.`email`,
			u.`displayname`,
			u.`bookingdescription`
FROM 		`user` u
INNER JOIN 	`employee` e
ON 			u.`userID` = e.`UserID`
WHERE 		`isActive` > 0
GROUP BY 	u.`userID`;

SELECT 	`userID`, 
		`firstname`, 
		`lastname`, 
		`email`,
		`displayname`,
		`bookingdescription`
FROM 	`user`
WHERE 	`isActive` > 0;

SELECT SEC_TO_TIME(
					FLOOR(
							(TIME_TO_SEC('16:36:00')+900)/1800
					)*1800
				);
                
SELECT		StartDate, 
			EndDate,
            CreditSubscriptionMonthlyPrice,
            CreditSubscriptionMinutePrice,
            CreditSubscriptionHourPrice,
            BIG_SEC_TO_TIME(CreditsGivenInSeconds) 			AS CreditsGiven,
			BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 	AS BookingTimeCharged,
			BIG_SEC_TO_TIME(
							IF(
								(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
                                BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
                                0
							)
			)												AS OverCreditsTimeExact,
            BIG_SEC_TO_TIME(
							FLOOR(
								(
									(
										IF(
											(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
											BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
											0
										)
                                    )+450
								)/900
							)*900
			)												AS OverCreditsTimeCharged
FROM (
		SELECT 	cch.`startDate` 							AS StartDate,
				cch.`endDate`								AS EndDate,							 
				cch.`minuteAmount`*60						AS CreditsGivenInSeconds,						
				cch.`monthlyPrice`							AS CreditSubscriptionMonthlyPrice,
				cch.`overCreditMinutePrice`					AS CreditSubscriptionMinutePrice,
				cch.`overCreditHourPrice`					AS CreditSubscriptionHourPrice,
			(SELECT FLOOR(((IFNULL(SUM(
					IF(
						(
							(
								DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
								)*86400 
							+ 
							(
								TIME_TO_SEC(b.`actualEndDateTime`) 
								- 
								TIME_TO_SEC(b.`startDateTime`)
								) 
						) > 60,
						IF(
							(
							(
								DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
								)*86400 
							+ 
							(
								TIME_TO_SEC(b.`actualEndDateTime`) 
								- 
								TIME_TO_SEC(b.`startDateTime`)
								) 
						) > 900, 
							(
							(
								DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
								)*86400 
							+ 
							(
								TIME_TO_SEC(b.`actualEndDateTime`) 
								- 
								TIME_TO_SEC(b.`startDateTime`)
								) 
						), 
							900
						),
						0
					)
				),0))+450)/900)*900
			FROM 		`booking` b
			WHERE 		b.`CompanyID` = 2
			AND 		b.`actualEndDateTime`
			BETWEEN		cch.`startDate`
			AND			cch.`endDate`
			)												AS BookingTimeChargedInSeconds
		FROM 		`companycreditshistory` cch
		INNER JOIN	`companycredits` cc
		ON 			cc.`CompanyID` = cch.`CompanyID`
		INNER JOIN 	`credits` cr
		ON 			cr.`CreditsID` = cc.`CreditsID`
		WHERE 		cch.`CompanyID` = 2
		AND 		cch.`hasBeenBilled` = 0
) 															AS PeriodInformation;

SELECT BIG_SEC_TO_TIME(
						SUM(
							IF(
								BookingTimeInSeconds > 60,
								IF(
									BookingTimeInSeconds > 900, 
									BookingTimeInSeconds, 
									900
								),
								0
							)
						)
)
FROM 		`booking` b
INNER JOIN (
				SELECT 	(
						SUM(
							DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
							)*86400 
						+ 
						SUM(
							TIME_TO_SEC(b.`actualEndDateTime`) 
							- 
							TIME_TO_SEC(b.`startDateTime`)
							) 
						) 	AS BookingTimeInSeconds
				FROM 		`booking` b  
				INNER JOIN 	`company` c 
				ON 			b.`CompanyID` = c.`CompanyID`
				WHERE		b.`CompanyID` = 2
				AND 		b.`actualEndDateTime`
				BETWEEN		c.`startDate`
				AND			c.`endDate`
				GROUP BY 	b.`bookingID`
			) AS SummedBookingTime
WHERE b.`CompanyID` = 2
GROUP BY b.`bookingID`;

SELECT *, COUNT(cch.`CompanyID`) AS Blah
FROM 		`company` c
LEFT JOIN	`companycredits` cc
ON			c.`CompanyID` = cc.`CompanyID`
LEFT JOIN	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
LEFT JOIN 	`companycreditshistory` cch
ON 			cch.`CompanyID` = c.`CompanyID`
LEFT JOIN	`employee` e
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	c.`CompanyID`;

SELECT (BIG_SEC_TO_TIME(SUM(
						IF(
							(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
									) 
							) > 60,
							IF(
								(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
									) 
							) > 900, 
								(
								(
									DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
									)*86400 
								+ 
								(
									TIME_TO_SEC(b.`actualEndDateTime`) 
									- 
									TIME_TO_SEC(b.`startDateTime`)
									) 
							), 
								900
							),
							0
						)
)))	AS BookingTimeUsed
FROM 		`booking` b  
INNER JOIN 	`company` c 
ON 			b.`CompanyID` = c.`CompanyID` 
WHERE 		b.`CompanyID` = 2
AND 		b.`actualEndDateTime`
BETWEEN		c.`startDate`
AND			c.`endDate`;


SELECT 	(
		SUM(
			DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
			)*86400 
		+ 
		SUM(
			TIME_TO_SEC(b.`actualEndDateTime`) 
			- 
			TIME_TO_SEC(b.`startDateTime`)
			) 
		) 	AS BookingTimeInSeconds
FROM 		`booking` b  
INNER JOIN 	`company` c 
ON 			b.`CompanyID` = c.`CompanyID` 
WHERE 		b.`CompanyID` = 2
AND 		b.`actualEndDateTime`
BETWEEN		c.`startDate`
AND			c.`endDate`;

SELECT *,(
		BIG_SEC_TO_TIME(
						SUM(
							DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
							)*86400 
						+ 
						SUM(
							TIME_TO_SEC(b.`actualEndDateTime`) 
							- 
							TIME_TO_SEC(b.`startDateTime`)
							) 
						) 
		)
FROM 		`booking` b  
INNER JOIN 	`company` c 
ON 			b.`CompanyID` = c.`CompanyID` 
WHERE 		b.`CompanyID` = 2
AND 		b.`actualEndDateTime`
BETWEEN		c.`startDate`
AND			c.`endDate`
GROUP BY 	b.`bookingID`;


SELECT 		u.`email`
FROM 		`user` u
INNER JOIN 	`accesslevel` a
WHERE		a.`AccessID` = u.`AccessID`
AND			a.`AccessName` = 'Admin';

SELECT 		b.`bookingID`,
			b.`companyID`,
			m.`name` 										AS BookedRoomName, 
			b.startDateTime 								AS StartTime, 
			b.endDateTime									AS EndTime, 
			b.displayName 									AS BookedBy,
			c.`name` 										AS BookedForCompany,
			u.firstName, 
			u.lastName, 
			u.email, 
			GROUP_CONCAT(c2.`name` separator ', ') 			AS WorksForCompany, 
			b.description 									AS BookingDescription, 
			b.dateTimeCreated 								AS BookingWasCreatedOn, 
			b.actualEndDateTime								AS BookingWasCompletedOn, 
			b.dateTimeCancelled								AS BookingWasCancelledOn 
FROM 		`booking` b 
LEFT JOIN 	`meetingroom` m 
ON 			b.meetingRoomID = m.meetingRoomID 
LEFT JOIN 	`user` u 
ON 			u.userID = b.userID 
LEFT JOIN 	`employee` e 
ON 			e.UserID = b.userID 
LEFT JOIN 	`company` c 
ON 			c.CompanyID = b.CompanyID
LEFT JOIN 	`company` c2
ON 			c2.CompanyID = e.CompanyID
WHERE		c.`isActive` = 1
GROUP BY 	b.bookingID
ORDER BY 	b.bookingID
DESC;

INSERT INTO `companycreditshistory`
SET			`CompanyID` = 1,
			`startDate` = '2017-03-15',
            `endDate` = '2017-04-15',
            `minuteAmount` = 90,
            `monthlyPrice` = 1500,
            `overCreditMinutePrice` = 2.5,
            `overCreditHourPrice` = NULL;

SELECT 	COUNT(*) 
FROM 	information_schema.SCHEMATA
WHERE	`SCHEMA_NAME` = 'test';

INSERT INTO `companycreditshistory`
SET			`CompanyID` = :companyID,
			`startDate` = :startDate,
            `endDate` = :endDate,
            `minuteAmount` = :minuteAmount,
            `monthlyPrice` = :monthlyPrice,
            `overCreditMinutePrice` = :overCreditMinutePrice,
            `overCreditHourPrice` = :overCreditHourPrice;
            
SELECT 		c.`CompanyID`			AS TheCompanyID,
			c.`startDate`,
			c.`endDate`,
            cr.`minuteAmount`,
            cr.`monthlyPrice`,
            cr.`overCreditMinutePrice`,
            cr.`overCreditHourPrice`,
            cc.`altMinuteAmount`,
            (
				SELECT (
						BIG_SEC_TO_TIME(
										SUM(
											DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
											)*86400 
										+ 
										SUM(
											TIME_TO_SEC(b.`actualEndDateTime`) 
											- 
											TIME_TO_SEC(b.`startDateTime`)
											) 
										) 
						) 
				FROM 		`booking` b  
				INNER JOIN 	`company` c 
				ON 			b.`CompanyID` = c.`CompanyID` 
				WHERE 		b.`CompanyID` = TheCompanyID
				AND 		b.`actualEndDateTime`
				BETWEEN		c.`startDate`
				AND			c.`endDate`
            )	AS BookingTimeThisPeriod
FROM 		`company` c
INNER JOIN 	`companycredits` cc
ON 			cc.`CompanyID` = c.`CompanyID`
INNER JOIN 	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
WHERE 		c.`isActive` = 1
AND			CURDATE() > c.`endDate`;

SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
			m.`name`			AS MeetingRoomName, 
			m.`capacity`		AS MeetingRoomCapacity, 
			m.`description`		AS MeetingRoomDescription, 
			m.`location`		AS MeetingRoomLocation,
			COUNT(re.`amount`)	AS MeetingRoomEquipmentAmount,
            (
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
                AND 	b.`endDateTime` > current_timestamp
                AND 	b.`dateTimeCancelled` IS NULL
                AND 	b.`actualEndDateTime` IS NULL
			)					AS MeetingRoomActiveBookings
FROM 		`meetingroom` m
LEFT JOIN 	`roomequipment` re
ON 			re.`meetingRoomID` = m.`meetingRoomID`			
GROUP BY 	m.`meetingRoomID`;

SELECT IF(
			DATE(`endDate`) = DATE_SUB(`startDate`, INTERVAL -1 -1 MONTH), 
			NULL, 
			1
		) AS ValidBillingDate,
		DATE_SUB(`startDate`,INTERVAL -1 MONTH) AS CompanyBillingDateStart,
		DATE_SUB(`startDate`,INTERVAL -1 - 1 MONTH) AS CompanyBillingDateEnd,
        `startDate`
FROM 	`company`
WHERE 	`companyID` = 1
LIMIT 	1;

SELECT *,
TIMESTAMPDIFF(MONTH, c.`dateTimeCreated`, date_add(c.`startDate`, INTERVAL 1 day))
FROM `company` c
WHERE c.`companyID` = 2;

SELECT IF(
			DATE(`endDate`) = DATE_SUB('2017-04-15', INTERVAL -1 MONTH), 
            NULL, 
            DATE_SUB('2017-04-15', INTERVAL -1 MONTH)
		) AS PreviousBillingDate
FROM 	`company`
WHERE 	`companyID` = 2
LIMIT 	1;

SELECT IF(
			DATE(`dateTimeCreated`) = DATE_SUB('2017-03-31', INTERVAL 1 MONTH), 
            NULL, 
            DATE_SUB('2017-03-31', INTERVAL 5 MONTH)
		) AS PreviousBillingDate
FROM 	`company`
WHERE 	`companyID` = 2
LIMIT 	1;

SELECT 	`name`,
		`prevStartDate`,
        DATE_SUB(`startDate`, INTERVAL 1 MONTH),
        `endDate`
FROM 	`company`
WHERE 	`companyID` = 2
LIMIT 	1;

SELECT 		b.`displayName`,
			b.`description`,
			b.`dateTimeCreated`,
			b.`startDateTime`,
			b.`actualEndDateTime`,
            u.`firstName`,
            u.`lastName`,
            u.`email`,
            m.`name`				AS MeetingRoomName
FROM 		`booking` b
INNER JOIN  `company` c
ON 			c.`CompanyID` = b.`companyID`
LEFT JOIN	`user` u
ON 			u.`userID` = b.`userID`
LEFT JOIN 	`meetingroom` m
ON			m.`meetingRoomID` = b.`meetingRoomID`
WHERE   	b.`CompanyID` = 2
AND 		b.`actualEndDateTime` IS NOT NULL
AND     	b.`dateTimeCancelled` IS NULL
AND         b.`actualEndDateTime`
BETWEEN	    c.`startDate`
AND			c.`endDate`;

SELECT 	u.`userID`					AS UsrID,
		c.`companyID`				AS TheCompanyID,
		c.`name`					AS CompanyName,
		u.`firstName`, 
		u.`lastName`,
		u.`email`,
		(
			SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByRemovedEmployees
			FROM 		`booking` b
			INNER JOIN 	`employee` e
			ON 			e.`companyID` = b.`companyID`
			WHERE 		b.`companyID` = 2
			AND 		b.`userID` IS NOT NULL
			AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = 2)
			AND 		b.`userID` = UsrID
			AND 		b.`actualEndDateTime`
			BETWEEN		c.`prevStartDate`
			AND			c.`startDate`
		)														AS PreviousMonthBookingTimeUsed,						
		(
			SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByRemovedEmployees
			FROM 		`booking` b
			INNER JOIN 	`employee` e
			ON 			e.`companyID` = b.`companyID`
			WHERE 		b.`companyID` = 2
			AND 		b.`userID` IS NOT NULL
            AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = 2)
			AND 		b.`userID` = UsrID
			AND 		b.`actualEndDateTime`
			BETWEEN		c.`startDate`
			AND			c.`endDate`
		)														AS MonthlyBookingTimeUsed,
		(
			SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByRemovedEmployees
			FROM 		`booking` b
			INNER JOIN 	`employee` e
			ON 			e.`companyID` = b.`companyID`
			WHERE 		b.`companyID` = 2
			AND 		b.`userID` IS NOT NULL
			AND			b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = 2)
			AND 		b.`userID` = UsrID
		)														AS TotalBookingTimeUsed
FROM 		`company` c
JOIN 		`booking` b
ON 			c.`companyID` = b.`companyID`
JOIN 		`user` u 
ON 			u.userID = b.UserID 
WHERE 		c.`companyID` = 2
AND 		b.`userID` NOT IN (SELECT `userID` FROM employee WHERE `CompanyID` = 2)
GROUP BY 	UsrID;

SELECT 		c.`CompanyID`									AS TheCompanyID,
			c.`name`										AS CompanyName,
			c.`startDate`									AS CompanyBillingMonthStart,
			c.`endDate`										AS CompanyBillingMonthEnd,
			cr.`CreditsID`									AS CreditsID,
			cr.`name`										AS CreditsName,
			cr.`description`								AS CreditsDescription,
			cr.`minuteAmount`								AS CreditsMinutesGiven,
			cr.`monthlyPrice`								AS CreditsMonthlyPrice,
			cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
			cr.`overCreditHourPrice`						AS CreditsHourPrice,
			cc.`altMinuteAmount`							AS CreditsAlternativeAmount,
			cc.`datetimeAdded` 								AS DateTimeAdded,
			cc.`lastModified`								AS DateTimeLastModified
FROM 		`company` c
JOIN 		`companycredits` cc
ON 			c.`CompanyID` = cc.`CompanyID`
JOIN 		`credits` cr
ON 			cr.`CreditsID` = cc.`CreditsID`
WHERE 		c.`isActive` > 0
ORDER BY	UNIX_TIMESTAMP(cc.`datetimeAdded`)
DESC;


INSERT INTO `logaction`(`name`,`description`) VALUES ('Company Credits Changed', 'The referenced company received the new referenced Credits.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Credits Added', 'The referenced Credits was added.');
INSERT INTO `logaction`(`name`,`description`) VALUES ('Credits Removed', 'The referenced Credits was removed.');

SELECT 		cr.`CreditsID`									AS TheCreditsID,
			cr.`name`										AS CreditsName,
			cr.`description`								AS CreditsDescription,
			cr.`minuteAmount`								AS CreditsGivenInMinutes,
			cr.`monthlyPrice`								AS CreditsMonthlyPrice,
			cr.`overCreditMinutePrice`						AS CreditsMinutePrice,
			cr.`overCreditHourPrice`						AS CreditsHourPrice,
			cr.`lastModified`								AS CreditsLastModified,
			cr.`datetimeAdded`								AS DateTimeAdded,
			UNIX_TIMESTAMP(cr.`datetimeAdded`)				AS OrderByDate,
			COUNT(cc.`CreditsID`)							AS CreditsIsUsedByThisManyCompanies
FROM 		`credits` cr
LEFT JOIN 	`companycredits` cc
ON 			cr.`CreditsID` = cc.`CreditsID`
GROUP BY 	cr.`CreditsID`
ORDER BY	OrderByDate
DESC;

DELETE FROM 	`credits`
WHERE	`CreditsID` = 1
AND		`name` != 'Default';

INSERT INTO `companycredits`(`CompanyID`, `CreditsID`) VALUES (39,2),(18,2);

SELECT 		COUNT(*)
FROM 		`company`
WHERE		`CompanyID` 
NOT IN		(
				SELECT 	`CompanyID`
				FROM 	`companycredits`
			);

SELECT 		`CompanyID`,
			(
				SELECT 	`CreditsID`
				FROM	`credits`
				WHERE	`name` = 'Default'
			)	AS CreditsID
FROM 		`company`
WHERE		`CompanyID` 
NOT IN		(
				SELECT 	`CompanyID`
				FROM 	`companycredits`
			);

SELECT 		COUNT(*),
			`CompanyID`
FROM 		`company`
WHERE		`CompanyID` 
NOT IN		(
				SELECT 	`CompanyID`
				FROM 	`companycredits`
			);

UPDATE 	`company`
SET		`prevStartDate` = `startDate`,
		`startDate` = `endDate`,
        `endDate` = (`startDate` + INTERVAL 1 MONTH)
WHERE	`companyID` <> 0
AND		CURDATE() > `endDate`;

UPDATE 	`user`
SET 	`AccessID` = ( 
						SELECT 	`AccessID`
						FROM 	`accesslevel`
						WHERE 	`AccessName` = 'Normal User'
						LIMIT 	1
					),
		`bookingCode` = NULL,
        `reduceAccessAtDate` = NULL
WHERE 	DATE(CURRENT_TIMESTAMP) >= `reduceAccessAtDate`
AND 	`isActive` = 1
AND		`userID` <> 0;

UPDATE 	`booking`
SET		`actualEndDateTime` = `endDateTime`,
		`cancellationCode` = NULL
WHERE 	CURRENT_TIMESTAMP > `endDateTime`
AND 	`actualEndDateTime` IS NULL
AND 	`dateTimeCancelled` IS NULL
AND		`bookingID` <> 0;

UPDATE 	`booking`
SET 	`actualEndDateTime` = `dateTimeCancelled`,
		`cancellationCode` = NULL
WHERE 	`actualEndDateTime` IS NULL
AND		`dateTimeCancelled`
BETWEEN `startDateTime`
AND		`endDateTime`
AND 	`bookingID` <> 0;

INSERT INTO `companycredits`
SET 		`CompanyID` = 21,
			`CreditsID` = 2;

INSERT INTO `companycredits`
SET 		`CompanyID` = 1,
			`CreditsID` = 2,
            `altMinuteAmount` = 500;

INSERT INTO `credits`
SET			`name` = 'Default',
			`description` = 'Default Subscription set for new companies. They have 0 credit and 0 monthly fee.',
            `minuteAmount` = 0,
            `monthlyPrice` = 0,
            `overCreditHourPrice` = 200;

INSERT INTO `credits`
SET			`name` = 'test',
			`description` = 'test',
            `minuteAmount` = 480,
            `monthlyPrice` = 2000,
            `overCreditHourPrice` = 200;

SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
			m.`name`			AS MeetingRoomName, 
			m.`capacity`		AS MeetingRoomCapacity, 
			m.`description`		AS MeetingRoomDescription, 
			m.`location`		AS MeetingRoomLocation,
			COUNT(re.`amount`)	AS MeetingRoomEquipmentAmount
FROM 		`meetingroom` m
LEFT JOIN 	`roomequipment` re
ON 			re.`meetingRoomID` = m.`meetingRoomID`
WHERE		m.`meetingRoomID` = 21
GROUP BY 	m.`meetingRoomID`
LIMIT 1;

SELECT 		b.`bookingID`,
						b.`companyID`,
						m.`name` 										AS BookedRoomName, 
						b.startDateTime 								AS StartTime, 
						b.endDateTime									AS EndTime, 
						b.displayName 									AS BookedBy,
						(	
							SELECT `name` 
							FROM `company` 
							WHERE `companyID` = b.`companyID`
						)												AS BookedForCompany,
						u.firstName, 
						u.lastName, 
						u.email, 
						GROUP_CONCAT(c.`name` separator ', ') 			AS WorksForCompany, 
						b.description 									AS BookingDescription, 
						b.dateTimeCreated 								AS BookingWasCreatedOn, 
						b.actualEndDateTime								AS BookingWasCompletedOn, 
						b.dateTimeCancelled								AS BookingWasCancelledOn 
			FROM 		`booking` b 
			LEFT JOIN 	`meetingroom` m 
			ON 			b.meetingRoomID = m.meetingRoomID 
			LEFT JOIN 	`user` u 
			ON 			u.userID = b.userID 
			LEFT JOIN 	`employee` e 
			ON 			e.UserID = u.userID 
			LEFT JOIN 	`company` c 
			ON 			c.CompanyID = e.CompanyID
            WHERE 		c.`isActive` = 1
			GROUP BY 	b.bookingID
			ORDER BY 	b.bookingID
			DESC;

UPDATE 	`user`
SET 	`AccessID` = ( 
						SELECT 	`AccessID`
						FROM 	`accesslevel`
						WHERE 	`AccessName` = 'Normal User'
						LIMIT 	1
					),
		`bookingCode` = NULL
WHERE 	DATE(CURRENT_TIMESTAMP) >= `reduceAccessAtDate`
AND 	`isActive` = 1
AND		`userID` <> 0;

SELECT 	m.`name`					AS MeetingRoomName,
		c.`name`					AS CompanyName,
		u.`email`,       
		b.`startDateTime`,
		b.`endDateTime`,
		b.`displayName`,
		b.`description`,
		b.`cancellationCode`
FROM	`booking` b
JOIN 	`meetingroom` m
ON 		b.`meetingRoomID` = m.`meetingRoomID`
JOIN	`company` c
ON 		c.`companyID` = b.`companyID`
JOIN	`user` u
ON		u.`userID` = b.`userID`
WHERE 	DATE_SUB(b.`startDateTime`, INTERVAL 20 MINUTE) < CURRENT_TIMESTAMP
AND		DATE_SUB(b.`startDateTime`, INTERVAL 2 MINUTE) > CURRENT_TIMESTAMP
AND 	b.`dateTimeCancelled` IS NULL
AND 	b.`actualEndDateTime` IS NULL
AND		b.`cancellationCode` IS NOT NULL
AND 	DATE_ADD(b.`dateTimeCreated`, INTERVAL 0 MINUTE) < CURRENT_TIMESTAMP
AND		b.`emailSent` = 0
AND		b.`bookingID` <> 0;

SELECT 	*
FROM	`booking`
WHERE 	DATE_SUB(`startDateTime`, INTERVAL 10 MINUTE) < CURRENT_TIMESTAMP
AND		`startDateTime` > CURRENT_TIMESTAMP
AND 	`dateTimeCancelled` IS NULL
AND 	`actualEndDateTime` IS NULL
AND		`cancellationCode` IS NOT NULL
AND 	DATE_ADD(`dateTimeCreated`, INTERVAL 5 MINUTE) < CURRENT_TIMESTAMP
AND		`emailSent` = 0
AND		`bookingID` <> 0;

SELECT *
FROM 	`company`
WHERE 	DATE(CURRENT_TIMESTAMP) >= `removeAtDate`
AND 	`isActive` = 1
AND		`companyID` <> 0;

UPDATE 	`company`
SET		`isActive` = 0
WHERE 	DATE(CURRENT_TIMESTAMP) >= `removeAtDate`
AND 	`isActive` = 1
AND		`companyID` <> 0;

UPDATE 	`company`
SET		`removeAtDate` = DATE_SUB(DATE(CURRENT_TIMESTAMP), INTERVAL 1 DAY)
WHERE 	`isActive` = 1
AND		`companyID` = 34;

DELETE FROM `user`
WHERE DATE_ADD(`create_time`, INTERVAL 8 HOUR) < CURRENT_TIMESTAMP
AND 	`isActive` = 0
AND		`userID` <> 0;

UPDATE 	`booking`
SET		`dateTimeCancelled` = NULL,
		`cancellationCode` = NULL
WHERE 	CURRENT_TIMESTAMP > `endDateTime`
AND 	`actualEndDateTime` IS NOT NULL
AND 	`dateTimeCancelled` > `actualEndDateTime`
AND 	`bookingID` <> 0;

SELECT * 
FROM 	`booking`
WHERE 	CURRENT_TIMESTAMP > `endDateTime`
AND 	`actualEndDateTime` IS NOT NULL
AND 	`dateTimeCancelled` > `actualEndDateTime`
AND 	`bookingID` <> 0;

UPDATE 	`booking`
SET		`actualEndDateTime` = `endDateTime`,
		`cancellationCode` = NULL
WHERE 	CURRENT_TIMESTAMP > `endDateTime`
AND 	`actualEndDateTime` IS NULL
AND 	`dateTimeCancelled` IS NULL
AND 	`bookingID` <> 0;


SELECT 	u.`userID`					AS UsrID,
						c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						u.`firstName`, 
						u.`lastName`,
						u.`email`,
						(
						SELECT 	(
								BIG_SEC_TO_TIME(
												SUM(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												SUM(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
												) 
								) AS TotalBookingTimeByRemovedEmployees
						FROM 	`booking` b
						INNER JOIN `employee` e
						ON 		e.`companyID` = b.`companyID`
						WHERE 	b.`companyID` = 1
						AND 	b.`userID` IS NOT NULL
						AND		e.`userID` != b.`userID`
                        AND 	b.`userID` = UsrID
						AND 			YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 			MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)														AS MonthlyBookingTimeUsed,
						(
						SELECT 	(
								BIG_SEC_TO_TIME(
												SUM(
													DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
													)*86400 
												+ 
												SUM(
													TIME_TO_SEC(b.`actualEndDateTime`) 
													- 
													TIME_TO_SEC(b.`startDateTime`)
													) 
												) 
								) AS TotalBookingTimeByRemovedEmployees
						FROM 	`booking` b
						INNER JOIN `employee` e
						ON 		e.`companyID` = b.`companyID`
						WHERE 	b.`companyID` = 1
						AND 	b.`userID` IS NOT NULL
						AND		e.`userID` != b.`userID`
                        AND 	b.`userID` = UsrID
						)														AS TotalBookingTimeUsed
				FROM 	`company` c
				JOIN 	`booking` b
				ON 		c.`companyID` = b.`companyID`
				JOIN 	`user` u 
				ON 		u.userID = b.UserID 
				WHERE 	c.`companyID` = 1
                GROUP BY UsrID;



SELECT *
FROM (
		(
			SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByDeletedUsers
			FROM 	`booking` b
			WHERE 	b.`companyID` = 1
			AND 	b.`userID` IS NULL) AS TotalBookingTimeByDeletedUsers 
		JOIN
			(
			SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByRemovedEmployees
			FROM 	`booking` b
			INNER JOIN `employee` e
			ON 		e.`companyID` = b.`companyID`
			WHERE 	b.`companyID` = 1
			AND 	b.`userID` IS NOT NULL
			AND		e.`userID` != b.`userID`) AS TotalBookingTimeByRemovedEmployees 
		JOIN 
			(
            SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByEmployees
			FROM 	`booking` b
			INNER JOIN `employee` e
			ON 		e.`companyID` = b.`companyID`
			WHERE 	b.`companyID` = 1
			AND 	b.`userID` IS NOT NULL
			AND		e.`userID` = b.`userID`) AS TotalBookingTimeByEmployees 
		JOIN 
			(
            SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeByExistingUsers
			FROM 	`booking` b
			WHERE 	b.`companyID` = 1
			AND 	b.`userID` IS NOT NULL) AS TotalBookingTimeByExistingUsers 
		JOIN 
			(
            SELECT 	(
					BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
					) AS TotalBookingTimeForCompany
			FROM 	`booking` b
			WHERE 	b.`companyID` = 1) AS TotalBookingTimeForCompany
	);



SELECT	(
	BIG_SEC_TO_TIME(
		SUM(
			DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
			)*86400 
		+ 
		SUM(
			TIME_TO_SEC(b.`actualEndDateTime`) 
			- 
			TIME_TO_SEC(b.`startDateTime`)
			) 
		)
)
FROM `employee` e
RIGHT OUTER JOIN `booking` b
ON b.`userID` = e.`userID`
WHERE b.`companyID` = 1;


SELECT *
FROM `employee` e
RIGHT OUTER JOIN `booking` b
ON b.`userID` = e.`userID`
WHERE b.`companyID` = 1;


SELECT 	c.`companyID`				AS TheCompanyID,
						c.`name`					AS CompanyName,
						(
						SELECT	(
								BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									)
								)
						FROM 		`booking` b
                        INNER JOIN 	`employee` e
                        ON			e.`companyID` = b.`companyID`
						WHERE 		b.`companyID` = 1
                        AND			e.`userID` != b.`userID`
						AND 		YEAR(b.`actualEndDateTime`) = YEAR(NOW())
						AND 		MONTH(b.`actualEndDateTime`) = MONTH(NOW())
						)														AS MonthlyBookingTimeUsed,
						(
						SELECT	(
								BIG_SEC_TO_TIME(
									SUM(
										DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`)
										)*86400 
									+ 
									SUM(
										TIME_TO_SEC(b.`actualEndDateTime`) 
										- 
										TIME_TO_SEC(b.`startDateTime`)
										) 
									) 
								)
						FROM 		`booking` b
                        INNER JOIN 	`employee` e
                        ON			e.`companyID` = b.`companyID`
						WHERE 		b.`companyID` = 1
                        AND			e.`userID` != b.`userID`	
						)														AS TotalBookingTimeUsed
				FROM 	`company` c
				WHERE	`companyID` = 1;

UPDATE 	`user`
SET		`lastActivity` = CURRENT_TIMESTAMP()
WHERE 	`userID` = 1
AND		`isActive` > 0;

SELECT 	COUNT(*)
FROM 	`booking`
WHERE 	`meetingRoomID` = 1
AND		
(		
		(
			`startDateTime` 
			BETWEEN STR_TO_DATE('2017-05-03 14:22:30','%Y-%m-%d %H:%i:%s')
			AND STR_TO_DATE('2017-05-03 14:24:30','%Y-%m-%d %H:%i:%s')
		) 
OR 		(
			`endDateTime`
			BETWEEN STR_TO_DATE('2017-05-03 14:22:30','%Y-%m-%d %H:%i:%s')
			AND STR_TO_DATE('2017-05-03 14:24:30','%Y-%m-%d %H:%i:%s')
		)
);

SELECT 	COUNT(*)
FROM 	`booking`
WHERE 	`meetingRoomID` = 1
AND		
(		
		(
			'2017-05-03 14:19:23'
			BETWEEN `startDateTime`
			AND `endDateTime`
		) 
OR 		(
			'2017-05-03 14:55:23'
			BETWEEN `startDateTime`
			AND `endDateTime`
		)
OR 		(
			`startDateTime` 
			BETWEEN '2017-05-03 14:22:30'
			AND '2017-05-03 14:24:30'
		) 
OR 		(
			`endDateTime`
			BETWEEN '2017-05-03 14:22:30'
			AND '2017-05-03 14:24:30'
		)        
);

SELECT 	`bookingID`,
						`meetingRoomID`									AS TheMeetingRoomID, 
						(
							SELECT	`name`
							FROM	`meetingroom`
							WHERE	`meetingRoomID` = TheMeetingRoomID 
						)												AS TheMeetingRoomName,
						`startDateTime`,
						`endDateTime`
				FROM	`booking`
				WHERE 	`cancellationCode` = '32ecc2c8f31f3f2de6c4452bd98b52ec6e705ea6c97c6cdc9d99ea91b49ce9a0'
				AND		`dateTimeCancelled` IS NULL
				LIMIT 	1;
                
UPDATE test.`user` SET `activationCode` = NULL WHERE `userID` <> 0 AND `isActive` = 1;
UPDATE test.`booking` SET `cancellationCode` = NULL WHERE `bookingID` <> 0 AND `dateTimeCancelled` IS NOT NULL;
UPDATE 	`user`
				SET		`isActive` = 1,
						`activationCode` = NULL
				WHERE 	`userID` = 43;

SELECT 		l.logID, 
							DATE_FORMAT(l.logDateTime, "%d %b %Y %T") 	AS LogDate, 
							la.`name` 									AS ActionName, 
							la.description 								AS ActionDescription, 
							l.description 								AS LogDescription 
				FROM 		`logevent` l 
				JOIN 		`logaction` la 
				ON 			la.actionID = l.actionID
                WHERE 		la.`name` = 'Account Created'
                OR 			la.`name` = 'Account Removed'
				ORDER BY 	UNIX_TIMESTAMP(l.logDateTime) 
				DESC
				LIMIT 10;

SELECT 	COUNT(*)
FROM 	`booking`
WHERE 	`meetingRoomID` = 2
AND		((`startDateTime` 
BETWEEN '2017-04-25 15:00:00' AND '2017-04-25 16:50:00') 
OR 		(`endDateTime`
BETWEEN '2017-04-25 15:00:00' AND '2017-04-25 16:50:00'))
AND 	`bookingID` != 38;

SELECT 		b.`bookingID`									AS TheBookingID,
							b.`companyID`									AS TheCompanyID,
							m.`name` 										AS BookedRoomName, 
							DATE_FORMAT(b.startDateTime, '%d %b %Y %T') 	AS StartTime, 
							DATE_FORMAT(b.endDateTime, '%d %b %Y %T') 		AS EndTime, 
							b.description 									AS BookingDescription,
							b.displayName 									AS BookedBy,
							(	
								SELECT `name` 
								FROM `company` 
								WHERE `companyID` = TheCompanyID
							)												AS BookedForCompany,
							u.`firstName`									AS UserFirstname,
							u.`lastName`									AS UserLastname,
							u.`email`										AS UserEmail
				FROM 		`booking` b 
				LEFT JOIN 	`meetingroom` m 
				ON 			b.meetingRoomID = m.meetingRoomID 
				LEFT JOIN 	`company` c 
				ON 			b.CompanyID = c.CompanyID
				LEFT JOIN 	`user` u
				ON 			b.`userID` = u.`userID`
				WHERE 		b.`bookingID` = 2
				GROUP BY 	b.`bookingID`;



UPDATE `booking`
SET `actualEndDateTime` = '2017-04-19 17:30:00'
WHERE `bookingID` = 36;

INSERT INTO `logevent` 
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Meeting Room Added'
											),
							`meetingRoomID` = 2,
							`description` = 'text';

SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
						m.`name`			AS MeetingRoomName, 
						m.`capacity`		AS MeetingRoomCapacity, 
						m.`description`		AS MeetingRoomDescription, 
						m.`location`		AS MeetingRoomLocation,
						COUNT(re.`amount`)	AS MeetingRoomEquipmentAmount
			FROM 		`meetingroom` m
			LEFT JOIN 	`roomequipment` re
			ON 			re.`meetingRoomID` = m.`meetingRoomID`
			GROUP BY 	m.`meetingRoomID`;

SELECT 	`companyID` AS CompanyID,
									`name`		AS CompanyName
							FROM 	`company`
							WHERE 	`companyID` = 2;

INSERT INTO `logaction` 
SET 
`name` = 'Booking Removed', 
`description` = 'The referenced booking was removed.';

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

SELECT BIG_SEC_TO_TIME(424*86400);

SELECT 		c.companyID 										AS CompID,
			c.`name` 											AS CompanyName, 
			COUNT(c.`name`) 									AS NumberOfEmployees,
			(SELECT BIG_SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `company` c 
			ON b.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID
			AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
			AND MONTH(b.`actualEndDateTime`) = MONTH(NOW())
            GROUP BY b.`bookingID`)   	AS MonthlyCompanyWideBookingTimeUsed,
			(
				SELECT (BIG_SEC_TO_TIME(SUM(DATEDIFF(b.`actualEndDateTime`, b.`startDateTime`))*86400 + SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)) ) ) 
			FROM `booking` b 
			INNER JOIN `company` c 
			ON b.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed,
			DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
			DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate 
FROM 		`company` c 
LEFT JOIN 	`employee` e 
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	c.`name`;


SELECT 		c.companyID 										AS CompID,
			c.`name` 											AS CompanyName, 
			COUNT(c.`name`) 									AS NumberOfEmployees,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `company` c 
			ON b.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID
			AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
			AND MONTH(b.`actualEndDateTime`) = MONTH(NOW())
            GROUP BY b.`bookingID`)   	AS MonthlyCompanyWideBookingTimeUsed,
			(SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) 
			FROM `booking` b 
			INNER JOIN `company` c 
			ON b.`CompanyID` = c.`CompanyID` 
			WHERE b.`CompanyID` = CompID)   					AS TotalCompanyWideBookingTimeUsed,
			DATE_FORMAT(c.`dateTimeCreated`, '%d %b %Y %T')		AS DatetimeCreated,
			DATE_FORMAT(c.`removeAtDate`, '%d %b %Y')			AS DeletionDate 
FROM 		`company` c 
LEFT JOIN 	`employee` e 
ON 			c.CompanyID = e.CompanyID 
GROUP BY 	c.`name`;

SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`))) AS MonthylBookingTime,
		TIME_TO_SEC(b.`actualEndDateTime`) AS StartSum,
        TIME_TO_SEC(b.`startDateTime`) AS EndSum,
        SUM(TIME_TO_SEC(b.`actualEndDateTime`) - TIME_TO_SEC(b.`startDateTime`)) AS WrongSum,
        timediff(b.`actualEndDateTime`, b.`startDateTime`) AS ActualSum
FROM `booking` b 
INNER JOIN `employee` e 
ON b.`UserID` = e.`UserID` 
INNER JOIN `company` c 
ON e.`CompanyID` = c.`CompanyID` 
WHERE b.`CompanyID` = 1
AND c.`CompanyID` = b.`companyID`
AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
AND MONTH(b.`actualEndDateTime`) = MONTH(NOW());

SELECT *
FROM `booking` b 
INNER JOIN `employee` e 
ON b.`UserID` = e.`UserID` 
INNER JOIN `company` c 
ON b.`CompanyID` = c.`CompanyID` 
WHERE b.`CompanyID` = 1
AND c.`CompanyID` = b.`companyID`
AND YEAR(b.`actualEndDateTime`) = YEAR(NOW())
AND MONTH(b.`actualEndDateTime`) = MONTH(NOW())
GROUP BY b.`bookingID`;


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

SELECT 	`userID` 	AS UserID,
						`firstname`,
						`lastname`,
						`email`
				FROM 	`user`
				WHERE	`isActive` > 0;

SELECT 	`userID` 	AS UserID,
		`firstname`,
		`lastname`,
		`email`
FROM 	`user`
WHERE	`isActive` > 0
AND 	(
		`firstname` LIKE '%4@test%'
					OR `lastname` LIKE '%4@test%'
					OR `email` LIKE '%4@test%'
		);

SELECT 	`companyID` AS CompanyID, 
		`name`	AS CompanyName 
FROM 	`company` 
WHERE 	`name` 
LIKE 	'%test%';


UPDATE `user`
SET 	`isActive` = 1
WHERE `userID` <> 0;

SELECT COUNT(*) 
FROM `user` u 
JOIN `accesslevel` a 
ON u.AccessID = a.AccessID
WHERE u.email = 'Test@test.com'
AND a.AccessName = 'Admin';

UPDATE `user`
SET `password` = 'b79fefe5115d86a696c1c880195591d644a6f6c21e22200b4b7ea0e52700a1e1'
WHERE `userID` = 1;


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

DELETE FROM `booking` WHERE `bookingID` <> 0 AND ((`actualEndDateTime` < CURDATE() - INTERVAL 30 DAY) OR  (`dateTimeCancelled` < CURDATE() - INTERVAL 30 DAY));

UPDATE `company` SET `removeAtDate` = DATE(CURRENT_TIMESTAMP) WHERE `CompanyID` = 9;

DELETE FROM `company` WHERE `removeAtDate` IS NOT NULL AND `removeAtDate` < CURRENT_TIMESTAMP AND `CompanyID` <> 0;

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
UPDATE  `company` SET  `prevStartDate` = `startDate`,   `startDate` = `endDate`,         `endDate` = (`startDate` + INTERVAL 1 MONTH) WHERE `companyID` <> 0 AND  CURDATE() > `endDate`
SELECT   COUNT(*)     FROM   `company` c     INNER JOIN  `companycredits` cc     ON    cc.`CompanyID` = c.`CompanyID`     INNER JOIN  `credits` cr     ON   cr.`CreditsID` = cc.`CreditsID`     WHERE   c.`isActive` = 1     AND   CURDATE() >= c.`endDate` LIMIT 0, 1000
