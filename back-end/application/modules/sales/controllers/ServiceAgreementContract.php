<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for 'Serivce Agreement Contract' objects
 * 
 * @property-read ServiceAgreement_model $ServiceAgreement_model
 */
class ServiceAgreementContract extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;

        $this->load->model(['ServiceAgreement_model', 'ServiceAgreementContract_model']);
        $this->loges->setLogType('crm');
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
     * Generate service agreement - private travel contract and envelop
     * @param {int} $service_agreement_attachment_id
     * return array
     */
    public function generate_private_travel_agreement_contract($service_agreement_attachment_id) {
            // call docusign api
            $this->call_generate_docusign($service_agreement_attachment_id,'private_travel');            
        
    }

    /*
     * Preview service agreement contract
     */
    function preview_docusign(){
        $reqData = request_handler('access_crm');
        $data = obj_to_arr($reqData->data);
        $adminId = $reqData->adminId;
        if (!empty($reqData->data)) {
            $sa_action = 'create';
            $dataObj = (array) $reqData->data;

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
                array('field' => 'signed_by', 'label' => 'Signed By', 'rules' => 'required'),
            ];

            if (isset($data['type']) && $data['type'] == 2) {
                $validation_rules[] = array('field' => 'service_agreement_type', 'label' => 'Service Agreement template type', 'rules' => 'required'); 
            }

            $this->form_validation->set_data($dataObj);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                // NDIS
                if ($data['service_agreement_type'] == 2) {
                    $service_agreement_id = $data['service_agreement_id'];
                    $return = $this->ServiceAgreementContract_model->preview_ndis_document($data, 'service_agreemnet_v2', 'service_agreemnet_v1_style');
                }
                else if ($data['service_agreement_type'] == 3) {
                    $return = $this->ServiceAgreementContract_model->preview_ndis_document($data, 'service_agreemnet_v3', 'service_agreemnet_v1_style');
                }
                else if ($data['service_agreement_type'] == 4) {
                    $return = $this->ServiceAgreementContract_model->preview_ndis_document($data, 'private_travel_agreemnet_v1', 'service_agreemnet_v1_style');
                }
                else if ($data['service_agreement_type'] == 1) {
                    $return = $this->ServiceAgreementContract_model->preview_ndis_document($data, 'constent_service_agreemnet_v1', 'constent_service_agreemnet_v1_style');
                }
                else {
                    $return = ['status' => false, 'error' => system_msgs('something_went_wrong')];
                } 
            } else {
                $errors = $this->form_validation->error_array();
                $return = ['status' => false, 'error' => implode(', ', $errors)];
            }
              
        } else {
            // If requested data is empty or null
            $return = ['status' => false, 'error' => 'Requested data is null']; 
        }
        return $this->output->set_output(json_encode($return));
    }

}
