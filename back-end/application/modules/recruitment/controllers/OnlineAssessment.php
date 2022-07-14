<?php

defined('BASEPATH') or exit('No direct script access allowed');
class OnlineAssessment extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model(['Online_assessment_model', 'Online_assessment_script_model']);        
    }

    /**
     * when recruiter initiate online assessment
     * creating assessment link and sending email to applicants
     */
    function initiate_assessment() {
       
        $reqData = request_handler('access_recruitment');
        
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $response = $this->Online_assessment_model->initiate_assessment($reqData, $adminId);
        echo json_encode($response); 
    }

    /** Send Email Template Asyncronously */
    function send_assessment_email() {
        $this->load->model('Online_assessment_model');     
        
        $reqData = $this->input->post('reqData');
        $adminId = $this->input->post('adminId', TRUE);
        $mail_type = $this->input->post('mail_type');
       
        if($mail_type == 'send_assessment') {
            //Sending OA Assesement Email to the Applicant
            $response = $this->Online_assessment_model->create_assessment_email_data($reqData, $adminId);
        }
        echo json_encode($response); 
    }

    /**
     * fetches list of interviews of applicants
     */
    function get_online_assessments()
    {
        $reqData = request_handler('access_recruitment');
        
        if (!empty($reqData->data)) {
            $response = $this->Online_assessment_model->get_online_assessments_list($reqData->data, $reqData->adminId);
            echo json_encode($response);
        }
    }

    /**
     * fetches list of interviews of applicants
     */
    function get_assessment_templates_by_jobid()
    {
        $reqData = request_handler('access_recruitment');
        
        if(!empty($reqData)) {
            $job_id = $reqData->data->job_id ?? '';
            $response = $this->Online_assessment_model->get_assessment_templates_by_jobid($job_id,$reqData);            
            echo json_encode($response);
        }
    }

    /**
     * Send Assessment email to Recruter
     */
    function send_assessment_completion_email()
    {
        $reqData = request_handler(0, 0);
       
        if(!empty($reqData)) {
            $adminId = $reqData->adminId??'';
            $reqData = (array) $reqData;            
            //Sending Assessment completion email to Recruiter consultant
            $response = $this->Online_assessment_model->send_assessment_completion_email_to_recruiter($reqData, $adminId);            
            echo json_encode($response);
        }
    }
   
    /**
     * Get detail of template with question and answer options
     * return json
     */
    function get_oa_template_by_uid() {
        $reqData = request_handler(0, 0);
        $reqData = (object) $reqData;
        $response = $this->Online_assessment_model->get_oa_template_by_uid($reqData);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

     /**
     * Save assessment answer
     * return json
     */
    function save_assessment_answer_by_uid() {
        $reqData = request_handler(0, 0);
        $reqData = (object) $reqData;
        $response = $this->Online_assessment_model->save_assessment_answer($reqData);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * fetches existing OA status
     */
    function get_exisiting_oa_assessment_by_status()
    {
        $reqData = $appData = request_handler('access_recruitment');
        $reqData = (object) $reqData;
        $response = ['status' => false];
        if(!empty($reqData)) {
            $response = $this->Online_assessment_model->get_exisiting_oa_assessment_by_status($reqData->data,$appData);            
        }
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

     /**
     * Save assessment answer
     * return json
     */
    function  save_oa_results_from_recruiter() {
        $this->load->model('Grade_online_assessment_model');
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (object) $reqData->data;
        $response = $this->Grade_online_assessment_model->save_oa_results_from_recruiter($reqData, $adminId);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
   /**
     * To Sends auto assessment reminder email to the candidate
     * 
    */
    function assessment_reminder_mail() {
        
        $reqData = request_handler('access_recruitment');        
        $adminId = $reqData->adminId ?? 1;
       
        $response = $this->Online_assessment_script_model->assessment_reminder_email($reqData, $adminId);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Update Auto assessment status
     * 
    */
    function assessment_auto_status_update() {
        
        $reqData = request_handler('access_recruitment');        
        $adminId = $reqData->adminId ?? 1;
       
        $response = $this->Online_assessment_script_model->assessment_auto_status_update($adminId);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Print online assessment - download
     */
    function print_online_assessment() {
        $reqData = request_handler('access_recruitment');
        $adminId = (integer) $reqData->adminId ?? 1;
        $reqData = (array) $reqData->data;
        
        $response = $this->Online_assessment_model->print_online_assessment((object) $reqData, $adminId);
        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }
}
 