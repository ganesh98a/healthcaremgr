<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberCreate and related models of it
 */
class LeadCreate_test extends TestCase {

    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Lead_model');
        $this->CI->load->model('../models/Basic_model');
        $this->CI->load->library('form_validation');
        $this->Lead_model = $this->CI->Lead_model;
        $this->Basic_model = $this->CI->Basic_model;
    }

    
    /*
     * Checking the member Creation of success
     */
    function test_create_update_lead() {
        $id = 200;
        $postdata = [];
        $emailObj = new stdClass();
        $emailObj->email = "selvaunit@yopmail.com";
        $phoneObj = new stdClass();
        $phoneObj->phone = "9677850152";
        $postdata = [
            "EmailInput" => [$emailObj],
            "PhoneInput" => [$phoneObj],
            "firstname" => "Selva",
            "lastname" => "Ramasamy",
            "lead_topic" => "unit test",
            "lead_owner" => 5,
            "lead_company" => "",
            "lead_source_code" => 3,
            "lead_description" => "Unit Desc",
            "lead_status" => 1,
        ];
        $output = $this->Lead_model->update_lead($id,$postdata, 11);

        $status = ['status'=> FALSE, 'msg'=>'Failed to Update Lead'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Lead Updated successfully'];
        }
        return $this->assertTrue($status['status']);
    }

    /*
     * Checking the successful creation of lead
     */
    function test_create_lead() {
        $id = 200;
        $obj = new stdClass();

        $email_obj = new stdClass();
        $email_obj->email = "pranav.gajjar@ampion.com.au";
        $phone_obj = new stdClass();
        $phone_obj->phone = "0404040404";

        $postdata = [
            "EmailInput" => [$email_obj],
            "PhoneInput" => [$phone_obj],
            "firstname" => "Pranav",
            "lastname" => "Gajjar",
            "lead_topic" => "lead from UT",
            "lead_owner" => 1,
            "lead_company" => "",
            "lead_source_code" => 381,
            "lead_description" => "Unit Desc",
            "lead_status" => 1,
        ];
        $obj->data = $postdata;
        $output = $this->Lead_model->create_lead($obj, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to create Lead'];
        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Lead created successfully'];
        }
        return $this->assertTrue($status['status']);
    }
    

}
