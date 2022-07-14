<?php

namespace classApplicant;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Applicant {

    public function __construct() {
        //$this->CI = &get_instance();			
    }

    private $id;
    private $applicant_id;
    private $action_id;
    private $applicant_message;
    private $email_status;
    private $status;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setAction_id($action_id) { $this->action_id = $action_id; }
    function getAction_id() { return $this->action_id; }

    function setApplicant_id($applicant_id) { $this->applicant_id = $applicant_id; }
    function getApplicant_id() { return $this->applicant_id; }
    function setApplicant_message($applicant_message) { $this->applicant_message = $applicant_message; }
    function getApplicant_message() { return $this->applicant_message; }
    function setEmail_status($email_status) { $this->email_status = $email_status; }
    function getEmail_status() { return $this->email_status; }
    function setStatus($status) { $this->status = $status; }
    function getStatus() { return $this->status; }


    public function attachApplicant() {
        $CI = & get_instance();
        $action_data = array('applicant_id' => $this->getApplicant_id(), 
            'action_id'=>$this->getAction_id(),
            'email_status'=>$this->getEmail_status(),
            'status'=>$this->getStatus(),
        );
        $result = $CI->basic_model->insert_records('recruitment_action_applicant', $action_data, $multiple = FALSE);
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
