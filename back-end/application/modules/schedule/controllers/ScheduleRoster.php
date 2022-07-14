<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ScheduleRoster extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model('Schedule_model');
        $this->load->model('Listing_model');
        $this->load->model('Roster_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;

        $this->loges->setLogType('roster');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function get_roster_requirements_for_participant() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data) && isset($reqData->data->participantId)) {
            $participantId = $reqData->data->participantId ?? 0;

            $data = $this->Schedule_model->get_shift_requirement_for_participant($participantId);
            $data['booker_list'] = $this->Schedule_model->get_booking_list($participantId);
            $data['participant_address'] = $this->Schedule_model->get_participant_address_by_participant_id($participantId);
            $data['plan_line_item'] = $this->Roster_model->get_participant_plan_line_item($participantId);

            echo json_encode(['status' => true, 'data' => $data]);
        }
    }

    public function get_participant_specific_plan_line_item() {
        require_once APPPATH . 'Classes/shift/Roster.php';
        $objRoster = new RosterClass\Roster();

        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $dates = $objRoster->getDateParticularDay($reqData);
          
            $reqData->start_time = $dates["start_time"];
            $reqData->end_time = $dates["end_time"];

            $reqData->day = (!empty($reqData->day)) ? [$reqData->day] : false;
            $res = $this->Schedule_model->get_user_specific_plan_line_item($reqData);

            echo json_encode(['status' => true, 'data' => $res]);
        }
        exit();
    }

    function mappingDate($shiftDates, $singleDate) {
        $matchDateKey = array();
        if (!empty($shiftDates)) {
            foreach ($shiftDates as $key => $val) {
                if ($val == $singleDate) {
                    $matchDateKey[] = $key;
                }
            }

            return $matchDateKey;
        }
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

    function check_roster_shift_list_data($d, $reqData) {
        $CI = & get_instance();
        $reqData = json_decode($reqData);
        $error_response = [];
        if (!empty($reqData->rosterList)) {
            foreach ($reqData->rosterList as $index => $week) {
                $week_number = $index + 1;

                foreach ($week as $day => $m_shift) {
                    foreach ($m_shift as $shift) {
                        if (!empty($shift->is_active)) {
                            $objVal = new MY_Form_validation();
                            $objVal->form_validation->CI = & $this;

                            $validation_rules = array(
                                array('field' => 'start_time', 'label' => 'start date', 'rules' => 'required'),
                                array('field' => 'end_time', 'label' => 'end date time', 'rules' => 'required'),
                                array('field' => 'booking_method', 'label' => 'booking for', 'rules' => 'required'),
                                array('field' => 'confirm_with_f_name', 'label' => 'confimation firstname', 'rules' => 'required'),
                                array('field' => 'confirm_with_l_name', 'label' => 'confimation lastname', 'rules' => 'required'),
                                array('field' => 'confirm_with_email', 'label' => 'confimation email', 'rules' => 'required|valid_email'),
                                array('field' => 'push_to_app', 'label' => 'push on app', 'rules' => 'required'),
                                array('field' => 'shift_note', 'label' => 'shift note', 'rules' => 'required'),
                                array('field' => 'autofill_shift', 'label' => 'auto fill shift', 'rules' => 'required'),
                            );

                            $objVal->set_data((array) $shift);
                            $objVal->set_rules($validation_rules);

                            if (!$objVal->run()) {
                                $errors = $objVal->error_array();
                                $err = implode(', ', $errors);
                                $this->form_validation->set_message('check_roster_shift_list_data', $err);
                                return false;
                            }
                        }
                    }
                }
            }
        } else {
            $this->form_validation->set_message('check_roster_shift_list_data', 'Please select at least one day to save roster');
            return false;
        }

        return true;
    }

    function validate_roster_data($reqestData) {
        $reqData = $reqestData->data;

        require_once APPPATH . 'Classes/shift/Roster.php';
        $objRoster = new RosterClass\Roster();

        $validation_rules = array(
            array('field' => 'start_date', 'label' => 'start date', 'rules' => 'required'),
            array('field' => 'is_default', 'label' => 'roster Type', 'rules' => 'required'),
            array('field' => 'userId', 'label' => 'participant', 'rules' => 'required'),
            array('field' => 'userId', 'label' => 'participant', 'rules' => 'callback_check_roster_shift_list_data[' . json_encode($reqData) . ']'),
        );

        if (!empty($reqData->is_default) && $reqData->is_default == 1) {
            $validation_rules[] = array('field' => 'end_date', 'label' => 'end date', 'rules' => 'required');
            $validation_rules[] = array('field' => 'title', 'label' => 'title', 'rules' => 'required');
        }

        $this->form_validation->set_data((array) $reqData);
        $this->form_validation->set_rules($validation_rules);

        if ($this->form_validation->run()) {

            if (!empty($reqData->rosterId)) {
                $objRoster->setRosterId($reqData->rosterId);
            }

            $shift_round = json_encode($objRoster->get_shift_round($reqData->rosterList));

            $objRoster->setParticipantId($reqData->userId);
            $objRoster->setStart_date(DateFormate($reqData->start_date, 'Y-m-d'));
            $objRoster->setCreated(DATE_TIME);
            $objRoster->setStatus(1);
            $objRoster->setShift_round($shift_round);
            $objRoster->setRosterList($reqData->rosterList);
            $objRoster->setCreated_type(2);
            $objRoster->setCreated_by($reqestData->adminId);

            $is_default = ($reqData->is_default == 1) ? 1 : 2;
            $objRoster->setIs_default($is_default);

            if ($is_default == 1) {
                $objRoster->setTitle($reqData->title);
                $objRoster->setEnd_date(DateFormate($reqData->end_date, 'Y-m-d'));
            }

            if ($objRoster->getIs_default() == 1) {
                $result = $objRoster->checkRosterAleadyExistThisDate();

                if (!empty($result)) {
                    echo json_encode(array('status' => false, 'error' => 'In this date another roster already exist'));
                    exit();
                }
            }

            $response = array('status' => true, 'objRoster' => $objRoster);
        } else {
            $errors = $this->form_validation->error_array();
            $response = array('status' => false, 'error' => implode(', ', $errors));
        }

        return $response;
    }

    function check_shift_collapse() {
        $reqestData = request_handler('create_schedule');
        $collapse_shifts_count = 0;

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;

            $resp = $this->validate_roster_data($reqestData);

            if ($resp['status']) {
                $objRoster = $resp['objRoster'];

                $new_roster_shifts = $objRoster->create_shift_from_roster();

                $new_shiftDate = array_column($new_roster_shifts, 'shift_date');
                $old_shifts = array();

                if (!empty($new_shiftDate)) {
                    $old_shifts = $objRoster->get_previous_shifts_who_collapse_by_date_time($new_shiftDate);
                }

                $collapse_shifts = array();
                $oldShiftDates = array_column($old_shifts, 'shift_date');

                $array_status = array(1 => 'Unfilled', 2 => 'Unconfirmed', 3 => 'Quote', 4 => 'Rejected', 5 => 'Cancelled', 7 => 'Confirmed');

                if (!empty($new_roster_shifts)) {

                    foreach ($new_roster_shifts as $key => $val) {
                        $collapse_shifts[$key]['is_collapse'] = false;

                        $responseMatchedDate = $this->mappingDate($oldShiftDates, $val['shift_date']);

                        if (!empty($responseMatchedDate)) {
                            $indexKey = 0;
                            foreach ($responseMatchedDate as $index => $old_shift_key) {

                                $o_str_time = $old_shifts[$old_shift_key]['start_time'];
                                $o_end_time = $old_shifts[$old_shift_key]['end_time'];

                                $condtion1 = (((strTime($val['start_time']) >= strTime($o_str_time)) && (strTime($o_str_time) <= strTime($val['end_time']))));
                                $condtion2 = (((strTime($val['start_time']) >= strTime($o_end_time)) && (strTime($o_end_time) <= strTime($val['end_time']))));


                                $condtion3 = (((strTime($o_str_time) >= strTime($val['start_time'])) && (strTime($val['start_time']) <= strTime($o_end_time))));
                                $condtion4 = (((strTime($o_str_time) >= strTime($val['end_time'])) && (strTime($val['end_time']) <= strTime($o_end_time))));


                                if ($condtion1 || $condtion2 || $condtion3 || $condtion4) {
                                    $collapse_shifts_count++;
                                    $collapse_shifts[$key]['is_collapse'] = true;
                                    $collapse_shifts[$key]['status'] = 2;


                                    $collapse_shifts[$key]['old'][$indexKey] = $old_shifts[$old_shift_key];
                                    $collapse_shifts[$key]['old'][$indexKey]['status'] = $array_status[$old_shifts[$old_shift_key]['status']];
                                    $collapse_shifts[$key]['old'][$indexKey]['active'] = false;
                                    $indexKey++;
                                }
                            }
                        }

                        $collapse_shifts[$key]['new'] = $val;

                        $startTime = new DateTime($val['start_time']);
                        $endTime = new DateTime($val['end_time']);
                        $interval = $endTime->diff($startTime);
                        $difference = $interval->format('%H.%i hrs.');


                        $collapse_shifts[$key]['new']['start_time'] = $val['start_time'];
                        $collapse_shifts[$key]['new']['end_time'] = $val['end_time'];
                        $collapse_shifts[$key]['new']['shift_date'] = $val['shift_date'];
                        $collapse_shifts[$key]['new']['duration'] = $difference;
                        $collapse_shifts[$key]['new']['status'] = 'Unfilled';
                    }
                }

                $response = array('status' => true, 'data' => $collapse_shifts, 'count' => $collapse_shifts_count);
            } else {
                $response = array('status' => false, 'error' => $resp['error']);
            }

            echo json_encode($response);
        }
    }

    public function create_roster() {
        $reqestData = request_handler('create_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if ($reqestData->data) {
            $reqData = $reqestData->data;
            $resp = $this->validate_roster_data($reqestData);

            if ($resp['status']) {
                $objRoster = $resp['objRoster'];
                $objRoster->setCollapse_shift($reqData->collapse_shift ?? []);


                if (!empty($reqData->rosterId)) {
                    return false;
                    // update roster
                    //$objRoster->update_roster($reqData);

                    $this->loges->setTitle('Update Roster : Roster Id ' . $objRoster->getRosterId());
                } else {
                    $objRoster->archive_previous_default_roster_when_create_new();

                    $objRoster->create_roster($reqData);

                    // create roster
                    $this->loges->setDescription(json_encode($reqestData->data));
                    $objRoster->rosterCreateMail();
                    $this->loges->setTitle('Added New Roster : Roster Id ' . $objRoster->getRosterId());
                }

                $this->loges->setUserId($objRoster->getRosterId());
                $this->loges->setLogType('roster');
                $this->loges->createLog();

                $return = array('status' => true);
            } else {
                $return = array('status' => false, 'error' => $resp['error']);
            }

            echo json_encode($return);
        }
    }

    public function get_roster_details() {
        $reqData = request_handler('access_schedule');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $result = $this->Roster_model->get_roster_details($reqData->rosterId);
            $participantId = $result['userId'];

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function approve_and_deny_roster() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);
        $this->loges->setLogType('roster');
        $this->load->model('Roster_model');

        $this->loges->setDescription(json_encode($reqestData));
        if ($reqestData->data) {
            $reqData = $reqestData->data;

            if ($reqData->status == 4) {
                $status_title = 'reject';
                $this->basic_model->update_records('participant_roster', array('status' => 4), $where = array('id' => $reqData->rosterId));
                $message = 'Roster reject successfully';
                $this->loges->setTitle('Roster rejected : Roster Id ' . $reqData->rosterId);
            } else {
                $status_title = 'approve successfully';
                // check that its tempary collapse shift
                $result = $this->Roster_model->getRosterTempData($reqData->rosterId);

                if (!empty($result)) {
                    $rosterData = json_decode($result->rosterData);
                    $this->resolve_collapse_shift_insert($rosterData, $result->participantId);
                }

                $this->loges->setTitle('Roster approve : Roster Id ' . $reqData->rosterId);
                $message = 'Roster approve successfully';
                if ($result->is_default == 0) {
                    // old defualt roster remove if new roster is default roster
                    $this->basic_model->update_records('participant_roster', array('status' => 5), $where = array('participantId' => $result->participantId, 'is_default' => 0));
                }
                $this->basic_model->update_records('participant_roster', array('status' => 1, 'updated' => DATE_TIME), $where = array('id' => $reqData->rosterId));
            }

            $this->loges->setUserId($reqData->rosterId);
            $this->loges->createLog();

            /* Notification */
            $title = 'Your roster request is ' . $status_title;
            $description = 'Roster is ' . $status_title . ' by admin, roster id .' . $reqData->rosterId;
            $notifi_data = ['user_type' => 2, 'sender_type' => 2, 'user_id' => $result->participantId, 'title' => $title, 'description' => $description];
            save_notification($notifi_data);
            /**/

            echo json_encode(array('status' => true, 'message' => $message));
        }
    }

    function get_manual_member_look_up_for_create_roster() {
        require_once APPPATH . 'Classes/shift/Roster.php';
        $objRoster = new RosterClass\Roster();

        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $reqData = $reqData->data;
            $shiftData = $reqData->extraParm ?? [];

            $booked_by = $shiftData->booked_by ?? 0;
            $address_ary = $shiftData->completeAddress ?? [];
            $line_items = $shiftData->selectedLineItemList ?? [];
            $funding_type = $shiftData->funding_type ?? 1;
            $time_of_days = $shiftData->time_of_days ?? [];


            $userId = $shiftData->userId;

            $shift_dates = $objRoster->getShiftDateParticularDay($reqData->extraParm);

            $startTime = isset($shiftData->start_time) && $shiftData->start_time != '' ? DateFormate($shiftData->start_time, DB_DATE_TIME_FORMAT) : '';
            $endTime = isset($shiftData->end_time) && $shiftData->end_time != '' ? DateFormate($shiftData->end_time, DB_DATE_TIME_FORMAT) : '';
            $shiftDate = isset($shiftData->start_time) && $shiftData->start_time != '' ? DateFormate($shiftData->start_time, DB_DATE_FORMAT) : '';

            if (empty($shiftData) || empty($userId) || empty($startTime) || empty($endTime) || empty($booked_by) || empty($address_ary) || empty($line_items)) {
                echo json_encode(array('status' => true, 'data' => []));
                exit();
            } else {
                $shiftDataIns = ['booked_by' => $booked_by, 'shift_date' => $shiftDate, 'start_time' => $startTime, 'end_time' => $endTime, 'funding_type' => $funding_type];
                $tempRequest = $this->Schedule_model->create_temp_shift_table(['shift_datetime' => $shift_dates, 'request_type' => 'create_roster', 'shift_data_ins' => $shiftDataIns, 'address_ary' => $address_ary, 'line_items' => $line_items, 'time_of_days' => $time_of_days]);
                $reqData->userId = $userId;
                
                $result = $this->Schedule_model->get_manual_member_look_up_for_create_shift($reqData, $tempRequest);
                echo json_encode(array('status' => true, 'data' => $result));
            }
        } else {
           
            echo json_encode(array('status' => true, 'data' => []));
        }
    }

}
