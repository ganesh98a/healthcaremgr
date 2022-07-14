<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'Classes/websocket/Websocket.php';
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ActionNotification
 *
 * @author user
 */
class ActionNotification {

    private $id;
    private $applicant_id;
    private $action_type;
    private $recruiterId;
    private $created = DATE_TIME;
    private $archive = 0;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setApplicant_id($applicant_id) {
        $this->applicant_id = $applicant_id;
    }

    function getApplicant_id() {
        return $this->applicant_id;
    }

    function setAction_type($action_type) {
        $this->action_type = $action_type;
    }

    function getAction_type() {
        return $this->action_type;
    }

    function setRecruiterId($recruiterId) {
        $this->recruiterId = $recruiterId;
    }

    function getRecruiterId() {
        return $this->recruiterId;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function createAction() {
        $wbObj = new Websocket();
        $CI = & get_instance();

        $action_data = array(
            'applicant_id' => $this->getApplicant_id(),
            'action_type' => $this->getAction_type(),
            'recruiterId' => $this->getRecruiterId(),
            'created' => $this->getCreated(),
            'archive' => $this->getArchive(),
        );

        $result = $CI->basic_model->insert_records('recruitment_action_notification', $action_data, $multiple = FALSE);
        // check websoket here send and alert
        if ($wbObj->check_webscoket_on()) {
            $data = array('chanel' => 'server', 'req_type' => 'recruitment_admin_actionable_notification', 'token' => $wbObj->get_token(), 'data' => true);
            $wbObj->send_data_on_socket($data);
        }

        return $result;
    }

}
