<?php 
// This is the index file for the COMPANYCREDITS folder
session_start();
// Include functions
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/helpers.inc.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

unsetSessionsFromAdminUsers(); // TO-DO: Add more/remove if it ruins multiple tabs open

// CHECK IF USER TRYING TO ACCESS THIS IS IN FACT THE ADMIN!
if (!isUserAdmin()){
	exit();
}


// Get only information from the specific company
if(isset($_GET['Company'])){	
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 		e.`EquipmentID`									AS TheEquipmentID,
							e.`name`										AS EquipmentName,
							e.`description`									AS EquipmentDescription,
							re.`amount`										AS EquipmentAmount,
							m.`meetingRoomID`								AS MeetingRoomID,
							m.`name`										AS MeetingRoomName,
							DATE_FORMAT(re.`datetimeAdded`,'%d %b %Y %T') 	AS DateTimeAdded,
							UNIX_TIMESTAMP(re.`datetimeAdded`)				AS OrderByDate
				FROM 		`equipment` e
				JOIN 		`roomequipment` re
				ON 			e.`EquipmentID` = re.`EquipmentID`
				JOIN 		`meetingroom` m
				ON 			m.`meetingRoomID` = re.`meetingRoomID`
				WHERE 		m.`meetingRoomID` = :MeetingRoomID
				ORDER BY	OrderByDate
				DESC";
				
		$s = $pdo->prepare($sql);
		$s->bindValue(':CompanyID', $_GET['Company']);
		$s->execute();
		
		$result = $s->fetchAll();
		$rowNum = sizeOf($result);
		
		//close connection
		$pdo = null;
			
	}
	catch (PDOException $e)
	{
		$error = 'Error getting room equipment information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}

// Get all companies and their credits
if(!isset($_GET['Company'])){
	try
	{
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		$pdo = connect_to_db();
		
		$sql = "SELECT 		c.`CompanyID`									AS TheCompanyID,
							c.`name`										AS CompanyName,
							e.`description`									AS EquipmentDescription,
							cc.`altMinuteAmount`							AS CreditsAlternativeAmount,
							cr.`CreditsID`									AS CreditsID,
							cr.`name`										AS CreditsName,
							DATE_FORMAT(cc.`datetimeAdded`,'%d %b %Y %T') 	AS DateTimeAdded,
							UNIX_TIMESTAMP(cc.`datetimeAdded`)				AS OrderByDate
				FROM 		`company` c
				JOIN 		`companycredits` cc
				ON 			c.`CompanyID` = cc.`CreditsID`
				JOIN 		`credits` cr
				ON 			cr.`CreditsID` = cc.`CreditsID`
				WHERE 		c.`isActive` > 0
				ORDER BY	OrderByDate
				DESC";
				
		$result = $pdo->query($sql);
		$rowNum = $result->rowCount();
		
		//close connection
		$pdo = null;
			
	}
	catch (PDOException $e)
	{
		$error = 'Error getting company credit information: ' . $e->getMessage();
		include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
		exit();
	}
}	

// Create an array with the actual key/value pairs we want to use in our HTML	
foreach($result AS $row){
	
	// Create an array with the actual key/value pairs we want to use in our HTML
	$companycredit[] = array(
							'TheEquipmentID' => $row['TheEquipmentID'],
							'EquipmentName' => $row['EquipmentName'],
							'EquipmentDescription' => $row['EquipmentDescription'],
							'EquipmentAmount' => $row['EquipmentAmount'],							
							'DateTimeAdded' => $row['DateTimeAdded'],
							'MeetingRoomID' => $row['MeetingRoomID'],
							'MeetingRoomName' => $row['MeetingRoomName']							
						);
}

var_dump($_SESSION); // TO-DO: remove after testing is done

// Create the company credits list in HTML
include_once 'companycredits.html.php';
?>