<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of disable_portal_access_note
 *
 * @author user
 */
class Disable_portal_access {

    private $userId;
    private $user_type;
    private $note;
    private $action_by;
    private $created;

    function __construct() {
        $this->CI = & get_instance();
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function setUser_type($user_type) {
        $this->user_type = $user_type;
    }

    function getUser_type() {
        return $this->user_type;
    }

    function setNote($note) {
        $this->note = $note;
    }

    function getNote() {
        return $this->note;
    }

    function setAction_by($action_by) {
        $this->action_by = $action_by;
    }

    function getAction_by() {
        return $this->action_by;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function add_history() {
        //save disable portal access note
        $note_data = array(
            'userId' => $this->userId,
            'user_type' => $this->user_type,
            'note' => $this->note,
            'action_by' => $this->action_by,
            'created' => DATE_TIME,
        );

        $this->CI->basic_model->insert_records('disable_portal_access_note', $note_data, $multiple = false);
    }

}
