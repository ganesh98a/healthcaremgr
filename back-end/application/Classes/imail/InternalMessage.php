<?php

namespace Imail\internalMessage;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of InternalMessage
 *
 * @author Corner stone solutions
 */
require_once APPPATH . 'Classes/imail/InternalMessageContent.php';

class InternalMessage extends \Imail\internalMessageContent\InternalMessageContent {

    private $id;
    private $companyId;
    private $title;
    private $created;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setCompanyId($companyId) {
        $this->companyId = $companyId;
    }

    function getCompanyId() {
        return $this->companyId;
    }

    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $this->title;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function createMessage() {
        $CI = & get_instance();

        $messageData = array('companyId' => $this->companyId, 'title' => $this->title, 'created' => $this->created);
        return $CI->basic_model->insert_records('internal_message', $messageData, $multiple = FALSE);
    }

}
