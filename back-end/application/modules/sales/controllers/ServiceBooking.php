<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller for 'Serivce Agreement Contract' objects
 * 
 * @property-read ServiceAgreement_model $ServiceAgreement_model
 */
class ServiceBooking extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');
        $this->load->helper('message');
        $this->load->model('Service_booking_model');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }


     /**
     * used to get service agreement type from reference table
     */ 
    function get_service_agreement_type_list(){
         $data = $this->Service_booking_model->get_service_agreement_type_list();
         echo json_encode($data);
         exit();
    }
    /**
     * used to create  service agreement
     */
    function create_update_service_booking(){
         $reqData = request_handler();
         $adminId = $reqData->adminId;
         $this->loges->setCreatedBy($adminId);
         $response = $this->Service_booking_model->create_update_service_booking($reqData, $adminId);
         echo json_encode($response);
         exit();
    }

    /**
     * used to get_funding_sum_by_service_agreement_type
     */
    function get_funding_sum_by_service_agreement_type(){
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        $response = $this->Service_booking_model->get_funding_sum_by_service_agreement_type($reqData, $adminId);
        echo json_encode($response);
        exit();
    }
    /**
     * used to get existing service booking 
     */
    function get_service_booking_list(){
        $reqData = request_handler();
        $response = $this->Service_booking_model->get_service_booking_list($reqData->data->related_service_agreement_id);
        echo json_encode($response);
        exit();
    }

    /**
     * used to get existing single service booking by id
     */
    function get_service_booking_by_id(){
        $reqData = request_handler();
        $response = $this->Service_booking_model->get_service_booking_by_id($reqData);
        echo json_encode($response);
        exit();
    }
    /**
     * used to delete existing service booking 
     */
    function delete_service_booking(){
        $reqData = request_handler();
        $adminId = $reqData->adminId;
        $response = $this->Service_booking_model->delete_service_booking($reqData->data,$adminId);
        echo json_encode($response);
        exit();
    }

    
}
