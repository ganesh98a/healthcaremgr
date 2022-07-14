<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Class: ListViewControls_test
 * Uses : TDD - Controller
 */


class ListViewControls_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load CRM_RiskAssessment_model
        $this->CI->load->model('../modules/sales/models/List_view_controls_model');
        $this->CI->load->library('form_validation');
    }

    /*
     * To get list control view by id if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    public function testGetListViewControlsByIdPositiveCase() {
        // Request data
        $adminId = '1931';
        $reqData = '
        {
            "filter_list_id": 1,
            "related_type":1                
        }';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->get_list_view_controls_by_id($reqData->related_type,$reqData->filter_list_id,$adminId);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Requested data is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Successfully Retrived';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To get list control view by id  if equals 0 failure case     * 
     *  Using - assertEquals* 
     * Case - filter_list_id null
     */
    public function testGetListViewControlsByIdFailureCase() {
        // Request data
        $adminId = '1931';
        $filter_list_id = '';
        $reqData = '
        {
                "filter_list_id": "'.$filter_list_id.'",
                "related_type":0
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->get_list_view_controls_by_id($reqData->related_type,$reqData->filter_list_id, $adminId);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Requested data is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Success';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To get list control view by related type if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    public function testGetListViewControlsByRelatedTypePositiveCase() {
        // Request data
        $adminId = '1931';
        $reqData = '
        {
                "related_type":1
        }

        ';

        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->get_list_view_controls_by_related_type($reqData, $adminId);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null           
            $response = array('status' => false, 'error' => 'Requested list is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Success';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To get list control view by related type  if equals 0 failure case
     * 
     *  Using - assertEquals     * 
     * Case - filter_list_id null
     */
    public function testGetListViewControlsByRelatedTypeFailureCase() {
        // Request data
        $adminId = '1931';
        $related_type = '9000';
        $reqData = '
        {
                "related_type": "'.$related_type.'"
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->get_list_view_controls_by_related_type($reqData, $adminId);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Requested list data is null', 'data' => []); 
        }
        $status = $response["status"];
        // Get msg if true else error
        if (!empty($status)) {
            $status_msg = 'List view control listed succesfully';
        } else {
            $status_msg = 'Related type is required';
        }

       // AssertsEquals true with response if false show the error msg 
       $this->assertEquals(true, $status, $status_msg);
    }


     /*
     * To archive the filter by id  if equals 0 failure case
     * 
     *  Using - assertEquals     * 
     * Case - filter_list_id null
     */
    public function testArchiveFilterPositiveCase() {
        // Request data
        $adminId = '1931';
        $reqData = '
        {
                "filter_list_id": 1
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->archive_filter_list($reqData->filter_list_id);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Id not found', 'data' => []); 
        }
        $status = $response["status"];
        // Get msg if true else error
        if (!empty($status)) {
            $status_msg = 'List view control deleted succesfully';
        } else {
            $status_msg = 'Id required';
        }

       // AssertsEquals true with response if false show the error msg 
       $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To archive the filter by id  if equals 0 failure case
     * 
     *  Using - assertEquals     * 
     * Case - filter_list_id null
     */
    public function testArchiveFilterFailureCase() {
        // Request data
        $adminId = '1931';
        $filter_list_id = '';
        $reqData = '
        {
                "filter_list_id": "'.$filter_list_id.'"
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get list view control
            $result = $this->CI->List_view_controls_model->archive_filter_list($reqData->filter_list_id);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Id not found', 'data' => []); 
        }
        $status = $response["status"];
        // Get msg if true else error
        if (!empty($status)) {
            $status_msg = 'List view control deleted succesfully';
        } else {
            $status_msg = 'Id required';
        }

       // AssertsEquals true with response if false show the error msg 
       $this->assertEquals(true, $status, $status_msg);
    }
    
    /*
     * To create List view filter test positive case 
     * Using - assertEquals
     */
    public function testCreateListViewControlsPositiveCase() {
        // Get unique reference id
        $adminId = '11';
        // Set data in libray for validation
        $reqData = '
            {               
                "list_name": "testdd",
                "related_type": "1",
                "user_view_by": 1,
                "shareSettings": "false"                
            }';
        $reqData = json_decode($reqData, true);
        $reqData = (object) $reqData;
        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $insert = (object) $reqData;
           // Validation rules set           
            $validation_rules = [
                array('field' => 'user_view_by', 'label' => 'List View', 'rules' => 'required'),
            ];
            if(!$insert->shareSettings){
                $validation_rules[] = array('field' => 'list_name', 'label' => 'List Name', 'rules' => 'required');                
            }

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Check list name is exist. Using name
                $list_name = $insert->list_name;
                
                $where = array('list_name' => $list_name, 'related_type'=> $insert->related_type, 'archive'=>0);
                
                $colown = array('id', 'list_name');
                $check_filter = $this->CI->basic_model->get_record_where('list_view_controls', $colown, $where);
                // If not exist 
                if (!$check_filter) {
                  
                    // Call create list view control
                    $filterId = $this->CI->List_view_controls_model->create_update_list_view_controls($insert, $adminId);

                    // Check $filterId is not empty 
                    // According to that got list view control is created or not
                    if ($filterId) {
                        $response = ['status' => true, 'msg' => 'List view controls has been created successfully.'];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Filter name already exist  '];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->CI->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } 
        else {
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
     * To update List view filter test positive case 
     * Using - assertEquals
     */
    public function testUpdateListViewControlsPositiveCase() {
        // Get unique reference id
        $adminId = '11';
        // Set data in libray for validation
        $reqData = '
            {
                "filter_list_id": 17,
                "list_name": "test12adsfs",
                "related_type": "1",
                "user_view_by": 1 ,
                "shareSettings": false               
            }';
        $reqData = json_decode($reqData, true);
        // Clear the form validation field data
        $this->CI->form_validation->reset_validation();

        if (!empty($reqData)) {
            $data = (array) $reqData;
            $insert = (object) $reqData;
            // Validation rules set           
            $validation_rules = [
                array('field' => 'user_view_by', 'label' => 'List View', 'rules' => 'required'),
            ];
            if(!$insert->shareSettings){
                $validation_rules[] = array('field' => 'list_name', 'label' => 'List Name', 'rules' => 'required');                
            } 

            // Set data in libray for validation
            $this->CI->form_validation->set_data($data);

            // Set validation rule
            $this->CI->form_validation->set_rules($validation_rules);

            // Check data is valid or not
            if ($this->CI->form_validation->run()) {

                $this->CI->load->model('Basic_model');

                // Check list name is exist. Using name
                $list_name = $insert->list_name;
                
                $where = array('list_name' => $list_name, 'id!=' => $insert->filter_list_id, 'related_type'=> $insert->related_type,'archive'=>0);
                
                $colown = array('id', 'list_name');
                $check_filter = $this->CI->basic_model->get_record_where('list_view_controls', $colown, $where);
                // If not exist 
                if (empty($check_filter)) {
                  
                    // Call create list view control model
                    $filterId = $this->CI->List_view_controls_model->create_update_list_view_controls($insert, $adminId);

                    // Check $filterId is not empty 
                    // According to that got list view control is created or not
                    if ($filterId) {
                        $response = ['status' => true, 'msg' => 'List view controls has been updated successfully.'];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                    }
                } else {
                     $response = ['status' => false, 'error' => 'Filter name already exist  '];
                }
            } else {
                // If requested data isn't valid
                $errors = $this->CI->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } 
        else {
            // If requested data is empty or null
            print_r('insei');
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
     * To get list view controls by default pinned if greater than 0 positive case
     * Using - assertGreaterThanOrEqual
     */
    public function testGetListViewControlsByDefaultPinnedPositiveCase() {
        // Request data
        $adminId = '1931';
        $reqData = '
        {
                "related_type": 1
        }';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData)) {
            // Call model for get default pinned data
            $result = $this->CI->List_view_controls_model->check_list_has_default_pinned($reqData,$adminId);
            if($result){
                $response = array('status' => true, 'data' => $result); 
            }else{
                $response = array('status' => false, 'msg' => 'Thers no data pinned by default'); 
            }
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Data is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Successfully Retrived';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To get list view controls by default pinned  if equals 0 failure case     * 
     *  Using - assertEquals* 
     * Case - filter_list_id null
     */
    public function testGetListViewControlsByDefaultPinnedFailureCase() {
        // Request data
        $adminId = '1931';
        $related_type = '';
        $reqData = '
        {
                "related_type": "'.$related_type.'"
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get default pinned data
            $result = $this->CI->List_view_controls_model->check_list_has_default_pinned($reqData,$adminId);
            if($result){
                $response = array('status' => true, 'data' => $result); 
            }else{
                $response = array('status' => false, 'msg' => 'Thers no data pinned by default'); 
            }
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Data is null', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Success';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }
}
