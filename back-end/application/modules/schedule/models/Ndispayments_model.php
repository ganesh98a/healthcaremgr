<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Ndispayments_model extends Basic_Model {

    var $time_of_day = [
        "daytime" => "daytime",
        "evening" => "evening",
        "overnight" => "overnight",
        "sleepover" => "sleepover",
    ];

    var $presitence_day_rule = [
        "public_holiday" => 3,
        "sunday" => 2,
        "saturday" => 1,
        "weekday" => 0,
    ];

    public function get_line_items_for_payment($data) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $section = $data['section'] ?? '';
        $index = $section == 'scheduled' ? 'Schedule' : 'Actual';
        //Form Validation
        $validation_rules = [
            array('field' => 'start_date', 'label' => $index.' start date & time', 'rules' => 'required',
                'errors' => [
                    'valid_date_format' => formatError('scheduled_start_date', 'Incorrect Schedule start date & time')
                ]),
            array('field' => 'end_date', 'label' => $index.'Schedule end date & time', 'rules' => 'required',
                'errors' => [
                    'valid_date_format' => formatError('scheduled_end_date', 'Incorrect Schedule end date & time'),
                ]),
            array('field' => 'start_time', 'label' => $index.' start time', 'rules' => 'required', 'errors' => [
                'required' =>  formatError($section.'_start_date', "The $index start time field is required")
            ]),
            array('field' => 'end_time', 'label' => $index.' end time', 'rules' => 'required', 'errors' => [
                'required' =>  formatError($section.'_end_time', "The $index end time field is required")
            ]),

        ];
        $data['scheduled_start_datetime'] = DATE('Y-m-d', strtotime($data['start_date'])) . " " . $data['start_time'];
        $data['scheduled_end_datetime'] = DATE('Y-m-d', strtotime($data['end_date'])) . " " . $data['end_time'];

        if( strtotime($data['scheduled_start_datetime']) > strtotime($data['scheduled_end_datetime'])) {
            $validation_rules[] = array(
                'field' => 'scheduled_end_datetime', 'label' => $index.' shift duration', 'rules' => 'error_message',
                'errors' => [
                    'error_message' => formatError( $section.'_end_date', $index." end date Should be greater than Scheduled Start date")
                    ]
            );
        }

        if($this->getmaxdruation_check($data['scheduled_start_datetime'], $data['scheduled_end_datetime']) && $section == 'scheduled' && isset($data['scheduled_duration']) && $this->hoursToMinutes($data['scheduled_duration']) > 600) {

            $validation_rules[] = array(
                'field' => 'scheduled_duration', 'label' => $index.' shift duration', 'rules' => 'required|error_message',
                'errors' => [
                    'error_message' => formatError('scheduled_section', "Maximum ".$index." shift duration is ".MAX_SHIFT_DURATION." Hrs")
                ]
            );
        } else if(!empty($data['scheduled_duration']) && $this->hoursToMinutes($data['scheduled_duration']) <= 0 && $section == 'scheduled') {

            $validation_rules[] = array(
                'field' => 'scheduled_duration', 'label' => $index.' shift duration', 'rules' => 'required|error_message',
                'errors' => [
                    'error_message' => formatError($section.'_section', "Please enter proper time in $section section")
                ]
            );
        } else if (!empty($data['scheduled_duration']) && $this->hoursToMinutes($data['scheduled_duration']) <= 0) {
            return ['status' => false, 'error' => [$section.'_ndis' => formatError('', 'Missing support items in the plan for the requested shift service')]];
        }

        //Adding validation for support type cleaning
        if($data['supportType'] == SUPPORT_TYPE_SELF_CLEAN) {
            $start_day = date('D', strtotime($data['scheduled_start_datetime']));
            $end_day = date('D', strtotime($data['scheduled_end_datetime']));
            $start_date = date('Y-m-d', strtotime($data['start_date']));
            $end_date = date('Y-m-d', strtotime($data['end_date']));

            $start_time_limit =  $start_date . " 06:00 AM";
            $end_time_limit = $end_date . " 08:00 PM";

            //Validation for checking if the shift date is more than one day
            if(strtotime($start_date) != strtotime($end_date)) {
                $validation_rules[] = array(
                    'field' => 'start_date', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' => formatError($section.'_ndis', "Cleaning supports could be availed only on a single weekday between 6 AM - 8 PM")]
                );
            }

            //validation for public holiday
            $start_is_holiday = $this->check_public_holiday($data, $start_date);
            $end_is_holiday = $this->check_public_holiday($data, $end_date);

            if($start_is_holiday || $end_is_holiday) {
                $validation_rules[] = array(
                    'field' => 'scheduled_end_datetime', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' =>  formatError($section.'_ndis', "Cleaning supports could be availed only on a working day")]
                );
            }
            //validation for checking Week end
            if($start_day == 'Sat' || $start_day == 'Sun'
                || $end_day == 'Sat' || $end_day == 'Sun') {
                $validation_rules[] = array(
                    'field' => 'scheduled_end_datetime', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' => formatError($section.'_ndis', "Cleaning Supports could only fall on Weekdays (Mon - Fri)")]
                );
            }

            //Validation for checking start time and end time
            if(strtotime($data['scheduled_start_datetime']) < strtotime($start_time_limit)
                || strtotime($data['scheduled_end_datetime']) > strtotime($end_time_limit) ) {

                    $validation_rules[] = array(
                        'field' => 'start_time', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                        'errors' => [
                            'error_message' => formatError('scheduled_support_type', "Cleaning supports could be availed only on a single weekday between 6 AM - 8 PM")]
                    );
            }

        } 
        else if ($data['supportType'] == SUPPORT_TYPE_SELF_COMM) {
           
            $scheduled_rows = $data['scheduled_rows'] ?? [];
            $section = $data['section']?? 'scheduled';
            $sleepover = FALSE;
            $sleepover_item = TRUE;
            foreach ($scheduled_rows as $row) {
                # fetching the sleepover id from reference table
                $sleepover = $this->basic_model->get_row('references', ['id'], ['key_name' => 'sleepover', 'id' => is_array($row) ? $row['break_type'] : $row->break_type ]);
                if (!empty($sleepover)) {
                    $sleepover_item = $this->check_so_line_item_availablity_for_comm_access($data['service_agreement_id']);
                    break;
                }
            }
            if (!$sleepover_item) {
                return ['status' => false, 'error' => [$section . '_ndis' => formatError($section . '_ndis', "Self - Care support is not funded in the participant's current active SA")]];
            }
        }

        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return ['status' => FALSE, 'error' => $errors];
        }
        if (isset($data)) {
            $line_items = $this->get_line_items_for_ndis_payment_type($data);
            if(!empty($line_items)) {
                if($line_items['missing_line_item'] == true) {
                    $return = ['status' => false, 'data' => $line_items['line_items'], 'missing_line_item' => $line_items['missing_line_item'], 'auto_insert_flag' => $line_items['auto_insert_flag'], 'error' => [$section.'_ndis' => formatError('', 'Missing support items in the plan for the requested shift service')], 'amount' => $line_items['amount']];
                } else {
                    $return = ['status' => true, 'data' => $line_items['line_items'], 'missing_line_item' => $line_items['missing_line_item'], 'auto_insert_flag' => $line_items['auto_insert_flag'], 'amount' => $line_items['amount']];
                }                
            } else {
                $return = ['status' => FALSE, 'error' => [$section.'_ndis' => formatError('', 'Missing support items in the plan for the requested shift service')]
            ];
            }
        }

        return $return;
    }

    /**
     * To pull the line items for ndis payment support type
     *
     * @param $data {array} array of datas
     *
     * @see dayDifferenceBetweenDate
     * @see hoursDifferenceBetweenDate
     * @see get_start_date_line_items
     *
     * @return $line_items {array} return List of line items
     */
    public function get_line_items_for_ndis_payment_type($data) {
        $line_items = [ 'line_items' => [], 'missing_line_item' => false, 'amount' => '0.00', 'auto_insert_flag' => false ];
        $location = '';
        
        $data['start_date'] =  DATE('Y-m-d', strtotime($data['start_date']));
        $data['end_date'] =  DATE('Y-m-d', strtotime($data['end_date']));
        $get_day_count = dayDifferenceBetweenDate($data['start_date'], $data['end_date']);
        $start_date = DATE('Y-m-d', strtotime($data['start_date']));
        $end_date = DATE('Y-m-d', strtotime($data['end_date']));

        $duration = hoursDiffBwnDates($data['scheduled_start_datetime'], $data['scheduled_end_datetime'], false);
        
        $support_type = $data['supportType'] ?? 0;
        $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
        $support_key_name = '';
        if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
            $support_key_name = $supportType->key_name;
        }
        
        # check the support type & get line item list
        if(!empty($data['supportType']) && ($data['supportType'] == SUPPORT_TYPE_SELF_CARE
            || $data['supportType'] == SUPPORT_TYPE_SELF_CLEAN || $data['supportType'] == SUPPORT_TYPE_SELF_COMM) && $support_key_name != 'mixed') {             
            $line_items = $this->get_start_date_line_items($get_day_count, $data, $start_date, $end_date, $location, $duration);
        } else if ($support_key_name == 'mixed') {
            $line_items = $this->get_start_date_mixed_line_items($get_day_count, $data, $start_date, $end_date, $location, $duration);
        }

        return $line_items;

    }

    /**
     * Get the list of line items for start date
     * - Support type `Mixed`
     *
     * @param $get_day_count {int} day count 0 or > 0
     * @param $data {array} array of datas
     * @param $start_date {string} start date
     * @param $end_date {string} end_date
     * @param $location {string} address
     * @param $duration {int} hours
     *
     * @return $line_items {array} List of line items
     */
    public function get_start_date_mixed_line_items($get_day_count, $data, $start_date, $end_date, $location, $duration) {
        $line_items = [];
        $line_items_all = [];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $day_array = [];
        $day_in = 0;
        $public_holiday = false;
        $sleepOver = [];
        $active_day_array = [];
        $section = $data['section'] ?? '';
        $index = $section == 'scheduled' ? 'Schedule' : 'Actual';
        
        if (empty($data['service_agreement_id'])) {
            $data['service_agreement_id'] = '';
        }
        
        # if two is same as weekday and no holiday mean no need to splitup
        if ($get_day_count > 0) {
            $start_day = $this->get_the_day($start_date);
            $end_day = $this->get_the_day($end_date);

            # check if it's holiday
            $start_is_holiday = $this->check_public_holiday($data, $start_date, $location);
            $end_is_holiday = $this->check_public_holiday($data, $end_date, $location);

            if (($start_day == $end_day && ((empty($start_is_holiday) && empty($end_is_holiday))) || (!empty($start_is_holiday) && !empty($end_is_holiday)))) {
                $get_day_count = 0; 
            }
        }

        # form day array with shift date is one day or two 
        if ($get_day_count < 1) {
            $duration_format = $this->formatHoursToMinutes($duration);
            $duration_min = $this->hoursToMinutes($duration_format);

            $day_array[$day_in] = $this->form_day_array($data, $start_date, $end_date, $start_time, $end_time, $duration, $get_day_count, $location, $duration_min);
            $day_in++;
        } else {
            $two_days_spending_hour = $this->split_more_than_oneday_duration($data, $duration);
            $start_day_duration = $two_days_spending_hour['first_day_hour'];
            $end_day_duration = $two_days_spending_hour['second_day_hour'];

            $first_day_min = $two_days_spending_hour['first_day_min'];
            $second_day_min = $two_days_spending_hour['second_day_min'];
           
            $day_array[$day_in] = $this->form_day_array($data, $start_date, $start_date, $start_time, $end_time, $start_day_duration, 0, $location, $first_day_min);
            $day_in++;

            $day_array[$day_in] = $this->form_day_array($data, $end_date, $end_date, $start_time, $end_time, $end_day_duration, 0, $location, $second_day_min);
            $day_in++;
        }
        $line_item_day_array = $day_array;
         # set standrad flag
         switch ($data['supportType']) {
            case SUPPORT_TYPE_SELF_CARE:
                $standard_flag = true;
                break;
            case SUPPORT_TYPE_SELF_CLEAN:
                $standard_flag = false;
                break;
            default:
                $standard_flag = true;
                break;
        }

        $break_rows = $data['scheduled_rows'] ?? '';
        $br_data = [];
        $start_datetime_raw = $start_date.' '.$start_time;
        $end_datetime_raw = $end_date.' '.$end_time;
        $br_data['start_datetime'] = date('Y-m-d H:i:s', strtotime($start_datetime_raw));
        $br_data['end_datetime'] = date('Y-m-d H:i:s', strtotime($end_datetime_raw));
        
        $br_data['start_date'] = $start_date;
        $br_data['end_date'] = $end_date;
        
        $adjusted_min['is_so_shift'] = FALSE;

        # check if it's have break - unpaid/sleepover
        if(!empty($break_rows)) {
            $adjusted_min['break_rows'] = $break_rows;
            $adjusted_min['is_so_shift'] = TRUE;
            $adjusted_min['day_array'] = $day_array;                
            $adjusted_min['data'] = $data;
            $adjusted_min['before_adjust_min'] = $adjusted_min['after_adjust_min'] = 0;
            
            $break_rows = $this->valid_shift_break_form_array($break_rows, $br_data);
            $day_array = $this->formatBreakDurationwithDate($break_rows, $start_date, $end_date, $get_day_count, $day_array, false);
            # total miniutes after reduce break
            
            $adjusted_min['actual_total_mins'] = (!empty($day_array[0]['duration_time_minute'])) ? $day_array[0]['duration_time_minute'] : 0;
            
            # Get sleepover line item
            $sleepOver = $this->getSleepOverLineItem($day_array, $break_rows, $get_day_count, $public_holiday, $data, $br_data['start_datetime'], $br_data['end_datetime']);
            
            # caluculate sleepover break hr and adjust the unit
            if (!empty($sleepOver) && isset($sleepOver['sa_line_item_all']) && isset($sleepOver['sa_line_item']) && $section == 'actual') {
                $day_array = $this->sleepoverUnitCalculation($break_rows, $start_datetime_raw, $end_datetime_raw, $get_day_count, $day_array);
                
                $adjusted_min['before_adjust_min'] = (!empty($day_array[0]['before_adjustment'])) ? $day_array[0]['before_adjustment'] : 0;
                $adjusted_min['after_adjust_min'] = (!empty($day_array[0]['after_adjustment'])) ? $day_array[0]['after_adjustment']: 0;
                $adjusted_min['day_array'] = $day_array;                
                $adjusted_min['data'] = $data;
                $adjusted_min['break_rows'] = $break_rows;
                
            }
        }

        $support_type_duration = $data['support_type_duration'] ?? [];
        #Total active duration both Schedule and actual
        $active_duration = $this->hoursToMinutes($data['scheduled_duration']);
        
        #Sleep over min unit adjustment if shift has S/O shifts
        if(!empty($sleepOver['sa_line_item_all']) && $section != 'scheduled') {
            #sleep over minimum unit adjustment start with min 1 hr and end with min 4 hr
            $active_day_array = $this->sleepover_min_unit_adjustment($get_day_count, $active_day_array);
            if(!empty($active_day_array)) {
                #Units adjustment for support type values
                $support_type_duration = $this->mixed_type_so_duration_adjustment($active_day_array, $support_type_duration, $get_day_count);
                           
            }
        }
        #Ndis minimum 2 units active duration adjustment
        else if($active_duration <= 120) {       
            $support_type_duration = $this->mixed_type_min_unit_adjustment($line_item_day_array, $support_type_duration, $active_duration, $get_day_count);
        }
        if ($get_day_count < 1) {
            $day_array[] = $day_array[0];
            $day_array = $this->format_duration_for_mixed_item($support_type_duration, $day_array);   
            
        } else if ($get_day_count > 0) {
            if(!empty($support_type_duration)) {

                $new_day_array = [];
                for($i=0; $i<=3; $i++) {
                    $key = 0;
                    if($i == 2 || $i == 3) {
                        $key = 1;
                    }
                    $new_day_array[$i] = $day_array[$key];
                }
                $day_array = $this->format_duration_for_mixed_item($support_type_duration, $new_day_array);
            }
            
        } else if ($get_day_count > 0) {
            if(!empty($support_type_duration)) {               
                $day_array = $this->format_duration_for_mixed_item($support_type_duration, $day_array, 2);
            }

        } else {
            $day_array = [];
        }

        # Get NDIS line item list 
        list($line_items_all, $line_items, $day_array) = $this->getLineItemList($data, $day_array, $sleepOver, $standard_flag, $adjusted_min, true);
        
        # Calculate the sub total & get missing line item
        list($line_items_all, $line_items, $total_amount, $missing_line_item, $auto_insert_flag) = $this->calculateSubTotAmount($line_items_all, $line_items, $data);
        
        return [ 'line_items' => $line_items_all, 'missing_line_item' => $missing_line_item, 'auto_insert_flag' => $auto_insert_flag, 'amount' => $total_amount ];
    }

    /**
     * Get the list of line items for start date
     *
     * @param $get_day_count {int} day count 0 or > 0
     * @param $data {array} array of datas
     * @param $start_date {string} start date
     * @param $end_date {string} end_date
     * @param $location {string} address
     * @param $duration {int} hours
     *
     * @return $line_items {array} List of line items
     */
    public function get_start_date_line_items($get_day_count, $data, $start_date, $end_date, $location, $duration) {
        
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $day_array = [];
        $day_in = 0;
        $public_holiday = false;
        $sleepOver = [];
        $section = $data['section'] ?? '';
        $index = $section == 'scheduled' ? 'Schedule' : 'Actual';
        
        if (empty($data['service_agreement_id'])) {
            $data['service_agreement_id'] = '';
        }
        
        # if two is same as weekday and no holiday mean no need to splitup
        if ($get_day_count > 0) {
            $start_day = $this->get_the_day($start_date);
            $end_day = $this->get_the_day($end_date);

            # check if it's holiday
            $start_is_holiday = $this->check_public_holiday($data, $start_date, $location);
            $end_is_holiday = $this->check_public_holiday($data, $end_date, $location);

            if (($start_day == $end_day && ((empty($start_is_holiday) && empty($end_is_holiday))) || (!empty($start_is_holiday) && !empty($end_is_holiday)))) {
                $get_day_count = 0; 
            }
        }
        //Single day
        if ($get_day_count < 1) {
            $duration_format = $this->formatHoursToMinutes($duration);
            $duration_min = $this->hoursToMinutes($duration_format);

            $day_array[$day_in] = $this->form_day_array($data, $start_date, $end_date, $start_time, $end_time, $duration, $get_day_count, $location, $duration_min);
            $day_in++;
        } else {
            $two_days_spending_hour = $this->split_more_than_oneday_duration($data, $duration);
            $start_day_duration = $two_days_spending_hour['first_day_hour'];
            $end_day_duration = $two_days_spending_hour['second_day_hour'];

            $first_day_min = $two_days_spending_hour['first_day_min'];
            $second_day_min = $two_days_spending_hour['second_day_min'];
           
            $day_array[$day_in] = $this->form_day_array($data, $start_date, $start_date, $start_time, $end_time, $start_day_duration, 0, $location, $first_day_min);
            $day_in++;

            $day_array[$day_in] = $this->form_day_array($data, $end_date, $end_date, $start_time, $end_time, $end_day_duration, 0, $location, $second_day_min);
            $day_in++;
        }

        # check standrad flag
        switch ($data['supportType']) {
            case SUPPORT_TYPE_SELF_CARE:
                $standard_flag = true;
                break;
            case SUPPORT_TYPE_SELF_CLEAN:
                $standard_flag = false;
                break;
            default:
                $standard_flag = true;
                break;
        }

        $break_rows = $data['scheduled_rows'] ?? '';
        $br_data = [];
        $start_datetime_raw = $start_date.' '.$start_time;
        $end_datetime_raw = $end_date.' '.$end_time;
        $br_data['start_datetime'] = date('Y-m-d H:i:s', strtotime($start_datetime_raw));
        $br_data['end_datetime'] = date('Y-m-d H:i:s', strtotime($end_datetime_raw));
        
        $br_data['start_date'] = $start_date;
        $br_data['end_date'] = $end_date;
        
        $adjusted_min['is_so_shift'] = FALSE;
        //Only while breaks available
        if(!empty($break_rows)) {
            $adjusted_min['break_rows'] = $break_rows;
            $adjusted_min['is_so_shift'] = TRUE;
            $adjusted_min['day_array'] = $day_array;                
            $adjusted_min['data'] = $data;
            $adjusted_min['before_adjust_min'] = $adjusted_min['after_adjust_min'] = 0;
            
            $break_rows = $this->valid_shift_break_form_array($break_rows, $br_data);
            $day_array = $this->formatBreakDurationwithDate($break_rows, $start_date, $end_date, $get_day_count, $day_array);
            #total miniutes after reduce break
            
            $adjusted_min['actual_total_mins'] = (!empty($day_array[0]['duration_time_minute'])) ? $day_array[0]['duration_time_minute'] : 0;
            
            # sleepover line item pull
            $sleepOver = $this->getSleepOverLineItem($day_array, $break_rows, $get_day_count, $public_holiday, $data, $br_data['start_datetime'], $br_data['end_datetime']);
            
            # caluculate sleepover break hr and adjust the unit
            if (!empty($sleepOver) && isset($sleepOver['sa_line_item_all']) && isset($sleepOver['sa_line_item']) && $section == 'actual') {
                $day_array = $this->sleepoverUnitCalculation($break_rows, $start_datetime_raw, $end_datetime_raw, $get_day_count, $day_array);
                
                $adjusted_min['before_adjust_min'] = (!empty($day_array[0]['before_adjustment'])) ? $day_array[0]['before_adjustment'] : 0;
                $adjusted_min['after_adjust_min'] = (!empty($day_array[0]['after_adjustment'])) ? $day_array[0]['after_adjustment']: 0;
                $adjusted_min['day_array'] = $day_array;                
                $adjusted_min['data'] = $data;
                $adjusted_min['break_rows'] = $break_rows;
                
            }
        }
        
        # Get NDIS line item list 
        list($line_items_all, $line_items, $day_array) = $this->getLineItemList($data, $day_array, $sleepOver, $standard_flag, $adjusted_min, false);

        # Calculate sub total amount and missing line item
        list($line_items_all, $line_items, $total_amount, $missing_line_item, $auto_insert_flag) = $this->calculateSubTotAmount($line_items_all, $line_items, $data);

        return [ 'line_items' => $line_items_all, 'missing_line_item' => $missing_line_item, 'auto_insert_flag' => $auto_insert_flag, 'amount' => $total_amount ];
    }

    /**
     * Get NDIS line item list 
     * @param {array} data
     * @param {array} day_array
     * @param {array} sleepOver
     * @param {boolean} standard_flag
     * @param {array} adjusted_min
     * @param {boolean} type_is_mixed
     */
    public function getLineItemList($data, $day_array, $sleepOver, $standard_flag, $adjusted_min, $type_is_mixed = false) {
        $line_items = [];
        $line_items_all = [];

        # sum of total mintues from array
        $minDuration = 120; // min 2 hours
        $totalMinDuration = array_sum(array_column($day_array, 'duration_time_minute'));

        # minus the min duration with total duration
        $additionHour = $minDuration - $totalMinDuration;
        if ($additionHour < 0) {
            $additionHour = 0;
        }

        # check day_of_week with prior order
        $dataValue = array_column($day_array, 'day_of_week');

        if (in_array('public_holiday', $dataValue)) {
            $minHourDay = 'public_holiday';
        } else if (in_array('sunday', $dataValue)) {
            $minHourDay = 'sunday';
        } else if (in_array('saturday', $dataValue)) {
            $minHourDay = 'saturday';
        } else if (in_array('weekday', $dataValue)) {
            $minHourDay = 'weekday';
        } else {
            $minHourDay = '';
        }
        
        foreach($day_array as $day_in => $day_item) {
            if ($day_item['duration_time_minute'] <= 0) {
                continue;
            }
            
            $day_of_week = $day_item['day_of_week']??'';
            $day_of_time = $day_item['day_of_time']??'';

            # if support type is mixed then get support type from day_array
            if ($type_is_mixed) {
                $support_type = $day_item['support_type'] ?? '';
            } else {
                $support_type = $data['supportType'];
            }
            
            # it is sleepover then line item should be evening if day_of_time is overnight
            # And support type not equals to mixed 
            # OR
            # if support type is mixed and day time is overnight with S/O then set overnight into evening
            if (!empty($sleepOver) && isset($sleepOver['sa_line_item_all']) &&
                     isset($sleepOver['sa_line_item']) && $day_of_time == 'overnight' && !$type_is_mixed 
                     || (!empty($sleepOver) && $day_of_time == 'overnight' && $type_is_mixed)) {
                $day_of_time = 'evening';
            }

            # add min hour to line item if additionHour greater than zero and day or week equal with prioir order
            # And support type not equals to mixed 
            if ($day_of_week == $minHourDay && $additionHour > 0 && !$type_is_mixed) {
                $minHourAddition = $day_item['duration_time_minute'] + $additionHour;
                $hoursAndMins = hoursandmins($minHourAddition);
                $formatHoursAndMinutes = formatHoursAndMinutes($hoursAndMins);
                # over write values
                $day_array[$day_in]['duration_time'] = $day_item['duration_time'] = $formatHoursAndMinutes;
                $day_array[$day_in]['duration_time_minute'] = $day_item['duration_time_minute'] = $minHourAddition;
            }

            $duration = $day_item['duration_time'];
           
            $start_date = $day_item['start_date'];
            $end_date = $day_item['end_date'];

            $start_datetime = $start_date.' '.$day_item['start_time'];
            $end_datetime = $end_date.' '.$day_item['end_time'];

            $start_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
            $end_datetime = date('Y-m-d H:i:s', strtotime($end_datetime));
            
            switch($day_of_week) {
                case 'public_holiday':
                    $get_line_items = $this->fetch_ndis_line_items('public_holiday', $data['service_agreement_id'], $support_type, $duration, '', $standard_flag);
                    $line_items = array_merge($line_items, $get_line_items);
                    
                    $get_line_items_all = $this->fetch_ndis_line_items_all('public_holiday', $data['service_agreement_id'], $support_type, $duration, '', $standard_flag, $start_datetime, $end_datetime, $adjusted_min, $data);
                    $line_items_all = array_merge($line_items_all, $get_line_items_all);
                    break;
                case 'saturday':
                case 'sunday':
                    $get_line_items = $this->fetch_ndis_line_items($day_of_week, $data['service_agreement_id'], $support_type, $duration, '', $standard_flag);
                    $line_items = array_merge($line_items, $get_line_items);

                    $get_line_items_all = $this->fetch_ndis_line_items_all($day_of_week, $data['service_agreement_id'], $support_type, $duration, '', $standard_flag, $start_datetime, $end_datetime, $adjusted_min, $data);
                    $line_items_all = array_merge($line_items_all, $get_line_items_all);
                    break;
                case 'weekday':
                    # get time
                    $time = $this->time_of_day[$day_of_time] ?? '';
                    $get_line_items = $this->fetch_ndis_line_items($day_of_week, $data['service_agreement_id'], $support_type, $duration, $time, $standard_flag);
                    $line_items = array_merge($line_items, $get_line_items, $data);

                    $get_line_items_all = $this->fetch_ndis_line_items_all($day_of_week, $data['service_agreement_id'], $support_type, $duration, $time, $standard_flag, $start_datetime, $end_datetime, $adjusted_min, $data);
                    $line_items_all = array_merge($line_items_all, $get_line_items_all);
                    break;
                default:
                    break;
            }
        }       
        
        # Add sleepover line item if count value greater than 0
        if (!empty($sleepOver) && isset($sleepOver['sa_line_item_all']) && isset($sleepOver['sa_line_item'])) {
            $get_line_items = $sleepOver['sa_line_item'];
            $get_line_items_all = $sleepOver['sa_line_item_all'];
            $line_items = array_merge($line_items, $get_line_items);
            $line_items_all = array_merge($line_items_all, $get_line_items_all);
            
        }
       
        return [ $line_items_all, $line_items, $day_array ];
    }

    /**
     * Calculate Sub total amount & define missing line item
     * @param {array} line_items_all
     * @param {array} line_items
     * @param {array} data
     */
    public function calculateSubTotAmount($line_items_all, $line_items, $data) {
        $auto_insert_flag = $missing_line_item = false;
        $total_amount = 0;
        
        # auto insertions
        foreach($line_items_all as $item_key => $item) {            
            $line_item_number = $item['line_item_number'];
            $key = array_search($line_item_number, array_column($line_items, 'line_item_number'));
            $auto_insert_flag_tmp = false;
            $line_items_all[$item_key]['sa_line_item_id'] = '';            
            if ($key === false) {
                $is_parent_check = $this->check_sa_parent_line_item_availablity($item, $data['service_agreement_id']);
                # Sets auto insert flag and missing line itme flag true if parent line item not found
                if(!$is_parent_check) {
                    $auto_insert_flag_tmp = true;
                    $auto_insert_flag = true;
                    $missing_line_item = true;
                }
            } else {
                $amount_sa = $line_items[$key]['amount'];
            }

            # amount calculation duration * price
            $amount = $line_items_all[$item_key]['amount'];
            $duration_raw = $this->formatHoursToMinutes($line_items_all[$item_key]['duration']);
            $line_items_all[$item_key]['duration_raw'] = $duration_raw;
            $total_min = $this->hoursToMinutes($duration_raw);
            if ($amount > 0) {
                $min_per_cost = $amount / 60;
            } else {
                $min_per_cost = 0;
            }
            # min per line item
            $cost_per_litem = $min_per_cost * $total_min;
            $total_amount = $cost_per_litem + $total_amount;
            $cost_per_litem = round($cost_per_litem, 2);
            $format_amount = $cost_per_litem;

            $line_items_all[$item_key]['sub_total'] = $format_amount;
            $line_items_all[$item_key]['sub_total_raw'] = strVal($format_amount);
            $line_items_all[$item_key]['auto_insert_flag'] = $auto_insert_flag_tmp;
        }
        
        $total_amount = round($total_amount, 2);
        $total_amount = number_format($total_amount, 2);

        return [$line_items_all, $line_items, $total_amount, $missing_line_item, $auto_insert_flag];
    }

    /** Get day priority */
    function reduce_druation_with_breaktime($day_array, $day_count, $durationBreakTime, $unset) {       
        if(!empty($day_array)) { 
            foreach($day_array as $key => $day) {               
                
                //Deduct if start and endtime mentioned value for start date
                if(!empty($durationBreakTime[$day['start_date']])) {
                    $duration = $day_array[$key]['duration_time_minute'] - $durationBreakTime[$day['start_date']];                    
                    $duration_raw = hoursandmins($duration);
                    $day_array[$key]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[$key]['duration_time_minute'] = $duration;
                    
                }

                //Deduct if end and endtime mentioned value for end date
                if($day['start_date'] != $day['end_date'] && isset($durationBreakTime[$day['end_date']]) && !empty($durationBreakTime[$day['end_date']])) {
                    $duration = $day_array[$key]['duration_time_minute'] - $durationBreakTime[$day['end_date']];                    
                    $duration_raw = hoursandmins($duration);
                    $day_array[$key]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[$key]['duration_time_minute'] = $duration;
                }

                //set the priority for more than one day
                if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count > 0) {
                    switch($day['day_of_week']) {
                        case 'public_holiday':
                            $priority = 4;
                            break;
                        case 'sunday':
                            $priority = 3;
                            break;
                        case 'saturday':
                            $priority = 2;
                            break;
                        case 'weekday':
                            $priority = 1;                
                            break;
                        default:
                            break;
                    }
                    $day_array[$key]['priority'] = $priority;
                }
            }
            
            //Adjust time duration based on the lowest value if more than two days 
            if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count > 0) {
             //Check if start day priority is lesser than next day
              if($day_array[0]['priority'] <= $day_array[1]['priority']) {
                $duration = $day_array[0]['duration_time_minute'] - $durationBreakTime['duration'];
                
                if($duration > 0) {
                    $duration_raw = hoursandmins($duration);
                    $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[0]['duration_time_minute'] = $duration;
                } else {
                    //Remove start day which is in negative or zero value
                    if ($unset === true) {
                        unset($day_array[0]);    
                    }  else {
                        $duration_raw = $duration < 1 ? 0 : hoursandmins($duration);
                        $duration = $duration < 1 ? 0 : $duration;
                        $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[0]['duration_time_minute'] = $duration;
                    }                  
                    //Add negative or zero value for adjust the next day duration
                    $duration = $day_array[1]['duration_time_minute'] + $duration;
                    $duration_raw = hoursandmins($duration);
                    $day_array[1]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[1]['duration_time_minute'] = $duration;
                }
              }  //Check if next day priority is lesser than start day          
              else if($day_array[1]['priority'] <= $day_array[0]['priority']) {
                $duration = $day_array[1]['duration_time_minute'] - $durationBreakTime['duration'];
                
                if($duration > 1) {
                    $duration_raw = hoursandmins($duration);
                    $day_array[1]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[1]['duration_time_minute'] = $duration;
                } else {
                    //Remove start day which is in negative or zero value
                    if ($unset === true) {
                        unset($day_array[1]);
                    }  else {
                        $duration_raw = $duration < 1 ? 0 : hoursandmins($duration);
                        $duration = $duration < 1 ? 0 : $duration;
                        $day_array[1]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[1]['duration_time_minute'] = $duration;
                    } 
                    //Add negative or zero value for adjust the next day duration
                    $duration = $day_array[0]['duration_time_minute'] + $duration;
                    $duration_raw = hoursandmins($duration);
                    $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[0]['duration_time_minute'] = $duration;
                }
              }
             
            } // Single day with only durations
            else if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count < 1) {
               
                $duration = $day_array[0]['duration_time_minute'] - $durationBreakTime['duration'];
                   
                $duration_raw = hoursandmins($duration);
                $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                $day_array[0]['duration_time_minute'] = $duration;
            }

            $day1_time = $day2_time = 0;

            #Get single day specific active duration after deduct from breaks for S/O min units adjustments
            if($day['start_date'] != $day['end_date'] && !empty($durationBreakTime) && 
                !empty( $durationBreakTime[$day['start_date']]) && !empty( $durationBreakTime[$day['end_date']])) {
                $start_date_time = date("Y-m-d H:i:s", strtotime($day['start_date'] ." ". $day['start_time']));
                $end_date_time = date("Y-m-d H:i:s", strtotime($day['end_date'] ." ". $day['end_time']));
                
                $end_time = date("Y-m-d H:i:s", strtotime($day['end_date']));
                #get day1_time, day1 Actual duration - sleepover break - duration
                $day1_time = minutesDifferenceBetweenDate($start_date_time, $end_time) - $durationBreakTime[$day['start_date']] - $durationBreakTime['duration'];

                #get day2_time, day2 Actual duration - sleepover break - duration - overflow (negative )unpaid break time from day1
                $day2_time = minutesDifferenceBetweenDate($end_time, $end_date_time) - $durationBreakTime[$day['end_date']] + ($day1_time < 0 ? $day1_time : 0);
                $day_array[0]['day1_duration_time_minute'] = $day1_time > 0 ? $day1_time : 0;
                $day_array[0]['day2_duration_time_minute'] = $day2_time > 0 ? $day2_time : 0;                    
                
            }          
            
        }

        return $day_array;
    }

    /**
     * Convert hours to mintues
     * @param {$hours} str
     */
    function formatHoursToMinutes($time) 
    {
        $hours = '00';
        $mintues = '00';
        $time_ex = explode(' ', $time);
        if (!empty($time_ex[0])) {
            $hours = str_replace("h", "", $time_ex[0]);
            $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
        }
        if (!empty($time_ex[1])) {
            $mintues = str_replace("m", "", $time_ex[1]);
            $mintues = str_pad($mintues, 2, '0', STR_PAD_LEFT);
        }

        $time_raw = $hours.':'.$mintues;
        return $time_raw;
    }

    /**
     * Convert hours to mintues
     * @param {$hours} str
     */
    function hoursToMinutes($hours) 
    { 
        $minutes = 0; 
        $time = '';
        if (strpos($hours, ':') !== false) 
        { 
            // Split hours and minutes. 
            list($hours, $minutes) = explode(':', $hours); 

            $time = $hours * 60 + $minutes;
        } 
        return $time;
    }

    /**
     * Form array week
     *
     * @param $get_day_count {int} day count 0 or > 0
     * @param $data {array} array of datas
     * @param $start_date {string} start date
     * @param $end_date {string} end_date
     * @param $location {string} address
     * @param $duration {int} hours
     */
    function form_day_array($data, $start_date, $end_date, $start_time, $end_time, $duration, $get_day_count, $location, $minutes) {
        $supportType = $data['supportType'] ?? '';
        $day_array = [];
        $day_array['start_date'] = $start_date;
        $day_array['end_date'] = $end_date;
        $day_array['start_time'] = $start_time;
        $day_array['end_time'] = $end_time;
        $day_array['duration_time'] = $duration;
        $day_array['duration_time_minute'] = $minutes;
        $day_array['duration_day'] = $get_day_count;

        # check day of week is weekend
        $day_of_week = '';
        $check_day = $this->get_the_day($start_date);
        if ($check_day != '') {
            $day_of_week = $check_day;
        }

        # check day of week is public holday
        $check_holiday = $this->check_public_holiday($data, $start_date, $location);
        if ($check_holiday) {
            $day_of_week = 'public_holiday';
        }

        $day_array['day_of_week'] = $day_of_week;

        # get day_of_time
        $start_time_str = strtotime($start_time);
        $end_time_str = strtotime($end_time);
        $get_time = $this->get_day_of_time($start_time_str, $end_time_str, $supportType);

        $day_array['day_of_time'] = $get_time;

        return $day_array;
    }

    /**
     *
     * To Check whether particular the date is met with public holiday or not
     *
     * @param $data {array} data
     * @param $check_publicholiday_date {string} date needs to be checking
     *  for public holiday
     * @param $location {string} - Optional param
     *
     * @see devide_google_or_manual_address - helps to get the state code
     *
     * @return {string} return the date
     */
    public function check_public_holiday($data, $check_publicholiday_date, $location = '') {
        if(!empty($data['account_address'])) {
            //full_account_address account address coming from shift creation API
            $account_address = (!empty($data['full_account_address'])) ? $data['full_account_address']['label'] : $data['account_address']->label;
            //To get the state code
            $address = devide_google_or_manual_address($account_address);

            if(!empty($address['state'])) {
                $data = $this->basic_model->get_row('state', ['name'], ['id' => $address['state']]);
                $location = $data->name ?? '';
            }
        }

        $condition = ['DATE(date)' => $check_publicholiday_date,
         'status' => 1, 'archive' => 0];

         if($location) {
           $condition['location like '] = '%' . $location . '%';
         }
        return $this->basic_model->get_row('holidays', ['date'], $condition);

    }

    /**
     *
     * To Check whether particular the date is met with Week end saturday or sunday
     *
     * @param $data {array} data
     * @param $date {string} date needs to be checking
     *  for Week end
     * @param $location {string} - Optional param
     *
     * @see devide_google_or_manual_address - helps to get the state code
     *
     * @return {string} return the date
     */
    public function check_week_end($data, $date) {
        $return = FALSE;
        $day = $this->get_the_day($date);

        if($day == 'saturday' || $day == 'sunday'){
            $return = TRUE;
        }
       return $return;
    }

    public function get_the_day($date) {

        $day = DATE('D', strtotime($date));

        switch ($day) {
            case 'Sat':
                $return = 'saturday';
                break;
            case 'Sun':
                $return = 'sunday';
                break;
            default:
                $return = 'weekday';
                break;
        }

        return $return;
    }

    /**
     * Pull the list of Line itmes
     *
     * @param $daytype {string} daytype 'public_holiday, week_end, week_day'
     * @param $service_agreement_id {int} service agreementID
     * @param $support_type {id} support type (1-self care, 2-cleaning, 2-Comm Access)
     * @param $duration {int} total duration between start and end date time
     *
     * @return {array} line items with hour
     */
    public function fetch_ndis_line_items($daytype, $service_agreement_id, $support_type, $duration, $day_of_time = '', $standard_flag = false) {

        $weekdayID = '';
        
        #Fetch selfcare sleepover shift for comm access sleepover shift
        if($support_type == SUPPORT_TYPE_SELF_COMM && $daytype == 'sleepover') {            
            $support_type = SUPPORT_TYPE_SELF_CARE;
        }

        $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');

        $this->db->select(["fli.id as line_item_id", "fli.line_item_number", "sai.id as sa_line_item_id", "CONCAT(fli.line_item_number,' ', fli.line_item_name, '(', '".$duration."' , ')' ) AS line_item_value", "CONCAT( '".$duration."') as duration", "sai.amount"]);

        $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");

        $this->db->join("tbl_finance_support_registration_group as grp", "grp.id = fli.support_registration_group and grp.is_standard = 1", "inner");

        $this->db->where(['sai.archive' => 0, 'sai.service_agreement_id' => $service_agreement_id, 'support_type' => $support_type]);

        if ($standard_flag == true) {
            $this->db->where('fli.line_item_number like "%_T"');
        }
        
        switch ($daytype) {
            case 'weekday':
                $weekdayID = WEEKDAY_ID;
                break;
            case 'saturday':
                $weekdayID = SATURDAY_WEEKDAY_ID;
                break;
            case 'sunday':
                $weekdayID = SUNDAY_WEEKDAY_ID;
                break;
            case 'public_holiday':
                $weekdayID = PUBLIC_HOLIDAY_WEEKDAY_ID;
                break;
            default:
                $weekdayID = '';
                break;
        }

        if($weekdayID) {
            $this->db->where("fli.{$daytype}", 1);
        }

        if($day_of_time != '' && ($daytype == 'weekday' || $daytype == 'sleepover')) {
            $this->db->where("fli.{$day_of_time}", 1);
            $this->db->limit(1);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->result_array();
    }

    // To check the maximum shift duration(10Hr) using shift start time end time.
    public function getmaxdruation_check($start_datetime, $end_datetime) {
        
        $shift_minutes = minutesDifferenceBetweenDate($start_datetime, $end_datetime);
            $shift_hours = 0;
            if($shift_minutes) {
                $shift_hours = round(($shift_minutes) / 60, 4);
                if($shift_hours > MAX_SHIFT_DURATION) {
                   return TRUE;
                }
            }
    }

    /**
     * split and pull the hours if day exceeded more than one day
     *
     * @param $data {array} array of datas
     * @param $duration {int} durations in a hour basis
     *
     * @return {array} first half and second half hour
     */
    public function split_more_than_oneday_duration($data, $duration) {

        $start_time = $data['scheduled_start_datetime'];
        $sec_end_time = $data['scheduled_end_datetime'];
        $end_time = (date('Y-m-d', strtotime($data['end_date'])) . " ". "12:00 AM");
        $first_day_hr = hoursDiffBwnDates($start_time , $end_time);
        $second_day_hr = hoursDiffBwnDates($end_time , $sec_end_time);
        
        $first_day_min = minutesDifferenceBetweenDate($start_time , $end_time);
        $second_day_min = minutesDifferenceBetweenDate($end_time , $sec_end_time);

        return['first_day_hour' => $first_day_hr, 'second_day_hour' => $second_day_hr, 
        'first_day_min' => $first_day_min, 'second_day_min' => $second_day_min];
    }

    public function get_day_of_time($start_time, $end_time, $supportType) {
        $time = '';
        $day_time_1 = strtotime("06:00:00");
        $day_time_2 = strtotime("20:00:00");
        $even_time_1 = strtotime("20:00:00");
        $even_time_2 = strtotime("00:00:00");
        $overnight_time = strtotime("00:00:00");
        /* If the time is grater than or equal to 06:00 hours, and less than or equal to 20:00 hours, so daytime */
        if ($start_time >= $day_time_1 && $end_time <= $day_time_2 && $end_time > $start_time) {
            $time = "daytime";
        } else
        /* If the time is grater than or equal to 20:01 hours, and less than or equal to 23:59 hours, so evening */
        if ($start_time >= $even_time_1 && $end_time <= $even_time_2) {
            $time = "evening";
        } else
        /* If the time is grater than or equal to 06:00 hours and 20:01 hours, so evening */
        if ($start_time >= $day_time_1 && $end_time >= $even_time_1) {
            $time = "evening";
        } else
        /* Should the time be between or equal to 00:00, so overnight */
        if ($start_time >= $overnight_time) {
            $time = "overnight";
        } else
        /* If the time is grater than or equal to 21:01 hours, and grater than or equal to 00:01 hours, so evening */
        if ($start_time >= $even_time_1 && $end_time >= $overnight_time) {
            $time = "overnight";
        } else {
            $time ='';
        }

        # if support type is community access && time is overnight then change it to evening
        if ($time === "overnight" && $supportType == SUPPORT_TYPE_SELF_COMM) {
            $time = "evening";
        }

        return $time;
    }

    /**
     * Pull the list of Line itmes
     *
     * @param $daytype {string} daytype 'public_holiday, week_end, week_day'
     * @param $service_agreement_id {int} service agreementID
     * @param $support_type {id} support type (1-self care, 2-cleaning, 2-Comm Access)
     * @param $duration {int} total duration between start and end date time
     *
     * @return {array} line items with hour
     */
    public function fetch_ndis_line_items_all($daytype, $service_agreement_id, $support_type, $duration, $day_of_time = '', $standard_flag = false, $start_date_time, $end_date_time, $adjusted_min = '', $data = '') {
        
        $weekdayID = '';
        $section = $data['section'] ?? '';
        $start_date = date('Y-m-d', strtotime($start_date_time));
        $end_date = date('Y-m-d', strtotime($end_date_time));
        $support_key_name = $data['support_key_name'] ?? '';

        #Fetch selfcare sleepover shift for comm access OR mixed type sleepover shift
        if(($support_type == SUPPORT_TYPE_SELF_COMM || $support_key_name == 'mixed') && $daytype == 'sleepover') {
            $support_type = SUPPORT_TYPE_SELF_CARE;
        }

        $this->db->from(TBL_PREFIX . 'finance_line_item as fli');

        $this->db->select(["fli.id as line_item_id", "fli.line_item_number", "fli.line_item_name", "CONCAT(fli.line_item_number,' ', fli.line_item_name ) AS line_item_value", "CONCAT( '".$duration."') as duration", "flip.upper_price_limit as amount", "fli.category_ref", "flip.id as line_item_price_id"]);

        $this->db->join("tbl_finance_support_registration_group as grp", "grp.id = fli.support_registration_group and grp.is_standard = 1", "inner");

        $this->db->join(TBL_PREFIX."finance_line_item_price as flip", "fli.id = flip.line_item_id AND flip.archive = 0 AND (STR_TO_DATE('{$end_date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`flip`.`start_date`, '%Y-%m-%d') AND DATE_FORMAT(`flip`.`end_date`, '%Y-%m-%d'))", "left", false);

        $this->db->where(['support_type' => $support_type]);

        if ($standard_flag == true) {
            $this->db->where('fli.line_item_number like "%_T"');
        }

        switch ($daytype) {
            case 'weekday':
                $weekdayID = WEEKDAY_ID;
                break;
            case 'saturday':
                $weekdayID = SATURDAY_WEEKDAY_ID;
                break;
            case 'sunday':
                $weekdayID = SUNDAY_WEEKDAY_ID;
                break;
            case 'public_holiday':
                $weekdayID = PUBLIC_HOLIDAY_WEEKDAY_ID;
                break;
            default:
                $weekdayID = '';
                break;
        }
        if($weekdayID) {
            $this->db->where("fli.{$daytype}", 1);
        }

        if($day_of_time != '' && 
        ($daytype == 'weekday' || $daytype == 'sleepover')) {
            $this->db->where("fli.{$day_of_time}", 1);
            $this->db->limit(1);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result_data = $query->result_array();
        
        foreach($result_data as $key => $result) {
            
            if(!$result['line_item_price_id'] && $section == 'scheduled') {
                $result_data[$key]['amount'] = $this->get_recent_item_price($result['line_item_id']);
            }
            //Check days not fall in single day and check the start date match with the old price list
            if($start_date != $end_date) {
                $this->db->from(TBL_PREFIX . 'finance_line_item as fli');

                $this->db->select(["fli.id as line_item_id", "fli.line_item_number", "fli.line_item_name", "CONCAT(fli.line_item_number,' ', fli.line_item_name ) AS line_item_value", "CONCAT( '".$duration."') as duration", "flip.upper_price_limit as amount", "fli.category_ref", "flip.id as line_item_price_id"]);
                $this->db->join(TBL_PREFIX."finance_line_item_price as flip", "fli.id = flip.line_item_id AND flip.archive = 0", "left", false);
                $this->db->where('flip.end_date', $start_date);
                $this->db->where('support_type', $support_type);
                $this->db->where('line_item_number', $result['line_item_number']);
                $qry = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
                $res = $qry->result_array();
                $result_data[$key]['is_old_price'] = FALSE;
                
                #Sleep over shift calculation based on the active duration adjustment
                if(!empty($res) && !empty($adjusted_min['is_so_shift']) && $adjusted_min['is_so_shift'] && ($daytype == 'weekday' || $daytype == 'public_holiday')) {
                    
                    $total_minutes = $adjusted_min['day_array'][0]['duration_time_minute'];
                   
                    # If available both before after adjust minutes
                    if($adjusted_min['before_adjust_min'] > 0 && $adjusted_min['after_adjust_min'] > 0) {
                        $res[0]['duration'] = $adjusted_min['before_adjust_min']."h";
                        $result_data[$key]['duration'] = $adjusted_min['after_adjust_min']."h";                   
                    }
                    else if($adjusted_min['before_adjust_min'] > 0) {
                        # If available only before adjust minutes
                        $res[0]['duration'] = $adjusted_min['before_adjust_min']."h";
                        #second the second day value by reduce with total - before active duration
                        $hrandmin = $total_minutes - $adjusted_min['before_adjust_min'] * 60;
                        $second_day = hoursandmins($hrandmin);
                        $second_day_duration = formatHoursAndMinutes($second_day);                         
                        $result_data[$key]['duration'] = $second_day_duration;
                    }
                    else if($adjusted_min['after_adjust_min'] > 0) {
                        # If available only after adjust minutes
                        #second the first day value by reduce with total - after active duration
                        $hrandmin = $total_minutes - $adjusted_min['after_adjust_min'] * 60;
                        $first_day = hoursandmins($hrandmin);
                        $first_day_duration = formatHoursAndMinutes($first_day);
                        $res[0]['duration'] = $first_day_duration;
                        $result_data[$key]['duration'] = $adjusted_min['after_adjust_min']."h";    
                    } else {
                        //If there is no S/O minimum adjustment then get the active duration between start and end date and then replace it
                        $start_datetime = $adjusted_min['day_array'][0]['start_date']. " ". $adjusted_min['day_array'][0]['start_time'];
                        $end_datetime = $adjusted_min['day_array'][0]['end_date']. " ". $adjusted_min['day_array'][0]['end_time'];
                        
                        $ismin2unit = FALSE;
                        if($adjusted_min['actual_total_mins'] < 120) {
                            $ismin2unit = TRUE;
                        }

                        $active_duration = $this->getActiveDurationBetweenDate(object_to_array ($adjusted_min['break_rows']), $start_datetime, $end_datetime, $adjusted_min['data'], $ismin2unit );
                        
                        $first_day = hoursandmins($active_duration['start_duration']);
                        $first_day_duration = formatHoursAndMinutes($first_day);
                        $res[0]['duration'] = $first_day_duration == '' ? '0' : $first_day_duration;                        

                        $second_day = hoursandmins($active_duration['end_duration']);
                        $second_day_duration = formatHoursAndMinutes($second_day);                        
                        $result_data[$key]['duration'] = $second_day_duration;
                        
                    }

                    $res[0]['is_old_price'] = TRUE;                    
                    $result_data = array_merge($res, $result_data);
                }
                else if(!empty($res) && empty($adjusted_min['is_so_shift']) && $daytype != 'sleepover' && ($daytype == 'weekday' || $daytype == 'public_holiday')) {
                    /* Split the duration for min 2 units calculation if new price list amount is greater than old price then
                    * take the start date and find the actual active hours for price calculation
                    */
                    $active_duration = minutesDifferenceBetweenDate($start_date_time, $end_date_time, false);
                   
                    $end_time = (date('Y-m-d', strtotime($end_date)) . " ". "12:00 AM");
                    
                    $start_time = $start_date_time;
                    $sec_end_time = $end_date_time;                        
                    
                    $first_day_min = minutesDifferenceBetweenDate($start_time, $end_time);                        
                    $first_day = hoursandmins($first_day_min);
                    $first_day_duration = formatHoursAndMinutes($first_day);
                    $res[0]['duration'] = $first_day_duration;
                    $second_day_min = minutesDifferenceBetweenDate($end_time , $sec_end_time);
                    
                    if($active_duration >= 120) {
                        $second_day = hoursandmins($second_day_min);
                        $second_day_duration = formatHoursAndMinutes($second_day);                    
                        $result_data[$key]['duration'] = $second_day_duration;
                    } else {
                        //If total hours is less than 2h then we apply the additional duration for second day
                        $hrandmin = explode(" ", $duration);
                        $min = 0;
                        if(!empty($hrandmin)) {
                            $hr = str_replace("h","", $hrandmin[0]) * 60;
                            if(count($hrandmin) == 2) {
                                $min = str_replace("m","", $hrandmin[1]);
                            }
                        } else {
                            $hr = str_replace("h","", $duration);
                        }
                        
                        $addition_duration = ($hr + $min) - ($first_day_min + $second_day_min);
                        $sec_min = $second_day_min + $addition_duration;
                        $second_day = hoursandmins($sec_min);
                        $second_day_duration = formatHoursAndMinutes($second_day);
                        $result_data[$key]['duration'] = $second_day_duration;
                        
                    }                                     

                    $res[0]['is_old_price'] = TRUE;
                    $result_data = array_merge($res, $result_data);
                }
                      
            }            
        } 
        
        return $result_data;
    }

    public function formatBreakDurationwithDate($break_rows, $st_date, $ed_date, $day_count, $day_array, $unset = true) {
        
        $durationBreakTime = [];
        if($break_rows) {
            $ins_ref_id = null;
            $insleep_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "interrupted_sleepover", "archive" => 0]);
            if ($insleep_details)
                $ins_ref_id = $insleep_details->id;
                
            $durationBreakTime['duration'] = 0;
              # form array for start date & time
              foreach($break_rows as $row) {
                if ($row['break_type'] == $ins_ref_id) 
                    continue;
                    
                if(!empty($row['break_start_datetime']) && !empty($row['break_end_datetime'])) {

                    $start_date = date('Y-m-d', strtotime($row['break_start_datetime']));
                    $end_date = date('Y-m-d', strtotime($row['break_end_datetime']));
                    # finding date diff
                    $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);
                    $end_time = (date('Y-m-d',strtotime($end_date)). " 12 AM");
                    
                    if($get_day_count > 0) { 
                        $prev_start_val = (!empty($durationBreakTime[$start_date])) ? intVal($durationBreakTime[$start_date]) : 0;
                        //Split the duration for start day
                        $durationBreakTime[$start_date] =  $prev_start_val + minutesDifferenceBetweenDate($row['break_start_datetime'], $end_time, true);
                      
                        $prev_end_val = (!empty($durationBreakTime[$end_date])) ? intVal($durationBreakTime[$end_date]) : 0;

                        //Split the duration for end day
                        $durationBreakTime[$end_date] =  $prev_end_val + minutesDifferenceBetweenDate($end_time, $row['break_end_datetime'], true);
                       
                    } else {
                        $prev_start_val = (!empty($durationBreakTime[$start_date])) ? intVal($durationBreakTime[$start_date]) : 0;
                        $durationBreakTime[$start_date] = $prev_start_val + $this->hoursToMinutes($row['break_duration']);
                    }
                     
                } else {
                    if (isset($row['break_duration']) && !empty($row['break_duration'])) {
                        $durationBreakTime['duration'] += $this->hoursToMinutes($row['break_duration']);    
                    }                    
                }
              }             
        }
        
        return $this->reduce_druation_with_breaktime($day_array, $day_count, $durationBreakTime, $break_rows, $unset);            
      
    }
    /**
     * Split duration for the date
     * @param {array} $break_rows
     */
    public function getBreakDurationForDate($break_rows, $start_datetime, $end_datetime) {
        $duration = [];
        $durationBreakTime = [];

        # calculating total shift durations in minutes
        $total_mins = minutesDifferennceBetweenDate($start_datetime, $end_datetime);

        if($break_rows) {
            # form array for start date & time
            foreach($break_rows as $row) {
                if(isset($row['break_start_datetime']) == true && isset($row['break_end_datetime']) == true && empty($row['break_start_datetime']) == false && empty($row['break_end_datetime']) == false) {
                    $start_date = date('Y-m-d', strtotime($row['break_start_datetime']));
                    $end_date = date('Y-m-d', strtotime($row['break_end_datetime']));
                    # finding date diff
                    $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);
                    $end_time = (date('Y-m-d',strtotime($end_date)). " 00:00:00");
                    # get_day_count is greater than 0 then split the time for the day
                    if ($get_day_count > 0) {
                        $duration_2 = $duration_1 = $row;
                        # split duration 1
                        $duration_1['break_end_datetime'] = $end_time;
                        $duration_1['break_end_time'] = "12:00 AM";
                        $duration_1['break_duration'] = hoursDiffBwnDates($duration_1['break_start_datetime'], $duration_1['break_end_datetime'], true);
                        $duration_1['duration_int'] = $this->hoursToMinutes($duration_1['break_duration']);
                        $duration[$start_date][] = $duration_1;

                        # sum of duration int for split duration date
                        if (isset($durationBreakTime[$start_date]) && empty($durationBreakTime[$start_date]) == false) {
                            $durationBreakTime[$start_date] = intVal($durationBreakTime[$start_date]) + intVal($duration_1['duration_int']);
                        } else {
                            $durationBreakTime[$start_date] = intVal($duration_1['duration_int']);
                        }

                        # split duration 2
                        $duration_2['break_start_time'] = "12:00 AM";
                        $duration_2['break_start_datetime'] = $end_time;
                        $duration_2['break_duration'] = hoursDiffBwnDates($duration_2['break_start_datetime'], $duration_2['break_end_datetime'], true);
                        $duration_2['duration_int'] = $this->hoursToMinutes($duration_2['break_duration']);
                        $duration[$end_date][] = $duration_2;

                        # sum of duration int for date
                        if (isset($durationBreakTime[$start_date]) && empty($durationBreakTime[$start_date]) == false) {
                            $durationBreakTime[$start_date] = intVal($durationBreakTime[$start_date]) + intVal($duration_2['duration_int']);
                        } else {
                            $durationBreakTime[$start_date] = intVal($duration_2['duration_int']);
                        }

                    } else {
                        $duration[$start_date][] = $row;

                        # sum of duration int for date
                        if (isset($durationBreakTime[$start_date]) && empty($durationBreakTime[$start_date]) == false) {
                            $durationBreakTime[$start_date] = intVal($durationBreakTime[$start_date]) + intVal($row['duration_int']);
                        } else {
                            $durationBreakTime[$start_date] = intVal($row['duration_int']);
                        }
                        
                    } 
                }
            }
            
            # form array for duration int (without given start & end time)
            $start_date = date('Y-m-d', strtotime($start_datetime));
            $end_date = date('Y-m-d', strtotime($end_datetime));
            $availBreakTime = [];

            # finding date diff
            $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);

            # get_day_count is greater than 0 then split the time for available time for the day
            if ($get_day_count > 0) {
                $startdate_endtime = $enddate_starttime = (date('Y-m-d', strtotime($end_datetime))). "00:00:00";
                $duration_1_int = hoursDiffBwnDates($start_datetime, $startdate_endtime, true);
                $duration_1_min = $this->hoursToMinutes($duration_1_int);
                $availBreakTime[$start_date] = $duration_1_min;

                $duration_2_int = hoursDiffBwnDates($enddate_starttime, $end_datetime, true);
                $duration_2_min = $this->hoursToMinutes($duration_2_int);
                $availBreakTime[$end_date] = $duration_2_min;
            } else {
                $duration_int = hoursDiffBwnDates($start_datetime, $end_datetime, true);
                $duration_min = $this->hoursToMinutes($duration_int);
                $availBreakTime[$start_date] = $duration_min;
            }

            $split_breaktime = $updated_breaktime = $start_datetime;
            $split_breaktime_end = (date('Y-m-d', strtotime($end_datetime))). "00:00:00";
            foreach($break_rows as $row) {
                # form start and end time
                if(empty($row['break_start_datetime']) == true && empty($row['break_end_datetime']) == true && empty($row['duration_int']) == false) {
                    $duration_break = $row['duration_int'];

                    if ($get_day_count > 0) {
                        $start_date = date('Y-m-d', strtotime($split_breaktime));
                        $break_startdatetime = date('Y-m-d H:i:s', strtotime($updated_breaktime));

                        if (isset($availBreakTime[$start_date]) && !empty($availBreakTime[$start_date]) && isset($durationBreakTime[$start_date]) && !empty($durationBreakTime[$start_date])) {
                            $avail_break = intVal($availBreakTime[$start_date]) - intVal($durationBreakTime[$start_date]);
                        } else {
                            $avail_break = $duration_break;
                        }
                        $remain_break = $avail_break - $duration_break;

                        # split the remain_break if avail break less -1 value for date duration 1 & duration 2
                        if ($remain_break > -1) {
                            $date = date('Y-m-d', strtotime($start_datetime));
                            # add break duration
                            $break_startdatetime = date('Y-m-d H:i:s', strtotime($updated_breaktime));
                            $updated_breaktime = $break_enddatetime = date('Y-m-d H:i:s', strtotime($updated_breaktime." + ".$duration_break." minutes"));

                            $row['break_start_time'] = date('h:i A', strtotime($break_startdatetime));
                            $row['break_end_time'] = date('h:i A', strtotime($break_enddatetime));
                            $row['break_start_datetime'] = $break_startdatetime;
                            $row['break_end_datetime'] = $break_enddatetime;

                            $duration[$date][] = $row;

                            # sum of duration int for date
                            if (isset($durationBreakTime[$date]) && empty($durationBreakTime[$date]) == false) {
                                $durationBreakTime[$date] = intVal($durationBreakTime[$date]) + intVal($row['duration_int']);
                            } else {
                                $durationBreakTime[$date] = intVal($row['duration_int']);
                            }
                        } else {
                            # duration break 1
                            $duration_break_1 = $avail_break;

                            $date = date('Y-m-d', strtotime($split_breaktime));
                            # add break duration
                            $break_startdatetime = date('Y-m-d H:i:s', strtotime($split_breaktime));
                            $split_breaktime = $break_enddatetime = date('Y-m-d H:i:s', strtotime($split_breaktime." + ".$duration_break_1." minutes"));

                            $row['break_start_time'] = date('h:i A', strtotime($break_startdatetime));
                            $row['break_end_time'] = date('h:i A', strtotime($break_enddatetime));
                            $row['break_start_datetime'] = $break_startdatetime;
                            $row['break_end_datetime'] = $break_enddatetime;
                            $row['duration_int'] = $duration_break_1;
                            $row['break_duration'] = hoursDiffBwnDates($row['break_start_datetime'], $row['break_end_datetime'], true);
                            $duration[$date][] = $row;

                            # sum of duration int for date
                            if (isset($durationBreakTime[$date]) && empty($durationBreakTime[$date]) == false) {
                                $durationBreakTime[$date] = intVal($durationBreakTime[$date]) + intVal($row['duration_int']);
                            } else {
                                $durationBreakTime[$date] = intVal($row['duration_int']);
                            }
                            
                            # duration break 2
                            $duration_break_2 = $duration_break - $avail_break;

                            $date = date('Y-m-d', strtotime($split_breaktime_end));
                            # add break duration
                            $break_startdatetime = date('Y-m-d H:i:s', strtotime($split_breaktime_end));
                            $split_breaktime_end = $break_enddatetime = date('Y-m-d H:i:s', strtotime($split_breaktime_end." + ".$duration_break_2." minutes"));

                            $row['break_start_time'] = date('h:i A', strtotime($break_startdatetime));
                            $row['break_end_time'] = date('h:i A', strtotime($break_enddatetime));
                            $row['break_start_datetime'] = $break_startdatetime;
                            $row['break_end_datetime'] = $break_enddatetime;
                            $row['duration_int'] = $duration_break_2;
                            $row['break_duration'] = hoursDiffBwnDates($row['break_start_datetime'], $row['break_end_datetime'], true);
                            $duration[$date][] = $row;

                            # sum of duration int for date
                            if (isset($durationBreakTime[$date]) && empty($durationBreakTime[$date]) == false) {
                                $durationBreakTime[$date] = intVal($durationBreakTime[$date]) + intVal($row['duration_int']);
                            } else {
                                $durationBreakTime[$date] = intVal($row['duration_int']);
                            }
                        }

                    } else {
                        $date = date('Y-m-d', strtotime($start_datetime));
                        # add break duration
                        $break_startdatetime = date('Y-m-d H:i:s', strtotime($updated_breaktime));
                        $updated_breaktime = $break_enddatetime = date('Y-m-d H:i:s', strtotime($updated_breaktime." + ".$duration_break." minutes"));

                        $row['break_start_time'] = date('h:i A', strtotime($break_startdatetime));
                        $row['break_end_time'] = date('h:i A', strtotime($break_enddatetime));
                        $row['break_start_datetime'] = $break_startdatetime;
                        $row['break_end_datetime'] = $break_enddatetime;
                        $row['break_duration'] = hoursDiffBwnDates($row['break_start_datetime'], $row['break_end_datetime'], true);

                        $duration[$date][] = $row;

                        # sum of duration int for date
                        if (isset($durationBreakTime[$date]) && empty($durationBreakTime[$date]) == false) {
                            $durationBreakTime[$date] = intVal($durationBreakTime[$date]) + intVal($row['duration_int']);
                        } else {
                            $durationBreakTime[$date] = intVal($row['duration_int']);
                        }
                    }
                }
            }
        }
        return $duration;
    }

    /**
     * This functions helps to get the active duration before and after S/O with unpaid breaks
     * @param $break_rows {array} break details with start date and end date and unpaid duration
     * @param $st_date {string} shift start date
     * @param $ed_date {string} shift end date
     * @param $data {array} shift details
     * 
     * @return $active_duration {array} active duration for start and end shift
     */
    public function getActiveDurationBetweenDate($break_rows, $st_date, $ed_date, $data, $ismin2unit) {
        
        $active_duration = [];
        $durationBreakTime['duration'] = 0;
        $high_priority = '';
        if($break_rows) {
            $get_12am = (date('Y-m-d',strtotime($ed_date)). " 12 AM");

            //Get the total active duration without break for start and end day
            //This start_active_duration_with_brk value will be replaced if it has start and end date break time
            $start_active_duration_with_brk = $start_active_duration_with_out_brk = minutesDifferenceBetweenDate($st_date, $get_12am);            
            $end_active_duration_with_brk = $end_active_duration_with_out_brk = minutesDifferenceBetweenDate($get_12am, $ed_date);

            $break_rows = $this->formBreakStartEndDateTime($break_rows, $st_date, $ed_date);
              # form array for start date & time
              foreach($break_rows as $row) {
                if(!empty($row['break_start_datetime']) && !empty($row['break_end_datetime'])) {
                    $start_date = date('Y-m-d', strtotime($row['break_start_datetime']));
                    $end_date = date('Y-m-d', strtotime($row['break_end_datetime']));
                    # finding date diff
                    $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);
                    if($get_day_count > 0) {                        
                        $start_active_duration_with_brk = $start_active_duration_with_out_brk - minutesDifferenceBetweenDate($row['break_start_datetime'], $get_12am);
                        $end_active_duration_with_brk = $end_active_duration_with_out_brk - minutesDifferenceBetweenDate($get_12am, $row['break_end_datetime']);
                    }
                     
                } else {
                    if (isset($row['break_duration']) && !empty($row['break_duration'])) {
                        $durationBreakTime['duration'] += $this->hoursToMinutes($row['break_duration']);    
                    }                    
                }
              }
            //Reduce the unpaid break time based on the day priority
            if(!empty($durationBreakTime)) {
                
                $start_day = $this->get_the_day($st_date);
                $end_day = $this->get_the_day($ed_date);

                # check if it's a holiday start date and end date
                $start_is_holiday = $this->check_public_holiday($data, $st_date, NULL);
                $end_is_holiday = $this->check_public_holiday($data, $ed_date, NULL);
                
                $day = $start_is_holiday ? 'public_holiday' : $start_day;
                $day1 = $this->set_priority($day);
                
                $day = $end_is_holiday ? 'public_holiday' : $end_day;
                $day2 = $this->set_priority($day);

                //Reduce the value if already it has start and end time break value
                if($day1 <= $day2) {
                    $start_active_duration_with_brk = $start_active_duration_with_brk - $durationBreakTime['duration'];
                    $high_priority = 'day2';
                } else {
                    $end_active_duration_with_brk = $end_active_duration_with_brk - $durationBreakTime['duration'];
                    $high_priority = 'day1';
                }

                //Adjust the duration based on minimum 2 units calculation
                if($ismin2unit && $high_priority == 'day2') {
                    #Adding the adjustment hours on second day based on the priority
                    $end_active_duration_with_brk += 120 - ($start_active_duration_with_brk + $end_active_duration_with_brk);
                    
                } else if($ismin2unit && $high_priority == 'day1') {
                    #Adding the adjustment hours on start day based on the priority
                    $start_active_duration_with_brk += 120 - ($start_active_duration_with_brk + $end_active_duration_with_brk);
                }
            }

            $active_duration['start_duration'] = $start_active_duration_with_brk;
            $active_duration['end_duration'] = $end_active_duration_with_brk;            
        }
        return $active_duration;
      
    }

    /**
     * Format break time - start and end date if time provided
     * @param {array} $rows
     * @param {str} $st_date_time
     * @param {str} $ed_date_time
     */
    public function formBreakStartEndDateTime($rows, $st_date_time, $ed_date_time) {
        $shift_start_date = date('Y-m-d', strtotime($st_date_time));
        $shift_end_date = date('Y-m-d', strtotime($ed_date_time));
        foreach($rows as $ind => $row) {
            if(!empty($row['break_start_time']) && !empty($row['break_end_time'])) {
                $date_in_break_start = date('Y-m-d', strtotime($shift_start_date));
                $date_in_break_end = date('Y-m-d', strtotime($shift_end_date));

                # if sleepover shift then checking which date to use for break start and end time
                if($shift_start_date != $shift_end_date) {
                    if(substr_count($row['break_start_time'],"PM") > 0 && substr_count($row['break_end_time'],"AM") > 0) {
                        $date_in_break_start = $shift_start_date;
                        $date_in_break_end = $shift_end_date;
                    }
                    else {
                        $check_break_start_from_start = $shift_start_date." ".$row['break_start_time'];
                        $check_break_end_from_start = $shift_start_date." ".$row['break_end_time'];

                        $valid_break_start_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_start_from_start,true);
                        if($valid_break_start_from_start) {
                            $date_in_break_start = $shift_start_date;
                        }
                        else {
                            $date_in_break_start = $shift_end_date;
                        }

                        $valid_break_end_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_end_from_start);
                        if($valid_break_end_from_start) {
                            $date_in_break_end = $shift_start_date;
                        }
                        else {
                            $date_in_break_end = $shift_end_date;
                        }
                    }
                }
                $break_start_datetime = $date_in_break_start." ".$row['break_start_time'];
                $break_end_datetime = $date_in_break_end." ".$row['break_end_time'];

                $rows[$ind]['break_start_datetime'] = $break_start_datetime;
                $rows[$ind]['break_end_datetime'] = $break_end_datetime;

                $break_minutes = minutesDifferenceBetweenDate($break_start_datetime, $break_end_datetime);
                $rows[$ind]['duration_int'] = $break_minutes;
                $rows[$ind]['duration'] = get_hour_minutes_from_int($break_minutes);
            } else {
                list($hour, $minutes) = explode(":",$row['break_duration']);
                $hour = (int) $hour;
                $minutes = (int) $minutes;
                $break_minutes = ($minutes + ($hour * 60));
                $rows[$ind]['duration_int'] = $break_minutes;
                $rows[$ind]['duration'] = $row['break_duration'];
            }
            $rows[$ind]['break_option'] = '';
        }
        return $rows;
    }

    //Set the priority based on the day
    public function set_priority($day) {
        switch($day) {
            case 'public_holiday':
                $priority = 4;
                break;
            case 'sunday':
                $priority = 3;
                break;
            case 'saturday':
                $priority = 2;
                break;
            case 'weekday':
                $priority = 1;                
                break;
            default:
                break;
        }
        return $priority;
    }
    /**
     * 
     * To checks the the parent line item added into Service agreement line items
     * if yes then return true or false
     * 
     * @param $item {array} line item data
     * @param $sa_id {int} service agreement id
     * 
     * @return bool true/false
     */
    function check_sa_parent_line_item_availablity($item, $sa_id) {
        
        $this->db->select('id');
        $this->db->from(TBL_PREFIX . 'finance_line_item');
        $this->db->where(['line_item_number' => $item['category_ref']]);        
        $this->db->limit(1);
        $where_clause = $this->db->get_compiled_select();


        $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');

        $this->db->select(["id"]);

        $this->db->where(['sai.service_agreement_id' => $sa_id, 'sai.archive' => 0]);
        $this->db->where("line_item_id = ($where_clause)", NULL, FALSE);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        if($query->num_rows() > 0) {
            return true;
        }
    }
     /**
     * validating shift break timings
     */
    public function valid_shift_break_form_array($break_rows, $data) {
        $errors = null;
        $total_break_minutes = 0;

        $shift_start_datetime = $data['start_datetime'];
        $shift_end_datetime = $data['end_datetime'];
        $shift_start_date = $data['start_date'];
        $shift_end_date = $data['end_date'];

        $rows = json_decode(json_encode($break_rows), true);

        foreach($rows as $ind => $row) {
            $row = (array) $row;
            $rowno = $ind+1;
            $break_minutes = null;
            if(!empty($row['break_start_time']) && !empty($row['break_end_time'])) {
                $break_start_time = "2020-01-01 ".$row['break_start_time'];
                $valid_break_start_time = date('Y-m-d h:i A', strtotime($break_start_time)) === $break_start_time;
                $break_end_time = "2020-01-01 ".$row['break_end_time'];
                $valid_break_end_time = date('Y-m-d h:i A', strtotime($break_end_time)) === $break_end_time;

                if(!$valid_break_start_time) {
                    $errors[] = "Please provide break start time in correct format row-{$rowno}";
                }
                else if(!$valid_break_end_time) {
                    $errors[] = "Please provide break start time in correct format row-{$rowno}";
                }
                else {
                    $date_in_break_start = $shift_start_date;
                    $date_in_break_end = $shift_end_date;

                    # if sleepover shift then checking which date to use for break start and end time
                    if($shift_start_date != $shift_end_date) {
                        if(substr_count($row['break_start_time'],"PM") > 0 && substr_count($row['break_end_time'],"AM") > 0) {
                            $date_in_break_start = $shift_start_date;
                            $date_in_break_end = $shift_end_date;
                        }
                        else {
                            $check_break_start_from_start = $shift_start_date." ".$row['break_start_time'];
                            $check_break_end_from_start = $shift_start_date." ".$row['break_end_time'];

                            $valid_break_start_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_start_from_start,true);
                            if($valid_break_start_from_start) {
                                $date_in_break_start = $shift_start_date;
                            }
                            else {
                                $date_in_break_start = $shift_end_date;
                            }

                            $valid_break_end_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_end_from_start);
                            if($valid_break_end_from_start) {
                                $date_in_break_end = $shift_start_date;
                            }
                            else {
                                $date_in_break_end = $shift_end_date;
                            }
                        }
                    }

                    $break_start_datetime = $date_in_break_start." ".$row['break_start_time'];
                    $break_end_datetime = $date_in_break_end." ".$row['break_end_time'];

                    $rows[$ind]['break_start_datetime'] = $break_start_datetime;
                    $rows[$ind]['break_end_datetime'] = $break_end_datetime;

                    $break_minutes = minutesDifferenceBetweenDate($break_start_datetime, $break_end_datetime);
                    $total_break_minutes += $break_minutes;
                    $rows[$ind]['duration_int'] = $break_minutes;
                    $rows[$ind]['duration'] = get_hour_minutes_from_int($break_minutes);
                }
            }
        }
        return $rows;
    }

    /**
     * Generate Error list  & export as excel file
     */
    public function generateErrNdisFile($csv_data, $shift_id) {
        $this->load->library("Excel");
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Retrieve the current active worksheet 
        $sheet = $spreadsheet->getActiveSheet(); 
        $sheet->setCellValueByColumnAndRow(1, 1, 'Shift ID');
        $sheet->setCellValueByColumnAndRow(2, 1, 'Shift Date');
        $sheet->setCellValueByColumnAndRow(3, 1, 'Shift Day');
        $sheet->setCellValueByColumnAndRow(4, 1, 'Signed Contract Error');
        $sheet->setCellValueByColumnAndRow(5, 1, 'Service Booking Error');
        $sheet->setCellValueByColumnAndRow(6, 1, 'Missing Line Items Error');

        $var_row = 2;
        foreach ($csv_data as $data)
        {
            $data = (object) $data;
            $date_format = date('d/m/Y', strtotime($data->date));
            $day_format = date('l', strtotime($data->date));
            
            $error_1 = '';
            $error_2 = '';
            $error_3 = '';

            if (isset($data->error_1) && $data->error_1 == true) {
                $error_1 = 'No Signed Contract exist';
            }

            if (isset($data->error_2) && $data->error_2 == true) {
                $error_2 = 'No Service Booking / Service Booking Not signed';
            }

            if (isset($data->error_3) && $data->error_3 == true) {
                $error_3 = 'Missing support items.';
            }

            # replace error msg if error 1 occur
            if (isset($data->error_1) && $data->error_1 == true) {
                $error_2 = $error_3 = 'NA - Due to non existence of Signed contract';
            }

            # data row form
            $sheet->SetCellValue('A'.$var_row, $data->shift_no);
            $sheet->SetCellValue('B'.$var_row, $date_format);
            $sheet->SetCellValue('C'.$var_row, $day_format);
            $sheet->SetCellValue('D'.$var_row, $error_1);
            $sheet->SetCellValue('E'.$var_row, $error_2);
            $sheet->SetCellValue('F'.$var_row, $error_3);

            $var_row++;
        }

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $filename = 'ShiftNDISError_'.$shift_id.'.xlsx';
        // Write an .xlsx file  
        $writer = new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet); 
        $writer->save(ARCHIEVE_DIR.'/'.$filename);

        return $filename;
    }

    /**
     * Get Sleepover line item
     * @param {array} $day_array
     * @param {array} $break_rows
     * @param {int} $get_day_count
     * @param {boolean} $public_holiday
     */
    public function getSleepOverLineItem($day_array, $break_rows, $get_day_count, $public_holiday, $data, $shift_start_date, $shift_end_date) {
        $sleepOver = [];

        # check day_of_time
        $dataValue = array_column($day_array, 'day_of_time');
        $dayOfTime = ''; 
        if (in_array('overnight', $dataValue)) {
            $dayOfTime = 'overnight';
        }

        # finding the sleepover break reference id for later use
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        $so_ref_id = '';
        if ($so_details) {
            $so_ref_id = $so_details->id;
        }            

        $soType = false; 
        $breakRows = array_column($break_rows, 'break_type');
        if (in_array($so_ref_id, $breakRows)) {
            $soType = true;
        }

        # check day not equals public holiday & shift time equal to overnight & break type must be sleepover
        if (!$public_holiday && $soType ) {
            $duration = '1';
            $soKey = 'sleepover';
            
            $support_type = $data['supportType'] ?? 0;
            
            $data['support_key_name'] = $this->get_finance_support_type_name($support_type);

            $saSleepOverLineItem = $this->fetch_ndis_line_items('sleepover', $data['service_agreement_id'], $data['supportType'], $duration, $soKey, false);

            $sleepOverLineItem = $this->fetch_ndis_line_items_all('sleepover', $data['service_agreement_id'], $data['supportType'], $duration, $soKey, false, $shift_start_date, $shift_end_date,'',$data);

            $sleepOver['sa_line_item'] = $saSleepOverLineItem;
            $sleepOver['sa_line_item_all'] = $sleepOverLineItem;
        }
        
        return $sleepOver;
    }

    /**
     * Get line item time of day
     * @param {int} $lineItemId
     */
    function get_time_of_day_of_line_item_by_id($lineItemId) {
        $this->db->select(['f_time.id', "f_time.name", "f_time.key_name"]);
        $this->db->from('tbl_finance_line_item_applied_time as f_t_a');
        $this->db->join('tbl_finance_time_of_the_day as f_time', 'f_t_a.finance_timeId = f_time.id', 'INNER');
        $this->db->where('f_t_a.archive', 0);
        $this->db->where('f_t_a.line_itemIds', $lineItemId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result();
    }

    /**
     * Get line item time of day
     * @param {int} $lineItemId
     */
    function get_time_of_day_by_line_item_id($lineItemId) {
        $this->db->select(['id','daytime', 'evening', 'overnight', 'sleepover']);
        $this->db->from(TBL_PREFIX . 'finance_line_item');
        $this->db->where('archive', 0);
        $this->db->where('id', $lineItemId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result();
    }

    /**
     * checking the sleepover shift with active hours before/after the sleepover
     * there has to be active hours period
    */
    public function sleepoverUnitCalculation($break_rows, $start_datetime, $end_datetime, $get_day_count, $day_array) {
        $ah_before_start_datetime = $ah_before_end_datetime = null;
        $ah_after_start_datetime = $ah_after_end_datetime = null;
        $active_hours_after = $active_hours_before = null;
        
        # finding the sleepover break reference id for later use
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;
        
        $before_after_missing = $before_missing = false;
        $adjust_min = $after_adjust_min = $before_adjust_min = $sl_in = $day1_adjust_min = $day2_adjust_min = 0;


        $start_date = date('Y-m-d', strtotime($start_datetime));
        $end_date = date('Y-m-d', strtotime($end_datetime));
        
        foreach($break_rows as $row) {
            $adjust_min = $after_adjust_min = $before_adjust_min = 0;
            if($row['break_type'] == $so_ref_id) {
                if(!isset($row['break_start_datetime']) || !isset($row['break_end_datetime']))
                continue;

                $break_start_datetime = $row['break_start_datetime'];
                $break_end_datetime = $row['break_end_datetime'];
                $active_hours_before = minutesDifferenceBetweenDate($start_datetime, $break_start_datetime);
                $ah_before_start_datetime = $start_datetime;
                $ah_before_end_datetime = $break_start_datetime;
                $active_hours_after = minutesDifferenceBetweenDate($break_end_datetime, $end_datetime);
                $ah_after_start_datetime = $break_end_datetime;
                $ah_after_end_datetime = $end_datetime;

                # if day count less than 1 // no spanning on next day (single day)
                if ($get_day_count < 1) {
                    # if active hour before seepover break start & after less than 4 hr (240 min)  then need to adjust 4 hr
                    if($active_hours_before < ACTIVE_SO_DURATION && $active_hours_after < ACTIVE_SO_DURATION) {
                        $adjust_min += ACTIVE_SO_DURATION - $active_hours_after;
                        $before_after_missing = true;
                        $day2_adjust_min = 4;
                    }

                    # if active hour before seepover break start less than 1 hr (60 min) then need to adjust 1 hr
                    if($active_hours_before < ACTIVE_SO_BEFORE_DURATION) {
                        $adjust_min += ACTIVE_SO_BEFORE_DURATION - $active_hours_before;
                        $before_missing = true;
                        $day1_adjust_min = 1;                       
                    }

                    # search start date and update the values
                    $key = array_search($start_date, array_column($day_array, 'start_date'));
                    if ($key !== false && $sl_in === 0) {
                        $duration = $day_array[$key]['duration_time_minute'];
                        $duration += $adjust_min;
                        $day_array[$key]['first_day_min'] = $day_array[$key]['duration_time_minute'];
                        $duration_hrmin = hoursandmins($duration);
                        $duration_format = formatHoursAndMinutes($duration_hrmin);
                        $day_array[$key]['duration_time_minute'] = $duration;
                        $day_array[$key]['duration_time'] = $duration_format;
                        $day_array[$key]['before_adjustment'] = $day1_adjust_min;
                        $day_array[$key]['after_adjustment'] = $day2_adjust_min;
                        
                    }
                } else {
                    # search start & end date and get index
                    $sd_day_week = '';
                    $sd_key = array_search($start_date, array_column($day_array, 'start_date'));
                    if ($sd_key !== false)  {
                        $sd_day_week = $day_array[$sd_key]['day_of_week'];
                    }
                    
                    $ed_key = array_search($end_date, array_column($day_array, 'start_date'));
                    $ed_day_week = '';
                    if ($ed_key !== false)  {
                        $ed_day_week = $day_array[$ed_key]['day_of_week'];
                    }

                    if ($sd_day_week == '' && $ed_day_week =='') {
                        continue;
                    }
                    
                    #get value by presitance
                    $sdPresitance = $this->presitence_day_rule[$sd_day_week] ?? NULL;
                    $edPresitance = $this->presitence_day_rule[$ed_day_week] ?? NULL;

                    
                    # lower to higher so need to adjust duration before 1 hr if not and adjust after 4 hr if not
                    if ($sdPresitance < $edPresitance) {
                        # if active hour before seepover break start & after less than 4 hr (240 min) then need to adjust 4 hr
                        if($active_hours_before < ACTIVE_SO_DURATION && $active_hours_after < ACTIVE_SO_DURATION) {
                            $after_adjust_min += ACTIVE_SO_DURATION - $active_hours_after;
                            $before_after_missing = true;
                            $day2_adjust_min = 4;
                        }

                        # if active hour before sleepover break start less than 1 hr (60 min) then need to adjust 1 hr
                        if($active_hours_before < ACTIVE_SO_BEFORE_DURATION) {
                            $before_adjust_min += ACTIVE_SO_BEFORE_DURATION - $active_hours_before;
                            $before_missing = true;
                            $day1_adjust_min = 1;
                        }

                    } 
                    # higher to lower so need to adjust duration before 4 hr if not
                    else {
                        # if active hour before seepover break start & after less than 4 hr (240 min) then need to adjust 4 hr
                        if($active_hours_before < ACTIVE_SO_DURATION && $active_hours_after < ACTIVE_SO_DURATION) {
                            $before_adjust_min += ACTIVE_SO_DURATION - $active_hours_before;
                            $before_after_missing = true;
                            $day1_adjust_min = 4;                            
                        } else if($active_hours_before < ACTIVE_SO_BEFORE_DURATION && $active_hours_after >= ACTIVE_SO_DURATION) {
                            $before_adjust_min += ACTIVE_SO_BEFORE_DURATION - $active_hours_before;
                            $before_missing = true;
                            $day1_adjust_min = 1;                            
                        }

                        # becasuse higher to lower presitance then no need to adjust after s/o break hr
                        if($active_hours_after < ACTIVE_SO_BEFORE_DURATION) {
                            $after_adjust_min += 0;
                        }
                    }

                    # search start date and update the values
                    if ($sd_key !== false && $sl_in === 0) {
                        $duration = $day_array[$sd_key]['duration_time_minute'];
                        $duration += $before_adjust_min;
                        $duration_hrmin = hoursandmins($duration);
                        $duration_format = formatHoursAndMinutes($duration_hrmin);
                        $day_array[$sd_key]['duration_time_minute'] = $duration;
                        $day_array[$sd_key]['duration_time'] = $duration_format;
                        $day_array[$sd_key]['before_adjustment'] = $day1_adjust_min;
                        $day_array[$sd_key]['after_adjustment'] = 0;
                    }

                    # search end date and update the values
                    if ($ed_key !== false && $sl_in === 0) {
                        $duration = $day_array[$ed_key]['duration_time_minute'];
                        $duration += $after_adjust_min;
                        $duration_hrmin = hoursandmins($duration);
                        $duration_format = formatHoursAndMinutes($duration_hrmin);
                        $day_array[$ed_key]['duration_time_minute'] = $duration;
                        $day_array[$ed_key]['duration_time'] = $duration_format;
                        $day_array[$sd_key]['before_adjustment'] = 0;
                        $day_array[$sd_key]['after_adjustment'] = $day2_adjust_min;
                    }
                }
                $sl_in ++;
            }            
        }

        return $day_array;
    }

    /**
     * 
     * To checks the Sleepover line parent line item added into Service agreement line items
     * if yes then return true or false
     *
     * @param $sa_id {int} service agreement id
     * 
     * @return bool true/false
     */
    public function check_so_line_item_availablity_for_comm_access($sa_id) {
        if(!$sa_id) {
            return FALSE;
        }
        $this->db->select('id');
        $this->db->from(TBL_PREFIX . 'finance_line_item');        
        
        $this->db->where("support_type = ". SUPPORT_TYPE_SELF_CARE . " AND sleepover = 1
        OR (line_item_number = 0" . SUPPORT_TYPE_SELF_CARE . ")");        
        $where_clause = $this->db->get_compiled_select();
        
        $this->db->select(["id"]);
        $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');
        $this->db->where(['sai.service_agreement_id' => $sa_id, 'sai.archive' => 0]);
        $this->db->where("line_item_id IN ($where_clause)", NULL, FALSE);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        if($query->num_rows() > 0) {
            return true;
        }
    }

    /**
     * Get the duration date
     */
    public function get_support_type_ndis_duration($data) {
        $data['start_date'] = $start_date = DATE('Y-m-d', strtotime($data['start_date']));
        $data['end_date'] = $end_date = DATE('Y-m-d', strtotime($data['end_date']));
        $get_day_count = dayDifferenceBetweenDate($data['start_date'], $data['end_date']);
        $start_day = $this->get_the_day($start_date);
        $end_day = $this->get_the_day($end_date);
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        $location = '';
        $start_is_holiday = [];
        $end_is_holiday = [];
        $day_array = [];
        $day_in = 0;
        $category = $data['section'] === 'scheduled' ? 1 : 2;

        $data['scheduled_start_datetime'] = DATE('Y-m-d', strtotime($data['start_date'])) . " " . $data['start_time'];
        $data['scheduled_end_datetime'] = DATE('Y-m-d', strtotime($data['end_date'])) . " " . $data['end_time'];

        $duration = hoursDiffBwnDates($data['scheduled_start_datetime'], $data['scheduled_end_datetime'], false);

        if (isset($data) && isset($data['full_account_address'])) {
            $data['full_account_address'] = (array) $data['full_account_address'];
        }

        # if two is same as weekday and no holiday mean no need to splitup
        if ($get_day_count > 0) {
            # check if it's holiday
            $start_is_holiday = $this->check_public_holiday($data, $start_date, $location);
            $end_is_holiday = $this->check_public_holiday($data, $end_date, $location);

            if (($start_day == $end_day && ((empty($start_is_holiday) && empty($end_is_holiday))) || (!empty($start_is_holiday) && !empty($end_is_holiday)))) {
                $get_day_count = 0; 
            }
        }

         # form day array with shift date is one day or two 
         if ($get_day_count < 1) {
            $duration_format = $this->formatHoursToMinutes($duration);
            $duration_min = $this->hoursToMinutes($duration_format);

            $day_array[$day_in] = $this->form_day_array($data, $start_date, $end_date, $start_time, $end_time, $duration, $get_day_count, $location, $duration_min);
            $day_in++;
        } else {
            $two_days_spending_hour = $this->split_more_than_oneday_duration($data, $duration);
            $start_day_duration = $two_days_spending_hour['first_day_hour'];
            $end_day_duration = $two_days_spending_hour['second_day_hour'];

            $first_day_min = $two_days_spending_hour['first_day_min'];
            $second_day_min = $two_days_spending_hour['second_day_min'];
           
            $day_array[$day_in] = $this->form_day_array($data, $start_date, $start_date, $start_time, $end_time, $start_day_duration, 0, $location, $first_day_min);
            $day_in++;

            $day_array[$day_in] = $this->form_day_array($data, $end_date, $end_date, $start_time, $end_time, $end_day_duration, 0, $location, $second_day_min);
            $day_in++;
        }

        $break_rows = $data['rows'] ?? '';
        $br_data = [];
        $start_datetime_raw = $start_date.' '.$start_time;
        $end_datetime_raw = $end_date.' '.$end_time;
        $br_data['start_datetime'] = date('Y-m-d H:i:s', strtotime($start_datetime_raw));
        $br_data['end_datetime'] = date('Y-m-d H:i:s', strtotime($end_datetime_raw));        
        $br_data['start_date'] = $start_date;
        $br_data['end_date'] = $end_date;

        # check if it's have break - unpaid/sleepover
        $breakDurationCount = 0;
        if(!empty($break_rows)) {            
            $break_rows = $this->valid_shift_break_form_array($break_rows, $br_data);
            list($day_array, $breakDurationCount) = $this->formatBreakDurationwithDateForMixed($break_rows, $start_date, $end_date, $get_day_count, $day_array);
        }
        
        $duration = [];
        if ($get_day_count > 0) {
            $du_in = 0;
            # Start Day
            if (!empty($start_is_holiday)) {
                $label = 'Public Holiday';
                $day = 'public_holiday';
            } else {
                $label = date('l', strtotime($start_date));
                $day = $start_day;            
            }

            # Get start date duration time
            $search = array_column($day_array, 'start_date');
            $key = array_search($start_date, $search);
            $st_duration_min = 0; 
            if ($key > -1) {
                $st_duration_min = $day_array[$key]['duration_time_minute'];
                $duration_format = $day_array[$key]['duration_time'];
            }
            
            $duration[$du_in]['day'] = $day;
            $duration[$du_in]['date'] = $start_date;
            $duration[$du_in]['day_label'] = $label;
            $duration[$du_in]['label'] = $label.' Duration';
            $duration[$du_in]['error'] = false;
            $duration[$du_in]['error_txt'] = '';
            $duration[$du_in]['required'] = true;
            $duration[$du_in]['duration_min'] = $st_duration_min;
            $duration[$du_in]['duration_format'] = $duration_format;
            $duration[$du_in]['day_count'] = $get_day_count;
            $duration[$du_in]['duration_break_count'] = $breakDurationCount;
            $dur_time = hoursandmins($st_duration_min);
            $duration[$du_in]['duration_time'] = formatHoursAndMinutes($dur_time, true); 
            $duration[$du_in]['duration'] = array(
                [ 'id' => '', 'category' => $category, 'support_type' => 1, 'duration' => '', 'order' => 1, 'error' => false, 'errorTxt'=> '', 'required' => false, 'day' => $start_day, 'date' => $start_date ],
                [ 'id' => '', 'category' => $category, 'support_type' => 2, 'duration' => '', 'order' => 2, 'error' => false, 'errorTxt'=> '', 'required' => false, 'day' => $start_day, 'date' => $start_date ],
            );
            $du_in++;

            # End date
            if (!empty($end_is_holiday)) {
                $label = 'Public Holiday';
                $day = 'public_holiday';
            } else {
                $label = date('l', strtotime($end_date));
                $day = $end_day;            
            }

            # Get end date duration time
            $search = array_column($day_array, 'end_date');
            $key = array_search($end_date, $search);
            $ed_duration_min = 0; 
            if ($key > -1) {
                $ed_duration_min = $day_array[$key]['duration_time_minute'];
                $duration_format = $day_array[$key]['duration_time'];
            }

            $duration[$du_in]['day'] = $day;
            $duration[$du_in]['date'] = $end_date;
            $duration[$du_in]['day_label'] = $label;
            $duration[$du_in]['label'] = $label.' Duration';
            $duration[$du_in]['error'] = false;
            $duration[$du_in]['errorTxt'] = '';
            $duration[$du_in]['required'] = true;
            $duration[$du_in]['duration_min'] = $ed_duration_min;
            $duration[$du_in]['duration_format'] = $duration_format;
            $duration[$du_in]['day_count'] = $get_day_count;
            $duration[$du_in]['duration_break_count'] = $breakDurationCount;
            $dur_time = hoursandmins($ed_duration_min);
            $duration[$du_in]['duration_time'] = formatHoursAndMinutes($dur_time, true); 
            $duration[$du_in]['duration'] = array(
                [ 'id' => '', 'category' => $category, 'support_type' => 1, 'duration' => '', 'order' => 1, 'error' => false, 'errorTxt'=> '', 'required' => false, 'day' => $day, 'date' => $end_date ],
                [ 'id' => '', 'category' => $category, 'support_type' => 2, 'duration' => '', 'order' => 2, 'error' => false, 'errorTxt'=> '', 'required' => false, 'day' => $day, 'date' => $end_date ],
            );
        } else {

            if (!empty($start_is_holiday) && !empty($end_is_holiday)) {
                $label = 'Public Holiday';
                $day = 'public_holiday';
            } else {
                $label = date('l', strtotime($start_date));
                $day = $start_day;            
            }

            # Get date duration time
            $search = array_column($day_array, 'start_date');
            $key = array_search($start_date, $search);
            $duration_min = 0; 
            if ($key > -1) {
                $duration_min = $day_array[$key]['duration_time_minute'];
                $duration_format = $day_array[$key]['duration_time'];
            }

            $du_in = 0;
            $duration[$du_in]['day'] = $day;
            $duration[$du_in]['date'] = $start_date;
            $duration[$du_in]['day_label'] = $label;
            $duration[$du_in]['label'] = $label. ' Duration';
            $duration[$du_in]['error'] = false;
            $duration[$du_in]['errorTxt'] = '';
            $duration[$du_in]['required'] = false;
            $duration[$du_in]['duration_min'] = $duration_min;
            $duration[$du_in]['duration_format'] = $duration_format;
            $duration[$du_in]['day_count'] = $get_day_count;
            $duration[$du_in]['duration_break_count'] = $breakDurationCount;
            $dur_time = hoursandmins($duration_min);
            $duration[$du_in]['duration_time'] = formatHoursAndMinutes($dur_time, true);            
            $duration[$du_in]['duration'] = array(
                [ 'id' => '', 'category' => $category, 'support_type' => 1, 'duration' => '', 'order' => 1, 'error' => false, 'errorTxt'=> '', 'required' => true, 'day' => $day, 'date' => $start_date ],
                [ 'id' => '', 'category' => $category, 'support_type' => 2, 'duration' => '', 'order' => 2, 'error' => false, 'errorTxt'=> '', 'required' => true, 'day' => $day, 'date' => $start_date ],
            );
        }
        
        # if day count is same then replace duration
        $support_type_duration = $data['support_type_duration'] ?? [];
        if (count($support_type_duration) == count($duration)) {
            foreach($support_type_duration as $key => $stDuration) {
                $stDuration =(array) $stDuration;
                $cDuration =(array) $stDuration['duration'];
                foreach($cDuration as $s_key => $sDuration) {
                    $sDuration = (object) $sDuration;
                    if (isset($duration) && isset($duration[$key]) && isset($duration[$key]['duration'])) {
                        $clDurationNew = (array) $duration[$key]['duration'];
                        $clDurationNew[$s_key]['duration'] = $sDuration->duration;
                        $duration[$key]['duration'] = $clDurationNew;
                    }
                }
                
            }
        }

        return $duration;
    }

    /**
     * Format duration for fetching line items
     * @param $support_type_duration {array} list duration which is given by user
     * @param $day_array {array} array of data based on start time and end time
     * 
     * @return $day_array {array} day array with updated duration and support type
     */
    public function format_duration_for_mixed_item($support_type_duration, $day_array) {
        
        foreach ($support_type_duration as $key_st => $st_dur) {
            $st_dur = (object) $st_dur;
            # minus the split up duration with day
            $duration = $st_dur->duration ?? '00:00';
            # Skip the duration if it doesn't has a duration
            if(empty($duration) || !isset($day_array[$key_st])) {
                continue;
            }
            $duration_min = $this->hoursToMinutes($duration);
            
            #Single day means just replace with new mixed duration split up
            $day_array[$key_st]['duration_time_minute'] = $duration_min;
            $hoursAndMins= hoursandmins($day_array[$key_st]['duration_time_minute']);
            $day_array[$key_st]['duration_time'] = formatHoursAndMinutes($hoursAndMins);
            $day_array[$key_st]['support_type'] = $st_dur->support_type;

            # Get support type id
            if ($st_dur->support_type == 1) {
               $support_type = 'self_care'; 
            } else if ($st_dur->support_type == 2) {
                $support_type = 'comm_access'; 
            } else {
                $support_type = ''; 
            }                
            $supportType = $this->basic_model->get_row('finance_support_type', ['id'], ['key_name' => $support_type]);
            if (isset($supportType) && !empty($supportType) && !empty($supportType->id)) {
                $day_array[$key_st]['support_type'] = $supportType->id;
                
                if( $supportType->id == SUPPORT_TYPE_SELF_COMM && !empty($day_array[$key_st]['day_of_time']) &&
                    $day_array[$key_st]['day_of_time'] == 'overnight') {
                    $day_array[$key_st]['day_of_time'] = 'evening';
                }
            }                
        }
        
        return $day_array;
    }


    /**
     * Calculate the break hr with out duration break
     */
    public function formatBreakDurationwithDateForMixed($break_rows, $st_date, $ed_date, $day_count, $day_array) {

        $durationBreakTime = [];
        if($break_rows) {
            $ins_ref_id = null;
            $insleep_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "interrupted_sleepover", "archive" => 0]);
            if ($insleep_details)
                $ins_ref_id = $insleep_details->id;
                
            $durationBreakTime['duration'] = 0;
            $durationBreakCount = 0;
            # form array for start date & time
              foreach($break_rows as $row) {
                if ($row['break_type'] == $ins_ref_id) 
                    continue;
                    
                if(!empty($row['break_start_datetime']) && !empty($row['break_end_datetime'])) {

                    $start_date = date('Y-m-d', strtotime($row['break_start_datetime']));
                    $end_date = date('Y-m-d', strtotime($row['break_end_datetime']));
                    # finding date diff
                    $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);
                    $end_time = (date('Y-m-d',strtotime($end_date)). " 12 AM");
                    
                    if($get_day_count > 0) { 
                        $prev_start_val = (!empty($durationBreakTime[$start_date])) ? intVal($durationBreakTime[$start_date]) : 0;
                        //Split the duration for start day
                        $durationBreakTime[$start_date] =  $prev_start_val + minutesDifferenceBetweenDate($row['break_start_datetime'], $end_time, true);
                      
                        $prev_end_val = (!empty($durationBreakTime[$end_date])) ? intVal($durationBreakTime[$end_date]) : 0;

                        //Split the duration for end day
                        $durationBreakTime[$end_date] =  $prev_end_val + minutesDifferenceBetweenDate($end_time, $row['break_end_datetime'], true);
                       
                    } else {
                        $prev_start_val = (!empty($durationBreakTime[$start_date])) ? intVal($durationBreakTime[$start_date]) : 0;
                        $durationBreakTime[$start_date] = $prev_start_val + $this->hoursToMinutes($row['break_duration']);
                    }
                     
                } else {
                    if (isset($row['break_duration']) && !empty($row['break_duration'])) {
                        $durationBreakCount++;
                        $durationBreakTime['duration'] += $this->hoursToMinutes($row['break_duration']);    
                    }                    
                }
            }             
        }

        $day_array = $this->reduce_druation_with_breaktime($day_array, $day_count, $durationBreakTime, TRUE); 

        return [ $day_array, $durationBreakCount ];
    }

    /** Get day priority */
    function reduce_druation_with_breaktime_for_mixed($day_array, $day_count, $durationBreakTime, $durationBreakCount) {       
        if(!empty($day_array)) { 
            foreach($day_array as $key => $day) {               
                
                //Deduct if start and endtime mentioned value for start date
                if(!empty($durationBreakTime[$day['start_date']])) {
                    $duration = $day_array[$key]['duration_time_minute'] - $durationBreakTime[$day['start_date']];                    
                    $duration_raw = hoursandmins($duration);
                    $day_array[$key]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[$key]['duration_time_minute'] = $duration;
                    
                }

                //Deduct if end and endtime mentioned value for end date
                if($day['start_date'] != $day['end_date'] && isset($durationBreakTime[$day['end_date']]) && !empty($durationBreakTime[$day['end_date']])) {
                    $duration = $day_array[$key]['duration_time_minute'] - $durationBreakTime[$day['end_date']];                    
                    $duration_raw = hoursandmins($duration);
                    $day_array[$key]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[$key]['duration_time_minute'] = $duration;
                }

                //set the priority for more than one day
                if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count > 0) {
                    switch($day['day_of_week']) {
                        case 'public_holiday':
                            $priority = 4;
                            break;
                        case 'sunday':
                            $priority = 3;
                            break;
                        case 'saturday':
                            $priority = 2;
                            break;
                        case 'weekday':
                            $priority = 1;                
                            break;
                        default:
                            break;
                    }
                    $day_array[$key]['priority'] = $priority;
                }
            }
            
            # reduce the duration if day count 0
            if ($day_count < 1) {
                //Adjust time duration based on the lowest value if more than two days 
                if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count > 0) {
                //Check if start day priority is lesser than next day
                if($day_array[0]['priority'] <= $day_array[1]['priority']) {
                    $duration = $day_array[0]['duration_time_minute'] - $durationBreakTime['duration'];
                    
                    if($duration > 0) {
                        $duration_raw = hoursandmins($duration);
                        $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[0]['duration_time_minute'] = $duration;
                    } else {
                        //Remove start day which is in negative or zero value
                        unset($day_array[0]);
                        //Add negative or zero value for adjust the next day duration
                        $duration = $day_array[1]['duration_time_minute'] + $duration;
                        $duration_raw = hoursandmins($duration);
                        $day_array[1]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[1]['duration_time_minute'] = $duration;
                    }
                }  //Check if next day priority is lesser than start day          
                else if($day_array[1]['priority'] <= $day_array[0]['priority']) {
                    $duration = $day_array[1]['duration_time_minute'] - $durationBreakTime['duration'];
                    
                    if($duration > 1) {
                        $duration_raw = hoursandmins($duration);
                        $day_array[1]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[1]['duration_time_minute'] = $duration;
                    } else {
                        //Remove start day which is in negative or zero value
                        unset($day_array[1]);
                        //Add negative or zero value for adjust the next day duration
                        $duration = $day_array[0]['duration_time_minute'] + $duration;
                        $duration_raw = hoursandmins($duration);
                        $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                        $day_array[0]['duration_time_minute'] = $duration;
                    }
                }
                
                } // Single day with only durations
                else if(!empty($durationBreakTime['duration']) && $durationBreakTime['duration'] > 0 && $day_count < 1) {
                
                    $duration = $day_array[0]['duration_time_minute'] - $durationBreakTime['duration'];
                    
                    $duration_raw = hoursandmins($duration);
                    $day_array[0]['duration_time'] = formatHoursAndMinutes($duration_raw);
                    $day_array[0]['duration_time_minute'] = $duration;
                }
         
            }
        }

        return $day_array;
    }

    /**
     * Adjust minimum units for mixed support item
     * @param $data {array} array of data for day1 and day2 or only day1
     * @param $support_type_duration {array} support type duration Schedule/Actual
     * @param $active_duration {int} available active units
     * @param $get_day_count {int} day count
     * 
     * @return $support_type_duration {array} support type duration with adjusted duration
     */
    public function mixed_type_min_unit_adjustment($data, $support_type_duration, $active_duration, $get_day_count) {
        #2 hrs - Active hrs / 2
        $adjust_min = (120 - $active_duration) / 2;
        $reverse = FALSE;
        
        #If day 2 is higher presitence the do reverse the array value and apply the adjustment for 2nd day
        if ($get_day_count > 0 && $this->presitence_day_rule[$data[1]['day_of_week']] > $this->presitence_day_rule[$data[0]['day_of_week']]) {            
            
            $support_type_duration = array_reverse($support_type_duration);
            $reverse = TRUE;
            
        }
        $is_selfcare =  $is_comm_access = FALSE;
        $with_out_round_off_min = $adjust_min;
        foreach($support_type_duration as $key => $support_duration) {
            $support_duration = (object) $support_duration;
            #If selfcare or comm access adjustment already completed then skip for next row
            if(empty($support_duration->duration) || ($key == 2 && $is_selfcare) || ($key == 3 && $is_comm_access)) {                    
                continue;
            }
            #Round of adjustment units with only one input higher rate value since highest values coming reverse order in array
            $adjust_min = $with_out_round_off_min;
            if($key == '0') {
                $adjust_min = round($adjust_min);
                $is_selfcare = TRUE;
            } elseif ($key == 1) {
                $is_comm_access = TRUE;
            }
            $input_units = $this->hoursToMinutes($support_duration->duration);
            
            // $support_type_duration[$key]->duration = hoursandmins($input_units + $adjust_min);
            $support_duration->duration = hoursandmins($input_units + $adjust_min);
            $support_type_duration[$key] = $support_duration;
            
        }
        #Rollback the array order after completing the min unit adjustment for displaying frontend
        if($get_day_count > 0 && $reverse) {
            $support_type_duration = array_reverse($support_type_duration);
        }
        return $support_type_duration;
    }

    /** Function to get recent item price suppose if line item price not available */
    public function get_recent_item_price($line_item_id) {

        $this->db->select(["upper_price_limit"]);
        $this->db->from(TBL_PREFIX . 'finance_line_item_price');
        $this->db->where(['line_item_id' => $line_item_id]);
        $this->db->where("MONTH(end_date) <= '" . date('Y-m-d') ."'");
        $this->db->order_by('end_date', 'DESC');
        $this->db->limit(1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        $upper_price_limit = 0;

        if($result) {
            $upper_price_limit = $result[0]['upper_price_limit'];
        }
        return $upper_price_limit;
    }

    /** Check shift has sleepover break or not  */
    public function check_sleepover_break($break_rows) {

        if(empty($break_rows)){
            return FALSE;
        }
        $sleepOver = FALSE;
        $sleep_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);

        if (!empty($sleep_details)) {
            $slp_ref_id = $sleep_details->id;
        }
        foreach($break_rows as $row) {
            
            if(!empty($row['break_start_datetime']) && !empty($row['break_end_datetime'])) {
                if ($row['break_type'] == $slp_ref_id) {
                    $sleepOver = TRUE;
                    break;
                }
            }
        }

        return $sleepOver;
        
    }

    /** Sleepover min unit adjustment for start with min 1 hr end with min 4hr
     * 
     * @param $get_day_count {int} day count
     * @param $day_array {array} day array with duration
     * 
     * @see formatHoursAndMinutes
     * @see hoursandmins
     * 
     * @return {$day_array} {array} daay array with min unit adjust 
     */
    function sleepover_min_unit_adjustment($get_day_count, $day_array ) {
        
        #Single day
        if($get_day_count == 0) {
            $total_duration = 0;
            if($day_array[0]['duration_time_minute'] < 240) {
                $total_duration = 300;
            } else if(!empty($day_array[0]['day1_duration_time_minute']) && $day_array[0]['day1_duration_time_minute'] < 60) {
                $total_duration = $day_array[0]['day2_duration_time_minute'] + 60;   
            } #if day1 and day 2 is less than 4 hrs then update end day with day 1 + 4hr
            else if(!empty($day_array[0]['day1_duration_time_minute']) && 
            $day_array[0]['day1_duration_time_minute'] < 240
             && !empty($day_array[0]['day2_duration_time_minute']) && $day_array[0]['day2_duration_time_minute'] < 240) {
                $total_duration = $day_array[0]['day1_duration_time_minute'] + 240;
                
            }
            
            if($total_duration) {                
                $day_array[0]['duration_time'] = formatHoursAndMinutes(hoursandmins($total_duration));
                $day_array[0]['duration_time_minute'] = $total_duration;
            }
        } #More than one day
        else if($get_day_count > 0) { 
          
            $sday_min = $day_array[0]['duration_time_minute'];
            $eday_min = $day_array[1]['duration_time_minute'];
            $total_minutes = $sday_min + $eday_min;
            
            $sdPresitance = $this->presitence_day_rule[$day_array[0]['day_of_week']] ?? NULL;
            $edPresitance = $this->presitence_day_rule[$day_array[1]['day_of_week']] ?? NULL;
           
            #If start date is higher priority then adjust units with start date
            if($sdPresitance > $edPresitance) {
                
                if($total_minutes < 240 && $sday_min < 60) {
                    #Adjust 1hr for day one if
                    $sday_min = 60;
                }

                #Adjust with 4hrs
                if($total_minutes < 240) {
                    $sday_min = 240;
                }
                else if($total_minutes >= 240 && $sday_min < 60) {
                    #Adjust 1hr for day one if
                    $sday_min = 60;
                }
               
            } else if($sdPresitance < $edPresitance) { #If end date is higher priority then adjust units with start date
                #if total minutes less than 4 hrs and start is day less than 1hr then adjust day1 value as 1hr
                if($sday_min < 60) {
                    #Adjust 1hr for day one if
                    $sday_min = 60;
                }
                if($eday_min < 240 && $sday_min < 240) {
                    #Adjust with 4hrs
                    $eday_min = 240;
                }                
            }
            #Split total duration separatly for start day and end day
            $day_array[0]['duration_time'] = formatHoursAndMinutes(hoursandmins($sday_min));
            $day_array[0]['duration_time_minute'] = $sday_min;
            $day_array[1]['duration_time'] = formatHoursAndMinutes(hoursandmins($eday_min));
            $day_array[1]['duration_time_minute'] = $eday_min;
            $day_array[0]['total_duration'] = $sday_min + $eday_min;
            
        }
        
        return $day_array;
    }

    /**
     * To adjust the S/O remaining minimum units based on the user inputs
     * 
     * @param $line_item_day_array {array} day array with durations
     * @param $support_type_duration{obj} support type duration user input
     * @param $get_day_count{int} day count
     * 
     * @see hoursToMinutes
     * 
     * @return $support_type_duration {obj} with or with out S/O adjustment
     */
    function mixed_type_so_duration_adjustment($line_item_day_array, $support_type_duration, $get_day_count) {
        
        if(empty($line_item_day_array) || empty($support_type_duration)) {
            return $support_type_duration;
        }
        $day_1_self_care = $support_type_duration[0]->duration ? $this->hoursToMinutes($support_type_duration[0]->duration) : 0;
        $day_1_comm_access = $support_type_duration[1]->duration ? $this->hoursToMinutes($support_type_duration[1]->duration) : 0;        
        
        $day_1_total_hrs = $day_1_self_care + $day_1_comm_access;
        #Total day one duration - user entered value for day 1 for getting adjustmented units
        $day_1_adjusted_min = $line_item_day_array[0]['duration_time_minute'] - $day_1_total_hrs;

        if( $get_day_count > 0 ) { #More than one day      
            #Total user entered hours
            if($day_1_adjusted_min > 0 && !empty($day_1_self_care) && !empty($day_1_comm_access)) {
                #Split the adjustments for both selfcare and comm access
                $split_hrs = $day_1_adjusted_min / 2;
                $support_type_duration[0]->duration = hoursandmins($day_1_self_care + round($split_hrs));
                $support_type_duration[1]->duration = hoursandmins($day_1_comm_access + $split_hrs);
            }
            else if(empty($day_1_comm_access)) {
                $support_type_duration[0]->duration = hoursandmins($day_1_self_care + $day_1_adjusted_min);
            }
            else if(empty($day_1_self_care)) {
                $support_type_duration[1]->duration = hoursandmins($day_1_comm_access + $day_1_adjusted_min);
            }

            $day_2_self_care = !empty($support_type_duration[2]->duration) ? $this->hoursToMinutes($support_type_duration[2]->duration) : 0;
            $day_2_comm_access = !empty($support_type_duration[3]->duration) ? $this->hoursToMinutes($support_type_duration[3]->duration) : 0;

            #Total user entered hours
            $day_2_total_hrs = $day_2_self_care + $day_2_comm_access;
            $day_2_adjusted_min = $line_item_day_array[1]['duration_time_minute'] - $day_2_total_hrs;

            if($day_2_adjusted_min > 0 && !empty($day_2_self_care) && !empty($day_2_comm_access)) {
                $split_hrs = $day_2_adjusted_min / 2;
                $support_type_duration[2]->duration = hoursandmins($day_2_self_care + round($split_hrs));
                $support_type_duration[3]->duration = hoursandmins($day_2_comm_access + $split_hrs);
            }
            else if(empty($day_2_comm_access) && !empty($day_2_self_care)) {
                $support_type_duration[2]->duration = hoursandmins($day_2_self_care + $day_2_adjusted_min);
            }
            else if(empty($day_2_self_care) && !empty($day_2_comm_access)) {
                $support_type_duration[3]->duration = hoursandmins($day_2_comm_access + $day_2_adjusted_min);
            }


        } else { #Single day
            
            $split_hrs = $day_1_adjusted_min / 2;
            $support_type_duration[0]->duration = hoursandmins($day_1_self_care + round($split_hrs));
            $support_type_duration[1]->duration = hoursandmins($day_1_comm_access + $split_hrs);
        }
       
        return $support_type_duration;
    }
        
    /** Get Finance support type keyname by support type id */
    function get_finance_support_type_name($support_type) {
        $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
        $support_key_name = '';
        if (!empty($supportType->key_name)) {
            $support_key_name = $supportType->key_name;
        }
        return $support_key_name;
    }
}
