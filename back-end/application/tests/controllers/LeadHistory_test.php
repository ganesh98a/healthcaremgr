<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller LeadHistory and related models of it
 */
class LeadHistory_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../../modules/sales/models/Lead_model');
        $this->CI->load->model('../../modules/sales/models/Leadhistory_model');
        $this->CI->load->library('form_validation');
        $this->Lead_model = $this->CI->Lead_model;
    }

    /*
     * checking the member unavailability of correct start date and end date range
     */
    function test_create_history_on_lead_creation() {
        $phone = new stdClass();
        $phone->phone = '0120248343457';
        $email = new stdClass();
        $email->email = rand().'@example.com';
        $lead_owner = new stdClass();
        $lead_owner->label = "Super Admin";
        $lead_owner->value = 11;
        $postdata = [
            'PhoneInput' => [
               $phone
            ],
            'EmailInput' => [
                $email
            ],
        'lead_owner' => $lead_owner,
        'lead_topic' => 'test lead',
        'firstname' => 'Pramod',
        'lastname' => 'Kumar',
        'lead_company' =>  'test company',
        'lead_description' => 'test description'
        ];
        $req = new stdClass();
        $req->data = $postdata;
        $output = $this->Lead_model->create_lead($req, 11);
        return $this->assertTrue($output['status']);
    }
}