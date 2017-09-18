<?php
// Functions to do anything related with sessions

// Remove all sessions used by admin
function unsetSessionsFromAdmin(){

	unset($_SESSION['AddBookingInfoArray']);
	unset($_SESSION['AddBookingChangeUser']);
	unset($_SESSION['AddBookingUsersArray']);
	unset($_SESSION['AddBookingOriginalInfoArray']);
	unset($_SESSION['AddBookingMeetingRoomsArray']);	
	unset($_SESSION['AddBookingUserSearch']);
	unset($_SESSION['AddBookingSelectedNewUser']);
	unset($_SESSION['AddBookingSelectedACompany']);
	unset($_SESSION['AddBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['AddBookingDefaultBookingDescriptionForNewUser']);	
	unset($_SESSION['AddBookingDisplayCompanySelect']);	
	unset($_SESSION['AddBookingCompanyArray']);
	unset($_SESSION['AddBookingUserCannotBookForSelf']);

	unset($_SESSION['EditBookingInfoArray']);
	unset($_SESSION['EditBookingChangeUser']);
	unset($_SESSION['EditBookingUsersArray']);
	unset($_SESSION['EditBookingOriginalInfoArray']);
	unset($_SESSION['EditBookingMeetingRoomsArray']);	
	unset($_SESSION['EditBookingUserSearch']);
	unset($_SESSION['EditBookingSelectedNewUser']);
	unset($_SESSION['EditBookingSelectACompany']);
	unset($_SESSION['EditBookingDefaultDisplayNameForNewUser']);
	unset($_SESSION['EditBookingDefaultBookingDescriptionForNewUser']);	
	unset($_SESSION['EditBookingDisplayCompanySelect']);
	unset($_SESSION['EditBookingCompanyArray']);

	unset($_SESSION['BookingHistoryIntervalNumber']);
	unset($_SESSION['BookingHistoryCompanyInfo']);
	unset($_SESSION['BookingHistoryFirstPeriodIntervalNumber']);

	unset($_SESSION['AddCompanyCompanyName']);

	unset($_SESSION['EditCompanyOriginalName']);
	unset($_SESSION['EditCompanyOriginalRemoveDate']);
	unset($_SESSION['EditCompanyChangedName']);
	unset($_SESSION['EditCompanyChangedRemoveDate']);
	unset($_SESSION['EditCompanyCompanyID']);

	unset($_SESSION['EditCompanyCreditsChangeCredits']);
	unset($_SESSION['EditCompanyCreditsChangeAlternativeCreditsAmount']);
	unset($_SESSION['EditCompanyCreditsCreditsArray']);
	unset($_SESSION['EditCompanyCreditsOriginalInfo']);
	unset($_SESSION['EditCompanyCreditsSelectedCreditsID']);
	unset($_SESSION['EditCompanyCreditsPreviouslySelectedCreditsID']);
	unset($_SESSION['EditCompanyCreditsNewAlternativeAmount']);

	unset($_SESSION['AddCreditsDescription']);
	unset($_SESSION['AddCreditsName']);
	unset($_SESSION['LastCreditsID']);

	unset($_SESSION['EditCreditsOriginalInfo']);
	unset($_SESSION['EditCreditsName']);
	unset($_SESSION['EditCreditsDescription']);
	unset($_SESSION['EditCreditsAmount']);
	unset($_SESSION['EditCreditsMonthlyPrice']);
	unset($_SESSION['EditCreditsHourPrice']);
	unset($_SESSION['EditCreditsCreditsID']);

	unset($_SESSION['AddEmployeeCompanySearch']);
	unset($_SESSION['AddEmployeeUserSearch']);
	unset($_SESSION['AddEmployeeSelectedCompanyID']);
	unset($_SESSION['AddEmployeeSelectedUserID']);
	unset($_SESSION['AddEmployeeSelectedPositionID']);
	unset($_SESSION['AddEmployeeCompaniesArray']);
	unset($_SESSION['AddEmployeeCompanyPositionArray']);
	unset($_SESSION['AddEmployeeUsersArray']);

	unset($_SESSION['EditEmployeeOriginalPositionID']);
	
	unset($_SESSION['TransferEmployeeSelectedCompanyID']);
	unset($_SESSION['TransferEmployeeSelectedCompanyName']);
	unset($_SESSION['TransferEmployeeSelectedCompanyID2']);
	unset($_SESSION['TransferEmployeeSelectedUserID']);
	unset($_SESSION['TransferEmployeeSelectedUserName']);	

	unset($_SESSION['AddEquipmentDescription']);
	unset($_SESSION['AddEquipmentName']);
	unset($_SESSION['LastEquipmentID']);

	unset($_SESSION['EditEquipmentOriginalInfo']);
	unset($_SESSION['EditEquipmentDescription']);
	unset($_SESSION['EditEquipmentName']);
	unset($_SESSION['EditEquipmentEquipmentID']);

	unset($_SESSION['AddEventWeeksSelected']);
	unset($_SESSION['AddEventDaysSelected']);
	unset($_SESSION['AddEventRoomChoiceSelected']);
	unset($_SESSION['AddEventRoomsSelected']);
	unset($_SESSION['AddEventInfoArray']);
	unset($_SESSION['AddEventMeetingRoomsArray']);
	unset($_SESSION['AddEventDaysConfirmed']);
	unset($_SESSION['AddEventDetailsConfirmed']);
	unset($_SESSION['AddEventWeekChoiceSelected']);
	unset($_SESSION['AddEventRoomSelectedButNotConfirmed']);
	unset($_SESSION['AddEventWeekSelectedButNotConfirmed']);

	unset($_SESSION['LogEventsLogLimitSet']);
	unset($_SESSION['LogEventsSearchCheckmarks']);
	unset($_SESSION['LogEventsSearchAllCheckmarks']);
	unset($_SESSION['logEventsEnableDelete']);	

	unset($_SESSION['AddMeetingRoomDescription']);
	unset($_SESSION['AddMeetingRoomName']);
	unset($_SESSION['AddMeetingRoomCapacity']);
	unset($_SESSION['AddMeetingRoomLocation']);
	unset($_SESSION['LastMeetingRoomID']);

	unset($_SESSION['EditMeetingRoomOriginalInfo']);	
	unset($_SESSION['EditMeetingRoomDescription']);
	unset($_SESSION['EditMeetingRoomName']);
	unset($_SESSION['EditMeetingRoomCapacity']);
	unset($_SESSION['EditMeetingRoomLocation']);
	unset($_SESSION['EditMeetingRoomMeetingRoomID']);

	unset($_SESSION['AddRoomEquipmentEquipmentArray']);	
	unset($_SESSION['AddRoomEquipmentEquipmentSearch']);
	unset($_SESSION['AddRoomEquipmentSelectedEquipment']);
	unset($_SESSION['AddRoomEquipmentSelectedEquipmentAmount']);
	unset($_SESSION['AddRoomEquipmentSelectedMeetingRoom']);
	unset($_SESSION['AddRoomEquipmentMeetingRoomArray']);
	unset($_SESSION['AddRoomEquipmentMeetingRoomSearch']);

	unset($_SESSION['EditRoomEquipmentOriginalEquipmentAmount']);
	
	unset($_SESSION['UserEmailsToBeDisplayed']);
	unset($_SESSION['UserEmailListSeparatorSelected']);

	unset($_SESSION['AddNewUserFirstname']);
	unset($_SESSION['AddNewUserLastname']);
	unset($_SESSION['AddNewUserEmail']);
	unset($_SESSION['AddNewUserSelectedAccess']);	
	unset($_SESSION['AddNewUserAccessArray']);
	unset($_SESSION['AddNewUserGeneratedPassword']);
	unset($_SESSION['AddNewUserDefaultAccessID']);
	
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

// Remove all sessions used by all users in user management
function unsetSessionsFromUserManagement(){
	unset($_SESSION['normalUserOriginalInfoArray']);
	unset($_SESSION['normalUserOriginalWorksForArray']);
	unset($_SESSION['normalUserEditInfoArray']);
	unset($_SESSION['normalUserEditWorksForArray']);
	unset($_SESSION['normalUserEditMode']);
}

// Remove all sessions used by all users in company management
function unsetSessionsFromCompanyManagement(){
	unset($_SESSION['normalUserCompanyIDSelected']);
	unset($_SESSION['normalUserCompanyNameSelected']);
	unset($_SESSION['normalCompanyCreateACompany']);
	unset($_SESSION['normalCompanyJoinACompany']);
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