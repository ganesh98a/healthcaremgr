<?php

namespace ShiftClass;

/*
 * Filename: Shift.php
 * Desc: Shift of Members, end and start time of shift
 * @author YDT <yourdevelopmentteam.com.au>
 */

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * Class: Shift
 * Desc: Class Has 5 Arrays, variables ans setter and getter methods of shifts
 * Created: 06-08-2018
 */

class Shift {

    public $CI;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('crm/Basic_model');
        $this->CI->load->model('crm/Roster_model');
    }

    /**
     * @var id
     * @access private
     * @vartype: integer
     */
    private $id;

    /**
     * @var booked_by
     * @access private
     * @vartype: tinyint
     */
    private $crm_participant_id;

    /**
     * @var shift_date
     * @access private
     * @vartype: timestamp
     */
    private $shift_date;

    /**
     * @var start_time
     * @access private
     * @vartype: timestamp
     */
    private $start_time;

    /**
     * @var end_time
     * @access private
     * @vartype: timestamp
     */
    private $end_time;

    /**
     * @var so
     * @access private
     * @vartype: tinyint
     */
    private $so;

    /**
     * @var an
     * @access private
     * @vartype: tinyint
     */
    private $an;

    /**
     * @var eco
     * @access private
     * @vartype: tinyint
     */
    private $eco;

    /**
     * @var price
     * @access private
     * @vartype: double
     */
    private $price;

    /**
     * @var allocate_pre_member
     * @access private
     * @vartype: tinyint
     */
    private $allocate_pre_member;

    /**
     * @var autofill_shift
     * @access private
     * @vartype: tinyint
     */
    private $autofill_shift;

    /**
     * @var push_to_app
     * @access private
     * @vartype: tinyint
     */
    private $push_to_app;

    /**
     * @var status
     * @access private
     * @vartype: tinyint
     */
    private $status;

    /**
     * @var created
     * @access private
     * @vartype: timestamp
     */
    private $created;




    /**
     * @array shift_participant
     * @access private
     * @vartype: integer|tinyint|timestamp
     */
    private $shift_participant = array();



    /**
     * @array shift_requirements
     * @access private
     * @vartype: integer|tinyint
     */
    private $shift_requirements = array();

    /**
     * @function getShiftid
     * @access public
     * @returns $id integer
     * Get Shift Id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @function setShiftid
     * @access public
     * @param $id integer
     * Set Shift Id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @function getBookedBy
     * @access public
     * @returns $booked_by tinyint
     * Get BookedBy
     */
    public function getCrmParticipantId() {
        return $this->crm_participant_id;
    }

    /**
     * @function setBookedBy
     * @access public
     * @param $bookedBy tinyint
     * Set BookedBy
     */
    public function setBookedBy($crm_participant_id) {
        $this->crm_participant_id = $crm_participant_id;
    }

    /**
     * @function getShiftDate
     * @access public
     * @returns $shift_date timestamp
     * Get Shift_date
     */
    public function getShiftDate() {
        return $this->shift_date;
    }

    /**
     * @function setShiftDate
     * @access public
     * @param $shiftDate timestamp
     * Set Shift_date
     */
    public function setShiftDate($shiftDate) {
        $this->shift_date = $shiftDate;
    }

    /**
     * @function getStartTime
     * @access public
     * @returns $start_time timestamp
     * Get StartTime
     */
    public function getStartTime() {
        return $this->start_time;
    }

    /**
     * @function setStartTime
     * @access public
     * @param $startTime timestamp
     * Set StartTime
     */
    public function setStartTime($startTime) {
        $this->start_time = $startTime;
    }

    /**
     * @function getEndTime
     * @access public
     * @returns $end_time timestamp
     * Get EndTime
     */
    public function getEndTime() {
        return $this->end_time;
    }

    /**
     * @function setEndTime
     * @access public
     * @param $endTime timestamp
     * Set EndTime
     */
    public function setEndTime($endTime) {
        $this->end_time = $endTime;
    }

    /**
     * @function getSo
     * @access public
     * @returns $so tinyint
     * Get So
     */
    public function getSo() {
        return $this->so;
    }

    /**
     * @function setSo
     * @access public
     * @param $so tinyint
     * Set So
     */
    public function setSo($so) {
        $this->so = $so;
    }

    /**
     * @function getAn
     * @access public
     * @returns $an tinyint
     * Get An
     */
    public function getAo() {
        return $this->ao;
    }

    /**
     * @function setAn
     * @access public
     * @param $an tinyint
     * Set An
     */
    public function setAo($ao) {
        $this->ao = $ao;
    }

    /**
     * @function getEco
     * @access public
     * @returns $eco tinyint
     * Get Eco
     */
    public function getEco() {
        return $this->eco;
    }

    /**
     * @function setEco
     * @access public
     * @param $eco tinyint
     * Set Eco
     */
    public function setEco($eco) {
        $this->eco = $eco;
    }




    /**
     * @function getPushToApp
     * @access public
     * @returns $push_to_app tinyint
     * Get PushToApp
     */
    public function getPushToApp() {
        return $this->push_to_app;
    }

    /**
     * @function PushToApp
     * @access public
     * @param $pushToApp tinyint
     * Set PushToApp
     */
    public function setPushToApp($pushToApp) {
        $this->push_to_app = $pushToApp;
    }

    /**
     * @function getStatus
     * @access public
     * @returns $status tinyint
     * Get Status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @function setStatus
     * @access public
     * @param $status tinyint
     * Set Status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @function getCreated
     * @access public
     * @returns $created tinyint
     * Get Created
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @function setCreated
     * @access public
     * @param $created tinyint
     * Set Created
     */
    public function setCreated($created) {
        $this->created = $created;
    }

    /**
     * @function getShiftMember
     * @access public
     * @returns $shift_member integer|tinyint|timestamp
     * Get ShiftMember
     */
    public function getShiftMember() {
        return $this->shift_member;
    }

    /**
     * @function setShiftMember
     * @access public
     * @param $shiftMember integer|tinyint|timestamp
     * Set ShiftMember
     */
    public function setShiftMember($shiftMember) {
        $this->shift_member = $shiftMember;
    }

    /**
     * @function getShiftOrgRequirements
     * @access public
     * @returns $shift_org_requirements integer|tinyint
     * Get ShiftOrgRequirements
     */
    public function getShiftOrgRequirements() {
        return $this->shift_org_requirements;
    }

    /**
     * @function setShiftOrgRequirements
     * @access public
     * @param $shiftOrgRequirements integer|tinyint
     * Set ShiftOrgRequirements
     */
    public function setShiftOrgRequirements($shiftOrgRequirements) {
        $this->shift_org_requirements = $shiftOrgRequirements;
    }

    /**
     * @function getShiftParticipant
     * @access public
     * @returns $shift_participant integer|tinyint|timestamp
     * Get ShiftParticipant
     */
    public function getShiftParticipant() {
        return $this->shift_participant;
    }

    /**
     * @function setShiftParticipant
     * @access public
     * @param $shiftParticipant integer|tinyint|timestamp
     * Set ShiftParticipant
     */
    public function setShiftParticipant($shiftParticipant) {
        $this->shift_participant = $shiftParticipant;
    }

    public function getShiftSite() {
        return $this->shift_site;
    }

    /**
     * @function setShiftParticipant
     * @access public
     * @param $shiftParticipant integer|tinyint|timestamp
     * Set ShiftParticipant
     */
    public function setShiftSite($shift_site) {
        $this->shift_site = $shift_site;
    }

    /**
     * @function getShiftPreferredMember
     * @access public
     * @returns $shift_preferred_member integer|tinyint|timestamp
     * Get ShiftPreferredMember
     */
    public function getShiftPreferredMember() {
        return $this->shift_preferred_member;
    }

    /**
     * @function setShiftPreferredMember
     * @access public
     * @param $shiftPreferredMember integer|tinyint|timestamp
     * Set ShiftPreferredMember
     */
    public function setShiftPreferredMember($shiftPreferredMember) {
        $this->shift_preferred_member = $shiftPreferredMember;
    }

    /**
     * @function ShiftRequirements
     * @access public
     * @returns $shift_requirements integer|tinyint
     * Get ShiftRequirements
     */
    public function getShiftRequirements() {
        return $this->shift_requirements;
    }

    /**
     * @function setShiftRequirements
     * @access public
     * @param $shiftRequirements integer|tinyint
     * Set ShiftRequirements
     */
    public function setShiftRequirements($shiftRequirements) {
        $this->shift_requirements = $shiftRequirements;
    }

    public function create_shift($reqData) {
        return $this->CI->Schedule_model->create_shift($reqData);
    }

    public function check_shift_exist(){
     return $this->CI->Schedule_model->check_shift_exist($this);
    }

    public function get_shift_details($multiple = false) {
        return $this->CI->CrmParticipant_model->get_shift_details($this->id, $multiple);
    }

    public function get_shift_participant() {
        return $this->CI->CrmParticipant_model->get_shift_participant($this->shiftId);
    }

    public function get_shift_location() {
        return $this->CI->Listing_model->get_shift_location($this->shiftId);
    }

    public function get_preferred_member() {
        return $this->CI->CrmParticipant_model->get_preferred_member($this->shiftId);
    }

    public function get_rejected_member() {
        return $this->CI->CrmParticipant_model->get_rejected_member($this->shiftId);
    }

    public function get_cancelled_details() {

        return $this->CI->CrmParticipant_model->get_cancelled_details($this->shiftId);
    }

    public function get_allocated_member() {
        return $this->CI->CrmParticipant_model->get_allocated_member($this->shiftId);
    }

    public function get_shift_requirement() {
        return $this->CI->CrmParticipant_model->get_shift_requirement($this->shiftId);
    }

    public function get_accepted_shift_member() {
        return $this->CI->CrmParticipant_model->get_accepted_shift_member($this->shiftId);
    }

    public function get_shift_oganization() {
        return $this->CI->CrmParticipant_model->get_shift_oganization($this->shiftId);
    }

    public function get_shift_confirmation_details() {
        return $this->CI->CrmParticipant_model->get_shift_confirmation_details($this->shiftId);
    }

    public function get_shift_caller() {
        return $this->CI->CrmParticipant_model->get_shift_caller($this->shiftId);
    }

//    public function get_available_member() {
//         return $this->CI->CrmParticipant_model->get_available_member($this->shiftId);
//    }

    public function get_available_member_by_city($shiftId, $shift_date, $shift_time, $pre_selected_member) {
        return $this->CI->CrmParticipant_model->get_available_member_by_city($shiftId, $shift_date, $shift_time, $pre_selected_member);
    }

    public function get_available_member_by_previous_work($memberId, $participantId) {
        return $this->CI->CrmParticipant_model->get_available_member_by_previous_work($memberId, $participantId);
    }

    public function get_available_member_by_preferences($by_city_members, $participantIds) {
        return $this->CI->CrmParticipant_model->get_available_member_by_preferences($by_city_members, $participantIds);
    }


    public function get_roster_listing($reqData, $participantId = false) {
        return $this->CI->Roster_model->get_active_roster($reqData, $participantId);
    }

    public function shiftCreateMail() {
        if ($this->booked_by == 2) {
            $participantData = $this->CI->basic_model->get_row('participant', array('firstname', 'lastname'), $where = array('id' => $this->shift_participant));
            $participantEmail = $this->CI->basic_model->get_row('participant_email', array('email'), $where = array('participantId' => $this->shift_participant, 'primary_email' => 1));


            if(!empty($participantEmail)){
                $userData['fullname'] = $participantData->firstname . ' ' . $participantData->lastname;
                $userData['email'] = $participantEmail->email;
                $userData['shift_date'] = DateFormate($this->shift_date, 'd-m-Y');
                $userData['start_time'] = DateFormate($this->start_time, 'h:i: a');
                $userData['end_time'] = DateFormate($this->end_time, 'h:i: a');

                shift_create_mail($userData);
            }
        }
    }

//    public function shiftChangeTimeMail() {
//        if ($this->booked_by == 2) {
//            $participantData = $this->CI->basic_model->get_row('participant', array('firstname', 'lastname'), $where = array('id' => $this->shift_participant));
//            $participantEmail = $this->CI->basic_model->get_row('participant_email', array('email'), $where = array('participantId' => $this->shift_participant, 'primary_email' => 1));
//
//
//            $userData['fullname'] = $participantData->firstname . ' ' . $participantData->lastname;
//            $userData['email'] = $participantEmail->email;
//            $userData['shift_date'] = DateFormate($this->shift_date, 'd-m-Y');
//            $userData['start_time'] = DateFormate($this->start_time, 'h:i: a');
//            $userData['end_time'] = DateFormate($this->end_time, 'h:i: a');
//
//            shift_create_mail($userData);
//        }
//    }

}
