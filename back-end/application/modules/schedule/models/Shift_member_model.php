<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Shift_member_model extends Basic_model {
    public function __construct()
    {
        parent::__construct();
        $this->load->model('member/Member_model');
        $this->table_name = 'shift_member';
        $this->object_fields['Member'] = [
            'field' => 'member_id',
            'object_fields' => $this->Member_model->getObjectFields()
        ];
        $this->object_fields['email'] = function($shiftId = '') {
            if (empty($shiftId)) {
                return 'Email';
            }
            $joins = [
                        ['left' => ['table' => 'member AS m', 'on' => 'sm.member_id = m.id']],
                        ['left' => ['table' => 'person AS p', 'on' => 'm.person_id = p.id']]
                    ];
            $result = $this->get_row('shift_member AS sm', ['p.username'], ['sm.shift_id' => $shiftId], $joins );
            if (!empty($result) && is_object($result) && property_exists($result, 'username')) {
                return $result->username;
            } else {
                return null;
            }
        };
        $this->object_fields['phone'] = function($shiftId = '') {
            if (empty($shiftId)) {
                return 'Phone';
            }
            $joins = [
                        ['left' => ['table' => 'member AS m', 'on' => 'sm.member_id = m.id']],
                        ['left' => ['table' => 'person AS p', 'on' => 'm.person_id = p.id']],
                        ['left' => ['table' => 'person_phone AS pp', 'on' => 'm.person_id = pp.person_id AND pp.archive = 0 AND pp.primary_phone = 1']]
                    ];
            $result = $this->get_rows('shift_member AS sm', ['pp.phone', 'sm.id'], ['sm.shift_id' => $shiftId], $joins );
            $phone = $result ?? []; 
            return $phone;
        };
    }

     /**
     * fetching published shifts that are assigned to a member
     * function is used mainly in the member/applicant web app
     */
    public function get_available_shifts_member($reqData) {
        
        $this->load->model('schedule/Schedule_model');

        $limit = $reqData->pageSize ?? 20;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];

        # default listing is available, if requested as accepted, changing the query a bit
        $type = $reqData->type ?? "available";

        $orderBy = '';
        $direction = '';

        # building status cases
        $status_label = "(CASE ";
        foreach($this->Schedule_model->schedule_status as $k => $v) {
            $status_label .= " WHEN s.status = {$k} THEN '{$v}'";
        };
        $status_label .= "ELSE '' END) as status_label";

        # Searching column
        $src_columns = array("r.name",
        "(CASE WHEN s.account_type = 1 THEN (select p1.name from tbl_participants_master p1 where p1.id = s.account_id) WHEN s.account_type = 2 THEN (select o.name from tbl_organisation o where o.id = s.account_id) ELSE '' END)",
        "s.shift_no", $status_label,
        "DATE_FORMAT(s.scheduled_start_datetime,'%d/%m/%Y')",
        "DATE_FORMAT(s.scheduled_end_datetime,'%d/%m/%Y')", "s.scheduled_duration");
        
        $search_key  = !empty($filter->search) ? $this->db->escape_str($filter->search, true) : '';
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

        # sorting part
        $available_column = ["id", "shift_no", "member_id", "fullname", "shift_id", "scheduled_start_datetime", "scheduled_end_datetime", "role_name", "status", "scheduled_duration", "account_id", "account_type"];
        $manual_order = false;
        if (!empty($sorted)) {
            $manual_order = true;
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {                
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {            
            if($type == "completed" || $type == "cancelled") {
                $orderBy = 's.scheduled_end_datetime';
                $direction = 'DESC';
            } else {
                $orderBy = 's.scheduled_start_datetime';
                $direction = 'ASC';
            }
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
            $this->db->join('tbl_shift_member as sma', 's.accepted_shift_member_id = sma.id AND sma.member_id = '.$reqData->member_id, 'inner');
            $this->db->where('sm.status', 1);
            $this->db->where_not_in('s.status', ['5','6']);
            $this->db->order_by('(CASE WHEN scheduled_start_datetime<CURDATE() THEN CURRENT_TIMESTAMP()-TIMESTAMP(scheduled_start_datetime) ELSE -1 END) ASC, scheduled_start_datetime ASC');
        } else if($type == "completed") {
            $this->db->join('tbl_shift_member as sma', 's.accepted_shift_member_id = sma.id AND sma.member_id = '.$reqData->member_id, 'inner');
            $this->db->where('sm.status', 1);
            $this->db->where('s.status', '5');
            $manual_order = true;
        } else if($type == "cancelled") {
            $this->db->join('tbl_shift_member as sma', 's.accepted_shift_member_id = sma.id AND sma.member_id = '.$reqData->member_id, 'inner');
            $this->db->where('sm.status', 1);
            $this->db->where('s.status', '6');
            $manual_order = true;
        }
        else {
            $this->db->where('s.accepted_shift_member_id IS NULL');
            $this->db->where('sm.status', 0);
            $this->db->where("s.scheduled_start_datetime > NOW()");
            $this->db->where('s.status', 2);
            $manual_order = true;
        }


        $this->db->where('s.archive', 0);
        $this->db->where('sm.archive', 0);
        $this->db->where('m.id', $reqData->member_id);
        if ($manual_order == true) {
            $this->db->order_by($orderBy, $direction);
        }        
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();

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
            $p_addresses = $this->Schedule_model->get_address_for_accounts($all_p_ac_ids, 1);
            $o_addresses = $this->Schedule_model->get_address_for_accounts($all_o_ac_id, 2);
            foreach($result as $s) {
                if (array_key_exists($s->account_id, $p_addresses) && $s->account_type == 1) {
                    $s->address = $p_addresses[$s->account_id];
                    if(!empty($p_addresses['unit_number'])){
                        $s->address = $p_addresses['unit_number'].', '.$p_addresses[$s->account_id];
                    }
                   
                }
                if (array_key_exists($s->account_id, $o_addresses) && $s->account_type == 2) {
                    $s->address = $o_addresses[$s->account_id];
                    if(!empty($o_addresses['unit_number'])){
                        $s->address = $o_addresses['unit_number'].', '.$o_addresses[$s->account_id];
                    }
                }
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch unavailability list successfully');
        return $return;
    }
    
}