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
	date_default_timezone_set('Europe/Oslo');
	$datetimeNow = new Datetime();
	return $datetimeNow->format('Y-m-d H:i:s');
}
// Function to check if a variable is a date with the Y-m-d format
function validateDate($date){
	$d = date_create_from_format('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}
// Function to check if a variable is a datetime with the Y-m-d H:i:s format
function validateDatetime($datetime){
	$d = date_create_from_format('Y-m-d H:i:s', $datetime);
    return $d && $d->format('Y-m-d H:i:s') === $datetime;
}

//Function to change date format to be correct for date input in database
function correctDateFormat($wrongDateString){
	// Correct date format is
	// yyyy-mm-dd
	echo 'old Date: ' . $wrongDateString . '<br />';
			
	if (validateDate($wrongDateString)){
		$wrongDate = date_create_from_format('Y-m-d', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
	} else {
		$wrongDate = date_create_from_format('d-m-Y', $wrongDateString);
		$correctDate = DATE_FORMAT($wrongDate,'Y-m-d');
	}
	echo 'new Date: ' . $correctDate . '<br />';
	return $correctDate;

}

//Function to change datetime format to be correct for datetime input in database
function correctDatetimeFormat($wrongDatetimeString){
	// Correct datetime format is
	// yyyy-mm-dd hh:mm:ss	
	echo 'old Datetime: ' . $wrongDatetimeString . '<br />';
	
	if(validateDatetime($wrongDatetimeString)){
		$wrongDatetime = date_create_from_format('Y-m-d H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
	} else {
		$wrongDatetime = date_create_from_format('d-m-Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
	}
	
	echo 'new Datetime: ' . $correctDatetime . '<br />';
	return $correctDatetime;
}

//Function to change datetime format to be correct for comparing with displayed booking time
function correctDatetimeFormatForBooking($wrongDatetimeString){
	// Correct datetime format is
	// yyyy-mm-dd hh:mm:ss
	//echo 'old Datetime: ' . $wrongDatetimeString . '<br />';
	if(validateDatetime($wrongDatetimeString)){	
		$wrongDatetime = date_create_from_format('Y-m-d H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
	} else {
		$wrongDatetime = date_create_from_format('d M Y H:i:s', $wrongDatetimeString);
		$correctDatetime = DATE_FORMAT($wrongDatetime,'Y-m-d H:i:s');
	}
	//echo 'new Datetime: ' . $correctDatetime . '<br />';
	return $correctDatetime;
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
		//TO-DO: Remove echo statement when testing is over
		echo 'The 64 bit char generated is : <b>' . $code . '</b>.<br />';
		
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
				WHERE 	`activationcode` = ' . $code;
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
		//TO-DO: Remove echo statement when testing is over
		echo 'The 64 bit char generated is : <b>' . $code . '</b>.<br />';
		
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
				WHERE 	`cancellationCode` = ' . $code;
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

?>