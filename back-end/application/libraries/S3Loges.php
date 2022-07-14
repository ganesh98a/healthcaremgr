<?php
/**
 * This Library file holds S3 related logs creation
 */
class S3Loges {
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

    function setlogType($logType) {
        $this->logType = $logType;
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

    function getlogType() {
        return $this->logType;
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

    function createS3Log() {
        $data = array(
            'module_id' => $this->moduleId,
            'title' => $this->title,
            'log_type' => $this->logType,
            'description' => $this->description,
            'created_by' => $this->createdBy ?? NULL,
            'created_at' => DATE_TIME
        );

        $this->CI->db->insert(TBL_PREFIX . 's3_logs', $data);
    }

}
