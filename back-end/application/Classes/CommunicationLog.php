<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CommunicationLog
 *
 * @author user
 */
class CommunicationLog
{

    private $id;
    private $userId;
    private $user_type;
    private $log_type;
    private $from;
    private $title;
    private $communication_text;
    private $send_by;
    private $created;

    function __construct()
    {
        $this->CI = &get_instance();

        $this->created = DATE_TIME;
        $this->mail_template_version = 1;
        $this->log_type_section_key = '';
    }

    function setId($id)
    {
        $this->id = $id;
    }
    function getId()
    {
        return $this->id;
    }
    function setUserId($userId)
    {
        $this->userId = $userId;
    }
    function getUserId()
    {
        return $this->userId;
    }

    function setUser_type($user_type)
    {
        $this->user_type = $user_type;
    }
    function getUser_type()
    {
        return $this->user_type;
    }

    function setLog_type($log_type)
    {
        $this->log_type = $log_type;
    }
    function getLog_type()
    {
        return $this->log_type;
    }

    function setFrom($from)
    {
        $this->from = $from;
    }
    function getFrom()
    {
        return $this->from;
    }

    function setTitle($title)
    {
        $this->title = $title;
    }
    function getTitle()
    {
        return $this->title;
    }
    function setCommunication_text($communication_text)
    {
        $this->communication_text = $communication_text;
    }
    function getCommunication_text()
    {
        return $this->communication_text;
    }
    function setSend_by($send_by)
    {
        $this->send_by = $send_by;
    }
    function getSend_by()
    {
        return $this->send_by;
    }
    function setCreated($created)
    {
        $this->created = $created;
    }
    function getCreated()
    {
        return $this->created;
    }

    function createCommunicationLog()
    {
        $log = array(
            'userId' => $this->userId ?? '',
            'user_type' => $this->user_type ?? '',
            'log_type' => $this->log_type,
            'from' => $this->from ?? '',
            'title' => $this->title ?? '',
            "communication_text" => $this->communication_text ?? '',
            'send_by' => $this->send_by ?? '',
            'created' => $this->created,
        );

        $result = $this->CI->basic_model->insert_records('communication_log', $log, $multiple = FALSE);
        return $result;
    }

    function createMuitipleCommunicationLog($data)
    {
        $result = $this->CI->basic_model->insert_records('communication_log', $data, $multiple = true);

        return $result;
    }
}
