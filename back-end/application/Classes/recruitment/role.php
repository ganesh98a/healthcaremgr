<?php

namespace classRoles;

use classPermission;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
require_once APPPATH . 'Classes/admin/permission.php';

class Roles extends classPermission\Permission {

    private $role_id;
    private $role_name;
    private $roles = array();
    private $role_status;
    private $company_id;
    private $role_createdate;
    private $role_modifieddate;
    private $archive = 0;

    public function __construct() {
        //$this->CI = &get_instance();			
    }

    /// <summary>
    /// get and set role id
    /// </summary>
    function setroleid($role_id) {
        return $this->role_id = $role_id;
    }

    function getroleid() {
        return $this->role_id;
    }

    /// <summary>
    /// get and set role id
    /// </summary>
    function setcompanyid($company_id) {
        return $this->company_id = $company_id;
    }

    function getcompanyid() {
        return $this->company_id;
    }

    /// <summary>
    /// get and set role role name
    /// </summary>
    function setrolename($role_name) {
        return $this->role_name = $role_name;
    }

    function getrolename() {
        return $this->role_name;
    }

    /// <summary>
    /// get and set role status
    /// </summary>
    function setrolestatus($role_status) {
        return $this->role_status = $role_status;
    }

    function getrolestatus() {
        return $this->role_status;
    }

    /// <summary>
    /// get and set created date
    /// </summary>
    function setcreateddate($role_createdate) {
        return $this->role_createdate = $role_createdate;
    }

    function getcreateddate() {
        return $this->role_createdate;
    }

    /// <summary>
    /// get and set modified date
    /// </summary>
    function setArchive($archive) {
        return $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function setRoles($roles) {
        $this->roles = $roles;
    }

    function getRoles() {
        return $this->roles;
    }

    /// <summary>
    /// Methods
    /// </summary>
    public function CreateRole() {
        $CI = & get_instance();
        $role_data = array('name' => $this->role_name, 'created' => date('Y-m-d h:i:s'));
        $result = $CI->basic_model->insert_records('role', $role_data, $multiple = FALSE);
        return $result;
    }

    public function UpdateRoles() {
        $CI = & get_instance();
        $role_data = array('name' => $this->role_name, 'archive' => $this->archive);
        $where = array('id' => $this->role_id);
        $result = $CI->basic_model->update_records('role', $role_data, $where);
        return $result;
    }

    public function checkAlreadyExist() {
        $where = array('name' => $this->role_name, 'archive' => 0);
        $CI = & get_instance();
        if ($this->role_id > 0) {
            $where['id !='] = $this->role_id;
        }

        $response = $CI->basic_model->get_record_where('role', $column = array('name'), $where);

        if (!empty($response)) {
            return false;
        } else {
            return true;
        }
    }

    public function getAllRoles() {
        $CI = & get_instance();
        $response = $CI->basic_model->get_record_where_orderby('role', $column = array('name', 'id', '1 as access'), $where = array('status' => 1, 'archive' => 0), 'weight', 'asc');

        return $response;
    }

    public function getUserBasedRoles($all = false) {
        $CI = & get_instance();
//        var_dump($all);
        // if admin id = 1 mean its supan admin then get all roles
        if ($this->getAdminId() == 1) {
            $response = $this->getAllRoles();
        } else {
            $response = $CI->admin_model->get_admin_based_roles($this->getAdminId(), $all);
        }

        return $response;
    }

}
