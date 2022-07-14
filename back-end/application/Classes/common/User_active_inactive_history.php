<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User_active_inactive_history
 *
 * @author user
 */
class User_active_inactive_history {

    private $userId;
    private $user_type;
    private $action_type;
    private $action_by;
    private $created;
    private $archive;

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

    function setAction_type($action_type) {
        $this->action_type = $action_type;
    }

    function getAction_type() {
        return $this->action_type;
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

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function addHistory() {
        $active_inactive_history = [
            'userId' => $this->userId,
            'user_type' => $this->user_type,
            'action_type' => $this->action_type,
            'action_by' => $this->action_by,
            'created' => DATE_TIME,
            'archive' => 0
        ];

        // insert history in inactive and active table according to action
        $this->CI->basic_model->insert_records('user_active_inactive_history', $active_inactive_history, $multiple = FALSE);
    }

}
