<?php

use classRoles as adminRoles;
use AdminClass as AdminClass;

require_once APPPATH . 'traits/formCustomValidation.php';
require_once APPPATH . 'Classes/websocket/Websocket.php';
defined('BASEPATH') or exit('No direct script access allowed');

//class Master extends MX_Controller

class Holiday extends CI_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('Holiday_model');
        $this->loges->setLogType('user_admin');
        $this->load->library('form_validation');
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

    public function index() {
        // nothing
        // added dummy line 1
    }

    /**
     * adding/updating the holidays and its relevant information
     */
    public function create_update_holidays() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Holiday_model->create_update_holiday($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
    /**
     * get Holiday details
     */
    public function get_holiday_details()
    {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $holidayId = $reqData->data->id;
            $response = $this->Holiday_model->get_holiday_details($holidayId);         
            if (!empty($response)) {
                echo json_encode(array('status' => true, 'data' => $response));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Sorry no data found'));
            }
        }
    }

    /**
     * get Holiday type
     */
    public function get_holiday_types()
    {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $response = $this->Holiday_model->get_holiday_types();         
            if (!empty($response)) {
                echo json_encode(array('status' => true, 'data' => $response));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Sorry no data found'));
            }
        }
    }

    /**
     * fetching holidays list
     */
    public function get_holidays_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $this->load->model('../../common/models/List_view_controls_model');
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            $filter_condition = str_replace(['status'], ['hdstatus'], $filter_condition);
            $result = $this->Holiday_model->get_holidays_list($reqData->data, $filter_condition);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * archiving holiday
     */
    public function archive_holiday() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Holiday_model->archive_holiday((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * get location list
    */
    public function get_location_list()
    {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $response = $this->Holiday_model->get_location_list();         
            if (!empty($response)) {
                echo json_encode(array('status' => true, 'data' => $response));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Sorry no data found'));
            }
        }
    }
    
}
