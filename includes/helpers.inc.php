<?php
// The whole collection of all the extra functions we have made

// Start session if not already started
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include all functions
require_once 'access.inc.php';
require_once 'mail.inc.php';
require_once 'datetime.inc.php';
require_once 'codegeneration.inc.php';
require_once 'htmlout.inc.php';
require_once 'inputvalidation.inc.php';
require_once 'cookies.inc.php';
require_once 'sessions.inc.php';
require_once 'convert.inc.php';
?>