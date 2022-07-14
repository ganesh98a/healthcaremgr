<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceUserManagement extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Finance_user_management');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->loges->setLogType('finance_user');
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

    function get_new_finance_user_name() {
        $reqData = request_handler('access_finance_admin');

        if (!empty($reqData->data)) {
            $result = $this->Finance_user_management->get_new_finance_user_name($reqData->data);
            echo json_encode($result);
        }
    }

    function get_finance_user_list() {
        $reqData = request_handler('access_finance_admin');

        if (!empty($reqData->data)) {
            $result = $this->Finance_user_management->get_finance_user_list($reqData->data);
            echo json_encode($result);
        }
    }

    function get_finance_user_details_for_add_edit() {
        $reqData = request_handler('access_finance_admin');

        if (!empty($reqData->data)) {

            $result = (array) $this->Finance_user_management->get_finance_user_details_for_add_edit($reqData->data);
           
            if (!empty($result)) {
                $result['finance_permissions'] = $this->Finance_user_management->get_finance_module_permission($reqData->data);
                echo json_encode(['status' => true, 'data' => $result]);
            } else {
                echo json_encode(['status' => false, 'error' => 'Admin not found']);
            }
        }
    }

    function add_new_finance_staff() {
        $requestData = request_handler('access_finance_admin');
        $reqData = $requestData->data;
       
        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'id', 'label' => 'adminId', 'rules' => 'callback_check_finance_permission_of_finance_user[' . json_encode($reqData->finance_permissions) . ']|callback_check_its_new_finance_finance_user[' . json_encode($reqData) . ']'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->Finance_user_management->add_edit_new_finance_staff($reqData);

                $finance_user = $this->username->getName('admin', $reqData->id);
                $this->loges->setTitle('Added New fiance user in finance module : ' . $finance_user);
                $this->loges->setUserId($reqData->id);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setCreatedBy($requestData->adminId);
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function check_finance_permission_of_finance_user($adminId, $finance_permission) {
        $finance_permission = json_decode($finance_permission);

        $access_true = false;
        if (!empty($finance_permission)) {
            foreach ($finance_permission as $val) {
                if (!empty($val->access)) {
                    $access_true = true;
                    break;
                }
            }
        }

        if (!$access_true) {
            $this->form_validation->set_message('check_finance_permission_of_finance_user', 'Please select at least one permission');
            return false;
        }

        return true;
    }

    function check_its_new_finance_finance_user($adminId, $reqData) {
        $reqData = json_decode($reqData);
//        $x = ['adminId' => $adminId];
        $res = $this->Finance_user_management->get_finance_user_details_for_add_edit((object) $reqData);

        if (!empty($res)) {
            return true;
        } else {
            $this->form_validation->set_message('check_its_new_finance_finance_user', 'Please provide valid finance user id');
            return false;
        }
    }

    function active_disable_finance_user() {
        $requestData = request_handler('access_finance_admin');
        $reqData = $requestData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'financeId', 'label' => 'financeId', 'rules' => 'required'),
                array('field' => 'adminId', 'label' => 'adminId', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->Finance_user_management->active_disable_finance_user($reqData);

                $finance_user = $this->username->getName('admin', $reqData->adminId);
                $this->loges->setTitle(($reqData->status == 1 ? "Enable " : "Disabled ") . 'Finance user : ' . $finance_user);
                $this->loges->setUserId($reqData->adminId);
                $this->loges->setDescription(json_encode($reqData));
                 $this->loges->setCreatedBy($requestData->adminId);
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function get_json() {
        $c = $this->basic_model->get_record_where('finance_measure', $column = '', $where = '');
        echo json_encode($c);
    }

}
