<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BusinessUnit extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('Business_unit_model');        
    }

     /**
     * adding/updating the business unit and its relevant information
     */
    public function create_update_business_unit() {
        $reqData = request_handler();        
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Business_unit_model->create_update_business_unit($data, $reqData->adminId);
        } else {
            $result = ['status' => FALSE, 'error' => 'Requested data is null'];
        }
        return $this->output->set_content_type('json')->set_output(json_encode($result));
    }

    /**
     * get Business Unit details
     */
    public function get_business_unit_list()
    {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $reqData = $reqData->data?? [];
            $response = $this->Business_unit_model->get_business_unit_list($reqData);
        }
        else {
            $response = ['status' => FALSE, 'error' => 'Sorry no data found'];
        }

        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

     /**
     * get Business unit details
     */
    public function get_business_unit_details()
    {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $buId = $reqData->data->id;
            $response = $this->Business_unit_model->get_business_unit_details($buId);

            if (!empty($response)) {
                $response = ['status' => true, 'data' => $response];
            } else {
                $response = ['status' => false, 'error' => 'Sorry no data found'];
            }
        }

        return $this->output->set_content_type('json')->set_output(json_encode($response));
    }

    /**
     * Get owner Details
     */
    public function get_owner_details() {
        $reqData = request_handler();
        $result = [];               
        if (!empty($reqData->data)) {
            $result = $this->Business_unit_model->get_owner_details($reqData->data);           
        }
        return $this->output->set_content_type('json')->set_output(json_encode($result));
    }
}