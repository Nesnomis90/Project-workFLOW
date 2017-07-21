<?php 
// This is the index file for the company folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// Make sure logout works properly and that we check if their login details are up-to-date
if(isSet($_SESSION['loggedIn'])){
	$gotoPage = ".";
	userIsLoggedIn();
}

unsetSessionsFromAdminUsers(); // TO-DO: Add more or remove

//variables to implement
$numberOfCompanies //int
$companies //array
$selectedCompanyID //int
$selectedCompanyName //string

// values to retrieve
$_POST['companySelect'] //int


include_once 'company.html.php';
?>