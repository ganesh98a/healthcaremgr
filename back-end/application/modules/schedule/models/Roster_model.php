<?php

class Roster_model extends CI_Model {

	# Roster status
	var $roster_status = [
        "1" => "Active",
        "2" => "InActive",
    ];

    # Roster stage values
    var $roster_stage = [
        "1" => "Open",
        "2" => "Finalise",
        "3" => "In progress",
        "4" => "Completed"
    ];

    # Roster end date option
    var $roster_end_date_options = [
        "1" => "End of 6 Weeks",
        "2" => "Custom Date"
    ];

    # Reference Data Type
    var $roster_data_type = [
        "roster_type" => "roster_type",
        "roster_funding_type" => "roster_funding_type"
    ];

	/*
     * To fetch the roster list
     */
    public function get_roster_list($reqData, $filter_condition = '', $adminId = null, $uuid_user_type = null) {

        if (empty($reqData)) return;

        $limit = $reqData->pageSize?? 99999;
        $page = $reqData->page?? 1;
        $sorted = $reqData->sorted?? [];
        $filter = $reqData->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)", "(CASE WHEN r.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = r.account_id) WHEN r.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = r.account_id) ELSE '' END)", "r.roster_no", "DATE_FORMAT(r.start_date,'%d/%m/%Y')", "DATE_FORMAT(r.end_date,'%d/%m/%Y')", "tref.display_name");
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
                if($filter_obj->select_filter_field == "account") {
                    $this->db->where("(CASE WHEN r.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = r.account_id) WHEN r.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = r.account_id) ELSE '' END) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "owner") {
                    $this->db->where("concat(m.firstname,' ',m.lastname) ".$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "roster_no") {
                    $this->db->where('r.roster_no '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "status_label") {
                    $this->db->where('r.status '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "stage_label") {
                    $this->db->where('r.stage '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "account_organisation_type") {
                    $this->db->where('tr.display_name '.$sql_cond_part);
                }
                if($filter_obj->select_filter_field == "start_date" || $filter_obj->select_filter_field == "end_date") {
                    $this->db->where('DATE_FORMAT(r.'.$filter_obj->select_filter_field.', "%Y-%m-%d") '.GetSQLOperator($filter_obj->select_filter_operator_sym), DateFormate($filter_obj->select_filter_value, 'Y-m-d'));
                }
            }
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }
        
        $available_columns = array('id', 'roster_no', 'firstname', 'lastname');
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_columns)) {
                $orderBy = $sorted[0]->id;
                if($orderBy == 'id'){
                    $orderBy = 'r.id';
                }
                else if($orderBy == 'roster_no'){
                    $orderBy = 'r.roster_no';
                }
                else if($orderBy == 'firstname'){
                    $orderBy = 'm.firstname';
                }
                else if($orderBy == 'lastname'){
                    $orderBy = 'm.lastname';
                }
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'r.id';
            $direction = 'ASC';
        }
        

        $select_column = ["r.id", "r.roster_no", "r.start_date", "r.end_date", "concat(m.firstname,' ',m.lastname) as owner_label", "concat(p.firstname,' ',p.lastname) as contact_label", "'' as actions", "r.contact_id", "r.owner_id", "r.account_type", "r.account_id", "r.status", "concat(cb.firstname,' ',cb.lastname) as created_by_label", "r.created", "tref.display_name as roster_type_label", "r.stage", "r.roster_type", "r.created_by","r.funding_type","r.end_date_option","trf.display_name as funding_type_label"];


        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(CASE WHEN r.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = r.account_id) WHEN r.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = r.account_id) ELSE '' END) as account");

        $status_label = "(CASE ";
        foreach($this->roster_status as $k => $v) {
            $status_label .= " WHEN r.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);

         $stage_label = "(CASE ";
        foreach($this->roster_stage as $k => $v) {
            $stage_label .= " WHEN r.status = {$k} THEN '{$v}'";
        };
        $stage_label .= "ELSE '' END) as stage_label";
        $this->db->select($stage_label);
        
        $this->db->from('tbl_roster as r');
        $this->db->join('tbl_member m', 'm.id = r.owner_id', 'left');
        $this->db->join('tbl_person as p', 'p.id = r.contact_id', 'left');
        $this->db->join('tbl_organisation as o', 'o.id = r.account_id AND r.account_type = 2', 'left');
        $this->db->join('tbl_references as tr', 'o.org_type  = tr.id AND tr.archive = 0', 'left');
        $this->db->join('tbl_references as tref', 'r.roster_type = tref.id AND tref.archive = 0', 'left');
        $this->db->join('tbl_references as trf', 'r.funding_type = trf.id AND trf.archive = 0', 'left');
        if($uuid_user_type==ADMIN_PORTAL || $uuid_user_type==MEMBER_PORTAL){
            $this->db->join('tbl_member cb', 'cb.uuid = r.created_by', 'left');
        }else {
            $this->db->join('tbl_person cb', 'cb.uuid = r.created_by', 'left');
        }   
        $this->db->where("r.archive", "0");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = $ci->db->last_query();

        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // Get the query result
        $result = $query->result();

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched roster list successfully',"last_query" => $last_query, 'total_item' => $total_item);
        return $return;
    }

    /*
     * To get refrence id of roster
     * 
     * return type array
     */
    function get_reference_id() {
        $column = ["r.roster_no", 'r.status'];
        $orderBy = "r.id";
        $orderDirection = "DESC";
        $this->db->select($column);
        $this->db->from(TBL_PREFIX . 'roster as r');
        $this->db->order_by($orderBy, $orderDirection);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /**
     * Roster reference type
     * @param {str} reference_data_type
     */
    public function get_roster_reference_data($reference_data_type) {
        $this->db->select(["r.display_name as label", 'r.id as value']);
        $this->db->from(TBL_PREFIX . 'references as r');
        $this->db->join(TBL_PREFIX . 'reference_data_type as rdt', "rdt.id = r.type AND rdt.archive = 0", "INNER");
        $this->db->where("r.archive", 0);
        $this->db->where("rdt.key_name", $reference_data_type);
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }

    /**
     * To get contact list related to account 
     */
    function get_contact_for_account_roster($reqData) {
       
        if (isset($reqData) && isset($reqData->account)) {
            # Account contact - 1 / Participant contact - 2
            if($reqData->account->account_type == 1)
            {
                $participant_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label"];
                $this->db->select($column);
                $this->db->from(TBL_PREFIX . 'participants_master as tp');
                $this->db->where(['tp.id' => $participant_id]);
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = tp.contact_id", "LEFT");  
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];

                $return = [ "status" => true, 'data' => $result ];      
            }
            else
            {
                $org_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label", 'sr.is_primary'];
                $this->db->from(TBL_PREFIX . 'sales_relation as sr');
                $this->db->select($column);
                $this->db->where('sr.source_data_id', $org_id);
                $this->db->where('sr.source_data_type', '2');
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : [];

                $return = [ "status" => true, 'data' => $result ];
            }            
        } else {
            $return = [ "status" => false, 'error' => 'Participant Id is null'];
        }

        return $return;
    }

    /**
     * Get account primary contact
     */
    function get_primary_contact($reqData) {
       
        if (isset($reqData) && isset($reqData->account)) {
            # Account contact - 2 / Participant contact - 1
            if($reqData->account->account_type != 1)
            {
                $org_id = $reqData->account->value;
                $column = ["sub_p.id as value","CONCAT_WS(' ', sub_p.firstname,sub_p.lastname) as label", 'sr.is_primary'];
                $this->db->from(TBL_PREFIX . 'sales_relation as sr');
                $this->db->select($column);
                $this->db->join(TBL_PREFIX . 'person as sub_p', "sub_p.id = sr.destination_data_id", "LEFT");
                $this->db->where('sr.source_data_id', $org_id);
                $this->db->where('sr.source_data_type', '2');
                $this->db->where('sr.is_primary', 1);
                $this->db->limit(1);
                $query = $this->db->get();
                $result = $query->num_rows() > 0 ? $query->result_array() : '';
                $contact_id = $result[0]['value'] ?? '';
            } else {
                $contact_id = '';
            }
        } else {
            $contact_id = '';
        }

        return $contact_id;
    }

    /**
     * fetches all the roster end date options
     */
    public function get_roster_end_date_options() {
        $data = [];
        foreach($this->roster_end_date_options as $value => $label) {
            $newrow = null;
            $newrow['label'] = $label;
            $newrow['value'] = $value;
            $data[] = $newrow;
        }
        return $data;
    }

    /**
     * Create or update roster
     * @param {obj} $reqData
     */
    public function create_update_roster($reqData) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        #initialize
        $response = ['status' => false, 'msg' => system_msgs('something_went_wrong')];
        $valid_data = (array) $reqData->data;
        $adminId = $reqData->adminId;
        // Validation rules set
        $validation_rules = [
            array('field' => 'owner_id', 'label' => 'Owner', 'rules' => 'required'), 
            array('field' => 'account_id', 'label' => 'Account (Participant/Site) Name', 'rules' => 'required'),
            array('field' => 'contact_id', 'label' => 'Contact', 'rules' => 'required'),
            array('field' => 'roster_type', 'label' => 'Roster Type', 'rules' => 'required'),
            array('field' => 'funding_type', 'label' => 'Roster Funding Type', 'rules' => 'required'),
            array('field' => 'start_date', 'label' => 'Start Date', 'rules' => 'required|valid_date_format[Y-m-d]'),
            array('field' => 'end_date', 'label' => 'End Date', 'rules' => 'required|valid_date_format[Y-m-d]'),
            array('field' => 'end_date_option', 'label' => 'End Date Option', 'rules' => 'required'),
        ];

        // Set data in libray for validation
        $this->form_validation->set_data($valid_data);

        // Set validation rule
        $this->form_validation->set_rules($validation_rules);

        // Check data is valid or not
        if ($this->form_validation->run()) {
            $check_roster=true;
            if($valid_data['reference_id'])
            {
              // Check roster is exist. Using roster_no
              $roster_no = $valid_data['roster_no'];
              $where = array('roster_no' => $roster_no);
              $colown = array('id', 'roster_no');
              $check_roster = $this->basic_model->get_record_where('roster', $colown, $where);
            }
            // If not exist 
            if (!$check_roster) {
                // Call save roster
                $rosterId = $this->save_roster($reqData);
                $data = (array) $reqData->data;
                // According to that rosterId will be created
                if ($rosterId) {
                    $this->load->library('UserName');
                    $adminName = $this->username->getName('admin', $adminId);

                    /**
                     * Create logs. it will represent the user action they have made.
                     */
                    $this->loges->setTitle("New roster created for " . $roster_no ." by " . $adminName);  // Set title in log
                    $this->loges->setDescription(json_encode($data));
                    $this->loges->setUserId($adminId);
                    $this->loges->setCreatedBy($adminId);
                    // Create log
                    $this->loges->createLog(); 
                    $data = array('roster_id' => $rosterId);
                    $response = ['status' => true, 'msg' => 'Roster has been created successfully.', 'data' => $data ];
                }
            } else {
                $rosterId = $this->save_roster($reqData);
                if ($rosterId) {
                    $response = ['status' => true, 'msg' => 'Roster has been updated successfully'];
                }

               
            }

        } else {
            // If requested data isn't valid
            $errors = $this->form_validation->error_array();
            $response = ['status' => false, 'error' => implode(', ', $errors)];
        }
        return $response;
    }

    /**
     * Save roster
     * @param {obj} $reqData
     * @param {str} $roster_no
     */
    public function save_roster($reqData) {
        $data = $reqData->data;
        $adminId = $reqData->adminId;
        // Assign the data
        $insData = [
            'account_type' => $data->account_type,
            'account_id' => $data->account_id,
            'owner_id' => $data->owner_id,
            'start_date' => date("Y-m-d", strtotime($data->start_date)),
            'end_date' => date("Y-m-d", strtotime($data->end_date)),
            'end_date_option' => $data->end_date_option,
            'roster_type' => $data->roster_type,
            'funding_type' => $data->funding_type,
            'contact_id' => $data->contact_id
            
        ];
        if(!empty($data->reference_id))
        {
            $insData['updated']=DATE_TIME;
            $insData['updated_by']= $adminId;
            $rosterId = $this->basic_model->update_records('roster', $insData, $where = array(
                'id' => $data->reference_id
            ));

        }
        else{
            $insData['created_by'] = $adminId;
            $insData['created_type'] = 1;
            $insData['created'] = DATE_TIME;
           // Insert the data using basic model function
           $rosterId = $this->basic_model->insert_records('roster', $insData);
        }

        return $rosterId;
    }

    /**
     * get roster details
     * @param {int} roster_id
     */
    public function get_roster_details($roster_id, $uuid_user_type=null) {

        // Get subquery of cerated & updated by
        $this->load->model("Common/Common_model");
        $created_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("r",$uuid_user_type,"created_by");  
        $updated_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("r",$uuid_user_type,"updated_by");  
        $owner_by_sub_query = $this->Common_model->get_created_by_updated_by_sub_query("r",$uuid_user_type,"owner_id");  
        $contact_by_sub_query = $this->get_name_sub_query('contact_id','r');

        $column = ["r.id as roster_id", "r.roster_no", "r.owner_id", "r.contact_id", "r.created", "r.created_by", "r.updated", "r.updated_by", "r.roster_type", "r.funding_type", "r.account_type", "r.account_id", "DATE_FORMAT(r.start_date,'%d/%m/%Y') as start_date", "DATE_FORMAT(r.end_date,'%d/%m/%Y') as end_date", "r.status", "r.stage","r.end_date_option" ];
        $orderBy = "r.id";
        $orderDirection = "DESC";
        $this->db->select($column);

        $status_label = "(CASE ";
        foreach($this->roster_status as $k => $v) {
            $status_label .= " WHEN r.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";
        $this->db->select($status_label);

        $this->db->select("(
            CASE 
            WHEN r.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = r.account_id) 
            WHEN r.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = r.account_id) 
            ELSE '' END
        ) as account");

         $stage_label = "(CASE ";
        foreach($this->roster_stage as $k => $v) {
            $stage_label .= " WHEN r.status = {$k} THEN '{$v}'";
        };
        $stage_label .= "ELSE '' END) as stage_label";

        $this->db->select("(SELECT display_name FROM tbl_references tr WHERE tr.id = r.roster_type ) as roster_type_label");
        $this->db->select("(SELECT display_name FROM tbl_references tr WHERE tr.id = r.funding_type ) as funding_type_label");
        $this->db->select("(" . $created_by_sub_query . ") as created_by");
        $this->db->select("(" . $updated_by_sub_query . ") as updated_by");
        $this->db->select("(" . $owner_by_sub_query . ") as owner_label");
        $this->db->select("(" . $contact_by_sub_query . ") as contact_label");

        $this->db->from(TBL_PREFIX . 'roster as r');
        $this->db->order_by($orderBy, $orderDirection);
        $this->db->where(['r.id' => $roster_id]);
        $this->db->limit(1);
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result_array() : [];
        $result = $query->num_rows() > 0 ? $result[0] : [];

        return [ "status" => true, 'data' => $result, 'msg' => 'Fetch roster detail successfully' ];
    }

    /*
     * it is used for making sub query of contact name
     * return type sql
     */
    public function get_name_sub_query($column, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_p.firstname,sub_p.lastname)");
        $this->db->from(TBL_PREFIX . 'person as sub_p');
        $this->db->where("sub_p.id = ".$tbl_alais.".".$column, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /*
     * it is used for making sub query created by (who creator|updated of participant)
     * return type sql
     */
    public function get_created_updated_by_sub_query($column_by, $tbl_alais) {
        $this->db->select("CONCAT_WS(' ', sub_m.firstname,sub_m.lastname)");
        $this->db->from(TBL_PREFIX . 'member as sub_m');
        $this->db->where("sub_m.uuid = ".$tbl_alais.".".$column_by, null, false);
        $this->db->limit(1);
        return $this->db->get_compiled_select();
    }

    /**
     * Get associated shift list with roster
     * @param {object} reqData
     */
    public function get_roster_shift_list($reqData, $adminId) {
        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? null;
        $orderBy = 's.id';
        $direction = 'desc';
        $roster_id = $reqData->roster_id ?? null;
        $participant_id = $reqData->participant_id ?? null;
        $page_name = $reqData->page_name ?? null;

        $this->load->model('Schedule_model');
        $status_label = "(CASE ";
        foreach($this->Schedule_model->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        # Searching column
        $src_columns = array("concat(m.firstname,' ',m.lastname)", "concat(p.firstname,' ',p.lastname)", "r.name", "am.fullname", "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)", "s.shift_no", "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_start_datetime,'%d/%m/%Y')", "DATE_FORMAT(s.actual_end_datetime,'%d/%m/%Y')", "s.scheduled_duration", "tr.display_name", "ros.roster_no", "DAYNAME(s.scheduled_start_datetime) as day_of_week");
        $src_columns[] = $status_label;

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

        if (isset($filter) == true && $filter != '' && !empty($filter)) {
            if (property_exists($filter, 'filter_status')) {
                if ($filter->filter_status != '' && $filter->filter_status !== "all") {
                    $this->db->where('s.status', $filter->filter_status);
                }
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
        
        $select_column = [
            "s.id", 
            "s.shift_no", 
            "s.scheduled_start_datetime",
            "s.scheduled_end_datetime",
            "s.actual_start_datetime",
            "s.actual_end_datetime",
            "concat(m.firstname,' ',m.lastname) as owner_fullname",
            "concat(p.firstname,' ',p.lastname) as contact_fullname",
            "r.name as role_name",
            "'' as actions",
            "s.person_id",
            "s.owner_id",
            "s.account_type",
            "s.account_id",
            "s.role_id",
            "s.status",
            "am.fullname as member_fullname",
            "am.id as member_id",
            "s.scheduled_duration",
            "tr.display_name as account_organisation_type",
            "ros.roster_no",
            "DAYNAME(s.scheduled_start_datetime) as day_of_week",
            "s.roster_id",
            "concat(TIME_FORMAT(s.scheduled_start_datetime, '%h:%i %p'), ' - ', TIME_FORMAT(s.scheduled_end_datetime, '%h:%i %p')) as scheduled_time",
            "s.primary_shift_id",
            "s.repeat_option",
            "s.repeat_specific_days",
            "s.not_be_invoiced",
            "s.scheduled_sa_id", "s.scheduled_sb_status", "scheduled_docusign_id", "s.actual_sa_id", "s.actual_sb_status", "actual_docusign_id"];
        

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(
            CASE 
                WHEN s.account_type = 1 THEN 
                    (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) 
                WHEN s.account_type = 2 THEN 
                    (select o.name from tbl_organisation o where o.id = s.account_id) 
                ELSE ''
            END) as account_fullname");
        $this->db->select("
            (
            CASE 
                WHEN s.primary_shift_id IS NULL or s.primary_shift_id = 0 THEN 'Yes'
                ELSE 'No'
            END
        ) as is_primary");
        $this->db->select("
            (
            CASE 
                WHEN s.primary_shift_id IS NULL or s.primary_shift_id = 0 THEN
                (
                    CASE 
                        WHEN s.repeat_option = 1 THEN 'Repeats next day'
                        WHEN s.repeat_option = 2 THEN 'Repeats rest of the week'
                        WHEN s.repeat_option = 3 THEN 'Repeats specific days'
                        WHEN s.repeat_option = 4 AND s.repeat_specific_days = 0 THEN 'Repeats every weeks'
                        WHEN s.repeat_option = 4 AND s.repeat_specific_days = 1 THEN 'Repeats specific weeks'
                        WHEN s.repeat_option = 5 AND s.repeat_specific_days = 0 THEN 'Repeats every fortnights'
                        WHEN s.repeat_option = 5 AND s.repeat_specific_days = 1 THEN 'Repeats specific fortnights'
                        WHEN s.repeat_option = 4 AND s.repeat_specific_days = 0 THEN 'Repeats every week of every month'
                        WHEN s.repeat_option = 4 AND s.repeat_specific_days = 1 THEN 'Repeats specific weeks of specific months'
                        ELSE ''
                    END
                )
                ELSE ''
                    
            END
            ) as frequency");
        
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
        
        if($page_name=='roster'){
            $this->db->where("s.roster_id", $roster_id);
        }

        if($page_name=='participants'){
            $this->db->where("s.account_type", 1);
            $this->db->where("s.account_id", $participant_id);
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
                $result[$key]->warnings = $this->Schedule_model->pull_shift_warnings($val);               
            } else {
                $result[$key]->warnings = false;
            }
        }

        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched shifts list successfully', 'record_count' => $total_item, 'total_item' => $total_item,'last_query' => $last_query);
        
    }     
}