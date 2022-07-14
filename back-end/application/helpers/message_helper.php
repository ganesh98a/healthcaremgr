<?php

function system_msgs($msg_key) {
    $global_ary = array(
        'something_went_wrong' => 'Something went wrong.',
        'wrong_username_password' => 'Invalid username or password.',
        'wrong_email_password' => 'Invalid email or password.',
        'account_not_active' => 'Your account is not active please contact to HCM.',
        'success_login' => 'Login successfully.',
        'This_email_not_exist_oversystem' => 'Email address does not exist in our system.',
        'forgot_password_send_mail_succefully' => 'Please visit your inbox to reset your password.',
        'verfiy_token_error' => 'Invalid request.',
        'password_reset_successfully' => 'Password reset successfully.',
        'password_pin_update_success' => 'Save successfully.',
        'verfiy_password_error' => 'Invalid request.',
        'encorrect_pin' => 'Incorrect pin.',
        'token_verfied' => 'Pin verfied.',
        'link_exprire' => 'Sorry your link is expired.',
        'permission_error' => 'Sorry you have no permission to access.',
        'server_error' => 'Server error.',
        'ip_address_error' => 'Ip address changed.',
        'no_staff' => 'Sorry, No staff assigned to this department.',
        'no_staff_create' => 'Sorry, No staff available.',
        'ndis_exist'=>'Sorry, This Ndis number is already exist.',
        'INVALID_INPUT'=>'You provide invalid input type',
        'wrong_email_token' => 'Invalid email or device token.',
        'empty_token_applicant' => 'applicant or device token is empty.',
        'invalid_json'=>'Invalid json data',
        'remaining_interview_start_time'=>'Your interview start after some time please wait',
        'remaining_interview_end_time_over'=>'you can not attempt the test now, because the test end time has already passed.',
        'email_id_exist'=>'Sorry, The Participant Email Id is already exist.',
        'ipad_completed_interview_login'=>'Login is not allowed as Interview task has been completed by Admin.'
    );
    return $global_ary[$msg_key];
}

?>
