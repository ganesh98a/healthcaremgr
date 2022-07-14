<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceInvoice extends MX_Controller {

    use formCustomValidation;

    function __construct() {
        parent::__construct();
        $this->load->model(['Finance_invoice_model']);
        $this->load->model(['Finance_common_model']);
        $this->load->model(['Finance_model']);
        $this->load->model(['Finance_ammount_calculation_model']);
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->form_validation->CI = & $this;
        $this->loges->setModule(22);
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

    public function srch_shift_bydate() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);
            $result = $this->Finance_invoice_model->srch_shift_bydate($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function srch_statement_bydate() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $reqData = json_decode($reqData->data);

            $result = $this->Finance_invoice_model->srch_statement_bydate($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function get_funding_type() {
        $reqData = request_handler('access_finance');
        $response_funding_type = $this->basic_model->get_record_where('funding_type', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
        $response_funding_type = isset($response_funding_type) && !empty($response_funding_type) ? $response_funding_type : array();

        $response_measure_type = $this->basic_model->get_record_where('finance_measure', $column = array('name as label', 'id as value'), $where = array('archive' => 0));
        $response_measure_type = isset($response_measure_type) && !empty($response_measure_type) ? $response_measure_type : array();

        echo json_encode(array('status' => true, 'data' => array('response_funding_type' => $response_funding_type, 'response_measure_type' => $response_measure_type)));
        exit();
    }

    public function get_line_item_by_funding_type() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            $result = $this->Finance_invoice_model->get_line_item_by_funding_type($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    public function save_invoice() {
        $reqData = request_handler('access_finance_invoice');
        if (!empty($reqData->data)) {
            $admin_id = $reqData->adminId;
            $reqData = $reqData->data;
            $data = (array) $reqData;

            $handle_lineItemInvoice = $handle_manualInvoiceItem = true;
            if (obj_to_arr($data['manualInvoiceItem']) && obj_to_arr($data['lineItemInvoice'])) {
                foreach (obj_to_arr($data['manualInvoiceItem']) as $value) {
                    if ($value['item_name'] == '') {
                        $handle_manualInvoiceItem = false;
                        break;
                    }
                }

                foreach (obj_to_arr($data['lineItemInvoice']) as $value) {
                    if ($value['measure_by'] == '') {
                        $handle_lineItemInvoice = false;
                        break;
                    }
                }
            }

            if ($handle_lineItemInvoice === FALSE && $handle_manualInvoiceItem === FALSE) {
                $return = array('status' => false, 'msg' => 'Please fill atleast one item from Line item invoice OR from Manual invoice');
                echo json_encode($return);
                exit();
            }
            $m_plan_line_itemId = 0;

            #only for participant we need to manage funds and check remaing fund before insert
            if ($reqData->UserTypeInt == 2 && obj_to_arr($data['manualInvoiceItem'])) {
                $fund = $this->Finance_model->check_amount_for_miscellaneous_item($reqData->manualInvoiceItem, $reqData->UserId, $reqData->UserTypeInt);
                if (!$fund['status']) {
                    $return = array('status' => false, 'msg' => 'Miscellaneous line item has not that much of fund that you are requested.');
                    echo json_encode($return);
                    exit();
                } else {
                    $m_plan_line_itemId = $fund['plan_line_itemId'];
                }
            }

            $temp_rule = array(
                array('field' => 'pay_by', 'label' => 'Pay By', 'rules' => 'required'),
                array('field' => 'funding_type', 'label' => 'Funding Type', 'rules' => 'required'),
                array('field' => 'manualInvoiceItem[]', 'label' => 'manualInvoiceItem', 'rules' => 'callback_check_miscellaneous_item[' . json_encode($reqData->manualInvoiceItem) . ']'),
            );

            if ($reqData->UserTypeInt == 2 || $reqData->UserTypeInt == 3) {
                $xx = array(
                    array('field' => 'lineItemInvoice[]', 'label' => 'lineItemInvoice', 'rules' => 'callback_check_invoice_lineitem[' . json_encode($reqData->lineItemInvoice) . ']')
                );
            } else {
                $xx = array(
                    array('field' => 'lineItemInvoice[]', 'label' => 'lineItemInvoice', 'rules' => 'callback_check_invoice_lineitem_for_other[' . json_encode($reqData->lineItemInvoice) . ']')
                );
            }

            $validation_rules = array_merge($xx, $temp_rule);
            #pr($validation_rules);
            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                if (isset($reqData->payrate_id) && $reqData->payrate_id > 0) {
                    #$this->Finance_invoice_model->add_payrate($reqData);
                    $msg = 'Invoice Updated successfully.';
                } else {
                    $this->Finance_invoice_model->save_invoice($reqData, $m_plan_line_itemId);
                    $msg = 'Invoice created successfully.';
                }
                $return = array('status' => true, 'msg' => $msg);
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'msg' => implode("\n", $errors));
            }
            echo json_encode($return);
        }
    }

    public function save_statemenets() {
        $reqData = request_handler('access_finance_payrate');

        if (!empty($reqData->data)) {
            $admin_id = $reqData->adminId;
            $reqData = $reqData->data;
            $data = (array) $reqData;
            $validation_rules = array(
                array('field' => 'statement_from_date', 'label' => 'statement from date', 'rules' => 'required'),
                array('field' => 'statement_to_date', 'label' => 'statement to date', 'rules' => 'required'),
                array('field' => 'statement_issue_date', 'label' => 'statement issue date', 'rules' => 'required'),
                array('field' => 'statement_due_date', 'label' => 'statement due date', 'rules' => 'required'),
                array('field' => 'statement_shift_notes', 'label' => 'Add Notes To Quote', 'rules' => 'required'),
                array('field' => 'UserId', 'label' => 'user (participant) ', 'rules' => 'required'),
                array('field' => 'UserTypeInt', 'label' => 'User Type required', 'rules' => 'required'),
                array('field' => 'srchShiftList_data', 'label' => 'manualInvoiceItem', 'rules' => 'callback_check_invoice_data[' . json_encode($reqData) . ']'),
            );

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $response = $this->Finance_invoice_model->save_statemenets($reqData);
                if ($response['status']) {
                    $UserId = $reqData->UserId;
                    $statement_id = $response['statement'];
                    // $statement_id=$reqData->data->statement_id;
                    $result = $this->Finance_invoice_model->get_send_statement_mail($statement_id);
                    $this->Finance_invoice_model->insert_statment_bookers($result);

                    $result['statement'] = $statement_id;
                    $sendMail = $this->send_statement_email($result);

                    if ($sendMail['status']) {
                        $this->basic_model->update_records('finance_statement', ['status' => 0], ['id' => $statement_id]);
                        $return = array('status' => true, 'msg' => 'Statement created successfully.');
                    } else {
                        $return = array('status' => true, 'msg' => 'Statement created successfully send mail failed.');
                    }
                } else {
                    $return = array('status' => false, 'error' => 'Create Statement failed');
                }
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'msg' => implode("\n", $errors));
            }
            echo json_encode($return);
        }
    }

    public function send_statement_email($statementData) {

        $filePath = FINANCE_STATEMENT_FILE_PATH;
        $fileFCPath = FCPATH . $filePath . $statementData['file'];
        if (file_exists($fileFCPath)) {
            $statementData['filepath'] = $fileFCPath;
            $statementData['subject'] = $statementData['file'];
            send_finanance_statement_email($statementData);
            return array('status' => true);
        } else {
            return array('status' => false, 'error' => "file not found");
        }
    }

    public function check_invoice_data($sourceData, $reqData) {
        $reqData = json_decode($reqData, true);
        if (!empty($reqData['srchShiftList'])) {
            foreach ($reqData['srchShiftList'] as $shiftList) {
                if ($shiftList['id'] > 0) {
                    
                } else {
                    $this->form_validation->set_message('check_invoice_data', 'null invoice not accepted please select right invoice.');
                    return false;
                }
            }
        } else {
            $this->form_validation->set_message('check_invoice_data', 'Invoice statement list required.');
            return false;
        }
        return true;
    }

    public function check_srch_shift($shift_data) {
        if (empty($shift_data)) {
            $this->form_validation->set_message('check_srch_shift', 'Need atleat 1 shift to create invoice.');
            return false;
        }
    }

    public function check_invoice_lineitem($z, $invoice_lineitem) {
        if (!empty($invoice_lineitem)) {
            if (!empty($invoice_lineitem->measure_by)) {
                if (empty($invoice_lineitem->lineItem)) {
                    $this->form_validation->set_message('check_invoice_lineitem', 'Please select line item.');
                    return false;
                }

                if (empty($invoice_lineitem->quantity)) {
                    $this->form_validation->set_message('check_invoice_lineitem', 'Please add quantity.');
                    return false;
                }

                if ((int) $invoice_lineitem->quantity == 0) {
                    $this->form_validation->set_message('check_invoice_lineitem', 'Quantity should be greater than 0');
                    return false;
                }

                $get_lineitem_remaing_fund = $this->basic_model->get_row('user_plan_line_items', array('CAST( fund_remaining as DECIMAL(10,2)) as fund_remaining'), $id_array = array('archive' => 0, 'line_itemId' => $invoice_lineitem->lineItem->value));

                if (empty($get_lineitem_remaing_fund)) {
                    #last_query();
                    $this->form_validation->set_message('check_invoice_lineitem', 'Selected line item is not assign.');
                    return false;
                } else {
                    $cost = $invoice_lineitem->cost;
                    $gst_on_cost = calculate_gst($cost);
                    $finalCostIncludeGst = floatval($cost) + floatval($gst_on_cost);

                    if (floatval($finalCostIncludeGst) > floatval($get_lineitem_remaing_fund->fund_remaining)) {
                        $this->form_validation->set_message('check_invoice_lineitem', $invoice_lineitem->lineItem->label . ' line item has not sufficient fund.');
                        return false;
                    } else {
                        return true;
                    }
                }
            }
        }
        return true;
    }

    public function check_invoice_lineitem_for_other($invoice_lineitem) {
        if (!empty($invoice_lineitem)) {
            if (!empty($invoice_lineitem->measure_by)) {
                if (empty($invoice_lineitem->lineItem)) {
                    $this->form_validation->set_message('check_invoice_lineitem_for_other', 'Please select line item.');
                    return false;
                }

                if (empty($invoice_lineitem->quantity)) {
                    $this->form_validation->set_message('check_invoice_lineitem_for_other', 'Please add quantity.');
                    return false;
                }

                if ((int) $invoice_lineitem->quantity == 0) {
                    $this->form_validation->set_message('check_invoice_lineitem_for_other', 'Quantity should be greater than 0');
                    return false;
                }

                $this->db->where(" '" . DATE_CURRENT . "' BETWEEN start_date and end_date");
                $get_lineitem_row = $this->basic_model->get_row('finance_line_item', array('id', 'line_item_number'), $id_array = array('id' => $invoice_lineitem->lineItem->value));

                if (empty($get_lineitem_row)) {
                    $this->form_validation->set_message('check_invoice_lineitem_for_other', $invoice_lineitem->lineItem->label . ' no such line item found or Line item is not active.');
                    return false;
                } else {
                    return true;
                }
            }
        }
        return true;
    }

    public function check_miscellaneous_item($manualInvoiceItem) {
        if (!empty($manualInvoiceItem)) {
            if (!empty($manualInvoiceItem->itemName)) {
                if (empty($manualInvoiceItem->itemDescription)) {
                    $this->form_validation->set_message('check_miscellaneous_item', 'Please add description.');
                    return false;
                }

                if (empty($manualInvoiceItem->itemCost)) {
                    $this->form_validation->set_message('check_miscellaneous_item', 'Please add item cost.');
                    return false;
                }
            }
        }
        return true;
    }

    public function dashboard_invoice_list() {
        $reqData = request_handler('access_finance_invoice');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->dashboard_invoice_list($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function export_invoice_to_csv() {
        $reqData = request_handler('access_finance_invoice');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->dashboard_invoice_list($reqData, ["dashboard_invoice_export_csv" => true]);

            $this->load->model('Finance_common_model');

            $dataHearder = ['invoice_number' => 'Invoice Number', 'description' => 'Description ', 'invoice_for' => 'Invoice For ', 'addressto' => 'Addressed To', 'amount' => 'Amount (Incl GST)', 'fund_type' => 'Fund Type', 'invoice_date' => 'Date of Invoice', 'invoice_status' => 'Status'];

            $response = $this->Finance_common_model->export_csv($dataHearder, obj_to_arr($result['data']), ['file_name' => 'Invoice_' . date('d_m_Y_H_i_s') . '.csv']);

            echo json_encode($response);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }

    public function ndis_invoice_list() {
        $reqData = request_handler('access_finance_ndis_invoice');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->dashboard_invoice_list($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function get_participant_organization_name() {
        $reqData = request_handler('access_finance');

        if (isset($reqData->data->search)) {
            $res = $this->Finance_invoice_model->get_participant_organization_name($reqData->data);

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

    public function invoice_scheduler_list() {
        $reqData = request_handler('access_finance_invoice');

        if (!empty($reqData->data)) {
            //$reqData = json_decode($reqData->data);
            $result = $this->Finance_invoice_model->invoice_scheduler_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    public function auto_invoice_create_shift_completed_by_shfit_id() {
        $shiftId = $this->input->post('shift_id');
        if (!empty($shiftId)) {
            $res = $this->Finance_invoice_model->create_invoice_by_shift_id($shiftId);
            if ($res['status']) {
                $this->block_to_used_status_update_shift_fund_by_shift_id($shiftId);
            }
            pr($res);
        }
        exit();
    }

    public function get_invoice_scheduler_history_list() {
        $reqData = request_handler('access_finance_invoice');

        if (!empty($reqData->data)) {
            $result = $this->Finance_invoice_model->get_invoice_scheduler_history_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    function get_invoice_pdf() {
        $reqData = request_handler('access_finance_invoice');

        if (isset($reqData->data->invoiceId)) {
            $res = $this->Finance_invoice_model->get_invoice_pdf($reqData->data->invoiceId);
            //print_r($res);
            if (!empty($res)) {
                if (!empty($res->invoice_file_path)) {
                    $pdf_fileFCpath = FCPATH . FINANCE_INVOICE_FILE_PATH . $res->invoice_file_path;
                    if (file_exists($pdf_fileFCpath) && !is_dir($pdf_fileFCpath)) {
                        $pdf_filePath = base_url() . 'mediaShowDocument/fi/' . urlencode(base64_encode('0')) . '/' . urlencode(base64_encode($res->invoice_file_path)) . '/' . urlencode($res->invoice_file_path);
                        $data = ['file_path' => $pdf_filePath, 'file_name' => $res->invoice_file_path, 'invoice_for' => $res->invoice_for];

                        echo json_encode(['status' => true, 'data' => $data]);
                        exit();
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invoice pdf file not exist.']);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invoice pdf file name not exist.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Records not exist.']);
                exit();
            }
        }
    }

    public function resend_invoice_mail() {
        $reqData = request_handler('access_finance_invoice');
        if (isset($reqData->data->invoiceId)) {
            $res = $this->Finance_invoice_model->resend_invoice_mail($reqData->data->invoiceId);
            if ($res) {
                echo json_encode(['status' => true, 'msg' => 'Mail send successfully.']);
                exit();
            } else {
                echo json_encode(['status' => false, 'error' => 'Something went wrong.']);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }

    public function update_invoice_status() {
        $reqData = request_handler('access_finance_invoice');
        $invoiceStatusData = json_decode(INVOICE_PAYMENT_STATUS_DATA);
        $invoiceStatusData = array_map('strtolower', $invoiceStatusData);
        $invoiceStatusData = array_flip($invoiceStatusData);

        if (isset($reqData->data->invoiceId) && isset($reqData->data->status) && isset($invoiceStatusData[$reqData->data->status]) && in_array($invoiceStatusData[$reqData->data->status], ['1', '2'])) {
            $res = $this->Finance_invoice_model->update_invoice_status($reqData->data->invoiceId, $invoiceStatusData[$reqData->data->status]);
            echo json_encode($res);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }

    public function dashboard_finance_invoice_statement_list() {
        $reqData = request_handler('access_finance');
        if (!empty($reqData->data)) {
            //$reqData = json_decode($reqData->data,true);
            $result = $this->Finance_invoice_model->dashboard_statement_list($reqData->data);
            echo json_encode($result);
            exit();
        }
    }

    public function ndis_export_csv() {
        $reqData = request_handler('access_finance_ndis_invoice');

        if (!empty($reqData->data)) {
            $this->load->model('Finance_common_model');
            $res = $this->Finance_invoice_model->dashboard_invoice_list($reqData->data, ['exportCall' => true]);
            $res = !empty($res) ? obj_to_arr($res) : [];
            $dataRes = !empty($res) && isset($res['data']) ? $res['data'] : [];
            $manualInvoiceData = array_filter($dataRes, function($row) {
                return $row['invoice_type'] == '2' ? true : false;
            });
            $shiftInvoiceData = array_filter($dataRes, function($row) {
                return $row['invoice_type'] == '1' ? true : false;
            });
            $manualInvoiceIds = array_column($manualInvoiceData, 'id');
            $shiftInvoiceIds = array_column($shiftInvoiceData, 'id');
            $manualInvoiceLineItemData = !empty($manualInvoiceIds) ? $this->Finance_invoice_model->get_manual_invoice_items_by_invoice_id($manualInvoiceIds, 'exportCall') : [];
            $shiftInvoiceLineItemData = !empty($shiftInvoiceIds) ? $this->Finance_invoice_model->get_invoice_items_by_invoice_id($shiftInvoiceIds, 'exportCall') : [];
            $invoiceData = !empty($dataRes) ? pos_index_change_array_data($dataRes, 'id') : [];


            $dataGenrate = [];
            if (!empty($manualInvoiceLineItemData)) {
                foreach ($manualInvoiceLineItemData as $row) {
                    $temp = [];
                    $tempData = $invoiceData[$row['invoice_id']] ?? [];
                    $tempDataIns = ['unique_id_data' => $row['invoice_id'] . $row['line_itme_id'] . $row['misc_item_id']];
                    $tempDataIns['shift_start_date'] = $tempData['invoice_date'] ?? '';
                    $tempDataIns['shift_end_date'] = $tempData['invoice_date'] ?? '';
                    $tempDataIns['invoice_gst_code'] = INVOICE_GST_CODE;
                    $tempDataIns['invoice_capped_price'] = INVOICE_CAPPED_PRICE;
                    $tempDataIns['invoice_blank_flied'] = '';
                    $tempDataIns['payment_request_number'] = uniqid();
                    $temp = array_merge($tempData, $row, $tempDataIns);
                    $dataGenrate[] = $temp;
                }
            }
            if (!empty($shiftInvoiceLineItemData)) {
                foreach ($shiftInvoiceLineItemData as $row) {
                    $temp = [];
                    $tempData = $invoiceData[$row['invoice_id']] ?? [];
                    $tempDataIns = ['unique_id_data' => $row['invoice_id'] . $row['line_itme_id'] . $row['misc_item_id']];
                    $shiftTime = $tempData['shift_date_time'] ?? '';
                    $shiftTimeData = explode(DEFAULT_BREAKER_SQL_CONCAT, $shiftTime);
                    $tempDataIns['shift_start_date'] = $shiftTimeData[0] ?? '';
                    $tempDataIns['shift_end_date'] = $shiftTimeData[1] ?? '';
                    //pr($tempDataIns);
                    $tempDataIns['invoice_gst_code'] = INVOICE_GST_CODE;
                    $tempDataIns['invoice_capped_price'] = INVOICE_CAPPED_PRICE;
                    $tempDataIns['invoice_blank_flied'] = '';
                    $tempDataIns['payment_request_number'] = uniqid();
                    $temp = array_merge($tempData, $row, $tempDataIns);
                    $dataGenrate[] = $temp;
                }
            }
            $dataGenrate = !empty($dataGenrate) ? pos_index_change_array_data($dataGenrate, 'unique_id_data') : [];
            ksort($dataGenrate);
            $dataHearder = ['register_number' => 'RegistrationNumber', 'invoice_ndis_number' => 'NDISNumber ', 'shift_start_date' => 'SupportsDeliveredFrom ', 'shift_end_date' => 'SupportsDeliveredTo', 'line_item_number' => 'SupportNumber', 'invoice_number' => 'ClaimReference', 'quantity' => 'Quantity', 'cost' => 'UnitPrice',
                'invoice_gst_code' => 'GSTCode',
                'amount' => 'PaidTotalAmount',
                'payment_request_number' => 'Payment Request Number',
                'invoice_for' => 'Participant Name',
                'invoice_capped_price' => 'Capped Price',
                'invoice_blank_flied_request_statue' => 'Payment Request Status',
                'invoice_blank_flied_request_msg' => 'Error Message',
                'invoice_blank_flied_request_claim_type' => 'ClaimType',
                'invoice_blank_flied_request_cancellation' => 'CancellationReason',
            ];
            $response = $this->Finance_common_model->export_csv($dataHearder, $dataGenrate, ['file_name' => 'NDIS_invoice_' . date('d_m_Y_H_i_s') . '.csv']);
            if ($response['status'] == true) {
                $ins_data = [
                    'file_name' => $response['filename'],
                    'created_by' => $reqData->adminId,
                    'created' => DATE_TIME
                ];
                $this->basic_model->insert_records('finance_invoice_ndis_export_log', $ins_data);
            }
            echo json_encode($response);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid Request.']);
            exit();
        }
    }

    public function read_csv_ndis_invoice_status() {
        $data = request_handlerFile('access_finance_ndis_invoice');
        $import_batch = uniqid();
        $checkData = (array) $data;
        $validation_rules = array(
            array('field' => 'file_title', 'label' => 'File Title', 'rules' => 'required'),
        );
        if (empty($_FILES['docsFile']['name'])) {
            $validation_rules[] = array('field' => 'docsFile', 'label' => 'Document File', 'rules' => 'required');
        }
        // set rules form validation
        $this->form_validation->set_rules($validation_rules);
        $this->form_validation->set_data($checkData);
        // check requested data return true or false
        if ($this->form_validation->run()) {
            $column_name_invoice_number = 'claimreference';
            $column_name_invoice_status = 'payment request status';
            $column_name_invoice_payment_request_number = 'payment request number';
            $reuiredColumnCheck = [$column_name_invoice_number, $column_name_invoice_status, 'error message', 'cancellationreason'];
            $this->load->library('csv_reader');
            //$mimes = array('application/vnd.ms-excel','text/plain','text/csv','text/tsv','application/octet-stream');
            $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
            if (in_array($_FILES['docsFile']['type'], $mimes)) {
                $tmpName = $_FILES['docsFile']['tmp_name'];
                $file = $this->csv_reader->read_csv_data($tmpName);
                // $file = array_map('str_getcsv', file($tmpName));
                $invoice_status_data = [];
                if (!empty($file)) {
                    $header = array_shift($file);
                    $header = array_map('strtolower', $header);
                    $header = array_map('trim', $header);
                    $columnRequired = array_diff($reuiredColumnCheck, $header);
                    if (count($columnRequired) > 0) {
                        $return = ["status" => false, "error" => "Required column missing (" . implode(', ', $columnRequired) . ") in file import.Please try importing the file again."];
                    } else {
                        foreach ($file as $row) {
                            if (count($row) == count($header)) {
                                $row = array_map("utf8_encode", $row);
                                $row = array_map("strtolower", $row);
                                $row = array_map("trim", $row);
                                $invoice_status_data[] = array_combine($header, $row);
                            } else {
                                $return = ["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."];
                                break;
                            }
                        }

                        if (!empty($invoice_status_data)) {
                            $return = $this->Finance_invoice_model->check_and_update_invoice_status_data($invoice_status_data, ['import_batch' => $import_batch, 'column_name_invoice_number' => $column_name_invoice_number, 'column_name_invoice_status' => $column_name_invoice_status, 'column_name_invoice_payment_request_number' => $column_name_invoice_payment_request_number]);
                            if ($return['status']) {

                                $config['upload_path'] = FINANCE_IMPORT_NDIS_STATUS_UPLOAD_PATH; // user here constact for specific path
                                $config['input_name'] = 'docsFile';
                                $config['remove_spaces'] = false;
                                $config['directory_name'] = '';
                                $config['allowed_types'] = FINANCE_IMPORT_NDIS_STATUS_UPLOAD_TYPE; //'csv';

                                $is_upload = do_upload($config); // upload file
                                // check here file is uploaded or not return key error true
                                if (isset($is_upload['error'])) {
                                    // return error comes in file uploading
                                    //echo json_encode(array('status' => false, 'error' => strip_tags($is_upload['error'])));
                                    ///exit();
                                } else {
                                    $insert_ary = array(
                                        'file_path' => $is_upload['upload_data']['file_name'],
                                        'file_title' => $data->file_title,
                                        'response' => json_encode($return),
                                        'created_by' => $data->adminId,
                                        'created' => DATE_TIME,
                                        'import_batch' => $import_batch
                                    );
                                    $this->basic_model->insert_records('finance_invoice_ndis_status_import_log', $insert_ary, $multiple = FALSE);
                                }
                            }
                        } else {
                            $return = ['status' => false, 'error' => 'file item row invalid data'];
                        }
                    }
                } else {
                    $return = ['status' => false, 'error' => 'File invalid data'];
                }
            } else {
                $return = ['status' => false, 'error' => 'File not valid extension.'];
            }
        } else {
            // return error else data data not valid
            $errors = $this->form_validation->error_array();
            $return = array('status' => false, 'error' => implode(', ', $errors));
        }
        echo json_encode($return);
        exit();
    }

    public function get_dashboard_graph_count() {
        $reqData = request_handler("access_finance");

        $return = ['status' => false, 'data' => [], 'error' => 'Invalid Request.'];
        if (!empty($reqData->data)) {
            $res = false;
            if ($reqData->data->mode == 'invoice_income') {
                $res = $this->Finance_invoice_model->get_income_from_invoice($reqData->data->type);
            } else if ($reqData->data->mode == 'loss_credit_note') {
                $res = $this->Finance_invoice_model->get_loss_credit_notes_invoice($reqData->data->type);
            } else if ($reqData->data->mode == 'loss_of_refund') {
                $res = $this->Finance_invoice_model->get_loss_refund_invoice($reqData->data->type);
            } else if ($reqData->data->mode == 'invoice_income_this_month') {
                $res = $this->Finance_invoice_model->get_invoice_money('month');
            } else if ($reqData->data->mode == 'invoice_credited_and_refund_paid_this_month') {
                $res = $this->Finance_invoice_model->get_invoice_credited_and_refund_paid('month');
            } else if ($reqData->data->mode == 'invoice_profit_this_month') {
                $res = $this->Finance_invoice_model->get_invoice_profit_money('month');
            }
            if ($res !== false) {
                $return = ['status' => true, 'data' => $res];
            }
        }
        echo json_encode($return);
        exit();
    }

    function get_statement_pdf() {
        $reqData = request_handler('access_finance_invoice');

        if (isset($reqData->data->invoiceId)) {
            $res = $this->Finance_invoice_model->get_statement_pdf($reqData->data->invoiceId);
            $res = obj_to_arr($res);
            if (!empty($res['status'])) {

                if (!empty($res['data']['statement_file_path'])) {
                    $fileName = $res['data']['statement_file_path'];
                    $pdf_fileFCpath = FCPATH . FINANCE_STATEMENT_FILE_PATH . $fileName;
                    if (file_exists($pdf_fileFCpath)) {
                        $participant_name = ($res['data']['statement_for']) ? ' For ' . $res['data']['statement_for'] : '';
                        $statement_title = $res['data']['statement_number'] . $participant_name;
                        $pdf_filePath = base_url() . 'mediaShowDocument/fs/' . urlencode(base64_encode('0')) . '/' . urlencode(base64_encode($fileName)) . '/' . urlencode($fileName);
                        $data = ['file_path' => $pdf_filePath, 'file_name' => $fileName, 'invoice_for' => $statement_title];

                        echo json_encode(['status' => true, 'data' => $data]);
                        exit();
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invoice pdf file not exist.']);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'statement pdf file name not exist.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Records not exist.']);
                exit();
            }
        }
    }

    public function get_ndis_dashboard_graph_count() {
        $reqData = request_handler('access_finance_ndis_invoice');
        $return = ['status' => false, 'data' => [], 'error' => 'Invalid Request.'];
        if (!empty($reqData->data)) {
            $res = -1;
            if ($reqData->data->mode == 'ndis_money_in') {
                $res = $this->Finance_invoice_model->get_ndis_invoice_money($reqData->data->type);
            } else if ($reqData->data->mode == 'ndis_invoice_error_rejected') {
                $res = $this->Finance_invoice_model->get_invoice_ndis_rejected($reqData->data->type);
            } else if ($reqData->data->mode == 'invoice_error_rejected') {
                $res = $this->Finance_invoice_model->get_invoice_rejected($reqData->data->type);
            } else if ($reqData->data->mode == 'last_ndis_billing') {
                $res = $this->Finance_invoice_model->get_last_ndis_billing();
            } else if ($reqData->data->mode == 'ndis_invoice_error_rejected_monthwise_financial_year') {
                $res = $this->Finance_invoice_model->get_ndis_invoice_rejected_monthwise_financial_year();
            }
            if ($res > -1) {
                $return = ['status' => true, 'data' => $res];
            }
        }
        echo json_encode($return);
        exit();
    }

    public function get_pending_invoice_list_for_credit_notes() {
        $reqData = request_handler('access_finance_credit_note_and_refund');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->dashboard_invoice_list($reqData, ['creditNoteCall' => true]);
            echo json_encode($result);
            exit();
        }
    }

    function resend_statemenets() {
        $reqData = request_handler('access_finance_invoice');

        if (!empty($reqData->data)) {
            $statement_id = $reqData->data->statement_id;
            $result = $this->Finance_invoice_model->get_send_statement_mail($statement_id);
            $result['statement'] = $statement_id;

            $sendMail = $this->send_statement_email($result);
            if ($sendMail['status']) {
                $this->basic_model->update_records('finance_statement', ['status' => 1], ['id' => $statement_id]);
                //$return = array('status' => true, 'msg' => 'Statement created successfully.');
                $return = array('status' => true, 'msg' => 'Resend mail statement successfully.');
            } else {
                $return = array('status' => false, 'msg' => 'Resend mail Statement failed.');
            }
        } else {
            $return = array('status' => false, 'msg' => 'something went wrong.');
        }
        echo json_encode($return);
        exit();
    }

    function save_credit_notes() {
        $reqData = $reqDataLogs = request_handler('access_finance_credit_note_and_refund');
        if (!empty($reqData->data)) {
            $admin_id = $reqData->adminId;
            $reqData = $reqData->data;
            $data = (array) $reqData;
            $validation_rules = array(
                array('field' => 'invoice_for', 'label' => 'user select', 'rules' => 'required'),
                array('field' => 'booked_by', 'label' => 'user select', 'rules' => 'required'),
                array('field' => 'totalCreditAvailabelAmount', 'label' => 'Credit Availabel Amount', 'rules' => 'required'),
            );
            if (!empty($reqData->from_select_invoice_apply)) {
                $data['from_select_invoice_apply_check'][] = $reqData->from_select_invoice_apply;
                $validation_rules[] = array('field' => 'from_select_invoice_apply_check[]', 'label' => 'Select for invoice Credit Notes', 'rules' => "callback_check_credit_notes_invoice_and_amount_assigned_to_user_from[amount," . $reqData->invoice_for . "," . $reqData->booked_by . "]");
            }
            if (!empty($reqData->to_select_invoice_apply)) {
                $data['to_select_invoice_apply_check'][] = $reqData->to_select_invoice_apply;
                $validation_rules[] = array('field' => 'to_select_invoice_apply_check[]', 'label' => 'Apply Credit Notes', 'rules' => "callback_check_credit_notes_invoice_and_amount_assigned_to_user_applied[invoice_apply_amount," . $reqData->invoice_for . "," . $reqData->booked_by . "]");
            }

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $return = $this->Finance_invoice_model->save_credit_notes($reqData);
                if ($return['status']) {
                    $this->loges->setCreatedBy($admin_id);
                    $adminName = $this->username->getName('admin', $admin_id);
                    $this->loges->setTitle('Create credit notes-' . ($return['credit_note_id'] ?? '') . ': ' . ($reqData->invoice_for_user ?? ''));
                    $this->loges->setSpecific_title('Create credit notes added by ' . $adminName);
                    $this->loges->setUserId($reqData->invoice_for);
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->createLog();
                    $url = rtrim(base_url(), '/') . '/cron/Finance_cron/update_finance_invoice_status_after_credit_note_apply';
                    $this->load->library('CustomCurlCall');
                    $this->customcurlcall->requestWithoutWait('GET', $url);
                }
            } else {
                $errors = $this->form_validation->error_array();
                $return = array('status' => false, 'error' => implode('., ', $errors));
            }
        } else {
            $return = array('status' => false, 'error' => 'Invalid Request.');
        }
        echo json_encode($return);
        exit();
    }

    function get_outstanding_statements_participants_count() {
        $reqData = request_handler('access_finance_statement');
        if (!empty($reqData->data)) {
            $result = $this->Finance_invoice_model->get_outstanding_statements_participants_count($reqData->data->view_type);
            echo json_encode(['status' => true, 'data' => $result]);
        } else {
            echo json_encode(['status' => true, 'msg' => 'something went wrong.']);
        }
    }

    function get_outstanding_statements_organization_count() {
        $reqData = request_handler('access_finance_statement');
        if (!empty($reqData->data)) {
            $result = $this->Finance_invoice_model->get_outstanding_statements_organization_count($reqData->data->view_type);
            echo json_encode(['status' => true, 'data' => $result]);
        } else {
            echo json_encode(['status' => true, 'msg' => 'something went wrong.']);
        }
    }

    public function get_credit_notes_list() {
        $reqData = request_handler('access_finance_credit_note_and_refund');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->get_credit_notes_list($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function get_refund_list() {
        $reqData = request_handler('access_finance_credit_note_and_refund');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->get_refund_list($reqData);
            echo json_encode($result);
            exit();
        }
    }

    public function get_credit_note_view() {
        $reqData = request_handler('access_finance_credit_note_and_refund');
        if (!empty($reqData->data)) {
            $reqData = ($reqData->data);
            $result = $this->Finance_invoice_model->get_credit_note_view($reqData->filtered->creditNoteId);
            echo json_encode($result);
            exit();
        }
    }

    private function block_to_used_status_update_shift_fund_by_shift_id($shiftId = 0) {
        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';
        $objTran = new LineItemTransactionHistory();
        $objTran->setShiftId($shiftId);
        $objTran->block_to_used_status_update_shift_fund_by_shift_id();
    }

// For Plan Management used funds update
    public function update_fund_details_from_plan_mgmt() {
        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';
        $objTran = new LineItemTransactionHistory();
        $fund_used = $this->input->post('fund_used');
        $participant_id = $this->input->post('participant_id');
        $line_item_code = $this->input->post('line_item_code');
        $objTran->setLine_item_fund_used($fund_used);
        $objTran->setUser_plan_line_items_id($line_item_code);
        $objTran->setLine_item_fund_used_type(4);
        $objTran->setLine_item_use_id(0);
        $objTran->setStatus(1);
        $objTran->setArchive(0);
        $result = $objTran->create_history();
        $objTran->update_fund_blocked();
    }

    public function get_line_item_code_participantId() {
        $participant_id = $this->input->post('participant_id');
        $res = $this->Finance_ammount_calculation_model->get_user_other_type_current_plan_line_item_id_by_user_id($participant_id, ['user_type' => 2]);
        $resonse['line_item_code'] = 0;
        if (!empty($res)) {
            $resonse['line_item_code'] = $res['id'];
        }
        echo json_encode($resonse);
    }

    /* public function shift_details_send_to_keyPay_by_shift_details(){
      $shiftId = $this->input->post('shift_id');
      $res =$this->Finance_invoice_model->shift_details_send_to_keyPay_by_shift_details($shiftId);

      } */

    public function get_plan_item_id_by_participantId($participant_id) {
        $res = $this->Finance_invoice_model->get_user_other_type_current_plan_line_item_id_by_participant_id($participant_id, ['user_type' => 2]);
        return $res;
    }

}
