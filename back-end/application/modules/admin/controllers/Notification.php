<?php

use classRoles as adminRoles;
use AdminClass as AdminClass;

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class Notification extends CI_Controller {

    function __construct() {
        parent::__construct();
        //$this->load->model('notification_model');
        $this->loges->setModule(1);
        $this->load->library('form_validation');
        $this->load->model('Notification_model');
        $this->load->model('common/List_view_controls_model');
        $this->load->helper('i_pad');
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

    public function get_all_notification() {
        // get request data
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $adminId = $reqData->adminId;
            $request = $reqData->data;
            $reqData = json_decode($request);
            $response = $this->Notification_model->get_all_notification($reqData, $adminId);
            echo json_encode($response);
        }
    }

    public function get_notification_alert() {
        // get request data

        $reqData = request_handler();
        if (!empty($reqData)) {

            $this->load->model('Notification_model');
            $response = $this->Notification_model->get_notification_alert($reqData->adminId);
            echo json_encode($response);
        }
    }

    /*
     * Get notification list
     */
    public function get_notification_without_imail_alert() {
        $reqData = request_handler();
        if (!empty($reqData)) {

            $this->load->model('Notification_model');
            $response = $this->Notification_model->get_notification_without_imail_alert($reqData->adminId, $reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }

    /*
     * Get notification list
     */
    public function get_notification_list_for_member() {
        $reqData = request_handler();

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata) && !empty($reqData->data->tobefilterdata)) {
            $reqData->data->save_filter_logic = false;
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['title','shortdescription', 'notification_type', 'notification_status', 'created'], ['tbl_notification.title','tbl_notification.shortdescription', 'tbl_notification.entity_type', 'tbl_notification.status', 'tbl_notification.created'], $filter_condition);
            }
        }

        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->get_notification_without_imail_alert(0, $reqData, $filter_condition);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }


     /*
     * Get unread notification count
     */
    public function get_unread_notification_count_for_member() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->get_unread_notification_count_for_member($reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }
    /*
     * Update notification status as read
     * set status = 1
     */
    public function update_notification_as_readed() {
        // get request data
        $reqData = request_handler();
        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->update_notification_as_readed($reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }

    /*
     * Update notification status as read
     * set status = 1
     */
    public function remove_notification() {
        // get request data
        $reqData = request_handler();
        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->remove_notification($reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }

    public function mark_all_as_read() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->mark_all_as_read($reqData->adminId);
            echo json_encode($response);
        }
    }

    public function clear_all_notification() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->clear_all_notification($reqData->adminId);
            echo json_encode($response);
        }
    }

    /*
     * Update notification status as read
     * set status = 1
     */
    public function update_member_notification_as_readed() {
        // get request data
        $reqData = new stdclass;
        $reqData->data = api_request_handler();
        $this->load->model('Notification_model');
        if(!empty($reqData) && isset($reqData->data->notification_data)) {
            foreach($reqData->data->notification_data as $data) {
                $reqData->data->notification_id = $data->id;
                $response = $this->Notification_model->update_notification_as_readed($reqData);
            }
            echo json_encode($response);
        }
        else if (!empty($reqData)) {
            $response = $this->Notification_model->update_notification_as_readed($reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }

    /*
     * Update notification status as read
     * set status = 1
     */
    public function remove_member_notification() {
        // get request data
        $reqData = new stdclass;
        $reqData->data = api_request_handler();

        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->remove_notification($reqData);
            echo json_encode($response);
        } else {
            $response = array('status' => false, 'error' => 'Required data is null');
            echo json_encode($response);
        }
    }

    public function mark_member_all_as_read() {
        $reqData = api_request_handler();

        if (!empty($reqData)) {
            $this->load->model('Notification_model');
            $response = $this->Notification_model->mark_all_as_read(0, $reqData->member_id);
            echo json_encode($response);
        }
    }

    public function get_imail_notification() {
        $reqData = request_handler('access_imail');

        $result_ex_im = $this->Notification_model->get_external_imail_notification($reqData->adminId);
        $result_int = $this->Notification_model->get_internal_imail_notification($reqData->adminId);

        $result = array_merge($result_ex_im, $result_int);
        $res = array('ImailNotificationData' => $result, 'internal_imail_count' => count($result_int), 'external_imail_count' => count($result_ex_im));

        echo json_encode(array('status' => true, 'data' => $res));
    }

    public function clear_imail_notification() {
        $reqData = request_handler('access_imail');

        if (!empty($reqData->data)) {
            $this->Notification_model->clear_imail_notification($reqData->data, $reqData->adminId);

            echo json_encode(array('status' => true));
        }
    }

}
