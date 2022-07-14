<?php

class Recruitment_model extends CI_Model {

    public function get_department($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_rec_dep = TBL_PREFIX . 'recruitment_department';

        $src_columns = array();
        $available_column = array("id", "name", "created", "department");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_rec_dep . '.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $src_columns = array($tbl_rec_dep . ".id", $tbl_rec_dep . ".name");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_box);
                } else {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }

        $select_column = array($tbl_rec_dep . ".id", $tbl_rec_dep . ".name", $tbl_rec_dep . ".created", "'Recruitment' AS department");
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_rec_dep);
        $this->db->where('archive=', '0');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $data = [];

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->created = $val->created != '0000-00-00 00:00:00' ? date('d/m/Y', strtotime($val->created)) : '';
                $val->value = $val->id;
                $val->text = $val->name;
                if (isset($val->id))
                    $val->alloted_staff_member = $this->allocated_members_by_dept_id($val->id);
                else
                    $val->alloted_staff_member = array();
            }
            $data = array_merge(array(array('demo' => [])), $dataResult);
            #$data = array_merge(array(array('demo'=>[])),$dataResult);
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }



    public function update_alloted_department($reqData) {
        if (!empty($reqData)) {
            $this->basic_model->update_records('recruitment_staff_department_allocations', array('status' => '0'), array('adminId' => $reqData->staffId));

            #pr($reqData->selectedData->options);
            foreach ($reqData->selectedData->options as $key => $myVal) {
                if ($myVal->value != null) {
                    $tbl = 'tbl_recruitment_staff_department_allocations';
                    $dt_query = $this->db->select(array($tbl . '.id'));
                    $this->db->from($tbl);
                    $sWhere = array($tbl . '.adminId' => $reqData->staffId, $tbl . '.allocated_department' => $myVal->value, $tbl . '.status' => '0');
                    $this->db->where($sWhere, null, false);
                    $query = $this->db->get();
                    #echo $this->db->last_query();
                    $tbl = 'recruitment_staff_department_allocations';
                    $row = $query->row_array();
                    if ($row) {
                        $this->Basic_model->update_records($tbl, array('status' => '1'), array('tbl_recruitment_staff_department_allocations.id' => $row['id']));
                        #echo $this->db->last_query();
                    } else {
                        $this->Basic_model->insert_records($tbl, array('adminId' => $reqData->staffId, 'allocated_department' => $myVal->value, 'status' => '1', 'created' => DATE_TIME));
                        #echo $this->db->last_query();
                    }
                }
            }

            $tbl = 'tbl_recruitment_staff_department_allocations';
            $dt_query = $this->db->select(array($tbl . '.allocated_department'));
            $this->db->from($tbl);
            $sWhere = array($tbl . '.adminId' => $reqData->staffId, $tbl . '.status' => '1');
            $this->db->where($sWhere, null, false);
            $query = $this->db->get();

            $z_dept = $query->result_array();
            $department = array();
            if (!empty($z_dept)) {
                foreach ($z_dept as $key => $valDept) {
                    $department[] = $valDept['allocated_department'];
                }
            }
            $department = array_filter($department);
            return $department;
        }
    }

    public function Recruitment_topic_list() {
        $tbl_question_topic = TBL_PREFIX . 'recruitment_question_topic';
        $this->db->select(array($tbl_question_topic . ".topic as label", $tbl_question_topic . ".id as value"));
        $this->db->from($tbl_question_topic);
        $this->db->where(array($tbl_question_topic . '.status' => 0));
        $query = $this->db->get();
        return $query->result();
    }

    public function get_applicant_list($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';
        $tbl_applicant = TBL_PREFIX . 'recruitment_applicant';
        $tbl_applicant_email = TBL_PREFIX . 'recruitment_applicant_email';
        $tbl_applicant_ph = TBL_PREFIX . 'recruitment_applicant_phone';

        $src_columns = array();
        $available_column = array("id", "applicant_name", "email", "phone");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id, $available_column) ) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = $tbl_applicant . '.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));

        if (!empty($filter->srch_box)) {
            $this->db->group_start();
            $src_columns = array($tbl_applicant . ".id", "CONCAT(" . $tbl_applicant . ".firstname,' '," . $tbl_applicant . ".middlename,' '," . $tbl_applicant . ".lastname)");

            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];
                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    $this->db->or_like($serch_column[0], $filter->srch_box);
                } else {
                    $this->db->or_like($column_search, $filter->srch_box);
                }
            }
            $this->db->group_end();
        }

        $select_column = array($tbl_applicant . ".id", "CONCAT(" . $tbl_applicant . ".firstname,' '," . $tbl_applicant . ".middlename,' '," . $tbl_applicant . ".lastname) AS applicant_name", $tbl_applicant_email . ".email", $tbl_applicant_ph . ".phone");
        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from($tbl_applicant);
        #$this->db->where('archive=','0');

        $this->db->join($tbl_applicant_email, $tbl_applicant_email . '.applicant_id = ' . $tbl_applicant . '.id AND ' . $tbl_applicant_email . '.archive = 0 AND ' . $tbl_applicant_email . '.primary_email =1', 'left');

        $this->db->join($tbl_applicant_ph, $tbl_applicant_ph . '.applicant_id = ' . $tbl_applicant . '.id AND ' . $tbl_applicant_ph . '.archive = 0 AND ' . $tbl_applicant_ph . '.primary_phone =1', 'left');

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        //last_query(); 
        $dt_filtered_total = $all_count = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();
        $data = [];

        if (!empty($dataResult)) {
            foreach ($dataResult as $val) {
                $val->value = $val->id;
                $val->text = $val->applicant_name;
            }
        }
        $return = array('count' => $dt_filtered_total, 'data' => $dataResult, 'all_count' => $all_count);
        return $return;
    }

    public function allocated_members_by_dept_id($dept_id) {
        $tbl_1 = TBL_PREFIX . 'recruitment_staff_department_allocations';
        $this->db->select(array("CONCAT(tbl_member.firstname,' ',tbl_member.lastname) AS name", "tbl_member.id as hcmr_id", $tbl_1 . ".created as allocation_date", "'Lauren Mckenzie' AS manager"));
        $this->db->from($tbl_1);
        $this->db->join('tbl_member', 'tbl_recruitment_staff_department_allocations.adminId = tbl_member.id', 'left');
        $this->db->where(array($tbl_1 . '.status' => 1, $tbl_1 . '.allocated_department' => $dept_id));
        $query = $this->db->get();
        return $query->result();
    }

}
