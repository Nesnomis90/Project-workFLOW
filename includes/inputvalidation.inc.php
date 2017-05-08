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
// TO-DO: UNTESTED
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
?>