<?php

function approval_mapping($msg_key, $specific_key = false) {
    $global_ary = array(
        'ProfileUpdate' => array(
            'function' => 'participant_profile',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_profile_details'
        ),
        'PreferredPlacesUpdate' => array(
            'function' => 'participant_place',
            'description' => 'Preferred Places',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_place'
        ),
        'PreferredActivityUpdate' => array(
            'function' => 'participant_activity',
            'description' => 'Preferred Activities',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_activity'
        ),
        'CareRequirment' => array(
            'function' => 'participant_care_requirement',
            'description' => 'Care Requirement',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_care_requirement'
        ),
        'EmailUpdate' => array(
            'function' => 'participant_email',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_email'
        ),
        'PhoneUpdate' => array(
            'function' => 'participant_phone',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_phone'
        ),
        'AddressUpdate' => array(
            'function' => 'participant_address',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_address'
        ),
        'KinUpdate' => array(
            'function' => 'participant_kin_update',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_kin_update'
        ),
        'BookerUpdate' => array(
            'function' => 'participant_booker_update',
            'description' => 'Profile Updates',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_booker_update'
        ),
        'ParticipantGoalAdd' => array(
            'function' => 'participant_add_goal',
            'description' => 'Add Goal',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_add_goal'
        ),
        'ParticipantGoalUpdate' => array(
            'function' => 'participant_update_goal',
            'description' => 'Update Goal',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_update_goal'
        ),
        'ParticipantGoalArchive' => array(
            'function' => 'participant_archive_goal',
            'description' => 'Archive Goal',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_archive_goal'
        ),
        'ShiftCreate' => array(
            'function' => 'participant_create_shift',
            'description' => 'Book Shift',
            'submit_uri' => 'admin/ApproveRequestParticipant/approve_participant_shift'
        ),
        'UpdateMemberInfo' => array(
            'function' => 'approve_member_profile_update',
            'description' => 'Update Member Profile',
            'submit_uri' => 'admin/MemberApprovalRequest/approve_member_profile'
        ),
        'UpdateMemberActivity' => array(
            'function' => 'approve_member_activity_update',
            'description' => 'Update Member Activity',
            'submit_uri' => 'admin/MemberApprovalRequest/member_approve_activity'
        ),
        'UpdateMemberPlaces' => array(
            'function' => 'approve_member_places_update',
            'description' => 'Update Member Places',
            'submit_uri' => 'admin/MemberApprovalRequest/member_approve_place'
        ),
    );

    if (!empty($specific_key)) {
        if (array_key_exists($msg_key, $global_ary)) {
            if (array_key_exists($specific_key, $global_ary[$msg_key])) {
                return $global_ary[$msg_key][$specific_key];
            } else {
                return false;
            }
        }
    }

    if (array_key_exists($msg_key, $global_ary)) {
        return $global_ary[$msg_key];
    }else{
        return false;
    }
}

function check_approval_request_and_set_notification($reqData, $notification_key) {
    $approve_status = false;
    $CI = & get_instance();

    if (!empty($reqData->approval_data)) {
        foreach ($reqData->approval_data as $val) {
            if (!empty($val->approve)) {
                $approve_status = true;
            }
        }
    }

    $CI->approval->setId($reqData->id);
    $CI->notification->setUserId($reqData->userId);
    $CI->notification->setTitle(notification_msgs($notification_key, 'title'));

    if (!empty($approve_status)) {
        $CI->notification->setShortdescription(notification_msgs($notification_key, 'approve'));

        // approve request
        $CI->approval->approveRequest();
    } else {
        $CI->notification->setShortdescription(notification_msgs($notification_key, 'deny'));

        // deny request
        $CI->approval->denyRequest();
    }

    $CI->notification->createNotification();

    return $approve_status;
}

function check_approval_request($reqData) {
    $approve_status = false;
    $CI = & get_instance();
    if (!empty($reqData->approval_data)) {
        foreach ($reqData->approval_data as $val) {
            if (!empty($val->approve)) {
                $approve_status = true;
            }
        }
    }
    $CI->approval->setId($reqData->id);
    if (!empty($approve_status)) {
        // approve request
        $CI->approval->approveRequest();
    } else {
        // deny request
        $CI->approval->denyRequest();
    }
    return $approve_status;
}

?>
