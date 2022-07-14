<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Imail\internalMessageContent;

/**
 * Description of InternalMessageContent
 *
 * @author Corner stone solution
 */
require_once APPPATH . 'Classes/imail/InternalMessageRecipient.php';

class InternalMessageContent extends \Imail\internalMessageRecipient\InternalMessageRecipient {

    private $messageId;
    private $recipientId;
    private $senderId;
    private $created;
    private $content;

    function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    function getMessageId() {
        return $this->messageId;
    }

    function setRecipientId($recipientId) {
        $this->recipientId = $recipientId;
    }

    function getRecipientId() {
        return $this->recipientId;
    }

    function setSenderId($senderId) {
        $this->senderId = $senderId;
    }

    function getSenderId() {
        return $this->senderId;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setContent($content) {
        $this->content = $content;
    }

    function getContent() {
        return $this->content;
    }

    function createMessageContent() {
        $CI = & get_instance();

        $messageContent = array('messageId' => $this->messageId, 'recipientId' => $this->recipientId, 'senderId' => $this->senderId, 'created' => date('Y-m-d H:i:s'), 'content' => $this->content);

        return $CI->basic_model->insert_records('internal_message_content', $messageContent, $multiple = FALSE);
    }

}
