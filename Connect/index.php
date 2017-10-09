<?php
// This is the Index file for the CONNECT folder. Only for testing purposes

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Only let admins do this.
if (!isUserAdmin()){
	exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
// Make sure our database and tables exist
// ATTEMPT TO CREATE DATABASE AND TABLES
create_db();
create_tables();
addMySQLFunctions();
?>