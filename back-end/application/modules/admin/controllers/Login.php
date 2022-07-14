<?php

use Admin\Auth;

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function index() {
        // added dummy line 1
    }

    /**
     * main login check function for HCM
     */
    public function check_login() {
        $this->load->helper('cookie');
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            echo json_encode($response);
            exit();
        }

        $data = (array) $reqData;
        $response = $this->Admin_model->check_login($data);
        echo json_encode($response);
        exit();
    }

    /**
     * when resend pin is requested for an old login account
     */
    public function resend_oldlogin_pin() {
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            echo json_encode($response);
            exit();
        }

        $data = (array) $reqData;
        $response = $this->Admin_model->resend_oldlogin_pin($data);
        echo json_encode($response);
        exit();
    }

    /**
     * when pin is submitted for an old login account
     */
    public function submit_oldlogin_pin() {
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            echo json_encode($response);
            exit();
        }

        $data = (array) $reqData;
        $response = $this->Admin_model->submit_oldlogin_pin($data);
        echo json_encode($response);
        exit();
    }
    
    public function online_assessment()
    {
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            echo json_encode($response);
            exit();
        }
        
        $data = (array) $reqData;
        $result = $this->Admin_model->get_applicant_data_by_uuid( $data );
        
        echo json_encode($result);
        exit();
    }

    /**
     * Validate applicant online assessment by dob with uuid
     */
    public function validate_dob()
    {
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            return $this->output->set_content_type('json')->set_output(json_encode($response));
        }
        
        # validate dob
        $data = (array) $reqData;
        $result = $this->Admin_model->get_birth_date_by_uuid($data);

        return $this->output->set_content_type('json')->set_output(json_encode($result));        
    }
    
    public function change_assessment_status(){
        $this->load->model('Admin_model');
        $reqData = request_handler(0, 0);
        if (empty($reqData)) {
            $response = array('status' => false, 'error' => "Empty data!");
            echo json_encode($response);
            exit();
        }
        
        $data = (array) $reqData;
        $result = $this->Admin_model->update_assessment_by_uuid( $data );
        echo json_encode($result);
        exit();
    }

    public function logout() {
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();

        // get request data
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $adminAuth->unsetAdminLogin($reqData->data);

            // make logout histry
            logout_login_history($reqData->adminId);
            $response = array('status' => true);
        } else {
            $response = array('status' => false);
        }
        echo json_encode($response);
    }

    public function request_reset_password() {
        $this->load->helper('email_template_helper');

        // get request data
        $reqData = request_handler(0, 0);

        if (!empty($reqData)) {
            $email = $reqData->email;
            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email'),
                array('type' => 'type', 'label' => 'type', 'rules' => 'required|valid_type'),
                array('uuid_user_type' => 'uuid_user_type', 'label' => 'uuid_user_type', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {

                $this->load->model('Admin_model');
                $result = $this->Admin_model->check_valid_email($email, $reqData->uuid_user_type);

                if (!empty($result)) {
                    $rand = mt_rand(10, 100000);
                    $token = encrypt_decrypt('encrypt', $rand);

                    $data = array('password_token' => $token);
                    $where = array('username' => $email, 'user_type'=>$reqData->uuid_user_type);
                    $this->basic_model->update_records('users', $data, $where);
                   
                    // set the redirection url
                    $redirect_url = 'server_url';
                    if($reqData->uuid_user_type==ORGANISATION_PORTAL){
                        $redirect_url = 'org_webapp_url';
                    }else if($reqData->uuid_user_type==MEMBER_PORTAL){
                        $redirect_url = 'member_webapp_url';
                    }
                    $url = $this->config->item($redirect_url)."/" . "reset_password/" . encrypt_decrypt('encrypt', $result->id) . '/' . $token . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME)).'/' . encrypt_decrypt('encrypt', $reqData->type).'/' . encrypt_decrypt('encrypt', $reqData->uuid_user_type);
                    if($reqData->uuid_user_type==ADMIN_PORTAL){
                        $url = $this->config->item($redirect_url). "reset_password/" . encrypt_decrypt('encrypt', $result->id) . '/' . $token . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME)).'/' . encrypt_decrypt('encrypt', $reqData->type).'/' . encrypt_decrypt('encrypt', $reqData->uuid_user_type);
                    }
                    $userdata = array(
                        'firstname' => $result->firstname,
                        'lastname' => $result->lastname,
                        'email' => $email,
                        'url' => $url,
                    );

                    forgot_password_mail($userdata, $cc_email_address = null);
                    $response = array('status' => true, 'success' => system_msgs('forgot_password_send_mail_succefully'));
                } else {
                    $response = array('status' => false, 'error' => 'Invalid email address');
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    public function is_password_strong($password) {
        if (preg_match('#[0-9]#', $password) && preg_match('#.*^(?=.{6,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#', $password)) {

            return TRUE;
        }
        $this->form_validation->set_message('is_password_strong', 'Password must contain at least one upper case, alphanumeric and one special character');
        return FALSE;
    }

    function reset_password() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);
        $adminAuth = new Admin\Auth\Auth();

        $this->form_validation->set_data((array) $reqData);
        $validation_rules = array(
            array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
            array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required|min_length[8]|max_length[25]|callback_is_password_strong'),
            array('field' => 'uuid_user_type', 'label' => 'uuid_user_type', 'rules' => 'required'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $user_id = encrypt_decrypt('decrypt', $reqData->id);
            $adminAuth->setAdminid($user_id);
            $adminAuth->setOcsToken($reqData->token);
            $adminAuth->setUuid_user_type($reqData->uuid_user_type);

            $result = $adminAuth->verify_token();

            if (!empty($result)) {
                $adminAuth->setPassword($reqData->password);
                $adminAuth->reset_password();
                $response = array('status' => true, 'success' => system_msgs('password_reset_successfully'));
            } else {
                $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
            }
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($response);
    }

    public function verify_reset_password_token() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);

        if ($reqData) {
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'dateTime', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'uuid_user_type', 'label' => 'missing something', 'rules' => 'required'),
            );
            

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $user_id = encrypt_decrypt('decrypt', $reqData->id);
                $adminAuth = new Admin\Auth\Auth();
                $adminAuth->setAdminid($user_id);
                $adminAuth->setOcsToken($reqData->token);
                $adminAuth->setUuid_user_type(encrypt_decrypt('decrypt', $reqData->uuid_user_type));
                $recieve_date_time = encrypt_decrypt('decrypt', $reqData->dateTime);
                $type = encrypt_decrypt('decrypt', $reqData->type);
                $diff = strtotime(DATE_TIME) - $recieve_date_time;

                if ($type=='forgot_password' && $diff > 3600) {
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'));
                }else if($type=='reset_password' && $diff > 86400){
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'));
                } else {
                    $result = $adminAuth->verify_token();

                    if (!empty($result)) {
                        $response = array('status' => true, 'data' => $result[0]);
                    } else {
                        $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    public function check_pin() {
        require_once APPPATH . 'Classes/admin/auth.php';
        $reqData = request_handler();

        if (!empty($reqData)) {

            $this->form_validation->set_data((array) $reqData->data);
            $validation_rules = array(
                array('field' => 'pinData', 'label' => 'pin', 'rules' => 'required'),
                array('field' => 'pinType', 'label' => 'token', 'rules' => 'required'),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $adminAuth = new Admin\Auth\Auth();

                $adminAuth->setPin($reqData->data->pinData);
                $adminAuth->setAdminid($reqData->adminId);
                $adminAuth->setPinType($reqData->data->pinType);
                $adminAuth->setToken($adminAuth->getEncodedJWT());
                $response = $adminAuth->verfiy_pin();
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    public function verify_email_update() {
        require_once APPPATH . 'Classes/admin/auth.php';
        $reqData = request_handler('', 0, 0);

        if (!empty($reqData->token)) {
            $adminAuth = new Admin\Auth\Auth();
            $token = $reqData->token;

            $adminAuth->setToken($token);
            $data = json_decode(encrypt_decrypt('decrypt', $token));

            $auth_res = '';

            if ($data->adminId) {
                $adminAuth->setAdminid($data->adminId);
                $auth_res = $adminAuth->checkAuthToken();
            }

            if (!empty($auth_res)) {
                $adminAuth->setPrimaryEmail($data->email);
                $adminAuth->UpdatePrimaryEmail($data->adminId);

                $return = array('status' => true, 'success' => 'Your email address changed');
            } else {
                $return = array('status' => false, 'error' => 'Invalid Request');
            }
        } else {
            $return = array('status' => false, 'error' => 'Invalid Request');
        }

        echo json_encode($return);
    }

    public function verify_generate_password_pin_token() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);

        if ($reqData) {
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'dateTime', 'label' => 'missing something', 'rules' => 'required'),
            );
            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $user_id = encrypt_decrypt('decrypt', $reqData->id);

                $adminAuth = new Admin\Auth\Auth();
                $adminAuth->setAdminid($user_id);
                $adminAuth->setOcsToken($reqData->token);
                $recieve_date_time = encrypt_decrypt('decrypt', $reqData->dateTime);
                $diff = strtotime(DATE_TIME) - $recieve_date_time;
                $roles_result = (array) $adminAuth->getUserBasedRoles();
                $roles = !empty($roles_result) ? array_column($roles_result, 'id') : array();
                $pin_access = false;

                foreach ($roles as $val) {
                    $pin_access = ($val == 1 || $val == 7 || $pin_access) ? true : false;
                }

                if ($diff > 3600) {
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'), 'pin_access' => $pin_access);
                } else {
                    $result = $adminAuth->verify_token();
                    if (!empty($result)) {
                        $response = array('status' => true, 'pin_access' => $pin_access ,'data' => $result[0]);
                    } else {
                        $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($response);
        }
    }

    function reset_password_pin() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);
        $adminAuth = new Admin\Auth\Auth();

        $this->form_validation->set_data((array) $reqData);
        $validation_rules = array(
            array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
            array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required|min_length[6]|max_length[25]|callback_is_password_strong'),
            array('field' => 'pin', 'label' => 'pin', 'rules' => 'callback_is_pin_required[' . json_encode(array('id' => $reqData->id, 'pin' => $reqData->pin)) . ']'),
            array('field' => 'uuid_user_type', 'label' => 'uuid_user_type', 'rules' => 'required'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $user_id = encrypt_decrypt('decrypt', $reqData->id);
            $adminAuth->setAdminid($user_id);
            $adminAuth->setOcsToken($reqData->token);
            $adminAuth->setUuid_user_type($reqData->uuid_user_type);
            $result = $adminAuth->verify_token();

            if (!empty($result)) {
                $adminAuth->setPassword($reqData->password);
                $adminAuth->setPin($reqData->pin);
                $adminAuth->reset_password();
                $adminAuth->updatePinAdmin();
                $response = array('status' => true, 'success' => system_msgs('password_pin_update_success'));
            } else {
                $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
            }
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($response);
    }

    public function is_pin_required($pin, $param) {
        $param = json_decode($param);
        $id = $param->id;
        $pin = $param->pin;
        $admin_id = encrypt_decrypt('decrypt', $id);
        require_once APPPATH . 'Classes/admin/auth.php';
        $adminAuth = new Admin\Auth\Auth();
        $adminAuth->setAdminid($admin_id);
        $roles_result = (array) $adminAuth->getUserBasedRoles();
        $roles = !empty($roles_result) ? array_column($roles_result, 'id') : array();
        $pin_access = false;

        foreach ($roles as $val) {
            $pin_access = ($val == 1 || $val == 7 || $pin_access) ? true : false;
        }

        if ($pin_access) {
            if (empty($pin)) {
                $this->form_validation->set_message('is_pin_required', 'The pin field is required.');
                return false;
            }
            if (strlen($pin) < 6) {
                $this->form_validation->set_message('is_pin_required', 'Pin should be of six digit.');
                return false;
            }
            if (!is_numeric($pin)) {
                $this->form_validation->set_message('is_pin_required', 'Only Numbers are allowed in Pin.');
                return false;
            }
        } else {
            return TRUE;
        }
    }

    public function verify_forgot_pin_token() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);

        if ($reqData) {
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'dateTime', 'label' => 'missing something', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $user_id = encrypt_decrypt('decrypt', $reqData->id);

                $adminAuth = new Admin\Auth\Auth();
                $adminAuth->setAdminid($user_id);
                $adminAuth->setOcsToken($reqData->token);
                $recieve_date_time = encrypt_decrypt('decrypt', $reqData->dateTime);
                $diff = strtotime(DATE_TIME) - $recieve_date_time;

                if ($diff > 3600) {
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'));
                } else {
                    $result = $adminAuth->verify_token('verify_pin_token');
                    if (!empty($result)) {
                        $response = array('status' => true);
                    } else {
                        $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function reset_forgot_pin_token() {
        require_once APPPATH . 'Classes/admin/auth.php';
        // get request data
        $reqData = request_handler(0, 0);

        if ($reqData) {
            $this->form_validation->set_data((array) $reqData);
            $validation_rules = array(
                array('field' => 'id', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'dateTime', 'label' => 'missing something', 'rules' => 'required'),
                array('field' => 'pin', 'label' => 'pin', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $user_id = encrypt_decrypt('decrypt', $reqData->id);

                $adminAuth = new Admin\Auth\Auth();
                $adminAuth->setAdminid($user_id);
                $adminAuth->setOcsToken($reqData->token);
                $recieve_date_time = encrypt_decrypt('decrypt', $reqData->dateTime);
                $diff = strtotime(DATE_TIME) - $recieve_date_time;

                if ($diff > 3600) {
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'));
                } else {
                    $result = $adminAuth->verify_token('verify_pin_token');
                    if (!empty($result)) {
                        $adminAuth->setPin($reqData->pin);
                        $adminAuth->updatePinAdmin();

                        // reset token of forgot pin
                        $this->basic_model->update_records('member', array('otp' => ''), $where = array('id' => $user_id));

                        $response = array('status' => true, 'success' => 'Pin reset successfully');
                    } else {
                        $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }


    function sent_org_portal_login_access() {
        $reqData = request_handler('access_recruitment'); 

        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $this->load->model('Admin_model');
        $results = $this->Admin_model->sent_org_portal_login_access($reqData, $adminId);

        if(!empty($results)){
             $result = ["status"=>true, "data"=> $results];
        }

        echo json_encode($results);
    }

    /**
     * verify token for organization portal
     */
    function verify_token() {
        $reqData = request_handler(0,0);
        $response=[];
        if ($reqData) {
            $this->form_validation->set_data((array) $reqData);
            $data = (array) $reqData;
            $req_from =  encrypt_decrypt('decrypt', $data['req_from']);

            $validation_rules = array(
                array('field' => 'password_token', 'label' => 'token', 'rules' => 'required'),
                array('field' => 'dateTime', 'label' => 'time', 'rules' => 'required'),
                array('field' => 'req_from', 'label' => 'req from', 'rules' => 'required'),
                array('field' => 'id', 'label' => 'id', 'rules' => 'required'),
            );

            
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $recieve_date_time = encrypt_decrypt('decrypt', $reqData->dateTime);
                $diff = strtotime(DATE_TIME) - $recieve_date_time;

                if($diff > 86400){
                    $response = array('status' => false, 'error' => system_msgs('link_exprire'));
                } else {
                    $this->load->model('Admin_model');
                    $result = $this->Admin_model->verify_token($reqData, $req_from);
                    if (!empty($result)) {
                        $response = array('status' => true);
                    } else {
                        $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
                    }
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);


        }

    }


    /**
     * set password for organization portal
     */
    function set_password() {
        $reqData = request_handler(0,0);

        $this->form_validation->set_data((array) $reqData);

        $data = (array) $reqData;
        $req_from =  encrypt_decrypt('decrypt', $data['req_from']);

        $validation_rules = array(
            array('field' => 'password_token', 'label' => 'token', 'rules' => 'required'),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required|min_length[8]|max_length[25]|callback_is_password_strong'),
        );

        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {
            $this->load->model('Admin_model');
            $result = $this->Admin_model->verify_token($reqData, $req_from);
            if (!empty($result)) {
                $update_pwd = $this->Admin_model->set_password($reqData, $req_from);
                $response = array('status' => true, 'success' => system_msgs('password_reset_successfully'));
            } else {
                $response = array('status' => false, 'error' => system_msgs('verfiy_password_error'));
            }
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($response);

    }


    function forgot_password() {
        $this->load->helper('email_template_helper');
        $reqData = request_handler(0,0);   

        if (!empty($reqData)) {
            $email = $reqData->email;
            $user_type = $reqData->user_type;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'email', 'label' => 'email', 'rules' => 'required|valid_email'),
                array('type' => 'type', 'label' => 'type', 'rules' => 'required|valid_type'),
                array('user_type' => 'user_type', 'label' => 'user_type', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $this->load->model('Admin_model');
                $check_mail = $this->Admin_model->check_email($email, $user_type);

                if (!empty($check_mail)) {
                    

                    if ($user_type == ORGANISATION_PORTAL) {
                        $rand = mt_rand(10, 100000);
                        $token = encrypt_decrypt('encrypt', $rand);

                        $data = array('password_token' => $token);
                        $where = array('username' => $email);
                        $rows = $this->basic_model->update_records('users', $data, $where);
                        


                        $userdata = array(
                            'firstname' => $check_mail->firstname,
                            'lastname' => $check_mail->lastname,
                            'email' => $email,
                            'url' => $this->config->item('org_webapp_url')."/"  . "reset_password/" . encrypt_decrypt('encrypt', $check_mail->id) . '/' . $token . '/' . encrypt_decrypt('encrypt', strtotime(DATE_TIME)).'/' . encrypt_decrypt('encrypt', 'organisation'),
                        );

                        forgot_password_mail($userdata, $cc_email_address = null);
                        $response = array('status' => true, 'success' => system_msgs('forgot_password_send_mail_succefully'));
                    }           

                    

                } else {
                    $response = array('status' => false, 'error' => 'Invalid email address');
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);

        }

    }

}
