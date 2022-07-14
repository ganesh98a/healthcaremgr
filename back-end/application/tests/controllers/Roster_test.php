<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller MemberDashboard and related models of it
 */
class Roster_test extends TestCase {
    
    protected $CI;
    public $postdata = [
        "roster_no" => null,
        "person_id" => 2,
        "owner_id" => 1,
        "account_type" => 1,
        "account_id" => 2,
        "contact_id" => 2,
        "role_id" => 1,
        "start_date" => "2021-04-29", # has to be Monday (1 week before timings_test.php)
        "end_date" => "2021-04-4",
    ];

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/schedule/models/Roster_model');
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->library('form_validation');
        $this->Roster_model = $this->CI->Roster_model;
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /*
     * For getting reference id of create roster
     * return type json
     */
    function get_create_reference_id() {
        // get reference id 
        $rows = $this->Roster_model->get_reference_id();

        if ($rows) {
            $previousRID = $rows[0]['roster_no'];
            // split the id as prefix and value
            $splitPos = 2;
            $prefix = substr($previousRID, 0, $splitPos);
            $value = substr($previousRID, $splitPos);
            $incValue = intVal($value) + 1;
             // Add  a preceeding 0 in a 8 digit
            $strPadDigits = 8;
            $str = 0;
            $incValueWPad = str_pad($incValue, $strPadDigits, $str, STR_PAD_LEFT);
            // Join two variable
            $reference_id = $prefix.$incValueWPad;
        } else {
            $reference_id = 'RT00000001';
        }

        return $reference_id;
    }

    /**
     * Create or Update roster success case
     */
    function test_create_update_roster() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "roster_no":"RT00000006",
                "owner_id":"160",
                "account_type":"1",
                "account_id":"1",
                "contact_id":"8",
                "start_date":"2021-03-29",
                "end_date":"2021-04-04",
                "funding_type":"585",
                "roster_type":"582",
                "end_date_option":2
            },
            "adminId": 20
        }';
        $reqData = json_decode($reqData, true);
        $data = [];
        # Get roster number 
        $roster_no = $this->get_create_reference_id();
        $reqData = (object) $reqData;
        if (!empty($reqData->data)) {
            $reqData->data = (object) $reqData->data;
            $reqData->data->roster_no = $roster_no;
            
            $result = $this->Roster_model->create_update_roster($reqData);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->assertTrue($result['status']);
    }

    /**
     * Get contact list for accounts | particpiant success case
     */
    public function test_get_contact_for_account_roster() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "account":{
                    "label":"test role one",
                    "value":"1",
                    "account_type":"1",
                    "is_site":"0"
                }
            }
        }
        ';
        $reqData = json_decode($reqData, true);
        $data = [];

        $reqData = (object) $reqData;

        if (!empty($reqData->data)) {
            $reqData->data = (object) $reqData->data;
            $reqData->data->account = (object) $reqData->data->account;
            $result = $this->Roster_model->get_contact_for_account_roster($reqData->data);

            # Get primary contact
            $contact_id = $this->Roster_model->get_primary_contact($reqData->data);

            $result['primary_contact'] = $contact_id;

        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->assertTrue($result['status']);
    }

    /**
     * Retrieve roster details. - success
     */
    function tes_get_roster_detail_by_id() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "roster_id": 1,
            "page_name": "roster"
        }
        ';
        $reqData = json_decode($reqData, true);
        $data = [];

        $reqData = (object) $reqData;

        if (empty($reqData->roster_id)) {
            $result = ['status' => false, 'error' => "Missing ID"];
        }
        $roster_id = $data->roster_id;

        $details = $this->basic_model->get_row('roster', array("MAX(id) AS last_roster_id"));
        if($details->last_roster_id)
        $roster_id = $details->last_roster_id;

        # Get roster details        
        $result = $this->Roster_model->get_roster_details($roster_id);

        return $this->assertTrue($result['status']);
    }

    /**
     * Get roster id list - success
     */
    function test_get_roster_option() {
        $adminId = '20';
        // Set data in libray for validation
        $reqData = '
        {
            "data":{
                "account":{
                    "label":"test role one",
                    "value":"1",
                    "account_type":"1",
                    "is_site":"0"
                }
            }
        }
        ';
        $reqData = json_decode($reqData, true);

        $reqData = (object) $reqData;
        if (!empty($reqData->data)) {
            $reqData->data = (object) $reqData->data;
            $reqData->data->account = (object) $reqData->data->account;
            $result = $this->Schedule_model->get_roster_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }

        return $this->assertTrue($result['status']);
    }
}