<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Participant and related models of it
 */
class Participant_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/item/models/Participant_model');
        $this->CI->load->model('../modules/document/models/Document_attachment_model');
        // load amazon s3 library
        $this->CI->load->library('AmazonS3');
        $this->CI->load->library('form_validation');
        $this->Participant_model = $this->CI->Participant_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /*
     * checking the mandatory participant id while adding a new participant member
     */
    function test_create_update_participant_member_val1() {
        $postdata =[
            "participant_id" => null,
            "participant_members" => [
                ["member_obj" => ["value" => 2],
                "status" => 382]
            ],
        ];
        $output = $this->Participant_model->assign_participant_members($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the mandatory member while adding a new participant member
     */
    function test_create_update_participant_member_val2() {
        $postdata =[
            "participant_id" => 1,
            "participant_members" => [
                ["member_obj" => ["value" => null],
                "status" => 382]
            ],
        ];
        $output = $this->Participant_model->assign_participant_members($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking if any member is provided then status must be provided
     */
    function test_create_update_participant_member_val3() {
        $postdata =[
            "participant_id" => 1,
            "participant_members" => [
                ["member_obj" => ["value" => 2],
                "status" => null]
            ],
        ];
        $output = $this->Participant_model->assign_participant_members($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the successful insertion of participant member
     */
    function test_create_update_participant_member_insert() {
        $postdata =[
            "participant_id" => 1,
            "participant_members" => [
                ["member_obj" => ["value" => 2],
                "status" => 382]
            ],
        ];
        $output = $this->Participant_model->assign_participant_members($postdata, 1);
        
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful updating of participant member
     */
    function test_create_update_participant_member_update() {
        $postdata =[
            "participant_id" => 1,
            "participant_members" => [
                ["member_obj" => ["value" => 2],
                "status" => 382],
                ["member_obj" => ["value" => 13],
                "status" => 383]
            ],
        ];
        $output = $this->Participant_model->assign_participant_members($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful archiving of participant member
     */
    function test_archive_participant_member() {
        $postdata = null;
        $details = $this->basic_model->get_row('participant_member', array("MAX(id) AS lastid"));
        if($details->lastid)
        $postdata['id'] = $details->lastid;

        $output = $this->Participant_model->archive_participant_member($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * Create participant test successful
     */
    function test_create_participant_case1() {
        $adminId = 20;
        $postdata =[
            "contact_id" => 1,
            "role_id" => 1,
            "name" => "test role one",
            "active" => 1
        ];
        $output = $this->Participant_model->create_participant($postdata, $adminId);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        return $this->assertGreaterThanOrEqual(0, $output);
    }

    /*
     * Update participant test successful
     */
    function test_update_participant_case1() {
        $adminId = 20;
        $postdata =[
            "participant_id" => 1,
            "contact_id" => 1,
            "role_id" => 1,
            "name" => "test role one",
            "active" => 1
        ];
        $output = $this->Participant_model->update_participant($postdata, $adminId);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        return $this->assertGreaterThanOrEqual(0, $output);
    }

    /*
     * Update participant test - participant id mandatory successful
     */
    function test_update_participant_case2() {
        $adminId = 20;
        $postdata =[
            "participant_id" => 0,
            "contact_id" => 1,
            "role_id" => 1,
            "name" => "test role one",
            "active" => 1
        ];
        $output = $this->Participant_model->update_participant($postdata, $adminId);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        return $this->assertEquals('', $output);
    }

    /*
     * To participant document test positive case 
     * Using - assertEquals
     */
    function test_create_document_by_user_page_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        $participant_id = '47';
        // Set data in libray for validation
        $reqData = '
        {
            "doc_type_id": "5",
            "participant_id": "'.$participant_id.'",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true,
            "attachments": {"name":["index.PNG"],"type":["image/png"],"tmp_name":["/tmp/phpE87A.tmp"],"error":[0],"size":[15189]},
            "user_page":"participants"
        }';

        $reqData = json_decode($reqData, true);
        // set $_FILES 
        $_FILES = array(
            "attachments" => array(
                "name" => array(0 => "index.PNG"),
                "type" => array(0 => "image/png"),
                "tmp_name" => array(0 => "/tmp/phpE87A.tmp"),
                "error" => array(0 => "0"),
                "size" => array(0 => 15189),
            )            
        );

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            // validate id based on user page
            if($data['user_page']=='member'){
                $validation_rules[] = array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]);
            }
            if($data['user_page']=='participants'){
                $validation_rules[] = array('field' => 'participant_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing participant id" ]);
            }

            /**
              * Dynamic validation fields related with document type
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && isset($data['issue_date']) && ($data['issue_date'] == '' || $data['issue_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && isset($data['expiry_date']) && ($data['expiry_date'] == '' || $data['expiry_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && isset($data['reference_number']) && ($data['reference_number'] == '' || $data['reference_number'] == null)) {
                $validation_rules[] = array(
                    'field' => 'reference_number', 'label' => 'Reference Number', 'rules' => 'required'
                );
            }

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create document model
                $document = $this->CI->Document_attachment_model->save_document_attachment($data, $adminId, true, $data['user_page']);
                // According to that document will be created
                if ($document['status'] == true) {
                    $data = array('document_id' => $document['document_id']);
                    $response = ['status' => true, 'msg' => 'Document has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $document['error']];
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
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To participant document test failure case
     * Using - assertEquals
     */
    function test_create_document_by_user_page_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "doc_type_id": "5",
            "participant_id": "",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true,
            "attachments": {"name":["index.PNG"],"type":["image/png"],"tmp_name":["/tmp/phpE87A.tmp"],"error":[0],"size":[15189]},
            "user_page": "participants"
        }';

        $reqData = json_decode($reqData, true);
        // set $_FILES 
        $_FILES = array(
            "attachments" => array(
                "name" => array(0 => "index.PNG"),
                "type" => array(0 => "image/png"),
                "tmp_name" => array(0 => "/tmp/phpE87A.tmp"),
                "error" => array(0 => "0"),
                "size" => array(0 => 15189),
            )            
        );
        
        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'doc_type_id', 'label' => 'Document Type', 'rules' => 'required'),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

            // validate id based on user page
            if($data['user_page']=='member'){
                $validation_rules[] = array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]);
            }
            if($data['user_page']=='participants'){
                $validation_rules[] = array('field' => 'participant_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing participant id" ]);
            }

            /**
              * Dynamic validation fields related with document type
              * - Issue Date
              * - Expiry Date
              * - Reference Number
              */
            $issue_date_mandatory = filter_var($data['issue_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($issue_date_mandatory == true && isset($data['issue_date']) && ($data['issue_date'] == '' || $data['issue_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'issue_date', 'label' => 'Issue Date', 'rules' => 'required'
                );
            }

            $expiry_date_mandatory = filter_var($data['expiry_date_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($expiry_date_mandatory == true && isset($data['expiry_date']) && ($data['expiry_date'] == '' || $data['expiry_date'] == null)) {
                $validation_rules[] = array(
                    'field' => 'expiry_date', 'label' => 'Expiry Date', 'rules' => 'required'
                );
            }

            $reference_number_mandatory = filter_var($data['reference_number_mandatory'], FILTER_VALIDATE_BOOLEAN);
            if ($reference_number_mandatory == true && isset($data['reference_number']) && ($data['reference_number'] == '' || $data['reference_number'] == null)) {
                $validation_rules[] = array(
                    'field' => 'reference_number', 'label' => 'Reference Number', 'rules' => 'required'
                );
            }

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Call create document model
                $document = $this->CI->Document_attachment_model->save_document_attachment($data, $adminId, true);
                // According to that document will be created
                if ($document['status'] == true) {
                    $data = array('document_id' => $document['document_id']);
                    $response = ['status' => true, 'msg' => 'Document has been created successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => $document['error']];
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

    /*
     * Create participant test & update service agreement successful
     */
    function test_create_participant_add_sa_case() {
        $adminId = 20;
        $postdata =[
            "contact_id" => 1,
            "role_id" => 1,
            "name" => "test role ones",
            "active" => 1,
            "service_agreement_id" => 1
        ];
        $output = $this->Participant_model->create_participant($postdata, $adminId);
        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        return $this->assertGreaterThanOrEqual(0, $output);
    }
}
