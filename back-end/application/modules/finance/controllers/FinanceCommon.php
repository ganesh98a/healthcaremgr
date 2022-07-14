<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceCommon extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model(['Finance_common_model' => 'Finance_common']);
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->loges->setModule(22);
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

    function get_organisation_name_payroll_exemption() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_common->get_organisation_name_payroll_exemption($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    function get_organisation_details() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_common->get_organisation_details_by_id($reqData->data->id);
            $result['finance_payroll_details'] = [];
            if (isset($reqData->data->extraParms) && isset($reqData->data->extraParms->type) && $reqData->data->extraParms->type == 'payrollExemption') {
                $this->load->model('Finanace_shift_payroll_model');
                $result_payroll = $this->Finanace_shift_payroll_model->get_org_payroll_exemption_current_status_by_current_date($reqData->data->id);
                if (!empty($result_payroll)) {
                    $result['finance_payroll_details'] = $result_payroll;
                }
            }

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        }
    }

    function get_participant_details() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_common->get_participant_details_by_id($reqData->data->id);

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        }
    }
    function get_site_details() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_common->get_site_details_by_id($reqData->data->id);

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        }
    }

    function test_notification() {
        $this->load->library('MobilePushNotification');
        $deviceId = array('353326065326094', '4B7787BC59C81429');
        $res = $this->mobilepushnotification->push_notification_android($deviceId, 'sd');

        print_r($res);
    }

    function create_pdf() {
        $this->load->model('Finance_invoice_model');

        $res = $this->Finance_invoice_model->create_invoice_pdf(1);

        print_r($res);
    }

    function get_house_details() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_common->get_house_details_by_id($reqData->data->id);

            echo json_encode(['status' => true, 'data' => $result]);
            exit();
        }
    }

}
