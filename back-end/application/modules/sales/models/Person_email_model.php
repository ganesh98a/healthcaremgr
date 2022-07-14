<?php
defined('BASEPATH') || exit('No direct script access allowed');

class Person_email_model extends Basic_Model {
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'person_email';
        $this->object_fields['email'] = function($personId = '') {
                                                                        if (empty($personId)) {
                                                                            return 'Email';
                                                                        }
                                                                        $result = $this->get_record_where('person_email','email', ['person_id' => $personId, 'archive' => '0']);
                                                                        return $result[0]->email;
                                                                    };
    }
}