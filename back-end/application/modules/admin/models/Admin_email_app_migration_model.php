<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Admin_email_app_migration_model extends Basic_Model {

    

    public function __construct() {
        $this->table_name = 'recruitment_applicant';
        $this->load->model('member/Member_model');
        // Call the CI_Model constructor
        parent::__construct();
    }
    
    function check_is_admin_have_email($reqData) { 
        $no_email_member = [];
        foreach($reqData->admin_emails as $val) {
            if (empty($val->email)) {
                return json_encode(['status' => false, 'error' => "Email is missing"]);
            }
            $where = array('username' => $val->email,"user_type"=>1, "enable_app_access" => 0,"source_type" => 0,"is_new_member" => 0,'archive' => 0);
            $member = $this->basic_model->get_row('member', ['id','fullname','username'], $where);
            if(empty($member)){
                    $no_email_member[] = $val->email;
            }
    }
        return $no_email_member;
    }

    /**
     * when resend login details is requested from the applicant info page
     * setting the temp password and emailing the loging details to the applicant
     */
    function send_admin_login($reqData) {
        if (empty($reqData['email'])) {
            $response = ['status' => false, 'error' => "email is Missing"];
            return json_encode($response);
        }
        $where = array('username' => $reqData['email'],"user_type"=>1, "enable_app_access" => 0,"source_type" => 0,"is_new_member" => 0,'archive' => 0);
        $member = $this->basic_model->get_row('member', ['id','firstname','lastname','username'], $where);      


        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();

        $objAdmin->setAdminid($member->id);
        $objAdmin->setUsername($member->username);
        $objAdmin->setFirstname($member->firstname);
        $objAdmin->setLastname($member->lastname);
        $objAdmin->setPrimaryEmail($member->username);

        // set admin access true
        $roleData = [
            'roleId' => 1,  // admin
            'adminId' => $member->id
        ];

        $this->basic_model->insert_records('admin_role', $roleData, $multiple = FALSE);

        # update member record with token for set the password

        $rand = mt_rand(10, 100000);
        $token = encrypt_decrypt('encrypt', $rand);
       
        $objAdmin->setToken($token);

        $where = array('id' => $member->id);
        $this->basic_model->update_records('member', $data = array('otp' => $token), $where);

         // send welcome to admin
         $objAdmin->send_welcome_mail();


        $response = ['status' => true, 'msg' => "Successfully sent the login details to ".$member->username];
        return json_encode($response);
    }

}
