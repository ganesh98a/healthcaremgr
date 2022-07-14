<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller ScheduleDashboard and related models of it
 */
class ScheduleDashboard_unavail_test extends TestCase {
    
    protected $CI;

    public $postdata = [
        "shift_no" => null,
        "person_id" => 2,
        "owner_id" => 1,
        "account_type" => 1,
        "account_id" => 2,
        "contact_id" => 2,
        "role_id" => 1,
        "scheduled_start_date" => "2021-06-02", # has to be wednesday same week in ScheduleDashboard_test.php
        "scheduled_end_date" => "2021-06-03", # has to be next day
        "scheduled_start_time" => "01:00 PM",
        "scheduled_end_time" => "11:00 PM",
        "scheduled_paid_break" => null,
        "scheduled_travel" => null,
        "scheduled_unpaid_break" => null,
        "actual_start_date" => null,
        "actual_end_date" => null,
        "actual_start_time" => null,
        "actual_end_time" => null,
        "actual_paid_break" => null,
        "actual_travel" => null,
        "actual_unpaid_break" => null,
        "description" => "my description",
        "notes" => "my notes",
        "status" => 2,
        "contact_email" => "test@yopmail.com",
        "contact_phone" => "9898989898"
    ];

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->library('form_validation');
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /**
     * validating the failure on adding shift members
     */
    function test_assign_shift_members_val1() {
        $postdata = null;
        $postdata['shift_id'] = 193;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating failure in the SO active period before
     */
    function test_create_update_shift_so_fail1() {
        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_time"] = "07:00 PM";
        $postdata["scheduled_end_time"] = "03:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => 386,
            "break_start_time" => "10:00 PM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ]
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating failure in the SO active period after
     */
    function test_create_update_shift_so_fail2() {
        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_time"] = "11:00 PM";
        $postdata["scheduled_end_time"] = "11:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => 386,
            "break_start_time" => "03:00 AM",
            "break_end_time" => "11:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 385,
            "break_start_time" => "01:00 AM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertContains("Shift needs to have 4 Hrs of Active work",$output['error']);
    }

    /**
     * validating successful updating of shift with SO active period and unpaid break
     */
    function test_create_update_shift_so_success1() {
        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_time"] = "10:00 PM";
        $postdata["scheduled_end_time"] = "11:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => 386,
            "break_start_time" => "03:00 AM",
            "break_end_time" => "11:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 385,
            "break_start_time" => "01:00 AM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating the failure in assigning shift as current shift timings overlaps with accepted shifts
     */
    function test_assign_shift_members_overlaps_accepted_shifts_fail1() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-2 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "11:00 AM";
        $postdata["scheduled_end_time"] = "03:00 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        //Making it failed because it needs to be rewritten
        return $this->assertTrue(false);
    }

    /**
     * validating the overtime failure on adding shift members
     */
    function test_assign_shift_members_overtime_fail1() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "01:00 PM";
        $postdata["scheduled_end_time"] = "11:00 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("require overtime",$output['error']);
    }

    /**
     * validating the next/prev day shift gap failure
     */
    function test_assign_shift_members_nextprev_day_fail1() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "06:00 PM";
        $postdata["scheduled_end_time"] = "11:30 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("Shift overtimes with next",$output['error']);
    }

    /**
     * validating the next/prev day shift gap failure
     */
    function test_assign_shift_members_nextprev_day_fail2() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+2 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "04:30 AM";
        $postdata["scheduled_end_time"] = "12:00 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("Shift overtimes with next",$output['error']);
    }

    /**
     * validating the same day shift gap failure
     */
    function test_assign_shift_members_same_day_fail1() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "05:30 PM";
        $postdata["scheduled_end_time"] = "08:30 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("Shift overtimes with same",$output['error']);
    }

    /**
     * validating the same day shift gap failure
     */
    function test_assign_shift_members_same_day_fail2() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "01:30 AM";
        $postdata["scheduled_end_time"] = "06:00 AM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("Shift overtimes with same",$output['error']);
    }

    /**
     * validating the sleep over shift gap failure
     */
    function test_assign_shift_members_so_gap_fail1() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_time"] = "06:00 PM";
        $postdata["scheduled_end_time"] = "07:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "07:00 PM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("gap with S/O shift",$output['error']);
    }

    /**
     * validating the sleep over shift gap failure
     */
    function test_assign_shift_members_so_gap_fail2() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+2 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "10:30 AM";
        $postdata["scheduled_end_time"] = "02:30 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("gap with S/O shift",$output['error']);
    }

    /**
     * validating the sleep over shift gap failure
     */
    function test_assign_shift_members_so_gap_fail3() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-2 day'));
        $postdata["scheduled_end_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["scheduled_start_time"] = "05:00 PM";
        $postdata["scheduled_end_time"] = "06:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "06:00 PM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("gap with S/O shift",$output['error']);
    }

    /**
     * validating the same day shift gap between two shifts
     */
    function test_assign_shift_members_shifts_gap_fail1() {

        // disable google maps duration check first
        save_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, 0);

        # removing copy/cloned/repeated shifts as it conflicts with existing shift timings
        $this->basic_model->delete_records('shift', 'id > 198');

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "03:00 AM";
        $postdata["scheduled_end_time"] = "06:30 AM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("mins gap with",$output['error']);
    }

    /**
     * validating the same day shift gap between two shifts
     */
    function test_assign_shift_members_shifts_gap_fail2() {

        // disable google maps duration check first
        save_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "03:30 PM";
        $postdata["scheduled_end_time"] = "07:00 PM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("mins gap with",$output['error']);
    }

    /**
     * validating the same day shift gap between two shifts using google maps duration check
     */
    function test_assign_shift_members_shifts_gap_google_fail1() {

        // enable google maps duration check first
        save_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, 1);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "03:00 AM";
        $postdata["scheduled_end_time"] = "07:30 AM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("less driving duration than",$output['error']);
    }

    /*
     * checking the success in assigning shif member after overwrting the overtime
     */
    function test_assign_shift_members_success() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 1);
        // disable google maps duration check first
        save_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, 0);

        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "01:30 AM";
        $postdata["scheduled_end_time"] = "06:00 AM";
        $return = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata = null;
        $postdata['shift_id'] = 193;

        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the failure in updating status to "scheduled" as assigned member requires overtime
     */
    function test_shift_status_update_failure() {

        // save overtime setting using this helper function
        save_setting(Setting::OVERTIME_ALLOWED, 0);

        $postdata['id'] = 193;
        $postdata["status"] = 3;
        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        return $this->assertContains("Shift overtimes",$output['error']);
    }

    /*
     * testing the failure in accepting the shift as it will require overtime
     */
    function test_accept_shift_failure() {

        $postdata = [
            "shift_id" => 193,
            "member_id" => 23,
            "status" => 1
        ];

        $details = $this->basic_model->get_row('shift_member', ['id'], ["shift_id" => $postdata['shift_id'], "member_id" => $postdata['member_id'], "archive" => 0]);
        if($details->id)
        $postdata['id'] = $details->id;

        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertContains("Shift overtimes",$output['error']);
    }
}
