<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApplicantCommunicationLog
 *
 * @author user
 */
class ApplicantCommunicationLog {

    private $id;
    private $applicant_id;
    private $log_type;
    private $title;
    private $communication_text;
    private $created;
    private $mail_template_version;
    private $recruiter_id;
    private $log_type_section_key;
    private $defined_log_title = [
        'group_interview_invitation' => 'Group interview invitation',
        'cab_day_interview_invitation' => 'CAB day interview invitation',
        'cab_day_docment_resend_sms' => 'CAB day interview DocuSign document resend by sms',
        'cab_day_docment_resend_email' => 'CAB day interview DocuSign document resend by email',
        'group_docment_resend_email' => 'Group draft contract document resend by email',
        'individual_interview_invitation' => 'Individual interview invitation',
        'external_imail_send' => 'Send email from External I-Mail',
    ];

    function __construct() {
        $this->CI = & get_instance();

        $this->created = DATE_TIME;
        $this->mail_template_version = 1;
        $this->log_type_section_key='';
    }

    function setMail_template_version($mail_template_version) {
        $this->mail_template_version = $mail_template_version;
    }

    function getMail_template_version() {
        return $this->mail_template_version;
    }

    function getLogTitle($key) {
        return $this->defined_log_title[$key];
    }

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setApplicant_id($applicant_id) {
        $this->applicant_id = $applicant_id;
    }

    function getApplicant_id() {
        return $this->applicant_id;
    }

    function setLog_type($log_type) {
        $this->log_type = $log_type;
    }

    function getLog_type() {
        return $this->log_type;
    }

    function setLog_type_section_key($title) {
        $this->log_type_section_key = $title;
    }

    function getLog_type_section_key() {
        return $this->log_type_section_key;
    }
    function setTitle($title) {
        $this->title = $title;
    }

    function getTitle() {
        return $this->title;
    }

    function setCommunication_text($communication_text) {
        $this->communication_text = $communication_text;
    }

    function getCommunication_text() {
        return $this->communication_text;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function getCreated() {
        return $this->created;
    }

    function setRecruiter_id($recruiter_id) {
        $this->recruiter_id = $recruiter_id;
    }

    function getRecruiter_id() {
        return $this->recruiter_id;
    }

    function createCommunicationLog() {

        $log = array(
            'applicant_id' => $this->getApplicant_id(),
            'recruiter_id' => $this->getRecruiter_id(),
            'log_type' => $this->getLog_type(),
            'title' => $this->getTitle(),
            'communication_text' => $this->getCommunication_text(),
            'created' => $this->getCreated(),
            'log_type_section_key' => $this->getLog_type_section_key(),
        );

        //$result = $this->CI->basic_model->insert_records('recruitment_applicant_communication_log', $log, $multiple = FALSE);

        return true;
    }

    function createMuitipleCommunicationLog($data) {

        $result = $this->CI->basic_model->insert_records('communication_log', $data, $multiple = true);

        return $result;
    }

}
