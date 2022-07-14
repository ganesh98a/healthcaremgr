<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDashboard and related models of it
 */
class MemberDashboard_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('member/Member_model');
        $this->CI->load->model('Basic_model');
        $this->CI->load->library('form_validation');
        $this->Member_model = $this->CI->Member_model;
        $this->Basic_model = $this->CI->Basic_model;
    }

    /*
     * checking the member unavailability of correct start date and end date range
     */
    function test_create_update_member_unavailability_val1() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-01",
            "start_time" => "06:00 AM",
            "end_time" => "05:00 AM",
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking if the member unavailability data required and valid fields
     */
    function test_create_update_member_unavailability_val2() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2020-asds:00",
            "end_date" => null,
            "start_time" => "02:00 AM",
            "end_time" => "05:00 AM",
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertContains("Incorrect Start date",$output['error']);
    }

    /*
     * checking the correct range of start date & time and end date & time
     */
    function test_create_update_member_unavailability_val3() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-01",
            "start_time" => "02:00 AM",
            "end_time" => "02:00 AM",
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertContains("should be lower",$output['error']);
    }

    /*
     * end time should be provided although it is optional when optional end date is provided
     */
    function test_create_update_member_unavailability_val4() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-02",
            "start_time" => "02:00 AM",
            "end_time" => null,
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * start date & end date of unavailability should not overlap with accepted shifts
     */
    function test_create_update_member_unavailability_val5() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-03-03",
            "end_date" => null,
            "start_time" => "12:00 AM",
            "end_time" => null,
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the correct insertion of the member unavailability
     */
    function test_create_update_member_unavailability_insert() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-02",
            "start_time" => "12:00 AM",
            "end_time" => "12:00 AM",
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * start date & end date of unavailability should not overlap with existing ones
     */
    function test_create_update_member_unavailability_val6() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-02",
            "start_time" => "12:00 AM",
            "end_time" => "12:00 AM",
            "archive" => 0
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the correct updating of the member unavailability
     */
    function test_create_update_member_unavailability_update() {
        $postdata = [
            "member_id" => 23,
            "type_id" => null,
            "start_date" => "2021-09-01",
            "end_date" => "2021-09-02",
            "start_time" => "12:00 AM",
            "end_time" => "12:00 AM",
            "archive" => 0
        ];
        $details = $this->Basic_model->get_row('member_unavailability', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * testing the correct archiving of the member unavailability
     */
    function test_create_update_member_unavailability_archive() {
        $details = $this->Basic_model->get_row('member_unavailability', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        $output = $this->Member_model->archive_member_unavailability($postdata, 1);
        return $this->assertContains("Successfully",$output['msg']);
    }
}
