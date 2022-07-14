<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_invoice_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
        $this->load->helper('text');
    }

    public function srch_shift_bydate($reqData) {

        $this->load->model('Finanace_shift_payroll_model');
        $shift_partcipant_name = $this->Finanace_shift_payroll_model->get_shift_participant_sub_query_by_other();
        $shift_site_name = $this->Finanace_shift_payroll_model->get_shift_site_sub_query_by_other();

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        
        $available_columns = array("id", "start_time", "end_time", "booked_by", "outstanding");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.id';
            $direction = 'DESC';
        }

        if (!empty($filter->invoice_shift_start_date)) {
            $this->db->where("DATE(s.start_time) >= '" . date('Y-m-d', strtotime($filter->invoice_shift_start_date)) . "'");
        }
        if (!empty($filter->invoice_shift_end_date)) {
            $this->db->where("DATE(s.start_time) <= '" . date('Y-m-d', strtotime($filter->invoice_shift_end_date)) . "'");
        }

        $select_column = array("s.id", "s.start_time", "s.end_time", 's.booked_by', '"Saturday -Active Over Night" as invoiceschedule', '"400" as cost', '"outstanding" as outstanding');
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_shift as s');

        $this->db->select("CASE WHEN s.booked_by=1 THEN (" . $shift_site_name . ") WHEN s.booked_by=2 THEN (" . $shift_partcipant_name . ") ELSE '' END as invoice_for", false);

        if (isset($filter->UserType) && $filter->UserType == 'participant')
            $this->db->join('tbl_shift_participant as sp', 'sp.participantId = ' . $filter->UserId, 'inner');
        else if (isset($filter->UserType) && $filter->UserType == 'finance')
            $this->db->join('tbl_shift_site as ss', 'ss.siteId = ' . $filter->UserId, 'inner');

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $this->db->where(array('s.status' => 6, 's.status!=' => 8));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        if (!empty($dataResult)) {
            
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count, "status" => true);
        return $return;
    }

    public function srch_statement_bydate($reqData) {

        $filter = $reqData->filtered;
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;


        $userType = $filter->UserType;
        $userId = $filter->UserId;

        $select_column = array("f.id", "f.invoice_id", "DATE_FORMAT(f.pay_by, '%d/%m/%Y') as shift_due");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("CASE WHEN f.invoice_type=1 THEN 'shift' ELSE 'manual invoice' END AS invoice_type", FALSE);
        $this->db->where(array('f.archive' => 0, 'f.invoice_for =' => $userId, 'f.booked_by =' => $userType));

        if (!empty($filter->statement_from_date)) {
            $this->db->where("DATE(f.invoice_date) >= '" . date('Y-m-d', strtotime($filter->statement_from_date)) . "'");
        }
        if (!empty($filter->statement_to_date)) {
            $this->db->where("DATE(f.invoice_date) <= '" . date('Y-m-d', strtotime($filter->statement_to_date)) . "'");
        }

        $this->db->from('tbl_finance_invoice as f');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        if (!empty($dataResult)) {
            
        }

        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count, "status" => true);
        return $return;
    }

    public function get_line_item_by_funding_type($reqData) {

        $selectedItemIds = [];
        if (isset($reqData->lineItemInvoiceState)) {
            $lineItemInvoiceState = obj_to_arr($reqData->lineItemInvoiceState);
            $already_selectedItem = array_filter(array_column($lineItemInvoiceState, 'lineItem'));
            if (!empty($already_selectedItem)) {
                foreach ($already_selectedItem as $item) {
                    $selectedItemIds[] = $item['value'];
                }
                $this->db->where_not_in('fli.id', $selectedItemIds);
            }
        }

        $this->db->select(["fli.line_item_number as label", "fli.id as value", "fli.upper_price_limit", "fli.national_price_limit", "fli.national_very_price_limit"]);
        $user_type = isset($reqData->user_type) ? $reqData->user_type : '';
        $user_id = $reqData->user_id;

        # 1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote
        if ($user_type != '' && ($user_type == 2 || $user_type == 3)) {
            $this->db->select(["ppli.fund_remaining", "ppli.id as plan_line_itemId"]);
            $this->db->join("tbl_user_plan as pp", "pp.archive=0 AND pp.funding_type=$reqData->funding_type", "inner");
            $this->db->join("tbl_user_plan_line_items as ppli", "ppli.user_planId=pp.id AND ppli.archive=0 AND ppli.line_itemId= fli.id AND pp.user_type = $user_type AND pp.userId=$user_id", "inner");
            $this->db->where(" '" . DATE_CURRENT . "' between pp.start_date AND pp.end_date ", null, false);
        } else {
            $this->db->where(" '" . DATE_CURRENT . "' BETWEEN fli.start_date and fli.end_date");
        }

        $this->db->join("tbl_finance_measure as fm", "fm.archive=0 AND fm.id=fli.measure_by", "inner");

        $this->db->where(" '" . DATE_CURRENT . "' between fli.start_date AND fli.end_date ", null, false);

        $this->db->from('tbl_finance_line_item as fli');
        $this->db->where(array('fli.funding_type' => $reqData->funding_type, 'fli.measure_by' => $reqData->measure_by));
        $this->db->group_start();
        $this->db->like("line_item_name", $reqData->search);
        $this->db->or_like("line_item_number", $reqData->search);
        $this->db->group_end();
        $query = $this->db->get();
        #last_query();
        $res = $query->result();
        if (!empty($res)) {
            return array('status' => true, 'data' => $res);
        } else {
            return array('status' => false, 'data' => []);
        }
    }

    public function save_invoice($reqData, $m_plan_line_itemId) {
        if (!empty($reqData)) {
            #pr($reqData);
            $invoice_for = $reqData->UserId;
            $booked_by = isset($reqData->UserTypeInt) ? $reqData->UserTypeInt : '';
            $funding_type = isset($reqData->funding_type) ? $reqData->funding_type : '';
            $invoice_data = array(
                'pay_by' => isset($reqData->pay_by) ? $reqData->pay_by : '',
                //'invoice_shift_start_date' => isset($reqData->invoice_shift_start_date) ? DateFormate($reqData->invoice_shift_start_date) : '',
                //'invoice_shift_end_date' => isset($reqData->invoice_shift_end_date) ? DateFormate($reqData->invoice_shift_end_date) : '',
                'invoice_shift_notes' => isset($reqData->invoice_shift_notes) ? $reqData->invoice_shift_notes : '',
                'line_item_notes' => isset($reqData->line_item_notes) ? $reqData->line_item_notes : '',
                'manual_invoice_notes' => isset($reqData->manual_invoice_notes) ? $reqData->manual_invoice_notes : '',
                'invoice_for' => isset($invoice_for) ? $invoice_for : '',
                'booked_by' => isset($booked_by) ? $booked_by : '',
            );

            if (isset($reqData->invoice_id) && $reqData->invoice_id > 0) {
                
            } else {
                $invoice_data['invoice_type'] = 2;
                $invoice_data['created'] = DATE_TIME;
                $invoice_data['invoice_date'] = DATE_CURRENT;
                $invoice_id = $this->basic_model->insert_records('finance_invoice', $invoice_data, $multiple = FALSE);
            }
            $sub_total = $jo = 0;

            $plan_line_itemId = 0;
            if (!empty($reqData->lineItemInvoice)) {
                $ins_ary = [];
                foreach ($reqData->lineItemInvoice as $key => $value) {
                    if ($value->measure_by != '') {
                        $cost = $value->cost;  //quantity*national_price_limit
                        $sub_total = $cost;
                        if ($booked_by == 2 || $booked_by == 3)
                            $plan_line_itemId = $value->lineItem->plan_line_itemId;
                        else
                            $plan_line_itemId = 0;

                        $gst = calculate_gst($sub_total);
                        $ins_ary[] = array('invoice_id' => $invoice_id,
                            'funding_type' => $funding_type,
                            'measure_by' => $value->measure_by,
                            'line_item' => $value->lineItem->value,
                            'quantity' => $value->quantity,
                            'cost' => $value->lineItem->national_price_limit,
                            'sub_total' => $cost,
                            'gst' => $gst,
                            'total' => $gst + $cost,
                            'plan_line_itemId' => $plan_line_itemId
                        );
                        $jo = $jo + $cost;
                    }
                }

                if (!empty($ins_ary)) {
                    if (isset($reqData->mode) && $reqData->mode == 'edit')
                        $this->basic_model->update_records('finance_manual_invoice_line_items', ['archive' => 1], ['invoice_id' => $invoice_id]);

                    $this->basic_model->insert_records('finance_manual_invoice_line_items', $ins_ary, $multiple = TRUE);
                }
            }

            if (!empty($reqData->manualInvoiceItem)) {
                $item_mis = [];
                foreach ($reqData->manualInvoiceItem as $key => $val) {
                    if ($val->item_name != '') {
                        $itemCost = $val->item_cost ?? 0;
                        $gst = calculate_gst($itemCost);

                        $item_mis[] = array('invoice_id' => $invoice_id,
                            'item_name' => $val->item_name,
                            'item_description' => $val->item_description,
                            'item_cost' => $itemCost,
                            'gst' => $gst,
                            'total' => $gst + $itemCost,
                            'plan_line_itemId' => $m_plan_line_itemId
                        );
                        $sub_total = $sub_total + $itemCost ?? 0;
                        $jo = $jo + $itemCost;
                    }
                }

                if (isset($reqData->mode) && $reqData->mode == 'edit')
                    $this->basic_model->update_records('finance_manual_invoice_miscellaneous_items', ['archive' => 1], ['invoice_id' => $invoice_id]);

                if (!empty($item_mis))
                    $this->basic_model->insert_records('finance_manual_invoice_miscellaneous_items', $item_mis, TRUE);
            }

            if (!empty($reqData->srchShiftList)) {
                /* $shifts = [];
                  foreach ($reqData->srchShiftList as $shift) {
                  $shifts[] = array('invoice_id' => $invoice_id,
                  'shift_id' => $shift->id,
                  );
                  }

                  if (isset($reqData->mode) && $reqData->mode == 'edit')
                  $this->basic_model->update_records('finance_invoice_shifts', ['archive' => 1], ['invoice_id' => $invoice_id]);

                  $this->basic_model->insert_records('finance_invoice_shifts', $shifts, TRUE); */
            }

            $gst = calculate_gst($jo);
            $total = $jo + $gst;
            $this->basic_model->update_records('finance_invoice', ['total' => $total, 'gst' => $gst, 'sub_total' => $jo], ['id' => $invoice_id]);

            $this->auto_fund_updated_by_invoice_line_items($invoice_id);

            $requestData = ['invoice_id' => $invoice_id, 'booked_by' => $booked_by, 'invoice_for' => $invoice_for];
            $this->save_invoice_addressed_to($requestData);
            if ($invoice_id > 0) {
                $this->invoice_send_to_xero_by_invoice_id($invoice_id, ['email_sent' => true]);
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }

    public function auto_fund_updated_by_invoice_line_items($invoice_id) {
        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';

        $this->db->select(array('id', 'line_item', 'total', 'plan_line_itemId'));
        $this->db->from('tbl_finance_manual_invoice_line_items');
        $this->db->where(array("invoice_id" => $invoice_id, 'archive' => 0));
        $query = $this->db->get();
        $dataResult = $query->result();

        $objTran = new LineItemTransactionHistory();
        if (!empty($dataResult)) {
            foreach ($dataResult as $value) {
                // create line item transaction history
                $objTran->setLine_item_fund_used($value->total); //include gst
                $objTran->setUser_plan_line_items_id($value->plan_line_itemId);
                $objTran->setLine_item_fund_used_type(2);
                $objTran->setLine_item_use_id($value->id); //item auto id
                $objTran->setStatus(1);
                $objTran->setArchive(0);
                $objTran->setCreated(DATE_TIME);
                $objTran->save_temp_history();
            }
        }

        $this->db->select(array('id', 'total', 'plan_line_itemId'));
        $this->db->from('tbl_finance_manual_invoice_miscellaneous_items');
        $this->db->where(array("invoice_id" => $invoice_id, 'archive' => 0));
        $query = $this->db->get();
        $dataResult = $query->result();


        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                // create line item transaction history
                $objTran->setLine_item_fund_used($val->total); //include gst
                $objTran->setUser_plan_line_items_id($val->plan_line_itemId);
                $objTran->setLine_item_fund_used_type(3);
                $objTran->setLine_item_use_id($val->id); //item auto id
                $objTran->setStatus(1);
                $objTran->setArchive(0);
                $objTran->setCreated(DATE_TIME);
                $objTran->save_temp_history();
            }
        }
        $objTran->save_all();
        $objTran->update_fund_blocked();
    }

    public function save_statemenets($reqData) {
        $statement_id = 0;
        if (!empty($reqData)) {

            $this->db->trans_begin();

            $fileName = 'statement.pdf';
            $shift = $reqData->srchShiftList;
            $ids = array_column($shift, 'id');
            $str = implode(",", $ids);
            $amountData = $this->get_invoice_amount_data($str);
            $result_amounts = pos_index_change_array_data($amountData, 'id');

            $statement_data = array('from_date' => isset($reqData->statement_from_date) ? date('Y-m-d', strtotime($reqData->statement_from_date)) : '',
                'to_date' => isset($reqData->statement_to_date) ? date('Y-m-d', strtotime($reqData->statement_to_date)) : '',
                'issue_date' => isset($reqData->statement_issue_date) ? date('Y-m-d', strtotime($reqData->statement_issue_date)) : '',
                'due_date' => isset($reqData->statement_due_date) ? date('Y-m-d', strtotime($reqData->statement_due_date)) : '',
                'statement_notes' => isset($reqData->statement_shift_notes) ? $reqData->statement_shift_notes : '',
                'statement_for' => isset($reqData->filtered->UserId) ? $reqData->filtered->UserId : 0,
                'statement_file_path' => $fileName,
                'statement_type' => 2,
                'booked_by' => isset($reqData->UserTypeInt) ? $reqData->UserTypeInt : 0,
                'status' => 0,
                'booker_mail' => isset($reqData->bookerCheck) ? 1 : 0,
                'total' => 0.00,
                'archive' => 0,
                'created' => DATE_TIME,
            );

            $statement_id = $this->basic_model->insert_records('finance_statement', $statement_data, $multiple = FALSE);

            $invoice_pdf_rows = '';
            $statment_list = array();
            $total_amount = 0.00;
            $sub_total_ex_gst = 0.00;
            $gst_total = 0.00;
            if (!empty($reqData->srchShiftList)) {
                foreach ($reqData->srchShiftList as $shiftList) {
                    $invoice = array('total' => 0.00, 'gst' => 0.00, 'sub_total' => 0.00);
                    if (!empty($result_amounts[$shiftList->id]) && $result_amounts[$shiftList->id]) {
                        $invoice_amt = $result_amounts[$shiftList->id];
                        $invoice['total'] = $invoice_amt['total'];
                        $invoice['gst'] = $invoice_amt['gst'];
                        $invoice['sub_total'] = $invoice_amt['sub_total'];

                        $total_amount += $invoice_amt['total'];
                        $sub_total_ex_gst += $invoice_amt['sub_total'];
                        $gst_total += $invoice_amt['gst'];

                        $invoice_pdf_rows .= '<tr><td class="text-center  br">' . $invoice_amt['invoice_id'] . '</td> <td class="text-left  br">Assistance with self care activites - weekdays (Daytime)</td>
                        <td class="text-left">$' . $invoice_amt['sub_total'] . '</td></tr>';
                    }



                    $data = array('invoice_id' => $shiftList->id, 'statement_id' => $statement_id, 'archive' => 0);
                    $data = array_merge($data, $invoice);
                    $statment_list[] = $data;
                }
            }

            if (!empty($statment_list)) {
                $booked_by = isset($reqData->UserTypeInt) ? $reqData->UserTypeInt : 0;
                $invoice_for = isset($reqData->filtered->UserId) ? $reqData->filtered->UserId : 0;
                /* user details start */
                if ($booked_by == 1) {
                    $rowUserDetails = $this->Finance_common_model->get_site_details_by_id($invoice_for);
                } else if ($booked_by == 2 || $booked_by == 3) {
                    $rowUserDetails = $this->Finance_common_model->get_participant_details_by_id($invoice_for);
                } else if ($booked_by == 4 || $booked_by == 5) {
                    $rowUserDetails = $this->Finance_common_model->get_organisation_details_by_id($invoice_for);
                } else if ($booked_by == 7) {
                    $rowUserDetails = $this->Finance_common_model->get_house_details_by_id($invoice_for);
                }

                /* user details end */
                $this->basic_model->insert_update_batch($action = 'insert', $table_name = 'finance_statement_attach', $statment_list);
                $this->basic_model->update_records('finance_statement', array('total' => $total_amount), $where = array('id' => $statement_id));
                $res_statement = $this->basic_model->get_record_where('finance_statement', $column = array('statement_number'), $where = array('id' => $statement_id, 'archive' => 0));

                $statement_pdf_data['statement_no'] = 0;
                if (!empty($res_statement)) {
                    $statement_pdf_data['statement_no'] = $res_statement[0]->statement_number;
                }

                $statement_pdf_data['due_date'] = isset($reqData->statement_due_date) ? date('d/m/Y', strtotime($reqData->statement_due_date)) : '';
                $statement_pdf_data['issue_date'] = isset($reqData->statement_issue_date) ? date('d/m/Y', strtotime($reqData->statement_issue_date)) : '';
                $statement_pdf_data['invoice_rows'] = $invoice_pdf_rows;
                $statement_pdf_data['total_amount'] = $total_amount;
                $statement_pdf_data['sub_total_amount'] = $sub_total_ex_gst;
                $statement_pdf_data['total_gst'] = $gst_total;
                $statement_pdf_data['user_details'] = $rowUserDetails;

                $statement_file_name = $this->get_statement_pdf($statement_id);
                $res = obj_to_arr($statement_file_name);
                $fileName = '';
                if (!empty($res['status'])) {
                    if (!empty($res['data']['statement_file_path'])) {
                        $fileName = $res['data']['statement_file_path'];
                    }
                    $this->basic_model->update_records('finance_statement', array('statement_file_path' => $fileName), $where = array('id' => $statement_id));
                }
            }
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return array('status' => false);
            } else {
                $this->db->trans_commit();
                if ($statement_id > 0) {
                    return array('status' => true, 'statement' => $statement_id, 'filepath' => $fileName);
                }
            }
        }
        return array('status' => false);
    }

    public function get_invoice_amount_data($invoiceIds) {
        $this->db->select("fi.invoice_id,fi.id,fi.total,fi.sub_total,fi.gst");
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where_in("fi.id", $invoiceIds, FALSE);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $result = $query->result();
    }

    public function create_statement_pdf($statement_pdf_data) {
        $path = base_url('assets/img/ocs_logo.png');
        error_reporting(0);

        $invoice_pdf_rows = '';
        $total_amount = 0.00;
        $sub_total_ex_gst = 0.00;
        $gst_total = 0.00;
        if (!empty($statement_pdf_data['attach'])) {
            foreach ($statement_pdf_data['attach'] as $attachList) {

                $invoice_pdf_rows .= '<tr><td class="text-center  br">' . $attachList['invoice_number'] . '</td> <td class="text-left  br">Assistance with self care activites - weekdays (Daytime)</td>
                <td class="text-left">$' . $attachList['sub_total'] . '</td></tr>';

                $total_amount += $attachList['total'];
                $gst_total += $attachList['gst'];
                $sub_total_ex_gst += $attachList['sub_total'];
            }

            $statement_pdf_data['pdf_total'] = $total_amount;
            $statement_pdf_data['pdf_sub_total'] = $sub_total_ex_gst;
            $statement_pdf_data['pdf_gst'] = $gst_total;
        } else {
            $statement_pdf_data['pdf_total'] = 0.00;
            $statement_pdf_data['pdf_sub_total'] = 0.00;
            $statement_pdf_data['pdf_gst'] = 0.00;
        }
        $statement_pdf_data['invoice_rows'] = $invoice_pdf_rows;
        $data = $statement_pdf_data;
        $data['logo_path'] = $path;

        $data['type'] = 'footer';
        $footerData = $this->load->view('create_invoice_statement_pdf', $data, true);

        $data['type'] = 'content';
        $file = $this->load->view('create_invoice_statement_pdf', $data, true);

        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load();
        $pdf->AddPage('L');
        $pdf->WriteHTML($file);
        $pdf->setFooter($footerData);
        $rand = date('d_m_Y_hisa');
        $filename = 'statement_' . $statement_pdf_data['statement_number'] . '_' . $rand . '.pdf';
        $pdfFilePath = FINANCE_STATEMENT_FILE_PATH . $filename;
        $pdf->Output($pdfFilePath, 'F');
        return $filename;
    }

    public function shift_caller_sub_query() {
        /*  (SELECT CONCAT_WS(sub_sc.firstname,sub_sc.lastname) from tbl_shift_caller as sub_sc
          INNER JOIN tbl_finance_invoice_shifts as sub_fis ON sub_fis.shift_id=sub_sc.shiftId AND sub_fis.archive=0
          where sub_fis.invoice_id=fi.id LIMIT 1) */
        $this->db->select("CONCAT_WS(' ',sub_sc.firstname,sub_sc.lastname)", false);
        $this->db->from("tbl_shift_caller as sub_sc");
        $this->db->join("tbl_finance_invoice_shifts as sub_fis", "sub_fis.shift_id=sub_sc.shiftId AND sub_fis.archive=0", "inner");
        $this->db->where("sub_fis.invoice_id=fi.id", NULL, FALSE);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function manual_invoice_caller_sub_query() {
        $this->db->select("CONCAT_WS(' ',sub_iat.firstname,sub_iat.lastname)", false);
        $this->db->from("tbl_invoice_addressed_to as sub_iat");
        $this->db->where("sub_iat.invoiceId=fi.id AND sub_iat.booked_by=fi.booked_by AND sub_iat.archive=0", NULL, FALSE);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function invoice_shift_notes_sub_query() {
        $this->db->select('sub_sn.notes');
        $this->db->from("tbl_shift_notes as sub_sn");
        $this->db->join("tbl_finance_invoice_shifts as sub_fis", "sub_fis.shift_id=sub_sn.shiftId AND sub_sn.archive=0 AND sub_fis.archive=sub_sn.archive", "inner");
        $this->db->where("sub_fis.invoice_id=fi.id", NULL, FALSE);
        $this->db->order_by("sub_sn.id", "DESC");
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function dashboard_invoice_list($reqData, $extraParm = []) {
        $exportCall = $extraParm['exportCall'] ?? false;
        $creditNoteCall = $extraParm['creditNoteCall'] ?? false;
        $dashboard_invoice_export_csv = $extraParm['dashboard_invoice_export_csv'] ?? false;
        $invoiceShiftNotesQuery = $this->invoice_shift_notes_sub_query();
        $invoiceStatusCaseQuery = $this->inovice_status_query();
        $invoiceForQueryCase = $this->common_for_status_query();
        $invoiceGenderQueryCase = $this->common_for_status_gender_query("fi", "invoice_for");
        $shift_partcipant_name = $this->get_invoice_participant_sub_query();
        $invoiceQueryFor = $this->invoice_for_user_query();
        //$shift_site_name = $this->get_invoice_site_sub_query();
        $shiftCallerSubQuery = $this->shift_caller_sub_query();
        $manualCallerSubQuery = $this->manual_invoice_caller_sub_query();
        $shiftFundingSubQuery = $this->get_invoic_fund_type_sub_query(1);
        $shiftNdisNumberSubQuery = $this->get_ndis_number_sub_query("fi", 1);
        $shiftDateTimeSubQuery = $this->invoice_shift_time_sub_query();

        $filter = $reqData->filtered ?? [];
        $sorted = $reqData->sorted ?? [];
        $orderBy = '';
        $direction = '';
        if ($exportCall || $creditNoteCall || $dashboard_invoice_export_csv) {
            
        } else {
            $limit = $reqData->pageSize;
            $page = $reqData->page;
        }


        if (isset($filter->fundType) && !empty($filter->fundType)) {
            $fundTypeFilter = $this->basic_model->get_row('funding_type', ['name'], ['id' => $filter->fundType]);
            $this->db->where('fund_type', $fundTypeFilter->name ?? 0);
        }

        $src_columns = array('invoice_number', 'description', 'invoice_for', 'addressto', 'amount', 'fund_type', 'invoice_date', 'invoice_status');
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
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_columns = array("id", "amount", "invoice_number", "invoice_date", "pay_by", "status", "description",
                                    "fund_type","invoice_status","invoice_for_booked"
                                );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $this->db->order_by('fi.created', 'DESC');
            $orderBy = 'fi.id';
            $direction = 'DESC';
        }

        if ($creditNoteCall) {
            if (isset($filter->invoice_for) && !empty($filter->invoice_for)) {
                $this->db->where('invoice_for', $filter->invoice_for);
            }

            if (isset($filter->invoice_for) && !empty($filter->invoice_for)) {
                $this->db->where('booked_by', $filter->booked_by);
            }
        }

        if (isset($filter->start_date) && !empty($filter->start_date)) {
            $this->db->where("DATE(fi.invoice_date) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
        }
        if (isset($filter->end_date) && !empty($filter->end_date)) {
            $this->db->where("DATE(fi.invoice_date) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
        }

        if ($exportCall) {
            $this->db->where_in('fi.status', ['0']);
        }

        $select_column_common = array("fi.id", "fi.total as amount", "fi.invoice_id as invoice_number", "DATE_FORMAT(fi.invoice_date,'%d/%m/%Y') as invoice_date", "DATE_FORMAT(fi.pay_by,'%d/%m/%Y') as pay_by", "fi.status", "CASE WHEN fi.invoice_type=1 THEN 'Shift' WHEN fi.invoice_type=2 THEN 'Manual' ELSE '' END as description",
            "CASE WHEN fi.invoice_type=1 THEN COALESCE((" . $shiftFundingSubQuery . "),'')
            WHEN fi.invoice_type=2 THEN (SELECT  GROUP_concat(distinct(ft.name)) FROM tbl_finance_manual_invoice_line_items fmili inner join tbl_funding_type as ft on ft.id= fmili.funding_type and fmili.archive=0 where fi.id=fmili.invoice_id)
            ELSE '' END fund_type",
            $invoiceStatusCaseQuery . " as invoice_status",
            "(" . $invoiceForQueryCase . ") AS invoice_for_booked",
        );
        $select_column_extra = [];
        if ($exportCall) {
            $select_column_extra = ["fi.invoice_for as register_number", "fi.invoice_type",
                "(" . $shiftNdisNumberSubQuery . ") AS invoice_ndis_number",
                "CASE
            WHEN fi.invoice_type=1 THEN COALESCE((" . $shiftDateTimeSubQuery . "),'') 
            ELSE '' END shift_date_time"
            ];
        } else {

            $select_column_extra = array('fi.line_item_notes', 'fi.manual_invoice_notes',
                "CASE
            WHEN fi.invoice_type=1 AND (fi.booked_by=1 OR  fi.booked_by=7)THEN COALESCE((" . $shiftCallerSubQuery . "),'')
            WHEN fi.invoice_type=1 AND (fi.booked_by=2 OR fi.booked_by=3) THEN COALESCE((" . $shiftCallerSubQuery . "),(" . $shift_partcipant_name . "),'')
            WHEN fi.invoice_type=2 THEN COALESCE((" . $manualCallerSubQuery . "),'')
            ELSE ''
            END as addressto",
                $invoiceGenderQueryCase . " as booked_gender",
                "CASE WHEN fi.invoice_type=1 THEN (" . $invoiceShiftNotesQuery . ") ELSE fi.invoice_shift_notes END as invoice_shift_notes"
            );
        }
        $select_column = array_merge($select_column_common, $select_column_extra);

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_invoice as fi');
        //$this->db->select("CASE WHEN fi.booked_by=1 THEN (" . $shift_site_name . ") WHEN fi.booked_by=2 OR fi.booked_by=3 THEN (" . $shift_partcipant_name . ") ELSE '' END as invoice_for", false);
        $this->db->select($invoiceQueryFor . " as invoice_for", false);
        $this->db->order_by($orderBy, $direction);
        if (!$exportCall && !$creditNoteCall && !$dashboard_invoice_export_csv) {
            $this->db->limit($limit, ($page * $limit));
        }
        $this->db->where(array('fi.archive' => 0));
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // last_query(1);

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if (!$exportCall && !$creditNoteCall && !$dashboard_invoice_export_csv) {
            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }
        }
        $dataResult = $query->result();
        if (!empty($dataResult)) {
            if ($creditNoteCall) {
                $invoiceIds = array_column($dataResult, 'id');
                $creditNoteData = !empty($invoiceIds) ? $this->get_invoice_credit_note_from_or_to_amount_by_invoice_ids($invoiceIds) : [];
            }
            foreach ($dataResult as $key => $value) {
                #$temp = explode('#_SEPARATER_#', $value->line_items_details);
                if ($creditNoteCall) {
                    $value->credit_note_from_used = $creditNoteData[$value->id]['amount_used_from'] ?? 0;
                    $value->credit_note_to_used = $creditNoteData[$value->id]['amount_used_to'] ?? 0;
                }
                #$value->fund_type = $temp[0]??'';
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count, "status" => true);
        return $return;
    }

    public function get_invoice_participant_sub_query($type = 1) {
        $query_res = array();
        $this->db->select("REPLACE(concat(COALESCE(sub_p.firstname,''),' ',COALESCE(sub_p.middlename,''),' ',COALESCE(sub_p.lastname,'')),'  ',' ')", false);
        $this->db->from('tbl_participant as sub_p');
        if ($type == 1) {
            $this->db->where('sub_p.id=fi.invoice_for AND fi.archive=0');
        } else if ($type == 2) {
            $this->db->where('sub_p.id=fs.statement_for AND fs.archive=0');
        } else if ($type == 3) {
            $this->db->where('sub_p.id=fcn.credit_note_for AND fcn.archive=0');
        }
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_invoice_credit_note_from_or_to_amount_by_invoice_ids($invoiceIds = []) {
        $invoiceIds = !empty($invoiceIds) ? $invoiceIds : 0;
        $invoiceIds = !empty($invoiceIds) && is_array($invoiceIds) ? $invoiceIds : [$invoiceIds];
        $this->db->select(["fcnia.invoice_id, CAST(SUM(CASE WHEN fcnia.attached_type=1 THEN fcnia.amount ELSE '0' END) as DECIMAL(10,2)) as amount_used_from", "CAST(SUM(CASE WHEN fcnia.attached_type=2 THEN fcnia.amount ELSE '0' END) as DECIMAL(10,2)) as amount_used_to"]);
        $this->db->from("tbl_finance_credit_note_invoice_attached fcnia");
        $this->db->where(["archive=0"]);
        $this->db->where_in("invoice_id", $invoiceIds);
        $this->db->group_by("invoice_id");
        $query = $this->db->get();
        return $query->num_rows() > 0 ? pos_index_change_array_data($query->result_array(), 'invoice_id') : [];
    }

    public function get_invoice_site_sub_query($type = 1) {
        $query_res = array();
        $this->db->select("sub_os.site_name", false);
        $this->db->from('tbl_organisation_site as sub_os');
        if ($type == 1) {
            $this->db->where('fi.invoice_for=sub_os.id AND fi.archive=0');
        } else if ($type == 2) {
            $this->db->where('fs.statement_for=sub_os.id AND fs.archive=0');
        } else if ($type == 3) {
            $this->db->where('fcn.credit_note_for=sub_os.id AND fcn.archive=0');
        }
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_invoice_house_sub_query($type = 1) {
        $query_res = array();
        $this->db->select("sub_h.name", false);
        $this->db->from('tbl_house as sub_h');
        if ($type == 1) {
            $this->db->where('fi.invoice_for=sub_h.id AND fi.archive=0');
        } else if ($type == 2) {
            $this->db->where('fs.statement_for=sub_h.id AND fs.archive=0');
        } else if ($type == 3) {
            $this->db->where('fcn.credit_note_for=sub_h.id AND fcn.archive=0');
        }
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_invoice_site_house_email_sub_query($type = 1) {
        $this->db->select("sub_ose.email", false);
        $this->db->from('tbl_house_and_site_email as sub_ose');
        $this->db->where('fi.invoice_for=sub_ose.siteId AND fi.archive=0 AND sub_ose.primary_email=1 AND sub_ose.archive=0 AND sub_ose.user_type=' . $type);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_invoice_participant_email_sub_query() {
        $this->db->select("sub_pe.email", false);
        $this->db->from('tbl_participant_email as sub_pe');
        $this->db->where('sub_pe.participantId=fi.invoice_for AND fi.archive=0 AND sub_pe.primary_email=1');
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    function get_manual_invoice_items_by_invoice_id($invoiceId = [], $type = 'pdf') {
        $invoiceId = !empty($invoiceId) ? $invoiceId : 0;
        $invoiceId = is_array($invoiceId) ? $invoiceId : [$invoiceId];
        $this->db->select(['line_item.cost', 'line_item.quantity']);
        if ($type == 'xero') {
            $this->db->select(["CAST(line_item.sub_total as DECIMAL(10,2)) as sub_total", "CAST(line_item.gst as DECIMAL(10,2)) as gst", "CAST(line_item.total as DECIMAL(10,2)) as total", "line_item.line_item as line_item_id", "'' as plan_line_itemId", "'' as xero_line_item_id"]);
            $this->db->select("(select description from tbl_finance_line_item where id = line_item.line_item) as item_description");
        } else if ($type == 'exportCall') {
            $this->db->select(['line_item.invoice_id', "(select line_item_number from tbl_finance_line_item where id = line_item.line_item) as line_item_number", "line_item.line_item as line_itme_id", '0 as misc_item_id']);
        }
        $this->db->select("(select line_item_name from tbl_finance_line_item where id = line_item.line_item) as item_name");
        $this->db->from('tbl_finance_manual_invoice_line_items as line_item');
        $this->db->where('line_item.archive', 0);
        $this->db->where_in('line_item.invoice_id', $invoiceId);
        $query_1 = $this->db->get_compiled_select();

        $this->db->select(['misc_item.item_cost as cost', "'1' as quantity"]);
        if ($type == 'xero') {
            $this->db->select(["CAST(misc_item.item_cost as DECIMAL(10,2)) as sub_total", "CAST(misc_item.gst as DECIMAL(10,2)) as gst", "CAST(misc_item.total as DECIMAL(10,2)) as total", "'0' as line_item_id", "'' as plan_line_itemId", "'' as xero_line_item_id", "misc_item.item_description"]);
        } else if ($type == 'exportCall') {
            $this->db->select(['misc_item.invoice_id', 'misc_item.item_name as line_item_number', '0 as line_itme_id', 'misc_item.id as misc_item_id']);
        }
        $this->db->select("misc_item.item_name");
        $this->db->from('tbl_finance_manual_invoice_miscellaneous_items as misc_item');
        $this->db->where('misc_item.archive', 0);
        $this->db->where_in('misc_item.invoice_id', $invoiceId);
        $query_2 = $this->db->get_compiled_select();
        $sql = $query_1 . ' UNION ' . $query_2;
        if ($type == 'exportCall') {
            $sql .= ' order by invoice_id ASC';
        }
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function get_invoice_items_by_invoice_id($invoiceId = [], $type = 'pdf') {
        $invoiceId = !empty($invoiceId) ? $invoiceId : 0;
        $invoiceId = is_array($invoiceId) ? $invoiceId : [$invoiceId];
        $this->db->select(['slia.cost', 'slia.quantity']);
        if ($type == 'xero') {
            $this->db->select(['CAST(slia.sub_total as DECIMAL(10,2)) as sub_total', 'CAST(slia.gst as DECIMAL(10,2)) as gst', 'CAST(slia.total as DECIMAL(10,2)) as total', 'slia.plan_line_itemId', 'slia.xero_line_item_id', 'slia.shiftId as shift_id', 'slia.line_item as line_item_id']);
            $this->db->select("(select sub_fli.description from tbl_finance_line_item as sub_fli where sub_fli.id = slia.line_item) as item_description");
        } else if ($type == 'exportCall') {
            $this->db->select(['fis.invoice_id', "(select sub_fli.line_item_number from tbl_finance_line_item as sub_fli where sub_fli.id = slia.line_item) as line_item_number", "slia.line_item as line_itme_id", '0 as misc_item_id']);
        }
        $this->db->select("(select sub_fli.line_item_name from tbl_finance_line_item as sub_fli where sub_fli.id = slia.line_item) as item_name");
        $this->db->from('tbl_shift_line_item_attached slia');
        $this->db->join('tbl_finance_invoice_shifts fis', 'fis.shift_id=slia.shiftId AND fis.archive=slia.archive', 'inner');
        $this->db->where('slia.archive', 0);
        $this->db->where_in('fis.invoice_id', $invoiceId);
        $this->db->order_by('fis.invoice_id', 'ASC');
        $query = $this->db->get();
        return $query->result_array();
    }

    function get_shift_invoice_address_to($invoiceId) {

        $this->db->select("CONCAT_WS(' ',sub_sc.firstname,sub_sc.lastname) as name,sub_sc.email,sub_sc.phone", false);
        $this->db->from("tbl_shift_caller as sub_sc");
        $this->db->join("tbl_finance_invoice_shifts as sub_fis", "sub_fis.shift_id=sub_sc.shiftId AND sub_fis.archive=0", "inner");
        $this->db->where("sub_fis.invoice_id", $invoiceId);
        $this->db->limit(1);
        $query = $this->db->get();
        $res = $query->num_rows() > 0 ? $query->row_array() : [];

        $this->db->select(["CONCAT_WS(', ',sl.address,sl.suburb,(SELECT sub_s.name FROM tbl_state as sub_s where sub_s.id=sl.state),sl.postal) as address"]);
        $this->db->from('tbl_shift_location sl');
        $this->db->join('tbl_finance_invoice_shifts fis', 'fis.shift_id=sl.shiftId AND fis.archive=0', 'inner');
        $this->db->where('fis.invoice_id', $invoiceId);
        $this->db->order_by('sl.id', 'ASC');
        $query1 = $this->db->get();
        $res1 = $query1->num_rows() > 0 ? $query1->row_array() : [];
        return array_merge($res, $res1);
    }

    function get_manual_invoice_address_to($invoiceId) {

        $this->db->select("CONCAT_WS(' ',iat.firstname,iat.lastname) as name,iat.email,iat.phone,iat.complete_address as address", false);
        $this->db->from("tbl_invoice_addressed_to as iat");
        $this->db->where("iat.invoiceId", $invoiceId);
        $this->db->where("iat.archive", 0);
        $this->db->limit(1);
        $query = $this->db->get();
        $res = $query->num_rows() > 0 ? $query->row_array() : [];

        return $res;
    }

    function get_invoice_details($invoiceId) {
        $this->load->model(['finance/Finance_common_model']);
        $select_column = array('fi.id', 'fi.invoice_type', 'fi.booked_by', 'fi.invoice_for', 'fi.manual_invoice_notes', 'fi.invoice_date', 'fi.pay_by', "fi.sub_total", "fi.gst", "fi.total", "fi.invoice_id");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_invoice as fi');
        $this->db->where('fi.archive', 0);
        $this->db->where('fi.id', $invoiceId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = [];
        $rowDetails = [];
        $invoice_items = [];
        if ($query->num_rows() > 0) {
            $result = $query->row_array();
            if ($result['booked_by'] == '1') {
                $rowDetails = $this->Finance_common_model->get_site_details_by_id($result['invoice_for']);
            } else if ($result['booked_by'] == '2' || $result['booked_by'] == '3') {
                $rowDetails = $this->Finance_common_model->get_participant_details_by_id($result['invoice_for']);
            } else if ($result['booked_by'] == '4' || $result['booked_by'] == '5') {
                $rowDetails = $this->Finance_common_model->get_organisation_details_by_id($result['invoice_for']);
            } else if ($result['booked_by'] == '7') {
                $rowDetails = $this->Finance_common_model->get_house_details_by_id($result['invoice_for']);
            }


            $result['invoice_for'] = isset($rowDetails['name']) && !empty($rowDetails['name']) ? $rowDetails['name'] : '';
            $result['invoice_email'] = isset($rowDetails['email']) && !empty($rowDetails['email']) ? $rowDetails['email'] : '';
            $result['invoice_phone'] = isset($rowDetails['phone']) && !empty($rowDetails['phone']) ? $rowDetails['phone'] : '';
            $result['invoice_address_for'] = isset($rowDetails['address']) && !empty($rowDetails['address']) ? $rowDetails['address'] : '';
            if ($result['invoice_type'] == '1') {
                $invoice_items = $this->get_invoice_items_by_invoice_id($invoiceId, 'pdf');
                $addressToData = $this->get_shift_invoice_address_to($invoiceId);
                $result['invoice_to'] = isset($addressToData['name']) && !empty($addressToData['name']) ? $addressToData['name'] : (!isset($addressToData['name']) ? $result['invoice_for'] : '');
                $result['invoice_email_to'] = isset($addressToData['email']) && !empty($addressToData['email']) ? $addressToData['email'] : (!isset($addressToData['email']) ? $result['invoice_email'] : '');
                $result['invoice_phone_to'] = isset($addressToData['phone']) && !empty($addressToData['phone']) ? $addressToData['phone'] : (!isset($addressToData['phone']) ? $result['invoice_phone'] : '');
                $result['invoice_address_to'] = !empty($addressToData) && isset($addressToData['address']) && !empty($addressToData['address']) && isset($addressToData['name']) ? $addressToData['address'] : (!isset($addressToData['name']) ? $result['invoice_address_for'] : '');
            } else if ($result['invoice_type'] == '2') {
                $invoice_items = $this->get_manual_invoice_items_by_invoice_id($invoiceId, 'pdf');
                $addressToData = $this->get_manual_invoice_address_to($invoiceId);
                $result['invoice_to'] = isset($addressToData['name']) && !empty($addressToData['name']) ? $addressToData['name'] : (!isset($addressToData['name']) ? $result['invoice_for'] : '');
                ;
                $result['invoice_email_to'] = isset($addressToData['email']) && !empty($addressToData['email']) ? $addressToData['email'] : (!isset($addressToData['email']) ? $result['invoice_email'] : '');
                $result['invoice_phone_to'] = isset($addressToData['phone']) && !empty($addressToData['phone']) ? $addressToData['phone'] : (!isset($addressToData['phone']) ? $result['invoice_phone'] : '');
                $result['invoice_address_to'] = !empty($addressToData) && isset($addressToData['address']) && !empty($addressToData['address']) && isset($addressToData['name']) ? $addressToData['address'] : (!isset($addressToData['name']) ? $result['invoice_address_for'] : '');
            }
            $result['invoice_credit_note_apply'] = $this->get_credit_notes_apply_to_invoic_by_invoice_id($invoiceId);
            $result['invoice_item'] = $invoice_items;
        }


        return $result;
    }

    private function get_credit_notes_apply_to_invoic_by_invoice_id($invoiceId) {
        $creditNoteNumberSubQuery = $this->get_credit_note_number_sub_query();
        $this->db->select(["fcnia.refund_number", "fcnia.amount as refund_amount", "DATE_FORMAT(fcnia.created,'%d/%m/%Y') as refund_date", "(" . $creditNoteNumberSubQuery . ") as refund_credit_note_number"]);
        $this->db->from("tbl_finance_credit_note_invoice_attached fcnia");
        $this->db->where(['fcnia.archive' => 0, 'fcnia.invoice_id' => $invoiceId, 'attached_type' => 2]);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_participant_organization_name($data) {
        $search = $data->search;

        $this->db->select(["concat_ws(' ',p.firstname,p.middlename,p.lastname) as label", "p.id as value", "'2' as type, CASE WHEN p.gender=1 THEN 'male' WHEN p.gender=2 THEN 'female' ELSE '' END as booked_gender", "'participant' as booked_by"]);
        $this->db->from('tbl_participant as p');
        $this->db->where('p.status', 1);
        $this->db->where('p.archive', 0);
        $this->db->like("concat_ws(' ',p.firstname,p.middlename,p.lastname)", $search);

        $query1 = $this->db->get_compiled_select();

        $this->db->select(["org.name as label", "org.id as value", "CASE WHEN org.parent_org=0 THEN '4' ELSE '5' END as type", "'' as booked_gender", "CASE WHEN org.parent_org=0 THEN 'org' ELSE 'sub_org' END as booked_by"]);
        $this->db->from(TBL_PREFIX . 'organisation as org');
        $this->db->where('org.status', 1);
        $this->db->where('org.archive', 0);
        #$this->db->group_start();
        #$this->db->where('parent_org', 0);
        #$this->db->or_where('bill_pay_by', 3);
        #$this->db->group_end();
        $this->db->like("org.name", $search);
        $query2 = $this->db->get_compiled_select();

        $this->db->select(["os.site_name as label", "os.id as value", "'1' as type", "'' as booked_gender", " 'site' as booked_by"]);
        $this->db->from(TBL_PREFIX . 'organisation_site as os');
        $this->db->where('os.status', 1);
        $this->db->where('os.archive', 0);
        #$this->db->where('os.bill_pay_by', 3);
        $this->db->like("os.site_name", $search);
        $query3 = $this->db->get_compiled_select();


        $this->db->select(["h.name as label", "h.id as value", "'7' as type", "'' as booked_gender", " 'house' as booked_by"]);
        $this->db->from(TBL_PREFIX . 'house as h');
        $this->db->where('h.status', 1);
        $this->db->where('h.archive', 0);
        $this->db->like("h.name", $search);
        $query4 = $this->db->get_compiled_select();

        $query = $this->db->query($query1 . ' UNION ' . $query2 . ' UNION ' . $query3 . ' UNION ' . $query4);
        #last_query();
        $res = $query->result_array();

        return $res;
    }

    private function get_invoic_fund_type_sub_query($type = 1) {
        if ($type == 3) {
            $this->db->select("1", false);
        } else {
            $this->db->select("sub_ft.name");
        }
        $this->db->from('tbl_shift as sub_s');
        $this->db->join("tbl_finance_invoice_shifts as sub_fis", "sub_fis.shift_id=sub_s.id AND sub_fis.archive=0", "inner");
        $this->db->join("tbl_funding_type as sub_ft", "sub_ft.id=sub_s.funding_type AND sub_ft.archive=0", "inner");
        if ($type == 1) {
            $this->db->where('sub_fis.invoice_id=fi.id AND fi.archive=0', null, false);
        } else if ($type == 2) {
            $this->db->where("sub_fis.invoice_id=substring_index(group_concat(fi.id order by fi.invoice_date DESC ,fi.id DESC SEPARATOR '" . DEFAULT_BREAKER_SQL_CONCAT . "'),'" . DEFAULT_BREAKER_SQL_CONCAT . "',1) AND fi.archive=0", null, false);
        } else if ($type = 3) {
            $this->db->where('sub_fis.invoice_id=fi.id AND fi.archive=0', null, false);
            $this->db->where('sub_ft.name', 'ndis');
        }
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function invoice_scheduler_list($reqData) {
        $invoiceForQueryCase = $this->common_for_status_query();
        $invoiceGenderQueryCase = $this->common_for_status_gender_query("fi", "invoice_for");
        $invoiceParticipantSubQuery = $this->get_invoice_participant_sub_query();
        $invoiceSiteSubQuery = $this->get_invoice_site_sub_query();
        $invoiceHouseSubQuery = $this->get_invoice_house_sub_query();
        $invoiceFundSingleSubQuery = $this->get_invoic_fund_type_sub_query(1);
        $invoiceFundlastInvoiceSubQuery = $this->get_invoic_fund_type_sub_query(2);

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $src_columns = array('invoice_for_name', 'booked_from', /* 'invoice_fund_type', */ 'invoice_schedule', 'last_invoice_date');
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
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $available_columns = array('invoice_for_name', 'booked_from','invoice_schedule', 'last_invoice_date');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fi.id';
            $direction = 'DESC';
        }

        if (!empty($filter->start_date)) {
            $this->db->where("DATE(fi.invoice_date) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
        }
        if (!empty($filter->end_date)) {
            $this->db->where("DATE(fi.invoice_date) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
        }

        /* $select_column = array("fi.id", "fi.total as amount", "fi.invoice_date", "fi.invoice_id as invoice_number", "fi.invoice_date", "fi.pay_by", "fi.invoice_shift_notes", 'fi.line_item_notes', 'fi.manual_invoice_notes', 'fi.status', '"Shift" as description', "(SELECT  GROUP_concat(distinct(ft.name)) FROM tbl_finance_manual_invoice_line_items fmili inner join tbl_funding_type as ft on ft.id= fmili.funding_type and fmili.archive=0 where fi.id=fmili.invoice_id) as fund_type", "(CASE WHEN fi.booked_by=1 THEN 'Organisation' WHEN fi.booked_by=2 THEN 'Participant' WHEN fi.booked_by=3 THEN 'Location' END) AS addressto ");

          $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false); */
        $this->db->select("SQL_CALC_FOUND_ROWS " . "'Instant' as invoice_schedule");
        $this->db->from('tbl_finance_invoice as fi');
        $this->db->select([
            "CASE WHEN count(fi.invoice_date)>1 THEN date_format(substring_index(substring_index(group_concat(fi.invoice_date order by fi.invoice_date DESC ,fi.id DESC SEPARATOR '" . DEFAULT_BREAKER_SQL_CONCAT . "' ),'" . DEFAULT_BREAKER_SQL_CONCAT . "',2),'" . DEFAULT_BREAKER_SQL_CONCAT . "',-1),'%d/%m/%Y') ELSE '' END as last_invoice_date",
            "CASE
            WHEN count(fi.invoice_date) >1 THEN COALESCE((" . $invoiceFundlastInvoiceSubQuery . "),'')
            WHEN count(fi.invoice_date) =1 THEN COALESCE((" . $invoiceFundSingleSubQuery . "),'')
            ELSE ''
            END as invoice_fund_type",
            "CASE WHEN fi.booked_by=1 THEN (" . $invoiceSiteSubQuery . ") WHEN fi.booked_by=7 THEN (" . $invoiceHouseSubQuery . ") WHEN fi.booked_by=2 OR fi.booked_by=3 THEN (" . $invoiceParticipantSubQuery . ") ELSE '' END as invoice_for_name",
            $invoiceForQueryCase . " as booked_from",
            //"CASE WHEN fi.booked_by=2 OR fi.booked_by=3 THEN 'participant' WHEN fi.booked_by=1 then 'site' ELSE '' END as booked_from",
            "fi.invoice_for as booked_for",
            "fi.booked_by as invoice_booked",
            $invoiceGenderQueryCase . " as booked_gender"
                ], false);
        $this->db->where(array('fi.archive' => 0, 'fi.invoice_type' => 1));
        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('fi.booked_by,fi.invoice_for');
        $this->db->limit($limit, ($page * $limit));
        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

    private function get_shift_details_by_shift_id(int $shiftId = 0, $paramExtra = []) {
        $forKeyPay = $paramExtra['for_keypay'] ?? false;
        $forInvoice = $paramExtra['for_invioce'] ?? false;
        $this->db->select([
            's.invoice_created',
            's.funding_type',
            "(SELECT sub_ft.name FROM tbl_funding_type as sub_ft where sub_ft.id=s.funding_type) as funding_type_name",
            's.start_time as shift_start_date',
            's.end_time as shift_end_date',
            's.booked_by',
            's.id as shift_id',
            "CASE
            WHEN s.booked_by=2 OR s.booked_by=3
            THEN COALESCE((SELECT sub_sp.participantId FROM tbl_shift_participant as sub_sp WHERE sub_sp.shiftId=s.id and sub_sp.status=1 LIMIT 1),0)
            WHEN s.booked_by=1
            THEN COALESCE((SELECT sub_ss.siteId FROM tbl_shift_site as sub_ss WHERE sub_ss.shiftId=s.id LIMIT 1),0)
            WHEN s.booked_by=7
            THEN COALESCE((SELECT sub_su.user_for FROM tbl_shift_users as sub_su WHERE sub_su.shiftId=s.id AND sub_su.user_type=7 LIMIT 1),0)
            ELSE '0'
            END as booked_for"
        ]);
        if ($forKeyPay) {
            $this->db->select([
                "COALESCE((SELECT sub_sm.memberId FROM tbl_shift_member as sub_sm where sub_sm.shiftId=s.id AND status=3 AND sub_sm.archive = 0 LIMIT 1),0) as member_id",
                "COALESCE((SELECT sub_sn.notes FROM tbl_shift_notes as sub_sn where sub_sn.shiftId=s.id AND archive=0 LIMIT 1),'') as shift_note",
                "COALESCE((SELECT CONCAT(sub_sl.lat,'" . DEFAULT_BREAKER_SQL_CONCAT . "',sub_sl.long)  FROM tbl_shift_location as sub_sl where sub_sl.shiftId=s.id order by sub_sl.id ASC LIMIT 1 ),'') as lat_long"
            ]);
        }
        $this->db->from('tbl_shift as s');
        $this->db->where(['s.id' => $shiftId, 's.status' => 6]);
        if ($forInvoice) {
            $this->db->where(['s.invoice_created' => 0]);
        } else if ($forKeyPay) {
            $this->db->where(['s.keypay_created' => 0]);
        }
        $query = $this->db->get();
        #last_query(1);
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    public function create_invoice_by_shift_id(int $shiftId = 0) {
        $this->load->model(['finance/Finance_common_model']);
        $shiftDetails = $this->get_shift_details_by_shift_id($shiftId, ['for_invioce' => true]);

        if (empty($shiftDetails)) {
            return ['status' => false, 'error' => 'Shift not completed or already invoice created yet.'];
        }
        $ins_invoice = [
            'invoice_date' => DATE_CURRENT,
            'pay_by' => date(DB_DATE_FORMAT, strtotime(DATE_CURRENT . ' +30 days')),
            'invoice_shift_start_date' => $shiftDetails['shift_start_date'],
            'invoice_shift_end_date' => $shiftDetails['shift_end_date'],
            'booked_by' => $shiftDetails['booked_by'],
            'invoice_for' => $shiftDetails['booked_for'],
            'status' => 0,
            'total' => 0,
            'gst' => 0,
            'sub_total' => 0,
            'archive' => 0,
            'invoice_type' => 1,
            'created' => DATE_TIME,
        ];
        $invoice_Id = $this->basic_model->insert_records('finance_invoice', $ins_invoice);
        $this->basic_model->insert_records('finance_invoice_shifts', ['invoice_id' => $invoice_Id, 'shift_id' => $shiftId, 'archive' => 0, 'created' => DATE_TIME]);
        $this->basic_model->update_records('shift', ['invoice_created' => 1], ['id' => $shiftId]);
        $res = $this->invoice_send_to_xero_by_invoice_id($invoice_Id, ['email_sent' => true]);
        $resMember = $this->shift_details_send_to_keyPay_by_shift_details($shiftId);
        return $res;
    }

    private function inovice_status_query($tableAlias = 'fi') {
        return "CASE WHEN " . $tableAlias . ".status=1 THEN 'Payment Received' WHEN " . $tableAlias . ".status=2 THEN 'Payment Not Received' WHEN " . $tableAlias . ".status=2 THEN 'Payment Pending' WHEN " . $tableAlias . ".status=0 THEN 'Payment Pending' ELSE '' END ";
    }

    private function common_for_status_query($tableAlias = 'fi') {
        return "CASE WHEN " . $tableAlias . ".booked_by=1 THEN 'site' WHEN " . $tableAlias . ".booked_by=2 OR " . $tableAlias . ".booked_by=3 THEN 'participant' WHEN " . $tableAlias . ".booked_by=4 THEN 'org' WHEN " . $tableAlias . ".booked_by=5 THEN 'sub_org' WHEN " . $tableAlias . ".booked_by=7 THEN 'house' ELSE '' END ";
    }

    private function common_for_user_query($tableAlias = 'fi', $tabType = 1) {
        $shift_partcipant_name = $this->get_invoice_participant_sub_query($tabType);
        $shift_site_name = $this->get_invoice_site_sub_query($tabType);
        $shift_house_name = $this->get_invoice_house_sub_query($tabType);
        $org_name = $this->get_invoice_org_sub_query(1, $tabType);
        $sub_org_name = $this->get_invoice_org_sub_query(2, $tabType);
        return "CASE WHEN " . $tableAlias . ".booked_by=1 THEN (" . $shift_site_name . ") WHEN " . $tableAlias . ".booked_by=7 THEN (" . $shift_house_name . ") WHEN " . $tableAlias . ".booked_by=2 OR " . $tableAlias . ".booked_by=3 THEN (" . $shift_partcipant_name . ") WHEN " . $tableAlias . ".booked_by=4 THEN (" . $org_name . ") WHEN " . $tableAlias . ".booked_by=5 THEN (" . $sub_org_name . ") ELSE '' END ";
    }

    private function invoice_for_user_query() {
        return $this->common_for_user_query('fi', 1);
    }

    function get_participant_gender_subQuery($tableAliasWithColumn = "fi.invoice_for") {
        $this->db->select("CASE WHEN sub_p.gender=1 THEN 'male' WHEN sub_p.gender=2 THEN 'female' ELSE '' END", false);
        $this->db->from('tbl_participant sub_p');
        $this->db->where($tableAliasWithColumn . "=sub_p.id", NULL, FALSE);
        return $this->db->get_compiled_select();
    }

    private function common_for_status_gender_query($tableAlias = 'fi', $column_name = 'invoice_for') {
        $participaintQuery = $this->get_participant_gender_subQuery($tableAlias . "." . $column_name);
        return "CASE  WHEN " . $tableAlias . ".booked_by=2 OR " . $tableAlias . ".booked_by=3 THEN (" . $participaintQuery . ") ELSE '' END ";
    }

    public function get_invoice_scheduler_history_list($reqData) {
        $invoiceStatusCaseQuery = $this->inovice_status_query();

        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $invoiceFor = !empty($filter->inf) ? $filter->inf : 0;
        $bookedBy = !empty($filter->booked_by) ? $filter->booked_by : 0;
        $available_columns = ["invoice_number","invoice_amount","invoice_send_date","invoice_amount_paid","invoice_finalised_date","invoice_status"]; 
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fi.id';
            $direction = 'DESC';
            $this->db->order_by('fi.created', 'DESC');
        }

        $this->db->from('tbl_finance_invoice as fi');
        $this->db->select([
            "fi.invoice_id as invoice_number",
            "fi.total as invoice_amount",
            "DATE_FORMAT(fi.invoice_date,'%d/%m/%Y') as invoice_send_date",
            "CASE WHEN fi.status=1 THEN fi.total ELSE '' END as invoice_amount_paid",
            "CASE WHEN fi.status=1 OR  fi.status=2 THEN DATE_FORMAT(fi.invoice_finalised_date,'%d/%m/%Y') ELSE '' END as invoice_finalised_date",
            $invoiceStatusCaseQuery . " as invoice_status",
                ], false);
        $this->db->where(array('fi.archive' => 0, 'invoice_for' => $invoiceFor, 'booked_by' => $bookedBy));

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $dataResult = $query->result();
        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

    public function get_invoice_pdf($invoiceId) {
        $response = $this->get_invoice_pdf_data($invoiceId);
        if (!empty($response->invoice_file_path)) {
            $pdfFileFCpath = FCPATH . FINANCE_INVOICE_FILE_PATH . $response->invoice_file_path;
            if (!file_exists($pdfFileFCpath)) {
                $this->create_invoice_pdf($invoiceId);
                $response = $this->get_invoice_pdf_data($invoiceId);
            }
        } else {
            $this->create_invoice_pdf($invoiceId);
            $response = $this->get_invoice_pdf_data($invoiceId);
        }
        return $response;
    }

    private function get_invoice_pdf_data($invoiceId, $returnEmail = false) {
        $where = ['id' => $invoiceId];
        $invoiceStatusCaseQuery = $this->inovice_status_query();
        $invoiceForQueryCase = $this->common_for_status_query();
        // $shiftPartcipantName = $this->get_invoice_participant_sub_query();
        //$shiftSiteName = $this->get_invoice_site_sub_query();
        $invoiceForSubQuery = $this->common_for_user_query('fi', 1);
        if ($returnEmail) {
            $invoiceEmailSubQuery = $this->common_invoice_email_user('fi');
        }
        $select_column = array(
            "fi.id", 'fi.status', 'fi.booked_by', 'fi.invoice_for',
            $invoiceForQueryCase . " AS invoice_for_booked",
            $invoiceStatusCaseQuery . " as invoice_status"
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select($invoiceForSubQuery . " as invoice_for", false);
        if ($returnEmail) {
            $this->db->select($invoiceEmailSubQuery . " as invoice_email", false);
        }
        return $this->basic_model->get_row('finance_invoice as fi', ['fi.invoice_file_path', 'fi.invoice_id'], $where);
    }

    public function create_invoice_pdf($invoiceId) {

        $invoice_data = $this->get_invoice_details($invoiceId);
        $path = base_url('assets/img/ocs_logo.png');
        error_reporting(0);

        $data['logo_path'] = $path;
        $data['invoice_data'] = $invoice_data;

        $data['type'] = 'footer';
        $footerData = $this->load->view('create_invoice_pdf', $data, true);

        $data['type'] = 'content';

        $file = $this->load->view('create_invoice_pdf', $data, true);
        # pr($data);
        $this->load->library('m_pdf');
        $pdf = $this->m_pdf->load(); //"'en-GB-x','A4-L','','',10,10,10,10,6,3,'L'");
        $pdf->AddPage('L');
        $pdf->WriteHTML($file);
        $pdf->setFooter($footerData); // $_SERVER['HTTP_HOST'] . '|{PAGENO}/{nbpg}|' . date('d-m-Y H:i:s')); // Add a footer
        $rand = date('d_m_Y_hisa');
        $filename = 'Invoice_' . $invoiceId . '_' . $rand . '.pdf';
        $pdfFilePath = FINANCE_INVOICE_FILE_PATH . $filename;
        create_directory(FCPATH . FINANCE_INVOICE_FILE_PATH);
        $pdf->Output($pdfFilePath, 'F');
        //echo $pdf->Output();die;
        $quote_data = $this->save_invoice_file($invoiceId, $filename);
    }

    function resend_invoice_mail($invoiceId, $cc_email = null) {
        $sendMailDetail = $this->get_invoice_pdf_data($invoiceId, true);
        $mail_res = false;
        $file_notExists = true;
        if (!empty($sendMailDetail->invoice_file_path)) {
            $pdf_fileFCpath = FCPATH . FINANCE_INVOICE_FILE_PATH . $sendMailDetail->invoice_file_path;
            if (file_exists($pdf_fileFCpath)) {
                $file_notExists = false;
                $sendMailDetail->invoicePdfPath = $pdf_fileFCpath;
                $sendMailDetail->cc_email = $cc_email;
                $mail_res = send_invoice_email($sendMailDetail);
            }
        }
        if ($file_notExists) {
            // create quote pdf if not exist
            $this->create_invoice_pdf($invoiceId);
            $sendMailDetail = $this->get_invoice_pdf_data($invoiceId, true);
            if (!empty($sendMailDetail->invoice_file_path)) {
                $pdf_fileFCpath = FCPATH . FINANCE_INVOICE_FILE_PATH . $sendMailDetail->invoice_file_path;
                if (file_exists($pdf_fileFCpath)) {
                    $sendMailDetail->invoicePdfPath = $pdf_fileFCpath;
                    $sendMailDetail->cc_email = $cc_email;
                    $mail_res = send_invoice_email($sendMailDetail);
                }
            }
        }
        return $mail_res;
    }

    function save_invoice_file($invoiceId, $fileName) {
        $where = array('id' => $invoiceId);
        return $quoteId = $this->basic_model->update_records('finance_invoice', ['invoice_file_path' => $fileName], ['id' => $invoiceId]);
    }

    function update_invoice_status($invoiceId, $status) {
        $invoiceData = $this->basic_model->get_row('finance_invoice', ['status', 'xero_invoice_id'], ['id' => $invoiceId]);
        if ($invoiceData) {
            if ($invoiceData->status == 0) {
                $this->basic_model->update_records('finance_invoice', ['status' => $status, 'invoice_finalised_date' => DATE_CURRENT], ['id' => $invoiceId]);
                $this->invoice_update_status($invoiceData->xero_invoice_id, $status);
                $res = ['status' => true, 'msg' => 'Invoice status updated successfully.'];
            } else {
                $res = ['status' => false, 'error' => 'Selected invoice status already updated.'];
            }
        } else {
            $res = ['status' => false, 'error' => 'Invalid request.'];
        }
        return $res;
    }

    /* $extraParm=['column_name_invoice_number'=>'claimreference','column_name_invoice_status'=>'payment request status','column_name_invoice_payment_request_number'=>'payment request number'] */

    public function check_and_update_invoice_status_data($rowData = [], $extraParm = []) {
        $column_name_invoice_number = $extraParm['column_name_invoice_number'] ?? 'claimreference';
        $column_name_invoice_status = $extraParm['column_name_invoice_status'] ?? 'payment request status';
        $column_name_invoice_payment_request_number = $extraParm['column_name_invoice_payment_request_number'] ?? 'payment request number';
        $import_batch = $extraParm['import_batch'] ?? '';
        if (!empty($rowData)) {
            $allowedStatus = json_decode(INVOICE_PAYMENT_NDIS_IMPORT_STATUS_DATA, true); //['successful'=>'1','error'=>'2'];
            $hcmPaymentStatus = json_decode(INVOICE_PAYMENT_STATUS_DATA);

            $currentDatetime = DATE_TIME;
            $table = 'invoice_import_ndis_status_number_' . time();
            $tablePreFix = TBL_PREFIX . $table;
            $this->db->query("DROP TABLE IF EXISTS " . $tablePreFix);
            $this->db->query(" CREATE TEMPORARY TABLE " . $tablePreFix . " (
               invoice_number VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL)");
            $invoice_number = array_map(function($row) use($column_name_invoice_number) {
                return ['invoice_number' => $row[$column_name_invoice_number]];
            }, $rowData);
            $this->basic_model->insert_update_batch('insert', $table, $invoice_number);
            $this->db->select(['fi.status', 'fi.invoice_id as invoice_number', 'fi.id', 'fi.xero_invoice_id']);
            $this->db->from('tbl_finance_invoice fi');
            $this->db->join($tablePreFix . ' as tmp_fi', 'fi.invoice_id = tmp_fi.invoice_number and fi.archive=0', 'inner');
            $qurey = $this->db->get();
            $invoiceExistsRes = $qurey->num_rows() > 0 ? pos_index_change_array_data($qurey->result_array(), 'invoice_number') : [];
            $errors = [];
            $duplicateInvoiceNumber = [];
            $statusInvoiceNumber = [];
            $rowInvoiceNumber = [];
            $updatedStausMsg = [];
            $updatedStausData = [];
            $updatedStausDataXero = [];
            $updatedErrorMsgStatus = [];
            foreach ($rowData as $key => $val) {
                $rowNumber = $key + 2;
                if (!isset($invoiceExistsRes[$val[$column_name_invoice_number]])) {
                    $errors[] = "Row - " . ($rowNumber) . " Records not found in HCM application.";
                    continue;
                }

                if (empty($val[$column_name_invoice_status]) || !in_array($val[$column_name_invoice_status], array_keys($allowedStatus))) {
                    $errors[] = "Row - " . ($rowNumber) . " ndis status empty or not allowed.(only allowed status is " . implode(' ,', array_keys($allowedStatus)) . ")";
                    $statusInvoiceNumber[$val[$column_name_invoice_number]][] = $val[$column_name_invoice_status];
                    continue;
                }
                if (isset($invoiceExistsRes[$val[$column_name_invoice_number]]) && $invoiceExistsRes[$val[$column_name_invoice_number]]['status'] != 0) {
                    $errors[] = "Row - " . ($rowNumber) . " Application status already updated or current status not in payment pending.";
                    continue;
                }
                $statusInvoiceNumber[$val[$column_name_invoice_number]][] = $val[$column_name_invoice_status];
                if (isset($updatedStausData[$val[$column_name_invoice_number]]) && $updatedStausData[$val[$column_name_invoice_number]]['status'] != $allowedStatus[$val[$column_name_invoice_status]]) {
                    $errors[] = "Row - " . ($rowNumber) . " multiple invoice status found (" . $val[$column_name_invoice_number] . ") exist on this sheet, this invoice will not be processed .";
                    $duplicateInvoiceNumber[] = $val[$column_name_invoice_number];
                    continue;
                }

                if (count(array_unique($statusInvoiceNumber[$val[$column_name_invoice_number]])) > 1) {
                    $errors[] = "Row - " . ($rowNumber) . " multiple invoice status(" . $val[$column_name_invoice_number] . ") exist on this sheet, this invoice will not be processed .";
                    $duplicateInvoiceNumber[] = $val[$column_name_invoice_number];
                    continue;
                }

                if ($allowedStatus[$val[$column_name_invoice_status]] == 2) {
                    $updatedErrorMsgStatus[$val[$column_name_invoice_number]][] = [
                        'error_msg' => $val['error message'] ?? '',
                        'cancellation_reason' => $val['cancellationreason'] ?? '',
                        'invoice_id' => $invoiceExistsRes[$val[$column_name_invoice_number]]['id'],
                        'payment_request_number' => $val[$column_name_invoice_payment_request_number],
                        'import_batch' => $import_batch,
                        'archive' => 0,
                        'created' => $currentDatetime,
                    ];
                }
                $updatedStausMsg[$val[$column_name_invoice_number]] = "Row - " . ($rowNumber) . " invoice data updated sucessfully from " . $hcmPaymentStatus[$invoiceExistsRes[$val[$column_name_invoice_number]]['status']] . " to " . $hcmPaymentStatus[$allowedStatus[$val[$column_name_invoice_status]]];
                $rowInvoiceNumber[$val[$column_name_invoice_number]] = $rowNumber;
                $updatedStausData[$val[$column_name_invoice_number]] = [
                    'id' => $invoiceExistsRes[$val[$column_name_invoice_number]]['id'],
                    'xero_invoice_id' => $invoiceExistsRes[$val[$column_name_invoice_number]]['xero_invoice_id'],
                    'status' => $allowedStatus[$val[$column_name_invoice_status]],
                    'invoice_finalised_date' => $currentDatetime
                ];
                $updatedStausDataXero[$val[$column_name_invoice_number]] = [
                    'invoice_id' => $invoiceExistsRes[$val[$column_name_invoice_number]]['id'],
                    'invoice_data' => json_encode($updatedStausData[$val[$column_name_invoice_number]]),
                    'log_status' => 0,
                    'created' => $currentDatetime,
                    'archive' => 0
                ];
            }
            if (!empty($duplicateInvoiceNumber)) {
                foreach ($duplicateInvoiceNumber as $dVal) {
                    if (isset($updatedStausData[$dVal])) {
                        $errors[] = "Row - " . ($rowInvoiceNumber[$dVal] ?? '') . " duplicate invoice (" . $dVal . ") exist on this sheet, this invoice will not be processed .";
                        unset($updatedStausData[$dVal]);
                        unset($updatedStausMsg[$dVal]);
                        unset($updatedStausDataXero[$dVal]);
                        unset($updatedErrorMsgStatus[$dVal]);
                    }
                }
            }
            $this->db->query("DROP TABLE IF EXISTS " . $tablePreFix);
            $errorStr = !empty($errors) ? '<p>' . implode('</p><p>', $errors) . '</p>' : '';
            if (!empty($updatedStausData)) {
                $this->basic_model->insert_update_batch('update', 'finance_invoice', $updatedStausData, 'id');
                $this->basic_model->insert_update_batch('insert', 'finance_invoice_import_status_update_log', $updatedStausDataXero);
                if (!empty($updatedErrorMsgStatus)) {
                    $updateDataErrorMsgTemp = [];
                    foreach ($updatedErrorMsgStatus as $row) {
                        $updateDataErrorMsgTemp = array_merge($updateDataErrorMsgTemp, $row);
                    }
                    $this->basic_model->insert_update_batch('insert', 'finance_invoice_ndis_error_message', $updateDataErrorMsgTemp);
                }

                $url = rtrim(base_url(), '/') . '/cron/Finance_cron/update_finance_invoice_status';
                //$dataStatus =['invoiceDetails'=>json_encode($updatedStausData),'token'=>''];
                $this->load->library('CustomCurlCall');
                $this->customcurlcall->requestWithoutWait('GET', $url);
                $successStr = '<p>' . implode('</p><p>', $updatedStausMsg) . '</p>';
                $res1 = ['status' => true, 'msg' => $successStr];
                if (!empty($errorStr)) {
                    $res1['warraing'] = $errorStr;
                }
                return $res1;
            } else {
                return ['status' => false, 'error' => $errorStr];
            }
        } else {
            return ['status' => false, 'error' => 'file row is empty.'];
        }
    }

    function get_income_from_invoice($type = 'week') {
        $response = ['0', '0', '0'];
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';

        $this->db->select("CAST(COALESCE(SUM(total),0) as DECIMAL(10,2)) as total, CAST(COALESCE(SUM(CASE when booked_by=2 OR booked_by=3 THEN total ELSE '0' END),0) as DECIMAL(10,2)) as participant , CAST(COALESCE(SUM(CASE when booked_by!=2 AND booked_by!=3 THEN total ELSE '0' END),0) as DECIMAL(10,2)) as orgs  ", false);
        $this->db->where(['status' => 1, 'archive' => 0]);
        $this->db->where("DATE(invoice_finalised_date) <=CURDATE()", NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(invoice_finalised_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(invoice_finalised_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get('tbl_finance_invoice');
        if ($query->num_rows() > 0) {
            $response = array_values($query->row_array());
        }
        return $response;
    }

    function get_loss_credit_notes_invoice($type = 'week') {
        $response = ['0', '0', '0'];
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';

        $this->db->select("CAST(COALESCE(SUM(total_amount),0) as DECIMAL(10,2)) as total, CAST(COALESCE(SUM(CASE when booked_by=2 OR booked_by=3 THEN total_amount ELSE '0' END),0) as DECIMAL(10,2)) as participant , CAST(COALESCE(SUM(CASE when booked_by!=2 AND booked_by!=3 THEN total_amount ELSE '0' END),0) as DECIMAL(10,2)) as orgs  ", false);
        $this->db->where(['status' => 1, 'archive' => 0]);
        $this->db->where("DATE(created) <=CURDATE()", NULL, false);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(created) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(created) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get('tbl_finance_credit_note');
        if ($query->num_rows() > 0) {
            $response = array_values($query->row_array());
        }
        return $response;
    }

    function get_loss_refund_invoice($type = 'week') {
        $response = ['0', '0', '0'];
        return $response;
    }

    function get_invoice_credited_and_refund_paid($type = 'week') {
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $this->db->select("CAST(COALESCE(SUM(fcn.total_amount),0) as DECIMAL(10,2)) as total_amount");
        $this->db->from("tbl_finance_credit_note as fcn");
        $this->db->where(["fcn.status" => 1, "fcn.archive" => 0]);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(fcn.created,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(fcn.created) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(fcn.created) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(fcn.created) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get();
        $amounts = $query->num_rows() > 0 ? $query->row()->total_amount : 0;
        return $amounts == '0.00' ? '0' : $amounts;
    }

    function get_invoice_profit_money($type = 'week') {
        $response = 0;
        $companyId = XERO_DEFAULT_COMPANY_ID;
        $params = array('company_id' => $companyId);
        $this->load->library('XeroInvoice', $params);
        $r = $this->xeroinvoice->get_profit_loss();

        if ($r['status']) {
            $response = $r['data'];
        }
        return $response;
    }

    function get_invoice_money($type = 'week') {
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $fundTypeSubquery = $this->get_invoic_fund_type_sub_query(3);
        $manualFundTypeSubquery = $this->manual_fund_type_check(3);
        $this->db->select("CAST(COALESCE(SUM(fi.total),0) as DECIMAL(10,2)) as total_amount ");
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where(["fi.status" => 1, "fi.archive" => 0]);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(fi.invoice_finalised_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(fi.invoice_finalised_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get();
        $amounts = $query->num_rows() > 0 ? $query->row()->total_amount : 0;
        return $amounts == '0.00' ? '0' : $amounts;
    }

    function get_ndis_invoice_money($type = 'week') {
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $fundTypeSubquery = $this->get_invoic_fund_type_sub_query(3);
        $manualFundTypeSubquery = $this->manual_fund_type_check(3);
        $this->db->select("CAST(COALESCE(SUM( CASE
            WHEN invoice_type=1 AND COALESCE((" . $fundTypeSubquery . "),0)=1 THEN fi.total
            WHEN invoice_type=2 AND COALESCE((" . $manualFundTypeSubquery . "),0)=1 THEN fi.total
            ELSE '0' END),0) as DECIMAL(10,2)) as total_amount ");
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where(["fi.status" => 1, "fi.archive" => 0]);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(fi.invoice_finalised_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(fi.invoice_finalised_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get();
        $amounts = $query->num_rows() > 0 ? $query->row()->total_amount : 0;
        return $amounts == '0.00' ? '0' : $amounts;
    }

    function get_invoice_ndis_rejected($type = 'week') {
        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $fundTypeSubquery = $this->get_invoic_fund_type_sub_query(3);
        $manualFundTypeSubquery = $this->manual_fund_type_check(3);
        $this->db->select("CAST(COALESCE(SUM( CASE
            WHEN invoice_type=1 AND COALESCE((" . $fundTypeSubquery . "),0)=1 THEN fi.total
            WHEN invoice_type=2 AND COALESCE((" . $manualFundTypeSubquery . "),0)=1 THEN fi.total
            ELSE '0' END),0) as DECIMAL(10,2)) as total_amount ");
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where(["fi.archive" => 0]);
        $this->db->where_in("fi.status", [2]);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(fi.invoice_finalised_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(fi.invoice_finalised_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get();
        $amounts = $query->num_rows() > 0 ? $query->row()->total_amount : 0;
        return $amounts == '0.00' ? '0' : $amounts;
    }

    function get_invoice_rejected($type = 'week') {

        $checkType = in_array(strtolower($type), ['week', 'year', 'month']) ? strtolower($type) : 'week';
        $this->db->select("CAST(COALESCE(SUM(fi.total),0) as DECIMAL(10,2)) as total_amount ");
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where(["fi.archive" => 0]);
        $this->db->where_in("fi.status", [2]);
        if ($checkType == 'week') {
            $this->db->where(' YEARWEEK(fi.invoice_finalised_date,7) = YEARWEEK(CURDATE(),7)', NULL, false);
        } else if ($checkType == 'month') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
            $this->db->where('MONTH(fi.invoice_finalised_date) = MONTH(CURDATE())', NULL, false);
        } else if ($checkType == 'year') {
            $this->db->where('YEAR(fi.invoice_finalised_date) = YEAR(CURDATE())', NULL, false);
        }
        $query = $this->db->get();
        $amounts = $query->num_rows() > 0 ? $query->row()->total_amount : 0;
        return $amounts == '0.00' ? '0' : $amounts;
    }

    function get_last_ndis_billing() {
        $this->db->select("COALESCE(DATEDIFF(CURDATE(),created),0) as total_count", false);
        $this->db->from('tbl_finance_invoice_ndis_export_log');
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row()->total_count : 0;
    }

    function manual_fund_type_check($type = 1) {

        if ($type == 3) {
            $this->db->select("1", false);
        } else {
            $this->db->select("GROUP_concat(distinct(ft.name)) as name", false);
        }
        $this->db->from('tbl_finance_manual_invoice_line_items fmili');
        $this->db->join('tbl_funding_type as ft', 'ft.id= fmili.funding_type and fmili.archive=0');
        $this->db->where("fi.id=fmili.invoice_id", NULL, FALSE);
        if ($type == 3) {
            $this->db->where("ft.name", "ndis");
            $this->db->limit(1);
        }
        $query = $this->db->get_compiled_select();
        return $query;
    }

    function get_ndis_invoice_rejected_monthwise_financial_year() {
        $fundTypeSubquery = $this->get_invoic_fund_type_sub_query(3);
        $manualFundTypeSubquery = $this->manual_fund_type_check(3);
        $dataYear = get_current_finacial_year_data();
        $fromDate = $dataYear[0]['from_date'];
        $toDate = $dataYear[0]['to_date'];
        $this->db->select(["CAST(SUM(CASE
            WHEN invoice_type=1 AND COALESCE((" . $fundTypeSubquery . "),0)=1 THEN fi.total
            WHEN invoice_type=2 AND COALESCE((" . $manualFundTypeSubquery . "),0)=1 THEN fi.total
            ELSE '0' END ) as DECIMAL(10,2)) as total_amount", "DATE_FORMAT(fi.invoice_finalised_date,'%Y-%m') as data_month"]);
        $this->db->from("tbl_finance_invoice as fi");
        $this->db->where(["fi.archive" => 0]);
        $this->db->where_in("fi.status", [2]);
        $this->db->where("DATE(fi.invoice_finalised_date) BETWEEN '" . $fromDate . "' AND '" . $toDate . "'", NULL, FALSE);
        $this->db->group_by("DATE_FORMAT(fi.invoice_finalised_date,'%Y-%m')");
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        $result = !empty($result) ? pos_index_change_array_data($result, 'data_month') : $result;
        $yearResult = get_interval_month_wise($fromDate, $toDate);
        $dataUpdate = [];
        if (!empty($yearResult) && !empty($result)) {
            foreach ($yearResult as $key => $val) {
                $dataUpdate[] = isset($result[Date('Y-m', strtotime($val['from_date']))]) ? $result[Date('Y-m', strtotime($val['from_date']))]['total_amount'] : 0;
            }
        } else {
            $dataUpdate = array_fill(0, 12, 0);
        }
        return $dataUpdate;
    }

    function dashboard_statement_list($reqData, $extraParm = []) {
        $exportCall = $extraParm['exportCall'] ?? false;
        $creditNoteCall = $extraParm['creditNoteCall'] ?? false;
        $filter = $reqData->filtered ?? [];
        $sorted = $reqData->sorted ?? [];
        $orderBy = '';
        $direction = '';
        $shiftFundingSubQuery = $this->get_invoic_fund_type_sub_query(1);
        $statementForSubQuery = $this->common_for_user_query('fs', 2);
        $statementForQueryCase = $this->common_for_status_query('fs');
        $statementForGenderQueryCase = $this->common_for_status_gender_query('fs', 'statement_for');
        //$shiftFundingSubQuery = $this->get_invoic_fund_type_sub_query(1);
        if ($exportCall || $creditNoteCall) {
            
        } else {
            $limit = $reqData->pageSize;
            $page = $reqData->page;
        }

        $src_columns = array('statement_number', 'statement_for', 'address_to', 'total', 'statement_type', 'fund_type');

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
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $available_columns = array("id", "statement_number", "statement_notes", "last_issue_date", "status", "total");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $this->db->order_by('fs.created', 'DESC');
            $orderBy = 'fs.id';
            $direction = 'DESC';
        }

        if (isset($filter->start_date) && !empty($filter->start_date)) {
            $this->db->where("DATE(fs.created) >= '" . date('Y-m-d', strtotime($filter->start_date)) . "'");
        }
        if (isset($filter->end_date) && !empty($filter->end_date)) {
            $this->db->where("DATE(fs.created) <= '" . date('Y-m-d', strtotime($filter->end_date)) . "'");
        }

        $select_column = array("fs.id", "fs.statement_number", "fs.statement_notes", "DATE_FORMAT(fs.due_date, '%d/%m/%Y') as last_issue_date", "fs.status", "fs.total");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_statement as fs');
        $this->db->select("CASE WHEN fs.statement_type=1 THEN 'shift' ELSE 'manual' END AS statement_type", FALSE);

        // $this->db->select("CASE WHEN fs.booked_by=1 THEN (" . $shift_site_name . ") WHEN fs.booked_by=2 OR fs.booked_by=3 THEN (" . $shift_partcipant_name . ") ELSE '' END as statement_for", false);
        $this->db->select($statementForSubQuery . " as statement_for", false);
        $this->db->select($statementForQueryCase . " as statement_for_booked", false);
        $this->db->select($statementForGenderQueryCase . " as booked_gender", false);

        $this->db->select("CASE
            when fs.booked_by=2 OR fs.booked_by=3
            THEN (select concat_ws(' ',firstname,lastname) from tbl_participant_booking_list as sub_pbl inner join
            tbl_finance_statement_booker as sub_fsb on sub_fsb.bookerfor=sub_pbl.participantId and sub_fsb.archive=0 where sub_fsb.booked_by=fs.booked_by and
            sub_fsb.statementId=fs.id limit 1)

            when fs.booked_by=4 OR fs.booked_by=5 THEN (select concat_ws(' ',name) from tbl_organisation_all_contact as org_cont inner join
            tbl_finance_statement_booker as sub_fsb on sub_fsb.bookerfor=org_cont.id and sub_fsb.archive=0 where sub_fsb.booked_by=fs.booked_by and
            sub_fsb.statementId=fs.id limit 1)

            when fs.booked_by=1 THEN (select concat_ws(' ',firstname,lastname) from tbl_house_and_site_key_contact as site_cont inner join
            tbl_finance_statement_booker as sub_fsb on sub_fsb.bookerfor=site_cont.id and sub_fsb.archive=0 where sub_fsb.booked_by=fs.booked_by and
            sub_fsb.statementId=fs.id AND site_cont.user_type = 1 limit 1)

            when fs.booked_by=7 THEN (select concat_ws(' ',firstname,lastname) from tbl_house_and_site_key_contact as site_cont inner join
            tbl_finance_statement_booker as sub_fsb on sub_fsb.bookerfor=site_cont.id and sub_fsb.archive=0 where sub_fsb.booked_by=fs.booked_by and
            sub_fsb.statementId=fs.id AND site_cont.user_type = 2 limit 1)
            else '' END as address_to ");

        $this->db->select("CASE WHEN fi.invoice_type=1 THEN COALESCE((" . $shiftFundingSubQuery . "),'')
            WHEN fi.invoice_type=2 THEN (SELECT  GROUP_concat(distinct(ft.name)) FROM tbl_finance_manual_invoice_line_items fmili inner join tbl_funding_type as ft on ft.id= fmili.funding_type and fmili.archive=0 where fi.id=fmili.invoice_id)
            ELSE '' END fund_type");



        $this->db->order_by($orderBy, $direction);
        if (!$exportCall && !$creditNoteCall) {
            $this->db->limit($limit, ($page * $limit));
        }
        $this->db->join('tbl_finance_statement_attach as sa', 'sa.statement_id=fs.id', 'inner');
        $this->db->join('tbl_finance_invoice as fi', 'fi.id=sa.invoice_id', 'inner');
        $this->db->where(array('fs.archive' => 0));
        $this->db->group_by("fs.id");
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        #last_query(1);
        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $dataResult = $query->result();
        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

    public function get_statement_pdf($statementId) {
        $response = $this->get_statement_pdf_data($statementId);
        if (!empty($response->statement_file_path)) {
            $pdfFileFCpath = FCPATH . FINANCE_STATEMENT_FILE_PATH . $response->statement_file_path;
            if (!file_exists($pdfFileFCpath)) {

                $response = $this->create_statement_pdf_By_stid($statementId);
                return $response;
            }
        } else {

            return $this->create_statement_pdf_By_stid($statementId);
        }
        return ['status' => true, "data" => $response];
    }

    private function get_statement_pdf_data($statementId) {
        /*  $shift_partcipant_name = $this->get_invoice_participant_sub_query(2);
          $shift_site_name = $this->get_invoice_site_sub_query(2); */
        $statementForSubQuery = $this->common_for_user_query('fs', 2);

        $select_column = array("fs.statement_file_path", "fs.statement_number");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_statement as fs');
        $this->db->where("fs.id", $statementId);
        //$this->db->select("CASE WHEN fs.booked_by=1 THEN (" . $shift_site_name . ") WHEN fs.booked_by=2 OR fs.booked_by=3 THEN (" . $shift_partcipant_name . ") ELSE '' END as statement_for", false);
        $this->db->select($statementForSubQuery . " as statement_for", false);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->row();
    }

    public function create_statement_pdf_By_stid($statement_id) {
        if (!$statement_id > 0) {
            return ['status' => false, "error" => "statement id required"];
        }
        $select_column = array("fs.id", "fs.statement_number", "fs.from_date", "fs.to_date", "DATE_FORMAT(fs.issue_date, '%d/%m/%Y') as issue_date", "DATE_FORMAT(fs.due_date, '%d/%m/%Y') as due_date", "fs.statement_notes", "fs.statement_type", "fs.statement_for", "fs.booked_by", "fs.total");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->where("fs.id", $statement_id);
        $this->db->from('tbl_finance_statement as fs');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $statementResult = $query->result_array();

        if (!empty($statementResult)) {
            $statementResult = $statementResult[0];
            $select_column = array("fsa.id", "fsa.invoice_id", "fsa.total", "fsa.gst", "fsa.sub_total", "fi.invoice_id as invoice_number");
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->join('tbl_finance_invoice as fi', 'fi.id = fsa.invoice_id AND fi.archive = 0');
            $this->db->where("fsa.statement_id", $statement_id);
            $this->db->where("fsa.archive", 0);
            $this->db->from('tbl_finance_statement_attach as fsa');
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $attachResult = $query->result_array();
            $statementResult['attach'] = $attachResult;

            // Get user details
            $booked_by = isset($statementResult['booked_by']) ? $statementResult['booked_by'] : 0;
            $invoice_for = isset($statementResult['statement_for']) ? $statementResult['statement_for'] : 0;
            // user details start
            if ($booked_by == 1) {
                $statementResult['user_details'] = $this->Finance_common_model->get_site_details_by_id($invoice_for);
            } else if ($booked_by == 2 || $booked_by == 3) {
                $statementResult['user_details'] = $this->Finance_common_model->get_participant_details_by_id($invoice_for);
            } else if ($booked_by == 4 || $booked_by == 5) {
                $statementResult['user_details'] = $this->Finance_common_model->get_organisation_details_by_id($invoice_for);
            }
            $response = $this->create_statement_pdf($statementResult);
            $pdf_fileFCpath = FCPATH . FINANCE_STATEMENT_FILE_PATH . $response;

            if (file_exists($pdf_fileFCpath)) {
                $statementResult['statement_file_path'] = $response;
                $this->basic_model->update_records('finance_statement', array('statement_file_path' => $response), $where = array('id' => $statement_id));
                return ['status' => true, "data" => $statementResult];
            } else {
                return ['status' => false, "error" => "File not created"];
            }
        } else {
            return ['status' => false, "error" => "statement not found"];
        }
    }

    function get_send_statement_mail($statement_id) {
        if (!$statement_id > 0) {
            return ['status' => false, "error" => "statement id required"];
        }
        $select_column = array("fs.id", "fs.statement_number", "fs.from_date", "fs.to_date", "DATE_FORMAT(fs.issue_date, '%d/%m/%Y') as issue_date", "DATE_FORMAT(fs.due_date, '%d/%m/%Y') as due_date", "fs.statement_notes", "fs.statement_type", "fs.statement_for", "fs.booked_by", "fs.total", "fs.statement_file_path as file");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->where("fs.id", $statement_id);
        $this->db->from('tbl_finance_statement as fs');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $statementResult = $query->result_array();


        $mail_array = array();
        if (!empty($statementResult)) {
            $statementResult = $statementResult[0];
            $mail_array = $statementResult;
            // Get user details

            $booked_by = isset($statementResult['booked_by']) ? $statementResult['booked_by'] : 0;
            $invoice_for = isset($statementResult['statement_for']) ? $statementResult['statement_for'] : 0;
            $mail_array['booked_by'] = $booked_by;
            // user details start
            if ($booked_by == 1) {
                $mail_array['booker'] = $this->get_site_key_contact_email_by_id($invoice_for, 1);
                $mail_array['user_details'] = $this->Finance_common_model->get_site_details_by_id($invoice_for);
            } else if ($booked_by == 2 || $booked_by == 3) {
                $mail_array['booker'] = $this->get_participant_email_by_id($invoice_for);
                //last_query(1);

                $mail_array['user_details'] = $this->Finance_common_model->get_participant_details_by_id($invoice_for);
            } else if ($booked_by == 4 || $booked_by == 5) {
                $mail_array['booker'] = $this->get_organisation_contact_email_by_id($invoice_for);
                $mail_array['user_details'] = $this->Finance_common_model->get_organisation_details_by_id($invoice_for);
            } else if ($booked_by == 7) {
                $mail_array['booker'] = $this->get_site_key_contact_email_by_id($invoice_for, 2);
                $mail_array['user_details'] = $this->Finance_common_model->get_house_details_by_id($invoice_for);
            }
        }
        return $mail_array;
    }

    public function get_participant_email_by_id($patId) {
        $this->db->select(array("pbl.participantId as contactId", "pbl.email"), false);
        $this->db->from('tbl_participant_booking_list as pbl');
        $this->db->where('pbl.participantId', $patId);
        $this->db->where('pbl.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result;
    }

    public function get_organisation_contact_email_by_id($orgId) {
        $this->db->select(array("contact_email.contactId", "contact_email.email"), false);
        $this->db->from('tbl_organisation_all_contact_email as contact_email');
        $this->db->join('tbl_organisation_all_contact as contact', 'contact.id = contact_email.contactId AND contact.archive = 0 AND contact.type = 3');
        $this->db->where('contact.organisationId', $orgId);
        $where = array('contact_email.archive ' => 0, 'contact_email.primary_email' => 1);
        $this->db->where($where);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result;
    }

    public function get_site_key_contact_email_by_id($siteId, $type = 1) {
        $this->db->select(array("contact_email.contactId", "contact_email.email"), false);
        $this->db->from('tbl_house_and_site_key_contact_email as contact_email');
        $this->db->join('tbl_house_and_site_key_contact as contact', 'contact.id = contact_email.contactId AND contact.archive = 0 AND contact.type = 3 AND contact.user_type =' . $type);
        $where = array('contact_email.archive ' => 0, 'contact_email.primary_email' => 1, 'contact_email.user_type' => $type);
        $this->db->where($where);
        $this->db->where('contact.siteId', $siteId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        return $result;
    }

    public function get_invoice_org_sub_query($type = 1, $table_type = 1) {
        $query_res = array();
        $this->db->select("sub_o.name", false);
        $this->db->from('tbl_organisation as sub_o');
        if ($type == 1) {
            $this->db->where('sub_o.parent_org', 0);
        } else if ($type == 2) {
            $this->db->where('sub_o.parent_org !=', 0);
        }
        if ($table_type == 1) {
            $this->db->where('sub_o.id=fi.invoice_for AND fi.archive=0');
        } else if ($table_type == 2) {
            $this->db->where('sub_o.id=fs.statement_for AND fs.archive=0');
        } else if ($table_type == 3) {
            $this->db->where('sub_o.id=fcn.credit_note_for AND fcn.archive=0');
        }
        $this->db->limit(1);
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function get_invoice_data_with_amount($params = []) {
        $invoice_for = $params['invoice_for'] ?? 0;
        $booked_by = $params['booked_by'] ?? 0;
        $invoiceids = $params['ids'] ?? '';
        $invoiceids = !empty($invoiceids) && is_array($invoiceids) ? $invoiceids : (!empty($invoiceids) ? [$invoiceids] : '');
        $amountUsedFromSubquery = $this->get_invoice_credit_note_from_or_to_amount_by_invoice_ids_sub_query(1);
        $amountUsedToSubquery = $this->get_invoice_credit_note_from_or_to_amount_by_invoice_ids_sub_query(2);

        $this->db->select(['fi.id', 'fi.invoice_id as invoice_number', 'fi.total', 'fi.status']);
        $this->db->select([
            "COALESCE((" . $amountUsedFromSubquery . "),0) as amount_used_from",
            "COALESCE((" . $amountUsedToSubquery . "),0) as amount_used_to"
        ]);
        $this->db->from('tbl_finance_invoice fi');

        if (!empty($invoice_for)) {
            $this->db->where('invoice_for', $invoice_for);
        }
        if (!empty($booked_by)) {
            $this->db->where('booked_by', $booked_by);
        }
        if (!empty($invoiceids)) {
            $this->db->where_in('id', $invoiceids);
        }
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_invoice_credit_note_from_or_to_amount_by_invoice_ids_sub_query($type) {
        $this->db->select(["CAST(SUM(CASE WHEN sub_fcnia.attached_type=" . $type . " THEN sub_fcnia.amount ELSE '0' END) as DECIMAL(10,2))"]);
        $this->db->from("tbl_finance_credit_note_invoice_attached sub_fcnia");
        $this->db->where(["sub_fcnia.archive" => 0]);
        $this->db->where("fi.id=sub_fcnia.invoice_id", NULL, FALSE);
        $query = $this->db->get_compiled_select();
        return $query;
    }

    public function save_credit_notes($reqData) {
        $fromAmountData = $reqData->from_select_invoice_apply;
        $toAmountData = $reqData->to_select_invoice_apply;
        $credit_note_for = $reqData->invoice_for;
        $booked_by = $reqData->booked_by;
        $totalFromAmount = array_column($fromAmountData, 'amount');
        $totalFromAmount = array_sum($totalFromAmount);
        $total_amount = $totalFromAmount;
        $applyInvoiceIdData = [];
        $insData = [
            'booked_by' => $booked_by,
            'credit_note_for' => $credit_note_for,
            'total_amount' => $total_amount,
            'credit_note_number' => null,
            'created' => DATE_TIME,
            'status' => 1,
            'archive' => 0
        ];
        $credit_note_id = $this->basic_model->insert_records('finance_credit_note', $insData);
        if ($credit_note_id) {
            $insRecord = [];
            foreach ($fromAmountData as $key => $row) {
                $temp = [
                    'description' => $row->desc,
                    'amount' => $row->amount,
                    'invoice_id' => $row->invoice_id,
                    'credit_note_id' => $credit_note_id,
                    'created' => DATE_TIME,
                    'attached_type' => 1,
                    'archive' => 0
                ];
                $insRecord[] = $temp;
            }

            foreach ($toAmountData as $key => $row) {
                $applyInvoiceIdData[] = ['id' => $row->invoice_id_selected, 'invoice_file_path' => null];
                $temp = [
                    'description' => null,
                    'amount' => $row->invoice_apply_amount,
                    'invoice_id' => $row->invoice_id_selected,
                    'credit_note_id' => $credit_note_id,
                    'created' => DATE_TIME,
                    'attached_type' => 2,
                    'archive' => 0
                ];
                $insRecord[] = $temp;
            }

            if (!empty($insRecord)) {
                $this->basic_model->insert_update_batch('insert', 'finance_credit_note_invoice_attached', $insRecord);
                if (!empty($applyInvoiceIdData)) {
                    $this->basic_model->insert_update_batch('update', 'finance_invoice', $applyInvoiceIdData, 'id');
                }
            }

            if ($booked_by == 3 || $booked_by == 2) {
                $this->load->model('Finance_ammount_calculation_model');
                $res = $this->Finance_ammount_calculation_model->get_user_other_type_current_plan_line_item_id_by_user_id($credit_note_for, ['user_type' => $booked_by]);
                if (!empty($res) && isset($res['id'])) {
                    require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';
                    $objTran = new LineItemTransactionHistory();
                    $objTran->setLine_item_fund_used($total_amount); //include gst
                    $objTran->setUser_plan_line_items_id($res['id']);
                    $objTran->setLine_item_fund_used_type(5);
                    $objTran->setLine_item_use_id($credit_note_id); //item auto id
                    $objTran->setStatus(3);
                    $objTran->setArchive(0);
                    $objTran->setCreated(DATE_TIME);
                    $objTran->create_history();
                    $this->basic_model->update_records('user_plan_line_items', ['total_funding' => ($res['total_funding'] + $total_amount)], ['id' => $res['id']]);
                    $objTran->update_fund_blocked();
                }
            }
            return ['status' => true, 'msg' => 'Credit note Created successfully.', 'credit_note_id' => $credit_note_id];
        } else {
            return ['status' => false, 'error' => 'Something went wrong. please try again.'];
        }
    }

    function get_outstanding_statements_participants_count($view_type = 'week') {

        $select_column = array("count(DISTINCT(fsa.invoice_id)) unpaid_participant");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_statement as fs');
        $this->db->where_in("fs.booked_by", [2, 3]);
        $this->db->join('tbl_finance_statement_attach as fsa', 'fsa.statement_id = fs.id AND fs.archive = 0 AND fsa.archive = 0');
        $this->db->join('tbl_finance_invoice as f', 'f.id = fsa.invoice_id AND f.archive = 0 AND f.status = 0');
        if ($view_type == 'year') {
            $where['YEAR(f.created)'] = date('Y');
            $this->db->where($where);
        } else if ($view_type == 'week') {
            $where = 'f.created BETWEEN (DATE_SUB(NOW(), INTERVAL 1 WEEK)) AND NOW()';
            $this->db->where($where);
        } else {
            $where['MONTH(f.created)'] = date('m');
            $this->db->where($where);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return $query->row();
    }

    function get_outstanding_statements_organization_count($view_type = 'week') {

        $select_column = array("count(DISTINCT(fsa.invoice_id)) unpaid_org");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_finance_statement as fs');
        $this->db->where_in("fs.booked_by", [4, 5]);
        $this->db->join('tbl_finance_statement_attach as fsa', 'fsa.statement_id = fs.id AND fs.archive = 0 AND fsa.archive = 0');
        $this->db->join('tbl_finance_invoice as f', 'f.id = fsa.invoice_id AND f.archive = 0 AND f.status = 0');
        if ($view_type == 'year') {
            $where['YEAR(f.created)'] = date('Y');
            $this->db->where($where);
        } else if ($view_type == 'week') {
            $where = 'f.created BETWEEN (DATE_SUB(NOW(), INTERVAL 1 WEEK)) AND NOW()';
            $this->db->where($where);
        } else {
            $where['MONTH(f.created)'] = date('m');
            $this->db->where($where);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->row();
    }

    function get_credit_notes_list($reqData) {
        $creditGenderQueryCase = $this->common_for_status_gender_query("fcn", "credit_note_for");
        $creditForQueryCase = $this->common_for_status_query('fcn');
        $creditQueryFor = $this->credit_note_for_user_query();
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array('credit_note_number', 'credit_note_for', 'total_amount', 'created_date', 'credit_note_status');
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
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $available_columns = array("credit_note_number","total_amount", "created_date", "credit_note_status","booked_from","booked_gender","id" );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fcn.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_by)) {
            
        }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcn.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcn.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fcn.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcn.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }
        $select_column = array(
            "fcn.credit_note_number",
            "fcn.total_amount",
            "DATE_FORMAT(fcn.created,'%d/%m/%Y') as created_date",
            "CASE WHEN fcn.status=1 THEN 'used' ELSE '' END as credit_note_status",
            $creditForQueryCase . " as booked_from",
            $creditGenderQueryCase . " as booked_gender",
            "fcn.id"
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select($creditQueryFor . " as credit_note_for", false);
        $this->db->from('tbl_finance_credit_note  as fcn');
        $this->db->where('fcn.archive', 0);
        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));
        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

    private function credit_note_for_user_query() {
        $shift_partcipant_name = $this->get_invoice_participant_sub_query(3);
        $shift_site_name = $this->get_invoice_site_sub_query(3);
        $shift_house_name = $this->get_invoice_house_sub_query(3);
        $org_name = $this->get_invoice_org_sub_query(1, 3);
        $sub_org_name = $this->get_invoice_org_sub_query(2, 3);
        return "CASE WHEN fcn.booked_by=1 THEN (" . $shift_site_name . ") WHEN fcn.booked_by=2 OR fcn.booked_by=3 THEN (" . $shift_partcipant_name . ") WHEN fcn.booked_by=4 THEN (" . $org_name . ") WHEN fcn.booked_by=5 THEN (" . $sub_org_name . ") WHEN fcn.booked_by=7 THEN (" . $shift_house_name . ") ELSE '' END ";
    }

    function insert_statment_bookers($booker) {


        $statement_id = $booker['id'];
        $booked_by = $booker['booked_by'];

        $statement_booker = [];
        //bookerfor
        if (!empty($booker['booker'])) {

            foreach ($booker['booker'] as $booker_data) {

                $bookerFor = $booker_data['contactId'];
                $statement_booker[] = array('statementId' => $statement_id, 'booked_by' => $booked_by, 'bookerfor' => $bookerFor, 'archive' => 0, 'created' => DATE_TIME);
            }

            if (!empty($statement_booker)) {
                $this->db->insert_batch('tbl_finance_statement_booker', $statement_booker);
            }
        }
    }

    private function get_invoice_details_sub_query_by_credit_note_invoice_attached($type = 1) {
        if ($type == 1) {
            $this->db->select(["CAST( sub_fi.total as DECIMAL(10,2))"]);
        } else if ($type == 2) {
            $this->db->select(["sub_fi.invoice_id"]);
        }
        $this->db->from("tbl_finance_invoice sub_fi");
        $this->db->where(["sub_fi.archive" => 0]);
        $this->db->where("fcnia.invoice_id=sub_fi.id AND fcnia.archive=sub_fi.archive", NULL, FALSE);
        $query = $this->db->get_compiled_select();
        return $query;
    }

    function get_refund_list($reqData) {
        $creditGenderQueryCase = $this->common_for_status_gender_query("fcn", "credit_note_for");
        $creditForQueryCase = $this->common_for_status_query('fcn');
        $creditQueryFor = $this->credit_note_for_user_query();
        $invoiceAmountQueryFor = $this->get_invoice_details_sub_query_by_credit_note_invoice_attached(1);
        $invoice_numberQueryFor = $this->get_invoice_details_sub_query_by_credit_note_invoice_attached(2);
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array('refund_number', 'refund_amount', 'refund_created_date', 'refund_for', 'invoice_number', 'invoice_amount');
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
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
        $available_columns = array("refund_number","refund_amount", "refund_created_date","booked_from","booked_gender", "refund_for", "invoice_number", "invoice_amount" );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_columns)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fcnia.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_by)) {
            
        }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcnia.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcnia.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fcnia.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fcnia.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }
        $select_column = array(
            "fcnia.refund_number",
            "fcnia.amount as refund_amount",
            "DATE_FORMAT(fcnia.created,'%d/%m/%Y') as refund_created_date",
            $creditForQueryCase . " as booked_from",
            $creditGenderQueryCase . " as booked_gender"
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select($creditQueryFor . " as refund_for", false);
        $this->db->select("(" . $invoice_numberQueryFor . ") as invoice_number", false);
        $this->db->select("(" . $invoiceAmountQueryFor . ") as invoice_amount", false);
        $this->db->from('tbl_finance_credit_note_invoice_attached  as fcnia');
        $this->db->join("tbl_finance_credit_note as fcn", "fcn.id=fcnia.credit_note_id AND fcn.archive=fcnia.archive AND fcnia.attached_type=2", "inner");
        $this->db->where(['fcnia.archive' => 0]);
        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));
        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

    function get_credit_note_view($creditNoteId) {
        //$creditNoteId =1;
        $invoiceStatusCaseQuery = $this->inovice_status_query();
        $shiftFundingSubQuery = $this->get_invoic_fund_type_sub_query(1);

        $select_column = array(
            "fi.id", "fi.total as amount", "fi.invoice_date", "fi.invoice_id as invoice_number", "DATE_FORMAT(fi.invoice_date,'%d/%m/%Y') as invoice_date",
            "DATE_FORMAT(fi.pay_by,'%d/%m/%Y') as pay_by", 'fi.line_item_notes', 'fi.manual_invoice_notes', 'fi.status', 'CASE WHEN fi.invoice_type=1 THEN "Shift" WHEN fi.invoice_type=2 THEN "Manual" ELSE "" END as description',
            "CASE WHEN fi.invoice_type=1 THEN COALESCE((" . $shiftFundingSubQuery . "),'')
            WHEN fi.invoice_type=2 THEN (SELECT  GROUP_concat(distinct(ft.name)) FROM tbl_finance_manual_invoice_line_items fmili inner join tbl_funding_type as ft on ft.id= fmili.funding_type and fmili.archive=0 where fi.id=fmili.invoice_id)
            ELSE '' END fund_type",
            "fcnia.description as item_desc",
            "fcnia.amount as item_amount",
            $invoiceStatusCaseQuery . " as invoice_status"
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from('tbl_finance_credit_note_invoice_attached  as fcnia');
        $this->db->join("tbl_finance_invoice as fi", "fi.id=fcnia.invoice_id AND fi.archive=fcnia.archive AND fcnia.attached_type=1", "inner");
        $this->db->where(['fcnia.credit_note_id' => $creditNoteId]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $result = $query->result();

        $select_column_apply = array(
            "fi.invoice_id as apply_invoice_number",
            "fcnia.amount as apply_item_amount",
            "fi.total as invoice_amount",
            "fcnia.refund_number as apply_refund_number"
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column_apply)), false);
        $this->db->from('tbl_finance_credit_note_invoice_attached  as fcnia');
        $this->db->join("tbl_finance_invoice as fi", "fi.id=fcnia.invoice_id AND fi.archive=fcnia.archive AND fcnia.attached_type=2", "inner");
        $this->db->where(['fcnia.credit_note_id' => $creditNoteId]);

        $query_apply = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total_apply = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $result_apply = $query_apply->result();

        $get_credit_details_Data = $this->basic_model->get_row('finance_credit_note', ['credit_note_number'], ['id' => $creditNoteId]);

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'data_apply' => $result_apply, 'credit_details_data' => $get_credit_details_Data);
        return $return;
    }

    private function get_credit_note_number_sub_query() {
        $this->db->select("sub_fcn.credit_note_number");
        $this->db->from("tbl_finance_credit_note sub_fcn");
        $this->db->where("sub_fcn.id=fcnia.credit_note_id", NULL, FALSE);
        return $this->db->get_compiled_select();
    }

    /* private function get_invoice_participaint_xero_contact_details_id(){
      $this->db->select("sub_pxcm.xero_contact_id");
      $this->db->from('tbl_participant_xero_contact_mapping sub_pxcm');
      $this->db->where('sub_pxcm.participant_id=fi.invoice_for and sub_pxcm.archive=0',NULL,FALSE);
      return $this->db->get_compiled_select();

      }
      private function get_invoice_site_xero_contact_details_id(){
      $this->db->select("sub_osxcm.xero_contact_id");
      $this->db->from('tbl_organisation_site_xero_contact_mapping sub_osxcm');
      $this->db->where('sub_osxcm.site_id=fi.invoice_for and sub_osxcm.archive=0',NULL,FALSE);
      return $this->db->get_compiled_select();

      } */

    private function invoice_xero_contact_details() {
        $this->db->select("sub_xcmfi.xero_contact_id");
        $this->db->from('tbl_xero_contact_mapping_for_finance_invoice sub_xcmfi');
        $this->db->where('sub_xcmfi.xero_for=fi.invoice_for and sub_xcmfi.booked_by=fi.booked_by and sub_xcmfi.archive=0', NULL, FALSE);
        $queryRes = $this->db->get_compiled_select();
        return " COALESCE((" . $queryRes . "),'') ";
    }

    private function get_shfit_or_manual_invoice_details_for_xero_by_invoice_id($invoice_Id) {

        $xeroContactIdSubQuery = $this->invoice_xero_contact_details();
        $this->db->select(['fi.invoice_id as invoice_number', 'fi.id', 'fi.invoice_type', 'fi.status', 'fi.booked_by', 'fi.invoice_for', 'fi.pay_by', 'fi.invoice_date', 'fi.xero_invoice_id']);
        $this->db->select($xeroContactIdSubQuery . ' as xero_contact_id');
        $this->db->from('tbl_finance_invoice fi');
        $this->db->where(['id' => $invoice_Id, 'archive' => 0]);
        $qurey = $this->db->get();
        if ($qurey->num_rows() > 0) {
            $res = $qurey->row_array();
            $invoice_line_item = [];
            if ($res['invoice_type'] == 1) {
                $invoice_line_item = $this->get_invoice_items_by_invoice_id($invoice_Id, 'xero');
            } else if ($res['invoice_type'] == 2) {
                $invoice_line_item = $this->get_manual_invoice_items_by_invoice_id($invoice_Id, 'xero');
            }
            $res['invoice_item'] = $invoice_line_item;
            return $res;
        } else {
            return [];
        }
    }

    public function invoice_send_to_xero_by_invoice_id($invoice_Id, $extraParm = []) {
        $invoiceDetails = $this->get_shfit_or_manual_invoice_details_for_xero_by_invoice_id($invoice_Id);
        if (empty($invoiceDetails)) {
            return ['status' => false, 'error' => 'invoice details not found'];
        }
        $emailSent = $extraParm['email_sent'] ?? false;
        $lineItems = [];
        $total_amount = 0;
        $sub_total = 0;
        $gst_total = 0;
        if (!empty($invoiceDetails['invoice_item'])) {
            foreach ($invoiceDetails['invoice_item'] as $key => $row) {
                $lineItems[] = [
                    'Description' => character_limiter($row['item_name'] . '-' . $row['item_description'], 4000),
                    'Quantity' => floatval($row['quantity']),
                    'UnitAmount' => floatval($row['cost']),
                    'DiscountRate' => 0,
                    'AccountCode' => XERO_DEFAULT_LINE_ITEM_ACCOUNT_CODE
                ];
                if ($invoiceDetails['invoice_type'] == '1') {
                    $temp_total = (floatval($row['quantity']) * floatval($row['cost']));
                    $sub_total += floatval($temp_total);
                    $gst_total += floatval($row['gst']);
                }
            }
            if ($invoiceDetails['invoice_type'] == '1') {
                //$gst_total = calculate_gst($sub_total);
                $total_amount = custom_round($gst_total + $sub_total);
            }
        }
        if ($invoiceDetails['invoice_type'] == '1') {
            $this->basic_model->update_records('finance_invoice', ['total' => $total_amount, 'gst' => $gst_total, 'sub_total' => $sub_total], ['id' => $invoice_Id]);
        }
        $invoiceNumber = $invoiceDetails['invoice_number'] ?? $invoice_Id;

        /* $getContactTbl = $invoiceDetails['booked_by'] == '1' ? 'organisation_site' : (($invoiceDetails['booked_by'] == '2' || $invoiceDetails['booked_by'] == '3') ? 'participant' : '');
          $getColumn = $getContactTbl == 'organisation_site' ? 'site_id' : ($getContactTbl == 'participant' ? 'participant_id' : '');
          $tableXeroContact = [
          'organisation_site' => 'organisation_site_xero_contact_mapping',
          'participant' => 'participant_xero_contact_mapping'
          ]; */

        $companyId = XERO_DEFAULT_COMPANY_ID;
        $params = array('company_id' => $companyId);
        $this->load->library('XeroContact', $params);
        $this->load->library('XeroInvoice', $params);

        if (empty($invoiceDetails['xero_contact_id'])) {
            $contactStatus = $this->create_xero_contact_details_for_invoice($invoiceDetails);
            if (!$contactStatus['status'] || !isset($contactStatus['xero_contact_id'])) {
                return $contactStatus;
            }
            $xeroContactId = $contactStatus['xero_contact_id'];
            /* $contactDetails = [];
              $xeroContactId = '';
              $insDataContact = [];
              $insData = [];
              if ($invoiceDetails['booked_by'] == '1') {
              $siteDetails = $this->Finance_common_model->get_site_details_by_id($invoiceDetails['invoice_for']);
              $insData = ['Name' => $siteDetails['name'] . '(site-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $siteDetails['email']];
              /*
              $insDataContact = [
              'site_id' => $invoiceDetails['invoice_for'],
              'archive' => 0,
              'created' => DATE_TIME
              ]; * /
              } else if ($invoiceDetails['booked_by'] == '2' || $invoiceDetails['booked_by'] == '3') {
              $participantDetails = $this->Finance_common_model->get_participant_details_by_id($invoiceDetails['invoice_for']);
              $insData = ['FirstName' => $participantDetails['firstname'], 'LastName' => $participantDetails['lastname'], 'Name' => $participantDetails['name'] . '(participant-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $participantDetails['email']];
              /*
              $contactDetails = $this->xerocontact->create_contact($companyId, $insData);
              $insDataContact = [
              'participant_id' => $invoiceDetails['invoice_for'],
              'archive' => 0,
              'created' => DATE_TIME
              ]; * /
              } else if($invoiceDetails['booked_by'] == '4' || $invoiceDetails['booked_by'] == '5'){
              $orgDetails = $this->Finance_common_model->get_organisation_details_by_id($invoiceDetails['invoice_for']);
              $insData = [ 'Name' => $orgDetails['name'] . '('.(!empty($orgDetails['parent_org'])? 'Sub ':'').'Organisation-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $orgDetails['email']];

              } else if($invoiceDetails['booked_by'] == '7'){
              $houseDetails = $this->Finance_common_model->get_house_details_by_id($invoiceDetails['invoice_for']);
              $insData = [ 'Name' => $houseDetails['name'] . '(House-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $houseDetails['email']];
              }

              if(empty($insData)){
              return ['status' => false, 'error' => 'invoice not created on xero because contact implemention not implemented for that'];
              }

              $contactDetails = $this->xerocontact->create_contact($companyId, $insData);
              $insDataContact = [
              'xero_for' => $invoiceDetails['invoice_for'],
              'booked_by' => $invoiceDetails['booked_by'],
              'archive' => 0,
              'created' => DATE_TIME
              ];
              if (!empty($contactDetails) && isset($contactDetails['status']) && $contactDetails['status']) {
              $xeroContactId = $contactDetails['data'][0]['ContactID'];
              if (!empty($insDataContact)) {
              $insDataContact['xero_contact_id'] = $xeroContactId;
              $this->basic_model->insert_records('xero_contact_mapping_for_finance_invoice', $insDataContact);
              }
              } else {
              $resStatus = true;
              if (!empty($contactDetails) && isset($contactDetails['status'])) {
              $res = $this->xerocontact->get_contact_list($companyId,['Name'=>$insData['Name'],'ContactStatus'=>'ACTIVE']);
              if($res['status'] && isset($res['data'][0]['ContactID'])){
              $resStatus = false;
              $xeroContactId =$res['data'][0]['ContactID'];
              $insDataContact['xero_contact_id'] = $xeroContactId;
              $this->basic_model->insert_records('xero_contact_mapping_for_finance_invoice', $insDataContact);
              }
              }
              if($resStatus){
              return ['status' => false, 'error' => 'invoice not created on xero because conatct not create on xero.','$contactDetails'=>$contactDetails];
              }
              } */
        } else {
            $xeroContactId = $invoiceDetails['xero_contact_id'];
        }

        if (empty($lineItems)) {
            $lineItems [] = [
                'Description' => 'default line item',
                'Quantity' => 1,
                'UnitAmount' => 0,
                'DiscountRate' => 0,
                'AccountCode' => XERO_DEFAULT_LINE_ITEM_ACCOUNT_CODE
            ];
        }
        $insData = [
            'Type' => 'ACCREC',
            'ContactID' => $xeroContactId,
            'LineItems' => $lineItems,
            'Date' => $invoiceDetails['invoice_date'],
            'DueDate' => $invoiceDetails['pay_by'],
            'LineAmountTypes' => 'Exclusive', // get option list from get_invoice_lineamount_list function
            'Reference' => $invoiceNumber, //ACCREC type only,
            'InvoiceNumber' => 'INV' . $invoiceNumber, //ACCREC type only,
            'Url' => base_url(), //URL link to a source document - shown as "Go to [appName]" in the Xero app,
            'CurrencyCode' => 'AUD', //get CurrencyCode form get_currency_code function,
            'Status' => 'DRAFT', //get CurrencyCode form get_currency_code function,
            'SentToContact' => 0, //Boolean to set whether the invoice in the Xero app should be marked as "sent". This can be set only on invoices that have been approved
            'ExpectedPaymentDate' => $invoiceDetails['pay_by'], //Shown on sales invoices (Accounts Receivable) when this has been set
            'PlannedPaymentDate' => $invoiceDetails['pay_by'], //Shown on bills (Accounts Payable) when this has been set
        ];
        $invoiceDetailsXero = $this->xeroinvoice->create_inovice($companyId, $insData);
        if ($invoiceDetailsXero['status'] && !empty($invoiceDetailsXero['data'])) {
            $invoiceIdXero = $invoiceDetailsXero['data'][0]['InvoiceID'];
            $this->basic_model->update_records('finance_invoice', ['xero_invoice_id' => $invoiceIdXero], ['id' => $invoice_Id]);
            $resEmail = 'ds';
            if ($emailSent) {
                $resEmail = $this->resend_invoice_mail($invoice_Id);
            }
            return ['status' => true, 'msg' => 'Shift auto invoice creation successfully.'];
        } else {
            return ['status' => false, 'error' => 'invoice not created on xero.', '$invoiceDetailsXero' => $invoiceDetailsXero, '$insData' => $insData];
        }
    }

    private function create_xero_contact_details_for_invoice($invoiceDetails = [/* 'booked_by'=>0,'invoice_for'=>0 */]) {
        $companyId = XERO_DEFAULT_COMPANY_ID;
        $params = array('company_id' => $companyId);
        $this->load->library('XeroContact', $params);
        $contactDetails = [];
        $xeroContactId = '';
        $insDataContact = [];
        $insData = [];
        $xeroContactId = '';
        $xeroStatus = false;
        if ($invoiceDetails['booked_by'] == '1') {
            $siteDetails = $this->Finance_common_model->get_site_details_by_id($invoiceDetails['invoice_for']);
            $insData = ['Name' => $siteDetails['name'] . '(site-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $siteDetails['email']];
            /*
              $insDataContact = [
              'site_id' => $invoiceDetails['invoice_for'],
              'archive' => 0,
              'created' => DATE_TIME
              ]; */
        } else if ($invoiceDetails['booked_by'] == '2' || $invoiceDetails['booked_by'] == '3') {
            $participantDetails = $this->Finance_common_model->get_participant_details_by_id($invoiceDetails['invoice_for']);
            $insData = ['FirstName' => $participantDetails['firstname'], 'LastName' => $participantDetails['lastname'], 'Name' => $participantDetails['name'] . '(participant-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $participantDetails['email']];
            /*
              $contactDetails = $this->xerocontact->create_contact($companyId, $insData);
              $insDataContact = [
              'participant_id' => $invoiceDetails['invoice_for'],
              'archive' => 0,
              'created' => DATE_TIME
              ]; */
        } else if ($invoiceDetails['booked_by'] == '4' || $invoiceDetails['booked_by'] == '5') {
            $orgDetails = $this->Finance_common_model->get_organisation_details_by_id($invoiceDetails['invoice_for']);
            $insData = ['Name' => $orgDetails['name'] . '(' . (!empty($orgDetails['parent_org']) ? 'Sub ' : '') . 'Organisation-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $orgDetails['email']];
        } else if ($invoiceDetails['booked_by'] == '7') {
            $houseDetails = $this->Finance_common_model->get_house_details_by_id($invoiceDetails['invoice_for']);
            $insData = ['Name' => $houseDetails['name'] . '(House-' . $invoiceDetails['invoice_for'] . ')', 'EmailAddress' => $houseDetails['email']];
        }

        if (empty($insData)) {
            return ['status' => false, 'error' => 'invoice not created on xero because contact implemention not implemented for that'];
        }

        $contactDetails = $this->xerocontact->create_contact($companyId, $insData);
        $insDataContact = [
            'xero_for' => $invoiceDetails['invoice_for'],
            'booked_by' => $invoiceDetails['booked_by'],
            'archive' => 0,
            'created' => DATE_TIME
        ];
        if (!empty($contactDetails) && isset($contactDetails['status']) && $contactDetails['status']) {
            $xeroContactId = $contactDetails['data'][0]['ContactID'];
            $xeroStatus = true;
            if (!empty($insDataContact)) {
                $insDataContact['xero_contact_id'] = $xeroContactId;
                $this->basic_model->insert_records('xero_contact_mapping_for_finance_invoice', $insDataContact);
            }
        } else {

            if (!empty($contactDetails) && isset($contactDetails['status'])) {
                $res = $this->xerocontact->get_contact_list($companyId, ['Name' => $insData['Name'], 'ContactStatus' => 'ACTIVE']);
                if ($res['status'] && isset($res['data'][0]['ContactID'])) {
                    $xeroStatus = true;
                    $xeroContactId = $res['data'][0]['ContactID'];
                    $insDataContact['xero_contact_id'] = $xeroContactId;
                    $this->basic_model->insert_records('xero_contact_mapping_for_finance_invoice', $insDataContact);
                }
            }
        }
        $xeroResponse = ['status' => $xeroStatus];
        if (!$xeroStatus) {
            $xeroResponse['error'] = 'invoice not created on xero because conatct not create on xero.';
        } else {
            $xeroResponse['msg'] = 'Xero contact created successfully.';
            $xeroResponse['xero_contact_id'] = $xeroContactId;
        }
        return $xeroResponse;
    }

    public function invoice_update_status($xero_invoice_id, $toStatus) {
        $companyId = XERO_DEFAULT_COMPANY_ID;
        $params = array('company_id' => $companyId);
        $this->load->library('XeroInvoice', $params);

        $vaildStatus = json_decode(INVOICE_PAYMENT_STATUS_DATA);
        $checkStatus = !empty($toStatus) && array_key_exists($toStatus, $vaildStatus) ? true : false; // 0 key not allowed valid to change invoice status
        if (!$checkStatus) {
            return ['status' => false, 'error' => 'invoice status not valid'];
        }

        $paramsData = ['InvoiceID' => $xero_invoice_id];
        $res = $this->xeroinvoice->invoice_status_mark_as_Authorised($companyId, $paramsData);

        if ($toStatus == 2 && $res['status']) {
            $resVoided = $this->xeroinvoice->invoice_status_mark_as_voided($companyId, $paramsData);
        } else if ($toStatus == 1 && $res['status']) {
            $resPaid = $this->xeroinvoice->invoice_status_mark_as_paid($companyId, $paramsData);
        }


        if (($toStatus == 2 && $res['status'] && isset($resVoided['status']) && $resVoided['status']) || ($toStatus != 2 && $res['status'])) {
            return ['status' => true, 'msg' => 'status update successfully.', '$res' => $res, '$paramsData' => $paramsData];
        } else {
            return ['status' => false, 'error' => 'xero status not updated.', '$res' => $res, '$paramsData' => $paramsData];
        }
    }

    public function get_pending_invoice_create($limit = 1) {
        $this->db->select(['id']);
        $this->db->from('tbl_finance_invoice');
        $this->db->where(['status' => 0, 'archive' => 0]);
        $this->db->group_start();
        $this->db->where('xero_invoice_id', '');
        $this->db->or_where("xero_invoice_id IS NULL", NULL, FALSE);
        $this->db->group_end();
        $this->db->limit($limit);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_finance_invoice_status_update($limit = 1) {
        $this->db->select(['id', 'invoice_id', 'invoice_data', 'log_status']);
        $this->db->from('tbl_finance_invoice_import_status_update_log');
        $this->db->where(['log_status' => 0, 'archive' => 0]);
        $this->db->limit($limit);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    function get_shift_line_items_by_shift_id($shiftId) {
        $this->db->select(['slia.cost', 'slia.quantity', 'slia.plan_line_itemId', 'slia.line_item', 'slia.sub_total', 'slia.gst', 'slia.total']);
        $this->db->select("(select sub_fli.line_item_name from tbl_finance_line_item as sub_fli where sub_fli.id = slia.line_item) as item_name");
        $this->db->from('tbl_shift_line_item_attached slia');
        $this->db->where('slia.archive', 0);
        $this->db->where('slia.shiftId', $shiftId);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function invoice_status_update_credit_notes_amount_apply($limit = 1) {
        $this->db->select(["CAST(SUM(sub_fcnia.amount) as DECIMAL(10,2))"]);
        $this->db->from("tbl_finance_credit_note_invoice_attached sub_fcnia");
        $this->db->where("sub_fcnia.attached_type=2 AND sub_fcnia.archive=0 AND invoice_id=fi.id", NULL, FALSE);
        $subQuery = $this->db->get_compiled_select();

        $this->db->select(["fi.id", "fi.invoice_id", "fi.xero_invoice_id",
            "CAST(fi.total as DECIMAL(10,2)) as total",
            "COALESCE((" . $subQuery . "),0) as credit_apply"]);
        $this->db->from('tbl_finance_invoice fi');
        $this->db->where(['fi.status' => 0, 'fi.archive' => 0]);
        $this->db->where("fi.xero_invoice_id IS NOT NULL AND fi.xero_invoice_id!=''", NULL, FALSE);
        $this->db->limit($limit);
        $this->db->having("total > 0 AND CAST((total-credit_apply) as DECIMAL(10,2))='0.00'");
        $query = $this->db->get();
        return $query->result_array();
    }

    //for plan mgmt other type using
    public function get_user_other_type_current_plan_line_item_id_by_participant_id($userId = 0, $extratParm = []) {
        $userType = $extratParm['user_type'] ?? 0;
        $userType = !empty($userType) ? $userType : 0;
        $userType = is_array($userType) ? $userType : [$userType];
        $this->db->select(["upli.id", "upli.total_funding"]);
        $this->db->from('tbl_user_plan up', 'tbl_finance_support_category fsc');
        $this->db->join("tbl_user_plan_line_items upli", "upli.user_planId =up.id AND upli.archive=up.archive");
        //$this->db->join("tbl_finance_line_item fli","upli.line_itemId=fli.id");
        $this->db->join("tbl_finance_support_category fsc", "fsc.key_name='other_type'");
        $this->db->where_in("up.user_type", $userType);
        $this->db->where("up.archive", 0);
        $this->db->where("up.userId", $userId);
        $this->db->where("'" . DATE_CURRENT . "' between up.start_date and up.end_date", NULL, FALSE);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    public function shift_details_send_to_keyPay_by_shift_details($shiftId = 0) {
        $shiftDetails = $this->get_shift_details_by_shift_id($shiftId, ['for_keypay' => true]);
        if (empty($shiftDetails)) {
            return ['status' => false, 'error' => 'Shift not completed or already keypay created yet.'];
        }
        $memberId = $shiftDetails['member_id'] ?? 0;
        if (!empty($memberId)) {
            $params = array('company_id' => KEYPAY_DEFAULT_COMPANY_ID);
            $this->load->library('KeyPayFetch', $params);
            $res = $this->keypayfetch->check_kiosk_exists_and_create();
            if ($res['status']) {
                $authId = $res['data']['auth_details']['id'];
                $memberDetails = $this->get_member_key_pay_details_by_member_id($memberId, $res['data']['auth_details']['id']);
                if (!empty($memberDetails) && isset($memberDetails['keypay_emp_id'])) {
                    $keypayEmpId = $memberDetails['keypay_emp_id'] ?? '';
                    if (empty($keypayEmpId)) {
                        $resEmpData = $this->keypayfetch->kiosk_employee_create($memberDetails, $res['status']['data']);
                        if (!$resEmpData['status']) {
                            return $resEmpData;
                        }
                        $keypayEmpId = $resEmpData['emp_id'] ?? 0;
                        $this->basic_model->insert_records('keypay_kiosks_emp_mapping_for_member', ['member_id' => $memberId, 'keypay_emp_id' => $keypayEmpId, 'keypay_auth_id' => $authId, 'archive' => 0, 'created' => DATE_TIME]);
                    }

                    $latLongDetail = $shiftDetails['lat_long'] ?? '';
                    $latLongDetail = explode(DEFAULT_BREAKER_SQL_CONCAT, $latLongDetail);
                    $lat = $latLongDetail[0] ?? '';
                    $long = $latLongDetail[1] ?? '';
                    $shiftData = [
                        'keypay_emp_id' => $keypayEmpId,
                        'ip_address' => get_client_ip_server(),
                        'latitude' => $lat,
                        'longitude' => $long,
                        'notes' => 'Shift Id-' . $shiftId . ': ' . $shiftDetails['shift_note'],
                        'start_time' => change_one_timezone_to_another_timezone($shiftDetails['shift_start_date'], DEFAULT_TIME_ZONE_SERVER, 'UTC'),
                        'end_time' => change_one_timezone_to_another_timezone($shiftDetails['shift_end_date'], DEFAULT_TIME_ZONE_SERVER, 'UTC'),
                        'current_time' => change_one_timezone_to_another_timezone(DATE_TIME, DEFAULT_TIME_ZONE_SERVER, 'UTC'),
                        'hcm_shift_id' => $shiftId
                    ];
                    $resShiftData = $this->keypayfetch->kiosk_complete_shift_create($shiftData, $res['status']['data']);
                    if ($resShiftData['status']) {
                        $this->basic_model->update_records('shift', ['keypay_created' => 1, 'keypay_shift_id' => $resShiftData['keypay_shift_id']], ['id' => $shiftId]);
                        return ['status' => true, 'msg' => 'shift detils send sucessfully on keypay.'];
                    } else if (isset($resShiftData['error'])) {
                        return ['status' => false, 'error' => $resShiftData['error']];
                    }
                }
            } else {
                return $res;
            }
        }
    }

    public function get_member_key_pay_details_by_member_id($memberId = 0, $authId = 0) {
        $memberEmailSubQuery = $this->Finance_common_model->call_specific_function(['function_name' => 'get_member_email_sub_query']);
        $memberPhonSubQuery = $this->Finance_common_model->call_specific_function(['function_name' => 'get_member_phone_sub_query']);
        $memberKeyPayEmpIdSubQuery = $this->Finance_common_model->call_specific_function(['function_name' => 'get_member_keypay_kiosk_mapping_sub_query_by_auth_id', 'argument' => ['authId' => $authId]]);
        $this->db->select(["COALESCE((" . $memberKeyPayEmpIdSubQuery . "),'') as keypay_emp_id", "firstname", "lastname", "COALESCE((" . $memberEmailSubQuery . "),'') as email", "COALESCE((" . $memberPhonSubQuery . "),'') as phone"]);
        $this->db->from('tbl_member m');
        $this->db->where('m.id', $memberId);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    public function get_organisation_email_sub_query() {
        $this->db->select("sub_oe.email", false);
        $this->db->from('tbl_organisation_email as sub_oe');
        $this->db->where('fi.invoice_for=sub_oe.organisationId AND fi.archive=0 AND sub_oe.primary_email=1 AND sub_oe.archive=0');
        $query_res = $this->db->get_compiled_select();
        return $query_res;
    }

    public function common_invoice_email_user($tableAlias = 'fi') {
        $shiftSiteEmail = $this->get_invoice_site_house_email_sub_query(1);
        $shiftHouseEmail = $this->get_invoice_site_house_email_sub_query(2);
        $shiftPartcipantEmail = $this->get_invoice_participant_email_sub_query();
        $shiftOrgAndSubOrgEmail = $this->get_organisation_email_sub_query();
        return "CASE WHEN " . $tableAlias . ".booked_by=1 THEN (" . $shiftSiteEmail . ") WHEN " . $tableAlias . ".booked_by=7 THEN (" . $shiftHouseEmail . ") WHEN " . $tableAlias . ".booked_by=4 OR " . $tableAlias . ".booked_by=5 THEN (" . $shiftOrgAndSubOrgEmail . ") WHEN " . $tableAlias . ".booked_by=2 OR " . $tableAlias . ".booked_by=3 THEN (" . $shiftPartcipantEmail . ") ELSE '' END ";
    }

    public function get_participaint_ndis_number_sub_query($type = 1) {
        $this->db->select('sub_p.ndis_num');
        $this->db->from('tbl_participant sub_p');
        if ($type == 1) {
            $this->db->where('sub_p.id=fi.invoice_for AND fi.archive=0');
        }
        $this->db->limit(1);
        return $query_res = $this->db->get_compiled_select();
    }

    public function get_ndis_number_sub_query($tableAlias = 'fi', $tabType = 1) {
        $shift_partcipant_ndis = $this->get_participaint_ndis_number_sub_query($tabType);
        return "CASE WHEN " . $tableAlias . ".booked_by=2 OR " . $tableAlias . ".booked_by=3 THEN (" . $shift_partcipant_ndis . ") ELSE '' END ";
    }

    public function invoice_shift_time_sub_query() {
        $this->db->select("CONCAT_WS('" . DEFAULT_BREAKER_SQL_CONCAT . "',DATE_FORMAT(sub_s.start_time, '%d/%m/%Y %H:%i'),DATE_FORMAT(sub_s.end_time, '%d/%m/%Y %H:%i'),CAST(sum(TIME_TO_SEC(TIMEDIFF(sub_s.end_time, sub_s.start_time))/60/60 ) as DECIMAL(10,2)) )");
        $this->db->from("tbl_shift as sub_s");
        $this->db->join("tbl_finance_invoice_shifts as sub_fis", "sub_fis.shift_id=sub_s.id ", "inner");
        $this->db->where("sub_fis.invoice_id=fi.id", NULL, FALSE);
        $this->db->order_by("sub_s.id", "DESC");
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function save_invoice_addressed_to($reqData) {
        #pr($reqData);
        #booked_by = 1 - site/2 - participant, 7-house, 4- org, 5-sub-org
        $invoice_id = $reqData['invoice_id'];
        $booked_by = $reqData['booked_by'];
        $user_id = $reqData['invoice_for'];

        $address_to_ary = [];
        $address_to_ary['invoiceId'] = $invoice_id;
        $address_to_ary['booked_by'] = $booked_by;

        if ($booked_by == 1) {
            $site_row = $this->basic_model->get_row('organisation_site as os', ['os.site_name', 'os.bill_pay_by', 'os.organisationId'], ['os.id' => $user_id]);
            if ($site_row->bill_pay_by == 3) {

                $this->db->select("case when hskc.state!=0 THEN 
                    (SELECT tbl_state.name as state_name FROM tbl_state where hskc.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);
                $site_key_row = $this->basic_model->get_row('house_and_site_key_contact as hskc', ['hskc.id', 'hskc.firstname', 'hskc.lastname', 'hskc.street', 'hskc.city', 'hskc.postal', 'hskc.state'], ['hskc.siteId' => $user_id, 'archive' => 0, 'type' => 3, 'user_type' => 1]);

                if (!empty($site_key_row)) {
                    $site_key_email_row = $this->basic_model->get_row('house_and_site_key_contact_email', ['email'], ['contactId' => $site_key_row->id, 'archive' => 0, 'primary_email' => 1, 'user_type' => 1]);
                    $site_key_ph_row = $this->basic_model->get_row('house_and_site_key_contact_phone', ['phone'], ['contactId' => $site_key_row->id, 'archive' => 0, 'primary_phone' => 1]);

                    $address_to_ary['firstname'] = $site_key_row->firstname;
                    $address_to_ary['lastname'] = $site_key_row->lastname;
                    $address_to_ary['email'] = !empty($site_key_email_row) ? $site_key_email_row->email : '';
                    $address_to_ary['phone'] = !empty($site_key_ph_row) ? $site_key_ph_row->phone : '';
                    $address_to_ary['complete_address'] = $site_key_row->street . ', ' . $site_key_row->city . ', ' . $site_key_row->state_name . ', ' . $site_key_row->postal;
                }
            } else {
                $parentOrg = $site_row->organisationId;
                $this->load->model('organisation/Org_dashboard_model');
                $result = $this->Org_dashboard_model->get_org_billing_detail($parentOrg);

                $address_to_ary['firstname'] = !empty($result['data']) ? $result['data']->firstname : '';
                $address_to_ary['lastname'] = !empty($result['data']) ? $result['data']->lastname : '';

                $address_to_ary['email'] = !empty($result['data']->OrganisationEmail[0]) ? $result['data']->OrganisationEmail[0]->email : '';
                $address_to_ary['phone'] = !empty($result['data']->OrganisationPh[0]) ? $result['data']->OrganisationPh[0]->phone : '';

                $this->db->select("case when oa.state!=0 THEN 
                    (SELECT tbl_state.name as state_name FROM tbl_state where oa.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);

                $org_address_row = $this->basic_model->get_row('organisation_address as oa', ['oa.street', 'oa.city', 'oa.postal', 'oa.state'], ['oa.organisationId' => $parentOrg, 'primary_address' => 1]);
                if (!empty($org_address_row))
                    $address_to_ary['complete_address'] = $org_address_row->street . ', ' . $org_address_row->city . ', ' . $org_address_row->state_name . ', ' . $org_address_row->postal;
                else
                    $address_to_ary['complete_address'] = '';
            }
        }
        else if ($booked_by == 2) {
            $participant_row = $this->basic_model->get_row('participant as p', ['firstname', 'lastname'], ['id' => $user_id]);

            $participant_email_row = $this->basic_model->get_row('participant_email', ['email'], ['participantId' => $user_id, 'primary_email' => 1]);
            $participant_ph_row = $this->basic_model->get_row('participant_phone', ['phone'], ['participantId' => $user_id, 'primary_phone' => 1]);

            $this->db->select("case when pa.state!=0 THEN 
                (SELECT tbl_state.name as state_name FROM tbl_state where pa.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);
            $p_address_row = $this->basic_model->get_row('participant_address as pa', ['pa.street', 'pa.city', 'pa.postal', 'pa.state'], ['pa.participantId' => $user_id, 'primary_address' => 1, 'archive' => 0]);

            $address_to_ary['firstname'] = !empty($participant_row) ? $participant_row->firstname : '';
            $address_to_ary['lastname'] = !empty($participant_row) ? $participant_row->lastname : '';
            $address_to_ary['email'] = (!empty($participant_email_row)) ? $participant_email_row->email : '';
            $address_to_ary['phone'] = (!empty($participant_ph_row)) ? $participant_ph_row->phone : '';
            if (!empty($p_address_row))
                $address_to_ary['complete_address'] = $p_address_row->street . ', ' . $p_address_row->city . ', ' . $p_address_row->state_name . ', ' . $p_address_row->postal;
            else
                $address_to_ary['complete_address'] = '';
        }
        else if ($booked_by == 4 || $booked_by == 5) {
            $org_row = $this->basic_model->get_row('organisation', ['bill_pay_by', 'parent_org'], ['id' => $user_id]);
            if ($org_row->bill_pay_by == 3) {
                
            } else {
                $user_id = $org_row->parent_org;
            }

            $orgC_key_row = $this->basic_model->get_row('organisation_all_contact as orgC', ['orgC.id', 'orgC.name', 'orgC.lastname'], ['orgC.organisationId' => $user_id, 'archive' => 0, 'type' => 3]);

            if (!empty($orgC_key_row)) {
                $org_key_email_row = $this->basic_model->get_row('organisation_all_contact_email', ['email'], ['contactId' => $orgC_key_row->id, 'archive' => 0, 'primary_email' => 1]);
                $org_key_ph_row = $this->basic_model->get_row('organisation_all_contact_phone', ['phone'], ['contactId' => $orgC_key_row->id, 'archive' => 0, 'primary_phone' => 1]);

                $this->db->select("case when oa.state!=0 THEN 
                    (SELECT tbl_state.name as state_name FROM tbl_state where oa.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);
                $org_address_row = $this->basic_model->get_row('organisation_address as oa', ['oa.street', 'oa.city', 'oa.postal', 'oa.state'], ['oa.organisationId' => $user_id, 'primary_address' => 1]);

                $address_to_ary['firstname'] = $orgC_key_row->name;
                $address_to_ary['lastname'] = $orgC_key_row->lastname;
                $address_to_ary['email'] = !empty($org_key_email_row) ? $org_key_email_row->email : '';
                $address_to_ary['phone'] = !empty($org_key_ph_row) ? $org_key_ph_row->phone : '';
                if (!empty($org_address_row))
                    $address_to_ary['complete_address'] = $org_address_row->street . ', ' . $org_address_row->city . ', ' . $org_address_row->state_name . ', ' . $org_address_row->postal;
                else
                    $address_to_ary['complete_address'] = '';
            }
        }
        else if ($booked_by == 7) {

            $site_key_row = $this->basic_model->get_row('house_and_site_key_contact as hskc', ['hskc.id', 'hskc.firstname', 'hskc.lastname'], ['hskc.siteId' => $user_id, 'archive' => 0, 'type' => 3, 'user_type' => 2]);

            $this->db->select("case when h.state!=0 THEN 
                (SELECT tbl_state.name as state_name FROM tbl_state where h.state=tbl_state.id AND tbl_state.archive=0) ELSE '' END as state_name", false);
            $house_addr_row = $this->basic_model->get_row('house as h', ['h.street', 'h.suburb', 'h.postal', 'h.state'], ['h.id' => $user_id, 'archive' => 0]);

            if (!empty($site_key_row)) {
                $site_key_email_row = $this->basic_model->get_row('house_and_site_key_contact_email', ['email'], ['contactId' => $site_key_row->id, 'archive' => 0, 'primary_email' => 1, 'user_type' => 2]);
                $site_key_ph_row = $this->basic_model->get_row('house_and_site_key_contact_phone', ['phone'], ['contactId' => $site_key_row->id, 'archive' => 0, 'primary_phone' => 1, 'user_type' => 2]);

                $address_to_ary['firstname'] = $site_key_row->firstname;
                $address_to_ary['lastname'] = $site_key_row->lastname;
                $address_to_ary['email'] = !empty($site_key_email_row) ? $site_key_email_row->email : '';
                $address_to_ary['phone'] = !empty($site_key_ph_row) ? $site_key_ph_row->phone : '';
                if (!empty($house_addr_row))
                    $address_to_ary['complete_address'] = $house_addr_row->street . ', ' . $house_addr_row->suburb . ', ' . $house_addr_row->state_name . ', ' . $house_addr_row->postal;
                else
                    $address_to_ary['complete_address'] = '';
            }
        }

        if (!empty($address_to_ary))
            $this->basic_model->insert_records('invoice_addressed_to', $address_to_ary);
    }

}
