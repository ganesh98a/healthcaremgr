<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Approval
 *
 * @author corner stone solutions
 */
class Approval {

    protected $CI;
    private $id;
    private $approval_date = DATE_TIME;
    private $approved_by;
    private $status;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setApproval_date($approval_date) {
        $this->approval_date = $approval_date;
    }

    function getApproval_date() {
        return $this->approval_date;
    }

    function setApproved_by($approved_by) {
        $this->approved_by = $approved_by;
    }

    function getApproved_by() {
        return $this->approved_by;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getStatus() {
        return $this->status;
    }

    function approveRequest() {
        $data = array(
            'approval_date' => $this->approval_date,
            'approved_by' => $this->approved_by,
            'status' => 1,
        );

        $where = array('id' => $this->id);

        $this->CI->db->where($where);
        $this->CI->db->update(TBL_PREFIX . 'approval', $data);
        $this->CI->db->affected_rows();

        return true;
    }

    function denyRequest() {
        $data = array(
            'approval_date' => $this->approval_date,
            'approved_by' => $this->approved_by,
            'status' => 2,
        );

        $where = array('id' => $this->id);

        $this->CI->db->where($where);
        $this->CI->db->update(TBL_PREFIX . 'approval', $data);
        $this->CI->db->affected_rows();

        return true;
    }

}
