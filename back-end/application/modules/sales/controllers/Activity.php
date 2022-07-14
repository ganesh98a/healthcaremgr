<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : Activity
 * use : use for handle activity request and response  
 * 
 * @property-read \Activity_model $Activity_model
 */
class Activity extends MX_Controller {

    // load custon validation traits function
    use formCustomValidation;

    // defualt contruct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');
        $this->load->library('UserName');
        // load contact model 
        $this->load->model('Activity_model');
        $this->load->helper('message');
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

    /*
     * Get contact for send email in activity
     */
    public function get_contact_name_search() {
        $reqData = request_handler();
        if ($reqData->data) {
            // validation rule
            $validation_rules = [
                array('field' => 'sales_type', 'label' => 'Last Name', 'rules' => 'required'),
                array('field' => 'salesId', 'label' => 'street', 'rules' => 'required'),
            ];

            // set data in libray for validate
            $this->form_validation->set_data((array) $reqData->data);

            // set validation rule
            $this->form_validation->set_rules($validation_rules);

            // check data is valid or not
            if ($this->form_validation->run()) {
                $result["contact_option"] = [];
                if(isset($reqData->data->type) && $reqData->data->type == 'own'){
                    if($reqData->data->sales_type != 'lead'){
                        $result["contact_option"] = $this->Activity_model->get_contact_name_search($reqData->data);
                    }
                    if($reqData->data->sales_type == 'lead'){
                        $result["contact_option"] = $this->Activity_model->get_option_of_lead_name($reqData->data);
                    }
                }
                if (isset($reqData->data->type) && $reqData->data->type == 'all') {
                    // $result["contact_option"] = $this->Activity_model->get_all_contact_name_search($reqData->data);
                    $result["contact_option"] = array_merge($this->Activity_model->get_option_of_lead_name_search($reqData->data), $this->Activity_model->get_option_of_contact_name_search($reqData->data));
                }
               
                $response = ["status" => true, "data" => $result];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        } else {
            $response = ["status" => false, "error" => "search key is required"];
        }

        echo json_encode($response);
    }
    
    /*
     * Save & Send Email - Activity 
     */
    function send_new_mail() {
        $reqData = request_handlerFile('access_crm',true,false,true);
        $this->loges->setCreatedBy($reqData->adminId);

        if ($reqData) {
            $current_admin = $reqData->adminId;

            $this->form_validation->set_data((array) $reqData);

            $validation_rules = array(
                array('field' => 'to_user', 'label' => 'To', 'rules' => 'required'),
                array('field' => 'subject', 'label' => 'Subject', 'rules' => 'required'),
                array('field' => 'content', 'label' => 'Content', 'rules' => 'required'),
            );

            // validate the to field if empty throw error msg
            $to_user = json_decode($reqData->to_user);
            if(empty($to_user) == true || count($to_user) == 0){
                $response = ['status'=>false, 'error'=>'Please select To contact'];
                echo json_encode($response);
                exit;
            }

            // update the email
            $update_response = $this->update_and_validate_email($reqData);

            if (isset($update_response) && isset($update_response['to_user'])) {
                $reqData->to_user = json_encode($update_response['to_user']);
            }
            if (isset($update_response) && isset($update_response['cc_user'])) {
                $reqData->cc_user = json_encode($update_response['cc_user']);
            }
            if (isset($update_response) && isset($update_response['bcc_user'])) {
                $reqData->bcc_user = json_encode($update_response['bcc_user']);
            }

            // validate the email
            // $valid_response = $this->validate_email($reqData);

            if ($update_response['status'] == false) {
                $response = ['status'=> $update_response['status'], 'error'=> $update_response['error']];
                echo json_encode($response);
                exit;
            }

            // set rules form validation
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                // Get username
                $this->load->library('UserName');
                $adminName = $this->username->getName('admin', $current_admin);
                // compose new mail
                $this->Activity_model->send_new_mail($reqData, $current_admin, $adminName);

                $response = array('status' => true);
            } else {
                $errors = $this->form_validation->error_array();
                $response = array('status' => false, 'error' => implode(', ', $errors));
            }

            echo json_encode($response);
        }
    }

    /*
     * Validate the email is available or not
     * @param {obj} $reqData
     */
    function validate_email($reqData) {
        $validate_res = [];
        $validate_res['status'] = true;
        $validate_res['error'] = '';
        $email_array = [];
        $to_user = [];
        $cc_user = [];
        $bcc_user = [];
        // convert str to arr
        $to_user = json_decode($reqData->to_user);
        $cc_user = json_decode($reqData->cc_user);
        $bcc_user = json_decode($reqData->bcc_user);
        // merge array
        $email_array = array_merge($to_user, $cc_user);
        $email_array = array_merge($email_array, $bcc_user);
        
        $error_status = true;
        $error_contact = [];
        $error_inc = 0;
        foreach($email_array as $email) {
            if ($email->subTitle == '' || $email->subTitle == null) {
                $error_status = false;
                $error_contact[] = $email->label;
                $error_inc++;
            }
        }
        $str = '';
        if ($error_inc > 1) {
            $str = 's';
        }
        $error_contact = implode(',', $error_contact);
        $validate_res['status'] = $error_status;
        $validate_res['error'] = $error_contact.' - Contact'.$str.' have no email address.';
        return $validate_res;
    }


    /*
     * Update the email is available or not
     * @param {obj} $reqData
     */
    function update_and_validate_email($reqData) {
        $response = [];
        $response['status'] = true;
        $response['error'] = '';
        $email_array = [];
        $to_user = [];
        $cc_user = [];
        $bcc_user = [];
        // convert str to arr
        if (isset($reqData) && isset($reqData->to_user)) {
            $to_user = json_decode($reqData->to_user, true);
        }
        if (isset($reqData) && isset($reqData->cc_user)) {
            $cc_user = json_decode($reqData->cc_user, true);
        }
        if (isset($reqData) && isset($reqData->bcc_user)) {
            $bcc_user = json_decode($reqData->bcc_user, true);
        }        
        // merge array
        $email_array = array_merge($to_user, $cc_user);
        $email_array = array_merge($email_array, $bcc_user);
        
        $error_status = true;
        $error_contact = [];
        $error_inc = 0;
        foreach($email_array as $email) {
            $personId = $email['id'];
            $get_email = [];
            if($email['type']=='contact'){                
                $where = array('person_id' => $personId, 'primary_email' => 1, 'archive' => 0);
                $colown = array('email');
                $get_email = $this->basic_model->get_record_where('person_email', $colown, $where);
            }else{
                $where = array('id' => $personId, 'archive' => 0);
                $colown = array('email');
                $get_email = $this->basic_model->get_record_where('leads', $colown,$where);
            }


            /*
             * update recipient email address by index with array_serach
             * if get_email have email address then the value is updated by index
             * if get_email have no email address then the value is null by index
             */
            $to_found_key = array_search($personId, array_column($to_user, 'id'));
            if(isset($get_email) && isset($get_email[0]) && $to_found_key > -1) {
                $to_user[$to_found_key]['subTitle'] = $get_email[0]->email;
            } 
            if(!isset($get_email) && !isset($get_email[0]) && $to_found_key > -1) {
                $to_user[$to_found_key]['subTitle'] = '';
            }

            $cc_found_key = array_search($personId, array_column($cc_user, 'id'));
            if(isset($get_email) && isset($get_email[0]) && $cc_found_key > -1) {
                $cc_user[$cc_found_key]['subTitle'] = $get_email[0]->email;
            }
            if(!isset($get_email) && !isset($get_email[0]) && $cc_found_key > -1) {
                $cc_user[$cc_found_key]['subTitle'] = '';
            }

            $bcc_found_key = array_search($personId, array_column($bcc_user, 'id'));
            if(isset($get_email) && isset($get_email[0]) && $bcc_found_key > -1) {
                $bcc_user[$bcc_found_key]['subTitle'] = $get_email[0]->email;
            }
            if(!isset($get_email) && !isset($get_email[0]) && $bcc_found_key > -1) {
                $bcc_user[$bcc_found_key]['subTitle'] = '';
            }

            if(isset($get_email) && isset($get_email[0])) {
                $email['subTitle'] = $get_email[0]->email;
            } else {
                $email['subTitle'] = '';
            }

            // to show error msg if email is null 
            if ($email['subTitle'] == '' || $email['subTitle'] == null) {
                $error_status = false;
                $error_contact[$personId] = $email['label'];
                $error_inc++;
            }
        }
        $str = '';
        if ($error_inc > 1) {
            $str = 's';
        }
        $error_contact = implode(',', $error_contact);
        $response['status'] = $error_status;
        $response['error'] = $error_contact.' - Contact'.$str.' have no email address.';
        $response['to_user'] = $to_user;
        $response['cc_user'] = $cc_user;
        $response['bcc_user'] = $bcc_user;

        return $response;
    }

    /*
     * get the list of notes by related type
     * * @param {obj} $reqData
     */
    function get_acitvity_notes_by_related_type(){
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        $this->load->model('recruitment/Recruitment_applicant_model');
       if ($reqData->data) {
           // validation rule
           $validation_rules = [
               array('field' => 'entity_id', 'label' => 'entity id', 'rules' => 'required'),
               array('field' => 'related_type', 'label' => 'sales type', 'rules' => 'required'),
           ];
           if (!property_exists($reqData->data, "entity_id") && !empty($reqData->data->entity_parent)) {
               unset($validation_rules[0]);
           }
           // set data in libray for validate
           $this->form_validation->set_data((array) $reqData->data);

           // set validation rule
           $this->form_validation->set_rules($validation_rules);

           // check data is valid or not
           if ($this->form_validation->run()) {
               $response = $this->Activity_model->get_acitvity_notes_by_related_type($reqData->data, $adminId);
           } else {
               $errors = $this->form_validation->error_array();
               $response = ['status' => false, 'error' => implode(', ', $errors)];
           }
       }

       echo json_encode($response);
   }
}
