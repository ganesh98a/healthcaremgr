<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_model extends CI_Model {

    var $pay_rate_status = [
        "0" => "Inactive",
        "1" => "Active"
    ];

    var $charge_rate_status = [
        "0" => "Inactive",
        "1" => "Active"
    ];

    var $timesheet_status = [
        "0" => "Draft",
        "1" => "Submitted",
        "2" => "Approved",
        "3" => "Pending Payment",
        "4" => "Paid"
    ];

    var $invoice_status = [
        "0" => "Open",
        "1" => "Invoice Sent",
        "2" => "Paid",
        "3" => "Cancelled",
        "4" => "Void/Write Off"
    ];

    var $invoice_status_final = [
        "2" => "Paid",
        "3" => "Cancelled",
        "4" => "Void/Write Off"
    ];

    var $invoice_status_grouped = [
        "0" => "Open",
        "1" => "Invoice Sent",
        "10" => "Final",
    ];

    var $invoice_types = [
        "1" => "Debtors",
        "2" => "SAMS",
        "3" => "COS (65+)",
        "4" => "NDIS",
        "5" => "SIL (NDA)"
    ];

    var $sa_fund_type = [
        "1" => "Portal", 
        "2" => "Plan", 
        "3" => "Self"
    ];

    // data items of pay rates
    private $pay_rate_items = array(
        "columns" => array("category", "award","role","pay_level","skill", "employment_type","start_date","end_date","amount","external_reference","description"),
        "required_columns" => array("category", "award","role","pay_level","skill", "employment_type","start_date","end_date","amount"), // Will be used by React.js to mark required fields with red asterisk.
    );

    // data items of charge rates
    private $charge_rate_items = array(
        "columns" => array("category","role","pay_level","skill", "cost_book","start_date","end_date","amount","external_reference"),
        "required_columns" => array("category","role","pay_level","skill", "cost_book","start_date","end_date","amount"), // Will be used by React.js to mark required fields with red asterisk.
    );

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->model('schedule/Schedule_model');
        $this->load->model('member/Member_model');
        $this->load->model('sales/Contact_model');
        $this->load->model('sales/ServiceAgreement_model');
        $this->warning = (object) ['is_warnable' => false, 'messages' => []];
    }

    /**
     * fetches all the invoice statuses
     */
    public function get_invoice_statuses() {
        $data = null;
        foreach($this->invoice_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the final invoice statuses
     */
    public function get_invoice_statuses_final() {
        $data = null;
        foreach($this->invoice_status_final as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the invoice statuses
     */
    public function get_invoice_statuses_grouped() {
        $data = null;
        foreach($this->invoice_status_grouped as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the invoice types
     */
    public function get_invoice_types() {
        $data = null;
        foreach($this->invoice_types as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the timesheet statuses
     */
    public function get_timesheet_statuses() {
        $data = null;
        foreach($this->timesheet_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * Getting distinct list of member id and external ref ids list of timesheets
     */
    public function get_unique_ext_emp_ids_of_timesheets($ids) {
        $select_column = ["(select mk.keypay_emp_id from tbl_keypay_kiosks_emp_mapping_for_member mk where mk.member_id = m.id and mk.archive = 0) as keypay_emp_id", "m.id as member_id"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_timesheet as ts');
        $this->db->join('tbl_member as m', 'ts.member_id = m.id and m.archive = 0', 'inner');
        $this->db->where("ts.archive", "0");
        $this->db->where("ts.id in (".implode(",",$ids).")");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $keypay_emp_ids = null;
        if($query->result_array()) {
            foreach ($query->result_array() as $val) {
                $keypay_emp_ids[$val['member_id']] = $val['keypay_emp_id'];
            }
        }
        return $keypay_emp_ids;
    }

    /**
     * Getting the min and max actual start dates range of list of timesheets
     */
    public function get_min_max_shifts_actual_dates($ids) {
        $select_column = ["min(date(actual_start_datetime)) as min_actual_start_datetime", "max(date(actual_start_datetime)) as max_actual_start_datetime"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_timesheet as ts');
        $this->db->join('tbl_member as m', 'ts.member_id = m.id and m.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id and s.archive = 0', 'inner');
        $this->db->where("ts.archive", "0");
        $this->db->where("ts.id in (".implode(",",$ids).")");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $keypay_emp_ids = null;
        if($query->result_array()) {
            $result = $query->result_array();
            return [$result[0]['min_actual_start_datetime'], $result[0]['max_actual_start_datetime']];
        }
        return false;
    }

    /**
     * Getting the list of line items of timeshees specific to a member
     */
    function get_bulk_timesheet_line_items($ids, $member_id) {
        $select_column = ["tsli.id", "tsli.units", "tsli.unit_rate", "tsli.total_cost", "tsli.external_reference", "tsli.category_id", "date(s.actual_start_datetime) as actual_start_date", "r.display_name as line_item_label"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_timesheet_line_item as tsli');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsli.timesheet_id and tsli.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id and s.archive = 0', 'inner');
        $this->db->join('tbl_member as m', 'ts.member_id = m.id and m.archive = 0', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsli.category_id and r.archive = 0', 'inner');
        $this->db->where("tsli.archive", "0");
        $this->db->where("ts.id in (".implode(",",$ids).")");
        $this->db->where("m.id",$member_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result_array();
    }

    /**
     * Fetching the timesheet line items from keypay which are payment processed and
     * marking relative timesheet line items as paid
     */
    public function get_paid_keypay_timesheets($adminId) {

        # adding timesheet line items into keypay
        $keypay_response = $this->Member_model->get_paid_keypay_timesheets($adminId);
        if (empty($keypay_response) || $keypay_response['status'] != true) {
            $response = ['status' => false, 'error' => $keypay_response['error']];
            return $response;
        }

        # no timesheets found but keypay fetch was successful?
        if(!is_array($keypay_response['data'])) {
            $return = ["status" => true, 'data_msg' => "Timesheet payments are yet to be processed since last run", 'import_id' => null];
            return $return;
        }

        # looping through all processed timesheet line items from keypay to find
        # their relavent hcm line item and updating its status
        $timesheets_processed = $timesheets_found = $timesheets_not_found = [];
        foreach($keypay_response['data'] as $row) {
            $line_item_id = $row;
            if(empty($line_item_id))
                continue;

            # finding timesheet and line item details
            $line_item_details = $this->get_timesheet_details_from_line_item($line_item_id);
            $timesheet_id = ($line_item_details && isset($line_item_details['timesheet_id'])) ? $line_item_details['timesheet_id'] : '';
            if(empty($timesheet_id))
                continue;

            if(array_search($timesheet_id, $timesheets_processed) === FALSE)
            $timesheets_processed[] = $timesheet_id;

            # updating the line item status as paid
            $upd_data['updated'] = DATE_TIME;
            $upd_data['updated_by'] = $adminId;
            $upd_data['archive'] = 0;
            $upd_data["is_paid"] = 1;
            $result = $this->basic_model->update_records("finance_timesheet_line_item", $upd_data, ["id" => $line_item_id]);

            # if timesheet itself is already paid then skipping it
            if($line_item_details['status'] == 4) {
                if(array_search($timesheet_id, $timesheets_found) === FALSE)
                    $timesheets_found[] = $timesheet_id;
                continue;
            }

            # checking the line items of coming timesheet, are all line items marked paid?
            $line_item_check = $this->basic_model->get_row('finance_timesheet_line_item', ['id'], ['timesheet_id' => $timesheet_id, 'archive' => 0, 'is_paid' => 0]);
            $unpaid_line_item_id = ($line_item_check && isset($line_item_check->id)) ? $line_item_check->id : '';
            if(empty($unpaid_line_item_id) && array_search($timesheet_id, $timesheets_found) === FALSE) {
                # mark the timesheet status as paid
                $upd_data2["id"] = $timesheet_id;
                $upd_data2["status"] = 4;
                $update_result = $this->update_timesheet_status($upd_data2, $adminId);
                if (!empty($update_result) && $update_result['status'] == true)
                    $timesheets_found[] = $timesheet_id;
            }
        }

        # preparing the summary counts
        $total_processed = count($timesheets_processed);
        $total_updated = count($timesheets_found);
        $total_not_updated = $total_processed - $total_updated;
        if($total_processed == 0) {
            return ["status" => true, "data_msg" => "Timesheet payments are yet to be processed since last run", "import_id" => null];
        }

        # inserting import job details into db
        $postdata["total_rows"] = $total_processed;
        $postdata["total_success_rows"] = $total_updated;
        $postdata["total_error_rows"] = $total_not_updated;
        $postdata["error_text"] = null;
        $postdata["summary_text"] = implode(",",$timesheets_processed);
        $postdata["created"] = DATE_TIME;
        $postdata["created_by"] = $adminId;
        $import_id = $this->basic_model->insert_records("admin_bulk_import", $postdata, $multiple = FALSE);

        # preparing stats to display to the user
        $data_msg = $total_processed. " ".(($total_processed > 1) ? "timesheets were" : "timesheet was")." found.";
        $data_msg .= $total_updated. " ".(($total_updated > 1) ? "timesheets were" : "timesheet was")." updated.";
        $data_msg .= $total_not_updated. " ".(($total_not_updated > 1) ? "timesheets were" : "timesheet was")." not updated.";

        $return = ["status" => true, 'data_msg' => $data_msg, 'import_id' => $import_id];
        return $return;
    }

    /**
     * finding timesheet, shift and member details using a list of timesheet ids
     */
    public function get_timesheet_shift_member_details($timesheet_ids) {

        # creating status sql query part
        $status_label = "(CASE ";
        foreach($this->timesheet_status as $k => $v) {
            $status_label .= " WHEN ts.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        $this->db->select("ts.timesheet_no, s.shift_no, m.fullname as member_label, ts.amount, DATE_FORMAT(ts.created,'%d/%m/%Y %h:%i %p') as date_created");
        $this->db->select($status_label);
        $this->db->from('tbl_finance_timesheet as ts');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id', 'inner');
        $this->db->join('tbl_member m', 'm.id = ts.member_id', 'inner');
        if($timesheet_ids)
            $this->db->where("ts.id in (".implode(",",$timesheet_ids).")");

        $this->db->where("ts.archive", "0");
        $this->db->where("s.archive", "0");
        $this->db->where("m.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->result_array();
    }

    /**
     * submitting timesheet line items into keypay
     */
    public function create_bulk_keypay_timesheet($ids, $adminId) {

        # finding the unique external employee ids of all the timesheet ids
        $keypay_emp_ids = $this->get_unique_ext_emp_ids_of_timesheets($ids);
        if(empty($keypay_emp_ids)) {
            return ["status" => false, "error" => "No members found of timesheets!"];
        }

        # fetching the timesheet line items list using combination of timesheet ids and member id
        $keypay_emp_line_items = null;
        foreach($keypay_emp_ids as $member_id => $keypay_emp_id) {
            $timesheet_line_items = $this->get_bulk_timesheet_line_items($ids, $member_id);

            if($timesheet_line_items)
                $keypay_emp_line_items[$keypay_emp_id] = $timesheet_line_items;
        }
        if(empty($keypay_emp_line_items)) {
            return ["status" => false, "error" => "No line items found of timesheets!"];
        }

        # fetching min and max actual start date of timesheets' shifts
        $dates_detail = $this->get_min_max_shifts_actual_dates($ids);
        if(empty($dates_detail)) {
            return ["status" => false, "error" => "No shift dates found of timesheets!"];
        }
        list($from_date, $to_date) = $dates_detail;

        # adding timesheet line items into keypay
        $keypay_response = $this->Member_model->create_keypay_timesheet_line($adminId, $from_date, $to_date, $keypay_emp_line_items);
        if (empty($keypay_response) || $keypay_response['status'] != true) {
            $response = ['status' => false, 'error' => $keypay_response['error']];
            return $response;
        }

        # changing status of timesheet to pending for payment
        foreach($ids as $id) {
            $upd_data["id"] = $id;
            $upd_data["status"] = 3;
            $update_result = $this->update_timesheet_status($upd_data, $adminId);
            if (empty($update_result) || $update_result['status'] != true)
                return $update_result;
        }        

        $label = count($ids)." timesheet".((count($ids) > 1) ? "s" : '');
        return ["status" => true, "msg" => "Successfully submitted {$label} into KeyPay"];
    }

    /** To disable shift edit for published time sheet */
    public function get_time_sheet_status_by_shift_id($shift_id) {
        $return = TRUE;
        $this->db->select('status');
        $this->db->from(TBL_PREFIX . 'finance_timesheet');
        $this->db->where(['shift_id' => $shift_id]);
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
       
        if(!empty($result) && $result[0]['status'] > 2) {
            $return = FALSE;
        }

        return $return;
    }

    /**
     * fetching the invoice information
     */
    public function get_invoice_details($id = null, $shift_id = null) {
        if (empty($id) && empty($shift_id)) return;

        # creating status sql query part
        $status_label = "(CASE ";
        foreach($this->invoice_status as $k => $v) {
            $status_label .= " WHEN i.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        # creating invoice type's sql query part
        $invoice_type_label = "(CASE ";
        foreach($this->invoice_types as $k => $v) {
            $invoice_type_label .= " WHEN i.invoice_type = {$k} THEN '{$v}'";
        };
        $invoice_type_label .= " ELSE '' END) as invoice_type_label";

        $this->db->select(["i.*", 
        "(CASE WHEN i.sa_managed_type = 1 THEN 'Portal Managed' WHEN i.sa_managed_type = 2 THEN 'Plan Managed' ELSE '' END) AS sa_managed_type_label",
        "concat(p.firstname,' ',p.lastname) as contact_label", "p.ndis_number"]);
        $this->db->select("r.display_name as cancel_void_reason_label");
        $this->db->select(("(select min(s.actual_start_datetime) from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0) as shift_start_date"));
        $this->db->select(("(select max(s.actual_start_datetime) from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0) as shift_end_date"));
        $this->db->select("(CASE WHEN i.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = i.account_id and p1.archive = 0) WHEN i.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = i.account_id and o.archive = 0) ELSE '' END) as account_label");
        $this->db->select("(CASE WHEN i.site_id IS NOT NULL THEN (select o.name from tbl_organisation o where o.id = i.site_id and o.archive = 0) ELSE '' END) as site_label");
        $this->db->select($status_label);
        $this->db->select($invoice_type_label);
        $this->db->from('tbl_finance_invoice as i');
        $this->db->join('tbl_person as p', 'p.id = i.contact_id and p.archive = 0', 'inner');
        $this->db->join('tbl_references as r', 'r.id = i.cancel_void_reason_id', 'left');
        if($id)
            $this->db->where("i.id", $id);

        $this->db->where("i.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Invoice not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
            $dataResult->account_person = [
                "value" => $val->account_id,
                "label" => $val->account_label,
                "account_type" => $val->account_type
            ];

            $dataResult->invoice_shifts = $this->get_invoice_shift_ids($val->id, true);
        }

        $dataResult->email_contact = [];
        if (isset($dataResult) == true && isset($dataResult->account_person) == true && empty($dataResult->account_person) == false) {
            $email_contact = $this->get_contact_for_account($dataResult->account_person['value'], $dataResult->account_person['account_type']);
            $dataResult->email_contact = $email_contact;
        }        

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * fetching timesheet and line item details
     */
    public function get_timesheet_details_from_line_item($line_item_id) {
        $this->db->select(["ts.id", "ts.status", "tsl.is_paid", "tsl.timesheet_id"]);
        $this->db->from('tbl_finance_timesheet_line_item as tsl');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsl.timesheet_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsl.category_id', 'inner');
        $this->db->where('ts.archive', 0);
        $this->db->where('tsl.archive', 0);
        $this->db->where('tsl.id', $line_item_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        if($result)
            return $result[0];
        else
            return false;
    }

    /**
     * fetching the timesheet information
     */
    public function get_timesheet_details($id = null, $shift_id = null) {
        if (empty($id) && empty($shift_id)) return;

        # creating status sql query part
        $status_label = "(CASE ";
        foreach($this->timesheet_status as $k => $v) {
            $status_label .= " WHEN ts.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        $this->db->select("ts.*, s.shift_no, m.fullname as member_label");
        
        $this->db->select([
            "concat(DATE_FORMAT(s.scheduled_start_datetime,'%h:%i %p'), ' - ', DATE_FORMAT(s.scheduled_end_datetime,'%h:%i %p')) as scheduled_shift_time",
            "concat(DATE_FORMAT(s.actual_start_datetime,'%h:%i %p'), ' - ', DATE_FORMAT(s.actual_end_datetime,'%h:%i %p')) as actual_shift_time",
            ]);
        $this->db->select("(select mk.keypay_emp_id from tbl_keypay_kiosks_emp_mapping_for_member mk where mk.member_id = m.id and mk.archive = 0) as keypay_emp_id");
        $this->db->select($status_label);
        $this->db->from('tbl_finance_timesheet as ts');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id', 'inner');
        $this->db->join('tbl_member m', 'm.id = ts.member_id', 'inner');

        if($shift_id)
            $this->db->where("ts.shift_id", $shift_id);

        if($id)
            $this->db->where("ts.id", $id);

        $this->db->where("ts.archive", "0");
        $this->db->where("s.archive", "0");
        $this->db->where("m.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('error' => "Timsheet not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        # for shift pre-selection
        $shift_details['label'] = $dataResult->shift_no;
        $shift_details['value'] = $dataResult->shift_id;
        $dataResult->shift_details = $shift_details;

        # fetching complete shift details
        $result = $this->Schedule_model->get_shift_details($dataResult->shift_id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }
        $dataResult->shift_full_details = (array) $result['data'];

        # for query pre-selection
        $dataResult->timesheet_query = $this->get_timesheet_query_ids($dataResult->id, true);
        $dataResult->timesheet_query_names = implode(",",$this->get_timesheet_query_ids($dataResult->id, false, true));

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * Updating the invoice status in bulk
     */
    function bulk_update_invoice_status($data, $adminId) {
        if (!isset($data['ids']) || !is_array($data['ids'])) {
            $response = ['status' => false, 'error' => "Missing IDs"];
            return $response;
        }

        foreach($data['ids'] as $id) {
            $newdata = null;
            $newdata['status'] = $data['status'];
            $newdata['id'] = $id;
            $upd_res = $this->update_invoice_status($newdata, $adminId);
            if($upd_res['status'] == false) {
                return $upd_res;
            }
        }

        $msg = count($data['ids']).(count($data['ids']) == 1 ? " invoice " : " invoices ")."updated successfully";
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * Updating the invoice status.
     */
    function update_invoice_status($data, $adminId) {

        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the invoice exist?
        $result = $this->get_invoice_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Invoice does not exist anymore."];
            return $response;
        }

        # updating status
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["status"] = $data['status'];
        $upd_data["cancel_void_reason_id"] = (isset($data['cancel_void_reason_id']) && $data['cancel_void_reason_id'] > 0) ? $data['cancel_void_reason_id'] : null;
        $upd_data["cancel_void_reason_notes"] = isset($data['cancel_void_reason_notes']) ? $data['cancel_void_reason_notes'] : null;
        $result = $this->basic_model->update_records("finance_invoice", $upd_data, ["id" => $id]);

        # adding a log entry
        $msg = "Invoice status is updated successfully";
        $this->add_timesheet_invoice_log($upd_data, $msg, $adminId, $id);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * Updating the timesheet status.
     */
    function update_timesheet_status($data, $adminId) {

        $id = isset($data['id']) ? $data['id'] : 0;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the timesheet exist?
        $result = $this->get_timesheet_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Timesheet does not exist anymore."];
            return $response;
        }

        # updating status
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["status"] = $data['status'];
        $result = $this->basic_model->update_records("finance_timesheet", $upd_data, ["id" => $id]);

        # adding a log entry
        $msg = "Timesheet status is updated successfully";
        $this->add_timesheet_invoice_log($upd_data, $msg, $adminId, $id);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
     * creating/updating invoice information
     */
    public function create_update_invoice($data, $adminId) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $invoice_id = $data['id'] ?? 0;

        # validation rule
        $validation_rules = [
            array('field' => 'invoice_type', 'label' => 'Invoice Type', 'rules' => 'required'),
            array('field' => 'invoice_date', 'label' => 'Invoice Date', 'rules' => 'required|valid_date_format[Y-m-d]', 'errors' => ['valid_date_format' => 'Incorrect Invoice Date']),
            array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            array('field' => 'account_id', 'label' => 'Account Id', 'rules' => 'required'),
            array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required'),
            array('field' => 'amount', 'label' => 'Amount', 'rules' => 'required|numeric'),
            array('field' => 'contact_id', 'label' => 'Contact Id', 'rules' => 'required'),
        ];

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }
        
        # calculating the invoice line items based on shift's actual timings
        # if only NDIS && account is participant then load line item from actual
        # else load line item from timesheet
        if ($data['account_type'] == 1 && $data['invoice_type'] == 4) {            
            $li_res = $this->calc_invoice_line_items_ndis($invoice_id, $data['invoice_shifts']);                
            $category_check = false;
            $line_item_check = true;
        } else {
            $li_res = $this->calc_invoice_line_items($invoice_id, $data['invoice_shifts']);
            $category_check = true;
            $line_item_check = false;
        }
        
        list($invoice_total, $invoice_line_items) = $li_res;

        $postdata['invoice_no'] = $data['invoice_no'];
        $postdata['invoice_type'] = $data['invoice_type'];
        $postdata['invoice_date'] = $data['invoice_date'];
	    $postdata['account_id'] = $data['account_id'];
        $postdata['contact_id'] = $data['contact_id'];
        $postdata['account_type'] = $data['account_type'];
        $postdata['site_id'] = $data['site_id'] ?? null;        
        $postdata['amount'] = $invoice_total??0;
        $postdata['status'] = $data['status'];
        $postdata['archive'] = 0;

        if (isset($data['service_agreement_id']) && !empty($data['service_agreement_id'])) {
            $postdata['sa_id'] = $data['service_agreement_id'];
        }
        if (isset($data['service_agreement_payment']) && !empty($data['service_agreement_payment']) && !empty($data['service_agreement_payment']['value'])) {
            $postdata['sa_managed_type'] = $data['service_agreement_payment']['value'];
        }

        if ($invoice_id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("finance_invoice", $postdata, ["id" => $invoice_id]);
        } else {
            $postdata["created"] = DATE_TIME;
            $postdata["created_by"] = $adminId;
            $invoice_id = $this->basic_model->insert_records("finance_invoice", $postdata, $multiple = FALSE);
        }

        # adding updating invoice shifts
        if (empty($data['id']))
            $this->add_update_invoice_shifts($invoice_id, $data['invoice_shifts'], $adminId);

        # adding updating invoice line items
        if (empty($data['id'])) {
            $invoice_li_data = [
                "invoice_id" => $invoice_id,
                "category_check" => $category_check,
                "line_item_check" => $line_item_check,
                "invoice_line_items" => $invoice_line_items?? []
            ];
            $line_items_res = $this->add_update_invoice_line_items($invoice_li_data, $adminId, true);
            if (empty($line_items_res) || $line_items_res['status'] != true) {
                return $line_items_res;
            }
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Invoice has been updated successfully.';
        } else {
            $msg = 'Invoice has been created successfully.';
        }

        # adding a log entry
        $this->add_timesheet_invoice_log($postdata, $msg, $adminId, isset($data['id'])?$data['id']:null);
        $response = ['status' => true, 'msg' => $msg, 'id' => $invoice_id];
        return $response;
    }

    /*
     * creating/updating timesheet information
     */
    public function create_update_timesheet($data, $adminId) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $timesheet_id = $data['id'] ?? 0;

        # validation rule
        $validation_rules = [
            array('field' => 'shift_id', 'label' => 'Shift', 'rules' => 'required'),
            array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required'),
	        array('field' => 'amount', 'label' => 'Amount', 'rules' => 'required|numeric')
        ];

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        $postdata['timesheet_no'] = $data['timesheet_no'];
	    $postdata['shift_id'] = $data['shift_id'];
        $postdata['member_id'] = $data['member_id'];
        $postdata['amount'] = $data['amount'];
        $postdata['status'] = $data['status'];
        $postdata['archive'] = 0;
        $postdata['is_exclude_ot'] = $data['is_exclude_ot'] ?? NULL;
        $postdata['ot_15_total'] = $data['ot_15_total'] ?? NULL;
        $postdata['ot_20_total'] = $data['ot_20_total'] ?? NULL;

        if ($timesheet_id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("finance_timesheet", $postdata, ["id" => $timesheet_id]);
        } else {
            $postdata["created"] = DATE_TIME;
            $postdata["created_by"] = $adminId;
            $timesheet_id = $this->basic_model->insert_records("finance_timesheet", $postdata, $multiple = FALSE);
        }

        # adding updating timesheet line items
        if(isset($data['timesheet_line_items'])) {
            $line_items_data['timesheet_id'] = $timesheet_id;
            $line_items_data['timesheet_line_items'] = $data['timesheet_line_items'];
            $line_items_data['shift_id'] = $data['shift_id'];
            $line_items_res = $this->add_update_timesheet_line_items($line_items_data, $adminId);
            if (empty($line_items_res) || $line_items_res['status'] != true) {
                return $line_items_res;
            }
        }

        # adding updating timesheet query
        if(isset($data['timesheet_query'])) {
            $query_data['timesheet_id'] = $timesheet_id;
            $query_data['timesheet_query'] = $data['timesheet_query'];
            $query_res = $this->add_update_timesheet_query($query_data, $adminId);
            if (empty($query_res) || $query_res['status'] != true) {
                return $query_res;
            }
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Timesheet has been updated successfully.';
        } else {
            $msg = 'Timesheet has been created successfully.';
        }

        # adding a log entry
        $this->add_timesheet_invoice_log($postdata, $msg, $adminId, isset($data['id'])?$data['id']:null);
        $response = ['status' => true, 'msg' => $msg, 'id' => $timesheet_id];
        return $response;
    }

    /**
     * fetching existing invoice account ids
     */
    public function get_invoice_account_ids($invoice_id, $key_pair = false) {
        $this->db->select(["s.account_id", "s.account_type", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END) as account_fullname"]);
        $this->db->from('tbl_finance_invoice_line_item as il');
        $this->db->join('tbl_finance_invoice as i', 'i.id = il.invoice_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = il.category_id', 'inner');
        $this->db->join('tbl_shift as s', 's.id = il.shift_id', 'inner');
        $this->db->where('i.archive', 0);
        $this->db->where('il.archive', 0);
        $this->db->where('s.archive', 0);
        $this->db->where('i.id', $invoice_id);
        $this->db->group_by('account_id', 'account_fullname');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($key_pair == true)
                $ids[] = ["label" => $row->account_fullname, "value" => $row->account_id, "account_type" => $row->account_type];
                else
                $ids[] = $row->account_id;
            }
        }
        return $ids;
    }

    /**
     * updating total with units and rate multiplication
     */
    public function cal_invoice_pdf_row_total(&$total, $units, $rate) {
        $total = round($total + ($units * $rate), 2);
    }

    /**
     * fetching existing invoice shift details for pdf listing
     */
    public function get_invoice_shift_for_pdf($invoice_id, $invoice_type = '') {
        $select_column = ["s.id", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y') as job_date", "DATE_FORMAT(s.actual_start_datetime,'%a') as job_day", "lower(concat(DATE_FORMAT(s.actual_start_datetime,'%l:%i%p'),'-',DATE_FORMAT(s.actual_end_datetime,'%l:%i%p'))) as actual_time", "am.fullname as member_fullname", "(CASE WHEN s.account_type = 2 THEN (select o1.id from tbl_organisation o1 where o1.id = s.account_id and o1.is_site = 1) ELSE '' END) as org_site_id", "s.actual_sa_id","i.account_type"];
        $this->db->select($select_column, false);
        $this->db->from('tbl_finance_invoice_shift as ish');
        $this->db->join('tbl_finance_invoice as i', 'i.id = ish.invoice_id and i.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ish.shift_id and s.archive = 0', 'inner');
        $this->db->join('tbl_finance_timesheet as t', 's.id = t.shift_id and t.archive = 0', 'inner');
        $this->db->join('tbl_member m', 'm.id = s.owner_id and m.archive = 0', 'inner');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id and asm.archive = 0', 'inner');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id and am.archive = 0', 'inner');
        $this->db->where("ish.archive", "0");
        $this->db->where('i.id', $invoice_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        $new_result = $prev_result = [];
        if($result) {
            foreach($result as $row) {
                $shift_breaks_line = $this->Schedule_model->get_shift_breaks_one_line($row['id'],2);
                $row['shift_time'] = $shift_breaks_line;
                
                $shift_breaks = $this->Schedule_model->get_shift_breaks_with_date_and_duration_one_line($row['id'], 2);

                //Add sleepover between the actual time
                if(!empty($shift_breaks['sleepover'])) {
                    $row['actual_time'] = str_replace('-', '-' . $shift_breaks['sleepover'] .'-', $row['actual_time']);
                }
                //Add break time at the end of actual time
                if(!empty($shift_breaks['unpaid'])) {
                    $row['actual_time'] = $row['actual_time'] . $shift_breaks['unpaid'];
                }
                               
                # fetching invoice & shift's line items
                $line_items_res = $this->get_invoice_shift_line_items($invoice_id, $row, $invoice_type);
                
                if(!($line_items_res['status'] == true && !empty($line_items_res['data']))) {
                    continue;
                }
                
                if(!empty($new_result)) {
                    $prev_result = $new_result;
                }

                if($invoice_type == 4 && $row['account_type'] == 1) {                    
                    $new_result = $this->get_ndis_invoice_line_items($line_items_res['data'], $row);                    
                } else {                   
                    $new_result = $this->get_invoice_line_items($line_items_res['data'], $row);
                }
                
                if($prev_result) {
                    $new_result = array_merge($prev_result, $new_result);
                }
            }
        }
        return ["status" => true, "data" => $new_result];
    }

    public function get_invoice_line_items($line_items, $row) {
        # preparing line items data in the format needed for PDF
                # setting a blank row
                $row['hours_worked'] = null;
                $row['rate'] = null;
                $row['sleepover'] = null;
                $row['hours_allowance'] = null;
                $row['rate_allowance'] = null;
                $row['expenses'] = null;
                $row['total_cost_exc'] = 0.00;
                $row['gst'] = 0.00;
                $row['total_cost_inc'] = 0.00;
                
                foreach($line_items as $li_row) {

                    # mon-fri, saturday and sunday and overtime group
                    if($li_row['key_name'] == 'mon_fri' || $li_row['key_name'] == 'saturday' || $li_row['key_name'] == 'sunday' ||
                         $li_row['key_name'] == 'overtime_15' || $li_row['key_name'] == 'overtime_20') {
                        if($row['hours_worked'])
                            $row['hours_worked'] .= ",";
                        $row['hours_worked'] .= $li_row['units'];

                        if($row['rate'])
                            $row['rate'] .= ",";
                        $row['rate'] .= "\$".$li_row['unit_rate'];

                        $this->cal_invoice_pdf_row_total($row['total_cost_exc'], $li_row['units'], $li_row['unit_rate']);
                    }

                    # sleepover weekend and weekday group
                    if($li_row['key_name'] == 'so_weekday' || $li_row['key_name'] == 'so_weekend') {
                        $row['sleepover'] = "\$".$li_row['total_cost'];

                        $row['total_cost_exc'] = round($row['total_cost_exc'] + $li_row['total_cost'], 2);
                    }

                    # shift allowance and night dury allowance group
                    if($li_row['key_name'] == 'shift_allowance' || $li_row['key_name'] == 'night_duty_allowance') {
                        if($row['hours_allowance'])
                            $row['hours_allowance'] .= ",";
                        $row['hours_allowance'] .= $li_row['units'];

                        if($row['rate_allowance'])
                            $row['rate_allowance'] .= ",";
                        $row['rate_allowance'] .= "\$".$li_row['unit_rate'];

                        $this->cal_invoice_pdf_row_total($row['total_cost_exc'], $li_row['units'], $li_row['unit_rate']);
                    }

                    # expenses group
                    if($li_row['key_name'] == 'personal_expenses') {
                        $row['expenses'] = "\$".$li_row['total_cost'];

                        $row['total_cost_exc'] = round($row['total_cost_exc'] + $li_row['total_cost'], 2);
                    }
                }
                $row['total_cost_exc_org'] = $row['total_cost_exc'];
                $row['gst_org'] = $row['gst'];
                $row['total_cost_inc_org'] = $row['total_cost_exc'];

                $row['total_cost_exc'] = "\$".number_format($row['total_cost_exc'],2);
                $row['gst'] = "\$".number_format($row['gst'],2);
                $row['total_cost_inc'] = $row['total_cost_exc'];
                $new_result[] = $row;

                return $new_result;
    }

    /**
     * To form NDIS line items and its rates for pdf generation
     * 
     * @param $line_items {array} line item details
     * @param $data {array} shift details
     * 
     * @return $new_result {array} line item details for pdf generation
     */
    public function get_ndis_invoice_line_items($line_items, $row) {
        # preparing line items data in the format needed for PDF
        # setting a blank row
        $row['hours_worked'] = null;
        $row['rate'] = null;
        
        $row['total_cost_exc'] = 0.00;
        $row['gst'] = 0.00;
        $row['total_cost_inc'] = 0.00;        
        $row['line_item_name'] = '';
        $row['line_item_number'] = '';

        $row['row_total_cost'] = null;

        foreach($line_items as $li_row) {
            
            if(!empty($row['line_item_number'])) {
                $row['line_item_number'] .=",";
            }

            $row['line_item_number'] .= $li_row['line_item_number'];

            if(!empty($row['line_item_name'])) {
                $row['line_item_name'] .=",";
            }
            $row['line_item_name'] .= $li_row['line_item_name'];
            
            if($row['hours_worked'])
                $row['hours_worked'] .= ",";
            $row['hours_worked'] .= $li_row['units'];

            if($row['rate'])
                $row['rate'] .= ",";
            $row['rate'] .= "\$".$li_row['unit_rate'];

            if($row['row_total_cost'])
                $row['row_total_cost'] .= ",";

            $row['row_total_cost'] .= "\$".$li_row['total_cost'];
            
            $row['total_cost_exc'] = round($row['total_cost_exc'] + ($li_row['units'] * $li_row['unit_rate']), 2);                 
        }

        $row['total_cost_exc_org'] = $row['total_cost_exc'];
        $row['gst_org'] = $row['gst'];
        $row['total_cost_inc_org'] = $row['total_cost_exc'];

        $row['total_cost_exc'] = "\$".number_format($row['total_cost_exc'],2);
        $row['gst'] = "\$".number_format($row['gst'],2);
        $row['total_cost_inc'] = $row['total_cost_exc'];
        $new_result[] = $row;
        
        return $new_result;
    }

    /**
     * fetching existing invoice shift ids
     */
    public function get_invoice_shift_ids($invoice_id, $ret_shift_ids = false, $key_pair = false) {
        $this->db->select(["ish.id", "s.shift_no", "ish.shift_id", "s.account_id", "s.account_type"]);
        $this->db->from('tbl_finance_invoice_shift as ish');
        $this->db->join('tbl_finance_invoice as i', 'i.id = ish.invoice_id', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ish.shift_id', 'inner');
        $this->db->where('i.archive', 0);
        $this->db->where('ish.archive', 0);
        $this->db->where('s.archive', 0);
        $this->db->where('i.id', $invoice_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($key_pair == true)
                    $ids[] = ["label" => $row->shift_no, "value" => $row->shift_id, "account_id" => $row->account_id, "account_type" => $row->account_type];
                else if($ret_shift_ids == true)
                    $ids[] = $row->shift_id;
                else
                    $ids[] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * fetching existing invoice line items
     */
    public function get_invoice_line_item_ids($invoice_id, $key_pair = false) {
        $this->db->select(["il.id", "r.key_name"]);
        $this->db->from('tbl_finance_invoice_line_item as il');
        $this->db->join('tbl_finance_invoice as ts', 'ts.id = il.invoice_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = il.category_id', 'inner');
        $this->db->where('ts.archive', 0);
        $this->db->where('il.archive', 0);
        $this->db->where('il.invoice_id', $invoice_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($key_pair == false)
                $ids[] = $row->id;
                else
                $ids[$row->key_name] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * fetching existing invoice line items by invoice id
     */
    public function get_invoice_line_item_ids_by_id($invoice_id) {
        $this->db->select(["il.id"]);
        $this->db->from('tbl_finance_invoice_line_item as il');
        $this->db->join('tbl_finance_invoice as ts', 'ts.id = il.invoice_id', 'inner');
        $this->db->where('il.archive', 0);
        $this->db->where('il.invoice_id', $invoice_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                $ids[] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * fetching existing timesheet query
     */
    public function get_timesheet_query_ids($timesheet_id, $catid_pair = false, $cat_pair = false) {
        $this->db->select(["tsq.id", "r.key_name", "tsq.category_id", "r.display_name"]);
        $this->db->from('tbl_finance_timesheet_query as tsq');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsq.timesheet_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsq.category_id', 'inner');
        $this->db->where('ts.archive', 0);
        $this->db->where('tsq.archive', 0);
        $this->db->where('tsq.timesheet_id', $timesheet_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($cat_pair == true)
                $ids[] = $row->display_name;
                else if($catid_pair == true)
                $ids[] = $row->category_id;
                else
                $ids[] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * fetching existing timesheet line items
     */
    public function get_timesheet_line_item_ids($timesheet_id, $key_pair = false) {
        $this->db->select(["tsl.id", "r.key_name"]);
        $this->db->from('tbl_finance_timesheet_line_item as tsl');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsl.timesheet_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsl.category_id', 'inner');
        $this->db->where('ts.archive', 0);
        $this->db->where('tsl.archive', 0);
        $this->db->where('tsl.timesheet_id', $timesheet_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($key_pair == false)
                $ids[] = $row->id;
                else
                $ids[$row->key_name] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * fetching charge rates list from role details (cost_book_id, role id, date and member roles)
     */
    public function get_chargerates_from_role_details($cost_book_id, $role_id, $check_date, $member_roles, $alt_return = false) {
        $select_column = ["cr.id", "cr.charge_rate_category_id", "r1.key_name as line_item_key", "cr.amount", "cr.external_reference"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_charge_rate as cr');
        $this->db->join('tbl_member_role as r', 'r.id = cr.role_id and r.archive = 0 and r.id = '.$role_id, 'inner');
        $this->db->join('tbl_references as r1', 'r1.id = cr.charge_rate_category_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r2', 'r2.id = cr.cost_book_id and r2.archive = 0', 'inner');
        $this->db->join('tbl_references as r3', 'r3.id = cr.pay_level_id and r3.archive = 0', 'inner');
        $this->db->join('tbl_references as r4', 'r4.id = cr.skill_level_id and r4.archive = 0', 'inner');
        $this->db->where("cr.archive", "0");
        $this->db->where("cr.role_id", $role_id);
        $this->db->where("cr.cost_book_id", $cost_book_id);
        $this->db->where("date(cr.start_date) <= '".$check_date."' and date(cr.end_date) >= '".$check_date."'");
        $this->db->group_start();
        foreach($member_roles as $role_info) {
            $this->db->or_where("cr.pay_level_id = ".$role_info['level']." and cr.skill_level_id = ".$role_info['pay_point']);
        }
        $this->db->group_end();
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rows = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($alt_return)
                $rows[$row->charge_rate_category_id] = $row->external_reference;
                else
                $rows[$row->line_item_key] = [$row->amount, $row->external_reference];
            }
        }
        return $rows;
    }

    /**
     * fetching pay rates list from role details (award_type_id, role id, award, pay level, pay point, employment type)
     */
    public function get_payrates_from_role_details($award_type_id, $role_id, $check_date, $member_roles, $alt_return = false) {
        $select_column = ["pr.id", "pr.pay_rate_category_id", "r1.key_name as line_item_key", "pr.amount", "pr.external_reference"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_pay_rate as pr');
        $this->db->join('tbl_member_role as r', 'r.id = pr.role_id and r.archive = 0 and r.id = '.$role_id, 'inner');
        $this->db->join('tbl_references as r1', 'r1.id = pr.pay_rate_category_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r2', 'r2.id = pr.pay_rate_award_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r3', 'r3.id = pr.pay_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r4', 'r4.id = pr.skill_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r5', 'r5.id = pr.employment_type_id and r1.archive = 0', 'inner');
        $this->db->where("pr.archive", "0");
        $this->db->where("pr.role_id", $role_id);
        $this->db->where("pr.pay_rate_award_id", $award_type_id);
        $this->db->where("date(pr.start_date) <= '".$check_date."' and date(pr.end_date) >= '".$check_date."'");
        $this->db->group_start();
        foreach($member_roles as $role_info) {
            $this->db->or_where("pr.pay_level_id = ".$role_info['level']." and pr.skill_level_id = ".$role_info['pay_point']." and pr.employment_type_id = ".$role_info['employment_type']);
        }
        $this->db->group_end();
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $rows = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($alt_return)
                $rows[$row->pay_rate_category_id] = $row->external_reference;
                else
                $rows[$row->line_item_key] = [$row->amount, $row->external_reference];
            }
        }
        return $rows;
    }

    /**
     * adding updating invoice shifts
     */
    public function add_update_invoice_shifts($invoice_id, $shifts, $adminId) {
        $existing_invoice_shift_ids = [];
        $selected_invoice_shift_ids = [];

        # fetching existing invoice line items
        $existing_invoice_shift_ids = $this->get_invoice_shift_ids($invoice_id);

        foreach($shifts as $shift_id) {
            $postdata = [
                "invoice_id" => $invoice_id,
                "shift_id" => $shift_id
            ];

            # adding/updating an entry of invoice line item
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("finance_invoice_shift", $postdata);
            }
            else {
                $id = $row['id'];
                $selected_invoice_shift_ids[] = $id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->update_records("finance_invoice_shift", $postdata, ["id" => $id]);
            }
        }

        # any existing invoice line items that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_invoice_shift_ids, $selected_invoice_shift_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->basic_model->update_records("finance_invoice_shift",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $rem_id]);
            }
        }
    }

    /**
     * getting invoice line items information of the added shifts
     */
    public function calc_invoice_line_items($invoice_id, $invoice_shifts) {

        $invoice_total = 0;
        $invoice_line_items = null;

        # fetching the associated reference ids of all pay rate and charge rate categories
        $existing_timesheet_line_items = $this->get_invoice_line_item_ids($invoice_id, $key_pair = true);
        $cat_ids = $this->get_pay_rate_ref_list("payrates_category", true);

        # going through all shifts to find its line items and appending invoice line items
        foreach($invoice_shifts as $shift_id) {
            # fetching shift information
            $result = $this->Schedule_model->get_shift_details($shift_id);
            if (empty($result) || $result['status'] != true) {
                $response = ['status' => false, 'error' => "Shift does not exist anymore."];
                return $response;
            }
            $shift_details = (array) $result['data'];
            $shift_member_id = $shift_details['accepted_shift_member_id'];

            # finding the shift member details
            $smresult = $this->Schedule_model->get_shift_member_details($shift_member_id, $shift_id);
            if (empty($smresult) || $smresult['status'] != true) {
                $response = ['status' => false, 'error' => "Shift member does not exist anymore."];
                return $response;
            }
            $shift_member_details = (array) $smresult['data'];

            # finding all active work types (roles) of member from current shift's work type
            $category_payrates = null;
            if($shift_details['role_id']) {
                $member_roles = $this->Member_model->get_member_active_roles($shift_member_details['member_id'], $shift_details['role_id'], date("Y-m-d"));

                # finding the pay rates information using the role details of member
                if($member_roles) {
                    $category_payrates = $this->get_chargerates_from_role_details($shift_details['cost_book_id'], $shift_details['role_id'], date("Y-m-d"), $member_roles);
                }
            }

            # fetching the list of timesheet line items of current shift
            $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "shift_id" => $shift_id];
            $line_items_res = $this->get_timesheet_line_items_list((object) $reqData);

            $line_items = null;
            if(empty($line_items_res['data']))
                continue;

            # calculating overall total from individual line item's total
            $line_items = object_to_array($line_items_res['data']);
            
            foreach($line_items as $row) {
                $line_item = $row['key_name'];

                # skipping first aid rows
                if($line_item == 'first_aid')
                    continue;

                $unit_rate = 0.00;
                $external_reference = '';
                if(isset($category_payrates[$line_item])) {
                    list($unit_rate, $external_reference) = $category_payrates[$line_item];
                }
                $total_cost = round($row['units'] * $unit_rate, 2);
                $invoice_total += $total_cost;

                $invoice_line_items[] = [
                    'id' => null,
                    'shift_id' => $shift_id,
                    'account_id' => $shift_details['account_id'],
                    'account_type' => $shift_details['account_type'],
                    'category_id' => $row['category_id'],
                    'units' => $row['units'],
                    'unit_rate' => $unit_rate,
                    'total_cost' => $total_cost,
                    'external_reference' => null
                ];
            }
        }
        return [$invoice_total, $invoice_line_items];
    }

    /**
     * adding updating invoice line items
     */
    public function add_update_invoice_line_items($data, $adminId, $upd_total = false) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        # manually validating data
        if(empty($data['invoice_id']))
            $errors[] = "Invoice id is missing";
        
        $category_check = $data['category_check'];
        $line_item_check = $data['line_item_check'];
        
        $cnt = $checkcnt = 0;
        $errors = $duplrows = null;
        if(isset($data['invoice_line_items']))
        foreach ($data['invoice_line_items'] as $row) {
            $valid = true;
            if(empty($row['account_id'])) {
                $errors[] = "Please provide the account for row-".($cnt+1);
            }
            if(empty($row['shift_id'])) {
                $errors[] = "Please provide the shift for row-".($cnt+1);
            }
            if(empty($row['category_id']) && $category_check) {
                $errors[] = "Please provide the item/category for row-".($cnt+1);
            }
            if(empty($row['line_item_id']) && $line_item_check) {
                $errors[] = "Please provide the line item for row-".($cnt+1);
            }
            else {
                $checkcnt = 0;
                if ($category_check) {
                    foreach ($data['invoice_line_items'] as $checkrow) {
                        if(!empty($checkrow['category_id']) && $row['category_id'] == $checkrow['category_id'] &&
                        !empty($checkrow['account_id']) && $row['account_id'] == $checkrow['account_id'] &&
                        !empty($checkrow['account_type']) && $row['account_type'] == $checkrow['account_type'] &&
                        !empty($checkrow['shift_id']) && $row['shift_id'] == $checkrow['shift_id'] &&
                        $cnt != $checkcnt && !isset($duplrows[$row['line_item_label']])) {
                            $errors[] = "Duplicate item/category ".$row['line_item_label'];
                            $duplrows[$row['line_item_label']] = 1;
                        }
                        $checkcnt++;
                    }
                }
                $checkcnt = 0;
                if ($line_item_check) {
                    foreach ($data['invoice_line_items'] as $checkrow) {
                        if(!empty($checkrow['line_item_id']) && $row['line_item_id'] == $checkrow['line_item_id'] && 
                        !empty($checkrow['shift_id']) && $row['shift_id'] == $checkrow['shift_id'] &&
                        $cnt != $checkcnt && !isset($duplrows[$row['line_item_id']])) {
                            $errors[] = "Duplicate line item ".$row['line_item_id'];
                            $duplrows[$row['line_item_id']] = 1;
                        }
                        $checkcnt++;
                    }
                }
            }
            if(empty($row['units'])) {
                $errors[] = "Please provide the units for row-".($cnt+1);
            }
            else if(!is_numeric($row['units'])) {
                $errors[] = "Please provide valid units for row-".($cnt+1);
            }
            if(empty($row['unit_rate']) && $row['unit_rate'] != 0) {
                $errors[] = "Please provide the unit rate for row-".($cnt+1);
            }
            else if(!is_numeric($row['unit_rate'])) {
                $errors[] = "Please provide valid unit rate for row-".($cnt+1);
            }
            $cnt++;
        }

        if($errors) {
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        $existing_invoice_line_item_ids = [];
        $selected_invoice_line_item_ids = [];

        # fetching existing invoice line items
        $existing_invoice_line_item_ids = $this->get_invoice_line_item_ids_by_id($data['invoice_id']);
        $invoice_id = $data['invoice_id'];
        
        foreach($data['invoice_line_items'] as $row) {
            $postdata = [
                "invoice_id" => $invoice_id,
                "shift_id" => $row['shift_id'],
                "units" => isset($row['units'])?$row['units']:0,
                "unit_rate" => isset($row['unit_rate'])?$row['unit_rate']:0,
                "total_cost" => isset($row['total_cost'])?$row['total_cost']:0,
                "external_reference" => isset($row['external_reference'])?$row['external_reference']:''
            ];

            if (isset($row['category_id']) && $row['category_id'] != '' && $row['category_id'] != null) {
                $postdata["category_id"] = intVal($row['category_id']);
            }

            if (isset($row['line_item_id']) && $row['line_item_id'] != '' && $row['line_item_id'] != null) {
                $postdata["line_item_id"] = intVal($row['line_item_id']);
            }

            # adding/updating an entry of invoice line item
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("finance_invoice_line_item", $postdata);
            }
            else {
                $id = $row['id'];
                $selected_invoice_line_item_ids[] = $id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->update_records("finance_invoice_line_item", $postdata, ["id" => $id]);
            }
        }

        # any existing invoice line items that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_invoice_line_item_ids, $selected_invoice_line_item_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->basic_model->update_records("finance_invoice_line_item",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $rem_id]);
            }
        }

        # if the invoice total needs updating
        if($upd_total) {
            $this->refresh_invoice_amount($data['invoice_id'], $adminId);
        }

        $response = ['status' => true, 'msg' => 'Successfully updated the invoice line items'];
        return $response;
    }

    /**
     * adding updating timesheet query
     */
    public function add_update_timesheet_query($data, $adminId) {

        $existing_timesheet_query_ids = [];
        $selected_timesheet_query_ids = [];

        # fetching existing timesheet query
        $existing_timesheet_query_ids = $this->get_timesheet_query_ids($data['timesheet_id']);

        $full_total = 0;
        foreach($data['timesheet_query'] as $category_id) {

            $postdata = [
                "timesheet_id" => $data['timesheet_id'],
                "category_id" => $category_id
            ];

            # adding/updating an entry of timesheet query
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("finance_timesheet_query", $postdata);
            }
            else {
                $id = $row['id'];
                $selected_timesheet_query_ids[] = $id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->update_records("finance_timesheet_query", $postdata, ["id" => $id]);
            }
        }

        # any existing timesheet query that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_timesheet_query_ids, $selected_timesheet_query_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->archive_timesheet_query(["id" => $rem_id], $adminId, false);
            }
        }

        $response = ['status' => true, 'msg' => 'Successfully updated the timesheet query'];
        return $response;
    }

    /**
     * adding updating timesheet line items
     */
    public function add_update_timesheet_line_items($data, $adminId, $upd_total = false) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        # manually validating data
        if(empty($data['timesheet_id']))
            $errors[] = "Timesheet id is missing";
        if(empty($data['shift_id']))
            $errors[] = "Shift id is missing";

        $cnt = $checkcnt = 0;
        $errors = $duplrows = null;
        if(isset($data['timesheet_line_items']))
        foreach ($data['timesheet_line_items'] as $row) {
            $valid = true;
            if(empty($row['category_id'])) {
                $errors[] = "Please provide the item/category for row-".($cnt+1);
            }
            else {
                $checkcnt = 0;
                foreach ($data['timesheet_line_items'] as $checkrow) {
                    if(!empty($checkrow['category_id']) && $row['category_id'] == $checkrow['category_id'] && $cnt != $checkcnt && !isset($duplrows[$row['line_item_label']])) {
                        $errors[] = "Duplicate item/category ".$row['line_item_label'];
                        $duplrows[$row['line_item_label']] = 1;
                    }
                    $checkcnt++;
                }
            }
            if(empty($row['units'])) {
                $errors[] = "Please provide the units for row-".($cnt+1);
            }
            else if(!is_numeric($row['units'])) {
                $errors[] = "Please provide valid units for row-".($cnt+1);
            }
            if(empty($row['unit_rate']) && $row['unit_rate'] != 0) {
                $errors[] = "Please provide the unit rate for row-".($cnt+1);
            }
            else if(!is_numeric($row['unit_rate'])) {
                $errors[] = "Please provide valid unit rate for row-".($cnt+1);
            }
            $cnt++;
        }

        if($errors) {
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        $shift_id = $data['shift_id'];

        # does the shift exist?
        $result = $this->Schedule_model->get_shift_details($shift_id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }
        $shift_details = (array) $result['data'];
        $shift_member_id = $shift_details['accepted_shift_member_id'];

        # finding the shift member details
        $smresult = $this->Schedule_model->get_shift_member_details($shift_member_id, $shift_id);
        if (empty($smresult) || $smresult['status'] != true) {
            $response = ['status' => false, 'error' => "Shift member does not exist anymore."];
            return $response;
        }
        $shift_member_details = (array) $smresult['data'];

        # finding all active work types (roles) of member from current shift's work type
        # finding the pay rates information using the role details of member
        $category_payrates = null;
        if($shift_details['role_id']) {
            $member_roles = $this->Member_model->get_member_active_roles($shift_member_details['member_id'], $shift_details['role_id'], date("Y-m-d"));

            if($member_roles) {
                $category_payrates = $this->get_payrates_from_role_details($shift_details['award_type_id'], $shift_details['role_id'], date("Y-m-d"), $member_roles, true);
            }
        }

        $existing_timesheet_line_item_ids = [];
        $selected_timesheet_line_item_ids = [];

        # fetching existing timesheet line items
        $existing_timesheet_line_item_ids = $this->get_timesheet_line_item_ids($data['timesheet_id']);

        $full_total = 0;
        foreach($data['timesheet_line_items'] as $row) {
            $unit_rate = $units = $total_cost = 0;
            if(!empty($row['units']))
                $row['units'] = number_format($row['units'],2,".","");
            if(!empty($row['unit_rate']))
                $row['unit_rate'] = number_format($row['unit_rate'],2,".","");
            $row['total_cost'] = number_format(($row['units'] * $row['unit_rate']),2,".","");
            $full_total += $row['total_cost'];

            $postdata = [
                "timesheet_id" => $data['timesheet_id'],
                "category_id" => $row['category_id'],
                "units" => isset($row['units'])?$row['units']:0,
                "unit_rate" => isset($row['unit_rate'])?$row['unit_rate']:0,
                "total_cost" => isset($row['total_cost'])?$row['total_cost']:0,
                "external_reference" => isset($category_payrates[$row['category_id']])?$category_payrates[$row['category_id']]:'',
                "order" => isset($row['order'])?$row['order']:0,
            ];

            # adding/updating an entry of timesheet line item
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("finance_timesheet_line_item", $postdata);
            }
            else {
                $id = $row['id'];
                $selected_timesheet_line_item_ids[] = $id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->update_records("finance_timesheet_line_item", $postdata, ["id" => $id]);
            }
        }

        # any existing timesheet line items that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_timesheet_line_item_ids, $selected_timesheet_line_item_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->archive_timesheet_line_item(["id" => $rem_id], $adminId, false);
            }
        }

        # if the timesheet total needs updating
        if($upd_total) {
            $this->refresh_timesheet_amount($data['timesheet_id'], $adminId);
        }

        $response = ['status' => true, 'msg' => 'Successfully updated the timesheet line items'];
        return $response;
    }

    /**
     * fetching the payrate categories
     */
    public function get_payrates_categories() {
        $category_payrates = $this->get_pay_rate_ref_list("payrates_category");
        $return = ["status" => true, "data" => $category_payrates];
        return $return;
    }

    /**
     * fetching the reference data of invoice line items
     */
    public function get_invoice_line_items_ref_data($invoice_id) {
        $category_option = $this->get_pay_rate_ref_list("payrates_category");
        $account_option = $this->get_invoice_account_ids($invoice_id, true);
        $shift_option = $this->get_invoice_shift_ids($invoice_id, false, true);
        $return = ["status" => true, "data" => [
            "category_option" => $category_option,
            "account_option" => $account_option,
            "shift_option" => $shift_option
        ]];
        return $return;
    }

    /**
     * archiving timesheet query
     */
    function archive_timesheet_query($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("finance_timesheet_query", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        $msg_title = "Successfully archived timesheet query";
        $this->add_finance_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * archiving invoice line item
     */
    function archive_invoice_line_item($data, $adminId, $update_total = true) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # getting invoice id
        $row = $this->basic_model->get_row('finance_invoice_line_item', ['invoice_id'], ['id' => $id, 'archive' => 0]);
        $invoice_id = ($row && isset($row->invoice_id)) ? $row->invoice_id : '';

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("finance_invoice_line_item", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # updating the invoice amount total
        if($update_total)
            $this->refresh_invoice_amount($invoice_id, $adminId);

        $msg_title = "Successfully archived line item";
        $this->add_finance_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * archiving timesheet line item
     */
    function archive_timesheet_line_item($data, $adminId, $update_total = true) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # getting timesheet id
        $row = $this->basic_model->get_row('finance_timesheet_line_item', ['timesheet_id'], ['id' => $id, 'archive' => 0]);
        $timesheet_id = ($row && isset($row->timesheet_id)) ? $row->timesheet_id : '';

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("finance_timesheet_line_item", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # updating the timesheet amount total
        if($update_total)
            $this->refresh_timesheet_amount($timesheet_id, $adminId);

        $msg_title = "Successfully archived line item";
        $this->add_finance_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /*
     * To fetch the timesheet line items list
     */
    public function get_timesheet_line_items_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 9999;
        $page = $reqData->page?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("tsli.units", "tsli.unit_rate", "tsli.total_cost", "r.display_name");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new filters
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "units", "unit_rate", "total_cost", "external_reference", "category_id", "actual_start_date", "line_item_label", "key_name"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tsli.order';
            $direction = 'ASC';
        }

        $select_column = ["tsli.id", "tsli.units", "tsli.unit_rate", "tsli.total_cost", "tsli.external_reference", "tsli.category_id", "date(s.actual_start_datetime) as actual_start_date", "r.display_name as line_item_label", "r.key_name"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_timesheet_line_item as tsli');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsli.timesheet_id and tsli.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id and s.archive = 0', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsli.category_id and r.archive = 0', 'inner');
        $this->db->where("tsli.archive", "0");
        if(!empty($reqData->timesheet_id))
            $this->db->where("ts.id", $reqData->timesheet_id);
        if(!empty($reqData->shift_id))
            $this->db->where("ts.shift_id", $reqData->shift_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        
        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched timesheets list successfully',"last_query" => $last_query);
        
    }

    /**
     * updates the invoice amount after going through all line items and total cost of them
     */
    public function refresh_invoice_amount($invoice_id, $adminId) {

        # fetching the list of line items
        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "invoice_id" => $invoice_id];
        $line_items_res = $this->get_invoice_line_items_list((object) $reqData);

        # calculating overall total from individual line item's total
        $overall_total = 0;
        if(!empty($line_items_res['data'])) {
            $line_items = object_to_array($line_items_res['data']);
            foreach($line_items as $row) {
                $overall_total += $row['total_cost'];
            }
        }

        $overall_total = number_format($overall_total,2,".","");
        $this->basic_model->update_records("finance_invoice",
            ["amount" => $overall_total, "updated" => DATE_TIME, "updated_by" => $adminId],
            ["id" => $invoice_id]);
        return true;
    }

    /**
     * updates the timesheet amount after going through all line items and total cost of them
     */
    public function refresh_timesheet_amount($timesheet_id, $adminId) {

        # fetching the list of line items
        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $timesheet_id];
        $line_items_res = $this->get_timesheet_line_items_list((object) $reqData);

        # calculating overall total from individual line item's total
        $overall_total = 0;
        if(!empty($line_items_res['data'])) {
            $line_items = object_to_array($line_items_res['data']);
            foreach($line_items as $row) {
                $overall_total += $row['total_cost'];
            }
        }

        $overall_total = number_format($overall_total,2,".","");
        $this->basic_model->update_records("finance_timesheet",
            ["amount" => $overall_total, "updated" => DATE_TIME, "updated_by" => $adminId],
            ["id" => $timesheet_id]);
        return true;
    }

        /*
     * To fetch the invoice shifts list
     */
    public function get_invoice_shifts_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 9999;
        $page = $reqData->page?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("s.shift_no", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y')", "concat(DATE_FORMAT(s.actual_start_datetime,'%h:%i %p'),' - ',DATE_FORMAT(s.actual_end_datetime,'%h:%i %p'))", "am.fullname","(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END)", "s.actual_duration", "t.timesheet_no", "t.amount");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new filters
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "shift_no", "account_id", "account_type", "actual_start_date", "actual_time", "member_fullname", "member_id", "scheduled_duration","account_fullname", "actual_duration", "timesheet_no", "timesheet_amount", "timesheet_id"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ish.id';
            $direction = 'DESC';
        }

        $select_column = ["s.id", "s.shift_no", "s.account_id", "s.account_type", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y') as actual_start_date", "concat(DATE_FORMAT(s.actual_start_datetime,'%h:%i %p'),' - ',DATE_FORMAT(s.actual_end_datetime,'%h:%i %p')) as actual_time", "am.fullname as member_fullname", "am.id as member_id", "s.scheduled_duration","(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END) as account_fullname", "s.actual_duration", "t.timesheet_no", "t.amount as timesheet_amount", "t.id as timesheet_id"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_invoice_shift as ish');
        $this->db->join('tbl_finance_invoice as i', 'i.id = ish.invoice_id and i.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ish.shift_id and s.archive = 0', 'inner');
        $this->db->join('tbl_finance_timesheet as t', 's.id = t.shift_id and t.archive = 0', 'inner');
        $this->db->join('tbl_member m', 'm.id = s.owner_id and m.archive = 0', 'inner');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id and asm.archive = 0', 'inner');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id and am.archive = 0', 'inner');
        $this->db->where("ish.archive", "0");
        $this->db->where("i.id", $reqData->invoice_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; #$ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched invoices list successfully',"last_query" => $last_query);
        return $return;
    }

    /*
     * To fetch the invoice line items list specific to a shift
     */
    public function get_invoice_shift_line_items($invoice_id, $row, $invoice_type = '') {

        $select_column = ["ili.id", "ili.category_id", "ili.shift_id", "ili.units", "ili.unit_rate", "ili.total_cost", "ili.external_reference"];

        if($invoice_type == 4 && $row['account_type'] == 1) {
            $select_column = array_merge($select_column, ["fli.line_item_number", "fli.line_item_name"]);
            $this->db->join('tbl_finance_line_item as fli', 'fli.id = ili.line_item_id', 'inner');
        } else {
            $this->db->join('tbl_references as r', 'r.id = ili.category_id and r.archive = 0', 'inner');
            $select_column = array_merge($select_column, ["r.display_name as line_item_label", "r.key_name"]);     
        }

        $this->db->select($select_column, false);
        $this->db->from('tbl_finance_invoice_line_item as ili');
        $this->db->join('tbl_finance_invoice as i', 'i.id = ili.invoice_id and ili.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ili.shift_id and s.archive = 0', 'inner');
        $this->db->where("ili.archive", "0");
        $this->db->where("i.id", $invoice_id);
        $this->db->where("ili.shift_id", $row['id']);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        
        return array('data' => $result, 'status' => true);
    }

    /*
     * To fetch the invoice line items list
     */
    public function get_invoice_line_items_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 9999;
        $page = $reqData->page?? 0;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("s.shift_no", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END)", "ili.units", "ili.unit_rate", "ili.total_cost", "r.display_name");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new filters
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "line_item_id", "line_item_code", "shift_no", "account_id", "account_type", "account_fullname", "category_id", "shift_id", "units", "unit_rate", "total_cost", "external_reference", 
                             "line_item_label","key_name", "actual_sa_id" ];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.id';
            $direction = 'ASC';
        }

        $select_column = ["ili.id", "li.id as line_item_id", "li.line_item_number as line_item_code", "s.shift_no", "s.account_id", "s.account_type", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END) as account_fullname", "ili.category_id", "ili.shift_id", "ili.units", "ili.unit_rate", "ili.total_cost", "ili.external_reference", 
        "(
            CASE 
                WHEN ili.category_id IS NULL THEN li.line_item_name
                ELSE r.display_name
            END
        ) as line_item_label",
        "r.key_name",
        "s.actual_sa_id"
        ];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_invoice_line_item as ili');
        $this->db->join('tbl_finance_invoice as i', 'i.id = ili.invoice_id and ili.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ili.shift_id and s.archive = 0', 'inner');
        $this->db->join('tbl_references as r', 'r.id = ili.category_id and r.archive = 0', 'LEFT');
        $this->db->join('tbl_finance_line_item as li', 'li.id = ili.line_item_id', 'LEFT');
        $this->db->where("ili.archive", "0");
        $this->db->where("i.id", $reqData->invoice_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched invoices list successfully',"last_query" => $last_query);
        return $return;
    }

    /**
     * fetches the next number in creating invoice
     */
    public function get_next_invoice_no() {
        # finding how many got added so far
        $details = $this->basic_model->get_row('finance_invoice', array("count(id) AS total"));
        $nextno = "1";
        if(!empty($details) && isset($details->total)) {
            $nextno = $details->total + 1;
        }
        $formatted_value = "INV".sprintf("%09d", $nextno);
        return array('status' => true, 'data' => $formatted_value);
    }

    /**
     * fetches the next number in creating timesheet
     */
    public function get_next_timesheet_no() {
        # finding how many got added so far
        $details = $this->basic_model->get_row('finance_timesheet', array("MAX(id) AS total"));
        $nextno = "1";
        if(!empty($details) && isset($details->total)) {
            $nextno = $details->total + 1;
        }
        $formatted_value = "TS".sprintf("%09d", $nextno);
        return array('status' => true, 'data' => $formatted_value);
    }

    /*
	 * used by the add/edit/delete timesheet/invoice to insert a log entry on
     */
    public function add_timesheet_invoice_log($data, $title, $adminId, $id) {
    	$this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /*
     * fetching the reference list of one reference type of pay rates
     */
    public function get_pay_rate_ref_list($keyname, $return_key = false) {
        if($return_key)
            $this->db->select(["r.key_name as label", 'r.id as value']);
        else
            $this->db->select(["r.display_name as label", 'r.id as value']);

        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $query = $this->db->get();
        if($query->num_rows() > 0 && $return_key == false)
            return $query->result_array();
        else if($query->num_rows() > 0 && $return_key == true) {
            $retrow = null;
            foreach($query->result_array() as $row) {
                $retrow[$row['label']] = $row['value'];
            }
            return $retrow;
        }
    }

    /*
     * checking if the reference value exists for a given category
     */
    public function find_reference_id($keyname, $display_name) {
        $this->db->select(["r.id"]);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $this->db->where("lower(r.display_name)", $display_name);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $rows = $query->result_array();
            return $rows[0]['id'];
        }
        return 0;
    }

    /*
     * fetching the reference data of timesheets
     */
    public function get_timesheet_ref_data() {
        $status_res = $this->get_timesheet_statuses();
        $result["status_options"] = $status_res['data'];
        $result["query_options"] = $this->get_pay_rate_ref_list("timesheet_query");
        $return = ["status" => true, "data" => $result];
        return $return;
    }

    /*
     * fetching the reference data of invoice
     */
    public function get_invoice_ref_data() {
        $status_res = $this->get_invoice_statuses();
        $types_res = $this->get_invoice_types();
        $result["status_options"] = $status_res['data'];
        $result["invoice_type_options"] = $types_res['data'];
        $return = ["status" => true, "data" => $result];
        return $return;
    }

    /*
     * fetches all the invoice cancell and void reasons list
     */
    function get_invoice_cancel_void_reasons() {
        $cancel_reason_option = $this->get_pay_rate_ref_list("invoice_cancel_reason");
        $void_reason_option = $this->get_pay_rate_ref_list("invoice_void_reason");
        $data = ["cancel_reason_option" => $cancel_reason_option, "void_reason_option" => $void_reason_option];
        $return = ["status" => true, "data" => $data];
        return $return;
    }

    /*
     * fetching the reference data of pay rates
     */
    public function get_pay_rate_ref_data() {
        $result["payrates_category_options"] = $this->get_pay_rate_ref_list("payrates_category");
        $result["payrates_award_options"] = $this->get_pay_rate_ref_list("payrates_award");
        $result["employment_type_options"] = $this->get_pay_rate_ref_list("employment_type");
        $result["skill_level_options"] = $this->get_pay_rate_ref_list("skills");
        $result["pay_level_options"] = $this->get_pay_rate_ref_list("pay_levels");
        $status_options = [];
        foreach($this->pay_rate_status as $key => $val) {
            $status_options[] = ["label" => $val, "value" => $key];
        }
        $result["status_options"] = $status_options;
        $return = ["status" => true, "data" => $result];
        return $return;
    }

    /*
     * fetching the reference data of charge rates
     */
    public function get_charge_rate_ref_data() {
        $result["chargerates_category_options"] = $this->get_pay_rate_ref_list("payrates_category");
        $result["cost_book_options"] = $this->get_pay_rate_ref_list("cost_book");
        $result["skill_level_options"] = $this->get_pay_rate_ref_list("skills");
        $result["pay_level_options"] = $this->get_pay_rate_ref_list("pay_levels");
        $status_options = [];
        foreach($this->pay_rate_status as $key => $val) {
            $status_options[] = ["label" => $val, "value" => ($key+1)];
        }
        $result["status_options"] = $status_options;
        $return = ["status" => true, "data" => $result];
        return $return;
    }

    /**
     * returns validation rules based on argument (either through add/edit form or bulk import)
     */
    public function get_pay_rate_validation_rules($bulk_import = false) {
        $rules1 = $rules2 = [];
        if($bulk_import == false) {
            $rules1 = [
                array('field' => 'pay_rate_category_id', 'label' => 'Category', 'rules' => 'required'),
                array('field' => 'pay_rate_award_id', 'label' => 'Award', 'rules' => 'required'),
                array('field' => 'role_id', 'label' => 'Work Type', 'rules' => 'required'),
                array('field' => 'pay_level_id', 'label' => 'Pay Level', 'rules' => 'required'),
                array('field' => 'skill_level_id', 'label' => 'Skill Level', 'rules' => 'required'),
                array('field' => 'employment_type_id', 'label' => 'Employment Type', 'rules' => 'required')
            ];
        }

        $rules2 = [
            array(
                'field' => 'start_date', 'label' => 'Start Date', 'rules' => 'required|valid_date_format[Y-m-d]',
                'errors' => [
                    'valid_date_format' => 'Incorrect Start Date',
                ]
            ),
            array(
                'field' => 'end_date', 'label' => 'End Date', 'rules' => 'required|valid_date_format[Y-m-d]',
                'errors' => [
                    'valid_date_format' => 'Incorrect End Date',
                ]
            ),
            array('field' => 'amount', 'label' => 'Amount', 'rules' => 'required|numeric')
        ];
        return array_merge($rules1, $rules2);
    }

    /**
     * returns validation rules based on argument (either through add/edit form or bulk import)
     */
    public function get_charge_rate_validation_rules($bulk_import = false) {
        $rules1 = $rules2 = [];
        if($bulk_import == false) {
            $rules1 = [
                array('field' => 'charge_rate_category_id', 'label' => 'Category', 'rules' => 'required'),
                array('field' => 'role_id', 'label' => 'Work Type', 'rules' => 'required'),
                array('field' => 'pay_level_id', 'label' => 'Pay Level', 'rules' => 'required'),
                array('field' => 'skill_level_id', 'label' => 'Skill Level', 'rules' => 'required'),
                array('field' => 'cost_book_id', 'label' => 'Cost Book', 'rules' => 'required')
            ];
        }

        $rules2 = [
            array(
                'field' => 'start_date', 'label' => 'Start Date', 'rules' => 'required|valid_date_format[Y-m-d]',
                'errors' => [
                    'valid_date_format' => 'Incorrect Start Date',
                ]
            ),
            array(
                'field' => 'end_date', 'label' => 'End Date', 'rules' => 'required|valid_date_format[Y-m-d]',
                'errors' => [
                    'valid_date_format' => 'Incorrect End Date',
                ]
            ),
            array('field' => 'amount', 'label' => 'Amount', 'rules' => 'required|numeric')
        ];
        return array_merge($rules1, $rules2);
    }

    /**
     * creates a new charge rate record or updates an existing one
     */
    function create_update_charge_rate($data, $adminId, $bulk_import = false, $exist_errors = []) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $chargerate_id = $data['id'] ?? 0;

        # validation rule
        $validation_rules = $this->get_charge_rate_validation_rules($bulk_import);

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        $errors = [];
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
        }

        if($errors || $exist_errors) {
            $errors = array_merge($exist_errors,$errors);
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        # checking list of business rules for creating charge rates
        $br_check = $this->charge_rates_business_rules($data['charge_rate_category_id'], $data['role_id'], $data['pay_level_id'], $data['skill_level_id'], $data['cost_book_id'], $data['start_date'], $data['end_date'], isset($data['external_reference'])?$data['external_reference']:null, $chargerate_id);
        if(isset($br_check['status']) && $br_check['status'] == false) {
            return $br_check;
        }

        $postdata = [
            "charge_rate_category_id" => isset($data['charge_rate_category_id'])?$data['charge_rate_category_id']:0,
            "role_id" => isset($data['role_id'])?$data['role_id']:0,
            "pay_level_id" => isset($data['pay_level_id'])?$data['pay_level_id']:0,
            "skill_level_id" => isset($data['skill_level_id'])?$data['skill_level_id']:0,
            "cost_book_id" => isset($data['cost_book_id'])?$data['cost_book_id']:0,
            "start_date" => date("Y-m-d H:i:s", strtotime($data['start_date'])),
            "end_date" => date("Y-m-d H:i:s", strtotime($data['end_date'])),
            "amount" => isset($data['amount'])?$data['amount']:0,
            "external_reference" => isset($data['external_reference'])?$data['external_reference']:null,
            "description" => isset($data['description'])?$data['description']:null,
            "status" => isset($data['status'])?$data['status']:null
        ];

        # adding an entry of charge rate
        if(!$chargerate_id) {
            $postdata['created'] = DATE_TIME;
            $postdata['created_by'] = $adminId;
            $postdata['archive'] = 0;

            $chargerate_id = $this->basic_model->insert_records("finance_charge_rate", $postdata);
        }
        else {
            $postdata['updated'] = DATE_TIME;
            $postdata['updated_by'] = $adminId;
            $postdata['archive'] = 0;

            $this->basic_model->update_records("finance_charge_rate", $postdata, ["id" => $chargerate_id]);
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Charge rate has been updated successfully.';
        } else {
            $msg = 'Charge rate has been created successfully.';
        }
        $this->add_charge_rate_log($data, $msg, $data['id'], $adminId);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * creates a new pay rate record or updates an existing one
     */
    function create_update_pay_rate($data, $adminId, $bulk_import = false, $exist_errors = []) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $payrate_id = $data['id'] ?? 0;

        # validation rule
        $validation_rules = $this->get_pay_rate_validation_rules($bulk_import);

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        $errors = [];
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
        }

        if($errors || $exist_errors) {
            $errors = array_merge($exist_errors,$errors);
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        # checking list of business rules for creating pay rates
        $br_check = $this->pay_rates_business_rules($data['pay_rate_category_id'], $data['role_id'], $data['pay_level_id'], $data['skill_level_id'], $data['pay_rate_award_id'], $data['start_date'], $data['end_date'], isset($data['external_reference'])?$data['external_reference']:null, $payrate_id);
        if(isset($br_check['status']) && $br_check['status'] == false) {
            return $br_check;
        }

        $postdata = [
            "pay_rate_category_id" => isset($data['pay_rate_category_id'])?$data['pay_rate_category_id']:0,
            "pay_rate_award_id" => isset($data['pay_rate_award_id'])?$data['pay_rate_award_id']:0,
            "role_id" => isset($data['role_id'])?$data['role_id']:0,
            "pay_level_id" => isset($data['pay_level_id'])?$data['pay_level_id']:0,
            "skill_level_id" => isset($data['skill_level_id'])?$data['skill_level_id']:0,
            "employment_type_id" => isset($data['employment_type_id'])?$data['employment_type_id']:0,
            "start_date" => date("Y-m-d H:i:s", strtotime($data['start_date'])),
            "end_date" => date("Y-m-d H:i:s", strtotime($data['end_date'])),
            "amount" => isset($data['amount'])?$data['amount']:0,
            "external_reference" => isset($data['external_reference'])?$data['external_reference']:null,
            "description" => isset($data['description'])?$data['description']:null,
            "status" => isset($data['status'])?$data['status']:null
        ];

        # adding an entry of pay rate
        if(!$payrate_id) {
            $postdata['created'] = DATE_TIME;
            $postdata['created_by'] = $adminId;
            $postdata['archive'] = 0;

            $payrate_id = $this->basic_model->insert_records("finance_pay_rate", $postdata);
        }
        else {
            $postdata['updated'] = DATE_TIME;
            $postdata['updated_by'] = $adminId;
            $postdata['archive'] = 0;

            $this->basic_model->update_records("finance_pay_rate", $postdata, ["id" => $payrate_id]);
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Pay rate has been updated successfully.';
        } else {
            $msg = 'Pay rate has been created successfully.';
        }
        $this->add_finance_log($data, $msg, $data['id'], $adminId);

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * bulk adding/updating pay rate records
     */
    function import_payrates($adminId) {
        # if submitted file is one of the allowed ones
        $this->load->library('csv_reader');
        $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (in_array($_FILES['attachments']['type'][0], $mimes) === FALSE) {
            $return = ['status' => false, 'error' => 'Invalid file extension, Please upload csv file only'];
            return $return;
        }

        # is there data in the submitted file?
        $tmpName = $_FILES['attachments']['tmp_name'][0];
        $filedata=$this->csv_reader->read_csv_data($tmpName);
        if (empty($filedata)) {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. File contains no data'];
            return $return;
        }

        # finding the first row (header) in the submitted file
        $header = array_shift($filedata);
        $header = convert_to_utf($header);
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        $data_to_import = [];

        # for each data item, there is a specific header to be used
        $csv_column_arr = $this->pay_rate_items['columns'];
        $arColMatch = array_diff($csv_column_arr, $col_header);

        if (!empty($arColMatch)) {
            $return = ['status' => false, 'error' => 'Invalid column names in uploaded csv file. It should be '.implode(", ",$csv_column_arr).' in that sequence'];
            return $return;
        }

        $cnt = 0;
        $allerrors = null;
        foreach ($filedata as $row) {
            $cnt++;
            $row = array_map("utf8_encode", $row);
            $valrow = null;
            foreach($csv_column_arr as $headerindex => $colname) {
                $tmparr = null;
                $valrow[$colname] = $row[$headerindex];
            }
            $data_to_import[] = $valrow;
        }

        if(empty($data_to_import)) {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. File contains no data'];
            return $return;
        }

        $row = $total_error_rows = $total_suc_rows = 0;
        foreach($data_to_import as $datarow) {
            $row_errors = [];
            $row++;

            $datarow['pay_rate_category_id'] = $this->find_reference_id("payrates_category", $datarow['category']);
            if(empty($datarow['pay_rate_category_id'])) {
                $row_errors[] = "Pay rate category not found: {$datarow['category']}";
            }

            $datarow['pay_rate_award_id'] = $this->find_reference_id("payrates_award", $datarow['award']);
            if(empty($datarow['pay_rate_award_id'])) {
                $row_errors[] = "Pay rate award not found: {$datarow['award']}";
            }

            $this->load->model('item/Document_model');
            $role_details = $this->Document_model->get_role_by_name(trim($datarow['role']));
            if(empty($role_details)) {
                $row_errors[] = "Role not found: {$datarow['role']}";
            }
            else {
                $datarow['role_id'] = $role_details[0]->value;
            }

            $datarow['pay_level_id'] = $this->find_reference_id("pay_levels", $datarow['pay_level']);
            if(empty($datarow['pay_level_id'])) {
                $row_errors[] = "Pay level not found: {$datarow['pay_level']}";
            }

            $datarow['skill_level_id'] = $this->find_reference_id("skills", $datarow['skill']);
            if(empty($datarow['skill_level_id'])) {
                $row_errors[] = "Skill level not found: {$datarow['skill']}";
            }

            $datarow['employment_type_id'] = $this->find_reference_id("employment_type", $datarow['employment_type']);
            if(empty($datarow['employment_type_id'])) {
                $row_errors[] = "Employment type not found: {$datarow['employment_type']}";
            }


            if(!empty($datarow['amount']))
                $datarow['amount'] = str_replace("$",'',$datarow['amount']);
            $datarow['status'] = 1;
            $datarow['id'] = null;

            if(isset($datarow['start_date'])) {
                $dt = DateTime::createFromFormat('d/m/Y',$datarow['start_date']);
                if($dt)
                $datarow['start_date'] = $dt->format('Y-m-d');
            }
            if(isset($datarow['end_date'])) {
                $dt = DateTime::createFromFormat('d/m/Y',$datarow['end_date']);
                if($dt)
                $datarow['end_date'] = $dt->format('Y-m-d');
            }
            
            $datarow['description'] = $datarow['description']?? NULL;

            $result = $this->create_update_pay_rate($datarow, $adminId, true, $row_errors);
            if($result['status'] == false) {
                $total_error_rows++;
                $errstring = $result['error'];
                $errstring = "Row: {$row} - ".$errstring;
                $allerrors[] = $errstring;
            }
            else {
                $total_suc_rows++;
            }
        }

        $postdata["total_rows"] = ($total_suc_rows + $total_error_rows);
        $postdata["total_success_rows"] = $total_suc_rows;
        $postdata["total_error_rows"] = $total_error_rows;
        $postdata["error_text"] = (!empty($allerrors)) ? implode("\r\n",$allerrors) : '';
        $postdata["created"] = DATE_TIME;
        $postdata["created_by"] = $adminId;
        $import_id = $this->basic_model->insert_records("admin_bulk_import", $postdata, $multiple = FALSE);

        # preparing stats to display to the user
        if(($total_suc_rows + $total_error_rows) > 1)
            $data_msg = ($total_suc_rows + $total_error_rows). " records were found.";
        else
        $data_msg = ($total_suc_rows + $total_error_rows). " record was found.";

        if($total_suc_rows > 1)
            $data_msg .= " {$total_suc_rows} records were updated.";
        else
        $data_msg .= " {$total_suc_rows} record was updated.";

        $error_msg = null;
        if($total_error_rows > 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} records had errors.";
        }
        else if($total_error_rows == 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} record had errors.";
        }

        $return = ["status" => true, 'error_msg' => $error_msg, 'data_msg' => $data_msg, 'import_id' => $import_id];
        return $return;
    }

    /**
     * bulk adding/updating charge rate records
     */
    function import_chargerates($adminId) {
        # if submitted file is one of the allowed ones
        $this->load->library('csv_reader');
        $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (in_array($_FILES['attachments']['type'][0], $mimes) === FALSE) {
            $return = ['status' => false, 'error' => 'Invalid file extension, Please upload csv file only'];
            return $return;
        }

        # is there data in the submitted file?
        $tmpName = $_FILES['attachments']['tmp_name'][0];
        $filedata=$this->csv_reader->read_csv_data($tmpName);
        if (empty($filedata)) {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. File contains no data'];
            return $return;
        }

        # finding the first row (header) in the submitted file
        $header = array_shift($filedata);
        $header = convert_to_utf($header);
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        $data_to_import = [];

        # for each data item, there is a specific header to be used
        $csv_column_arr = $this->charge_rate_items['columns'];
        $arColMatch = array_diff($csv_column_arr, $col_header);

        if (!empty($arColMatch)) {
            $return = ['status' => false, 'error' => 'Invalid column names in uploaded csv file. It should be '.implode(", ", $csv_column_arr).' in that sequence'];
            return $return;
        }

        $cnt = 0;
        $allerrors = null;
        foreach ($filedata as $row) {
            $cnt++;
            $row = array_map("utf8_encode", $row);
            $valrow = null;
            foreach($csv_column_arr as $headerindex => $colname) {
                $tmparr = null;
                $valrow[$colname] = $row[$headerindex];
            }
            $data_to_import[] = $valrow;
        }

        if(empty($data_to_import)) {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. File contains no data'];
            return $return;
        }

        $row = $total_error_rows = $total_suc_rows = 0;
        foreach($data_to_import as $datarow) {
            $row_errors = [];
            $row++;

            $datarow['charge_rate_category_id'] = $this->find_reference_id("payrates_category", $datarow['category']);
            if(empty($datarow['charge_rate_category_id'])) {
                $row_errors[] = "Charge rate category not found: {$datarow['category']}";
            }

            $this->load->model('item/Document_model');
            $role_details = $this->Document_model->get_role_name_search($datarow['role']);
            if(empty($role_details)) {
                $row_errors[] = "Role not found: {$datarow['role']}";
            }
            else {
                $datarow['role_id'] = $role_details[0]->value;
            }

            $datarow['pay_level_id'] = $this->find_reference_id("pay_levels", $datarow['pay_level']);
            if(empty($datarow['pay_level_id'])) {
                $row_errors[] = "Pay level not found: {$datarow['pay_level']}";
            }

            $datarow['skill_level_id'] = $this->find_reference_id("skills", $datarow['skill']);
            if(empty($datarow['skill_level_id'])) {
                $row_errors[] = "Skill level not found: {$datarow['skill']}";
            }

            $datarow['cost_book_id'] = $this->find_reference_id("cost_book", $datarow['cost_book']);
            if(empty($datarow['cost_book_id'])) {
                $row_errors[] = "Cost book not found: {$datarow['cost_book']}";
            }


            if(!empty($datarow['amount']))
                $datarow['amount'] = str_replace("$",'',$datarow['amount']);
            $datarow['status'] = 1;
            $datarow['id'] = null;

            if(isset($datarow['start_date'])) {
                $dt = DateTime::createFromFormat('d/m/Y',$datarow['start_date']);
                if($dt)
                $datarow['start_date'] = $dt->format('Y-m-d');
            }
            if(isset($datarow['end_date'])) {
                $dt = DateTime::createFromFormat('d/m/Y',$datarow['end_date']);
                if($dt)
                $datarow['end_date'] = $dt->format('Y-m-d');
            }

            $result = $this->create_update_charge_rate($datarow, $adminId, true, $row_errors);
            if($result['status'] == false) {
                $total_error_rows++;
                $errstring = $result['error'];
                $errstring = "Row: {$row} - ".$errstring;
                $allerrors[] = $errstring;
            }
            else {
                $total_suc_rows++;
            }
        }

        $postdata["total_rows"] = ($total_suc_rows + $total_error_rows);
        $postdata["total_success_rows"] = $total_suc_rows;
        $postdata["total_error_rows"] = $total_error_rows;
        $postdata["error_text"] = (!empty($allerrors)) ? implode("\r\n",$allerrors) : '';
        $postdata["created"] = DATE_TIME;
        $postdata["created_by"] = $adminId;
        $import_id = $this->basic_model->insert_records("admin_bulk_import", $postdata, $multiple = FALSE);

        # preparing stats to display to the user
        if(($total_suc_rows + $total_error_rows) > 1)
            $data_msg = ($total_suc_rows + $total_error_rows). " records were found.";
        else
        $data_msg = ($total_suc_rows + $total_error_rows). " record was found.";

        if($total_suc_rows > 1)
            $data_msg .= " {$total_suc_rows} records were updated.";
        else
        $data_msg .= " {$total_suc_rows} record was updated.";

        $error_msg = null;
        if($total_error_rows > 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} records had errors.";
        }
        else if($total_error_rows == 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} record had errors.";
        }

        $return = ["status" => true, 'error_msg' => $error_msg, 'data_msg' => $data_msg, 'import_id' => $import_id];
        return $return;
    }

    /**
     * checking list of business rules for creating/updating charge rate
     */
    public function charge_rates_business_rules($charge_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $cost_book_id, $start_date, $end_date, $external_reference, $charge_rate_id = null) {
        $errors = null;

        # start date should be lower than end date
        $valid_date_range = check_dates_lower_to_other($start_date, $end_date, true);
        if(!$valid_date_range) {
            return [
                "status" => false,
                "error" => "Start date: ".date("d/m/Y", strtotime($start_date))." should be lower to End date: ".date("d/m/Y", strtotime($end_date))
            ];
        }

        # checking start & end date not overlapping for a combination of
        # category, role, pay level and skill level
        $dates_overlap = $this->check_charge_rates_dates_not_overlapping($charge_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $cost_book_id, $start_date, $end_date, $charge_rate_id);
        if(isset($dates_overlap['status']) && $dates_overlap['status'] == false) {
            $errors[] = $dates_overlap['error'];
        }

        # checking unique external reference number for a combination of
        # category, role, pay level and skill level
        if($external_reference) {
            $dates_overlap = $this->check_charge_rates_duplicate_ext_ref($charge_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $external_reference, $charge_rate_id);
            if(isset($dates_overlap['status']) && $dates_overlap['status'] == false) {
                $errors[] = $dates_overlap['error'];
            }
        }

        if(!empty($errors)) {
            return [
                "status" => false,
                "error" => implode(",",$errors)];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * checking list of business rules for creating/updating pay rate
     */
    public function pay_rates_business_rules($pay_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $pay_rate_award_id, $start_date, $end_date, $external_reference, $pay_rate_id = null) {
        $errors = null;

        # start date should be lower than end date
        $valid_date_range = check_dates_lower_to_other($start_date, $end_date, true);
        if(!$valid_date_range) {
            return [
                "status" => false,
                "error" => "Start date: ".date("d/m/Y", strtotime($start_date))." should be lower to End date: ".date("d/m/Y", strtotime($end_date))
            ];
        }

        # checking start & end date not overlapping for a combination of
        # category, role, pay level and skill level
        $dates_overlap = $this->check_pay_rates_dates_not_overlapping($pay_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $pay_rate_award_id, $start_date, $end_date, $pay_rate_id);
        if(isset($dates_overlap['status']) && $dates_overlap['status'] == false) {
            $errors[] = $dates_overlap['error'];
        }

        # checking unique external reference number for a combination of
        # category, role, pay level and skill level
        if($external_reference) {
            $dates_overlap = $this->check_pay_rates_duplicate_ext_ref($pay_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $external_reference, $pay_rate_id);
            if(isset($dates_overlap['status']) && $dates_overlap['status'] == false) {
                $errors[] = $dates_overlap['error'];
            }
        }

        if(!empty($errors)) {
            return [
                "status" => false,
                "error" => implode(",",$errors)];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * checking unique external reference number for a combination of
     * category, role, pay level and skill level for pay rates
     */
    public function check_pay_rates_duplicate_ext_ref($pay_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $external_reference, $pay_rate_id = null) {
        $select_column = ["pr.*"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_pay_rate as pr');
        $this->db->where('pr.archive', 0);
        $this->db->where('pr.pay_rate_category_id', $pay_rate_category_id);
        $this->db->where('pr.role_id', $role_id);
        if($pay_rate_id)
        $this->db->where('pr.id != '.$pay_rate_id);
        $this->db->where('pr.pay_level_id', $pay_level_id);
        $this->db->where('pr.skill_level_id', $skill_level_id);
        $this->db->where('pr.external_reference', $external_reference);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if($result) {
            return [
                "status" => false,
                "error" => "The external reference provided for this category, role, pay level and skill level already exists"];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * checking start & end date not overlapping for a combination of
     * category, role, pay level and skill level for pay rates
     */
    public function check_pay_rates_dates_not_overlapping($pay_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $pay_rate_award_id, $start_date, $end_date, $pay_rate_id = null) {
        $select_column = ["pr.*"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_pay_rate as pr');
        $this->db->where('pr.archive', 0);
        $this->db->where('pr.pay_rate_category_id', $pay_rate_category_id);
        $this->db->where('pr.role_id', $role_id);
        if($pay_rate_id)
        $this->db->where('pr.id != '.$pay_rate_id);
        $this->db->where('pr.pay_level_id', $pay_level_id);
        $this->db->where('pr.skill_level_id', $skill_level_id);
        $this->db->where('pr.pay_rate_award_id', $pay_rate_award_id);
        $this->db->where("(
            (date(pr.start_date) >= '{$start_date}' and date(pr.start_date) <= '{$end_date}')
            or
            (date(pr.end_date) >= '{$start_date}' and date(pr.end_date) <= '{$end_date}')
            or
            (date(pr.start_date) <= '{$start_date}' and date(pr.end_date) >= '{$end_date}')
        )");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if($result) {
            return [
                "status" => false,
                "error" => "The date range selected for this category, role, pay level and skill level must not overlap."];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * checking unique external reference number for a combination of
     * category, role, pay level and skill level for charge rates
     */
    public function check_charge_rates_duplicate_ext_ref($charge_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $external_reference, $charge_rate_id = null) {
        $select_column = ["pr.*"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_charge_rate as pr');
        $this->db->where('pr.archive', 0);
        $this->db->where('pr.charge_rate_category_id', $charge_rate_category_id);
        $this->db->where('pr.role_id', $role_id);
        if($charge_rate_id)
        $this->db->where('pr.id != '.$charge_rate_id);
        $this->db->where('pr.pay_level_id', $pay_level_id);
        $this->db->where('pr.skill_level_id', $skill_level_id);
        $this->db->where('pr.external_reference', $external_reference);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if($result) {
            return [
                "status" => false,
                "error" => "The external reference provided for this category, role, pay level and skill level already exists"];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * checking start & end date not overlapping for a combination of
     * category, role, pay level and skill level for charge rates
     */
    public function check_charge_rates_dates_not_overlapping($charge_rate_category_id, $role_id, $pay_level_id, $skill_level_id, $cost_book_id, $start_date, $end_date, $charge_rate_id = null) {
        $select_column = ["pr.*"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_charge_rate as pr');
        $this->db->where('pr.archive', 0);
        $this->db->where('pr.charge_rate_category_id', $charge_rate_category_id);
        $this->db->where('pr.role_id', $role_id);
        if($charge_rate_id)
        $this->db->where('pr.id != '.$charge_rate_id);
        $this->db->where('pr.pay_level_id', $pay_level_id);
        $this->db->where('pr.skill_level_id', $skill_level_id);
        $this->db->where('pr.cost_book_id', $cost_book_id);
        $this->db->where("(
            (date(pr.start_date) >= '{$start_date}' and date(pr.start_date) <= '{$end_date}')
            or
            (date(pr.end_date) >= '{$start_date}' and date(pr.end_date) <= '{$end_date}')
            or
            (date(pr.start_date) <= '{$start_date}' and date(pr.end_date) >= '{$end_date}')
        )");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if($result) {
            return [
                "status" => false,
                "error" => "The date range selected for this category, role, pay level and skill level must not overlap."];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * adding the log of actions
     */
    public function add_finance_log($data, $title, $id, $adminId) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /**
     * used by add/edit/delete charge rate
     */
    public function add_charge_rate_log($data, $title, $chargerate_id, $adminId) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($chargerate_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /**
     * fetching a single pay rate details
     */
    public function get_pay_rate_details($id) {
        if (empty($id)) return;

        $this->db->select("pr.*, mr.name as role_name");
        $this->db->from('tbl_finance_pay_rate as pr');
        $this->db->join('tbl_member_role as mr', 'mr.id = pr.role_id', 'inner');
        $this->db->where("pr.id", $id);
        $this->db->where("pr.archive", "0");
        $this->db->where("mr.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null;#$ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Pay rate not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # for role pre-selection
            $role_details['label'] = $val->role_name;
            $role_details['value'] = $val->role_id;
            $dataResult->role_details = $role_details;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * fetching a single charge rate details
     */
    public function get_charge_rate_details($id) {
        if (empty($id)) return;

        $this->db->select("pr.*, mr.name as role_name");
        $this->db->from('tbl_finance_charge_rate as pr');
        $this->db->join('tbl_member_role as mr', 'mr.id = pr.role_id', 'inner');
        $this->db->where("pr.id", $id);
        $this->db->where("pr.archive", "0");
        $this->db->where("mr.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null;#$ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Charge rate not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # for role pre-selection
            $role_details['label'] = $val->role_name;
            $role_details['value'] = $val->role_id;
            $dataResult->role_details = $role_details;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /*
     * To fetch the charge rates list
     */
    public function get_charge_rates_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 0;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("pr.amount", "pr.external_reference", "pr.description", "r1.display_name", "r3.display_name", "r4.display_name", "r5.display_name", "DATE_FORMAT(pr.start_date,'%d/%m/%Y')", "DATE_FORMAT(pr.end_date,'%d/%m/%Y')");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new lightening filters
       /*  if(isset($filter->filters)) {

            foreach($filter->filters as $filter_obj) {
                if(empty($filter_obj->select_filter_value)) continue;

                if($filter_obj->select_filter_field == "status_label") {
                    $filter_obj->select_filter_value -= 1;
                }
                $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
                if($filter_obj->select_filter_field == "charge_rate_category_label") {
                    $this->db->where("r1.id ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "pay_level_label") {
                    $this->db->where("r3.id ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "skill_level_label") {
                    $this->db->where("r4.id ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "cost_book_label") {
                    $this->db->where("r5.id ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "status_label") {
                    $this->db->where("pr.status = ".$filter_obj->select_filter_value);
                }
                if($filter_obj->select_filter_field == "amount") {
                    $this->db->where('pr.amount '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "start_date" || $filter_obj->select_filter_field == "end_date") {
                    $this->db->where('DATE_FORMAT(pr.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        } */
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "charge_rate_category_id", "role_id", "pay_level_id", "skill_level_id", "cost_book_id", "start_date", "end_date", "amount", "external_reference", "description", "status"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'pr.id';
            $direction = 'DESC';
        }

        $select_column = ["pr.id", "pr.charge_rate_category_id", "pr.role_id", "pr.pay_level_id", "pr.skill_level_id", "pr.cost_book_id", "pr.start_date", "pr.end_date", "pr.amount", "pr.external_reference", "pr.description", "pr.status"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("r1.display_name as charge_rate_category_label");
        $this->db->select("r3.display_name as pay_level_label");
        $this->db->select("r4.display_name as skill_level_label");
        $this->db->select("r5.display_name as cost_book_label");

        $status_label = "(CASE ";
        foreach($this->charge_rate_status as $k => $v) {
            $status_label .= " WHEN pr.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);

        $this->db->from('tbl_finance_charge_rate as pr');
        $this->db->join('tbl_member_role as r', 'r.id = pr.role_id and r.archive = 0', 'inner');
        $this->db->join('tbl_references as r1', 'r1.id = pr.charge_rate_category_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r3', 'r3.id = pr.pay_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r4', 'r4.id = pr.skill_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r5', 'r5.id = pr.cost_book_id and r1.archive = 0', 'inner');
        $this->db->where("pr.archive", "0");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $total_item =$dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched charge rates list successfully',"last_query" => $last_query,'total_item'=>$total_item);
        return $return;
    }

    /*
     * To fetch the pay rates list
     */
    public function get_pay_rates_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 99999;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("pr.amount", "pr.external_reference", "pr.description", "r1.display_name", "r2.display_name", "r3.display_name", "r4.display_name", "r5.display_name", "DATE_FORMAT(pr.start_date,'%d/%m/%Y')", "DATE_FORMAT(pr.end_date,'%d/%m/%Y')");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $available_column = ["id", "pay_rate_category_id", "pay_rate_award_id", "role_id", "pay_level_id", "skill_level_id", "employment_type_id", 
                             "formatted_start_date", "formatted_end_date", "amount", "external_reference", "description", "status", "start_date", "end_date"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'pr.id';
            $direction = 'DESC';
        }

        $select_column = ["pr.id", "pr.pay_rate_category_id", "pr.pay_rate_award_id", "pr.role_id", "pr.pay_level_id", "pr.skill_level_id", "pr.employment_type_id", "DATE_FORMAT(pr.start_date,'%d/%m/%Y') as formatted_start_date", "DATE_FORMAT(pr.end_date,'%d/%m/%Y') as formatted_end_date", "pr.amount", "pr.external_reference", "pr.description", "pr.status", "date(pr.start_date) as start_date, date(pr.end_date) as end_date"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("r1.display_name as pay_rate_category_label");
        $this->db->select("r2.display_name as pay_rate_award_label");
        $this->db->select("r3.display_name as pay_level_label");
        $this->db->select("r4.display_name as skill_level_label");
        $this->db->select("r5.display_name as employment_type_label");

        $status_label = "(CASE ";
        foreach($this->pay_rate_status as $k => $v) {
            $status_label .= " WHEN pr.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);

        $this->db->from('tbl_finance_pay_rate as pr');
        $this->db->join('tbl_member_role as r', 'r.id = pr.role_id and r.archive = 0', 'inner');
        $this->db->join('tbl_references as r1', 'r1.id = pr.pay_rate_category_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r2', 'r2.id = pr.pay_rate_award_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r3', 'r3.id = pr.pay_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r4', 'r4.id = pr.skill_level_id and r1.archive = 0', 'inner');
        $this->db->join('tbl_references as r5', 'r5.id = pr.employment_type_id and r1.archive = 0', 'inner');
        $this->db->where("pr.archive", "0");
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $total_item = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();

        $return = array('total_item' => $total_item, 'data' => $result, 'status' => true, 'msg' => 'Fetched pay rates list successfully',"last_query" => $last_query);
        return $return;
    }

    /**
     * getting user to download the error report after bulk pay rates import
     */
    function download_import_stats($import_id) {

        $details = $this->basic_model->get_row('admin_bulk_import', ["error_text", "summary_text"], ["id" => $import_id]);
        if(empty($details)) {
            return ["status" => false, "error" => "Couldn't find the summary!"];
        }

        $data = null;
        if(!empty($details->summary_text)) {
            $filename = "paid_timesheet_summary_".$import_id.".csv";
            header("Content-type: application/csv");
            header("Content-Disposition: attachment; filename=".$filename);

            $timesheet_ids = explode(",",$details->summary_text);
            $summary = $this->get_timesheet_shift_member_details($timesheet_ids);
            $data_rows[] = "Timesheet, Shift, Member, Amount, Created, Status";
            if(!empty($summary)) {
                foreach($summary as $row) {
                    $data_rows[] = $row['timesheet_no'].",".$row['shift_no'].",".$row['member_label'].",\$".$row['amount'].",".$row['date_created'].",".$row['status_label'];
                }
            }
            $data = implode("\r\n", $data_rows);
        }

        if(!empty($details->error_text)) {
            $filename = "payrates_import_".$import_id.".txt";
            header("Content-type: text/plain");
            header("Content-Disposition: attachment; filename=".$filename);
            $data = $details->error_text;
        }
        return ["status" => true, "data" => $data];
    }

    /**
     * archiving pay rate
     */
    function archive_pay_rate($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the pay rate exist?
        $result = $this->get_pay_rate_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Pay rate does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("finance_pay_rate", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived pay rate";
        $this->add_finance_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * archiving charge rate
     */
    function archive_charge_rate($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the charge rate exist?
        $result = $this->get_charge_rate_details($data['id']);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Charge rate does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("finance_charge_rate", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived charge rate";
        $this->add_charge_rate_log($data, $msg_title, $data['id'], $adminId);
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /*
     * To fetch the invoices list
     */
    public function get_invoices_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 0;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # creating status sql query part
        $status_label = "(CASE ";
        foreach($this->invoice_status as $k => $v) {
            $status_label .= " WHEN i.status = {$k} THEN '{$v}'";
        };
        $status_label2 = $status_label." ELSE '' END)";
        $status_label .= " ELSE '' END) as status_label";

        # creating invoice type's sql query part
        $invoice_type_label = "(CASE ";
        foreach($this->invoice_types as $k => $v) {
            $invoice_type_label .= " WHEN i.invoice_type = {$k} THEN '{$v}'";
        };
        $invoice_type_label2 = $invoice_type_label." WHEN i.invoice_type = -1 THEN NULL ELSE '' END)";
        $invoice_type_label .= " ELSE '' END) as invoice_type_label";

        # Searching column
        $src_columns = array("i.amount", "i.invoice_no", "i.status", "(CASE WHEN i.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = i.account_id and p1.archive = 0) WHEN i.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = i.account_id and o.archive = 0) ELSE '' END)", "DATE_FORMAT(i.invoice_date,'%d/%m/%Y')", "concat(p.firstname,' ',p.lastname)", "(select DATE_FORMAT(min(s.actual_start_datetime),'%d/%m/%Y') from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0)", "(select DATE_FORMAT(max(s.actual_start_datetime),'%d/%m/%Y') from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0)", $status_label2, $invoice_type_label2);
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "amount", "invoice_no", "status", "account_id", "account_type", "invoice_created_date", 
                             "contact_id", "contact_fullname", "i_created"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'i.id';
            $direction = 'DESC';
        }

        $select_column = ["i.id", "i.amount", "i.invoice_no", "i.status", "i.account_id", "i.account_type", "DATE_FORMAT(i.invoice_date,'%d/%m/%Y') as invoice_created_date", "i.contact_id", "concat(p.firstname,' ',p.lastname) as contact_fullname", "i.created as i_created"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN i.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = i.account_id and p1.archive = 0) WHEN i.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = i.account_id and o.archive = 0) ELSE '' END) as account_label");
        $this->db->select(("(select DATE_FORMAT(min(s.actual_start_datetime),'%d/%m/%Y') from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0) as shift_start_date"));
        $this->db->select(("(select DATE_FORMAT(max(s.actual_start_datetime),'%d/%m/%Y') from tbl_shift s, tbl_finance_invoice_shift si where si.invoice_id = i.id and si.archive = 0 and s.id = si.shift_id and s.archive = 0) as shift_end_date"));
        $this->db->select($status_label);
        $this->db->select($invoice_type_label);
        $this->db->from('tbl_finance_invoice as i');
        $this->db->join('tbl_person as p', 'p.id = i.contact_id', 'inner');
        $this->db->where("i.archive", "0");
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $result = $query->result();
        $return = array('total_item' => $total_item, 'data' => $result, 'status' => true);
        return $return;
    }

    /**
     * generating invoice pdf, storing it onto S3 bucket
     * using invoice details to populate dynamic data into pdf
     */
    public function generate_invoice_pdf($data, $adminId, $pdfUnlink = true) {

        # need to provide invoice id
        if(empty($data['id'])) {
            return ['status' => false, 'error' => "Invoice id missing"];
        }
        # fetching the invoice details
        $result = $this->get_invoice_details($data['id']);
        if (empty($result) || $result['status'] == false) {
            return ['status' => false, 'error' => "Invoice does not exist anymore."];
        }
        $invoice_details = (array) $result['data'];
        
        # fetching the account information
        $this->load->model('account/Account_model');
        $result = $this->Schedule_model->get_address_for_account($invoice_details['account_id'],$invoice_details['account_type'], $skip_location = true, 1);
        $account_address = (array) $result['data'];

        $result = $this->get_invoice_shift_for_pdf($data['id'], $invoice_details['invoice_type']); 
       
        $billing_address = !empty($account_address[0]['label']) ? $account_address[0]['unit_number'] . " " . $account_address[0]['label'] : '';
        
        $invoice_shifts = $site_address =  null;
        $total_cost_exc = $total_gst = $total_cost_inc = 0.00;
        if (!empty($result) && $result['status'] == true) {
            $managed_type = "";
            $invoice_shifts = (array) $result['data'];
            if($invoice_details['invoice_type'] == 4 && !empty($invoice_details['sa_managed_type']) && $invoice_details['account_type'] == 1) {
                $managed_type = $invoice_details['sa_managed_type'];               
            }
            
            foreach($invoice_shifts as $shift_row) {
                $total_cost_exc = $total_cost_exc + $shift_row['total_cost_exc_org'];
                $total_gst = $total_gst + $shift_row['gst_org'];
                $total_cost_inc = $total_cost_inc + $shift_row['total_cost_inc_org'];
                $org_site_id = !empty($shift_row['org_site_id'])?? NULL;
                if(!empty($org_site_id)) {
                    $site_res = $this->Schedule_model->get_address_for_account($org_site_id,2, $skip_location = true, 1);
                    $site_address = (array) $site_res['data'];
                }
            }
        }
        $total_cost_exc = "\$".number_format($total_cost_exc,2);
        $total_gst = "\$".number_format($total_gst,2);
        $total_cost_inc = "\$".number_format($total_cost_inc,2);        


        $view = 'finance/preview_invoice_pdf';
        $participant_name = '';
         if($invoice_details['invoice_type'] == 4 && $invoice_details['account_type'] == 1) {
            $view = 'finance/preview_ndis_invoice_pdf';
            $participant_name = $invoice_details['account_person']['label'];
            $site_address = $participant_name . ": " . $billing_address;
        }else {
            $site_address = !empty($site_address) ?  $site_address[0]['unit_number'] . " ". $site_address[0]['label'] : '';
        }
        
        if($managed_type == 2) {
            # Billing Address of the organisation selected in the SA Payment method of the Account (plan managed)
            $billing_address  = $this->payment_type_org_address_details($invoice_details['sa_id']);            
        }
        
        # preparing dynamic data to update in static PDF base file
        $pdf_data = [
            "base_url" => base_url(),
            "invoice_no" => $invoice_details['invoice_no'],
            "invoice_type_label" => $invoice_details['invoice_type_label'],
            "contact_label" => ($invoice_details['invoice_type'] == 4 && $invoice_details['account_type'] == 1) ? "ABN" : $invoice_details['contact_label'],
            "billing_address" => ($managed_type == 1) ? "45 Brougham St <br> Geelong VIC 3220" : $billing_address ?? '',
            "site_address" => $site_address,
            "invoice_date" =>  date('d/m/Y', strtotime($invoice_details['invoice_date'])),
            "invoice_due_date" => date('d/m/Y', strtotime($invoice_details['invoice_date'] . '+14 day')),
            "amount" => "\$".$invoice_details['amount'],
            "invoice_shifts" => $invoice_shifts,
            "total_cost_exc" => $total_cost_exc,
            "total_gst" => $total_gst,
            "total_cost_inc" => $total_cost_inc,
            "managed_type" => $managed_type,
            "participant_name" => $participant_name,
            'ndis_number' => $invoice_details['ndis_number']
        ];         
        
        # loading the html content from base PDF files
        $main_html = $this->load->view($view, $pdf_data, true);        

        # mpdf initializing
        require_once APPPATH . 'third_party/mpdf7/vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_top' => '10',
            'margin_bottom' => '10',
            'margin_left' => '10',
            'margin_right' => '10',
            'setAutoBottomMargin' => 'stretch',
            'orientation' => 'L'
        ]);
        
        $mpdf->WriteHTML($main_html);
        
        $footer_html = $this->load->view("finance/preview_invoice_footer", $pdf_data, true);
        # writing the PDF
        if(!empty($footer_html))
            $mpdf->SetHTMLFooter($footer_html);      
        
        # preparing file meta data and creating a pdf file
        $filename = $invoice_details['invoice_no'].'.pdf';
        $pdfFilePath = FINANCE_INVOICE_FILE_PATH . $filename;
        $mpdf->Output($pdfFilePath, "F");

        # uploading the pdf file to AWS S3 bucket
        require_once APPPATH . 'Classes/common/Aws_file_upload.php';
        $awsFileupload = new Aws_file_upload();

        # preparing AWS config data
        $config['file_name'] = $filename;
        $config['upload_path'] = S3_FINANCE_INVOICE_PATH;
        $config['directory_name'] = '';
        $config['allowed_types'] = DEFAULT_ATTACHMENT_UPLOAD_TYPE; //'jpg|jpeg|png|xlx|xls|doc|docx|pdf|pages';
        $config['max_size'] = DEFAULT_MAX_UPLOAD_SIZE;
        $config['uplod_folder'] = './uploads/';
        $config['adminId'] = $adminId;
        $config['title'] = $invoice_details['invoice_no'];
        $config['module_id'] = 5; # Finance
        $config['created_by'] = $adminId ?? NULL;

        # uploading to S3 bucket
        $s3_response = $awsFileupload->upload_from_app_to_s3($config, FALSE);
        $s3_upload = false;
        if (!empty($s3_response['aws_response'])) {
            $aws_response = (!empty($s3_response['aws_uploaded_flag']) && $s3_response['aws_uploaded_flag'] !=0 ) ? (array) json_decode($s3_response['aws_response']) : [];
            if(isset($aws_response['@metadata']) && $aws_response['@metadata']->statusCode == 200)
                $s3_upload = true;
        }

        # any error in uploading?
        if(!$s3_upload) {
            return ['status' => false, 'error' => "Error uploading invoice to AWS S3"];
        }

        # remove the local server file used for S3 uploading
        if ($pdfUnlink == true) {
            unlink($pdfFilePath);
        }        

        $preview_url = 'mediaShow/f/'. urlencode(base64_encode(S3_FINANCE_INVOICE_PATH.$filename)). '?download_as='.$filename.'&s3=true';

        return ['status' => true, 'msg' => "Successfully generated invoice PDF", 'preview_url' => $preview_url, 'pdfFilePath' => $pdfFilePath];
        
    }

    /*
     * To fetch the timesheets list
     */
    public function get_timesheets_list($reqData, $filter_condition = '') {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 0;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # creating status sql query part
        $status_label = "(CASE ";
        foreach($this->timesheet_status as $k => $v) {
            $status_label .= " WHEN ts.status = {$k} THEN '{$v}'";
        };
        $status_label2 = $status_label."ELSE '' END)";
        $status_label .= "ELSE '' END) as status_label";

        # Searching column
        $src_columns = array(
        "(CASE WHEN s.scheduled_start_datetime = s.actual_start_datetime AND s.scheduled_end_datetime = s.actual_end_datetime AND (select sum(duration_int) from tbl_shift_break sb where sb.shift_id = s.id and sb.archive = 0 and sb.break_category = 1) = (select sum(duration_int) from tbl_shift_break sb where sb.shift_id = s.id and sb.archive = 0 and sb.break_category = 2) THEN 'Yes' ELSE 'No' END)",
        "ts.amount", "ts.timesheet_no", "ts.status", "ts.shift_id", "ts.member_id", "concat(m.firstname,' ',m.lastname)", "DATE_FORMAT(ts.created,'%d/%m/%Y')",
        "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id and p1.archive = 0) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id and o.archive = 0) ELSE '' END)",
        "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')", "concat(DATE_FORMAT(s.scheduled_start_datetime,'%h:%i %p'), ' - ', DATE_FORMAT(s.scheduled_end_datetime,'%h:%i %p'))", "s.shift_no",
        $status_label);

        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # filter conditions
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $available_column = ["id", "amount", "timesheet_no", "status", "shift_id", "member_id", "shift_no", "ts_created", "ts_created_date", "account_id", "account_type"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ts.id';
            $direction = 'DESC';
        }

        $select_column = ["ts.id", "ts.amount", "ts.timesheet_no", "ts.status", "ts.shift_id", "ts.member_id", "s.shift_no", "ts.created as ts_created", "DATE_FORMAT(ts.created,'%d/%m/%Y') as ts_created_date", "s.account_id", "s.account_type"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("m.fullname as member_label, DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y') as scheduled_start_date");
        $this->db->select("concat(DATE_FORMAT(s.scheduled_start_datetime,'%h:%i %p'), ' - ', DATE_FORMAT(s.scheduled_end_datetime,'%h:%i %p')) as scheduled_shift_time");

        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id and p1.archive = 0) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id and o.archive = 0) ELSE '' END) as account_label");

        $this->db->select("(
            CASE WHEN s.scheduled_start_datetime = s.actual_start_datetime
            and s.scheduled_end_datetime = s.actual_end_datetime
            and COALESCE(
                    (select sum(duration_int) from tbl_shift_break sb where sb.shift_id = s.id and sb.archive = 0 and sb.break_category = 1),0) =
                COALESCE(
                    (select sum(duration_int) from tbl_shift_break sb where sb.shift_id = s.id and sb.archive = 0 and sb.break_category = 2),0)
            THEN 'Yes' ELSE 'No' END) as matches_actual");

        $this->db->select("(select mk.keypay_emp_id from tbl_keypay_kiosks_emp_mapping_for_member mk where mk.member_id = m.id and mk.archive = 0) as keypay_emp_id");

        $this->db->select($status_label);
        $this->db->from('tbl_finance_timesheet as ts');
        $this->db->join('tbl_member as m', 'm.id = ts.member_id and m.archive = 0', 'inner');
        $this->db->join('tbl_shift as s', 's.id = ts.shift_id and s.archive = 0', 'inner');
        $this->db->where("ts.archive", "0");
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $total_item = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result_array();
        $new_result = [];
        if($result) {
            foreach($result as $row) {
                $shift_breaks_line = $this->Schedule_model->get_shift_breaks_one_line($row['shift_id'],1);
                $row['scheduled_shift_time'] .= " ".$shift_breaks_line;
                $new_result[] = $row;
            }
        }

        $return = array('total_item' => $total_item, 'data' => $new_result, 'status' => true, 'msg' => 'Fetched timesheets list successfully',"last_query" => $last_query);
        return $return;
    }

    public function check_amount_for_miscellaneous_item($manualInvoiceItems,$UserId,$userType)
    {
        $this->db->select(["ppli.fund_remaining","ppli.id as plan_line_itemId"]);
        $this->db->join("tbl_user_plan as pp", "pp.archive=0", "inner");
        $this->db->join("tbl_finance_support_category as fsc", "fsc.id=fli.support_category AND fsc.key_name='other_type'", "inner");
        $this->db->join("tbl_user_plan_line_items as ppli", "ppli.user_planId=pp.id AND ppli.archive=0 AND ppli.line_itemId= fli.id AND pp.user_type = $userType AND pp.userId=$UserId", "inner");
        $this->db->where(" '" . DATE_CURRENT . "' between pp.start_date AND pp.end_date ", null, false);
        $this->db->from('tbl_finance_line_item as fli');

        $query = $this->db->get();
        #last_query();
        $res = $query->row_array();
        $fund_remaining = isset($res['fund_remaining'])?$res['fund_remaining']:0;
        #pr($fund_remaining);
        $total_cost = 0;
        if(!empty(obj_to_arr($manualInvoiceItems)))
        {
            foreach (obj_to_arr($manualInvoiceItems) as $value) {
                if($value['item_cost']!=''){
                    $total_cost = $total_cost + $value['item_cost'];
                }
            }
        }

        if((int)$total_cost > (int)$fund_remaining){
            return array('status'=>false);
        }else{
            return array('status'=>true,'plan_line_itemId'=>$res['plan_line_itemId']);
        }
    }

    /**
     * To get contact related to account 
     * @param {int} $account_id
     * @param {int} $account_type
     */
    function get_contact_for_account($account_id, $account_type) {
        
        $default_id = '';
        $default = [];

        if($account_type == 1)
        {

            $participant_id = $account_id;
            $column = ["sub_p.id as contact_id","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as name", "'contact' as type", "pe.email as email"];
            $this->db->select($column);
            $this->db->from(TBL_PREFIX . 'participants_master as tp');
            
            $this->db->where(['tp.id' => $participant_id]);
            $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = tp.contact_id", "LEFT");
            $this->db->join("tbl_person_email as pe", "pe.person_id = sub_p.id and pe.archive = 0", "LEFT");
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            // to add the new contact data
            if (isset($result) === true && isset($result[0]) === true) {
                $default = $result[0];
                $default_id = $result[0]['contact_id'];
            }

        } else {

            $org_id = $account_id;

            # Get Primary Contact
            $column = ["sub_p.id as contact_id","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as name", "'contact' as type", "pe.email as email", "sr.is_primary"];

            $this->db->from(TBL_PREFIX . 'sales_relation as sr');
            $this->db->select($column);
            $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
            $this->db->join("tbl_person_email as pe", "pe.person_id = sub_p.id and pe.archive = 0", "LEFT");
            $this->db->where('sr.source_data_id', $org_id);
            $this->db->where('sr.source_data_type', '2');
            $this->db->where('sr.is_primary', '1');

            $query_pri = $this->db->get();
            $result_pri = $query_pri->num_rows() > 0 ? $query_pri->result_array() : [];

            if (isset($result_pri) === true && isset($result_pri[0]) === true) {
                $default = $result_pri[0];
                $default_id = $result_pri[0]['contact_id'];
            }
        }
        return ['default_contact' => $default, 'default_contact_id' => $default_id];
    }

    /**
     * Send invoice email 
     * @param {obj} $reqData
     * @param {int} $adminId 
     */
    public function send_invoice_mail($data, $adminId) {

        if(!isset($data['ids']) || !is_array($data['ids'])) {
            $response = ['status' => false, 'error' => "No invoices selected!"];
            return $response;
        }

        # creating a list of unique account so we don't send multiple emails to same account
        # in case of multiple invoices of the same account, one email with individual invoice attachment will be used
        $total = 0;
        $unq_account = null;
        foreach($data['ids'] as $invoice_id) {
            # fetching the invoice details
            $result = $this->get_invoice_details($invoice_id);
            if (empty($result) || $result['status'] == false) {
                $response = ['status' => false, 'error' => "Invoice does not exist anymore."];
                return $response;
            }
            $invoice_details = (array) $result['data'];
            $contact_id = $invoice_details['contact_id'];
            $unq_account[$contact_id][] = $invoice_id;
        }


        $total = 0;
        foreach($unq_account as $contact_id => $invoices) {

            # fetching contact details
            $email_contact = $this->Contact_model->get_contact_details($contact_id);
            if(empty($email_contact) || !isset($email_contact['EmailInput'][0]) || !isset($email_contact['EmailInput'][0]->email)) {
                $response = ['status' => false, 'error' => "Couldn't retrive invoice contact details!"];
                return $response;
            }

            # email data gether
            $emailData = null;
            $emailData['name'] = $email_contact['fullname'];
            $emailData['to_email'] = $email_contact['EmailInput'][0]->email;

            # adding individual invoice as attachment
            $file_path_arr = null;
            foreach($invoices as $invoice_id) {
                # generate PDF File
                $fileArr = [ 'id' => $invoice_id ];
                $filePDFArr = $this->generate_invoice_pdf($fileArr, $adminId, false);

                if (isset($filePDFArr) == true && isset($filePDFArr['pdfFilePath']) == true) {
                    $file_path = FCPATH . $filePDFArr['pdfFilePath'];
                    $emailData['file_attach'][] = $file_path;
                    $file_path_arr[] = $file_path;
                }
            }

            # send mail
            $result = $this->send_invoice($emailData);

            # remove app server files once email sent
            if(getenv('IS_APPSERVER_UPLOAD') != 'yes' && $file_path_arr) {
                foreach($file_path_arr as $file_path) {
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
            }

            foreach($invoices as $invoice_id) {
                $total++;

                # update invoice status
                $this->updateInvoiceStatus($invoice_id, 1, $adminId);

                # adding a log entry
                $msg = "Invoice mail sent successfully";
                $this->add_timesheet_invoice_log($emailData, $msg, $adminId, $invoice_id);
            }
        }

        $return = [ 'status' => true, 'msg' => $total.(($total > 1) ? " invoices" : " invoice")." emailed successfully"];
       
        return $return;        
    }

    /**
     * Update status as invoice sent
     * @param {int} invoice_id
     * @param {int} status
     * @param {int} adminID
    */
    public function updateInvoiceStatus($invoice_id, $status, $adminId) {
        # Updating status
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["status"] = $status;
        $result = $this->basic_model->update_records("finance_invoice", $upd_data, ["id" => $invoice_id]);

        # adding a log entry
        $msg = "Invoice status is updated successfully";
        $this->add_timesheet_invoice_log($upd_data, $msg, $adminId, $invoice_id);
    }

    /**
     * Send mail
     * @param {array} $emailData
     */
    public function send_invoice($emailData) {
        # load helper
        $this->load->helper('email_template_helper');

        # email content
        $content = '';
        $cc_email = '';
        $data = $emailData;
        $file_attach = $emailData['file_attach'] ?? [];       
        $to_email = $emailData['to_email'] ?? '';
        $email_subject = 'ONCALL Group Australia - Invoice/s';       

        # get mail content
        $content = $this->load->view('finance/invoice_mail_content', $data, true);

        # extra param
        $extraParam = [];
        $extraParam['from_label'] = "Oncall Service oncallservice@oncall.com.au on behalf of ONCALL Accounts accounts@oncall.com.au";

        # send email
        $status = send_mail($to_email, $email_subject, $content, $cc_email, $file_attach, $extraParam);

        return $status;
    }

      /*
     * its used for gettting account or participant name on base of @param $query
     */
    public function account_participant_name_search($ownerName = '', $skip_sites = false,$isNdis=false)
    {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $ndisId=$this->get_ndis_id();
        $this->db->select(["p.name as label", 'p.id as value', "'1' as account_type", "'0' as is_site"]);
        $this->db->from(TBL_PREFIX . 'participants_master as p');
        $this->db->where(['p.archive' => 0]);
        if($isNdis)
        {
            $this->db->where(['p.role_id' =>$ndisId]);
        } else {
            $this->db->where("p.role_id != ", $ndisId);
        }
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();
        $this->db->select(["o.name as label", 'o.id as value', "'2' as account_type", "is_site"]);
        $this->db->from(TBL_PREFIX . 'organisation as o');
        if($isNdis)
        {
            $this->db->where(['o.role_id' =>$ndisId]);
        } else {
            $this->db->where("o.role_id != ", $ndisId);
        }
        if($skip_sites)
            $this->db->where(['o.is_site' => 0]);
        $this->db->where(['o.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();
        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);
        return $result = $query->result();
    }


    public function get_ndis_id()
    {
        $this->db->select(["id"]);
        $this->db->from(TBL_PREFIX . 'member_role');
        $this->db->where (['name'=>'NDIS']);
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        if($result&&$result[0])
        {
            return  $result[0]['id'];
        } 
      
    }

    /**
     * Get list of service agreement based on account with status active only
     * @param {obj} reqData
     */
    public function get_service_agreement_by_participant($reqData) {

        # check data is valid or not
        if (empty($reqData) || empty($reqData->account) || !isset($reqData->account->value)) {
            return ['status' => false, 'error' => 'Account is required'];
        }

        if (isset($reqData) && isset($reqData->account)) {
            $participant_id = '';

            # Active status
            $sa_active_status = 5;

            if (isset($reqData->account) == true && isset($reqData->account->value) == true) {
                $participant_id = $reqData->account->value;
            }

            $column = [ "sa.service_agreement_id as label", "sa.id", "sa.contract_start_date", "sa.contract_end_date", "sa.status", "sa.plan_start_date", "sa.plan_end_date" ];
            $this->db->select($column);
            $this->db->from(TBL_PREFIX . 'service_agreement as sa');
            $this->db->where(['sa.participant_id' => $participant_id, 'sa.status' => $sa_active_status ]);
            $this->db->limit(1);
            $query = $this->db->get();
            $serviceAgreement = $query->num_rows() > 0 ? $query->row_array() : [];

            if (!empty($serviceAgreement) && !empty($serviceAgreement['id'])) {
                $serviceAgreementPayments = [];
                $managed = [ 1, 2, 3 ];
                $managed_label = [ 1 => 'Portal Managed', 2 => 'Plan Managed', 3 => 'Self Managed' ];
                $paymentType = [];
                $service_agreement_id = $serviceAgreement['id'];

                $this->load->model('../../sales/models/ServiceAgreement_model');

                # get payment methods
                $serviceAgreementPayments = $this->ServiceAgreement_model->service_agreement_payments($service_agreement_id);
                if (!empty($serviceAgreementPayments['managed_type']) && in_array($serviceAgreementPayments['managed_type'], $managed)) {
                    $paymentType = [];
                    $paymentType['value'] = $serviceAgreementPayments['managed_type'];
                    $paymentType['label'] = $managed_label[$serviceAgreementPayments['managed_type']];
                }
            }
            # return
            $data = [];            
            $data['service_agreement_id'] = $serviceAgreement['id'] ?? '';
            $data['service_agreement_payment'] = $paymentType ?? '';

            $result = [ "status" => true, 'msg' => 'Fetch service agreement successfully', 'data' => $data];
        } else {
            $result = [ "status" => false, 'error' => 'Participant Id is null'];
        }
        return $result;
    }

    /**
     * To get the Billing Address of the organisation selected in the SA Payment method of the Account (plan managed)
     * 
     * @param $sa_id {int} service agreement ID
     * 
     * @see service_agreement_payments
     * @see get_organisation_address
     * 
     * @return $billing_address {string} Billing address of the organization
     */
    public function payment_type_org_address_details($sa_id)
    {
        $billing_address = '';
        $this->load->model('../../sales/models/ServiceAgreement_model');
        $pay_details = $this->ServiceAgreement_model->service_agreement_payments($sa_id);
        
        if(!empty($pay_details)) {
            $this->load->model('../../organisation/models/Org_model');
            // get org billing address
            $billing_address_data = $this->Org_model->get_organisation_address($pay_details['organisation_id'], 1);
            
            if(!empty($billing_address_data)) {
                $org_select = json_decode($pay_details['organisation_select']);                
                $billing_address = $org_select->label . "<br>" . $billing_address_data->address;       
            }    
            
        }        
        return $billing_address;
    }

    /**
     * * getting invoice line items information of the added shifts only for NDIS
     */
    public function calc_invoice_line_items_ndis($invoice_id, $invoice_shifts) {

        $invoice_total = 0;
        $invoice_line_items = null;
        
        $this->load->model('schedule/Ndispayments_model');

        # fetching the associated reference ids of all pay rate and charge rate categories
        $existing_timesheet_line_items = $this->get_invoice_line_item_ids($invoice_id, $key_pair = true);
        $cat_ids = $this->get_pay_rate_ref_list("payrates_category", true);

        # going through all shifts to find its line items and appending invoice line items
        foreach($invoice_shifts as $shift_id) {
            # fetching shift information
            $result = $this->Schedule_model->get_shift_details($shift_id);
            if (empty($result) || $result['status'] != true) {
                $response = ['status' => false, 'error' => "Shift does not exist anymore."];
                return $response;
            }
            $shift_details = (array) $result['data'];
            $shift_member_id = $shift_details['accepted_shift_member_id'];

            # finding the shift member details
            $smresult = $this->Schedule_model->get_shift_member_details($shift_member_id, $shift_id);
            if (empty($smresult) || $smresult['status'] != true) {
                $response = ['status' => false, 'error' => "Shift member does not exist anymore."];
                return $response;
            }
            $shift_member_details = (array) $smresult['data'];

            # finding all active work types (roles) of member from current shift's work type
            $category_payrates = null;
            if($shift_details['role_id']) {
                $member_roles = $this->Member_model->get_member_active_roles($shift_member_details['member_id'], $shift_details['role_id'], date("Y-m-d"));

                # finding the pay rates information using the role details of member
                if($member_roles) {
                    $category_payrates = $this->get_chargerates_from_role_details($shift_details['cost_book_id'], $shift_details['role_id'], date("Y-m-d"), $member_roles);
                }
            }

            # fetching the list of line items - current shift
            $line_items_res = $this->Schedule_model->get_service_agreement_line_item_by_shift_id($shift_id, 2);
            
            $line_items = null;
            if(empty($line_items_res))
                continue;

            # calculating overall total from individual line item's total
            $line_items = object_to_array($line_items_res);

            foreach($line_items as $row) {
                $line_item = $row['line_item_id'];

                $unit_rate = $row['amount'];
                $duration_int = $row['duration_raw'];
                $mintues = $this->Ndispayments_model->hoursToMinutes($duration_int);
                $units = round(($mintues / 60),2);

                $total_cost = $row['sub_total'];
                $invoice_total += $total_cost;

                $invoice_line_items[] = [
                    'id' => null,
                    'shift_id' => $shift_id,
                    'account_id' => $shift_details['account_id'],
                    'account_type' => $shift_details['account_type'],
                    'category_id' => null,
                    'line_item_id' => $line_item,
                    'units' => $units,
                    'unit_rate' => $unit_rate,
                    'total_cost' => $total_cost,
                    'external_reference' => null
                ];
            }
        }
        return [$invoice_total, $invoice_line_items];
    }

   /*
     * fetching line item list based on the searched keyword
     */
    public function get_line_item_list_from_sa($keyword = '', $data) {
        if (empty($data->shift_id)) {
            return false;
        }
        $field = $data->field ?? 'code';
        if ($field == 'code') {
            $column = 'line_item_number';
        } else {
            $column = 'line_item_name';
        }
        $shift_id = $data->shift_id ?? '';
        # get shift details
        $shiftDetails = $this->Schedule_model->get_shift_details($shift_id);
        $shiftData = $shiftDetails['data'] ?? [];
        $startDate = $endDate = $curDate = date('Y-m-d');
        if ($shiftData->actual_sa_id) {
            $service_agreement_id = $shiftData->actual_sa_id;
        }
        if ($shiftData->actual_start_datetime) {
            $startDate = date('Y-m-d', strtotime($shiftData->actual_start_datetime));
        }
        if ($shiftData->actual_start_datetime) {
            $endDate = date('Y-m-d', strtotime($shiftData->actual_start_datetime));
        }
        
        $this->db->like("fli.{$column}", $keyword);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        # get added line item in service agreement items
        $this->db->select(["fli.line_item_number", 'fli.category_ref']);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_service_agreement as sa', 'sa.id = '.$service_agreement_id, 'inner');
        $this->db->join('tbl_service_agreement_items as sai', 'sai.line_item_id = fli.id and sai.archive = 0', 'inner');
        $this->db->where("sai.service_agreement_id", $service_agreement_id);
        $this->db->group_by("fli.id");
        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);
        $result = $query->result();

        # split the values
        $lineItemNumber = '';      
        if (!empty($result)) {
            $lineItemNumber = array_column($result, 'line_item_number');
            $lineItemNumber = implode("','", $lineItemNumber);
        }        

        # get line item
        $this->db->select(["fli.{$column} as label", 'fli.id as value', "fli.line_item_name", "fli.line_item_number as code ", "fli.upper_price_limit as rate"]);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->where("
            STR_TO_DATE('{$startDate}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`fli`.`start_date`, '%Y-%m-%d') AND DATE_FORMAT(`fli`.`end_date`, '%Y-%m-%d') AND
            STR_TO_DATE('{$endDate}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`fli`.`start_date`, '%Y-%m-%d') AND DATE_FORMAT(`fli`.`end_date`, '%Y-%m-%d')
            ");
        $this->db->group_start();
        $this->db->where("fli.line_item_number in ('{$lineItemNumber}') OR fli.category_ref in ('{$lineItemNumber}')");
        $this->db->group_end();
        $this->db->having($queryHaving);
        $this->db->group_by("fli.id");
        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);
        $linItem = $query->result();

        return $linItem;
    }
    
    /** Reorganize Line item list */
    function reorder_lineitem_list($line_items, $shift_day1, $shift_day2) {
        $temp_array = [];

        if(($shift_day1 == 'public_holiday' || $shift_day1 == 'sunday') 
            && ($shift_day2 == 'weekday' || $shift_day2 == 'saturday' || $shift_day2 == 'sunday')) {
            $temp_array[$shift_day1] = $line_items[$shift_day1];
            unset($line_items[$shift_day1]);
            $line_items = array_merge($temp_array, $line_items);
        }

        return $line_items;
    }

    /**
     * Pull the Ndis error table list
     * @param $reqData {obj} front end data
     * @param $filter_condition {obj} filter data coming from front end
     * 
     * @see ndis_error_listing_col() function for select and search column
     * @see get_shift_breaks_list
     * @see get_shift_breaks_list
     * @see shift_breaks_one_line
     * @see get_service_agreement_line_item_by_shift_id
     * @see pull_shift_warnings
     * 
     * @return $result {array} list of table data
     */
    function get_ndis_error_list($reqData, $filter_condition = '') {
        if (empty($reqData)) {
            return ["status" => FALSE, "message" => 'Something went wrong'];
        }

        $limit = $reqData->pageSize?? 10;
        $page = $reqData->page?? 1;

        $filter = $reqData->filtered?? NULL;
        
        $data = $this->ndis_error_listing_col();
        $select_column = $data['select_column'];
        $src_columns = $data['src_columns'];
        
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as account_fullname");
        
        # Searching column       
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if($column_search == 'sap.managed_type' && (strtolower($filter->search) == 'portal' || strtolower($filter->search) == 'plan' || strtolower($filter->search) == 'self')) {
                    
                    $fund_type = array_map('strtolower', $this->sa_fund_type );
                    $fund_type = array_search ($filter->search, $fund_type);
                    
                    $this->db->or_like($src_columns[$i], $fund_type);
                }
                else if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }        
       
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_member m', 'm.id = s.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = s.person_id', 'left');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id', 'left');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id', 'left');
        $this->db->join('tbl_sa_payments as sap', 'sap.service_agreement_id = s.actual_sa_id', 'left');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->where("s.archive", 0);
        $this->db->where("s.status", 5);
        $this->db->where("s.not_be_invoiced", 1);

        $this->db->order_by('s.created', 'DESC');

        $this->db->group_by('s.id');

        $this->db->limit($limit, ($page * $limit));
        
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        // Get the query result
        $result = $query->result();
        
        foreach($result as $key=> $val) {

            #Fund Manager Value
            if(!empty($val->fund_type)) {
                $result[$key]->fund_type = $this->sa_fund_type[$val->fund_type];
            }

            #Append breaks with Shit time
            $shift_breaks = $this->Schedule_model->get_shift_breaks_list($val->id);
            if($shift_breaks) {
                $result[$key]->shift_time .= $this->shift_breaks_one_line($shift_breaks);
            }

            if(!empty($val->actual_travel)) {
                $result[$key]->shift_time .= " (". str_replace('.00','',$val->actual_travel) ." KM)";         
            }

            if($val->role_name == "NDIS" && $val->account_type == 1) { 
                
                #Append Missing Rate into error
                $act_sa_line_item = $this->Schedule_model->get_service_agreement_line_item_by_shift_id($val->id, 2);
                $total = 0;
                
                $warning_message = $this->Schedule_model->pull_shift_warnings($val); 
               
                if (!empty($act_sa_line_item)) {
                    foreach($act_sa_line_item as $line_item) {
                        $total += $line_item->sub_total;
                    }
                    $miss_rate = array("Missing Rate/Cost");
                    #Append with existing error
                    if($total == 0 && !empty($warning_message) && !empty($warning_message->warning_messages)) {
                        array_push($warning_message->warning_messages, $miss_rate);
                        
                    } else if($total == 0 && empty($warning_message->warning_messages)) {
                        $warning_message = new stdclass();
                        $warning_message->is_warnable = TRUE;
                        $warning_message->warning_messages = $miss_rate;
                    } 
                    $result[$key]->warnings = $warning_message;
                } else if(!empty($warning_message)) { 
                    // Append error message only NDIS Service agreement found
                    $result[$key]->warnings = $warning_message;
                }
            } else {
                $result[$key]->warnings = $this->warning;
            }
            $more = '';
            if(!empty($result[$key]->warnings) && !empty($result[$key]->warnings->warning_messages) && 
                count($result[$key]->warnings->warning_messages) > 1) {
                $more = '...';
            }
            
            #Display only first error message in table view
            $result[$key]->short_warning_msg = (!empty($result[$key]->warnings->warning_messages)) ?
                 $result[$key]->warnings->warning_messages[0] . $more : NULL;

        }

        return ["status" => TRUE, "count" => $dt_filtered_total, "data" => $result,'total_item'=> $total_item];
       
    }

    /**
     * Display shift breaks in single line
     * 
     * @param $shift_breaks {obj} shift breaks
     * @return $text {str} return breaks text
     */
    public function shift_breaks_one_line($shift_breaks) {
        $text = ''; 
        if($shift_breaks) {
            if($shift_breaks[0]->key_name == 'unpaid' && count($shift_breaks) > 1) {
                $temp_array[] = $shift_breaks[0];
                unset($shift_breaks[0]);   
                $shift_breaks = array_merge($shift_breaks, $temp_array);
            }
            
            foreach($shift_breaks as $breaks) {
                if($breaks->key_name == 'sleepover') {
                    $text .= " S/O " . $breaks->break_start_time . " - " . $breaks->break_end_time;
                }
                if($breaks->key_name == 'interrupted_sleepover') {
                    $text .= ", Int.S/O " . $breaks->break_start_time . " - " . $breaks->break_end_time;
                }
                if($breaks->key_name == 'unpaid') {
                    $text .= " (".get_hour_minutes_from_int($breaks->duration_int, TRUE).")";
                }
            }
        }
            
        return $text;
    }

    /**
     * Splitup for select and search column
     */
    public function ndis_error_listing_col() {
        $data = [];
        
        $data['select_column'] = ["s.*","s.id", "s.shift_no as shift_id","r.name as role_name", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y') AS actual_start_datetime_format", "s.actual_start_datetime", "s.actual_end_datetime","concat(DATE_FORMAT(s.actual_start_datetime,'%h:%i %p'),' - ', DATE_FORMAT(s.actual_end_datetime,'%h:%i %p')) as shift_time", "concat(m.firstname,' ',m.lastname) as owner_fullname", "s.account_type", "s.account_id", "s.role_id", "s.actual_duration", "s.actual_sa_id", "sap.managed_type as fund_type", "s.actual_travel"];
       
         # Searching column
        $data['src_columns'] = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)","r.name as role_name",  "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)", "s.shift_no","DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y') as actual_start_datetime_format","s.actual_duration","concat(DATE_FORMAT(s.actual_start_datetime,'%h:%i %p'),' - ', DATE_FORMAT(s.actual_end_datetime,'%h:%i %p')) as shift_time", "sap.managed_type");
        
        return $data;
    }

}
