<?php

class Schedule_model extends Basic_Model {

    var $schedule_status = [
        "1" => "Open",
        "2" => "Published",
        "3" => "Scheduled",
        "4" => "In progress",
        "5" => "Completed",
        "6" => "Cancelled",
    ];

    var $schedule_status_final = [
        "5" => "Completed",
        "6" => "Cancelled",
    ];

    var $schedule_status_grouped = [
        "1" => "Open",
        "2" => "Published",
        "3" => "Scheduled",
        "4" => "In progress",
        "7" => "Closed",
    ];

    var $schedule_status_portal = [
        "3" => "Scheduled",
        "4" => "Start",
        "5" => "Complete",
    ];

    var $presitence_day_rule = [
        "public_holiday" => 3,
        "sunday" => 2,
        "saturday" => 1,
        "weekday" => 0,
    ];

    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'shift';
		$this->load->model('common/Common_model');
		$this->load->model('common/Common_model');
        $this->load->model('schedule/Shift_member_model');
        $this->load->model('schedule/Shift_sms_log_model');
        $this->load->model('schedule/Schedule_ndis_model');
        $this->load->model('sales/Person_model');
        $this->load->model('item/Participant_model');        
        $this->load->model('sales/Account_model');
        $this->load->model('admin/Notification_model');
        $this->load->model('sales/Contact_model');

        $this->load->library('session');

        $this->object_fields['shift_no'] = 'Shift Number';
        $this->object_fields['status'] = 'Status';
        $this->object_fields['scheduled_start_datetime'] = 'Schedule Start Time';
        $this->object_fields['scheduled_end_datetime'] = 'Schedule End Time';
        $this->object_fields['Shift Member'] = [
                                            'field' => 'id',
                                            'foreign_key' => 'shift_id',
                                            'object_fields' => $this->Shift_member_model->getObjectFields()
                                        ];
        $this->object_fields['Participant'] = [
                                            'field' => 'account_id',
                                            'object_fields' => $this->Participant_model->getObjectFields()

                                        ];
        $this->object_fields['Contact'] = [
                                            'field' => 'person_id',
                                            'object_fields' => $this->Person_model->getObjectFields()

                                        ];
        $this->object_fields['Owner'] = [
                                            'field' => 'owner_id',
                                            'object_fields' => $this->Member_model->getObjectFields()
        ];

        $this->object_fields['Creator'] = [
                                            'field' => 'created_by',
                                            'object_fields' => $this->Member_model->getObjectFields()
                                        ];
        $this->object_fields['Account'] = [
                                            'field' => ['account_id', 'account_type'],
                                            'object_fields' => $this->Account_model->getObjectFields()
                                        ];
        $this->object_fields['status'] = [
                                            'label' => 'Status',
                                            'values' => $this->get_shift_statuses_as_array()
                                        ];
        $this->object_fields['url'] = [
                                            'label' => 'Url',
                                            'field' => 'id',
                                            'value' => function($id) {
                                                $url = '';
                                                if ($id > 0) {
                                                    $url = $this->config->item('member_webapp_url')."/"."shift/$id";
                                                }
                                                return $url;
                                            }
                                        ];
        $this->object_fields['start'] = [
                                        'label' => 'Shift Start Time',
                                        'field' => 'scheduled_start_datetime',
                                        'value' => function($scheduled_start_datetime) {
                                            $formatted = '';
                                            if (!empty($scheduled_start_datetime)) {
                                                $formatted = date('j/n/Y g:ia', strtotime($scheduled_start_datetime));
                                            }
                                            return $formatted;
                                        }
                                    ];
        $this->object_fields['account_address'] = [
                                        'label' => 'Shift Address',
                                        'field' => ['account_address', 'account_type'],
                                        'value' => function($address_id, $type) {
                                            $address = '';
                                            if (!empty($address_id)) {
                                                $result = [];
                                                if ($type == 1) {
                                                    $result = $this->get_row('person_address as pa', ['pa.street', 'pa.suburb', 's.name', 'pa.postcode'], ['pa.id' => $address_id, 'pa.archive' => '0'], [['left' => ['table' => 'state as s', 'on' => 'pa.state = s.id']]]);
                                                    $row = (array) $result;
                                                    $address = implode(' ', array_values($row));
                                                }
                                                if ($type == 2) {
                                                    $result = $this->get_row('organisation_address as oa', ['oa.street', 'oa.city', 's.name', 'oa.postal'], ['oa.id' => $address_id, 'oa.archive' => '0'], [['left' => ['table' => 'state as s', 'on' => 'oa.state = s.id']]]);
                                                    $row = (array) $result;
                                                    $address = implode(' ', array_values($row));
                                                }
                                            }
                                            return $address;
                                        }
                                    ];
        $this->setScheduleRecipients();
        $this->warning = (object) ['is_warnable' => false, 'messages' => []];
    }

    /**
     * fetches all the shift statuses
     */
    public function get_shift_statuses_as_array() {
        $data = null;
        foreach($this->schedule_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return $data;
    }

    /**
     * fetches all the shift statuses
     */
    public function get_shift_statuses() {
        $data = null;
        foreach($this->schedule_status as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        /*$newrownew = null;
        $newrownew['label'] = "manhattan";
        $newrownew['value'] = $this->amazons3->lambdaShift();
        //$newrownew['value'] = 3;
        $data[] = $newrownew;*/
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the final shift statuses
     */
    public function get_shift_statuses_final() {
        $data = null;
        foreach($this->schedule_status_final as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the shift statuses
     */
    public function get_shift_statuses_grouped() {
        $data = null;
        foreach($this->schedule_status_grouped as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /**
     * fetches all the shift statuses needed to show in member portal
     */
    public function get_shift_status_portal() {
        $data = null;
        foreach($this->schedule_status_portal as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return array('status' => true, 'data' => $data);
    }

    /*
    * its used for gettting adddress
    */
    public function get_address_for_account($id,$account_type, $skip_location = false, $account_address_type = 2)
    {
        $output = array();
        if($account_type == "1") {
            $this->db->select(["pt.id as value, concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as label","pt.unit_number"]);
            $this->db->from("tbl_participants_master as p");
            $this->db->join('tbl_person_address as pt', 'pt.person_id = p.contact_id and pt.archive = 0', 'inner');
            $this->db->where("p.id", $id);
            $query = $this->db->get();
            $address = $query->num_rows() > 0 ? $query->result_array() : [];
            $location = $this->Participant_model->get_participant_location($id);
            if($skip_location)
                $output = $address;
            else
                $output = array_merge($address,$location);

            return array('status' => true, 'data' => $output);
        }
        else if($account_type == "2") {
            $this->db->select(["oa.id as value, concat(oa.street,', ',oa.city,' ',(select s.name from tbl_state as s where s.id = oa.state),' ',oa.postal) as label","oa.unit_number"]);
            $this->db->from("tbl_organisation_address as oa");
            $this->db->where("oa.organisationId", $id);
            $this->db->where("oa.primary_address", 1);
            $this->db->where("oa.address_type", $account_address_type);
            $this->db->where("oa.archive", 0);
            $query = $this->db->get();
            $orgAddress = $query->num_rows() > 0 ? $query->result_array() : [];
            return array('status' => true, 'data' => $orgAddress);
        }
    }

    /*
     * its used for gettting account or participant name on base of @param $ownerName
     */
    public function account_participant_name_search($ownerName = '', $skip_sites = false)
    {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["p.name as label", 'p.id as value', "'1' as account_type", "'0' as is_site"]);
        $this->db->from(TBL_PREFIX . 'participants_master as p');
        $this->db->where(['p.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();
        $this->db->select(["o.name as label", 'o.id as value', "'2' as account_type", "is_site"]);
        $this->db->from(TBL_PREFIX . 'organisation as o');
        if($skip_sites)
            $this->db->where(['o.is_site' => 0]);
        $this->db->where(['o.archive' => 0]);
        $this->db->having($queryHaving);
        $sql[] = $this->db->get_compiled_select();

        $sql = implode(' union ', $sql);
        $query = $this->db->query($sql);
        return $result = $query->result(); 
    }


    /*
     * its used for gettting site name on base of contact
     */
    function contact_site_name_search($reqData) {

        $this->db->like('label', $reqData->query);

        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';


        $select_column = ["o.name as label, o.id as value, o.is_site, '2' as account_type,  o.name, p.id as person_id, (CASE WHEN o.status = 1 THEN 'Active' WHEN o.status = 0 THEN 'Inactive' else '' end) as status, o.created, o.updated, sr.is_primary"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        
        $this->db->from("tbl_sales_relation as sr");
        $this->db->join('tbl_person as p', 'p.id = sr.destination_data_id', 'inner');
        $this->db->join('tbl_organisation as o', 'o.id = sr.source_data_id', 'inner');

        $this->db->where("o.archive", 0);
        $this->db->where("p.archive", 0);
        $this->db->where("sr.archive", 0);

        $this->db->where("sr.destination_data_type", 1);
        $this->db->where("o.is_site", 1);
        $this->db->where("sr.destination_data_id", $reqData->person_id);
        $this->db->where("sr.source_data_type", '2');
        $this->db->having($queryHaving); 

        $query = $this->db->get();
        return $result = $query->result(); 
    }

    

    /**
     * fetches the next number in creating shift
     */
    public function get_next_shift_no() {
        # finding how many got added so far
        $details = $this->basic_model->get_row_where_orderby('shift', array("id AS total"), '', 'id', 'DESC');
        $nextno = "1";
        if(!empty($details) && isset($details->total)) {
            $nextno = $details->total + 1;
        }
        $formatted_value = "ST".sprintf("%09d", $nextno);
        return array('status' => true, 'data' => $formatted_value);
    }

    /**
     * wrapper function for repeat shift option
     * does only validations part first and then actual db operations part
     */
    function create_update_shift_repeat_wrapper($data, $adminId, $uuid_user_type=ADMIN_PORTAL) {

        $errors = null;

        # let's only validate the main shift (no db ops)
        $main_shift_res = $this->Schedule_model->create_update_shift($data, $adminId,$uuid_user_type, true);
        if($main_shift_res['status'] == false && isset($main_shift_res['account_shift_overlap'])) {
            $errors[] = $main_shift_res['error'];
        }
        else if($main_shift_res['status'] == false)
            return $main_shift_res;

        # let's only validate the repeat shifts (no db ops)
        $repeat_res = $this->Schedule_model->repeat_shift($data, $adminId, true, $uuid_user_type);
        if($repeat_res['status'] == false && isset($repeat_res['account_shift_overlap'])) {
            $errors[] = $repeat_res['error'];
        }
        else if($repeat_res['status'] == false)
            return $repeat_res;

        # if any of above operations found the overlapping shift
        $account_shift_overlap = false;
        if(isset($main_shift_res['account_shift_overlap']) || isset($repeat_res['account_shift_overlap']))
            $account_shift_overlap = true;

        if(!empty($errors))
            return ["status" => false, "account_shift_overlap" => $account_shift_overlap, "error" => implode(",", $errors)];

        # let's do the db operations since all validations are ok
        $main_shift_res = $this->Schedule_model->create_update_shift($data, $adminId,$uuid_user_type);
        if (isset($main_shift_res) == true && isset($main_shift_res['id']) == true) {
            $data['primary_shift_id'] = $main_shift_res['id'];
            $data['scheduled_ndis_line_item_list'] = [];
            $data['scheduled_sa_id'] = '';
            $data['scheduled_docusign_id'] = '';
            $data['actual_ndis_line_item_list'] = [];
            $data['actual_sa_id'] = '';
            $data['actual_docusign_id'] = '';
        }
        
        $repeat_res = $this->Schedule_model->repeat_shift($data, $adminId, false, $uuid_user_type);
        $ndisErrUrl = '';
        if (isset($repeat_res['ndis']) && isset($repeat_res['ndis_error']) && $repeat_res['ndis'] == true && count($repeat_res['ndis_error']) > 0) {
            $shift_id = $main_shift_res['id'] ?? '';
            $ndisErr = $repeat_res['ndis_error'];
            # load modal
            $this->load->model('schedule/Ndispayments_model');
            $fullname = $this->Ndispayments_model->generateErrNdisFile($ndisErr, $shift_id);
            $ndisErrUrl = base_url('mediaShowTemp/ShiftNdisError?filename=' . urlencode(base64_encode($fullname)));
        }

        $main_shift_res['ndisErrUrl'] = $ndisErrUrl;
        return $main_shift_res;
    }

    /**
     * creating multiple shift based on the repeat option selected
     * either tomorrow, rest of the week or selected days
     */
    function repeat_shift($data, $adminId, $skip_db_ops = false, $uuid_user_type) {
        $response = null;

        # were we asked to repeat the shift?
        # tomorrow
        if($data['repeat_option'] == 1) {
            $data['repeat_days_selected'] = null;
            $data['repeat_days_selected'][] = date('Y-m-d', strtotime($data['scheduled_start_date'] . '+1 day'));
        }
        # rest of the week
        else if($data['repeat_option'] == 2) {
            $data['repeat_days_selected'] = null;
            $next_day = date("Y-m-d", strtotime('monday this week', strtotime($data['scheduled_start_date'])));
            for($i=1;$i<7;$i++) {
                $next_day = date('Y-m-d', strtotime($next_day . '+1 day'));
                $date_diff = dayDifferenceBetweenDate($data['scheduled_start_date'], $next_day);
                if($date_diff>0)
                $data['repeat_days_selected'][] = $next_day;
            }
        }
        # for specific days selection (repeat_option = 3), "$data['repeat_days_selected']" is passed from front-end
        if(empty($data['repeat_days_selected'])) {
            return ["status" => false, "error" => "Please select atleast one day to repeat the shift"];
        }

        # setting all actual shift info to null as we donot have to include it in repeat shift
        $data['actual_rows'] = null;
        $data['actual_travel'] = null;
        $data['actual_reimbursement'] = null;
        $data['notes'] = null;
        $data['actual_start_datetime'] = null;
        $data['actual_end_datetime'] = null;
        $data['actual_start_date'] = null;
        $data['actual_end_date'] = null;
        $data['actual_start_time'] = null;
        $data['actual_end_time'] = null;

        # load modal
        $this->load->model('schedule/Ndispayments_model');

        if(isset($data['repeat_days_selected']) == true && empty($data['repeat_days_selected']) == false) {
            $date_diff = dayDifferenceBetweenDate($data['scheduled_start_date'], $data['scheduled_end_date']);

            # for each repeat day, calling the modal function to create the shift
            # also adding a log
            $errors = null;
            $ndisPaymentError = [];
            $ndis_in = 0;
            foreach ($data['repeat_days_selected'] as $repeat_date) {

                $newdata = $data;
                $newdata['scheduled_start_date'] = $repeat_date;

                if($date_diff > 0)
                $newdata['scheduled_end_date'] = date('Y-m-d', strtotime($newdata['scheduled_start_date'] . '+'.$date_diff.' day'));
                else
                $newdata['scheduled_end_date'] = $newdata['scheduled_start_date'];

                # get ndis payment - only for participant
                if (isset($data['account_id']) && isset($data['account_type']) && $data['account_type'] == 1) {
                    $ndisErrTemp = [];

                    # get the new shift no
                    $next_shift_det = $this->get_next_shift_no();
                    $next_shift_det_id = $next_shift_det['data'];

                    $ndisErrTemp['shift_no'] = $next_shift_det_id;
                    $ndisErrTemp['date'] = $newdata['scheduled_start_date'];
                    $ndisErrTemp['error_1'] = false;
                    $ndisErrTemp['error_2'] = false;
                    $ndisErrTemp['error_3'] = false;

                    # get service agreement
                    $serviceAgreementData = [];
                    $account = [];
                    $account['value'] = $data['account_id'];
                    $account['account_type'] = $data['account_type'];
                    $serviceAgreementData['account'] = (object) $account;
                    $serviceAgreementData['start_date'] = $newdata['scheduled_start_date'] ?? '';
                    $serviceAgreementData['end_date'] = $newdata['scheduled_end_date'] ?? '';
                    $serviceAgreementData['section'] = 'scheduled';
                    $getServiceAgreement = $this->get_service_agreement((object) $serviceAgreementData);

                    if (isset($getServiceAgreement['status']) && $getServiceAgreement['status'] == false && isset($getServiceAgreement['rule']) && $getServiceAgreement['rule'] != '') {
                        if ($getServiceAgreement['rule'] == 1) {
                            $ndisErrTemp['error_1'] = true;
                        } else {
                            $ndisErrTemp['error_2'] = true;
                        }
                    }

                    $scheduled_sa_id = '';
                    if (isset($getServiceAgreement) && array_key_exists('data', $getServiceAgreement) && !empty($getServiceAgreement['data'])) {
                        $scheduledSAData = $getServiceAgreement['data'];
                        $scheduled_sa_id = $scheduledSAData['service_agreement_id'] ?? '';
                        $newdata['scheduled_sa_id'] = $scheduled_sa_id;
                        $newdata['scheduled_docusign_id'] = $scheduledSAData['docusign_id'] ?? '';
                    }

                    # get  line items
                    $support_type = '';
                    if (isset($data['scheduled_support_type']) && !empty($data['scheduled_support_type'])) {
                        $support_type = $data['scheduled_support_type'];
                    }

                    $lineItemData = [];
                    $ph_data = $newdata['account_address'] ?? '';
                    $phDataAddress = [];
                    if($ph_data != '' && isset($ph_data['value'])) {
                        $phDataAddress = (object) $ph_data;
                    } else {
                        $phDataAddress['account_address'] = [];
                        if (isset($newdata['full_account_address']) && !empty($newdata['full_account_address'])) {
                            $phDataAddress =(object) $data['full_account_address'];
                        }          
                    }   
                    $lineItemData['account_address'] = (object) $phDataAddress;
                    $lineItemData['start_date'] = $newdata['scheduled_start_date'] ?? '';
                    $lineItemData['start_time'] = $newdata['scheduled_start_time'] ?? '';
                    $lineItemData['end_date'] = $newdata['scheduled_end_date'] ?? '';
                    $lineItemData['end_time'] = $newdata['scheduled_end_time'] ?? '';
                    $lineItemData['service_agreement_id'] = $scheduled_sa_id;
                    $lineItemData['supportType'] = $support_type;
                    $lineItemData['section'] = 'scheduled';
                    $lineItemData['scheduled_rows'] = $newdata['scheduled_rows'] ?? [];
                    $lineItemData['scheduled_duration'] = $newdata['scheduled_duration'];

                    # Get support type keyname
                    $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
                    $support_key_name = '';
                    if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
                        $support_key_name = $supportType->key_name;
                    }

                    # Set support type duration if it is mixed
                    if (isset($newdata['scheduled_support_type_duration']) && !empty($newdata['scheduled_support_type_duration']) && $support_key_name == 'mixed') {
                        $item_data = $lineItemData;
                        $item_data['rows'] = $lineItemData['scheduled_rows'];
                        $item_data['support_type_duration'] = $newdata['scheduled_support_type_duration'];

                        # form a day array for mixed type
                        $getNDISDateFormat = $this->Ndispayments_model->get_support_type_ndis_duration($item_data);
                        $newdata['scheduled_support_type_duration'] = $getNDISDateFormat;
                        $sch_support_type_duration = $getNDISDateFormat;

                        # Prepare array for pulling line item based on day specific 
                        $support_type_duration =[];
                        foreach($sch_support_type_duration as $support_duration) {
                            $support_duration = (object) $support_duration;
                            $support_type_duration = array_merge($support_type_duration, $support_duration->duration); 
                        }
                        
                        $lineItemData['support_type_duration'] = $support_type_duration;
                    }
                    $schLineItem = $this->Ndispayments_model->get_line_items_for_payment($lineItemData);

                    $newdata['scheduled_ndis_line_item_list'] = [];
                    $newdata['scheduled_missing_line_item'] = false;
                    if (isset($schLineItem) && array_key_exists('data', $schLineItem) && !empty($schLineItem['data'])) {
                        $newdata['scheduled_ndis_line_item_list'] = $schLineItem['data'] ?? [];
                        $newdata['scheduled_missing_line_item'] = $schLineItem['missing_line_item'];
                        $ndisErrTemp['error_3'] = $schLineItem['missing_line_item'];
                    }

                    # if any one error should true than add to array
                    if ($ndisErrTemp['error_1'] == true || $ndisErrTemp['error_2'] == true || $ndisErrTemp['error_3'] == true) {
                        $ndisPaymentError[$ndis_in] = $ndisErrTemp;
                        $ndis_in++;
                    }                    
                }
                $response = $this->create_update_shift($newdata, $adminId,$uuid_user_type, $skip_db_ops,false,[],false);
                if(!(isset($response) && $response['status'] == true) && isset($response['account_shift_overlap'])) {
                    $errors[] = $response['error'];
                }
                else if(!(isset($response) && $response['status'] == true)) {
                    return $response;
                }
            }

            if(!empty($errors)) {
                return ["status" => false, "account_shift_overlap" => true, "error" => implode(", ", $errors)];
            }
        }

        # return with ndis error if ndis_in greater than 0
        if ($ndis_in > 0) {
            $response['ndis_error'] = [];
            $response['ndis_error'] = $ndisPaymentError;
            $response['ndis'] = true;
        }
        return $response;
    }

    /**
     * used by the create_update_shift function to insert a log entry on shift adding / updating
     */
    public function add_create_update_shift_log($data, $adminId, $shift_id) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        if (!empty($shift_id)) {
            $this->loges->setTitle("Updated shift:".$shift_id." by " . $adminName);
        } else {
            $this->loges->setTitle("New shift created by " . $adminName);
        }
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($shift_id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /**
     * updating shift information from member portal
     */
    function create_update_shift_portal($data, $adminId,$uuid_user_type) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();
        $shift_id = $data['id'] ?? 0;
        
        # validation rule
        $validation_rules = [
            array('field' => 'shift_no', 'label' => 'Shift No', 'rules' => 'required'),
            array('field' => 'id', 'label' => 'Shift id', 'rules' => 'required'),
            array('field' => 'actual_start_date', 'label' => 'Actual start date', 'rules' => 'required'),
            array('field' => 'actual_start_time', 'label' => 'Actual start time', 'rules' => 'required'),
            array('field' => 'actual_end_date', 'label' => 'Actual end date', 'rules' => 'required'),
            array('field' => 'actual_end_time', 'label' => 'Actual end time', 'rules' => 'required')
        ];

        if($data['actual_start_date'] && $data['actual_start_time']) {
            $data['actual_start_datetime'] = $data['actual_start_date']." ".$data['actual_start_time'];
            $validation_rules[] = array(
                'field' => 'actual_start_datetime', 'label' => 'Scheduled start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect Actual start date & time',
                ]
            );
        }
        else
        $data['actual_start_datetime'] = '';

        if($data['actual_end_date'] && $data['actual_end_time']) {
            $data['actual_end_datetime'] = $data['actual_end_date']." ".$data['actual_end_time'];
            $validation_rules[] = array(
                'field' => 'actual_end_datetime', 'label' => 'Scheduled end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect Actual end date & time',
                ]
            );
        }
        else
        $data['actual_end_datetime'] = '';

        if(!empty($data['actual_travel_duration_duration_hr'])){
            $validation_rules[] = array('field' => 'actual_travel_duration_duration_hr', 'label' => 'Actual Commuting Travel Allowance hr', 'rules' => 'required|numeric|less_than[25]',
            'errors' => [
                'less_than' => 'Actual Commuting Travel Allowance Hours should be less than or equal to 24',
            ]
            );
        }
        if(!empty($data['actual_travel_duration_duration_min']))
        {
            $validation_rules[] = array('field' => 'actual_travel_duration_duration_min', 'label' => 'Actual Commuting Travel Allowance min', 'rules' => 'required|numeric|less_than[60]',
            'errors' => [
                'less_than' => 'Actual Commuting Travel Allowance Minutes should be less than or equal to 59',
            ]
            );
        }

        if(!empty($data['actual_travel']))
            $validation_rules[] = array('field' => 'actual_travel', 'label' => 'Actual travel', 'rules' => 'required|numeric');
        if(!empty($data['actual_reimbursement']))
            $validation_rules[] = array('field' => 'actual_reimbursement', 'label' => 'Actual Reimbursement', 'rules' => 'required|numeric');

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        if (isset($data['actual_rows']) && !empty($data['actual_rows'])) {
            $data['actual_rows'] =(array) json_decode($data['actual_rows'], true);
        }
        # checking the valid actual shift date range
        # checking break entries and validating them
        $valid_dates = $this->validate_shift_and_breaks_dates($data,isset($data['actual_rows'])?$data['actual_rows']:null,$data['actual_start_datetime'], $data['actual_end_datetime'],2, null, true);
        if($valid_dates && $valid_dates['status'] == false)
            return $valid_dates;
        else if($valid_dates && $valid_dates['status'] == true)
            $data['actual_rows'] = $valid_dates['data'];

        # finding the actual shift duration
        $newdata2 = $data;
        $newdata2['break_cat'] = 2;
        $shift_dur_details = $this->calculate_shift_duration($newdata2);
        if($shift_dur_details['status'] == true)
            $data['actual_duration'] = $shift_dur_details['data'];

        $id = $data['id'] ?? '';
        
        if (isset($data['actual_support_type_duration']) && !empty($data['actual_support_type_duration'])) {
            
            #Prepare array for pulling line item based on day specific 
            $actual_support_type_duration = json_decode($data['actual_support_type_duration']);
            $act_support_type_duration =[];
            foreach($actual_support_type_duration as $support_duration) {
                $act_support_type_duration = array_merge($act_support_type_duration, $support_duration->duration); 
            }
            
            $data['actual_support_type_duration'] = json_decode($data['actual_support_type_duration'], true);
        }

        if (isset($data['actual_support_type_duration']) && !empty($data['actual_support_type_duration'])) {
            $data['actual_support_type_duration'] = json_decode($data['actual_support_type_duration'], true);
        }

        # if only account is participant
        if (isset($data['account_type']) && $data['account_type'] == 1) {

            $data['account_address'] = json_decode($data['account_address']);
            $data['account'] = json_decode($data['account']);

            # get service agreement
            $serviceAgreementData = [];
            $account = $data['account'] ?? '';
            $serviceAgreementData['account'] = (object) $account;
            $serviceAgreementData['start_date'] = $data['actual_start_datetime'] ?? '';
            $serviceAgreementData['end_date'] = $data['actual_end_datetime'] ?? '';
            $serviceAgreementData['section'] = 'actual';
            $getServiceAgreement = $this->get_service_agreement((object) $serviceAgreementData);
            
            $actual_sa_id = '';
            if (isset($getServiceAgreement) && array_key_exists('data', $getServiceAgreement) && !empty($getServiceAgreement['data'])) {
                $actSAData = $getServiceAgreement['data'];
                $actual_sa_id = $actSAData['service_agreement_id'] ?? '';
                $data['actual_sa_id'] = $actual_sa_id;
                $data['actual_docusign_id'] = $actSAData['docusign_id'] ?? '';
            }

            # get  line items
            $support_type = '';
            if (isset($data['actual_support_type']) && !empty($data['actual_support_type'])) {
                $support_type = $data['actual_support_type'];
            }

            if ($support_type == '' && isset($data['scheduled_support_type']) && !empty($data['scheduled_support_type'])) {
                $support_type = $data['scheduled_support_type'];
            }

            $data['actual_support_type'] = $support_type;
            
            $lineItemData = [];
            $account_address = $data['account_address'] ?? '';
            $lineItemData['account_address'] = (object) $account_address;
            $lineItemData['start_date'] = $data['actual_start_date'] ?? '';
            $lineItemData['start_time'] = $data['actual_start_time'] ?? '';
            $lineItemData['end_date'] = $data['actual_end_date'] ?? '';
            $lineItemData['end_time'] = $data['actual_end_time'] ?? '';
            $lineItemData['service_agreement_id'] = $actual_sa_id;
            $lineItemData['supportType'] = $support_type;
            $lineItemData['section'] = 'actual';
            $lineItemData['scheduled_rows'] = $data['actual_rows'] ?? [];
            $lineItemData['scheduled_duration'] = $data['actual_duration'];
            $lineItemData['support_type_duration'] = $data['actual_support_type_duration'] ?? [];

            $this->load->model('schedule/Ndispayments_model');
            
            $actLineItem = $this->Ndispayments_model->get_line_items_for_payment($lineItemData);
            //Pass the error into front end if any on while saving timesheet in member portal
            if (!empty($actLineItem) && !empty($actLineItem['error'])) {
                return ['status' => false, 'error' => $actLineItem['error']];
            }
            
            $data['actual_ndis_line_item_list'] = [];
            $data['actual_missing_line_item'] = false;
            if (isset($actLineItem) && array_key_exists('data', $actLineItem) && !empty($actLineItem['data'])) {
                $data['actual_ndis_line_item_list'] = $actLineItem['data'] ?? [];
                $data['actual_missing_line_item'] = $actLineItem['missing_line_item'];
            }

            $support_type = $data['actual_support_type'] ?? 0;
            $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
            $support_key_name = '';
            if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
                $support_key_name = $supportType->key_name;
            }
            
            if(isset($data['actual_support_type']) && !empty($data['actual_support_type']) && $support_key_name == 'mixed' && !empty($data['actual_support_type_duration']) && $data['actual_start_datetime'] != '' && $data['actual_end_datetime'] != '' && ($data['actual_sa_id'] == '' || $data['actual_sa_id'] == null || $data['actual_docusign_id'] == '' || $data['actual_docusign_id'] == null)) {
                return ['status' => false, 'error' => 'Actual - Signed Service Agreement is required for Mixed Support shifts'];       
            }

            if(isset($data['actual_support_type']) && !empty($data['actual_support_type']) && $support_key_name == 'mixed' && !empty($data['actual_support_type_duration']) && $data['actual_start_datetime'] != '' && $data['actual_end_datetime'] != '' && isset($data['actual_missing_line_item']) && $data['actual_missing_line_item']) {
                return ['status' => false, 'error' => 'Actual - Both Self Care/Comm Access codes are required to be added in the plan for Mixed Support Shifts'];       
            }
        }
        
        # call create/update shift modal function
        $shift_id = $this->populate_shift($data, $adminId,$uuid_user_type, false, '', true);

        # check $shift_id is not empty
        if (!$shift_id) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # adding scheduled breaks
        if(!empty($data['actual_rows']) && !empty($data['actual_start_datetime'])) {
            $result = $this->assign_shift_breaks($data['actual_rows'], 2, $shift_id, $adminId);
            if($result['status'] == false)
            return $result;
        }
        else if(!empty($data['actual_rows']) && empty($data['actual_start_datetime'])) {
            # remove actual breaks if they were provided earlier but now the actual start & end times are not provided
            $this->archive_shift_breaks($data['actual_rows'], $adminId);
        }
        else {
            # remove scheduled breaks if they were provided earlier but now they are empty
            $old_actual_breaks = $this->get_shift_breaks_list($shift_id, 2);
            if($old_actual_breaks) {
                $old_actual_breaks = object_to_array($old_actual_breaks);
                $this->archive_shift_breaks($old_actual_breaks, $adminId);
            }
        }

        # adding a log entry
        $this->add_create_update_shift_log($data, $adminId, $shift_id);

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Shift has been completed successfully.';
            if($uuid_user_type==MEMBER_PORTAL){
                $msg = 'Timesheet updated successfully';
            }
            
        } else {
            $msg = 'Shift has been created successfully.';
        }
        $response = ['status' => true, 'msg' => $msg, 'id' => $shift_id];
        return $response;

    }


    

    /**
     * validates shift start and end dates
     * validate shift breaks and some S/O rules
     */
    function validate_shift_and_breaks_dates($data, $breaks, $start_datetime, $end_datetime, $type, $skip_some_checks = null, $validate_inso = false) {
        if(empty($start_datetime) || empty($end_datetime))
        return;

        $label = "scheduled";
        if($type == 2) {
            $label = "actual";
        }
        $uclabel = ucwords($label);

        # do not check some business rules if overtime is set to allowed
        $overtime_allowed = get_setting(Setting::OVERTIME_ALLOWED, OVERTIME_ALLOWED);

        $valid_date_range = check_dates_lower_to_other($start_datetime, $end_datetime);
        if(!$valid_date_range) {
            $field_key = $label . "_section";
            $response = [
                "status" => false,
                "error" => [$field_key => formatError($field_key, "{$uclabel} start date-time: ".date("d/m/Y h:i A", strtotime($start_datetime))." should be lower to {$label} end date-time: ".date("d/m/Y h:i A", strtotime($end_datetime)))]
            ];
            return $response;
        }

        $total_sleepover_minutes = 0;
        $so_ref_id = $new_rows = $up_ref_id = null;
        if(!empty($breaks)) {
            # validating shift break timings
            list($invalid_breaks, $new_rows) = $this->validate_shift_breaks($data, $breaks, $type, $skip_some_checks, $validate_inso);
            if($invalid_breaks) {
                $response = [
                    "status" => false,
                    "error" => ["{$label}_breaks" => formatError("{$label}_breaks", implode(". ", $invalid_breaks))]
                ];
                return $response;
            }

            # checking if there are any sleepover breaks provided. If so, excluding that timing
            # from overall shift duration
            $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
            if ($so_details)
                $so_ref_id = $so_details->id;
            foreach($new_rows as $row) {
                if($row['break_type'] == $so_ref_id)
                $total_sleepover_minutes += $row['duration_int'];
            }

            # finding the unpaid break reference id for later use
            $up_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "unpaid", "archive" => 0]);
            if ($up_details)
                $up_ref_id = $up_details->id;
        }

        # checking the overall duration of shift, cannot be more than the threshold
        # excluding the sleepover breaks
        # only check if overtime is not allowed and breaks are scheduled types
        if($type == 1 && $overtime_allowed == false && $skip_some_checks == false) {
            $shift_minutes = minutesDifferenceBetweenDate($start_datetime, $end_datetime);
            $shift_hours = 0;
            if($shift_minutes) {
                $shift_hours = round(($shift_minutes - $total_sleepover_minutes) / 60,4);
                if($shift_hours > MAX_SHIFT_DURATION) {
                    $response = [
                        "status" => false,
                        "error" => ["{$label}_duration" => formatError("scheduled_section", "Maximum {$label} shift duration is ".MAX_SHIFT_DURATION." Hrs")]
                    ];
                    return $response;
                }
            }
        }

        # donot need to proceed if overtime is allowed or breaks are actual types or no sleepover break provided
        if(($total_sleepover_minutes == 0 || $skip_some_checks == true || $type == 2) || ($overtime_allowed == true && $type == 2)) {
            $response = ["status" => true, "data" => $new_rows ];
            return $response;
        }

        # checking the sleepover shift with active hours before/after the sleepover
        # there has to be active hours period
        $valid_so_work = $this->validate_so_shift_active_work($start_datetime, $end_datetime, $new_rows);
        if($valid_so_work['status'] == false)
            return $valid_so_work;
        
        $response = ["status" => true, "data" => $new_rows ];
        return $response;
    }

    /**
     * checking the sleepover shift with active hours before/after the sleepover
     * there has to be active hours period
    */
    function validate_so_shift_active_work($start_datetime, $end_datetime, $break_rows, $check_all = false) {

        $ah_before_start_datetime = $ah_before_end_datetime = null;
        $ah_after_start_datetime = $ah_after_end_datetime = null;
        $active_hours_after = $active_hours_before = null;

        # finding the sleepover break reference id for later use
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;

        # finding the unpaid break reference id for later use
        $up_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "unpaid", "archive" => 0]);
        if ($up_details)
            $up_ref_id = $up_details->id;

        $before_after_missing = $before_missing = false;
        foreach($break_rows as $row) {
            if($row['break_type'] == $so_ref_id) {
                if(!isset($row['break_start_datetime']) || !isset($row['break_end_datetime']))
                continue;

                $break_start_datetime = $row['break_start_datetime'];
                $break_end_datetime = $row['break_end_datetime'];
                $active_hours_before = minutesDifferenceBetweenDate($start_datetime, $break_start_datetime);
                $ah_before_start_datetime = $start_datetime;
                $ah_before_end_datetime = $break_start_datetime;
                $active_hours_after = minutesDifferenceBetweenDate($break_end_datetime, $end_datetime);
                $ah_after_start_datetime = $break_end_datetime;
                $ah_after_end_datetime = $end_datetime;
                if($active_hours_before < ACTIVE_SO_DURATION && $active_hours_after < ACTIVE_SO_DURATION) {
                    $before_after_missing = true;
                    if($check_all == false) {
                        $response = [
                            "status" => false,
                            "error" => ['scheduled_breaks' => formatError('scheduled_breaks', "Shift needs to have ".(ACTIVE_SO_DURATION / 60)." Hrs of Active work before/after the S/O break period")]
                        ];
                        return $response;
                    }
                }

                if($active_hours_before < ACTIVE_SO_BEFORE_DURATION) {
                    $before_missing = true;
                    if($check_all == false) {
                        $response = [
                            "status" => false,
                            "error" => ['scheduled_breaks' => formatError('scheduled_breaks', "Shift needs to have ".(ACTIVE_SO_BEFORE_DURATION / 60)." Hr of Active work before the S/O break period")]
                        ];
                        return $response;
                    }
                }
            }
        }

        # checking if any unpaid breaks provided that may reduce the active hours before/after
        # excluding those break durations from active hours before/after calculations
        $total_up_break_minutes_before = $total_up_break_minutes_after = 0;
        foreach($break_rows as $ind => $row) {
            if($row['break_type'] != $up_ref_id)
                continue;

            if(!isset($row['break_start_datetime']) || !isset($row['break_end_datetime']))
                continue;

            if(!empty($row['break_duration']) && $row['duration_disabled'] == 0) continue;

            $combreak_start_datetime = $row['break_start_datetime'];
            $combreak_end_datetime = $row['break_end_datetime'];

            $start_in_range = check_dates_between_two_dates($ah_before_start_datetime, $ah_before_end_datetime, $combreak_start_datetime, true);
            if($start_in_range) {
                $up_break_minutes_before = minutesDifferenceBetweenDate($combreak_start_datetime, $combreak_end_datetime);
                $total_up_break_minutes_before += $up_break_minutes_before;
            }

            $end_in_range = check_dates_between_two_dates($ah_after_start_datetime, $ah_after_end_datetime, $combreak_end_datetime, true);
            if($end_in_range) {
                $up_break_minutes_after = minutesDifferenceBetweenDate($combreak_start_datetime, $combreak_end_datetime);
                $total_up_break_minutes_after += $up_break_minutes_after;
            }
        }

        $active_hours_before = $active_hours_before - $total_up_break_minutes_before;
        $active_hours_after = $active_hours_after - $total_up_break_minutes_after;

        if($active_hours_before < ACTIVE_SO_DURATION && $active_hours_after < ACTIVE_SO_DURATION) {
            $before_after_missing = true;
            if($check_all == false) {
                $response = [
                    "status" => false,
                    "before_invalid" => false,
                    "error" => ['scheduled_breaks' => formatError('scheduled_breaks', "Shift needs to have ".(ACTIVE_SO_DURATION / 60)." Hrs of Active work before/after the S/O break period")]
                ];
                return $response;
            }
        }

        if($active_hours_before < ACTIVE_SO_BEFORE_DURATION) {            
            $before_missing = true;
            if($check_all == false) {
                $response = [
                    "status" => false,
                    "before_invalid" => true,
                    "error" => ['scheduled_breaks' => formatError('scheduled_breaks', "Shift needs to have ".(ACTIVE_SO_BEFORE_DURATION / 60)." Hr of Active work before the S/O break period")]
                ];
                return $response;
            }
        }

        if($before_after_missing || $before_missing) {
            $active_hours_before = ($active_hours_before > 0) ? round($active_hours_before / 60, 2) : 0;
            $active_hours_after = ($active_hours_after > 0) ? round($active_hours_after / 60, 2) : 0;
            return ["status" => false, "error" => [$before_after_missing, $before_missing, $active_hours_before, $active_hours_after]];
        }
        else
            return ["status" => true, "data" => "ok"];
    }

    /**
     * creates a new shift record or updates an existing one
     */
    function create_update_shift($data, $adminId,$uuid_user_type, $skip_db_ops = false, $isDataFromImport=false, $errorsFromImport=[],$mixedValidation=true) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $shift_id = $data['id'] ?? 0;
        $account_id = $data['account_id'] ?? 0;
        $account_type = $data['account_type'] ?? 0;
        $repeat_selected = !empty($data['repeat_option']) ? 1 : 0;

        # is the object being altered currently by other user? if yes - cannot perform this action
        if($shift_id) {
            $lock_taken = $this->add_shift_lock($shift_id, $adminId);
            if($lock_taken['status'] == false)
                return $lock_taken;
        }

        # appending timings into date fields
        $data['scheduled_start_datetime'] = $data['scheduled_start_date']." ".$data['scheduled_start_time'];
        $data['scheduled_end_datetime'] = $data['scheduled_end_date']." ".$data['scheduled_end_time'];
        if($data['actual_start_date'] || $data['actual_start_time'])
            $data['actual_start_datetime'] = $data['actual_start_date']." ".$data['actual_start_time'];
        else
            $data['actual_start_datetime'] = '';

        if($data['actual_end_date'] || $data['actual_end_time'])
            $data['actual_end_datetime'] = $data['actual_end_date']." ".$data['actual_end_time'];
        else
            $data['actual_end_datetime'] = '';

        if(strlen(trim($data['scheduled_start_datetime'])) > 0 && $data['scheduled_start_datetime'] != '' && $data['scheduled_start_datetime'] != null && $data['scheduled_start_datetime'] != '0000-00-00'&&strtotime($data['scheduled_start_datetime']) !== false) {
            $data['scheduled_start_datetime'] = date('Y-m-d h:i A',strtotime($data['scheduled_start_datetime']));
        }

        if (strlen(trim($data['scheduled_end_datetime'])) > 0 && $data['scheduled_end_datetime'] != '' && $data['scheduled_end_datetime'] != null && $data['scheduled_end_datetime'] != '0000-00-00'&&strtotime($data['scheduled_end_datetime']) !== false) {
            $data['scheduled_end_datetime'] = date('Y-m-d h:i A',strtotime($data['scheduled_end_datetime']));
        }

        if ( strlen(trim($data['actual_start_datetime'])) > 0 && $data['actual_start_datetime'] != '' && $data['actual_start_datetime'] != null && $data['actual_start_datetime'] != '0000-00-00'&&strtotime($data['actual_start_datetime']) !== false) {
            $data['actual_start_datetime'] = date('Y-m-d h:i A',strtotime($data['actual_start_datetime']));
        }

        if (strlen(trim($data['actual_end_datetime'])) > 0  && $data['actual_end_datetime'] != '' && $data['actual_end_datetime'] != null && $data['actual_end_datetime'] != '0000-00-00'&&strtotime($data['actual_end_datetime']) !== false) {
            $data['actual_end_datetime'] = date('Y-m-d h:i A',strtotime($data['actual_end_datetime']));
        }        
        

        # validation rule
        $validation_rules = [
            array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'shift_account', 'message' => 'Name is required'
            ])]),
            array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'account_type', 'message' => 'Account Type is required'
            ])]),
            array('field' => 'role_id', 'label' => 'Service Type', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'Service Type', 'message' => 'Service Type is required'
            ])]),
            array('field' => 'status', 'label' => 'Status', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'Status', 'message' => 'Status is required'
            ])]),
            array(
                'field' => 'scheduled_start_datetime', 'label' => 'Scheduled start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => json_encode([
                        'field' => 'scheduled_section', 'message' => 'Please enter the correct date/time']),
                    'required' => json_encode([
                        'field' => 'scheduled_section', 'message' => 'Scheduled start date & time is required'])
                ]
            ),
            array(
                'field' => 'scheduled_end_datetime', 'label' => 'Scheduled end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => json_encode([
                        'field' => 'scheduled_section', 'message' => 'Please enter the correct date/time']),
                    'required' => json_encode([
                        'field' => 'scheduled_section', 'message' => 'Scheduled end date & time is required'])
                    
                ]
                ),
            array('field' => 'contact_id', 'label' => 'Contact', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'combobox_contact', 'message' => 'Contact is required'
            ])]),
            array('field' => 'contact_phone', 'label' => 'Confirmation Phone', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'contact_phone', 'message' => 'Phone is required'
            ])]),
            array('field' => 'contact_email', 'label' => 'Confirmation Email', 'rules' => 'required', 'errors' => ['required' => json_encode([
                'field' => 'contact_email', 'message' => 'Email is required'
            ])]),
        ];
        if($isDataFromImport){
            $validation_rules[1]=[];
            $validation_rules[2]=[];
            $validation_rules[3]=[];
            $validation_rules[7]=[];
        }
        
        # checking actual start date & time and end date & time
        # if any of those is provided, all need to be validated
        if (!empty($data['actual_start_date']) || !empty($data['actual_start_time']) || !empty($data['actual_end_date']) || !empty($data['actual_end_time'])) {
            $validation_rules[] = array(
                'field' => 'actual_start_datetime', 'label' => 'Actual start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => json_encode(['field' => 'actual_start_date', 'message' => 'Incorrect actual start date & time'])
                ]
            );
            $validation_rules[] = array(
                'field' => 'actual_end_datetime', 'label' => 'Actual end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => json_encode(['field' => 'actual_end_date', 'message' => 'Incorrect actual end date & time'])
                ]
            );
        }
        
        # checking optional numeric fields
        if(!empty($data['scheduled_travel'])) {
            $validation_rules[] = array('field' => 'scheduled_travel', 'label' => 'Scheduled travel', 'rules' => 'required|numeric', 'errors' => [
                'required' => json_encode(['field' => 'scheduled_travel', 'message' => 'Travel Allowance is required']),
                'numeric' => json_encode(['field' => 'scheduled_travel', 'message' => 'Travel Allowance should be a number only'])
            ]);
        }
        if(!empty($data['scheduled_reimbursement'])) {
            $validation_rules[] = array('field' => 'scheduled_reimbursement', 'label' => 'Scheduled Reimbursement', 'rules' => 'required|numeric', 'errors' => [
                'numeric' => json_encode(['field' => 'scheduled_reimbursement', 'message' => 'Reimbursements should be a number only'])
            ]);
        }        
        if(!empty($data['scheduled_travel_duration_hr'])) {
            $validation_rules[] = array('field' => 'scheduled_travel_duration_hr', 'label' => 'Scheduled Commuting Travel Allowance (Duration HH:MM)', 'rules' => 'required|numeric|less_than[25]',
            'errors' => [
                'numeric' => json_encode(['field' => 'scheduled_travel_duration_hr', 'message' => 'Scheduled Commuting Travel Allowance Hours should be a number only']),
                'less_than' => json_encode(['field' => 'scheduled_travel_duration_hr', 'message' => 'Scheduled Commuting Travel Allowance Hours should be less than or equal to 24'])
            ]
            );
        }
        if(!empty($data['scheduled_travel_duration_min'])) {
            $validation_rules[] = array('field' => 'scheduled_travel_duration_min', 'label' => 'Scheduled Commuting Travel Allowance (Duration HH:MM)', 'rules' => 'required|numeric|less_than[60]',
            'errors' => [
                'less_than' => json_encode(['field' => 'scheduled_travel_duration_min', 'message' => 'Scheduled Commuting Travel Allowance Minutes should be less than or equal to 59'])
            ]
            );
        }
        if(!empty($data['scheduled_travel_distance'])) {
            $validation_rules[] = array('field' => 'scheduled_travel_distance', 'label' => 'Scheduled Commuting Travel Allowance (Distance hrs)', 'rules' => 'required|numeric', 'errors' => [
                'numeric' => json_encode(['field' => 'scheduled_travel_duration_hr', 'message' => 'Scheduled Commuting Travel Allowance (Distance hrs) should be a number only'])
            ]);
        }

        if(!empty($data['actual_reimbursement'])) {
            $validation_rules[] = array('field' => 'actual_reimbursement', 'label' => 'Actual Reimbursement', 'rules' => 'required|numeric', 'errors' => [
                'numeric' => json_encode(['field' => 'actual_allowances', 'message' => 'Reimbursement should be a number only'])
            ]);
        }
        if(!empty($data['actual_travel'])) {
            $validation_rules[] = array('field' => 'actual_travel', 'label' => 'Actual travel', 'rules' => 'required|numeric', 'errors' => [
                'numeric' => json_encode(['field' => 'actual_allowances', 'message' => 'Travel should be a number only'])
            ]);
        }
        if(!empty($data['actual_travel_duration_duration_hr'])) {
            $validation_rules[] = array('field' => 'actual_travel_duration_duration_hr', 'label' => 'Actual Commuting Travel Allowance (Duration HH:MM)', 'rules' => 'required|numeric|less_than[25]',
            'errors' => [
                'less_than' => json_encode(['field' => 'actual_allowances', 'message' => 'Actual Commuting Travel Allowance Hours should be less than or equal to 24'])
            ]
            );
        }
        if(!empty($data['actual_travel_duration_duration_min'])) {
            $validation_rules[] = array('field' => 'actual_travel_duration_duration_min', 'label' => 'Actual Commuting Travel Allowance (Duration HH:MM)', 'rules' => 'required|numeric|less_than[60]',
            'errors' => [
                'less_than' => json_encode(['field' => 'actual_allowances', 'message' => 'Actual Commuting Travel Allowance Minutes should be less than or equal to 59'])
            ]
            );
        }
        if(!empty($data['actual_travel_distance']))
            $validation_rules[] = array('field' => 'actual_travel_distance', 'label' => 'Actual Commuting Travel Allowance (Distance hrs)', 'rules' => 'required|numeric', 'errors' => [
                'numeric' => json_encode(['field' => 'actual_allowances', 'message' => 'Commuting Travel Allowance (Distance KMs) should be a number only'])
            ]);
            
        $this->load->model('schedule/Ndispayments_model');        

        //Adding validation for support type cleaning
        if(isset($data['scheduled_support_type']) && $data['scheduled_support_type'] == SUPPORT_TYPE_SELF_CLEAN) {
            $start_day = date('D', strtotime($data['scheduled_start_date']));
            $end_day = date('D', strtotime($data['scheduled_end_date']));

            $start_time_limit = "06:00 AM";
            $end_time_limit = "08:00 PM";
            //Validation for checking if the shift date is more than one day
            if($start_day != $end_day) {
                $validation_rules[] = array(
                    'field' => 'scheduled_end_date', 'label' => 'scheduled start date', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' => json_encode(['field' => 'scheduled_support_type', 'message' => "Cleaning supports could be availed only on a single weekday between 6 AM - 8 PM"])]
                );
            }

            $start_is_holiday = $this->Ndispayments_model->check_public_holiday($data, $data['scheduled_start_date']);
            $end_is_holiday = $this->Ndispayments_model->check_public_holiday($data, $data['scheduled_end_date']);
            //validation for public holiday
            if($start_is_holiday || $end_is_holiday) {
                $validation_rules[] = array(
                    'field' => 'scheduled_end_datetime', 'label' => 'scheduled public holiday', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' => json_encode(['field' => 'scheduled_support_type', 'message' => "Cleaning supports could be availed only on a working day"])]
                );
            }

            //validation for checking Week end
            if($start_day == 'Sat' || $start_day == 'Sun'
                || $end_day == 'Sat' || $end_day == 'Sun') {
                $validation_rules[] = array(
                    'field' => 'scheduled_start_date', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                    'errors' => [
                        'error_message' => json_encode(['field' => 'scheduled_ndis', 'message' => "Cleaning Supports could only fall on Weekdays (Mon - Fri)"])]
                );
            }

            //Validation for checking start time and end time
            if(strtotime($data['scheduled_start_time']) < strtotime($start_time_limit)
                || strtotime($data['scheduled_end_time']) > strtotime($end_time_limit) ) {

                    $validation_rules[] = array(
                        'field' => 'scheduled_end_time', 'label' => 'scheduled shift duration', 'rules' => 'error_message',
                        'errors' => [
                            'error_message' => json_encode(['field' => 'scheduled_support_type', 'message' => "Cleaning supports could be availed only on a single weekday between 6 AM - 8 PM"])]
                    );
            }

        }

        # validate the line item & restrict shift creation if missing with support type is 
        # skip validation if it's repeated shift
        if($mixedValidation) {
            $support_type = $data['scheduled_support_type'] ?? 0;
            $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
            $support_key_name = '';
            if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
                $support_key_name = $supportType->key_name;
            }
            
           
            if(empty($data['validateOnly']) && isset($data['scheduled_support_type']) && !empty($data['scheduled_support_type']) && $support_key_name == 'mixed' && ($data['scheduled_sa_id'] == '' || $data['scheduled_sa_id'] == null || $data['scheduled_docusign_id'] == '' || $data['scheduled_docusign_id'] == null)) {
                return ['status' => false, 'error' => 'Scheduled - Signed Service Agreement is required for Mixed Support shifts'];
            }
            
            if(empty($data['validateOnly']) && isset($data['scheduled_support_type']) && !empty($data['scheduled_support_type']) && $support_key_name == 'mixed' && isset ($data['scheduled_missing_line_item']) && $data['scheduled_missing_line_item']) {
                return ['status' => false, 'error' => 'Scheduled - Both Self Care/Comm Access codes are required to be added in the plan for Mixed Support Shifts'];       
            }
    
            $support_type = $data['actual_support_type'] ?? 0;
            $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
            $support_key_name = '';
            if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
                $support_key_name = $supportType->key_name;
            }
            
            if(empty($data['validateOnly']) && isset($data['actual_support_type']) && !empty($data['actual_support_type']) && $support_key_name == 'mixed' && !empty($data['actual_support_type_duration']) && $data['actual_start_datetime'] != '' && $data['actual_end_datetime'] != '' && ($data['actual_sa_id'] == '' || $data['actual_sa_id'] == null || $data['actual_docusign_id'] == '' || $data['actual_docusign_id'] == null)) {
                return ['status' => false, 'error' => 'Actual - Signed Service Agreement is required for Mixed Support shifts'];       
            }
    
            if(empty($data['validateOnly']) && isset($data['actual_support_type']) && !empty($data['actual_support_type']) && $support_key_name == 'mixed' && !empty($data['actual_support_type_duration']) && $data['actual_start_datetime'] != '' && $data['actual_end_datetime'] != '' && isset ($data['actual_missing_line_item'])  && $data['actual_missing_line_item']) {
                return ['status' => false, 'error' => 'Actual - Both Self Care/Comm Access codes are required to be added in the plan for Mixed Support Shifts'];
            }
            
        }
        
        # set data in libray for validate
        $this->form_validation->set_data($data);
        # set validation rule
        $this->form_validation->set_rules($validation_rules);
        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $field_errors = formatErrors($errors);
            $response = ['status' => false, 'error' => $field_errors];
            return $response;
        }
        # if account is participant & support type is Comm Access - validate if it's s/o shift then Active SA should be Self care parent item or Self care Sleepover line item
        if (!empty($data['scheduled_support_type']) && $data['scheduled_support_type'] == SUPPORT_TYPE_SELF_COMM) { 
            $scheduled_rows = [];
            if($data['scheduled_rows']) {
                $scheduled_rows = $data['scheduled_rows'];
                $section = 'scheduled';
            } elseif($data['actual_rows']) {
                $scheduled_rows = $data['actual_rows'];
                $section = 'actual';
            }
            $sleepover = FALSE;
            $sleepover_item = TRUE;
            foreach ($scheduled_rows as $row) {
                # fetching the sleepover id from reference table
                $sleepover = $this->basic_model->get_row('references', ['id'], ['key_name' => 'sleepover', 'id' => $row['break_type'] ]);
                if (!empty($sleepover)) {
                    $this->load->model('schedule/Ndispayments_model');
                    $sleepover_item = $this->Ndispayments_model->check_so_line_item_availablity_for_comm_access($data['scheduled_sa_id']);
                    break;
                }
            }
            #This will apply while click refresh/save button
            if (!$sleepover_item) {
                return ['status' => false, 'error' => [$section . '_ndis' => formatError($section . '_ndis', "Self - Care support is not funded in the participant's current active SA")]];
            }
        }

        # returning errors as a string if called from import
        if(count($errorsFromImport)>0) {
            return ['status' => false, 'error' => implode(', ', [])];
        }

        # checking the valid scheduled shift date range
        # checking break entries and validating them
        $valid_dates = $this->validate_shift_and_breaks_dates($data,isset($data['scheduled_rows'])?$data['scheduled_rows']:null,$data['scheduled_start_datetime'], $data['scheduled_end_datetime'],1, null, true);
        if($valid_dates && $valid_dates['status'] == false)
            return $valid_dates;
        else if($valid_dates && $valid_dates['status'] == true)
            $data['scheduled_rows'] = $valid_dates['data'];

        # checking the valid actual shift date range
        # checking break entries and validating them
        $valid_dates = $this->validate_shift_and_breaks_dates($data,isset($data['actual_rows'])?$data['actual_rows']:null,$data['actual_start_datetime'], $data['actual_end_datetime'],2, null, true);
        if($valid_dates && $valid_dates['status'] == false)
            return $valid_dates;
        else if($valid_dates && $valid_dates['status'] == true)
            $data['actual_rows'] = $valid_dates['data'];

        # checking the shift timings, should be unique and not overlapping account's other shifts
        # only perform that check if the overlapping is not confirmed yet
        if(empty($data['validateOnly']) && empty($data['skip_account_shift_overlap'])) {
            $unq_account_shift_dates = $this->validate_unique_account_shifts($shift_id, $data['account_type'], $data['account_id'], $data['scheduled_start_datetime'], $data['scheduled_end_datetime']);
            if($unq_account_shift_dates && $unq_account_shift_dates['status'] == false)
                return $unq_account_shift_dates;
        }

        # skip doing the actual add/update shift operation (only validations part)
        # if we are asked specifically
        if($skip_db_ops) {
            $response = ['status' => true, 'msg' => 'validations ok'];
            return $response;
        }

        # finding the scheduled and actual shift duration
        $newdata = $data;
        $newdata['break_cat'] = 1;
        $shift_dur_details = $this->calculate_shift_duration($newdata);
        if($shift_dur_details['status'] == true)
            $data['scheduled_duration'] = $shift_dur_details['data'];

        $newdata2 = $data;
        $newdata2['break_cat'] = 2;
        $shift_dur_details = $this->calculate_shift_duration($newdata2);
        if($shift_dur_details['status'] == true)
            $data['actual_duration'] = $shift_dur_details['data'];

        # if status is getting changed to scheduled (in update), does it have only one member assigned?
        if($data['status'] == 3&&!$isDataFromImport) {
            $accept_response = $this->check_shift_members_and_accept($data['id']);
            if($accept_response['status'] == false)
            return $accept_response;
        }
        if (!empty($data['validateOnly'])) {
            return ['status' => false, 'msg' => "All checks applied"];
        }
        # call create/update shift modal function
        $shift_id = $this->populate_shift($data, $adminId, $uuid_user_type);

        # check $shift_id is not empty
        if (!$shift_id) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }
        
        if ($adminId) {
            $this->load->library('EventDispatcher');
            $data['created_by'] = $adminId;
            $data['updated_by'] = $adminId;
            $this->eventdispatcher->dispatch('onAfterShiftCreated', $shift_id, $data);
        }        
        
        # adding scheduled breaks
        if(!empty($data['scheduled_rows'])) {
            $result = $this->assign_shift_breaks($data['scheduled_rows'], 1, $shift_id, $adminId);
            if($result['status'] == false)
            return $result;
        }
        else {
            # remove scheduled breaks if they were provided earlier but now they are empty
            $old_scheduled_breaks = $this->get_shift_breaks_list($shift_id, 1);
            if($old_scheduled_breaks) {
                $old_scheduled_breaks = object_to_array($old_scheduled_breaks);
                $this->archive_shift_breaks($old_scheduled_breaks, $adminId);
            }
        }

        if(!empty($data['actual_rows']) && empty($data['actual_start_datetime'])) {
            # remove actual breaks if they were provided earlier but now the actual start & end times are not provided
            $this->archive_shift_breaks($data['actual_rows'], $adminId);
        }
        else {
            $result = $this->assign_shift_breaks(isset($data['actual_rows'])?$data['actual_rows']:null, 2, $shift_id, $adminId);
            if($result['status'] == false)
            return $result;
        }

        # adding a log entry
        // $this->add_create_update_shift_log($data, $adminId, $shift_id);

        # setting the message title
        if (!empty($data['shift_no'])) {
             #shift_no has been added in messsage
            $msg = $data['shift_no']. ' has been updated successfully.';
            # removing any access level locks taken by the admin user
            $shift_lock_res = $this->remove_shift_lock($data['id'], $adminId);
            if($shift_lock_res['status'] == false)
                return $shift_lock_res;
        } else {
            #shift_no has been added in messsage
            $shift_no=substr("ST000000000",0,11-strlen($shift_id)).$shift_id;
            $msg = $shift_no. ' has been created successfully.';
        }
        $response = ['status' => true, 'msg' => $msg, 'id' => $shift_id];
        return $response;
    }

    /**
     * checks if any accepted shifts of member is falling between shift and its timings
     */
    function validate_unique_account_shifts($shift_id, $account_type, $account_id, $start_datetime, $end_datetime) {
        $start_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
        $end_datetime = date('Y-m-d H:i:s', strtotime($end_datetime));
        $select_column = ["s.shift_no", "s.scheduled_start_datetime", "s.scheduled_end_datetime"];
        $this->db->select($select_column);
        $this->db->from('tbl_shift as s');
        $this->db->where('s.archive', 0);
        if($shift_id)
            $this->db->where('s.id != '.$shift_id);
        $this->db->where('s.account_type = '.$account_type.' and s.account_id = '.$account_id);
        $this->db->where("((s.scheduled_start_datetime >= '{$start_datetime}' and s.scheduled_start_datetime < '{$end_datetime}') or (s.scheduled_end_datetime > '{$start_datetime}' and s.scheduled_end_datetime <= '{$end_datetime}') or (s.scheduled_start_datetime <= '{$start_datetime}' and s.scheduled_end_datetime >= '{$end_datetime}'))");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if(!empty($result)) {
            $found_shifts = [];
            foreach ($result as $row) {
                $found_shifts[] = $row->shift_no. " (".date('d/m/Y h:i A', strtotime($row->scheduled_start_datetime))." to ".date('d/m/Y h:i A', strtotime($row->scheduled_end_datetime)).")";
            }
            return ["status" => false, "error" => "Scheduled timing is overlapping with timing of shift:".implode(", ",$found_shifts), "account_shift_overlap" => true];
        }
        return ["status" => true, "msg" => "shift is ok"];
    }

    /**
     * clone a shift record validation
     */
    function clone_shift_validation($data, $adminId) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $shift_id = $data['id'] ?? 0;
        $account_id = $data['account_id'] ?? 0;
        $account_type = $data['account_type'] ?? 0;

        # appending timings into date fields
        $data['scheduled_start_datetime'] = $data['scheduled_start_date']." ".$data['scheduled_start_time'];
        $data['scheduled_end_datetime'] = $data['scheduled_end_date']." ".$data['scheduled_end_time'];

        if($data['actual_start_date'] || $data['actual_start_time'])
        $data['actual_start_datetime'] = $data['actual_start_date']." ".$data['actual_start_time'];
        else
        $data['actual_start_datetime'] = '';

        if($data['actual_end_date'] || $data['actual_end_time'])
        $data['actual_end_datetime'] = $data['actual_end_date']." ".$data['actual_end_time'];
        else
        $data['actual_end_datetime'] = '';

        # validation rule
        $validation_rules = [
            array('field' => 'shift_no', 'label' => 'Shift No', 'rules' => 'required'),
            array('field' => 'account_id', 'label' => 'Account', 'rules' => 'required'),
            array('field' => 'account_type', 'label' => 'Account Type', 'rules' => 'required'),
            array('field' => 'role_id', 'label' => 'Service Type', 'rules' => 'required'),
            array('field' => 'status', 'label' => 'Status', 'rules' => 'required'),
            array(
                'field' => 'scheduled_start_datetime', 'label' => 'Scheduled start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect scheduled start date & time',
                ]
            ),
            array(
                'field' => 'scheduled_end_datetime', 'label' => 'Scheduled end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect scheduled end date & time',
                ]
                ),
                array('field' => 'contact_id', 'label' => 'Contact', 'rules' => 'required'),
        ];

        # checking actual start date & time and end date & time
        # if any of those is provided, all need to be validated
        if (!empty($data['actual_start_date']) || !empty($data['actual_start_time']) || !empty($data['actual_end_date']) || !empty($data['actual_end_time'])) {
            $validation_rules[] = array(
                'field' => 'actual_start_datetime', 'label' => 'Actual start date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect actual start date & time',
                ]
            );
            $validation_rules[] = array(
                'field' => 'actual_end_datetime', 'label' => 'Actual end date & time', 'rules' => 'required|valid_date_format[Y-m-d h:i A]',
                'errors' => [
                    'valid_date_format' => 'Incorrect actual end date & time',
                ]
            );
        }

        # checking optional numeric fields
        if(!empty($data['scheduled_travel']))
            $validation_rules[] = array('field' => 'scheduled_travel', 'label' => 'Scheduled travel', 'rules' => 'required|numeric');
        if(!empty($data['actual_travel']))
            $validation_rules[] = array('field' => 'actual_travel', 'label' => 'Actual travel', 'rules' => 'required|numeric');
        if(!empty($data['scheduled_reimbursement']))
            $validation_rules[] = array('field' => 'scheduled_reimbursement', 'label' => 'Scheduled Reimbursement', 'rules' => 'required|numeric');
        if(!empty($data['actual_reimbursement']))
            $validation_rules[] = array('field' => 'actual_reimbursement', 'label' => 'Actual Reimbursement', 'rules' => 'required|numeric');

        # set data in libray for validate
        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        # checking the valid scheduled shift date range
        # checking break entries and validating them
        $valid_dates = $this->validate_shift_and_breaks_dates($data,isset($data['scheduled_rows'])?$data['scheduled_rows']:null,$data['scheduled_start_datetime'], $data['scheduled_end_datetime'],1, null, true);
        if($valid_dates && $valid_dates['status'] == false)
            return $valid_dates;
        else if($valid_dates && $valid_dates['status'] == true)
            $data['scheduled_rows'] = $valid_dates['data'];

        # checking the valid actual shift date range
        # checking break entries and validating them
        $valid_dates = $this->validate_shift_and_breaks_dates($data,isset($data['actual_rows'])?$data['actual_rows']:null,$data['actual_start_datetime'], $data['actual_end_datetime'],2, null, true);
        if($valid_dates && $valid_dates['status'] == false)
            return $valid_dates;
        else if($valid_dates && $valid_dates['status'] == true)
            $data['actual_rows'] = $valid_dates['data'];

        $msg = 'Shift has been validated successfully.';

        $response = ['status' => true, 'msg' => $msg, 'id' => 0];
        return $response;
    }

    /**
     * checking if a shift is changing to "scheduled" and does it have only one member
     * if so, auto accepting the shift for that one member
     */
    function check_shift_members_and_accept($shift_id) {
        $srch_para = [
            "shift_id" => $shift_id,
            "pageSize" => 1
        ];

        # not proceeding if the shift itself has member already assigned
        $result = $this->get_shift_details($shift_id);
        if(!empty($result) && !empty($result['data']) && $result['data']->accepted_shift_member_id > 0
        && $result['data']->is_restricted!=1)
        {
            return ['status' => true];
        }
       

        $is_one_member = $this->get_shift_member_list((object) $srch_para);
        if(empty($is_one_member) || empty($is_one_member['count']) || $is_one_member['count'] != 1) {
            $response = ['status' => false, 'error' => "Please assign only one registered member to schedule this shift"];
            return $response;
        }

        # checking member availability using unavailability provided, overtime rules and shifts distance rules
        $member_id = $is_one_member['data'][0]->member_id;
        $member_unavailable_det = $this->is_member_available_between_datetimes($shift_id, $member_id, $result['data']->scheduled_start_datetime, $result['data']->scheduled_end_datetime, $result['data']->owner_address);
        if($member_unavailable_det && $member_unavailable_det['status'] == false) {
            return $member_unavailable_det;
        }

        # mark that one registered member as accepted the shift
        $one_member_data = (array)$is_one_member['data'][0];
        $one_member_data['status'] = 1;
        $accept_response = $this->accept_reject_shift($one_member_data);
        return $accept_response;
    }

    /**
     * Updating the shift status.
     */
    function update_shift_status($data, $adminId,$uuid_user_type=ADMIN_PORTAL, $from_admin = false) {
        $id = isset($data['id']) ? $data['id'] : 0;
        $PreventionTime=10;
        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }
        # does the shift exist?
        $result = $this->get_shift_details($data['id']);
        
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }
        $shift_details = $result['data'] ?? [];

        # is the object being altered currently by other user? if yes - cannot perform this action
        $lock_taken = $this->add_shift_lock($id, $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;

        # if shift is getting started/in-progress, make sure it is the same day of the shift start date
        if($data['status'] == 4) {
            $shift_start_date = date("d/m/Y", strtotime($result['data']->scheduled_start_datetime));
            $today = date("d/m/Y");

            # does the shift start date & time passed?
            # there is a possibility of member not refreshing the page for while and it gets passed
            $valid_shift_date = check_dates_lower_to_other(DATE_TIME, $result['data']->scheduled_start_datetime, false);

            $cur_str_time = strtotime(DATE_TIME);
            $start_str_time = strtotime($result['data']->scheduled_start_datetime);
            $end_str_time = strtotime($result['data']->scheduled_end_datetime);
            $early_start_str_time = $start_str_time - 60 * $PreventionTime;

            $is_early_start = FALSE;
            # Allow user to start the shift before 10 mins       
            if($cur_str_time >= $early_start_str_time && $early_start_str_time <= $start_str_time &&
                $cur_str_time <= $start_str_time && $cur_str_time < $end_str_time) {
                $is_early_start = TRUE;
            }# Start the shift on scheduled time. show error msg if started before
            else if($cur_str_time <= $start_str_time && $cur_str_time < $end_str_time) {
                return [
                    "status" => false,
                    "error" => "Start shift on or before 10 mins from scheduled time"
                ];
            }

            # Shift end time has passed away. show error msg if scheduled end time is passed
            if($cur_str_time >= $start_str_time && $cur_str_time >= $end_str_time) {
                $response = [
                    "status" => false,
                    "error" => "Scheduled end date-time: ".date("d/m/Y h:i A", strtotime($result['data']->scheduled_end_datetime))." of the shift has already passed"
                ];
                return $response;
            }

            if($shift_start_date !== $today) {
                # remove shift lock
                $shift_lock_res = $this->remove_shift_lock($id, $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                return ['status' => false, 'error' => "Start shift on the mentioned date"];
            }

            /** if shift begins earlier than 15 mins or later than scheduled time actual will be defaulted to
             * scheduled
             */           
             if(!$result['data']->actual_start_datetime||!$result['data']->actual_end_datetime)
             {
                $upd_data['actual_start_datetime'] = date("Y-m-d H:i:s", strtotime($result['data']->scheduled_start_datetime));
                $upd_data['actual_end_datetime']   = date("Y-m-d H:i:s", strtotime($result['data']->scheduled_end_datetime));
                $upd_data['actual_duration']       = $result['data']->scheduled_duration;
                if (isset($result['data']->account_id) && isset( $result['data']->account_type) &&  $result['data']->account_type == 1 &&$result['data']->rolelabel=='NDIS') {
                    $res_data = (array)$result['data'];
                    $resp= $this->setLineItemsFromDefaults($res_data,$adminId, $uuid_user_type);
                }
             }     
        }
        # if shift is completed then record the date time
        if($data['status'] == 5) {

            # if calling from admin portal and no actual start/end datetime provided then
            # returning with a validation message
            if(empty($shift_details->actual_start_datetime) || empty($shift_details->actual_end_datetime)) {
                $response = ['status' => false, 'error' => "Please provide shift's actual start & end timings"];
                return $response;
            }

            # only auto add the actual end date&time if not provided earlier
            if(empty($result['data']->actual_end_datetime))
            $upd_data['actual_end_datetime'] = date("Y-m-d H:i:s");

            # creating a timesheet for member who completed this shift
            # also creating an invoice for the account (participant or org)
            $shift_complete = $this->create_shift_timesheet_invoice($data['id'], $adminId);
            if($shift_complete['status'] == false) {
                # remove shift lock
                $shift_lock_res = $this->remove_shift_lock($id, $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                return $shift_complete;
            }
        }
        # if status is getting changed to scheduled, does it have only one member assigned?
        if($data['status'] == 3) {
            $accept_response = $this->check_shift_members_and_accept($data['id']);
            if($accept_response['status'] == false) {
                # remove shift lock
                $shift_lock_res = $this->remove_shift_lock($id, $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                return $accept_response;
            }
        }
        # if status is getting changed to published, unassign the member who accepted the shift
        else if(($data['status'] == 2 || $data['status'] == 1) && $result['data']->accepted_shift_member_id > 0) {
            # updating status field in the shift member table for accepted member
            $upd_data2["status"] = 0;
            $result2 = $this->basic_model->update_records("shift_member", $upd_data2, ["id" => $result['data']->accepted_shift_member_id]);
            if (!$result2) {
                # remove shift lock
                $shift_lock_res = $this->remove_shift_lock($id, $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                return $response;
            }

            # setting shift table to available
            $upd_data['accepted_shift_member_id'] = null;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["status"] = $data['status'];
        $upd_data["cancel_reason_id"] = (isset($data['reason_drop']) && $data['reason_drop'] > 0) ? $data['reason_drop'] : null;
        $upd_data["cancel_notes"] = isset($data['reason_note']) ? $data['reason_note'] : null;
        $result = $this->basic_model->update_records("shift", $upd_data, ["id" => $id]);

         //Create Notification to members if shift is published
        if($data['status'] == 2) {
            $this->create_shift_publish_notification($shift_details);
        }

        //Dispatch the event
        $this->load->library('EventDispatcher');
        $this->eventdispatcher->dispatch('onAfterShiftUpdated', $id, (array) $upd_data, $shift_details);

        # adding a log entry
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        $this->loges->setTitle(sprintf("Successfully updated shift status with ID of %s by %s", $data['id'], $adminName));
        $this->loges->setSpecific_title(sprintf("Successfully updated shift status with ID of %s by %s", $data['id'], $adminName));  // set title in log
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($data['id']);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        # remove shift lock
        $shift_lock_res = $this->remove_shift_lock($id, $adminId);
        if($shift_lock_res['status'] == false)
        {
            return $shift_lock_res;
        }
        $msg = "Successfully updated shift status";
        if($data['status'] == 4)
        {
            $this->basic_model->update_records("shift",["shift_start_action_datetime" => date('Y-m-d H:i:s')],["id" => $data['id']]);
              $msg = "Shift started successfully";
        }
        else if($data['status'] == 5)
        {
            $this->basic_model->update_records("shift",["shift_end_action_datetime" => date('Y-m-d H:i:s')],["id" => $data['id']]);
            $msg = "Shift completed successfully";
        }
  

        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
     * fetching shifts list based on the searched keyword
     */
    public function get_shift_name_search($shift_keyword = '') {
        $this->db->like('s.shift_no', $shift_keyword);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["s.shift_no as label", 's.id as value', 'm.id as member_id', 'm.fullname as member_label']);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id and s.id = asm.shift_id', 'inner');
        $this->db->join('tbl_member m', 'm.id = asm.member_id', 'inner');
        $this->db->where("m.archive", "0");
        $this->db->where("s.archive", "0");
        $this->db->where("asm.archive", "0");
        $this->db->where("s.status", "5");
        $this->db->having($queryHaving);
        $sql = $this->db->get_compiled_select();
        $query = $this->db->query($sql);
        return $result = $query->result();
    }

    /**
     * adding shift level lock
     */
    function add_shift_lock($shift_id, $adminId) {
        $lock_data['object_type'] = "shift";
        $lock_data['object_id'] = $shift_id;
        $lock_taken = $this->Common_model->get_take_access_lock($lock_data, $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;
        else
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * removing shift level edit lock
     */
    function remove_shift_lock($shift_id, $adminId) {
        # removing any access level locks taken by the admin user
        $remove_lock_data['object_type'] = "shift";
        $remove_lock_data['object_id'] = $shift_id;
        $lock_taken = $this->Common_model->remove_access_lock($remove_lock_data, $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;
        else
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * creates a timesheet record for shift & member combination who performed and completed
     * the shift. also creating an invoice for the account (participant or org)
     */
    public function create_shift_timesheet_invoice($shift_id, $adminId) {

        # does the shift exist?
        $result = $this->get_shift_details($shift_id);

        if (empty($result)) {
            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }
        $shift_details = (array) $result['data'];
        $shift_member_id = $shift_details['accepted_shift_member_id'];

        # finding the shift member details
        $smresult = $this->get_shift_member_details($shift_member_id, $shift_id);
        if (empty($smresult) || $smresult['status'] != true) {
            $response = ['status' => false, 'error' => "Shift member does not exist anymore."];
            return $response;
        }
        $shift_member_details = (array) $smresult['data'];

        # fetching timsheet information for a given shift
        $this->load->model('finance/Finance_model');
        $timesheet = $this->Finance_model->get_timesheet_details($id = null, $shift_id);
        $timesheet_id = null;
        if (isset($timesheet['data']) && !empty($timesheet['data'])) {
            $data['id'] = $timesheet['data']->id;
            $data['timesheet_no'] = $timesheet['data']->timesheet_no;
            $timesheet_id = $data['id'];
        }
        else {
            $next_timesheet_det = $this->Finance_model->get_next_timesheet_no();
            $data['timesheet_no'] = $next_timesheet_det['data'];
        }

        # making sure timsheet status is approved if actual shift timings are exactly the sheduled timings
        $timings_updated = false;
        if(($shift_details['actual_start_datetime'] != $shift_details['scheduled_start_datetime']) ||
        ($shift_details['actual_end_datetime'] != $shift_details['scheduled_end_datetime'])
        ) {
            $timings_updated = true;
        }
        else if(count($shift_details['actual_rows']) != count($shift_details['scheduled_rows'])) {
            $timings_updated = true;
        }
        else if(!empty($shift_details['actual_rows']) && !empty($shift_details['scheduled_rows'])) {
            $scheduled_rows = $shift_details['scheduled_rows'];
            $actual_rows = $shift_details['actual_rows'];
            for($i=0;$i<count($shift_details['scheduled_rows']);$i++) {
                if($scheduled_rows[$i]['break_duration'] != $actual_rows[$i]['break_duration']) {
                    $timings_updated = true;
                }
            }
        }

        # calculating the timesheet line items based on shift's actual timings
        list($timesheet_total, $timesheet_line_items) = $this->calc_timesheet_line_items($timesheet_id, $shift_member_details, $shift_details);

        # creating/updating the timesheet
        $data['shift_id'] = $shift_id;
        $data['member_id'] = $shift_member_details['member_id'];
        $data['amount'] = $timesheet_total;
        $data['timesheet_line_items'] = $timesheet_line_items;
        
        if(isset($this->session->is_exclude_ot)) {
            $data['is_exclude_ot'] = $this->session->is_exclude_ot;
        }
        if(isset($this->session->overtime_15_tot)) {
            $data['ot_15_total'] = $this->session->overtime_15_tot; 
        }
        if(isset($this->session->overtime_20_tot)) {
            $data['ot_20_total'] = $this->session->overtime_20_tot;            
        }

        #Unset timesheet session
        $this->remove_timesheet_calc_session();
       
        if($timings_updated)
            $data['status'] = 1;
        else
            $data['status'] = 2;
        return $this->Finance_model->create_update_timesheet($data, $adminId);
    }

    /**
     * getting timesheet line items information of the completed shift
     */
    public function calc_timesheet_line_items($timesheet_id, $shift_member_details, $shift_details) {

        # calculating completed shift's line items
        $line_items = $this->calc_shift_line_items($shift_member_details, $shift_details, $timesheet_id);        
        # fetching the associated reference ids of all pay rate and charge rate categories
        $this->load->model('finance/Finance_model');
        $existing_timesheet_line_items = $this->Finance_model->get_timesheet_line_item_ids($timesheet_id, $key_pair = true);
        $cat_ids = $this->Finance_model->get_pay_rate_ref_list("payrates_category", true);

        # finding all active work types (roles) of member from current shift's work type
        $category_payrates = null;
        if($shift_details['role_id']) {
            $this->load->model('member/Member_model');
            $member_roles = $this->Member_model->get_member_active_roles($shift_member_details['member_id'], $shift_details['role_id'], date("Y-m-d"));

            # finding the pay rates information using the role details of member
            if($member_roles) {
                $category_payrates = $this->Finance_model->get_payrates_from_role_details($shift_details['award_type_id'], $shift_details['role_id'], date("Y-m-d"), $member_roles);
            }
        }

        # going through all line items found and attaching number of units and unit price to it
        $timesheet_total = 0;
        $timesheet_line_items = null;
        $order = 1;
        foreach($line_items as $line_item => $units) {

            # finding active pay rate associated with shift work type + member role (work type)
            $unit_rate = 0.00;
            $external_reference = '';
            if(isset($category_payrates[$line_item])) {
                list($unit_rate, $external_reference) = $category_payrates[$line_item];
            }
            
            # Override the below line item unit rate values
            if($line_item == 'first_aid' || $line_item == 'so_weekday' || $line_item == 'so_weekend'
                     || $line_item == 'mileage' || $line_item == 'mileage_taxable' || $line_item == 'personal_expenses') { 
                        $item = $this->get_pay_rates_amount_by_cat_name($line_item, $member_roles);
                        if(!empty($item)) {
                            list($unit_rate, $external_reference) = $item[$line_item];    
                        } else {
                            list($unit_rate, $external_reference) = [0.00, NULL];
                        }           
            } 
                
            $total_cost = round($units * $unit_rate, 2);            

            $timesheet_total += $total_cost;
            $timesheet_line_items[] = [
                'id' => isset($existing_timesheet_line_items[$line_item])?$existing_timesheet_line_items[$line_item]:null,
                'key' => $line_item,
                'category_id' => $cat_ids[$line_item],
                'units' => $units,
                'unit_rate' => $unit_rate,
                'total_cost' => $total_cost,
                'external_reference' => $external_reference,
                'order' => $order++
            ];
        }
        return [$timesheet_total, $timesheet_line_items];
    }

    /**
     * Get pay rates amount using payrates category name
     * @param $cat_name {string}- keyname example - first_aid/shift_allowance/so_weekday etc
     * @param $member_roles {array} member roles which is assigned against member page
     * @param $alt_return {bool} true/false
     * 
     * @return $rows {array} list of values of amount and external reference
     */
    public function get_pay_rates_amount_by_cat_name($cat_name, $member_roles, $alt_return = false) {
        
        $select_column = ["pr.id", "pr.pay_rate_category_id", "ref.key_name as line_item_key", "pr.amount", "pr.external_reference"];
        $this->db->select($select_column);
        $this->db->from('tbl_finance_pay_rate as pr');    
        $this->db->join('tbl_references as ref', "ref.id = pr.pay_rate_category_id and ref.archive = 0 and ref.key_name = '". $cat_name ."'", 'inner');
        $this->db->where("pr.archive", "0");
        if(!empty($member_roles)) {
            $this->db->group_start();
            foreach($member_roles as $role_info) {
                $this->db->or_where("pr.employment_type_id = " . $role_info['employment_type']);
            }
            $this->db->group_end();
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        $rows = [];
            
        if($result) {
            foreach ($result as $row) {
                if($alt_return)
                    $rows[$row->pay_rate_category_id] = $row->external_reference;
                else
                    $rows[$row->line_item_key] = [$row->amount, $row->external_reference];
            }
        }
        
        return $rows;
    }

    /**
     * calculating the shift line items based on shift's actual timings
     */
    public function calc_shift_line_items($shift_member_details, $shift_details, $timesheet_id = NULL) {

        # preparing checks data
        $startdatetime = $shift_details['actual_start_datetime'];
        $enddatetime = $shift_details['actual_end_datetime'];
        $midnight_start = date("Y-m-d 00:00:00", strtotime($startdatetime));
        $sixam_start = date("Y-m-d 06:00:00", strtotime($startdatetime));
        $midnight_end = date("Y-m-d 00:00:00", strtotime($enddatetime));
        $sixam_end = date("Y-m-d 06:00:00", strtotime($enddatetime));
        $eightpm_end = date("Y-m-d 20:00:00", strtotime($enddatetime));
        $midnight_end2 = date("Y-m-d 23:59:59", strtotime($enddatetime));

        $monday_start = date("Y-m-d 00:00:00", strtotime('monday this week', strtotime($startdatetime)));
        $friday_end = date("Y-m-d 23:59:59", strtotime('friday this week', strtotime($startdatetime)));
        $saturday_start = date("Y-m-d 00:00:00", strtotime('saturday this week', strtotime($startdatetime)));
        $sunday_end = date("Y-m-d 23:59:59", strtotime('sunday this week', strtotime($startdatetime)));
        $weekday_range = check_dates_between_two_dates($monday_start, $friday_end, $startdatetime);
        $weekday_range_end = check_dates_between_two_dates($monday_start, $friday_end, $enddatetime);
        $weekend_range = check_dates_between_two_dates($saturday_start, $sunday_end, $startdatetime);
        $weekend_range_end = check_dates_between_two_dates($saturday_start, $sunday_end, $enddatetime);
        
        # calculating total shift hours and individual break hours based on break category
        $shift_hours_details = $this->calc_total_shift_ind_break_hours_w_holiday($shift_details, 2);
        list($total_shift_hours, $total_mins, $total_sleepover_minutes, $total_unpaid_minutes, $total_paid_minutes, $weekday_minutes, $saturday_minutes, $sunday_minutes, $publicholiday_mintues, $day_array) = $shift_hours_details;
        $weekend_minutes = $saturday_minutes + $sunday_minutes;
    
        $shift_day1 = $shift_day2 = null;
        $prevnext_day_shift = [];
        $single_day_shift = TRUE;
        #Get day is weekday/saturday/sunday/public holiday
        if(!empty($day_array) && count($day_array) == 2) {
            $shift_day1 = $day_array[0]['day_of_week'];
            $shift_day2 = $day_array[1]['day_of_week'];
            $single_day_shift = FALSE;
        }
        # BR-4, calculating Mon-Fri line item and its units
        $mon_fri = 0;
        if($weekday_minutes > 0) {
            $mon_fri = round(($weekday_minutes / 60),2);
        }
        
        # BR-16, if shift runs on saturday, adding saturday line item
        $saturday = 0;
        if($saturday_minutes > 0) {
            $saturday = round(($saturday_minutes / 60),2);
        }

        # BR-17, if shift runs on sunday, adding sunday line item
        $sunday = 0;
        if($sunday_minutes > 0) {
            $sunday = round(($sunday_minutes / 60),2);
        }
        
        # BR-25, calculating public holiday line item and its units
        $public_holiday = 0;
        if($publicholiday_mintues > 0) {
            $public_holiday = round(($publicholiday_mintues / 60),2);
        }
        
        # BR-24, if s/o break is provided and active work period before is not 1 hour or active work period before/after is not 4 hours then adding remaining hours in before/after days
        if($total_sleepover_minutes > 0 ) {
            $valid_so_work = $this->validate_so_shift_active_work($startdatetime, $enddatetime, $shift_details['actual_rows'], true);
        
            if($valid_so_work['status'] == false) {
                list($before_after_missing, $before_missing, $active_hours_before, $active_hours_after) = $valid_so_work['error'];
                /** Adjust the sleep over active hours if Day 1 = 0 & Day 2 > 0 or Day > 1 and Day 2 = 0 **/
                if(count($day_array) == 2) {
                     #get value by presitance
                    $sdPresitance = $this->presitence_day_rule[$shift_day1] ? $this->presitence_day_rule[$shift_day1] : NULL;
                    $edPresitance = $this->presitence_day_rule[$shift_day2] ? $this->presitence_day_rule[$shift_day2] : NULL;

                    $day1 = $shift_day1 == 'weekday' ? 'mon_fri' : $shift_day1;
                    $day2 = $shift_day2 == 'weekday' ? 'mon_fri' : $shift_day2;
                    
                    # If day 1 active hours is 0 and day 2 active hours is greater than 0
                    if($active_hours_before == 0 && $active_hours_after > 0) { 
                        if ($sdPresitance < $edPresitance) {
                            ${$day1} = 1; #1:4 combination
                            ${$day2} = ($active_hours_after > 4) ? $active_hours_after : 4;
                        } else if($day1 == 'public_holiday' && $day2 == 'public_holiday' && $public_holiday > 0) { 
                            $public_holiday = ($public_holiday < 4) ? 5 : $public_holiday;
                        }
                         else { #4:day2 hrs combination
                            ${$day1} = ($active_hours_after < 4) ? 4 : 1;
                        }    
                        
                    }# If day 2 active hours is 0 and day 1 active hours is greater than 0 
                    else if($active_hours_after == 0 && $active_hours_before > 0) {
                        if($sdPresitance < $edPresitance) {
                            ${$day2} = 4;
                        } else if($day1 == 'public_holiday' && $day2 == 'public_holiday' && $public_holiday >  0) {
                            $public_holiday = ($public_holiday < 4) ? 5 : $public_holiday;
                        }
                        else {
                            ${$day1} = ($active_hours_before > 4) ? $active_hours_before : 4;
                           
                        }  
                    }
                    
                }
                
                # for next 3 blocks, first day is given 1 hour if it is missing and 4 hour in later day
                if($sunday && $mon_fri) { 
                    if($active_hours_before < 4 && $active_hours_after < 4)
                        $sunday = 4;
                    else if($active_hours_before < 1)
                        $sunday = 1;
                    if($active_hours_after < 1)
                        $mon_fri = 1;                   
                }
                else if($shift_day2 == 'public_holiday' && $sunday && $public_holiday) { 
                    if($before_missing > 0)
                        $sunday = 1;

                    if($before_after_missing > 0)
                        $public_holiday = 4;
                }
                else if($mon_fri && $saturday) {
                    if($before_missing > 0)
                        $mon_fri = 1;

                    if($before_after_missing > 0)
                        $saturday = 4;
                }
                else if($saturday && $sunday || ($shift_day2 == 'public_holiday' && $saturday && $public_holiday)) {
                    if($before_missing > 0) {
                        $saturday = $active_hours_before = 1;
                    }

                    if($before_after_missing > 0) {
                        if($public_holiday) {
                            $public_holiday = 4; 
                        } else {
                            $sunday = 4;
                        }
                    }
                }
                # for s/o only in mon-fri, need to make sure 4/5 hours are minimum
                else if($mon_fri) {                    
                    
                    if($shift_day1 == 'public_holiday') {                        
                        if($active_hours_before < 4 && $active_hours_after < 4
                        && $public_holiday < 4) {
                            $public_holiday = 4;
                            $active_hours_after = ($active_hours_after < 1) ? 1 : $active_hours_after;
                        } elseif($active_hours_after >= 4) {
                            $public_holiday =  $active_hours_before = ($active_hours_before < 1) ? 1 : $active_hours_before;
                        }
                    }
                    else if($shift_day2 == 'public_holiday') {
                        if($active_hours_before < 4 && $active_hours_after < 4) {
                            $public_holiday = 4;
                            $mon_fri = $active_hours_before = ($active_hours_before < 1) ? 1 : $active_hours_before;
                        }
                        else if($active_hours_after >= 4) {
                            $mon_fri =  $active_hours_before = ($active_hours_before < 1) ? 1 : $active_hours_before;
                        }
                    }

                    else if($before_missing > 0 && $mon_fri < 5 && ($public_holiday + $mon_fri) < 5 || $before_missing > 0 && $before_after_missing > 0)
                        $mon_fri = 5;
                    elseif(empty($before_missing) && $before_after_missing > 0 && $active_hours_after < 4) {
                        $mon_fri = $active_hours_before + 4;
                    }
                    else if($before_after_missing > 0 && $mon_fri < 4)
                        $mon_fri = 4;
                    elseif($before_missing > 0 && $active_hours_before < 1) {
                        if($shift_day1 == 'public_holiday') {
                            $public_holiday += 1 - $active_hours_before;
                        } else {
                            $mon_fri += $active_hours_before;
                        }
                    }                    
                }
                else if($shift_day1 == 'public_holiday' && ($saturday || $sunday)) { #PH and Saturday or PH and Sunday
                    $day2 = ($saturday) ? 'saturday' : 'sunday';
                    if($before_missing > 0 && $before_after_missing > 0) {
                        $public_holiday = 4;                        
                    }
                    else if($active_hours_before < 4 && $active_hours_after < 4) {
                        $public_holiday = 4;
                        ${$day2} = $active_hours_after = ($active_hours_after < 1) ? 1 : $active_hours_after;
                    }
                    else if($active_hours_after >= 4) {
                        $public_holiday = $active_hours_before = ($active_hours_before < 1) ? 1 : $active_hours_after;
                    }
                }
                if($shift_day1 == 'public_holiday' && $shift_day2 == 'public_holiday' && $public_holiday < 4) { 
                    $public_holiday = 5;        
                }
            }
            
            $weekend_minutes = ($saturday + $sunday) * 60;
            $total_shift_hours = ($mon_fri + $saturday + $sunday + $public_holiday) * 60;            
        }
        
        # BR-7, checking night duty line items and its units
        $night_duty_allowance = 0;
        $nightduty_start_range = check_dates_between_two_dates($midnight_end, $sixam_end, $startdatetime, true, true, false);
        $nightduty_end_range = check_dates_between_two_dates($midnight_end, $sixam_end, $enddatetime, true, false, true);
        $nightduty_whole_range = check_dates_between_two_dates($startdatetime, $enddatetime, $sixam_end, true, false, true);

        if(($nightduty_start_range || $nightduty_end_range || $nightduty_whole_range) && $total_sleepover_minutes == 0) {
            if ($public_holiday) {
                $night_allowance = $total_shift_hours - $publicholiday_mintues;
            } else {
                $night_allowance = $total_shift_hours;
            }
            $night_duty_allowance = round(($night_allowance / 60),2);
            
        }

        # BR9, checking shift allowance line items and its units
        $shift_allowance = 0;
        $roundoff_val = strtolower($shift_details['rolelabel']) == 'ndis' ? 2 : 3;
        
        $shift_allowance_end_range = check_dates_between_two_dates($eightpm_end, $midnight_end2, $enddatetime, true, false, true);
        if($shift_allowance_end_range && $public_holiday == 0) {
            #Adjust shift allowance line item based on the          
            $shift_hours = (!$weekend_minutes && round(($total_shift_hours / 60), 2) < $roundoff_val) ? $roundoff_val : round(($total_shift_hours / 60), 2);
            $shift_allowance = $shift_hours;            
        }

        # BR-1, calculating Sleepover weekday line items and its units
        # BR-2, calculating Sleepover weekend line items and its units
        # BR-8, checking shift allowance line items and its units
        $so_weekday = $so_weekend = 0;
        if($total_sleepover_minutes > 0 ) {
            # shift allowance only for week days for sleep over shifts
            if ($mon_fri > 0) {
                $shift_allowance = $mon_fri;
            }            

            if($weekday_range)
                $so_weekday = 1;
            if($weekend_range)
                $so_weekend = 1;
            
            //If starting of the shift/Single day shift falls public holiday then so_weekday will be so_weekend
            if($public_holiday && ($shift_day1 != NULL && $shift_day2 != NULL && $shift_day1 == 'public_holiday') ||
            $shift_day1 == NULL && $shift_day2 == NULL ) {
                $so_weekday = 0;
                $so_weekend = 1;
            }
        }

        # BR-14, if shift_allowance or night_duty_allowance allowance is applied
        # making sure we donot include any weekend hours/time
        if($weekend_minutes) {
            $weekend_hours = round(($weekend_minutes / 60),2);

            //Skip to reduce if friday is sleepover
            if($shift_allowance && !$so_weekday && !$mon_fri)
                $shift_allowance -= $weekend_hours;

            // if($night_duty_allowance)
            //     $night_duty_allowance -= $weekend_hours;
        }

        # BR-15, if any reimbursement is provided adding the same number of units as "personal_expenses"
        $personal_expenses = 0;
        if(isset($shift_details['actual_reimbursement']) && $shift_details['actual_reimbursement']) {
            $personal_expenses = $shift_details['actual_reimbursement'];
        }

        # if shift service type is ndis and total shift hours are less than 120 then any later day will be increased to 2 hours
        # if shift service type is not ndis and total shift hours are less than 180 then any later day will be increased to 3 hours
        # and no s/o in the shift
        $remaining_hour = 0;
        if((isset($shift_details['rolelabel']) && strtolower($shift_details['rolelabel']) == 'ndis' && $total_shift_hours < 120) || (isset($shift_details['rolelabel']) && strtolower($shift_details['rolelabel']) != 'ndis' && $total_shift_hours < 180)) {
           
            if (isset($shift_details['rolelabel']) && strtolower($shift_details['rolelabel']) == 'ndis') {
                $remaining_hour = round(((120 - $total_shift_hours) / 60),2);
            } else {
                $remaining_hour = round(((180 - $total_shift_hours) / 60),2);
            }

            if ($public_holiday) {
                $public_holiday = $public_holiday + $remaining_hour;
            } else if ($sunday) {
                $sunday = $sunday + $remaining_hour; 
            } else if($saturday) {
                $saturday = $saturday + $remaining_hour;
            } else if($mon_fri) {
                $mon_fri = $mon_fri + $remaining_hour;
            }
        }
        
        # BR-3, calculating First Aid line item and its units
        $first_aid = round(($total_shift_hours / 60),2);
        
        # BR-10, calculating Overtime 1.5 line item and its units
        # BR-11, calculating Overtime 2.0 line item and its units

        $overtime_15 = $overtime_20 = $overtime_15_tot = $overtime_20_tot = 0;
        $weeklyHExceedFlag = false;
        # fetching member's accepted shift hours and current shift hours total
        $member_weekly_hours = $this->check_shift_durations_of_member($shift_details['id'], $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true, true);        
        
        $total_weekly_hours = $used_weekly_hours = 0;
        if($member_weekly_hours && $member_weekly_hours['status'] == true) { 
           
            $total_weekly_hours = $member_weekly_hours['data'] + $remaining_hour;
            $used_weekly_hours = $member_weekly_hours['used_weekly_hours'];
        }
            
        # if all previous shifts within the week has already exceeded then everything should go into overtime 2.0               
        if ($total_weekly_hours > (WEEKLY_MAX_MEMBER_HOURS + 2) && $used_weekly_hours <= WEEKLY_MAX_MEMBER_HOURS) {
            $weeklyHExceedFlag = true;
            $overtime_15 = 2;
            $overtime_20 = round( $total_weekly_hours - (WEEKLY_MAX_MEMBER_HOURS+2),2);
        } else if ($total_weekly_hours > WEEKLY_MAX_MEMBER_HOURS && $total_weekly_hours <= (WEEKLY_MAX_MEMBER_HOURS + 2) && $used_weekly_hours <= WEEKLY_MAX_MEMBER_HOURS) {
            $weeklyHExceedFlag = true;
            $overtime_15 = round( $total_weekly_hours - WEEKLY_MAX_MEMBER_HOURS,2); # 1hour
            $overtime_20 = 0;
        } else if ($total_weekly_hours > WEEKLY_MAX_MEMBER_HOURS && $used_weekly_hours <= (WEEKLY_MAX_MEMBER_HOURS + 2)) {
            $weeklyHExceedFlag = true;
            $overtime_15 = round( (WEEKLY_MAX_MEMBER_HOURS+2) - $used_weekly_hours,2);
            $overtime_20 = round( $total_weekly_hours - (WEEKLY_MAX_MEMBER_HOURS+2),2);
        }                
        else if ($total_weekly_hours > (WEEKLY_MAX_MEMBER_HOURS + 2) && $used_weekly_hours > WEEKLY_MAX_MEMBER_HOURS + 2) {
            $weeklyHExceedFlag = true;
            #Used weekly hours and total weekly hours are greater than 40 assign current shift full active duration into 2.0                
            $overtime_15 = 0;
            $overtime_20 = round( $total_weekly_hours - $used_weekly_hours, 2);
        }

        if ($overtime_20 < 0) {
            $overtime_20 = 0;
        }
        
        #Unset existing timesheet session
        $this->remove_timesheet_calc_session();

        $is_interrupt_hr = (array_key_exists('actual_in_sleepover_rows', $shift_details)) ? TRUE : FALSE;
        $is_skip_shift_ot = FALSE;
        # caluculate the overtime with days for weekly exceeding 38 hrs w/o sleepover
        if (!$is_interrupt_hr && $weeklyHExceedFlag) {
            if($sunday || $public_holiday) {
                $is_skip_shift_ot = TRUE;
                $overtime_15_tot = $overtime_15;
                $overtime_20_tot = $overtime_20;
            }

            $weeklyExceedingHoursCalculation = $this->calculateWeeklyHoursExceedHour($total_weekly_hours, $used_weekly_hours, $shift_day1, $shift_day2, $mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20); 
            list($mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20) = $weeklyExceedingHoursCalculation;
           
            $this->session->is_exclude_ot = TRUE;
           
        }
        
        # calculating overtime 1.5 and overtime 2.0
        # only during weekdays and saturday (not on Sunday & phublic holiday)
        $overnight_shift = [];
        $overnight_shift['status'] = FALSE;
        

        if($is_interrupt_hr) {
            
            list($inter_day1_mins, $inter_day2_mins) = $this->get_day_spec_interrupt_so_mins($shift_details);
           
            list($first_aid, $mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20) = $this->get_interrupted_so_split_up($shift_details, $first_aid, $inter_day1_mins, $inter_day2_mins, $mon_fri, $saturday, $sunday, $public_holiday, $shift_day1, $shift_day2, 0, 0);

            # Set flag true for timesheet has interrupted S/O break or else interrupted OT hours will reduced actual hours           
            $this->session->is_exclude_ot = TRUE;
        }
        else if(($weekday_range || $saturday_minutes > 0 || $weekday_minutes > 0) && ($mon_fri !=0 || $saturday !=0 ) && !$weeklyHExceedFlag) { 
            # BR-5, calculating Overtime 1.5 line item and its units
            if($total_shift_hours > 600) {
                $overtime_15 = round((($total_shift_hours-600) / 60),2);
                if($overtime_15 > 2)
                    $overtime_15 = 2;
            }

            # BR-6, calculating Overtime 2.0 line item and its units
            if($total_shift_hours > 720) {
                $overtime_20 = round((($total_shift_hours-720) / 60),2);
            }

            # BR-19, calculating Overtime 2.0 line item and its units when shifts are on same day where overall span not exceeding 12 hours
            $same_day_shift = $this->check_shifts_ot_on_same_day($shift_details['id'], $shift_member_details['member_id'], $startdatetime, $enddatetime, TRUE);
            
            if($same_day_shift && $same_day_shift['status'] && !empty($same_day_shift['data'])) {                
                $overtime_15 = 0;
                $overtime_20 = round($same_day_shift['data']['ot2.0'] / 60, 2);
                #set $mon_fri or $saturday value
                //${$same_day_shift['data']['day_name']} = round($same_day_shift['data']['day_duration'] / 60, 2);
            }

            # BR-21, calculating Overtime 2.0 line item and its units when shifts are on same day with one of them overnight            
            $overnight_shift = $this->check_breaks_between_shifts_of_member($shift_details, $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true);
            
            if($overnight_shift && $overnight_shift['status'] && $overnight_shift['data'] != 'current_shift' && ($sunday || $public_holiday)) {
                #Calculate OT20 for weekly 38 hrs calculations
               $overtime_20_tot = $this->calc_sunday_ph_ot20($overnight_shift, $shift_details, $first_aid, $sunday, $public_holiday);
            }

            
            #Calculate OT for Weekday and saturday
            if($overnight_shift && $overnight_shift['status'] && !$sunday && !$public_holiday && !empty($overnight_shift['data']) && $overnight_shift['data'] !='current_shift' && 
            ($total_sleepover_minutes > 0 || $overnight_shift['is_full_ot'])) {                
                #Full Active hours will be OT 2.0
                $overtime_20 = ($first_aid < $roundoff_val) ? $roundoff_val : $first_aid;
                $overtime_15 = $mon_fri = $saturday = 0;
            }#Calculate OT for saturday/sunday or sunday/monday
            elseif($overnight_shift && $overnight_shift['status'] && $sunday && ($saturday || $mon_fri) &&
            $total_sleepover_minutes > 0 && !empty($overnight_shift['reference_date'])) {
                
                $day = $saturday > 0 ? 'saturday' : 'mon_fri';
                 
                $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($overnight_shift['reference_date'])));

                #Shift falls completly after over time span hours means full saturday as Overtime and sunday will be sunday
                if($shift_details['actual_start_datetime'] >= $ot_span_time || $overnight_shift['is_full_ot']) {
                    $overtime_20 = $saturday ? $saturday : $mon_fri;
                    ${$day} = 0;
                }

            }#Calculate OT for friday/saturday or sunday/monday or friday/PH or weekday/PH
            elseif($overnight_shift && $overnight_shift['status'] && ($mon_fri || $saturday) && $shift_day2 == 'public_holiday' &&
            $total_sleepover_minutes > 0 && !empty($overnight_shift['reference_date'])) {  
                
                $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($overnight_shift['reference_date'])));
                
                #Shift falls completly after over time span hours means only mon_fri as Overtime and PH will be PH
                if($shift_details['actual_start_datetime'] >= $ot_span_time || $overnight_shift['is_full_ot']) {
                    $overtime_20 = ($mon_fri) ? $mon_fri : $saturday;
                    $mon_fri = $saturday = 0;
                }
            }#Current shift is reference shift and total active hours is greater than 10 then find the single day OT calculation
            elseif($overnight_shift && $overnight_shift['status'] && !empty($overnight_shift['data']) && $overnight_shift['data'] == 'current_shift' && $first_aid > 10) {
                $overnight_shift['status'] = FALSE;
            }#Calculate OT for non PH
            else if($overnight_shift && $overnight_shift['status'] && !empty($overnight_shift['data'])) {
                $overtime_15 = 0;
                $overtime_20 = $overnight_shift['data'] == 'current_shift' ? 0 : $overnight_shift['data'];
                
                if($sunday && $mon_fri) {
                    #Full shift overtime
                    if($sunday + $mon_fri == $overtime_20) {
                        $overtime_20_tot = $sunday;
                    }
                    if($sunday + $mon_fri == $overtime_20 || $mon_fri == $overtime_20) {
                        $overtime_20 = $mon_fri;
                        $mon_fri = 0;
                    }else {
                        #Combination of mon_fri,sunday,OT 2.0               
                        $mon_fri = $first_aid - ($overtime_20 + $sunday);
                        $mon_fri = ($mon_fri > 0) ? $mon_fri : 0;
                        $overtime_20 = $first_aid - $sunday - $mon_fri;
                    }                   
                }
                else if($mon_fri && $saturday) {
                    //If complete shift time falls OT means $mon_fri + $saturday = $overtime_20 then reset the $mon_fri value
                    if($mon_fri + $saturday == $overtime_20) {
                        $mon_fri = $saturday = 0;                        
                    } else if($saturday == $overtime_20) {
                        #If saturday and 2.0 value is same then saturday will be OT
                        $saturday = 0;
                    }elseif (!empty($overnight_shift['reference_date']) && $overtime_20 > 0) {
                        #Calculate OT 2.0 except if Reference shift is current shift 
                        $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($overnight_shift['reference_date'])));
                
                        #Shift falls completly after over time span hours means only mon_fri as Overtime and PH will be PH
                        if($shift_details['actual_start_datetime'] >= $ot_span_time || $overnight_shift['is_full_ot']) {
                           $overtime_20 = ($mon_fri) ? $mon_fri : $saturday;
                           $mon_fri = $saturday = 0;
                        } else {              
                            $mins = minutesDifferenceBetweenDate($shift_details['actual_start_datetime'] , $ot_span_time);              
                            $mon_fri = round(( $mins/ 60), 2);
                            $saturday = 0;
                        }
                    }
                     else if($saturday != $overtime_20 && $overtime_20 > 0) {
                        #Reduce OT value from saturday if OT falls in saturday
                        $saturday = $saturday - $overtime_20;
                        #Reduce mon_fri value from mon_fri if OT falls in mon_fri
                        $mon_fri = $overtime_20 - $mon_fri;
                    } else if($overtime_20 > 0) {
                        $mon_fri = $overtime_20 - $mon_fri;
                        $saturday = 0;
                    }
                    
                }
                else if(!$mon_fri && $saturday && !$sunday && !$public_holiday) {
                    #If saturday and 2.0 value is same then saturday will be OT
                    if($saturday == $overtime_20) {
                        $saturday = 0;                        
                    } else {
                        $saturday = $saturday - $overtime_20;
                    }
                }
                else if($saturday && $sunday) {
                    //If complete shift time falls OT means then only saturday will be OT
                    if($sunday + $saturday == $overtime_20) {
                        $overtime_20 = $overtime_20 - $sunday;                       
                        $saturday = 0;
                    } else if($sunday == $overtime_20) {
                        #If sunday and 2.0 value is same then there is no OT
                        $sunday = $overtime_20;
                        $saturday = $overtime_20 = 0;
                    } else if($sunday != $overtime_20 && $overtime_20 > 0) {
                        #Reduce OT value from saturday if OT falls in saturday
                        $overtime_20 = $overtime_20 - $sunday;
                        $saturday = ($overtime_20 > 0) ? $saturday - $overtime_20 : $saturday;
                        
                    } else if($overtime_20 > 0) {                        
                        $sunday = $overtime_20 - $sunday;
                        $saturday = 0;
                    }
                    
                }
                else if($shift_day1 == 'public_holiday' && $shift_day2 == 'weekday') {
                    
                    if($public_holiday + $mon_fri == $overtime_20) {
                        $overtime_20_tot = $public_holiday;
                    }

                    if($public_holiday + $mon_fri == $overtime_20 || $mon_fri == $overtime_20) {                        
                        $overtime_20 = $mon_fri;
                        $mon_fri = 0;
                    }else if($mon_fri != $overtime_20) {
                        #Combination of mon_fri,sunday,OT 2.0
                        $mon_fri = $first_aid - ($overtime_20 + $public_holiday);
                        $mon_fri = ($mon_fri > 0) ? $mon_fri : 0;
                        $overtime_20 = $first_aid - $public_holiday - $mon_fri;                       
                    }
                }
                else if($shift_day1 == 'weekday' && $shift_day2 == 'public_holiday') {
                    
                    if($mon_fri + $public_holiday == $overtime_20) {
                        $overtime_20_tot = $public_holiday;
                        $overtime_20 = $mon_fri;
                        $mon_fri = 0;
                    }
                    else if($public_holiday == $overtime_20) {
                        $overtime_20_tot = $public_holiday;
                        $overtime_20 = 0;
                    } else if($public_holiday != $overtime_20  && $overtime_20 > 0) {
                         #Reduce OT value from saturday if OT falls in saturday
                         $overtime_20 = $overtime_20 - $public_holiday;
                         $mon_fri = ($overtime_20 > 0) ? $mon_fri - $overtime_20 : $mon_fri;
                    }
                    
                }
                else if($shift_day1 == 'public_holiday' && $shift_day2 == 'saturday') {

                        $saturday = $first_aid - ($overtime_20 + $public_holiday);
                        $saturday = ($saturday > 0) ? $saturday : 0;
                        $overtime_20 = $first_aid - $public_holiday - $saturday;
                }
                else if($shift_day1 == 'saturday' && $shift_day2 == 'public_holiday') {
                   
                    if($saturday + $public_holiday == $overtime_20) {
                        $overtime_20_tot = $public_holiday;
                        $overtime_20 = $overtime_20 - $public_holiday;                       
                        $saturday = 0;
                    } else if($public_holiday != $overtime_20 && $overtime_20 > 0) {
                        #Reduce OT value from saturday if OT falls in saturday
                        $overtime_20 = $overtime_20 - $public_holiday;
                        $saturday = ($overtime_20 > 0) ? $saturday - $overtime_20 : $saturday;                        
                    }
                }                
                else if($mon_fri) {
                    $mon_fri = $mon_fri - $overtime_20;
                }               
                
            }
        } #Save only overtime total for weekly 38 hrs calculation on single day Broken between shifts
        else if(!$weeklyHExceedFlag && $single_day_shift && ($sunday || $public_holiday)) {
            $single_day_overnight_shift = $this->check_breaks_between_shifts_of_member($shift_details, $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true);
            
            if($single_day_overnight_shift && $single_day_overnight_shift['status'] && $single_day_overnight_shift['data'] != 'current_shift') {
                $overtime_20_tot = $this->calc_sunday_ph_ot20($single_day_overnight_shift, $shift_details, $first_aid, $sunday, $public_holiday);
            }
        }
        
        $overtime_20_tot = ($overtime_20_tot < 0) ? 0 : $overtime_20_tot;

        $mon_fri_10hr_span_exceed = $mon_sat_12hr_span_exceed = $is_single_shift_ot = FALSE;
        /** 
         * BR-27, Single shift overtime calculation if shift falls > 10 Hrs
        */
        if(!$overnight_shift['status'] && !$weeklyHExceedFlag && !$is_interrupt_hr) { 
            $overtime_15 = ($overtime_15 < 0) ? 0 : $overtime_15;
            $overtime_20 = ($overtime_20 < 0) ? 0 : $overtime_20;
            $is_single_shift_ot = TRUE;
            
            #If shift fall single day with overtime then calculate OT total to skip OT hrs on Weekly 38 Hrs calculation
            if($first_aid > 10) {
                $overtime_15_tot = ($first_aid >= 12) ? 2 : $first_aid - 10;
                $overtime_20_tot = ($first_aid > 12) ? $first_aid - 12 : 0;
            }
            $single_day_overtime_calc = $this->single_day_overtime_calc($first_aid, $overtime_15, $overtime_20, $mon_fri, $saturday, $sunday, $public_holiday, $mon_fri_10hr_span_exceed, $mon_sat_12hr_span_exceed, $shift_day1, $shift_day2, $roundoff_val);
            
            list($mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20) = $single_day_overtime_calc;           
        }
        $preference_1 = false;
        $preference_2 = false;
        if($saturday && !$weeklyHExceedFlag  && !$is_interrupt_hr) { 
            $sat_hrs = 0;
            if ($total_weekly_hours > WEEKLY_MAX_MEMBER_HOURS) {
                $preference_1 = true;
                $sat_ordinary_hours = WEEKLY_MAX_MEMBER_HOURS - $used_weekly_hours;
                $total_overtime = $total_weekly_hours - WEEKLY_MAX_MEMBER_HOURS;
                // if member performs less than 38 hours e.g. 35 in week days
                if ($sat_ordinary_hours >= 0) {
                    //deduct ordinary hours from overtime
                    //$total_overtime = $total_overtime - $sat_ordinary_hours;
                    if ($total_overtime >= $roundoff_val) {
                        $sat_hrs = $sat_ordinary_hours + $roundoff_val;
                        $overtime_20 = $total_overtime - $roundoff_val;
                    } else {
                        $sat_hrs = $sat_ordinary_hours + $total_overtime;
                        $overtime_20 = 0;
                    }
                } else {
                    $sat_hrs = $roundoff_val + $sat_ordinary_hours;
                    $overtime_20 = ($total_overtime - $sat_hrs) + $sat_ordinary_hours;
                }

                if($saturday && $sunday) {
                    $sunday = 0;
                }
            } else {
                $sat_hrs = $saturday;
            }
           
            /** BR-30 
             * if current shift and previous shift day gaps less than 10 hrs and current shift is fall 
             * saturday means first two hour of the current shift falls saturday and remaining falls overtime 2.0
             */
            if((!$mon_sat_12hr_span_exceed && $saturday || ($saturday && $sunday)) && !$mon_fri && $total_weekly_hours <= (WEEKLY_MAX_MEMBER_HOURS)) {
                if(!empty($prevnext_day_shift['data']) && $prevnext_day_shift['data'] < NEXT_DAY_SHIFTS_GAP) {
                    $sat_hrs = $roundoff_val; 
                    $overtime_20 = $first_aid > $roundoff_val ? $first_aid - $roundoff_val : 0;
                    $preference_2 = true;
                }
                //Sleep over shift
                if($so_weekend == 1)
                {   
                    //Two friday and saturday shift current shift sleep over shift
                    if(!empty($prevnext_day_shift['data']) && $prevnext_day_shift['data'] < 8) {
                        $sat_hrs = $roundoff_val; 
                        $overtime_20 = $first_aid > $roundoff_val ? $first_aid - $roundoff_val : 0;
                        $sunday = 0;
                        $preference_2 = true;
                    } else {
                        $same_day_prev_shift = $this->check_shifts_of_member_on_prev_shift_same_day_sleepover($shift_details['id'], $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true, false);
                        //If previous shift falls same day and it has a sleep over then consider current shift as a overtime shift
                        if(!empty($same_day_prev_shift['data']) && $same_day_prev_shift['data'] < 8) {               
                            $sat_hrs = $roundoff_val; 
                            $overtime_20 = $first_aid > $roundoff_val ? $first_aid - $roundoff_val : 0;
                            $sunday = 0;
                            $preference_2 = true;
                        }
                    }
                } else {
                    # check sleep over if only saturday
                    if ($weekday_minutes > 0 && $saturday_minutes > 0) {
                        $check_sleepover = false;
                    } else {
                        $check_sleepover = true;
                    }

                    //Check prev shift falls with same day and it has sleepover break
                    $same_day_prev_shift = $this->check_shifts_of_member_on_prev_shift_same_day_sleepover($shift_details['id'], $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true, $check_sleepover);

                    //If previous shift falls same day and it has a sleep over then consider current shift as a overtime shift
                    if(!empty($same_day_prev_shift['data']) && $prevnext_day_shift['data'] < 8) {
                        $sat_hrs = $roundoff_val; 
                        $overtime_20 = $first_aid > $roundoff_val ? $first_aid - $roundoff_val : 0;
                        $sunday = 0;
                        $preference_2 = true;
                    }
                }
            }
            $saturday = $sat_hrs;
            if ($saturday < 0) {
                $saturday = 0;
            }            
           
        }
        
        # if saturday & sunday or saturday and public holiday overnight shift then check the preference 1 (WEEKLY_MAX_MEMBER_HOURS) & preference 2 (saturday 2.0) neads to be false, then calculate BR-19 & BR -18 Rule
        if(($saturday && $sunday || $saturday && $public_holiday) && !$preference_1 && !$preference_2 && !$mon_sat_12hr_span_exceed && !$weeklyHExceedFlag  && !$is_interrupt_hr) {
            //If same shift saturday exceed more than 12 hr
            if ($saturday >= 12) {
                $totOThrs = $saturday - 10;
                $saturday = 10;
                #if total OT hours exceed more than 2 means first 2 assign to 1.5 remaining 2.0
                if($totOThrs > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = ($totOThrs - $overtime_15 > 0)? $totOThrs - $overtime_15 : 0;
                } else {
                    $overtime_15 = $totOThrs;
                    $overtime_20 = 0;
                }            
            }
            //   else if(!$overnight_shift['status']) {
                // $sunday = $sunday - $overtime_15 - $overtime_20; 
                // $saturday = $saturday - $overtime_15 - $overtime_20;
            // }
            if($saturday < 0) {
                $saturday = 0;
            }            
            if($sunday < 0) {
                $sunday = 0;
            }
        }
        
        # BR-22, calculating Mileage, for first 2500 KMs in a given financial year
        # BR-23, calculating Mileage_Taxable, for more than 5000 KMs
        $mileage = $mileage_taxable = $total_travel = 0;
        if($shift_details['actual_travel']) {
            $shift_travel = $shift_details['actual_travel']; # 1500

            # finding the travel so far in current financial year
            $travel_details = $this->check_travel_kms_of_member($shift_details['id'], $shift_member_details['member_id'], $startdatetime, $enddatetime, true, true);
            if($travel_details && $travel_details['status'] == true && !empty($travel_details['data'])) {
                $total_travel = $travel_details['data']; #5500
            }
            $overall_travel = $shift_travel + $total_travel; # 7000

            if($total_travel >= MILEAGE_ALLOWED) {
                $mileage_taxable = $shift_travel;
            }
            else if($overall_travel >= MILEAGE_ALLOWED) {
                $mileage_taxable = ($overall_travel - MILEAGE_ALLOWED);
            }
            $mileage = $shift_travel - $mileage_taxable;
        }
        
        //Set the shift allowance and night duty allowance min value 2 for NDIS 3 for non NDIS all the scenarios
        if($first_aid < $roundoff_val && $first_aid > 0) {
            $first_aid = $roundoff_val;
        }

        #BR-30 Adjust the line items if less than 2 hrs
        if(!isset($total_weekly_hours) || $total_weekly_hours < WEEKLY_MAX_MEMBER_HOURS) {       
                      
            //Adjust monday to friday if over time 1.5 less than 2 for NDIS 3 for non NDIS
            if($overtime_15 < $roundoff_val && $overtime_15 > 0 && !$mon_fri_10hr_span_exceed && !$is_interrupt_hr && !$mon_sat_12hr_span_exceed) {
                $mon_fri = $first_aid - ($overtime_15 + $overtime_20);
            }            
        }
        
        if($mon_fri > 0 && $shift_allowance > 0) {
            $shift_allowance = $mon_fri;
        } else {
            $shift_allowance = 0;
        }
        if($mon_fri > 0 && $night_duty_allowance > 0) {
            $night_duty_allowance = $mon_fri;
        } else {
            $night_duty_allowance = 0;
        }

        if ($night_duty_allowance > 0 && $night_duty_allowance > $mon_fri) {
            $night_duty_allowance = $mon_fri;
        }
        
         /** Update the status if interrupted S/O and weekly exceed hour not available this will help to update incase if we update the existing shift completed status
          */
         if(!$is_interrupt_hr && !$weeklyHExceedFlag) {
            $this->session->is_exclude_ot = FALSE;
        }
        
        #If $overtime_15_tot and $overtime_15_tot replaced aleady with full OT 1.5 and OT 2.0 value means we have to skip to add again.
        if(!$is_single_shift_ot && !$is_skip_shift_ot) {
            
            $overtime_15 = ($overtime_15 < 0) ? 0 : $overtime_15;
            $overtime_20 = ($overtime_20 < 0) ? 0 : $overtime_20;

            $overtime_15_tot = ($overtime_15_tot < 0) ? 0 : $overtime_15_tot;
            $overtime_20_tot = ($overtime_20_tot < 0) ? 0 : $overtime_20_tot;

            $overtime_15_tot = $overtime_15_tot + $overtime_15;
            $overtime_20_tot = $overtime_20_tot + $overtime_20;
            
        }
        
        $this->session->overtime_15_tot = $overtime_15_tot;
        $this->session->overtime_20_tot = $overtime_20_tot;
        
        # mapping the business rule findings of units with the reference category id
        $line_items = [];
        if($sunday && $sunday > 0 && ($shift_day1 != 'saturday'))
            $line_items["sunday"] = $sunday;       
        if($mon_fri && $mon_fri > 0)
            $line_items["mon_fri"] = $mon_fri;       
        if($saturday && $saturday > 0)
            $line_items["saturday"] = $saturday;       
        if($overtime_15 && $overtime_15 > 0)
            $line_items["overtime_15"] = $overtime_15;
        if($overtime_20 && $overtime_20 > 0)
            $line_items["overtime_20"] = $overtime_20; 
        if($sunday && $sunday > 0 && ($shift_day1 == 'saturday'))
            $line_items["sunday"] = $sunday;             
        if($public_holiday && $public_holiday > 0)
            $line_items["public_holiday"] = $public_holiday;
        if($first_aid && $first_aid > 0)
            $line_items["first_aid"] = $first_aid;
        if($personal_expenses && $personal_expenses > 0)
            $line_items["personal_expenses"] = $personal_expenses;
        if($mileage && $mileage > 0)
            $line_items["mileage"] = $mileage;
        if($mileage_taxable && $mileage_taxable > 0)
            $line_items["mileage_taxable"] = $mileage_taxable;        
        if($night_duty_allowance && $night_duty_allowance > 0)
            $line_items["night_duty_allowance"] = $night_duty_allowance;
        if($shift_allowance && $shift_allowance > 0)
            $line_items["shift_allowance"] = $shift_allowance;
        if($so_weekday && $so_weekday > 0)
            $line_items["so_weekday"] = $so_weekday;
        if($so_weekend && $so_weekend > 0)
            $line_items["so_weekend"] = $so_weekend;        
       
        if(!$single_day_shift) {
            $line_items =  $this->Finance_model->reorder_lineitem_list($line_items, $shift_day1, $shift_day2);
        }
       
        return $line_items;
    }

    /**
     * adjusting saturday line items based on overtime calculated
     */
    public function adjust_saturday_line_items($saturday, $overtime_15) {
        if($overtime_15 > 0) {
            $saturday += $overtime_15;
            $overtime_15 = 0;
        }
        return [$saturday, $overtime_15];
    }

    /**
     * adjusting sunday line items based on overtime calculated
     */
    public function adjust_sunday_line_items($sunday, $overtime_15, $overtime_20) {
        if($overtime_15 > 0) {
            $sunday += $overtime_15;
            $overtime_15 = 0;
        }
        if($overtime_20 > 0) {
            $sunday += $overtime_20;
            $overtime_20 = 0;
        }
        return [$sunday, $overtime_15, $overtime_20];
    }

    /**
     * calculating total shift hours and individual break hours based on break category
     */
    public function calc_total_shift_ind_break_hours($data, $break_cat = 1) {

        if($break_cat == 2) {
            $break_rows = $data['actual_rows'];
            $start_datetime = $data['actual_start_datetime'];
            $end_datetime = $data['actual_end_datetime'];
        }
        else {
            $break_rows = $data['scheduled_rows'];
            $start_datetime = $data['scheduled_start_datetime'];
            $end_datetime = $data['scheduled_end_datetime'];
        }

        # calculating total shift durations in minutes
        $total_mins = minutesDifferenceBetweenDate($start_datetime, $end_datetime);
        
        # finding sleepover break reference id
        $total_sleepover_minutes = $total_unpaid_minutes = $total_paid_minutes = 0;
        $so_ref_id = $up_ref_id = $p_ref_id = $ins_ref_id = 0;
        $this->db->select(['id', 'key_name']);
        $this->db->from('tbl_references');
        $this->db->where_in('key_name', ['sleepover', 'unpaid', 'paid', 'interrupted_sleepover']);
        $this->db->where('archive', 0);
        $break_details = $this->db->get()->result_array();
        if (!empty($break_details)) {
            foreach($break_details as $bd) {
                if ($bd['key_name'] === 'sleepover') {
                    $so_ref_id = $bd['id'];
                }
                if ($bd['key_name'] === 'unpaid') {
                    $up_ref_id = $bd['id'];
                }
                if ($bd['key_name'] === 'paid') {
                    $p_ref_id = $bd['id'];
                }
                if ($bd['key_name'] === 'interrupted_sleepover') {
                    $ins_ref_id = $bd['id'];
                }
            }
        }
        
        $break_weekday_minutes = $break_saturday_minutes = $break_sunday_minutes = 0;
        if($break_rows) {
            foreach($break_rows as $row) {
                if(!isset($row['duration_int']))
                    continue;
                if ($row['break_type'] == $ins_ref_id) 
                    continue;
                if($row['break_type'] == $so_ref_id)
                $total_sleepover_minutes += $row['duration_int'];
                if($row['break_type'] == $up_ref_id)
                $total_unpaid_minutes += $row['duration_int'];
                if($row['break_type'] == $p_ref_id)
                $total_paid_minutes += $row['duration_int'];

                if(($row['break_type'] == $up_ref_id || $row['break_type'] == $so_ref_id) && isset($row['break_start_datetime']) && isset($row['break_end_datetime'])) {
                    # finding the weekday, saturday and sunday minutes spreading of shift break
                    list($weekday_minutes, $saturday_minutes, $sunday_minutes) = get_weekday_sat_sun_minutes($row['break_start_datetime'], $row['break_end_datetime']);

                    $break_weekday_minutes += $weekday_minutes;
                    $break_saturday_minutes += $saturday_minutes;
                    $break_sunday_minutes += $sunday_minutes;
                }
            }
        }

        $total_shift_hours = $total_mins - $total_sleepover_minutes - $total_unpaid_minutes;

        # finding the weekday, saturday and sunday minutes spreading of shift timings
        list($weekday_minutes, $saturday_minutes, $sunday_minutes) = get_weekday_sat_sun_minutes($start_datetime, $end_datetime);
        $weekday_minutes = $weekday_minutes - $break_weekday_minutes;
        $saturday_minutes = $saturday_minutes - $break_saturday_minutes;
        $sunday_minutes = $sunday_minutes - $break_sunday_minutes;

        $return = [$total_shift_hours, $total_mins, $total_sleepover_minutes, $total_unpaid_minutes, $total_paid_minutes, $weekday_minutes, $saturday_minutes, $sunday_minutes];
        return $return;
    }

    /**
     * calculating total shift hours and individual break hours based on break category
     */
    public function calc_total_shift_ind_break_hours_w_holiday($data, $break_cat = 1) { 
        if($break_cat == 2) {
            $break_rows = $data['actual_rows'];
            $start_datetime = $data['actual_start_datetime'];
            $end_datetime = $data['actual_end_datetime'];
        }
        else {
            $break_rows = $data['scheduled_rows'];
            $start_datetime = $data['scheduled_start_datetime'];
            $end_datetime = $data['scheduled_end_datetime'];
        }
        
        # calculating total shift durations in minutes
        $total_mins = minutesDifferenceBetweenDate($start_datetime, $end_datetime);

        # finding sleepover break reference id
        $total_sleepover_minutes = $total_unpaid_minutes = $total_paid_minutes = 0;
        $so_ref_id = $up_ref_id = $p_ref_id = 0;
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;

        # finding the unpaid break reference id
        $up_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "unpaid", "archive" => 0]);
        if ($up_details)
            $up_ref_id = $up_details->id;

        # finding the paid break reference id
        $p_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "paid", "archive" => 0]);
        if ($p_details)
            $p_ref_id = $p_details->id;

        $start_date = date('Y-m-d', strtotime($start_datetime));
        $end_date = date('Y-m-d', strtotime($end_datetime));

        # finding the public start date and end date is holiday or not
        $get_day_count = dayDifferenceBetweenDate($start_date, $end_date);

        $this->load->model('schedule/Ndispayments_model');
        
        $start_day = $this->Ndispayments_model->get_the_day($start_datetime);
        $end_day = $this->Ndispayments_model->get_the_day($end_datetime);
        
        $ph_data = $data['account_address'] ?? '';
        $phDataAddress = [];
        if($ph_data != '' && isset($ph_data['value'])) {
            $phDataAddress['account_address'] = (object) $ph_data;
        } else {
            $phDataAddress['account_address'] = [];
            if (isset($data['full_account_address']) && !empty($data['full_account_address'])) {
                $phDataAddress['full_account_address'] =(object) $data['full_account_address'];
            }          
        }        

        $start_date_start_time = ($start_datetime);
        $end_date_end_time = ($end_datetime);
        $day_in = 0;

        # if both date is holiday means no need to splitup
        if ($get_day_count > 0) {
            # check if it's holiday
            $start_is_holiday = $this->Ndispayments_model->check_public_holiday($phDataAddress, $start_date, '');
            $end_is_holiday = $this->Ndispayments_model->check_public_holiday($phDataAddress, $end_date, '');
        } else {
            $start_is_holiday = $end_is_holiday = $this->Ndispayments_model->check_public_holiday($phDataAddress, $start_date, '');
        }
        # form array
        if ($get_day_count < 1) {
            $duration_hr = hoursDiffBwnDates($start_date_start_time , $end_date_end_time);
            $duration_format = $this->Ndispayments_model->formatHoursToMinutes($duration_hr);
            $duration_min = $this->Ndispayments_model->hoursToMinutes($duration_format);
            
            $day_array[$day_in] = $this->Ndispayments_model->form_day_array($phDataAddress, $start_date, $end_date, $start_date_start_time, $end_date_end_time, $duration_hr, $get_day_count, '', $duration_min);
            $day_in++;
        } else {
            
            $end_date_start_time = $start_date_end_time = (date('Y-m-d', strtotime($end_date)) . " ". "00:00:00");
            $first_day_hr = hoursDiffBwnDates($start_date_start_time , $start_date_end_time);
            $second_day_hr = hoursDiffBwnDates($end_date_start_time , $end_date_end_time);
            
            $start_day_duration = $first_day_hr;
            $end_day_duration = $second_day_hr;

            $first_day_min = minutesDifferenceBetweenDate($start_date_start_time , $start_date_end_time);
            $second_day_min = minutesDifferenceBetweenDate($end_date_start_time , $end_date_end_time);
           
            $day_array[$day_in] = $this->Ndispayments_model->form_day_array($phDataAddress, $start_date, $start_date, $start_date_start_time, $start_date_end_time, $start_day_duration, 0, '', $first_day_min);
            $day_in++;

            $day_array[$day_in] = $this->Ndispayments_model->form_day_array($phDataAddress, $end_date, $end_date, $end_date_start_time, $end_date_end_time, $end_day_duration, 0, '', $second_day_min);
            $day_in++;
        }

        // # get break duration for date
       
        if(!empty($break_rows)) {

            if($break_cat == 2) {
                $data['start_datetime'] = $data['actual_start_datetime'];
                $data['end_datetime'] = $data['actual_end_datetime'];
                $data['start_date'] = date('Y-m-d', strtotime($data['actual_start_datetime']));
                $data['end_date'] = date('Y-m-d', strtotime($data['actual_end_datetime']));
            } else {
                $data['start_datetime'] = $data['scheduled_start_datetime'];
                $data['end_datetime'] = $data['scheduled_end_datetime'];
                $data['start_date'] = date('Y-m-d', strtotime($data['scheduled_start_datetime']));
                $data['end_date'] = date('Y-m-d', strtotime($data['scheduled_end_datetime']));
            }

            $break_rows =  $this->Ndispayments_model->valid_shift_break_form_array($break_rows, $data);
            
            $start_date = date('Y-m-d', strtotime($start_datetime));
            $end_date = date('Y-m-d', strtotime($end_datetime));

            $day_array = $this->Ndispayments_model->formatBreakDurationwithDate($break_rows, $start_date, $end_date, $get_day_count, $day_array); 
        }

        # calculating total shift durations in minutes
        $total_mins = minutesDifferenceBetweenDate($start_datetime, $end_datetime);
        $weekday_minutes = $saturday_minutes = $sunday_minutes = $public_holiday_minutes = 0;
        
        foreach($day_array as $day_item) {

            switch ($day_item['day_of_week']) {
                case 'public_holiday':
                    $public_holiday_minutes += $day_item['duration_time_minute'];
                    break;
                case 'sunday':
                    $sunday_minutes += $day_item['duration_time_minute'];
                    break;
                case 'saturday':
                    $saturday_minutes += $day_item['duration_time_minute'];
                    break;
                
                default:
                    $weekday_minutes += $day_item['duration_time_minute'];
                    break;
            }
            
        }

        //Finding total sleepover and unpaid/paid break time
        if($break_rows) {
            foreach($break_rows as $row) {
                if(!isset($row['duration_int']))
                    continue;

                if($row['break_type'] == $so_ref_id) {
                    $total_sleepover_minutes += $row['duration_int'];
                }
                if($row['break_type'] == $up_ref_id) {
                    $total_unpaid_minutes += $row['duration_int'];
                }
                if($row['break_type'] == $p_ref_id) {
                    $total_paid_minutes += $row['duration_int'];
                }
            }
        }
        
        $total_shift_hours = $total_mins - $total_sleepover_minutes - $total_unpaid_minutes;

        return [$total_shift_hours, $total_mins, $total_sleepover_minutes, $total_unpaid_minutes, $total_paid_minutes, $weekday_minutes, $saturday_minutes, $sunday_minutes, $public_holiday_minutes, $day_array];
        
    }

    /**
     * Accepting or rejecting a shift for a given member
     * Making necessary flow changes depending upon the action requested
     */
    public function accept_reject_shift($data) {
        $shift_member_id = isset($data['id']) ? $data['id'] : 0;
        $shift_id = isset($data['shift_id']) ? $data['shift_id'] : 0;
        $shift_no = isset($data['shift_no']) ? $data['shift_no'] : 0;
        $member_id = isset($data['member_id']) ? $data['member_id'] : 0;
        $status = isset($data['status']) ? $data['status'] : null;

        # validating the incoming data
        if (empty($shift_member_id)) {
            $response = ['status' => false, 'error' => "Missing Id"];
            return $response;
        }
        if (empty($shift_id)) {
            $response = ['status' => false, 'error' => "Missing Shift"];
            return $response;
        }
        if (empty($member_id)) {
            $response = ['status' => false, 'error' => "Missing Member"];
            return $response;
        }
        if (empty($status) || ($status != 1 && $status != 2)) {
            $response = ['status' => false, 'error' => "Missing / in-correct status"];
            return $response;
        }
        $status_label = ($status == 1) ? "accepted" : "declined";

        # does the shift exist?
        $shiftresult = $this->get_shift_details($shift_id);
        if (empty($shiftresult)) {
            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }

        # fetching the shift member information        
        $smresult = $this->get_shift_member_details($shift_member_id, $shift_id);
        if (empty($smresult) || $smresult['status'] != true) {
            $response = ['status' => false, 'error' => "Shift member does not exist anymore."];
            return $response;
        }

        # accepting but is it already accepted?
        if($data['status'] == 1 && ((isset($shiftresult['data']) && !empty($shiftresult['data']->accepted_shift_member_id)) || $smresult['data']->status == 1)) {
            $response = ['status' => false, 'error' => "Shift is already accepted"];
            return $response;
        }

        # rejecting but is it already rejected?
        if($data['status'] == 2 && isset($smresult['data']->status) && $smresult['data']->status == 2) {
            $response = ['status' => false, 'error' => "Shift is already declined"];
            return $response;
        }

        # does the shift start date & time passed?
        # there is a possibility of member not refreshing the page for while and it gets passed
        $valid_shift_date = check_dates_lower_to_other(DATE_TIME, $shiftresult['data']->scheduled_start_datetime, false);
        if(!$valid_shift_date) {
            $response = [
                "status" => false,
                "error" => "Scheduled start date-time: ".date("d/m/Y h:i A", strtotime($shiftresult['data']->scheduled_start_datetime))." of the shift has already passed"
            ];
            return $response;
        }

        # updating "accepted_shift_member_id" in the shift table to mark the assignment if accepting
        if($data['status'] == 1) {

            # checking member availability using unavailability provided, overtime rules and shifts distance rules
            $member_unavailable_det = $this->is_member_available_between_datetimes($shift_id, $member_id, $shiftresult['data']->scheduled_start_datetime, $shiftresult['data']->scheduled_end_datetime, $shiftresult['data']->owner_address);
            if($member_unavailable_det && $member_unavailable_det['status'] == false) {
                return $member_unavailable_det;
            }

            $upd_data["accepted_shift_member_id"] = $shift_member_id;
            $upd_data["status"] = 3;
            $result = $this->basic_model->update_records("shift", $upd_data, ["id" => $shift_id]);

            //Dispatch the event
            $this->load->library('EventDispatcher');
            $this->eventdispatcher->dispatch('onAfterShiftUpdated', $shift_id, (array) $upd_data);

            if (!$result) {
                $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                return $response;
            }
        }

        # updating status field in the shift member table
        $upd_data2["status"] = $data['status'];
        $result = $this->basic_model->update_records("shift_member", $upd_data2, ["id" => $shift_member_id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        $response = ['status' => true, 'msg' => "Successfully {$status_label} shift: ".$shift_no];
        return $response;
    }

    /**
     * archiving shift
     */
    function archive_shift($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # is the object being altered currently by other user? if yes - cannot perform this action
        $lock_taken = $this->add_shift_lock($id, $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;

        # does the shift exist?
        $result = $this->get_shift_details($data['id']);
        if (empty($result)) {
            # removing any access level locks taken by the admin user
            $shift_lock_res = $this->remove_shift_lock($data['id'], $adminId);
            if($shift_lock_res['status'] == false)
                return $shift_lock_res;

            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("shift", $upd_data, ["id" => $id]);

        # removing any access level locks taken by the admin user
        $shift_lock_res = $this->remove_shift_lock($data['id'], $adminId);
        if($shift_lock_res['status'] == false)
            return $shift_lock_res;

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        $response = ['status' => true, 'msg' => "Successfully archived shift"];
        return $response;
    }

    /**
     * wrapper function for copy shift option
     * does only validations part first and then actual db operations part
     */
    public function copy_shift_weekly_intervals_wrapper($data, $adminId, $uuid_user_type) {
        if (empty($data)) return;

        # checking shifts
        if(!isset($data['shifts']) || empty($data['shifts'])) {
            $response = ['status' => false, 'error' => "Please provide shifts to copy"];
            return $response;
        }
        if(!isset($data['weeks_list_selected']) || empty($data['weeks_list_selected'])) {
            $response = ['status' => false, 'error' => "Please select at least one week to copy shifts"];
            return $response;
        }
        if(count($data['shifts']) > 5) {
            $response = ['status' => false, 'error' => "Please select 5 or less shifts"];
            return $response;
        }

        # let's only validate the copy shifts (no db ops)
        $copy_res = $this->copy_shift_weekly_intervals($data, $adminId, true, $uuid_user_type);
        if($copy_res['status'] == false)
            return $copy_res;

        # let's do the db operations since all validations are ok
        $copy_res = $this->copy_shift_weekly_intervals($data, $adminId, false, $uuid_user_type);

        $shift_label = count($data['shifts']) == 1 ? " shift" : " shifts";
        $week_label = count($data['weeks_list_selected']) == 1 ? " week" : " weeks";
        $response = ['status' => true, 'msg' => "Successfully copied ".count($data['shifts']).$shift_label." into ".count($data['weeks_list_selected']).$week_label];
        return $response;
    }

    /**
     * based on the weekly selections of selected shifts
     * copying the shifts into those selections
     */
    public function copy_shift_weekly_intervals($data, $adminId, $skip_db_ops = false, $uuid_user_type) {

        # fetching the min and max shift scheduled startdate from the shifts list
        $shift_det = $this->get_min_max_shifts_dates($data);
        if (empty($shift_det)) {
            $return = array('msg' => "Shifts not found!", 'status' => false);
            return $return;
        }

        list($startdate, $enddate) = $shift_det;
        $monday_start = date("Y-m-d", strtotime('monday this week', strtotime($startdate)));

        # looping through shifts list and creating copies based on the weeks selection
        $this->db->select(["id as com_shift_id", "account_id", "account_type", "status", "role_id", "owner_id", "notes", "description", "person_id as contact_id", "scheduled_end_datetime", "scheduled_paid_break", "scheduled_start_datetime", "scheduled_travel", "scheduled_unpaid_break", "actual_end_datetime", "actual_paid_break", "actual_start_datetime", "actual_travel", "actual_unpaid_break", "email as contact_email", "phone as contact_phone", "scheduled_reimbursement", "actual_travel_duration", "actual_travel_distance", "scheduled_travel_duration", "scheduled_travel_distance"]);
        $this->db->from('tbl_shift as s');
        $this->db->where("s.id in (".implode(",",$data['shifts']).")");
        $this->db->where("s.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Shifts not found!", 'status' => false);
            return $return;
        }

        $errors = null;
        foreach ($query->result() as $val) {
            $shift_details = (array) $val;

            # status needs to be always open for now
            $shift_details['status'] = 1;

            # splitting the date and time from scheduled and actual datetime
            $shift_details['scheduled_start_date'] = date('Y-m-d', strtotime($shift_details['scheduled_start_datetime']));
            $shift_details['scheduled_start_time'] = date('h:i A', strtotime($shift_details['scheduled_start_datetime']));
            $shift_details['scheduled_end_date'] = date('Y-m-d', strtotime($shift_details['scheduled_end_datetime']));
            $shift_details['scheduled_end_time'] = date('h:i A', strtotime($shift_details['scheduled_end_datetime']));

            # setting all actual shift info to null as we donot have to copy it
            $shift_details['actual_rows'] = null;
            $shift_details['actual_travel'] = null;
            $shift_details['actual_reimbursement'] = null;
            $shift_details['notes'] = null;
            $shift_details['actual_start_datetime'] = null;
            $shift_details['actual_end_datetime'] = null;
            $shift_details['actual_start_date'] = null;
            $shift_details['actual_end_date'] = null;
            $shift_details['actual_start_time'] = null;
            $shift_details['actual_end_time'] = null;

            $shift_details['scheduled_travel_distance'] = $shift_details['scheduled_travel_distance'];
            $shift_details['scheduled_travel_duration'] = $shift_details['scheduled_travel_duration'];
            $shift_details['actual_travel_distance'] = null;
            $shift_details['actual_travel_duration'] = null;

            # getting the shift scheduled breaks
            $scheduled_breaks = $this->get_shift_breaks_list($val->com_shift_id, 1);
            $shift_details['scheduled_rows'] = null;
            if($scheduled_breaks) {
                $scheduled_breaks = object_to_array($scheduled_breaks);
                $scheduled_rows = null;
                foreach($scheduled_breaks as $break_row) {
                    unset($break_row['id']);
                    unset($break_row['shift_id']);
                    $scheduled_rows[] = $break_row;
                }
                $shift_details['scheduled_rows'] = $scheduled_rows;
            }

            # checking the days difference from base start date
            $base_date_diff = dayDifferenceBetweenDate($monday_start, $shift_details['scheduled_start_date']);
            $shift_date_diff = dayDifferenceBetweenDate($shift_details['scheduled_start_date'], $shift_details['scheduled_end_date']);

            # for each week start day, calling the modal function to create the shift
            foreach ($data['weeks_list_selected'] as $repeat_date) {
                $newdata = $shift_details;
                $newdata['scheduled_start_date'] = date('Y-m-d', strtotime($repeat_date . '+'.$base_date_diff.' day'));

                if($shift_date_diff > 0)
                $newdata['scheduled_end_date'] = date('Y-m-d', strtotime($newdata['scheduled_start_date'] . '+'.$shift_date_diff.' day'));
                else
                $newdata['scheduled_end_date'] = $newdata['scheduled_start_date'];
                # skipping overlapping shift check?
                if(!empty($data['skip_account_shift_overlap']))
                    $newdata['skip_account_shift_overlap'] = true;

                $response = $this->create_update_shift($newdata, $adminId,$uuid_user_type, $skip_db_ops);
                if(!(isset($response) && $response['status'] == true) && isset($response['account_shift_overlap'])) {
                    $errors[] = $response['error'];
                }
                else if(!(isset($response) && $response['status'] == true)) {
                    return $response;
                }
            }
        }

        if(!empty($errors)) {
            return ["status" => false, "account_shift_overlap" => true, "error" => implode(", ", $errors)];
        }
        return ["status" => true, "msg" => "Shifts copied"];
    }

    /**
     * fetching the min and max shift scheduled startdate from the shifts list
     */
    public function get_min_max_shifts_dates($data) {
        $this->db->select(["min(scheduled_start_datetime) as min", "max(scheduled_start_datetime) as max"]);
        $this->db->from('tbl_shift as s');
        $this->db->where("s.id in (".implode(",",$data['shifts']).")");
        $this->db->where("s.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = null;
        if (empty($query->result())) {
            return false;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        $startdate = $dataResult->min;
        $enddate = $dataResult->max;

        return [$startdate, $enddate];
    }

    /**
     * fetching the weekly intervals based on the shifts selection
     */
    public function get_copy_shift_intervals($data) {
        if (empty($data)) return;

        # checking shifts
        if(!isset($data['shifts']) || empty($data['shifts'])) {
            $response = ['status' => false, 'error' => "Please provide shifts to copy"];
            return $response;
        }
        if(count($data['shifts']) > 5) {
            $response = ['status' => false, 'error' => "Please select 5 or less shifts"];
            return $response;
        }

        # fetching the min and max shift scheduled startdate from the shifts list
        $shift_det = $this->get_min_max_shifts_dates($data);
        if (empty($shift_det)) {
            $return = array('msg' => "Shifts not found!", 'status' => false);
            return $return;
        }
        list($startdate, $enddate) = $shift_det;
        $monday_start = date("Y-m-d", strtotime('monday this week', strtotime($startdate)));
        $monday_end = date("Y-m-d", strtotime('monday this week', strtotime($enddate)));

        # finding date and week difference between earliest and lattest shift
        $date_diff = dayDifferenceBetweenDate($monday_start, $monday_end);
        $week_diff = ($date_diff / 7) + 1;

        # week difference cannot be more than 4
        if($week_diff > 4) {
            $response = ['status' => false, 'error' => "Weekly intervals of selected shifts is more than 4, it must be 4 or less."];
            return $response;
        }

        # finding total week intervals to display
        # 6 months for now!
        $week_intervals = (floor(26 / $week_diff)-1);

        # displaying first monday for each week interval
        $week_selections = [];
        $next_week = date('Y-m-d', strtotime($monday_end . '+1 week'));
        $week_selections[] = ['id' => 0, 'week' => $next_week];
        for($i=1;$i<=$week_intervals;$i++) {
            $next_week = date('Y-m-d', strtotime($next_week . '+'.$week_diff.' week'));
            $week_selections[] = ['id' => $i, 'week' => $next_week];
        }

        $return = array('data' => $week_selections, 'status' => true);
        return $return;
    }

    /**
     * fetching the shifts of accounts that their timesheets are paid and not yet invoiced
     */
    public function get_paid_non_invoice_shifts($data) {

        # validating date range
        if(!empty($data['start_date']) || !empty($data['end_date'])) {
            $this->load->library('form_validation');
            $this->form_validation->reset_validation();

            # validation rule
            if(!empty($data['start_date']))
                $validation_rules[] = array('field' => 'start_date', 'label' => 'Start Date', 'rules' => 'valid_date_format[Y-m-d]', 'errors' => ['valid_date_format' => 'Incorrect Start Date']);
            if(!empty($data['end_date']))
                $validation_rules[] = array('field' => 'end_date', 'label' => 'End Date', 'rules' => 'valid_date_format[Y-m-d]', 'errors' => ['valid_date_format' => 'Incorrect End Date']);

            # set data in libray for validate
            $this->form_validation->set_data($data);

            # set validation rule
            $this->form_validation->set_rules($validation_rules);

            # check data is valid or not
            if (!$this->form_validation->run()) {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
                return $response;
            }

            # check the range is correct
            if(!empty($data['start_date']) && !empty($data['end_date'])) {
                $valid_date_range = check_dates_lower_to_other($data['start_date'], $data['end_date']);
                if(!$valid_date_range) {
                    $response = [
                        "status" => false,
                        "error" => "Start date: ".date("d/m/Y", strtotime($data['start_date']))." should be lower to end date: ".date("d/m/Y", strtotime($data['end_date'])) ];
                    return $response;
                }
            }
        }

        $select_column = ["s.id", "s.shift_no", "s.account_id", "s.account_type", "s.actual_start_datetime", "s.actual_end_datetime", "am.fullname as member_fullname", "am.id as member_id", "s.scheduled_duration","(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o1.name from tbl_organisation o1 where o1.id = s.account_id) ELSE '' END) as account_fullname"];
        $this->db->select($select_column, false);

        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_member m', 'm.id = s.owner_id and m.archive = 0', 'inner');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id and asm.archive = 0', 'inner');
        $this->db->join('tbl_finance_timesheet as ft', 'ft.shift_id = s.id and ft.archive = 0 and ft.status = 4', 'inner');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id and am.archive = 0', 'inner');
        $this->db->join('tbl_finance_invoice_shift as ish', 'ish.shift_id = s.id and ish.archive = 0', 'left');
        $this->db->join('tbl_finance_invoice as i', 'ish.invoice_id = i.id and i.archive = 0 and i.status != 3', 'left');

        # restricting to the account type
        if($data['account_type'] == 1) {
            $this->db->join('tbl_participants_master as pm', 'pm.id = s.account_id and pm.archive = 0 and pm.id = '.$data['account_id'], 'inner');
        }
        else if($data['account_type'] == 2) {

            $org_ids[] = $data['account_id'];

            # include all the shifts of selected org and/or selected sites
            if(!empty($data['invoice_sites']))
                $org_ids = array_merge($org_ids, $data['invoice_sites']);

            $this->db->join('tbl_organisation as o', 'o.id = s.account_id and o.archive = 0 and o.id in ('.implode(",",$org_ids).')', 'inner');
        }

        # restricting by the dates
        if(!empty($data['start_date'])) {
            $this->db->where("date(s.actual_start_datetime) >= '{$data['start_date']}'");
        }
        if(!empty($data['end_date'])) {
            $this->db->where("date(s.actual_start_datetime) <= '{$data['end_date']}'");
        }

        $this->db->where("s.archive", "0");
        $this->db->where("s.account_type", $data['account_type']);
        $this->db->where("i.id is null");
        $this->db->where("s.not_be_invoiced", 0);
        $this->db->order_by("s.actual_start_datetime", 'DESC');

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();

        $return = array('data' => $result, 'status' => true, 'msg' => 'Fetched shifts list successfully');
        return $return;
    }

    /*
     * To fetch the shifts list
     */
    public function get_shifts_list($reqData, $filter_condition = '', $adminId = null, $uuid_user_type) {
        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 20;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';
        $statusArr=$reqData->statusArr??[1,2,3,4];
        if(isset($reqData->current_shift_list)){
            $response = $this->get_current_shift_list_data($adminId,$reqData->current_shift_list, $page,$limit,[],$reqData->shift_list_current_offset,$limit,$filter,$filter_condition,$reqData->current_shift_list,$reqData->status_filter_value, $uuid_user_type);
            $total_data=$this->get_shift_total_rows($reqData,$filter_condition,$adminId,$statusArr, $filter, $uuid_user_type);
            $response['total_item']=$total_data;
            return $response;
            exit();
      }
      else{
        return array('count' => 0, 'data' => [], 'status' => true, 'msg' => 'Fetched shifts list successfully', 'total_item' => 0);
      }
     /*    # Searching column
        $src_columns = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)", "r.name", "am.fullname", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)", "s.shift_no", "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_end_datetime,'%d/%m/%Y')", "s.scheduled_duration", "tr.display_name", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week");

        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }
                else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }
            $this->db->group_end();
        }

        # new lightening filters
        if(isset($filter->filters)) {

            foreach($filter->filters as $filter_obj) {
                if(empty($filter_obj->select_filter_value)) continue;

                $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
                if($filter_obj->select_filter_field == "account_fullname") {
                    $this->db->where("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "owner_fullname") {
                    $this->db->where("concat(m.firstname,' ',m.lastname) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "role_name") {
                    $this->db->where('r.name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "shift_no") {
                    $this->db->where('s.shift_no '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "status_label") {
                    $this->db->where('s.status '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "scheduled_duration") {
                    $this->db->where('s.scheduled_duration '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "account_organisation_type") {
                    $this->db->where('tr.display_name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "scheduled_start_datetime" || $filter_obj->select_filter_field == "scheduled_end_datetime") {
                    $this->db->where('DATE_FORMAT(s.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.id';
            $direction = 'DESC';
        }

        $select_column = ["s.id", "s.shift_no", "s.scheduled_start_datetime", "s.scheduled_end_datetime", "s.actual_start_datetime", "s.actual_end_datetime", "concat(m.firstname,' ',m.lastname) as owner_fullname", "concat(p.firstname,' ',p.lastname) as contact_fullname", "r.name as role_name", "", "'' as actions", "s.person_id", "s.owner_id", "s.account_type", "s.account_id", "s.role_id", "s.status", "am.fullname as member_fullname", "am.id as member_id", "s.scheduled_duration","tr.display_name as account_organisation_type", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week", "s.roster_id", "s.not_be_invoiced", "s.scheduled_sa_id", "s.scheduled_sb_status", "scheduled_docusign_id", "s.actual_sa_id", "s.actual_sb_status", "actual_docusign_id"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as account_fullname");

        $status_label = "(CASE ";
        foreach($this->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);
        if($adminId)
            $this->db->select('al.id as is_shift_locked');

        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_member m', 'm.id = s.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = s.person_id', 'left');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id', 'left');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id', 'left');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->join('tbl_organisation as o', 'o.id = s.account_id AND s.account_type = 2', 'left');
        $this->db->join('tbl_references as tr', 'o.org_type  = tr.id AND tr.archive = 0', 'left');
        $this->db->join('tbl_roster as ros', 'ros.id = s.roster_id', 'LEFT');
        if($adminId)
            $this->db->join('tbl_access_lock as al', 's.id = al.object_id and al.archive = 0 and al.created_by != '.$adminId.' and al.object_type_id = 1', 'left');
            
        $this->db->where("s.archive", "0");

        if (!empty($reqData->user_type) && $reqData->user_type === 3) {
            $this->db->where("s.created_by", $adminId);
        }

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        foreach($result as $key=> $val) {
            if($val->role_name == "NDIS" && $val->account_type == 1) { 
                $result[$key]->warnings = $this->pull_shift_warnings($val);               
            } else {
                $warning_obj = new stdClass();
                $warning_obj->is_warnable =false;
                $warning_obj->warning_messages = [];
                $result[$key]->warnings = $warning_obj;
            }
        }

        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched shifts list successfully',"last_query" => $last_query, 'total_item' => $total_item);
         */
    }

    /** Fetch the shift warning status by shift data
     * @param $val {object} shift data
     * 
     * @return $warnings {bool} true/false
     */
    public function pull_shift_warnings($val) {

        $warning_obj = new stdClass();
        $warning_obj->is_warnable =false;
        $warning_obj->warning_messages = array();
        $warnings = false;

        $section = ($val->actual_start_datetime && $val->actual_end_datetime) ? 'actual' : 'scheduled';
              
               if(empty($val->{$section."_sa_id"}) || empty($val->{$section."_docusign_id"})) {  
                    array_push($warning_obj->warning_messages,"No NDIS Service Agreement exists for the requested shift date");     
               } else if($val->{$section."_sb_status"} > 1 ||$val->{$section."_sb_status"} < 1) {
                    $sa_post_data=new stdClass();
                    $account_person= new stdClass();
                    $account_person->label = $val->account_fullname;
                    $account_person->value = $val->account_id;
                    $account_person->account_type = $val->account_type;
                    $sa_post_data->account= $account_person;
                    $sa_post_data->section = $section;
                   
                    $sa_post_data->start_date = $val->{$section . "_start_datetime"};
                    $sa_post_data->end_date = $val->{$section . "_end_datetime"};
                    $sa_post_data->shift_id=$val->id;
                    $result = $this->get_service_agreement($sa_post_data);
                     if(!$result['status'] && $result['error'] !== 'API ERROR')
                     {
                        $msg=$result['rule']==2?'No service booking exist for the requested shift date':'Existing Service Booking for the requested shift date is not signed';
                        array_push($warning_obj->warning_messages,$msg);  
                     }
               }
               if(!empty($val->{$section."_sa_id"}) && !empty($val->{$section."_docusign_id"})) {

                  $is_line_item_exist = $this->basic_model->get_row('shift_ndis_line_item', ['id'], ['shift_id' => $val->id]);
                  if(!$is_line_item_exist)
                  {
                    array_push($warning_obj->warning_messages,'Missing Support Items in the plan for the request shift service');
                     
                  }else{
                    $missed_item = $this->basic_model->get_row('shift_ndis_line_item', ['id'], ['shift_id' => $val->id, 'auto_insert_flag' => 1,'archive' => 0]);
                    if($missed_item) {
                        array_push($warning_obj->warning_messages,'Missing Support Items in the plan for the request shift service');
                    }
                  }
                 
                   
               }

               if(count($warning_obj->warning_messages)>0)
               {
                $warning_obj->is_warnable=true;
               }
              
               return $warning_obj;
    }

    /**
     * archiving shift member
     */
    function archive_shift_member($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $upd_data["status"] = 0; # no more kept as accepted
        $result = $this->basic_model->update_records("shift_member", $upd_data, ["id" => $id]);
        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # is this shift member has accepted any shifts?
        # mark the "accepted_shift_member_id" of tbl_shift table blank
        $result = $this->basic_model->update_records("shift", ["accepted_shift_member_id" => null], ["accepted_shift_member_id" => $id]);

        $response = ['status' => true, 'msg' => "Successfully archived shift member"];
        return $response;
    }

    /**
     * fetching published shifts that are assigned to a member
     * function is used mainly in the member/applicant web app
     */
    public function get_shifts_member($reqData) {
        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];

        # default listing is available, if requested as accepted, changing the query a bit
        $type = $reqData->type ?? "available";

        $orderBy = '';
        $direction = '';

        # building status cases
        $status_label = "(CASE ";
        foreach($this->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        # Searching column
        $src_columns = array("r.name",
        "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)",
        "s.shift_no", $status_label,
        "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')",
        "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "s.scheduled_duration");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, TRUE);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $available_column = ["id", "shift_no", "member_id", "fullname", "shift_id", "scheduled_start_datetime", "scheduled_end_datetime", "role_name", "status", "scheduled_duration", "account_id", "account_type"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 's.scheduled_start_datetime';
            $direction = 'ASC';
        }
        $select_column = ["sm.id", "s.shift_no", "sm.member_id", "m.fullname", "sm.shift_id", "s.scheduled_start_datetime", "s.scheduled_end_datetime", "r.name as role_name", "'' as actions", "s.status", "s.scheduled_duration", "s.account_id", "s.account_type"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as account_fullname");
        $this->db->select($status_label);

        $this->db->from('tbl_shift_member as sm');
        $this->db->join('tbl_member as m', 'm.id = sm.member_id', 'inner');
        $this->db->join('tbl_shift as s', 's.id = sm.shift_id', 'inner');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        if($type == "accepted") {
            $this->db->join('tbl_shift_member as sma', 's.accepted_shift_member_id = sma.id AND sma.member_id = '. sprintf("%d", $reqData->member_id), 'inner');
            $this->db->where('sm.status', 1);
            // $this->db->where('s.status', 2);
        }
        else {
            $this->db->where('s.accepted_shift_member_id IS NULL');
            $this->db->where('sm.status', 0);
            $this->db->where("s.scheduled_start_datetime > NOW()");
            $this->db->where('s.status', 2);
        }


        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('m.id', $reqData->member_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        //fetch account address for all shifts
        // collect accounts with their types
        if (!empty($result)) {
            $p_type = []; //person
            $o_type = []; //organisation
            foreach($result as $s) {
                if ($s->account_type == 1) {
                    $p_type[$s->id] = $s->account_id;
                }
                if ($s->account_type == 2) {
                    $o_type[$s->id] = $s->account_id;
                }
            }
            $all_p_ac_ids = array_unique(array_values($p_type));
            $all_o_ac_id = array_unique(array_values($o_type));
            $p_addresses = $this->get_address_for_accounts($all_p_ac_ids, 1);
            $o_addresses = $this->get_address_for_accounts($all_o_ac_id, 2);
            foreach($result as $s) {
                if (array_key_exists($s->account_id, $p_addresses) && $s->account_type == 1) {
                    $s->address = $p_addresses[$s->account_id];
                }
                if (array_key_exists($s->account_id, $o_addresses) && $s->account_type == 2) {
                    $s->address = $o_addresses[$s->account_id];
                }
            }
        }
        $return = array('count' => $dt_filtered_total, 'last_query' => $last_query, 'data' => $result, 'status' => true, 'msg' => 'Fetch unavailability list successfully');
        return $return;
    }

    /*
     * For getting shift members list
     */
    public function get_shift_member_list($reqData) {

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $is_restricted=$reqData->is_restricted??false;
        $orderBy = '';
        $direction = '';
        # Searching column
        $src_columns = ["m.fullname", "s.shift_no", "DATE_FORMAT(sm.created,'%d/%m/%Y')"];
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, true);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $available_column = ["id", "shift_no", "member_id", "fullname", "shift_id", "created","archive"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'sm.id';
            $direction = 'DESC';
        }
        $select_column = ["sm.id", "s.shift_no", "sm.member_id", "m.fullname", "sm.shift_id", "sm.created", "'' as actions","sm.archive"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN sm.status = 2 THEN '1' ELSE '' END) as is_declined");
        $this->db->select("(CASE WHEN sm.status = 1 THEN '1' ELSE '' END) as is_accepted");
        $this->db->select("sm.is_restricted");
        $this->db->from('tbl_shift_member as sm');
      
        $this->db->join('tbl_member as m', 'm.id = sm.member_id', 'inner');
        if($is_restricted){
            $this->db->where("sm.archive= 0 and sm.shift_id=$reqData->shift_id and sm.is_restricted=0  OR sm.archive= 1 and sm.is_restricted=1" );
        }
        else{
            $this->db->where("sm.archive= 0 and sm.shift_id=$reqData->shift_id and sm.is_restricted=0" );
        }
         
        $this->db->join('tbl_shift as s', 's.id = sm.shift_id', 'inner');
        if(isset($reqData->shift_id) && $reqData->shift_id > 0)
        $this->db->where('sm.shift_id', $reqData->shift_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        // Get the query result
        $result = $query->result();
        if($is_restricted){
            $dt_filtered_total=count($result);
        }
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch unavailability list successfully');
        return $return;
    }

    /**
     * fetching the shift member ids list of a given shift id
     * optional member_id will give only one record
     */
    public function get_shift_member_ids($shift_id, $member_id = null) {
        $select_column = ["sm.id", "s.shift_no", "sm.member_id", "m.fullname", "sm.shift_id", "sm.created", "'' as actions"];

        $this->db->select(["sm.id", "sm.member_id"]);
        $this->db->from('tbl_shift_member as sm');
        $this->db->join('tbl_member as m', 'm.id = sm.member_id', 'inner');
        $this->db->join('tbl_shift as s', 's.id = sm.shift_id', 'inner');
        $this->db->where('sm.archive', 0);
        $this->db->where('s.archive', 0);
        $this->db->where('m.archive', 0);
        $this->db->where('sm.shift_id', $shift_id);
        if($member_id)
        $this->db->where('sm.member_id', $member_id);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shift_member_ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $shift_member_ids[] = $row->id;
            }
        }
        return $shift_member_ids;
    }

    /**
     * validating shift break timings using number of business rules
     */
    function validate_shift_breaks($data, $rows, $break_cat = 1, $overtime_allowed = null, $validate_inso) {
        $errors = null;
        $total_break_minutes = 0;

        if($break_cat == 2) {
            $shift_start_datetime = $data['actual_start_datetime'];
            $shift_end_datetime = $data['actual_end_datetime'];
            $shift_start_date = $data['actual_start_date'];
            $shift_end_date = $data['actual_end_date'];
        }
        else {
            $shift_start_datetime = $data['scheduled_start_datetime'];
            $shift_end_datetime = $data['scheduled_end_datetime'];
            $shift_start_date = $data['scheduled_start_date'];
            $shift_end_date = $data['scheduled_end_date'];
        }

        # fetching the SO reference id
        $so_ref_id = null;
        $so_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "sleepover", "archive" => 0]);
        if ($so_details)
            $so_ref_id = $so_details->id;

        # fetching the InterruptSO reference id
        $inso_ref_id = null;
        $inso_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "interrupted_sleepover", "archive" => 0]);
        if ($so_details)
            $inso_ref_id = $inso_details->id;

        foreach($rows as $ind => $row) {
            $row = (array) $row;
            $rowno = $ind+1;
            $break_minutes = null;

            # need to select break type
            if(empty($row['break_type'])) {
                $errors[] = "Please provide Break Type for break row-{$rowno}";
            }

            # need to have both start time and end time OR duration
            if (($row['break_type'] == $so_ref_id || $row['break_type'] == $inso_ref_id) && (empty($row['break_start_time']) || empty($row['break_end_time']))) {
                $errors[] = "Please provide Start Time & End Time for break row-{$rowno}";
            } else if(empty($row['break_start_time']) && empty($row['break_end_time']) && empty($row['break_duration'])) {
                $errors[] = "Please provide either Start Time & End Time OR Duration for break row-{$rowno}";
            }
            # duration need to be in correct format
            else if(!empty($row['break_duration']) && $row['duration_disabled'] == 0) {
                $duration_full = "2020-01-01 ".$row['break_duration'].":00";
                $valid_duration = date('Y-m-d H:i:s', strtotime($duration_full)) === $duration_full;
                if(!$valid_duration) {
                    $errors[] = "Please provide duration in HH:MM format only for break row-{$rowno}";
                }
                else {
                    list($hour, $minutes) = explode(":",$row['break_duration']);
                    $hour = (int) $hour;
                    $minutes = (int) $minutes;
                    $break_minutes = ($minutes + ($hour * 60));
                    $total_break_minutes += $break_minutes;
                    $rows[$ind]['duration_int'] = $break_minutes;
                    $rows[$ind]['duration'] = $row['break_duration'];
                }
            }
            # need to have both start time and end time OR duration
            else if(empty($row['break_start_time']) || empty($row['break_end_time'])) {
                $errors[] = "Please provide both Start Time & End Time for break row-{$rowno}";
            }
            else if(!empty($row['break_start_time']) && !empty($row['break_end_time'])) {
                $break_start_time = "2020-01-01 ".$row['break_start_time'];
                $valid_break_start_time = date('Y-m-d h:i A', strtotime($break_start_time)) === $break_start_time;
                $break_end_time = "2020-01-01 ".$row['break_end_time'];
                $valid_break_end_time = date('Y-m-d h:i A', strtotime($break_end_time)) === $break_end_time;
                if(!$valid_break_start_time) {
                    $errors[] = "Please provide break start time in correct format row-{$rowno}";
                }
                else if(!$valid_break_end_time) {
                    $errors[] = "Please provide break start time in correct format row-{$rowno}";
                }
                else {
                    $date_in_break_start = $shift_start_date;
                    $date_in_break_end = $shift_end_date;

                    # if sleepover shift then checking which date to use for break start and end time
                    if($shift_start_date != $shift_end_date) {
                        if(substr_count($row['break_start_time'],"PM") > 0 && substr_count($row['break_end_time'],"AM") > 0) {
                            $date_in_break_start = $shift_start_date;
                            $date_in_break_end = $shift_end_date;
                        }
                        else {
                            $check_break_start_from_start = $shift_start_date." ".$row['break_start_time'];
                            $check_break_end_from_start = $shift_start_date." ".$row['break_end_time'];

                            $valid_break_start_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_start_from_start,true);
                            if($valid_break_start_from_start) {
                                $date_in_break_start = $shift_start_date;
                            }
                            else {
                                $date_in_break_start = $shift_end_date;
                            }

                            $valid_break_end_from_start = check_dates_lower_to_other_exc($shift_start_datetime, $check_break_end_from_start);
                            if($valid_break_end_from_start) {
                                $date_in_break_end = $shift_start_date;
                            }
                            else {
                                $date_in_break_end = $shift_end_date;
                            }
                        }
                    }

                    $break_start_datetime = $date_in_break_start." ".$row['break_start_time'];
                    $break_end_datetime = $date_in_break_end." ".$row['break_end_time'];

                    $rows[$ind]['break_start_datetime'] = $break_start_datetime;
                    $rows[$ind]['break_end_datetime'] = $break_end_datetime;

                    # start time should be greater than shift start time
                    $valid_date_range = check_dates_lower_to_other_exc($shift_start_datetime, $break_start_datetime, true);
                    if(!$valid_date_range) {
                        $errors[] = "Break start-time: ".$row['break_start_time']." should be later or equal to shift start-time: ".date("d/m/Y h:i A", strtotime($shift_start_datetime))." for break row-{$rowno}";
                    }

                    # end time should be lower than shift end time
                    $valid_date_range = check_dates_lower_to_other_exc($break_end_datetime, $shift_end_datetime, true);
                    if(!$valid_date_range) {
                        $errors[] = "Break end-time: ".$row['break_end_time']." should be lower or equal to shift end-time: ".date("d/m/Y h:i A", strtotime($shift_end_datetime))." for break row-{$rowno}";
                    }

                    # break end time should be greater than break start time
                    if(strtotime($break_start_datetime) > strtotime($break_end_datetime)) {
                        $errors[] = "Break start-time: ".$row['break_start_time']." should be lower to break end-time: ".$row['break_end_time']." for break row-{$rowno}";
                    }

                    $break_minutes = minutesDifferenceBetweenDate($break_start_datetime, $break_end_datetime);
                    
                    if ($inso_ref_id != $row['break_type']) {
                        $total_break_minutes += $break_minutes;
                    }
                    
                    $rows[$ind]['duration_int'] = $break_minutes;
                    $rows[$ind]['duration'] = get_hour_minutes_from_int($break_minutes);
                }
            }

            # sleepover break needs to be more than the threshold
            if($row['break_type'] == $so_ref_id && $break_minutes < SO_BREAK_DURATION && $break_cat == 1 && !$overtime_allowed) {
                $errors[] = "Sleepover needs to be more than ".(SO_BREAK_DURATION / 60)." Hrs for row-{$rowno}";
            }
        }
        
        if(!$errors && !$overtime_allowed) {
            foreach($rows as $ind => $row) {
                if ($inso_ref_id == $row['break_type']) 
                    continue;
                if(!empty($row['break_duration']) && $row['duration_disabled'] == 0) continue;
                # start & time should not overlap with other start & end time
                $overlapdetails = $this->check_breaks_not_overlapping($ind, $rows, $rows[$ind]['break_start_datetime'], $rows[$ind]['break_end_datetime']);
                if($overlapdetails) {
                    if($errors)
                    $errors = array_merge($errors, $overlapdetails);
                    else
                    $errors = $overlapdetails;
                }
            }
        }
        
        if ($validate_inso) {
            $so_key = array_search($so_ref_id, array_column($rows, 'break_type'));
            $inso_key = array_search($inso_ref_id, array_column($rows, 'break_type'));

            $breakTypeCol = $this->array_column($rows, 'break_type');
            $breakTypeCount = array_count_values($breakTypeCol);

            // validate more than one timing for s/o
            if (!empty($breakTypeCount) && isset($breakTypeCount[$so_key]) && $breakTypeCount[$so_key] > 1) {
                $errors[] = "Interrupted S/O can't be added more than one";
            }

            // validate more than one timing for Interrupted s/o
            if (!empty($breakTypeCount) && isset($breakTypeCount[$inso_key]) && $breakTypeCount[$inso_key] > 1) {
                $errors[] = "S/O can't be added more than one";
            }

            if($break_cat == 2 && !$errors) {                
                $errors = $this->interrupted_sleepover_validation($rows, $so_ref_id, $inso_ref_id);                   
            }
        }

        $total_shift_minutes = minutesDifferenceBetweenDate($shift_start_datetime, $shift_end_datetime);
        
        if($total_break_minutes >= $total_shift_minutes) {
            $errors[] = "Overall break durations is equal or more than the shift duration";
        }
        return [$errors, $rows];
    }

    /**
     * array_column
     *
     * @param array $array rows - multidimensional
     * @param int|string $key column
     * @return array;
     */
    public function array_column($array, $key) {
        $column = array();
        foreach($array as $origKey => $value) {
            if (isset($value[$key])) {
                $column[$origKey] = $value[$key];
            }            
        }
        return $column;
    }

    /**
     * checks if any given break start & end datetime is not overlapping with other breaks' start & end datetime
     */
    function check_breaks_not_overlapping($ind, $rows, $break_start_datetime, $break_end_datetime) {
        $errors = null;
        $ins_ref_id = null;
        $insleep_details = $this->basic_model->get_row('references', ['id'], ["key_name" => "interrupted_sleepover", "archive" => 0]);
        if ($insleep_details)
            $ins_ref_id = $insleep_details->id;
        
        foreach($rows as $comind => $row) {
            // $rowno = $comind+1;
            if ($row['break_type'] == $ins_ref_id) 
                continue;
            if($comind == $ind) continue;
            if(!empty($row['break_duration']) && $row['duration_disabled'] == 0) continue;
            $combreak_start_datetime = $rows[$comind]['break_start_datetime'];
            $combreak_end_datetime = $rows[$comind]['break_end_datetime'];

            $start_in_range = check_dates_between_two_dates($combreak_start_datetime, $combreak_end_datetime, $break_start_datetime);
            if($start_in_range) {
                $errors[] = "Break start-time: ".$rows[$ind]['break_start_time']." overlaps with other break timings";
            }

            $end_in_range = check_dates_between_two_dates($combreak_start_datetime, $combreak_end_datetime, $break_end_datetime);
            if($end_in_range) {
                $errors[] = "Break end-time: ".$rows[$ind]['break_end_time']." overlaps with other break timings";
            }
            
        }
        return $errors;
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_shift_duration($shift_details) {
        
        if($shift_details['break_cat'] == 2) {
            $shift_details['actual_start_datetime'] = $shift_details['actual_start_date']." ".$shift_details['actual_start_time'];
            $shift_details['actual_end_datetime'] = $shift_details['actual_end_date']." ".$shift_details['actual_end_time'];

            # checking the valid actual shift date range
            # checking break entries and validating them
            $valid_dates = $this->validate_shift_and_breaks_dates($shift_details,isset($shift_details['actual_rows'])?$shift_details['actual_rows']:null,$shift_details['actual_start_datetime'], $shift_details['actual_end_datetime'],2,true);
            if($valid_dates && $valid_dates['status'] == false)
                return ['status' => true, 'data' => "00:00", 'invalid' => $valid_dates];
            else if($valid_dates && $valid_dates['status'] == true)
                $shift_details['actual_rows'] = $valid_dates['data'];
            }
        else {
            $shift_details['scheduled_start_datetime'] = $shift_details['scheduled_start_date']." ".$shift_details['scheduled_start_time'];
            $shift_details['scheduled_end_datetime'] = $shift_details['scheduled_end_date']." ".$shift_details['scheduled_end_time'];

            # checking the valid scheduled shift date range
            # checking break entries and validating them
            $valid_dates = $this->validate_shift_and_breaks_dates($shift_details,isset($shift_details['scheduled_rows'])?$shift_details['scheduled_rows']:null,$shift_details['scheduled_start_datetime'], $shift_details['scheduled_end_datetime'],1, true);
            if($valid_dates && $valid_dates['status'] == false)
                return ['status' => true, 'data' => "00:00", 'invalid' => $valid_dates];
            else if($valid_dates && $valid_dates['status'] == true)
                $shift_details['scheduled_rows'] = $valid_dates['data'];
        }

        $shift_hours_details = $this->calc_total_shift_ind_break_hours($shift_details, $shift_details['break_cat']);
        list($total_shift_hours, $total_mins, $total_sleepover_minutes, $total_unpaid_minutes, $total_paid_minutes, $weekday_minutes, $saturday_minutes, $sunday_minutes) = $shift_hours_details;
        $duration = get_hour_minutes_from_int($total_shift_hours);

        $response = ['status' => true, 'data' => $duration];
        return $response;
    }

    /**
     * calculate the duration between two timings and returns in HH:MM format
     */
    function calculate_break_duration($data) {
        $break_start_time = "2020-01-01 ".$data['break_start_time'];
        $valid_break_start_time = date('Y-m-d h:i A', strtotime($break_start_time)) === $break_start_time;
        $break_end_time = "2020-01-01 ".$data['break_end_time'];
        $valid_break_end_time = date('Y-m-d h:i A', strtotime($break_end_time)) === $break_end_time;
        if(!$valid_break_start_time || !$valid_break_end_time) {
            $response = ['status' => false, 'data' => ''];
            return $response;
        }
        else if($break_start_time == $break_end_time) {
            $response = ['status' => false, 'data' => '00:00'];
            return $response;
        }

        $start_lower = check_dates_lower_to_other_exc($break_start_time, $break_end_time);
        $first_date = $break_start_time;
        $second_date = $break_end_time;
        if(!$start_lower) {
            $second_date = "2020-01-02 ".$data['break_end_time'];
        }

        $break_minutes = minutesDifferenceBetweenDate($first_date, $second_date);
        $duration = get_hour_minutes_from_int($break_minutes);
        $response = ['status' => true, 'data1' => $break_minutes, 'data2' => $duration];
        return $response;
    }

    /**
     * fetching the shift break ids list of a given shift id
     */
    public function get_shift_break_ids($shift_id, $break_cat) {
        $this->db->select(["sb.id"]);
        $this->db->from('tbl_shift_break as sb');
        $this->db->join('tbl_shift as s', 's.id = sb.shift_id', 'inner');
        $this->db->where('sb.archive', 0);
        $this->db->where('sb.shift_id', $shift_id);
        $this->db->where('sb.break_category', $break_cat);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shift_break_ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $shift_break_ids[] = $row->id;
            }
        }
        return $shift_break_ids;
    }

    /**
     * marks one of more shift break rows as archive
     */
    public function archive_shift_breaks($break_rows, $adminId) {
        foreach ($break_rows as $row) {
            if(isset($row['id']) && !empty($row['id'])) {
                $this->basic_model->update_records("shift_break",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $row['id']]);
            }
        }
    }

    /**
     * adding/updating shift breaks data of a given shift id into database
     * also removes them if they were previously added and not provided in the current lot
     */
    public function assign_shift_breaks($break_rows, $break_cat, $shift_id, $adminId) {
        # fetching existing shift break ids
        $existing_shift_break_ids = [];
        $existing_shift_break_ids = $this->get_shift_break_ids($shift_id, $break_cat);

        $selected_shift_break_ids = [];
        if($break_rows) {
            foreach ($break_rows as $row) {

                if(!isset($row['id']) || empty($row['id'])) {
                    # adding an entry of shift & break
                    $shift_break_id = $this->basic_model->insert_records("shift_break", [
                        "shift_id" => $shift_id,
                        "break_category" => $break_cat,
                        "break_type" => $row['break_type'],
                        "start_datetime" => isset($row['break_start_datetime'])?date('Y-m-d H:i:s', strtotime($row['break_start_datetime'])):null,
                        "end_datetime" => isset($row['break_end_datetime'])?date('Y-m-d H:i:s', strtotime($row['break_end_datetime'])):null,
                        "duration" => $row['duration'],
                        "duration_int" => $row['duration_int'],
                        "created" => DATE_TIME,
                        "created_by" => $adminId,
                        "archive" => 0]);
                }
                else {
                    $shift_break_id = $row['id'];
                    $selected_shift_break_ids[] = $row['id'];

                    # adding an entry of shift & break
                    $shift_break_id = $this->basic_model->update_records("shift_break", [
                        "break_type" => $row['break_type'],
                        "start_datetime" => isset($row['break_start_datetime'])?date('Y-m-d H:i:s', strtotime($row['break_start_datetime'])):null,
                        "end_datetime" => isset($row['break_end_datetime'])?date('Y-m-d H:i:s', strtotime($row['break_end_datetime'])):null,
                        "duration" => $row['duration'],
                        "duration_int" => $row['duration_int'],
                        "updated" => DATE_TIME,
                        "updated_by" => $adminId,
                        "archive" => 0], ["id" => $shift_break_id]);
                }

                # check $shift_break_id is not empty
                if (!$shift_break_id) {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                    return $response;
                }
            }
        }
        # any existing shift break ids that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_shift_break_ids, $selected_shift_break_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_sb_id) {
                $this->basic_model->update_records("shift_break",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $rem_sb_id]);
            }
        }

        $response = ['status' => true];
        return $response;
    }

    /**
     * it creates a blank break series for actual breaks to use in the create/update shift form
     * using the sheduled breaks data
     */
    function use_scheduled_breaks_in_actual($scheduled_rows) {
        if(!is_array($scheduled_rows))
            return;

        $return = null;
        foreach($scheduled_rows as $row) {
            $row->id = "";
            $row->break_category = 2;
            $row->break_duration = "";
            $row->break_start_time = "";
            $row->break_end_time = "";
            $row->timing_disabled = false;
            $row->duration_disabled = false;
            $return[] = object_to_array($row);
        }
        return $return;
    }

    /**
     * checking member availability using unavailability provided, overtime rules and shifts distance rules
     */
    public function is_member_available_between_datetimes($shift_id, $member_id, $start_datetime, $end_datetime, $shift_address) {
        $this->load->model('member/Member_model');
        $unavailable_msg = null;

        # checking already scheduled(accept) shifts, it should not overlap with current shift timings
        $exist_scheduled_shifts = $this->Schedule_model->check_accepted_shifts_overlap($shift_id, $member_id, $start_datetime, $end_datetime);
        if($exist_scheduled_shifts && $exist_scheduled_shifts['status'] == false) {
            $unavailable_msg[] = $exist_scheduled_shifts['error'];
        }

        # checks if any member's unavailability is falling between shift and its timings
        $unavailability_provided = $this->Member_model->check_unavailability_provided($member_id, $start_datetime, $end_datetime, $shift_id);
        if($unavailability_provided && $unavailability_provided['status'] == false) {
            $unavailable_msg[] = $unavailability_provided['error'];
        }

        # check shift overtime rules against member and shift timing
        $overtime_found = $this->Schedule_model->check_shift_overtime_rules($shift_id, $member_id, $start_datetime, $end_datetime);
        if($overtime_found && $overtime_found['status'] == false) {
            $unavailable_msg[] = $overtime_found['error'];
        }

        # check shift distance rules
        $distance_break_found = $this->Schedule_model->check_shift_distance_rules($shift_id, $member_id, $start_datetime, $end_datetime, $shift_address);
        if($distance_break_found && $distance_break_found['status'] == false) {
            $unavailable_msg[] = $distance_break_found['error'];
        }

        if(!empty($unavailable_msg))
            return ["status" => false, 'error' => implode(",",$unavailable_msg)];
        else
            return ["status" => true, "msg" => "member available"];
    }

    /**
     * checks if any accepted shifts of member is falling between shift and its timings
     */
    function check_accepted_shifts_overlap($shift_id, $member_id, $start_datetime, $end_datetime) {
        $select_column = ["s.shift_no"];
        $this->db->select($select_column);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('sm.archive', 0);
        $this->db->where('s.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);
        $this->db->where("((s.scheduled_start_datetime >= '{$start_datetime}' and s.scheduled_start_datetime < '{$end_datetime}') or (s.scheduled_end_datetime > '{$start_datetime}' and s.scheduled_end_datetime <= '{$end_datetime}') or (s.scheduled_start_datetime <= '{$start_datetime}' and s.scheduled_end_datetime >= '{$end_datetime}'))");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();

        if(!empty($result)) {
            $found_shifts = [];
            foreach ($result as $row) {
                $found_shifts[] = $row->shift_no;
            }
            return ["status" => false, "error" => "Shift is overlapping with accepted shift(s):".implode(",",$found_shifts)];
        }
        return ["status" => true, "msg" => "member available"];
    }

    /**
     * checking shifts distance rules using distance duration set OR finding the travel duration between accepted shifts vs currrent shifts against time duration between shifts
     */
    public function check_shift_distance_rules($shift_id, $member_id, $start_datetime, $end_datetime, $shift_address) {
        $rule_failure_msg = null;

        $google_distance_check = get_setting(Setting::GOOGLE_DURATION_CHECK_ALLOWED, GOOGLE_DURATION_CHECK_ALLOWED);
        if($google_distance_check == true) {
            # finding the timing duration between accepted and current shift using google api
            $valid_shifts_gap = $this->check_gap_between_shifts_of_member($shift_id, $member_id, $start_datetime, $end_datetime, $shift_address, true);
            return $valid_shifts_gap;
        }

        # finding the timing duration between accepted and current shift
        $valid_shifts_gap = $this->check_gap_between_shifts_of_member($shift_id, $member_id, $start_datetime, $end_datetime, $shift_address);
        return $valid_shifts_gap;
    }

    /**
     * checking shift overtime rules against member and shift timing
     */
    public function check_shift_overtime_rules($shift_id, $member_id, $start_datetime, $end_datetime) {

        # do not check any rules if overtime is set to allowed
        $overtime_allowed = get_setting(Setting::OVERTIME_ALLOWED, OVERTIME_ALLOWED);
        if($overtime_allowed == true) {
            return ["status" => true, "msg" => "member available"];
        }

        $rule_failure_msg = null;

        # is the member who is getting assigned not exceeding the weekly threshold?
        $valid_weekly_hours = $this->check_shift_durations_of_member($shift_id, $member_id, $start_datetime, $end_datetime);
        if($valid_weekly_hours && $valid_weekly_hours['status'] == false) {
            $rule_failure_msg[] = $valid_weekly_hours['error'];
        }

        # checking the shifts accepted by members against the shift getting assigned for same day
        $same_day_shift = $this->check_shifts_of_member_on_same_day($shift_id, $member_id, $start_datetime, $end_datetime);
        if($same_day_shift && $same_day_shift['status'] == false) {
            $rule_failure_msg[] = $same_day_shift['error'];
        }

        # checking the shifts accepted by members against the shift getting assigned for next/prev day
        $prevnext_day_shift = $this->check_shifts_of_member_on_prevnext_day($shift_id, $member_id, $start_datetime, $end_datetime);
        if($prevnext_day_shift && $prevnext_day_shift['status'] == false) {
            $rule_failure_msg[] = $prevnext_day_shift['error'];
        }

        # checking the shifts accepted by members against the shift getting assigned for overnight shifts
        $overnight_shift = $this->check_overnight_shifts_of_member($shift_id, $member_id, $start_datetime, $end_datetime);
        if($overnight_shift && $overnight_shift['status'] == false) {
            $rule_failure_msg[] = $overnight_shift['error'];
        }

        if(!empty($rule_failure_msg))
            return ["status" => false, 'error' => implode(",",$rule_failure_msg)];
        else
            return ["status" => true, "msg" => "member available"];
    }

    /**
     * finding the timing duration between accepted and current shift
     * returns false if it is less than threshold set with an error message
     * returns true if it is not exceeding and within the threshold set
     */
    public function check_gap_between_shifts_of_member($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $shift_address, $google_check = false) {

        $shifts_gap = get_setting(Setting::GAP_BETWEEN_SHIFTS, GAP_BETWEEN_SHIFTS);
        if($google_check == true)
        $shifts_gap = GAP_BETWEEN_SHIFTS_GOOGLE_CHECK;

        $this->db->select(["s.shift_no", "s.id", "ABS(TIMESTAMPDIFF(MINUTE, '{$shift_end_datetime}', s.scheduled_start_datetime)) AS incom_end_exist_start_gap", "ABS(TIMESTAMPDIFF(MINUTE, s.scheduled_end_datetime, '{$shift_start_datetime}')) AS exist_end_incom_start_gap"]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);
        $this->db->where("(
            (s.scheduled_start_datetime < ('".$shift_end_datetime."' + INTERVAL {$shifts_gap} MINUTE) AND s.scheduled_start_datetime > ('".$shift_start_datetime."' - INTERVAL {$shifts_gap} MINUTE))
            OR
            (s.scheduled_end_datetime < ('".$shift_end_datetime."' + INTERVAL {$shifts_gap} MINUTE) AND s.scheduled_end_datetime > ('".$shift_start_datetime."' - INTERVAL {$shifts_gap} MINUTE))
        )");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        $gap_found_msg = null;

        if(!empty($result)) {
            foreach ($result as $row) {
                $check_shift_id = $row['id'];
                $check_shift_no = $row['shift_no'];

                $shift_result = $this->get_shift_details($check_shift_id);
                $shift_details = $shift_result['data'];

                # skip checking the shifts travel duration if comparing shift and current conflicting shift address are exactly the same
                if($shift_details->owner_address == $shift_address) {
                    continue;
                }

                if($google_check == true) {
                    $incom_end_exist_start_gap = $row['incom_end_exist_start_gap'];
                    $exist_end_incom_start_gap = $row['exist_end_incom_start_gap'];

                    list($distance, $duration) = getDistanceBetweenTwoAddress($shift_address, $shift_details->owner_address);
                    if($duration > $incom_end_exist_start_gap || $duration > $exist_end_incom_start_gap) {
                        $gap_found_msg[] = $check_shift_no;
                    }
                }
                else {
                    $gap_found_msg[] = $check_shift_no;
                }
            }
        }

        if(!empty($gap_found_msg) && $google_check == true) {
            return ["status" => false, "error" => "Shift has less driving duration than shift gap with:".implode(",",$gap_found_msg)];
        }
        if(!empty($gap_found_msg)) {
            return ["status" => false, "error" => "Shift has less than {$shifts_gap} mins gap with:".implode(",",$gap_found_msg)];
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * fetching the overall travel KMs in current financial year
     */
    public function check_travel_kms_of_member($shift_id, $member_id, $shift_start_date, $shift_end_date, $actual_times = false, $completed_only_shifts = false) {

        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $col = "scheduled_travel";
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $col = "actual_travel";
        }
        $shift_month = date("n", strtotime($shift_start_date));
        if($shift_month <= 6) {
            $year_start = date("Y-m-01", strtotime('july previous year', strtotime($shift_start_date)));
        }
        else {
            $year_start = date("Y-m-01", strtotime('july this year', strtotime($shift_start_date)));
        }
        $year_end = date("Y-m-01", strtotime('july next year', strtotime($year_start)));

        $this->db->select(["sum(".$col.") AS total_travel"]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        if($completed_only_shifts)
            $this->db->where('s.status in (5) and s.id != '.$shift_id);
        else
            $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);
        $this->db->where("
            (s.".$start_col." >= '".$year_start." 00:00:00' and s.".$start_col." < '".$year_end." 00:00:00')
            ");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();

        $total_travel = 0;
        if($result) {
            $total_travel = $result[0]['total_travel'];
        }

        if($actual_times == true)
            return ["status" => true, "data" => $total_travel];
        else
            return ["status" => true, "msg" => $total_travel];
    }

    /**
     * fetching the overall duration of assigned shifts to a member for a given date and its week
     * returns false if it is exceeding with an error message
     * returns true if it is not exceeding and within the threshold
     */
    public function check_shift_durations_of_member($shift_id, $member_id, $shift_start_date, $shift_end_date, $actual_times = false, $completed_only_shifts = false, $exclude_ot = false) {

        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $break_cat = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $break_cat = 2;
        }
        $week_start = date("Y-m-d", strtotime('monday this week', strtotime($shift_start_date)));
        $week_end = date('Y-m-d', strtotime('saturday this week', strtotime($shift_start_date)));
        // $shift_end_date = date('Y-m-d', strtotime('2021-09-06'));
        $week_end = date('Y-m-d', strtotime('next monday', strtotime($shift_end_date)));
        $shift_start_day = date('l', strtotime($shift_start_date));
        $shift_end_day = date('l', strtotime($shift_end_date));
        $shift_end_time = date('H:i', strtotime($shift_end_date));
        $week_end_time = date('H:i', strtotime('10:00:00'));

        # If shit ends in monday before or on 10:00 AM
        if ($shift_end_day == 'Monday') {
            if (strtotime($week_end_time) > strtotime($shift_end_time) || strtotime($week_end_time) == strtotime($shift_end_time)) {
                $week_start = date("Y-m-d", strtotime('previous monday', strtotime($shift_end_date)));
                $week_end = date('Y-m-d', strtotime('monday this week', strtotime($shift_end_date)));
            } else {
                $week_start = date("Y-m-d", strtotime('monday this week', strtotime($shift_end_date)));
                $week_end = date('Y-m-d', strtotime('next monday', strtotime($shift_end_date)));
            }
        } else {
            $week_start = date("Y-m-d", strtotime('monday this week', strtotime($shift_start_date)));
            $week_end = date('Y-m-d', strtotime('next monday', strtotime($shift_start_date)));
        }
        
        $this->db->select(["
            (TIMESTAMPDIFF(MINUTE, s.".$start_col.", s.".$end_col.")/60) -
		    (SELECT IFNULL(SUM(duration_int)/60,0) FROM tbl_shift_break sb, tbl_references r WHERE r.id = sb.break_type and r.key_name in ('unpaid','sleepover') and sb.shift_id = s.id AND sb.archive = 0 AND sb.break_category = ".$break_cat.")
             AS hour_diff",'r.name as rolelabel']);

        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        if($completed_only_shifts)
            $this->db->where('s.status in (5) and s.id != '.$shift_id);
        else
            $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);
        $this->db->where("
            ((s.".$start_col." >= '".$week_start." 10:00:00' or s.".$end_col." >= '".$week_start." 10:00:00') and s.".$end_col." <= '".$week_end." 10:00:00')
            ");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        
        $week_hours = 0;
        if($result) {
            #Minimum unit adjust for ndis non ndis total week hours calculation            
            foreach($result as $res) {
                $roundoff_val = strtolower($res['rolelabel']) == 'ndis' ? 2 : 3;
                $week_hours += ($res['hour_diff'] < $roundoff_val) ? $roundoff_val : $res['hour_diff'];            
            }
            
            #Fetch same week shift Over Time units only on completing the shift
            if($exclude_ot) {
                $existing_ot_hrs = $this->get_member_weekly_ot_duration($member_id, $shift_id, $start_col, $end_col, $week_start, $week_end);                      
                #Reduce the OT values from week hours 
                $week_hours = $week_hours - $existing_ot_hrs;
                
            }
        }

        # calculating total shift hours and individual break hours based on break category
        $shift_result = $this->get_shift_details($shift_id);
        $shift_details = (array) $shift_result['data'];
        $shift_hours_details = $this->calc_total_shift_ind_break_hours($shift_details, $break_cat);
        list($total_shift_hours, $total_mins, $total_sleepover_minutes, $total_unpaid_minutes, $total_paid_minutes, $weekday_minutes, $saturday_minutes, $sunday_minutes) = $shift_hours_details;

        # adding the shift hours to weekly hours found
        $total_member_hours = $week_hours + round(($total_shift_hours / 60),2);
        
        if($actual_times == true)
            return ["status" => true, "used_weekly_hours" => $week_hours, "data" => $total_member_hours];

        if($total_member_hours > WEEKLY_MAX_MEMBER_HOURS) {
            return ["status" => false, "error" => "Can't accept shift because it will require overtime"];
        }
        return ["status" => true, "used_weekly_hours" => $week_hours, "msg" => $total_member_hours];
    }

    /**
     * checking the shifts accepted by members against the shift getting assigned for S/O shifts
     * returns false if the gap is more than the set threshold
     * returns true if it is not exceeding and within the threshold
     */
    public function check_overnight_shifts_of_member($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times = false, $completed_only_shifts = false) {

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }

        # if the current shift has sleep over then comparing against shifts should be on same day
        # else comparing shifts should have S/O
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $shift_type, "sleepover");

        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));
        $shifts_gap = GAP_BETWEEN_SO_SHIFTS;

        $this->db->select(["s.shift_no", "(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) as gap_start", "(TIMESTAMPDIFF(MINUTE, s.".$end_col.", '".$shift_start_datetime."')/60) as gap_end"]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);

        if($completed_only_shifts)
            $this->db->where('s.status in (5) and s.id != '.$shift_id);
        else
            $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);

        # either comparing shift or comparing against shifts should be S/O shift
        if(!empty($sleepover_breaks))
            $this->db->where("s.id not in (select shift_id from tbl_shift_break sb, tbl_references r where sb.break_category = {$shift_type} and sb.break_type = r.id and r.key_name = 'sleepover' and sb.archive = 0 and sb.shift_id = s.id)");
        else
            $this->db->where("s.id in (select shift_id from tbl_shift_break sb, tbl_references r where sb.break_category = {$shift_type} and sb.break_type = r.id and r.key_name = 'sleepover' and sb.archive = 0 and sb.shift_id = s.id)");

        $this->db->where("(
            (s.".$start_col." < ('".$shift_end_datetime."' + INTERVAL {$shifts_gap} MINUTE) AND s.".$start_col." > ('".$shift_start_datetime."' - INTERVAL {$shifts_gap} MINUTE))
            OR
            (s.".$end_col." < ('".$shift_end_datetime."' + INTERVAL {$shifts_gap} MINUTE) AND s.".$end_col." > ('".$shift_start_datetime."' - INTERVAL {$shifts_gap} MINUTE))
        )");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        $gap_found_msg = null;
        // last_query();

        if(!empty($result)) {
            $check_shift_no = $result[0]['shift_no'];
            $gap_found_msg[] = $check_shift_no;

            if($actual_times == true) {
                $max_gap = 0;
                if($result[0]['gap_start'] > 0 && $result[0]['gap_start'] > $result[0]['gap_end'])
                    $max_gap = $result[0]['gap_start'];
                if($result[0]['gap_end'] > 0 && $result[0]['gap_end'] > $result[0]['gap_start'])
                    $max_gap = $result[0]['gap_end'];

                return ["status" => true, "data" => $max_gap];
            }

            if(!empty($gap_found_msg)) {
                return ["status" => false, "error" => "Shift has less than ".($shifts_gap/60)." hrs gap with S/O shift(s):".implode(",",$gap_found_msg)];
            }
        }
        return ["status" => true, "msg" => "ok"];
    }

    /**
     * Pull the list of previously completed shift for checking break gaps
     */
    public function check_breaks_between_shifts_of_member($shift_details, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times = false, $completed_only_shifts = false) {
        $shift_id = $shift_details['id'];
        $created = $shift_details['created'];

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }

        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));
        $shifts_gap = SAME_DAY_SHIFTS_BREAK_GAP * 60;

        $this->db->select(["s.id", $start_col, $end_col]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.created <=', $created);

        #Pull the current shift also for checking reference shift or not
        if($completed_only_shifts)
            $this->db->where('s.status = 5 or s.id = '.$shift_id);

        $this->db->order_by("s.id", "DESC");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        $return = ["status" => FALSE, "msg" => 'No shift found'];
        
        if(!empty($result)) {
            $return = $this->get_overtime($result, $start_col, $end_col, $shift_details);
        }

        return $return;
    }
    
    /**
     * Get Over time 2.0 for weekdays and non PH
     * 
     * @param $result {array} List of shifts which is fall previously from current shift
     * @param $start_col {string} scheduled_start_datetime / actual_start_datetime
     * @param $end_col {string} scheduled_end_datetime / actual_end_datetime
     * @param $shift_details {array} details of the current shift
     * 
     * @return $status {array} status with or with out reference date
     */
    public function get_overtime($result, $start_col, $end_col, $shift_details) {
        $current_shift_ref = $ref_shift_sleepover = $only_two_days = FALSE;
        $shift_id = $shift_details['id'];
        $min_duration = $shift_details['rolelabel'] == 'NDIS' ? 120 : 180;
        $prev_start_time = $prev_end_time = $second_prev_start_time = $overtime_20 = 0;
        $reference_date = '';
        
        $sleepover = array_key_exists('actual_sleepover_rows', $shift_details) ? TRUE : FALSE;
        
        foreach($result as $key => $data) {
            
            if($prev_start_time != 0 ) {
                
                $shift_gap = minutesDifferenceBetweenDate($data[$end_col], $prev_start_time); 
                    
                #If current shift is sleep over shift and previous shift gap is lessthan 8 hour means then mark as current shift is Reference shift and full hours is OT 2.0
                if($sleepover && $shift_gap < GAP_BETWEEN_SO_SHIFTS) {
                    $current_shift_ref = TRUE;
                    $reference_date = $prev_start_time;
                    break;
                }
                
                //Pull the list of unpaid, sleepover break
                $actual_breaks = $this->get_shift_breaks_list($data['id'], 2);
                $is_sleepover_break = FALSE;
                //If shift has sleepover break type then add the flag for sleepver check
                if($actual_breaks) {
                    foreach($actual_breaks as $breaks) {                       
                        if($breaks->key_name == 'sleepover') {
                            $is_sleepover_break = TRUE;
                        }
                    }
                }

                if($is_sleepover_break && $shift_gap < GAP_BETWEEN_SO_SHIFTS ) {
                    /** If Current shift is non S/O and next immediate prev shift is S/O shift and gaps is fall less than 8 then mark as current shift is Reference shift and full hours is OT 2.0 **/
                    $reference_date = $data[$start_col];
                    if($key == 1) {
                        $current_shift_ref = TRUE; 
                        break;
                    }   
                    $ref_shift_sleepover = TRUE;
                    break;
                }
                //Reset OT 2.0 If shift has proper shift gap and non sleepover shift
                else if(round($shift_gap / 60 , 2) >= SAME_DAY_SHIFTS_BREAK_GAP) {
                    $reference_date = $prev_start_time;
                    break;
                }
            }
            $prev_start_time = $data[$start_col];
        } 
        
        /** If reference shift is marks as S/O shift and if non sleep over shift available between the current shift and S/O shift means then check the gap between current and previous shift 8 hr instead 10 hr **/
        if($ref_shift_sleepover) {
            foreach($result as $data) {
                
                if($second_prev_start_time != 0 ) {
                    $shift_gap = minutesDifferenceBetweenDate($data[$end_col], $second_prev_start_time);
                    #If reference shift is marks as S/O shift and gaps greater than 8 hour means then mark as current shift is Reference shift no OT 2.0
                    if($shift_gap >= GAP_BETWEEN_SO_SHIFTS) {
                        $reference_date = $second_prev_start_time;
                        break;
                    }  #If reference shift is marks as S/O shift and gaps less than 8 hour means then mark as current shift full hours is OT 2.0
                    else if($data[$start_col] == $reference_date && $shift_gap < GAP_BETWEEN_SO_SHIFTS) {                       
                        $reference_date = $data[$end_col];                   
                        $current_shift_ref = TRUE;
                        break;
                    }              
                }
                $second_prev_start_time =  $data[$start_col];
            }
        }
        $status = ['status' => FALSE, 'msg' => 'No Reference date'];
        
        #If current shift is sleep over shift and previous shift gap is lessthan 8 hour means full shift consider as OT 2.0
        if($current_shift_ref) {
            $overtime_20 = minutesDifferenceBetweenDate($shift_details[$start_col], $shift_details[$end_col]);
            $status = ['status' => TRUE, 'data' => round($overtime_20 / 60, 2), 'is_full_ot' => TRUE, 'reference_date' => $reference_date];
        } #If not able to find ref shift means then set last shift as a reference shift
        else if(!$ref_shift_sleepover && count($result) > 1 && !$reference_date) { 
           
            $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($prev_start_time))); 
            
            #If shift starts before 12 hrs and ends with after 12 hrs from reference shift
            if($shift_details['actual_start_date'] < $ot_span_time && $shift_details['actual_end_date'] > $ot_span_time) {
                $overtime_20 = minutesDifferenceBetweenDate($ot_span_time, $shift_details[$end_col]);
                $active_hrs = minutesDifferenceBetweenDate($shift_details[$start_col], $shift_details[$end_col]);
                #Adjust OT hrs baseed on minimum units calculations
                if($active_hrs < $min_duration) {
                    $day_hrs = minutesDifferenceBetweenDate($shift_details[$start_col], $ot_span_time);
                    $overtime_20 = $min_duration - $day_hrs;
                }
                $status = ['status' => TRUE, 'data' => round($overtime_20 / 60, 2), 'is_full_ot' => FALSE, 'reference_date' => $prev_start_time];
            }
            else if($shift_details['actual_end_date'] > $ot_span_time) {
                $overtime_20 = minutesDifferenceBetweenDate($shift_details[$start_col], $shift_details[$end_col]);
                $status = ['status' => TRUE, 'data' => round($overtime_20 / 60, 2), 'is_full_ot' => TRUE, 'reference_date' => $prev_start_time];
            } else {
                $status = ['status' => TRUE, 'data' => TRUE, 'is_full_ot' => FALSE, 'reference_date' => $prev_start_time];
            }
        }
        #If current date is Reference date then no OT
        else if(!$ref_shift_sleepover && $reference_date && $reference_date == $shift_details[$start_col]) {
            $status = ['status' => TRUE, 'data' => "current_shift", 'is_full_ot' => FALSE, 'reference_date' => $reference_date];
        }
        //Skip if reference date and is same as current shift start time
        else if($reference_date && $reference_date != $shift_details[$start_col]) {
            $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($reference_date)));
            //Shift falls completly after over time span hours means fully consider as Overtime
            if($shift_details[$start_col] > $ot_span_time) {
                $overtime_20 = minutesDifferenceBetweenDate($shift_details[$start_col], $shift_details[$end_col]);
                
                #overwrite with minimum unit values for minimum unit adjusted values
                if($min_duration > $overtime_20) {
                    $overtime_20 = $min_duration;
                }

                $status = ['status' => TRUE, 'data' => round($overtime_20 / 60, 2), 'is_full_ot' => FALSE, 'reference_date' => $reference_date];
            }            
            else if($shift_details[$end_col] > $ot_span_time) {
                $time = minutesDifferenceBetweenDate($ot_span_time, $shift_details[$end_col]);         
                $overtime_20 = $time > 0 ? round($time / 60, 2) : 0;
                
                #overwrite with minimum unit values for minimum unit adjusted values
                if($min_duration > $overtime_20) {
                    $overtime_20 = $min_duration;
                }
                
                $status = ['status' => TRUE, 'data' => $overtime_20, 'is_full_ot' => FALSE , 'reference_date' => $reference_date];
            }            
            
        }
        return $status;
    }    
   
    /**
     * checking the shifts accepted by members against the shift getting assigned for next/prev day
     * without S/O
     * returns false if the gap is more than the set threshold
     * returns true if it is not exceeding and within the threshold
     */
    public function check_shifts_of_member_on_prevnext_day($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times = false, $completed_only_shifts = false, $allow_sleepover = false) {

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }

        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));

        # shift doesn't have to have S/O
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $shift_type, "sleepover");
        if(!empty($sleepover_breaks) && !$allow_sleepover)
        return ["status" => true, "data" => ""];

        $next_date = date('Y-m-d', strtotime($shift_start_datetime . '+1 day'));
        $prev_date = date('Y-m-d', strtotime($shift_start_datetime . '-1 day'));
        $gap = NEXT_DAY_SHIFTS_GAP;

        $this->db->select(["s.shift_no", "(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) as gap_start", "(TIMESTAMPDIFF(MINUTE, s.".$end_col.", '".$shift_start_datetime."')/60) as gap_end"]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        if($completed_only_shifts) {
            $this->db->where('s.status in (5) and s.id != '.$shift_id);
        }
        else {
            $this->db->where("DATE(s.".$start_col.") = DATE(s.".$end_col.")");
            $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);
        }
        $this->db->where("DATE(s.".$start_col.") IN ('".$next_date."','".$prev_date."')");
        $this->db->where("(
        (
            (TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) < {$gap}
            AND
            (TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) > 0
        )
        OR
        (
            (TIMESTAMPDIFF(MINUTE, s.".$end_col.", '".$shift_start_datetime."')/60) < {$gap} AND
            (TIMESTAMPDIFF(MINUTE, s.".$end_col.", '".$shift_start_datetime."')/60) > 0
        )
        )");
        $this->db->order_by("gap_start", "DESC");
        $this->db->order_by("gap_end", "DESC");
        $this->db->limit("1");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        if(!empty($result)) {
            if($actual_times == true) {
                $max_gap = 0;
                if($result[0]['gap_start'] > 0 && $result[0]['gap_start'] > $result[0]['gap_end'])
                    $max_gap = $result[0]['gap_start'];
                if($result[0]['gap_end'] > 0 && $result[0]['gap_end'] > $result[0]['gap_start'])
                    $max_gap = $result[0]['gap_end'];

                return ["status" => true, "data" => $max_gap];
            }

            return ["status" => false, "error" => "Shift overtimes with next/prev day shift:".$result[0]['shift_no']];
        }
        $return = ["status" => true, "data" => ''];
        return $return;
    }

    /**
     * checking the shifts accepted by members against the shift getting assigned for same day
     * returns false if the gap is more than the set threshold
     * returns true if it is not exceeding and within the threshold
     */
    public function check_shifts_of_member_on_same_day($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times = false, $completed_only_shifts = false) {

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }

        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));
        $gap = SAME_DAY_SHIFTS_GAP;

        # shift doesn't have to have S/O
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $shift_type, "sleepover");
        if(!empty($sleepover_breaks))
        return ["status" => true, "msg" => "ok"];

        $this->db->select(["s.shift_no", "ABS(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) as gap_start", "ABS(TIMESTAMPDIFF(MINUTE, '".$shift_start_datetime."', s.".$end_col.")/60) as gap_end", $start_col, $end_col]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);

        # comparing against shifts should not have S/O breaks
        $this->db->where("s.id not in (select shift_id from tbl_shift_break sb, tbl_references r where sb.break_category = {$shift_type} and sb.break_type = r.id and r.key_name = 'sleepover' and sb.archive = 0 and sb.shift_id = s.id)");

        # the comparing against shifts have to be on same day
        $this->db->where("DATE(s.".$start_col.") = DATE(s.".$end_col.")");
        $this->db->where("DATE(s.".$start_col.")", $shift_start_date);
        $this->db->where("(
        ABS(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) > {$gap}
        OR
        ABS(TIMESTAMPDIFF(MINUTE, '".$shift_start_datetime."', s.".$end_col.")/60) > {$gap}
        )
        ");

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        
        if(!empty($result)) {

            if($actual_times == true) {
                $max_gap = 0;
                if($result[0]['gap_start'] > 0 && $result[0]['gap_start'] > $result[0]['gap_end'])
                    $max_gap = $result[0]['gap_start'];
                if($result[0]['gap_end'] > 0 && $result[0]['gap_end'] > $result[0]['gap_start'])
                    $max_gap = $result[0]['gap_end'];

                return ["status" => true, "data" => $max_gap];
            }

            return ["status" => false, "error" => "Shift overtimes with same day shift:".$result[0]['shift_no']];
        }
        return ["status" => true, "msg" => "ok"];
    }

    public function check_shifts_ot_on_same_day($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times) {

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }

        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));
        $gap = SAME_DAY_SHIFTS_GAP;

        # shift doesn't have to have S/O
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $shift_type, "sleepover");
        if(!empty($sleepover_breaks))
        return ["status" => true, "msg" => "ok"];

        $this->db->select(["s.id", $start_col, $end_col]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (3,4,5,6,7)');

        # comparing against shifts should not have S/O breaks
        $this->db->where("s.id not in (select shift_id from tbl_shift_break sb, tbl_references r where sb.break_category = {$shift_type} and sb.break_type = r.id and r.key_name = 'sleepover' and sb.archive = 0 and sb.shift_id = s.id)");

        # the comparing against shifts have to be on same day
        $this->db->where("DATE(s.".$start_col.") = DATE(s.".$end_col.")");
        $this->db->where("DATE(s.".$start_col.")", $shift_start_date);               

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();

        $overtime_20 = 0;
        $single_day = ['status' => FALSE, 'No OT 2.0'];
        if(count($result) > 1) {
            $single_day = $this->check_single_day_ot($result, $start_col, $end_col, $shift_id);
            
        }
        return $single_day;
    }

    public function check_single_day_ot($result, $start_col, $end_col, $shift_id) {
        #Same day first shift start time
        $presidence_shift_start_time = $result[0][$start_col];
        $presidence_shift_end_time = $result[0][$end_col];
        $is_ot_check = FALSE;
        $prev_end_time = $overtime_20 = 0;
        
        $ot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($presidence_shift_start_time)));
        $day_week = date('D', strtotime($presidence_shift_start_time));
        
        if($day_week == 'sunday') {
            return ['status' => FALSE, 'msg' => 'Shift falls on sunday'];
        }
        
        foreach($result as $key => $data) {
            //If Reference and current shift id is same then skip to check OT
            if($result[0]['id'] == $shift_id) {
                break;
            }
            if($data['id'] == $shift_id) {                
                // minutesDifferenceBetweenDate($presidence_shift_start_time, $data[$end_col])
                $otspan = minutesDifferenceBetweenDate($presidence_shift_start_time, $data[$end_col]);
                
                if($otspan > SAME_DAY_SHIFTS_GAP) {
                    $is_ot_check = TRUE;
                    switch ($day_week) {
                        case 'saturday':
                            $day = 'saturday';
                            break;
                       
                        default:
                            $day = 'mon_fri';
                            break;
                    }

                    #If shift starts after 12 hrs of span means all the units will be OT 
                    $overtime_20 = minutesDifferenceBetweenDate($data[$start_col], $data[$end_col]);
                    ${$day} = 0;
                    #If shift starts befor 12 hrs of span and end with after 12 hrs means all the units will be OT                   
                    if($data[$start_col] < $ot_span_time) {
                        ${$day} = minutesDifferenceBetweenDate($data[$start_col], $ot_span_time);
                        $overtime_20 =  $overtime_20 - ${$day};                        
                    } else if($data[$start_col] < $ot_span_time){
                        ${$day} = $overtime_20;
                        $overtime_20 =  0;
                    }
                }
            }   
        }

        if($is_ot_check) {
            foreach($result as $key => $data) {
                if($prev_end_time != 0) {
                    $breakgap = minutesDifferenceBetweenDate($prev_end_time, $data[$start_col]);                   
                    //Reset OT 2.0 If shift has proper shift gap
                    if(round($breakgap / 60 , 2) >= SAME_DAY_SHIFTS_BREAK_GAP) {
                        ${$day} = $overtime_20;
                        $overtime_20 = 0;
                    }
                }         
                $prev_end_time = $data[$end_col];                
            }
        }

        if($is_ot_check) {
            $data = ['day_name' => $day, 'ot2.0' => $overtime_20, 'day_duration' => ${$day}];
            $return = ['status' => TRUE, 'data' => $data];
        } else {
            $return = ['status' => FALSE, 'msg' => 'No OT'];
        }
        return $return;
    }
    /**
     * function used for assigning one or more members to a shift
     */
    public function assign_shift_members($reqData, $adminId) {

        # validating is atleast one member has been selected
        $valid = false;
        $shift_members = [];
        if(isset($reqData['shift_members']))
        foreach ($reqData['shift_members'] as $obj) {
            if(isset($obj->selected) && !empty($obj->selected)) {
                $valid = true;
                $shift_members[] = $obj->id;
            }
        }

        if(!$valid) {
            $response = [
                "status" => false,
                "error" => "Please select atleast one member to assign to the shift" ];
            return $response;
        }
        if(empty($reqData['shift_id'])) {
            $response = [
                "status" => false,
                "error" => "Shift id is missing" ];
            return $response;
        }

        # is the object being altered currently by other user? if yes - cannot perform this action
        $lock_taken = $this->add_shift_lock($reqData['shift_id'], $adminId);
        if($lock_taken['status'] == false)
        return $lock_taken;

        # does the shift exist?
        $shift_id = $reqData['shift_id'];
        $shiftresult = $this->get_shift_details($shift_id);
        if (empty($shiftresult)) {
            # removing any access level locks taken by the admin user
            $shift_lock_res = $this->remove_shift_lock($reqData["shift_id"], $adminId);
            if($shift_lock_res['status'] == false)
                return $shift_lock_res;

            $response = ['status' => false, 'error' => "Shift does not exist anymore."];
            return $response;
        }

        foreach ($shift_members as $member_id) {
            # checking member availability using unavailability provided, overtime rules and shifts distance rules
            $member_unavailable_det = $this->is_member_available_between_datetimes($shift_id, $member_id, $shiftresult['data']->scheduled_start_datetime, $shiftresult['data']->scheduled_end_datetime, $shiftresult['data']->owner_address);
            if($member_unavailable_det && $member_unavailable_det['status'] == false) {
                # removing any access level locks taken by the admin user
                $shift_lock_res = $this->remove_shift_lock($reqData["shift_id"], $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                return $member_unavailable_det;
            }
        }

        # fetching existing shift member ids
        $existing_shift_member_ids = $this->get_shift_member_ids($reqData['shift_id']);

        $selected_shift_member_ids = [];
        foreach ($shift_members as $member_id) {

            # donot insert if the assignment already exists
            $assigned_details = $this->basic_model->get_row('shift_member', ['id'], ["shift_id" => $reqData["shift_id"],"member_id" => $member_id, "archive" => 0]);
            if ($assigned_details) {
                $selected_shift_member_ids[] = $assigned_details->id;
                continue;
            }

            # adding an entry of shift & member
            $shift_member_id = $this->basic_model->insert_records("shift_member", ["shift_id" => $reqData["shift_id"],"member_id" => $member_id, "created" => DATE_TIME, "created_by" => $adminId, "archive" => 0]);

            # check $shift_member_id is not empty
            if (!$shift_member_id) {
                # removing any access level locks taken by the admin user
                $shift_lock_res = $this->remove_shift_lock($reqData["shift_id"], $adminId);
                if($shift_lock_res['status'] == false)
                    return $shift_lock_res;

                $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                return $response;
            }
        }

        # any existing shift member ids that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_shift_member_ids, $selected_shift_member_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_sm_id) {
                $this->basic_model->update_records("shift_member",
                ["archive" => true, "updated" => DATE_TIME, "updated_by" => $adminId],
                ["id" => $rem_sm_id]);
            }
        }

        # removing any access level locks taken by the admin user
        $shift_lock_res = $this->remove_shift_lock($reqData["shift_id"], $adminId);
        if($shift_lock_res['status'] == false)
            return $shift_lock_res;

        $msg = 'Shift support worker have been assigned successfully.';
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * inserts or updates the shift record in the database
     */
    function populate_shift($data, $adminId,$uuid_user_type, $all_data = true, $primarId = NULL, $portal = false) {
        $id = $data['id'] ?? 0;
        $postdata1 = [];
        if($all_data == true) {
            $postdata1 = [
                "shift_no" => $data['shift_no'] ?? '',
                "person_id" => ((isset($data['contact_id']) && $data['contact_id'] > 0)?$data['contact_id']:null),
                "account_id" => $data['account_id'],
                "account_type" => $data['account_type'],
                "account_address" => $data['account_address']??'',
                "owner_id" => ((isset($data['owner_id']) && $data['owner_id'] > 0)?$data['owner_id']:null),
                "role_id" => $data['role_id'],
                "scheduled_start_datetime" => date("Y-m-d H:i:s", strtotime($data['scheduled_start_datetime'])),
                "scheduled_end_datetime" => date("Y-m-d H:i:s", strtotime($data['scheduled_end_datetime'])),
                "scheduled_travel" => ($data['scheduled_travel']>0?$data['scheduled_travel']:null),
                "scheduled_reimbursement" => (isset($data['scheduled_reimbursement']) && $data['scheduled_reimbursement'] >0)?$data['scheduled_reimbursement']:null,
                "scheduled_travel_duration" => (isset($data['scheduled_travel_duration']) && $data['scheduled_travel_duration']) ? $data['scheduled_travel_duration'] : null,
                "scheduled_travel_distance" => (isset($data['scheduled_travel_distance']) && $data['scheduled_travel_distance']) ? $data['scheduled_travel_distance'] : null,
                "status" => $data['status'],
                "description" => $data['description'],
                "scheduled_duration" => isset($data['scheduled_duration']) ? $data['scheduled_duration'] : '',
                "actual_duration" => isset($data['actual_duration']) ? $data['actual_duration'] : '',
                "email" => array_key_exists('contact_email', $data) ? $data['contact_email'] : '' ,
                "phone" => array_key_exists('contact_phone', $data) ? $data['contact_phone'] : '',
                "archive" => 0,
                "repeat_option" => (isset($data['repeat_option']) && $data['repeat_option'] > 0) ? $data['repeat_option'] : '',
                "primary_shift_id" => (isset($data['primary_shift_id']) && $data['primary_shift_id'] > 0) ? $data['primary_shift_id'] : '',
                "repeat_specific_days" => isset($data['repeat_specific_days']) ? $data['repeat_specific_days'] : '',
                "scheduled_support_type" => !empty($data['scheduled_support_type']) ? $data['scheduled_support_type'] : NULL,
                "scheduled_docusign_id" => !empty($data['scheduled_docusign_id'])? $data['scheduled_docusign_id'] : NULL,
                'scheduled_sb_status'=>!empty($data['scheduled_sb_status'])?$data['scheduled_sb_status']:0,
                'actual_sb_status'=>!empty($data['actual_sb_status'])?$data['actual_sb_status']:0,
            ];
            if (array_key_exists('scheduled_sa_id', $data) && $data['scheduled_sa_id'] != '') {
                $postdata1['scheduled_sa_id'] = $data['scheduled_sa_id'];
            } else {
                $postdata1['scheduled_sa_id'] = null;
            }

            if (array_key_exists('roster_id', $data) && $data['roster_id'] != '') {
                $postdata1['roster_id'] = $data['roster_id'];
            }
            if ($primarId != NULL && $primarId != '') {
                $postdata1['primary_shift_id'] = $primarId;
            }
        }

        //Update the Shift status for goal tracking
        if(!empty($data['status'])) {
            $postdata1['status'] = $data['status'];
        }

        if (isset($data['actual_support_type']) && !empty($data['actual_support_type']) && $data['actual_support_type'] == 'null') {
            $data['actual_support_type'] = NULL;
        }

        $postdata2 = [
            "actual_sa_id" => !empty($data['actual_sa_id']) ? $data['actual_sa_id'] : NULL,
            "actual_support_type" => !empty($data['actual_support_type']) ? $data['actual_support_type'] : NULL,
            "actual_docusign_id" => !empty($data['actual_docusign_id'])? $data['actual_docusign_id'] : NULL,
            "actual_start_datetime" => ($data['actual_start_datetime']) ? date("Y-m-d H:i:s", strtotime($data['actual_start_datetime'])):null,
            "actual_end_datetime" =>  ($data['actual_end_datetime']) ? date("Y-m-d H:i:s", strtotime($data['actual_end_datetime'])):null,
            "actual_duration" => isset($data['actual_duration']) ? $data['actual_duration'] : '',
            "actual_travel" => ($data['actual_travel']>0?$data['actual_travel']:null),
            "actual_reimbursement" => (isset($data['actual_reimbursement']) && $data['actual_reimbursement']>0)?$data['actual_reimbursement']:null,
            "actual_travel_duration" => (isset($data['actual_travel_duration']) && $data['actual_travel_duration']) ? $data['actual_travel_duration'] : null,
            "actual_travel_distance" => (isset($data['actual_travel_distance']) && $data['actual_travel_distance']) ? $data['actual_travel_distance'] : null,
            "notes" => $data['notes'],
        ];
        $postdata = array_merge($postdata1, $postdata2);

        if ($id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("shift", $postdata, ["id" => $id]);
        } else {
            $postdata["created"] = DATE_TIME;
            $postdata["created_by"] = $adminId;
            $id = $this->basic_model->insert_records("shift", $postdata, $multiple = FALSE);
        }

        $role_id = 0;
        if ($id) {
            $shiftDetails = $this->get_shift_details($id, $clone = false, $from_portal = false, $shift_lock = false, $adminId,$uuid_user_type);
            if (!empty($shiftDetails['data'])) {
                $role_id = $shiftDetails['data']->role_id ?? 0; 
            }
        } else {
            $role_id = $data['role_id'];
        }

        $servive_type = $this->basic_model->get_row('member_role', ['name'], ['id' => $role_id]);

        //Populate ndis line items only for ndis service type
        if (array_key_exists('account_type', $data) && $data['account_type'] == 1 && strtolower($servive_type->name) == 'ndis') {
            # NDIS service agreement line type
            $this->populate_ndis_line_items($data, $id, $adminId, $portal);

            $support_type = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $data['scheduled_support_type']]);
            if (isset($support_type) && !empty($support_type) && strtolower($support_type->key_name) == 'mixed') {
                # NDIS Mixed Support Duration Save 
                $this->Schedule_ndis_model->populate_ndis_suport_type_duration($data, $id, $adminId, $portal);
            } else {
                $updateWhere = [ 'shift_id' => $id ];
                $this->update_records('shift_ndis_support_duration', array('archive' => '1'), $updateWhere);
            }
        } else {
            $updateWhere = [ 'shift_id' => $id ];
            $this->update_records('shift_ndis_line_item', array('archive' => '1'), $updateWhere);
        }

        $data['adminId'] = $adminId;
        $temp_attachments = "";
        if (!empty($reqData->attachments)) {
            $temp_attachments = $reqData->attachments;
        }

        // upload attachment to permanaent folder for portal shift
        if(!empty($data['member_id'])){
            $response = $this->Schedule_attachment_model->upload_shift_attachement($id, $temp_attachments, $data['member_id']);
        }
        
        return $id;
    }

    

     /**
     * Save ndis line items
     * @param {array} $data
     * @param {int} $shift_id
     * @param {int} $adminId
     */
    public function populate_ndis_line_items($data, $shift_id, $adminId, $portal) {
        if (isset($data['id']) && $data['id'] != '' && $data['id'] != 0) {
            $id = $data['id'];
        } else {
            $id = $shift_id;
        }
        
        $updateWhere = [ 'shift_id' => $id ];
        if ($portal) {
            $updateWhere  = ['shift_id' => $id, 'category' => 2];
        }

        $this->update_records('shift_ndis_line_item', array('archive' => '1'), $updateWhere);

        //IF NDIS Shift doesn't have any line items then its marked as a invoice eligible as false
        $not_be_invoiced = 0;
        if (empty($data['actual_docusign_id']) || $data['actual_docusign_id'] == '') {
            $not_be_invoiced = 1;
        }
        if (isset($data['actual_missing_line_item']) == true && $data['actual_missing_line_item'] == true) {
            $not_be_invoiced = 1;
        }
        if(array_key_exists('actual_support_type', $data) && $data['actual_support_type'] && count($data['actual_ndis_line_item_list']) == 0) {
            $not_be_invoiced = 1;
        }

        if (isset($data['actual_docusign_id']) && !empty($data['actual_docusign_id'])) {
            $service_agreement_id = $data['actual_sa_id'] ?? '';
            $docusign_id = $data['actual_docusign_id'] ?? '';
            $managed = [ 1 , 2 ];

            $this->load->model('../../sales/models/ServiceAgreement_model');
            # get payment methods
            $serviceAgreementPayments = $this->ServiceAgreement_model->service_agreement_payments($service_agreement_id);

            if (!empty($serviceAgreementPayments['managed_type']) && in_array($serviceAgreementPayments['managed_type'], $managed)) {
                $this->load->model('../../sales/models/Service_booking_model');
                $serviceBooking = $this->Service_booking_model->get_service_booking_with_status_by_id($service_agreement_id, '', $docusign_id);
                $sa_b_in = 0;

                if (empty($serviceBooking) || $serviceBooking == '') {
                    $not_be_invoiced = 1;
                }

                foreach($serviceBooking as $record) {
                    if ($record['status'] != 'active') {
                        $not_be_invoiced = 1;
                        break;
                    }
                    if ($record['is_received_signed'] != '1') {
                        $not_be_invoiced = 1;
                        break;
                    }
                }
            }
        }

        // Scheduled line items
        if (array_key_exists('scheduled_ndis_line_item_list', $data) == true && count($data['scheduled_ndis_line_item_list']) > 0) {
            $ndis_line_item = $data['scheduled_ndis_line_item_list'];

            $insData = [];
            foreach ($ndis_line_item as $value) {
                $value = (array) $value;                
                $sa_item = [];
                $sa_item['shift_id'] = $id;
                $sa_item['category'] = 1;
                $sa_item['line_item_id'] = $value['line_item_id'];
                $sa_item['line_item_price_id'] = $value['line_item_price_id'] ?? null;
                $sa_item['sa_line_item_id'] = $value['sa_line_item_id'];
                $sa_item['duration'] = $value['duration_raw'] ?? null;
                $sa_item['price'] = $value['amount'] ?? null;
                $sa_item['amount'] = $value['sub_total'] ?? null;
                $sa_item['auto_insert_flag'] = $value['auto_insert_flag'] ?? 0;
                $sa_item['is_old_price'] = $value['is_old_price'] ?? 0;
                $sa_item['archive'] = 0;
                $sa_item['created_by'] = $adminId;
                $sa_item['created_at'] = DATE_TIME;

                $insData[] = $sa_item;
            }

            $this->basic_model->insert_records('shift_ndis_line_item', $insData, true);
        }

        // Actual line items
         $total_cost_amount=1;
        if (array_key_exists('actual_ndis_line_item_list', $data) == true && count($data['actual_ndis_line_item_list']) > 0) {
            $ndis_line_item = $data['actual_ndis_line_item_list'];
            $insData = [];
            foreach ($ndis_line_item as $value) {
                $not_be_invoiced = ($value['line_item_price_id'] && !$not_be_invoiced) ? 0 : 1;
                $sa_item = [];
                $sa_item['shift_id'] = $id;
                $sa_item['category'] = 2;
                $sa_item['line_item_id'] = $value['line_item_id'];
                $sa_item['line_item_price_id'] = $value['line_item_price_id'] ?? null;
                $sa_item['sa_line_item_id'] = $value['sa_line_item_id'];
                $sa_item['duration'] = $value['duration_raw'] ?? null;
                $sa_item['price'] = $value['amount'] ?? null;
                $sa_item['amount'] = $value['sub_total'] ?? null;
                $sa_item['auto_insert_flag'] = $value['auto_insert_flag'] ?? 0;
                $sa_item['is_old_price'] = $value['is_old_price'] ?? 0;
                $sa_item['archive'] = 0;
                $sa_item['created_by'] = $adminId;
                $sa_item['created_at'] = DATE_TIME;

                $insData[] = $sa_item;
                if($value['amount']==0)
                {
                    $total_cost_amount=0;
                }
               
            }
            if($not_be_invoiced==0 && $total_cost_amount==0)
            {
                $not_be_invoiced=1;
                $this->update_records('shift', array('not_be_invoiced' => $not_be_invoiced), array('id' => $shift_id));
            }
            $this->basic_model->insert_records('shift_ndis_line_item', $insData, true);
        }        
        $this->update_records('shift', array('not_be_invoiced' => $not_be_invoiced), array('id' => $shift_id));
    }
    /**
     * Update Goal tracking details
     */
    public function add_update_goal_tracking($data, $adminId) {
        //Add or update goal tracking table
        $data['adminId'] = $adminId;
        if(!empty($data['goalDetails'])) {
            $this->add_update_goal_type($data);
        }

        //Add or update Shift goal notes
        if(!empty($data['task_taken']) || !empty($data['worked_well'])
            || !empty($data['done_better'])) {
                $this->add_update_shift_goal_notes($data);
        }

        //Add or update Shift incident report
        if(!empty($data['incident_occur_today']) ) {
            $this->add_update_shift_incident_report($data);
        }

        return ['status' => true, 'msg' => 'Goals updated successfully'];
    }

    //Add or update goal tracking table
    public function add_update_goal_type($data) {
        // Here id is relating to the shift id
        foreach($data['goalSelection'] as $key => $value) {

            $is_goal = $this->basic_model->get_row('shift_goal_tracking', ['id'], ['goal_id' => $value['goal_id'], 'shift_id' => $data['id']]);

            $post_data = [];
            $post_data['shift_id'] = $data['id'];
            $post_data['goal_id'] = $value['goal_id'];
            $post_data['goal_type'] = $value['type'];
            $post_data['outcome_type'] = $value['outcome_type']??null;
            $post_data['created_user_type'] = $data['created_user_type'];

            if(!empty($data['snapShotList'])) {
                foreach($data['snapShotList'] as $snapshot ) {
                    if($snapshot['goal_id'] == $value['goal_id']) {
                        $post_data['snapshot'] = !empty($snapshot['value']) ? $snapshot['value'] : NULL;
                    }
                }
            }

            $update_data = $this->set_shift_goal_updated_info($is_goal, $data['adminId']);
            $postdata = array_merge($post_data, $update_data);          

            if($is_goal) { //Update record
                $this->basic_model->update_records("shift_goal_tracking", $postdata, ["shift_id" => $data['id'], 'goal_id'=> $value['goal_id']]);             
            } else { //Insert record
                $this->basic_model->insert_records("shift_goal_tracking", $postdata, FALSE);
            }
        }
    }

    function add_update_shift_goal_notes($data) {
        $is_goal = $this->check_shift_goal_tracking('shift_goal_notes', $data['id']);

        $post_data['shift_id'] = $data['id'];
        $post_data['task_taken'] = $data['task_taken'];
        $post_data['worked_well'] = $data['worked_well'];
        $post_data['done_better'] = $data['done_better'];

        $update_data = $this->set_shift_goal_updated_info($is_goal, $data['adminId']);
        $postdata = array_merge($post_data, $update_data);

        if($is_goal) { //Update record
            $this->basic_model->update_records("shift_goal_notes", $postdata, ["shift_id" => $data['id']]);
        } else { //Insert record
            $this->basic_model->insert_records("shift_goal_notes", $postdata, FALSE);
        }
    }

    /**
     * Add or update Shift incident report
     */
    function add_update_shift_incident_report($data) {
        $is_goal = $this->check_shift_goal_tracking('shift_incident_report', $data['id']);

        $post_data['shift_id'] = $data['id'];
        $post_data['incident_occur_today'] = $data['incident_occur_today'];
        $post_data['incident_report'] = !empty($data['incident_report']) ? $data['incident_report'] : NULL;

        $update_data = $this->set_shift_goal_updated_info($is_goal, $data['adminId']);
        $postdata = array_merge($post_data, $update_data);

        if($is_goal) { //Update record
            $this->basic_model->update_records("shift_incident_report", $postdata, ["shift_id" => $data['id']]);
        } else { //Insert record
            $this->basic_model->insert_records("shift_incident_report", $postdata, FALSE);
        }
    }

    //set created and updated details
    public function set_shift_goal_updated_info($is_goal, $adminId) {
        if($is_goal) {
            $post_data['updated'] = DATE_TIME;
            $post_data['updated_by'] = $adminId;
        } else {
            $post_data['created'] = DATE_TIME;
            $post_data['created_by'] = $adminId;

        }
        return $post_data;
    }
    public function check_shift_goal_tracking($table_name, $shift_id) {

        return $this->basic_model->get_row($table_name, ['id'], ['shift_id' => $shift_id]);
    }

    //Get shift goal tracking details to display in view page
    public function get_shift_goal_tracking_details($data) {

        $this->db->select('notes.task_taken, notes.worked_well, notes.done_better, report.incident_occur_today, report.incident_report');

        $this->db->select("(CASE WHEN report.incident_occur_today = 1 THEN ('Yes')
        WHEN report.incident_occur_today = 2 THEN ('No') ELSE '' END)
        as incident_occur_today_label");

        $this->db->select("(CASE WHEN report.incident_report = 1 THEN 'Yes'
        WHEN report.incident_report = 2 THEN ('No') ELSE '' END)
        as incident_report_label");

        $this->db->from('tbl_shift as shift');
        $this->db->join('tbl_shift_goal_notes notes', 'notes.shift_id = shift.id', 'left');
        $this->db->join('tbl_shift_incident_report as report','report.shift_id = shift.id', 'left');

        $this->db->where("shift.id", $data['id']);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dataResult = $query->result();
        $result = [];
        if (!empty($dataResult)) {
            $tracking = $this->get_shift_goal_details_by_id($data['id']);
            $result['goals_notes_reports'] = $dataResult[0];
            $result['goals_tracking'] = !empty($tracking['data']) ? $tracking['data'] : [];
            $return = ['data' => $result, 'status' => TRUE];
        } else {
            $return = ['status' => FALSE, 'data' => [], 'error' => 'Data not found'];
        }
        return $return;
    }

    //Get Goal name, Goal type, snapshot details
    public function get_shift_goal_details_by_id($shift_id) {

        $select_column = ["tg.id as goal_id", "tg.goal as goal_title", "track.snapshot","track.goal_type","track.outcome_type"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->select("(CASE WHEN track.goal_type = 1 THEN ('Not Attempted:Not relevant to this shift')
        WHEN track.goal_type = 1 THEN ('Not Attempted:Not relevant to this shift')
        WHEN track.goal_type = 2 THEN ('Not Attempted:Customer\'s Choice')
        WHEN track.goal_type = 3 THEN ('Verbal Prompt')
        WHEN track.goal_type = 4 THEN ('Physical Assistance')
        WHEN track.goal_type = 5 THEN ('Participant Proactivity') ELSE '' END)
        as goaltype");
        $this->db->select("(CASE WHEN track.outcome_type = 1 THEN ('Achieved')
        WHEN track.outcome_type = 2 THEN (' Partially Achieved')
         ELSE '' END)
        as outcometype");
        $this->db->from('tbl_goals_master as tg');
        $this->db->join('tbl_shift_goal_tracking track', 'track.goal_id = tg.id', 'inner');
        $this->db->where('track.shift_id', $shift_id);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = $query->result();

        if (!empty($dataResult)) {
            $return = ['data' => $dataResult, 'status' => TRUE];
        } else {
            $return = ['status' => FALSE, 'error' => 'Data not found'];
        }

        return $return;
    }
    /**
     * fetching a single shift member details using member and shift id
     */
    public function get_shift_member_details_frm_member_id($member_id, $shift_id) {
        if (empty($member_id) || empty($shift_id)) return;

        $this->db->select("sm.*,s.shift_no, (CASE WHEN sm.id = s.accepted_shift_member_id THEN true ELSE false END) as accepted_shift, s.accepted_shift_member_id,
                          sta.id as attachment_id, sta.filename, sta.file_type, sta.file_size,sta.file_path, sta.aws_uploaded_flag");
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id', 'inner');
        $this->db->join('tbl_member m', 'm.uuid = s.owner_id', 'inner');
        $this->db->join('tbl_shift_timesheet_attachment as sta', 's.id = sta.shift_id', 'left');
        $this->db->where("sm.member_id", $member_id);
        $this->db->where("s.id", $shift_id);
        $this->db->where("sm.archive", "0");
        $this->db->where("s.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $result = $query->result();
        $dataResult = [];
        if (empty($query->result())) {
            $return = array('msg' => "Shift member not found!", 'status' => false);
            return $return;
        }
        $attach_arr = [];
        foreach ($result as $val) {
            $dataResult = $val;
            $dataResult->accepted_shift = (boolean) $dataResult->accepted_shift;

            $s3 = ($val->aws_uploaded_flag == 1) ? "true" : "false";
            //$file_url = base_url( $val->shift_id . '?filename=' . urlencode(base64_encode($val->filename)) . '&s3=' . $s3 . '&fp=' . $val->file_path);
            $file_url = $val->shift_id . '/' . urlencode(base64_encode($val->file_path)) . '?s3=' . $s3 . '&download_as=' . $val->filename;
            $val->file_show_url = $file_url;
        }

        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /**
     * fetching a single shift member details
     */
    public function get_shift_member_details($id, $shift_id) {
        if (empty($id) || empty($shift_id)) return;

        $this->db->select("sm.*, s.shift_no");
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id', 'inner');
        $this->db->join('tbl_member m', 'm.uuid = s.owner_id', 'inner');
        $this->db->where("sm.id", $id);
        $this->db->where("s.id", $shift_id);
        $this->db->where("sm.archive", "0");
        $this->db->where("s.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null;#$ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Shift member not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $dataResult = $val;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /**
     * fetching shift breaks information in one line in a readable format
     */
    public function get_shift_breaks_one_line($shift_id, $break_cat = 1) {

        $total_unpaid_break_duration_int = $total_so_break_duration_int = 0;

        # fetching unpaid break type details
        $unpaid_breaks = $this->get_shift_breaks_list($shift_id, $break_cat, "unpaid");
        if($unpaid_breaks) {
            $unpaid_breaks = object_to_array($unpaid_breaks);
            foreach($unpaid_breaks as $break_row) {
                $total_unpaid_break_duration_int += $break_row['duration_int'];
            }
        }

        # fetching unpaid break type details
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $break_cat, "sleepover");
        if($sleepover_breaks) {
            $sleepover_breaks = object_to_array($sleepover_breaks);
            foreach($sleepover_breaks as $break_row) {
                $total_so_break_duration_int += $break_row['duration_int'];
            }
        }

        $break_str = '';
        if($total_unpaid_break_duration_int)
            $break_str = "(".get_hour_minutes_from_int($total_unpaid_break_duration_int, true).")";
        if($total_so_break_duration_int) {
            if($break_str)
            $break_str .= ' ';

            $break_str .= '(S/O '.get_hour_minutes_from_int($total_so_break_duration_int, true).')';
        }
        return $break_str;
    }

    /**
     * fetching shift breaks information in one line in a start and end time or break time
     */
    public function get_shift_breaks_with_date_and_duration_one_line($shift_id, $break_cat = 1) {

        $total_unpaid_break_duration_int = 0;
        $total_so_break_date = '';

        # fetching unpaid break type details
        $unpaid_breaks = $this->get_shift_breaks_list($shift_id, $break_cat, "unpaid");
        if($unpaid_breaks) {
            $unpaid_breaks = object_to_array($unpaid_breaks);
            foreach($unpaid_breaks as $break_row) {
                $total_unpaid_break_duration_int += $break_row['duration_int'];
            }
        }

        # fetching unpaid break type details
        $sleepover_breaks = $this->get_shift_breaks_list($shift_id, $break_cat, "sleepover");
        
        if($sleepover_breaks) {
            $sleepover_breaks = object_to_array($sleepover_breaks);
            
            foreach($sleepover_breaks as $break_row) {
                //Replace 00 mins 
                $total_so_break_date .= str_replace('.00', '', $break_row['for_invoice_break_start_time']) . " s/o " . str_replace('.00', '', $break_row['for_invoice_break_end_time']) ;                
            }
        }

        $break_str = [];
        if($total_unpaid_break_duration_int) {            
            $break_str['unpaid'] = "(".get_hour_minutes_from_int($total_unpaid_break_duration_int, true)." break)";
        }
        if($total_so_break_date) {

            $break_str['sleepover'] = $total_so_break_date;
        }
        
        return $break_str;
    }

    /**
     * fetching a single shift details
     */
    public function get_shift_details($id, $clone = false, $from_portal = false, $shift_lock = false, $adminId = null, $uuid_user_type=ADMIN_PORTAL) {
        if (empty($id)) return;

        if($shift_lock && !$clone) {
            # is the object being altered currently by other user? if yes - cannot perform this action
            $lock_taken = $this->add_shift_lock($id, $adminId);
            if($lock_taken['status'] == false)
            return $lock_taken;
        }

        
        $this->db->select("s.*, s.email as contact_email, s.phone as contact_phone, m.uuid as ownervalue, concat(m.firstname,' ',m.lastname) as ownerlabel,
        p.id as contactvalue, concat(p.firstname,' ',p.lastname) as contactlabel, pp.phone as contactphone, pe.email as contactemail, r.name as rolelabel, rf.display_name as cancel_reason_label, s.cancel_reason_id, s.cancel_notes, s.roster_id, ros.roster_no");
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as accountlabel");

        $this->db->select("(select concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) from tbl_person_address pt where pt.id = s.account_address) as addresslabel,(select p1.id from tbl_person_address p1 where p1.id = s.account_address) as address_id ");

        $this->db->select("(
            CASE WHEN s.scheduled_support_type = 1 THEN 'Self Care'
            WHEN s.scheduled_support_type = 2 THEN 'Cleaning'
            WHEN s.scheduled_support_type = 4 THEN 'Comm Access'
            ELSE '' END) as support_type_label");

        $this->db->select("(
            CASE WHEN s.scheduled_sa_id IS NOT NULL THEN ( SELECT service_agreement_id FROM tbl_service_agreement WHERE id = s.scheduled_sa_id)
            ELSE '' END) as scheduled_service_agreement_no");
        
        $this->db->select("(
            CASE WHEN s.actual_sa_id IS NOT NULL THEN ( SELECT service_agreement_id FROM tbl_service_agreement WHERE id = s.actual_sa_id)
            ELSE '' END) as actual_service_agreement_no");

        $this->db->select("(
            CASE WHEN s.actual_support_type IS NOT NULL THEN ( SELECT type FROM tbl_finance_support_type WHERE id = s.scheduled_support_type)
            ELSE '' END) as scheduled_support_type_label");
        
        $this->db->select("(
            CASE WHEN s.scheduled_support_type IS NOT NULL THEN ( SELECT key_name FROM tbl_finance_support_type WHERE id = s.scheduled_support_type)
            ELSE '' END) as scheduled_support_type_key_name");
        
        $this->db->select("(
            CASE WHEN s.actual_support_type IS NOT NULL THEN ( SELECT key_name FROM tbl_finance_support_type WHERE id = s.actual_support_type)
            ELSE '' END) as actual_support_type_key_name");

        $this->db->select("(
            CASE WHEN s.actual_support_type IS NOT NULL THEN ( SELECT type FROM tbl_finance_support_type WHERE id = s.actual_support_type)
            ELSE '' END) as actual_support_type_label");

        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.cost_book_id from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.cost_book_id from tbl_organisation o where o.id = s.account_id) ELSE '' END) as cost_book_id");
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p2.contact_id from tbl_participants_master p2 where p2.id = s.account_id) ELSE '' END) as participant_contact_id");
        $status_label = "(CASE ";
        foreach($this->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);
        $this->db->select("sm.is_restricted");
        $this->db->select("sm.status as shift_member_status");
        if($adminId) {
            $this->db->select("al.created_by as is_shift_locked, (select CONCAT(firstname,' ',lastname) from tbl_member m where m.uuid = al.created_by) as shift_locked_by");
        }
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member sm', 'sm.shift_id = s.id', 'left');
        $this->db->join('tbl_member m', 'm.uuid = s.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = s.person_id', 'left');
        $this->db->join('tbl_person_phone as pp', 'p.id = pp.person_id and pp.archive = 0 and pp.primary_phone = 1', 'left');
        $this->db->join('tbl_person_email as pe', 'p.id = pe.person_id and pe.archive = 0 and pe.primary_email = 1', 'left');
        $this->db->join('tbl_references as rf', 'rf.id = s.cancel_reason_id', 'left');
        if($adminId)
        $this->db->join('tbl_access_lock as al', 's.id = al.object_id and al.archive = 0 and al.created_by != '.$adminId.' and al.object_type_id = 1', 'left');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->join('tbl_roster as ros', 'ros.id = s.roster_id', 'left');

        $this->db->group_by('s.id');
        $this->db->where("s.id", $id);
        $this->db->where("s.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $ci = &get_instance();
        $last_query = null;

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Shift not found!", 'status' => false);
            return $return;
        }

        $this->load->model('schedule/Ndispayments_model');

        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # if status is Scheduled, In Progress, Cancelled or Completed, donot allow to add/edit shift members
            $dataResult->disabled_members = 1;
            if($val->status == 1 || $val->status == 2) {
                $dataResult->disabled_members = 0;
            }

            # for member pre-selection
            $owner_person['label'] = $val->ownerlabel;
            $owner_person['value'] = $val->ownervalue;
            $dataResult->owner_person = $owner_person;

            # getting owner address
            $this->load->model('sales/Contact_model');
            $this->load->model('sales/Account_model');
            if($val->account_type == 1) {
                $dataResult->owner_address = $this->Contact_model->get_contact_address($val->participant_contact_id);
            } else {
                $this->load->model('organisation/Org_model');
                $dataResult->owner_address = $this->Org_model->get_organisation_address($val->account_id, 2);
            }

            # for contact pre-selection
            $contact_person['label'] = $val->contactlabel;
            $contact_person['value'] = $val->contactvalue;
            $dataResult->contact_person = $contact_person;

            # for role pre-selection
            $role_details['label'] = $val->rolelabel;
            $role_details['value'] = $val->role_id;
            $dataResult->role_details = $role_details;

            # fetching the org type and award type
            $dataResult->org_type = '';
            $dataResult->award_type = 'SCHADS'; # default for participant and org
            if($val->account_type == 2) {
                $this->load->model('sales/Account_model');
                $org_details = $this->Account_model->get_account_details_for_view($val->account_id, $uuid_user_type);
                if($org_details) {
                    if(isset($org_details['status']) && $org_details['status'] == 1) {
                        $dataResult->org_type = $org_details['data']->org_source_name;
                        if(isset($org_details['data']->dhhs) && $org_details['data']->dhhs == 1)
                            $dataResult->award_type ='DHHS';
                    }
                }
            }

            # fetching the award type id from reference table
            $award_details = $this->basic_model->get_row('references', ['id'], ['key_name' => $dataResult->award_type]);
            $dataResult->award_type_id = 0;
            if ($award_details)
                $dataResult->award_type_id = $award_details->id;

            # for account pre-selection
            $account_person['label'] = $val->accountlabel;
            $account_person['value'] = $val->account_id;
            $account_person['account_type'] = $val->account_type;
            $dataResult->account_person = $account_person;

            # for account address pre-selection
            // $account_address['label'] = $val->addresslabel;
            // $account_address['value'] = $val->address_id;
            // $dataResult->account_address = $account_address;

            // contact information
            $dataResult->contact_email = $val->contact_email;
            $dataResult->contact_phone = $val->contact_phone;

            # Roster no
            $dataResult->roster_id = $val->roster_id;
            $dataResult->roster_no = $val->roster_no;

            # fetching shift break details
            $scheduled_breaks = $this->get_shift_breaks_list($val->id, 1);
            if($scheduled_breaks && $clone) {
                $scheduled_breaks = object_to_array($scheduled_breaks);
                $scheduled_rows = null;
                foreach($scheduled_breaks as $break_row) {
                    unset($break_row['id']);
                    unset($break_row['shift_id']);
                    $scheduled_rows[] = $break_row;
                }
                $dataResult->scheduled_rows = $scheduled_rows;
            }
            else if($scheduled_breaks) {
                $dataResult->scheduled_rows = object_to_array($scheduled_breaks);
            }
            else
                $dataResult->scheduled_rows = [];

            $actual_breaks = $this->get_shift_breaks_list($val->id, 2);
            if($actual_breaks)
                $dataResult->actual_rows = object_to_array($actual_breaks);
            else if($scheduled_breaks && ($dataResult->actual_start_datetime && empty($dataResult->actual_end_datetime)) &&  $from_portal == true)
                $dataResult->actual_rows = $this->use_scheduled_breaks_in_actual($scheduled_breaks);
            else
                $dataResult->actual_rows = [];

            # fetching the overall scheduled and actual break durations
            $dataResult->scheduled_break_duration_int = 0;
            if(!empty($dataResult->scheduled_rows)) {
                foreach($dataResult->scheduled_rows as $break_row) {
                    $dataResult->scheduled_break_duration_int += $break_row['duration_int'];
                }
            }
            $dataResult->actual_break_duration_int = 0;
            if(!empty($dataResult->actual_rows)) {
                foreach($dataResult->actual_rows as $break_row) {
                    if ($break_row['key_name'] != 'interrupted_sleepover') {
                        $dataResult->actual_break_duration_int += $break_row['duration_int'];
                    }
                }
            }
            $dataResult->scheduled_break_duration = get_hour_minutes_from_int($dataResult->scheduled_break_duration_int);
            $dataResult->actual_break_duration = get_hour_minutes_from_int($dataResult->actual_break_duration_int);

            # fetching individual break type details
            $scheduled_paid_breaks = $this->get_shift_breaks_list($val->id, 1, "paid");
            if($scheduled_breaks)
            $dataResult->scheduled_paid_rows = object_to_array($scheduled_paid_breaks);

            $scheduled_unpaid_breaks = $this->get_shift_breaks_list($val->id, 1, "unpaid");
            if($scheduled_unpaid_breaks)
            $dataResult->scheduled_unpaid_rows = object_to_array($scheduled_unpaid_breaks);

            $scheduled_sleepover_breaks = $this->get_shift_breaks_list($val->id, 1, "sleepover");
            if($scheduled_sleepover_breaks)
            $dataResult->scheduled_sleepover_rows = object_to_array($scheduled_sleepover_breaks);

            $actual_paid_breaks = $this->get_shift_breaks_list($val->id, 2, "paid");
            if($actual_breaks)
            $dataResult->actual_paid_rows = object_to_array($actual_paid_breaks);

            $actual_unpaid_breaks = $this->get_shift_breaks_list($val->id, 2, "unpaid");
            if($actual_unpaid_breaks)
            $dataResult->actual_unpaid_rows = object_to_array($actual_unpaid_breaks);

            $actual_sleepover_breaks = $this->get_shift_breaks_list($val->id, 2, "sleepover");
            if($actual_sleepover_breaks)
            $dataResult->actual_sleepover_rows = object_to_array($actual_sleepover_breaks);

            $actual_in_sleepover_breaks = $this->get_shift_breaks_list($val->id, 2, "interrupted_sleepover");
            if($actual_in_sleepover_breaks)
                $dataResult->actual_in_sleepover_rows = object_to_array($actual_in_sleepover_breaks);

            if(isset($dataResult->actual_in_sleepover_rows) && !empty($dataResult->actual_in_sleepover_rows && count($dataResult->actual_in_sleepover_rows) > 0)) {
                $actual_in_sleepover_duration_int = array_sum(array_column($dataResult->actual_in_sleepover_rows, 'duration_int'));
                $dataResult->actual_in_sleepover_duration = get_hour_minutes_from_int($actual_in_sleepover_duration_int);
            }

            $dataResult->scheduled_start_date = $dataResult->scheduled_start_datetime;
            $dataResult->actual_start_date = $dataResult->actual_start_datetime;
            $dataResult->scheduled_end_date = $dataResult->scheduled_end_datetime;
            $dataResult->actual_end_date = $dataResult->actual_end_datetime;

            # preselect start time and end time
            $this->load->model('member/Member_model');
            $half_hourly_times = $this->Member_model->get_half_hourly_times();

            if(!empty($val->scheduled_start_date) && $val->scheduled_start_date != "0000-00-00 00:00:00")
                $dataResult->scheduled_start_time = get_time_id_from_series($val->scheduled_start_date);
            else {
                $val->scheduled_start_date = '';
                $val->scheduled_start_time = '';
            }

            if(!empty($val->scheduled_end_date) && $val->scheduled_end_date != "0000-00-00 00:00:00")
                $dataResult->scheduled_end_time = get_time_id_from_series($val->scheduled_end_date);
            else {
                $val->scheduled_end_date = '';
                $val->scheduled_end_time = '';
            }

            if(!empty($val->actual_start_date) && $val->actual_start_date != "0000-00-00 00:00:00")
                $dataResult->actual_start_time = get_time_id_from_series($val->actual_start_date);
            else {
                $val->actual_start_date = '';
                $val->actual_start_time = '';
            }

            if(!empty($val->actual_end_date) && $val->actual_end_date != "0000-00-00 00:00:00")
                $dataResult->actual_end_time = get_time_id_from_series($val->actual_end_date);
            else {
                $val->actual_end_date = '';
                $val->actual_end_time = '';
            }

            //get shift account address
            $shift_account_address = $this->get_address_for_account($val->account_id, $val->account_type, true);
            $dataResult->account_address = $shift_account_address['data'][0]?? "";

            # Get scheduled line items
            $sa_line_item = $this->get_service_agreement_line_item_by_shift_id($id, 1);
            if ($sa_line_item != '' && count($sa_line_item) > 0) {
                $dataResult->scheduled_ndis_line_item_list = $sa_line_item;
            }

            # Get actual line items
            $act_sa_line_item = $this->get_service_agreement_line_item_by_shift_id($id, 2);
            if ($act_sa_line_item != '' && count($act_sa_line_item) > 0) {
                $dataResult->actual_ndis_line_item_list = $act_sa_line_item;
            }

            # Get scheduled ndis support duration ndis line items
            $stdData = [];
            $stdData['start_date'] = date('Y-m-d', strtotime($dataResult->scheduled_start_date));
            $stdData['end_date'] = date('Y-m-d', strtotime($dataResult->scheduled_end_date));
            $stdData['start_time'] = $dataResult->scheduled_start_time;
            $stdData['end_time'] = $dataResult->scheduled_end_time;
            $stdData['duration'] = $dataResult->scheduled_duration;
            $stdData['rows'] = $dataResult->scheduled_rows;
            $stdData['account_address'] =(object) $dataResult->account_address;
            $stdData['section'] = 'scheduled';

            $sd_scheduled = $this->Ndispayments_model->get_support_type_ndis_duration($stdData);
            $sch_st_duration = $this->Schedule_ndis_model->get_support_duration_by_shift_id($id, 1, $sd_scheduled, $stdData);

            if ($sch_st_duration != '' && count($sch_st_duration) > 0) {
                $dataResult->scheduled_support_type_duration = $sch_st_duration;
            }

            # Get actual ndis support duration ndis line items
            $actdData = [];
            $actdData['start_date'] = date('Y-m-d', strtotime($dataResult->actual_start_date));
            $actdData['end_date'] = date('Y-m-d', strtotime($dataResult->actual_end_date));
            $actdData['start_time'] = $dataResult->actual_start_time;
            $actdData['end_time'] = $dataResult->actual_end_time;
            $actdData['duration'] = $dataResult->actual_duration;
            $actdData['rows'] = $dataResult->actual_rows;
            $actdData['account_address'] =(object) $dataResult->account_address;
            $actdData['section'] = 'actual';
            $sd_actual = $this->Ndispayments_model->get_support_type_ndis_duration($actdData);
            
            $act_st_duration = $this->Schedule_ndis_model->get_support_duration_by_shift_id($id, 2, $sd_actual, $actdData);
            if ($act_st_duration != '' && count($act_st_duration) > 0) {
                $dataResult->actual_support_type_duration = $act_st_duration;
            }

            $dataResult->scheduled_signed_status = 0;
            $warnings = [];
            # get schedule sa document
            if (!empty($dataResult->scheduled_sa_id) && !empty($dataResult->scheduled_docusign_id)) {
                $service_agreement_id = $dataResult->scheduled_sa_id;
                $docusign_id = $dataResult->scheduled_docusign_id;
                $this->load->model('../../sales/models/ServiceAgreement_model');
                # get service agreement attachment
                $SAAttachment = $this->ServiceAgreement_model->service_docusign_data_by_sa_id($service_agreement_id,$docusign_id);
                if (!empty($SAAttachment)) {
                    $dataResult->scheduled_docusign_url = $SAAttachment['url'] ?? '';
                    $dataResult->scheduled_docusign_related = $SAAttachment['related'] ?? '';
                    $dataResult->scheduled_signed_status = $SAAttachment['signed_status'] ?? 0;
                }

                if(!empty($val->scheduled_sb_status)) {
                    if ($val->scheduled_sb_status == 2) {
                        $warnings['scheduled'][0] = 'No Service Booking exists for the requested shift date';
                    } else if ($val->scheduled_sb_status == 3) {
                        $warnings['scheduled'][0] = 'Existing Service Booking for the requested shift date is not signed';
                    }
                }
                $dataResult->warnings = $warnings;
            }

            # get actual sa document
            if (!empty($dataResult->actual_sa_id) && !empty($dataResult->actual_docusign_id)) {
                $service_agreement_id = $dataResult->actual_sa_id;
                $docusign_id = $dataResult->actual_docusign_id;
                $this->load->model('../../sales/models/ServiceAgreement_model');
                # get service agreement attachment
                $SAAttachment = $this->ServiceAgreement_model->service_docusign_data_by_sa_id($service_agreement_id,$docusign_id);
                if (!empty($SAAttachment)) {
                    $dataResult->actual_docusign_url = $SAAttachment['url'] ?? '';
                    $dataResult->actual_docusign_related = $SAAttachment['related'] ?? '';
                }

                if(!empty($val->actual_sb_status)) {
                    if ($val->actual_sb_status == 2) {
                        $warnings['actual'][0] = 'No Service Booking exists for the requested shift date';
                    } else if ($val->actual_sb_status == 3) {
                        $warnings['actual'][0] = 'Existing Service Booking for the requested shift date is not signed';
                    }
                }
                $dataResult->warnings = $warnings;
            }
            

            $sc_old_price = $actual_old_price = [];
            $is_old_price = false;
            //Check old price exist in line items
            if(!empty($dataResult->scheduled_ndis_line_item_list)) {
                $scheduled_missing_line_item = false;
                //Alter line_item_value for front end display if it has old price list
                foreach($dataResult->scheduled_ndis_line_item_list as $key => $line_item) {
                    //Add old price duration and new duration
                    if($line_item->is_old_price){
                        $is_old_price = true;
                        $sc_time = explode(':', $line_item->duration_raw);
                        $sc_old_price[$line_item->line_item_number]['duration'] = ($sc_time[0] * 60) + $sc_time[1];
                        continue;
                    }
                    if($is_old_price) {
                        $sc_time = explode(':', $line_item->duration_raw);
                        if (!empty($sc_old_price[$line_item->line_item_number]['duration'])) {
                            $sc_old_price[$line_item->line_item_number]['duration'] += ($sc_time[0] * 60) + $sc_time[1];
                        } else {
                            $sc_old_price[$line_item->line_item_number]['duration'] = ($sc_time[0] * 60) + $sc_time[1];
                        }
                        $dataResult->scheduled_ndis_line_item_list[$key]->line_item_value = 
                        $line_item->line_item_number . " " . $line_item->label . "(" . formatHoursAndMinutes(hoursandmins($sc_old_price[$line_item->line_item_number]['duration'])) . ")";
                    }
                }
            }
            //Check old price exist in line items
            if(!empty($dataResult->actual_ndis_line_item_list)) {
                $actual_missing_line_item = false;
                foreach($dataResult->actual_ndis_line_item_list as $key => $line_item) {
                    if ($line_item->auto_insert_flag == 1) {
                        $actual_missing_line_item = true;
                    }
                    //Add old price duration and new duration
                    if($line_item->is_old_price) {
                        $is_old_price = true;
                        $ac_time = explode(':', $line_item->duration_raw);
                        $actual_old_price[$line_item->line_item_number]['duration'] = ($ac_time[0] * 60) + $ac_time[1];
                        continue;
                    }
                    if($is_old_price) {
                        $ac_time = explode(':', $line_item->duration_raw);
                        if (!empty($actual_old_price[$line_item->line_item_number]['duration'])) {
                            $actual_old_price[$line_item->line_item_number]['duration'] += ($ac_time[0] * 60) + $ac_time[1];
                        } else {
                            $actual_old_price[$line_item->line_item_number]['duration'] = ($ac_time[0] * 60) + $ac_time[1];
                        }
                        
                        
                        $dataResult->actual_ndis_line_item_list[$key]->line_item_value = 
                        $line_item->line_item_number . " " . $line_item->label . "(" . formatHoursAndMinutes(hoursandmins($actual_old_price[$line_item->line_item_number]['duration'])) . ")";
                    }
                }
                $dataResult->actual_missing_line_item = $actual_missing_line_item;
            }
            # no need to set id, status and shift_no while cloning
            if($clone) {
                $dataResult->id = null;
                $dataResult->status = 1;
                $dataResult->actual_rows = [];
                $dataResult->actual_travel = null;
                $dataResult->actual_reimbursement = null;
                $dataResult->notes = null;
                $dataResult->actual_start_date = null;
                $dataResult->actual_start_time = null;
                $dataResult->actual_end_date = null;
                $dataResult->actual_end_time = null;
                $dataResult->actual_end_datetime = null;
                $dataResult->actual_end_datetime = null;
                $dataResult->actual_travel_distance = null;
                $dataResult->actual_travel_duration = null;
                $dataResult->scheduled_sa_id = '';
                $dataResult->sa_line_items = [];
                $dataResult->scheduled_ndis_line_item_list = [];
                $dataResult->scheduled_docusign_id = '';
                $dataResult->scheduled_docusign_url = '';
                $dataResult->scheduled_docusign_related = '';
                $dataResult->actual_sa_id = '';
                $dataResult->sa_line_items = [];

                $dataResult->actual_ndis_line_item_list = [];
                $dataResult->actual_docusign_id = '';
                $dataResult->actual_docusign_url = '';
                $dataResult->actual_docusign_related = '';
                $dataResult->scheduled_signed_status = 0;
                $dataResult->actual_support_type_duration = [];
            }
        }
        # To disable shift edit for published time sheet
        $this->load->model('finance/Finance_model');
        $isEditable = $this->Finance_model->get_time_sheet_status_by_shift_id($id);
        $dataResult->isEditable = ($isEditable)??False;
        
        //fetch safety checklist associated with participant
        $this->load->model('sales/Opportunity_model');
        $req = new stdClass();
        $req->data = new stdClass();
        $req->data->participant_id = $dataResult->account_id;
        $checklist = $this->Opportunity_model->get_staff_safety_checklist($req);

        $return = array('data' => $dataResult, 'status' => true, 'checklist' => $checklist);
        return $return;
    }

    /**
     * fetching a list of shift break details for a given shift and break category
     */
    public function get_shift_breaks_list($shift_id, $break_cat = null, $break_type = null) {
        $select_column = ["sb.id", "sb.shift_id", "sb.break_type", "sb.start_datetime as break_start_time", "sb.end_datetime as break_end_time", "r.key_name", "sb.duration as break_duration", "(CASE WHEN sb.start_datetime IS NULL THEN '1' ELSE '0' END) as timing_disabled", "(CASE WHEN sb.start_datetime IS NOT NULL THEN '1' ELSE '0' END) as duration_disabled" ];
        $this->db->select($select_column);
        $this->db->from('tbl_shift_break as sb');
        $this->db->join('tbl_shift as s', 's.id = sb.shift_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = sb.break_type', 'inner');
        $this->db->where('sb.archive', 0);
        $this->db->where('sb.shift_id', $shift_id);
        if($break_cat)
        $this->db->where('sb.break_category', $break_cat);
        if($break_type)
        $this->db->where('r.key_name', $break_type);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $shift_breaks = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($row->timing_disabled)
                $row->timing_disabled = true;
                else
                $row->timing_disabled = false;

                if($row->duration_disabled)
                $row->duration_disabled = true;
                else
                $row->duration_disabled = false;

                $row->break_start_datetime = $row->break_start_time;
                $row->break_end_datetime = $row->break_end_time;
                if($row->break_start_time) {
                    $row->for_invoice_break_start_time = date("g.ia", strtotime($row->break_start_time));
                    $row->break_start_time = date("h:i A", strtotime($row->break_start_time));
                }
                if($row->break_end_time) {
                    $row->for_invoice_break_end_time = date("g.ia", strtotime($row->break_end_time));
                    $row->break_end_time = date("h:i A", strtotime($row->break_end_time));
                }

                list($hour, $minutes) = explode(":",$row->break_duration);
                $hour = (int) $hour;
                $minutes = (int) $minutes;
                $break_minutes = ($minutes + ($hour * 60));
                $row->duration_int = $break_minutes;
                $shift_breaks[] = $row;
            }
        }
        return $shift_breaks;
    }

    public function get_participant_name($post_data) {
        $this->db->select("CONCAT(firstname,' ',middlename,' ',lastname) as label");
        $this->db->select('id as value');
        $this->db->where('archive', 0);
        $this->db->where('status', 1);
        $this->db->group_start();
        $this->db->or_like('firstname', $post_data);
        $this->db->or_like('lastname', $post_data);
        $this->db->group_end();
        $query = $this->db->get(TBL_PREFIX . 'participant');
        $query->result();
        return $query->result();
    }

    //Get the data from Participant master
    public function get_participant_master_name($post_data) {
        $this->db->select("name as label");
        $this->db->select('id as value');
        $this->db->where('archive', 0);
        $this->db->group_start();
        $this->db->or_like('name', $post_data);
        $this->db->group_end();
        $query = $this->db->get(TBL_PREFIX . 'participants_master');
        $query->result();
        return $query->result();
    }

    public function get_site_name($post_data) {
        $this->db->like('site_name', $post_data, 'both');
        $this->db->where('archive =', 0);
        $this->db->where('organisationId !=', 0);
        $this->db->select('site_name, id');
        $query = $this->db->get(TBL_PREFIX . 'organisation_site');
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->site_name, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    ///Get Site name with out full Organization list
    public function get_is_site_name($post_data) {
        $this->db->like('name', $post_data, 'both');
        $this->db->where('archive =', 0);
        $this->db->select('name, id');
        $this->db->where(['is_site' => 1]);
        $query = $this->db->get(TBL_PREFIX . 'organisation');
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->name, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function create_shift($reqData, $adminId) {
        #pr($reqData);
        $responseAry = $reqData->data;
        $address_ary = $responseAry->completeAddress;
        $mobility_ary = $responseAry->mobility;
        $assistance_ary = $responseAry->assistance;
        $shift_requirement_ary = $responseAry->shift_requirement;
        $shift_requirement_mobility_ary = $responseAry->shift_requirement_mobility;
        $org_shift_requirement_ary = $responseAry->shift_org_requirement;
        $preferred_member_ary = $responseAry->preferred_member_ary;
        $participant_member_ary = $responseAry->participant_member_ary;
        $site_lookup_ary = $responseAry->site_lookup_ary;
        $house_lookup_ary = $responseAry->house_lookup_ary;
        $booked_by = $responseAry->booked_by;
        $funding_type = $responseAry->funding_type ?? 1;
        $is_quoted = $responseAry->status == 3 ? 1 : 0;

        if ($is_quoted == 1)
            $push_to_app = 0;
        else
            $push_to_app = ($responseAry->push_to_app == 2) ? 0 : 1;

        $shift_status = ($responseAry->status == 3) ? 3 : 1;

        $shift_ary = array('booked_by' => $booked_by,
            'shift_date' => isset($responseAry->start_time) ? DateFormate($responseAry->start_time, 'Y-m-d') : '',
            'start_time' => isset($responseAry->start_time) ? DateFormate($responseAry->start_time, "Y-m-d H:i:s") : '',
            'end_time' => isset($responseAry->end_time) ? DateFormate($responseAry->end_time, "Y-m-d H:i:s") : '',
            'allocate_pre_member' => $responseAry->allocate_pre_member,
            'push_to_app' => $push_to_app,
            'autofill_shift' => $responseAry->autofill_shift,
            'is_quoted' => $responseAry->status == 3 ? 1 : 0,
            'status' => $shift_status,
            'funding_type' => $funding_type,
            'created' => DATE_TIME,
        );

        $shift_id = $this->Basic_model->insert_records('shift', $shift_ary, $multiple = FALSE);

        // make shift caller
        $this->make_shift_caller($responseAry, $shift_id);

        // make shift address
        $this->make_shift_address($address_ary, $shift_id);

        // make shift requirement
        if ($booked_by == 2 || $booked_by == 3) {
            $this->make_shift_requirement($mobility_ary, $shift_id, 1);
            $this->make_shift_requirement($assistance_ary, $shift_id, 2);
        }

        // if booked by organization then inset organization requirement
        if ($booked_by == 1 || $booked_by == 7) {
            $this->make_shift_requirement($shift_requirement_mobility_ary, $shift_id, 1);
            $this->make_shift_requirement($shift_requirement_ary, $shift_id, 2);
            $this->make_org_shift_requirement($org_shift_requirement_ary, $shift_id);
        }

        // make shift preferred member
        $assing_prefer_member = ($shift_status == 1) ? $responseAry->allocate_pre_member : 0;
        $this->make_shift_preferred_member_and_allocate($preferred_member_ary, $assing_prefer_member, $shift_id);

        // make shift confirmation details
        $this->make_shift_confirmation_details($responseAry, $shift_id);

        // if booked by organization then inset organization information
        if ($booked_by == 1) {
            $this->make_org_shift_user($site_lookup_ary, $shift_id);
        }

        if ($booked_by == 7) {
            $this->make_house_shift_user($house_lookup_ary, $shift_id);
        }

        // if booked by participant and location then inset participant information
        if ($booked_by == 2 || $booked_by == 3) {
            $this->make_shift_participant_user($participant_member_ary, $shift_id);
        }

        $allocation_result = false;
        if ($responseAry->autofill_shift == 1 && $shift_status == 1) {
            $allocation_result = $this->make_auto_fill_shift($shift_id);
        }

        $this->add_shift_category_in_shift($responseAry->time_of_days, $shift_id);
        $this->add_shift_note_in_shift($responseAry->shift_note, $shift_id, $adminId);
        $this->attach_line_item_in_shift($responseAry, $shift_id);

        if ((int) $push_to_app === 1) {
            #Send notification to all member near 40 KM of shift
            #when shift move to app
            $this->Listing_model->send_notification_to_all_member(array($shift_id));
        }

        return array('shiftId' => $shift_id, 'allocation' => $responseAry->autofill_shift, 'allocation_res' => $allocation_result);
    }

    function add_shift_note_in_shift($shift_note, $shiftId, $adminId) {
        $note_data = array(
            'shiftId' => $shiftId,
            'adminId' => $adminId,
            'notes' => $shift_note,
            'archive' => 0,
            'created' => DATE_TIME,
            'note_date' => DATE_TIME,
        );

        $this->basic_model->insert_records('shift_notes', $note_data, false);
    }

    function add_shift_category_in_shift($shift_category, $shiftId) {
        if (!empty($shift_category)) {
            foreach ($shift_category as $val) {
                if (!empty($val->checked)) {
                    $data[] = [
                        'shiftId' => $shiftId,
                        'timeId' => $val->id,
                        'created' => DATE_TIME,
                        'archive' => 0,
                    ];
                }
            }

            if (!empty($data)) {
                $this->basic_model->insert_records('shift_time_category', $data, TRUE);
            }
        }
    }

    function getLineItemCost($lineItemIds) {
        $this->db->select(["id", "upper_price_limit", "national_price_limit", "national_very_price_limit"]);
        $this->db->from('tbl_finance_line_item');

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

    function attach_line_item_in_shift($req, $shiftId) {
        require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';

        $line_items = $req->selectedLineItemList;
        $sub_total = 0;

        if (!empty($line_items)) {
            $line_itemIds = array_column($line_items, 'line_itemId');

            $this->load->model('finance/Finance_quote_model');
            $line_item_cost = $this->getLineItemCost($line_itemIds);


            foreach ($line_items as $val) {
                if (!empty($val->checked)) {
                    $qty = calculate_qty_using_date($req->start_time, $req->end_time);
                    $postcode = (!empty($req->completeAddress[0]->postcode)) ? $req->completeAddress[0]->postcode : 0;
                    $price_type = get_price_type_on_base_postcode($postcode);
                    $item_cost = $line_item_cost[$val->line_itemId][$price_type];
                    $item_sub_total = custom_round(($item_cost * $qty), 2);
                    $item_gst = calculate_gst($item_sub_total);
                    $item_total = custom_round($item_sub_total + $item_gst);

                    $attach_line_item = array(
                        'shiftId' => $shiftId,
                        'plan_line_itemId' => $val->plan_line_itemId,
                        'line_item' => $val->line_itemId,
                        'quantity' => $qty,
                        'cost' => $item_cost,
                        'sub_total' => $item_sub_total,
                        'gst' => $item_gst,
                        'total' => $item_total,
                        'xero_line_item_id' => '',
                        'created' => DATE_TIME,
                        'updated' => DATE_TIME,
                        'archive' => 0,
                    );

                    $sub_total += $item_sub_total;

                    $attachId = $this->basic_model->insert_records('shift_line_item_attached', $attach_line_item, false);

                    // create line item transaction history
                    $objTran = new LineItemTransactionHistory();

                    $objTran->setLine_item_fund_used($item_total);
                    $objTran->setUser_plan_line_items_id($val->plan_line_itemId);
                    $objTran->setLine_item_fund_used_type(1);
                    $objTran->setLine_item_use_id($attachId);
                    $objTran->setStatus(0);
                    $objTran->setArchive(0);

                    $objTran->create_history();
                }
            }

            if (!empty($attach_line_item)) {
                $gst = calculate_gst($sub_total);
                $total = $gst + $sub_total;
                $this->basic_model->update_records('shift', ['gst' => $gst, 'price' => $total, 'sub_total' => $sub_total], ['id' => $shiftId]);

                $objTran->update_fund_blocked();
            }
        }
    }

    function make_shift_caller($responseAry, $shift_id) {
        $shift_caller = array('shiftId' => $shift_id,
            'firstname' => $responseAry->caller_name,
            'lastname' => isset($responseAry->caller_lastname) ? $responseAry->caller_lastname : '',
            'email' => $responseAry->caller_email,
            'phone' => $responseAry->caller_phone,
            'booker_id' => isset($responseAry->booker_id) ? $responseAry->booker_id : '',
            'booking_method' => $responseAry->booking_method
        );
        // insert caller data
        $this->Basic_model->insert_records('shift_caller', $shift_caller, $multiple = FALSE);
    }

    // calling at also from roster
    function make_shift_address($address_ary, $shift_id) {
        if (!empty($address_ary)) {
            foreach ($address_ary as $value) {
                $address = $value->street . ' ' . $value->suburb . ' ' . $value->state . ' ' . $value->postal;
                $lat_long = getLatLong($address);
                if (!empty($lat_long)) {
                    $lat = $lat_long['lat'];
                    $long = $lat_long['long'];
                }
                $shift_addr[] = array('shiftId' => $shift_id,
                    'address' => $value->street,
                    'suburb' => $value->suburb,
                    'state' => $value->state,
                    'postal' => $value->postal,
                    'lat' => isset($lat) ? $lat : '',
                    'long' => isset($long) ? $long : '',
                );
            }
            $this->CI->Listing_model->create_shift_location($shift_addr);
        }
    }

    function make_shift_requirement($shift_requirement_ary, $shift_id, $type = 0) {
        if (!empty($shift_requirement_ary)) {
            $shift_requirements = array();
            foreach ($shift_requirement_ary as $key => $val) {
                if (isset($val->checked)) {
                    $temp = [];
                    $temp = array(
                        'shiftId' => $shift_id,
                        'requirementId' => $val->value,
                        'requirement_type' => $type,
                        'requirement_other' => null,
                    );
                    if ($val->key_name == 'other') {
                        $temp['requirement_other'] = $val->other_title;
                    }
                    $shift_requirements[] = $temp;
                }
            }
            if (!empty($shift_requirements))
                $this->Basic_model->insert_records('shift_requirements', $shift_requirements, $multiple = TRUE);
        }
    }

    function make_org_shift_requirement($org_shift_requirement_ary, $shift_id) {
        if (!empty($org_shift_requirement_ary)) {
            $org_shift_requirements = array();
            foreach ($org_shift_requirement_ary as $val) {
                if (isset($val->checked)) {
                    $org_shift_requirements[] = array('shiftId' => $shift_id,
                        'requirementId' => $val->value,
                    );
                }
            }
            if (!empty($org_shift_requirements)) {
                $this->Basic_model->insert_records('shift_org_requirements', $org_shift_requirements, $multiple = TRUE);
            }
        }
    }

    function make_shift_preferred_member_and_allocate($preferred_member_ary, $allocate_preferred, $shift_id) {
        if (!empty($preferred_member_ary)) {
            $shift_member = array();
            $allocated_member = array();
            foreach ($preferred_member_ary as $key => $vall) {
                if (isset($vall->name->value)) {
                    $shift_member[] = array('shiftId' => $shift_id, 'memberId' => $vall->name->value);
                    $allocated_member[] = array('shiftId' => $shift_id, 'memberId' => $vall->name->value, 'status' => 1, 'created' => DATE_TIME);
                }
            }
            if (!empty($shift_member)) {
                $this->Basic_model->insert_records('shift_preferred_member', $shift_member, $multiple = TRUE);
            }
            if ($allocate_preferred == 1 && !empty($allocated_member)) {
                $this->move_shift_to_unconfirmed($shift_id);
                $this->Basic_model->insert_records('shift_member', $allocated_member, $multiple = TRUE);
            }
        }
    }

    function make_shift_confirmation_details($responseAry, $shift_id) {
        $shift_confirmation = array(
            'shiftId' => $shift_id,
            'confirm_with' => $responseAry->confirm_with,
            'confirm_userId' => $responseAry->confirm_userId ?? 0,
            'confirm_by' => $responseAry->confirm_by,
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'phone' => '',
        );
        $shift_confirmation['firstname'] = $responseAry->confirm_with_f_name;
        $shift_confirmation['lastname'] = $responseAry->confirm_with_l_name;
        $shift_confirmation['email'] = $responseAry->confirm_with_email;
        $shift_confirmation['phone'] = $responseAry->confirm_with_mobile;

        // insert confirmation details
        $this->Basic_model->insert_records('shift_confirmation', $shift_confirmation, $multiple = FALSE);
    }

    function make_org_shift_user($site_lookup_ary, $shift_id) {
        if (!empty($site_lookup_ary)) {
            $shift_site = array();
            foreach ($site_lookup_ary as $key => $value) {
                if (isset($value->name->value)) {
                    $shift_site[] = array('shiftId' => $shift_id,
                        'siteId' => $value->name->value,
                        'created' => DATE_TIME,
                    );
                }
            }
            if (!empty($shift_site)) {
                $this->Basic_model->insert_records('shift_site', $shift_site, $multiple = true);
            }
        }
    }

    function make_house_shift_user($house_lookup_ary, $shift_id) {
        if (!empty($house_lookup_ary)) {
            $house_site = array();
            foreach ($house_lookup_ary as $key => $value) {
                if (isset($value->name->value)) {
                    $house_site[] = array('shiftId' => $shift_id,
                        'user_for' => $value->name->value,
                        'created' => DATE_TIME,
                        'user_type' => 7,
                        'archive' => 0
                    );
                }
            }
            if (!empty($house_site)) {
                $this->Basic_model->insert_records('shift_users', $house_site, $multiple = true);
            }
        }
    }

    function make_shift_participant_user($participant_member_ary, $shift_id) {
        if (!empty($participant_member_ary)) {
            $shift_participant = array();
            foreach ($participant_member_ary as $value) {
                if (isset($value->name->value)) {
                    $shift_participant[] = array('shiftId' => $shift_id,
                        'participantId' => $value->name->value,
                        'status' => 1,
                        'created' => DATE_TIME,
                    );
                }
            }
            if (!empty($shift_participant)) {
                $this->Basic_model->insert_records('shift_participant', $shift_participant, $multiple = true);
            }
        }
    }

    public function get_booking_list($participantId) {

        $tbl_participant_booking_list = 'tbl_participant_booking_list';
        $this->db->select(array("CONCAT(" . $tbl_participant_booking_list . ".firstname,' '," . $tbl_participant_booking_list . ".lastname ) as name", "id", "phone", "email", "firstname", "lastname"));
        $this->db->where('participantId', $participantId);
        $this->db->where('archive', '0');
        $query = $this->db->get($tbl_participant_booking_list);
        $state = array();
        $query->result();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $state[] = array('label' => $val->name, 'value' => $val->id, 'firstname' => $val->firstname, 'lastname' => $val->lastname, 'email' => $val->email, 'phone' => $val->phone);
            }
        }
//        $state[] = array('label' => 'Other', 'value' => 'value');
        return $state;
    }

    function move_shift_to_unconfirmed($shiftId) {
        $this->basic_model->update_records('shift', array('status' => 2), $where = array('id' => $shiftId));
    }

    public function make_auto_fill_shift($shiftId) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $allocation = false;

        $objShift->setShiftId($shiftId);

        $shift_det = $objShift->get_shift_detail_with_required_level_and_paypoint();

        $objShift->setUserId($shift_det['userId']);
        $objShift->setBookedBy($shift_det['booked_by']);
        $objShift->setShiftDate($shift_det['shift_date']);
        $objShift->setStartTime($shift_det['start_time']);
        $objShift->setPre_selected_member([]);
        $objShift->setLimit(1);

        $objShift->setRequired_level($shift_det['required_level_priority']);
        $objShift->setRequired_paypoint($shift_det['required_point_priority']);

        $records = $objShift->get_available_member_by_city(true);

        if (!empty($records) && !empty($records['available_members'])) {
            $memberids = $records['available_members'];

            // we set already limit 1 then always get only one data and loop run only one time
            if (!empty($memberids)) {
                foreach ($memberids as $key => $memberId) {
                    $name = $records["member_details"][$memberId]["member_name"];
                    $allocate_member[] = array('memberId' => $memberId, 'shiftId' => $shiftId, 'status' => 1, 'created' => DATE_TIME);

                    $this->loges->setUserId($shiftId);
                    $this->loges->setDescription(json_encode($allocate_member));
                    $this->loges->setTitle('Assign shift to member ' . $name . ' : Shift Id ' . $shiftId);
                    $this->loges->createLog();
                    $allocation = true;
                }

                if (!empty($allocate_member)) {
                    $allocation = true;
                    $this->basic_model->insert_records('shift_member', $allocate_member, $multiple = true);
                    $this->move_shift_to_unconfirmed($shiftId);
                }
            }
        }
        return $allocation;
    }

    public function get_nearest_shift_member($reqData) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $limit = $reqData->memberLimit ?? 3;
        $objShift->setShiftId($reqData->shiftId);

        $shift_det = $objShift->get_shift_detail_with_required_level_and_paypoint();

        $objShift->setUserId($shift_det['userId']);
        $objShift->setBookedBy($shift_det['booked_by']);
        $objShift->setShiftDate($shift_det['shift_date']);
        $objShift->setStartTime($shift_det['start_time']);
        $objShift->setPre_selected_member([]);
        $objShift->setAvailable_member_order($reqData->member_lookup);
        $objShift->setLimit($limit);

        $objShift->setRequired_level($shift_det['required_level_priority']);
        $objShift->setRequired_paypoint($shift_det['required_point_priority']);


        if ($shift_det['booked_by'] == 1) {
            $participantId = 0;
        } elseif ($shift_det['booked_by'] == 2) {
            $participantId = $shift_det['userId'];
        }

        $records = $objShift->get_available_member_by_city(true);

        $memberIds = $records['available_members'];
        $temp = $memberIds;

        $available_members = array();

        if (!empty($temp)) {
            foreach ($temp as $key => $value) {
                $avail_temp['memberId'] = $value;
                $avail_temp['memberName'] = $records["member_details"][$value]["member_name"];

                $available_members[] = $avail_temp;
                if (($key + 1) == $limit)
                    break;
            }
        }
        return $available_members;
    }

    public function get_available_member_by_preferences($memberIds, $participantIds, $limit) {
        $particpantID = $participantIds[0];
        $tbl_participant_place = TBL_PREFIX . 'participant_place';
        $tbl_member_place = TBL_PREFIX . 'member_place';
        $resultPlace = array();
        $this->db->select(array("count(tbl_participant_place.placeId) as cnt", "tbl_member_place.memberId"));
        $this->db->from($tbl_participant_place);
        $this->db->where('participantId', $particpantID);
        $this->db->join($tbl_member_place, $tbl_member_place . '.placeId = ' . $tbl_participant_place . '.placeId AND ' . $tbl_member_place . '.memberId IN (' . implode(', ', $memberIds) . ')', 'inner');
        // $this->db->join($tbl_place, $tbl_member_place . '.placeId = ' . $tbl_place . '.id', 'inner');
        $this->db->group_by("tbl_member_place.memberId");
        $this->db->order_by("cnt", 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get();
        $place_member = $query->result_array();

        if (!empty($place_member)) {
            return array_column($place_member, 'memberId');
        } else {
            return $memberIds;
        }
    }

    public function get_member_name1($memberId) {
        $tbl_member = 'tbl_member';
        $this->db->select("CONCAT(" . $tbl_member . ".firstname,' '," . $tbl_member . ".middlename,' '," . $tbl_member . ".lastname ) as memberName");
        $this->db->from($tbl_member);
        $this->db->where($tbl_member . '.id', $memberId);
        $query = $this->db->get();
        return $result = $query->row();
    }

    public function get_previous_shifts($participantID, $shiftIds) {
        $tbl_shift = TBL_PREFIX . 'shift';
        $tbl_shift_participant = TBL_PREFIX . 'shift_participant';
        $select_column = array($tbl_shift . ".id", $tbl_shift . ".shift_date", $tbl_shift . ".start_time", $tbl_shift . ".end_time", $tbl_shift . ".status");
        $this->db->select("CONCAT(MOD( TIMESTAMPDIFF(hour," . $tbl_shift . ".start_time," . $tbl_shift . ".end_time), 24), ':',MOD( TIMESTAMPDIFF(minute," . $tbl_shift . ".start_time," . $tbl_shift . ".end_time), 60), ' hrs') as duration");
        $this->db->select($select_column);
        $this->db->from($tbl_shift_participant);
        $this->db->join($tbl_shift, $tbl_shift_participant . '.shiftId = ' . $tbl_shift . '.id', 'inner');
        $this->db->where_in('shift_date', $shiftIds);
        $this->db->where_in($tbl_shift . '.status', array(1, 2, 3, 4, 5, 6, 7));
        $this->db->where('participantId', $participantID);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->result_array();
        if (!empty($response)) {
            foreach ($response as $key => $val) {
                $response[$key] = $val;
                $response[$key]['duration'] = ($val['duration']);
            }
        }
        return $response;
    }

    function get_participant_shift_related_information($participantId) {
        $tbl_participant = TBL_PREFIX . 'participant';
        $tbl_participant_address = TBL_PREFIX . 'participant_address';
        $tbl_participant_email = TBL_PREFIX . 'participant_email';
        $tbl_participant_phone = TBL_PREFIX . 'participant_phone';
        $this->db->select(array($tbl_participant_address . '.street as address', $tbl_participant_address . '.city as suburb', $tbl_participant_address . '.postal', $tbl_participant_address . '.state', $tbl_participant_address . '.lat', $tbl_participant_address . '.long'));
        $this->db->select(array($tbl_participant . '.firstname', $tbl_participant . '.lastname', $tbl_participant . '.prefer_contact'));
        $this->db->select(array($tbl_participant_email . '.email', $tbl_participant_phone . '.phone'));
        $this->db->from($tbl_participant);
        $this->db->join($tbl_participant_address, $tbl_participant_address . '.participantId = ' . $tbl_participant . '.id', 'inner');
        $this->db->join($tbl_participant_email, $tbl_participant_email . '.participantId = ' . $tbl_participant . '.id AND ' . $tbl_participant_email . '.primary_email = 1', 'inner');
        $this->db->join($tbl_participant_phone, $tbl_participant_phone . '.participantId = ' . $tbl_participant . '.id AND ' . $tbl_participant_phone . '.primary_phone = 1', 'inner');
        $this->db->where($tbl_participant . '.id', $participantId);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $response = $query->row();
        $response->requirement = $this->basic_model->get_record_where('participant_assistance', array('assistanceId'), $where = array('participantId' => $participantId, 'type' => 'assistance'));
        return $response;
    }

    function check_shift_exist($objShift) {
        $responseAry = $objShift;

        $start_time = date('Y-m-d H:i:s', strtotime($responseAry->getStartTime()));
        $end_time = date('Y-m-d H:i:s', strtotime($responseAry->getEndTime()));

        if ($objShift->getBookedBy() == 1) {
            $this->db->join('tbl_shift_site as ss', 'ss.shiftId = s.id', 'inner');
            $this->db->where('ss.siteId', $objShift->getUserId());
        } elseif ($objShift->getBookedBy() == 2) {
            $this->db->join('tbl_shift_participant as sp', 'sp.shiftId = s.id', 'inner');
            $this->db->where('sp.participantId', $objShift->getUserId());
        } else if ($objShift->getBookedBy() == 7) {
            $this->db->join('tbl_shift_users as su', 'su.shiftId = s.id', 'inner');
            $this->db->where('su.user_for', $objShift->getUserId());
            $this->db->where('su.user_type', $objShift->getBookedBy());
        }

        $this->db->select(array("s.id", "s.start_time", "s.end_time", "s.status"));
        $this->db->from('tbl_shift as s');

        $where = "(('" . $start_time . "' BETWEEN (s.start_time) AND (s.end_time))  or ('" . $end_time . "' BETWEEN (start_time) AND (end_time)) OR
        ((s.start_time) BETWEEN '" . $start_time . "' AND '" . $end_time . "')  or ((s.end_time) BETWEEN '" . $start_time . "' AND '" . $end_time . "'))";
        $this->db->where($where);
        $this->db->where_in('s.status', [1, 2, 4, 7]);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        return $query->result();
    }


    public function get_house_key_billing_persion($siteHouseId, $type = 1) {
        $colown = array('contact.id as value', 'contact.lastname', 'contact.firstname', 'concat(contact.firstname," ",contact.lastname) as label', 'contact_email.email', 'contact_phone.phone', "contact.type");
        $this->db->select($colown);
        $this->db->from('tbl_house_and_site_key_contact as contact');
        $this->db->join('tbl_house_and_site_key_contact_email as contact_email', 'contact_email.contactId = contact.id and contact_email.primary_email = "1"', 'left');
        $this->db->join('tbl_house_and_site_key_contact_phone as contact_phone', 'contact_phone.contactId = contact.id AND contact_phone.primary_phone = "1"', 'left');
        $this->db->where('contact.archive', "0");
        $this->db->where('contact.user_type', $type);
        if ($type == 1) {
            $this->db->where_in('contact.type', [3, 4]);
        } else if ($type == 2) {
            $this->db->where_in('contact.type', [3, 1]);
        }
        $this->db->where('contact.siteId', $siteHouseId);
        $query = $this->db->get();

        $result = $query->result();

        $res = ['bookingList' => [], 'confirmList' => [], 'parent_org_name' => ''];
        if (!empty($result)) {
            foreach ($result as $val) {
                if ($val->type == 3) {
                    $res['bookingList'][] = $val;
                } else {
                    $res['confirmList'][] = $val;
                }
            }
        }
        if ($type == 1) {
            $res['parent_org_name'] = $this->get_parent_organisation_name_by_site_id($siteHouseId);
        }

        return $res;
    }

    public function get_member_name($post_data) {
        $pre_selected = array();
        if (!empty($post_data['pre_selected'])) {
            foreach ($post_data['pre_selected'] as $val) {
                if (!empty($val->member)) {
                    $pre_selected[] = $val->member->value;
                }
            }
        }
        if (!empty($post_data['pre_assign'])) {
            foreach ($post_data['pre_assign'] as $val) {
                $pre_selected[] = $val->memberId;
            }
        }
        $search = $post_data['search'];
        $this->db->like("CONCAT(m.firstname,' ',m.middlename,' ',m.lastname)", $search);

        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);
        if (!empty($pre_selected)) {
            $this->db->where_not_in('id', implode(', ', $pre_selected));
        }
        $this->db->select("CONCAT(firstname,' ',middlename,' ',lastname) as label");
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "external_staff"');
        $this->db->select(array('m.id as value'));
        $query = $this->db->get(TBL_PREFIX . 'member as m');
        return $query->result();
    }

    function get_canceler_list($reqData) {
        $reqData->shiftId;
        $reqData->cancel_type;
        $shift_participant = $this->Listing_model->get_shift_participant($reqData->shiftId);
        if (!empty($shift_participant)) {
            $participatnId = $shift_participant[0]['participantId'];
            if ($reqData->cancel_type == 'kin') {
                $where = array('participantId' => $participatnId);
                $column = array("concat(firstname,' ',lastname) as  label", "id as value");
                $result = $this->basic_model->get_record_where('participant_kin', $column, $where);
            } else if ($reqData->cancel_type == 'booker') {
                $where = array('participantId' => $participatnId, 'archive' => '0');
                $column = array("concat(firstname,' ',lastname) as  label", "id as value");
                $result = $this->basic_model->get_record_where('participant_booking_list', $column, $where);
            }
            return $result;
        }
    }

    function cancel_shift($reqData) {
        $cancel_by = '';
        #pr($reqData);
        $response = $this->Listing_model->get_accepted_shift_member($reqData->shiftId);
        // set member cancelled shift
        if (!empty($response)) {
            $cancel_by = $response[0]->memberId;
        }


        if ($reqData->cancel_type == 'member') {
            $this->basic_model->update_records('shift_member', $data = array('status' => 4, "updated" => DATE_TIME), $where = array('shiftId' => $reqData->shiftId, "status" => 3));
            $cancel_by = $response[0]->memberId;
        } else if ($reqData->cancel_type == 'participant') {

            $response = $this->Listing_model->get_shift_participant($reqData->shiftId);
            if (!empty($response))
                $cancel_by = $response[0]['participantId'];
        }else if ($reqData->cancel_type == 'kin') {
            $cancel_by = $reqData->cancel_person;
        } else if ($reqData->cancel_type == 'booker') {
            $cancel_by = $reqData->cancel_person;
        } else if ($reqData->cancel_type == 'org') {
            $result = $this->Listing_model->get_shift_oganization($reqData->shiftId);
            if (!empty($result)) {
                $cancel_by = $result[0]->siteId;
            }
        } else if ($reqData->cancel_type == 'site') {
            $result = $this->Listing_model->get_shift_oganization($reqData->shiftId);
            if (!empty($result)) {
                $cancel_by = $result[0]->siteId;
            }
        }

        if ($reqData->reason === 'other') {
            $reqData->reason = $reqData->other_reason;
        }

        $cancel_array = array(
            'shiftId' => $reqData->shiftId,
            'reason' => $reqData->reason,
            'cancel_type' => $reqData->cancel_type,
            'cancel_by' => $cancel_by,
            'cancel_method' => $reqData->cancel_method,
            'person_name' => ''
        );

        $this->basic_model->insert_records('shift_cancelled', $cancel_array);

        // if shift cancel by member then no need to release fund because its going in unfilled
        if ($reqData->cancel_type === 'member') {
            $this->basic_model->update_records('shift', $data = array('status' => 1), $where = array('id' => $reqData->shiftId));
        } else {
            require_once APPPATH . 'Classes/Finance/LineItemTransactionHistory.php';
            $objTran = new \LineItemTransactionHistory();

            $objTran->setShiftId($reqData->shiftId);
            $objTran->relese_fund_by_shiftId();

            $this->basic_model->update_records('shift', $data = array('status' => 5), $where = array('id' => $reqData->shiftId));
        }

        $this->loges->setUserId($reqData->shiftId);
        $this->loges->setDescription(json_encode($reqData));
        $this->loges->setTitle('Cancel shift : Shift Id ' . $reqData->shiftId);
        $this->loges->createLog();

        /* Send notification */
        $shift_details = $this->basic_model->get_row('shift', ['booked_by'], ['id' => $reqData->shiftId]);
        if ($shift_details) {
            $row_booked_by = $shift_details->booked_by ?? 0;
            if ($row_booked_by == 2) {
                $msg = 'Shift is cancelled by:- ' . $reqData->cancel_type . ' and his id:- ' . $cancel_by . 'Reason:- ' . $reqData->reason;
                $data_ary_for_participant = ['user_type' => 2, 'sender_type' => 2, 'user_id' => $cancel_by, 'title' => "Shift is cancelled (Shift id $reqData->shiftId)", 'description' => $msg];
                save_notification($data_ary_for_participant);
            }
        }
        /**/
        return $return = array('status' => true);
    }

    function get_shift_logs($reqData) {
        $colowmn = array('lg.title', "DATE_FORMAT(lg.created, '%d/%m/%Y') as created", "DATE_FORMAT(lg.created, '%h:%i %p') as time");
        $this->db->select($colowmn);
        $this->db->from('tbl_logs as lg');
        $this->db->join('tbl_module_title as mt', 'mt.id = lg.sub_module', 'INNER');
        $this->db->where('lg.userId', $reqData->shiftId);
        $this->db->where('mt.key_name', 'shift');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_shift_requirement_for_participant($participantId = 0) {
        $result = ['assistance' => [], 'mobility' => []];

        $query = $this->get_shift_requirement_for_participant_by_participant_id_or_shift_id('1', $participantId);
        $res = $query->result();
        if (!empty($res)) {
            foreach ($res as $val) {
                if ($val->type == 'assistance') {
                    $result[$val->type][] = $val;
                } else {
                    $result[$val->type][] = $val;
                }
            }
        }

        return $result;
    }

    function get_user_specific_plan_line_item($reqData) {
        $result = [];

        $user_type = $reqData->booked_by == 3 ? 2 : $reqData->booked_by;
        $funding_type = $reqData->funding_type ?? 1;
        $day = $reqData->day ?? false;
        $userId = $reqData->userId ?? false;

        $specific_time = [];
        $daytimeHaving = '';
        if (!empty($reqData->time_of_days)) {
            //$this->db->group_start();
            foreach ($reqData->time_of_days as $val) {
                if (!empty($val->checked)) {
                    $specific_time[] = $val->id;
                    $this->db->or_having("FIND_IN_SET('" . $val->id . "', day_time)", null, false);
                }
            }
            //$this->db->group_end();
            $daytimeHaving = $this->db->get_compiled_select();
            $daytimeHaving = explode('HAVING', $daytimeHaving);
            $daytimeHaving = isset($daytimeHaving[1]) ? '(' . $daytimeHaving[1] . ')' : '';
        }
        $stateIds = array_column($reqData->completeAddress, 'state');
        $stateIds = !empty($stateIds) ? array_unique($stateIds) : [0];

        if (!empty($reqData->time_of_days)) {
            //$this->db->group_start();
            foreach ($reqData->time_of_days as $val) {
                if (!empty($val->checked)) {
                    $specific_time[] = $val->id;
                    $this->db->or_having("FIND_IN_SET('" . $val->id . "', day_time)", null, false);
                }
            }
            //$this->db->group_end();
            $daytimeHaving = $this->db->get_compiled_select();
            $daytimeHaving = explode('HAVING', $daytimeHaving);
            $daytimeHaving = isset($daytimeHaving[1]) ? '(' . $daytimeHaving[1] . ')' : '';
        }

        $stateId = (!empty($reqData->completeAddress[0]->state)) ? $reqData->completeAddress[0]->state : 0;
        $postcode = (!empty($reqData->completeAddress[0]->postcode)) ? $reqData->completeAddress[0]->postcode : 0;
        $price_type = get_price_type_on_base_postcode($postcode);

        $start_time = !empty($reqData->start_time) ? DateFormate($reqData->start_time, "Y-m-d H:i:s") : '';
        $end_time = !empty($reqData->end_time) ? DateFormate($reqData->end_time, "Y-m-d H:i:s") : '';



        $its_public_holiday = false;
        if ($userId > 0 && $stateId > 0 && $start_time && $end_time && $day) {
            $s = DateFormate($start_time, 'Y-m-d');
            $e = DateFormate($end_time, 'Y-m-d');
            $its_public_holiday = $this->check_date_its_public_holiday([$s, $e], $stateId);

            if ($its_public_holiday) {
                $this->db->where('fli.public_holiday', 1);
            }

            if ($user_type == 7 || $user_type == 1) {
                if (!empty($reqData->support_line_item_category_filter_selected_by)) {
                    $this->db->where('fli.support_category', $reqData->support_line_item_category_filter_selected_by);
                }

                if (!empty($reqData->support_line_item_category_filter_selected_search)) {
                    $this->db->group_start();
                    $this->db->like("fli.line_item_name", $reqData->support_line_item_category_filter_selected_search);
                    $this->db->or_like("fli.line_item_number", $reqData->support_line_item_category_filter_selected_search);
                    $this->db->group_end();
                }
            }

            if ($user_type == 2 || $user_type == 3) {
                $this->db->select(['ppli.id as plan_line_itemId', 'ppli.fund_remaining']);
            } else {
                $this->db->select(["'0' as plan_line_itemId", "'0' as fund_remaining"], false);
            }
            $this->db->select(['fsc.name as support_item_name', 'fsc.id as support_item_number', 'fli.line_item_number', 'fli.line_item_name', 'fli.id as line_itemId']);
            $this->db->select(['group_concat(distinct day.short_name) as week_day']);

            if ($price_type == 1) {
                $this->db->select(['fli.upper_price_limit as cost']);
            } elseif ($price_type == 2) {
                $this->db->select(['fli.national_price_limit as cost']);
            } else {
                $this->db->select(['fli.national_very_price_limit as cost']);
            }

            if ($user_type == 2 || $user_type == 3) {
                $this->db->from('tbl_user_plan_line_items as ppli');
                $this->db->join('tbl_user_plan as pp', 'pp.id = ppli.user_planId AND pp.archive = 0');
                $this->db->join('tbl_finance_line_item as fli', 'fli.id = ppli.line_itemId', 'INNER');
            } else {
                $this->db->from('tbl_finance_line_item as fli');
            }
            $this->db->join('tbl_finance_support_category as fsc', 'fsc.id = fli.support_category', 'INNER');

            if ($user_type == 2 || $user_type == 3) {
                $this->db->select(["fli.start_date as line_item_start_date", "fli.end_date as line_item_end_date"]);
                $this->db->where('pp.user_type', $user_type);
                $this->db->where('pp.userId', $userId);

                $this->db->where("date('" . $start_time . "') between pp.start_date AND pp.end_date", null, false);
                $this->db->where("date('" . $end_time . "') between pp.start_date AND pp.end_date", null, false);

                $this->db->where("date('" . $start_time . "') between fli.start_date AND fli.end_date", null, false);
                $this->db->where("date('" . $end_time . "') between fli.start_date AND fli.end_date", null, false);
            } else {
                $this->db->where("date('" . $start_time . "') between fli.start_date AND fli.end_date", null, false);
                $this->db->where("date('" . $end_time . "') between fli.start_date AND fli.end_date", null, false);
            }

            if (!empty($specific_time)) {
                $this->db->select(['group_concat(distinct time_day.id) as day_time']);
                $this->db->join('tbl_finance_line_item_applied_time as line_item_time', 'line_item_time.line_itemId = fli.id', 'INNER');
                $this->db->join('tbl_finance_time_of_the_day as time_day', 'time_day.id = line_item_time.finance_timeId', 'INNER');
                /* foreach ($specific_time as $t) {
                  $this->db->or_having("FIND_IN_SET('" . $t . "', day_time)", null, false);
                  } */
                $this->db->having($daytimeHaving, null, false);
            }

            if (!empty($stateIds)) {
                $this->db->select(['group_concat(distinct applied_state.stateId) as applied_state']);
                $this->db->join('tbl_finance_line_item_applied_state as applied_state', 'applied_state.line_itemId = fli.id and applied_state.archive=0', 'INNER');
                foreach ($stateIds as $s) {
                    $this->db->having("FIND_IN_SET('" . $s . "', applied_state)", null, false);
                }
            }

            $this->db->join('tbl_finance_line_item_applied_days as line_item_day', 'line_item_day.line_itemId = fli.id', 'INNER');
            $this->db->join('tbl_week_day as day', 'day.id = line_item_day.week_dayId', 'INNER');
            $this->db->join('tbl_finance_measure as fm', 'fm.id = fli.measure_by AND fm.kay_name = "hourly"', 'INNER');

            if ($user_type == 2 || $user_type == 3) {
                $this->db->group_by('ppli.id');
                $this->db->where('ppli.fund_remaining > 0', null, false);
            } else {
                $this->db->group_by('fli.id');
                $this->db->where('fli.funding_type', $funding_type);
            }

            foreach ($day as $d) {
                $this->db->having("FIND_IN_SET('" . $d . "', week_day)", null, false);
            }

            $this->db->group_by("fli.id");
            $result = $this->db->get()->result_array();
        }

        return ['lineItemList' => $result, 'its_public_holiday' => $its_public_holiday];
    }

    function get_user_specific_plan_line_item_old($reqData) {
        $result = [];

        $user_type = $reqData->booked_by == 3 ? 2 : $reqData->booked_by;

        if ($user_type == 1) {
            $userId = (!empty($reqData->site_lookup_ary[0]->name->value)) ? $reqData->site_lookup_ary[0]->name->value : 0;
        } else if ($user_type == 7) {
            $userId = (!empty($reqData->house_lookup_ary[0]->name->value)) ? $reqData->house_lookup_ary[0]->name->value : 0;
        } else {
            $userId = (!empty($reqData->participant_member_ary[0]->name->value)) ? $reqData->participant_member_ary[0]->name->value : 0;
        }


        $stateId = (!empty($reqData->completeAddress[0]->state)) ? $reqData->completeAddress[0]->state : 0;
        $postcode = (!empty($reqData->completeAddress[0]->postcode)) ? $reqData->completeAddress[0]->postcode : 0;
        $price_type = get_price_type_on_base_postcode($postcode);

        $start_time = !empty($reqData->start_time) ? DateFormate($reqData->start_time, "Y-m-d H:i:s") : '';
        $end_time = !empty($reqData->end_time) ? DateFormate($reqData->end_time, "Y-m-d H:i:s") : '';

        $day = '';
        if ($start_time && $end_time) {
            $first_day = DateFormate($start_time, "D");
            $seconday_day = DateFormate($end_time, "D");

            $day = ($first_day === $seconday_day) ? [$first_day] : [$first_day, $seconday_day];
        }

        $specific_time = [];
        if (!empty($reqData->time_of_days)) {
            foreach ($reqData->time_of_days as $val) {
                if (!empty($val->checked)) {
                    $specific_time[] = $val->id;
                }
            }
        }

        if ($userId > 0 && $stateId > 0 && $start_time && $end_time && $day) {
            $s = DateFormate($start_time, 'Y-m-d');
            $e = DateFormate($end_time, 'Y-m-d');
            $its_public_holiday = $this->check_date_its_public_holiday([$s, $e], $stateId);

            if ($its_public_holiday) {
                $this->db->where('fli.public_holiday', 1);
            }

            if ($user_type == 7 || $user_type == 1) {
                if (!empty($reqData->support_line_item_category_filter_selected_by)) {
                    $this->db->where('fli.support_category', $reqData->support_line_item_category_filter_selected_by);
                }

                if (!empty($reqData->support_line_item_category_filter_selected_search)) {
                    $this->db->group_start();
                    $this->db->like("fli.line_item_name", $reqData->support_line_item_category_filter_selected_search);
                    $this->db->or_like("fli.line_item_number", $reqData->support_line_item_category_filter_selected_search);
                    $this->db->group_end();
                }
            }

            $this->db->select(['ppli.id as plan_line_itemId', 'fsc.name as support_item_name', 'fsc.id as support_item_number', 'fli.line_item_number', 'fli.line_item_name', 'fli.id as line_itemId', 'ppli.fund_remaining']);
            $this->db->select(['group_concat(distinct day.short_name) as week_day']);

            if ($price_type == 1) {
                $this->db->select(['fli.upper_price_limit as cost']);
            } elseif ($price_type == 2) {
                $this->db->select(['fli.national_price_limit as cost']);
            } else {
                $this->db->select(['fli.national_very_price_limit as cost']);
            }


            $this->db->from('tbl_user_plan_line_items as ppli');
            $this->db->join('tbl_user_plan as pp', 'pp.id = ppli.user_planId AND pp.archive = 0');
            $this->db->join('tbl_finance_line_item as fli', 'fli.id = ppli.line_itemId', 'INNER');
            $this->db->join('tbl_finance_support_category as fsc', 'fsc.id = fli.support_registration_group', 'INNER');

            $this->db->where('pp.user_type', $user_type);
            $this->db->where('pp.userId', $userId);
            $this->db->where("date('" . $start_time . "') between pp.start_date AND pp.end_date", null, false);
            $this->db->where("date('" . $end_time . "') between pp.start_date AND pp.end_date", null, false);

            if (!empty($specific_time)) {
                $this->db->select(['group_concat(distinct time_day.id) as day_time']);
                $this->db->join('tbl_finance_line_item_applied_time as line_item_time', 'line_item_time.line_itemId = fli.id', 'INNER');
                $this->db->join('tbl_finance_time_of_the_day as time_day', 'time_day.id = line_item_time.finance_timeId', 'INNER');

                foreach ($specific_time as $t) {
                    $this->db->having("FIND_IN_SET('" . $t . "', day_time)", null, false);
                }
            }

            $this->db->join('tbl_finance_line_item_applied_days as line_item_day', 'line_item_day.line_itemId = fli.id', 'INNER');
            $this->db->join('tbl_week_day as day', 'day.id = line_item_day.week_dayId', 'INNER');
            $this->db->group_by('ppli.id');
            $this->db->where('ppli.fund_remaining > 0', null, false);

            foreach ($day as $d) {
                $this->db->having("FIND_IN_SET('" . $d . "', week_day)", null, false);
            }

            $result = $this->db->get()->result_array();
        }

        return ['lineItemList' => $result, 'its_public_holiday' => $its_public_holiday];
    }

    function check_date_its_public_holiday($dates, $stateId) {
        $this->db->select(['holiday_date']);
        $this->db->from('tbl_public_holiday');
        $this->db->where('stateId', $stateId);
        $this->db->where('archive', 0);
        $this->db->where_in('holiday_date', $dates);

        $result = $this->db->get()->row();
        if (!empty($result)) {
            return true;
        } else {
            return false;
        }
    }

    function archive_shift_old($shiftIds) {
        $update_data = ['status' => 8];
        $this->db->where_in('id', $shiftIds);

        $this->db->update("tbl_shift", $update_data);
        return $this->db->affected_rows();
    }

    public function get_house_name($post_data) {
        $this->db->like('name', $post_data, 'both');
        $this->db->where('archive =', 0);
        $this->db->where('status', 1);
        $this->db->select('name, id');
        $query = $this->db->get(TBL_PREFIX . 'house');
        $query->result();
        $participant_rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $participant_rows[] = array('label' => $val->name, 'value' => $val->id);
            }
        }
        return $participant_rows;
    }

    public function get_parent_organisation_name_by_site_id($siteId = 0) {
        $this->db->select(["CASE WHEN sub_o.parent_org=0 THEN sub_o.name WHEN sub_o.parent_org>0 THEN (SELECT sub_sub_o.name FROM tbl_organisation as sub_sub_o WHERE sub_sub_o.id=sub_o.id limit 1) ELSE '' END"]);
        $this->db->from("tbl_organisation as sub_o");
        $this->db->where("sub_o.id=os.organisationId", null, false);
        $subQuery = $this->db->get_compiled_select();
        $this->db->select(["(" . $subQuery . ") as parent_org_name"]);
        $this->db->from('tbl_organisation_site as os');
        $this->db->where('os.id', $siteId);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row()->parent_org_name : '';
    }

    public function get_participant_address_by_participant_id($parId = 0) {
        $this->db->select(["pa.id as value", "
            CONCAT_WS(', ',pa.street,pa.city,(CASE WHEN pa.state>0 THEN (SELECT sub_s.name FROM tbl_state as sub_s where sub_s.id=pa.state) END),pa.postal) as label",
            "pa.street",
            "pa.city as suburb",
            "pa.street",
            "pa.postal",
            "pa.primary_address",
            "pa.state"
        ]);
        $this->db->from('tbl_participant_address pa');
        $this->db->where("pa.participantId", $parId);
        $this->db->where("pa.archive", 0);
        $this->db->order_by("pa.primary_address", "DESC");
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function update_as_unconfirmed_if_shift_is_filled($shiftId) {
        $shift_details = $this->basic_model->get_row('shift', ['status'], ['id' => $shiftId]);
        $shift_details->status ?? 0;

        if ($shift_details->status == 7) {
            $this->basic_model->update_records("shift", ['status' => 2], ['id' => $shiftId]);
            $this->basic_model->update_records("shift_member", ['status' => 1], ['shiftId' => $shiftId, 'status' => 3]);

            $this->loges->setUserId($shiftId);
            $this->loges->setTitle('Shift went for reconfirmation to member: Shift Id ' . $shiftId);
            $this->loges->createLog();
        }

        return true;
    }

    public function get_shift_requirement_for_participant_by_participant_id_or_shift_id($type = 1, $id = 0) {
        if ($type == 1) {
            $columnCase = "CASE
                WHEN pg.key_name = 'other'
                THEN ( SELECT COALESCE((CASE
                WHEN pg.type='assistance' THEN  require_assistance_other
                WHEN pg.type='mobility' THEN  require_mobility_other
                ELSE ''
                END
            ),'') FROM tbl_participant_care_requirement as sub_pcr WHERE
            sub_pcr.participantId=pa.participantId LIMIT 1)  ELSE pg.name END
            ";
        }

        if ($type == 2) {
            $columnCase = "CASE
                WHEN pg.key_name = 'other' AND  COALESCE((SELECT 1 FROM tbl_shift_requirements as sub_spr WHERE sub_spr.shiftId=sp.shiftId AND pa.assistanceId=sub_spr.requirementId AND (sub_spr.requirement_other is null or sub_spr.requirement_other='') LIMIT 1),0)=1
                THEN ( SELECT COALESCE((CASE
                WHEN pg.type='assistance' THEN  require_assistance_other
                WHEN pg.type='mobility' THEN  require_mobility_other
                ELSE ''
                END
            ),'') FROM tbl_participant_care_requirement as sub_pcr WHERE sub_pcr.participantId=pa.participantId LIMIT 1)  ELSE (SELECT sub_spr.requirement_other FROM tbl_shift_requirements as sub_spr WHERE sub_spr.shiftId=sp.shiftId AND pa.assistanceId=sub_spr.requirementId LIMIT 1) END";
        }


        if ($type == 3) {
            $columnCase = "CASE
            WHEN pg.key_name= 'other'
            THEN CONCAT (pg.name,' (',coalesce(spr.requirement_other,'N/A'),')')
             ELSE
             pg.name END";
            //$columnCase .= " sub_pcr.participantId=sp.participantId LIMIT 1)  ELSE pg.name END";
        }


        if ($type == 4 || $type == 5) {
            $columnCase = "coalesce( '','')";
        }

        $colowmn = array($columnCase . " as other_title", $columnCase . " as other_value", "pg.name as label", "pg.id as value", "pg.type", 'pg.key_name', false);
        $this->db->select($colowmn);
        $this->db->from('tbl_participant_genral as pg');
        $this->db->where('pg.status', 1);
        if ($type == 1 || $type == 2) {
            $this->db->join('tbl_participant_assistance as pa', "pa.assistanceId = pg.id", 'INNER');
        }
        if ($type == 1) {
            $this->db->where("pa.participantId", $id);
        } else if ($type == 2) {
            $this->db->select(["pg.name", "pg.id", "
            COALESCE( (CASE
            WHEN pg.type='mobility'
            THEN (SELECT 1 FROM tbl_shift_requirements as sub_spr WHERE sub_spr.shiftId=sp.shiftId AND pa.assistanceId=sub_spr.requirementId AND sub_spr.requirement_type=1 LIMIT 1)
            WHEN pg.type='assistance'
            THEN (SELECT 1 FROM tbl_shift_requirements as sub_spr WHERE sub_spr.shiftId=sp.shiftId AND pa.assistanceId=sub_spr.requirementId AND sub_spr.requirement_type=2 LIMIT 1)
            ELSE '0'
            END),'0') as active
            "]);
            $this->db->join("tbl_shift_participant as sp", "sp.participantId=pa.participantId AND sp.shiftId='" . $id . "'", "inner");
        } else if ($type == 3) {
            $this->db->select([$columnCase . " as name"], false);
            $this->db->join('tbl_shift_requirements as spr', "spr.requirementId = pg.id AND spr.shiftId='" . $id . "'", 'INNER');
            $this->db->join("tbl_shift_participant as sp", "sp.shiftId=spr.shiftId", "inner");
        } else if ($type == 4) {
            $this->db->where('pg.type', 'assistance');
        } else if ($type == 5) {
            $this->db->where('pg.type', 'mobility');
        }
        $this->db->order_by('pg.order', 'ASC');
        $query = $this->db->get();
        if ($type == 2) {
            //last_query(1);
        }
        return $query;
    }

    function make_shift_requirement_participant($shift_requirement_ary, $shift_id, $type = 0) {
        if (!empty($shift_requirement_ary)) {
            $shift_requirements = array();
            foreach ($shift_requirement_ary as $key => $val) {
                if (isset($val->checked) && !empty($type)) {
                    $shift_requirements[] = array(
                        'shiftId' => $shift_id,
                        'requirementId' => $val->value,
                        'requirement_type' => $type
                    );
                }
            }
            if (!empty($shift_requirements))
                $this->Basic_model->insert_records('shift_participant_requirements', $shift_requirements, $multiple = TRUE);
        }
    }

    function get_manual_member_look_up_for_shift($reqData) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();

        $objShift->setShiftId($reqData->shiftId);

        $shift_details = $objShift->get_shift_detail_with_required_level_and_paypoint();
        $objShift->setBookedBy($shift_details['booked_by']);
        $objShift->setUserId($shift_details['userId']);

        $shared_count_place_sub_query = $this->Listing_model->count_of_preference_shared_places_sub_query($objShift);
        $shared_count_activity_sub_query = $this->Listing_model->count_of_preference_shared_activity_sub_query($objShift);
        $member_busy_sub_query = $this->Listing_model->get_member_who_busy_in_another_shift_at_day_sub_query($objShift);

        //$shift_time = getAvailabilityType($shift_details['shift_date']);
        $shiftTimeData = $this->Listing_model->get_shift_availabilityType_by_shift_id($objShift->getShiftId(), ['shift_table' => $objShift->getShiftTableName(true), 'shift_time_category_table' => $objShift->getShiftTimeCategoryTableName(true), 'alias_column' => 'availability_type_concat', 'get_having_condition' => 1]);
        $havingConditionData = $shiftTimeData['having_condition'] ?? '';
        $date = date('Y-m-d', strtotime($shift_details['shift_date']));

        $this->db->select("concat(cl.level_priority, cp.point_priority) as level_priority_concat");
        $this->db->select("GROUP_CONCAT(DISTINCT CASE WHEN DATE(s.start_time)=DATE(mal.availability_date) THEN mal.availability_type ELSE null END) as availability_type_concat");
        $this->db->select("GROUP_CONCAT(DISTINCT CASE WHEN DATE(s.end_time)=DATE(mal.availability_date) THEN mal.availability_type ELSE null END) as availability_type_data_last");
        $this->db->select("(" . $shared_count_place_sub_query . ") as shared_place_count", false);
        $this->db->select("(" . $shared_count_activity_sub_query . ") as shared_activity_count", false);
        $this->db->select(["concat_ws(' ', m.firstname, m.middlename, m.lastname) as label", "m.id as value"]);
        $this->db->from("tbl_member as m");

        $this->db->join('tbl_member_availability_list as mal', 'mal.memberId = m.id and mal.archive=0', 'INNER');
        $this->db->join('tbl_member_position_award as mpa', 'mpa.memberId = m.id AND mpa.archive = 0', 'INNER');
        $this->db->join('tbl_classification_level as cl', 'cl.id = mpa.level AND cl.archive = 0', 'INNER');
        $this->db->join('tbl_classification_point as cp', 'cp.id = mpa.pay_point AND cp.archive = 0', 'INNER');

        $this->db->join('tbl_recruitment_applicant_work_area as work_area', 'work_area.id = mpa.work_area', 'INNER');
        $this->db->join('tbl_funding_type as ft', 'ft.key_name = work_area.key AND ft.archive = 0', 'INNER');
        $this->db->join('tbl_shift as s', 's.funding_type = ft.id', 'INNER');

        $this->db->where("m.id NOT IN (" . $member_busy_sub_query . ")", null, false);

        //$this->db->where(array("mal.availability_date" => $date));
        $this->db->where("date(mal.availability_date) between  date(s.start_time) and date(s.end_time)", null, false);
        $this->db->where(array("m.status" => 1));
        $this->db->where(array("s.id" => $reqData->shiftId));
        $this->db->where(array("m.archive" => 0,"m.enable_app_access"=>1));
        //$this->db->where_in("mal.availability_type", $shift_time);

        $this->db->having("level_priority_concat>=" . $shift_details['required_level_priority'] . $shift_details['required_point_priority'], null, false);
        if (!empty($havingConditionData)) {
            $this->db->having($havingConditionData, null, false);
        }

//        $this->db->where("cl.level_priority >= " . $shift_details['required_level_priority'], null, false);
//        $this->db->where("cp.point_priority >= " . $shift_details['required_point_priority'], null, false);

        $this->db->group_by("m.id");
        $this->db->order_by("shared_activity_count desc, shared_place_count desc, cl.level_priority asc, cp.point_priority asc", null, false);

        $this->db->like("concat_ws(' ', m.firstname, m.middlename, m.lastname)", $reqData->search);

        $query = $this->db->get();
        #last_query(1);
        return $query->result_array();
    }

    function call_allocated($reqData) {
        $this->db->where(["shiftId" => $reqData->shiftId, "archive" => 0]);
        $this->db->where_in(["status" => [1, 3]]);
        $this->db->update("tbl_shift_member", ["confirmed_with_allocated" => DATE_TIME]);
        return $this->db->affected_rows();
    }

    function get_manual_member_look_up_for_create_shift($reqData, $other = []) {
        require_once APPPATH . 'Classes/shift/Shift.php';
        $objShift = new ShiftClass\Shift();
        $objShift->setShiftTableName($other['tableData']['shift_table']);
        $objShift->setShiftLocationTableName($other['tableData']['shift_location_tabel']);
        $objShift->setShiftLineItemTableName($other['tableData']['shift_line_item_tabel']);
        $objShift->setShiftTimeCategoryTableName($other['tableData']['shift_time_category_table']);
        $shiftId = 1;
        $objShift->setShiftId($shiftId);

        $shift_details = $objShift->get_shift_detail_with_required_level_and_paypoint(false);

        $objShift->setBookedBy($shift_details['booked_by']);
        $objShift->setUserId($reqData->userId); //when it shift not created only selected when create select
        $objShift->setShiftDate($shift_details['shift_date']);
        $objShift->setStartTime($shift_details['start_time']);
        $objShift->setPre_selected_member([]);
        $objShift->setRequired_level($shift_details['required_level_priority']);
        $objShift->setRequired_paypoint($shift_details['required_point_priority']);
        $objShift->setMemeberNameSearch($reqData->search); //when it shift not only name search
        $objShift->setRequest_for($other["request_type"] ?? ''); //when it shift not only name search

        $near_city_members = $objShift->get_available_member_by_city(true);
        return !empty($near_city_members['member_details']) ? array_values($near_city_members['member_details']) : [];
    }

    public function create_temp_shift_table($tempData = []) {
        $address_ary = $tempData['address_ary'] ?? [];
        $line_items = $tempData['line_items'] ?? [];
        $time_of_days = $tempData['time_of_days'] ?? [];
        $time_of_days = $tempData['time_of_days'] ?? [];
        $request_type = $tempData['request_type'] ?? 'create_shift';
        $shiftDataInsData = $tempData['shift_data_ins'] ?? ['booked_by' => 0, 'shift_date' => '0000-00-00 00:00:00', 'start_time' => '0000-00-00 00:00:00', 'end_time' => '0000-00-00 00:00:00', 'funding_type' => 0];
        if (!empty($shiftDataInsData)) {
            extract($shiftDataInsData);
        }
        $tblTtme = time();
        $table = 'shift_temp_' . $tblTtme;
        $tableLocation = 'shift_location_temp_' . $tblTtme;
        $tableLineItem = 'shift_line_item_attached_temp_' . $tblTtme;
        $tableShiftTimeCategory = 'shift_time_category_temp_' . $tblTtme;
        $tablePreFix = TBL_PREFIX . $table;
        $tableLocationPreFix = TBL_PREFIX . $tableLocation;
        $tableLineItemPreFix = TBL_PREFIX . $tableLineItem;
        $tableShiftTimeCategoryPreFix = TBL_PREFIX . $tableShiftTimeCategory;
        $this->db->query("DROP TABLE IF EXISTS " . $tablePreFix);
        $this->db->query("DROP TABLE IF EXISTS " . $tableLocationPreFix);
        $this->db->query("DROP TABLE IF EXISTS " . $tableLineItemPreFix);
        $this->db->query("DROP TABLE IF EXISTS " . $tableShiftTimeCategoryPreFix);
        $this->db->query(" CREATE TEMPORARY TABLE " . $tablePreFix . " (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `booked_by` smallint(5) unsigned DEFAULT NULL COMMENT '1 - site/2 - participant/3 - location(participant)/4- org/5 - sub-org/6 - reserve in quote/7-house',
            `shift_date` date DEFAULT NULL,
            `start_time` datetime NOT NULL,
            `end_time` datetime NOT NULL,
            `expenses` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
            `status` int(10) unsigned NOT NULL COMMENT '1-Unfilled/ 2- Unconfirmed / 3- Quote / 4 -Rejected / 5 -Cancelled / 6 - Completed / 7 - Confirmed / 8 - Archive',
            `created` timestamp NOT NULL,
            `funding_type` int(10) unsigned DEFAULT '1' COMMENT 'tbl_funding_type auto increment id',
            PRIMARY KEY (`id`) )");

        $this->db->query(" CREATE TEMPORARY TABLE " . $tableLocationPreFix . " (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `shiftId` int(10) unsigned NOT NULL,
            `address` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
            `suburb` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'city',
            `state` tinyint(3) unsigned NOT NULL,
            `postal` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
            `lat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            `long` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `" . $tableLocationPreFix . "_shiftid_index` (`shiftId`))");

        $this->db->query(" CREATE TEMPORARY TABLE " . $tableLineItemPreFix . " (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `shiftId` int(10) unsigned DEFAULT NULL COMMENT 'tbl_shift auto incremant id',
            `plan_line_itemId` int(10) unsigned NOT NULL COMMENT 'primary key tbl_user_plan_line_item',
            `line_item` int(10) unsigned DEFAULT NULL COMMENT 'tbl_finance_line_item auto increment id',
            `created` datetime NOT NULL,
            `archive` smallint(6) NOT NULL DEFAULT '0' COMMENT '0 -Not/1 - Archive',
            PRIMARY KEY (`id`)
        )");

        $this->db->query(" CREATE TEMPORARY TABLE " . $tableShiftTimeCategoryPreFix . " (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `shiftId` int(10) unsigned NOT NULL COMMENT 'primary key tbl_shift',
            `timeId` int(10) unsigned NOT NULL COMMENT 'primary key tbl_finance_time_of_the_day',
            `created` datetime NOT NULL,
            `archive` tinyint(3) unsigned NOT NULL COMMENT '0- Not/ 1 - Yes',
            PRIMARY KEY (`id`)
        )");

        $shiftIds = [];
        if ($request_type === 'create_roster') {
            if (!empty($tempData["shift_datetime"])) {
                foreach ($tempData["shift_datetime"] as $val) {
                    $val = (object) $val;
                    $insData[] = [
                        'booked_by' => $booked_by,
                        'shift_date' => $val->start_time,
                        'start_time' => $val->start_time,
                        'end_time' => $val->end_time,
                        'expenses' => 0,
                        'status' => 1,
                        'created' => DATE_TIME,
                        'funding_type' => $funding_type,
                    ];
                }

                $this->basic_model->insert_records($table, $insData, true);
                $x = $this->basic_model->get_record_where($table, '*', '');
                $shiftIds = array_column(obj_to_arr($x), 'id');
            }
        } else {
            $insData = [
                'booked_by' => $booked_by,
                'shift_date' => $shift_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'expenses' => 0,
                'status' => 1,
                'created' => DATE_TIME,
                'funding_type' => $funding_type,
            ];
            $shiftId = $this->basic_model->insert_records($table, $insData);
            $shiftIds = [$shiftId];
        }


        if (!empty($shiftIds)) {
            foreach ($shiftIds as $shiftId) {
                $shift_addr = [];
                if (!empty($address_ary)) {
                    foreach ($address_ary as $value) {
                        $address = $value->street . ' ' . $value->suburb . ' ' . $value->state . ' ' . $value->postal;
                        $lat_long = getLatLong($address);
                        if (!empty($lat_long)) {
                            $lat = $lat_long['lat'];
                            $long = $lat_long['long'];
                        }
                        $shift_addr[] = array('shiftId' => $shiftId,
                            'address' => $value->street,
                            'suburb' => $value->suburb,
                            'state' => $value->state,
                            'postal' => $value->postal,
                            'lat' => isset($lat) ? $lat : '',
                            'long' => isset($long) ? $long : '',
                        );
                    }


                    $this->basic_model->insert_records($tableLocation, $shift_addr, TRUE);
                }



                if (!empty($line_items)) {
                    $attach_line_item = [];
                    foreach ($line_items as $val) {
                        if (!empty($val->checked)) {
                            $attach_line_item[] = array(
                                'shiftId' => $shiftId,
                                'plan_line_itemId' => $val->plan_line_itemId,
                                'line_item' => $val->line_itemId,
                                'created' => DATE_TIME,
                                'archive' => 0,
                            );
                        }
                    }
                    if (!empty($attach_line_item)) {
                        $this->basic_model->insert_records($tableLineItem, $attach_line_item, true);
                    }
                }
                if (!empty($time_of_days)) {
                    $attach_time_of_days = [];
                    foreach ($time_of_days as $val) {
                        if (!empty($val->checked)) {
                            $attach_time_of_days[] = [
                                'shiftId' => $shiftId,
                                'timeId' => $val->id,
                                'created' => DATE_TIME,
                                'archive' => 0,
                            ];
                        }
                    }
                    if (!empty($attach_time_of_days)) {
                        $this->basic_model->insert_records($tableShiftTimeCategory, $attach_time_of_days, true);
                    }
                }
            }
        }

        return ['request_type' => $request_type, 'tableData' => ['shift_table' => $table, 'shift_location_tabel' => $tableLocation, 'shift_line_item_tabel' => $tableLineItem, 'shift_time_category_table' => $tableShiftTimeCategory]];
    }

    public function get_site_or_hosue_address_by_site_house_id(int $siteHousId = 0, int $type = 1) {
        $cityType = "sh.city";
        $tableName = "tbl_organisation_site";
        if ($type == 2) {
            $cityType = "sh.suburb";
            $tableName = "tbl_house";
        }
        $this->db->select(["sh.id as value",
            $cityType . " as suburb",
            "CONCAT_WS(', ',sh.street," . $cityType . ",(CASE WHEN sh.state>0 THEN (SELECT sub_s.name FROM tbl_state as sub_s where sub_s.id=sh.state) END),sh.postal) as label",
            "sh.street",
            "sh.postal",
            "0 as primary_address",
            "sh.state"
        ]);

        $this->db->from($tableName . ' sh');
        $this->db->where("sh.id", $siteHousId);
        $this->db->where("sh.archive", 0);
        $this->db->order_by("primary_address", "DESC");
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    function get_shift_cancel_reason_option()
    {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'shift_cancel_reason' AND rdt.archive = 0", "INNER");

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_skill_reference_data() {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'skill' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * To fetch the members' skills list
     */
    public function get_shift_skills_list($reqData) {

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("r.display_name");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, true);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $available_column = ["id","shift_id", "skill_id", "condition", "skill_name"];
        # sorting part
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ss.id';
            $direction = 'DESC';
        }
        $select_column = ["ss.id","ss.shift_id", "ss.skill_id", "ss.condition", "r.display_name as skill_name", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from('tbl_shift_skills as ss');
        $this->db->join('tbl_references as r', 'r.id = ss.skill_id', 'inner');
        $this->db->where('ss.archive', 0);

        if(isset($reqData->shift_id) && $reqData->shift_id > 0)
        $this->db->where('ss.shift_id', $reqData->shift_id);

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch Goals list successfully');
        return $return;
    }

    /**
     * fetching a single shift skill details
     */
    public function get_shift_skill_details($id) {
        if (empty($id)) return;

        $this->db->select("ms.*");

        $this->db->from('tbl_shift_skills as ms');
        $this->db->where("ms.id", $id);
        $this->db->where("ms.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null;#$ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            return array('msg' => "Shift skill not found!", 'status' => false);
        }else{
            $dataResult = $query->result();
            return array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        }
    }

    /**
     * checks if an entry exists for a member and skill
     */
    public function check_shift_skill_already_exist($shift_id,$skill_id,$id=0) {
        $this->db->select(array('r.display_name'));
        $this->db->from('tbl_shift_skills as ss');
        $this->db->join('tbl_references as r', 'r.id = ss.skill_id', 'inner');
        $this->db->where('ss.archive', 0);
        if($id>0)
            $this->db->where('ss.id != ', $id);

        $this->db->where("ss.shift_id", $shift_id);
        $this->db->where("ss.skill_id", $skill_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }
    /**
     * Saves relationship details of an account against multiple contacts
     *
     * @param array[] $data Multidimentional array
     * @return array
     */
    public function create_update_shift_skills(array $data = [], $archive_skill_id, $adminId)
    {
        $savedIds = [];

        $cnt = 0;
        foreach ($data as $shift_skill) {
            $id = $shift_skill['id'] ?? null;

            # is the object being altered currently by other user? if yes - cannot perform this action
            if($cnt == 0) {
                $lock_taken = $this->add_shift_lock($shift_skill['shift_id'], $adminId);
                if($lock_taken['status'] == false)
                return $lock_taken;
            }
            $cnt++;

            if ($id) {
                $where = ['id' => $id];
                $updateData = [
                    'shift_id' => $shift_skill['shift_id'],
                    'skill_id' => $shift_skill['skill_id'],
                    'condition' => $shift_skill['condition'],
                ];
                $this->db->update('tbl_shift_skills', $updateData, $where);
                $savedIds[] = $id;
            } else {
                $insertData = [
                    'shift_id' => $shift_skill['shift_id'],
                    'skill_id' => $shift_skill['skill_id'],
                    'condition' => $shift_skill['condition'],
                ];
                $this->db->insert('tbl_shift_skills', $insertData);

                $savedIds[] = $this->db->insert_id();
            }
        }

        foreach ($archive_skill_id as $shift_skill) {
            $id = $shift_skill ?? null;

            if ($id) {
                $where = ['id' => $id];
                $updateData = [
                    'archive' => 1,

                ];
                $this->db->update('tbl_shift_skills', $updateData, $where);
            }
        }

        # removing any access level locks taken by the admin user
        $shift_lock_res = $this->remove_shift_lock($shift_skill["shift_id"], $adminId);
        if($shift_lock_res['status'] == false)
            return $shift_lock_res;

        return [
            'status' => true,
            'savedIds' => $savedIds,
        ];
    }

    public function getRecipientTypes() {
        $object_recipient_types = [
            'Shift.Users' => 'Shift Users'
        ];
        $this->setRecipientTypes($object_recipient_types);
        return $this->recipient_types;
    }

    public function setScheduleRecipients() {
        //field should match with $this->object_fields
        $recipients['Shift.Users'] = [
            ['field' => 'Shift Member', 'label' => 'Shift Member'],
            ['field' => 'Participant', 'label' => 'Participant'],
            ['field' => 'Contact', 'label' => 'Contact']
        ];
        $this->setObjectRecipients($recipients);
    }

    public function getStatusOptions_old()
    {
        $status_options = [1 =>'Open', 2 =>'Published', 3 => 'Scheduled', 4 => 'In Progress', 5 => 'Completed', 6 => 'Cancelled'];
        return $status_options;
    }
    public function create_shift_publish_notification($data) {
        if(!empty($data)) {

            $result = $this->basic_model->get_record_where('shift_member', ['member_id'],
                ['shift_id' => $data->id, 'archive' => 0 ]);

            if(!empty($result)) {

                $shift_no = $data->shift_no;
                $owner_id = $data->owner_id;
                $id = $data->id;

                $time = '';
                if(!empty($data->scheduled_start_datetime)){

                    $time = date('d/m/y h:i A', strtotime($data->scheduled_start_datetime));
                }

                foreach($result as $res) {

                    $data = [
                        'userId'=> $res->member_id,
                        'user_type'=> 1,
                        'title' => "Shift Available - " . $time,
                        'shortdescription' => "You are added to shift " . $shift_no .
                             " scheduled for " . $time .".",
                        'created' => DATE_TIME,
                        'status' => 0,
                        'sender_type' => 2,
                        'specific_admin_user' => $owner_id,
                        'entity_type' => 8,
                        'entity_id' => $id
                    ];

                     $this->Notification_model->create_notification($data);
                }
            }
        }
    }

    /**
     *  Get list of roster based on account
     */
    public function get_roster_list($reqData) {
        if (isset($reqData) && isset($reqData->account)) {
            $account_id = $reqData->account->value;
            $account_type = $reqData->account->account_type;

            $column = ["r.roster_no as label", "r.id as value", "r.start_date", "r.end_date"];
            $this->db->select($column);
            $this->db->from(TBL_PREFIX . 'roster as r');
            $this->db->where(['r.account_id' => $account_id]);
            $this->db->where(['r.account_type' => $account_type]);
            $query = $this->db->get();
            $result = $query->num_rows() > 0 ? $query->result_array() : [];
            $result = [ "status" => true, 'msg' => 'Fetch roster successfully', 'data' => $result];
        } else {
            $result = [ "status" => false, 'error' => 'Account Id is null'];
        }
        return $result;
    }

    /**
     * To get contact list related to account
     */
    function get_contact_list_for_account($reqData, $allow_new_contact,$fromDataImport=false) {

        if (isset($reqData) && isset($reqData->account)) {
            $name = $reqData->query ?? '';
            $contact_id = $reqData->contact_id ?? '';

            // Get subquery of cerated & updated by
            $new_contact = array("label"=>"New Contact", "value"=>"new contact");

            $this->db->like('label', $name);

            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);

            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

            $default_id = '';
            $default = [];

            if($reqData->account->account_type == 1)
            {

                $participant_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label", "'contact' as type", "pe.email as subTitle"];
                $this->db->select($column);
                $this->db->from(TBL_PREFIX . 'participants_master as tp');
                if(!$fromDataImport)
                {

                    $this->db->group_start();
                    $this->db->or_like('sub_p.firstname', $name);
                    $this->db->or_like('sub_p.lastname', $name);
                    $this->db->group_end();
                }

                $this->db->where(['tp.id' => $participant_id]);
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = tp.contact_id", "LEFT");
                $this->db->join("tbl_person_email as pe", "pe.person_id = sub_p.id and pe.archive = 0", "LEFT");
                $this->db->having($queryHaving);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];
                // to add the new contact data
                if (isset($result) === true && isset($result[0]) === true) {
                    $default[0] = $result[0];
                    $default_id = $result[0]['value'];
                }

            } else {
                $org_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label", "'contact' as type", "pe.email as subTitle", "sr.is_primary"];
                $this->db->from(TBL_PREFIX . 'sales_relation as sr');
                $this->db->select($column);
                $this->db->where('sr.source_data_id', $org_id);
                $this->db->where('sr.source_data_type', '2');
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
                $this->db->join("tbl_person_email as pe", "pe.person_id = sub_p.id and pe.archive = 0", "LEFT");
                if(!$fromDataImport)
                {

                   $this->db->group_start();
                   $this->db->or_like('sub_p.firstname', $name);
                   $this->db->or_like('sub_p.lastname', $name);
                   $this->db->group_end();
                }
                $this->db->having($queryHaving);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];

                # Get Primary Contact
                $this->db->from(TBL_PREFIX . 'sales_relation as sr');
                $this->db->select($column);
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
                $this->db->join("tbl_person_email as pe", "pe.person_id = sub_p.id and pe.archive = 0", "LEFT");
                $this->db->where('sr.source_data_id', $org_id);
                $this->db->where('sr.source_data_type', '2');
                if ($contact_id != '') {
                    $this->db->where('sub_p.id', $contact_id);
                } else {
                    $this->db->where('sr.is_primary', '1');
                }

                $query_pri = $this->db->get();
                $result_pri = $query_pri->num_rows() > 0 ? $query_pri->result_array() : [];

                if (isset($result_pri) === true && isset($result_pri[0]) === true) {
                    $default[0] = $result_pri[0];
                    $default_id = $result_pri[0]['value'];
                }
            }
            return [ "status" => true, 'data' => $result, 'msg' => 'Successfully fetched', 'default_contact' => $default, 'default_contact_id' => $default_id];
        } else {
            return [ "status" => false, 'error' => 'Participant Id is null'];
        }
    }

    /**
     * Get list of service agreement based on account with status active only
     * @param {obj} reqData
     */
    public function get_service_agreement($reqData) {
        $data = (array) $reqData;
        $section = $data['section'] ?? '';
        $index = $section == 'scheduled' ? 'Schedule' : 'Actual';
        $validation_rules = [
            array('field' => 'start_date', 'label' => $index.' start date & time', 'rules' => 'required',
                'errors' => [
                    'valid_date_format' => 'Incorrect Schedule start date & time',
                ]),
            array('field' => 'end_date', 'label' => $index.' end date & time', 'rules' => 'required',
                'errors' => [
                    'valid_date_format' => 'Incorrect Schedule end date & time',
                ]),
        ]; 

        $this->form_validation->set_data($data);

        # set validation rule
        $this->form_validation->set_rules($validation_rules);

        # check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        if (isset($reqData) && isset($reqData->account)) {
            $participant_id = '';

            # Active status
            $sa_active_status = 5;

            if (isset($reqData->account) == true && isset($reqData->account->value) == true) {
                $participant_id = $reqData->account->value;
            }

            $start_date = $reqData->start_date;
            $end_date = $reqData->end_date;

            if ($start_date != '') {
                $start_date = date('Y-m-d', strtotime($start_date));
            }

            if ($end_date != '') {
                $end_date = date('Y-m-d', strtotime($end_date));
            }

            $column = [ "sa.service_agreement_id as label", "sa.id", "sa.contract_start_date", "sa.contract_end_date", "sa.status", "sa.plan_start_date", "sa.plan_end_date", 'ref.key_name as sa_type' ];
            $this->db->select($column);
            $this->db->from(TBL_PREFIX . 'service_agreement as sa');
            $this->db->join(TBL_PREFIX . 'opportunity as o', 'sa.opportunity_id = o.id', 'left');
            $this->db->join(TBL_PREFIX . 'references as ref', 'o.opportunity_type = ref.id', 'left');
            $this->db->where(['sa.participant_id' => $participant_id, 'sa.status' => $sa_active_status ]);
            $this->db->where("
            STR_TO_DATE('{$start_date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`sa`.`contract_start_date`, '%Y-%m-%d') AND DATE_FORMAT(`sa`.`contract_end_date`, '%Y-%m-%d') AND
            STR_TO_DATE('{$end_date}', '%Y-%m-%d') BETWEEN DATE_FORMAT(`sa`.`contract_start_date`, '%Y-%m-%d') AND DATE_FORMAT(`sa`.`contract_end_date`, '%Y-%m-%d')
            ");
            $this->db->where('sa.archive',0);
            $this->db->limit(1);
            $query = $this->db->get();
            $serviceAgreement = $query->num_rows() > 0 ? $query->row_array() : [];

            $serviceBooking = [];
            $SAAttachment = [];
            $serviceAgreementPayments = [];
            $managed = [ 1 , 2 ];
            $receive_signed = true;
            $service_booking_id = '';
            $signed_status = '';
            $service_booking_needed = false;
            if (!empty($serviceAgreement) && !empty($serviceAgreement['id'])) {
                $service_agreement_id = $serviceAgreement['id'];

                $this->load->model('../../sales/models/ServiceAgreement_model');
                # get service agreement attachment
                $SAAttachment = $this->ServiceAgreement_model->service_docusign_data_by_id($service_agreement_id);

                if (!empty($SAAttachment)) {
                    $docusign_id = $SAAttachment['id'];
                    $signed_status = $SAAttachment['signed_status'];
                    # get payment methods
                    $serviceAgreementPayments = $this->ServiceAgreement_model->service_agreement_payments($service_agreement_id);

                    if (!empty($serviceAgreementPayments['managed_type']) && in_array($serviceAgreementPayments['managed_type'], $managed)) {
                        $service_booking_needed = true;
                        $this->load->model('../../sales/models/Service_booking_model');
                        $serviceBooking = $this->Service_booking_model->get_service_booking_with_status_by_id($service_agreement_id, $start_date, $docusign_id);
                        $sa_b_in = 0;
                        foreach($serviceBooking as $record) {
                            if ($record['status'] != 'active') {
                                $receive_signed = false;
                                break;
                            }
                            if ($record['is_received_signed'] != '1') {
                                $receive_signed = false;
                                break;
                            }
                            if ($sa_b_in != 0) {
                                $service_booking_id = $record['id'];
                                $sa_b_in++;
                            }
                        }
                    }
                }
            }

            $rule = 0;
            $data = [];

            # BR - 1 Shift start & end data not meet with plan date
            if (empty($SAAttachment)) { 
                $rule = 1;
                $status = false;
                $ndis = '';
                if (!empty($serviceAgreement) && $serviceAgreement['sa_type'] === 'ndis') {
                    $ndis = ' NDIS';
                }
                $msg = "No Signed$ndis Service Agreement exists for the requested shift date";
            }
            # BR - 2 Check if there is an ‘Active’ service booking with the Service Agreement Type ‘NDIS’. The ‘Date Submitted’ in the Service Booking
            else if (empty($serviceBooking) && !empty($serviceAgreementPayments['managed_type']) && in_array($serviceAgreementPayments['managed_type'], $managed)) {
                $status = false;
                $rule = 2;
                $msg = 'No Service Booking exists for the requested shift date';
            }
            # BR - 3 Check if the existing ‘Active’ service booking is signed by the NDIS, which implies that the value for ‘Received Signed Service Booking’ in the Service Booking
            else if ($receive_signed == false) {
                $rule = 3;
                $status = false;
                $msg = 'Existing Service Booking for the requested shift date is not signed';
            } else if (empty($serviceAgreement)) {
                $status = false;
                $data['service_booking_id'] = $service_booking_id;
                $msg = 'No Service Agreement exists for the requested shift date';
            } else {
                $status = true;
                $data['service_booking_id'] = $service_booking_id;

                $msg = 'Fetch service agreement successfully';
            }

            # return
            $data['service_agreement_id'] = $serviceAgreement['id'] ?? '';
            $data['docusign_id'] = $SAAttachment['id'] ?? '';
            $data['docusign_url'] = $SAAttachment['url'] ?? '';
            $data['docusign_related'] = $SAAttachment['related'] ?? '';
            $data['signed_status'] = $signed_status;
            $data['service_booking_needed'] = $service_booking_needed;
            $data['rule'] = $rule;

            if ($status == true) {
                $result = [ "status" => true, 'msg' => 'Fetch service agreement successfully', 'data' => $data];
            } else {
                $result = [ "status" => false, 'error' => $msg, 'rule' => $rule, 'data' => $data ];
            }
        } else {
            $result = [ "status" => false, 'error' => 'Participant Id is null'];
        }
        return $result;
    }

    /**
     * Get list of service agreement line item list
     * @param {obj} reqData
     */
    public function get_service_agreement_line_item_list($reqData) {

        if (isset($reqData) && isset($reqData->service_agreement_id)) {
            $service_agreement_id = '';

            if (isset($reqData->service_agreement_id) == true && isset($reqData->service_agreement_id) == true) {
                $service_agreement_id = $reqData->service_agreement_id;
            }

            $this->db->from(TBL_PREFIX . 'service_agreement_items as sai');
            $this->db->select(array('sai.id as sa_line_item_id', 'sai.line_item_id as value', 'fli.line_item_name as label', 'fli.line_item_number', 'fli.category_ref', 'fsc.name as support_cat'));
            $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
            $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");
            $this->db->join("tbl_finance_support_category as fsc", "fsc.id = fli.support_category AND fsc.archive=0", "inner");
            $this->db->where(array('sai.archive' => 0, 'sai.service_agreement_id' => $service_agreement_id));
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $item_ary = $query->result();

            $sorted = [];
            foreach ($item_ary as $li) {
                if (empty($li->category_ref)) { // is a cat
                    array_push($sorted, $li);
                    foreach ($item_ary as $child) { // find its children
                        if ($child->category_ref == $li->line_item_number) {
                            array_push($sorted, $child);
                        }
                    }
                } else {
                    $bIsOphan = true;
                    foreach ($item_ary as $parent) {
                        if ($li->category_ref == $parent->line_item_number) {
                            $bIsOphan = false;
                            break;
                        }
                    }
                    if ($bIsOphan) array_push($sorted, $li);
                }
            }

            $result = [ "status" => true, 'msg' => 'Fetch service agreement line item successfully', 'data' => $sorted];
        } else {
            $result = [ "status" => false, 'error' => 'Service Agreement Id is null'];
        }
        return $result;
    }

    /**
     * Get list of service agreement line item list
     * @param {obj} reqData
     */
    public function get_service_agreement_line_item_by_shift_id($shift_id, $category) {
        $sorted = [];
        if ($shift_id && $shift_id != '') {

            $this->db->from(TBL_PREFIX . 'shift_ndis_line_item as sai');
            $this->db->select(array('sai.id', 'sai.sa_line_item_id', 'sai.line_item_id as line_item_id', 'fli.line_item_name', 'fli.line_item_name as label', 'fli.id as fli_line_item_id', 'fli.line_item_number', 'fli.category_ref', 'fsc.name as support_cat',
            "CONCAT(fli.line_item_number,' ', fli.line_item_name ) AS line_item_value", 'sai.duration as duration',
            '( CASE 
                WHEN sai.auto_insert_flag = 0 THEN false
                ELSE true
                END
            ) as auto_insert_flag',
            'sai.price as amount',
            'sai.amount as sub_total',
            'sai.amount as sub_total_raw',
            'sai.is_old_price',
            'sai.line_item_price_id'
            ));
            
            $this->db->select("CASE WHEN fli.oncall_provided=1 THEN 'Yes' ELSE 'No' END AS oncall_provided", FALSE);
            $this->db->join("tbl_finance_line_item as fli", "fli.id = sai.line_item_id", "inner");
            $this->db->join("tbl_finance_support_category as fsc", "fsc.id = fli.support_category AND fsc.archive=0", "inner");
            $this->db->where(array('sai.archive' => 0, 'sai.shift_id' => $shift_id, 'sai.category' => $category));
            $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
            $item_ary = $query->result();

            $this->load->model('schedule/Ndispayments_model');
            
            foreach ($item_ary as $li) {
                //  get line item time of day
                $lineItemId = $li->fli_line_item_id;
                $fliTimeOfDay = $this->Ndispayments_model->get_time_of_day_by_line_item_id($lineItemId);
                
                $li->fliTimeOfDay = $fliTimeOfDay;
                // check if its sleepover
                $sleepoverItem = false;
                if (isset($fliTimeOfDay) && !empty($fliTimeOfDay)) {
                    // search keyword s_o
                    
                    if ($fliTimeOfDay[0]->sleepover) {
                        $sleepoverItem = true;
                    }
                }
                
                $li->duration_raw = $li->duration;
                $hr_check = explode(":", $li->duration);                
                
                if(!empty($hr_check)) {
                    $li->duration = formatHoursAndMinutes($li->duration);
                }
                $li->line_item_value = $li->line_item_value. ' ('.$li->duration .')'; 
                if (empty($li->category_ref)) { // is a cat
                    array_push($sorted, $li);
                    foreach ($item_ary as $child) { // find its children
                        if ($child->category_ref == $li->line_item_number) {
                            array_push($sorted, $child);
                        }
                    }
                } else {
                    $bIsOphan = true;
                    foreach ($item_ary as $parent) {
                        if ($li->category_ref == $parent->line_item_number) {
                            $bIsOphan = false;
                            break;
                        }
                    }
                    if ($bIsOphan) array_push($sorted, $li);
                }
            }
        }
        return $sorted;
    }
    //Get the list of goals
    public function get_goals_list($reqData) {
        $orderBy = 'tg.id';
        $direction = 'DESC';
        $shift_status=0;
        $is_goal_already_submitted=null;
        if(isset($reqData->shift_id) && !empty($reqData->shift_id)){
            $shift_status=$this->get_shift_status_by_id($reqData->shift_id);
            $is_goal_already_submitted= $this->check_if_goals_already_submitted($reqData->shift_id);
            $select_column = ["tg.id as goal_id", "tg.goal as goal_title", "tg.participant_master_id", "tg.start_date", "tg.end_date", "'' as actions", "sgt.snapshot", "sgt.shift_id"];
        }else{
            $select_column = ["tg.id as goal_id", "tg.goal as goal_title", "tg.participant_master_id", "tg.start_date", "tg.end_date", "'' as actions", "sgt.snapshot"];
        }
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_goals_master as tg');
        if(isset($reqData->shift_id) && !empty($reqData->shift_id)){
            if($shift_status>4||$is_goal_already_submitted){
                $this->db->join('tbl_shift_goal_tracking as sgt', "tg.id = sgt.goal_id and sgt.shift_id=".$reqData->shift_id, 'inner');
            }
            else{
                $this->db->join('tbl_shift_goal_tracking as sgt', "tg.id = sgt.goal_id and sgt.shift_id=".$reqData->shift_id, 'left');
            }
           

        }else{
            $this->db->join('tbl_shift_goal_tracking as sgt', "tg.id = sgt.goal_id", 'left');
        }
        if($shift_status<5&&!$is_goal_already_submitted){
            $this->db->where('tg.archive', 0);
        }
      
        
        if(isset($reqData->participant_id) && $reqData->participant_id > 0) {
            $this->db->where('tg.participant_master_id', $reqData->participant_id);
        }

        $this->db->order_by($orderBy, $direction);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        return ['data' => $query->result(), 'status' => true, 'msg' => 'Fetch Goals list successfully'];
    }
   

    public function check_if_goals_already_submitted($shift_id){
        $this->db->select('id');
        $this->db->from('tbl_shift_goal_tracking');
        $this->db->where('shift_id', $shift_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        if(!empty($result)){
            return count($result)>0?true:false;
        }
        return false;
    }

    public function get_shift_status_by_id($shift_id){
        $this->db->select('status as shift_status');
        $this->db->from('tbl_shift');
        $this->db->where('id', $shift_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result();
        if(!empty($result)){
            return $result[0]->shift_status;
        }
       } 
    /*
    * get multiple account addresses
    */
    public function get_address_for_accounts($ids, $account_type, $account_address_type = 2)
    {
        $result = array();
        $addresses = array();
        if (!empty($ids)) {
            if($account_type == "1") {
                $this->db->select(["p.id as account_id, pt.id as test_id, concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address", "pt.unit_number as unit", "'unit_number' as unit_number"]);
                $this->db->from("tbl_participants_master as p");
                $this->db->join('tbl_person_address as pt', 'pt.person_id = p.contact_id and pt.archive = 0', 'inner');
                $this->db->where_in("p.id", $ids);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];
            }
            else if($account_type == "2") {
                $this->db->select(["oa.organisationId as account_id,oa.id as test_id, concat(oa.street,', ',oa.city,' ',(select s.name from tbl_state as s where s.id = oa.state),' ',oa.postal) as address","oa.unit_number as unit","'unit_number' as unit_number"]);
                $this->db->from("tbl_organisation_address as oa");
                $this->db->where_in("oa.organisationId", $ids);
                $this->db->where("oa.primary_address", 1);
                $this->db->where("oa.address_type", $account_address_type);
                $this->db->where("oa.archive", 0);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];
            }
            if (!empty($result)) {
                foreach($result as $row) {
                    $addresses[$row['account_id']] = $row['address'];
                    $addresses[$row['unit_number']] = $row['unit'];
                }
            }
        }
        return $addresses;
    }

    /**
     * fetching a single shift details
     */
    public function get_shift_timesheet_attachment_details($id, $member_id='') {
        if (empty($id)) return;

        $this->db->select("sta.*");
        $this->db->from('tbl_shift_timesheet_attachment as sta');       

        $this->db->where(["sta.shift_id" => $id, "sta.archive" => "0"]);
        if(!empty($member_id)){
            $this->db->where("sta.member_id", $member_id);
        }       
        
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null;

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Shift not found!", 'status' => false);
            return $return;
        }
        $dataResult  = $query->result();
        $result = [];
        foreach ($dataResult as $val) {            
            $s3 = ($val->aws_uploaded_flag == 1) ? "true" : "false";
            $file_url = 'mediaShow/EA/' . $val->shift_id . '?filename=' . urlencode(base64_encode($val->file_path)) . '&s3=' . $s3 . '&download_as=' . $val->filename;
            $val->file_show_url = $file_url;
            $result[] = $val;
        }
       
        $return = array('data' => $dataResult, 'status' => true);
        return $return;
    }

    /** Check same day previous shift has a sleep over */
    public function check_shifts_of_member_on_prev_shift_same_day_sleepover($shift_id, $member_id, $shift_start_datetime, $shift_end_datetime, $actual_times = false, $completed_only_shifts = false, $issleepover = true) {

        $shift_type = null;
        if(!$actual_times) {
            $start_col = "scheduled_start_datetime";
            $end_col = "scheduled_end_datetime";
            $shift_type = 1;
        }
        else {
            $start_col = "actual_start_datetime";
            $end_col = "actual_end_datetime";
            $shift_type = 2;
        }
        
        $shift_start_date = date("Y-m-d", strtotime($shift_start_datetime));
        $shift_end_date = date("Y-m-d", strtotime($shift_end_datetime));
        $gap = SAME_DAY_SHIFTS_GAP;

        $this->db->select(["s.id", "ABS(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) as gap_start", "ABS(TIMESTAMPDIFF(MINUTE, '".$shift_start_datetime."', s.".$end_col.")/60) as gap_end"]);
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (3,4,5,6,7) and s.id != '.$shift_id);

        if($issleepover) {
            # comparing against previous shifts should have S/O breaks
            $this->db->where("s.id in (select shift_id from tbl_shift_break sb, tbl_references r where sb.break_category = {$shift_type} and sb.break_type = r.id and r.key_name = 'sleepover' and sb.archive = 0 and sb.shift_id = s.id)");
        }

        # the comparing against shifts have to be on same day
        $this->db->where("DATE(s.".$start_col.") = DATE(s.".$end_col.")");
        $this->db->where("DATE(s.".$start_col.")", $shift_start_date);
        $this->db->where("(
        ABS(TIMESTAMPDIFF(MINUTE, '".$shift_end_datetime."', s.".$start_col.")/60) > {$gap}
        OR
        ABS(TIMESTAMPDIFF(MINUTE, '".$shift_start_datetime."', s.".$end_col.")/60) > {$gap}
        )
        ");

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        if(!empty($result)) {
            if($actual_times == true) {
                $max_gap = 0;
                if($result[0]['gap_start'] > 0 && $result[0]['gap_start'] > $result[0]['gap_end'])
                    $max_gap = $result[0]['gap_start'];
                if($result[0]['gap_end'] > 0 && $result[0]['gap_end'] > $result[0]['gap_start'])
                    $max_gap = $result[0]['gap_end'];
                if(!$issleepover) {
                    $max_gap = $result[0]['gap_end'];
                }
                return ["status" => true, "data" => $max_gap];
            }            
        }
        return ["status" => false, "data" => NULL];
        
    }

    public function setLineItemsFromDefaults($newdata,$adminId, $uuid_user_type){

        # get ndis payment - only for participant
                   $ndisErrTemp=[];
                  $ndisErrTemp['error_1'] = false;
                    $ndisErrTemp['error_2'] = false;
                    $ndisErrTemp['error_3'] = false;
                    $ndis_in = 0;

                    $newdata['scheduled_start_date']=explode(" ", $newdata['scheduled_start_date'])[0];
                    $newdata['scheduled_end_date']=explode(" ", $newdata['scheduled_end_date'])[0];
                    $newdata['contact_id']= $newdata['contactvalue'];
               
          # get service agreement
          $serviceAgreementData = [];
          $account = [];
          $account['value'] = $newdata['account_id'];
          $account['account_type'] = $newdata['account_type'];
          $serviceAgreementData['account'] = (object) $account;
          $serviceAgreementData['start_date'] = $newdata['scheduled_start_date'] ?? '';
          $serviceAgreementData['end_date'] = $newdata['scheduled_end_date'] ?? '';
          $serviceAgreementData['section'] = 'actual';
          $getServiceAgreement = $this->get_service_agreement((object) $serviceAgreementData);

          if (isset($getServiceAgreement['status']) && $getServiceAgreement['status'] == false && isset($getServiceAgreement['rule']) && $getServiceAgreement['rule'] != '') {
              if ($getServiceAgreement['rule'] == 1) {
                  $ndisErrTemp['error_1'] = true;
              } else {
                  $newdata['actual_sb_status']=$getServiceAgreement['rule'];
                  $ndisErrTemp['error_2'] = true;
              }
          }
          $actual_sa_id = '';
          if (isset($getServiceAgreement) && array_key_exists('data', $getServiceAgreement) && !empty($getServiceAgreement['data'])) {
              $actualSAData = $getServiceAgreement['data'];
              $actual_sa_id = $actualSAData['service_agreement_id'] ?? '';
              $newdata['actual_sa_id'] = $actual_sa_id;
              $newdata['actual_docusign_id'] = $actualSAData['docusign_id'] ?? '';
          }

          # get  line items
          $support_type = '';
        
          if (isset($newdata['scheduled_support_type']) && !empty($newdata['scheduled_support_type'])) {
              $support_type = $newdata['scheduled_support_type'];
          }
          $lineItemData = [];
          $ph_data = $newdata['account_address'] ?? '';
          $phDataAddress = [];
          if($ph_data != '' && isset($ph_data['value'])) {
              $phDataAddress = (object) $ph_data;
          } else {
              $phDataAddress['account_address'] = [];
              if (isset($newdata['full_account_address']) && !empty($newdata['full_account_address'])) {
                  $phDataAddress =(object) $data['full_account_address'];
              }          
          }   
        $lineItemData['account_address'] = (object) $phDataAddress;
        $lineItemData['start_date'] = $newdata['scheduled_start_date'] ?? '';
        $lineItemData['start_time'] = $newdata['scheduled_start_time'] ?? '';
        $lineItemData['end_date'] = $newdata['scheduled_end_date'] ?? '';
        $lineItemData['end_time'] = $newdata['scheduled_end_time'] ?? '';
        $lineItemData['service_agreement_id'] = $actual_sa_id;
        $lineItemData['supportType'] = $support_type;
        $lineItemData['section'] = 'actual';
        $lineItemData['scheduled_rows'] = $newdata['scheduled_rows'] ?? [];
        $lineItemData['scheduled_duration'] = $newdata['scheduled_duration'];
          
        # Get support type keyname
        $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);
        $support_key_name = '';
        if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
            $support_key_name = $supportType->key_name;
        }

        # Set support type duration if it is mixed
        if (isset($newdata['scheduled_support_type_duration']) && !empty($newdata['scheduled_support_type_duration']) && $support_key_name == 'mixed') {
            $item_data = $lineItemData;
            $item_data['rows'] = $lineItemData['scheduled_rows'];
            $item_data['support_type_duration'] = $newdata['scheduled_support_type_duration'];

            # form a day array for mixed type
            $getNDISDateFormat = $this->Ndispayments_model->get_support_type_ndis_duration($item_data);
            $newdata['actual_support_type_duration'] = $getNDISDateFormat;
            $sch_support_type_duration = $getNDISDateFormat;

            # Prepare array for pulling line item based on day specific 
            $support_type_duration =[];
            foreach($sch_support_type_duration as $support_duration) {
                $support_duration = (object) $support_duration;
                $support_type_duration = array_merge($support_type_duration, $support_duration->duration); 
            }
            
            $lineItemData['support_type_duration'] = $support_type_duration;
        }
        
          $this->load->model('schedule/Ndispayments_model');
          $schLineItem = $this->Ndispayments_model->get_line_items_for_payment($lineItemData);

          $newdata['actual_ndis_line_item_list'] = [];
          $newdata['actual_missing_line_item'] = false;
          if (isset($schLineItem) && array_key_exists('data', $schLineItem) && !empty($schLineItem['data'])) {
              $newdata['actual_ndis_line_item_list'] = $schLineItem['data'] ?? [];
              $newdata['actual_missing_line_item'] = $schLineItem['missing_line_item'];
              $ndisErrTemp['error_3'] = $schLineItem['missing_line_item'];
          }

          # if any one error should true than add to array
          if ($ndisErrTemp['error_1'] == true || $ndisErrTemp['error_2'] == true || $ndisErrTemp['error_3'] == true) {
              $ndisPaymentError[$ndis_in] = $ndisErrTemp;
              $ndis_in++;
          }      

          $newdata['account_address'] = $newdata['account_address']['value'];
          $newdata['skip_account_shift_overlap']=true;
          $response = $this->create_update_shift($newdata, $adminId, $uuid_user_type);  
          
      }

    public function get_current_shift_list_data($adminId,$current_shift_list,$page,$limit,$array_to_merge=[],$offset,$initial_limit,$filter,$filter_condition='',$initial_shift_list,$status_filter_value='active',$uuid_user_type =''){
        $shift_status_filter_value=5; //for all
         if($status_filter_value=='active')
         {
          $shift_status_filter_value=3; 
         }
        $where= $direction ='';
        $ids=[];
        $direction = 'DESC';
        if($current_shift_list==1)
        {  
            $ids=[4];
            $where= 'ABS(s.scheduled_start_datetime) =';
            $direction = 'DESC';
        }
        else if($current_shift_list==2){
            $ids=[1,2,3,4];
            $direction = 'ASC';
            $where= 'ABS(s.scheduled_start_datetime) >';
        }
        else if($current_shift_list==3)
        {   $ids=[1,2,3,4];
            $direction = 'DESC';
            $where= 'ABS(s.scheduled_start_datetime) < ';
      
        }
        else if($current_shift_list==4)
        {   $ids=[5];
            $direction = 'DESC';
        }
        else if($current_shift_list==5)
        {    $ids=[6];
            $direction = 'DESC';
        }
        if($current_shift_list==6){
            foreach($array_to_merge as $key=> $val) {
                if($val->role_name == "NDIS" && $val->account_type == 1) { 
                    $array_to_merge[$key]->warnings = $this->pull_shift_warnings($val);               
                } else {
                    $array_to_merge[$key]->warnings = $this->warning;
                }
            }

            return ["status" => true, "data" => $array_to_merge,"total_item"=>count($array_to_merge), 'msg'=>'Fetched shifts list successfully',"data_ends"=>true];
        }
        $now = new DateTime();
        $current_date_time=$now->format('Y-m-d H:i:s');
        $select_column = ["s.id", "s.shift_no", "s.scheduled_start_datetime", "s.scheduled_end_datetime", "s.actual_start_datetime", "s.actual_end_datetime", "concat(m.firstname,' ',m.lastname) as owner_fullname", "concat(p.firstname,' ',p.lastname) as contact_fullname", "r.name as role_name", "", "'' as actions", "s.person_id", "s.owner_id", "s.account_type", "s.account_id", "s.role_id", "s.status", "am.fullname as member_fullname", "am.id as member_id", "s.scheduled_duration","tr.display_name as account_organisation_type", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week", "s.roster_id", "s.not_be_invoiced", "s.scheduled_sa_id", "s.scheduled_sb_status", "scheduled_docusign_id", "s.actual_sa_id", "s.actual_sb_status", "actual_docusign_id"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as account_fullname");
         # Searching column
        $src_columns = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)", "r.name", "am.fullname", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)", "s.shift_no", "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_end_datetime,'%d/%m/%Y')", "s.scheduled_duration", "tr.display_name", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, true);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }
        $status_label = "(CASE ";
        foreach($this->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);
        if($adminId)
            $this->db->select('al.id as is_shift_locked');
        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_member m', 'm.id = s.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = s.person_id', 'left');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id', 'left');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id', 'left');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->join('tbl_organisation as o', 'o.id = s.account_id AND s.account_type = 2', 'left');
        $this->db->join('tbl_references as tr', 'o.org_type  = tr.id AND tr.archive = 0', 'left');
        $this->db->join('tbl_roster as ros', 'ros.id = s.roster_id', 'LEFT');
        if($adminId)
            $this->db->join('tbl_access_lock as al', 's.id = al.object_id and al.archive = 0 and al.created_by != '.$adminId.' and al.object_type_id = 1', 'left');
        $this->db->where("s.archive", "0");
        
        if (!empty($uuid_user_type) && $uuid_user_type == ORGANISATION_PORTAL) {
            $this->db->where("s.created_by", $adminId);
        }
        if($current_shift_list<4&&$current_shift_list!=1){
            $this->db->where($where."ABS(CURRENT_TIMESTAMP())");
        }
        if($current_shift_list==1)
        {
            $this->db->where($where."ABS(CURRENT_TIMESTAMP())");
        }
        $this->db->where_in("s.status" ,$ids);
        $this->db->order_by('s.scheduled_start_datetime', $direction);
        $this->db->limit($limit, ($offset));
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();
        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        // Get the query result
        $result = $query->result();
        if(!empty($result)) {
            foreach($result as $key=> $val) {
                if($val->role_name == "NDIS" && $val->account_type == 1) { 
                    $result[$key]->warnings = $this->pull_shift_warnings($val);               
                } else {
                    $result[$key]->warnings = false;
                }
            }
            
            $merged_result=[];
            $data_offset=0;
            $merged_result =  array_merge($array_to_merge, $result);
            if((count($merged_result))>$initial_limit-1){
                $data_offset= count($result)+$offset;
                foreach($merged_result as $key=> $val) {
                    if($val->role_name == "NDIS" && $val->account_type == 1) { 
                        $merged_result[$key]->warnings = $this->pull_shift_warnings($val);               
                    } else {
                        $merged_result[$key]->warnings =$this->warning;
                    }
                }
                return ["status" => true, "data" => $merged_result,"current_shift_list"=>$current_shift_list,"current_page"=>$page+1,'offset'=>$data_offset,'total_item'=>$total_item];
             }
             else{
                 if($current_shift_list!=$shift_status_filter_value)
                 {
                   $data_limit=abs($initial_limit-count($merged_result));
                   $response1 = $this->get_current_shift_list_data($adminId,$current_shift_list+1,0,$data_limit,$merged_result,$data_offset,$initial_limit,$filter,$filter_condition,$initial_shift_list,$status_filter_value,$uuid_user_type);
                   return  $response1;
                 }else{
                    foreach($merged_result as $key=> $val) {
                        if($val->role_name == "NDIS" && $val->account_type == 1) { 
                            $merged_result[$key]->warnings = $this->pull_shift_warnings($val);               
                        } else {
                            $merged_result[$key]->warnings =$this->warning;
                        }
                    }
                    return ["status" => true, "data" => $merged_result,"data_ends"=>true,'total_item'=>$total_item];
                 }
             }          
        }
        else{
            if(empty($result) && $current_shift_list!=$shift_status_filter_value)
            {
                if($current_shift_list==$initial_shift_list)
                {
                    $offset=0;
                }
                 $response2=$this->get_current_shift_list_data($adminId,$current_shift_list+1,0,$limit,$array_to_merge,$offset,$initial_limit,$filter,$filter_condition,$initial_shift_list,$status_filter_value, $uuid_user_type);
                 return  $response2;
            }
           }
            $listStatus=true;
            $msg='';
            if(count($array_to_merge)<1 && count($result)>0)
            {
                $total_item =count($result);
                $array_to_merge=$result;
                $msg='Fetched shifts list successfully';
            }
             if(count($array_to_merge)>0)
            {
                $total_item =count($array_to_merge);
                $msg='Fetched shifts list successfully';
            }
            foreach($array_to_merge as $key=> $val) {
                if($val->role_name == "NDIS" && $val->account_type == 1) { 
                    $array_to_merge[$key]->warnings = $this->pull_shift_warnings($val);               
                } else {
                    $array_to_merge[$key]->warnings =$this->warning;
                }
            }
            return ["status" => $listStatus, "data" => $array_to_merge,"total_item"=>$total_item, 'msg'=>$msg,"data_ends"=>true];
    }

    public function get_shift_total_rows($reqData,$filter_condition,$adminId,$status_filter=[1,2,3,4],$filter, $uuid_user_type){
        $src_columns = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)", "r.name", "am.fullname", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)", "s.shift_no", "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_end_datetime,'%d/%m/%Y')", "s.scheduled_duration", "tr.display_name", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week");
        if(!empty($filter->search)) {
            $search_key  = $this->db->escape_str($filter->search, true);
            if (!empty($search_key)) {
                $this->db->group_start();
                for ($i = 0; $i < count($src_columns); $i++) {
                    $column_search = $src_columns[$i];
                    if (strstr($column_search, "as") !== false) {
                        $serch_column = explode(" as ", $column_search);
                        if ($serch_column[0] != 'null')
                            $this->db->or_like($serch_column[0], $search_key);
                    }
                    else if ($column_search != 'null') {
                        $this->db->or_like($column_search, $search_key);
                    }
                }
                $this->db->group_end();
            }
        }

         # new lightening filters
         if(isset($filter->filters)) {

            foreach($filter->filters as $filter_obj) {
                if(empty($filter_obj->select_filter_value)) continue;

                $sql_cond_part = GetSQLCondPartFromSymbol($filter_obj->select_filter_operator_sym, $filter_obj->select_filter_value);
                if($filter_obj->select_filter_field == "account_fullname") {
                    $this->db->where("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "owner_fullname") {
                    $this->db->where("concat(m.firstname,' ',m.lastname) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "role_name") {
                    $this->db->where('r.name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "shift_no") {
                    $this->db->where('s.shift_no '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "status_label") {
                    $this->db->where('s.status '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "scheduled_duration") {
                    $this->db->where('s.scheduled_duration '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "account_organisation_type") {
                    $this->db->where('tr.display_name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "scheduled_start_datetime" || $filter_obj->select_filter_field == "scheduled_end_datetime") {
                    $this->db->where('DATE_FORMAT(s.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
    
        $select_column = ["s.id", "s.shift_no", "s.scheduled_start_datetime", "s.scheduled_end_datetime", "s.actual_start_datetime", "s.actual_end_datetime", "concat(m.firstname,' ',m.lastname) as owner_fullname", "concat(p.firstname,' ',p.lastname) as contact_fullname", "r.name as role_name", "", "'' as actions", "s.person_id", "s.owner_id", "s.account_type", "s.account_id", "s.role_id", "s.status", "am.fullname as member_fullname", "am.id as member_id", "s.scheduled_duration","tr.display_name as account_organisation_type", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week", "s.roster_id", "s.not_be_invoiced", "s.scheduled_sa_id", "s.scheduled_sb_status", "scheduled_docusign_id", "s.actual_sa_id", "s.actual_sb_status", "actual_docusign_id"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END) as account_fullname");

        $status_label = "(CASE ";
        foreach($this->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };

        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);
        if($adminId)
            $this->db->select('al.id as is_shift_locked');

        $this->db->from('tbl_shift as s');
        $this->db->join('tbl_member m', 'm.id = s.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = s.person_id', 'left');
        $this->db->join('tbl_shift_member as asm', 'asm.id = s.accepted_shift_member_id', 'left');
        $this->db->join('tbl_member as am', 'am.id = asm.member_id', 'left');
        $this->db->join('tbl_member_role as r', 'r.id = s.role_id', 'inner');
        $this->db->join('tbl_organisation as o', 'o.id = s.account_id AND s.account_type = 2', 'left');
        $this->db->join('tbl_references as tr', 'o.org_type  = tr.id AND tr.archive = 0', 'left');
        $this->db->join('tbl_roster as ros', 'ros.id = s.roster_id', 'LEFT');
        if($adminId)
            $this->db->join('tbl_access_lock as al', 's.id = al.object_id and al.archive = 0 and al.created_by != '.$adminId.' and al.object_type_id = 1', 'left');
        $this->db->where("s.archive", "0");
        
        if (!empty($uuid_user_type) && $uuid_user_type === ORGANISATION_PORTAL) {
            $this->db->where("s.created_by", $adminId);
        }
        $this->db->where_in("s.status" ,$status_filter);
        $this->db->limit(20);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();
        // Get total rows count
        $total_item = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        return $total_item;
    }

    /**
     * Calculate the timesheet unit if member allocation shift hours exceeding 38 hrs
     * - For shift
     */
    public function calculateWeeklyHoursExceedHour($total_weekly_hours, $used_weekly_hours, $shift_day1, $shift_day2, $mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20) {
        
        if (!$sunday && !$saturday && !$mon_fri && $public_holiday) {
            $overtime_15 = $overtime_20 = 0;
        }
        
        # If shift fall two days except weekday
        if ($shift_day1 != null && $shift_day2 != null) {
            #get value by presitance
            $sdPresitance = $this->presitence_day_rule[$shift_day1] ? $this->presitence_day_rule[$shift_day1] : NULL;
            $edPresitance = $this->presitence_day_rule[$shift_day2] ? $this->presitence_day_rule[$shift_day2] : NULL;

            # If lower to higher day presitence
            # Else higher to lower day presitence
            if ($sdPresitance < $edPresitance) {
                # if shift falls weekday to saturday 
                if ($mon_fri && $saturday) {
                    $mon_used_hour = $used_weekly_hours + $mon_fri;
                    $remainMonFri = $mon_used_hour > WEEKLY_MAX_MEMBER_HOURS ? $mon_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                    if ($remainMonFri > 0 && $remainMonFri <= 2) {
                        $mon_fri = $mon_fri - $remainMonFri;
                        $overtime_15 = $remainMonFri;
                    } else if ($remainMonFri > 0 && $remainMonFri > 2) {
                        $mon_fri = $mon_fri - $remainMonFri;                        
                    } else {
                        $overtime_15 = 0;
                    }
                                        
                    $sat_used_hour = $mon_used_hour + $saturday;
                    $remainSat = $sat_used_hour > (WEEKLY_MAX_MEMBER_HOURS) ? $sat_used_hour - (WEEKLY_MAX_MEMBER_HOURS) : 0;
                    if ($remainSat > 0) {
                        $overtime_15 = $remainSat >= 2 ? 2 : 2 - $remainSat;
                        $saturday = $saturday - $remainSat;
                        $overtime_20 = $overtime_20 > 0 ? $overtime_20 :  $remainSat - $overtime_15;  
                    }
                }
                
                # if shift falls saturday to sunday 
                if ($saturday && $sunday) { 
                    $saturday_used_hour = $used_weekly_hours + $saturday;
                    $remainStaurday = $saturday_used_hour > (WEEKLY_MAX_MEMBER_HOURS) ? $saturday_used_hour - (WEEKLY_MAX_MEMBER_HOURS) : 0;
                    if ($remainStaurday > 0) {
                        $overtime_15 = $remainStaurday >= 2 ? 2 : 2 - $remainStaurday;

                        $saturday = $saturday - $remainStaurday;
                        $overtime_20 = $overtime_20 - $sunday;
                        
                    } else {
                        $sunday_used_hour = $saturday_used_hour + $sunday;
                        $remain20 = $sunday_used_hour > (WEEKLY_MAX_MEMBER_HOURS + 2) ? $sunday_used_hour - (WEEKLY_MAX_MEMBER_HOURS + 2) : 0;
                        $overtime_15 = 0;
                        if ($remain20 > 0) {
                            // $sunday = $sunday - $overtime_20;
                            $overtime_20 = 0;
                        }
                    }
                }

                # if shift falls weekday to public holiday 
                if ($mon_fri && $public_holiday) {
                    $mon_used_hour = $used_weekly_hours + $mon_fri;
                    $remainMonFri = $mon_used_hour > WEEKLY_MAX_MEMBER_HOURS ? $mon_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                    if ($remainMonFri > 0) {
                        $mon_fri = $mon_fri - $remainMonFri;
                        # caluculate overtime only for weekday active duration
                        $overtime_15 = $remainMonFri < 2 ? $remainMonFri : $overtime_15;
                        $overtime_20 = $remainMonFri > 2 ? $remainMonFri - $overtime_15: 0;
                        $overtime_20 = $overtime_20 < 0 ? 0 : $overtime_20;
                    } else {
                        $ph_used_hour = $mon_used_hour + $public_holiday;
                        $remain15 = $ph_used_hour > (WEEKLY_MAX_MEMBER_HOURS) ? $ph_used_hour - (WEEKLY_MAX_MEMBER_HOURS) : 0;
                        $overtime_15 = 0;
                        if ($remain15 > 0) {
                            // $public_holiday = $public_holiday - $overtime_20;
                            $overtime_20 = 0;
                        }
                    }
                }

                # if shift falls saturday to public holiday 
                if ($saturday && $public_holiday) {
                    $saturday_used_hour = $used_weekly_hours + $saturday;
                    $remainStaurday = $saturday_used_hour > (WEEKLY_MAX_MEMBER_HOURS) ? $saturday_used_hour - (WEEKLY_MAX_MEMBER_HOURS) : 0;
                    if ($remainStaurday > 0) {
                        $overtime_15 = $remainStaurday >= 2 ? 2 : 2 - $remainStaurday;

                        $saturday = $saturday - $remainStaurday;

                        $overtime_20 = $remainStaurday - $overtime_15;
                        $overtime_20 = $overtime_20 < 0 ? 0 : $overtime_20;
                        
                    } else {
                        $ph_used_hour = $saturday_used_hour + $public_holiday;
                        $remain20 = $ph_used_hour > (WEEKLY_MAX_MEMBER_HOURS + 2) ? $ph_used_hour - (WEEKLY_MAX_MEMBER_HOURS + 2) : 0;
                        $overtime_15 = 0;
                        if ($remain20 > 0) {
                            // $public_holiday = $public_holiday - $overtime_20;
                            $overtime_20 = 0;
                        }
                    }
                }

                # if shift falls sunday to public_holiday 
                if ($sunday && $public_holiday) {
                    $overtime_15 = 0;
                    $overtime_20 = 0;
                }
            } else {
                # if shift falls public_holiday to sunday
                if ($public_holiday && $sunday) {
                    $overtime_15 = 0;
                    $overtime_20 = 0;
                }

                # if shift falls sunday to weekday 
                if ($sunday && $mon_fri) {
                    $sunday_used_hour = $used_weekly_hours + $sunday;
                    $remainSunday = $sunday_used_hour > WEEKLY_MAX_MEMBER_HOURS  ? $sunday_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                    
                    if ($remainSunday > 0) {
                        if ($sunday_used_hour >= (WEEKLY_MAX_MEMBER_HOURS + 2)) {
                            $mon_used_hour = $sunday_used_hour + $mon_fri;
                            $remainMonFri = $mon_used_hour > WEEKLY_MAX_MEMBER_HOURS ? $mon_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                            $mon_fri = $overtime_15 = 0;
                            $overtime_20 = $remainMonFri - $remainSunday;
                        } else {
                            $overtime_15 = $overtime_15 - $remainSunday;
                            $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
                        }
                    } else {
                        $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
                    }
                }

                # if shift falls public_holiday to weekday 
                if ($public_holiday && $mon_fri) {
                    $ph_used_hour = $used_weekly_hours + $public_holiday;
                    $remainPH = $ph_used_hour > WEEKLY_MAX_MEMBER_HOURS  ? $ph_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                    if ($remainPH > 0) {
                        if ($ph_used_hour >= (WEEKLY_MAX_MEMBER_HOURS + 2)) {
                            $mon_used_hour = $ph_used_hour + $mon_fri;
                            $remainMonFri = $mon_used_hour > WEEKLY_MAX_MEMBER_HOURS ? $mon_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                            $mon_fri = $overtime_15 = 0;
                            $overtime_20 = $remainMonFri - $remainPH;                                
                        } else {
                            $overtime_15 = $overtime_15 - $remainPH;
                            $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
                        }
                    } else {
                        $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
                    }
                }

                # if shift falls public_holiday to saturday 
                if ($public_holiday && $saturday) {
                    $ph_used_hour = $used_weekly_hours + $public_holiday;

                    $remainPH = $ph_used_hour > WEEKLY_MAX_MEMBER_HOURS  ? $ph_used_hour - WEEKLY_MAX_MEMBER_HOURS : 0;
                    if ($remainPH > 0) {
                        if ($ph_used_hour > (WEEKLY_MAX_MEMBER_HOURS)) {
                            $overtime_15 = 2 - $remainPH;
                            $sat_used_hour = $ph_used_hour + $saturday;
                            $remainSat = $sat_used_hour > (WEEKLY_MAX_MEMBER_HOURS) ? $sat_used_hour - $ph_used_hour : 0;
                            $saturday  = 0;
                            $overtime_20 = $remainSat - $overtime_15;                                
                        } else {
                            $overtime_15 = 0;
                            $saturday = $saturday - $overtime_20;
                        }
                    } else {
                            $actual_saturday = $saturday;
                            $saturday = WEEKLY_MAX_MEMBER_HOURS - $ph_used_hour;
                            $actual_saturday = $actual_saturday - $saturday;
                            $overtime_15 = ($actual_saturday > 2) ? 2 : $actual_saturday;
                    }
                }

                # If mon_fri and same weekday then minus overtime 2.0 and overtime 1.5 from mon_fri
                if ($mon_fri > 0 && $shift_day1 == $shift_day2) {
                    $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
                }
            }
        } else {
            # If mon_fri then minus overtime 2.0 and overtime 1.5 from mon_fri
            if ($mon_fri > 0 || $saturday > 0) {
                $day = $mon_fri > 0 ? 'mon_fri' : 'saturday'; 
                ${$day} = ${$day} - $overtime_15 - $overtime_20;
            }            

            # If sunday or public_holiday overwrite with 0 
            if ($sunday || $public_holiday) {
                $overtime_15 = 0;
                $overtime_20 = 0;
            }
        }

        return [ $mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20 ];
    }

    /**
     * Get day specific minitues for interrupted sleepover OT calculation
     * 
     * @param $shift_details {array} shift details
     * @return $inter_day_mins {array} day 1 mins, day 2 mins
     */
    public function get_day_spec_interrupt_so_mins($shift_details) {
      
        $actual_start_date = date('Y-m-d', strtotime($shift_details['actual_start_datetime']));        
        $actual_end_date = date('Y-m-d', strtotime($shift_details['actual_end_datetime']));
        $inter_day1_mins = $inter_day2_mins = 0;

        if(count($shift_details['actual_in_sleepover_rows']) > 0) {
            
            foreach($shift_details['actual_in_sleepover_rows'] as $key => $inso) {
                $actual_in_so_start_date = date("Y-m-d", strtotime($shift_details['actual_in_sleepover_rows'][$key]['break_start_datetime']));
                $actual_in_so_end_date = date("Y-m-d", strtotime($shift_details['actual_in_sleepover_rows'][$key]['break_end_datetime']));

                $inter_day_mins = [0, 0];        
                $get_day_count = dayDifferenceBetweenDate($actual_start_date, $actual_end_date);
                //If shift falls single day then there is no need for split up
                if($get_day_count == 0) {
                    return $inter_day_mins;
                }

                if($actual_start_date == $actual_in_so_start_date && $actual_start_date == $actual_in_so_end_date) {
                    $inter_day1_mins += $shift_details['actual_in_sleepover_rows'][$key]['duration_int'];
                }else if($actual_end_date == $actual_in_so_start_date && $actual_end_date == $actual_in_so_end_date) {                    
                    $inter_day2_mins += $shift_details['actual_in_sleepover_rows'][$key]['duration_int'];
                } else {           
                    $midnight_start = date("Y-m-d 00:00:00", strtotime($shift_details['actual_in_sleepover_rows'][$key]['break_end_datetime']));
                    $inter_day1_mins += minutesDifferenceBetweenDate($shift_details['actual_in_sleepover_rows'][$key]['break_start_datetime'], $midnight_start);
                    $inter_day2_mins += minutesDifferenceBetweenDate($midnight_start, $shift_details['actual_in_sleepover_rows'][$key]['break_end_datetime']); 
                    
                }
            }
            $inter_day_mins = [$inter_day1_mins, $inter_day2_mins];
        }

       return $inter_day_mins;
    }

    /** Get interrupted sleep over split up */
    public function get_interrupted_so_split_up($shift_details, $first_aid, $inter_day1_mins, $inter_day2_mins, $mon_fri, $saturday, $sunday, $public_holiday, $shift_day1, $shift_day2, $overtime_15, $overtime_20) {

        $interrupt_hr = round((($inter_day1_mins + $inter_day2_mins) / 60),2);
    
        $first_aid = $first_aid + $interrupt_hr;
        $add_ot_mon_fri = FALSE;
        $inter_day1_mins = round((($inter_day1_mins) / 60),2);
        $inter_day2_mins = round((($inter_day2_mins) / 60),2);
        
        /**Saturday and Sunday */
        if ($saturday && $sunday) {
            #If int S/O falls fully in sunday
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                # If total interrupted hr > 2 full interrupted hrs will be added into sunday
                $sunday = $sunday + $interrupt_hr;                               
            }
            #If int S/O falls fully in saturday 
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                $add_ot_mon_fri = TRUE;               
            }#If int S/O falls both saturday and sunday
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                # If inter_day1_mins > 2 Add first 2 hrs into OT 1.5 hrs and remaining will be OT 2.0
                if($inter_day1_mins > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = $inter_day1_mins - 2;
                } else {
                    # If inter_day1_mins < 2 Add inter_day1_mins into OT 1.5 hrs and OT 2.0 will be 0
                    $overtime_15 = $inter_day1_mins;
                }
                
                if($inter_day2_mins) {
                    $sunday = $sunday + $inter_day2_mins;
                }
            }
        }
        /**Sunday and Monday */
        else if($sunday && $mon_fri) {
            #If int S/O falls fully in Monday
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                $add_ot_mon_fri = TRUE;                                          
            }
            #If int S/O falls fully in Sunday 
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                # If total interrupted hr > 2 full interrupted hrs will be added into sunday
                $sunday = $sunday + $interrupt_hr;
            }#If int S/O falls both saturday and sunday
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                if($inter_day1_mins) {
                    $sunday = $sunday + $inter_day1_mins;
                }

                # If inter_day2_mins > 2 Add first 2 hrs into OT 1.5 hrs and remaining will be OT 2.0
                if($inter_day2_mins) {
                    $overtime_15 = ($inter_day1_mins > 2) ? 0 : 2 - $inter_day1_mins;
                    $overtime_20 = $inter_day2_mins - $overtime_15;
                }
            }
        }
        /**public_holiday and Sunday */
        else if($shift_day1 == 'public_holiday' && $sunday) {;
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                $sunday = $sunday + $interrupt_hr;
            }
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                $public_holiday = $public_holiday + $interrupt_hr;
            }
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                $public_holiday = $public_holiday + $inter_day1_mins;
                $sunday = $sunday + $inter_day2_mins;
                
            }
        }
        else if($shift_day2 == 'public_holiday' && $sunday) {
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                $public_holiday = $public_holiday + $interrupt_hr;
            }
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                $sunday = $sunday + $interrupt_hr;
            }
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                $sunday = $sunday + $inter_day1_mins;
                $public_holiday = $public_holiday + $inter_day2_mins;
                
            }
        }
        /**public_holiday and mon_fri */
        else if($shift_day1 == 'public_holiday' && ( $mon_fri || $saturday )) {
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                $add_ot_mon_fri = TRUE;
            }
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                $public_holiday = $public_holiday + $interrupt_hr;
            }
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                if($inter_day1_mins) {
                    $public_holiday = $public_holiday + $inter_day1_mins;
                }

                # If inter_day2_mins > 2 Add first 2 hrs into OT 1.5 hrs and remaining will be OT 2.0
                if($inter_day2_mins) {
                    $overtime_15 = ($inter_day1_mins >= 2) ? 0 : 2 - $inter_day1_mins;
                    $overtime_20 = $inter_day2_mins - $overtime_15;
                }
                
            }
        }
         /**Friday or saturday and public_holiday */
        else if(($mon_fri || $saturday) && $shift_day2 == 'public_holiday') {
            if($inter_day1_mins == 0 && $inter_day2_mins > 0) {
                $public_holiday = $public_holiday + $interrupt_hr;
            }
            else if($inter_day2_mins == 0 && $inter_day1_mins > 0) {
                $add_ot_mon_fri = TRUE;
            }
            else if($inter_day1_mins > 0 && $inter_day2_mins > 0) {
                # If inter_day2_mins > 2 Add first 2 hrs into OT 1.5 hrs and remaining will be OT 2.0
                if($inter_day1_mins > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = $inter_day1_mins - $overtime_15;
                } else {
                    $overtime_15 = $inter_day1_mins;
                }
                if($inter_day2_mins) {
                    $public_holiday = $public_holiday + $inter_day2_mins;
                }                
            }
        }
        else if($shift_day1 == 'public_holiday' && $shift_day2 == 'public_holiday' || $public_holiday) {
            $public_holiday = $public_holiday + $interrupt_hr;
        }
        else if(!$shift_day1 && !$shift_day2 && $saturday) {
            $add_ot_mon_fri = TRUE;            
        }
        else if(!$shift_day1 && !$shift_day2 && $sunday) {
            $sunday = $sunday + $interrupt_hr;
        }
        
        /** Calculation for mon_fri single day or if OT needs falls fully mon_fri on two days split */
        if(($mon_fri || $saturday) && !$sunday && !$public_holiday || $add_ot_mon_fri) {
            
            if($interrupt_hr > 2) {
                #Add first 2 hrs into overtime_15 remaining will be OT 2.0
                $overtime_15 = 2;
                $overtime_20 = $interrupt_hr - $overtime_15;
            } else {
                $overtime_15 = $interrupt_hr;
                $overtime_20 = 0;
            }
        }
        
        return [$first_aid,$mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20];
    }
    /** Validation for Interrupted sleepover breaks
     * @param $rows {array} shift breaks data
     * @param $so_ref_id {int} Sleepover reference Id
     * @param $inso_ref_id {int} Intrupped Sleepover reference Id
     * 
     * @return $errors {array} error message
     */
    public function interrupted_sleepover_validation($rows, $so_ref_id, $inso_ref_id) {
        $so_key = array_search($so_ref_id, array_column($rows, 'break_type'));
        $inso_key = array_search($inso_ref_id, array_column($rows, 'break_type'));
        $errors = [];
        # check interrupted S/O time is with in S/O time
        if ($inso_key > -1 && ($so_key < 0 || $so_key === false)) {
            $errors[] = "Interrupted S/O can't be added without S/O timing";
        }

        # check interrupted S/O time is with in S/O time
        if ($inso_key > -1 && $so_key > -1) {
            $interrupted_so_list = []; 
            $key = 0;
            foreach($rows as $row) {
                if($row['break_type'] == $inso_ref_id) {
                    
                    $so_break_start_datetime = $rows[$so_key]['break_start_datetime'];
                    $so_break_end_datetime = $rows[$so_key]['break_end_datetime'];

                    $in_so_break_start_datetime = $row['break_start_datetime'];
                    $in_so_break_end_datetime = $row['break_end_datetime'];

                    $start_in_range = true;
                    if (strtotime($in_so_break_start_datetime) >= strtotime($so_break_start_datetime) && strtotime($in_so_break_start_datetime) <= strtotime($so_break_end_datetime)) {
                        $start_in_range = false;
                    }

                    $end_in_range = true;
                    if (strtotime($in_so_break_end_datetime) >= strtotime($so_break_start_datetime) && strtotime($in_so_break_end_datetime) <= strtotime($so_break_end_datetime)) {
                        $end_in_range = false;
                    }
                    
                    if($start_in_range || $end_in_range) {
                        $errors[] = "Interrupted Sleepover must be within the Sleepover Break Time";
                    }
                    else if(!empty($interrupted_so_list)) {                        
                        if(strtotime($in_so_break_start_datetime) < strtotime($interrupted_so_list[$key - 1]['break_end_datetime'])) {
                            $errors[] = "An interrupted S/O timing should not start before the previously specified interrupted S/O times";
                        }
                    }                    
                    $interrupted_so_list[$key]['break_start_datetime'] = $in_so_break_start_datetime;
                    $interrupted_so_list[$key]['break_end_datetime'] = $in_so_break_end_datetime;
                    //Create separete index for interrupted sleepover
                    $key = $key + 1;
                }
            }           
            
        }
        return $errors;
    }

    
     /**
     * To get contact list related to account get email phone
     */
    function get_contact_email_phone_by_account($reqData) {

        if (isset($reqData) && !empty($reqData->person_id)) {
                $this->db->select(["pe.email", "ph.phone","c.id as id"]);
                $this->db->from(TBL_PREFIX . 'person as c');
                $this->db->join('tbl_person_email as pe', 'pe.person_id = c.id and pe.primary_email=1 and pe.archive=0 ', 'left');
                $this->db->join('tbl_person_phone as ph', 'ph.person_id = c.id and ph.primary_phone=1 and ph.archive=0 ', 'left');
                $this->db->where(['c.archive' => 0, 'pe.archive'=>0, 'c.id'=>$reqData->person_id]);
                $this->db->limit(1);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];
                if(!empty($result)){
                    $result = $result[0];
                }
                return [ "status" => true, 'data' => $result, 'msg' => 'Successfully fetched'];
            } else {
                return [ "status" => true, 'error' => ' req data Id is null'];
            }
           
        }
        
        /** Check and pull the NA documents based on the shift and participant id
         * @param $member_id {int} member id
         * @param $shift_id {int} shift id
         * @param $participant_id {int} participant id
         * 
         * @return $result {array} True/False with message
         */
        public function check_shift_NA_assets($member_id, $shift_id, $participant_id) {
            if (empty($member_id) || empty($shift_id) || empty($participant_id))
            {
                return ['status' => FALSE,  'msg' => 'Something went wrong'];
            }
    
            $this->db->select("sm.*, s.shift_no");
            $this->db->from('tbl_shift as s');
            $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id', 'inner');          
            $this->db->where("sm.member_id", $member_id);
            $this->db->where("s.id", $shift_id);
            $this->db->where("s.account_id", $participant_id);
            $this->db->where("sm.archive", 0);
            $this->db->where("s.archive", 0);
            $query = $this->db->get();
            $result = ['status' => FALSE,  'msg' => 'Record not found'];

            if($query->num_rows() > 0) {
                $result = ['status' => TRUE, 'data' => $query->result_array(), 'msg' => 'Record found'];
            }

            return $result;
        }
        

    /**
     * Get already assigned ot hours for same member weekly max active hours exceed calculation.     
     * 
     * @param $member_id {int} member id
     * @param $shift_id {int} shift id
     * @param $start_col {string} start time
     * @param $end_col {string} end time
     * @param $week_start {string} start day of the week
     * @param $week_end {string} end day of the week
     * 
     * @return $tot_weekly_ot_hours {array} array of data
     */
    public function get_member_weekly_ot_duration($member_id, $shift_id, $start_col, $end_col, $week_start, $week_end) {
            
        $this->db->select(["SUM(ft.ot_15_total + ft.ot_20_total) AS hour_diff"]);
        $this->db->from('tbl_finance_timesheet AS ft');        
        #Skip the already created OT hours by weekly 38 hours and interrupted sleepover using is_exclude_ot variable
        $this->db->join('tbl_shift AS s', 's.id = ft.shift_id', 'inner');
        $this->db->join('tbl_shift_member as sm', 's.id = sm.shift_id and s.accepted_shift_member_id = sm.id', 'inner');
        
        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('sm.member_id', $member_id);
        $this->db->where('s.status in (5) and s.id != '.$shift_id);
        $this->db->where('ft.is_exclude_ot', 0);
        
        $this->db->where("
            ((s.".$start_col." >= '".$week_start." 10:00:00' or s.".$end_col." >= '".$week_start." 10:00:00') and s.".$end_col." <= '".$week_end." 10:00:00')
            ");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        
        $tot_weekly_ot_hours = 0;
        if($result) {
            $tot_weekly_ot_hours = $result[0]['hour_diff'];
        }

        return $tot_weekly_ot_hours;
    }

    /**
     * validate the shift support type
     * type - mixed
    */
    public function get_shift_support_type_validation($reqData) {
        $data = (array) $reqData->data;
        $support_type = $data['support_type'] ?? 0;
        $supportType = $this->basic_model->get_row('finance_support_type', ['key_name'], ['id' => $support_type]);      
        $support_key_name = '';
        if (isset($supportType) && !empty($supportType) && !empty($supportType->key_name)) {
            $support_key_name = $supportType->key_name;
        }      

        # If service agreement is not exist or service agreement contract not exist show error
        if(empty($data['validateOnly']) && isset($data['support_type']) && !empty($data['support_type']) && $support_key_name == 'mixed' && (!$data['sa_id'] || !$data['docusign_id'])){
            return ['status' => false, 'error' => 'Signed Service Agreement is required for Mixed Support shifts'];
        }
        # If 01 and 04 code not in the plan then show error
        if(empty($data['validateOnly']) && isset($data['support_type']) && !empty($data['support_type']) && $support_key_name == 'mixed' && isset ($data['missing_line_item']) && !$data['missing_line_item']){            
            $line_items = $this->validate_comm_access_self_care_available($data['sa_id']); 
            if( !empty($line_items) && !$line_items['status']) {
                return ['status' => false, 'error' => 'Both Self Care/Comm Access codes are required to be added in the plan for Mixed Support Shifts']; 
            }  
        }
        
        if(empty($data['validateOnly']) && isset($data['support_type']) && !empty($data['support_type']) && $support_key_name == 'mixed' && isset ($data['missing_line_item']) && $data['missing_line_item']) {
            return ['status' => false, 'error' => 'Both Self Care/Comm Access codes are required to be added in the plan for Mixed Support Shifts'];       
        }

        return ['status' => true, 'data' => ''];
   }
    /** Calculation Overtime for exceed total active hours > 10
     * @param $first_aid, $overtime_15, $overtime_20, $mon_fri,$saturday, $sunday, $public_holiday, $roundoff_val {int} hours
     * @param $mon_fri_10hr_span_exceed, $mon_sat_12hr_span_exceed {bool} TRUE/FALSE
     * $shift_day1, $shift_day2  {bool} TRUE/FALSE
     * 
     * @return {array} OT hours
     */
    public function single_day_overtime_calc($first_aid, $overtime_15, $overtime_20, $mon_fri,$saturday,$sunday, $public_holiday, $mon_fri_10hr_span_exceed, $mon_sat_12hr_span_exceed, $shift_day1, $shift_day2, $roundoff_val) {
        if($sunday && $mon_fri && ($sunday + $mon_fri > $roundoff_val)) {
            if($first_aid >= 10 && $sunday < 10) {
                $second_day_value = $mon_fri;
                $mon_fri_10hr_span_exceed = TRUE;
                $mon_fri = 10 - $sunday;
                $ot_hr = $second_day_value - $mon_fri;
                $overtime_15 = ($ot_hr > 2) ? 2 : $ot_hr;
                $overtime_20 = ($ot_hr > 2) ? $ot_hr - $overtime_15 : 0; 
            }
            else if($mon_fri >= 10) {
                $mon_fri_10hr_span_exceed = TRUE;
                $totOThrs = $mon_fri - 10;
                #if total OT hours exceed more than 2 means first 2 assign to 1.5 remaining 2.0
                if($totOThrs > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = ($totOThrs - $overtime_15 > 0)? $totOThrs - $overtime_15 : 0;
                } else {
                    $overtime_15 = $totOThrs;
                    $overtime_20 = 0;
                }
                $mon_fri = 10;
            }//Sunday shift exceed 10hr or equal to 10 hr means monday shift will be consider as OT 2.0
            else if($sunday >= 10) {
                $overtime_20 = $mon_fri;
                $mon_fri = $overtime_15 = 0;                
            }
            else {
                $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
            }
            
            if($mon_fri < 0)
                $mon_fri = 0;
        }
        else if($mon_fri && $saturday && ($saturday + $mon_fri > $roundoff_val)) {
            $mon_sat_12hr_span_exceed = TRUE;
            if($mon_fri >= 10) {
                $mon_fri_10hr_span_exceed = TRUE;
                $mon_ot_hr = $mon_fri - 10;
                $mon_fri = 10;
                $ot_15_adjutment = 0;

                $overtime_15 = 2;
                if($mon_ot_hr < 2) {
                    $ot_15_adjutment = 2 - $mon_ot_hr;
                }

                $overtime_20 = $saturday - $ot_15_adjutment;
                $saturday = 0;                                  
            }
            else {                    
                # Skip the weekday calculation if Saturday and weekday total duration is lessthan 2 for overnight minimum unit calculation                    
                $saturday = $saturday - $overtime_15 - $overtime_20;
                if($saturday < 0)
                    $saturday = 0;
                
                $mon_fri = $first_aid - $saturday - $overtime_15 - $overtime_20;
                if($mon_fri < 0)
                    $mon_fri = 0;                  
            }
        
        }
        else if($shift_day1 == 'public_holiday' && $shift_day2 == 'weekday' || $shift_day1 == 'public_holiday' && $shift_day2 == 'saturday') {
            $day = ($shift_day2 == 'weekday') ? 'mon_fri' : 'saturday'; 
            $second_day_value = ${$day};
           
            if($day == 'mon_fri') {
                $mon_fri_10hr_span_exceed = TRUE;
            } else {
                $mon_sat_12hr_span_exceed = TRUE;
            }

            //If firstday is weekday with PH/no OT and 2nd day is week day then take 1st PH day hrs and 
            //2nd day hrs for rounding off 10 example day 1 + day 2(3+7)
            if($public_holiday < 10 && $public_holiday + ${$day} > 10) {                    
                ${$day} = 10 - $public_holiday;
                $ot_hr = $second_day_value - ${$day};
                $overtime_15 = ($ot_hr > 2) ? 2 : $ot_hr;
                $overtime_20 = ($ot_hr > 2) ? $ot_hr - $overtime_15 : 0;
            }
            #If Public holiday exceed 12 or more than 12 then next weekday fully consider as a OT 2.0
            else if($public_holiday >= 12) {              
                $overtime_20 = ${$day};
                $overtime_15 = ${$day} = 0;                           
            }
            else if($public_holiday > 10 && $public_holiday < 12) {
              #If Public holiday is B/W 10 to 12 means                  
                $overtime_15 = 12 - $public_holiday;
                $overtime_20 = ${$day} - $overtime_15;
                ${$day} = 0;                
            }
            else if(${$day} >= 10) {
                $totOThrs = ${$day} - 10;
                #if total OT hours exceed more than 2 means first 2 assign to 1.5 remaining 2.0
                if($totOThrs > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = ($totOThrs - $overtime_15 > 0)? $totOThrs - $overtime_15 : 0;
                } else {
                    $overtime_15 = $totOThrs;
                    $overtime_20 = 0;
                }           
                ${$day} = 10;
            
            } else {
                ${$day} = ${$day} - $overtime_15 - $overtime_20;
            }
            
            if(${$day} < 0)
                ${$day} = 0;
        }
        else if($shift_day1 == 'weekday' && $shift_day2 == 'public_holiday') {
            $mon_fri_10hr_span_exceed = TRUE;
            #If Public holiday exceed 12 or more than 12 then next weekday fully consider as a OT 2.0
            // if($mon_fri >= 10 && $public_holiday < 12 || $mon_fri < 10 && $public_holiday < 12) 
            if($mon_fri >= 10 && $public_holiday < 12) {               
                $totOThrs = $mon_fri - 10;
                #if total OT hours exceed more than 2 means first 2 assign to 1.5 remaining 2.0
                if($totOThrs > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = ($totOThrs - $overtime_15 > 0)? $totOThrs - $overtime_15 : 0;
                } else {
                    $overtime_15 = $totOThrs;
                    $overtime_20 = 0;
                }           
                $mon_fri = 10;
            
            } #if mon_fri falls less than or equal to 10 means there is no overtime because next day is public holiday
             else if($public_holiday >= 12 || $first_aid > 10 && $mon_fri <= 10) { 
                 #if public holiday goes more than 12 hr then remaining will be OT 2.0               
                $overtime_20 = $overtime_15 = 0;                     
            }
            else {
                $mon_fri = $mon_fri - $overtime_15 - $overtime_20;
            }

            if($mon_fri < 0)
                $mon_fri = 0;
        }                  
        else if($shift_day1 == 'saturday' && $shift_day2 == 'public_holiday') {
            #If Public holiday exceed 12 or more than 12 then next weekday fully consider as a OT 2.0               
            $mon_sat_12hr_span_exceed = TRUE;
            $ot_duration = 10;     
            
            if($saturday > $ot_duration) {                    
                $ot_hrs = $saturday - $ot_duration;
                $overtime_15 = ($ot_hrs > 2) ? 2 : $ot_hrs;
                $overtime_20 = $ot_hrs - $overtime_15;
            } else if($saturday <= $ot_duration) {
                $overtime_15 = $overtime_20 = 0;
            }
            
        }
        else if($shift_day1 == 'public_holiday' && $shift_day2 == 'sunday' || $shift_day1 == 'sunday' && $shift_day2 == 'public_holiday') {
            $overtime_15 = $overtime_20 = 0;
        }             
        else if($saturday && $sunday) {        
            $mon_sat_12hr_span_exceed = TRUE;
            $ot_15_adjutment = 0;
           
            if($saturday > 10) {
                $sat_ot_hr = $saturday - 10;
                $saturday = 10;
                
                if($sat_ot_hr < 2) {
                    $overtime_15 = $sat_ot_hr;
                    $overtime_20 = 0;
                } else {
                    $overtime_20 = $sat_ot_hr - $overtime_15;
                }
                
            } else { //No over time for sundays
                $overtime_20 = $overtime_15 = 0;
            }
            
            if($saturday < 0)
                $saturday = 0;                    
        } 
        else if($sunday && !$saturday) {
            $sunday = $sunday - $overtime_15 - $overtime_20;
            if($sunday < 0)
                $sunday = 0;

            # BR-29, give overtime 1.5 and overtime 2.0 to sunday line item as sunday rates are already same as overtime 1.5 and overtime 2.0
            list($sunday, $overtime_15, $overtime_20) = $this->adjust_sunday_line_items($sunday, $overtime_15, $overtime_20);
        }else if($mon_fri || $saturday) {
            $day = $saturday ? 'saturday' : 'mon_fri';
           if(${$day} >= 10) {
               if($mon_fri) {
                    $mon_fri_10hr_span_exceed = TRUE;
               } else {
                $mon_sat_12hr_span_exceed = TRUE;
               }
                
                $totOThrs = ${$day} - 10;
                #if total OT hours exceed more than 2 means first 2 assign to 1.5 remaining 2.0
                if($totOThrs > 2) {
                    $overtime_15 = 2;
                    $overtime_20 = ($totOThrs - $overtime_15 > 0)? $totOThrs - $overtime_15 : 0;
                } else {
                    $overtime_15 = $totOThrs;
                    $overtime_20 = 0;
                }           
                ${$day} = 10;
            
            } else {
                ${$day} = ${$day} - $overtime_15 - $overtime_20;
            }
            
            if(${$day} < 0)
                ${$day} = 0;
        }
        
        return [ $mon_fri, $saturday, $sunday, $public_holiday, $overtime_15, $overtime_20,$mon_fri_10hr_span_exceed, $mon_sat_12hr_span_exceed ];
    }

    #Unset the timesheet calcuation session
    function remove_timesheet_calc_session() {
        $this->session->unset_userdata(['is_exclude_ot','overtime_15_tot','overtime_20_tot']);
    }

    /**
     * Calculate Sunday and Public holiday OT hours for various calculation like weekly
     * 38 hrs execeed.
     * @param $overnight_shift {array} Reference shift details
     * @param $shift_details {array} current shift details
     * @param $first_aid {int} first aid hours
     * @param $sunday {int} sunday hours
     * @param $public_holiday {int} public holiday hours
     * 
     * @see minutesDifferenceBetweenDate
     * @return $overtime_20_tot {int} total hours of overtime 2.0
     */
    function calc_sunday_ph_ot20($overnight_shift, $shift_details, $first_aid, $sunday, $public_holiday) {
        $overtime_20_tot = 0;
        $ot_tot_span_time = date("Y-m-d H:i:s", strtotime('+12 hours', strtotime($overnight_shift['reference_date'])));
        $ot_day = ($sunday) ? 'sunday' : 'public_holiday';
        #Shift falls completly after over time span hours means full saturday as Overtime and sunday will be sunday
        if($shift_details['actual_start_datetime'] >= $ot_tot_span_time || $overnight_shift['is_full_ot']) { 
            $overtime_20_tot = ${$ot_day};               
        } else {
            $mins = minutesDifferenceBetweenDate($shift_details['actual_start_datetime'] , $ot_tot_span_time);       
            $bal_time = round(( $mins/ 60), 2);
            $overtime_20_tot = $first_aid - $bal_time;
        }
        
        return $overtime_20_tot;
    }

    /***
     * validate line item self care and comm access available or not in SA
     */
    public function validate_comm_access_self_care_available($sa_id){
        if(!empty($sa_id)){
            $this->load->model('schedule/Ndispayments_model');
           
            $this->db->select("CONCAT(0, id) AS category_ref");
            $this->db->from('tbl_finance_support_type');
            $this->db->where_in('key_name', ['self_care', 'comm_access']);
            $query = $this->db->get();
            $response = $query->result_array();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    $line_items = $this->Ndispayments_model->check_sa_parent_line_item_availablity(($val), $sa_id);
                    if(!$line_items){
                        return [ 'status' => false , 'msg' => 'Item not found']; 
                    }
                    
                }
            }

            return [ 'status' => true]; 
        }
    }
   
}
