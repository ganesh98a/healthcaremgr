<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class RecruitmentDevice extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('Recruitment_device_model');

        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->form_validation->CI = & $this;
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

    function get_device_listing() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_device_model->get_device_listing($reqData->data);

            echo json_encode($response);
        }
    }
    function get_allocated_or_unallocated_device_listing() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $response = $this->Recruitment_device_model->get_allocated_or_unallocated_device_listing($reqData->data);

            echo json_encode($response);
        }
    }

    function get_device_location_list() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $column = ['name as label', 'id as value'];
            $response = $this->basic_model->get_record_where('recruitment_location', $column, ['archive' => 0]);

            echo json_encode(['status' => true, 'data' => $response]);
        }
    }

    function add_edit_device() {
        $reqData = request_handler('access_recruitment');

        if (!empty($reqData->data)) {
            $edit_mode = $deviceId = (isset($reqData->data->id)) ? $reqData->data->id : '';

            $validation_rules = array(
                array('field' => 'device_location', 'label' => 'device location', 'rules' => 'required'),
                array('field' => 'device_name', 'label' => 'device name', 'rules' => 'callback_check_device_name_already_exist[' . $deviceId . ']'),
                array('field' => 'device_number', 'label' => 'device number', 'rules' => 'callback_check_device_number_already_exist[' . $deviceId . ']'),
            );

//            print_r((array) $reqData->data);
            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $deviceId = $this->Recruitment_device_model->add_edit_device($reqData->data);
                $txt = $edit_mode? "Modified device : ".$reqData->data->device_name : "Added New device : ".$reqData->data->device_name;
                $this->loges->setTitle($txt);
                $this->loges->setUserId($deviceId);
                $this->loges->setDescription(json_encode($reqData->data));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function check_device_name_already_exist($device_name, $deviceId) {
        if (!empty($device_name)) {
            $where = ['device_name' => $device_name];

            if (!empty($deviceId)) {
                $where['id !='] = $deviceId;
            }
            $res = $this->basic_model->get_row('recruitment_device', ['id'], $where);

            if (!empty($res)) {
                $this->form_validation->set_message('check_device_name_already_exist', "Device name already exist.");
                return false;
            }
        } else {
            $this->form_validation->set_message('check_device_name_already_exist', "The Device name field is required.");
            return false;
        }
    }

    function check_device_number_already_exist($device_number, $deviceId) {
        if (!empty($device_number)) {
            $where = ['device_number' => $device_number];

            if (!empty($deviceId)) {
                $where['id !='] = $deviceId;
            }
            $res = $this->basic_model->get_row('recruitment_device', ['id'], $where);

            if (!empty($res)) {
                $this->form_validation->set_message('check_device_number_already_exist', "Device number already exist.");
                return false;
            }
        } else {
            $this->form_validation->set_message('check_device_name_already_exist', "The Device name field is required.");
            return false;
        }
    }

}
