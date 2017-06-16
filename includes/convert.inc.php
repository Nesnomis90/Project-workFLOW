<?php
// This holds all the functions we use to convert values (excluding datetime convertions, see datetime.inc.php)

// Takes time xx:yy (x hours and y minutes) and returns xxhyym as text
function convertTimeToHoursAndMinutes($time){
	$timeHour = substr($time,0,strpos($time,":"));
	$timeMinute = substr($time,strpos($time,":")+1, 2);
	return $timeHour . 'h' . $timeMinute . 'm';	
}

// Integer minute input to string output
function convertMinutesToHoursAndMinutes($GivenInMinutes){
	if($GivenInMinutes > 59){
		$GivenInHours = floor($GivenInMinutes/60);
		$GivenInMinutes -= $GivenInHours*60;
		$GivenInHoursAndMinutes = $GivenInHours . 'h' . $GivenInMinutes . 'm';
	} elseif($GivenInMinutes > 0) {
		$GivenInHoursAndMinutes = '0h' . $GivenInMinutes . 'm';
	} else {
		$GivenInHoursAndMinutes = 'None';
	}	
	return $GivenInHoursAndMinutes;
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
function convertTwoDateTimesToTimeDifferenceInMinutes($startDateTime,$endDateTime){
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($endDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);
	$timeDifferenceInMinutes = $timeDifference->i;
	$timeDifferenceInHours = $timeDifference->h;
	$timeDifferenceInDays = $timeDifference->d;
	
	$timeDifference = $timeDifferenceInDays*3600 + $timeDifferenceInHours*60 + $timeDifferenceInMinutes;

	return $timeDifference;
}

// Two datetimes to difference in months
// TO-DO: This might need a change. It does not properly deal with all dates
// e.g. 31 January 2011 to 28 February 2011 gives 0 months, not 1.
function convertTwoDateTimesToTimeDifferenceInMonths($startDateTime,$endDateTime){
	$timeDifferenceStartDate = new DateTime($startDateTime);
	$timeDifferenceCompletionDate = new DateTime($endDateTime);
	$timeDifference = $timeDifferenceStartDate->diff($timeDifferenceCompletionDate);
	$timeDifferenceInYears = $timeDifference->y;
	$timeDifferenceInMonths = $timeDifference->m;
	
	$timeDifference = $timeDifferenceInYears*12 + $timeDifferenceInMonths;

	return $timeDifference;
}
?>