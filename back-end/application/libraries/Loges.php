<?php

class Loges {

    protected $CI;
    protected $companyId = '1';
    protected $userId;
    protected $module;
    protected $sub_module = '0';
    protected $title;
    protected $description;
    private $specific_title = '';
    protected $created_by;
    protected $log_type;
    protected $created_type = 1;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setUserID($userId) {
        $this->userId = $userId;
    }

    function getUserID() {
        return $this->userId;
    }

    function setCompanyId($companyId) {
        $this->companyId = $companyId;
    }

    function getCompanyId() {
        return $this->companyId;
    }

    function setModule($module) {
        $this->module = $module;
    }

    function getModule() {
        return $this->module;
    }

    function setSubModule($sub_module) {
        $this->sub_module = $sub_module;
    }

    function getSubModule() {
        return $this->sub_module;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $this->title;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function getDescription() {
        return $this->description;
    }

    function setCreatedBy($create_by) {
        $this->created_by = $create_by;
    }

    function getCreatedBy() {
        return $this->created_by;
    }

    function setCreated_type($created_type) {
        $this->created_type = $created_type;
    }

    function getCreated_type() {
        return $this->created_type;
    }

    function setLogType($log_type) {
        $this->log_type = $log_type;
    }

    function getLogType() {
        return $this->log_type;
    }

    function setSpecific_title($specific_title) {
        $this->specific_title = $specific_title;
    }

    function getSpecific_title() {
        return $this->specific_title;
    }

    function createLog() {
        $this->setSubAndModuleDetails();

        $data = array(
            'companyId' => 1,
            'userId' => $this->userId,
            'module' => $this->module,
            'sub_module' => $this->sub_module,
            'title' => $this->title,
            'specific_title' => $this->specific_title,
            'description' => $this->description,
            'created_by' => $this->created_by,
            'created_type' => $this->created_type,
            'created' => DATE_TIME,
        );

        $this->CI->db->insert(TBL_PREFIX . 'logs', $data);
    }

    function setSubAndModuleDetails() {
        $this->CI->db->select(['id', 'parentId']);
        $this->CI->db->from(TBL_PREFIX . 'module_title as mt');
        $this->CI->db->where('mt.key_name', $this->log_type);
        $query = $this->CI->db->get();

        $res = $query->row();
        if (!empty($res)) {
            if ($res->parentId > 0) {
                $this->setModule($res->parentId);
                $this->setSubModule($res->id);
            } else {
                $this->setModule($res->id);
            }
        }
    }

}
