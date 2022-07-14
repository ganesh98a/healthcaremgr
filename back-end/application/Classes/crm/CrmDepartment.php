<?php

namespace classCrmDepartment;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class CrmDepartment  {

    private $id;
    private $name;
    private $archive;
    private $created;

    function setId($id) { $this->id = $id; }
    function getId() { return $this->id; }
    function setName($name) { $this->name = $name; }
    function getName() { return $this->name; }
    function setArchive($archive) { $this->archive = $archive; }
    function getArchive() { return $this->archive; }
    function setCreated($created) { $this->created = $created; }
    function getCreated() { return $this->created; }


    public function __construct() {
        //$this->CI = &get_instance();			
    }

    public function creatCrmDepartment() {
        $CI = & get_instance();
        $dep_data = array('name' => $this->getName(), 'created' => DATE_TIME);
        $result = $CI->basic_model->insert_records('crm_department', $dep_data, $multiple = FALSE);
        return $result;
    }

    public function updateCrmDepartment() {
        $CI = & get_instance();
        $dep_data = array('name' => $this->name);
        $where = array('id' => $this->getId());
        $result = $CI->basic_model->update_records('crm_department', $dep_data, $where);
        return $result;
    }   
}
