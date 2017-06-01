<?php 
// This is the index file for the CREDITS folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add more/remove if it ruins multiple tabs open

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}

// Function to clear sessions used to remember user inputs on refreshing the edit credits form
function clearEditCreditsSessions(){
	unset($_SESSION['EditCreditsOriginalInfo']);
	unset($_SESSION['EditCreditsDescription']);
	unset($_SESSION['EditCreditsName']);
	unset($_SESSION['EditCreditsCreditsID']);
}

// Function to check if user inputs for credits are correct
function validateUserInputs(){
	$invalidInput = FALSE;
	
	// Get user inputs
	if(isset($_POST['CreditsName']) AND !$invalidInput){
		$creditsName = trim($_POST['CreditsName']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a name!";
	}
	if(isset($_POST['CreditsDescription']) AND !$invalidInput){
		$creditsDescription = trim($_POST['CreditsDescription']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a description!";
	}
	if(isset($_POST['CreditsAmount']) AND !$invalidInput){
		$creditsAmount = trim($_POST['CreditsAmount']);
	} else {
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a monthly given amount!";
	}
	if(isset($_POST['CreditsHourPrice']) AND !$invalidInput){
		$creditsHourPrice = trim($_POST['CreditsHourPrice']);
	} else {
		$creditsHourPrice = ""; 
		// TO-DO: Change if needed
		// Can be either hour price or minute price, so can be not set
		/*$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a hourly over credits fee!";*/
	}
	if(isset($_POST['CreditsMinutePrice']) AND !$invalidInput){
		$creditsMinutePrice = trim($_POST['CreditsMinutePrice']);
	} else {
		$creditsMinutePrice = ""; 
		// TO-DO: Change if needed
		// Can be either hour price or minute price, so can be not set
		/*$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "A credits cannot be added without a minute by minute over credits fee!";*/
	}
	
	// Remove excess whitespace and prepare strings for validation
	$validatedCreditsName = trimExcessWhitespace($creditsName);
	$validatedCreditsDescription = trimExcessWhitespaceButLeaveLinefeed($creditsDescription);
	$validatedCreditsAmount = trimAllWhitespace($creditsAmount);
	$validatedCreditsHourPrice = trimAllWhitespace($creditsHourPrice);
	$validatedCreditsMinutePrice = trimAllWhitespace($creditsMinutePrice);
	
	// Do actual input validation
	if(validateString($validatedCreditsName) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits name has illegal characters in it.";
	}
	if(validateString($validatedCreditsDescription) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits description has illegal characters in it.";
	}
	if(validateIntegerNumber($validatedCreditsAmount) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted credits amount has illegal characters in it.";
	}
	// TO-DO: Make hour rate a float, just in case?
	if(validateIntegerNumber($validatedCreditsHourPrice) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted hourly over credits fee has illegal characters in it.";
	}
	if(validateFloatNumber($validatedCreditsMinutePrice) === FALSE AND !$invalidInput){
		$invalidInput = TRUE;
		$_SESSION['EditCreditsError'] = "Your submitted minute by minute over credits fee has illegal characters in it.";
	}	
	
	// Are values actually filled in?
	if($validatedCreditsName == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a name for your credits.";	
		$invalidInput = TRUE;		
	}
	if($validatedCreditsDescription == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a description for your credits.";	
		$invalidInput = TRUE;		
	}
	if($validatedCreditsAmount == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a monthly given amount for your credits.";	
		$invalidInput = TRUE;
	}
	if($validatedCreditsHourPrice == "" AND $validatedCreditsMinutePrice == "" AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "You need to fill in a hourly or minute by minute over credits fee for your credits.";	
		$invalidInput = TRUE;		
	}

	// Check if input length is allowed
		// Credits Name
		// Uses same limit as display name (max 255 chars)
	$invalidCreditsName = isLengthInvalidDisplayName($validatedCreditsName);
	if($invalidCreditsName AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits name submitted is too long.";	
		$invalidInput = TRUE;		
	}	
		// Credits Description // Just use same check as with equipment description
	$invalidCreditsDescription = isLengthInvalidEquipmentDescription($validatedCreditsDescription);
	if($invalidCreditsDescription AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits description submitted is too long.";	
		$invalidInput = TRUE;		
	}
		// Credits Amount
		// TO-DO: We check the amount in minutes. Does admin also submit in minutes or just hours?
	$invalidCreditsAmount = isNumberInvalidCreditsAmount($validatedCreditsAmount);
	if($invalidCreditsAmount AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The credits amount submitted is too big.";	
		$invalidInput = TRUE;
	}
		// Credits Hourly Price
	$invalidCreditsHourPrice = isNumberInvalidCreditsHourPrice($validatedCreditsHourPrice);
	if($invalidCreditsAmount AND !$invalidInput){
		$_SESSION['EditCreditsError'] = "The hourly over credits fee submitted is too big.";	
		$invalidInput = TRUE;
	}	
	
	// Check if the credits already exists (based on name).
		// only if we have changed the name (edit only)
	if(isset($_SESSION['EditCreditsOriginalInfo'])){
		$originalcreditsName = strtolower($_SESSION['EditCreditsOriginalInfo']['CreditsName']);
		$newcreditsName = strtolower($validatedCreditsName);		

		if($originalcreditsName == $newcreditsName){
			// Do nothing, since we haven't changed the name we're editing
		} elseif(!$invalidInput) {
			// Check if new name is taken
			try
			{
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
				$pdo = connect_to_db();
				$sql = 'SELECT 	COUNT(*) 
						FROM 	`credits`
						WHERE 	`name`= :creditsName';
				$s = $pdo->prepare($sql);
				$s->bindValue(':creditsName', $validatedCreditsName);		
				$s->execute();
				
				$pdo = null;
				
				$row = $s->fetch();
				
				if ($row[0] > 0)
				{
					// This name is already being used for a credits
					$_SESSION['EditCreditsError'] = "There is already a credits with the name: " . $validatedCreditsName . "!";
					$invalidInput = TRUE;	
				}
				// Credits name hasn't been used before	
			}
			catch (PDOException $e)
			{
				$error = 'Error searching through credits.' . $e->getMessage();
				include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
				$pdo = null;
				exit();
			}
		}	
	}
return array($invalidInput, $validatedCreditsDescription, $validatedCreditsName);
}

?>