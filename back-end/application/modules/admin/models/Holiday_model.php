<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Holiday_model extends CI_Model
{
    var $status = [
        "1" => "Success",
        "2" => "Failure"
    ];

    var $application = [
        "1" => "Desktop",
        "2" => "Mobile",
        "3" => "Tablet"
    ];

    public function __construct()
    {
        // Call the CI_Model constructor
        // added dummy line 1
        parent::__construct();
    }

    /**
     * fetching holiday types
     */
    public function get_holiday_types() {
        $row = new stdClass();
        $row->value = "sunday";
        $row->label = "sunday";
        return [$row];
        $this->db->select(array(
            'ref.display_name as value',
            'ref.display_name as label'
        ));
        $this->db->from('tbl_references as ref');
        $this->db->join('tbl_reference_data_type as reft', 'reft.id = ref.type', 'inner');
        $this->db->where('reft.title', 'Holiday');
        $this->db->where('ref.archive', 0);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            return $query->result();            
        }
    }


    /**
     * fetching holiday details
    */
    public function get_holiday_details($holidayId)
    {
        $select_column = array(
            'tbl_holidays.id',
            'tbl_holidays.holiday_name as holiday',
            'tbl_holidays.date',
            'tbl_holidays.day',
            'tbl_holidays.status',
            'tbl_holidays.location',
            'tbl_references.key_name as holiday_type'
        );
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
        $this->db->from(TBL_PREFIX . 'holidays');
        $this->db->join('tbl_references', 'tbl_references.id = tbl_holidays.type', 'left');
        $sWhere = array(
            TBL_PREFIX . 'holidays.id' => $holidayId,
        );
        $this->db->where($sWhere);
        $this->db->group_by('tbl_holidays.id');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $dataResult = array();
        $location = array();
        $location_result = array();
        if (!empty($query->result())) {
            foreach ($query->result() as $val) {
                if($val->location !='') {
                    $location = explode(",", $val->location);
                    foreach($location as $loc) {                  
                        $select_column = array(
                            'tr.id',
                            'tr.name as label'
                        );
                        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
                        $this->db->from('tbl_state as tr');
                        $this->db->where('name' , trim($loc) );
                        $location_query = $this->db->get();
                        $location_result[] = $location_query->row();
                     }
                    $row['id'] = $val->id;
                    $row['holiday'] = $val->holiday;
                    $row['status'] = $val->status;
                    $row['date'] = $val->date;
                    $row['day'] = $val->day;
                    $row['location'] = $val->location;
                    $row['location_selected'] = $location_result;
                    $row['holiday_type'] = $val->holiday_type;
                    $dataResult = $row;
                } else {
                    $dataResult = $query->row();
                }                
            }
        }
        return $dataResult;
    }

    /**
     * fetching existing holiday
    */
    public function check_holiday_exists($newData,$holiday_id) {
       $this->db->select(array(
            'hday.id',
            "DATE_FORMAT(hday.date,'%Y-%m-%d')",
            'hday.type'
        ));
        $this->db->from('tbl_holidays as hday');
        $formatted_date = date('Y-m-d', strtotime(str_replace('/', '-', $newData["date"])));
        $this->db->or_like('hday.date', DATE($formatted_date));
        $this->db->where('hday.type', $newData['type']);
        if($holiday_id > 0)
          $this->db->where('hday.id !=', $holiday_id);
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            foreach ($query->result() as $row) {
            $ids[] = $row->id;
            }
        }
        return $ids;
    }

    /**
     * adding/updating the holiday and its relevant information
     */
    public function create_update_holiday($data, $adminId) {
        $this->load->library('form_validation');
        $this->form_validation->reset_validation();

        $holiday_id = $data['id'] ?? 0;
        $errors = $insert_data = null;
        if(!isset($data['holiday']) || empty($data['holiday']))
            $errors[] = "Please provide Holiday name";

        if(!isset($data['type']) || empty($data['type']))
            $errors[] = "Please select at least one type";
        # if any validation has failed
        if(!empty($errors)) {
            $response = ['status' => false, 'error' => implode(",",$errors)];
            return $response;
        }
        $holiday_ref_id = $data['holiday_type'];

        # adding/updating tbl_holidays table

        // Check type is exist. Using name
        $name = $data['holiday_type'];
        $this->db->select('tr.id');
        $this->db->from('tbl_references as tr');
        $this->db->where('display_name' , $name );
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->row();
        $holiday_ref_id = $result->id;

        # adding transport options
        if (!empty($data['location_selected'])) {
            $location = null;
            foreach($data['location_selected'] as $refobj => $value) {
                $location[] = ' '. $value['label'];
            }
            $location = implode(',',$location);
        }
        $postdata = [
            "id" => $holiday_id,
            "holiday_name" => $data['holiday'],
            "type" => $holiday_ref_id,
            "date" => isset($data['date'])? $data['date']:0,
            "day" => isset($data['day'])?$data['day']:'',
            "status" => isset($data['status'])?$data['status']:0,
            "location" => isset($location) ? $location : '',
        ];

        //check if holiday exists, Using date and type
        $is_holiday_exists = $this->check_holiday_exists($postdata,$holiday_id);
        if(!empty($is_holiday_exists)) {
            $response = ['status' => false, 'error' => 'Holiday already exists on this date/type'];
            return $response;
        }

        if ($holiday_id) {
            $postdata["updated"] = DATE_TIME;
            $postdata["updated_by"] = $adminId;
            $this->basic_model->update_records("holidays", $postdata, ["id" => $holiday_id]);
        } else {
            $postdata["created"] = DATE_TIME;
            $postdata["updated"] = DATE_TIME;
            $postdata["created_by"] = $adminId;
            $holiday_id = $this->basic_model->insert_records("holidays", $postdata, $multiple = FALSE);
        }

        # setting the message title
        if (!empty($data['id'])) {
            $msg = 'Holiday has been updated successfully.';
        } else {
            $msg = 'Holiday has been created successfully.';
        }
        
        $response = ['status' => true, 'msg' => $msg];
        return $response;
    }

    /*
     * To fetch the holidays list
     */
    public function get_holidays_list($data, $filter_condition = '') {
        
        if (empty($data)) {
            $response = array('status' => false, 'error' => "Missing data");
            return $response;
        }

        $limit = $data->pageSize?? 99999;
        $page = $data->page?? 0;
        $sorted = $data->sorted?? [];
        $filter = $data->filtered?? null;
        $orderBy = '';
        $direction = '';

        # Searching column
        $src_columns = array("hday.holiday_name", "DATE_FORMAT(hday.date,'%d/%m/%Y')","hday.day", "ref.display_name");
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

        # status filters
        if(isset($filter) && !empty($filter->select_filter_field)) {
            if($filter->select_filter_field == "Active") {
                $this->db->where("hday.status", "1");
            }
            if($filter->select_filter_field == "InActive") {
                $this->db->where("hday.status", "0");
            }
        }
        if (!empty($filter_condition)) {
            $this->db->having($filter_condition);
        }

        # sorting part
        $available_column = ["id", "holiday_name", "type", "location","day","hdstatus","date","holiday_type","updated","archive","status"];
        if (!empty($sorted)) {
            if (!empty($sorted[0]->id) && in_array($sorted[0]->id,$available_column)) {
                $orderBy = $sorted[0]->id;
                $direction = ($sorted[0]->desc == 1) ? 'Desc' : 'Asc';
            }
        } else {
            $orderBy = 'hday.id';
            $direction = 'DESC';
        }

        $select_column = ["hday.id", "hday.holiday_name", "hday.type", "hday.location","hday.day","hday.status as hdstatus","hday.date","ref.display_name as holiday_type","hday.updated","hday.archive","(case when hday.status=1 then 'Active' else 'InActive' end) as status"];
        
        $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);
            
        $this->db->from('tbl_holidays as hday');
        $this->db->join('tbl_references as ref', 'ref.id = hday.type', 'left');
        $this->db->where("hday.archive", "0");

        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));

        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ci = &get_instance();
        $last_query = null; # $ci->db->last_query();

        // Get total rows count
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;
        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }
        // Get the query result
        $result = $query->result();
        
        $return = array('count' => $dt_filtered_total, 'data' => $result, 'status' => true, 'msg' => 'Fetched holidays list successfully',"last_query" => $last_query, 'total_item' => $total_item);
        return $return;
    }

    /**
     * archiving Holiday
     */
    function archive_holiday($data, $adminId) {
        $id = isset($data['id']) ? $data['id'] : 0;

        if (empty($id)) {
            $response = ['status' => false, 'error' => "Missing ID"];
            return $response;
        }
  
        $upd_data["updated"] = DATE_TIME;
        $upd_data["updated_by"] = $adminId;
        $upd_data["archive"] = 1;
        $result = $this->basic_model->update_records("holidays", $upd_data, ["id" => $id]);

        $msg_title = "Successfully archived Holiday";
        $response = ['status' => true, 'msg' => $msg_title];
        return $response;
    }

    /**
     * fetching location list
     */
    public function get_location_list() {
        $this->db->select(array(
            'loc.id as id',
            'loc.name as label'
        ));
        $this->db->from('tbl_state as loc');
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $ids = [];
        if($query->result()) {
            return $query->result();            
        }
    }
}
