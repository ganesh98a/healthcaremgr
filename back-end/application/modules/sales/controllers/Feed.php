<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : Feed
 * Uses : Used for handle Feed request and response
 * Getting request data - request_handler('access_crm')
 * Response type - Json format
 *
 * Library
 * form_validation - used for validating the form data
 *
 * LogType - crm
 *
 * @property-read \Feed_model $Feed_model
 */
class Feed extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        // load model
        $this->load->model('Feed_model');
        $this->load->model('Basic_model');

        // set the log
        $this->loges->setLogType('crm');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    /**
     * Post Feed
     */
    public function post_feed() {
        try {
            // Get the request data
            $reqData = request_handler('');
            $adminId = $reqData->adminId;
            //  Response initialize
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

            if (!empty($reqData->data)) {
                $data =(array) $reqData->data;
                // Validation rules set
                $validation_rules = [
                    array('field' => 'feed_title', 'label' => 'Feed Title', 'rules' => 'required'),
                    array('field' => 'source_id', 'label' => 'Source Id', 'rules' => "required", 'errors' => ['required' => 'Source Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->form_validation->set_data($data);

                // Set validation rule
                $this->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->form_validation->run()) {
                    // Call create feed model
                    $feedId = $this->Feed_model->save_feed($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($feedId) {
                        $this->load->library('UserName');
                        $adminName = $this->username->getName('admin', $adminId);

                        /**
                         * Create logs. it will represent the user action they have made.
                         */
                        $this->loges->setTitle("New Feed posted for " . $data['feed_title'] ." by " . $adminName);  // Set title in log
                        $this->loges->setDescription(json_encode($data));
                        $this->loges->setUserId($adminId);
                        $this->loges->setCreatedBy($adminId);
                        // Create log
                        $this->loges->createLog();
                        $data = array('feed_id' => $feedId);
                        $response = ['status' => true, 'msg' => 'Feed has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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

        $response = json_encode($response);
        echo $response;
        exit();
    }

    /**
     * Post comment
     */
    public function post_comment() {
        try {
            // Get the request data
            $reqData = request_handler('');
            $adminId = $reqData->adminId;
            //  Response initialize
            $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];

            if (!empty($reqData->data)) {
                $data =(array) $reqData->data;
                // Validation rules set
                $validation_rules = [                    
                    array('field' => 'history_id', 'label' => 'History Id', 'rules' => "required", 'errors' => ['required' => 'History Id Missing']),
                    array('field' => 'related_type', 'label' => 'Related Type', 'rules' => "required", 'errors' => ['required' => 'Related Type Missing'])
                ];

                // Set data in libray for validation
                $this->form_validation->set_data($data);

                // Set validation rule
                $this->form_validation->set_rules($validation_rules);

                // Check data is valid or not
                if ($this->form_validation->run()) {
                    // Call create feed model
                    $commentId = $this->Feed_model->save_comment($data, $adminId);

                    // Check $feedId is empty or not
                    // According to that feed will be created
                    if ($commentId) {
                        $this->load->library('UserName');
                        $adminName = $this->username->getName('admin', $adminId);

                        /**
                         * Create logs. it will represent the user action they have made.
                         */
                        $this->loges->setTitle("New Comment posted for " . $data['feed_comment'] ." by " . $adminName);  // Set title in log
                        $this->loges->setDescription(json_encode($data));
                        $this->loges->setUserId($adminId);
                        $this->loges->setCreatedBy($adminId);
                        // Create log
                        $this->loges->createLog();
                        $data = array('comment_id' => $commentId);
                        $response = ['status' => true, 'msg' => 'Comment has been posted successfully.', 'data' => $data ];
                    } else {
                        $response = ['status' => false, 'error' => system_msgs('something_went_wrong')];
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

        $response = json_encode($response);
        echo $response;
        exit();
    }

    /**
     * Get feed related type
     */
    public function get_related_type() {
        $reqData = request_handler();

        if (empty($reqData->data) == false && isset($reqData->data->related) == true ) {
            // Call model for get related type
            $related = $reqData->data->related;
            $response = $this->Feed_model->get_related_type($related);
            $result = ['status' => true, 'data' => ['related_type' => $response], 'msg' => "Related Type has been fetched successfully."];
        } else {
            // If requested data is empty or null
            if (isset($reqData->data->related) == false || $reqData->data->related == '') {
                $result = ['status' => false, 'error' => 'Related type is null'];
            } else {
                $result = ['status' => false, 'error' => 'Requested data is null'];
            }
        }
        echo json_encode($result);
        exit();
    }
}