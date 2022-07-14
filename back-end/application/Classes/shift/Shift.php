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
        $this->CI->load->model('schedule/Schedule_model');
        $this->CI->load->model('schedule/Listing_model');
        $this->CI->load->model('schedule/Basic_model');
        $this->CI->load->model('schedule/Roster_model');
    }

    /**
     * @var shiftid
     * @access private
     * @vartype: integer
     */
    private $shiftId;

    /**
     * @var booked_by
     * @access private
     * @vartype: tinyint
     */
    private $booked_by;

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
     * @var status
     * @access private
     * @vartype: tinyint
     * used in notification
     */
    private $shift_status;

    /**
     * @var created
     * @access private
     * @vartype: timestamp
     */
    private $created;

    /**
     * @array shift_member
     * @access private
     * @vartype: integer|tinyint|timestamp
     */
    private $shift_member = array();
    private $shift_site = array();
    private $shift_house = array();

    /**
     * @array shift_org_requirements
     * @access private
     * @vartype: integer|tinyint
     */
    private $shift_org_requirements = array();

    /**
     * @array shift_participant
     * @access private
     * @vartype: integer|tinyint|timestamp
     */
    private $shift_participant = array();

    /**
     * @array shift_preferred_member
     * @access private
     * @vartype: integer|tinyint|timestamp
     */
    private $shift_preferred_member = array();

    /**
     * @array shift_requirements
     * @access private
     * @vartype: integer|tinyint
     */
    private $gst;
    private $sub_total;
    private $is_quoted;
    private $created_by;
    private $funding_type;
    private $shift_requirements = array();
    private $pre_selected_member = [];
    private $required_paypoint;
    private $required_level;
    private $userId;
    private $available_member_order;
    private $limit;
    private $request_for;

    /**
     * @string shiftTable
     * @access private
     * @vartype: string
     */
    private $shiftTimeCategoryTableName = 'shift_time_category';

    /**
     * @string shiftTable
     * @access private
     * @vartype: string
     */
    private $shiftTableName = 'shift';

    /**
     * @string shiftTable
     * @access private
     * @vartype: string
     */
    private $shiftLocationTableName = 'shift_location';

    /**
     * @string shiftTable
     * @access private
     * @vartype: string
     */
    private $shiftLineItemTableName = 'shift_line_item_attached';
    private $memberNameSearch = '';

    /**
     * @function getShiftid
     * @access public
     * @returns $shiftid integer
     * Get Shift Id
     */
    public function getShiftId() {
        return $this->shiftId;
    }

    /**
     * @function setShiftid
     * @access public
     * @param $shiftid integer 
     * Set Shift Id
     */
    public function setShiftId($shiftId) {
        $this->shiftId = $shiftId;
    }

    /**
     * @function getBookedBy
     * @access public
     * @returns $booked_by tinyint
     * Get BookedBy 
     */
    public function getBookedBy() {
        return $this->booked_by;
    }

    /**
     * @function setBookedBy
     * @access public
     * @param $bookedBy tinyint 
     * Set BookedBy
     */
    public function setBookedBy($bookedBy) {
        $this->booked_by = $bookedBy;
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
     * @function getPrice
     * @access public
     * @returns $price double
     * Get Price 
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @function setPrice
     * @access public
     * @param $price double 
     * Set Price
     */
    public function setPrice($price) {
        $this->price = $price;
    }

    /**
     * @function getAllocatePreMember
     * @access public
     * @returns $allocate_pre_member tinyint
     * Get AllocatePreMember 
     */
    public function getAllocatePreMember() {
        return $this->allocate_pre_member;
    }

    /**
     * @function setAllocatePreMember
     * @access public
     * @param $allocatePreMember tinyint 
     * Set AllocatePreMember
     */
    public function setAllocatePreMember($allocatePreMember) {
        $this->allocate_pre_member = $allocatePreMember;
    }

    /**
     * @function getAutofillShift
     * @access public
     * @returns $autofill_shift tinyint
     * Get AutofillShift 
     */
    public function getAutofillShift() {
        return $this->autofill_shift;
    }

    /**
     * @function setAutofillShift
     * @access public
     * @param $autofillShift tinyint 
     * Set AutofillShift
     */
    public function setAutofillShift($autofillShift) {
        $this->autofill_shift = $autofillShift;
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

    public function getShiftHouse() {
        return $this->shift_house;
    }

    /**
     * @function setShiftParticipant
     * @access public
     * @param $shiftParticipant integer|tinyint|timestamp
     * Set ShiftParticipant
     */
    public function setShiftHouse($shift_house) {
        $this->shift_house = $shift_house;
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

    function setPre_selected_member($pre_selected_member) {
        $this->pre_selected_member = $pre_selected_member;
    }

    function getPre_selected_member() {
        return $this->pre_selected_member;
    }

    function setRequired_paypoint($required_paypoint) {
        $this->required_paypoint = $required_paypoint;
    }

    function getRequired_paypoint() {
        return $this->required_paypoint;
    }

    function setRequired_level($required_level) {
        $this->required_level = $required_level;
    }

    function getRequired_level() {
        return $this->required_level;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function setAvailable_member_order($available_member_order) {
        $this->available_member_order = $available_member_order;
    }

    function getAvailable_member_order() {
        return $this->available_member_order;
    }

    function setLimit($limit) {
        $this->limit = $limit;
    }

    function getLimit() {
        return $this->limit;
    }

    function setGst($gst) {
        $this->gst = $gst;
    }

    function getGst() {
        return $this->gst;
    }

    function setSub_total($sub_total) {
        $this->sub_total = $sub_total;
    }

    function getSub_total() {
        return $this->sub_total;
    }

    function setIs_quoted($is_quoted) {
        $this->is_quoted = $is_quoted;
    }

    function getIs_quoted() {
        return $this->is_quoted;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    function setFunding_type($funding_type) {
        $this->funding_type = $funding_type;
    }

    function getFunding_type() {
        return $this->funding_type;
    }

    function setMemeberNameSearch($Search = '') {
        $this->memberNameSearch = $Search;
    }

    function getMemeberNameSearch() {
        return $this->memberNameSearch;
    }

    function setRequest_for($request_for) {
        $this->request_for = $request_for;
    }

    function getRequest_for() {
        return $this->request_for;
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

    /**
     * @function ShiftTable
     * @access public
     * @returns $shift_requirements integer|tinyint
     * Get ShiftTableName
     */
    public function getShiftTableName($preFix = false) {
        return $preFix ? TBL_PREFIX . $this->shiftTableName : $this->shiftTableName;
    }

    /**
     * @function setShiftTableName
     * @access public
     * @param $ShiftTableName integer|tinyint
     * Set ShiftTableName
     */
    public function setShiftTableName($tableName = '') {
        $find = TBL_PREFIX;
        $replace = '';
        $result = preg_replace("/$find/", $replace, $tableName, 1);
        $this->shiftTableName = $result;
    }

    /**
     * @function ShiftTable
     * @access public
     * @returns $shift_requirements integer|tinyint
     * Get ShiftTableName
     */
    public function getShiftLocationTableName($preFix = false) {
        return $preFix ? TBL_PREFIX . $this->shiftLocationTableName : $this->shiftLocationTableName;
    }

    /**
     * @function setShiftTableName
     * @access public
     * @param $ShiftTableName integer|tinyint
     * Set ShiftTableName
     */
    public function setShiftLocationTableName($tableName = '') {
        $find = TBL_PREFIX;
        $replace = '';
        $result = preg_replace("/$find/", $replace, $tableName, 1);
        $this->shiftLocationTableName = $result;
    }

    /**
     * @function ShiftTable
     * @access public
     * @returns $shift_requirements integer|tinyint
     * Get ShiftTableName
     */
    public function getShiftLineItemTableName($preFix = false) {
        return $preFix ? TBL_PREFIX . $this->shiftLineItemTableName : $this->shiftLineItemTableName;
    }

    /**
     * @function setShiftTableName
     * @access public
     * @param $ShiftTableName integer|tinyint
     * Set ShiftTableName
     */
    public function setShiftLineItemTableName($tableName = '') {
        $find = TBL_PREFIX;
        $replace = '';
        $result = preg_replace("/$find/", $replace, $tableName, 1);
        $this->shiftLineItemTableName = $result;
    }

    /**
     * @function ShiftTable
     * @access public
     * @returns $shift_requirements integer|tinyint
     * Get ShiftTableName
     */
    public function getShiftTimeCategoryTableName($preFix = false) {
        return $preFix ? TBL_PREFIX . $this->shiftTimeCategoryTableName : $this->shiftTimeCategoryTableName;
    }

    /**
     * @function setShiftTableName
     * @access public
     * @param $ShiftTableName integer|tinyint
     * Set ShiftTableName
     */
    public function setShiftTimeCategoryTableName($tableName = '') {
        $find = TBL_PREFIX;
        $replace = '';
        $result = preg_replace("/$find/", $replace, $tableName, 1);
        $this->shiftTimeCategoryTableName = $result;
    }

    public function create_shift($reqData, $adminId) {
        return $this->CI->Schedule_model->create_shift($reqData, $adminId);
    }

    public function check_shift_exist() {
        return $this->CI->Schedule_model->check_shift_exist($this);
    }

    public function get_shift_details($multiple = false) {
        return $this->CI->Listing_model->get_shift_details($this->shiftId, $multiple);
    }

    public function get_shift_participant() {
        return $this->CI->Listing_model->get_shift_participant($this->shiftId);
    }

    public function get_shift_house() {
        return $this->CI->Listing_model->get_shift_house($this->shiftId);
    }

    public function get_shift_location() {
        return $this->CI->Listing_model->get_shift_location($this->shiftId);
    }

    public function get_preferred_member() {
        return $this->CI->Listing_model->get_preferred_member($this->shiftId);
    }

    public function get_rejected_member() {
        return $this->CI->Listing_model->get_rejected_member($this->shiftId);
    }

    public function get_cancelled_details() {

        return $this->CI->Listing_model->get_cancelled_details($this->shiftId);
    }

    public function get_allocated_member() {
        return $this->CI->Listing_model->get_allocated_member($this->shiftId);
    }

    public function get_shift_requirement() {
        return $this->CI->Listing_model->get_shift_requirement($this->shiftId, $this->booked_by);
    }

    public function get_accepted_shift_member() {
        return $this->CI->Listing_model->get_accepted_shift_member($this->shiftId);
    }

    public function get_shift_oganization() {
        return $this->CI->Listing_model->get_shift_oganization($this->shiftId);
    }

    public function get_shift_confirmation_details() {
        return $this->CI->Listing_model->get_shift_confirmation_details($this->shiftId);
    }

    public function get_shift_caller() {
        return $this->CI->Listing_model->get_shift_caller($this->shiftId);
    }

//    public function get_available_member() {
//         return $this->CI->Listing_model->get_available_member($this->shiftId);
//    }

    public function get_available_member_by_city($equal_and_greater) {
        return $this->CI->Listing_model->get_available_member_by_city($this, $equal_and_greater);
    }

    public function get_available_member_by_preferences($by_city_members, $participantIds) {
        return $this->CI->Listing_model->get_available_member_by_preferences($by_city_members, $participantIds);
    }

    public function get_prefference_activity_places($memberId, $participantId) {
        return $this->CI->Listing_model->get_prefference_activity_places($memberId, $participantId);
    }

    public function get_member_details($memberId) {
        return $this->CI->Listing_model->get_member_details($memberId);
    }

    public function get_roster_listing($reqData, $participantId = false) {
        return $this->CI->Roster_model->get_active_roster($reqData, $participantId);
    }

    public function get_shift_status() {
        return $this->shift_status;
    }
    public function set_shift_status($shift_status) {
        $this->shift_status = $shift_status;
    }

    public function shiftCreateMail() {
        if ($this->booked_by == 2) {
            $participantData = $this->CI->basic_model->get_row('participant', array("concat_ws(' ',firstname,lastname) as fullname"), $where = array('id' => $this->userId));
            $participantEmail = $this->CI->basic_model->get_row('participant_email', array('email'), $where = array('participantId' => $this->userId, 'primary_email' => 1));


            if (!empty($participantEmail)) {
                $userData['fullname'] = $participantData->fullname ?? '';
                $userData['email'] = $participantEmail->email ?? '';
                $userData['shift_date'] = DateFormate($this->shift_date, 'd-m-Y');
                $userData['start_time'] = DateFormate($this->start_time, 'h:i: a');
                $userData['end_time'] = DateFormate($this->end_time, 'h:i: a');

                if (!empty($userData['email'])) {
                    shift_create_mail($userData);
                }
            }
        }
    }

    function archive_shift($archive_shift_ids) {
        $archive_shift_ids = !empty($archive_shift_ids) ? $archive_shift_ids : 0;
        $archive_shift_ids = (is_array($archive_shift_ids)) ? $archive_shift_ids : [$archive_shift_ids];

        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';

        if (!empty($archive_shift_ids)) {

            $objTran = new \LineItemTransactionHistory();
            $objTran->setShiftId($archive_shift_ids);
            $objTran->relese_fund_by_shiftId();


            $this->CI->Schedule_model->archive_shift($archive_shift_ids);
        }
    }

    public function get_shift_detail_with_required_level_and_paypoint($multiple = false) {
        $other = [
            'required_level_and_paypoint' => true,
            'multiple' => $multiple,
            'table_name_shift' => $this->getShiftTableName(true),
            'table_name_line_item' => $this->getShiftLineItemTableName(true),
        ];
        return $this->CI->Listing_model->get_shift_details($this->shiftId, $other);
    }

    public function get_shift_detail_with_user($multiple = false) {
        $other = [
            'multiple' => $multiple,
            'shift_for' => true
        ];
        return $this->CI->Listing_model->get_shift_details($this->shiftId, $other);
    }

    function send_notification_to_booked_for() {
        #quote for all 3 type of booked by
        #create shift for all 3 type of booked by

        $shift_id = $this->shiftId;
        $user_id = $this->userId;
        $sender_type = 2;

        if ($this->booked_by == 7) {
            $user_type = 4;
        }else if ($this->booked_by == 2) {
            $user_type = 2;
        }else if ($this->booked_by == 1) {
            $user_type = 3;
        }
       
        if ($this->shift_status == 3) {         
            $title = $description = 'New Shift save as Quote, shift id:- ' . $shift_id;
        } else {
         $title = "New shift is created (Shift id $shift_id)";
         if ($this->booked_by == 7) {
            $description = "New shift is created for House = " . $user_id . " And shift Id is " . $shift_id;
        } else if ($this->booked_by == 2) {
            $description = "New shift is created for Participant = " . $user_id . " And shift Id is " . $shift_id;
        } else if ($this->booked_by == 1) {                
            $description = "New shift is created for Site = " . $user_id . " And shift Id is " . $shift_id;
        }
    }

    $notifi_data = ['user_type' => $user_type, 'sender_type' => $sender_type, 'user_id' => $user_id, 'title' => $title, 'description' => $description];
    #method define in hcm_helper
    save_notification($notifi_data);
}

}
