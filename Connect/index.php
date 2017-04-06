<?php
// This is the Index file for the CONNECT folder

// Get database connection functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

// Make sure our database and tables exist
// ATTEMPT TO CREATE DATABASE AND TABLES
create_db();
create_tables();
?>