<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller recruitment quiz and related models of it
 */
class RecruitmentQuiz_test extends TestCase {
    // Defualt contruct function
    protected $CI;
    public function setUp() {
        $this->CI = &get_instance();
        // Load
        $this->CI->load->model('../modules/recruitment/models/Recruitment_quiz_model');
        $this->Recruitment_quiz_model = $this->CI->Recruitment_quiz_model;
        $this->CI->load->library('form_validation');
        $this->CI->load->model('Basic_model');
        $this->Basic_model = $this->CI->Basic_model;
    }

    /**
     * Test Success case - create quiz
     */
    public function test_create_quiz_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {
                    "quiz_id":"",
                    "title":"test",
                    "owner":"19",
                    "related_to":"93",
                    "form_id":"16",
                    "applicant_id":"83",
                    "application_id":"93",
                    "quiz_start_date":"2021-01-19",
                    "quiz_start_time":"06:00 AM",
                    "quiz_end_date":"2021-01-26",
                    "quiz_end_time":"05:30 AM"
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['quiz_start_datetime'] = $data['quiz_start_date']." ".$data['quiz_start_time'];
            $data['quiz_end_datetime'] = $data['quiz_end_date']." ".$data['quiz_end_time'];

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
                array(
                    'field' => 'quiz_start_datetime', 'label' => 'Quiz start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz start date & time',
                    ]
                ),
                array(
                    'field' => 'quiz_end_datetime', 'label' => 'Quiz end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz end date & time',
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

                // Call create quiz model
                $quiz = $this->Recruitment_quiz_model->create_update_quiz($data, $adminId);
                // According to that quiz will be created
                if ($quiz['status'] == true) {
                    $data = array('quiz_id' => $quiz['quiz_id']);
                    $response = ['status' => true, 'msg' => 'Quiz has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $quiz['error']];
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
     * Test Failure case - create quiz
     */
    public function test_create_form_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {
                    "quiz_id":"",
                    "title":"test",
                    "owner":"",
                    "related_to":"93",
                    "form_id":"16",
                    "applicant_id":"83",
                    "application_id":"93",
                    "quiz_start_date":"2021-01-19",
                    "quiz_start_time":"06:00 AM",
                    "quiz_end_date":"2021-01-26",
                    "quiz_end_time":"05:30 AM"
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['quiz_start_datetime'] = $data['quiz_start_date']." ".$data['quiz_start_time'];
            $data['quiz_end_datetime'] = $data['quiz_end_date']." ".$data['quiz_end_time'];

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
                array(
                    'field' => 'quiz_start_datetime', 'label' => 'Quiz start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz start date & time',
                    ]
                ),
                array(
                    'field' => 'quiz_end_datetime', 'label' => 'Quiz end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect quiz end date & time',
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

                // Call create quiz model
                $quiz = $this->Recruitment_quiz_model->create_update_quiz($data, $adminId);
                // According to that form will be created
                if ($quiz['status'] == true) {
                    $data = array('quiz_id' => $quiz['quiz_id']);
                    $response = ['status' => true, 'msg' => 'Form has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $quiz['error']];
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

    /**
     * Success case
     * Update Applicant quiz status
     */
    public function test_update_quiz_status() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "id":"47",
                "quiz_task_status":2
            }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'id', 'label' => 'Quiz Id', 'rules' => 'required', "errors" => [ "required" => "Missing quiz Id" ]),
                array('field' => 'quiz_task_status', 'label' => 'Status', 'rules' => 'required', "errors" => [ "required" => "Missing Status" ]),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call update quiz status
                $result = $this->Recruitment_quiz_model->update_quiz_status($data, $adminId);
            } else {
               // If requested data isn't valid
                $errors = $this->CI->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        $status = $result['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $result['msg'];
        } else {
            $status_msg = $result['error'];
        }

        // AssertsEquals false with response if false show the error msg
        $this->assertEquals(true, $status, $status_msg);
    }

    /**
     * Failure case
     * Update Applicant quiz status
     */
    public function test_update_quiz_status_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "id":"",
                "quiz_task_status":2
            }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'id', 'label' => 'quiz Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ]),
                array('field' => 'quiz_task_status', 'label' => 'Status', 'rules' => 'required', "errors" => [ "required" => "Missing Status" ]),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitment_quiz_model->update_quiz_status($data, $adminId);
            } else {
               // If requested data isn't valid
                $errors = $this->CI->form_validation->error_array();
                $result = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => "Requested data is null"];
        }
        $status = $result['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $result['msg'];
        } else {
            $status_msg = $result['error'];
        }

        // AssertsEquals false with response if false show the error msg
        $this->assertEquals(false, $status, $status_msg);
    }

    //Successful quiz submission status
    public function test_update_quiz_applicant_status_case1() {

        $details = $this->Basic_model->get_row('recruitment_task', array("MAX(id) AS lastid"));

        $applicant_info['applicantID'] = 83;
        $applicant_info['fullname'] = 'XYZ';
        $applicant_info['appId'] = 'APPID:12345';
        $applicant_info['owner'] = NULL;

        $result = $this->Recruitment_quiz_model->update_applicant_quiz_status($details->lastid, 25, $applicant_info);

        if($result){
            $status = TRUE;
            $status_msg = 'Quiz submitted successfully';
        }
        $this->assertEquals(true, $status, $status_msg);
    }

    //Failed quiz submission status
    public function test_update_quiz_applicant_status_case2() {

        $result = $this->Recruitment_quiz_model->update_applicant_quiz_status(NULL, 25, NULL);

        if($result) {
            $status = FALSE;
            $status_msg = 'Task ID null';
        }
        $this->assertEquals(false, $status, $status_msg);
    }
}