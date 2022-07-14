<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class : Task
 * Uses : Used for handle Task request and response  
 * Getting request data - request_handler('access_crm')
 * Response type - Json format
 * 
 * Library
 * form_validation - used for validating the form data
 * 
 * LogType - crm
 * 
 * @property-read \Task_modal $Task_modal
 */
class Task extends MX_Controller {
    // Load custom validation traits function
    use formCustomValidation;

    // Defualt construct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        
        // load task model 
        $this->load->model('Task_model');

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

    /*
     * For getting task list
     * 
     * Return type json
     * - count 
     * - data
     * - status
     */
    function get_task_list() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for task list
            $result = $this->Task_model->get_task_list($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

     /*
     * For get task by id
     * 
     * Return type json 
     * - data
     * - status
     */
    function get_task_details_for_view() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for task list
            $result = $this->Task_model->get_task_details_for_view($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }

    /*
     * For get task by id for edit
     * 
     * Return type json 
     * - data
     * - status
     */
    function get_task_details_for_edit() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            // Call model for task list
            $result = $this->Task_model->get_task_details_for_edit($reqData->data);
        } else {
            // If requested data is empty or null
            $result = ['status' => false, 'error' => 'Requested data is null'];           
        }      
        echo json_encode($result);
        exit();  
    }
}
