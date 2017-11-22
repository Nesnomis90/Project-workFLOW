<?php
// This is the Index file for the CONNECT folder. Only for testing purposes

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php'; // Starts session if not already started
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// First check if the database even exists, if not, there's no reason to check if someone is an admin
try
{
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	$pdo = connect_to_db();

	$tableExists = tableExists($pdo, 'user');

	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error validating account status.';
	include_once 'error.html.php';
	$pdo = null;
	exit();
}

// Only let admins do this.
if($tableExists AND !isUserAdmin()){
	exit();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
// Make sure our database and tables exist
// ATTEMPT TO CREATE DATABASE AND TABLES
create_db();
create_tables();
addMySQLFunctions();
?>