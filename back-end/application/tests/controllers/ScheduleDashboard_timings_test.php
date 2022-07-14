<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDashboard and related models of it
 */
class ScheduleDashboard_timings_test extends TestCase {
    
    protected $CI;
    public $postdata = [
        "shift_no" => null,
        "person_id" => 3,
        "owner_id" => 1,
        "account_type" => 1,
        "account_id" => 2,
        "contact_id" => 2,
        "role_id" => 1,
        "scheduled_start_date" => "2021-06-10", # has to be thursday of next week in unavail_test.php
        "scheduled_end_date" => "2021-06-11", # has to be next day of start date
        "scheduled_start_time" => "10:00 PM",
        "scheduled_end_time" => "07:00 AM",
        "scheduled_paid_break" => null,
        "scheduled_travel" => null,
        "scheduled_unpaid_break" => null,
        "actual_start_date" => "2021-06-10", # has to be thursday of next week in unavail_test.php
        "actual_end_date" => "2021-06-11", # has to be next day of start date
        "actual_start_time" => "11:00 PM",
        "actual_end_time" => "07:00 AM",
        "actual_paid_break" => null,
        "actual_travel" => null,
        "actual_unpaid_break" => null,
        "description" => "my description",
        "notes" => "my notes",
        "status" => 1,
        "contact_email" => "test@yopmail.com",
        "contact_phone" => "9898989898"
    ];

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->model('common/Common_model');
        $this->CI->load->library('form_validation');
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
        $this->Common_model = $this->CI->Common_model;

        $person_detail = $this->basic_model->get_row('person', array("MAX(id) AS person_id"));
        if ($person_detail->person_id) 
        $this->postdata["contact_id"] = $person_detail->person_id;
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data1() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "unpaid", "archive" => 0]);
        if ($details)
            $up_ref_id = $details->id;

        $postdata = $this->postdata;
        // $postdata['id'] = 190;
        $postdata['shift_no'] = "ST000000190";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-9 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 AM";
        $postdata["actual_end_time"] = "02:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $actual_rows = [
            ["id" => null,
            "break_type" => $up_ref_id,
            "break_start_time" => "08:00 AM",
            "break_end_time" => "08:15 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $scheduled_rows = [
            ["id" => null,
            "break_type" => $up_ref_id,
            "break_start_time" => "08:00 AM",
            "break_end_time" => "08:15 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data2() {

        $postdata = $this->postdata;
        // $postdata['id'] = 198;
        $postdata['shift_no'] = "ST000000198";
        $postdata['account_type'] = 1;
        $postdata['account_id'] = 2;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-7 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "09:00 AM";
        $postdata["actual_end_time"] = "07:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data3() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;

        $postdata = $this->postdata;
        // $postdata['id'] = 191;
        $postdata['shift_no'] = "ST000000191";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-6 day'));
        $postdata["actual_end_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "10:00 PM";
        $postdata["actual_end_time"] = "01:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $postdata['skip_account_shift_overlap'] = true;
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $scheduled_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data4() {

        $postdata = $this->postdata;
        $postdata['id'] = 194;
        $postdata['shift_no'] = "ST000000194";
        $postdata['account_type'] = 1;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-10 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "06:00 AM";
        $postdata["actual_end_time"] = "12:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 194;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data5() {

        $postdata = $this->postdata;
        $postdata['id'] = 184;
        $postdata['shift_no'] = "ST000000184";
        $postdata['account_type'] = 1;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-4 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "06:00 AM";
        $postdata["actual_end_time"] = "09:00 AM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 184;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data6() {

        $so_ref_id = null;
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;

        $postdata = $this->postdata;
        // $postdata['id'] = 183;
        $postdata['shift_no'] = "ST000000183";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-5 day'));
        $postdata["actual_end_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "10:00 PM";
        $postdata["actual_end_time"] = "11:00 AM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ]
        ];
        $postdata['actual_rows'] = $actual_rows;
        $postdata['scheduled_rows'] = $postdata['actual_rows'];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * preparing the shift data for comparison
     */
    function test_prepare_required_data7() {
        $postdata = $this->postdata;
        $postdata['id'] = 185;
        $postdata['shift_no'] = "ST000000185";
        $postdata['account_type'] = 1;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-5 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "06:00 AM";
        $postdata["actual_end_time"] = "07:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = "06:00 AM";
        $postdata["scheduled_end_time"] = "04:00 PM";
        $postdata['actual_rows'] = [];
        $postdata['scheduled_rows'] = [];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 185;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /*
     * checking the mandatory shift no
     */
    function test_create_update_shift_val1() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the mandatory account-id and work type
     */
    function test_create_update_shift_val2() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata['account_id'] = null;
        $postdata['role_id'] = null;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertContains("The Account field is required., The Service Type field is required", $output['error']);
    }

    /*
     * checking the mandatory scheduled start time and scheduled end date
     */
    function test_create_update_shift_val3() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];
        $postdata['scheduled_start_time'] = null;
        $postdata['scheduled_end_date'] = null;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the actual end time and actual start time correct format
     
    function test_create_update_shift_val4() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata["actual_start_time"] = "01:00AM";
        $postdata["actual_end_time"] = "1.55 AM";

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertContains("Incorrect actual start date & time, Incorrect actual end date & time", $output['error']);
    }*/

    /*
     * checking the actual end date and actual start date in correct range
     */
    function test_create_update_shift_val5() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "01:00 AM";
        $postdata["actual_end_time"] = "12:55 AM";

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating sheduled breaks, all data including break type and (start & end time OR duration) need to be provided
     */
    function test_create_update_shift_val6() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $scheduled_rows = [
            ["id" => null,
            "break_type" => null,
            "break_start_time" => null,
            "break_end_time" => null,
            "break_duration" => null,
            "duration_disabled" => false,
            "timing_disabled" => false
            ]
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        $break_type_invalid = $this->assertContains("Please provide Break Type", $output['error']);
        $timing_invalid = $this->assertContains("Please provide either Start Time", $output['error']);
        $is_invalid = ($break_type_invalid == false && $timing_invalid == false) ? true : false;
        
        return $this->assertTrue($is_invalid);
    }

    /**
     * scheduled break start time should be lower than shift scheduled start time
     */
    function test_create_update_shift_val7() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $scheduled_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "09:30 PM",
            "break_end_time" => "11:00 PM",
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
     * scheduled break end time should be lower than scheduled break start time
     */
    function test_create_update_shift_val8() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $scheduled_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "01:30 AM",
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
     * scheduled break timing should not overlap with other scheduled break timing
     */
    function test_create_update_shift_val9() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $scheduled_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:30 AM",
            "break_end_time" => "04:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * scheduled break durations should not exceed the overall scheduled shift duration
     */
    function test_create_update_shift_val10() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $scheduled_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "01:00 AM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => null,
            "break_end_time" => null,
            "break_duration" => "08:30",
            "duration_disabled" => false,
            "timing_disabled" => true
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating actual breaks, all data including break type and (start & end time OR duration) need to be provided
     */
    function test_create_update_shift_val11() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $actual_rows = [
            ["id" => null,
            "break_type" => null,
            "break_start_time" => null,
            "break_end_time" => null,
            "break_duration" => null,
            "duration_disabled" => false,
            "timing_disabled" => false
            ]
        ];
        $postdata['actual_rows'] = $actual_rows;

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        $break_type_invalid = $this->assertContains("Please provide Break Type", $output['error']);
        $timing_invalid = $this->assertContains("Please provide either Start Time", $output['error']);
        $is_invalid = ($break_type_invalid == false && $timing_invalid == false) ? true : false;
        
        return $this->assertTrue($is_invalid);
    }

    /**
     * actual break start time should be lower than shift actual start time
     */
    function test_create_update_shift_val12() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $actual_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "09:30 PM",
            "break_end_time" => "11:00 PM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ]
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * actual break end time should be lower than actual break start time
     */
    function test_create_update_shift_val13() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $actual_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "01:30 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ]
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * actual break timing should not overlap with other actual break timing
     */
    function test_create_update_shift_val14() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $actual_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:30 AM",
            "break_end_time" => "04:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * actual break durations should not exceed the overall actual shift duration
     */
    function test_create_update_shift_val15() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $actual_rows = [
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "01:00 AM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => "02:00 AM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 384,
            "break_start_time" => null,
            "break_end_time" => null,
            "break_duration" => "08:30",
            "duration_disabled" => false,
            "timing_disabled" => true
            ],
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
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
        $postdata["scheduled_end_time"] = "07:00 AM";
        $scheduled_rows = [
            ["id" => null,
            "break_type" => 386,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => 385,
            "break_start_time" => "10:00 PM",
            "break_end_time" => "10:30 PM",
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
     * testing updating a shift which is already being updated by other member
     */
    function test_create_update_shift_being_updated_fail1() {
        # remove any existing lock of current user 1
        $remarr['object_type'] = 'shift';
        $remarr['object_id'] = 193;
        $this->Common_model->remove_access_lock($remarr, 1);

        # getting shift details for updation with lock acquired by other user 25
        $details = $this->Schedule_model->get_shift_details(193, $clone = false, $from_portal = false, $shift_lock = true, $adminId = 25);

        # trying to update the same shift should fail by current user
        $postdata = $this->postdata;
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata["scheduled_start_time"] = "11:00 PM";
        $postdata["scheduled_end_time"] = "05:00 AM";
        $postdata['scheduled_rows'] = [];
        $output = $this->Schedule_model->create_update_shift($postdata, 1);

        return $this->assertContains("This record is locked",$output['error']);
    }

    /**
     * testing assigning shift memebers for a shift which is already being updated by other member
     */
    function test_create_update_shift_being_updated_fail2() {
        # trying to assign a member
        $postdata['shift_id'] = 193;
        $newobj = new StdClass;
        $newobj->id = 23;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;
        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertContains("This record is locked",$output['error']);
    }

    /**
     * testing archiving a shift which is already being updated by other member
     */
    function test_create_update_shift_being_updated_fail3() {
        # trying to archive a shift
        $output = $this->Schedule_model->archive_shift(["id" => 193], 1);
        
        # remove the lock from other user as we are done
        $remarr['object_type'] = 'shift';
        $remarr['object_id'] = 193;
        $this->Common_model->remove_access_lock($remarr, 25);

        return $this->assertContains("This record is locked",$output['error']);
    }

    /**
     * validating successful updating of shift with SO active period and unpaid break
     */
    function test_create_update_shift_so_success1() {
        $postdata = $this->postdata;
        // $postdata['id'] = 48;
        $postdata['shift_no'] = "ST000000048";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["scheduled_end_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
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
     * validating successful updating of shift with SO more than 8 hours
     * active period before SO less than 1 hour
     * active period after SO less than 4 hours
     * for Actual break timings
     */
    function test_create_update_shift_so_success2() {

        $so_ref_id = null;
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;

        $postdata = $this->postdata;
        $postdata['id'] = 48;
        $postdata['shift_no'] = "ST000000048";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["scheduled_end_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "10:00 PM";
        $postdata["actual_end_time"] = "04:00 AM";
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "10:30 PM",
            "break_end_time" => "03:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ]
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 2,3,4,8,14,17
     */
    function test_create_update_shift_timesheet_line_items_suc1() {

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
        // $postdata['id'] = 5;
        $postdata['shift_no'] = "ST000000005";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 100;

        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+3 day'));
        $postdata["scheduled_end_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+3 day'));
        $postdata["actual_end_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "09:00 PM";
        $postdata["actual_end_time"] = "11:00 AM";
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 1,3,4,5,6,8,14,16,24
     */
    function test_create_update_shift_timesheet_line_items_suc2() {

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
        // $postdata['id'] = 48;
        $postdata['shift_no'] = "ST000000048";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 99;
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["scheduled_end_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_end_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "10:00 PM";
        $postdata["actual_end_time"] = "08:00 PM";
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "10:30 PM",
            "break_end_time" => "03:30 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 3,4,7,22
     */
    function test_create_update_shift_timesheet_line_items_suc3() {
        
        $postdata = $this->postdata;
        $postdata['id'] = 35;
        $postdata['shift_no'] = "ST000000035";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "05:00 AM";
        $postdata["scheduled_end_time"] = "11:00 AM";
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-1 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "05:00 AM";
        $postdata["actual_end_time"] = "11:00 AM";
        $postdata["actual_travel"] = "3000";
        $postdata['actual_rows'] = [];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 35;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 3,4,9,12 and 13
     */
    function test_create_update_shift_timesheet_line_items_suc4() {
        
        $postdata = $this->postdata;
        $postdata['id'] = 197;
        $postdata['shift_no'] = "ST000000197";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-2 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "02:00 PM";
        $postdata["scheduled_end_time"] = "10:00 PM";
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-2 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "02:00 PM";
        $postdata["actual_end_time"] = "10:00 PM";
        $postdata['actual_rows'] = [];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 197;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 3,4,10,11,22 and 23
     */
    function test_create_update_shift_timesheet_line_items_suc5() {
        
        $postdata = $this->postdata;
        $postdata['id'] = 196;
        $postdata['shift_no'] = "ST000000196";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-3 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "07:00 AM";
        $postdata["scheduled_end_time"] = "05:00 PM";
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-3 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "07:00 AM";
        $postdata["actual_end_time"] = "06:00 PM";
        $postdata["actual_travel"] = "2500";
        $postdata['actual_rows'] = [];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 196;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 18-19
     */
    function test_create_update_shift_timesheet_line_items_suc6() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $postdata = $this->postdata;
        $postdata['id'] = 189;
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata['shift_no'] = "ST000000189";
        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-9 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "08:00 PM";
        $postdata["scheduled_end_time"] = "11:00 PM";
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-9 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 PM";
        $postdata["actual_end_time"] = "11:00 PM";
        $postdata['actual_rows'] = [];
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 189;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 20-21
     */
    function test_create_update_shift_timesheet_line_items_suc7() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $postdata = $this->postdata;
        // $postdata['id'] = 187;
        $postdata['shift_no'] = "ST000000187";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;

        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-8 day'));
        $postdata["actual_end_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_start_time"] = "10:00 PM";
        $postdata["actual_end_time"] = "06:00 AM";
        $postdata["actual_reimbursement"] = "11.00";
        $actual_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "02:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
        ];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata2['id'] = $details->last_shift_id;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 25-26
     */
    function test_create_update_shift_timesheet_line_items_suc8() {

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $postdata = $this->postdata;
        $postdata['id'] = 186;
        $postdata['shift_no'] = "ST000000186";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-6 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "02:00 AM";
        $postdata["actual_end_time"] = "05:00 AM";

        $postdata["scheduled_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-7 day'));
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $actual_rows = [];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 186;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 28
     */
    function test_create_update_shift_timesheet_line_items_suc9() {       
        $postdata = $this->postdata;
        $postdata['id'] = 181;
        $postdata['shift_no'] = "ST000000181";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-5 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 PM";
        $postdata["actual_end_time"] = "11:00 PM";

        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "05:00 PM";
        $postdata["scheduled_end_time"] = "08:00 PM";
        $actual_rows = [];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 181;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 28
     */
    function test_create_update_shift_timesheet_line_items_suc10() {
        $postdata = $this->postdata;
        $postdata['id'] = 182;
        $postdata['shift_no'] = "ST000000182";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-5 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 AM";
        $postdata["actual_end_time"] = "02:00 PM";

        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "09:00 AM";
        $postdata["scheduled_end_time"] = "12:00 PM";
        $postdata['skip_account_shift_overlap'] = true;
        $actual_rows = [];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 182;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }

    /**
     * validating successful marking of shift as successful and creating of timesheet
     * with line item business rule 28
     */
    function test_create_update_shift_timesheet_line_items_suc11() {
        $postdata = $this->postdata;
        $postdata['id'] = 180;
        $postdata['shift_no'] = "ST000000180";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-5 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "02:00 PM";
        $postdata["actual_end_time"] = "07:00 PM";

        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["scheduled_start_date"];
        $postdata["scheduled_start_time"] = "11:00 AM";
        $postdata["scheduled_end_time"] = "02:00 PM";
        $postdata['skip_account_shift_overlap'] = true;
        $actual_rows = [];
        $postdata['actual_rows'] = $actual_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);

        $postdata2['id'] = 180;
        $postdata2["status"] = 5;
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        return $this->assertTrue($test_status);
    }
}
