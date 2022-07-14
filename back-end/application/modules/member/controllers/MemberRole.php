<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MemberRole extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Member_model');
        $this->load->model('common/Common_model');
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

    private function jsonResponse($response) {
        ob_start();
        echo json_encode($response);
        echo ob_get_clean();
        exit();
    }

    /*
     * It used for search account person
     * return type json
     */
    public function get_member_name_search() {
        $reqData = request_handler('access_member');
        $memberName = $reqData->data->query ?? '';
        $rows = $this->Member_model->get_member_name_search($memberName);
        $this->jsonResponse($rows);
    }

    public function create_update_member_role() {
        $reqData = request_handler('access_member');
        $rows = $this->Member_model->create_update_member_role($reqData);
        $this->jsonResponse($rows);
    }

    public function get_member_roles() {
        $data = new stdClass();
        $reqData = request_handler('access_member');
        $memberId = $reqData->data->id ?? '';
        $limit = $reqData->data->limit ?? 0;
        $rows = $this->Member_model->get_member_roles($memberId, 0, $limit);
        $data->roles = $rows;
        $member = $this->basic_model->get_record_where('member', ['fullname as member_name', 'id as member_id'], ['archive' => 0, 'id' => $memberId]);
        if (!empty($member)) {
            $data->member = $member[0];
        }
        $data->pay_points = $this->Common_model->get_pay_point_options();
        $data->levels = $this->Common_model->get_level_options();
        $this->jsonResponse(array('status' => true, 'data' => $data));
    }
    
    public function get_member_role_details() {
        $reqData = request_handler('access_member');
        $memberRoleId = $reqData->data->id ?? '';
        $rows = $this->Member_model->get_member_roles(0, $memberRoleId);
        $this->jsonResponse(array('status' => true, 'data' => $rows[0]));
    }

    public function archive_member_role() {
        $reqData = request_handler('access_member');
        $memberRoleId = $reqData->data->id ?? '';
        $this->Member_model->archive_member_role($memberRoleId);
        $this->jsonResponse(array('status' => true, 'msg' => "Role successfully archived"));
    }
}
