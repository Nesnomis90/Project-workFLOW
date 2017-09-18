<?php

if(isSet($_GET['logoutForNav'])){
	// Same stuff we do on logout in access
	unset($_SESSION['loggedIn']);
	unset($_SESSION['email']);
	unset($_SESSION['password']);
	unset($_SESSION['LoggedInUserID']);
	unset($_SESSION['LoggedInUserName']);
	unset($_SESSION['loginEmailSubmitted']);
	header("Location: /");
	exit();
}
?>