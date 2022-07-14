<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * class is used for controlling HCM API
 */
class Api extends MX_Controller {

    private $function_calls = array("create_lead", "view_applicant");
    use formCustomValidation;

    /**
     * contructor
     */
    function __construct() {
        parent::__construct();
        // load model
        $this->load->model('All_module_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->load->model('../../sales/models/Lead_model');
        $this->load->model('recruitment/Recruitment_applicant_model');
        $this->form_validation->CI = & $this;
    }

    /**
     * validates the submitted api data
     */
    private function validate_submitted_data($reqData) {
        $val_rules = array(
            array('field' => 'username', 'label' => 'username', 'rules' => 'required'),
            array('field' => 'password', 'label' => 'password', 'rules' => 'required'),
            array('field' => 'apikey', 'label' => 'api', 'rules' => 'required|callback_validate_hcm_api_credentials'),
            array('field' => 'function', 'label' => 'function', 'rules' => 'required'),
        );

        $this->form_validation->CI = & $this;
        $this->form_validation->set_rules($val_rules);
        $this->form_validation->set_data((array) $reqData);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $errors;
        }
    }

    /**
     * while using the HCM api, we need to validate submitted api function
     */
    public function validate_hcm_api_function($apifunction) {
        $this->form_validation->set_message('validate_hcm_api_function', "Invalid username or password");
            return false;
    }

    /**
     * while using the HCM api, we need to validate submitted api key
     */
    public function validate_hcm_api_credentials($reqData) {
        $request_body = file_get_contents('php://input');
        $reqData = (array) json_decode($request_body);

        if(!empty($reqData['username']) && !empty($reqData['password']) && ($this->username != $reqData['username'] || $this->password != $reqData['password'])) {
            $this->form_validation->set_message('validate_hcm_api_credentials', "Invalid username or password");
            return false;
        }
        if(!empty($reqData['apikey']) && $this->apikey != $reqData['apikey']) {
            $this->form_validation->set_message('validate_hcm_api_credentials', "Invalid api key");
            return false;
        }
        if(!empty($reqData['function']) && array_search($reqData['function'],$this->function_calls) === FALSE) {
            $this->form_validation->set_message('validate_hcm_api_credentials', "Invalid api function name");
            return false;
        }
        return true;
    }

    /**
     * api function that inserts lead
     */
    private function api_create_lead($reqData, $adminId) {
        $sub_data_object = $reqData;

        $result = $this->basic_model->get_row('references', array('id'), ["key_name" => "oncall_website_contact"]);
        if(!isset($result) || !isset($result->id))
        $sub_data_object->data->lead_source_code = 412;
        else
        $sub_data_object->data->lead_source_code = $result->id;

        # setting email & phone specified into a special array/object combination that is required by model
        if(isset($sub_data_object->data->email)) {
            $email_obj = new stdClass();
            $email_obj->email = $sub_data_object->data->email;
            $sub_data_object->data->EmailInput[] = $email_obj;
        }
        else {
            $sub_data_object->data->EmailInput = [];
        }
        if(isset($sub_data_object->data->phone)) {
            $phone_obj = new stdClass();
            $phone_obj->phone = $sub_data_object->data->phone;
            $sub_data_object->data->PhoneInput[] = $phone_obj;
        }
        else {
            $sub_data_object->data->PhoneInput = [];
        }

        # calling a lead model function to validate and create the lead
        $response = $this->Lead_model->create_lead($sub_data_object, $adminId);
        return $response;
    }

    /**
     * api function to view the list of applicants & applications
     */
    private function api_view_applicant($reqData, $adminId) {
        $response = $this->Recruitment_applicant_model->get_api_applications($reqData->filter_data, $adminId);
        return $response;
    }
    /**
     * main controller function that handles the api calls
     */
    public function callapi() {

        $request_body = file_get_contents('php://input');
        $reqData = json_decode($request_body);

        if(!isset($reqData) || empty($reqData)) {
            echo json_encode(["status" => false, "error" => "No data provided"]);
            exit();
        }
        
        # api credentials need to be validated
        $validation_failed = $this->validate_submitted_data((array) $reqData);
        
        # if there are validation errors in each row processing
        if($validation_failed) {
            echo json_encode(["status" => false, "error" => $validation_failed]);
            exit();
        }
        
        $result = $this->basic_model->get_row('member', array('id'), ["is_super_admin" => 1]);
        if(!isset($result) || !isset($result->id)) {
            echo json_encode(["status" => false, "error" => "No default user found"]);
            exit();
        }
        $adminId = $result->id;

        # based on the api function, using appropriate function to perform the action
        if($reqData->function == "create_lead")
            $response = $this->api_create_lead($reqData, $adminId);
        else if($reqData->function == "view_applicant")
            $response = $this->api_view_applicant($reqData, $adminId);

        echo json_encode($response);
        exit();
    }
}
