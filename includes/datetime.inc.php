<?php

//Function to get the current datetime
function getDatetimeNow() {
	// We use the same format as used in MySQL
	// yyyy-mm-dd hh:mm:ss
	date_default_timezone_set('Europe/Oslo');
	$datetimeNow = new Datetime();
	return $datetimeNow->format('Y-m-d H:i:s');
}

// Function to check if the datetime submitted is in the format that's submitted
function validateDatetimeWithFormat($datetime, $format){
	// We take in a datetime string and the format we want to check if it's in
	// We then either return true or false
	date_default_timezone_set('Europe/Oslo');
	$d = date_create_from_format($format, $datetime);
    return $d && $d->format($format) === $datetime;	
}

//Function to change date format to be correct for date input in database
function correctDateFormat($wrongDateString){
	// Correct date format is
	// yyyy-mm-dd

	date_default_timezone_set('Europe/Oslo');		
	if (validateDatetimeWithFormat($wrongDateString, 'Y-m-d')){
		$wrongDate = date_create_from_format('Y-m-d', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
	}
	
	if (validateDatetimeWithFormat($wrongDateString, 'd-m-Y')){
		$wrongDate = date_create_from_format('d-m-Y', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
	}

	return $correctDate;
}

//	Function to change datetime format to be correct for datetime input in database
//	We check for the datetimes we assume the user might submit
function correctDatetimeFormat($wrongDatetimeString){
	// Correct datetime format we want out is
	// yyyy-mm-dd hh:mm:ss => 'Y-m-d H:i:s'
	// If no hit we return FALSE
	// Seems excessive but execution time to go through everything takes around 1 µ second
	// TO-DO: Make sure we don't confuse the input by allowing multiple interpretations of the same text string?
	// TO-DO: When converting non time strings into timestrings it submits the time right now
	// 			Let's make this return 00:00:00 instead?
	// TO-DO: Not heavily tested!!!!
	// TO-DO: Still needs fixing separating date and time parts

	date_default_timezone_set('Europe/Oslo');
	
	// Remove white spaces before and after the datetime submitted
	$wrongDatetimeString = trim($wrongDatetimeString);
	echo $wrongDatetimeString . "<br />";
	
	// Replace some characters if the user for some reason uses it
	// TO-DO: use regex to limit what user can submit later?
	$wrongDatetimeString = str_replace('.', '-',$wrongDatetimeString);
	$wrongDatetimeString = str_replace(',', '-',$wrongDatetimeString);
	$wrongDatetimeString = str_replace('/', '-',$wrongDatetimeString);
	$wrongDatetimeString = str_replace('_', '-',$wrongDatetimeString);
	
	echo $wrongDatetimeString . "<br />";
	
	// The characters we want to allow in the string
	$allowedChars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -:";
	foreach(str_split($wrongDatetimeString) AS $char){
		if(strpos($allowedChars,$char) === FALSE){
			// Found an illegal character
			return FALSE;
		}
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
	
	echo "datepart: $datePart <br />";
	if(isset($timePart)){
		echo "timepart: $timePart <br />";		
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

	echo $wrongDatetimeString . "<br />";
	
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
	
	// If no valid hit, return FALSE
	return FALSE;
}

// Function to convert a datetime to whatever datetime format we submit
function convertDatetimeToFormat($oldDatetimeString, $oldformat, $format){
	// Some useful formats to remember
	// 'Y-m-d H:i:s' = 2017-03-03 12:15:33 (MySQL Datetime)
	// 'Y-m-d' = 2017-03-03 (MySQL Date)
	// 'd M Y H:i:s' = 3 March 2017 12:15:33
	// 
	date_default_timezone_set('Europe/Oslo');
	
	if(validateDatetimeWithFormat($oldDatetimeString, $oldformat)){
		$oldDatetime = date_create_from_format($oldformat, $oldDatetimeString);
		$newDatetime= DATE_FORMAT($oldDatetime , $format);
		
		return $newDatetime;
	} else {
		return FALSE;
	}
}





?>