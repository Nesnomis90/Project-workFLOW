<?php
// This is a collection of cuntions we use to check if user inputs are OK

// Function to check if variables are too big for MySQL or our liking
	//Display Names
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidDisplayName($displayName){
	// Has to be less than 255 chars (MySQL - VARCHAR 255)

	$dspnameLength = strlen(utf8_decode($displayName));
	$dspnameMaxLength = 255; // TO-DO: Adjust max length if needed.
	if($dspnameLength > $dspnameMaxLength AND !$invalidInput){	
		Return TRUE;	
	}
	Return FALSE;
}
	//Booking Descriptions
// Returns TRUE on invalid, FALSE on valid
function isLengthInvalidBookingDescription($bookingDescription){
	// Has to be less than 65,535 bytes (MySQL - TEXT) (too much anyway)

	$bknDscrptnLength = strlen(utf8_decode($bookingDescription));
	$bknDscrptnMaxLength = 500; // TO-DO: Adjust max length if needed.
	if($bknDscrptnLength > $bknDscrptnMaxLength AND !$invalidInput){
		Return TRUE;	
	}
	Return FALSE;
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
	return trim(preg_replace('/\s+/', ' ', $oldString))
}

// Function to check if input string uses legal characters and trims the input down
// For Names
// Allows empty strings
function validateNames($oldString){
	$trimmedString = trimExcessWhitespace($oldString);
	
	// Check if string uses allowed characters
		// We allow all language letters and accents.
		// Also space, and the symbols ', . and -
		// TO-DO: Change if we need other symbols
	if (preg_match("/^[\p{L}\p{M} '-]*$/u", $trimmedString)) {
		return $trimmedString;
	} else {
		return FALSE;
	}		
}

// Function to check if input string uses legal characters and trims the input down
// 
// Allows empty strings
function validateString($oldString){
	$trimmedString = trimExcessWhitespaceButLeaveLinefeed($oldString);
	
	// Check if string uses allowed characters
		// " -~" matches all printable ASCII characters (A-Z, a-z, 0-9, etc.)
		// For unicode we add /u and p{L} for all language letters and p{M} for all accents
		// There are still characters that are not allowed, like currency symbols
		// and symbols like ´ (when not used as an accent)
		// For currency symbols add \p{Sc}
		// For math symbols add \p{Sm}
		// TO-DO: change because it probably isn't good
		
	if (preg_match('/^[ -~\p{L}\p{M}\r\n]*$/u', $trimmedString)) {
		return $trimmedString;
	} else {
		return FALSE;
	}
}

// Function to check if input string uses legal characters for our datetime convertions and trims excess spaces
// Allows empty strings
function validateDateTimeString($oldString){
	$trimmedString = trimExcessWhitespace($oldString);
	
	// Check if string uses allowed characters
		// We allow the characters , . : / - _ and space
	if (preg_match('/^[A-Za-z0-9.:\/_ -]*$/', $trimmedString)) {
		return $trimmedString;
	} else {
		return FALSE;
	}	
}
?>