<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function calculate_gst($amount) {
    $gst = 0;
    if ($amount > 0) {
        $gst = ($amount * INVOICE_GST) / 100;
        $gst = custom_round($gst);
    }
    return $gst;
}

function custom_round($amount) {
    $amount = (float) round($amount, 2);
    return $amount;
}

function calculate_qty_using_date($tart_date, $end_date) {
    $start_time = new DateTime(DateFormate($tart_date, "Y-m-d H:i:s"));
    $end_time = new DateTime(DateFormate($end_date, "Y-m-d H:i:s"));
    $interval = $start_time->diff($end_time);

    $hours = (int) $interval->format('%h');
    $hours += (float) ((int) $interval->format('%i') / 60);

    return $hours;
}

function get_price_type_on_base_postcode($postcode) {
    //1 = upper_price_limit
    //2 = national_price_limit
    //3 = national_very_price_limit
    $price_types = ['upper_price_limit' => 1, 'national_price_limit' => 2, 'national_very_price_limit' => 3];

    return 1;
}

function save_notification($data) {
    $ci = & get_instance();
    $ci->load->library('Notification');
    $ci->notification->setUser_type($data['user_type']);
    $ci->notification->setSender_type($data['sender_type']);
    $ci->notification->setUserId($data['user_id']);
    $ci->notification->setTitle($data['title']);
    $ci->notification->setShortdescription($data['description']);
    $ci->notification->createNotification();
}

function get_shift_confirm_by($id, $notExistsMsg = '') {
    $data = json_decode(SHIFT_CONFIRM_BY, TRUE);
    return $data[$id] ?? $notExistsMsg;
}

function get_current_n_previous_financial_year() {
    $years = [];
    $current_month = date('m');
    if (date('m') > 6) {
        //Upto June 2014-2015
        $start_year = $financial_start = date('Y');
        $end_year = $financial_end = date('Y') + 1;
    } else {
        //After June 2015-2016
        $start_year = $financial_start = date('Y') - 1;
        $end_year = $financial_end = date('Y');
    }

    $years['financial_start'] = $financial_start = DateFormate($financial_start . '-07-01', 'Y-m-d');
    $years['financial_end'] = $financial_end = DateFormate($financial_end . '-06-30', 'Y-m-d');

//    $lastyear = strtotime("-1 year", strtotime($financial_start));
//    $previous_finicial_start_date = date("Y-m-d", $lastyear);
//
//    $lastyear2 = strtotime("-1 year", strtotime($financial_end));
//    $previous_finicial_end_date = date("Y-m-d", $lastyear2);
//
//    $years['previous_finicial_start_date'] = $previous_finicial_start_date;
//    $years['previous_finicial_end_date'] = $previous_finicial_end_date;
    return $years;
}

#get domain name from url without www and extension by passing URL

function getDomain($url) {
    $things_like_WWW_at_the_start = array('www');
    $urlContents = parse_url($url);
    $domain = explode('.', $urlContents['host']);

    if (!in_array($domain[0], $things_like_WWW_at_the_start))
        return $domain[0];
    else
        return $domain[1];
}

function get_all_stage_mapping_component() {
    $mapping_coponent = [
        'review_answer' => 'ReviewAnswerStage',
        'phone_interview' => 'PhoneInterviewStage',
        'document_checklist' => 'DocumentCheckListStage',
        'position_and_award_level' => 'PositionAndAwardLevelsStage',
        'review_references' => 'ReferenceChecksStage',
        'recruitment_complete' => 'RecruitmentCompletedStage',
        'schedule_individual_interview' => 'IndividualInterviewStage',
        'individual_applicant_responses' => 'ApplicantResponseIndividualStage',
        'individual_interview_result' => 'IndividualInterviewResultStage',
        'individual_interview_offer' => 'OffersStage',
        'group_schedule_interview' => 'ScheduleInterviewStage',
        'group_applicant_responses' => 'ApplicantResponseGroupStage',
        'group_interview_result' => 'GroupInterviewResultStage',
        'schedule_cab_day' => 'ScheduleCabDayStage',
        'cab_applicant_responses' => 'ApplicantResponseCabDayStage',
        'cab_day_result' => 'CabDayResultStage',
        'employment_contract' => 'EmploymentContractStage',
        'member_app_onboarding' => 'MemberAppOnbordingStage'
    ];
    return $mapping_coponent;
}

function devide_google_or_manual_address($address) {
    $x = explode(",", $address);

    if (!empty($x[1])) {
        $suburb_state_postcode = $x[1];
        $postcode = trim(substr($suburb_state_postcode, -4));

        substr_replace($postcode, "", -1);
        $suburb_state = trim(str_replace($postcode, "", $suburb_state_postcode));

        $ci = & get_instance();
        $state_list = $ci->basic_model->get_record_where("state", ["name", "id"], ["archive" => 0]);

        $stateId = null;
        $suburb = '';

        $sub_state = explode(' ', $suburb_state);

        if (!empty($state_list)) {
            foreach ($state_list as $val) {
                //check and replace last array value for state check
                if (!empty($sub_state) && strtoupper($sub_state[count($sub_state) - 1]) == strtoupper($val->name)) {
                    $stateId = $val->id;
                    array_pop($sub_state);
                    $suburb = implode(" ", $sub_state);
                    break;
                }
            }
        }
    }

    $return_address = [
        'street' => $x[0] ?? '',
        'suburb' => $suburb ?? '',
        'state' => $stateId ?? '',
        'postcode' => $postcode ?? '',
    ];

    return $return_address;
}

/*
*Create associate unique
*by passing key
**/
function my_unique_array($my_array, $key) {
    $result = array();
    $i = 0;
    $key_array = array();

    foreach($my_array as $val) {
        if (!in_array($val[$key], $key_array)) {
            $key_array[$i] = $val[$key];
            $result[$i] = $val;
            $i++;
        }
    }
    return $result;
}

/**
 * Function that groups an array of associative arrays by some key.
 *
 * @param {String} $key Property to sort by.
 * @param {Array} $data Array that stores multiple associative arrays.
 */

function group_by($key, $data) {
    $result = array();

    foreach($data as $val) {
        if(array_key_exists($key, $val)){
            $result[$val[$key]][] = $val;
        }else{
            $result[""][] = $val;
        }
    }

    return $result;
}