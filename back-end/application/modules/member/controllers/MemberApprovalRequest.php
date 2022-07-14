<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MemberApprovalRequest extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('notification_helper');
        $this->load->helper('approval_helper');
        $this->load->library('Notification');
        $this->load->library('Approval');
        $this->load->model('RequestParticipant_model');
        $this->notification->setUser_type(2);
        $this->loges->setLogType('member');
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
        request_handler('access_member');
        die('Invalid request');
    }

    public function approve_member_profile_update() {
        $reqData = request_handler('update_admin', 1, 1);
        $this->approval->setApproved_by($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $approve_status = check_approval_request_and_set_notification($reqData, 'participant_create_shift');

            if (!empty($approve_status)) {
                $this->RequestParticipant_model->create_participant_shift($reqData);
            } else {
                $this->db->trans_rollback();
            }

            echo json_encode(array('status' => true));
        }
    }

}
