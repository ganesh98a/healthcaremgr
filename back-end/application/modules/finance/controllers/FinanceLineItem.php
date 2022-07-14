<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class FinanceLineItem extends MX_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('Finance_line_item');
        $this->load->model('common/List_view_controls_model');
        $this->load->library('form_validation');
        $this->form_validation->CI = & $this;
        $this->load->library('UserName');
        $this->loges->setLogType('finance_line_item');        
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

    function get_create_line_item_option_and_details() {
        $requestData = request_handler('access_finance_line_item');
        $reqData = $requestData->data;

        $res = [];
        if (!empty($reqData)) {

            $lineItemId = 0;
            if (!empty($reqData->lineItemId)) {
                $lineItemId = (int) $reqData->lineItemId;
                $res = $this->Finance_line_item->get_line_item_details($reqData->lineItemId);
            }

            $res['funding_type_option'] = $this->basic_model->get_record_where('funding_type', ['name as label', 'id as value'], '');
            $res['outcome_domain_option'] = $this->basic_model->get_record_where('finance_support_outcome_domain', ['name', 'id', '"true" as disabled'], '');
            $res['registration_group_option'] = $this->basic_model->get_record_where('finance_support_registration_group', ['name', 'id'], '');
            $res['support_category_option'] = $this->basic_model->get_record_where('finance_support_category', ['name', 'id'], '');
            $res['level_option'] = $this->basic_model->get_record_where('classification_level', ['level_name as label', 'id as value'], ['archive' => 0]);
            $res['point_name_option'] = $this->basic_model->get_record_where('classification_point', ['point_name as label', 'id as value'], ['archive' => 0]);

            $res['time_of_the_days'] = $this->Finance_line_item->get_time_of_day_of_line_item($lineItemId);

            $res['week_days'] = $this->Finance_line_item->get_week_days_of_line_item($lineItemId);

            $res['state'] = $this->Finance_line_item->get_state_of_line_item($lineItemId);

            $res['support_purpose_option'] = $this->basic_model->get_record_where('finance_support_purpose', ['purpose as label', 'id as value'], [ 'archive' => 0 ]);

            $res['support_type_option'] = $this->basic_model->get_record_where('finance_support_type', ['type as label', 'id as value'], [ 'archive' => 0 ]);

            echo json_encode(['status' => true, 'data' => $res]);
        }
    }

    function add_update_line_item() {
        $requestData = request_handler('access_finance_line_item');
        $this->loges->setCreatedBy($requestData->adminId);
        $reqData = $requestData->data;


        if (!empty($reqData)) {
            $reqData->lineItemId = !empty($reqData->lineItemId) ? $reqData->lineItemId : '';

            $validation_rules = array( 
                array('field' => 'funding_type', 'label' => 'funding type', 'rules' => 'required'),
                array('field' => 'line_item_number', 'label' => 'line item number', 'rules' => 'callback_check_line_item_number_already_exist[' . json_encode($reqData) . ']'),
                array('field' => 'line_item_name', 'label' => 'line item name', 'rules' => 'required|max_length[200]'),
                array('field' => 'lineItemId', 'label' => 'line item', 'rules' => 'callback_check_line_item_status_for_update[' . json_encode($reqData) . ']'),
                array('field' => 'support_category', 'label' => 'Support Category', 'rules' => 'required')
            );

            if (!empty($reqData->is_cat) && $reqData->is_cat == false) {
                $validation_rules[] = array('field' => 'units', 'label' => 'units', 'rules' => 'required|numeric');
                $validation_rules[] = array('field' => 'upper_price_limit', 'label' => 'upper price limit', 'rules' => 'required|numeric|less_than_equal_to[99999.99]');
                $validation_rules[] = array('field' => 'start_date', 'label' => 'start date', 'rules' => 'required');
                $validation_rules[] = array('field' => 'end_date', 'label' => 'end date', 'rules' => 'required');
            }

            # validate only if NDIS type
            if (isset($reqData) == true && !empty($reqData->funding_type) && $reqData->funding_type == 1) {
                $validation_rules[] = array('field' => 'support_purpose', 'label' => 'Support Purpose', 'rules' => 'required');
            }

            if (isset($reqData) == true && !empty($reqData->member_ratio)) {
                $validation_rules[] = array('field' => 'member_ratio', 'label' => 'member ratio', 'rules' => 'required|numeric|less_than_equal_to[9]');
            }

            if (isset($reqData) == true && !empty($reqData->participant_ratio)) {
                $validation_rules[] = array('field' => 'participant_ratio', 'label' => 'participant ratio', 'rules' => 'required|numeric|less_than_equal_to[9]');               
            }

            $this->form_validation->set_data((array) $reqData);
            $this->form_validation->set_rules($validation_rules);

            if ($this->form_validation->run()) {
                $startDate = date('Y-m-d', strtotime($reqData->start_date));
                $line_item_number = $reqData->line_item_number;
                $curDate = date('Y-m-d');
                # Check the start date is in past then through error msg for create
                if ($startDate < $curDate && (empty($reqData->lineItemId) || !isset($reqData->lineItemId))) {
                    $response = ['status' => false, 'error' => 'Please enter a future start date for the support item number '.$line_item_number];
                    echo json_encode($response);
                    exit;
                }

                $res = $this->Finance_line_item->check_line_item_number_already_exist($reqData);

                $overwrite = false;
                if (isset($reqData->overwrite)) {
                    $overwrite = filter_var($reqData->overwrite, FILTER_VALIDATE_BOOLEAN);    
                }

                if (!empty($res) && isset($res[0]) && count($res) == 1 && $overwrite == false) {
                    $startDate = date('Y-m-d', strtotime($reqData->start_date));
                    $updateDate = date('d/m/Y', strtotime($startDate.'-1 day'));
                    $lineItem = $res[0];
                    if (strtotime($startDate) > strtotime($lineItem['start_date'])) {
                        $response = ['status' => false, 'overwrite' => true, 'error' => 'An item with the same support item code already exists. The end date of the existing time will be updated as '.$updateDate.'. Are you sure you want to continue?'];
                        echo json_encode($response);
                        exit;
                    } else {
                        $response = ['status' => false, 'error' => 'On current duration another line item number already exist.'];
                        echo json_encode($response);
                        exit;
                    }
                }               
                
                $lineItemId = $this->Finance_line_item->add_update_line_item($reqData, $overwrite, $requestData->adminId, $requestData);

                $txt = (!empty($reqData->lineItemId)) ? "Update line Item number" : "Create New line Item number";
                $this->loges->setTitle($txt . ' : ' . $reqData->line_item_number);
                $this->loges->setUserId($lineItemId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();
                
                $response = ['status' => true];
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }

            echo json_encode($response);
        }
    }

    /**
     * Update Line Item - Aysnc
     * - Shift NDIS Line Item
     */
    function update_price_line_item() {
        $this->load->model(['Finance_line_item']);
        $requestData = (object) $this->input->post('requestData');

        $this->Finance_line_item->update_price_line_item($requestData->adminId);

        exit();
    }

    function check_line_item_status_for_update($lineItemId, $reqData) {
        // here check only for edit line item
        
        if (!empty($lineItemId)) {
            $reqData = json_decode($reqData);
            $res = $this->Finance_line_item->get_line_item_details($lineItemId);

            if (!empty($res['status'])) {

                $start_date = DateFormate($reqData->start_date, 'Y-m-d');
                $end_date = DateFormate($reqData->end_date, 'Y-m-d');

                $prev_start_date = (DateFormate($res['start_date'], 'Y-m-d'));
                $prev_end_date = (DateFormate($res['end_date'], 'Y-m-d'));
                $curr_date = (date('Y-m-d'));

                // check if current line end date or update end date is same then need to show message
                if ($prev_end_date == $curr_date && $curr_date == $end_date) {
                    $this->form_validation->set_message('check_line_item_status_for_update', 'Tomorrow this line item will be archive so its can not be edited');
                    return false;
                }

                if ($res['status'] == 3) {

                    $this->form_validation->set_message('check_line_item_status_for_update', 'Archive line item can not updated');
                    return false;
                } elseif ($res['status'] == 1) {
                    return true;
                } else {
                    return true;
                }
            } else {
                $this->form_validation->set_message('check_line_item_status_for_update', 'Please provide valid line item id');
                return false;
            }
        }

        return true;
    }

    function check_line_item_number_already_exist($line_item_number, $reqData) {
        if (!empty($line_item_number)) {
            $reqData = json_decode($reqData);
            $res = $this->Finance_line_item->check_line_item_number_already_exist($reqData);

            if (empty($res)) {
                return true;
            } else {
                if (!empty($res) && isset($res[0]) && count($res) == 1 && !empty($reqData->start_date)) {
                    $startDate = date('Y-m-d', strtotime($reqData->start_date));
                    $updateDate = date('d/m/Y', strtotime($startDate.'-1 day'));
                    $lineItem = $res[0];
                    if (strtotime($startDate) > strtotime($lineItem['start_date'])) {
                        return true;
                    } else {
                        $this->form_validation->set_message('check_line_item_number_already_exist', 'On current duration another line item number already exist.');
                        return false;
                    }
                } else {
                    if (!empty($reqData->is_cat)) {
                        $this->form_validation->set_message('check_line_item_number_already_exist', 'Parent line item number already exist.');
                        return false;
                    } else {
                        $this->form_validation->set_message('check_line_item_number_already_exist', 'On current duration another line item number already exist.');
                        return false;
                    }
                    
                }
                
            }
        } else {
            $this->form_validation->set_message('check_line_item_number_already_exist', 'Please provide line item number.');
            return false;
        }
    }

    function get_finance_line_item_listing() {
        $reqData = request_handler('access_finance_line_item');

        $filter_condition = '';
        if (isset($reqData->data->tobefilterdata)) {
            $filter_condition = $this->List_view_controls_model->get_filter_logic($reqData);
            if (is_array($filter_condition)) {
                echo json_encode($filter_condition);
                exit();
            }
            if (!empty($filter_condition)) {
                $filter_condition = str_replace(['support_category','support_purpose', 'support_outcome_domain', 'support_type'], ['fli.support_category',
                    'fli.support_purpose', 'fli.support_outcome_domain', 'fli.support_type'], $filter_condition);
            }
        }

        if (!empty($reqData->data)) {
            $result = $this->Finance_line_item->get_finance_line_item_listing($reqData->data, $filter_condition);
            echo json_encode($result);
        }
    }

    function archive_line_item() {
        $requestData = request_handler('access_finance_line_item');
        $this->loges->setCreatedBy($requestData->adminId);
        $reqData = $requestData->data;

        if (!empty($reqData->lineItemId)) {

            $response = $this->Finance_line_item->archive_line_item($reqData->lineItemId);

            if (!empty($response['status'])) {
                $this->loges->setTitle('Archive Line item : ' . $reqData->lineItemId);
                $this->loges->setUserId($reqData->lineItemId);
                $this->loges->setDescription(json_encode($reqData));
                $this->loges->createLog();
            }
        } else {
            $response = ['status' => false, 'error' => "line item id not found"];
        }

        echo json_encode($response);
    }

    function get_finance_line_item_listing_filter_option() {
        request_handler('access_finance_line_item');
        $response = [];
        $response['funding_type_option'] = $this->basic_model->get_record_where('funding_type', ['name as label', 'id as value'], ['archive' => 0]);
        $response['state_option'] = $this->basic_model->get_record_where('state', ['name as label', 'id as value'], ['archive' => 0]);

        echo json_encode(['status' => true, 'data' => $response]);
    }

    /**
     * Support purpose category
     */
    function get_support_purpose_and_outcome_by_category() {
        $reqData = request_handler('access_finance_line_item');
        $this->loges->setCreatedBy($reqData->adminId);
        if (!empty($reqData->data)) {
            $return = $this->Finance_line_item->get_support_purpose_and_outcome_by_category($reqData->data, $reqData->adminId);
        } else {
            $return = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->output->set_output(json_encode($return));
    }

    /**
     * Support purpose category
     */
    function get_support_type_by_category() {
        $reqData = request_handler('access_finance_line_item');
        $this->loges->setCreatedBy($reqData->adminId);
        if (!empty($reqData->data)) {
            $return = $this->Finance_line_item->get_support_type_by_category($reqData->data, $reqData->adminId);
        } else {
            $return = ['status' => false, 'error' => 'Requested data is null'];
        }
        return $this->output->set_output(json_encode($return));
    }

    /*
     * fetching the line item support data for filters
     */
    function get_finance_line_item_filter_data() {
        $reqData = request_handler();
        if (!empty($reqData->adminId)) {
            $result = $this->Finance_line_item->get_finance_line_item_filter_data();
        } else {
            $result = ['status' => false, 'error' => 'Requested data is null'];
        }
        echo json_encode($result);
        exit();
    }

}
