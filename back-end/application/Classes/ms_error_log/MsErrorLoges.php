<?php
/*
 * Filename: MsErrorLoges.php
 * Desc: Deatils of MsErrorLoges
 * 
 */

namespace MsErrorClass;

use stdClass;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class MsErrorLoges {
    protected $moduleID;
    protected $title;
    protected $description;
    protected $created_by;
    protected $log_type;

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    function setModuleID($moduleId) {
        $this->moduleId = $moduleId;
    }

    function setTitle($title) {
        $this->title = $title;      
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setCreatedBy($createdBy) {
        $this->createdBy = $createdBy;
    }

    function setCreatedAT($createdAt) {
        $this->createdAt = $createdAt;
    }

    function getModuleID() {
        return $this->moduleId;
    }

    function getTitle() {
        return $this->title;
    }    

    function getDescription() {
        return $this->description;
    }

    function getCreatedBy() {
        return $this->createdBy;
    }

    function getCreatedAT() {
        return $this->createdAt;
    }

    function createMsErrorLog() {
        $data = array(
            'module_id' => $this->moduleId,
            'title' => $this->title,
            'description' => $this->description,
            'created_by' => $this->createdBy ?? NULL,
            'created_at' => DATE_TIME
        );

        $this->CI->db->insert(TBL_PREFIX . 'ms_error_logs', $data);
    }

}
