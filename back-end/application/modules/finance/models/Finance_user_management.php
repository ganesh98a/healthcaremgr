<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Finance_user_management extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_new_finance_user_name($reqData) {
        $search = $reqData->search;

        $this->db->select(array('concat(m.firstname," ",m.lastname) as label', 'm.id as value'));
        $this->db->from('tbl_member as m');

        $this->db->join('tbl_finance_staff as fs', 'fs.adminId = m.id AND fs.archive=0 AND m.archive=0 AND fs.approval_permission = 0', 'inner');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->like('concat(m.firstname," ",m.lastname)', $search);
        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
        return $result;
    }

    public function get_finance_user_details_for_add_edit($reqData) {
        $adminId = $reqData->adminId;

        if ($reqData->type == "add_finance_user") {
            $this->db->where("fs.approval_permission", 0);
        } else {
            $this->db->where("fs.approval_permission", 1);
        }

        $this->db->select(array('concat(m.firstname," ",m.lastname) as fullname', 'm.id', 'mp.phone', 'me.email', 'm.gender', 'm.profile_image', 'fs.userId', "fs.access_permission as select_all"));
        $this->db->from('tbl_member as m');

        $this->db->join('tbl_finance_staff as fs', 'fs.adminId = m.id AND fs.archive=0 AND m.archive=0', 'inner');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_member_phone as mp', 'mp.memberId = m.id AND mp.archive = 0 AND mp.primary_phone = 1', 'inner');
        $this->db->join('tbl_member_email as me', 'me.memberId = m.id AND me.archive = 0 AND me.primary_email = 1', 'inner');
        $this->db->where('m.id', $adminId);
        $this->db->where('m.archive', 0);
        $this->db->where('m.status', 1);

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->row_array();

        if (!empty($result)) {
            $result['profile_image'] = get_admin_img($result['id'], $result['profile_image'], $result['gender']);
        }
        return $result;
    }

    function get_finance_module_permission($reqData) {
        $adminId = $reqData->adminId;

        if ($reqData->type == "edit_finance_user") {
            $this->db->select("(select 1 from tbl_admin_permission as ap where ap.permissionId = p.id AND ap.adminId = " . $adminId . ") as access");
        }

        $this->db->select(array('p.permission', 'p.title', 'p.id as permissionId', 'p.id'));
        $this->db->from('tbl_permission as p');

        $this->db->join('tbl_module_title as mt', 'mt.id = p.moduleId AND mt.archive = 0 AND mt.key_name = "finance"', 'inner');
        $this->db->where_not_in("p.permission", ["access_finance", "access_finance_admin"]);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();

        return $result;
    }

    function add_edit_new_finance_staff($reqData) {
        if (!empty($reqData->finance_permissions)) {

            $role = $this->basic_model->get_row('role', ['id'], ['role_key' => 'finance', 'archive' => 0]);

            $this->basic_model->delete_records('admin_permission', ['adminId' => $reqData->id, 'roleId' => $role->id]);

            foreach ($reqData->finance_permissions as $val) {

                if (!empty($val->access)) {
                    $permissions[] = array(
                        'permissionId' => $val->id,
                        'adminId' => $reqData->id,
                        'roleId' => $role->id,
                        'created' => DATE_TIME,
                    );
                }
            }

            $this->basic_model->insert_records('admin_permission', $permissions, $multiple = true);
        }

        $update_data = ['status' => 1, 'approval_permission' => 1, 'access_permission' => $reqData->select_all];
        $this->basic_model->update_records('finance_staff', $update_data, ['adminId' => $reqData->id]);

        return true;
    }

    function get_finance_user_list($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';

        $src_columns = array('fs.userId', 'concat_ws(" ",m.firstname,m.middlename,m.lastname) as fullname', 'fs.adminId', "spell_condition");
        $available_column = array('id', 'userId', 'status', "FullName", 'start_date', "phone", "email", "access_permission", "profile_image", "gender", "adminId");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'fs.id';
            $direction = 'DESC';
        }

        if (!empty($filter->filter_by)) {
            if ($filter->filter_by == 'active') {
                $this->db->where('fs.status', 1);
            } elseif ($filter->filter_by == 'disabled') {
                $this->db->where('fs.status', 0);
            }
        }

        if (!empty($filter->start_date) && empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fs.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
        } elseif (!empty($filter->start_date) && !empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fs.created, "%Y-%m-%d") >= ', DateFormate($filter->start_date, 'Y-m-d'));
            $this->db->where('DATE_FORMAT(fs.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        } elseif (!empty($filter->end_date)) {
            $this->db->where('DATE_FORMAT(fs.created, "%Y-%m-%d") <= ', DateFormate($filter->end_date, 'Y-m-d'));
        }


        if (!empty($filter->search)) {

            $search_value = addslashes($filter->search);

            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search);
                }elseif ($column_search === "spell_condition") {
                    $this->db->or_where('case 
                            when fs.access_permission = 0 THEN (select mt.id from tbl_permission as p 
                                INNER join tbl_admin_permission as ap on ap.permissionId = p.id
                                INNER join tbl_module_title as mt on mt.id = p.moduleId AND mt.key_name = "finance" where ap.adminId = m.id and (p.title LIKE \'%' . $search_value . '%\' ) limit 1)
                            else "All" = "' . $search_value . '"
                            end', null, false);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search);
                }
            }


            $this->db->group_end();
        }


        $select_column = array('fs.id', 'fs.userId', 'fs.status', "concat_ws(' ',m.firstname,m.middlename,m.lastname) as FullName", 'fs.created as start_date', "mp.phone", "me.email", "fs.access_permission", "m.profile_image", "m.gender", "fs.adminId");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select('case 
                            when fs.access_permission = 0 THEN (select group_concat(p.title SEPARATOR \', \') from tbl_permission as p 
                                INNER join tbl_admin_permission as ap on ap.permissionId = p.id
                                INNER join tbl_module_title as mt on mt.id = p.moduleId AND mt.key_name = "finance" where ap.adminId = m.id)
                            else "All" 
                            end as finance_permissions', false);


        $this->db->from('tbl_finance_staff as fs');
        $this->db->join('tbl_member as m', 'm.id = fs.adminId AND m.archive = 0 AND m.status = 1', 'inner');
        $this->db->join('tbl_department as d', 'd.id = m.department AND d.short_code = "internal_staff"', 'inner');
        $this->db->join('tbl_member_email as me', 'me.memberId = m.id AND me.primary_email = 1 AND me.archive = 0', 'inner');
        $this->db->join('tbl_member_phone as mp', 'mp.memberId = m.id AND mp.primary_phone = 1 AND mp.archive = 0', 'left');

        $this->db->where('fs.archive', 0);
        $this->db->where('fs.approval_permission', 1);
        $this->db->order_by($orderBy, $direction);

        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();

        if (!empty($result)) {
            foreach ($result as $val) {
                $val->profile_image = get_admin_img($val->adminId, $val->profile_image, $val->gender);
            }
        }

        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true);
        return $return;
    }

    function active_disable_finance_user($reqData) {
        $update = ['status' => (!empty($reqData->status) && $reqData->status == 1) ? 1 : 0];
        $where = ['id' => $reqData->financeId];

        $this->basic_model->update_records('finance_staff', $update, $where);

        return true;
    }

}
