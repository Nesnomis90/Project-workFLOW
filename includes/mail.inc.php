<?php
// The mail() function allows you to send emails directly from a script
// syntax mail(to,subject,message,headers,(optional)parameters)
//
// message : Each line should be separated with a LF (\n). Lines should not exceed 70 characters.
//
// headers : Specifies additional headers, like From, Cc, and Bcc. 
//			The additional headers should be separated with a CRLF (\r\n).
//			When sending an email, it must contain a From header.
//
// Return value: Returns TRUE on success or FALSE on failure. 
// 				Note: Keep in mind that even if the email was accepted for delivery, 
//				it does NOT mean the email is actually sent and received!


// Function that prepares an email to be sent
// Takes in one or multiple emails.
// Returns TRUE if prepared, FALSE if not prepared.
// TO-DO: Untested with proper php.ini settings
function sendEmail($toEmail, $subject, $message){
	
	// Check if the email(s) is(are) a valid email	
	if(is_Array($toEmail)){
		for($i=0; $i<sizeOf($toEmail); $i++){
			if(validateUserEmail($toEmail[$i])){
				$validEmail[] = $toEmail[$i];
			}
		}
	} else {
		if(validateUserEmail($toEmail)){
			$validEmail[] = $toEmail;
		}
	}
	
	// Prepare email to be sent with valid email(s)
	if(isSet($validEmail) AND sizeOf($validEmail) > 0){

		$encoding = "utf-8";

		// Preferences for Subject field
		$subject_preferences = array(
										"input-charset" => $encoding,
										"output-charset" => $encoding,
										"line-length" => 76,
										"line-break-chars" => "\r\n"
									);

			// If subject is left blank, set a default subject
		if($subject == ""){
			$subject = "Message from Meeting Flow booking service.";
		}
			// If msg is not empty, prepare the email
		if($message != ""){

			$ourName = FROM_NAME_USED_IN_EMAIL;
			$ourEmail = EMAIL_USED_FOR_SENDING_INFORMATION;
			$ourContact = CONTACT_INFO_SENT_IN_MAIL;

			// Add a "No reply"-warning to all emails sent out.
			$message .= "\n\nThis Email address is not monitored for responses.";
			$message .= "\nTo contact us check out " . $ourContact;
			// Use wordwrap() if lines are longer than 70 characters
			$message = wordwrap($message,70,"\r\n");

			$toEmail = implode(', ', $validEmail);

			// Set header information
			$header = 	"Content-type: text/html; charset=" . $encoding . "\r\n";
			$header .= 	"From: " . $ourName." <" . $ourEmail . ">\r\n";
			$header .=	"Bcc: " . $toEmail . "\r\n";
			$header .= 	"MIME-Version: 1.0\r\n";
			$header .= 	"Content-Transfer-Encoding: 8bit\r\n";
			$header .= 	"Date: " . date("r (T)") . "\r\n";
			$header .= 	iconv_mime_encode("Subject", $subject, $subject_preferences);

			// Prepare the email to be sent
			//return mail($toEmail, $subject, $message, $from); This version sends email to the users without hiding emails
			return mail($ourEmail, $subject, $message, $header); 	// This version sends email to ourselves and sends blind carbon copies (BCC) to the receivers
																	// Effectively hiding receiver emails from showing up to other receivers.
																	// TO-DO: if we don't want to send email to ourselves, set field to NULL	
		} else {
			// No message submitted, we can't send the email
			return FALSE;
		}
	} else {
		// Invalid email(s)
		return FALSE;
	}
}

// Function to validate a user email
function validateUserEmail($email){
	/*Following RFC 5321, best practice for validating an email address would be to:

	Check for presence of at least one @ symbol in the address
	Ensure the local-part is no longer than 64 octets
	Ensure the domain is no longer than 255 octets
	Ensure the address is deliverable
	To ensure an address is deliverable, the only way to check this is to send the user an email and have the user take action to confirm receipt. Beyond confirming that the email address is valid and deliverable, this also provides a positive acknowledgement that the user has access to the mailbox and is likely to be authorized to use it. This does not mean that other users cannot access this mailbox, for example when the user makes use of a service that generates a throw away email address.

	Email verification links should only satisfy the requirement of verify email address ownership and should not provide the user with an authenticated session (e.g. the user must still authenticate as normal to access the application).
	Email verification codes must expire after the first use or expire after 8 hours if not used.*/
	
	// To avoid email injection the emails can't have \n\r in them.
	if(preg_match('/\r|\n/',$email)){
		return FALSE;
	}
	
	// Check for the presence of at least one @ symbol
	if(strpos($email, '@') !== FALSE) {
		// Email contains an @
		
		// Check that the local-part is no longer than 64 octets (64x8 bit = 64 byte)
			// Get local-part based on last occurance of @-symbol
		$local = substr($email, 0, strrpos($email, "@"));
		if(strlen($local) > 64){
			// local part is bigger than 64 octets
			return FALSE;
		}
		// Check that the domain is no longer than 255 octets (255x8 bit = 255 byte)
		$domain = substr(strrchr($email, "@"), 1);
		if(strlen($domain) > 255){
			// domain is bigger than 255 octets
			return FALSE;
		}
		
		// Email needs at least a local and a domain part, so *@* minimum.
		if(strlen($local) == 0 OR strlen($domain) == 0){
			// missing a local or domain part
			return FALSE;
		}
		
		// Email seems valid. Now we can at least try sending a verification email
		return TRUE;
		
	} else {
		// No @ found, invalid email.
		return FALSE;
	}
}
?>