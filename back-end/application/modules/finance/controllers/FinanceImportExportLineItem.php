<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FinanceImportExportLineItem extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model([ 'Finance_import_export_line_item', 'Finance_import_meta_data', 'Finance_import_meta_data_w_price']);
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->load->library('UserName');
        $this->loges->setLogType('finance_line_item');
        $this->funcding_type = "NDIS";
        $this->file_mime_type = [
            'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
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

    function read_data_from_data_sheet() {
        $data = request_handlerFile('access_finance_line_item');
        
        # Validate file
        if (empty($_FILES['docsFile']['name'])) {
            $return = ['status' => false, 'error' => 'Please select a csv file to upload.'];
            return $this->output->set_output(json_encode($return));
        }

        # Validate file
        if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {

            # file name
            $tmp_f_name = $_FILES['docsFile']['tmp_name'];
            $overwrite_item = false;
            if (isset($data->overwrite_item)) {
                $overwrite_item = filter_var($data->overwrite_item, FILTER_VALIDATE_BOOLEAN);    
            }
            
            if (in_array($_FILES['docsFile']['type'], $this->file_mime_type)) {
                $return = $this->Finance_import_export_line_item->read_data_from_data_sheet($tmp_f_name, $overwrite_item, $data->adminId);
            } else {
                $return = ['status' => false, 'error' => 'Invalid file extension, Please upload supported (CSV, Excel) file only'];
            }            
        } else {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.'];
        }
        return $this->output->set_output(json_encode($return));
    }

    /**
     * Update meta data of exisiting line item
     */
    function meta_data_from_data_sheet() {
        $data = request_handlerFile('access_finance_line_item');
        
        # Validate file
        if (empty($_FILES['docsFile']['name'])) {
            $return = ['status' => false, 'error' => 'Please select a csv file to upload.'];
            return $this->output->set_output(json_encode($return));
        }

        # Validate file
        if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {

            # file name
            $tmp_f_name = $_FILES['docsFile']['tmp_name'];
            $overwrite_item = false;
            if (isset($data->overwrite_item)) {
                $overwrite_item = filter_var($data->overwrite_item, FILTER_VALIDATE_BOOLEAN);    
            }
            
            if (in_array($_FILES['docsFile']['type'], $this->file_mime_type)) {
                $return = $this->Finance_import_meta_data->read_data_from_data_sheet($tmp_f_name, $overwrite_item, $data->adminId);
            } else {
                $return = ['status' => false, 'error' => 'Invalid file extension, Please upload supported (CSV, Excel) file only'];
            }            
        } else {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.'];
        }
        return $this->output->set_output(json_encode($return));
    }

    /**
     * Update meta data of exisiting line item with price
     */
    function meta_data_w_price_from_data_sheet() {
        $data = request_handlerFile('access_finance_line_item');
        
        # Validate file
        if (empty($_FILES['docsFile']['name'])) {
            $return = ['status' => false, 'error' => 'Please select a csv file to upload.'];
            return $this->output->set_output(json_encode($return));
        }

        # Validate file
        if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {

            # file name
            $tmp_f_name = $_FILES['docsFile']['tmp_name'];
            $overwrite_item = false;
            if (isset($data->overwrite_item)) {
                $overwrite_item = filter_var($data->overwrite_item, FILTER_VALIDATE_BOOLEAN);    
            }
            
            if (in_array($_FILES['docsFile']['type'], $this->file_mime_type)) {
                $return = $this->Finance_import_meta_data_w_price->read_data_from_data_sheet($tmp_f_name, $overwrite_item, $data->adminId);
            } else {
                $return = ['status' => false, 'error' => 'Invalid file extension, Please upload supported (CSV, Excel) file only'];
            }            
        } else {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.'];
        }
        return $this->output->set_output(json_encode($return));
    }

}