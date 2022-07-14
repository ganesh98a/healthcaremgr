<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Class: CRM_RiskAssessment_test
 * Uses : TDD - Controller
 * RA - Risk Assessment
 */


class CRM_RiskAssessment_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load CRM_RiskAssessment_model
        $this->CI->load->model('../modules/sales/models/RiskAssessment_model');
        $this->CI->load->library('form_validation');
    }

    /*
     * For getting reference id of create risk assessment
     * return type json
     */
    function get_create_reference_id() {

        // get reference id 
        $rows = $this->CI->RiskAssessment_model->get_reference_id();

        if ($rows) {
            $previousRID = $rows[0]['reference_id'];
            // split the id as prefix and value
            $splitPos = 2;
            $prefix = substr($previousRID, 0, $splitPos);
            $value = substr($previousRID, $splitPos);
            $incValue = intVal($value) + 1;
            // Add 0 in left of value with 8 digit
            $strPadDigits = 8;
            $str = 0;
            $incValueWPad = str_pad($incValue, $strPadDigits, $str, STR_PAD_LEFT);
            // Join two variable
            $reference_id = $prefix.$incValueWPad;
        } else {
            $reference_id = 'RA00000001';
        }
       return $reference_id;
    }

    /*
     * To create risk assessment test positive case 
     * Using - assertEquals
     */
    public function testCreateRAPositiveCase() {
        // Get unique reference id
        $reference_id = $this->get_create_reference_id();
        $adminId = '1';
        // Set data in libray for validation
        $reqData = '
        {
            "reference_id": "'.$reference_id.'",
            "topic": "Test Risk Assessment - Create TDD",
            "account_type": 1,
            "account_id": 1,
            "owner_id": 1,
            "status": "1",
            "created_date": "2020-05-19T14:00:00.000Z",
            "created_by": "'.$adminId.'"
        }';
        $reqData = json_decode($reqData, true);

        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
        
        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required|max_length[15]', 'errors' => ['max_length' => "%s field cannot exceed 15 characters."]),              
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Check risk assessment is exist. Using reference_id
                $referenceId = $data['reference_id'];
                $where = array('reference_id' => $referenceId);
                $colown = array('id', 'reference_id');
                $check_risk_assessment = $this->CI->basic_model->get_record_where('crm_risk_assessment', $colown, $where);
                // If not exist 
                if (!$check_risk_assessment) {
                  
                    // Call create risk assessment model
                    $riskAssessmentId = $this->CI->RiskAssessment_model->create_risk_assessment($data, $adminId);

                    // Check $riskAssessmentId is not empty 
                    // According to that got risk assessment is created or not
                    if ($riskAssessmentId) {
                        $response = ['status' => true, 'msg' => 'Risk Assessment has been created successfully.'];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Risk Assessment already exist '];
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
     * To create risk assessment test failure case without param
     * Case - required data is null 
     * Using - assertEquals
     */
    public function testCreateRAFailureCaseWOParam() {
        // Get unique reference id
        $reference_id = "RA00000001";
        $adminId = '1931';

        // Set data in libray for validation
        $reqData = '';
        $reqData = json_decode($reqData, true);

        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();
        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required|max_length[15]', 'errors' => ['max_length' => "%s field cannot exceed 15 characters."]),              
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Check risk assessment is exist. Using reference_id
                $referenceId = $data['reference_id'];
                $where = array('reference_id' => $referenceId);
                $colown = array('id', 'reference_id');
                $check_risk_assessment = $this->CI->basic_model->get_record_where('crm_risk_assessment', $colown, $where);
                // If not exist 
                if (!$check_risk_assessment) {
                  
                    // Call create risk assessment model
                    $riskAssessmentId = $this->CI->RiskAssessment_model->create_risk_assessment($data, $adminId);

                    // Check $riskAssessmentId is not empty 
                    // According to that got risk assessment is created or not
                    if ($riskAssessmentId) {
                        $response = ['status' => true, 'msg' => 'Risk Assessment has been created successfully.'];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Risk Assessment already exist '];
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
     * To get risk assessment list if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    public function testGetRAListPositiveCase() {
        // Request data
        $reqData = '
        {
            "pageSize": 10,
            "page":0,
            "sorted":[],
            "filtered":
                {
                    "filter_status":"all"
                }
        }

        ';

        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get risk assessment list
            $response = $this->CI->RiskAssessment_model->get_risk_assessment_list($reqData);
            $data = $response['data'];
        } else {
            // If requested data is empty or null
            $data = array();
            $response = array('status' => false, 'error' => 'Requested data is null', 'data' => $data); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $response['msg'];
        } else {
            $status_msg = $response['error'];
        }

        // assertGreaterThanOrEqual 0 with data if false show the error msg - size
        $this->assertGreaterThanOrEqual(0, count($data));
    }

    /*
     * To get risk assessment list if equals 0 failure case
     * 
     * Case - value divide by 0 - total row count / 0
     * Using - assertCount
     */
    public function testGetRAListFailureCase() {
        // Request data
        $reqData = '
        {
            "pageSize": 0,
            "page":0,
            "sorted":[],
            "filtered":
                {
                    "filter_status":"all"
                }
        }

        ';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get risk assessment list
            $response = $this->CI->RiskAssessment_model->get_risk_assessment_list($reqData);
            $data = $response['data'];
        } else {
            // If requested data is empty or null
            $data = array();
            $response = array('status' => false, 'error' => 'Requested data is null', 'data' => $data); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $response['msg'];
        } else {
            $status_msg = $response['error'];
        }

        // assertCount 0 with data if false show the error msg
        $this->assertCount(0, $data);
    }

   
    /*
     * For edit risk assessment test positive case 
     * Using - assertEquals
     */
    public function testEditRAPositiveCase() {
        // Get unique reference id
        $reference_id = 'RA00000001';
        $adminId = '1931';
        $risk_assessment_id = 1;
        // Set data in libray for validation
        $reqData = '
        {
            "risk_assessment_id": "'.$risk_assessment_id.'",
            "reference_id": "'.$reference_id.'",
            "topic": "Test Risk Assessment - Edit TDD",
            "account_type": 1,
            "account_id": 1,
            "owner_id": 1,
            "status": "1"
        }';
        $reqData = json_decode($reqData, true);

        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
        
        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $data['id'] = 0;

            // Validation rules set
            $validation_rules = [
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required|max_length[15]', 'errors' => ['max_length' => "%s field cannot exceed 15 characters."]),              
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');         
              
                // Call create risk assessment model
                $riskAssessmentId = $this->CI->RiskAssessment_model->update_risk_assessment($data, $adminId);

                // According to that got risk assessment is created or not
                if ($riskAssessmentId) {
                    $response = ['status' => true, 'msg' => 'Risk Assessment has been updated successfully.'];
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
     * For edit risk assessment test failure case 
     * Using - assertEquals
     * 
     * Case - Risk Assessment id null
     */
    public function testEditRAFailureCase() {
        // Get unique reference id
        $reference_id = 'RA00000001';
        $adminId = '1931';
        $risk_assessment_id = '';
        // Set data in libray for validation
        $reqData = '
        {
            "risk_assessment_id": "'.$risk_assessment_id.'",
            "topic": "Test Risk Assessment - Edit TDD",
            "reference_id": "'.$reference_id.'",
            "account_type": 1,
            "account_id": 1,
            "owner_id": 1,
            "status": "1"
        }';
        $reqData = json_decode($reqData, true);

        //  Response initialize
        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
        
        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();

        if (!empty($reqData)) {
            $data = (array) $reqData;

            // Validation rules set
            $validation_rules = [
                array('field' => 'reference_id', 'label' => 'Reference Id', 'rules' => 'required|max_length[15]', 'errors' => ['max_length' => "%s field cannot exceed 15 characters."]),              
                array('field' => 'topic', 'label' => 'Topic', 'rules' => 'required'),
                array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
                array('field' => 'account_id', 'label' => 'Account (Person/Org) Name', 'rules' => 'required'),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required|in_list[1,2,3]','errors' => [
                    'in_list' => 'The status must be one of: Draft, Final, InActive'
                ],),
            ];

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');         
              
                // Call create risk assessment model
                $riskAssessmentId = $this->CI->RiskAssessment_model->update_risk_assessment($data, $adminId);

                // According to that got risk assessment is created or not
                if ($riskAssessmentId) {
                    $response = ['status' => true, 'msg' => 'Risk Assessment has been updated successfully.'];
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

    /*
     * Get risk assessment details with header - positive case
     * Using - assertEquals
     * 
     */
    function testRADetailHeaderPositiveCase() {
        // reqData set
        $risk_assessment_id = 15;

        $reqData = '
        {
            "risk_assessment_id": "'.$risk_assessment_id.'"
        }';

        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get risk assessment list
            $response = $this->CI->RiskAssessment_model->get_risk_assessment_detail_by_id($reqData);
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
     * Get risk assessment details with header - failure case
     * Using - assertEquals
     * 
     * Case - Risk Assessment id null
     */
    function testRADetailHeaderFailureCase() {
        // reqData set
        $risk_assessment_id = '';

        $reqData = '
        {
            "risk_assessment_id": "'.$risk_assessment_id.'"
        }';

        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get risk assessment list
            $response = $this->CI->RiskAssessment_model->get_risk_assessment_detail_by_id($reqData);
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
