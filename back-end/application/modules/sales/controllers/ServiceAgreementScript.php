<?php

use XeroPHP\Models\Accounting\Receipt;

defined('BASEPATH') OR exit('No direct script access allowed');

require_once(__DIR__."/ServiceAgreementContract.php");

/**
 * Controller for 'Serivce Agreement' objects
 * 
 * @property-read ServiceAgreement_model $ServiceAgreement_model
 */
class ServiceAgreementScript extends ServiceAgreementContract
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('ServiceAgreementScript_model');
        $this->load->helper('message');
        $this->load->helper('i_pad');
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


    /**
     * Fetch all duplicate line items
     */
    public function get_line_item_duplicate_script()
    {
        $reqData=(array) api_request_handler(); 
        $data = $reqData;
        if(!empty($data['type'])){
            $line_tems = $this->ServiceAgreementScript_model->get_duplicate_line_item_from_finance($data['type']);            
            return $this->output->set_output(json_encode(['line_tems' => $line_tems])); 
        }else{
            return $this->output->set_output(json_encode(["status"=> false , "msg"=>"Please enter all required data"]));           
        }
        
    }

    /**
     * Update duplicate line items
     */
    public function update_line_item_duplicate_script()
    {
        $reqData=(array) api_request_handler(); 
        $data = $reqData;        
        if(!empty($data['type'])){
            $line_tems = $this->ServiceAgreementScript_model->update_line_item_duplicate($data['type']);            
            return $this->output->set_output(json_encode(['line_tems' => $line_tems])); 
        }else{
            return $this->output->set_output(json_encode(["status"=> false , "msg"=>"Please enter all required data"]));           
        }
    }

    /**
     * Delete duplicate line item
     */
    public function delete_duplicate_line_item_number()
    {
        $reqData=(array) api_request_handler(); 
        $data = $reqData;        
        $deleted_id = $this->ServiceAgreementScript_model->delete_duplicate_line_item_number();            
        return $this->output->set_output(json_encode(['deleted_id' => $deleted_id])); 
       
    }
}
