<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace RosterClass;

/**
 * Description of Roster
 *
 * @author user
 */
class Roster {

    public $CI;
    private $rosterId;
    private $participantId;
    private $title;
    private $shift_round;
    private $is_default;
    private $start_date;
    private $end_date;
    private $status;
    private $created;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('crm/CrmParticipant_model');
    }

    public function getRosterId() {
        return $this->rosterId;
    }

    public function setRosterId($rosterId) {
        $this->rosterId = $rosterId;
    }

    public function getParticipantId() {
        return $this->participantId;
    }

    public function setParticipantId($participantId) {
        $this->participantId = $participantId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getShift_round() {
        return $this->shift_round;
    }

    public function setShift_round($shift_round) {
        $this->shift_round = $shift_round;
    }

    public function getIs_default() {
        return $this->is_default;
    }

    public function setIs_default($is_default) {
        $this->is_default = $is_default;
    }

    public function getStart_date() {
        return $this->start_date;
    }

    public function setStart_date($start_date) {
        $this->start_date = $start_date;
    }

    public function getEnd_date() {
        return $this->end_date;
    }

    public function setEnd_date($end_date) {
        $this->end_date = $end_date;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function createRoster() {
   
        $roster = array('start_date' => $this->start_date, 'participantId' => $this->participantId, 'status' => $this->status, 'created' => $this->created, 'shift_round' => $this->shift_round, 'is_default' => $this->is_default);

        if ($this->is_default == 1) {
            $roster['end_date'] = $this->end_date;
            $roster['title'] = $this->title;
        }

        return $rosterId = $this->CI->basic_model->insert_records('crm_participant_roster', $roster, $multiple = FALSE);
    }

    public function updateRoster() {
        $roster = array('start_date' => $this->start_date, 'participantId' => $this->participantId, 'status' => $this->status, 'created' => $this->created, 'shift_round' => $this->shift_round, 'is_default' => $this->is_default);

        if ($this->is_default == 1) {
            $roster['end_date'] = $this->end_date;
            $roster['title'] = $this->title;
        } else {
            $roster['end_date'] = '';
            $roster['title'] = '';
        }

        $this->CI->basic_model->update_records('crm_participant_roster', $roster, $where = array('id' => $this->rosterId));
    }

    function rosterCreateMail() {
        $participantData = $this->CI->basic_model->get_row('crm_participant', array('firstname', 'lastname'), $where = array('id' => $this->participantId));
        $participantEmail = $this->CI->basic_model->get_row('crm_participant_email', array('email'), $where = array('crm_participant_id' => $this->participantId, 'primary_email' => 1));

        $userData['fullname'] = $participantData->firstname . ' ' . $participantData->lastname;
        $userData['email'] = $participantEmail->email;

        $userData['start_date'] = DateFormate($this->start_date, 'd-m-Y');
        $userData['is_default'] = $this->is_default;

        if ($this->is_default == 1) {
            $userData['end_date'] = DateFormate($this->end_date, 'd-m-Y');
            $userData['title'] = $this->title;
        }
        roster_create_mail($userData);
    }

    function checkRosterAleadyExistThisDate() {
        return $this->CI->Roster_model->check_roster_start_end_date_already_exist($this);
    }

}
