<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Person_model extends Basic_Model {

    public function __construct() {
        parent::__construct();
        $this->table_name = 'person';
        $this->object_fields['firstname'] = 'First Name';
        $this->object_fields['lastname'] = 'Last Name';
        $this->object_fields['email'] = function($personId = '') {
                                            if (empty($personId)) {
                                                return 'Email';
                                            }
                                            $result = $this->get_record_where('person_email', 'email', ['person_id' => $personId, 'archive' => '0']);
                                            $emails = array_map(function($row){
                                                return $row->email;
                                            }, $result);
                                            
                                            return $emails;
                                        };
    }
}