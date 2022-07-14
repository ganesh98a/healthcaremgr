<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller recruitment interview and related models of it
 */
class RecruitmentInterview_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load
        $this->CI->load->model('../modules/recruitment/models/Recruitment_interview_model');
        $this->Recruitment_interview_model = $this->CI->Recruitment_interview_model;
        $this->CI->load->library('form_validation');
    }

    /**
     * Test Success case - create interview
     */
    public function test_create_interview_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {   
                    "interview_id":"",
                    "title":"test",
                    "owner":"19",
                    "location_id":"1",
                    "interview_type_id":"",
                    "interview_start_date":"2021-01-30",
                    "interview_start_time":"06:00 AM",
                    "interview_end_date":"2021-01-30",
                    "interview_end_time":"08:30 AM"
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['interview_start_datetime'] = $data['interview_start_date']." ".$data['interview_start_time'];
            $data['interview_end_datetime'] = $data['interview_end_date']." ".$data['interview_end_time'];

            // Validation rules set
            $validation_rules = [                
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array(
                    'field' => 'interview_start_datetime', 'label' => 'Interview start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect interview start date & time',
                    ]
                ),
                array(
                    'field' => 'interview_end_datetime', 'label' => 'Interview end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect interview end date & time',
                    ]
                    ),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create interview model
                $interview = $this->Recruitment_interview_model->create_update_interview($data, $adminId);
                // According to that interview will be created
                if ($interview['status'] == true) {
                    $data = array('interview_id' => $interview['interview_id']);
                    $response = ['status' => true, 'msg' => 'Interview has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $interview['error']];
                }
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
     * Test Failure case - create interview
     */
    public function test_create_form_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {
                   "interview_id":"",
                    "title":"",
                    "owner":"19",
                    "location_id":"1",
                    "interview_type_id":"",
                    "interview_start_date":"2021-01-19",
                    "interview_start_time":"06:00 AM",
                    "interview_end_date":"2021-01-26",
                    "interview_end_time":"05:30 AM"
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['interview_start_datetime'] = $data['interview_start_date']." ".$data['interview_start_time'];
            $data['interview_end_datetime'] = $data['interview_end_date']." ".$data['interview_end_time'];

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
                array(
                    'field' => 'interview_start_datetime', 'label' => 'Interview start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect interview start date & time',
                    ]
                ),
                array(
                    'field' => 'interview_end_datetime', 'label' => 'Interview end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect interview end date & time',
                    ]
                    ),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create interview model
                $interview = $this->Recruitment_interview_model->create_update_interview($data, $adminId);
                // According to that form will be created
                if ($interview['status'] == true) {
                    $data = array('interview_id' => $interview['interview_id']);
                    $response = ['status' => true, 'msg' => 'Form has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $interview['error']];
                }
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
        $this->assertEquals(false, $status, $status_msg);
    }
}