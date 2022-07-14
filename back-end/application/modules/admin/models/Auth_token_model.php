<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_token_model extends CI_Model {

    public function __construct() {
        // Call the CI_Model constructor
        parent::__construct();
    }

    function check_auth_token($token) {
        $where = array(
            'ml.token' => $token,
        );
        $this->db->select(['ml.updated', 'u.id as adminId', 'ml.token', 'ml.ip_address', 'u.user_type as uuid_user_type']);
        $this->db->from('tbl_member_login as ml');
        $this->db->join('tbl_users as u', 'u.id = ml.memberId', 'inner');
        $this->db->where($where);
        $query = $this->db->get();

        return $query->row();
    }


    function update_token_time($token) {
        $where_al = array('token' => $token);
        return $this->basic_model->update_records('member_login', $columns = array('updated' => DATE_TIME), $where_al);
    }

}
