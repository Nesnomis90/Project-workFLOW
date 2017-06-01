<?php
// Functions to do anything related with sessions

// Remove all sessions used by admin in the user overview
function unsetSessionsFromAdminUsers(){
	unset($_SESSION['UserEmailsToBeDisplayed']);
	unset($_SESSION['UserEmailListSeparatorSelected']);

	unset($_SESSION['AddNewUserFirstname']);
	unset($_SESSION['AddNewUserLastname']);
	unset($_SESSION['AddNewUserEmail']);
	unset($_SESSION['AddNewUserSelectedAccess']);	
	unset($_SESSION['AddNewUserAccessArray']);
	unset($_SESSION['AddNewUserGeneratedPassword']);
	unset($_SESSION['AddNewUserDefaultAccessID']);
	
	unset($_SESSION['lastUserID']);
	
	unset($_SESSION['EditUserOriginaEmail']);
	unset($_SESSION['EditUserOriginalFirstName']);
	unset($_SESSION['EditUserOriginalLastName']);
	unset($_SESSION['EditUserOriginaAccessID']);
	unset($_SESSION['EditUserOriginaAccessName']);
	unset($_SESSION['EditUserOriginaDisplayName']);
	unset($_SESSION['EditUserOriginaBookingDescription']);
	unset($_SESSION['EditUserOriginaReduceAccessAtDate']);
	unset($_SESSION['EditUserOriginalUserID']);
	
	unset($_SESSION['EditUserChangedEmail']);	
	unset($_SESSION['EditUserChangedFirstname']);
	unset($_SESSION['EditUserChangedLastname']);
	unset($_SESSION['EditUserChangedAccessID']);
	unset($_SESSION['EditUserChangedDisplayname']);
	unset($_SESSION['EditUserChangedBookingDescription']);
	unset($_SESSION['EditUserChangedReduceAccessAtDate']);
	
	unset($_SESSION['EditUserAccessList']);	
}

// Removes all stored info e.g. logs out user
function destroySession(){
	session_start();
	// Unset all of the session variables.
	$_SESSION = array();

	// Delete the session cookie.
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}

	// Destroy the session.
	session_destroy();
	
	// Start the new session.
	session_start();
}
?>