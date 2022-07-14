<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class DataImport extends MX_Controller {

    use formCustomValidation;

    /**
     * keep populating following member variable with new data items
     */
    private $data_items = array(
        "0" => array(
            "id" => 1,
            "title" => "Applicants",
            "columns" => array("firstname", "lastname","emails","phones","street", "suburb","state","postal","flagged_reason_title","flagged_reason_notes"),
            "required_columns" => array("firstname", "lastname", "emails", "phones"), // Will be used by React.js to mark required fields with red asterisk.
            "note" => array("")
        ),
        "1" => array(
            "id" => 2,
            "title" => "Members",
            "columns" => array("member_name", "contact_id", "hours_per_week", "member_reference_id"),
            "required_columns" => array("member_name", "contact_id"),
            "note" => array("In CSV, 'contacts' mentioned against member records should already exist within HCM Admin")
        ),
        "2" => array(
            "id" => 3,
            "title" => "Member Roles",
            "columns" => array("member_id","member_role", "start_date", "start_time","pay_point", "level","employment_type"),
            "required_columns" => array("member_id", "member_role", "start_date", "start_time","pay_point", "level","employment_type"),
            "note" => array("In CSV, 'member role' mentioned against member records should already exist within HCM Admin")
        ),
        "3" => array(
            "id" => 4,
            "title" => "Member - Organisation",
            "columns" => array("member_id", "organisation_id","status"),
            "required_columns" => array("member_id", "organisation_id", "status"),
            "note" => array("In CSV, 'organisation' mentioned against member records should already exist within HCM Admin")
        ),
        "4" => array(
            "id" => 5,
            "title" => "Member - Participant",
            "columns" => array("member_id", "participant_id","status"),
            "required_columns" => array("member_id", "participant_id","status"),
            "note" => array("In CSV, 'participant' mentioned against member records should already exist within HCM Admin")
        ),
        "5" => array(
            "id" => 6,
            "title" => "Shifts",
            "columns" => array("shift_no",
            "owner",
            "account",
            "contact",
            "service_type",
            "status",
            "description",
            "contact_phone",
            "contact_email",
            "scheduled_start_date_time",
            "scheduled_end_date_time",
            "scheduled_travel_allowance",
            "scheduled_reimbursements",
            "scheduled_travel_distance_allowance",
            "scheduled_travel_duration_allowance",
            "actual_start_date_time",
            "actual_end_date_time",
            "actual_travel_allowance",
            "actual_reimbursements",
            "actual_travel_distance_allowance",
            "actual_travel_duration_allowance",
            "notes"),
            "required_columns" => array("shift_no", "account","contact","service_type","status","scheduled_start_date_time","scheduled_end_date_time","contact_phone","contact_email"),
            "note" => array("In CSV, shifts records should already exist within HCM Admin")
        ),
    );

    /**
     * contructor
     */
    function __construct() {
        parent::__construct();

        // load model
        $this->load->model('All_module_model');
        $this->load->library('form_validation');
        $this->load->library('UserName');
        $this->load->model('../../recruitment/models/Recruitment_applicant_model');
        $this->load->model('../../member/models/Member_model');
        $this->load->model('../../item/models/Document_model');
        $this->load->model('../../item/models/Participant_model');
        $this->load->model('../../sales/models/Account_model');
        $this->form_validation->CI = & $this;
        $this->load->model('../../schedule/models/Schedule_model');
        $this->load->model('../../sales/models/Opportunity_model');
        $this->load->model('../../sales/models/Contact_model');
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
     * fetches all the data items configured in this controller
     */
    public function fetch_data_items() {
        echo json_encode(['status' => true, 'data' => $this->data_items]);
        exit();
    }

    /**
     * returns the columns associated with a given data item id
     */
    private function get_data_item_header($id) {
        for($i=0;$i<count($this->data_items);$i++) {
            if($this->data_items[$i]['id'] == $id)
            return $this->data_items[$i]['columns'];
        }
    }

    /**
     * imports the validated applicants into db
     */
    private function import_applicants($data_to_import, $adminid) {

        // TODO: for safety, maybe wrap this long running operation in transaction

        $rows = 0;

        $importIntoPersonTables = false;

        foreach ($data_to_import as $datarow) {
            $rows++;
            $applicant_row = array(
                "firstname" => $datarow['firstname'],
                "lastname" => $datarow['lastname'],
                "status" => "0",
                "jobId" => "0",
                "channelId" => "0",
                "current_stage" => "0",
                "recruiter" => "0",
                "duplicatedId" => "0",
                "duplicated_status" => "0",
                "flagged_status" => "0",
                "created" => DATE_TIME,
                "updated" => DATE_TIME,
            );

            $applicantId = $this->basic_model->insert_records('recruitment_applicant', $applicant_row);

            if(!empty($datarow['emails'])){
                $mail_data = ['email' => $datarow['emails'][0]->email,'applicant_id'=>$applicantId,'created'=>DATE_TIME,'primary_email'=>1];
                $this->basic_model->insert_records('recruitment_applicant_email', $mail_data);
            }

            if(!empty($datarow['phones'])){
                $phone_data = ['phone' => $datarow['phones'][0]->phone,'applicant_id'=>$applicantId,'created'=>DATE_TIME,'primary_phone'=>1];
                $this->basic_model->insert_records('recruitment_applicant_phone', $phone_data);
            }

            if(!empty($datarow['addresses'])){
                $val = $datarow['addresses'][0];
                $address_data = ['street' => $val->street, 'city' => $val->city, 'postal' => $val->postal, 'state' => $val->state, 'primary_address' => 1, 'applicant_id' => $applicantId];

                $this->load->model("recruitment/Recruitment_applicant_model");
                $this->Recruitment_applicant_model->create_applicant_address($address_data);
            }

            if(!empty($datarow['flagged'])){
                $val = $datarow['flagged'][0];
                if($val->flagged_reason_title) {
                    $reason_id = $this->Recruitment_applicant_model->get_insert_flag_reason($val->flagged_reason_title);
                    $flag_obj = new stdClass();
                    $flag_obj->applicant_id = $applicantId;
                    $flag_obj->reason_id = $reason_id;
                    $flag_obj->reason_title = $val->flagged_reason_title;
                    $flag_obj->reason_note = $val->flagged_reason_notes;
                    $this->Recruitment_applicant_model->flag_applicant($flag_obj, $adminid);
                }
            }

            if ($importIntoPersonTables) {
                $TYPE_APPLICANT = 1;
                $this->db->insert('tbl_person', [
                    'firstname' => $applicant_row['firstname'],
                    'lastname' => $applicant_row['lastname'],
                    'type' => $TYPE_APPLICANT,
                    'status' => $applicant_row['status'],
                ]);

                $person_id = $this->db->insert_id();

                if (isset($mail_data)) {
                    $this->db->insert('tbl_person_email', [
                        'email' => $mail_data['email'],
                        'primary_email' => $mail_data['primary_email'],
                        'person_id' => $person_id,
                    ]);
                }

                if (isset($phone_data)) {
                    $this->db->insert('tbl_person_phone', [
                        'email' => $phone_data['phone'],
                        'primary_phone' => $phone_data['primary_phone'],
                        'person_id' => $person_id,
                    ]);
                }

                $this->db->update('tbl_recruitment_applicant', ['person_id' => $person_id], [
                    'id' => $applicantId,
                ]);
            }

        }
        return $rows;
    }

    /**
     * main function that imports the validated rows to respective db tables of data item
     */
    private function import_validated_data($id, $data_to_import, $adminid) {
        $total_inserted = 0;
        if($id == 1)
            $total_inserted = $this->import_applicants($data_to_import, $adminid);
        return $total_inserted;
    }

    /**
     * returns the list of validation rules associated with a given data item id
     */
    private function fetch_data_item_validation_rules($id) {
        $val_rules = null;

        # applicants
        if($id == 1) {
            $val_rules = array(
                array('field' => 'firstname', 'label' => 'firstname', 'rules' => 'required|max_length[30]'),
                array('field' => 'lastname', 'label' => 'lastname', 'rules' => 'required|max_length[30]'),
                array('field' => 'phones[]', 'label' => 'phone', 'rules' => 'callback_phone_number_check[phone,required,Please enter valid mobile number.]'),
                array('field' => 'emails[]', 'label' => 'email', 'rules' => 'callback_check_valid_email_address[email]|callback_check_email_already_exist_to_another_applicant[]'),
                array('field' => 'addresses[]', 'label' => 'addresses', 'rules' => 'callback_check_recruitment_applicant_address_ifadded|callback_check_suburb_state_exists'),
                array('field' => 'flagged[]', 'label' => 'flag', 'rules' => 'callback_check_flag_and_flagreason'),
            );
        }
        if($id == 2) {
            $val_rules = array(
                array('field' => 'member_name', 'label' => 'member_name', 'rules' => 'required|max_length[30]'),
                array('field' => 'contact_name', 'label' => 'contact_name', 'rules' => 'required|max_length[30]'),
            );
        }
        return $val_rules;
    }

    //Check if user upload proper csv or not and remove empty rows incase if it's found
    public function check_csv_file_vaidation($files, $reqData) {

        $_FILES = $files;
        # if data item is not selected
        if(!isset($reqData->dataitem) || empty($reqData->dataitem)) {
            echo json_encode(['status' => false, 'error' => 'Please select the data item.']);
            exit();
        }
        # if file is not submitted
        if (!isset($_FILES) || !isset($_FILES['selected_file']) || !isset($_FILES['selected_file']['name']) || empty($_FILES['selected_file']['name'])) {
            echo json_encode(['status' => false, 'error' => 'Please select a csv file to upload.']);
            exit();
        }
        # if file submission has error
        else if (!empty($_FILES) && $_FILES['selected_file']['error'] != 0) {
            echo json_encode(['status' => false, 'error' => 'Unsuccessful file import. Please try importing the file again.']);
            exit();
        }

        # if submitted file is one of the allowed ones
        $this->load->library('csv_reader');
        $mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (!in_array($_FILES['selected_file']['type'], $mimes)) {
            echo json_encode(['status' => false, 'error' => 'Invalid file extension, Please upload csv file only']);
            exit();
        }

        # is there data in the submitted file?
        $tmpName = $_FILES['selected_file']['tmp_name'];
        $filedata=$this->csv_reader->read_csv_data($tmpName);
        if (empty($filedata)) {
            echo json_encode(['status' => false, 'error' => 'Unsuccessful file import. File contains no data']);
            exit();
        }

        return $filedata;
    }
    /**
     * main controller function that handles all the importing stuff
     * based on the data item selected like applicants, participants etc, it populates
     * the relevant db tables
     */
    public function import_data() {
        $reqData = request_handlerFile('access_admin', 1, 1);
        $filedata = $this->check_csv_file_vaidation($_FILES, $reqData);

        # finding the first row (header) in the submitted file
        $header = array_shift($filedata);
        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);
        $col_header = $header;
        $data_to_import = [];
        sort($col_header);

        # for each data item, there is a specific header to be used
        $csv_column_arr = $this->get_data_item_header($reqData->dataitem);
        $arColMatch = array_diff($csv_column_arr, $col_header);

        if (!empty($arColMatch)) {
            echo json_encode(['status' => false, 'error' => 'Invalid column names in uploaded csv file. It should be '.implode(", ",$csv_column_arr).' in that sequence']);
            exit();
        }

        $cnt = 0;
        $unique_emails = [];
        $duplicate_emails = [];
        switch ($reqData->dataitem) {
            case 1:

                foreach ($filedata as $row) {
                    $cnt++;
                    if (count($row) == count($header)) {
                        $row = array_map("utf8_encode", $row);
                        $valrow = null;
                        $address_obj = new stdClass();
                        $email_obj = new stdClass();
                        $phone_obj = new stdClass();
                        $flag_obj = new stdClass();
                        foreach($csv_column_arr as $headerindex => $colname) {
                            $tmparr = null;

                            # if the data is email then generating a separate sub array within it
                            if($colname == "emails") {
                                $email_obj->email = $row[$headerindex];
                                $valrow[$colname][] = $email_obj;

                                # if email already found in the current list of data items
                                if(isset($unique_emails[$row[$headerindex]]))
                                    $duplicate_emails[] = $row[$headerindex];
                                else
                                    $unique_emails[$row[$headerindex]] = 1;
                            }
                            # if the data is phone then generating a senparate sub array within it
                            else if($colname == "phones") {
                                $phone_obj->phone = $row[$headerindex];
                                $valrow[$colname][] = $phone_obj;
                            }
                            # if the data is address fields then generating a separate sub array within it
                            else if($colname == "street" || $colname == "suburb" || $colname == "postal" || $colname == "state") {
                                if($colname == "suburb")
                                $colname = "city";
                                $address_obj->$colname = $row[$headerindex];
                                if($colname == "state")
                                $valrow['addresses'][] = $address_obj;
                            }
                            # if flagged and flaggedreason is provided
                            else if($colname == "flagged_reason_title" || $colname == "flagged_reason_notes") {
                                $flag_obj->$colname = $row[$headerindex];
                                if($colname == "flagged_reason_notes")
                                $valrow['flagged'][] = $flag_obj;
                            }
                            else
                                $valrow[$colname] = $row[$headerindex];
                        }
                        $data_to_import[] = $valrow;
                    } else {
                        echo json_encode(["status" => false, "error" => "Unsuccessful file import. Row-{$cnt} contains non-required columns"]);
                        exit();
                    }
                }

                $validation_rules = $this->fetch_data_item_validation_rules($reqData->dataitem);

                $allerrors = null;
                if($data_to_import) {
                    # did we have any duplicate emails within the data set?
                    if(!empty($duplicate_emails)) {
                        $errstring = "Duplicate email(s) found\r\n".implode(", ",$duplicate_emails);
                        $allerrors[] = $errstring;
                    }

                    $row = 1;
                    foreach ($data_to_import as $datarow) {
                        $row++;
                        $this->form_validation->CI = & $this;
                        $this->form_validation->set_rules($validation_rules);
                        $this->form_validation->set_data((array) $datarow);
                        if (!$this->form_validation->run()) {
                            $errors = $this->form_validation->error_array();
                            $errstring = null;
                            if(!empty($errors)) {
                                $errstring = implode("\r\n", $errors);
                                $errstring = "Following error(s) found for row: {$row}\r\n".$errstring;
                                $allerrors[] = $errstring;
                            }
                        }
                        $this->form_validation->reset_validation();
                    }
                }

                # if there are validation errors in each row processing
                if($allerrors) {
                    echo json_encode(["status" => false, "error" => implode($allerrors,"\r\n\r\n")]);
                    exit();
                }

                # finally importing validated data
                if($data_to_import) {
                    $total_inserted = $this->import_validated_data($reqData->dataitem, $data_to_import, $reqData->adminId);
                    echo json_encode(["status" => true, "msg" => "Successfully imported {$total_inserted} applicants"]);
                    exit();
                }
                break;
            case 2:
                $this->process_member_data($filedata, $header, $csv_column_arr, $reqData->adminId);
                break;

            case 3:
                $this->process_member_role_data($filedata, $header, $csv_column_arr, $reqData->adminId);
            break;
            case 4:
                $this->process_member_organisation_data($filedata, $header, $csv_column_arr, $reqData->adminId);
            break;
            case 5:
                $this->process_member_participant_data($filedata, $header, $csv_column_arr, $reqData->adminId);
            break;
            case 6:
                $this->process_shift_data($filedata, $header, $csv_column_arr, $reqData->adminId);
            break;
            default:
                break;
        }

    }
   
    public function process_shift_data($filedata, $header, $csv_column_arr, $adminId) {
    $row = $total_error_rows = $total_suc_rows = 0;
    $row_errors = [];
    $data = $this -> empty_sheet_check($filedata, $csv_column_arr);
    for ($x = 0; $x < count($data['data']); $x++)

    {
        $row++;
        $iteratedData = $data['data'][$x];
        $isShiftexist = $this -> find_shift_id_already_exist($iteratedData['shift_no']);
       
        if(!empty($iteratedData['shift_no']))
        {
            if ($isShiftexist)
            {
                $errstring = "Shift No already exist";
                $row_errors[$x][] = $errstring;
    
            }
           else if ($iteratedData['shift_no'] < 1)
            {
                $errstring = "Shift No is not valid";
                $row_errors[$x][] = $errstring;
    
            }
        }
        
       

       if(!empty($iteratedData['account']))
       {
           if (count($this -> Schedule_model -> account_participant_name_search($iteratedData['account'])) < 1)

            {
                $errstring = "Account does not exist";
                $row_errors[$x][] = $errstring;
    
            } else
    
            {
    
                for ($j = 0; $j < count($this -> Schedule_model -> account_participant_name_search($iteratedData['account'])); $j++)
    
                {
    
                    if (trim($this -> Schedule_model -> account_participant_name_search($iteratedData['account'])[$j] -> {
                            'label'
                        }) === trim($iteratedData['account']))
    
                    {
    
                        $iteratedData['account_type'] = $this -> Schedule_model -> account_participant_name_search($iteratedData['account'])[$j] -> {
                            'account_type'
                        };
                        $iteratedData['account_id'] = $this -> Schedule_model -> account_participant_name_search($iteratedData['account'])[$j] -> {
                            'value'
                        };
                        break;
    
                    } 
                }
              
            
            if(!isset($iteratedData['account_type']))
            {
    
                $errstring = "Account does not exist";
                $row_errors[$x][] = $errstring;
    
            }
        }
       }

       if(empty($iteratedData['account']))
       {
        $errstring = "Account Field is required";
        $row_errors[$x][] = $errstring;
       }   
        if(!empty($iteratedData['owner']))
        {
            for ($z = 0; $z < count($this -> Opportunity_model -> get_owner_staff_by_name($iteratedData['owner'])); $z++)

            {

                if (trim($this -> Opportunity_model -> get_owner_staff_by_name($iteratedData['owner'])[$z][
                        'label'
                    ])=== trim($iteratedData['owner']))

                {

                    $iteratedData['owner_id'] = $this -> Opportunity_model -> get_owner_staff_by_name($iteratedData['owner'])[$z]['value'];
                    break;

                } 
            }

            if(!isset( $iteratedData['owner_id']))
            {

                $errstring = "Owner does not exist";
                $row_errors[$x][] = $errstring;
                
            }
        }
        
            
        
            

            $iteratedData['contact'] = preg_replace('/\s+/', ' ', $iteratedData['contact']);
            $iteratedData['firstname'] = explode(" ", $iteratedData['contact'])[0];

            if (count(explode(" ", $iteratedData['contact'])) > 1)
            {

                $iteratedData['lastname'] = explode(" ", $iteratedData['contact'])[1];
            }
        if(isset($iteratedData['account_type']))
        {
            $contact_details= new StdClass();
            $account_details= new StdClass();
            $contact_details->query=trim($iteratedData['contact']);
            $account_details->account_type=$iteratedData['account_type'];
            $account_details->label=$iteratedData['account'];
            $account_details->value=$iteratedData['account_id'];
            $contact_details->account= $account_details;

         
          
            for ($l = 0; $l < count($this -> Contact_model -> get_contact_for_account($contact_details,true,true)); $l++)
            {
                              

               if(isset($this -> Contact_model -> get_contact_for_account($contact_details,true,true)[$l]['label'])
                  &&isset($this -> Contact_model -> get_contact_for_account($contact_details,true,true)[$l]['value'])){
                if (trim($this -> Contact_model -> get_contact_for_account($contact_details,true,true)[$l]['label']) 
                == trim($iteratedData['contact']))

             {
         
                 $iteratedData['contact_id'] = $this-> Contact_model-> get_contact_for_account($contact_details,true,true)[$l]['value'] ;
                 break;

             } 
                }
               
            }
            
            if(!isset($iteratedData['contact_id'])&&$iteratedData['account_type']==2)
            {
                $account_details= new StdClass();
                $account_details->account_type=$iteratedData['account_type'];
                $account_details->label=$iteratedData['account'];
                $account_details->value=$iteratedData['account_id'];
                $new_contact= array("firstname"=>$iteratedData['firstname'],"lastname"=>$iteratedData['lastname'],"status"=>1,"account_person"=>$account_details);
                $contact_result=$this->Contact_model->create_update_contact($new_contact, $adminId);
                $iteratedData['contact_id']= $contact_result;
            }
            
        }
         if(isset($iteratedData['account_type'])&&(!isset($iteratedData['contact_id'])))
        {
            $errstring = "Contact is not associated with the participant";
            $row_errors[$x][] = $errstring;
        }

        if(!empty(trim($iteratedData['service_type'])))
        {
            for ($m = 0; $m < count($this -> Document_model -> get_role_name_search($iteratedData['service_type'])); $m++)
            {

              
                if (trim($this -> Document_model -> get_role_name_search($iteratedData['service_type'])[$m] -> {
                        'label'
                    }) === trim($iteratedData['service_type']))

                {

                    $iteratedData['role_id'] = $this -> Document_model -> get_role_name_search($iteratedData['service_type'])[$m] -> {
                        'value'
                    };
                    break;

                } 
            }
            if(!isset( $iteratedData['role_id']))
            {

                $errstring = "Service Type  does not exist";
                $row_errors[$x][] = $errstring;
                
            }
        }
        else
        {
            $errstring = "Service Type Field is required";
            $row_errors[$x][] = $errstring;
        }
        if (!empty($iteratedData['status']))

        {

            $currentStatus = $iteratedData['status'];
            $status_list = $this-> Schedule_model-> get_shift_statuses();
            $indexMatched = 0;

            for ($y = 0; $y < count($status_list['data']); $y++)

            {

                if (strtolower($status_list['data'][$y]['label']) === strtolower($currentStatus))

                {

                    $indexMatched = $status_list['data'][$y]['value'];

                }
            }
            if ($indexMatched < 1)

            {

                $iteratedData['status'] = 0;
                $errstring = "Status does not exist";
                $row_errors[$x][] = $errstring;

            } else

            {

                $iteratedData['status'] = $indexMatched;

            }
        }
        $iteratedData['scheduled_reimbursement'] = $iteratedData['scheduled_reimbursements'];
        $iteratedData['scheduled_travel'] = $iteratedData['scheduled_travel_allowance'];
        $iteratedData['actual_reimbursement'] = $iteratedData['actual_reimbursements'];


        if (!empty($iteratedData['scheduled_start_date_time']))
        {

            $iteratedData['scheduled_start_date_time'] = preg_replace('/\s+/', ' ', $iteratedData['scheduled_start_date_time']);
            $iteratedData['scheduled_start_date'] = explode(" ", $iteratedData['scheduled_start_date_time'])[0];
            if (count(explode(" ", $iteratedData['scheduled_start_date_time'])) > 1)

            {

                $iteratedData['scheduled_start_time'] = explode(" ", $iteratedData['scheduled_start_date_time'])[1];
               
            } else
            {
                $iteratedData['scheduled_start_time'] = '';

            }
        } else

        {

            $iteratedData['scheduled_start_date'] = "";
            $iteratedData['scheduled_start_time'] = "";

        }

        if (!empty($iteratedData['scheduled_end_date_time']))

        {

            $iteratedData['scheduled_end_date_time'] = preg_replace('/\s+/', ' ', $iteratedData['scheduled_end_date_time']);
            $iteratedData['scheduled_end_date'] = explode(" ", $iteratedData['scheduled_end_date_time'])[0];


            if (count(explode(" ", $iteratedData['scheduled_end_date_time'])) > 1)

            {

                $iteratedData['scheduled_end_time'] = explode(" ", $iteratedData['scheduled_end_date_time'])[1];
            } 
            else
            {

                $iteratedData['scheduled_end_time'] = "";

            }
        } else

        {

            $iteratedData['scheduled_end_date'] = "";
            $iteratedData['scheduled_end_time'] = "";

        }
        if (!empty($iteratedData['actual_start_date_time']))

        {

            $iteratedData['actual_start_date_time'] = preg_replace('/\s+/', ' ', $iteratedData['actual_start_date_time']);
            $iteratedData['actual_start_date'] = explode(" ", $iteratedData['actual_start_date_time'])[0];

            if (count(explode(" ", $iteratedData['actual_start_date_time'])) > 1)

            {

                $iteratedData['actual_start_time'] = explode(" ", $iteratedData['actual_start_date_time'])[1];


                if (strtotime($iteratedData['actual_start_date_time']) !== false)

                {


                    if ((date('Y', strtotime($iteratedData['actual_start_date'])) < date('Y')) || (date('Y', strtotime($iteratedData['actual_start_date'])) > date('Y') + 2))

                    {

                        $errstring = "Actual_start_date year is wrong";
                        $row_errors[$x][] = $errstring;

                    }
                }
            } else

            {

                $iteratedData['actual_start_time'] = "";

            }
        } else

        {

            $iteratedData['actual_start_date'] = "";
            $iteratedData['actual_start_time'] = "";

        }
        if (!empty($iteratedData['actual_end_date_time']))

        {

            $iteratedData['actual_end_date_time'] = preg_replace('/\s+/', ' ', $iteratedData['actual_end_date_time']);
            $iteratedData['actual_end_date'] = explode(" ", $iteratedData['actual_end_date_time'])[0];


            if (count(explode(" ", $iteratedData['actual_end_date_time'])) > 1)

            {

                $iteratedData['actual_end_time'] = explode(" ", $iteratedData['actual_end_date_time'])[1];

                if (strtotime($iteratedData['actual_end_date_time']) !== false)

                {

                    if ((date('Y', strtotime($iteratedData['actual_end_date'])) < date('Y')) || (date('Y', strtotime($iteratedData['actual_end_date'])) > date('Y') + 2))

                    {

                        $errstring = "Actual_end_date year is wrong";
                        $row_errors[$x][] = $errstring;

                    }
                }
            } else

            {

                $iteratedData['actual_end_time'] = "";

            }
        } else

        {
            $iteratedData['actual_end_date'] = "";
            $iteratedData['actual_end_time'] = "";

        }
        $iteratedData['actual_travel'] = $iteratedData['actual_travel_allowance'];
        $iteratedData['scheduled_travel_duration'] = $iteratedData['scheduled_travel_duration_allowance'];
        $iteratedData['scheduled_travel_distance'] = $iteratedData['scheduled_travel_distance_allowance'];
        $iteratedData['actual_travel_distance'] = $iteratedData['actual_travel_distance_allowance'];
        $iteratedData['actual_travel_duration'] = $iteratedData['actual_travel_duration_allowance'];

        if ($iteratedData['contact_email'])

        {

            if (!filter_var($iteratedData['contact_email'], FILTER_VALIDATE_EMAIL))

            {

                $errstring = "Contact Email format is invalid";
                $row_errors[$x][] = $errstring;

            }

        }

        if ($iteratedData['contact_phone'])

        {

            if (!preg_match('/^[0-9]{10}+$/', $iteratedData['contact_phone']))

            {

                $errstring = "Contact Phone format is invalid";
                $row_errors[$x][] = $errstring;

            }
        }

        $errors = [];
        if (!empty($row_errors[$x]))

        {

            $errors = $row_errors[$x];

        }
        $res = $this-> Schedule_model-> create_update_shift($iteratedData, $adminId, false, true, $errors);
        if (!$res['status'])

        {
            $total_error_rows++;
            $errstring = $res['error'];
            $errstring = $errstring;
            $row_errors[$x][] = $errstring;

        } else

        {

            $total_suc_rows++;

        }
        if (!empty($row_errors[$x]))

        {

            $row_errors[$x] = "Row: $row ".implode(', ', $row_errors[$x]);

        }
    }

    $this -> create_error_log($total_suc_rows, $total_error_rows, $row_errors, $adminId);
    return $res;
}

    public function process_member_data($filedata, $header, $csv_column_arr, $adminId) {

        $data = $this->empty_sheet_check($filedata, $csv_column_arr);

        if(!$data['status']) {
            echo json_encode($data);
            exit();
        }
        else {
            $data_to_import = $data['data'];
        }
        $row = $total_error_rows = $total_suc_rows = 0;
        $postdata = [];
        $row_errors = [];
        $member_details = [];
        foreach($data_to_import as $index => $datarow) {
            $row++;

            if(empty($datarow['member_name'])) {
                $row_errors[$index][] = "Member not found: {$datarow['member_name']}";
            }
            else{
                //Get member details
                $member_details =
                $this->basic_model->get_record_where('member', ["id", "fullname"], ['fullname' =>
                $datarow['member_name'], 'archive' => 0]);

            }

            if(!empty($datarow['contact_id']))
             {

                $contact_details = $this->basic_model->get_row('person', ["id", "firstname", "lastname"],
                                    ['id' => $datarow['contact_id'], 'archive' => 0 ]);

                if(!$contact_details) {
                    $row_errors[$index][] = "Contact ID not found: {$datarow['contact_id']}";
                } else{
                    $datarow['person_id'] = $contact_details->id;
                }

            } else {
                $row_errors[$index][] = "Contact ID not found: {$datarow['contact_id']}";
            }

            if(!empty($datarow['member_name']) && !$member_details && !empty($contact_details)) {

                $obj = new stdClass;
                $obj->value = $contact_details->id;

                $postdata = [
                    "account_person" => $obj,
                    'fullname' => $datarow['member_name'],
                    'firstname' => $contact_details->firstname,
                    'lastname' => $contact_details->lastname,
                    'hours_per_week' => $datarow['hours_per_week'] ?? NULL,
                    'status' => 1,
                    'department' => 2,
                    'keypay_emp_id' => $datarow['member_reference_id'] ?? NULL,
                ];

                $member_id = $this->Member_model->create_member($postdata, $adminId);

                if(!$member_id) {

                    $errstring = "Row: {$row} - Error on creating member" . $datarow['member_name'];
                    $row_errors[$index][] = $errstring;

                }
                else {
                    $total_suc_rows++;
                }
            }
            //Create new entry if member and contact not found
            else if(!empty($member_details) && !empty($contact_details) && empty($row_errors[$index])) {

                foreach($member_details as $member) {

                    $member_contact =
                        $this->basic_model->get_row('member', ['id','person_id'],
                        ['id' => $member->id, "person_id" => $datarow['person_id'], 'archive' => 0]);

                     if(!empty($member_contact)) {
                        $member_id = $member_contact->id;
                        $person_id = $member_contact->person_id;

                     } else {
                        $member_id = '';
                        $person_id = $datarow['person_id'];

                     }
                     $obj = new stdClass;
                     $obj->value = $person_id;

                     $postdata = [
                        'id' => $member_id,
                        "account_person" => $obj,
                        'fullname' => $datarow['member_name'],
                        'firstname' => $contact_details->firstname,
                        'lastname' => $contact_details->lastname,
                        'hours_per_week' => $datarow['hours_per_week'] ?? NULL,
                        'status' => 1,
                        'department' => 2,
                        'keypay_emp_id' => $datarow['member_reference_id'] ?? NULL,
                    ];

                    $member_id = $this->Member_model->create_member($postdata, $adminId);

                    if(!$member_id) {

                        $errstring = "Row: {$row} - Error on creating member" . $datarow['member_name'];
                        $row_errors[$index][] = $errstring;

                    }
                    else {
                        $total_suc_rows++;
                    }

                }

            } else {
                $total_error_rows++;
            }

            if(!empty($row_errors[$index])) {
                $row_errors[$index] = "Row: $row " . implode(', ' , $row_errors[$index]);
            }
        }

        $this->create_error_log($total_suc_rows, $total_error_rows, $row_errors, $adminId);
    }

    public function process_member_role_data($filedata, $header, $csv_column_arr, $adminId) {

        $data = $this->empty_sheet_check($filedata, $csv_column_arr);

        if(!$data['status']) {
            echo json_encode($data);
            exit();
        }
        else {
            $data_to_import = $data['data'];
        }
        $row = $total_error_rows = $total_suc_rows = 0;
        $postdata = [];
        $row_errors = [];

        foreach($data_to_import as $index => $datarow) {
            $row++;

            //Get member details
            $member_details =
            $this->basic_model->get_record_where('member', ["id", "fullname"], ['id' =>
             $datarow['member_id'], 'archive' => 0]);

            if(empty($member_details)) {
                $row_errors[$index][] = "Member ID not found: {$datarow['member_id']}";
            }

            $role_details =
            $this->basic_model->get_row('member_role', ["id", "name"], ['name' =>
             $datarow['member_role'], 'archive' => 0]);

            if(empty($role_details)) {
                $row_errors[$index][] = "Role not found: {$datarow['member_role']}";
            }
            else {
                $datarow['role_id'] = $role_details->id;
            }

            if($datarow['start_date']) {
                $dt = DateTime::createFromFormat('d/m/Y', $datarow['start_date']);
                if($dt) {
                    $datarow['start_date'] = $dt->format('Y-m-d');
                }
            } else {
                $row_errors[$index][] = "Start date not found: {$datarow['start_date']}";
            }

            if($datarow['start_time']) {
                $dt = DateTime::createFromFormat('H:i a', $datarow['start_time']);

                if($dt && $dt != '00:00:00') {
                    $datarow['start_time'] = $dt->format('H:i:s');
                }
            } else{
                $row_errors[$index][] = "Start time not found: {$datarow['start_date']}";
            }

             $pay_point = $this->find_reference_id("skills", $datarow['pay_point']);

            if(empty($pay_point)) {
                $row_errors[$index][] = "Pay Point not found: {$datarow['pay_point']}";
            } else {
                $datarow['pay_point'] = $pay_point;
            }

            $level = $this->find_reference_id("pay_levels", $datarow['level']);

            if(empty($level)) {
                $row_errors[$index][] = "Level not found: {$datarow['level']}";
            } else {
                $datarow['level'] = $level;
            }

            $datarow['employment_type'] = $this->find_reference_id("employment_type", $datarow['employment_type']);
            if(empty($datarow['employment_type'])) {
                $row_errors[$index][] = "Employment type not found: {$datarow['employment_type']}";
            }


            //Create new entry if member and role not found
            if(!empty($member_details) && !empty($role_details) && empty($row_errors[$index])) {

                foreach($member_details as $member) {

                    $member_roles =
                        $this->basic_model->get_row('member_role_mapping', ['id','member_id', 'member_role_id'],
                        ['member_id' => $member->id, "member_role_id" => $datarow['role_id'], 'archive' => 0]);

                     if(!empty($member_roles)) {
                        $member_id = $member_roles->member_id;
                        $mapping_id = $member_roles->id;
                        $member_role_id = $member_roles->member_role_id;
                     } else {
                        $member_id = $member->id;
                        $mapping_id = '';
                        $member_role_id =  $datarow['role_id'];
                     }

                     $postdata = [
                     'member_id' => $member_id,
                     'role_id' => $member_role_id,
                     'member_role_id' => $mapping_id,
                     'start_date' => $datarow['start_date'],
                     'start_time' => $datarow['start_time'],
                     'pay_point' => $datarow['pay_point'],
                     'level' => $datarow['level'],
                     'employment_type' => $datarow['employment_type'],
                     'adminId' => $adminId
                    ];

                    $result = $this->Member_model->create_update_member_role(NULL, $postdata);

                    if(!$result['status']) {
                        $errstring = $result['error'];
                        $errstring = "Row: {$row} - ".$errstring;
                        $row_errors[$index][] = $errstring;

                    }
                    else {
                        $total_suc_rows++;
                    }

                }

            } else {
                $total_error_rows++;
            }

            if(!empty($row_errors[$index])) {
                $row_errors[$index] = "Row: $row " . implode(', ' , $row_errors[$index]);
            }
        }

        $this->create_error_log($total_suc_rows, $total_error_rows, $row_errors, $adminId);
    }

    public function process_member_organisation_data($filedata, $header, $csv_column_arr, $adminId) {

        $data = $this->empty_sheet_check($filedata, $csv_column_arr);

        if(!$data['status']) {
            echo json_encode($data);
            exit();
        }
        else {
            $data_to_import = $data['data'];
        }
        $row = $total_error_rows = $total_suc_rows = 0;
        $postdata = [];
        $row_errors = [];

        foreach($data_to_import as $index => $datarow) {
            $row++;

            if(empty($datarow['member_id'])) {
                $row_errors[$index][] = "Member ID not found: {$datarow['member_id']}";
            }
            else{
                //Get member details
                $member_details =
                $this->basic_model->get_record_where('member', ["id", "fullname"], ['id' =>
                $datarow['member_id'], 'archive' => 0]);
                if(!$member_details) {
                    $row_errors[$index][] = "Member ID not found: {$datarow['member_id']}";
                }
            }

            if(!empty($datarow['organisation_id'])) {

                $org_details = $this->basic_model->get_row('organisation', ["id", "name"],
                ['id' => $datarow['organisation_id'], 'archive' => 0]);

                if(!$org_details) {
                    $row_errors[$index][] = "Organisation ID not found: {$datarow['organisation_id']}";
                } else{
                    $datarow['org_id'] = $org_details->id;
                }

            } else {
                $row_errors[$index][] = "Organisation ID not found: {$datarow['organisation_id']}";
            }

            if($datarow['status']) {

                $status_id = $this->find_reference_id("participant_member_status", $datarow['status']);

                if(empty($status_id)) {
                    $row_errors[$index][] = "Status not found: {$datarow['status']}";
                } else {
                    $datarow['status'] = $status_id;
                }

            }

            if(!empty($member_details) && !empty($org_details) && empty($row_errors[$index])) {

                foreach($member_details as $member) {

                    $member_org =
                        $this->basic_model->get_row('organisation_member', ['id','member_id', 'organisation_id'],
                        ['member_id' => $member->id, 'organisation_id' => $datarow['org_id'], 'archive' => 0]);

                     if(!empty($member_org)) {
                        $id = $member_org->id;
                        $member_id = $member_org->member_id;
                        $org_id = $member_org->organisation_id;

                     } else {
                        $id = '';
                        $member_id = $member->id;
                        $org_id = $datarow['org_id'];

                     }

                     $postdata = [
                        'id' => $id,
                        'member_id' => $member_id,
                        'org_id' => $org_id,
                        'status' => $status_id,
                    ];

                    $result = $this->Account_model->create_update_org_member($postdata, $adminId);

                    if(!$result['status']) {

                        $total_error_rows++;
                        $errstring = $result['error'];
                        $errstring = "Row: {$row} - ".$errstring;
                        $row_errors[$index][] = $errstring;

                    }
                    else {
                        $total_suc_rows++;
                    }

                }

            } else {
                $total_error_rows++;
            }

            if(!empty($row_errors[$index])) {
                $row_errors[$index] = "Row: $row " . implode(', ' , $row_errors[$index]);
            }
        }

        $this->create_error_log($total_suc_rows, $total_error_rows, $row_errors, $adminId);

    }

    public function process_member_participant_data($filedata, $header, $csv_column_arr, $adminId) {

        $data = $this->empty_sheet_check($filedata, $csv_column_arr);

        if(!$data['status']) {
            echo json_encode($data);
            exit();
        }
        else {
            $data_to_import = $data['data'];
        }
        $row = $total_error_rows = $total_suc_rows = 0;
        $postdata = [];
        $row_errors = [];

        foreach($data_to_import as $index => $datarow) {
            $row++;

            if(empty($datarow['member_id'])) {
                $row_errors[$index][] = "Member ID not found: {$datarow['member_id']}";
            }
            else{
                //Get member details
                $member_details =
                $this->basic_model->get_record_where('member', ["id", "fullname"], ['id' =>
                $datarow['member_id'], 'archive' => 0]);

                if(!$member_details) {
                    $row_errors[$index][] = "Member ID not found: {$datarow['member_id']}";
                }

            }

            if(!empty($datarow['participant_id'])) {

                $participant_details = $this->basic_model->get_row('participants_master', ["id", "contact_id", "name"],
                ['id' => $datarow['participant_id'], 'archive' => 0]);

                if(!$participant_details) {
                    $row_errors[$index][] = "Participant ID not found: {$datarow['participant_id']}";
                } else{
                    $datarow['participant_id'] = $participant_details->id;
                }

            } else {
                $row_errors[$index][] = "Participant ID not found: {$datarow['participant_id']}";
            }

            $status_id = $this->find_reference_id("participant_member_status", $datarow['status']);

            if(empty($status_id)) {
                $row_errors[$index][] = "Status not found: {$datarow['status']}";
            } else {
                $datarow['status'] = $status_id;
            }

            if(!empty($member_details) && !empty($participant_details) && empty($row_errors[$index])) {

                foreach($member_details as $mem_index => $member) {

                    $member_participant =
                        $this->basic_model->get_row('participant_member', ['member_id','participant_id'],
                        ['member_id' => $member->id, "participant_id" => $datarow['participant_id'], 'archive' => 0]);

                     if(!empty($member_participant)) {
                        $member_id = $member_participant->member_id;
                        $participant_id = $member_participant->participant_id;

                     } else {
                        $member_id = $member->id;
                        $participant_id = $datarow['participant_id'];

                     }
                     $obj = [];
                     $obj = ['label' => $member->fullname, 'value' => $member_id];

                     $postdata = [
                         'participant_id' => $participant_id,
                         'participant_members' => [ $mem_index => [
                            'status' => $status_id,
                            'member_obj' => $obj
                            ]
                        ]
                    ];

                    $result = $this->Participant_model->assign_participant_members($postdata, $adminId, TRUE);

                    if(!$result['status']) {

                        $total_error_rows++;
                        $errstring = $result['error'];
                        $errstring = "Row: {$row} - ".$errstring;
                        $row_errors[$index][] = $errstring;

                    }
                    else {
                        $total_suc_rows++;
                    }

                }

            } else {
                $total_error_rows++;
            }

            if(!empty($row_errors[$index])) {
                $row_errors[$index] = "Row: $row " . implode(', ' , $row_errors[$index]);
            }
        }

        $this->create_error_log($total_suc_rows, $total_error_rows, $row_errors, $adminId);

    }

    public function create_error_log($total_suc_rows, $total_error_rows, $allerrors, $adminId) {
        $postdata["total_rows"] = ($total_suc_rows + $total_error_rows);
        $postdata["total_success_rows"] = $total_suc_rows;
        $postdata["total_error_rows"] = $total_error_rows;
        $postdata["error_text"] = (!empty($allerrors)) ? implode("\r\n",$allerrors) : '';
        $postdata["created"] = DATE_TIME;
        $postdata["created_by"] = $adminId;
        $import_id = $this->basic_model->insert_records("admin_bulk_import", $postdata, $multiple = FALSE);

        # preparing stats to display to the user
        if(($total_suc_rows + $total_error_rows) > 1)
            $data_msg = ($total_suc_rows + $total_error_rows). " records were found.";
        else
        $data_msg = ($total_suc_rows + $total_error_rows). " record was found.";

        if($total_suc_rows > 1)
            $data_msg .= " {$total_suc_rows} records were imported.";
        else
        $data_msg .= " {$total_suc_rows} record was imported.";

        $error_msg = null;
        if($total_error_rows > 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} records had errors.";
        }
        else if($total_error_rows == 1) {
            $error_msg = true;
            $data_msg .= " {$total_error_rows} record had errors.";
        }

        echo json_encode(["status" => true, 'error_msg' => $error_msg, 'data_msg' => $data_msg,
            'import_id' => $import_id]);

    }

    public function empty_sheet_check($filedata, $csv_column_arr) {

        foreach ($filedata as $row) {
            $row = array_map("utf8_encode", $row);
            $valrow = NULL;
            foreach($csv_column_arr as $headerindex => $colname) {
                $valrow[$colname] = $row[$headerindex];
            }
            $data_to_import[] = $valrow;
        }

        if(empty($data_to_import)) {
            $return = ['status' => false, 'error' => 'Unsuccessful file import. File contains no data'];

        } else {
            $return = ['status' => true, 'data' => $data_to_import];
        }

        return $return;
    }

    public function find_reference_id($keyname, $display_name) {
        $this->db->select(["r.id"]);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $this->db->where("lower(r.display_name)", $display_name);
        $query = $this->db->get();
        if($query->num_rows() > 0) {
            $rows = $query->result_array();
            return $rows[0]['id'];
        }
        return 0;
    }


    public function find_shift_id_already_exist($shift_no){
        $this->db->select(["*"]);
        $this->db->from('tbl_shift as s');
        $this->db->where('s.shift_no',  $shift_no);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = null;
        if (!empty($query->result())) {
          return 1;
        }
        return 0;
    }

}
