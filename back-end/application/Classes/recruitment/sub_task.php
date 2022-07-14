<?php

namespace classSubTask;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sub_task {

    public function __construct() {
        //$this->CI = &get_instance();			
    }

    private $id;
    private $action_id;
    private $subtask;
    private $subtask_detail;
    private $notes;
    private $assigned_to;
    private $due_date;
    private $attachment;
    private $task_completed;
    private $archive;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setAction_id($action_id) { $this->action_id = $action_id; }
    function getAction_id() { return $this->action_id; }
    function setSubtask($subtask) { $this->subtask = $subtask; }
    function getSubtask() { return $this->subtask; }
    function setSubtask_detail($subtask_detail) { $this->subtask_detail = $subtask_detail; }
    function getSubtask_detail() { return $this->subtask_detail; }
    function setNotes($notes) { $this->notes = $notes; }
    function getNotes() { return $this->notes; }
    function setAssigned_to($assigned_to) { $this->assigned_to = $assigned_to; }
    function getAssigned_to() { return $this->assigned_to; }
    function setDue_date($due_date) { $this->due_date = $due_date; }
    function getDue_date() { return $this->due_date; }
    function setAttachment($attachment) { $this->attachment = $attachment; }
    function getAttachment() { return $this->attachment; }
    function setTask_completed($task_completed) { $this->task_completed = $task_completed; }
    function getTask_completed() { return $this->task_completed; }
    function setArchive($archive) { $this->archive = $archive; }
    function getArchive() { return $this->archive; }


    public function CreateSubTask() {
        $CI = & get_instance();
        $action_data = array('action_id' => $this->getAction_id(), 
            'subtask'=>$this->getSubtask(),
            'subtask_detail'=>$this->getSubtask_detail(),
            #'notes'=>$this->getNotes(),
            'assigned_to'=>$this->getAssigned_to(),
            'due_date'=>$this->getDue_date(),
            #'attachment'=>$this->getAttachment(),
            'task_completed'=>$this->getTask_completed(),
        );
        $result = $CI->basic_model->insert_records('recruitment_action_subtask', $action_data, $multiple = FALSE);
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
