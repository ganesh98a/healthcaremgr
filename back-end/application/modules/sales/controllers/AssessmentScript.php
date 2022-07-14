<?php

defined('BASEPATH') OR exit('No direct script access allowed');


class AssessmentScript extends MX_Controller {

    use formCustomValidation;

    function __construct() {

        parent::__construct();
        $this->load->library('form_validation');
        $this->load->model('Basic_model');
        $this->form_validation->CI = & $this;
        $this->load->helper('i_pad');
    }

    /**
     * using destructor to mark the completion of backend requests and write it to a log file
     */
    function __destruct(){
        # HCM- 7552, adding all requests to backend in a log file
        # defined in /helper/index_error_reporting.php
        # Args: log type, message heading, module name
        log_message("message", null, "admin");
    }

    public function change_risk_assessment_ref_id(){
        
        $reqData=(array) api_request_handler();     
        $limit = $reqData['pageSize'] ?? 9999;
        $page = $reqData['page'] ?? 0;  
        $orderBy = 'ra.id';
        $direction = 'ASC';
   
        // get entity type value
        $select_column = array('ra.id','ra.reference_id');

        $dt_query = $this->db->select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $select_column)), false);

        $this->db->from(TBL_PREFIX . 'crm_risk_assessment as ra');
        $this->db->where(["created_date <" => $reqData['created_date']]);

        
        $this->db->group_by('ra.id');
        $this->db->order_by($orderBy, $direction);
        $this->db->limit($limit, ($page * $limit));


        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $total_item = $dt_filtered_total = $this->db->query('SELECT FOUND_ROWS() as count;')->row()->count;

        if ($dt_filtered_total % $limit == 0) {
            $dt_filtered_total = ($dt_filtered_total / $limit);
        } else {
            $dt_filtered_total = ((int) ($dt_filtered_total / $limit)) + 1;
        }

        $result = $query->result();
        $updateData = [];
        foreach ($result as  $value) {
            $reference_no=substr("RA00000000",0,10-strlen($value->id)).$value->id;
            // Assign the data
            $updateData = [
                'reference_id' => $reference_no,
            ];
            // Update the data using basic model
            $where = array('id' =>$value->id);
            $this->basic_model->update_records('crm_risk_assessment', $updateData, $where);

            $updateData[] =$reference_no ; 
                
        }

        $return = array('count' => $dt_filtered_total, 'data' => $updateData, 'status' => true, 'total_item' => $total_item);
        return $this->output->set_output(json_encode(['updated_records' => $return])); 
       
    }   

 }