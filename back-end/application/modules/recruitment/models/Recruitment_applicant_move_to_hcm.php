<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recruitment_applicant_move_to_hcm extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function get_applicant_info($applicantId) {

        $this->db->select(["ra.firstname", "ra.lastname", "ra.middlename", "ra.dob", "ra.gender","ra.recruiter", "p.username", "p.password", "ra.person_id", "CONCAT_WS(' ',ra.firstname,ra.lastname) as fullname"]);
        $this->db->from('tbl_recruitment_applicant as ra');
        $this->db->join('tbl_person as p', 'p.id = ra.person_id', 'inner');
        $this->db->where('ra.id', $applicantId);
        $this->db->where('ra.archive', 0);
        $this->db->where('ra.status', 3);

        $query = $this->db->get();
        $res = $query->row();

        return $res;
    }

    function get_applicant_phone($applicant_id) {
        $where = array('applicant_id' => $applicant_id, 'archive' => 0);
        $column = array('id', 'phone', 'primary_phone');

        return $this->basic_model->get_record_where('recruitment_applicant_phone', $column, $where);
    }

    function get_applicant_email($applicant_id) {
        $where = array('applicant_id' => $applicant_id, 'archive' => 0);
        $column = array('id', 'email', 'primary_email');

        return $this->basic_model->get_record_where('recruitment_applicant_email', $column, $where);
    }

    function get_department_id_by_key($key) {
        $this->db->select(array('d.id'));
        $this->db->from('tbl_department as d');
        $this->db->where('d.archive', 0);
        $this->db->where('d.short_code', $key);


        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->row('id');
        return $result;
    }

    function check_applicant_already_active($applicantId, $uuid='', $app_data=NULL) {
        if(!empty($uuid)){
            return $this->basic_model->get_row('member', ['uuid'], ["uuid"=>$uuid]);
        }else{
            $column = ['applicant_id'];
            $where = ['applicant_id' => $applicantId, 'archive' => 0];
    
            return $this->basic_model->get_row('member', $column, $where);
        }
        
    }

    function check_applicant_phone_already_exist_in_hcm($phone_numbers) {
        $this->db->select(array('mp.phone'));
        $this->db->from('tbl_member_phone as mp');
        $this->db->join('tbl_member as m', 'm.id = mp.memberId AND m.archive = 0', 'inner');
        $this->db->where('mp.archive', 0);
        $this->db->where_in("REPLACE(REPLACE(mp.phone,' ',''), '+','')", $phone_numbers);
        $this->db->group_by("mp.phone");
        
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());

        $result = $query->result();
        return $result;
    }

    function check_applicant_email_already_exist_in_hcm($emails) {
        $this->db->select(array('me.email'));
        $this->db->from('tbl_member_email as me');
        $this->db->join('tbl_member as m', 'm.id = me.memberId AND m.archive = 0', 'inner');
        $this->db->where('me.archive', 0);
        $this->db->where_in("me.email", $emails);
        $this->db->group_by("me.email");
        $query = $this->db->get() or die('MySQL Error: ' . $this->db->_error_number());
        $result = $query->result_array();
        return $result;
    }

    function get_applicant_work_area($applicant_id) {
        $where = array('applicant_id' => $applicant_id);
        $column = array('id', 'work_area', 'pay_point','pay_level');
        return $this->basic_model->get_record_where('recruitment_applicant_pay_point_options', $column, $where);
    }

}
