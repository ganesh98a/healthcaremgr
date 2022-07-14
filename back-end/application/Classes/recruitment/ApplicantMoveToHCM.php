<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApplicantMoveToHCM
 *
 * @author user
 */
class ApplicantMoveToHCM {

    private $id;
    private $user_type;
    private $applicant_id;
    private $department;
    private $memberId;
    private $warning;
    private $decryped_pin;
    private $primary_email;
    private $application_id;

    function __construct() {
        $this->CI = &get_instance();
        $this->CI->load->model('recruitment/Recruitment_applicant_move_to_hcm');

        $this->created = DATE_TIME;
    }

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function setAdmin_User_type($admin_user_type) {
        $this->admin_user_type = $admin_user_type;
    }

    function getAdmin_User_type() {
        return $this->admin_user_type;
    }

    function setApplicant_id($applicant_id) {
        $this->applicant_id = $applicant_id;
    }

    function getApplicant_id() {
        return $this->applicant_id;
    }

    function setWarning($warning) {
        $this->warning[] = $warning;
    }

    function setDecrypedPin($pin) {
        $this->decryped_pin = $pin;
    }

    function getDecrypedPin() {
        return $this->decryped_pin;
    }

    function setMemberId($memberId) {
        $this->memberId = $memberId;
    }

    function getMemberId() {
        return $this->memberId;
    }

    function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    function getFirstname() {
        return $this->firstname;
    }

    function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    function getLastname() {
        return $this->lastname;
    }

    function setMiddlename($middlename) {
        $this->middlename = $middlename;
    }

    function getMiddlename() {
        return $this->middlename;
    }

    function setPrimary_email($primary_email) {
        $this->primary_email = $primary_email;
    }

    function getPrimary_email() {
        return $this->primary_email;
    }

    /**
     * @param int $application_id 
     */
    public function setApplicationId($application_id) {
        $this->application_id = $application_id;
    }

    /**
     * 
     * @return int 
     */
    public function getApplicationId() {
        return $this->application_id;
    }

    function move_applicant() {
        // get applicant details 
        $applicantData = $this->get_applicant_details();
        $applicant = isset($applicantData['user_detais']) ? $applicantData['user_detais'] : [];
        $applicantDetails = isset($applicantData['applicant_details']) ? $applicantData['applicant_details'] : [];

        // check applicant details found 
        // if its empty then return error applicant not found as hired
        if (empty($applicant)) {
            $txt = 'Applicant not found as hired';

            // set log
            $this->create_move_applicant_log(2, $txt);
            return ['status' => false, 'error' => $txt];
        }

        // check applicant in HCM module is already exist as active or inactive member profile
        $appl_check = $this->check_applicant_already_active_in_hcm();

        // if get active member then return error as applicant already exist
        if (!empty($appl_check)) {
            $txt = 'Applicant already active in HCM';
            // set log
            $this->create_move_applicant_log(2, $txt);
            return ['status' => false, 'error' => $txt];
        }

        // insert applicant details in HCM
        $memberId = $this->CI->basic_model->insert_records('member', $applicant, FALSE);

        if ($memberId > 0) {
            // set member id
            $this->setMemberId($memberId);

            // get applicant phone number details
            $phone_number = $this->get_applicant_phone_number();

            // check phone number not empty
            if (!empty($phone_number)) {
                // check dubplicate phone of this applicant in HCM section
                $this->check_applicant_phone_already_exist_in_hcm($phone_number);
            }

            // get applicant email details
            $email_address = $this->get_applicant_email_address();

            // check email address not empty
            if (!empty($email_address)) {
                // check duplicate email address of this applicant in HCM section
                $this->check_applicant_email_already_exist_in_hcm($email_address);
            }

            if (!empty($phone_number)) {
                // insert applicant phone number in HCM for new member
                $this->CI->basic_model->insert_records('member_phone', $phone_number, TRUE);
            }

            if (!empty($email_address)) {
                // insert applicant email address in HCM for new member
                $this->CI->basic_model->insert_records('member_email', $email_address, TRUE);
            }

            if ($this->getAdmin_User_type() == '3') {
                $notification_title = 'Applicant Move to admin module';
                $notification_description = 'Congratulations! ' . $applicantDetails->firstname . ' ' . $applicantDetails->middlename . ' ' . $applicantDetails->lastname . ' has been successfully recruited and pushed to the HCM admin module';
            } else {
                // get applicant address
                $address = $this->get_applicant_address();

                // get applicant work area
                $work_area = $this->get_applicant_work_area();

                // get applicant skill
                $skills = $this->get_applicant_skill();

                if (!empty($work_area)) {
                    $position = isset($work_area['position_award']) && !empty($work_area['position_award']) ? $work_area['position_award'] : [];
                    $wa = isset($work_area['work_area']) && !empty($work_area['work_area']) ? $work_area['work_area'] : [];

                    if (!empty($wa))
                        $this->CI->basic_model->insert_records('member_work_area', $wa, TRUE);

                    if (!empty($position))
                        $this->CI->basic_model->insert_records('member_position_award', $position, TRUE);
                }

                if (!empty($address)) {
                    // insert applicant  address in HCM for new member
                    $this->CI->load->model('member/Member_model');
                    $this->CI->Member_model->create_member_address($address);
                }

                if (!empty($skills)) {
                    // insert applicant skill in HCM for new member
                    $this->CI->basic_model->insert_records('member_skill', $skills, TRUE);
                }

                // TODO: Not sure if this is correct.
                // Should we also archive attachments from other applications of this applicant?
                $this->CI->basic_model->update_records('recruitment_applicant_stage_attachment', ['member_move_archive' => 1, 'archive' => 0], ['applicant_id' => $this->applicant_id, 'application_id' => $this->application_id]);

                // send welcome mail to member
                $this->send_welcome_mail_and_username_and_pin_to_member([
					'firstname' => $applicant['firstname'],
					'lastname' => $applicant['lastname'],
					'email' => $applicant['username'],
					'userId' => $memberId
					//'function_content' => $this->CI->config->item('member_webapp_url')."<br>Username/Email: ".$applicant['username']."<br><br>App Username:".$this->memberId."<br>App Pin: ".$this->getDecrypedPin()
				]);

                $notification_title = 'Applicant Move to member module';
                $notification_description = 'Congratulations! ' . $applicantDetails->firstname . ' ' . $applicantDetails->middlename . ' ' . $applicantDetails->lastname . ' has been successfully recruited and pushed to the HCM members module';
            }

            if (isset($applicantDetails->recruiter) && !empty($applicantDetails->recruiter)) {
                $notification = [
                    'specific_admin_user' => $applicantDetails->recruiter,
                    'sender_type' => 2,
                    'status' => 0,
                    'created' => DATE_TIME,
                    'user_type' => 1,
                    'userId' => $memberId,
                    'title' => $notification_title,
                    'shortdescription' => $notification_description,
                ];
                $this->CI->basic_model->insert_records('notification', $notification);
            }
            // set log
            $text = $this->warning ? implode(', ', $this->warning) : '';
            $this->create_move_applicant_log(1, $text);
            return ['status' => true];
        } else {
            $txt = 'Applicant not inserted in db';

            // set log
            $this->create_move_applicant_log(2, $txt);
            return ['status' => false, 'error' => $txt];
        }
    }

    /*
     *  function: get_applicant_details
     *  use: get only applicant details and set as member array format
     */

    function get_applicant_details() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        // get applicant details 
        $applicant_det = $this->CI->Recruitment_applicant_move_to_hcm->get_applicant_info($this->applicant_id);

        if (!empty($applicant_det)) {

            $pin = $this->genrate_member_pin();

            $this->setDecrypedPin($pin);
            $this->setFirstname($applicant_det->firstname);
            $this->setLastname($applicant_det->lastname);
            $this->setMiddlename($applicant_det->middlename);

            $this->update_applicant_pin($pin);
            $encrypted_pin = password_hash($pin, PASSWORD_BCRYPT);

            #is_new_member set to 1 when applicant move to member section
            $user_detais = [
                'firstname' => $applicant_det->firstname,
                'lastname' => $applicant_det->lastname,
                'person_id' => $applicant_det->person_id,
                'fullname' => $applicant_det->fullname,
                'username' => $applicant_det->username,
                'password' => $applicant_det->password ?? password_hash('123456', PASSWORD_BCRYPT),
                'middlename' => $applicant_det->middlename,
                'dob' => $applicant_det->dob,
                'gender' => $applicant_det->gender,
                'department' => $this->get_department(),
                'user_type' => $this->getAdmin_User_type(),
                'created' => DATE_TIME,
                'archive' => 0,
                'enable_app_access' => 1,
                'status' => 1,
                'source_type' => 1,
                'applicant_id' => $this->applicant_id,
                'pin' => $encrypted_pin,
                'is_new_member' => 1,
                'created_by' => $adminId
            ];

            return ['user_detais' => $user_detais, 'applicant_details' => $applicant_det];
        }
    }

    /*
     * function: get_applicant_phone_number
     * use: get applicant phone number and set formate as array according to insert data
     */

    function get_applicant_phone_number() {
        $get_data = $this->CI->Recruitment_applicant_move_to_hcm->get_applicant_phone($this->applicant_id);

        if (!empty($get_data)) {
            foreach ($get_data as $val) {
                $insert_data[] = [
                    'memberId' => $this->memberId,
                    'phone' => $val->phone,
                    'primary_phone' => $val->primary_phone,
                    'archive' => 0,
                ];
            }

            return $insert_data;
        }
    }

    /*
     * function: get_applicant_email_address
     * use: get applicant email address and set according insert data formate
     */

    function get_applicant_email_address() {
        $get_data = $this->CI->Recruitment_applicant_move_to_hcm->get_applicant_email($this->applicant_id);

        if (!empty($get_data)) {
            foreach ($get_data as $val) {
                $insert_data[] = [
                    'memberId' => $this->memberId,
                    'email' => $val->email,
                    'primary_email' => $val->primary_email,
                    'archive' => 0,
                ];

                // set only primary email
                if ($val->primary_email) {
                    $this->setPrimary_email($val->email);
                }
            }

            // if did not get any primary email then set first email as primary
            if (!$this->primary_email) {
                $this->setPrimary_email($insert_data[0]['email']);
            }

            return $insert_data;
        }
    }

    /*
     * function: get_applicant_address
     * use: get applicant address and set according insert data formate
     */
    function get_applicant_address() {
        // get applicant address
        $get_data = $this->CI->Recruitment_applicant_model->get_applicant_address($this->applicant_id);

        // check its not empty
        if (!empty($get_data)) {
            foreach ($get_data as $val) {
                // get address lat long
                $x = (object) getLatLong($val->address);

                $this->CI->load->file(APPPATH.'Classes/member/MemberAddress.php');
                $member_address = new MemberAddress();
                $member_address->setMemberId($this->memberId);
                $member_address->setStreet($val->street);
                $member_address->setPrimaryAddress($val->primary_address);
                $member_address->setCity($val->city);
                $member_address->setPostal($val->postal);
                $member_address->setState($val->state);
                $member_address->setLat((!empty($x->lat)) ? $x->lat : '');
                $member_address->setLong((!empty($x->long)) ? $x->long : '');
                $member_address->setArchive(0);

                $insert_data = $member_address;
            }

            return $insert_data;
        }
    }

    /*
     * function: check_applicant_already_active_in_hcm
     * use: check applicant already active in HCM module using applicant id
     */

    function check_applicant_already_active_in_hcm() {
        return $this->CI->Recruitment_applicant_move_to_hcm->check_applicant_already_active($this->applicant_id);
    }

    /*
     * function: get_department
     * use: according user type get department Id
     */

    function get_department() {
        return $this->CI->Recruitment_applicant_move_to_hcm->get_department_id_by_key($this->user_type);
    }

    /*
     * function: check_applicant_phone_already_exist_in_hcm
     * use: check applicant phone number already exist in HCM module as active member
     */

    function check_applicant_phone_already_exist_in_hcm($phone_number) {
        // conver object to array
        $numbers = (array) $phone_number;

        // check not empty
        if (!empty($numbers)) {
            foreach ($numbers as $key => $val) {
                $val = (array) $val;

                // only for check duplicate phone number remove spaces and + sign
                $numbers[$key]['phone'] = str_replace(' ', '', $numbers[$key]['phone']);
                $numbers[$key]['phone'] = str_replace('+', '', $numbers[$key]['phone']);
            }
        }

        // making index array for direclty check in "WHERE IN" query
        $x = array_column($numbers, 'phone');

        // check duplicate phone number
        $res = $this->CI->Recruitment_applicant_move_to_hcm->check_applicant_phone_already_exist_in_hcm($x);

        // if its not empty mean some duplicate phone number found
        if (!empty($res)) {

            // array convert in index array of duplicate phone number
            $dub_phone = array_column(obj_to_arr($res), 'phone');

            // making text formate for set warning
            $txt = implode(', ', $dub_phone) . ' this phone number already exist in HCM';

            // set warning
            $this->setWarning($txt);
        }
    }

    /*
     * function : check_applicant_email_already_exist_in_hcm
     * use: check applicant email already exist in HCM module as active or inactive
     */

    function check_applicant_email_already_exist_in_hcm($emails) {
        // change object to array
        $emails = (array) $emails;

        // making index array for directly check in "WHERE IN" query
        $x = array_column($emails, 'email');

        // check duplicate email address
        $res = $this->CI->Recruitment_applicant_move_to_hcm->check_applicant_email_already_exist_in_hcm($x);

        // if response is not empty mean duplicate email found
        if (!empty($res)) {
            // making index array of email for conversion in text
            $dub_email = array_column(obj_to_arr($res), 'email');

            // making text for set duplicate email found warning
            $txt = implode(', ', $dub_email) . ' this email already exist in HCM';

            // set warning
            $this->setWarning($txt);
        }
    }

    /*
     * function: create_move_applicant_log
     * use: create applicant log
     */

    function create_move_applicant_log($status, $message = '') {

        $log = array(
            'applicant_id' => $this->applicant_id,
            'application_id' => $this->application_id,
            'memberId' => ($this->memberId) ? $this->memberId : '',
            'status' => $status,
            'any_warning' => !empty($this->warning) ? 1 : 0,
            'message' => $message,
            //            'applicant_data' => json_encode($applicant_data),
            'created' => DATE_TIME,
        );

        return $this->CI->basic_model->insert_records('recruitment_applicant_push_in_member_log', $log, false);
    }

    /*
     * function: genrate_member_pin
     * use: genrate member pin
     */

    function genrate_member_pin() {
        return rand(100000, 999999);
    }

    /*
     * function: update_applicant_pin
     * use: update applicant pin as view mode
     */

    function update_applicant_pin($pin) {

        $data = ['pin' => $pin, "hired_date" => DATE_TIME];
        return $this->CI->basic_model->update_records('recruitment_applicant', $data, ['id' => $this->applicant_id]);
    }

    /*
     * function : get_applicant_work_area
     * use: get applicant work area
     */

    function get_applicant_work_area() {
        $get_data = $this->CI->Recruitment_applicant_move_to_hcm->get_applicant_work_area($this->applicant_id);
        $insert_data_position_award = $insert_data_work_area = [];
        if (!empty($get_data)) {
            foreach ($get_data as $val) {
                $insert_data_position_award[] = [
                    'memberId' => $this->memberId,
                    'work_area' => $val->work_area,
                    'pay_point' => $val->pay_point,
                    'level' => $val->pay_level,
                    'created' => DATE_TIME,
                    'support_type' => '',
                    'archive' => 0,
                ];

                $insert_data_work_area[] = [
                    'memberId' => $this->memberId,
                    'work_area' => $val->work_area,
                    'work_status' => 1,
                    'created' => DATE_TIME,
                    'archive' => 0,
                ];
            }
            return array('work_area' => $insert_data_work_area, 'position_award' => $insert_data_position_award);
        }
    }

    /*
     * function: get_applicant_skill
     * use: get all applicant skill and according to insert in member module
     */

    function get_applicant_skill() {
        $column = ['id', "applicant_id", "skillId", "other_title"];
        $where = ['applicant_id' => $this->applicant_id];
        $skill = $this->CI->basic_model->get_record_where("recruitment_applicant_skill", $column, $where);

        $member_skill = [];
        if (!empty($skill)) {
            foreach ($skill as $val) {
                $member_skill[] = [
                    'member_id' => $this->applicant_id,
                    'skillId' => $val->skillId,
                    'other_title' => $val->other_title,
                    'created' => DATE_TIME
                ];
            }
        }

        return $member_skill;
    }

    /**
	 * sending an automatic email using template to newly created member
	 * it will contain the login details to access applicant/member portal as well as pin for app
	 */
    function send_welcome_mail_and_username_and_pin_to_member($data) {
        require_once APPPATH . 'Classes/Automatic_email.php';
		$obj = new Automatic_email();
		$obj->setEmail_key('new_member_creation');
		$obj->setEmail($data['email']);
		$obj->setDynamic_data($data);
		$obj->setUserId($data['userId']);
		$obj->setUser_type(1);
        $obj->automatic_email_send_to_user();
    }

}
