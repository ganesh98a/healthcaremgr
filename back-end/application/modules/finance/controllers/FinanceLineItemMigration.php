<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FinanceLineItemMigration extends MX_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Basic_model');
        $this->load->helper('i_pad');
        $this->lineItem = [
            "funding_type", "support_registration_group", "support_category", "support_purpose", "support_type", "support_outcome_domain", "line_item_number", "line_item_name", "category_ref", "description", "quote_required", "price_control", "travel_required", "cancellation_fees", "ndis_reporting", "non_f2f", "levelId", "pay_pointId", "units", "schedule_constraint", "member_ratio", "participant_ratio", "measure_by", "oncall_provided", "weekday", "saturday", "sunday", "daytime", "evening", "overnight", "sleepover", "archive",
            // "public_holiday"
        ];

        $this->commonField = [
            "funding_type", "support_registration_group", "support_category", "support_purpose", "support_type", "support_outcome_domain", "line_item_number", "line_item_name", "category_ref", "description", "quote_required", "price_control", "travel_required", "cancellation_fees",  "non_f2f", "levelId", "pay_pointId", "units", "schedule_constraint", "member_ratio", "participant_ratio", "measure_by", "oncall_provided", "public_holiday", "start_date", "end_date", "upper_price_limit"
        ];

        $this->lineItemPrice = [
            "id","line_item_id", "start_date", "end_date", "upper_price_limit", "archive"
        ];
    }

    /**
     * Compare Line item & line item old
     */
    public function listMasterLinetItem() {
        api_request_handler();
        $this->db->select($this->commonField);
        $this->db->from('tbl_finance_line_item as tfli');
        $this->db->join('tbl_finance_line_item_price as tflip', "tflip.line_item_id = tfli.id", "LEFT");
        $this->db->order_by('tfli.line_item_number', 'ASC');
        $query = $this->db->get();
        $mlItems = $query->result_array();

        $data = ['msg' => 'tbl_finance_line_item', 'count'=> count($mlItems), 'list' => $mlItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Compare Line item & line item old
     */
    public function listOldLinetItem() {
        api_request_handler();
        $this->db->select($this->commonField);
        $this->db->from('tbl_finance_line_item_old as tflio');
        $this->db->order_by('tflio.line_item_number', 'ASC');
        $this->db->group_by('tflio.line_item_number');
        $query = $this->db->get();
        $plItems = $query->result_array();

        $data = ['msg' => 'tbl_finance_line_item_old', 'count'=> count($plItems), 'list' => $plItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Compare Line item & line item old
     */
    public function listOldLinePriceItem() {
        api_request_handler();
        $this->db->select($this->commonField);
        $this->db->from('tbl_finance_line_item_old as tflio');
        $this->db->order_by('tflio.line_item_number', 'ASC');
        // $this->db->group_by('tflio.line_item_number');
        $query = $this->db->get();
        $plItems = $query->result_array();

        $data = ['msg' => 'tbl_finance_line_item_old', 'count'=> count($plItems), 'list' => $plItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Line Item Master List update
     */
    public function runLineItemMasterList() {
        api_request_handler();
        $this->db->select(['tflip.*']);
	    $this->db->from('tbl_finance_line_item_old as tflip');
	    $query = $this->db->get();
	    $line_items = $query->result();
        $rows = [];
        $exist = [];
        foreach ($line_items as $li_key => $litems) {
            $line_item_number = trim($litems->line_item_number);
            $this->db->select(['tfli.*']);
            $this->db->from('tbl_finance_line_item as tfli');
            $this->db->where('tfli.line_item_number', $line_item_number);
            $query = $this->db->get();
            $mlItems = $query->result_array();

            $insertLineItem = $this->lineItem;
            $insertLineItem = array_fill_keys($insertLineItem, null);
            ksort($insertLineItem);
            $litems = (array) $litems;
            ksort($litems);
            $insertLineItem = array_intersect_key($litems, $insertLineItem);

            if(!empty($mlItems)) {
                $exist[] = $insertLineItem; 
            } else {
                $rows[] = $insertLineItem;
                $this->db->insert('tbl_finance_line_item', $insertLineItem);
            }
            
        }
        $data = ['msg' => 'Update Successfully', 'exist_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'exist_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Line Item Price List update
     */
    public function updateLineItemPriceList() {
        api_request_handler();
        $this->db->select(['tflip.*']);
	    $this->db->from('tbl_finance_line_item_old as tflip');
	    $query = $this->db->get();
	    $line_items = $query->result();
        $rows = [];
        $exist = [];
        foreach ($line_items as $li_key => $litems) {
            $line_item_number = trim($litems->line_item_number);
            $this->db->select(['tfli.*']);
            $this->db->from('tbl_finance_line_item as tfli');
            $this->db->where('tfli.line_item_number', $line_item_number);
            $query = $this->db->get();
            $mlItems = $query->row();

            $insertLineItemPrice = $this->lineItemPrice;
            $insertLineItemPrice = array_fill_keys($insertLineItemPrice, null);
            ksort($insertLineItemPrice);
            $litems = (array) $litems;
            ksort($litems);
            $insertLineItemPrice = array_intersect_key($litems, $insertLineItemPrice);
            
            if(!empty($mlItems)) {
                $line_item_id = $mlItems->id;
                $insertLineItemPrice['line_item_id'] = $line_item_id;
                $this->db->insert('tbl_finance_line_item_price', $insertLineItemPrice); 
                $line_item_price_id = $this->db->insert_id();
                $insertLineItemPrice['id'] = $line_item_price_id;
                $rows[] = $insertLineItemPrice;
            } else {
                $exist[] = $litems;
            }
            
        }
        $data = ['msg' => 'Update Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Line Item Master - applied days update
     */
    public function updateLineItemMasterListDays() {
        api_request_handler();
        $this->db->select(['tflip.*']);
	    $this->db->from('tbl_finance_line_item_old as tflip');
	    $query = $this->db->get();
	    $line_items = $query->result();
        $rows = [];
        $exist = [];
        foreach ($line_items as $li_key => $litems) {
            $line_item_number = trim($litems->line_item_number);
            $line_item_id = $litems->id;
            $this->db->select(['tfliad.*']);
            $this->db->from('tbl_finance_line_item_applied_days as tfliad');
            $this->db->where('tfliad.line_itemId', $line_item_id);
            $this->db->where('tfliad.archive', 0);
            $query = $this->db->get();
            $adlItems = $query->result_array();

            $updateLIAD = [];
            foreach($adlItems as $adItem) {
                $week_dayId = $adItem['week_dayId'];
                switch($week_dayId) {
                    case "1":
                        $updateLIAD['weekday'] = 1;
                        break;
                    case "2":
                        $updateLIAD['saturday'] = 1;
                        break;
                    case "3":
                        $updateLIAD['sunday'] = 1;
                        break;
                    case "4":
                        $updateLIAD['public_holiday'] = 1;
                        break;
                    default:
                    break;
                }
            }
            if (isset($updateLIAD) && !empty($updateLIAD)) {
                $this->db->where("line_item_number", $line_item_number);
                $this->db->update("tbl_finance_line_item", $updateLIAD);
                $updateLIAD['line_item_number'] = $line_item_number;
                $updateLIAD['adlItems'] = $adlItems;
                $rows[] = $updateLIAD;
            } else {
                $updateLIAD['line_item_number'] = $line_item_number;
                $updateLIAD['adlItems'] = $adlItems;
                $exist[] = $updateLIAD;
            }
        }
        $data = ['msg' => 'Days Update Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Line Item Master - applied time of day update
     */
    public function updateLineItemMasterListTimeOfDay() {
        api_request_handler();
        $this->db->select(['tflip.*']);
	    $this->db->from('tbl_finance_line_item_old as tflip');
	    $query = $this->db->get();
	    $line_items = $query->result();
        $rows = [];
        $exist = [];
        foreach ($line_items as $li_key => $litems) {
            $line_item_number = trim($litems->line_item_number);
            $line_item_id = $litems->id;
            $this->db->select(['tfliat.*']);
            $this->db->from('tbl_finance_line_item_applied_time as tfliat');
            $this->db->where('tfliat.line_itemId', $line_item_id);
            $this->db->where('tfliat.archive', 0);
            $query = $this->db->get();
            $tdlItems = $query->result_array();

            $updateLITD = [];
            foreach($tdlItems as $tdItem) {
                $time_dayId = $tdItem['finance_timeId'];
                switch($time_dayId) {
                    case "1":
                        $updateLITD['daytime'] = 1;
                        break;
                    case "2":
                        $updateLITD['evening'] = 1;
                        break;
                    case "3":
                        $updateLITD['sleepover'] = 1;
                        break;
                    case "4":
                        $updateLITD['overnight'] = 1;
                        break;
                    default:
                    break;
                }
            }
            if (isset($updateLITD) && !empty($updateLITD)) {
                $this->db->where("line_item_number", $line_item_number);
                $this->db->update("tbl_finance_line_item", $updateLITD);
                $updateLITD['line_item_number'] = $line_item_number;
                $updateLITD['tdlItems'] = $tdlItems;
                $rows[] = $updateLITD;
            } else {
                $updateLITD['line_item_number'] = $line_item_number;
                $updateLITD['tdlItems'] = $tdlItems;
                $exist[] = $updateLITD;
            }
        }
        $data = ['msg' => 'Time Of Day Update Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Opportunity item - line item id update
     */
    public function updateOpportunityLineItemId() {
        api_request_handler();
        $this->db->select(['toi.*', 'tflip.line_item_id']);
	    $this->db->from('tbl_opportunity_items as toi');
        $this->db->join('tbl_finance_line_item_price as tflip', "tflip.id = toi.line_item_price_id", "LEFT");
	    $query = $this->db->get();
	    $opportunityItems = $query->result_array();
        $rows = [];
        $exist = [];
        foreach($opportunityItems as $okey => $oitem) {
            $oitem = (object) $oitem;
            if ($oitem->line_item_id) {
                $this->basic_model->update_records('opportunity_items', ['line_item_id' => $oitem->line_item_id], ['id' => $oitem->id]);
                $rows[] = $oitem;
            } else {
                $exist[] = $oitem;
            }
        }

        $data = ['msg' => 'Opportunity Updated Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Opportunity item - line item id list
     */
    public function listOpportunityLineItemId() {
        api_request_handler();
        $this->db->select(['toi.*']);
	    $this->db->from('tbl_opportunity_items as toi');
	    $query = $this->db->get();
	    $opportunityItems = $query->result_array();
        $data = ['msg' => 'Opportunity List Successfully', 'total_row' => count($opportunityItems), 'row' => $opportunityItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Service Agreement item - line item id update
     */
    public function updateServceiAgreementLineItemId() {
        api_request_handler();
        $this->db->select(['tsai.*', 'tflip.line_item_id']);
	    $this->db->from('tbl_service_agreement_items as tsai');
        $this->db->join('tbl_finance_line_item_price as tflip', "tflip.id = tsai.line_item_price_id", "LEFT");
	    $query = $this->db->get();
	    $SAItems = $query->result_array();
        $rows = [];
        $exist = [];
        foreach($SAItems as $okey => $oitem) {
            $oitem = (object) $oitem;
            if ($oitem->line_item_id) {
                $this->basic_model->update_records('service_agreement_items', ['line_item_id' => $oitem->line_item_id], ['id' => $oitem->id]);
                $rows[] = $oitem;
            } else {
                $exist[] = $oitem;
            }
        }

        $data = ['msg' => 'Service Agreement Updated Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Service Agreement item - line item id list
     */
    public function listServiceAgreementLineItemId() {
        api_request_handler();
        $this->db->select(['tsai.*']);
	    $this->db->from('tbl_service_agreement_items as tsai');
	    $query = $this->db->get();
	    $SAItems = $query->result_array();
        $data = ['msg' => 'Service Agreement List Successfully', 'total_row' => count($SAItems), 'row' => $SAItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Shift NDIS Line item - line item id update
     */
    public function updateShiftNDISLineItemId() {
        api_request_handler();
        $this->db->select(['tsnli.*', 'tflip.line_item_id']);
	    $this->db->from('tbl_shift_ndis_line_item as tsnli');
        $this->db->join('tbl_finance_line_item_price as tflip', "tflip.id = tsnli.line_item_price_id", "LEFT");
	    $query = $this->db->get();
	    $SAItems = $query->result_array();
        $rows = [];
        $exist = [];
        foreach($SAItems as $okey => $oitem) {
            $oitem = (object) $oitem;
            if ($oitem->line_item_id) {
                $this->basic_model->update_records('shift_ndis_line_item', ['line_item_id' => $oitem->line_item_id], ['id' => $oitem->id]);
                $rows[] = $oitem;
            } else {
                $exist[] = $oitem;
            }
        }

        $data = ['msg' => 'Shift NDIS Line Item Updated Successfully', 'not_updated_item_count' => count($exist), 'updated_item_count' => count($rows), 'updated_item' => $rows, 'not_updated_item' => $exist];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Shift NDIS Line item - line item id list
     */
    public function listShiftNDISLineItemId() {
        api_request_handler();
        $this->db->select(['tsnli.*']);
	    $this->db->from('tbl_shift_ndis_line_item as tsnli');
	    $query = $this->db->get();
	    $SAItems = $query->result_array();
        $data = ['msg' => 'Shift NDIS Line Item List Successfully', 'total_row' => count($SAItems), 'row' => $SAItems ];
        echo json_encode($data);
        exit(0);
    }

    /**
     * Line item - Price end date Update
     */
    public function updateLineItemPriceEndDate() {
        $request = api_request_handler();
        $current_end_date = $request->current_end_date ?? '';
        $update_end_date = $request->update_end_date ?? '';
        
        if (!empty($current_end_date) && !empty($update_end_date)) {
            $current_end_date = date('Y-m-d', strtotime($current_end_date));
            $update_end_date = date('Y-m-d', strtotime($update_end_date));

            # Update Price end date
            $updateLIPDate['end_date'] = $update_end_date;
            $this->db->where("end_date", $current_end_date);
            $this->db->update("tbl_finance_line_item_price", $updateLIPDate);
            $data = [ 'status' => true, 'msg' => 'Line Item end date updated successfully.'];
            echo json_encode($data);
            exit(0);
        } else {
            $data = [ 'status' => false, 'msg' => 'Current end date & Update end date is required.'];
            echo json_encode($data);
            exit(0);
        }
    }
}