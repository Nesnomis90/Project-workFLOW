<?php 
// Data with GET
// This method creates a new URL with the queries attached
// Less secure, since users can see queries in url
// More useful in the sense that users can bookmark the page
// Has a limit to how much data can be passed due to url length limitations
// PHP creates the Array $_GET automatically when it receives a request from a browser
// Used with html form method="get"
///$firstname = $_GET['firstname'];
///$lastname = $_GET['lastname'];

// Data with POST
// Does not display queries to users. Is "invisible"
// Does not allow bookmarks as there is no new url created.
// Allows large values or sensetive values.
// PHP creates the Array $_POST automatically when it receives a request from a browser
// Used with html form method=post"
///$firstname = $_POST['firstname'];
///$lastname = $_POST['lastname'];

// Data with REQUEST
// Holds all the variables from $_GET and $_POST
// Allows data input from either url alteration or user input?
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];

// To avoid malicious user interactions we have to avoid HTML character inputs
// htmlspecialchars convers HTML characters into character entities. Browsers don't interpret this as HTML
echo 'Welcome to our web site, ' .
	htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8') . ' ' .
	htmlspecialchars($lastname, ENT_QUOTES, 'UTF-8') . '!';
	
	
?>