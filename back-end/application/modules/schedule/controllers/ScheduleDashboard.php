<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ScheduleDashboard extends MX_Controller {

    use formCustomValidation;


    function __construct() {
        parent::__construct();
        $this->load->model('Schedule_model');
        $this->load->model('Listing_model');
        $this->load->model('common/List_view_controls_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;

        $this->loges->setLogType('shift');
        $this->date_fields = [
            'scheduled_start_datetime',
            'scheduled_end_datetime'
        ];
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

    /**
     * fetches all the final shift statuses for shift details page
     */
    function get_shift_statuses_final() {
        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $response = $this->Schedule_model->get_shift_statuses_final();
            echo json_encode($response);
        }
        exit(0);
    }

    /**
     * fetches all the shift statuses grouped for shift details page
     */
    function get_shift_statuses_grouped() {
        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $response = $this->Schedule_model->get_shift_statuses_grouped();
            echo json_encode($response);
        }
        exit(0);
    }

    /**
     * fetching the shifts of accounts that their timesheets are paid and not yet invoiced
     */
    public function get_paid_non_invoice_shifts() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Schedule_model->get_paid_non_invoice_shifts($data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }


    /**
     * fetches all the shift statuses
     */
    function get_shift_statuses() {
        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $response = $this->Schedule_model->get_shift_statuses();
            echo json_encode($response);
        }
        exit(0);
    }

    /**
     * fetches cancel and lost reason options from the reference list
     */
    function get_shift_cancel_reason_option() {
        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $response = $this->Schedule_model->get_shift_cancel_reason_option();
            echo json_encode(['status' => true, 'data' => $response]);
        }
        exit(0);
    }

    function get_create_shift_option() {
        request_handler('create_schedule');

        $res['stateList'] = $this->basic_model->get_record_where('state', ['id as value', 'name as label'], '');
        $res['time_of_days'] = $this->basic_model->get_record_where('finance_time_of_the_day', ['id', 'short_name as name', 'key_name'], '');
        $resSupportCategory = $this->basic_model->get_record_where_orderby('finance_support_category', ['id as value', 'name as label'], ['archive' => 0], 'name', 'ASC');
        $resDefault = [['value' => '', 'label' => 'Select Support Category']];
        $resSupportCategoryData = !empty($resSupportCategory) ? array_merge($resDefault, $resSupportCategory) : $resDefault;
        $res['support_line_item_category'] = $resSupportCategoryData;

        echo json_encode(['status' => true, 'data' => $res]);
    }

    function get_user_specific_plan_line_item() {
        $reqData = request_handler('create_schedule');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $user_type = $reqData->booked_by == 3 ? 2 : $reqData->booked_by;
            if ($user_type == 1) {
                $reqData->userId = (!empty($reqData->site_lookup_ary[0]->name->value)) ? $reqData->site_lookup_ary[0]->name->value : 0;
            } else if ($user_type == 7) {
                $reqData->userId = (!empty($reqData->house_lookup_ary[0]->name->value)) ? $reqData->house_lookup_ary[0]->name->value : 0;
            } else {
                $reqData->userId = (!empty($reqData->participant_member_ary[0]->name->value)) ? $reqData->participant_member_ary[0]->name->value : 0;
            }

            $start_time = !empty($reqData->start_time) ? DateFormate($reqData->start_time, "Y-m-d H:i:s") : '';
            $end_time = !empty($reqData->end_time) ? DateFormate($reqData->end_time, "Y-m-d H:i:s") : '';

            if ($start_time && $end_time) {
                $first_day = DateFormate($start_time, "D");
                $seconday_day = DateFormate($end_time, "D");

                $reqData->day = ($first_day === $seconday_day) ? [$first_day] : [$first_day, $seconday_day];
            }

            $res = $this->Schedule_model->get_user_specific_plan_line_item($reqData);

            echo json_encode(['status' => true, 'data' => $res]);
        }
        exit();
    }

    /*
     * its used for gettting account or participant name on base of @param $ownerName
     */
    public function account_participant_name_search()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            return false;
        }

        $skip_sites =isset($reqData->data->skip_sites) ? true : false;
        $data = $this->Schedule_model->account_participant_name_search($reqData->data->query, $skip_sites);
        echo json_encode($data);
        exit();
    }


    /*
     * its used for gettting site name on base of contact
     */
    public function contact_site_name_search()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            return false;
        }

        $data = $this->Schedule_model->contact_site_name_search($reqData->data);
        echo json_encode($data);
        exit();
    }

    /*
     * its used for gettting address
     */
    public function get_address_for_account()
    {

        $reqData = request_handler();
        //print_r($reqData);exit();
        if (empty($reqData->data)) {
            return false;
        }

        $data = $this->Schedule_model->get_address_for_account(
            $reqData->data->value,$reqData->data->account_type);
        echo json_encode($data);
        exit();
    }

    /*
     * fetching shifts list based on the searched keyword
     */
    public function get_shift_name_search()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            $result = ['status' => false, 'error' => 'Requested data is null'];
            echo json_encode($result);
            exit();
        }

        $data = $this->Schedule_model->get_shift_name_search($reqData->data->query);
        echo json_encode($data);
        exit();
    }

    /**
     * based on the weekly selections of selected shifts
     * copying the shifts into those selections
     */
    public function copy_shift_weekly_intervals() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        $uuid_user_type = $reqData->uuid_user_type;
        if (empty($reqData->data)) {
            $result = ['status' => false, 'error' => 'Requested data is null'];
            echo json_encode($result);
            exit();
        }
        $data = (array) $reqData->data;
        $result = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($data, $adminId, $uuid_user_type);
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the weekly intervals based on the shifts selection
     */
    public function get_copy_shift_intervals() {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            $result = ['status' => false, 'error' => 'Requested data is null'];
            echo json_encode($result);
            exit();
        }
        $data = (array) $reqData->data;
        $result = $this->Schedule_model->get_copy_shift_intervals($data);
        echo json_encode($result);
        exit();
    }

    /*
     * For getting shifts list
     */
    function get_shifts_list() {
        $reqData = request_handler();
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            foreach ($reqData->data->tobefilterdata as $filter) {
                if (in_array($filter->select_filter_field_val, $this->date_fields) && strpos($filter->select_filter_value, '/') !== false) {
                    $parts = explode(' ', $filter->select_filter_value);
                    $dt_parts = explode('/', $parts[0]);
                    $date_time = $dt_parts[2] . '-' . $dt_parts[1] . '-' . $dt_parts[0];
                    if (!empty($parts[1])) {
                        $date_time .= ' ' . $parts[1];
                    } else {
                        $date_time .= ' 00:00:00';
                    }
                    $filter->select_filter_value = $date_time;
                }
            }
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            $account_fullname_cond = "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) ";
            $filter_condition = str_replace(["id", 'account_fullname', 'role_name', 'shift_no', 'status_label', 'roster_no', 'day_of_week'], ["s.id", $account_fullname_cond, 'r.name', 's.shift_no', 's.status', 'ros.roster_no', 'DAYNAME(s.scheduled_start_datetime)' ], $filter_condition);

        }
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_shifts_list($reqData->data, $filter_condition, $reqData->adminId, $reqData->uuid_user_type);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the shift status.
     */
    public function update_shift_status() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = (array) $reqData->data;
        $response = $this->Schedule_model->update_shift_status($data, $adminId, true);
        echo json_encode($response);
        exit();
    }
    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_break_duration() {
        $reqData = request_handler('create_schedule');
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->calculate_break_duration((array) $reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_shift_duration() {
        $reqData = request_handler('create_schedule');
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->calculate_shift_duration(object_to_array($reqData->data));
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        /*
        * start_time actual or schedule has undefined as value means it returns long duration
          like 455700 to restrict that returning 00:00 
        */
         $hours =  explode(':', $result['data']);
         if($hours[0]>1000)
         {
            $result['data']='00:00';
         } 
        echo json_encode($result);
        exit();
    }

    /*
    * get shift break types from reference list
    */
    function get_shift_break_types() {
        $reqData = request_handler('create_schedule');
        $rows = $this->basic_model->get_result('references', ['archive'=>0,'type'=>25],['id as value','display_name as label', 'key_name']);
        echo json_encode(["status" => true, "data" => $rows]);
    }

    /*
     * its used for create/update shift
     * handle request for create/update shift modal
     */
    function create_update_shift() {
        $reqData = request_handler('create_schedule');
        $adminId = $reqData->adminId;
        $uuid_user_type = $reqData->uuid_user_type;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return json_encode($response);

        $data = object_to_array($reqData->data);
        if($data['scheduled_travel_duration_hr'] && $data['scheduled_travel_duration_min']) {
            $data['scheduled_travel_duration'] = $data['scheduled_travel_duration_hr'] . ":"
            . $data['scheduled_travel_duration_min'];
        } else {
            $data['scheduled_travel_duration'] = '';
        }

        if($data['actual_travel_duration_hr'] && $data['actual_travel_duration_min']) {
            $data['actual_travel_duration'] = $data['actual_travel_duration_hr'] . ":"
            . $data['actual_travel_duration_min'];
        } else {
            $data['actual_travel_duration'] = '';
        }

        if(!empty($data['repeat_option']))
            $response = $this->Schedule_model->create_update_shift_repeat_wrapper($data, $adminId, $uuid_user_type);
        else
            $response = $this->Schedule_model->create_update_shift($data, $adminId, $uuid_user_type);

        echo json_encode($response);
        exit();
    }

    /*
     * its used for clone shift
     * handle request for clone_shift shift modal
     */
    function clone_shift() {
        $reqData = request_handler('create_schedule');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        $data = object_to_array($reqData->data);
        $response = $this->Schedule_model->clone_shift_validation($data, $adminId);
        if(isset($response) && $response['status'] == true) {
            # were we asked to repeat the shift?
            if(!empty($data['repeat_option'])) {
                $re_response = $this->Schedule_model->repeat_shift($data, $adminId, $clone = true);
                if ($re_response != null && $re_response != '') {
                    $response = $re_response;
                }
            }
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Getting currently logged in admin user's details
     */
    public function get_current_admin_user_details() {
        $reqData = request_handler('access_schedule');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        require_once APPPATH . 'Classes/admin/admin.php';
        $objAdmin = new AdminClass\Admin();
        $objAdmin->setAdminid($adminId);
        if (!empty($reqData->data->user_type) && $reqData->data->user_type == 3) {
            $objAdmin->setUuid_user_type($reqData->data->user_type);
            $objAdmin->setUuid($adminId);
            $CI = & get_instance();
            $CI->load->model('Admin_model');
            $result = $CI->Admin_model->get_member_details_by_uuid($objAdmin);
        } else {
            $result = $objAdmin->get_admin_details();
        }
        
        if (empty($result)) {
            $response = ['status' => false, 'error' => "User details not found!"];
            echo json_encode($response);
            exit();
        }

        if (!empty($reqData->data->user_type) && $reqData->data->user_type == 3) {
            $response = ['status' => true, 'data' => ["value" => $result->id, "label" => $result->full_name ]];
        } else {
            $response = ['status' => true, 'data' => ["value" => $result['uuid'], "label" => $result['firstname']." ".$result['lastname'], "email" => $result['username']]];
        }

        echo json_encode($response);
        exit();
    }

    /*
     * For getting shift members list
     */
    function get_shift_member_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_shift_member_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Mark shift member as archived.
     */
    public function archive_shift_member() {
        $reqData = request_handler();
        $adminId = $reqData->adminId;

        $data = (array) $reqData->data;
        $response = $this->Schedule_model->archive_shift_member($data, $adminId);
        if(isset($response) && $response['status'] == true) {
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $this->loges->setTitle(sprintf("Successfully archived shift member with ID of %s by %s", $data['id'], $adminName));
            $this->loges->setSpecific_title(sprintf("Successfully archived shift member with ID of %s by %s", $data['id'], $adminName));  // set title in log
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($data['id']);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * Retrieve shift details.
     */
    public function get_shift_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;

        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        $clone_shift = false;
        if(isset($data->clone_id) && $data->clone_id)
        $clone_shift = true;

        $shift_lock = false;
        if(isset($data->shift_lock) && $data->shift_lock)
        $shift_lock = true;

        $result = $this->Schedule_model->get_shift_details($data->id, $clone_shift, false, $shift_lock, $reqData->adminId, $reqData->uuid_user_type);
        echo json_encode($result);
        exit();
    }

    //Get shift goal tracking details
    function get_shift_goal_tracking_details() {
        $reqData = request_handler('access_member');
        if (!empty($reqData)) {
            $data = object_to_array($reqData->data);
            $result = $this->Schedule_model->get_shift_goal_tracking_details($data);
        }else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * controller function used for assigning one or more members to a shift
     */
    public function assign_shift_members() {
        $reqData = request_handler('create_schedule');
        $adminId = $reqData->adminId;

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (empty($reqData->data))
        return $response;

        $data = (array) $reqData->data;

        $response = $this->Schedule_model->assign_shift_members($data, $adminId);
        if(isset($response) && $response['status'] == true) {
            $this->loges->setCreatedBy($adminId);
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);

            # create log setter getter
            $this->loges->setTitle("Assigned members to shift:".$data['shift_id']." by " . $adminName);
            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($data['shift_id']);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog();
        }
        echo json_encode($response);
        exit();
    }

    /**
     * fetches the next number in creating shift
     */
    public function get_next_shift_no() {
        $reqData = request_handler('access_member');
        if (!empty($reqData->data)) {
            $response = $this->Schedule_model->get_next_shift_no();
            echo json_encode($response);
        }
        exit(0);
    }

    public function create_shift() {
        $reqData = request_handler('create_schedule');
        $this->loges->setCreatedBy($reqData->adminId);

        $shift_data = (array) $reqData->data;
        $reqestData = $reqData->data;
        $validation_rules = array(
            array('field' => 'participant_member_ary', 'label' => 'Participant', 'rules' => 'callback_check_selected_val[' . json_encode($shift_data) . ']'),
            array('field' => 'completeAddress[]', 'label' => 'Address', 'rules' => 'callback_check_shift_address|callback_postal_code_check[postal]'),
            array('field' => 'lineItem', 'label' => 'Participant', 'rules' => 'callback_check_line_item_is_valid_to_apply_on_shift[' . json_encode($shift_data) . ']'),
            array('field' => 'caller_name', 'label' => 'booker firstname', 'rules' => 'required'),
            array('field' => 'caller_lastname', 'label' => 'booker lastname', 'rules' => 'required'),
            array('field' => 'caller_phone', 'label' => 'booker phone', 'rules' => 'required|callback_phone_number_check[caller_phone,required,Booking Details contact should be enter valid phone number.]'),
            array('field' => 'caller_email', 'label' => 'booker name', 'rules' => 'required|valid_email'),
            array('field' => 'start_time', 'label' => 'start date time', 'rules' => 'callback_check_shift_calendar_date_time[' . json_encode($shift_data) . ']'),
            array('field' => 'end_time', 'label' => 'end date time', 'rules' => 'required'),
            array('field' => 'booking_method', 'label' => 'booking for', 'rules' => 'required'),
            array('field' => 'confirm_with_f_name', 'label' => 'confimation firstname', 'rules' => 'required'),
            array('field' => 'confirm_with_l_name', 'label' => 'confimation lastname', 'rules' => 'required'),
            array('field' => 'confirm_with_email', 'label' => 'confimation email', 'rules' => 'required|valid_email'),
            array('field' => 'confirm_with_mobile', 'label' => 'confimation mobile number', 'rules' => 'required|callback_phone_number_check[confirm_with_mobile,required,Confimation Details contact should be enter valid phone number.]'),
            array('field' => 'push_to_app', 'label' => 'push on app', 'rules' => 'required'),
            array('field' => 'shift_note', 'label' => 'shift note', 'rules' => 'required'),
            array('field' => 'autofill_shift', 'label' => 'auto fill shift', 'rules' => 'required'),
            array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
        );

        $this->form_validation->set_data($shift_data);
        $this->form_validation->set_rules($validation_rules);


        if ($this->form_validation->run()) {

            require_once APPPATH . 'Classes/shift/Shift.php';
            $objShift = new ShiftClass\Shift();

            $replace_previous_shift = (!empty($reqestData->replace_previous_shift)) ? true : false;

            $objShift->setBookedBy($reqestData->booked_by);
            $objShift->setShiftDate($reqestData->start_time);
            $objShift->setStartTime($reqestData->start_time);
            $objShift->setEndTime($reqestData->end_time);
            $objShift->setStatus($reqestData->status == 3 ? 1 : 0);
            $objShift->set_shift_status($reqestData->status);

            if ($reqestData->booked_by == 2 || $reqestData->booked_by == 3) {
                $objShift->setShiftParticipant($reqestData->participant_member_ary[0]->name->value);
                $objShift->setUserId($reqestData->participant_member_ary[0]->name->value);
            } elseif ($reqestData->booked_by == 1) {
                $objShift->setShiftSite($reqestData->site_lookup_ary[0]->name->value);
                $objShift->setUserId($reqestData->site_lookup_ary[0]->name->value);
            } elseif ($reqestData->booked_by == 7) {
                $objShift->setShiftHouse($reqestData->house_lookup_ary[0]->name->value);
                $objShift->setUserId($reqestData->house_lookup_ary[0]->name->value);
            }

            $check_shift_exist = $objShift->check_shift_exist();
            if (!empty($check_shift_exist) && !$replace_previous_shift) {
                $shift_exist = array('status' => false, 'error' => 'Shift already exist.', 'shift_already_exist' => true, 'shifts' => $check_shift_exist);
                echo json_encode($shift_exist);
                exit();
            }


            $result = $objShift->create_shift($reqData, $reqData->adminId);


            if ($replace_previous_shift) {
                // archive previous shifts
                $shiftIds = array_column(obj_to_arr($check_shift_exist), 'id');
                $objShift->archive_shift($shiftIds);
            }

            if (!empty($result['shiftId'])) {
                $objShift->setShiftId($result['shiftId']);
                $objShift->shiftCreateMail();
                $objShift->send_notification_to_booked_for();

                // create log
                $this->loges->setTitle('New shift created : Shift Id ' . $result['shiftId']);
                $this->loges->setUserId($result['shiftId']);
                $this->loges->setDescription(json_encode($reqestData));
                $this->loges->createLog();

                $redirect = 'unfilled/unfilled';
                $msg = '';
                if ($reqData->data->autofill_shift == 1 && !$result['allocation_res']) {
                    $msg = 'Autofill Shift Member not found.';
                } else if ($reqData->data->autofill_shift == 1 && $result['allocation_res']) {
                    $redirect = 'unconfirmed/unconfirmed';
                } else if (!empty($reqData->data->allocate_pre_member) && $reqData->data->allocate_pre_member == 1) {
                    $redirect = 'unconfirmed/unconfirmed';
                } elseif (!empty($reqData->data->push_to_app) && $reqData->data->push_to_app == 1) {
                    $redirect = 'unfilled/app';
                }
                if (!empty($reqestData->status) && $reqestData->status == 3) {
                    $redirect = 'unconfirmed/quoted';
                }

                $return = array('status' => true, 'shift_id' => $result['shiftId'], 'redirect_type' => $redirect, 'msg' => $msg);
            } else {
                $return = array('status' => false);
            }
        } else {
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
        }
        echo json_encode($return);
        exit();
    }

    public function check_selected_val($name_ary, $param_type) {
        $return = false;

        $param_type = json_decode($param_type);
        $booked_by = $param_type->booked_by;
        if ($booked_by == 1) {
            $message = 'Please select Site Look-up.';
            if (isset($param_type->site_lookup_ary[0]->name->value)) {
                $return = true;
            }
        } else if ($booked_by == 2) {
            $message = 'Please select Participant Name.';
            if (isset($param_type->participant_member_ary[0]->name->value)) {
                $return = true;
            }
        } else {
            $message = '';
            $return = true;
        }

        $this->form_validation->set_message('check_selected_val', $message);
        return $return;
    }

    public function check_line_item_is_valid_to_apply_on_shift($shift_requirement, $param_type) {

        $reqData = json_decode($param_type);
        $status = false;
        $selected_line_items = [];
        $selected_line_items_itemid = [];
        $qty = calculate_qty_using_date($reqData->start_time, $reqData->end_time);
        $postcode = (!empty($reqData->completeAddress[0]->postcode)) ? $req->completeAddress[0]->postcode : 0;
        $price_type = get_price_type_on_base_postcode($postcode);

        $line_itemIds = array_column(obj_to_arr($reqData->selectedLineItemList), "line_itemId");

        $line_item_cost = $this->Schedule_model->getLineItemCost($line_itemIds);

        if (!empty($reqData->selectedLineItemList)) {
            foreach ($reqData->selectedLineItemList as $val) {
                if (!empty($val->checked)) {
                    $qty = calculate_qty_using_date($reqData->start_time, $reqData->end_time);
                    $postcode = (!empty($reqData->completeAddress[0]->postcode)) ? $reqData->completeAddress[0]->postcode : 0;
                    $price_type = get_price_type_on_base_postcode($postcode);
                    $item_cost = $line_item_cost[$val->line_itemId][$price_type];
                    $item_sub_total = custom_round(($item_cost * $qty), 2);
                    $item_gst = calculate_gst($item_sub_total);
                    $val->item_required_cost = custom_round($item_sub_total + $item_gst);

                    $selected_line_items[] = $val->plan_line_itemId;
                    $selected_line_items_itemid[] = $val->line_itemId;
                    $status = true;
                }
            }
        } else {
            $this->form_validation->set_message('check_line_item_is_valid_to_apply_on_shift', 'Please select atleast one line item to create shift');
            return false;
        }

        $line_item_required_fund = array_column($reqData->selectedLineItemList, "item_required_cost", "plan_line_itemId");

        if (!$status) {
            $this->form_validation->set_message('check_line_item_is_valid_to_apply_on_shift', 'Please select atleast one line item to create shift');
            return false;
        }

        $user_type = $reqData->booked_by == 3 ? 2 : $reqData->booked_by;
        if ($user_type == 1) {
            $reqData->userId = (!empty($reqData->site_lookup_ary[0]->name->value)) ? $reqData->site_lookup_ary[0]->name->value : 0;
        } else if ($user_type == 7) {
            $reqData->userId = (!empty($reqData->house_lookup_ary[0]->name->value)) ? $reqData->house_lookup_ary[0]->name->value : 0;
        } else {
            $reqData->userId = (!empty($reqData->participant_member_ary[0]->name->value)) ? $reqData->participant_member_ary[0]->name->value : 0;
        }

        $start_time = !empty($reqData->start_time) ? DateFormate($reqData->start_time, "Y-m-d H:i:s") : '';
        $end_time = !empty($reqData->end_time) ? DateFormate($reqData->end_time, "Y-m-d H:i:s") : '';

        if ($start_time && $end_time) {
            $first_day = DateFormate($start_time, "D");
            $seconday_day = DateFormate($end_time, "D");

            $reqData->day = ($first_day === $seconday_day) ? [$first_day] : [$first_day, $seconday_day];
        }
        $reqData->support_line_item_category_filter_selected_by = '';
        $reqData->support_line_item_category_filter_selected_search = '';
        $get_user_plan_line_item = $this->Schedule_model->get_user_specific_plan_line_item($reqData);

        if (!empty($get_user_plan_line_item)) {
            if ($user_type == 2 || $user_type == 3) {
                $plan_line_items = array_column(obj_to_arr($get_user_plan_line_item['lineItemList']), 'plan_line_itemId');
                $plan_line_fund = array_column(obj_to_arr($get_user_plan_line_item['lineItemList']), "fund_remaining", "plan_line_itemId");
                $plan_line_name = array_column(obj_to_arr($get_user_plan_line_item['lineItemList']), "line_item_number", "plan_line_itemId");

                $x = array_intersect($selected_line_items, $plan_line_items);
                $any_diff = array_diff($selected_line_items, $x);

                if (!empty($line_item_required_fund)) {
                    foreach ($line_item_required_fund as $line_item => $required_fund) {
                        if ($required_fund > $plan_line_fund[$line_item]) {
                            $this->form_validation->set_message('check_line_item_is_valid_to_apply_on_shift', 'Insufficient Fund in line item ' . $plan_line_name[$line_item]);
                            return false;
                        }
                    }
                }
            } else {

                $line_item_id = array_column(obj_to_arr($get_user_plan_line_item['lineItemList']), 'line_itemId');
                $x = array_intersect($selected_line_items_itemid, $line_item_id);
                $any_diff = array_diff($selected_line_items_itemid, $x);
            }

            if (!empty($any_diff)) {
                $this->form_validation->set_message('check_line_item_is_valid_to_apply_on_shift', 'Selected line item is not valid');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_line_item_is_valid_to_apply_on_shift', 'Selected line item is not valid');
            return false;
        }

        return true;
    }

    function check_shift_calendar_date_time($start_time, $shiftData) {

        $shiftData = json_decode($shiftData);

        if (!empty($shiftData)) {

            /* time calculation */
            $strTime = strtotime(DateFormate($shiftData->start_time, 'Y-m-d H:i:s'));
            $endTime = strtotime(DateFormate($shiftData->end_time, 'Y-m-d H:i:s'));
            $next_day_time = strtotime('+' . SHIFT_MAX_DURATION_HOURS . ' hours', $strTime);

            $strTimeCurrunt = strtotime(DATE_TIME);
            if (!empty($strTime)) {
                if ($strTime > $strTimeCurrunt) {

                } else {
                    $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift start time can not less then current time');
                    return false;
                }
            } else {
                $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift start date time required can not empty');
                return false;
            }


            if (!empty($endTime)) {
                if ($endTime <= $next_day_time) {

                } else {
                    $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift end time not greater then ' . SHIFT_MAX_DURATION_HOURS . ' hours');
                    return false;
                }
            } else {
                $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift end date time required can not empty');
                return false;
            }

            if ($strTime > $endTime) {
                $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift end time not less then start time');
                return false;
            }

            if ($strTime == $endTime) {
                $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift end time not equal to start time');
                return false;
            }
        } else {
            $this->form_validation->set_message('check_shift_calendar_date_time', 'Shift data required can not empty');
            return false;
        }
        return true;
    }

    public function get_participant_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Schedule_model->get_participant_name($post_data);
        echo json_encode($rows);
    }

    public function get_site_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Schedule_model->get_site_name($post_data);
        echo json_encode($rows);
    }
    ///Get Site name with out full Organization list
    public function get_is_site_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Schedule_model->get_is_site_name($post_data);
        echo json_encode($rows);
    }

     //Get the data from participant master table
     public function get_participant_master_name() {

        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Schedule_model->get_participant_master_name($post_data);
        echo json_encode($rows);
    }

    public function get_shift_requirement_for_participant() {
        $reqData = request_handler('create_schedule');
        if (!empty($reqData->data[0]->name->value)) {

            $participantId = $reqData->data[0]->name->value ?? 0;
            $rows = $this->Schedule_model->get_shift_requirement_for_participant($participantId);
            echo json_encode(array('status' => true, 'data' => $rows));
        }
    }

    public function get_shift_details_old() {
        $reqData = request_handler('access_schedule');

        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        if (!empty($reqData->data)) {
            $shiftId = $reqData->data->id;

            $objShift->setShiftId($shiftId);

            // get shift details
            $result = $objShift->get_shift_details();
            $objShift->setBookedBy($result['booked_by']);

            // if booked by 1 mean this booking by organization so get shift relation details get form organization
            if ($result['booked_by'] == 1) {
                // get shift organization
                $result['shift_organiztion_site'] = $objShift->get_shift_oganization();
            } else {
                // get shift participant
                $result['shift_participant'] = $objShift->get_shift_participant();
            }

            // get shift location
            $result['shift_location'] = $objShift->get_shift_location();

            if (!empty($result['shift_location'])) {
                foreach ($result['shift_location'] as $key => $val) {
                    $result['shift_location'][$key]->street = $val->address;
                }
            }

            // if status 7 that mean shift is confirmed then get confirmed member details
            if ($result['status'] == 7 || $result['status'] == 6) {
                $result['allocated_member'] = $objShift->get_accepted_shift_member();
            } elseif ($result['status'] == 2) {
                $result['allocated_member'] = $objShift->get_allocated_member();
            } else if ($result['status'] == 5) {
                $result['cancel_data'] = $objShift->get_cancelled_details();
            } else if ($result['status'] == 4) {
                $result['rejected_data'] = $objShift->get_rejected_member();
            } else {
                $preffered_member = $objShift->get_preferred_member();
                if (!empty($preffered_member)) {
                    foreach ($preffered_member as $val) {
                        $val->select = array('value' => $val->memberId, 'label' => $val->memberName);
                    }
                }
                $result['preferred_member'] = $preffered_member;
            }

            // get shift caller
            $result['shift_caller'] = (array) $objShift->get_shift_caller();

            // get shift confirmation details
            $result['confirmation_details'] = (array) $objShift->get_shift_confirmation_details();

            // get shift requirement
            $response = $objShift->get_shift_requirement();

            $result = array_merge($response, $result);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function cancel_shift() {
        $reqData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqData->adminId);

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'shiftId', 'label' => 'shift Id', 'rules' => 'required'),
                array('field' => 'cancel_method', 'label' => 'cancel method', 'rules' => 'required'),
                array('field' => 'cancel_type', 'label' => 'who cancel', 'rules' => 'required'),
                array('field' => 'reason', 'label' => 'reason', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $return = $this->Schedule_model->cancel_shift($reqData);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($return);
        }
    }

    public function get_shift_notes() {
        $reqData = request_handler('access_schedule');

        if (!empty($reqData->data)) {
            $reqData = $reqData->data;

            $result = $this->basic_model->get_record_where('shift_notes', $column = array('id', 'adminId', 'title', 'notes', "DATE_FORMAT(note_date,'%d/%m/%Y') as note_date", "DATE_FORMAT(created,'%d/%m/%Y') as created"), $where = array('shiftId' => $reqData->shiftId, 'archive' => 0));

            echo json_encode(array('status' => true, 'data' => $result));
        }
    }

    public function create_shift_notes() {
        $reqestData = request_handler();
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            #pr($reqData);
            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'shiftId', 'label' => 'Shift Id', 'rules' => 'required'),
                array('field' => 'notes', 'label' => 'Notes', 'rules' => 'required'),
                array('field' => 'title', 'label' => 'Title', 'rules' => 'required'),
            );
            if (empty($reqData->id)) {
                $validation_rules[] = array('field' => 'note_date', 'label' => 'Date', 'rules' => 'required');
            }

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->loges->setUserId($reqData->shiftId);
                $this->loges->setDescription(json_encode($reqData));

                $data = array('title' => $reqData->title, 'notes' => $reqData->notes, 'shiftId' => $reqData->shiftId, 'adminId' => $reqestData->adminId, 'note_date' => DateFormate($reqData->note_date, DB_DATE_FORMAT));

                if (!empty($reqData->id)) {
                    // check permission
                    check_permission($reqestData->adminId, 'update_schedule');
                    $this->loges->setTitle('Update notes: Shift Id ' . $reqData->shiftId);
                    $this->basic_model->update_records('shift_notes', $data, $where = array('id' => $reqData->id));
                    $title = 'Internal Notes for shift is updated.';
                    $description = 'Internal Notes for shift is updated : Shift Id = ' . $reqData->shiftId;
                } else {
                    // check permission
                    check_permission($reqestData->adminId, 'create_schedule');
                    $this->loges->setTitle('Added new notes: Shift Id ' . $reqData->shiftId);
                    $data['created'] = DATE_TIME;
                    $this->basic_model->insert_records('shift_notes', $data);
                    $title = 'Add new internal notes for shift.';
                    $description = 'New internal notes for shift is created : Shift Id = ' . $reqData->shiftId;
                }

                $this->loges->createLog();

                $shift_row = $this->basic_model->get_row('shift', array('booked_by'), $where = array('id' => $reqData->shiftId));
                $booked_by = $shift_row->booked_by;
                $this->send_notification_on_shift_update($booked_by, $reqData->shiftId, $title, $description);
                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    public function update_shift_date_time() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'shiftId', 'label' => 'shift Id', 'rules' => 'required'),
                array('field' => 'start_time', 'label' => 'start date time', 'rules' => 'required'),
                array('field' => 'end_time', 'label' => 'end date time', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $this->loges->setUserId($reqData->shiftId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Update shift date time: Shift Id ' . $reqData->shiftId);
                $this->loges->createLog();

                $shfit_date = date('Y-m-d H:i:s', strtotime($reqData->start_time));

                $start_time = DateFormate($reqData->start_time, 'Y-m-d H:i:s');
                $end_time = DateFormate($reqData->end_time, 'Y-m-d H:i:s');

                $where = array('id' => $reqData->shiftId);
                $data = array('shift_date' => $shfit_date, 'start_time' => $start_time, 'end_time' => $end_time);
                $this->basic_model->update_records('shift', $data, $where);
                $this->Schedule_model->update_as_unconfirmed_if_shift_is_filled($reqData->shiftId);

                $title = 'Your shift date time is updated.';
                $description = 'Update shift date time: Shift Id = ' . $reqData->shiftId;

                $this->send_notification_on_shift_update($reqData->booked_by, $reqData->shiftId, $title, $description);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }
            echo json_encode($response);
        }
    }

    public function update_shift_address() {
        $reqestData = request_handler('create_schedule');

        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $location = array();

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'shift_location[]', 'label' => 'Address', 'rules' => 'callback_check_shift_address|callback_postal_code_check[postal]'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if ($reqData->shift_location) {
                    foreach ($reqData->shift_location as $key => $val) {

                        $val->suburb = $val->suburb;
                        $val->address = $val->street;

                        if (isset($val->street)) {
                            unset($val->street);
                        }
                        if (isset($val->city)) {
                            unset($val->city);
                        }
                        if (isset($val->postal_code)) {
                            unset($val->postal_code);
                        }

                        $state = getStateById($val->state);
                        $adds = $val->address . ' ' . $val->suburb . ' ' . $state;
                        $lat_long = getLatLong($adds);


                        $location[$key] = (array) $val;

                        if (!empty($lat_long)) {
                            $lat_long = array_map('strval', $lat_long);
                            $location[$key] = array_merge($location[$key], $lat_long);
                        }

                        unset($location[$key]['site']);
                        $location[$key]['shiftId'] = $reqData->shiftId;
                    }


                    $this->loges->setUserId($reqData->shiftId);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Update address : Shift Id ' . $reqData->shiftId);
                    $this->loges->createLog();

                    $this->CI->Listing_model->delete_shift_location(array('shiftId' => $reqData->shiftId));
                    $this->CI->Listing_model->create_shift_location($location);

                    $this->Schedule_model->update_as_unconfirmed_if_shift_is_filled($reqData->shiftId);

                    $title = 'Your shift address is updated.';
                    $description = 'Update shift address: Shift Id = ' . $reqData->shiftId;

                    $this->send_notification_on_shift_update($reqData->booked_by, $reqData->shiftId, $title, $description);
                }

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    public function update_shift_requirement() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $reqData->shift_requirement = $reqData->requirement;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'shiftId', 'label' => 'shiftId', 'rules' => 'required'),
                array('field' => 'booked_by', 'label' => 'Booked by', 'rules' => 'required'),
            );

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $booked_by = $reqData->booked_by;
                $requirementMobility = $reqData->requirement_mobility;
                $requirement = $reqData->requirement;
                $orgShiftData = $reqData->org_shift_data;


                $updated_requirement_mobility = array();
                $updated_requirement = array();
                $updated_orgShiftData = array();

                if ($booked_by == 1 || $booked_by == 7) {
                    $this->basic_model->delete_records('shift_requirements', $where = array('shiftId' => $reqData->shiftId));
                    $this->basic_model->delete_records('shift_org_requirements', $where = array('shiftId' => $reqData->shiftId));
                } else if ($booked_by == 2 || $booked_by == 3) {
                    $this->basic_model->delete_records('shift_requirements', $where = array('shiftId' => $reqData->shiftId));
                }


                if (!empty($requirementMobility)) {
                    foreach ($requirementMobility as $key => $val) {
                        if ($val->active) {
                            $temp = [];
                            $temp['shiftId'] = $reqData->shiftId;
                            $temp['requirementId'] = $val->id;
                            $temp['requirement_type'] = 1;
                            $temp['requirement_other'] = null;
                            if ($val->key_name == 'other') {
                                $temp['requirement_other'] = $val->other_title;
                            }
                            $updated_requirement_mobility[] = $temp;
                        }
                    }
                }

                if (!empty($requirement)) {
                    foreach ($requirement as $key => $val) {
                        if ($val->active) {
                            $temp = [];
                            $temp['shiftId'] = $reqData->shiftId;
                            $temp['requirementId'] = $val->id;
                            $temp['requirement_type'] = 2;
                            $temp['requirement_other'] = null;
                            if ($val->key_name == 'other') {
                                $temp['requirement_other'] = $val->other_title;
                            }
                            $updated_requirement[] = $temp;
                        }
                    }
                }

                if (!empty($orgShiftData) && ($booked_by == 1 || $booked_by == 7)) {
                    foreach ($orgShiftData as $key => $val) {
                        if ($val->active) {
                            $temp = [];
                            $temp['shiftId'] = $reqData->shiftId;
                            $temp['requirementId'] = $val->id;
                            $updated_orgShiftData[] = $temp;
                        }
                    }
                }


                $this->loges->setUserId($reqData->shiftId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Update shift requirement : Shift Id ' . $reqData->shiftId);
                $this->loges->createLog();
                //pr([$updated_requirement,$updated_orgShiftData]);
                if (!empty($updated_requirement_mobility)) {
                    $table = 'shift_requirements';
                    $this->basic_model->insert_records($table, $updated_requirement_mobility, $multiple = true);
                }
                if (!empty($updated_requirement)) {
                    $table = 'shift_requirements';
                    $this->basic_model->insert_records($table, $updated_requirement, $multiple = true);
                }
                if (!empty($updated_orgShiftData) && ($booked_by == 1 || $booked_by == 7)) {
                    $table = 'shift_org_requirements';
                    $this->basic_model->insert_records($table, $updated_orgShiftData, $multiple = true);
                }

                //$this->basic_model->insert_records('shift_requirements', $updated_requirement, $multiple = true);
                $title = 'Your shift requirement is updated.';
                $description = 'Update shift requirement: Shift Id = ' . $reqData->shiftId;

                $this->send_notification_on_shift_update($reqData->booked_by, $reqData->shiftId, $title, $description);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    function update_preffered_member() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $reqData = $reqestData->data;
            $preffer_member = array();

            $this->basic_model->delete_records('shift_preferred_member', $where = array('shiftId' => $reqData->shiftId));
            if (!empty($reqData->preferred_members)) {
                foreach ($reqData->preferred_members as $val) {
                    if (!empty($val->select->value)) {
                        $preffer_member[] = array('memberId' => $val->select->value, 'shiftId' => $reqData->shiftId);
                    }
                }
            }
            if (!empty($preffer_member)) {
                $this->basic_model->insert_records('shift_preferred_member', $preffer_member, $multiple = true);

                $this->loges->setUserId($reqData->shiftId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->setTitle('Update prefered member : Shift Id ' . $reqData->shiftId);
                $this->loges->createLog();
            }

            echo json_encode(array('status' => true));
        }
    }

    public function call_allocated() {
        $reqestData = request_handler('access_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $shiftId = $reqestData->data->shiftId;

            $this->loges->setUserId($shiftId);
            $this->loges->setDescription(json_encode($reqestData));
            $this->loges->setTitle('Call to allocater : Shift Id ' . $shiftId);
            $this->loges->createLog();

            $this->Schedule_model->call_allocated($reqestData->data);

            echo json_encode(array('status' => true));
        }
    }

    public function call_booker() {
        $reqestData = request_handler('access_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);

        if (!empty($reqestData->data)) {
            $shiftId = $reqestData->data->shiftId;

            $this->loges->setUserId($shiftId);
            $this->loges->setDescription(json_encode($reqestData));
            $this->loges->setTitle('Call to booker : Shift Id ' . $shiftId);
            $this->loges->createLog();

            $data = array('confirmed_with_booker' => DATE_TIME);
            $this->basic_model->update_records('shift_confirmation', $data, $where = array('shiftId' => $shiftId));
            echo json_encode(array('status' => true));
        }
    }

    public function get_booking_list() {
        $reqestData = request_handler('access_schedule');

        $rows = $this->Schedule_model->get_booking_list($reqestData->data->srchId);
        $rowsAddress = $this->Schedule_model->get_participant_address_by_participant_id($reqestData->data->srchId);

        $resDefault = [['value' => '', 'label' => 'Select Participant Address']];
        $rowsAddressData = !empty($rowsAddress) ? array_merge($resDefault, $rowsAddress) : $resDefault;
        echo json_encode(array('status' => true, 'shift_data' => $rows, 'shift_participaint_address' => $rowsAddressData));
    }

    public function get_auto_fill_shift_member() {
        $reqestData = request_handler('access_schedule');
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $pre_selected_member = array();

        if ($reqestData->data) {
            $reqData = $reqestData->data;


            if ($reqData->listShiftsIDs) {
                $listShifts = array_keys((array) $reqData->listShiftsIDs, true);

                $objShift->setShiftId($listShifts);

                // true for multiple shift id details
                $result = $objShift->get_shift_detail_with_required_level_and_paypoint(true);

                if (!empty($result)) {
                    foreach ($result as $key => $val) {
                        $objShift->setShiftId($val['id']);
                        $objShift->setBookedBy($val['booked_by']);
                        $objShift->setUserId($val['userId']);
                        $objShift->setShiftDate($val['shift_date']);
                        $objShift->setStartTime($val['start_time']);

                        $current_pre_select = $pre_selected_member[DateFormate($val['start_time'], "Y-m-d")] ?? [];
                        $objShift->setPre_selected_member($current_pre_select);

                        $objShift->setRequired_level($val['required_level_priority']);
                        $objShift->setRequired_paypoint($val['required_point_priority']);

                        $result[$key]['preferred_member'] = $val['preferred_member'] = $participants = $objShift->get_preferred_member();
                        $result[$key]['location'] = $val['location'] = $objShift->get_shift_location();

                        $result[$key]['available_member'] = $this->get_available_member($val, $objShift);

                        if (!empty($result[$key]['available_member']['memberId'])) {
                            $pre_selected_member[$val['shift_date']][] = $result[$key]['available_member']['memberId'];
                            $result[$key]['accepted'] = true;
                        } else {
                            $result[$key]['accepted'] = false;
                        }
                    }
                }
            }
        }
        echo json_encode(array('status' => true, 'data' => $result));
    }

    public function get_available_member($shiftDetails, $objShift) {
        // check all available member according city with required level and pay point
        // first get member with equal required pay point and level
        $near_city_members = $objShift->get_available_member_by_city(false);

        $available_member = $near_city_members['available_members'];

        // if did not get any member then get member with greather and equal pay point and level
        if (empty($available_member)) {
            $near_city_members = $objShift->get_available_member_by_city(true);

            $available_member = $near_city_members['available_members'];
        }

        // check here preffer member if availble then alot
        if (!empty($shiftDetails['preferred_member']) && count($available_member) > 1) {
            foreach ($shiftDetails['preferred_member'] as $val) {
                if (in_array($val->memberId, $available_member)) {
                    //array_column('memberId', $available_member)
                    $available_member = array($val->memberId);
                }
            }
        }

        if (empty($available_member)) {
            return array('shared_activity' => [], 'shared_place' => []);
        }

        // get member details according to house
        if ($shiftDetails['booked_by'] == 7) {
            // get participant ids
            $houseId = $shiftDetails['userId'];

            // get member details
            $result = $near_city_members['member_details'][$available_member[0]];
            $result['shared_activity'] = array();
            $result['shared_place'] = array();

            return $result;
        } elseif ($shiftDetails['booked_by'] == 1) { // get member details accoding to organization
            // get participant ids
            $siteId = $shiftDetails['userId'];

            // get member details
            $result = (array) $near_city_members['member_details'][$available_member[0]];
            $result['shared_activity'] = array();
            $result['shared_place'] = array();

            return $result;
        } elseif ($shiftDetails['booked_by'] == 2) { // get member details accoding to participants
            // get participant ids
            $participantId = $shiftDetails['userId'];

            // if get more member then check its shared places and activity
            if (count($available_member) > 0) {
                $available_member[] = $objShift->get_available_member_by_preferences($available_member, $participantId);
            }


            if (!empty($available_member)) {
                // get member details
                $result = $near_city_members['member_details'][$available_member[0]];

                //get all perfered places and activity
                $shared = $objShift->get_prefference_activity_places($available_member[0], $participantId);

                $result = array_merge($result, $shared);

                return $result;
            }
        }
    }

    public function assign_autofill_member() {
        $reqestData = request_handler('update_schedule');
        $this->loges->setCreatedBy($reqestData->adminId);
        $memberAssign = $updateShift = array();

        if ($reqestData->data) {
            $shift_details = $reqestData->data;

            if (!empty($shift_details)) {
                $temp_ary = $notify_ary = [];
                foreach ($shift_details as $val) {
                    if ($val->accepted) {
                        $updateShift = array('updated' => DATE_TIME, 'status' => 2);
                        $this->basic_model->update_records('shift', $updateShift, $where = array('id' => $val->id));

                        $memberAssign[] = array('shiftId' => $val->id, 'memberId' => $val->available_member->memberId, 'status' => 1, 'created' => DATE_TIME);

                        $this->loges->setUserId($val->id);
                        $this->loges->setDescription(json_encode($val));
                        $this->loges->setTitle('Assign shift to member ' . $val->available_member->member_name . ' : Shift Id ' . $val->id);
                        $this->loges->createLog();

                        $temp_ary['userId'] = $val->available_member->memberId;
                        $temp_ary['user_type'] = 1;
                        $temp_ary['title'] = 'New Shift is assign to you.';
                        $temp_ary['shortdescription'] = "New Shift is assign to you (shift id = $val->id).";
                        $temp_ary['created'] = DATE_TIME;
                        $temp_ary['sender_type'] = 2;
                        $notify_ary[] = $temp_ary;
                    }
                }
                if (!empty($notify_ary)) {
                    $this->basic_model->insert_update_batch($action = 'insert', $table_name = 'notification', $notify_ary, $update_base_column_key = '');
                }
            }

            if (!empty($memberAssign)) {
                $this->basic_model->insert_records('shift_member', $memberAssign, $multiple = true);

                echo json_encode(array('status' => true));
            } else {
                echo json_encode(array('status' => false, 'error' => 'Please assign member to atleast one shift'));
            }
        }
    }

    public function get_next_auto_fill_available_shift_member() {
        $reqestData = request_handler('access_schedule');
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $pre_selected_member = array();

        if (isset($reqestData->data) && !empty($reqestData->data->shiftId)) {
            $reqData = $reqestData->data;

            $objShift->setShiftId($reqData->shiftId);

            // true for multiple shift id details
            $result = $objShift->get_shift_detail_with_required_level_and_paypoint();

            if (!empty($result)) {
                $objShift->setBookedBy($result['booked_by']);
                $objShift->setUserId($result['userId']);
                $objShift->setShiftDate($result['shift_date']);
                $objShift->setStartTime($result['start_time']);

                $crossed_member = $reqData->crossed_member ?? [];

                if (!empty($reqData->shiftDetails)) {
                    foreach ($reqData->shiftDetails as $val) {
                        if (DateFormate($result['shift_date'], "Y-m-d") === DateFormate($val->shift_date, "Y-m-d")) {
                            if (!empty($val->available_member->memberId)) {
                                $crossed_member[] = $val->available_member->memberId;
                            }
                        }
                    }
                }
                $objShift->setPre_selected_member($crossed_member);

                $objShift->setRequired_level($result['required_level_priority']);
                $objShift->setRequired_paypoint($result['required_point_priority']);

                // if booked by 1 mean this booking by organization so get shift relation details get form organization
                if ($result['booked_by'] == 1) {
                    // get shift organization
                    $result['shift_organiztion_site'] = $objShift->get_shift_oganization();
                } elseif ($result['booked_by'] == 2 || $result['booked_by'] == 3) {
                    // get shift participant
                    $result['shift_participant'] = $objShift->get_shift_participant();
                }

                $result['preferred_member'] = $result['preferred_member'] = $participants = $objShift->get_preferred_member();
                $result['location'] = $objShift->get_shift_location();

                $result['available_member'] = $this->get_available_member($result, $objShift);

                $result['accepted'] = (!empty($result['available_member']['memberId'])) ? true : false;

                echo json_encode(array('status' => true, 'data' => $result));
            } else {
                echo json_encode(array('status' => true, 'data' => $result));
            }
        } else {
            echo json_encode(array('status' => false, 'error' => 'shift id not found'));
        }
    }

    public function get_shift_loges() {
        $reqestData = request_handler('access_schedule');
        if ($reqestData->data) {

            $result = $this->Schedule_model->get_shift_logs($reqestData->data);
            echo json_encode(array('status' => true, 'data' => $result));
        }
    }


    public function get_key_billing_person() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $post_data = $reqData->data;
            $type = $post_data->type ?? 1;

            if (!empty($post_data->site_lookup_ary[0]->name)) {
                $siteId = $post_data->site_lookup_ary[0]->name->value;

                $data = $this->Schedule_model->get_house_key_billing_persion($siteId, $type);
                echo json_encode(array('data' => $data, 'status' => TRUE));
            } else {
                echo json_encode(array('data' => 'Site not found', 'status' => false));
            }
        }
    }

    public function get_house_name() {
        $reqData = request_handler();
        $reqData->data = json_decode($reqData->data);
        $post_data = isset($reqData->data->query) ? $reqData->data->query : '';
        $rows = $this->Schedule_model->get_house_name($post_data);
        echo json_encode($rows);
    }

    public function send_notification_on_shift_update($booked_by, $shiftId, $title, $description) {
        if ($booked_by == 2) {
            $user_row = $this->basic_model->get_row('shift_participant', array('participantId'), $where = array('shiftId' => $shiftId));
            $user_id = $user_row->participantId;
            $user_type = 2;
        } else if ($booked_by == 1) {
            $user_row = $this->basic_model->get_row('shift_site', array('siteId'), $where = array('shiftId' => $shiftId));
            $user_id = $user_row->siteId;
            $user_type = 3;
        } else if ($booked_by == 7) {
            $user_row = $this->basic_model->get_row('shift_users', array('user_for'), $where = array('shiftId' => $shiftId, 'user_type' => 7));
            $user_id = $user_row->user_for;
            $user_type = 4;
        }
        $notifi_data = ['user_type' => $user_type, 'sender_type' => 2, 'user_id' => $user_id, 'title' => $title, 'description' => $description];
        #method define in hcm_helper
        save_notification($notifi_data);
    }

    public function confirmation_pdf() {
        $reqData = request_handler('access_schedule');
        if (!empty($reqData->data)) {
            require_once APPPATH . 'Classes/shift/Shift.php';
            $objShift = new ShiftClass\Shift();
            $shiftId = $reqData->data->shiftId ?? 0;
            $objShift->setShiftId($shiftId);

            $result = $objShift->get_shift_detail_with_user();

            $res = (array) $objShift->get_shift_confirmation_details();
            $result['shift_location'] = $objShift->get_shift_location();

            if ($result['status'] == 7 || $result['status'] == 6) {
                $result['allocated_member'] = $objShift->get_accepted_shift_member();
            } elseif ($result['status'] == 2) {
                $result['allocated_member'] = $objShift->get_allocated_member();
            } else {
                $result['allocated_member'] = [];
            }



            $fileData = $this->load->view('confirmation_pdf', ['shiftData' => $result, 'shiftConfirmationData' => $res], true);
            error_reporting(0);
            $this->load->library('m_pdf', NULL, 'mpdflib');
            $pdf = $this->mpdflib->load();
            $pdf->AddPage('L');
            $pdf->WriteHTML($fileData);
            $fileDir = FCPATH . ARCHIEVE_DIR . '/';
            $file_name = 'Shift_confirmation_' . $shiftId . '_' . time() . '.pdf';
            $pdfFilePath = $fileDir . $file_name;
            $xx = $pdf->Output($pdfFilePath, 'F');
            if (file_exists($pdfFilePath)) {
                echo json_encode(['status' => true, 'filename' => $file_name]);
                exit();
            } else {
                echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }
    /*
     * For getting shift' skills list
     */
    function get_skill_reference_data() {
        $reqData = request_handler();
        $rows = $this->Schedule_model->get_skill_reference_data();

        $selectOption = $reqData->data->select_option ?? 0;
        $rowsData = [];
        if ($selectOption == 1) {
            $rowsData[] = ['label' => 'Select Lead Source', 'value' => ''];
        }
        $rowsData = array_merge($rowsData, $rows);
        echo json_encode($rowsData);
    }

    /*
     * For getting shift' skills list
     */
    function get_shift_skills_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_shift_skills_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Mark shift skill as archived.
     */
    public function archive_shift_skill() {
        $reqData = request_handler();
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        $id = isset($data->id) ? $data->id : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        # does the shift skill exist?
        $result = $this->Schedule_model->get_shift_skill_details($data->id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Shift skill does not exist anymore."];
            echo json_encode($response);
            exit();
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("shift_skills", $upd_data, ["id" => $id]);

        # logging action
        $id = $result['id'] ?? $id;
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived shift skill with ID of %s by %s", $id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived shift skill with ID of %s by %s", $id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        $response = ['status' => true, 'msg' => "Successfully archived member skill"];
        echo json_encode($response);
        exit();
    }

    public function create_update_shift_skills()
    {
        $reqData = request_handler('access_schedule');
        $this->output->set_content_type('json');
        $adminId = $reqData->adminId;

        $data = obj_to_arr($reqData->data->dataToBeSubmitted);
        $archive_skill_id = obj_to_arr($reqData->data->archive_skill_id);

        if (empty($data)) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => 'Unable to process your request'
            ]));
        }

        // YES, validate each items in array
        // Let's make server-side validation rules more comprehensive
        $validation_rules = [];
        foreach ($data as $i => $item) {
            $n = $i + 1;

            $validation_rules[] = [
                'field' => "data[$i][shift_id]",
                'label' => "Shift Id for row #{$n}",
                'rules' => ['required']
            ];
            $validation_rules[] = [
                'field' => "data[$i][skill_id]",
                'label' => "Skill for row #{$n}",
                'rules' => ['required'],
            ];
            $validation_rules[] = [
                'field' => "data[$i][condition]",
                'label' => "Condition for row #{$n}",
                'label' => "Skill for row #{$n}",
            ];
        }

        $this->form_validation->set_data(['data' => $data])
            ->set_rules($validation_rules);

        if (!$this->form_validation->run()) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => implode(', ', $this->form_validation->error_array()),
            ]));
        }

        $result = $this->Schedule_model->create_update_shift_skills($data, $archive_skill_id, $adminId);

        if (!$result['status']) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => $result['error'],
            ]));
        }

        // @todo: Logging

        return $this->output->set_output(json_encode([
            'status' => true,
            'msg' => 'Shift skills saved successfully',
        ]));

    }
    /**
     * Get roster id list
     */
    function get_roster_option() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_roster_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting contact list for accounts
     */
    public function get_contact_list_for_account() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $allow_new_contact = $reqData->data->new_contact ?? true;
            $result = $this->Schedule_model->get_contact_list_for_account($reqData->data, $allow_new_contact);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get service agreement list
     */
    function get_service_agreement() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_service_agreement($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get service agreement line itmes list
     */
    function get_service_agreement_line_item_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_service_agreement_line_item_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Get support type
     */
    function get_support_type() {
        $reqData = request_handler();
        if (!empty($reqData)) {
            $support_type_option = $this->basic_model->get_record_where('finance_support_type', ['type as label', 'id as value', 'key_name'], [ 'archive' => 0, 'key_name !=' => 'establishment_fee'  ]);
            $result = [ 'status' => true, 'data' => $support_type_option ];
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Retrieve shift details.
     */
    public function get_shift_timesheet_attachment_details() {
        $reqData = request_handler('access_member');
        $data = $reqData->data;
        $member_id = '';
        if (empty($data->id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            echo json_encode($response);
            exit();
        }

        if(isset($data->member_id) && !empty($data->member_id)){
            $member_id = $data->member_id;
        }

        $result = $this->Schedule_model->get_shift_timesheet_attachment_details($data->id, $member_id);
        echo json_encode($result);
        exit();
    }

     /**
     * getting contact list for accounts
     */
    public function get_contact_email_phone_by_account() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Schedule_model->get_contact_email_phone_by_account($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

     # validate the shift support type
     public function get_shift_support_type_validation()
     {
         $reqData = request_handler();
         if (empty($reqData->data)) {
             return false;
         }     

         $result = $this->Schedule_model->get_shift_support_type_validation($reqData); 
         return $this->output->set_output(json_encode($result));
     }
}
