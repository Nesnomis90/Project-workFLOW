<?php
// This holds all the functions we use to convert values (excluding datetime convertions, see datetime.inc.php)

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
?>