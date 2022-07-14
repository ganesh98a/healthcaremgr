<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Admin_model extends CI_Model
{
    var $status = [
        "1" => "Success",
        "2" => "Failure"
    ];

    var $application = [
        "1" => "Desktop",
        "2" => "Mobile",
        "3" => "Tablet"
    ];

    public function __construct()
    {
        // Call the CI_Model constructor
        // added dummy line 1
        parent::__construct();
    }

    /**
     * fetches all the user statuses
     */
    public function get_user_statuses() {
        $data['status'] = null;
        $data['application'] = null;
        foreach($this->status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data['status'][] = $newrow;
        }
        foreach($this->application as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data['application'][] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * main login check function for HCM
     */
    public function check_login($data) {
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        
        # running validation
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'email', 'label' => 'email', 'rules' => ['required', 'valid_email']),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required'),
            array('field' => 'uuid_user_type', 'label' => 'uuid_user_type', 'rules' => 'required'),
        );
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
            return $response;
        }

        // Get UUID for logging user
        $user_details = $this->basic_model->get_row('users', ['id'], ['username' => $data['email'], 'user_type' => $data['uuid_user_type']]);

        # using the admin auth class for authentication
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminEmail($data['email']);
        $adminAuth->setPassword($data['password']);
        $adminAuth->setUuid_user_type($data['uuid_user_type']);
        $adminAuth->setUuid($user_details->id ?? 0);
        $auth_response = $adminAuth->check_auth();
        # above function returns false and account is locked message
        # returning back so front-end displays lock account screen
        if($auth_response['status'] == 'false' && isset($auth_response['error']) && ($auth_response['error'] == "Your account is locked!" || $auth_response['error'] == "Your account is Inactive!")) {
            return array('status' => false, 'error' => $auth_response['error']);
        }

        # if login validation failed
        if(empty($auth_response['status'])) {
            $this->load->model('member/Member_model');
            $member_id = $this->Member_model->get_member_id_from_email($data['email']);
            if(empty($member_id)){
                return $auth_response;
            }
            add_login_history($member_id, 'Invalid', 'Invalid Password', 2);
        
            # now let's check recent failed attempts. If they are more than
            # the threshold then lock this member
            $valid_attempt = $this->validate_past_invalid_attempts($member_id);
            if(!$valid_attempt) {
                $response = array('status' => false, 'error' => "Your account is locked!");
                return $response;
            }

            return $auth_response;
        }

        $uuid_user_type = $data['uuid_user_type'];

        if ($uuid_user_type == ADMIN_PORTAL) {
            # checking if it is old login
            $is_old_login = $this->check_is_old_login($adminAuth->getAdminid());

            if($is_old_login && get_setting(Setting::PRIVACY_IDEA_OTP_ENABLED) == 1) {
                # if old login detected which is older than the threshold
                # let's get him to activate his email using OTP
                $response = $this->ProcessUserForOTP($auth_response['username']);
                if($response['status'] == false && isset($response['error'])) {
                    return $response;
                }

                # returning back with message
                $response = array('status' => false, 'error' => "Old account detected", 'member_id' => $adminAuth->getAdminid(), 'serial' => $response['serial']);
                return $response;
            }

            # make history of login
            
        }

        add_login_history($adminAuth->getAdminid(), $adminAuth->getOcsToken(),$auth_response['success'], 1, 1);

        # get all permission
        $presmission = get_all_permission($adminAuth->getAdminid());
        $auth_response['permission'] = $presmission;
        $auth_response['type'] = ($adminAuth->getAdminid() == 1) ? 1 : 2;
        $auth_response['usertype'] = $uuid_user_type;
        $auth_response['id'] = $adminAuth->getAdminid();
        return $auth_response;
    }

    /**
     * after old login detection, using PrivacyIdea to send the OTP
     * creates a user in PrivacyIdea if not found
     * then sends an email with OTP
     */
    public function ProcessUserForOTP($email) {

        # using the PrivacyIdea class to perform OTP operations
        require_once APPPATH . 'Classes/PrivacyIdea.php';
        $obj = new PrivacyIdea();
        
        # authenticating and logging into PI system
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # checking if the user already added in their system
        $isuser = $obj->GetUser($email);
        if(!$isuser && $obj->get_error()) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }
        # if not, adding a new user
        else if(!$isuser) {
            $response = array('status' => false, 'error' => "User not found in PI system");
            return $response;
        }
        
        # generating an OTP
        $otpcreated = $obj->GenerateOTP($email);
        if(!$otpcreated && $obj->get_error()) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }
        
        # emailing the OTP
        $otpsent = $obj->EmailOTP($email, $obj->get_serial());
        if(!$otpsent && $obj->get_error()) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        $serial = $obj->get_serial();
        $response = array('status' => true, 'msg' => "Successfully sent the OTP", 'serial' => $serial);
        return $response;
    }

    /**
     * when resend OTP is requested
     */
    public function resend_oldlogin_pin($data) {
        # running validation
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'member_id', 'label' => 'Member', 'rules' => ['required']),
            array('field' => 'email', 'label' => 'email', 'rules' => ['required', 'valid_email']),
        );
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
            return $response;
        }

        # using the PrivacyIdea class to perform OTP operations
        require_once APPPATH . 'Classes/PrivacyIdea.php';
        $obj = new PrivacyIdea();
        
        # authenticating and logging into PI system
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # generating an OTP
        $otpcreated = $obj->GenerateOTP($data['email']);
        if(!$otpcreated && $obj->get_error()) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }
        
        # emailing the OTP
        $otpsent = $obj->EmailOTP($data['email'], $obj->get_serial());
        if(!$otpsent && $obj->get_error()) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        $serial = $obj->get_serial();
        $response = array('status' => true, 'msg' => "Successfully resent the OTP", 'serial' => $serial);
        return $response;
    }

    /**
     * when OTP form is submitted for old login attempts
     */
    public function submit_oldlogin_pin($data) {
        # running validation
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'member_id', 'label' => 'Member', 'rules' => ['required']),
            array('field' => 'email', 'label' => 'email', 'rules' => ['required', 'valid_email']),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required'),
            array('field' => 'pin', 'label' => 'pin', 'rules' => 'required'),
            array('field' => 'serial', 'label' => 'pin', 'rules' => 'required'),
        );
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
            return $response;
        }

        # using the admin auth class for authentication
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminEmail($data['email']);
        $adminAuth->setPassword($data['password']);
        $adminAuth->setUuid($data['member_id'] ?? 0);
        $adminAuth->setUuid_user_type($data['uuid_user_type']);
        $auth_response = $adminAuth->check_auth();
        # above function returns false and account is locked message
        # returning back so front-end displays lock account screen
        if($auth_response['status'] == false && $auth_response['error'] == "Your account is locked!") {
            return $auth_response;
        }

        # if login validation failed
        if(empty($auth_response['status'])) {
            add_login_history($data['member_id'], 'Invalid', 'Invalid Password', 2);
        
            # now let's check recent failed attempts. If they are more than
            # the threshold then lock this member
            $valid_attempt = $this->validate_past_invalid_attempts($data['member_id']);
            if(!$valid_attempt) {
                $response = array('status' => false, 'error' => "Your account is locked!");
                return $response;
            }
        }

        # using the PrivacyIdea class to perform OTP operations
        require_once APPPATH . 'Classes/PrivacyIdea.php';
        $obj = new PrivacyIdea();
        
        # authenticating and logging into PI system
        $islogin = $obj->AuthenticateDetails();
        if(!$islogin) {
            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # validating the OTP
        $otpsent = $obj->ValidateOTP($data['email'], $data['pin'], $data['serial']);
        if(!$otpsent && $obj->get_error()) {

            add_login_history($data['member_id'], 'Invalid', 'Invalid OTP', 2, 2);
        
            # now let's check recent failed attempts. If they are more than
            # the threshold then lock this member
            $valid_attempt = $this->validate_past_invalid_attempts($data['member_id'], 2);
            if(!$valid_attempt) {
                $response = array('status' => false, 'error' => "Your account is locked!");
                return $response;
            }

            $response = array('status' => false, 'error' => $obj->get_error());
            return $response;
        }

        # if all looking good after OTP verification, let's set the sessions
        # make history of logins
        add_login_history($adminAuth->getAdminid(), $adminAuth->getOcsToken(),"Correct OTP", 1, 2);
        add_login_history($adminAuth->getAdminid(), $adminAuth->getOcsToken(),$auth_response['success'], 1, 1);

        # get all permission
        $presmission = get_all_permission($adminAuth->getAdminid());
        $auth_response['permission'] = $presmission;
        $auth_response['type'] = ($adminAuth->getAdminid() == 1) ? 1 : 2;
        $auth_response['id'] = $adminAuth->getAdminid();
        return $auth_response;
    }

    /**
     * checks the last successful login attempt of the user
     * if it is older than the set threshhold, it returns true or else false
     */
    function check_is_old_login($member_id) {
        $old_login = false;
        $this->db->select('mlh.*',false);
        $this->db->from('tbl_member_login_history mlh');
        $this->db->join('tbl_member as m', 'm.id = mlh.memberId', 'inner');
        $this->db->where('mlh.memberId = '.$member_id.' AND mlh.access_using = 1 AND mlh.status_id = 1');
        $this->db->order_by('mlh.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $last_attempt = $query->result();
        
        if(!empty($last_attempt)) {
            $last_attempt_id = $last_attempt[0]->id;
            $this->db->select('mlh.id',false);
            $this->db->from('tbl_member_login_history mlh');
            $this->db->where('mlh.memberId = '.$member_id.' AND mlh.access_using = 1 AND mlh.status_id = 1');
            $this->db->where('mlh.login_time >= (NOW() - INTERVAL '.OLD_LOGIN_INTERVAL.' DAY)');
            $this->db->where('mlh.id',$last_attempt_id);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $last_attempt_valid = $query->result();
            
            if(empty($last_attempt_valid)) {
                $old_login = true;
            }
        }
        return $old_login;
    }
    
    /**
     * To get applicant data by uuid
     * @param uuid -- text
     * @return mixed array
    */
    public function get_applicant_data_by_uuid($data){
        
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'uuid', 'label' => 'uuid', 'rules' => ['required']),
        );
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
            return $response;
        }
        
        $column = array(
            'rja.status',
            'rja.expiry_date',
            'rja.applicant_id',
            'rja.application_id'
        );
        $where = array(
            'rja.uuid' => $this->db->escape_str($data['uuid'], true),
        );

        $this->db->select($column);
        $this->db->from('tbl_recruitment_job_assessment rja');
        $this->db->join('tbl_recruitment_job as rj', 'rj.id = rja.job_id', 'inner');
        $this->db->join('tbl_recruitment_applicant as ra', 'ra.id = rja.applicant_id', 'inner');
        $this->db->join('tbl_person as p', 'p.id = ra.person_id', 'left');
        $this->db->where($where);
        $this->db->order_by('rja.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();        
        $required_data  = [];
        
        if(!empty($result[0]->status) &&  $result[0]->status == 2) {
           //Update the status error if same link accessed more than one time while in progress
           $this->basic_model->update_records('recruitment_job_assessment', ['status' => OA_ERROR, 
            'updated_at' => DATE_TIME], ['uuid' => $data['uuid']]);
           $required_data['status'] = 6;
           $this->load->library('UserName'); 
           $applicant_name = $this->username->getName('applicant', $result[0]->applicant_id);
           
            // create feed for OA Error
            $dataToBeUpdated = [
                'oa_status' => OA_ERROR,           
                'application_id'=> $result[0]->application_id,
                'applicant_name'=>$applicant_name
            ];                     
            $this->load->model('recruitment/Online_assessment_model');
            $this->Online_assessment_model->updateHistory($dataToBeUpdated, $result[0]->application_id, 1);

        }
        else if (!empty($result)) {
            foreach ($result as $val) {
                $curDateTime = date("Y-m-d H:i:s");
                $expireDateTime = date("Y-m-d H:i:s", strtotime($val->expiry_date));
                
                if($expireDateTime < $curDateTime || $val->status == 3){
                    $val->status = 5;
                }
                $required_data['status'] = $val->status;
            }
        }
        
        return array('status' => true, 'data' => $required_data);
    }

    /**
     * validate applicant DOB by uuid
     * @param {array} $data - {uuid, date_of_birht}
     * @return mixed array
    */
    public function get_birth_date_by_uuid($data){
        
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'uuid', 'label' => 'uuid', 'rules' => ['required']),
            array('field' => 'date_of_birth', 'label' => 'Date of birth', 'rules' => ['required']),
        );
        $this->form_validation->set_rules($validation_rules);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return array('status' => false, 'error' => implode(', ', $errors));
        }
        
        # Get applicant DOB
        $column = array(
            'p.date_of_birth',
            'concat(p.firstname," ",p.lastname) as full_name',
            'rja.status',
            'rja.job_id',
            'rj.sub_category as job_type',
            'rja.expiry_date'
        );
        $where = array(
            'rja.uuid' => $this->db->escape_str($data['uuid'], true),
        );
        $this->db->select($column);
        $this->db->from('tbl_recruitment_job_assessment rja');
        $this->db->join('tbl_recruitment_job as rj', 'rj.id = rja.job_id', 'inner');
        $this->db->join('tbl_recruitment_applicant as ra', 'ra.id = rja.applicant_id', 'inner');
        $this->db->join('tbl_person as p', 'p.id = ra.person_id', 'left');
        $this->db->where($where);
        $this->db->order_by('rja.id', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row_array();
        
        $required_data = array();
        $required_data['validate'] = false;

        //HCM_CHILD_YOUTH_ASS_FAMILY  - 3
        //DISABILITY_ASS_DURATION - 4
        //NDIS_JOB_READY_ASS_DURATION - 5

        # validate dob with requested dob
        if (!empty($result) && !empty($result['date_of_birth'])) {
            $assessment = (object) $result;
            $req_dob =date('Y-m-d', strtotime($data['date_of_birth']));
            $dob = date('Y-m-d', strtotime($result['date_of_birth']));
            if ($req_dob === $dob) {
                $required_data['validate'] = true;
            }

            # Duration
            $duration = 0;
            if($assessment->job_type == 3) {
                $duration = HCM_CHILD_YOUTH_ASS_FAMILY; //define in constant.php
            }
            else if($assessment->job_type == 4) {
                $duration = DISABILITY_ASS_DURATION;
            }
            else if($assessment->job_type == 5) {
                $duration = NDIS_JOB_READY_ASS_DURATION;
            }

            # check assessment status
            $curDateTime = date("Y-m-d H:i:s");
            $expireDateTime = date("Y-m-d H:i:s", strtotime($assessment->expiry_date));            
            if($expireDateTime < $curDateTime || $assessment->status == 3){
                $assessment->status = 5;
            }
            
            $required_data['full_name'] = $assessment->full_name;
            $required_data['status'] = $assessment->status;
            $required_data['duration'] = $duration;
        }
        
        return array('status' => true, 'data' => $required_data);
    }

    /**
     * update assessment status 
     * @param  mixed array - $data
     * @return on error => object, on success => true
    */    
    public function update_assessment_by_uuid($data){
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $this->form_validation->set_data($data);
        $validation_rules = array(
            array('field' => 'uuid', 'label' => 'uuid', 'rules' => ['required']),
        );
        $this->form_validation->set_rules($validation_rules);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
            return $response;
        }

        $ex_status = '';
        $status_raw = [ "1" => "Pending", "2" => "In-progress", "3" => "Completed", "4" => "Marked", "5" => "Expired"];
        $status_raw_txt = '';
        $req_status = sprintf('%d',$data['status']);
        $where = array('uuid' => $this->db->escape_str($data['uuid'], true));

        # get existing status data
        $existingStatus = $this->db->get_where('tbl_recruitment_job_assessment', $where)->row_array();  

        if (!empty($existingStatus['status'])) {
            $ex_status = $existingStatus['status'];
        }

        # validate status
        $status_raw_txt = $status_raw[$req_status];
        if ($ex_status == $req_status) {
            $error_msg = "Assessment is already ".$status_raw_txt;
            return array('status' => false, "error" => $error_msg);
        }
        
        # update array
        $updateData = array('status' => $req_status);
        $updateData['updated_at'] = DATE_TIME;
        $updateData['start_date_time'] = DATE_TIME;       
        if ($req_status == 3) {
            $updateData['start_date_time'] = $existingStatus['start_date_time'];
            $updateData['completed_date_time'] = DATE_TIME;
        }
        
        $result = $this->basic_model->update_records('recruitment_job_assessment', $updateData, $where);

        if($result && ($req_status == 2 || $req_status == 3) && !empty($existingStatus['status'])){
            $this->load->model('recruitment/Recruitment_applicant_model');
            $app_details = $this->Recruitment_applicant_model->get_applicant_info
                ($existingStatus['applicant_id'], '');
                $applicant_name = $app_details['fullname'];


            // create feed for OA inprogress
            $dataToBeUpdated = [
                'oa_start_date_time' => $updateData['start_date_time'],
                'applicant_name' => $applicant_name
            ];     

            if($req_status == 2){
                $dataToBeUpdated['oa_status'] = OA_INPROGRESS;
            }
            
            if($req_status == 3){
                $dataToBeUpdated['oa_status'] = OA_SUBMITTED;
                $dataToBeUpdated['oa_completed_date_time'] = $updateData['completed_date_time'];
            }            
            $this->load->model('recruitment/Online_assessment_model');
            $this->Online_assessment_model->updateHistory($dataToBeUpdated, $existingStatus['application_id'], $existingStatus['created_by']);
        }
        
        return array('status' => true, "msg" => "Assessment ".$status_raw_txt." successfully");;
    }     
   
    /**
     * checking if there are more than set number of failed login attempts within set timeframe
     * if it is then locking the user from logging in and allowing super admin to unlock that user
     */
    function validate_past_invalid_attempts($member_id, $access_using = 1) {
        $this->db->select('mlh.*',false);
        $this->db->from('tbl_member_login_history mlh');
        $this->db->join('tbl_member as m', 'm.id = mlh.memberId', 'inner');
        $this->db->where('mlh.memberId = '.$member_id.' AND access_using = '.$access_using.' AND mlh.status_id = 2 AND mlh.login_time >= (NOW() - INTERVAL '.INVALID_ATTEMPT_INTERVAL.' MINUTE)');
        $this->db->where('(CASE WHEN m.date_unlocked IS NOT NULL THEN mlh.login_time > m.date_unlocked ELSE 1 = 1 END)',null,false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $failed_attempts = $query->result();
        $total_failed = 0;
        if(!empty($failed_attempts)) {
            $total_failed = count($failed_attempts);
        }

        if($total_failed >= INVALID_ATTEMPT) {
            $this->basic_model->update_records('member', array('is_locked' => 1), $where = array('id' => $member_id));
            return false;
        }
        return true;
    }

    /**
     * unlocks the member
     */
    function unlock_member($member_id) {
        $this->basic_model->update_records('member', array('is_locked' => 0), $where = array('id' => $member_id));
        return true;
    }

    /*
     * For getting users' login history list
     */
    public function get_user_login_history_list($reqData) {

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("lh.ip_address", "lh.country","lh.login_url","DATE_FORMAT(lh.login_time,'%d/%m/%Y')","(CASE WHEN lh.status_id = 1 THEN 'Successful' WHEN lh.status_id = 2 THEN 'Failure' END)", "(CASE WHEN lh.application = 1 THEN 'Desktop' WHEN lh.application = 2 THEN 'Mobile' WHEN lh.application = 3 THEN 'Tablet' END)");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new lightening filters
        if(isset($filter->filters)) {
            foreach($filter->filters as $filter_obj) {
                if(empty($filter_obj->select_filter_value)) continue;

                $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
                
                if($filter_obj->select_filter_field == "ip_address") {
                    $this->db->where('lh.ip_address '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "login_url") {
                    $this->db->where('lh.login_url '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "country") {
                    $this->db->where('lh.country '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "application_label") {
                    $this->db->where('lh.application '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "status_label") {
                    $this->db->where('lh.status_id '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "login_time") {
                    $this->db->where('DATE_FORMAT(lh.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        }

        # sorting part
        $available_column = ["ip_address", "country","status_id", "application","login_time","login_url"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'lh.id';
            $direction = 'DESC';
        }
        $select_column = ["lh.ip_address", "lh.country","lh.status_id", "lh.application","lh.login_time","lh.login_url", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE 
        WHEN lh.status_id = 1 THEN 'Successful'
        WHEN lh.status_id = 2 THEN 'Failure'
        ELSE ''
        END as status_label");
        $this->db->select("CASE 
        WHEN lh.application = 1 THEN 'Desktop'
        WHEN lh.application = 2 THEN 'Mobile'
        WHEN lh.application = 3 THEN 'Tablet'
        ELSE ''
        END as application_label");

        $this->db->from('tbl_member_login_history as lh');
        $this->db->join('tbl_member as m', 'm.uuid = lh.memberId', 'inner');
        $this->db->where('lh.archive', 0);

        if(isset($reqData->user_id) && $reqData->user_id > 0)
        $this->db->where('lh.memberId', $reqData->user_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; # $ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'last_query' => $last_query, 'data' => $result, 'status' => true, 'msg' => 'Fetched login history list successfully');
        return $return;
    }

    public function get_member_details_from_email($obj)
    {
        // using query check username
        $column = array(
            'm.id',
            'concat(m.firstname," ",m.lastname) as full_name',
            'm.password',
            'm.gender',
            'm.status',
            'm.is_locked',
            'm.username',
            'tbl_member_email.email',
            'm.profile_pic as avatar'
        );
        // short_code = internal_staff mean only internal department user can login
        $where = array(
            'tbl_member_email.email' => $obj->getAdminEmail(),
            'm.archive' => 0,
            'd.short_code' => 'internal_staff',
        );
        $this->db->select($column);
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');
        $this->db->join('tbl_member_email', 'tbl_member_email.memberId = m.id AND tbl_member_email.primary_email = 1', 'INNER'); // LEFT join is slow in this query
        $this->db->where($where);
        $query = $this->db->get();

        return $query->row();
    }


    public function get_member_details_by_uuid($obj)
    {
        // using query check username
        $column = array(
            'u.id',
            'concat(m.firstname," ",m.lastname) as full_name',
            'u.password',
            'm.gender',
            'm.status',
            'm.is_locked',
            'u.username',
            'm.profile_pic as avatar',
            'm.id as member_id'
        );
        
        /**
         * short_code = internal_staff mean only internal department user can login
         *  user_type = 1 - admin , 2 - member , 3 - organisation
         * 
         * */ 
        $uuid_user_type = $obj->getUuid_user_type();
        
        if($uuid_user_type == ADMIN_PORTAL) {
            $where = array(
                'm.uuid' => $obj->getUuid(),
                'm.archive' => 0,
                'd.short_code' => 'internal_staff',
            );

            $this->db->select($column);
            $this->db->from('tbl_users as u');
            $this->db->join('tbl_member as m', 'm.uuid=u.id', 'inner');
            $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');
            $this->db->where($where);
            $query = $this->db->get();

        return $query->row();
        }else if($uuid_user_type == MEMBER_PORTAL){
            $column = array(
                'u.password',
                'u.id',
                'u.status',
                'u.username'
            );
            $where = array(
                'u.id' => $obj->getUuid(),
                'u.archive' => 0, 
            );

            $this->db->select($column);
            $this->db->from('tbl_users as u');
            $this->db->where($where);
            $query = $this->db->get();

        return $query->row();
        }else if($uuid_user_type == ORGANISATION_PORTAL){
            $column = array(
                'concat(p.firstname," ",p.lastname) as full_name',
                'u.username',
                'p.id as person_id',
                'u.password',
                'u.status',
                'u.id',
                "'' as avatar"
            );
            $where = array(
                'p.uuid' => $obj->getUuid(),
                'p.archive' => 0,
            );

            $this->db->select($column);
            $this->db->from('tbl_users as u');
            $this->db->join('tbl_person as p', 'p.uuid = u.id', 'inner');
            $this->db->where($where);
            $query = $this->db->get();

            return $query->row();
        }
        
        
    }

    public function check_pin($obj)
    {
        // using query check username
        $column = array(
            'm.pin'
        );
        // short_code = internal_staff mean only internal department user can login
        $where = array(
            'm.uuid' => $obj->getAdminid(),
            'm.archive' => 0,
            'd.short_code' => 'internal_staff'
        );
        $this->db->select($column);
        $this->db->from('tbl_member as m');
        $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');
        $this->db->where($where);
        $query = $this->db->get();

        return $query->row();
    }

    public function check_valid_email($email, $user_type)
    {
        // using query check username
        // $where condition is common for all user type
        $res = [];
        $where = [
                'u.username' => $email,
                'u.user_type' => $user_type,
                'u.archive' => 0,
                'u.status' => 1,
                ];
        if($user_type==ORGANISATION_PORTAL){
            $column = array(
                'u.username',
                'u.user_type',
                'u.archive',
                'p.firstname',
                'p.lastname',
                'u.status',
                'u.id'
                );
                 // using query check username       
                $this->db->select($column);
                $this->db->from('tbl_users as u');
                $this->db->join('tbl_person as p', 'u.id = p.uuid', 'inner');
                $this->db->where($where);
                $query = $this->db->get();
                return $query->row();
           
        }else if($user_type==MEMBER_PORTAL){
               $column = array(
                'u.username',
                'u.user_type',
                'u.archive',
                'p.firstname',
                'p.lastname',
                'u.status',
                'u.id'
                );

            $where['m.enable_app_access'] = 1;
            $where['m.source_type'] = 1;
            $where['m.is_new_member'] = 1;
            $where['m.archive'] = 0;

            $this->db->select($column);
            $this->db->from('tbl_users as u');
            $this->db->join('tbl_member as m', 'u.id = m.uuid', 'inner');
            $this->db->join('tbl_person as p', 'p.id = m.person_id', 'inner');
            $this->db->where($where);
            $query = $this->db->get();
            $member_or_applicant_data = $query->row();
            if(empty($member_or_applicant_data)){
                // get applicant details
                $column = array( 'u.id','ra.firstname','ra.lastname');
                $where = [
                    'u.username' => $email,
                    'u.user_type' => $user_type,
                    'u.archive' => 0,
                    'u.status' => 1,
                    ];

                $this->db->select($column);
                $this->db->from('tbl_users as u');
                $this->db->join('tbl_recruitment_applicant as ra', 'u.id = ra.uuid', 'inner');
                $this->db->join('tbl_person as p', 'p.id = ra.person_id', 'inner');
                $this->db->where($where);
                
                $query = $this->db->get();
                $member_or_applicant_data = $query->row();

                if(empty($member_or_applicant_data)){
                    return $res;
                }
                $member_or_applicant_data->type = 'applicant';
            }else{
                $member_or_applicant_data->type='member';
            } 
            return $member_or_applicant_data;
        }else{
            // Admin portal
            $column = array(
                'u.id',
                'm.firstname',
                'm.lastname',
                'm.status'
                );

                $this->db->select($column);
                $this->db->from('tbl_users as u');
                $this->db->join('tbl_member as m', 'm.uuid = u.id', 'inner');
                $this->db->join('tbl_member_email as me', 'me.memberId = m.id', 'inner');
                $this->db->join('tbl_department as d', 'd.id = m.department', 'inner');
                $this->db->where($where);
                $query = $this->db->get();
                return $query->row();
        }

    }

    public function list_role_dataquery($reqData)
    {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $available_colomn = array('id', 'name', 'status', 'created', 'archive' );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_colomn)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'id';
            $direction = 'asc';
        }
        if (!empty($filter)) {
            $this->db->like('id', $filter);
            $this->db->or_like('name', $filter);
            $this->db->or_like('created', $filter);
        }
        $colowmn = array(
            'id',
            'name',
            'status',
            'created',
            'archive'
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->from(TBL_PREFIX . 'role');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where('archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $row = array();
                $row['id'] = $val->id;
                $row['name'] = $val->name;
                $row['created'] = date('d-m-y', strtotime($val->created));
                $row['action'] = '';
                $row['archive'] = $val->archive;
                $row['status'] = $val->status;
                $dataResult[] = $row;
            }
        }
        $return = array(
            'count' => $dt_filtered_total,
            'data' => $dataResult
        );
        return $return;
    }

    //this method is used Recruitment side also to check duplicate email
    public function check_dublicate_email($email, $adminId)
    {
        $this->db->select(array(
            'username',
            'id'
        ));
        $this->db->from('tbl_users');
        $this->db->where(array(
            'username' => $email,
            'user_type' => ADMIN_PORTAL
        ));
        if (!empty($adminId)) {            
            $this->db->where('id !=', $adminId);
        }
            
        $query = $this->db->get();
        return $query->result();
    }

    public function list_admins_dataquery($reqData)
    {
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? NULL;
        $orderBy = '';
        $direction = '';
        
        $available_colomn = array('id', 'uuid', 'username', 'firstname', 'lastname', 'status', 'gender', 'archive','access_role_label');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_colomn)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'm.created';
            $direction = 'desc';
        }
        $src_columns = array('username', 'firstname', 'lastname', 'phone', 'email', 'gender', 'id','ar.name');

        if (!empty($filter->search)) {
            $this->db->group_start();
            $search_value = $filter->search;
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $search_value);
                } else {
                    $this->db->or_like($column_search, $search_value);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        if (!empty($filter->search_by)) {
            if ($filter->search_by == 'archive_only') {
                $this->db->where('m.archive', 1);
            } elseif ($filter->search_by == 'inactive_only') {
                $this->db->where('status', 0);
            } elseif ($filter->search_by == 'active_only') {
                $this->db->where('status', 1);
                $this->db->where('m.archive', 0);
            }
        }
        $colowmn = array('m.id', 'm.uuid', 'username', 'm.firstname', 'm.lastname','concat(m.firstname," ",m.lastname) as fullname', 'status', 'gender', 'm.archive','ar.name as access_role_label');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->select("(select me.email from tbl_member_email as me where me.memberId = m.id AND me.primary_email = 1 AND me.archive = 0) as email");
        $this->db->select("(select mp.phone from tbl_member_phone as mp where mp.memberId = m.id AND mp.primary_phone = 1 AND mp.archive = 0) as phone");


        $this->db->from('tbl_member as m');
        //        $this->db->join('tbl_member_email as me', 'me.memberId = m.id AND me.primary_email = 1', 'Inner');
        //        $this->db->join('tbl_member_phone as mp', 'mp.memberId = m.id AND mp.primary_phone = 1', 'Inner');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_admin_user_type as aut', 'aut.id = m.user_type AND aut.archive = 0', 'inner');
        $this->db->join('tbl_access_role as ar', 'ar.id = m.access_role_id and ar.archive = 0', 'left');
       
        $this->db->select("(
            CASE WHEN m.status=0 and m.archive=0  THEN 'Inactive'
                 WHEN m.archive=1 and m.status=0 THEN 'Archived'
                 WHEN m.status=1  THEN 'Active'
                 END) as member_status");
       
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        $return = array(
            'count' => $dt_filtered_total,
            'data' => $dataResult
        );
        return $return;
    }

    public function get_admin_permission($adminId)
    {
        $this->db->select("CASE
            WHEN p.moduleId > 0 AND coalesce((select 1 from tbl_module_title where key_name = 'finance' and id = p.moduleId),0) = 1  THEN coalesce((select '1' from tbl_finance_staff where status = 1 AND archive = 0 and adminId = ap.adminId),0 )
            WHEN mt.its_enable_disable_module = 1 THEN mt.status
            ELSE '1'
        END as access", false);
        $this->db->select(array('p.permission'));
        $this->db->from('tbl_admin_permission as ap');
        $this->db->join('tbl_permission as p', 'ap.permissionId = p.id', 'inner');
        $this->db->join('tbl_module_title as mt', 'mt.id = p.moduleId AND mt.archive = 0', 'inner');
        $this->db->where(array('ap.adminId' => $adminId));
        $this->db->having("access", 1);
        $permission_query2 = $this->db->get_compiled_select();

        $this->db->select(array('ar.roleId'));
        $this->db->from('tbl_admin_role as ar');
        $this->db->where(array('ar.adminId' => $adminId));
        $sub_query_for_role = $this->db->get_compiled_select();


        $super_admins = get_super_admins();

        $this->db->select("(CASE WHEN mt.its_enable_disable_module = 1 THEN mt.status = 1 ELSE 1 END ) as access");
        $this->db->select(array('p.permission'));
        $this->db->from("tbl_permission as p");
        $this->db->join("tbl_role_permission as rp", 'rp.permission = p.id', 'inner');
        $this->db->join('tbl_module_title as mt', 'mt.id = p.moduleId AND mt.archive = 0', 'inner');
        $this->db->having("access", 1);

        if (!in_array($adminId, $super_admins)) {
            $this->db->where_in('rp.roleId', $sub_query_for_role, false);
        }

        $permission_query1 = $this->db->get_compiled_select();
        $permission_query = $this->db->query($permission_query1 . ' UNION ' . $permission_query2);
        $res = $permission_query->result();

        // last_query();
        $res = array_filter($res, function ($val) {
            return $val->access == 1 ? true : false;
        });
        return $res;
    }

    public function check_admin_permission($adminId, $pemission_key)
    {
        if (in_array($adminId, get_super_admins()) && $pemission_key)
            return true;

        $this->db->select("CASE
            WHEN (p.moduleId > 0 OR p.permission = 'access_finance' ) AND
            r.role_key != 'finance_admin' AND
            coalesce((select 0 from tbl_admin_role as sub_ar INNER JOIN tbl_role as sub_r ON sub_r.id = sub_ar.roleId AND sub_r.role_key= 'finance_admin' where sub_ar.adminId = ar.adminId), 1) = 1  AND
            (coalesce((select 1 from tbl_module_title where key_name = 'finance' and id = p.moduleId AND mt.its_enable_disable_module = 1 AND mt.status = 1), 0) = 1  OR p.permission = 'access_finance') 
                   THEN coalesce((select '1' from tbl_finance_staff where status = 1 and archive = 0 and adminId = ar.adminId), 0 )
            WHEN mt.its_enable_disable_module = 1 THEN mt.status
            ELSE '1'
        END as access", false);
        $this->db->select(array('p.permission'));
        $this->db->from('tbl_admin_role as ar');
        $this->db->join('tbl_role as r', 'r.id = ar.roleId', 'inner');
        $this->db->join('tbl_role_permission as rp', 'r.id = rp.roleId', 'inner');
        $this->db->join('tbl_permission as p', 'rp.permission = p.id', 'inner');
        $this->db->join('tbl_module_title as mt', 'mt.id = p.moduleId AND mt.archive = 0', 'inner');

        // check specific permission status like recruitment section and CRM section
        if ($pemission_key === 'access_recruitment' || $pemission_key === 'access_recruitment_admin') {
            // check from recruitment staff table status
            // if 1 = enable / 0- disable
            $this->db->join("tbl_recruitment_staff as rs", 'rs.adminId = ar.adminId AND rs.status = 1', 'inner');
        } elseif ($pemission_key === 'access_crm') {
            // check from CRM staff table status
            // if 1 = enable / 0- disable
            $this->db->join("tbl_crm_staff as cs", 'cs.admin_id = ar.adminId AND cs.status = 1', 'inner');
        }
        $this->db->where(array('p.permission' => $pemission_key));
        $this->db->where(array('ar.adminId' => $adminId));
        $this->db->having("access", 1);
        $permission_query1 = $this->db->get_compiled_select();

        // check individual check permission query
        $this->db->select("CASE
            WHEN p.moduleId > 0 AND coalesce((select 1 from tbl_module_title where key_name = 'finance' and id = p.moduleId AND mt.its_enable_disable_module = 1 AND mt.status = 1),0) = 1  THEN coalesce((select '1' from tbl_finance_staff where status = 1 AND archive = 0 and adminId = ap.adminId),0 )
            WHEN mt.its_enable_disable_module = 1 THEN mt.status
            ELSE '1'
        END as access", false);
        $this->db->select(array('p.permission'));
        $this->db->from('tbl_admin_permission as ap');
        $this->db->join('tbl_permission as p', 'ap.permissionId = p.id', 'inner');
        $this->db->join('tbl_module_title as mt', 'mt.id = p.moduleId AND mt.archive = 0', 'inner');
        $this->db->where(array('p.permission' => $pemission_key));
        $this->db->where(array('ap.adminId' => $adminId));
        $this->db->having("access", 1);
        $permission_query2 = $this->db->get_compiled_select();

        $permission_query = $this->db->query($permission_query1 . ' UNION ' . $permission_query2);

        $res = $permission_query->row();
        // last_query();
        if (!empty($res)) {
            return ($res->access) ? true : false;
        } else {
            return false;
        }
    }

    public function get_admin_based_roles($adminId, $all)
    {
        $join = ($all) ? 'left' : 'inner';
        $this->db->select(array(
            'r.id',
            'r.name',
            'ar.adminId as access',
            'r.role_key'
        ));
        $this->db->from('tbl_role as r');
        if ($adminId == 1) {
            $this->db->join('tbl_admin_role as ar', 'r.id = ar.roleId', $join);
        } else {
            $this->db->join('tbl_admin_role as ar', 'r.id = ar.roleId AND ar.adminId = ' . $adminId, $join);
        }
        $this->db->group_by("r.id");
        $this->db->order_by("r.weight", "asc");
        $role_query = $this->db->get();
        $rols_data = $role_query->result();
        if (!empty($rols_data)) {
            foreach ($rols_data as $val) {
                if ($val->access > 0) {
                    $val->access = true;
                }
            }
        }
        return $rols_data;
    }

    public function get_admin_details($adminId) {
        $this->db->select(array('m.id','m.uuid', 'm.username', 'm.firstname', 'm.lastname', 'm.position', 'm.department', 'm.user_type', 'm.is_super_admin as its_super_admin','m.timezone', 'concat(m.firstname," ",m.lastname) as fullname','m.archive','m.is_locked','m.date_unlocked', 'm.access_role_id', 'ar.name as access_role_label', 'm.profile_pic as avatar', 'bu.id as business_unit', 'bu.business_unit_name'));

        $this->db->from(TBL_PREFIX .'member as m');
        $this->db->join(TBL_PREFIX .'users as u', 'u.id = m.uuid', 'inner');
        $this->db->join(TBL_PREFIX .'user_business_unit as buu',  'buu.user_id = u.id', 'left');
        $this->db->join(TBL_PREFIX .'business_units as bu',  'bu.id = buu.bu_id', 'left');
        $this->db->join(TBL_PREFIX .'department as d', 'd.id = m.department', 'inner');
        $this->db->join(TBL_PREFIX .'access_role as ar', 'ar.id = m.access_role_id and ar.archive = 0', 'left');
        $this->db->where(array(
            'm.uuid' => $adminId,
            'd.short_code' => 'internal_staff'
        ));
        $query = $this->db->get();
        $result = (array) $query->row();
        if(!empty($result)){
            $result['its_super_admin'] = (boolean) $result['its_super_admin'];
            $result['is_locked'] = (boolean) $result['is_locked'];
        }
        return $result;
    }

    function get_admin_phone_number($adminId)
    {
        $tbl_admin_phone = TBL_PREFIX . 'member_phone';
        $this->db->select(array(
            'phone as name',
            'primary_phone'
        ));
        $this->db->from($tbl_admin_phone);
        $this->db->where(array(
            'memberId' => $adminId
        ));
        $query = $this->db->get();
        return $result = (array) $query->result();
    }

    function get_admin_email($adminId)
    {
        $tbl_admin_email = TBL_PREFIX . 'member_email';
        $this->db->select(array(
            'email as name',
            'primary_email'
        ));
        $this->db->from($tbl_admin_email);
        $this->db->where(array(
            'memberId' => $adminId
        ));
        $query = $this->db->get();
        return $result = (array) $query->result();
    }

    public function get_all_loges($reqData)
    {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $moduleArr = array(
            'admin' => 1,
            'participant' => 2,
            'member' => 3,
            'schedule' => 4,
            'fms' => 5,
            'house' => 6,
            'organisation' => 7,
            'imail' => 8
        );
        $available_colomn = array('id', 'created_by', 'title', 'time' );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_colomn) ) {
                if ($sorted[0]->id == 'time') {
                    $orderBy = 'TIME(lg.created)';
                } else {
                    $orderBy = 'lg.' . $sorted[0]->id;
                }
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'id';
            $direction = 'desc';
        }
        if (!empty($filter->search_box)) {
            $this->db->like('lg.created_by', date($filter->search_box));
            $this->db->or_like('lg.title', $filter->search_box);
        }
        if (!empty($filter->on_date)) {
            $this->db->where("Date(lg.created)", date('Y-m-d', strtotime($filter->on_date)));
        } else {
            if (!empty($filter->start_date)) {
                $this->db->where("Date(lg.created) >", date('Y-m-d', strtotime($filter->start_date)));
            }
            if (!empty($filter->end_date)) {
                $this->db->where("Date(lg.created) <", date('Y-m-d', strtotime($filter->end_date)));
            }
        }
        $colowmn = array(
            'lg.id',
            'lg.created_by',
            'lg.title',
            "DATE_FORMAT(lg.created, '%d/%m/%Y') as created",
            "DATE_FORMAT(lg.created, '%h:%i %p') as time"
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $colowmn)), false);
        $this->db->from('tbl_logs as lg');
        $this->db->join('tbl_module_title as mt', 'mt.id = lg.module', 'INNER');
        if (!empty($filter->module)) {
            $this->db->where('mt.key_name', $filter->module);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        $return = array(
            'status' => true,
            'count' => $dt_filtered_total,
            'data' => $dataResult
        );
        return $return;
    }

    /**
     * getting access role details along with objects and its permissions
     */
    public function get_access_role_objects_permissions($data) {
        if(!isset($data['id']) || empty($data['id'])) {
            $response = array('status' => false, 'error' => "Missing access role id");
            return $response;
        }
        $access_role_id = $data['id'];

        # fetching the access tole title
        $select_column = ["ar.name"];
        $this->db->select($select_column);
        $this->db->from('tbl_access_role as ar');
        $this->db->where('ar.archive', 0);
        $this->db->where('ar.id', $access_role_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ardata = $query->result_array();
        
        if(empty($ardata)) {
            $response = array('status' => false, 'error' => "Access role not found");
            return $response;
        }

        # getting the distinct module names
        $select_column = ["distinct(r.name)", "r.id"];
        $this->db->select($select_column);
        $this->db->from('tbl_access_role_object as aro');
        $this->db->join('tbl_module_object as mo', 'mo.id = aro.module_object_id', 'inner');
        $this->db->join('tbl_role as r', 'r.id = mo.role_id', 'inner');
        $this->db->where('aro.archive', 0);
        $this->db->where('mo.archive', 0);
        $this->db->where('r.archive', 0);
        $this->db->where('aro.access_role_id', $access_role_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $objdata = $query->result_array();
        if(empty($objdata)) {
            $response = array('status' => false, 'error' => "Access role not found");
            return $response;
        }

        # looping through distinct module names to fetch objects and permissions of it
        $return_data['name'] = $ardata[0]['name'];
        $module_rows = null;
        for($i=0;$i<count($objdata);$i++) {
            $module_rows[$i]['module_id'] = $objdata[$i]['id'];
            $module_rows[$i]['module_name'] = $objdata[$i]['name'];
            $module_objects_list = $this->get_module_objects(["module_id" => $module_rows[$i]['module_id'], "access_role_id" => $access_role_id]);

            if($module_objects_list['status'] == true)
                $module_rows[$i]['module_objects_list'] = $module_objects_list['data'];
            else
                $module_rows[$i]['module_objects_list'] = [];
        }
        $return_data['module_rows'] = $module_rows;
        $response = array('status' => true, 'data' => $return_data);
        return $response;
    }

    /**
     * getting list of all objects of module stored in tbl_module_object table
     */
    public function get_module_objects($data) {
        
        $access_role_id = (isset($data['access_role_id']) && $data['access_role_id']) ? $data['access_role_id'] : null;
        $module_id = (isset($data['module_id']) && $data['module_id']) ? $data['module_id'] : null;

        if($access_role_id)
            $select_column = ["mo.*", "r.id as module_id", "r.name as module_name", "aro.id as access_role_object_id", "aro.read_access as read_checked", "aro.create_access as create_checked", "aro.edit_access as edit_checked", "aro.delete_access as delete_checked"];
        else
            $select_column = ["mo.*", "r.id as module_id", "r.name as module_name"];
        $this->db->select($select_column);
        $this->db->from('tbl_module_object as mo');
        $this->db->join('tbl_role as r', 'r.id = mo.role_id', 'inner');
        if($access_role_id)
        $this->db->join('tbl_access_role_object as aro', 'mo.id = aro.module_object_id and aro.archive = 0 and aro.access_role_id = '.$access_role_id, 'left');
        $this->db->where('mo.archive', 0);
        $this->db->where('r.archive', 0);
        if($module_id)
        $this->db->where('r.id', $module_id);
        $this->db->order_by('mo.id', 'ASC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $data = $query->result_array();
        $response = array('status' => true, 'data' => $data);
        return $response;
    }

    /**
     * adding/updating the access role and its relevant information
     */
    public function create_update_access_role($data, $adminId) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $access_role_id = $data['id'] ?? 0;
        $errors = $insert_data = null;
        if(!isset($data['name']) || empty($data['name']))
            $errors[] = "Please provide Role name";

        if(!isset($data['module_objects_list']) || empty($data['module_objects_list']))
            $errors[] = "Please select at least one module and its permissions";
        else {
            $one_perm_checked = false;
            foreach($data['module_objects_list'] as $row) {
                $module_id = $row['module_id'];
                $module_name = $row['module_name'];
                
                $insert_row = null;
                $insert_row['module_object_id'] = $row['id'];
                $insert_row['access_role_object_id'] = (isset($row['access_role_object_id']) ? $row['access_role_object_id'] : null);
                $insert_row['read_access'] = 0;
                $insert_row['create_access'] = 0;
                $insert_row['edit_access'] = 0;
                $insert_row['delete_access'] = 0;

                if(isset($row['read_checked']) && $row['read_checked'] == true) {
                    $one_perm_checked = true;
                    $insert_row['read_access'] = 1;
                }
                
                if(isset($row['create_checked']) && $row['create_checked'] == true) {
                    $one_perm_checked = true;
                    $insert_row['create_access'] = 1;
                }
                
                if(isset($row['edit_checked']) && $row['edit_checked'] == true) {
                    $one_perm_checked = true;
                    $insert_row['edit_access'] = 1;
                }

                if(isset($row['delete_checked']) && $row['delete_checked'] == true) {
                    $one_perm_checked = true;
                    $insert_row['delete_access'] = 1;
                }

                if($insert_row['read_access'] || $insert_row['create_access'] || $insert_row['edit_access'] || $insert_row['delete_access'])
                $insert_data[] = $insert_row;
            }

            if(!$one_perm_checked)
            $errors[] = "Please provide at least one access permission";
        }

        # if any validation has failed
        if(!empty($errors)) {
            $response = ['status' => false, 'error' => implode(",",$errors)];
            return $response;
        }

        # adding/updating tbl_access_role table
        $postdata["name"] = $data['name'];
        if ($access_role_id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("access_role", $postdata, ["id" => $access_role_id]);
        } else {
            $postdata["created"] = DATE_TIME;
            $postdata["created_by"] = $adminId;
            $access_role_id = $this->basic_model->insert_records("access_role", $postdata, $multiple = FALSE);
        }

        $existing_access_role_obj_ids = [];
        $selected_access_role_obj_ids = [];
        # fetching existing access role objects
        if (!empty($data['id']))
        $existing_access_role_obj_ids = $this->get_access_role_object_ids($access_role_id);

        foreach($insert_data as $row) {
            $postdata = [
                "access_role_id" => $access_role_id,
                "module_object_id" => $row['module_object_id'],
                "read_access" => isset($row['read_access'])?$row['read_access']:0,
                "create_access" => isset($row['create_access'])?$row['create_access']:0,
                "edit_access" => isset($row['edit_access'])?$row['edit_access']:0,
                "delete_access" => isset($row['delete_access'])?$row['delete_access']:0];

            # adding an entry of module object permission
            if(!isset($row['access_role_object_id']) || empty($row['access_role_object_id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $access_role_object_id = $this->basic_model->insert_records("access_role_object", $postdata);
            }
            else {
                $access_role_object_id = $row['access_role_object_id'];
                $selected_access_role_obj_ids[] = $access_role_object_id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $access_role_object_id = $this->basic_model->update_records("access_role_object", $postdata, ["id" => $access_role_object_id]);
            }
        }

        # any existing module object permissions that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_access_role_obj_ids, $selected_access_role_obj_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->basic_model->update_records("access_role_object", 
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $rem_id]);
            }
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Access role has been updated successfully.';
        } else {
            $msg = 'Access role has been created successfully.';
        }
        $this->add_access_role_log($data, $msg, $data['id'], $adminId);
        
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * fetching existing access role objects
     */
    public function get_access_role_object_ids($access_role_id) {
        $this->db->select(["aro.id"]);
        $this->db->from('tbl_access_role_object as aro');
        $this->db->join('tbl_access_role as ar', 'ar.id = aro.access_role_id', 'inner');
        $this->db->where('ar.archive', 0);
        $this->db->where('aro.archive', 0);
        $this->db->where('aro.access_role_id', $access_role_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $ids[] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * getting list of all modules stored in tbl_role table
     */
    public function get_module_names() {
        $this->db->select(["r.id as value", "r.name as label"]);
        $this->db->from('tbl_role as r');
        $this->db->where('r.archive', '0');
        $role_query = $this->db->get();
        $rols_data = $role_query->result_array();
        return $rols_data;
    }

    /*
     * To fetch the access roles list
     */
    public function get_access_roles_list($data, $filter_condition = '') {
        if (empty($data)) {
            $response = array('status' => false, 'error' => "Missing data");
            return $response;
        }
        $limit = $data->pageSize?? 20;
        $page = $data->page?? 1;
        $sorted = $data->sorted?? [];
        $filter = $data->filtered?? null;
        $orderBy = '';
        $direction = '';
        # Searching column
        $src_columns = array("ar.name", "DATE_FORMAT(ar.created,'%d/%m/%Y')", "concat(m.firstname,' ',m.lastname)");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        # sorting part
        $available_column = ["id", "name", "created", "created_by", "created_by_label","member_id"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ar.id';
            $direction = 'DESC';
        }

        $select_column = ["ar.id", "ar.name", "ar.created", "ar.created_by", "concat(m.firstname,' ',m.lastname) as created_by_label","m.id as member_id"];
        
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            
        $this->db->from('tbl_access_role as ar');
        $this->db->join('tbl_member m', 'm.id = ar.created_by', 'inner');
        $this->db->where("ar.archive", "0");
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; # $ci->db->last_query();
        // Get total rows count
        $total_item =$dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        // Get the query result
        $result = $query->result();
        $return = array('total_item' => $total_item,'count' => count($result), 'data' => $result, 'status' => true, 'msg' => 'Fetched access roles list successfully',"last_query" => $last_query);
        return $return;
    }


    /**
     * archiving access role
     */
    function archive_access_role($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the access role exist?
        $result = $this->get_access_role_objects_permissions(["data" => $data['id']]);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Access role does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("access_role", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived access role";
        $this->add_access_role_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * used by add/edit/delete access role
     */
    public function add_access_role_log($data, $title, $access_role_id, $adminId) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($access_role_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    function get_alloted_to_admin_roles($adminId)
    {
        $this->db->select(array(
            'ar.id',
            'r.id as roleId'
        ));
        $this->db->from('tbl_admin_role as ar');
        $this->db->join('tbl_role as r', 'ar.roleId = r.id', 'inner');
        $this->db->where('ar.adminId', $adminId);
        $role_query = $this->db->get();
        $rols_data = $role_query->result_array();
        return $rols_data;
    }

    // Logs can be added when user give access to plan management
    function insert_audit_logs($auditlogs)
    {
        $return = array();
        if (!empty($auditlogs)) {
            $return = $this->db->insert('tbl_plan_management_audit_logs', $auditlogs);
        }
        return $return;
    }


    function sent_org_portal_login_access($reqData, $adminId) {
        $response = [];
        if (empty($reqData)) {
            $response['status'] = false;
            $response['error'] = "Invalid request";
            return $response;
        }    

       
        $result = $this->check_contact_already_exists($reqData['person_id']);

        $this->load->model('../../sales/models/Contact_model');
        $contact = $this->Contact_model->get_contact_details($reqData['person_id']);

        $uuid = $this->check_contact_already_exists_users($reqData['person_id']);
        
        if ($result['status']) {            

            if (!empty($contact)) {
                if (empty($contact['EmailInput'][0]->email)) {
                    $response['status'] = false;
                    $response['error'] = 'No email found';
                    return $response;
                }

                $contact_mail = $contact['EmailInput'][0]->email;

                $rand = mt_rand(10, 100000);
                $token = encrypt_decrypt('encrypt', $rand);

                if ($uuid['status']) {
                    $ins_data = array('username'=> $contact_mail, 'user_type'=> 3 , 'password_token' => $token, 'created_by'=> $adminId);
                    $ids = $this->basic_model->insert_records('users', $ins_data);

                    $data = array('uuid' => $ids);
                    $where = array('id' => $reqData['person_id']);
                    $rows = $this->basic_model->update_records('person', $data, $where);

                    

                } else {
                    $ids = $uuid['data'][0]->uuid;
                    $data = array('password_token' => $token);
                    $where = array('id' => $uuid['data'][0]->uuid);
                    $rows = $this->basic_model->update_records('users', $data, $where);
                }  

                

                $this->load->model('../../recruitment/models/RecruitmentAppliedForJob_model');
                $this->RecruitmentAppliedForJob_model->send_new_applicant_login_email([

                   'firstname' => $contact['firstname'],
                   'lastname' => $contact['lastname']."<br><br>",
                   'email' => $contact_mail,
                   'userId' => $reqData['person_id'],
                   'admin_id' => $adminId,

                   'function_content' => "<br><br><a href=" .  $this->config->item('org_webapp_url')."/"  . "reset_password/" . encrypt_decrypt('encrypt',  $ids) . '/' . $token . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME)) . '/' . encrypt_decrypt('encrypt', 'organisation') . "
                    style='color: #0000ff;width: auto;padding: 0;text-align: center;'>Link</a>"

                ]);

                if ($uuid['status']) {
                    $this->org_users_updateRole($ids);
                }

                $response['status'] = true;
                $response['msg'] = "Successfully sent the login details to ".$contact_mail;
                return $response;
            } else {
                $response['status'] = false;
                $response['error'] = 'No email found';
                return $response;
            }
        } else {
            $response['status'] = false;
            $response['error'] = "User can't added to org portal";
            return $response;
        }
    }


    function check_contact_already_exists($person_id)
    {
        if (!empty($person_id)) {
            $where["person_id"] = $person_id;
            $column = array('*');
            $result = $this->basic_model->get_record_where('member', $column, $where);

            if (empty($result)) {
                return ["status"=> true];
            } else {
                return ["status"=> false, "data"=> $result];
            }
        }
    }

    function check_contact_already_exists_users($person_id) {
        if (!empty($person_id)) {
            $where = array('id' => $person_id);
            $column = array('uuid');
            $result = $this->basic_model->get_record_where('person', $column, $where);

            if (empty($result[0]->uuid)) {
                return ["status"=> true];
            } else {
                return ["status"=> false, "data"=> $result];
            }
        }
    }

    function verify_token($reqData, $req_from) {
        $CI = & get_instance();

        $user_id = encrypt_decrypt('decrypt', $reqData->id);

        if ($req_from === 'organisation') {
            $where = array('id' => $user_id, 'password_token' => $reqData->password_token);
            $result = $CI->basic_model->get_record_where('users', array('id', 'username','user_type'), $where);
        }

        return $result;
    }

    function set_password($reqData, $req_from) {
        $CI = & get_instance();
        $encry_password = password_hash($reqData->password, PASSWORD_BCRYPT);
        
        if ($req_from === 'organisation') {
            $passwordUpdateData = array('password_token' => '', 'password' => $encry_password, 'status' => 1);
            $user_id = encrypt_decrypt('decrypt', $reqData->id);
             //empty the password token  and update password       
            $result = $CI->basic_model->update_records('users', $passwordUpdateData, $where = array('id' => $user_id));

            // $statuschange = array('status' => 1);
            // $where = array('uuid' => $user_id);
            // $result = $CI->basic_model->update_records('member', $statuschange, $where);
        }

        return $result;
    }


    public function get_member_details_by_uuids($obj) {       
        
        /**
         * short_code = internal_staff mean only internal department user can login
         *  user_type = 1 - admin , 2 - member , 3 - organisation
         * 
         * */ 
        $uuid_user_type = $obj->getUuid_user_type();
       
        if($uuid_user_type == ORGANISATION_PORTAL) {
            $column = array(
                'u.id',
                'u.username',
                'u.password',
                'p.firstname',
                'p.lastname',
                'p.id as person_id',
                'u.status'
            );
            $where = array(
                'u.id' => $obj->getUuid(),
                'u.archive' => 0,
                'u.status'  => 1             
            );

            $this->db->select($column);
            $this->db->from('tbl_users as u');
            $this->db->join('tbl_person as p', 'p.uuid=u.id', 'inner');
            $this->db->where($where);
            $query = $this->db->get();
    
            return $query->row();
        }
        
       
    }

    function check_email($email, $user_type) {

         // where condition applicable for all user type
         $where = array(
            'u.username' => $email,
            'u.user_type' => $user_type,
            'u.archive' => 0,
            'u.status' => 1,
        );
        if($user_type==ORGANISATION_PORTAL){
            $column = array(
                'u.username',
                'u.user_type',
                'u.archive',
                'p.firstname',
                'p.lastname',
                'u.status',
                'u.id'
                );
           
        }else if($user_type==MEMBER_PORTAL){
            $column = array(
                'm.id',
                'm.firstname',
                'm.lastname',
                'm.status',
                'm.archive'
                );
        }else{
            $column = array(
                'm.id',
                'm.firstname',
                'm.lastname',
                'm.status'
                );
        }
        // using query check username
       
         $this->db->select($column);
         $this->db->from('tbl_users as u');
         $this->db->join('tbl_person as p', 'u.id = p.uuid', 'inner');
         $this->db->where($where);
         $query = $this->db->get();
         return $query->row();
     }

    function org_users_updateRole($adminId) {
        $CI = &get_instance();
                
        $list_roles = $CI->basic_model->get_record_where('role', ['id', 'role_key'], '');
        $list_roles = array_column($list_roles, 'role_key', 'id');
                 
        foreach($list_roles as $key => $val) {
            $temp_roles[] = array('roleId' => $key, 'adminId' => $adminId);
        }
                   
        $CI->basic_model->insert_records('admin_role', $temp_roles, $multiple = true);

        // adding access crm

        $crm_data = ['admin_id' => $adminId, 'archive' => 0, 'status' => 1, 'approval_permission' => 1, 'its_crm_admin' => 1]; 
        $CI->basic_model->insert_records('crm_staff', $crm_data);


        $recruiter_data = ['adminId' => $adminId, 'archive' => 0, 'approval_permission' => 1, 'its_recruitment_admin' => 1, 'status' => 1, 'created' => DATE_TIME];
        $CI->basic_model->insert_records('recruitment_staff', $recruiter_data);

        $finance_data = ['adminId' => $adminId, 'status' => 1, 'archive' => 0, 'approval_permission' => 0];
        $CI->basic_model->insert_records('finance_staff', $finance_data);
    }
}
