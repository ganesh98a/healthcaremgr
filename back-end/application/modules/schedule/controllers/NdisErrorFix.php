<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class NdisErrorFix extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Ndis_error_fix_model');
    }

    /**
     * This function used to update the existing shift warning and mark it as invoicesable if all NDIS error has fixed
     * - Async API call
     */
    public function update_shift_ndis_warning() {
        $this->Ndis_error_fix_model->update_shift_ndis_warning($this->input->post('requestData'));
        exit();
    }

    /**
     * This function used to update the existing shift warning and mark it as invoicesable if all NDIS error has fixed
     * - Async API call
     */
    public function update_shift_missing_ndis_line_item() {
        $this->Ndis_error_fix_model->update_ndis_line_item_missing($this->input->post('requestData'));
        exit();
    }

}