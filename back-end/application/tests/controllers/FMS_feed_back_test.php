<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDocument and related models of it
 */
class FMS_feed_back_test extends TestCase {
    // Defualt contruct function
    protected $CI;
    public function setUp() {
        $this->CI = &get_instance();
        // Load MemberDocument_model
        $this->CI->load->model('../modules/fms/models/Fms_model');
        $this->basic_model = $this->CI->basic_model;

    }

    /*
     * Create Fms Feed
     *
     */
    function test_create_fms_feed_case1() {
        $adminId = 20;
        $intcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "init_hcm_general", "archive" => 0]);
        $agcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "aga_hcm_general", "archive" => 0]);
        $feedcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "currently_works", "archive" => 0]);
        $departoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "head_office", "archive" => 0]);

        $details = $this->basic_model->get_row('fms_feedback', array("MAX(id) AS last_id"));
        $no = $details->last_id + 1;
        $feedback_id = "FK".sprintf("%09d", $no);

        $reqData = [
            "feedback_id" => $feedback_id,
            "alertType" => 1,
            "feedbackType" => '',
            "address" => 'Rundle Street, Adelaide SA 5000, Australia',
            "AgainstCategory" => $agcatoption->id,
            "DepartmentDetails" =>$departoption->id,
            "FeedCategory" => $feedcatoption->id,
            "InitiatorCategory" => $intcatoption->id,
            "agCatOption" => "aga_member_of_public",
            "agEmail" => "xxx@gmail.com",
            "agFirstName" => "12",
            "agPhone" => "1234566889",
            "description" => "Desc",
            "event_date" => "2021-04-06 19:18:40",
            "initCatOption" => "init_hcm_general",
            "initFirstName" => "xxxx",
            "initLasttName" => "yyyy",
            "assignedTo" => ['value' => $adminId],
            "created" => DATE_TIME,
            "created_by" => $adminId
        ];

        $response = $this->CI->Fms_model->create_update_feed($reqData, $adminId);

        // AssertsEquals true with response if false show the error msg
        return $this->assertTrue($response['status']);
    }

    /*
     * Update Fms Feed
     *
     */
    function test_update_fms_feed_case2() {
        $adminId = 20;
        $intcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "init_hcm_general", "archive" => 0]);
        $agcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "aga_hcm_general", "archive" => 0]);
        $feedcatoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "currently_works", "archive" => 0]);
        $departoption = $this->basic_model->get_row('references', ['id'], ["key_name" => "head_office", "archive" => 0]);

        $details = $this->basic_model->get_row('fms_feedback', array("MAX(id) AS last_id"));

        $reqData = [
            "id" => $details->last_id,
            "alertType" => 1,
            "feedbackType" => '',
            "address" => 'Rundle Street, Adelaide SA 5000, Australia',
            "AgainstCategory" => $agcatoption->id,
            "DepartmentDetails" =>$departoption->id,
            "FeedCategory" => $feedcatoption->id,
            "InitiatorCategory" => $intcatoption->id,
            "agCatOption" => "aga_member_of_public",
            "agEmail" => "xxx@gmail.com",
            "agFirstName" => "Firstname update",
            "agPhone" => "1234566889",
            "description" => "Desc",
            "event_date" => "2021-04-06 19:18:40",
            "initCatOption" => "init_hcm_general",
            "initFirstName" => "xxxx upate",
            "initLasttName" => "yyyy update",
            "assignedTo" => ['value' => $adminId],
            "updated" => DATE_TIME,
            "updated_by" => $adminId
        ];

        $response = $this->CI->Fms_model->create_update_feed($reqData, $adminId);

        // AssertsEquals true with response if false show the error msg
        return $this->assertTrue($response['status']);
    }

     /*
     * Archive Fms Feed
     *
     */
    function test_archive_fms_feed_case3() {
        $adminId = 20;

        $details = $this->basic_model->get_row('fms_feedback', array("MAX(id) AS last_id"), ["archive" => 0]);

        $reqData = [
            "id" => $details->last_id
        ];
        $response = $this->CI->Fms_model->fms_archive_feedback($reqData,  $adminId);
        return $this->assertTrue($response['status']);
    }

}