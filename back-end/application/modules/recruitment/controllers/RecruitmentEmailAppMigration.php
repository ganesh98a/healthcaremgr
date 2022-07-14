<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Recruitment_applicant_email_app_migration_model $Recruitment_applicant_email_app_migration_model
 * @property-read \Recruitment_applicant_model $Recruitment_applicant_model
 * @package
 */
class RecruitmentEmailAppMigration extends MX_Controller
{
    use formCustomValidation;
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Recruitment_applicant_email_app_migration_model');
        $this->load->model('Recruitment_applicant_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('recruitment_applicant');
        $this->load->library('Asynclibrary');
        $this->load->helper('i_pad');
    }

    /**
     * when resend login details is requested from the member info page
     * setting the temp password and emailing the loging details to the member portal
     */
    function send_applicant_login() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;

        echo $this->Recruitment_applicant_email_app_migration_model->send_applicant_login($reqData, $adminId);
        return true;
    }

    /**
     * when resend login details is requested from the applicant info page
     * setting the temp password and emailing the loging details to the applicant
     */
    function send_member_login_single() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;

        echo $this->Recruitment_applicant_email_app_migration_model->send_member_login($reqData, $adminId);
        return true;
    }

// Send email for to reset the password for member portal  

function bulk_send_member_login() {
    $this->load->library('session');
    $reqestData = request_handler();
    
    
    if (!empty($reqestData->data)) {
      
        $reqData = $reqestData->data;
        $adminId = $reqestData->adminId;
        $check_is_member_have_email = $this->Recruitment_applicant_email_app_migration_model->check_is_member_have_email($reqData);          
        if(!empty($check_is_member_have_email)){
            echo json_encode(['status' => false, 'error' => "Please add the email for the member following member(s)", 'data' => $check_is_member_have_email]);
            exit();
        }else{
            $this->load->model(['Recruitment_applicant_email_app_migration_model', 'Recruitment_applicant_model']);
            
            $url = base_url()."recruitment/RecruitmentEmailAppMigration/send_bulk_app_login_email";            
            $param = array('reqData' => $reqData,'reqestData' => $reqestData, '$adminId' => $adminId );
           
             $this->asynclibrary->do_in_background($url, $param);
             $resMemberEmail = true;            

            if($resMemberEmail) {
                echo json_encode(['status' => true, 'msg' => "Successfully sent the login details"]);
            }
            else {
                echo json_encode(['status' => false, 'error' => "Error sending the email"]);
            }
            exit();
        }
    }
        
}

    function send_bulk_app_login_email() {
        $this->load->model(['Recruitment_applicant_email_app_migration_model', 'Recruitment_applicant_model']);
        $reqData = $this->input->post('reqData');
        $adminId = $this->input->post('adminId', true);

        $index = 0;
        foreach($reqData['member'] as $val) {
            $resSendEmail = $this->Recruitment_applicant_email_app_migration_model->send_member_login($val, $adminId);           
            $index ++;
        }
        exit();
    }
}

 