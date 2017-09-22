USE test;
SET NAMES utf8;
USE meetingflow;
SHOW WARNINGS;
SELECT CURRENT_TIMESTAMP;
/*PDO::FETCH_ASSOC*/

INSERT INTO `companycreditshistory`
SET			`CompanyID` = 2,
			`startDate` = '2017-07-15',
            `endDate` = '2017-08-15',
            `minuteAmount` = 95,
            `monthlyPrice` = 2000,
            `overCreditHourPrice` = 250;

INSERT INTO `companycredits`(`CompanyID`, `CreditsID`) VALUES (1,2);

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
		`cancellationCode` = NULL,
        `emailSent` = 1
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

UPDATE 	`company`
SET		`isActive` = 0
WHERE 	DATE(CURRENT_TIMESTAMP) >= `removeAtDate`
AND 	`isActive` = 1
AND		`companyID` <> 0;

SELECT 		c.`CompanyID`	AS CompanyID,
			c.`name`		AS CompanyName
FROM		`company` c
INNER JOIN	`employee` e
ON			e.`CompanyID` = c.`CompanyID`
INNER JOIN	`companyposition` cp
ON			cp.`PositionID` = e.`PositionID`
WHERE		cp.`name` = 'Owner'
AND 		c.`companyID`
NOT IN		(
				SELECT 	`companyID`
				FROM	`employee`
				WHERE	`userID` = 28
			)
GROUP BY	c.`CompanyID`
ORDER BY	c.`name`;

SELECT 	COUNT(*)	AS HitCount,
		`userID` 	AS UserID,
		`firstname`,
		`lastname`,
		`email`,
		(
			SELECT	`name`
			FROM	`company`
			WHERE	`CompanyID` = 2
		)			AS CompanyName
FROM 	`user`
WHERE	`isActive` > 0
AND		`email` = 'test@test.com'
LIMIT 	1;

SELECT 		c.`CompanyID`				AS TheCompanyID,
			c.`dateTimeCreated`			AS dateTimeCreated,
			c.`startDate`				AS StartDate,
			c.`endDate`					AS EndDate,
			cr.`minuteAmount`			AS CreditsGivenInMinutes,
			cr.`monthlyPrice`			AS MonthlyPrice,
			cr.`overCreditHourPrice`	AS HourPrice,
			cc.`altMinuteAmount`		AS AlternativeAmount,
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
											) > 300,
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
				WHERE 		b.`CompanyID` = TheCompanyID
				AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
				AND			b.`mergeNumber` = 0
			)							AS BookingTimeThisPeriodFromCompany,
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
											) > 300,
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
				WHERE 		b.`CompanyID` = TheCompanyID
				AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
				AND			b.`mergeNumber` <> 0
			)							AS BookingTimeThisPeriodFromTransfers
FROM 		`company` c
INNER JOIN 	`companycredits` cc
ON 			cc.`CompanyID` = c.`CompanyID`
INNER JOIN 	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
WHERE		c.`CompanyID` = 68;

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
			cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
			cc.`lastModified`									AS CompanyCreditsLastModified,
			cr.`name`											AS CreditSubscriptionName,
			cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
			cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
			cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice,
			COUNT(cch.`CompanyID`)								AS CompanyCreditsHistoryPeriods,
			SUM(cch.`hasBeenBilled`)							AS CompanyCreditsHistoryPeriodsSetAsBilled
FROM 		`company` c
LEFT JOIN	`companycredits` cc
ON			c.`CompanyID` = cc.`CompanyID`
LEFT JOIN	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
LEFT JOIN 	`companycreditshistory` cch
ON 			cch.`CompanyID` = c.`CompanyID`
GROUP BY 	c.`CompanyID`;

SELECT		StartDate, 
			EndDate,
			CompanyMergeNumber,
			CreditSubscriptionMonthlyPrice,
			CreditSubscriptionHourPrice,
			CreditsGivenInSeconds/60							AS CreditSubscriptionMinuteAmount,
			BIG_SEC_TO_TIME(CreditsGivenInSeconds) 				AS CreditsGiven,
			BIG_SEC_TO_TIME(BookingTimeChargedInSeconds) 		AS BookingTimeCharged,
			BIG_SEC_TO_TIME(
							IF(
								(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
								BookingTimeChargedInSeconds-CreditsGivenInSeconds,
                                0
                                )
			)													AS OverCreditsTimeExact,
			BIG_SEC_TO_TIME(
							FLOOR(
								(
									IF(
										(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
										BookingTimeChargedInSeconds-CreditsGivenInSeconds,
                                        0
									)
								)/'900'
							)*'900'
			)													AS OverCreditsTimeCharged
FROM (
		SELECT 	cch.`startDate`									AS StartDate,
				cch.`endDate`									AS EndDate,
				cch.`minuteAmount`*60							AS CreditsGivenInSeconds,
				cch.`monthlyPrice`								AS CreditSubscriptionMonthlyPrice,
				cch.`overCreditHourPrice`						AS CreditSubscriptionHourPrice,
				cch.`mergeNumber`								AS CompanyMergeNumber,
				(
					SELECT (IFNULL(SUM(
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
							) > '300',
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
								) > '900', 
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
								'900'
							),
						0)
					),0))
					FROM 		`booking` b
					WHERE 		b.`CompanyID` = '68'
					AND			b.`mergeNumber` = cch.`mergeNumber`
					AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
					AND			DATE(b.`actualEndDateTime`) < cch.`endDate`
				)										AS BookingTimeChargedInSeconds
			FROM 		`companycreditshistory` cch
			INNER JOIN	`companycredits` cc
			ON 			cc.`CompanyID` = cch.`CompanyID`
			INNER JOIN 	`credits` cr
			ON 			cr.`CreditsID` = cc.`CreditsID`
			WHERE 		cch.`CompanyID` = '68'
			AND 		cch.`hasBeenBilled` = 0
)													AS PeriodInformation;

SELECT 	COUNT(*)
FROM	`companycreditshistory`
WHERE	`companyID` = 2
AND		`startDate` = '2017-05-15'
AND		`endDate` = '2017-06-15'
LIMIT	1;

SELECT 	u.`userID`					AS UsrID,
		c.`companyID`				AS TheCompanyID,
		c.`name`					AS CompanyName,
		u.`firstName`, 
		u.`lastName`,
		u.`email`,
		cp.`name`					AS PositionName, 
		e.`startDateTime`			AS StartDateTime,
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
										) > 300,
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
			INNER JOIN `employee` e
			ON 			b.`userID` = e.`userID`
			INNER JOIN `company` c
			ON 			c.`companyID` = e.`companyID`
			INNER JOIN 	`user` u 
			ON 			e.`UserID` = u.`UserID` 
			WHERE 		b.`userID` = UsrID
			AND 		b.`companyID` = 2
			AND 		c.`CompanyID` = b.`companyID`
			AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
			AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
		) 							AS PreviousMonthBookingTimeUsed,						
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
										) > 300,
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
			INNER JOIN `employee` e
			ON 			b.`userID` = e.`userID`
			INNER JOIN `company` c
			ON 			c.`companyID` = e.`companyID`
			INNER JOIN 	`user` u 
			ON 			e.`UserID` = u.`UserID` 
			WHERE 		b.`userID` = UsrID
			AND 		b.`companyID` = 2
			AND 		c.`CompanyID` = b.`companyID`
			AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
			AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
		) 							AS MonthlyBookingTimeUsed,
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
										) > 300,
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
			INNER JOIN `employee` e
			ON 			b.`userID` = e.`userID`
			INNER JOIN `company` c
			ON 			c.`companyID` = e.`companyID`
			INNER JOIN 	`user` u 
			ON 			e.`UserID` = u.`UserID` 
			WHERE 		b.`userID` = UsrID
			AND 		b.`companyID` = 2
			AND 		c.`CompanyID` = b.`companyID`
		) 							AS TotalBookingTimeUsed							
FROM 	`company` c 
JOIN 	`employee` e
ON 		e.CompanyID = c.CompanyID 
JOIN 	`companyposition` cp 
ON 		cp.PositionID = e.PositionID
JOIN 	`user` u 
ON 		u.userID = e.UserID 
WHERE 	c.`companyID` = 2;

SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
			m.`name`			AS MeetingRoomName, 
			m.`capacity`		AS MeetingRoomCapacity, 
			m.`description`		AS MeetingRoomDescription, 
			m.`location`		AS MeetingRoomLocation,
			(
				SELECT 	COUNT(*)
				FROM 	`roomequipment` re
				WHERE 	re.`MeetingRoomID` = TheMeetingRoomID
			)					AS MeetingRoomEquipmentAmount,
            (
				SELECT	COUNT(*)
                FROM	`booking` b
                WHERE	b.`meetingRoomID` = TheMeetingRoomID
                AND		b.`actualEndDateTime` IS NULL
                AND		b.`dateTimeCancelled` IS NULL
                AND		CURRENT_TIMESTAMP 
                BETWEEN	b.`startDateTime` 
                AND 	b.`endDateTime`
            )					AS MeetingRoomStatus,
			(
				SELECT		b.`startDateTime`
                FROM		`booking` b
                WHERE		b.`meetingRoomID` = TheMeetingRoomID
                AND			b.`actualEndDateTime` IS NULL
                AND			b.`dateTimeCancelled` IS NULL
                AND			CURRENT_DATE = DATE(b.`startDateTime`)
                ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
                ASC
                LIMIT 1
            )					AS NextMeetingStart           
FROM 		`meetingroom` m;


SELECT 		cp.`name`					AS CompanyPosition,
			c.`name`					AS CompanyName,
			c.`CompanyID`				AS CompanyID,
			e.`sendEmailOnceOrAlways`	AS SendEmailOnceOrAlways
FROM		`employee` e
INNER JOIN	`company` c
ON			c.`CompanyID` = e.`CompanyID`
INNER JOIN	`companyposition` cp
ON			e.`PositionID` = cp.`PositionID`
WHERE		e.`userID` = 28;

SELECT		u.`email`		AS Email,
			u.`sendEmail`	AS SendEmail
FROM 		`user` u
INNER JOIN	`employee` e
ON 			e.`UserID` = u.`UserID`
INNER JOIN	`company` c
ON 			c.`CompanyID` = e.`CompanyID`
INNER JOIN	`companyposition` cp
ON			e.`PositionID` = cp.`PositionID`
WHERE 		c.`CompanyID` = 4
AND			cp.`name` = "Owner"
AND			u.`email` <> "d@d.com";

SELECT SUM(cnt) AS HitCount
FROM 
(
	(
		SELECT 1 AS cnt
    )
    UNION
 	(
		SELECT 1 AS cnt
    ) 
	UNION
 	(
		SELECT 0 AS cnt
    )
 	UNION
 	(
		SELECT 1 AS cnt
    )   
) AS TimeSlotTaken;


SELECT 		m.`meetingRoomID`	AS MeetingRoomID,
			m.`name`			AS MeetingRoomName
FROM 		`meetingroom` m
WHERE		m.`meetingRoomID` 
NOT IN
(
	SELECT 		b.`meetingRoomID`
	FROM 		`booking` b
	WHERE 		b.`dateTimeCancelled` IS NULL
	AND			b.`actualEndDateTime` IS NULL
	AND
			(		
					(
						b.`startDateTime` >= '2017-08-11 13:00:00' AND 
						b.`startDateTime` < '2017-08-11 17:00:00'
					) 
			OR 		(
						b.`endDateTime` > '2017-08-11 13:00:00' AND 
						b.`endDateTime` <= '2017-08-11 17:00:00'
					)
			OR 		(
						'2017-08-11 17:00:00' > b.`startDateTime` AND 
						'2017-08-11 17:00:00' < b.`endDateTime`
					)
			OR 		(
						'2017-08-11 13:00:00' > b.`startDateTime` AND 
						'2017-08-11 13:00:00' < b.`endDateTime`
					)
			)
)
AND 	m.`meetingRoomID`
NOT IN
(
	SELECT 		rev.`meetingRoomID`
	FROM 		`roomevent` rev
	WHERE 
			(		
					(
						rev.`startDateTime` >= '2017-08-11 13:00:00' AND 
						rev.`startDateTime` < '2017-08-11 17:00:00'
					) 
			OR 		(
						rev.`endDateTime` > '2017-08-11 13:00:00' AND 
						rev.`endDateTime` <= '2017-08-11 17:00:00'
					)
			OR 		(
						'2017-08-11 17:00:00' > rev.`startDateTime` AND 
						'2017-08-11 17:00:00' < rev.`endDateTime`
					)
			OR 		(
						'2017-08-11 13:00:00' > rev.`startDateTime` AND 
						'2017-08-11 13:00:00' < rev.`endDateTime`
					)
			)
);

SELECT 		m.`meetingRoomID`	AS MeetingRoomID,
			m.`name`			AS MeetingRoomName
FROM 		`meetingroom` m
WHERE		m.`meetingRoomID` 
NOT IN
(
	SELECT * 
	FROM 
	(
		(
		SELECT 		b.`meetingRoomID`
		FROM 		`booking` b
		WHERE 		b.`dateTimeCancelled` IS NULL
		AND			b.`actualEndDateTime` IS NULL
		AND
				(		
						(
							b.`startDateTime` >= '2017-08-11 13:00:00' AND 
							b.`startDateTime` < '2017-08-11 17:00:00'
						) 
				OR 		(
							b.`endDateTime` > '2017-08-11 13:00:00' AND 
							b.`endDateTime` <= '2017-08-11 17:00:00'
						)
				OR 		(
							'2017-08-11 17:00:00' > b.`startDateTime` AND 
							'2017-08-11 17:00:00' < b.`endDateTime`
						)
				OR 		(
							'2017-08-11 13:00:00' > b.`startDateTime` AND 
							'2017-08-11 13:00:00' < b.`endDateTime`
						)
				)
		)
		UNION
		(
		SELECT 		rev.`meetingRoomID`
		FROM 		`roomevent` rev
		WHERE 
				(		
						(
							rev.`startDateTime` >= '2017-08-11 13:00:00' AND 
							rev.`startDateTime` < '2017-08-11 17:00:00'
						) 
				OR 		(
							rev.`endDateTime` > '2017-08-11 13:00:00' AND 
							rev.`endDateTime` <= '2017-08-11 17:00:00'
						)
				OR 		(
							'2017-08-11 17:00:00' > rev.`startDateTime` AND 
							'2017-08-11 17:00:00' < rev.`endDateTime`
						)
				OR 		(
							'2017-08-11 13:00:00' > rev.`startDateTime` AND 
							'2017-08-11 13:00:00' < rev.`endDateTime`
						)
				)
		)
	) AS OccupiedMeetingRooms
);


SELECT 		m.`name`			AS MeetingRoomName,
			m.`meetingRoomID` 	AS MeetingRoomID,
			b.`bookingID`		AS BookingID
FROM		`meetingroom` m
INNER JOIN 	`booking` b
ON			b.`meetingRoomID` = m.`meetingRoomID`
WHERE		`actualEndDateTime` IS NULL
AND 		`dateTimeCancelled` IS NULL
AND 		b.`bookingID`
NOT IN	
(
	SELECT 	`bookingID`
    FROM 	`booking`
    WHERE	`actualEndDateTime` IS NULL
    AND		`dateTimeCancelled` IS NULL
	AND								
		(		
				(
					`startDateTime` >= '2017-08-08 15:30:00' AND 
					`startDateTime` < '2017-08-08 17:00:00'
				) 
		OR 		(
					`endDateTime` > '2017-08-08 15:30:00' AND 
					`endDateTime` <= '2017-08-08 17:00:00'
				)
		OR 		(
					'2017-08-08 17:00:00' > `startDateTime` AND 
					'2017-08-08 17:00:00' < `endDateTime`
				)
		OR 		(
					'2017-08-08 15:30:00' > `startDateTime` AND 
					'2017-08-08 15:30:00' < `endDateTime`
				)
		)
);


SELECT 		m.`name`			AS MeetingRoomName,
			m.`meetingRoomID` 	AS MeetingRoomID,
			b.`bookingID`		AS BookingID,
            rev.`EventID`		AS EventID
FROM		`meetingroom` m
LEFT JOIN	`booking` b
ON 			b.`meetingRoomID` = m.`meetingRoomID`
AND			b.`actualEndDateTime` IS NULL
AND			b.`dateTimeCancelled` IS NULL
AND								
	(		
			(
				b.`startDateTime` >= '2017-08-08 15:30:00' AND 
				b.`startDateTime` < '2017-08-08 17:00:00'
			) 
	OR 		(
				b.`endDateTime` > '2017-08-08 15:30:00' AND 
				b.`endDateTime` <= '2017-08-08 17:00:00'
			)
	OR 		(
				'2017-08-08 17:00:00' > b.`startDateTime` AND 
				'2017-08-08 17:00:00' < b.`endDateTime`
			)
	OR 		(
				'2017-08-08 15:30:00' > b.`startDateTime` AND 
				'2017-08-08 15:30:00' < b.`endDateTime`
			)
	)
LEFT JOIN 	`roomevent` rev
ON			b.`meetingRoomID` = rev.`meetingRoomID`
AND	
		(		
				(
					rev.`startDateTime` >= '2017-08-08 15:30:00' AND 
					rev.`startDateTime` < '2017-08-08 17:00:00'
				) 
		OR 		(
					rev.`endDateTime` > '2017-08-08 15:30:00' AND 
					rev.`endDateTime` <= '2017-08-08 17:00:00'
				)
		OR 		(
					'2017-08-08 17:00:00' > rev.`startDateTime` AND 
					'2017-08-08 17:00:00' < rev.`endDateTime`
				)
		OR 		(
					'2017-08-08 15:30:00' > rev.`startDateTime` AND 
					'2017-08-08 15:30:00' < rev.`endDateTime`
				)
		)
WHERE	rev.`EventID` IS NULL
AND 	b.`meetingRoomID` IS NULL;


SELECT 		m.`name`			AS MeetingRoomName,
			m.`meetingRoomID` 	AS MeetingRoomID,
			b.`bookingID`		AS BookingID
FROM		`booking` b
INNER JOIN	`meetingroom` m
ON 			b.`meetingRoomID` = m.`meetingRoomID`
WHERE 		b.`actualEndDateTime` IS NULL
AND			b.`dateTimeCancelled` IS NULL
AND			b.`startDateTime` <= CURRENT_TIMESTAMP
AND			b.`endDateTime` > CURRENT_TIMESTAMP
AND
(
	(
		b.`startDateTime` <= '2017-08-11 13:45:00' AND
        b.`endDateTime` >= '2017-08-11 16:30:00' AND
        
    )
);

INSERT INTO `user`(`firstname`, `lastname`, `password`, `activationcode`, `email`, `accessID`)
SELECT		'',
			'',
			'1234567890123456789012345678901234567890123456789012345678901234',
			'1234567890123456789012345678901234567890123456789012345678901234',
			'test@email',
			IF(
				(a.`AccessName` = "Normal User"),
				(a.`AccessID`),
				(
					SELECT 	`AccessID`
					FROM	`accesslevel`
					WHERE	`AccessName` = "In-House User"
				)
			) AS AccessID
FROM 		`accesslevel` a
INNER JOIN	`user` u
ON			u.`AccessID` = a.`AccessID`
WHERE		u.`UserID` = 28;

SELECT 		COUNT(*) 	AS HitCount	
FROM		`user` u
INNER JOIN	`accesslevel` a
ON 			u.`AccessID` = a.`AccessID`
WHERE 		u.`userID` = 2
AND			a.`AccessName` = "Admin"
LIMIT		1;

SELECT 		COUNT(*)		AS HitCount,
			b.`userID`,
			u.`email`		AS UserEmail,
			u.`firstName`,
			u.`lastName`
FROM		`booking` b
INNER JOIN	`employee` e
ON			e.`CompanyID` = b.`CompanyID`
INNER JOIN 	`user` u
ON 			e.`userID` = u.`userID`
INNER JOIN	`companyposition` cp
ON			cp.`PositionID` = e.`PositionID`
WHERE 		b.`bookingID` = 204
AND			e.`UserID` = 56
AND			cp.`name` = "Owner"
LIMIT 		1;

SELECT		c.`companyID`,
			c.`name` 					AS companyName,
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
											) > 300,
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
				WHERE 		b.`CompanyID` = e.`CompanyID`
				AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
			)													AS MonthlyCompanyWideBookingTimeUsed,
			(
				SELECT 	IFNULL(cc.`altMinuteAmount`, cr.`minuteAmount`)
                FROM 		`company` c
				INNER JOIN	`companycredits` cc
				ON			c.`CompanyID` = cc.`CompanyID`
				INNER JOIN	`credits` cr
				ON			cr.`CreditsID` = cc.`CreditsID`
                WHERE		c.`CompanyID` = e.`CompanyID`
                
            ) 													AS CreditSubscriptionMinuteAmount
FROM 		`user` u
INNER JOIN 	`employee` e
ON 			e.`userID` = u.`userID`
INNER JOIN	`company` c
ON 			c.`companyID` = e.`companyID`
WHERE 		u.`userID` = 28;

SELECT COUNT(*)
FROM 	`booking`
WHERE 	`companyID` = 2;

SELECT 		c.`companyID` 										AS CompanyID,
			c.`name` 											AS CompanyName,
			c.`dateTimeCreated`									AS DatetimeCreated,
			c.`removeAtDate`									AS DeletionDate,
			c.`isActive`										AS CompanyActivated,
			(
				SELECT 	COUNT(e.`CompanyID`)
				FROM 	`employee` e
				WHERE 	e.`companyID` = 2
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
											) > 300,
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
				AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
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
											) > 300,
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
				AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
											) > 300,
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
				WHERE 		b.`CompanyID` = 2
			)													AS TotalCompanyWideBookingTimeUsed,
			(
				SELECT 	COUNT(*)
				FROM	`booking`
				WHERE	`companyID` = 2
			)													AS TotalBookedMeetings,
            (
				SELECT 	COUNT(*)
				FROM	`booking`
				WHERE	`companyID` = 2
				AND 	`actualEndDateTime` IS NULL
				AND 	`dateTimeCancelled` IS NULL
				AND 	`endDateTime` > CURRENT_TIMESTAMP
			)													AS ActiveBookedMeetings,
			(
				SELECT 	COUNT(*)
				FROM	`booking`
				WHERE	`companyID` = 2
				AND 	(
							`actualEndDateTime` IS NOT NULL
						OR
							(
										`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NULL
								AND 	`endDateTime` <= CURRENT_TIMESTAMP
							)
						)
			)													AS CompletedBookedMeetings,
			(
				SELECT 	COUNT(*)
				FROM	`booking`
				WHERE	`companyID` = 2
				AND 	`actualEndDateTime` IS NULL
				AND 	`dateTimeCancelled` IS NOT NULL
			)													AS CancelledBookedMeetings,
			cc.`altMinuteAmount`								AS CompanyAlternativeMinuteAmount,
			cc.`lastModified`									AS CompanyCreditsLastModified,
			cr.`name`											AS CreditSubscriptionName,
			cr.`minuteAmount`									AS CreditSubscriptionMinuteAmount,
			cr.`monthlyPrice`									AS CreditSubscriptionMonthlyPrice,
			cr.`overCreditHourPrice`							AS CreditSubscriptionHourPrice
FROM 		`company` c
LEFT JOIN	`companycredits` cc
ON			c.`CompanyID` = cc.`CompanyID`
LEFT JOIN	`credits` cr
ON			cr.`CreditsID` = cc.`CreditsID`
LEFT JOIN 	`companycreditshistory` cch
ON 			cch.`CompanyID` = c.`CompanyID`
WHERE		c.`CompanyID` = 2
GROUP BY 	c.`CompanyID`
LIMIT 		1;

SELECT 		COUNT(*) 	AS HitCount,
			cp.`name` 	AS CompanyPosition
FROM 		`employee` e
INNER JOIN `companyposition` cp
ON 			cp.`PositionID` = e.`PositionID`
WHERE		`CompanyID` = 5
AND 		`UserID` = 7
LIMIT 		1;

SELECT 	*
FROM	`booking`
WHERE	`userID` = 28;

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
			u.`firstName` 									AS firstName,
			u.`lastName`									AS lastName,
			u.`email` 										AS email,
			(
				SELECT 		GROUP_CONCAT(c.`name` separator ",\n")
				FROM 		`company` c
				INNER JOIN 	`employee` e
				ON 			e.`CompanyID` = c.`CompanyID`
				WHERE  		e.`userID` = b.`userID`
				AND			c.`isActive` = 1
				GROUP BY 	e.`userID`
			)												AS WorksForCompany,		 
			b.`description`									AS BookingDescription,
			b.`dateTimeCreated`								AS BookingWasCreatedOn, 
			b.`actualEndDateTime`							AS BookingWasCompletedOn, 
			b.`dateTimeCancelled`							AS BookingWasCancelledOn 
FROM 		`booking` b
INNER JOIN 	`user` u
ON 			u.`UserID` = b.`UserID`
WHERE		b.`UserID` = 28
ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
ASC;

SELECT 		u.`email`				AS Email,
			u.`firstName`			AS FirstName,
			u.`lastName`			AS LastName,
			u.`displayName`			AS DisplayName,
			u.`bookingDescription`	AS BookingDescription,
			u.`bookingCode`			AS BookingCode,
            u.`lastCodeUpdate` 		AS LastCodeUpdate,
			DATE_ADD(
						u.`lastCodeUpdate`,
                        INTERVAL 30 DAY
					)				AS NextBookingCodeChange,
			u.`create_time`			AS DateTimeCreated,
            u.`lastActivity`		AS LastActive,
			u.`sendEmail`			AS SendEmail,
			u.`sendAdminEmail`		AS SendAdminEmail,
			u.`password`			AS HashedPassword,
			a.`AccessName`			AS AccessName,
			a.`Description` 		AS AccessDescription,
            (
				SELECT 	COUNT(*)
                FROM	`booking`
				WHERE	`userID` = 28
            )						AS TotalBookedMeetings,
            (
				SELECT 	COUNT(*)
                FROM	`booking`
				WHERE	`userID` = 28
                AND 	`actualEndDateTime` IS NULL
                AND 	`dateTimeCancelled` IS NULL
                AND 	`endDateTime` > CURRENT_TIMESTAMP
            )						AS ActiveBookedMeetings,
            (
				SELECT 	COUNT(*)
                FROM	`booking`
				WHERE	`userID` = 28
                AND 	(
							`actualEndDateTime` IS NOT NULL
						OR
							(
										`actualEndDateTime` IS NULL
								AND 	`dateTimeCancelled` IS NULL
								AND 	`endDateTime` <= CURRENT_TIMESTAMP
                            )
                        )
            )						AS CompletedBookedMeetings,
            (
				SELECT 	COUNT(*)
                FROM	`booking`
				WHERE	`userID` = 28
                AND 	`actualEndDateTime` IS NULL
                AND 	`dateTimeCancelled` IS NOT NULL
            )						AS CancelledBookedMeetings
FROM		`user` u
INNER JOIN	`accesslevel` a
ON 			u.`AccessID` = a.`AccessID`
WHERE 		`userID` = 28
AND			`isActive` = 1
LIMIT 		1;

SELECT 	`meetingRoomID`	AS MeetingRoomID,
		`name` 			AS MeetingRoomName
FROM 	`meetingroom`
WHERE 	`name` LIKE '%å%';

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
						INNER JOIN `employee` e
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
WHERE		b.`meetingRoomID` = 21
AND			b.`dateTimeCancelled` IS NULL
AND 		b.`actualEndDateTime` IS NULL
ORDER BY 	UNIX_TIMESTAMP(b.`startDateTime`)
ASC;

INSERT INTO `user`(`firstname`, `lastname`, `password`, `activationcode`, `email`, `accessID`)
SELECT	'la',
		'da', 
        'b79fefe5115d86a696c1c880195591d644a6f6c21e22200b4b7ea0e52700a1e1', 
        'b79fefe5115d86a696c1c880195591d644a6f6c21e22200b4b7ea0e52700a1e1', 
        'la@da', 
        `accessID`
FROM 	`accesslevel`
WHERE	`AccessName` = 'Normal User';

SELECT  	m.`meetingRoomID`	AS TheMeetingRoomID, 
			m.`name`			AS MeetingRoomName, 
			m.`capacity`		AS MeetingRoomCapacity, 
			m.`description`		AS MeetingRoomDescription, 
			m.`location`		AS MeetingRoomLocation,
			(
				SELECT 	COUNT(*)
                FROM 	`roomequipment` re
                WHERE 	re.`MeetingRoomID` = TheMeetingRoomID
			)					AS MeetingRoomEquipmentAmount,
			(
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
				AND 	b.`endDateTime` > current_timestamp
				AND 	b.`dateTimeCancelled` IS NULL
				AND 	b.`actualEndDateTime` IS NULL
			)					AS MeetingRoomActiveBookings,
			(
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
				AND 	b.`actualEndDateTime` < current_timestamp
				AND 	b.`dateTimeCancelled` IS NULL
			)					AS MeetingRoomCompletedBookings,
			(
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
				AND 	b.`dateTimeCancelled` < current_timestamp
				AND 	b.`actualEndDateTime` IS NULL
			)					AS MeetingRoomCancelledBookings						
FROM 		`meetingroom` m;

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
			)					AS MeetingRoomActiveBookings,
			(
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
				AND 	b.`actualEndDateTime` < current_timestamp
				AND 	b.`dateTimeCancelled` IS NULL
			)					AS MeetingRoomCompletedBookings,
			(
				SELECT 	COUNT(b.`bookingID`)
				FROM	`booking` b
				WHERE  	b.`meetingRoomID` = TheMeetingRoomID
				AND 	b.`dateTimeCancelled` < current_timestamp
				AND 	b.`actualEndDateTime` IS NULL
			)					AS MeetingRoomCancelledBookings						
FROM 		`meetingroom` m
LEFT JOIN 	`roomequipment` re
ON 			re.`meetingRoomID` = m.`meetingRoomID`			
GROUP BY 	m.`meetingRoomID`;

SELECT 		e.`EquipmentID`									AS TheEquipmentID,
			e.`name`										AS EquipmentName,
			e.`description`									AS EquipmentDescription,
			e.`datetimeAdded`								AS DateTimeAdded,
			UNIX_TIMESTAMP(e.`datetimeAdded`)				AS OrderByDate,
			(
				SELECT 		GROUP_CONCAT(m.`name` separator ",\n")
                FROM 		`meetingroom` m
                INNER JOIN 	`roomequipment` re
                ON 			m.`meetingRoomID` = re.`meetingRoomID`
                WHERE		re.`equipmentID` = TheEquipmentID
                GROUP BY	re.`equipmentID`
            )												AS EquipmentIsInTheseRooms
FROM 		`equipment` e
ORDER BY	OrderByDate
DESC;

SELECT 		e.`EquipmentID`									AS TheEquipmentID,
			e.`name`										AS EquipmentName,
			e.`description`									AS EquipmentDescription,
			e.`datetimeAdded`								AS DateTimeAdded,
			UNIX_TIMESTAMP(e.`datetimeAdded`)				AS OrderByDate,
			GROUP_CONCAT(m.`name` separator ', ')			AS EquipmentIsInTheseRooms
FROM 		`equipment` e
LEFT JOIN 	`roomequipment` re
ON 			e.`EquipmentID` = re.`EquipmentID`
LEFT JOIN 	`meetingroom` m
ON 			m.`meetingRoomID` = re.`meetingRoomID`
GROUP BY 	e.`EquipmentID`
ORDER BY	OrderByDate
DESC;

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
			(
				SELECT 	COUNT(cc.`CreditsID`)
                FROM 	`companycredits` cc
                WHERE 	cc.`CreditsID` = TheCreditsID
            )												AS CreditsIsUsedByThisManyCompanies
FROM 		`credits` cr
ORDER BY	OrderByDate
DESC;

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

SELECT 		(
				SELECT 	`name`
				FROM 	`meetingroom`
				WHERE 	`meetingRoomID` = b.`meetingRoomID`
			)							AS MeetingRoomName,			
			(
				SELECT 	`name`
				FROM 	`company`
				WHERE 	`companyID` = b.`companyID`
			)							AS CompanyName,
			u.`email`					AS UserEmail,
			b.`bookingID`				AS TheBookingID,
			b.`dateTimeCreated`			AS DateCreated,
			b.`startDateTime`			AS StartDate,
			b.`endDateTime`				AS EndDate,
			b.`displayName`				AS DisplayName,
			b.`description`				AS BookingDescription,
			b.`cancellationCode`		AS CancelCode
FROM		`booking` b
INNER JOIN `user` u
ON 			b.`userID` = u.`userID`
WHERE 		DATE_SUB(b.`startDateTime`, INTERVAL 30 MINUTE) < CURRENT_TIMESTAMP
AND			DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
AND 		b.`dateTimeCancelled` IS NULL
AND 		b.`actualEndDateTime` IS NULL
AND			b.`cancellationCode` IS NOT NULL
AND 		DATE_ADD(b.`dateTimeCreated`, INTERVAL 0 MINUTE) < CURRENT_TIMESTAMP
AND			b.`emailSent` = 0
AND			u.`sendEmail` = 1
AND			b.`bookingID` <> 0;

SELECT 	(
			SELECT 	`name`
			FROM 	`meetingroom`
			WHERE 	`meetingRoomID` = b.`meetingRoomID`
        )							AS MeetingRoomName,			
		(
			SELECT 	`name`
			FROM 	`company`
			WHERE 	`companyID` = b.`companyID`
        )							AS CompanyName,
 		(
			SELECT 	`email`
			FROM 	`user`
			WHERE 	`userID` = b.`userID`
        )							AS UserEmail,
		b.`bookingID`				AS TheBookingID,
		b.`dateTimeCreated`			AS DateCreated,
		b.`startDateTime`			AS StartDate,
		b.`endDateTime`				AS EndDate,
		b.`displayName`				AS DisplayName,
		b.`description`				AS BookingDescription,
		b.`cancellationCode`		AS CancelCode
FROM	`booking` b
WHERE 	DATE_SUB(b.`startDateTime`, INTERVAL 30 MINUTE) < CURRENT_TIMESTAMP
AND		DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
AND 	b.`dateTimeCancelled` IS NULL
AND 	b.`actualEndDateTime` IS NULL
AND		b.`cancellationCode` IS NOT NULL
AND 	DATE_ADD(b.`dateTimeCreated`, INTERVAL 0 MINUTE) < CURRENT_TIMESTAMP
AND		b.`emailSent` = 0
AND		b.`bookingID` <> 0;

SELECT 	m.`name`					AS MeetingRoomName,
		c.`name`					AS CompanyName,
		u.`email`					AS UserEmail,
		b.`bookingID`				AS TheBookingID,
		b.`dateTimeCreated`			AS DateCreated,
		b.`startDateTime`			AS StartDate,
		b.`endDateTime`				AS EndDate,
		b.`displayName`				AS DisplayName,
		b.`description`				AS BookingDescription,
		b.`cancellationCode`		AS CancelCode
FROM	`booking` b
JOIN 	`meetingroom` m
ON 		b.`meetingRoomID` = m.`meetingRoomID`
JOIN	`company` c
ON 		c.`companyID` = b.`companyID`
JOIN	`user` u
ON		u.`userID` = b.`userID`
WHERE 	DATE_SUB(b.`startDateTime`, INTERVAL 30 MINUTE) < CURRENT_TIMESTAMP
AND		DATE_SUB(b.`startDateTime`, INTERVAL 1 MINUTE) > CURRENT_TIMESTAMP
AND 	b.`dateTimeCancelled` IS NULL
AND 	b.`actualEndDateTime` IS NULL
AND		b.`cancellationCode` IS NOT NULL
AND 	DATE_ADD(b.`dateTimeCreated`, INTERVAL 0 MINUTE) < CURRENT_TIMESTAMP
AND		b.`emailSent` = 0
AND		b.`bookingID` <> 0;

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
					SELECT 	`sendEmail`
					FROM 	`user`
					WHERE 	`userID` = TheUserID
				)
            )												AS sendEmail,
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
WHERE		b.`bookingID` = 135
LIMIT 		1;

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
				AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
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
				AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
AND         DATE(b.`actualEndDateTime`) >= '2017-03-15'
AND         DATE(b.`actualEndDateTime`) < '2017-06-15';

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
AND         DATE(b.`actualEndDateTime`) >= '2017-03-15'
AND         DATE(b.`actualEndDateTime`) < '2017-06-15';

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


SELECT 	SUM(cnt)	AS HitCount,
		(SELECT `name` FROM `meetingroom` WHERE `meetingRoomID` = 32) AS MeetingRoomName
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
									IF(
										(BookingTimeChargedInSeconds>CreditsGivenInSeconds),
										BookingTimeChargedInSeconds-CreditsGivenInSeconds, 
										0
									)
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
			(SELECT (IFNULL(SUM(
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
				),0))
			FROM 		`booking` b
			WHERE 		b.`CompanyID` = 2
			AND 		DATE(b.`actualEndDateTime`) >= cch.`startDate`
			AND 		DATE(b.`actualEndDateTime`) < cch.`endDate`
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
				AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
AND 		DATE(b.`actualEndDateTime`) < c.`endDate`;

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
AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
AND 		DATE(b.`actualEndDateTime`) < c.`endDate`;

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
AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
				AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
				AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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
AND         DATE(b.`actualEndDateTime`) >=  c.`startDate`
AND         DATE(b.`actualEndDateTime`) <  c.`endDate`;

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
			AND			b.`userID` NOT IN (SELECT `userID` FROM `employee` WHERE `CompanyID` = 2)
			AND 		b.`userID` = UsrID
			AND 		DATE(b.`actualEndDateTime`) >= c.`prevStartDate`
			AND 		DATE(b.`actualEndDateTime`) < c.`startDate`
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
			AND 		DATE(b.`actualEndDateTime`) >= c.`startDate`
			AND 		DATE(b.`actualEndDateTime`) < c.`endDate`
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