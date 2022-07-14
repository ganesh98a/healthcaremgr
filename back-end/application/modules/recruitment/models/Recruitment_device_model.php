<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_device_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    public function get_device_listing($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';



        $src_columns = array("rd.id", "rd.device_name", "rd.device_number");
        $available_column = array("id", "device_name", "device_number", "device_location", "location", "is_offline");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'rd.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));


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


        $select_column = array("rd.id", "rd.device_name", "rd.device_number", "rd.device_location", "rl.name as location", "rd.is_offline");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_recruitment_device as rd');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rd.device_location AND rl.archive = 0', 'inner');
        $this->db->where('rd.archive', 0);

        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('rd.id');
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();   
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();


        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

    function add_edit_device($reqData) {
        $device_data = array(
            'device_name' => $reqData->device_name,
            'device_number' => $reqData->device_number,
            'device_location' => $reqData->device_location,
            'updated' => DATE_TIME,
            'archive' => 0,
        );

        if (!empty($reqData->id)) {
            $this->basic_model->update_records('recruitment_device', $device_data, ['id' => $reqData->id]);
        } else {
            $device_data['created'] = DATE_TIME;
            $this->basic_model->insert_records('recruitment_device', $device_data, $multiple = FALSE);
        }

        return true;
    }

    public function get_allocated_or_unallocated_device_listing($reqData) {
        $limit = $reqData->pageSize;
        $page = $reqData->page;
        $sorted = $reqData->sorted;
        $filter = $reqData->filtered;
        $orderBy = '';
        $direction = '';



        $src_columns = array("rd.id", "rd.device_name", "rd.device_number");
        $available_column = array("id", "device_name", "device_number", "device_location", "location", "is_offline");
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'rd.id';
            $direction = 'DESC';
        }
        $orderBy = $this->db->escape_str(str_replace('#', '', $orderBy));


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


        $select_column = array("rd.id", "rd.device_name", "rd.device_number", "rd.device_location", "rl.name as location", "rd.is_offline");

        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from('tbl_recruitment_device as rd');
        $this->db->join('tbl_recruitment_location as rl', 'rl.id = rd.device_location AND rl.archive = 0', 'inner');
        
//        $this->db->join('tbl_recruitment_manage_device as rmd', 'rmd.deviceId = rd.id AND rmd.archive = 0', 'LEFT');
//        $this->db->join('tbl_recruitment_task as rt', 'rt.id = rmd.taskId AND rt.status = 1', 'LEFT');
//        $this->db->join('tbl_recruitment_applicant as ra', 'ra.id = rmd.applicant_id AND ra.archive = 0', 'LEFT');
        $this->db->where('rd.archive', 0);

        $this->db->order_by($orderBy, $direction);
        $this->db->group_by('rd.id');
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
//        last_query();   
        $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();


        $return = array('count' => $dt_filtered_total, 'data' => $result);
        return $return;
    }

}
