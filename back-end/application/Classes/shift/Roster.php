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

    const START_SHIFT_AFTER = 48; // Hours
    const DEFAULT_DAYS_SHIFT = 28; // Days

    public $CI;
    private $rosterId;
    private $participantId;
    private $title;
    private $shift_round;
    private $is_default;
    private $start_date;
    private $end_date;
    private $status;
    private $created_type;
    private $created_by;
    private $created;
    private $rosterList;
    private $collapse_shift;

    function __construct() {
        $this->CI = & get_instance();
        $this->CI->load->model('schedule/Roster_model');
        $this->CI->load->model('schedule/Make_shift_from_roster_model');
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

    function setCreated_type($created_type) {
        $this->created_type = $created_type;
    }

    function getCreated_type() {
        return $this->created_type;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    function setRosterList($rosterList) {
        $this->rosterList = $rosterList;
    }

    function getRosterList() {
        return $this->rosterList;
    }

    function setCollapse_shift($collapse_shift) {
        $this->collapse_shift = $collapse_shift;
    }

    function getCollapse_shift() {
        return $this->collapse_shift;
    }

    function get_shift_round($rosterList) {
        $shift_round = array();
        if (!empty($rosterList)) {
            foreach ($rosterList as $key => $val) {
                $shift_round[] = $key + 1;
            }
        }
        return $shift_round;
    }

    function dateRangeBetweenDateWithWeek($start_date, $end_date, $totalWeek) {
        $first = $start_date;
        $last = $end_date;

        $dates = array();
        $step = '+1 day';
        $format = 'Y-m-d';
        $current = strtotime($first);
        $last = strtotime($last);
        $weekNumber = 0;
        $cnt = count($totalWeek);

        $weekCount = 1;
        while ($current <= $last) {

            $date1 = date($format, $current);
            if (($weekNumber) == $cnt) {
                $weekNumber = 0;
            }

            $dates[$totalWeek[$weekNumber]][$weekCount][$date1] = date('D', $current);

            if (date('D', $current) == 'Sun') {
                $weekNumber++;
                $weekCount++;
            }

            $current = strtotime($step, $current);
        }
        return $dates;
    }

    function getShiftDateParticularDay($reqData) {
        $is_default = (!empty($reqData->is_default) && $reqData->is_default == 2) ? 2 : 1;

        if ($is_default == 1) {
            $end_date = DateFormate($reqData->end_date, 'Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($reqData->start_date . ' + ' . self::DEFAULT_DAYS_SHIFT . ' days'));
        }

        $after_create_shift = date('Y-m-d H:i:s', strtotime(DATE_TIME . " +" . self::START_SHIFT_AFTER . " hours"));

        $first = $reqData->start_date;
        $last = $end_date;

        $step = '+1 day';
        $format = 'Y-m-d';
        $current = strtotime($first);
        $last = strtotime($last);
        $weekNumber = 0;
        $cnt = 4;

        $weekCount = 1;
        $dates = [];
        while ($current <= $last) {

            $date1 = date($format, $current);
            if (($weekNumber) == $cnt) {
                $weekNumber = 0;
            }

            if (strtolower(date('D', $current)) === strtolower($reqData->day)) {
                $start_time = DateFormate($date1 . ' ' . DateFormate($reqData->start_time, "H:i"));
                $end_time = DateFormate($date1 . ' ' . DateFormate($reqData->end_time, "H:i"));
                $dates[] = ['start_time' => $start_time, 'end_time' => $end_time];
            }

            if (date('D', $current) == 'Sun') {
                $weekNumber++;
                $weekCount++;
            }

            $current = strtotime($step, $current);
        }
        return $dates;
    }

    function getDateParticularDay($reqData) {
        $is_default = (!empty($reqData->is_default) && $reqData->is_default == 2) ? 2 : 1;

        if ($is_default == 1) {
            $end_date = DateFormate($reqData->end_date, 'Y-m-d');
        } else {
            $end_date = date('Y-m-d', strtotime($reqData->start_date . ' + ' . self::DEFAULT_DAYS_SHIFT . ' days'));
        }

        $after_create_shift = date('Y-m-d H:i:s', strtotime(DATE_TIME . " +" . self::START_SHIFT_AFTER . " hours"));

        $first = DateFormate($reqData->start_date, 'Y-m-d');
        $last = $end_date;

        $step = '+1 day';
        $format = 'Y-m-d';
        $current = strtotime($first);
        $last = strtotime($last);
        $weekNumber = 0;
        $cnt = 4;

        $weekCount = 1;
        $start_time = '';
        $end_time = '';
        while ($current <= $last) {

            $date1 = date($format, $current);
            if (($weekNumber) == $cnt) {
                $weekNumber = 0;
            }

            if (strtolower(date('D', $current)) === strtolower($reqData->day)) {

                if (!$start_time) {
                    $start_time = DateFormate($date1 . ' ' . DateFormate($reqData->start_time, "H:i"));
                }
                $end_time = DateFormate($date1 . ' ' . DateFormate($reqData->end_time, "H:i"));
            }

            if (date('D', $current) == 'Sun') {
                $weekNumber++;
                $weekCount++;
            }

            $current = strtotime($step, $current);
        }
        return ["start_time" => $start_time, "end_time" => $end_time];
    }

    public function create_roster($reqData) {
        return $this->CI->Roster_model->create_roster($this, $reqData);
    }

    public function update_roster($reqData) {
        return $this->CI->Roster_model->update_roster($this, $reqData);
    }

    function rosterCreateMail() {
        $participantData = $this->CI->basic_model->get_row('participant', array("concat_ws(' ',firstname,lastname) as fullname"), $where = array('id' => $this->participantId));
        $participantEmail = $this->CI->basic_model->get_row('participant_email', array('email'), $where = array('participantId' => $this->participantId, 'primary_email' => 1));

        $userData['fullname'] = $participantData->fullname ?? '';
        $userData['email'] = $participantEmail->email ?? '';

        $userData['start_date'] = DateFormate($this->start_date, 'd-m-Y');
        $userData['is_default'] = $this->is_default;

        if ($this->is_default == 1) {
            $userData['end_date'] = DateFormate($this->end_date, 'd-m-Y');
            $userData['title'] = $this->title;
        }

        if (!empty($userData['email'])) {
            roster_create_mail($userData);
        }
    }

    function checkRosterAleadyExistThisDate() {
        return $this->CI->Roster_model->check_roster_start_end_date_already_exist($this);
    }

    function create_shift_from_roster() {
        $shiftRoster = array();

        $rosterList = $this->rosterList;

        $rosters = [];
        foreach ($rosterList as $key => $weekData) {
            $week_number = ($key + 1);
            $dayCounter = 1;
            foreach ($weekData as $day => $multipleShift) {
                foreach ($multipleShift as $val) {

                    $updated_shift = (!empty($val->updated_shift)) ? true : false;
                    $roster_shiftId = $val->roster_shiftId ?? '';

                    if ($val->is_active && $val->in_funding && (!$roster_shiftId || $updated_shift)) {
                        $val->start_time = date('Y-m-d H:i:s', strtotime($val->start_time));
                        $val->end_time = date('Y-m-d H:i:s', strtotime($val->end_time));

                        $rosters[$week_number][numberToDay($dayCounter)][] = $val;
                    }
                }
                $dayCounter++;
            }
        }

        $newRosterData = obj_to_arr($rosters);

        if ($this->is_default == 1) {
            $shiftCreateEndDate = DateFormate($this->end_date, 'Y-m-d');
        } else {
            $shiftCreateEndDate = date('Y-m-d', strtotime($this->start_date . ' + ' . self::DEFAULT_DAYS_SHIFT . ' days'));
        }

        $date_range = $this->dateRangeBetweenDateWithWeek($this->start_date, $shiftCreateEndDate, json_decode($this->shift_round));
        $after_create_shift = date('Y-m-d H:i:s', strtotime(DATE_TIME . " +" . self::START_SHIFT_AFTER . " hours"));

        if (!empty($date_range)) {
            foreach ($date_range as $week_number => $week) {
                foreach ($week as $days) {
                    foreach ($days as $date => $day) {

                        if (array_key_exists($week_number, $newRosterData)) {

                            if (array_key_exists($day, $newRosterData[$week_number])) {

                                for ($i = 0; $i < count($newRosterData[$week_number][$day]); $i++) {
                                    $start_time = $newRosterData[$week_number][$day][$i]['start_time'];
                                    $end_time = $newRosterData[$week_number][$day][$i]['end_time'];

                                    $start_time = DateFormate(DateFormate($date, "Y-m-d") . DateFormate($start_time, "H:i:s"), "Y-m-d H:i:s");
                                    $end_time = DateFormate(DateFormate($date, "Y-m-d") . DateFormate($end_time, "H:i:s"), "Y-m-d H:i:s");


                                    if (strtotime($start_time) > strtotime($after_create_shift)) {
                                        $shift = array(
                                            'booked_by' => 2,
                                            'shift_date' => $date,
                                            'start_time' => $start_time,
                                            'end_time' => $end_time,
                                            'status' => 1,
                                            "week_number" => $week_number,
                                            "day" => $day,
                                            "index" => $i
                                        );

                                        $shiftRoster[] = $shift;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $shiftRoster;
    }

    function get_previous_shifts_who_collapse_by_date_time($shift_dates) {
        return $this->CI->Roster_model->get_previous_shifts_who_collapse_by_date_time($this, $shift_dates);
    }

    function save_shift_who_added_in_roster() {
        return $this->CI->Roster_model->save_shift_who_added_in_roster($this);
    }

    function make_shift_from_roster() {
        return $this->CI->Make_shift_from_roster_model->make_shift_from_roster($this);
    }

    function archive_previous_default_roster_when_create_new() {
        if ($this->getIs_default() == 2) {
            // old defualt roster remove if new roster is default roster
            $this->CI->basic_model->update_records('roster', array('status' => 5, 'updated' => DATE_TIME), $where = array('userId' => $this->getParticipantId(), 'is_default' => 2));
        }
    }

    function archive_roster_shift($roster_shiftIds) {
        return $this->CI->Roster_model->archive_roster_shift($roster_shiftIds);
    }

}
