<?php

defined('BASEPATH') || exit('No direct script access allowed');


class LeadHistory extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('sales/Leadhistory_model');
        $this->load->model('sales/Lead_model');
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

    private function sendResponse($data, $succes_msg = '') {
        if ($succes_msg) {
            $response = ['status' => true, 'data' => $data, 'msg' => $succes_msg];
        } else {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        }
        echo json_encode($response);
        exit();
    }

    public function get_field_history() {
        $reqData = request_handler('access_crm');
        if (empty($reqData)) {
            return;
        }
        $items = $this->Leadhistory_model->get_field_history($reqData->data);
        $this->sendResponse($items, 'Success');
    }
}
