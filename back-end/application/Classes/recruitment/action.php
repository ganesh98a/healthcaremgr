<?php

namespace classAction;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Action {

    public function __construct() {
        //$this->CI = &get_instance();			
    }

    private $id;
    private $action_name;
    private $user;
    private $action_type;
    private $start_datetime;
    private $end_datetime;
    private $training_location;
    private $status;
    private $mail_status;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setAction_name($action_name) { $this->action_name = $action_name; }
    function getAction_name() { return $this->action_name; }
    function setUser($user) { $this->user = $user; }
    function getUser() { return $this->user; }
    function setAction_type($action_type) { $this->action_type = $action_type; }
    function getAction_type() { return $this->action_type; }
    function setStart_datetime($start_datetime) { $this->start_datetime = $start_datetime; }
    function getStart_datetime() { return $this->start_datetime; }
    function setEnd_datetime($end_datetime) { $this->end_datetime = $end_datetime; }
    function getEnd_datetime() { return $this->end_datetime; }
    function setTraining_location($training_location) { $this->training_location = $training_location; }
    function getTraining_location() { return $this->training_location; }
    function setStatus($status) { $this->status = $status; }
    function getStatus() { return $this->status; }
    function setMail_status($mail_status) { $this->mail_status = $mail_status; }
    function getMail_status() { return $this->mail_status; }

    public function CreateAction() {
        $CI = & get_instance();
        $action_data = array('action_name' => $this->getAction_name(), 
                            'created' => DATE_TIME,
                            'user'=>$this->getUser(),
                            'action_type'=>$this->getAction_type(),
                            'training_location'=>$this->getTraining_location(),
                            'status'=>$this->getStatus(),
                            'mail_status'=>$this->getMail_status(),
                            'start_datetime'=>$this->getStart_datetime(),
                            'end_datetime'=>$this->getEnd_datetime()
                        );
        $result = $CI->basic_model->insert_records('recruitment_action', $action_data, $multiple = FALSE);
        return $result;
    }

    public function UpdateRoles() {
        $CI = & get_instance();
        $role_data = array('name' => $this->role_name, 'archive' => $this->archive);
        $where = array('id' => $this->role_id);
        $result = $CI->basic_model->update_records('role', $role_data, $where);
        return $result;
    }
}
