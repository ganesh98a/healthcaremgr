<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/**
 * Class to test the controller RecruitmentApplicant and related models of it
 */
class RecruitmentReference_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/Recruitment_applicant_model');
        $this->CI->load->model('../modules/recruitment/models/Recruitment_reference_data_model');
        $this->CI->load->library('form_validation');
        $this->Recruitment_applicant_model = $this->CI->Recruitment_applicant_model;
        $this->reference_model = $this->CI->Recruitment_reference_data_model;
    }

    /*
     * checking the reapplying of the job, application should not be submitted
     */
    function test_get_referece_list_by_id() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "applicant_id":"195","application_id":"560","pageSize":4,"page":0,"sorted":"","filtered":""
        }';

        $data = (object) json_decode($reqData, true);
        $result = $this->reference_model->get_applicant_reference($data);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, $result);
    }

    /**
     * Test Success case - create reference
     */
    public function test_create_reference_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {   
            "adminId": 20,
            "data":{"id":"43","full_name":"Test 1","email":"testone@yopmail.com","phone":"95656324578","status":"1","notes":"Test values","written_reference_check":true,"applicant_id":"195"}
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            // Validation rules set
            $validation_rules = array(
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Applicant Id is missing']),
                array('field' => 'phone', 'label' => 'Phone', 'rules' => 'required'),
                array('field' => 'email', 'label' => 'Email', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            );
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');
                $data =(object) $reqData->data;
                
                $applicant_id = $data->applicant_id;

                $list = $this->reference_model->create_update_reference($data, $reqData->adminId);

                $id = $data->id ?? 0;
                if ($id != '' && $id != 0) {
                    $msg = 'updated';
                } else {
                    $msg = 'created';
                }
                $response = ['status' => true, 'msg' => "Reference ".$msg." successfully" ];
            } else {
                // If requested data isn't valid
                $errors = $this->CI->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $response = ['status' => false, 'error' => 'Requested data is null'];
        }

        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $response['msg'];
        } else {
            $status_msg = $response['error'];
        }
        
        // AssertsEquals false with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /**
     * Mark reference as archived.
     */
    public function test_archive_reference() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {   
            "adminId": 20,
            "data":{"reference_id":"1"}
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData->data)) {
            $data = $reqData->data;

            $validation_rules = array(
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required', 'errors'=> [ 'required' => 'Reference Id is missing'] ),
            );

            $this->CI->form_validation->set_data((array) $data);
            $this->CI->form_validation->set_rules($validation_rules);

            if ($this->CI->form_validation->run()) {
                $data =(object) $data;
                $reference_id = $data->reference_id;
                $list = $this->reference_model->archive_reference($reference_id, $reqData->adminId);
                $response = ['status' => true, 'msg' => "Reference archived successfully"];
            } else {
                $errors = $this->CI->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
            
        } else {
            $response = ['status' => false, 'error' => "Requested data is null"];
        }
         $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $response['msg'];
        } else {
            $status_msg = $response['error'];
        }
        
        // AssertsEquals false with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}