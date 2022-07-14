<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Data provider model for 'Template' controller actions
 */
class Automatic_email_model extends CI_Model {

    public function automatic_email_listing($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = (array) $reqData->sorted;
        $filter = $reqData->filtered  ?? NULL;

        $orderBy = '';
        $direction = '';

        $src_columns = array("id", "email_name","template_name");
        $available_column = array("id", "email_name", "templateId","template_name");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'ae.id';
            $direction = 'desc';
        }

        if (isset($filter->search_box) && $filter->search_box != '') {
            $this->db->group_start();
            for ($i = 0; $i < count($src_columns); $i++) {
                $column_search = $src_columns[$i];

                if (strstr($column_search, "as") !== false) {
                    $serch_column = explode(" as ", $column_search);
                    if ($serch_column[0] != 'null')
                        $this->db->or_like($serch_column[0], $filter->search_box);
                } else if ($column_search != 'null') {
                    $this->db->or_like($column_search, $filter->search_box);
                }
            }
            $this->db->group_end();

            $queryHavingData = $this->db->get_compiled_select();
            $queryHavingData = explode('WHERE', $queryHavingData);
            $queryHaving = isset($queryHavingData[1]) ? $queryHavingData[1] : '';
            $this->db->having($queryHaving, null, false);
        }

        if (isset($filter->filter_template_name)) {
            if ($filter->filter_template_name !== "all") {
                $this->db->where("ae.templateId", $filter->filter_template_name);
            }
        }

        $select_column = array("ae.id", "ae.email_name", "ae.templateId");


        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->select("(select name from tbl_email_templates as et where et.id = ae.templateId) as template_name");
        $this->db->from('tbl_automatic_email as ae');
        $this->db->where("ae.archive", 0);
        //check of $reqData has any condition i.e. to check if template is assigned or not
        if (!empty($reqData->conditions)) {
            $conditions = (array) $reqData->conditions;
            $this->db->where($conditions);
        }
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));


        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_count = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $dataResult = $query->result();

        $return = array('status' => true, 'count' => $dt_filtered_total, 'data' => $dataResult, 'total_count' => $total_count);
        return $return;
    }

    function assign_template_to_email($reqData, $current_admin) {
        $update_data = ['templateId' => $reqData->templateId, "assign_by" => $current_admin, "assign_at" => DATE_TIME];

        $where = ["id" => $reqData->automaticEmailId];

        $this->basic_model->update_records("automatic_email", $update_data, $where);
        return true;
    }

    function get_template_content_details($email_key) {
        $this->db->select(["et.id", "et.name", "et.from", "et.subject", "et.content"]);
        $this->db->from("tbl_automatic_email as ae");
        $this->db->join("tbl_email_templates as et", "et.id = ae.templateId", "INNER");
        $this->db->where("ae.email_key_name", $email_key);
        $res = $this->db->get()->row_array();

        return $res;
    }

    function get_template_content_details_by_template_id($templateId) {
        $this->db->select(["et.id", "et.name", "et.from", "et.subject", "et.content"]);
        $this->db->from("tbl_email_templates as et");

        $this->db->where("et.id", $templateId);
        $res = $this->db->get()->row_array();
  
        return $res;
    }

}
