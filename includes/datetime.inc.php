<?php
require_once 'variables.inc.php';

// Function to check our set minimum booking time slices to get the next valid end time
// We assume all possible booking slices are 1/5/10/15/30/60
function getNextValidBookingStartTime(){
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$datetimeNow = new Datetime();
	$timeNow = $datetimeNow->format('Y-m-d H:i');
	
	return getNextValidBookingEndTime($timeNow);
}

function getNextValidBookingEndTime($startTimeString){

	if(validateDatetimeWithFormat($startTimeString,'Y-m-d H:i:s')){
		$startTime = convertDatetimeToFormat($startTimeString,'Y-m-d H:i:s','Y-m-d H:i');
		$startTime = stringToDateTime($startTime, 'Y-m-d H:i');
	} else {
		$startTime = stringToDateTime($startTimeString, 'Y-m-d H:i');
	}
	
	$startTimeDatePart = $startTime->format('Y-m-d');
	$startTimeHourPart = $startTime->format('H');
	$startTimeMinutePart = $startTime->format('i');
	
	$minimumBookingTime = MINIMUM_BOOKING_TIME_IN_MINUTES;
	
	if($startTimeMinutePart+$minimumBookingTime >= 60){
		if($startTimeHourPart == 23){
			// Set new day
			$startTime->modify('+1 day');
			$startTimeDatePart = $startTime->format('Y-m-d');
		}
		
		// Set new hour
		$hourIncrease = floor(($startTimeMinutePart+$minimumBookingTime)/60);
		$startTime->modify('+' . $hourIncrease . ' hour');
		$startTimeHourPart = $startTime->format('H');

		$startTimeMinutePart -= 60;
	}


	for($i = 0; $i < 61; ){
		if($startTimeMinutePart < $i){
			// Next valid slice found.
			if($i == 0){
				$min = '00';
			} elseif($i < 10){
				$min = '0' . $i;
			} else {
				$min = $i;
			}
			break; 
		}
		$i += $minimumBookingTime;	
	}		

	$endTimeString = $startTimeDatePart . ' ' . $startTimeHourPart . ':' . $min .':00';	
	return $endTimeString;
}

// Function to get the current datetime in MySQL format
function getDatetimeNow() {
	// We use the same format as used in MySQL
	// yyyy-mm-dd hh:mm:ss
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$datetimeNow = new Datetime();
	return $datetimeNow->format('Y-m-d H:i:s');
}

// Function to get the current datetime in display format (with seconds)
function getDatetimeNowInDisplayFormat(){
	$timeNow = getDatetimeNow();
	$displayTimeNow = convertDatetimeToFormat($timeNow,'Y-m-d H:i:s',DATETIME_DEFAULT_FORMAT_TO_DISPLAY_WITH_SECONDS);
	return $displayTimeNow;
}

// Function to get the current date
function getDateNow() {
	// We use the same format as used in MySQL
	// yyyy-mm-dd
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$datetimeNow = new Datetime();
	return $datetimeNow->format('Y-m-d');	
}

// Function to convert string to datetime in MySQL format
// TO-DO: Just broken. But still, not broken...?
function stringToDateTime($datetimeString, $format){
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$d = date_create_from_format($format, $datetimeString);
	return $d;
}

// Function to check if the datetime submitted is in the format that's submitted
function validateDatetimeWithFormat($datetime, $format){
	// We take in a datetime string and the format we want to check if it's in
	// We then either return true or false
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	$d = date_create_from_format($format, $datetime);
    return $d && $d->format($format) === $datetime;	
}

//Function to change date format to be correct for date input in database
function correctDateFormat($wrongDateString){
	// Correct date format is
	// yyyy-mm-dd

	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);		
	if (validateDatetimeWithFormat($wrongDateString, 'Y-m-d')){
		$wrongDate = date_create_from_format('Y-m-d', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
		return $correctDate;
	}
	
	if (validateDatetimeWithFormat($wrongDateString, 'd-m-Y')){
		$wrongDate = date_create_from_format('d-m-Y', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
		return $correctDate;
	}

	return FALSE;
}

//	Function to change datetime format to be correct for datetime input in database
//	We check for the datetimes we assume the user might submit
function correctDatetimeFormat($wrongDatetimeString){
	// Take in a bunch of different datetime formats and output the correct format
	// Accepts dates: Y-n-j, j-n-Y, j-M-Y, j-F-Y, jS-F-Y, F-j-Y, F-jS-Y
	// Accepts times: H-i-s, H-i, H or none
	// Correct datetime format we want out is
	// yyyy-mm-dd hh:mm:ss => 'Y-m-d H:i:s'
	// If not a correct input format we return FALSE

	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	
	// Remove white spaces before and after the datetime submitted
	$wrongDatetimeString = trim($wrongDatetimeString);
	
	// Replace some characters if the user for some reason uses it
	// Shouldn't really make a difference if we actually used validateDateTimeString before calling this,
	// since these characters wouldn't be allowed
	$wrongDatetimeString = preg_replace('/[\.\/\,_;]+/','-', $wrongDatetimeString);
	
	// Check that we only have legal characters before checking if the format is correct
	// This should be done before calling this function
	if(validateDateTimeString($wrongDatetimeString) === FALSE){
		return FALSE;
	}

	// Reduce number of validateDatetimeWithFormat by replacing spaces and leading 0s
	$spacesInDatetimeString = substr_count($wrongDatetimeString, ' ');
	$dashesInDatetimeString = substr_count($wrongDatetimeString, '-');
	
	$totalDividersInDatetimeString = $spacesInDatetimeString + $dashesInDatetimeString;
	
	if ($spacesInDatetimeString > 0 AND $totalDividersInDatetimeString < 3){
		$datePart = $wrongDatetimeString;
	} elseif($spacesInDatetimeString > 0 AND $totalDividersInDatetimeString > 2) {
		$datePart = substr($wrongDatetimeString, 0, strrpos($wrongDatetimeString, " "));
		$timePart = substr(strrchr($wrongDatetimeString, " "), 0);
	} 
	
	// change spaces in date part
	$datePart= str_replace(' ', '-',$datePart);

	// Remove leading zeros
	$datePartWithLeadingZeros = explode('-', $datePart);
	
	foreach($datePartWithLeadingZeros AS $number){
		$datePartWithoutLeadingZerosArray[] = ltrim($number, '0');
	}
	
	$datePartWithoutLeadingZeros = implode('-',$datePartWithoutLeadingZerosArray);
	
	$datePartWithNoSpacesOrLeadingZeros = $datePartWithoutLeadingZeros;
	
	if(!isset($timePart)){
		$timePart = "";
	}
	$wrongDatetimeString = $datePartWithNoSpacesOrLeadingZeros . $timePart;
	
	if(validateDatetimeWithFormat($wrongDatetimeString, 'Y-n-j H:i:s')){
		$wrongDatetime = date_create_from_format('Y-n-j H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if(validateDatetimeWithFormat($wrongDatetimeString, 'Y-n-j H:i')){
		$wrongDatetime = date_create_from_format('Y-n-j H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if(validateDatetimeWithFormat($wrongDatetimeString, 'Y-n-j H')){
		$wrongDatetime = date_create_from_format('Y-n-j H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if(validateDatetimeWithFormat($wrongDatetimeString, 'Y-n-j')){		
		$wrongDatetime = date_create_from_format('Y-n-j', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-n-Y H:i:s')){
		$wrongDatetime = date_create_from_format('j-n-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-n-Y H:i')){
		$wrongDatetime = date_create_from_format('j-n-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-n-Y H')){
		$wrongDatetime = date_create_from_format('j-n-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}		
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-n-Y')){	
		$wrongDatetime = date_create_from_format('j-n-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}	
	
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-M-Y H:i:s')){
		$wrongDatetime = date_create_from_format('j-M-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-M-Y H:i')){
		$wrongDatetime = date_create_from_format('j-M-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-M-Y H')){
		$wrongDatetime = date_create_from_format('j-M-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}		

	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-M-Y')){	
		$wrongDatetime = date_create_from_format('j-M-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}		
		
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-F-Y H:i:s')){
		$wrongDatetime = date_create_from_format('j-F-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-F-Y H:i')){
		$wrongDatetime = date_create_from_format('j-F-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-F-Y H')){
		$wrongDatetime = date_create_from_format('j-F-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'j-F-Y')){
		$wrongDatetime = date_create_from_format('j-F-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}

	if (validateDatetimeWithFormat($wrongDatetimeString, 'jS-F-Y H:i:s')){
		$wrongDatetime = date_create_from_format('jS-F-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'jS-F-Y H:i')){
		$wrongDatetime = date_create_from_format('jS-F-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'jS-F-Y H')){
		$wrongDatetime = date_create_from_format('jS-F-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'jS-F-Y')){
		$wrongDatetime = date_create_from_format('jS-F-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}	
	
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-j-Y H:i:s')){
		$wrongDatetime = date_create_from_format('F-j-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-j-Y H:i')){
		$wrongDatetime = date_create_from_format('F-j-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-j-Y H')){
		$wrongDatetime = date_create_from_format('F-j-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-j-Y')){
		$wrongDatetime = date_create_from_format('F-j-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-jS-Y H:i:s')){
		$wrongDatetime = date_create_from_format('F-jS-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-jS-Y H:i')){
		$wrongDatetime = date_create_from_format('F-jS-Y H:i', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-jS-Y H')){
		$wrongDatetime = date_create_from_format('F-jS-Y H', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}
	if (validateDatetimeWithFormat($wrongDatetimeString, 'F-jS-Y')){
		$wrongDatetime = date_create_from_format('F-jS-Y', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
		return $correctDatetime;
	}		
	
	// If no valid hit, return FALSE
	return FALSE;
}

// Function to convert a datetime to whatever datetime format we submit
function convertDatetimeToFormat($oldDatetimeString, $oldformat, $format){
	// Some useful formats to remember
	// 'Y-m-d H:i:s' = 2017-03-03 12:15:33 (MySQL Datetime)
	// 'Y-m-d' = 2017-03-03 (MySQL Date)
	// 'd M Y H:i:s' = 3 March 2017 12:15:33
	// 'F jS Y H:i' = March 3rd 2017 12:15
	// 'H:i j F Y' = 12:15 3 March 2017
	date_default_timezone_set(DATE_DEFAULT_TIMEZONE);
	
	if(validateDatetimeWithFormat($oldDatetimeString, $oldformat)){
		$oldDatetime = date_create_from_format($oldformat, $oldDatetimeString);
		$newDatetime= DATE_FORMAT($oldDatetime , $format);
		
		return $newDatetime;
	} else {
		return FALSE;
	}
}
?>