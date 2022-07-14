<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller recruitment form and related models of it
 */
class RecruitmentForm_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load
        $this->CI->load->model('../modules/recruitment/models/Recruitmentform_model');
        $this->Recruitmentform_model = $this->CI->Recruitmentform_model;
        $this->CI->load->library('form_validation');
    }

    /**
     * Test success case - list of all form template
     */
    public function test_get_question_form_template() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "search": "test"
        }';
        $reqData = json_decode($reqData, true);
        $data = [];

        $data = json_decode(json_encode($reqData), true);
        $result = $this->Recruitmentform_model->get_question_form_template($data);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, $data);
    }

    /**
     * Test Failure case - list of all form template
     */
    public function test_get_question_form_template_case_() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "search": ""
        }';
        $reqData = json_decode($reqData, true);
        $data = [];

        $data = json_decode(json_encode($reqData), true);
        $result = $this->Recruitmentform_model->get_question_form_template($data);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, $data);
    }

    /**
     * Test Success case - create form
     */
    public function test_create_form_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {
                    "title":"test",
                    "owner":"19",
                    "related_to":"93",
                    "form_id":"16",
                    "applicant_id":"83",
                    "application_id":"93",
                    "form_start_date": "2021-03-16",
                    "form_start_time": "12:00 AM",
                    "form_end_date": "2021-03-16",
                    "form_end_time": "11:30 PM",
                    "interview_type_id":4,
                    "referred_by":1
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            # appending timings into date fields
            $data['form_start_datetime'] = '';
            $data['form_end_datetime'] = '';

            if ($data['form_start_date'] != '') {
                $data['form_start_datetime'] = $data['form_start_date']." ".$data['form_start_time'];
            }
            if ($data['form_end_date'] != '') {
                $data['form_end_datetime'] = $data['form_end_date']." ".$data['form_end_time']; 
            }

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];

            # date time msg validation
            if ($data['form_start_date'] != '' && $data['form_start_time'] == '') {
                 $validation_rules[] = array(
                    'field' => 'form_start_time', 'label' => 'Form start time', 'rules' => 'required'
                );
            }

            if ($data['form_end_date'] != '' && $data['form_end_time'] == '') {
                 $validation_rules[] = array(
                    'field' => 'form_end_time', 'label' => 'Form end time', 'rules' => 'required'
                );
            }

            if ($data['form_start_datetime'] != '' && $data['form_start_date'] != '' && $data['form_start_time'] != '') {
                $validation_rules[] = array(
                    'field' => 'form_start_datetime', 'label' => 'Form start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect form start date & time',
                    ]
                );
            }

            if ($data['form_end_datetime'] != '' && $data['form_end_date'] != '' && $data['form_end_time'] != '') {
                $validation_rules[] = array(
                    'field' => 'form_end_datetime', 'label' => 'Form end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                    'errors' => [
                        'valid_date_format' => 'Incorrect form start date & time',
                    ]
                );
            }
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create form model
                $form = $this->Recruitmentform_model->save_form($data, $adminId);
                // According to that form will be created
                if ($form['status'] == true) {
                    $data = array('form_id' => $form['form_id']);
                    $response = ['status' => true, 'msg' => 'Form has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $form['error']];
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
     * Test Failure case - create form
     */
    public function test_create_form_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":
                {
                    "title":"test",
                    "owner":"",
                    "related_to":"93",
                    "form_id":"16",
                    "applicant_id":"83",
                    "application_id":"93"
                }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData->data;

            // Validation rules set
            $validation_rules = [
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
                array('field' => 'form_id', 'label' => 'Form Template', 'rules' => 'required', "errors" => [ "required" => "Form Template is required" ]),
                array('field' => 'related_to', 'label' => 'Related To', 'rules' => 'required', "errors" => [ "required" => "Related to application is required" ]),
                array('field' => 'owner', 'label' => 'Owner', 'rules' => 'required'),
                array('field' => 'application_id', 'label' => 'Application Id', 'rules' => 'required', "errors" => [ "required" => "Missing Application Id" ]),
                array('field' => 'applicant_id', 'label' => 'Applicant Id', 'rules' => 'required', "errors" => [ "required" => "Missing Applicant Id" ]),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create form model
                $form = $this->Recruitmentform_model->save_form($data, $adminId);
                // According to that form will be created
                if ($form['status'] == true) {
                    $data = array('form_id' => $form['form_id']);
                    $response = ['status' => true, 'msg' => 'Form has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $form['error']];
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
     * Update Applicant form status
     */
    public function test_update_form_status() {
        $this->CI->form_validation->reset_validation();
        $orderby = "id";
        $direction = "desc";
        $q = $this->CI->db->query("SELECT `rfa`.*, CONCAT('',rfa.application_id) as application, `raaa`.`referred_by`, `raaa`.`referred_phone`, `raaa`.`referred_email`, `rf`.`interview_type`, (SELECT CONCAT_WS(' ', `sub_m`.`firstname`, sub_m.lastname)
        FROM `tbl_member` as `sub_m`
        WHERE sub_m.uuid = rfa.created_by
         LIMIT 1) as created_by, (SELECT CONCAT_WS(' ', `sub_m`.`firstname`, sub_m.lastname)
        FROM `tbl_member` as `sub_m`
        WHERE sub_m.uuid = rfa.updated_by
         LIMIT 1) as updated_by, (SELECT CONCAT_WS(' ', `sub_m`.`firstname`, sub_m.lastname)
        FROM `tbl_member` as `sub_m`
        WHERE sub_m.uuid = rfa.owner
         LIMIT 1) as owner_name
        FROM `tbl_recruitment_form_applicant` as `rfa`
        INNER JOIN `tbl_recruitment_form` as `rf` ON `rf`.`id` = `rfa`.`form_id`
        INNER JOIN `tbl_recruitment_applicant_applied_application` as `raaa` ON `raaa`.`id` = `rfa`.`application_id` AND `raaa`.`applicant_id` = `rfa`.`applicant_id` limit 1");
        $record = $q->row();
        //$record = $this->CI->load->Basic_model->get_row_where_orderby('recruitment_form_applicant', array('id'), ['status'=>2, 'archive' => 0], $orderby, $direction);
        $form_id = 1;
        if (!empty($record)) {
            $form_id = $record->id;
        }

        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "form_id":"'.$form_id.'",
                "status":2
            }
        }';

        $reqData = (object) json_decode($reqData, true);
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ]),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required', "errors" => [ "required" => "Missing Status" ]),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->update_form_status((object) $reqData->data, $adminId);
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
     * Update Applicant form status
     */
    public function test_update_form_status_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "form_id":"",
                "status":2
            }
        }';

        $reqData = (object) json_decode($reqData, true);

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            // Validation rules set
            $validation_rules = [
                array('field' => 'form_id', 'label' => 'Form Id', 'rules' => 'required', "errors" => [ "required" => "Missing Form Id" ]),
                array('field' => 'status', 'label' => 'Status', 'rules' => 'required', "errors" => [ "required" => "Missing Status" ]),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call model for get form list
                $result = $this->Recruitmentform_model->update_form_status((object) $reqData->data, $adminId);
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
}