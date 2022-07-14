<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller ProcessBuilder and related models of it
 */
class ProcessBuilder_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../../modules/admin/models/Process_management_model');
        $this->CI->load->library('form_validation');
        $this->Process_management_model = $this->CI->Process_management_model;
    }    

    function test_create_update_event_with_template() {
        $data = '{"criteria": "no_criteria",
        "description": "wewewe",
        "event_action": "send_email",
        "event_trigger": "record_created",
        "id": 0,
        "name": "dfdfdf",
        "object_name": "applicant",
        "recipient": "applicant",
        "show_template_list": true,
        "email_template":1
    }';
        $postdata = json_decode($data);
        $output = $this->Process_management_model->create_update_event($postdata, 11);
        return $this->assertTrue($output>0);
    }

    function test_create_update_event_with_conditions() {
        $data = '{"id":0,"email_template":"22","event_trigger":"record_created","object_name":"GroupBooking","criteria":"with_conditions","event_action":"send_email","recipient":"[{\"label\":\"Applicant\",\"value\":\"Applicant\"}]","conditions":{"0":{"field":"GroupBooking.invite_type","values":[{"label":"Quiz","value":1},{"label":"Meeting Invite","value":2}]},"1":{"field":"GroupBooking.interview_stage_status","values":[{"label":"Open","value":0},{"label":"Scheduled","value":1},{"label":"In progress","value":2},{"label":"Successful","value":3}]}},"condition_logic":"","recipient_type":"GroupBooking.Users","name":"test11","description":"testdesc","expression_inputs":{"expression":{"conditions":[{"isGroup":false,"field":"","operator":"equals","value":1},{"isGroup":false,"field":"","operator":"equals","value":1}],"triggerType":"all"},"inputs":[{"field":"GroupBooking.invite_type","values":[{"label":"Quiz","value":1},{"label":"Meeting Invite","value":2}]},{"field":"GroupBooking.interview_stage_status","values":[{"label":"Open","value":0},{"label":"Scheduled","value":1},{"label":"In progress","value":2},{"label":"Successful","value":3}]}]},"expression":{"conditions":[{"isGroup":false,"field":"","operator":"equals","value":1},{"isGroup":false,"field":"","operator":"equals","value":1}],"triggerType":"all"},"template_label":"Applicant Portal Login"}';
        $postdata = json_decode($data);
        $output = $this->Process_management_model->create_update_event($postdata, 11);
        return $this->assertTrue($output>0);
    }

    function test_create_update_event_no_template() {
        $data = '{"criteria": "no_criteria",
        "description": "wewewe",
        "event_action": "send_email",
        "event_trigger": "record_created",
        "id": 0,
        "name": "dfdfdf",
        "object_name": "applicant",
        "recipient": "applicant",
        "show_template_list": true}';
        $postdata = json_decode($data);
        $output = $this->Process_management_model->create_update_event($postdata, 11);
        return $this->assertFalse($output->status);
    }
}