<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isSet($_GET['logoutForNav'])){
	// Same stuff we do on logout in access
	unset($_SESSION['loggedIn']);
	unset($_SESSION['email']);
	unset($_SESSION['password']);
	unset($_SESSION['LoggedInUserID']);
	unset($_SESSION['LoggedInUserName']);
	header("Location: /");
	exit();
}
?>