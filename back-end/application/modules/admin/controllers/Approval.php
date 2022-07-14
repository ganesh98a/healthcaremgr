<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'Classes/approval/ParticipantAprroval.php';
require_once APPPATH . 'Classes/approval/MemberAprroval.php';

//class Master extends MX_Controller  
class Approval extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Approval_model');
        $this->load->helper('approval_helper');
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

    // using this method get admin list
    public function get_list_approval() {
        $reqData = request_handler('access_admin', 1, 1);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $reqData = json_decode($reqData);
            $response = $this->Approval_model->get_list_approval($reqData);

            echo json_encode($response);
        }
    }

    public function get_approval_data() {
        $reqData = request_handler('access_admin', 1, 1);

        if ($reqData->data) {
            $reqData = $reqData->data;

            $approval_data = $this->Approval_model->get_approve_data($reqData->aprrovalId);
            if (!empty($approval_data)) {
                $temp_data = approval_mapping($approval_data['approval_area']);
               // echo $temp_data['function'];
                $approval_data['approval_data'] = $this->{$temp_data['function']}($approval_data);
                $approval_data['approval_area'] = $temp_data['description'];
                $approval_data['submit_uri'] = $temp_data['submit_uri'];
                echo json_encode(array('status' => true, 'data' => $approval_data));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Invalid approval id'));
            }
        }
    }

    public function add_to_pin() {
        $reqestData = request_handler('access_admin', 1, 1);
        $reqData = $reqestData->data;

        if ($reqData->approvalId) {
            $this->Approval_model->add_to_pin($reqData);
            echo json_encode(array('status' => true));
        } else {
            echo json_encode(array('status' => false, 'missing approval id or status'));
        }
    }

    public function participant_profile($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_profile($userData);
    }

    function participant_place($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_place($userData);
    }

    function participant_activity($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_activity($userData);
    }

    function participant_care_requirement($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_care_requirement($userData);
    }

    function participant_email($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_email($userData);
    }

    function participant_phone($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_phone($userData);
    }

    function participant_address($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_address($userData);
    }

    function participant_kin_update($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_kin_update($userData);
    }

    function participant_booker_update($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_booker_update($userData);
    }

    function participant_add_goal($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_add_goal($userData);
    }

    function participant_update_goal($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_update_goal($userData);
    }

    function participant_archive_goal($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_archive_goal($userData);
    }

    function participant_create_shift($userData) {
        $obj = new ClassParticipantAprroval\ParticipantAprroval();
        return $obj->participant_create_shift($userData);
    }

    function approve_member_places_update($userData) {
        $obj = new ClassMemberAprroval\MemberAprroval();
        return $obj->update_member_places($userData);
    }

    function approve_member_activity_update($userData) {
        $obj = new ClassMemberAprroval\MemberAprroval();
        return $obj->update_member_activity($userData);
    }
    
    function approve_member_profile_update($userData) {
        $obj = new ClassMemberAprroval\MemberAprroval();
        return $obj->update_member_profile($userData);
    }

    public function archive_approval() {
        $request = request_handler('access_admin', 1, 1);
        if (!empty($request->data)) {
            $req = $request->data;
           
            $id = $req->id;
            $arr = array("id" => $id);
            $row = $this->basic_model->update_records('approval', array('archive' => 1,'updated'=>DATE_TIME), $arr);

            if ($row) {
                echo json_encode(array('status' => true));
                exit();
            } else {
                echo json_encode(array('status' => false));
                exit();
            }
        }
    
    }

}
