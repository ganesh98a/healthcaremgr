<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FinanceShiftAndPayRoll extends MX_Controller
{
    use formCustomValidation;
    function __construct()
    {
        parent::__construct();
        $this->load->model(['Finanace_shift_payroll_model' => 'Finanace_shift_payroll']);
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
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

    function get_shift_list()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->data)) {
            $result = $this->Finanace_shift_payroll->get_shift_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }
    
     function export_shift_to_csv()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->data)) {
        $result = $this->Finanace_shift_payroll->get_shift_list($reqData->data, ["all_shift_export_to_csv" => true]);
            $this->load->model('Finance_common_model');

            $dataHearder = ['shift_id' => 'Shift ID', 'shift_for' => 'Shift For ', 'completed_by' => 'Shift Completed By', 'shfit_date_format' => 'Date', 'total_hours' => 'Hours', 'amount' => 'Amount', 'work_area_type' => 'Work Area type', 'shfit_status' => 'Status'];

            $response = $this->Finance_common_model->export_csv($dataHearder, obj_to_arr($result['data']), ['file_name' => 'Shift_' . date('d_m_Y_H_i_s') . '.csv']);

            echo json_encode($response);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }

    /*
     * function upload_attachment_docs
     * return type json 
     */

    public function upload_attachment_docs()
    {
        // handle request and check permission to upload applicant docs
        $data = request_handlerFile('access_finance_shift_and_payroll');

        // include participant docs class
        require_once APPPATH . 'Classes/Finance/financePayrollExemptionDocs.php';
        $docObj = new ClassFinancePayrollExemptionDocs\FinancePayrollExemptionDocs();

        // set requested data for validate
        $checkData = (array) $data;
        $checkData['check_orgId_date_range'] = $checkData['orgId'];
        $this->form_validation->set_data($checkData);

        // make validation rule
        $validation_rules = array(
            array('field' => 'orgId', 'label' => 'Organisation Id', 'rules' => 'required'),
            array('field' => 'from_date', 'label' => 'Valid From', 'rules' => 'required|callback_date_check[to_date,date_check_lessthenotequal_other_field,Valid From date must be less than Valid Until date.,' . DateFormate($data->to_date, DB_DATE_FORMAT) . ']'),
            array('field' => 'to_date', 'label' => 'Valid Until', 'rules' => 'required|callback_date_check[to_date,date_check_greaterthenotequal_other_field,Valid Until date must be greater than Valid From date.,' . DateFormate($data->from_date, DB_DATE_FORMAT) . ']'),
            array('field' => 'file_title', 'label' => 'File Name', 'rules' => 'required'),
        );
        if (empty($_FILES['docsFile']['name'])) {
            $validation_rules[] = array('field' => 'docsFile', 'label' => 'Document File', 'rules' => 'required');
        }

        if (!empty($_FILES['docsFile']['name'])) {
            $validation_rules[] = array('field' => 'check_orgId_date_range', 'label' => 'Payroll Exemption', 'rules' => 'required|callback_check_payroll_exemption_exists_between_date_range[orgId,' . DateFormate($data->from_date, DB_DATE_FORMAT) . ',' . DateFormate($data->to_date, DB_DATE_FORMAT) . ', already exists for given valid from and valid until date range.]');
        }

        // set rules form validation
        $this->form_validation->set_rules($validation_rules);

        // check requested data return true or false
        if ($this->form_validation->run()) {
            if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {

                $docObj->setFilePath($_FILES['docsFile']['name']);
                $docObj->setOrganisationId($data->orgId);
                $docObj->setFileTitle($data->file_title);
                $docObj->setCreated(DATE_TIME);
                $docObj->setCreatedBy($data->adminId);
                $docObj->setValidFrom(DateFormate($data->from_date, DB_DATE_FORMAT));
                $docObj->setValidTo(DateFormate($data->to_date, DB_DATE_FORMAT));
                $docObj->setArchive(0);
                $docObj->setStatus(1);

                //$dub_result = $docObj->checkDublicateDocs(); // check its duplicate
                $config['upload_path'] = FINANCE_PAYROLL_EXEMPTION_ORG_UPLOAD_PATH; // user here constact for specific path
                $config['input_name'] = 'docsFile';
                $config['remove_spaces'] = false;
                $config['directory_name'] = $data->orgId;
                $config['allowed_types'] = FINANCE_PAYROLL_EXEMPTION_ORG_UPLOAD_TYPE; //'jpg|jpeg|png|pdf';

                $is_upload = do_upload($config); // upload file
                // check here file is uploaded or not return key error true
                if (isset($is_upload['error'])) {
                    // return error comes in file uploading
                    echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                    exit();
                } else {
                    $docObj->setFilePath($is_upload['upload_data']['file_name']);
                    $docObj->createFileData(); // insert data related doc
                    $this->loges->setCreatedBy($data->adminId);
                    $orgName = $this->username->getName('organisation', $data->orgId);
                    $adminName = $this->username->getName('admin', $data->adminId);
                    $this->loges->setTitle('Payroll Exemption added - : ' . $orgName);
                    $this->loges->setSpecific_title('Payroll Exemption added by ' . $adminName);
                    $this->loges->setUserId($data->orgId);
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->createLog();
                    $return = array('status' => true, 'msg' => "Document uploaded successfully.");
                    /*  if (!$dub_result['status']) {
                        $return = array('status' => true, 'warn' => $dub_result['warn']);
                    } else {
                        $return = array('status' => true, 'msg'=>"Document uploaded successfully.");
                    } */
                }
            } else {
                $return = array('status' => false, 'error' => 'Please select a file to upload');
            }
        } else {
            // return error else data data not valid
            $errors = $this->form_validation->error_array();

            $return = array('status' => false, 'error' => implode(', ', $errors));
        }

        echo json_encode($return);
        exit();
    }

    function get_payroll_exemption_list()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->data)) {
            $result = $this->Finanace_shift_payroll->get_payroll_exemption_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    function get_payroll_exemption_history_list()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->data)) {
            $result = $this->Finanace_shift_payroll->get_payroll_exemption_history_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    function set_inactive_payroll_exemption()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');
        if (!empty($reqData->data)) {
            $validation_rules = array(
                array('field' => 'selectedData[]', 'label' => 'Payroll Exemption Row', 'rules' => 'required'),
            );

            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $response = $this->Finanace_shift_payroll->set_inactive_payroll_exemption($reqData->data->selectedData);
                if ($response['status']) {
                    $this->loges->setCreatedBy($reqData->adminId);
                    $adminName = $this->username->getName('admin', $reqData->adminId);
                    $this->loges->setTitle('Payroll Exemption Status updated to inactive');
                    $this->loges->setSpecific_title('Payroll Exemption Status updated to inactive by ' . $adminName);
                    $this->loges->setUserId($reqData->adminId);
                    $this->loges->setDescription(json_encode($reqData->data));
                    $this->loges->createLog();
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
            exit();
        }
    }

    public function update_payroll_exemption_note()
    {

        $reqData = request_handler('access_finance_shift_and_payroll');
        $adminId = $reqData->adminId;
        $this->loges->setCreatedBy($reqData->adminId);
        $reqData = $reqData->data;

        if (!empty($reqData)) {
            $validation_rules = array(
                array('field' => 'exemption_id', 'label' => 'Payroll Exemption Id', 'rules' => 'required'),
                array('field' => 'note', 'label' => 'Note', 'rules' => 'required'),
                array('field' => 'orgId', 'label' => 'Payroll Exemption Organisations id', 'rules' => 'required'),
            );
            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Finanace_shift_payroll->update_payroll_exemption_note($reqData, $adminId);
                if (isset($return['status']) && $return['status']) {

                    // set log details
                    $orgName = $this->username->getName('organisation', $reqData->orgId);
                    $adminName = $this->username->getName('admin', $adminId);

                    $this->loges->setUserId($reqData->exemption_id);
                    $this->loges->setDescription(json_encode($reqData));
                    $this->loges->setTitle('Payroll exemption note added on ' . $orgName);
                    $this->loges->setSpecific_title('Payroll exemption note added on ' . $orgName . ' by ' . $adminName);
                    $this->loges->createLog();
                }
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        } else {
            $return = array('status' => false, 'error' => 'Invalid Request.');
        }
        echo json_encode($return);
    }

    function get_payroll_fetch()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->data)) {
            $result = $this->Finanace_shift_payroll->get_payroll_fetch($reqData->data);
            echo json_encode($result);
            exit();
        }
    }
    function get_payroll_graph_fetch()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');

        if (!empty($reqData->adminId)) {
            $result = $this->Finanace_shift_payroll->get_payroll_graph_fetch();
            echo json_encode($result);
            exit();
        }
    }
    function send_renewal_email_payroll()
    {
        $reqData = request_handler('access_finance_shift_and_payroll');
        
        if (!empty($reqData->data)) {
            $validation_rules = array(
                array('field' => 'exemption_id', 'label' => 'Payroll Exemption Id', 'rules' => 'required'),
                array('field' => 'orgId', 'label' => 'Payroll Exemption Organisations id', 'rules' => 'required'),
            );
            $this->form_validation->set_data((array) $reqData->data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run() == TRUE) {
                $return = $this->Finanace_shift_payroll->send_renewal_email_payroll($reqData->data->orgId);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode(', ', $errors));
            }
        }else {
            $return = array('status' => false, 'error' => 'Invalid Request.');
        }
        echo json_encode($return);
        exit();
    }

    public function get_payroll_graph_dashboard_fetch(){
        $reqData = request_handler('access_finance_shift_and_payroll');
        if (!empty($reqData->data)) {
            if($reqData->data->mode=='payroll_exemption_expire'){
                $return = $this->Finanace_shift_payroll->get_payroll_exemption_expire_within_month();
            }else if($reqData->data->mode=='payroll_tax_ydt'){
                $return = $this->Finanace_shift_payroll->get_total_payroll_exeption_current_financial_year();
            }else if($reqData->data->mode=='shift_queries_amount'){
                $return = $this->Finanace_shift_payroll->get_shfit_queries_amount($reqData->data->type);
            }else if($reqData->data->mode=='shift_by_work_area'){
                $return = $this->Finanace_shift_payroll->get_shfit_total_spent_by_work_area($reqData->data->type);
            }

        }else {
            $return = array('status' => false, 'error' => 'Invalid Request.');
        }
        echo json_encode($return);
        exit();
    }
}
