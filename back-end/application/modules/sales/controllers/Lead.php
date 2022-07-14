<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property-read \Lead_model $Lead_model
 * @property-read \UserName $username
 */
class Lead extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->loges->setLogType('crm');
        $this->load->model('Lead_model');
        $this->load->helper('message');
        $this->load->model('../../common/models/List_view_controls_model');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 3485, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    function get_owner_staff() {
        $reqData = request_handler();
        $ownerName = $reqData->data->query ?? '';
        $rows = $this->Lead_model->get_owner_staff_by_name($ownerName);
        echo json_encode($rows);
    }

    function get_lead_source() {
        $reqData = request_handler();
        $rows = $this->Lead_model->get_lead_source();

        $selectOption = $reqData->data->select_option ?? 0;
        $rowsData = [];
        if ($selectOption == 1) {
            $rowsData[] = ['label' => 'Select Lead Source', 'value' => ''];
        }
        $rowsData = array_merge($rowsData, $rows);
        echo json_encode($rowsData);
    }

    function check_user_emailaddress_already_exist($id = 0) {
        $email = ($this->input->get()) ? $this->input->get() : '';
        if (empty($email)) {
            echo 'true';
        }
        $email = is_array($email) ? current($email) : $email;
        $res = $this->Lead_model->check_person_duplicate_email($email, $id);
        echo!empty($res) ? 'false' : 'true';
    }

    /**
     * Callback validation to check if email is already taken by another lead. 
     * 
     * If `$person_id` is provided, the person ID's email will not be 
     * considered as 'already taken'
     * 
     * Tip: The prefixed undescore is just a little trick to disallow 
     * access to this action and to make this method callable 
     * by Form_validation library
     * 
     * @param string $email 
     * @param int $person_id 
     * @return bool 
     */
    public function _check_lead_person_duplicate_email($email, $person_id = 0) {
        if (empty($email)) {
            return true;
        }

        $found = $this->Lead_model->check_person_duplicate_email($email, $person_id);
        if (empty($found)) {
            return true;
        }

        $this->form_validation->set_message(__FUNCTION__, sprintf("The email %s is already taken by another lead", $email));
        return false;
    }

    function create_lead() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);
        $response = $this->Lead_model->create_lead($reqData, $adminId);
        echo json_encode($response);
        exit();
    }

    /**
     * Update existing lead
     * 
     * @return \CI_Output 
     */
    public function update_lead() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;

        $this->output->set_content_type('json');



        // early exit if empty
        if (empty($reqData->data)) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'msg' => system_msgs('something_went_wrong')
            ]));
        }


        $data = obj_to_arr($reqData->data);

        $lead_statuses = $this->db->get_where('tbl_lead_status', ['archive' => 0])->result_array();
        $lead_status_ids = array_values(array_column($lead_statuses, 'id'));

        // rules
        $validation_rules = [    
            [
                'field' => 'lastname',
                'label' => 'Last Name',
                'rules' => 'required|max_length[40]',
                'errors' => [
                    'max_length' => "%s field cannot exceed 40 characters."
                ]
            ],

            [
                'field' => 'lead_status',
                'label' => 'Status',
                'rules' => [
                    'required',
                    'trim', 'in_list[' . implode(',', $lead_status_ids) . ']'
                ],
                'errors' => [
                    'in_list' => 'The status you submitted is no longer in our system'
                ],
            ],
            [
                'field' => 'lead_topic',
                'label' => 'Topic',
                'rules' => [
                    'required'
                ]                
            ],
        ];
        $chk_email = '';
        $chk_phone = '';
        foreach ($data['EmailInput'] as $val) {
            $val = (object) $val;
            $chk_email = $val->email;                
        }
        foreach ($data['PhoneInput'] as $val) {
            $val = (object) $val;
            $chk_phone = $val->phone;                
        }
        
        if (empty($chk_email) && empty($chk_phone) ) {
            return $this->output->set_output(json_encode([
                'status' => false,
                'error' => 'Phone or Email is required',
        ]));       
        }

        if(!empty($chk_email)){
        if (!empty($data['EmailInput'])) {
            foreach ($data['EmailInput'] as $i => $EmailInput) {
                $validation_rules[] = [
                    'field' => "EmailInput[$i][email]",
                    'label' => 'Email',
//                    'rules' => 'callback__check_lead_person_duplicate_email[' . $EmailInput['person_id'] . ']', // The double __ is intentional, not a typo!
                    'rules' => 'required|valid_email', // The double __ is intentional, not a typo!
                ];
                break; // ATM We're currently expecting only 1 email 
            }
        }
    }

        if (!empty($data['PhoneInput'])) {
            foreach ($data['PhoneInput'] as $i => $PhoneInput) {
                $validation_rules[] = [
                    'field' => "PhoneInput[$i][phone]",
                    'label' => 'Phone',
                    'rules' => 'callback_phone_number_check[phone,,Please enter valid phone number.]'
                ];
                break; // atm we're currently accept 1 phone
            }
        }

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules($validation_rules);

        // early exit if validation fails
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => implode(', ', $errors),
            ]));
        }

        $check_service_type = $this->Lead_model->check_service_type_exist_ref('lead_service_type',$data['lead_topic']);
        if (!$check_service_type) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => 'Please select a service type',
            ]));
        }
        // update existing lead
        $result = $this->Lead_model->update_lead($data['id'], $data, $adminId);

        // if something's wrong, early exit and send error
        if (!$result['status']) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ]));
        }

        // Log this event
        $lead_id = $result['lead_id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully updated lead with ID of %s by %s", $lead_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully updated lead with ID of %s by %s", $lead_id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($lead_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog(); // create log

        return $this->output->set_output(json_encode([
                    'status' => true,
                    'msg' => 'Lead details successfully updated',
        ]));
    }

    /**
     * Retrieve lead details. Common use-case is editing existing lead.
     * 
     * `POST: /sales/Leads/get_lead_details`
     * 
     * @return CI_Output 
     */
    public function get_lead_details() {
        $reqData = request_handler('access_crm');
        $data = $reqData->data;
        $this->output->set_content_type('json');

        if (empty($data->id)) {
            return $this->output->set_output(json_decode([
                        'status' => false,
                        'error' => 'Missing ID'
            ]));
        }

        $result = $this->Lead_model->get_lead_details($data->id);
        
        
        

        if (!$result) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => 'Lead does not exist anymore',
            ]));
        }
        
        //pass key name for type which type option need
        $this->load->model("Common/common_model");
        $result["unqualified_reason_option"] = $this->common_model->get_central_reference_data_option("unqualified_reason_lead");
        
        if($result['lead_status_key_name'] === "unqualified"){
            $result["unqualified_reason_det"] = $this->Lead_model->get_unqualified_reason_of_nots($data->id);
        }
        
        return $this->output->set_output(json_encode([
                    'status' => true,
                    'data' => $result,
        ]));
    }

    
    /**
     * Mark lead as archived. Also mark related person as archived.
     * 
     * Archived leads will be excluded in the list of leads.
     * 
     * @todo: If you require destroying the whole lead, not just soft-deleting it, 
     * you have to look for another function or implement your own!
     */
    public function archive_lead() {
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;

        $this->output->set_content_type('json');

        // check if lead exist
        $result = $this->Lead_model->get_lead_details($data['id']);
        if (empty($result)) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => 'Lead does not exist anymore. Please refresh your page',
            ]));
        }

        $result = $this->Lead_model->archive_lead($data['id']);

        if (empty($result) || !$result['status']) {
            return $this->output->set_output(json_encode([
                        'status' => false,
                        'error' => $result['error'] ?? system_msgs('something_went_wrong'),
            ]));
        }

        // when you are are this point, the above code has ran successfully
        // Log
        $lead_id = $result['id'];

        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully archived lead with ID of %s by %s", $lead_id, $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully archived lead with ID of %s by %s", $lead_id, $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($lead_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        return $this->output->set_output(json_encode([
                    'status' => true,
                    'msg' => "Lead successfully archived"
        ]));
    }

    function get_lead_status() {
        $reqData = request_handler();
        $rows = $this->Lead_model->get_lead_status();
        $selectOption = $reqData->data->select_option_type ?? 0;
        $rowsData = [];
        $selectOptionData = ['all' => 'All', '1' => 'Select Lead Status'];
        if (isset($selectOptionData[$selectOption])) {
            $rowsData[] = ['label' => $selectOptionData[$selectOption], 'value' => ''];
        }
        $rowsData = array_merge($rowsData, $rows);
        echo json_encode($rowsData);
    }

    function get_leads_list() {
        $reqData = request_handler('access_crm');
        if (!empty($reqData->data)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            $filter_condition = str_replace(['lead_status'], ['lead_status_val'], $filter_condition);
            $result = $this->Lead_model->get_leads_list($reqData->data, $filter_condition);
            echo json_encode($result);
            exit();
        }
    }
    
    /*
     * its use to check created contact email address already exist
     * 
     * @params $lead_id
     * 
     * #Error: error will set default library of CI
     * return type boolean
     */
    function check_created_contact_email_address_already_exist($def , $data){
        $data = obj_to_arr(json_decode($data));
        $lead_id = $data["lead_id"] ?? 0;
        
        $lead_details = $this->Lead_model->get_lead_details($lead_id);
        $this->load->model("Contact_model");
        
        if(!empty($lead_details["emails"])){
            foreach($lead_details["emails"] as $val){
                if (!empty($val['email'])) {
                 
                    $res = $this->Contact_model->check_email_address_is_uniqe_of_contact($val['email'], 0);
                   
                    if(!empty($res)){
                        $msg = $val['email'] . " email address is already exist to another contact '".$res->contact_name."'";
                        $this->form_validation->set_message('check_created_contact_email_address_already_exist', $msg);
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /*
     * its use for check account name already exist
     * 
     * @params $account_name
     * 
     * return type boolean
     * true / false
     */

    function check_organisation_name_should_be_uniqe($account_name) {
        if ($account_name) {
            $res = $this->Lead_model->check_organisation_name_should_be_uniqe($account_name);

            if (!empty($res)) {
                $this->form_validation->set_message('check_organisation_name_should_be_uniqe', 'This Account (Organisation) name already exist');
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_organisation_name_should_be_uniqe', 'Account name is required');
            return false;
        }
    }

    function check_existing_org_and_existing_are_already_associated($x, $data) {
        $data = obj_to_arr(json_decode($data));

        if ($data["exixting_org"]["type"] == 2) {
            $org_id = $data["exixting_org"]["value"];
            $contactId = $data["exixting_contact"]["value"];

            $where = ["source_data_id" => $org_id, "source_data_type" => 2, "destination_data_id" => $contactId, "destination_data_type" => 1];
            $res = $this->basic_model->get_row("sales_relation", ["id"], $where);

            if (empty($res)) {
                $this->form_validation->set_message('check_existing_org_and_existing_are_already_associated', 'Contact is not associated with selected Organisation');
                return false;
            }
        }

        if ($data["exixting_org"]["type"] == 1) {
            if ($data["exixting_org"]["value"] == $data["exixting_contact"]["value"]) {
                $this->form_validation->set_message('check_existing_org_and_existing_are_already_associated', 'Select existing account and existing contact should not be same');
                return false;
            }
        }
        return true;
    }

    /*
     * its use for handle convert lead request and response
     * and convert lead to organisation, opportunity and contact
     * 
     * return type json
     */

    function convert_lead() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);


        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (!empty($reqData->data)) {
            $data = obj_to_arr($reqData->data);
                      
            if ($data["account_type"] == 1 && !empty($data["ndis_number"]) &&(!empty($data["person_account"]) || !empty($data["contact_is_account"])))
            {
                $validation_rules[] = array('field' => 'ndis_number', 'label' => 'NDIS number', 'rules' => 'trim|callback__check_ndis_number_already_been_used[0]');


            }
            if ($data["account_type"] == 1) {
                if (empty($data["person_account"]) && empty($data["contact_is_account"])) {
                    $validation_rules[] = array('field' => 'org_name', 'label' => 'org name', 'rules' => 'callback_check_organisation_name_should_be_uniqe');
                } else {
                    $validation_rules[] = array('field' => 'org_name', 'label' => 'org name', 'rules' => 'required');
                }
            } else {
                $data["exixting_org_id"] = $data["exixting_org"]["value"] ?? '';
                $validation_rules[] = array('field' => 'exixting_org_id', 'label' => 'existing org', 'rules' => 'required');
            }

            if ($data["contact_type"] == 1) {
                if (!empty($data["person_account"]) && !empty($data["contact_is_account"])) {
//                    $validation_rules[] = array('field' => 'contact_name', 'label' => 'contact name', 'rules' => 'required');
                } else {
                    $validation_rules[] = array('field' => 'contact_name', 'label' => 'contact name', 'rules' => 'required');
                }
            } else {
                 $data["exixting_contact_id"] = $data["exixting_contact"]["value"] ?? '';
                  if (empty($data["contact_is_account"]))
                  {
                    
                    $validation_rules[] = array('field' => 'exixting_contact_id', 'label' => 'existing contact', 'rules' => 'required');

                  }
            }

            if ($data["opportunity_type"] == 1) {
                $validation_rules[] = array('field' => 'opportunity_name', 'label' => 'opportunity name', 'rules' => 'required');
            } else {
                $data["exixting_opportunity_id"] = $data["exixting_opportunity"]["value"] ?? '';
                $validation_rules[] = array('field' => 'exixting_opportunity_id', 'label' => 'existing opportunity', 'rules' => 'required');
            }

            // check selected existing account (organisation) and selected existing contact both are associated 
            if ($data["account_type"] == 2 && $data["contact_type"] == 2) {
                $validation_rules[] = array('field' => 'check_spacial', 'label' => 'contact name', 'rules' => 'callback_check_existing_org_and_existing_are_already_associated[' . json_encode($data) . ']');
            }
            
            $validation_rules[] = array('field' => 'lead_id', 'label' => 'contact name', 'rules' => 'callback_check_lead_is_already_converted[' . json_encode($data) . ']');
            
            // check during copy lead to contact email address is already exist or not if lead email aleady eixst
            // if email address already exist then stop to convert lead with error 
            if ($data["contact_type"] == 1) {
                $validation_rules[] = array('field' => 'contact_email_check', 'label' => 'lead_id', 'rules' => 'callback_check_created_contact_email_address_already_exist[' . json_encode($data) . ']');
            }

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                // call model for convert lead
                $lead_number = $this->Lead_model->convert_lead($data, $adminId);

                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $adminId);

                $this->loges->setTitle(sprintf("Lead converted " . $lead_number . ' by admin ' . $adminName));
                $this->loges->setDescription(json_encode($data));
                $this->loges->setUserId($data['lead_id']);
                $this->loges->setCreatedBy($adminId);
                $this->loges->createLog(); // create log


                $response = ['status' => true, 'msg' => "Lead has been converted successfully"];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }

        echo json_encode($response);
        exit();
    }

    /*
     * its use for check lead is already converted or lead id valid or not
     * 
     * @params $lead_id
     * 
     * @error error set in default libary of CI
     * return type boolean
     */

    function check_lead_is_already_converted($lead_id) {
        if ($lead_id) {
            $res = $this->Lead_model->check_lead_is_already_converted($lead_id);

            if (!$res["status"]) {
                $this->form_validation->set_message('check_lead_is_already_converted', $res["error"]);
                return false;
            }
        } else {
            $this->form_validation->set_message('check_lead_is_already_converted', 'Lead id is required');
            return false;
        }

        return true;
    }
    
    /*
     * its use for get organisation and contact name as option on search select box
     * return type json
     * 
     * return ["status" => true, 'data' => $rows]
     */
    function get_account_name_and_contact_name_search_option() {
        $reqData = request_handler();

        if ($reqData->data->search) {
            $rows = $this->Lead_model->get_account_name_and_contact_name_search_option($reqData->data);
            $res = ["status" => true, 'data' => $rows];
        } else {
            $res = ["status" => true, 'data' => []];
        }

        echo json_encode($res);
    }
    
    /*
     * its use for get contact name as option on search select box
     * return type json
     * 
     * return object ["status" => true, 'data' => []]
     */
    function get_contact_name_search_option() {
        $reqData = request_handler();

        if ($reqData->data->search) {
            $rows = $this->Lead_model->get_contact_name_search_option($reqData->data);
            $res = ["status" => true, 'data' => $rows];
        } else {
            $res = ["status" => true, 'data' => []];
        }

        echo json_encode($res);
    }
    
    /*
     * its use for get opportunity name as option on search in select
     *  
     * return type json
     * return object ["status" => true, 'data' => $rows]
     */
    function get_opportunity_name_search_option() {
        $reqData = request_handler();

        if ($reqData->data->search) {
            $rows = $this->Lead_model->get_opportunity_name_search_option($reqData->data);
            $res = ["status" => true, 'data' => $rows];
        } else {
            $res = ["status" => true, 'data' => []];
        }

        echo json_encode($res);
    }
    
    /*
     * its use for update lead status
     * handle request from fron-end for update status request
     * 
     * return type json
     * return object status: true
     */
    function update_status_lead() {
        $reqData = request_handler('access_crm');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($adminId);

        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];

        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;

            $validation_rules = [
                array('field' => 'lead_id', 'label' => 'lead id', 'rules' => "required"),
                array('field' => 'status', 'label' => 'status', 'rules' => 'required'),
            ];

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $res = $this->Lead_model->update_status_lead($data, $adminId);

                $response = $res;
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        echo json_encode($response);
        exit();
    }
    function get_convertlead_related_contemail() {
        $reqData = request_handler();
        // echo '<pre>';print_r($reqData->data->email);die;
        if ($reqData->data->email) {
            $rows = $this->Lead_model->get_convertlead_related_contemail($reqData->data->email);
            $res = ["status" => true, 'data' => $rows];
        } else {
            $res = ["status" => false, 'data' => []];
        }
        echo json_encode($res);
    }

    /**
     * Check if NDIS number was already been used by another active account
     * @param string $value 
     * @param int $dont_validate_this_person_id
     * @return bool 
     */
    public function _check_ndis_number_already_been_used($value, $dont_validate_this_person_id = 0)
    {
        $ndis = preg_replace("/\s+/", '', $value);
        if (!$ndis) {
            return true;
        }

        $STATUS_ACTIVE = 1;

        $query = $this->db
                ->from('tbl_person AS p')
                ->where([
                    'p.ndis_number' => $ndis,
                    'p.status' => $STATUS_ACTIVE,
                    'p.archive' => 0,
                ])
                ->where_not_in('p.id', [$dont_validate_this_person_id])
                ->select(['p.*'])
                ->get();
        
        $numRows = $query->num_rows();
        if ($numRows > 0) {
            $this->form_validation->set_message(__FUNCTION__, sprintf("The NDIS number '%s' was already been taken by another active contact", $value));
            return false;
        }

        return true;
    }

    public function get_lead_service_type_ref_list() {
        $reqData = request_handler();
        $rows = $this->Lead_model->get_lead_service_type_ref_list('lead_service_type');
        echo json_encode($rows);
    }

}
