<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Business_unit_model extends CI_Model
{
    public function __construct()
    {
        // Call the CI_Model constructor        
        parent::__construct();
    }

    /**
     * Adding/updating the business unit and its relevant information
     */
    public function create_update_business_unit($data, $adminId) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $bu_id = $data['id'] ?? 0;

        if(empty($data['business_unit_name'])) {
            $errors[] = "Please provide Business unit name";
        }

        if(empty($data['owner_person']['value'])) {
            $errors[] = "Please provide owner name";
        }

        if(empty($data['region_id'])) {
            $errors[] = "Please select at least one Region name";
        }

        if($data['status'] == "0" && empty($data['notes'])) {
            $errors[] = "Please mention the notes";
        }

        # check data is valid or not
        if (!empty($errors)) {
            return ['status' => FALSE, 'error' => implode(', ', $errors)];
        }

        $postdata = [
            "id" => $bu_id,
            "business_unit_name" => $data['business_unit_name'],
            "region_id" => $data['region_id'] ?? '',
            "notes" => $data['notes']??'',
            "owner_id" => !empty($data['owner_person']['value']) ? $data['owner_person']['value'] : 0,
            "status" => isset($data['status']) ? $data['status'] : 0,
        ];

        if ($bu_id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("business_units", $postdata, ["id" => $bu_id]);
        } else {
            $postdata["created"] = DATE_TIME;            
            $postdata["created_by"] = $adminId;
            $this->basic_model->insert_records("business_units", $postdata);
        }

         # setting the message title
         if (!empty($data['id'])) {
            $msg = 'Business Unit has been updated successfully.';
        } else {
            $msg = 'Busineess Unit has been created successfully.';
        }
        
        return ['status' => true, 'msg' => $msg];
    
    }

    /*
     * To fetch the business_unit list
     */
    public function get_business_unit_list($data, $filter_condition = '') {
        
        if (empty($data)) {
            return array('status' => FALSE, 'error' => "Missing data");
        }

        $limit = $data->pageSize?? 20;
        $page = $data->page?? 0;
        $sorted = $data->sorted?? [];
        $filter = $data->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("bu.business_unit_name","s.name","m.firstname","notes", "bu.status");
        if (!empty($filter->search)) {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if($column_search == "m.firstname") {
                    $this->db->or_where('concat(m.firstname," ",m.lastname) LIKE', '%'. $filter->search .'%');
                }
                else if($column_search == "bu.status") {
                    if(stristr("Active",$filter->search)) {
                        $status = 1;
                    } else if(stristr("Inactive",$filter->search)) {
                        $status = 0;
                    } else {
                        $status = NULL;
                    }
                    if($status >= 0) {
                        $this->db->or_where('bu.status', $status);
                    }
                }
                else if (strstr($column_search, "as") !== false) {
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
        $available_column = ["id", "business_unit_name", "region_id","status"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'bu.id';
            $direction = 'DESC';
        }

        $select_column = ["bu.id", "bu.business_unit_name", "s.name as name","bu.status as status","bu.updated","bu.archive","(case when bu.status=1 then 'Active' else 'InActive' end) as status", "concat(m.firstname,' ',m.lastname) as ownerlabel","m.firstname","bu.notes"];
        
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            
        $this->db->from(TBL_PREFIX . 'business_units as bu');
        $this->db->join(TBL_PREFIX . 'member m', 'm.uuid = bu.owner_id', 'left');
        $this->db->join(TBL_PREFIX . 'state s', 's.id = bu.region_id', 'left');
        $this->db->where("bu.archive", "0");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get();
        $last_query = null;

        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();
        
        return array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched Business Units list successfully',"last_query" => $last_query, 'total_item' => $total_item);
    }

    public function get_business_unit_details($buId) {

        $select_column = array(
            'bu.id',
            'business_unit_name',
            's.id as region_id',
            'bu.status',
            'bu.notes',
            "concat(m.firstname,' ',m.lastname) as ownerlabel",
            "m.uuid as ownervalue"
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from(TBL_PREFIX . 'business_units bu');
        $this->db->join('tbl_member m', 'm.uuid = bu.owner_id', 'left');
        $this->db->join(TBL_PREFIX . 'state s', 's.id = bu.region_id', 'left');
        $this->db->where('bu.id', $buId);
        $query = $this->db->get();
        $dataResult = []; $owner_person = []; $location_result = [];
       
        if (!empty($query->result())) {            
            foreach ($query->result() as $val) {
                    $row['id'] = $val->id;
                    $row['business_unit_name'] = $val->business_unit_name;
                    $row['status'] = $val->status;                   
                    $row['region_id'] = $val->region_id;
                    $row['location_selected'] = $location_result;
                    $row['notes'] = $val->notes;

                    $owner_person['label'] = $val->ownerlabel;
                    $owner_person['value'] = $val->ownervalue;
                    
                    $dataResult = $row;
                    $dataResult['owner_person'] = $owner_person;
                             
            }
        }
        return $dataResult;
    }

     /**
     * Archiving Business Unit
     */
    function archive_business_unit($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            return ['status' => false, 'error' => "Missing ID"];
        }
  
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $this->basic_model->update_records("business_units", $upd_data, ["id" => $id]);

        return ['status' => true, 'msg' => "Successfully archived Business Unit Management"];
    }

    /**
     * Get owner details 
     */
    public function get_owner_details($reqData) {
        $search = '' ;
        if(isset($reqData->search) && !empty($reqData->search)){
            $search = $reqData->search;
        }

        if (!empty($reqData->assigned_user)) {
            $already_assigned = json_decode(json_encode($reqData->assigned_user), true);
            $already_assigned_ids = array_column($already_assigned, 'value');

            if (!empty($already_assigned_ids)) {
                $this->db->where_not_in('a.id', $already_assigned_ids);
            }
        }

        $this->db->select("(CASE WHEN a.is_super_admin = 1 THEN 1 
			WHEN ((select id from tbl_recruitment_staff as rs where rs.adminId = a.uuid and rs.archive= 0 AND rs.status = 1 AND rs.approval_permission = 1) > 0) THEN 1
			ELSE 0 end) as is_recruitment_user");
        $this->db->select(array('concat(a.firstname," ",a.lastname) as label', 'a.uuid as value', '"2" as primary_recruiter', 'a.username as email'), false);
        $this->db->from('tbl_member as a');
        $this->db->join('tbl_department as d', 'd.id = a.department AND d.short_code = "internal_staff"', 'inner');

        if(!empty($search)){
            $this->db->like('concat(a.firstname," ",a.lastname)', $search);
        }
        $this->db->where('a.archive', 0);
        $this->db->where('a.status', 1);
        $this->db->having("is_recruitment_user", 1);       

        $query = $this->db->get();
        return $query->result();          
    }
}

?>