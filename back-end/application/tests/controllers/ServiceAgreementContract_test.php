<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller ServiceAgreementContract and related models of it
 */

require_once './_ci_phpunit_test/autoloader.php';

use PHPUnit\Framework\TestCase;

class ServiceAgreementContract_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/sales/models/ServiceAgreementContract_model');
        $this->CI->load->model('../modules/sales/models/ServiceAgreement_model');
        $this->CI->load->library('form_validation');
        // load docusing evelop
        $this->CI->load->library('DocuSignEnvelope');
        $this->ServiceAgreement_model = $this->CI->ServiceAgreement_model;
        $this->ServiceAgreementContract_model = $this->CI->ServiceAgreementContract_model;
    }

    /*
     * Save service agreement contract
     */
    function test_save_add_newdocusign_case1(){
        $reqData = '{
            "adminId": 20,
            "type":2,
            "to":"21",
            "to_select": {"label":"GK Test","value":"21"},
            "service_agreement_type":"3",
            "related":"Service Agreement for test sa 1",
            "service_agreement_id":"23",
            "account_id":"6",
            "account_type":"1",
            "opporunity_id":"4",
            "signed_by":"21",
            "subject":"testse",
            "email_content":"Please DocuSign the Service Agreement.",
            "cc_email_flag":0,
            "cc_email":"",
            "completed_email_content":"All parties have completed the Service Agreement."
        }';

        $reqData = json_decode($reqData);
        $data = (array) ($reqData);
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $sa_action = 'create';
            $dataObj = (array) $reqData;

            $validation_rules = [
                array('field' => 'service_agreement_id', 'label' => 'Service Agreement Id', 'rules' => "required", 'errors' => [
                    'required' => "The Service Agreement Id is required"
                ]),
                array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Id is required"
                ]),
                array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Type is required"
                ]),
                array('field' => 'to', 'label' => 'To', 'rules' => 'required'),
                array('field' => 'signed_by', 'label' => 'Signed By', 'rules' => 'required'),
                array('field' => 'subject', 'label' => 'Email Subject', 'rules' => 'required'),
                array('field' => 'email_content', 'label' => 'Email Content', 'rules' => 'required'),
            ];

            if (isset($data['type']) && $data['type'] == 2) {
                $validation_rules[] = array('field' => 'service_agreement_type', 'label' => 'Service Agreement template type', 'rules' => 'required'); 
            }

            $this->CI->form_validation->set_data($data);
            $this->CI->form_validation->set_rules($validation_rules);

            if ($this->CI->form_validation->run()) {
                $sa_newdocusign_id = $this->ServiceAgreement_model->save_add_newdocusign($data, $adminId, $sa_action);
                if ($sa_newdocusign_id) {
                    // generate contract 1 Consent / 2 _ Service Agreement
                    if ($data['type'] == 1) {
                        // to do
                    } else {
                        // Private Travel SA
                        if ($data['service_agreement_type'] == 3) {
                            // function call extends ServiceAgreementContract Controller
                            // $this->generate_private_travel_agreement_contract($sa_newdocusign_id);
                        }
                    }
                    $return = ['status'=>true, 'msg'=>'New Docu Sign is Added successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
            } else {
                $errors = $this->CI->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
              
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
         $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

    /*
     * Save service agreement contract -  case 2
     */
    function test_save_add_newdocusign_case2(){
        $reqData = '{
            "adminId": 20,
            "type":2,
            "to":"",
            "to_select": {"label":"GK Test","value":"21"},
            "service_agreement_type":"3",
            "related":"Service Agreement for test sa 1",
            "service_agreement_id":"23",
            "account_id":"6",
            "account_type":"1",
            "opporunity_id":"4",
            "signed_by":"21",
            "subject":"testse",
            "email_content":"Please DocuSign the Service Agreement.",
            "cc_email_flag":0,
            "cc_email":"",
            "completed_email_content":"All parties have completed the Service Agreement."
        }';

        $reqData = json_decode($reqData);
        $data = (array) ($reqData);
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $sa_action = 'create';
            $dataObj = (array) $reqData;

            $validation_rules = [
                array('field' => 'service_agreement_id', 'label' => 'Service Agreement Id', 'rules' => "required", 'errors' => [
                    'required' => "The Service Agreement Id is required"
                ]),
                array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Id is required"
                ]),
                array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Type is required"
                ]),
                array('field' => 'to', 'label' => 'To', 'rules' => 'required'),
                array('field' => 'signed_by', 'label' => 'Signed By', 'rules' => 'required'),
                array('field' => 'subject', 'label' => 'Email Subject', 'rules' => 'required'),
                array('field' => 'email_content', 'label' => 'Email Content', 'rules' => 'required'),
            ];

            if (isset($data['type']) && $data['type'] == 2) {
                $validation_rules[] = array('field' => 'service_agreement_type', 'label' => 'Service Agreement template type', 'rules' => 'required'); 
            }

            $this->CI->form_validation->set_data($data);
            $this->CI->form_validation->set_rules($validation_rules);

            if ($this->CI->form_validation->run()) {
                $sa_newdocusign_id = $this->ServiceAgreement_model->save_add_newdocusign($data, $adminId, $sa_action);
                if ($sa_newdocusign_id) {
                    // generate contract 1 Consent / 2 _ Service Agreement
                    if ($data['type'] == 1) {
                        // to do
                    } else {
                        // Private Travel SA
                        if ($data['service_agreement_type'] == 3) {
                            // function call extends ServiceAgreementContract Controller
                            $this->generate_private_travel_agreement_contract($sa_newdocusign_id);
                        }
                    }
                    $return = ['status'=>true, 'msg'=>'New Docu Sign is Added successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
            } else {
                $errors = $this->CI->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
              
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
         $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(false, $status, $status_msg);
    }

    /*
     * Generate service agreement - private travel contract and envelop - case 1
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function test_generate_pt_contract_case1() {

        // service agreement id
        $service_agreement_attachment_id = 100;

        $serviceAgreementContract = $this->ServiceAgreement_model->get_sa_contract_details($service_agreement_attachment_id);
        // return status with false if service agreement not exist
        if (isset($serviceAgreementContract) == false && !isset($serviceAgreementContract['id']) ==false) {
            return [ "status" => false, "error" => "Service Agreement contract not exist" ];
        }

        // gather details
        $service_agreement_id = $serviceAgreementContract['service_agreement_id'];

        // get email details
        $email = $this->ServiceAgreement_model->get_service_agreement_attachment_email($service_agreement_attachment_id);

        // get dynamic data
        $data = $this->ServiceAgreementContract_model->get_dynamic_data_for_contract($service_agreement_id, $serviceAgreementContract, $service_agreement_attachment_id);

        // Get attachment details
        $to_name = $serviceAgreementContract['to_name'];
        $to_email = $serviceAgreementContract['to_email'];
        $attachment_type = $serviceAgreementContract['contract_type'];

        $docusignResponse = array(
            "envelope_id" => "e65c7b47-b240-4283-be37-a64b943d437c",
            "statusDateTime" => "2019-09-12T09:59:02.5338440Z",
            "uri" => "/envelopes/e65c7b47-b240-4283-be37-a64b943d437c",
            "status" => 1,
        );

        if ($docusignResponse['status'] == 1) {
            $statusDoc = true;
        } else {
            $statusDoc = false;
        }

        $this->assertEquals(true, $statusDoc);
    }

    /*
     * Checking service agreement contract with multiple cc - success case
     */
    function test_save_add_newdocusign_cc_case1(){
        $this->CI->form_validation->reset_validation();
        $reqData = '{
            "adminId": 20,
            "type":2,
            "to":"21",
            "to_select": {"label":"GK Test","value":"21"},
            "service_agreement_type":"3",
            "related":"Service Agreement for test sa 1",
            "service_agreement_id":"23",
            "account_id":"6",
            "account_type":"1",
            "opporunity_id":"4",
            "signed_by":"21",
            "subject":"testse",
            "email_content":"Please DocuSign the Service Agreement.",
            "cc_email_flag":1,
            "cc_email": ["test@yopmail.com","test1@yopmail.com"],
            "completed_email_content":"All parties have completed the Service Agreement."
        }';

        $reqData = json_decode($reqData);
        $data = (array) ($reqData);
        $adminId = $reqData->adminId;
        if (!empty($reqData)) {
            $sa_action = 'create';
            $dataObj = (array) $reqData;

            $validation_rules = [
                array('field' => 'service_agreement_id', 'label' => 'Service Agreement Id', 'rules' => "required", 'errors' => [
                    'required' => "The Service Agreement Id is required"
                ]),
                array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Id is required"
                ]),
                array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required', 'errors' => [
                    'required' => "The Account Type is required"
                ]),
                array('field' => 'to', 'label' => 'To', 'rules' => 'required'),
                array('field' => 'signed_by', 'label' => 'Signed By', 'rules' => 'required'),
                array('field' => 'subject', 'label' => 'Email Subject', 'rules' => 'required'),
                array('field' => 'email_content', 'label' => 'Email Content', 'rules' => 'required'),
            ];

            if (isset($data['type']) && $data['type'] == 2) {
                $validation_rules[] = array('field' => 'service_agreement_type', 'label' => 'Service Agreement template type', 'rules' => 'required'); 
            }

            $this->CI->form_validation->set_data($data);
            $this->CI->form_validation->set_rules($validation_rules);

            if ($this->CI->form_validation->run()) {
                $sa_newdocusign_id = $this->ServiceAgreement_model->save_add_newdocusign($data, $adminId, $sa_action);
                if ($sa_newdocusign_id) {
                    // generate contract 1 Consent / 2 _ Service Agreement
                    if ($data['type'] == 1) {
                        // to do
                    } else {
                        // Private Travel SA
                        if ($data['service_agreement_type'] == 3) {
                            // function call extends ServiceAgreementContract Controller
                            // $this->generate_private_travel_agreement_contract($sa_newdocusign_id);
                        }
                    }
                    $return = ['status'=>true, 'msg'=>'New Docu Sign is Added successfully'];
                } else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
            } else {
                $errors = $this->CI->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
              
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
         $status = $return['status'];
        // Get msg if true else error
        if ($status) {
            $status_msg = $return['msg'];
        } else {
            $status_msg = $return['error'];
        }
        
        // AssertsEquals true with response if false show the error msg 
        $this->assertEquals(true, $status, $status_msg);
    }

}