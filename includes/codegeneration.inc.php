<?php

// Include salts
require_once 'salts.inc.php';

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
?>