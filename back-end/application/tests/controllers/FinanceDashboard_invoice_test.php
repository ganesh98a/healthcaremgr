<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Class to test the controller FinanceDashboard and related models of it
 */
class FinanceDashboard_invoice_test extends TestCase {
    
    protected $CI;

    /**
     * setting up and loading models & libraries needed
     */
    public function setUp() {
        $this->CI = &get_instance();
        $this->CI->load->model('../modules/finance/models/Finance_model');
        $this->CI->load->model('../modules/schedule/models/Schedule_model');
        $this->CI->load->model('../modules/sales/models/Account_model');
        $this->CI->load->library('form_validation');
        $this->Finance_model = $this->CI->Finance_model;
        $this->Schedule_model = $this->CI->Schedule_model;
        $this->Account_model = $this->CI->Account_model;
        $this->basic_model = $this->CI->basic_model;
    }

    /**
     * validating adding new invoice failure
     * empty invoice type and contact id
     */
    function test_create_update_invoice_val1() {

        $output = $this->Finance_model->get_next_invoice_no();
        if($output['data'])
            $postdata['invoice_no'] = $output['data'];
        
        $postdata['invoice_type'] = null;
        $postdata['invoice_date'] = date("Y-m-d");
	    $postdata['account_id'] = 1;
        $postdata['account_type'] = 2;
        $postdata["status"] = 1;
        $postdata["amount"] = 0.00;
        
        $output = $this->Finance_model->create_update_invoice($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating adding new invoice failure
     * empty status and invoice date
     */
    function test_create_update_invoice_val2() {

        $output = $this->Finance_model->get_next_invoice_no();
        if($output['data'])
            $postdata['invoice_no'] = $output['data'];
        
        $postdata['invoice_type'] = 1;
        $postdata['invoice_date'] = null;
	    $postdata['account_id'] = 1;
        $postdata['account_type'] = 2;
        $postdata["status"] = null;
        $postdata["amount"] = 0.00;

        $acc_contact_srch['account_id'] = $postdata['account_id'];
        $acc_contact_srch['account_type'] = $postdata['account_type'];
        $acc_contacts_res = $this->Account_model->get_account_contacts_selection($acc_contact_srch);
        if($acc_contacts_res && isset($acc_contacts_res['data'])) {
            $postdata['contact_id'] = $acc_contacts_res['data'][0]['value'];
        }

        $output = $this->Finance_model->create_update_invoice($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating adding new invoice success
     */
    function test_create_update_invoice_suc1() {

        $output = $this->Finance_model->get_next_invoice_no();
        if($output['data'])
            $postdata['invoice_no'] = $output['data'];
        
        $postdata['invoice_type'] = 1;
        $postdata['invoice_date'] = date("Y-m-d");
	    $postdata['account_id'] = 113;
        $postdata['account_type'] = 2;
        $postdata["status"] = 1;
        $postdata["amount"] = 0.00;
        $postdata['invoice_shifts'] = [];

        $acc_contact_srch['account_id'] = $postdata['account_id'];
        $acc_contact_srch['account_type'] = $postdata['account_type'];
        $acc_contacts_res = $this->Account_model->get_account_contacts_selection($acc_contact_srch);
        if($acc_contacts_res && isset($acc_contacts_res['data'])) {
            $postdata['contact_id'] = $acc_contacts_res['data'][0]['value'];
        }

        $acc_shift_srch['account_id'] = $postdata['account_id'];
        $acc_shift_srch['account_type'] = $postdata['account_type'];
        $acc_shifts_res = $this->Schedule_model->get_paid_non_invoice_shifts($acc_shift_srch);
        if(is_array($acc_shifts_res) && !empty($acc_shifts_res['data'])) {
            $postdata['invoice_shifts'] = [$acc_shifts_res['data'][0]['id']];
        }

        $output = $this->Finance_model->create_update_invoice($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating updating existing invoice success
     */
    function test_create_update_invoice_suc2() {

        $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['id'] = $details->last_id;
        
        $inv_details_res = $this->Finance_model->get_invoice_details($postdata['id']);
        $postdata = (array) $inv_details_res['data'];
        $postdata['invoice_date'] = date("Y-m-d");
        $postdata["status"] = 3;
        $postdata["invoice_shifts"] = $this->Finance_model->get_invoice_shift_ids($postdata['id'], true);

        $output = $this->Finance_model->create_update_invoice($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating updating invoice line items failure
     * invalid unit rate and units
     */
    function test_add_update_invoice_line_items_fail1() {

        $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['invoice_id'] = $details->last_id;
        
        $inv_lineitems_res = $this->Finance_model->get_invoice_line_items_list((object) ["invoice_id" => $postdata['invoice_id']]);
        $invoice_line_items = object_to_array($inv_lineitems_res['data']);
        $invoice_line_items[0]['units'] = "as.121";
        $invoice_line_items[0]['unit_rate'] = "g.11";
        $postdata['invoice_line_items'] = $invoice_line_items;
        $postdata['category_check'] = true;
        $postdata['line_item_check'] = false;

        $output = $this->Finance_model->add_update_invoice_line_items($postdata, 1);
        return $this->assertFalse($output['status']);
    }

    /**
     * validating updating invoice line items success
     */
    function test_add_update_invoice_line_items_suc1() {

        $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
        if($details->last_id)
            $postdata['invoice_id'] = $details->last_id;
        
        $inv_lineitems_res = $this->Finance_model->get_invoice_line_items_list((object) ["invoice_id" => $postdata['invoice_id']]);
        $invoice_line_items = object_to_array($inv_lineitems_res['data']);
        $invoice_line_items[0]['units'] = "2";
        $invoice_line_items[0]['unit_rate'] = "0.50";
        $postdata['invoice_line_items'] = $invoice_line_items;
        $postdata['category_check'] = true;
        $postdata['line_item_check'] = false;

        $output = $this->Finance_model->add_update_invoice_line_items($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * validating archiving invoice line item success
     */
    function test_archive_invoice_line_item_suc1() {

        $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
        if($details->last_id)
            $invoice_id = $details->last_id;
        
        $inv_lineitems_res = $this->Finance_model->get_invoice_line_items_list((object) ["invoice_id" => $invoice_id]);
        
        $invoice_line_items = object_to_array($inv_lineitems_res['data']);
        if (empty($invoice_line_items)) {
            return $this->assertTrue(true);
        }
        $postdata['id'] = $invoice_line_items[0]['id'];

        $output = $this->Finance_model->archive_invoice_line_item($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful creation of the invoice pdf in AWS S3 bucket
     */
    function test_generate_invoice_pdf_suc1() {
        $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
        if($details->last_id)
            $invoice_id = $details->last_id;
        $postdata['id'] = $invoice_id;
        $output = $this->Finance_model->generate_invoice_pdf($postdata, 1);
        return $this->assertTrue($output['status']);
    }

    /**
     * testing successful emailing of the invoice pdf
     */
    // function test_send_invoice_mail_suc1() {
    //     $details = $this->basic_model->get_row('finance_invoice', array("MAX(id) AS last_id"));
    //     if($details->last_id)
    //         $invoice_id = $details->last_id;
    //     $postdata['ids'] = [$invoice_id];
    //     $output = $this->Finance_model->send_invoice_mail($postdata, 1);
    //     return $this->assertTrue($output['status']);
    // }
}
