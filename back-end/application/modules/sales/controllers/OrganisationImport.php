<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : OrganisationImport
 * use : use for handle contact request and response  
 * 
 * @property-read \Contact_model $Contact_model
 */
class OrganisationImport extends MX_Controller {
    // load custon validation traits function
    use formCustomValidation;
    // defualt contruct function
    function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
        $this->form_validation->CI = &$this;
        $this->loges->setLogType('crm');
        $this->load->library('UserName');

        // load contact model 
        $this->load->model('Contact_model');
        $this->load->helper('message');

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

     /**
     * Read the CSV File and checking valid columns 
     * Valid - Inserting Coding check_insert_organisation()
     */
    function read_csv_organisation()
    {
        $data = request_handlerFile('access_finance_line_item');
            $csv_column_arr = array("account name", "abn/acn","parent account","website","phone","fax","billing address","same shipping address (yes)","shipping address","dhhs");   
            sort($csv_column_arr);
        if (empty($_FILES['docsFile']['name'])) {
            echo json_encode(['status' => false, 'error' => 'Please select a csv file to upload.']);
            exit();
        }
        if (!empty($_FILES) && $_FILES['docsFile']['error'] == 0) {
            $this->load->library('csv_reader');
            $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
            if (in_array($_FILES['docsFile']['type'], $mimes)) {
                $tmpName = $_FILES['docsFile']['tmp_name'];                            
                $file = $this->csv_reader->read_csv_data($tmpName);
                $header = array_shift($file);
                $header = array_map('trim', $header);
                $header = array_map('strtolower', $header);
                $col_header = $header;
                $org_data = [];
                if (!empty($file)) {
                    sort($col_header);
                    $arColMatch = array_diff($csv_column_arr, $col_header);
                    if (empty($arColMatch)) {
                        foreach ($file as $row) {
                            if (count($row) == count($header)) {
                                $row = array_map("utf8_encode", $row);
                                $org_data[] = array_combine($header, $row);
                            } else {
                                echo json_encode(["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."]);
                                exit();
                            }
                        }
                        if (!empty($org_data)) {
                            $adminName = $this->username->getName('admin', $data->adminId);
                            $this->loges->setTitle("Import Orgnisation  csv : " . $adminName);
                            $this->loges->setCreatedBy($data->adminId);
                            $this->loges->setUserId($data->adminId);
                            $this->loges->setDescription("Import csv organisation file " . $_FILES['docsFile']['name']);
                            $response = $this->check_insert_organisation($org_data, $csv_column_arr);
                            echo json_encode($response);
                            exit();
                        } else {
                            echo json_encode(['status' => false, 'error' => 'Contact invalid data']);
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
     /**
     * org_data - Parsed data from csv files 
     * csv_column_arr - Columns to be Inserted
     * getorganisations - Getting Organisation from Organisation module
     */
    function check_insert_organisation($org_data, $csv_column_arr){
        $insert_orgn_data = array();
        $insert_orgn_phone_data = array();
        $insert_orgbillingaddress_array = array();
        $insert_orgshipaddress_array = array();
        $cnt = 1;
        $dupacnamecnt = 1;
        $dupcnt = 1;
        $acname_array = [];
        $same_shiping_array= array('1'=>"yes");
        $getorgs_array = [];
        $getorgs = $this->getorganisations();
        $enc_getorgs = json_encode($getorgs);
        if(count($getorgs)>0){
        foreach ($getorgs as $getorgs_options) {
            $getorgs_array[$getorgs_options['value']] =  strtolower($getorgs_options['label']);
         }
        }
        // All Fields validations 
        foreach ($org_data as $key => $row_value) {
            $cnt++;
            $acname_array[] = trim($row_value['account name']);
            $abn_array[] = trim($row_value['abn/acn']); 
            $dhhs_array[] = trim($row_value['dhhs']); 
            $getRowArray[$key+2] = trim($row_value['account name']);
            $getRowndisArray[$key+2] = trim($row_value['abn/acn']);
            $dup_array[$key]['account name'] = trim($row_value['account name']);
            $dup_array[$key]['abn/acn']  = trim($row_value['abn/acn']);

            $trimmed_acname = trim($row_value['account name']); 
            if ($trimmed_acname == "") {
                echo json_encode(['status' => false, 'error' => 'Account Name not exist in row ' . $cnt]);
                exit();
            }
            $trimmed_abnacn = trim($row_value['abn/acn']); 
            if ($trimmed_abnacn != "") {
                $result = $this->get_abn_acn_number_on_base_search($trimmed_abnacn);
                $getres = json_decode($result);
                if($getres->status == ""){
                echo json_encode(['status' => false, 'error' => $getres->error.' in row ' . $cnt]);
                exit();
                } 
                if(!$getres->status){
                echo json_encode(['status' => false, 'error' => $getres->error.' in row ' . $cnt]);
                exit();
                } 
            }
            $trimmed_paracnt = trim($row_value['parent account']); 
            if ($trimmed_paracnt != "") {
                $paraccount = $trimmed_paracnt;
                $paraccount_val = strtolower($paraccount);
                if (!in_array($paraccount_val,$getorgs_array)){
                echo json_encode(['status' => false, 'error' => 'Parent Account Field Should contains one of the Organisation Name from Organisation Data in row ' . $cnt]);
                exit();
                }
            }
            $trimmed_phone = trim($row_value['phone']); 
            if ($trimmed_phone != "") {
                if (!is_numeric($trimmed_phone)) {
                    echo json_encode(['status' => false, 'error' => 'Enter Valid Phone Number in row ' . $cnt]);
                    exit();
                }
                if(strlen($trimmed_phone) > 18){
                    echo json_encode(['status' => false, 'error' => 'Maxlength of Phone Number is 18 in row ' . $cnt]);
                    exit();      
                 }
            }
            $trimmed_dhhs = trim($row_value['dhhs']); 
            if ($trimmed_dhhs != "") {
                if (is_numeric($trimmed_dhhs)) {
                    echo json_encode(['status' => false, 'error' => 'Enter Valid DHHS in row ' . $cnt]);
                    exit();
                }
                else
                {
                    $trimmed_dhhs = strtolower($trimmed_dhhs);
                    if($trimmed_dhhs != "yes" && $trimmed_dhhs != "no")
                    {
                        echo json_encode(['status' => false, 'error' => 'Enter Valid DHHS in row ' . $cnt]);
                        exit();

                    }

                }
                if(strlen($trimmed_phone) > 18){
                    echo json_encode(['status' => false, 'error' => 'Maxlength of Phone Number is 18 in row ' . $cnt]);
                    exit();      
                 }
            }
            $trimmed_same_shipad = trim($row_value['same shipping address (yes)']);
            if ($trimmed_same_shipad != "") {
                $trimmed_sameship = $trimmed_same_shipad;
                $trimmed_sameship_val = strtolower($trimmed_sameship);
                if (!in_array($trimmed_sameship_val,$same_shiping_array))
                {
                  echo json_encode(['status' => false, 'error' => 'Same Shipping Field Should contains only (Yes) Or leave it Blank in row ' . $cnt]);
                  exit();
                }
            }
         }
        // find duplicate acname and abn in csv
        $array_acname_temp = array();
        $array_abn_temp = array();
        foreach($dup_array as $val) {
            $dupcnt++;
            $tm_acname = trim($val['account name']);
            $tm_abn = trim($val['abn/acn']);
            if($tm_acname != "") {
            if (!in_array($tm_acname, $array_acname_temp)){
            $array_acname_temp[] = $tm_acname;
            }else{
            echo json_encode(['status' => false, 'error' => 'Duplicate Account Name in row ' . $dupcnt]);
            exit();
            }
          }
          if($tm_abn != "") {
            if (!in_array($tm_abn, $array_abn_temp)){
            $array_abn_temp[] = $tm_abn;
            }else{
            echo json_encode(['status' => false, 'error' => 'Duplicate ABN/ACN Number in row ' . $dupcnt]);
            exit();
           }
        }
        }
        // db checking for existing emails and abn with csv
        $fetchacname = "[]";
        if(count(array_filter($acname_array))> 0) {
        $fetchacname =  $acname_array;
        }
        $fetchabn = "[]";
        if(count(array_filter($abn_array))> 0) {
        $fetchabn =  $abn_array;
        }
        $this->db->select(array("name","abn"));
        $this->db->from("tbl_organisation");
        $this->db->where_in("name", $acname_array);
        $this->db->or_where_in("abn", $fetchabn);
        $check_exists_data =  $this->db->get()->result();
        $check_exists_data_array = array_filter(json_decode(json_encode($check_exists_data), true));
        $getacnames = [];
        $getabn = [];
        foreach ($check_exists_data_array as $get_existing_data) {
            $getacnames[] = $get_existing_data['name'];
            $getabn[] = $get_existing_data['abn'];
        }
        foreach ($org_data as $row_value) {
            $trm_acname = trim($row_value['account name']);
            $trm_abn = trim($row_value['abn/acn']);
            if($trm_acname != ""){
            if(in_array($trm_acname,$getacnames)){
                $getrow = array_search($trm_acname, $getRowArray); 
                echo json_encode(['status' => false, 'error' => 'Account Name Already Exists in row ' . $getrow]);
                exit();
            }
            if($trm_abn != "") {
                if(in_array($trm_abn,$getabn)){
                    $getabnrow = array_search($trm_abn, $getRowndisArray); 
                    echo json_encode(['status' => false, 'error' => 'ABN/ACN Number Already Exists in row ' . $getabnrow]);
                    exit();
            }
            } 
          }
        }
        // Started Inserting  Datas
        foreach ($org_data as $row_value) {
            $insert_orgn_data = array();
            $insert_orgn_phone_data = array();
            $insert_orgbillingaddress_array = array();
            $insert_orgshipaddress_array = array();
            $address_type = 1; 
            $insert_orgn_data['logo_file'] = "";
            $insert_orgn_data['status'] = 1;
            $insert_orgn_data['archive'] = 0;
            $insert_orgn_data['source_type'] = 3;
            $insert_orgn_data["created"] = DATE_TIME;
            $insert_orgn_data['website'] =  trim($row_value["website"]) ?? "";
            $insert_orgn_data['fax'] =  trim($row_value["fax"]) ?? "";
            $trimmed_abnacn = trim($row_value['abn/acn']); 
            $insert_orgn_data["abn"] = $trimmed_abnacn;
            $trimmed_dhhs = trim($row_value['dhhs']);
            $trimmed_dhhs = strtolower($trimmed_dhhs);

            if($trimmed_dhhs == "yes")
               $trimmed_dhhs = 1;
            else
            $trimmed_dhhs = 0;

            $insert_orgn_data["dhhs"] = $trimmed_dhhs;
            $insert_orgn_data["name"] =  trim($row_value['account name']); 
            if($trimmed_abnacn != ""){
            $result = $this->get_abn_acn_number_on_base_search($trimmed_abnacn);
            $getres = json_decode($result);
            if($getres->status){
              $get_abn_acname = json_decode(json_encode($getres->data), true);
              $insert_orgn_data["abn"] = $get_abn_acname['abn'];
              $insert_orgn_data["name"] = $get_abn_acname['account_name'];
            }
            }
            $trimmed_paracnt = trim($row_value['parent account']); 
            if ($trimmed_paracnt != "") {
                $paraccount = $trimmed_paracnt;
                $paraccount_val = strtolower($paraccount);
                if (in_array($paraccount_val,$getorgs_array)){
                    $par_id = array_search($paraccount_val, $getorgs_array);
                    $insert_orgn_data["parent_org"] = $par_id;
                    }
            }
            $trimmed_phone = trim($row_value['phone']);
            if ($trimmed_phone != "") {
                $insert_orgn_phone_data['phone'] =  $trimmed_phone;
                $insert_orgn_phone_data['primary_phone'] = 1;
                $insert_orgn_phone_data['archive'] =  0;
            }

            $trimmed_billaddress = trim($row_value['billing address']); 
            if ($trimmed_billaddress != "") {
                $insert_orgbillingaddress_array = $this->populate_organisation_address(1,1,$trimmed_billaddress);
            }

            $trimmed_shipaddress = trim($row_value['shipping address']); 
            if ($trimmed_shipaddress != "") {
                $shipaddress_array = $this->populate_organisation_address(1,2,$trimmed_shipaddress);
                $insert_orgshipaddress_array =  $shipaddress_array;
            }

            $trimmed_same_shipad = trim($row_value['same shipping address (yes)']);
            if ($trimmed_same_shipad != "" && $trimmed_shipaddress == "") {
                $insert_orgshipaddress_array =  $insert_orgbillingaddress_array;
                $insert_orgshipaddress_array['address_type'] = 2;
            }
            if ($trimmed_same_shipad != "" && $trimmed_shipaddress != "") {
                $insert_orgshipaddress_array =  $shipaddress_array;
            }
            $get_lastinsterted_person_id =  $this->db->insert('tbl_organisation', $insert_orgn_data);
            $get_orgn_id =  $this->db->insert_id();
            $insert_orgbillingaddress_array['organisationId'] = $get_orgn_id;
            $insert_orgshipaddress_array['organisationId'] = $get_orgn_id;
            $insert_orgn_phone_data['organisationId'] = $get_orgn_id;
            if ($trimmed_phone != "") {
                 $this->db->insert('tbl_organisation_phone', $insert_orgn_phone_data);
            }
            if($trimmed_shipaddress != "" || $trimmed_billaddress != ""){
                $this->load->model("organisation/Org_model");
                if($trimmed_billaddress != ""){
                    $this->Org_model->create_organisation_address($insert_orgbillingaddress_array);
                }
                if (($trimmed_same_shipad != "" && $trimmed_shipaddress != "") || 
                   ($trimmed_same_shipad != "" && $trimmed_shipaddress == "") ||
                   ($trimmed_same_shipad == "" && $trimmed_shipaddress != "") ||
                   ($trimmed_same_shipad != "")){
                
                    $this->Org_model->create_organisation_address($insert_orgshipaddress_array);
                }
            }
        }
        return ["status" => true, "message" => "Successful file import"];
    }

    function populate_organisation_address($is_primary, $address_type, $address_as_string) {
        $address = $address_as_string;
        $addr = devide_google_or_manual_address($address);

        $organisation_address['primary_address'] = $is_primary;
        $organisation_address['address_type'] = $address_type;
        $organisation_address['street'] = $addr["street"] ?? '';
        $organisation_address['city'] = $addr["suburb"] ?? '';
        $organisation_address['postal'] = $addr["postcode"] ?? '';
        $organisation_address['state'] = !empty($addr["state"]) ? $addr["state"] : "";

        return $organisation_address;
    }

    function get_abn_acn_number_on_base_search($abn) {
        if ($abn != "") {
            $this->load->library('abn_search');
            $abn_found = false;
            if ($abn) {
                $srch_record = $this->abn_search->search_name_by_abn_number($abn);
                $rows = array();
                if (!empty($srch_record)) {
                    $srch_record = str_replace('callback(', '', $srch_record);
                    $srch_record = substr($srch_record, 0, strlen($srch_record) - 1); 
                    //strip out last paren
                    $object = json_decode($srch_record); 
                    if (!empty($object->EntityName)) {
                        $abn_found = true;
                        $abn_acn_result = array('abn' => $object->Abn, 'account_name' => $object->EntityName);
                        $res = ["status" => true, "data" => $abn_acn_result];
                    } else {
                        $res = ["status" => false, "error" => "ABN number not found"];
                    }
                }
            }
            if (!$abn_found) {
                $srch_record = $this->abn_search->search_name_by_acn_number($abn);
                $rows = array();
                if (!empty($srch_record)) {
                    $srch_record = str_replace('callback(', '', $srch_record);
                    $srch_record = substr($srch_record, 0, strlen($srch_record) - 1); 
                    //strip out last paren
                    $object = json_decode($srch_record); 
                    if (!empty($object->EntityName)) {
                        $abn_acn_result = array('abn' => $object->Abn, 'account_name' => $object->EntityName);
                        $res = ["status" => true, "data" => $abn_acn_result];
                    } else {
                        $res = ["status" => false, "error" => "ACN/ABN number not found"];
                    }
                }
            }
        }
        else {
            $res = ["status" => false, "data" => "ABN/ACN is mission"];
        }
        return json_encode($res);
    }
    function getorganisations()
    {
        $this->db->select(["name as label", "o.id as value"]);
        $this->db->from("tbl_organisation as o");
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];

    }
    function check_org_address_is_valid($address, $address_type) {
        if (!empty($address)) {
            $addr = devide_google_or_manual_address($address);
            $wrong_address_standard = false;
            if (!$addr["street"]) {
                $wrong_address_standard = true;
            } elseif (!$addr["suburb"]) {
                $wrong_address_standard = true;
            } elseif (!$addr["suburb"]) {
                $wrong_address_standard = true;
            } else if (!$addr["suburb"]) {
                $wrong_address_standard = true;
            }
            return true;
        }
    }
}