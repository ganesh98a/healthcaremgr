<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller  
class AllModule extends MX_Controller {

    function __construct() {
        parent::__construct();
        // load model
        $this->load->model('All_module_model');
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

    // get listing of module who enable and disable
    public function get_all_module_who_enable_disable() {
        $reqData = request_handler('access_admin', 1, 1);

        if ($reqData->data) {
            $reqData = $reqData->data;

            // get listing of module
            $response = $this->All_module_model->get_all_module_who_enable_disable($reqData);

            echo json_encode(['status' => true, 'data' => $response]);
        }
    }

    // save update enable disable data of modules
    public function save_enable_disable_module_status(){
        // check permission of admin
        $reqData = request_handler('access_admin', 1, 1);

        // check module list data
        if (isset($reqData->data->moduleList)) {
            $reqData = $reqData->data;
           
            // save modules updated status data
            $this->All_module_model->save_enable_disable_module_status($reqData->moduleList);

            echo json_encode(['status' => true]);
        }else{
            echo json_encode(['status' => false, 'error' => "module list not found"]);
        }
    }

}
