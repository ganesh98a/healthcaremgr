<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Finance_quote_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_participant_organization_name($data) {
        $search = $data->search;

        $this->db->select(["concat_ws(' ',p.firstname,p.middlename,p.lastname) as label", "p.id as value", "'2' as type"]);
        $this->db->from('tbl_participant as p');
        $this->db->where('p.status', 1);
        $this->db->where('p.archive', 0);
        $this->db->like("concat_ws(' ',p.firstname,p.middlename,p.lastname)", $search);

        $query1 = $this->db->get_compiled_select();

        $this->db->select(["org.name as label", "org.id as value", "'4' as type"]);
        $this->db->from(TBL_PREFIX . 'organisation as org');
        $this->db->where('org.status', 1);
        $this->db->where('org.archive', 0);
        $this->db->like("org.name", $search);

        $query2 = $this->db->get_compiled_select();

        $query = $this->db->query($query1 . ' UNION ' . $query2);
        $res = $query->result_array();

        $res[] = ['label' => 'Create New Enquery', 'value' => 3, 'type' => 6];
        return $res;
    }

    function get_line_item_name($reqData) {
        if (!empty($reqData->previous_selected_item)) {
            $lineitemIds = array_column(obj_to_arr($reqData->previous_selected_item), 'line_ItemId');
            if (!empty($lineitemIds)) {
                $lineitemIds = array_column($lineitemIds, 'value');

                if (!empty($lineitemIds)) {
                    $this->db->where_not_in('id', $lineitemIds);
                }
            }
        }

        $this->db->select(["line_item_number as label", "id as value", "upper_price_limit", "national_price_limit", "national_very_price_limit"]);
        $this->db->from('tbl_finance_line_item');

        $current_date = date("Y-m-d");
        $this->db->where("'$current_date' between start_date and end_date", null, false);
        $this->db->where('funding_type', $reqData->funding_type);

        $this->db->group_start();
        $this->db->like("line_item_name", $reqData->search);
        $this->db->or_like("line_item_number", $reqData->search);
        $this->db->group_end();

        $query = $this->db->get();

        $res = $query->result_array();

        return $res;
    }

    function check_we_can_update_quote_or_create_new($reqData) {
        // return true if its change anything in  items
        // return false if not change anything in  items

        $previous_quote_item = $this->get_quote_items($reqData->quoteId);
        $previous_quote_item = obj_to_arr($previous_quote_item);

        $new_items = obj_to_arr($reqData->items);
        $new_manual_item = obj_to_arr($reqData->manual_item);

        if (!empty($previous_quote_item['items'])) {
            foreach ($previous_quote_item['items'] as $index => $val) {
                $item_exist = isset($new_items[$index]['line_ItemId']['value']) ? true : false;

                if ($item_exist && $val['line_ItemId']['value'] != $new_items[$index]['line_ItemId']['value']) {
                    return true;
                } elseif ($item_exist && $val['qty'] != $new_items[$index]['qty']) {
                    return true;
                } elseif ($item_exist && $val['price_type'] != $new_items[$index]['price_type']) {
                    return true;
                } elseif (!$item_exist) {
                    return true;
                } else {
                    unset($new_items[$index]);
                }
            }
        }
        if (!empty($new_items)) {
            return true;
        }



        if (!empty($previous_quote_item['manual_item'])) {
            foreach ($previous_quote_item['manual_item'] as $index => $val) {
                $item_exist = isset($new_manual_item[$index]['cost']) ? true : false;

                if ($item_exist && $val['cost'] != $new_manual_item[$index]['cost']) {
                    return true;
                } elseif (!$item_exist) {
                    return true;
                } else {
                    unset($new_manual_item[$index]);
                }
            }
        }
        if (!empty($new_items)) {
            return true;
        }

        return false;
    }

    function create_update_quote($reqData, $adminId) {
        $sub_total = 0;
        $still_create_new = false;
        if (!empty($reqData->quoteId)) {

            $still_create_new = $this->check_we_can_update_quote_or_create_new($reqData);
        }

        // check customer type and according to that create customer
        if ($reqData->user_type == 6) {

            // create new customer
            $userId = $this->create_new_enquiry_customer($reqData);
        } else {
            $userId = $reqData->userId;
        }

        // 1 - Sent, 5 - Draft
        $quote_data = array(
            'user_type' => $reqData->user_type,
            'userId' => $userId,
            'quote_date' => DateFormate($reqData->quote_date, "Y-m-d"),
            'valid_until' => DateFormate($reqData->valid_until, "Y-m-d"),
            'quote_note' => $reqData->quote_note,
            'status' => $reqData->save_type == 1 ? 1 : 5,
            'created' => DATE_TIME,
            'updated' => DATE_TIME,
            'created_by' => $adminId,
        );

        if (!$still_create_new && !empty($reqData->quoteId)) {
            // update quote
            $this->basic_model->update_records('finance_quote', $quote_data, ['id' => $reqData->quoteId]);
            $quoteId = $reqData->quoteId;
        } else {
            if ($still_create_new && !empty($reqData->quoteId)) {
                $this->basic_model->update_records('finance_quote', ['status' => 8], ['id' => $reqData->quoteId]);
            }

            // create quote
            $quoteId = $this->basic_model->insert_records('finance_quote', $quote_data, $multiple = FALSE);
        }


        $quate_item = [];
        // making a array of line item of quote and for line item set item_type = 1
        if (!empty($reqData->items)) {

            if ($still_create_new || empty($reqData->quoteId)) {
                foreach ($reqData->items as $x) {
                    $lineItemIds[] = $x->line_ItemId->value;
                }

                $lineItemPricing = $this->getLineItemCost($lineItemIds);


                foreach ($reqData->items as $val) {
                    $item_cost = (isset($lineItemPricing[$val->line_ItemId->value][$val->price_type]) ? $lineItemPricing[$val->line_ItemId->value][$val->price_type] : $lineItemPricing[$val->line_ItemId->value][1]);

                    $sub_total += ($item_cost * $val->qty);

                    $quate_item[] = array(
                        'quoteId' => $quoteId,
                        'item_type' => 1,
                        'itemId' => $val->line_ItemId->value,
                        'item_name' => '',
                        'description' => '',
                        'price_type' => $val->price_type,
                        'qty' => $val->qty,
                        'cost' => $item_cost,
                        'created' => DATE_TIME,
                        'archive' => 0
                    );
                }
            }
        }

        // check any manual item
        if (!empty($reqData->manual_item)) {
            foreach ($reqData->manual_item as $val) {
                if (!empty($val->item_name) && !empty($val->description) && !empty($val->cost)) {
                    $sub_total += $val->cost;
                    // create manual line item
                    $item = array(
                        'quoteId' => $quoteId,
                        'item_type' => 2,
                        'itemId' => 0,
                        'item_name' => $val->item_name,
                        'description' => $val->description,
                        'price_type' => '',
                        'qty' => 1,
                        'cost' => $val->cost,
                        'created' => DATE_TIME,
                        'archive' => 0
                    );

                    if (!empty($val->id) && !$still_create_new) {
                        $this->basic_model->update_records('finance_quote_item', $item, ['id' => $val->id]);
                    } else {
                        $quate_item[] = $item;
                    }
                }
            }
        }

        if ($still_create_new || empty($reqData->quoteId)) {
            $gst = calculate_gst($sub_total);
            $amm = array('sub_total' => $sub_total, 'gst' => $gst, 'total' => ($sub_total + $gst));
            $this->basic_model->update_records('finance_quote', $amm, ['id' => $quoteId]);
        }


        if (!empty($quate_item)) {
            $this->basic_model->insert_records('finance_quote_item', ($quate_item), true);
        }

        $this->create_quote_pdf($quoteId);
        // if draft not send pdf is status sent then send the pdf email
        if ($reqData->save_type == 1) {
            $this->resend_quote_mail((object)['quoteId'=>$quoteId]);
            // send pdf mail here
        }
        return true;
    }

    function getLineItemCost($lineItemIds) {
        $this->db->select(["id", "upper_price_limit", "national_price_limit", "national_very_price_limit"]);
        $this->db->from('tbl_finance_line_item');

        $current_date = date('Y-m-d');
        $this->db->where("'$current_date' between start_date and end_date", null, false);
        $this->db->where_in('id', $lineItemIds);

        $query = $this->db->get();
        $res = $query->result();

        $response = [];
        if ($res) {
            foreach ($res as $val) {

                $response[$val->id][1] = $val->upper_price_limit;
                $response[$val->id][2] = $val->national_price_limit;
                $response[$val->id][3] = $val->national_very_price_limit;
            }
        }

        return $response;
    }

    function create_new_enquiry_customer($reqData) {

        $customer_data = array(
            'name' => $reqData->name,
            'customerCategoryId' => $reqData->customerCategoryId,
            'contact_name' => $reqData->contact_name,
            'company_name' => (!empty($reqData->company_name)) ? $reqData->company_name : '',
            'email' => $reqData->email,
            'primary_phone' => $reqData->primary_phone,
            'seconday_phone' => (!empty($reqData->seconday_phone)) ? $reqData->seconday_phone : '',
            'street' => $reqData->street,
            'suburb' => $reqData->suburb,
            'state' => $reqData->state,
            'postcode' => $reqData->postcode,
            'created' => DATE_TIME,
            'archive' => 0,
        );

        if (!empty($reqData->quoteId) && !empty($reqData->userId)) {
            $this->basic_model->update_records('finance_quote_enquiry_customer', $customer_data, ['id' => $reqData->userId]);

            return $reqData->userId;
        } else {
            return $this->basic_model->insert_records('finance_quote_enquiry_customer', $customer_data, $multiple = false);
        }
    }

    function get_quote_listing($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array('quote_note', "quote_number", "amount", "funding_type", "quote_for", "created_by", "status_name");

        if (isset($filter->search) && $filter->search != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];

                if (!empty($filter->filter_by) && $filter->filter_by != 'all' && $filter->filter_by != $column_search) {
                    continue;
                }
                
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                } else if ($column_search != 'null') {
                    if ($column_search == "status_name") {
                        $this->db->or_like($column_search, $filter->search, "none");
                    }else{
                        $this->db->or_like($column_search, $filter->search);
                    }
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        if (!empty($queryHaving)) {

            $this->db->having($queryHaving);
        }
        $available_column = array('id', 'user_type', 'quote_date', 'status', 'quote_note', 'quote_number');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fq.id';
            $direction = 'DESC';
        }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fq.quote_date, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fq.quote_date, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fq.quote_date, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fq.quote_date, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }


        $select_column = array('fq.id', 'fq.user_type', 'fq.quote_date', 'fq.status', 'fq.quote_note', "fq.quote_number");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select('fq.total as amount', null, false);

        $this->db->select('(select group_concat(distinct ft.name SEPARATOR ", ") from tbl_finance_quote_item as fqi 
                            INNER join tbl_finance_line_item as fli ON fli.id = fqi.itemId AND fqi.item_type = 1 AND fqi.archive = 0 
                            INNER JOIN tbl_funding_type as ft ON ft.id = fli.funding_type 
                            where quoteId = fq.id AND fqi.archive = 0) as funding_type', null, false);

        $this->db->select("case
                 when fq.status = 1 THEN 'Sent'
                 when fq.status = 2 THEN 'Sent & Read'
                 when fq.status = 3 THEN 'Accepted'
                 when fq.status = 4 THEN 'Not Accepted'
                 when fq.status = 5 THEN 'Draft'
                 when fq.status = 6 THEN 'Error Sending'
                 ELSE 'Archived'
                 END as status_name", null, false);

        $this->db->select("(select REPLACE(concat(COALESCE(firstname,''),' ',COALESCE(middlename,' '),' ',COALESCE(lastname,' ')),'  ',' ') from tbl_member where id = fq.created_by) as created_by", null, false);
        $this->db->select("case
                 when fq.user_type = 2 THEN (select REPLACE(concat(COALESCE(firstname,''),' ',COALESCE(middlename,' '),' ',COALESCE(lastname,' ')),'  ',' ') from tbl_participant where id = fq.userId)
                 when fq.user_type = 4 THEN (select name from tbl_organisation where id = fq.userId)
                 when fq.user_type = 6 THEN (select name from tbl_finance_quote_enquiry_customer where id = fq.userId)
                 ELSE 'N/A'
                 END as quote_for", null, false);

        $this->db->select("case
                 when fq.user_type = 2 THEN (select CASE WHEN gender = 1 THEN 'male' WHEN gender = 2 THEN 'female' ELSE '' END from tbl_participant where id = fq.userId)
                
                 ELSE ''
                 END as booked_gender", null, false);


        $this->db->from('tbl_finance_quote as fq');


        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();


        if (!empty($result)) {
            foreach ($result as $val) {
                $val->funding_type = strpos($val->funding_type, ',') ? 'Multiple ' . $val->funding_type : $val->funding_type;
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

    function accept_reject_or_archive_quote($reqData, $adminId) {
        if ($reqData->action == 'reject') {
            $status = 4;
        } elseif ($reqData->action == 'accept') {
            $status = 3;
        } else {
            $status = 8;
        }

        $data = ['status' => $status, 'action_by' => $adminId, 'action_at' => DATE_TIME];
        $this->basic_model->update_records('finance_quote', $data, ['id' => $reqData->quoteId]);

        return true;
    }

    function check_quote_can_accept_or_reject_or_archive_or_resend_mail($quoteId, $action) {
        $where = ['id' => $quoteId];
        if ($action == 'archive') {
            $this->db->where_in('status', [1, 4, 5, 6]);
        } elseif ($action == 'resend_mail') {
            $this->db->where_in('status', [1, 3, 5, 6]);
        } else {
            $where['status'] = 1;
        }
        return $this->basic_model->get_row('finance_quote', ['status'], $where);
    }

    function create_finance_quote_pdf($quote_id) {

        $select_column = array('fq.id', 'fq.user_type', 'fq.userId', 'fq.quote_note', 'fq.quote_date','fq.quote_number'); //, 'fq.status','fq.quote_date', 'fq.status');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // $this->db->select('(select SUM(cost) from tbl_finance_quote_item where quoteId = fq.id AND archive = 0) as amount', null, false);
        $this->db->select("case
       when fq.user_type = 2 THEN (select concat_ws(' ',firstname,middlename,lastname) from tbl_participant where id = fq.userId limit 1)
       when fq.user_type = 4 THEN (select name from tbl_organisation where id = fq.userId limit 1)
       when fq.user_type = 6 THEN (select name from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_for", null, false);
        // Email
        $this->db->select("case
       when fq.user_type = 2 THEN (select email from tbl_participant_email where participantId = fq.userId and primary_email=1 limit 1)
       when fq.user_type = 4 THEN (select email from tbl_organisation_email where organisationId = fq.userId and primary_email=1 and archive=0 limit 1)
       when fq.user_type = 6 THEN (select email from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_email", null, false);

        // Contact
        $this->db->select("case
       when fq.user_type = 2 THEN (select phone from tbl_participant_phone where participantId = fq.userId and primary_phone=1 limit 1)
       when fq.user_type = 4 THEN (select phone from tbl_organisation_phone where organisationId = fq.userId and primary_phone=1 and archive=0 limit 1)
       when fq.user_type = 6 THEN (select primary_phone from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_phone", null, false);

        // Address
        $this->db->select("case
       when fq.user_type = 2 THEN (select concat_ws(' ',street,city,postal) from tbl_participant_address where participantId = fq.userId and primary_address=1 and archive=0 limit 1)
       when fq.user_type = 4 THEN (select concat_ws(' ',street,city,postal) from tbl_organisation_address where organisationId = fq.userId and primary_address=1 limit 1)
       when fq.user_type = 6 THEN (select concat_ws(' ',street,suburb,postcode)  from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_address", null, false);

        //1 - Line Item   = tbl_finance_line_item
        //2 - Manual item = tbl_finance_quote_manaual_item        
        $this->db->from('tbl_finance_quote as fq');
        $this->db->where('fq.id', $quote_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row_array();

        $line_items = $this->getLineItems($quote_id);
        $result['line_item'] = $line_items;

        return $result;
    }

    function save_quote_file($quote_data, $fileName) {

        $where = array('id' => $quote_data['id']);
        return $quoteId = $this->basic_model->update_records('finance_quote', ['pdf_file' => $fileName], $where);
    }

    function getLineItems($quote_id) {

        $select_column = array('fqi.id', 'fqi.qty'); //, 'fq.status','fq.quote_date', 'fq.status');
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("case
       when fqi.item_type = 1 THEN (select line_item_number from tbl_finance_line_item where id = fqi.itemId)       
       when fqi.item_type = 2 THEN fqi.item_name
       ELSE ''
       END as line_item_name", null, false);

        $this->db->select("case
       when fqi.item_type = 1 THEN (select line_item_name from tbl_finance_line_item where id = fqi.itemId)       
       when fqi.item_type = 2 THEN fqi.description
       ELSE ''
       END as line_item_desc", null, false);

        $this->db->select("case
       when fqi.item_type = 1 THEN (select upper_price_limit from tbl_finance_line_item where id = fqi.itemId)       
       when fqi.item_type = 2 THEN fqi.cost
       ELSE ''
       END as line_item_price", null, false);

        $this->db->join('tbl_finance_quote_item as fqi', 'fqi.quoteId = fq.id', 'inner');

        $this->db->from('tbl_finance_quote as fq');
        $this->db->where('fq.id', $quote_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $result = $query->result();
    }

    public function create_quote_pdf($quote_id) {

        $quote_data = $this->create_finance_quote_pdf($quote_id);

        $path = base_url('assets/img/ocs_logo.png');
        error_reporting(0);

        $data['logo_path'] = $path;
        $data['quote_data'] = $quote_data;

        $data['type'] = 'footer';
        $footerData = $this->load->view('create_quote_pdf', $data, true);

        $data['type'] = 'content';
        $file = $this->load->view('create_quote_pdf', $data, true);

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load(); //"'en-GB-x','A4-L','','',10,10,10,10,6,3,'L'");
        $pdf->AddPage('L');
        $pdf->WriteHTML($file);
        $pdf->setFooter($footerData); // $_SERVER['HTTP_HOST'] . '|{PAGENO}/{nbpg}|' . date('d-m-Y H:i:s')); // Add a footer       
        $rand = date('d_m_Y_hisa');
        $filename = 'QUOTE_' . $quote_id . '_' . $rand . '.pdf';
        $pdfFilePath = QUOTE_FILE_PATH . $filename;
        $pdf->Output($pdfFilePath, 'F');

        $quote_data = $this->save_quote_file($quote_data, $filename);
    }

    public function get_quote_pdf($quoteId) {
        $response = $this->get_quote_pdf_data($quoteId);
        if (!empty($response->pdf_file)) {
            $pdf_fileFCpath = FCPATH . QUOTE_FILE_PATH . $response->pdf_file;
            if (!file_exists($pdf_fileFCpath)) {
                $this->create_quote_pdf($quoteId);
                $response = $this->get_quote_pdf_data($quoteId);
            }
        } else {
            $this->create_quote_pdf($quoteId);
            $response = $this->get_quote_pdf_data($quoteId);
        }

        return $response;
    }

    private function get_quote_pdf_data($quoteId) {
        $where = ['id' => $quoteId];

        $select_column = array('fq.user_type', 'fq.userId', 'fq.status');
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("case
        when fq.user_type = 2 THEN (select concat_ws(' ',firstname,lastname) from tbl_participant where id = fq.userId limit 1)
        when fq.user_type = 4 THEN (select name from tbl_organisation where id = fq.userId limit 1)
        when fq.user_type = 6 THEN (select name from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
        ELSE ''
        END as quote_for", null, false);

        return $this->basic_model->get_row('finance_quote as fq', ['fq.pdf_file'], $where);
    }

    public function get_send_quote_email_details($quoteId) {
        $where = ['id' => $quoteId];

        $select_column = array('fq.user_type', 'fq.quote_number', 'fq.userId', 'fq.status', 'fq.valid_until'); //, 'fq.status','fq.quote_date', 'fq.status');

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        // $this->db->select('(select SUM(cost) from tbl_finance_quote_item where quoteId = fq.id AND archive = 0) as amount', null, false);
        $this->db->select("case
       when fq.user_type = 2 THEN (select concat_ws(' ',firstname,lastname) from tbl_participant where id = fq.userId limit 1)
       when fq.user_type = 4 THEN (select name from tbl_organisation where id = fq.userId limit 1)
       when fq.user_type = 6 THEN (select name from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_for", null, false);

        $this->db->select("case
       when fq.user_type = 2 THEN (select email from tbl_participant_email where participantId = fq.userId and primary_email=1 limit 1)
       when fq.user_type = 4 THEN (select email from tbl_organisation_email where organisationId = fq.userId and primary_email=1 and archive=0 limit 1)
       when fq.user_type = 6 THEN (select email from tbl_finance_quote_enquiry_customer where id = fq.userId limit 1)
       ELSE ''
       END as quote_email", null, false);

        return $this->basic_model->get_row('finance_quote as fq', ['fq.pdf_file'], $where);
    }

    function resend_quote_mail($quotedata) {
        $quoteId = $quotedata->quoteId;

        $status_arr = [5, 6];
        // 1 - Sent,2 - Sent & Read,3 - Accepted,4 - Not Accepted,5 - Draft,6 - Error Sending,8 - Archived	
        // here to get quote info
        $sendMailDetail = $this->get_send_quote_email_details($quoteId);
        //print_r($sendMailDetail);
        // die;
        $quoteStatus = $sendMailDetail->status;
        if (!empty($sendMailDetail->pdf_file)) {
            $pdf_fileFCpath = FCPATH . QUOTE_FILE_PATH . $sendMailDetail->pdf_file;
            if (file_exists($pdf_fileFCpath)) {
                $sendMailDetail->quotePdfPath = $pdf_fileFCpath;
                //$sendMailDetail->adminEmail='testteam.developer@gmail.com';                
                $mail_res = send_quote_email($sendMailDetail);
                if ($mail_res) {
                    if (in_array($quoteStatus, $status_arr)) {
                        $quoteStatus = 1;
                        $this->update_quote_status($quoteId, $quoteStatus);
                    }
                } else {
                    $quoteStatus = 6;
                    $this->update_quote_status($quoteId, $quoteStatus);
                }
            } else {
                // create quote pdf if not exist
                $this->create_quote_pdf($quoteId);
                $sendMailDetail = $this->get_send_quote_email_details($quoteId);
                if (!empty($sendMailDetail->pdf_file)) {
                    $pdf_fileFCpath = FCPATH . QUOTE_FILE_PATH . $sendMailDetail->pdf_file;
                    if (file_exists($pdf_fileFCpath)) {
                        $sendMailDetail->quotePdfPath = $pdf_fileFCpath;
                        $mail_res = send_quote_email($sendMailDetail);
                        if ($mail_res) {
                            if (in_array($quoteStatus, $status_arr)) {
                                $quoteStatus = 1;
                                $this->update_quote_status($quoteId, $quoteStatus);
                            }
                        } else {
                            $quoteStatus = 6;
                            $this->update_quote_status($quoteId, $quoteStatus);
                        }
                    }
                }
            }
        }
    }

    private function update_quote_status($quote_id, $status) {
        $where = array('id' => $quote_id);
        return $quoteId = $this->basic_model->update_records('finance_quote', ['status' => $status], $where);
    }

    function get_quote_details($quoteId) {
        $this->db->select(['fq.quote_number', 'fq.user_type', 'fq.userId', 'fq.quote_date', 'fq.valid_until', 'fq.quote_note', 'fq.user_type', 'fq.status']);
        $this->db->from('tbl_finance_quote as fq');
        $this->db->where('fq.id', $quoteId);
        $this->db->where_in('fq.status', [1, 5, 6]);

        $query = $this->db->get();
        $res = $query->row();

        if (!empty($res)) {
            if ($res->user_type == 6) {

                $column = ['name', 'customerCategoryId', 'contact_name', 'company_name', 'email', 'primary_phone', 'seconday_phone', 'street', 'suburb', 'state', 'postcode'];
                $user_details = $this->basic_model->get_row('finance_quote_enquiry_customer', $column, ['id' => $res->userId]);

                $res = (object) array_merge((array) $res, (array) $user_details);
            }

            $x = $this->get_quote_items($quoteId);
            $res->items = $x['items'];
            $res->manual_item = $x['manual_item'];

            $res->add_manual_item = !empty($res->manual_item) ? true : false;
        }

        return $res;
    }

    function get_quote_items($quoteId) {
        $this->db->select(['id', 'item_type', 'itemId', 'description', 'qty', 'cost', 'price_type']);
        $this->db->select("(CASE 
                WHEN item_type = 1 THEN (select concat(line_item_number, '||BREAKER||', id, '||BREAKER||',upper_price_limit,'||BREAKER||',national_price_limit,'||BREAKER||',national_very_price_limit,'||BREAKER||', (select id from tbl_funding_type where id = funding_type)) from tbl_finance_line_item where id = itemId)
                WHEN item_type = 2 THEN item_name
                ELSE 'N/A'
                END
            ) as item_name");
        $this->db->from('tbl_finance_quote_item');
        $this->db->where('quoteId', $quoteId);
        $this->db->where(['archive' => 0]);

        $query = $this->db->get();
        $res = $query->result();

        $items = [];
        $manaual_item = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                if ($val->item_type == 1) {
                    $x = explode('||BREAKER||', $val->item_name);
                    $items[] = [
                        'funding_type' => $x[5],
                        'line_ItemId' => [
                            'value' => $x[1],
                            'label' => $x[0],
                            'price' => $val->cost,
                            'upper_price_limit' => $x[2],
                            'national_price_limit' => $x[3],
                            'national_very_price_limit' => $x[4],
                        ],
                        'qty' => $val->qty, ''
                        . 'price_type' => $val->price_type
                    ];
                } else {
                    $manaual_item[] = $val;
                }
            }
        }

        return ['items' => $items, 'manual_item' => $manaual_item];
    }

    function get_quote_dashboard_graph($quote_type, $reqData) {
        $checkType = in_array(strtolower($reqData->duration_type), ['week', 'year', 'month']) ? strtolower($reqData->duration_type) : 'week';

        $this->db->select('sum(total) as amount', false);
        $this->db->select(['user_type']);
        $this->db->from('tbl_finance_quote');
        $this->db->group_by('user_type');
        $this->db->order_by('user_type', 'asc');


        if ($quote_type == 'accepted_quote') {
            $this->db->where('status', 3);
        } else {
            //1 - Sent,2 - Sent & Read,3 - Accepted,4 - Not Accepted,5 - Draft,6 - Error Sending,8 - Archived
            $this->db->where_in('status', [1, 2, 3, 4, 6]);
        }

        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(created) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
        }

        $query = $this->db->get();
        $res = $query->result_array();

        $ret = [0, 0, 0];

        if (!empty($res)) {

            $graph_data = array_column($res, 'amount', 'user_type');
            $participant_total = isset($graph_data[2]) ? $graph_data[2] : 0;
            $org_total = isset($graph_data[4]) ? $graph_data[4] : 0;
            $other_total = isset($graph_data[6]) ? $graph_data[6] : 0;
            $grand_total = $participant_total + $org_total + $other_total;
            $ret = [(string) $grand_total, (string) $participant_total, (string) $org_total];
        }

        return $ret;
    }

    function get_quote_dashboard_graph_for_average_time($reqData) {
        $checkType = in_array(strtolower($reqData->duration_type), ['week', 'year', 'month']) ? strtolower($reqData->duration_type) : 'week';

        $this->db->select('(AVG(TIME_TO_SEC(TIMEDIFF(action_at, created))/(60*60*24))) as average', false);
        $this->db->select(['user_type']);
        $this->db->from('tbl_finance_quote');
        $this->db->group_by('user_type');
        $this->db->order_by('user_type', 'asc');

        $this->db->where('status', 3);

        if ($checkType == 'week') {
            $this->db->where('YEARWEEK(created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(created) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
        }

        $query = $this->db->get();
        $res = $query->result_array();

        $ret = [0, 0, 0];

        if (!empty($res)) {

            $graph_data = array_column($res, 'average', 'user_type');
            $participant_total = isset($graph_data[2]) ? $graph_data[2] : 0;
            $org_total = isset($graph_data[4]) ? $graph_data[4] : 0;
            $other_total = isset($graph_data[6]) ? $graph_data[6] : 0;
            $grand_total = $participant_total + $org_total + $other_total;
            $ret = [(string) $grand_total, (string) $participant_total, (string) $org_total];
        }

        return $ret;
    }

}
