<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * class: Contact_model
 * use for query operation of contact
 */

//class Master extends MX_Controller
class Contact_model extends Basic_Model {

    public $object_fields = [
        'contact_code' => 'Contact Code',
        'firstname' => 'First Name',
        'lastname' => 'Last Name',
        'status' => 'Status',
        'gender' => 'Gender',
        'created' => 'Created Date',
        'updated' => 'Last Updated Date',
        'preferred_name' => 'Preferred Name',
        'date_of_birth' => 'Date of Birth',
        'person_account' => 'Person Account?',
        'contact_is_account' => 'Contact is Account?'
    ];

    function __construct() {
        parent::__construct();
        $this->load->model('sales/Person_email_model');
        $this->table_name = 'person';
        $this->object_fields['email'] = function($personId = '') {
                                                                    if (empty($personId)) {
                                                                        return 'Email';
                                                                    }
                                                                    $result = $this->get_record_where('person_email','email', ['person_id' => $personId, 'archive' => '0']);
                                                                    return $result[0]->email;
                                                                };
    }

    /*
     * its use for get source option
     * return type Array object
     *
     */

    public function get_source_option() {
        $this->db->select(["lsc.name as label", 'lsc.id as value']);
        $this->db->from(TBL_PREFIX . 'lead_source_code as lsc');
        $this->db->where(['lsc.archive' => 0]);
        $this->db->order_by('lsc.order_ref', 'ASC');
        $query = $this->db->get();

        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get option of contact type
     * return type Array object
     */

    public function get_contact_type_option() {
        $this->db->select(["ct.name as label", 'ct.id as value']);
        $this->db->from(TBL_PREFIX . 'person_type as ct');
        $this->db->where(['ct.archive' => 0]);
        $this->db->order_by('ct.id', 'ASC');
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for create contact
     *
     * @@contact and person both are same@@
     *
     * return contactId / personId
     */

    public function create_update_contact($data, $adminId) {
        // include peson class for create person of contact
        require_once APPPATH . 'Classes/person/person.php';
        $objPerson = new PersonClass\Person();

        // setter getter of person date
        $objPerson->setFirstName(trim($data['firstname'] ?? ''));
        $objPerson->setUsername(isset($data['username']) ? trim($data['username']) : '');
        $objPerson->setPassword(isset($data['password']) ? trim($data['password']) : '');
        $objPerson->setLastName(trim($data['lastname'] ?? ''));
        $objPerson->setPersonType(isset($data['contact_type']) ? trim($data['contact_type']) : '');
        $objPerson->setPerson_source(trim($data['contact_source'] ?? null));
        $objPerson->setDateOfBirth(trim($data['date_of_birth'] ?? ''));
        $objPerson->setReligion(trim($data['religion'] ?? ''));
        $objPerson->setCulturalPractices(trim($data['cultural_practices'] ?? ''));
        $objPerson->setAboriginal(trim($data['aboriginal'] ?? ''));
        $objPerson->setCommunicationMethod(trim($data['communication_method'] ?? ''));
        $objPerson->setCreated_by($adminId);
        $objPerson->setStatus($data['status']);
        $objPerson->setNdisNumber(trim($data['ndis_number'] ?? '')); // all whitespaces of NDIS number will be trimmed internally
        $objPerson->setGender(trim($data['gender'] ?? ''));
        $objPerson->setInterpreter(trim($data['interpreter'] ?? 0));
        $objPerson->setAvatar(trim($data['avatar'] ?? ''));
        $objPerson->setMiddleName(trim($data['middlename'] ?? ''));
        $objPerson->setPreviousName(trim($data['previous_name'] ?? ''));
        if (!empty($data["contactId"])) {
            $objPerson->setPersonId($data["contactId"]);

            # need to check if the person is associated with applicant or not
            # if he is, we need to make email change also reflected in the username
            if ($data['contact_type'] == 1 && !empty($data['EmailInput']))
            $objPerson->setUsername(trim($data['EmailInput'][0]->email));

            // its deprecated
            // restore previous NDIS number if contact is active
            // Based on HCM-1567, contacts with status of active should not be able to update NDIS numbers
            //HCM-1928 as per rule NDIS number can be updated by admin
            //$STATUS_ACTIVE = 1;
            //if ($objPerson->getStatus() == $STATUS_ACTIVE) {
            //    $existingPerson = $this->db->get_where('tbl_person', ['id' => $data['contactId']])->row_array();
            //    if (!empty($existingPerson)) {
            //        $objPerson->setNdisNumber($existingPerson['ndis_number']);
            //    }
            //}

            // update contact/ person
            $objPerson->updatePerson();
            $personId = $data["contactId"];

            // update the email in member table for to create the member
            if($data['EmailInput'][0]->email){
                $where = array('person_id' => $personId, "enable_app_access" => 1,"source_type" => 1,"is_new_member" => 1,'archive' => 0);
                $member = $this->basic_model->get_row('member', ['id','uuid'], $where);
                if(!empty($member)){
                    $this->basic_model->update_records('member', ['username' => trim($data['EmailInput'][0]->email)], ['id' => $member->id, 'person_id' => $personId, 'archive' => 0]);
                    if(!empty($member->uuid)){
                        $this->basic_model->update_records('users', ['username' => trim($data['EmailInput'][0]->email)], ['id' => $member->uuid, 'user_type' => MEMBER_PORTAL]);
                    }
                }
            }
            

        } else {
            
            // create person (contact )
            $personId = $objPerson->createPerson();

            #Add contact role relation ship
            $this->save_relation_contact_as_agent($data, $personId, $adminId);
        }

        // create email of person (contact )
        if (!empty($data['EmailInput'])) {
            $objPerson->setPersonEmail($data['EmailInput']);
            $objPerson->insertEmail('email');
        }

        // create phone of person (contact )
        if (!empty($data['PhoneInput'])) {
            $objPerson->setPersonPhone($data['PhoneInput']);
            $objPerson->insertPhone('phone');
        }

        $addr = [];
         //update address if the person exist in applicant
        $where = array('person_id' => $data["contactId"],'archive' => 0);
        $applicant = $this->basic_model->get_row('recruitment_applicant', ['id'], $where);
        if(!$data['is_manual_address']){
                if (!empty($data['address'])) {
                    $address = devide_google_or_manual_address($data['address']);
                    $addr[] = [
                        'unit_number' => $data['unit_number'] ?? '',
                        'street' => $address['street'] ?? '',
                        'state' => !empty($address["state"]) ? $address["state"] : null,
                        'suburb' => $address['suburb'] ?? '',
                        'postcode' => $address['postcode'] ?? '',
                        'is_manual_address' => $data['is_manual_address'],
                        'manual_address' => NULL
                    ];
                        if(!empty($applicant) && !empty($address)){
                            $applicant_address = [
                                'unit_number' => $data['unit_number'] ?? '',
                                'street' => $address["street"] ?? '',
                                'city' => $address["suburb"] ?? '',
                                'postal' => $address["postcode"] ?? '',
                                'state' => $address["state"] ?? '',                        
                                'applicant_id' => $applicant->id,
                                'is_manual_address' => $data['is_manual_address'],
                                'manual_address' => NULL
                            ];
                            $this->basic_model->update_records('recruitment_applicant_address', $applicant_address, ['applicant_id' => $applicant->id]);
                        }
                }
                // create / delete of person (contact )
                    $objPerson->setPersonAddress($addr);
                    $objPerson->insertAddress();
            }else{     
                $this->add_manual_address_in_person_address($personId, $data);                   

                // update applicant address table
                if(!empty($applicant)){
                    $this->basic_model->update_records('recruitment_applicant_address', $manual_address, ['applicant_id' => $applicant->id]);
                }
                   
            }
        if(!empty($applicant)){
            $applicant_details = [
                'firstname' => trim($data['firstname'] ?? ''),
                'lastname' => trim($data["lastname"] ?? ''),
                'dob' => trim($data["date_of_birth"] ?? ''),
                'gender' => trim($data["gender"] ?? ''),
                'previous_name' => trim($data["previous_name"] ?? ''), 
                'middlename' => trim($data["middlename"] ?? '')                   
            ];
            $this->basic_model->update_records('recruitment_applicant', $applicant_details,  $where);
            if(!empty($data['PhoneInput']))
            {
                $applicant_details = ['phone' => $data['PhoneInput'][0]->phone ?? ''];
                $this->basic_model->update_records('recruitment_applicant_phone', $applicant_details, ['applicant_id' => $applicant->id]); 
            }
            if(!empty($data['EmailInput']))
            {
                $applicant_details = ['email' => $data['EmailInput'][0]->email ?? ''];
                $this->basic_model->update_records('recruitment_applicant_email', $applicant_details, ['applicant_id' => $applicant->id]); 
            }
        }
        

        

        return $personId;
    }

    /*
     * its use for update contact against member details 
     * its not relating to applicant
     * API used in member portal
     * 
     * @@contact and person both are same@@
     * 
     * return contactId / personId
     */

    public function update_contact_for_member_portal($data) {
        // include peson class for create person of contact
        require_once APPPATH . 'Classes/person/person.php';
        $objPerson = new PersonClass\Person();
        if (!empty($data["person_id"])) {
            $objPerson->setPersonId($data["person_id"]);
            // update contact/ person
          
            $personId = $data["person_id"];

            // create phone of person (contact )
        if (!empty($data['PhoneInput'])) {
            $objPerson->setPersonPhone($data['PhoneInput']);
            $objPerson->insertPhone('phone');
        }
        //Update DOB in person table
        if(!empty($data['date_of_birth']) && !empty($personId)){
            $this->basic_model->update_records('person',array('date_of_birth' => $data['date_of_birth']), array('id' => $personId));
        }

        $addr = [];
        if(!$data['is_manual_address']){
            if (!empty($data['address'])) {
                $addr[] = [
                    'unit_number' => $data['unit_number'] ?? '',
                    'street' => $data['address']->street ?? '',
                    'state' => !empty($data['address']->state) ? $data['address']->state : null, 
                    'suburb' => $data['address']->suburb ?? '',
                    'postcode' => $data['address']->postcode ?? '',
                    'is_manual_address' => $data['is_manual_address'] ?? 0,
                    'manual_address' => NULL
                ];                   
            }
            // create / delete of person (contact )
                $objPerson->setPersonAddress($addr);
                $objPerson->insertAddress();
        }else{           
            $this->add_manual_address_in_person_address($personId, $data);
              
        }

        $objPerson->setAvatar($data['avatar']);
        if (!empty($personId)) {
            $objPerson->updatePersonAvatar();
        }
        
        }   
        return $personId;
    }

    /*
     * its use for get option of contact type
     * return type Array object
     */

    public function add_manual_address_in_person_address($personId, $data) {
        $where = array('person_id' => $personId,'archive' => 0);
        $person_address_id = $this->basic_model->get_row('person_address', ['id'], $where);
        if(!empty($person_address_id)){
            $manual_address['unit_number'] = $data['unit_number'] ?? '';             
            $manual_address['is_manual_address'] = $data['is_manual_address'] ?? 0;
            $manual_address['manual_address'] = $data['manual_address'] ?? NULL;
            // update person address table
            return  $this->basic_model->update_records('person_address', $manual_address, ['person_id' => $personId]);
        }else{
            $manual_address['unit_number'] = $data['unit_number'] ?? '';  
            $manual_address['is_manual_address'] = $data['is_manual_address'] ?? 0;
            $manual_address['manual_address'] = $data['manual_address'] ?? NULL;
            $manual_address['person_id'] = $personId;     
            $manual_address['primary_address'] = 1;    
            return $this->basic_model->insert_records('person_address', $manual_address);
        }
    }

    /**
     * fetching the reference list using the reference key name
     */
    public function get_reference_data_agent($key_name, $code) {
        $this->db->select(["r.id AS value", "r.display_name AS label"]);
        $this->db->from(TBL_PREFIX . 'reference_data_type as rdt');
        $this->db->join(TBL_PREFIX . 'references as r','rdt.id = r.type AND r.archive = 0', 'INNER');
        $this->db->where(['rdt.archive' => 0, 'rdt.key_name' => $key_name, 'r.code' => $code]);
        $res = $this->db->get();
        return $res->row_array();
    }

    /**
     * Add Contact relation when user create a contact against account in shift
     * @param {array} save_relation_contact_as_agent
     * @param {id} personId
     */
    public function save_relation_contact_as_agent($data, $personId, $adminId) {
        $account_person = $data['account_person'] ?? '';

        # Create relation only if account_person have value
        if (isset($account_person) == true && empty($account_person) == false && isset($account_person->value) == true) {
            $account_id = $account_person->value;

            # Get agent reference unique id
            $role = $this->get_reference_data_agent('account_role_type_org', 'org_contact_role_agent');
            $role_id = '';
            if (isset($role) == true && isset($role['value']) == true) {
                $role_id = $role['value'];
                $postdata = [
                    'roll_id' => $role_id,
                    'can_book' => null,
                    'source_data_id' => $account_id,
                    'source_data_type' => 2, // org/suborg/site
                    'destination_data_id' => $personId,
                    'destination_data_type' => 1, //contact
                    "is_primary" => 0
                ];
                $postdata['created'] = DATE_TIME;
                $postdata['created_by'] = $adminId;
                $postdata['archive'] = 0;

                $id = $this->basic_model->insert_records("sales_relation", $postdata);
            }
        }
    }
    /*
     * its use for get contact list
     *
     * operation: searching, filter, sorting
     * return type array
     */

    public function get_contact_list($reqData, $filter_condition = '') {
        // get subquery of cerated by
        $uuid_user_type = $reqData->uuid_user_type;
        $reqData = $reqData->data;
        $createdByNameSubQuery = $this->get_contact_created_by_sub_query('p',$uuid_user_type);

        $limit = $reqData->pageSize ?? 99999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        // searching column
        $src_columns = array('contact_code', 'concat(firstname," ",lastname) as fullName', 'created_by', "type");
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

        $available_column = ["contact_code", "created","firstname","lastname" , "id", "type", "archive", "status"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'p.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_status)) {
            if ($filter->filter_status === "active") {
                $this->db->where('p.status', 1);
            } elseif ($filter->filter_status === "inactive") {
                $this->db->where('p.status', 0);
            }
        }

        $select_column = ["p.contact_code", "p.created","p.firstname","p.lastname" , "p.id", "p.type", "p.archive", "p.status"];

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("concat_ws(' ',firstname,lastname) as fullname");
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
        $this->db->select("(select pt.name from tbl_person_type as pt where pt.id = p.type) as type");

        $this->db->select("(CASE
         WHEN p.status = 1 THEN 'Active'
         WHEN p.status = 0 THEN 'Inactive'
         Else '' end
     ) as status");

        $this->db->from('tbl_person as p');

        $this->db->where('p.archive', 0);
        
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
      

        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->where($queryHaving);
        }

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($limit != 0) {
            if ($dt_filtered_total % $limit == 0) {
                $dt_filtered_total = ($dt_filtered_total / $limit);
            } else {
                $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
            }
        }

        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;
    }

    /*
     * its use for get subquery of contact type
     * return type query
     */

    public function get_contact_created_by_sub_query($tbl = "p", $uuid_user_type='') {

        if($uuid_user_type==ADMIN_PORTAL){
            $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
            $this->db->from(TBL_PREFIX . 'users as u');
            $this->db->join(TBL_PREFIX . 'member as sub_m', "u.id = sub_m.uuid", "INNER");
            $this->db->where("sub_m.uuid = " . $tbl . ".created_by", null, false);
            $this->db->limit(1);
            return $this->db->get_compiled_select();
        }
      
    }

    /*
     * its use for get contact/person details
     *
     * @params $contactId
     *
     * return type array
     * return contact details
     */

    function get_contact_details($contactId) {
        $source_type_sub_query = $this->sub_query_get_source_type("p.person_source");

        $this->db->select("(CASE
            WHEN p.status = 1 THEN 'Active'
            WHEN p.status = 0 THEN 'Inactive'
            else ''
            end) as  status_label
            ");
        $this->db->select("(select tr.display_name from tbl_references as tr where tr.id = p.gender) as gender_label");
        $this->db->select("(select pt.name from tbl_person_type as pt where pt.id = p.type) as contact_type_label");
        $this->db->select("(" . $source_type_sub_query . ") as source_type_label");
        $this->db->select("(select concat_ws(' ',m.firstname,m.lastname) from tbl_member as m where m.id = p.owner) as owner_name");
        $this->db->select(["p.id", "p.firstname", "p.lastname", "p.status", "p.archive", "p.person_source as contact_source", "p.type as contact_type", "p.archive", 'p.ndis_number', 'p.contact_is_account', 'p.date_of_birth', 'p.aboriginal', 'p.communication_method', 'p.religion', 'p.cultural_practices', "p.status as my_status",'p.ndis_number as my_ndis_number','p.gender',
        'p.interpreter', 'p.profile_pic as avatar','p.middlename','p.previous_name']);
        $this->db->select("concat_ws(' ',firstname, lastname) as fullname");
        $this->db->from("tbl_person as p");
        $this->db->where("p.id", $contactId);

        $contact = $this->db->get()->row_array();

        if (!empty($contact)) {
            // get contact address details
            $contact_data = $this->get_contact_address($contactId);
            $contact['contact_address']['unit_number'] = isset($contact_data->unit_number) ? $contact_data->unit_number : null;           
            $contact['contact_address']['address'] = isset($contact_data->address) ? $contact_data->address : null;
            $contact['contact_address']['manual_address'] = isset($contact_data->manual_address) ? $contact_data->manual_address : null;
            $contact['contact_address']['is_manual_address'] = isset($contact_data->is_manual_address) ? $contact_data->is_manual_address : null;

            // get contact email
            $contact["EmailInput"] = (array) $this->basic_model->get_record_where("person_email", ["email"], ["archive" => 0, "person_id" => $contactId]);

            // get contact phone
            $contact["PhoneInput"] = (array) $this->basic_model->get_record_where("person_phone", ["phone"], ["archive" => 0, "person_id" => $contactId]);

            // Getting communication method value
            $contact["communication_method_val"] = "";
            if(isset($contact["communication_method"])) {
                if($contact["communication_method"] == 1)
                $contact["communication_method_val"] = "Phone";
                else if($contact["communication_method"] == 2)
                $contact["communication_method_val"] = "Email";
                else if($contact["communication_method"] == 3)
                $contact["communication_method_val"] = "Post";
                else if($contact["communication_method"] == 4)
                $contact["communication_method_val"] = "SMS";
            }

            // Getting aboriginal value
            $contact["aboriginal_val"] = "";
            if(isset($contact["aboriginal"])) {
                if($contact["aboriginal"] == 1)
                $contact["aboriginal_val"] = "Aboriginal";
                else if($contact["aboriginal"] == 2)
                $contact["aboriginal_val"] = "Torres Strait Islander";
                else if($contact["aboriginal"] == 3)
                $contact["aboriginal_val"] = "Both";
                else if($contact["aboriginal"] == 4)
                $contact["aboriginal_val"] = "Neither";
            }
            // Getting interpreter value
            $contact["interpreter_val"] = "";
            if(isset($contact["interpreter"])) {
                if($contact["interpreter"] == 1)
                $contact["interpreter_val"] = "No";
                else if($contact["interpreter"] == 2)
                $contact["interpreter_val"] = "Yes";
            }
        }

        return $contact;
    }

    /*
     * its use for archive contact
     *
     * @params $contactId
     * return type boolean
     * true/false
     */

    function archive_account($org_id) {
        $updated_data = ["archive" => 1];
        return $this->basic_model->update_records("person", $updated_data, ["id" => $org_id]);
    }

    /*
     * its use for get contact address
     *
     * @params $contactId
     *
     * return type string
     * complete address string
     */

    function get_contact_address($contactId) {
        $this->db->select(["concat(pt.street,', ',pt.suburb,' ',(select s.name from tbl_state as s where s.id = pt.state),' ',pt.postcode) as address","pt.unit_number","pt.is_manual_address","pt.manual_address"]);
        $this->db->from("tbl_person_address as pt");
        $this->db->where("pt.person_id", $contactId);
        $this->db->where("pt.primary_address", 1);
        $this->db->where("pt.archive", 0);

        return $this->db->get()->row();
    }

    function create_contact_addresses($contact_addresses) {
        // TODO use basic_model
        $this->db->insert('tbl_person_address', $contact_addresses);
    }

    function sub_query_get_source_type($column_name) {
        $this->db->select(["r.display_name"]);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'lead_source'", "INNER");
        $this->db->where("r.id = " . $column_name, null, false);

        return $query = $this->db->get_compiled_select();
    }

    /** Retrieves the opportunities assosciated with the given
     *  contactId */
    function get_opportunities($personId) {
        $result = $this->db->select(['opp.id', 'opportunity_number', 'topic', 'amount'])
            ->from('tbl_opportunity as opp')
            ->join('tbl_sales_relation', 'destination_data_id = opp.id')
            ->where('source_data_type', 1)
            ->where('destination_data_type', 3)
            ->where('source_data_id', $personId)
            ->get()->result();

        // check reverse?

        return $result;
    }

    /**
     * Getting all contact accounts
     */
    function get_contact_accounts_list($reqData) {

        $limit = $reqData->pageSize ?? 9999;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';
        $contact_id = !empty($reqData->id) ? $reqData->id : null;
        $account_type = !empty($reqData->account_type) ? $reqData->account_type : null;
        $is_site = !empty($reqData->is_site) ? $reqData->is_site : 0;

        # Searching column
        $src_columns = array("o.name", "r1.name as service_type_label", "r.display_name", "DATE_FORMAT(o.created,'%d/%m/%Y')", "DATE_FORMAT(o.updated,'%d/%m/%Y')", "(CASE WHEN o.status = 1 THEN 'active' WHEN o.status = 0 THEN 'inactive' else '' end)", "(CASE WHEN sr.is_primary = 1 THEN 'Yes' WHEN sr.is_primary = 0 THEN 'No' else '' end)");

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
        $available_column = ["id", "account_id", "service_type_label", "role_id", "role_name", "name", "person_id", "status", "created", "updated", "is_primary"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'o.created';
            $direction = 'DESC';
        }

        $select_column = ["sr.id, o.id as account_id, r1.name as service_type_label, sr.roll_id as role_id, r.display_name as role_name, o.name, p.id as person_id, (CASE WHEN o.status = 1 THEN 'Active' WHEN o.status = 0 THEN 'Inactive' else '' end) as status, o.created, o.updated, sr.is_primary"];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(select pe.email from tbl_person_email as pe where pe.person_id = p.id and pe.archive = 0 and pe.primary_email = 1 limit 1) as email");
        $this->db->select("(select pp.phone from tbl_person_phone as pp where pp.person_id = p.id and pp.archive = 0 and pp.primary_phone = 1 limit 1) as phone");
        
        $this->db->from("tbl_sales_relation as sr");
        $this->db->join('tbl_person as p', 'p.id = sr.destination_data_id', 'inner');
        $this->db->join('tbl_organisation as o', 'o.id = sr.source_data_id', 'inner');
        $this->db->join('tbl_references as r', 'r.id = sr.roll_id', 'inner');
        $this->db->join('tbl_member_role as r1', 'o.role_id = r1.id', 'left');

        $this->db->where("o.archive", 0);
        $this->db->where("p.archive", 0);
        $this->db->where("r.archive", 0);
        $this->db->where("sr.archive", 0);

        $this->db->where("sr.destination_data_type", 1);
        $this->db->where("o.is_site", $is_site);
        $this->db->where("sr.destination_data_id", $contact_id);
        $this->db->where("sr.source_data_type", $account_type);
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

                $account['label'] = $row['name'];
                $account['value'] = $row['id'];
                $row['account'] = $account;
                $main_result[] = $row;
            }
        }
        $result = ['status' => true, 'count' => $dt_filtered_total, 'data' => $main_result];
        return $result;
    }

    /**
     * To get contact list related to account 
     */

    function get_contact_for_account($reqData, $allow_new_contact,$fromDataImport=false) {
        
        if (isset($reqData) && isset($reqData->account)) {
            $name = $reqData->query ?? '';
            // Get subquery of cerated & updated by
            $new_contact = array("label"=>"New Contact", "value"=>"new contact");
        
            $this->db->like('label', $name);
            
            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
          
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
          
            if($reqData->account->account_type == 1)
            {

             $participant_id = $reqData->account->value;
             $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label"];
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
             $this->db->having($queryHaving);
             $query = $this->db->get();
             $result = $query->num_rows() > 0 ? $query->result_array() : [];
             // to add the new contact data
            if ($allow_new_contact == true) {
                $result[] = array_push($result, $new_contact);
            }
            return $result;
             
            }
            else
            {
                $org_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label"];
                $this->db->from(TBL_PREFIX . 'sales_relation as sr');
                $this->db->select($column);
                $this->db->where('sr.source_data_id', $org_id);
                $this->db->where('sr.source_data_type', '2');
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
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
                // to add the new contact data
                if ($allow_new_contact == true) {
                    $result[] = array_push($result, $new_contact);
                }
                return $result;
               
            }
            
        } else {
            return [ "status" => false, 'error' => 'Participant Id is null'];
        }
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
                $this->db->select(["o.name", "o.id"]);
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

    function get_option_task_field_ralated_to($reqData) {
        $search = $reqData->search ?? '';
        $sql = [];

        // for opportunity by ID and topic
        $this->db->select(["o.id as value", "CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') as label", "'1' as type"]);
        $this->db->from("tbl_opportunity as o");
        $this->db->where("o.archive", 0);
        $this->db->group_start();
        $this->db->like("o.opportunity_number", $search);
        $this->db->or_like("o.topic", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        // for lead by Id and topic
        $this->db->select(["l.id as value", "CONCAT_WS(' ', l.lead_topic,'-','(',l.lead_number,')') as label", "'2' as type"]);
        $this->db->from("tbl_leads as l");
        $this->db->where("l.archive", 0);
        $this->db->group_start();
        $this->db->like("l.lead_number", $search);
        $this->db->or_like("l.lead_topic", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        // for service agreement by Id
        $this->db->select(["sa.id as value", "sa.service_agreement_id as label", "'3' as type"]);
        $this->db->from("tbl_service_agreement as sa");
        $this->db->where("sa.archive", 0);
        $this->db->like("sa.service_agreement_id", $search);
        $sql[] = $this->db->get_compiled_select();

        // for need agreement by Id and title
        $this->db->select(["na.id as value", "na.title as label", "'4' as type"]);
        $this->db->from("tbl_need_assessment as na");
        $this->db->where("na.archive", 0);
        $this->db->group_start();
        $this->db->like("na.title", $search);
        $this->db->or_like("na.need_assessment_number", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        // for risk assessment
        $this->db->select(["cra.id as value", "cra.topic as label", "'5' as type"]);
        $this->db->from("tbl_crm_risk_assessment as cra");
        $this->db->where("cra.status", 2);
        $this->db->where("cra.is_deleted", 0);
        $this->db->group_start();
        $this->db->like("cra.reference_id", $search);
        $this->db->or_like("cra.topic", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        // for shifts
        $this->db->select(["s.id as value", "s.shift_no as label", "'6' as type"]);
        $this->db->from("tbl_shift as s");
        $this->db->where("s.archive", 0);
        $this->db->like("s.shift_no", $search);
        $sql[] = $this->db->get_compiled_select();

        // for application
        $this->db->select(["ta.id as value", "ta.id as label", "'8' as type"]);
        $this->db->from("tbl_recruitment_applicant_applied_application as ta");
        $this->db->where("ta.archive", 0);
        $this->db->like("ta.id", $search);
        $sql[] = $this->db->get_compiled_select();

        // for timesheets
        $this->db->select(["ts.id as value", "ts.timesheet_no as label", "'7' as type"]);
        $this->db->from("tbl_finance_timesheet as ts");
        $this->db->where("ts.archive", 0);
        $this->db->like("ts.timesheet_no", $search);
        $sql[] = $this->db->get_compiled_select();
        // for application
        $this->db->select(["ta.id as value", "ta.id as label", "'8' as type"]);
        $this->db->from("tbl_recruitment_applicant_applied_application as ta");
        $this->db->where("ta.archive", 0);
        $this->db->like("ta.id", $search);
        $sql[] = $this->db->get_compiled_select();

        // for interview
        $this->db->select(["ri.id as value", "ri.title as label", "'9' as type"]);
        $this->db->from("tbl_recruitment_interview as ri");
        $this->db->where("ri.archive", 0);
        $this->db->like("ri.title", $search);
        $sql[] = $this->db->get_compiled_select();

        // for invoices
        $this->db->select(["i.id as value", "i.invoice_no as label", "'10' as type"]);
        $this->db->from("tbl_finance_invoice as i");
        $this->db->where("i.archive", 0);
        $this->db->like("i.invoice_no", $search);
        $sql[] = $this->db->get_compiled_select();

        $sql_query = implode(' union ', $sql);
        $query = $this->db->query($sql_query);

        return $result = $query->result();
    }

    function get_option_task_field_name($reqData) {
        $search = $reqData->search ?? '';
        $sql = [];

        // for lead by Id and topic
        $this->db->select(["l.id as value", "concat(l.firstname, ' ', l.lastname) as label", "'2' as type"]);
        $this->db->from("tbl_leads as l");
        $this->db->where("l.archive", 0);
        $this->db->group_start();
        $this->db->like("l.lead_number", $search);
        $this->db->or_like("l.firstname", $search);
        $this->db->or_like("l.lastname", $search);
        $this->db->or_like("concat(l.firstname,' ',l.lastname)", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["p.id as value", "concat(p.firstname, ' ', p.lastname) as label", "'6' as type"]);
        $this->db->where("p.archive", 0);
        $this->db->from("tbl_person as p");
        $this->db->group_start();
        // $this->db->like("p.contact_code", $search);
        $this->db->or_like("p.firstname", $search);
        $this->db->or_like("p.lastname", $search);
        $this->db->or_like("concat(p.firstname,' ',p.lastname)", $search);
        $this->db->group_end();
        $sql[] = $this->db->get_compiled_select();

        $sql_query = implode(' union ', $sql);
        $query = $this->db->query($sql_query);

        return $result = $query->result();
    }

    function give_id_of_entity_type($sales_type) {
        if ($sales_type === "contact") {
            $source_data_type = 1;
        }elseif ($sales_type == "invoice") {
            $source_data_type = 10;
        } elseif ($sales_type === "organisation") {
            $source_data_type = 2;
        } elseif ($sales_type === "opportunity") {
            $source_data_type = 3;
        } elseif ($sales_type == "lead") {
            $source_data_type = 4;
        }elseif ($sales_type == "service") {
            $source_data_type = 5;
        }elseif ($sales_type == "shift") {
            $source_data_type = 6;
        }elseif ($sales_type == "timesheet") {
            $source_data_type = 7;
        }elseif ($sales_type == "application") {
            $source_data_type = 8;
        }elseif ($sales_type == "interview") {
            $source_data_type = 9;
        }
        else {
            $source_data_type = false;
        }

        return $source_data_type;
    }

    function get_option_of_contact_name_search($reqData) {
//        $search = $reqData->search ?? '';
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';

        $source_data_type = false;
        if ($sales_type == "contact") {          
            $this->db->where("p.id", $salesId);
        } else {
            $source_data_type = $this->give_id_of_entity_type($sales_type);
        }
        if ($source_data_type && $source_data_type!=5 && $source_data_type!=8) {
            $this->db->join("tbl_sales_relation as sr", "sr.destination_data_id = p.id AND sr.destination_data_type = 1", "INNER");
            $this->db->where("sr.source_data_id",  $salesId);
            $this->db->where("sr.source_data_type", $source_data_type);
        }

       
//        $this->db->like("concat_ws(' ',firstname,lastname)", $search);
        if($source_data_type==5 || $source_data_type==8){
            $this->db->select(["concat_ws(' ',firstname,lastname) as label", "p.id as value","'6' as type"]);
            $this->db->where("p.id",  $salesId);
        }
       
        $this->db->from("tbl_person as p");
        
        $this->db->where("p.archive", 0);
        $this->db->where("p.status", 1);
        $this->db->group_by("p.id");

        $return = $this->db->get()->result();
        return $return;
    }

    function get_option_of_lead_name_search($reqData) {
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';

        $this->db->select(["concat(tbl_leads.firstname, ' ', tbl_leads.lastname) as label", "tbl_leads.id as value","'2' as type"]);
        $this->db->from("tbl_leads");
        $this->db->where("tbl_leads.id", $salesId);

        // $this->db->join("tbl_leads as sr", "(sr.converted_contact_id = p.id OR sr.converted_account_contact_id = p.id)", "INNER");
        // $this->db->where("sr.id", $salesId);

        // $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label", "p.id as value"]);
        // $this->db->from("tbl_person as p");
        // $this->db->where("p.archive", 0);
        // $this->db->where("p.status", 1);
        // $this->db->group_by("p.id");

        $query = $this->db->get();
        return $query->result();
    }

    function create_task_for_contact($reqData, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';
        $default_priority = 2;
        $task_data = [
            "crm_participant_id" => $reqData->contactId ? ($reqData->name_type == "6" ? $reqData->contactId : null) : null,
            "lead_id" => $reqData->contactId ? ($reqData->name_type == "2" ? $reqData->contactId : null) : null,
            "user_type" => 2,
            "task_name" => $reqData->task_name,
            "priority" => $default_priority,
            "due_date" => $reqData->due_date ? DateFormate($reqData->due_date, "Y-m-d") : '',
            "assign_to" => $reqData->assign_to,
            "relevant_task_note" => "",
            "note" => "note",
            "task_status" => $reqData->status ?? 0,
            "created_at" => DATE_TIME,
            "archive" => 0,
            "related_to" => $reqData->related_to ?? null,
            "related_type" => $reqData->related_type ?? null,
            "entity_id" => $reqData->salesId ?? 0,
            "entity_type" => $this->give_id_of_entity_type($reqData->sales_type),
            "created_by" => $adminId,
        ];

        $taskId = $this->basic_model->insert_records("crm_participant_schedule_task", $task_data, false);

        // create activity log for it
        $act_ob = new SalesActivity();

        $act_ob->setActivity_type(1);
        $act_ob->setContactId($reqData->contactId ? ($reqData->name_type==6 ? $reqData->contactId : null) : null);
        $act_ob->setLead_id( $reqData->contactId ? ($reqData->name_type==2 ? $reqData->contactId : null) : null);
        $act_ob->setRelated_to($reqData->related_to ?? null);
        $act_ob->setRelated_type($reqData->related_type ?? null);
        $act_ob->setEntity_id($reqData->salesId);
        $act_ob->setEntity_type($this->give_id_of_entity_type($reqData->sales_type));
        $act_ob->setTaskId($taskId);
        $act_ob->setCreated_by($adminId);

        $act_ob->createActivity();

        return $taskId;
    }

    function create_call_log_for_contact($reqData, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';

        

        if(!@$reqData->related_type && $reqData->sales_type == "lead")
           $reqData->related_type = 2;
        if(!@$reqData->related_type && $reqData->sales_type == "contact")
           $reqData->related_type = 4;
        if((!@$reqData->related_type || $reqData->related_type == 0) && $reqData->sales_type == "opportunity")
        {
            $reqData->related_type = 1;
        }
        if((!@$reqData->related_type || $reqData->related_type == 0) && $reqData->sales_type == "organisation")
        {
            $reqData->related_type = 1;
        }
           
        if(!@$reqData->related_to)
          $reqData->related_to = 0;

        // create activity log for it
        $act_ob = new SalesActivity();

        $act_ob->setActivity_type(3);
        $act_ob->setContactId($reqData->contactId ? ($reqData->name_type==6 ? $reqData->contactId : null) : null);
        $act_ob->setLead_id( $reqData->contactId ? ($reqData->name_type==2 ? $reqData->contactId : null) : null);
        $act_ob->setRelated_to($reqData->related_to);
        $act_ob->setRelated_type($reqData->related_type);
        $act_ob->setEntity_id($reqData->related_to);
        $act_ob->setEntity_type($this->give_id_of_entity_type($reqData->sales_type));
        $act_ob->setSubject($reqData->subject ?? "");
        $act_ob->setComment($reqData->comment ?? "");
        $act_ob->setCreated_by($adminId);

        $activityId = $act_ob->createActivity();

        return $activityId;
    }

    /**
     * Create note activity this for common function will work based on related type
     **/ 
    function create_note_for_activity($reqData, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';
        
        // return related type and entity type
        // For shift notes are same value for related and entity type
        $source_data_type = $this->give_id_of_entity_type($reqData->sales_type);
           
        if(!@$reqData->related_to)
          $reqData->related_to = 0;

        // create activity log for it
        $act_ob = new SalesActivity();

        $act_ob->setActivity_type(4);
        $act_ob->setRelated_to($reqData->salesId);
        $act_ob->setRelated_type($source_data_type);
        $act_ob->setEntity_id($reqData->salesId);
        $act_ob->setEntity_type($source_data_type);
        $act_ob->setSubject($reqData->title ?? "");
        $act_ob->setComment($reqData->description ?? "");
        $act_ob->setCreated_by($adminId);
        $act_ob->setNoteType($reqData->note_type ?? "");
        $act_ob->setConfidential($reqData->confidential ?? "");

        $activityId = $act_ob->createActivity();
        return $activityId;
    }

    function update_task_for_contact($reqData, $adminId) {
        require_once APPPATH . 'Classes/sales/SalesActivity.php';
        $default_priority = 2;
        $taskId= $reqData->id;
        $entityId= $reqData->entity_id;
        if($reqData->related_type == 1 || $reqData->related_type == 2){
            $entityId = $reqData->salesId;
        }
        $task_data = [
            "crm_participant_id" => $reqData->contactId ? ($reqData->name_type == "6" ? $reqData->contactId : null) : null,
            "lead_id" => $reqData->contactId ? ($reqData->name_type == "2" ? $reqData->contactId : null) : null,
            "user_type" => 2,
            "task_name" => $reqData->task_name,
            "priority" => $default_priority,
            "due_date" => $reqData->due_date ? DateFormate($reqData->due_date, "Y-m-d") : '',
            "assign_to" => $reqData->assign_to,
            "relevant_task_note" => "",
            "note" => "note",
            "task_status" => $reqData->task_status ?? 0,
            "created_at" => DATE_TIME,
            "archive" => 0,
            "related_to" => $reqData->related_to ?? null,
            "related_type" => $reqData->related_type ?? null,
            "entity_id" => $entityId,
            "entity_type" => $this->give_id_of_entity_type($reqData->sales_type),
            "created_by" => $adminId,
        ];

        $this->basic_model->update_records('crm_participant_schedule_task', $task_data, ["id" => $taskId]);
        // create activity log for it
        $act_ob = new SalesActivity();

        $act_ob->setActivity_type(1);
        $act_ob->setContactId($reqData->contactId ? ($reqData->name_type==6 ? $reqData->contactId : null) : null);
        $act_ob->setLead_id( $reqData->contactId ? ($reqData->name_type==2 ? $reqData->contactId : null) : null);
        $act_ob->setRelated_to($reqData->related_to ?? null);
        $act_ob->setRelated_type($reqData->related_type ?? null);
        $act_ob->setEntity_id($entityId);
        $act_ob->setEntity_type($this->give_id_of_entity_type($reqData->sales_type));
        $act_ob->setTaskId($taskId);
        $act_ob->setCreated_by($adminId);

        $act_ob->updateActivityTaskId();

        return $taskId;
    }

    function get_acitvity_as_per_entity_id_and_type($reqData) {
        $sale_type = $this->give_id_of_entity_type($reqData->sales_type);
        $rq_sale_id = (integer) $reqData->salesId ?? 0;
        $res = "";
        # to pull the lead task for other page based on entity type
        $lead_entity_type = "('1','2','3','4','5','6','7','8','9','10')";
        if($sale_type != 2)
        {
          
            $this->db->select("(CASE
                WHEN sa.activity_type = 1 THEN (select task_name from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId)
                WHEN sa.activity_type = 2 OR sa.activity_type = 3 OR sa.activity_type = 4 THEN sa.subject                
                else ''
                end) as title
                ");

            $this->db->select("(select crm_participant_id from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId) as crm_participant_id");
            $this->db->select("(select assign_to from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId) as assign_to_id");

            $this->db->select("(CASE
                    WHEN entity_type = 4 and lead_id is null and contactId is null THEN  (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = sa.contactId)
                    WHEN lead_id is not null and  contactId is null THEN  (select concat_ws(' ', `firstname`, lastname) from tbl_leads as lds where lds.id = sa.lead_id)
                    ELSE                       (select concat_ws(' ',firstname,lastname) from tbl_person as sub_p where sub_p.id = sa.contactId)
                    end
                    ) as contact_name");

            $this->db->select("(CASE
                    when related_type = 1 THEN (select CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') from tbl_opportunity as o where o.id = sa.related_to)
                    when related_type = 10 THEN (select invoice_no from tbl_finance_invoice as i where i.id = sa.related_to)
                    when related_type = 2 THEN (select CONCAT_WS(' ', l.lead_topic,'-','(',l.lead_number,')') from tbl_leads as l where l.id = sa.related_to)
                    when related_type = 3 THEN (select service_agreement_id from tbl_service_agreement as sa where sa.id = sa.related_to)
                    when related_type = 4 THEN (select title from tbl_need_assessment as na where na.id = sa.related_to)
                    when related_type = 5 THEN (select topic from tbl_crm_risk_assessment as cra where cra.id = sa.related_to)
                    when related_type = 6 THEN (select shift_no from tbl_shift as s where s.id = sa.related_to)
                    when related_type = 7 THEN (select timesheet_no from tbl_finance_timesheet as ts where ts.id = sa.related_to)
                    when related_type = 8 THEN (select id from tbl_recruitment_applicant_applied_application as ta where ta.id = sa.related_to)
                    when related_type = 9 THEN (select title from tbl_recruitment_interview as ri where ri.id = sa.related_to)
                    ELSE ''
                    end
                    ) as related_to_label");
                    $this->db->select("((select applicant_id from tbl_recruitment_applicant_applied_application as ta where ta.id = sa.related_to)
                    ) as applicant_id");
                    $this->db->select("(select email from tbl_leads as lds where lds.id = sa.contactId) as lead_email");
                    $this->db->select("(select phone from tbl_leads as lds where lds.id = sa.contactId) as lead_phone");
            $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as sub_m where sub_m.uuid = created_by) as created_by");
            $this->db->select(["sa.activity_type", "entity_id", "entity_type", "taskId", "created", "sa.subject", "sa.comment", "sa.related_type", "sa.related_to", "sa.contactId", "sa.lead_id", "sa.id as activity_id", "sa.note_type", "sa.confidential"]);
            $this->db->from("tbl_sales_activity as sa");
            $this->db->where_not_in("sa.activity_type", ["2","5"]);
            $this->db->group_start();
            if(!empty($reqData->related_type)){
                if($reqData->related_type == 2){
                    $this->db->group_start();
                        $this->db->group_start();
                        $this->db->where('sa.related_to', $reqData->salesId);
                        $this->db->or_where('sa.lead_id', $reqData->salesId);
                        $this->db->group_end();
                    $this->db->where('sa.related_type', $reqData->related_type);
                    $this->db->group_end();
                                    
                    $this->db->or_group_start();
                        $this->db->group_start();
                        $this->db->where('sa.entity_id', $reqData->salesId);
                        $this->db->or_where('sa.lead_id', $reqData->salesId);
                        $this->db->group_end();
                    $this->db->where('sa.entity_type', $lead_entity_type);
                    $this->db->group_end();
               
                }else{
                    $this->db->group_start();
                    $this->db->where('sa.related_to', $reqData->salesId);
                    $this->db->where('sa.related_type', $reqData->related_type);
                    $this->db->group_end();

                    $this->db->or_group_start();
                    $this->db->where('sa.entity_id', $reqData->salesId);
                    $this->db->where('sa.entity_type', $sale_type);
                    $this->db->group_end();
                }

            }else{
              
                if($sale_type == 1 || $sale_type == 4) // if contact
                 {
                    $this->db->group_start();
                    $this->db->where('sa.related_to', $reqData->salesId);
                    $this->db->where('sa.related_type', $sale_type);
                    $this->db->group_end();
                    $this->db->or_where('sa.contactId', $reqData->salesId);
                } else {
                    $this->db->group_start();
                    $this->db->where('sa.related_to', $reqData->salesId);
                    $this->db->where('sa.related_type', $sale_type);
                    $this->db->group_end();

                }
                $this->db->or_group_start();
                $this->db->where('sa.entity_id', $reqData->salesId);
                $this->db->where('sa.entity_type', $sale_type);
                $this->db->group_end();
            }
            $this->db->group_end();
            $res = $this->db->get()->result();            
            // Get email activity log
            $get_activity_recipient = $this->get_activity_recipient($reqData, $sale_type);

            $res = array_merge($res, $get_activity_recipient);
        }
       
        $taskIds = [];
        $activityIds = [];
        $smsActivityIds = [];
        $res_sort = [];
        if (!empty($res)) {
            foreach ($res as $val) {
                if ($val->activity_type == 1) {
                    $taskIds[] = $val->taskId;
                }

                if ($val->activity_type == 2) {
                    $activityIds[] = $val->activity_id;
                }

                if ($val->activity_type == 5) {
                    $smsActivityIds[] = $val->activity_id;
                }
                $res_sort[$val->activity_id] = $val;
            }
            // sort array asc by key
            ksort($res_sort);
            $res_sort = array_values($res_sort);

            $task_details = [];
            if (!empty($taskIds)) {
                $task_details = $this->get_task_details_by_task_ids($taskIds);
            }

            // load contact model
            $this->load->model('Activity_model');

            $email_details = [];
            if (!empty($activityIds)) {
                $email_details = $this->Activity_model->get_email_details_by_activity_ids($activityIds);
            }

            $sms_details = [];
            if (!empty($smsActivityIds)) {
                $sms_details = $this->Activity_model->get_sms_details_by_activity_ids($smsActivityIds, $rq_sale_id, $sale_type);
            }            

            $email_attachment = [];
            if (!empty($activityIds)) {
                $email_attachment = $this->Activity_model->get_email_attachment_by_activity_ids($activityIds);
            }

            foreach ($res_sort as $val) {
                switch($val->activity_type) {
                    case 1:
                        $task = $task_details[$val->taskId] ?? [];
                        $val->task_details = $task;
                        $val->description = $val->created_by . " created a task with " . ($task->assign_to ?? "N/A");
                        break;
                    case 2:
                        $email = $email_details[$val->activity_id] ?? [];
                        $val->email_details = $email;
                        $email_attach = $email_attachment[$val->activity_id] ?? [];
                        $val->email_details['attachment'] = $email_attach;
                        $val->description = $val->created_by . " Sent a email";
                        break;
                    case 3:
                        $val->description = $val->created_by . " logged a call with " . ($val->contact_name ?? "N/A");
                        break;
                    case 5:
                        $sms = $sms_details[$val->activity_id] ?? [];
                        $val->title = $val->subject . " SMS sent";
                        $val->sms_details = $sms;
                        break;
                    default:
                        break;
                }
            }
        }
     
        return $res_sort;
    }

    /**
     * List all activity recipient based on sales id with to (recipient of to) or related_to
     *
     * @param {obj} reqData
     * @param {int} sale_type
     *
     * @return array {result of query}
     */
    public function get_activity_recipient($reqData, $sale_type) {
        $related_type = $this->give_id_of_related_type($reqData->sales_type);

        $this->db->select("(CASE
                WHEN sa.activity_type = 1 THEN (select task_name from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId)
                WHEN sa.activity_type = 2 THEN sa.subject
                WHEN sa.activity_type = 3 THEN sa.subject
                else ''
                end) as title
                ");
            $this->db->select("(CASE
                WHEN sa.activity_type = 5 THEN 
                    CASE
                    WHEN sa.related_to = sa.entity_id AND sa.related_type = 8 AND sa.related_type = sa.entity_type THEN 1
                    ELSE 0
                    END
                else 0
                end) as is_single_sms
                ");

            $this->db->select("(select crm_participant_id from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId) as crm_participant_id");
            $this->db->select("(select assign_to from tbl_crm_participant_schedule_task as cpst where cpst.id = sa.taskId) as assign_to_id");

            $this->db->select("(CASE
                    WHEN sa.entity_type = 4 THEN  (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = sa.contactId)
                    ELSE                       (select concat_ws(' ',firstname,lastname) from tbl_person as sub_p where sub_p.id = sa.contactId)
                    end
                    ) as contact_name");

            $this->db->select("(CASE
                    when related_type = 1 THEN (select CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') from tbl_opportunity as o where o.id = sa.related_to)
                    when related_type = 10 THEN (select invoice_no from tbl_finance_invoice as i where i.id = sa.related_to) 
                    when related_type = 2 THEN (select CONCAT_WS(' ', l.lead_topic,'-','(',l.lead_number,')') from tbl_leads as l where l.id = sa.related_to)
                    when related_type = 3 THEN (select service_agreement_id from tbl_service_agreement as sa where sa.id = sa.related_to)
                    when related_type = 4 THEN (select title from tbl_need_assessment as na where na.id = sa.related_to)
                    when related_type = 5 THEN (select topic from tbl_crm_risk_assessment as cra where cra.id = sa.related_to)
                    when related_type = 6 THEN (select shift_no from tbl_shift as s where s.id = sa.related_to)
                    when related_type = 7 THEN (select timesheet_no from tbl_finance_timesheet as ts where ts.id = sa.related_to)
                    when related_type = 8 THEN (select id from tbl_recruitment_applicant_applied_application as ta where ta.id = sa.related_to)
                    when related_type = 9 THEN (select title from tbl_recruitment_interview as ri where ri.id = sa.related_to)
                    ELSE ''
                    end
                    ) as related_to_label");
            $this->db->select("((select applicant_id from tbl_recruitment_applicant_applied_application as ta where ta.id = sa.related_to)
                    ) as applicant_id");
            $this->db->select("(select email from tbl_leads as lds where lds.id = sa.contactId) as lead_email");
            $this->db->select("(select phone from tbl_leads as lds where lds.id = sa.contactId) as lead_phone");
            $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as sub_m where sub_m.uuid = created_by) as created_by");
            $this->db->select(["sa.activity_type", "entity_id", "sa.entity_type", "saer.entity_type as recipient_entity_type", "taskId", "created", "sa.subject", "sa.comment", "sa.related_type", "sa.related_to", "sa.contactId", "sa.id as activity_id"]);
            $this->db->from("tbl_sales_activity as sa");
            $this->db->join("tbl_sales_activity_recipient AS saer", "sa.`id` = saer.`activity_id`", "INNER");
            $this->db->where_in("sa.activity_type", ["2","5"]);
            /**
             * if contact get recipient even entity type is null. because entity type field is introduced lately
             */
            $this->db->group_start();
            $this->db->group_start();
            $this->db->where('sa.related_to', $reqData->salesId);
            $this->db->where('sa.related_type', $related_type);
            $this->db->group_end();
                    
            if($sale_type == 1)
            {
                $this->db->or_group_start();
                $this->db->where('saer.recipient', $reqData->salesId);
                $this->db->where('saer.type', 2);
                $this->db->group_start();
                    $this->db->where('saer.entity_type', $sale_type);
                    $this->db->or_where('saer.entity_type IS NULL');
                $this->db->group_end();
                $this->db->group_end();
            } else {
                $this->db->or_group_start();
                $this->db->where('saer.recipient', $reqData->salesId);
                $this->db->where('saer.type', 2);
                $this->db->where('saer.entity_type', $sale_type);
                $this->db->group_end();
            }
            $this->db->group_end();
            return $this->db->get()->result();
    }


    /**
     * Search contact against his/her full name.
     *
     * This is usually been used as data source for dropdown list in
     * client (eg react-select-plus)
     *
     * @param string $name
     * @return array
     */
    public function get_contact_options($name = '', $limit)
    {
        $this->db->like('label', $name);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',p.firstname,p.lastname) as label", 'p.id as value']);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0]);
        if(!empty($limit)){
        $this->db->limit($limit);
        }
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }


    /**
     * Fetch role options for person/org.
     *
     * This is usually been used as data source for dropdown list in client
     *
     * @param int $type `account_contact_role_type_person=10, account_contact_role_type_org=11`
     * @return array
     */
    public function get_account_contact_role_options($type)
    {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->where(['r.archive' => 0,'r.type'=> $type]);
        $query = $this->db->get();
        $results = $query->num_rows() > 0 ? $query->result_array() : [];
        return $results;
    }

    /**
     * Find account contact roles by source id (contact id or org id) and source data type (person=1, org=2)
     *
     * @param int $source_id
     * @param int $source_data_type person=1, org=2
     * @return array
     */
    public function get_account_contact_roles_by_source_id($source_id, $source_data_type)
    {
        $query = $this->db->get_where('tbl_sales_relation', [
            'archive' => 0,
            'source_data_type' => $source_data_type,
            'source_data_id' => $source_id,
            'roll_id >' => 0, // the col name is actually roll_id
        ]);

        $results = $query->result_array();

        return $results;
    }


    /**
     * Find all account contact role refs
     * @return array
     */
    protected function get_all_account_contact_roles_refs()
    {
        $REF_TYPE_ACCOUNT_CONTACT_ROLE_PERSON = 10;
        $REF_TYPE_ACCOUNT_CONTACT_ROLE_ORG = 11;

        return $this->db
            ->where_in('type', [$REF_TYPE_ACCOUNT_CONTACT_ROLE_ORG, $REF_TYPE_ACCOUNT_CONTACT_ROLE_PERSON])
            ->get('tbl_references')
            ->result_array();
    }


    /**
     * Populate items in `$contacts` with role information
     *
     * @param array[] $contacts
     * @param array[] $account_contact_roles
     * @param mixed $source_data_type contact=1, org=2
     * @param int $destination_data_type contact=1 (default), org=2
     * @return array
     *
     * @todo: Write unit/integration test.
     */
    public function merge_account_contact_roles_with_contact(array $contacts, array $account_contact_roles, $source_data_type, $destination_data_type = 1)
    {
        $allContactRoleRefs = $this->get_all_account_contact_roles_refs();
        $roleBag = array_column($allContactRoleRefs, 'display_name', 'id');

        $accountContactRoleBag = [];

        foreach ($account_contact_roles as $account_contact_role) {
            if (
                $source_data_type != $account_contact_role['source_data_type']
                || $destination_data_type != $account_contact_role['destination_data_type']
            ) {
                continue;
            }

            $source_data_id = $account_contact_role['destination_data_id'];
            $accountContactRoleBag[$source_data_id] = $account_contact_role;
        }

        $newContacts = [];

        foreach ($contacts as $contact) {
            $newContact = $contact;

            if (array_key_exists($contact['id'], $accountContactRoleBag)) {
                $contactRole = $accountContactRoleBag[$contact['id']];

                $newContact = array_merge($newContact, [
                    'can_book' => $contactRole['can_book'],
                    'roll_id' => $contactRole['roll_id'],
                    'role' => [
                        'value' => $contactRole['roll_id'],
                        'label' => $roleBag[$contactRole['roll_id']] ?? null,
                    ],
                    'sales_relation_id' => $contactRole['id'],
                ]);
            }

            $newContacts[] = $newContact;
        }

        return $newContacts;

    }


    function get_task_details_by_task_ids($taskIds) {
        $this->db->select("(select concat_ws(' ',firstname,lastname) from tbl_member as sub_m where sub_m.uuid = assign_to) as assign_to");

        $this->db->select("(CASE
            when user_type = 2 THEN
                (CASE
                    when task.entity_type = 4 and lead_id is null and task.crm_participant_id is null THEN (select concat_ws(' ',firstname,lastname) from tbl_leads as lds where lds.id = task.crm_participant_id)
                    WHEN lead_id is not null and lead_id != 0 THEN  (select concat_ws(' ', `firstname`, lastname) from tbl_leads as lds where lds.id = task.lead_id)

                    ELSE (select concat_ws(' ',firstname,lastname) from tbl_person as sub_p where sub_p.id = task.crm_participant_id)
                end)
            ELSE ''
            end
        ) as contact_name");

        $this->db->select("(CASE
            when related_type = 1 THEN (select CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') from tbl_opportunity as o where o.id = task.related_to)
            when related_type = 10 THEN (select invoice_no from tbl_finance_invoice as i where i.id = task.related_to)
            when related_type = 2 THEN (select CONCAT_WS(' ', l.lead_topic,'-','(',l.lead_number,')') from tbl_leads as l where l.id = task.related_to)
            when related_type = 3 THEN (select service_agreement_id from tbl_service_agreement as sa where sa.id = task.related_to)
            when related_type = 4 THEN (select title from tbl_need_assessment as na where na.id = task.related_to)
            when related_type = 5 THEN (select topic from tbl_crm_risk_assessment as cra where cra.id = task.related_to)
            when related_type = 6 THEN (select shift_no from tbl_shift as s where s.id = task.related_to)
            when related_type = 7 THEN (select timesheet_no from tbl_finance_timesheet as ts where ts.id = task.related_to)
            when related_type = 8 THEN (select id from tbl_recruitment_applicant_applied_application as ta where ta.id = task.related_to)
            when related_type = 9 THEN (select title from tbl_recruitment_interview as ri where ri.id = task.related_to)
            ELSE ''
            end
        ) as related_to_label");

        $this->db->select("(CASE
            when task_status = 0 THEN 'Assigned'
            when task_status = 1 THEN 'completed'
            when task_status = 3 THEN 'Archived'
            ELSE ''
            end
        ) as status_label");

        $this->db->select("(select name from tbl_crm_task_priority as ctp where ctp.id = task.priority) as priority");

        $this->db->select(array('task.id as task_id', 'task.crm_participant_id', 'task.task_name', 'DATE_FORMAT(task.due_date, "%d-%m-%Y") as due_date', 'DATE_FORMAT(task.updated_at, "%d-%m-%Y") as updated_at', 'task.relevant_task_note', 'task.related_type', 'task.related_to', "task.task_status", 'task.entity_type','task.lead_id'));
        $this->db->from('tbl_crm_participant_schedule_task as task');

        $this->db->where_in("task.id", $taskIds);
        $query = $this->db->get();
        $result = $query->result();

        $null_date = strtotime('0000-00-00');
        $unix_epoch = strtotime('1970-01-01');

        $return = [];
        if (!empty($result)) {
            foreach ($result as $val) {
                $due_date = strtotime($val->due_date);
                if (empty($due_date) || $due_date == $null_date || $due_date == $unix_epoch)
                    $val->due_date = '';
                $return[$val->task_id] = $val;
            }
        }

        return $return;
    }

    function get_default_related_to_field_value_label($reqData) {
        $salesId = $reqData->salesId ?? '';
        $sales_type = $reqData->sales_type ?? '';
        $serviceagremmentId = $reqData->serviceagremmentId ?? '';
        $application_id = $reqData->application_id ?? '';

        $res = [];

        if ($sales_type === "opportunity") {
            $this->db->select(["o.id as value", "CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') as label", "'1' as type"]);
            $this->db->from("tbl_opportunity as o");
            $this->db->where("o.archive", 0);
            $this->db->where("o.id", $salesId);

            $res = $this->db->get()->row();
        }
        elseif ($sales_type === "invoice") {
            $this->db->select(["i.id as value", "i.invoice_no as label", "'10' as type"]);
            $this->db->from("tbl_finance_invoice as i");
            $this->db->where("i.archive", 0);
            $this->db->where("i.id", $salesId);
            $res = $this->db->get()->row();
        }
        else if ($sales_type === "service") {
            $this->db->select(["sa.id as value", "sa.service_agreement_id as label", "'3' as type"]);
            $this->db->from("tbl_opportunity as o");
            $this->db->join("tbl_service_agreement as sa", "sa.opportunity_id = o.id", "INNER");
            $this->db->where("o.archive", 0);
            $this->db->where("sa.id", $salesId);
            $res = $this->db->get()->row();
        }
        else if ($sales_type === "lead") {
            // for lead by Id and topic
            $this->db->select(["l.id as value", "CONCAT_WS(' ', l.lead_topic,'-','(',l.lead_number,')') as label", "'2' as type"]);
            $this->db->from("tbl_leads as l");
            $this->db->where("l.archive", 0);
            $this->db->where("l.id", $salesId);
            $res = $this->db->get()->row();
        }
        else if ($sales_type === "shift") {
            $this->db->select(["s.id as value", "s.shift_no as label", "'6' as type"]);
            $this->db->from("tbl_shift as s");
            $this->db->where("s.archive", 0);
            $this->db->where("s.id", $salesId);
            $res = $this->db->get()->row();
        }
        elseif ($sales_type === "application") {
            $this->db->select(["ta.id as value", "ta.id as label", "'8' as type"]);
            $this->db->from("tbl_recruitment_applicant_applied_application as ta");
            $this->db->where("ta.archive", 0);
            $this->db->where("ta.id", $application_id);
            $res = $this->db->get()->row();
        }
        else if ($sales_type === "timesheet") {
            $this->db->select(["ts.id as value", "ts.timesheet_no as label", "'7' as type"]);
            $this->db->from("tbl_finance_timesheet as ts");
            $this->db->where("ts.archive", 0);
            $this->db->where("ts.id", $salesId);
            $res = $this->db->get()->row();
        }
        // Interviews
        else if ($sales_type === "interview") {
            $this->db->select(["ri.id as value", "ri.title as label", "'9' as type"]);
            $this->db->from("tbl_recruitment_interview as ri");
            $this->db->where("ri.archive", 0);
            $this->db->where("ri.id", $salesId);
            $res = $this->db->get()->row();
        }
        return $res;
    }

    function get_option_of_assign_to_field_value_label($reqData) {
        $assign_to = $reqData->assign_to ?? '';
        $sales_type = $reqData->sales_type ?? '';

        $res = [];
            $this->db->select(["m.id as value", "CONCAT_WS(' ', m.firstname,m.lastname) as label"]);
            $this->db->from("tbl_member as m");
            $this->db->where("m.id", $assign_to);

            $res = $this->db->get()->row();


        return $res;
    }

    function check_email_address_is_uniqe_of_contact($email, $contactId = 0) {
        $this->db->select(["pe.email", "concat_ws(' ',firstname,lastname) as contact_name", "p.id"]);
        $this->db->from("tbl_person as p");
        $this->db->join("tbl_person_email as pe", "pe.person_id = p.id and pe.archive = 0", "INNER");
        $this->db->where("pe.email", trim($email));
        $this->db->where("p.archive", 0);

        if ($contactId) {
            $this->db->where("p.id !=", $contactId);
        }

        return $this->db->get()->row();
    }

    /*
     * Update the task status
     * @param {int} task_id - used to update the data by increament id
     * @param {int} adminId - used to update the data of updated_by
     * return type array
     */
    public function complete_task($task_id, $adminId)
    {
        $where = array();
        $rows = array();
        if (!empty($task_id)) {

            $data = array('updated_at' => date('Y-m-d h:i:s'), 'task_status' => 1, "updated_by" => $adminId);
            $where = array('id' => $task_id);
            $rows = $this->basic_model->update_records('crm_participant_schedule_task', $data, $where);


            return (array('status' => true, 'data' => $rows));
        }
    }

     /*
     * Get Mulitple Filter Datas
     * @filterdatas - getting all filtered datas with operators
     * return type array
     */

    public function get_selectedfilter_contacts($filterdatas,$limit,$page,$filter_logic,$filter_operand_length, $uuid_user_type)
    {
        $createdByNameSubQuery = $this->get_contact_created_by_sub_query("p",$uuid_user_type);

        $select_column = ["p.contact_code", "p.created", "p.id","p.created_by",'p.type','p.status', 'p.archive'];
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("concat_ws(' ',firstname,lastname) as fullname");
        $this->db->select("(" . $createdByNameSubQuery . ") as created_by");
        $this->db->select("(select pt.name from tbl_person_type as pt where pt.id = p.type) as type");
        $this->db->select("(CASE
         WHEN p.status = 1 THEN 'Active'
         WHEN p.status = 0 THEN 'Inactive'
         Else '' end
        ) as status");
        $this->db->from('tbl_person as p');
        $this->db->where('p.archive', 0);
        $forhavingappend = [];
        $whereArrData = [];
        foreach ($filterdatas  as $filterdata) {
           $wherecondition = null;
           $filter_field = $filterdata['select_filter_field_val'];
           $filter_value = $filterdata['select_filter_value'];
           $filter_operator = $filterdata['select_filter_operator'];
           if($filter_field && $filter_value && $filter_operator){
            $wherecondition = $this->getoperator($filterdata,$forhavingappend,$filter_logic);
            array_push($whereArrData, $wherecondition);
            if(empty($filter_logic)){
                $this->db->where($wherecondition);
            }

         }
         if($filter_field == "created_by"){
            $forhavingappend[$filterdata['select_filter_field_val']][] = $wherecondition;
        }
        }

        // validating the filter logic ex (1 OR 2) AND 3
        $whereLogic =  $filter_logic;
        if(!empty($filter_logic)){
            if($filter_operand_length == count($whereArrData)){
                for($i = 1;$i<= 10;$i++){
                    $whereLogic = str_replace($i, "operand-".$i, $whereLogic);
                   }
                   for($i = 1;$i<= 10;$i++){
                       $searchVal = "operand-".$i;
                       if($i <= count($whereArrData) ){
                            $whereLogic = str_replace($searchVal, $whereArrData[$i-1], $whereLogic);
                       }
                   }
                //add alias to status
                $whereLogic = str_replace(['status'], ['p.status'], $whereLogic);
                $this->db->having($whereLogic);
           }else{
            $return = array('data' =>[], 'status' => false, 'msg'=>'filter_error', 'error'=> 'Filter value is null');
            return $return;
           }
        }
        $this->db->limit($limit, ($page * $limit));
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $qs = $this->db->last_query();

        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        $result = $query->result();
        $return = array('data' => $result, 'status' => true, 'total_item' => $total_item);
        return $return;
    }

    /*
     * Get Mulitple Filter Datas
     * @filter - Forming filter conditions with db field values
     * return type array
     */

    function getoperator($filter,$forhavingappend,$filter_logic){
        $cond = null;
        $select_filter_field_val = $filter["select_filter_field_val"];
        $select_filter_value = $filter["select_filter_value"];
        $select_filter_operator = $filter["select_filter_operator"];

        $contact_type_array = array('1'=>"Applicant",'2'=>"Lead",'3'=>"Participant",'4'=>"Booker",'5'=>"Agent",'6'=>"Organisation");
        $status_option_array = array('1'=>"Active",'0'=>"In Active");

        // Check Created Date Query
        if($filter["select_filter_field_val"] == 'created'){
            $date_operator = $filter["select_filter_operator"];
            $date_opertr_sym = $filter["select_filter_operator_sym"];
            if($filter["selected_date_range"] && $filter["selected_date_range"]!="" && !empty($filter["selected_date_range"])){
                $start_end_date = explode("," , $filter["selected_date_range"]);
                if(count($start_end_date)>1){
                    return $this->createdDateConditionsCustomField($date_operator,$start_end_date[0],$start_end_date[1],$date_opertr_sym);
                }else{
                    $formated_date = date('Y-m-d',strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                    return $this->createdDateConditionsForSpecificField($date_operator,$formated_date,$date_opertr_sym);
                }
            }else{
                $formated_date = date('Y-m-d',strtotime(str_replace('/', '-', $filter["select_filter_value"])));
                return $this->createdDateConditionsForSpecificField($date_operator,$formated_date,$date_opertr_sym);
                          
            }
            
            
        }

        // Check Type Cast Query

        if($select_filter_field_val == "type" || $select_filter_field_val == "p.type"){
            $type_operator = $filter["select_filter_operator"];
            $type_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value,$contact_type_array))
            {
            $select_filter_value= array_search($select_filter_value, $contact_type_array);
            }
            return $this->typeConditions($type_operator,$select_filter_value,$select_filter_field_val,$type_opertr_sym);
         }

         // Check Status Types Integer Type Query

        else if($select_filter_field_val == "status"){
            $status_operator = $filter["select_filter_operator"];
            $status_opertr_sym = $filter["select_filter_operator_sym"];
            if (in_array($select_filter_value,$status_option_array))
            {
            $select_filter_value = array_search($select_filter_value, $status_option_array);
            }
            return $this->statusConditions($status_operator,$select_filter_value,$select_filter_field_val,$status_opertr_sym);
        }

        // Check Full Names CONCAT Type Query

        if($select_filter_field_val == "fullname"){
            $setsymbol = $filter["select_filter_operator_sym"];
            $fullname_operator = $filter["select_filter_operator"];
            return $this->fullnameConditions($setsymbol,$fullname_operator,$select_filter_value);
        }

        if($select_filter_field_val == "created_by"){
            $setsymbol = $filter["select_filter_operator_sym"];
            $creby_operator = $filter["select_filter_operator"];
            return $this->createdByConditions($setsymbol,$creby_operator,$select_filter_value,$forhavingappend,$filter_logic);
        }
        $operator =  $select_filter_operator ?? "";
        $filter_value =  $select_filter_value ?? "";
        $field_val =  $select_filter_field_val ?? "";
        if($operator && $filter_value && $field_val){
         return $this->switchCaseOperators($operator,$filter_value,$field_val);
        }

    }
    function createdByConditions($setsymbol,$operator,$filter_value,$forhavingappend,$filter_logic){
        $cond = null;
        $setnotEqual = '';
        $secondpercentage = "%";
        $firstPercenatge = "%";
        if($operator == "equals"){
            $firstPercenatge = "";
            $secondpercentage = "";
          }
        if($operator == "does not contains"){
            $setsymbol =  " NOT LIKE ";
            $setnotEqual =   "OR `created_by` IS NULL";
        }
        else if($operator == "contains"){
            $setsymbol = " LIKE ";
        }
        else if($operator == "starts with"){
            $setsymbol = " LIKE ";
            $firstPercenatge = "";
        }
        else if($operator == "not equal to"){
          $setnotEqual =  "OR `created_by` IS NULL";
          $firstPercenatge = "";
          $secondpercentage = "";
        }
        if(count($forhavingappend) == 0 && !empty($filter_logic))
        {
        //  $cond .= "`p`.`archive` = 0 ON HAVING (`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";
        $cond .= "`p`.`archive` = 0  And (`p`.`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";

        }
        else if(count($forhavingappend) == 0)
        {
        //  $cond .= "`p`.`archive` = 0  HAVING(`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";
        $cond .= "`p`.`archive` = 0  And (`p`.`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";

        }else{
         $cond .= "(`created_by` ".$setsymbol." '".$firstPercenatge.$filter_value.$secondpercentage."' ".$setnotEqual."    )";
        }
        return $cond;
    }
    function fullnameConditions($setsymbol,$operator,$filter_value){
        $cond = null;
        $secondpercentage = "%";
        $firstPercenatge = "%";
        if($operator == "equals"){
            $firstPercenatge = "";
            $secondpercentage = "";
          }
        if($operator == "does not contains"){
            $setsymbol =  " NOT LIKE ";
        }
        if($operator == "contains"){
            $setsymbol = " LIKE ";
        }
        if($operator == "starts with"){
            $setsymbol = " LIKE ";
            $firstPercenatge = "";
        }
        if($operator == "not equal to"){
          $firstPercenatge = "";
          $secondpercentage = "";
        }
        //$cond .= "CONCAT(`firstname`,' ',`lastname`)" .$setsymbol  . "'".$firstPercenatge.$filter_value.$secondpercentage."'";
        $cond .= "fullname" .$setsymbol  . "'".$firstPercenatge.$filter_value.$secondpercentage."'";
        return $cond;
    }
    function statusConditions($operator,$filter_value,$field_val,$opertr_sym){
        $cond = null;
        if($operator == "does not contains"){
            $cond .= "(CAST(status AS INTEGER) NOT LIKE '".$filter_value."'". ' OR  '. "`status` IS NULL)";
        }
        else if($operator == "contains" || $operator == "starts with"){
            $cond .= "CAST(status AS INTEGER) LIKE '".$filter_value."%'";
        }
        else if($operator == "not equal to"){
            $cond .= "(".$field_val . $opertr_sym . $filter_value . ' OR  '. "`status` IS NULL".")";
        }
        else{
            $cond .= $field_val . $opertr_sym . $filter_value;
            }
        return $cond;
    }

    function typeConditions($operator,$filter_value,$field_val,$opertr_sym){
        $cond = null;
    if($operator == "does not contains"){
        $cond .= "(CAST(`p`.type AS INTEGER) NOT LIKE '".$filter_value."'". ' OR  '. "`p`.`type` IS NULL)";
    }
    else if($operator == "contains" || $operator == "starts with"){
        $cond .= "CAST(`p`.type AS INTEGER) LIKE '".$filter_value."%'";
    }
    else if($operator == "not equal to"){
        $cond .= "(`p`.".$field_val . $opertr_sym . $filter_value . ' OR  '. "`p`.`type` IS NULL".")";
    }
    else{
        $cond .= '`p`.'.$field_val . $opertr_sym . $filter_value;
        }
    return $cond;
    }
    // validate the specific filed date
    function createdDateConditionsForSpecificField($operator,$formated_date,$opertr_sym){
        $cond = null;
        if($operator == "does not contains"){
            $cond .= "DATE(created) NOT LIKE '%".$formated_date."'";
        }else if($operator == "contains"){
            $cond .= "DATE(created) LIKE '%".$formated_date."'";
        }else if($operator == "starts with"){
            $cond .= "DATE(created) LIKE '".$formated_date."%'";
        }else{
            $cond .= "DATE(created) ".$opertr_sym." '".$formated_date."'";
        }
        return $cond;
    }
    // validate the custome filed date ex: this week, this month
    function createdDateConditionsCustomField($operator,$start_date,$end_date,$opertr_sym){
        $cond = null;
    
        if($operator == "equals" || $operator == "contains" || $operator == "starts with"){
            $cond .= "DATE(created) BETWEEN '".$start_date."' AND '".$end_date."' ";
        }else if($operator == "not equal to" || $operator == "does not contains"){
            $cond .= "DATE(created) NOT BETWEEN '".$start_date."' AND '".$end_date."' ";
        }else{
            $cond .= "DATE(created) ".$opertr_sym." '".$start_date."'";
        }
        return $cond;
    }

    //Switch Cases For Operators
    function switchCaseOperators($operator,$filter_value,$field_val){
        $cond = null;
        switch ($operator) {
        case 'equals':
            $cond .= $field_val . ' = "' . $filter_value.'"';
            break;
        case 'not equal to':
            $cond .= $field_val . ' <> "' .  $filter_value.'"';
            break;
        case 'less than':
            $cond .= $field_val . ' < "' .  $filter_value.'"';
            break;
        case 'greater than':
            $cond .= $field_val . ' > "' .  $filter_value.'"';
            break;
        case 'less or equal':
            $cond .= $field_val . ' <= "' .  $filter_value.'"';
            break;
        case 'greater or equal':
            $cond .= $field_val . ' >= "' .  $filter_value.'"';
            break;
        case 'contains':
            $cond .= $field_val . " LIKE '%" . $filter_value. "%'";
            break;
        case 'does not contains':
            $cond .= $field_val . " NOT LIKE '%" . $filter_value. "%'";
            break;
        case 'starts with':
            $cond .= $field_val . " LIKE '" . $filter_value. "%'";
            break;
        default:
            break;
    }
    return $cond;
    }

    /**
     * get the related to type id
     * @param {str} related_type
     */
    function give_id_of_related_type($related_type) {
        switch($related_type) {
            case "opportunity":
                $source_data_type = 1;
                break;
            case "lead":
                $source_data_type = 2;
                break;
            case "service":
                $source_data_type = 3;
                break;
            case "need":
                $source_data_type = 4;
                break;
            case "risk":
                $source_data_type = 5;
                break;
            case "shift":
                $source_data_type = 6;
                break;
            case "timesheet":
                $source_data_type = 7;
                break;
            case "application":
                $source_data_type = 8;
                break;
            case "interview":
                $source_data_type = 9;
                break;
            case "invoice":
                $source_data_type = 10;
                break;
            default:
                $source_data_type = 0;
                break;
        }

        return $source_data_type;
    }

    //Get the gender options data from tbl_references table based on the key reference
    public function get_gender_option() {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'gender' AND rdt.archive = 0", "INNER");

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /*
     * its use for get contact/person details
     *
     * @params $contactId
     *
     * return type array
     * return contact details
     */

    function get_contact_details_by_id($contactId) {
        $this->db->select(["concat_ws(' ',firstname,lastname) as label", 'p.id as value']);
        $this->db->from(TBL_PREFIX . 'person as p');
        $this->db->where(['p.archive' => 0, 'p.id' => $contactId]);
        $this->db->order_by('p.id', 'ASC');
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /**
     * Get contact phone no
     * - Activity tab (Contact Detail)
     * @param {int} $person_id
     */
    public function get_contact_data_for_sms($reqData) {
        $return = ['status' => true, 'data' => ''];
        if (!empty($reqData->person_id)) {
            $select_column = array('p.id as person_id', 'concat(p.firstname," ",p.lastname) as name', 'pp.phone');
            $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            $this->db->from(TBL_PREFIX . 'person as p');
            $this->db->join(TBL_PREFIX . "person_phone as pp", "pp.person_id = p.id AND pp.archive = 0 AND pp.primary_phone = 1", "INNER");
            $this->db->where('p.id', $reqData->person_id);
            $query = $this->db->get();
            $result = $query->result();

            $data = [];
            foreach($result as $p_in => $person) {
                $data_temp = [];
                $data_temp['id'] = $p_in + 1;
                $data_temp['label'] = $person->name;
                $data_temp['value'] = $person->person_id;
                $data_temp['phone'] = $person->phone;
                $data[] = (object) $data_temp;
            }
            $return = ['status' => true, 'data' => $data];  
        }
        return $return;
    }
}
