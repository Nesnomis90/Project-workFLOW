<?php 
// This is the index file for the users folder

// Display users list
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
try
{
	$pdo = connect_to_db();
	$sql = "SELECT 	u.`userID`, 
					u.`firstname`, 
					u.`lastname`, 
					u.`email`,
					a.`AccessName`,
					u.`displayname`,
					u.`bookingdescription`,
                    GROUP_CONCAT(CONCAT_WS(' for ', cp.`name`, c.`name`) separator ', ') AS WorksFor,
					DATE_FORMAT(u.`create_time`, '%d %b %Y %T') AS DateCreated,
					u.`isActive`,
					DATE_FORMAT(u.`lastActivity`, '%d %b %Y %T') AS LastActive
					FROM `user` u 
					LEFT JOIN `employee` e 
					ON e.UserID = u.userID 
					LEFT JOIN `company` c 
					ON e.CompanyID = c.CompanyID 
					LEFT JOIN `companyposition` cp 
					ON cp.PositionID = e.PositionID
					LEFT JOIN `accesslevel` a
					ON u.AccessID = a.AccessID
					GROUP BY u.`userID`
                    ORDER BY u.`AccessID`
                    ASC"
					;
	$result = $pdo->query($sql);

	//Close connection
	$pdo = null;
}
catch (PDOException $e)
{
	$error = 'Error fetching users from the database!';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/error.html.php';
	$pdo = null;
	exit();
}
foreach ($result as $row)
{
	$users[] = array('id' => $row['userID'], 
					'firstname' => $row['firstname'],
					'lastname' => $row['lastname'],
					'email' => $row['email'],
					'accessname' => $row['AccessName'],
					'displayname' => $row['displayname'],
					'bookingdescription' => $row['bookingdescription'],
					'worksfor' => $row['WorksFor'],
					'datecreated' => $row['DateCreated'],
					'isActive' => $row['isActive'],					
					'lastactive' => $row['LastActive'],
					);
}

// Create the registered users list in HTML
include_once 'users.html.php';
?>