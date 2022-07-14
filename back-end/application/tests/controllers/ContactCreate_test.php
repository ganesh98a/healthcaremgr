<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller ContactCreate and related models of it
 */
class ContactCreate_test extends TestCase {

    protected $CI;

    public $postdata = [
        "firstname" => "Testcase Check Insert",
        "lastname" => "Test last name",
        "contact_type_option" => [],
        "stateList" => [],
        "address" => "Teston Close, Whittlesea VIC 3757, Australia",
        "source_option" => [],
        "aboriginal" => 2,
        "communication_method" => 2,
        "contact_type" => 1,
        "contact_source" => NULL,
        "validation_calls" => 1,
        "contactId" => NULL,
        "date_of_birth" => "2020-10-06 00:00:00",
        "religion" => NULL,
        "cultural_practices" => NULL,
        "status" => 1,
        "ndis_number" => 322323232
    ];

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/Contact_model');
        $this->CI->load->model('../models/Basic_model');
        $this->CI->load->library('form_validation');
        $this->Contact_model = $this->CI->Contact_model;
        $this->Basic_model = $this->CI->Basic_model;
        $emailObj = new stdClass();
        $emailObj->email = "test_".rand(1, 9999).date("Ymdhis")."@yopmail.com";
        $this->postdata["EmailInput"] = [$emailObj];
    }

    /*
     * Checking the contact Creation of success
     */
    function test_create_update_contact_val1() {
        $postdata = $this->postdata;

        $obj = new stdClass;
        $emailobj = new stdClass;

        $obj->phone = 123456789;
        $emailobj->email = "hcmtest@hcm.com";
        //Fetch the Gender Options
        $details = $this->Contact_model->get_gender_option();

        $gender = NULL;

        if(count($details) > 0) {
            //Store only first value for testing
            $gender = $details[0]['value'];
        }

        $postdata["account_person"] = $obj;
        $postdata["like_selection"] = $emailobj;
        $postdata["gender"] = $gender;

        $output = $this->Contact_model->create_update_contact($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to Insert contact'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'contact Insert successfully'];
        }
        return $this->assertTrue($status['status']);
    }

    /*
     * Checking the contact update of success
     */
    function test_create_update_contact_val2() {
        $postdata = $this->postdata;

        $obj = new stdClass;
        $emailobj = new stdClass;

        $obj->phone = 123456789;
        $emailobj->email = "hcmtest@hcm.com";

        //Fetch the Gender Options
        $details = $this->Contact_model->get_gender_option();

        $gender = NULL;

        if(count($details) > 0) {
            //Store only first value for testing
            $gender = $details[0]['value'];
        }

        $postdata["firstname"] = "Testcase Check Update";
        $postdata["account_person"] = $obj;
        $postdata["like_selection"] = $emailobj;
        $postdata["gender"] = $gender;

        //Get the last inserted record for updating data
        $details = $this->Basic_model->get_row('person', array("MAX(id) AS last_per_id"));

        if($details->last_per_id)
            $postdata['contactId'] = $details->last_per_id;

        $output = $this->Contact_model->create_update_contact($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to update Contact'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Contact updated successfully'];
        }

        return $this->assertTrue($status['status']);
    }

    /*
     * Checking the contact update of success for interpreter
     */
    function test_create_update_contact_for_interpreter_val2() {
        $postdata = $this->postdata;

        $obj = new stdClass;
        $emailobj = new stdClass;

        $obj->phone = 123456789;
        $emailobj->email = "hcmtest@hcm.com";

        //Fetch the Gender Options
        $details = $this->Contact_model->get_gender_option();

        $gender = NULL;
        $interpreter = 2;

        if(count($details) > 0) {
            //Store only first value for testing
            $gender = $details[0]['value'];
        }

        $postdata["firstname"] = "Testcase Check Update";
        $postdata["account_person"] = $obj;
        $postdata["like_selection"] = $emailobj;
        $postdata["gender"] = $gender;
        $postdata["interpreter"] = $interpreter;

        //Get the last inserted record for updating data
        $details = $this->Basic_model->get_row('person', array("MAX(id) AS last_per_id"));

        if($details->last_per_id)
            $postdata['contactId'] = $details->last_per_id;

        $output = $this->Contact_model->create_update_contact($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to update Contact'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Contact updated successfully'];
        }

        return $this->assertTrue($status['status']);
    }


    /*
     * Checking the contact create of success for shift
     */
    function test_create_contact_for_shift_as_agent() {
        $postdata = $this->postdata;
        $account_person = '{"label":"Test Ac","value":"1","account_type":"2","is_site":"0"}';
        $postdata['account_person'] = json_decode($account_person);

        $obj = new stdClass;
        $emailobj = new stdClass;

        $obj->phone = 123456789;
        $emailobj->email = "hcmtest@hcm.com";

        //Fetch the Gender Options
        $details = $this->Contact_model->get_gender_option();

        $gender = NULL;
        $interpreter = 2;

        if(count($details) > 0) {
            //Store only first value for testing
            $gender = $details[0]['value'];
        }

        $postdata["firstname"] = "Testcase Check Update";
        $postdata["account_person"] = $obj;
        $postdata["like_selection"] = $emailobj;
        $postdata["gender"] = $gender;
        $postdata["interpreter"] = $interpreter;

        //Get the last inserted record for updating data
        $details = $this->Basic_model->get_row('person', array("MAX(id) AS last_per_id"));

        if($details->last_per_id)
            $postdata['contactId'] = $details->last_per_id;

        $output = $this->Contact_model->create_update_contact($postdata, 1);

        $status = ['status'=> FALSE, 'msg'=>'Failed to update Contact'];

        if($output) {
            $status = ['status'=> TRUE, 'msg'=>'Contact updated successfully'];
        }

        return $this->assertTrue($status['status']);
    }
}
