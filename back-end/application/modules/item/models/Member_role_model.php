<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Class : Member_role_model
 * Uses : for handle query operation of role
 *
 */
class Member_role_model extends CI_Model {

    function __construct() {

        parent::__construct();
    }

    /*
     * It is used to get the role list
     * 
     * Operation: 
     *  - searching
     *  - filter
     *  - sorting
     * 
     * Return type Array
     */
    public function get_role_list($reqData) {
        // Get subqueries

        $limit = $reqData->pageSize ?? 0;
        $page = $reqData->page ?? 0;
        $sorted = $reqData->sorted ?? [];
        $filter = $reqData->filtered ?? [];
        $orderBy = '';
        $direction = '';

        // Searching column
        $src_columns = array('name');
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

        // Sort by id 
        $available_column = ["role_id", "name","description", "start_date", "end_date"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'tr.id';
            $direction = 'DESC';
        }

        // Filter by status
        // if (!empty($filter->filter_status)) {
        //     if ($filter->filter_status === "active") {
        //         $this->db->where('tr.active', 1);
        //     } else if ($filter->filter_status === "inactive") {
        //         $this->db->where('tr.active', 0);
        //     }
        // }

        $select_column = ["tr.id as role_id", "tr.name","tr.description", "tr.start_date", "tr.end_date"];
        // Query
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
       
        $this->db->from('tbl_member_role as tr');
        $this->db->where('tr.archive', 0);
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        /* it is used for subquery filter */
        if (!empty($queryHaving)) {
            $this->db->having($queryHaving);
        }

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        // Get total rows inserted count
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        // If limit 0 return empty 
        if ($limit == 0) {
            $return = array('count' => $dt_filtered_total, 'data' => array(), 'status' => false, 'error' => 'Pagination divide by zero');
            return $return;
        }

        // Get the count per page and total page
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetch role list successfully');
        return $return;
    }
    
    /* 
     * To create or update role
     * 
     * @params {array} $data
     * @params {int} $adminId
     * 
     * return type roleId
     */
    function create_upadte_role($data, $adminId) {
        // Assign the data

        $roleId = '';
        if(!empty($data['role_id'])){
            $updateData = [
                'name' => $data["name"],
                'description' => $data["description"] ?? null ,
                'start_date' => $data["start_date"],
                'end_date' => $data["end_date"] ?? null ,
                'updated_by' => $adminId,
                'updated_at' => DATE_TIME,
            ];
    
            $roleId = $this->basic_model->update_records('member_role', $updateData, $where = array('id' => $data['role_id']));
        }else{
            $insData = [
                'name' => $data["name"],
                'description' => $data["description"] ?? null ,
                'start_date' => $data["start_date"],
                'end_date' => $data["end_date"] ?? null ,
                // 'active' => $data["active"],
                'created_by' => $adminId,
                'created_at' => DATE_TIME,
            ];
    
            // Insert the data using basic model function
            $roleId = $this->basic_model->insert_records('member_role', $insData);
        }
       

        return $roleId;
    }

     /*
     * its use for get contact/person details
     * 
     * @params $contactId
     * 
     * return type array
     * return contact details
     */

    function get_role_details($roleId) {
        $this->db->select(["tr.id as role_id", "tr.name", "tr.description","tr.start_date", "tr.end_date",]);
        $this->db->from("tbl_member_role as tr");
        $this->db->where("tr.id", $roleId);
        $this->db->where('tr.archive', 0);
        $query = $this->db->get();

        return $query->row();

    }

    /**
     * Mark role by given ID as archived.
     * 
     * @param int $id 
     * @return array 
     */
    public function archive_role($id) {
        // archive the role, even if it is already archived
        $cond = ['id' => $id];
        $existingRole = $this->db->get_where('tbl_member_role', $cond)->row_array();

        // Record already been destroyed, just return success anyway
        if (!$existingRole) {
            return [
                'status' => true,
                'id' => $id
            ];
        }

        // Archive role anyway, even if it is already archived
        $this->db->update('tbl_member_role', ['archive' => 1], $cond);

        return [
            'status' => true,
            'id' => $id,
        ];
    }

    /**
     * get role list by search.
     * 
     * @param int $search 
     * @return array 
     */
    public function get_role_list_by_search($search_key = null) { 
        $this->db->select(["tr.id as value", "tr.name as label"]);
        $this->db->from(TBL_PREFIX . 'member_role as tr');
        $this->db->where(['tr.archive' => 0]);
        if(!empty($search_key)){
            $this->db->like("tr.name", $search_key);
        }
        $query = $this->db->get();
        return $query->num_rows() > 0 ? $query->result_array() : [];
    }
}

    
