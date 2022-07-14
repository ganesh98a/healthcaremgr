<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Recruitment_oa_template extends MX_Controller
{
   
    

    function __construct()
    {
        parent::__construct();
        $this->load->model('Recruitment_oa_template_model');
    }

    /**
     * when recruiter initiate online assessment
     * creating assessment link and sending email to applicants
     */
    function create_update_oa_template() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $response = $this->Recruitment_oa_template_model->create_update_oa_template($reqData, $adminId);
        echo json_encode($response); 
    }


     /**
     * when recruiter wants to read/edit the existing template
     *
     */
    function get_oa_template() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $response = $this->Recruitment_oa_template_model->get_oa_template($reqData, $adminId);
        echo json_encode($response); 
    }

     /**
     * when recruiter wants to get existing record for evaluation
     *
     */
    function retrieve_oa_template() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $assessment_id =  $reqData['assessment_id'];
        $application_id =  $reqData['application_id'];
        $response = $this->Recruitment_oa_template_model->get_assessment_result_by_id($assessment_id,$application_id,$adminId);
        echo json_encode($response);
    }

     /**
     * when recruiter wants to archive the existing template
     *
     */
    function archive_existing_template() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $response = $this->Recruitment_oa_template_model->archive_existing_template($reqData['id'], $adminId);
        echo json_encode($response); 
    }

      /**
     * when recruiter wants to inactive the existing template
     *
     */
    function change_oa_template_status() {
        $reqData = request_handler('access_recruitment');
        $adminId = $reqData->adminId;
        $reqData = (array) $reqData->data;
        $response = $this->Recruitment_oa_template_model->change_oa_template_status($reqData['id'],$reqData['status'], $adminId);
        echo json_encode($response); 
    }
}

 