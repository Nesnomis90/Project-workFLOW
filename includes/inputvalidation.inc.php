<?php
// This is a collection of cuntions we use to check if user inputs are OK

// Function that (hopefully) removes excess white space, line feeds etc.
// TO-DO: Laves a space too much some places
function trimExcessWhitespaceButLeaveLinefeed($oldString){
	$trimmedString = trim($oldString);

	return preg_replace('/[ \t]+/', ' ', preg_replace('#\R+#', "\n", $trimmedString));
}

// Function that (hopefully) removes excess white space, line feeds etc.
function trimExcessWhitespace($oldString){
	$trimmedString = trim($oldString);
	
	// Replace any amount of white space with a single space
	return preg_replace('/^[\s]*$/', ' ', $trimmedString);
}

// Function to check if input string uses legal characters and trims the input down
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
		//	// TO-DO: change because it probably isn't good
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