<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'Classes/websocket/Websocket.php';

//class Master extends MX_Controller
class Out_of_office extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('Out_of_office_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->loges->setLogType('external_imail');
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

    function get_out_of_office_admin_name_for_create() {
        $reqData = request_handler();
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $reqData = json_decode($reqData);
            $result = $this->Out_of_office_model->get_out_of_office_admin_name_for_create($reqData);
            echo json_encode($result);
        }
    }

    function get_admin_name_by_filter() {
        $reqData = request_handler();
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $result = $this->Out_of_office_model->get_admin_name_by_filter($reqData);
            echo json_encode(['status' => true, 'data' => $result]);
        }
    }

    function check_end_date_of_out_of_office_is_valid($end_date) {
        if ($end_date) {
            $x = (strtotime(DateFormate($end_date, "Y-m-d")) >= strtotime(DateFormate(DATE_TIME, "Y-m-d")) ? true : false);

            if (!$x) {
                $this->form_validation->set_message('check_end_date_of_out_of_office_is_valid', "End date cannot less than current date");
                return false;
            }
            return true;
        }
    }

    public function create_update_out_of_office_message() {
        $reqData = request_handler("out_of_office");
        $this->loges->setCreatedBy($reqData->adminId);

        require_once APPPATH . 'Classes/imail/OutOfOfficeMessage.php';
        $objMess = new OutOfOfficeMessage();

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $data['id'] = (!empty($data['id'])) ? $data['id'] : false;

            $this->form_validation->set_data($data);

            $validation_rules = array(
                array('field' => 'from_date', 'label' => 'from date', 'rules' => 'required'),
                array('field' => 'end_date', 'label' => 'end date', 'rules' => 'required|callback_check_end_date_of_out_of_office_is_valid'),
            );


            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if (!empty($data["create_for"])) {
//                    check_permission($reqData->adminId, "access_admin");
                    $objMess->setCreate_for($data["create_for"]);
                } else {
                    $objMess->setCreate_for($reqData->adminId);
                }

                $objMess->setCreated_by($reqData->adminId);
                $objMess->setReplace_by($data["replace_by"]);
                $objMess->setFrom_date(DateFormate($data["from_date"], "Y-m-d"));
                $objMess->setEnd_date(DateFormate($data['end_date'], "Y-m-d"));
                $objMess->setAdditional_message($data['additional_message'] ?? '');

                if ($data['id'] > 0) {
                    $objMess->setId($data['id']);
                }

                $res = $objMess->check_already_exist_out_of_office();
                if (!empty($res)) {
                    echo json_encode(['status' => false, 'error' => "Already exist on selected date"]);
                    exit();
                }

                //create and update out of office message
                $outOfOfficeId = $objMess->create_update_out_of_office_message();

                if ($data['id'] > 0) {
                    $this->loges->setTitle('Update out of office: ' . $outOfOfficeId);
                } else {
                    $this->loges->setTitle('Create new out of office : ' . $outOfOfficeId);
                }

                $this->loges->setUserID($outOfOfficeId);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->createLog();

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function get_out_of_office_calander_listing() {
        $reqData = request_handler("out_of_office");
        if ($reqData->data) {
            $result = $this->Out_of_office_model->get_out_of_office_calander_listing($reqData->data);
            echo json_encode(['status' => true, "data" => $result]);
        }
    }

    function get_out_of_office_details() {
        $reqData = request_handler();
        if ($reqData->data) {
            $result = $this->Out_of_office_model->get_out_of_office_details($reqData->data);
            echo json_encode(['status' => true, "data" => $result]);
        }
    }

    function get_out_of_office_details_for_edit() {
        $reqData = request_handler();
        if ($reqData->data) {
            $result = $this->Out_of_office_model->get_out_of_office_details($reqData->data);

            if (!$result->its_editable) {
                echo json_encode(['status' => false, "error" => "Edit permission not allow"]);
            } else {
                echo json_encode(['status' => true, "data" => $result]);
            }
        }
    }

}
