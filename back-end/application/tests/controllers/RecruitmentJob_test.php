<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/**
 * Class to test the controller RecruitmentApplicant and related models of it
 */
class RecruitmentJob_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/Recruitment_jobs_model');
        $this->CI->load->library('form_validation');
        $this->Recruitment_jobs_model = $this->CI->Recruitment_jobs_model;
        $this->form_validation = $this->CI->form_validation;
        $this->CI->load->model('Basic_model');
    }

    /**
     * Get all active job application
     * return - json format
     */
    public function test_get_job_application() {
        $postdata = new stdClass();
        $row = $this->CI->Basic_model->get_row('recruitment_applicant_applied_application as raaa', array('raaa.*'), ['archive' => 0]);
        $postdata->applicant_id = $row->applicant_id;
        $results = $this->Recruitment_jobs_model->job_options($postdata);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, $results);
    }

    /*
     * check successful - Transfer Application
     */
    public function test_transfer_application() {
        $reqData = '{
            "data": {
                "applicant_id":"188",
                "application_id":"398",
                "selected_job": {
                    "label":"IndCabRegTest",
                    "value":"93"
                }
            }
        }';
        $reqData = json_decode($reqData);
        $data = $reqData->data;
        $adminId = 20;
        if (!empty($data)) {
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant id', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application id', 'rules' => 'required'),
            );

            $data_array = (array) $data;
            $this->form_validation->set_data($data_array);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $result = $this->Recruitment_jobs_model->transfer_application($data, $adminId);
                $return = array('status' => true, 'data' => $result, 'msg'=> 'Job Reassigned Successfully');
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } else {
            $return = array('status' => false, 'error' => 'Request data is null');
        }
        $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }

        // AssertsEquals false with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}