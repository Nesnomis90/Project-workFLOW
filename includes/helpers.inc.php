<?php
// This file has some functions to make our life easier when coding
// by not having to repeat the same information by ourselves.

// Include some salts
require_once 'salts.inc.php';
require_once 'access.inc.php';


// Function to reduce the amount of typing we need to do, since the only thing
// that changes is the text output to html.
function html($input){
	return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Function that uses the html() function and outputs the information directly
function htmlout($text){
	echo html($text);
}

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
	
	if ($spacesInDatetimeString > 0 AND $totalDividersInDatetimeString < 2){
		$datePart = $wrongDatetimeString;
	} elseif($spacesInDatetimeString > 0 AND $totalDividersInDatetimeString > 2) {
		$datePart = substr($wrongDatetimeString, 0, strrpos($wrongDatetimeString, " "));
		$timePart = substr(strrchr($wrongDatetimeString, " "), 0);
	} 
	
	echo "datepart: $datePart <br />";
	echo "timepart: $timePart <br />";
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

// Function to generate a password to be sent to new users
function generateUserPassword($length){
	// The characters we want to generate a password string from
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	// Use str_shuffle to randomly shuffle the characters around.
	// Then we use substr to grab a portion of that string as our password
	$randomString = substr(str_shuffle($chars),0,$length);
	return $randomString;
}

// Function to generate an activation code for new users
// Result is a 64 char code
function generateActivationCode(){
	try
	{
		// Create a 64char code
		$code = hash('sha256', mt_rand());
		
		// Check if code has already been used
		// If it has, continue making more codes until we find one
		// that hasn't been used yet.
		// If it has not been used, return the code
		if(activationCodeExists($code)){
			$newcode = generateActivationCode();
			return $newcode;
		} else {
			return $code;
		}
	}
	catch (PDOException $e)
	{
		$error = 'Error generating user Activation Code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to check if activation code already exists in database or not
function activationCodeExists($code){
	try 
	{
		// Check database if the code already exists or not
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo =  connect_to_db();
		$sql = 'SELECT 	1 
				FROM 	`user` 
				WHERE 	`activationcode` = ' . $code .
				' LIMIT 1'; //To-do: remove/fix limit 1 if broken
		$result = $pdo->query($sql);
		$row = $result->rowCount();
		
		$pdo = null;
		
		// The result will either be an empty set, if it doesn't exist. Or a single row, if it does exist.
		if($row > 0){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	catch (PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}		
}

// Function to generate a cancellation code for new bookings
// Result is a 64 char code
function generateCancellationCode(){
	try
	{
		// Create a 64char code
		$code = hash('sha256', mt_rand());
		
		// Check if code has already been used
		// If it has, continue making more codes until we find one
		// that hasn't been used yet.
		// If it has not been used, return the code
		if(cancellationCodeExists($code)){
			$newcode = generateCancellationCode();
			return $newcode;
		} else {
			return $code;
		}
		
	}
	catch (PDOException $e)
	{
		$error = 'Error generating booking Cancellation Code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
}

// Function to check if cancellation code already exists in database or not
function cancellationCodeExists($code){
	try 
	{
		// Check database if the code already exists or not
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		$pdo =  connect_to_db();
		$sql = 'SELECT 	1 
				FROM 	`booking` 
				WHERE 	`cancellationCode` = ' . $code . 
				' LIMIT 1'; //To-do: remove/fix limit 1 if broken
		$result = $pdo->query($sql);
		$row = $result->rowCount();
		
		$pdo = null;
		
		// The result will either be an empty set, if it doesn't exist. Or a single row, if it does exist.
		if($row > 0){
			return TRUE;
		} else {
			return FALSE;
		}
	}
	catch (PDOException $e)
	{
		$pdo = null;
		return FALSE;
	}		
}

// Function to validate a user email
// TO-DO: UNTESTED
function validateUserEmail($email){
	/*Following RFC 5321, best practice for validating an email address would be to:

	Check for presence of at least one @ symbol in the address
	Ensure the local-part is no longer than 64 octets
	Ensure the domain is no longer than 255 octets
	Ensure the address is deliverable
	To ensure an address is deliverable, the only way to check this is to send the user an email and have the user take action to confirm receipt. Beyond confirming that the email address is valid and deliverable, this also provides a positive acknowledgement that the user has access to the mailbox and is likely to be authorized to use it. This does not mean that other users cannot access this mailbox, for example when the user makes use of a service that generates a throw away email address.

	Email verification links should only satisfy the requirement of verify email address ownership and should not provide the user with an authenticated session (e.g. the user must still authenticate as normal to access the application).
	Email verification codes must expire after the first use or expire after 8 hours if not used.*/
	
	// Check for the presence of at least one @ symbol
	if(strpos($email, '@') !== FALSE) {
		// Email contains an @
		
		// Check that the local-part is no longer than 64 octets (64x8 bit = 64 byte)
			// Get local-part based on last occurance of @-symbol
		$local = substr($email, 0, strrpos($email, "@"));
		if(strlen($local) > 64){
			// local part is bigger than 64 octets
			return FALSE;
		}
		// Check that the domain is no longer than 255 octets (255x8 bit = 255 byte)
		$domain = substr(strrchr($email, "@"), 1);
		if(strlen($domain) > 255){
			// domain is bigger than 255 octets
			return FALSE;
		}
		
		// Email seems valid. Now we can at least try sending a verification email
		return TRUE;
		
	} else {
		// No @ found, invalid email.
		return FALSE;
	}
}
?>