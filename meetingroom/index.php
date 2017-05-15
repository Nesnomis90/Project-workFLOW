<?php 
// This is the index file for the meeting room folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

/*
	TO-DO:
	
*/

// Load the html template
include_once 'meetingroom.html.php';
?>