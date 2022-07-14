<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_line_item extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $days_applied = "Days Applied To";
        $time_applied = "Time Of Day Applied To";
        $this->line_item = [
            "funding_type" => "Funding Type", 
            "support_registration_group" => "Support Registeration Group", "support_category" => "Support Category", "support_purpose" => "Support Purpose", "support_type" => "Support Type", "support_outcome_domain" => "Support Outcome Domain", "line_item_number" => "Line Item Number", "line_item_name" => "Line Item Name", "category_ref" => "Category Ref", "description" => "Description", "quote_required" => "Quote Required", "price_control" => "Price Control", "travel_required" => "Travel Required", "cancellation_fees" => "Cancellation Fees", "ndis_reporting" => "NDIS Reporting", "non_f2f" => "None f2f", "levelId" => "Level", "pay_pointId" => "Pay Ponit", "units" => "Units", "schedule_constraint" => "Schedule Constratint", "member_ratio" => "Member Ratio", "participant_ratio" => "Participant Ratio", "measure_by" => "Measure By", "oncall_provided" => "Oncall Provided", "weekday" => $days_applied, "saturday" => $days_applied, "sunday" => $days_applied, "daytime" => $time_applied, "evening" => $time_applied, "overnight" => $time_applied, "sleepover" => $time_applied, "public_holiday" => $days_applied,
        ];
    }

    function index() {
        
    }

    public function add_update_line_item($reqData, $overwrite, $adminId, $requestData) {

        $create_new_line_item = false;
        $start_date = null;
        $end_date = null;

        if (!empty($reqData->start_date)) {
            $start_date = DateFormate($reqData->start_date, 'Y-m-d');
        }
        if (!empty($reqData->end_date)) {
            $end_date = DateFormate($reqData->end_date, 'Y-m-d');
        }
        
        $line_item_data = array(
            'funding_type' => $reqData->funding_type,
            'support_registration_group' => ((!empty($reqData->support_registration_group)) ? $reqData->support_registration_group : ''),
            'support_category' => ((!empty($reqData->support_category)) ? $reqData->support_category : ''),
            'support_outcome_domain' => ((!empty($reqData->support_outcome_domain)) ? $reqData->support_outcome_domain : ''),
            'line_item_number' => $reqData->line_item_number,            
            'line_item_name' => ((!empty($reqData->line_item_name)) ? $reqData->line_item_name : ''),
            'category_ref' => (!empty($reqData->category_ref) ? $reqData->category_ref : ''),
            'description' => ((!empty($reqData->description)) ? $reqData->description : ''),
            'quote_required' => ((!empty($reqData->quote_required) && $reqData->quote_required == 1) ? 1 : 0),
            'price_control' => ((!empty($reqData->price_control) && $reqData->price_control == 1) ? 1 : 0),
            'travel_required' => ((!empty($reqData->travel_required) && $reqData->travel_required == 1) ? 1 : 0),
            'cancellation_fees' => ((!empty($reqData->cancellation_fees) && $reqData->cancellation_fees == 1) ? 1 : 0),
            'ndis_reporting' => ((!empty($reqData->ndis_reporting) && $reqData->ndis_reporting == 1) ? 1 : 0),
            'non_f2f' => ((!empty($reqData->non_f2f) && $reqData->non_f2f == 1) ? 1 : 0),
            'levelId' => ((!empty($reqData->levelId)) ? $reqData->levelId : 0),
            'pay_pointId' => ((!empty($reqData->pay_pointId)) ? $reqData->pay_pointId : 0),
            'units' => $reqData->units ?? null,
            'schedule_constraint' => ((!empty($reqData->schedule_constraint) && $reqData->schedule_constraint == 1) ? 1 : 0),
            'weekday' => ((!empty($reqData->weekday) && $reqData->weekday == 1) ? 1 : 0),
            'saturday' => ((!empty($reqData->saturday) && $reqData->saturday == 1) ? 1 : 0),
            'sunday' => ((!empty($reqData->sunday) && $reqData->sunday == 1) ? 1 : 0),
            'public_holiday' => ((!empty($reqData->public_holiday) && $reqData->public_holiday == 1) ? 1 : 0),
            'daytime' => ((!empty($reqData->daytime) && $reqData->daytime == 1) ? 1 : 0),
            'evening' => ((!empty($reqData->evening) && $reqData->evening == 1) ? 1 : 0),
            'sleepover' => ((!empty($reqData->sleepover) && $reqData->sleepover == 1) ? 1 : 0),
            'overnight' => ((!empty($reqData->overnight) && $reqData->overnight == 1) ? 1 : 0),
            'member_ratio' => ((!empty($reqData->member_ratio)) ? $reqData->member_ratio : '0.00'),
            'participant_ratio' => ((!empty($reqData->participant_ratio)) ? $reqData->participant_ratio : '0.00'),
            'measure_by' => 1,
            'oncall_provided' => ((!empty($reqData->oncall_provided) && $reqData->oncall_provided == 1) ? 1 : 0),
            'support_type' => ((!empty($reqData->support_type)) ? $reqData->support_type : null),
            'support_purpose' => ((!empty($reqData->support_purpose)) ? $reqData->support_purpose : ''),
            'needs' => ((!empty($reqData->needs)) ? $reqData->needs : ''),
        );

        $li_price_data = array(
            'start_date' => $start_date ?? null,
            'end_date' => $end_date ?? null,
            'upper_price_limit' => ((!empty($reqData->upper_price_limit)) ? $reqData->upper_price_limit : ''),
        );

        $priceListLI = $this->Finance_line_item->check_li_price_exist_validation($reqData);
        
        # validate line item data mis-matched with existing and provided
        if (!empty($priceListLI)) {
            $mismatchField = [];
            # Get the mis-matched fields by value
            foreach($line_item_data as $lkey => $item) {
                if(isset($priceListLI[$lkey]) && $line_item_data[$lkey] != $priceListLI[$lkey]) {
                    $mismatchField[] = $this->line_item[$lkey];
                }
            }
            if (isset($mismatchField) && !empty($mismatchField)) {
                $mismatchField = array_unique($mismatchField);
                $misMatchFieldStr = implode(', ', $mismatchField);
                $response = ['status' => false, 'error' => 'Existing Line Item data mis-matched. Please provide same value - '. $misMatchFieldStr];
                echo json_encode($response);
                exit;
            }
        }

        # Update existing line item end date
        $res = $this->Finance_line_item->check_line_item_number_already_exist($reqData);
        if (!empty($res) && isset($res[0]) && count($res) == 1 && $overwrite == true) {
            $startDate = date('Y-m-d', strtotime($reqData->start_date));
            $updateDate = date('Y-m-d', strtotime($startDate.'-1 day'));
            $lineItem = $res[0];
            if (strtotime($startDate) > strtotime($lineItem['start_date'])) {
                # update end date
                $line_item_price_id = $lineItem['line_item_price_id'];
                $tmplineItem = [];
                $tmplineItem['end_date'] = $updateDate;                    
                $tmplineItem['updated_at'] = DATE_TIME;
                $tmplineItem['updated_by'] = $adminId;
                $update = $this->basic_model->update_records('finance_line_item_price', $tmplineItem, ['id' => $line_item_price_id]);
            }
        }
        
        // according to check condition insert or update
        if (!empty($reqData->lineItemId) && !$create_new_line_item) {           
            $line_item_details = $this->get_line_item_details($reqData->lineItemId);
            $line_itemId = $line_item_details['id'] ?? '';

            $priceList = $this->Finance_line_item->check_line_item_price_already_exist($reqData);           
            if (empty($priceList)) {                
                if ($line_itemId) {
                    $line_item_data['updated_at'] = DATE_TIME;
                    $line_item_data['updated_by'] = $adminId;
                    $this->basic_model->update_records('finance_line_item', $line_item_data, ['id' => $line_itemId]);
                }
            }

            $li_price_data['updated_at'] = DATE_TIME;
            $li_price_data['updated_by'] = $adminId;
            $this->basic_model->update_records('finance_line_item_price', $li_price_data, ['id' => $reqData->lineItemId]);
        } else {
            $line_item_data['created_at'] = DATE_TIME;
            $line_item_data['created_by'] = $adminId;
            
            $LIData = $this->Finance_line_item->check_line_item_number_exist($reqData);
            if (!empty($LIData)) {
                $line_item_id = $LIData['id'];
                $li_price_data['line_item_id'] = $line_item_id;
                $li_price_data['created_at'] = DATE_TIME;
                $li_price_data['created_by'] = $adminId;
                $line_itemId = $this->basic_model->insert_records('finance_line_item_price', $li_price_data, false);
            } else {
                $line_itemId = $this->basic_model->insert_records('finance_line_item', $line_item_data, false);
                $li_price_data['line_item_id'] = $line_itemId;
                $li_price_data['created_at'] = DATE_TIME;
                $li_price_data['created_by'] = $adminId;
                $line_itemId = $this->basic_model->insert_records('finance_line_item_price', $li_price_data, false);
            }
            
        }
        
        # Async API used to update the shift ndis line item price once price line item added with date range. 
        # Shift ndis line item price will be updated  only the line_item_price_id is null or 0 and date range or met with schedule and actual date range.  

        $this->load->library('Asynclibrary');
        $url = base_url()."finance/FinanceLineItem/update_price_line_item";
        $param = array('requestData' => $requestData);
        $this->asynclibrary->do_in_background($url, $param);

        return $line_itemId;
    }

    /**
    * Update Line Item - Aysnc
    * - Shift NDIS Line Item
    */
    public function update_price_line_item($adminId) {
         # Update the shift ndis line item price using trigger once price added with date range
        # To update the price id using snli.category = 1 for Shift schedule ndis with schedule end date
        # To update the price id using snli.category = 2 for Shift actual ndis with actual end date
        # NEW - the object of newly inserted data
        # snli.amount - updated based one ((price / 60) * totalmintues). ex (70/60) * 120) = 140

        $this->db->select(['snli.*', 's.scheduled_end_datetime', 's.actual_end_datetime','s.id as sid']);
        $this->db->from('tbl_shift_ndis_line_item as snli');
        $this->db->join('tbl_shift as s', 's.id = snli.shift_id AND s.archive = 0', 'INNER');
        $this->db->where('snli.archive', 0);
        $this->db->where('(snli.line_item_price_id IS NULL OR snli.line_item_price_id = 0 )');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ndisLI = $query->result_array();

        foreach($ndisLI as $key => $item) {
            $this->db->select(['flip.*']);
            $this->db->from("tbl_finance_line_item_price as flip");
            $this->db->where('flip.archive', 0);
            $this->db->where('flip.line_item_id', $item['line_item_id']);
            if ($item['category'] == 1) {
                $date = $item['scheduled_end_datetime'];
            } else {
                $date = $item['actual_end_datetime'];
            }
            $this->db->where("(STR_TO_DATE('{$date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`flip`.`start_date`, '%Y-%m-%d') AND DATE_FORMAT(`flip`.`end_date`, '%Y-%m-%d'))");
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $priceI = $query->row_array();

            if (!empty($priceI)) {
                $line_item_price_id = $priceI['id'];
                $upper_price_limit = $priceI['upper_price_limit'];

                $duration = $item['duration'];
                $snliID = $item['id'];
                $total_min = $this->hoursToMinutes($duration);
                if ($upper_price_limit > 0) {
                    $min_per_cost = $upper_price_limit / 60;
                } else {
                    $min_per_cost = 0;
                }
                # min per line item
                $cost_per_litem = $min_per_cost * $total_min;
                
                $updData = [];
                $updData['line_item_price_id'] = $line_item_price_id;
                $updData['price'] = $upper_price_limit;
                $updData['amount'] = $cost_per_litem;
                $updData['updated_by'] = $adminId;
                $updData['updated_at'] = DATE_TIME;
                 # Update not_be_invoiced column(tbl_shift) in background while updating line items for the shift
                if($upper_price_limit > 0)
                {
                    $this->update_not_be_invoiced($item['sid']);
                }
                
                # Update Line Item price id
                $this->basic_model->update_records('shift_ndis_line_item', $updData, ['id' => $snliID]);
            }
        }

        return true;
    }

    /**
     * Convert hours to mintues
     * @param {$hours} str
     */
    function hoursToMinutes($hours) 
    { 
        $minutes = 0; 
        if (strpos($hours, ':') !== false) 
        { 
            // Split hours and minutes. 
            list($hours, $minutes) = explode(':', $hours); 
        } 
        return $hours * 60 + $minutes; 
    }

    /**
     * Check the line item number is existing in master line item
     */
    function check_line_item_number_exist($reqData) {
        if (!empty($reqData->lineItemId)) {
            $this->db->where('id !=', $reqData->lineItemId, false);
        }

        $this->db->select(['fli.*']);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->where('fli.line_item_number', $reqData->line_item_number);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->row_array();
    }

    /**
     * Check the line item price existing
     */
    function check_line_item_price_already_exist($reqData) {
        if (!empty($reqData->lineItemId)) {
            $this->db->where('flip.id !=', $reqData->lineItemId, false);
        }
        $this->db->select(['fli.id', 'flip.id as line_item_price_id', 'fli.line_item_number', 'flip.start_date', 'flip.end_date']);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as flip', 'fli.id = flip.line_item_id', 'INNER');
        $this->db->where('fli.line_item_number', $reqData->line_item_number);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->result_array();
    }

    /**
     * Check the line item price existing
     */
    function check_li_price_exist_validation($reqData) {
        if (!empty($reqData->lineItemId)) {
            $this->db->where('flip.id !=', $reqData->lineItemId, false);
        }
        $this->db->select(["fli.id as line_item_id", "funding_type", "support_registration_group", "support_category", "support_purpose", "support_type", "support_outcome_domain", "line_item_number", "line_item_name", "category_ref", "description", "quote_required", "price_control", "travel_required", "cancellation_fees", "ndis_reporting", "non_f2f", "levelId", "pay_pointId", "units", "schedule_constraint", "member_ratio", "participant_ratio", "measure_by", "oncall_provided", "weekday", "saturday", "sunday", "daytime", "evening", "overnight", "sleepover", "public_holiday", "needs"]);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as flip', 'fli.id = flip.line_item_id', 'INNER');
        $this->db->where('fli.line_item_number', $reqData->line_item_number);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->row_array();
    }

    function update_line_item_state($states, $line_itemId) {
        $previous_data = $this->basic_model->get_record_where('finance_line_item_applied_state', ['stateId'], $where = ['line_itemId' => $line_itemId, 'archive' => 0]);
        $previous_data = array_column(obj_to_arr($previous_data), 'stateId');

        $archive_ids = [];
        if (!empty($states)) {

            foreach ($states as $val) {
                if (!empty($val->selected) && !in_array($val->id, $previous_data)) {

                    $state[] = ['line_itemId' => $line_itemId, 'stateId' => $val->id, 'archive' => 0, 'created' => DATE_TIME];
                } elseif (in_array($val->id, $previous_data) && empty($val->selected)) {
                    $archive_ids[] = $val->id;
                }
            }

            if (!empty($state)) {
                $this->basic_model->insert_records('finance_line_item_applied_state', $state, true);
            }

            if (!empty($archive_ids)) {
                $this->db->where('line_itemId', $line_itemId);
                $this->db->where_in('stateId', $archive_ids);
                $this->db->update('tbl_finance_line_item_applied_state', ['archive' => 1]);
            }
        }
    }

    function update_line_item_time($time_of_the_days, $line_itemId) {
        $previous_data = $this->basic_model->get_record_where('finance_line_item_applied_time', ['finance_timeId'], $where = ['line_itemId' => $line_itemId, 'archive' => 0]);
        $previous_data = array_column(obj_to_arr($previous_data), 'finance_timeId');

        $archive_ids = [];
        if (!empty($time_of_the_days)) {
            foreach ($time_of_the_days as $val) {
                if (!empty($val->selected) && !in_array($val->id, $previous_data)) {

                    $times[] = ['line_itemId' => $line_itemId, 'finance_timeId' => $val->id, 'archive' => 0, 'created' => DATE_TIME];
                } elseif (in_array($val->id, $previous_data) && empty($val->selected)) {
                    $archive_ids[] = $val->id;
                }
            }

            if (!empty($times)) {
                $this->basic_model->insert_records('finance_line_item_applied_time', $times, true);
            }

            if (!empty($archive_ids)) {
                $this->db->where('line_itemId', $line_itemId);
                $this->db->where_in('finance_timeId', $archive_ids);
                $this->db->update('tbl_finance_line_item_applied_time', ['archive' => 1]);
            }
        }
    }

    function update_line_item_week_days($week_days, $line_itemId) {
        $previous_data = $this->basic_model->get_record_where('finance_line_item_applied_days', ['week_dayId'], $where = ['line_itemId' => $line_itemId, 'archive' => 0]);
        $previous_data = array_column(obj_to_arr($previous_data), 'week_dayId');

        $archive_ids = [];
        if (!empty($week_days)) {
            foreach ($week_days as $val) {
                if (!empty($val->selected) && !in_array($val->id, $previous_data)) {

                    $days[] = ['line_itemId' => $line_itemId, 'week_dayId' => $val->id, 'archive' => 0, 'created' => DATE_TIME];
                } elseif (in_array($val->id, $previous_data) && empty($val->selected)) {
                    $archive_ids[] = $val->id;
                }
            }

            if (!empty($days)) {
                $this->basic_model->insert_records('finance_line_item_applied_days', $days, true);
            }

            if (!empty($archive_ids)) {
                $this->db->where('line_itemId', $line_itemId);
                $this->db->where_in('week_dayId', $archive_ids);
                $this->db->update('tbl_finance_line_item_applied_days', ['archive' => 1]);
            }
        }
    }

    /**
     * Get finance line item listing
     * @param {obj} $reqData - page details, filter & sort column
     * @param {obj} $filter_condition - Advanced filter
     */
    function get_finance_line_item_listing($reqData, $filter_condition = '') {
        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 0;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        
        $src_columns = array('fli.line_item_number', 'fli.line_item_name', "fli.description", "flip.upper_price_limit", "fli.units", "flip.start_date", "flip.end_date");

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

        
        # sorting part
        $available_column = array("id", 'line_item_number', 'line_item_name', 'start_date', "end_date", 'upper_price_limit', "funding_type", "description", 
                           "category_ref", "national_price_limit", "national_very_price_limit", "member_ratio", "participant_ratio", "schedule_constraint", 
                           "measure_by","support_registration_group","price_control", "units", "oncall_provided");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'flip.id';
            $direction = 'DESC';
        }

        $select_column = array("flip.id", 'fli.line_item_number', 'fli.line_item_name', 'flip.start_date', "flip.end_date", 'flip.upper_price_limit', "fft.name as funding_type", "fli.description", "fli.category_ref", "fli.member_ratio", "fli.participant_ratio", "fli.schedule_constraint", "fm.name as measure_by","fsrg.name as support_registration_group","fli.price_control", "fli.units", "fli.oncall_provided", "concat('$', flip.upper_price_limit) as rate", "fli.support_category", "fli.support_purpose", "fli.support_type", "fli.support_outcome_domain");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("case when fli.support_category!=0 THEN 
                (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category_label", false);
        $this->db->select("case when fli.support_outcome_domain!=0 THEN 
                (SELECT fsod.name as support_outcome_domain FROM tbl_finance_support_outcome_domain as fsod where fsod.id = fli.support_outcome_domain) ELSE '' END as support_outcome_domain_label", false);
        $this->db->select("case when fli.support_purpose!=0 THEN 
                (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose_label", false);
        $this->db->select("case when fli.support_type!=0 THEN 
                (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type_label", false);
        $this->db->select("concat('$',(SELECT flip.upper_price_limit FROM tbl_finance_line_item_price AS flip WHERE flip.line_item_id = fli.id ORDER BY end_date DESC LIMIT 1)) AS rate");
        $this->db->select("(SELECT flip.start_date FROM tbl_finance_line_item_price AS flip WHERE flip.line_item_id = fli.id ORDER BY end_date DESC LIMIT 1) AS start_date");
        $this->db->select("(SELECT flip.end_date FROM tbl_finance_line_item_price AS flip WHERE flip.line_item_id = fli.id ORDER BY end_date DESC LIMIT 1) AS end_date");
        $this->db->select("(SELECT flip.id FROM tbl_finance_line_item_price AS flip WHERE flip.line_item_id = fli.id ORDER BY end_date DESC LIMIT 1) AS id");
        $this->db->from('tbl_finance_line_item_price as flip');
        $this->db->join('tbl_finance_line_item as fli', 'fli.id = flip.line_item_id', 'INNER');
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.measure_by', 'left');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'left');
        $this->db->group_by('flip.line_item_id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
       
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $result = $query->result();
        $sorted = [];
        $sort_in = 0;

        if (!empty($result)) {
            foreach ($result as $val) {
                $start_date = (int) strtotime(DateFormate($val->start_date, "Y-m-d"));
                $end_date = (int) strtotime(DateFormate($val->end_date, "Y-m-d"));
                $current_date = (int) strtotime(date('Y-m-d'));
                $val->edit_status = 0;
                if ($start_date <= $current_date && $current_date <= $end_date) {
                    $val->status = "1"; //1 for active
                    $val->edit_status = 1;
                    $val->status_label = "Active";
                } elseif ($start_date > $current_date) {
                    $val->status = "2"; //2 inactive
                    $val->status_label = "InActive";
                } else {
                    $val->status = "3"; //3 archive
                    $val->status_label = "Archive";
                }

                # parent item
                if(!$val->category_ref) {
                    $val->status = "1"; //3 archive
                    $val->edit_status = 1;
                }             
            }
        }

        return array('total_item' => $dt_filtered_total , 'data' => $result, 'status' => true, 'count' => intVal($dt_filtered_total));
    }

    function archive_line_item($lineItemId) {
        $this->db->select(['flip.id', "flip.start_date", "flip.end_date"]);
        $this->db->from('tbl_finance_line_item_price as flip');
        $this->db->where('flip.id', $lineItemId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();

        if (!empty($result)) {
            $current_strtotime = strtotime(date('Y-m-d'));

            if (strtotime($result->start_date) > $current_strtotime) {
                $res = ['status' => false, 'error' => 'Inactive line item can not be archive'];
            } elseif (strtotime($result->end_date) < $current_strtotime) {
                $res = ['status' => false, 'error' => 'line item already archived'];
            } elseif (strtotime($result->end_date) == $current_strtotime) {
                $res = ['status' => false, 'error' => 'Line Item Will Be Archive Today'];
            } else {
                $this->basic_model->update_records('finance_line_item_price', ['end_date' => DATE_TIME], ['id' => $lineItemId]);

                $res = ['status' => true];
            }
        } else {
            $res = ['status' => false, 'error' => 'Line Item not found'];
        }

        return $res;
    }

    function get_line_item_details($lineItemId) {
        $this->db->select(['fli.id', "flip.id as line_item_price", "fli.funding_type", "fli.support_registration_group", "fli.support_category", "fli.support_outcome_domain", "fli.line_item_number", "fli.line_item_name", "fli.category_ref", "flip.start_date", "flip.end_date", "fli.description", "fli.quote_required", "fli.price_control", "fli.travel_required", "fli.cancellation_fees", "fli.ndis_reporting", "fli.non_f2f", "fli.schedule_constraint", "fli.public_holiday", "fli.member_ratio", "fli.participant_ratio", "fli.pay_pointId", "fli.units", "fli.levelId","fli.oncall_provided", "fli.support_type", "fli.support_purpose", "fli.needs", "fli.weekday", "fli.saturday", "fli.sunday", "fli.daytime", "fli.evening", "fli.sleepover", "fli.overnight", 
        "(CASE
                WHEN flip.upper_price_limit = '0.00' THEN ''
                ELSE flip.upper_price_limit
                END
            ) as upper_price_limit"
        ]);

        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as flip', 'fli.id = flip.line_item_id', 'INNER');
        $this->db->where('flip.id', $lineItemId);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row_array();

        if (!empty($result)) {
            $start_date = (int) strtotime(DateFormate($result['start_date'], "Y-m-d"));
            $end_date = (int) strtotime(DateFormate($result['end_date'], "Y-m-d"));
            $current_date = (int) strtotime(date('Y-m-d'));

            if ($start_date <= $current_date && $current_date <= $end_date) {
                $result['status'] = 1; // 1 for active
            } elseif ($start_date > $current_date) {
                $result['status'] = 2; // inactive
            } else {
                $result['status'] = 3; // archive
            }

             # parent item
            if(!$result['category_ref']) {
                $result['status'] = 1; 
            }
        } else {
            $result = [];
        }

        return $result;
    }

    function get_time_of_day_of_line_item($lineItemId) {
        $this->db->select(['f_time.id', "f_time.name"]);
        if ($lineItemId > 0) {
            $this->db->select('(select 1 from tbl_finance_line_item_applied_time where finance_timeId = f_time.id AND archive = 0 AND line_itemId = "' . $lineItemId . '" limit 1) as selected');
        }

        $this->db->from('tbl_finance_time_of_the_day as f_time');
        $this->db->where('archive', 0);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {
                if (!empty($val->selected)) {
                    $val->selected = $val->selected == 1 ? true : false;
                }
            }
        }

        return $result;
    }

    function get_week_days_of_line_item($lineItemId) {
        $this->db->select(['w_day.id', "w_day.name"]);
        if ($lineItemId > 0) {
            $this->db->select('(select 1 from tbl_finance_line_item_applied_days where week_dayId = w_day.id AND archive = 0 AND line_itemId = "' . $lineItemId . '" limit 1) as selected');
        }

        $this->db->from('tbl_finance_applied_days as w_day');
        $this->db->where('archive', 0);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {
                if (!empty($val->selected)) {
                    $val->selected = $val->selected == 1 ? true : false;
                }
            }
        }

        return $result;
    }

    function get_state_of_line_item($lineItemId) {
        $this->db->select(['s.id', "s.long_name"]);
        if ($lineItemId > 0) {
            $this->db->select('(select 1 from tbl_finance_line_item_applied_state where stateId = s.id AND archive = 0 AND line_itemId = "' . $lineItemId . '" limit 1) as selected');
        }

        $this->db->from('tbl_state as s');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {
                if (!empty($val->selected)) {
                    $val->selected = $val->selected == 1 ? true : false;
                }
            }
        }

        return $result;
    }

    function check_line_item_number_already_exist($reqData) {
        if (!empty($reqData->lineItemId)) {
            $this->db->where('flip.id !=', $reqData->lineItemId, false);
        }

        if (!empty($reqData->start_date) && !empty($reqData->end_date)) {
            $start_date = DateFormate($reqData->start_date, 'Y-m-d');
            $end_date = DateFormate($reqData->end_date, 'Y-m-d');

            $this->db->where('("' . $start_date . '" BETWEEN flip.start_date and flip.end_date OR "' . $end_date . '" BETWEEN flip.start_date and flip.end_date)', null, false);
        }

        $this->db->select(['fli.id', 'flip.id as line_item_price_id', 'fli.line_item_number', 'flip.start_date', 'flip.end_date']);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as flip', 'fli.id = flip.line_item_id', 'INNER');
        $this->db->where('fli.line_item_number', $reqData->line_item_number);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->result_array();
    }

    /**
     * Get support purpose by category
     * @param {obj} data
     * @param {int} adminId 
     */
    public function get_support_purpose_and_outcome_by_category($reqData, $adminId) {
        if (isset($reqData) && isset($reqData->support_category)) {
            # get support purpose id
            $support_purpose = $this->get_support_purpose_by_category($reqData->support_category, $adminId);

            # get support outcome option
            $support_outcome_domain_option = $this->get_support_outcome_by_category($reqData->support_category, $adminId);

            # get support outcome
            $support_outcome_domain = $this->get_support_outcome_id_by_category($reqData->support_category, $adminId);

            $result = [ "status" => true, 'msg' => 'Fetch Detail successfully', 'support_purpose' => $support_purpose, 'finance_support_outcome_domain' => $support_outcome_domain_option, 'support_outcome_domain' => $support_outcome_domain ];

        } else {
            $result = [ "status" => false, 'error' => 'Support category Id is null'];
        }
        return $result;
    }

    /**
     * Get support purpose by category
     * @param {obj} data
     * @param {int} adminId 
     */
    public function get_support_purpose_by_category($support_category, $adminId) {

        $support_category_id = $support_category;

        $this->db->select(['support_purpose_id']);
        $this->db->from('tbl_finance_support_purpose_mapping');
        $this->db->where('support_category_id', $support_category_id);
        $this->db->where('archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();
        $support_purpose = $result->support_purpose_id ?? '';

        return $support_purpose;
    }

    /**
     * Get support outcome by category
     * @param {obj} data
     * @param {int} adminId 
     */
    public function get_support_outcome_by_category($support_category, $adminId) {

        $support_category_id = $support_category;

        $this->db->select(['fsod.id as id', 'fsod.name as name']);
        $this->db->select('(
            CASE 
                WHEN fsodm.id IS NOT NULL THEN "false"
                ELSE "true"
                END
            ) as disabled
            ');
        $this->db->from('tbl_finance_support_outcome_domain as fsod');
        $this->db->join('tbl_finance_support_outcome_mapping as fsodm', 'fsodm.support_outcome_domain_id = fsod.id AND fsodm.support_category_id = '.$support_category_id, 'LEFT');
        $this->db->where('fsod.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();        

        return $result;
    }

    /**
     * Get support outcome by category
     * @param {obj} data
     * @param {int} adminId 
     */
    public function get_support_outcome_id_by_category($support_category, $adminId) {

        $support_category_id = $support_category;

        $this->db->select(['support_outcome_domain_id']);
        $this->db->from('tbl_finance_support_outcome_mapping');
        $this->db->where('support_category_id', $support_category);
        $this->db->where('archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();
        $support_outcome = $result->support_outcome_domain_id ?? '';       

        return $support_outcome;
    }

    public function get_support_type_by_category($reqData, $adminId) {
        if (isset($reqData) && !empty($reqData->support_category) && isset($reqData->line_item_name)) {
            $this->db->select(['item_name', 'support_type_id']);
            $this->db->from('tbl_finance_support_type_mapping');
            $this->db->where('support_category_id', $reqData->support_category);
            $this->db->where('archive', 0);
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $result = $query->result_array();

            $line_item_name = $reqData->line_item_name;
            $support_type = '';
            foreach ($result as $option) {
                if (stripos($line_item_name, $option['item_name']) !== false) {
                    $support_type = $option['support_type_id'];
                    break;
                }
            }
            $result = [ "status" => true, 'msg' => 'Fetch Detail successfully', 'support_type' => $support_type ];

        } else {
            $result = [ "status" => false, 'error' => 'Support category Id or Line item name is null'];
        }
        return $result;
    }


# Update not_be_invoiced column(tbl_shift) in background while updating line items for the shift
    public function update_not_be_invoiced($shift_id)
    {
        $this->load->model('schedule/Schedule_model');
        $data=$this->Schedule_model->get_shift_details($shift_id);
        //IF NDIS Shift doesn't have any line items then its marked as a invoice eligible as false
        $not_be_invoiced = 0;
        $shift_data=obj_to_arr($data['data']);
    
        if (empty($shift_data['actual_docusign_id']) || $shift_data['actual_docusign_id'] == '') {
            $not_be_invoiced = 1;
        }
    
        $is_line_item_exist = $this->basic_model->get_row('shift_ndis_line_item', ['id'], ['shift_id' => $shift_id]);
        if(!$is_line_item_exist)
        {
            $not_be_invoiced = 1;
                        
        }
        else{
            $missed_item = $this->basic_model->get_row('shift_ndis_line_item', ['id'], ['shift_id' =>  $shift_id, 'auto_insert_flag' => 1,'archive' => 0]);
            if($missed_item) { 
                $not_be_invoiced = 1;
            }
        }
    
        if (isset($shift_data['actual_docusign_id']) && !empty($shift_data['actual_docusign_id'])) {
            $service_agreement_id = $shift_data['actual_sa_id'] ?? '';
            $docusign_id = $shift_data['actual_docusign_id'] ?? '';
            $managed = [ 1 , 2 ];

            $this->load->model('../../sales/models/ServiceAgreement_model');
            # get payment methods
            $serviceAgreementPayments = $this->ServiceAgreement_model->service_agreement_payments($service_agreement_id);

            if (!empty($serviceAgreementPayments['managed_type']) && in_array($serviceAgreementPayments['managed_type'], $managed)) {
                $this->load->model('../../sales/models/Service_booking_model');
                $serviceBooking = $this->Service_booking_model->get_service_booking_with_status_by_id($service_agreement_id, '', $docusign_id);
                $sa_b_in = 0;

                if (empty($serviceBooking) || $serviceBooking == '') {
                    $not_be_invoiced = 1;
                }

                foreach($serviceBooking as $record) {
                    if ($record['status'] != 'active') {
                        $not_be_invoiced = 1;
                        break;
                    }
                    if ($record['is_received_signed'] != '1') {
                        $not_be_invoiced = 1;
                        break;
                    }
                }
            }
        }

        $this->basic_model->update_records('shift', array('not_be_invoiced' => $not_be_invoiced), array('id' => $shift_id));
    
    }

    /*
     * fetching the line item options for filter
     * - Support Category Options
     * - Support Type Options
     * - Support Outcome Domain Options
     * - Support Type
     */
    public function get_finance_line_item_filter_data() {
        $result = [];
        $result["support_category_options"] = $this->get_finance_line_item_filter_list("finance_support_category", "name", true);
        $result["support_purpose_options"] = $this->get_finance_line_item_filter_list("finance_support_purpose", "purpose", false);
        $result["support_outcome_domain_options"] = $this->get_finance_line_item_filter_list("finance_support_outcome_domain", "name", true);
        $result["support_type_options"] = $this->get_finance_line_item_filter_list("finance_support_type", "type", false);

        return ["status" => true, "data" => $result];
    }

    /**
     * Get finance line item support list options
     */
    function get_finance_line_item_filter_list($table_name, $label_name, $order = false) {
        $this->db->select(["td.{$label_name} as label", 'td.id as value']);
        $this->db->from(TBL_PREFIX . $table_name . ' as td');
        $this->db->where("td.archive", 0);
        if ($order) {
            $this->db->order_by("td.order", "ASC");
        } else {
            $this->db->order_by("td.id", "ASC");
        }
        $query = $this->db->get();
        return $query->result_array();
    }
}

class LineItemUnits
{
    // private function __construct() {}

    const each = 1 << 0;
    const hourly = 1 << 1;
    const daily = 1 << 2;
    const weekly = 1 << 3;
    const monthly = 1 << 4;
    const annually = 1 << 5;
    const km = 1 << 6;
}