<?php

namespace classPermission;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Permission {
      public $CI;
      
    private $permission_id;
    private $permission_name;
    private $permission_title;
    private $permission_status;
    private $permissioncompany_id;

    public function __construct() {
        $this->CI = & get_instance();
         
//        echo '<pre>';
        //print_r(  $CI);
       //$this->CI->load->model('Admin_model');
       $this->CI->load->model('admin/Admin_model');
    }

    /// <summary>
    /// get and set permission id
    /// </summary>
    function setpermissionid($permission_id) {
        return $this->permission_id = $permission_id;
    }

    function getpermissionid() {
        return $this->permission_id;
    }

    /// <summary>
    /// get and set permission name
    /// </summary>
    function setpermissionname($permission_name) {
        return $this->permission_name = $permission_name;
    }

    function getpermissionname() {
        return $this->permission_name;
    }

    /// <summary>
    /// get and set permission title
    /// </summary>
    function setpermissiontitle($permission_title) {
        return $this->permission_title = $permission_title;
    }

    function getpermissiontitle() {
        return $this->permission_title;
    }

    /// <summary>
    /// get and set permission status
    /// </summary>
    function setpermissionstatus($permission_status) {
        return $this->permission_status = $permission_status;
    }

    function getpermissionstatus() {
        return $this->permission_status;
    }

    /// <summary>
    /// get and set company id
    /// </summary>
    function setpermissioncompanyid($permissioncompany_id) {
        return $this->permissioncompany_id = $permissioncompany_id;
    }

    function getpermissioncompanyid() {
        return $this->permissioncompany_id;
    }

    function check_permission($adminId, $pemission_key) {
        $CI = & get_instance();
//        $CI->load->model('Admin_model');

        $response = $this->CI->Admin_model->get_admin_permission($adminId, $pemission_key);
        
        if (!empty($response)) {
            return true;
        } else {
            return false;
        }
    }

    function get_all_permission($token) {
        $CI = & get_instance();
        $CI->load->model('admin_model');

        $permissions = array();
        $response = $CI->admin_model->get_admin_permission($token);

        if (!empty($response)) {
            foreach ($response as $val) {
                $permissions[$val->permission] = 1;
            }
        }

        return $permissions;
    }

}
