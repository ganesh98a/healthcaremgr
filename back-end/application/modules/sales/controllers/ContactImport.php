<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * class : Contact
 * use : use for handle contact request and response  
 * 
 * @property-read \Contact_model $Contact_model
 */
class ContactImport extends MX_Controller {
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
     * Valid - Inserting Coding check_insert_contact()
     */
    function read_csv_contacts()
    {
        $data = request_handlerFile('access_finance_line_item');
            $csv_column_arr = array(
            "first name", "last name","phone","email","dob (dd-mm-yyyy)","address","aboriginal or torres strait islander heritage (aboriginal,torres strait islander,both,neither)","preferred communication method (phone,email,post,sms)","religion",
            "cultural practices observed","type (applicant,lead,participant,booker,agent,organisation)","source","status (active,inactive)","ndis number");   
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
                $contact_data = [];
                if (!empty($file)) {
                    sort($col_header);
                    $arColMatch = array_diff($csv_column_arr, $col_header);
                    if (empty($arColMatch)) {
                        foreach ($file as $row) {
                            if (count($row) == count($header)) {
                                $row = array_map("utf8_encode", $row);
                                $contact_data[] = array_combine($header, $row);
                            } else {
                                echo json_encode(["status" => false, "error" => "Unsuccessful file import. Please try importing the file again."]);
                                exit();
                            }
                        }
                        if (!empty($contact_data)) {
                            $response = $this->check_insert_contact($contact_data, $csv_column_arr);
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
     * contact_data - Parsed data from csv files 
     * csv_column_arr - Columns to be Inserted
     * get_lead_source - Getting Lead source from Reference module
     */
    function check_insert_contact($contact_data, $csv_column_arr){
        $dupemilcnt =1;
        $cnt = 1;
        $insert_person_array = array();
        $insert_email_array = array();
        $insert_phone_array = array();
        $insert_address_array = array();
        $email_array = array();
        $ndis_array = array();
        // Defining Option array as like contact forms
        $aboriginal_array = array('1'=>"aboriginal",'2'=>"torres strait islander",'3'=>"both",'4'=>"neither");
        $pref_communication_array = array('1'=>"phone",'2'=>"email",'3'=>"post",'4'=>"sms");
        $contact_type_array = array('1'=>"applicant",'2'=>"lead",'3'=>"participant",'4'=>"booker",'5'=>"agent",'6'=>"organisation");
        $status_option_array = array('1'=>"active",'0'=>"inactive");
        $this->load->model("Lead_model");
        $source_options = $this->Lead_model->get_lead_source();
        foreach ($source_options as $source_option) {
            $source_option_array[$source_option['value']] =  strtolower($source_option['label']);
        }
        /**
         * All columns validations 
         * getting array of all email and ndis, rows with keys 
         * getting array of all exact rows - email and ndis
         * forming duplicates arrays
         */
        foreach ($contact_data as $key => $row_value) {
            $cnt++;
            $email_array[] = trim($row_value['email']);
            $ndis_array[] = trim($row_value['ndis number']);
            $getRowArray[$key+2] = trim($row_value['email']);
            $getRowndisArray[$key+2] = trim($row_value['ndis number']);
            $dup_array[$key]['email'] = trim($row_value['email']);
            $dup_array[$key]['ndis']  = trim($row_value['ndis number']);

            $lname_trimed = trim($row_value['last name']);
            if ($lname_trimed != "") {
                if(strlen($lname_trimed) > 40){
                    echo json_encode(['status' => false, 'error' => 'Maxlength of Last Name is 40 in row ' . $cnt]);
                    exit();
                }
             }else {
             echo json_encode(['status' => false, 'error' => 'Last Name not exist in row ' . $cnt]);
             exit();
             } 
             $trimmed_fname = trim($row_value['first name']); 
             if ($trimmed_fname != "") {
                if(strlen($trimmed_fname) > 40){
                    echo json_encode(['status' => false, 'error' => 'Maxlength of First Name is 40 in row ' . $cnt]);
                    exit();
                }  
            } 
            $trimmed_phone = trim($row_value['phone']); 
            if ($trimmed_phone != "") {
                if (!is_numeric($trimmed_phone)) {
                    echo json_encode(['status' => false, 'error' => 'Enter Valid Mobile Number in row ' . $cnt]);
                    exit();
                }
                if(strlen($trimmed_phone) > 18){
                    echo json_encode(['status' => false, 'error' => 'Maxlength of Phone Number is 18 in row ' . $cnt]);
                    exit();      
                 }
            }
            $trimmed_address = trim($row_value['address']); 
            if ($trimmed_address != "") {
                $address = $trimmed_address;
                $addr = devide_google_or_manual_address($address);
                $wrong_address_standard = false;
                if (!$addr["street"]) {
                    $wrong_address_standard = true;
                } elseif (!$addr["suburb"]) {
                    $wrong_address_standard = true;
                } elseif (!$addr["state"]) {
                    $wrong_address_standard = true;
                } else if (!$addr["postcode"]) {
                    $wrong_address_standard = true;
                }
                if ($wrong_address_standard) {
                    echo json_encode(['status' => false, 'error' => 'Please provide valid address like street, suburb state postcode in row ' . $cnt]);
                    exit();   
                }
            } 
             $trimmed_dob = trim($row_value['dob (dd-mm-yyyy)']); 
            if($trimmed_dob != "") {
                $date = $trimmed_dob;
                $hypenformat = 'd-m-Y';  $slashformat = 'd/m/Y';
                $d = DateTime::createFromFormat($hypenformat, $date);
                $hyphenresult =  $d && $d->format($hypenformat) === $date;
                $checkhypenIsdate = $hyphenresult;
                $sd = DateTime::createFromFormat($slashformat, $date);
                $slahresult =  $sd && $sd->format($slashformat) === $date;
                $checkslashIsdate = $slahresult;
                if(empty($checkhypenIsdate)){
                    echo json_encode(['status' => false, 'error' => 'DOB is not in Date Format (dd-mm-yyyy) in row ' . $cnt]);
                    exit();
                }
            }
            $trimmed_aborg = trim($row_value['aboriginal or torres strait islander heritage (aboriginal,torres strait islander,both,neither)']);
            if ($trimmed_aborg != "") {
                $aboriginal = $trimmed_aborg;
                $aboriginal_val = strtolower($aboriginal);
                if (!in_array($aboriginal_val,$aboriginal_array))
                {
                    echo json_encode(['status' => false, 'error' => 'Aboriginal or Torres Strait Islander heritage Field Should contains of one of these (Aboriginal,Torres Strait Islander,Both,Neither) in row ' . $cnt]);
                    exit();
                }
            }
            $trmimmed_preferred_comm = trim($row_value['preferred communication method (phone,email,post,sms)']);
            if ($trmimmed_preferred_comm != "") {
                $prefere_comm = $trmimmed_preferred_comm;
                $prefere_comm_val = strtolower($prefere_comm);
                if (!in_array($prefere_comm_val,$pref_communication_array))
                {
                  echo json_encode(['status' => false, 'error' => 'Preferred Communication Method Field Should contains of one of these (Phone,Email,Post,Sms) in row ' . $cnt]);
                  exit();    
                }
            }
            $trimmed_type = trim($row_value['type (applicant,lead,participant,booker,agent,organisation)']);
            if ($trimmed_type != "") {
                $contact_type = $trimmed_type;
                $contact_type_val = strtolower($contact_type);
                if (!in_array($contact_type_val,$contact_type_array))
                {
                  echo json_encode(['status' => false, 'error' => 'Type Field Should contains of one of these (applicant,lead,participant,booker,agent,organisation) in row ' . $cnt]);
                  exit();
               }
            }
            $trimmed_status = trim($row_value['status (active,inactive)']);
            if ($trimmed_status != "") {
                $status = $trimmed_status;
                $status_val = strtolower($status);
                if (!in_array($status_val,$status_option_array)){
                    echo json_encode(['status' => false, 'error' => 'Status Field Should contains of one of these (Active,Inactive) in row ' . $cnt]);
                     exit();
                }
                }else {
                    echo json_encode(['status' => false, 'error' => 'Status not exist in row ' . $cnt]);
                    exit();
                }
                $trimmed_source = trim($row_value['source']);
                if ($trimmed_source != "") {
                    $source = $trimmed_source;
                    $source_val = strtolower($source);
                    if (!in_array($source_val,$source_option_array)){
                    echo json_encode(['status' => false, 'error' => 'Source Field Should contains of one of the Lead Source In Reference Data in row ' . $cnt]);
                    exit();
                    }
                 }
                 $trimmed_ndis = trim($row_value['ndis number']);
                if ($trimmed_ndis != "") {
                    $check_ndis = $trimmed_ndis;
                    if (!is_numeric($check_ndis) || strlen($check_ndis) != 9) {
                     echo json_encode(['status' => false, 'error' => 'Enter Valid NDIS Number in row ' . $cnt]);
                    exit();
                    } 
                }
                $trimmed_email = trim($row_value['email']);
                if($trimmed_email != "") {
                    if (!filter_var($trimmed_email, FILTER_VALIDATE_EMAIL)) {
                        echo json_encode(['status' => false, 'error' => 'Enter Valid Email in row ' . $cnt]);
                        exit();
                    }
                }
            }

        // find duplicate emails and ndis in csv
        $array_email_temp = array();
        $array_ndis_temp = array();
        foreach($dup_array as $val){
            $tm_mail = trim($val['email']);
            $tm_ndis = trim($val['ndis']);
            $dupemilcnt++;
            if($tm_mail != "") {
                if (!in_array($tm_mail, $array_email_temp)){
                $array_email_temp[] = $tm_mail;
                }else{
                echo json_encode(['status' => false, 'error' => 'Duplicate Email in row ' . $dupemilcnt]);
                exit();
               }
            }
            if($tm_ndis != "") {
                if (!in_array($tm_ndis, $array_ndis_temp)){
                $array_ndis_temp[] = $tm_ndis;
                }else{
                echo json_encode(['status' => false, 'error' => 'Duplicate NDIS Number in row ' . $dupemilcnt]);
                exit();
               }
            }
        }
        // db checking for existing emails and ndis with csv
        $fetchemail = "[]";
        if(count(array_filter($email_array))> 0) {
        $fetchemail =  $email_array;
        }
        $fetchndis = "[]";
        if(count(array_filter($ndis_array))> 0) {
        $fetchndis =  $ndis_array;
        }
        $this->db->select(["pe.email","p.ndis_number","p.id"]);
        $this->db->from("tbl_person as p");
        $this->db->join("tbl_person_email as pe", "pe.person_id = p.id", "INNER");
        $this->db->where_in("pe.email", $fetchemail);
        $this->db->or_where_in("p.ndis_number", $fetchndis);
        $check_exists_data =  $this->db->get()->result();
        $check_exists_data_array = array_filter(json_decode(json_encode($check_exists_data), true));
        $getmailIds = [];
        $getndis = [];
        foreach ($check_exists_data_array as $get_existing_data) {
            $getmailIds[] = $get_existing_data['email'];
            $getndis[] = $get_existing_data['ndis_number'];
        }
        foreach ($contact_data as $row_value) {
            $trm_mail = trim($row_value['email']);
            $trm_ndis = trim($row_value['ndis number']);
            if($trm_mail != "") {
            if(in_array($trm_mail,$getmailIds)){
                $getmailrow = array_search($trm_mail, $getRowArray); 
                echo json_encode(['status' => false, 'error' => 'Email Id Already Exists in row ' . $getmailrow]);
                exit();
             }
            }
            if($trm_ndis != "") {
                if(in_array($trm_ndis,$getndis)){
                    $getndisrow = array_search($trm_ndis, $getRowndisArray); 
                    echo json_encode(['status' => false, 'error' => 'NDIS Number Already Exists in row ' . $getndisrow]);
                    exit();
                 }
            } 
        }

        // Started to Inserting Datas 
        foreach ($contact_data as $row_value) {
            $insert_array = [];
            $insert_person_array['firstname'] = "";
            $insert_person_array['lastname'] = "";
            $insert_email_array['email'] = "";
            $insert_email_array['archive'] = 0;
            $insert_email_array['primary_email'] = 1; 
            $insert_phone_array['phone'] = ""; 
            $insert_phone_array['primary_phone'] = 1; 
            $insert_person_array['date_of_birth'] = "";
            $insert_address_array['street'] ="";
            $insert_address_array['state'] = null;
            $insert_address_array['suburb'] = "";
            $insert_address_array['postcode'] = "";
            $insert_address_array['primary_address'] = 0;
            $insert_person_array['aboriginal'] = 0;
            $insert_person_array['communication_method'] = 0;
            $insert_person_array['religion'] = "";
            $insert_person_array['cultural_practices'] = "";
            $insert_person_array['type'] = null;
            $insert_person_array['person_source'] = "";
            $insert_person_array['ndis_number'] = "";
            $insert_person_array['archive'] = 0;
            $lname_trimed = trim($row_value['last name']);
            $trimmed_fname = trim($row_value['first name']); 
            $trimmed_phone = trim($row_value['phone']); 
            $trimmed_address = trim($row_value['address']); 
            $trimmed_dob = trim($row_value['dob (dd-mm-yyyy)']); 
            $trimmed_aborg = trim($row_value['aboriginal or torres strait islander heritage (aboriginal,torres strait islander,both,neither)']);
            $trmimmed_preferred_comm = trim($row_value['preferred communication method (phone,email,post,sms)']);
            $trimmed_type = trim($row_value['type (applicant,lead,participant,booker,agent,organisation)']);
            $trimmed_religion = trim($row_value['religion']);
            $trimmed_cultural = trim($row_value['cultural practices observed']);
            $trimmed_status = trim($row_value['status (active,inactive)']);
            $trimmed_source = trim($row_value['source']);
            $trimmed_ndis = trim($row_value['ndis number']);
            $trimmed_email = trim($row_value['email']);
           
            if($trimmed_fname != "") {
            $insert_person_array['firstname'] = $trimmed_fname;
            }
            if($lname_trimed != "") {
            $insert_person_array['lastname'] = $lname_trimed;
            }
            if($trimmed_email != "") {
            $insert_email_array['email'] = $trimmed_email;
            }
            if($trimmed_phone != "") {
            $insert_phone_array['phone'] = $trimmed_phone;
            }
            if($trimmed_dob != "") {
            $insert_person_array['date_of_birth'] =  date("Y-m-d", strtotime($trimmed_dob));
            }
            if($trimmed_address != "") {
            $addr = [];
            $address = devide_google_or_manual_address($trimmed_address);
            $addr = [
                'street' => $address['street'] ?? '',
                'state' => !empty($address["state"]) ? $address["state"] : null,
                'suburb' => $address['suburb'] ?? '',
                'postcode' => $address['postcode'] ?? '',
            ];
            $insert_address_array['street'] = $addr['street'];
            $insert_address_array['state'] = $addr['state'];
            $insert_address_array['suburb'] = $addr['suburb'];
            $insert_address_array['postcode'] = $addr['postcode'];
            $insert_address_array['primary_address'] = 1;
            }
            if($trimmed_aborg != "") {
            $aboriginal = $trimmed_aborg;
            $aboriginal_val = strtolower($aboriginal);
            if (in_array($aboriginal_val,$aboriginal_array)){
            $aboriginal_id = array_search($aboriginal_val, $aboriginal_array); 
            $insert_person_array['aboriginal'] = $aboriginal_id;
            }
            }
            if ($trmimmed_preferred_comm != "") {
            $prefere_comm = $trmimmed_preferred_comm;
            $prefere_comm_val = strtolower($prefere_comm);
            if (in_array($prefere_comm_val,$pref_communication_array)){
            $pref_communication_id = array_search($prefere_comm_val, $pref_communication_array); 
            $insert_person_array['communication_method'] = $pref_communication_id;
            }
            }
            if ($trimmed_religion != "") {
            $insert_person_array['religion'] = $trimmed_religion;
            }
            if ($trimmed_cultural != "") {
            $insert_person_array['cultural_practices'] = $trimmed_cultural;
            }
            if ($trimmed_type != "") {
            $contact_type = $trimmed_type;
            $contact_type_val = strtolower($contact_type);
            if (in_array($contact_type_val,$contact_type_array))
            {
            $contact_type_id = array_search($contact_type_val, $contact_type_array); 
            $insert_person_array['type'] = $contact_type_id;
            }
            }
            if ($trimmed_status != "") {
            $status = $trimmed_status;
            $status_val = strtolower($status);
            if (in_array($status_val,$status_option_array))
            {
            $status_id = array_search($status_val, $status_option_array); 
            $insert_person_array['status'] = $status_id;
            }
            }
            if ($trimmed_source != "") {
            $source = $trimmed_source;
            $source_val = strtolower($source);
            if (in_array($source_val,$source_option_array))
            {
            $source_id = array_search($source_val, $source_option_array); 
            $insert_person_array['person_source'] = $source_id;
            }
            }
            if($trimmed_ndis != "") {
            $insert_person_array['ndis_number'] = $trimmed_ndis;  
            }
            $get_lastinsterted_person_id =  $this->db->insert('tbl_person', $insert_person_array);
            $get_person_id =  $this->db->insert_id();
            $insert_phone_array['person_id'] = $get_person_id;
            $insert_email_array['person_id'] = $get_person_id;
            $insert_address_array['person_id'] = $get_person_id;
            $this->db->insert('tbl_person_phone', $insert_phone_array);
            $this->db->insert('tbl_person_email', $insert_email_array);
            
            $this->Contact_model->create_contact_addresses($insert_address_array);
        }
        return ["status" => true, "message" => "Successful file import"];
    }
}