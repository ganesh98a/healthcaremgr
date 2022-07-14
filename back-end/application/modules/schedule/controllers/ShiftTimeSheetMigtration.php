<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ShiftTimeSheetMigtration extends MX_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model('../../schedule/models/Schedule_model');
        $this->load->model('../../finance/models/Finance_model');
        $this->load->model('../../member/models/Member_model');
        $this->load->library('form_validation');
        $this->load->model('Basic_model');
        $this->form_validation->CI = & $this;
        $this->load->helper('i_pad');
    }

    function completed_shift_timesheet_list() {
        $reqestData = api_request_handler();

        # get shift list if actual duraion less than 3 hours to replace with two
        $shiftList = $this->basic_model->get_record_where_orderby('shift', ['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ], ['archive' => 0, 'status' => 5, 'actual_duration <' => '03:00'], 'id', 'ASC');
        $adminId = 1;

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift->id;
            $tsData = $this->Finance_model->get_timesheet_details($id = null, $shift_id);
            $ts = [];
            if (isset($tsData['data']) && !empty($tsData['data'])) {
                $timesheet = (array) $tsData['data'];
                $ts['id'] = $timesheet['id'];
                $ts['timesheet_no'] = $timesheet['timesheet_no'];
                $ts['shift_id'] = $timesheet['shift_id'];
                $ts['member_id'] = $timesheet['member_id'];
                $ts['amount'] = $timesheet['amount'];

                $existing_timesheet_line_item = $this->get_timesheet_line_item_ids($timesheet['id']);
                $ts['timesheet_line_items'] = $existing_timesheet_line_item;
            }
            $shiftList[$s_key]->time_sheet = $ts;
        }
        
        $data = [];
        $data['msg'] = 'List Successfully';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }

    /**
     * fetching existing timesheet line items
     */
    public function get_timesheet_line_item_ids($timesheet_id) {
        $this->db->select(["tsl.id", "r.key_name as key", "tsl.category_id", "tsl.units", "tsl.unit_rate", "tsl.total_cost", "tsl.external_reference" ]);
        $this->db->from('tbl_finance_timesheet_line_item as tsl');
        $this->db->join('tbl_finance_timesheet as ts', 'ts.id = tsl.timesheet_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = tsl.category_id', 'inner');
        $this->db->where('ts.archive', 0);
        $this->db->where('tsl.archive', 0);
        $this->db->where('tsl.timesheet_id', $timesheet_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->result();
    }

    /**
     * Update timesheet Unti as 2 as min instead of 3 
     */
    function completed_shift_timesheet_list_before_update() {
        $reqestData = api_request_handler();
    
        $reqestData->data = $reqestData;

        # get shift list if actual duraion less than 3 hours to replace with two
        $shiftList = $this->basic_model->get_record_where_orderby('shift', ['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ], ['archive' => 0, 'status' => 5, 'actual_duration <' => '03:00'], 'id', 'ASC');
        $adminId = 1;

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift->id;
            $updateLineItem = $this->create_shift_timesheet_invoice($shift_id, $adminId, false);
            $shiftList[$s_key]->time_sheet = $updateLineItem;
        }
        
        $data = [];
        $data['msg'] = 'Updated Successfully';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }

    /**
     * Update timesheet Unti as 2 as min instead of 3 
     */
    function completed_shift_timesheet_update() {
        $reqestData = api_request_handler();
    
        $reqestData->data = $reqestData;

        # get shift list if actual duraion less than 3 hours to replace with two
        $shiftList = $this->basic_model->get_record_where_orderby('shift', ['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ], ['archive' => 0, 'status' => 5, 'actual_duration <' => '03:00'], 'id', 'ASC');
        $adminId = 1;

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift->id;
            $updateLineItem = $this->create_shift_timesheet_invoice($shift_id, $adminId);
            $shiftList[$s_key]->time_sheet = $updateLineItem;
        }
        
        $data = [];
        $data['msg'] = 'Updated Successfully';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }

    /**
     * creates a timesheet record for shift & member combination who performed and completed
     * the shift. also creating an invoice for the account (participant or org)
     */
    public function create_shift_timesheet_invoice($shift_id, $adminId, $update = true) {

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

        # fetching timsheet information for a given shift
        $this->load->model('finance/Finance_model');
        $timesheet = $this->Finance_model->get_timesheet_details($id = null, $shift_id);
        $timesheet_id = null;
        if (isset($timesheet['data']) && !empty($timesheet['data'])) {
            $data['id'] = $timesheet['data']->id;
            $data['timesheet_no'] = $timesheet['data']->timesheet_no;
            $timesheet_id = $data['id'];
        }

        if ($timesheet_id == null || $timesheet_id == '') {
            $response = ['status' => false, 'error' => "Time Sheet Not exits."];
            return;
        }

        # making sure timsheet status is approved if actual shift timings are exactly the sheduled timings
        $timings_updated = false;
        if(($shift_details['actual_start_datetime'] != $shift_details['scheduled_start_datetime']) ||
        ($shift_details['actual_end_datetime'] != $shift_details['scheduled_end_datetime'])
        ) {
            $timings_updated = true;
        }
        else if(count($shift_details['actual_rows']) != count($shift_details['scheduled_rows'])) {
            $timings_updated = true;
        }
        else if(!empty($shift_details['actual_rows']) && !empty($shift_details['scheduled_rows'])) {
            $scheduled_rows = $shift_details['scheduled_rows'];
            $actual_rows = $shift_details['actual_rows'];
            for($i=0;$i<count($shift_details['scheduled_rows']);$i++) {
                if($scheduled_rows[$i]['break_duration'] != $actual_rows[$i]['break_duration']) {
                    $timings_updated = true;
                }
            }
        }

        # calculating the timesheet line items based on shift's actual timings
        list($timesheet_total, $timesheet_line_items) = $this->Schedule_model->calc_timesheet_line_items($timesheet_id, $shift_member_details, $shift_details);

        # creating/updating the timesheet
        $data['shift_id'] = $shift_id;
        $data['member_id'] = $shift_member_details['member_id'];
        $data['amount'] = $timesheet_total;
        $data['timesheet_line_items'] = $timesheet_line_items;

        if ($update == true) {
            $timesheet_response = $this->create_update_timesheet($data, $adminId);
        }        
        return $data;
    }

    /*
     * creating/updating timesheet information
     */
    public function create_update_timesheet($data, $adminId) {

        $postdata['timesheet_no'] = $data['timesheet_no'];
	    $postdata['shift_id'] = $data['shift_id'];
        $postdata['member_id'] = $data['member_id'];
        $postdata['amount'] = $data['amount'];
        $postdata['archive'] = 0;
        $timesheet_id = $data['id'] ?? 0;
        
        # adding updating timesheet line items
        if(isset($data['timesheet_line_items'])) {
            $line_items_data['timesheet_id'] = $timesheet_id;
            $line_items_data['timesheet_line_items'] = $data['timesheet_line_items'];
            $line_items_data['shift_id'] = $data['shift_id'];
            $line_items_res = $this->add_update_timesheet_line_items($line_items_data, $adminId, true);
            if (empty($line_items_res) || $line_items_res['status'] != true) {
                return $line_items_res;
            }
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Timesheet has been updated successfully.';
        } else {
            $msg = 'Timesheet has been created successfully.';
        }

        $response = ['status' => true, 'msg' => $msg, 'id' => $timesheet_id];
        return $response;
    }

    /**
     * adding updating timesheet line items
     */
    public function add_update_timesheet_line_items($data, $adminId, $upd_total = false) {
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
                $category_payrates = $this->Finance_model->get_payrates_from_role_details($shift_details['award_type_id'], $shift_details['role_id'], date("Y-m-d"), $member_roles, true);
            }
        }

        $existing_timesheet_line_item_ids = [];
        $selected_timesheet_line_item_ids = [];

        # fetching existing timesheet line items
        $existing_timesheet_line_item_ids = $this->Finance_model->get_timesheet_line_item_ids($data['timesheet_id']);

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
                "external_reference" => isset($category_payrates[$row['category_id']])?$category_payrates[$row['category_id']]:''
            ];

            # adding/updating an entry of timesheet line item
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("finance_timesheet_line_item", $postdata);
            }
            else {
                $id = $row['id'];
                $selected_timesheet_line_item_ids[] = $id;
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
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * updates the timesheet amount after going through all line items and total cost of them
     */
    public function refresh_timesheet_amount($timesheet_id, $adminId) {

        # fetching the list of line items
        $reqData = ["pageSize" => null, "page" => null, "sorted" => null,"filtered" => null, "timesheet_id" => $timesheet_id];
        $line_items_res = $this->Finance_model->get_timesheet_line_items_list((object) $reqData);

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
            ["amount" => $overall_total],
            ["id" => $timesheet_id]);
        return true;
    }

    /**
     * Get completed shift and time sheet for Public holiday
     */
    function completed_shift_timesheet_list_by_date() {
        $reqestData = api_request_handler();

        if (!isset($reqestData->date) || empty($reqestData->date)) {
            echo 'Please provide date (YYYY-MM-DD)';
            exit;
        }
        $date = $reqestData->date;
        $date_format = date('Y-m-d', strtotime($date));
        
        # get shift list if actual duraion less than 3 hours to replace with two
        $this->db->select(['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ]);
        $this->db->from('tbl_shift as s');
        $this->db->where("STR_TO_DATE('{$date_format}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`s`.`actual_start_datetime`, '%Y-%m-%d') AND DATE_FORMAT(`s`.`actual_end_datetime`, '%Y-%m-%d')");
        $this->db->where('s.archive', 0);
        $this->db->where('s.status', 5);
        $this->db->order_by('s.id', 'ASC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shiftList = $query->result_array();

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift['id'];
            $tsData = $this->Finance_model->get_timesheet_details($id = null, $shift_id);
            $ts = [];
            if (isset($tsData['data']) && !empty($tsData['data'])) {
                $timesheet = (array) $tsData['data'];
                $ts['id'] = $timesheet['id'];
                $ts['timesheet_no'] = $timesheet['timesheet_no'];
                $ts['shift_id'] = $timesheet['shift_id'];
                $ts['member_id'] = $timesheet['member_id'];
                $ts['amount'] = $timesheet['amount'];

                $existing_timesheet_line_item = $this->get_timesheet_line_item_ids($timesheet['id']);
                $ts['timesheet_line_items'] = $existing_timesheet_line_item;
            }
            $shiftList[$s_key]['time_sheet'] = $ts;
        }
        
        $data = [];
        $data['msg'] = 'Public Holiday List Successfully';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }

    /**
     * Get completed shift and time sheet for Public holiday
     */
    function completed_shift_timesheet_list_by_date_before_update() {
        $reqestData = api_request_handler();

        if (!isset($reqestData->date) || empty($reqestData->date)) {
            echo 'Please provide date (YYYY-MM-DD)';
            exit;
        }
        $date = $reqestData->date;
        $date_format = date('Y-m-d', strtotime($date));
        
        # get shift list if actual duraion less than 3 hours to replace with two
        $this->db->select(['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ]);
        $this->db->from('tbl_shift as s');
        $this->db->where("STR_TO_DATE('{$date_format}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`s`.`actual_start_datetime`, '%Y-%m-%d') AND DATE_FORMAT(`s`.`actual_end_datetime`, '%Y-%m-%d')");
        $this->db->where('s.archive', 0);
        $this->db->where('s.status', 5);
        $this->db->order_by('s.id', 'ASC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shiftList = $query->result_array();

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift['id'];
            $updateLineItem = $this->create_shift_timesheet_invoice($shift_id, '', false);
            $shiftList[$s_key]['time_sheet'] = $updateLineItem;
        }
        
        $data = [];
        $data['msg'] = 'Will be Updated';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }

    /**
     * Update completed shift and time sheet for date
     */
    function completed_shift_timesheet_list_by_date_update() {
        $reqestData = api_request_handler();

        if (!isset($reqestData->date) || empty($reqestData->date)) {
            echo 'Please provide date (YYYY-MM-DD)';
            exit;
        }
        $date = $reqestData->date;
        $date_format = date('Y-m-d', strtotime($date));
        
        # get shift list if actual duraion less than 3 hours to replace with two
        $this->db->select(['id', 'shift_no', 'actual_start_datetime', 'actual_end_datetime', 'actual_duration' ]);
        $this->db->from('tbl_shift as s');
        $this->db->where("STR_TO_DATE('{$date_format}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`s`.`actual_start_datetime`, '%Y-%m-%d') AND DATE_FORMAT(`s`.`actual_end_datetime`, '%Y-%m-%d')");
        $this->db->where('s.archive', 0);
        $this->db->where('s.status', 5);
        $this->db->order_by('s.id', 'ASC');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shiftList = $query->result_array();

        foreach($shiftList as $s_key => $shift) {
            $shift_id = $shift['id'];
            $updateLineItem = $this->create_shift_timesheet_invoice($shift_id, '');
            $shiftList[$s_key]['time_sheet'] = $updateLineItem;
        }
        
        $data = [];
        $data['msg'] = 'Will be Updated';
        $data['data'] = $shiftList;
        echo json_encode($data);
        exit;
    }
}