<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceDashboard extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Finance_quote_model');
        $this->load->model('Finance_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->model('common/List_view_controls_model');
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

    public function get_quote_dashboard_graph() {
        $reqData = request_handler('access_finance');
        $reqData = $reqData->data;

        if (isset($reqData)) {
            if ($reqData->type === 'genrated_quote' || $reqData->type == 'all') {
                $res['genrated_quote'] = $this->Finance_quote_model->get_quote_dashboard_graph('genrated_quote', $reqData);
            }

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

    /*
     * fetching the reference data of pay rates
     */
    function get_pay_rate_ref_data() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_pay_rate_ref_data();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetching the reference data of charge rates
     */
    function get_charge_rate_ref_data() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_charge_rate_ref_data();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches the next number in creating timesheet
     */
    function get_next_timesheet_no() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_next_timesheet_no();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches the next number in creating invoice
     */
    function get_next_invoice_no() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_next_invoice_no();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * bulk importing pay rates and its relevant information
     */
    public function import_payrates() {
        $reqData = request_handlerFile('access_finance');
        if (!empty($reqData->adminId) && !empty($_FILES)) {
            $result = $this->Finance_model->import_payrates($reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Please provide a CSV file'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Fetching the timesheet line items from keypay which are payment processed and
     * marking relative timesheet line items as paid
     */
    public function get_paid_keypay_timesheets() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_paid_keypay_timesheets($reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * bulk importing charge rates and its relevant information
     */
    public function import_chargerates() {
        $reqData = request_handlerFile('access_finance');
        if (!empty($reqData->adminId) && !empty($_FILES)) {
            $result = $this->Finance_model->import_chargerates($reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Please provide a CSV file'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * submitting timesheet line items into keypay
     */
    public function create_keypay_timesheet() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $pass_ids = null;
            if(is_array($data['id'])) {
                $pass_ids = $data['id'];
            }
            else {
                $pass_ids[] = $data['id'];
            }
            $result = $this->Finance_model->create_bulk_keypay_timesheet($pass_ids, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the pay rate and its relevant information
     */
    public function create_update_pay_rate() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->create_update_pay_rate($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the timesheet and its relevant information
     */
    public function create_update_timesheet() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->create_update_timesheet($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the invoice and its relevant information
     */
    public function create_update_invoice() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->create_update_invoice($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * adding/updating the charge rate and its relevant information
     */
    public function create_update_charge_rate() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->create_update_charge_rate($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting finance charge rate details
     */
    public function get_charge_rate_details() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->get_charge_rate_details($data['id']);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting finance pay rate details
     */
    public function get_pay_rate_details() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->get_pay_rate_details($data['id']);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting user to download the error report after bulk pay rates import
     */
    public function download_import_stats() {
        $import_id = $this->input->get('id');
        if(!empty($import_id)) {
            $result = $this->Finance_model->download_import_stats($import_id);

            if($result['status'] == false) {
                echo json_encode($result);
                exit();
            }

            # plain print without json encoding
            echo $result['data'];
            exit();
        }
    }

    /**
     * fetching pay rates and its relevant information
     */
    public function get_pay_rates_list() {
        $reqData = request_handler();

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
        }
        
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_pay_rates_list($reqData->data, $filter_condition);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching charge rates and its relevant information
     */
    public function get_charge_rates_list() {
        $reqData = request_handler();
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
        $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
        if (is_array($filter_condition)) {
            echo json_encode($filter_condition);
            exit();
        }
        }
        if (!empty($reqData->data)) {
  
            $filter_condition = str_replace([
                "charge_rate_category_label", 
                'pay_level_label', 
                'skill_level_label',
                  'cost_book_label',
                  ], 
            ["pr.charge_rate_category_id", "pr.pay_level_id", "pr.skill_level_id", "pr.cost_book_id" ], $filter_condition);
            $result = $this->Finance_model->get_charge_rates_list($reqData->data,$filter_condition);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the payrate categories
     */
    function get_payrates_categories() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_payrates_categories();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }      
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the reference data of invoice line items
     */
    function get_invoice_line_items_ref_data() {
        $reqData = request_handler();
        if (!empty($reqData->data) && !empty($reqData->data->id)) {
            $result = $this->Finance_model->get_invoice_line_items_ref_data($reqData->data->id);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }      
        echo json_encode($result);
        exit();
    }

    /**
     * archiving timesheet line item
     */
    public function archive_timesheet_line_item() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->archive_timesheet_line_item((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * archiving invoice line item
     */
    public function archive_invoice_line_item() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->archive_invoice_line_item((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * archiving pay rate
     */
    public function archive_pay_rate() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->archive_pay_rate((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * archiving charge rate
     */
    public function archive_charge_rate() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->archive_charge_rate((array) $reqData->data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the timesheets list
     */
    public function get_timesheets_list() {
        $reqData = request_handler();

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
        }

        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_timesheets_list($reqData->data, $filter_condition);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the invoices list
     */
    public function get_invoices_list() {
        $reqData = request_handler();

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
        }

        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_invoices_list($reqData->data, $filter_condition);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting finance invoice details
     */
    public function get_invoice_details() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->get_invoice_details($data['id']);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * getting finance timesheet details
     */
    public function get_timesheet_details() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->get_timesheet_details($data['id']);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the timesheet status.
     */
    public function update_timesheet_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;            
            $result = $this->Finance_model->update_timesheet_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Adding/updating the line items of timesheet
     */
    public function add_update_timesheet_line_items() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->add_update_timesheet_line_items($data, $reqData->adminId, true);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Adding/updating the line items of invoice
     */
    public function add_update_invoice_line_items() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->add_update_invoice_line_items($data, $reqData->adminId, true);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * generating invoice pdf, storing it onto S3 bucket
     * using invoice details to populate dynamic data into pdf
     */
    public function generate_invoice_pdf() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->generate_invoice_pdf($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the invoice status in bulk
     */
    public function bulk_update_invoice_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->bulk_update_invoice_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * Updating the invoice status.
     */
    public function update_invoice_status() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $result = $this->Finance_model->update_invoice_status($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches all the invoice statuses
     */
    function get_invoice_statuses() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_invoice_statuses();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetches all the final invoice statuses for invoice details page
     */
    function get_invoice_statuses_final() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $response = $this->Finance_model->get_invoice_statuses_final();
            echo json_encode($response);
        }
        exit(0);
    }

    /**
     * fetches all the invoice statuses grouped for invoice details page
     */
    function get_invoice_statuses_grouped() {
        $reqData = request_handler();

        if (!empty($reqData->data)) {
            $response = $this->Finance_model->get_invoice_statuses_grouped();
            echo json_encode($response);
        }
        exit(0);
    }

    /*
     * fetches all the timesheet statuses and query list
     */
    function get_timesheet_ref_data() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_timesheet_ref_data();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches all the invoice statuses and query list
     */
    function get_invoice_ref_data() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_invoice_ref_data();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches all the invoice cancell and void reasons list
     */
    function get_invoice_cancel_void_reasons() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_invoice_cancel_void_reasons();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * fetches all the timesheet statuses
     */
     function get_timesheet_statuses() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_model->get_timesheet_statuses();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the timesheet line items list
     */
    public function get_timesheet_line_items_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_timesheet_line_items_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the invoice shifts list
     */
    public function get_invoice_shifts_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_invoice_shifts_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
     * fetching the invoice line items list
     */
    public function get_invoice_line_items_list() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_invoice_line_items_list($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /**
    * Send invoice email
    */
    function send_invoice_mail() {
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $data = object_to_array($reqData->data);
            $result = $this->Finance_model->send_invoice_mail($data, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

     /*
     * its used for gettting account or participant name on base of @param $query
     */
    public function account_participant_name_search()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            return false;
        }
        $skip_sites =isset($reqData->data->skip_sites) ? true : false;
        $isNdis=$reqData->data->invoice_type->label=='NDIS'?true:false;
        $data = $this->Finance_model->account_participant_name_search($reqData->data->query, $skip_sites,$isNdis);
        echo json_encode($data);
        exit();
    }

    public function get_service_agreement_by_participant(){
        $reqData = request_handler();
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_service_agreement_by_participant($reqData->data);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

    /*
     * To get the line item name on base of @param $query
     */
    public function get_line_item_list_from_sa()
    {
        $reqData = request_handler();
        if (empty($reqData->data)) {
            return false;
        }
        $data = $this->Finance_model->get_line_item_list_from_sa($reqData->data->query, $reqData->data);
        echo json_encode($data);
        exit();
    }

     /**
     * Pull the Ndis error table list
     * @param $reqData {obj} front end data
     * @param $filter_condition {obj} filter data coming from front end
     * 
     */
    public function get_ndis_error_list() {

        $reqData = request_handler();
        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
           
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);                
                exit();
            }
            
            $account_fullname_cond = "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) ";
            $filter_condition = str_replace(['account_fullname', 'role_name'], [$account_fullname_cond, 'r.name'], $filter_condition);
        }
        if (!empty($reqData->data)) {
            $result = $this->Finance_model->get_ndis_error_list($reqData->data, $filter_condition, $reqData->adminId);
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }
}
