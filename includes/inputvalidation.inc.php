<?php
require_once 'variables.inc.php';
// This is a collection of functions we use to check if user inputs are OK

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

// Function to check if variables are too big for MySQL or our liking
	//Display Names
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidDisplayName($displayName){
	// Has to be less than 255 chars (MySQL - VARCHAR 255)

	$dspnameLength = strlen(utf8_decode($displayName));
	$dspnameMaxLength = 255; // TO-DO: Adjust max length if needed.
	if($dspnameLength > $dspnameMaxLength AND !$invalidInput){	
		return TRUE;	
	}
	return FALSE;
}
	//Booking Descriptions
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidBookingDescription($bookingDescription){
	// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)

	$bknDscrptnLength = strlen(utf8_decode($bookingDescription));
	$bknDscrptnMaxLength = 500; // TO-DO: Adjust max length if needed.
	if($bknDscrptnLength > $bknDscrptnMaxLength AND !$invalidInput){
		return TRUE;	
	}
	return FALSE;
}

	//Equipment Descriptions
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidEquipmentDescription($equipmentDescription){
	// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)

	$eqpmntDscrptnLength = strlen(utf8_decode($equipmentDescription));
	$eqpmntDscrptnMaxLength = 500; // TO-DO: Adjust max length if needed.
	if($eqpmntDscrptnLength > $eqpmntDscrptnMaxLength AND !$invalidInput){
		return TRUE;	
	}
	return FALSE;
}

	//Meeting Room Descriptions
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidMeetingRoomDescription($meetingRoomDescription){
	// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)

	$mtngrmDscrptnLength = strlen(utf8_decode($meetingRoomDescription));
	$mtngrmDscrptnMaxLength = 500; // TO-DO: Adjust max length if needed.
	if($mtngrmDscrptnLength > $mtngrmDscrptnMaxLength AND !$invalidInput){
		return TRUE;	
	}
	return FALSE;
}

	//Meeting Room Location
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidMeetingRoomLocation($meetingRoomCapacity){
	// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)

	$mtngrmCapacityLength = strlen(utf8_decode($meetingRoomCapacity));
	$mtngrmCapacityMaxLength = 500; // TO-DO: Adjust max length if needed.
	if($mtngrmCapacityLength > $mtngrmCapacityMaxLength AND !$invalidInput){
		return TRUE;	
	}
	return FALSE;
}

	// Meeting Room Capacity
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidMeetingRoomCapacity($capacityNumber){
	// Has to be between 0 and 255
	// In practice the meeting room needs at least room for 1 person.
	
	$maxNumber = 255;	// To-do: change if needed
	$minNumber = 1;
	if($capacityNumber < $minNumber OR $capacityNumber > $maxNumber){
		return TRUE;
	}
	return FALSE;
}

	// Credits Amount
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidCreditsAmount($creditsAmount){
	// Has to be between 0 and 65535 (minutes)
	
	$maxNumber = 65535;	// To-do: change if needed
	$minNumber = 0;
	if($creditsAmount < $minNumber OR $creditsAmount > $maxNumber){
		return TRUE;
	}
	return FALSE;
}

	// Credits Hour Price
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidCreditsHourPrice($creditsHourPrice){
	// Has to be between 0 and 65535
	
	$maxNumber = 65535;	// To-do: change if needed
	$minNumber = 0;
	if($creditsHourPrice < $minNumber OR $creditsHourPrice > $maxNumber){
		return TRUE;
	}
	return FALSE;
}

	// Credits Minute Price
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidCreditsMinutePrice($creditsMinutePrice){
	// Is a float so it has a large range
	// In practice we only need from 0 to some big number
	
	$maxNumber = 65535;	// To-do: change if needed
	$minNumber = 0;
	if($creditsMinutePrice < $minNumber OR $creditsMinutePrice > $maxNumber){
		return TRUE;
	}
	return FALSE;	
}

	// Credits Monthly Subscription Price
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidCreditsMonthlyPrice($creditsMonthlyPrice){
	// Has to be between 0 and 65535
	
	$maxNumber = 65535;	// To-do: change if needed
	$minNumber = 0;
	if($creditsMonthlyPrice < $minNumber OR $creditsMonthlyPrice > $maxNumber){
		return TRUE;
	}
	return FALSE;	
}

	// Booking Code Digits
// Returns TRUE on invalid, FALSE on valid
function isNumberInvalidBookingCode($bookingCode){
	// Has to be between 0 and 6 digits (ideally 6 digits all the time, but we add 0's to make it 6)
	// Also has to return invalid on blocked digits, if implemented
	$bookingCodeLength = BOOKING_CODE_LENGTH;
	// Make sure we have enough digits submitted	
	if(strlen($bookingCode) < $bookingCodeLength){
		$sprintftext = "%0" . $bookingCodeLength . "u";
		$bookingCode = sprintf($sprintftext, $bookingCode); // Add 0s before submitted digits
	}
	// For security reasons we want to disable some easy to guess codes
	/*$blockedDigits = array(	'000000', '111111', '222222', '333333', '444444', 	
								'555555', '666666', '777777', '888888', '999999', 
								'012345', '123456', '234567', '345678', '456789',	// Block ascending digits
								'567890', '678901', '789012', '890123', '901234',
								'987654', '876543', '765432', '654321', '543210',	// Block descendig digits
								'432109', '321098', '210987', '109876', '098765'
							); This has been changed from manual to code generated*/
	$ascNum = "01234567890123456789";
	$descNum = "98765432109876543210";
	for($i=0; $i < 10; $i++){
		$blockedDigits[] = str_repeat($i,$bookingCodeLength); // Block all equal digits
		$blockedDigits[] = substr($ascNum,$i,$bookingCodeLength); // Block ascending digits
		$blockedDigits[] = substr($descNum,$i,$bookingCodeLength); // Block descending digits
	}	
							
	$minNumber = 0;
	$maxNumber = pow(10,BOOKING_CODE_LENGTH)-1; // Sets the highest number with our set digits (10^digits - 1)
	if($bookingCode < $minNumber OR $bookingCode > $maxNumber){
		return TRUE;
	}
	foreach($blockedDigits AS $number){
		if($bookingCode == $number){
			return TRUE;
		}
	}
	return FALSE;	
}

// Function that (hopefully) removes excess white space, line feeds etc.
function trimExcessWhitespaceButLeaveLinefeed($oldString){

	// TO-DO: Seems to be working, but change if needed
	// Inner preg replaces takes all white space before and after a line feed and turns it into a single line feed
	// Outer preg replaces takes all excess spaces and tabs between words on a line and replaces with a single space
	// trim removes excess spaces before/after
	return trim(preg_replace('/[ \t]+/', ' ', preg_replace('/\s*\R+\s*/', "\n", $oldString)));
}

// Function that (hopefully) removes excess white space, line feeds etc.
function trimExcessWhitespace($oldString){

	// Replace any amount of white space with a single space
	// Also remove excess space at start/end
	return trim(preg_replace('/\s+/', ' ', $oldString));
}

// Function that (hopefully) removes all white space
function trimAllWhitespace($oldString){
	return preg_replace('/\s+/', '', $oldString);
}

// Function to check if input string uses legal characters and trims the input down
// For Names
// Allows empty strings
function validateNames($oldString){
	//$trimmedString = trimExcessWhitespace($oldString);
	
	// Check if string uses allowed characters
		// We allow all language letters and accents.
		// Also space, and the symbols ', . and -
		// TO-DO: Change if we need other symbols
	if (preg_match("/^[\p{L}\p{M} '-]*$/u", $oldString)) {
		return TRUE;
	} else {
		return FALSE;
	}		
}

// Function to check if input string uses legal characters and trims the input down
// Allows empty strings
function validateString($oldString){
	//$trimmedString = trimExcessWhitespaceButLeaveLinefeed($oldString);
	
	// Check if string uses allowed characters
		// " -~" matches all printable ASCII characters (A-Z, a-z, 0-9, etc.)
		// For unicode we add /u and p{L} for all language letters and p{M} for all accents
		// There are still characters that are not allowed, like currency symbols
		// and symbols like ´ (when not used as an accent)
		// For currency symbols add \p{Sc}
		// For math symbols add \p{Sm}
		// TO-DO: change because it probably isn't good
		
	if (preg_match('/^[ -~\p{L}\p{M}\r\n]*$/u', $oldString)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

// Function to check if input string uses legal characters for an integer number only
// \d = any digit
function validateIntegerNumber($oldString){
	if(preg_match('/^[+-]?\d+$/', $oldString)){
		return TRUE;
	} else {
		return FALSE;
	}
}

// Function to check if input string uses legal characters for a float number only
// We also allow + or - in front and a single decimal point (.)
function validateFloatNumber($oldString){
	$oldString = str_replace(',','.',$oldString);
	if(preg_match('/^[+-]?\d+\.?\d*$/', $oldString)){
			return TRUE;
		} else {
			return FALSE;
		}	
}

// Function to check if input string uses legal characters for our datetime convertions and trims excess spaces
// Allows empty strings
function validateDateTimeString($oldString){
	//$trimmedString = trimExcessWhitespace($oldString);
	
	// Check if string uses allowed characters
		// We allow the characters , . : / - _ and space
	if (preg_match('/^[A-Za-z0-9.:\/_ -]*$/', $oldString)) {
		return TRUE;
	} else {
		return FALSE;
	}	
}

// Function to check if the submitted end time has a valid minute slice
// e.g. with 15 minute booking slices it will be 00, 15, 30 or 45.
// Returns TRUE on invalid, FALSE on valid
// TO-DO: Untested in code
function isBookingEndTimeInvalid($endTimeString){
	$endTime = stringToDateTime($endTimeString, 'Y-m-d H:i:s');
	$endTimeMinutePart = $endTime->format('i');
	
	$minimumBookingTime = MINIMUM_BOOKING_TIME_IN_MINUTES;
	
	for($i = 0; $i <= $endTimeMinutePart; ){
		if($endTimeMinutePart == $i){
			return FALSE;
		}
		$i += $minimumBookingTime;	
	}		
	
	return TRUE;
}
?>