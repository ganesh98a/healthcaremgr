<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberCreate and related models of it
 */
class MemberCreate_test extends TestCase {

    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/member/models/Member_model');
        $this->CI->load->model('../models/Basic_model');
        $this->CI->load->library('form_validation');
        $this->Member_model = $this->CI->Member_model;
        $this->Basic_model = $this->CI->Basic_model;
    }

    /*
     * checking the member decimal value check
     */
    function test_create_update_member_val1() {
        $obj = new stdClass;
        $likeobj = new stdClass;

        $postdata = [
            "fullname" => "Testcase check",
            "account_person" => [
                    $obj->label = "Mathew Wade",
                    $obj->value = 18
            ],
            "hours_per_week" => 46.12,
            "language_selection" => [],
            "transport_selection" => [],
            "like_selection" => [
                $likeobj->id = 1,
                $likeobj->label = "Aircraft Spotting",
            ],
            "status" => 1,
            "max_dis_to_travel" => 45,
            "mem_experience" => 12,
        ];

        $validation_rules = [
            array('field' => 'max_dis_to_travel', 'label' => '
            Max distance to travel (in Kms)', 'rules' => "decimal", 'errors' => [
                'required' => "Max distance to travel (in Kms) is Should be decimal value"
            ]),
            array('field' => 'mem_experience', 'label' => 'Account', 'rules' => 'required', 'errors' => [
                'required' => "Experience (In Years)  is Should be decimal value"
            ]),
        ];

        $this->CI->form_validation->set_data($postdata);
        $this->CI->form_validation->set_rules($validation_rules);

        if (!$this->CI->form_validation->run()) {
            $errors = $this->CI->form_validation->error_array();
            $output = ['status' => false, 'error' => implode(', ', $errors)];
        }
        return $this->assertFalse($output['status']);
    }
    /*
     * Checking the member Creation of success
     */
    function test_create_update_member_val2() {
        $obj = new stdClass;
        $likeobj = new stdClass;

        $obj->label = "Mathew Wade";
        $obj->value = 18;

        $likeobj->id = 1;
        $likeobj->label = "Aircraft Spotting";
        $postdata = [];

        $postdata = [
            "fullname" => "Testcase Check Insert",
            "account_person" => $obj,
            "hours_per_week" => 46.12,
            "language_selection" => [],
            "transport_selection" => [],
            "like_selection" => $likeobj,
            "status" => 1,
            "max_dis_to_travel" => 45.50,
            "mem_experience" => 12.40,
        ];
        $output = $this->Member_model->insert_update_member($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to Insert member'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Member Insert successfully'];
        }
        return $this->assertTrue($status['status']);
    }

    /*
     * Checking the member update of success
     */
    function test_create_update_member_val3() {
        $obj = new stdClass;
        $likeobj = new stdClass;

        $obj->label = "Mathew Wade";
        $obj->value = 18;

        $likeobj->id = 1;
        $likeobj->label = "Aircraft Spotting";
        $postdata = [];
        $postdata = [
            "fullname" => "Testcase Check Update",
            "account_person" => $obj,
            "hours_per_week" => 46.12,
            "language_selection" => [],
            "transport_selection" => [],
            "like_selection" => $likeobj,
            "status" => 1,
            "max_dis_to_travel" => 45.50,
            "mem_experience" => 12.40,
        ];

        //Get the last inserted record for updating data
        $details = $this->Basic_model->get_row('member', array("MAX(id) AS last_mem_id"));

        if($details->last_mem_id)
            $postdata['id'] = $details->last_mem_id;

        $output = $this->Member_model->insert_update_member($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to update member'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Member updated successfully'];
        }

        return $this->assertTrue($status['status']);
    }

}
