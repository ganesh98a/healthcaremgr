<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller Participant and related models of it
 */
class Feed_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Feed_model');
        $this->CI->load->model('../modules/sales/models/Opportunity_model');
        $this->CI->load->library('form_validation');
        $this->Feed_model = $this->CI->Feed_model;
        $this->Opportunity_model = $this->CI->Opportunity_model;
        $this->basic_model = $this->CI->basic_model;
        $this->form_validation = $this->CI->form_validation;
    }

    /**
     * checking feed related type
     */
    public function test_get_related_type() {
         // Get the request data
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "related": "lead"
        }';
        $reqData =(object) json_decode($reqData, true);
        
        if (empty($reqData) == false && isset($reqData->related) == true ) {
            // Call model for get related type
            $related = $reqData->related;
            $response = $this->Feed_model->get_related_type($related);
            $result = ['status' => true, 'data' => ['related_type' => $response], 'msg' => "Related Type has been fetched successfully."];
        } else {
            // If requested data is empty or null
            if (isset($reqData->related) == false || $reqData->related == '') {
                $result = ['status' => false, 'error' => 'Related type is null'];
            } else {
                $result = ['status' => false, 'error' => 'Requested data is null'];
            }
        }
        $status = $result['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $result['msg'];
        } else {
            $status_msg = $result['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /**
     * Checking post feed successfull
     */
    public function test_post_feed_case1() {
        try {
            // Clear the form validation field data
            $this->CI->form_validation->reset_validation();
            
            // Get the request data
            $adminId = '20';
            // Set data in libray for validation
            $reqData = '
            {
                "feed_title":"Test Post","source_id":"34","related_type":"2", "adminId": 20
            }';
            $reqData = json_decode($reqData, true);
            //  Response initialize
            $response = ['status' => false, 'error' => 'something_went_wrong)'];

            if (!empty($reqData)) {
                $data =(array) $reqData;
                // Validation rules set
                $validation_rules = [
                    array('field' => 'feed_title', 'label' => 'Feed Title', 'rules' => 'required|max_length[255]', 'errors' => ['max_length' => "%s field cannot exceed 255 characters."]),
                    array('field' => 'source_id', 'label' => 'Source Id', 'rules' => "required", 'errors' => ['required' => 'Source Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->CI->form_validation->set_data($data);

                // Set validation rule
                $this->CI->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->CI->form_validation->run()) {
                    // Call create feed model
                    $feedId = $this->Feed_model->save_feed($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($feedId) {
                        $data = array('feed_id' => $feedId);
                        $response = ['status' => true, 'msg' => 'Feed has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => 'something_went_wrong'];
                    }
                } else {
                    // If requested data isn't valid
                    $errors = $this->form_validation->error_array();
                    $response = ['status' => false, 'error' => implode(', ', $errors)];
                }
            } else {
                // If requested data is empty or null
                $response = ['status' => false, 'error' => 'Requested data is null'];
            }

        } catch (Exception $e) {
            $response = ['status' => false, 'error' => $e];
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

    /**
     * Checking post feed failure
     */
    public function test_post_feed_case2() {
        try {
            // Clear the form validation field data
            $this->CI->form_validation->reset_validation();
            
            // Get the request data
            $adminId = '20';
            // Set data in libray for validation
            $reqData = '
            {
                "feed_title":"","source_id":"34","related_type":"2", "adminId": 20
            }';
            $reqData = json_decode($reqData, true);
            //  Response initialize
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

            if (!empty($reqData)) {
                $data =(array) $reqData;
                // Validation rules set
                $validation_rules = [
                    array('field' => 'feed_title', 'label' => 'Feed Title', 'rules' => 'required|max_length[255]', 'errors' => ['max_length' => "%s field cannot exceed 255 characters."]),
                    array('field' => 'source_id', 'label' => 'Source Id', 'rules' => "required", 'errors' => ['required' => 'Source Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->CI->form_validation->set_data($data);

                // Set validation rule
                $this->CI->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->CI->form_validation->run()) {
                    // Call create feed model
                    $feedId = $this->Feed_model->save_feed($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($feedId) {
                        $data = array('feed_id' => $feedId);
                        $response = ['status' => true, 'msg' => 'Feed has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => 'something_went_wrong'];
                    }
                } else {
                    // If requested data isn't valid
                    $errors = $this->form_validation->error_array();
                    $response = ['status' => false, 'error' => implode(', ', $errors)];
                }
            } else {
                // If requested data is empty or null
                $response = ['status' => false, 'error' => 'Requested data is null'];
            }

        } catch (Exception $e) {
            $response = ['status' => false, 'error' => $e];
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

    /**
     * Checking postost comment successfull
     */
    public function test_post_comment_case1() {
        try {
            // Clear the form validation field data
            $this->CI->form_validation->reset_validation();

            // Get the request data
            $adminId = '20';
            // Set data in libray for validation
            $reqData = '
            {
                "feed_comment":"Test Post","source_id":"34","related_type":"2", "adminId": 20, "history_id": 20
            }';
            $reqData = json_decode($reqData, true);
            //  Response initialize
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

            if (!empty($reqData)) {
                $data =(array) $reqData;
                // Validation rules set
                $validation_rules = [
                    array('field' => 'feed_comment', 'label' => 'Comment', 'rules' => 'required|max_length[225]', 'errors' => ['max_length' => "%s field cannot exceed 225 characters."]),
                    array('field' => 'history_id', 'label' => 'History Id', 'rules' => "required", 'errors' => ['required' => 'History Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->CI->form_validation->set_data($data);

                // Set validation rule
                $this->CI->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->CI->form_validation->run()) {
                    // Call create feed model
                    $commentId = $this->Feed_model->save_comment($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($commentId) {
                        $data = array('comment_id' => $commentId);
                        $response = ['status' => true, 'msg' => 'Comment has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => 'something_went_wrong'];
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

        } catch (Exception $e) {
            $response = ['status' => false, 'error' => $e];
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

    /**
     * Checking postost comment failure
     */
    public function test_post_comment_case2() {
        try {
            // Clear the form validation field data
            $this->CI->form_validation->reset_validation();
            
            // Get the request data
            $adminId = '20';
            // Set data in libray for validation
            $reqData = '
            {
                "feed_title":"","source_id":"34","related_type":"2", "adminId": 20
            }';
            $reqData = json_decode($reqData, true);
            //  Response initialize
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

            if (!empty($reqData)) {
                $data =(array) $reqData;
                // Validation rules set
                $validation_rules = [
                    array('field' => 'feed_comment', 'label' => 'Comment', 'rules' => 'required|max_length[225]', 'errors' => ['max_length' => "%s field cannot exceed 225 characters."]),
                    array('field' => 'history_id', 'label' => 'History Id', 'rules' => "required", 'errors' => ['required' => 'History Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->CI->form_validation->set_data($data);

                // Set validation rule
                $this->CI->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->CI->form_validation->run()) {
                    // Call create feed model
                    $commentId = $this->Feed_model->save_comment($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($commentId) {
                        $data = array('comment_id' => $commentId);
                        $response = ['status' => true, 'msg' => 'Comment has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => 'something_went_wrong'];
                    }
                } else {
                    // If requested data isn't valid
                    $errors = $this->form_validation->error_array();
                    $response = ['status' => false, 'error' => implode(', ', $errors)];
                }
            } else {
                // If requested data is empty or null
                $response = ['status' => false, 'error' => 'Requested data is null'];
            }

        } catch (Exception $e) {
            $response = ['status' => false, 'error' => $e];
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