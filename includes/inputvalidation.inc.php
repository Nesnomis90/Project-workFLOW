<?php
// This is a collection of cuntions we use to check if user inputs are OK

// Function to check if input string uses legal characters and trims the input down
// Allows empty strings
function validateString($oldString){
	$trimmedString = trim($oldString);
	
	// Check if string uses allowed characters
		// " -~" matches all printable ASCII characters (A-Z, a-z, 0-9, etc.)
		// For unicode we add /u and p{L} for all language letters and p{M} for all accents
		//	// TO-DO: change because it probably isn't good
	if (preg_match('/^[ -~\p{L}\p{M}]*$/u', $trimmedString)) {
		return $trimmedString;
	} else {
		return FALSE;
	}
}

// Function to check if input string uses legal characters for our datetime convertions and trims excess spaces
// Allows empty strings
function validateDateTimeString($oldString){
	$trimmedString = trim($oldString);
	
	// Check if string uses allowed characters
		// We allow the characters , . : / - _ and space
	if (preg_match('/^[A-Za-z0-9.:\/_ -]*$/', $trimmedString)) {
		return $trimmedString;
	} else {
		return FALSE;
	}	
}

?>