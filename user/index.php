<?php 
// This is the index file for the user folder (all users)
session_start();

// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

// TO-DO: Add a "Set new password" after user activates their account with the link?
// or just have them do it after they log in themselves...

// Function to activate an account from activation link
if(isset($_GET['activateaccount'])){
	
	$activationCode = $_GET['activateaccount'];
		
	// Check if code is correct (64 chars)
	if(strlen($activationCode)!=64){
		$_SESSION['normalUserFeedback'] = "The activation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}
		
	//	Check if the submitted code is in the database
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "SELECT 	`userID`,
						`email`,
						`firstname`,
						`lastname`,
						`password`
				FROM	`user`
				WHERE 	`activationCode` = :activationCode
				AND		`isActive` = 0
				LIMIT 	1";
		$s = $pdo->prepare($sql);
		$s->bindValue(':activationCode', $activationCode);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error validating activation code: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}
	
	// Check if the select even found something
	$result = $s->fetch(PDO::FETCH_ASSOC);
	if(isset($result)){
		$rowNum = sizeOf($result);
	} else {
		$rowNum = 0;
	}
	if($rowNum == 0){
		// No match.
		$_SESSION['normalUserFeedback'] = "The activation code that was submitted is not a valid code.";
		header("Location: .");
		exit();
	}
	
	$userID = $result['userID'];
	$email = $result['email'];
	$firstname = $result['firstname'];
	$lastname = $result['lastname'];
	$hashedPassword = $result['password'];
	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "UPDATE 	`user`
				SET		`isActive` = 1,
						`activationCode` = NULL
				WHERE 	`userID` = :userID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':userID', $userID);
		$s->execute();
		
		//Close the connection
		$pdo = null;
	}
	catch(PDOException $e)
	{
		$error = 'Error activating user: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
	
	$_SESSION['normalUserFeedback'] = 	"The account for " . $lastname . ", " . $firstname . " - " . $email . 
										" has been activated!";
									
	// Add a log event that the account got activated
	try
	{
		// Save a description with information about the user that was activated
		
		$logEventDescription = 	"The account for " . $lastname . ", " . $firstname . " - " . $email . 
								" has been activated by using the activation link!";
		
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
		$pdo = connect_to_db();
		$sql = "INSERT INTO `logevent`
				SET			`actionID` = 	(
												SELECT `actionID` 
												FROM `logaction`
												WHERE `name` = 'Account Activated'
											),
							`description` = :description,
							`userID` = :userID";
		$s = $pdo->prepare($sql);
		$s->bindValue(':description', $logEventDescription);
		$s->bindValue(':userID', $userID);
		$s->execute();
		
		//Close the connection
		$pdo = null;		
	}
	catch(PDOException $e)
	{
		$error = 'Error adding log event to database: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		$pdo = null;
		exit();
	}	
}

// Load the html template
include_once 'user.html.php';
?>