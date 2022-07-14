<?php

namespace Imail\externalMessage;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExternalMessage
 *
 * @author Corner stone solutions
 */

require_once APPPATH . 'Classes/imail/ExternalMessageContent.php';

class ExternalMessage extends \Imail\externalMessageContent\ExternalMessageContent{

    private $id;
    private $companyId;
    private $title;
    private $created;
    private $type;
    private $recipient_type;

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

    function setType($type) {
        $this->type = $type;
    }

    function getType() {
        return $this->type;
    }

    function setRecipient_type($recipient_type) {
        $this->recipient_type = $recipient_type;
    }

    function getRecipient_type() {
        return $this->recipient_type;
    }

    function createMessage() {
        $CI = & get_instance();

        $messageData = array('companyId' => $this->companyId, 'title' => $this->title, 'created' => $this->created, 'type' => $this->type, 'recipient_type' => $this->recipient_type);
        return $CI->basic_model->insert_records('external_message', $messageData, $multiple = FALSE);
    }

}
