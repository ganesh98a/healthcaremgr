<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GetUserName
 *
 * @author user
 */
class UserName {

    protected $CI;
    protected $cons = [
        "applicant" => [
            "table" => "recruitment_applicant",
            "column" => ["concat_ws(' ',firstname,middlename,lastname) as name"],
            "id" => "id"
        ],
        "admin" => [
            "table" => "member",
            "column" => ["concat_ws(' ',firstname,middlename,lastname) as name"],
            "id" => "uuid"
        ],
        "stage_label" => [
            "table" => "recruitment_stage",
            "column" => ["concat('Stage ',stage,': ',title,': ') as name"],
            "id" => "id"
        ],
        "organisation" => [
            "table" => "organisation",
            "column" => ["name"],
            "id" => "id"
        ],
        "quote_number" => [
            "table" => "finance_quote",
            "column" => ["quote_number as name"],
            "id" => "id"
        ],
        "participant" => [
            "table" => "participant",
            "column" => ["concat_ws(' ',firstname,middlename,lastname) as name"],
            "id" => "id"
        ],
        "house" => [
            "table" => "house",
            "column" => ["name"],
            "id" => "id"
        ]
    ];

    public function __construct() {
        // Assign the CodeIgniter super-object
        $this->CI = & get_instance();
    }

    public function getName($role, $id) {
        if (!empty($this->cons[$role])) {
            $res = $this->CI->basic_model->get_row($this->cons[$role]['table'], $this->cons[$role]['column'], [$this->cons[$role]["id"] => $id]);
            if (!empty($res)) {
                return $res->name;
            } else {
                return '';
            }
        }
    }

}
