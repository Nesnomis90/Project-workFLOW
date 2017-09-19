<?php
// This holds all the adjustable variables/defines we use througout the code

// Our Contact/Email information
define('EMAIL_USED_FOR_SENDING_INFORMATION', 'jusimonsen@gmail.com'); // TO-DO: REPLACE WITH PROPER EMAIL AFTER UPLOADING.
define('FROM_NAME_USED_IN_EMAIL', "Meeting FLOW booking service");	// TO-DO: REPLACE WITH WANTED FROM NAME
define('CONTACT_INFO_SENT_IN_MAIL', "http://www.flownorway.com/contact-us/");	// TO-DO: REPLACE WITH WANTED CONTACT INFO

// Database
	// Connection definitions
//$dbengine 	= 'mysql';
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '5Bdp32LAHYQ8AemvQM9P');
define('DB_NAME', 'test');

// Cookies
	// Cookie names we use to identify if the website is accessed locally by a meeting room panel
	// TO-DO: Change after uploading?
define('MEETINGROOM_NAME', 'Temp_Cookie_Name_To_Hold_Meeting_Room_Name'); 
define('MEETINGROOM_IDCODE', 'Temp_Cookie_Name_To_Hold_Meeting_Room_ID_CODE'); 

// Datetime
	// Define the default date and datetime format we want to use
	// Also the default timezone we use for our datetime functions
define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS', 'H:i:s j F Y'); //<- What we want
define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY', 'H:i j F Y'); //<- What we want
define('DATE_DEFAULT_FORMAT_TO_DISPLAY', 'j F Y'); //<- What we want
define('DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR', 'j F'); //<- What we want
//define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS', 'F jS Y H:i:s'); //To-DO: REPLACE WITH ABOVE
//define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY', 'F jS Y H:i'); //To-DO: REPLACE WITH ABOVE
//define('DATE_DEFAULT_FORMAT_TO_DISPLAY', 'F jS Y'); //To-DO: REPLACE WITH ABOVE
//define('DATE_DEFAULT_FORMAT_TO_DISPLAY_WITHOUT_YEAR', 'F jS'); //To-DO: REPLACE WITH ABOVE
define('TIME_DEFAULT_FORMAT_TO_DISPLAY', 'H:i');
define('DATE_DEFAULT_TIMEZONE', 'Europe/Oslo');

// Currency
define('SET_CURRENCY', 'NOK');
define('SET_CURRENCY_SYMBOL', '');
define('SET_CURRENCY_DECIMAL_PRECISION', '2');
define('SET_CURRENCY_STEP_PRECISION', '0.01');

// Timing variables
	// variables used to validate code, handle events (minutes)
define('MINIMUM_BOOKING_TIME_IN_MINUTES', 15); //1, 5, 10, 15, 30 or 60
define('MINIMUM_TIME_PASSED_IN_MINUTES_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL', 2);
define('TIME_LEFT_IN_MINUTES_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL', 30);
define('MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS', 15);
define('BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS', 5);
define('ROUND_SUMMED_BOOKING_TIME_CHARGED_FOR_PERIOD_DOWN_TO_THIS_CLOSEST_MINUTE_AMOUNT', 15);
define('BOOKING_CODE_WRONG_GUESS_TIMEOUT_IN_MINUTES', 5); // Doesn't work on numbers less than 2
define('WRONG_LOGIN_GUESS_TIMEOUT_IN_MINUTES', 15);

	// refresh timers (seconds)
define('SECONDS_BEFORE_REFRESHING_BOOKING_PAGE', 15);
define('SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE', 15);
define('SECONDS_BEFORE_REFRESHING_ADMIN_PAGES', 15);

// Length variables
define('MINIMUM_PASSWORD_LENGTH', 6);
define('BOOKING_CODE_LENGTH', 6);

// Repetition variables
define('MAXIMUM_BOOKING_CODE_GUESSES', 5);
define('MAXIMUM_ADMIN_BOOKING_CODE_GUESSES', 3);
define('MAXIMUM_WRONG_LOGIN_GUESSES', 5);
define('MAXIMUM_WRONG_LOGIN_TIMEOUTS', 3);
define('MAX_NUMBER_OF_EMAILS_TO_SEND_AT_ONCE', 5); // TO-DO: FIX-ME: Change to an appropriate amount

// Numbers we use
define('MAXIMUM_FLOAT_NUMBER', 65535);
define('MAXIMUM_UNSIGNED_SMALLINT_NUMBER', 65535); // unsigned SMALLINT max number for MYSQL is 65535
define('MAXIMUM_UNSIGNED_TINYINT_NUMBER', 255);	// unsigned TINYINT max number for MYSQL is 255
?>