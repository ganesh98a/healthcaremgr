<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Auth_token_admin
 *
 * @author user
 */
class Auth_token_admin {

    private $token;
    private $adminId;
    private $permission;
    private $uuid_user_type;

    public function __construct() {
        $this->CI = & get_instance();

        $this->CI->load->model('admin/Auth_token_model');
        $this->CI->load->model('admin/Admin_model');
    }

    function setToken($token) {
        $this->token = $token;
    }

    function getToken() {
        return $this->token;
    }

    function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    function getAdminId() {
        return $this->adminId;
    }

    function setUuid_user_type($uuid_user_type) {
        $this->uuid_user_type = $uuid_user_type;
    }

    function getUuid_user_type() {
        return $this->uuid_user_type;
    }

    function setPermission($permission) {
        $this->permission = $permission;
    }

    function getPermission() {
        return $this->permission;
    }

    function check_auth_token() {
        return $this->CI->Auth_token_model->check_auth_token($this->token);
    }

    function update_token_time() {
        return $this->CI->Auth_token_model->update_token_time($this->token);
    }

    function check_permission() {
        $response = $this->CI->Admin_model->check_admin_permission($this->adminId, $this->permission);

        if (!empty($response)) {
            return true;
        } else {
            return false;
        }
    }

    function check_another_location_opened() {
        $CI = & get_instance();

        $where_h = array('token' => $this->token, 'ip_address' => get_client_ip_server());
        $response = $CI->basic_model->get_row('member_login_history', $columns = array('last_access'), $where_h);

        if (!empty($response)) {
            $diff = strtotime(DATE_TIME) - strtotime($response->last_access);
            
            if ($diff < $CI->config->item('jwt_token_time')) {
                return true;
                echo json_encode(array('status' => false, 'another_location_opened' => true, 'error' => 'This account is opened at another location, you are being logged off.'));
                exit();
            }
        }
        
        return false;
    }

}
