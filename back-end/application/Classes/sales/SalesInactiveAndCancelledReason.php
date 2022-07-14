<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SalesInactiveAndCancelledReason
 *
 * @author user
 */
class SalesInactiveAndCancelledReason {

    private $id;
    private $entity_id;
    private $entity_type;
//    private $reason_reference_data_type_id;
    private $reason_id;
    private $reason_note;
    private $created = DATE_TIME;
    private $created_by;
    private $archive;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setEntity_id($entity_id) {
        $this->entity_id = $entity_id;
    }

    function getEntity_id() {
        return $this->entity_id;
    }

    function setEntity_type($entity_type) {
        $this->entity_type = $entity_type;
    }

    function getEntity_type() {
        return $this->entity_type;
    }

//    function setReason_reference_data_type_id($reason_reference_data_type_id) {
//        $this->reason_reference_data_type_id = $reason_reference_data_type_id;
//    }
//
//    function getReason_reference_data_type_id() {
//        return $this->reason_reference_data_type_id;
//    }

    function setReason_id($reason_id) {
        $this->reason_id = $reason_id;
    }

    function getReason_id() {
        return $this->reason_id;
    }

    function setReason_note($reason_note) {
        $this->reason_note = $reason_note;
    }

    function getReason_note() {
        return $this->reason_note;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setCreated_by($created_by) {
        $this->created_by = $created_by;
    }

    function getCreated_by() {
        return $this->created_by;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function createReason() {
        $CI = &get_instance();

        $reson_data = [
            "entity_id" => $this->entity_id,
            "entity_type" => $this->entity_type ?? "",
//            "reason_reference_data_type_id" => $this->reason_reference_data_type_id ?? "",
            "reason_id" => $this->reason_id ?? "",
            "reason_note" => $this->reason_note ?? "",
            "created" => $this->created,
            "created_by" => $this->created_by ?? "",
            "archive" => 0,
        ];

        $where = ["entity_id" => $this->entity_id, "entity_type" => $this->entity_type, "archive" => 0];
        $CI->basic_model->update_records("sales_inactive_and_cancelled_reason", ["archive" => 1], $where);
        return $CI->basic_model->insert_records("sales_inactive_and_cancelled_reason", $reson_data, false);
    }

}
