<?php
// This holds all the functions we use to convert values (excluding datetime convertions, see datetime.inc.php)

// Takes hours and minutes (xxhyym) and returns minutes
function convertHoursAndMinutesToMinutes($hoursAndMinutes){
	if(strtolower($hoursAndMinutes) === "none"){
		return 0;
	}
	elseif(substr($hoursAndMinutes,0,1) === "-"){
		$timeHour = substr($hoursAndMinutes,1,strpos($hoursAndMinutes,"h"));
		$timeMinute = substr($hoursAndMinutes,strpos($hoursAndMinutes,"h")+1, strpos($hoursAndMinutes,"m"));
		return -($timeHour * 60 + $timeMinute);
	} else {
		$timeHour = substr($hoursAndMinutes,0,strpos($hoursAndMinutes,"h"));
		$timeMinute = substr($hoursAndMinutes,strpos($hoursAndMinutes,"h")+1, strpos($hoursAndMinutes,"m"));
		return $timeHour * 60 + $timeMinute;
	}
}

// Takes time xx:yy (x hours and y minutes) and returns xxhyym as text
function convertTimeToHoursAndMinutes($time){
	$timeHour = substr($time,0,strpos($time,":"));
	$timeMinute = substr($time,strpos($time,":")+1, 2);
	if(empty($timeHour)){
		$timeHour = 0;
	}
	if(empty($timeMinute)){
		$timeMinute = 0;
	}
	return $timeHour . 'h' . $timeMinute . 'm';
}

// Takes time xx:yy (x hours and y minutes) and returns minutes
function convertTimeToMinutes($time){
	if(!empty($time)){
		$timeHour = substr($time,0,strpos($time,":"));
		$timeMinute = substr($time,strpos($time,":")+1, 2);
		return $timeHour*60 + $timeMinute;
	} else {
		return 0;
	}
}

// Integer minute input to string output
function convertMinutesToHoursAndMinutes($givenInMinutes){
	if($givenInMinutes > 59){
		$hours = floor($givenInMinutes/60);
		$minutes = ($givenInMinutes % 60);
		$givenInHoursAndMinutes = $hours . 'h' . $minutes . 'm';
	} elseif($givenInMinutes > 0) {
		//$givenInHoursAndMinutes = '0h' . $givenInMinutes . 'm';
		$givenInHoursAndMinutes = $minutes . 'm';
	} else {
		$givenInHoursAndMinutes = 'None';
	}
	return $givenInHoursAndMinutes;
}

function convertMinutesToTime($givenInMinutes){
	if($givenInMinutes > 0){
		$hours = floor($givenInMinutes/60);
		$minutes = ($givenInMinutes % 60);
		if($hours < 10){
			$hours = "0" . $hours;
		} elseif($hours == 24){
			$hours = "00";
		}
		if($minutes < 10){
			$minutes = "0" . $minutes;
		}

		$givenInTime = $hours . ":" . $minutes;
	} else {
		$givenInTime = "00:00";
	}

	return $givenInTime;
}

// Number value to currency (string) output
function convertToCurrency($input){
	if(SET_CURRENCY_DECIMAL_PRECISION > 0){
		if(SET_CURRENCY_SYMBOL != ""){
			$output = number_format($input,SET_CURRENCY_DECIMAL_PRECISION) . SET_CURRENCY_SYMBOL;
		} elseif(SET_CURRENCY != ""){
			$output = number_format($input,SET_CURRENCY_DECIMAL_PRECISION) . " " . SET_CURRENCY;
		} else {
			$output = number_format($input,SET_CURRENCY_DECIMAL_PRECISION);
		}
	} else {
		if(SET_CURRENCY_SYMBOL != ""){
			$output = $input . SET_CURRENCY_SYMBOL;
		} elseif(SET_CURRENCY != ""){
			$output = $input . " " . SET_CURRENCY;
		} else {
			$output = $input;
		}
	}
	return $output;
}

// Two datetimes to time difference in minutes
function convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime, $endDateTime){
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($endDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);
	$timeDifferenceInSeconds = $timeDifference->s;
	$timeDifferenceInMinutes = $timeDifference->i;
	$timeDifferenceInHours = $timeDifference->h;
	$timeDifferenceInDays = $timeDifference->d;

	if($timeDifferenceInSeconds > 0){
		$timeDifferenceInMinutes += 1;
	}

	$timeDifference = $timeDifferenceInDays*1440 + $timeDifferenceInHours*60 + $timeDifferenceInMinutes;

	return $timeDifference;
}

// Two datetimes to difference in days.
function convertTwoDateTimesToTimeDifferenceInDays($startDateTime, $endDateTime){
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($endDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);

	$timeDifferenceInDays = $timeDifference->days;

	return $timeDifferenceInDays;
}

// Two datetimes to difference in months /Note: We no longer use this, since it didn't work for our period use/
// e.g. 31 January 2011 to 28 February 2011 gives 0 months, not 1.
function convertTwoDateTimesToTimeDifferenceInMonths($startDateTime, $endDateTime){
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($endDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);
	$timeDifferenceInYears = $timeDifference->y;
	$timeDifferenceInMonths = $timeDifference->m;

	$timeDifference = $timeDifferenceInYears*12 + $timeDifferenceInMonths;

	return $timeDifference;
}
?>