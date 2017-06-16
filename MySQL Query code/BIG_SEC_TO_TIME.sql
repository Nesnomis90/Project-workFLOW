DROP FUNCTION IF EXISTS BIG_SEC_TO_TIME;
DELIMITER $$
CREATE FUNCTION BIG_SEC_TO_TIME(SECS BIGINT)
RETURNS TEXT
READS SQL DATA
DETERMINISTIC
BEGIN
	DECLARE HEURES TEXT;
	DECLARE MINUTES CHAR(5);
	DECLARE SECONDES CHAR(5);

	IF (SECS IS NULL) THEN RETURN NULL; END IF;

	SET HEURES = FLOOR(SECS / 3600);

	SET MINUTES = FLOOR((SECS - (HEURES*3600)) / 60);

	SET SECONDES = MOD(SECS, 60);

	IF MINUTES < 10 THEN SET MINUTES = CONCAT( "0", MINUTES); END IF;
	IF SECONDES < 10 THEN SET SECONDES = CONCAT( "0", SECONDES); END IF;

	RETURN CONCAT(HEURES, ":", MINUTES, ":", SECONDES);
END;
$$
DELIMITER ;