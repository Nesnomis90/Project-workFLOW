<?php
// This holds all the adjustable variables/defines we use througout the code

// Database
	// Connection definitions
//$dbengine 	= 'mysql';
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '5Bdp32LAHYQ8AemvQM9P');
define('DB_NAME', 'test');

// Cookies
	// Cookie names we use to identify if the website is accessed locally by a meeting room panel
	// TO-DO: Change after uploading
define('MEETINGROOM_NAME', 'Temp_Cookie_Name_To_Hold_Meeting_Room_Name'); 
define('MEETINGROOM_IDCODE', 'Temp_Cookie_Name_To_Hold_Meeting_Room_ID_CODE'); 

// Datetime
	// Define the default date and datetime format we want to use
	// Also the default timezone we use for our datetime functions
//define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS', 'H:i:s j F Y'); <- What we want
//define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY', 'H:i j F Y'); <- What we want
//define('DATE_DEFAULT_FORMAT_TO_DISPLAY', 'j F Y'); <- What we want
define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS', 'F jS Y H:i:s'); //To-DO: REPLACE WITH ABOVE
define('DATETIME_DEFAULT_FORMAT_TO_DISPLAY', 'F jS Y H:i'); //To-DO: REPLACE WITH ABOVE
define('DATE_DEFAULT_FORMAT_TO_DISPLAY', 'F jS Y'); //To-DO: REPLACE WITH ABOVE
define('DATE_DEFAULT_TIMEZONE', 'Europe/Oslo');

// Currency
define('SET_CURRENCY', 'NOK');
define('SET_CURRENCY_SYMBOL', '');
define('SET_CURRENCY_DECIMAL_PRECISION', '2');
define('SPLIT_PRICE_PER_HOUR_INTO_THIS_MANY_PIECES', 4); // 400 NOK /hr = 100 NOK /15 min.

// Timing variables
	// variables used to validate code, handle events
define('MINIMUM_BOOKING_TIME_IN_MINUTES', 15); //1, 5, 10, 15, 30 or 60
define('MINIMUM_TIME_PASSED_AFTER_CREATING_BOOKING_BEFORE_SENDING_EMAIL', 30);
define('TIME_LEFT_UNTIL_MEETING_STARTS_BEFORE_SENDING_EMAIL', 30);
define('MINIMUM_BOOKING_DURATION_IN_MINUTES_USED_IN_PRICE_CALCULATIONS', 15);
define('BOOKING_DURATION_IN_MINUTES_USED_BEFORE_INCLUDING_IN_PRICE_CALCULATIONS', 1);
	// refresh timers
define('SECONDS_BEFORE_REFRESHING_BOOKING_PAGE', 15);
define('SECONDS_BEFORE_REFRESHING_MEETINGROOM_PAGE', 15);
define('SECONDS_BEFORE_REFRESHING_ADMIN_PAGES', 15);

// Length variables
define('MINIMUM_PASSWORD_LENGTH', 6);
define('BOOKING_CODE_LENGTH', 6);
?>