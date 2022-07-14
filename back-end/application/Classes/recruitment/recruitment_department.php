<?php

namespace classRecuritmentDepartment;

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Recruitment_department  {

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

    public function creatRecuritmentDepartment() {
        $CI = & get_instance();
        $role_data = array('name' => $this->getName(), 'created' => DATE_TIME);
        $result = $CI->basic_model->insert_records('recruitment_department', $role_data, $multiple = FALSE);
        return $result;
    }

    public function updateRecuritmentDepartment() {
        $CI = & get_instance();
        $role_data = array('name' => $this->name);
        $where = array('id' => $this->getId());
        $result = $CI->basic_model->update_records('recruitment_department', $role_data, $where);
        return $result;
    }   
}
