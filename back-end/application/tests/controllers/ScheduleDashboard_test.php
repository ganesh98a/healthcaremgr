<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDashboard and related models of it
 */
class ScheduleDashboard_test extends TestCase {
    
    protected $CI;
    
    public $postdata;

    public $member_id_to_assign = 12;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->library('form_validation');
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
        
        $person_detail = $this->basic_model->get_row('person', array("MAX(id) AS person_id"));
        
        $this->postdata = [
            "shift_no" => null,
            "person_id" => 3,
            "owner_id" => 1,
            "account_type" => 1,
            "account_id" => 2,
            "contact_id" => $person_detail->person_id,
            "role_id" => 1,
            "scheduled_start_date" => "2021-06-01", # has to be Monday (1 week before timings_test.php)
            "scheduled_end_date" => "2021-06-01",
            "scheduled_start_time" => "01:00 AM",
            "scheduled_end_time" => "07:00 AM",
            "scheduled_paid_break" => null,
            "scheduled_travel" => null,
            "scheduled_unpaid_break" => null,
            "actual_start_date" => "2021-06-01",  # has to be Monday (1 week before timings_test.php)
            "actual_end_date" => "2021-06-01",
            "actual_start_time" => "01:00 AM",
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
    }

    /**
     * check next shift no is retrived correctly
     */
    function test_next_shift_no() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        return $this->assertTrue($output['status']);
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
        $postdata['id'] = 193;
        $postdata['shift_no'] = "ST000000193";
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '-1 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 AM";
        $postdata["actual_end_time"] = "02:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $scheduled_rows = [];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $postdata['actual_rows'] = $scheduled_rows;
        $output1 = $this->Schedule_model->create_update_shift($postdata, 1);
        
        $shift_with_member = $this->basic_model->get_row('shift_member', array("MAX(shift_id) AS shift_id"), array("status" => 1));

        $postdata2['id'] = $shift_with_member->shift_id;
        $postdata2["status"] = 5;
        
        $output2 = $this->Schedule_model->update_shift_status($postdata2, 1);

        $test_status = ($output1['status'] == true && $output2['status'] == true);
        
        return $this->assertTrue($test_status);
    }

    /*
     * checking the successful insertion of shift without the owner-id
     */
    function test_create_update_shift_nowner_insert() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata['owner_id'] = null;
        $postdata['skip_account_shift_overlap'] = true;

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
    * checking the schedule timings overlapping check while adding a single shift
    */
    function test_create_shift_overlap_check1() {

        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata['owner_id'] = null;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['account_shift_overlap']);
    }

    /*
     * checking the validation failure in copying of shifts
     */
    function test_copy_shift_val1() {
        $postdata['shifts'] = null;
        $postdata['weeks_list_selected'] = [
            date("Y-m-d", strtotime('monday next week', strtotime($this->postdata["scheduled_start_date"])))
        ];

        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the validation failure in copying of shifts
     */
    function test_copy_shift_val2() {
        $postdata['shifts'] = [198];
        $postdata['weeks_list_selected'] = null;

        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking the validation failure in copying of shifts
     */
    function test_copy_shift_val3() {
        $postdata['shifts'] = [198,197,196,195,194,193];
        $next_monday = date("Y-m-d", strtotime('monday next week', strtotime($this->postdata["scheduled_start_date"])));
        $postdata['weeks_list_selected'] = [$next_monday];

        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
    * checking the successful copying of shifts single week
    */
    function test_copy_shift_success1() {

        # removing copy/cloned/repeated shifts as it conflicts with copying shift
        // $this->basic_model->delete_records('shift', 'id > 198');

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shifts'] = [$details->last_shift_id];
        $postdata['skip_account_shift_overlap'] = true;
        
        $next_monday = date("Y-m-d", strtotime('monday next week', strtotime($this->postdata["scheduled_start_date"])));
        $postdata['weeks_list_selected'] = [$next_monday];
        
        
        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
    * checking the schedule timings overlapping check while copying of shifts single week
    */
    function test_copy_shift_overlap_check1() {

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shifts'] = [$details->last_shift_id];

        $next_monday = date("Y-m-d", strtotime('monday next week', strtotime($this->postdata["scheduled_start_date"])));
        $postdata['weeks_list_selected'] = [$next_monday];
        
        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        return $this->assertTrue($output['account_shift_overlap']);

    }

    /*
     * checking the successful copying of shifts multiple weeks
     */
    function test_copy_shift_success2() {

        # removing copy/cloned/repeated shifts as it conflicts with copying shift
        // $this->basic_model->delete_records('shift', 'id > 198');

        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shifts'] = [$details->last_shift_id];

        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $next_monday = date("Y-m-d", strtotime('monday next week', strtotime($this->postdata["scheduled_start_date"])));
        $next_next_monday = date("Y-m-d", strtotime('monday next week', strtotime($next_monday)));
        $postdata['weeks_list_selected'] = [$next_monday, $next_next_monday];
        $postdata['skip_account_shift_overlap'] = true;

        $output = $this->Schedule_model->copy_shift_weekly_intervals_wrapper($postdata, 1);
        
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful insertion of shift
     */
    function test_create_update_shift_insert() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $postdata["actual_start_date"] = date('Y-m-d', strtotime($postdata["actual_start_date"] . '+1 day'));
        $postdata["actual_end_date"] = $postdata["actual_start_date"];
        $postdata["actual_start_time"] = "08:00 AM";
        $postdata["actual_end_time"] = "02:00 PM";
        $postdata["scheduled_start_date"] = $postdata["actual_start_date"];
        $postdata["scheduled_end_date"] = $postdata["actual_end_date"];
        $postdata["scheduled_start_time"] = $postdata["actual_start_time"];
        $postdata["scheduled_end_time"] = $postdata["actual_end_time"];
        $postdata['skip_account_shift_overlap'] = true;

        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful repeating of shift for tomorrow
     */
    function test_repeat_shift_success1() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];
        
        # repeating tomorrow
        $postdata['repeat_option'] = 1;
        $postdata['skip_account_shift_overlap'] = true;

        $output = $this->Schedule_model->create_update_shift_repeat_wrapper($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
    * checking the schedule timings overlapping check while copying of shifts single week
    */
    function test_repeat_shift_overlap_check1() {
        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];
        
        # repeating tomorrow
        $postdata['repeat_option'] = 1;

        $output = $this->Schedule_model->create_update_shift_repeat_wrapper($postdata, 1);
        return $this->assertTrue($output['account_shift_overlap']);
    }

    /*
     * checking the successful repeating of shift for rest of week
     */
    function test_repeat_shift_success2() {

        # removing copy/cloned/repeated shifts as it conflicts with repeating shift
        // $this->basic_model->delete_records('shift', 'id > 198');

        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];
        
        # repeating for rest of the week
        $postdata['repeat_option'] = 2;
        $postdata['skip_account_shift_overlap'] = true;

        $output = $this->Schedule_model->create_update_shift_repeat_wrapper($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful repeating of shift on specific day
     */
    function test_repeat_shift_success3() {

        # removing copy/cloned/repeated shifts as it conflicts with repeating shift
        // $this->basic_model->delete_records('shift', 'id > 198');

        $postdata = $this->postdata;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];
        
        # repeating for rest of the week
        $postdata['repeat_option'] = 3;
        $postdata['skip_account_shift_overlap'] = true;
        $postdata['repeat_days_selected'] = [
            date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '-1 day'))
        ];

        $output = $this->Schedule_model->create_update_shift_repeat_wrapper($postdata, 1);
        return $this->assertTrue($output['status']);
    }
    
    /*
     * checking the successful insertion of shift with scheduled and actual breaks
     */
    function test_create_update_shift_break_insert() {

        # removing copy/cloned/repeated shifts as it conflicts with repeating shift
        // $this->basic_model->delete_records('shift', 'id > 198');
        
        $postdata = $this->postdata;
        $postdata['skip_account_shift_overlap'] = true;
        $postdata['account_type'] = 2;
        $postdata['account_id'] = 1;
        $output = $this->Schedule_model->get_next_shift_no();
        if($output['data'])
        $postdata['shift_no'] = $output['data'];

        $so_ref_id = $p_ref_id = $up_ref_id = null;
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($details)
            $so_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "paid", "archive" => 0]);
        if ($details)
            $p_ref_id = $details->id;
        
        $details = $this->basic_model->get_row('references', ['id'], ["key_name" => "unpaid", "archive" => 0]);
        if ($details)
            $up_ref_id = $details->id;

        $postdata['scheduled_start_time'] = "10:00 PM";
        $postdata['scheduled_end_time'] = "11:00 AM";
        $postdata['scheduled_end_date'] = date('Y-m-d', strtotime($postdata["scheduled_start_date"] . '+1 day'));

        $scheduled_rows = [
            ["id" => null,
            "break_type" => $so_ref_id,
            "break_start_time" => "11:00 PM",
            "break_end_time" => "07:00 AM",
            "break_duration" => null,
            "duration_disabled" => true,
            "timing_disabled" => false
            ],
            ["id" => null,
            "break_type" => $up_ref_id,
            "break_start_time" => null,
            "break_end_time" => null,
            "break_duration" => "00:30",
            "duration_disabled" => false,
            "timing_disabled" => true
            ],
        ];
        $postdata['scheduled_rows'] = $scheduled_rows;
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful status updating to cancel of shift with notes and reason id
     */
    function test_shift_status_update() {
        $postdata = $this->postdata;
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['id'] = $details->last_shift_id;

        $details = $this->basic_model->get_row('shift', array("shift_no"), ["id" => $postdata['id']]);
        if($details->shift_no)
        $postdata['shift_no'] = $details->shift_no;

        $cancel_reasons = $this->Schedule_model->get_shift_cancel_reason_option();
        if(!empty($cancel_reasons) && isset($cancel_reasons[0]) && isset($cancel_reasons[0]->value))
        $postdata["cancel_reason_id"] = $cancel_reasons[0]->value;
        
        $postdata["status"] = 6;
        $postdata["cancel_notes"] = "test notes";

        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the successful updating of shift
     */
    function test_create_update_shift_update() {
        $postdata = $this->postdata;
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['id'] = $details->last_shift_id;

        $details = $this->basic_model->get_row('shift', array("shift_no"), ["id" => $postdata['id']]);
        if($details->shift_no)
        $postdata['shift_no'] = $details->shift_no;
        
        $postdata["actual_end_time"] = "08:00 AM";
        $postdata['skip_account_shift_overlap'] = true;
        
        $output = $this->Schedule_model->create_update_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the failure in updating status to "scheduled" as there are no registered members assigned
     */
    function test_shift_status_update_failure1() {
        $postdata = $this->postdata;
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['id'] = $details->last_shift_id;
        $postdata["status"] = 3;

        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating the failure on adding shift members
     */
    function test_assign_shift_members_val1() {
        $postdata = null;
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        # no member assignment

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating the success on adding shift members
     */
    function test_assign_shift_members_success() {
        $postdata = null;
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $newobj = new StdClass;
        $newobj->id = $this->member_id_to_assign;
        $newobj->selected = 1;
        $postdata['shift_members'][] = $newobj;

        $output = $this->Schedule_model->assign_shift_members($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * checking the success in updating status to "scheduled" as there is one registered member assigned
     */
    function test_shift_status_update_success1() {
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['id'] = $details->last_shift_id;
        $postdata["status"] = 3;
        $output = $this->Schedule_model->update_shift_status($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * testing the failure in archiving of the shift member
     */
    function test_archive_shift_member_val1() {
        $postdata = [
            "id" => null
        ];
        $output = $this->Schedule_model->archive_shift_member($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the correct archiving of the shift member assignment
     */
    function test_archive_shift_member_success() {

        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;

        $output = $this->Schedule_model->archive_shift_member($postdata, 1);
        return $this->assertContains("Successfully",$output['msg']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val1() {

        # re-archive the earlier member as we want to test the accept/reject
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
            $this->basic_model->update_records("shift_member", ["archive" => 0], ["id" => $details->last_shift_member_id]);

        $postdata = [
            "id" => 0
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val2() {
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;

        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val3() {
        
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $postdata['member_id'] = null;

        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val4() {
        
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $postdata['member_id'] = $this->member_id_to_assign;
        $postdata['status'] = 4; # not acceptable

        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * rejecting shift with success
     */
    function test_accept_reject_shift_success1() {
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $postdata['member_id'] = $this->member_id_to_assign;
        $postdata['status'] = 2;
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * accepting shift with success
     */
    function test_accept_reject_shift_success2() {
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $postdata['member_id'] = $this->member_id_to_assign;
        $postdata['status'] = 1;
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * accepting already accepted shift
     */
    function test_accept_reject_shift_failure1() {
        $postdata["shift_id"] = null;
        $details = $this->basic_model->get_row('shift_member', array("MAX(id) AS last_shift_member_id"));
        if($details->last_shift_member_id)
        $postdata['id'] = $details->last_shift_member_id;
        
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['shift_id'] = $details->last_shift_id;

        $postdata['member_id'] = $this->member_id_to_assign;
        $postdata['status'] = 1;
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertContains("Shift is already accepted",$output['error']);
    }

    /*
     * checking assigned member getting de-assigned after shift status turning from scheduled to
     * published
     */
    function test_shift_status_update_success2() {
        $details = $this->basic_model->get_row('shift', array("MAX(id) AS last_shift_id"));
        if($details->last_shift_id)
        $postdata['id'] = $details->last_shift_id; //shift is in scheduled

        $postdata["status"] = 2; // putting it to published
        $output = $this->Schedule_model->update_shift_status($postdata, 1);

        $details = $this->Schedule_model->get_shift_details($postdata['id']);
        $success = false;
        if(empty($details) || !isset($details['data']))
        return $success;
        
        $shift_details = $details['data'];
        if($shift_details->status == 2 && empty($shift_details->accepted_shift_member_id))
        $success = true;
        
        return $this->assertTrue($success);
    }
}
