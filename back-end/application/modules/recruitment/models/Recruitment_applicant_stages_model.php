<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model file used for applicant and its stage operation
 * 
 */
class Recruitment_applicant_stages_model extends \CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->model('Recruitmentformapplicant_model');
    }

    /*
    *Get stage key by passing stage number
    */
    function get_stage_key_byid($id_ary){
        $this->db->select(['key_name']);
        $this->db->from('tbl_recruitment_stage_label');
        $this->db->where_in('id',$id_ary);
        $this->db->where('archive',0);
        $query = $this->db->get();
        $res =[];
        if($query->num_rows()>0){
            $res = $query->result_array();
            $res = array_map('current',$res);
        }
        return $res;
    }  

    /*
    * Get stage key by passing stage number
    * for applicant, stage number is same for GI and individual
    * and for CAB and offers day
    * 
    * @todo: Should the function name be 'get_all_stage_key_for_application'
    */
    function get_all_stage_key_for_applicant($applicantId, $application_id = 0){
        $this->db->select(['rsl.key_name']);
        $this->db->from('tbl_recruitment_stage_label as rsl');
        $this->db->join('tbl_recruitment_stage as rs', 'rsl.id = rs.stage_label_id AND rs.archive = 0', 'inner');
        $this->db->join('tbl_recruitment_job_stage as rjs', 'rjs.stage_id = rsl.id AND rjs.archive = 0', 'inner'); 

        if (!empty($application_id)) {
            $this->db->join('tbl_recruitment_applicant_applied_application as raaa', 'raaa.jobId = rjs.jobId', 'inner'); 
            $this->db->where('raaa.id', $application_id);
        } 
        // @deprecated 
        else {
            $this->db->join('tbl_recruitment_applicant as ra', 'ra.jobId = rjs.jobId AND ra.id ='.$this->db->escape_str($applicantId, true), 'inner'); 
        }

        $this->db->where('rsl.archive',0);
        $this->db->group_by('rs.stage_label_id');
        $query = $this->db->get();
        $res =[];
        if($query->num_rows()>0){
            $res = $query->result_array();
        }
        return $res;
    }    
    
    function get_application_current_stage($application_id){
        $res = $this->basic_model->get_row("recruitment_applicant_applied_application", ["current_stage"], ["id" => $application_id]);
        
        return $res->current_stage ?? '';
    }
}
