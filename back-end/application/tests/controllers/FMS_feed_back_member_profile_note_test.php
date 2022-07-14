<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDocument and related models of it
 */
class FMS_feed_back_member_profile_note_test extends TestCase {
    // Defualt contruct function
    protected $CI;
    public function setUp() {
        $this->CI = &get_instance();
        // Load MemberDocument_model
        $this->CI->load->model('../modules/fms/models/Fms_model');
        $this->basic_model = $this->CI->basic_model;

    }
    /*
     * get feedback member profile note list 
     *
     */
    function test_get_member_profile_note_feedback_list_case1() {
        $adminId = 20;

        $details = $this->basic_model->get_row('member', array("MAX(id) AS last_id"), ["archive" => 0]);

        $reqData = [
            "member_id" => $details->last_id
        ];
        $response = $this->CI->Fms_model->get_member_profile_note_feedback_list($reqData);
        return $this->assertTrue($response['status']);
    }

}