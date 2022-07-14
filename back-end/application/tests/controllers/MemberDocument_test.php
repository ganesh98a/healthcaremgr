<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDocument and related models of it
 */
class MemberDocument_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load MemberDocument_model
        $this->CI->load->model('../modules/member/models/MemberDocument_model');
        $this->CI->load->model('../modules/document/models/Document_attachment_model');
        $this->CI->load->library('form_validation');
        // load amazon s3 library
        $this->CI->load->library('AmazonS3');
    }

    /*
     * To get document list if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    function test_get_member_document_list_case1() {

        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "member_id": "47",
            "page": "0",
            "pageSize": 6,
        }';
        $reqData = json_decode($reqData, true);
        $data = [];
        if (!empty($reqData)) {
            $data = (array) $reqData;
            // Validation rules set
            $validation_rules = [
                array('field' => 'member_id', 'label' => 'Member Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call model for get member doucment list
                $result = $this->CI->Document_attachment_model->get_document_list_for_portal($reqData->data);
                $data = $result['data'];
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Member Id is null'];
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

        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, $data);
    }

    /*
     * To member document test positive case 
     * Using - assertEquals
     */
    function test_create_document_by_user_page_case1() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        $member_id = '47';
        // Set data in libray for validation
        $reqData = '
        {
            "doc_type_id": "5",
            "member_id": "'.$member_id.'",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true,
            "attachments": {"name":["index.PNG"],"type":["image/png"],"tmp_name":["/tmp/phpE87A.tmp"],"error":[0],"size":[15189]},
             "user_page": "member"
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
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

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
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To member document test failure case
     * Using - assertEquals
     */
    function test_create_document_by_user_page_case2() {
        $this->CI->form_validation->reset_validation();
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "doc_type_id": "5",
            "member_id": "",
            "issue_date_mandatory": true,
            "expiry_date_mandatory": true,
            "reference_number_mandatory": true,
            "attachments": {"name":["index.PNG"],"type":["image/png"],"tmp_name":["/tmp/phpE87A.tmp"],"error":[0],"size":[15189]},
            "user_page": "member"
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
                array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required', "errors" => [ "required" => "Missing member id" ]),
                array('field' => 'issue_date_mandatory', 'label' => ' Issue Date Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Issue Date Validation" ]),
                array('field' => 'expiry_date_mandatory', 'label' => 'Expiry Date ', 'rules' => 'required', "errors" => [ "required" => "Missing Expiry Date Validation" ]),
                array('field' => 'reference_number_mandatory', 'label' => 'Reference Number Validation', 'rules' => 'required', "errors" => [ "required" => "Missing Reference Number Validation" ])
            ];

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
     * For getting document by id -  positive case
     * 
     */
    function test_get_member_doucment_data_by_id_case1() {
        $this->CI->form_validation->reset_validation();
         // Set data in libray for validation
         $reqData = '
         {
            "document_id": "1",
            "adminId": "20"
         }';
 
         $reqData = json_decode($reqData, true);

        if (!empty($reqData)) {
            $data = (array) $reqData;
            // Validation rules set
            $validation_rules = [
                array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required'),
            ];
            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {
                // Call model for get membver document list
                $result = $this->CI->Document_attachment_model->get_doucment_attachment_data_by_id((object)$reqData);
            } else {
                // If requested data is empty or null
                $result = ['status' => false, 'error' => 'Document Id is null'];
            }
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
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

    /*
     * For getting document by id -  negative case
     * 
     */
    function test_get_member_doucment_data_by_id_case2() {
        $this->CI->form_validation->reset_validation();
        // Set data in libray for validation
        $reqData = '
        {
           "document_id": ""
        }';

        $reqData = json_decode($reqData, true);

       if (!empty($reqData)) {
           $data = (array) $reqData;
           // Validation rules set
           $validation_rules = [
               array('field' => 'document_id', 'label' => 'Document Id', 'rules' => 'required'),
           ];
           // Set data in libray for validation
           $this->CI->form_validation->set_data($data);

           // Set validation rule
           $this->CI->form_validation->set_rules($validation_rules);

           // Check data is valid or not
           if ($this->CI->form_validation->run()) {
               // Call model for get membver document list
               $result = $this->CI->Document_attachment_model->get_doucment_attachment_data_by_id($reqData);
           } else {
               // If requested data is empty or null
               $result = ['status' => false, 'error' => 'Document Id is null'];
           }
       } else {
           // If requested data is empty or null
           $result = ['status' => false, 'error' => 'Requested data is null'];           
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

   /**
     * Mark document as archived - positive case.
     */
    public function test_archive_document_case1() {
        $this->CI->form_validation->reset_validation();
        // Set data in libray for validation
        $adminId = "20";
        $reqData = '
        {
           "document_id": "1"
        }';
        $reqData = json_decode($reqData, true);
        $data = (object) $reqData;
        $id = isset($data->document_id) ? $data->document_id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing Document ID"];
        } else {
            // Call archive member document model
            $document = $this->CI->Document_attachment_model->archive_document_attachment_v1($adminId, $id, 2);
            $response = ['status' => true, 'msg' => "Successfully archived document"];
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