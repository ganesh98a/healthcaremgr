<?php

defined('BASEPATH') OR exit('No direct script access allowed');
include APPPATH . 'Classes/websocket/Websocket.php';

//class Master extends MX_Controller
class External_imail extends MX_Controller {

    function __construct() {

        parent::__construct();
        $this->load->model('External_model');
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

    public function index() {
        
    }

    public function get_external_messages() {
        $reqData = request_handler('access_imail');

        $result = $this->External_model->get_external_messages($reqData->data, $reqData->adminId);
        echo json_encode(array('status' => true, 'data' => $result));
    }

    public function get_single_chat() {
        $reqData = request_handler('access_imail');
        $currnetAdminId = $reqData->adminId;

        if ($reqData->data) {
            $reqData = $reqData->data;

            $result = $this->External_model->get_single_chat($reqData, $currnetAdminId);

            echo json_encode($result);
        }
    }

    function compose_new_mail() {
        $reqData = request_handlerFile('create_imail',true,false,true);
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'content', 'label' => 'Content', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
                array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
                array('field' => 'submit_type', 'label' => 'Type', 'rules' => 'required'),
                array('field' => 'is_priority', 'label' => 'priority', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // compose new mail
                $this->External_model->compose_new_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_favourite() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {

            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_fav', 'label' => 'favorite status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_fav' => $reqtData->is_fav);
                $where = array('messageId' => $reqtData->messageId, 'user_type' => 1, 'userId' => $current_admin);
                $this->basic_model->update_records('external_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_flage() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {

            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_flage', 'label' => 'flag status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_flage' => $reqtData->is_flage);
                $where = array('messageId' => $reqtData->messageId, 'user_type' => 1, 'userId' => $current_admin);
                $this->basic_model->update_records('external_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_block() {
        $reqestData = request_handler('update_imail');
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'is_block', 'label' => 'block status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('is_block' => $reqtData->is_block);
                $where = array('id' => $reqtData->messageId);
                $this->basic_model->update_records('external_message', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function add_to_archive() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'archive', 'label' => 'archive status', 'rules' => 'required|integer'),
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $data = array('archive' => $reqtData->archive);
                $where = array('messageId' => $reqtData->messageId, 'user_type' => 1, 'userId' => $current_admin);
                $this->basic_model->update_records('external_message_action', $data, $where);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function reply_mail() {
        $reqData = request_handlerFile('create_imail');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'content', 'label' => 'To:', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
                array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // reply mail
                $this->External_model->reply_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function validate_compose_mail($reqData) {
        $this->form_validation->set_data((array) $reqData);

        $validation_rules = array(
            array('field' => 'content', 'label' => 'mail content', 'rules' => 'required'),
            array('field' => 'title', 'label' => 'subject', 'rules' => 'required'),
            array('field' => 'to_user', 'label' => 'categories', 'rules' => 'required'),
            array('field' => 'contentId', 'label' => 'contentId', 'rules' => 'required'),
            array('field' => 'messageId', 'label' => 'messageId', 'rules' => 'required'),
            array('field' => 'submit_type', 'label' => 'Type', 'rules' => 'required'),
            array('field' => 'is_priority', 'label' => 'priority', 'rules' => 'required'),
        );

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {

            $response = array('status' => true);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        return $response;
    }

    function send_draft_mail() {
        $reqData = request_handlerFile('create_imail');
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->validate_compose_mail($reqData);

            if ($this->form_validation->run()) {

                // save draft mail or open draft mail
                $this->External_model->save_or_send_draft_mail($reqData, $current_admin);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function mark_as_read_unread() {
        $reqestData = request_handler('update_imail');
        $current_admin = $reqestData->adminId;
        $reqtData = $reqestData->data;

        if (!empty($reqtData)) {
            $this->form_validation->set_data((array) $reqtData);

            $validation_rules = array(
                array('field' => 'messageId', 'label' => 'message Id', 'rules' => 'required|integer'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->External_model->mark_read_unread($reqtData->messageId, $current_admin, $reqtData->action);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function get_mail_pre_filled_data() {
        $reqData = request_handler('access_imail');
        $res = $this->External_model->get_mail_pre_filled_data($reqData->data, $reqData->adminId);

        echo json_encode(array('status' => true, 'data' => $res));
    }

    function recieve_attachment_from_mobile_site() {
        $data = $this->input->post();

        if (!empty($data['files'])) {
            $files = json_decode($data['files']);
            foreach ($files as $val) {
                $copied = EXTERNAL_IMAIL_PATH . $val->messageContentId . '/' . $val->filename;
                create_directory(EXTERNAL_IMAIL_PATH . $val->messageContentId);
                // file_put_contents($copied, fopen($val->path, 'r'));

                $this->download_file_using_curl($copied, $val->path);
            }
        }
    }

    function download_file_using_curl($copied, $url) {
        set_time_limit(0);
        //This is the file where we save the    information
        $fp = fopen($copied, 'w+');
        //Here is the file we are downloading, replace spaces with %20
        $ch = curl_init(str_replace(" ", "%20", $url));
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        // write curl response to file
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        // get curl response
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    function get_external_imail_cc_and_bcc_opton() {
        $reqData = request_handler();

        if (!empty($reqData->data->search)) {
            $rows = $this->External_model->get_external_imail_cc_and_bcc_opton($reqData->data, $reqData->adminId);
        } else {
            $rows = ['status' => true, 'data' => []];
        }


        echo json_encode($rows);
    }

}
