<?php

defined('BASEPATH') OR exit('No direct script access allowed');

//class Master extends MX_Controller
class Lead_model extends Basic_Model {

    function __construct() {

        parent::__construct();
    }

    public function get_owner_staff_by_name($ownerName = '') {
        $this->db->like('label', $ownerName);
        $queryHavingData = $this->db->get_compiled_select();
        $queryHavingData = explode('WHERE', $queryHavingData);
        $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';

        $this->db->select(["CONCAT_WS(' ',m.firstname,m.lastname) as label", 'm.id as value']);
        $this->db->from(TBL_PREFIX . 'member as m');
        $this->db->join(TBL_PREFIX . "department as d", "d.id = m.department AND d.short_code = 'internal_staff'", "inner");
        $this->db->where(['m.archive' => 0]);
        $this->db->having($queryHaving);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_lead_source() {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'lead_source' AND rdt.archive = 0", "INNER");

        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /**
     * it validates and creates the lead data into database
     * along with person details
     */
    public function create_lead($reqData, $adminId = null) {
        require_once APPPATH . 'Classes/person/person.php';
        $objPerson = new PersonClass\Person();
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        if (!empty($reqData->data)) {
            $data = (array) $reqData->data;
            $data['id'] = 0;
            $validation_rules = [
                array('field' => 'lastname', 'label' => 'Last Name', 'rules' => 'required|max_length[40]', 'errors' => ['max_length' => "%s field cannot exceed 40 characters."]),
                array('field' => 'lead_topic', 'label' => 'Topic', 'rules' => 'required')
            ];
            $chk_email = '';
            $chk_phone = '';
            foreach ($data['EmailInput'] as $val) {
                $val = (object) $val;
                $chk_email = $val->email;
            }
            foreach ($data['PhoneInput'] as $val) {
                $val = (object) $val;
                $chk_phone = $val->phone;
            }

            if (empty($chk_email) && empty($chk_phone) ) {
                return [
                    'status' => false,
                    'error' => 'Phone or Email is required',
                ];
            }
            if (!empty($reqData->data->EmailInput)) {
                if(!empty($chk_email)){
                    $validation_rules[] = array('field' => 'EmailInput[]', 'label' => 'Email', 'rules' => 'callback_check_valid_email_address[email]');
                }
            }

            if (!empty($reqData->data->PhoneInput)) {
                $validation_rules[] = array('field' => 'PhoneInput[]', 'label' => 'Phone', 'rules' => 'callback_phone_number_check[phone,,Please enter valid phone number.]');
            }

            $this->form_validation->set_data($data);
            $this->form_validation->set_rules($validation_rules);
            if ($this->form_validation->run()) {
                $statusType = $this->Lead_model->get_lead_status_id_by_key_name('open');
                $org_email = '';
                if (!empty($data['EmailInput'])) {
                    foreach ($data['EmailInput'] as $val) {
                        $org_email = $val->email;
                    }
                }

                $org_phone = '';
                if (!empty($data['PhoneInput'])) {
                    foreach ($data['PhoneInput'] as $val) {
                        $org_phone = $val->phone;
                    }
                }
                $insData = [
                    'firstname' => $data['firstname'] ?? '',
                    'lastname' => $data['lastname'] ?? '',
                    'email' => $org_email,
                    'phone' => $org_phone,
                    'lead_status' => $statusType,
                    'lead_converted' => 0,
                    'archive' => 0,
                    'created' => DATE_TIME,
                    'created_by' => $adminId ?? 0,
                    'lead_topic' => $data['lead_topic'] ?? '',
                    'lead_owner' => $data['lead_owner']->value ?? 0,
                    'lead_description' => $data['lead_description'] ?? '',
                    'lead_company' => $data['lead_company'] ?? '',
                    'lead_source_code' => $data['lead_source_code'] ?? 0,
                    'referrer_firstname' => $data['referrer_firstname'] ?? '',
                    'referrer_lastname' => $data['referrer_lastname'] ?? '',
                    'referrer_email' => $data['referrer_email'] ?? '',
                    'referrer_phone' => $data['referrer_phone'] ?? '',
                    'referrer_relation' => $data['referrer_relation'] ?? '',
                    'middlename'=>$data['middlename'] ??'',
                    'previous_name'=>$data['previous_name'] ?? ''
                ];

                $leadId = $this->basic_model->insert_records('leads', $insData);

                if ($leadId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);
                    $first_name = $data['firstname'] ?? '' ;
                    $last_name = $data['lastname'] ?? '';
                    $this->loges->setTitle("New Lead created for " . $adminName . " " . $last_name . " by " . $adminName);  // set title in log
                    $this->loges->setSpecific_title("New Lead created for " . $adminName . " " . $last_name . " by " . $adminName);  // set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($leadId);
                    $this->loges->setCreatedBy($adminId);
                    $this->loges->createLog(); // create log
                    //create history
                    $this->load->model('sales/Leadhistory_model');
                    $this->Leadhistory_model->updateHistory(['id' => $leadId, 'created' => ''], $insData, $adminId);

                    $response = ['status' => true, 'msg' => 'Lead has been created successfully.'];
                } else {
                    $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
                }
            } else {
                $errors = $this->form_validation->error_array();
                $response = ['status' => false, 'error' => implode(', ', $errors)];
            }
        }
        return $response;
    }

    public function check_person_duplicate_email($email = '', $id = 0) {
        $type = get_person_type('lead');
        $this->db->select('pe.email');
        $this->db->from(TBL_PREFIX . 'person_email as pe');
        $this->db->join(TBL_PREFIX . "person as p", "p.id = pe.person_id and pe.archive=p.archive AND p.type = '" . $type . "'", "inner");
        $this->db->where(['pe.archive' => 0, 'pe.person_id !=' => $id, 'pe.email' => $email]);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->row_array() : [];
    }

    public function get_lead_status() {
        $this->db->select(["ls.name as label", 'ls.id as value']);
        $this->db->from(TBL_PREFIX . 'lead_status as ls');
        $this->db->where(['ls.archive' => 0]);
        $this->db->order_by('ls.order_ref', 'ASC');
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    public function get_leads_list($reqData, $filter_condition = '') {
        $ownerNameSubQuery = $this->get_member_name_sub_query(['type' => 1]);
        $createdByNameSubQuery = $this->get_member_name_sub_query(['type' => 2]);
        $statusNameSubQuery = $this->get_lead_status_name_sub_query();
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';        

        $src_columns = array('lead_number', 'lead_topic', 'person_name', 'lead_status', 'lead_owner', 'created_format', 'created_by_name');
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

        $available_column = array("lead_id", "id", "lead_number", "is_converted", "lead_topic","created_format",
                                   "lead_status", "person_name", "lead_owner", "created_by_name","lead_status_val"
        );
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
                if ($orderBy == 'created_format') {
                    $orderBy = "l.created";
                }
            }
        } else {
            $orderBy = 'l.id';
            $direction = 'DESC';
        }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(l.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(l.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(l.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(l.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }

        if (!empty($filter->filter_lead_status)) {
            $this->db->where('l.lead_status', $filter->filter_lead_status);
        }

        $this->load->file(APPPATH.'Classes/common/ViewedLog.php');
        $viewedLog = new ViewedLog();
        // get entity type value
        $entity_type = $viewedLog->getEntityTypeValue('lead');

        $select_column = array(
            "l.id as lead_id",
            "l.id as id",
            "l.lead_number",
            "l.is_converted",
            "l.lead_topic",
            "DATE_FORMAT(l.created,'%d/%m/%Y') as created_format",
            "CASE WHEN l.lead_status> 0 THEN COALESCE((" . $statusNameSubQuery . "),'') ELSE '' END as lead_status",
            "concat_ws(' ',l.firstname, l.lastname) as person_name",
            "CASE WHEN l.lead_owner> 0 THEN COALESCE((" . $ownerNameSubQuery . "),'') ELSE '' END as lead_owner",
            "CASE WHEN l.created_by> 0 THEN COALESCE((" . $createdByNameSubQuery . "),'') ELSE '' END as created_by_name",            
            'l.lead_status as lead_status_val'
        );

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
      
        $this->db->from(TBL_PREFIX . 'leads l');
       
        $this->db->where('l.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is useed for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        
        if(count($result)) {
            foreach($result as $key => $value)
            {
              $result[$key]->lead_status = ucfirst(strtolower($result[$key]->lead_status));
            }
        }
                
        if(!empty($result)) {

            $this->load->model('common/Common_model');
            $this->Common_model->get_viewed_log($result, $entity_type);
           
        }
        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'total_item' => $total_item);
        
    }
   
    /**
     * Update existing lead
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update_lead($id, array $data = [], $adminId = null) {
        $existingLead = $this->db->get_where('tbl_leads', ['id' => $id, 'archive' => 0], 1)->row_array();
        if (!$existingLead) {
            return [
                'status' => false,
                'error' => 'The lead you are trying to modify was either removed or marked as archived',
            ];
        }

        $org_email = '';
        if (!empty($data['EmailInput'])) {
            foreach ($data['EmailInput'] as $val) {
                $val = (object) $val;
                $org_email = $val->email;
            }
        }

        $org_phone = '';
        if (!empty($data['PhoneInput'])) {
            foreach ($data['PhoneInput'] as $val) {
                $val = (object) $val;
                $org_phone = $val->phone;
            }
        }

        $dataToBeUpdated = [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $org_email,
            'phone' => $org_phone,
            'lead_topic' => $data['lead_topic'] ?? '',
            'lead_owner' => ($data['lead_owner'] ?? [])['value'] ?? null,
            'lead_company' => $data['lead_company'],
            'lead_source_code' => $data['lead_source_code'],
            'lead_description' => $data['lead_description'],
            'lead_status' => $data['lead_status'],
            'referrer_firstname' => $data['referrer_firstname'] ?? '',
            'referrer_lastname' => $data['referrer_lastname'] ?? '',
            'referrer_email' => $data['referrer_email'] ?? '',
            'referrer_phone' => $data['referrer_phone'] ?? '',
            'referrer_relation' => $data['referrer_relation'] ?? '',
            'middlename'=>$data['middlename'] ??'',
            'previous_name'=>$data['previous_name'] ?? ''
        ];

        $success = $this->db->update('tbl_leads', $dataToBeUpdated, ['id' => $id]);
        //record the history
        $this->load->model('sales/Leadhistory_model');
        $this->Leadhistory_model->updateHistory($existingLead, $dataToBeUpdated, $adminId);
        if (!$success) {
            return [
                'status' => false,
                'error' => 'Unable to update lead',
            ];
        }

        return [
            'status' => true,
            'msg' => 'Successfully updated lead',
            'lead_id' => $id,
        ];
    }

    /**
     * Find lead by ID. Will also fetch related information on other tables
     *
     * @param int $id
     * @return array|null
     */
    public function get_lead_details($id) {
        $this->db->select("(select ls.key_name from tbl_lead_status as ls where ls.id = l.lead_status) as lead_status_key_name");
        $query = $this->db
                ->from('tbl_leads AS l')
                ->where([
                    'l.id' => $id,
                    'l.archive' => 0,
                ])
                ->select(['l.*'])
                ->get();

        $result = $query->row_array();

        if(empty($result['firstname']))
        {
            $x = explode(" ", $result['lastname']);
            if (count($x) > 1) {
                $count = count($x);
                $result['lastname_new'] = $x[$count - 1];

                unset($x[$count - 1]);
                $result['firstname_new'] = implode(" ", $x);
            }
            else
            {
                $result['firstname_new'] = "";
                $result['lastname_new'] =  $result['lastname'];

            }

        }

        if (!$result) {
            return null;
        }

        // retrieve related data
        // instead of using subqueries, let's just run another query
        //$result['person'] = $this->db->get_where('tbl_person', ['id' => $result['person_id'], 'archive' => 0], 1)->row_array();
//        if ($result['person']) {
//            $result['person'] = array_merge($result['person'], [
//                'emails' => $this->db->get_where('tbl_person_email', ['person_id' => $result['person_id'], 'archive' => 0])->result_array(),
//                'phones' => $this->db->get_where('tbl_person_phone', ['person_id' => $result['person_id'], 'archive' => 0])->result_array(),
//            ]);
//        }

        $result['emails'] = [["email" => $result['email']]];
        $result['phones'] = [["phone" => $result['phone']]];

        // Tip: When fetching tbl_members, let's not fetch sentitive information
        $result['lead_owner_member'] = $this->db
                ->from('tbl_member')
                ->where(['uuid' => $result['lead_owner'], 'archive' => 0])
                ->select(['firstname', 'lastname', 'id'])
                ->get()
                ->row_array();

        $result['lead_source_code_details'] = $this->db
                ->where(['id' => $result['lead_source_code'], 'archive' => 0])
                ->get('tbl_references', 1)
                ->row_array();
        $result['lead_docusign_datas'] = $this->lead_docusign_datas($id, 1);
        if (!empty($result['converted_contact_id'])) {
           $prow = $this->db
           ->from('tbl_person')
           ->where(['id' => $result['converted_contact_id'], 'archive' => 0])
           ->select(['profile_pic'])
           ->get()
           ->row_array();
           $result['avatar'] = $prow['profile_pic'];

        }
        return $result;
    }

    /**
     * Mark lead by given ID as archived.
     *
     * @param int $id
     * @return array
     */
    public function archive_lead($id) {
        // archive the lead, even if it is already archived
        $cond = ['id' => $id];
        $existingLead = $this->db->get_where('tbl_leads', $cond)->row_array();

        // Record already been destroyed, just return success anyway
        if (!$existingLead) {
            return [
                'status' => true,
                'id' => $id
            ];
        }

        // Archive lead anyway, even if it is already archived
        $this->db->update('tbl_leads', ['archive' => 1], $cond);

        return [
            'status' => true,
            'id' => $id,
        ];
    }

    /**
     * Determine member fullname by member ID. Used by `Lead_model::get_lead_details`
     *
     * @param int $member_id
     * @return null|string
     */
    protected function determine_lead_owner_fullname($member_id) {
        $member = $this->db->get_where('tbl_member', ['id' => $member_id, 'archive' => 0])->row_array();
        if (!$member) {
            return null;
        }

        return implode(' ', [$member['firstname'], $member['lastname']]);
    }

    private function get_person_name_sub_query() {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person sub_p');
        $this->db->where("sub_p.id=l.person_id and sub_p.archive=l.archive", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    private function get_member_name_sub_query($extraPamams = []) {
        $type = $extraPamams['type'] ?? 1;
        $typeData = ['1' => 'l.lead_owner', '2' => 'l.created_by'];
        $condition = $typeData[$type] ?? $typeData[1];
        if($condition=='l.created_by'){
            $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
            $this->db->from(TBL_PREFIX . 'users as u');
            $this->db->join(TBL_PREFIX . 'member as sub_m', "u.id = sub_m.uuid", "INNER");
            $this->db->where("sub_m.uuid = " .$condition, null, false);
            $this->db->limit(1);
        }else{
            $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
            $this->db->from(TBL_PREFIX . 'member sub_m');
            $this->db->where("sub_m.uuid=" . $condition . " and sub_m.archive=l.archive", null, false);
            $this->db->limit(1);
        }
       
        return $this->db->get_compiled_select();
    }

    private function get_lead_status_name_sub_query() {
        $this->db->select("sub_ls.name");
        $this->db->from(TBL_PREFIX . 'lead_status sub_ls');
        $this->db->where("sub_ls.id=l.lead_status and sub_ls.archive=l.archive", null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    public function get_lead_status_id_by_key_name($keyName = 'open') {
        $row = !empty($keyName) ? $this->basic_model->get_row('lead_status', ['id'], ['key_name' => $keyName, 'archive' => 0]) : false;
        return $row ? $row->id ?? 0 : 0;
    }

    /*
     * its use for convert lead to contact, organisation and opportunity
     *
     * @params $data, $adminId
     * $data reqdata
     * $adminId converted by user admin id
     *
     * number of Scenario total 3 for create only
     *
     * 1) phone account ($data["person_account"]) is not checked and contact is account ($data["contact_is_account"]) is not checked
     * action --- three record created organisation, contact and opportunityt
     * account attachment (tbl_opportunity.account_person) : - genarated account id attached
     *
     * 2) phone account ($data["person_account"]) is  checked and contact is account ($data["contact_is_account"]) is checked
     * action --- 2 record created contact and opportunityt
     * account attachment (tbl_opportunity.account_person) : - genrated contact id attached
     *
     * 3) phone account ($data["person_account"]) is  checked and contact is account ($data["contact_is_account"]) is not checked
     * action --- 3 record created 2 contact and 1 opportunityt
     * account attachment (tbl_opportunity.account_person) : - genrated 2 nd contact id (which come in account name key) attached
     *
     *
     * // Update Scenario (choose exixting) is different for convert lead
     *
     *  return lead details
     */

    function convert_lead($data, $adminId) {
        // get lead details

        $lead_details = $this->get_lead_details($data["lead_id"]);

        $data["lead_details"] = $lead_details;

        $converted_organisation_id = null;
        $converted_contact_id = null;
        $converted_opportunity_id = null;
        $converted_account_contact_id = null;
        $its_first_contact = true;

        // here decision taking of account
        if ($data["account_type"] == 1) {
            if (empty($data["person_account"]) && empty($data["contact_is_account"])) {

                $converted_organisation_id = $this->create_organisation_from_lead($data, $adminId);
                $data["account_person"] = $converted_organisation_id;
                $data["account_user_type"] = 2; // account type 2 mean account person is organisation
            }

            if (!empty($data["person_account"]) && !empty($data["contact_is_account"])) {
                // orgaganisation not created
                $converted_organisation_id = null;

                // here person_account and contact_is_account is checked then contact will be created
                $converted_contact_id = $this->create_contact_from_lead($data, $adminId, $data['org_name'], true,$its_first_contact);
                $data["account_person"] = $converted_contact_id;
                $data["account_user_type"] = 1;
            }

            if (!empty($data["person_account"]) && empty($data["contact_is_account"])) {
                // orgaganisation not created
                $converted_organisation_id = null;

                // here person_account is checked and contact_is_account is note checked then contact will be created
                // here last parameter true its not main contact its account_type contact and 1 another contact will create
                // so its use for skip to copy lead email to contact email
                $its_first_contact = false;
                $converted_account_contact_id = $this->create_contact_from_lead($data, $adminId, $data["org_name"], true, $its_first_contact);
                $data["account_person"] = $converted_account_contact_id;
                $data["account_user_type"] = 1; // account type one mean account person is contact
            }
        } else {
            $account = $data["exixting_org"];
            if ($account['type'] == 1) {
                $converted_account_contact_id = $account['value'];
            } else {
                $converted_organisation_id = $account['value'];
            }

            // here account type 1 mean contact/person and account type is 2 mean organisation
            $data["account_person"] = $account['value'];
            $data["account_user_type"] = $account['type'];
        }
        // here decision taking of contact
        if ($data["contact_type"] == 1) {
            if ($data["account_type"] == 1) {
                if (empty($data["person_account"]) && empty($data["contact_is_account"])) {
                   // create contact from lead
                    $converted_contact_id = $this->create_contact_from_lead($data, $adminId, $data['contact_name']);

                }

                if (!empty($data["person_account"]) && !empty($data["contact_is_account"])) {
                    // nothing to do bcz person_account and contact_is_account is checked
                }

                if (!empty($data["person_account"]) && empty($data["contact_is_account"])) {

                    // second contact create contact from lead
                    $converted_contact_id = $this->create_contact_from_lead($data, $adminId, $data['contact_name']);
                }
            }

            if ($data["account_type"] == 2) {
                $converted_contact_id = $this->create_contact_from_lead($data, $adminId, $data['contact_name']);
            }
        } else {
              if(empty($data["contact_is_account"]))
                 $converted_contact_id = $data["exixting_contact"]["value"];
        }

        // here decision taking of contact
        if ($data["opportunity_type"] == 1) {
            // create opportunity from lead
            $converted_opportunity_id = $this->create_opportunity_from_lead($data, $adminId);
            // Get activity list
            $entity_type_log = 4;
            // $activit_list = $this->get_activity_list($data["lead_id"], $entity_type_log);
            $activity = [];
            $activity['salesId'] = $data["lead_id"];
            $activity['sales_type'] = "lead";
            $activity['entity_type'] = $entity_type_log;
            $activity['related_type'] = 2;

            $activity_log = (object) $activity;

            $this->load->model("Contact_model");
            $activit_list = $this->Contact_model->get_acitvity_as_per_entity_id_and_type($activity_log);
            // pr($activit_list);
            // Create List of task
            if(!empty($activit_list)){
                foreach ($activit_list as $val) {
                    $converted_task_id = $this->create_task_from_lead_to_opportunity($val, $adminId, $converted_opportunity_id, $converted_contact_id, $data["lead_id"]);
                }
            }
        } else {
            $converted_opportunity_id = $data["exixting_opportunity"]["value"];
        }



        // make relation to each other
        $relation_data = [];
        if ($converted_organisation_id) {
            $relation_data[] = ["source_data_id" => $converted_organisation_id, "source_data_type" => 2, "destination_data_id" => $converted_contact_id, "destination_data_type" => 1, "is_primary" => 1];
            $relation_data[] = ["source_data_id" => $converted_organisation_id, "source_data_type" => 2, "destination_data_id" => $converted_opportunity_id, "destination_data_type" => 3, "is_primary" => 1];

            $relation_data[] = ["source_data_id" => $converted_contact_id, "source_data_type" => 1, "destination_data_id" => $converted_organisation_id, "destination_data_type" => 2, "is_primary" => 1];

            $relation_data[] = ["source_data_id" => $converted_opportunity_id, "source_data_type" => 3, "destination_data_id" => $converted_organisation_id, "destination_data_type" => 2, "is_primary" => 1];
        }

        $relation_data[] = ["source_data_id" => $converted_contact_id, "source_data_type" => 1, "destination_data_id" => $converted_opportunity_id, "destination_data_type" => 3, "is_primary" => 1];
        $relation_data[] = ["source_data_id" => $converted_opportunity_id, "source_data_type" => 3, "destination_data_id" => $converted_contact_id, "destination_data_type" => 1, "is_primary" => 1];

        if ($converted_account_contact_id) {
            $relation_data[] = ["source_data_id" => $converted_account_contact_id, "source_data_type" => 1, "destination_data_id" => $converted_contact_id, "destination_data_type" => 1, "is_primary" => 1];
            $relation_data[] = ["source_data_id" => $converted_account_contact_id, "source_data_type" => 1, "destination_data_id" => $converted_opportunity_id, "destination_data_type" => 3, "is_primary" => 1];

            $relation_data[] = ["source_data_id" => $converted_contact_id, "source_data_type" => 1, "destination_data_id" => $converted_account_contact_id, "destination_data_type" => 1, "is_primary" => 2];

            $relation_data[] = ["source_data_id" => $converted_opportunity_id, "source_data_type" => 3, "destination_data_id" => $converted_account_contact_id, "destination_data_type" => 1, "is_primary" => 1];
        }

        if (!empty($relation_data)) {
            foreach ($relation_data as $val) {

                $where = ["source_data_id" => $val["source_data_id"], "source_data_type" => $val["source_data_type"], "destination_data_id" => $val["destination_data_id"], "destination_data_type" => $val["destination_data_type"]];
                $res = $this->basic_model->get_row("sales_relation", ["id"], $where);

                if (empty($res)) {
                    // check already exist primary role
                    $where = ["source_data_id" => $val["source_data_id"], "source_data_type" => $val["source_data_type"], "destination_data_type" => $val["destination_data_type"], "is_primary" => 1];
                    $check_already_exist_primary = $this->basic_model->get_row("sales_relation", ["id"], $where);

                    if (!empty($check_already_exist_primary)) {
                        $val["is_primary"] = 2;
                    }

                    $this->basic_model->insert_records("sales_relation", $val, false);
                }
            }
        }

        // get lead status when lead is converted
        $lead_status = $this->get_lead_status_id_by_key_name('qualified');

        // update lead details as its converted
        $update_lead = [
            'converted_opportunity_id' => $converted_opportunity_id,
            'converted_organisation_id' => $converted_organisation_id,
            'converted_contact_id' => $converted_contact_id,
            'converted_account_contact_id' => $converted_account_contact_id,
            'converted_date' => DATE_TIME,
            'is_converted' => 1, // set status 1 when its converted
            'converted_by' => $adminId,
            'updated' => DATE_TIME,
            'lead_status' => $lead_status,
            "person_account" => $data["person_account"],
            "contact_is_account" => $data["contact_is_account"],
        ];

        $where = ["id" => $data["lead_id"]];
        $this->basic_model->update_records("leads", $update_lead, $where);        
        //create history
        $this->load->model('sales/Leadhistory_model');
        $this->Leadhistory_model->updateHistory(['id' => $data["lead_id"], 'lead_status' => $lead_details['lead_status']], ['lead_status' => $lead_status], $adminId);
        return $lead_details["lead_number"];
    }


    /*
     * its use for create list of task while converting lead to opportunity
     *
     * @params $data, $adminId
     * $data reqdata
     * $adminId converted by user admin id
     *
     * return type id
     * $converted_contact_id
     * $converted_opportunity_id
     */

    function create_task_from_lead_to_opportunity($data, $adminId, $salesId,$converted_contact_id, $lead_id) {
        $default_priority = 2;
        $activity_type = $data->activity_type;
        $activity_id = $data->activity_id;

        /**
         * activity_type
         * 1 - Task
         * 2 - Email
         * 3 - Call
         */
        switch($activity_type) {
            case 1:
                /*
                 * Update participant schedule task
                 */
                $task_data = [
                    "updated_by" => $adminId
                ];
                // if entity_type equalto 4 and ( contactId is equal to lead_id or lead_id equal to lead_id )then update the mapping lead into converted contact
                if (($data->entity_type == 4 && $data->contactId == $lead_id ) || ($data->entity_type == 4 && $data->lead_id == $lead_id)) {
                    $task_data["crm_participant_id"] = $converted_contact_id ?? null;
                    $task_data["entity_id"] = $salesId;
                    $task_data["lead_id"] = '';
                    $task_data["entity_type"] = 3;
                }

                // if related_type equalto 2 and related_to is equal to lead_id then update the mapping lead into converted opportunity
                if ($data->related_type == 2 && $data->related_to == $lead_id) {
                    $task_data["related_to"] = $salesId;
                    $task_data["related_type"] = 1;
                }

                $where = array(
                    "id" => $data->taskId
                );
                $converted_task_id = $this->basic_model->update_records("crm_participant_schedule_task", $task_data, $where);
                // update activity log type 1 - task
                $activity_data = array(
                    "entity_id" => $salesId,
                    "entity_type" => 3,
                );

                // if entity_type equalto 4 and contactId is equal to lead_id then update the mapping lead into converted contact
                if (($data->entity_type == 4 && $data->contactId == $lead_id ) || ($data->entity_type == 4 && $data->lead_id == $lead_id)) {
                    $activity_data["contactId"] = $converted_contact_id;
                    $activity_data["entity_id"] = $salesId;
                    $activity_data["lead_id"] = '';
                    $activity_data["entity_type"] = 3;
                }

                // if related_type equalto 2 and related_to is equal to lead_id then update the mapping lead into converted opportunity
                if ($data->related_type == 2 && $data->related_to == $lead_id) {
                    $activity_data["related_to"] = $salesId;
                    $activity_data["related_type"] = 1;
                }

                $where = array(
                    "id" => $activity_id
                );

               $activityId = $this->basic_model->update_records("sales_activity", $activity_data, $where);
                break;

            case 2:
                $activity_data = array(
                    "entity_id" => $salesId,
                    "entity_type" => 3,
                );

                // if entity_type equalto 4 and contactId is equal to lead_id then update the mapping lead into converted contact
                if ($data->entity_type == 4 && ($data->contactId == $lead_id)) {
                    $activity_data["contactId"] = $converted_contact_id;
                    $activity_data["entity_id"] = $salesId;
                    $activity_data["lead_id"] = '';
                    $activity_data["entity_type"] = 3;
                }

                // if related_type equalto 2 and related_to is equal to lead_id then update the mapping lead into converted opportunity
                if ($data->related_type == 2 && $data->related_to == $lead_id) {
                    $activity_data["related_to"] = $salesId;
                    $activity_data["related_type"] = 1;
                }

                $where = array(
                    "id" => $activity_id
                );

                $activityId = $this->basic_model->update_records("sales_activity", $activity_data, $where);

                // Update the recipient mapping if it is lead
                $activity_data = array(
                    "recipient" => $converted_contact_id,
                    "entity_type" => 1,
                );

                $where = array(
                    "type" => 2,
                    "recipient" => $lead_id,
                    "activity_id" => $activity_id
                );

                $activityRecipientId = $this->basic_model->update_records("sales_activity_recipient", $activity_data, $where);
                break;

            case 3:

                 $activity_data = array(
                    "entity_id" => $salesId,
                    "entity_type" => 3,
                );

                // if related_type equalto 4 and related_to is equal to lead_id then update the mapping lead into converted contact
                if (($data->entity_type == 4 && $data->contactId == $lead_id ) || ($data->entity_type == 4 && $data->lead_id == $lead_id)) {
                    $activity_data["contactId"] = $converted_contact_id;
                    $activity_data["entity_id"] = $salesId;
                    $activity_data["lead_id"] = '';
                    $activity_data["entity_type"] = 3;
                }

                // if related_type equalto 2 and related_to is equal to lead_id then update the mapping lead into converted opportunity
                if ($data->related_type == 2 && $data->related_to == $lead_id) {
                    $activity_data["related_to"] = $salesId;
                    $activity_data["related_type"] = 1;
                }

                $where = array(
                    "id" => $activity_id
                );

                $activityId = $this->basic_model->update_records("sales_activity", $activity_data, $where);

                break;
            default:
                $activityId = 0;
                break;
        }

        return $activityId;
    }

    /*
     * its use get id of person type which pass key of person type
     *
     * @params $person_type_kay
     *
     * @return type id
     */

    function get_person_type_id_by_key($person_type_kay) {
        $res = $this->basic_model->get_row("person_type", ["id"], ["key_name" => $person_type_kay]);
        return $res->id ?? null;
    }

    /*
     * its use for create organisation form lead when convert lead
     *
     * @params $data, $adminId
     * $data reqdata
     * $adminId converted by user admin id
     *
     * return type id
     * $converted_organisation_id
     */

    function create_organisation_from_lead($data, $adminId) {
        $lead_person_details = $data["lead_details"];

        $org_data = [
            "name" => $data["org_name"],
            "abn" => '',
            "logo_file" => "",
            "parent_org" => '',
            "website" => '',
            "fax" => '',
            "status" => 1, // set default active status
            "archive" => 0, // set default not archive
            "created" => DATE_TIME,
            "source_type" => 3, // mean create from sales module
            "created_by" => $adminId,
            "owner" => $lead_person_details["lead_owner"],
        ];

        $converted_organisation_id = $this->basic_model->insert_records("organisation", $org_data, $multiple = FALSE);

        if (!empty($lead_person_details['phones'])) {
            $phone = $lead_person_details['phones'];

            foreach ($phone as $val) {
                $val = (object) $val;

                $org_phone = [
                    "organisationId" => $converted_organisation_id,
                    "phone" => $val->phone,
                    "primary_phone" => 1,
                    "archive" => 0,
                ];
            }

            if(!empty($org_phone)){
                $this->basic_model->insert_records("organisation_phone", $org_phone, $multiple = FALSE);
            }
        }
        return $converted_organisation_id;
    }

    /*
     * its use for create contact form lead when lead convert
     *
     * @params $data, $adminId, $contact_name, $contact_type
     * $data : reqdata
     * $adminId : converted by user admin id
     * $contact_name : contact name
     * $contact_type : when its true then copy person_account and contact_is_account
     * $its_first_contact : when its true do not copy copy email from lead because seconday contact are creating and email address already copied in first contact and so for duplicate email address we are not copy email address
     *
     * return type id
     * $converted_contact_id
     */

    function create_contact_from_lead($data, $adminId, $contact_name, $contact_type = false, $its_first_contact = true) {

        // include peson class for create person of account/organisation


        require_once APPPATH . 'Classes/person/person.php';
        $objPerson = new PersonClass\Person();
        $lead_person_details = $data["lead_details"];


            $x = explode(" ", $contact_name);

            if (count($x) > 1) {
                $count = count($x);
                $lastname = $x[$count - 1];

                unset($x[$count - 1]);
                $firstname = implode(" ", $x);
            } else {
                $firstname = $contact_name;
                $lastname = '';
            }


        if($its_first_contact === true)
        {


            if(@$data['first_name_contact'] && empty($data['contact_is_account']))
            {

                $firstname = $data['first_name_contact'];

            }
            if(@$data['first_name_account'] && !empty($data['contact_is_account'])){
                $firstname = $data['first_name_account'];
            }

            if(@$data['last_name_contact'] && empty($data['contact_is_account']) ){
                $lastname = $data['last_name_contact'];


            }
            if(@$data['last_name_account'] && !empty($data['contact_is_account'])){
                $lastname = $data['last_name_account'];


            }


        }


         $objPerson->setFirstName($firstname ?? '');

         $objPerson->setLastName($lastname ?? '');
         $objPerson->setMiddleName($lead_person_details['middlename'] ?? '');
         $objPerson->setPreviousName($lead_person_detail['previous_name'] ?? '');
        $objPerson->setOwner($lead_person_details["lead_owner"] ?? '');
        if ($contact_type) {
            $objPerson->setContact_is_account($data['contact_is_account'] ? 1 : 0);
            $objPerson->setPerson_account($data['person_account'] ? 1 : 0);
        }

        // set contact type person
        $person_type = $this->get_person_type_id_by_key("participant");

        $objPerson->setPersonType($person_type);
        $objPerson->setCreated_by($adminId);
        $objPerson->setStatus(1);
        if((!empty($data['person_account']) && !empty($data['ndis_number']) && !empty($data['contact_is_account'])) || (!empty($data['person_account']) && !empty($data['ndis_number']) && $its_first_contact === false))
        {

            $objPerson->setNdisNumber($data['ndis_number']);
        }

        // create person (contact) for organisation
        $converted_contact_id = $objPerson->createPerson();


        if (!empty($lead_person_details['emails']) && $its_first_contact && empty($data['email_contact']) && empty($data['email_account']) )
        {

            $objPerson->setPersonEmail($lead_person_details['emails']);
            $objPerson->insertEmail("email");
        }
        if(empty($data['person_account']) &&  empty($data['contact_is_account']))
        {


           if(@$data['email_contact']){

                $emails = [["email" => $data['email_contact']]];
                $objPerson->setPersonEmail($emails);
                $objPerson->insertEmail("email");
            }
            if(@$data['phone_contact']){

                $phones = [["phone" => $data['phone_contact']]];
                $objPerson->setPersonPhone($phones);
                $objPerson->insertPhone("phone");
            }

        }
        if(!empty($data['person_account']))
        {


            if(($its_first_contact === false &&  empty($data['contact_is_account'])) || ($its_first_contact === true &&  !empty($data['contact_is_account'])))
            {

                if(!empty($data['email_account']))
                  $emails = [["email" => $data['email_account']]];
                if(!empty($data['phone_account']))
                  $phones = [["phone" => $data['phone_account']]];


            }
            else
            {

                if(!empty($data['email_contact']))
                 $emails = [["email" => $data['email_contact']]];
                if(!empty($data['phone_contact']))
                  $phones = [["phone" => $data['phone_contact']]];


            }

           if(@$emails){
                $objPerson->setPersonEmail($emails);
                $objPerson->insertEmail("email");
            }
            if(@$phones){
                $objPerson->setPersonPhone($phones);
                $objPerson->insertPhone("phone");
            }

        }
        if (!empty($lead_person_details['phones']) && $its_first_contact && empty($data['phone_contact']) && empty($data['phone_account'])) {

            $objPerson->setPersonPhone($lead_person_details['phones']);
            $objPerson->insertPhone("phone");
        }

        return $converted_contact_id;
    }

    /*
     * its use for create opportunity from lead
     *
     * @params $data, $adminId
     * $data reqdata
     * $adminId converted by user admin id
     *
     * return type id
     * $converted_opportunity_id
     */

    function create_opportunity_from_lead($data, $adminId) {
        $lead_person_details = $data["lead_details"];

        // lead model of Opportunity
        $this->load->model("Opportunity_model");

        // get default status of opportunity
        $default_opportunity_status = $this->Opportunity_model->get_opportunity_initial_status_id();



        $insData = [
            'topic' => $data["opportunity_name"],
            'related_lead' => $data["lead_id"],
            'account_person' => $data["account_person"] ?? null,
            'account_type' => $data["account_user_type"] ?? null,
            'opportunity_type' => $data["lead_source_code"] ?? null,
            'opportunity_status' => $default_opportunity_status,
            'owner' => (!empty($lead_person_details["lead_owner"])) ? $lead_person_details["lead_owner"] : null,
            'created_by' => $adminId,
            'created' => DATE_TIME,
            'updated' => DATE_TIME,
            'archive' => 0,
        ];

        // insert opportunity
        $converted_opportunity_id = $this->basic_model->insert_records('opportunity', $insData);

        // new opp - create specialised field history
        $bSuccess = $this->db->insert(TBL_PREFIX . 'opportunity_history',
        [   'opportunity_id' => $converted_opportunity_id,
            'created_by' => $adminId,
            'created_at' => date('Y-m-d H:i:s') ]);
        $history_id = $this->db->insert_id();

        // should use: Opportunity_model->create_field_history_entry()
        $bSuccess = $this->db->insert(TBL_PREFIX . 'opportunity_field_history', [
            'history_id' => $history_id,
            'opportunity_id' => $converted_opportunity_id,
            'field' => 'converted',
            'prev_val' => $data["lead_id"],
            'value' => $converted_opportunity_id]);

        if (!$bSuccess) die('MySQL Error: ' . $this->db->_error_number());

        return $converted_opportunity_id;
    }

    /*
     * its use for call query check lead is already convert or lead is exist or not
     *
     * @params $lead_id
     *
     * return type array
     * ["status" => false, "Lead is already converted"]
     * ["status" => false, "Lead is not exist in database"]
     * ["status" => true];
     */

    function check_lead_is_already_converted($lead_id) {
        $this->db->select(["l.id", "l.is_converted"]);
        $this->db->from("tbl_leads as l");
        $this->db->where("l.id", $lead_id);

        $res = $this->db->get()->row();

        if (!empty($res)) {
            // here 1 mean lead is converted
            if ($res->is_converted == 1) {
                $return = ["status" => false, "error" => "Lead is already converted"];
            } else {
                $return = ["status" => true];
            }
        } else {
            $return = ["status" => false, "error" => "Lead is not exist in database"];
        }

        return $return;
    }

    /*
     * @query for check account name already exist
     *
     * @params $account_name
     *
     * return type query result
     */

    function check_organisation_name_should_be_uniqe($account_name) {

        $this->db->select(["o.id"]);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.archive", 0);
        $this->db->where("o.name", $account_name);

        return $this->db->get()->row();
    }

    /*
     * its use for find account name and contact name
     *
     * @params $req_data
     *
     * return type json
     *
     */

    function get_account_name_and_contact_name_search_option($reqData) {
        $reqData = (array) $reqData;

        $search = $reqData["search"] ?? '';
        $sql = [];

        $this->db->select(["name as label", "id as value", "'2' as type"]);
        $this->db->from("tbl_organisation as o");
        $this->db->where("o.archive", 0);
        $this->db->where("o.status", 1);
        $this->db->like("o.name", $search);

        $sql[] = $this->db->get_compiled_select();

        $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label", "p.id as value", "'1' as type"]);
        $this->db->from("tbl_person as p");
        $this->db->where("p.archive", 0);
        $this->db->where("p.status", 1);
        $this->db->like("concat(' ',p.firstname,p.lastname)", $search);

        $sql[] = $this->db->get_compiled_select();

        $sqlsting = implode(' union ', $sql);
        $query = $this->db->query($sqlsting . ' order by label');

        $result = $query->result();


        return $result;
    }

    function get_contact_name_search_option($reqData) {
        $reqData = obj_to_arr($reqData);

        $search = $reqData["search"] ?? '';

        if (!empty($reqData["exixting_org"])) {

            if ($reqData["exixting_org"]['type'] == 2) {
                $org_id = addslashes($reqData["exixting_org"]['value']);

                $this->db->where("p.id IN (select destination_data_id from tbl_sales_relation as sr where source_data_id = " . $org_id . " AND source_data_type = '2' AND destination_data_type = '1')", null, false);
            }
        }


        $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label", "p.id as value", "'2' as type"]);
        $this->db->from("tbl_person as p");
        $this->db->where("p.archive", 0);
        $this->db->where("p.status", 1);
        $this->db->like("concat_ws(' ',p.firstname,p.lastname)", $search);

        $result = $this->db->get()->result();

        return $result;
    }

    function get_opportunity_name_search_option($reqData) {
        $reqData = obj_to_arr($reqData);

        $search = $reqData["search"] ?? '';
        if (!empty($reqData["exixting_org"])) {

            $account_id = addslashes($reqData["exixting_org"]['value']);
            $account_type = addslashes($reqData["exixting_org"]['type']);

            $this->db->where("o.id IN (select destination_data_id from tbl_sales_relation as sr where source_data_id = " . $account_id . " AND source_data_type = " . $account_type . " AND destination_data_type = '3')", null, false);
        }

        $this->db->select(["CONCAT_WS(' ', o.topic,'-','(',o.opportunity_number,')') as label", "o.id as value"]);
        $this->db->from("tbl_opportunity as o");
        $this->db->where("o.archive", 0);
        $this->db->like("o.topic", $search);
        $this->db->or_like("o.opportunity_number", $search);

        $result = $this->db->get()->result();
//        last_query();
        return $result;
    }

    function update_status_lead($reqData, $adminId){
        $reqData = (object) $reqData;
        //get existing lead status for history
        $status_res = $this->basic_model->get_row("leads", ["lead_status"], ["id" => $reqData->lead_id]);

        $check_res = $this->check_lead_status_going_to_back_then_check_admin_permission($reqData, $adminId);

        if ($check_res["status"]) {
            $res = $this->basic_model->get_row("lead_status", ["key_name"], ["id" => $reqData->status]);
            $key_name = $res->key_name ?? '';


            if ($key_name == "unqualified") {
                $reson_data = [
                    "lead_id" => $reqData->lead_id,
                    "reason" => $reqData->reason_drop ?? "",
                    "reason_note" => $reqData->reason_note ?? "",
                ];

                $this->basic_model->update_records("lead_unqualified_reason", ["archive" => 1], ["lead_id" => $reqData->lead_id]);
                $this->basic_model->insert_records("lead_unqualified_reason", $reson_data, false);
            }

            $where = ["id" => $reqData->lead_id];
            $data = ["lead_status" => $reqData->status, "updated" => DATE_TIME];

            $this->basic_model->update_records("leads", $data, $where);
            //create history
            $this->load->model('sales/Leadhistory_model');
            $this->Leadhistory_model->updateHistory(['id' => $reqData->lead_id, 'lead_status' => $status_res->lead_status], ['lead_status' => $reqData->status], $adminId);
            $response = ['status' => true, 'msg' => 'Status updated successfully.'];
        } else {
            $response = $check_res;
        }

        return $response;
    }


     function check_lead_status_going_to_back_then_check_admin_permission($reqData, $adminId) {
        $opportunity = $this->basic_model->get_row("leads", ["lead_status"], ["id" => $reqData->lead_id]);
        $current_opportunity_status = $opportunity->lead_status ?? 0;

        $x = $this->basic_model->get_row("lead_status", ["order_ref"], ["id" => $current_opportunity_status]);

        $current_order = $x->order_ref ?? 0;

        $y = $this->basic_model->get_row("lead_status", ["order_ref"], ["id" => $reqData->status]);
        $update_order = $y->order_ref ?? 0;


        // then we have to check admin permission
        // its should be crm admin
        //
        // access_crm_admin
        if ($current_order > $update_order) {
            require_once APPPATH . 'Classes/admin/permission.php';

            $obj_permission = new classPermission\Permission();
            $result = $obj_permission->check_permission($adminId, "access_crm_admin");

            if (!$result) {
                return array('status' => false, 'error' => "Not have permission unset status of lead");
            }
        }

        return array('status' => true);
    }

     function get_unqualified_reason_of_nots($lead_id) {
        $res = $this->basic_model->get_row("lead_unqualified_reason", ["id", "reason", "reason_note"], ["archive" => 0, "lead_id" => $lead_id]);

        if (!empty($res)) {
            $this->db->select(["r.display_name"]);
            $this->db->from(TBL_PREFIX . 'references as r');
            $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = 'unqualified_reason_lead' AND rdt.archive = 0", "INNER");
            $this->db->where("r.id", $res->reason);
            $res->reason_label = $this->db->get()->row("display_name");
        }

        return $res;
    }
    function get_convertlead_related_contemail($email) {
        $this->db->select(["concat_ws(' ',p.firstname,p.lastname) as label","p.id as value", "'2' as type"]);
        $this->db->from(TBL_PREFIX . 'person_email as pe');
        $this->db->join(TBL_PREFIX . "person as p", "p.id = pe.person_id", "inner");
        $this->db->where('pe.email', $email,'pe');
        $this->db->where('pe.archive','0');
        $result = $this->db->get()->result();

        return $result;
    }

    /**
     * Get activity list based on entity id and entity type
     * @param {int} entity_id
     * @param {int} entity_type
     * return type array
     */
    function get_activity_list($entity_id, $entity_type) {
        $this->db->select(["*"]);
        $this->db->from(TBL_PREFIX . 'sales_activity');
        $this->db->where([ "entity_id" => $entity_id,  "entity_type" => $entity_type ]);
        $result = $this->db->get()->result_array();

        return $result;
    }

    /*
     * fetching the reference list of one reference type of lead service type
     */
    public function get_lead_service_type_ref_list($keyname, $return_key = false) {
        if($return_key)
            $this->db->select(["r.key_name as label", 'r.display_name as value']);
        else
            $this->db->select(["r.display_name as label", 'r.display_name as value']);
        
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $query = $this->db->get();
        if($query->num_rows() > 0 && $return_key == false)
            return $query->result_array();
        else if($query->num_rows() > 0 && $return_key == true) {
            $retrow = null;
            foreach($query->result_array() as $row) {
                $retrow[$row['label']] = $row['value'];
            }
            return $retrow;
        }
    }

    public function check_service_type_exist_ref($keyname, $display_name){
        $this->db->select(["r.id"]);        
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.key_name = '{$keyname}' AND rdt.archive = 0", "INNER");
        $this->db->where(["r.archive" => 0 , "r.display_name" => $display_name]);
        $result = $this->db->get()->row();        
        return $result;
    }

    /*
     * Get service agreement contract data by id
     * @param {int} id
     * return array
     */
    public function lead_docusign_datas($id, $type = NULL)
    {
        $data = [];
        $base_url = base_url('mediaShow/SA');
        $base_s3_url = base_url('mediaShow/s3');

        $query = $this->db
            ->select(array(
                "saa.*",
                "CONCAT_WS(' ', creator.firstname, creator.lastname) AS created_by_fullname",
                "(
                    CASE
                        WHEN saa.signed_status = 0 THEN 'Not Signed Yet'
                        WHEN saa.signed_status = 1 THEN 'Signed'
                    END
                ) AS contract_status",
                "CONCAT_WS(' ', l.firstname, l.lastname) as lead_name",
                "CONCAT_WS(' ', l.firstname, l.lastname) AS signed_by_name",
                "saa.signed_by AS signed_by_id",
                "(
                    CASE
                        WHEN saa.aws_uploaded_flag = 1 THEN
                        (CONCAT('".$base_s3_url."', '/', saa.id, '/', REPLACE(TO_BASE64(saa.file_path), '=', '%3D%3D'), '?ownload_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=true'))
                        ELSE
                        (CONCAT('".$base_url."', '/', saa.id, '?filename=', REPLACE(TO_BASE64(saa.signed_file), '=', '%3D%3D'), '&download_as=', REPLACE(saa.signed_file, ' ', ''), '&s3=false'))
                    END

                ) AS url",

                ));
            $this->db->from('tbl_service_agreement_attachment as saa');
            $this->db->join('tbl_leads AS l', 'l.id = saa.lead_id', 'LEFT');
            $this->db->join('tbl_member AS creator', 'saa.created_by = creator.uuid', 'LEFT');
            //$this->db->join('tbl_person AS person', 'saa.signed_by = person.id', 'LEFT');
            $this->db->where(['lead_id' => $id]);

            if($type) {
                $this->db->where(['saa.type' => $type]);
            }
            $query = $this->db->get();
            //echo $s = $this->db->last_query();die;
        // save result if nom_rows not equal to zero
        if (!empty($query)) {
            $data = $query->result();
        }

        return $data;
    }
}
