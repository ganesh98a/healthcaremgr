<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Imail\externalMessageContent;

/**
 * Description of ExternalMessageContent
 *
 * @author Corner stone solution
 */
require_once APPPATH . 'Classes/imail/ExternalMessageRecipient.php';

class ExternalMessageContent extends \Imail\externalMessageRecipient\ExternalMessageRecipient {

    private $messageId;
    private $adminId;
    private $userId;
    private $sender_type;
    private $created;
    private $content;
    private $is_read;

    function setMessageId($messageId) {
        $this->messageId = $messageId;
    }

    function getMessageId() {
        return $this->messageId;
    }

    function setAdminId($adminId) {
        $this->adminId = $adminId;
    }

    function getAdminId() {
        return $this->adminId;
    }

    function setUserId($userId) {
        $this->userId = $userId;
    }

    function getUserId() {
        return $this->userId;
    }

    function setSender_type($sender_type) {
        $this->sender_type = $sender_type;
    }

    function getSender_type() {
        return $this->sender_type;
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

    function setIs_read($is_read) {
        $this->is_read = $is_read;
    }

    function getIs_read() {
        return $this->is_read;
    }

    function createMessageContent() {
        $CI = & get_instance();

        $messageContent = array('messageId' => $this->messageId, 'adminId' => $this->adminId, 'userId' => $this->userId, 'sender_type' => $this->sender_type, 'created' => DATE_TIME, 'content' => $this->content, 'is_read' => 0);

        return $CI->basic_model->insert_records('external_message_content', $messageContent, $multiple = FALSE);
    }

}
