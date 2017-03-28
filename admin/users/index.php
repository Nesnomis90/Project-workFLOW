<?php 
// This is the index file for the users folder

// Display users list
include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
try
{
	$pdo = connect_to_db();
	$sql = 'SELECT 	u.`userID`, 
					u.`firstname`, 
					u.`lastname`, 
					u.`email`, 
					c.`name` AS CompanyName, 
					cp.`name` AS CompanyPosition 
					FROM `user` u 
					LEFT JOIN `employee` e 
					ON e.UserID = u.userID 
					LEFT JOIN `company` c 
					ON e.CompanyID = c.CompanyID 
					LEFT JOIN `companyposition` cp 
					ON cp.PositionID = e.PositionID 
					ORDER BY c.`name`'
					;
	$result = $pdo->query($sql);
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
					'companyname' => $row['CompanyName'],
					'companyposition' => $row['CompanyPosition'],
					);
}
include 'users.html.php';

?>