<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once APPPATH . 'helpers/web_recruit_helper.php';

/*
 * Class: RecruitmentWeb_test
 * Uses : TDD - Controller
 */


class RecruitmentWeb_test extends TestCase {
    // Defualt contruct function
    protected $CI;  
    public function setUp() {   
        $this->CI = &get_instance();
        // Load CRM_RiskAssessment_model
        $this->CI->load->model('../modules/recruitment/models/Recruitment_member_model');
        $this->CI->load->library('form_validation');
        $this->CI->load->model('../modules/member/models/Member_model');
        $this->CI->load->model('schedule/Schedule_model');
        $this->Member_model = $this->CI->Member_model;
        $this->Recruitment_member_model = $this->CI->Recruitment_member_model;
        $this->Schedule_model = $this->CI->Schedule_model;
    }

    /*
     * To  get applicant by member positive case if greater than 0 positive case
     * Using - assertGreaterThanOrEqualget_applicant_member_details_by_id
     */
    public function testGetApplicantMemberDetailsByIdPositiveCase() {
        // Request data
        $reqData = '
        {
            "applicant_id": 102
        }';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData)) {
            // Call model for applicant member details
            $result = $this->CI->Recruitment_member_model->get_applicant_member_details_by_id($reqData->applicant_id);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $data = array();
            $response = array('status' => false, 'error' => 'Applicant member data is null', 'data' => $data); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Successfully Retrived';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * To get applicant by member failure case  if equals 0 failure case     * 
     *  Using - assertEquals* 
     * Case - applicant_id null
     */
    public function testGetApplicantMemberDetailsByIdFailureCase() {
        // Request data
        $applicant_id = '';
        $reqData = '
        {
                "applicant_id": "'.$applicant_id.'"
        }';
        // Json decode
        $reqData = json_decode($reqData, true);
        // Convert array to object
        $reqData = (object) $reqData;

        if (!empty($reqData)) {
            // Call model for get applicant member details
            $result = $this->CI->Recruitment_member_model->get_applicant_member_details_by_id($reqData->applicant_id);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $data = array();
            $response = array('status' => false, 'error' => 'Applicant member is null', 'data' => $data); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Success';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * checking the member unavailability of correct start date and end date range
     */
    function test_create_update_member_unavailability_val1() {
        $postdata = [
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-09-01",
            "end_date" => "2020-09-01",
            "start_time_id" => "5", // 5 means 06:00
            "end_time_id" => "4", // 4 means 05:00
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * checking if the member unavailability data required and valid fields
     */
    function test_create_update_member_unavailability_val2() {
        $postdata = [
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-asds:00",
            "end_date" => null,
            "start_time_id" => "1",
            "end_time_id" => "4",
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertContains("Incorrect start date",$output['error']);
    }

    /*
     * checking the correct range of start date & time and end date & time
     */
    function test_create_update_member_unavailability_val3() {
        $postdata = [
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-09-01",
            "end_date" => "2020-09-01",
            "start_time_id" => "1",
            "end_time_id" => "1",
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertContains("should be lower",$output['error']);
    }

    /*
     * end time should be provided although it is optional when optional end date is provided
     */
    function test_create_update_member_unavailability_val4() {
        $postdata = [
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-09-01",
            "end_date" => "2020-09-02",
            "start_time_id" => "1",
            "end_time_id" => null,
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the correct insertion of the member unavailability
     */
    function test_create_update_member_unavailability_insert() {
        $postdata = [
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-07-02",
            "end_date" => "2020-07-02",
            "start_time_id" => "1",
            "end_time_id" => "2",
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * testing the correct updating of the member unavailability
     */
    function test_create_update_member_unavailability_update() {
        $postdata = [
            "id" => 1,
            "member_id" => 1,
            "type_id" => null,
            "start_date" => "2020-07-02",
            "end_date" => "2020-07-02",
            "start_time_id" => "1",
            "end_time_id" => "2",
            "archive" => 0,
            "start_time" => "00:00:00",
            "end_time" => "23:59:00"
        ];
        $output = $this->Member_model->create_update_member_unavailability($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * testing the correct archiving of the member unavailability
     */
    function test_create_update_member_unavailability_archive() {
        $postdata = [
            "id" => 1
        ];
        $output = $this->Member_model->archive_member_unavailability($postdata, 1);
        return $this->assertContains("Successfully",$output['msg']);
    }

    /**
     * validating ipad/web portal login failure
     */
    function test_validate_applicant_member_login_failure() {
        $postdata = [
            "email" => "pranavgajjar@gmail.com",
            "device_pin" => 12345678
        ];
        $output = $this->Recruitment_member_model->validate_applicant_member_login($postdata, 1);
        $output = (array) json_decode($output);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating ipad/web portal login success
     */
    function test_validate_applicant_member_login_success() {
        $postdata = [
            "email" => "pranav.gajjar@ampion.com.au",
            "device_pin" => 123456
        ];
        $output = $this->Recruitment_member_model->validate_applicant_member_login($postdata, 1);
        $output = (array) json_decode($output);
        return $this->assertTrue($output['status']);
    }
     /*
     * To  update the applicant quiz status to inprogress
     * Using - assertEqualsid
     */
    public function testUpdateApplicantQuizSubmitStatusPositiveCase() {
        // Request data
        $reqData = '
        {
            "applicant_id": 86,
            "task_applicant_id": 259

        }';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData)) {
            // Call model for update quiz status
            $result = $this->CI->Recruitment_member_model->update_applicant_quiz_submit_status($reqData->task_applicant_id, 3);
            $response = ["status" => true, "data" => $result];
        } else {
            // If requested data is empty or null
            $response = array('status' => false, 'error' => 'Quiz status updated', 'data' => []); 
        }
        $status = $response['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = 'Successfully Retrived';
        } else {
            $status_msg = 'Failed';
        }

        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val1() {
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
        $postdata = [
            "id" => 61,
            "shift_id" => null,
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val3() {
        $postdata = [
            "id" => 61,
            "shift_id" => 2,
            "member_id" => null,
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * testing the validation failure in accepting / rejecting shift
     */
    function test_accept_reject_shift_val4() {
        $postdata = [
            "id" => 61,
            "shift_id" => 2,
            "member_id" => 19,
            "status" => 4
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /*
     * accepting shift that has start time passed
     */
    function test_accept_reject_shift_failure2() {
        $postdata = [
            "id" => 64,
            "shift_id" => 34,
            "member_id" => 23,
            "status" => 1
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertContains("shift has already passed",$output['error']);
    }

    /*
     * rejecting shift with success
     */
    function test_accept_reject_shift_success1() {
        $postdata = [
            "id" => 30,
            "shift_id" => 24,
            "member_id" => 23,
            "status" => 2
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /*
     * accepting shift with success
     */
    function test_accept_reject_shift_success2() {
        $postdata = [
            "id" => 87,
            "shift_id" => 35,
            "member_id" => 23,
            "status" => 1
        ];
        $output = $this->Schedule_model->accept_reject_shift($postdata, 1);
        return $this->assertTrue($output['status']);
    }
}
