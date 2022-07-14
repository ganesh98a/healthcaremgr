<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property-read \Attachment_model $Attachment_model
 * @package
 */
class Opportunity_model extends Basic_Model
{

    static $account_role_id = 4294967295;

    function __construct()
    {
        parent::__construct();
    }

    function get_field_history($data)
    {
        $items = [];

        $items = $this->db->from(TBL_PREFIX . 'opportunity_history as h')
            ->select(['h.id', 'h.id as history_id', 'f.id as field_history_id', 'f.field', 'f.value', 'f.prev_val', 'CONCAT(m.firstname, \' \', m.lastname) as created_by', 'h.created_at', 'hf.desc as feed_title', 'hf.id as feed_id'])
            ->where(['h.opportunity_id' => $data->opportunity_id])
            ->join(TBL_PREFIX . 'opportunity_field_history as f', 'f.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'opportunity_history_feed as hf', 'hf.history_id = h.id', 'left')
            ->join(TBL_PREFIX . 'opportunity as o', 'o.id = h.opportunity_id', 'left')
            ->join(TBL_PREFIX . 'member as m', 'm.uuid = h.created_by', 'left')
            ->order_by('h.id', 'DESC')
            ->get()->result();
            // echo last_query();
        // prefetching the list of possible types AoT, as there will not be high volumes of these records,
        // will be more effecient than repeated queries in loop
        $status_types           = $this->db->from(TBL_PREFIX . 'opportunity_status as s')->select('*')->where('archive', 0)->get()->result_array();
        $opportunity_src_types  = $this->determine_opportunity_source_options();
        $opportunity_types      = $this->determine_opportunity_types();

        $this->load->model('Feed_model');
        $related_type = $this->Feed_model->get_related_type('opportunity');

        $feed = [];
        // map fields to rendered values
        foreach ($items as $item) {
            $item->related_type = $related_type;
            $item->expanded = true;
            $item->feed = false;
            $item->comments = [];
            $history_id = $item->history_id;
            // history comments
            $comments = $this->Feed_model->get_comment_by_history_id($item->history_id, $related_type);
            $item->comments = $comments;
            $item->comments_count = count($comments);
            $item->comment_create = false;
            $item->comment_post = false;
            $item->comment_desc = '';
            if($item->field=='owner'){
                $item->field='Assigned To';
            }

            switch ($item->field) {
                case 'status':
                    $accessor        = function ($field) {
                        return ((array)$field)['id'];
                    };
                    $item->value     = isset($item->value) ? $this->map_history_field($status_types, $item->value, $accessor)['name'] ?? 'N/A' : 'N/A';
                    $item->prev_val  = isset($item->prev_val) ? $this->map_history_field($status_types, $item->prev_val, $accessor)['name'] ?? 'New' : 'New';
                    break;

                case 'Assigned To':
                    $owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->value])->get()->result();
                    $prev_owner = $this->db->from(TBL_PREFIX . 'member as m')->select('CONCAT(m.firstname, \' \', m.lastname) as user')->where(['id' => $item->prev_val])->get()->result();
                    $item->value = !empty($owner) ? $owner[0]->user : 'N/A';
                    $item->prev_val = !empty($prev_owner) ? $prev_owner[0]->user : 'N/A';
                    break;

                case 'amount':
                    $item->value     = isset($item->value) && is_numeric($item->value) ? '$' . $item->value : '$00.00';
                    $item->prev_val  = isset($item->prev_val) && is_numeric($item->prev_val) ? '$' . $item->prev_val : '$00.00';
                    break;

                case 'source':
                    $accessor        = function ($field) {
                        return $field['value'];
                    };
                    $item->value     = isset($item->value) ? $this->map_history_field($opportunity_src_types, $item->value, $accessor)['label'] ?? 'N/A' : 'N/A';
                    $item->prev_val  = isset($item->prev_val) ? $this->map_history_field($opportunity_src_types, $item->prev_val, $accessor)['label'] ?? 'N/A' : 'N/A';
                    break;

                case 'opportunity type':
                    $accessor        = function ($field) {
                        return $field['id'];
                    };
                    $item->value     = isset($item->value) ? $this->map_history_field($opportunity_types, $item->value, $accessor)['display_name'] ?? 'N/A' : 'N/A';
                    $item->prev_val  = isset($item->prev_val) ? $this->map_history_field($opportunity_types, $item->prev_val, $accessor)['display_name'] ?? 'N/A' : 'N/A';
                    break;

                case 'description':
                    $item->value = !empty($item->value) ? $item->value : 'N/A';
                    $item->prev_val = !empty($item->prev_val) ? $item->prev_val : 'N/A';
                    break;
                case 'NULL':
                case '':
                    $item->feed = true;
                    break;
                default:
                    $item->feed = false;
                    break;
            }
            if ($item->feed_id == '' || $item->feed_id == NULL) {
                $item->feed = false;
            }

            if (($item->field_history_id != '' && $item->field_history_id != NULL) || ($item->feed_id != '' && $item->feed_id != NULL)) {
                $feed[$history_id][] = $item;
            }
        }
        krsort($feed);
        $feed = array_values($feed);
        // let the client group history items
        echo json_encode((array) $feed);
    }

    private function map_history_field($types, $id, $accessor)
    {
        $map_fn = function ($id) use (&$types, $accessor) {
            return current(array_filter(
                $types,
                function ($src_type) use ($id, $accessor) {
                    return $accessor($src_type) == $id;
                }
            ));
        };

        return $map_fn($id);
    }

    function get_finance_active_line_item_listing($reqData)
    {
        $current_date_format = date('Y-m-d');
        $src_columns = array('fli.line_item_number', 'fli.category_ref', 'fli.line_item_name');

        if (!empty($reqData->data->srch_box)) {
            $srch_val = $reqData->data->srch_box;
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $srch_val);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $srch_val);
                }
            }
            $this->db->group_end();
        }


        $select_column = array("fli.id", 'fli.line_item_number', 'fli.line_item_name', 'fli.category_ref', 'tflip.start_date', "tflip.end_date", 'tflip.upper_price_limit', "fft.name as funding_type", "fm.name as measure_by", "fsrg.name as support_registration_group", "fli.price_control", "tflip.id as line_item_price_id");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);

        $this->db->select("true as qty_editable", false);
        $this->db->select("true as amount_editable", false);
        $this->db->select("1 as qty", false);
        $this->db->select("'' as amount", false);


        $this->db->select("case when fli.support_type!=0 THEN
        (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type", false);

        $this->db->select("case when fli.support_purpose!=0 THEN
        (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose", false);

        $this->db->select("case when fli.support_category!=0 THEN
            (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category", false);

        $this->db->from('tbl_finance_line_item as fli');
        # only get with range of current month/date
        $this->db->join('tbl_finance_line_item_price as tflip', 'tflip.line_item_id = fli.id  AND (STR_TO_DATE("'.$current_date_format.'", "%Y-%m-%d") BETWEEN DATE_FORMAT(tflip.start_date, "%Y-%m-%d") AND DATE_FORMAT(tflip.end_date, "%Y-%m-%d"))', 'LEFT', false);
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.units', 'inner');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'inner');
        $this->db->where("( fli.category_ref != '' AND fli.category_ref IS NOT NULL )");
        #$this->db->order_by($orderBy, $direction);
        #$this->db->limit(10);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query();
        #$dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        /*if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }*/
        $result = $query->result();
        $selected_line_item_ary = [];
        if (!empty($reqData->data->opp_id)) {
            $selected_line_item = $this->get_opportunity_items($reqData->data->opp_id);
            if (!empty($selected_line_item)) {
                $selected_line_item_ary = pos_index_change_array_data($selected_line_item, 'line_item_id');
            }
        }

        $childItem = [];
        $parentItemSearch = [];
        $current_date = (int) strtotime(date('Y-m-d'));
        if (!empty($result)) {
            foreach ($result as $k => $val) {
                $val->is_old_price = false;
                if (!$val->line_item_price_id || !isset($val->line_item_price_id) || $val->line_item_price_id == '') {
                    $line_item_id = $val->id;
                    $getLeastRate = $this->get_line_item_least_rate($line_item_id, $select_column, $current_date_format);
                    
                    if (!empty($getLeastRate) && !empty($getLeastRate['upper_price_limit'])) {
                        $val->upper_price_limit = $getLeastRate['upper_price_limit'];
                        $val->is_old_price = true;
                    }
                }
                $start_date = (int) strtotime(DateFormate($val->start_date, "Y-m-d"));
                $end_date = (int) strtotime(DateFormate($val->end_date, "Y-m-d"));                
                //skip child item that has their end date less than current date
                if (!empty($val->category_ref) && $end_date <= $current_date) {
                    unset($result[$k]);
                    continue;
                }
                if (!empty($selected_line_item_ary) && array_key_exists($val->id, $selected_line_item_ary)) {
                    $val->selected = true;
                    $val->qty = $selected_line_item_ary[$val->id]['qty'];
                    $val->amount = $selected_line_item_ary[$val->id]['amount'];
                    $val->incr_id_opportunity_items = $selected_line_item_ary[$val->id]['incr_id_opportunity_items'];
                } else {
                    $val->selected = false;
                    $val->qty = "";
                    $val->amount = '';
                    $val->incr_id_opportunity_items = 0;
                }

                if ($start_date <= $current_date && $current_date <= $end_date) {
                    $val->status = "1"; //1 for active
                } elseif ($start_date > $current_date) {
                    $val->status = "2"; //2 inactive
                } else {
                    $val->status = "3"; //3 archive
                }

                if (!empty($val->category_ref) && $val->category_ref != '') {
                    $category_ref = trim($val->category_ref);
                    $childItem[$category_ref][] = (array) $val;
                    $parentItemSearch[] = $val->category_ref;
                } else {
                    $childItem[] = $val;
                }
            }
        }

        # praent Item
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->select("true as qty_editable", false);
        $this->db->select("true as amount_editable", false);
        $this->db->select("1 as qty", false);
        $this->db->select("'' as amount", false);
        $this->db->select("case when fli.support_type!=0 THEN
            (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type", false);
        $this->db->select("case when fli.support_purpose!=0 THEN
        (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose", false);
        $this->db->select("case when fli.support_category!=0 THEN
            (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category", false);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->join('tbl_finance_line_item_price as tflip', 'tflip.line_item_id = fli.id', 'LEFT');
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.units', 'inner');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'left');
        $this->db->group_start();
        $this->db->where("fli.category_ref = '' OR fli.category_ref IS NULL");
        $this->db->group_end();
        if (!empty($reqData->data->srch_box) && !empty($parentItemSearch)) {
            $this->db->where_in('fli.line_item_number', array_values($parentItemSearch));
        }
        $this->db->order_by("fli.line_item_number");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $parentItem = $query->result();

        $sorted = [];
        foreach($parentItem as $p_key => $parent) {
            $start_date = (int) strtotime(DateFormate($parent->start_date, "Y-m-d"));
            $end_date = (int) strtotime(DateFormate($parent->end_date, "Y-m-d"));

            if (!empty($selected_line_item_ary) && array_key_exists($parent->id, $selected_line_item_ary)) {
                $parent->selected = true;
                $parent->qty = $selected_line_item_ary[$parent->id]['qty'];
                $parent->amount = $selected_line_item_ary[$parent->id]['amount'];
                $parent->incr_id_opportunity_items = $selected_line_item_ary[$parent->id]['incr_id_opportunity_items'];
            } else {
                $parent->selected = false;
                $parent->qty = "";
                $parent->amount = '';
                $parent->incr_id_opportunity_items = 0;
            }

            if ($start_date <= $current_date && $current_date <= $end_date) {
                $parent->status = "1"; //1 for active
            } elseif ($start_date > $current_date) {
                $parent->status = "2"; //2 inactive
            } else {
                $parent->status = "3"; //3 archive
            }

            $category = trim($parent->line_item_number);
            if (isset($childItem[$category]) && !empty($childItem[$category])) {
                $temp = [];
                $temp = $childItem[$category];
                array_push($sorted, $parent);
                $sorted = array_values($sorted);
                $sorted = array_merge($sorted, $temp);
                unset($childItem[$category]);
            } else {
                $sorted[$category] = $parent;
            }
        }

        $sorted = array_values($sorted);

        $return = array('data' => $sorted, 'status' => true);
        return $return;
    }

    /**
     * Get the least price rate of the line item 
     * @param {int} line_item_id
     * @param {array} select_clumn
     * @param {str} current_date_format
     */
    function get_line_item_least_rate($line_item_id, $select_column, $current_date_format) {
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->select("true as qty_editable", false);
        $this->db->select("true as amount_editable", false);
        $this->db->select("1 as qty", false);
        $this->db->select("'' as amount", false);

        $this->db->select("case when fli.support_type!=0 THEN
        (SELECT fst.type as support_type FROM tbl_finance_support_type as fst where fst.id = fli.support_type) ELSE '' END as support_type", false);

        $this->db->select("case when fli.support_purpose!=0 THEN
        (SELECT fsp.purpose as support_purpose FROM tbl_finance_support_purpose as fsp where fsp.id = fli.support_purpose) ELSE '' END as support_purpose", false);

        $this->db->select("case when fli.support_category!=0 THEN
            (SELECT fsc.name as support_category FROM tbl_finance_support_category as fsc where fsc.id = fli.support_category) ELSE '' END as support_category", false);

        $this->db->from('tbl_finance_line_item as fli');
        # only get with previous range of current month/date
        $this->db->join('tbl_finance_line_item_price as tflip', 'tflip.line_item_id = fli.id AND DATE_FORMAT(tflip.end_date, "%Y-%m-%d") <= STR_TO_DATE("'.$current_date_format.'", "%Y-%m-%d")', 'INNER', false);
        $this->db->join('tbl_funding_type as fft', 'fft.id = fli.funding_type', 'inner');
        $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.measure_by', 'inner');
        $this->db->join('tbl_finance_support_registration_group as fsrg', 'fsrg.id = fli.support_registration_group', 'inner');
        $this->db->where("( fli.category_ref != '' AND fli.category_ref IS NOT NULL ) AND fli.id = {$line_item_id}");
        $this->db->order_by('tflip.end_date', 'DESC');
        $this->db->limit(1);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $lineItemPrice = $query->row_array();

        return $lineItemPrice;
    } 

    function save_opportunity_item($data, $adminId, $fullData=[])
    {
        if (!empty($data)) {
            $softDelete = $tempDelete = $insData = $updateData = $tempInsert 
            =  $additional_payment = $addi_payment =  $tempUpdate = [];
            $before_update_amount = 0;
            $after_update_amount = 0;
            $opp_items = [];
            $before_update_item_sa_total_amount = 0;

            if (!empty($data['opportunity_id'])) {
                $reqData = new stdClass();
                $reqData->opportunity_id = $data['opportunity_id'];
                $opp_detail = $this->get_opportunity_detail($reqData, true);
                $before_update_amount = $opp_detail['amount'] ?? 0;
                $before_update_item_sa_total_amount = !empty($opp_detail['line_item_sa_total']) ? 
                $opp_detail['line_item_sa_total'] : 0;

                $opp_items = $this->get_opportunity_items($data['opportunity_id']);
            }

            foreach ($data as $item) {
                if (isset($item['selected']) && $item['selected'] == true) {
                    if ($item['incr_id_opportunity_items'] == 0) {
                        $tempInsert = [
                            'opportunity_id' => $data['opportunity_id'] ?? 0,
                            'line_item_id' => $item['id'] ?? 0,
                            'line_item_price_id' => $item['line_item_price_id'] ?? NULL,
                            'qty' => $item['qty'] ?? 0,
                            'amount' => $item['amount'] ?? 0,
                            'created_by' => $adminId,
                            'price' => $item['upper_price_limit'] ?? 0
                        ];
                        $insData[] = $tempInsert;
                    }
                    if ($item['incr_id_opportunity_items'] && $item['incr_id_opportunity_items'] > 0) {
                        $tempUpdate = [
                            'line_item_price_id' => $item['line_item_price_id'] ?? NULL,
                            'qty' => $item['qty'] ?? 0,
                            'amount' => $item['amount'] ?? 0,
                            'updated_by' => $adminId,
                            'id' => $item['incr_id_opportunity_items'],
                            'price' => $item['upper_price_limit'] ?? 0

                        ];
                        $updateData[] = $tempUpdate;
                    }
                } else {
                    if (isset($item['incr_id_opportunity_items']) && $item['incr_id_opportunity_items'] > 0) {
                        $tempDelete = [
                            'archive' => 1,
                            'id' => $item['incr_id_opportunity_items']
                        ];
                        $softDelete[] = $tempDelete;
                    }
                }
            }

            if (!empty($data['additional_rows'])) {
                foreach($data['additional_rows'] as $additem) {
                    $addi_payment = [
                        'opportunity_id' => $data['opportunity_id'] ?? 0,
                        'additional_title' => $additem['additional_title'] ?? '',
                        'additional_price' => $additem['additional_price'] ?? 0,
                        'created_by' => $adminId,
                        'created' => DATE_TIME
                    ];
                    $additional_payment[] = $addi_payment;
                }
                
            }
            if (!empty($insData)) {
                $this->basic_model->insert_records('opportunity_items', $insData, true);
                //get inserted records id for item history
                $ids = [];
                foreach($insData as $line_item) {
                    $ids[] = $line_item['line_item_id'];
                }

                $item_ids = $this->db->from(TBL_PREFIX . 'opportunity_items')
                                    ->select(['id', 'line_item_id'])
                                    ->where(['opportunity_id' => $data['opportunity_id']])
                                    ->where_in('line_item_id', array_values($ids))
                                    ->get()->result();
            }
            if (!empty($updateData)) {
                $this->basic_model->insert_update_batch('update', 'opportunity_items', $updateData, 'id');
            }
            if (!empty($softDelete)) {
                $this->basic_model->insert_update_batch('update', 'opportunity_items', $softDelete, 'id');
            }

             //Archive the existing element               
             $this->basic_model->update_records('opportunity_additional_fund', ['archive' => 1],
             ['opportunity_id' => $data['opportunity_id']]);

            if(!empty($additional_payment)) {
                
                $this->basic_model->insert_records('opportunity_additional_fund', $additional_payment, true);
            }

            if (!empty($insData) || !empty($updateData) || !empty($softDelete)) {
                if (!empty($data['opportunity_id'])) {

                    //Update the sa total and total values
                    $this->basic_model->update_records('opportunity', 
                    ['line_item_sa_total' => $fullData->data->line_item_sa_total??0,
                    'line_item_total' => $fullData->data->line_item_total??0,
                    'amount' => $fullData->data->line_item_total??0],
                    
                    ['id' => $data['opportunity_id']]);

                    $updated_opp_items = $this->get_opportunity_items($data['opportunity_id']);
                    $after_update_amount = array_sum(array_column($updated_opp_items, 'amount'));
                    if (empty($after_update_amount) && !empty($opp_detail['amount'])) {
                        $after_update_amount = $opp_detail['amount'];
                    }
                    if ($before_update_amount != $after_update_amount) {
                        $bSuccess = $this->db->insert(
                            TBL_PREFIX . 'opportunity_history',
                            [
                                'opportunity_id' => $data['opportunity_id'],
                                'created_by' => $adminId,
                                'created_at' => date('Y-m-d H:i:s')
                            ]
                        );

                        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());

                        $history_id = $this->db->insert_id();
                        $this->create_field_history_entry($history_id, $data['opportunity_id'], 'amount', $after_update_amount, $before_update_amount);

                        $this->create_field_history_entry($history_id, $data['opportunity_id'], 'line_item_sa_total', $fullData->data->line_item_sa_total, $before_update_item_sa_total_amount);

                        $this->create_field_history_entry($history_id, $data['opportunity_id'], 'line_item_total', $fullData->data->line_item_total, $before_update_amount);
                    }
                }
                //record item history
                
                if (!empty($insData) && !empty($item_ids)) {
                    foreach($data as $i => $opp_item) {
                        if (!is_array($opp_item) || empty($opp_item['id'])) {
                            continue;
                        }
                        foreach($item_ids as $item_obj) {                           
                            if ($item_obj->line_item_id == $opp_item['id']) { 
                                $opp_item['incr_id_opportunity_items'] = $item_obj->id;
                                $data[$i] = $opp_item;
                            }
                        }

                    }
                }
                //Skip the additional rows
                unset($data['additional_rows']);
                $this->updateHistory($opp_items, $data, $data['opportunity_id'], $adminId);
            }
        }
        return true;
    }

    /*
     *its use for search owner staff in database table (admin user)
     *
     * @params
     * $ownerName search key parameter
     *
     *
     * return type array
     *
     */
    public function get_owner_staff_by_name($ownerName = '')
    {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.uuid as value']);
        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
        $this->db->where(['m.archive' => 0]);
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get account person name on base of @param $ownerName
     *
     * @params
     * $ownerName search parameter
     *
     * return type array
     * also used in create service agreement
     */
    public function get_account_person_name_search($ownerName = '')
    {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label","p.middlename","p.previous_name", 'p.id as value', "'1' as account_type"]);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["o.name as label", 'o.id as value',"'' as middlename","'' as previous_name", "'2' as account_type"]);
        $this->db->from(TBL_PREFIX . 'organisation as o');
        $this->db->where(['o.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);

        return $result = $query->result();
    }

    /*
     * its use for search lead number on the base searching @param
     *
     * @params
     * $ownerName use for search in database key
     *
     * return type array
     */
    public function get_lead_number_search($ownerName = '')
    {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["l.lead_number as label", 'l.id as value']);
        $this->db->from(TBL_PREFIX . 'leads as l');
        $this->db->where(['l.archive' => 0]);
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get option of lead source
     *
     * return type array
     * in form label value
     */
    public function get_lead_source()
    {
        $this->db->select(["lsc.name as label", 'lsc.id as value']);
        $this->db->from(TBL_PREFIX . 'lead_source_code as lsc');
        $this->db->where(['lsc.archive' => 0]);
        $this->db->order_by('lsc.order_ref', 'ASC');
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get get option of opportunity type option
     *
     * return type array
     */
    function get_opportunity_type_option()
    {
        $this->db->select(["ot.display_name as label", 'ot.id as value', 'ot.key_name']);
        $this->db->from('tbl_references as ot');
        $this->db->where(['ot.archive' => 0, 'ot.type' => 17]);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get opportunity initial status id
     *
     * return type id (string)
     */
    function get_opportunity_initial_status_id()
    {
        $this->db->select(["os.id"]);
        $this->db->from('tbl_opportunity_status as os');
        $this->db->where(['os.archive' => 0]);
        $this->db->where(['os.key_name' => "new"]);
        return $query = $this->db->get()->row("id");
    }

    /*
     * its use for create opportunity
     *
     * @params
     * $data its reqdata
     * $adminId created by
     *
     * @todo: MISLEADING METHOD NAME!
     * this is not  create' opportunity anymore, it is more like 'save' opportunity
     *
     * return type opportunityId
     */
    function create_opportunity($data, $adminId)
    {

        if (isset($data["opportunity_id"])) { // existing opportunity - record field history
            $formerState = $this->db->from(TBL_PREFIX . 'opportunity')
                ->select('*')
                ->where(['id' => $data["opportunity_id"]])
                ->get()->result();

            // create an OpportunityHistoryField entry
            $bSuccess = $this->db->insert(
                TBL_PREFIX . 'opportunity_history',
                [
                    'opportunity_id' => $data['opportunity_id'],
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );

            if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());

            $history_id = $this->db->insert_id();

            if (!empty($formerState)) {
                $formerState = $formerState[0];

                if (isset($data["topic"]) && $data["topic"] != $formerState->topic)
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'topic', $data['topic'], $formerState->topic);

                if (isset($data["opportunity_type"]) && $data["opportunity_type"] != $formerState->opportunity_type)
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'opportunity type', $data['opportunity_type'], $formerState->opportunity_type);


                if (isset($data["amount"]) && (double)$data["amount"] !== (double)$formerState->amount) {
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'amount', $data['amount'], $formerState->amount);
                }

                if (isset($data["owner"]) && $data["owner"] != $formerState->owner)
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'owner', $data['owner'], $formerState->owner);

                if (isset($data["oppurtunity_description"]) && $data["oppurtunity_description"] != $formerState->oppurtunity_description)
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'description', $data['oppurtunity_description'], $formerState->oppurtunity_description);

                if (isset($data["opportunity_source"]) && $data["opportunity_source"] != $formerState->opportunity_source)
                    $this->create_field_history_entry($history_id, $data['opportunity_id'], 'source', $data['opportunity_source'], $formerState->opportunity_source);
            }
        }

        $insData = [
            'topic' => $data["topic"],
            'opportunity_source' => (!empty($data['opportunity_source'])) ? $data['opportunity_source'] : null,
            'related_lead' => (!empty($data['related_lead'])) ? $data['related_lead'] : null,
            'opportunity_type' => $data['opportunity_type'],
            'need_support_plan' => $data['need_support_plan'] ?? 0,
            'account_person' => (!empty($data['account_person'])) ? $data['account_person'] : null,
            'account_type' => (!empty($data['account_type'])) ? $data['account_type'] : null,
            'amount' => $data['amount'] ?? '',
            'oppurtunity_description' => $data['oppurtunity_description'] ?? '',
            'owner' => (!empty($data['owner'])) ? $data['owner'] : null,
            'opportunity_status' => $data['opportunity_status'] ?? $this->get_opportunity_initial_status_id(),
            'archive' => 0,
        ];
        if (isset($data['opportunity_id'])) {
            $insData['updated_by'] = $adminId;
            $insData['updated'] = DATE_TIME;
            $this->basic_model->update_records('opportunity', $insData, array('id' => $data['opportunity_id']));
            $opportunityId = $data['opportunity_id'];

            // if the Account field for this Opp was updated, remove prev relationship
            $this->db->delete(TBL_PREFIX . 'sales_relation', 'tbl_sales_relation.roll_id=' . Opportunity_model::$account_role_id . ' AND tbl_sales_relation.source_data_type=1 AND tbl_sales_relation.destination_data_id=' . $opportunityId . ' AND tbl_sales_relation.destination_data_type=3');
        } else {
            $insData['created_by'] = $adminId;
            $insData['created'] = DATE_TIME;
            $insData['updated'] = DATE_TIME;

            $opportunityId = $this->basic_model->insert_records('opportunity', $insData);

            // new opp - create specialised field history
            $bSuccess = $this->db->insert(
                TBL_PREFIX . 'opportunity_history',
                [
                    'opportunity_id' => $opportunityId,
                    'created_by' => $adminId,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            );
            $history_id = $this->db->insert_id();

            $this->create_field_history_entry($history_id, $opportunityId, 'created', '', '');
        }

        // establish contact -> opp relationship
        if (!empty($data['account_person'])) {
            $this->basic_model->insert_records('sales_relation', ['source_data_type' => 1, 'source_data_id' => $data['account_person'], 'destination_data_type' => 3, 'destination_data_id' => $opportunityId, 'roll_id' => Opportunity_model::$account_role_id, 'is_primary' => 0, 'updated' => DATE_TIME, 'archive' => 0]);
        }

        return $opportunityId;
    }

    private function create_field_history_entry($history_id, $opportunity_id, $field, $val, $prev_val)
    {

        $bSuccess = $this->db->insert(TBL_PREFIX . 'opportunity_field_history', [
            'history_id' => $history_id,
            'opportunity_id' => $opportunity_id,
            'field' => $field,
            'prev_val' => $prev_val,
            'value' => $val
        ]);

        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());
    }

    /*
     * its use for making sub query of owner name used in listing opportunity
     * return type sql
     */
    private function get_owner_name_sub_query()
    {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = o.owner", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }
    
    /*
     * its use for making sub query of account name
     * return type sql
     */
    private function get_account_name_sub_query()
    {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person as sub_p');
        $this->db->where("sub_p.id = o.account_person", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use making subquery of related lead of opportunity
     * return type sql
     */
    private function get_related_lead_sub_query()
    {
        $this->db->select("sub_l.lead_number");
        $this->db->from(TBL_PREFIX . 'leads as sub_l');
        $this->db->where("sub_l.id = o.related_lead", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for making sub query of opportunity status
     * return type sql
     */
    private function get_opportunity_status_sub_query()
    {
        $this->db->select("sub_os.name");
        $this->db->from(TBL_PREFIX . 'opportunity_status as sub_os');
        $this->db->where("sub_os.id = o.opportunity_status", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for making sub query of opportunity type
     * return type sql
     */
    private function get_opportunity_type_sub_query()
    {
        $this->db->select("sub_ot.display_name");
        $this->db->from(TBL_PREFIX . 'references as sub_ot');
        $this->db->where("sub_ot.id = o.opportunity_type", null, false);
        $this->db->where(['sub_ot.type' => 17, 'sub_ot.archive' => 0]);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }


    /*
     * its use for get option of opportunity option
     * return type array object
     */
    function get_opportunity_status_option()
    {
        $this->db->select(["id as value", "name as label"]);
        $this->db->from(TBL_PREFIX . 'opportunity_status');
        $this->db->where("archive", 0);

        return $this->db->get()->result();
    }
    /*
     * its use for get option of opportunity description
     */
    function get_oppurtunity_description_sub_query()
    {
        $this->db->select("sub_od.name");
        $this->db->from(TBL_PREFIX . 'oppurtunity_description as sub_od');
        $this->db->where("sub_od.id = o.oppurtunity_description", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * its use for get opportunity list
     *
     * @params
     * $reqData request data like special opration like filter,search, sort
     *
     * return type array
     *
     */
    function get_opportunity_list($reqData, $adminId, $filter_condition = '', $uuid_user_type='')    {
        
        $ownder_name_sub_query = $this->get_owner_name_sub_query();
        $account_name_sub_query = $this->get_account_name_sub_query();
        $related_lead_sub_query = $this->get_related_lead_sub_query();
        $opportunity_status_sub_query = $this->get_opportunity_status_sub_query();
        $this->load->model("Common/Common_model");
        $opportunity_created_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("o",$uuid_user_type,"created_by");
        $get_opportunity_type_sub_query = $this->get_opportunity_type_sub_query();
        $oppurtunity_description_sub_query = $this->get_oppurtunity_description_sub_query($uuid_user_type);

        $limit = $reqData->pageSize ?? 99999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        $src_columns = array('opportunity_number', 'topic', "owner_name", "account", "related_lead", "created_by", "opportunity_type", "oppurtunity_description", "created", "viewed_date", "viewed_by");

        if (isset($filter->search) && $filter->search != '') {

            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                if($column_search == 'created' || $column_search == 'viewed_date') {
                    $this->db->or_like($column_search, DateFormate(str_replace('/','-', $filter->search), 'Y-m-d'));
                }
                else if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_column = [ "opportunity_number", "topic", "created", "opportunity_id", "amount", "oppurtunity_description",
                              "viewed_date", "vl.viewed_by", "viewed_by_id", "op_status"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                if ($orderBy == 'created_format') {
                    $orderBy = "l.created";
                }
            }
        } else {
            $orderBy = 'o.id';
            $direction = 'DESC';
        }

        $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
        $viewedLog = new ViewedLog();
        // get entity type value
        $entity_type = $viewedLog->getEntityTypeValue('opportunity');

        $select_column = [ "opportunity_number", "topic", "created", "o.id as opportunity_id", "amount", "oppurtunity_description",
            'vl.viewed_date',
            'vl.viewed_by',
            'vl.viewed_by as viewed_by_id', 'opportunity_status as op_status'
        ];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $ownder_name_sub_query . ") as owner_name");
        $this->db->select("(CASE
            when o.account_type = 1 THEN (" . $account_name_sub_query . ")
            when o.account_type = 2 THEN (select sub_o.name from tbl_organisation as sub_o where sub_o.id = o.account_person)
            else ''
            end)
            as account");
        $this->db->select("(" . $related_lead_sub_query . ") as related_lead");
        $this->db->select("(" . $opportunity_status_sub_query . ") as status");
        $this->db->select("(" . $opportunity_created_by_sub_query . ") as created_by");
        $this->db->select("(" . $get_opportunity_type_sub_query . ") as opportunity_type");
        $this->db->select("(select concat(mvl.firstname,' ',mvl.lastname) as viewed_by_name from tbl_member as mvl join tbl_users as u on u.id=mvl.uuid where u.id = vl.viewed_by) as viewed_by");
        $this->db->from("tbl_opportunity as o");
        $this->db->join('tbl_viewed_log as vl', 'o.id = vl.entity_id AND vl.id = ( SELECT id FROM tbl_viewed_log as ivl WHERE o.id = ivl.entity_id  AND ivl.entity_type = '.$entity_type.' ORDER BY ivl.viewed_date DESC LIMIT 1) AND vl.entity_type = '.$entity_type, 'LEFT');
        $this->db->where('o.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is useed for subquery filter */
         //list view filter condition
         if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }

        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);

    }

    /**
     * Find all possible source options for opportunity based on `Lead_model`
     *
     * The usage of `Lead_model` is because of usage
     * in this method `Opportunity::get_create_opportunity_option()`
     *
     * @return mixed
     */
    public function determine_opportunity_source_options()
    {
        $this->load->model('Lead_model');
        return $this->Lead_model->get_lead_source();
    }

    /**
     * Find all possible opportunity status types
     * @return array
     */
    protected function determine_opportunity_status_options()
    {
        $q = $this->db
            ->from('tbl_opportunity_status AS o')
            ->where(['o.archive' => 0])
            ->select(['o.id AS value', 'o.name AS label'])
            ->get();

        $results = $q->result_array();
        return $results;
    }

    /**
     * Find all possible opportunity types
     * @return array
     */
    protected function determine_opportunity_types()
    {
        $q = $this->db
            ->from('tbl_references AS r')
            ->where(['type' => 17, 'r.archive' => 0])
            ->select('*')
            ->get();

        $results = $q->result_array();
        return $results;
    }


    public function get_opportunity_detail($reqData, $opp_data_only = false)
    {
        if (!empty($reqData)) {
            $id = $reqData->opportunity_id;
            $tbl_o = TBL_PREFIX . 'opportunity as o';

            $sql = "SELECT
            o.topic,
            o.opportunity_source,
            o.need_support_plan,
            o.opportunity_type,
            o.amount,
            o.line_item_sa_total,
            o.line_item_total,
            o.related_lead as lead_id,
            o.owner as owner_id,
            o.account_person as person_id,
            o.account_type,
            o.id as opportunity_id,
            o.opportunity_status,
            o.oppurtunity_description,
            sum(oi.amount) as item_amount
            FROM `tbl_opportunity` as o
            LEFT JOIN `tbl_opportunity_items` as oi on o.id = oi.opportunity_id and oi.archive = '0'
            WHERE o.archive = '0'
            AND o.id = '" . $id . "'
            GROUP BY o.id";
            $query = $this->db->query($sql);

            $opp_ary = $query->row_array();
            //if only opportunity data is required
            if (!empty($opp_data_only)) {
                return $opp_ary;
            }
            if (empty($opp_ary) || is_null($opp_ary)) {
                $return = array('status' => false);
                return $return;
            }

            $opp_ary['related_lead'] = [];
            if ($opp_ary['lead_id'] && $opp_ary['lead_id'] != null) {
                $this->db->select(["l.lead_number as label", 'l.id as value', 'l.lead_description']);
                $this->db->from(TBL_PREFIX . 'leads as l');
                $this->db->where(['l.archive' => 0, 'l.id' => $opp_ary['lead_id']]);
                $query = $this->db->get();
                $lead_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $opp_ary['related_lead'] = $lead_row;
            }

            $opp_ary['owner'] = [];
            if ($opp_ary['owner_id'] && $opp_ary['owner_id'] != null) {
                $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.id as value']);
                $this->db->from(TBL_PREFIX . 'member as m');
                $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
                $this->db->where(['m.archive' => 0, 'm.uuid' => $opp_ary['owner_id']]);
                $query = $this->db->get();
                $owner_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $opp_ary['owner'] = $owner_row;
            }

            $opp_ary['account_person'] = [];
            if ($opp_ary['person_id'] && $opp_ary['person_id'] != null) {

                if ($opp_ary['account_type'] == 1) {
                    $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'person as p');
                    $this->db->where(['p.archive' => 0, 'p.id' => $opp_ary['person_id']]);
                }

                if ($opp_ary['account_type'] == 2) {
                    $this->db->select(["org.name as label", 'org.id as value', "'2' as account_type"]);
                    $this->db->from(TBL_PREFIX . 'organisation as org');
                    $this->db->where(['org.archive' => 0, 'org.id' => $opp_ary['person_id']]);
                }

                $query = $this->db->get();
                $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                $opp_ary['account_person'] = $person_row;
            }

            // If amount is 0.00, use blank value so we
            // dont have to be force to fill up the amount in client side
            $opportunityAmount = (double) ($opp_ary['amount'] ?? 0);
            if ($opportunityAmount == 0) {
                $opp_ary['amount'] = '';
            }

            $return = array('status' => true, 'data' => $opp_ary);

            return $return;
        }
    }

    public function view_opportunity($reqData)
    {
        if (!empty($reqData)) {
            $id = $reqData->opportunity_id;
            $this->db->select("case when o.opportunity_type!=0 THEN
            (SELECT o_t.display_name as opp_type_label FROM tbl_references as o_t where o_t.id=o.opportunity_type AND o_t.archive=0 AND o_t.type=17) ELSE '' END as opp_type_label", false);

            $this->db->select("(select os.key_name from tbl_opportunity_status as os where os.id = o.opportunity_status) as opportunity_status_key_name");

            $this->db->from(TBL_PREFIX . 'opportunity as o');
            $this->db->select(array("o.id","o.opportunity_number", 'o.topic', 'o.opportunity_source', 'o.need_support_plan', 'o.opportunity_type', 'o.amount', 'o.related_lead as lead_id', 'o.owner as owner_id', 'o.account_person as person_id', 'o.account_type', 'o.id as opportunity_id', 'o.opportunity_status', 'o.oppurtunity_description', 'o.line_item_sa_total', 'o.line_item_total'));
            $this->db->where(array('o.archive' => 0, 'o.id' => $id));
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $oppr_ary = $query->row_array();

            if (empty($oppr_ary) || is_null($oppr_ary)) {
                $return = array('status' => false);
                return $return;
            } else {
                $oppr_ary['owner_label'] = $oppr_ary['account_label'] = '';
                $oppr_ary['page_title'] = $oppr_ary['topic'] . ' - ' . $oppr_ary['opp_type_label'];
                $oppr_ary['related_lead'] = [];
                if ($oppr_ary['lead_id'] && $oppr_ary['lead_id'] != null) {
                    $this->db->select(["l.lead_number as label", 'l.id as value', 'l.lead_description']);
                    $this->db->from(TBL_PREFIX . 'leads as l');
                    $this->db->where(['l.archive' => 0, 'l.id' => $oppr_ary['lead_id']]);
                    $query = $this->db->get();
                    $lead_row = $query->num_rows() > 0 ? $query->row_array() : [];
                    $oppr_ary['related_lead'] = $lead_row;
                }
                $oppr_ary['owner'] = [];
                if ($oppr_ary['owner_id'] && $oppr_ary['owner_id'] != null) {
                    $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.uuid as value']);
                    $this->db->from(TBL_PREFIX . 'member as m');
                    $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
                    $this->db->where(['m.archive' => 0, 'm.uuid' => $oppr_ary['owner_id']]);
                    $query = $this->db->get();
                    $owner_row = $query->num_rows() > 0 ? $query->row_array() : [];
                    $oppr_ary['owner'] = $owner_row;
                }
                $oppr_ary['account_person'] = [];
                if ($oppr_ary['person_id'] && $oppr_ary['person_id'] != null) {
                    if ($oppr_ary['account_type'] == 1) {
                        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type", "p.profile_pic as avatar"]);
                        $this->db->from(TBL_PREFIX . 'person as p');
                        $this->db->where(['p.archive' => 0, 'p.id' => $oppr_ary['person_id']]);
                        $query = $this->db->get();
                        $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                    } else if ($oppr_ary['account_type'] == 2) {
                        $this->db->select(["org.name as label", 'org.id as value', "'2' as account_type"]);
                        $this->db->from(TBL_PREFIX . 'organisation as org');
                        $this->db->where(['org.archive' => 0, 'org.id' => $oppr_ary['person_id']]);
                        $query = $this->db->get();
                        $person_row = $query->num_rows() > 0 ? $query->row_array() : [];
                    } else {
                        $person_row = [];
                    }
                    $oppr_ary['account_person'] = $person_row;
                }
                $oppr_ary['contacts'] = $this->get_opportunity_contacts($id);
                $oppr_ary['service_agreement'] = $this->get_opportunity_service_agreement($id);
            }
            $return = array('status' => true, 'data' => $oppr_ary);

            $return['data']['opportunity_type_options'] = $this->get_opportunity_type_option();
            $return['data']['opportunity_source_options'] = $this->determine_opportunity_source_options();
            $return['data']['opportunity_status_options'] = $this->determine_opportunity_status_options();
            $return['data']['opportunity_items'] = $items = $this->get_opportunity_items_detail($id); //new method to get all item details
            $total = 0;
            if (!empty($items)) {
                foreach ($items as $row) {
                    if ($row->category_ref === "" || !$this->isParentAdded($items, $row)) {
                        $amount = $row->amount;
                        if ($amount === "") {
                            $amount = 0;
                        }
                        $amount = $amount >=0 ? $amount : $row->qty * $row->upper_price_limit;
                        $total += $amount;
                    }
                }
            }

            $return['data']['opportunity_items_total'] = $total;

            $return['data']['cancel_lost_reason_option'] = $this->get_cancel_lost_reason_option();
            // $return['data']['opportunity_items'] = $items = $this->get_opportunity_items_detail($id); //new method to get all item details
            // $total = 0;
            // if(!empty($items))
            // $total =  array_sum(array_column($items, 'amount'));

            // $return['data']['opportunity_items_total'] = $total;

            $x = $this->Opportunity_model->get_opportunity_status_list($oppr_ary["opportunity_status"], $oppr_ary["opportunity_status_key_name"]);

            if ($oppr_ary["opportunity_status_key_name"] == "lost" || $oppr_ary["opportunity_status_key_name"] == "cancelled") {
                $return['data']['cancel_lost_reason_details'] = $this->get_lost_and_cancel_reason_of_nots($id);
            }

            $return['data'] = array_merge($return['data'], $x);

            return $return;
        }
    }



    function get_lost_and_cancel_reason_of_nots($opportunityId)
    {
        $res = $this->basic_model->get_row("opportunity_cancelled_and_lost_reason", ["id", "reason", "reason_note"], ["archive" => 0, "opportunity_id" => $opportunityId]);

        if (!empty($res)) {
            $this->db->select(["r.display_name"]);
            $this->db->from(TBL_PREFIX . 'references as r');
            $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'cancel_and_lost_reason_opp' AND rdt.archive = 0", "INNER");
            $this->db->where("r.id", $res->reason);
            $res->reason_label = $this->db->get()->row("display_name");
        }

        return $res;
    }

    function get_cancel_lost_reason_option()
    {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'cancel_and_lost_reason_opp' AND rdt.archive = 0", "INNER");

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_opportunity_contacts($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("false as contact_editable", false);
        $this->db->select("false as role_editable", false);
        $this->db->select("false as primary_editable", false);

        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('oc.roll_id', 'oc.is_primary', '(select email from tbl_person_email where primary_email = 1 and archive = 0 and person_id=p.id) as person_email', '(select phone from tbl_person_phone where primary_phone = 1 and archive = 0 and person_id=p.id) as person_phone', 'p.id as person_id', 'oc.id as sales_id', "p.id as contactId"));

        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $oppr_ary = $query->result();

        if (!empty($oppr_ary)) {
            foreach ($oppr_ary as $value) {
                $value->contact_key_val = ["label" => $value->name, 'value' => $value->person_id];
                #$value->role_key_val = ["label"=>$value->role, 'value'=>$value->roll_id];
            }
            obj_to_arr($oppr_ary);
        }
        return $oppr_ary;
    }

    public function get_contact_list_for_opportunity($name = '',$limit,$allow_new_contact = TRUE)
    {
        $new_contact = array("label"=>"New Contact", "value"=>"new contact");
        $this->db->like('label', $name);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value', "'1' as account_type"]);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0]);
        $this->db->having($queryHaving);
        if(!empty($limit)){
               $this->db->limit($limit);
           }
        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);
        $contact_result = $query->num_rows() > 0 ? $query->result_array() : [];
        // to add the new contact data
        if ($allow_new_contact == true) {
            $contact_result[] = array_push($contact_result, $new_contact);
        }
        return $contact_result;        
    }

    public function get_roles_for_opportunity()
    {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->where(['r.archive' => 0, 'r.type' => 6]);
        $query = $this->db->get();
        $results = $query->num_rows() > 0 ? $query->result_array() : [];

        foreach ($results as $i => $result) {
            if (strtolower($result['label']) === 'decision maker') {
                $results[$i]['max_num_contacts_with_this_role'] = 1;
            }
        }
        return $results;
    }

    public function update_opportunity_contact_role($reqData)
    {
        $data = $reqData->data;
        $userId = $reqData->adminId;

        if (!empty($data)) {
            $temp = [];
            $updated_data = [];
            $inserted_data = [];
            foreach ($data as $val) {
                if ($val->sales_id ?? null) {
                    $contactId = $val->contact_key_val->value ?? 0;

                    $temp = [];
                    $temp['id'] = $val->sales_id;
                    $temp['roll_id'] = $val->roll_id;
                    $temp['is_primary'] = ($val->is_primary) ?? 0;
                    $temp['destination_data_id'] = $contactId;
                    $temp['updated_by'] = $userId;
                    $temp['updated'] = DATE_TIME;
                    $updated_data[] = $temp;

                    // find existing contactId
                    $prevId = $this->db->select('destination_data_id')->from('tbl_sales_relation')->where('id', $val->sales_id)->get()->row()->destination_data_id;

                    if (isset($prevId))
                        $item = $this->db->get_where('tbl_sales_relation', ['source_data_type' => 1, 'source_data_id' => $prevId, 'destination_data_type' => 3, 'destination_data_id' => $val->source_data_id])->row();

                    if (isset($item))
                        $updated_data[] = ['id' => $item->id, 'roll_id' => $item->roll_id, 'is_primary' => $item->is_primary, 'source_data_id' => $contactId];
                } else {
                    $temp = [];
                    $DESTINATION_DATA_TYPE_CONTACT = 1;
                    $SOURCE_DATA_TYPE_OPPORTUNITY = 3;

                    $temp['source_data_id'] = $val->source_data_id; // usually the opportunity id
                    $temp['source_data_type'] = $SOURCE_DATA_TYPE_OPPORTUNITY;

                    $temp['roll_id'] = $val->roll_id;
                    $temp['is_primary'] = ($val->is_primary) ?? 0;

                    $temp['destination_data_id'] = ($val->contact_key_val->value) ?? 0;
                    $temp['destination_data_type'] =  $DESTINATION_DATA_TYPE_CONTACT;

                    $temp['created_by'] = $userId;
                    $inserted_data[] = $temp;

                    $this->basic_model->insert_records('sales_relation', ['source_data_type' => 1, 'source_data_id' => $temp['destination_data_id'], 'destination_data_type' => 3, 'destination_data_id' => $temp['source_data_id']]); // add the reverse mapping
                }
            }

            if (!empty($updated_data)) {
                $this->basic_model->insert_update_batch($action = 'update', $table_name = 'sales_relation', $updated_data, 'id');
            }

            if (!empty($inserted_data)) {
                $this->basic_model->insert_update_batch('insert', 'sales_relation', $inserted_data);
            }

            return true;
        }
    }

    public function save_service_agreement($data, $adminId)
    {
        $data['additional_services_custom'] = trim($data['additional_services_custom']);

        if (!empty($data['additional_services_custom']) && !in_array(5, $data['additional_services'])) {
            echo json_encode('err');
            die();
        }
        if (!empty($data['agreement_id'])) {
            $formerState = $this->db->from(TBL_PREFIX . 'service_agreement')
                ->select('*')
                ->where(['id' => $data['agreement_id']])
                ->get()->result();
            $formerStateGoals = $this->db->from(TBL_PREFIX . 'service_agreement_goal')
                ->select('id, goal, outcome')
                ->where(['service_agreement_id' => $data['agreement_id'], 'archive' => 0])
                ->get()->result();
        }
        $insData = [
            'opportunity_id' => $data["opp_id"] ?? 0,
            'status' => $data['status'] ?? 0,
            'owner' => $data['owner'] ?? 0,
            'account' => $data['account'],
            'account_type' => $data['account_type'],
            'grand_total' => $data['grand_total'] ?? 0,
            'sub_total' => $data['sub_total'] ?? 0,
            'tax' => $data['tax'] ?? 0,
            'additional_services' => json_encode($data['additional_services']),
            'additional_services_custom' => $data['additional_services_custom'],
            'customer_signed_date' => $data['customer_signed_date'] ?? '',
            'contract_start_date' => $data['contract_start_date'] ?? '',
            'contract_end_date' => $data['contract_end_date'] ?? '',
            'plan_start_date' => $data['plan_start_date'] ?? '',
            'plan_end_date' => $data['plan_end_date'] ?? '',
            'signed_by' => $data['signed_by'] ?? 0,
            'created_by' => $adminId,
        ];
        $participant_id = NULL;
        if($data['account_type']==1 && !empty($data['account'])){
            $participant_data = $this->basic_model->get_row("participants_master", ["id"], ["archive" => 0, "contact_id" => $data['account']]);
            if(!empty($participant_data)){
                $participant_id = $participant_data->id;
                $insData['participant_id'] =  $participant_id;
            }
        }

        if (isset($data['agreement_id']) && $data['agreement_id'] > 0) {
            $insData['updated_by'] = $adminId;
            $this->basic_model->update_records('service_agreement', $insData, array('id' => $data['agreement_id']));
            $agreement_id = $data['agreement_id'];

            // update goals
            $goals = obj_to_arr($data['goals']);
            $this->save_goals($agreement_id, $goals, ['adminId' => $adminId]);
        } else {
            $insData['created_by'] = $adminId;
            $agreement_id = $this->basic_model->insert_records('service_agreement', $insData);
            if ($agreement_id) {
                $goal_ary = $temp = [];
                foreach ($data['goals'] as $value) {
                    $temp = ['goal' => $value->goal, 'outcome' => $value->outcome, 'created_by' => $adminId, 'service_agreement_id' => $agreement_id];
                    $goal_ary[] = $temp;
                }
                if (!empty($goal_ary))
                    $this->basic_model->insert_records('service_agreement_goal', $goal_ary, true);
            }
        }

        $bSuccess = $this->db->insert(
            TBL_PREFIX . 'service_agreement_history',
            [
                'service_agreement_id' => $agreement_id,
                'created_by' => $adminId,
                'created_at' => date('Y-m-d H:i:s')
            ]
        );

        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());

        $history_id = $this->db->insert_id();
        if (empty($data['agreement_id'])) {
            $this->create_agreement_field_history_entry($history_id, $agreement_id, 'created', date('Y-m-d H:i:s'), '');
        }
        if (!empty($agreement_id) && !empty($formerState) && !empty($formerStateGoals)) {

            $data['agreement_id'] = $agreement_id;
            $this->trackAgreementGoalsChanges($data, $formerStateGoals, $history_id);
            if (!empty($formerState)) {
                $formerState = $formerState[0];

                if (isset($data["status"]) && $data["status"] != $formerState->status) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'status', $data['status'], $formerState->status);
                }

                if (isset($data["owner"]) && $data["owner"] != $formerState->owner)
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'owner', $data['owner'], $formerState->owner);

                if (isset($data["grand_total"]) && $data["grand_total"] != $formerState->grand_total)
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'grand_total', $data['grand_total'], $formerState->grand_total);

                if (isset($data["sub_total"]) && $data["sub_total"] != $formerState->sub_total)
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'sub_total', $data['sub_total'], $formerState->sub_total);

                if (isset($data["tax"]) && (double)$data["tax"] !== (double)$formerState->tax) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'tax', $data['tax'], $formerState->tax);
                }
                if (isset($data["contract_start_date"]) && $data["contract_start_date"] !== $formerState->contract_start_date) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'contract_start_date', $data['contract_start_date'], $formerState->contract_start_date);
                }

                if (isset($data["contract_end_date"]) && $data["contract_end_date"] !== $formerState->contract_end_date) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'contract_end_date', $data['contract_end_date'], $formerState->contract_end_date);
                }
                if (isset($data["plan_start_date"]) && $data["plan_start_date"] !== $formerState->plan_start_date) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'plan_start_date', $data['plan_start_date'], $formerState->plan_start_date);
                }
                if (isset($data["plan_end_date"]) && $data["plan_end_date"] !== $formerState->plan_end_date) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'plan_end_date', $data['plan_end_date'], $formerState->plan_end_date);
                }
                 if (empty($data["customer_signed_date"])) {
                    $data["customer_signed_date"] = '0000-00-00 00:00:00';
                }
                if ($data["customer_signed_date"] !== $formerState->customer_signed_date) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'customer_signed_date', $data['customer_signed_date'], $formerState->customer_signed_date);
                }
                if ((isset($data["additional_services"]) && json_encode($data["additional_services"]) !== $formerState->additional_services) || isset($data["additional_services_custom"]) && $data["additional_services_custom"] !== $formerState->additional_services_custom) {
                    if (!empty($data['additional_services_custom'])) {
                        $data['additional_services'][] = $data['additional_services_custom'];
                    }
                    $formerStateServices = json_decode($formerState->additional_services);
                    if (!empty($formerState->additional_services_custom)) {
                        $formerStateServices[] = $formerState->additional_services_custom;
                    }
                    $formerStateServicesJson = json_encode($formerStateServices);
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'additional_services', json_encode($data['additional_services']), $formerStateServicesJson);
                }
                if (isset($data["signed_by"]) && $data["signed_by"] !== $formerState->signed_by) {
                    $this->create_agreement_field_history_entry($history_id, $agreement_id, 'signed_by', $data['signed_by'], $formerState->signed_by);
                }
            }
        }

        return $agreement_id;
    }

    /**
     * Update list of goals. If a goal has blank ID, it will be inserted, otherwise update existing.
     * If goal was not updated, it will be marked not archived.
     *
     * @param int $service_agreement_id
     * @param array[] $goals
     * @param array $options Will use `adminId` created_by/updated_by
     * @return int[] Ids of inserted or updated goals
     */
    public function save_goals($service_agreement_id, array $goals, array $options = [])
    {
        $existingGoals = $this->db->get_where('tbl_service_agreement_goal', [
            'service_agreement_id' => $service_agreement_id,
            'archive' => 0,
        ])->result_array();

        $goalIds = [];

        $existingGoalsById = [];
        foreach ($existingGoals as $existingGoal) {
            $existingGoalsById[$existingGoal['id']] = $existingGoal;
        }

        foreach ($goals as $goal) {
            $goal_id = $goal['id'] ?? null;
            if ($goal_id && array_key_exists($goal_id, $existingGoalsById)) {
                $updatedGoal = array_merge($existingGoalsById[$goal_id], [
                    'goal' => $goal['goal'],
                    'outcome' => $goal['outcome'],
                    'updated_by' => $options['adminId'] ?? 0,
                    'updated' => DATE_TIME
                ]);

                $isSuccess = $this->db->update('tbl_service_agreement_goal', $updatedGoal, ['id' => $updatedGoal['id']]);
                if ($isSuccess) {
                    $goalIds[] = $goal_id;
                    unset($existingGoalsById[$goal_id]);
                }
            } else {
                $newGoal = [
                    'service_agreement_id' => $service_agreement_id,
                    'goal' => $goal['goal'],
                    'outcome' => $goal['outcome'],
                    'created' => DATE_TIME,
                    'created_by' => $options['adminId'] ?? 0,
                ];

                $this->db->insert('tbl_service_agreement_goal', $newGoal);
                $goalIds[] = $this->db->insert_id();
            }
        }

        if (!empty($existingGoalsById)) {
            foreach ($existingGoalsById as $id => $goalById) {
                $this->db->update('tbl_service_agreement_goal', ['archive' => 1], ['id' => $id]);
            }
            $existingGoalsById = [];

            // // if you need to destroy records, use the commented 2 lines below
            // $goalIdsToBeDeleted = array_keys($existingGoalsById);
            // $this->db->where_in('id', $goalIdsToBeDeleted)->delete('tbl_service_agreement_goal');
        }

        $goalIds = array_map('intval', $goalIds);
        return $goalIds;
    }

    public function get_opportunity_service_agreement($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'service_agreement as sa');
        $this->db->select(array('sa.id'));
        $this->db->where(array('sa.archive' => 0, 'sa.opportunity_id' => $opportunity_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $oppr_agree_ary = $query->result();
        return $oppr_agree_ary;
    }

    public function get_opportunity_items($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'opportunity_items as oi');
        $this->db->select(array('oi.id as incr_id_opportunity_items', 'oi.line_item_id', 'oi.amount', 'oi.qty'));
        $this->db->where(array('oi.archive' => 0, 'oi.opportunity_id' => $opportunity_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $item_ary = $query->result();
        return $item_ary;
    }

    function get_opportunity_status_list($statusId, $key_name)
    {
        $this->db->select(["id", "name", "key_name", "its_final_stage"]);
        $this->db->from("tbl_opportunity_status");
        $this->db->where("archive", 0);
        $this->db->order_by("order_status", "asc");

        $res = $this->db->get()->result();

        $return = ["final_opportunity_status_list" => [], "opportunity_status_list" => []];

        $last_stage = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                if ($val->its_final_stage == 1) {
                    if ($key_name == $val->key_name) {
                        $last_stage = $val;
                    }
                    $return["final_opportunity_status_list"][] = $val;
                } else {
                    $return["opportunity_status_list"][] = $val;
                }
            }
        }
        if (!empty($last_stage)) {
            $return["opportunity_status_list"][] = $last_stage;
        } else {
            $return["opportunity_status_list"][] = ["name" => "Closed", "key_name" => 'closed'];
        }

        return $return;
    }

    function check_opporuntiy_status_going_to_back_then_check_admin_permission($reqData, $adminId)
    {
        $opportunity = $this->basic_model->get_row("opportunity", ["opportunity_status"], ["id" => $reqData->opportunity_id]);
        $current_opportunity_status = $opportunity->opportunity_status ?? 0;

        $x = $this->basic_model->get_row("opportunity_status", ["order_status"], ["id" => $current_opportunity_status]);

        $current_order = $x->order_status ?? 0;

        $y = $this->basic_model->get_row("opportunity_status", ["order_status"], ["id" => $reqData->status]);
        $update_order = $y->order_status ?? 0;


        // then we have to check admin permission
        // its should be crm admin
        //
        // access_crm_admin
        if ($current_order > $update_order) {
            require_once APPPATH . 'Classes/admin/permission.php';

            $obj_permission = new classPermission\Permission();
            $result = $obj_permission->check_permission($adminId, "access_crm_admin");

            if (!$result) {
                return array('status' => false, 'error' => "Not have permission unset status of opportunity");
            }
        }

        return array('status' => true);
    }

    function get_opportunity_type_key_name_by_opportunity_id($opportunityId)
    {
        $this->db->select(["ot.key_name"]);
        $this->db->from("tbl_opportunity as o");
        $this->db->join("tbl_references as ot", "ot.id = o.opportunity_type", "INNER");
        $this->db->where("o.id", $opportunityId);
        $this->db->where(array("ot.type" => 17, "ot.archive" => 0));

        return $this->db->get()->row("key_name");
    }

    function update_status_opportunity($reqData, $adminId)
    {
        $reqData = (object) $reqData;

        $check_res = $this->check_opporuntiy_status_going_to_back_then_check_admin_permission($reqData, $adminId);

        if ($check_res["status"]) {
            $res = $this->basic_model->get_row("opportunity_status", ["key_name"], ["id" => $reqData->status]);
            $key_name = $res->key_name ?? '';

            ## curretly comment this condtion we will ad latter ##
            /* $opportunity_type_key_name = $this->get_opportunity_type_key_name_by_opportunity_id($reqData->opportunity_id);

            // when key_name = discovery then check all service agreement should be active
            if ($key_name == "discovery" && $opportunity_type_key_name == "ndis") {
                $service_agreement = $this->basic_model->get_record_where("service_agreement", ["id", "status", "service_agreement_id"], ["opportunity_id" => $reqData->opportunity_id, "archive" => 0]);

                $not_acive_yet = [];
                if (!empty($service_agreement)) {
                    foreach ($service_agreement as $val) {
                        if ($val->status != 5) {
                            $not_acive_yet[] = $val->service_agreement_id;
                        }
                    }

                    return ["status" => false, "error" => implode(", ", $not_acive_yet). " need to be active for mark status as completed"];
                }
            } */

            if ($key_name == "lost" || $key_name == "cancelled") {
                $reson_data = [
                    "opportunity_id" => $reqData->opportunity_id,
                    "reason" => $reqData->reason_drop ?? "",
                    "reason_note" => $reqData->reason_note ?? "",
                ];

                $this->basic_model->update_records("opportunity_cancelled_and_lost_reason", ["archive" => 1], ["opportunity_id" => $reqData->opportunity_id]);
                $this->basic_model->insert_records("opportunity_cancelled_and_lost_reason", $reson_data, false);
            }

            $where = ["id" => $reqData->opportunity_id];
            $data = ["opportunity_status" => $reqData->status, "updated" => DATE_TIME];

            $this->basic_model->update_records("opportunity", $data, $where);

            $succes_msg = 'Status updated successfully.';

            $response = ['status' => true, 'msg' => 'Status updated successfully.'];
        } else {
            $response = $check_res;
        }

        return $response;
    }

    public function get_opportunity_items_detail($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'opportunity_items as oi');
        $this->db->select(array('oi.id as incr_id_opportunity_items', 'oi.line_item_id', 'oi.amount', 'oi.qty', 'fli.line_item_name', 'fli.line_item_number', 'fli.category_ref', 'fsc.name as support_cat', 'oi.price  AS rate', 'oi.line_item_price_id'));
        $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
        $this->db->join("tbl_finance_line_item as fli", "fli.id = oi.line_item_id", "inner");
        $this->db->join("tbl_finance_support_category as fsc", "fsc.id = fli.support_category AND fsc.archive=0", "inner");
        $this->db->where(array('oi.archive' => 0, 'oi.opportunity_id' => $opportunity_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $item_ary = $query->result();

        $sorted = [];
        $parentItem = [];
        $childItem = [];
        foreach ($item_ary as $li) {
            if (empty($li->category_ref)) { // is a cat
                array_push($parentItem, $li);
            } else {
                $childItem[$li->category_ref][] = $li;
            }
        }

        foreach ($parentItem as $item) {
            array_push($sorted, $item);
            if (isset($childItem[$item->line_item_number])) { // is a cat
                $temp_child = [];
                $temp_child = $childItem[$item->line_item_number];
                $sorted = array_merge($sorted, $temp_child);
                unset($childItem[$item->line_item_number]);
            }
        }
        
        if (!empty($childItem)) {
            foreach ($childItem as $item) {
                $sorted = array_merge($sorted, $item);
            }
        }
        return $sorted;
    }

    public function get_opportunity_service_agreements_summary($opportunity_id)
    {
        return $this->db->select([
            'sa.id',
            "(
                CASE
                    WHEN sa.status = 0 THEN 'Draft'
                    WHEN sa.status = 1 THEN 'Issued'
                    WHEN sa.status = 2 THEN 'Approved'
                    WHEN sa.status = 3 THEN 'Accepted'
                    WHEN sa.status = 4 THEN 'Declined'
                    WHEN sa.status = 5 THEN 'Active'
                END
            ) as status_label",
            'sa.contract_start_date',
            'topic'
        ])
            ->from('tbl_service_agreement as sa')
            ->join('tbl_opportunity', 'tbl_opportunity.id = sa.opportunity_id')
            ->where(['opportunity_id' => $opportunity_id, 'sa.archive' => 0])
            ->get()->result();
    }

    function get_oppunty_contacts($opportunity_id)
    {
        $this->db->from(TBL_PREFIX . 'sales_relation as oc');
        $this->db->select("case when oc.roll_id!=0 THEN
        (SELECT display_name as role FROM tbl_references as r where r.id=oc.roll_id AND r.archive=0 AND r.type=6) ELSE '' END as role", false);
        $this->db->select("CONCAT(p.firstname,' ',p.lastname) AS name", FALSE);
        $this->db->select(array('p.id as person_id'));
        $this->db->join("tbl_person as p", "p.id = oc.destination_data_id AND p.archive = 0", "inner");
        $this->db->where(array('oc.archive' => 0, 'oc.source_data_id' => $opportunity_id, 'oc.source_data_type' => 3, 'oc.destination_data_type' => 1));
        $query = $this->db->get()->result();
        $rows = array();
        if (!empty($query)) {
            foreach ($query as $val) {
                $rows[] = array('label' => $val->name, 'value' => $val->person_id);
            }
        }
        return $rows;
    }
    function delete_oppurunity_contacts($data)
    {
        if ($data && $data['sales_id']) {
            $prevId = $this->db->select('destination_data_id')->from('tbl_sales_relation')->where('id',  $data['sales_id'])->get()->row()->destination_data_id;
            $item = $this->db->get_where('tbl_sales_relation', ['source_data_type' => 1, 'source_data_id' => $prevId, 'destination_data_type' => 3, 'destination_data_id' => $data['contact_id']])->row();
            $del_ids = array($data['sales_id'], $item->id);
            $this->db->where_in('id', $del_ids);
            if ($this->db->delete('tbl_sales_relation')) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Insert service agreement history record for a particular field
     * @param int $history_id ID of current history record from table tbl_service_agreement_history
     * @param int $agreement_id ID of agreement
     * @param string $field Name of field being changed
     * @param mixed $val Value of the field
     * @param mixed $prev_val Previous value of the field
     * @return void
     */
    public function create_agreement_field_history_entry($history_id, $agreement_id, $field, $val, $prev_val)
    {

        $bSuccess = $this->db->insert(TBL_PREFIX . 'service_agreement_field_history', [
            'history_id' => $history_id,
            'service_agreement_id ' => $agreement_id,
            'field' => $field,
            'prev_val' => $prev_val,
            'value' => $val
        ]);

        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());
    }
    /**
     * Check and insert agreement goals changes
     */
    public function trackAgreementGoalsChanges($data, $formerStateGoals, $history_id)
    {
        $dataGoals = $data['goals'];
        $is_changed = false;
        if (count($dataGoals) != count($formerStateGoals)) {
            $is_changed = true;
        }
        if (!$is_changed && !empty($formerStateGoals)) {
            foreach ($dataGoals as $goal) {
                foreach ($formerStateGoals as $fgoal) {
                    if (!empty($goal->id) && $goal->id == $fgoal->id && $goal != $fgoal) {
                        $is_changed = true;
                        break;
                    }
                }
            }
        }
        if ($is_changed) {
            $this->create_agreement_field_history_entry($history_id, $data['agreement_id'], 'goals', json_encode($dataGoals), json_encode($formerStateGoals));
        }
    }

    /**
     * Create history item for each change field
     * @param array $existingItems Existing data
     * @param array $updatedItems updated data
     * @return void
     */
    public function updateHistory($oldItems, $newItems, $opportunityId, $adminId) {
        $existingItems = [];
        $updatedItems = []; //print_r($newItems); die;
        unset($newItems['opportunity_id']);
        if (!empty($oldItems) || !empty($newItems)) {
            foreach($oldItems as $item) {
                $itemObj = new stdClass();
                $itemObj->id = $item->incr_id_opportunity_items;
                $itemObj->qty = $item->qty;
                $itemObj->amount = $item->amount;
                $itemObj->selected = true;
                $existingItems[$item->incr_id_opportunity_items] = $itemObj;
            }
            foreach($newItems as $item) {
                $itemObj = new stdClass();
                $itemObj->id = $item['incr_id_opportunity_items'];
                $itemObj->qty = $item['qty'];
                $itemObj->amount = $item['amount'];
                $itemObj->selected = $item['selected'];
                $updatedItems[$item['id']] = $itemObj;
            }
            //create history record for each field
            foreach($updatedItems as $updatedItem) {
                if (empty($updatedItem->id)) {
                    continue;
                }
                $existing_item = '';
                $history_id = 0;
                //check if item is unchanged
                if (array_key_exists($updatedItem->id, $existingItems) && $existingItems[$updatedItem->id] == $updatedItem) {
                    continue;
                }
                if (array_key_exists($updatedItem->id, $existingItems)) {
                    $existing_item = $existingItems[$updatedItem->id];
                }
                $history = $this->db->insert(
                    TBL_PREFIX . 'opportunity_item_history',
                    [
                        'opportunity_id' => $opportunityId,
                        'opportunity_item_id' => $updatedItem->id,
                        'created_by' => $adminId,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                );
                if ($history) {
                    $history_id = $this->db->insert_id();
                }
                if (!empty($existing_item)) {
                    if($updatedItem->qty != $existing_item->qty) {
                        $this->create_field_history($history_id, $updatedItem->id, 'quantity', $updatedItem->qty, $existing_item->qty);
                    }
                    if($updatedItem->amount != $existing_item->amount) {
                        $this->create_field_history($history_id, $updatedItem->id, 'amount', $updatedItem->amount, $existing_item->amount);
                    }
                    if($updatedItem->selected != $existing_item->selected) {
                        $this->create_field_history($history_id, $updatedItem->id, 'archive', date('Y-m-d H:i:s'), '');
                    }
                } else {
                    $this->create_field_history($history_id, $updatedItem->id, 'created', date('Y-m-d H:i:s'), '');
                }
            }
        }
        return true;
    }

    /**
     * Create history record to be used for all history items in the update
     * @param int $historyId Id of related update history
     * @param int $opportunity_item_id
     * @param string $fieldName
     * @param string $new_value
     * @param string $oldValue
     * @return int Last insert id
     */
    public function create_field_history($historyId, $opportunityItemId, $fieldName, $newValue, $oldValue) {
        return $this->db->insert(TBL_PREFIX . 'opportunity_item_field_history', [
            'history_id' => $historyId,
            'opportunity_item_id' => $opportunityItemId,
            'field' => $fieldName,
            'prev_val' => $oldValue,
            'value' => $newValue
        ]);
    }

    public function get_staff_safety_checklist($reqData) {
        $opportunity_id = $reqData->data->opportunity_id?? 0;
        $contact_id = $reqData->data->contact_id?? 0;       
        $participant_id = $reqData->data->participant_id?? 0;  

        if (!empty($contact_id)) {
            // get all opportunities by contact
            $this->load->model("sales/Contact_model");
            $req2 = ["source_data_type" => 1, "destination_data_type" => 3, "source_data_id" => $contact_id];
            $opportunities = $this->Contact_model->get_sales_relation_linked_items($req2);
            //$opportunity_ids = array_column($opportunities, 'id');
            //pr($opportunities);
            //$this->db->where_in('opportunity_id', $opportunity_ids);
        } elseif(!empty($participant_id)) {
            $this->db->where(array('participant_id' => $participant_id));
        } else {           
            $this->db->where(array('opportunity_id' => $opportunity_id));
        }
        $this->db->select("*");
        $this->db->from(TBL_PREFIX . 'opportunity_staff_saftey_checklist');
        if (!empty($opportunity_id) || !empty($participant_id)) {
            $row = $this->db->get()->row();
            if (!empty($row)) {
                $this->db->select("tbl_member.id, CONCAT(tbl_member.firstname,' ',tbl_member.middlename, ' ', tbl_member.lastname) AS full_name");
                $this->db->from('tbl_member');
                $this->db->where_in('tbl_member.id', [$row->created_by, $row->created_by]);
                $user_rows = $this->db->get()->result();
                if (!empty($user_rows)) {
                    foreach($user_rows as $user_row) {
                        if ($user_row->id == $row->created_by) {
                            $row->created_by_name = $user_row->full_name;
                        }
                        if ($user_row->id == $row->updated_by) {
                            $row->updated_by_name = $user_row->full_name;
                        }
                    }
                }
            }
            return $row;
        } else if (!empty($contact_id)) {
            //$rows = $this->db->get()->result();
            $checklist_opportunities = [];
            foreach($opportunities as $opp) {
                $obj = new stdClass();
                $obj->label = $opp-> opportunity_number . '-' . $opp->topic;
                $obj->value = $opp->id;
                $checklist_opportunities[] = $obj;
            }
            return ['checklist_opportunities' => $checklist_opportunities];
        }
    }

    public function get_staff_safety_checklist_items($reqData) {
        $opportunity_id = $reqData->data->opportunity_id?? 0;     
        $participant_id = $reqData->data->participant_id?? 0;    
        $this->db->select("cc.category_name, ci.item_name, ci.id as item_id, oc.item_value, oc.item_details, oc.id as checklist_id, oc.updated_by, oc.updated_at, oc.participant_id");
        $this->db->from(TBL_PREFIX . 'staff_saftey_checklist_categories as cc');
        $this->db->join("tbl_staff_saftey_checklist_items as ci", "cc.id = ci.category_id AND ci.archive = 0", "left");
        if (!empty($opportunity_id)) {
            $this->db->join("tbl_opportunity_staff_saftey_checklist as oc", "ci.id = oc.item_id AND oc.archive = 0 AND oc.opportunity_id = $opportunity_id", "left");  
        } elseif (!empty($participant_id)) {
            $this->db->join("tbl_opportunity_staff_saftey_checklist as oc", "ci.id = oc.item_id AND oc.archive = 0 AND oc.participant_id = $participant_id", "left");
        }
        $where = array('cc.archive' => 0);
        $this->db->where($where);
        $this->db->order_by('cc.id asc, sort_order asc');
        $query = $this->db->get();
        //$s=$this->db->last_query();
        $rows = $query->result();
        //group items by category
        $data = array();
        $updated_by = 0;
        $updated_at = '';
        $participant_id = null;
        foreach($rows as $row) {
            $data['items'][$row->category_name][] = $row;
            $updated_by = $row->updated_by;
            $updated_at = $row->updated_at;
            $participant_id = $row->participant_id;
        }
        $data['participant_id'] = $participant_id;
        if (!empty($updated_by)) {
            $this->db->select("CONCAT(m.firstname,' ',m.middlename, ' ', m.lastname) AS updated_by_name", FALSE);
            $this->db->from('tbl_member as m');
            $this->db->where('m.id', $updated_by);
            $urow = $this->db->get()->row();
            $data['updated_by'] = $updated_by;
            $data['updated_by_name'] = str_replace('  ', ' ', $urow->updated_by_name);
            $data['updated_at'] = $updated_at;
        }
        return $data;
    }

    public function save_staff_safety_checklist_items($reqData) {
        $opportunity_id = $reqData->data->opportunity_id?? null;
        $participant_id = $reqData->data->participant_id?? null;
        $sql = '';
        if (!empty($opportunity_id)) {
            $sql = 'select participant_id from tbl_opportunity_staff_saftey_checklist where opportunity_id= '. $opportunity_id . ' LIMIT 1';
            $query = $this->db->query($sql);
            $result = $query->result();
            if (!empty($result)) {
                $participant_id = $result[0]->participant_id;
            }
        }
        if (!empty($participant_id)) {
            $sql = 'select participant_id from tbl_opportunity_staff_saftey_checklist where participant_id= '. $participant_id . ' LIMIT 1';
            $query = $this->db->query($sql);
            $result = $query->result();
            if (!empty($result)) {
                $opportunity_id = $result[0]->opportunity_id;
            }
        }
        $items = $reqData->data->items;
        $data = [];
        foreach($items as $cat_items) {
            foreach($cat_items as $item) {
                $obj = new stdClass();
                $obj->opportunity_id = $opportunity_id;
                $obj->participant_id = $participant_id;
                $obj->item_id = $item->item_id;
                $obj->item_value = $item->item_value;
                $obj->item_details = $item->item_details;
                $obj->created_by = $reqData->adminId;
                $obj->updated_by = $reqData->adminId;
                $obj->archive = 0;
                $obj->created_at = date("Y:m:d H:i:s");
                $obj->updated_at = date("Y:m:d H:i:s");
                $data[] = $obj;
            }            
        }
        $this->delete_records("opportunity_staff_saftey_checklist", ["opportunity_id" => $opportunity_id]);
        $this->insert_records("opportunity_staff_saftey_checklist", $data, true);
    }
    public function isParentAdded($items, $item) {
        if ($item->category_ref === "") {
            return false;
        }
        $parent_exists = false;
        foreach($items as $row) {
            if ($row->line_item_number === $item->category_ref) {
                $parent_exists = true;
                break;
            }
        }
        return $parent_exists;
    }

    /** Get Additional funding line item */
    public function get_line_items_additional_funding_detail($data) {

        $this->db->from(TBL_PREFIX . 'opportunity as opp');
        $this->db->select(array('opp.id as opportunity_id', 'af.additional_title','af.additional_price'));
        $this->db->join(TBL_PREFIX . "opportunity_additional_fund as af", "af.opportunity_id = opp.id", "inner");      
        $this->db->where(array('opp.archive' => 0, 'af.archive' => 0, 
            'opp.id' => $data->opportunity_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return array('data' => $query->result(), 'status' => true);
    }
    /** Get additional funding line item for service agreement */
    public function get_opportunity_additional_fund($opportunity_id) {
        $this->db->from(TBL_PREFIX . 'opportunity as opp');
        $this->db->select(array('opp.id as opportunity_id', 'af.*',));
        $this->db->join(TBL_PREFIX . "opportunity_additional_fund as af", "af.opportunity_id = opp.id", "inner");      
        $this->db->where(array('opp.archive' => 0, 'af.archive' => 0, 
            'opp.id' => $opportunity_id));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
 
        return $query->result();
    }
}
