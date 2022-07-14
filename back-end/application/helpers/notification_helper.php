<?php

function notification_msgs($msg_key, $key) {
    $global_ary = array(
        'update_your_detail' => array('title' => 'Update Profile Detail', 'approve' => 'Your profile details update successfully', 'deny' => 'Sorry your profile update request denied'),
        'update_preferred_places' => array('title' => 'Update Preferred Places', 'approve' => 'Your preferred places update successfully', 'deny' => 'Sorry your preferred places request denied'),
        'update_preferred_activities' => array('title' => 'Update Preferred Activities', 'approve' => 'Your preferred activities update successfully', 'deny' => 'Sorry your preferred activities request denied'),
        'archive_participant_goal' => array('title' => 'Archive Goal', 'approve' => 'Your goal archived successfully', 'deny' => 'Sorry your goal archive request denied'),
        'update_participant_goal' => array('title' => 'Update Goal', 'approve' => 'Your goal update successfully', 'deny' => 'Sorry your update goal  request denied'),
        'add_participant_goal' => array('title' => 'Update Goal', 'approve' => 'Your goal added successfully', 'deny' => 'Sorry your goal  request denied'),
        'update_participant_address' => array('title' => 'Update Address', 'approve' => 'Your address update successfully', 'deny' => 'Sorry your update address request denied'),
        'update_participant_booker' => array('title' => 'Update Booker', 'approve' => 'Your booker update successfully', 'deny' => 'Sorry your update booker request denied'),
        'update_participant_phone' => array('title' => 'Update Phone', 'approve' => 'Your phone number update successfully', 'deny' => 'Sorry your update phone number request denied'),
        'update_participant_email' => array('title' => 'Update Email', 'approve' => 'Your email address update successfully', 'deny' => 'Sorry your update email address request denied'),
        'update_participant_kin_details' => array('title' => 'Update Kin details', 'approve' => 'Your kin details update successfully', 'deny' => 'Sorry your update kin details request denied'),
        'update_participant_care_requirement' => array('title' => 'Update care requirement', 'approve' => 'Your care requirement update successfully', 'deny' => 'Sorry your update care requirement request denied'),
        'participant_create_shift' => array('title' => 'Book Shift', 'approve' => 'Your shift create successfully', 'deny' => 'Sorry your shift request denied'),
        
        'update_kin_detail' => array('title' => 'Update Kin Detail', 'description' => 'Update Participant Profile kin Detail'),
        'update_booker_detail' => array('title' => 'Update Booker Detail', 'description' => 'Update Participant Profile Booker Detail'),
        'update_address_detail' => array('title' => 'Update Address Detail', 'description' => 'Update Participant Address Detail'),
        'update_phone_detail' => array('title' => 'Update Phone', 'description' => 'Update Participant Phone Detail'),
        'update_email_detail' => array('title' => 'Update Email', 'description' => 'Update Participant Email Detail'),
        'update_care_requirements' => array('title' => 'Update Care Requirements', 'description' => 'Update Participant Care Requirements Detail'),
        'update_participant_care' => array('title' => 'Update Participant Care', 'description' => 'Update Participant Care Requirement'),
        'update_oc_services' => array('title' => 'Update HCM Services', 'description' => 'Update Participant HCM Services'),
        'update_support_required' => array('title' => 'Update Support Required', 'description' => 'Update Participant Support Required'),
        'update_assistance_required' => array('title' => 'Update Assistance Required', 'description' => 'Update Participant Assistance Required'),
        'update_mobility_required' => array('title' => 'Update Mobility Required', 'description' => 'Update Participant Mobility Required'),
        'update_preferred_language' => array('title' => 'Update Preferred Language', 'description' => 'Update Participant Preferred Language'),
        'update_language_interpreter' => array('title' => 'Update Language Interpreter', 'description' => 'Update Participant Language Interpreter'),
        'update_hearing_interpreter' => array('title' => 'Update Hearing Interpreter', 'description' => 'Update Participant Hearing Interpreter'),
        'add_new_shift' => array('title' => 'Add New Shift', 'description' => 'Insert Participant New Shift'),
        'add_request_roster' => array('title' => 'Add New Request Roster', 'description' => 'Insert Participant Request Roster'),
        'add_new_goal' => array('title' => 'Add New Goal', 'description' => 'Participant Add new Goal'),
        'update_new_goal' => array('title' => 'Update Goal', 'description' => 'Participant update Goal'),
        'add_provide_feedback' => array('title' => 'Add Provide Feedback', 'description' => 'Participant Add Provide Feedback'),
        'update_change_password' => array('title' => 'Participant Change Password', 'description' => 'Participant Change Password'),
        'update_profile_pic' => array('title' => 'Update Profile Pic', 'description' => 'Participant Update Profile Picture'),
        'participant_remove_account' => array('title' => 'Participant remove account', 'description' => 'Participant send remove account request')
    );

    return $global_ary[$msg_key][$key];
}

?>
