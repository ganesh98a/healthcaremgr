<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require_once('Finance_line_item.php');

class Finance_import_meta_data_w_price extends CI_Model {
    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();

        $this->import_header = [
            "line_item_number" => "SupportItemNumber",
            "support_registration_group" => "RegistrationGroup",
            "support_outcome_domain" => "OutcomeDomainID_p4",
            "line_item_name" => "SupportItem",
            "support_type" => "SupportType",
            "needs" => "Needs",
            "day_of_week" => "DayOfWeek",
            "time_of_day" => "TimeOfDay",
            "description" => "SupportItemDescription",
            "units" => "UnitOfMeasure",
            "quote_required" => "Quote",
            "price_control" => "PriceControl",
            "weekday" => "Weekday",
            "saturday" => "Saturday",
            "sunday" => "Sunday",
            "public_holiday" => "PublicHoliday",
            "sleepover" => "Sleepover",
            "overnight" => "Overnight",
            "daytime" => "Daytime",
            "evening" => "Evening",
            "non_f2f" => "Non_F2F",
            "oncall_provided" => "OncallApproved2019_20",
            "travel_required" => "Travel",
            "cancellation_fees" => "Cancellations",
            "ndis_reporting" => "NDIA_Reporting",
            "support_category" => "SupportCategoryID_p1",
            "upper_price_limit" => "Price",
            "start_date" => "ValidFrom",
            "end_date" => "ValidTo",
        ];

        $this->header_required_fields = [
            "SupportItemNumber",
        ];

        $this->weekOfDay = [
            "weekday",
            "saturday",
            "sunday",
            "public_holiday"
        ];

        $this->timeOfDay = [
            "daytime",
            "evening",
            "sleepover",
            "overnight"
        ];

        $days_applied = "Day of Week";
        $time_applied = "Time Of Day";
        $this->line_item = [
            "funding_type" => "Funding Type",
            "support_registration_group" => "Support Registeration Group",
            "support_category" => "Support Category",
            "support_purpose" => "Support Purpose",
            "support_type" => "Support Type",
            "support_outcome_domain" => "Support Outcome Domain",
            "line_item_number" => "Line Item Number", 
            "line_item_name" => "Line Item Name",
            "category_ref" => "Category Ref", 
            "description" => "Description", 
            "quote_required" => "Quote Required", 
            "price_control" => "Price Control", 
            "travel_required" => "Travel Required", 
            "cancellation_fees" => "Cancellation Fees", 
            "ndis_reporting" => "NDIS Reporting", 
            "non_f2f" => "None f2f", 
            "levelId" => "Level", 
            "pay_pointId" => "Pay Ponit", 
            "units" => "Units", 
            "schedule_constraint" => 
            "Schedule Constratint", 
            "member_ratio" => "Member Ratio", 
            "participant_ratio" => "Participant Ratio", 
            "measure_by" => "Measure By", 
            "oncall_provided" => "Oncall Provided", 
            "weekday" => $days_applied, 
            "saturday" => $days_applied, 
            "sunday" => $days_applied, 
            "daytime" => $time_applied, 
            "evening" => $time_applied, 
            "overnight" => $time_applied, 
            "sleepover" => $time_applied, 
            "public_holiday" => $days_applied,
            "upper_price_limit" => "Price", 
            "start_date" => "Start Date", 
            "end_date" => "End Date", 
        ];
    }

    function insert_bulk_import_line_item($data, $adminId) {        
        $line_item_id = 0;    
        $list_item = $data;
        
        unset($list_item['weekofday'], $list_item['timeofday'], $list_item['start_date'], 
        $list_item['end_date'], $list_item['upper_price_limit']);

        #Skip the existing Line item values to skip the duplicate entry
        if(empty($data['line_item_id'])) {
            $list_item['created_by'] = $adminId;
            $this->db->insert('tbl_finance_line_item', $list_item);
            $line_item_id = $this->db->insert_id();
        } else {
            $line_item_id = $data['line_item_id'];
        }
        
        if ($line_item_id > 0) {
            #Insert line item price details
            $item_price_detail = [];
            $item_price_detail['line_item_id'] = $line_item_id;
            $item_price_detail['start_date'] = $data['start_date'];
            $item_price_detail['end_date'] = $data['end_date'];
            $item_price_detail['upper_price_limit'] = $data['upper_price_limit'];
            $item_price_detail['created_at'] = DATE_TIME;
            $item_price_detail['created_by'] = $adminId;

            $this->db->insert('tbl_finance_line_item_price', $item_price_detail);
        }

        return;
    }

    /**
     * Get data from sheet
     * @param {str} $tmp_f_name
     */
    function read_data_from_data_sheet($tmp_f_name, $overwrite_item, $adminId) {
        # library
        $this->load->library("Excel");
        mb_convert_encoding($tmp_f_name, 'UTF-16LE', 'UTF-8');
        $this->excel->setWorkSheet(0);
        $this->excel->setTmpFileName($tmp_f_name);
        $data = $this->excel->read_data_from_file();
        
        $rows = $data['rows'] ?? [];
        $total_column = $data['total_column'] ?? 0;
        $total_rows = $data['total_row'] ?? 0;

        if (empty($rows)) {
            return ['status' => false, 'error' => 'Invalid data in uploaded csv file'];
        }

        # validate columns
        $colValidation = $this->validate_column($rows);

        if (isset($colValidation) == true && $colValidation['status'] == false) {
            return $colValidation;
        }

        # validate columns
        $dataValidation = $this->validate_data($rows, $overwrite_item, $adminId);

        if ($dataValidation && isset($dataValidation['status']) && ($dataValidation['status'] == true || $overwrite_item == true)) {
            # Insert or update
            $data = $dataValidation['data'] ?? [];
            
            $result = $this->data_import($data, $adminId);

            if ($result == true) {      
                # Async API used to update the shift ndis line item price once price line item added with date range. 
                # Shift ndis line item price will be updated  only the line_item_price_id is null or 0 and date range or met with schedule and actual date range.

                $this->load->library('Asynclibrary');
                $url = base_url()."finance/FinanceLineItem/update_price_line_item";
                $param = array('data' => $data, 'adminId' => $adminId);
                $param['requestData'] = $param;
                $this->asynclibrary->do_in_background($url, $param);          
                $result = [ 'status' => true, 'msg' => 'File data imported successfully' ];
            } else {
                $result = [ 'status' => false, 'msg' => 'Import failed. Please try again' ];
            }
            
        } else {
            $result = $dataValidation;
        }

        return $result;
    }

    /**
     * Data Import
     * @param {array} $DbData
     */
    function data_import($DbData, $adminId) {
        $this->load->model(['Finance_import_export_line_item']);
        $status_row = [];
        $this->db->trans_start();
        foreach ($DbData as $key => $data) {
            $valid_table_response = $this->Finance_import_export_line_item->validate_csv_import_data_between_date($data);

            # allow to add new line item for HCM-8110
            if (!empty($valid_table_response)) {
                if (count($valid_table_response) == 1 && isset($valid_table_response[0])) {
                    $lineItem = $valid_table_response[0];
                    if (strtotime($data['start_date']) == strtotime($lineItem['start_date']) && strtotime($data['end_date']) >= strtotime($lineItem['end_date'])) {
                        # update end date
                        $line_item_price_id = $lineItem['id'];
                        $tmplineItem = [];
                        $tmplineItem['end_date'] = date('Y-m-d', strtotime($data['end_date']));
                        $tmplineItem['upper_price_limit'] = $data['upper_price_limit'];
                        $tmplineItem['updated_at'] = DATE_TIME;
                        $tmplineItem['updated_by'] = $adminId;

                        $this->basic_model->update_records('finance_line_item_price', $tmplineItem, ['id' => $line_item_price_id]);
                    }
                    else if (isset($lineItem) && (strtotime($data['start_date']) > strtotime($lineItem['start_date']))) { 
                        # update end date
                        $line_item_price_id = $lineItem['id'];
                        $tmplineItem = [];
                        $tmplineItem['end_date'] = date('Y-m-d', strtotime($data['start_date'].'-1 day'));
                        $tmplineItem['updated_at'] = DATE_TIME;
                        $tmplineItem['updated_by'] = $adminId;

                        $this->basic_model->update_records('finance_line_item_price', $tmplineItem, ['id' => $line_item_price_id]);

                        # add new line item
                        $status_row[$key] = $this->insert_bulk_import_line_item($data, $adminId);
                    }
                }
            } else {
                # check if line item exist with same date
                $checkVal = $this->Finance_import_export_line_item->validate_import_data_between_date($data);
                $line_item_number = $checkVal->id ?? '';
                $line_item_price_id = $checkVal->line_item_price_id ?? '';

                if ($line_item_number == '') {
                    $status_row[$key] = $this->insert_bulk_import_line_item($data, $adminId);
                } else if ($line_item_number != '' && $line_item_price_id == '') {
                    $data['line_item_id'] = $line_item_number;
                    $status_row[$key] = $this->insert_bulk_import_line_item($data, $adminId);
                } else {                    
                    $data['line_item_id'] = $line_item_number;
                    $data['line_item_price_id'] = $line_item_price_id;
                    $status_row[$key] = $this->update_bulk_import_line_item($data, $adminId);
                }
            }
        }
        
        $this->db->trans_complete();
        if ($this->db->trans_status() === FALSE) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            # Everything is Perfect. 
            # Committing data to the database.
            $this->db->trans_commit();
            return true;
        }
    }

    /**
     * Update Line Item
     */
    function update_bulk_import_line_item($data,$adminId) {
        $line_item_id = 0;
        $this->load->model('Finance_line_item');
           
        $list_item = $data;
        
        $line_item_id = $data['line_item_id'];
        $line_item_price_id = $data['line_item_price_id'];

        unset($list_item['line_item_id'], $list_item['line_item_price_id'], $list_item['weekofday'], $list_item['timeofday'], $list_item['start_date'], 
        $list_item['end_date'], $list_item['upper_price_limit']);
        $list_item['updated_by'] = $adminId;
        $list_item['updated_at'] = DATE_TIME;
        $this->basic_model->update_records('finance_line_item', $list_item, ['id' => $line_item_id]);

        if ($line_item_price_id > 0) {
            #Insert line item price details
            $item_price_detail = [];            
            $item_price_detail['start_date'] = $data['start_date'];
            $item_price_detail['end_date'] = $data['end_date'];
            $item_price_detail['upper_price_limit'] = $data['upper_price_limit'];
            $item_price_detail['updated_at'] = DATE_TIME;
            $item_price_detail['updated_by'] = $adminId;

            $this->basic_model->update_records('finance_line_item_price', $item_price_detail, ['id' => $line_item_price_id]);
        }
    }

    /**
     * Validate data from data sheet
     * @param {array} $rows
     */
    function validate_data($rows, $overwrite_item, $adminId) {
        
        $this->load->model([ 'Finance_line_item', 'Finance_import_export_line_item']);
        $weekOfdays = $this->get_week_day_list();
        $timeOfdays = $this->get_time_of_day_list();
        $fundingType = $this->get_funding_type_list();
        $supportCategory = $this->get_support_category_list();
        $supportCategoryByName = $this->get_support_category_list_by_name();
        $supportOutcomeDomain = $this->get_support_outcome_domain_list();
        $supportOutcomeDomainByName = $this->get_support_outcome_domain_list_by_name();
        $supportRegistrationGroup = $this->get_support_registration_group_list();
        $supportRegistrationGroupByName = $this->get_support_registration_group_list_by_name();
        $measure_list = $this->get_measured_by_list();
        $purpose = $this->get_purpose_list();
        $purposeMapping = $this->get_purpose_mapping_list();
        $supportType = $this->get_support_type_list();

        # Get header columns from sheet
        $header = array_shift($rows);
        $raw_header = $header;
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        sort($col_header);

        $line_item_data = [];
        $line_item_invalid = [];
        $ct_in = 1;
        $overwrite = false;
        $bulk_insert_array = [];
        $totalRows = $overwriteRow = 0;
        foreach ($rows as $row) {
            $ct_in++;
            $validateEmpty = array_filter(array_map('trim', $row), 'strlen');
            if (empty($validateEmpty)) {
                continue;
            }
            $arrayTemp = [];
            $insert_array = [];
            // $row = array_map("utf8_encode", $row);            
            $arrayTemp = array_combine($header, $row);

            $line_item_data[] = $arrayTemp;
            
            # validate support item number
            $in_su_item_number = strtolower(trim($this->import_header['line_item_number']));
            $support_item_no = trim($arrayTemp[$in_su_item_number]);
            $value = $support_item_no;
            $category_prefix = $sec_prefix = $register_group_prefix = $outcome_prefix = $purpose_prefix = '';
            if ($value != '' && $value != null && $value !='null') {
                $line_item_ex = explode('_', $support_item_no);
                $category_prefix = $line_item_ex[0] ?? '';
                $sec_prefix = $line_item_ex[1] ?? '';
                $register_group_prefix = $line_item_ex[2] ?? '';
                $outcome_prefix = $line_item_ex[3] ?? '';
                $purpose_prefix = $line_item_ex[4] ?? '';
                $insert_array['line_item_number'] = $value;
            } else {
                $insert_array['line_item_number'] = '';
                $header_name = $this->get_raw_header_err_index($in_su_item_number, $header, $raw_header);
                $line_item_invalid[$header_name]['header'] = $header_name;
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['options'] = [];
                $line_item_invalid[$header_name]['error'] = [ $header_name.' cannot be empty' ];
            }

            $priceListLI = $this->check_li_price_exist_validation((object)$insert_array);

            if (isset($priceListLI['line_item_id'])) {
                $insert_array['line_item_id']  = $priceListLI['line_item_id'];
            }
            # validate outcome domain
            if ($outcome_prefix != '') {
                $outcome_prefix = intVal($outcome_prefix);
            } else {
                if ($category_prefix != '') {
                    # get support outcome
                    $support_outcome_domain = $this->Finance_line_item->get_support_outcome_id_by_category($category_prefix, '');
                    $outcome_prefix = intVal($support_outcome_domain);
                }
            }

            $in_domain = strtolower(trim($this->import_header['support_outcome_domain']));
            $domain = 'support_outcome_domain';
            $value = strtolower(trim($arrayTemp[$in_domain]));
            if (isset($supportOutcomeDomainByName[$value]) && !empty($supportOutcomeDomainByName[$value])) {
                $insert_array['support_outcome_domain'] = $supportOutcomeDomainByName[$value];
            } else if (isset($supportOutcomeDomain[$outcome_prefix]) && !empty($supportOutcomeDomain[$outcome_prefix])) {
                $insert_array['support_outcome_domain'] = $supportOutcomeDomain[$outcome_prefix];
            } else if (isset($supportOutcomeDomain[$value]) && !empty($supportOutcomeDomain[$value])) {
                $insert_array['support_outcome_domain'] = $supportOutcomeDomain[$value];
            } else {
                $header_name = $this->get_raw_header_err_index($in_domain, $header, $raw_header);
                $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($supportOutcomeDomainByName)));
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            }

            # validate registration group
            if ($register_group_prefix != '') {
                $register_group_prefix = intVal($register_group_prefix);
            }

            $in_reg_grp = strtolower(trim($this->import_header['support_registration_group']));
            $reg_group = $arrayTemp[$in_reg_grp];
            $value = strtolower(trim($reg_group));
            if (isset($supportRegistrationGroupByName[$value]) && !empty($supportRegistrationGroupByName[$value])) {
                $insert_array['support_registration_group'] = $supportRegistrationGroupByName[$value];
            } else if (isset($supportRegistrationGroup[$register_group_prefix]) && !empty($supportRegistrationGroup[$register_group_prefix])) {
                $insert_array['support_registration_group'] = $supportRegistrationGroup[$register_group_prefix];
            } else {
                $header_name = $this->get_raw_header_err_index($in_reg_grp, $header, $raw_header);
                $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($supportRegistrationGroupByName)));
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            }

            # validate category
            if ($category_prefix != '') {
                $category_prefix = intVal($category_prefix);
            }

            $in_su_cat = strtolower(trim($this->import_header['support_category']));
            $support_categories =  $arrayTemp[$in_su_cat];
            $value = strtolower(trim($support_categories));
            # validate category
            if ($category_prefix == '') {
                $category_prefix = intVal($value);
            }
            if (isset($supportCategoryByName[$value]) && !empty($supportCategoryByName[$value])) {
                $insert_array['support_category'] = $supportCategoryByName[$value];
            } else if (isset($supportCategory[$category_prefix]) && !empty($supportCategory[$category_prefix])) {
                $category = $supportCategory[$category_prefix];
                $insert_array['support_category'] = $category;

                # get purpose using mapping
                $support_purpose = $this->Finance_line_item->get_support_purpose_by_category($category, '');
                if ($support_purpose != '' && $support_purpose != null && $support_purpose !='null') {
                    $insert_array['support_purpose'] = $support_purpose;
                }

            } else {
                $header_name = $this->get_raw_header_err_index('support categories', $header, $raw_header);
                $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($supportCategoryByName)));
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            }

            # validate support item
            $in_li_name = strtolower(trim($this->import_header['line_item_name']));
            $reg_group = $arrayTemp[$in_li_name];
            $value = strtolower(trim($reg_group));
            if ($value != '' && $value != null && $value !='null') {
                $insert_array['line_item_name'] = $value;

                # get support type using mapping
                $reqData = [];
                $reqData['support_category'] = $insert_array['support_category'] ?? '';
                $reqData['line_item_name'] = $value;

                $supportTypeAr = $this->Finance_line_item->get_support_type_by_category((object) $reqData, '');
                if (!empty($supportTypeAr) && $supportTypeAr['status'] == true && !empty($supportTypeAr['support_type'])) {
                    $insert_array['support_type'] = $supportTypeAr['support_type'];
                } else {
                    $insert_array['support_type'] = '0';
                }
            } else {
                $insert_array['support_type'] = '0';
            }

            # optional - needs
            $in_needs = strtolower(trim($this->import_header['needs']));
            $needs = $arrayTemp[$in_needs] ?? '';
            $value = strtolower(trim($needs));
            $insert_array['needs'] = '';
            if ($value != '' && $value != null && $value !='null') {
                $insert_array['needs'] = $value;
            }

            # optional - description
            $in_descrip = strtolower(trim($this->import_header['description']));
            $item_description = $arrayTemp[$in_descrip] ?? '';
            $value = strtolower(trim($item_description));
            if ($value != '' && $value != null && $value !='null') {
                $insert_array['description'] = $value;
            }

            # validate unit of measure
            $validateUnit = $this->validate_unit_data($arrayTemp, $header, $raw_header);

            if ((empty($validateUnit) || $validateUnit['status'] == false)) {
                $header_name = $validateUnit['data'];
                $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($measure_list)));
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            } else {
                $insert_array['units'] = $validateUnit['data'];
            }

            # optional - quote
            $validateQuote = $this->validate_quote_data($arrayTemp, $header, $raw_header);

            if (!empty($validateQuote['data_available']) && $validateQuote['data_available'] == true) {
                if (empty($validateQuote) || $validateQuote['status'] == false) {
                    $header_name = $validateQuote['data'];
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {                
                    $insert_array['quote_required'] = $validateQuote['data'];
                }
            }

            # optional - oncall approved
            $in_oncall_approved = strtolower(trim($this->import_header['oncall_provided']));
            $validateOncallAp = $this->validate_true_or_false_data($in_oncall_approved, $arrayTemp, $header, $raw_header);
            if (!empty($validateOncallAp['data_available']) && $validateOncallAp['data_available'] == true) {
                if (empty($validateOncallAp) || $validateOncallAp['status'] == false) {
                    $header_name = $validateOncallAp['data'];
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['oncall_provided'] = $validateOncallAp['data'];
                }
            }

            # validate the price control
            $in_price_con = strtolower(trim($this->import_header['price_control']));
            $validatePriceControll = $this->validate_true_or_false_data($in_price_con, $arrayTemp, $header, $raw_header);
            if (!empty($validatePriceControll['data_available']) && $validatePriceControll['data_available'] == true) {
                if (empty($validatePriceControll) || $validatePriceControll['status'] == false) {
                    $header_name = $validatePriceControll['data'];
                    
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['price_control'] = $validatePriceControll['data'];
                }
            }

            # validate the nonf2f
            $in_non_f2f = strtolower(trim($this->import_header['non_f2f']));
            $validatePriceControll = $this->validate_true_or_false_data($in_price_con, $arrayTemp, $header, $raw_header);
            if (!empty($validatePriceControll['data_available']) && $validatePriceControll['data_available'] == true) {
                if (empty($validatePriceControll) || $validatePriceControll['status'] == false) {
                    $header_name = $validatePriceControll['data'];
                    
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['non_f2f'] = $validatePriceControll['data'];
                }
            }

            # validate the ndis_reporting
            $in_ndis_reporting = strtolower(trim($this->import_header['ndis_reporting']));
            $validateNDISRep = $this->validate_true_or_false_data($in_ndis_reporting, $arrayTemp, $header, $raw_header);
            if (!empty($validateNDISRep['data_available']) && $validateNDISRep['data_available'] == true) {
                if (empty($validateNDISRep) || $validateNDISRep['status'] == false) {
                    $header_name = $validateNDISRep['data'];
                    
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['ndis_reporting'] = $validateNDISRep['data'];
                }
            }

            # validate the ndis_reporting
            $in_can_fees = strtolower(trim($this->import_header['cancellation_fees']));
            $validateCanFees = $this->validate_true_or_false_data($in_can_fees, $arrayTemp, $header, $raw_header);
            if (!empty($validateCanFees['data_available']) && $validateCanFees['data_available'] == true) {
                if (empty($validateCanFees) || $validateCanFees['status'] == false) {
                    $header_name = $validateCanFees['data'];
                    
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['cancellation_fees'] = $validateCanFees['data'];
                }
            }

            # validate the travel_required
            $in_travel_required = strtolower(trim($this->import_header['travel_required']));
            $validateTravelReq = $this->validate_true_or_false_data($in_travel_required, $arrayTemp, $header, $raw_header);
            if (!empty($validateTravelReq['data_available']) && $validateTravelReq['data_available'] == true) {
                if (empty($validateTravelReq) || $validateTravelReq['status'] == false) {
                    $header_name = $validateTravelReq['data'];
                    
                    $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $insert_array['travel_required'] = $validateTravelReq['data'];
                }
            }

            $arr_week = array_values($weekOfdays);
            $arr_daytime = array_values($timeOfdays);

            # optional - week of day
            $in_day_of_week = strtolower(trim($this->import_header['day_of_week']));
            $validateWeekDay = $this->validate_week_of_day($in_day_of_week, $arrayTemp, $header, $raw_header);
            if (!empty($validateWeekDay['data_available']) && $validateWeekDay['data_available'] == true) {
                if (empty($validateWeekDay) || $validateWeekDay['status'] == false) {
                    $header_name = $validateWeekDay['data'];
                    $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($weekOfdays)));
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $weekofday = $validateWeekDay['data'];
                    $weekofday = array_values($weekofday);
                    # find difference & fill value as 0
                    $diffDay = array_diff($this->weekOfDay,$weekofday);
                    $diffDay = array_fill_keys($diffDay, 0);

                    # fill all the values as 1
                    $weekofday = array_fill_keys($weekofday, 1);

                    # merge difference weekday from user input
                    if (!empty($diffDay)) {
                        $weekofday = array_merge($weekofday, $diffDay);
                    }

                    if (!empty($weekofday)) {
                        $insert_array = array_merge($insert_array,$weekofday);
                    }
                }
            } else {
                # validate the weekday
                $in_weekday = strtolower(trim($this->import_header['weekday']));
                $insert_array['weekday'] = 0;
                $validateDay = $this->validate_true_or_false_data($in_weekday, $arrayTemp, $header, $raw_header);
                if (!empty($validateDay['data_available']) && $validateDay['data_available'] == true) {
                    if (empty($validateDay) || $validateDay['status'] == false) {
                        $header_name = $validateDay['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['weekday'] = $validateDay['data'];
                    }
                }

                # validate the saturday
                $in_saturday = strtolower(trim($this->import_header['saturday']));
                $insert_array['saturday'] = 0;
                $validateDay = $this->validate_true_or_false_data($in_saturday, $arrayTemp, $header, $raw_header);
                if (!empty($validateDay['data_available']) && $validateDay['data_available'] == true) {
                    if (empty($validateDay) || $validateDay['status'] == false) {
                        $header_name = $validateDay['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['saturday'] = $validateDay['data'];
                    }
                }

                # validate the sunday
                $in_sunday = strtolower(trim($this->import_header['sunday']));
                $insert_array['sunday'] = 0;
                $validateDay = $this->validate_true_or_false_data($in_sunday, $arrayTemp, $header, $raw_header);
                if (!empty($validateDay['data_available']) && $validateDay['data_available'] == true) {
                    if (empty($validateDay) || $validateDay['status'] == false) {
                        $header_name = $validateDay['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['sunday'] = $validateDay['data'];
                    }
                }

                # validate the public_holiday
                $in_public_holiday = strtolower(trim($this->import_header['public_holiday']));
                $insert_array['public_holiday'] = 0;
                $validateDay = $this->validate_true_or_false_data($in_public_holiday, $arrayTemp, $header, $raw_header);
                if (!empty($validateDay['data_available']) && $validateDay['data_available'] == true) {
                    if (empty($validateDay) || $validateDay['status'] == false) {
                        $header_name = $validateDay['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['public_holiday'] = $validateDay['data'];
                    }
                }
            }

            # optional - time of day
            $in_time_of_day = strtolower(trim($this->import_header['time_of_day']));
            $validatTimeDay = $this->validate_time_of_day($in_time_of_day, $arrayTemp, $header, $raw_header);
            if (!empty($validatTimeDay['data_available']) && $validatTimeDay['data_available'] == true) {
                if (empty($validatTimeDay) || $validatTimeDay['status'] == false) {
                    $header_name = $validatTimeDay['data'];
                    $line_item_invalid[$header_name]['options'] = array_values(array_map("ucwords", array_flip($timeOfdays)));
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $timeofday = $validatTimeDay['data'];
                    $timeofday = array_values($timeofday);
                    # find difference & fill value as 0
                    $diffTime = array_diff($this->timeOfDay,$timeofday);
                    $diffTime = array_fill_keys($diffTime, 0);

                    # fill all the values as 1
                    $timeofday = array_fill_keys($timeofday, 1);

                    # merge difference weekday from user input
                    if (!empty($diffTime)) {
                        $timeofday = array_merge($timeofday, $diffTime);
                    }
                    
                    if (!empty($timeofday)) {
                        $insert_array = array_merge($insert_array,$timeofday);
                    }
                }
            } else {
                # validate the daytime
                $in_daytime = strtolower(trim($this->import_header['daytime']));
                $validateTime = $this->validate_true_or_false_data($in_daytime, $arrayTemp, $header, $raw_header);
                $insert_array['daytime'] = 0;
                if (!empty($validateTime['data_available']) && $validateTime['data_available'] == true) {
                    if (empty($validateTime) || $validateTime['status'] == false) {
                        $header_name = $validateTime['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['daytime'] = $validateTime['data'];
                    }
                }

                # validate the evening
                $in_evening = strtolower(trim($this->import_header['evening']));
                $insert_array['evening'] = 0;
                $validateTime = $this->validate_true_or_false_data($in_evening, $arrayTemp, $header, $raw_header);
                if (!empty($validateTime['data_available']) && $validateTime['data_available'] == true) {
                    if (empty($validateTime) || $validateTime['status'] == false) {
                        $header_name = $validateTime['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['evening'] = $validateTime['data'];
                    }
                }

                # validate the overnight
                $in_overnight = strtolower(trim($this->import_header['overnight']));
                $insert_array['overnight'] = 0;
                $validateTime = $this->validate_true_or_false_data($in_overnight, $arrayTemp, $header, $raw_header);
                if (!empty($validateTime['data_available']) && $validateTime['data_available'] == true) {
                    if (empty($validateTime) || $validateTime['status'] == false) {
                        $header_name = $validateTime['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['overnight'] = $validateTime['data'];
                    }
                }

                # validate the sleepover
                $in_sleepover = strtolower(trim($this->import_header['sleepover']));
                $insert_array['sleepover'] = 0;
                $validateTime = $this->validate_true_or_false_data($in_sleepover, $arrayTemp, $header, $raw_header);
                if (!empty($validateTime['data_available']) && $validateTime['data_available'] == true) {
                    if (empty($validateTime) || $validateTime['status'] == false) {
                        $header_name = $validateTime['data'];
                        
                        $line_item_invalid[$header_name]['options'] = [ 'Y', 'YES', 'TRUE', 'N', 'NO', 'FALSE' ];
                        $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid' ];
                        $line_item_invalid[$header_name]['rows'][] = $ct_in;
                        $line_item_invalid[$header_name]['header'] = $header_name;
                    } else {
                        $insert_array['sleepover'] = $validateTime['data'];
                    }
                }
            }

            # validate start date
            $in_start_date = strtolower(trim($this->import_header['start_date']));
            $validateStartDate = $this->checkIsAValidDate($in_start_date, $arrayTemp, $header, $raw_header);
            if ((empty($validateStartDate) || $validateStartDate['status'] == false)) {
                $header_name = $validateStartDate['data'];
                $line_item_invalid[$header_name]['options'] = [ 'DD/MM/YYYY', 'DD-MM-YYYY' ];
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            } else {
                $startDate = $insert_array['start_date'] = $validateStartDate['data'];
                # Check the start date is in past then through error msg
                $curDate = date('Y-m-d');
                /* if ($startDate < $curDate) {
                    $li_no = $insert_array['line_item_number'] ?? '';
                    $header_name = 'Start Date - is past';
                    $line_item_invalid[$header_name]['options'] = [ 'DD/MM/YYYY', 'DD-MM-YYYY' ];
                    $line_item_invalid[$header_name]['error'] = [ 'Please provide a future start date for the below support item number ' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in . ' - ('.$li_no.')';
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } */
            }

            # validate end date
            $in_end_date = strtolower(trim($this->import_header['end_date']));
            $validateEndDate = $this->checkIsAValidDate($in_end_date, $arrayTemp, $header, $raw_header);
            if ((empty($validateEndDate) || $validateEndDate['status'] == false)) {
                $header_name = $validateEndDate['data'];
                $line_item_invalid[$header_name]['options'] = [ 'DD/MM/YYYY', 'DD-MM-YYYY' ];
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            } else {
                $insert_array['end_date'] = $validateEndDate['data'];
            }                

            # validate the date
            if (!empty($insert_array['start_date']) && !empty($insert_array['end_date']) && strtotime($insert_array['start_date']) > strtotime($insert_array['end_date'])) {
                $header_name = $this->get_raw_header_err_index('start date', $header, $raw_header);
                if (!empty($line_item_invalid[$header_name])) {
                    $line_item_invalid[$header_name]['options'] = [ 'DD/MM/YYY', 'DD-MM-YYYY' ];
                    $line_item_invalid[$header_name]['error'][] = $header_name.' must be lesser than end date';
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                } else {
                    $line_item_invalid[$header_name]['options'] = [ 'DD/MM/YYY', 'DD-MM-YYYY' ];
                    $line_item_invalid[$header_name]['error'] = [ $header_name.' must be lesser than end date' ];
                    $line_item_invalid[$header_name]['rows'][] = $ct_in;
                    $line_item_invalid[$header_name]['header'] = $header_name;
                }
                
            }

            # validate price limit
            $in_upper_price_limit = strtolower(trim($this->import_header['upper_price_limit']));
            $validateEndDate = $this->Finance_import_export_line_item->validate_price_data($in_upper_price_limit, $arrayTemp, $header, $raw_header);
            if (empty($validateEndDate) || $validateEndDate['status'] == false) {
                $header_name = $validateEndDate['data'];
                $line_item_invalid[$header_name]['options'] = [];
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is invalid or cannot be empty' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            } else {
                $insert_array['upper_price_limit'] = $validateEndDate['data'];
            }

            # Default funding type set NDIS=1
            $insert_array['funding_type'] = $fundingType['ndis'];
            $insert_array['measure_by'] = 1;

            $arrValidDate = $this->multiSearch($bulk_insert_array, array('line_item_number' => $insert_array['line_item_number']));
            
            if (!empty($arrValidDate)) {
                $in_line_item_number = strtolower(trim($this->import_header['line_item_number']));
                $header_name = $this->get_raw_header_err_index($in_line_item_number, $header, $raw_header);
                $line_item_invalid[$header_name]['options'] = [];
                $line_item_invalid[$header_name]['error'] = [ $header_name.' is duplicated' ];
                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                $line_item_invalid[$header_name]['header'] = $header_name;
            } else {    
                if (!empty($insert_array['start_date']) && !empty($insert_array['end_date'])) {
                
                    $valid_table_response = $this->Finance_import_export_line_item->validate_csv_import_data_between_date($insert_array, false);
                    $comInArr = $insert_array;
                    unset($comInArr['weekofday'], $comInArr['timeofday'], $comInArr['start_date'], $comInArr['end_date'], $comInArr['upper_price_limit'], $comInArr['funding_type'], $comInArr['measure_by']);
                    
                    $priceListLI = $this->Finance_line_item->check_li_price_exist_validation((object)$insert_array);

                    # validate line item data mis-matched with existing and provided
                    $mismatchField = [];
                    if (!empty($priceListLI)) {
                        if (isset($priceListLI['line_item_id'])) {
                            $insert_array['line_item_id']  = $priceListLI['line_item_id'];
                        }
                        
                        # Get the mis-matched fields by value
                        foreach($comInArr as $lkey => $item) {
                            if(isset($priceListLI[$lkey]) && strtolower(trim($comInArr[$lkey])) != strtolower(trim($priceListLI[$lkey]))) {
                                $mismatchField[] = $this->line_item[$lkey];
                            }
                        }
                        if (isset($mismatchField) && !empty($mismatchField)) {
                            $mismatchField = array_unique($mismatchField);
                            $misMatchFieldStr = implode(', ', $mismatchField);
                            $response = ['status' => false, 'error' => 'Existing Line Item data mis-matched. Please provide same value - '. $misMatchFieldStr];

                            $header_name = 'Data Mis-Matched';
                            $line_item_invalid[$header_name]['options'] = [];
                            $line_item_invalid[$header_name]['error'] = ['Existing Line Item data mis-matched. Please provide same value' ];
                            $line_item_invalid[$header_name]['rows'][] = $ct_in. ' ('.$misMatchFieldStr.')';
                            $line_item_invalid[$header_name]['header'] = $header_name;
                        }
                    }

                    # allow to add new line item for HCM-8110
                    if (!empty($valid_table_response)) {
                        $msg = ' already exists in the system for the specified dates is overlapping Start and End date';
                        if (count($valid_table_response) == 1 && isset($valid_table_response[0])) {
                            $lineItem = $valid_table_response[0];
                            if (strtotime($insert_array['start_date']) == strtotime($lineItem['start_date']) && strtotime($insert_array['end_date']) >= strtotime($lineItem['end_date'])) {
                                $insert_array['line_item_id']  = $lineItem['line_item_id'];
                                $bulk_insert_array[] = $insert_array;
                            } else if (strtotime($insert_array['start_date']) > strtotime($lineItem['start_date']) ) {
                                $insert_array['line_item_id']  = $lineItem['line_item_id'];
                                $bulk_insert_array[] = $insert_array;
                                $overwrite = true;
                                $overwriteRow++;
                            } else {
                                $header_name = $this->get_raw_header_err_index('support item number', $header, $raw_header);
                                $line_item_invalid[$header_name]['options'] = [];
                                $line_item_invalid[$header_name]['error'] = [ $header_name.$msg ];
                                $line_item_invalid[$header_name]['rows'][] = $ct_in;
                                $line_item_invalid[$header_name]['header'] = $header_name;
                            }
                        } else {                            
                            $header_name = $this->get_raw_header_err_index('support item number', $header, $raw_header);
                            $line_item_invalid[$header_name]['options'] = [];
                            $line_item_invalid[$header_name]['error'] = [ $header_name.$msg ];
                            $line_item_invalid[$header_name]['rows'][] = $ct_in;
                            $line_item_invalid[$header_name]['header'] = $header_name;
                        }
                    } else {
                        $bulk_insert_array[] = $insert_array;
                    }
                }
            }
            $totalRows++;
        }
        
        if (empty($line_item_invalid) && !$overwrite) {
            $return = [ 'status' => true, 'data' => $bulk_insert_array, 'overwrite' => $overwrite, 'total_rows' => $totalRows, 'overwrite_rows' => $overwriteRow  ];
        } else {
            if (count($line_item_invalid) > 0 || !empty($line_item_invalid)) {
                $overwrite = false;
            }

            $return = [ 'status' => false, 'error_data' => array_values($line_item_invalid), 'error' => 'Unsuccessful file import.', 'overwrite' => $overwrite, 'total_rows' => $totalRows, 'overwrite_rows' => $overwriteRow, 'data' => $bulk_insert_array ];

            # add insert data if only true
            if ($overwrite_item) {
                $return['data'] = $bulk_insert_array;
            }
        }

        return $return;
    }

    function get_week_day_list() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_finance_applied_days');
        $this->db->where('archive', 0);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return array_column($result, 'id', 'name');
    }

    function get_purpose_list() {
        $this->db->select(['id', "LOWER(purpose) as name"]);
        $this->db->from('tbl_finance_support_purpose');
        $this->db->where('archive', 0);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return array_column($result, 'id', 'name');
    }

    function get_purpose_mapping_list() {
        $this->db->select(['fsp.id', "LOWER(fsp.purpose) as name", "fspm.support_category_id"]);
        $this->db->from('tbl_finance_support_purpose as fsp');
        $this->db->join('tbl_finance_support_purpose_mapping as fspm', 'fspm.support_purpose_id = fsp.id', 'LEFT');
        $this->db->where('fsp.archive', 0);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return $result;
    }

    function get_support_type_list() {
        $this->db->select(['id', "LOWER(type) as name"]);
        $this->db->from('tbl_finance_support_type');
        $this->db->where('archive', 0);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return array_column($result, 'id', 'name');
    }

    function get_time_of_day_list() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_finance_time_of_the_day');
        $this->db->where('archive', 0);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        return array_column($result, 'id', 'name');
    }

    function get_state_list() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_state');
        $this->db->order_by("name", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_funding_type_list() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_funding_type');
        $this->db->where('archive', 0);
        $this->db->order_by("name", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_support_category_list() {
        $this->db->select(['id', "prefix as name"]);
        $this->db->from('tbl_finance_support_category');
        $this->db->where('archive', 0);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_support_category_list_by_name() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_finance_support_category');
        $this->db->where('archive', 0);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_support_outcome_domain_list() {
        $this->db->select(['id', "prefix as name"]);
        $this->db->from('tbl_finance_support_outcome_domain');
        $this->db->where('archive', 0);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_support_outcome_domain_list_by_name() {
        $this->db->select(['id', "LOWER(name) as name"]);
        $this->db->from('tbl_finance_support_outcome_domain');
        $this->db->where('archive', 0);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_classification_level_list() {
        $where = array('status' => 1, 'archive' => 0);

        $this->db->select(['id', "LOWER(level_name) as name"]);
        $this->db->from('tbl_classification_level');
        $this->db->where($where);
        $this->db->order_by("level_name", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_classification_point_list() {
        $where = array('status' => 1, 'archive' => 0);

        $this->db->select(['id', "LOWER(point_name) as name"]);
        $this->db->from('tbl_classification_point');
        $this->db->where($where);
        $this->db->order_by("point_name", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_support_registration_group_list() {
        $where = array('archive' => 0);

        //$this->db->select(['id', "LOWER(name) as name"]);
        $this->db->select(['id', "name", "prefix"]);
        $this->db->from('tbl_finance_support_registration_group');
        $this->db->where($where);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'prefix');
    }

    function get_support_registration_group_list_by_name() {
        $where = array('archive' => 0);

        $this->db->select(['id', "LOWER(name) as name"]);
        //$this->db->select(['id', "batchId as name"]);
        $this->db->from('tbl_finance_support_registration_group');
        $this->db->where($where);
        $this->db->order_by("order", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return array_column($result, 'id', 'name');
    }

    function get_measured_by_list() {
        $where = array('archive' => 0);

        $this->db->select(['id', "LOWER(name) as name"]);
        // $this->db->select(['id', "Lower(batchId) as name"]);
        $this->db->from('tbl_finance_measure');
        $this->db->where($where);
        $this->db->order_by("id", "asc");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();        
        return array_column($result, 'id', 'name');
    }
    
    /**
     * multiSearch
     * @param {array} $array
     * @param {array} $pairs 
     */
    public static function multiSearch(array $array, array $pairs)
    {
        $found = array();
        foreach ($array as $aKey => $aVal) {
            $coincidences = 0;
            foreach ($pairs as $pKey => $pVal) {
                if (array_key_exists($pKey, $aVal) && $aVal[$pKey] == $pVal) {
                    $coincidences++;
                }
            }
            if ($coincidences > 0) {
                $found[$aKey] = $aVal;
            }
        }

        return $found;
    }

    /**
     * validate vategory ref
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_category_ref_data($arrayTemp, $header, $raw_header) {
        # Validate category required if "is category" value is Y or Yes or True 
        $is_category_low = strtolower(trim($arrayTemp['is category']));
        $is_category = false;
        switch($is_category_low) {
            case 'n':
            case 'no':
            case 'false':
            case '0':
                $is_category = false;
                break;
            case 'y':
            case 'yes':
            case 'true':
            case '1':
                $is_category = true;
                break;
            default:
                $is_category = false;
                break;
        }

        $category_ref = $arrayTemp['category ref'];

        if ($is_category == false && ($category_ref == '' || $category_ref == null)) {
            $header_name = $this->get_raw_header_err_index('category ref', $header, $raw_header);
            $return = [ 'status' => false, 'data' => $header_name, 'is_category' => $is_category ];
        } else {
            $return = [ 'status' => true, 'data' => $arrayTemp['category ref'], 'is_category'=> $is_category ];
        }

        return $return;
    }

    /**
     * validate unit measure ref
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_unit_measure_data($arrayTemp, $header, $raw_header) {
        $measure_list = $this->get_measured_by_list();
        # Validate category required if "is category" value is Y or Yes or True 
        $unit_measure = $arrayTemp['unit of measure'];
        $value = strtolower(trim($unit_measure));
        switch($value) {
            case 'h':
            case 'hour':
                $value = 'hourly';
                break;
            case 'e':
            case 'ea':
                $value = 'each';
            case 'd':
            case 'day':
                $value = 'daily';
            case 'w':
            case 'wk':
            case 'week':
                $value = 'weekly';
                break;
            case 'm':
            case 'mon':
                $value = 'monthly';
            case 'a':
            case 'annual':
                $value = 'annually';
            default:
                break;
        }

        if (isset($measure_list[$value]) && !empty($measure_list[$value])) {
            $return = [ 'status' => true, 'data' => $measure_list[$value] ];
        } else {
            $header_name = $this->get_raw_header_err_index('unit of measure', $header, $raw_header);
            $return = [ 'status' => false, 'data' => $header_name ];

        }

        return $return;
    }

    /**
     * validate unit
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_unit_data($arrayTemp, $header, $raw_header) {
        $measure_list = $this->get_measured_by_list();
        # Validate category required if "is category" value is Y or Yes or True 
        $in_unit = strtolower(trim($this->import_header['units']));
        $unit_measure = $arrayTemp[$in_unit];
        $value = strtolower(trim($unit_measure));
        switch($value) {
            case 'h':
            case 'hour':
            case 'hourly':
                $value = LineItemUnits::hourly;
                break;
            case 'e':
            case 'ea':
            case 'each':
                $value = LineItemUnits::each;
                break;
            case 'd':
            case 'day':
            case 'daily':
                $value = LineItemUnits::daily;
                break;
            case 'w':
            case 'wk':
            case 'week':
            case 'weekly':
                $value = LineItemUnits::weekly;
                break;
            case 'm':
            case 'mon':
            case 'monthly':
                $value = LineItemUnits::monthly;
                break;
            case 'y':
            case 'yr':
            case 'a':
            case 'annual':
            case 'annually':
                $value = LineItemUnits::annually;
                break;
            case 'k':
            case 'km':
                $value = LineItemUnits::km;
                break;
            default:
                $value = '';
                break;
        }

        if ($value != '' && $value != null && $value !='null') {
            $return = [ 'status' => true, 'data' => $value ];
        } else {
            $header_name = $this->get_raw_header_err_index('unit of measure', $header, $raw_header);
            $return = [ 'status' => false, 'data' => $header_name ];

        }

        return $return;
    }

    /**
     * validate quote
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_quote_data($arrayTemp, $header, $raw_header) {
        $in_quote = strtolower(trim($this->import_header['quote_required']));
        $quote = !empty($arrayTemp[$in_quote]) ? $arrayTemp[$in_quote] : '';
        $value = strtolower(trim($quote));
        $return = [ 'data_available' => false ];
        if ($value != '' && $value != null && $value !='null') {
            switch($value) {
                case 'y':
                case 'yes':
                case 'true':
                case '1':
                    $return = [ 'status' => true, 'data' => 1, 'data_available' => true ];
                    break;
                case 'n':
                case 'no':
                case 'false':
                case '0':
                    $return = [ 'status' => true, 'data' => 0, 'data_available' => true ];
                    break;
                default:
                    $header_name = $this->get_raw_header_err_index('quote', $header, $raw_header);
                    $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
                    break;
            }
        }

        return $return;
    }

    /**
     * validate true or false data
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_true_or_false_data( $mystring, $arrayTemp, $header, $raw_header) {

        $return = [ 'data_available' => false ];
        $data = isset($arrayTemp[$mystring]) ? $arrayTemp[$mystring] : '';
        if ($data === true) {
            $data = 'true';
        }
        if ($data === false) {
            $data = 'false';
        }
        $value = strtolower(trim($data));
        if ($value != '' && $value != null && $value !='null') {
            switch($value) {
                case 'y':
                case 'yes':
                case 'true':
                case '1':
                    $return = [ 'status' => true, 'data' => 1, 'data_available' => true ];
                    break;
                case 'n':
                case 'no':
                case 'false':
                case 'benchmark':
                case '0':
                    $return = [ 'status' => true, 'data' => 0, 'data_available' => true ];
                    break;
                default:
                    $header_name = $this->get_raw_header_err_index($mystring, $header, $raw_header);
                    $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
                    break;
            }
        }

        return $return;
    }

    /**
     * validate week of day
     * @param {str} $search_str
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_week_of_day($mystring, $arrayTemp, $header, $raw_header) {
        $weekOfdays = $this->get_week_day_list();
        $return = [ 'data_available' => false ];
        $day_od_week = $arrayTemp[$mystring] ?? '';
        $value = strtolower(trim($day_od_week));
        if ($value != '' && $value != null && $value !='null') {
            $week_ex = explode(',',$value);
            $arrayWeek = [];
            foreach($week_ex as $week) {
                $week = strtolower(trim($week));
                switch($week) {
                    case 'week':
                    case 'weekday':
                        $week = 'weekday';
                        break;
                    case 'pub hol':
                    case 'pub':
                    case 'public holiday':
                        $week = 'public_holiday';
                        break;
                    case 'sat':
                    case 'saturday':
                        $week = 'saturday';
                        break;
                    case 'sun':
                    case 'sunday':
                        $week = 'sunday';
                        break;
                    default:
                        break;
                }

                if ($week != '') {
                    $arrayWeek[] = $week;
                }
            }

            if (isset($arrayWeek) && !empty($arrayWeek)) {
                $return = [ 'status' => true, 'data' => $arrayWeek, 'data_available' => true ];
            } else {
                $header_name = $this->get_raw_header_err_index($mystring, $header, $raw_header);
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
            }
        }
        return $return;
    }

    /**
     * validate time of day
     * @param {str} $search_str
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_time_of_day($mystring, $arrayTemp, $header, $raw_header) {
        $timeOfdays = $this->get_time_of_day_list();
        $return = [ 'data_available' => false ];
        $time_of_week = $arrayTemp[$mystring] ?? '';
        $value = strtolower(trim($time_of_week));
        if ($value != '' && $value != null && $value !='null') {
            $time_ex = explode(',',$value);
            $arrayTime = [];
            foreach($time_ex as $time) {
                $time = strtolower(trim($time));
                switch(strtolower($time)) {
                    case 'daytime':
                    case 'day':
                        $time = 'daytime';
                        break;
                    case 'eve':
                    case 'even':
                    case 'evening':
                        $time = 'evening';
                        break;
                    case 's/o':
                    case 'sleep over':
                    case 'overnight (sleep over)':
                    case 'overnight (sleepover)':
                        $time = 'sleepover';
                        break;
                    case 'a/o':
                    case 'active':
                    case 'overnight':
                    case 'active overnight':
                        $time = 'overnight';
                        break;
                    default:
                        $time = '';
                        break;
                }
                
                if ($time != '') {
                    $arrayTime[] = $time;
                }                
            }

            if (isset($arrayTime) && !empty($arrayTime)) {
                $return = [ 'status' => true, 'data' => $arrayTime, 'data_available' => true ];
            } else {
                $header_name = $this->get_raw_header_err_index($mystring, $header, $raw_header);
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
            }
        }
        return $return;
    }

    /**
     * validate the price
     * @param {str} $search_str
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function validate_price_data($my_string, $arrayTemp, $header, $raw_header)
    {
        $dollar = '$';
        $return = [ 'data_available' => false, 'status' => false ];
        $myDateString = !empty($arrayTemp[$my_string]) ? $arrayTemp[$my_string] : '';
        $value = trim($myDateString);

        $header_name = $this->get_raw_header_err_index($my_string, $header, $raw_header);
        $valid = true;
        if ($value != '' && $value != null && $value !='null') {
            $value = str_replace(',', '', $value);
            $dollar_count = substr_count($value, $dollar);
            if ($dollar_count > 1) {
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
                $valid = false;
            }
            $dollar_position = strpos($value, $dollar);
            if ($dollar_position > 0) {
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
                $valid = false;
            } else if (strlen($dollar_position) == 1) {
                $value = substr($value, 1);
                $return = [ 'status' => true, 'data' => $value, 'data_available' => true ];
            }
            if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value) && $valid == true) {
                $value = (float) $value;
                $check_positive = (float) number_format((float) $value, 2, '.', '');
                $return = [ 'status' => true, 'data' => $check_positive, 'data_available' => true ];
            } else {
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
            }

        } else {
            $value = '0.00';
            $return = [ 'status' => true, 'data' => $header_name, 'data_available' => true ];
        }
        return $return;
    }
    /**
     * Get the array index for error validation
     * @param {str} $search_str
     * @param {array} $header
     * @param {array} $raw_header
     */
    function get_raw_header_err_index($search_str, $header, $raw_header) {
        $raw_header_in = array_search($search_str, $header);
        $header_name = $raw_header[$raw_header_in] ?? 'not_exist';
        return $header_name;
    }

    /**
     * validate file column header with our required header column
     * @param {array} $rows
     */
    function validate_column($rows) {

        # Get header columns from sheet
        $header = array_shift($rows);
        $raw_header = $header;
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        sort($col_header);

        # import columns
        $import_header = $this->import_header;
        $import_header = array_map('trim', $import_header);
        $import_header = array_map('strtolower', $import_header);
        sort($import_header);

        $arColMatch = array_diff($import_header, $col_header);

        # required fields validation $this->header_required_filed
        $req_fields = $this->header_required_fields;
        $req_fields = array_map('trim', $req_fields);
        $req_fields = array_map('strtolower', $req_fields);
        sort($req_fields);
        $containsSearch = count(array_intersect($req_fields, $col_header)) === count($req_fields);

        $req_join = implode(', ', $this->header_required_fields);
        if ($containsSearch === false)  {
            return ['status' => false, 'error' => 'Please uploda the file with required columns - '. $req_join];
        }

        # check if any duplication 
        $col_header = array_values (array_unique($header));

        # check invalid columns are placed
        $inValidCol = [];
        foreach($col_header as $col) {
            if (!in_array($col, $import_header)) {
                $raw_header_in = array_search($col, $header);
                $inValidCol[] = $raw_header[$raw_header_in];
            }
        }

        if (!empty($inValidCol))  {
            $im_invalid_col = implode(',',$inValidCol);
            // return ['status' => false, 'error' => 'Invalid column names in uploaded file - '. $im_invalid_col];
        }

        return [ 'status' => true ];
    }

    /**
     * Check the line item price existing
     */
    function check_li_price_exist_validation($reqData) {
        $this->db->select(["fli.id as line_item_id", "funding_type", "support_registration_group", "support_category", "support_purpose", "support_type", "support_outcome_domain", "line_item_number", "line_item_name", "category_ref", "description", "quote_required", "price_control", "travel_required", "cancellation_fees", "ndis_reporting", "non_f2f", "levelId", "pay_pointId", "units", "schedule_constraint", "member_ratio", "participant_ratio", "measure_by", "oncall_provided", "weekday", "saturday", "sunday", "daytime", "evening", "overnight", "sleepover", "public_holiday", "needs"]);
        $this->db->from('tbl_finance_line_item as fli');
        $this->db->where('fli.line_item_number', $reqData->line_item_number);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        return $query->row_array();
    }

    /**
     * check the date format
     * @param {str} $search_str
     * @param {array} $arrayTemp
     * @param {array} $header
     * @param {array} $raw_header
     */
    function checkIsAValidDate($my_string, $arrayTemp, $header, $raw_header)
    {
        $this->load->model([ 'Finance_import_export_line_item']);
        $type = 'd/m/Y';
        $status = false;
        $validdate = '';
        $return = [ 'data_available' => false, 'status' => false ];
        $myDateString = !empty($arrayTemp[$my_string]) ? $arrayTemp[$my_string] : '';
        $value = trim($myDateString);

        if ($myDateString) {
            $format = $this->Finance_import_export_line_item->date_extract_format($value);
            if ($format) {
                $type = $format;
            }
        }

        switch ($type) {
            case 'd-m-Y':
                $valid = validateDateWithFormat($myDateString, 'd-m-Y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d-m-Y', $myDateString);
                    $return = [ 'status' => true, 'data' => $dateTime, 'data_available' => true ];
                    break;
                }
            case 'd-m-y':
                $valid = validateDateWithFormat($myDateString, 'd-m-y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d-m-y', $myDateString);
                    $return = [ 'status' => true, 'data' => $dateTime, 'data_available' => true ];
                    break;
                }

            case 'd/m/y':
                $valid = validateDateWithFormat($myDateString, 'd/m/y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d/m/y', $myDateString);
                    $return = [ 'status' => true, 'data' => $dateTime, 'data_available' => true ];
                    break;
                }
            case 'd/m/Y':
                $valid = validateDateWithFormat($myDateString, 'd/m/Y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d/m/Y', $myDateString);
                    $return = [ 'status' => true, 'data' => $dateTime, 'data_available' => true ];
                    break;
                }
            default:
                $header_name = $this->get_raw_header_err_index($my_string, $header, $raw_header);
                $return = [ 'status' => false, 'data' => $header_name, 'data_available' => true ];
                break;
        }
        if ($status) {
            $validdate = $dateTime->format('Y-m-d');
            $return = [ 'status' => true, 'data' => $validdate, 'data_available' => true ];
        }

        return $return;
    }

}