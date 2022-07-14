<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceQuoteManagement extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Finance_quote_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->loges->setLogType('finance_quote');
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

    public function get_participant_organization_name() {
        $reqData = request_handler('access_finance_quote');

        if (isset($reqData->data->search)) {
            $res = $this->Finance_quote_model->get_participant_organization_name($reqData->data);

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

    public function get_line_item_name() {
        $reqData = request_handler('access_finance_quote');

        if (isset($reqData->data->search)) {
            $res = $this->Finance_quote_model->get_line_item_name($reqData->data);

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

    public function get_finance_quote_option_and_details() {
        $reqData = request_handler('access_finance_quote');
        $reqData = $reqData->data;

        if ((!empty($reqData->user_type) && !empty($reqData->userId)) || (!empty($reqData->quoteId))) {
            if (!empty($reqData->quoteId)) {
                $result = $this->Finance_quote_model->get_quote_details($reqData->quoteId);

                if (!empty($result)) {

                    $reqData->user_type = $result->user_type;
                    $reqData->userId = $result->userId;

                    $result = (array) $result;
                } else {
                    echo json_encode(["status" => false, "error" => "Quote Id does not exist"]);
                    exit();
                }
            }



            if ($reqData->user_type == 6) {
                $result['customer_catogory'] = $this->basic_model->get_record_where('finance_enquiry_customer_catogory', ['id as value', 'name as label'], ['archive' => 0]);
                $result['states'] = $this->basic_model->get_record_where('state', ['id as value', 'name as label'], ['archive' => 0]);
            }
            $this->load->model('Finance_common_model');
            if ($reqData->user_type == 2) {
                $result['user_details'] = $this->Finance_common_model->get_participant_details_by_id($reqData->userId);
            } elseif ($reqData->user_type == 4) {
                $result['user_details'] = $this->Finance_common_model->get_organisation_details_by_id($reqData->userId);
            }

            $result['funding_types'] = $this->basic_model->get_record_where('funding_type', ['id as value', 'name as label'], ['archive' => 0]);

            echo json_encode(["status" => true, "data" => $result]);
        } else {
            echo json_encode(["status" => false, "error" => "user type or user Id not found"]);
        }
    }

    function create_update_quote() {
        $reqData = request_handler('access_finance_quote');
        $adminId = $reqData->adminId;
        $reqData = $reqData->data;

        if ($reqData) {
            $reqData->user_type = !empty($reqData->user_type) ? $reqData->user_type : 0;

            $validation_rules = array(
                array('field' => 'quote_date', 'label' => 'quote date', 'rules' => 'required'),
                array('field' => 'valid_until', 'label' => 'valid until', 'rules' => 'required'),
                array('field' => 'save_type', 'label' => 'save type', 'rules' => 'required'),
                array('field' => 'userId', 'label' => 'user Id', 'rules' => 'required'),
                array('field' => 'quote_note', 'label' => 'quote note', 'rules' => 'required'),
                array('field' => 'user_type', 'label' => 'user type', 'rules' => 'required|in_list[2,4,6]'),
                array('field' => 'item', 'label' => 'line item', 'rules' => 'callback_check_line_item_for_quote[' . json_encode($reqData) . ']'),
                array('field' => 'manaul_item', 'label' => 'line item', 'rules' => 'callback_check_manual_item_for_quote[' . json_encode($reqData) . ']'),
            );

            if ($reqData->user_type == 6) {
                $validation_rules[] = array('field' => 'name', 'label' => 'service for', 'rules' => 'required');
                $validation_rules[] = array('field' => 'customerCategoryId', 'label' => 'customer Category Id', 'rules' => 'required');
                $validation_rules[] = array('field' => 'contact_name', 'label' => 'contact name', 'rules' => 'required');
                $validation_rules[] = array('field' => 'email', 'label' => 'email', 'rules' => 'required');
                $validation_rules[] = array('field' => 'primary_phone', 'label' => 'primary phone', 'rules' => 'required');
                $validation_rules[] = array('field' => 'seconday_phone', 'label' => 'seconday phone', 'rules' => 'required');
                $validation_rules[] = array('field' => 'street', 'label' => 'street', 'rules' => 'required');
                $validation_rules[] = array('field' => 'street', 'label' => 'street', 'rules' => 'required');
                $validation_rules[] = array('field' => 'postcode', 'label' => 'postcode', 'rules' => 'required');
                $validation_rules[] = array('field' => 'suburb', 'label' => 'suburb', 'rules' => 'required');
            }

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $quoteId = $this->Finance_quote_model->create_update_quote($reqData, $adminId);

                $quoteNumber = $this->username->getName('quote_number', $quoteId);

                $txt = "Added new quote : " . $quoteNumber;
                $this->loges->setTitle($txt);
                $this->loges->setUserId($quoteId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function check_line_item_for_quote($userless, $reqData) {
        $data = json_decode($reqData);

        if (!empty($data->items)) {
            foreach ($data->items as $val) {
                if (empty($val->funding_type)) {
                    $this->form_validation->set_message('check_line_item_for_quote', 'Please select funding type in item');
                    return false;
                }
                if (empty($val->line_ItemId->value)) {
                    $this->form_validation->set_message('check_line_item_for_quote', 'Please select line item in item');
                    return false;
                }
                if (empty($val->qty)) {
                    $this->form_validation->set_message('check_line_item_for_quote', 'Please specify quantity in item');
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_line_item_for_quote', 'Please attach at least one line item');
            return false;
        }
    }

    function check_manual_item_for_quote($reqData) {
        $data = json_decode($reqData);

        if (!empty($data->manual_item)) {
            foreach ($data->manual_item as $key => $val) {
                if (empty($val->item_name) && empty($val->description) && empty($val->cost) && empty($val->charge_by)) {
                    
                } else {
                    if (empty($val->item_name)) {
                        $this->form_validation->set_message('check_manual_item_for_quote', 'Please select item_name in manual item');
                        return false;
                    }
                    if (empty($val->description)) {
                        $this->form_validation->set_message('check_manual_item_for_quote', 'Please specify description in manual item');
                        return false;
                    }
                    if (empty($val->cost)) {
                        $this->form_validation->set_message('check_manual_item_for_quote', 'Please specify cost in manual item');
                        return false;
                    }
                    if (empty($val->charge_by)) {
                        $this->form_validation->set_message('check_manual_item_for_quote', 'Please specify charge_by in manual item');
                        return false;
                    }
                }
            }
        }
    }

    function get_quote_listing() {
        $reqData = request_handler('access_finance_quote');

        if (isset($reqData->data)) {

            $res = $this->Finance_quote_model->get_quote_listing($reqData->data);

            echo json_encode($res);
        }
    }

    function check_quote_can_accept_or_reject_or_archive_or_resend_mail($quoteId, $action) {
        if (!empty($quoteId)) {
            $res = $this->Finance_quote_model->check_quote_can_accept_or_reject_or_archive_or_resend_mail($quoteId, $action);

            if (empty($res)) {
                $this->form_validation->set_message('check_quote_can_accept_or_reject_or_archive_or_resend_mail', 'Quote Id not valid for ' . $action);
                return false;
            } else {
                return true;
            }
        } else {
            $this->form_validation->set_message('check_quote_can_accept_or_reject_or_archive_or_resend_mail', 'Quote id is required');
            return false;
        }
    }

    function accept_or_reject_quote() {
        $reqData = request_handler('access_finance_quote');
        $adminId = $reqData->adminId;
        $this->loges->setUserId($reqData->adminId);
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $reqData->action = isset($reqData->action) ? $reqData->action : '';

            $validation_rules = array(
                array('field' => 'quoteId', 'label' => 'quote Id', 'rules' => 'callback_check_quote_can_accept_or_reject_or_archive_or_resend_mail[' . $reqData->action . ']'),
                array('field' => 'action', 'label' => 'action', 'rules' => 'required|in_list[accept,reject,archive]'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Finance_quote_model->accept_reject_or_archive_quote($reqData, $adminId);

                $quoteNumber = $this->username->getName('quote_number', $reqData->quoteId);

                $txt = $reqData->action . "Quote : " . $quoteNumber;
                $this->loges->setTitle($txt);
                $this->loges->setUserId($reqData->quoteId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function resend_quote_mail() {
        $requestData = request_handler('access_finance_quote');
        $this->loges->setUserId($requestData->adminId);
        $reqData = $requestData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'quoteId', 'label' => 'quote Id', 'rules' => 'callback_check_quote_can_accept_or_reject_or_archive_or_resend_mail[resend_mail]'),
            );

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {

                $this->Finance_quote_model->resend_quote_mail($reqData);

                $quoteNumber = $this->username->getName('quote_number', $reqData->quoteId);

                $txt = "Resend Quote : " . $quoteNumber;
                $this->loges->setTitle($txt);
                $this->loges->setUserId($reqData->quoteId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();

                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    function get_quote_pdf() {
        $reqData = request_handler('access_finance_quote');
        
        if (isset($reqData->data)) {
            $res = $this->Finance_quote_model->get_quote_pdf($reqData->data);
            //print_r($res);
            if (!empty($res)) {
                if (!empty($res->pdf_file)) {
                    $pdf_fileFCpath = FCPATH . QUOTE_FILE_PATH . $res->pdf_file;
                    if (file_exists($pdf_fileFCpath)) {
                        $pdf_filePath = base_url() . 'mediaShowDocument/fq/' . urlencode(base64_encode($res->pdf_file));

                        // Check download quote file permission
                        $adminId=$reqData->adminId;
                        require_once APPPATH . 'Classes/admin/permission.php';
                        $obj_permission = new classPermission\Permission();
                        $result = $obj_permission->check_permission($adminId, 'access_finance_admin');
                        $showDownload='#toolbar=0';
                        if($result){
                            $showDownload='#toolbar=1';
                        }

                        $data = ['file_path' => $pdf_filePath . $showDownload, 'file_name' => $res->pdf_file, 'quote_for' => $res->quote_for, 'status' => $res->status];
                        
                        echo json_encode(['status' => true, 'data' => $data]);
                        exit();
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Quote pdf file not exist']);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Quote pdf file name not exist']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Records not exist']);
                exit();
            }
        }
    }

    public function get_quote_dashboard_graph() {
        $reqData = request_handler('access_finance_quote');
        $reqData = $reqData->data;

        if (isset($reqData)) {

            if ($reqData->type === 'genrated_quote' || $reqData->type == 'all') {
                $res['genrated_quote'] = $this->Finance_quote_model->get_quote_dashboard_graph('genrated_quote', $reqData);
            }

            if ($reqData->type === 'accepted_quote' || $reqData->type == 'all') {
                $res['accepted_quote'] = $this->Finance_quote_model->get_quote_dashboard_graph('accepted_quote', $reqData);
            }

            if ($reqData->type === 'average_time' || $reqData->type == 'all') {
                $res['average_time'] = $this->Finance_quote_model->get_quote_dashboard_graph_for_average_time($reqData);
            }

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

}
