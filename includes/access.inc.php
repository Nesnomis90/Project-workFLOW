<?php
// These are functions to handle user access

// Constants used to salt passwords
require_once 'salts.inc.php';

// Function to salt and hash passwords
function hashPassword($rawPassword){
	$SaltedPassword = $rawPassword . PW_SALT;
	$HashedPassword = hash('sha256', $SaltedPassword);
	return $HashedPassword;
}

// returns TRUE if user is logged in
function userIsLoggedIn()
{
	// If user is trying to log in
	if (isset($_POST['action']) and $_POST['action'] == 'login')
	{
		// Check if user has filled in the necessary information
		if (!isset($_POST['email']) or $_POST['email'] == '' or
		!isset($_POST['password']) or $_POST['password'] == '')
		{
			$GLOBALS['loginError'] = 'Please fill in both fields';
			return FALSE;
		}
		
		// User has filled in both fields, check if login details are correct
			// Add our custom password salt and compare the finished hash to the database
		$SubmittedPassword = $_POST['password'];
		$password = hashPassword($SubmittedPassword);
		if (databaseContainsUser($_POST['email'], $password))
		{
			// Correct log in info! Start a new session for the user
			session_start();
			$_SESSION['loggedIn'] = TRUE;
			$_SESSION['email'] = $_POST['email'];
			$_SESSION['password'] = $password;
			return TRUE;
		}
		else
		{
			// Wrong log in info.
			session_start();
			unset($_SESSION['loggedIn']);
			unset($_SESSION['email']);
			unset($_SESSION['password']);
			$GLOBALS['loginError'] =
			'The specified email address or password was incorrect.';
			return FALSE;
		}
	}
	// If user wants to log out
	if (isset($_POST['action']) and $_POST['action'] == 'logout')
	{
		session_start();
		unset($_SESSION['loggedIn']);
		unset($_SESSION['email']);
		unset($_SESSION['password']);
		header('Location: ' . $_POST['goto']);
		exit();
	}
	
	// The user is in a session that was previously logged in
	// Let's check if the user STILL EXISTS in the database
	// i.e. if the login info is still correct
	session_start(); //Starts a new session or continues a session already in progress
	if (isset($_SESSION['loggedIn']))
	{
		return databaseContainsUser($_SESSION['email'],
		$_SESSION['password']);
	}
}

// Function to check if the submitted user exists in our database
// AND has been activated
function databaseContainsUser($email, $password)
{
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = 'SELECT 	COUNT(*) 
				FROM 	`user`
				WHERE 	email = :email 
				AND 	password = :password
				AND		`isActive` > 0';
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $email);
		$s->bindValue(':password', $password);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for user.';
		include 'error.html.php';
		$pdo = null;
		exit();
	}
	
	$row = $s->fetch();
	// If we got a hit, then the user info was correct
	if ($row[0] > 0)
	{
		return TRUE;
	}
	else
	{
		return FALSE;
	}
}

// Check if user has the specific access we're looking for
function userHasAccess($access)
{
	try
	{
		include_once 'db.inc.php';
		$pdo = connect_to_db();
		$sql = "SELECT 		COUNT(*) 
				FROM 		`user` u
				INNER JOIN 	accesslevel a
				ON 			u.AccessID = a.AccessID
				WHERE 		u.email = :email 
				AND 		a.AccessName = :AccessName";
		$s = $pdo->prepare($sql);
		$s->bindValue(':email', $_SESSION['email']);
		$s->bindValue(':AccessName', $access);
		$s->execute();
		
		$pdo = null;
	}
	catch (PDOException $e)
	{
		$error = 'Error searching for user access.';
		include 'error.html.php';
		$pdo = connect_to_db();
		exit();
	}
	
	$row = $s->fetch();
	if ($row[0] > 0)
	{
		// User has the access we were looking for!
		return TRUE;
	}
	else
	{
		// User does NOT have the access needed.
		return FALSE;
	}
}

// Function to make sure user is Admin
function isUserAdmin(){
		// Check if user is logged in
	if (!userIsLoggedIn())
	{
		// Not logged in. Send user a login prompt.
		include '../login.html.php';
		exit();
	}
		// Check if has Admin access
	if (!userHasAccess('Admin'))
	{
		// User is NOT ADMIN.
		$error = 'Only Admin may access this page.';
		include '../accessdenied.html.php';
		return false;
	}
	return true;
}

// Function to make sure user is In-House User
function isUserInHouseUser(){
		// Check if user is logged in
	if (!userIsLoggedIn())
	{
		// Not logged in. Send user a login prompt.
		include '../login.html.php';
		exit();
	}

	if (!userHasAccess('In-House User'))
	{
		// User is NOT IN-HOUSE USER.
		$error = 'Only In-House Users can access this page.';
		include '../accessdenied.html.php';
		return false;
	}
	return true;
}
?>