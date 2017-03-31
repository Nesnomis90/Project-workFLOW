<?php
// This file has some functions to make our life easier when coding
// by not having to repeat the same information by ourself.

// Function to reduce the amount of typing we need to do, since the only thing
// that changes is the text output to html.
function html($input){
	return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Function that uses the html() function and outputs the information directly
function htmlout($text){
	echo html($text);
}


// Function to generate a password to be sent to new users


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