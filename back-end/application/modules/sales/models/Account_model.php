<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * class: Contact_model
 * use for query operation of contact
 */

//class Master extends MX_Controller
class Account_model extends Basic_Model {

    function __construct() {
        parent::__construct();
        $this->load->model('common/Common_model');
        $this->object_fields['name'] =  function($id = '', $type = '') {
                                            if (empty($id)) {
                                                return 'Name';
                                            }
                                            $result = [];
                                            if ($type == 1) {
                                                $result = $this->get_record_where('person', 'CONCAT(firstname, " ", lastname) as name', ['id' => $id, 'archive' => '0']);
                                            }
                                            if ($type == 2) {
                                                $result = $this->get_record_where('organisation', 'name', ['id' => $id, 'archive' => '0']);
                                            }
                                            $name = count($result)? $result[0]->name : "";                                            
                                            return $name;
                                        };
    }

    /*
     * its use for get contact list 
     * 
     * operation: searching, filter, sorting
     * return type array
     */

    public function get_account_list($reqData, $filter_condition = '') {
        // get subquery of cerated by
        $this->load->model('Contact_model');
        $uuid_user_type = $reqData->uuid_user_type;
        $reqData = $reqData->data;
        $createdByNameSubQuery = $this->Contact_model->get_contact_created_by_sub_query("o", $uuid_user_type);

        $limit = $reqData->pageSize ?? 999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        
        // searching column
        $src_columns = array('org_code', 'name', 'o.created_by', 'primary_contact_name', 'DATE_FORMAT(o.created, \'%d/%m/%Y\')');
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

        $available_column = ["id", "org_code", "created", "name", "created_by"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'o.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "active") {
                $this->db->where('o.status', 1);
            } elseif ($filter->filter_status === "inactive") {
                $this->db->where('o.status', 0);
            }
        }

        $select_column = ["o.id", "o.org_code", "o.created", "o.name", "o.created_by"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by, concat_ws(' ',pp.firstname,pp.lastname) as primary_contact_name, pp.id as primary_contact_id");

        $this->db->select("(CASE  
			WHEN o.status = 1 THEN 'Active'
			WHEN o.status = 0 THEN 'Inactive'
			Else '' end
		) as status");

        $this->db->from('tbl_organisation as o');
        $this->db->join('tbl_sales_relation as sr', 'sr.source_data_id = o.id and sr.source_data_type = 2 and sr.archive = 0 and sr.is_primary = 1', 'left');
        $this->db->join('tbl_person as pp', 'sr.destination_data_id = pp.id and sr.destination_data_type = 1 and pp.archive = 0', 'left');

        $this->db->where(array('o.archive' => 0, 'is_site' => 0));
        if (!empty($filter_condition)) {
            $this->db->where($filter_condition);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $result = $query->result();
        $return = array('total_item' => $total_item, 'data' => $result, 'status' => true);
        return $return;
    }

    /*
     * @query of check abn numbur already exist
     * 
     * @params $abn
     * 
     * @return type query result 
     */

    function check_abn_number_should_be_uniqe($abn, $org_id) {
        $abn = str_replace(' ', '', $abn);

        $this->db->select(["o.abn"]);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.archive", 0);
        $this->db->where("o.abn", $abn);

        if ($org_id) {
            $this->db->where("o.id != ", $org_id, false);
        }

        return $this->db->get()->row();
    }

    /*
     * @query for check account name already exist
     * 
     * @params $account_name
     * 
     * return type query result
     */

    function check_account_name_should_be_uniqe($account_name, $org_id) {

        $this->db->select(["o.id"]);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.archive", 0);
        $this->db->where("o.name", $account_name);

        if ($org_id) {
            $this->db->where("o.id != ", $org_id, false);
        }

        return $this->db->get()->row();
    }

    /*
     * its use get id of account type organisation
     * 
     * @return type id
     */

    function get_account_type_id_by_key() {
        $res = $this->basic_model->get_row("person_type", ["id"], ["key_name" => "organisation"]);
        return $res->id ?? null;
    }

    /*
     * its use for check valid address of organisation
     * 
     * @params $address
     * 
     * return type boolean
     */

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

    /*
     * its use for create orgnisation
     * 
     * return type $orgId
     */
    function create_organisation($data, $adminId, $unq_check = true) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $org_id = $data['id'] ?? 0;
        $is_site = $data['is_site'] ?? 0;
        $data['service_area_selected_options_length'] = count($data['service_area_selected_options']) > 0 ? 1 : '';
        // validation rule
        if(!$is_site && $unq_check)
            $validation_rules[] = array('field' => 'ABN', 'label' => 'ABN', 'rules' => 'callback_check_abn_number_should_be_uniqe[' . $org_id . ']');
        if($unq_check)
            $validation_rules[] = array('field' => 'account_name', 'label' => 'Account Name', 'rules' => 'callback_check_account_name_should_be_uniqe[' . $org_id . ']');
        $validation_rules[] = array('field' => 'service_area_selected_options_length', 'label' => 'Service Area', 'rules' => 'required');
        $validation_rules[] = array('field' => 'shipping_address', 'label' => 'Shipping Address', 'rules' => 'required');

        // callback for payable email address
        if (!empty($reqData->data->payable_email)) {
            $validation_rules[] = array('field' => 'payable_email', 'label' => 'Email', 'rules' => 'callback_check_payable_email_address[' . json_encode($data) . ']');
        }
        $validation_rules[] = array('field' => 'billing_address', 'label' => 'Billing Address', 'rules' => 'required');
        $validation_rules[] = array('field' => 'invoice_to', 'label' => 'Invoice To', 'rules' => 'required');
        $validation_rules[] = array('field' => 'payable_phone', 'label' => 'Account Payable Phone', 'rules' => 'required');
        $validation_rules[] = array('field' => 'payable_email', 'label' => 'Account Payable Email', 'rules' => 'required');
        // callback for payable phone number
        if (!empty($reqData->data->payable_phone)) {
            $validation_rules[] = array('field' => 'payable_phone', 'label' => 'Phone', 'rules' => 'callback_phone_number_check[phone,,Please enter valid payable phone number.]');
        }

        // set data in libray for validate
        $this->form_validation->set_data($data);

        // set validation rule
        $this->form_validation->set_rules($validation_rules);

        // check data is valid or not
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }

        // create/updated organisation of account
        $orgId = $this->insert_update_organisation($data, $adminId);

        if (!empty($orgId)) {
            $data['id'] = $orgId;
        }

        // organisation phone
        if (!empty($data['primary_phone']) || !empty($data['secondary_phone'])) {
            $pp = $data['primary_phone']?? '';
            $sp = $data['secondary_phone']?? '';
            $this->basic_model->update_records("organisation_phone", ["archive" => 1], ["organisationId" => $orgId]);
            $phones = [];
            $phones[] = [
                "organisationId" => $orgId,
                "phone" => $pp,
                "primary_phone" => 1,
                "archive" => 0,
            ];
            if (!empty($data['secondary_phone'])) {
                $phones[] = [
                    "organisationId" => $orgId,
                    "phone" => $sp,
                    "primary_phone" => 0,
                    "archive" => 0,
                ];
            }
            // insert phone
            $this->basic_model->insert_records("organisation_phone", $phones, true);
        }

        // organisation email
        if (!empty($data['primary_email']) || !empty($data['secondary_email'])) {
            $pe = $data['primary_email']?? '';
            $se = $data['secondary_email']?? '';
            $this->basic_model->update_records("organisation_email", ["archive" => 1], ["organisationId" => $orgId]);
            $email_ids = [];
            $email_ids[] = [
                "organisationId" => $orgId,
                "email" => $pe,
                "primary_email" => 1,
                "archive" => 0,
            ];
            if (!empty($data['secondary_email'])) {
                $email_ids[] = [
                    "organisationId" => $orgId,
                    "email" => $se,
                    "primary_email" => 0,
                    "archive" => 0,
                ];
            }

           
            //insert email
           $this->basic_model->insert_records("organisation_email", $email_ids, true);
        }

        // payable phone
        if (!empty($data['payable_phone'])) {           
            $this->basic_model->update_records("organisation_accounts_payable_phone", ["archive" => 1], ["organisationId" => $orgId]);

            $payable_phone = [
                "organisationId" => $orgId,
                "phone" => $data['payable_phone'],
                "primary_phone" => 1,
                "archive" => 0,
            ];

            // insert phone
            $this->basic_model->insert_records("organisation_accounts_payable_phone", $payable_phone, $multiple = FALSE);
        }

        // payable email
        if (!empty($data['payable_email'])) {  
            $this->basic_model->update_records("organisation_accounts_payable_email", ["archive" => 1], ["organisationId" => $orgId]);          

            $payable_email = [
                "organisationId" => $orgId,
                "email" => $data['payable_email'],
                "primary_email" => 1,
                "archive" => 0,
            ];

            // insert payable email
            $this->basic_model->insert_records("organisation_accounts_payable_email", $payable_email, $multiple = FALSE);
        }

        $address = [];

        $this->load->model("organisation/Org_model");
        $this->Org_model->update_organisation_address(["archive" => 1], ["organisationId" => $orgId, "address_type" => 1]);
        if (!empty($data["billing_address"])) {
            $addr = devide_google_or_manual_address($data["billing_address"]);

            $address = [
                "organisationId" => $orgId,
                "primary_address" => 1,
                "address_type" => 1,
                "street" => $addr["street"] ?? '',
                "city" => $addr["suburb"] ?? '',
                "postal" => $addr["postcode"] ?? '',
                "state" => !empty($addr["state"]) ? $addr["state"] : "",
                "unit_number" => !empty($data["billing_unit_number"]) ? $data["billing_unit_number"] : ""
            ];

            // insert billing address
            $this->Org_model->create_organisation_address($address);
        }

        $this->Org_model->update_organisation_address(["archive" => 1], ["organisationId" => $orgId, "address_type" => 1, "address_type" => 2]);
        
        if (!empty($data["shipping_address"])) {
            $addr = devide_google_or_manual_address($data["shipping_address"]);

            $address = [
                "organisationId" => $orgId,
                "primary_address" => 1,
                "address_type" => 2,
                "street" => $addr["street"] ?? '',
                "city" => $addr["suburb"] ?? '',
                "postal" => $addr["postcode"] ?? '',
                "state" => !empty($addr["state"]) ? $addr["state"] : "",
                "unit_number" => !empty($data["shipping_unit_number"]) ? $data["shipping_unit_number"] : ""
            ];

            // insert shipping_address
            $this->Org_model->create_organisation_address($address);
        }
        // insert service areas
        if ($orgId) {
            $service_areas = $this->get_organisation_service_area($orgId);
            $sa = $data['service_area_selected_options'];
            $sa_to_delete = [];
            $sa_to_insert = [];
            $existing = [];
            foreach($service_areas as $service_area) {
                $existing[] = $service_area['value'];
                if ( !in_array($service_area['value'], $sa) || empty($sa)) {
                    $sa_to_delete[] = $service_area['value'];
                }
            }
            $org_sa = [];
            foreach($sa as $v) {
                if (!in_array($v, $existing)) {
                    $org_sa[] = [
                        "organisation_id" => $orgId,
                        "service_area_id " => $v,
                        "archive " => 0
                    ];
                }                
            }
            if (!empty($sa_to_delete)) {
                foreach($sa_to_delete as $satd) {
                    $this->basic_model->update_records("organisation_service_area", ['archive' => 1], ['organisation_id' => $orgId, 'service_area_id' => $satd]);
                } 
            }
            if (!empty($org_sa)) {
                $this->basic_model->insert_records("organisation_service_area", $org_sa, true);
            }
        }
             
        if($data['is_site']==1)
        {   
            $org_id = $data['id'] ?? 0;
            $swa_id=$data['selected_swa'];
            $this->insert_update_preferred_swa($org_id ,$swa_id,$adminId);
        }
            
        
        // check $orgId is not empty 
        // according to that got orgni is created or not
        if ($orgId) {
            $this->load->library('UserName');
            $adminName = $this->username->getName('admin', $adminId);
            $caption = $is_site ? "Site" : "Organisation";

            // create log setter getter
            if ($org_id>0) {
                $msg = "{$caption} has been updated successfully.";
                $this->loges->setTitle("Updated {$caption} of " . $data['account_name'] . " by " . $adminName);  // set title in log
            } else {
                $msg = "{$caption} has been created successfully.";
                $this->loges->setTitle("New {$caption} created for " . $data['account_name'] . " by " . $adminName);  // set title in log
            }

            $this->loges->setDescription(json_encode($data));
            $this->loges->setUserId($orgId);
            $this->loges->setCreatedBy($adminId);
            $this->loges->createLog(); // create log

            $response = ['status' => true, 'msg' => $msg];
            return $response;
        }

        $response = ['status' => false, 'error' => "Something went wrong!"];
        return $response;
    }

    /*
     * its use for insert/update organisation data
     * return type $orgId
     */

    function insert_update_organisation($data, $adminId) {
        $org_id = $data['id'] ?? 0;

        $org_data = [
            "logo_file" => "",
            "parent_org" => $data["parent_org"]['value'] ?? '',
            "is_site" => $data["is_site"] ? 1 : 0,
            "cost_book_id" => $data["cost_book_id"] ?? null,
            "website" => $data["website"] ?? '',
            "role_id" => $data["role_id"] ?? 0,
            "otherName" => $data["otherName"] ?? '',
            "fax" => $data["fax"] ?? '',
            "status" => 1,
            "archive" => 0,
            "source_type" => 3,
            "dhhs" => $data["dhhs"] ??0,
            "invoice_to" => $data["invoice_to"] ?? '',
            "billing_same_as_parent" => $data["billing_same_as_parent"] ?? false
        ];

        if(!empty($data["billing_same_as_parent"]) && !empty($data["is_site"]) && !empty($data["parent_org"]['value'])){
            $parent_org_id = $data["parent_org"]['value'];
            $parent_billing_data = $this->basic_model->get_row("organisation", ["gst,site_discount,payroll_tax"], ["id" => $parent_org_id]);
            $org_data['gst'] = $parent_billing_data->gst;
            $org_data['site_discount'] = $parent_billing_data->site_discount;
            $org_data['payroll_tax'] = $parent_billing_data->payroll_tax;            
        }
        if ($org_id) {
            $org_data["updated"] = DATE_TIME;
            $org_data["updated_by"] = $adminId;
            $org_data["status"] = $data["status"];
            $org_data["name"] = $data["account_name"];
            $org_data["abn"] = (!empty($data["abn"])) ? str_replace(' ', '', $data["abn"]) : '';
            $org_data["org_type"] = isset($data["org_type"]) ? $data["org_type"] : 0;
            $org_data["role_id"] = $data["role_id"] ?? 0;
            $this->basic_model->update_records("organisation", $org_data, ["id" => $org_id]);
            return $org_id;
        } else {
            $org_data["created"] = DATE_TIME;
            $org_data["created_by"] = $adminId;
            $org_data["name"] = $data["account_name"];
            $org_data["abn"] = (!empty($data["abn"])) ? str_replace(' ', '', $data["abn"]) : '';
            $org_data["org_type"] = isset($data["org_type"]) ? $data["org_type"] : 0;
            $org_data["role_id"] = $data["role_id"] ?? 0;
            return $this->basic_model->insert_records("organisation", $org_data, $multiple = FALSE);
        }
    }

    /*
     * @query query of making option of account
     * 
     * @return type array
     */

    function get_option_of_account($search_key , $type = '') {
        $this->db->select(["name as label", "o.id as value"]);
        $this->db->from("tbl_organisation as o");
        if(!empty($type) && $type=='organisation'){
            $this->db->where("o.is_site", 0);
        }
        $this->db->like("o.name", $search_key);        
        return $this->db->get()->result();
    }

    /**
     * fetches the next site number for a given parent org
     */
    public function get_next_site_no($account_id) {
        # finding how many got added so far
        $details = $this->basic_model->get_row('organisation', array("count(id) AS total"), array("parent_org" => $account_id, "is_site" => 1));
        $nextno = "1";
        if(!empty($details) && isset($details->total)) {
            $nextno = $details->total + 1;
        }
        return array('status' => true, 'data' => $nextno);
    }

    /*
     * its use for archive account
     * 
     * @params $org_id
     * return type boolean
     * true/false
     */
    function archive_account($org_id, $adminId) {

        # does the organisation exist?
        $result = $this->get_organisation_details($org_id);
        if (empty($result)) {
            $response = ['status' => false, 'error' => "Organisation does not exist anymore."];
            return $response;
        }

        $updated_data = ["archive" => 1];
        $this->basic_model->update_records("organisation", $updated_data, ["id" => $org_id]);

        # adding a log entry
        $logtitle = sprintf("Successfully archived organisation with ID of %s", $org_id);
        $this->add_organisation_log($updated_data, $logtitle, $org_id, $adminId);

        $type = "Organisation";
        if($result->is_site == 1)
            $type = "Site";

        return ['status' => true, 'msg' => "{$type} successfully archived"];
    }

    /**
     * getting org account details for pre-selection
     */
    function get_organisation_account_details($org_id) {
        $retarr = [];
        $det = $this->get_organisation_details($org_id);
        if(!empty($det)) {
            $retarr['label'] = $det->account_name;
            $retarr['value'] = $org_id;
        }
        return $retarr;
    }

    /**
     * creates a new org member record or updates an existing one
     */
    function create_update_org_member($data, $adminId) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $org_member_id = $data['id'] ?? 0;
        $org_id = $data['org_id'] ?? 0;

        # validation rule
        $validation_rules = [
            array('field' => 'org_id', 'label' => 'Organisation', 'rules' => 'required'),
            array('field' => 'member_id', 'label' => 'Member', 'rules' => 'required'),
        ];

        # checking end date & time
        if (!empty($data['reg_date'])) {
            $validation_rules[] = array(
                'field' => 'reg_date', 'label' => 'Registration date', 'rules' => 'required|valid_date_format[Y-m-d]',
                'errors' => [
                    'valid_date_format' => 'Incorrect registration date',
                ]
            );
        }

        # checking if the unavailability for member is not previously added
        if(!empty($data['org_id']) && !empty($data['member_id']))
        {
            $rows = $this->check_org_member_already_exist($data['org_id'],$data['member_id'],$org_member_id);
            if(!empty($rows))
            {
                $errors = 'Member "'.$rows[0]['fullname'].'" already added for this organisation';
                $return = array('status' => false, 'error' => $errors);
                echo json_encode($return);exit();
            }
        }

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

        # call create/update organisation member function
        $org_member_id = $this->populate_org_member($data, $adminId);

        # check $org_member_id is not empty
        if (!$org_member_id) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # adding a log entry
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);
        if (!empty($data['id'])) {
            $this->loges->setTitle("Updated registered member by " . $adminName);
        } else {
            $this->loges->setTitle("New registered member created by " . $adminName);
        }
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($data['org_id']);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Registered member has been updated successfully.';
        } else {
            $msg = 'Registered member has been created successfully.';
        }
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /**
     * checks if an entry exists for an organisation and member
     */
    public function check_org_member_already_exist($org_id, $member_id, $id=0) {
        $this->db->select(array('m.fullname'));
        $this->db->from('tbl_organisation_member as om');
        $this->db->join('tbl_member as m', 'm.id = om.member_id', 'inner');
        $this->db->where('om.archive', 0);
        if($id>0)
            $this->db->where('om.id != ', $id);

        $this->db->where("om.member_id", $member_id);
        $this->db->where("om.organisation_id", $org_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }

    /**
     * fetching a single organisation member details
     */
    public function get_organisation_member_details($id) {
        if (empty($id)) return;

        $this->db->select("om.*, m.id as value, m.fullname as label");
        $this->db->from('tbl_organisation_member as om');
        $this->db->join('tbl_member m', 'om.member_id = m.id', 'inner');
        $this->db->where("om.id", $id);
        $this->db->where("om.archive", "0");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; #$ci->db->last_query();

        $dataResult = null;
        if (empty($query->result())) {
            $return = array('msg' => "Registered member not found!", 'status' => false);
            return $return;
        }
        foreach ($query->result() as $val) {
            $row = $val;
            $dataResult = $row;

            # for member pre-selection
            $member['label'] = $val->label;
            $member['value'] = $val->value;
            $dataResult->member = $member;
        }

        $return = array('data' => $dataResult, 'status' => true, 'last_query' => $last_query);
        return $return;
    }

    /*
     * For getting organisations members list
     */
    public function get_organisation_members_list($reqData,$from_fms=false) {

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("m.fullname", "r.display_name","DATE_FORMAT(om.reg_date,'%d/%m/%Y')","DATE_FORMAT(om.created,'%d/%m/%Y')","om.ref_no");
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

        # sorting part
        $available_column = ["id", "status_label", "member_id", "organisation_id", "fullname","reg_date","created","ref_no"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'om.id';
            $direction = 'DESC';
        }
        $select_column = ["om.id", "r.display_name as status_label", "om.member_id", "om.organisation_id", "m.fullname","om.reg_date","om.created","om.ref_no", "'' as actions"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_organisation_member as om');
        $this->db->join('tbl_member as m', 'm.id = om.member_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = om.status', 'left');
        $this->db->where('om.archive', 0);

        if(isset($reqData->account_id) && $reqData->account_id > 0)
        $this->db->where('om.organisation_id', $reqData->account_id);
        if($from_fms==true){
            $this->db->where('om.member_id', $reqData->member_id);
        }
        $this->db->order_by($orderBy, $direction);
        if($limit>0)
        {
            $this->db->limit($limit, ($page * $limit));
        }
        
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; #$ci->db->last_query();

        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched registered members list successfully', 'last_query' => $last_query);
        return $return;
    }

    /**
     * archiving account contact
     */
    function archive_account_contact($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        # does the account contact exists?
        $res = $this->basic_model->get_row("sales_relation", ["source_data_type"], ["id" => $id]);
        $account_type = $res->source_data_type ?? null;
        if(empty($account_type)) {
            $response = ['status' => false, 'error' => "Account contact not found!"];
            return $response;
        }

        $type = "organisation";
        if($account_type == 3)
        $type = "opportunity";

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("sales_relation", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # adding a log entry
        $logtitle = sprintf("Successfully archived {$type} contact with ID of %s", $data['id']);
        $this->add_organisation_log($data, $logtitle, $id, $adminId);

        $response = ['status' => true, 'msg' => "Successfully archived {$type} contact"];
        return $response;
    }

    /**
     * archiving organisation member
     */
    function archive_organisation_member($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }

        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("organisation_member", $upd_data, ["id" => $id]);

        if (!$result) {
            $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
            return $response;
        }

        # adding a log entry
        $logtitle = sprintf("Successfully archived registered member with ID of %s", $data['id']);
        $this->add_organisation_log($data, $logtitle, $id, $adminId);

        $response = ['status' => true, 'msg' => "Successfully archived registered member"];
        return $response;
    }

    /**
     * inserts or updates the organisation member record in the database
     */
    function populate_org_member($data, $adminId) {
        $org_member_id = $data['id'] ?? 0;

        $unavailabilitydata = [
            "organisation_id" => $data['org_id'],
            "member_id" => $data['member_id'],
            "reg_date" => (!empty($data["reg_date"])) ? $data["reg_date"] : null,
            "ref_no" => (!empty($data["ref_no"])) ? $data["ref_no"] : null,
            "status" => (isset($data['status']) && !empty($data["status"])) ? $data["status"] : null,
            "archive" => 0
        ];

        if ($org_member_id) {
            $unavailabilitydata["updated"] = DATE_TIME;
            $unavailabilitydata["updated_by"] = $adminId;
            $this->basic_model->update_records("organisation_member", $unavailabilitydata, ["id" => $org_member_id]);
            return $org_member_id;
        } else {
            $unavailabilitydata["created"] = DATE_TIME;
            $unavailabilitydata["created_by"] = $adminId;
            return $this->basic_model->insert_records("organisation_member", $unavailabilitydata, $multiple = FALSE);
        }
    }

    /*
     * its use for get org/account detaisl
     * 
     * @params $org_id
     * 
     * return ttype array object
     * organisation details
     */
    function get_organisation_details($org_id) {
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as m where m.id = o.owner limit 1) as owner_name");
        $this->db->select("(select sub_o.name from tbl_organisation as sub_o where sub_o.id = o.parent_org) as parent_org_name");
        $this->db->select("(select name from tbl_member_role as r where r.id = o.role_id) as role_name");
        $this->db->select("(select r.display_name from tbl_references as r where r.id = o.org_type limit 1) as org_source_name");
        $this->db->select(["o.name as account_name", "o.abn", "o.website", "o.otherName","o.fax", "o.id", "o.parent_org", "o.status", "o.archive","o.org_type","o.role_id","o.dhhs","o.is_site","concat_ws(' ',pp.firstname,pp.lastname) as primary_contact_name", "pp.id as primary_contact_id", "o.cost_book_id", "r.display_name as cost_book_name","o.invoice_to","o.billing_same_as_parent", "o.gst", "o.payroll_tax", "o.communication_mode", "o.site_discount", "o.billing_same_as_parent"]);
        $this->db->from("tbl_organisation as o");
        $this->db->join('tbl_sales_relation as sr', 'sr.source_data_id = o.id and sr.source_data_type = 2 and sr.archive = 0 and sr.is_primary = 1', 'left');
        $this->db->join('tbl_person as pp', 'sr.destination_data_id = pp.id and sr.destination_data_type = 1 and pp.archive = 0', 'left');
        $this->db->join('tbl_references as r', 'r.id = o.cost_book_id', 'left');
        $this->db->where("o.id", $org_id);

        $org = $this->db->get()->row();
        // get org phone
        $org->primary_phone = null;
        $org->secondary_phone = null;
        $org->is_secondary_phone = 0;
        $org_phone_data = $this->get_organisation_phone($org_id);
        if(!empty($org_phone_data)){
            $filter_pp = array_filter($org_phone_data, function($ph) {
                return $ph['primary_phone'];
            });
            $org->primary_phone = count($filter_pp)? array_values($filter_pp)[0]['phone'] : '';
            $filter_sp = array_filter($org_phone_data, function($ph) {
                return !$ph['primary_phone'];
            });
            $org->secondary_phone = count($filter_sp)? array_values($filter_sp)[0]['phone'] : '';
            if ($org->secondary_phone) {
                $org->is_secondary_phone = 1;
            }
        }
        // get org email
        $org->primary_email = $org->secondary_email = '';
        $org->is_secondary_email = 0;
        $org_email_data = $this->get_organisation_email($org_id);
        if(!empty($org_email_data)){
            $filter_pe = array_filter($org_email_data, function($ph) {
                return $ph['primary_email'];
            });
            $org->primary_email = count($filter_pe)? array_values($filter_pe)[0]['email'] : '';
            $filter_se = array_filter($org_email_data, function($ph) {
                return !$ph['primary_email'];
            });
            $org->secondary_email = count($filter_se)? array_values($filter_se)[0]['email'] : '';
            if ($org->secondary_email) {
                $org->is_secondary_email = 1;
            }
        }
        $org->parent_org_service_area_options = [];
        
            $org->parent_org_id = $org->parent_org;
        //get org primary email
      /*   $org_primary_email = $this->get_organisation_email($org_id);
            if(!empty($org_primary_email)){
                $org->primary_email  = $org_primary_email;
            } */
        

        if (!empty($org)) {
            if (!empty($org->parent_org_name)) {
              $get_child_par_phone = $this->get_organisation_phone($org->parent_org);
              $org->parent_org_service_area_options = $this->get_organisation_service_area($org->parent_org);
              $org->parent_org = ["value" => $org->parent_org, "label" => $org->parent_org_name,"phone"=>$get_child_par_phone];              
            }

            if ($org->abn) {
                $org->valid_abn = true;
            }

            // get org payable phone
            $org->payable_phone = $this->get_organisation_payable_phone($org_id);

            // get org payable email
            $org->payable_email = $this->get_organisation_payable_email($org_id);

            $this->load->model('organisation/Org_model');
            // get org billing address
            $billing_address_data = $this->Org_model->get_organisation_address($org_id, 1);

            if(!empty($billing_address_data)){
                $org->billing_address = $billing_address_data->address ?? '';
                $org->billing_unit_number = $billing_address_data->unit_number ?? '';
            }           

            // get org shipping address
            $shipping_address_data = $this->Org_model->get_organisation_address($org_id, 2);    
            if(!empty($shipping_address_data)){
                $org->shipping_address = $shipping_address_data->address ?? '';
                $org->shipping_unit_number = $shipping_address_data->unit_number ?? '';  
            }
            // get org service area
            $org->service_area_selected_options = $this->get_organisation_service_area($org_id);
         
             //get_organisation_swa
            if($org->is_site==1)
            {
                $org->selected_support_area_worker = $this->get_organisation_swa($org_id);  
                $sa_id = !empty($org->service_area_selected_options)? $org->service_area_selected_options[0]['value'] : 0;
                $swa_id = !empty($org->selected_support_area_worker)? $org->selected_support_area_worker[0]['value'] : 0;
                $org->cost_codes = $this->get_sa_swa_cost_codes($sa_id, $swa_id);
            }
            $org->parent_billing_info = null;
            if (!empty($org->parent_org_id)) {
                $parent_billing_info = $this->get_organisation_billing($org->parent_org_id);
                $org->parent_billing_info = $parent_billing_info? $parent_billing_info['org_billing'] : null;
            }
        }

        return $org;
    }

    /*
     * its use for get organisation phone
     * 
     * @params  $org_id
     * 
     * return type string
     * phone
     */

    function get_organisation_phone($org_id) {
        $this->db->select(["op.phone", "primary_phone"]);
        $this->db->from("tbl_organisation_phone as op");
        $this->db->where("op.organisationId", $org_id);
        $this->db->where("op.archive", 0);

        return $this->db->get()->result_array();
    }

    /*
     * its use for get organisation email
     * 
     * @params  $org_id
     * 
     * return type string
     * email
     */

    function get_organisation_email($org_id) {
        $this->db->select(["oe.email", "primary_email"]);
        $this->db->from("tbl_organisation_email as oe");
        $this->db->where("oe.organisationId", $org_id);
        $this->db->where("oe.archive", 0);

        return $this->db->get()->result_array();
    }

    /*
     * its use get sub organisation details of organsaiton
     * 
     * @params $org_id
     * 
     * return type array
     */
    function get_sub_organisation_details($org_id, $is_site = null, $reqData = null, $uuid_user_type='') {

        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = 'o.id';
        $direction = 'DESC';
        // get subquery of cerated by
        $this->load->model("Common/Common_model");
        $createdByNameSubQuery = $this->Common_model->get_created_by_updated_by_sub_query("o",$uuid_user_type,"created_by");   
        # Searching column
        $src_columns = array("o.name","o.id", "r.name as service_type_label", "oa.street", "oa.city", "oa.postal", "DATE_FORMAT(o.created,'%d/%m/%Y')", "DATE_FORMAT(o.updated,'%d/%m/%Y')", "(CASE WHEN o.status = 1 THEN 'Active' WHEN o.status = 0 THEN 'Inactive' else '' end)");
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

        # sorting part
        $available_column = ["name","id", "service_type_label", "created", "updated","created_by"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        }

        $select_column = ["o.name","o.id", "r.name as service_type_label", "o.created", "o.updated","o.created_by"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by");


        $this->db->select(["concat(oa.street,', ',oa.city,' ',(select s.name from tbl_state as s where s.id = oa.state),' ',oa.postal) as shipping_address, concat_ws(' ',pp.firstname,pp.lastname) as primary_contact_name, pp.id as primary_contact_id"]);
        $this->db->select("(CASE 
                WHEN o.status = 1 THEN 'Active'
                WHEN o.status = 0 THEN 'Inactive'
                else ''
                end) as  status
                ");
        $this->db->select("(select phone from tbl_organisation_phone as op where op.organisationId = o.id and op.archive = 0 and op.primary_phone limit 1) as phone");
        $this->db->from("tbl_organisation as o");
        $this->db->join('tbl_member_role as r', 'o.role_id = r.id', 'left');
        $this->db->join('tbl_organisation_address as oa', 'oa.organisationId = o.id and oa.address_type = 2 and oa.archive = 0 and oa.primary_address = 1', 'left');
        $this->db->join('tbl_sales_relation as sr', 'sr.source_data_id = o.id and sr.source_data_type = 2 and sr.archive = 0 and sr.is_primary = 1', 'left');
        $this->db->join('tbl_person as pp', 'sr.destination_data_id = pp.id and sr.destination_data_type = 1 and pp.archive = 0', 'left');
        $this->db->where("o.parent_org", $org_id);
        if(isset($is_site)) {
            $this->db->where("o.is_site", $is_site);
        }
        $this->db->where("o.archive", 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));
        $result = $this->db->get()->result_array();
    
        for($i=0;$i<count($result);$i++) {
             // get org email
             $result[$i]['primary_email'] =  $result[$i]['secondary_email'] = '';
             $result[$i]['is_secondary_email'] = 0;
            $org_email_data = $this->get_organisation_email($org_id);
        /*    if(!empty($org_email_data)){
            $filter_pe = array_filter($org_email_data, function($ph) {
                return $ph['primary_email'];
            });
            $org->primary_email = count($filter_pe)? array_values($filter_pe)[0]['email'] : '';
            $filter_se = array_filter($org_email_data, function($ph) {
                return !$ph['primary_email'];
            });
            $$result[$i]['secondary_email'] = count($filter_se)? array_values($filter_se)[0]['email'] : '';
            if ($result[$i]['secondary_email']) {
                $result[$i]['is_secondary_email']=1;
            }
        }
 */
        //get org primary email
         $org_primary_email = $this->get_organisation_email($org_id);
           /*  if(!empty($org_primary_email)){
                $org->primary_email  = $org_primary_email;
            } */
            $result[$i]['service_area_selected_options'] = $this->get_organisation_service_area($result[$i]['id']);
            if(count($result[$i]['service_area_selected_options'])>0)
            {
                $result[$i]['service_area_label']= $result[$i]['service_area_selected_options'][0]['label'];
            }
            else{
                $result[$i]['service_area_label']='';
            }
        }

      
        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        $result = ['status' => true, 'data' => $result, 'count' => $dt_filtered_total];
        return $result;
    }

    /*
     * its use for get id of relation data
     * 
     * @params 
     * type array
     * 
     * return type array
     * return id 
     */

    function get_sales_relation_linked_items($req) {
        $this->db->select(["sr.destination_data_id"]);
        $this->db->from("tbl_sales_relation as sr");
        $this->db->where("destination_data_type", $req["destination_data_type"]);
        $this->db->where("source_data_type", $req["source_data_type"]);
        $this->db->where("source_data_id", $req["source_data_id"]);
        $this->db->where("archive", 0);

        $res = $this->db->get()->result_array();
//        last_query();
        $main_result = [];

        if (!empty($res)) {
            $ids = array_column($res, "destination_data_id");

            if ($req["destination_data_type"] == 1) {
                $this->db->select("(select pp.phone from tbl_person_phone as  pp where pp.person_id = p.id and pp.archive = 0 and pp.primary_phone = 1 limit 1) as phone");
                $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as email");
                $this->db->select(["concat_ws(' ',firstname,lastname) as contact_name", "p.id"]);
                $this->db->from("tbl_person as p");

                $this->db->where_in("p.id", $ids);
                $this->db->where("p.archive", 0);
                $main_result = $this->db->get()->result();
            }

            if ($req["destination_data_type"] == 3) {
                $this->db->select(["topic", "amount", "opportunity_number", "id"]);
                $this->db->from("tbl_opportunity as o");

                $this->db->where_in("o.id", $ids);
                $this->db->where_in("o.archive", 0);
                $main_result = $this->db->get()->result();
            }

            if ($req["destination_data_type"] == 2) {
                $this->db->select(["o.name", "id"]);
                $this->db->select("(CASE 
                    WHEN o.status = 1 THEN 'Active'
                    WHEN o.status = 0 THEN 'Inactive'
                    else ''
                    end) as status");

                $this->db->select("(select phone from tbl_organisation_phone as op where op.organisationId = o.id and op.archive = 0 and op.primary_phone limit 1) as phone");

                $this->db->from("tbl_organisation as o");
                $this->db->where_in("o.id", $ids);
                $this->db->where("o.archive", 0);

                return $this->db->get()->result_array();
            }
        }

        return $main_result;
    }

    /*
     * fetching the reference data of account roles
     */
    public function get_account_roles($account_type) {
        $ref_data_type = null;
        if($account_type == 2) // org/suborg/sites
            $ref_data_type = 'account_role_type_org';
        else if($account_type == 3) // opportunity
            $ref_data_type = 'account_role_type_person';
        else if($account_type == 1) // contacts
            $ref_data_type = 'contact_role_type';
        
        $data = $this->Common_model->get_reference_data_list($ref_data_type);
        return ["status" => true, "data" => $data];
    }

    function get_account_list_names($post_data){
        $this->db->or_like('name', $post_data);
        $this->db->select(array('id', 'name'));
        $query = $this->db->get(TBL_PREFIX . 'organisation');
            //last_query();
        $query->result();
        $rows = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                $rows[] = array('label' => $val->name, 'value' => $val->id);
            }
        }
        return $rows;
    }
    function get_account_contacts($post_data){
        $getParmval = json_decode($post_data);
        $this->db->select(array('destination_data_id'));
        $this->db->from("tbl_sales_relation");
        $this->db->where("destination_data_type", 1);
        $this->db->where("source_data_type", 2);
        $this->db->where("source_data_id",$getParmval->orgId);
        $res = $this->db->get()->result_array();
        $rows = array();
        if (!empty($res)) {
            $ids = array_column($res, "destination_data_id");
            // echo '<pre>';print_r($ids);die;
                $this->db->select(["concat_ws(' ',firstname,lastname) as contact_name", "p.id"]);
                $this->db->from("tbl_person as p");
                $this->db->where_in("p.id", $ids);
                $main_result = $this->db->get()->result();
              
                if (!empty($main_result)) {
                    foreach ($main_result as $val) {
                        $rows[] = array('label' => $val->contact_name, 'value' => $val->id);
                    }
                }      
        }
        return $rows;
      
    }

    /*
     * it is use for get organisation accounts payable phone
     * 
     * @params {int} $org_id
     * 
     * return type string
     * phone
     */

    function get_organisation_payable_phone($org_id) {
        $this->db->select(["op.phone"]);
        $this->db->from("tbl_organisation_accounts_payable_phone as op");
        $this->db->where("op.organisationId", $org_id);
        $this->db->where("op.primary_phone", 1);
        $this->db->where("op.archive", 0);

        return $this->db->get()->row("phone");
    }

    /*
     * it is use for get organisation account payable email
     * 
     * @params {int} $org_id
     * 
     * return type string
     * email
     */

    function get_organisation_payable_email($org_id) {
        $this->db->select(["op.email"]);
        $this->db->from("tbl_organisation_accounts_payable_email as op");
        $this->db->where("op.organisationId", $org_id);
        $this->db->where("op.primary_email", 1);
        $this->db->where("op.archive", 0);

        return $this->db->get()->row("email");
    }

    public function get_organization_source() { 
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'organisation_type' AND rdt.archive = 0", "INNER");
        $this->db->where('r.archive', 0);

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * Get all organisation srvice type value from reference type
     */
    public function get_organization_service_type() { 
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'organisation_service_type' AND rdt.archive = 0", "INNER");
        $this->db->where('r.archive', 0);

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /**
     * fetching the organisation details
     */
    public function get_account_details_for_view($org_id, $uuid_user_type=ADMIN_PORTAL) {
        $det = $this->get_organisation_details($org_id);
        $suborgs_res = $this->get_sub_organisation_details($org_id,'' , '',$uuid_user_type);
        $det->sub_organisation = $suborgs_res['data'];

        $par_status =  $det->status == 1? "Active" : "InActive";
        $par_org_datas = [];
        $par_sub_org_datas = [];
        if(!empty($det->parent_org)){
        $par_id = $det->parent_org["value"];
        $par_phone = $det->parent_org["phone"] ? $det->parent_org["phone"]  : "N/A";
        $par_org_datas[] = array("name" => $det->parent_org ? $det->parent_org["label"] : "N/A","status"=>$par_status,"phone" =>$par_phone,"id"=>$par_id);
        }
        if($det->sub_organisation){
            $par_sub_org_datas = $det->sub_organisation;
        }
        $child_par_organisation = array_slice(array_merge($par_sub_org_datas,$par_org_datas), 0, 3);
        $det->child_par_organisation= $child_par_organisation;
        if ($det->archive == 1) {
            $response = ['status' => false, 'data' => "Organisation is deleted"];
        } else {
            $req = ["source_data_type" => 2, "destination_data_type" => 1, "source_data_id" => $det->id];
            $det->contacts = $this->get_sales_relation_linked_items($req);

            // populate contact with contact roles information
            $SOURCE_DATA_TYPE_ORG = 2;
            $this->load->model('sales/Contact_model');
            $det->account_contact_roles = $this->Contact_model->get_account_contact_roles_by_source_id($det->id, $SOURCE_DATA_TYPE_ORG);
            $det->contacts = $this->Contact_model->merge_account_contact_roles_with_contact(
                obj_to_arr($det->contacts), 
                $det->account_contact_roles, 
                $SOURCE_DATA_TYPE_ORG
            );

            $response = ['status' => true, 'data' => $det ];
        }
        return $response;
    }

    /**
     * adding the log of actions
     */
    public function add_organisation_log($data, $title, $id, $adminId) {
        $this->loges->setCreatedBy($adminId);
        $this->load->library('UserName');
        $adminName = $this->username->getName('admin', $adminId);

        # create log setter getter
        $this->loges->setTitle($title);
        $this->loges->setDescription(json_encode($data));
        $this->loges->setUserId($id);
        $this->loges->setCreatedBy($adminId);
        $this->loges->createLog();
    }

    /**
     * fetching existing account contacts
     */
    public function get_account_contact_ids($account_id, $account_type, $key_pair = false) {
        $this->db->select(["sr.id"]);
        $this->db->from("tbl_sales_relation as sr");
        $this->db->join('tbl_person as p', 'p.id = sr.destination_data_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = sr.roll_id', 'inner');

        $this->db->where("sr.archive", 0);
        $this->db->where("p.archive", 0);
        $this->db->where("r.archive", 0);

        $this->db->where("sr.destination_data_type", 1);
        $this->db->where("sr.source_data_type", $account_type);
        $this->db->where("sr.source_data_id", $account_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
                if($key_pair == false)
                $ids[] = $row->id;
                else
                $ids[$row->key_name] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * adding updating account contacts
     */
    public function save_account_contact_roles($data, $adminId) {

        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        # manually validating data
        if(empty($data['account_id']))
            $errors[] = "Account/organisation id is missing";
        if(empty($data['account_type']))
            $errors[] = "Account/organisation type is missing";

        $type = null;
        if($data['account_type'] == 2) {
            # does the organisation exist?
            $result = $this->get_organisation_details($data['account_id']);
            if (empty($result)) {
                $response = ['status' => false, 'error' => "Organisation does not exist anymore."];
                return $response;
            }
            $type = "organisation";
            if($result->is_site == 1)
                $type = "site";
        }
        else if($data['account_type'] == 3) {
            $type = "opportunity";
        }

        $cnt = $checkcnt = $found_primary = 0;
        $errors = $duplrows = null;
        if(isset($data['account_contacts']) && count($data['account_contacts']) > 0) {
            foreach ($data['account_contacts'] as $row) {
                $valid = true;
                if(empty($row['contact']) || empty($row['contact']['value'])) {
                    $errors[] = "Please provide the contact for row-".($cnt+1);
                }
                else {
                    $checkcnt = 0;
                    foreach ($data['account_contacts'] as $checkrow) {
                        if(!empty($checkrow['contact']) && !empty($checkrow['contact']['value']) && $row['contact']['value'] == $checkrow['contact']['value'] && $cnt != $checkcnt && !isset($duplrows[$row['contact']['value']])) {
                            $errors[] = "Duplicate contact ".$row['contact']['label'];
                            $duplrows[$row['contact']['value']] = 1;
                        }
                        $checkcnt++;
                    }
                }
                if(empty($row['role_id'])) {
                    $errors[] = "Please provide the contact role for row-".($cnt+1);
                }
                if(!empty($row['is_primary']) && $row['is_primary'] == 1) {
                    $found_primary = 1;
                }
                $cnt++;
            }
            if(!$found_primary) {
                $errors[] = "Please provide one primary contact";
            }
        }

        if($errors) {
            $response = ['status' => false, 'error' => implode(', ', $errors)];
            return $response;
        }
        $existing_account_contact_ids = [];
        $selected_account_contact_ids = [];

        # fetching existing account contacts
        $existing_account_contact_ids = $this->get_account_contact_ids($data['account_id'], $data['account_type']);

        foreach($data['account_contacts'] as $row) {

            $postdata = [
                'roll_id' => $row['role_id'],
                'can_book' => null,
                'source_data_id' => $data['account_id'],
                'source_data_type' => $data['account_type'], // org/suborg/site
                'destination_data_id' => $row['contact']['value'],
                'destination_data_type' => 1, //contact
                "is_primary" => $row['is_primary']
            ];

            # adding/updating an entry of account contact
            if(!isset($row['id']) || empty($row['id'])) {
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("sales_relation", $postdata);
                //save reverse relation
                $rpostdata = [
                    'roll_id' => $row['role_id'],
                    'can_book' => null,
                    'source_data_id' => $row['contact']['value'],
                    'source_data_type' => 1,
                    'destination_data_id' => $data['account_id'],
                    'destination_data_type' => $data['account_type'],
                    "is_primary" => $row['is_primary']
                ];
                $this->basic_model->insert_records("sales_relation", $rpostdata);
            }
            else {
                $id = $row['id'];
                $selected_account_contact_ids[] = $id;

                $postdata['updated'] = DATE_TIME;
                $postdata['updated_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->update_records("sales_relation", $postdata, ["id" => $id]);
                //update reverse relation
                $rpostdata = [
                    'roll_id' => $row['role_id'],
                    'can_book' => null,
                    'source_data_id' => $row['contact']['value'],
                    'source_data_type' => 1,
                    'destination_data_id' => $data['account_id'],
                    'destination_data_type' => $data['account_type'],
                    "is_primary" => $row['is_primary']
                ];
                $this->basic_model->update_records("sales_relation", $rpostdata, ["id" => $id]);
            }
        }

        # any existing account contacts that are not selected this time
        # let's remove them
        $tobe_removed = array_diff($existing_account_contact_ids, $selected_account_contact_ids);
        if($tobe_removed) {
            foreach($tobe_removed as $rem_id) {
                $this->archive_account_contact(["id" => $rem_id], $adminId);
            }
        }
        
        # adding a log entry
        $logtitle = sprintf("Successfully updated {$type} contacts with ID of %s", $data['account_id']);
        $this->add_organisation_log($data, $logtitle, $data['account_id'], $adminId);

        $msg = "Successfully updated {$type} contacts";
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
     * getting organisation sites list for selection
     */
    function get_account_sites_selection($data) {
        $account_id = !empty($data['account_id']) ? $data['account_id'] : null;
        $this->db->select(["o.name as label", "o.id as value"]);
        $this->db->from("tbl_organisation as o");
        $this->db->join('tbl_member_role as r', 'o.role_id = r.id', 'left');
        $this->db->join('tbl_organisation_address as oa', 'oa.organisationId = o.id and oa.address_type = 2 and oa.archive = 0 and oa.primary_address = 1', 'left');
        $this->db->join('tbl_sales_relation as sr', 'sr.source_data_id = o.id and sr.source_data_type = 2 and sr.archive = 0 and sr.is_primary = 1', 'left');
        $this->db->join('tbl_person as pp', 'sr.destination_data_id = pp.id and sr.destination_data_type = 1 and pp.archive = 0', 'left');
        $this->db->where("o.parent_org", $account_id);
        $this->db->where("o.is_site", 1);
        $this->db->where("o.archive", 0);
        $result = $this->db->get()->result_array();
        // last_query();
        return ['status' => true, 'data' => $result];
    }

    /**
     * getting organisation contacts list for selection
     */
    function get_account_contacts_selection($data) {
        $account_id = !empty($data['account_id']) ? $data['account_id'] : null;
        $account_type = !empty($data['account_type']) ? $data['account_type'] : null;

        if($account_type == 1) {
            $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label", "p.id as value", "1 as is_primary"]);
            $this->db->from("tbl_participants_master as pm");
            $this->db->join('tbl_person as p', 'p.id = pm.contact_id', 'inner');
            $this->db->where("pm.archive", 0);
            $this->db->where("p.archive", 0);
            $this->db->where("pm.id", $account_id);
            $result = $this->db->get()->result_array();
        }
        else {
            $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label", "p.id as value", "sr.is_primary"]);
            $this->db->from("tbl_sales_relation as sr");
            $this->db->join('tbl_person as p', 'p.id = sr.destination_data_id', 'inner');
            $this->db->join('tbl_references as r', 'r.id = sr.roll_id', 'inner');
            $this->db->where("sr.archive", 0);
            $this->db->where("p.archive", 0);
            $this->db->where("r.archive", 0);
            $this->db->where("sr.destination_data_type", 1);
            $this->db->where("sr.source_data_type", $account_type);
            $this->db->where("sr.source_data_id", $account_id);
            $result = $this->db->get()->result_array();
        }
        $primary_id = null;
        if($result) {
            foreach($result as $row) {
                if(!$primary_id)
                    $primary_id = ($row['is_primary'] == 1) ? $row['value'] : '';
            }
        }
        return ['status' => true, 'data' => $result, 'contact_id' => $primary_id];
    }

    /**
     * Getting all organisation contacts
     */
    function get_account_contacts_list($reqData) {

        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        $account_id = !empty($reqData->id) ? $reqData->id : null;
        $account_type = !empty($reqData->account_type) ? $reqData->account_type : null;

        # Searching column
        $src_columns = array("concat_ws(' ',p.firstname,p.lastname)", "DATE_FORMAT(SR.created,'%d/%m/%Y')", "DATE_FORMAT(SR.updated,'%d/%m/%Y')", "(CASE WHEN sr.is_primary = 1 THEN 'yes' WHEN sr.is_primary = 0 THEN 'no' else '' end)");

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

        # sorting part
        $available_column = ["id", "role_id", "role_name", "contact_name", "person_id", "is_primary", "created", "updated"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'sr.created';
            $direction = 'DESC';
        }

        $select_column = ["sr.id, sr.roll_id as role_id, r.display_name as role_name, concat_ws(' ',p.firstname,p.lastname) as contact_name, p.id as person_id, sr.is_primary, sr.created, sr.updated"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(select pe.email from tbl_person_email as  pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as email");
        $this->db->select("(select pp.phone from tbl_person_phone as  pp where pp.person_id = p.id and pp.archive = 0 and pp.primary_phone = 1 limit 1) as phone");
        $this->db->select("(CASE
            WHEN u.status = 1 THEN 'Active'
              else ''
              end
            ) as org_status
        ");
        $this->db->from("tbl_sales_relation as sr");
        $this->db->join('tbl_person as p', 'p.id = sr.destination_data_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = sr.roll_id', 'inner');
        $this->db->join('tbl_users as u', 'u.id = p.uuid', 'left');
        
        $this->db->where("sr.archive", 0);
        $this->db->where("p.archive", 0);
        $this->db->where("r.archive", 0);

        $this->db->where("sr.destination_data_type", 1);
        $this->db->where("sr.source_data_type", $account_type);
        $this->db->where("sr.source_data_id", $account_id);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $res = $this->db->get()->result_array();
        // Get total rows count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        $main_result = [];

        if (!empty($res)) {
            foreach($res as $row) {
                $role['label'] = $row['role_name'];
                $role['value'] = $row['role_id'];
                $row['role'] = $role;

                $contact['label'] = $row['contact_name'];
                $contact['value'] = $row['person_id'];
                $row['contact'] = $contact;
                $main_result[] = $row;
            }
        }
        $result = ['status' => true, 'count' => $dt_filtered_total, 'data' => $main_result];
        return $result;
    }


    /** 
     * Get all child oraganisation with sub level 
     * @params {int} $org_id
     * 
     * @return type array result 
     */
    function get_account_child_list($org_id) {

        $this->db->select(["*"]);
        $this->db->select("(CASE  
            WHEN o.status = 1 THEN 'Active'
            WHEN o.status = 0 THEN 'Inactive'
            Else '' end
        ) as status");
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.archive", 0);
        $this->db->order_by("o.id", 'ASC');
        $result = $this->db->get()->result_array();

        /**
         * Form the result as tree format
         */
        $tree = [];
        foreach($result as $id => $row) {
            // Allow if parent_org value not equal to currently selected org and id equal to currently selected org
            if ($row['id'] == $org_id && $row['parent_org'] != $org_id) {
                // get count of child
                $child_count = $this->db->query('
                        SELECT count(*) AS count FROM tbl_organisation WHERE parent_org = '.$row['id'].'
                     AND archive = 0')->row()->count;
                $parent_id = $row['id'];
                $current_id = $row['id'];
                $child_count = $child_count > 1 ? $child_count - 1 : $child_count;
                $tree_child = $this->buildTreeV2($result, $row['id'], ($child_count), $parent_id, $current_id);
                // Add current org as parent
                $row['parent_org'] = 0;
                $tree[0] = $row;
                $tree[0]['_children'] = $tree_child ?? [];
            } 

            if ($row['id'] == $org_id && $row['parent_org'] == $org_id) {
                // get count of child
                $child_count = $this->db->query('
                        SELECT count(*) AS count FROM tbl_organisation WHERE parent_org = '.$row['id'].'
                     AND archive = 0')->row()->count;
                $parent_id = $row['id'];
                $current_id = $row['id'];
                $child_count = $child_count > 1 ? $child_count - 1 : $child_count;
                // Overwrite the parent_org value as 0 bcz the selected org is same
                $result[$id]['parent_org'] = 0;
                $tree_child = $this->buildTreeV2($result, $row['id'], ($child_count), $parent_id, $current_id);
                // Add current org as parent
                $row['parent_org'] = 0;
                $tree[0] = $row;
                $tree[0]['_children'] = $tree_child ?? [];
            }
        }

        /**
         * Convert the array tree format into flat array
         */
        $output = [];
        $treeFormat = $this->printTree($tree, $output);
        $treeFormat = array_values($treeFormat);
        return $treeFormat;
    }    

    /**
     * Conversion tree format to flat array
     * @param {array} $arr 
     */
    function flatten($arr) {
        $result = [];
        foreach($arr as $item) {
            if (isset($item['_children']))
                $result = array_merge($result, $this->flatten($item['_children']));
            unset($item['_children']);
            $result[] = $item;  
        }
        return $result;
    }

    /**
     * Form array with nested children depth
     */
    function buildTreeV1($org_id, Array $data, $parent = 0, $spacing = '', $tree = [], $r = 0) {
        foreach ($data as $d) {
            if ($d['parent_org'] == $parent) {
                $dash = ($d['parent_org'] == 0) ? '' : str_repeat('', $r) .' ';
                $name = $dash.$d['name'];
                $tree[$d['id']] = $d;
                $tree[$d['id']]['name'] = $name;
                $tree[$d['id']]['space'] = $dash;
                $tree[$d['id']]['space_depth'] = $r;
                if ($d['parent_org'] == $parent) {
                    // reset $r
                    $r = 0;
                }
                $tree = $this->buildTreeV1($org_id, $data, $d['id'], '&nbsp;&nbsp;', $tree, ++$r);
            }
        }
        return $tree;
    }

    /**
     * Build flat array format into tree format
     * like 
        Array (
            id => 1,
            name => 'test'
            _children => Array (
                [0] =>
                    Array (
                        // data
                    )
                )
        )
     * return tree format array
     */
    function buildTreeV2(Array $data, $parent = 0, $child_count, $org_parent_id, $current_id, $org_child = 0) {
        $tree = array();
        foreach ($data as $d) {
            if ($org_parent_id == $d['parent_org'] && $current_id == $d['id']) {
                $org_child++;                
            }

            if ($org_child > $child_count) {
                // continue;
            }

            if ($d['parent_org'] == $parent) {
                $children = $this->buildTree($data, $d['id'], $child_count, $org_parent_id, $current_id, $org_child);
                // set a trivial key
                if (!empty($children)) {
                    $d['_children'] = $children;
                }
                $tree[] = $d;
            }
        }
        return $tree;
    }

    function buildTree(Array $data, $parent = 0) {
        $tree = array();
        foreach ($data as $d) {
            if ($d['parent_org'] == $parent) {
                $children = $this->buildTree($data, $d['id']);
                // set a trivial key
                if (!empty($children)) {
                    $d['_children'] = $children;
                }
                $tree[] = $d;
            }
        }
        return $tree;
    }

    /**
     * Conversion tree format to flat array
     * @param {array} $arr 
     */
    function printTree($tree, $output, $r = 0, $p = null) {
        $result = [];
        foreach ($tree as $i => $t) {
            $dash = ($t['parent_org'] == 0) ? '' : str_repeat('', $r) .' ';
            $name = $dash.$t['name'];
            // printf("\t<option value='%d'>%s%s</option>\n", $t['id'], $dash, $name);
            $output[$t['id']] = $t;
            $output[$t['id']]['name'] = $name;
            $output[$t['id']]['space'] = $dash;
            $output[$t['id']]['space_depth'] = $r;
            if ($t['parent_org'] == $p) {
                // reset $r
                // $r = 0;
            }
            if (isset($t['_children'])) {
                $output = $this->printTree($t['_children'], $output, $r+1, $t['parent_org']);
            }
        }
        return $output;
    }

    function get_organisation_service_area($org_id) {
        $this->db->select(['fsa.id as value', 'fsa.title as label']);
        $this->db->from('tbl_organisation_service_area as osa');
        $this->db->join('tbl_finance_service_area as fsa', 'osa.service_area_id = fsa.id', 'left');
        $this->db->where(["osa.organisation_id" => $org_id, 'osa.archive' => 0]);
        $res = $this->db->get()->result_array();
        return $res;
    }

    function get_mapped_sa_and_swa() {
        $select_column = array( 'sam.service_area_id AS service_area', 
        'swa_id AS support_worker_area',
        'fsa.title AS service_area_label','fwas.title AS support_worker_area_label');
        
        $this->db->select($select_column);
        $this->db->distinct();
        $this->db->from('tbl_finance_service_area_swa_mapping as sam');
        $this->db->join('tbl_finance_service_area as fsa', ' fsa.id=sam.service_area_id', 'inner');
        $this->db->join('tbl_finance_support_worker_area as fwas', 'fwas.id=sam.swa_id', 'inner');
        $this->db->where(["sam.archive" => 0, 'fsa.archive' => 0,'fwas.archive' => 0]);
        $res = $this->db->get()->result_array();
        return $res;
    }

    function insert_update_preferred_swa($orgId,$swa_id,$adminId){
        // fetch existing data
        $org_swa = $this->get_row('organisation_preferred_support_worker_area', ['id'], ['organisation_id' => $orgId]);
        $id = !empty($org_swa)? $org_swa->id : 0;
        if ($id) {
            $org_data["updated_at"] = DATE_TIME;
            $org_data["updated_by"] = $adminId;
            $org_data["swa_id"] = $swa_id;
            $org_data["organisation_id"] = $orgId;
            $this->basic_model->update_records("organisation_preferred_support_worker_area", $org_data, ["organisation_id" => $orgId]);
            return $id;
        } else {
            $org_data["created_at"] = DATE_TIME;
            $org_data["created_by"] = $adminId;
            $org_data["swa_id"] = $swa_id;
            $org_data["organisation_id"] =$orgId;
            return $this->basic_model->insert_records("organisation_preferred_support_worker_area", $org_data, $multiple = FALSE);
        }
    }

    function get_organisation_swa($org_id) {
        $this->db->select(['fsa.id as value', 'fsa.title as label']);
        $this->db->from('tbl_organisation_preferred_support_worker_area as osa');
        $this->db->join('tbl_finance_support_worker_area as fsa', 'osa.swa_id = fsa.id', 'left');
        $this->db->where(["osa.organisation_id" => $org_id, 'osa.archive' => 0]);
        $res = $this->db->get()->result_array();
        return $res;
    }
    
    /*
     * its use for insert/update organisation data
     * return type $orgId
     */

    function save_billing_info($data, $adminId) {
        $updated = false;
        $data = (array) $data;
        $org_id = $data['org_id'] ?? 0;
        if (!empty($org_id)) {
            $billing_data = [
                "gst" => $data["gst"] ?? '',
                "payroll_tax" => $data["payroll_tax"] ?? 0,
                "communication_mode" => $data["communication_mode"] ?? 0,
                "site_discount" => $data["site_discount"] ?? 0,
                "billing_same_as_parent" => $data["billing_same_as_parent"] ?? 0,
                "updated_by" => $adminId,
                "updated" => date('Y-m-d H:i:s')
            ];
            if (!empty($data['billing_same_as_parent'])) {
                $billing_data = [
                    "gst" => '',
                    "payroll_tax" => 0,
                    "communication_mode" => 0,
                    "site_discount" => 0,
                    "billing_same_as_parent" => $data["billing_same_as_parent"],
                    "updated_by" => $adminId,
                    "updated" => date('Y-m-d H:i:s')
                ];
            }
            $updated = $this->basic_model->update_records("organisation", $billing_data, ["id" => $org_id]);
            // update additional billing info
            if ($updated) {
                $addi_billing_info = [
                    "orgnisation_id" => $org_id,
                    "invoice_type" => $data['ab_invoice_type']?? '',
                    "invoice_batch" => $data['ab_invoice_batch']?? '',
                    "cost_code" => $data['ab_cost_code']?? 0,
                    "cost_book_id" => $data['ab_cost_book']?? null,
                    "site_discount" => !empty($data['ab_site_discount'])? 1 : 0,
                    "confirm_billing_info" => !empty($data['confirm_bi'])? 1 : 0,
                    "updated_by" => $adminId,
                    "updated_at" => date('Y-m-d H:i:s'),
                    "archive" => 0
                ];
                $existing = $this->get_row('organisation_additional_billing_info', ['id'], ['orgnisation_id' => $org_id]);
                if (empty($existing)) {
                    $addi_billing_info['created_by '] = $adminId;
                    $addi_billing_info['created_at '] = date('Y-m-d H:i:s');
                    $this->insert_records("organisation_additional_billing_info", $addi_billing_info);
                } else {
                    $this->update_records("organisation_additional_billing_info", $addi_billing_info, ["orgnisation_id" => $org_id]);
                }
            }
        }
        return $updated;
    }

    /*
     * its use for get organisation billing info
     * 
     * @params  $org_id
     * 
     * return type string
     * phone
     */

    function get_organisation_billing($org_id, $result = [], $recursion = false) {
        $this->db->select(['o.id', 'o.gst', 'o.payroll_tax', 'o.communication_mode', 'o.site_discount', 'o.parent_org', 'o.billing_same_as_parent']);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.id", $org_id);
        $this->db->where("o.archive", 0);
        $res = $this->db->get()->result();
        $org = !empty($res)? $res[0] : null;
        if (!empty($org)) {
            $result['org_billing'] = $org;
            if (empty($recursion)) {
                $parent_id = $org->parent_org;
                $osa = $this->get_organisation_service_area($org_id);
                $result['org_sa'] = $osa;
                $oswa = $this->get_organisation_swa($org_id); 
                $result['org_swa'] = $oswa;
                if (!empty($parent_id)) {
                    $posa = $this->get_organisation_service_area($parent_id);
                    $result['parent_org_sa'] = $posa;   
                    $osa = $this->get_organisation_service_area($org_id);
                    $result['org_sa'] = $osa;             
                }
            }
            
            if ($org->billing_same_as_parent == 1) {
                $result = $this->get_organisation_billing($org->parent_org, $result, true);
            }
        }
        return $result;
    }

    function get_sa_swa_cost_codes($sa_id, $swa_id) {
        $this->db->select(['sa_swa.cost_code_id as value', 'cc.title as label']);
        $this->db->from('tbl_finance_service_area_swa_mapping as sa_swa');
        $this->db->join('tbl_finance_cost_code as cc', 'sa_swa.cost_code_id = cc.id', 'left');
        $this->db->where(["sa_swa.service_area_id" => $sa_id, "sa_swa.swa_id" => $swa_id]);
        $res = $this->db->get()->result_array();
        return $res;
    }

    function get_organisation_additional_billing($org_id) {
        $this->db->select(['ab.invoice_type as ab_invoice_type', 'ab.invoice_batch as ab_invoice_batch', 'ab.site_discount as ab_site_discount', 'ab.confirm_billing_info as confirm_bi','ab.cost_code as ab_cost_code','trc.display_name as ab_cost_book_label', 'ab.cost_book_id as ab_cost_book_id']);
        $this->db->from("tbl_organisation_additional_billing_info as ab");
        $this->db->where("ab.orgnisation_id", $org_id);
        $this->db->join('tbl_references as trc', 'trc.id = ab.cost_book_id', 'left');
        $this->db->where("ab.archive", 0);
        $q = $this->db->get();
        $s = $this->db->last_query();
        return $q->result_array();
    }
    
    /**
     * Get the cost book with mapping
     * @param {array} $data
     */
    public function get_cost_book_options($data) {
        $org_id = $data->org_id ?? '';
        $cost_code_id = $data->cost_code ?? '';
        $service_area_id = $data->service_area ?? '';
        $site_discount = $data->site_discount ?? '0';
        $payroll_tax = 0; // false
        
        # Get organisation details 
        if (!empty($org_id)) {
            $org = $this->get_organisation_payroll_tax($org_id);
            if (!empty($org)) {
                $parent_org_id =  $org->parent_org;
                # Get parent organisation details 
                $parent_org = $this->get_organisation_payroll_tax($parent_org_id);
                if (!empty($parent_org) && $parent_org->payroll_tax == 1) {
                    $payroll_tax = 1; // true
                }
            }
        }        

        $costBookMapping = [];
        $cost_book_key = [];
        if (!empty($cost_code_id) && !empty($service_area_id)) {
            # Get cost book by mapping
            $this->db->select(["fcbm.id", "fcbm.cost_code_id", "fcbm.service_area_id", "fcbm.payroll_tax", "fcbm.site_discount", "fcbm.cost_book_key_name"]);
            $this->db->from("tbl_finance_cost_book_mapping as fcbm");
            $this->db->where("fcbm.cost_code_id", $cost_code_id);
            $this->db->where("fcbm.service_area_id", $service_area_id);
            $this->db->where("fcbm.archive", 0);
            $costBookMapping = $this->db->get()->result_array();
        }

        foreach($costBookMapping as $cbm_key => $cost_book) {
            $valid = false;
            # validate payroll tax and site discounst
            if ( ($cost_book['payroll_tax'] == '' && $cost_book['site_discount'] == '') || ($cost_book['payroll_tax'] != '' && $cost_book['payroll_tax'] == $payroll_tax && $site_discount != 1) || ($cost_book['site_discount'] != '' && $cost_book['site_discount'] == $site_discount)) {
                $valid = true;
            }

            # if valid true then add key_name
            if ($valid) {
                $cost_book_key[] = $cost_book['cost_book_key_name'];
            }
        }

        $cost_book = [];
        # Get cost book options by key_name
        if (!empty($cost_book_key)) {
            $cost_book = $this->get_cost_book_options_by_key_name($cost_book_key);
        }
        
        return $cost_book;
    }

    /**
     * Get Cost book by key name by reference
     * @param {array} $cost_book_key
     */
    function get_cost_book_options_by_key_name($cost_book_key) {
        $this->db->select(["r.id as value", "r.display_name as label", "r.key_name" ]);
        $this->db->from("tbl_references as r");
        $this->db->where_in("r.key_name", $cost_book_key);
        return $this->db->get()->result_array();
    }

    /**
     * Get payroll tax value by organisation id
     * @param {int} org_id
     */
    public function get_organisation_payroll_tax($org_id) {
        $this->db->select(["o.id", "o.parent_org", "o.payroll_tax" ]);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.id", $org_id);
        return $this->db->get()->row();
    }
}
