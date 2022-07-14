<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Shift_Schedule extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Schedule_model');
        $this->load->model('Listing_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;

        $this->loges->setLogType('shift');
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

    public function get_nearest_shift_member() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $post_data = $reqData->data;
            $result = $this->Schedule_model->get_nearest_shift_member($post_data);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function allot_member_to_shift() {
        $reqData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqData->adminId);
        $post_data = $reqData->data;

        if (!empty($post_data->shiftId)) {
            $memberAssign = array();

            if (!empty($post_data->allocate_to)) {
                $temp_ary = $notify_ary = [];
                foreach ($post_data->allocate_to as $val) {
                    if (!empty($val->access) && $val->access == 1) {
                        $memberAssign = array('shiftId' => $post_data->shiftId, 'memberId' => $val->memberId, 'status' => 1, 'created' => DATE_TIME, "updated" => DATE_TIME);

                        $this->loges->setUserId($post_data->shiftId);
                        $this->loges->setDescription(json_encode($val));
                        $this->loges->setTitle('Re-assign shift to member ' . $val->memberName . ' : Shift Id ' . $post_data->shiftId);
                        $this->loges->createLog();

                        $temp_ary['userId'] = $val->memberId;
                        $temp_ary['user_type'] = 1;
                        $temp_ary['title'] = 'New Shift is assign to you.';
                        $temp_ary['shortdescription'] = "Shift is re-assign to member (shift id = $post_data->shiftId).";
                        $temp_ary['created'] = DATE_TIME;
                        $temp_ary['sender_type'] = 2;
                        $notify_ary[] = $temp_ary;
                    }
                }
                if(!empty($notify_ary)){
                    $this->basic_model->insert_update_batch($action = 'insert', $table_name = 'notification', $notify_ary, $update_base_column_key = '');
                }
            }

            if (!empty($memberAssign)) {
                $this->basic_model->update_records('shift_member', array('archive' => 1, "updated" => DATE_TIME), $where = array('shiftId' => $post_data->shiftId, "status" => 1));
                $this->basic_model->insert_records('shift_member', $memberAssign, $multiple = false);

                $this->basic_model->update_records('shift', array('status' => 2), $where = array('id' => $post_data->shiftId));
                $return = array('status' => true);
            } else {
                $return = array('status' => true, 'error' => 'Please select at least one member');
            }
        } else {
            $return = array('status' => true, 'error' => 'Shift id is missing');
        }


        echo json_encode($return);
    }

    function assing_member_manual() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);
        $reqData = $reqestData->data;

        if (!empty($reqData->shiftId)) {
            $allocate_member = array();

            if (!empty($reqData->members)) {
                $temp_ary = $notify_ary = [];
                foreach ($reqData->members as $val) {
                    if (!empty($val->member->value)) {
                        $allocate_member[] = array('memberId' => $val->member->value, 'shiftId' => $reqData->shiftId, 'status' => 1, 'created' => DATE_TIME, 'assign_type' => 1);

                        $temp_ary['userId'] = $val->member->value;
                        $temp_ary['user_type'] = 1;
                        $temp_ary['title'] = 'New Shift is assign to you.';
                        $temp_ary['shortdescription'] = "New Shift is assign to you (shift id = $reqData->shiftId).";
                        $temp_ary['created'] = DATE_TIME;
                        $temp_ary['sender_type'] = 2;
                        $notify_ary[] = $temp_ary;

                        $this->loges->setUserId($reqData->shiftId);
                        $this->loges->setDescription(json_encode($reqData));
                        $this->loges->setTitle('Assign shift to member ' . $val->member->label . ' : Shift Id ' . $reqData->shiftId);
                        $this->loges->createLog();
                    }
                }
                if(!empty($notify_ary)){
                    $this->basic_model->insert_update_batch($action = 'insert', $table_name = 'notification', $notify_ary, $update_base_column_key = '');
                }
            }

            if (!empty($allocate_member)) {
                $this->basic_model->insert_records('shift_member', $allocate_member, $multiple = true);
                $this->basic_model->update_records('shift', array('status' => 2), $where = array('id' => $reqData->shiftId));
                $response = array('status' => true);
            } else {
                $response = array('status' => false, 'error' => 'please select at least one member to assign shift');
            }
        } else {
            $response = array('status' => false, 'error' => 'Shift id is missing');
        }

        echo json_encode($response);
    }

    function get_member_name() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $reqData = (array) $reqData->data;

            $result = $this->Schedule_model->get_member_name($reqData);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function get_short_shift_details() {
        $reqData = request_handler('access_schedule');

        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        if (!empty($reqData->data)) {
            $shiftId = $reqData->data->id;

            $objShift->setShiftId($shiftId);

            // get shift details
            $result = $objShift->get_shift_details();

            // if booked by 1 mean this booking by organization so get shift relation details get form organization
            if ($result['booked_by'] == 1) {
                // get shift organization
                $result['shift_organiztion_site'] = $objShift->get_shift_oganization();
            } else {
                // get shift participant
                $result['shift_participant'] = $objShift->get_shift_participant();
            }

            // get shift location
            $result['location'] = $objShift->get_shift_location();

            // if status 7 that mean shift is confirmed then get confirmed member details
            if ($result['status'] == 7) {
                $result['allocated_member'] = $objShift->get_accepted_shift_member();
            } elseif ($result['status'] == 2) {
                $result['allocated_member'] = $objShift->get_allocated_member();
            } else {
                $preffered_member = $objShift->get_preferred_member();
                if (!empty($preffered_member)) {
                    foreach ($preffered_member as $val) {
                        $val->select = array('value' => $val->memberId, 'label' => $val->memberName);
                    }
                }
                $result['preferred_member'] = $preffered_member;
            }

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function get_canceler_list() {
        $reqData = request_handler('access_schedule');

        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_canceler_list($reqData->data);

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function archive_shift_notes() {
        $reqData = request_handler('delete_schedule');

        if (!empty($reqData->data)) {
            $result = $this->basic_model->update_records('shift_notes', ['archive' => 1], ['id' => $reqData->data->id]);

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function get_manual_member_look_up_for_shift() {
        $reqData = request_handler('access_schedule');

        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_manual_member_look_up_for_shift($reqData->data);

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    function get_manual_member_look_up_for_create_shift() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            $shiftData = $reqData->data->extraParm ?? [];
            $booked_by = $shiftData->booked_by ?? 0;
            $address_ary = $shiftData->completeAddress ?? [];
            $line_items = $shiftData->selectedLineItemList?? [];
            $funding_type = $shiftData->funding_type ?? 1;
            $time_of_days = $shiftData->time_of_days ?? [];
            $userId=0;
            if($booked_by==2 ||$booked_by==3){
                $userId = $shiftData->participant_member_ary[0]->name->value ?? 0;
            }else if($booked_by==1){
                $userId = $shiftData->site_lookup_ary[0]->name->value ?? 0;
            }else if($booked_by==7){
                $userId = $shiftData->house_lookup_ary[0]->name->value ?? 0;
            }

            $startTime = isset($shiftData->start_time) && $shiftData->start_time!='' ? DateFormate($shiftData->start_time ,DB_DATE_TIME_FORMAT) :'';
            $endTime = isset($shiftData->end_time) && $shiftData->end_time!='' ? DateFormate($shiftData->end_time ,DB_DATE_TIME_FORMAT) :'';
            $shiftDate = isset($shiftData->start_time) && $shiftData->start_time!='' ? DateFormate($shiftData->start_time ,DB_DATE_FORMAT) :'';

            if(empty($shiftData) || empty($userId) || empty($startTime) || empty($endTime) || empty($booked_by) || empty($address_ary) || empty($line_items)){
                echo json_encode(array('status' => true, 'data' => []));
                exit();
            }else{
                $shiftDataIns = ['booked_by'=>$booked_by,'shift_date'=>$shiftDate,'start_time'=>$startTime,'end_time'=>$endTime,'funding_type'=>$funding_type];
                $tempRequest = $this->Schedule_model->create_temp_shift_table(['request_type'=>'create_shift','shift_data_ins'=>$shiftDataIns,'address_ary'=>$address_ary,'line_items'=>$line_items,'time_of_days'=>$time_of_days ]);
                $reqData->data->userId = $userId;
                $result = $this->Schedule_model->get_manual_member_look_up_for_create_shift($reqData->data,$tempRequest);
                echo json_encode(array('status' => true, 'data' => $result));
            }
        }else{
            echo json_encode(array('status' => true, 'data' => []));
        }
    }

}
