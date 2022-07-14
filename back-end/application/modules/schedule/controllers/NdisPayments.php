<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class NdisPayments extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Ndispayments_model');
    }

    //Load line items for
    public function get_line_items_for_payment()
    {

        $reqData = request_handler();

        if (empty($reqData->data)) {
            return false;
        }
        $reqData = (array) $reqData->data;

        $data = $this->Ndispayments_model->get_line_items_for_payment($reqData);
        echo json_encode($data);
    }

    # Get support ndis duration for mixed type with date
    public function get_support_type_ndis_duration()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            return false;
        }
        $reqData = (array) $reqData->data;

        $data = $this->Ndispayments_model->get_support_type_ndis_duration($reqData);

        $result = [ 'status' => true, 'data' => $data];
        return $this->output->set_output(json_encode($result));
    }    
}