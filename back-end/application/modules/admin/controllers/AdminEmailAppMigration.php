<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Admin_email_app_migration_model $Admin_email_app_migration_model
 * @property-read \Recruitment_applicant_model $Recruitment_applicant_model
 * @package
 */
class AdminEmailAppMigration extends MX_Controller
{
    use formCustomValidation;
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Admin_email_app_migration_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->form_validation->CI = &$this;
        $this->load->library('Asynclibrary');
        $this->load->helper('i_pad');
    }

// Send email for to reset the password for member portal  

function bulk_send_admin_login() {
    $this->load->library('session');
    $reqestData = api_request_handler();
    $reqestData->data = $reqestData;
    if (!empty($reqestData->data)) {
      
        $reqData = $reqestData->data;
        $check_is_admin_have_email = $this->Admin_email_app_migration_model->check_is_admin_have_email($reqData); 
        if(!empty($check_is_admin_have_email)){
            echo json_encode(['status' => false, 'msg' => "Please add the email for the admin", 'data' => $check_is_admin_have_email]);
            exit();
        }else{
            $this->load->model(['Admin_email_app_migration_model']);
            
            $url = base_url()."admin/AdminEmailAppMigration/send_bulk_admin_login_email";            
            $param = array('reqData' => $reqData,'reqestData' => $reqestData );
           
             $this->asynclibrary->do_in_background($url, $param);
             echo json_encode(['status' => true, 'msg' => "Successfully sent the login details"]);
            exit();
        }
    }
        
}

    function send_bulk_admin_login_email() {
        $this->load->model(['Admin_email_app_migration_model']);
        $reqData = $this->input->post('reqData');
        $index = 0;
        foreach($reqData['admin_emails'] as $val) {
            $resSendEmail = $this->Admin_email_app_migration_model->send_admin_login($val);           
            $index ++;
        }
        exit();
    }
}

 