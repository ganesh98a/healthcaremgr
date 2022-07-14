<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OutOfOfficeMessage
 *
 * @author user
 */
class OutOfOfficeMessage {

    private $id;
    private $create_for;
    private $from_date;
    private $end_date;
    private $replace_by;
    private $created_by;
    private $additional_message;
    private $created;
    private $updated;
    private $archive;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setFrom_date($from_date) {
        $this->from_date = $from_date;
    }

    function getFrom_date() {
        return $this->from_date;
    }

    function setEnd_date($end_date) {
        $this->end_date = $end_date;
    }

    function getEnd_date() {
        return $this->end_date;
    }

    function setCreate_for($create_for) {
        $this->create_for = $create_for;
    }

    function getCreate_for() {
        return $this->create_for;
    }

    function setReplace_by($replace_by) {
        $this->replace_by = $replace_by;
    }

    function getReplace_by() {
        return $this->replace_by;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    function setAdditional_message($additional_message) {
        $this->additional_message = $additional_message;
    }

    function getAdditional_message() {
        return $this->additional_message;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setUpdated($updated) {
        $this->updated = $updated;
    }

    function getUpdated() {
        return $this->updated;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function create_update_out_of_office_message() {
        $CI = & get_instance();
        $CI->load->model("imail/Out_of_office_model");

        return $CI->Out_of_office_model->create_update_out_of_office_message($this);
    }

    function check_already_exist_out_of_office() {
        $CI = & get_instance();
        $CI->load->model("imail/Out_of_office_model");

        return $CI->Out_of_office_model->check_already_exist_out_of_office($this);
    }

}
