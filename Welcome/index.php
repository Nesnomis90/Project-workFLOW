<?php
// Index file for Welcome

// This method keeps the same url host/Welcome even when changing between the two forms.

// Checks if the user has submitted info into the login fields
//	(Here checked by if firstname field has data in it)
if (!isset($_REQUEST['firstname']))
{
	//	If not active, activate the logging template
	include 'form.html.php';
}
else
{
	$firstname = $_REQUEST['firstname'];
	$lastname = $_REQUEST['lastname'];
	
	if ($firstname == 'Kevin' and $lastname == 'Yank')
	{
		$output = 'Welcome, oh glorious leader!';
	}
	else
	{
		$output = 'Welcome to our web site, ' .
			htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') . ' ' .
			htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8') . '!';
	}
	
	// If the login input fields has info in it, load the welcome template
	include 'welcome.html.php';
}
?>