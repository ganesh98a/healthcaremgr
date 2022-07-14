<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller DOcumentType and related models of it
 */
class Document_type_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        $this->CI->load->library('form_validation');
        // load document model
        $this->CI->load->model('../modules/item/models/Document_model');
        $this->CI->load->model('../modules/document/models/Document_type_model');
        $this->Document_model = $this->CI->Document_model;
        $this->Document_type_model = $this->CI->Document_type_model;
        $this->basic_model = $this->CI->basic_model;
        $this->CI->load->library('form_validation');
    }

    /*
     * Checking edit document - successful
     */
    function test_update_document_case1() {
        $reqData = '{
        	"title":"Test Doc Type 2",
        	"issue_date_mandatory":0,
        	"expire_date_mandatory":0,
        	"reference_number_mandatory":0,
        	"active":1,
        	"doc_related_to_selection":[{"id":2,"label":"Member"}],
        	"doc_related_to_selection_ids":"",
        	"mandatory":false,
        	"document_id":"75",
        	"doc_category":"",
        	"adminId": 20
        }';
        $reqData =(object) json_decode($reqData, true);
        // Get the request data
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData)) {
        	$related_to = $reqData->doc_related_to_selection;
            $data = (array) $reqData;
            // pr($reqData->doc_related_to_selection);
            $data['id'] = 0;
            $data['doc_related_to_selection'] = $related_to;
            // Validation rules set
            $validation_rules = [
                array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required', "errors" => [ "required" => "Document Id is missing"]),
                array('field' => 'title', 'label' => 'Document Name', 'rules' => 'required'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'issue_date_mandatory', 'label' => 'Issue Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Issue Date Mandatory value is null"]),
                array('field' => 'expire_date_mandatory', 'label' => 'Expire Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Expire Date Mandatory value is null"]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Mandatory', 'rules' => 'required', "errors" => [ "required" => "Reference Number Mandatory value is null"]),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                // Call create document model
                $documentId = $this->Document_model->update_document($data, $adminId);

                // Check $documentId is empty or not
                // According to that document will be created
                if ($documentId) {
                    $data = array('document_id' => $documentId);
                    $response = ['status' => true, 'msg' => 'Document has been updated successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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
     * Checking edit document - failure
     */
    function test_update_document_case2() {
        $reqData = '{
        	"title":"Test Doc Type 2",
        	"issue_date_mandatory":0,
        	"expire_date_mandatory":0,
        	"reference_number_mandatory":0,
        	"active":1,
        	"doc_related_to_selection":[{"id":2,"label":"Member"}],
        	"doc_related_to_selection_ids":"",
        	"mandatory":false,
        	"document_id":"",
        	"doc_category":"",
        	"adminId": 20
        }';
        $reqData =(object) json_decode($reqData, true);
        // Get the request data
        $adminId = $reqData->adminId;
        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        if (!empty($reqData)) {
        	$related_to = $reqData->doc_related_to_selection;
            $data = (array) $reqData;
            // pr($reqData->doc_related_to_selection);
            $data['id'] = 0;
            $data['doc_related_to_selection'] = $related_to;
            // Validation rules set
            $validation_rules = [
                array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required', "errors" => [ "required" => "Document Id is missing"]),
                array('field' => 'title', 'label' => 'Document Name', 'rules' => 'required'),
                array('field' => 'active', 'label' => 'Active', 'rules' => 'required', "errors" => [ "required" => "Active value is null"]),
                array('field' => 'issue_date_mandatory', 'label' => 'Issue Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Issue Date Mandatory value is null"]),
                array('field' => 'expire_date_mandatory', 'label' => 'Expire Date Mandatory', 'rules' => 'required', "errors" => [ "required" => "Expire Date Mandatory value is null"]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Mandatory', 'rules' => 'required', "errors" => [ "required" => "Reference Number Mandatory value is null"]),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                // Call create document model
                $documentId = $this->Document_model->update_document($data, $adminId);

                // Check $documentId is empty or not
                // According to that document will be created
                if ($documentId) {
                    $data = array('document_id' => $documentId);
                    $response = ['status' => true, 'msg' => 'Document has been updated successfully.', 'data' => $data ];
                } else {
                    $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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
        $this->assertEquals(false, $status, $status_msg);
    }
 }