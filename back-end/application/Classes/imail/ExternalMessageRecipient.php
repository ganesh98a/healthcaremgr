<?php

namespace Imail\externalMessageRecipient;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExternalMessageRecipient
 *
 * @author Corner stone solution
 */
class ExternalMessageRecipient {

    private $recipientId;
    private $messageId;
    private $archive;
    private $is_read;
    private $is_favourite;

    function setRecipientId($recipientId) {
        $this->recipientId = $recipientId;
    }

    function getRecipientId() {
        return $this->recipientId;
    }

    function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    function getMessageId() {
        return $this->messageId;
    }

    function setArchive($archive) {
        $this->archive = $archive;
    }

    function getArchive() {
        return $this->archive;
    }

    function setIs_read($is_read) {
        $this->is_read = $is_read;
    }

    function getIs_read() {
        return $this->is_read;
    }

    function setIs_favourite($is_favourite) {
        $this->is_favourite = $is_favourite;
    }

    function getIs_favourite() {
        return $this->is_favourite;
    }

    function createMessageExternalDetails() {
        $CI = & get_instance();

        $externalDetails = array('recipientId' => $this->getRecipientId(), 'messageId' => $this->getMessageId(), 'archive' => $this->archive, 'is_favourite' => $this->is_favourite);

        return $CI->basic_model->insert_records('external_message_recipient', $externalDetails, $multiple = FALSE);
    }

}
