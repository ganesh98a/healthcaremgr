<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/*
 * Class: RecruitmentWeb_shifts_test
 * Uses : TDD - Controller
 */
class RecruitmentWeb_shifts_test extends TestCase {
    // Defualt contruct function
    protected $CI;
    protected $postdata = [
        "actual_end_date" => "2021-01-05", # has to be same tuesday as 'actual_start_date'
        "actual_end_time" => "05:00 PM",
        "actual_reimbursement" => null,
        "actual_rows" => null,
        "actual_start_date" => "2021-01-05", # has to be tuesday
        "actual_start_time" => "03:00 PM",
        "actual_travel" => null,
        "applicant_id" => "110",
        "id" => "193",
        "member_id" => "23",
        "notes" => "my notes",
        "shift_no" => "ST000000193"
    ];

    public function setUp() {   
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/recruitment/models/Recruitment_member_model');
        $this->CI->load->library('form_validation');
        $this->CI->load->model('../modules/member/models/Member_model');
        $this->CI->load->model('schedule/Schedule_model');
        $this->Member_model = $this->CI->Member_model;
        $this->Recruitment_member_model = $this->CI->Recruitment_member_model;
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /*
     * testing the validation failure in editing shifts
     */
    function test_create_update_shift_portal_val1() {
        $postdata = $this->postdata;
        $postdata["id"] = null;
        $postdata["actual_start_date"] = null;
        $postdata["actual_end_date"] = null;
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in editing shifts
     */
    function test_accept_reject_shift_val2() {
        $postdata = $this->postdata;
        $postdata["actual_start_time"] = null;
        $postdata["actual_end_time"] = null;
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the correct data time range of actual timings
     */
    function test_accept_reject_shift_val3() {
        $postdata = $this->postdata;
        $postdata["actual_start_time"] = "03:00 PM";
        $postdata["actual_end_time"] = "02:30 PM";
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the actual break time should be after the actual shift start date time
     */
    function test_accept_reject_shift_val4() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "paid", "archive" => 0]);
        if ($details)
            $p_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $up_ref_id = $details->id;

        $postdata = $this->postdata;
        $postdata["actual_rows"] = 
            json_encode([[
                "id" => null,
                "break_duration" => "01:30",
                "break_end_time" => "04:00 PM",
                "break_start_time" => "02:30 PM",
                "break_type" => $p_ref_id,
                "duration_disabled" => true,
                "shift_id" => "193",
                "timing_disabled" => false
            ]]);
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the break timings validity against actual shift start & end time
     */
    function test_accept_reject_shift_val5() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "paid", "archive" => 0]);
        if ($details)
            $p_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $up_ref_id = $details->id;

        $postdata = $this->postdata;
        $postdata["actual_rows"] = json_encode([
            [
                "id" => null,
                "break_duration" => "01:00",
                "break_end_time" => "04:00 PM",
                "break_start_time" => "03:00 PM",
                "break_type" => $p_ref_id,
                "duration_disabled" => true,
                "shift_id" => "193",
                "timing_disabled" => false
            ],
            [
                "id" => null,
                "break_duration" => "01:00",
                "break_end_time" => "04:30 PM",
                "break_start_time" => "03:30 PM",
                "break_type" => $up_ref_id,
                "duration_disabled" => true,
                "shift_id" => "193",
                "timing_disabled" => false
            ]
        ]);
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing successful updating of a shift
     */
    function test_accept_reject_shift_success1() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "paid", "archive" => 0]);
        if ($details)
            $p_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $up_ref_id = $details->id;

        $postdata = $this->postdata;
        $postdata["actual_rows"] = json_encode([
            [
                "id" => null,
                "break_duration" => "00:30",
                "break_end_time" => "03:30 PM",
                "break_start_time" => "03:00 PM",
                "break_type" => $p_ref_id,
                "duration_disabled" => true,
                "shift_id" => "193",
                "timing_disabled" => false
            ],
            [
                "id" => null,
                "break_duration" => "01:00",
                "break_end_time" => "04:30 PM",
                "break_start_time" => "03:30 PM",
                "break_type" => $up_ref_id,
                "duration_disabled" => true,
                "shift_id" => "193",
                "timing_disabled" => false
            ]
        ]);
        $output = $this->Schedule_model->create_update_shift_portal($postdata, 23);
        return $this->assertTrue($output['status']);
    }
}
