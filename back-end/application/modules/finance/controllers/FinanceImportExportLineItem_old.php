<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FinanceImportExportLineItem extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->model('Finance_import_export_line_item');
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
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

    // import CSV code start       
    public function check_line_item($item_name)
    {
        $this->db->select(array("id"));
        $this->db->from("tbl_finance_support_registration_group");
        $this->db->where("line_item_name='" . $item_name . "'");
        $res = $this->db->get();
        $res_array = $res->row_array();
        if (!empty($res_array)) {
            return ["status" => true, "data" => $res_array['id']];
        }
        return ["status" => false];
    }

    public function getAllSupportBatchId()
    {
        $getData = $this->basic_model->get_record_where('finance_support_registration_group', ['id', 'batchId'], ['archive' => 0]);
        $arr_support_batch = array();
        foreach ($getData as $data) {
            $arr_support_batch[$data->batchId] = $data->id;
        }
        return $arr_support_batch;
    }

    public function updateSupportNumber()
    {
        $getData = $this->basic_model->get_record_where('finance_support_registration_group', ['id'], ['archive' => 0]);
        foreach ($getData as $data) {
            // echo $data->id;
            $where_columns = ["id" => $data->id];
            $data = ["batchId" => $data->id + 100];
            $this->db->update('tbl_finance_support_registration_group', $data, $where_columns);
        }
    }


    /** Start code 14 Feb 20 */

    // Export csv data according to provided by HCM team
    public function get_csv_line_item_old()
    {
        $requestData = request_handler('access_finance_line_item');

        $data = $requestData->data;
        if (isset($data->exportType) && !empty($data->exportType)) {

            $record = $this->Finance_import_export_line_item->get_line_item_csv_report($data->exportType);

            //print_r($record);


            $weekOfdays_list = $this->Finance_import_export_line_item->get_week_day_list();
            $timeOfdays_list = $this->Finance_import_export_line_item->get_time_of_day_list();
            $states_list = $this->Finance_import_export_line_item->get_state_list();

            $date = date('Y-m-d');

            $this->load->library("Excel");
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);

            $colName = 0;
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Registration Group Number');
            // $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Funding Type');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Registration Group Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Registration Group Name');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Category Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Category Name');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Outcome Domain');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Outcome Domain');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Item Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Item Name');

            // $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Start Date (DD-MM-YYYY)');
            // $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'End Date (DD-MM-YYYY)');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Item Description');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Unit');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Price Controlled');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Quote Required');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'State');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'National Non-Remote (MM 1-5)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'National Remote (MM 6)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'National Very Remote (MM 7)');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Travel');

            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Cancellations');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'NDIA Reporting');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Non-F2F');

            /*$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Member to Participant Ratio');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Does this item have a schedule constraint? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Type of Day');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Time of Day');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'State');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Public Holiday (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Minimum Level Required (Level 1, Level 2, upto Level 8)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Minimum Pay Point Required (Pay Point 1, Pay Point 2, Pay Point 3, Pay Point 4)');
            */

            if (!empty($record)) {
                $var_row = 2;

                foreach ($record as $data) {
                    $col = 0;

                    $states = explode('@#_BREAKER_#@', $data->states);
                    $timeofday = explode('@#_BREAKER_#@', $data->timeofday);
                    $weekofday = explode('@#_BREAKER_#@', $data->weekofday);

                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_registration_group_number);
                    // $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_funding_type);
                    // $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, $data->support_registration_group);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_registration_group_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_category_number);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_category_name);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_outcome_domain_name);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, $data->support_outcome_domain);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->line_item_number);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->line_item_name);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, DateFormate($data->start_date, "d-m-Y"));
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, DateFormate($data->end_date, "d-m-Y"));
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->description);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->unit);

                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->price_control);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->quote_required_type);

                    $strState = '';
                    $cnt = 0;
                    foreach ($states_list as $key => $value) {
                        if (in_array($value, $states)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strState = ucfirst($key);
                            else
                                $strState .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strState);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->upper_price_limit);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->national_price_limit);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->national_very_price_limit);


                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->travel_required);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->cancellation_fees);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->ndis_reporting);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->non_f2f);

                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->member_participant_ratio);

                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->schedule_constraint);
                    /*
                    $strWeek = '';
                    $cnt = 0;
                    foreach ($weekOfdays_list as $key => $value) {
                        if (in_array($value, $weekofday)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strWeek = ucfirst($key);
                            else
                                $strWeek .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strWeek);

                    $strTime = '';
                    $cnt = 0;
                    foreach ($timeOfdays_list as $key => $value) {
                        if (in_array($value, $timeofday)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strTime = ucfirst($key);
                            else
                                $strTime .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strTime);

                    $strState = '';
                    $cnt = 0;
                    foreach ($states_list as $key => $value) {
                        if (in_array($value, $states)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strState = ucfirst($key);
                            else
                                $strState .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strState);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->public_holiday);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->level_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->point_name);
                    */

                    $var_row++;
                }

                $object->setActiveSheetIndex()
                    ->getStyle('A1:D1')
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'C0C0C0:')
                            )
                        )
                    );
            } else {
                $object->setActiveSheetIndex()
                    ->mergeCells('A2:I2');
                $object->getActiveSheet()
                    ->getCell('A2')
                    ->setValue('No detail found for selected filters.');
            }
            $filename = time() . '_line__item_report' . '.csv';
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
            $filePath = FCPATH;
            $filePath .= ARCHIEVE_DIR . '/';
            $response = $object_writer->save($filePath . $filename);
            $csv_fileFCpath = $filePath . $filename;
            if (file_exists($csv_fileFCpath)) {
                echo json_encode(['status' => true, 'filename' => $filename]);
                exit();
            }
            echo json_encode(['status' => false, 'error' => 'line item csv file not exist']);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Please select line item export type first']);
            exit();
        }
    }


    function read_csv_line_items_url_import()
    {
        //$data = request_handlerFile('access_finance_line_item');
        $currunt_date = date("d-m-Y", strtotime(DATE_TIME));
        $str_currunt_date = strtotime(DATE_TIME);

        $csv_column_arr = array(
            "Registration Group Number", "Registration Group Name", "Support Category Number", "Support Category Name", "Support Item Number", "Support Item Name",
            "Support Item Description", "Unit", "Price Controlled", "Quote Required", "State", "National Non-Remote (MM 1-5)", "National Remote (MM 6)", "National Very Remote (MM 7)", "Travel", "Cancellations", "NDIA Reporting", "Non-F2F"
        );
        $csv_column_arr = array_map('strtolower', $csv_column_arr);
        sort($csv_column_arr);

        /*  if (empty($_FILES['docsFile']['name'])) {
            echo json_encode(['status' => false, 'error' => 'Please select a csv file to upload.']);
            exit();
        } */

        //if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {
        $this->load->library('csv_reader');

        $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        //if (in_array($_FILES['docsFile']['type'], $mimes)) {
        $tmpName =  FCPATH . 'uploads/finance/PB_Support_Catalogue_2019_20_Line items.csv'; //$_FILES['docsFile']['tmp_name'];

        $file = $this->csv_reader->read_csv_data($tmpName);

        $header = array_shift($file);
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        $line_item_data = [];
        if (!empty($file)) {
            sort($col_header);
            $arColMatch = array_diff($csv_column_arr, $col_header);
            if (empty($arColMatch)) {
                foreach ($file as $row) {

                    if (count($row) == count($header)) {
                        $row = array_map("utf8_encode", $row);
                        $line_item_data[] = array_combine($header, $row);
                    } else {

                        echo json_encode(["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."]);
                        exit();
                    }
                }
                if (!empty($line_item_data)) {

                    $response = $this->check_insert_update_line_item_data_url_import($line_item_data, $csv_column_arr);
                    echo json_encode($response);
                    exit();
                } else {
                    echo json_encode(['status' => false, 'error' => 'Line Item invalid data']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Invalid column names in uploaded csv file']);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Invalid data in uploaded csv file']);
            exit();
        }
        /*} else {
                echo json_encode(['status' => false, 'error' => 'Invalid file extension, Please upload csv file only']);
                exit();
            }*/
        /* } else {
            echo json_encode(['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.']);
            exit();
        }*/
    }


    function check_insert_update_line_item_data_url_import($line_item_data, $csv_column_arr)
    {

        $weekOfdays = $this->Finance_import_export_line_item->get_week_day_list();
        $timeOfdays = $this->Finance_import_export_line_item->get_time_of_day_list();
        $states = $this->Finance_import_export_line_item->get_state_list();
        $fundingType = $this->Finance_import_export_line_item->get_funding_type_list();
        $supportCategory = $this->Finance_import_export_line_item->get_support_category_list();
        // $supportOutcomeDomain = $this->Finance_import_export_line_item->get_support_outcome_domain_list();
        $classificationLevel = $this->Finance_import_export_line_item->get_classification_level_list();
        $classificationPoint = $this->Finance_import_export_line_item->get_classification_point_list();

        $support_registration_group = $this->Finance_import_export_line_item->get_support_registration_group_list();
        $measure_list = $this->Finance_import_export_line_item->get_measured_by_list();


        $cnt = 1;
        $insert_array = array();
        $bulk_insert_array = array();
        foreach ($line_item_data as $row_value) {
            $cnt++;
            // Start Validation 
            if (isset($row_value['registration group number']) && !empty($row_value['registration group number'])) {
                $value = strtolower(trim($row_value['registration group number']));
                if (isset($support_registration_group[$value]) && !empty($support_registration_group[$value])) {
                    $insert_array['support_registration_group'] = $support_registration_group[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Registration group number" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Registration group number" not exist in row ' . $cnt]);
                exit();
            }

            if (isset($row_value['registration group name']) && !empty($row_value['registration group name'])) {
                $value = strtolower(trim($row_value['registration group name']));
                if (!empty($value)) {
                    //$insert_array['support_registration_group'] = $support_registration_group[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Registration group name" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Registration group name" not exist in row ' . $cnt]);
                exit();
            }

            if (isset($row_value['support category name']) && !empty($row_value['support category name'])) {
            } else {

                echo json_encode(['status' => false, 'error' => 'support category name not exist in row ' . $cnt]);
                exit();
            }



            if (isset($row_value['support category number']) && !empty($row_value['support category number'])) {
                $value = strtolower(trim($row_value['support category number']));
                if (isset($supportCategory[$value]) && !empty($supportCategory[$value])) {
                    $insert_array['support_category'] = $supportCategory[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support category number" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                $insert_array['support_category'] = 0;
                echo json_encode(['status' => false, 'error' => 'support category number not exist in row ' . $cnt]);
                exit();
            }

            if (isset($row_value['support item number']) && !empty($row_value['support item number'])) {
                $value = strtolower(trim($row_value['support item number']));
                $insert_array['line_item_number'] = $row_value['support item number'];
                /*if (isset($supportCategory[$value]) && !empty($supportCategory[$value])) {
                    $insert_array['support_category'] = $supportCategory[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support item number" (' . $value . ') in row ' . $cnt]);
                    exit();
                }*/
            } else {
                echo json_encode(['status' => false, 'error' => 'support item number not exist in row ' . $cnt]);
                exit();
            }


            if (isset($row_value['support item name']) && !empty($row_value['support item name'])) {
                $value = strtolower(trim($row_value['support item name']));
                $insert_array['line_item_name'] = $row_value['support item name'];
                /*if (isset($supportCategory[$value]) && !empty($supportCategory[$value])) {
                    $insert_array['support_category'] = $supportCategory[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support item number" (' . $value . ') in row ' . $cnt]);
                    exit();
                }*/
            } else {

                echo json_encode(['status' => false, 'error' => 'support item name not exist in row ' . $cnt]);
                exit();
            }

            // -------------------- support item description ----------------------------------------
            if (isset($row_value['support item description']) && !empty($row_value['support item description'])) {
                if (strlen($row_value['support item description']) > 1000) {
                    echo json_encode(['status' => false, 'error' => '"Support item description" can not be more than 1000 characters, in row ' . $cnt]);
                    exit();
                }
                $insert_array['description'] = $row_value['support item description'];
            } else {
                $insert_array['description'] = '';
            }

            // -------------------- Unit ----------------------------------------

            if (isset($row_value['unit']) && !empty($row_value['unit'])) {
                $value = strtolower(trim($row_value['unit']));
                if (isset($measure_list[$value]) && !empty($measure_list[$value])) {
                    $insert_array['measure_by'] = $measure_list[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "unit" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                $insert_array['support_category'] = 0;
                echo json_encode(['status' => false, 'error' => 'Unit not exist in row ' . $cnt]);
                exit();
            }

            // -------------------- Price Controlled ----------------------------------------
            if (isset($row_value['price controlled']) && !empty($row_value['price controlled'])) {
                $value = strtolower(trim($row_value['price controlled']));
                $insert_array['price_control'] = 0;
                if ($value == 'y') {
                    $insert_array['price_control'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Price controlled" required in row ' . $cnt]);
                exit();
            }

            // -------------------- Quote Required ----------------------------------------
            if (isset($row_value['quote required']) && !empty($row_value['quote required'])) {
                $value = strtolower(trim($row_value['quote required']));
                $insert_array['quote_required'] = 0;
                if ($value == 'y') {
                    $insert_array['quote_required'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Quote Required" required in row ' . $cnt]);
                exit();
            }

            // -------------------- states ----------------------------------------               

            if (isset($row_value['state']) && !empty($row_value['state'])) {
                $value = strtolower(trim($row_value['state']));
                $arr_state = [];
                $arr_states = explode(",", $value);
                foreach ($arr_states as $stateName) {
                    if (isset($states[$stateName])) {
                        $arr_state[] = $states[$stateName];
                    } else {
                        echo json_encode(['status' => false, 'error' => 'invalid "state" (' . $stateName . ') in row ' . $cnt]);
                        exit();
                    }
                }
                $insert_array['states'] = $arr_state;
            } else {
                $arr_state = array_values($states);
                $insert_array['states'] = $arr_state;
            }


            // -------------------- travel required (yes/no) ----------------------------------------
            if (isset($row_value['travel']) && !empty($row_value['travel'])) {
                $value = strtolower(trim($row_value['travel']));
                $insert_array['travel_required'] = 0;
                if ($value == 'y') {
                    $insert_array['travel_required'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Travel" required in row ' . $cnt]);
                exit();
            }

            // -------------------- cancellation fees (yes/no) ----------------------------------------
            if (isset($row_value['cancellations']) && !empty($row_value['cancellations'])) {
                $value = strtolower(trim($row_value['cancellations']));
                $insert_array['cancellation_fees'] = 0;
                if ($value == 'y') {
                    $insert_array['cancellation_fees'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Cancellation" required in row ' . $cnt]);
                exit();
            }

            // -------------------- ndia reporting ----------------------------------------
            if (isset($row_value['ndia reporting']) && !empty($row_value['ndia reporting'])) {
                $value = strtolower(trim($row_value['ndia reporting']));
                $insert_array['ndis_reporting'] = 0;
                if ($value == 'y') {
                    $insert_array['ndis_reporting'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"NDIA reporting" required in row ' . $cnt]);
                exit();
            }

            // -------------------- Non-F2F ----------------------------------------
            if (isset($row_value['non-f2f']) && !empty($row_value['non-f2f'])) {
                $value = strtolower(trim($row_value['non-f2f']));
                $insert_array['non_f2f'] = 0;
                if ($value == 'y') {
                    $insert_array['non_f2f'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Non-F2F" required in row ' . $cnt]);
                exit();
            }

            // -------------------- National Non-Remote (MM 1-5) ----------------------------------------
            $max_value = 99999.99;
            $find_dollar = '$';
            if (isset($row_value['national non-remote (mm 1-5)']) && !empty($row_value['national non-remote (mm 1-5)'])) {
                $value = strtolower(trim($row_value['national non-remote (mm 1-5)']));
                $value = trim($value);
                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Non-Remote (MM 1-5)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Non-Remote (MM 1-5)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "National Non-Remote (MM 1-5)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }

                $value = (float) $value; //, 2, '.', '');

                if (is_numeric($value) || is_float($value)) {
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "National Non-Remote (MM 1-5)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"National Non-Remote (MM 1-5)" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } elseif ($check_positive > 0) {
                        $insert_array['upper_price_limit'] = $check_positive;
                    } else {
                        echo json_encode(['status' => false, 'error' => '"National Non-Remote (MM 1-5)" value should be greater than 0 in, row ' . $cnt]);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Non-Remote (MM 1-5)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                //echo json_encode(['status' => false, 'error' => '"National Non-Remote (MM 1-5)" required in row ' . $cnt]);
                //exit();
                $insert_array['upper_price_limit'] = 0.00;
            }

            // -------------------- national remote (mm 6) ----------------------------------------
            if (isset($row_value['national remote (mm 6)']) && !empty($row_value['national remote (mm 6)'])) {
                $value = strtolower(trim($row_value['national remote (mm 6)']));

                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "national remote (mm 6)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "national remote (mm 6)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "national remote (mm 6)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }

                $value = (float) $value;
                if (is_numeric($value) || is_float($value)) {
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "national remote (mm 6)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"national remote (mm 6)" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } else {
                        $insert_array['national_price_limit'] = $check_positive;
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "national remote (mm 6)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                $insert_array['national_price_limit'] = 0.00;
            }


            // -------------------- National Very Remote (MM 7) ----------------------------------------
            if (isset($row_value['national very remote (mm 7)']) && !empty($row_value['national very remote (mm 7)'])) {
                $value = strtolower(trim($row_value['national very remote (mm 7)']));
                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National very remote (mm 7)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                // echo $dollar_position."dollar";
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National very remote (mm 7)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "National very remote (mm 7)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }

                $value = (float) $value;
                if (is_numeric($value) || is_float($value)) {
                    // $value = preg_replace("/[^0-9.]/", "", $value);
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "National very remote (mm 7)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"National very remote (mm 7)" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } else {
                        $insert_array['national_very_price_limit'] = $check_positive;
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National very remote (mm 7)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                $insert_array['national_very_price_limit'] = 0.00;
            }


            $strStartDate = date('Y-m-d', strtotime('first day of january this year')); // $row_value['start date (dd-mm-yyyy)'];
            $strEndDate =  date('Y-m-d', strtotime('last day of December this year')); // $row_value['end date (dd-mm-yyyy)'];

            $insert_array['start_date'] = $strStartDate;
            $insert_array['end_date'] = $strEndDate;



            // -------------------- type of day ----------------------------------------

            $arr_week = array_values($weekOfdays);
            $insert_array['weekofday'] = $arr_week;

            $arr_daytime = array_values($timeOfdays);
            $insert_array['timeofday'] = $arr_daytime;













            // Default funding type set NDSI=1
            $insert_array['funding_type'] = $fundingType['ndis'];
            $insert_array['public_holiday'] = 0;
            $insert_array['schedule_constraint'] = 0;
            $insert_array['pay_pointId'] = $classificationPoint['pay point 1'];
            $insert_array['levelId'] = $classificationLevel['level 1'];
            $insert_array['member_ratio'] = 1;
            $insert_array['participant_ratio'] = 2;


            // $insert_array['support_outcome_domain'] = 0;
            //  $insert_array['weekofday'] = $arr_week;
            // $insert_array['timeofday'] = $arr_daytime;
            // $insert_array['line_item_number'] = $row_value['reference number'];
            // $insert_array['line_item_name'] = $row_value['line item name'];
            // End Validation

            $valid = $this->validate_csv_duplicate_records($bulk_insert_array, $insert_array, $cnt);

            if ($valid) {
                $valid_table_response = $this->Finance_import_export_line_item->validate_csv_import_data_between_date($insert_array);
                if (!empty($valid_table_response)) {
                    //echo json_encode(['status' => false, 'error' => 'Same line item allready exist in table between dates row '.$cnt]);
                    echo json_encode(['status' => false, 'error' => 'Line item "' . $insert_array['line_item_number'] . '" already exists in the system for the specified dates. Please try importing the file again, row ' . $cnt]);
                    exit();
                } else {
                    $bulk_insert_array[] = $insert_array;
                }
            }
        }
        if (!empty($bulk_insert_array)) {
            $res_insert = $this->Finance_import_export_line_item->insert_bulk_import_line_item($bulk_insert_array);
            if ($res_insert) {
                return ["status" => true, "message" => "Successful file import"];
            } else {
                return ["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."];
            }
        } else {
            return ["status" => false, "error" => "Data not exist in file."];
        }
    }


    /** End Code 14 Feb 20 */

    /* Import and export csv data according to line item form field */
    // Export csv data according to line item form fields
    public function get_csv_line_item()
    {
        $requestData = request_handler('access_finance_line_item');

        $data = $requestData->data;
        if (isset($data->exportType) && !empty($data->exportType)) {

            $record = $this->Finance_import_export_line_item->get_line_item_csv_report($data->exportType);



            $weekOfdays_list = $this->Finance_import_export_line_item->get_week_day_list();
            $timeOfdays_list = $this->Finance_import_export_line_item->get_time_of_day_list();
            $states_list = $this->Finance_import_export_line_item->get_state_list();

            $date = date('Y-m-d');

            $this->load->library("Excel");
            $object = new PHPExcel();
            $object->setActiveSheetIndex(0);

            $colName = 0;
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Funding Type');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Registration Group Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support registration group');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Category Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Category');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Outcome Domain');
            //$object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Support Outcome Domain');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Reference Number');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Line Item Name');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Is Category (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Category Ref');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Start Date (DD-MM-YYYY)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'End Date (DD-MM-YYYY)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Line Item Description');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Quote Required? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Does this Item have a Price Control? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Travel Required (Yes/No)');
            // $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Unit');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Cancellation Fees (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'NDIA Reporting? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Non-F2F? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Member to Participant Ratio');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Upper Price Limit (National Non Remote)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'National Remote Price');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'National Very Remote Price');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Does this item have a schedule constraint? (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Type of Day');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Time of Day');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'State');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Public Holiday (Yes/No)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Minimum Level Required (Level 1, Level 2, upto Level 8)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Minimum Pay Point Required (Pay Point 1, Pay Point 2, Pay Point 3, Pay Point 4)');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'Units');
            $object->getActiveSheet()->setCellValueByColumnAndRow($colName++, 1, 'ONCALL provided (Yes/No)');


            if (!empty($record)) {
                $var_row = 2;

                foreach ($record as $data) {
                    $col = 0;

                    $states = explode('@#_BREAKER_#@', $data->states);
                    $timeofday = explode('@#_BREAKER_#@', $data->timeofday);
                    $weekofday = explode('@#_BREAKER_#@', $data->weekofday);

                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_funding_type);
                    // $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, $data->support_registration_group);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_registration_group_name);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, $data->support_category);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_category_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->support_outcome_domain_name);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, $data->support_outcome_domain);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->line_item_number);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->line_item_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, empty($data->category_ref) ? 'Yes' : 'No');
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->category_ref);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, DateFormate($data->start_date, "d-m-Y"));
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, DateFormate($data->end_date, "d-m-Y"));
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->description);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->quote_required_type);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->price_control);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->travel_required);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->cancellation_fees);
                    //$object->getActiveSheet()->SetCellValue(getNameFromNumber($col++).$var_row, 'Unit');
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->ndis_reporting);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->non_f2f);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->member_participant_ratio);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->upper_price_limit);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->national_price_limit);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, '$' . $data->national_very_price_limit);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->schedule_constraint);

                    $strWeek = '';
                    $cnt = 0;
                    foreach ($weekOfdays_list as $key => $value) {
                        if (in_array($value, $weekofday)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strWeek = ucfirst($key);
                            else
                                $strWeek .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strWeek);

                    $strTime = '';
                    $cnt = 0;
                    foreach ($timeOfdays_list as $key => $value) {
                        if (in_array($value, $timeofday)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strTime = ucfirst($key);
                            else
                                $strTime .= ',' . ucfirst($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strTime);

                    $strState = '';
                    $cnt = 0;
                    foreach ($states_list as $key => $value) {
                        if (in_array($value, $states)) {
                            $cnt++;
                            if ($cnt == 1)
                                $strState = strtoupper($key);
                            else
                                $strState .= ',' . strtoupper($key);
                        }
                    }
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $strState);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->public_holiday);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->level_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->point_name);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->units);
                    $object->getActiveSheet()->SetCellValue(getNameFromNumber($col++) . $var_row, $data->oncall_provided);


                    $var_row++;
                }

                $object->setActiveSheetIndex()
                    ->getStyle('A1:D1')
                    ->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'C0C0C0:')
                            )
                        )
                    );
            } else {
                $object->setActiveSheetIndex()
                    ->mergeCells('A2:I2');
                $object->getActiveSheet()
                    ->getCell('A2')
                    ->setValue('No detail found for selected filters.');
            }
            $filename = time() . '_line__item_report' . '.csv';
            $object_writer = PHPExcel_IOFactory::createWriter($object, 'CSV');
            $filePath = FCPATH;
            $filePath .= ARCHIEVE_DIR . '/';
            $response = $object_writer->save($filePath . $filename);
            $csv_fileFCpath = $filePath . $filename;
            if (file_exists($csv_fileFCpath)) {
                echo json_encode(['status' => true, 'filename' => $filename]);
                exit();
            }
            echo json_encode(['status' => false, 'error' => 'line item csv file not exist']);
            exit();
        } else {
            echo json_encode(['status' => false, 'error' => 'Please select line item export type first']);
            exit();
        }
    }
    function read_csv_line_items()
    {
        $data = request_handlerFile('access_finance_line_item');

        $csv_column_arr = array(
            "support funding type", "support registration group", "support category", "support outcome domain", "reference number", "line item name", "start date (dd-mm-yyyy)",
            "end date (dd-mm-yyyy)", "line item description", "quote required? (yes/no)", "does this item have a price control? (yes/no)", "travel required (yes/no)", "cancellation fees (yes/no)",
            "ndia reporting? (yes/no)", "non-f2f? (yes/no)", "member to participant ratio", "upper price limit (national non remote)", "national remote price", "national very remote price",
            "does this item have a schedule constraint? (yes/no)", "type of day", "time of day", "state", "public holiday (yes/no)", "minimum level required (level 1, level 2, upto level 8)",
            "minimum pay point required (pay point 1, pay point 2, pay point 3, pay point 4)", "oncall provided (yes/no)"
        );
        sort($csv_column_arr);

        if (empty($_FILES['docsFile']['name'])) {
            echo json_encode(['status' => false, 'error' => 'Please select a csv file to upload.']);
            exit();
        }

        if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {
            $this->load->library('csv_reader');
            //$mimes = array('application/vnd.ms-excel', 'text/plain', 'text/csv', 'text/tsv');
            $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
            if (in_array($_FILES['docsFile']['type'], $mimes)) {
                $tmpName = $_FILES['docsFile']['tmp_name'];

                //$file = array_map('str_getcsv', file($tmpName));

                //$tmpName = $_FILES['docsFile']['tmp_name'];                              
                $file = $this->csv_reader->read_csv_data($tmpName);

                $header = array_shift($file);
                $header = array_map('trim', $header);
                $header = array_map('strtolower', $header);
                $col_header = $header;
                $line_item_data = [];
                if (!empty($file)) {
                    sort($col_header);
                    $arColMatch = array_diff($csv_column_arr, $col_header);
                    #pr([$csv_column_arr, $col_header]);
                    if (empty($arColMatch)) {
                        foreach ($file as $row) {
                            if (count($row) == count($header)) {
                                $row = array_map("utf8_encode", $row);
                                $line_item_data[] = array_combine($header, $row);
                            } else {
                                echo json_encode(["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."]);
                                exit();
                            }
                        }
                        if (!empty($line_item_data)) {

                            $adminName = $this->username->getName('admin', $data->adminId);
                            $this->loges->setTitle("Import line item csv : " . $adminName);
                            $this->loges->setCreatedBy($data->adminId);
                            $this->loges->setUserId($data->adminId);
                            $this->loges->setModule('finance_line_item');
                            $this->loges->setDescription("Import csv line item file " . $_FILES['docsFile']['name']);
                            $response = $this->check_insert_update_line_item_data($line_item_data, $csv_column_arr);
                            echo json_encode($response);
                            exit();
                        } else {
                            echo json_encode(['status' => false, 'error' => 'Line Item invalid data']);
                            exit();
                        }
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid column names in uploaded csv file']);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid data in uploaded csv file']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Invalid file extension, Please upload csv file only']);
                exit();
            }
        } else {
            echo json_encode(['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.']);
            exit();
        }
    }

    function check_insert_update_line_item_data($line_item_data, $csv_column_arr)
    {

        $weekOfdays = $this->Finance_import_export_line_item->get_week_day_list();
        $timeOfdays = $this->Finance_import_export_line_item->get_time_of_day_list();
        $states = $this->Finance_import_export_line_item->get_state_list();
        $fundingType = $this->Finance_import_export_line_item->get_funding_type_list();
        $supportCategory = $this->Finance_import_export_line_item->get_support_category_list_by_name();
        $supportOutcomeDomain = $this->Finance_import_export_line_item->get_support_outcome_domain_list();
        $classificationLevel = $this->Finance_import_export_line_item->get_classification_level_list();
        $classificationPoint = $this->Finance_import_export_line_item->get_classification_point_list();
        $support_registration_group = $this->Finance_import_export_line_item->get_support_registration_group_list_by_name();

        $cnt = 1;
        
        $bulk_insert_array = array();
        foreach ($line_item_data as $row_value) {
            $cnt++;
            $insert_array = [];

            if (isset($row_value['support funding type']) && !empty($row_value['support funding type'])) {
                $value = strtolower(trim($row_value['support funding type']));
                if (isset($fundingType[$value]) && !empty($fundingType[$value])) {
                    $insert_array['funding_type'] = $fundingType[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Funding Type" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Funding Type" not exist in row ' . $cnt]);
                exit();
            }


            if (isset($row_value['support registration group']) && !empty($row_value['support registration group'])) {
                $value = strtolower(trim($row_value['support registration group']));
                if (isset($support_registration_group[$value]) && !empty($support_registration_group[$value])) {
                    $insert_array['support_registration_group'] = $support_registration_group[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support registration group" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"registration group" not exist in row ' . $cnt]);
                exit();
            }

            if (isset($row_value['support category']) && !empty($row_value['support category'])) {
                $value = strtolower(trim($row_value['support category']));
                if (isset($supportCategory[$value]) && !empty($supportCategory[$value])) {
                    $insert_array['support_category'] = $supportCategory[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support category" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                $insert_array['support_category'] = 0;
                //echo json_encode(['status' => false, 'error' => 'support category not exist in row '.$cnt]);
                //exit(); 
            }

            // -------------------- support outcome domain ----------------------------------------
            if (isset($row_value['support outcome domain']) && !empty($row_value['support outcome domain'])) {
                $value = strtolower(trim($row_value['support outcome domain']));
                if (isset($supportOutcomeDomain[$value]) && !empty($supportOutcomeDomain[$value])) {
                    $insert_array['support_outcome_domain'] = $supportOutcomeDomain[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "support outcome domain" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                $insert_array['support_outcome_domain'] = 0;
                //echo json_encode(['status' => false, 'error' => 'support outcome domain not exist in row '.$cnt]);
                //exit(); 
            }
            // -------------------- type of day ----------------------------------------
            if (isset($row_value['type of day']) && !empty($row_value['type of day'])) {
                $value = strtolower(trim($row_value['type of day']));
                $arr_week = [];
                $arr_weekdays = explode(",", $value);
                foreach ($arr_weekdays as $days) {
                    if (isset($weekOfdays[$days])) {
                        $arr_week[] = $weekOfdays[$days];
                    } else {
                        echo json_encode(['status' => false, 'error' => 'invalid "type of day" (' . $days . ') in row ' . $cnt]);
                        exit();
                    }
                }
                $insert_array['weekofday'] = $arr_week;
            } else {
                //echo json_encode(['status' => false, 'error' => 'Type of day required row '.$cnt]);
                //exit(); 
                $insert_array['weekofday'] = '';
            }

            // -------------------- Time of day ----------------------------------------
            if (isset($row_value['time of day']) && !empty($row_value['time of day'])) {
                $value = strtolower(trim($row_value['time of day']));
                $arr_daytime = [];
                $arr_timeofday = explode(",", $value);
                foreach ($arr_timeofday as $daytime) {
                    if (isset($timeOfdays[$daytime])) {
                        $arr_daytime[] = $timeOfdays[$daytime];
                    } else {
                        echo json_encode(['status' => false, 'error' => 'invalid "time of day" (' . $daytime . ') in row ' . $cnt]);
                        exit();
                    }
                }
                $insert_array['timeofday'] = $arr_daytime;
            } else {
                //echo json_encode(['status' => false, 'error' => 'Time of day required row '.$cnt]);
                //exit(); 
                $insert_array['timeofday'] = '';
            }

            // -------------------- states ----------------------------------------

            if (isset($row_value['state']) && !empty($row_value['state'])) {
                $value = strtolower(trim($row_value['state']));
                $arr_state = [];
                $arr_states = explode(",", $value);
                foreach ($arr_states as $stateName) {
                    if (isset($states[$stateName])) {
                        $arr_state[] = $states[$stateName];
                    } else {
                        echo json_encode(['status' => false, 'error' => 'invalid "state" (' . $stateName . ') in row ' . $cnt]);
                        exit();
                    }
                }
                $insert_array['states'] = $arr_state;
            } else {
                echo json_encode(['status' => false, 'error' => '"State" required in row ' . $cnt]);
                exit();
            }

            // -------------------- minimum pay point required (pay point 1, pay point 2, pay point 3, pay point 4) ----------------------------------------
            if (isset($row_value['minimum pay point required (pay point 1, pay point 2, pay point 3, pay point 4)']) && !empty($row_value['minimum pay point required (pay point 1, pay point 2, pay point 3, pay point 4)'])) {
                $value = strtolower(trim($row_value['minimum pay point required (pay point 1, pay point 2, pay point 3, pay point 4)']));
                if (isset($classificationPoint[$value]) && !empty($classificationPoint[$value])) {
                    $insert_array['pay_pointId'] = $classificationPoint[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Minimum pay point" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Minimum pay point" required in row ' . $cnt]);
                exit();
            }

            // -------------------- minimum level required (level 1, level 2, upto level 8) ----------------------------------------
            if (isset($row_value['minimum level required (level 1, level 2, upto level 8)']) && !empty($row_value['minimum level required (level 1, level 2, upto level 8)'])) {
                $value = strtolower(trim($row_value['minimum level required (level 1, level 2, upto level 8)']));
                if (isset($classificationLevel[$value]) && !empty($classificationLevel[$value])) {
                    $insert_array['levelId'] = $classificationLevel[$value];
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Minimum level" (' . $value . ') in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Minimum level" required in row ' . $cnt]);
                exit();
            }

            // -------------------- Public holidays ----------------------------------------
            if (isset($row_value['public holiday (yes/no)']) && !empty($row_value['public holiday (yes/no)'])) {
                $value = strtolower(trim($row_value['public holiday (yes/no)']));
                $insert_array['public_holiday'] = 0;
                if ($value == 'yes') {
                    $insert_array['public_holiday'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Public holiday" required in row ' . $cnt]);
                exit();
            }

            // -------------------- quote required? (yes/no) ----------------------------------------
            if (isset($row_value['quote required? (yes/no)']) && !empty($row_value['quote required? (yes/no)'])) {
                $value = strtolower(trim($row_value['quote required? (yes/no)']));
                $insert_array['quote_required'] = 0;
                if ($value == 'yes') {
                    $insert_array['quote_required'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Quote Required" required in row ' . $cnt]);
                exit();
            }

            // -------------------- does this item have a price control? (yes/no) ----------------------------------------
            if (isset($row_value['does this item have a price control? (yes/no)']) && !empty($row_value['does this item have a price control? (yes/no)'])) {
                $value = strtolower(trim($row_value['does this item have a price control? (yes/no)']));
                $insert_array['price_control'] = 0;
                if ($value == 'yes') {
                    $insert_array['price_control'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Does this Item have a Price Control" required in row ' . $cnt]);
                exit();
            }

            // -------------------- travel required (yes/no) ----------------------------------------
            if (isset($row_value['travel required (yes/no)']) && !empty($row_value['travel required (yes/no)'])) {
                $value = strtolower(trim($row_value['travel required (yes/no)']));
                $insert_array['travel_required'] = 0;
                if ($value == 'yes') {
                    $insert_array['travel_required'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Travel required" required in row ' . $cnt]);
                exit();
            }

            // -------------------- cancellation fees (yes/no) ----------------------------------------
            if (isset($row_value['cancellation fees (yes/no)']) && !empty($row_value['cancellation fees (yes/no)'])) {
                $value = strtolower(trim($row_value['cancellation fees (yes/no)']));
                $insert_array['cancellation_fees'] = 0;
                if ($value == 'yes') {
                    $insert_array['cancellation_fees'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Cancellation fees" required in row ' . $cnt]);
                exit();
            }

            // -------------------- ndia reporting? (yes/no) ----------------------------------------
            if (isset($row_value['ndia reporting? (yes/no)']) && !empty($row_value['ndia reporting? (yes/no)'])) {
                $value = strtolower(trim($row_value['ndia reporting? (yes/no)']));
                $insert_array['ndis_reporting'] = 0;
                if ($value == 'yes') {
                    $insert_array['ndis_reporting'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"NDIA reporting" required in row ' . $cnt]);
                exit();
            }

            // -------------------- ndia reporting? (yes/no) ----------------------------------------
            if (isset($row_value['non-f2f? (yes/no)']) && !empty($row_value['non-f2f? (yes/no)'])) {
                $value = strtolower(trim($row_value['non-f2f? (yes/no)']));
                $insert_array['non_f2f'] = 0;
                if ($value == 'yes') {
                    $insert_array['non_f2f'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Non-F2F" required in row ' . $cnt]);
                exit();
            }

            // -------------------- does this item have a schedule constraint? (yes/no) ----------------------------------------
            if (isset($row_value['does this item have a schedule constraint? (yes/no)']) && !empty($row_value['does this item have a schedule constraint? (yes/no)'])) {
                $value = strtolower(trim($row_value['does this item have a schedule constraint? (yes/no)']));
                $insert_array['schedule_constraint'] = 0;
                if ($value == 'yes') {
                    $insert_array['schedule_constraint'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"schedule constraint" required in row ' . $cnt]);
                exit();
            }

            // -------------------- does this item ONCALL provided? (yes/no) ----------------------------------------
            if (isset($row_value['oncall provided (yes/no)']) && !empty($row_value['oncall provided (yes/no)'])) {
                $value = strtolower(trim($row_value['oncall provided (yes/no)']));
                $insert_array['oncall_provided'] = 0;
                if ($value == 'yes') {
                    $insert_array['oncall_provided'] = 1;
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"ONCALL provided" required in row ' . $cnt]);
                exit();
            }

            // -------------------- line item description ----------------------------------------
            if (isset($row_value['line item description']) && !empty($row_value['line item description'])) {
                if (strlen($row_value['line item description']) > 1000) {
                    echo json_encode(['status' => false, 'error' => '"Line Item Description" can not be more than 1000 characters, in row ' . $cnt]);
                    exit();
                }
                $insert_array['description'] = $row_value['line item description'];
            } else {
                $insert_array['description'] = '';
                //echo json_encode(['status' => false, 'error' => 'Line item descrioption required row '.$cnt]);
                //exit(); 
            }

            // -------------------- upper price limit (national non remote) ----------------------------------------
            $max_value = 99999.99;
            $find_dollar = '$';
            if (isset($row_value['upper price limit (national non remote)']) && !empty($row_value['upper price limit (national non remote)'])) {
                $value = strtolower(trim($row_value['upper price limit (national non remote)']));
                $value = trim($value);
                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "Upper Price Limit (National Non Remote)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "Upper Price Limit (National Non Remote)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "Upper Price Limit (National Non Remote)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }


                $value = (float) $value; //, 2, '.', '');
                //var_dump($value);
                if (is_numeric($value) || is_float($value)) {
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "Upper Price Limit (National Non Remote)", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"Upper Price Limit (National Non Remote)" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } elseif ($check_positive > 0) {
                        $insert_array['upper_price_limit'] = $check_positive;
                    } else {
                        echo json_encode(['status' => false, 'error' => '"Upper Price Limit (National Non Remote)" value should be greater than 0 in, row ' . $cnt]);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "Upper Price Limit (National Non Remote)", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Upper Price Limit (National Non Remote)" required in row ' . $cnt]);
                exit();
            }

            // -------------------- National Remote Price ----------------------------------------
            if (isset($row_value['national remote price']) && !empty($row_value['national remote price'])) {
                $value = strtolower(trim($row_value['national remote price']));

                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "National Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }

                $value = (float) $value;
                if (is_numeric($value) || is_float($value)) {
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "National Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"National Remote Price" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } else {
                        $insert_array['national_price_limit'] = $check_positive;
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                $insert_array['national_price_limit'] = 0.00;
            }


            // -------------------- national very remote price ----------------------------------------
            if (isset($row_value['national very remote price']) && !empty($row_value['national very remote price'])) {
                $value = strtolower(trim($row_value['national very remote price']));
                $value = str_replace(',', '', $value);
                $dollar_count = substr_count($value, $find_dollar);
                if ($dollar_count > 1) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Very Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
                $dollar_position = strpos($value, $find_dollar);
                // echo $dollar_position."dollar";
                if ($dollar_position > 0) {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Very Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                } elseif (strlen($dollar_position) == 1) {
                    $value = substr($value, 1);
                } elseif ($dollar_position == 0) {
                }

                if (strlen($value) > 0) {
                    if (preg_match('/^[0-9]+(.[0-9]+)?$/', $value)) {
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Invalid value in "National Very Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    }
                }

                $value = (float) $value;
                if (is_numeric($value) || is_float($value)) {
                    // $value = preg_replace("/[^0-9.]/", "", $value);
                    $check_positive = (float) number_format((float) $value, 2, '.', '');
                    if ($check_positive < 0) {
                        echo json_encode(['status' => false, 'error' => 'Only positive values are accepted in "National Very Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                        exit();
                    } elseif ($check_positive > $max_value) {
                        echo json_encode(['status' => false, 'error' => '"National Very Remote Price" value should be less than ' . $max_value . ' in, row ' . $cnt]);
                        exit();
                    } else {
                        $insert_array['national_very_price_limit'] = $check_positive;
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'Invalid value in "National Very Remote Price", Please check row ' . $cnt . ' and try importing the file again.']);
                    exit();
                }
            } else {
                $insert_array['national_very_price_limit'] = 0.00;
            }

            // -------------------- member to participant ratio ----------------------------------------
            if (isset($row_value['member to participant ratio']) && !empty($row_value['member to participant ratio'])) {
                $value = strtolower(trim($row_value['member to participant ratio']));

                $arr_member = explode(":", $value);
                if (count($arr_member) == 2) {
                    $memberRatio = (int) $arr_member[0];

                    if ($memberRatio < 1) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row ' . $cnt]);
                        exit();
                    } elseif ($memberRatio > 9) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row ' . $cnt]);
                        exit();
                    }
                    if (!preg_match('/^[1-9]*$/', $memberRatio)) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row 1' . $cnt]);
                        exit();
                    } else {
                        $insert_array['member_ratio'] = $memberRatio;
                    }


                    $partRatio = (int) $arr_member[1];
                    if ($partRatio < 1) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row ' . $cnt]);
                        exit();
                    } elseif ($partRatio > 9) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row ' . $cnt]);
                        exit();
                    }
                    if (!preg_match('/^[1-9]*$/', $partRatio)) {
                        echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" value should be between 1 to 9 in row ' . $cnt]);
                        exit();
                    } else {
                        $insert_array['participant_ratio'] = $partRatio;
                    }
                } else {
                    echo json_encode(['status' => false, 'error' => 'invalid "Member to Participant Ratio" in row ' . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => '"Member to Participant Ratio" required in row ' . $cnt]);
                exit();
            }

            // -------------------- start date (dd/mm/yyyy) / end date (dd/mm/yyyy) ----------------------------------------
            if (!isset($row_value['start date (dd-mm-yyyy)']) && empty($row_value['start date (dd-mm-yyyy)'])) {
                echo json_encode(['status' => false, 'error' => 'Line item "start date" required in row ' . $cnt]);
                exit();
            }
            if (!isset($row_value['end date (dd-mm-yyyy)']) && empty($row_value['end date (dd-mm-yyyy)'])) {
                echo json_encode(['status' => false, 'error' => 'Line item "end date" required in row ' . $cnt]);
                exit();
            }
            $strStartDate = $row_value['start date (dd-mm-yyyy)'];
            $strEndDate = $row_value['end date (dd-mm-yyyy)'];

            $arrStart = $this->checkIsAValidDate($strStartDate);

            if (!$arrStart['status']) {
                echo json_encode(['status' => false, 'error' => 'invalid "start date" in row ' . $cnt]);
                exit();
            }
            $arrEnd = $this->checkIsAValidDate($strEndDate);
            if (!$arrEnd['status']) {
                echo json_encode(['status' => false, 'error' => 'invalid "end date" in row ' . $cnt]);
                exit();
            }

            $start_date = $arrStart['date'];
            $end_date = $arrEnd['date'];

            $str_start_date = strtotime($start_date);
            $str_end_date = strtotime($end_date);

            if ($str_start_date > $str_end_date) {
                echo json_encode(['status' => false, 'error' => '"Start date" must be greater than or equal to "end date" in row ' . $cnt]);
                exit();
            }

            $insert_array['start_date'] = $start_date;
            $insert_array['end_date'] = $end_date;

            // -------------------- Line item name ----------------------------------------
            if (isset($row_value['line item name']) && !empty($row_value['line item name'])) {
                if (strlen($row_value['line item name']) > 255) {
                    echo json_encode(['status' => false, 'error' => '"Line Item name" can not be more than 255 characters, in row ' . $cnt]);
                    exit();
                }
                $insert_array['line_item_name'] = $row_value['line item name'];
            } else {
                echo json_encode(['status' => false, 'error' => '"Line item name" required in row ' . $cnt]);
                exit();
            }

            // -------------------- reference number ----------------------------------------
            if (isset($row_value['reference number']) && !empty($row_value['reference number'])) {
                if (strlen($row_value['reference number']) > 40) {
                    echo json_encode(['status' => false, 'error' => 'Line Item "Reference Number" can not be more than 40 characters, in row ' . $cnt]);
                    exit();
                }
                $insert_array['line_item_number'] = $row_value['reference number'];
                $insert_array['measure_by'] = 1;
            } else {
                echo json_encode(['status' => false, 'error' => 'Line item "reference number" required in row ' . $cnt]);
                exit();
            }

            // -------------------- category reference ----------------------------------------
            if (isset($row_value['is category (yes/no)']) && !empty($row_value['is category (yes/no)'])) {
                $bIsCategory = strtolower(trim($row_value['is category (yes/no)']));
                if ($bIsCategory == 'no') {
                    if (isset($row_value['category ref']) && !empty($row_value['category ref'])) {
                        if (strlen($row_value['category ref']) > 40) {
                            echo json_encode(['status' => false, 'error' => 'Line Item "Category Ref" can not be more than 40 characters, in row ' . $cnt]);
                            exit();
                        }
                        $insert_array['category_ref'] = $row_value['category ref'];
                    } else {
                        echo json_encode(['status' => false, 'error' => 'Line item is not a Category - "Category Ref" required in row ' . $cnt]);
                        exit();
                    }
                } else if ($bIsCategory == 'yes') {
                    if (!empty($row_value['category ref'])) {
                        echo json_encode(['status' => false, 'error' => "Line item \"Is Category\" is set to 'yes' - \"Category Ref\" must be empty on row " . $cnt]);
                        exit();
                    }
                } else if ($bIsCategory != 'yes') {
                    echo json_encode(['status' => false, 'error' => "Line item \"Is Category\" must be set to 'yes' or 'no' in row " . $cnt]);
                    exit();
                }
            } else {
                echo json_encode(['status' => false, 'error' => 'Line item "Is Category" required in row ' . $cnt]);
                exit();
            }

            // -------------------- category reference ----------------------------------------
            if (isset($row_value['units']) && !empty($row_value['units'])) {
                $val = strtolower(trim($row_value['units']));
                switch ($val) {
                    case 'each': 
                        $insert_array['units'] = LineItemUnits::each;                
                        break;
                    case 'hourly': 
                        $insert_array['units'] = LineItemUnits::hourly;                
                        break;
                    case 'daily': 
                        $insert_array['units'] = LineItemUnits::daily;                
                        break;
                    case 'weekly': 
                        $insert_array['units'] = LineItemUnits::weekly;                
                        break;
                    case 'monthly': 
                        $insert_array['units'] = LineItemUnits::monthly;                
                        break;
                    case 'annually': 
                        $insert_array['units'] = LineItemUnits::annually;                
                        break;
                    case 'km': 
                        $insert_array['units'] = LineItemUnits::km;                
                        break;
                    default:
                        echo json_encode(['status' => false, 'error' => '\'Units\' must be one of - Each | Hourly | Daily | Weekly | Monthly | Annually | KM' . $cnt]);
                        exit();
                }
            }

            $isValid = $this->validate_csv_duplicate_records($bulk_insert_array, $insert_array, $cnt);
            if ($isValid) {
                $valid_table_response = $this->Finance_import_export_line_item->validate_csv_import_data_between_date($insert_array);
                if (!empty($valid_table_response)) {
                    echo json_encode(['status' => false, 'error' => 'Line item "' . $insert_array['line_item_number'] . '" already exists in the system for the specified dates. Please try importing the file again, row ' . $cnt]);
                    exit();
                } else {
                    $bulk_insert_array[] = $insert_array;
                }
            }
        }

        if (!empty($bulk_insert_array)) {
            $res_insert = $this->Finance_import_export_line_item->insert_bulk_import_line_item($bulk_insert_array);
            if ($res_insert) {
                $this->loges->createLog();
                return ["status" => true, "message" => "Successful file import"];
            } else {
                return ["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."];
            }
        } else {
            return ["status" => false, "error" => "Data not exist in file."];
        }
    }

    function validate_csv_duplicate_records($bulk_insert_array, $insert_array, $cnt)
    {

        //'line_item_name'=>$insert_array['line_item_name'],
        $arrValidDate = $this->multiSearch($bulk_insert_array, array('line_item_number' => $insert_array['line_item_number']));
        if (!empty($arrValidDate)) {
            $currunt_date = date("d-m-Y", strtotime(DATE_TIME));
            $str_currunt_date = strtotime(DATE_TIME);
            $cur_str_startDate = strtotime($insert_array['start_date']);
            $cur_str_endDate = strtotime($insert_array['end_date']);

            foreach ($arrValidDate as $key => $value) {
                //funding_type

                $startDate = $value['start_date'];
                $endDate = $value['end_date'];
                $str_start_date = strtotime($startDate);
                $str_end_date = strtotime($endDate);
                if ($cur_str_startDate >= $str_start_date && $cur_str_startDate <= $str_end_date) {
                    // Start date is greater then currunt date
                    echo json_encode(['status' => false, 'error' => 'Duplicate Line Item already present in csv with overlapping Start and End date, in row ' . $cnt]);
                    exit();
                } elseif ($cur_str_endDate >= $str_start_date && $cur_str_endDate <= $str_end_date) {
                    // Start date is greater then currunt date
                    echo json_encode(['status' => false, 'error' => 'Duplicate Line Item already present in csv with overlapping Start and End date, in row ' . $cnt]);
                    exit();
                } elseif ($str_start_date >= $cur_str_startDate && $str_start_date <= $cur_str_startDate) {
                    // Start date is greater then currunt date
                    echo json_encode(['status' => false, 'error' => 'Duplicate Line Item already present in csv with overlapping Start and End date, in row ' . $cnt]);
                    exit();
                } elseif ($str_end_date >= $cur_str_endDate && $str_end_date <= $cur_str_endDate) {
                    // Start date is greater then currunt date
                    echo json_encode(['status' => false, 'error' => 'Duplicate Line Item already present in csv with overlapping Start and End date, in row ' . $cnt]);
                    exit();
                }
            }
        }
        return true;
    }

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
            if ($coincidences > 0) { // count($pairs)) {
                $found[$aKey] = $aVal;
            }
        }

        return $found;
    }

    function check_funding_type()
    {
    }

    function checkIsAValidDate($myDateString)
    {
        $type = 'd-m-Y';
        $status = false;
        $validdate = '';
        switch ($type) {
            case 'd-m-Y':
                $valid = validateDateWithFormat($myDateString, 'd-m-Y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d-m-Y', $myDateString);
                    break;
                }
            case 'd-m-y':
                $valid = validateDateWithFormat($myDateString, 'd-m-y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d-m-y', $myDateString);
                    break;
                }

            case 'd/m/y':
                $valid = validateDateWithFormat($myDateString, 'd/m/y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d/m/y', $myDateString);
                    break;
                }
            case 'd/m/Y':
                $valid = validateDateWithFormat($myDateString, 'd/m/Y');
                if ($valid) {
                    $status = true;
                    $dateTime = DateTime::createFromFormat('d/m/Y', $myDateString);
                    break;
                }
        }

        if ($status) {
            $validdate = $dateTime->format('Y-m-d');
            return ['status' => $status, 'date' => $validdate];
        }
        return ['status' => $status];
    }

    /** End New Code here */
}